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
|   > Component Location Notification
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

/*
+--------------------------------------------------------------------------
|   This module has two functions:
|   get_session_variables: Return the session variables for the class_session functions
|   parse_online_entries: Parses an array from online.php
|   See each function for more information
+--------------------------------------------------------------------------
*/

//-----------------------------------------
// This must always be 'components_location'
//-----------------------------------------

class components_location_downloads
{
	var $ipsclass;
	var $catlib;

	//						req=$$$$				loc=$$$$
	var	$req_map = array (
							'idx'				=>	'idx',
							'display'			=>	'idx',
							'download'			=>  'download',
							'ucp_favs'			=>	'ucp',
							'ucp_subs'			=>	'ucp',
							'moderate'			=>	'mod',
							'submit'			=>	'post',
							'search'			=>	'search',
							'comments'			=>  'comments',
						 );

	/*-------------------------------------------------------------------------*/
	// get_session_variables
	// Returns:
	// array( '1_type' => {location type #1} [ char(10) ]
	//        '1_id'   => {location ID #1}   [ int(10)  ]
	//        '2_type' => {location type #2} [ char(10) ]
	//        '2_id'   => {location ID #2}   [ int(10)  ]
	//		  '3_type' => {location type #3} [ char(10) ]
	//        '3_id'   => {location ID #3}   [ int(10)  ]
	//      );
	// All are optional.
	// Use this to populate the 'module_id_*' fields in the session table
	// so you can check in your own scripts it the member is active in your module
	// {variable} can be 30 chrs long and alpha numerical
	// "location" in the sessions table will be the name of the module called
	/*-------------------------------------------------------------------------*/

	function get_session_variables()
	{
		$return_array = array();


		$req = ( isset( $this->req_map[ $this->ipsclass->input['req'] ] ) ? strtolower($this->ipsclass->input['req']) : 'idx' );

		$return_array['1_type'] = $this->req_map[$req];

		if ( intval($this->ipsclass->input['id']) )
		{
			$return_array['1_id'] = intval( $this->ipsclass->input['id'] );
		}
		
		if( $this->req_map[ $this->ipsclass->input['req'] ] == 'idx'
			OR $this->req_map[ $this->ipsclass->input['req'] ] == 'download' )
		{
			$return_array['2_type'] = substr( $this->ipsclass->input['code'], 0, 10 );
			$return_array['2_id']	= intval( $this->ipsclass->input['id'] );
		}

		return $return_array;
	}

	/*-------------------------------------------------------------------------*/
	// parse_online_entries
	// INPUT: $array IS:
	// $array[ $session_id ] = $session_array;
	// Session array is DB row from ibf_sessions
	// EXPECTED RETURN ------------------------------------
	// $array[ $session_id ]['_parsed'] = 1;
	// $array[ $session_id ]['_url']    = {Location url}
	// $array[ $session_id ]['_text']   = {Location text}
	// $array[ $session_id ] = $session_array...
	//
	// YOU ARE RESPONSIBLE FOR PERMISSION CHECKS. IF THE MEMBER DOESN'T
	// HAVE PERMISSION RETURN '_url'    => $this->ipsclass->base_url,
	// 						  '_text'   => $this->ipsclass->lang['board_index'],
	//						  '_parsed' => 1 { as well as the rest of $session_array }
	/*-------------------------------------------------------------------------*/

	function parse_online_entries( $sessions=array() )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$return 		= array();
		$files			= array();
		$cats			= array();

		//-----------------------------------------
		// Load language file
		//-----------------------------------------
		if ( ! isset( $this->ipsclass->lang['idm_loc_idx'] ) )
		{
			$this->ipsclass->load_language( 'lang_downloads' );
		}
		
		require_once( ROOT_PATH.'sources/components_public/downloads/lib/lib_cats.php' );
		$this->catlib = new lib_cats();
		$this->catlib->ipsclass =& $this->ipsclass;
		$this->catlib->normal_init();
		$this->catlib->get_member_cat_perms();
		
		$open_cats = $this->catlib->get_open_cats();

		//-----------------------------------------
		// LOOP
		//-----------------------------------------

		if ( is_array( $sessions ) and count( $sessions ) )
		{
			foreach( $sessions as $session_id => $session_array )
			{
				if ( $session_array['location'] == 'mod:downloads' && $session_array['location_1_type'] == 'idx' && intval($session_array['location_1_id']) )
				{
					if( isset( $session_array['location_2_type'] ) )
					{
						if( $session_array['location_2_type'] == 'cat' )
						{
							$cat_ids[] = intval($session_array['location_1_id']);
						}
						else if ( $session_array['location_2_type'] == 'file' )
						{
							$file_ids[] = intval($session_array['location_1_id']);
						}
						else if ( $session_array['location_2_type'] == 'ss' )
						{
							$file_ids[] = intval($session_array['location_1_id']);
						}						
					}
				}
				else if ( $session_array['location'] == 'mod:downloads' && $session_array['location_1_type'] == 'download' && intval($session_array['location_1_id']) )
				{
					if ( $session_array['location_2_type'] == 'getfile' )
					{
						$file_ids[] = intval($session_array['location_1_id']);
					}
				}
			}
			
			if( count( $cat_ids ) > 0 )
			{
				foreach( $cat_ids as $key => $value )
				{
					if( in_array( $value, $open_cats ) )
					{
						if( in_array( $value, $this->catlib->member_access['show'] ) )
						{
							$cats[$value] = $this->catlib->cat_lookup[$value];
						}
					}
				}
			}

			if ( count( $file_ids ) > 0 )
			{
				$query_string = "";
				
				$query[] = "file_id IN(".implode( ",", $file_ids ).")";
				
				if ( !$this->ipsclass->member['g_is_supmod'] )
				{
					$query[] = "file_open = 1";
				}
				
				if( is_array( $this->catlib->member_access['view'] ) && count( $this->catlib->member_access['view'] ) )
				{
					$query[] = "file_cat IN(".implode( ",", $this->catlib->member_access['view']).")";
				}

		    	if ( count($query) )
		    	{
		    		$query_string = "AND ".implode( " AND ", $query );
		    	}

				$this->ipsclass->DB->simple_construct( array( 'select'	=> 'file_id, file_name',
															  'from'	=> 'downloads_files',
															  'where'	=> "file_name IS NOT NULL ".$query_string
													 )		);
				$this->ipsclass->DB->simple_exec();
				while ( $row = $this->ipsclass->DB->fetch_row() )
				{
					$files[$row['file_id']] = $row;
				}
			}

			foreach( $sessions as $session_id => $session_array )
			{
				if ( $session_array['location'] == 'mod:downloads' )
				{
					if ( $session_array['location_1_type'] == 'idx' )
					{
						if ( $session_array['location_2_type'] == 'cat' && isset($cats[$session_array['location_2_id']]) )
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;showcat='.$session_array['location_2_id'];
							$text = $this->ipsclass->lang['idm_loc_cat']." '".$cats[ $session_array['location_2_id'] ]['cname']."'";
						}
						else if( $session_array['location_2_type'] == 'file' && isset($files[$session_array['location_2_id']]) )
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;showfile='.$session_array['location_2_id'];
							$text = $this->ipsclass->lang['idm_loc_file']." '".$files[ $session_array['location_2_id'] ]['file_name']."'";
						}
						else if( $session_array['location_2_type'] == 'ss' && isset($files[$session_array['location_2_id']]) )
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;showfile='.$session_array['location_2_id'];
							$text = $this->ipsclass->lang['idm_loc_file']." '".$files[ $session_array['location_2_id'] ]['file_name']."'";
						}
						else
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;req=idx';
							$text = $this->ipsclass->lang['idm_loc_idx'];
						}
					}
					else if( $session_array['location_1_type'] == 'download' )
					{
						if( $session_array['location_2_type'] == 'getfile' && isset($files[$session_array['location_2_id']]) )
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;showfile='.$session_array['location_2_id'];
							$text = $this->ipsclass->lang['idm_loc_file']." '".$files[ $session_array['location_2_id'] ]['file_name']."'";
						}
						else
						{
							$location = $this->ipsclass->base_url.'autocom=downloads&amp;req=idx';
							$text = $this->ipsclass->lang['idm_loc_idx'];
						}
					}
					else
					{
						$location = $this->ipsclass->base_url."autocom=downloads";
						$text = $this->ipsclass->lang['idm_loc_'.$session_array['location_1_type']];
					}
				}
				else
				{
					$location = $this->ipsclass->base_url;
					$text     = $this->ipsclass->lang['board_index'];
				}

				$return[ $session_id ] = array_merge( $session_array, array( '_url' => $location, '_text' => $text, '_parsed' => 1 ) );
			}
		}

		return $return;
	}

}

?>