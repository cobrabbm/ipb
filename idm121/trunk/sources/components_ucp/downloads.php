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
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

//-----------------------------------------
// Must be "components_ucp_{component name}
//-----------------------------------------

class components_ucp_downloads
{
	/*-------------------------------------------------------------------------*/
	// Build menu
	// Use: $content .= menu_bar_new_link( $url, $name ) for the links
	// Use: menu_bar_new_category( 'Blog', $content ) for the content
	/*-------------------------------------------------------------------------*/

	function ucp_build_menu()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$content = "";

		$this->ipsclass->load_template( 'skin_ucp' );
		$this->ipsclass->load_language('lang_downloads');

		//-----------------------------------------
		// Get links
		//-----------------------------------------

		$content .= $this->ipsclass->compiled_templates['skin_ucp']->menu_bar_new_link( "{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_files",
																							$this->ipsclass->lang['ucp_manage_files'] );

		$content .= $this->ipsclass->compiled_templates['skin_ucp']->menu_bar_new_link( "{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_favs",
																							$this->ipsclass->lang['ucp_manage_favs'] );

		$content .= $this->ipsclass->compiled_templates['skin_ucp']->menu_bar_new_link( "{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_subs",
																							$this->ipsclass->lang['ucp_manage_subs'] );

		if ( $content )
		{
			return $this->ipsclass->compiled_templates['skin_ucp']->menu_bar_new_category( $this->ipsclass->lang['ucp_menu_cat'], $content );
		}
		else
		{
			return '';
		}
	}
}

?>