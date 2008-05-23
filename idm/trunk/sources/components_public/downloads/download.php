<?php

/*
+--------------------------------------------------------------------------
|   Invision Download Manager
|   ========================================
|   by Brandon Farber
|   (c) 2005 - 2006 Invision Power Services
|   ========================================
+---------------------------------------------------------------------------
|
|   > Download Script - Does the download
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class idm_download
{
	var $ipsclass;
	var $catlib;

	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";
	
	var $restricted		= 0;
	
	var $dl_sessions	= array();

    /*-------------------------------------------------------------------------*/
	// Our run_me function
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
	    // Global navigation bar/title
	    $this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=downloads'>".$this->ipsclass->lang['idm_navbar'].'</a>';
		$this->page_title = $this->ipsclass->vars['board_name']." -> ".$this->ipsclass->lang['idm_pagetitle'];
		
		// Setup access permissions
		$this->catlib->get_member_cat_perms();
		
		// Can we see any categories?
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
		}
		else
		{
			switch( $this->ipsclass->input['code'] )
			{
				case 'confirm_download':
					$this->display_confirm( );
					break;
				
				case 'do_download':
					$this->do_download( );
					break;
					
				case 'version_download':
					$this->do_version_download();
					break;
					
				default:
					$this->display_confirm( );
					break;
			}
		}
		
		// Print the output
    	$this->ipsclass->print->add_output( $this->output );
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'NAV' => $this->nav ) );
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Search Form
    /*-------------------------------------------------------------------------*/
    	
	function display_confirm( )
	{
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}

		
		$info = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'file_name, file_id, file_cat, file_open',
																'from'	=> "downloads_files", 
																'where' => 'file_id='.intval($this->ipsclass->input['id'])
														)		);
		if( !$info['file_id'] )
		{
			$this->produce_error( 'error_generic' );
			return;
		}
		
		$category = $this->catlib->cat_lookup[$info['file_cat']];
		
		if( !in_array( $info['file_cat'], $this->catlib->member_access['download'] ) )
		{
			if( $category['coptions']['opt_noperm_dl'] )
			{
				$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
			}
			else
			{
				$this->produce_error( 'cannot_do_download' );
			}
			return;
		}
		
		if( !$info['file_open'] )
		{
			$ok	= 0;

			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$ok   = 1;
			}
			else
			{
				if( is_array( $this->catlib->cat_mods[ $info['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $info['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $info['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
						}
					}
				}
			}
			
			if( !$ok )
			{
				if( $category['coptions']['opt_noperm_dl'] )
				{
					$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
				}
				else
				{
					$this->produce_error( 'cannot_do_download' );
				}
				return;
			}
		}
		
		$this->ipsclass->lang['dpage_starting'] = sprintf( $this->ipsclass->lang['dpage_starting'], $info['file_name'] );
		
		if( $category['cdisclaimer'] )
		{
			$info['disclaimer'] = $category['cdisclaimer'];
		}
		else
		{
			$this->do_download();
			return;
		}
		
		$this->nav = array_merge( $this->nav, $this->catlib->get_nav( $info['file_cat'] ) );
		$this->nav[] = $info['file_name'];
		
		$this->page_title .= " -> {$category['cname']} -> {$info['file_name']}";
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->confirm_download( $info );
		return;
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Do Download
    /*-------------------------------------------------------------------------*/
    	
	function do_download( )
	{
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}
		if( count($this->catlib->member_access['download']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}
	
		if( $this->ipsclass->vars['idm_antileech'] && isset($_SERVER['HTTP_REFERER']) )
		{
			$referer 	= getenv("HTTP_REFERER");
			$host 		= parse_url($referer);
			if( $host['host'] != $_SERVER['HTTP_HOST'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads&showfile={$this->ipsclass->input['id']}" );
			}
		}
		
		$this->sort_restrictions();
		
		// Are we restricted?
		
		if( $this->restricted == 1 )
		{
			return;
		}
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'db' )
		{
			$this->ipsclass->DB->cache_add_query( "get_download_info_db", array( 'file_id' => intval($this->ipsclass->input['id']) ), "sql_idm_queries" );
			$this->ipsclass->DB->cache_exec_query();
		}
		else
		{
			$this->ipsclass->DB->cache_add_query( "get_download_info", array( 'file_id' => intval($this->ipsclass->input['id']) ), "sql_idm_queries" );
			$this->ipsclass->DB->cache_exec_query();
		}			
		
		$info = $this->ipsclass->DB->fetch_row();
		
		$category = $this->catlib->cat_lookup[$info['file_cat']];
		
		if( !$info['file_id'] )
		{
			$this->produce_error( 'error_generic' );
			return;
		}
		
		if( !in_array( $info['file_cat'], $this->catlib->member_access['download'] ) )
		{
			if( $category['coptions']['opt_noperm_dl'] )
			{
				$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
			}
			else
			{
				$this->produce_error( 'cannot_do_download' );
			}
			return;
		}
		
		if( !$info['file_open'] )
		{
			$ok	= 0;

			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$ok   = 1;
			}
			else
			{
				if( is_array( $this->catlib->cat_mods[ $info['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $info['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $info['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
						}
					}
				}
			}
			
			if( !$ok )
			{
				if( $category['coptions']['opt_noperm_dl'] )
				{
					$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
				}
				else
				{
					$this->produce_error( 'cannot_do_download' );
				}
				return;
			}
		}
		
		// Store this session now, and the shutdown query
		
		$dsess_id = md5( uniqid( microtime(), true ) );

		$this->ipsclass->DB->do_insert( 'downloads_sessions', array( 'dsess_id' 	=> $dsess_id,
																	 'dsess_mid'	=> $this->ipsclass->member['id'],
																	 'dsess_ip'		=> $this->ipsclass->ip_address,
																	 'dsess_file'	=> intval($this->ipsclass->input['id']),
																	 'dsess_start'	=> time()
										)							);

		$this->ipsclass->DB->build_query( array( 'delete' => 'downloads_sessions', 'where' => "dsess_id='{$dsess_id}'" ) );
		$this->ipsclass->DB->exec_shutdown_query();
		
		$types = array( 'inline'	=> array() );
		
		if( count( $this->ipsclass->cache['idm_mimetypes'] ) )
		{
			foreach( $this->ipsclass->cache['idm_mimetypes'] as $k => $v )
			{
				$inline = explode( ",", $v['mime_inline'] );
				if( in_array( $category['coptions']['opt_mimemask'], $inline ) )
				{
					$types['inline'][] = $v['mime_mimetype'];
				}
			}
		}
		
		if( in_array( $info['mime_mimetype'], $types['inline'] ) )
		{
			$disposition = "inline";
		}
		else
		{
			$disposition = "attachment";
		}
		
		if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
			if( $this->ipsclass->operating_system == 'unknown' )
			{
				if( strstr( strtolower($_SERVER['HTTP_USER_AGENT']), 'linux' ) )
				{
					$this->ipsclass->operating_system = 'linux';
				}
			}
			
			$to_insert = array( 'dfid' 		=> $info['file_id'],
								'dtime'		=> time(),
								'dip'		=> $this->ipsclass->ip_address,
								'dmid'		=> $this->ipsclass->member['id'],
								'dsize'		=> $info['file_size'],
								'dua'		=> addslashes($_SERVER['HTTP_USER_AGENT']) );
			
			require_once( DL_PATH.'lib/lib_traffic.php' );
			$traffic = new lib_traffic();
			$traffic->ipsclass =& $this->ipsclass;
			$traffic->load_libraries();
			
			$parsed_visitor = $traffic->return_stat_data( $to_insert );
			
			$to_insert['dbrowsers']	= $parsed_visitor['stat_browser_key'];
			$to_insert['dcountry']	= $parsed_visitor['stat_country'];
			$to_insert['dos']		= $parsed_visitor['stat_os_key'];
			
			$this->ipsclass->DB->do_insert( "downloads_downloads", $to_insert );
		}
		
		$this->ipsclass->DB->simple_update( "downloads_files", 'file_downloads=file_downloads+1', 'file_id='.$info['file_id'] );
		$this->ipsclass->DB->exec_query();
		
		$this->catlib->rebuild_fileinfo($info['file_cat']);
		$this->catlib->rebuild_stats_cache();
		
		if( $info['file_realname'] )
		{
			$info['file_downloadasname'] = $info['file_realname'];
		}
		else
		{
			$info['file_downloadasname'] = $info['file_filename'];
			
			$curr_ext = strrchr( $info['file_downloadasname'], "." );
			
			if(  $curr_ext == ".txt" AND $curr_ext != ".".$info['mime_extension'] )
			{
				$info['file_downloadasname'] = str_replace( $curr_ext, ".".$info['mime_extension'], $info['file_downloadasname'] );
			}
	
			$info['file_downloadasname'] = str_replace( $info['file_id']."-", "", $info['file_downloadasname'] );
			$info['file_downloadasname'] = preg_replace( "/^\d{10,11}\-(.+?)$/", "\\1", $info['file_downloadasname'] );
		}
		
		if( $info['file_url'] )
		{
			$this->ipsclass->boink_it( $info['file_url'] );
		}
		
		// Everything is good, do the download
		switch( $info['file_storagetype'] )
		{
			case 'db':
				$content = base64_decode($info['storage_file']);
				
				header( "Content-Type: ".$info['mime_mimetype'] );
				header( "Content-Disposition: {$disposition}; filename=\"".$info['file_downloadasname']."\"" );
				header( "Content-Length: ".(string)(strlen( $content ) ) );
				
				print $content;
				break;
				
			case 'ftp':
				$path = $this->ipsclass->vars['idm_remotefileurl']."/".$info['file_filename'];
				
				$this->ipsclass->boink_it( $path );
				break;
				
			case 'web':
			case 'nonweb':
				if( $info['file_storagetype'] == 'web' )
				{
					$path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] )."/";
				}
				else
				{
					$path = $this->ipsclass->vars['idm_localfilepath']."/";
				}
				
				$to_send = array(	'name' 			=> $info['file_downloadasname'],
									'mimetype' 		=> $info['mime_mimetype'],
									'disposition'	=> $disposition,
									'true_file'		=> $path . $info['file_filename'],
								);
									
				if( !$this->download_local_file( $to_send ) )   
				{
					$this->produce_error( 'file_not_found' );
					return;
				}

				break;
		}
		
		exit();
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Do Version Download
    /*-------------------------------------------------------------------------*/
    	
	function do_version_download( )
	{
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}
		
		if( count($this->catlib->member_access['download']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}
		
		if( !$this->ipsclass->vars['idm_versioning'] )
		{
			$this->produce_error( 'no_downloads_permissions' );
			return;
		}			
	
		if( $this->ipsclass->vars['idm_antileech'] && isset($_SERVER['HTTP_REFERER']) )
		{
			$referer 	= getenv("HTTP_REFERER");
			$host 		= parse_url($referer);

			if( $host['host'] != $_SERVER['HTTP_HOST'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads&showfile={$this->ipsclass->input['id']}" );
			}
		}
		
		$id = intval($this->ipsclass->input['id']);
		
		$info = $this->ipsclass->DB->build_and_exec_query( array( 'select' 		=> 'b.*', 
																	'from' 		=> array( 'downloads_filebackup' => 'b' ), 
																	'where' 	=> 'b.b_id='.$id,
																	'add_join'	=> array(
																						array( 'select' => 'f.*',
																								'from'	=> array( 'downloads_files' => 'f' ),
																								'where'	=> 'f.file_id=b.b_fileid',
																								'type'	=> 'left'
																							)
																						)
														) 		);
		
		$category = $this->catlib->cat_lookup[$info['file_cat']];
		
		if( !$info['file_id'] )
		{
			$this->produce_error( 'error_generic' );
			return;
		}
		
		if( !in_array( $info['file_cat'], $this->catlib->member_access['download'] ) )
		{
			if( $category['coptions']['opt_noperm_dl'] )
			{
				$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
			}
			else
			{
				$this->produce_error( 'cannot_do_download' );
			}
			return;
		}
		
		if( !$info['file_open'] )
		{
			$ok	= 0;

			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$ok   = 1;
			}
			else
			{
				if( is_array( $this->catlib->cat_mods[ $info['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $info['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $info['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								if( $v['modcanapp'] )
								{
									$ok = 1;
								}
							}
						}
					}
				}
			}
			
			if( !$ok )
			{
				if( $category['coptions']['opt_noperm_dl'] )
				{
					$this->produce_error( $category['coptions']['opt_noperm_dl'], 1 );
				}
				else
				{
					$this->produce_error( 'cannot_do_download' );
				}
				return;
			}
		}
		
		// Store this session now
		
		$dsess_id = md5( uniqid( microtime(), true ) );

		$this->ipsclass->DB->do_insert( 'downloads_sessions', array( 'dsess_id' 	=> $dsess_id,
																	 'dsess_mid'	=> $this->ipsclass->member['id'],
																	 'dsess_ip'		=> $this->ipsclass->ip_address,
																	 'dsess_file'	=> intval($this->ipsclass->input['id']),
																	 'dsess_start'	=> time()
										)							);

		$this->ipsclass->DB->build_query( array( 'delete' => 'downloads_sessions', 'where' => "dsess_id='{$dsess_id}'" ) );
		$this->ipsclass->DB->exec_shutdown_query();
		
		$types = array( 'inline'	=> array() );
		
		if( count( $this->ipsclass->cache['idm_mimetypes'] ) )
		{
			foreach( $this->ipsclass->cache['idm_mimetypes'] as $k => $v )
			{
				$inline = explode( ",", $v['mime_inline'] );
				if( in_array( $category['coptions']['opt_mimemask'], $inline ) )
				{
					$types['inline'][] = $v['mime_mimetype'];
				}
			}
		}
		
		if( in_array( $info['b_filemime'], $types['inline'] ) )
		{
			$disposition = "inline";
		}
		else
		{
			$disposition = "attachment";
		}
		
		if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
			if( $this->ipsclass->operating_system == 'unknown' )
			{
				if( strstr( strtolower($_SERVER['HTTP_USER_AGENT']), 'linux' ) )
				{
					$this->ipsclass->operating_system = 'linux';
				}
			}
			
			$to_insert = array( 'dfid' 		=> $info['file_id'],
								'dtime'		=> time(),
								'dip'		=> $this->ipsclass->ip_address,
								'dmid'		=> $this->ipsclass->member['id'],
								'dsize'		=> $info['file_size'],
								'dua'		=> addslashes($_SERVER['HTTP_USER_AGENT']) );
			
			require_once( DL_PATH.'lib/lib_traffic.php' );
			$traffic = new lib_traffic();
			$traffic->ipsclass =& $this->ipsclass;
			$traffic->load_libraries();
			
			$parsed_visitor = $traffic->return_stat_data( $to_insert );
			
			$to_insert['dbrowsers']	= $parsed_visitor['stat_browser_key'];
			$to_insert['dcountry']	= $parsed_visitor['stat_country'];
			$to_insert['dos']		= $parsed_visitor['stat_os_key'];
			
			$this->ipsclass->DB->do_shutdown_insert( "downloads_downloads", $to_insert );
		}
		
		$this->ipsclass->DB->simple_update( "downloads_files", 'file_downloads=file_downloads+1', 'file_id='.$info['file_id'] );
		$this->ipsclass->DB->exec_query();
		
		$this->catlib->rebuild_fileinfo($info['file_cat']);
		$this->catlib->rebuild_stats_cache();
		
		$info['file_downloadasname'] = $info['b_filereal'];
		
		$curr_ext = strrchr( $info['file_downloadasname'], "." );
		
		if(  $curr_ext == ".txt" AND $curr_ext != ".".$info['mime_extension'] )
		{
			$info['file_downloadasname'] = str_replace( $curr_ext, ".".$info['mime_extension'], $info['file_downloadasname'] );
		}

		$info['file_downloadasname'] = str_replace( $info['file_id']."-", "", $info['file_downloadasname'] );
		
		$info['file_downloadasname'] = preg_replace( "/^\d{10,11}\-(.+?)$/", "\\1", $info['file_downloadasname'] );
		
		if( $info['b_fileurl'] )
		{
			$this->ipsclass->boink_it( $info['b_fileurl'] );
		}
		
		if( $info['b_storage'] == 'web' )
		{
			$path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] )."/";
		}
		else
		{
			$path = $this->ipsclass->vars['idm_localfilepath']."/";
		}
		
		$to_send = array(	'name' 			=> $info['file_downloadasname'],
							'mimetype' 		=> $info['b_filemime'],
							'disposition'	=> $disposition,
							'true_file'		=> $path . $info['b_filename'],
						);
							
		if( !$this->download_local_file( $to_send ) )   
		{
			$this->produce_error( 'file_not_found' );
			return;
		}

		exit();
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Sort out group-based restrictions
    /*-------------------------------------------------------------------------*/	
    
    function sort_restrictions()
    {
	    $my_groups = array( $this->ipsclass->member['mgroup'] );
	    
	    if( $this->ipsclass->member['mgroup_others'] )
	    {
		    $other_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
		    
		    $my_groups = array_merge( $my_groups, $other_mgroups );
	    }
	    
	    $can_download = 1;
	    
	    // First, loop through groups and determine what restrictions are placed on member (better overrides worse)
	    // Then, loop through the restrictions and see if they're blocked
	    // If blocked, set can_download to 0, break loop, and show error
	    
	    $my_restrictions 	= array();
	    
	    $less_is_more		= array( 'min_posts', 'posts_per_dl' );
	    
	    foreach( $my_groups as $gid )
	    {
		    $group = $this->ipsclass->cache['group_cache'][ $gid ];
		   	
		    $this_restrictions = array();
		    $this_restrictions = unserialize( $group['idm_restrictions'] );
		    
		    if( is_array( $this_restrictions ) AND count( $this_restrictions ) )
		    {
			    if( $this_restrictions['enabled'] == 1 )
			    {
				    foreach( $this_restrictions as $k => $v )
				    {
					    if( isset($my_restrictions[$k]) AND $my_restrictions[$k] == 0 )
					    {
						    // Zero is always best - it means no restriction
						    continue;
					    }
					    else if( in_array( $k, $less_is_more ) )
					    {
						    // Lower the better for post-based restrictions
						    
						    if( isset( $my_restrictions[$k] ) )
						    {
							    if( $v < $my_restrictions[$k] )
							    {
								    $my_restrictions[$k] = $v;
							    }
						    }
						    else
						    {
							    $my_restrictions[$k] = $v;
						    }
					    }
					    else
					    {
						    // Higher the better for bw/dl restrictions
						    
						    if( $v > intval($my_restrictions[$k]) )
						    {
							    $my_restrictions[$k] = $v;
						    }
					    }
				    }
			    }
		    }
	    }
	    
	    // Now we should have this member's restrictions in place.
	    // Let's check...if all are 0, go ahead and return now
	    
	    if( !is_array($my_restrictions) OR !count($my_restrictions) )
	    {
		    // No restrictions
		    return;
	    }
	    else
	    {
		    $at_least_one = 0;
		    
		    foreach( $my_restrictions as $k => $v )
		    {
			    if( $v > 0 )
			    {
				    $at_least_one = 1;
				    break;
			    }
		    }
		    
		    if( $at_least_one == 0 )
		    {
			    // All restrictions disabled
			    return;
		    }
	    }
	    
	    // Still here?  Ok, check restrictions
	    
	    // Before we loop, let's get the counts we'll need (easier to do this in three queries)
	    
	    // If this is a guest, check IP too
	    
	    $ip_check	= '';
	    
	    if( !$this->ipsclass->member['id'] )
	    {
		    $ip_check = " AND dip='{$this->ipsclass->ip_address}'";
	    }
	    
	    $one_day	= time() - 86400;
	    $daily 		= $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as dl, SUM(dsize) as bw', 'from' => 'downloads_downloads', 'where' => 'dmid='.$this->ipsclass->member['id'].' AND dtime > '.$one_day . $ip_check ) );
	    
	    $one_week	= time() - 604800;
	    $weekly		= $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as dl, SUM(dsize) as bw', 'from' => 'downloads_downloads', 'where' => 'dmid='.$this->ipsclass->member['id'].' AND dtime > '.$one_week . $ip_check ) );
	    
	    $one_month	= time() - 2592000;
	    $monthly	= $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as dl, SUM(dsize) as bw', 'from' => 'downloads_downloads', 'where' => 'dmid='.$this->ipsclass->member['id'].' AND dtime > '.$one_month . $ip_check ) );
	    
	    foreach( $my_restrictions as $k => $v )
	    {
		    if( $v > 0 )
		    {
			    if( $k == 'min_posts' )
			    {
				    if( $this->ipsclass->member['posts'] < $v )
				    {
					    $this->produce_error( 'dl_restrict_min_posts' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'posts_per_dl' )
			    {
				    // Get last download stamp
				    
				    $download = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'MAX(dtime) as dtime', 'from' => 'downloads_downloads', 'where' => 'dmid='.$this->ipsclass->member['id'] . $ip_check ) );
				    
				    if( $download['dtime'] )
				    {
					    $posts = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as num', 'from' => 'posts', 'where' => 'author_id='.$this->ipsclass->member['id'].' AND post_date>'.$download['dtime'] ) );
					    
					    if( $posts['num'] < $v )
					    {
						    $this->produce_error( 'dl_restrict_posts_p_dl' );
						    $this->restricted = 1;
						    return;
					    }
				    }
			    }
			    
			    if( $k == 'daily_bw' AND $daily['bw'] )
			    {
				    if( $daily['bw'] > ($v*1024) )
				    {
					    $this->produce_error( 'dl_restrict_daily_bw' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'weekly_bw' AND $weekly['bw'] )
			    {
				    if( $weekly['bw'] > ($v*1024) )
				    {
					    $this->produce_error( 'dl_restrict_weekly_bw' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'monthly_bw' AND $monthly['bw'] )
			    {
				    if( $monthly['bw'] > ($v*1024) )
				    {
					    $this->produce_error( 'dl_restrict_monthly_bw' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'daily_dl' AND $daily['dl'] )
			    {
				    if( $daily['dl'] > $v )
				    {
					    $this->produce_error( 'dl_restrict_daily_dl' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'weekly_dl' AND $weekly['dl'] )
			    {
				    if( $weekly['dl'] > $v )
				    {
					    $this->produce_error( 'dl_restrict_weekly_dl' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'monthly_dl' AND $monthly['dl'] )
			    {
				    if( $monthly['dl'] > $v )
				    {
					    $this->produce_error( 'dl_restrict_monthly_dl' );
					    $this->restricted = 1;
					    return;
				    }
			    }
			    
			    if( $k == 'limit_sim' )
			    {
				    $ip_extra = !$this->ipsclass->member['id'] ? " AND dsess_ip='{$this->ipsclass->ip_address}'" : '';
				    
				    $this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_sessions', 'where' => "dsess_mid={$this->ipsclass->member['id']}{$ip_extra}" ) );
				    $this->ipsclass->DB->exec_query();
				    
				    while( $r = $this->ipsclass->DB->fetch_row() )
				    {
					    $this->dl_sessions[] = $r;
				    }

				    $sess_count = 0;
				    
					if( count($this->dl_sessions) )
					{
						foreach( $this->dl_sessions as $session )
						{
							// If this is a request for the same file and the HTTP_RANGE header is sent don't count
							// It's probably a download manager.  If HTTP_RANGE isn't set, member is trying to download two copies simultaneously
							
							if( intval($this->ipsclass->input['id']) == $session['dsess_file'] AND $this->ipsclass->my_getenv('HTTP_RANGE') )
							{
								continue;
							}
							
							$sess_count++;
						}
					}
					
					if( $sess_count >= $v )
					{
					    $this->produce_error( 'dl_restrict_sim' );
					    $this->restricted = 1;
					    return;
				    }
			    }
		    }
	    }
    }
	
    
    /*-------------------------------------------------------------------------*/
    // Download a local file
    /*-------------------------------------------------------------------------*/
    
    function download_local_file( $file=array() )
    {
	    if( !is_array($file) OR !count($file) OR !$file['true_file'] )
	    {
		    return FALSE;
	    }
	    
	    if( !file_exists( $file['true_file'] ) )
	    {
		    return FALSE;
	    }
	    
	    if( $this->ipsclass->browser['browser'] == 'ie' )
	    {
		    // Multiple periods in filename in IE cause brackets - i.e. file[1].something.ext

		    $file['name'] = preg_replace('/\./', '%2e', $file['name'], substr_count( $file['name'], '.' ) - 1 );
	    }
	    
	    //------------------------------------------
	    // Set length and filesize to the total
	    //	size of the file.  Get range header
	    //------------------------------------------
	    
	    $filesize 	= $length = filesize( $file['true_file'] );
	    $range		= 0;
	    $range_head	= $this->ipsclass->my_getenv('HTTP_RANGE');

	    //------------------------------------------
	    // Send some cache headers
	    //------------------------------------------
	    
		//@header( "Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT" );
 		//@header( "Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT" );
 		//@header( "Pragma: " );
 		//@header( "Cache-Control: public" );

	    //------------------------------------------
	    // Yup, we accept ranges
	    //------------------------------------------
	    
 		@header("Accept-Ranges: bytes");

	    //------------------------------------------
	    // Requesting a range?
	    //------------------------------------------
	    
		if( $range_head ) 
		{
		    //------------------------------------------
		    // Drop "bytes=" and grab actual range
		    //------------------------------------------

			list( $a, $range ) 					= explode( "=", $range_head );
			
		    //------------------------------------------
		    // Check for multiple ranges
		    //------------------------------------------
		    
			$ranges								= explode( ",", $range );
			
		    //------------------------------------------
		    // No multiple ranges?
		    //------------------------------------------
		    
			if( count($ranges) == 1 )
			{
			    //------------------------------------------
			    // Get start and end range request
			    //------------------------------------------
		    
				list( $start_range, $end_range ) 	= explode( "-", $range );
	
			    //------------------------------------------
			    // No start (last x bytes of file)
			    //------------------------------------------
			    
				if( !$start_range )
				{
					$size	= $filesize - 1;
					$length = $end_range + 1;
				}
				
			    //------------------------------------------
			    // No end (first x bytes of file)
			    //------------------------------------------
			    
				else if( !$end_range )
				{
					$size	= $filesize - 1;
					$length	= $size - $start_range + 1;
				}
				
			    //------------------------------------------
			    // Start + End = Specific range
			    //------------------------------------------
			    
				else
				{
					$size	= $end_range;
					$length	= $end_range - $start_range + 1;
				}
	
			    //------------------------------------------
			    // Is range invalid?  If so, send 416 header
			    //	and tell client this is full range
			    //------------------------------------------
			    
				if( $start_range > $size OR $end_range > $size )
				{
					$length = $filesize;

					@header( "Status: 416 Requested Range Not Satisfiable" );
					@header( "Content-Range: */{$filesize}" );
					exit;
				}
				
			    //------------------------------------------
			    // Otherwise send 206 and indicate range
			    //------------------------------------------
			    
				else
				{
					@header( "HTTP/1.1 206 Partial Content" );
					@header( "Content-Range: bytes {$start_range}-{$size}/{$filesize}" );
				}
				
			    //------------------------------------------
			    // Common headers
			    //------------------------------------------
			    
				@header( "Content-Length: {$length}" );			
				@header( "Content-Transfer-Encoding: binary" );
			}
			
		    //------------------------------------------
		    // Multiple ranges...
		    //------------------------------------------
	    
			else
			{
			    //------------------------------------------
			    // Init markers and start looping
			    //------------------------------------------
			    
				$the_responses		= array();
				
				foreach( $ranges as $arange )
				{
				    //------------------------------------------
				    // Get start and end range request
				    //------------------------------------------
			    
					list( $start_range, $end_range ) 	= explode( "-", $arange );
					
				    //------------------------------------------
				    // No start (last x bytes of file)
				    //------------------------------------------

					if( !$start_range )
					{
						$size	= $filesize - 1;
						$length = $end_range + 1;
					}
					
				    //------------------------------------------
				    // No end (first x bytes of file)
				    //------------------------------------------
			    
					else if( !$end_range )
					{
						$size	= $filesize - 1;
						$length	= $size - $start_range + 1;
					}
					
				    //------------------------------------------
				    // Start + End = Specific range
				    //------------------------------------------
			    
					else
					{
						$size	= $end_range;
						$length	= $end_range - $start_range + 1;
					}

				    //------------------------------------------
				    // Is range invalid?  If so, send 416 header
				    //	and tell client this is full range.  Also
				    //	stop looping, since we can't send multipart
				    //------------------------------------------

					if( $start_range > $size OR $end_range > $size )
					{
						$length 		= $filesize;
						$the_responses	= array();
	
						@header( "Status: 416 Requested Range Not Satisfiable" );
						@header( "Content-Range: */{$filesize}" );
						exit;
					}

				    //------------------------------------------
				    // Otherwise store this particular range
				    //------------------------------------------

					else
					{
						$the_responses[] 	= array( $start_range, $size, $length );
					}
				}

			    //------------------------------------------
			    // Only one range from the multiple request?
			    //------------------------------------------
			    
			    if( count($the_responses) == 1 )
			    {
				    $length			= $the_responses[0][2];
				    $start_range	= $the_responses[0][0];
				    $size			= $the_responses[0][1];
				    
				    $the_responses 	= array();
				    
					@header( "HTTP/1.1 206 Partial Content" );
					@header( "Content-Range: bytes {$start_range}-{$size}/{$filesize}" );
					@header( "Content-Length: {$length}" );			
					@header( "Content-Transfer-Encoding: binary" );
				}
				    
			    //------------------------------------------
			    // Multiple ranges - send proper headers
			    //------------------------------------------
			    
				else if( count($the_responses) > 1 )
				{
					$content_length	= 0;
					
					foreach( $the_responses as $part )
					{
						$content_length	+= strlen( "\r\n--IPDOWNLOADSBOUNDARYMARKER\r\n" );
						$content_length	+= strlen( "Content-Type: " . $file['mimetype'] . "\r\n" );
						$content_length	+= strlen( "Content-Range: bytes {$part[0]}-{$part[1]}/{$filesize}\r\n\r\n" );
						$content_length	+= $part[2];
					}
					
					$content_length	+= strlen( "\r\n--IPDOWNLOADSBOUNDARYMARKER--\r\n" );
					
					@header( "HTTP/1.1 206 Partial Content" );
					@header( "Content-Type: multipart/x-byteranges; boundary=IPDOWNLOADSBOUNDARYMARKER" );
					@header( "Content-Transfer-Encoding: binary" );
					@header( "Content-Length: " . $content_length );
				}
			}
		}
		
	    //------------------------------------------
	    // Not requesting a range - send entire file
	    //------------------------------------------
	    
		else
		{
			$size = $filesize - 1;

			@header( "HTTP/1.1 200 OK" );
			@header( "Content-Length: {$length}" );
			@header( "Content-Range: bytes 0-{$size}/{$filesize}" );
			@header( "Content-Transfer-Encoding: binary" );
		}
		
	    //------------------------------------------
	    // Not requesting multiple ranges, set
	    //	filetype and disposition properly
	    //------------------------------------------
	    
		if( !count($the_responses) )
		{
			@header( "Content-Type: ".$file['mimetype'] );
			@header( "Content-Disposition: {$file['disposition']}; filename=\"".$file['name']."\"" );
		}

		//-----------------------------------------
		// Clean output buffer
		//-----------------------------------------
		
		@ob_end_clean();
		
	    //------------------------------------------
	    // Open file for reading
	    //------------------------------------------
	    
		if( $fh = fopen( $file['true_file'], 'rb' ) )
		{
		    //------------------------------------------
		    // Multiple ranges?  If so, we need to grab
		    //	just those ranges - no need to loop on
		    //	feof, since we may not even hit it
		    //------------------------------------------
		    
		    if( count($the_responses) )
		    {
			    foreach( $the_responses as $part )
			    {
				    $length = $part[2];

				    echo "\r\n--IPDOWNLOADSBOUNDARYMARKER\r\n";
				    echo "Content-Type: ".$file['mimetype'] . "\r\n";
				    echo "Content-Range: bytes {$part[0]}-{$part[1]}/{$filesize}\r\n\r\n";
				    
				    fseek( $fh, $part[0] );
				    
					while( $part[2] AND !feof($fh) )
					{		
						$read 	 = $part[2] > 4096 ? 4096 : $part[2];
						$part[2] -= $read;
						
					    //------------------------------------------
					    // Output that..
					    //------------------------------------------
			    
						echo fread( $fh, $read );
						
						flush();
						@ob_flush();
					}
				}

				echo "\r\n--IPDOWNLOADSBOUNDARYMARKER--\r\n";
				
				flush();
				@ob_flush();
			}
			
		    //------------------------------------------
		    // Single range (or entire file)
		    //------------------------------------------
		    
			else
			{
			    //------------------------------------------
			    // Jump to start point
			    //------------------------------------------
		    
				fseek( $fh, $range );

			    //------------------------------------------
			    // Loop while reading file
			    //------------------------------------------
			    
				while( !feof($fh) )
				{
				    //------------------------------------------
				    // Only grabbing a portion of the file?
				    //------------------------------------------
	
					if( $length < $filesize )
					{
					    //------------------------------------------
					    // We will read either the rest of the file,
					    //	or 4096 bytes, whichever is smaller
					    //	Then we remove that amount from length
					    //	remaining for next loop.
					    //------------------------------------------
	
						$read 	 = $length > 4096 ? 4096 : $length;
						$length	-= $read;
						
					    //------------------------------------------
					    // Output that..
					    //------------------------------------------
			    
						echo fread( $fh, $read );
						
					    //------------------------------------------
					    // And are we done now?  If this was a range
					    //	request it's possible we'll never hit
					    //	eof otherwise, so output and quite loop
					    //------------------------------------------
					    
						if( $length == 0 )
						{
							flush();
							@ob_flush();
							break;
						}
					}
					
				    //------------------------------------------
				    // Entire file, loop until it's all printed
				    //------------------------------------------
				    
					else
					{
						echo fread( $fh, 4096 );
					}
	
					flush();
					@ob_flush();
				}
			}
			
		    //------------------------------------------
		    // Close file pointer - we're done
		    //------------------------------------------
		    
			@fclose( $fh );
			
			return true;
		}
		
		return false;
    }

    /*-------------------------------------------------------------------------*/
    // Produce Internal error message
    /*-------------------------------------------------------------------------*/
    
    function produce_error( $lang_bit="", $text_already=0 )
    {
	    $message = "";
	    
	    if( $text_already == 1 )
	    {
		    if( !$lang_bit )
		    {
			    $message = $this->ipsclass->lang['generic_error'];
		    }
		    else
		    {
			    $message = $lang_bit;
		    }
	    }
	    else if( !$lang_bit )
	    {
		    $message = $this->ipsclass->lang['generic_error'];
	    }
	    else if( ! array_key_exists( $lang_bit, $this->ipsclass->lang ) )
	    {
		    $message = $this->ipsclass->lang['generic_error'];
	    }
	    else
	    {
		    $message = $this->ipsclass->lang[ $lang_bit ];
	    }
	    
	    $this->output .= $this->ipsclass->compiled_templates['skin_downloads']->error_box( $message );
	    return;
    }
	
	

}

?>