<?php

class cp_skin_management {

var $ipsclass;


//===========================================================================
// Menu manage:Blank Pos
//===========================================================================
function calendar_position_blank($cal_id) {

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
function calendar_position_up($cal_id) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_move&amp;move=up&amp;cal_id={$cal_id}' title='上移'><img src='{$this->ipsclass->skin_acp_url}/images/arrow_up.png' width='12' height='12' border='0' style='vertical-align:middle' /></a>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Menu manage:Blank Down
//===========================================================================
function calendar_position_down($cal_id) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_move&amp;move=down&amp;cal_id={$cal_id}' title='下移'><img src='{$this->ipsclass->skin_acp_url}/images/arrow_down.png' width='12' height='12' border='0' style='vertical-align:middle' /></a>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Component FORM
//===========================================================================
function calendar_form($form, $title, $formcode, $button, $calendar) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type='text/javascript'>
//<![CDATA[

function perm_check( obj )
{
	var real_obj      = document.getElementById( obj );
	var total_options = real_obj.options.length;
	var total_checked = 0;
	
	for( var i = 0 ; i < real_obj.options.length ; i++ )
	{
		if ( real_obj.options[i].selected )
		{
			total_checked++;
		}
	}
	
	if ( total_checked == total_options )
	{
		document.getElementById( obj+'_all' ).checked = true;
	}
	else
	{
		document.getElementById( obj+'_all' ).checked = false;
	}
}

function perm_check_all( obj )
{
	var real_obj   = document.getElementById( obj );
	var isselected = document.getElementById( obj+'_all').checked ? true : false;
	
	for( var i = 0 ; i < real_obj.options.length ; i++ )
	{
		real_obj.options[i].selected = isselected;
	}
	
	document.getElementById( obj+'_all').checked = isselected;
}


//]]>
</script>
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;cal_id={$calendar['cal_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>$title</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td width='40%' class='tablerow1'><strong>日历: 标题</strong></td>
   <td width='60%' class='tablerow2'>{$form['cal_title']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>日历: 开启认证管理</strong><div class='desctext'>如果开启这一选项, 所有不包含在 '规避认证管理列表' 的会员所发布的日历事件都必须经过审核.</div></td>
   <td width='60%' class='tablerow2'>{$form['cal_moderate']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>日历: 事件数目限制</strong><div class='desctext'>这一选项是输入在链接前所显示的每日事件数目</div></td>
   <td width='60%' class='tablerow2'>{$form['cal_event_limit']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>日历: 生日数目限制</strong><div class='desctext'>这一选项是输入在链接前所显示的每日生日数目<br />输入 0 来禁止显示生日</div></td>
   <td width='60%' class='tablerow2'>{$form['cal_bday_limit']}</td>
 </tr>
 <tr>
   <td colspan='2' class='tablerow1'>
    <fieldset>
     <legend><strong>RSS 选项</strong></legend>
     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td width='40%' class='tablerow1'><strong>RSS: 开启</strong><div class='desctext'>如果开启这一选项, 全部 <em>n</em> 个即将到来的而且游客可以访问的事件 (基于格林尼治标准时间) 都将输出</div></td>
	   <td width='60%' class='tablerow2'>{$form['cal_rss_export']}</td>
	 </tr>
	 <tr>
	   <td width='40%' class='tablerow1'><strong>RSS: 将来时段</strong></td>
	   <td width='60%' class='tablerow2'>{$form['cal_rss_export_days']}</td>
	 </tr>
	 <tr>
	   <td width='40%' class='tablerow1'><strong>RSS: 输出事件最大值</strong><div class='desctext'>这是输出事件数量的最大值</div></td>
	   <td width='60%' class='tablerow2'>{$form['cal_rss_export_max']}</td>
	 </tr>
   	 <tr>
	   <td width='40%' class='tablerow1'><strong>RSS: 更新频率</strong><div class='desctext'>RSS 缓存重建的时长 (按分钟)</div></td>
	   <td width='60%' class='tablerow2'>{$form['cal_rss_update']}</td>
	 </tr>
    </table>
   </fieldset>
   </td>
 </tr>
 <tr>
   <td colspan='2' class='tablerow1'>
    <fieldset>
     <legend><strong>日历权限</strong></legend>
     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td width='40%' class='tablerow1' valign='top'><strong>权限: 读取日历事件</strong><div class='desctext'>请选择允许读取论坛日历事件的用户组.<br />如果您希望在 RSS 中导出日历事件, 请不要忘记开启游客读取权限.</div></td>
	   <td width='60%' class='tablerow2'>
	   	<input type='checkbox' name='perm_read_all' id='perm_read_all' value='1' onclick='perm_check_all("perm_read")' {$form['perm_read_all']} /> 选择所有当前和将来的用户组
	   	<br /><select onchange='perm_check("perm_read")' id='perm_read' name='perm_read[]' size='6' multiple='true'>{$form['perm_read']}</select>
	   </td>
	 </tr>
	 <tr>
	   <td width='40%' class='tablerow1' valign='top'><strong>权限: 新建日历事件</strong><div class='desctext'>请选择允许新建论坛日历事件的用户组</div></td>
	   <td width='60%' class='tablerow2'>
	   	<input type='checkbox' name='perm_post_all' id='perm_post_all' value='1' onclick='perm_check_all("perm_post")' {$form['perm_post_all']} /> 选择所有当前和将来的用户组
	   	<br /><select onchange='perm_check("perm_post")' id='perm_post' name='perm_post[]' size='6' multiple='true'>{$form['perm_post']}</select>
	   </td>
	 </tr>
	 <tr>
	   <td width='40%' class='tablerow1' valign='top'><strong>权限: 规避审核列表</strong><div class='desctext'>请选择允许规避日历事件审核的用户组</div></td>
	   <td width='60%' class='tablerow2'>
	   <input type='checkbox' name='perm_nomod_all' id='perm_nomod_all' value='1' onclick='perm_check_all("perm_nomod")' {$form['perm_nomod_all']} /> 选择所有当前和将来的用户组
	   <br /><select onchange='perm_check("perm_nomod")' id='perm_nomod' name='perm_nomod[]' size='6' multiple='true'>{$form['perm_nomod']}</select>
	   </td>
	 </tr>
    </table>
   </fieldset>
   </td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='$button' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// COMPONENTS: Overview
//===========================================================================
function calendar_overview( $content ) {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>日历</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='90%'>名称</td>
  <td class='tablesubheader' width='5%' align='center'>位置</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_add   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_add'>新建日历...</a>",
  			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_rebuildcache'>重建日历事件缓存...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// COMPONENTS: row
//===========================================================================
function calendar_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><strong>{$data['cal_title']}</strong></td>
 <td class='tablerow2' align='center' nowrap='nowrap'>{$data['_pos_up']} &nbsp; {$data['_pos_down']}</td>
 <td class='tablerow1'><img id="menu{$data['cal_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['cal_id']}",
  new Array(
           img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_edit&amp;cal_id={$data['cal_id']}'>编辑日历...</a>",
           img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_rss_cache&amp;cal_id={$data['cal_id']}'>重建日历 RSS 输出...</a>",
           img_delete   + " <a href='#' onclick='confirm_action(\"{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=calendar_delete&amp;cal_id={$data['cal_id']}\"); return false;'>删除日历...</a>"
  		) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// calendar_rss_recurring
//===========================================================================
function calendar_rss_recurring( $event ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<p>{$event['event_content']}</p>
<br />
<p>循环事件
<br />从: {$event['_from_month']}/{$event['_from_day']}/{$event['_from_year']}
<br />到: {$event['_to_month']}/{$event['_to_day']}/{$event['_to_year']}</p>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// calendar_rss_recurring
//===========================================================================
function calendar_rss_range( $event ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<p>{$event['event_content']}</p>
<br />
<p>多日事件
<br />从: {$event['_from_month']}/{$event['_from_day']}/{$event['_from_year']}
<br />到: {$event['_to_month']}/{$event['_to_day']}/{$event['_to_year']}</p>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// calendar_rss_recurring
//===========================================================================
function calendar_rss_single( $event ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<p>{$event['event_content']}</p>
<br />
<p>单日事件在: {$event['_from_month']}/{$event['_from_day']}/{$event['_from_year']}</p>
EOF;

//--endhtml--//
return $IPBHTML;
}




}

?>