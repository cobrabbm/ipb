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
|   > $Date: 2007-01-11 17:33:01 -0500 (Thu, 11 Jan 2007) $
|   > $Revision: 826 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Custom profile field functions
|   > Module written by Matt Mecham
|   > Date started: 24th June 2002
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


class ad_profilefields {

	var $base_url;
	var $func;
	
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
	var $perm_child = "field";
	
	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '会员附加信息' );
		
		//-----------------------------------------
		// get class
		//-----------------------------------------
		
		require_once( ROOT_PATH.'sources/classes/class_custom_fields.php' );
		$this->func = new custom_fields( $DB );
		
		//-----------------------------------------
		// switch-a-magoo
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
			case 'add':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':add' );
				$this->main_form('add');
				break;
				
			case 'doadd':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':add' );
				$this->main_save('add');
				break;
				
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->main_form('edit');
				break;
				
			case 'doedit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':edit' );
				$this->main_save('edit');
				break;
				
			case 'delete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':remove' );
				$this->delete_form();
				break;
				
			case 'dodelete':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':remove' );
				$this->do_delete();
				break;
						
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
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
		$this->ipsclass->cache['profilefields'] = array();
				
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'order' => 'pf_position' ) );
						 
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['profilefields'][ $r['pf_id'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'profilefields', 'array' => 1, 'deletefirst' => 1 ) );	
	}
	
	//-----------------------------------------
	//
	// Delete a group
	//
	//-----------------------------------------
	
	function delete_form()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("无法处理用户组 ID, 请重试");
		}
		
		$this->ipsclass->admin->page_title = "删除会员附加信息";
		
		$this->ipsclass->admin->page_detail = "请确认您正试图删除会员附加信息, <b>所有的数据将丢失!</b>.";
		
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'where' => "pf_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( ! $field = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("无法匹配数据库的数据行");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
																 2 => array( 'act'   , 'field'     ),
																 3 => array( 'id'    , $this->ipsclass->input['id']   ),
																 4 => array( 'section', $this->ipsclass->section_code ),
														)      );
									     
		
		
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "删除确认" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>会员附加信息已删除</b>" ,
												                 "<b>".$field['pf_title']."</b>",
									                   )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("删除");
										 
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
		// Check to make sure that the relevant groups exist.
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'where' => "pf_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( ! $row = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->admin->error("Could not resolve the ID's passed to deletion");
		}
		
		$this->ipsclass->DB->sql_drop_field( 'pfields_content', "field_{$row['pf_id']}" );
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'pfields_data', 'where' => "pf_id=".intval($this->ipsclass->input['id']) ) );
		
		$this->rebuild_cache();
		
		$this->ipsclass->admin->done_screen("附加信息已删除", "会员附加信息管理", "{$this->ipsclass->form_code}", 'redirect' );
		
	}
	
	
	//-----------------------------------------
	//
	// Save changes to DB
	//
	//-----------------------------------------
	
	function main_save($type='edit')
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		
		if ($this->ipsclass->input['pf_title'] == "")
		{
			$this->ipsclass->admin->error("您必须输入信息标题.");
		}
		
		//-----------------------------------------
		// check-da-motcha
		//-----------------------------------------
		
		if ($type == 'edit')
		{
			if ($this->ipsclass->input['id'] == "")
			{
				$this->ipsclass->admin->error("Could not resolve the field id");
			}
			
		}
		
		$content = "";
		
		if ( $_POST['pf_content'] != "")
		{
			$content = $this->func->method_format_content_for_save( $_POST['pf_content'] );
		}
		
		$db_string = array( 'pf_title'        => $this->ipsclass->input['pf_title'],
						    'pf_desc'         => $this->ipsclass->input['pf_desc'],
						    'pf_content'      => $this->ipsclass->txt_stripslashes($content),
						    'pf_type'         => $this->ipsclass->input['pf_type'],
						    'pf_not_null'     => intval($this->ipsclass->input['pf_not_null']),
						    'pf_member_hide'  => intval($this->ipsclass->input['pf_member_hide']),
						    'pf_max_input'    => intval($this->ipsclass->input['pf_max_input']),
						    'pf_member_edit'  => intval($this->ipsclass->input['pf_member_edit']),
						    'pf_position'     => intval($this->ipsclass->input['pf_position']),
						    'pf_show_on_reg'  => intval($this->ipsclass->input['pf_show_on_reg']),
						    'pf_input_format' => $this->ipsclass->input['pf_input_format'],
						    'pf_admin_only'   => intval($this->ipsclass->input['pf_admin_only']),
						    'pf_topic_format' => $this->ipsclass->txt_stripslashes( $_POST['pf_topic_format']),
						  );
		
						  
		if ($type == 'edit')
		{
			$this->ipsclass->DB->do_update( 'pfields_data', $db_string, 'pf_id='.$this->ipsclass->input['id'] );
			
			$this->rebuild_cache();
			
			$this->ipsclass->main_msg = "附加信息已编辑";
			$this->main_screen();
			
		}
		else
		{
			$this->ipsclass->DB->do_insert( 'pfields_data', $db_string );
			
			$new_id = $this->ipsclass->DB->get_insert_id();
			
			$this->ipsclass->DB->sql_add_field( 'pfields_content', "field_$new_id", 'text' );
			
			$this->ipsclass->DB->sql_optimize_table( 'pfields_content' );
			
			$this->rebuild_cache();
			
			$this->ipsclass->main_msg = "附加信息已添加";
			$this->main_screen();
		}
	}
	
	
	//-----------------------------------------
	//
	// Add / edit group
	//
	//-----------------------------------------
	
	function main_form($type='edit')
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		$this->ipsclass->admin->nav[] = array( '', '添加/编辑会员附加信息' );
		
		if ($type == 'edit')
		{
			if ( ! $this->ipsclass->input['id'] )
			{
				$this->ipsclass->admin->error("No group id to select from the database, please try again.");
			}
			
			$form_code = 'doedit';
			$button    = '保存修改';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = '添加信息';
		}
		
		//-----------------------------------------
		// get field from db
		//-----------------------------------------
		
		if ( $this->ipsclass->input['id'] )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'where' => "pf_id=".intval($this->ipsclass->input['id']) ) );
			$this->ipsclass->DB->simple_exec();
		
			$fields = $this->ipsclass->DB->fetch_row();
		}
		else
		{
			$fields = array( 'pf_topic_format' => '{title}: {content}<br />' );
		}
		
		//-----------------------------------------
		// Top 'o 'the mornin'
		//-----------------------------------------
		
		if ($type == 'edit')
		{
			$this->ipsclass->admin->page_title = "编辑附加信息 ".$fields['pf_title'];
		}
		else
		{
			$this->ipsclass->admin->page_title = '添加附加信息';
			$fields = array( 'pf_title'			=> '',
							 'pf_content'		=> '',
							 'pf_desc'			=> '',
							 'pf_type'			=> '',
							 'pf_max_input'		=> '',
							 'pf_position'		=> '',
							 'pf_input_format' 	=> '',
							 'pf_topic_format'	=> '',
							 'pf_show_on_reg'	=> '',
							 'pf_not_null'		=> '',
							 'pf_member_edit'	=> '',
							 'pf_member_hide'	=> '',
							 'pf_admin_only'	=> '' );
		}
		
		//-----------------------------------------
		// Wise words
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "提交表单前请仔细检查下面的信息.";
		
		//-----------------------------------------
		// Start form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , $form_code  ),
												                 2 => array( 'act'   , 'field'     ),
												                 3 => array( 'id'    , $this->ipsclass->input['id']   ),
												                 4 => array( 'section', $this->ipsclass->section_code ),
									                    )     );
		
		//-----------------------------------------
		// Format...
		//-----------------------------------------
									     
		$fields['pf_content'] = $this->func->method_format_content_for_edit($fields['pf_content'] );
		
		//-----------------------------------------
		// Tbl (no ae?)
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "信息设置" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>信息标题</b><div class='graytext'>最大字符数: 200</div>" ,
												                 $this->ipsclass->adskin->form_input("pf_title", $fields['pf_title'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>信息描述</b><div class='graytext'>最大字符: 250<br />可以用来提示会员这是必填信息或者私有信息</div>" ,
												                 $this->ipsclass->adskin->form_input("pf_desc", $fields['pf_desc'] )
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>信息类型</b>" ,
																 $this->ipsclass->adskin->form_dropdown("pf_type",
																					  array(
																							   0 => array( 'text' , '文本框' ),
																							   1 => array( 'drop' , '下拉框' ),
																							   2 => array( 'area' , '多行文本' ),
																						   ),
																					  $fields['pf_type'] )
														)      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>最大输入字符数</b><div class='graytext'>用于文本框和多行文本</div>" ,
												                 $this->ipsclass->adskin->form_input("pf_max_input", $fields['pf_max_input'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>显示顺序</b><div class='graytext'>编辑和显示时的顺序 ( 数字 1 表示最后面 )</div>" ,
												                 $this->ipsclass->adskin->form_input("pf_position", $fields['pf_position'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>输入格式</b><div class='graytext'>表示字母: <b>a</b><br />表示数字: <b>n</b>.<br />例如, 信用卡号码的格式为: nnnn-nnnn-nnnn-nnnn<br />例如, 生日的格式为: nn-nn-nnnn<br />留空表示接受任何输入的字符</div>" ,
												                 $this->ipsclass->adskin->form_input("pf_input_format", $fields['pf_input_format'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>可选内容（用于下拉框）</b><div class='graytext'>每行一项内容<br />例如, 性别信息：<br />m=男<br />f=女<br />u=保密<br />显示效果为：<br /><select name='pants'><option value='m'>男</option><option value='f'>女</option><option value='u'>保密</option></select><br />m、f 或 u 保存在数据库. 而在资料中显示时, 将使用等号后面的内容（f=女, 显示“女”）</div>" ,
												                 $this->ipsclass->adskin->form_textarea("pf_content", $fields['pf_content'] )
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>显示在注册页面?</b><div class='graytext'>如果是, 此信息将显示在注册页面.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("pf_show_on_reg", $fields["pf_show_on_reg"] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>必须填写信息, 不能留空?</b><div class='graytext'>如果是, 如果没有填写此信息, 将会有错误提示.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("pf_not_null", $fields['pf_not_null'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>会员可以自己编辑信息?</b><div class='graytext'>如果否, 会员不能编辑自己的信息, 只有管理员和总版主可以.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("pf_member_edit", $fields['pf_member_edit'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>私有信息?</b><div class='graytext'>如果是, 只有会员本人以及管理员和总版主可以看到信息, 如果否, 会员可以看到其他人的信息.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("pf_member_hide", $fields['pf_member_hide'] )
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>仅管理员和总版主可以查看/编辑?</b><div class='graytext'>如果是, 将会覆盖上面的设置, 只有管理员和总版主可以查看或编辑本信息.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("pf_admin_only", $fields['pf_admin_only'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>主题查看格式</b><div class='graytext'>如果您不想在主题旁的作者栏显示本信息, 请留空. <br />{title} 表示信息标题, {content} 表示会员添加的内容. {key} 表示下拉框所选内容的值. <br />例如: {title}:{content}&lt;br /&gt;<br />例如: {title}:&lt;img src='imgs/{key}'&gt;</div>" ,
												                 $this->ipsclass->adskin->form_textarea("pf_topic_format", $fields['pf_topic_format'] )
									                    )      );					     							     
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form($button);
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
			
			
	}

	//-----------------------------------------
	//
	// Show "Management Screen
	//
	//-----------------------------------------
	
	function main_screen()
	{
		$this->ipsclass->admin->page_title   = "会员附加信息";
		
		$this->ipsclass->admin->page_detail  = "会员附加信息可以在会员资料中添加可选或必填的信息. 如果您想要记录的会员数据 IPB 没有内置, 这里可以为您实现. ";
		
		$this->ipsclass->adskin->td_header[] = array( "信息标题"		, "20%" );
		$this->ipsclass->adskin->td_header[] = array( "信息类型"		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "必填信息"		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "私有信息"		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "注册时显示"		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "仅管理员可见"	, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "编辑"			, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "删除"			, "10%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "会员附加信息管理" );
		
		$real_types = array( 'drop' => '下拉框',
							 'area' => '多行文本',
							 'text' => '文本框',
						   );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'order' => 'pf_position' ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
			
				$hide   = '&nbsp;';
				$req    = '&nbsp;';
				$regi   = '&nbsp;';
				$admin  = '&nbsp;';
				
				//-----------------------------------------
				// Hidden?
				//-----------------------------------------
				
				if ($r['pf_member_hide'] == 1)
				{
					$hide = '<center><span style="color:red">是</span></center>';
				}
				
				//-----------------------------------------
				// Required?
				//-----------------------------------------
				
				if ($r['pf_not_null'] == 1)
				{
					$req = '<center><span style="color:red">是</span></center>';
				}
				
				//-----------------------------------------
				// Show on reg?
				//-----------------------------------------
				
				if ($r['pf_show_on_reg'] == 1)
				{
					$regi = '<center><span style="color:red">是</span></center>';
				}
				
				//-----------------------------------------
				// Admin only...
				//-----------------------------------------
				
				if ($r['pf_admin_only'] == 1)
				{
					$admin = '<center><span style="color:red">是</span></center>';
				}
				
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$r['pf_title']}</b><div class='graytext'>{$r['pf_desc']}</div>" ,
																		 "<center>{$real_types[$r['pf_type']]}</center>",
																		 $req,
																		 $hide,
																		 $regi,
																		 $admin,
																		 "<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&id=".$r['pf_id']."'>编辑</a></center>",
																		 "<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=delete&id=".$r['pf_id']."'>删除</a></center>",
															)      );
											 
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("没有找到记录", "center", "tablerow1");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<div class='fauxbutton-wrapper'><span class='fauxbutton'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=add'>添加信息</a></span></div>", 'center', 'tablefooter' );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		$this->ipsclass->admin->output();
		
		
	}
}


?>