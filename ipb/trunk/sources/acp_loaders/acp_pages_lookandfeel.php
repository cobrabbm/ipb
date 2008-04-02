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

$CATS[]  = array( '主题模板' );

$PAGES[] = array(
					1 => array( '模板管理'            , 'section=lookandfeel&amp;act=sets'        ),
					2 => array( '模板工具'              , 'section=lookandfeel&amp;act=skintools'   ),
					3 => array( '模板搜索 & 替换'   , 'section=lookandfeel&amp;act=skintools&amp;code=searchsplash'   ),
					4 => array( '模板导入/导出'      , 'section=lookandfeel&amp;act=import'      ),
					5 => array( '模板差异比较'        , 'section=lookandfeel&amp;act=skindiff'      ),
					6 => array( '模板重测图'          , 'section=lookandfeel&amp;act=skinremap'      ),
					7 => array( '简易 Logo 更换'       , 'section=lookandfeel&amp;act=skintools&amp;code=easylogo'   ),
			       );
			       
$CATS[]  = array( '语言管理' );

$PAGES[] = array(
					 1 => array( '管理语言包'        , 'section=lookandfeel&amp;act=lang'             ),
					 2 => array( '导入语言包'       , 'section=lookandfeel&amp;act=lang&amp;code=import' ),
			     );
			       
$CATS[]  = array( '表情图标' );

$PAGES[] = array(
					1 => array( '表情图标管理'      , 'section=lookandfeel&amp;act=emoticons&amp;code=emo'               ),
					2 => array( '图标导入/导出'   , 'section=lookandfeel&amp;act=emoticons&amp;code=emo_packsplash'    ),
			       );
			       


?>