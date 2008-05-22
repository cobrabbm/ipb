<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   =============================================
+---------------------------------------------------------------------------
|   > $Date: 2006-08-01 17:02:55 +0100 (Tue, 01 Aug 2006) $
|   > $Revision: 425 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Personal Profile Portal Class: IDM
|   > Module written by Brandon Farber
|   > Date started: 2nd August 2006
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

class profile_idm
{
	/**
	* Global IPSCLASS
	* @var	object
	*/
	var $ipsclass;
	
	/*-------------------------------------------------------------------------*/
	// Return data
	/*-------------------------------------------------------------------------*/
	
	/**
	* Returns a block of HTML back to the ajax handler
	* which then replaces the inline content with the HTML
	* returned.
	*
	*/
	function return_html_block( $member=array() ) 
	{
		if( !$this->ipsclass->DB->field_exists( "file_id", "downloads_files" ) )
		{
			return $this->ipsclass->lang['err_no_posts_to_show'];
		}
		
		define( 'IDM_PATH', ROOT_PATH.'sources/components_public/downloads/' );
		
		$this->ipsclass->load_language( 'lang_downloads' );
		$this->ipsclass->load_template( 'skin_downloads_external' );

		//-----------------------------------------
		// Get gallery library and API
		//-----------------------------------------
		
		require_once( ROOT_PATH.'sources/api/api_core.php' );
		require_once( ROOT_PATH . 'sources/api/idm/api_idm.php' );
		
		//-----------------------------------------
		// Create API Object
		//-----------------------------------------
		
		$idm_api = new api_idm;
		$idm_api->ipsclass = $this->ipsclass;

		//-----------------------------------------
		// Get images
		//-----------------------------------------
		
		$files = array();
		$files = $idm_api->return_idm_data( $member['id'], 10 );
		
		//-----------------------------------------
		// Ready to pull formatted stuff?
		//-----------------------------------------
		
		if( count($files) )
		{
			$data = array();

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
			
			$output = $this->ipsclass->compiled_templates['skin_downloads_external']->profile_display( $data );
		}
		else
		{
			if ( ! is_object( $this->ipsclass->compiled_templates['skin_profile'] ) )
			{
				$this->ipsclass->load_template( 'skin_profile' );
			}
		
			$output .= $this->ipsclass->compiled_templates['skin_profile']->personal_portal_no_content( 'no_files_in_category' );
		}
		      
		//-----------------------------------------
		// Macros...
		//-----------------------------------------
		
		if ( ! is_array( $this->ipsclass->skin['_macros'] ) OR ! count( $this->ipsclass->skin['_macros'] ) )
    	{
    		$this->ipsclass->skin['_macros'] = unserialize( stripslashes($this->ipsclass->skin['_macro']) );
    	}
		
		if ( is_array( $this->ipsclass->skin['_macros'] ) )
      	{
			foreach( $this->ipsclass->skin['_macros'] as $row )
			{
				if ( $row['macro_value'] != "" )
				{
					$output = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $output );
				}
			}
		}
		
		$output = str_replace( "<#IMG_DIR#>", $this->ipsclass->skin['_imagedir'], $output );
		$output = str_replace( "<#EMO_DIR#>", $this->ipsclass->skin['_emodir']  , $output );

		return $output;
	}
	
}


?>