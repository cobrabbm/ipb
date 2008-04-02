<?php

class cp_skin_settings {

var $ipsclass;



//===========================================================================
// Index
//===========================================================================
function acp_last_logins_row( $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1' width='1' valign='middle'>
	<img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/user.png' border='0' alt='-' class='ipd' />
 </td>
 <td class='tablerow1'> <strong>{$r['admin_username']}</strong></td>
 <td class='tablerow2'><div class='desctext'>IP 地址: {$r['admin_ip_address']}</div></td>
 <td class='tablerow2' align='center'>{$r['_admin_time']}</td>
 <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$r['_admin_img']}' border='0' alt='-' class='ipd' /></td>
 <td class='tablerow1' width='1' valign='middle'>
 	<a href='#' onclick="return ipsclass.pop_up_window('{$this->ipsclass->base_url}section=admin&amp;act=loginlog&amp;code=view_detail&amp;detail={$r['admin_id']}', 400, 400)" title='查看详细信息'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/index/view.png' border='0' alt='-' class='ipd' /></a>
 </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Index
//===========================================================================
function settings_check_differences( $original_missing, $original_new ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>设置 XML 差异</div>
 <table width='100%' cellpadding='0' cellspacing='0'>
 <tr>
	<td width='50%' valign='top' class='tablerow2'>
		<div class='tablesubheader'>不在数据库中的设置</div>
		<table width='100%' cellpadding='4' cellspacing='0'>
EOF;
if ( is_array( $original_missing ) )
{
	foreach( $original_missing as $key => $data )
	{
$IPBHTML .= <<<EOF
	<tr>
		<td class='tablerow1'>{$key}</td>
	</tr>
EOF;
	}
}

$IPBHTML .= <<<EOF
		</table>
	</td>
	<td width='50%' valign='top' class='tablerow2'>
	<div class='tablesubheader'>不在上传的 XML 文件中的设置</div>
	<table width='100%' cellpadding='4' cellspacing='0'>
EOF;
if ( is_array( $original_new ) )
{
	foreach( $original_new as $key => $data )
	{
$IPBHTML .= <<<EOF
<tr>
	<td class='tablerow1'>{$data['conf_key']}</td>
</tr>
EOF;
	}
}

$IPBHTML .= <<<EOF
		</table>
	</td>
 </table>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}


function popup_help( $title, $text )
{
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>{$title}</div>
 
 <div class='tablerow1'>{$text}</div>
 
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

}


?>