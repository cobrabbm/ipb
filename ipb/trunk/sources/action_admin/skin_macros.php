<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2006-10-05 12:03:45 -0400 (Thu, 05 Oct 2006) $
|   > $Revision: 609 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Skin -> Image Macro functions
|   > Module written by Matt Mecham
|   > Date started: 4th April 2002
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_skin_macros {

	var $base_url;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "lookandfeel";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "image";


	function auto_run()
	{
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->show_macros();
				break;
				
			case 'doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->edit_set_name();
				break;
				
			case 'macroremove':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':remove' );
				$this->macro_remove();
				break;
				
			case 'doeditmacro':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->macro_edit();
				break;
				
			case 'doaddmacro':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':add' );
				$this->macro_add();
				break;
				
			default:
				print "No action"; exit();
				break;
		}
	}
	
	//-----------------------------------------
	// Remove macro
	//-----------------------------------------
	
	function macro_remove()
	{
		if ($this->ipsclass->input['mid'] == "")
		{
			$this->ipsclass->admin->error("You must specify an existing macro ID, go back and try again");
		}
		
		$id = intval($this->ipsclass->input['id']);
		$p  = intval($this->ipsclass->input['p']);
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'skin_macro', 'where' => "macro_id=".intval($this->ipsclass->input['mid']) ) );
		
		//-----------------------------------------
		// Recache macros
		//-----------------------------------------
		
		$this->ipsclass->cache_func->_recache_macros($id, $p);
		
		//-----------------------------------------
		// Bounce back
		//-----------------------------------------
		
		$this->ipsclass->input['id'] = $id;
		$this->ipsclass->main_msg = "宏已删除!";
		$this->show_macros();
	}
	
	//-----------------------------------------
	// Apply the edit to the DB
	//-----------------------------------------
	
	function macro_edit()
	{
		if ($this->ipsclass->input['mid'] == "")
		{
			$this->ipsclass->admin->error("You must specify an existing image set ID, go back and try again");
		}
		
		$id = intval($this->ipsclass->input['id']);
		$p  = intval($this->ipsclass->input['p']);
		
		$key = $this->ipsclass->DB->add_slashes( $this->ipsclass->txt_safeslashes($_POST['variable']) );
		$val = $this->ipsclass->DB->add_slashes( $this->ipsclass->txt_safeslashes($_POST['replacement']) );
		
		//-----------------------------------------
		// Get macro for examination..
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_macro', 'where' => "macro_id=".intval($this->ipsclass->input['mid']) ) );
		$this->ipsclass->DB->simple_exec();
		
 		if ( ! $row = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("Could not retrieve the record from the DB");
		}
		
		//-----------------------------------------
		// Is this our macro set?
		//-----------------------------------------
		
		if ( $row['macro_set'] == $id )
		{
			//-----------------------------------------
			// Okay, update...
			//-----------------------------------------
			
			$this->ipsclass->DB->simple_construct( array( 'update' => 'skin_macro', 'set' => "macro_value='$key', macro_replace='$val'", 'where' => "macro_id=".intval($this->ipsclass->input['mid']) ) );
			$this->ipsclass->DB->simple_exec();
		}
		else
		{
			//-----------------------------------------
			// No? OK - best add it as a 'new' macro
			//-----------------------------------------
			
			$this->ipsclass->DB->manual_addslashes = 1;
			$this->ipsclass->DB->do_insert( 'skin_macro', array (
												'macro_value'         => $key,
												'macro_replace'       => $val,
												'macro_can_remove'    => 1,
												'macro_set'           => $id
										)      );
										
			$this->ipsclass->DB->manual_addslashes = 0;
		}
		
		
		//-----------------------------------------
		// Recache macros
		//-----------------------------------------
		
		$this->ipsclass->cache_func->_recache_macros($id, $p);
		
		//-----------------------------------------
		// Bounce back
		//-----------------------------------------
		
		$this->ipsclass->input['id'] = $id;
		$this->show_macros();
	}
	
	//-----------------------------------------
	// ADD MACRO
	//-----------------------------------------
	
	function macro_add()
	{
		if ($this->ipsclass->input['mid'] == "")
		{
			$this->ipsclass->admin->error("You must specify an existing macro set ID, go back and try again");
		}
		
		$id = intval($this->ipsclass->input['id']);
		$p  = intval($this->ipsclass->input['p']);
		
		$this->ipsclass->DB->do_insert( 'skin_macro', array (
											 'macro_value'         => $this->ipsclass->txt_safeslashes($_POST['variable']),
											 'macro_replace'       => $this->ipsclass->txt_safeslashes($_POST['replacement']),
											 'macro_can_remove'    => 1,
											 'macro_set'           => $id
									 )      );
		
		//-----------------------------------------
		// Recache macros
		//-----------------------------------------
		
		$this->ipsclass->cache_func->_recache_macros($id, $p);
		
		//-----------------------------------------
		// Bounce back
		//-----------------------------------------
		
		$this->ipsclass->input['id'] = $id;
		$this->show_macros();
		
	}
	
	
	
	//-----------------------------------------
	// Show macros
	//-----------------------------------------
	
	function show_macros()
	{
		//-----------------------------------------
		
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("You must specify an existing macro set ID, go back and try again");
		}
		
		//-----------------------------------------
		// check tree...
		//-----------------------------------------
		
		$this_set      = "";
		
		if ( $this->ipsclass->input['p'] > 0 )
		{
			$in = ','.$this->ipsclass->input['p'];
		}
		
		//-----------------------------------------
		// Get macros
		//-----------------------------------------
		
		$macros = $this->ipsclass->cache_func->_get_macros($this->ipsclass->input['id'], $this->ipsclass->input['p']);
		
		//-----------------------------------------
		// Get img_dir this set is using...
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_sets', 'where' => "set_skin_set_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		$skin = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->admin->page_detail = "要编辑宏, 请点击宏旁的“编辑”按钮.";
										 
		$this->ipsclass->admin->page_title  = "编辑主题 {$skin['set_name']} 的宏定义";
		
		//-----------------------------------------
		// Start output
		//-----------------------------------------
															  
		$this->ipsclass->html .= "<script type='text/javascript'>
							function editmacro(id, variable, replace)
							{
								document.macroform.code.value         = 'doeditmacro';
								document.macroform.submitbutton.value = '保存修改';
								document.macroform.mid.value          = id;
								document.macroform.variable.value     = variable;
								document.macroform.replacement.value  = replace;
								scroll(0,0);
								togglediv( 'popbox', 1 );
								return false;
							}
							function addmacro(id)
							{
								document.macroform.code.value         = 'doaddmacro';
								document.macroform.submitbutton.value = '添加宏';
								document.macroform.mid.value          = id;
								document.macroform.variable.value     = '';
								document.macroform.replacement.value  = '';
								scroll(0,0);
								togglediv( 'popbox', 1 );
								return false;
							}
							function removemacro(url)
							{
								checkdelete(url);
							}
							</script>
							<div class='tableborder'>
							<div class='tableheaderalt'>
							  <table cellpadding='0' cellspacing='0' border='0' width='100%'>
							  <tr>
							  <td align='left' width='100%' style='font-weight:bold;font-size:12px;color:#FFF'>宏定义管理</td>
							  <td align='right' nowrap='nowrap' style='padding-right:2px'><input type='button' class='realdarkbutton' value='添加宏' onclick=\"addmacro('{$this->ipsclass->input['id']}');\" /></td>
							  </tr>
							  </table>
							  <div align='center' style='position:absolute;display:none;text-align:center;' id='popbox'>
							   <form name='macroform' action='{$this->ipsclass->base_url}' method='post'>
							   <input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
							   <input type='hidden' name='act' value='image' />
							   <input type='hidden' name='section' value='{$this->ipsclass->section_code}' />
							   <input type='hidden' name='code' value='' />
							   <input type='hidden' name='mid' value='' />
							   <input type='hidden' name='id' value='{$this->ipsclass->input['id']}' />
							   <input type='hidden' name='p' value='{$this->ipsclass->input['p']}' />
							   <table cellspacing='0' width='500' align='center' cellpadding='6' style='background:#EEE;border:2px outset #555;'>
							   <tr>
								<td width='1%' nowrap='nowrap' valign='top'>
								 <b>变量</b><br><input class='textinput' name='variable' type='text' size='20' />
								 <br /><br />
								 <center><input type='submit' class='realbutton' value='编辑宏' name='submitbutton' /> <input type='button' class='realdarkbutton' value='Close' onclick=\"togglediv('popbox');\" /></center>
								</td>
								<td width='99%'><b>替换为</b><br /><textarea class='textinput' name='replacement' style='width:99%;height:50px'></textarea></td>
							   </tr>
							   </table>
							   </form>
							  </div>
							</div>
							</div>
							
						   ";
			
		$this->ipsclass->html .= "<div class='tableborder'>\n<div class='tablepad'>\n<table width='100%' cellspacing='0' cellpadding='0' border='0'>";											  
		
		$this->unaltered    = "<img src='{$this->ipsclass->skin_acp_url}/images/skin_item_unaltered.gif' border='0' alt='-' title='Unaltered from parent skin set' />&nbsp;";
		$this->altered      = "<img src='{$this->ipsclass->skin_acp_url}/images/skin_item_altered.gif' border='0' alt='+' title='Altered from parent skin set' />&nbsp;";
		$this->inherited    = "<img src='{$this->ipsclass->skin_acp_url}/images/skin_item_inherited.gif' border='0' alt='|' title='Inherited from parent skin set' />&nbsp;";
		
		//-----------------------------------------
		// Loop and print
		//-----------------------------------------
		
		foreach( $macros as $row )
		{
			$real = $this->ipsclass->txt_htmlspecialchars( $row['macro_replace'] );
			$real = str_replace("\n", '\n', str_replace( "\r", '\r', $real) );
			
			//-----------------------------------------
			// Altered?
			//-----------------------------------------
			
			if ( $row['macro_set'] == $this->ipsclass->input['id'] )
			{
				$altered_image = $this->altered;
				$css_info      = '#FFDCD8';
			}
			else if ( $row['macro_set'] == 1 )
			{
				$altered_image = $this->unaltered;
				$css_info      = '#EEE';
			}
			else
			{
				$altered_image = $this->inherited;
				$css_info      = '#FFF2D3';
			}
			
			//-----------------------------------------
			// Figure out quotes
			//-----------------------------------------
			
			$out_quote = '"';
			$in_quote  = "'";
			
			if ( preg_match( "/&#039;/", $real ) )
			{
				$out_quote = "'";
				$in_quote  = '"';
			}
			
			if( $in_quote == '"' )
			{
				$real = str_replace( "&quot;", '\"', $real );
			}
			
			if( $in_quote == "'" )
			{
				$real = str_replace( "'", "\\'", $real );
			}
			
			$preview = str_replace( "<#IMG_DIR#>", $skin['set_image_dir'], $row['macro_replace'] );
			
			if ( $row['macro_set'] > 1 and $row['macro_set'] == $this->ipsclass->input['id'] )
			{
				$remove_button = "<input type='button' class='realbutton' name='remove' value='恢复' onclick=\"removemacro('{$this->ipsclass->form_code_js}&code=macroremove&mid={$row['macro_id']}&id={$this->ipsclass->input['id']}&p={$this->ipsclass->input['p']}');\" />";
			}
			else
			{
				$remove_button = "";
			}
			
			//-----------------------------------------
			// Not an image?
			//-----------------------------------------
			
			if ( ! preg_match( "#img\s{1,}src=#i", $row['macro_replace'] ) )
			{
				$preview = substr( $real, 0, 200 );
			}
			else
			{
				$preview = preg_replace( "#style_images#", $this->ipsclass->vars['board_url'].'/style_images', $preview );
			}
			
			$edit_button = "<input type='button' class='realbutton' value='修改' onclick={$out_quote}editmacro( {$in_quote}{$row['macro_id']}{$in_quote}, {$in_quote}{$row['macro_value']}{$in_quote}, {$in_quote}$real{$in_quote});{$out_quote} />";
			
			//-----------------------------------------
			// Render row
			//-----------------------------------------
			
			$style = "padding:4px;border-bottom:1px solid #DDD;background:{$css_info}";
			
			$this->ipsclass->html .= "<tr>
								 <!--<td style='$style' align='center' width='1%'><img src='{$this->ipsclass->skin_acp_url}/images/skin_macro.gif' alt='Macro' title='ID: {$row['macro_id']}' style='vertical-align:middle' /></td>-->
								 <td style='$style' align='left' width='1%' nowrap='nowrap'>$altered_image
								  &nbsp;&lt;{<span style='font-size:12px;font-weight:bold' title='ID: {$row['macro_id']}. SET: {$row['macro_set']}' href='#' >{$row['macro_value']}</span>}&gt;
								  </td>
								 <td style='$style;padding-right:3px;text-align:center;' align='center' width='99%' align='center'>$preview</td>
								 <td style='$style' align='right' width='40%' nowrap='nowrap'>$remove_button $edit_button</td>
								</tr>
								
								";
		}
									     
										 
		$this->ipsclass->html .= "</table>
						   </div>
						   <div class='tablesubheader' align='center'>
						    <input type='button' class='realdarkbutton' value='添加宏' onclick=\"addmacro('{$this->ipsclass->input['id']}');\" />
						   </div>
						   </div>";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->skin_jump_menu_wrap();
		
		//-----------------------------------------
		// Show altered / unaltered
		// legend
		//-----------------------------------------
		
		$this->ipsclass->html .= "<br />
							<div><strong>宏定义例程</strong><br />
							如果您添加了一个变量“green_font”, 设置它替换为 '&lt;font color='green'>', 那么那么每一个 <span style='color:red'><b>&lt;{green_font}&gt;</b></span> 将被自动转换成 &lt;font color='green'>
							<br /><b>&lt;#IMG_DIR#></b> 任何宏都可以使用, 此标记将自动转换为对应主题的图像目录
							</div><br />
							<div><strong>宏定义图例:</strong><br />
							{$this->altered} 此项目有自定义内容.
							<br />{$this->unaltered} 此项目没有自定义内容.
							<br />{$this->inherited} 此项目继承了父主题的自定义内容.
							</div>";
		
		$this->ipsclass->admin->nav[] = array( 'section='.$this->ipsclass->section_code.'&act=sets' ,'主题管理' );
		$this->ipsclass->admin->nav[] = array( '' ,'编辑主题 '.$skin['set_name'] .' 的宏定义');
		
		$this->ipsclass->admin->output();
		
	}
	
	
	
}


?>