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
|   > Main Admin Module
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .03
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

define( 'DL_ADMIN_PATH'	, ROOT_PATH.'sources/components_acp/downloads/' );
define( 'DL_PATH'		, ROOT_PATH.'sources/components_public/downloads/' );
define( 'DL_VERSION'	, '1.2.1' );
define( 'DL_RVERSION'	, '12006'	);
define( 'DL_LINK'		, '' );


class ad_downloads_main {

	var $ipsclass;
	var $base_url;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';		

	function auto_run()
	{
		$this->base_url = $this->ipsclass->base_url."&".$this->ipsclass->form_code."&";

   		#$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_downloads_admin_queries.php', 'sql_downloads_admin_queries' );

		$this->ipsclass->admin->page_title = "IP.Downloads";
		$this->ipsclass->admin->page_detail = "You can configure your Download Manager from this section.";
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads', 'IP.Downloads' );


		//---------------------------------------------------------------------------
		// For security purposes we will setup a pre-approved
		// list of the &req= values that we expect to see.
		// Not in the list, you go to the home page
		// Format is 'req value' => array( 'classname/filename', 'function_name' )
		//---------------------------------------------------------------------------
		
		$valid_reqs = array (
								'idx'					=> array( 'ad_downloads_index'	, ''			),
								'settings'				=> array( ''					, ''			),
								'mime'					=> array( 'ad_downloads_mime'	, ''			),
								'customfields'			=> array( 'ad_downloads_cfields', ''			),
								'categories'			=> array( 'ad_downloads_cats'	, ''			),
								'tools'					=> array( 'ad_downloads_tools'	, ''			),
								'stats'					=> array( 'ad_downloads_stats'	, ''			),
								'groups'				=> array( 'ad_downloads_groups'	, ''			),
						 	);

		$req = isset( $valid_reqs[ $this->ipsclass->input['req'] ] ) ? strtolower($this->ipsclass->input['req']) : 'idx';

		if ( $req == 'settings' )
		{
			$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':settings' );
			
			require_once( ROOT_PATH.'sources/action_admin/settings.php' );
			$settings             			=  new ad_settings();
			$settings->ipsclass   			=& $this->ipsclass;
			$settings->get_by_key        	= 'idm';
			$settings->return_after_save 	= $this->ipsclass->form_code.'&req=settings';
			$settings->setting_view();
		}
		else
		{
			// Require and run
	        require( DL_ADMIN_PATH . $valid_reqs[ $req ][0].'.php' );
	        $page = new $valid_reqs[ $req ][0];
	        $page->ipsclass =& $this->ipsclass;
	        $page->auto_run( $this, $valid_reqs[ $req ][1] );
		}
	}
}

?>