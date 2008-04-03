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
|   > $Date: 2007-04-19 18:08:23 -0400 (Thu, 19 Apr 2007) $
|   > $Revision: 947 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Administration Module
|   > Module written by Matt Mecham
|   > Date started: 27th January 2004
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Mon 24th May 2004
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_banandbadword {

	var $functions = "";
	var $ipsclass;
	var $html;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "content";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "babw";
	
	function auto_run()
	{
		$this->html = $this->ipsclass->acp_load_template('cp_skin_bbcode_badword');
		
		//-----------------------------------------
		// Require and RUN !! THERES A BOMB
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "";
		$this->ipsclass->admin->page_title  = "论坛屏蔽项目管理";

		//-----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
			//-----------------------------------------
			// Badword
			//-----------------------------------------
			
			case 'badword':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->badword_start();
				break;
				
			case 'badword_add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-add' );
				$this->badword_add();
				break;
				
			case 'badword_remove':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-remove' );
				$this->badword_remove();
				break;
				
			case 'badword_edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-edit' );
				$this->badword_edit();
				break;
				
			case 'badword_doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-edit' );
				$this->badword_doedit();
				break;
				
			case 'badword_export':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-export' );
				$this->badword_export();
				break;
				
			case 'badword_import':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':bw-import' );
				$this->badword_import();
				break;
				
			//-----------------------------------------
			// BAN (d-aid)
			//-----------------------------------------
			
			case 'ban':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':ban-view' );
				$this->ban_start();
				break;
			case 'ban_add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':ban-add' );
				$this->ban_add();
				break;
			case 'ban_delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':ban-remove' );
				$this->ban_delete();
				break;
		}
	}
	
 	//-----------------------------------------
	// BAN: Rebuild cache
	//-----------------------------------------
	
	function ban_rebuildcache()
	{
		require_once ROOT_PATH.'sources/classes/bbcode/class_bbcode_core.php';
		
		$this->ipsclass->cache['banfilters'] = array();
			
		$this->ipsclass->DB->simple_construct( array( 'select' => 'ban_content', 'from' => 'banfilters', 'where' => "ban_type='ip'" ) );
		$this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['banfilters'][] = $r['ban_content'];
		}
		
		usort( $this->ipsclass->cache['banfilters'] , array( 'class_bbcode_core', 'word_length_sort' ) );
		
		$this->ipsclass->update_cache( array( 'name' => 'banfilters', 'array' => 1, 'deletefirst' => 1 ) );
	}
	
	//-----------------------------------------
	// BAN: DELETE
	//-----------------------------------------
	
	function ban_delete()
	{
		$ids = array();
		
		foreach ($this->ipsclass->input as $key => $value)
		{
			if ( preg_match( "/^id_(\d+)$/", $key, $match ) )
			{
				if ( $this->ipsclass->input[$match[0]] )
				{
					$ids[] = $match[1];
				}
			}
		}
		
		$ids = $this->ipsclass->clean_int_array( $ids );
		
		if ( count( $ids ) )
		{
			$this->ipsclass->DB->simple_construct( array( 'delete' => 'banfilters', 'where' => 'ban_id IN('.implode( ",",$ids ).')' ) );
			$this->ipsclass->DB->simple_exec();
		}
		
		$this->ban_rebuildcache();
		
		$this->ipsclass->main_msg = "论坛屏蔽项目已删除";
		$this->ban_start();
	}
	
	//-----------------------------------------
	// BAN: ADD
	//-----------------------------------------
	
	function ban_add()
	{
		if ( ! $this->ipsclass->input['bantext'] )
		{
			$this->ipsclass->main_msg = "您必须输入需要屏蔽的内容!";
			$this->ban_start();
		}
		
		//-----------------------------------------
		// Check for existing entry
		//-----------------------------------------
		
		$result = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'banfilters', 'where' => "ban_content='{$this->ipsclass->input['bantext']}' and ban_type='{$this->ipsclass->input['bantype']}'" ) );
		
		if ( $result['ban_id'] )
		{
			$this->ipsclass->main_msg = "重复的项目.";
			$this->ban_start();
		}
		
		$this->ipsclass->DB->do_insert( 'banfilters', array( 'ban_type' => $this->ipsclass->input['bantype'], 'ban_content' => trim($this->ipsclass->input['bantext']), 'ban_date' => time() ) );
		
		$this->ban_rebuildcache();
		
		$this->ipsclass->main_msg = "论坛屏蔽项目已添加";
		
		$this->ban_start();
		
	}
	
	//-----------------------------------------
	// BAN: START
	//-----------------------------------------
	
	function ban_start()
	{
		$this->ipsclass->admin->page_title = "论坛屏蔽项目";
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code.'&code=ban', '论坛屏蔽' );
		
		$this->ipsclass->admin->page_detail = "您可以设置一些需要屏蔽的项目, 例如 IP 地址, 邮件地址, 或者保留会员名. 
										 <br /><strong>您可以使用 * 作为通配符 ( 例如：127.0.*, *@yahoo.com, bannedname* ). </strong>";

		//-----------------------------------------
		// Get things
		//-----------------------------------------
		
		$ban = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'banfilters', 'order' => 'ban_date desc' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$ban[ $r['ban_type'] ][ $r['ban_id'] ] = $r;
		}
		
		//-----------------------------------------
		// SHOW THEM!
		//-----------------------------------------
		
		$add_form = "<div align='center'><form method='post' action='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=ban_add'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='30' class='textinput' value='' name='bantext' />
					   <select class='dropdown' name='bantype'><option value='ip'>IP Address</option><option value='email'>Email Address</option><option value='name'>Name</option></select>
					   <input type='submit' value='Add New Filter' class='realdarkbutton' /></form></div>
					   <form method='post' action='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=ban_delete'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />";

		$this->ipsclass->adskin->td_header[] = array( "{none}"	, "1%" );
		$this->ipsclass->adskin->td_header[] = array( "{none}"	, "80%" );
		$this->ipsclass->adskin->td_header[] = array( "{none}"	, "20%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "论坛屏蔽项目" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( $add_form, "center", "tablesubheader");
		//-----------------------------------------
		// Banned IP Addresses
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("屏蔽的 IP 地址", "left", "tablesubheader");
		
		if ( isset($ban['ip']) AND  is_array( $ban['ip'] ) AND count( $ban['ip'] ) )
		{
			foreach ( $ban['ip'] as $entry )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<input type='checkbox' name='id_{$entry['ban_id']}' value='1' />",
																		 $entry['ban_content'],
																	 	 $this->ipsclass->get_date( $entry['ban_date'], 'SHORT' ),
																)      );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("没有需要屏蔽的 IP 地址", "left", "tablerow1");
		}
		
		//-----------------------------------------
		// Banned Email Addresses
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("屏蔽的邮件地址", "left", "tablesubheader");
		
		if ( isset($ban['email']) AND  is_array( $ban['email'] ) AND count( $ban['email'] ) )
		{
			foreach ( $ban['email'] as $entry )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<input type='checkbox' name='id_{$entry['ban_id']}' value='1' />",
																		 $entry['ban_content'],
																	 	 $this->ipsclass->get_date( $entry['ban_date'], 'SHORT' ),
																)      );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("没有需要屏蔽的邮件地址", "left", "tablerow1");
		}
		
		//-----------------------------------------
		// Banned Names
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("不可注册的会员名", "left", "tablesubheader");
		
		if ( isset($ban['name']) AND is_array( $ban['name'] ) AND count( $ban['name'] ) )
		{
			foreach ( $ban['name'] as $entry )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<input type='checkbox' name='id_{$entry['ban_id']}' value='1' />",
																		 $entry['ban_content'],
																	 	 $this->ipsclass->get_date( $entry['ban_date'], 'SHORT' ),
																)      );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("没有会员名", "left", "tablerow1");
		}
		
		$end_it_now = "<div align='left' style='float:left;width:auto;'>
		 			   <input type='submit' value='删除选中的屏蔽项目' class='realdarkbutton' />
					   </div></form>";
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( $end_it_now, "center", "tablesubheader");									 
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();

		
	}
	
	//-----------------------------------------
	// BADWORDS: Import
	//-----------------------------------------
	
	function badword_import()
	{
		$content = $this->ipsclass->admin->import_xml( 'ipb_badwords.xml' );
		
		//-----------------------------------------
		// Got anything?
		//-----------------------------------------
		
		if ( ! $content )
		{
			$this->ipsclass->main_msg = "上传失败, 找不到 ipb_badwords.xml 或文件为空";
			$this->badword_start();
			return;
		}
		
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Unpack the datafile
		//-----------------------------------------
		
		$xml->xml_parse_document( $content );
		
		if( !is_array($xml->xml_array['badwordexport']['badwordgroup']['badword']) OR !count($xml->xml_array['badwordexport']['badwordgroup']['badword']) )
		{
			$this->ipsclass->main_msg = "XML 导入失败: pb_badwords.xml 为空或者格式错误";
			$this->badword_start();
			return;
		}
		
		//-----------------------------------------
		// Get current badwords
		//-----------------------------------------
		
		$words = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'badwords', 'order' => 'type' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$words[ $r['type'] ] = 1;
		}
		
		//-----------------------------------------
		// pArse
		//-----------------------------------------
		
		foreach( $xml->xml_array['badwordexport']['badwordgroup']['badword'] as $entry )
		{
			$type    = $entry['type']['VALUE'];
			$swop    = $entry['swop']['VALUE'];
			$m_exact = $entry['m_exact']['VALUE'];
			
			if ( $words[ $type ] )
			{
				continue;
			}
			
			if ( $type )
			{
				$this->ipsclass->DB->do_insert( 'badwords', array( 'type' => $type, 'swop' => $swop, 'm_exact' => $m_exact ) );
			}
		}
		
		$this->badword_rebuildcache();
                    
		$this->ipsclass->main_msg = "屏蔽词 XML 文件导入完成";
		
		$this->badword_start();
	
	}
	
	//-----------------------------------------
	// BADWORD: Export
	//-----------------------------------------
	
	function badword_export()
	{
		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------
		
		require_once( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();
		
		//-----------------------------------------
		// Start...
		//-----------------------------------------
		
		$xml->xml_set_root( 'badwordexport', array( 'exported' => time() ) );
		
		//-----------------------------------------
		// Get emo group
		//-----------------------------------------
		
		$xml->xml_add_group( 'badwordgroup' );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'type, swop, m_exact', 'from' => 'badwords', 'order' => 'type' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$content = array();
			
			foreach ( $r as $k => $v )
			{
				$content[] = $xml->xml_build_simple_tag( $k, $v );
			}
			
			$entry[] = $xml->xml_build_entry( 'badword', $content );
		}
		
		$xml->xml_add_entry_to_group( 'badwordgroup', $entry );
		
		$xml->xml_format_document();
		
		//-----------------------------------------
		// Send to browser.
		//-----------------------------------------
		
		$this->ipsclass->admin->show_download( $xml->xml_document, 'ipb_badwords.xml' );
	}
	
	//-----------------------------------------
	// BADWORD: Start
	//-----------------------------------------
	
	function badword_start()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$badword_html = "";
			
		$this->ipsclass->admin->page_detail = "您可以在这里设置忌语过滤列表. <br>忌语过滤将会在帖子、签名以及主题标题中自动转换指定的内容. <br><br><b>模糊匹配</b>：如果您输入“hell”作为需要过滤的内容, 那么系统会自动替换“hell”和“hello”到您指定的内容或者 6 个星号（大小写无关）, <br><br><b>精确匹配</b>：如果您输入“hell”作为需要过滤的内容, 那么系统将只替换“hell”到您指定的内容或者 6 个星号（大小写无关）. ";
		$this->ipsclass->admin->page_title  = "忌语过滤";
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code.'&code=badword', 'Bad Word Filters' );
		
		//-----------------------------------------
		// Start form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'badword_add' ),
												  			     		  	 2 => array( 'act'   , 'babw'       ),
															  			     4 => array( 'section', $this->ipsclass->section_code ),
												     			  )      );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'badwords', 'order' => 'type' ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$words[] = $r;
			}
			
			foreach($words as $r)
			{
				$r['replace'] = $r['swop']    ? stripslashes($r['swop']) : '######';
				$r['method']  = $r['m_exact'] ? '精确' : '模糊';
				$r['type'] 	= stripslashes($r['type']);
				
				$badword_html .= $this->html->badword_row( $r );
			}
			
		}
		
		$this->ipsclass->html .= $this->html->badword_wrapper( $badword_html );
		
		//-----------------------------------------
		// IMPORT: Start table
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;" , "40%" );
		
		//-----------------------------------------
		// IMPORT: Start output
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'badword_import' ),
															   2 => array( 'act'   , 'babw'      ),
															   3 => array( 'MAX_FILE_SIZE', '10000000000' ),
															   4 => array( 'section', $this->ipsclass->section_code ),
													  ) , "uploadform", " enctype='multipart/form-data'"     );
													
													  			
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "导入屏蔽词列表" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
													 		 "<b>上传一个 XML 忌语列表</b><div style='color:gray'>文件名必须是“ipb_badwords.xml”或“ipb_badwords.xml.gz”. 重复的项目不会被导入. </div>",
													  		$this->ipsclass->adskin->form_upload(  )
													   )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("导入");
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	//-----------------------------------------
	// BADWORD: Complete Edit
	//-----------------------------------------
	
	function badword_doedit()
	{
		if ($this->ipsclass->input['before'] == "")
		{
			$this->ipsclass->admin->error("您必须输入一个需要替换掉的词汇!");
		}
		
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("You must pass a valid filter id, silly!");
		}
		
		$this->ipsclass->input['match'] = $this->ipsclass->input['match'] ? 1 : 0;
		
		strlen($this->ipsclass->input['swop']) > 1 ?  $this->ipsclass->input['swop'] : "";
		
		$this->ipsclass->DB->do_update( 'badwords', array( 'type'    => trim($this->ipsclass->input['before']),
										   'swop'    => trim($this->ipsclass->input['after']),
										   'm_exact' => $this->ipsclass->input['match'],
								  ), "wid='".$this->ipsclass->input['id']."'"  );
												  
		$this->badword_rebuildcache();
		
		$this->ipsclass->main_msg = "屏蔽词已编辑";
		
		$this->badword_start();
	}
	
	//-----------------------------------------
	// BADWORD:  Edit
	//-----------------------------------------
	
	function badword_edit()
	{
		$this->ipsclass->admin->page_detail = "您可以编辑下面的忌语";
		$this->ipsclass->admin->page_title  = "屏蔽关键字过滤";
		$this->ipsclass->admin->nav[] 		= array( $this->ipsclass->form_code.'&code=badword', 'Bad Word Filters' );
		$this->ipsclass->admin->nav[] 		= array( '', '编辑关键字过滤' );
		
		//-----------------------------------------
		
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("You must pass a valid filter id, silly!");
		}
		
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'badwords', 'where' => "wid='".$this->ipsclass->input['id']."'" ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( ! $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("We could not find that filter in the database");
		}
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'badword_doedit' ),
												 			     2 => array( 'act'   , 'babw'     ),
												  			     3 => array( 'id'    , $this->ipsclass->input['id'] ),
												  			     4 => array( 'section', $this->ipsclass->section_code ),
									                    )      );
		
		
		
		$this->ipsclass->adskin->td_header[] = array( "词汇"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "替换为"   , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "替换方式"  , "20%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "编辑关键词过滤" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $this->ipsclass->adskin->form_input('before', stripslashes($r['type']) ),
												  			     $this->ipsclass->adskin->form_input('after' , stripslashes($r['swop']) ),
												  			     $this->ipsclass->adskin->form_dropdown( 'match', array( 0 => array( 1, '精确'  ), 1 => array( 0, '模糊' ) ), $r['m_exact'] )
													    )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('保存编辑');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	
	}
	
	//-----------------------------------------
	// BADWORD: Remove badowrd
	//-----------------------------------------
	
	function badword_remove()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("You must pass a valid filter id, silly!");
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'badwords', 'where' => "wid='".$this->ipsclass->input['id']."'" ) );
		
		$this->badword_rebuildcache();
		
		$this->ipsclass->main_msg = "关键词已删除";
		
		$this->badword_start();
		return;
	}
	
	//-----------------------------------------
	// BADWORD: Add badword
	//-----------------------------------------
	
	function badword_add()
	{
		if ($this->ipsclass->input['before'] == "")
		{
			$this->ipsclass->admin->error("您必须输入一个需要替换掉的词汇!");
		}
		
		$this->ipsclass->input['match'] = $this->ipsclass->input['match'] ? 1 : 0;
		
		strlen($this->ipsclass->input['swop']) > 1 ?  $this->ipsclass->input['swop'] : "";
		
		$this->ipsclass->DB->do_insert( 'badwords', array( 'type'    => trim($this->ipsclass->input['before']),
														   'swop'    => trim($this->ipsclass->input['after']),
														   'm_exact' => $this->ipsclass->input['match'],
												  )      );
		
		$this->badword_rebuildcache();
		
		$this->ipsclass->main_msg = "关键词已添加";
		
		$this->badword_start();
	}
	
	//-----------------------------------------
	// BADWORD Rebuild Cache
	//-----------------------------------------
	
	function badword_rebuildcache()
	{
		require_once ROOT_PATH.'sources/classes/bbcode/class_bbcode_core.php';
		
		$this->ipsclass->cache['badwords'] = array();
			
		$this->ipsclass->DB->simple_construct( array( 'select' => 'type,swop,m_exact', 'from' => 'badwords' ) );
		$this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['badwords'][] = $r;
		}
		
		usort( $this->ipsclass->cache['emoticons'] , array( 'class_bbcode_core', 'word_length_sort' ) );
		
		$this->ipsclass->update_cache( array( 'name' => 'badwords', 'array' => 1, 'deletefirst' => 1 ) );
	}
	

}


?>