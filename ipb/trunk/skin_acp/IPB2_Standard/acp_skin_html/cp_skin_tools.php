<?php

class cp_skin_tools {

var $ipsclass;


//===========================================================================
// Menu manage:Blank Pos
//===========================================================================
function components_position_blank($com_id) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<img src='{$this->ipsclass->skin_acp_url}/images/blank.gif' width='12' height='12' border='0' style='vertical-align:middle' />
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Menu manage:Blank Pos
//===========================================================================
function components_position_up($com_id) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_move&amp;move=up&amp;com_id={$com_id}' title='Move up in position'><img src='{$this->ipsclass->skin_acp_url}/images/arrow_up.png' width='12' height='12' border='0' style='vertical-align:middle' /></a>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Menu manage:Blank Down
//===========================================================================
function components_position_down($com_id) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_move&amp;move=down&amp;com_id={$com_id}' title='Move down in position'><img src='{$this->ipsclass->skin_acp_url}/images/arrow_down.png' width='12' height='12' border='0' style='vertical-align:middle' /></a>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Component FORM
//===========================================================================
function components_form($form, $title, $formcode, $button, $component, $menu_text, $menu_url, $menu_redirect, $menu_permbit, $menu_permlang) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->skin_acp_url}/acp_components.js'></script>
<script type="text/javascript">

  var comp = new components();
  // Title
  var menu_text     =
  {
{$menu_text}
  };

  var menu_url      =
  {
{$menu_url}
  };

  var menu_redirect =
  {
{$menu_redirect}
  };

   var menu_permbit =
  {
{$menu_permbit}
  };

   var menu_permlang =
  {
{$menu_permlang}
  };

  // HTML elements
  var html_add_menu_row     = "<br />[<a href='#' title='添加新菜单项目' style='color:green;font-weight:bold' onclick='return comp.add_menu_row()'>添加新菜单项目</a>]";
  var html_box_text         = "<tr><td width='20%'>菜单文字</td><td><input type='text' id='menu_text_<%1>' name='menu_text[<%1>]' size='30' class='forminput' value='<%2>' /></td></tr>";
  var html_box_url          = "<tr><td width='20%'>菜单 URL 地址</td><td><input type='text' id='menu_url_<%1>' name='menu_url[<%1>]' size='30' class='forminput' value='<%2>' /></td></tr>";
  var html_box_redirect     = "<tr><td width='20%'>菜单重定位?</td><td><input type='checkbox' id='menu_redirect_<%1>' name='menu_redirect[<%1>]' class='forminput' value='1' /></td></tr>";
  var html_box_permbit      = "<tr><td width='20%'>菜单固定项</td><td><input type='text' id='menu_permbit_<%1>' name='menu_permbit[<%1>]' size='30' class='forminput' value='<%2>' /></td></tr>";
  var html_box_permlang     = "<tr><td width='20%'>菜单固定语言</td><td><input type='text' id='menu_permlang_<%1>' name='menu_permlang[<%1>]' size='30' class='forminput' value='<%2>' /></td></tr>";
  var html_menu_wrap        = "<div><fieldset style='padding:4px'><table cellpadding='2' cellspacing='0' width='100%'><tr><%1></tr></table>[<a href='#' title='删除菜单项' style='color:red;font-weight:bold' onclick='return comp.remove_menu_row("+'"'+'<%2>'+'"'+")'>删除菜单项</a>]</fieldset></div>";
 </script>
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;com_id={$component['com_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>$title</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件标题</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_title']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件版本</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_version']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件简介</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_description']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件作者</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_author']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件官方地址</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_url']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1' valign='top'><strong>组件后台菜单数据</strong>
   	<div class='desctext'>
   	<a href='#' onclick='pop_win("act=quickhelp&id=comp_menu", "help", 250, 400 )'>点击这里查看更多这一设置的帮助信息</a>
   	</div>
   </td>
   <td width='60%' class='tablerow2'>
   <div id='components-menu-box'>

   </div>
   <!--{$form['com_menu_data']}-->

   </td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>论坛组件头部 URL 地址</strong><div class='desctext'>{ipb.base_url} 将会转换为论坛根目录地址 (完整包含 Session ID, 等)</div></td>
   <td width='60%' class='tablerow2'>{$form['com_url_uri']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>论坛组件头部名称</strong><div class='desctext'>{ipb.lang['some_words']} 将会转换为 lang_global.php 文件中的相应 "some_words" 条目</div></td>
   <td width='60%' class='tablerow2'>{$form['com_url_title']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件开启</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_enabled']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>组件部分代码</strong><div class='desctext'>用来加载相应文件的组件部分代码. 组件代码必须唯一, 并且与 component_* 文件的文件名相同 (去掉 .php 后缀). </div></td>
   <td width='60%' class='tablerow2'>{$form['com_section']}</td>
 </tr>
 <!--<tr>
   <td width='40%' class='tablerow1'><strong>组件 PHP 文件地址</strong><div class='desctext'>在 'sources/components_*/' 文件夹中的 PHP 文件地址</div></td>
   <td width='60%' class='tablerow2'>{$form['com_filename']}<strong>.php</strong></td>
 </tr>-->
EOF;
//startif
if ( $form['com_safemode'] != '' )
{
$IPBHTML .= <<<EOF
<tr>
   <td width='40%' class='tablerow1'><strong>开启安全模式? (不能被会员编辑和删除)</strong></td>
   <td width='60%' class='tablerow2'>{$form['com_safemode']}</td>
 </tr>
EOF;
}//endif
$IPBHTML .= <<<EOF
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='$button' /></div>
</div>
 <script type="text/javascript">
  comp.init();
 </script>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// COMPONENTS: Overview
//===========================================================================
function component_overview( $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>注册组件</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='30%'>名称</td>
  <td class='tablesubheader' width='25%'>作者</td>
  <td class='tablesubheader' width='5%' align='center'>位置</td>
  <td class='tablesubheader' width='5%'>开启</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
<br />
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_import' enctype='multipart/form-data' method='POST'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<input type='hidden' name='MAX_FILE_SIZE' value='10000000000' />
<div class='tableborder'>
 <div class='tableheaderalt'>导入 XML 组件设置文件</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td class='tablerow1'><strong>从您的电脑中上传 XML 组件设置文件</strong><div class='desctext'>不会覆盖重复的项目, 但是默认设置和其他选项将被更新. 文件必须以 "ipd_" 开头并且以 ".xml" 或者 ".xml.gz" 结尾.</div></td>
  <td class='tablerow2'><input class='textinput' type='file' size='30' name='FILE_UPLOAD' /></td>
 </tr>
 <tr>
  <td class='tablerow1'><strong><u>或者</u> 输入 XML 组件设置文件名</strong><div class='desctext'>文件必须已经上传到论坛根目录</div></td>
  <td class='tablerow2'><input class='textinput' type='text' size='30' name='file_location' /></td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='导入' /></div>
</div>
</form>

 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_add   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_add'>注册新组件...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// COMPONENTS: row
//===========================================================================
function component_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><strong>{$data['_fullname']}</strong><div class='desctext'>{$data['com_description']}</td>
 <td class='tablerow2'>{$data['_fullauthor']}</td>
 <td class='tablerow2' align='center' nowrap='nowrap'>{$data['_pos_up']} &nbsp; {$data['_pos_down']}</td>
 <td class='tablerow2' align='center'>
  <a href={$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_toggle_enabled&amp;com_id={$data['com_id']}' title='Toggle Enabled/Disabled'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['_enabled_img']}' border='0' alt='YN' class='ipd' /></a>
 </td>
 <td class='tablerow1'><img id="menu{$data['com_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['com_id']}",
  new Array(
EOF;
//startif
if ( ! $data['com_safemode'] OR ( $data['com_safemode'] AND IN_DEV ) )
{
$IPBHTML .= <<<EOF
			img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_edit&amp;com_id={$data['com_id']}'>编辑组件...</a>",
			img_delete   + " <a href='#' onclick='maincheckdelete(\"{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_delete&amp;com_id={$data['com_id']}\",\"您确定要删除吗?\")'>删除组件...</a>",
EOF;
}//endif
//startif
if ( $this->ipsclass->member['mgroup'] == $this->ipsclass->vars['admin_group'] && $data['com_hasuninstall'] )
{
$IPBHTML .= <<<EOF
  			 img_delete   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_uninstall&amp;com_id={$data['com_id']}'>卸载组件...</a>",
EOF;
}//endif
$IPBHTML .= <<<EOF
  			 img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=component_export&amp;com_id={$data['com_id']}'>导出组件 XML...</a>"
  			 ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// CACHE: Overview
//===========================================================================
function cache_overview( $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>缓存内容</div>
  <div class='tablesubheader' style='padding-right:0px;height:25px'>
    <div style='float:right;padding-right:12px'>
     <span class='desctext'>大小</span> &nbsp;&nbsp;
     <img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' />
    </div>
    <div style='padding-top:6px'>缓存类型</div>
  </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=cache_update_all'>更新全部缓存...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// CACHE: row
//===========================================================================
function cache_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
 	<div style='float:left;'>
 	 <img src='{$this->ipsclass->skin_acp_url}/images/menu_item.gif' class='ipd' /> <strong>{$data['cs_key']}</strong><div class='desctext'>{$data['_desc']}</div>
 	</div>
 	 <div align='right' style='height:18px;padding:0px 5px 2px 0px;'>
	   <span class='desctext'>{$data['_size']}kb</span> &nbsp;
	   <img id="menu{$data['cs_key']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' />
	</div>
  </td>
 <script type="text/javascript">
 menu_build_menu(
  "menu{$data['cs_key']}",
  new Array( img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=cacheend&amp;cache={$data['cs_key']}'>更新缓存...</a>",
			 img_view   + " <a href='#' onclick='pop_win(\"{$this->ipsclass->form_code}&amp;code=viewcache&amp;cache={$data['cs_key']}\",\"预览\", 400,600)'>查看缓存内容...</a>"
		    ) );
 </script>
 </tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOGIN: Overview
//===========================================================================
function login_overview( $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>登录验证方法</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='90%'>标题</td>
  <td class='tablesubheader' width='5%' nowrap='nowrap'>已安装</td>
  <td class='tablesubheader' width='5%' nowrap='nowrap'>开启</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>

 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_add   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_add'>注册新登录方法...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOGIN: Overview
//===========================================================================
function login_diagnostics( $login=array() ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>Diagnostics for: {$login['login_title']}</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
  <tr>
   <td width='49%' valign='top'>
   <table cellpadding='0' cellspacing='0' width='100%'>
   <tr>
	<td class='tablesubheader' width='70%'>&nbsp;</td>
	<td class='tablesubheader' width='30%'>&nbsp;</td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>登录开启</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_enabled_img']}' border='0' alt='*' class='ipd' /></td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>登录安装</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_installed_img']}' border='0' alt='*' class='ipd' /></td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>登录需要设置</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_has_settings']}' border='0' alt='*' class='ipd' /></td>
   </tr>
   </table>
   <div align='center' class='tablefooter'>&nbsp;</div>
  </td>
  <td width='2%' class='tablefooter'>&nbsp;</td>
  <td width='49%' valign='top'>
   <table cellpadding='0' cellspacing='0' width='100%'>
   <tr>
	<td class='tablesubheader' width='60%'>文件名称</td>
	<td class='tablesubheader' width='20%' align='center'>已存在</td>
	<td class='tablesubheader' width='20%' align='center'>可写</td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>./sources/loginauth/{$login['login_folder_name']}/auth.php</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_file_auth_exists']}' border='0' alt='*' class='ipd' /></td>
    <td class='tablerow2' align='center'>-</td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>./sources/loginauth/{$login['login_folder_name']}/acp.php</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_file_acp_exists']}' border='0' alt='*' class='ipd' /></td>
    <td class='tablerow2' align='center'>-</td>
   </tr>
   <tr>
    <td class='tablerow1'><strong>./sources/loginauth/{$login['login_folder_name']}/conf.php</strong></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_file_conf_exists']}' border='0' alt='*' class='ipd' /></td>
    <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$login['_file_conf_write']}' border='0' alt='*' class='ipd' /></td>
   </tr>
   </table>
   <div align='center' class='tablefooter'>&nbsp;</div>
  </td>
 </tr>
 </table>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOGIN FORM
//===========================================================================
function login_form($form, $title, $formcode, $button, $login) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;login_id={$login['login_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>$title</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录名称</strong></td>
   <td width='60%' class='tablerow2'>{$form['login_title']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录描述</strong><div class='desctext'>输入简短的登录方法描述</div></td>
   <td width='60%' class='tablerow2'>{$form['login_description']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录文件夹名称</strong><div class='desctext'>登录 PHP 文件所处的文件夹.<br />例如: 如果文件为 ./sources/loginauth/<strong>internal</strong>/auth.php 就输入: internal</div></td>
   <td width='60%' class='tablerow2'>{$form['login_folder_name']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录会员维护 URL 地址</strong><div class='desctext'>会员修改自己邮件、密码等的 URL 地址.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_maintain_url']} <div class='desctext'>(对 优先本地验证 验证方式为可选)</div></td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录会员注册 URL 地址</strong><div class='desctext'>注册新会员帐号的 URL 地址.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_register_url']} <div class='desctext'>(对于 优先本地验证 验证方式为可选)</div></td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录验证类型?</strong><div class='desctext'>强制远程验证: 这一登录方式将只对远程数据库进行验证检索. 所有的帐号必须保存在远程数据库中.<br />优先本地验证: 如果本地数据库查找不到相关帐号, IPB 才会检索远程数据库.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_type']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录表单 HTML 代码</strong><div class='desctext'>输入登录表单的 HTML 代码来添加或代替论坛默认的登录表单.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_alt_login_html']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录 HTML 替换表单</strong><div class='desctext'>如果选择 '是' 上面的 HTML 代码将会替换论坛默认登录表单. 如果选择 '否' 它将会显示在论坛登录表单旁边.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_replace_form']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>会员登录 URL 地址</strong><div class='desctext'>会员登录表单的 URL 地址.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_login_url']} <div class='desctext'></div></td>
 </tr>
<tr>
   <td width='40%' class='tablerow1'><strong>会员退出 URL 地址</strong><div class='desctext'>会员推出表单的 URL 地址.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_logout_url']} <div class='desctext'></div></td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录开启</strong><div class='desctext'>如果选择 '是', 这一登录方式将会开启并替换现有登录方式.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_enabled']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>登录会员名称类型</strong><div class='desctext'>选择登录会员名称表单所需要填写的类型. 这一类型在很多登录模块中可能是固定的.</div></td>
   <td width='60%' class='tablerow2'>{$form['login_user_id']}</td>
 </tr>
  <!--<tr>
   <td width='40%' class='tablerow1'><strong>登录需要设置</strong><div class='desctext'>如果选择 '是', 全部 acp.php 和 conf.php 文件都必须已经存在并且可以被 IPB 程序写入 (请使用诊断来检查).</div></td>
   <td width='60%' class='tablerow2'>{$form['login_settings']}</td>
 </tr>-->
EOF;
//startif
if ( $form['login_safemode'] != '' )
{
$IPBHTML .= <<<EOF
<tr>
   <td width='40%' class='tablerow1'><strong>开启安全模式?</strong><div class='desctext'>无法被会员删除或编辑</div></td>
   <td width='60%' class='tablerow2'>{$form['login_safemode']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>标记为已安装?</strong><div class='desctext'>无法被会员删除或编辑</div></td>
   <td width='60%' class='tablerow2'>{$form['login_installed']}</td>
 </tr>
EOF;
}//endif
$IPBHTML .= <<<EOF
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='$button' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOGIN: row
//===========================================================================
function login_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><img src='{$this->ipsclass->skin_acp_url}/images/lock_close.gif' border='0' alt='*' class='ipd' /></td>
 <td class='tablerow1'><strong>{$data['login_title']}</strong><div class='desctext'>{$data['login_description']}</div></td>
 <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['_installed_img']}' border='0' alt='YN' class='ipd' /></td>
 <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['_enabled_img']}' border='0' alt='YN' class='ipd' /></td>
 <td class='tablerow1'><img id="menu{$data['login_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['login_id']}",
  new Array(
EOF;
//startif
if ( ($data['login_safemode'] AND IN_DEV) or $data['login_installed'] )
{
$IPBHTML .= <<<EOF
			 img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_edit_details&amp;login_id={$data['login_id']}'>编辑细节...</a>",
EOF;
}//endif
//startif
if ( $data['login_installed'] != 1 )
{
$IPBHTML .= <<<EOF
			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_install&amp;login_id={$data['login_id']}'>安装...</a>",
EOF;
}//endif
//startif
if ( $data['login_installed'] == 1 ) // NOT USED??
{
//$IPBHTML .= <<<EOF
//			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_edit_settings&amp;login_id={$data['login_id']}'>Edit Settings...</a>",
// 			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_export&amp;login_id={$data['login_id']}'>Export...</a>",
//EOF;
}//endif
$IPBHTML .= <<<EOF
			 img_view   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=login_diagnostics&amp;login_id={$data['login_id']}'>诊断...</a>"
  			 ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// PORTAL: Overview
//===========================================================================
function portal_pop_overview( $title, $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>{$this->ipsclass->acp_lang['portal_pop_tags']} {$title}</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='30%'>{$this->ipsclass->acp_lang['portal_pop_name']}</td>
  <td class='tablesubheader' width='70%'>{$this->ipsclass->acp_lang['portal_pop_desc']}</td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// PORTAL: row
//===========================================================================
function portal_pop_row( $tag, $desc ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>&lt;!--::<strong>{$tag}</strong>::--&gt;</td>
 <td class='tablerow1'><div class='desctext'>{$desc}</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// PORTAL: Overview
//===========================================================================
function portal_overview( $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>{$this->ipsclass->acp_lang['portal_main_title']}</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='95%'>{$this->ipsclass->acp_lang['portal_main_key']}</td>
  <td class='tablesubheader' width='5%'>&nbsp;</td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// PORTAL: row
//===========================================================================
function portal_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><img src='{$this->ipsclass->skin_acp_url}/images/menu.png' border='0' alt='选项' class='ipd' /></td>
 <td class='tablerow1'><strong>{$data['pc_title']}</strong><div class='desctext'>{$data['pc_desc']}</td>
 <td class='tablerow1'><img id="menu{$data['pc_key']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['pc_key']}",
  new Array(
EOF;
//startif
if ( $data['pc_settings_keyword'] )
{
$IPBHTML .= <<<EOF
			img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=portal_settings&amp;pc_key={$data['pc_key']}'>{$this->ipsclass->acp_lang['portal_row_menu_settings']}</a>",
EOF;
}//endif
$IPBHTML .= <<<EOF
  			 img_export   + " <a href='#'  onclick=\"menu_action_close(); pop_win('{$this->ipsclass->form_code}&amp;code=portal_viewtags&amp;pc_key={$data['pc_key']}', '{$data['pc_key']}', 400,200)\">{$this->ipsclass->acp_lang['portal_row_menu_view_tags']}</a>"
  			 ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}


}

?>