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
|   > Download Script - UCP Pages
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

class idm_ucp
{
	var $ipsclass;
	var $catlib;
	var $message;
	var $funcs;

	var $output;
	var $nav;
	var $page_title;

    /*-------------------------------------------------------------------------*/
	// Run me
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
		if ( intval($this->ipsclass->member['id']) == 0 )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'no_guests' ) );
		}
		
		$this->ipsclass->load_template( 'skin_downloads_ucp' );
		    	
		//--------------------------------------------
		// UserCP functions
		//--------------------------------------------

    	$this->ipsclass->load_language( 'lang_ucp' );
    	$this->ipsclass->load_template( 'skin_ucp' );

		require_once( ROOT_PATH."sources/lib/func_usercp.php" );
		$this->lib   = new func_usercp($this);
		$this->lib->ipsclass =& $this->ipsclass;

		$this->output = $this->lib->ucp_generate_menu();
		
	    $this->nav[] = "<a href='".$this->ipsclass->base_url."act=UserCP&amp;CODE=00'>".$this->ipsclass->lang['t_title']."</a>";
	    $this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=downloads'>".$this->ipsclass->lang['idm_navbar'].'</a>';
		$this->page_title = $this->ipsclass->vars['board_name']." -> ".$this->ipsclass->lang['idm_pagetitle']." -> ";
	    
        //--------------------------------------------
        // Prep form check
        //--------------------------------------------
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->funcs->produce_error( 'no_downloads_permissions' );
		}
		else
		{
	    	switch( $this->ipsclass->input['req'] )
	    	{
				case 'ucp_files':
					$this->list_my_files();
					$this->page_title	.= $this->ipsclass->lang['ucp_manage_files'];
					$this->nav[] 		= "<a href='".$this->ipsclass->base_url."autocom=downloads&amp;req=ucp_files'>".$this->ipsclass->lang['ucp_manage_files']."</a>";
					break;
					
				case 'ucp_favs':
					$this->nav[] 		= "<a href='".$this->ipsclass->base_url."autocom=downloads&amp;req=ucp_favs'>".$this->ipsclass->lang['ucp_manage_favs']."</a>";
					$this->page_title	.= $this->ipsclass->lang['ucp_manage_favs'];
					switch( $this->ipsclass->input['code'] )
					{
						case 'idx':
							$this->list_favs();
							break;
							
						case 'addfavs':
							$this->do_favs_add( );
							break;
							
						case 'do_remove':
							$this->do_favs_remove( );
							break;
							
						default:
							$this->list_favs();
							break;
					}
					break;
							
				case 'ucp_subs':
					$this->nav[] 		= "<a href='".$this->ipsclass->base_url."autocom=downloads&amp;req=ucp_subs'>".$this->ipsclass->lang['ucp_manage_subs']."</a>";
					$this->page_title	.= $this->ipsclass->lang['ucp_manage_subs'];
					switch( $this->ipsclass->input['code'] )
					{
						case 'idx':
							$this->list_subs();
							break;
							
						case 'addsub':
							$this->do_sub_add( );
							break;
							
						case 'do_remove':
							$this->do_sub_remove( );
							break;
							
						default:
							$this->list_subs();
							break;
					}
					break;
					
				default:
					$this->list_my_files();
					$this->nav[] = "<a href='".$this->ipsclass->base_url."autocom=downloads&amp;req=ucp_files'>".$this->ipsclass->lang['ucp_manage_files']."</a>";
					$this->page_title	.= $this->ipsclass->lang['ucp_manage_files'];
					break;
			}
		}

		//-----------------------------------------
		// Let's finish it up and print the output
		//-----------------------------------------
		
    	$fj = $this->ipsclass->build_forum_jump();
		$fj = preg_replace( "!#Forum Jump#!", $this->ipsclass->lang['forum_jump'], $fj);

		$this->output .= $this->ipsclass->compiled_templates['skin_ucp']->CP_end();
    	$this->output .= $this->ipsclass->compiled_templates['skin_ucp']->forum_jump($fj, $links);
    	
		if( $this->message )
		{
			$this->output = str_replace( "<!--MESSAGE-->", $this->ipsclass->compiled_templates['skin_downloads']->message_box( $this->message ), $this->output );
		}

    	$this->ipsclass->print->add_output("$this->output");

        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 1, NAV => $this->nav ) );

	}

    /*-------------------------------------------------------------------------*/
	// List all of a user's files
    /*-------------------------------------------------------------------------*/

	function list_my_files()
	{
		$orderby 	= $this->ipsclass->input['order'] && in_array( $this->ipsclass->input['order'], 
							array( 'file_submitted', 'file_updated', 'file_downloads', 'file_rating', 'file_views', 'file_name' ) ) ?
							$this->ipsclass->input['order'] : 'file_name';

		$ordertype 	= $this->ipsclass->input['ascdesc'] && in_array( $this->ipsclass->input['ascdesc'],
							array( 'asc', 'desc' ) ) ? $this->ipsclass->input['ascdesc'] : 'asc';

		$st			= intval($this->ipsclass->input['st']) > 0 ? intval($this->ipsclass->input['st']) : 0;
							
		$this->ipsclass->input['ascdesc'] = $ordertype == 'asc' ? 'desc' : 'asc';
		
		$cnt = $this->ipsclass->DB->build_and_exec_query( array ( 'select'	=>	'count(*) as num',
        										  				 'from'		=> 'downloads_files',
        										  				 'where'	=> "file_submitter={$this->ipsclass->member['id']}",
        												)		);

		$page_links	= $this->ipsclass->build_pagelinks( array(  'TOTAL_POSS'  => $cnt['num'],
													   			'PER_PAGE'    => 20,
													   			'CUR_ST_VAL'  => $st,
													   			'L_SINGLE'    => '&nbsp;',
													   			'BASE_URL'    => $this->ipsclass->base_url."autocom=downloads&amp;req=ucp_files&amp;order={$orderby}&amp;ascdesc={$ordertype}",
													  )	  	 );		
							
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_header( $this->ipsclass->lang['ucp_manage_files'], $page_links );
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_files_thbar();	
		
		$this->ipsclass->DB->build_query( array ( 'select'	=>	'*',
        										  				 'from'		=> 'downloads_files',
        										  				 'where'	=> "file_submitter={$this->ipsclass->member['id']}",
        										  				 'order'	=> $orderby." ".$ordertype,
        										  				 'limit'	=> array( $st, 20 ),
        												)		);
		$files = $this->ipsclass->DB->exec_query();
		
		

        if ( $this->ipsclass->DB->get_num_rows() == 0 )
        {
        	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_blank_row( "6" );
        }
        else
        {
	       	while( $file = $this->ipsclass->DB->fetch_row($files) )
	       	{
		       	$file['shaded'] = "";
		       	
		       	if( $file['file_open'] == 0 )
		       	{
			       	$file['shaded'] = "shaded";
		       	}
		       	
		       	if( $file['file_updated'] > $file['file_submitted'] )
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_updated'], 'TINY', 1 );
		       	}
		       	else
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_submitted'], 'TINY', 1 );
		       	}
		       	
		       	$file['submitted'] = $this->ipsclass->get_date( $file['file_submitted'], 'TINY', 1 );
		       	
				if( in_array( $file['file_cat'], $this->catlib->member_access['rate'] ) )
				{
					$file['_allow_rate'] = 1;
				}
				
				$this->ipsclass->load_language( 'lang_topic' );
				$file['_rate_img']      = $this->ipsclass->compiled_templates['skin_downloads']->file_rating_image( 0 );
				
				if( !is_null($file['file_votes']) AND $file['file_votes'] != '' AND $file['file_votes'] != '0' )
				{
					$votes = unserialize(stripslashes($file['file_votes']));
					
					if( is_array($votes) AND count($votes) > 0 )
					{
						foreach( $votes as $k => $v )
						{
							if( $k == $this->ipsclass->member['id'] )
							{			
								$file['_rating_value'] 	= $v;
								$file['_allow_rate']	= 0;
							}
						}
					}
				}
				
				$file['_rating_value'] 	= $file['_rating_value'] ? $file['_rating_value'] : $this->ipsclass->lang['you_have_not_rated'];
				
				$file['_rate_int'] 		= $file['file_rating'];
				$file['_rate_cnt']		= count($votes);
				
				$file['_rate_img']  	= $this->ipsclass->compiled_templates['skin_downloads']->file_rating_image($file['_rate_int'] );
				
				$file['vote_panel'] 	= $this->ipsclass->compiled_templates['skin_downloads']->vote_pips( $file );
		       	
		       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_files_row( $file );
	       	}
       	}
       	
       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_end( $page_links );
	}
	
    /*-------------------------------------------------------------------------*/
	// List all of a user's favorites
    /*-------------------------------------------------------------------------*/

	function list_favs()
	{
		$orderby = $this->ipsclass->input['order'] && in_array( $this->ipsclass->input['order'], 
							array( 'file_updated', 'file_name' ) ) ?
							$this->ipsclass->input['order'] : 'file_name';

		$ordertype = $this->ipsclass->input['ascdesc'] && in_array( $this->ipsclass->input['ascdesc'],
							array( 'asc', 'desc' ) ) ? $this->ipsclass->input['ascdesc'] : 'asc';
							
		$this->ipsclass->input['ascdesc'] = $ordertype == 'asc' ? 'desc' : 'asc';
							
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_start_form();
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_header( $this->ipsclass->lang['ucp_manage_favs'] );
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_subs_favs_thbar();							
							
		$this->ipsclass->DB->build_query( array ( 'select'	=>	'fav.*, file.file_id, file.file_name, file.file_open, file.file_updated, file.file_submitted',
        										  				 'from'		=> 'downloads_favorites fav LEFT JOIN ibf_downloads_files file ON (file.file_id=fav.ffid)',
        										  				 'where'	=> "file.file_open=1 AND fav.fmid={$this->ipsclass->member['id']}",
        										  				 'order'	=> "file.".$orderby." ".$ordertype
        												)		);
		$files = $this->ipsclass->DB->exec_query();

        if ( $this->ipsclass->DB->get_num_rows() == 0 )
        {
        	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_blank_row( "3" );
        }
        else
        {
	       	while( $file = $this->ipsclass->DB->fetch_row($files) )
	       	{
		       	if( $file['file_updated'] > $file['file_submitted'] )
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_updated'], 'TINY', 1 );
		       	}
		       	else
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_submitted'], 'TINY', 1 );
		       	}

		       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_subs_favs_row( $file );
	       	}
	       	
	       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_end_form( "3" );
       	}
       	
       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_end();
	}	
	
    /*-------------------------------------------------------------------------*/
	// List all of a user's subscriptions
    /*-------------------------------------------------------------------------*/

	function list_subs()
	{
		$orderby = $this->ipsclass->input['order'] && in_array( $this->ipsclass->input['order'], 
							array( 'file_updated', 'file_name' ) ) ?
							$this->ipsclass->input['order'] : 'file_name';

		$ordertype = $this->ipsclass->input['ascdesc'] && in_array( $this->ipsclass->input['ascdesc'],
							array( 'asc', 'desc' ) ) ? $this->ipsclass->input['ascdesc'] : 'asc';
							
		$this->ipsclass->input['ascdesc'] = $ordertype == 'asc' ? 'desc' : 'asc';
							
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_start_form();
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_header( $this->ipsclass->lang['ucp_manage_subs'] );
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_subs_favs_thbar();							
							
		$this->ipsclass->DB->build_query( array ( 'select'	=>	'file_id, file_name, file_open, file_updated, file_submitted',
        										  				 'from'		=> 'downloads_files',
        										  				 'where'	=> "file_open=1 AND file_sub_mems LIKE '%,{$this->ipsclass->member['id']},%'",
        										  				 'order'	=> $orderby." ".$ordertype
        												)		);
		$files = $this->ipsclass->DB->exec_query();

        if ( $this->ipsclass->DB->get_num_rows() == 0 )
        {
        	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_blank_row( "3" );
        }
        else
        {
	       	while( $file = $this->ipsclass->DB->fetch_row($files) )
	       	{
		       	if( $file['file_updated'] > $file['file_submitted'] )
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_updated'], 'TINY', 1 );
		       	}
		       	else
		       	{
			       	$file['updated'] = $this->ipsclass->get_date( $file['file_submitted'], 'TINY', 1 );
		       	}

		       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_subs_favs_row( $file );
	       	}
	       	
	       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_end_form( "3" );
       	}
       	
       	$this->output .= $this->ipsclass->compiled_templates['skin_downloads_ucp']->ucp_main_end();
	}	
	
	
	/*-------------------------------------------------------------------------*/
    // Subscribe to updates to this file
    /*-------------------------------------------------------------------------*/
    	
	function do_sub_add( )
	{
		$id = intval($this->ipsclass->input['id']);
		
		$this->ipsclass->DB->build_query( array( 'select'	=> '*',
												 'from'		=> 'downloads_files',
												 'where'	=> 'file_id='.$id
										)		);
		$this->ipsclass->DB->exec_query();
		
		$file = $this->ipsclass->DB->fetch_row();
		
		if( !$file['file_id'] )
		{
			if( $this->ipsclass->input['return'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
			}
			else
			{
				$this->funcs->produce_error( 'cannot_find_to_addsub' );
				$this->list_subs();
				return;
			}
		}
		
		$members = array();
		
		if( !is_null($file['file_sub_mems']) AND $file['file_sub_mems'] != '' )
		{
			// Get rid of the extra commas
			$file['file_sub_mems'] = $this->ipsclass->clean_perm_string( $file['file_sub_mems'] );
			
			$members = explode( ",", $file['file_sub_mems'] );
			if( in_array( $this->ipsclass->member['id'], $members ) )
			{
				// Already subscribed, just return them
				if( $this->ipsclass->input['return'] == 1 )
				{
					$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_addedtosubs'], "autocom=downloads&amp;showfile={$id}" );
				}
				else
				{
					$this->message = $this->ipsclass->lang['ucp_addedtosubs'];
					$this->list_subs();
				}
			}
			else
			{
				$members[] = $this->ipsclass->member['id'];
				$update_string = ",".implode( ",", $members ).",";
				
				$this->ipsclass->DB->do_update( "downloads_files", array( 'file_sub_mems' => $update_string ), "file_id=".$id );
				
				if( $this->ipsclass->input['return'] == 1 )
				{
					$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_addedtosubs'], "autocom=downloads&amp;showfile={$id}" );
				}
				else
				{
					$this->message = $this->ipsclass->lang['ucp_addedtosubs'];
					$this->list_subs();
				}
			}
		}
		else
		{
			$update_string = ",".$this->ipsclass->member['id'].",";
			
			$this->ipsclass->DB->do_update( "downloads_files", array( 'file_sub_mems' => $update_string ), "file_id=".$id );
			
			if( $this->ipsclass->input['return'] == 1 )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_addedtosubs'], "autocom=downloads&amp;showfile={$id}" );
			}
			else
			{
				$this->message = $this->ipsclass->lang['ucp_addedtosubs'];
				$this->list_subs();
			}
		}
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Unsubscribe from update notifications
    /*-------------------------------------------------------------------------*/	
	
	function do_sub_remove( )
	{
		$to_remove = array();
		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^rm_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$to_remove[ $match[1] ] = $this->ipsclass->input[$match[0]];
 				}
 			}
 		}
 		
		if( count($to_remove) == 0 )
		{
			if( $this->ipsclass->input['return'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
			}
			else
			{
				$this->funcs->produce_error( 'cannot_find_to_rmsub' );
				$this->list_subs();
				return;
			}
		}
		else
		{
			$removed_cnt = 0;
			
			foreach( $to_remove as $id => $yes )
			{
				if( $yes == 1 )
				{
					$id = intval($id);
 		
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
					
					$members = array();
					
					if( !is_null($file['file_sub_mems']) AND $file['file_sub_mems'] != '' )
					{
						// Get rid of the extra commas
						$file['file_sub_mems'] = $this->ipsclass->clean_perm_string( $file['file_sub_mems'] );
						
						$members = explode( ",", $file['file_sub_mems'] );
						if( in_array( $this->ipsclass->member['id'], $members ) )
						{
							$new_members = array();
							
							foreach( $members as $k => $v )
							{
								if( $v != $this->ipsclass->member['id'] )
								{
									$new_members[] = $v;
								}
							}
							
							if( count($new_members) == 0 )
							{
								$this->ipsclass->DB->do_update( "downloads_files", array( 'file_sub_mems' => NULL ), "file_id=".$id );
							}
							else
							{
								$update_string = ",".implode( ",", $new_members ).",";
								
								$this->ipsclass->DB->do_update( "downloads_files", array( 'file_sub_mems' => $update_string ), "file_id=".$id );
							}
							
							$removed_count++;
						}
					}
				}
			}

			if( $this->ipsclass->input['return'] == 1 )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_rmsubs'], "autocom=downloads&amp;showfile={$id}" );
			}
			else
			{
				$this->message = sprintf( $this->ipsclass->lang['ucp_rmsubss'], $removed_count );
				$this->list_subs();
			}
		}
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Add to Favorites
    /*-------------------------------------------------------------------------*/
    	
	function do_favs_add( )
	{
		$id = intval($this->ipsclass->input['id']);
		
		$this->ipsclass->DB->build_query( array( 'select'	=> '*',
												 'from'		=> 'downloads_files',
												 'where'	=> 'file_id='.$id
										)		);
		$this->ipsclass->DB->exec_query();
		
		$file = $this->ipsclass->DB->fetch_row();
		
		if( !$file['file_id'] )
		{
			if( $this->ipsclass->input['return'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
			}
			else
			{
				$this->funcs->produce_error( 'cannot_find_to_addfavs' );
				$this->list_favs();
				return;
			}
		}
		
		$check = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'COUNT(*) as cnt', 'from' => 'downloads_favorites', 'where' => "fmid={$this->ipsclass->member['id']} AND ffid=$id" ) );
		
		if( $check['cnt'] > 0 )
		{
			if( $this->ipsclass->input['return'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads&showfile={$id}" );
			}
			else
			{
				$this->funcs->produce_error( 'already_addfavs' );
				$this->list_favs();
				return;
			}
		}			
		
		$this->ipsclass->DB->do_insert( "downloads_favorites", array( 'fmid' 		=> $this->ipsclass->member['id'],
																		'ffid' 		=> $id,
																		'fupdated' 	=> $file['file_updated'] ) );
		
		if( $this->ipsclass->input['return'] == 1 )
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_addedtofavs'], "autocom=downloads&amp;showfile={$id}" );
		}
		else
		{
			$this->message = $this->ipsclass->lang['ucp_addedtofavs'];
			$this->list_favs();
		}
	}
	

	/*-------------------------------------------------------------------------*/
    // Remove From Favorites
    /*-------------------------------------------------------------------------*/
    	
	function do_favs_remove( )
	{
		$to_remove = array();
		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^rm_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$to_remove[ $match[1] ] = $this->ipsclass->input[$match[0]];
 				}
 			}
 		}
 		
		if( count($to_remove) == 0 )
		{
			if( $this->ipsclass->input['return'] )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
			}
			else
			{
				$this->funcs->produce_error( 'cannot_find_to_remove' );
				$this->list_subs();
				return;
			}
		}
		else
		{
			$removed_cnt = 0;
			
			foreach( $to_remove as $id => $yes )
			{
				if( $yes == 1 )
				{
					$id = intval($id);
		
					if( !$id )
					{
						continue;
					}		
		
					$this->ipsclass->DB->simple_delete( "downloads_favorites", "fmid={$this->ipsclass->member['id']} AND ffid={$id}" );
					$this->ipsclass->DB->exec_query();
					
					$removed_count++;
				}
			}
				
			if( $this->ipsclass->input['return'] == 1 )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['ucp_removedtofavs'], "autocom=downloads&amp;showfile={$id}" );
			}
			else
			{
				$this->message = sprintf( $this->ipsclass->lang['ucp_removedtofavss'], $removed_count );
				$this->list_favs();
			}
		}
	}
		

}
?>