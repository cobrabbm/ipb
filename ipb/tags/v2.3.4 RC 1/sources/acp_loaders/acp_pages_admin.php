<?php

/*
+---------------------------------------------------------------------------
|   Invision Power Board
|   ========================================
|   by Matthew Mecham
|   (c) 2004 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
+---------------------------------------------------------------------------
|   INVISION POWER BOARD IS NOT FREE SOFTWARE!
|   http://www.invisionboard.com
+---------------------------------------------------------------------------
|
|   > CONTROL PANEL PAGES FILE
|   > Script written by Matt Mecham
|   > Date started: Fri 8th April 2005 (12:07)
|
+---------------------------------------------------------------------------
*/

//===========================================================================
// Simple library that holds all the links for the admin cp
//===========================================================================

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url
// $PAGES[ $cat_id ][$page_id][2] = Look for folder before showing
// $PAGES[ $cat_id ][$page_id][3] = URL type: 1 = Board URL 0 = ACP URL
// $PAGES[ $cat_id ][$page_id][4] = Item icon: 1= redirect 0 = Normal
			
$CATS[]  = array( '安全中心' );

$PAGES[] = array(
					1 => array( '安全中心'		 , 'section=admin&amp;act=security' ),
					2 => array( '列出所有管理员', 'section=admin&amp;act=security&amp;code=list_admins'  ),
					3 => array( '管理权限设置' , 'section=admin&amp;act=acpperms&amp;code=acpp_list'   ),
			       );

$CATS[]  = array( '论坛日志' );

$PAGES[] = array(
					1 => array( '查看版主日志'  , 'section=admin&amp;act=modlog'    ),
					2 => array( '查看管理员日志'      , 'section=admin&amp;act=adminlog'  ),
					3 => array( '查看邮件日志'      , 'section=admin&amp;act=emaillog'  ),
					4 => array( '查看邮件错误日志', 'section=admin&amp;act=emailerror' ),
					5 => array( '查看搜索引擎日志'        , 'section=admin&amp;act=spiderlog' ),
					6 => array( '查看警告日志'       , 'section=admin&amp;act=warnlog'   ),
					7 => array( '查看后台登录日志' , 'section=admin&amp;act=loginlog'   ),
			       );
									
$CATS[]  = array( '组件管理' );

$PAGES[] = array(
					1 => array( '管理组件'      , 'section=admin&amp;act=components'   ),
					2 => array( '注册组件' , 'section=admin&amp;act=components&amp;code=component_add' ),
			       );
			       
$CATS[]  = array( '统计中心' );

$PAGES[] = array(
					1 => array( '注册状态统计' , 'section=admin&amp;act=stats&amp;code=reg'   ),
					2 => array( '新主题统计'    , 'section=admin&amp;act=stats&amp;code=topic' ),
					3 => array( '发帖数统计'         , 'section=admin&amp;act=stats&amp;code=post'  ),
					4 => array( '短消息统计'   , 'section=admin&amp;act=stats&amp;code=msg'   ),
					5 => array( '主题查看统计'        , 'section=admin&amp;act=stats&amp;code=views' ),
			       );
			       
			       
$CATS[]  = array( '数据库管理' );

$PAGES[] = array(
					1 => array( '数据库工具箱'     , 'section=admin&amp;act=sql'           ),
					2 => array( '数据库备份'     , 'section=admin&amp;act=sql&amp;code=backup'    ),
					3 => array( '数据库执行信息', 'section=admin&amp;act=sql&amp;code=runtime'   ),
					4 => array( '数据库系统变量' , 'section=admin&amp;act=sql&amp;code=system'    ),
					5 => array( '数据库进程'   , 'section=admin&amp;act=sql&amp;code=processes' ),
			       );

$CATS[]  = array( 'API 管理' );

$PAGES[] = array(
					1 => array( '管理 XML-RPC 会员', 'section=admin&amp;act=api&amp;code=api_list' ),
					2 => array( '查看 XML-RPC 日志'   , 'section=admin&amp;act=api&amp;code=log_list' ),
			       ); 

?>