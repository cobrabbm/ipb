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
|   > Moderate Script - mod libraries
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

class idm_moderate
{
	var $ipsclass;
	var $catlib;
	var $modfunc;
	var $funcs;
	
	var $func_mod;
	var $post;
	var $email;
	var $han_editor;
	var $parser;
	var $msglib;
	var $classes_loaded = 0;
	var $send_msg		= 0;
	
	var $message		= "";
	
	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";

    /*-------------------------------------------------------------------------*/
	// Our run_me function
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
	    // Global navigation bar/title
	    $this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=downloads'>".$this->ipsclass->lang['idm_navbar'].'</a>';
		$this->page_title = $this->ipsclass->vars['board_name']." -> ".$this->ipsclass->lang['idm_pagetitle'];
		
		$pass = 0;
		
		// Can we see any categories?
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_downloads_permissions' );
		}
		else if( $this->ipsclass->member['g_is_supmod'] == 1 )
		{
			$pass = 1;
		}
		else
		{
			if( $this->ipsclass->input['code'] == 'broken' OR ( ( $this->ipsclass->input['code'] == 'delete' OR $this->ipsclass->input['code'] == 'versions' ) AND $this->ipsclass->vars['idm_allow_delete'] ) )
			{
				$pass = 1;
			}
			else
			{
				if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								$pass = 1;
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								$pass = 1;
							}
						}
					}
				}
			}
		}
		
		if( $pass = 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
		}
		else
		{
			$canadd = ( count( $this->catlib->member_access['add'] ) > 0 ) ? 1 : 0;
						
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $canadd, 1 );
			
			switch( $this->ipsclass->input['code'] )
			{
				case 'togglefile':
					$this->do_togglefile( );
					break;
					
				case 'broken':
					$this->report_broken( );
					break;
					
				case 'notbroken':
					$this->report_unbroken( );
					break;					
				
				case 'delete':
					$this->do_delete( );
					break;
					
				case 'modaction':
					$this->do_mod_action( );
					break;
					
				case 'versions':
					$this->version_control();
					break;
					
				case 'commentMMOD':
					$this->comment_multimod();
					break;
					
				case 'multimod':
					$this->file_multimod();
					break;
					
				default:
					$this->display_panel( );
					break;
			}
		}
		
		// Print the output
    	$this->ipsclass->print->add_output( $this->output );
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'NAV' => $this->nav ) );
	}
	
    /*-------------------------------------------------------------------------*/
    // File multi-moderation
    /*-------------------------------------------------------------------------*/	
	
	function file_multimod()
	{
		$ids = $this->ipsclass->clean_int_array( explode( ',', $this->ipsclass->input['selectedfileids'] ) );
		$cat = intval($this->ipsclass->input['catid']);
		
		if( !is_array($ids) OR !count($ids) )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		if( !$cat )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		if( $this->ipsclass->input['doaction'] == 'move' AND !$this->ipsclass->input['moveto'] )
		{
			$categories 	= $this->catlib->cat_jump_list( 1, 'view' );
			$category_opts	= '';
			
			if( count($categories) )
			{
				foreach( $categories as $cat )
				{
					$canapp = 0;
					
					if( $this->ipsclass->member['g_is_supmod'] )
					{
						$canapp = 1;
					}
					else
					{
						if( is_array( $this->catlib->cat_mods[ $cat[0] ] ) )
						{
							if( count($this->catlib->cat_mods[ $cat[0] ]) )
							{
								foreach( $this->catlib->cat_mods[ $cat[0] ] as $k => $v )
								{
									if( $k == "m".$this->ipsclass->member['id'] )
									{
										if( $v['modcanapp'] )
										{
											$canapp = 1;
										}
									}
									else if( $k == "g".$this->ipsclass->member['mgroup'] )
									{
										if( $v['modcanapp'] )
										{
											$canapp = 1;
										}
									}
								}
							}
						}
					}
					
					if( !$canapp )
					{
						continue;
					}

					$category_opts .= "<option value='{$cat[0]}'>{$cat[1]}</option>\n";
				}
			}

			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->get_moveto_cat( $category_opts );
			return;
		}
		else if( $this->ipsclass->input['doaction'] == 'move' )
		{
			$category = $this->catlib->cat_lookup[ intval($this->ipsclass->input['moveto']) ];
			
			if( !$category['cid'] )
			{
				$this->output .= $this->funcs->produce_error( 'error_generic' );
				return;
			}
		}
				
				
		$fids 		= array();
		
		$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => 'file_id IN(' . implode( ',', $ids ) . ') AND file_cat=' . $cat ) );
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			switch( $this->ipsclass->input['doaction'] )
			{
				case 'del':
					$canmod = $this->funcs->checkPerms( $r, 'modcandel', 'idm_allow_delete' );
				break;
				
				case 'app':
				case 'unapp':
					$canmod = $this->funcs->checkPerms( $r, 'modcanapp' );
				break;
				
				case 'move':
					$canmod = $this->funcs->checkPerms( $r, 'modcanapp', 'idm_allow_edit' );
				break;
			}
			
			if( $canmod )
			{
				$fids[ $r['file_id'] ] = $r['file_id'];
			}
		}
		
		if( !count($fids) )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		switch( $this->ipsclass->input['doaction'] )
		{
			case 'del':
				$this->do_multi_delete( $fids );
			break;
			
			case 'app':
				$this->do_multi_approve( $fids );
			break;
			
			case 'unapp':
				$this->do_multi_unapprove( $fids );
			break;
			
			case 'move':
				$this->do_multi_move( $fids, intval($this->ipsclass->input['moveto']) );
				$this->catlib->rebuild_fileinfo( intval($this->ipsclass->input['moveto']) );
			break;
		}
		
		$this->ipsclass->my_setcookie('modfileids', '', 0);

		$this->catlib->rebuild_fileinfo( $cat );
		$this->catlib->rebuild_cat_cache();
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['file_mmod_success'], "autocom=downloads&amp;showcat={$cat}" );
	}
	
    /*-------------------------------------------------------------------------*/
    // Comment multi-moderation
    /*-------------------------------------------------------------------------*/	
	
	function comment_multimod()
	{
		$ids = $this->ipsclass->clean_int_array( explode( ',', $this->ipsclass->input['selectedpids'] ) );
		$fid = intval($this->ipsclass->input['id']);
		
		if( !is_array($ids) OR !count($ids) )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		if( !$fid )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		$pids 		= array();
		
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'c.*',
												 'from'		=> array( 'downloads_comments' => 'c' ),
												 'where'	=> 'c.comment_id IN( ' . implode( ',', $ids ) . ') AND c.comment_fid=' . $fid,
												 'add_join'	=> array(
												 					array( 'select'	=> 'f.file_cat, f.file_id, f.file_name',
												 							'from'	=> array( 'downloads_files' => 'f' ),
												 							'where'	=> 'f.file_id=c.comment_fid',
												 							'type'	=> 'left'
												 						)
												 					)
										)		);
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$canmod = $this->funcs->checkPerms( $r, 'modcancomments' );
			
			if( $canmod )
			{
				$pids[ $r['comment_id'] ] = $r['comment_id'];
			}
		}
		
		if( !count($pids) )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		switch( $this->ipsclass->input['idmact'] )
		{
			case 'delcomments':
				$this->ipsclass->DB->do_delete( 'downloads_comments', 'comment_id IN(' . implode( ',', $pids ) . ')' );
			break;
			
			case 'approvecomments':
				$this->ipsclass->DB->do_update( 'downloads_comments', array( 'comment_open' => 1 ), 'comment_id IN(' . implode( ',', $pids ) . ')' );
			break;
			
			case 'unapprovecomments':
				$this->ipsclass->DB->do_update( 'downloads_comments', array( 'comment_open' => 0 ), 'comment_id IN(' . implode( ',', $pids ) . ')' );
			break;
		}
		
		$this->ipsclass->my_setcookie('idmmodpids', '', 0);

		$this->funcs->rebuild_pend_comment_cnt( $fid );
		
		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_mmod_success'], "autocom=downloads&req=comments&code=pop_com&file={$fid}" );
		}
		else
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_mmod_success'], "autocom=downloads&amp;showfile={$fid}" );
		}
	}
	
    /*-------------------------------------------------------------------------*/
    // Version Control API Gateway
    /*-------------------------------------------------------------------------*/	
	
    function version_control()
    {
	    // File ID
	    
	    $id		= intval($this->ipsclass->input['id']);
	    $vid	= intval($this->ipsclass->input['rid']);
	    
		if( !$id )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}
		
		if( !$vid )
		{
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}	
	    
	    $file 	= $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => 'file_id='.$id ) );
	    $ver 	= $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => 'b_fileid='.$id.' AND b_id='.$vid ) );
	    
	    if( !$file['file_id'] OR !$ver['b_id'] )
	    {
			$this->output .= $this->funcs->produce_error( 'error_generic' );
			return;
		}		    
		    
		$candelete = $this->funcs->checkPerms( $file, 'modcandel', 'idm_allow_delete' );
		
		if( !$candelete )
		{
			$this->output .= $this->funcs->produce_error( 'not_your_file' );
			return;
		}
		
		// Still here?  Permissions Groovy?  Let's go then!
		
		require_once( DL_PATH.'lib/lib_versioning.php' );
		
		$versions 				= new lib_versioning();
		$versions->ipsclass 	=& $this->ipsclass;
		
		$versions->file_id 		= $file['file_id'];
		$versions->file_data	= $file;
		
		$versions->init();
		
		$text = "";
						
		switch( $this->ipsclass->input['do'] )
		{
			case 'restore':
				$versions->restore( $vid );
				$text = 'version_restore_succesful';
				break;
				
			case 'hide':
				$versions->hide( $vid );
				$text = 'version_hide_succesful';
				break;
				
			case 'unhide':
				$versions->unhide( $vid );
				$text = 'version_unhide_succesful';
				break;
				
			case 'delete':
				$versions->remove( $vid, $ver, $current );
				$text = 'version_remove_succesful';
				break;
		}
		
		if( $versions->error )
		{
			$this->output .= $this->funcs->produce_error( $this->ipsclass->lang[ $versions->error ] );
			return;
		}
		else
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang[ $text ], "autocom=downloads&amp;showfile={$id}" );
		}
	}    
	
	
    /*-------------------------------------------------------------------------*/
    // Main moderator action processing
    /*-------------------------------------------------------------------------*/
    	
	function do_mod_action( )
	{
		$this->message = "";
		
		if( $this->ipsclass->member['g_is_supmod'] )
		{
			$canapp   	= 1;
			$canbroke 	= 1;
			$appcats 	= '*';
			$brokecats 	= '*';
		}
		else
		{
			if( is_array( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] ) )
			{
				if( count($this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ]) )
				{
					foreach( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] as $k => $v )
					{
						if( $v['modcanapp'] )
						{
							$canapp = 1;
							$appcats = $v['modcats'];
						}
						if( $v['modcanbrok'] )
						{
							$canbroke = 1;
							$brokecats = $v['modcats'];
						}
					}
				}
			}
			else if( is_array( $this->catlib->mem_mods[ $this->ipsclass->member['id'] ] ) )
			{
				if( count($this->catlib->mem_mods[ $this->ipsclass->member['id'] ]) )
				{
					foreach( $this->catlib->mem_mods[$this->ipsclass->member['id'] ] as $k => $v )
					{
						if( $v['modcanapp'] )
						{
							$canapp = 1;
							$appcats = $v['modcats'];
						}
						if( $v['modcanbrok'] )
						{
							$canbroke = 1;
							$brokecats = $v['modcats'];
						}
					}
				}
			}
		}
		
		if( $canapp == 0 AND $canbroke == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			$this->display_panel();
			return;
		}
		
		$ids = array();
		$temp = explode( ',', $this->ipsclass->clean_perm_string( $this->ipsclass->input['selectedfileids'] ) );
 		
 		foreach( $temp as $id )
 		{
			$ids[ intval($id) ] = $id;
 		}
 		
 		switch( $this->ipsclass->input['type'] )
 		{
	 		case 'app':
	 			if( $canapp == 0 )
	 			{
					$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
					$this->display_panel();
					return;
				}
				
	 			if( $this->ipsclass->input['doaction'] == 'del' )
	 			{
		 			$this->send_msg = 1;
		 			$cnt = $this->do_multi_delete( $ids );
		 			$this->catlib->rebuild_fileinfo('all');
					$this->catlib->rebuild_stats_cache();
		 			$this->message .= sprintf( $this->ipsclass->lang['modact_message_del'], $cnt );
		 			$this->display_panel();
		 			return;
	 			}
	 			else if( $this->ipsclass->input['doaction'] == 'app' )
	 			{
		 			$cnt = $this->do_multi_approve( $ids );
		 			$this->catlib->rebuild_fileinfo('all');
					$this->catlib->rebuild_stats_cache();		 			
		 			$this->message .= sprintf( $this->ipsclass->lang['modact_message_app'], $cnt );
		 			$this->display_panel();
		 			return;
	 			}
	 			else
	 			{
		 			$this->message = $this->ipsclass->lang['modact_message_huh'];
		 			$this->display_panel();
		 			return;
	 			}
	 			break;
	 		case 'broke':
	 			if( $canbroke == 0 )
	 			{
					$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
					$this->display_panel();
					return;
				}
					 		
	 			if( $this->ipsclass->input['doaction'] == 'del' )
	 			{
		 			$cnt = $this->do_multi_delete( $ids );
		 			$this->catlib->rebuild_fileinfo('all');
					$this->catlib->rebuild_stats_cache();		 			
		 			$this->message .= sprintf( $this->ipsclass->lang['modact_message_del'], $cnt );
		 			$this->display_panel();
		 			return;
	 			}
	 			else if( $this->ipsclass->input['doaction'] == 'rem' )
	 			{
		 			$cnt = $this->do_multi_unbroke( $ids );
		 			$this->message .= sprintf( $this->ipsclass->lang['modact_message_br'], $cnt );
		 			$this->display_panel();
		 			return;
	 			}
	 			else
	 			{
		 			$this->message = $this->ipsclass->lang['modact_message_huh'];
		 			$this->display_panel();
		 			return;
	 			}
	 			break;
	 		default:
		 		$this->message = $this->ipsclass->lang['modact_message_huh'];
		 		$this->display_panel();
		 		return;
		 		break;
	 	}
	 	
	 	// Are we still here?  We shouldn't be...
 		$this->message = $this->ipsclass->lang['modact_message_huh'];
 		$this->display_panel();
	}	
	
    /*-------------------------------------------------------------------------*/
    // Main moderators panel
    /*-------------------------------------------------------------------------*/
    	
	function display_panel( )
	{
		if( $this->ipsclass->member['g_is_supmod'] )
		{
			$canapp   	= 1;
			$canbroke 	= 1;
			$appcats 	= '*';
			$brokecats 	= '*';
		}
		else
		{
			if( is_array( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] ) )
			{
				if( count($this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ]) )
				{
					foreach( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] as $k => $v )
					{
						if( $v['modcanapp'] )
						{
							$canapp = 1;
							$appcats = $v['modcats'];
						}
						if( $v['modcanbrok'] )
						{
							$canbroke = 1;
							$brokecats = $v['modcats'];
						}
					}
				}
			}
			else if( is_array( $this->catlib->mem_mods[ $this->ipsclass->member['id'] ] ) )
			{
				if( count($this->catlib->mem_mods[ $this->ipsclass->member['id'] ]) )
				{
					foreach( $this->catlib->mem_mods[$this->ipsclass->member['id'] ] as $k => $v )
					{
						if( $v['modcanapp'] )
						{
							$canapp = 1;
							$appcats = $v['modcats'];
						}
						if( $v['modcanbrok'] )
						{
							$canbroke = 1;
							$brokecats = $v['modcats'];
						}
					}
				}
			}
		}
		
		if( $canapp == 0 AND $canbroke == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			return;
		}
		
		if( $this->message )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->message_box( $this->message );
		}
		
		//----------------------------------
		// Get Files Pending Approval
		//----------------------------------
		
		if( $appcats == '*' )
		{
			$limiter = '';
		}
		else
		{
			$limiter = "AND f.file_cat IN({$appcats})";
		}
		
		$results = array();
		
		if( $canapp AND $canbroke )
		{
			$this->ipsclass->DB->cache_add_query( 'mod_get_both', array( 'limiter' => $limiter	), 'sql_idm_queries'	);
			$this->ipsclass->DB->cache_exec_query();
			
			while( $row = $this->ipsclass->DB->fetch_row() )
			{
				if( $row['file_broken'] == 1 )
				{
					$results['broken'][] = $row;
				}
				
				if( $row['file_open'] == 0 )
				{
					$results['open'][] = $row;
				}
			}
		}
		else if( $canapp )
		{
			$this->ipsclass->DB->cache_add_query( 'mod_get_pending', array( 'limiter' => $limiter	), 'sql_idm_queries'	);
			$this->ipsclass->DB->cache_exec_query();
			
			while( $row = $this->ipsclass->DB->fetch_row() )
			{
				$results['open'][] = $row;
			}			
		}
		else if( $canbroke )
		{
			$this->ipsclass->DB->cache_add_query( 'mod_get_broken', array( 'limiter' => $limiter	), 'sql_idm_queries'	);
			$this->ipsclass->DB->cache_exec_query();
			
			while( $row = $this->ipsclass->DB->fetch_row() )
			{
				$results['broken'][] = $row;
			}
		}			
		
		if( $canapp )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_top( $this->ipsclass->lang['moderate_pend_top'], '', 1, 'modform', 'app' );
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_thbar( 1 );
		
			if( count($results['open']) )
			{
				foreach( $results['open'] as $k => $row )
				{
					$row['css'] = 'row2shaded';
					$row['un']	= 'un';
					
					$row['file_submitteddis'] = $this->ipsclass->get_date( $row['file_submitted'], 'LONG' );
					if( $row['file_updated'] > $row['file_submitted'] )
					{
						$row['file_updateddis'] = "<strong>".$this->ipsclass->lang['catdis_updated']."</strong>".$this->ipsclass->get_date( $row['file_updated'], 'LONG' );
					}
					else
					{
						$row['file_updateddis'] = "";
					}
					
					$row['submitter'] = $this->ipsclass->make_profile_link( $row['members_display_name'], $row['file_submitter'] );
					$row['filename']  = $this->ipsclass->compiled_templates['skin_downloads']->file_link( $row['file_name'], $row['file_id'] );
					
					$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_row_withoutss( $row, 1 );				
				}
			}
			else
			{
				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_empty(1);
			}
			
			$option = "<option value='app'>{$this->ipsclass->lang['modact_approve']}</option>";
			
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_bottom( '', 1, $option );
		}
		
		if( $canbroke )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_top( $this->ipsclass->lang['moderate_broke_top'], '', 1, 'brokeform', 'broke' );
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_thbar( 1, 'brokeform' );
		
			if( count($results['broken']) )
			{
				foreach( $results['broken'] as $k => $row )
				{
					$row['css'] = $row['file_open'] == 1 ? 'row2' : 'row2shaded';
					$row['un']	= 'un';
					
					$row['file_submitteddis'] = $this->ipsclass->get_date( $row['file_submitted'], 'LONG' );
					if( $row['file_updated'] > $row['file_submitted'] )
					{
						$row['file_updateddis'] = "<strong>".$this->ipsclass->lang['catdis_updated']."</strong>".$this->ipsclass->get_date( $row['file_updated'], 'LONG' );
					}
					else
					{
						$row['file_updateddis'] = "";
					}
					
					$row['submitter'] = $this->ipsclass->make_profile_link( $row['members_display_name'], $row['file_submitter'] );
					$row['filename']  = $this->ipsclass->compiled_templates['skin_downloads']->file_link( $row['file_name'], $row['file_id'] );
					
					$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_row_withoutss( $row, 1, 'brokeform' );				
				}
			}
			else
			{
				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_empty(1);
			}
			
			$option = "<option value='rem'>{$this->ipsclass->lang['modact_rembroke']}</option>";
			
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_bottom( '', 1, $option );
		}		
		
		$this->nav[] = $this->ipsclass->lang['moderate_nav'];
		
		$this->page_title .= " -> {$this->ipsclass->lang['moderate_nav']}";
		
		return;
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Do approve/unapprove
    /*-------------------------------------------------------------------------*/
    
    function do_togglefile( )
    {
		$id = intval($this->ipsclass->input['id']);
		
		if( !$id )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_toggle' );
			$this->display_panel();
			return;
		}			
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																  'from'	=> 'downloads_files',
																  'where'	=> 'file_id='.$id
														)		);
		
		if( !$file['file_id'] )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_toggle' );
			$this->display_panel();
			return;
		}
		
		$cantog	= $this->funcs->checkPerms( $file, 'modcanapp' );
		
		if( $cantog == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			$this->display_panel();
			return;
		}
		
		$to_update = array();
		
		if( $file['file_open'] == 0 )
		{
			$this->do_multi_approve( array( $id => $id ) );
			$text = $this->ipsclass->lang['moderate_approve'];
		}
		else
		{
			$to_update['file_open'] = 0;
			$this->ipsclass->DB->do_update( "downloads_files", $to_update, "file_id=".$id );
			$text = $this->ipsclass->lang['moderate_unapprove'];
		}
		
		$this->catlib->rebuild_fileinfo($file['file_cat']);
		$this->catlib->rebuild_stats_cache();
		
		if( $this->ipsclass->input['return'] == 1 )
		{
			$this->ipsclass->print->redirect_screen( $text, "autocom=downloads&amp;showfile={$id}" );
		}
		else
		{
			$this->message = $text;
			$this->display_panel();
		}		
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Report Broken File
    /*-------------------------------------------------------------------------*/
    
    function report_broken( )
    {
		$id = intval($this->ipsclass->input['id']);
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																  'from'	=> 'downloads_files',
																  'where'	=> 'file_id='.$id
														)		);
		
		if( !$file['file_id'] )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_report' );
			$this->display_panel();
			return;
		}
		
		//-----------------------------------------
		// Load and config the std/rte editors
		//-----------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_editor.php" );
		$han_editor = new han_editor();
		$han_editor->ipsclass =& $this->ipsclass;
		$han_editor->init();
		
		//-----------------------------------------
		// Load and config the post parser
		//-----------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
		$parser = new parse_bbcode();
		$parser->ipsclass =& $this->ipsclass;
		$parser->allow_update_caches = 1;
		$parser->bypass_badwords = intval($this->ipsclass->member['g_bypass_badwords']);
		$parser->parse_bbcode = 1;
		$parser->parse_smilies = 1;			
		
		if( !$this->ipsclass->input['do_broken'] )
		{
			$this->ipsclass->load_template( 'skin_downloads_submit' );
			
			$editor_html = $han_editor->show_editor( '', 'Post' );
					
			$this->output = $this->ipsclass->compiled_templates['skin_downloads_submit']->broken_file_reason( $file );
			
			$this->output = str_replace( "<!--BROKEN_EDITOR-->", $editor_html, $this->output );
			return;
		}
		else
		{
			$reason = "";
			
			if( $han_editor->method == 'rte' )
			{
				$_POST['Post'] = $parser->convert_ipb_html_to_html( $_POST['Post'] );
			}

			// Fully formatted and ready to insert
			$reason = $parser->pre_db_parse( $han_editor->process_raw_post( 'Post' ) );			
		
			$this->ipsclass->DB->do_update( "downloads_files", array( 'file_broken' => 1, 'file_broken_reason' => $reason, 'file_broken_info' => $this->ipsclass->member['id'] . '|' . $this->ipsclass->member['members_display_name'] . '|' . time() ), "file_id=".$id );
		}
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['moderate_broken'], "autocom=downloads&amp;showfile={$id}" );
	}
	
	
    function report_unbroken( )
    {
		$id = intval($this->ipsclass->input['id']);
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																  'from'	=> 'downloads_files',
																  'where'	=> 'file_id='.$id
														)		);
		
		if( !$file['file_id'] )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_unreport' );
			$this->display_panel();
			return;
		}
		
		$cando	= $this->funcs->checkPerms( $file, 'modcanbrok' );
		
		if( $cando == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_unreport' );
			$this->display_panel();
			return;
		}		
		
		$this->ipsclass->DB->do_update( "downloads_files", array( 'file_broken' => 0, 'file_broken_reason' => '' ), "file_id=".$id );
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['moderate_unbroken'], "autocom=downloads&amp;showfile={$id}" );
	}		
		
	
    /*-------------------------------------------------------------------------*/
    // Do Delete
    /*-------------------------------------------------------------------------*/
    	
	function do_delete( )
	{
		$id = intval($this->ipsclass->input['id']);
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'		=> 'f.*',
																  'from'		=> array( 'downloads_files' => 'f' ),
																  'where'		=> 'f.file_id=' . $id,
																  'add_join'	=> array(
																  						array( 'select'	=> 'm.id',
																  								'from'	=> array( 'members' => 'm' ),
																  								'where'	=> 'm.id=f.file_submitter',
																  								'type'	=> 'left'
																  							)
																  						)
														)		);
		
		if( !$file['file_id'] )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_find_to_del' );
			$this->display_panel();
			return;
		}
		
		$candelete = $this->funcs->checkPerms( $file, 'modcandel', 'idm_allow_delete' );

		if( $candelete == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'cannot_delete' );
			$this->display_panel();
			return;
		}		
		
		//-----------------------------------------
		// Delete the files from the server(s)
		//-----------------------------------------
		
		switch( $file['file_storagetype'] )
		{
			case 'web':
			case 'nonweb':
				@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $file['file_filename'] );
				if( $file['file_ssname'] )
				{
					@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_ssname'] );
					if( $file['file_thumb'] )
					{
						@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_thumb'] );
					}
				}
				
				break;
			case 'ftp':
				if( $this->ipsclass->vars['idm_remoteurl'] AND
					$this->ipsclass->vars['idm_remoteport'] AND
					$this->ipsclass->vars['idm_remoteuser'] AND
					$this->ipsclass->vars['idm_remotepass'] AND
					$this->ipsclass->vars['idm_remotefilepath'] )
				{
					$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 2000 );
					if( !$conid )
					{
						$this->output .= $this->funcs->produce_error( 'addfile_ftp_error1' );
						$this->display_panel( );
						return;
					}
					$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
					if( !$check )
					{
						$this->output .= $this->funcs->produce_error( 'addfile_ftp_error1' );
						$this->display_panel( );
						return;
					}
					
					@ftp_delete( $conid, $this->ipsclass->vars['idm_remotefilepath']."/".$file['file_filename'] );

					if( $file['file_ssname'] )
					{
						if( $this->ipsclass->vars['idm_remotesspath'] )
						{
							@ftp_delete( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_ssname'] );
							
							if( $file['file_thumb'] )
							{
								@ftp_delete( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_thumb'] );
							}
						}
					}
				}
				break;
		}
		
		//-----------------------------------------
		// Delete from database tables
		//-----------------------------------------		
		
		$this->ipsclass->DB->do_delete( 'downloads_favorites', "ffid=".$id );
		$this->ipsclass->DB->do_delete( 'downloads_ccontent', "file_id=".$id );
		$this->ipsclass->DB->do_delete( 'downloads_comments', "comment_fid=".$id );
		$this->ipsclass->DB->do_delete( 'downloads_files', "file_id=".$id );
		$this->ipsclass->DB->do_delete( 'downloads_filestorage', "storage_id=".$id );
		$this->ipsclass->DB->do_delete( 'downloads_fileviews', "view_fid=".$id );
		
		$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => "b_fileid=".$id ) );
		$outer = $this->ipsclass->DB->exec_query();
		
		if( $this->ipsclass->DB->get_num_rows() )
		{
			require_once( DL_PATH.'lib/lib_versioning.php' );
			$versions 				= new lib_versioning();
			$versions->ipsclass 	=& $this->ipsclass;
			$versions->file_id 		= $id;
			$versions->file_data	= $file;
			$versions->init();
			
			while( $r = $this->ipsclass->DB->fetch_row($outer) )
			{
				$versions->remove( $r['b_id'], $r );
			}
		}

		//-----------------------------------------
		// Delete the topic if appropriate
		//-----------------------------------------		
	
		if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topice'] )
		{
			if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf'] )
			{
				if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicd'] )
				{
					$tid = $file['file_topicid'];
					
					if( $tid > 0 )
					{
						require_once( ROOT_PATH.'sources/lib/func_mod.php' );
				        $this->modfunc = new func_mod();
				        $this->modfunc->ipsclass =& $this->ipsclass;
				        
						$this->ipsclass->DB->simple_construct( array( 'select' => 'tid, forum_id', 'from' => 'topics', 'where' => "state='link' AND moved_to='".$tid.'&'.$this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf']."'" ) );
						$this->ipsclass->DB->simple_exec();
						
						if ( $linked_topic = $this->ipsclass->DB->fetch_row() )
						{
							$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'topics', 'where' => "tid=".$linked_topic['tid'] ) );
							$this->modfunc->topic_delete($linked_topic['tid']);
						}
						
						$forum_id = $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf'];
						
						$this->modfunc->init( $this->ipsclass->cache['forum_cache'][$forum_id] );
						$this->modfunc->topic_delete($tid);
						$this->modfunc->add_moderate_log($this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf'], $tid, '', $file['file_name'], "Deleted an IDM Auto-Generated topic");
					}
				}
			}
		}
		
		if( !$this->classes_loaded )
		{
			$this->load_classes();
		}
		
		if( $this->ipsclass->vars['idm_file_notification'] != 'n' && $file['file_new'] )
		{
			$mid = $file['file_submitter'];
			
			$this->ipsclass->DB->build_query( array( 'select' => 'members_display_name, email, view_pop, email_pm',
												 	 'from'   => 'members',
												 	 'where'  => 'id='.intval($mid)
											)		);
			$this->ipsclass->DB->exec_query();
			
			$author = $this->ipsclass->DB->fetch_row();
							
			//b=both,p=pm,e=email
			$message = sprintf( $this->ipsclass->lang['moderate_dennotify'],
								$author['members_display_name'],
								$file['file_name'] );
								
			$pm = 0;
			$email = 0;
			
			if( $this->ipsclass->vars['idm_file_notification'] == 'b' )
			{
				$pm = 1;
				$email = 1;
			}
			else if( $this->ipsclass->vars['idm_file_notification'] == 'p' )
			{
				$pm = 1;
			}
			else if( $this->ipsclass->vars['idm_file_notification'] == 'e' )
			{
				$email = 1;
			}
			
			if( $email == 1 AND $author['id'] )
			{
				$this->email->template = stripslashes($lang['header']) . $message . stripslashes($lang['footer']);
					
				$this->email->message = $this->email->template;
				        					
				$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] );
				$this->email->to      = $author['email'];
				$this->email->send_mail();
			}
			
			if( $pm == 1 AND $author['id'] )
			{
				$postkey = md5( microtime() );
				$show_popup = $author['view_pop'];
				
					$this->ipsclass->DB->do_insert( 'message_text', array(
													   'msg_date'	       => time(),
													   'msg_post'          => $this->ipsclass->remove_tags($message),
													   'msg_cc_users'      => '',
													   'msg_sent_to_count' => 1,
													   'msg_post_key'      => $postkey,
													   'msg_author_id'     => $this->ipsclass->member['id'],
													   'msg_ip_address'    => $this->ipsclass->ip_address
											  )      );
				$msg_id = $this->ipsclass->DB->get_insert_id();
				
				$this->ipsclass->DB->force_data_type = array( 'mt_title' => 'string' );
				
				$this->ipsclass->DB->do_insert( 'message_topics', array(
														 'mt_msg_id'     => $msg_id,
														 'mt_date'       => time(),
														 'mt_title'      => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] ),
														 'mt_from_id'    => $this->ipsclass->member['id'],
														 'mt_to_id'      => $file['file_submitter'],
														 'mt_vid_folder' => 'in',
														 'mt_tracking'   => 0,
														 'mt_addtosent'	 => 0,
														 'mt_hasattach'  => 0,
														 'mt_owner_id'   => $file['file_submitter'],
														 'mt_hide_cc'    => 0,
										       )      );
				$mt_id = $this->ipsclass->DB->get_insert_id();
																       
				$this->ipsclass->DB->build_query( array( 'select' => 'vdirs', 'from' => 'member_extra', 'where' => "id={$file['file_submitter']}" ) );
				$this->ipsclass->DB->exec_query();
				$vdirs = $this->ipsclass->DB->fetch_row();
				
				$inbox_count = $this->msglib->_get_dir_count( $vdirs['vdirs'], 'in' );
				
				$new_vdir = $this->msglib->rebuild_dir_count( $file['file_submitter'],
													  "",
													  'in',
													  $inbox_count + 1,
													  'save',
													  "msg_total=msg_total+1,new_msg=new_msg+1,show_popup={$show_popup}"
													);
																							
				//-----------------------------------------
				// Has this member requested a PM email nofity?
				//-----------------------------------------
				
				if ($author['email_pm'] == 1)
				{
					$this->email->get_template("pm_notify");
				
					$this->email->build_message( array(
														'NAME'   => $author['members_display_name'],
														'POSTER' => $this->ipsclass->member['members_display_name'],
														'TITLE'  => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] ),
														'LINK'   => "?act=Msg&CODE=03&VID=in&MSID={$mt_id}",
														)       );
												
					$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] );
					$this->email->to      = $author['email'];
					$this->email->send_mail();
				
				}
			}
		}
		
		$this->catlib->rebuild_fileinfo($file['file_cat']);
		$this->catlib->rebuild_stats_cache();
		
		if( $this->ipsclass->input['return'] == 1 )
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['moderate_filedeleted'], "autocom=downloads&amp;showcat={$file['file_cat']}" );
		}
		else
		{
			$this->message = $this->ipsclass->lang['moderate_filedeleted'];
			$this->display_panel();
		}
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Do multi-approve
    /*-------------------------------------------------------------------------*/
    
    function do_multi_approve( $ids=array() )
    {
		if( !is_array($ids) OR count($ids) < 1 )
		{
			return 0;
		}
		else
		{
			$num = 0;
			
			foreach( $ids as $id => $flag )
			{			    
				$this->ipsclass->DB->build_query( array( 'select'	=> '*',
														 'from'		=> 'downloads_files',
														 'where'	=> 'file_id='.$id
												)		);
				$this->ipsclass->DB->exec_query();
				
				$file = $this->ipsclass->DB->fetch_row();
				$category = $this->catlib->cat_lookup[ $file['file_cat'] ];
				
				if( !$file['file_id'] )
				{
					continue;
				}
				
				if( $file['file_open'] == 1 )
				{
					// Already open..
					continue;
				}
				
				$mid = $file['file_submitter'];
				
				$this->ipsclass->DB->build_query( array( 'select' => 'members_display_name, email, view_pop, email_pm',
													 	 'from'   => 'members',
													 	 'where'  => 'id='.intval($mid)
												)		);
				$this->ipsclass->DB->exec_query();
				
				$author = $this->ipsclass->DB->fetch_row();
				
				$to_update = array( 'file_open' 	=> 1,
									'file_approver'	=> $this->ipsclass->member['id'],
									'file_approvedon' => time(),
									'file_new' => 0
								   );
				
				$this->ipsclass->DB->do_update( "downloads_files", $to_update, "file_id=".$id );
				
				// Do topic if necessary
				
				//-----------------------------------------
				// Load and config the libraries
				//-----------------------------------------
				
				if( !$this->classes_loaded )
				{
					$this->load_classes();
				}
				
				$this->parser->parse_bbcode = $this->catlib->cat_lookup[ $file['file_cat'] ]['coptions']['opt_bbcode'];
				$this->parser->parse_html 	= $this->catlib->cat_lookup[ $file['file_cat'] ]['coptions']['opt_html'];
				
				require_once( DL_PATH.'lib/lib_topics.php' );
				$lib_topics = new lib_topics();
				$lib_topics->ipsclass 	=& $this->ipsclass;
				$lib_topics->email		=& $this->email;
				$lib_topics->parser		=& $this->parser;
				$lib_topics->han_editor	=& $this->han_editor;
				
				$file['file_submitter_name'] = $author['members_display_name'];
				$file['file_open']			 = 1;
		
				$lib_topics->sort_topic( $file, $category, $file['file_topicid'] ? 'edit' : 'new', 1 );

				// Send subscribed members a notification?
				if( $file['file_updated'] > $file['file_submitted'] )
				{
					$members = array();
					
					if( !is_null($file['file_sub_mems']) AND $file['file_sub_mems'] != '' )
					{
						// Get rid of the extra commas
						$file['file_sub_mems'] = $this->ipsclass->clean_perm_string( $file['file_sub_mems'] );
						
						$members = explode( ",", $file['file_sub_mems'] );
						
						if ( ! is_array( $lang ) )
						{
							$lang = array();
						}
						
						if( file_exists( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" ) )
						{
							require_once( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" );
							
							if( count($members) )
							{
								foreach( $members as $k => $v )
								{
									if( trim($v) == '' )
									{
										continue;
									}
									
									$this->ipsclass->DB->build_query( array( 'select' => 'email, members_display_name', 'from' => 'members', 'where' => 'id='.$v ) );
									$this->ipsclass->DB->exec_query();
									
									$row = $this->ipsclass->DB->fetch_row();
									
									$this->email->template = stripslashes($lang['header']) . stripslashes($this->ipsclass->lang['subsription_notifications']) . stripslashes($lang['footer']);
										
									$this->email->build_message( array(
																		'NAME'  		=> $row['members_display_name'],
																		'AUTHOR'		=> $author['members_display_name'],
																		'TITLE' 		=> $file['file_name'],
																		'FILE_ID'		=> $file['file_id'],
																	  )
									        					);
									        					
									$this->email->subject = sprintf( $this->ipsclass->lang['sub_notice_subject'], $file['file_name'] );
									$this->email->to      = $row['email'];
										
									$this->email->send_mail();
								}
							}
						}
					}
				}
		
				if( $this->ipsclass->vars['idm_file_notification'] != 'n' && $file['file_new'] )
				{
					//b=both,p=pm,e=email
					$message = sprintf( $this->ipsclass->lang['moderate_appnotify'],
										$author['members_display_name'],
										$file['file_name'] );
										
					$pm = 0;
					$email = 0;
					
					if( $this->ipsclass->vars['idm_file_notification'] == 'b' )
					{
						$pm = 1;
						$email = 1;
					}
					else if( $this->ipsclass->vars['idm_file_notification'] == 'p' )
					{
						$pm = 1;
					}
					else if( $this->ipsclass->vars['idm_file_notification'] == 'e' )
					{
						$email = 1;
					}
					
					if( $email == 1 )
					{
						$this->email->template = stripslashes($lang['header']) . $message . stripslashes($lang['footer']);
							
						$this->email->message = $this->email->template;
						        					
						$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_app_lang'] );
						$this->email->to      = $author['email'];
						$this->email->send_mail();
					}
					
					if( $pm == 1 )
					{
						$postkey = md5( microtime() );
						$show_popup = $author['view_pop'];
						
 						$this->ipsclass->DB->do_insert( 'message_text', array(
															   'msg_date'	       => time(),
															   'msg_post'          => $this->ipsclass->remove_tags($message),
															   'msg_cc_users'      => '',
															   'msg_sent_to_count' => 1,
															   'msg_post_key'      => $postkey,
															   'msg_author_id'     => $this->ipsclass->member['id'],
															   'msg_ip_address'    => $this->ipsclass->ip_address
													  )      );
						$msg_id = $this->ipsclass->DB->get_insert_id();
						
						$this->ipsclass->DB->force_data_type = array( 'mt_title' => 'string' );
						
						$this->ipsclass->DB->do_insert( 'message_topics', array(
																 'mt_msg_id'     => $msg_id,
																 'mt_date'       => time(),
																 'mt_title'      => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_app_lang'] ),
																 'mt_from_id'    => $this->ipsclass->member['id'],
																 'mt_to_id'      => $file['file_submitter'],
																 'mt_vid_folder' => 'in',
																 'mt_tracking'   => 0,
																 'mt_addtosent'	 => 0,
																 'mt_hasattach'  => 0,
																 'mt_owner_id'   => $file['file_submitter'],
																 'mt_hide_cc'    => 0,
												       )      );
						$mt_id = $this->ipsclass->DB->get_insert_id();
																		       
						$this->ipsclass->DB->build_query( array( 'select' => 'vdirs', 'from' => 'member_extra', 'where' => "id={$file['file_submitter']}" ) );
						$this->ipsclass->DB->exec_query();
						$vdirs = $this->ipsclass->DB->fetch_row();
						
						$inbox_count = $this->msglib->_get_dir_count( $vdirs['vdirs'], 'in' );
						
						$new_vdir = $this->msglib->rebuild_dir_count( $file['file_submitter'],
															  "",
															  'in',
															  $inbox_count + 1,
															  'save',
															  "msg_total=msg_total+1,new_msg=new_msg+1,show_popup={$show_popup}"
															);
															
						//-----------------------------------------
						// Has this member requested a PM email nofity?
						//-----------------------------------------
						
						if ($author['email_pm'] == 1)
						{
							$this->email->get_template("pm_notify");
						
							$this->email->build_message( array(
																'NAME'   => $author['members_display_name'],
																'POSTER' => $this->ipsclass->member['members_display_name'],
																'TITLE'  => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_app_lang'] ),
																'LINK'   => "?act=Msg&CODE=03&VID=in&MSID={$mt_id}",
																)       );
														
							$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_app_lang'] );
							$this->email->to      = $author['email'];
							$this->email->send_mail();
						
						}
					}
				}
				
				$num++;
			}
			
			return $num;
		}
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Do multi-unapprove
    /*-------------------------------------------------------------------------*/
    
    function do_multi_unapprove( $ids=array() )
    {
		if( !is_array($ids) OR count($ids) < 1 )
		{
			return 0;
		}
		else
		{
			$num = 0;
			
			foreach( $ids as $id => $flag )
			{			    
				$this->ipsclass->DB->build_query( array( 'select'	=> '*',
														 'from'		=> 'downloads_files',
														 'where'	=> 'file_id='.$id
												)		);
				$this->ipsclass->DB->exec_query();
				
				$file = $this->ipsclass->DB->fetch_row();
				$category = $this->catlib->cat_lookup[ $file['file_cat'] ];
				
				if( !$file['file_id'] )
				{
					continue;
				}
				
				if( $file['file_open'] == 0 )
				{
					// Already closed..
					continue;
				}
				
				
				$this->ipsclass->DB->do_update( "downloads_files", array( 'file_open' => 0 ), "file_id=".$id );

				$num++;
			}
			
			return $num;
		}
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Do multi-move
    /*-------------------------------------------------------------------------*/
    
    function do_multi_move( $ids=array(), $newcatid=0 )
    {
		if( !is_array($ids) OR count($ids) < 1 OR !$newcatid )
		{
			return 0;
		}
		else
		{
			$num = 0;
			
			foreach( $ids as $id => $flag )
			{			    
				$this->ipsclass->DB->build_query( array( 'select'	=> '*',
														 'from'		=> 'downloads_files',
														 'where'	=> 'file_id='.$id
												)		);
				$this->ipsclass->DB->exec_query();
				
				$file = $this->ipsclass->DB->fetch_row();
				$category = $this->catlib->cat_lookup[ $file['file_cat'] ];
				
				if( !$file['file_id'] )
				{
					continue;
				}
				
				if( $file['file_cat'] == $newcatid )
				{
					// Already in that cat
					continue;
				}
				
				
				$this->ipsclass->DB->do_update( "downloads_files", array( 'file_cat' => $newcatid ), "file_id=".$id );

				$num++;
			}
			
			return $num;
		}
	}	
	
    /*-------------------------------------------------------------------------*/
    // Do Multi-Delete
    /*-------------------------------------------------------------------------*/
    	
	function do_multi_delete( $ids=array() )
	{
		if( !is_array($ids) OR count($ids) < 1 )
		{
			return 0;
		}
		else
		{
			$num = 0;
			
			foreach( $ids as $id => $flag )
			{		
				$this->ipsclass->DB->build_query( array( 'select'	=> '*',
														 'from'		=> 'downloads_files',
														 'where'	=> 'file_id='.$id
												)		);
				$this->ipsclass->DB->exec_query();
				
				$file = $this->ipsclass->DB->fetch_row();
				
				if( !$file['file_id'] )
				{
					continue;
				}
				
				$mid = $file['file_submitter'];
				
				$this->ipsclass->DB->build_query( array( 'select' => 'members_display_name, email, view_pop, email_pm',
													 	 'from'   => 'members',
													 	 'where'  => 'id='.intval($mid)
												)		);
				$this->ipsclass->DB->exec_query();
				
				$author = $this->ipsclass->DB->fetch_row();				
				
				//-----------------------------------------
				// Delete the files from the server(s)
				//-----------------------------------------
				
				switch( $file['file_storagetype'] )
				{
					case 'web':
					case 'nonweb':
						@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $file['file_filename'] );
						if( $file['file_ssname'] )
						{
							@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_ssname'] );
							if( $file['file_thumb'] )
							{
								@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_thumb'] );
							}
						}
						
						break;
					case 'ftp':
						if( $this->ipsclass->vars['idm_remoteurl'] AND
							$this->ipsclass->vars['idm_remoteport'] AND
							$this->ipsclass->vars['idm_remoteuser'] AND
							$this->ipsclass->vars['idm_remotepass'] AND
							$this->ipsclass->vars['idm_remotefilepath'] )
						{
							$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 2000 );
							if( !$conid )
							{
								$this->message .= $file['file_filename'].$this->ipsclass->lang['ftp_couldnot_del']."<br />";
								break;
							}
							$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
							if( !$check )
							{
								$this->message .= $file['file_filename'].$this->ipsclass->lang['ftp_couldnot_del']."<br />";
								break;
							}
							
							@ftp_delete( $conid, $this->ipsclass->vars['idm_remotefilepath']."/".$file['file_filename'] );
		
							if( $file['file_ssname'] )
							{
								if( $this->ipsclass->vars['idm_remotesspath'] )
								{
									@ftp_delete( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_ssname'] );
									
									if( $file['file_thumb'] )
									{
										@ftp_delete( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_thumb'] );
									}
								}
							}
						}
						break;
				}
				
				//-----------------------------------------
				// Delete from database tables
				//-----------------------------------------		
				
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_favorites', 'where' => "ffid=".$id ) );
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_ccontent', 'where' => "file_id=".$id ) );
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_comments', 'where' => "comment_fid=".$id ) );
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_files', 'where' => "file_id=".$id ) );
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_filestorage', 'where' => "storage_id=".$id ) );
				$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_fileviews', 'where' => "view_fid=".$id ) );
		
				$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => "b_fileid=".$id ) );
				$outer = $this->ipsclass->DB->exec_query();
				
				if( $this->ipsclass->DB->get_num_rows() )
				{
					require_once( DL_PATH.'lib/lib_versioning.php' );
					$versions 				= new lib_versioning();
					$versions->ipsclass 	=& $this->ipsclass;
					$versions->file_id 		= $id;
					$versions->file_data	= $file;
					$versions->init();
					
					while( $r = $this->ipsclass->DB->fetch_row($outer) )
					{
						$versions->remove( $r['b_id'], $r );
					}
				}

				//-----------------------------------------
				// Delete the topic if appropriate
				//-----------------------------------------		
			
				if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topice'] )
				{
					if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf'] )
					{
						if( $this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicd'] )
						{
							$tid = $file['file_topicid'];
							
							if( $tid > 0 )
							{
								require_once( ROOT_PATH.'sources/lib/func_mod.php' );
						        $this->modfunc = new func_mod();
						        $this->modfunc->ipsclass =& $this->ipsclass;
						        
						        $this->modfunc->init($this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf']);
						        
								$this->ipsclass->DB->simple_construct( array( 'select' => 'tid, forum_id', 'from' => 'topics', 'where' => "state='link' AND moved_to='".$tid.'&'.$this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf']."'" ) );
								$this->ipsclass->DB->simple_exec();
								
								if ( $linked_topic = $this->ipsclass->DB->fetch_row() )
								{
									$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'topics', 'where' => "tid=".$linked_topic['tid'] ) );
									
									$this->modfunc->forum_recount($linked_topic['forum_id']);
								}
								
								$this->modfunc->topic_delete($tid);
								$this->modfunc->forum_recount($this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf']);
								$this->modfunc->add_moderate_log($this->catlib->cat_lookup[$file['file_cat']]['coptions']['opt_topicf'], $tid, '', $file['file_name'], "Deleted an IDM Auto-Generated topic");
							}
						}
					}
				}
				
				if( $this->send_msg )
				{
					if( !$this->classes_loaded )
					{
						$this->load_classes();
					}
					
					if( $this->ipsclass->vars['idm_file_notification'] != 'n' && $file['file_new'] )
					{
						//b=both,p=pm,e=email
						$message = sprintf( $this->ipsclass->lang['moderate_dennotify'],
											$author['members_display_name'],
											$file['file_name'] );
											
						$pm = 0;
						$email = 0;
						
						if( $this->ipsclass->vars['idm_file_notification'] == 'b' )
						{
							$pm = 1;
							$email = 1;
						}
						else if( $this->ipsclass->vars['idm_file_notification'] == 'p' )
						{
							$pm = 1;
						}
						else if( $this->ipsclass->vars['idm_file_notification'] == 'e' )
						{
							$email = 1;
						}
						
						if( $email == 1 AND $author['id']  )
						{
							$this->email->template = stripslashes($lang['header']) . $message . stripslashes($lang['footer']);
								
							$this->email->message = $this->email->template;
							        					
							$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] );
							$this->email->to      = $author['email'];
							$this->email->send_mail();
						}
						
						if( $pm == 1 AND $author['id']  )
						{
							$postkey = md5( microtime() );
							$show_popup = $author['view_pop'];
							
	 						$this->ipsclass->DB->do_insert( 'message_text', array(
																   'msg_date'	       => time(),
																   'msg_post'          => $this->ipsclass->remove_tags($message),
																   'msg_cc_users'      => '',
																   'msg_sent_to_count' => 1,
																   'msg_post_key'      => $postkey,
																   'msg_author_id'     => $this->ipsclass->member['id'],
																   'msg_ip_address'    => $this->ipsclass->ip_address
														  )      );
							$msg_id = $this->ipsclass->DB->get_insert_id();
							
							$this->ipsclass->DB->force_data_type = array( 'mt_title' => 'string' );
							
							$this->ipsclass->DB->do_insert( 'message_topics', array(
																	 'mt_msg_id'     => $msg_id,
																	 'mt_date'       => time(),
																	 'mt_title'      => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] ),
																	 'mt_from_id'    => $this->ipsclass->member['id'],
																	 'mt_to_id'      => $file['file_submitter'],
																	 'mt_vid_folder' => 'in',
																	 'mt_tracking'   => 0,
																	 'mt_addtosent'	 => 0,
																	 'mt_hasattach'  => 0,
																	 'mt_owner_id'   => $file['file_submitter'],
																	 'mt_hide_cc'    => 0,
													       )      );
							$mt_id = $this->ipsclass->DB->get_insert_id();
																			       
							$this->ipsclass->DB->build_query( array( 'select' => 'vdirs', 'from' => 'member_extra', 'where' => "id={$file['file_submitter']}" ) );
							$this->ipsclass->DB->exec_query();
							$vdirs = $this->ipsclass->DB->fetch_row();
							
							$inbox_count = $this->msglib->_get_dir_count( $vdirs['vdirs'], 'in' );
							
							$new_vdir = $this->msglib->rebuild_dir_count( $file['file_submitter'],
																  "",
																  'in',
																  $inbox_count + 1,
																  'save',
																  "msg_total=msg_total+1,new_msg=new_msg+1,show_popup={$show_popup}"
																);
																
							//-----------------------------------------
							// Has this member requested a PM email nofity?
							//-----------------------------------------
							
							if ($author['email_pm'] == 1)
							{
								$this->email->get_template("pm_notify");
							
								$this->email->build_message( array(
																	'NAME'   => $author['members_display_name'],
																	'POSTER' => $this->ipsclass->member['members_display_name'],
																	'TITLE'  => sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] ),
																	'LINK'   => "?act=Msg&CODE=03&VID=in&MSID={$mt_id}",
																	)       );
															
								$this->email->subject = sprintf( $this->ipsclass->lang['moderate_subject'], $file['file_name'], $this->ipsclass->lang['moderate_del_lang'] );
								$this->email->to      = $author['email'];
								$this->email->send_mail();
							
							}
						}
					}
				}
				
				$num++;
			}
			
			return $num;
		}
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Remove broken file flag
    /*-------------------------------------------------------------------------*/
    	
	function do_multi_unbroke( $ids=array() )
	{
		if( !is_array($ids) OR count($ids) < 1 )
		{
			return 0;
		}
		else
		{
			$num = 0;
			
			foreach( $ids as $id => $flag )
			{
				$this->ipsclass->DB->do_update( "downloads_files", array( 'file_broken' => 0 ), "file_id=".$id );
				
				$num++;
			}
			
			return $num;
		}
	}
	
	
	function load_classes()
	{
		if( !$this->classes_loaded )
		{
			if( !is_object($this->han_editor) )
			{
				require_once( ROOT_PATH."sources/handlers/han_editor.php" );
				$this->han_editor = new han_editor();
				$this->han_editor->ipsclass =& $this->ipsclass;
				$this->han_editor->init();
			}
			
			if( !is_object($this->parser) )
			{	
				require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
				$this->parser 						= new parse_bbcode();
				$this->parser->ipsclass 			=& $this->ipsclass;
				$this->parser->allow_update_caches 	= 1;
				$this->parser->bypass_badwords 		= intval($this->ipsclass->member['g_bypass_badwords']);
				$this->parser->parse_bbcode			= 1;
				$this->parser->parse_smilies		= 1;
				$this->parser->parse_html			= 0;
			}
			
			if( !is_object($this->email) )
			{		
				require_once( ROOT_PATH."sources/classes/class_email.php" );
				$this->email = new emailer();
				$this->email->ipsclass =& $this->ipsclass;
				$this->email->email_init();		
			}
			
			if( !is_object($this->func_mod) )
			{
				require_once( ROOT_PATH.'sources/lib/func_mod.php' );
				$this->func_mod           =  new func_mod();
				$this->func_mod->ipsclass =& $this->ipsclass;
			}
			
			if( !is_object($this->post) )
			{
				require_once( ROOT_PATH."sources/classes/post/class_post.php" );
				$this->post           =  new class_post();
				$this->post->ipsclass =& $this->ipsclass;
    			$this->post->email =& $this->email;
			}
			
			if( !is_object($this->msglib) )
			{
		    	require_once( ROOT_PATH.'sources/lib/func_msg.php' );
		 		$this->msglib = new func_msg();
		    	$this->msglib->ipsclass =& $this->ipsclass;
	    	}
			
			$this->classes_loaded = 1;
		}
	}


}

?>