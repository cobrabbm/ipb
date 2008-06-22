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
|   > CONTROL PANEL (COMPONENTS) PAGES FILE
|   > Script written by Matt Mecham
|   > Date started: Tue. 15th February 2005
|
+---------------------------------------------------------------------------
*/

//===========================================================================
// Simple library that holds all the links for the admin cp
// THIS PAGE CLASS: Generate menu from DB
//===========================================================================

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url
// $PAGES[ $cat_id ][$page_id][2] = Look for folder before showing
// $PAGES[ $cat_id ][$page_id][3] = URL type: 1 = Board URL 0 = ACP URL
// $PAGES[ $cat_id ][$page_id][4] = Item icon: 1= redirect 0 = Normal
// $PAGES[ $cat_id ][$page_id][5] = Regular link - open exact link in new window

global $ipsclass;

$CATS  = array();
$PAGES = array();

$CATS[]  = array( '支持中心' );

$PAGES[] = array(
					0 => array( '提交帮助申请' , 'section=help&amp;act=support&amp;code=support'   ),
					1 => array( 'IPB 知识库' 	, 'section=help&amp;act=support&amp;code=kb'   ),
					2 => array( 'IPB 文档中心' 	, 'section=help&amp;act=support&amp;code=doctor'   ),
					3 => array( 'IPS 资源中心' 		, 'section=help&amp;act=support&amp;code=resources'   ),
					4 => array( '联系 IPS 官方' 			, 'section=help&amp;act=support&amp;code=contact'  ),
					5 => array( '新功能建议' 	, 'section=help&amp;act=support&amp;code=features'   ),
					6 => array( 'Bug 报告' 			, 'section=help&amp;act=support&amp;code=bugs'   ),
			       );

$CATS[]  = array( '论坛诊断' );

$PAGES[] = array(
					0 => array( '系统概貌'		, "section=help&amp;act=diag' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					//2 => array( 'Version Checker' 		, "section=help&amp;act=diag&amp;code=fileversions' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					3 => array( '数据库检查' 		, "section=help&amp;act=diag&amp;code=dbchecker' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					4 => array( '数据库索引检查' , "section=help&amp;act=diag&amp;code=dbindex' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					5 => array( '文件权限检查' , "section=help&amp;act=diag&amp;code=filepermissions' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					6 => array( '空白文件检查' 	, "section=help&amp;act=diag&amp;code=whitespace' onclick='xmlobj = new ajax_request();xmlobj.show_loading()'"   ),
					7 => array( '安全中心' 		, "section=admin&amp;act=security", 0, 0, 1   ),
			       );	

?>