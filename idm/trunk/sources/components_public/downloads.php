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
|   > Main Component Wrapper
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .02
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

define( 'DL_PATH'	, ROOT_PATH.'sources/components_public/downloads/' );
define( 'DL_VERSION', '1.2.1' );
define( 'DL_LINK'	, '' );

if( @ini_get('zlib.output_compression') )
{
	@ini_set( 'zlib.output_compression', 'Off' );
}

/*-------------------------------------------------------------------------*/
// Main module class
/*-------------------------------------------------------------------------*/
class component_public
{
	var $ipsclass;
	var $parser;
	var $catlib;
	var $funcs;
	var $html   = "";
	var $result = "";

    /*-------------------------------------------------------------------------*/
	// Constructer
    /*-------------------------------------------------------------------------*/
	function run_component()
	{
        //-----------------------------------------
		// Load our primary category library
        //-----------------------------------------

		require ( DL_PATH.'lib/lib_cats.php' );
		$this->catlib 			= new lib_cats();
		$this->catlib->ipsclass = & $this->ipsclass;
		$this->catlib->normal_init();
		
        //-----------------------------------------
		// Load our functions library
        //-----------------------------------------

		require ( DL_PATH.'lib/lib_funcs.php' );
		$this->funcs 			= new lib_funcs();
		$this->funcs->ipsclass 	= & $this->ipsclass;
		$this->funcs->catlib	= $this->catlib;

        //-----------------------------------------
		// Build the permissions
		// Stores to this->catlib->member_access[]
        //-----------------------------------------

		$this->catlib->get_member_cat_perms();

        //-----------------------------------------
		// Load DB query file
        //-----------------------------------------

		$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_idm_queries.php', 'sql_idm_queries' );

        //-----------------------------------------
		// Load language and template files
        //-----------------------------------------

		$this->ipsclass->load_language('lang_downloads');
		$this->ipsclass->load_template('skin_downloads');
		
		//-----------------------------------------
		// Global Online Check
        //-----------------------------------------

        if( $this->ipsclass->vars['idm_online'] == 0 )
        {
	        $can_access = 0;
	        
	        // Get the groups who have access
	        $groups = array();
	        $groups = explode( ",", $this->ipsclass->vars['idm_offline_groups'] );
	        
	        $my_groups = array( $this->ipsclass->member['mgroup'] );
	        
	        if( $this->ipsclass->member['mgroup_others'] )
	        {
		        $my_groups = array_merge( $my_groups, explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroup_others'] ) ) );
	        }
	        
	        if( count($groups) )
	        {
		        foreach( $groups as $group )
		        {
			        if( in_array( $group, $my_groups ) )
			        {
				        $can_access = 1;
			        }
		        }
	        }
	        
	        if( $can_access == 0 )
	        {
		        $this->ipsclass->vars['idm_offline_msg'] = $this->ipsclass->my_nl2br($this->ipsclass->vars['idm_offline_msg']);
		        
		        $this->output = $this->ipsclass->compiled_templates['skin_downloads']->offline_box();
		        $this->ipsclass->print->add_output("$this->output");
		        $this->ipsclass->print->do_output( array( 'TITLE' => $this->ipsclass->lang['idm_offline_msg'], 'JS' => 1, NAV => $this->ipsclass->lang['idm_offline_msg'] ) );
	        }
        }
        
        if( $this->ipsclass->input['rss'] == 1 )
        {
	        $this->_print_rss();
	        exit;
        }

        //-----------------------------------------
		// Initialize cache and to-do list
        //-----------------------------------------

		//					req=$$$$						file=$$$$	class=$$$$
		$valid_reqs = array (
							'idx'				=>	array( 'display', 	'idm_display' ),		// Index -> view root cats
							'display'			=>	array( 'display', 	'idm_display' ),		// View specific cat or file
							'download'			=>  array( 'download',	'idm_download' ),		// Do the download
							'ucp_files'			=>	array( 'ucp', 		'idm_ucp' ),			// User cp file management
							'ucp_favs'			=>	array( 'ucp', 		'idm_ucp' ),			// User cp favorite management
							'ucp_subs'			=>	array( 'ucp', 		'idm_ucp' ),			// User cp subscription management
							'submit'			=>  array( 'submit', 	'idm_submit' ),			// Well, um, submit of course
							'mod'				=>  array( 'moderate', 	'idm_moderate' ),		// Moderation libraries
							'search'			=>  array( 'search', 	'idm_search' ),			// Search functions
							'comments'			=>  array( 'comments', 	'idm_comments' ),		// Commenting handler
							'ajax'				=>  array( 'ajax', 		'idm_ajax' ),			// AJAX Request handler
						 );

		if ( $this->ipsclass->input['showfile'] )
		{
			$this->ipsclass->input['req'] 	= 'display';
			$this->ipsclass->input['code'] 	= 'file';
			$this->ipsclass->input['id'] 	= intval( $this->ipsclass->input['showfile'] );
		}
		
		if ( $this->ipsclass->input['showcat'] )
		{
			$this->ipsclass->input['req'] 	= 'display';
			$this->ipsclass->input['code'] 	= 'cat';
			$this->ipsclass->input['id'] 	= intval( $this->ipsclass->input['showcat'] );
			$this->ipsclass->input['catid'] =& $this->ipsclass->input['id'];
		}

		$req = ( isset( $valid_reqs[ $this->ipsclass->input['req'] ] ) ? strtolower($this->ipsclass->input['req']) : 'idx' );

		if ( $valid_reqs[$req][0] != 'ucp' )
		{
	        $this->ipsclass->skin['_wrapper'] = str_replace( "<% COPYRIGHT %>" , $this->funcs->return_copyright() , $this->ipsclass->skin['_wrapper'] );
        }
        
        if( $this->ipsclass->vars['idm_rss'] )
        {
	        $this->ipsclass->skin['_wrapper'] = str_replace( "<% GENERATOR %>"      , $this->ipsclass->compiled_templates['skin_global']->global_rss_link( array( 'title' => $this->ipsclass->lang['idm_rss_title'],
	        																																					  'url'   => $this->ipsclass->vars['board_url']."/index.php?autocom=downloads&amp;rss=1" ) ), $this->ipsclass->skin['_wrapper']);
	        $this_footer  = $this->ipsclass->compiled_templates['skin_global']->global_board_footer( $this->ipsclass->get_date( time(), 'SHORT', 1 ) );
	        $this->ipsclass->skin['_wrapper'] = str_replace( "<% BOARD FOOTER %>"   , $this_footer                        , $this->ipsclass->skin['_wrapper']);
	        $this->ipsclass->skin['_wrapper'] = str_replace( "<% SYNDICATION %>"    , $this->funcs->_get_synd_link()             , $this->ipsclass->skin['_wrapper']);
        }

        //-----------------------------------------
		// Require and run me already
        //-----------------------------------------
        require( DL_PATH . $valid_reqs[$req][0] . "." . $this->ipsclass->vars['php_ext'] );
        $obj 			= new $valid_reqs[$req][1];
        $obj->ipsclass 	=& $this->ipsclass;
        $obj->catlib 	=& $this->catlib;
        $obj->funcs		=& $this->funcs;
        $obj->run_me();

	}
	

	

	
	
	function _print_rss()
	{
		//--------------------------------------------
		// Require classes
		//--------------------------------------------
		
		require_once( KERNEL_PATH . 'class_rss.php' );
		$class_rss              =  new class_rss();
		$class_rss->ipsclass    =& $this->ipsclass;
		$class_rss->use_sockets =  1;
		$class_rss->doc_type    =  $this->ipsclass->vars['gb_char_set'];
		
		//-----------------------------------------
        // Load and config the post parser
        //-----------------------------------------
        
        require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
        $this->parser                      = new parse_bbcode();
        $this->parser->ipsclass            = $this->ipsclass;
        $this->parser->allow_update_caches = 1;
        $this->parser->bypass_badwords     = 0;
        
		$channel_id = $class_rss->create_add_channel( array( 'title'       => $this->ipsclass->lang['idm_rss_title'],
															 'link'        => $this->ipsclass->vars['board_url'].'/index.php?autocom=downloads&amp;req=search&amp;code=last_ten',
															 'pubDate'     => $class_rss->rss_unix_to_rfc( time() ),
															 'ttl'         => 30 * 60,
															 'description' => $this->ipsclass->lang['idm_rss_desc']
													)      );
													
		$this->ipsclass->DB->simple_construct( array( 'select' 	=> 'f.*',
												 'from'   	=> array('downloads_files' => 'f'),
									             'add_join'	=> array( 0 => array(
									             								  'select' => 'm.members_display_name',
																                  'from'   => array( 'members' => 'm' ),
																                  'where'  => "f.file_submitter=m.id",
																                  'type'   => 'left'
																	)			),
												 'where'	=> 'f.file_open=1',
												 'order'	=> 'f.file_submitted DESC',
												 'limit'	=> array(0,10)	)	);
		$outer = $this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row($outer) )
		{
			$r['file_desc'] = $this->parser->pre_display_parse( $r['file_desc'] );
			
			$class_rss->create_add_item( $channel_id, array( 'title'           => $r['file_name'],
															 'link'            => $this->ipsclass->vars['board_url'].'/index.php?autocom=downloads&amp;showfile='.$r['file_id'],
															 'description'     => $r['file_desc'],
															 'pubDate'	       => $class_rss->rss_unix_to_rfc( $r['file_submitted'] ),
															 'guid'            => $r['file_id']
									  )                    );
		}
		
		$class_rss->rss_create_document();
		
    	@header('Content-Type: text/xml');
		@header('Expires: ' . gmdate('D, d M Y H:i:s', time()+60*30) . ' GMT');
		@header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		@header('Pragma: public');
		print $class_rss->rss_document;
		exit();
	}
}

?>