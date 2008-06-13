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

$CATS[]  = array( '会员和用户组' );

$PAGES[] = array(
					 1  => array ( '会员管理'        , 'section=content&amp;act=mem&amp;code=search' ),
					 2  => array ( '添加新会员'        , 'section=content&amp;act=mem&amp;code=add'  ),
					 3  => array ( '头衔管理'          , 'section=content&amp;act=mem&amp;code=title'),
					 4  => array ( '用户组管理'    , 'section=content&amp;act=group'         ),
					 5  => array ( '激活会员管理'     , 'section=content&amp;act=mtools&amp;code=mod'  ),
					 6  => array ( '帐号锁定管理'     	   , 'section=content&amp;act=mtools&amp;code=lock'  ),
					 9  => array ( '自定义资料项' , 'section=content&amp;act=field'         ),
					 11 => array ( '会员 IP 工具'       , 'section=content&amp;act=mtools'        ),
					 12 => array ( '会员设置'       , 'section=tools&amp;act=op&amp;code=findsetting&amp;key=userprofiles', '', 0, 1 ),
			       );
			       
$CATS[]  = array( '论坛管理' );

$PAGES[] = array(
					 1 => array( '论坛管理'         	, 'section=content&amp;act=forum'                ),
					 2 => array( '添加新分类'         , 'section=content&amp;act=forum&amp;code=new&amp;type=category'       ),
					 3 => array( '添加新论坛'            , 'section=content&amp;act=forum&amp;code=new&amp;type=forum'       ),
					 4 => array( '权限管理'      	, 'section=content&amp;act=group&amp;code=permsplash'),
					 // 6 => array( 'Moderators'            , 'section=content&amp;act=mod'                  ),
					 7 => array( '批量主题管理'	, 'section=content&amp;act=multimod'          ),
					 //8 => array( '回收站设置'      	, 'section=tools&amp;act=op&amp;code=findsetting&amp;key=trashcanset-up', '', 0, 1 ),
					 8 => array( '回收站设置'      	, 'section=tools&amp;act=op&amp;code=findsetting&amp;key=trashcansetup', '', 0, 1 ),
			       );
			       
$CATS[]  = array( '订阅管理' );

$PAGES[] = array(
					 1 => array( '管理支付网关'   , 'section=content&amp;act=msubs&amp;code=index-gateways' ),
					 2 => array( '管理订阅包裹'           , 'section=content&amp;act=msubs&amp;code=index-packages' ),
					 3 => array( '交易管理'       , 'section=content&amp;act=msubs&amp;code=index-tools' ),
					 4 => array( '货币管理'         , 'section=content&amp;act=msubs&amp;code=currency' ,  ),
					 5 => array( '手动添加交易'  , 'section=content&amp;act=msubs&amp;code=addtransaction' ),
					 6 => array( '安装支付网关'  , 'section=content&amp;act=msubs&amp;code=install-index' ),
					 //9 => array( '付费订阅设置'     , 'section=tools&amp;act=op&amp;code=findsetting&amp;key='.urlencode('subscriptionsmanager'), '', 0, 1 ),
					 9 => array( '付费订阅设置'     , 'section=tools&amp;act=op&amp;code=findsetting&amp;key=subsmanager', '', 0, 1 ),
				  
			       );
			       
$CATS[]  = array( '日历管理' );

$PAGES[] = array(
					1 => array( '论坛日历管理' , 'section=content&amp;act=calendars&amp;code=calendar_list' ),
					2 => array( '添加新日历' , 'section=content&amp;act=calendars&amp;code=calendar_add'  ),
			       );
			       
$CATS[]  = array( 'RSS 管理' );

$PAGES[] = array(
					1 => array( 'RSS 输出管理' , 'section=content&amp;act=rssexport&amp;code=rssexport_overview'        ),
					2 => array( 'RSS 输入管理' , 'section=content&amp;act=rssimport&amp;code=rssimport_overview'    ),
			       );
			       
$CATS[]  = array( '自定义 BBCode' );

$PAGES[] = array(
					1 => array( '自定义 BBCode 管理' , 'section=content&amp;act=bbcode&amp;code=bbcode'        ),
					2 => array( '添加新 BBCode'        , 'section=content&amp;act=bbcode&amp;code=bbcode_add'    ),
			       );
			       
$CATS[]  = array( '脏话过滤' );

$PAGES[] = array(
					1 => array( '管理脏话过滤', 'section=content&amp;act=babw&amp;code=badword'     ),
					2 => array( '管理屏蔽'    , 'section=content&amp;act=babw&amp;code=ban'  ),
			       );
			       
$CATS[]  = array( '附件管理' );

$PAGES[] = array(
					1 => array( '附件类型'      , 'section=content&amp;act=attach&amp;code=types'  ),
					2 => array( '附件统计'      , 'section=content&amp;act=attach&amp;code=stats'  ),
					3 => array( '附件搜索'     , 'section=content&amp;act=attach&amp;code=search'  ),
			       );
			  

?>