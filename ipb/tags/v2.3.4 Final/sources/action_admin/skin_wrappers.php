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
|   > $Date: 2006-12-07 06:46:15 -0500 (Thu, 07 Dec 2006) $
|   > $Revision: 777 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Help Control functions
|   > Module written by Matt Mecham
|   > Date started: 2nd April 2002
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

class ad_skin_wrappers {

	var $base_url;
	var $template = "";
	var $functions = "";

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
	var $perm_child = "wrap";
	
	function auto_run()
	{
		//-----------------------------------------
		// Get the libraries
		//-----------------------------------------
		
		require_once( ROOT_PATH.'sources/lib/admin_template_functions.php' );
		
		$this->functions = new admin_template_functions();
		$this->functions->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'floateditor':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->functions->build_editor_area_floated(1);
				break;
			
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->do_form('edit');
				break;
				
			case 'doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->save_wrapper('edit');
				break;
				
			case 'export':
				$this->export();
			
			default:
				print "No action chosen"; exit();
				break;
				
			//case 'wrapper':
			//	$this->list_wrappers();
			//	break;
			//case 'add':
			//	$this->add_splash();
			//	break;
			//case 'doadd':
			//	$this->save_wrapper('add');
			//	break;
			//case 'remove':
			//	$this->remove();
			//	break;
		}
		
	}
	
	
	//-----------------------------------------
	// ADD / EDIT WRAPPERS
	//-----------------------------------------
	
	function save_wrapper( $type='add' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$id = intval( $this->ipsclass->input['id'] );
		
		//-----------------------------------------
		// Type?
		//-----------------------------------------
		
		if ($type == 'edit')
		{
			if ($this->ipsclass->input['id'] == "")
			{
				$this->ipsclass->admin->error("You must specify an existing wrapper ID, go back and try again");
			}
		}
		
		if ($this->ipsclass->input['txtwrapper'] == "")
		{
			$this->ipsclass->admin->error("模板不能为空");
		}
		
		$tmpl = $this->ipsclass->admin->form_to_text( $this->ipsclass->txt_stripslashes($_POST['txtwrapper']) );
		$tmpl = str_replace( '&#46;&#46;/', '../' , $tmpl );
		
		if ( ! preg_match( "/<% BOARD %>/", $tmpl ) )
		{
			$this->ipsclass->admin->error("您不能删除 &lt% BOARD %> 标记!");
		}
		
		if ( ! preg_match( "/<% COPYRIGHT %>/", $tmpl ) )
		{
			$this->ipsclass->admin->error("您不能删除 &lt% COPYRIGHT %> 标记!");
		}
		
		$this->ipsclass->DB->do_update( 'skin_sets', array( 'set_wrapper' => $tmpl ), 'set_skin_set_id='.$id );
		
		$this->ipsclass->cache_func->_recache_wrapper( $this->ipsclass->input['id'] );
		
		//-----------------------------------------
		// Done
		//-----------------------------------------
		
		if ( ! $this->ipsclass->input['savereload'] )
		{
			$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code ,'主题管理' );
			$this->ipsclass->main_msg = "论坛页面结构已更新";
			$this->ipsclass->admin->done_screen("坛页面结构已更新", "主题管理", 'section='.$this->ipsclass->section_code.'&act=sets', "redirect" );
		}
		else
		{
			//-----------------------------------------
			// Reload edit window
			//-----------------------------------------
			
			$this->ipsclass->main_msg = "论坛页面结构已更新";
			$this->do_form('edit');
		}
		
	}
	
	//-----------------------------------------
	// FORM
	//-----------------------------------------
	
	function do_form( $type='add' )
	{
		//-----------------------------------------
		// Check input
		//-----------------------------------------
		
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("You must specify an existing skin set ID, go back and try again");
		}
		
		//-----------------------------------------
		// check tree...
		//-----------------------------------------
		
		$found_id      = "";
		$found_content = "";
		$this_set      = "";
		
		if ( $this->ipsclass->input['p'] > 0 )
		{
			$in = ','.intval($this->ipsclass->input['p']);
		}
		
		//-----------------------------------------
		// Query
		//-----------------------------------------
		
		$this->ipsclass->DB->cache_add_query( 'stylesheets_do_form_concat', array( 'id' => intval($this->ipsclass->input['id']), 'parent' => $in ) );
		$this->ipsclass->DB->cache_exec_query();
		
		//-----------------------------------------
		// check tree...
		//-----------------------------------------
		
		while( $row = $this->ipsclass->DB->fetch_row() )
		{
			if ( $row['set_wrapper'] and ! $found_id )
			{
				$found_id      = $row['set_skin_set_id'];
				$found_content = $row['set_wrapper'];
			}
			
			if ( $this->ipsclass->input['id'] == $row['set_skin_set_id'] )
			{
				$this_set = $row;
			}
		}
		
		if ($type == 'add')
		{
			$code = 'doadd';
			$button = '创建页面结构';
		}
		else
		{
			$code = 'doedit';
			$button = '保存页面结构';
		}
		
		//-----------------------------------------
		// Header
		//-----------------------------------------
	
		$this->ipsclass->admin->page_detail = "创建和编辑页面结构时, 您可以使用 HTML 代码.";
		$this->ipsclass->admin->page_title  = "编辑论坛页面结构";
		
		if ( $found_id == 1 )
		{
			$this->ipsclass->admin->page_detail .= "<br /><strong>这是主页面结构的副本, 编辑这里将会生成一个新的页面结构</strong>";
		}
		
		//-----------------------------------------
		// Start form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , $code      ),
																			 2 => array( 'act'   , 'wrap'     ),
																			 3 => array( 'id'    , $this->ipsclass->input['id']   ),
																			 4 => array( 'fid'   , $found_id  ),
																			 5 => array( 'section', $this->ipsclass->section_code ),
																	), "theform"     );
									     
		//-----------------------------------------
		// Stop /textarea murdering layout
		//-----------------------------------------
		
		$found_content = $this->ipsclass->admin->text_to_form( $found_content );
		
		//-----------------------------------------
		// Editor section
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->functions->build_generic_editor_area( array( 'section' => $this->ipsclass->section_code, 'act' => 'wrap', 'title' => '', 'textareaname' => 'wrapper', 'textareainput' => $found_content ) );
		
		$formbuttons = "<div align='center' class='tablesubheader'>
						<input type='submit' name='submit' value='$button' class='realdarkbutton'>
						<input type='submit' name='savereload' value='保存并重新载入页面结构' class='realdarkbutton'>
						</div></form>\n";
		
		$this->ipsclass->html = str_replace( '<!--IPB.EDITORBOTTOM-->', $formbuttons, $this->ipsclass->html );
		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->skin_jump_menu_wrap();
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->ipsclass->admin->nav[] = array( 'section='.$this->ipsclass->section_code.'&act=sets' ,'主题管理' );
		$this->ipsclass->admin->nav[] = array( '' ,'编辑主题 '.$this_set['set_name'] .' 的论坛页面结构' );
		
		$this->ipsclass->admin->output();
	}
	
	
	
}


?>