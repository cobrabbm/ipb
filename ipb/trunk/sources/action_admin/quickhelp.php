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
|   > $Date: 2006-09-22 06:28:31 -0400 (Fri, 22 Sep 2006) $
|   > $Revision: 567 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Admin Quick Help System
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
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


class ad_quickhelp {

	var $help_text = array();
	
	function init_help_array()
	{
	
		return array(	'mg_dohtml' => array( 'title' => "发表 HTML",
											  'body'  => "这将允许该用户组的全体会员在可以使用 HTML 代码的论坛中发布包含 HTML 的帖子. 您可以在每个单独的论坛设置关闭这一权限. 请注意: 当没有发布 HTML 帖子权限的会员引用包含 HTML 代码的帖子时, 该帖子将会以纯 HTML 代码形式出现而大部分格式将不会解析.<br />
											  			  <br /><b>警告!</b><br />
											  			  允许会员发布包含 HTML 代码的主题是非常危险的, 如果您不能保证您的会员完全没有恶意请不要开启这一权限. 虽然易维论坛对于可能包含有害代码的帖子进了过滤, 但是任何的过滤都不能保证万无一失, 一些有害的代码将有可能通过 Cookies 获取会员权限, 更改会员资料或者造成版式错误.
											  			  <br />对于有此所造成的损失官方无法提供有效的防护因而也无法对其负责.
											  			  <br /><br /><b>请谨慎使用!</b>
														 ",
											 ),
											 
						'mod_mmod' =>  array( 'title' => "批量主题管理",
											  'body'  => "如果您允许版主使用批量主题管理功能, 请您确保他们会正确的使用.
											  			  <br />例如, 如果您的批量主题管理允许进行移动操作而不论您是否开启了版主的移动操作权限他们都将可以进行这一操作.
														 ",
											 ),
											 
						'set_spider' => array( 'title' => "搜索蜘蛛是什么?",
											  'body'  => "搜索引擎就是类似 Google 这样的网站会使用特殊的抓取技术来 '爬网' .<br />易维论坛采用特殊的技术来区别搜索蜘蛛和普通的访问者, 同时您也可以通过相关的设置引导搜索蜘蛛正确的抓取页面来获得较好的排名.
											  			  <br />
											  			  <br />
											  			  <b>警告!</b>
											  			  <br />
											  			  易维论坛可以通过它们发送的请求的客户端识别搜索蜘蛛. 但是请注意进行伪造并非不可能因为一个恶意用户很可能伪造请求来假冒搜索蜘蛛.
											  			  <br />当搜索蜘蛛只能使用默认的游客权限的时候这并不是一个问题, 但是您必须注意当您赋予更高权限的时候可能造成的安全问题.
											  			  <br /><br />另外也请注意大多数搜索引擎并不会去抓取动态生成的内容以防在阅读主题的过程中给服务器带来过重的负担.
														 ",
											 ),
		
		
		
						'mg_upload' => array( 'title' => "上传权限",
											  'body'  => "如果您希望开启这一用户组的上传权限, 您必须完整填写以下表单:
											  			  <ul>
											  			  <li>您在 '全局上传空间' 正确填写了一个合法的数值.
											  			  <li>您在相应的论坛开启了该用户组的上传权限.
											  			  </ul>
											  			  这将允许您限制用户组可以上传的论坛.
														  <br /><br /><b>警告!</b><br />如果在发帖的时候, 没有发布帖子内容并且跳转到论坛首页, 请通过输入 0 来关闭该用户组的上传权限. 这将关闭上传所使用的动态表单.
														 ",
											 ),
		
		
						'mg_promote' => array( 'title' => "用户组提升",
											   'body'  => "如果开启 (通过选择提升后的用户组来以及填写会员获得提升相应的主题数目)
											    		   当您的会员达到或者超过该数目以后将会自动提升到您所设置的用户组.
											    		   <br /><br />
											    		   大多数管理员使用这一功能来提升会员到 '高级会员' 用户组来获得更多的权限 (比如更长的修改时限、可以发布更长的主题以及更大的上传空间等等) 您也可以通过这一权限的设置来对于新注册会员隐藏一些论坛使得他们在发布足够的主题后才能查看.
											    		   <br /><br /><b>警告!</b><br />请谨慎填写并且仔细检查您的设置.<br />这一设置将有可能影响到管理员用户组.
											   			  ",
											 ),
						's_reg_antispam' => array ( 'title' => "防自动注册功能",
													'body'  => "为了阻止自动注册机器人的恶意注册 (例如通过注册来发布垃圾广告帖或者向论坛会员发送垃圾邮件等)
													            您可以开启这一选项.
													            <br /><br />一旦开启, 注册时访问者将被要求填写一个自动生成的 6 位数字或字母. 如果不能正确填写访问者将无法完成注册.",
											 ),
											 
						'm_bulkemail'    => array ( 'title' => "群发邮件",
												    'body' => "<b>系统概览</b><br />群发邮件允许您给您所有的论坛会员发送邮件通知.
												    <br /><br /><b>系统设置</b><br />您可以通过选择用户组和设置忽略 '允许管理员给我发送邮件' 功能来发送邮件. 然而我们建议您不要忽略这一选项.<hr>
												    <b>开启标签</b><br />虽然系统通过分批次延迟发送的方式发送邮件来减少服务器的负载, 您同样可以使用以下的一些标签来添加到邮件正文中.
												    <br />{board_name} 将会返回论坛名称
													<br />{reg_total} 将会返回论坛总计注册会员数
													<br />{total_posts} 将会返回总计发贴数
													<br />{busy_count} 将会返回最高在线人数
													<br />{busy_time} 将会返回最高在线人数的时间
													<br />{board_url} 将会返回论坛 URL 地址
													<br /><br />由于是延迟发送的方式, 在邮件中显示用户名称、密码和其他的一些个人信息不太可能.",
												),
						'comp_menu' => array ( 'title' => "论坛组件系统",
											   'body'  => "<strong>菜单项目</strong> 表示菜单实际的项目名称. <em>例如: 工具设置.</em><br />
   														   <strong>菜单地址</strong> 表示菜单 URL 地址中的最后一部分. 无需填写完整的 URL 地址或者类似 'section=components&act=blog' 的部分除非需要跳转. <em>例如: code={code}</em><br />
														   <strong>菜单跳转</strong> 如果开启, 请完整填写 'act={}&section={}&code={}' 的 URL 地址部分否则无法指向正确的地址.<br />
														   <strong>菜单固定项和语言</strong> 是后台限制菜单项. 条目: 添加、删除、编辑、重建、重计数和刷新语言文件缓存. 并且项目名称也必须添加进语言项中. 例如: 您的语言项为 'tools', 您可以输入 '开启工具权限' 作为语言项内容.<br />",
											 ),
					);
	
	}

	function auto_run()
	{
		$id = $this->ipsclass->input['id'];
		
		$this->help_text = $this->init_help_array();
		
		if ($this->help_text[$id]['title'] == "")
		{
			$this->ipsclass->admin->error("当前尚无任何该项的快速帮助信息");
		}
		
		print "<html>
				<head>
				 <title>快速帮助</title>
				</head>
				<body leftmargin='0' topmargin='0' bgcolor='#F5F9FD'>
				 <table width='95%' align='center' border='0' cellpadding='6'>
				 <tr>
				  <td style='font-family:verdana, arial, tahoma;color:#4C77B6;font-size:16px;letter-spacing:-1px;font-weight:bold'>{$this->help_text[$id]['title']}</td>
				 </tr>
				 <tr>
				  <td style='font-family:verdana, arial, tahoma;color:black;font-size:9pt'>{$this->help_text[$id]['body']}</td>
				 </tr>
				 </table>
				</body>
				</html>";
		
		
		exit();
		
	}
	
	
	
}


?>