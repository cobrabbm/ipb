<?php

class cp_skin_dashboard {

var $ipsclass;


//===========================================================================
// Index http://www.invisionboard.com/acp-ipb/getnews.php
//===========================================================================
function acp_main_template( $content, $f_dd, $g_dd, $urls=array() ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div style='font-size:30px; padding-left:7px; letter-spacing:-2px; border-bottom:1px solid #EDEDED'>
 欢迎使用易维论坛!
</div>
<br />
<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:120px;display:none;z-index:100'></div>
<!--in_dev_notes-->
<!--in_dev_check-->
<table border='0' width='100%' cellpadding='0' cellspacing='4'>
<tr>
 <td valign='top' width='75%'>
	<table border='0' width='100%' cellpadding='0' cellspacing='0'>
	<tr>
	 <td>
		<div class='homepage_pane_border'>
		 <div class='homepage_section'>常规操作</div>
		 <table width='100%' cellspacing='0' cellpadding='4' id='common_actions'>
			 <tr>
			  <td width='33%' valign='top'>
				<div><a href='{$this->ipsclass->base_url}&section=content&act=mem&code=search' title='管理会员'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/members.png' border='0' alt='管理会员' /> 管理会员</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=content&act=mtools&code=mod' title='处理等待验证会员'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/validating.png' border='0' alt='处理等待验证会员' /> 等待验证会员</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=content&act=forum' title='管理板块'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/forums.png' border='0' alt='管理板块' /> 管理板块</a></div>
			</td>
			<td width='33%' valign='top'>
				<div><a href='{$this->ipsclass->base_url}&section=tools' title='修改系统设置'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/settings.png' border='0' alt='修改系统设置' /> 修改系统设置</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=lookandfeel&act=sets' title='主题管理'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/skins.png' border='0' alt='主题管理' /> 主题管理</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=tools&act=postoffice' title='批量发送邮件'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/bulkmail.png' border='0' alt='批量发送邮件' /> 批量发送邮件</a></div>
			</td>
			<td width='33%' valign='top'>
				<div><a href='{$this->ipsclass->base_url}&section=content&act=group' title='管理用户组'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/groups.png' border='0' alt='管理用户组' /> 管理用户组</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=lookandfeel&act=lang' title='管理语言'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/languages.png' border='0' alt='管理语言' /> 管理语言</a></div>
				<div><a href='{$this->ipsclass->base_url}&section=lookandfeel&act=emoticons&code=emo' title='管理表情'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/emos.png' border='0' alt='管理表情' /> 管理表情</a></div>
			 </tr>
		 </table>
		</div>
	</td>
	</tr>
	<tr>
	 <td>&nbsp;</td>
	</tr>
	<tr>
	 <td>
		<div class='homepage_pane_border'>
		 <div class='homepage_section'>任务与统计</div>
		 <table width='100%' cellspacing='0' cellpadding='4'>
			 <tr>
			  <td width='50%' valign='top'>
			  	{$content['stats']}
			  </td>
			  <td width='50%' valign='top'>
				<div class='homepage_border'>
				 <div class='homepage_sub_header'>快速管理</div>
				 <table width='100%' cellpadding='4' cellspacing='0'>
				 <tr>
				  <td class='homepage_sub_row'>
					<strong>寻找或修改会员</strong> <span class='desctext' title='输入一部分或全部用户名'>?</span>
					<br /><form name='DOIT' id='DOIT' action='{$this->ipsclass->adskin->base_url}&section=content&act=mem&code=searchresults&searchtype=normal&' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='33' class='textinput' id='members_display_name' name='members_display_name' value='' /> <input type='submit' value='查找...' class='realbutton' onclick='edit_member()' /></form>
				  </td>
				 </tr>
				 <tr>
				  <td class='homepage_sub_row'>
					<strong>添加新会员</strong> <span class='desctext' title='输入用户名和用户组'>?</span>
				    <br /><form name='newmem' id='newmem' action='{$this->ipsclass->adskin->base_url}&section=content&act=mem&code=add' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='17' class='textinput' name='name' value='' /> <select name='mgroup'>{$g_dd}</select> <input type='submit' value='添加...' class='realbutton' /></form></td>
				 </tr>
				 <tr>
				  <td class='homepage_sub_row'>
					<strong>编辑一个板块</strong> <span class='desctext' title='选择一个板块以修改'>?</span>
				    <br /><form name='newmem' id='newmem' action='{$this->ipsclass->adskin->base_url}&section=content&act=forum&code=edit' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><select name='f'>{$f_dd}</select> <input type='submit' value='编辑...' class='realbutton' /></form></td>
				 </tr>
				 <tr>
				  <td class='homepage_sub_row'>
					<strong>IP 地址搜索</strong> <span class='desctext' title='查找某 IP 地址的信息'>?</span>
				  	<br /><form name='ipform' id='ipform' action='{$this->ipsclass->adskin->base_url}&section=content&act=mtools&code=learnip' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='33' class='textinput' name='ip' value='' /> <input type='submit' value='查找...' class='realbutton' /></form></td>
				 </tr>
				 <tr>
				  <td class='homepage_sub_row'>
					<strong>搜索系统设置</strong> <span class='desctext' title='搜索某个设置以修改'>?</span>
				  	<br /><form name='settingform' id='settingform' action='{$this->ipsclass->adskin->base_url}&section=tools&act=op&code=setting_view' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='33' class='textinput' name='search' value='' /> <input type='submit' value='搜索...' class='realbutton' /></form></td>
				 </tr>
				 </table>
				</div>
		  	</td>
		   </tr>
	    </table>
	   </div>
	 </td>
	</tr>
	<tr>
	 <td>&nbsp;</td>
	</tr>
	<tr>
	 <td>
		<div class='homepage_pane_border'>
		 <div class='homepage_section'>交流</div>
		 <table width='100%' cellspacing='0' cellpadding='4'>
			 <tr>
			  <td valign='top' width='50%'>
			  	<div class='homepage_border'>
					<div class='homepage_sub_header'>记事本</div>
					<br />{$content['ad_notes']}<br />
				</div>
			  </td>
			  <td width='50%' valign='top'>
			  	{$content['acp_online']}
			  </td>
			 </tr>
		 </table>
		</div>
	 </td>
	</tr>
	
	</table>
 </td>
 <td valign='top' width='25%'>
	<div id='acp-update-wrapper' style='display:none'>
		<!-- Security Update -->
		<div class='homepage_pane_warning_border' id='acp-update-security' style='display:none'>
		 <div class='homepage_section_warning'>存在安全更新</div>
			<div style='font-size:12px;padding:4px; text-align:center'>
				<p>
					<strong><span id='acp-version-security'></span></strong> 存在安全更新!
				</p>
				<input type='button' onclick='VU_moreinfo()' value=' 更多信息 ' /> <input type='button' onclick='VU_reset()' value=' 复位警告 ' />
			</div>
		</div>
		<!-- Normal Version Upgrade -->
		<div class='homepage_pane_border' id='acp-update-update' style='display:none'>
		 <div class='homepage_section'>存在更新</div>
			<div style='font-size:12px;padding:4px; text-align:center'>
				<p>
					<strong><span id='acp-version-update'></span></strong> Update Available Now!
				</p>
				<input type='button' onclick='VU_moreinfo()' value=' 更多信息 ' /> <input type='button' onclick='VU_reset()' value=' 复位提示 ' />
			</div>
		</div>
		<!-- Normal Version Upgrade -->
		<div class='homepage_pane_border' id='acp-update-normal' style='display:none'>
		 <div class='homepage_section'>存在新版本</div>
			<div style='font-size:12px;padding:4px; text-align:center'>
				<p>
					版本 <strong><span id='acp-version-normal'></span></strong> 现在可以使用!
				</p>
				<input type='button' onclick='VU_moreinfo()' value=' 更多信息 ' />
			</div>
		</div>
		<br />
	</div>
	<!--warninginstaller-->
	<!--warningupgrade-->
	<!--warningskin-->
	<!--warningftext-->
	<!--phpversioncheck-->
	<!--boardoffline-->
	{$content['validating']}
	<div class='homepage_pane_border' id='acp-news-outer'>
	 <div class='homepage_section'>最新 IPS 新闻</div>
		<div>
			<div id='acp-news-wrapper'>
			</div>
		</div>
	</div>
	<br />
	<div class='homepage_pane_border' id='acp-blog-outer'>
	 <div class='homepage_section'>最新 IPS 日志</div>
		<div id='acp-blog-wrapper'>
		</div>
	</div>
	<br />
	<div class='homepage_pane_border'>
	 <div class='homepage_section'>IP.Board 公告板</div>
		<div id='keith-is-not-hidden'>
		</div>
	</div>
	<!--acplogins-->
 </td>
</tr>
</table>
<!-- HIDDEN "STOP" DIV -->
<div id='acp-update-stop-wrapper' style='display:none;width:450px;'>
	<div class='homepage_pane_warning_border' style='height:130px'>
		<div class='homepage_section_warning'>注意: 重设更新提醒</div>
		<div style='float:left'>
			<img src='{$this->ipsclass->skin_acp_url}/images/update_icons/update_warning.png' border='0' />
		</div>
		<div style='padding:4px;font-size:12px'>
			如果您 <strong>已经完成升级</strong> 请进行重设.
			<p>
				重设提醒 <strong>不会</strong> 给您完成升级过程.
			</p>
			<p style='text-align: right'>
				<input type='button' value=' 继续 ' onclick='VR_continue()' style='background-color:lightgreen;font-size:14px;' />
				<input type='button' value=' CANCEL ' onclick='VR_cancel()' style='background-color:pink;font-size:14px;' />
			</p>
		</div>
	</div>
</div>
<!-- / HIDDEN "STOP" DIV -->
<!-- HIDDEN "INFORMATION" DIV -->
<div id='acp-update-info-wrapper' style='display:none;width:450px;'>
	<div class='homepage_pane_border' style='height:130px'>
		<div class='homepage_section'>注意: 更新信息</div>
		<div style='float:left'>
			<img src='{$this->ipsclass->skin_acp_url}/images/update_icons/update_info.png' border='0' />
		</div>
		<div style='padding:4px;font-size:12px'>
			<p>
				下载最新更新, 请登录 <strong>IPS 官方客户中心</strong> 进行 <strong>您的下载</strong>
			</p>
			<p style='text-align: right;'>
				<input type='button' value=' 访问 IPS 官方客户中心 ' onclick='VU_continue()' style='background-color:lightgreen;font-size:14px;width:190px;' />
				<input type='button' value=' 关闭 ' onclick='VU_cancel()' style='background-color:pink;font-size:14px;width:70px;' />
			</p>
		</div>
	</div>
</div>
<!-- / HIDDEN "INFORMATION" DIV -->

<script type='text/javascript'>

var infoCenterDiv = '';

/* Upgrade DOWNLOAD / RESET */
function VU_reset()
{
	centerDiv = new center_div();
	centerDiv.divname = 'acp-update-stop-wrapper';
	centerDiv.move_div();
}
function VU_moreinfo()
{
	if( !infoCenterDiv )
	{
		infoCenterDiv = new center_div();
		infoCenterDiv.divname = 'acp-update-info-wrapper';
	}

	infoCenterDiv.move_div();
}
function VU_cancel()
{
	//document.getElementById( 'acp-update-info-wrapper' ).style.display = 'none';
	infoCenterDiv.hide_div();
}
function VU_continue()
{
	ipsclass.pop_up_window( IPSSERVER_download_link, 800, 600 );
	document.getElementById( 'acp-update-info-wrapper' ).style.display = 'none';
}

/* Warning CONTINUE / CANCEL */
function VR_continue()
{
	ipsclass.location_jump( ipb_var_base_url + "&amp;section=dashboard&amp;reset_security_flag=1&amp;new_build=" + IPSSERVER_download_ve + "&amp;new_reason=" + IPSSERVER_download_vt, 1 );
}
function VR_cancel()
{
	document.getElementById( 'acp-update-stop-wrapper' ).style.display = 'none';
}

/* Edit member box */
function edit_member()
{
	if (document.getElementById('DOIT').members_display_name.value == "")
	{
		alert("您必须输输入一个用户名称!");
		return false;
	}
}

/* INIT find names */
init_js( 'DOIT', 'members_display_name');

/* Set up global vars */
var _newsFeed     = null;
var _blogFeed     = null;
var _versionCheck = null;
var _keithFeed    = null;

/* ---------------------- */
/* ONLOAD: IPS widgets    */
/* ---------------------- */

function onload_ips_widgets()
{
	/* Grab files */
	ipsclass.include_javascript( "{$urls['news']}" );
	ipsclass.include_javascript( "{$urls['blogs']}" );
	ipsclass.include_javascript( "{$urls['version_check']}" );
	ipsclass.include_javascript( "{$urls['keiths_bits']}&inline=1" );
	
	/* ---------------------- */
	/* Feeds                  */
	/* ---------------------- */
	
	_newsFeed = setTimeout( '_newsFeedFunction()', 1000 );
	_blogFeed = setTimeout( '_blogFeedFunction()', 1000 );
	
	/* ---------------------- */
	/* Update boxes           */
	/* ---------------------- */
	
	_versionCheck = setTimeout( '_versionCheckFunction()', 1000 );
	
	/* ---------------------- */
	/* Load Keith             */
	/* ---------------------- */
	
	_keithFeed = setTimeout( '_keithFeedFunction()', 1000 );
}

/* ---------------------- */
/* Keith Feed YumYum      */
/* ---------------------- */

function _keithFeedFunction()
{
	if ( typeof( IPS_KEITH_CONTENT ) != 'undefined' )
	{
		clearTimeout( _keithFeed );
		
		if ( IPS_KEITH_CONTENT && IPS_KEITH_CONTENT != 'none' )
		{
			/* Show version numbers */
			document.getElementById( 'keith-is-not-hidden' ).innerHTML = IPS_KEITH_CONTENT.replace( /&#0039;/g, "'" );

			/* Fade it in */
			ipsclass.fade_in_element( 'keith-is-not-hidden' );
		}
	}
	else
	{
		_keithFeed = setTimeout( '_keithFeedFunction()', 1000 );
	}
}

/* ---------------------- */
/* Version Check          */
/* ---------------------- */

function _versionCheckFunction()
{
	if ( typeof( IPSSERVER_update_type ) != 'undefined' )
	{
		clearTimeout( _versionCheck );
		
		if ( IPSSERVER_update_type && IPSSERVER_update_type != 'none' )
		{
			var _show = '';
			var _text = '';

			switch( IPSSERVER_update_type )
			{
				case 'security':
					_show = 'acp-update-security';
					_text = 'acp-version-security';
				break;
				case 'update':
					_show = 'acp-update-update';
					_text = 'acp-version-update';
				break;
				case 'normal':
					_show = 'acp-update-normal'
					_text = 'acp-version-normal';
				break;
			}

			( IPSSERVER_update_type == 'security' ) ? 'acp-update-security' : 'acp-update-normal';

			document.getElementById( _show ).style.display                = '';
			document.getElementById( 'acp-update-wrapper' ).style.display = '';

			/* Show version numbers */
			document.getElementById( _text ).innerHTML = IPSSERVER_download_vh;

			/* Fade it in */
			ipsclass.fade_in_element( _show );
		}
	}
	else
	{
		_versionCheck = setTimeout( '_versionCheckFunction()', 1000 );
	}
}

/* ---------------------- */
/* BLOG FEED              */
/* ---------------------- */

function _blogFeedFunction()
{
	if ( typeof( ipsBlogFeed ) != 'undefined' )
	{
		clearTimeout( _blogFeed );
	
		eval( ipsBlogFeed );
		var finalString = '';
		var _len        = ipsBlogFeed['items'].length;
	
		if( typeof( ipsBlogFeed['error'] ) == 'undefined' )
		{
			for( i = 0; i < _len; i++ )
			{
				var _style   = ( i + 1 < _len ) ? 'padding:2px;border-bottom:1px dotted black' : 'padding:2px';
				var _title   = ( ipsBlogFeed['items'][i]['title'].length > 36 ) ? ipsBlogFeed['items'][i]['title'].substr( 0, 33 ) + '...' : ipsBlogFeed['items'][i]['title'];
				finalString += "<div style='" + _style + "'><img src='" + ipb_var_image_url + "/ipsnews_item.gif' border='0' /> <a href='" + ipsBlogFeed['items'][i]['link'] + "' target='_blank' style='text-decoration:none'title='" + ipsBlogFeed['items'][i]['title'] + "'>" + _title + "</a></div>\\n";
			}
		}
	
		if ( finalString )
		{
			document.getElementById( 'acp-blog-wrapper' ).innerHTML = finalString;
		}
		else
		{
			document.getElementById( 'acp-blog-outer' ).style.display = 'none';
		}
	}
	else
	{
		_blogFeed = setTimeout( '_blogFeedFunction()', 1000 );
	}
}

/* ---------------------- */
/* NEWS FEED              */
/* ---------------------- */

function _newsFeedFunction()
{
	if ( typeof( ipsNewsFeed ) != 'undefined' )
	{
		clearTimeout( _newsFeed );
		
		eval( ipsNewsFeed );
		var finalString = '';
		var _len        = ipsNewsFeed['items'].length;

		if( typeof( ipsNewsFeed['error'] ) == 'undefined' )
		{
			for( i = 0; i < _len; i++ )
			{
				var _style   = ( i + 1 < _len ) ? 'padding:2px;border-bottom:1px dotted black' : 'padding:2px';
				var _title   = ( ipsNewsFeed['items'][i]['title'].length > 36 ) ? ipsNewsFeed['items'][i]['title'].substr( 0, 33 ) + '...' : ipsNewsFeed['items'][i]['title'];
				finalString += "<div style='" + _style + "'><img src='" + ipb_var_image_url + "/ipsnews_item.gif' border='0' /> <a href='" + ipsNewsFeed['items'][i]['link'] + "' target='_blank' style='text-decoration:none' title='" + ipsNewsFeed['items'][i]['title'] + "'>" + _title + "</a></div>\\n";
			}
		}

		if ( finalString )
		{
			document.getElementById( 'acp-news-wrapper' ).innerHTML = finalString;
		}
		else
		{
			document.getElementById( 'acp-news-outer' ).style.display = 'none';
		}
	}
	else
	{
		_newsFeed = setTimeout( '_newsFeedFunction()', 1000 );
	}
}

/* Run main loop */
var tmp = setTimeout( 'main_loop()', 10 );

/* Set up onload event */
add_onload_event( onload_ips_widgets );
//]]>
</script>
EOF;

if ( IN_DEV )
{
$IPBHTML .= <<<EOF
<br />
<div class='tableborder'>
 <div class='tableheaderalt'>开发包安装 XML 导入</div>
 <div class='tablepad'>
	<a href='{$this->ipsclass->base_url}&amp;section=admin&amp;act=components&amp;code=master_xml_export'>系统组件</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=tools&amp;act=loginauth&amp;code=master_xml_export'>登录模块</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=group&amp;code=master_xml_export'>用户组</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=attach&amp;code=master_xml_export'>附件</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=lookandfeel&amp;act=sets&amp;code=master_xml_export'>主题</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=tools&amp;act=task&amp;code=master_xml_export'>任务</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=tools&amp;act=help&amp;code=master_xml_export'>FAQ</a>
	&middot; <a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=bbcode&amp;code=bbcode_export'>BBCode</a>
 </div>
</div>
EOF;
}

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Index
//===========================================================================
function acp_validating_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='homepage_pane_border'>
	<div class='homepage_section'>管理员激活列表</div>
	{$content}
	<div align='right'>
	   <a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mtools&amp;code=mod' style='text-decoration:none'>更多 &raquo;</a>
	 </div>
</div>
<br />
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_validating_block( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='homepage_sub_row_3'>
 <div style='float:right;'>
  <a href='{$this->ipsclass->base_url}&section=content&act=mtools&code=domod&_admin_auth_key={$this->ipsclass->_admin_auth_key}&mid_{$data['member_id']}=1&type=approve'><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' alt='是' class='ipd' /></a>&nbsp;
  <a href='{$this->ipsclass->base_url}&section=content&act=mtools&code=domod&_admin_auth_key={$this->ipsclass->_admin_auth_key}&mid_{$data['member_id']}=1&type=delete'><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' alt='否' class='ipd' /></a>
 </div>
 <div>
  <strong><a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$data['member_id']}' target='_blank'>{$data['members_display_name']}</a></strong>{$data['_coppa']}<br />
  &nbsp;&nbsp;{$data['email']}</a><br />
  <div class='desctext'>&nbsp;&nbsp;IP: <a href='{$this->ipsclass->base_url}&section=content&act=mtools&code=learnip&ip={$data['ip_address']}'>{$data['ip_address']}</a></div>
  <div class='desctext'>&nbsp;&nbsp;注册日期 {$data['_entry']}</div>
 </div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function warning_converter() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	为了您的系统安全我们强烈建议您删除数据转换工具.
   	<br />您只需删除您所安装的转换工具中的 <b>convert/index.php</b> 文件即可.
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_notes($notes) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div align='center'>
<form action='{$this->ipsclass->base_url}&amp;section=dashboard&amp;act=dashboard&amp;save=1' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<textarea name='notes' style='background-color:#F9FFA2;border:1px solid #CCC;width:95%;font-family:verdana;font-size:10px' rows='8' cols='25'>{$notes}</textarea>
<div><br /><input type='submit' value='保存管理日志' class='realbutton' /></div>
</form>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}



//===========================================================================
// Index
//===========================================================================
function acp_last_logins_row( $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='homepage_sub_row_3' width='1' valign='middle'>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/user.png' border='0' alt='-' class='ipd' />
 </td>
 <td class='homepage_sub_row_3'>
	<strong>{$r['admin_username']}</strong>
	<div class='desctext'>
		{$r['_admin_time']}
	</div>
 </td>
 <td class='homepage_sub_row_3' align='center'>
	<img src='{$this->ipsclass->skin_acp_url}/images/{$r['_admin_img']}' border='0' alt='-' class='ipd' />
	<br /><a href='#' onclick="return ipsclass.pop_up_window('{$this->ipsclass->base_url}&amp;section=admin&amp;act=loginlog&amp;code=view_detail&amp;detail={$r['admin_id']}', 400, 400)" title='查看详细信息'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/view.png' border='0' alt='-' class='ipd' /></a>
</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_last_logins_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<br />
<div class='homepage_pane_border'>
 <div class='homepage_section'>最近后台登录</div>
	<table cellspacing='0' cellpadding='0' border='0' width='100%'>
	$content
	</table>
	<div align='right'><a href='{$this->ipsclass->base_url}&amp;section=admin&amp;act=loginlog' style='text-decoration:none'>更多 &raquo;</a></div>
</div>
<br />
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Index
//===========================================================================
function acp_onlineadmin_row( $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1' align='center'>
	 <div><img src='{$r['pp_thumb_photo']}' width='{$r['pp_thumb_width']}' height='{$r['pp_thumb_height']}' style='border:1px solid #000000; background-color:#FFFFFF; padding:6px' /></div>
</td>
 <td class='tablerow2'>
	<strong style='font-size:12px'><a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['session_member_id']}' target='_blank'>{$r['members_display_name']}</a></strong>
	<div style='margin-top:6px'>登录时间: {$r['_log_in']}</div>
	<div class='desctext'>IP 地址: {$r['session_ip_address']}</div>
	<div class='desctext'>登录地址: {$r['session_location']}</div>
	<div class='desctext'>最后点击: {$r['_click']}</div>
</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_onlineadmin_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='homepage_border'>
 <div class='homepage_sub_header'>正在使用后台的管理员</div>
 <table width='100%' cellpadding='4' cellspacing='0'>
 $content
 </table>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_lastactions_row( $rowb ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1' width='1' valign='middle'>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/user.png' border='0' alt='-' class='ipd' />
 </td>
 <td class='tablerow1'>
	 <b>{$rowb['members_display_name']}</b>
	<div class='desctext'>IP 地址: {$rowb['ip_address']}</div>
</td>
 <td class='tablerow2'>{$rowb['_ctime']}</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_lastactions_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>最近 5 个后台活动记录</div>
 <table width='100%' cellpadding='4' cellspacing='0'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='44'>会员名称</td>
  <td class='tablesubheader' width='55%'>活动时间</td>
 </tr>
 $content
 </table>
 <div class='tablefooter' align='right'>
   <a href='{$this->ipsclass->base_url}section=admin&amp;act=adminlog' style='text-decoration:none'>更多 &raquo;</a>
 </div>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Index
//===========================================================================
function acp_stats_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='homepage_border'>
 <div class='homepage_sub_header'><a href='{$this->ipsclass->base_url}&amp;section=help&amp;act=diag'>系统概览</a></div>
 <table width='100%' cellpadding='4' cellspacing='0'>
 <tr>
  <td class='homepage_sub_row' width='60%'><strong>IP.Board 版本</strong> &nbsp;(<a href='{$this->ipsclass->base_url}&amp;section=help&amp;act=diag#versions'>历史</a>)</td>
  <td class='homepage_sub_row' width='40%'><span style='color:red'>{$content['ipb_version']} (ID: {$content['ipb_id']})</span></td>
 </tr>
 <tr>
  <td class='homepage_sub_row'><strong>会员</strong></td>
  <td class='homepage_sub_row'>
  	<a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mem&amp;code=search'>管理</a>
  	(<strong>{$content['members']}</strong>)
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>在线会员</strong></td>
  <td class='homepage_sub_row'>
  	<a href='{$this->ipsclass->vars['board_url']}/index.php?act=online' target='_blank'>查看在线列表</a>
  	(<strong>{$content['sessions']}</strong>)
  </td>
 </tr> 
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>等待激活列表</strong></td>
  <td class='homepage_sub_row'>
	<a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mtools&amp;code=mod'>管理</a>
  	(<strong>{$content['validate']}</strong>)
  </td>
 </tr>
EOF;

if( $this->ipsclass->vars['ipb_bruteforce_attempts'] > 0 )
{
$IPBHTML .= <<<EOF
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>锁定帐户</strong></td>
  <td class='homepage_sub_row'>
  	<a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mtools&amp;code=lock'>管理</a>
  	(<strong>{$content['locked']}</strong>)
  </td>
 </tr>
EOF;
}

$IPBHTML .= <<<EOF
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>COPPA 帐户</strong></td>
  <td class='homepage_sub_row'>
  	<a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mtools&amp;code=mod&amp;filter=coppa'>管理</a>
  	(<strong>{$content['coppa']}</strong>)
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'><strong>主题</strong></td>
  <td class='homepage_sub_row'>
  	<strong>{$content['topics']}</strong>
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>等待审核</strong></td>
  <td class='homepage_sub_row'>
  	{$content['topics_mod']}
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'><strong>帖子</strong></td>
  <td class='homepage_sub_row'>
  	<strong>{$content['replies']}</strong>
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'>&nbsp;&nbsp;&#124;-<strong>等待审核</strong></td>
  <td class='homepage_sub_row'>
  	{$content['posts_mod']}
  </td>
 </tr>
 <tr>
  <td class='homepage_sub_row'><strong>上传</strong></td>
  <td class='homepage_sub_row'><strong><em><div id='uploads-size'><i>正在上传...</i></div></em></strong></td>
 </tr>

 </table>
</div>

<script type='text/javascript'>
function get_uploads_size()
{
	var content = document.getElementById( 'uploads-size' );

	/*--------------------------------------------*/
	// Main function to do on request
	// Must be defined first!!
	/*--------------------------------------------*/
	
	do_request_function = function()
	{
		//----------------------------------
		// Ignore unless we're ready to go
		//----------------------------------
		
		if ( ! xmlobj.readystate_ready_and_ok() )
		{
			// Could do a little loading graphic here?
			return;
		}
		
		//----------------------------------
		// INIT
		//----------------------------------
		
		var returned = xmlobj.xmlhandler.responseText;
		content.innerHTML = returned;
	}
	
	//----------------------------------
	// LOAD XML
	//----------------------------------
	
	xmlobj = new ajax_request();
	xmlobj.onreadystatechange( do_request_function );
	
	xmlobj.process( ipb_var_base_url + '&act=xmlout&do=get-dir-size' );
	
	return false;
}
	
get_uploads_size();

</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_php_version_warning( $phpversion ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	易维论坛需要最低 PHP 4.3.0 版本环境来实现所有的功能.<br />如果您不进行升级一些后台管理操作将无法实现.
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function acp_ftext_warning( $a, $b ) {

$IPBHTML = "";
//--starthtml--//

if( $a )
{
$IPBHTML .= <<<EOF
	您的数据库服务器支持全文搜索, 这种搜索方式比手工搜索具有更好的性能表现, 然而在这种环境中您将无法创建全文索引.
	<br /><br />
	创建全文索引请阅读 <a href='{$this->ipsclass->base_url}&section=tools&act=op&code=dofulltext'>这里</a> (请注意您可以 <a href='{$this->ipsclass->base_url}&section=help&act=support&code=support'>提交支持请求</a> 来寻求官方帮助)
	<br /><br />
EOF;
}

if( $b )
{
	$query = urlencode( '搜索使用类型' );
$IPBHTML .= <<<EOF
	我们检测到全文搜索在您的论坛中没有开启. 如果您创建了全文索引, 您必须开启 <a href='{$this->ipsclass->base_url}&section=tools&act=op&code=setting_view&search={$query}'>这一设置</a> 来使用这一功能.
EOF;
}

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Index
//===========================================================================
function warning_box($title, $content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='homepage_pane_warning_border'>
 <div class='homepage_section_warning'>$title</div>
 $content
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function warning_unlocked_installer() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	请立即在您的服务器删除 <b>install/index.php</b> 文件!
  	<br />保留这一文件将给您的系统造成安全隐患.
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Index
//===========================================================================
function warning_upgrade() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	IPB 检测到您的升级过程尚未完成. 请点击 <a href='{$this->ipsclass->vars['board_url']}/upgrade/index.php'>这里</a> 来完成升级.
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function warning_installer() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	虽然我们已经锁定了安装系统, 但是我们仍然建议您进行删除操作.
   	<br />您只需删除 <b>install/index.php</b> 文件即可.
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function warning_rebuild_emergency() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
   您或者您的会员遇到了主题错误. 下面的动作自动完成了:
   <ul>
	<li>他们已经被告知清除他们的主题设置</li>
	<li>他们被告知点击链接来尝试进入后台</li>
	<li>系统后台已经记录主题错误并且重建主题文件缓存, 启用默认主题并且转而采用安全模式</li>
   </ul>
   <b>下一步怎么做</b>
   <ul>
	<li>首先, 如果您不希望启用安全模式的主题, 请设置 'skin_cache' 文件夹属性为可写这样系统才能够向文件夹中写入主题文件缓存</li>
	<li>如果文件夹权限正确, 请您检查 '系统设置 -&gt; 通用系统设置' 点击禁用 '安全模式主题' - 如果您不需要这一设置</li>
	<li>As a precaution, rebuild all your skins by following the link below</li>
   </ul>
   <b>&gt;&gt; <a href='{$this->ipsclass->base_url}&section=lookandfeel&act=sets&code=rebuildalltemplates&removewarning=1'>重建所有主题缓存 & 移除这一警告</a> &lt;&lt;</b>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function warning_rebuild_upgrade() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
   您将需要更新所有主题缓存来确保新的模板项可以正确添加.
   <br /><br /><b>&gt;&gt; <a href='{$this->ipsclass->base_url}&section=lookandfeel&act=sets&code=rebuildalltemplates&removewarning=1'>重建所有主题缓存 & 移除这一警告</a> &lt;&lt;</b>
EOF;

//--endhtml--//
return $IPBHTML;
}

}

?>