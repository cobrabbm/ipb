<?php
//===========================================================================
// Simple library that holds all the links for the admin cp
// Invision Power Board
//===========================================================================

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url
// $PAGES[ $cat_id ][$page_id][2] = Look for folder before showing
// $PAGES[ $cat_id ][$page_id][3] = URL type: 1 = Board URL 0 = ACP URL
// $PAGES[ $cat_id ][$page_id][4] = Item icon: 1= redirect 0 = Normal

$PAGES = array(
				# SETTINGS
				
				100 => array (
							
							 1 => array( '查看所有全局设置', 'act=op' ),
							 2 => array( '添加新的全局设置'  , 'act=op&code=settingnew' ),
							 //7 => array( '打开 / 关闭论坛'      , 'act=op&code=findsetting&key='.urlencode('boardoffline/online'), '', 0, 1 ),
							 7 => array( '打开 / 关闭论坛'      , 'act=op&code=findsetting&key=boardoffline'), '', 0, 1 ),
							 8 => array( '论坛规则设置'         , 'act=op&code=findsetting&key='.urlencode('boardguidelines'), '', 0, 1 ),
							 //9 => array( '论坛全局设置'    , 'act=op&code=findsetting&key='.urlencode('generalconfiguration'), '', 0, 1 ),
							 9 => array( '论坛全局设置'    , 'act=op&code=findsetting&key=general', '', 0, 1 ),
							 10 => array( 'CPU 降压设置'              , 'act=op&code=findsetting&key='.urlencode('cpusaving&optimization'), '', 0, 1 ),
							 11 => array( 'IP 聊天室插件'                 , 'act=pin&code=ipchat'  ),
							 12 => array( 'IPB 许可证'             , 'act=pin&code=reg'     ),
							 14 => array( 'IPB 版权移除工具'   , 'act=pin&code=copy'    ),
						   ),
						   
			    # MEMBER MANAGEMENT
						   
				200 => array (
							 1 => array( '新建论坛'             , 'act=forum&code=new'       ),
							 2 => array( '管理论坛'         , 'act=forum'                ),
							 3 => array( '权限设置'      , 'act=group&code=permsplash'),
							 6 => array( '论坛版主'            , 'act=mod'                  ),
							 7 => array( '主题批量管理', 'act=multimod'          ),
							 //8 => array( '回收站设置'      , 'act=op&code=findsetting&key=trashcanset-up', '', 0, 1 ),
							 8 => array( '回收站设置'      , 'act=op&code=findsetting&key=trashcansetup', '', 0, 1 ),
						   ),
						   
						   
				300 => array (
				            1  => array ( '管理会员'        , 'act=mem&code=search' ),
							2  => array ( '添加新会员'        , 'act=mem&code=add'  ),
							6  => array ( '管理队列'          , 'act=mem&code=title'),
							7  => array ( '管理用户组'    , 'act=group'         ),
							8  => array ( '管理认证会员'     , 'act=mem&code=mod'  ),
							9  => array ( '自定义资料项' , 'act=field'         ),
							11 => array ( 'IP 会员工具'       , 'act=mtools'        ),
							12 => array ( '会员设置'       , 'act=op&code=findsetting&key=userprofiles', '', 0, 1 ),
						   ),
				
					   
				400 => array(
							 1 => array( '管理支付网关'   , 'act=msubs&code=index-gateways' ),
							 2 => array( '管理订阅包裹'           , 'act=msubs&code=index-packages' ),
							 3 => array( '管理交易记录'       , 'act=msubs&code=index-tools' ),
							 4 => array( '管理交易货币'         , 'act=msubs&code=currency' ,  ),
							 5 => array( '手动添加交易'  , 'act=msubs&code=addtransaction' ),
							 6 => array( '安装支付网关'  , 'act=msubs&code=install-index' ),
							 //9 => array( '付费订阅设置'     , 'act=op&code=findsetting&key='.urlencode('subscriptionsmanager'), '', 0, 1 ),
							 9 => array( '付费订阅设置'     , 'act=op&code=findsetting&key=subsmanager', '', 0, 1 ),
						   ),
				
				# POST MANAGEMENT
				
				500 => array (
							1 => array( '论坛附件类型'      , 'act=attach&code=types'  ),
							2 => array( '论坛附件统计'      , 'act=attach&code=stats'  ),
							3 => array( '论坛附件搜索'     , 'act=attach&code=search'  ),
				  			),
				  			
				  			
				600 => array(
							1 => array( '自定义 BBCode 管理' , 'act=admin&code=bbcode'        ),
							2 => array( '添加新 BBCode'        , 'act=admin&code=bbcode_add'    ),
						   ),
						   
				700 => array(
							1 => array( '论坛表情图标管理'      , 'act=admin&code=emo'               ),
							2 => array( '导出/导入表情包'   , 'act=admin&code=emo_packsplash'    ),
						   ),		   
						   
				800 => array (
							1 => array( '管理词语过滤', 'act=admin&code=badword'     ),
							6 => array( '管理禁止过滤'    , 'act=admin&code=ban'  ),
							),		
				
				# SKINS & LANGS
				
				900 => array (
							1 => array( '主题管理'            , 'act=sets'        ),
							2 => array( '主题工具'              , 'act=skintools'   ),
							3 => array( '主题搜索 & 替换'   , 'act=skintools&code=searchsplash'   ),
							4 => array( '主题导入/导出'      , 'act=import'      ),
							5 => array( '快捷 Logo 更换'       , 'act=skintools&code=easylogo'   ),
						   ),
						   			
				1000 => array (
							1 => array( '管理语言包'        , 'act=lang'             ),
							2 => array( '导入语言包'       , 'act=lang&code=import' ),
						   ),
				
				
				# ADMIN
						   
				1100 => array (
							1 => array( '管理帮助文件'     , 'act=help'                   ),
							2 => array( '缓存控制'         , 'act=admin&code=cache'       ),
							3 => array( '重计数 & 重建'     , 'act=rebuild'                ),
							4 => array( '清理工具'        , 'act=rebuild&code=tools'     ),
						   ),
						   
			    1200 => array(
			    			1  => array( '管理群发邮件'      , 'act=postoffice'                    ),
			    			2  => array( '创建新邮件'      , 'act=postoffice&code=mail_new'      ),
			    			3  => array( '查看邮件日志'       , 'act=emaillog', '', 0, 1 ),
			    			4  => array( '查看邮件错误日志' , 'act=emailerror', '', 0, 1 ),
			    			//5  => array( '邮件设置'        , 'act=op&code=findsetting&key=emailset-up', '', 0, 1 ),
			    			5  => array( '邮件设置'        , 'act=op&code=findsetting&key=email', '', 0, 1 ),
			    		    ),
			    
			    1300 => array (
							 1 => array( '任务管理器'        , 'act=task'                ),
							 2 => array( '查看任务日志'      , 'act=task&code=log'       ),
						   ),
				
				
				1400 => array(
							 1 => array( '易维相册插件'        , 'act=gallery' ),
							 2 => array( '|-- 相册设置'            , 'act=op&code=findsetting&key='.urlencode('invisiongallerysettings'), '', 0, 0 ),
							 3 => array( '|-- 相册管理'       , 'act=gallery&code=albums'  , 'modules/gallery' ),
							 4 => array( '|-- 多媒体管理'  , 'act=gallery&code=media'   , 'modules/gallery' ),
							 5 => array( '|-- 用户组'              , 'act=gallery&code=groups'  , 'modules/gallery' ),  
							 6 => array( '|-- 相册统计'               , 'act=gallery&code=stats'   , 'modules/gallery' ),
							 7 => array( '|-- 相册工具'               , 'act=gallery&code=tools'   , 'modules/gallery' ),
							 8 => array( '&#039;-- 提交表单'      , 'act=gallery&code=postform', 'modules/gallery' ),
						   ),
						   
				1450 => array(
							 1 => array( '易维博客插件'          , 'act=blog' ),
							 2 => array( '博客设置'           , 'act=op&code=findsetting&key='.urlencode('communityblog'), '', 0, 1 ),
							 3 => array( '用户组'				   , 'act=blog&amp;cmd=groups' ),
							 4 => array( '内容模块'		   , 'act=blog&amp;cmd=cblocks' ),
							 5 => array( '博客工具'				   , 'act=blog&amp;cmd=tools' ),
						   ),
				
				1500 => array (
							1 => array( '注册状态' , 'act=stats&code=reg'   ),
							2 => array( '新主题状态'    , 'act=stats&code=topic' ),
							3 => array( '新帖子状态'         , 'act=stats&code=post'  ),
							4 => array( '短消息'   , 'act=stats&code=msg'   ),
							5 => array( '主题查看'        , 'act=stats&code=views' ),
						   ),
						   
				1600 => array (
							1 => array( 'SQL 工具箱'     , 'act=mysql'           ),
							2 => array( 'SQL 备份'     , 'act=mysql&code=backup'    ),
							3 => array( 'SQL 执行时间', 'act=mysql&code=runtime'   ),
							4 => array( 'SQL 系统变量' , 'act=mysql&code=system'    ),
							5 => array( 'SQL 进程'   , 'act=mysql&code=processes' ),
						   ),
				
				1700 => array(
							1 => array( '查看版主日志'  , 'act=modlog'    ),
							2 => array( '查看管理员日志'      , 'act=adminlog'  ),
							3 => array( '查看邮件日志'      , 'act=emaillog'  ),
							4 => array( '查看邮件错误日志', 'act=emailerror' ),
							5 => array( '查看搜索蜘蛛日志'        , 'act=spiderlog' ),
							6 => array( '查看警告日志'       , 'act=warnlog'   ),
						   ),
			   );
			   
			   
$CATS = array (   
				  100 => array( "系统设置"   , '#caf2d9;margin-bottom:12px;' ),
				  
				  200 => array( '论坛控制'     , '#F9FFA2' ),
				  300 => array( '会员和用户组'  , '#F9FFA2' ),
				  400 => array( "付费订阅设置"     , '#F9FFA2;margin-bottom:12px;' ),
				  
				  500 => array( "论坛附件设置"       , '#f5cdcd' ),
				  600 => array( "自定义 BBCode"     , '#f5cdcd' ),
				  700 => array( "论坛表情图标管理"         , '#f5cdcd' ),
				  800 => array( "脏话 & 禁言过滤", '#f5cdcd;margin-bottom:12px;' ),
				  
				  900 => array( '主题 & 模板设置' , '#DFE6EF' ),
				  1000 => array( '语言文件设置'        , '#DFE6EF;margin-bottom:12px;' ),
				  
				  1100 => array( '论坛维护'      , '#caf2d9' ),
				  1200 => array( '邮件中心'      , '#caf2d9' ),
				  1300 => array( '任务管理器'     , '#caf2d9;margin-bottom:12px;' ),
				  
				  1400 => array( "易维相册" , '#F9FFA2;' ),
				  1450 => array( "易维博客"   , '#F9FFA2;margin-bottom:12px;' ),
				  
				  1500 => array( '统计中心' , '#f5cdcd' ),
				  1600 => array( 'SQL 管理'   , '#f5cdcd' ),
				  1700 => array( '论坛日志'       , '#f5cdcd' ),
			  );
			  

			  
$DESC = array (
				  100 => "编辑论坛设置如 Cookies 路径、安全特性、发帖权限等等",
				  
				  200 => "创建、编辑、删除分类、论坛、版主",
				  300 => "管理会员、用户组和权限队列",
				  400 => "管理您的会员付费订阅等",
				  
				  500 => "管理您的附件",
				  600 => "管理您的自定义 BBCode",
				  700 => "管理您的论坛表情以及上传/下载表情包",
				  800 => "管理您的脏话、禁言过滤",
				  
				  900 => "管理主题、模板、图片目录等.",
				  1000 => "管理语言设置",
				  
				  1100 => "管理论坛帮助文件、禁言过滤、表情文件等",
				  1200 => "管理您的邮件以及群发邮件会员",
				  1300 => "管理您的计划任务.",
				  
				  1400 => "管理您的相册",
				  1450 => "管理您的博客",
				  1500 => "管理您的注册和发帖统计",
				  1600 => "管理您的数据库; 修理、优化以及导入数据",
				  1700 => "查看管理员、版主和邮件日志 (仅限系统管理员)",
			  );
?>