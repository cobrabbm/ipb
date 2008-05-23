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
|   > Admin Category + Moderator Control
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .07
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_cats {
	
	var $no_cats	= 0;
	var $printed	= 0;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';
	
	var $seen_mods  = array();

	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=categories', 'IP.Downloads Category Management' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=categories";
		$this->ipsclass->form_code_js = $this->ipsclass->form_code_js."&req=categories";

		require_once( DL_PATH . 'lib/lib_cats.php' );
		$this->func = new lib_cats( );
		$this->func->ipsclass =& $this->ipsclass;
		$this->func->full_init();
		
		$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_idm_queries.php', 'sql_idm_queries' );

		switch($this->ipsclass->input['code'])
		{
			case 'main':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_screen();
				break;
							
			case 'new':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_form('new');
				break;
			case 'donew':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_save('new');
				break;
			//-----------------------------------------
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_form('edit');
				break;
			case 'doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_save('edit');
				break;
			//-----------------------------------------
			case 'reorder':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->reorder_form();
				break;
			case 'doreorder':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->do_reorder();
				break;
				
			//-----------------------------------------
			case 'modform':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->show_mod_form('add');
				break;
			case 'editmod':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->show_mod_form('edit');
				break;				
			case 'domod':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->do_moderator('add');
				break;
			case 'doeditmod':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->do_moderator('edit');
				break;
			case 'delmod':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->del_moderator();
				break;				

			//-----------------------------------------
			case 'dodelete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->do_delete();
				break;
			case 'resynch':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->recount();
				break;
			case 'doempty':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->do_empty();
				break;
			//-----------------------------------------

			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':categories' );
				$this->main_screen();
				break;
		}
		
	}
	
	function main_screen()
	{
		$this->ipsclass->admin->page_title   = "Download Categories Overview";
		$this->ipsclass->admin->page_detail  = "You can manage your categories from here. Roll your mouse over the icons for more information.";
		
		//-----------------------------------------
		// Nav
		//-----------------------------------------
		
		if ( $this->ipsclass->input['c'] )
		{
			$nav = $this->func->get_nav( $this->ipsclass->input['c'], '&'.$this->ipsclass->form_code.'&c=', 1);
			
			if ( is_array($nav) and count($nav) > 1 )
			{
				array_shift($nav);
				
				$this->ipsclass->html .= "<div class='navstrip'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}'>Categories</a> &gt; ".implode( " &gt; ", $nav )."</div><br />";
			}
		}
		

		//-----------------------------------------
		// Print screen
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'doreorder'	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'categories'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													             			 5 => array( 'c'	  , $this->ipsclass->input['c'] ),
													    			)      );
		
		$seen_count  = 0;
		$total_items = 0;
		$temp_html = "";

		if( count( $this->func->cat_cache[ 0 ] ) == 0 )
		{
			$this->ipsclass->html .= "
				<div class='bad-box' style='margin-bottom:10px'>
<table cellpadding='0' cellspacing='0'>
<tr>
	<td width='1%' valign='middle'>
			<img src='{$this->ipsclass->skin_acp_url}/images/global-infoicon.gif' alt='information' />
	</td>
	<td width='71%' valig='top' style='margin-left:10px'>
		 <div style='font-size:14px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:5px;margin-bottom:5px;margin-right:5px'>Attention</div>
		 <div>You have not created any categories yet.  Click the 'Add New Category' button to begin.</div>
	</td>
</tr>
</table>
</div>
				<br />";
			$this->no_cats = 1;
		}
		else
		{
			if( $this->ipsclass->input['c'] AND $this->func->cat_lookup[$this->ipsclass->input['c']]  )
			{
				$depth_guide = $this->func->parent_lookup[$this->ipsclass->input['c']];
			}
			else
			{
				$depth_guide = 0;
			}
			
			foreach( $this->func->cat_cache[$depth_guide] as $id => $outer_data )
			{
				$this->seen_mods = array();
				
				if ( is_array( $this->func->cat_cache[ $outer_data['cid'] ] ) )
				{
					$temp_html = '<br /><fieldset style="margin-top:4px"><legend><b>Sub-Categories</b></legend>';
					$sub = array();
					
					foreach( $this->func->cat_cache[ $outer_data['cid'] ] as $id => $data )
					{
						$sub[] = "<a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&c={$data['cid']}'>".$this->func->cat_lookup[$data['cid']]['cname']."</a>";
					}
					$temp_html .= implode( ", ", $sub ).'</fieldset>';
				}
				
				if( is_array($this->func->cat_mods[ $outer_data['cid'] ] ) )
				{
					$temp_html .= '<br /><fieldset style="margin-top:4px"><legend><b>Moderators</b></legend>';
					$mod = array();
					
					foreach( $this->func->cat_mods[ $outer_data['cid'] ] as $id => $data )
					{
						$data['c'] = $outer_data['cid'];
						
						if( in_array( $data['mem_id'], $this->seen_mods ) )
						{
							continue;
						}
						else if( in_array( $data['group_id'], $this->seen_mods ) )
						{
							continue;
						}
						
						if( $data['modtype'] == 0 )
						{
							
							$data['_fullname'] = "Group: ".$data['group_name'];
							$mod[] = $this->moderator_dropdown( $data );
							$this->seen_mods[] = $data['group_id'];
						}
						else
						{
							$data['_fullname'] = $data['mem_name'];
							$mod[] = $this->moderator_dropdown( $data );
							$this->seen_mods[] = $data['mem_id'];
						}
					}
					
					$temp_html .= implode( "", $mod ).'</fieldset>';
				}					

				$this->ipsclass->html .= $this->render_category( $temp_html, $outer_data );
				unset($temp_html);
			}
		}
		
		if( ! $this->no_cats )
		{
			$choose = "";
			$options_cats = "";
			$jump_cats = $this->func->cat_jump_list(1);
			
			if( is_array($jump_cats) AND count($jump_cats) > 0 )
			{
				foreach( $jump_cats as $k => $v )
				{
					if( count( $this->func->subcat_lookup[ $v[0] ] ) > 0 )
					{
						$options_cats .= "<option value='{$v[0]}'>{$v[1]}</option>";
					}
				}
				
				if( $option_cats != '' )
				{
					$choose = "<select name='roots' class='realbutton'>";
					$choose .= $options_cats;
					$choose .= "</select>";
				}
			}
			
			$this->ipsclass->html .= "
				<script type='text/javascript'>
					function gochildrenofthecorn()
					{
   						var chosenroot = document.forms[0].roots.options[document.forms[0].roots.selectedIndex].value;
   						self.location.href = '{$this->ipsclass->base_url}&{$this->ipsclass->form_code_js}&code=reorder&c=' + chosenroot;
					}
				</script>";			
		}
		
		$this->ipsclass->html .= "
				<div class='tableborder'>
				<table cellpadding='4' cellspacing='0' width='100%' border='0' class='tablerow1'>
				<tr>
 				<td align='left' valign='middle'>";
 				
 		if( ! $this->no_cats )
 		{
	 		if( $choose != '' )
	 		{
				$this->ipsclass->html .= "{$choose}&nbsp;<input type='button' class='realbutton' value='Reorder Children' onclick='gochildrenofthecorn()'/>";
			}
		}
		else
		{
			$this->ipsclass->html .= "&nbsp;";
		}
		
		$this->ipsclass->html .= "
				</td>
 				<td align='right'><input type='button' class='realbutton' value='Add New Category' onclick='locationjump(\"&{$this->ipsclass->form_code}&code=new&p=0\")' />
 					&nbsp;&nbsp;<input type='submit' value='Reorder Categories' class='realbutton' /></form>
 				</td>
				</tr>
				</table>
				</div>
				<br />";
				
		$this->ipsclass->admin->output();
	}
	
	
	function reorder_form()
	{
		$this->ipsclass->admin->page_title   = "Download Categories Reordering";
		$this->ipsclass->admin->page_detail  = "You can reorder your categories from here.";
		
		//-----------------------------------------
		// Nav
		//-----------------------------------------
		
		if ( $this->ipsclass->input['c'] )
		{
			$nav = $this->func->get_nav( $this->ipsclass->input['c'], '&'.$this->ipsclass->form_code.'&c=', 1);
			
			if ( is_array($nav) and count($nav) > 1 )
			{
				array_shift($nav);
				
				$this->ipsclass->html .= "<div class='navstrip'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}'>Categories</a> &gt; ".implode( " &gt; ", $nav )."</div><br />";
			}
		}
		else
		{
			$this->ipsclass->admin->error("You must select a category to reorder the children");
		}
		
		if( count( $this->func->subcat_lookup[ $this->ipsclass->input['c'] ] ) == 0 )
		{
			$this->ipsclass->admin->error("This category has no subcategories to reorder");
		}			
		

		//-----------------------------------------
		// Print screen
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'doreorder'	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'categories'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
		
		$seen_count  = 0;
		$total_items = 0;
		$temp_html = "";

		if( count( $this->func->cat_cache[ 0 ] ) == 0 )
		{
			$this->ipsclass->html .= "
				<div id='subtitle'>You have not created any categories yet!  Click the 'Add New Category' button below to start.</div>
				<br />";
			$this->no_cats = 1;
		}
		else
		{
			foreach( $this->func->subcat_lookup[ $this->ipsclass->input['c'] ] as $key => $catid )
			{
				$catinfo = $this->func->cat_lookup[$catid];
				$temp_html = "";
				$this->ipsclass->html .= $this->render_category( $temp_html, $catinfo, 0, 1 );
			}
		}
		
		$this->ipsclass->html .= "
				<div class='tableborder'>
				<table cellpadding='4' cellspacing='0' width='100%' border='0' class='tablerow1'>
				<tr>
 				<td align='center'><input type='submit' value='Reorder Categories' class='realbutton' /></form></td>
				</tr>
				</table>
				</div>
				<br />";
		
		$this->ipsclass->admin->output();
	}	
	
	
	function do_reorder()
	{
		$ids = array();
 		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^c_(\d+)$/", $key, $match ) )
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
 			foreach( $ids as $cat_id => $new_position )
 			{
 				$this->ipsclass->DB->do_update( 'downloads_categories', array( 'cposition' => intval($new_position) ), 'cid='.$cat_id );
 			}
 		}
 		
 		$this->func->rebuild_cat_cache();
 		
 		$this->ipsclass->boink_it( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code.'&c='.$this->ipsclass->input['c'] );
	}		
	
	
	function recount()
	{
		$catid = intval($this->ipsclass->input['c']);
		
		$this->func->rebuild_fileinfo( $catid );
 		
 		$this->ipsclass->boink_it( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code .'&c='.$catid );
	}
	
	
	function do_empty()
	{
		$catid = intval($this->ipsclass->input['c']);
		
		if( !$catid )
		{
			$this->ipsclass->main_msg = "You did not select a category to empty!";
			$this->main_screen();
			return;
		}
		
		$file_ids = array();
		
		$this->ipsclass->DB->build_query( array( 'select' => 'file_id', 'from' => 'downloads_files', 'where' => 'file_cat='.$catid )	);
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$file_ids[$r['file_id']] = $r['file_id'];
		}
		
		if( count($file_ids) > 0 )
		{
			require DL_PATH.'moderate.php';
			$mod = new idm_moderate();
			$mod->ipsclass =& $this->ipsclass;
			$mod->catlib =& $this->func;
			
			$cnt = $mod->do_multi_delete( $file_ids );
		}
		
		if( !$cnt )
		{
			$cnt = 0;
		}
			
		$this->func->rebuild_fileinfo( $catid );
		$this->func->rebuild_cat_cache();
		$this->func->rebuild_stats_cache();
 		
 		$this->ipsclass->main_msg = "{$this->func->cat_lookup[$catid]['cname']} emptied ({$cnt} files deleted)!";
 		$this->ipsclass->admin->save_log("{$this->func->cat_lookup[$catid]['cname']} emptied ({$cnt} files deleted)!");
 		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code );
	}	
	
	
	function do_delete()
	{
		$catid = intval($this->ipsclass->input['c']);
		
		if( !$catid )
		{
			$this->ipsclass->main_msg = "You did not select a category to delete!";
			$this->main_screen();
			return;
		}
		
		$file_ids = array();
		
		$this->ipsclass->DB->build_query( array( 'select' => 'file_id', 'from' => 'downloads_files', 'where' => 'file_cat='.$catid )	);
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$file_ids[$r['file_id']] = $r['file_id'];
		}
		
		if( count($file_ids) > 0 )
		{
			require DL_PATH.'moderate.php';
			$mod = new idm_moderate();
			$mod->ipsclass =& $this->ipsclass;
			$mod->catlib =& $this->func;
			
			$cnt = $mod->do_multi_delete( $file_ids );
		}
		
		if( !$cnt )
		{
			$cnt = 0;
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_categories', 'where' => 'cid='.$catid )	);
		
		$this->func->rebuild_cat_cache();
		$this->func->rebuild_stats_cache();
		
		$this->func->normal_init();
 		
 		$this->ipsclass->main_msg = "{$this->func->cat_lookup[$catid]['cname']} deleted ({$cnt} files removed)!";
 		$this->ipsclass->admin->save_log("{$this->func->cat_lookup[$catid]['cname']} deleted ({$cnt} files removed)!");
 		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code );
	}	
	
	
	function render_category( $content, $cat, $show_buttons=1, $show_reorder=0 )
	{
		$this->printed++;
		
		$bar_id = $this->ipsclass->input['c'] ? $this->func->cat_lookup[ $this->ipsclass->input['c'] ]['cparent'] : 0;
		$no_root = count( $this->func->cat_cache[ $bar_id ] );
		$reorder = "";
		$reorder = "<select id='editbutton' name='c_{$cat['cid']}'>";
		for( $i = 1 ; $i <= $no_root ; $i++ )
		{
			$sel = "";
				
			if ( $this->printed == $i )
			{
				$sel =  'selected="selected" ';
			}
				
			$reorder .= "\n<option value='$i'{$sel}>$i</option>";
		}
		$reorder .= "</select>\n";

		$html = "
				<div class='tableborder'>
 				<div class='tableheaderalt'>";

		if ( $show_buttons )
		{
			$html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 				<tr>
  				<td align='left' width='95%' style='font-size:12px; vertical-align:middle; font-weight:bold; color:#FFF;' title='ID: {$cat['cid']}'>{$cat['cname']}</td>
  				<td align='right' width='5%' nowrap='nowrap' style='padding-right:8px;'>
   					{$reorder}
   					
   					<img id=\"menum-{$cat['cid']}\" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' />
 				</td>
 				</tr>
				</table>";
		}
		else if ( $show_reorder )
		{
			$html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				<tr>
 				<td align='left' width='40%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;' title='ID: {$cat['cid']}'>{$cat['cname']}</td>
 				<td align='right' width='60%'>
 					{$reorder}
 					&nbsp;&nbsp;
 				</td>
				</tr>
				</table>";
		}
		else
		{
			$html .= $cat['cname'];
		}

		$html .= "</div>
 				<table cellpadding='0' cellspacing='0' width='100%'>
 				 <tr>
 				  <td class='tablerow1' width='95%'>{$cat['cdesc']}<br />
 					{$content}
 				  </td>
 				 </tr>
 				</table>
				</div>
				<script type=\"text/javascript\">
  					menu_build_menu(
  						\"menum-{$cat['cid']}\",
  						new Array( img_add   + \" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=new&p={$cat['cid']}'>New Category...</a>\",
  			 				img_edit   + \" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&c={$cat['cid']}'>Edit Settings...</a>\",
  			 				img_password   + \" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=modform&c={$cat['cid']}'>Add Moderator...</a>\",
  			 				img_delete   + \" <a href='javascript:confirm_cat_delete({$cat['cid']})'>Delete Category...</a>\",
  			 				img_delete   + \" <a href='javascript:confirm_cat_empty({$cat['cid']})'>Empty Category...</a>\",
  			 				img_action   + \" <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=resynch&c={$cat['cid']}'>Resynchronize Category...</a>\"
		    			) 	);
		    			
		    		function confirm_cat_delete( catid )
		    		{
			    		if( catid < 1 )
			    		{
				    		alert(\"Category id missing\");
			    		}
			    		else
			    		{
				    		maincheckdelete(\"{$this->ipsclass->base_url}&{$this->ipsclass->form_code_js}&code=dodelete&c=\" +catid,\"Are you sure you wish to delete this category and all of it's contents? There will be no other confirmation screens, and you cannot undo this action!\");
			    		}
		    		}
		    		
		    		function confirm_cat_empty( catid )
		    		{
			    		if( catid < 1 )
			    		{
				    		alert(\"Category id missing\");
			    		}
			    		else
			    		{
				    		maincheckdelete(\"{$this->ipsclass->base_url}&{$this->ipsclass->form_code_js}&code=doempty&c=\" +catid,\"Are you sure you wish to empty this category? There will be no other confirmation screens, and you cannot undo this action!\");
			    		}
		    		}		    		
 				</script>
				<br />";		
		
		return $html;
	}
	
	
	function main_save($type='new')
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$this->ipsclass->input['cname'] = trim($this->ipsclass->input['cname']);
		$this->ipsclass->input['c']     = intval($this->ipsclass->input['c']);
		
		//-----------------------------------------
		// Check
		//-----------------------------------------
		
		if ($this->ipsclass->input['cname'] == "")
		{
			$this->ipsclass->main_msg = "You must enter a category name";
			$this->main_form( $type );
			return;
		}
		

		if ( $this->ipsclass->input['cparent'] != $this->func->cat_lookup[ $this->ipsclass->input['c'] ]['cparent'] )
		{
			if( $this->ipsclass->input['cparent'] != 0 AND $this->ipsclass->input['c'] != 0)
			{
				$ids   = $this->func->get_children( $this->ipsclass->input['c'] );
				$ids[] = $this->ipsclass->input['c'];
				
				if ( in_array( $this->ipsclass->input['cparent'], $ids ) )
				{
					$this->ipsclass->main_msg = "You have specified an invalid parent category.";
					$this->main_form( $type );
					return;
				}
			}
		}
		
		// Let's set an array with all of the custom fields we wish to show.
		// We'll then strip the 0's and duplicates.
		$ids = array();
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^cfield_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$ids[ $match[1] ] = $this->ipsclass->input[$match[0]];
 				}
 			}
 		}
 		
 		if( count($ids) )
 		{
 			$custom_fields = implode(",", array_unique($ids) );
		}
		else
		{
			$custom_fields = "";
		}
		
		if( $this->ipsclass->input['opt_topice'] == 1 )
		{
			if( !$this->ipsclass->input['opt_topicf'] )
			{
				$this->ipsclass->main_msg = "If you enable topic generation, you must specify a forum.";
				$this->main_form( $type );
				return;
			}
			
			if( !$this->ipsclass->cache['forum_cache'][$this->ipsclass->input['opt_topicf']]['sub_can_post'] )
			{
				$this->ipsclass->main_msg = "You cannot specify a root category as the forum for IP.Downloads topics";
				$this->main_form( $type );
				return;
			}
			
			if( $this->ipsclass->cache['forum_cache'][$this->ipsclass->input['opt_topicf']]['redirect_on'] )
			{
				$this->ipsclass->main_msg = "You cannot specify a redirect forum as the forum for IP.Downloads topics";
				$this->main_form( $type );
				return;
			}			
		}
		
		$max_upload = @ini_get('upload_max_filesize') ? @ini_get('upload_max_filesize') : 0;
		
		if( substr( $max_upload, -1, 1 ) == "M" )
		{
			$max_upload = substr( $max_upload, 0, -1 );
			$max_upload = $max_upload*1024;
		}
		
		if( $this->ipsclass->input['opt_maxfile'] > $max_upload || $this->ipsclass->input['opt_maxss'] > $max_upload )
		{
			$this->ipsclass->main_msg = "The maximum upload file size you can specify is {$max_upload}.  If you require larger file uploads, please contact your host to have this limit raised in your php.ini configuration file.";
			$this->main_form( $type );
			return;
		}
		
		# File Options, saved to coptions
		$options = array(	'opt_mimemask'			=> $this->ipsclass->input['opt_mimemask'],
							'opt_bbcode'			=> intval($this->ipsclass->input['opt_bbcode']),
							'opt_html'				=> intval($this->ipsclass->input['opt_html']),
							'opt_catss'				=> intval($this->ipsclass->input['opt_catss']),
							'opt_filess'			=> intval($this->ipsclass->input['opt_filess']),
							'opt_comments'			=> intval($this->ipsclass->input['opt_comments']),
							'opt_allowss'			=> intval($this->ipsclass->input['opt_allowss']),
							'opt_reqss'				=> intval($this->ipsclass->input['opt_reqss']),
							'opt_sortorder'			=> $this->ipsclass->input['opt_sortorder'],
							'opt_sortby'			=> $this->ipsclass->input['opt_sortby'],
							'opt_maxfile'			=> intval($this->ipsclass->input['opt_maxfile']),
							'opt_maxss'				=> intval($this->ipsclass->input['opt_maxss']),
							'opt_thumb_x'			=> intval($this->ipsclass->input['opt_thumb_x']),
							'opt_thumb_y'			=> intval($this->ipsclass->input['opt_thumb_y']),
							'opt_topice'			=> intval($this->ipsclass->input['opt_topice']),
							'opt_topicf'			=> intval($this->ipsclass->input['opt_topicf']),
							'opt_topicp'			=> $this->ipsclass->input['opt_topicp'],
							'opt_topics'			=> $this->ipsclass->input['opt_topics'],
							'opt_topicd'			=> intval($this->ipsclass->input['opt_topicd']),
							'opt_topicss'			=> intval($this->ipsclass->input['opt_topicss']),
							'opt_disfiles'			=> intval($this->ipsclass->input['opt_disfiles']),
							'opt_noperm_view'		=> $this->ipsclass->my_nl2br( $this->ipsclass->txt_stripslashes($_POST['opt_noperm_view']) ),
							'opt_noperm_add'		=> $this->ipsclass->my_nl2br( $this->ipsclass->txt_stripslashes($_POST['opt_noperm_add']) ),
							'opt_noperm_dl'			=> $this->ipsclass->my_nl2br( $this->ipsclass->txt_stripslashes($_POST['opt_noperm_dl']) ),
						);
						
		$coptions	 = $this->ipsclass->txt_safeslashes( serialize( $options ) );
						
		$perms 		 = $this->func->compile_cat_perms();
		$cperms 	 = $this->ipsclass->txt_safeslashes( serialize( array(
													   				'show'  	=> $perms['SHOW'],
													   				'view'  	=> $perms['VIEW'],
													   				'add'   	=> $perms['ADD'],
													   				'download' 	=> $perms['DOWNLOAD'],
													   				'rate'   	=> $perms['RATE'],
													   				'comment'   => $perms['COMMENT'],
													   				'auto'   	=> $perms['AUTO']
									 )		  )     );

		$ccfields = "";
		if( is_array( $this->ipsclass->input['ccfields'] ) )
		{
			$ccfields = implode(",", $this->ipsclass->input['ccfields'] );
		}
		
		
		//-----------------------------------------
		// Save array
		//-----------------------------------------
		
		$save = array (  'cname' 				=> $this->ipsclass->input['cname'],
						 'cdesc'  				=> $this->ipsclass->my_nl2br( $this->ipsclass->txt_stripslashes($_POST['cdesc']) ),
						 'cdisclaimer'			=> $this->ipsclass->my_nl2br( $this->ipsclass->txt_stripslashes($_POST['cdisclaimer']) ),
						 'copen'				=> intval($this->ipsclass->input['copen']),
						 'cparent'              => intval($this->ipsclass->input['cparent']),
						 'cperms'               => $cperms,
						 'coptions'             => $coptions,
						 'ccfields'				=> $ccfields,
					 );
						 
		//-----------------------------------------
		// ADD
		//-----------------------------------------
		
		if ( $type == 'new' )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'MAX(cid) as maxcid', 'from' => 'downloads_categories' ) );
			$this->ipsclass->DB->simple_exec();
			 
			$row = $this->ipsclass->DB->fetch_row();
			 
			if ( $row['maxcid'] < 1 )
			{
				$row['maxcid'] = 0;
			}
			 
			$row['maxcid']++;
			 
			$save['cposition']		= $row['maxcid'];
			$save['cfileinfo']      = "";
			
			$this->ipsclass->DB->do_insert( 'downloads_categories', $save );
			
			$this->ipsclass->main_msg = "Category '{$save['cname']}' Created";
			
			$this->ipsclass->admin->save_log("Downloads Category '{$save['cname']}' created");
		}
		else
		{
			$this->ipsclass->DB->do_update( 'downloads_categories', $save, "cid={$this->ipsclass->input['c']}"  );
			
			$this->ipsclass->main_msg = "Category '{$save['cname']}' Edited";
			
			$this->ipsclass->admin->save_log("Downloads Category '{$save['cname']}' edited");
		}
		
		$this->func->rebuild_cat_cache();
		$temp = $this->ipsclass->input['c'];		
		$this->ipsclass->input['c'] = "";
		$this->func->init = 0;
		$this->func->full_init();
		$this->func->rebuild_stats_cache();
		
		$this->ipsclass->input['c'] = $temp ? $temp : $save['cid'];
		$this->main_screen();
	}
	
	
	function main_form( $type='edit' )
	{
		$nav = $this->func->get_nav( $this->ipsclass->input['c'], '&'.$this->ipsclass->form_code.'&c=', 1);
		
		if ( is_array($nav) and count($nav) > 1 )
		{
			array_shift($nav);
			
			$this->ipsclass->html .= "<div class='navstrip'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}'>Categories</a> &gt; ".implode( " &gt; ", $nav )." &gt; ".ucwords($type)." Form</div><br />";
		}
		
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$form        	= array();
		$forum       	= array();
		$cat_id    		= intval($this->ipsclass->input['c']);
		$parentid    	= intval($this->ipsclass->input['p']) ? intval($this->ipsclass->input['p']) : 0;
		$cname      	= $this->ipsclass->parse_clean_value(urldecode($_REQUEST['cname']));
		$perm_matrix 	= "";
		
		$dd_order    = array( 
							 0 => array( 'submitted'		, 'File Submission Date' ),
							 1 => array( 'updated'			, 'File Last Update' ),
							 2 => array( 'name'    			, 'File Title' ),
							 3 => array( 'downloads'    	, 'Downloads' ),
							 4 => array( 'views'    		, 'File Views' ),
							 5 => array( 'rating'			, 'File Rating' ),
							);
																							
		$dd_by       = array( 
							 0 => array( 'Z-A', 'Descending (Z - A, 10 - 0)' ),
							 1 => array( 'A-Z', 'Ascending (A - Z, 0 - 10)'  )
							);
																									 
		//-----------------------------------------
		// Set up title, desc
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "This section allows you to manage a Downloads category.  You can add/edit the category from here, as well as
												control the custom fields used by this category, and the category permissions.";
		
		//-----------------------------------------
		// ini_get max upload size?
		//-----------------------------------------
				
		$max_upload = @ini_get('upload_max_filesize') ? @ini_get('upload_max_filesize') : 0;
		$max_upload_display = $max_upload != '0' ? $max_upload : "<i>Cannot read php.ini</i>";
		
		if( substr( $max_upload, -1, 1 ) == "M" )
		{
			$max_upload = substr( $max_upload, 0, -1 );
			$max_upload = $max_upload*1024;
		}
		
		//-----------------------------------------
		// EDIT
		//-----------------------------------------
		
		if ( $type == 'edit' )
		{
			//-----------------------------------------
			// Check
			//-----------------------------------------
			
			if ( ! $cat_id )
			{
				$this->ipsclass->admin->error("You didn't choose a category to edit!");
			}
			
			//-----------------------------------------
			// Do not show category in drop down menu
			//-----------------------------------------
			
			$this->func->exclude_from_list = $cat_id;
			
			//-----------------------------------------
			// Get this forum
			//-----------------------------------------
			
			$cat = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_categories', 'where' => 'cid='.$cat_id ) );
			
			//-----------------------------------------
			// Check
			//-----------------------------------------
			
			if ($cat['cid'] == "")
			{
				$this->ipsclass->admin->error("Could not retrieve the category data based on ID {$cat_id}");
			}
			
			$cat 		= array_merge( $cat, unserialize( $this->ipsclass->txt_stripslashes( $cat['coptions'] ) ) );
			$catperms 	= unserialize( $this->ipsclass->txt_stripslashes( $cat['cperms'] ) );
			
			$cat['ccfields'] = explode( ",", $cat['ccfields'] );
			
			//-----------------------------------------
			// Set up code buttons
			//-----------------------------------------
			
			$title  = "Editing Category: {$cat['cname']}";
			$button = "Edit Category";
			$code   = "doedit";
			
			//-----------------------------------------
			// Basic title
			//-----------------------------------------
			
			$basic_title = "Basic Settings for {$cat['cname']}";
		}
		
		//-----------------------------------------
		// NEW
		//-----------------------------------------
		
		else
		{
			$cat_id = 0;
			
			$cat = array(
							'sub_can_post' 	=> 1,
							'cname'         => $cname ? $cname : 'New Category',
							'cparent'    	=> $parentid,
							'opt_html'      => 0,
							'opt_bbcode'	=> 1,
							'opt_maxfile'	=> $max_upload,
							'opt_maxss'		=> $max_upload,
							'sort_key'     	=> 'updated',
							'sort_order'   	=> 'Z-A',
							'ccfields'		=> $_POST['ccfields'],
							'copen'			=> 1,
							'opt_disfiles'	=> 1,
							'opt_thumb_x'	=> 100,
							'opt_thumb_y'	=> 100,
						  );
						  
			$catperms 	 = array();
						  
			$title       = "Add a category";
			$button      = "Add Category";
			$code        = "donew";
			$basic_title = 'Basic Settings';
		}
		
		//-----------------------------------------
		// Build forumlist for topic submission
		//-----------------------------------------
		require_once( ROOT_PATH.'sources/lib/admin_forum_functions.php' );
		$this->forumfunc = new admin_forum_functions();
		$this->forumfunc->ipsclass =& $this->ipsclass;
		
		require_once( ROOT_PATH.'sources/classes/class_forums.php' );
		$this->ipsclass->forums = new forum_functions();
		$this->ipsclass->forums->ipsclass =& $this->ipsclass;
		$this->ipsclass->forums->forums_init();
		$forumlist = $this->forumfunc->ad_forums_forum_list(1);
		
		//-----------------------------------------
		// Build category list for parent cat
		//-----------------------------------------
		
		$catlist = $this->func->cat_jump_list();
		
		//-----------------------------------------
		// Build Mime-type masks
		//-----------------------------------------		

		$masks = array();
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mimemask' ) );
		$this->ipsclass->DB->simple_exec();
		while( $getmasks = $this->ipsclass->DB->fetch_row() )
		{
			$masks[] = array( $getmasks['mime_maskid'], $getmasks['mime_masktitle'] );
		}
		
		
		//-----------------------------------------
		// Build per-cat Custom Fields
		//-----------------------------------------

		$cfields = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'order' => 'cf_position' ) );
		$this->ipsclass->DB->simple_exec();
		while( $fields = $this->ipsclass->DB->fetch_row() )
		{
			$cfields[] = array( $fields['cf_id'], $fields['cf_title'] );
		}
		
		
		//-----------------------------------------
		// Page title...
		//-----------------------------------------
		
		$this->ipsclass->admin->page_title = $title;
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , $code     ),
																			 2 => array( 'act'    , 'downloads'  ),
																			 3 => array( 'req'	  , 'categories'	),
																			 4 => array( 'c'     , $this->ipsclass->input['c'] ),
																			 5 => array( 'section', $this->ipsclass->section_code ),
																	),  "adminform"    );
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $basic_title );

		//-----------------------------------------
		// Generate form items
		//-----------------------------------------
		
		# Main settings
		$form['cname']     		= $this->ipsclass->adskin->form_input(   "cname"        , $_POST['cname'] ? $this->ipsclass->parse_clean_value( $_POST['cname'] ) : $cat['cname'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Category Name</b>", $form['cname']  )      );		
		
		$form['cdesc']  		= $this->ipsclass->adskin->form_textarea("cdesc" 		, $this->ipsclass->my_br2nl( $this->ipsclass->txt_stripslashes( $_POST['cdesc'] ? $_POST['cdesc'] : $cat['cdesc'] ) ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Category Description</b><div style='color:gray;'>HTML allowed</div>", $form['cdesc']  )      );		
		
		$form['cparent']    	= $this->ipsclass->adskin->form_dropdown("cparent"   	, $catlist, $_POST['cparent'] ? $_POST['cparent']    : $cat['cparent'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Parent Category</b><div style='color:gray;'>Set to 'Root Category' to make this a root category.  Subcategories are optional.  If you enable 'add' permissions below, you can submit to this category, regardless of whether it has subcategories or not.</div>", $form['cparent']  )      );
		
		$form['copen']       	= $this->ipsclass->adskin->form_yes_no(  "copen"      	, $_POST['copen']    ? $_POST['copen']       : $cat['copen'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Category Open?</b><div style='color:gray;'>Set category open? Admins can see all categories regardless of this setting.</div>", $form['copen']  )      );		
		
		$form['opt_disfiles'] 	= $this->ipsclass->adskin->form_yes_no(  "opt_disfiles"	, $_POST['opt_disfiles']    ? $_POST['opt_disfiles']       : $cat['opt_disfiles'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Show File Listing?</b><div style='color:gray;'>If you do not allow submissions to this category (i.e. you have subcategories and don't allow users to submit to this category) you can set this to 'no' to not show an empty file listing when users have 'Show' permissions in this category.</div>", $form['opt_disfiles']  )      );		
		
		$form['cdisclaimer']	= $this->ipsclass->adskin->form_textarea("cdisclaimer" 	, $this->ipsclass->my_br2nl( $this->ipsclass->txt_stripslashes( $_POST['cdisclaimer'] ? $_POST['cdisclaimer'] : $cat['cdisclaimer'] ) ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Download Disclaimer</b><div style='color:gray;'>You can enter a disclaimer that users must agree to before being able to download files.  Leave blank to disable.</div>", $form['cdisclaimer']  )      );		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Category Options" );
		
		# Per-Cat Options
		$form['opt_mimemask']  	= $this->ipsclass->adskin->form_dropdown("opt_mimemask" , $masks, $_POST['opt_mimemask'] ? $_POST['opt_mimemask']    : $cat['opt_mimemask'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Mime-Type mask</b><div style='color:gray;'>This is the mime-type mask that you wish to use</div>", $form['opt_mimemask']  )      );
		
		$form['ccfields']  		= $this->ipsclass->adskin->form_multiselect("ccfields[]" , $cfields, $cat['ccfields'], '6'  );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Custom Fields</b><div style='color:gray;'>If you would like to use any of your configured custom fields for this category, please select them.<br /><br /> Hold CTRL + C to select more than one.</div>", $form['ccfields']  )      );
				
		$form['opt_bbcode']  	= $this->ipsclass->adskin->form_yes_no(  "opt_bbcode" 	, $_POST['opt_bbcode']  	? $_POST['opt_bbcode']  	: $cat['opt_bbcode']  );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow BBCode</b>", $form['opt_bbcode']  )      );
		
		$form['opt_html']   	= $this->ipsclass->adskin->form_yes_no(  "opt_html"  	, $_POST['opt_html']   		? $_POST['opt_html']   		: $cat['opt_html']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow HTML</b><div style='color:gray;'>Use at your own risk! Do not enable if you don't trust your members</div>", $form['opt_html']  )      );
		
		$form['opt_catss'] 		= $this->ipsclass->adskin->form_yes_no(  "opt_catss"  	, $_POST['opt_catss']  		? $_POST['opt_catss']   	: $cat['opt_catss']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Display screenshot thumbnails when viewing this category?</b>", $form['opt_catss']  )      );
		
		$form['opt_filess'] 	= $this->ipsclass->adskin->form_yes_no(  "opt_filess"  	, $_POST['opt_filess']  	? $_POST['opt_filess']  	: $cat['opt_filess']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Display screenshot thumbnails when viewing files in this category?</b>", $form['opt_filess']  )      );
		
		$form['opt_comments']	= $this->ipsclass->adskin->form_yes_no(  "opt_comments"	, $_POST['opt_comments']  	? $_POST['opt_comments']  	: $cat['opt_comments']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Use built-in commenting system?</b><div style='color:gray;'>It is not recommended to enable this if you plan to use the automatic topic generation abilities, configurable below</div>", $form['opt_comments']  )      );
		
		$form['opt_allowss']	= $this->ipsclass->adskin->form_yes_no(  "opt_allowss"	, $_POST['opt_allowss']  	? $_POST['opt_allowss']  	: $cat['opt_allowss']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Allow screenshots in this category?</b>", $form['opt_allowss']  )      );
		
		$form['opt_reqss']		= $this->ipsclass->adskin->form_yes_no(  "opt_reqss"	, $_POST['opt_reqss']  		? $_POST['opt_reqss']  		: $cat['opt_reqss']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Require screenshots in this category?</b><div style='color:gray;'>This setting has no effect if you do not allow screenshots in this category</div>", $form['opt_reqss']  )      );
		
		$form['opt_sortorder']	= $this->ipsclass->adskin->form_dropdown("opt_sortorder"   	, $dd_order, $_POST['opt_sortorder'] ? $_POST['opt_sortorder']    : $cat['opt_sortorder'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Default sort order</b>", $form['opt_sortorder']  )      );
		
		$form['opt_sortby']		= $this->ipsclass->adskin->form_dropdown("opt_sortby"   	, $dd_by, $_POST['opt_sortby'] ? $_POST['opt_sortby']    : $cat['opt_sortby'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Default sort-by ordering</b>", $form['opt_sortby']  )      );
		
		$form['opt_maxfile']	= $this->ipsclass->adskin->form_input(   "opt_maxfile" 	, $_POST['opt_maxfile'] 	? intval($_POST['opt_maxfile'])  : $cat['opt_maxfile']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum file sizes allowed (in kB)</b><div style='color:gray;'>Your host has limited the maximum file upload size to {$max_upload_display}.  You cannot set this value any higher than the maximum your host has set.", $form['opt_maxfile']  )      );
		
		$form['opt_maxss']		= $this->ipsclass->adskin->form_input(   "opt_maxss" 	, $_POST['opt_maxss'] 		? intval($_POST['opt_maxss'])  	 : $cat['opt_maxss']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum screenshot file sizes allowed (in kB)</b><div style='color:gray;'>Your host has limited the maximum file upload size to {$max_upload_display}.  You cannot set this value any higher than the maximum your host has set.", $form['opt_maxss']."<br>".
																				"Thumbnail Width: ".$this->ipsclass->adskin->form_simple_input(   "opt_thumb_x" 	, $_POST['opt_thumb_x'] 		? intval($_POST['opt_thumb_x'])  	 : $cat['opt_thumb_x']   ).
																				" x Thumbnail Height: ".$this->ipsclass->adskin->form_simple_input(   "opt_thumb_y" 	, $_POST['opt_thumb_y'] 		? intval($_POST['opt_thumb_y'])  	 : $cat['opt_thumb_y']   )  )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Topic Options" );		
		
		# topice = enabled/disabled topic posting
		# topicf = forum id to post in
		# topicp = topic prefix
		# topics = topic suffix
		# topicd = auto deletion of topic when file is removed
		# topicss = include screenshot when available
		$form['opt_topice'] 	= $this->ipsclass->adskin->form_yes_no(  "opt_topice"  	, $_POST['opt_topice']  	? $_POST['opt_topice']  	: $cat['opt_topice']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Enable topic generation upon file submission?</b><div style='color:gray;'>Enabling this setting will allow your Download Manager to post a new topic based on the below configuration when a file is submitted (and approved) in this category.</div>", $form['opt_topice']  )      );
		
		$form['opt_topicf'] 	= $this->ipsclass->adskin->form_dropdown("opt_topicf"   , $forumlist, $_POST['opt_topicf'] ? $_POST['opt_topicf']    : $cat['opt_topicf'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>What forum should the topic be posted in?</b><div style='color:gray;'>Warning: no further permission/forum checks are done other than to verify the forum exists.  Please ensure you select the correct forum!</div>", $form['opt_topicf']  )      );
		
		$form['opt_topicp'] 	= $this->ipsclass->adskin->form_input(   "opt_topicp"  	, $this->ipsclass->parse_clean_value( $_POST['opt_topicp'] ) ? $this->ipsclass->parse_clean_value( $_POST['opt_topicp'] ) 	: $cat['opt_topicp']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Topic Prefix</b><div style='color:gray;'>You can enter a topic prefix using this setting.<br />Hint: {catname} will automatically be replaced with the category name</div>", $form['opt_topicp']  )      );
		
		$form['opt_topics'] 	= $this->ipsclass->adskin->form_input(   "opt_topics"  	, $this->ipsclass->parse_clean_value( $_POST['opt_topics'] ) ? $this->ipsclass->parse_clean_value( $_POST['opt_topics'] ) 	: $cat['opt_topics']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Topic Suffix</b><div style='color:gray;'>You can enter a topic suffix using this setting.<br />Hint: {catname} will automatically be replaced with the category name</div>", $form['opt_topics']  )      );
		
		$form['opt_topicd'] 	= $this->ipsclass->adskin->form_yes_no(  "opt_topicd"  	, $_POST['opt_topicd']  	? $_POST['opt_topicd']  	: $cat['opt_topicd']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Automatically delete topics when files are deleted?</b>", $form['opt_topicd']  )      );
		
		$form['opt_topicss'] 	= $this->ipsclass->adskin->form_yes_no(  "opt_topicss"  , $_POST['opt_topicss']  	? $_POST['opt_topicss']  	: $cat['opt_topicss']   );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Include screenshot with topic?</b><div style='color:gray;'>If set to yes, the screenshot will display in the posted topic.  You must have HTML enabled in the forum when editing the topics or the screenshot links will be broken</div>", $form['opt_topicss']  )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Custom Permission Denied Messages" );
		
		$form['opt_noperm_view']	= $this->ipsclass->adskin->form_textarea("opt_noperm_view" 	, $this->ipsclass->my_br2nl( $this->ipsclass->txt_stripslashes( $_POST['opt_noperm_view'] ? $_POST['opt_noperm_view'] : $cat['opt_noperm_view'] ) ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Cannot View Message</b><div style='color:gray;'>If you would like to show a custom permission denied message for users who do not have 'view' permissions in this category enter it here.  HTML enabled.  Leave blank to disable.</div>", $form['opt_noperm_view']  )      );		

		$form['opt_noperm_add']		= $this->ipsclass->adskin->form_textarea("opt_noperm_add" 	, $this->ipsclass->my_br2nl( $this->ipsclass->txt_stripslashes( $_POST['opt_noperm_add'] ? $_POST['opt_noperm_add'] : $cat['opt_noperm_add'] ) ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Cannot Add Message</b><div style='color:gray;'>If you would like to show a custom permission denied message for users who do not have 'add' permissions in this category enter it here.  HTML enabled.  Leave blank to disable.</div>", $form['opt_noperm_add']  )      );		

		$form['opt_noperm_dl']		= $this->ipsclass->adskin->form_textarea("opt_noperm_dl" 	, $this->ipsclass->my_br2nl( $this->ipsclass->txt_stripslashes( $_POST['opt_noperm_dl'] ? $_POST['opt_noperm_dl'] : $cat['opt_noperm_dl'] ) ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Cannot Download Message</b><div style='color:gray;'>If you would like to show a custom permission denied message for users who do not have 'download' permissions in this category enter it here.  HTML enabled.  Leave blank to disable.</div>", $form['opt_noperm_dl']  )      );		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		//-----------------------------------------
		// Show permission matrix
		//-----------------------------------------
		
		$perm_matrix = $this->build_perms_html( $catperms['show'], $catperms['view'], $catperms['add'],
												 $catperms['download'], $catperms['comment'], $catperms['rate'], $catperms['auto']  );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_standalone_row( $perm_matrix, 'left', '' );

		$this->ipsclass->html .= "<br />";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form_standalone( $button );
		
		//-----------------------------------------
		// Nav and print
		//-----------------------------------------
		
		$this->ipsclass->admin->nav[] = array( '', ucwords($type).' Category' );
		
		$this->ipsclass->admin->output();
	}
		
	
	
	function build_perms_html( $show='*', $view='*', $add='*', $download='*', $comment='*', $rate='*', $auto='*', $title="Category Permissions" )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$global  = array();
		$perm    = array();
		$data    = array();
		$checked = "";
		$check   = " checked='checked'";
		$content = "";
		
		//-----------------------------------------
		// GLOBALS
		//-----------------------------------------
		
		# SHOW CATEGORY
		$checked             	= ($show == '*') ? $check : "";
		$global['html_show'] 	= "<input type='checkbox' onclick='check_all(\"SHOW\")' name='SHOW_ALL' id='SHOW_ALL' value='1' {$checked}>\n";
		
		# VIEW FILE INFO
		$checked             	= ($view == '*') ? $check : "";
		$global['html_view'] 	= "<input type='checkbox' onclick='check_all(\"VIEW\")' name='VIEW_ALL' id='VIEW_ALL' value='1' {$checked}>\n";
		
		# SUBMIT FILES
		$checked             	= ($add == '*') ? $check : "";
		$global['html_add']  	= "<input type='checkbox' onclick='check_all(\"ADD\")' name='ADD_ALL' id='ADD_ALL' value='1' {$checked}>\n";
		
		# DOWNLOAD FILES
		$checked             	= ($download == '*') ? $check : "";
		$global['html_download'] = "<input type='checkbox' onclick='check_all(\"DOWNLOAD\")' name='DOWNLOAD_ALL' id='DOWNLOAD_ALL' value='1' {$checked}>\n";
		
		# RATE FILES
		$checked             	= ($rate == '*') ? $check : "";
		$global['html_rate'] 	= "<input type='checkbox' onclick='check_all(\"RATE\")' name='RATE_ALL' id='RATE_ALL' value='1' {$checked}>\n";

		# COMMENT ON FILES
		$checked             	= ($comment == '*') ? $check : "";
		$global['html_comment'] = "<input type='checkbox' onclick='check_all(\"COMMENT\")' name='COMMENT_ALL' id='COMMENT_ALL' value='1' {$checked}>\n";
		
		# AUTO-APPROVE SUBMISSIONS
		$checked             	= ($auto == '*') ? $check : "";
		$global['html_auto'] 	= "<input type='checkbox' onclick='check_all(\"AUTO\")' name='AUTO_ALL' id='AUTO_ALL' value='1' {$checked}>\n";

		//-----------------------------------------
		// Per mask settings
		// Let's stick to forum permission masks
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'forum_perms', 'order' => "perm_name ASC" ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $data = $this->ipsclass->DB->fetch_row() )
		{
			# SHOW CATEOGRY
			$checked           	= ($show == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $show ) ) ? $check : "";
			$perm['html_show'] 	= "<input type='checkbox' name='SHOW_{$data['perm_id']}' id='SHOW_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('SHOW', '{$data['perm_id']}')\">";
		
			# VIEW FILE INFO
			$checked           	= ($view == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $view ) ) ? $check : "";
			$perm['html_view'] 	= "<input type='checkbox' name='VIEW_{$data['perm_id']}' id='VIEW_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('VIEW', '{$data['perm_id']}')\">";
			
			# SUBMIT FILES
			$checked            = ($add == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $add ) ) ? $check : "";
			$perm['html_add'] 	= "<input type='checkbox' name='ADD_{$data['perm_id']}' id='ADD_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('ADD', '{$data['perm_id']}')\">";
			
			# DOWNLOAD FILES
			$checked            = ($download == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $download ) ) ? $check : "";
			$perm['html_download'] = "<input type='checkbox' name='DOWNLOAD_{$data['perm_id']}' id='DOWNLOAD_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('DOWNLOAD', '{$data['perm_id']}')\">";
			
			# RATE FILES
			$checked            = ($rate == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $rate ) ) ? $check : "";
			$perm['html_rate'] 	= "<input type='checkbox' name='RATE_{$data['perm_id']}' id='RATE_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('RATE', '{$data['perm_id']}')\">";

			# COMMENT ON FILES
			$checked            = ($comment == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $comment ) ) ? $check : "";
			$perm['html_comment'] = "<input type='checkbox' name='COMMENT_{$data['perm_id']}' id='COMMENT_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('COMMENT', '{$data['perm_id']}')\">";
			
			# AUTO-APPROVE SUBMISSIONS
			$checked            = ($auto == '*' OR preg_match( "/(^|,)".$data['perm_id']."(,|$)/", $auto ) ) ? $check : "";
			$perm['html_auto'] 	= "<input type='checkbox' name='AUTO_{$data['perm_id']}' id='AUTO_{$data['perm_id']}' value='1' {$checked} onclick=\"obj_checked('AUTO', '{$data['perm_id']}')\">";

			$content .= "
				<tr>
  				<td colspan='8' class='tablerow1'>
  				<fieldset>
 				 <legend><strong>{$data['perm_name']}</strong></legend>
  				<table cellpadding='4' cellspacing='0' border='0' class='tablerow1' width='100%'>
 				 <tr>
   				  <td class='tablerow2' width='13%'><input type='button' id='button' value='+' onclick='checkrow({$data['perm_id']},1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkrow({$data['perm_id']},0)' /></td>
   				  <td class='tablerow1' width='12%' style='background-color:#ecd5d8' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('SHOW_{$data['perm_id']}')\"><center><div class='red-perm'>Show Files</div> {$perm['html_show']}</center></td>
   				  <td class='tablerow1' width='12%' style='background-color:#dbe2de' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('VIEW_{$data['perm_id']}')\"><center><div class='green-perm'>View Files</div> {$perm['html_view']}</center></td>
      			  <td class='tablerow1' width='12%' style='background-color:#dbe6ea' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('ADD_{$data['perm_id']}')\"><center><div class='yellow-perm'>Add Files</div> {$perm['html_add']}</center></td>
   				  <td class='tablerow1' width='12%' style='background-color:#d2d5f2' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('DOWNLOAD_{$data['perm_id']}')\"><center><div class='blue-perm'>Download</div> {$perm['html_download']}</center></td>
   				  <td class='tablerow1' width='12%' style='background-color:#ece6d8' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('RATE_{$data['perm_id']}')\"><center><div class='orange-perm'>Rate Files</div> {$perm['html_rate']}</center></td>
   				  <td class='tablerow1' width='12%' style='background-color:#dfdee9' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('COMMENT_{$data['perm_id']}')\"><center><div class='purple-perm'>Comment</div> {$perm['html_comment']}</center></td>
   				  <td class='tablerow1' width='12%' style='background-color:#ecd5d8' id='clickable_{$data['perm_id']}' onclick=\"toggle_box('AUTO_{$data['perm_id']}')\"><center><div class='red-perm'>Auto-Post</div> {$perm['html_auto']}</center></td>
   				 </tr>
   				</table>
   				</fieldset>
   				</td>
   				</tr>";
		}
		
		return <<<EOF
<script type='text/javascript'>

var formobj = document.getElementById('adminform');

//----------------------------------
// Check all column
//----------------------------------

function check_all( permtype )
{
	var checkboxes = formobj.getElementsByTagName('input');

	for ( var i = 0 ; i <= checkboxes.length ; i++ )
	{
		var e = checkboxes[i];
		
		if ( e && (e.id != 'SHOW_ALL') && (e.id != 'VIEW_ALL') && (e.id != 'ADD_ALL') && (e.id != 'DOWNLOAD_ALL') && (e.id != 'RATE_ALL') && (e.id != 'COMMENT_ALL') && (e.id != 'AUTO_ALL') && (e.type == 'checkbox') && (! e.disabled) )
		{
			var s = e.id;
			var a = s.replace( /^(.+?)_.+?$/, "$1" );
			
			if (a == permtype)
			{
				e.checked = true;
			}
		}
	}
	
	if ( document.getElementById( permtype + '_ALL' ).checked )
	{
		document.getElementById( permtype + '_ALL' ).checked = false;
	}
	else
	{
		document.getElementById( permtype + '_ALL' ).checked = true;
	}
	
	return false;
}

//----------------------------------
// Object has been checked
//----------------------------------

function obj_checked( permtype, pid )
{
	var totalboxes = 0;
	var total_on   = 0;
	
	if ( pid )
	{
		document.getElementById( permtype+'_'+pid ).checked = document.getElementById( permtype+'_'+pid ).checked ? false : true;
	}
	
	var checkboxes = formobj.getElementsByTagName('input');

	for ( var i = 0 ; i <= checkboxes.length ; i++ )
	{
		var e = checkboxes[i];
		
		if ( e && (e.id != 'SHOW_ALL') && (e.id != 'VIEW_ALL') && (e.id != 'ADD_ALL') && (e.id != 'DOWNLOAD_ALL') && (e.id != 'RATE_ALL') && (e.id != 'COMMENT_ALL') && (e.id != 'AUTO_ALL') && (e.type == 'checkbox') && (! e.disabled) )
		{
			var s = e.id;
			var a = s.replace( /^(.+?)_.+?$/, "$1" );
			
			if ( a == permtype )
			{
				totalboxes++;
				
				if ( e.checked )
				{
					total_on++;
				}
			}
		}
	}
	
	if ( totalboxes == total_on )
	{
		document.getElementById( permtype + '_ALL' ).checked = true;
	}
	else
	{
		document.getElementById( permtype + '_ALL' ).checked = false;
	}
	
	return false;
}

//----------------------------------
// Check column
//----------------------------------

function checkcol( permtype ,status)
{
	var checkboxes = formobj.getElementsByTagName('input');

	for ( var i = 0 ; i <= checkboxes.length ; i++ )
	{
		var e = checkboxes[i];
		
		if ( e && (e.id != 'SHOW_ALL') && (e.id != 'VIEW_ALL') && (e.id != 'ADD_ALL') && (e.id != 'DOWNLOAD_ALL') && (e.id != 'RATE_ALL') && (e.id != 'COMMENT_ALL') && (e.id != 'AUTO_ALL') && (e.type == 'checkbox') && (! e.disabled) )
		{
			var s = e.id;
			var a = s.replace( /^(.+?)_.+?$/, "$1" );
			
			if ( a == permtype )
			{
				if ( status == 1 )
				{
					e.checked = true;
					document.getElementById( permtype + '_ALL' ).checked = true;
				}
				else
				{
					e.checked = false;
					document.getElementById( permtype + '_ALL' ).checked = false;
				}
			}
		}
	}
	
	return false;
}

//----------------------------------
// Remote click box
//----------------------------------

function toggle_box( compiled_permid )
{
	if ( document.getElementById( compiled_permid ).checked )
	{
		document.getElementById( compiled_permid ).checked = false;
	}
	else
	{
		document.getElementById( compiled_permid ).checked = true;
	}
	
	obj_checked( compiled_permid.replace( /^(.+?)_.+?$/, "$1" ) , '');
	
	return false;
}

//----------------------------------
// INIT
//----------------------------------

function init_perms()
{
	var tds = formobj.getElementsByTagName('td');

	for ( var i = 0 ; i <= tds.length ; i++ )
	{
		var thisobj   = tds[i];
		
		if ( thisobj && thisobj.id )
		{
			var name      = thisobj.id;
			var firstpart = name.replace( /^(.+?)_.+?$/, "$1" );
			
			if ( firstpart == 'clickable' )
			{
				try
				{
					document.getElementById( tds[i].id ).style.cursor = "pointer";
				}
				catch(e)
				{
					document.getElementById( tds[i].id ).style.cursor = "hand";
				}
			}
		}
	}
}

//----------------------------------
// Check row
//----------------------------------

function checkrow( permid, status )
{
	document.getElementById( "SHOW"   	+ '_' + permid ).checked = status ? true : false;
	document.getElementById( "VIEW"  	+ '_' + permid ).checked = status ? true : false;
	document.getElementById( "ADD"  	+ '_' + permid ).checked = status ? true : false;
	document.getElementById( "DOWNLOAD" + '_' + permid ).checked = status ? true : false;
	document.getElementById( "RATE"   	+ '_' + permid ).checked = status ? true : false;
	document.getElementById( "COMMENT" 	+ '_' + permid ).checked = status ? true : false;
	document.getElementById( "AUTO"   	+ '_' + permid ).checked = status ? true : false;
	
	obj_checked("SHOW");
	obj_checked("VIEW");
	obj_checked("ADD");
	obj_checked("DOWNLOAD");
	obj_checked("RATE");
	obj_checked("COMMENT");
	obj_checked("AUTO");		
	
	return false;
}
</script>	
 <div class='tableheaderalt' id='perm-header'>{$title}</div>
 <table cellpadding='4' cellspacing='0' border='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='13%'>&nbsp;</td>
  <td class='tablesubheader' width='12%' align='center'>Show Files</td>
  <td class='tablesubheader' width='12%' align='center'>View Files</td>
  <td class='tablesubheader' width='12%' align='center'>Add Files</td>
  <td class='tablesubheader' width='12%' align='center'>Download</td>
  <td class='tablesubheader' width='12%' align='center'>Rate Files</td>
  <td class='tablesubheader' width='12%' align='center'>Comment</td>
      <td class='tablesubheader' width='12%' align='center'>Auto-Post</td>
 </tr>
 <tr>
  <td colspan='8' class='tablerow1'>
  <fieldset>
  <legend><strong>Global Permissions</strong> (All current and future permission masks)</legend>
  <table cellpadding='4' cellspacing='0' border='0' class='tablerow1' width='100%'>
  <tr>
   <td class='tablerow2' width='13%'>&nbsp;</td>
   <td class='tablerow1' width='12%' style='background-color:#ecd5d8' onclick='check_all("SHOW")'><center><div class='red-perm'>Show Files</div> {$global['html_show']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#dbe2de' onclick='check_all("VIEW")'><center><div class='green-perm'>View Files</div> {$global['html_view']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#dbe6ea' onclick='check_all("ADD")'><center><div class='yellow-perm'>Add Files</div> {$global['html_add']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#d2d5f2' onclick='check_all("DOWNLOAD")'><center><div class='blue-perm'>Download</div> {$global['html_download']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#ece6d8' onclick='check_all("RATE")'><center><div class='orange-perm'>Rate Files</div> {$global['html_rate']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#dfdee9' onclick='check_all("COMMENT")'><center><div class='purple-perm'>Comment</div> {$global['html_comment']}</center></td>
   <td class='tablerow1' width='12%' style='background-color:#ecd5d8' onclick='check_all("AUTO")'><center><div class='red-perm'>Auto-Post</div> {$global['html_auto']}</center></td>
   </tr>
  </table>
  </fieldset>
  </td>
 </tr>
 {$content}
 <tr>
  <td class='tablerow2'>&nbsp;</td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("SHOW",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("SHOW",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("VIEW",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("VIEW",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("ADD",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("ADD",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("DOWNLOAD",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("DOWNLOAD",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("RATE",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("RATE",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("COMMENT",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("COMMENT",0)' /></center></td>
  <td class='tablerow1'><center><input type='button' id='button' value='+' onclick='checkcol("AUTO",1)' />&nbsp;<input type='button' id='button' value='-' onclick='checkcol("AUTO",0)' /></center></td>    
</tr>		
</table>

<script type='text/javascript'>
 init_perms();
</script>

EOF;
	}
	
	
	function show_mod_form( $type='add' )
	{
		$this->ipsclass->admin->page_title   = "Download Manager Moderator Control";
		$this->ipsclass->admin->page_detail  = "You can add or edit a moderator from this page.";
		
		//-----------------------------------------
		// Nav
		//-----------------------------------------
		
		if ( $this->ipsclass->input['c'] )
		{
			$nav = $this->func->get_nav( $this->ipsclass->input['c'], '&'.$this->ipsclass->form_code.'&c=', 1);
			
			if ( is_array($nav) and count($nav) > 1 )
			{
				array_shift($nav);
				
				$this->ipsclass->html .= "<div class='navstrip'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}'>Categories</a> &gt; ".implode( " &gt; ", $nav )."</div><br />";
			}
		}
		
		$catlist = $this->func->cat_jump_list(1);
		
		$mod_cats[] = $this->ipsclass->input['c'];
		
		if( $type == 'edit' )
		{
			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_mods', 'where' => 'modid='.intval($this->ipsclass->input['modid']) ) );
			$this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows() )
			{
				$row = $this->ipsclass->DB->fetch_row();
				
				$thiscats = explode( ",", $row['modcats'] );
				
				if( count($thiscats) )
				{
					foreach( $thiscats as $k => $v )
					{
						$mod_cats[] = $v;
					}
				}
			}
			
			$code = 'doeditmod';
		}
		else
		{
			$code = 'domod';
		}
		
		
		if( $catlist == '' )
		{
			$this->ipsclass->admin->error("You must create categories before you can assign moderators to those categories");
		}
		
		//-----------------------------------------
		// Print screen
		//-----------------------------------------
		
		$this->ipsclass->html .= "<script type='text/javascript' src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>";
		$this->ipsclass->html .= "<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:160px;display:none;z-index:1'></div>";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , $code	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'categories'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													             			 5 => array( 'modid'  , $this->ipsclass->input['modid'] ),
													    			)      );
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    		, "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Moderator Settings" );
		
		$dropdown = array( array( 1, 'Member' ), array( 0, 'Group' ) );
		$form['modtype']		= $this->ipsclass->adskin->form_dropdown( "modtype", $dropdown, $_POST['modtype'] ? $_POST['modtype'] : $row['modtype'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Is this moderator a group or a member</b><div style='color:gray;'>Hint: Member-specific privileges are honored over group moderator privileges.</div>", $form['modtype']  )      );
		
		$groups[] = array(0, 'Not A Group');
		$this->ipsclass->DB->build_query( array( 'select' => 'g_id, g_title', 'from' => 'groups', 'order' => 'g_title' ) );
		$this->ipsclass->DB->exec_query();
		
		while( $g = $this->ipsclass->DB->fetch_row() )
		{
			$groups[] = array( $g['g_id'], $g['g_title'] );
		}
		
		if( $type == 'edit' )
		{
			$formdefault = explode( ":", $row['modgmid'] );
		}
		
		$form['modgid']			= $this->ipsclass->adskin->form_dropdown( "modgid", $groups, $_POST['modgid'] ? $_POST['modgid'] : ( $row['modtype'] == 0 ? $formdefault[0] : 0 ) );
		$form['modmid']			= $this->ipsclass->adskin->form_input( "modmid", $this->ipsclass->parse_clean_value( $_POST['modmid'] ? $_POST['modmid'] : ( $row['modtype'] == 1 ? $formdefault[1] : '' ) ), "text", "id='modmid'" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Group or Member</b><div style='color:gray;'>Please select the group or enter the member's name.</div>", $form['modgid']." <b><i>OR</i></b> ".$form['modmid']  )      );
		
		$form['modcanedit']		= $this->ipsclass->adskin->form_yes_no( "modcanedit", $_POST['modcanedit'] ? $_POST['modcanedit'] : $row['modcanedit'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Can edit files</b><div style='color:gray;'>Do you wish to allow this moderator the ability to edit files</div>", $form['modcanedit'] )      );
		
		$form['modcandel']		= $this->ipsclass->adskin->form_yes_no( "modcandel", $_POST['modcandel'] ? $_POST['modcandel'] : $row['modcandel'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Can delete files</b><div style='color:gray;'>Do you wish to allow this moderator the ability to delete files</div>", $form['modcandel'] )      );
		
		$form['modcanapp']		= $this->ipsclass->adskin->form_yes_no( "modcanapp", $_POST['modcanapp'] ? $_POST['modcanapp'] : $row['modcanapp'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Can manage files</b><div style='color:gray;'>Do you wish to allow this moderator the ability to approve and unapprove files</div>", $form['modcanapp'] )      );
		
		$form['modcanbrok']		= $this->ipsclass->adskin->form_yes_no( "modcanbrok", $_POST['modcanbrok'] ? $_POST['modcanbrok'] : $row['modcanbrok'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Can manage broken files</b><div style='color:gray;'>Do you wish to allow this moderator the ability to manage files reported as broken</div>", $form['modcanbrok'] )      );
		
		$form['modcancomm']		= $this->ipsclass->adskin->form_yes_no( "modcancomments", $_POST['modcancomments'] ? $_POST['modcancomments'] : $row['modcancomments'] );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Can manage comments</b><div style='color:gray;'>Do you wish to allow this moderator the ability to manage comments?  This includes editing, deleting, and approving the comments.</div>", $form['modcancomm'] )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Categories</b><div style='color:gray;'>Please select the category or categories this moderator can manage.<br />Hint: You can select more than one by holding Ctrl while clicking</div>", 
																			 $this->ipsclass->adskin->form_multiselect( "modcats[]", $catlist, $_POST['modcats'] ? $_POST['modcats'] : $mod_cats, "8")
																	  )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Submit");
		
		$this->ipsclass->html .= "<script type='text/javascript'>
								// INIT find names
								init_js( 'theAdminForm', 'modmid', 'get-member-names');
								// Run main loop
								setTimeout( 'main_loop()', 10 );
								</script>";
								
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		$this->ipsclass->admin->output();
	}
	
	
	function do_moderator( $type='add' )
	{
		$moderator = array();
		
		if( $type == 'edit' && !$this->ipsclass->input['modid'] )
		{
			$this->ipsclass->main_msg = "There was a problem attempting to edit this moderator.";
			$this->show_mod_form( $type );
			return;
		}
		
		if( $type == 'edit' && $this->ipsclass->input['modid'] )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mods', 'where' => "modid=".intval($this->ipsclass->input['modid']) ) );
			$this->ipsclass->DB->simple_exec();
			
			if (! $this->ipsclass->DB->get_num_rows() )
			{
				$this->ipsclass->main_msg = "We could not find the moderator you tried to edit.";
				$this->show_mod_form( $type );
				return;
			}
			else
			{
				$moderator = $this->ipsclass->DB->fetch_row();
			}
		}		
					
		if( count($this->ipsclass->input['modcats']) == 0 )
		{
			$this->ipsclass->main_msg = "You did not select any categories for this moderator to moderate.";
			$this->show_mod_form( $type );
			return;
		}
		
		if( $this->ipsclass->input['modtype'] == 0 && !$this->ipsclass->input['modgid'] )
		{
			$this->ipsclass->main_msg = "You selected 'Group' for the moderator type but did not select the group.";
			$this->show_mod_form( $type );
			return;
		}			
		
		if( $this->ipsclass->input['modtype'] == 1 && !$this->ipsclass->input['modmid'] )
		{
			$this->ipsclass->main_msg = "You selected 'Member' for the moderator type but did not enter a member name.";
			$this->show_mod_form( $type );
			return;
		}
		
		if( $this->ipsclass->input['modtype'] == 0 )
		{
			$gid = intval($this->ipsclass->input['modgid']);
			
			$member = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'g_id, g_title', 'from' => 'groups', 'where' => 'g_id='.$gid ) ); 
			
			if( !$member['g_id'] )
			{
				$this->ipsclass->main_msg = "The group you selected appears to be invalid.";
				$this->show_mod_form( $type );
				return;
			}
		}
		else
		{
			$name = trim(str_replace( "|", "&#124;", $this->ipsclass->input['modmid'] ));
			
			$this->ipsclass->DB->simple_construct( array( 'select' => 'id as g_id, members_display_name as g_title', 'from' => 'members', 'where' => "name='".$name."' OR members_display_name='".$name."'", 'limit' => array(0,1) ) );
			$this->ipsclass->DB->simple_exec();
			
			if (! $this->ipsclass->DB->get_num_rows() )
			{
				$this->ipsclass->main_msg = "The member name you entered appears to be invalid.";
				$this->show_mod_form( $type );
				return;
			}
			else
			{
				$member = $this->ipsclass->DB->fetch_row();
			}
		}
		
		$cats = implode( ",", $this->ipsclass->input['modcats'] );
		
		$save_array = array( 'modtype'			=> intval($this->ipsclass->input['modtype']),
							 'modgmid'			=> $member['g_id'].":".$member['g_title'],
							 'modcanedit'		=> intval($this->ipsclass->input['modcanedit']),
							 'modcandel'		=> intval($this->ipsclass->input['modcandel']),
							 'modcanapp'		=> intval($this->ipsclass->input['modcanapp']),
							 'modcanbrok'		=> intval($this->ipsclass->input['modcanbrok']),
							 'modcancomments'	=> intval($this->ipsclass->input['modcancomments']),
							 'modcats'			=> $cats,
							);
							
		if( $type == 'add' )
		{
			$this->ipsclass->DB->do_insert( "downloads_mods", $save_array );
		}
		else
		{
			$this->ipsclass->DB->do_update( "downloads_mods", $save_array, "modid=".intval($this->ipsclass->input['modid']) );
		}
		
		$this->func->rebuild_mod_cache();
		
		$this->ipsclass->main_msg = "The moderator was succesfully added or edited!";
		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code );
	}
	
	
	function del_moderator( )
	{
		if( !$this->ipsclass->input['modid'] )
		{
			$this->ipsclass->main_msg = "There was a problem attempting to delete this moderator.";
			$this->show_mod_form( $type );
			return;
		}
		
		if( $this->ipsclass->input['modid'] )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_mods', 'where' => "modid=".intval($this->ipsclass->input['modid']) ) );
			$this->ipsclass->DB->simple_exec();
			
			if (! $this->ipsclass->DB->get_num_rows() )
			{
				$this->ipsclass->main_msg = "We could not find the moderator you tried to delete.";
				$this->show_mod_form( $type );
				return;
			}
			else
			{
				$moderator = $this->ipsclass->DB->fetch_row();
				
				$cats = explode( ",", $moderator['modcats'] );
				
				if( count($cats) == 1 && $this->ipsclass->input['c'] == $cat[0] )
				{
					$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_mods", 'where' => "modid=".intval($this->ipsclass->input['modid']) ) );
				}
				else if( count($cats) > 1 && $this->ipsclass->input['c'] )
				{
					$new_cats = array();
					
					foreach( $cats as $k => $v )
					{
						if( $v != $this->ipsclass->input['c'] )
						{
							$new_cats[] = $v;
						}
					}
					
					if( count($new_cats) > 0 )
					{
						$save_array = array( 'modcats' => implode( ",", $new_cats ) );
						$this->ipsclass->DB->do_update( "downloads_mods", $save_array, "modid=".intval($this->ipsclass->input['modid']) );
					}
				}
				else
				{
					$this->ipsclass->DB->build_and_exec_query( array( 'delete' => "downloads_mods", 'where' => "modid=".intval($this->ipsclass->input['modid']) ) );
				}
			}
		}		
					
		$this->func->rebuild_mod_cache();
		
		$this->ipsclass->main_msg = "The moderator was succesfully deleted!";
		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code.'&c='.$this->ipsclass->input['c'] );
	}
		
	
	function moderator_dropdown( $data=array() )
	{
		if( count($data) == 0 )
		{
			return;
		}
		
return <<<EOF
<div class='tablerow1' style='white-space:nowrap;font-weight:bold;float:left' id='modmenu{$data['c']}{$data['modid']}'>{$data['_fullname']} <img src='{$this->ipsclass->skin_acp_url}/images/icon_open.gif' border='0' style='vertical-align:top'/></div>
<script type="text/javascript">
  menu_build_menu(
  "modmenu{$data['c']}{$data['modid']}",
  new Array( img_item   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&c={$data['c']}&code=delmod&modid={$data['modid']}'>Remove...</a>",
  			 img_item   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=editmod&modid={$data['modid']}'>Edit...</a>"
		    ) );
</script>
EOF;
	}	
}
?>