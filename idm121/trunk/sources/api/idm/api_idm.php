<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   =============================================
+---------------------------------------------------------------------------
|   > $Date: 2005-10-10 14:08:54 +0100 (Mon, 10 Oct 2005) $
|   > $Revision: 23 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > API: IDM last 5 submissions
|   > Module written by Brandon Farber
|   > Date started: Thursday 7/27/2006
|
+--------------------------------------------------------------------------
*/

/**
* API: IDM
*
* EXAMPLE USAGE
* <code>
* To follow
* </code>
*
* @package		InvisionPowerBoard
* @subpackage	APIs
* @author		Brandon Farber
* @copyright	Invision Power Services, Inc.
* @version		2.1
*/

if ( ! defined( 'IPS_API_PATH' ) )
{
	/**
	* Define classes path
	*/
	define( 'IPS_API_PATH', dirname(__FILE__) ? dirname(__FILE__) : '.' );
}

if ( ! class_exists( 'api_core' ) )
{
	require_once( IPS_API_PATH.'/api_core.php' );
}

/**
* API: IDM
*
* This class will pull the last 5 IDM submissions a user has submitted
*
* @package		InvisionPowerBoard
* @subpackage	APIs
* @author  	 	Brandon Farber
* @version		2.1
* @since		2.2.0
*/
class api_idm extends api_core
{
	/**
	* IPS Class Object
	*
	* @var object
	*/
	//var $ipsclass;
	
									
	/*-------------------------------------------------------------------------*/
	// Returns an array of download data
	/*-------------------------------------------------------------------------*/
	/**
	* Returns an array of download data
	*
	* @return   array	Array of download data
	*/
	function return_idm_data( $member_id = 0, $limit = 5, $nomember = 0, $order='' )
	{
		if( !$member_id AND !$nomember )
		{
			return array();
		}
		
		//-----------------------------------------
		// Permissions?
		//-----------------------------------------
		
		if( !isset($this->ipsclass->vars['idm_online']) )
		{
			// IDM is not installed since the key doesn't exist
			
			return array();
		}
		
		if( $this->ipsclass->vars['idm_online'] == 0 )
		{
			$offline_access = explode( ",", $this->ipsclass->vars['idm_offline_groups'] );
			
			$my_groups = array( $this->ipsclass->member['mgroup'] );
			
			if( $this->ipsclass->member['mgroups_other'] )
			{
				$my_groups = array_merge( $my_groups, explode( ",", $this->ipsclass->clean_perm_string( $this->ipsclass->member['mgroups_other'] ) ) );
			}
			
			$continue = 0;
			
			foreach( $my_groups as $group_id )
			{
				if( in_array( $group_id, $offline_access ) )
				{
					$continue = 1;
					break;
				}
			}
			
			if( $continue == 0 )
			{
				// Offline, and we don't have access
				
				return array();
			}
		}
				
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$files = array();
		
		$member_id = intval($member_id);
		
		if( $member_id )
		{
			$submitter = 'f.file_submitter='.$member_id.' AND ';
		}
		else if( $nomember )
		{
			$submitter = '';
		}
		
		//-----------------------------------------
		// Grab allowed categories
		//-----------------------------------------	
		
		if( !file_exists( ROOT_PATH.'sources/components_public/downloads/lib/lib_cats.php' ) )
		{
			return array();
		}
		
		//-----------------------------------------
		// Load caches - uses external lib if avail
		//-----------------------------------------	
		
		$this->ipsclass->init_load_cache( array( 'idm_cats', 'idm_mods' ) );
		
		require_once ( ROOT_PATH.'sources/components_public/downloads/lib/lib_cats.php' );
		$this->catlib 			= new lib_cats();
		$this->catlib->ipsclass = & $this->ipsclass;
		$this->catlib->normal_init();
		$this->catlib->get_member_cat_perms( );
		
		$categories = $this->catlib->member_access['view'];

		if( !is_array($categories) OR !count($categories) )
		{
			//No category permissions
			
			return array();
		}
		
		$order = $order ? $order : 'f.file_submitted DESC';
		
		$this->ipsclass->DB->build_query( array( 'select'	=> 'f.*',
												 'from'		=> array( 'downloads_files' => 'f' ),
												 'where'	=> $submitter . 'f.file_open=1 AND f.file_cat IN ('.implode( ',', $categories ).')',
												 'add_join'	=> array( 
												 				1 => array( 'type'		=> 'left',
												 							'select'	=> 'c.cname as category_name',
												 							'from'		=> array( 'downloads_categories' => 'c' ),
												 							'where'		=> "c.cid=f.file_cat",
												 						  ),
												 				2 => array( 'type'		=> 'left',
												 							'select'	=> 'm.members_display_name',
												 							'from'		=> array( 'members' => 'm' ),
												 							'where'		=> "m.id=f.file_submitter",
												 						  )
												 					),												 					
												 'order'	=> $order,
												 'limit'	=> array( 0, $limit )
										)		);
										
		$res = $this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$files[] = $r;
		}
		
		
		return $files;
	}
	
}



?>