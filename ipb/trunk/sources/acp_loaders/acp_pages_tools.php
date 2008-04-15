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

$CATS[]  = array( '系统设置' );

$PAGES[] = array(
					 1 => array( '查看所有通用设置', 'section=tools&amp;act=op' ),
					 2 => array( '添加新的通用设置'  , 'section=tools&amp;act=op&amp;code=settingnew' ),
					 3 => array( '管理门户插件', 'section=tools&amp;act=portal' ),
					 //7 => array( '论坛打开/关闭'      , 'section=tools&amp;act=op&amp;code=findsetting&amp;key='.urlencode('boardoffline/online'), '', 0, 1 ),
					 7 => array( '论坛打开/关闭'      , 'section=tools&amp;act=op&amp;code=findsetting&amp;key=boardoffline', '', 0, 1 ),
					 8 => array( '论坛规则设置'         , 'section=tools&amp;act=op&amp;code=findsetting&amp;key='.urlencode('boardguidelines'), '', 0, 1 ),
					 9 => array( '论坛通用设置'    , 'section=tools&amp;act=op&amp;code=findsetting&amp;key='.urlencode('generalconfiguration'), '', 0, 1 ),
					 10 => array( '服务器 CPU 降耗'              , 'section=tools&amp;act=op&amp;code=findsetting&amp;key='.urlencode('cpusaving'), '', 0, 1 ),
					// 11 => array( 'IP Chat'                 , 'section=tools&amp;act=pin&amp;code=ipchat'  ),
					 //12 => array( 'IPB License'             , 'section=tools&amp;act=pin&amp;code=reg'     ),
					 //14 => array( 'IPB Copyright Removal'   , 'section=tools&amp;act=pin&amp;code=copy'    ),
				);
			       
$CATS[]  = array( '论坛维护' );

$PAGES[] = array(
					1 => array( '管理帮助文件'     , 'section=tools&amp;act=help'                   ),
					2 => array( '缓存控制'         , 'section=tools&amp;act=admin&amp;code=cache'       ),
					3 => array( '重计数 &amp; 重建'     , 'section=tools&amp;act=rebuild'                ),
					4 => array( '论坛清理工具'        , 'section=tools&amp;act=rebuild&amp;code=tools'     ),
			       );
			       
$CATS[]  = array( '邮件中心' );

$PAGES[] = array(
					1  => array( '管理群发邮件'      , 'section=tools&amp;act=postoffice'                    ),
			    	2  => array( '创建新邮件'      , 'section=tools&amp;act=postoffice&amp;code=mail_new'      ),
			    	3  => array( '查看邮件日志'       , 'section=admin&amp;act=emaillog', '', 0, 1 ),
			    	4  => array( '查看邮件错误日志' , 'section=admin&amp;act=emailerror', '', 0, 1 ),
			    	//5  => array( '邮件设置'        , 'section=tools&amp;act=op&amp;code=findsetting&amp;key=emailset-up', '', 0, 1 ),
					5  => array( '邮件设置'        , 'section=tools&amp;act=op&amp;code=findsetting&amp;key=email', '', 0, 1 ),

			       );

$CATS[]  = array( '登录设置' );

$PAGES[] = array(
					1 => array( '登录入口管理'    , 'section=tools&amp;act=loginauth'                    ),
					2 => array( '创建新登录入口' , 'section=tools&amp;act=loginauth&amp;code=login_add' ),
			       );
			       
$CATS[]  = array( '任务管理' );

$PAGES[] = array(
					1 => array( '任务管理器'        , 'section=tools&amp;act=task'                ),
					2 => array( '查看任务日志'      , 'section=tools&amp;act=task&amp;code=log'       ),
			       );
			       
			  

?>