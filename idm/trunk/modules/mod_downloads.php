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

class module
{
	var $ipsclass;

	function run_module()
	{
		$uri = $this->ipsclass->vars['board_url'] . '/index.php?';
		
		if( count( $_GET ) )
		{
			foreach( $_GET as $k => $v )
			{
				$uri .= $k . '=' . $v . '&';
			}

			$uri = str_replace( 'automodule', 'autocom', substr( $uri, 0, -1 ) );
			$uri = str_replace( 'act=module', '', $uri );
			$uri = str_replace( 'module', 'autocom', $uri );
		}
		
		$this->ipsclass->boink_it( $uri );
	}
}

?>