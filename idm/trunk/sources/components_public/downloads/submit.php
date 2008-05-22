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
|   > Submission Script - Add + Edit
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .05
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class idm_submit
{
	var $ipsclass;
	var $catlib;
	var $funcs;

	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";

	var $mime_types		= array();
	
	var $error_msg;
	
	var $stored_ftp		= array();
	
	var $ismod;

    /*-------------------------------------------------------------------------*/
	// Our run_me function
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
	    // Global navigation bar/title
	    $this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=downloads'>".$this->ipsclass->lang['idm_navbar'].'</a>';
		$this->page_title = $this->ipsclass->vars['board_name']." -> ".$this->ipsclass->lang['idm_pagetitle'];
		
		$this->ipsclass->load_template( 'skin_downloads_submit' );
		
		// Can we see any categories?
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->funcs->produce_error( 'no_downloads_permissions' );
		}
		else
		{
			switch( $this->ipsclass->input['code'] )
			{
				case 'add_start':
					$this->start_form( 'new' );
					break;
					
				case 'add_cont':
					$this->continue_form( 'new' );
					break;
				
				case 'edit_main':
					$this->continue_form( 'edit' );
					break;
					
				case 'edit_cat':
					$this->start_form( 'edit' );
					break;
					
				case 'add_comp':
					$this->main_save( 'new' );
					break;
					
				case 'edit_comp':
					$this->main_save( 'edit' );
					break;
					

				case 'email':
					$this->send_email();
					break;
					
				default:
					$this->start_form( 'new' );
					break;
			}
		}
		
		// Print the output
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 1, NAV => $this->nav ) );
	}
	
	

	/*-------------------------------------------------------------------------*/
    // Send email
    /*-------------------------------------------------------------------------*/
    
    function send_email()
    {
	    $id 	= intval($this->ipsclass->input['id']);
	    $addy 	= $this->ipsclass->clean_email( $this->ipsclass->input['email'] );
	    $data	= trim($this->ipsclass->input['content']);
	    
	    if( !$this->ipsclass->member['id'] )
	    {
		    $this->ipsclass->Error( array( 'MSG' => 'idm_emails_onlymembers' ) );
	    }
	    
	    if( !$addy )
	    {
		     $this->ipsclass->Error( array( 'MSG' => 'idm_emails_noemail' ) );
	    }
	    
		if( !$id )
		{
			$this->ipsclass->Error( array( 'MSG' => 'idm_emails_noid' ) );
	    }

		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																 'from'		=> 'downloads_files',
																 'where'	=> 'file_id='.$id
														)		);
		
		if( !$file['file_id'] )
		{
			$this->ipsclass->Error( array( 'MSG' => 'idm_emails_noid' ) );
	    }
	    
	    
		if( count($this->catlib->member_access['view']) == 0 OR !in_array($file['file_cat'], $this->catlib->member_access['view']) )
		{
			$this->ipsclass->Error( array( 'MSG' => 'idm_emails_noid' ) );
		}		    
	    
		require_once( ROOT_PATH."sources/classes/class_email.php" );
		$email = new emailer();
		$email->ipsclass =& $this->ipsclass;
		$email->email_init();	
		
		if ( !isset($lang) OR !is_array( $lang ) )
		{
			$lang = array();
		}
		
		if( file_exists( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" ) )
		{
			require_once( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" );
		}
		
		$email->template = stripslashes($lang['header']) . $this->ipsclass->lang['idm_emails_template'] . stripslashes($lang['footer']);
		
		$email->build_message( array(
											'FILENAME' 		=> $file['file_name'],
											'CONTENT'		=> $data,
											'LINK' 			=> $this->ipsclass->vars['board_url'] . '/index.php?autocom=downloads&showfile=' . $file['file_id'],
										  )
		        					);
		
		$email->subject = sprintf( $this->ipsclass->lang['email_link_sub'], $this->ipsclass->member['members_display_name'] );
		$email->to      = $addy;
			
		$email->send_mail();
	    
    	$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['thanks_for_emailing'], "autocom=downloads&amp;showfile={$id}" );
    }
	
    

    
    
	/*-------------------------------------------------------------------------*/
    // Main save routine
    /*-------------------------------------------------------------------------*/
    
	function main_save( $type='new' )
	{
		$_POST['Post'] 						= $this->ipsclass->txt_stripslashes( $_POST['Post'] );
		$this->ipsclass->input['file_name'] = $this->ipsclass->txt_stripslashes( $this->ipsclass->input['file_name'] );
		
		$catid = intval($this->ipsclass->input['file_cat']);
		
		if( !$catid )
		{
			$this->start_form( $type );
		}
		
		if( $type == 'edit' )
		{
			$canedit = 0;
			
			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$canedit = 1;
				$this->ismod = 1;
			}
			
			if( $this->ipsclass->vars['idm_allow_edit'] )
			{
				$canedit = 1;
			}
			
			if( is_array( $this->catlib->cat_mods[ $catid ] ) )
			{
				if( count($this->catlib->cat_mods[ $catid ]) )
				{
					foreach( $this->catlib->cat_mods[ $catid ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
					}
				}
			}			
			
			if( !$canedit )
			{
				$this->funcs->produce_error( 'no_permitted_categories' );
				return;
			}
										
			$file_id = intval($this->ipsclass->input['id']);
			
			$file = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> '*',
															 		  'from'	=> 'downloads_files',
															 		  'where'	=> 'file_id='.$file_id
													)		);
			
			if( $this->ipsclass->member['id'] != $file['file_submitter'] AND !$this->ismod )
			{
				$this->funcs->produce_error( 'not_your_file' );
				return;
			}			
			
			if( $this->ipsclass->input['file_remove_screenshot'] == 1 )
			{
				if( $file['file_ssname'] )
				{
					switch( $file['file_storagetype'] )
					{
						case 'web':
							@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_ssname'] );
							if( $file['file_thumb'] )
							{
								@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $file['file_thumb'] );
							}
							break;
							
						case 'nonweb':
							@unlink( $this->ipsclass->vars['idm_localsspath'] ."/". $file['file_ssname'] );
							if( $file['file_thumb'] )
							{
								@unlink( $this->ipsclass->vars['idm_localsspath'] ."/". $file['file_thumb'] );
							}
							break;
							
						case 'ftp':
							$this->stored_ftp[] = array( 'delete', $this->ipsclass->vars['idm_remotesspath'] ."/". $file['file_ssname'] );
							$this->stored_ftp[] = array( 'delete', $this->ipsclass->vars['idm_remotesspath'] ."/". $file['file_thumb'] );
							break;
							
						case 'db':
							$this->ipsclass->DB->do_update( "downloads_filestorage", array( 'storage_ss' => NULL, 'storage_thumb' => NULL ), "storage_id='{$file['file_id']}'" );
							$this->ipsclass->DB->sql_optimize_table( "downloads_filestorage" );
							break;
					}
					$file['file_ssname'] 	= null;
					$file['file_thumb'] 	= null;
				}
				else if( $file['file_ssurl'] )
				{
					$this->ipsclass->DB->do_update( "downloads_files", array( 'file_ssurl' => '' ), 'file_id=' . $file['file_id'] );
					
					$this->ipsclass->input['file_ssurl'] 	= null;
					$file['file_ssurl'] 					= null;
				}
			}
			
			// Versioning Control
			
			if( $this->ipsclass->vars['idm_versioning'] )
			{
				require_once( DL_PATH.'lib/lib_versioning.php' );
				$versions 				= new lib_versioning();
				$versions->ipsclass 	=& $this->ipsclass;
				
				$versions->file_id 		= $file['file_id'];
				$versions->file_data	= $file;
				
				$versions->init();
				$versions->backup();
			}			
		}
		else
		{
			$file = array();
			
			$cols = array( 'file_new' => 1 );
			
			$this->ipsclass->DB->do_insert( "downloads_files", $cols );
			
			$file['file_id'] = $this->ipsclass->DB->get_insert_id();
		}
		
		//-----------------------------------------
		// Get category and cat settings
		//-----------------------------------------
				
		$category = $this->catlib->cat_lookup[$catid];
		
		if( count($this->catlib->member_access['add']) == 0 )
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
				
				$this->funcs->produce_error( 'no_addfile_permissions' );
				return;
			}
			else
			{
				$catid = $file['file_cat'];
			}
		}
		else if( !in_array( $catid, $this->catlib->member_access['add'] ) )
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
			}
			
			if( $category['coptions']['opt_noperm_add'] )
			{
				$this->funcs->produce_error( $category['coptions']['opt_noperm_add'], 1 );
			}
			else
			{
				$this->funcs->produce_error( 'no_addthiscat_permissions' );
			}
			return;
		}
		
		//-----------------------------------------
		// Get Mime-Type mask, and it's types
		//-----------------------------------------
		
		$file['allowed_file'] 	= "";
		$file['allowed_ss']	= "";
		$types = array( 'files'	=> array(),
						'ss'	=> array() );
		
		if( count( $this->ipsclass->cache['idm_mimetypes'] ) )
		{
			foreach( $this->ipsclass->cache['idm_mimetypes'] as $k => $v )
			{
				$addfile = explode( ",", $v['mime_file'] );
				if( in_array( $category['coptions']['opt_mimemask'], $addfile ) )
				{
					$types['files'][] = $v['mime_extension'];
				}
				
				$addss = explode( ",", $v['mime_screenshot'] );
				if( in_array( $category['coptions']['opt_mimemask'], $addss ) )
				{
					$types['ss'][] = $v['mime_extension'];
				}
			}
		}
		
		//-----------------------------------------
		// Some Basic Checks First
		//-----------------------------------------
		
		$file['file_name'] = trim($this->ipsclass->input['file_name']);
		if( !$file['file_name'] )
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
			}
			
			$this->error_msg = $this->ipsclass->lang['addfile_error_filename'];
			$this->continue_form( $type );
			return;
		}
		
		$file['file_desc'] = trim($_POST['Post']);

		if( strlen( $file['file_desc'] ) < 1 )
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
			}
			
			$this->error_msg = $this->ipsclass->lang['addfile_error_filedesc'];
			$this->continue_form( $type );
			return;
		}
			
		$_POST['Post'] = $this->ipsclass->remove_tags( $_POST['Post'] );
		
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
		$parser->parse_html    = $category['coptions']['opt_html'] ? 1 : 0;
		$parser->parse_nl2br   = 0;
		$parser->parse_smilies = 1;
		$parser->parse_bbcode  = $category['coptions']['opt_bbcode'] ? 1 : 0;
		
		if( $han_editor->method == 'rte' )
		{
			$_POST['Post'] = $parser->convert_ipb_html_to_html( $_POST['Post'] );
		}

		// Fully formatted and ready to insert
		$file['file_desc'] = $parser->pre_db_parse( $han_editor->process_raw_post( 'Post' ) );
		
		// Filename badwords: Bug 6937
		$file['file_name']	= $parser->bad_words( $file['file_name'] );

		//-----------------------------------------
		// Get soundex meta record
		//-----------------------------------------
				
		$soundex_arr = explode( " ", $file['file_desc'] );
		$soundex_arr = array_merge( $soundex_arr, explode( " ", $file['file_name'] ) );
		
		$soundex_final = array();
		if( count($soundex_arr) > 0 )
		{
			foreach( $soundex_arr as $key => $value )
			{
				if( strlen( $value ) > 3 )
				{
					$soundex_final[] = soundex($value);
				}
			}
		}
		
		$soundex_save_string = "";
		
		if( count($soundex_final) > 0 )
		{
			$soundex_save_string = ",".implode( ",", $soundex_final ).",";
		}
		
		//-----------------------------------------
		// Load the upload library
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_upload.php' );
		$upload = new class_upload();
		
		//-----------------------------------------
		// Set up the variables for FILE
		//-----------------------------------------
		
		$upload->out_file_name    = $file['file_id'].'-'.time().'-'.preg_replace( "/\s+/", "_", $this->_get_file_no_extension($_FILES['file']['name']) );
		$upload->max_file_size    = $category['coptions']['opt_maxfile']*1024;
		$upload->make_script_safe = 1;
		$upload->force_data_ext   = 'ipb';
		
		$upload->upload_form_field = 'file';
		
		//-----------------------------------------
		// Populate allowed extensions
		//-----------------------------------------
		
		$upload->allowed_file_ext  = $types['files'];
		
		switch( $this->ipsclass->vars['idm_filestorage'] )
		{
			case 'web':
				$upload->out_file_dir = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] );
				break;
							
			case 'nonweb':
				$upload->out_file_dir = $this->ipsclass->vars['idm_localfilepath'];
				break;
				
			case 'ftp':
			case 'db':
				$upload->out_file_dir     = $this->ipsclass->vars['upload_dir'];
				break;
		}			
		
		//-----------------------------------------
		// We should check path/url stuff first
		//-----------------------------------------		
		
		if( $this->ipsclass->vars['idm_allow_urls'] )
		{
			$groups = explode( ",", $this->ipsclass->vars['idm_groups_link'] );
			
			$can_do_it = 0;
			
			$my_groups = array( $this->ipsclass->member['mgroup'] );
			
			if( $this->ipsclass->member['mgroup_others'] )
			{
				$o_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
				
				$my_groups = array_merge( $my_groups, $o_mgroups );
			}
			
			foreach( $my_groups as $groupid )
			{
				if( in_array( $groupid, $groups ) )
				{
					$can_do_it = 1;
					break;
				}
			}
						
			if( $can_do_it == 1 AND $this->ipsclass->input['file_url'] AND $this->ipsclass->xss_check_url( $this->ipsclass->input['file_url'] ) )
			{
				$file['file_url'] = trim($this->ipsclass->input['file_url']);
				
				$file_data = array_pop( explode( "/", $file['file_url'] ) );
				
				$upload->file_extension = $upload->_get_file_extension( $file_data );
				
				if ( ! $upload->file_extension )
				{
					$upload->error_no = 2;
				}
				else
				{
					$upload->real_file_extension = $upload->file_extension;
					
					if ( ! in_array( $upload->file_extension, $upload->allowed_file_ext ) )
					{
						$upload->error_no = 2;
					}
				}
				
				$file['file_size'] = $this->_obtain_remote_size( $file['file_url'] );
			}
		}
		
		if( $file['file_url'] AND !$this->ipsclass->input['file_url'] )
		{
			$file['file_url'] = '';
		}
		
		if( $file['file_ssurl'] AND !$this->ipsclass->input['file_ssurl'] )
		{
			$file['file_ssurl'] = '';
		}
		
		
		if( $this->ipsclass->vars['idm_allow_path'] )
		{
			$groups = explode( ",", $this->ipsclass->vars['idm_path_users'] );
			
			$can_do_it = 0;
			
			$my_groups = array( $this->ipsclass->member['mgroup'] );
			
			if( $this->ipsclass->member['mgroup_others'] )
			{
				$o_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
				
				$my_groups = array_merge( $my_groups, $o_mgroups );
			}
			
			foreach( $my_groups as $groupid )
			{
				if( in_array( $groupid, $groups ) )
				{
					$can_do_it = 1;
					break;
				}
			}
		
			$this->ipsclass->input['file_path'] = str_replace( "&#46;&#46;/", "../"	, $this->ipsclass->input['file_path'] );
			$this->ipsclass->input['file_path'] = str_replace( "&#92;"		, "/"	, $this->ipsclass->input['file_path'] );
			$this->ipsclass->input['file_path'] = str_replace( "\\"			, "/"	, $this->ipsclass->input['file_path'] );
			
			if( $can_do_it == 1 )
			{
				if( $this->ipsclass->input['file_path'] )
				{
					$file_data = explode( "/", $this->ipsclass->input['file_path'] );
					
					$FILE_NAME = array_pop( $file_data );
					
					// Does file exist?
					
					if( !file_exists( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) . '/' . $this->ipsclass->input['file_path'] ) )
					{
						$upload->error_no = 1;
					}
					
					$upload->file_extension = $upload->_get_file_extension( $this->ipsclass->input['file_path'] );
					
					if ( ! $upload->file_extension )
					{
						$upload->error_no = 2;
					}
					else
					{
						$upload->real_file_extension = $upload->file_extension;
						
						if ( ! in_array( $upload->file_extension, $upload->allowed_file_ext ) )
						{
							$upload->error_no = 2;
						}
						else
						{
							$file['file_realname'] = $FILE_NAME;
								
							$FILE_NAME = preg_replace( "/[^\w\.]/", "_", $FILE_NAME );
							$upload->original_file_name = $FILE_NAME;
							
							$upload->out_file_name    = $file['file_id'].'-'.time().'-'.preg_replace( "/\s+/", "_", $this->_get_file_no_extension($FILE_NAME) );
							
							if ( $upload->out_file_name )
							{
								$upload->parsed_file_name = $upload->out_file_name;
							}
							else
							{
								$upload->parsed_file_name = preg_replace( '#\.'.$upload->file_extension."#is", "", $FILE_NAME );
							}
							
							if ( $upload->make_script_safe )
							{
								if ( preg_match( "/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)/i", $FILE_NAME ) )
								{
									$FILE_TYPE                 = 'text/plain';
									$upload->file_extension      = 'txt';
								}
							}
							
							if ( is_array( $upload->image_ext ) and count( $upload->image_ext ) )
							{
								if ( in_array( $upload->file_extension, $upload->image_ext ) )
								{
									$upload->is_image = 1;
								}
							}
							
							if ( $upload->force_data_ext and ! $upload->is_image )
							{
								$upload->file_extension = str_replace( ".", "", $upload->force_data_ext ); 
							}
							
							$upload->parsed_file_name .= '.'.$upload->file_extension;
							
							$upload->saved_upload_name = $upload->out_file_dir.'/'.$upload->parsed_file_name;								
							
							if ( ! @rename( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) . '/' . $this->ipsclass->input['file_path'], $upload->saved_upload_name) )
							{
								$upload->error_no = 4;
							}
							else
							{
								@chmod( $upload->saved_upload_name, 0777 );

								if ( $upload->is_image )
								{
									//-------------------------------------------------
									// Are we making sure its an image?
									//-------------------------------------------------
									
									if ( $upload->image_check )
									{
										$img_attributes = @getimagesize( $upload->saved_upload_name );
										
										if ( ! is_array( $img_attributes ) or ! count( $img_attributes ) )
										{
											// Unlink the file first
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
										else if ( ! $img_attributes[2] )
										{
											// Unlink the file first
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
										else if ( $img_attributes[2] == 1 AND ( $upload->file_extension == 'jpg' OR $upload->file_extension == 'jpeg' ) )
										{
											// Potential XSS attack with a fake GIF header in a JPEG
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
									}
								}
							}
						}
					}
				}
			}
		}		

		if( !$this->ipsclass->input['file_path'] AND !$file['file_url'] )
		{
			//-----------------------------------------
			// Upload...
			//-----------------------------------------
			
			$upload->upload_process();
		}
		
		//-----------------------------------------
		// Error?
		//-----------------------------------------

		if ( $upload->error_no )
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
			}

			switch( $upload->error_no )
			{
				case 1:
					// No upload
					if( $type != 'edit' )
					{
						$this->error_msg = $this->ipsclass->lang['addfile_upload_error1'];
						$this->continue_form( $type );
						return;
					}
					break;
				case 2:
					// Invalid file ext
					$this->error_msg = $this->ipsclass->lang['addfile_upload_error2'];
					$this->continue_form( $type );
					return;
					break;					
				case 3:
					// Too big...
					$this->error_msg = $this->ipsclass->lang['addfile_upload_error3'];
					$this->continue_form( $type );
					return;
					break;
				case 4:
					// Cannot move uploaded file
					$this->error_msg = $this->ipsclass->lang['addfile_upload_error4'];
					$this->continue_form( $type );
					return;
					break;
				case 5:
					// Possible XSS attack (image isn't an image)
					$this->error_msg = $this->ipsclass->lang['addfile_upload_error5'];
					$this->continue_form( $type );
					return;
					break;
			}
		}

		//-----------------------------------------
		// Still here?
		// File itself uploaded succesfully...
		//-----------------------------------------
		
		if ( ( $upload->saved_upload_name and @file_exists( $upload->saved_upload_name ) ) OR $file['file_url'] )
		{
			$tmp_file_name = $file['file_filename'];
			
			$file['file_size']   			= $upload->saved_upload_name ? @filesize( $upload->saved_upload_name ) : $file['file_size'];
			
			if( $file['file_size'] < 1 AND !$file['file_url'] )
			{
				$file['file_size'] = $_FILES['file']['size'];
			}
			
			if( $upload->parsed_file_name )
			{
				$file['file_filename']   		= $upload->parsed_file_name;
				$file['file_realname'] 			= $upload->original_file_name;
			}
			else
			{
				// URL
				
				$file['file_filename']			= $file_data;
				$file['file_realname'] 			= $file_data;
			}
			
			$file['file_mime']       		= $this->ipsclass->cache['idm_mimetypes'][$upload->real_file_extension]['mime_id'];
			
			// We need to update now, in case an error occurs again
			
			if( $type == 'edit' AND !$file['file_url'] )
			{
				switch( $this->ipsclass->vars['idm_filestorage'] )
				{
					case 'web':
						if( !$this->ipsclass->vars['idm_versioning'] )
						{
							@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $tmp_file_name );
						}
						
						$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_size' 		=> $file['file_size'],
																					'file_filename' => $file['file_filename'],
																					'file_realname'	=> $file['file_realname'],
																					'file_mime'		=> $file['file_mime'] ), 'file_id='.$file['file_id'] );

						break;
									
					case 'nonweb':
						if( !$this->ipsclass->vars['idm_versioning'] )
						{
							@unlink( $this->ipsclass->vars['idm_localfilepath'] ."/". $tmp_file_name );
						}

						$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_size' 		=> $file['file_size'],
																					'file_filename' => $file['file_filename'],
																					'file_realname'	=> $file['file_realname'],
																					'file_mime'		=> $file['file_mime'] ), 'file_id='.$file['file_id'] );

						break;
						
					case 'ftp':
						$this->stored_ftp[] = array( 'delete', $this->ipsclass->vars['idm_remotefilepath'] ."/". $tmp_file_name );
						$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_size' 		=> $file['file_size'],
																					'file_filename' => $file['file_realname'],
																					'file_realname'	=> $file['file_realname'],
																					'file_mime'		=> $file['file_mime'] ), 'file_id='.$file['file_id'] );

						break;
						
					case 'db':
						$filedata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_filename'] ) );
					
						$this->ipsclass->DB->do_update( "downloads_filestorage", array( 'storage_file' => $filedata ), "storage_id='{$file['file_id']}'" );
						
						unset($filedata);

						$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_size' 		=> $file['file_size'],
																					'file_filename' => $file['file_filename'],
																					'file_realname'	=> $file['file_realname'],
																					'file_mime'		=> $file['file_mime'] ), 'file_id='.$file['file_id'] );

						break;
				}
				
			}
			
			unset($tmp_file_name);
		}
		else
		{
			if( $type == 'new' )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
				
				$this->error_msg = $this->ipsclass->lang['addfile_upload_mainerror'];
				$this->continue_form( $type );				
				return;
			}
		}
		
		if( $category['coptions']['opt_allowss'] )
		{
			//-----------------------------------------
			// Set up the variables for SCREEN
			//-----------------------------------------
			$upload->saved_upload_name = null;
			$upload->parsed_file_name = null;
			
			$upload->out_file_name    = $file['file_id'].'-'.time().'-'.preg_replace( "/\s+/", "_", $this->_get_file_no_extension($_FILES['file_screenshot']['name']) );
			
			$upload->max_file_size    = $category['coptions']['opt_maxss']*1024;
			
			$upload->upload_form_field = 'file_screenshot';
			
			//-----------------------------------------
			// Populate allowed extensions
			//-----------------------------------------
			
			$upload->allowed_file_ext = $types['ss'];
			$upload->img_ext 		  = $types['ss'];
			
			switch( $this->ipsclass->vars['idm_filestorage'] )
			{
				case 'web':
					$upload->out_file_dir = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] );
					break;
								
				case 'nonweb':
					$upload->out_file_dir 	= $this->ipsclass->vars['idm_localsspath'];
					break;
					
				case 'ftp':
				case 'db':
					$upload->out_file_dir   = $this->ipsclass->vars['upload_dir'];
					break;
			}			
			
			//-----------------------------------------
			// We should check path/url stuff first
			//-----------------------------------------		
			
			$did_ss = 0;
			
			if( $this->ipsclass->vars['idm_allow_urls'] )
			{
				$groups = explode( ",", $this->ipsclass->vars['idm_groups_link'] );
				
				$can_do_it = 0;
				
				$my_groups = array( $this->ipsclass->member['mgroup'] );
				
				if( $this->ipsclass->member['mgroup_others'] )
				{
					$o_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
					
					$my_groups = array_merge( $my_groups, $o_mgroups );
				}
				
				foreach( $my_groups as $groupid )
				{
					if( in_array( $groupid, $groups ) )
					{
						$can_do_it = 1;
						break;
					}
				}
							
				if( $can_do_it == 1 AND $this->ipsclass->input['file_ssurl'] AND $this->ipsclass->xss_check_url( $this->ipsclass->input['file_ssurl'] ) )
				{
					$file['file_ssurl'] = trim($this->ipsclass->input['file_ssurl']);
					
					$file_data = array_pop( explode( "/", $file['file_ssurl'] ) );
					
					$upload->file_extension = $upload->_get_file_extension( $file_data );
					
					if ( ! $upload->file_extension )
					{
						$upload->error_no = 2;
					}
					else
					{
						$upload->real_file_extension = $upload->file_extension;
						
						if ( ! in_array( $upload->file_extension, $upload->allowed_file_ext ) )
						{
							$upload->error_no = 2;
						}
					}
					
					$did_ss = 1;
				}
			}
			
			if( $this->ipsclass->vars['idm_allow_path'] AND $can_do_it == 1 )
			{
				if( $this->ipsclass->input['file_sspath'] )
				{
					$file_data = explode( "/", $this->ipsclass->input['file_sspath'] );
					
					$FILE_NAME = array_pop( $file_data );
					
					// Does file exist?
					
					if( !file_exists( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) . '/' . $this->ipsclass->input['file_sspath'] ) )
					{
						$upload->error_no = 1;
					}
					
					$upload->file_extension = $upload->_get_file_extension( $this->ipsclass->input['file_sspath'] );
					
					if ( ! $upload->file_extension )
					{
						$upload->error_no = 2;
					}
					else
					{
						$upload->real_file_extension = $upload->file_extension;
						
						if ( ! in_array( $upload->file_extension, $upload->allowed_file_ext ) )
						{
							$upload->error_no = 2;
						}
						else
						{
							$FILE_NAME = preg_replace( "/[^\w\.]/", "_", $FILE_NAME );
							$upload->original_file_name = $FILE_NAME;
							
							$upload->out_file_name    = $file['file_id'].'-'.time().'-'.preg_replace( "/\s+/", "_", $this->_get_file_no_extension($FILE_NAME) );
							
							if ( $upload->out_file_name )
							{
								$upload->parsed_file_name = $upload->out_file_name;
							}
							else
							{
								$upload->parsed_file_name = preg_replace( '#\.'.$upload->file_extension."#is", "", $FILE_NAME );
							}
							
							if ( $upload->make_script_safe )
							{
								if ( preg_match( "/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)/i", $FILE_NAME ) )
								{
									$FILE_TYPE                 = 'text/plain';
									$upload->file_extension      = 'txt';
								}
							}
							
							if ( is_array( $upload->image_ext ) and count( $upload->image_ext ) )
							{
								if ( in_array( $upload->file_extension, $upload->image_ext ) )
								{
									$upload->is_image = 1;
								}
							}
							
							if ( $upload->force_data_ext and ! $upload->is_image )
							{
								$upload->file_extension = str_replace( ".", "", $upload->force_data_ext ); 
							}
							
							$upload->parsed_file_name .= '.'.$upload->file_extension;
							
							$upload->saved_upload_name = $upload->out_file_dir.'/'.$upload->parsed_file_name;								
							
							if ( ! @rename( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) . '/' . $this->ipsclass->input['file_sspath'], $upload->saved_upload_name) )
							{
								$upload->error_no = 4;
							}
							else
							{
								@chmod( $upload->saved_upload_name, 0777 );

								if ( $upload->is_image )
								{
									//-------------------------------------------------
									// Are we making sure its an image?
									//-------------------------------------------------
									
									if ( $upload->image_check )
									{
										$img_attributes = @getimagesize( $upload->saved_upload_name );
										
										if ( ! is_array( $img_attributes ) or ! count( $img_attributes ) )
										{
											// Unlink the file first
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
										else if ( ! $img_attributes[2] )
										{
											// Unlink the file first
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
										else if ( $img_attributes[2] == 1 AND ( $upload->file_extension == 'jpg' OR $upload->file_extension == 'jpeg' ) )
										{
											// Potential XSS attack with a fake GIF header in a JPEG
											@unlink( $upload->saved_upload_name );
											$upload->error_no = 5;
										}
									}
								}
							}
						}
						
						$did_ss = 1;
					}
				}
			}
			
			//-----------------------------------------
			// Upload...
			//-----------------------------------------
			
			if( !$did_ss )
			{
				$upload->upload_process();
			}
			
			//-----------------------------------------
			// Error?
			//-----------------------------------------
			
			if ( $upload->error_no )
			{
				switch( $upload->error_no )
				{
					case 1:
						// No upload
						if( $category['coptions']['opt_reqss'] && $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
							
							$this->error_msg = $this->ipsclass->lang['addfile_upload_error6'];
							$this->continue_form( $type );
							return;
						}
						break;
					case 2:
						// Invalid file ext
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
						
						$this->error_msg = $this->ipsclass->lang['addfile_upload_error7'];
						$this->continue_form( $type );
						return;
						break;					
					case 3:
						// Too big...
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
												
						$this->error_msg = $this->ipsclass->lang['addfile_upload_error8'];
						$this->continue_form( $type );
						return;
						break;
					case 4:
						// Cannot move uploaded file
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
												
						$this->error_msg = $this->ipsclass->lang['addfile_upload_error4'];
						$this->continue_form( $type );
						return;
						break;
					case 5:
						// Possible XSS attack (image isn't an image)
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
												
						$this->error_msg = $this->ipsclass->lang['addfile_upload_error5'];
						$this->continue_form( $type );
						return;
						break;
				}
			}
						
			//-----------------------------------------
			// Still here?
			// File itself uploaded succesfully...
			//-----------------------------------------
			
			if ( ( $upload->saved_upload_name and @file_exists( $upload->saved_upload_name ) ) OR $file['file_ssurl'] )
			{
				$tmp_screen_name = $file['file_ssname'];
				$tmp_thumb_name  = $file['file_thumb'];

				if( $upload->parsed_file_name )
				{
					$file['file_ssname']   		= $upload->parsed_file_name;
				}
				
				$file['file_ssmime']       	= intval($this->ipsclass->cache['idm_mimetypes'][$upload->real_file_extension]['mime_id']);
				
				if( !$file['file_ssurl'] )
				{
					require_once( DL_PATH.'lib/lib_thumb.php' );
					$image = new lib_thumb();
		
					$image->in_type        = 'file';
					$image->out_type       = 'file';
					
					$image->in_file_dir    = $upload->out_file_dir;
					$image->in_file_name   = $file['file_ssname'];
					
					// Do we want to append a copyright or watermark?
					// If so we DON'T want to do it to the main img
					
					if( $this->ipsclass->vars['idm_addwatermark'] )
					{
						$image->out_file_dir   = $upload->out_file_dir;
	
						$image->gd_version     = $this->ipsclass->vars['gd_version'];
						$image->do_water	   = $this->ipsclass->vars['idm_addwatermark'];
						$image->water_path	   = $this->ipsclass->vars['idm_watermarkpath'];
					}
					else if( $this->ipsclass->vars['idm_addcopyright'] )
					{
						$image->out_file_dir   = $upload->out_file_dir;
	
						$image->gd_version     = $this->ipsclass->vars['gd_version'];
						$image->do_copy		   = $this->ipsclass->vars['idm_addcopyright'];
						$image->cpy_txt		   = $this->ipsclass->vars['idm_copyrighttext'];
					}
					
					// What about thumbnails?  Do we want thumbnails?
					// As long as you don't keep nibbling on them
					
					if( $category['coptions']['opt_thumb_x'] && $category['coptions']['opt_thumb_y'] )
					{
						$image->desired_width  = $category['coptions']['opt_thumb_x'];
						$image->desired_height = $category['coptions']['opt_thumb_y'];
						
						$image->out_file_name  = 'thumb-'.$upload->out_file_name;
						$return = $image->generate_thumbnail();
						
						$file['file_thumb']	   = $return['thumb_location'];
						
						if( $file['file_thumb'] == $file['file_ssname'] )
						{
							// Not a thumbnail, just getting the image we sent returned
							$file['file_thumb'] = "";
						}
					}
					
					# Free some memory
					unset($image);
				}
				
				if( $type == 'edit' AND !$file['file_ssurl'] )
				{
					switch( $this->ipsclass->vars['idm_filestorage'] )
					{
						case 'web':
							if( $tmp_screen_name AND !$this->ipsclass->vars['idm_versioning'] )
							{
								@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/".$tmp_screen_name );
								if( $tmp_thumb_name )
								{
									@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $tmp_thumb_name );
								}
							}
							
							$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_ssname' => $file['file_ssname'],
																					  'file_thumb'  => $file['file_thumb'],
																					  'file_ssmime' => intval($file['file_ssmime']) ), 'file_id='.$file['file_id'] );

							break;
										
						case 'nonweb':
							if( $tmp_screen_name AND !$this->ipsclass->vars['idm_versioning'] )
							{
								@unlink( $this->ipsclass->vars['idm_localsspath'] ."/". $tmp_screen_name );
								if( $tmp_thumb_name )
								{
									@unlink( $this->ipsclass->vars['idm_localsspath'] ."/".  $tmp_thumb_name );
								}
							}
							
							$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_ssname' => $file['file_ssname'],
																					  'file_thumb'  => $file['file_thumb'],
																					  'file_ssmime' => intval($file['file_ssmime']) ), 'file_id='.$file['file_id'] );

							break;
							
						case 'ftp':
							if( $tmp_screen_name  )
							{
								$this->stored_ftp[] = array( 'delete', $this->ipsclass->vars['idm_remotesspath'] ."/". $tmp_screen_name );
								$this->stored_ftp[] = array( 'delete', $this->ipsclass->vars['idm_remotesspath'] ."/". $tmp_thumb_name );
							}
							
							$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_ssname' => $file['file_ssname'],
																					  'file_thumb'  => $file['file_thumb'],
																					  'file_ssmime' => intval($file['file_ssmime']) ), 'file_id='.$file['file_id'] );

							break;
							
						case 'db':
							$ssdata = $thumbdata = "";
							
							if( $file['file_ssname'] )
							{
								$ssdata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_ssname'] ) );
								
								if( $file['file_thumb'] )
								{
									$thumbdata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_thumb'] ) );
								}
							}

							if( $tmp_screen_name  )
							{
								$this->ipsclass->DB->do_update( "downloads_filestorage", array( 'storage_ss' => $ssdata, 'storage_thumb' => $thumbdata ), "storage_id='{$file['file_id']}'" );
								$this->ipsclass->DB->sql_optimize_table( "downloads_filestorage" );
							}
							
							$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_ssname' => $file['file_ssname'],
																					  'file_thumb'  => $file['file_thumb'],
																					  'file_ssmime' => intval($file['file_ssmime']) ), 'file_id='.$file['file_id'] );

							break;
					}
				}
				
				unset($tmp_screen_name);
				unset($tmp_thumb_name);
			}
		}

		switch( $this->ipsclass->vars['idm_filestorage'] )
		{
			case 'web':
				$file['file_storagetype'] = 'web';
				break;
							
			case 'nonweb':
				$file['file_storagetype'] = 'nonweb';
				break;
				
			case 'ftp':
				if( $this->ipsclass->vars['idm_remoteurl'] AND
					$this->ipsclass->vars['idm_remoteport'] AND
					$this->ipsclass->vars['idm_remoteuser'] AND
					$this->ipsclass->vars['idm_remotepass'] AND
					$this->ipsclass->vars['idm_remotefilepath'] )
				{
					$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 999999 );
					if( !$conid )
					{
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
						
						$this->error_msg = $this->ipsclass->lang['addfile_ftp_error1'];
						$this->continue_form( $type );
						return;
					}
					$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
					if( !$check )
					{
						if( $type == 'new' )
						{
							$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
						}
						
						$this->error_msg = $this->ipsclass->lang['addfile_ftp_error1'];
						$this->continue_form( $type );
						return;
					}
					
					if( count($this->stored_ftp) )
					{
						foreach( $this->stored_ftp as $k => $v )
						{
							if( $v[0] == 'delete' )
							{
								@ftp_delete( $conid, $v[1] );
							}
						}
					}
					
					@ftp_pasv( $conid, TRUE );
					
					$ftp_do_file_upload = @ftp_put( $conid, $this->ipsclass->vars['idm_remotefilepath']."/".$file['file_realname'], $this->ipsclass->vars['upload_dir']."/".$file['file_filename'], FTP_BINARY );
					
					$file['file_filename'] = $file['file_realname'];
					
					if( $file['file_ssname'] )
					{
						if( $this->ipsclass->vars['idm_remotesspath'] )
						{
							$ftp_do_ss_upload 		= @ftp_put( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_ssname'], $this->ipsclass->vars['upload_dir']."/".$file['file_ssname'], FTP_BINARY );
							$ftp_do_thumb_upload 	= @ftp_put( $conid, $this->ipsclass->vars['idm_remotesspath']."/".$file['file_thumb'], $this->ipsclass->vars['upload_dir']."/".$file['file_thumb'], FTP_BINARY );
						}
					}
				}
				$file['file_storagetype'] = "ftp";
				break;
				
			case 'db':
				// Get file data first
				$filedata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_filename'] ) );
				
				if( $filedata == '' )
				{
					if( $type == 'new' )
					{
						$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
					}
					
					$this->error_msg = $this->ipsclass->lang['addfile_ftp_error1'];
					$this->continue_form( $type );
					return;
				}
				
				if( $file['file_ssname'] )
				{
					$ssdata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_ssname'] ) );
					
					if( $file['file_thumb'] )
					{
						$thumbdata = base64_encode( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$file['file_thumb'] ) );
					}
				}
				
				$this->ipsclass->DB->do_insert( "downloads_filestorage", array( 'storage_id' 	=> $file['file_id'],
																				'storage_file'	=> $filedata,
																				'storage_ss'	=> $ssdata,
																				'storage_thumb'	=> $thumbdata )	);
				@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_filename']);
				if( $file['file_ssname'] )
				{
					@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_ssname']);
					
					if( $file['file_thumb'] )
					{
						@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_thumb']);
					}
				}
				
				$file['file_storagetype'] = "db";
				break;
		}
		
		// Free some memory
		unset($upload);
		
		// Auto post?
		$file_new = 0;
		
		if( $type == 'new' )
		{
			$open = in_array( $catid, $this->catlib->member_access['auto'] ) ? 1 : 0;
			$file_new = $open ? 0 : 1;
		}
		else
		{
			if( $file['file_open'] == 0 )
			{
				$open = 0;
			}
			else
			{
				$open = in_array( $catid, $this->catlib->member_access['auto'] ) ? 1 : ($file['file_open'] == 1 && $this->ipsclass->vars['idm_allow_autoedit'] ? 1 : $file['file_open']);
			}
		}
			
		
		$save_array = array( 'file_name'		=> $file['file_name'],
							 'file_desc'		=> $file['file_desc'],
							 'file_cat'			=> $catid,
							 'file_open'		=> $open,
							 'file_filename'	=> $file['file_filename'],
							 'file_realname'	=> $file['file_realname'],
							 'file_ssname'		=> $file['file_ssname'],
							 'file_mime'		=> $file['file_mime'],
							 'file_ssmime'		=> intval($file['file_ssmime']),
							 'file_size'		=> $file['file_size'],
							 'file_thumb'		=> $file['file_thumb'],
							 'file_ipaddress'	=> $this->ipsclass->input['IP_ADDRESS'],
							 'file_storagetype'	=> $file['file_storagetype'],
							 'file_meta'		=> $soundex_save_string,
							 'file_updated'		=> time(),
							 'file_new'			=> $file_new,
							 'file_url'			=> $file['file_url'],
							 'file_ssurl'		=> $file['file_ssurl'],
							);
							
		$cat_stats = array();
		
		if( $category['ccfields'] )
		{
    		require_once( DL_PATH.'lib/lib_cfields.php' );
    		$fields = new lib_cfields();
    		$fields->ipsclass =& $this->ipsclass;
    	
    		$fields->file_id   	= $file['file_id'];
    		$fields->cat_id		= $category['ccfields'];
    		$fields->parser		=& $parser;
    		$fields->editor		=& $han_editor;
    		
    		$fields->cache_data = $this->ipsclass->cache['idm_cfields'];
    	
    		$fields->init_data();
    		$fields->parse_to_save();
    		
			if ( count( $fields->error_fields['empty'] ) )
			{
				if( $type == 'new' )
				{
					$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
				}
				
				$this->error_msg = $this->ipsclass->lang['addfile_error_cfield'];
				$this->continue_form( $type );
				return;
			}
		
			if ( count( $fields->error_fields['toobig'] ) )
			{
				if( $type == 'new' )
				{
					$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_files", 'where' => "file_id={$file['file_id']}" ) );
				}
				
				$this->error_msg = $this->ipsclass->lang['addfile_error_cfield1'];
				$this->continue_form( $type );
				return;
			}
			
			if ( count( $fields->out_fields ) )
			{
				$check = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'file_id', 'from' => 'downloads_ccontent', 'where' => 'file_id='.$file['file_id'] ) );
				
				if ( $check['file_id'] )
				{
					$this->ipsclass->DB->do_update( 'downloads_ccontent', $fields->out_fields, 'file_id='.$file['file_id'] );
				}
				else
				{
					$fields->out_fields['file_id'] = $file['file_id'];
					
					$this->ipsclass->DB->do_insert( 'downloads_ccontent', $fields->out_fields );
				}
			}			
		}		
		 
		if( $type == 'new' )
		{
			$save_array['file_submitted']	= time();
			$save_array['file_submitter']	= $this->ipsclass->member['id'];
			$save_array['file_placeholder'] = 0;
			
			$this->ipsclass->DB->do_update( "downloads_files", $save_array, "file_id=".$file['file_id'] );
			
			if( $save_array['file_open'] )
			{
				$cat_stats = array( 'total_files'	=> $category['cfileinfo']['total_files']+1,
									'date'			=> $save_array['file_submitted'],
									'mid'			=> $this->ipsclass->member['id'],
									'mname'			=> $this->ipsclass->member['members_display_name'],
									'fid'			=> $file['file_id'],
									'fname'			=> $save_array['file_name'] );
			}
			
			$author_name = $this->ipsclass->member['members_display_name'];
		}
		else
		{
			$this->ipsclass->DB->do_update( "downloads_files", $save_array, "file_id=".$file['file_id'] );
			
			if( $save_array['file_open'] )
			{
				$this->ipsclass->DB->query("SELECT members_display_name FROM ibf_members WHERE id={$file['file_submitter']}");
				$name = $this->ipsclass->DB->fetch_row();
				
				$cat_stats = array( 'date'			=> $save_array['file_updated'],
									'mid'			=> $file['file_submitter'],
									'mname'			=> $name['members_display_name'],
									'fid'			=> $file['file_id'],
									'fname'			=> $save_array['file_name'] );
									
				$author_name = $name['members_display_name'];
			}
		}
		
		if( count( $cat_stats ) )
		{
			$this->ipsclass->DB->do_update( "downloads_categories", array( 'cfileinfo' => $this->ipsclass->txt_safeslashes(serialize($cat_stats)) ), "cid=".$catid );
		}
		
		// Have to recursively rebuild the file info for parent cats
		// Also have to rebuild previous cat if editing a file cat
		$this->catlib->rebuild_fileinfo( $catid );
		
		if( $type == 'edit' AND $catid<>$file['file_cat'] )
		{
			$this->catlib->rebuild_fileinfo( $file['file_cat'] );
		}
		
		$this->catlib->rebuild_stats_cache();
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'ftp' )
		{
			@ftp_close( $conid );
			@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_filename']);
			
			if( $file['file_ssname'] )
			{
				@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_ssname']);
				
				if( $file['file_thumb'] )
				{
					@unlink($this->ipsclass->vars['upload_dir']."/".$file['file_thumb']);
				}				
			}
		}
		
		//---------------------------------------------------------
		// We need this for the topic posting, and subscription
		// notifications.  Let's instantiate it here.
		//---------------------------------------------------------
		
		require_once( ROOT_PATH."sources/classes/class_email.php" );
		$email = new emailer();
		$email->ipsclass =& $this->ipsclass;
		$email->email_init();
		
		
		require_once( DL_PATH.'lib/lib_topics.php' );
		$lib_topics = new lib_topics();
		$lib_topics->ipsclass 	=& $this->ipsclass;
		$lib_topics->email		=& $email;
		$lib_topics->parser		=& $parser;
		$lib_topics->han_editor	=& $han_editor;
		
		$file['file_submitter_name'] = $author_name;

		$lib_topics->sort_topic( array_merge( $file, $save_array ), $category, $type );
		
		if( $save_array['file_open'] )		
		{
			if( $type == 'edit' )
			{
				$members = array();
				
				if( !is_null($file['file_sub_mems']) AND $file['file_sub_mems'] != '' )
				{
					// Get rid of the extra commas
					$file['file_sub_mems'] = $this->ipsclass->clean_perm_string( trim($file['file_sub_mems']) );
					
					if( strlen($file['file_sub_mems']) > 0 )
					{
						$members = explode( ",", $file['file_sub_mems'] );
					}
					
					if ( ! is_array( $lang ) )
					{
						$lang = array();
					}
					
					if( file_exists( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" ) )
					{
						require_once( ROOT_PATH."cache/lang_cache/".$this->ipsclass->vars['default_language']."/lang_email_content.php" );
						
						if( count($members) )
						{
							foreach( $members as $v )
							{
								if( !$v OR $v == 0 )
								{
									continue;
								}
								
								$this->ipsclass->DB->build_query( array( 'select' => 'email, members_display_name', 'from' => 'members', 'where' => 'id='.$v ) );
								$this->ipsclass->DB->exec_query();
								
								$row = $this->ipsclass->DB->fetch_row();
								
								$email->template = stripslashes($lang['header']) . stripslashes($this->ipsclass->lang['subsription_notifications']) . stripslashes($lang['footer']);
									
								$email->build_message( array(
																	'NAME'  		=> $row['members_display_name'],
																	'AUTHOR'		=> $this->ipsclass->member['members_display_name'],
																	'TITLE' 		=> $file['file_name'],
																	'FILE_ID'		=> $file['file_id'],
																  )
								        					);
								        					
								$email->subject = sprintf( $this->ipsclass->lang['sub_notice_subject'], $file['file_name'] );
								$email->to      = $row['email'];
									
								$email->send_mail();
							}
						}
					}
				}
			}
		}
						
					
		//-----------------------------------------
		// Still here?  Wippii...success
		//-----------------------------------------
		
		$lang = $save_array['file_open'] ? $this->ipsclass->lang['submission_live'] : $this->ipsclass->lang['submission_approve'];
		$url  = "showfile={$file['file_id']}"; //$save_array['file_open'] ? "showfile={$file['file_id']}" : "showcat={$catid}";
		
		$this->ipsclass->print->redirect_screen( $lang, "autocom=downloads&".$url );
	}	
	
	
    /*-------------------------------------------------------------------------*/
    // Start Form - Choose category
    /*-------------------------------------------------------------------------*/
    	
	function start_form( $type='new' )
	{
		if( $type == 'edit' )
		{
			$file_id = intval($this->ipsclass->input['id']);
			
			$this->ipsclass->DB->build_query( array( 'select' 	=> 'file_id, file_cat, file_name, file_submitter',
												 	 'from'		=> 'downloads_files',
												 	 'where'	=> 'file_id='.$file_id
										)		);
			$this->ipsclass->DB->exec_query();
			
			$file = $this->ipsclass->DB->fetch_row();
			
			$canedit = 0;
			
			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$canedit = 1;
				$this->ismod = 1;
			}
			
			if( $this->ipsclass->vars['idm_allow_edit'] )
			{
				$canedit = 1;
			}
			
			if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
					}
				}
			}			
			
			if( !$canedit )
			{
				$this->funcs->produce_error( 'no_permitted_categories' );
				return;
			}			
			
			if( $this->ipsclass->member['id'] != $file['file_submitter'] AND !$this->ismod )
			{
				$this->funcs->produce_error( 'not_your_file' );
				return;
			}
						
			$file['code'] = 'edit_main';
		}
		else
		{
			$file = array( 'code' => 'add_cont' );
		}
		
		$default_catid = $this->ipsclass->input['cid'] ? intval($this->ipsclass->input['cid']) :
								( $file['file_cat'] ? $file['file_cat'] : 0 );
			
		if( count($this->catlib->member_access['add']) == 0 )
		{
			if( $type == 'new' )
			{
				$this->funcs->produce_error( 'no_addfile_permissions' );
				return;
			}
			else
			{
				$category_selector[] = array( $file['file_cat'], $this->catlib->cat_lookup[$file['file_cat']]['cname'] );
			}
		}
		else
		{
			$category_selector = $this->catlib->cat_jump_list( 1, 'add' );
		}
		
		$drop_down_menu = "<option value='0'>{$this->ipsclass->lang['idm_cat_selectcat']}</option>";
		
		foreach( $category_selector as $key => $real_array )
		{
			$selected = "";
			if( $real_array[0] == $default_catid )
			{
				$selected = " selected='selected'";
			}
			
			$drop_down_menu .= "<option value='{$real_array[0]}'{$selected}{$real_array[2]}>{$real_array[1]}</option>";
		}
					

		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_submit']->submission_start( $drop_down_menu, $file );

		$this->nav[] = $this->ipsclass->lang['file_submit_nav_header'];
		$this->page_title .= " -> ".$this->ipsclass->lang['file_submit_nav_header'];
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Continue Form - Main information form
    /*-------------------------------------------------------------------------*/
    	
	function continue_form( $type='new' )
	{
		if( $type == 'new' )
		{
			$catid = intval($this->ipsclass->input['file_cat']);
			if( !$catid )
			{
				$this->start_form( $type );
				return;
			}
		}
		
		if( $type == 'edit' )
		{
			$file_id = intval($this->ipsclass->input['id']);
			
			$this->ipsclass->DB->build_query( array( 'select' 	=> '*',
												 	 'from'		=> 'downloads_files',
												 	 'where'	=> 'file_id='.$file_id
										)		);
			$this->ipsclass->DB->exec_query();
			
			$file = $this->ipsclass->DB->fetch_row();
			
			$canedit = 0;
			
			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$canedit = 1;
				$this->ismod = 1;
			}
			
			if( $this->ipsclass->vars['idm_allow_edit'] )
			{
				$canedit = 1;
			}
			
			if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
								$this->ismod = 1;
							}
						}
					}
				}
			}			
			
			if( !$canedit )
			{
				$this->funcs->produce_error( 'no_permitted_categories' );
				return;
			}			
			
			if( $this->ipsclass->member['id'] != $file['file_submitter'] AND !$this->ismod )
			{
				$this->funcs->produce_error( 'not_your_file' );
				return;
			}
			
			$file['code'] = 'edit_comp';
			$file['button'] = $this->ipsclass->lang['edit_button'];
			$file['header_lang'] = $this->ipsclass->lang['sform_editfile_header'];
			$catid = $file['file_cat'];
			
			if( $this->ipsclass->input['file_cat'] )
			{
				// We just edited the category
				$catid = $this->ipsclass->input['file_cat'];
			}
			
		}
		else
		{
			$file = array( 'code' 			=> 'add_comp',
							'button' 		=> $this->ipsclass->lang['add_button'],
							'header_lang' 	=> $this->ipsclass->lang['sform_addfile_header'],
							'file_name'		=> $this->ipsclass->input['file_name'],
							'file_url'		=> $this->ipsclass->input['file_url'],
							'file_ssurl'	=> $this->ipsclass->input['file_ssurl'],
							'file_desc'		=> $_POST['Post'] );
		}

		$category = $this->catlib->cat_lookup[$catid];		
		
		if( count($this->catlib->member_access['add']) == 0 )
		{
			if( $type == 'new' )
			{
				$this->funcs->produce_error( 'no_addfile_permissions' );
				return;
			}
		}
		else if( !in_array( $catid, $this->catlib->member_access['add'] ) )
		{
			if( $category['coptions']['opt_noperm_add'] )
			{
				$this->funcs->produce_error( $category['coptions']['opt_noperm_add'], 1 );
			}
			else
			{
				$this->funcs->produce_error( 'no_addthiscat_permissions' );
			}
			return;
		}			
		
		if( $type == 'edit' )
		{
			$file['edit_cat_link'] = sprintf( $this->ipsclass->lang['sform_editcat'], $category['cname'] );
		}
		
		$file['file_cat'] = $catid;
		
		$file['cat_cname'] = $category['cname'];
		
		//-----------------------------------------
		// Get Mime-Type mask, and it's types
		//-----------------------------------------
		
		$file['allowed_file'] 	= "";
		$file['allowed_ss']	= "";
		$types = array( 'files'	=> array(),
						'ss'	=> array() );
		
		if( is_array($this->ipsclass->cache['idm_mimetypes']) AND count( $this->ipsclass->cache['idm_mimetypes'] ) > 0 )
		{
			foreach( $this->ipsclass->cache['idm_mimetypes'] as $k => $v )
			{
				$addfile = explode( ",", $v['mime_file'] );
				if( in_array( $category['coptions']['opt_mimemask'], $addfile ) )
				{
					$types['files'][] = $v['mime_extension'];
				}
				
				$addss = explode( ",", $v['mime_screenshot'] );
				if( in_array( $category['coptions']['opt_mimemask'], $addss ) )
				{
					$types['ss'][] = $v['mime_extension'];
				}
			}
		}
		
		natcasesort($types['files']);
		natcasesort($types['ss']);
		
		$file['allowed_file'] 	= implode( ", ", $types['files'] );
		$file['allowed_ss'] 	= implode( ", ", $types['ss'] );
		
		$file['require_ss']	= $category['coptions']['opt_reqss'];
		
		if( $category['coptions']['opt_maxss'] >= 1024 )
		{
			$file['ss_maxsize']	= round($category['coptions']['opt_maxss']/1024, 2) . $this->ipsclass->lang['sform_mb'];
		}
		else
		{
			$file['ss_maxsize']	= $category['coptions']['opt_maxss'] . $this->ipsclass->lang['sform_kb'];
		}
		
		if( $category['coptions']['opt_maxfile'] >= 1024 )
		{
			$file['file_maxsize']	= round($category['coptions']['opt_maxfile']/1024, 2) . $this->ipsclass->lang['sform_mb'];
		}
		else
		{
			$file['file_maxsize']	= $category['coptions']['opt_maxfile'] . $this->ipsclass->lang['sform_kb'];
		}		
		
		$file['bbcode_legend'] = sprintf( $this->ipsclass->lang['submit_bbcode_legend'], 
										( $category['coptions']['opt_bbcode'] == 1 ? "" : $this->ipsclass->lang['submit_not_legend'] ) );
		
		$file['html_legend'] 	= sprintf( $this->ipsclass->lang['submit_html_legend'], 
										( $category['coptions']['opt_html'] == 1 ? "" : $this->ipsclass->lang['submit_not_legend'] ) );										


		$file['ss_bar']		= $category['coptions']['opt_allowss'] == 1 ?
										$this->ipsclass->compiled_templates['skin_downloads_submit']->screenshot_submit( $file ) : "";

		$groups = explode( ",", $this->ipsclass->vars['idm_groups_link'] );
		
		$can_do_it = 0;
		
		$my_groups = array( $this->ipsclass->member['mgroup'] );
		
		if( $this->ipsclass->member['mgroup_others'] )
		{
			$o_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
			
			$my_groups = array_merge( $my_groups, $o_mgroups );
		}
		
		foreach( $my_groups as $groupid )
		{
			if( in_array( $groupid, $groups ) )
			{
				$can_do_it = 1;
				break;
			}
		}
					
		if( $can_do_it == 1 )
		{	
			$file['ss_url_bar']	= ( $this->ipsclass->vars['idm_allow_urls'] == 1 AND $category['coptions']['opt_allowss'] == 1 ) ?
											$this->ipsclass->compiled_templates['skin_downloads_submit']->text_input( 'file_ssurl', 'sform_filessurl', $file ) : "";

			$file['allow_link'] = 1;
		}

		if( $this->ipsclass->vars['idm_allow_path'] )
		{
			$groups = explode( ",", $this->ipsclass->vars['idm_path_users'] );
			
			$can_do_it = 0;
			
			$my_groups = array( $this->ipsclass->member['mgroup'] );
			
			if( $this->ipsclass->member['mgroup_others'] )
			{
				$o_mgroups = explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) );
				
				$my_groups = array_merge( $my_groups, $o_mgroups );
			}
			
			foreach( $my_groups as $groupid )
			{
				if( in_array( $groupid, $groups ) )
				{
					$can_do_it = 1;
					break;
				}
			}
		
			if( $can_do_it == 1 )
			{
				$file['ss_path_bar']	= ( $this->ipsclass->vars['idm_allow_path'] == 1 AND $category['coptions']['opt_allowss'] == 1 ) ?
												$this->ipsclass->compiled_templates['skin_downloads_submit']->text_input( 'file_sspath', 'sform_filesspath', $file ) : "";

				$file['path_bar']	= ( $this->ipsclass->vars['idm_allow_path'] == 1 ) ?
												$this->ipsclass->compiled_templates['skin_downloads_submit']->text_input( 'file_path', 'sform_filepath', $file ) : "";
			}
		}
										
		$file['file_name'] = $this->ipsclass->input['file_name'] ? $this->ipsclass->input['file_name'] : $file['file_name'];
		$file['Post'] = $_POST['Post'] ? $_POST['Post'] : $file['file_desc'];

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
		
		if( $han_editor->method == 'rte' )
		{
			$file['Post'] = $parser->convert_ipb_html_to_html( $file['Post'] );
		}
		else
		{
			$parser->parse_html    = $category['coptions']['opt_html'] ? 1 : 0;
			$parser->parse_nl2br   = 1;
			$parser->parse_smilies = 1;
			$parser->parse_bbcode  = $category['coptions']['opt_bbcode'] ? 1 : 0;
			
			$file['Post'] = $parser->pre_edit_parse( $file['Post'] );
		}
		
		$editor_html = $han_editor->show_editor( $file['Post'], 'Post' );

    	$cfield_output = "";
    	
		if( $category['ccfields'] )
		{
    		require_once( DL_PATH.'lib/lib_cfields.php' );
    		$fields = new lib_cfields();
    		$fields->ipsclass =& $this->ipsclass;
    	
    		$fields->file_id   	= $file['file_id'];
    		$fields->cat_id		= $category['ccfields'];
    		$fields->parser		=& $parser;
    		$fields->editor		=& $han_editor;    		
    		
    		$fields->cache_data = $this->ipsclass->cache['idm_cfields'];
    	
    		$fields->init_data();
    		$fields->parse_to_edit();
    		
    		foreach( $fields->out_fields as $id => $data )
    		{
    			if ( $fields->cache_data[ $id ]['cf_type'] == 'drop' )
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_dropdown( 'field_'.$id, $data );
				}
				else if ( $fields->cache_data[ $id ]['cf_type'] == 'area' )
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_textarea( 'field_'.$id, $data );
				}
				else
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_textinput( 'field_'.$id, $data );
				}
			
				$form_fields[] = array( 'name' => $fields->field_names[ $id ], 'desc' => $fields->field_desc[ $id ], 'form_element' => $form_element, 'req' => $fields->cache_data[ $id ]['cf_not_null'] );
    		}
    		
    		$cfield_display = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_wrapper( $form_fields );
		}
		
		$file['unique_id'] = md5( uniqid( microtime(), true ) );
		
		//-----------------------------------------
		// Force a form action?
		//-----------------------------------------
		
		$is_reset = 0;
		
		if( $this->ipsclass->vars['upload_domain'] )
		{
			$is_reset = 1;
			$original = $this->ipsclass->base_url;
			
			if( $this->ipsclass->session_type == 'cookie' )
			{
				$this->ipsclass->base_url = $this->ipsclass->vars['upload_domain'] . '/index.' . $this->ipsclass->vars['php_ext'].'?';
			}
			else
			{
				$this->ipsclass->base_url = $this->ipsclass->vars['upload_domain'] . '/index.' . $this->ipsclass->vars['php_ext'].'?s='.$this->ipsclass->session_id.'&amp;';
			}
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_submit']->main_submit_form( $file, $cfield_display );
		
		$this->output = str_replace( "<!--FILE_DESCRIPTION-->", $editor_html, $this->output );
		
		//-----------------------------------------
		// Reset forced form action?
		//-----------------------------------------
		
		if( $is_reset )
		{
			$this->ipsclass->base_url = $original;
		}
		
		if( $this->error_msg )
		{
			$this->output = str_replace( "<!--ERROR_MSG-->", $this->ipsclass->compiled_templates['skin_downloads_submit']->submit_error_box( $this->error_msg ), $this->output );
		}
	}
	

    
	function _get_file_no_extension($file)
	{
		return strtolower( str_replace( ".", "", substr( $file, 0, (strrpos( $file, '.' )) ) ) );
	}
	
	
	function _obtain_remote_size( $url="" )
	{
		if( !$url )
		{
			return 0;
		}
		
		if( !parse_url( $url ) )
		{
			return 0;
		}
		else
		{
			$url_bits = parse_url( $url );
		}
		
		if( function_exists( 'curl_init' ) )
		{
			ob_start();
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			
			$ok = curl_exec($ch);
			curl_close($ch);
			
			$head = ob_get_contents();
			ob_end_clean();
			
			preg_match( '/Content-Length:\s([0-9].+?)\s/', $head, $matches );
			
			return isset($matches[1]) ? $matches[1] : 0;
		}
		else
		{
			$socket_connection = @fsockopen( $url_bits['host'], 80 );
			
			if( !$socket_connection )
			{
				return 0;
			}
			
			$head = "HEAD $url HTTP/1.0\r\nConnection: Close\r\n\r\n";
			
			fwrite( $socket_connection, $head );
			
   			$i			= 0;
   			$results 	= "";
   			
   			while( true && $i<20 )
   			{
	   			if( $i >= 20 )
	   			{
		   			$results = "";
		   			break;
	   			}
	   			
       			$s = fgets( $socket_connection, 4096 );
       
       			$results .= $s;

       			if( strcmp( $s, "\r\n" ) == 0 || strcmp( $s, "\n" ) == 0 )
       			{
           			break;
       			}
       
       			$i++;
   			}
   
			fclose( $socket_connection );
			
			preg_match( '/Content-Length:\s([0-9].+?)\s/', $results, $matches );
			
			return isset($matches[1]) ? $matches[1] : 0;
		}
	}
	
}

?>