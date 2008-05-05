<?php

class cp_skin_member {

var $ipsclass;

//===========================================================================
// MEMBER FORM
//===========================================================================
function member_form($mem, $form) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/skin_acp/clientscripts/ipd_form_functions.js'></script>
<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/skin_acp/clientscripts/ipd_tab_factory.js'></script>
<script type="text/javascript">
//<![CDATA[
// INIT FORM FUNCTIONS stuff
var formfunctions = new form_functions();
// INIT TAB FACTORY stuff
var tabfactory    = new tab_factory();

var show   = '';
{$form['_perm_masks_js']};
function saveit(f)
{
	show = '';
	for (var i = 0 ; i < f.options.length; i++)
	{
		if (f.options[i].selected)
		{
			tid  = f.options[i].value;
			show += '\\n' + eval('perms_'+tid);
		}
	}
	
	if ( show != '' )
	{
		document.forms[0].override.checked = true;
	}
	else
	{
		document.forms[0].override.checked = false;
	}
}

function show_me()
{
	if (show == '')
	{
		show = '未检测到更改\\n点击多选框来激活';
	}
	
	alert('Selected Permission Sets\\n---------------------------------\\n' + show);
}
//]]>
</script>
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=doedit&amp;mid={$mem['id']}' id='mainform' onsubmit='ValidateForm()' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<input type='hidden' name='curemail' value='{$mem['email']}' />
<input type='hidden' name='curgroup' value='{$mem['mgroup']}' />
{$form['_custom_hidden_fields']}
<div class='tabwrap'>
	<div id='tabtab-1' class='taboff'>通用设置</div>
	<div id='tabtab-2' class='taboff'>帖子 &amp; 存取限制</div>
	<div id='tabtab-3' class='taboff'>论坛 &amp; 资料信息</div>
	<div id='tabtab-4' class='taboff'>签名</div>
	<div id='tabtab-5' class='taboff'>关于我</div>
	<div id='tabtab-6' class='taboff'>自定义字段</div>
</div>
<div class='tabclear'>编辑: {$mem['members_display_name']} <span style='font-weight:normal'>(ID: {$mem['id']})</span></div>
<div class='tableborder'>
<div id='tabpane-1' class='formmain-background'>
 	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td>
		<fieldset class='formmain-fieldset'>
		    <legend><strong>{$mem['members_display_name']}</strong></legend>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
				<td width='1%' class='tablerow1'>
					<div style='border:1px solid #000;background:#FFF;width:{$mem['pp_main_width']}px; padding:15px'>
						<img src="{$mem['pp_main_photo']}" width='{$mem['pp_main_width']}' height='{$mem['pp_main_height']}' />
					</div>
				</td>
				<td>
					
					     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
						 <tr>
						   <td width='40%' class='tablerow1'><strong>注册 IP 地址</strong></td>
						   <td width='60%' class='tablerow2'>
								<a href='{$this->ipsclass->base_url}&amp;section={$this->ipsclass->section_code}&amp;act=mtools&amp;code=learnip&amp;ip={$mem['ip_address']}' title='查找更多此 IP 地址的会员...'>{$mem['ip_address']}</a>
								[ <a href='{$this->ipsclass->base_url}&amp;section={$this->ipsclass->section_code}&amp;act=mtools&amp;code=showallips&amp;member_id={$mem['id']}'>显示全部 IP 地址</a> ]
						   </td>
						  </tr>
						  <tr>
							<td width='40%' class='tablerow1'><strong>邮件地址</strong></td>
							<td width='60%' class='tablerow2'>{$form['email']}</td>
						  </tr>
						  <tr>
							<td width='40%' class='tablerow1'><strong>帖子数目</strong></td>
							<td width='60%' class='tablerow2'>{$form['posts']}</td>
						  </tr>
						  <tr>
							<td width='40%' class='tablerow1'><strong>删除会员照片</strong></td>
							<td width='60%' class='tablerow2'>{$form['remove_photo']}</td>
						  </tr>
						  <tr>
							<td width='40%' class='tablerow1'><strong>删除会员头像</strong></td>
							<td width='60%' class='tablerow2'>{$form['remove_avatar']}</td>
						 </tr>
						 <tr>
							<td width='40%' class='tablerow1'><strong>警告等级调整</strong></td>
							<td width='60%' class='tablerow2'>
								{$form['warn_level']}
								[ <a href='#' onclick="ipsclass.pop_up_window('{$this->ipsclass->vars['board_url']}/index.php?act=warn&amp;mid={$mem['id']}&amp;CODE=view','500','450'); return false;">查看会员记录</a> ]
								[ <a href='#' onclick="ipsclass.pop_up_window('{$this->ipsclass->vars['board_url']}/index.php?act=warn&amp;mid={$mem['id']}&amp;CODE=add_note','500','450'); return false;">添加新记录</a> ]
							</td>
						 </tr>
						 <tr>
							<td width='40%' class='tablerow1'><strong>会员名称</strong></td>
							<td width='60%' class='tablerow2'>{$form['member_title']}</td>
						 </tr>
						 </table>
					   
				</td>
			 </tr>
			</table>
			
			
		</fieldset>
		
		<br />
		
		<fieldset class='formmain-fieldset'>
		    <legend><strong>用户组选项</strong></legend>
EOF;
if ( $form['_show_fixed'] != TRUE )
{
$IPBHTML .= <<<EOF
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
			   <td width='40%' class='tablerow1'><strong>初始用户组</strong><div style='color:gray'>会员资料中所显示的用户组</div></td>
			   <td width='60%' class='tablerow2'>{$form['mgroup']}</td>
			  </tr>
			  <tr>
				<td width='40%' class='tablerow1'><strong>二级用户组</strong><br />您可以选择多个用户组.<div style='color:gray'>会员将会继承 '最宽泛' 的权限设置并且将会继承所有的开启权限.</div></td>
				<td width='60%' class='tablerow2'>{$form['mgroup_others']}</td>
			 </tr>
			 </table>
EOF;
}
else
{
$IPBHTML .= <<<EOF
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
			   <td width='40%' class='tablerow1'><strong>初始用户组</strong></td>
			   <td width='60%' class='tablerow2'>{$form['_mgroup']}<b>系统管理员或论坛管理员</b> (不可改变)</td>
			  </tr>
			 </table>
EOF;
}


$IPBHTML .= <<<EOF
		</fieldset>
		</td>
	</tr>
	</table>
</div>
<div id='tabpane-2' class='formmain-background'>
	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td>
		<fieldset class='formmain-fieldset'>
		    <legend><strong>发帖和登录限制</strong></legend>
		     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
			 <tr>
			   <td width='40%' class='tablerow1'><strong>用...覆盖用户组权限</strong><br />您可以选择多个用户组.<div style='color:gray'>覆盖将废除所有初始用户组和二级用户组的权限设置.</div></td>
			   <td width='60%' class='tablerow2'>
					<input type='checkbox' name='override' {$form['_permid_tick']} value='1' > <b>强制会员使用所选的权限设置...</b><br />
					{$form['permid']}
					<br><input style='margin-top:5px' id='editbutton' type='button' onclick='show_me();' value='给我展示所选权限'>
			   </td>
			  </tr>
			  <tr>
				<td width='40%' class='tablerow1'><strong>强制版主预览所有该会员帖子?</strong><div style='color:gray'>如果开启, 所有该会员发表的帖子都将发到版主审核列表. 取消选择并且清除数值来移除这一设置.</div></td>
				<td width='60%' class='tablerow2'>
					<input type='checkbox' name='mod_indef' value='1' {$form['_mod_tick']}> 版主无限期预览
					<br />
					<strong>或者</strong>
					{$form['mod_timespan']} {$form['mod_units']} {$form['_mod_extra']}
				</td>
			 </tr>
			 <tr>
				<td width='40%' class='tablerow1'><strong>禁止 {$mem['members_display_name']} 发表权限?</strong><div style='color:gray'>取消选择并且清除数值来移除这一设置.</div></td>
				<td width='60%' class='tablerow2'>
					<input type='checkbox' name='post_indef' value='1' {$form['_post_tick']}> 永久限制发帖权限
					<br />
					<strong>或者</strong>
					{$form['post_timespan']} {$form['post_units']} {$form['_post_extra']}
				</td>
			 </tr>
			 </table>
		 </fieldset>
		</td>
	</tr>
	</table>
</div>
<div id='tabpane-3' class='formmain-background'>
	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td>
		<fieldset class='formmain-fieldset'>
		    <legend><strong>论坛设置</strong></legend>
		     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
			  <tr>
				<td width='40%' class='tablerow1'><strong>语言选择</strong></td>
				<td width='60%' class='tablerow2'>{$form['language']}</td>
			 </tr>
			 <tr>
				<td width='40%' class='tablerow1'><strong>主题选择</strong></td>
				<td width='60%' class='tablerow2'><select name='skin' class='dropdown'><option value='0'>--无 / 使用默认设置--</option>{$form['_skin_list']}</select></td>
			 </tr>
			 <tr>
				<td width='40%' class='tablerow1'><strong>隐藏该会员邮件地址?</strong></td>
				<td width='60%' class='tablerow2'>{$form['hide_email']}</td>
			 </tr>
			 <tr>
				<td width='40%' class='tablerow1'><strong>邮件发送短消息提醒?</strong></td>
				<td width='60%' class='tablerow2'>{$form['email_pm']}</td>
			 </tr>
			 <tr>
				<td width='40%' class='tablerow1'><strong>{$this->ipsclass->acp_lang['mem_edit_pm_title']}</strong></td>
				<td width='60%' class='tablerow2'>{$form['members_disable_pm']}</td>
			 </tr>
			 </table>
		   </fieldset>
		
			<br />
			
			<fieldset class='formmain-fieldset'>
			    <legend><strong>联系信息</strong></legend>
			     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
					<td width='40%' class='tablerow1'><strong>帐号: AIM</strong></td>
					<td width='60%' class='tablerow2'>{$form['aim_name']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>帐号: MSN</strong></td>
					<td width='60%' class='tablerow2'>{$form['msnname']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>帐号: Yahoo!</strong></td>
					<td width='60%' class='tablerow2'>{$form['yahoo']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>帐号: ICQ</strong></td>
					<td width='60%' class='tablerow2'>{$form['icq_number']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>个人主页</strong></td>
					<td width='60%' class='tablerow2'>{$form['website']}</td>
				 </tr>
				 </table>
		   </fieldset>
		
			<br />
			
			<fieldset class='formmain-fieldset'>
			    <legend><strong>个人资料信息</strong></legend>
			     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
					<td width='40%' class='tablerow1'><strong>头像地址</strong></td>
					<td width='60%' class='tablerow2'>{$form['avatar']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>头像类型</strong></td>
					<td width='60%' class='tablerow2'>{$form['avatar_type']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>头像大小</strong></td>
					<td width='60%' class='tablerow2'>{$form['avatar_size']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>地址</strong></td>
					<td width='60%' class='tablerow2'>{$form['location']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>爱好</strong></td>
					<td width='60%' class='tablerow2'>{$form['interests']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>生日</strong></td>
					<td width='60%' class='tablerow2'>{$form['birthday']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>性别</strong></td>
					<td width='60%' class='tablerow2'>{$form['pp_gender']}</td>
				 </tr>
				 <tr>
					<td width='40%' class='tablerow1'><strong>个人状态</strong></td>
					<td width='60%' class='tablerow2'>{$form['pp_bio_content']}</td>
				 </tr>
				 </table>
		   </fieldset>
		
	   </td>
     </tr>
    </td>
	</table>
</div>
<div id='tabpane-4' class='formmain-background'>
	<fieldset class='formmain-fieldset'>
	    <legend><strong>会员 关于我 信息</strong></legend>
		{$form['aboutme']}
	</fieldset>
</div>
<div id='tabpane-6' class='formmain-background'>
	<fieldset class='formmain-fieldset'>
	    <legend><strong>会员签名</strong></legend>
		{$form['signature']}
	</fieldset>
</div>
<div id='tabpane-5' class='formmain-background'>
	<fieldset class='formmain-fieldset'>
	    <legend><strong>自定义资料项</strong></legend>
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			{$form['_custom_fields']}
		</table>
	</fieldset>
</div>
<div align='center' class='tablefooter'>
 	<div class='formbutton-wrap'>
 		<div id='button-save'><img src='{$this->ipsclass->skin_acp_url}/images/icons_form/save.gif' border='0' alt='保存'  title='保存' class='ipd-alt' /> 保存会员</div>
	</div>
</div>
</div>
</form>
<script type="text/javascript">
//<![CDATA[
// Init form functions, grab stuff
formfunctions.init();
// Pass ID name of FORM tag
formfunctions.name_form = 'mainform';
formfunctions.add_submit_event( 'button-save' );
// Stuff. Well done Matt
tabfactory.init_tabs();
//]]>
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Member: delete stuff start
//===========================================================================
function member_delete_posts_start( $member, $topics, $posts ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=deleteposts_process&amp;mid={$member['id']}' method='POST'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>{$this->ipsclass->acp_lang['mem_delete_posts_title']} {$member['members_display_name']}</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablerow1' width='90%'><strong>{$this->ipsclass->acp_lang['mem_delete_delete_posts']}</strong><div class='desctext'>{$this->ipsclass->acp_lang['mem_delete_delete_posts_desc']}</div></td>
  <td class='tablerow2' width='10%'><input type='checkbox' value='1' name='dposts' /></td>
 </tr>
 <tr>
  <td class='tablerow1' width='90%'><strong>{$this->ipsclass->acp_lang['mem_delete_delete_topics']}</strong><div class='desctext'>{$this->ipsclass->acp_lang['mem_delete_delete_topics_desc']}</div></td>
  <td class='tablerow2' width='10%'><input type='checkbox' value='1' name='dtopics' /></td>
 </tr>
 <tr>
  <td class='tablerow1' width='90%'><strong>{$this->ipsclass->acp_lang['mem_delete_posts_trash']}</strong><div class='desctext'>{$this->ipsclass->acp_lang['mem_delete_posts_trash_desc']}</div></td>
  <td class='tablerow2' width='10%'><input type='checkbox' value='1' name='use_trash_can' /></td>
 </tr> 
 <tr>
  <td class='tablerow1' width='90%'><strong>{$this->ipsclass->acp_lang['mem_delete_delete_pergo']}</strong><div class='desctext'>{$this->ipsclass->acp_lang['mem_delete_delete_pergo_desc']}</div></td>
  <td class='tablerow2' width='10%'><input type='input' value='50' size='3' name='dpergo' /></td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='{$this->ipsclass->acp_lang['mem_delete_process']}' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Member: validating
//===========================================================================
function member_validating_wrapper($content, $st, $new_ord, $links) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript">
//<![CDATA[
 function check_boxes()
 {
 	var ticked = document.getElementById('maincheckbox').checked;
 	
 	var checkboxes = document.getElementsByTagName('input');

	for ( var i = 0 ; i < checkboxes.length ; i++ )
	{
		var e = checkboxes[i];
		
		if ( e.type == 'checkbox')
		{
			var boxname  = e.id;
			var boxcheck = boxname.replace( /^(.+?)_.+?$/, "$1" );
			
			if ( boxcheck == 'mid' )
			{
				e.checked = ticked;
			}
		}
	}
 }

//]]>
</script>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='40%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>会员激活列表</td>
  <td align='right' width='60%'>
   <form name='selectform' id='selectform' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=mod' method='post'>
   <input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
   <select name='filter' class='dropdown'>
    <option value='all'>显示所有</option>
    <option value='reg_user_validate'>显示已经注册 (会员确认)</option>
    <option value='reg_admin_validate'>显示正在注册 (管理员确认)</option>
    <option value='email_chg'>邮件更改</option>
    <option value='coppa'>未成年注册请求</option>
   </select>
   <input type='submit' class='realbutton' value=' 提交 &gt;' />
   </form>
  </td>
 </tr>
 </table>
 </div>
 <form name='theAdminForm' id='adminform' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=domod' method='post'>
 <input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
 <table cellpadding='4' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='20%'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=mod&amp;st=$st&amp;sort=mem&amp;ord=$new_ord'>会员显示名称</a></td>
  <td class='tablesubheader' width='15%'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mod&st=$st&sort=email&ord=$new_ord'>邮件地址</a></td>
  <td class='tablesubheader' width='20%'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mod&st=$st&sort=sent&ord=$new_ord'>邮件发送</a></td>
  <td class='tablesubheader' width='5%' align='center'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mod&st=$st&sort=posts&ord=$new_ord'>帖子数目</a></td>
  <td class='tablesubheader' width='15%'><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=mod&st=$st&sort=reg&ord=$new_ord'>注册日期</a></td>
  <td class='tablesubheader' width='1%'><input type='checkbox' id='maincheckbox' onclick='check_boxes()' /></td>
 </tr>
 {$content}
 <tr>
  <td class='tablesubheader' colspan='2' align='left'>{$links}</td>
  <td class='tablesubheader' colspan='4' align='right'>
   <select name='type' class='dropdown'><option value='approve'>批准这些帐号</option><option value='delete'>删除这些帐号</option><option value='resend'>重新发送确认邮件</option></select>
   <input type='submit' class='realbutton' value=' 执行 &gt;&gt;' />
  </td>
 </tr>
 </table>
 </form>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Member: validating
//===========================================================================
function member_validating_row( $r="" ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
  <td class='tablerow2'><a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['id']}'><strong>{$r['members_display_name']}</strong></a>{$r['_coppa']}<div class='desctext'>IP 地址: <a href='{$this->ipsclass->base_url}&section=content&act=mtools&code=learnip&ip={$r['ip_address']}'>{$r['ip_address']}</a></div></td>
  <td class='tablerow1'>{$r['email']}</td>
  <td class='tablerow1'><span style='color:green'>{$r['_where']}</span><br />{$r['_entry']}<div class='desctext'>{$r['_days']} 天和 {$r['_rhours']} 小时以前</div></td>
  <td class='tablerow1' align='center'>{$r['posts']}</td>
  <td class='tablerow1'>{$r['_joined']}</td>																
  <td class='tablerow1' align='center'><input type='checkbox' id="mid_{$r['member_id']}" name='mid_{$r['member_id']}' value='1' /></td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Member: locked account
//===========================================================================
function member_locked_wrapper($content, $st, $links) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript">
//<![CDATA[
 function check_boxes()
 {
 	var ticked = document.getElementById('maincheckbox').checked;
 	
 	var checkboxes = document.getElementsByTagName('input');

	for ( var i = 0 ; i < checkboxes.length ; i++ )
	{
		var e = checkboxes[i];
		
		if ( e.type == 'checkbox')
		{
			var boxname  = e.id;
			var boxcheck = boxname.replace( /^(.+?)_.+?$/, "$1" );
			
			if ( boxcheck == 'mid' )
			{
				e.checked = ticked;
			}
		}
	}
 }

//]]>
</script>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='100%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>锁定会员列表</td>
 </tr>
 </table>
 </div>
 <form name='theAdminForm' id='adminform' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=unlock' method='post'>
 <input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
 <table cellpadding='4' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='20%'>显示名称</td>
  <td class='tablesubheader' width='15%'>邮件地址</td>
  <td class='tablesubheader' width='20%'>失败登录</td>
  <td class='tablesubheader' width='5%' align='center'>帖子数目</td>
  <td class='tablesubheader' width='15%'>加入时间</td>
  <td class='tablesubheader' width='1%'><input type='checkbox' id='maincheckbox' onclick='check_boxes()' /></td>
 </tr>
 {$content}
 <tr>
  <td class='tablesubheader' colspan='2' align='left'>{$links}</td>
  <td class='tablesubheader' colspan='4' align='right'>
   <select name='type' class='dropdown'><option value='unlock'>解除这些帐号锁定</option><option value='ban'>禁止这些帐号</option></select>
   <input type='submit' class='realbutton' value=' 执行 &gt;&gt;' />
  </td>
 </tr>
 </table>
 </form>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Member: No rows/ setting disabled
//===========================================================================
function member_locked_no_rows( $lang ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
  <td class='tablerow2' colspan='6'><strong>{$lang}</strong></td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Member: Locked account row
//===========================================================================
function member_locked_row( $r="" ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
  <td class='tablerow2'><strong>{$r['members_display_name']}</strong><div class='desctext'>用户组: {$r['group_title']}</div><div class='desctext'>{$r['ip_addresses']}</div></td>
  <td class='tablerow1'>{$r['email']}</td>
  <td class='tablerow1'>Oldest Failure: {$r['oldest_fail']}<br />最近登录失败: {$r['newest_fail']}<br />总计失败次数: {$r['failed_login_count']}</td>
  <td class='tablerow1' align='center'>{$r['posts']}</td>
  <td class='tablerow1'>{$r['_joined']}</td>																
  <td class='tablerow1' align='center'><input type='checkbox' id="mid_{$r['id']}" name='mid_{$r['id']}' value='1' /></td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


}

?>