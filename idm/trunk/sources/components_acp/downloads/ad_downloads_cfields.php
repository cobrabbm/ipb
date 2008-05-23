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
|   > Custom Field Management
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 11, 2005 5:48 PM EST
|
|	> Module Version .02
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_cfields {

	var $base_url;
	var $func;
	var $lib;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';	

	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=customfields', 'IP.Downloads Custom Fields' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=customfields";
				
		//-----------------------------------------
		// Take a trip to the Library
		//-----------------------------------------
		
		require_once( DL_PATH . 'lib/lib_cfields.php' );
		$this->func = new lib_cfields( );
		$this->func->ipsclass =& $this->ipsclass;
		
		require DL_PATH . 'lib/lib_cats.php';
		$this->lib = new lib_cats();
		$this->lib->ipsclass =& $this->ipsclass;
		$this->lib->normal_init();
		
		switch($this->ipsclass->input['code'])
		{
			case 'add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->main_form('add');
				break;
				
			case 'doadd':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->main_save('add');
				break;
				
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->main_form('edit');
				break;
				
			case 'doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->main_save('edit');
				break;
				
			case 'reorder':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->do_reorder();
				break;
				
			case 'delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->delete_form();
				break;
				
			case 'dodelete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->do_delete();
				break;
						
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':customfields' );
				$this->main_screen();
				break;
		}
		
	}
	
	//-----------------------------------------
	//
	// Rebuild cache
	//
	//-----------------------------------------
	
	function rebuild_cache()
	{
		$this->ipsclass->cache['idm_cfields'] = array();
				
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'order' => 'cf_position' ) );
						 
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_cfields'][ $r['cf_id'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_cfields', 'array' => 1, 'deletefirst' => 1 ) );	
	}
	
	//-----------------------------------------
	//
	// Delete a field
	//
	//-----------------------------------------
	
	function delete_form()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("Could not resolve the custom field ID, please try again");
		}
		
		$this->ipsclass->admin->page_title = "Deleting a Custom Download Field";
		
		$this->ipsclass->admin->page_detail = "Please check to ensure that you are attempting to remove the correct custom field as <b>all data will be lost!</b>.";
		
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'where' => "cf_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( ! $field = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("Could not find the field in the database");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'dodelete'  ),
																 			 2 => array( 'act'    , 'downloads'     ),
																 			 3 => array( 'id'     , $this->ipsclass->input['id']   ),
																 			 4 => array( 'section', $this->ipsclass->section_code ),
																 			 5 => array( 'req'	  , 'customfields'	),
																	)      );
									     
		
		
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Removal Confirmation" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Custom field to remove</b>" ,
												                 "<b>".$field['cf_title']."</b>",
									                   )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Delete this custom field");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	
	
	function do_delete()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("Could not resolve the field ID, please try again");
		}
		
		//-----------------------------------------
		// Verify field existence
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'where' => "cf_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( ! $row = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("Could not resolve the ID's passed to deletion");
		}
		
		$this->ipsclass->DB->sql_drop_field( 'downloads_ccontent', "field_{$row['cf_id']}" );
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_cfields', 'where' => "cf_id=".intval($this->ipsclass->input['id']) ) );
		
		$this->rebuild_cache();
		
		$this->ipsclass->admin->done_screen("Custom Field Removed", "Custom Download Field Management", "{$this->ipsclass->form_code}", 'redirect' );
		
	}
	
	
	//-----------------------------------------
	//
	// Save changes to DB
	//
	//-----------------------------------------
	
	function main_save($type='edit')
	{
		$id = intval($this->ipsclass->input['id']);
		
		if ($this->ipsclass->input['cf_title'] == "")
		{
			$this->ipsclass->admin->error("You must enter a field title.");
		}
		
		//-----------------------------------------
		// check-da-motcha
		//-----------------------------------------
		
		if ($type == 'edit')
		{
			if( !$id )
			{
				$this->ipsclass->admin->error("Could not resolve the field id");
			}
			
		}
		
		$content = "";
		
		if ( $_POST['cf_content'] != "")
		{
			$content = $this->func->method_format_content_for_save( $_POST['cf_content'] );
		}
		
		$db_string = array( 'cf_title'        => $this->ipsclass->input['cf_title'],
						    'cf_desc'         => $this->ipsclass->input['cf_desc'],
						    'cf_content'      => $this->ipsclass->txt_stripslashes($content),
						    'cf_type'         => $this->ipsclass->input['cf_type'],
						    'cf_not_null'     => $this->ipsclass->input['cf_not_null'],
						    'cf_max_input'    => $this->ipsclass->input['cf_max_input'],
						    'cf_input_format' => $this->ipsclass->input['cf_input_format'],
						    'cf_topic'		  => $this->ipsclass->input['cf_topic'],
						    'cf_search'		  => $this->ipsclass->input['cf_search'],
						  );

		if ($type == 'edit')
		{
			$this->ipsclass->DB->do_update( 'downloads_cfields', $db_string, 'cf_id=' . $id );
			
			$this->rebuild_cache();
			
			$this->ipsclass->main_msg = "Custom Field Edited";
		}
		else
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'MAX(cf_position) as newpos',
															'from' => 'downloads_cfields' ) );
			$this->ipsclass->DB->simple_exec();			
			$max = $this->ipsclass->DB->fetch_row();
			
			$db_string['cf_position'] = $max['newpos']+1;
			
			$this->ipsclass->DB->do_insert( 'downloads_cfields', $db_string );
			
			$id = $this->ipsclass->DB->get_insert_id();
			
			$this->ipsclass->DB->sql_add_field( 'downloads_ccontent', "field_{$id}", 'text' );
			
			$this->ipsclass->DB->sql_optimize_table( 'downloads_ccontent' );
			
			$this->rebuild_cache();
			
			$this->ipsclass->main_msg = "Custom Field Added";
		}
		
		if( is_array($this->ipsclass->input['cats_apply']) AND count($this->ipsclass->input['cats_apply']) )
		{
			$did_at_least_one = 0;
			
			foreach( $this->lib->cat_lookup as $cid => $cdata )
			{
				$cfields = explode( ',', $cdata['ccfields'] );
				
				if( !in_array( $id, $cfields ) )
				{
					if( in_array( $cid, $this->ipsclass->input['cats_apply'] ) )
					{
						array_push( $cfields, $id );
						
						$this->ipsclass->DB->do_update( 'downloads_categories', array( 'ccfields' => implode( ',', $cfields ) ), 'cid=' . $cid );
						
						$did_at_least_one = 1;
					}
				}
				else
				{
					if( !in_array( $cid, $this->ipsclass->input['cats_apply'] ) )
					{
						$new_cfields = array();
						
						foreach( $cfields as $fid )
						{
							if( $fid != $id )
							{
								$new_cfields[] = $fid;
							}
						}
						
						$this->ipsclass->DB->do_update( 'downloads_categories', array( 'ccfields' => implode( ',', $new_cfields ) ), 'cid=' . $cid );
						
						$did_at_least_one = 1;
					}
				}
			}
			
			if( $did_at_least_one )
			{
				$this->lib->rebuild_cat_cache();
			}
		}

		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code.'&req=customfields' );
	}
	
	
	//-----------------------------------------
	//
	// Add / edit group
	//
	//-----------------------------------------
	
	function main_form($type='edit')
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		
		if ($type == 'edit')
		{
			if ( ! $this->ipsclass->input['id'] )
			{
				$this->ipsclass->admin->error("No custom field id was passed to edit.");
			}
			
			$form_code = 'doedit';
			$button    = 'Complete Edit';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = 'Add Field';
		}
		
		//-----------------------------------------
		// Get field from db
		//-----------------------------------------
		
		if ( $this->ipsclass->input['id'] )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'where' => "cf_id=".intval($this->ipsclass->input['id']) ) );
			$this->ipsclass->DB->simple_exec();
		
			$fields = $this->ipsclass->DB->fetch_row();
		}
		else
		{
			$fields = array();
		}
		
		//-----------------------------------------
		// Top 'o 'the mornin'
		//-----------------------------------------
		
		if ($type == 'edit')
		{
			$this->ipsclass->admin->page_title = "Editing Custom Field ".$fields['cf_title'];
		}
		else
		{
			$this->ipsclass->admin->page_title = 'Adding a new custom field';
			$fields['cf_title'] = '';
		}
		
		//-----------------------------------------
		// Wise words
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "Please double check the information before submitting the form.";
		
		//-----------------------------------------
		// Start form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , $form_code  ),
												                 			 2 => array( 'act'    , 'downloads'     ),
												                 			 3 => array( 'id'     , $this->ipsclass->input['id']   ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
												                 			 5 => array( 'req'	  , 'customfields'	),
									                    )     );
		
		//-----------------------------------------
		// Format...
		//-----------------------------------------
									     
		$fields['cf_content'] = $this->func->method_format_content_for_edit($fields['cf_content'] );
		
		//-----------------------------------------
		// Tbl (no ae?)
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Field Settings" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Field Title</b><div class='graytext'>Max characters: 200</div>" ,
												                 $this->ipsclass->adskin->form_input("cf_title", $fields['cf_title'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Description</b><div class='graytext'>Max Characters: 250</div>" ,
												                 $this->ipsclass->adskin->form_input("cf_desc", $fields['cf_desc'] )
									                    )      );
									  
		//-----------------------------------------
		// Apply to categories
		//-----------------------------------------
		
		$sel_menu = "<select name='cats_apply[]' size='5' multiple='multiple'>\n";
		
		$cur 	  = $this->lib->get_cats_cfield( $fields['cf_id'] );
		$opts	  = $this->lib->cat_jump_list( 1, 'none', $cur );

		if( is_array($opts) AND count($opts) )
		{
			foreach( $opts as $cdata )
			{
				if( is_array($cur) AND in_array( $cdata[0], $cur ) )
				{
					$cdata[2] = " selected='selected'";
				}
				
				$sel_menu .= "<option value='{$cdata[0]}'{$cdata[2]}>{$cdata[1]}</option>\n";
			}
		}
		
		$sel_menu .= "</select>";
			
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Use in Categories</b><div class='graytext'>Select the categories to use this field in</div>" ,
												                 $sel_menu
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Field Type</b>" ,
																 					  $this->ipsclass->adskin->form_dropdown("cf_type",
																					  array(
																							   0 => array( 'text' , 'Text Input' ),
																							   1 => array( 'drop' , 'Drop Down Box' ),
																							   2 => array( 'area' , 'Text Area' ),
																						   ),
																					  $fields['cf_type'] )
														)      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum Input</b><div class='graytext'>For text input and text areas (in characters)</div>" ,
												                 $this->ipsclass->adskin->form_input("cf_max_input", $fields['cf_max_input'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Expected Input Format</b><div class='graytext'>Use: <b>a</b> for alpha characters<br />Use: <b>n</b> for numerics.<br />Example, for credit card numbers: nnnn-nnnn-nnnn-nnnn<br />Example, Date of Birth: nn-nn-nnnn<br />Leave blank to accept any input</div>" ,
												                 $this->ipsclass->adskin->form_input("cf_input_format", $fields['cf_input_format'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Option Content (for drop downs)</b><div class='graytext'>In sets, one set per line<br>Example for 'Software Version' field:<br>10=1.0<br>20=2.0<br>na=Not Applicable<br>Will produce:<br><select name='version'><option value='10'>1.0</option><option value='20'>2.0</option><option value='na'>Not Applicable</option></select><br>10,20, or na stored in database. When showing field on download page, will use value from pair (20=2.0, shows '2.0')</div>" ,
												                 $this->ipsclass->adskin->form_textarea("cf_content", $fields['cf_content'] )
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Field MUST be completed and not left empty?</b><div class='graytext'>If 'yes', an error will be shown if this field is not completed.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("cf_not_null", $fields['cf_not_null'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Include field in auto-generated topics?</b><div class='graytext'>Only applies to categories that automatically generate topics for file submissions</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("cf_topic", $fields['cf_topic'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow users to search in these fields?</b>" ,
												                 $this->ipsclass->adskin->form_yes_no("cf_search", $fields['cf_search'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form($button);
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
			
	}
	
	//-----------------------------------------
	//
	// Re-order positioning
	//
	//-----------------------------------------
		
	function do_reorder()
	{
		$ids = array();
 		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^f_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$ids[ $match[1] ] = $this->ipsclass->input[$match[0]];
 				}
 			}
 		}
 		
 		//-----------------------------------------
 		// Save changes
 		//-----------------------------------------
 		
 		if ( count($ids) )
 		{ 
 			foreach( $ids as $field_id => $new_position )
 			{
 				$this->ipsclass->DB->do_update( 'downloads_cfields', array( 'cf_position' => intval($new_position) ), 'cf_id='.$field_id );
 			}
 		}
 		
 		$this->rebuild_cache();
 		
 		$this->ipsclass->boink_it( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code );
	}	

	//-----------------------------------------
	//
	// Show "Management Screen
	//
	//-----------------------------------------
	
	function main_screen()
	{
		$this->ipsclass->admin->page_title   = "Custom Download Fields";
		
		$this->ipsclass->admin->page_detail  = "Custom fields can be used to add optional or required fields to be completed when submitting downloads to the database. This is useful if you wish to record data from your members that is not already present in the base package (i.e. software versions, operating system compatibility, etc.).";
		
		$this->ipsclass->adskin->td_header[] = array( "Title"    				, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Used by Categories"      , "25%" );
		$this->ipsclass->adskin->td_header[] = array( "Type"           			, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Required"       			, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Position"        		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"         			, "15%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'reorder'  ),
												                 			 2 => array( 'act'    , 'downloads'     ),
												                 			 3 => array( 'id'     , $this->ipsclass->input['id']   ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
												                 			 5 => array( 'req'	  , 'customfields'	),
									                    )     );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Custom Download Field Management" );
									                    		
		$real_types = array( 'drop' => 'Drop Down Box',
							 'area' => 'Text Area',
							 'text' => 'Text Input',
						   );
						   
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'order' => 'cf_position' ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			$position = array();
			
			for( $i=1; $i<=$this->ipsclass->DB->get_num_rows(); $i++ )
			{
				$position[] = array( $i, $i );
			}
			
			$max_pos = 1;
				
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$checked_img    = "<center><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' /></center>";			
				$crossed_img    = "<center><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' /></center>";			
				
				$req    = $r['cf_not_null'] ? $checked_img : $crossed_img;
				
				$menu_html = <<<EOF
				<img id="menum-{$r['cf_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' />
				<script type="text/javascript">
  					menu_build_menu(
  						"menum-{$r['cf_id']}",
  						new Array( 
  			 				img_edit   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&req=customfields&code=edit&id={$r['cf_id']}'>Edit...</a>",
  			 				img_delete   + " <a href='javascript:maincheckdelete(\"{$this->ipsclass->base_url}&{$this->ipsclass->form_code_js}&req=customfields&code=delete&id={$r['cf_id']}\",\"Are you sure you wish to delete this custom field?\");'>Delete...</a>"
		    			) 	);
		    	</script>
EOF;
				$cat_ids 		= $this->lib->get_cats_cfield( $r['cf_id'] );
				$cats_in_use 	= "";
				
				if( ! count( $cat_ids ) )
				{
					$cats_in_use = "<center><i>None</i></center>";
				}
				else
				{
					foreach( $cat_ids as $k => $v )
					{
						$cats_in_use .= "&middot;{$this->lib->cat_lookup[ $v ]['cname']}<br />";
					}
				}
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$r['cf_title']}</b><div class='graytext'>{$r['cf_desc']}</div>" ,
																		 $cats_in_use,
																		 "<center>{$real_types[$r['cf_type']]}</center>",
																		 $req,
																 		 $this->ipsclass->adskin->form_dropdown( "f_{$r['cf_id']}", $position, $max_pos ),
																 		 $menu_html
															)      );
				$max_pos++;
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("None found", "center", "tablerow1");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$html = "
			<div class='tableborder'>
			<table cellpadding='4' cellspacing='0' width='100%' border='0' class='tablerow1'>
			 <tr>
				<td align='left' width='30%'>&nbsp;</td>
 				<td align='center' width='40%'><input type='button' class='realbutton' value='Add New Custom Field' onclick='locationjump(\"&{$this->ipsclass->form_code}&code=add\")' /></td>
 				<td align='right' width='30%'><input type='submit' value='Reorder Fields' class='realbutton' /></form></td>
			 </tr>
			</table>
			</div>";
			
		$this->ipsclass->html .= $html;

		$this->ipsclass->admin->output();

	}
}


?>