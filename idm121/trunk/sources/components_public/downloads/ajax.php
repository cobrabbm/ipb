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
|   > Download Script - AJAX Request Handler
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

class idm_ajax
{
	var $ipsclass;
	var $catlib;
	var $message;

	var $output;
	var $nav;
	var $page_title;

    /*-------------------------------------------------------------------------*/
	// Run me
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
		// Setup access permissions
		$this->catlib->get_member_cat_perms();
		
		//-----------------------------------------
		// Load ajax class
		//-----------------------------------------
		
		require_once( KERNEL_PATH . 'class_ajax.php' );

		$this->class_ajax           =  new class_ajax();
		$this->class_ajax->ipsclass =& $this->ipsclass;
    	$this->class_ajax->class_init();
		    	
    	switch( $this->ipsclass->input['code'] )
    	{
			case 'progressBar':
				$this->showProgressBar();
			break;
			
			case 'addVote':
				$this->addVote();
			break;
			
			case 'commentEdit':
				$this->commentEdit();
			break;
			
			case 'commentEditSave':
				$this->commentEditSave();
			break;
				
			default:
				$this->class_ajax->print_nocache_headers();
				$this->class_ajax->return_string( 'error' );
			break;
		}

		exit();
	}

    /*-------------------------------------------------------------------------*/
	// Return data for progress bar
    /*-------------------------------------------------------------------------*/

	function showProgressBar()
	{
		$pg_key		= $this->ipsclass->txt_md5_clean( $this->ipsclass->input['progress_key'] );
		
		if( !$pg_key )
		{
			$this->class_ajax->print_nocache_headers();
			$this->class_ajax->return_string( 'error' );
			exit();
		}
		
		if( !function_exists( 'apc_fetch' ) AND !function_exists( 'uploadprogress_get_info' ) )
		{
			$this->class_ajax->print_nocache_headers();
			$this->class_ajax->return_string( 'error' );
			exit();
		}
		
		$return = array();
		
		if( function_exists( 'apc_fetch' ) )
		{
			$status = apc_fetch( 'upload_'.$_GET['progress_key'] );
			
			$est_ttl = 0;
			
			$status['current']	= $status['current'] > 0 ? $status['current'] : 1;
			$status['total']	= $status['total'] > 0 ? $status['total'] : 1;
			
			if( $status['start_time'] )
			{
				$status['start_time'] 	= intval($status['start_time']);
				$cur_time				= time();
				$time_diff				= $cur_time - $status['start_time'];
				$rate					= $time_diff ? ($status['current']/$time_diff) : 1;
				$est_ttl				= @round( ($status['total'] - $status['current'])/ $rate );
			}

			$return	= array(
							0	=> $this->ipsclass->size_format( $status['total'] ), // Total size
							1	=> $this->ipsclass->size_format( $status['current'] ), // So far
							2	=> round( ( $status['rate'] ? $status['rate'] : $rate ) / 1024, 2 ), // Speed
							3	=> $est_ttl,  // Est time left
							4	=> round(100* ($status['current']/$status['total'])), // Percent finished
						   );
		}
		else
		{
			$status = uploadprogress_get_info( $_GET['progress_key'] );
			
			$status['bytes_total']		= $status['bytes_total'] > 0 ? $status['bytes_total'] : 1;
			$status['bytes_uploaded']	= $status['bytes_uploaded'] ? $status['bytes_uploaded'] : 1;

			$return	= array(
							0	=> $this->ipsclass->size_format( $status['bytes_total'] ), // Total size
							1	=> $this->ipsclass->size_format( $status['bytes_uploaded'] ), // So far
							2	=> round( $status['speed_average'] / 1024, 2 ), // Speed
							3	=> $status['est_sec'] , // Est time left
							4	=> round(100* ($status['bytes_uploaded']/$status['bytes_total'])), // Percent finished
						   );
		}

		$this->class_ajax->print_nocache_headers();
		$this->class_ajax->return_string( @implode( "||", $return ) );
	}
	
	
    /*-------------------------------------------------------------------------*/
	// Add vote to a file
    /*-------------------------------------------------------------------------*/
    
    function addVote()
    {
	    $id 	= intval($this->ipsclass->input['id']);
	    $vote 	= intval($this->ipsclass->input['vote']);
	    
	    $vote = $vote > 5 ? 5 : ( $vote < 1 ? 1 : $vote );

	    if( !$id OR !$vote )
	    {
		    $this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> '*', 'from'		=> 'downloads_files', 'where'	=> 'file_id=' . $id ) );
		
		if( !$file['file_id'] )
		{
			$this->ipsclass->boink_it( $this->ipsclass->base_url."autocom=downloads" );
	    }
	    
		if( count($this->catlib->member_access['rate']) == 0 OR !in_array($file['file_cat'], $this->catlib->member_access['rate']) )
		{
			if( $this->ipsclass->input['xml'] == 1 )
			{
				$this->class_ajax->return_string( "<i>".$this->ipsclass->lang['cannot_rate_file']."</i>" );
				exit;
			}
			else
			{
				$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['cannot_rate_file'], "autocom=downloads&amp;showfile={$id}" );
			}
		}
	    
	    $the_votes = array();
	    
	    if( $file['file_votes'] )
	    {
		    $the_votes = unserialize(stripslashes($file['file_votes']));
	    }
	    
	    if( is_array($the_votes) AND count($the_votes) > 0 )
	    {
		    if( !array_key_exists( $this->ipsclass->member['id'], $the_votes ) )
		    {
			    $the_votes[$this->ipsclass->member['id']] = $vote;
		    }
		    else
		    {
			    if( $this->ipsclass->input['xml'] == 1 )
			    {
				    foreach( $the_votes as $k => $v )
				    {
					    $num_votes++;
					    $vote_ttl += $v;
				    }
				    
				    if( $num_votes > 0 )
				    {
					    $final_vote = round($vote_ttl/$num_votes);
				    }

				    $this->class_ajax->return_html( $vote_ttl . ',' . $num_votes . ',' . $final_vote . ',' . 'new' );
				    exit;
			    }
			    else
			    {
			    	$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['already_voted'], "autocom=downloads&amp;showfile={$id}" );
		    	}
		    }
	    }
	    else
	    {
		    $the_votes[$this->ipsclass->member['id']] = $vote;
	    }
	    
	    $num_votes	= 0;
	    $vote_ttl	= 0;
	    $final_vote	= 0;
	    
	    foreach( $the_votes as $k => $v )
	    {
		    $num_votes++;
		    $vote_ttl += $v;
	    }
	    
	    if( $num_votes > 0 )
	    {
		    $final_vote = round($vote_ttl/$num_votes);
	    }
	    
	    $vote_string = addslashes(serialize($the_votes));
	    
	    $this->ipsclass->DB->do_update( "downloads_files", array( 'file_rating' => $final_vote, 'file_votes' => $vote_string ), "file_id=" . $id );
	    
	    if( $this->ipsclass->input['xml'] == 1 )
	    {
			$this->class_ajax->return_html( $vote_ttl . ',' . $num_votes . ',' . $final_vote . ',new' );
			exit;
		}
		else
		{
	    	$this->ipsclass->print->redirect_screen( $this->ipsclass->lang['thanks_for_voting'], "autocom=downloads&amp;showfile={$id}" );
    	}
    }
	
    
    /*-------------------------------------------------------------------------*/
	// Display comment editing form
	/*-------------------------------------------------------------------------*/
      
    function commentEdit( $error_msg="" )
    {
    	//-----------------------------------------
    	// INIT
    	//-----------------------------------------
    	
    	$cid = intval( $this->ipsclass->input['cid'] );
    	
    	//-----------------------------------------
    	// Check CID
    	//-----------------------------------------
    	
    	if ( !$cid  )
    	{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		
		//-----------------------------------------
		// Get comment
		//-----------------------------------------
		
		$comment = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'c.comment_text, c.comment_mid as id',
												 					 'from'		=> array( 'downloads_comments' => 'c' ),
												 					 'add_join' => array( 
												 					 					array( 'select' => 'f.file_cat, f.file_id',
												 												'from' 	=> array( 'downloads_files' => 'f' ),
												 												'where' => 'f.file_id=c.comment_fid',
												 												'type' 	=> 'left',
												 											 )
												 										),
												 'where'	=> 'c.comment_id=' . $cid
										)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		else if( ! in_array( $comment['file_cat'], $this->catlib->member_access['view'] ) )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		
		$moderator = $this->funcs->checkPerms( $comment, 'modcancomments', 'idm_comment_edit' );
		
		if ( !$moderator )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		
		//-----------------------------------------
		// Get parser
		//-----------------------------------------
		
		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
		$this->parser 						= new parse_bbcode();
		$this->parser->ipsclass 			=& $this->ipsclass;
		$this->parser->allow_update_caches 	= 1;
		$this->parser->bypass_badwords 		= intval($this->ipsclass->member['g_bypass_badwords']);		
		$this->parser->parse_nl2br 			= 1;
		$this->parser->parse_smilies		= 1;
		
		//-----------------------------------------
		// Convert and return the html
		//-----------------------------------------
		
		$this->ipsclass->load_template( 'skin_post' );
		$this->ipsclass->load_language( 'lang_post' );
		
		$html     = $this->ipsclass->compiled_templates['skin_post']->inline_edit_quick_box( $this->parser->pre_edit_parse( $comment['comment_text'] ), $cid, $error_msg, 0 );
		
		if ( $this->ipsclass->vars['ipb_img_url'] )
		{
			$html = preg_replace( "#img\s+?src=([\"'])style_(images|avatars|emoticons)(.+?)[\"'](.+?)?".">#is", "img src=\\1".$this->ipsclass->vars['ipb_img_url']."style_\\2\\3\\1\\4>", $html );
		}		
		
		$this->class_ajax->return_html( $html );
    }	
	
    
    /*-------------------------------------------------------------------------*/
	// Save edited comment
	/*-------------------------------------------------------------------------*/
      
    function commentEditSave()
    {
    	//-----------------------------------------
    	// INIT
    	//-----------------------------------------
    	
    	$cid           = intval( $this->ipsclass->input['cid'] );
    	$md5_check     = $this->ipsclass->txt_md5_clean( $this->ipsclass->input['md5check'] );
    	
   		$_POST['Post'] = $this->class_ajax->convert_unicode( $_POST['Post'] );
   		$_POST['Post'] = $this->class_ajax->convert_html_entities( $_POST['Post'] );
   		
   		//-----------------------------------------
    	// Set things right
    	//-----------------------------------------
    	
    	$_POST['std_used']             = 1;
    	$this->ipsclass->input['Post'] = $this->ipsclass->parse_clean_value( $_POST['Post'] );
    	
    	//-----------------------------------------
    	// Check MD5
    	//-----------------------------------------
    	
    	if ( $md5_check != $this->ipsclass->return_md5_check() )
    	{
    		$this->class_ajax->print_nocache_headers();
			$this->class_ajax->return_string( 'error' );
		}
    	
    	//-----------------------------------------
    	// Check CID
    	//-----------------------------------------
    	
    	if ( !$cid )
    	{
    		$this->class_ajax->print_nocache_headers();
			$this->class_ajax->return_string( 'error' );
		}
		
		$comment = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'c.comment_text, c.comment_mid as id, c.use_emo, c.use_sig',
												 					 'from'		=> array( 'downloads_comments' => 'c' ),
												 					 'add_join' => array( 
												 					 					array( 'select' => 'f.file_cat, f.file_id',
												 												'from' 	=> array( 'downloads_files' => 'f' ),
												 												'where' => 'f.file_id=c.comment_fid',
												 												'type' 	=> 'left',
												 											 )
												 										),
												 'where'	=> 'c.comment_id=' . $cid
										)		);
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		else if( ! in_array( $comment['file_cat'], $this->catlib->member_access['view'] ) )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		
		$moderator = $this->funcs->checkPerms( $comment, 'modcancomments', 'idm_comment_edit' );
		
		if ( !$moderator )
		{
    		$this->class_ajax->return_string( 'error' );
    		exit();
		}
		
		//-----------------------------------------
		// Get classes
		//-----------------------------------------
		
        if( !is_object($this->parser) )
        {
			require_once( ROOT_PATH . "sources/handlers/han_editor.php" );
			$this->han 							= new han_editor();
			$this->han->ipsclass 				=& $this->ipsclass;
			$this->han->method 					= 'std';
			$this->han->init();
			
			require_once( ROOT_PATH . "sources/handlers/han_parse_bbcode.php" );
			$this->parser 						= new parse_bbcode();
			$this->parser->ipsclass 			=& $this->ipsclass;
			$this->parser->allow_update_caches 	= 1;
			$this->parser->bypass_badwords 		= intval($this->ipsclass->member['g_bypass_badwords']);		
			$this->parser->parse_html 			= 0;
			$this->parser->parse_nl2br 			= 1;
			$this->parser->parse_bbcode			= 1;
			$this->parser->parse_smilies		= $comment['use_emo'];
		}
		
		$new_comment = $this->parser->pre_db_parse( $this->han->process_raw_post( 'Post' ) );
		
		$open = $this->ipsclass->vars['idm_comment_approval'] ? 0 : 1;
		
		$this->ipsclass->DB->do_update( 'downloads_comments', array( 'comment_open' => $open, 'comment_text' => $new_comment ), 'comment_id=' . $cid );
		
		$this->funcs->rebuild_pend_comment_cnt( $comment['file_id'] );
		
		//-----------------------------------------
		// Prep for display
		//-----------------------------------------	
		
		$raw_post = $this->parser->pre_display_parse( $new_comment );
		
		foreach( $this->ipsclass->skin['_macros'] as $row )
      	{
			if ( $row['macro_value'] != "" )
			{
				$raw_post = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $raw_post );
			}
		}
		
		if ( $this->ipsclass->vars['ipb_img_url'] )
		{
			$raw_post = preg_replace( "#img\s+?src=([\"'])style_(images|avatars|emoticons)(.+?)[\"'](.+?)?".">#is", "img src=\\1".$this->ipsclass->vars['ipb_img_url']."style_\\2\\3\\1\\4>", $raw_post );
		}
		
		$this->class_ajax->print_nocache_headers();
		$this->class_ajax->return_html( $raw_post );
    }
    
    


}
?>