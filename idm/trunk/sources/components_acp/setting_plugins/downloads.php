<?php

/*
+--------------------------------------------------------------------------
|   IP.Downloads
|   =============================================
|   by Brandon Farber
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   =============================================
+---------------------------------------------------------------------------
|   > $Date: 2006-08-01 17:02:55 +0100 (Tue, 01 Aug 2006) $
|   > $Revision: 425 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Settings Plug In
|   > Module written by Brandon Farber
|   > Date started: 2 Oct 2007
|
+--------------------------------------------------------------------------
*/

/**
* Main content
*
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class setting_downloads
{
	/**
	* Global IPSCLASS
	* @var	object
	*/
	var $ipsclass;
	
	/*-------------------------------------------------------------------------*/
	// Pre-parse
	/*-------------------------------------------------------------------------*/
	
	/**
	* Allow one to modify the values before the setting is parsed
	* This function is passed an array of settings of which the index
	* of the array is the configuration ID.
	* array( index => array(
	* 						  conf_id
	* 						  conf_title
	* 						  conf_description
	* 						  conf_type
	* 						  conf_key
	* 						  conf_value
	* 						  conf_default
	* 						  conf_extra
	* 						  conf_evalphp ) );
    *
	*
	* @param	array  Settings
	* @param    array  Settings
	*
	*/
	function settings_pre_parse( $settings=array() ) 
	{
		//-----------------------------------------
		// Check 'em
		//-----------------------------------------
		
		foreach( $settings as $id => $data )
		{
			if ( $data['conf_key'] == 'idm_progress_bar' )
			{
				if( PHP_VERSION < '5.2.0' )
				{
					$settings[ $id ]['conf_description'] = str_replace( "<!--CAPABLE-->", "<b style='color:red;'>Not running PHP version 5.2.0 or greater</b>", $settings[ $id ]['conf_description'] );
				}
				else if( !function_exists( 'apc_fetch' ) AND !function_exists( 'uploadprogress_get_info' ) )
				{
					$settings[ $id ]['conf_description'] = str_replace( "<!--CAPABLE-->", "<b style='color:red;'>PECL package APC or uploadprogress required for this feature: apc_fetch and uploadprogress_get_info functions not available</b>", $settings[ $id ]['conf_description'] );
				}
				else
				{
					if( function_exists( 'apc_fetch' ) )
					{
						$check = @ini_get( 'apc.rfc1867' );
					}
					else
					{
						$check = true;
					}
					
					if( !$check )
					{
						$settings[ $id ]['conf_description'] = str_replace( "<!--CAPABLE-->", "<b style='color:orange;'>Could not determine if php.ini value for 'apc.rfc1867' is set to On</b>", $settings[ $id ]['conf_description'] );
					}
					else
					{
						$settings[ $id ]['conf_description'] = str_replace( "<!--CAPABLE-->", "<b style='color:green;'>Progress meter available!</b>", $settings[ $id ]['conf_description'] );
					}
				}
			}
		}
		
		//-----------------------------------------
		// Return
		//-----------------------------------------
	
		return $settings;
	}
	
	/*-------------------------------------------------------------------------*/
	// Post-parse
	/*-------------------------------------------------------------------------*/
	
	/**
	* Allow one to modify the values just before being saved to the DB
	* If an error occurs, please set the relevant index's '_error' flag.
	*
	*
	* For example:
	* if ( ! $true )
	* {
	*	$settings[ $conf_id ]['_error'] = 'Not true!'
	* }
	* This will then show the form again with the error in the relevant
	* setting box.
	*
	* The user entered value for the key is held in $settings[ $conf_id ]['_value']
	*
	* This function is passed an array of settings of which the index
	* of the array is the configuration ID.
	* array( index => array(
	* 						  conf_id
	* 						  conf_title
	* 						  conf_description
	* 						  conf_type
	* 						  conf_key
	* 						  conf_value
	* 						  conf_default
	* 						  conf_extra
	* 						  _error
	*						  _value ) );
    *
	*
	* @param	array  Settings
	* @param    array  Settings
	*
	*/
	function settings_post_parse( $settings=array() ) 
	{
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		return $settings;
	}
	
}


?>