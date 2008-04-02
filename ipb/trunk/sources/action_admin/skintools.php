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
|   > $Date: 2007-03-29 06:51:39 -0400 (Thu, 29 Mar 2007) $
|   > $Revision: 911 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Skin Tools
|   > Module written by Matt Mecham
|   > Date started: 22nd January 2004
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

class ad_skintools {

	var $base_url;
	var $db_html_files = "";
	var $ff_html_files = "";
	var $skin_id       = "";
	var $ff_fixes      = array();
	var $log           = array();
	
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
	var $perm_child = "skintools";


	function auto_run()
	{
		$this->ipsclass->admin->page_detail = "请仔细阅读每个工具的说明.";
		$this->ipsclass->admin->page_title  = "主题工具";
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code, '主题工具' );

		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'rebuildcaches':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->rebuildcaches();
				break;
				
			case 'rewritemastercache':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->rewrite_master_cache();
				break;
			
			case 'rebuildmastermacros':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->rewrite_master_macros();
				break;
				
			case 'rebuildmaster':
				$this->rebuildmaster();
				break;
				
			case 'rebuildmasterhtml':
				$this->rebuildmaster_html();
				break;
				
			case 'rebuildmastercomponents':
				$this->rebuildmaster_components();
				break;
				
			case 'changemember':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':changemember' );
				$this->change_member();
				break;
				
			case 'changeforum':
				$this->change_forum();
				break;				
				
			//-----------------------------------------
			// Search stuff
			//-----------------------------------------
			
			case 'searchsplash':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':search' );
				$this->searchreplace_start();
				break;
				
			case 'simplesearch':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':search' );
				$this->simple_search();
				break;
				
			case 'searchandreplace':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':search' );
				$this->search_and_replace();
				break;
				
			//-----------------------------------------
			// Search stuff
			//-----------------------------------------
			
			case 'easylogo':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->easy_logo_start();
				break;
			case 'easylogo_complete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->easy_logo_complete();
				break;
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->show_intro();
				break;
		}
	}
	
	//-----------------------------------------
	// REBUILD MASTER MACROS
	//-----------------------------------------
	
	function rewrite_master_macros()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$file     = ROOT_PATH . 'resources/macro.xml';
		$macros   = array();
		$updated  = 0;
		$inserted = 0;
		
		//-----------------------------------------
		// CHECK
		//-----------------------------------------
		
		if ( ! file_exists( $file ) )
		{
			$this->ipsclass->main_msg = "找不到文件 $file. 请检查并重新上传此文件";
			$this->show_intro();
		}
		
		//-----------------------------------------
		// Get current macros
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
												 'from'   => 'skin_macro',
												 'where'  => 'macro_set=1' ) );
												
		$this->ipsclass->DB->exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row() )
		{
			$macros[ $row['macro_value'] ] = $row['macro_replace'];
		}
		
		//-----------------------------------------
		// Get XML
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );
		
		$xml = new class_xml();		
				
		//-----------------------------------------
		// Get XML file
		//-----------------------------------------
		
		$skin_content = implode( "", file($file) );
		
		//-----------------------------------------
		// Unpack the datafile (TEMPLATES)
		//-----------------------------------------
		
		$xml->xml_parse_document( $skin_content );
		
		//-----------------------------------------
		// Check macros
		//-----------------------------------------
		
		if ( ! is_array( $xml->xml_array['macroexport']['macrogroup']['macro'] ) )
		{
			$this->ipsclass->main_msg = "处理 macros.xml 文件出错 - 无法处理 XML 属性";
			$this->show_intro();
		}
	
		foreach( $xml->xml_array['macroexport']['macrogroup']['macro'] as $entry )
		{
			$_key = $entry[ 'macro_value' ]['VALUE'];
			$_val = $entry[ 'macro_replace' ]['VALUE'];
			
			if ( $macros[ $_key ] )
			{
				$updated++;
				
				$this->ipsclass->DB->do_update( 'skin_macro', array( 'macro_value'   => $_key,
																	 'macro_replace' => $_val ), "macro_set=1 AND macro_value='".$this->ipsclass->DB->add_slashes( $_key )."'" );
			}
			else
			{
				$inserted++;
				
				$this->ipsclass->DB->do_insert( 'skin_macro', array( 'macro_set'     => 1,
																	 'macro_value'   => $_key,
																	 'macro_replace' => $_val  ) );
			}
		}
		
		$this->ipsclass->cache_func->_recache_macros( 1, -1 );

		$this->ipsclass->main_msg = "$updated 项宏已更新, $inserted 项已添加.";
		$this->show_intro();
	}
	
	/*-------------------------------------------------------------------------*/
	// Rebuild Master System Skin Set
	/*-------------------------------------------------------------------------*/
	
	/**
	* Rebuild Master System Templates from cacheid_1 directory
	*
	* @return	void
	*/
	function rewrite_master_cache()
	{
		$this->ipsclass->cache_func->_recache_templates( 1, -1, 0, 1, 1 );
		
		$this->ipsclass->main_msg .= implode("<br />", $this->ipsclass->cache_func->messages);
		
		$this->show_intro();
	}
	
	//-----------------------------------------
	// EASY LOGO CHANGER (COMPLETE)
	//-----------------------------------------
	
	function easy_logo_complete()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$master = array();
		
		//-----------------------------------------
		// Check id
		//-----------------------------------------
		
		if ( ! $this->ipsclass->input['set_skin_set_id'] )
		{
			$this->ipsclass->main_msg = "No skin set ID was passed. Please ensure you actually chose a skin set to edit";
			$this->easy_logo_start();
		}
		
		//-----------------------------------------
		// Grab the default template bit
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_templates', 'where' => "group_name='skin_global' AND func_name='global_board_header'" ) );
		$this->ipsclass->DB->simple_exec();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$master[ $r['set_id'] ] = $r;
		}
		
		if ( !isset($master[ $this->ipsclass->input['set_skin_set_id'] ]) OR !is_array($master[ $this->ipsclass->input['set_skin_set_id'] ]) )
		{
			$final_html = $master[1]['section_content'];
		}
		else
		{
			$final_html = $master[ $this->ipsclass->input['set_skin_set_id'] ]['section_content'];
		}
		
		if ( ! strstr( $final_html, '<!--ipb.logo.end-->' ) )
		{
			$this->ipsclass->main_msg = "找不到此主题的 Logo 标签 - 请确认您的模板已更新到最新版本.";
			$this->easy_logo_start();
		}
		
		//-----------------------------------------
		// Upload or new logo?
		//-----------------------------------------
		
		if ( $_FILES['FILE_UPLOAD']['name'] == "" or ! $_FILES['FILE_UPLOAD']['name'] or ($_FILES['FILE_UPLOAD']['name'] == "none") )
		{
			if ( ! $_POST['logo_url'] )
			{
				$this->ipsclass->main_msg = "您必须上传一个图片或者指定一个路径";
				$this->easy_logo_start();
			}
			
			$newlogo = $_POST['logo_url'];
		}
		else
		{
			if ( ! is_writable( CACHE_PATH.'style_images' ) )
			{
				$this->ipsclass->main_msg = "您必须确认“style_images”目录的 CHMOD 设置为可写, 有必要的话设置为 0777.";
				$this->easy_logo_start();
			}
			
			//-----------------------------------------
			// Upload
			//-----------------------------------------
			
			$FILE_NAME = $_FILES['FILE_UPLOAD']['name'];
			$FILE_SIZE = $_FILES['FILE_UPLOAD']['size'];
			$FILE_TYPE = $_FILES['FILE_UPLOAD']['type'];
			
			//-----------------------------------------
			// Silly spaces
			//-----------------------------------------
			
			$FILE_NAME = preg_replace( "/\s+/", "_", $FILE_NAME );
			
			//-----------------------------------------
			// Naughty Opera adds the filename on the end of the
			// mime type - we don't want this.
			//-----------------------------------------
			
			$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
			
			//-----------------------------------------
			// Correct file type?
			//-----------------------------------------
			
			if ( ! preg_match( "#\.(?:gif|jpg|jpeg|png)$#is", $FILE_NAME ) )
			{
				$this->ipsclass->main_msg = "您上传的文件格式不正确. 必须为 GIF, JPEG 或 PNG 格式.";
				$this->easy_logo_start();
			}
			
			if ( move_uploaded_file( $_FILES[ 'FILE_UPLOAD' ]['tmp_name'], CACHE_PATH."style_images/{$this->ipsclass->input['set_skin_set_id']}_".$FILE_NAME) )
			{
				@chmod( CACHE_PATH."style_images/{$this->ipsclass->input['set_skin_set_id']}_".$FILE_NAME, 0777 );
			}
			else
			{
				$this->ipsclass->main_msg = "上传失败. 请检查“style_images”目录的权限并确保上传文件小于 PHP 的限制 ( 一般为 2MB ).";
				$this->easy_logo_start();
			}
			
			$newlogo = "style_images/{$this->ipsclass->input['set_skin_set_id']}_".urlencode($FILE_NAME);
		}
		
		//-----------------------------------------
		// Convert back stuff
		//-----------------------------------------
		
		foreach( array( 'headerhtml', 'javascripthtml', 'leftlinkshtml', 'rightlinkshtml' ) as $mail )
		{
			//$_POST[ $mail ] = $this->ipsclass->admin->form_to_text( $_POST[ $mail ] );
			//$_POST[ $mail ] = str_replace( "\r\n", "\n", $_POST[ $mail ] );
		}
		
		//-----------------------------------------
		// Okay! Form the template
		//-----------------------------------------
		
		//$final_html = $_POST['headerhtml'];
		//$final_html = str_replace( "<{BOARD_LOGO}>", "<!--ipb.logo.start--><img src='$newlogo' alt='IPB' style='vertical-align:top' border='0' /><!--ipb.logo.end-->"      , $final_html );
		//$final_html = str_replace( "<{JAVASCRIPT}>", "<!--ipb.javascript.start-->\n{$_POST['javascripthtml']}\n<!--ipb.javascript.end-->"       , $final_html );
		//$final_html = str_replace( "<{LEFT_HAND_SIDE_LINKS}>", "<!--ipb.leftlinks.start-->{$_POST['leftlinkshtml']}<!--ipb.leftlinks.end-->"    , $final_html );
		//$final_html = str_replace( "<{RIGHT_HAND_SIDE_LINKS}>", "<!--ipb.rightlinks.start-->{$_POST['rightlinkshtml']}<!--ipb.rightlinks.end-->", $final_html );
		
		$final_html = preg_replace( "#<!--ipb.logo.start-->.+?<!--ipb.logo.end-->#si", "<!--ipb.logo.start--><img src='$newlogo' alt='IPB' style='vertical-align:top' border='0' /><!--ipb.logo.end-->"      , $final_html );
		
		//-----------------------------------------
		// Update the DeeBee
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'skin_templates', 'where' => "set_id=".intval($this->ipsclass->input['set_skin_set_id'])." AND group_name='skin_global' AND func_name='global_board_header'" ) );
		
		$this->ipsclass->DB->do_insert( 'skin_templates', array( 'section_content' => $final_html,
																 'set_id'          => $this->ipsclass->input['set_skin_set_id'],
																 'group_name'      => 'skin_global',
																 'func_name'       => 'global_board_header',
																 'func_data'       => '$component_links=""'
									 )                         );
		
		$this->ipsclass->cache_func->_rebuild_all_caches(array($this->ipsclass->input['set_skin_set_id']));
		
		$this->ipsclass->main_msg = 'Logo 已更换, 主题缓存已重建 (id: '.$this->ipsclass->input['set_skin_set_id'].')';
			
		$this->ipsclass->main_msg .= "<br />".implode("<br />", $this->ipsclass->cache_func->messages);
		
		$this->easy_logo_start();
	}
	
	//-----------------------------------------
	// EASY LOGO CHANGER (START)
	//-----------------------------------------
	
	function easy_logo_start()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$master    = array();
		$skin_list = "";
		$html      = array();
		
		//-----------------------------------------
		// Grab the default template bit
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_templates', 'where' => "group_name='skin_global' AND func_name='global_board_header'" ) );
		$this->ipsclass->DB->simple_exec();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$master[ $r['set_id'] ] = $r;
		}
		
		if ( ! $master[1]['section_content'] )
		{
			$this->ipsclass->main_msg = "找不到主模板元素 'global_board_header'";
			$this->show_intro();
		}
		
		if ( ! strstr( $master[1]['section_content'], '<!--ipb.logo.end-->' ) )
		{
			$this->ipsclass->main_msg = "找不到 Logo 标签 - 请确认您的模板已更新到最新版本.";
			$this->show_intro();
		}
		
		//-----------------------------------------
		// Get Skin Names
		//-----------------------------------------
		
		$skin_list = $this->_get_skinlist( 1 );
		
		//-----------------------------------------
		// get URL
		//-----------------------------------------
		
		preg_match( "#<!--ipb.logo.start--><img src=[\"'](.+?)[\"'].+?<!--ipb.logo.end-->#si", $master[1]['section_content'], $match );
		
		$current_img_url = $match[1];
		
		//-----------------------------------------
		// get current HTML
		//-----------------------------------------
		
		$current_html = $master[1]['section_content'];
		
		$current_html = preg_replace( "#<!--ipb.javascript.start-->.+?<!--ipb.javascript.end-->#is"               , "<{JAVASCRIPT}>"                   , $current_html );
		$current_html = preg_replace( "#<!--ipb.logo.start--><img src=[\"'](.+?)[\"'].+?<!--ipb.logo.end-->#si"   , "<{BOARD_LOGO}>"                   , $current_html );
		$current_html = preg_replace( "#<!--ipb.leftlinks.start-->.+?<!--ipb.leftlinks.end-->#si"                 , "<{LEFT_HAND_SIDE_LINKS}>"         , $current_html );
		$current_html = preg_replace( "#<!--ipb.rightlinks.start-->.+?<!--ipb.rightlinks.end-->#si"               , "<{RIGHT_HAND_SIDE_LINKS}>"        , $current_html );
		
		//-----------------------------------------
		// Regex out me bits
		//-----------------------------------------
		
		preg_match( "#<!--ipb.javascript.start-->(.+?)<!--ipb.javascript.end-->#si", $master[1]['section_content'], $match );
		$html['javascript'] = $this->ipsclass->admin->text_to_form($match[1]);
		
		preg_match( "#<!--ipb.leftlinks.start-->(.+?)<!--ipb.leftlinks.end-->#si"  , $master[1]['section_content'], $match );
		$html['leftlinks']  = $this->ipsclass->admin->text_to_form($match[1]);
		
		preg_match( "#<!--ipb.rightlinks.start-->(.+?)<!--ipb.rightlinks.end-->#si"  , $master[1]['section_content'], $match );
		$html['rightlinks']  = $this->ipsclass->admin->text_to_form($match[1]);
		
		$current_html        = $this->ipsclass->admin->text_to_form($current_html);
		
		//-----------------------------------------
		// Can we upload into style_images?
		//-----------------------------------------
		
		$warning = ! is_writable( CACHE_PATH.'style_images' ) ? "<div class='redbox' style='padding:4px'><strong>警告: 无法上传到 'style_images'.请检查该目录的文件访问许可!</strong></div>" : '';
		
		//-----------------------------------------
		// Start the form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'          ),
															     2 => array( 'code' , 'easylogo_complete'  ),
															     3 => array( 'MAX_FILE_SIZE', '10000000000' ),
															     4 => array( 'section', $this->ipsclass->section_code ),
													 ) , "uploadform", " enctype='multipart/form-data'"     );
													 
									     
		$this->ipsclass->html .= "<div class='tableborder'>
							<div class='tableheaderalt'>快速更换 Changer</div>
							<div class='tablepad' style='background-color:#EAEDF0'>
							$warning
							<fieldset class='tdfset'>
							 <legend><strong>配置</strong></legend>
							 <table width='100%' cellpadding='5' cellspacing='0' border='0'>
							 <tr>
							   <td width='40%' class='tablerow1'>应用到主题?<div class='graytext'>如果您在论坛模板中修改过论坛页眉, 本操作将覆盖您的修改</div></td>
							   <td width='60%' class='tablerow1'>$skin_list</td>
							 </tr>
							 <tr>
							   <td width='40%' class='tablerow1'>新 Logo 地址<div class='graytext'>可以使用相对连接或以 'http://' 开头的完整连接</div></td>
							   <td width='60%' class='tablerow1'>".$this->ipsclass->adskin->form_simple_input('logo_url', ( isset($_POST['logo_url']) AND $_POST['logo_url'] ) ? $_POST['logo_url'] : $current_img_url, '60' )."</td>
							 </tr>
							 <tr>
							   <td width='40%' class='tablerow1'><b><u>或者</u></b> 上传新<div class='graytext'>文件必须是 gif. jpg. jpeg 或 png 格式</div></td>
							   <td width='60%' class='tablerow1'>".$this->ipsclass->adskin->form_upload()."</td>
							 </tr>
							</table>
							</fieldset>
							</div>
							</div>";
							
		//-----------------------------------------
												 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form_standalone("确定");
		
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	}
	
	//-----------------------------------------
	// REBUILD MASTER COMPONENTS
	//-----------------------------------------
	
	function rebuildmaster_components()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$file    = ROOT_PATH . 'resources/skinsets.xml';
		
		if ( ! file_exists( $file ) )
		{
			$this->ipsclass->main_msg = "找不到文件 $file , 请检查并重新上传";
			$this->show_intro();
		}
		
		//-----------------------------------------
		// Get XML
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );
		
		$xml = new class_xml();		
				
		//-----------------------------------------
		// Get XML file (CSS/WRAPPERS)
		//-----------------------------------------
		
		$skin_content = implode( "", file($file) );
		
		//-----------------------------------------
		// Unpack the datafile (TEMPLATES)
		//-----------------------------------------
		
		$xml->xml_parse_document( $skin_content );
		
		//-----------------------------------------
		// (TEMPLATES)
		//-----------------------------------------

		if ( ! $xml->xml_array['export']['group']['row'][0]['set_css']['VALUE'] OR ! $xml->xml_array['export']['group']['row'][0]['set_wrapper']['VALUE'] )
		{
			$this->ipsclass->main_msg = "处理 resources/ipb_templates.xml 文件出错 - 无法处理 XML 属性";
			$this->show_intro();
		}
		else
		{		
			$this->ipsclass->DB->do_update( 'skin_sets', array( 'set_css'           => $xml->xml_array['export']['group']['row'][0]['set_css']['VALUE'],
																'set_cache_css'     => $xml->xml_array['export']['group']['row'][0]['set_css']['VALUE'],
																'set_wrapper'       => $xml->xml_array['export']['group']['row'][0]['set_wrapper']['VALUE'],
																'set_cache_wrapper' => $xml->xml_array['export']['group']['row'][0]['set_wrapper']['VALUE'],
															  ), 'set_skin_set_id=1' );
		}
		
		$this->ipsclass->main_msg = "主模版组件已更新";
		$this->show_intro();
	}
	
	//-----------------------------------------
	// REBUILD MASTER HTML
	//-----------------------------------------
	
	function rebuildmaster_html()
	{
		$master  = array();
		$inserts = 0;
		$updates = 0;
		
		//-----------------------------------------
		// Template here?
		//-----------------------------------------
		
		if ( ! file_exists( ROOT_PATH.'resources/ipb_templates.xml' ) )
		{
			$this->ipsclass->main_msg = "找不到 resources/ipb_templates.xml 文件, 请检查并重新上传";
			$this->show_intro();
		}
		
		//-----------------------------------------
		// First, get all the default bits
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'suid,group_name,func_name', 'from' => 'skin_templates', 'where' => 'set_id=1' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$master[ strtolower( $r['group_name'] ) ][ strtolower( $r['func_name'] ) ] = $r['suid'];
		}
		
		//-----------------------------------------
		// Get XML
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );
		
		$xml = new class_xml();
		
		//-----------------------------------------
		// Get XML file (TEMPLATES)
		//-----------------------------------------
		
		$xmlfile = ROOT_PATH.'resources/ipb_templates.xml';
		
		$setting_content = implode( "", file($xmlfile) );
		
		//-----------------------------------------
		// Unpack the datafile (TEMPLATES)
		//-----------------------------------------
		
		$xml->xml_parse_document( $setting_content );
		
		//-----------------------------------------
		// (TEMPLATES)
		//-----------------------------------------
		
		if ( ! is_array( $xml->xml_array['templateexport']['templategroup']['template'] ) )
		{
			$this->ipsclass->main_msg = "处理 resources/ipb_templates.xml 文件出错 - 无法处理 XML 属性";
			$this->show_intro();
		}
	
		foreach( $xml->xml_array['templateexport']['templategroup']['template'] as $entry )
		{
			$newrow = array();
			
			$newrow['group_name']            = $entry[ 'group_name' ]['VALUE'];
			$newrow['section_content']       = $entry[ 'section_content' ]['VALUE'];
			$newrow['func_name']             = $entry[ 'func_name' ]['VALUE'];
			$newrow['func_data']             = $entry[ 'func_data' ]['VALUE'];
			$newrow['group_names_secondary'] = $entry[ 'group_names_secondary' ]['VALUE'];
			$newrow['set_id']                = 1;
			$newrow['updated']               = time();
			
			if ( $master[ strtolower( $newrow['group_name'] ) ][ strtolower( $newrow['func_name'] ) ] )
			{
				//-----------------------------------------
				// Update
				//-----------------------------------------
				
				$updates++;
				
				$this->ipsclass->DB->do_update( 'skin_templates', $newrow, 'suid='.$master[ strtolower( $newrow['group_name'] ) ][ strtolower( $newrow['func_name'] ) ] );
			}
			else
			{
				//-----------------------------------------
				// Insert
				//-----------------------------------------
				
				$inserts++;
				
				$this->ipsclass->DB->do_insert( 'skin_templates', $newrow );
			}
		}
		
		$this->ipsclass->main_msg = "主模板已重建!<br />更新 $updates 项模板元素, 插入 $inserts 项新模版元素";
		
		$this->show_intro();
	}
	
	//-----------------------------------------
	// COMPLEX SEARCH
	//-----------------------------------------
	
	function search_and_replace()
	{
		//-----------------------------------------
		// Get $skin_names stuff
		//-----------------------------------------
		
		require_once( ROOT_PATH.'sources/lib/skin_info.php' );
		
		$SEARCH_set  = intval( $this->ipsclass->input['set_skin_set_id'] );
		$SEARCH_all  = intval( $this->ipsclass->input['searchall'] );
		
		//-----------------------------------------
		// Get set stuff
		//-----------------------------------------
		
		$this_set = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'skin_sets', 'where' => 'set_skin_set_id='.$SEARCH_set ) ); 
		
		//-----------------------------------------
		// Clean up before / after
		//-----------------------------------------
		
		$before = $this->ipsclass->txt_stripslashes($_POST['searchfor']);
		$after  = $this->ipsclass->txt_stripslashes($_POST['replacewith']);
		$before = str_replace( '"', '\"', $before );
		$after  = str_replace( '"', '\"', $after  );
		
		if ( ! $before )
		{
			$this->ipsclass->main_msg = "您必须输入搜索条件.";
			$this->searchreplace_start();
		}
		
		//-----------------------------------------
		// Clean up regex
		//-----------------------------------------
		
		if ( $this->ipsclass->input['regexmode'] )
		{
			$before = str_replace( '#', '\#', $before );
			
			//-----------------------------------------
			// Test to ensure they are legal
			// - catch warnings, etc
			//-----------------------------------------
			
			ob_start();
			eval( "preg_replace( \"#{$before}#i\", \"{$after}\", '' );");
			$return = ob_get_contents();
			ob_end_clean();
			
			if ( $return )
			{
				$this->ipsclass->main_msg = "处理“搜索”和“替换为”变量时出错 - 请确保它们是合法的正则表达式.";
				$this->searchreplace_start();
			}
		}
		
		//-----------------------------------------
		// we're here, so it's good
		//-----------------------------------------
		
		$templates = array();
		$the_templates = array();
		$matches   = 0;
		
		if ( $SEARCH_all )
		{
			$the_templates = $this->ipsclass->cache_func->_get_templates( $this_set['set_skin_set_id'], $this_set['set_skin_set_parent'], 'all' );
		}
		else
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_templates', 'where' => 'set_id='.$SEARCH_set ) );
			$this->ipsclass->DB->simple_exec();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$the_templates[ $r['group_name'] ][ strtolower($r['func_name']) ] = $r;
			}
		}
		
		if( count($the_templates) && is_array($the_templates) )
		{
			foreach( $the_templates as $group_name => $group_data )
			{
				foreach( $group_data as $func_name => $template_data )
				{
					if ( $this->ipsclass->input['regexmode'] )
					{
						if ( preg_match( "#{$before}#i", $template_data['section_content'] ) )
						{
							$templates[ $group_name ][ $func_name ] = $template_data;
							$matches++;
						}
					}
					else if ( strstr( $template_data['section_content'], $before ) )
					{
						$templates[ $group_name ][ $func_name ] = $template_data;
						$matches++;
					}
				}
			}
		}
		

		/*$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_templates', 'where' => 'set_id='.$SEARCH_set ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			if ( $this->ipsclass->input['regexmode'] )
			{
				if ( preg_match( "#{$before}#i", $r['section_content'] ) )
				{
					$templates[ $r['group_name'] ][ strtolower($r['func_name']) ] = $r;
					$matches++;
				}
			}
			else if ( strstr( $r['section_content'], $before ) )
			{
				$templates[ $r['group_name'] ][ strtolower($r['func_name']) ] = $r;
				$matches++;
			}
		}*/
		
		//-----------------------------------------
		// No matches...
		//-----------------------------------------
		
		if ( ! count($templates) )
		{
			$this->ipsclass->html .= "<div class='tableborder'>
								 <div class='tableheaderalt'>搜索 & 替换结果</div>
								 <div class='tablepad'>
								  <b>您搜索的关键字是: ".stripslashes(htmlspecialchars($before))."</b>
								  <br />
								  <br />
								  很不幸, 没有搜索到任何记录. 请放宽搜索条件后重试.
								 </div>
								</div>";
			
			$this->ipsclass->admin->output();
		}
		
		//-----------------------------------------
		// Swapping or showing?
		//-----------------------------------------
		
		if ( $this->ipsclass->input['testonly'] )
		{
			$this->ipsclass->html .= "<div class='tableborder'>
								 <div class='tableheaderalt'>搜索 & 替换结果</div>
								 <div class='tablepad' style='padding:5px'><b style='font-size:12px'>找到 {$matches} 项匹配关键字：“".htmlentities($before)."”，替换为“".htmlentities($after)."”</b><br /><br />";
								 
			//-----------------------------------------
			// Go fru dem all and print..
			//-----------------------------------------
			
			foreach( $templates as $group => $d )
			{
				foreach( $templates[ $group ] as $tmp_data )
				{
					if ( isset($skin_names[ $group ]) )
					{
						$group_name = $skin_names[ $group ][0];
					}
					else
					{
						$group_name = $group;
					}
					
					$html = $tmp_data['section_content'];
					
					//-----------------------------------------
					// Decode...
					//-----------------------------------------
					
					$hl    = $before;
					$after = str_replace( '\\\\', '\\\\\\', $after );
					
					if ( ! $after )
					{
						$hl   = preg_replace( "#\((.+?)\)#s", "(?:\\1)", $hl );
						$html = preg_replace( "#({$hl})#si" , '{#-^--opentag--^-#}'."\\1".'{#-^--closetag--^-#}', $html );
					}
					else
					{
						//-----------------------------------------
						// Wrap tags (so we don't use
						// < >, etc )
						//-----------------------------------------
						
						$html = preg_replace( "#{$hl}#si", '{#-^--opentag--^-#}'.$after.'{#-^--closetag--^-#}', $html );
					}
					
					//-----------------------------------------
					// Clean up..
					//-----------------------------------------
					
					$html = str_replace( "{#-^--opentag--^-#}\\", '{#-^--opentag--^-#}', $html );
					
					//-----------------------------------------
					// convert to printable html
					//-----------------------------------------
					
					$html = str_replace( "<" , "&lt;"  , $html);
					$html = str_replace( ">" , "&gt;"  , $html);
					$html = str_replace( "\"", "&quot;", $html);
					
					$html = preg_replace( "!&lt;\!--(.+?)(//)?--&gt;!s"              , "&#60;&#33;<span style='color:red'>--\\1--\\2</span>&#62;", $html );
					$html = preg_replace( "#&lt;([^&<>]+)&gt;#s"                     , "<span style='color:blue'>&lt;\\1&gt;</span>"             , $html );   //Matches <tag>
					$html = preg_replace( "#&lt;([^&<>]+)=#s"                        , "<span style='color:blue'>&lt;\\1</span>="                , $html );   //Matches <tag
					$html = preg_replace( "#&lt;/([^&]+)&gt;#s"                      , "<span style='color:blue'>&lt;/\\1&gt;</span>"            , $html );   //Matches </tag>
					$html = preg_replace( "!=(&quot;|')([^<>])(&quot;|')(\s|&gt;)!s" , "=\\1<span style='color:purple'>\\2</span>\\3\\4"         , $html );   //Matches ='this'
					
					//-----------------------------------------
					// convert back wrap tags
					//-----------------------------------------
					
					$html = str_replace( '{#-^--opentag--^-#}' , "<span style='color:red;font-weight:bold;background-color:yellow'>", $html );
					$html = str_replace( '{#-^--closetag--^-#}', "</span>", $html );
			
					$this->ipsclass->html .= "<div class='tableborder'>
										 <div class='tableheaderalt'>{$group_name} &middot; {$tmp_data['func_name']}</div>
										 <div class='tablerow2' style='height:100px;overflow:auto'><pre>{$html}</pre></div>
										</div>
										<br />";
				}
			}
			
			$this->ipsclass->html .= "</div></div>";
			
			$this->ipsclass->admin->nav[] = array( "", "从主题 ".$this_set['set_name']." 的搜索结果" );
			
			$this->ipsclass->admin->output();
		}
		else
		{
			//-----------------------------------------
			// Jus' do iiit
			//-----------------------------------------
			
			$after  = str_replace( '\\\\', '\\\\\\', $after );
			$report = array();
			
			foreach( $templates as $group => $d )
			{
				foreach( $templates[ $group ] as $tmp_data )
				{
					if ( $this->ipsclass->input['regexmode'] )
					{
						$tmp_data['section_content'] = preg_replace( "#{$before}#si", $after, $tmp_data['section_content'] );
						
					}
					else
					{
						$tmp_data['section_content'] = str_replace( $before, $after, $tmp_data['section_content'] );
					}
					
					$do_insert = 0;
					$insert_array = array();
					
					// Protect master templates...
					if( $tmp_data['set_id'] == 1 )
					{
						$tmp_data['set_id'] = $SEARCH_set;
					
						$quick_check = $this->ipsclass->DB->simple_exec_query( array( 'select' => "COUNT(*) as thecnt", 'from' => 'skin_templates', 
														'where' => "group_name='{$tmp_data['group_name']}' AND func_name='{$tmp_data['func_name']}' AND set_id='{$tmp_data['set_id']}'" ) );

						if( $quick_check['thecnt'] == 0 )
						{
							$do_insert = 1;
						}
					}

					if( !$do_insert )
					{
						//-----------------------------------------
						// Update DB
						//-----------------------------------------
						
						$this->ipsclass->DB->do_update( 'skin_templates', array( 'section_content' => $tmp_data['section_content'] ), 'suid='.$tmp_data['suid'] );
					}
					else
					{
						$insert_array = array( 'set_id' 			=> $tmp_data['set_id'],
												'group_name' 		=> $tmp_data['group_name'],
												'func_name' 		=> $tmp_data['func_name'],
												'section_content' 	=> $tmp_data['section_content'],
												'func_data' 		=> $tmp_data['func_data'],
												'updated' 			=> time(),
												'can_remove' 		=> 1
											 );
						
						$this->ipsclass->DB->do_insert( 'skin_templates', $insert_array );
					}
					
					$report[] = $tmp_data['func_name'].' 已更新...';
				}
			}
			
			//-----------------------------------------
			// Recache skin template..
			//-----------------------------------------
			
			$this->ipsclass->cache_func->_recache_templates( $SEARCH_set, $this_set['set_skin_set_parent'] );
			$report[] = "{$this_set['set_name']}的模板缓存已重建";
			
			$this->ipsclass->main_msg = implode( "<br />", $report );
			$this->searchreplace_start();
		}
	}
	
	//-----------------------------------------
	// SIMPLE SEARCH
	//-----------------------------------------
	
	function simple_search()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$rawword   = $_GET['searchkeywords'] ? urldecode( $_GET['searchkeywords'] ) : $_POST['searchkeywords'];
 		$templates = array();
		$final     = array();
		$matches   = array();
		
		//-----------------------------------------
		// CLEAN UP
		//-----------------------------------------
		
		$SEARCH_word = trim( $this->ipsclass->txt_safeslashes( $rawword ) );
		$SEARCH_safe = urlencode( $SEARCH_word );
		$SEARCH_all  = intval( $this->ipsclass->input['searchall'] );
		$SEARCH_set  = intval( $this->ipsclass->input['set_skin_set_id'] );
		
		//-----------------------------------------
		// check (please?)
		//-----------------------------------------
		
		if ( ! $SEARCH_word )
		{
			$this->ipsclass->main_msg = "您必须输入搜索条件";
			$this->searchreplace_start();
		}
		
		//-----------------------------------------
		// Get set stuff
		//-----------------------------------------
		
		$this_set = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'skin_sets', 'where' => 'set_skin_set_id='.$SEARCH_set ) ); 
		
		if ( ! $this_set['set_skin_set_id'] )
		{
			$this->ipsclass->main_msg = "No such set was found in the DB";
			$this->searchreplace_start();
		}
		
		//-----------------------------------------
		// Get templates from DB
		//-----------------------------------------
		
		if ( $SEARCH_all )
		{
			$templates = $this->ipsclass->cache_func->_get_templates( $this_set['set_skin_set_id'], $this_set['set_skin_set_parent'], 'all' );
		}
		else
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_templates', 'where' => 'set_id='.$SEARCH_set ) );
			$this->ipsclass->DB->simple_exec();
			
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$templates[ $r['group_name'] ][ strtolower($r['func_name']) ] = $r;
			}
		}
		
		if ( ! count( $templates ) )
		{
			$this->ipsclass->main_msg = "Couldn't locate any templates to search in!";
			$this->searchreplace_start();
		}
		
		//-----------------------------------------
		// Go fru dem all and search
		//-----------------------------------------
		
		foreach( $templates as $group => $d )
		{
			foreach( $templates[ $group ] as $tmp_data )
			{
				if ( strstr( strtolower( $tmp_data['section_content'] ), strtolower( $SEARCH_word ) ) )
				{
					$final[ $group ][] = $tmp_data;
				}
			}
		}
		
		//-----------------------------------------
		// Print..
		//-----------------------------------------
		
		if ( ! count($final) )
		{
			$this->ipsclass->html .= "<div class='tableborder'>
								 <div class='tableheaderalt'>搜索结果</div>
								 <div class='tablepad'>
								  <b>您搜索的关键字是: ".htmlentities($SEARCH_word)."</b>
								  <br />
								  <br />
								  很不幸, 没有搜索到任何记录. 请放宽搜索条件后重试.
								 </div>
								</div>";
								
			$this->ipsclass->admin->output();
		}
		
		//-----------------------------------------
		// SET ids right
		//-----------------------------------------
		
		$this->ipsclass->input['id']   = $SEARCH_set;
		$this->ipsclass->input['p']    = $this_set['set_skin_set_parent'];
		$this->ipsclass->input['code'] = 'template-sections-list';
		$this->ipsclass->input['act']  = 'templ';
		$this->ipsclass->form_code     = 'section=lookandfeel&amp;act=templ';
		$this->ipsclass->form_code_js  = str_replace( '&amp;', '&', $this->ipsclass->form_code );
		
		//-----------------------------------------
		// Pass array
		//-----------------------------------------
		
		require_once( ROOT_PATH."sources/action_admin/skin_template_bits.php" );
		$temp              =  new ad_skin_template_bits();
		$temp->ipsclass    =& $this->ipsclass;
		$temp->search_bits =  $final;
		$temp->auto_run();
	}
	
	//-----------------------------------------
	// SEARCH & REPLACE SPLASH
	//-----------------------------------------
	
	function searchreplace_start()
	{
		$skin_list = $this->_get_skinlist( 1 );
		
		$this->ipsclass->admin->page_detail = "您可以使用本工具批量搜索某个关键字, 替换到其他 HTML.";
		$this->ipsclass->admin->page_title  = "主题搜索 & 替换";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'     ),
																			 2 => array( 'code' , 'simplesearch'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "简单搜索" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索条件...</b><br /><span style='color:gray'>可以输入词语或者 HTML 代码</span>",
															       $this->ipsclass->adskin->form_simple_input( 'searchkeywords', '', 30 )
													    )      );
													  
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索范围...</b>",
															     $skin_list
															     ."<br /><input type='checkbox' name='searchall' value='1'> 在所选主题和所有父模板中搜索."
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Search");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// Search and replace
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'     ),
															     			 2 => array( 'code' , 'searchandreplace'  ),
															     	 		 4 => array( 'section', $this->ipsclass->section_code ),
													    )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "搜索和替换" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索条件...</b><br /><span style='color:gray'>可以输入词语或者 HTML 代码.<br />如果开启了“正则模式”，您可以输入正则表达式.</span>",
															      $this->ipsclass->adskin->form_textarea( 'searchfor', $_POST['searchfor'] )
													    )      );
													  
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>替换为...</b><br /><span style='color:gray'>可以输入 HTML 代码<br />如果开启了'正则模式'，您可以输入正则表达式.</span>",
															     $this->ipsclass->adskin->form_textarea( 'replacewith', $_POST['replacewith'] )
													    )      );
													    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索范围...</b><br /><span style='color:gray'>NOTE: The search and replace will only work on the specified skin set. The parent and master skin sets will NOT be searched or any replacements made on them.</span>",
															     $skin_list
															     ."<br /><input type='checkbox' name='searchall' value='1'> 在所选主题和所有父模板中搜索."
													    )      );
													    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>测试搜索和替换?</b><br /><span style='color:gray'>>如果是, 您会看到替换的效果, 但是不会真的替换数据.</span>",
															      $this->ipsclass->adskin->form_yes_no( 'testonly', 1 )
													    )      );
													    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Enable 'regex' mode?</b><br /><span style='color:gray'>如果是, 您可以使用正则表达式.
																 <br />例如: - 替换所有的 &lt;br&gt; or &lt;br /&gt; 替换为 &lt;br clear='all' /&gt;
																 <br />搜索条件为: <b>&lt;(br)&#92;s?/?&gt;</b>
																 <br />替换为: <b>&lt;&#92;&#92;1 clear='all' /&gt;</b></span>",
															      $this->ipsclass->adskin->form_yes_no( 'regexmode', 0 )
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("搜索");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	//-----------------------------------------
	// Swap members...
	//-----------------------------------------
	
	function change_member()
	{
		if( is_array($this->ipsclass->input['set_skin_set_id']) AND count($this->ipsclass->input['set_skin_set_id']) )
		{
			$this->ipsclass->input['set_skin_set_id'] = $this->ipsclass->clean_int_array($this->ipsclass->input['set_skin_set_id']);
			
			$query_bit = " IN (".implode(",",$this->ipsclass->input['set_skin_set_id']).")";
		}
		else
		{
			$this->ipsclass->main_msg = "您没有选择主题";
			$this->show_intro();
			return;
		}
		
		$new_id = intval($this->ipsclass->input['set_skin_set_id2']);
		
		if ($new_id == 'n')
		{
			$this->ipsclass->DB->do_update( 'members', array( 'skin' => '' ), 'skin'.$query_bit );
		}
		else
		{
			$this->ipsclass->DB->do_update( 'members', array( 'skin' => $new_id ), 'skin'.$query_bit );
		}
		
		$this->ipsclass->main_msg = "会员信息已更新";
		
		$this->show_intro();
	}
	
	//-----------------------------------------
	// Swap forums...
	//-----------------------------------------
	
	function change_forum()
	{
		if( is_array($this->ipsclass->input['set_skin_set_id']) AND count($this->ipsclass->input['set_skin_set_id']) )
		{
			$this->ipsclass->input['set_skin_set_id'] = $this->ipsclass->clean_int_array($this->ipsclass->input['set_skin_set_id']);
			
			$query_bit = " IN (".implode(",",$this->ipsclass->input['set_skin_set_id']).")";
		}
		else
		{
			$this->ipsclass->main_msg = "您没有选择主题";
			$this->show_intro();
			return;
		}
		
		$new_id = intval($this->ipsclass->input['set_skin_set_id2']);
		
		if ($new_id == 'n')
		{
			$this->ipsclass->DB->do_update( 'forums', array( 'skin_id' => '' ), 'skin_id'.$query_bit );
		}
		else
		{
			$this->ipsclass->DB->do_update( 'forums', array( 'skin_id' => $new_id ), 'skin_id'.$query_bit );
		}
		
		$this->ipsclass->update_forum_cache();
		
		$this->ipsclass->main_msg = "版块已更新";
		
		$this->show_intro();
	}	
	
	//-----------------------------------------
	// REBUILD MASTER
	//-----------------------------------------
	
	function rebuildmaster()
	{
		$pid = intval($this->ipsclass->input['phplocation']);
		$cid = intval($this->ipsclass->input['csslocation']);
		
		if ( $this->ipsclass->input['phpyes'] )
		{
			if ( ! file_exists( CACHE_PATH.'cache/skin_cache/cacheid_'.$pid ) )
			{
				$this->ipsclass->main_msg = '由于文件夹”cacheid_$pid“不存在，无法重建主模板';
			}
			
			$this->ipsclass->cache_func->_rebuild_templates_from_php($pid);
			
			$this->ipsclass->main_msg = '试图从 PHP 缓存文件重建主主题...';
				
			$this->ipsclass->main_msg .= "<br />".implode("<br />", $this->ipsclass->cache_func->messages);
		}
		
		if ( $this->ipsclass->input['cssyes'] )
		{
			if ( ! file_exists( CACHE_PATH.'style_images/css_'.$cid.'.css' ) )
			{
				$this->ipsclass->main_msg = '由于 CSS 文件”css_$cid“不存在, 无法重建主 CSS.';
			}
			
			$css = @file_get_contents( CACHE_PATH.'style_images/css_'.$cid.'.css' );
			
			if ( ! $css )
			{
				$this->ipsclass->main_msg = '由于 CSS 文件”css_$cid" 没有内容, 无法重建主 CSS.';
			}
			
			$css = trim( preg_replace( "#^.*\*~START CSS~\*/#s", "", $css ) );
			
			//-----------------------------------------
			// Attempt to rearrange style_images dir stuff
			//-----------------------------------------
			
			$this->ipsclass->main_msg = '试图从 CSS 缓存文件重建主 CSS...';
			
			$css = preg_replace( "#url\(([\"'])?(.+?)/(.+?)([\"'])?\)#is", "url(\\1style_images/1/\\3\\4)", $css );
			
			$this->ipsclass->DB->do_update( 'skin_sets', array( 'set_css' => $css, 'set_cache_css' => $css, 'set_css_updated' => time() ), 'set_skin_set_id=1' );
			
			$this->ipsclass->cache_func->_write_css_to_cache(1);
			
			$this->ipsclass->main_msg .= "<br />".implode("<br />", $this->ipsclass->cache_func->messages);
		}
		
		$this->show_intro();
	}
	
	//-----------------------------------------
	// REBUILD CACHES
	//-----------------------------------------
	
	function rebuildcaches()
	{
		$this->ipsclass->cache_func->_rebuild_all_caches(array($this->ipsclass->input['set_skin_set_id']));
		
		$this->ipsclass->main_msg = '主题缓存已重建 (id: '.$this->ipsclass->input['set_skin_set_id'].')';
			
		$this->ipsclass->main_msg .= "<br />".implode("<br />", $this->ipsclass->cache_func->messages);
		
		$this->show_intro();
	}
	
	//-----------------------------------------
	// SHOW MAIN SCREEN
	//-----------------------------------------
	
	function show_intro()
	{
		$skin_list = $this->_get_skinlist();
		
		//-----------------------------------------
		// REBUILD MASTER TEMPLATES
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																			 2 => array( 'code' , 'rebuildmasterhtml'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
													    			
		//-----------------------------------------
		// Attempt to get filemtime
		//-----------------------------------------
		
		$filemtime  = 0;
		$file       = ROOT_PATH . 'resources/ipb_templates.xml';
		$error      = "";
		$notice     = "";
		$extra_html = "";
		
		if ( @file_exists( $file ) )
		{
			if ( $filemtime = @filemtime( $file ) )
			{
				$notice = "resources/ipb_templates.xml 最后更新于: " . $this->ipsclass->get_date( $filemtime, 'JOINED' );
			}
			
			if ( $filemtime2 = @filemtime( ROOT_PATH . 'sources/ipsclass.php' ) )
			{
				if ( ( $filemtime2 - (86400 * 7) ) > $filemtime )
				{
					$error = "请检查 resources/ipb_templates.xml - 'ipsclass.php' 文件更新一点.";
				}
			}
		}
		else
		{
			$error = "找不到文件 '{$file}' - 请确认您上传了这个文";
		}
		
		//-----------------------------------------
		// Got notices?
		//-----------------------------------------
		
		if ( $notice )
		{
			$extra_html .= "<div class='input-ok-content'>$notice</div>";
		}
		
		if ( $error )
		{
			$extra_html .= "<div class='input-warn-content'>$error</div>";
		}
		
		//-----------------------------------------
		// Continue
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "{none}"  , "100%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建主模板" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>运行本工具将会重建您的主 HTML 模板, 所有的主题都是继承自这里.</b>
																			  <br />运行后, 您需要重建主题缓存来更新主题.
																			  $extra_html",
																	)      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// REBUILD MASTER CSS and BOARDWRAPPER
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																			 2 => array( 'code' , 'rebuildmastercomponents'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
													    			
		//-----------------------------------------
		// Attempt to get filemtime
		//-----------------------------------------
		
		$filemtime  = 0;
		$file       = ROOT_PATH . 'resources/skinsets.xml';
		$error      = "";
		$notice     = "";
		$extra_html = "";
		
		if ( @file_exists( $file ) )
		{
			if ( $filemtime = @filemtime( $file ) )
			{
				$notice = "resources/skinsets.xml  最后更新于: " . $this->ipsclass->get_date( $filemtime, 'JOINED' );
			}
			
			if ( $filemtime2 = @filemtime( ROOT_PATH . 'sources/ipsclass.php' ) )
			{
				if ( ( $filemtime2 - (86400 * 7) ) > $filemtime )
				{
					$error = "请检查 resources/skinsets.xml - 'ipsclass.php' 文件更新一点.";
				}
			}
		}
		else
		{
			$error = "Cannot locate '{$file}' - please make sure that the 'install' directory exists.";
		}
		
		//-----------------------------------------
		// Got notices?
		//-----------------------------------------
		
		if ( $notice )
		{
			$extra_html .= "<div class='input-ok-content'>$notice</div>";
		}
		
		if ( $error )
		{
			$extra_html .= "<div class='input-warn-content'>$error</div>";
		}
		
		//-----------------------------------------
		// Continue
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "{none}"  , "100%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建主主题组件" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>运行本工具将会重建您的论坛页面结构和 CSS.</b>
																			  <br />运行后, 您需要重建主题缓存来更新主题.
																			  $extra_html",
																	)      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// REBUILD MASTER MACROS
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																			 2 => array( 'code' , 'rebuildmastermacros'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
													    			
		//-----------------------------------------
		// Attempt to get filemtime
		//-----------------------------------------
		
		$filemtime  = 0;
		$file       = ROOT_PATH . 'resources/macro.xml';
		$error      = "";
		$notice     = "";
		$extra_html = "";
		
		if ( @file_exists( $file ) )
		{
			if ( $filemtime = @filemtime( $file ) )
			{
				$notice = "resources/macro.xml 最后更新于: " . $this->ipsclass->get_date( $filemtime, 'JOINED' );
			}
			
			if ( $filemtime2 = @filemtime( ROOT_PATH . 'sources/ipsclass.php' ) )
			{
				if ( ( $filemtime2 - (86400 * 7) ) > $filemtime )
				{
					$error = "请检查 resources/macro.xml - 'ipsclass.php' 文件更新一点.";
				}
			}
		}
		else
		{
			$error = "Cannot locate '{$file}' - please make sure that the 'install' directory exists.";
		}
		
		//-----------------------------------------
		// Got notices?
		//-----------------------------------------
		
		if ( $notice )
		{
			$extra_html .= "<div class='input-ok-content'>$notice</div>";
		}
		
		if ( $error )
		{
			$extra_html .= "<div class='input-warn-content'>$error</div>";
		}
		
		//-----------------------------------------
		// Continue
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "{none}"  , "100%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建主主题宏" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>运行本工具将会重建您的主宏.</b>
																			  <br />运行后, 您需要重建主题缓存来更新主题.
																			  $extra_html",
																	)      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具l...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// REBUILD CACHES
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
															     			 2 => array( 'code' , 'rebuildcaches'  ),
															     			 4 => array( 'section', $this->ipsclass->section_code ),
													    )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建主题缓存" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>重建主题缓存...</b><br /><span style='color:gray'>本操作将重建所选主题以及所有子主题的模板 HTML, 页面结构, 宏和 CSS 缓存.</span><br />[ <a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=sets&code=rebuildalltemplates'>全部重建</a> ]",
															     $skin_list
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		// CHANGE MEMBERS 
		//-----------------------------------------
		
		$dd_two = str_replace( "select name='set_skin_set_id'", "select name='set_skin_set_id2'", $skin_list );
		$dd_two = str_replace( "<!--DD.OPTIONS-->", "<option value='n'>无 - 使用管理员默认设置</option>", $dd_two );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																			 2 => array( 'code' , 'changemember'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
													   			    )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "更新会员主题选择" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>如果会员使用主题...</b>",
																			 str_replace( "select name='set_skin_set_id'", "select name='set_skin_set_id[]' multiple='multiple' size='6'", $skin_list )
																	)      );
													  
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>将他们的选择重置为...</b>",
															     $dd_two
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																			 2 => array( 'code' , 'changeforum'  ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
													   			    )      );
									     
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "更新版块主题选择" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>如果版块使用主题...</b>",
																			 str_replace( "select name='set_skin_set_id'", "select name='set_skin_set_id[]' multiple='multiple' size='6'", $skin_list )
																	)      );
													  
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>将它们的选择重置到...</b>",
															     $dd_two
													    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();		
		
		//-----------------------------------------
		// REBUILD MASTER
		//-----------------------------------------
		
		if ( IN_DEV )
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																				 2 => array( 'code' , 'rebuildmaster'  ),
																				 4 => array( 'section', $this->ipsclass->section_code ),
																		)      );
											 
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "重建主主题" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>从 CSS 文件和 PHP 文件重建“IPB Master Skin Set.</b><br /><span style='color:gray'>本操作将主主题的重建模板 HTML. 请小心使用!</span>",
																				 "<input type='checkbox' name='phpyes' value='1' /> PHP 缓存目录.: skin_cache/cacheid_ ".$this->ipsclass->adskin->form_simple_input( 'phplocation', '1', 3 )."<br />".
																				 "<input type='checkbox' name='cssyes' value='1' /> CSS 缓存文件: style_images/css_ ".$this->ipsclass->adskin->form_simple_input( 'csslocation', '1',3 )
																		)      );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
			
			//-----------------------------------------
			// Rewrite cache files to directory
			//-----------------------------------------

			$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'act'  , 'skintools'      ),
																     			 2 => array( 'code' , 'rewritemastercache'  ),
																     			 4 => array( 'section', $this->ipsclass->section_code ),
														    )      );

			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );

			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Rewrite cacheid_1 master skins from the DB" );

			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Rebuild cacheid_1 master skins...</b><br /><span style='color:gray'>This option will rewrite all your master cache skin files from the DB.</span>",
														    			)      );

			$this->ipsclass->html .= $this->ipsclass->adskin->end_form("运行工具...");

			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->admin->output();
	
	}
	
	//-----------------------------------------
	// Get dropdown of skin
	//-----------------------------------------
	
	function _get_skinlist( $check_default=0 )
	{
		$skin_sets = array();
		$skin_list = "<select name='set_skin_set_id' class='dropdown'><!--DD.OPTIONS-->";
		
		//-----------------------------------------
		// Get formatted list of skin sets
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'skin_sets', 'order' => 'set_skin_set_parent, set_skin_set_id' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $s = $this->ipsclass->DB->fetch_row() )
		{
			$skin_sets[ $s['set_skin_set_id'] ] = $s;
			$skin_sets[ $s['set_skin_set_parent'] ]['_children'][] = $s['set_skin_set_id'];
		}
		
		//-----------------------------------------
		// Roots
		//-----------------------------------------
		
		foreach( $skin_sets as $id => $data )
		{
			if ( isset($data['set_skin_set_parent']) AND $data['set_skin_set_parent'] < 1 and $id > 1 )
			{
				if( $check_default )
				{
					$default = $data['set_default'] ? " selected='selected'" : '';
				}
				
				$skin_list .= "\n<option value='$id'{$default}>{$data['set_name']}</option><!--CHILDREN:{$id}-->";
			}
		}
		
		//-----------------------------------------
		// Kids...
		//-----------------------------------------
		
		foreach( $skin_sets as $id => $data )
		{	
			if ( isset($data['_children']) AND is_array( $data['_children'] ) and count( $data['_children'] ) > 0 )
			{
				$html = "";
				
				foreach( $data['_children'] as $cid )
				{
					if( $check_default )
					{
						$default = $skin_sets[ $cid ]['set_default'] ? " selected='selected'" : '';
					}
					
					$html .= "\n<option value='$cid'{$default}>---- {$skin_sets[ $cid ]['set_name']}</option>";
				}
				
				$skin_list = str_replace( "<!--CHILDREN:{$id}-->", $html, $skin_list );
			}
		}
		
		$skin_list .= "</select>";
		
		return $skin_list;
	}
	
	//-----------------------------------------
	// Sort by group name
	//-----------------------------------------
	
	function perly_alpha_sort($a, $b)
	{
		return strcmp($a['easy_name'], $b['easy_name']);
	}
	
}


?>