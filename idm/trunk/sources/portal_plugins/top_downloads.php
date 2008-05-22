<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   =============================================
+---------------------------------------------------------------------------
|   > $Date: 2006-09-22 05:28:54 -0500 (Fri, 22 Sep 2006) $
|   > $Revision: 567 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > PORTAL PLUG IN MODULE: Recent Downloads
|   > Module written by Jason Lombardozzi
|   > Date started: Wednesday 4th October 2006 (13:37)
+--------------------------------------------------------------------------
*/

/**
* Portal Plug In Module
*
* This module displays the recently posted download title,
* who submitted by and when.
*
* Each class name MUST be in the format of:
* ppi_{file_name_minus_dot_php}
*
* @package		InvisionPowerBoard
* @subpackage	PortalPlugIn
* @author		Jason Lombardozzi
* @copyright	Invision Power Services, Inc.
* @version		2.2
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_top_downloads
{
	/**
	* IPS Global object
	*
	* @var string
	*/
	var $ipsclass;

	/**
	* Array of portal objects including:
	* good_forum, bad_forum
	*
	* @var array
	*/
	var $portal_object = array();
	
	/*-------------------------------------------------------------------------*/
 	// INIT
	/*-------------------------------------------------------------------------*/
 	/**
	* This function must be available always
	* Add any set up here, such as loading language and skins, etc
	*
	*/
 	function init()
 	{
 	}
 	
	/*-------------------------------------------------------------------------*/
 	// SHOW RECENT DOWNLOADS X
	/*-------------------------------------------------------------------------*/
	/**
	* Show Top Downloads x
	*
	* @return VOID
	*/
	function top_downloads()
	{
		
		define( 'IDM_PATH', ROOT_PATH.'modules/downloads/' );
		
		$this->ipsclass->load_language( 'lang_downloads' );
		$this->ipsclass->load_template( 'skin_downloads_external' );

		//-----------------------------------------
		// Get gallery library and API
		//-----------------------------------------
		
		if( !class_exists( 'api_idm' ) )
		{
			require_once( ROOT_PATH.'sources/api/api_core.php' );
			require_once( ROOT_PATH . 'sources/api/idm/api_idm.php' );
		}
		
		//-----------------------------------------
		// Create API Object
		//-----------------------------------------
		
		$idm_api = new api_idm;
		$idm_api->ipsclass = $this->ipsclass;

		//-----------------------------------------
		// Get images
		//-----------------------------------------
		
		$files = array();
		$files = $idm_api->return_idm_data( $member['id'], 10, 1, 'f.file_downloads DESC' );
		
		//-----------------------------------------
		// Ready to pull formatted stuff?
		//-----------------------------------------
		
		$data = array();
		
		if( count($files) )
		{
			foreach( $files as $row )
			{
				$row['css'] = $row['file_open'] == 1 ? 'row2' : 'row2shaded';
				
				$row['file_submitteddis'] = $this->ipsclass->get_date( $row['file_submitted'], 'LONG' );
				
				if( $row['file_updated'] > $row['file_submitted'] )
				{
					$row['file_updateddis'] = "<strong>".$this->ipsclass->lang['catdis_updated']."</strong>".$this->ipsclass->get_date( $row['file_updated'], 'LONG' );
				}
				else
				{
					$row['file_updateddis'] = "&nbsp;";
				}
				
				if( $row['file_submitter'] == 0 OR !$row['members_display_name'] )
				{
					$row['members_display_name'] = $this->ipsclass->lang['idm_guestlang'];
				}
				
				$row['submitter'] = $this->ipsclass->make_profile_link( $row['members_display_name'], $row['file_submitter'] );
				$row['filename']  = $this->ipsclass->compiled_templates['skin_downloads_external']->file_link( $row['file_name'], $row['file_id'] );
							
				$data[] = $row;
			}
		}
		      
		$output = $this->ipsclass->compiled_templates['skin_downloads_external']->portal_block( $this->ipsclass->lang['top_downloads'], $data, 1 );

		return $output;
	}
}