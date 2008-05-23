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
|   > Admin Mime-Type Control
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .05
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_mime {

	var $lib;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';	

	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=mime', 'IP.Downloads Mime Types' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=mime";


		switch( $this->ipsclass->input['code'] )
		{
			// Mime-Type Stuff
			case 'types':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_start();
				break;
			case 'mime_add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_form('add');
				break;
			case 'mime_doadd':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_save('add');
				break;
			case 'mime_edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_form('edit');
				break;
			case 'mime_delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_delete();
				break;
			case 'mime_doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_save('edit');
				break;
			case 'mime_export':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_export();
				break;
			case 'mime_import':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mime_type_import();
				break;

			// Masks
			case 'mask_delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->delete_mask();
				break;
			case 'mask_splash':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mask_splash();
				break;
			case 'mask_add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mask_add();
				break;
			case 'do_mask_edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mask_edit_save();
				break;
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':mime' );
				$this->mask_splash();
				break;
		}
	}
	
	
	function mask_add()
	{
		$this->ipsclass->input['new_mask_name'] = trim($this->ipsclass->input['new_mask_name']);
		
		if ($this->ipsclass->input['new_mask_name'] == "")
		{
			$this->ipsclass->admin->error("You must enter a name for this Mime-Type mask");
		}
		
		$copy_id = $this->ipsclass->input['new_mask_copy'];
		
		//-----------------------------------------
		// UPDATE DB
		//-----------------------------------------
		
		$this->ipsclass->DB->do_insert( 'downloads_mimemask', array( 'mime_masktitle' => $this->ipsclass->input['new_mask_name'] ) );
		
		$new_id = $this->ipsclass->DB->get_insert_id();
		
		if ( $copy_id != '0' )
		{
			$old_id = intval($copy_id);
			
			if ( ($new_id > 0) and ($old_id > 0) )
			{
				$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mime' ) );
				$get = $this->ipsclass->DB->simple_exec();
				
				while( $r = $this->ipsclass->DB->fetch_row($get) )
				{
					$files = explode( ",", $r['mime_file'] );
					
					if( is_array( $files ) )
					{
						if( in_array( $old_id, $files ) )
						{
							$files[] = $new_id;
						}
					}
					
					$screenshots = explode( ",", $r['mime_screenshot'] );
					
					if( is_array( $screenshots ) )
					{
						if( in_array( $old_id, $screenshots ) )
						{
							$screenshots[] = $new_id;
						}
					}
					
					$inline = explode( ",", $r['mime_inline'] );
					
					if( is_array( $inline ) )
					{
						if( in_array( $old_id, $inline ) )
						{
							$inline[] = $new_id;
						}
					}
					
					$this->ipsclass->DB->do_update( "downloads_mime", array( 'mime_file' => implode( ",", array_unique($files) ),
																				'mime_screenshot' => implode( ",", array_unique($screenshots) ),
																				'mime_inline' => implode( ",", array_unique($inline) ) ), "mime_id='{$r['mime_id']}'" );
				}
			}
		}
		
		//-----------------------------------------
		// Recache mime-types
		//-----------------------------------------
		
		$this->mime_type_rebuildcache();
				
		$this->mask_splash();
	}
	
	
	function mask_edit_save()
	{
		$this->ipsclass->input['new_mask_name'] = trim($this->ipsclass->input['new_mask_name']);
		
		if( $this->ipsclass->input['new_mask_name'] == "" )
		{
			$this->ipsclass->admin->error("You must enter a name for this Mime-Type mask");
		}
		
		if( !$this->ipsclass->input['mask_id'] )
		{
			$this->ipsclass->admin->error("No ID passed to update!");
		}
		
		$id = intval($this->ipsclass->input['mask_id']);
		
		//-----------------------------------------
		// UPDATE DB
		//-----------------------------------------
		
		$this->ipsclass->DB->do_update( 'downloads_mimemask', array( 'mime_masktitle' => $this->ipsclass->input['new_mask_name'] ), "mime_maskid=".$id );
		
		$this->mime_type_rebuildcache();
		
		$this->ipsclass->input['id'] = $id;
				
		$this->mime_type_start();
	}	
	

	function delete_mask()
	{
		//-----------------------------------------
		// Check for a valid ID
		//-----------------------------------------
		
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("Could not resolve the Mime-Type mask ID, please try again");
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_mimemask', 'where' => "mime_maskid=".intval($this->ipsclass->input['id']) ) );
		$old_id = intval($this->ipsclass->input['id']);
		
		//-----------------------------------------
		// Remove from mime-types...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mime' ) );
		$get = $this->ipsclass->DB->simple_exec();
		
		while( $r = $this->ipsclass->DB->fetch_row($get) )
		{
			$new_files = array();
			$files = explode( ",", $r['mime_file'] );
					
			if( is_array( $files ) )
			{
				foreach( $files as $k => $v )
				{
					if( $v != $old_id )
					{
						$new_files[] = $v;
					}
				}
			}
			
			$new_screenshots = array();	
			$screenshots = explode( ",", $r['mime_screenshot'] );
					
			if( is_array( $screenshots ) )
			{
				foreach( $screenshots as $k => $v )
				{
					if( $v != $old_id )
					{
						$new_screenshots[] = $v;
					}
				}
			}
			
			$new_inline = array();
			$inline = explode( ",", $r['mime_inline'] );
					
			if( is_array( $inline ) )
			{
				foreach( $inline as $k => $v )
				{
					if( $v != $old_id )
					{
						$new_inline[] = $v;
					}
				}
			}			
					
			$this->ipsclass->DB->do_update( "downloads_mime", array( 'mime_file' => implode( ",", array_unique($new_files) ),
																		'mime_screenshot' => implode( ",", array_unique($new_screenshots) ),
																		'mime_inline' => implode( ",", array_unique($new_inline) ) ), "mime_id='{$r['mime_id']}'" );

		}
		
		//-----------------------------------------
		// Recache mime-types
		//-----------------------------------------
				
		$this->mime_type_rebuildcache();
				
		$this->mask_splash();
	}	
	
	function mask_splash()
	{
		$this->ipsclass->admin->page_title  = "Mime-Type Mask Configuration";
		$this->ipsclass->admin->page_detail = "You can setup Mime-Type Masks, and configure different permissions for each mask.  Then you can select the mask you wish to use on a per-category basis to limit the file types allowed to each category.";
		
		$this->ipsclass->adskin->td_header[] = array( "Mask Name"        		, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Used by Categories"      , "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Edit"       				, "20%" );
		$this->ipsclass->adskin->td_header[] = array( "Delete"       			, "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Mime-Type Masks" );
		
		require DL_PATH . 'lib/lib_cats.php';
		$this->lib = new lib_cats();
		$this->lib->ipsclass =& $this->ipsclass;
		$this->lib->normal_init();
		
		$dlist = "";
		
		//-----------------------------------------
		// Get 'em
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mimemask', 'order' => 'mime_masktitle' ) );
		$outer = $this->ipsclass->DB->simple_exec();
		
		while( $r = $this->ipsclass->DB->fetch_row($outer) )
		{
			$dlist .= "<option value='{$r['mime_maskid']}'>{$r['mime_masktitle']}</option>";
			$cat_ids = array();
			$cats_in_use = "";
			$cat_ids = $this->lib->get_cats_mimemask( $r['mime_maskid'] );
			
			if( ! count( $cat_ids ) )
			{
				$cats_in_use = "<center><i>None</i></center>";
				$delete = $this->ipsclass->adskin->js_make_button("Delete", "{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mask_delete&id={$r['mime_maskid']}", 'realdarkbutton' );
			}
			else
			{
				foreach( $cat_ids as $k => $v )
				{
					$cats_in_use .= "&middot;{$this->lib->cat_lookup[ $v ]['cname']}<br />";
				}
				$delete = "<i>In Use</i>";
			}
			
			$edit   = $this->ipsclass->adskin->js_make_button("Edit", "{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=types&id={$r['mime_maskid']}" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $r['mime_masktitle'],
																				 $cats_in_use,
																				 "<center>".$edit."</center>",
																				 "<center>".$delete."</center>",
																	   )      );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		$this->ipsclass->html .= <<<EOF
<br />
<form name='theAdminForm' id='adminform' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=mask_add' method='post'>
 <div class='tableborder'>
  <div class='tableheaderalt'>Create a new mime-type mask</div>
  <table cellpadding='4' cellspacing='0' width='100%'>
  <tr>
   <td class='tablerow1'><strong>Mime Mask Name</strong></td>
   <td class='tablerow2'><input type='text' class='input' size='30' name='new_mask_name' /></td>
  </tr>
  <tr>
   <td class='tablerow1'><strong>Inherit mask settings from...</strong></td>
   <td class='tablerow2'><select name='new_mask_copy' class='dropdown'>{$dlist}</select></td>
  </tr>
 </table>
 <div class='tablefooter' align='center'><input type='submit' value='Create' class='realbutton' /></div>
</div>
</form>
EOF;
		
		$this->ipsclass->admin->output();
	}
	
	
	//-----------------------------------------
	// TYPE: Import
	//-----------------------------------------
	
	function mime_type_import()
	{
		$content = $this->ipsclass->admin->import_xml( 'idm_mimetypes.xml' );
		
		//-----------------------------------------
		// Got anything?
		//-----------------------------------------
		
		if ( ! $content )
		{
			$this->ipsclass->main_msg = "Upload failed, idm_mimetypes.xml was either missing or empty";
			$this->mime_type_start();
		}
		
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Unpack the datafile
		//-----------------------------------------
		
		$xml->xml_parse_document( $content );
		
		//-----------------------------------------
		// Get current mime types
		//-----------------------------------------
		
		$types = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mime', 'order' => "mime_extension" ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$types[ $r['mime_extension'] ] = 1;
		}
		
		//-----------------------------------------
		// pArse
		//-----------------------------------------
		
		foreach( $xml->xml_array['mimetypesexport']['mimetypesgroup']['mimetype'] as $idx => $entry )
		{
			$insert_array = array( 'mime_extension' 	=> $entry['mime_extension']['VALUE'],
								   'mime_mimetype'  	=> $entry['mime_mimetype']['VALUE'],
								   'mime_file'      	=> 0,
								   'mime_screenshot'    => 0,
								   'mime_inline'		=> 0,
								   'mime_img'       	=> $entry['mime_img']['VALUE']
								 );
			
			if ( $types[ $entry['mime_extension']['VALUE'] ] )
			{
				continue;
			}
			
			if ( $entry['mime_extension']['VALUE'] and $entry['mime_mimetype']['VALUE'] )
			{
				$this->ipsclass->DB->do_insert( 'downloads_mime', $insert_array );
			}
		}
		
		$this->mime_type_rebuildcache();
                    
		$this->ipsclass->main_msg = "Download Mime Types XML file import completed";
		
		$this->mime_type_start();
	
	}
	
	//-----------------------------------------
	//
	// TYPES: Export
	//
	//-----------------------------------------
	
	function mime_type_export()
	{
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Start...
		//-----------------------------------------
		
		$xml->xml_set_root( 'mimetypesexport', array( 'exported' => time() ) );
		
		//-----------------------------------------
		// Get group
		//-----------------------------------------
		
		$xml->xml_add_group( 'mimetypesgroup' );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'mime_extension,mime_mimetype,mime_img',
									  'from'   => 'downloads_mime',
									  'order'  => "mime_extension" ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$content = array();
			
			foreach ( $r as $k => $v )
			{
				$content[] = $xml->xml_build_simple_tag( $k, $v );
			}
			
			$entry[] = $xml->xml_build_entry( 'mimetype', $content );
		}
		
		$xml->xml_add_entry_to_group( 'mimetypesgroup', $entry );
		
		$xml->xml_format_document();
		
		//-----------------------------------------
		// Send to browser.
		//-----------------------------------------
		
		$this->ipsclass->admin->show_download( $xml->xml_document, 'idm_mimetypes.xml' );
	}
	
	//-----------------------------------------
	//
	// TYPES: DELETE
	//
	//-----------------------------------------
	
	function mime_type_delete()
	{
		$this->ipsclass->input['mid'] = intval($this->ipsclass->input['mid']);
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_mime', 'where' => 'mime_id='.$this->ipsclass->input['mid'] ) );
		
		$this->mime_type_rebuildcache();
		
		$this->ipsclass->main_msg = "Downloads Mime type deleted";
		
		$this->mime_type_start();
	}
	
	//-----------------------------------------
	//
	// TYPES: SAVE (edit / add)
	//
	//-----------------------------------------
	
	function mime_type_save( $type='add' )
	{
		$this->ipsclass->input['mid'] = intval($this->ipsclass->input['mid']);
		
		//-----------------------------------------
		// Check basics
		//-----------------------------------------
		
		if ( ! $this->ipsclass->input['mime_extension'] or ! $this->ipsclass->input['mime_mimetype'] )
		{
			$this->ipsclass->main_msg = "You must enter at least an extension and mime-type before continuing.";
			$this->mime_type_form( $type );
		}
		
		$save_array = array( 'mime_extension' 	=> ( substr( $this->ipsclass->input['mime_extension'], 0, 1 ) == '.' ) ? ( substr( $this->ipsclass->input['mime_extension'], 1 ) ) : $this->ipsclass->input['mime_extension'],
							 'mime_mimetype'  	=> $this->ipsclass->input['mime_mimetype'],
							 'mime_file'      	=> $this->ipsclass->input['mime_file'] == 1 ? intval($this->ipsclass->input['id']) : 0,
							 'mime_screenshot'  => $this->ipsclass->input['mime_screenshot'] == 1 ? intval($this->ipsclass->input['id']) : 0,
							 'mime_inline'		=> $this->ipsclass->input['mime_inline'] == 1 ? intval($this->ipsclass->input['id']) : 0,
							 'mime_img'       	=> $this->ipsclass->input['mime_img']
						   );
		
		if ( $type == 'add' )
		{
			//-----------------------------------------
			// Check for existing..
			//-----------------------------------------
			
			$mime = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_mime', 'where' => "mime_extension='".$save_array['mime_extension']."'" ) );
			
			if ( $mime['mime_id'] )
			{
				$this->ipsclass->main_msg = "The extension '{$save_array['mime_extension']}' already exists, please choose another extension.";
				$this->mime_type_form($type);
			}
			
			$this->ipsclass->DB->do_insert( 'downloads_mime', $save_array );
			
			$this->ipsclass->main_msg = "Downloads Mime type added";
			
		}
		else
		{
			$r = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_mime', 'where' => "mime_extension='".$save_array['mime_extension']."'" ) );

			$new_files = array();
			$files = explode( ",", $r['mime_file'] );
					
			if( is_array( $files ) )
			{
				foreach( $files as $k => $v )
				{
					// Blank cats cause problems?
					if( $v == '' OR $v == 0 )
					{
						continue;
					}
					
					if( $v == $this->ipsclass->input['id'] )
					{
						if( $this->ipsclass->input['mime_file'] != 1 )
						{
							continue;
						}
					}
					
					$new_files[] = $v;
				}
			}
						
			if( $this->ipsclass->input['mime_file'] == 1 )
			{
				$new_files[] = intval($this->ipsclass->input['id']);
			}
			
			if( count($new_files) < 1 )
			{
				$new_files[] = 0;
			}			

			$new_screenshots = array();
			$screenshots = explode( ",", $r['mime_screenshot'] );
					
			if( is_array( $screenshots ) )
			{
				foreach( $screenshots as $k => $v )
				{
					if( $v == '' OR $v == 0 )
					{
						continue;
					}
					
					if( $v == $this->ipsclass->input['id'] )
					{
						if( $this->ipsclass->input['mime_screenshot'] != 1 )
						{
							continue;
						}
					}
					
					$new_screenshots[] = $v;
				}
			}
						
			if( $this->ipsclass->input['mime_screenshot'] == 1 )
			{
				$new_screenshots[] = intval($this->ipsclass->input['id']);
			}
			
			if( count($new_screenshots) < 1 )
			{
				$new_screenshots[] = 0;
			}
			
			$new_inline = array();
			$inline = explode( ",", $r['mime_inline'] );
					
			if( is_array( $inline ) )
			{
				foreach( $inline as $k => $v )
				{
					if( $v == '' )
					{
						continue;
					}
					
					if( $v == $this->ipsclass->input['id'] )
					{
						if( $this->ipsclass->input['mime_inline'] != 1 )
						{
							continue;
						}
					}
					
					$new_inline[] = $v;
				}
			}
						
			if( $this->ipsclass->input['mime_inline'] == 1 )
			{
				$new_inline[] = intval($this->ipsclass->input['id']);
			}
			
			$save_array['mime_file'] 		= implode( ",", array_unique($new_files) );
			$save_array['mime_screenshot']	= implode( ",", array_unique($new_screenshots) );
			$save_array['mime_inline']		= implode( ",", array_unique($new_inline) );
			
			$this->ipsclass->DB->do_update( 'downloads_mime', $save_array, 'mime_id='.$this->ipsclass->input['mid'] );
			
			$this->ipsclass->main_msg = "Downloads Mime type edited";
		}
		
		$this->mime_type_rebuildcache();
		
		$this->mime_type_start();
		
	}
	
	//-----------------------------------------
	//
	// TYPES: FORM (edit / add)
	//
	//-----------------------------------------
	
	function mime_type_form( $type='add' )
	{
		$this->ipsclass->input['mid']     = intval($this->ipsclass->input['mid']);
		$this->ipsclass->input['baseon'] = intval($this->ipsclass->input['baseon']);
		
		if ( $type == 'add' )
		{
			$code   = 'mime_doadd';
			$button = 'Add New Mime Type';
			
			if ( $this->ipsclass->input['baseon'] )
			{
				$mime = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_mime', 'where' => 'mime_id='.$this->ipsclass->input['baseon'] ) );

				$files 					 = explode( ",", $mime['mime_file'] );
				$mime['mime_file'] 		 = in_array( $this->ipsclass->input['id'], $files ) ? 1 : 0;

				$screenshots 			 = explode( ",", $mime['mime_screenshot'] );
				$mime['mime_screenshot'] = in_array( $this->ipsclass->input['id'], $screenshots ) ? 1 : 0;

				$inline 				 = explode( ",", $mime['mime_inline'] );
				$mime['mime_inline'] 	 = in_array( $this->ipsclass->input['id'], $inline ) ? 1 : 0;								
			}
			else
			{
				$mime = array();
			}
			
			//-----------------------------------------
			// Generate 'base on'
			//-----------------------------------------
			
			$dd = "";
			
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mime', 'order' => 'mime_extension' ) );
			$this->ipsclass->DB->simple_exec();
		
			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				$dd .= "<option value='{$r['mime_id']}'>Base on: {$r['mime_extension']}</option>\n";
			}
			
			$title = " <div style='float:right;width:auto;padding-right:3px;'>
					  <form method='post' action='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mime_add&amp;id={$this->ipsclass->input['id']}'>
					  <select name='baseon' class='realbutton'>{$dd}</select> &nbsp;<input type='submit' value='Go' class='realdarkbutton' />
					  </form>
					  </div><div>{$button}</div>";
			
		}
		else
		{
			$code   = 'mime_doedit';
			$button = 'Edit Mime Type';
			$title  = $button;
			
			$mime = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_mime', 'where' => 'mime_id='.$this->ipsclass->input['mid'] ) );
		
			if ( ! $mime['mime_id'] )
			{
				$this->ipsclass->main_msg = "No ID was passed, please try again.";
				$this->mime_type_start();
			}
			
			$files 					 = explode( ",", $mime['mime_file'] );
			$mime['mime_file'] 		 = in_array( $this->ipsclass->input['id'], $files ) ? 1 : 0;

			$screenshots 			 = explode( ",", $mime['mime_screenshot'] );
			$mime['mime_screenshot'] = in_array( $this->ipsclass->input['id'], $screenshots ) ? 1 : 0;

			$inline 				 = explode( ",", $mime['mime_inline'] );
			$mime['mime_inline'] 	 = in_array( $this->ipsclass->input['id'], $inline ) ? 1 : 0;
		}
		
		//-----------------------------------------
		// HEADER
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $title );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , $code     ),
																			 2 => array( 'act'    , 'downloads'  ),
																			 3 => array( 'req'	  , 'mime'	),
																			 4 => array( 'mid'     , $this->ipsclass->input['mid'] ),
																			 5 => array( 'section', $this->ipsclass->section_code ),
																			 6 => array( 'id'	  , $this->ipsclass->input['id'] ),
																	)      );

		//-----------------------------------------
		// FORM
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Extension</b><div style='color:gray'>This is the (usually) three character filename suffix.<br />You don't need to add the '.' before the extension</div>",
												 				 $this->ipsclass->adskin->form_simple_input( 'mime_extension', $_POST['mime_extension'] ? $_POST['mime_extension'] : $mime['mime_extension'], 10 ),
														)      );
														
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Mime-Type</b><div style='color:gray'>Unsure what the correct mime-type is?. <a href='http://www.utoronto.ca/webdocs/HTMLdocs/Book/Book-3ed/appb/mimetype.html' target='_blank'>Try looking here</a></div>",
												 				 $this->ipsclass->adskin->form_simple_input( 'mime_mimetype', $_POST['mime_mimetype'] ? $_POST['mime_mimetype'] : $mime['mime_mimetype'], 40 ),
														)      );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow this Mime-Type for files?</b>",
												 				 $this->ipsclass->adskin->form_yes_no( 'mime_file', $_POST['mime_file'] ? $_POST['mime_file'] : $mime['mime_file'] ),
														)      );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow this Mime-Type for screenshots?</b>",
												 				 $this->ipsclass->adskin->form_yes_no( 'mime_screenshot', $_POST['mime_screenshot'] ? $_POST['mime_screenshot'] : $mime['mime_screenshot'] ),
														)      );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Show file inline?</b><div style='color:gray'>By default the IP.Downloads Manager forces file requests to download, rather than displaying files inline when possible.  Setting this to yes will attempt to show this file type inline instead.</div>",
												 				 $this->ipsclass->adskin->form_yes_no( 'mime_inline', $_POST['mime_inline'] ? $_POST['mime_inline'] : $mime['mime_inline'] ),
														)      );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Mime-Type Mini-Image</b><div style='color:gray'>This is the little icon that represents the mime-type on the file details page.</div>",
												 				 $this->ipsclass->adskin->form_simple_input( 'mime_img', $_POST['mime_img'] ? $_POST['mime_img'] : $mime['mime_img'], 40 ),
														)      );	
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form($button);
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->admin->output();
	}
	
	
	//-----------------------------------------
	//
	// TYPES: Start
	//
	//-----------------------------------------
	
	function mime_type_start()
	{
		
		$this->ipsclass->DB->build_query( array( 'select' => 'mime_masktitle', 'from' => 'downloads_mimemask', 'where' => 'mime_maskid='.$this->ipsclass->input['id'] ) 	);
		$this->ipsclass->DB->exec_query();
		$mask = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'do_mask_edit' ),
																			 2 => array( 'act'   , 'downloads'        ),
																			 3 => array( 'req'	 , 'mime'	),
																			 4 => array( 'mask_id'	 , $this->ipsclass->input['id'] ),
																			 5 => array( 'section', $this->ipsclass->section_code ),
																	) 		);


		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"        , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"     	  , "80%" );
													  			
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Rename Mime-Mask" );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
													 		    "<b>New Mask Name</b>",
													  		    $this->ipsclass->adskin->form_input( "new_mask_name", $mask['mime_masktitle']  )
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Re-name");
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();				
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"        , "1%" );
		$this->ipsclass->adskin->td_header[] = array( "Extension"     , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "Mime-Type"     , "30%" );
		$this->ipsclass->adskin->td_header[] = array( "+File"         , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "+Screenshot"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "+Inline"   	  , "10%" );		
		$this->ipsclass->adskin->td_header[] = array( "Options"       , "20%" );
		
		$export_button = $this->ipsclass->adskin->js_make_button("Export Mime-Types", $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=mime_export");
		
		$table = "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
				  <td align='left' width='100%' style='font-weight:bold;font-size:11px;color:#FFF'>IP.Downloads Mime-Types</td>
				  <td align='right' nowrap='nowrap' style='padding-right:2px'>{$export_button}</td>
				  </tr>
				  </table>";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $table );
		
		//-----------------------------------------
		// Get 'em
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mime', 'order' => 'mime_extension' ) );
		$this->ipsclass->DB->simple_exec();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$files 					 = explode( ",", $r['mime_file'] );
			$r['mime_file'] 		 = in_array( $this->ipsclass->input['id'], $files ) ? 1 : 0;

			$screenshots 			 = explode( ",", $r['mime_screenshot'] );
			$r['mime_screenshot'] 	 = in_array( $this->ipsclass->input['id'], $screenshots ) ? 1 : 0;

			$inline 				 = explode( ",", $r['mime_inline'] );
			$r['mime_inline'] 	 	 = in_array( $this->ipsclass->input['id'], $inline ) ? 1 : 0;
						
			$checked_img    = "<img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' />";
			$file_checked   = $r['mime_file']  ? $checked_img : '&nbsp;';
			$ss_checked 	= $r['mime_screenshot'] ? $checked_img : '&nbsp;';
			$inline_checked	= $r['mime_inline'] ? $checked_img : '&nbsp;';
			
			$edit   = $this->ipsclass->adskin->js_make_button("Edit", "{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mime_edit&mid={$r['mime_id']}&id={$this->ipsclass->input['id']}" );
			$delete = $this->ipsclass->adskin->js_make_button("Delete", "{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mime_delete&mid={$r['mime_id']}&id={$this->ipsclass->input['id']}", 'realdarkbutton' );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->ipsclass->skin['_imagedir']}/{$r['mime_img']}' border='0' />",
																				 ".<strong>{$r['mime_extension']}</strong>",
																				 $r['mime_mimetype'],
																				 "<div align='center'>{$file_checked}</div>",
																				 "<div align='center'>{$ss_checked}</div>",
																				 "<div align='center'>{$inline_checked}</div>",
																				 "<div align='center'>{$edit} &nbsp; &nbsp; {$delete}</div>",
																	   )      );
		}
		
		$add_new = $this->ipsclass->adskin->js_make_button("Add New Mime-Type", "{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mime_add&id={$this->ipsclass->input['id']}" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( $add_new, "center", "tablesubheader");		
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// IMPORT: Start output
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'mime_import' ),
																			 2 => array( 'act'   , 'downloads'        ),
																			 3 => array( 'req'	 , 'mime'	),
																			 4 => array( 'MAX_FILE_SIZE', '10000000000' ),
																			 5 => array( 'section', $this->ipsclass->section_code ),
																			 6 => array( 'id'	 , $this->ipsclass->input['id'] ),
																	) , "uploadform", " enctype='multipart/form-data'"     );
													
													  			
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Import an Downloads Mime-Types List" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
													 		    "<b>Upload XML Mime-Types List</b><div style='color:gray'>Browse your computer for 'idm_mimetypes.xml' or 'idm_mimetypes.xml.gz'. Duplicate entries will not be imported.</div>",
													  		    $this->ipsclass->adskin->form_upload(  )
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Import");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	}
	
	//-----------------------------------------
	//
	// TYPES: Rebuild Cache
	//
	//-----------------------------------------
	
	function mime_type_rebuildcache()
	{
		$this->ipsclass->cache['idm_mimetypes'] = array();
			
		$this->ipsclass->DB->simple_construct( array( 'select' => 'mime_id,mime_extension,mime_mimetype,mime_file,mime_screenshot,mime_inline,mime_img', 'from' => 'downloads_mime' ) );
		$this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_mimetypes'][ $r['mime_extension'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_mimetypes', 'array' => 1, 'deletefirst' => 1 ) );
	}
	
	
	
	
	
	
	
}

?>