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
|   > Comments Script - Add/Edit/Delete/Approve/Display
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

class idm_comments
{
	var $ipsclass;
	var $catlib;

	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";

	var $error_msg;
	
	var $han;
	var $parser;
	var $custom_fields;
	
	var $mod 			= 0;
	
	var $quoted_pids	= array();

    /*-------------------------------------------------------------------------*/
	// Our run_me function
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
		if( $this->ipsclass->member['g_is_supmod'] )
		{
			$this->mod = 1;
		}
		
		// Can we see any categories?
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_downloads_permissions' );
		}
		else
		{
			switch( $this->ipsclass->input['code'] )
			{
				case 'add_comment':
					$this->add_comment();
				break;
				case 'delcomment':
					$this->delete_comment();
				break;
				case 'appcomment':
					$this->app_comment();
				break;
				case 'fulledit':
					$this->full_edit();
				break;
				case 'reportcomment':
					$this->report_comment_form();
				break;
				case 'doreportcomment':
					$this->report_comment_save();
				break;
					
				case 'pop_com':
					require_once( DL_PATH . 'lib/lib_comments.php' );
					
					$comments 			= new lib_comments();
					$comments->ipsclass =& $this->ipsclass;
					$comments->catlib	=& $this->catlib;
					$comments->funcs	=& $this->funcs;
					$comments->init();
					
					// For comment link in popup
					$this->ipsclass->input['showfile'] = $this->ipsclass->input['file'];
					
					$output = $comments->return_file_comments($this->ipsclass->input['file']);

					if( !$output )
					{
						$output = $this->ipsclass->compiled_templates['skin_downloads']->no_comments();
					}
					
					$output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( 0,0,'',1,0 );
					
					$sqr 	= isset($this->ipsclass->member['_cache']['qr_open']) ? $this->ipsclass->member['_cache']['qr_open'] : 0;
					$show 	= $sqr == 1 ? '' : 'none';
					
					$this->ipsclass->input['showfile'] = $this->ipsclass->input['file'];
								
					$output = str_replace( '<!--IBF.QUICK_REPLY_OPEN-->' 	, $this->ipsclass->compiled_templates[ 'skin_downloads' ]->quick_reply_box_open( $show, $this->ipslcass->md5_check ), $output );
					$output = str_replace( '<!--IBF.POPUP_CSS-->' 			, $this->ipsclass->compiled_templates['skin_global']->get_rte_css(), $output );
					
					$this->ipsclass->print->pop_up_window( $this->ipsclass->lang['comments_titlebar'], $output );
					exit;
				break;
				
				case 'add_form':
				default:
					$this->add_form();
				break;
			}
		}
		
		// Print the output
    	$this->ipsclass->print->add_output( $this->output );
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'NAV' => $this->nav ) );
	}
	
	
	/*-------------------------------------------------------------------------*/
    // Process reported comment
    /*-------------------------------------------------------------------------*/
    
	function report_comment_save()
	{		
		if( ! $this->ipsclass->input['comment'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}
		
		if( $this->ipsclass->vars['disable_reportpost'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}
		
		if( !$this->ipsclass->vars['idm_guest_report'] AND $this->ipsclass->member['id'] == 0 )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission') );
		}
		
		$comment = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'c.comment_id', 
																	 'from' 	=> array( 'downloads_comments' => 'c' ), 
																	 'where' 	=> 'c.comment_id=' . intval($this->ipsclass->input['comment'] ),
																	 'add_join'	=> array(
																	 					array( 'select'	=> 'f.file_id,f.file_cat, f.file_name',
																	 							'from'	=> array( 'downloads_files' => 'f' ),
																	 							'where'	=> 'f.file_id=c.comment_fid',
																	 							'type'	=> 'left'
																	 						)
																	 					)
															)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		else if( ! in_array( $comment['file_cat'], $this->catlib->member_access['view'] ) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		
		if( !$this->ipsclass->input['message'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'complete_form' ) );	
		}

		$mods = array();
		
		$this->ipsclass->DB->cache_add_query( 'get_mod_groups', array( 'where' => $q ), 'sql_idm_queries' );
		$this->ipsclass->DB->cache_exec_query();
		
		while( $i = $this->ipsclass->DB->fetch_row() )
		{
			$mods[] = $i;
		}

		if( count($mods) )
		{
			if( $this->ipsclass->vars['idm_reports'] == 'email' )
			{
			 	require ROOT_PATH . "sources/classes/class_email.php";		
				$this->email 				= new emailer();
				$this->email->html_email 	= 1;
				$this->email->ipsclass 		=& $this->ipsclass;
				$this->email->email_init();
			}
			else
			{
				require_once( ROOT_PATH . 'sources/lib/func_msg.php' );
				$this->lib 				= new func_msg();
				$this->lib->ipsclass 	=& $this->ipsclass;
				$this->lib->init();
			}
		
			$report = trim( stripslashes( $this->ipsclass->input['message'] ) );

			$st 	= intval($this->ipsclass->input['st']) > 0 ? intval($this->ipsclass->input['st']) : 0;
		
			foreach( $mods as $data )
			{
				$message = $this->ipsclass->lang['report_comment_email'];
	
				$message = str_replace( '<#MODNAME#>'	, $data['members_display_name']		 				, $message );
				$message = str_replace( '<#SENDERNAME#>', $this->ipsclass->member['members_display_name']	, $message );		
				$message = str_replace( '<#LINK#>'	 	, "<a href='{$this->ipsclass->vars['board_url']}/index.{$this->ipsclass->vars['php_ext']}"."?autocom=downloads&showfile={$comment['file_id']}&st={$st}#comment{$comment['comment_id']}'>{$this->ipsclass->lang['link_to_comment']}</a>", $message );			
				$message = str_replace( '<#REPORT#>'	, $report											, $message );
				
				if( $this->ipsclass->vars['idm_reports'] == 'email' )
				{
					$this->email->to	 	= $data['email'];
					$this->email->from		= $this->ipsclass->member['email'];
					$this->email->subject	= $this->ipsclass->lang['comment_reported'].' '.$this->ipsclass->vars['board_name'];
					$this->email->message	= $message;
					$this->email->send_mail();			
				}
				else
				{
					$this->lib->postlib->parser->parse_nl2br 	= 1;
					$this->lib->postlib->parser->parse_bbcode	= 1;
					$this->lib->postlib->parser->parse_smilies	= 1;
					$this->lib->postlib->parser->parse_html		= 0;

					$this->lib->to_by_id	= $data['id'];
	 				$this->lib->from_member = $this->ipsclass->member;
	 				$this->lib->msg_title	= $this->ipsclass->lang['comment_reported'].' '.$comment['file_name'];
					$this->lib->msg_post	= $this->lib->postlib->parser->pre_db_parse( $this->lib->postlib->parser->pre_display_parse( $this->lib->postlib->han_editor->class_editor->_clean_post( $this->lib->postlib->parser->pre_edit_parse( $message ) ) ) );
					$this->lib->force_pm	= 1;
					
					$this->lib->send_pm();
					
					if ( $this->lib->error )
					{
						print $this->error;
						exit();
					}				
				}
			}
		}
		
		$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['report_redirect_comment'], "autocom=downloads&amp;showfile={$comment['file_id']}&amp;st={$st}" );
	}       
    

	/*-------------------------------------------------------------------------*/
    // Report comment form
    /*-------------------------------------------------------------------------*/
    
	function report_comment_form()
	{		
		if( ! $this->ipsclass->input['comment'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}
		
		if( $this->ipsclass->vars['disable_reportpost'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}
		
		if( !$this->ipsclass->vars['idm_guest_report'] AND $this->ipsclass->member['id'] == 0 )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission') );
		}
		
		$comment = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'c.comment_id', 
																	 'from' 	=> array( 'downloads_comments' => 'c' ), 
																	 'where' 	=> 'c.comment_id=' . intval($this->ipsclass->input['comment'] ),
																	 'add_join'	=> array(
																	 					array( 'select'	=> 'f.file_cat',
																	 							'from'	=> array( 'downloads_files' => 'f' ),
																	 							'where'	=> 'f.file_id=c.comment_fid',
																	 							'type'	=> 'left'
																	 						)
																	 					)
															)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		else if( ! in_array( $comment['file_cat'], $this->catlib->member_access['view'] ) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}

		$this->ipsclass->load_template( 'skin_downloads_submit' );
		
		$comment['st']	= $this->ipsclass->input['st'];

		$this->output .= $this->ipsclass->compiled_templates['skin_downloads_submit']->report_comment_form( $comment );
		
		$this->nav[] 		= "<a href='".$this->ipsclass->base_url."autocom=downloads'>{$this->ipsclass->lang['idm_header']}</a>";
		$this->nav[] 		= $this->ipsclass->lang['report_comment_page'];
		$this->page_title 	= $this->ipsclass->lang['report_comment_page'];
	}    
    
	/*-------------------------------------------------------------------------*/
    // Add comment form
    /*-------------------------------------------------------------------------*/
    
    function add_form( )
    {
		$id = intval($this->ipsclass->input['id']);
		
		if( !$id )
		{
			$this->output .= $this->funcs->produce_error( 'file_not_found' );
			return;
		}
		
		//-----------------------------------------
		// Load the file
		//-----------------------------------------
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'file_cat, file_id, file_name',
												 				  'from'	=> 'downloads_files',
												 				  'where'	=> 'file_id=' . $id
														)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		else if( ! in_array( $file['file_cat'], $this->catlib->member_access['view'] ) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		
		if( count($this->catlib->member_access['comment']) == 0 OR !in_array($file['file_cat'], $this->catlib->member_access['comment']) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}		    
		
	    $this->page_title 	= sprintf( $this->ipsclass->lang['add_comments_pt'], $file['file_name'] );
	    $this->nav[] 		= sprintf( $this->ipsclass->lang['add_comments_pt'], $file['file_name'] );
		
		$this->ipsclass->load_language( 'lang_post' );

		//-----------------------------------------------------------
		// Load the parser
		//-----------------------------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
        $this->parser                      	=  new parse_bbcode();
        $this->parser->ipsclass            	=& $this->ipsclass;
        $this->parser->allow_update_caches 	=  1;
        $this->parser->parse_html    		=  0;
        $this->parser->parse_nl2br   		=  1;
        $this->parser->parse_smilies 		=  $comment['use_emo'];
        $this->parser->parse_bbcode  		=  1;
        $this->parser->bypass_badwords 		=  intval($this->ipsclass->member['g_bypass_badwords']);
        
		//-----------------------------------------------------------
		// Load the editor
		//-----------------------------------------------------------

        require_once( ROOT_PATH."sources/handlers/han_editor.php" );
        $this->han_editor           = new han_editor();
        $this->han_editor->ipsclass =& $this->ipsclass;
        $this->han_editor->init();
	        
		if ( isset($this->post_errors) AND $this->post_errors )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->error_box( $this->ipsclass->lang[ $this->post_errors ], 1 );
		}
		        
		$raw_post = $this->check_multi_quote(); 
		
       	$editor_html = $this->han_editor->show_editor( $raw_post, 'Post' );

       	$file['use_emo']							= 1;
       	$file['use_sig']							= 1;
       	$this->ipsclass->member['g_append_edit']	= 0;

		$this->output .= $this->ipsclass->compiled_templates[ 'skin_downloads' ]->comment_form( $file['file_id'], $file, $editor_html );
		
		if( $this->ipsclass->member['id'] == 0 AND $this->ipsclass->vars['guest_captcha'] )
		{
			$this->output = str_replace( "<!--GUEST.CAPTCHA-->", $this->ipsclass->compiled_templates['skin_downloads']->guest_captcha(), $this->output );
			
			$imgid = md5( uniqid(microtime()) );

			if( $this->ipsclass->vars['bot_antispam'] == 'gd' )
			{
				//-----------------------------------------
				// Get 6 random chars
				//-----------------------------------------
								
				$img_code = strtoupper( substr( md5($imgid), 0, 6 ) );
			}
			else
			{
				//-----------------------------------------
				// Set a new 6 character numerical string
				//-----------------------------------------
				
				mt_srand ((double) microtime() * 1000000);
				
				$img_code = mt_rand(100000,999999);
			}			
			
			$this->ipsclass->DB->do_insert( 'reg_antispam', array (
												   'regid'      => $imgid,
												   'regcode'    => $img_code,
												   'ip_address' => $this->ipsclass->input['IP_ADDRESS'],
												   'ctime'      => time(),
									   )       );
									   
			if( $this->ipsclass->vars['guest_captcha'] == 'gd' )
			{
				$this->output = str_replace( "<!--CAPTCHA.IMAGE-->", $this->ipsclass->compiled_templates['skin_downloads']->bot_antispam_gd( $imgid ), $this->output );
			}
			else if ( $this->ipsclass->vars['guest_captcha'] == 'gif' )
			{
				$this->output = str_replace( "<!--CAPTCHA.IMAGE-->", $this->ipsclass->compiled_templates['skin_downloads']->bot_antispam_gif( $imgid ), $this->output );
			}
		}	

		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$this->ipsclass->print->pop_up_window( $this->ipsclass->lang['comments_titlebar'], $this->output );
		}
	}	
	
	/*-------------------------------------------------------------------------*/
    // Edit comment form
    /*-------------------------------------------------------------------------*/
    
    function full_edit( )
    {
	    $this->page_title 	= $this->ipsclass->lang['comments_pt'];
	    $this->nav[] 		= $this->ipsclass->lang['comments_pt'];
	    
		$id = intval($this->ipsclass->input['cid']);
		
		if( !$id )
		{
			$this->output .= $this->funcs->produce_error( 'no_comment_foredit' );
			return;
		}
		
		if( $this->mod )
		{
			$limiter  = "";
			$limiter2 = "";
		}
		else
		{
			$limiter  = " AND comment_open=1";
			$limiter2 = " AND c.comment_open=1";
		}
		
		//-----------------------------------------
		// Load profile lib
		//-----------------------------------------
		
		$comment = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'c.*',
												 					 'from'		=> array( 'downloads_comments' => 'c' ),
												 					 'add_join' => array( 
												 					 					array( 'select' => 'f.file_cat, f.file_id',
												 												'from' 	=> array( 'downloads_files' => 'f' ),
												 												'where' => 'f.file_id=c.comment_fid',
												 												'type' 	=> 'left',
												 											 )
												 										),
												 'where'	=> 'c.comment_id=' . $id
										)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		else if( ! in_array( $comment['file_cat'], $this->catlib->member_access['view'] ) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		
		if( !$this->mod )		
		{
			if( is_array( $this->catlib->cat_mods[ $comment['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $comment['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $comment['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcancomments'] )
							{
								$this->mod = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcancomments'] )
							{
								$this->mod = 1;
							}
						}
					}
				}
			}
		}
		
		$this->ipsclass->load_language( 'lang_post' );

		//-----------------------------------------------------------
		// Load the parser
		//-----------------------------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
        $this->parser                      	=  new parse_bbcode();
        $this->parser->ipsclass            	=& $this->ipsclass;
        $this->parser->allow_update_caches 	=  1;
        $this->parser->parse_html    		=  0;
        $this->parser->parse_nl2br   		=  1;
        $this->parser->parse_smilies 		=  $comment['use_emo'];
        $this->parser->parse_bbcode  		=  1;
        $this->parser->bypass_badwords 		=  intval($this->ipsclass->member['g_bypass_badwords']);
        
		//-----------------------------------------------------------
		// Load the editor
		//-----------------------------------------------------------

        require_once( ROOT_PATH."sources/handlers/han_editor.php" );
        $this->han_editor           = new han_editor();
        $this->han_editor->ipsclass =& $this->ipsclass;
        $this->han_editor->init();
	        
		//-----------------------------------------
		// Unconvert the saved post if required
		//-----------------------------------------
		
		if ( ! isset($_POST['Post']) )
		{
			//-----------------------------------------
			// If we're using RTE, then just clean up html
			//-----------------------------------------
			
			if ( $this->han_editor->method == 'rte' )
			{
				$comment['comment_text'] = $this->parser->convert_ipb_html_to_html( $comment['comment_text'] );
			}
			else
			{
				$comment['comment_text'] = $this->parser->pre_edit_parse( $comment['comment_text'] );
			}
		}
		else
		{
			if ( $this->ipsclass->input['_from'] == 'quickedit' )
			{
				$this->parser->parse_html    = 0;
				$this->parser->parse_nl2br   = 1;
				$this->parser->parse_smilies = $comment['use_emo'];
				$this->parser->parse_bbcode  = 1;

				if ( $this->han_editor->method == 'rte' )
				{
					$comment['comment_text'] = $this->parser->convert_std_to_rte( $this->ipsclass->txt_stripslashes( $_POST['Post'] ) );
					
					foreach( $this->ipsclass->skin['_macros'] as $row )
			      	{
						if ( $row['macro_value'] != "" )
						{
							$comment['comment_text'] = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $comment['comment_text'] );
						}
					}

					$comment['comment_text'] = str_replace( "<#IMG_DIR#>", $this->ipsclass->skin['_imagedir'], $comment['comment_text'] );
					$comment['comment_text'] = str_replace( "<#EMO_DIR#>", $this->ipsclass->skin['_emodir']  , $comment['comment_text'] );
				}
				else
				{
					$comment['comment_text'] = $this->ipsclass->txt_stripslashes( $_POST['Post'] );
				}
			}
			else
			{
				$comment['comment_text'] = $this->ipsclass->txt_stripslashes( $_POST['Post'] );
			}
		}
        
       	$editor_html = $this->han_editor->show_editor( $comment['comment_text'], 'Post' );

		$this->output .= $this->ipsclass->compiled_templates[ 'skin_downloads' ]->comment_form( $comment['file_id'], $comment, $editor_html );

		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$this->ipsclass->print->pop_up_window( $this->ipsclass->lang['comments_titlebar'], $this->output );
		}
	}	

	/*-------------------------------------------------------------------------*/
    // Add Comment
    /*-------------------------------------------------------------------------*/
    
    function add_comment()
    {
	    $id = intval($this->ipsclass->input['id']);

	    if( !$id )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																  'from'	=> 'downloads_files',
																  'where'	=> 'file_id=' . $id
														)		);
		
		if( !$file['file_id'] )
		{
			$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
	    
		if( count($this->catlib->member_access['comment']) == 0 OR !in_array($file['file_cat'], $this->catlib->member_access['comment']) )
		{
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['cannot_comment_file'], "autocom=downloads&req=comments&code=pop_com&file={$id}" );
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['cannot_comment_file'], "autocom=downloads&amp;showfile={$id}" );
			}
		}		    
	    
		//-----------------------------------------
		// Load and config the libraries
		//-----------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_editor.php" );
		$this->han 							= new han_editor();
		$this->han->ipsclass 				=& $this->ipsclass;
		$this->han->init();
				
		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
		$this->parser 						= new parse_bbcode();
		$this->parser->ipsclass 			=& $this->ipsclass;
		$this->parser->allow_update_caches 	= 1;
		$this->parser->bypass_badwords 		= intval($this->ipsclass->member['g_bypass_badwords']);	
		$this->parser->parse_html 			= 0;	
		$this->parser->parse_nl2br 			= 1;
		$this->parser->parse_smilies		= $this->ipsclass->input['enableemo'] == 'yes' ? 1 : 0;
		$this->parser->parse_bbcode			= 1;

		$comment = $this->parser->pre_db_parse( $this->han->process_raw_post( 'Post' ) );

		$db_str = array( 'comment_fid'		=> $id,
						 'comment_mid'		=> $this->ipsclass->member['id'],
						 'comment_date'		=> time(),
						 'comment_open'		=> $this->ipsclass->vars['idm_comment_approval'] == 0 ? 1 : 0,
						 'comment_text'		=> $comment,
						 'use_sig'			=> $this->ipsclass->input['enablesig'] == 'yes' ? 1 : 0,
						 'use_emo'			=> $this->ipsclass->input['enableemo'] == 'yes' ? 1 : 0,
						 'ip_address'		=> $this->ipsclass->ip_address,
						);
						
		if( $this->ipsclass->input['is_edit'] )
		{
			$cid = intval($this->ipsclass->input['is_edit']);
			
			$c = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*',
																   'from'	=> 'downloads_comments',
																   'where'	=> 'comment_id=' . $cid
														)		);
			
			if( !$c['comment_id'] )
			{
				$this->ipsclass->Error( array( 'MSG' => 'cannot_find_comment', 'LEVEL' => 1 ) );
		    }
		    
			//-----------------------------------------
			// Got permission?
			//-----------------------------------------
	
			if( !$this->mod )		
			{
				if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								if( $v['modcancomments'] )
								{
									$this->mod = 1;
								}
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								if( $v['modcancomments'] )
								{
									$this->mod = 1;
								}
							}
						}
					}
				}
			}
			
			if( $c['comment_mid'] == $this->ipsclass->member['id'] && $this->ipsclass->vars['idm_comment_edit'] )
			{
				$this->mod = 1;
			}
			
			if ( !$this->mod )
			{
				if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
				{
					$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['cannot_comment_file'], "autocom=downloads&req=comments&code=pop_com&file={$id}" );
				}
				else
				{
					$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['cannot_comment_file'], "autocom=downloads&amp;showfile={$id}" );
				}
			}
		    
		    $this->ipsclass->DB->do_update( "downloads_comments", array( 'comment_open'			=> $this->ipsclass->vars['idm_comment_approval'] == 0 ? 1 : 0, 
		    															 'comment_text'			=> $comment,
		    															 'comment_append_edit'	=> intval($this->ipsclass->input['add_edit']),
		    															 'comment_edit_time'	=> time(),
		    															 'comment_edit_name'	=> $this->ipsclass->member['members_display_name'],
		    															 'use_sig'				=> $this->ipsclass->input['enablesig'] == 'yes' ? 1 : 0,
						 												 'use_emo'				=> $this->ipsclass->input['enableemo'] == 'yes' ? 1 : 0,
		    															), "comment_id=".$cid );
		    
		    $this->funcs->rebuild_pend_comment_cnt( $file['file_id'] );
		    
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->print->redirect_screen( $db_str['comment_open'] == 1 ? $this->ipsclass->lang['comment_edit_approved'] : $this->ipsclass->lang['comment_edit_pending'] , "autocom=downloads&req=comments&code=pop_com&file={$id}" );
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $db_str['comment_open'] == 1 ? $this->ipsclass->lang['comment_edit_approved'] : $this->ipsclass->lang['comment_edit_pending'] , "autocom=downloads&amp;showfile={$id}" );
			}		    
	    }
	    else
	    {
		    $this->ipsclass->DB->do_insert( "downloads_comments", $db_str );
		    
		    $this->funcs->rebuild_pend_comment_cnt( $file['file_id'] );
	    
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->print->redirect_screen( $db_str['comment_open'] == 1 ? $this->ipsclass->lang['comment_approved'] : $this->ipsclass->lang['comment_pending'] , "autocom=downloads&req=comments&code=pop_com&file={$id}" );
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $db_str['comment_open'] == 1 ? $this->ipsclass->lang['comment_approved'] : $this->ipsclass->lang['comment_pending'] , "autocom=downloads&amp;showfile={$id}" );
			}		    
		}
    }
    

    
    
	/*-------------------------------------------------------------------------*/
    // Delete Comment
    /*-------------------------------------------------------------------------*/
    
    function delete_comment()
    {
	    $id = intval($this->ipsclass->input['cid']);
	    
	    if( !$id )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
	    if( $this->ipsclass->input['key'] != $this->ipsclass->return_md5_check() )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
		$this->ipsclass->DB->build_query( array( 'select'	=> 'c.*',
												 'from'		=> array( 'downloads_comments' => 'c' ),
												 'add_join' => array( 0 => array( 'select' => 'f.file_cat, f.file_id',
												 									'from' => array( 'downloads_files' => 'f' ),
												 									'where' => 'f.file_id=c.comment_fid',
												 									'type' => 'left',
												 					)			),
												 'where'	=> 'c.comment_id='.$id
										)		);
		$this->ipsclass->DB->exec_query();
		
		$file = $this->ipsclass->DB->fetch_row();
		
		if( !$file['file_id'] )
		{
			$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
	    $this->mod = $this->funcs->checkPerms( $file, 'modcancomments' );
	    
		if( !$this->mod )
		{
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['no_comments_perms'], "autocom=downloads&req=comments&code=pop_com&file={$file['file_id']}" );
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['no_comments_perms'], "autocom=downloads&amp;showfile={$file['file_id']}" );
			}			
		}		    
	    
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_comments', 'where' => 'comment_id='.$id ) );
		
		$this->funcs->rebuild_pend_comment_cnt( $file['file_id'] );
	    
		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_deleted'], "autocom=downloads&req=comments&code=pop_com&file={$file['file_id']}" );
		}
		else
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_deleted'] , "autocom=downloads&amp;showfile={$file['file_id']}" );
		}
    }
    
    
	/*-------------------------------------------------------------------------*/
    // Approve Comment
    /*-------------------------------------------------------------------------*/
    
    function app_comment()
    {
	    $id = intval($this->ipsclass->input['cid']);
	    
	    if( !$id )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
	    if( $this->ipsclass->input['key'] != $this->ipsclass->return_md5_check() )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
		$this->ipsclass->DB->build_query( array( 'select'	=> 'c.*',
												 'from'		=> array( 'downloads_comments' => 'c' ),
												 'add_join' => array( 0 => array( 'select' => 'f.file_cat, f.file_id, f.file_pendcomments',
												 									'from' => array( 'downloads_files' => 'f' ),
												 									'where' => 'f.file_id=c.comment_fid',
												 									'type' => 'left',
												 					)			),
												 'where'	=> 'c.comment_id='.$id
										)		);
		$this->ipsclass->DB->exec_query();
		
		$file = $this->ipsclass->DB->fetch_row();
		
		if( !$file['file_id'] )
		{
			$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
	    if( $file['comment_open'] == 1 )
	    {
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads&req=comments&code=pop_com&file={$file['file_id']}" );
			}
			else
			{
				$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads&showfile={$file['file_id']}" );
			}		    
	    }
	    	    
		$this->mod = $this->funcs->checkPerms( $file, 'modcancomments' );
	    
		if( !$this->mod )
		{
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['no_comments_perms'], "autocom=downloads&req=comments&code=pop_com&file={$file['file_id']}" );
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['no_comments_perms'], "autocom=downloads&amp;showfile={$file['file_id']}" );
			}
		}		    
	    
		$this->ipsclass->DB->do_update( 'downloads_comments', array( 'comment_open' => 1 ), 'comment_id='.$id );
		
		$this->funcs->rebuild_pend_comment_cnt( $file['file_id'] );	    
		
		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_manapproved'], "autocom=downloads&req=comments&code=pop_com&file={$file['file_id']}" );
		}
		else
		{
			$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['comment_manapproved'] , "autocom=downloads&amp;showfile={$file['file_id']}" );
		}		
    }
    
  
    function check_multi_quote()
	{
		$add_tags = 0;
		
		if ( ! $this->ipsclass->input['qpid'] )
		{
			$this->ipsclass->input['qpid'] = $this->ipsclass->my_getcookie('idm_pids');

			if ($this->ipsclass->input['qpid'] == ",")
			{
				$this->ipsclass->input['qpid'] = "";
			}
		}

		$this->ipsclass->input['qpid'] = preg_replace( "/[^,\d]/", "", trim($this->ipsclass->input['qpid']) );

		if ( $this->ipsclass->input['qpid'] )
		{
			$this->ipsclass->my_setcookie('idm_pids', ',', 0);
			
			$this->quoted_pids = preg_split( '/,/', $this->ipsclass->input['qpid'], -1, PREG_SPLIT_NO_EMPTY );

			//-----------------------------------------
			// Get the posts from the DB and ensure we have
			// suitable read permissions to quote them
			//-----------------------------------------
			
			if ( count($this->quoted_pids) )
			{
				$this->quoted_pids = $this->ipsclass->clean_int_array( $this->quoted_pids );

				$this->ipsclass->DB->build_query( array( 'select'	=> 'c.*',
														 'from'		=> array( 'downloads_comments' => 'c' ),
														 'where'	=> 'c.comment_id IN(' . implode( ',', $this->quoted_pids ) . ')',
														 'add_join'	=> array(
														 					array( 'select'	=> 'f.file_cat, f.file_id',
														 							'from'	=> array( 'downloads_files' => 'f' ),
														 							'where'	=> 'f.file_id=c.comment_fid',
														 							'type'	=> 'left'
														 						),
														 					array( 'select'	=> 'm.members_display_name',
														 							'from'	=> array( 'members' => 'm' ),
														 							'where'	=> 'm.id=c.comment_mid',
														 							'type'	=> 'left'
														 						),
														 					)
												)		);
				$q = $this->ipsclass->DB->exec_query();

				while ( $tp = $this->ipsclass->DB->fetch_row( $q ) )
				{
					if( ! in_array( $tp['file_cat'], $this->catlib->member_access['view'] ) )
					{
						continue;
					}

					if ( $this->han_editor->method == 'rte' )
					{
						$tmp_post = $this->parser->convert_ipb_html_to_html( $tp['comment_text'] );
					}
					else
					{
						$tmp_post = trim( $this->parser->pre_edit_parse( $tp['comment_text'] ) );
					}

					if( $this->ipsclass->vars['strip_quotes'] )
					{
						$tmp_post = preg_replace( "#\[QUOTE(=.+?,.+?)?\].+?\[/QUOTE\]#is", "", $tmp_post );

						$tmp_post = preg_replace( "#(?:\n|\r){3,}#s", "\n", trim($tmp_post) );
					}

					if ( $tmp_post )
					{
						if ( $this->han_editor->method == 'rte' )
						{
							$raw_post .= "[quote name='".$this->parser->make_quote_safe($tp['members_display_name'])."' date='".$this->parser->make_quote_safe($this->ipsclass->get_date( $tp['comment_date'], 'LONG', 1 ))."']<br />$tmp_post<br />[/quote]<br /><br /><br />";
						}
						else
						{
							$raw_post .= "[quote name='".$this->parser->make_quote_safe($tp['members_display_name'])."' date='".$this->parser->make_quote_safe($this->ipsclass->get_date( $tp['comment_date'], 'LONG', 1 ))."']\n$tmp_post\n[/quote]\n\n\n";
						}
					}
				}
				
				$raw_post = trim($raw_post)."\n";
			}
		}
		
		if ( isset( $this->ipsclass->input['Post'] ) )
		{
			//-----------------------------------------
			// Raw post from preview?
			//-----------------------------------------
		
			$raw_post .= isset($_POST['Post']) ? $this->ipsclass->txt_htmlspecialchars($_POST['Post']) : "";
	
			if (isset($raw_post))
			{
				$raw_post = $this->ipsclass->txt_raw2form($raw_post);
			}
		}

		return $raw_post;
	}	


}
?>