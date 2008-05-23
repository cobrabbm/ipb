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
|   > Component Initialization
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
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

//-----------------------------------------
// This must always be 'component_init'
//-----------------------------------------

class component_init
{
	var $ipsclass;

	/*-------------------------------------------------------------------------*/
	// run_init
	// Get caches we need for IDM
	/*-------------------------------------------------------------------------*/

	function run_init()
	{
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
		}
		
		//					req=$$$$						file=$$$$	cache=$$$$
		$valid_reqs = array (
							'idx'				=>	array( 'display', 	array('idm_stats','idm_cats','idm_cfields','idm_mimetypes','idm_mods','profilefields','ranks','bbcode') ),
							'display'			=>	array( 'display', 	array('idm_stats','idm_cats','idm_cfields','idm_mimetypes','idm_mods','profilefields','ranks','bbcode','emoticons','badwords') ),
							'ucp_favs'			=>	array( 'ucp', 		array('bbcode','idm_cats','idm_mods') ),
							'ucp_subs'			=>	array( 'ucp', 		array('bbcode','idm_cats','idm_mods') ),
							'submit'			=>  array( 'submit', 	array('bbcode','idm_cfields','idm_cats','idm_mods','idm_mimetypes','emoticons','badwords') ),
							'mod'				=>  array( 'moderate', 	array('bbcode','idm_cats','idm_mods','idm_mimetypes','emoticons','badwords') ),
							'search'			=>  array( 'search', 	array('bbcode','idm_cats','idm_mods','emoticons','idm_stats','idm_cfields') ),
							'download'			=>  array( 'display', 	array('idm_stats','idm_cats','idm_mods','idm_mimetypes') ),
							'comments'			=>	array( 'comments', 	array('idm_cats','idm_mods','profilefields','ranks') ),
						 );

		$req = ( isset( $valid_reqs[ $this->ipsclass->input['req'] ] ) ? strtolower($this->ipsclass->input['req']) : 'idx' );

		if ( isset( $valid_reqs[$req] ) )
		{
			$this->ipsclass->cache_array = array_merge( $this->ipsclass->cache_array, $valid_reqs[$req][1]);
		}
	}
}

?>