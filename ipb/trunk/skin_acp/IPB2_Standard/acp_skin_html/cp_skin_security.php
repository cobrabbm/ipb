<?php

class cp_skin_security {

var $ipsclass;

//===========================================================================
// DIAGNOSTICS
//===========================================================================
function list_admin_overview($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>具有后台登录权限的会员</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
	<td class='tablesubheader' width='30%'>名称</td>
	<td class='tablesubheader' width='30%'>原始组别</td>
	<td class='tablesubheader' width='30%'>附加组别</td>
	<td class='tablesubheader' width='30%'>IP 地址</td>
	<td class='tablesubheader' width='30%'>邮件地址</td>
	<td class='tablesubheader' width='30%'>帖子</td>
	<td class='tablesubheader' width='30%'>&nbsp;</td>
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
// DIAGNOSTICS
//===========================================================================
function list_admin_row( $member ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
	<strong>{$member['members_display_name']}</strong>
	<div class='desctext'>{$member['_joined']}</div>
 </td>
 <td class='tablerow1'>
	{$member['_mgroup']}
 </td>
 <td class='tablerow2'>
	{$member['_mgroup_others']}&nbsp;
 </td>
 <td class='tablerow1'>
	<div class='desctext'>{$member['ip_address']}</div>
 </td>
 <td class='tablerow2'>
	{$member['email']}
 </td>
 <td class='tablerow2'>
	{$member['posts']}
 </td>
 <td class='tablerow1'>
	<a href='{$this->ipsclass->base_url}&amp;section=content&amp;act=mem&amp;code=doform&amp;mid={$member['id']}'>编辑</a>
 </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}



//===========================================================================
// DIAGNOSTICS
//===========================================================================
function deep_scan_bad_files_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=deep_scan' method='POST'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div style='padding-bottom:10px'>Show: 
	<select name='filter'>
	    <option value='all'>显示全部</option>
		<option value='score-5'>等级 5 或更高</option>
		<option value='score-6'>等级 6 或更高</option>
		<option value='score-7'>等级 7 或更高</option>
		<option value='score-8'>等级 8 或更高e</option>
		<option value='score-9'>等级 9 或更高</option>
		<option value='large'>文件大小 55k 或更大</option>
		<option value='recent'>最近 30 天修改</option>
	</select>
	<input type='submit' value=' 过滤 ' />
</div>
</form>
<div class='tableborder'>
 <div class='tableheaderalt'>可执行文件</div>
 <div class='tablesubheader' style='padding-right:0px'>
  <div align='right' style='padding-right:10px'>
     (文件大小) &nbsp; 最近修改
    </div>
  </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
	$content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// DIAGNOSTICS
//===========================================================================
function deep_scan_bad_files_row( $file_path, $file_name, $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
	<div style='float:right'>
		<div class='desctext'>({$data['human']}k) &nbsp; {$data['mtime']}</div>
	</div>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/bad_file.png' border='0' alt='-' class='ipd' />
	<span style='border:1px solid #555;background-color:#FFFFFF'>
		<span style='width:{$data['left_width']}px;background-color:{$data['color']}'>
			<img src='{$this->ipsclass->skin_acp_url}/images/blank.gif' height='20' width='{$data['left_width']}' alt='' />
		</span>
		<img src='{$this->ipsclass->skin_acp_url}/images/blank.gif' height='20' width='{$data['right_width']}' alt='' />
	</span>
	&nbsp; <span class='desctext'>[ {$data['score']} ]</span> <a target='_blank' href='{$this->ipsclass->vars['board_url']}/{$file_path}'>$file_name</a>
 </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// DIAGNOSTICS
//===========================================================================
function anti_virus_checked_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<br />
<div class='tableborder'>
 <div class='tableheaderalt'>已进行非法文件检查的文件夹</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
	$content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// DIAGNOSTICS
//===========================================================================
function anti_virus_checked_row( $file_path ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/checked_folder.png' border='0' alt='-' class='ipd' />
	$file_path
 </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}



//===========================================================================
// DIAGNOSTICS
//===========================================================================
function anti_virus_bad_files_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>可疑文件</div>
 <div class='tablesubheader' style='padding-right:0px'>
  <div align='right' style='padding-right:10px'>
     (文件大小) &nbsp; 最后更改
    </div>
  </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
	$content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// DIAGNOSTICS
//===========================================================================
function anti_virus_bad_files_row( $file_path, $file_name, $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
	<div style='float:right'>
		<div class='desctext'>({$data['human']}k) &nbsp; {$data['mtime']}</div>
	</div>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/bad_file.png' border='0' alt='-' class='ipd' />
	<span style='border:1px solid #555;background-color:#FFFFFF'>
		<span style='width:{$data['left_width']}px;background-color:{$data['color']}'>
			<img src='{$this->ipsclass->skin_acp_url}/images/blank.gif' height='20' width='{$data['left_width']}' alt='' />
		</span>
		<img src='{$this->ipsclass->skin_acp_url}/images/blank.gif' height='20' width='{$data['right_width']}' alt='' />
	</span>
	&nbsp; <span class='desctext'>[ {$data['score']} ]</span> <a target='_blank' href='{$this->ipsclass->vars['board_url']}/{$file_path}'>$file_name</a>
 </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// OVERVIEW
//===========================================================================
function security_overview( $content ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/skin_acp/clientscripts/ipd_form_functions.js'></script>
<script type="text/javascript">
 //<![CDATA[
 // INIT FORM FUNCTIONS stuff
 var formfunctions = new form_functions();
 //]]>
</script>
<form id='mainform'>
<div class='information-box'>
	<table cellpadding='0' cellspacing='0'>
	<tr>
		<td width='1%' valign='top'>
 			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/id_card_ok.png' alt='information' />
		</td>
		<td width='100%' valig='top' style='padding-left:10px'>
 			<h2 style='margin:0px'>IPBChina 论坛安全中心</h2>
			 <p style='margin:0px'>
			 	<br />
			 	论坛安全中心是一个安全检查和设置工具的集中地.
				<br />
				系统检查了您的 IPB 安装文件和设置, 以下是给您提供的进一步提升安全性的建议.
				<br />
				<br />
				{$content['bad']}
				{$content['ok']}
				{$content['good']}
			 </p>
		</td>
	</tr>
	</table>
</div>
</form>
<script type="text/javascript">
//<![CDATA[
// Init form functions, grab stuff
formfunctions.init();
//]]>
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// BAD
//===========================================================================
function security_item_bad( $title, $desc, $button, $url, $key ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='bad-box' style='margin-bottom:10px'>
<table cellpadding='0' cellspacing='0'>
<tr>
	<td width='1%' valign='middle'>
			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/lock_error.png' alt='information' />
	</td>
	<td width='71%' valig='top' style='margin-left:10px'>
		 <div style='font-size:14px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:5px;margin-bottom:5px;margin-right:5px'>{$title}</div>
		 <div>$desc</div>
	</td>
	<td width='18%' valign='middle'>
		<div class='formbutton-wrap'>
		 	<div id='button-link-{$key}'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/run_tool.png' border='0' alt='执行'  title='执行' class='ipd' /> $button</div>
		 </div>
	</td>
</tr>
</table>
</div>
<script type="text/javascript">
//<![CDATA[
formfunctions.add_link_event( 'button-link-{$key}', '{$this->ipsclass->base_url}&$url' );
//]]>
</script>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// GOOD
//===========================================================================
function security_item_good( $title, $desc, $button, $url, $key ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='good-box' style='margin-bottom:10px'>
<table cellpadding='0' cellspacing='0'>
<tr>
	<td width='1%' valign='middle'>
			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/lock_ok.png' alt='information' />
	</td>
	<td width='71%' valig='top' style='margin-left:10px'>
		 <div style='font-size:14px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:5px;margin-bottom:5px;margin-right:5px'>{$title}</div>
		 <div>$desc</div>
	</td>
	<td width='18%' valign='middle'>
		<div class='formbutton-wrap'>
		 	<div id='button-link-{$key}'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/run_tool.png' border='0' alt='执行'  title='执行' class='ipd' /> $button</div>
		 </div>
	</td>
</tr>
</table>
</div>
<script type="text/javascript">
//<![CDATA[
formfunctions.add_link_event( 'button-link-{$key}', '{$this->ipsclass->base_url}&$url' );
//]]>
</script>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// OK
//===========================================================================
function security_item_ok( $title, $desc, $button, $url, $key ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='ok-box' style='margin-bottom:10px'>
<table cellpadding='0' cellspacing='0'>
<tr>
	<td width='1%' valign='middle'>
			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/lock_information.png' alt='information' />
	</td>
	<td width='71%' valig='top' style='margin-left:10px'>
		 <div style='font-size:14px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:5px;margin-bottom:5px;margin-right:5px'>{$title}</div>
		 <div>$desc</div>
	</td>
	<td width='18%' valign='middle'>
		<div class='formbutton-wrap'>
		 	<div id='button-link-{$key}'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/run_tool.png' border='0' alt='执行'  title='执行' class='ipd' /> $button</div>
		 </div>
	</td>
</tr>
</table>
</div>
<script type="text/javascript">
//<![CDATA[
formfunctions.add_link_event( 'button-link-{$key}', '{$this->ipsclass->base_url}&$url' );
//]]>
</script>

EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// HTaccess form
//===========================================================================
function htaccess_form() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form id='mainform' method='post' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=acphtaccess_do'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='information-box'>
	<table cellpadding='0' cellspacing='0'>
	<tr>
		<td width='1%' valign='top'>
 			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/id_card_ok.png' alt='information' />
		</td>
		<td width='100%' valig='top' style='padding-left:10px'>
 			<h2 style='margin:0px'>后台 .htaccess 权限保护</h2>
			 <p style='margin:0px'>
			 	<br />
			 	论坛可以生成 .htaccess 保护文件到您的后台文件夹.
				<br />
				<br />
				<strong>请注意</strong>
				<br />
				使用这一工具将覆盖后台当前的保护文件. 在您保存设置后您将被立即要求输入用户名称和用户密码. 额外的, 如果您选择更改后台文件夹的名称, 请记得通过 FTP 删除这个文件否则您将无法登入后台.
				<br />
				<br />
				<fieldset>
					<legend><strong>用户名称</strong></legend>
					<input type='text' name='name' size='40' value='{$_POST['name']}' />
				</fieldset>
				<br />
				<fieldset>
					<legend><strong>用户密码</strong></legend>
					<strong>请注意您的用户密码将被截取为前 8 个字符</strong><br />
					<input type='password' name='pass' size='40' value='{$_POST['pass']}' />
				</fieldset>
				<br />
				<input type='submit' value=' 执行 ' />
			 </p>
		</td>
	</tr>
	</table>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// HTaccess data
//===========================================================================
function htaccess_data( $htaccess_pw, $htaccess_auth ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form id='mainform'>
<div class='information-box'>
	<table cellpadding='0' cellspacing='0'>
	<tr>
		<td width='1%' valign='top'>
 			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/id_card_ok.png' alt='information' />
		</td>
		<td width='100%' valig='top' style='padding-left:10px'>
 			<h2 style='margin:0px'>后台 .htaccess 保护</h2>
			 <p style='margin:0px'>
			 	<br />
			 	<strong>易维论坛无法写入文件到后台文件夹</strong>
				<br />
				<br />
				请手工创建名为 ".htpasswd" 的文件并且将下面的内容复制到文件中. 保存后, 上传这一文件到您的 'admin' 文件夹.
				<br />
				<textarea rows='5' cols='70' style='width:98%;height:100px'>$htaccess_pw</textarea>
				<br />
				<br />
				请手工创建名为 ".htpasswd" 的文件并且将下面的内容复制到文件中. 保存后, 上传这一文件到您的 'admin' 文件夹.
				<br />
				<textarea rows='5' cols='70' style='width:98%;height:100px'>$htaccess_auth</textarea>
			 </p>
		</td>
	</tr>
	</table>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Rename the admin directory
//===========================================================================
function rename_admin_dir() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form id='mainform'>
<div class='information-box'>
	<table cellpadding='0' cellspacing='0'>
	<tr>
		<td width='1%' valign='top'>
 			<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/security/id_card_ok.png' alt='information' />
		</td>
		<td width='100%' valig='top' style='padding-left:10px'>
 			<h2 style='margin:0px'>重命名后台文件夹</h2>
			 <p style='margin:0px'>
			 	<br />
			 	易维论坛有专用的后台管理中心文件夹. 为了更进一步地提高论坛安全性, 您可以重命名后台文件夹.
				<br />
				<br />
				<strong>步骤 1:</strong>
				<br />
				首先, 您必须手工重命名后台文件夹. 用 FTP 软件登录您的论坛服务器浏览您的论坛文件夹 (该文件夹有您的论坛
				index.php 文件).
				<br/ >
				定位到 'admin' 文件夹. 它有一个名为 'index.php' 的文件. 从 FTP 软件中选择重命名菜单进行重命名. 比如我们将其重命名为 'admin_secret'.  <b>如果您已经创建了 .htaccess 保护文件在这个文件夹中, 您必须删除该文件否则您无法登录后台管理中心. 您可以在完成下一步后再重新创建 .htaccess 文件保护.</b>
				<br />
				<br />
				<strong>步骤 2:</strong>
				打开 'init.php' 文件. 该文件位于您的论坛根目录下. 下载后在文本编辑器中打开.
				<br />在文件的开头, 您将会看到下面的代码:
				<br />
				<pre>//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------
/**
* ROOT PATH
*
* If __FILE__ is not supported, try ./ and
* turn off "USE_SHUTDOWN" or manually add in
* the full path
* @since 2.0.0.2005-01-01
*/
define( 'ROOT_PATH', dirname( __FILE__ ) ."/" );

<strong>/**
* Directory name for the admin folder
* @since 2.2.0.2006-06-30
*/
define( 'IPB_ACP_DIRECTORY', 'admin' );</strong></pre>
				
				<br />
				改变代码行: <pre>define( 'IPB_ACP_DIRECTORY', '<strong>admin</strong>' );</pre> 为 <pre>define( 'IPB_ACP_DIRECTORY', '<strong>admin_secret</strong>' );</pre> 即可
				<br />
				<br />
				<strong>您的后台文件夹现在已经成功更改了.</strong>
				<br />别忘了在后台安全中心删除论坛中的后台链接菜单.
			 </p>
		</td>
	</tr>
	</table>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

}


?>