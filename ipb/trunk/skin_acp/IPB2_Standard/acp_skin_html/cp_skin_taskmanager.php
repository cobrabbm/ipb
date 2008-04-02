<?php

class cp_skin_taskmanager {

var $ipsclass;


//===========================================================================
// TASK MANAGER: Overview
//===========================================================================
function task_manager_logsshow_wrapper( $last5 ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>任务管理日志</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader'>任务执行</td>
  <td class='tablesubheader'>执行日期</td>
  <td class='tablesubheader'>日志信息</td>
 </tr>
 $last5
 </table>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// TASK MANAGER: Overview
//===========================================================================
function task_manager_logs_wrapper( $last5, $form ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>最后 5 次执行任务</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader'>任务执行</td>
  <td class='tablesubheader'>执行日期</td>
  <td class='tablesubheader'>日志信息</td>
 </tr>
 $last5
 </table>
</div>

<br />

<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_log_show' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>查看任务管理日志</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablerow1'><strong>查看以下任务日志</strong></td>
  <td class='tablerow2'>{$form['task_title']}</td>
 </tr>
 <tr>
  <td class='tablerow1'><strong>显示 <em>n</em> 个日志条目</strong></td>
  <td class='tablerow2'>{$form['task_count']}</td>
 </tr>
 <tr>
  <td colspan='2' class='tablefooter' align='center'><input class='realbutton' type='submit' value='查看日志' /></td>
 </tr>
 </table>
</div>
</form>

<br />

<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_log_delete' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>删除任务管理日志</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablerow1'><strong>删除以下任务日志</strong></td>
  <td class='tablerow2'>{$form['task_title_delete']}</td>
 </tr>
 <tr>
  <td class='tablerow1'><strong>删除早于 <em>n</em> 天的任务日志</strong></td>
  <td class='tablerow2'>{$form['task_prune']}</td>
 </tr>
 <tr>
  <td colspan='2' class='tablefooter' align='center'><input class='realbutton' type='submit' value='删除日志' /></td>
 </tr>
 </table>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// TRAFFIC: POPULAR row
//===========================================================================
function task_manager_last5_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td width='25%' class='tablerow1'><strong>{$data['log_title']}</strong></td>
 <td width='15%' class='tablerow2'>{$data['log_date']}</td>
 <td width='45%' class='tablerow2'>{$data['log_desc']}</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// Task manager form
//===========================================================================
function task_manager_form( $form, $button, $formbit, $type, $title, $task ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type='text/javascript' language='javascript'>
function updatepreview()
{
	var formobj  = document.adminform;
	var dd_wday  = new Array();
	
	dd_wday[0]   = '周日';
	dd_wday[1]   = '周一';
	dd_wday[2]   = '周二';
	dd_wday[3]   = '周三';
	dd_wday[4]   = '周四';
	dd_wday[5]   = '周五';
	dd_wday[6]   = '周六';
	
	var output       = '';
	
	chosen_min   = formobj.task_minute.options[formobj.task_minute.selectedIndex].value;
	chosen_hour  = formobj.task_hour.options[formobj.task_hour.selectedIndex].value;
	chosen_wday  = formobj.task_week_day.options[formobj.task_week_day.selectedIndex].value;
	chosen_mday  = formobj.task_month_day.options[formobj.task_month_day.selectedIndex].value;
	
	var output_min   = '';
	var output_hour  = '';
	var output_day   = '';
	var timeset      = 0;
	
	if ( chosen_mday == -1 && chosen_wday == -1 )
	{
		output_day = '';
	}
	
	if ( chosen_mday != -1 )
	{
		output_day = '在 '+chosen_mday+'.';
	}
	
	if ( chosen_mday == -1 && chosen_wday != -1 )
	{
		output_day = '在 ' + dd_wday[ chosen_wday ]+'.';
	}
	
	if ( chosen_hour != -1 && chosen_min != -1 )
	{
		output_hour = '在 '+chosen_hour+':'+formatnumber(chosen_min)+'.';
	}
	else
	{
		if ( chosen_hour == -1 )
		{
			if ( chosen_min == 0 )
			{
				output_hour = 'On every hour';
			}
			else
			{
				if ( output_day == '' )
				{
					if ( chosen_min == -1 )
					{
						output_min = '每分钟';
					}
					else
					{
						output_min = '每隔 '+chosen_min+' 分钟.';
					}
				}
				else
				{
					output_min = '在首次执行后 '+formatnumber(chosen_min)+' 分钟';
				}
			}
		}
		else
		{
			if ( output_day != '' )
			{
				output_hour = '在 ' + chosen_hour + ':00';
			}
			else
			{
				output_hour = '每隔 ' + chosen_hour + ' 小时';
			}
		}
	}
	
	output = output_day + ' ' + output_hour + ' ' + output_min;
	
	formobj.showtask.value = output;
}
							
function formatnumber(num)
{
	if ( num == -1 )
	{
		return '00';
	}
	if ( num < 10 )
	{
		return '0'+num;
	}
	else
	{
		return num;
	}
}
</script>
<form name='adminform' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=$formbit&amp;task_id={$task['task_id']}&amp;type=$type' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<input type='hidden' name='task_cronkey' value='{$task['task_cronkey']}' />
<div class='tableborder'>
 <div class='tableheaderalt'>
  <div style='float:left'>$title</div>
  <div align='right' style='padding-right:5px'><input type='text' name='showtask' class='realbutton' size='50' style='font-size:10px;width:auto;font-weight:normal;'/></div>
 </div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td width='40%' class='tablerow1'><strong>任务名称</strong></td>
   <td width='60%' class='tablerow2'>{$form['task_title']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>任务简短描述</strong></td>
   <td width='60%' class='tablerow2'>{$form['task_description']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>任务执行 PHP 文件</strong><div class='desctext'>这是任务执行时需要的 PHP 文件s='tablerow2'>./sources/tasks/ {$form['task_file']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1' colspan='2'>
    <fieldset>
    <legend><strong>时间选项</strong></legend>
    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
    <tr>
   		<td width='40%' class='tablerow1'><strong>任务时间: 分钟</strong><div class='desctext'>选择 '每分钟' 来每隔一分钟执行一次任务或者选择一个特定的一小时内的执行时间</div></td>
   		<td width='60%' class='tablerow2'>{$form['task_minute']}</td>
 	</tr>
 	<tr>
   		<td width='40%' class='tablerow1'><strong>任务时间: 小时</strong><div class='desctext'>选择 '每小时' 来每隔一小时执行一次任务或者选择一个特定的一天内的执行时间</div></td>
   		<td width='60%' class='tablerow2'>{$form['task_hour']}</td>
 	</tr>
 	<tr>
   		<td width='40%' class='tablerow1'><strong>任务时间: 星期</strong><div class='desctext'>选择 '每天' 来每隔一天执行一次任务或者选择一个特定的一周内的执行时间</div></td>
   		<td width='60%' class='tablerow2'>{$form['task_week_day']}</td>
 	</tr>
 	<tr>
   		<td width='40%' class='tablerow1'><strong>任务时间: 日期</strong><div class='desctext'>选择 '每天' 来每隔一天执行一次任务或者选择一个特定的一月内的执行时间</div></td>
   		<td width='60%' class='tablerow2'>{$form['task_month_day']}</td>
 	</tr>
    </table>
   </fieldset>
  </td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>开启任务日志</strong><div class='desctext'>将会在每次任务执行时写入任务日志, 对于每隔几分钟就会执行一次的常规任务则不推荐开启.</div></td>
   <td width='60%' class='tablerow2'>{$form['task_log']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>任务开启?</strong><div class='desctext'>如果您正在使用定是任务模式, 您可能需要在内部管理器中关闭这一任务.</div></td>
   <td width='60%' class='tablerow2'>{$form['task_enabled']}</td>
 </tr>
EOF;
//startif
if ( $form['task_key'] != "" )
{		
$IPBHTML .= <<<EOF
 <tr>
   <td width='40%' class='tablerow1'><strong>任务键值</strong><div class='desctext'>在任务更换 ID 这个键值将会用来对任务进行标识</div></td>
   <td width='60%' class='tablerow2'>{$form['task_key']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>任务安全模式</strong><div class='desctext'>如果选择 '是', 这一任务将不能被管理员编辑</div></td>
   <td width='60%' class='tablerow2'>{$form['task_safemode']}</td>
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
// TASK MANAGER: Overview
//===========================================================================
function task_manager_wrapper($content, $date) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>预定任务</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='40%'>名称</td>
  <td class='tablesubheader' width='25%'>下次执行</td>
  <td class='tablesubheader' width='5%'>分</td>
  <td class='tablesubheader' width='5%'>时</td>
  <td class='tablesubheader' width='5%'>日期</td>
  <td class='tablesubheader' width='5%'>星期</td>
  <td class='tablesubheader' width='1%'>选项</td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'><div class='fauxbutton-wrapper'><span class='fauxbutton'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_add'>添加新任务</a></span></div></div>
</div>
<br />
<div align='center' class='desctext'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_rebuild_xml'>从 tasks.xml 文件重建任务选项</a></em></div>
<br />
<div align='center' class='desctext'><em>全部时间为格林尼治标准时间. 当前格林尼治标准时间为: $date</em></div>
EOF;
//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// TRAFFIC: POPULAR row
//===========================================================================
function task_manager_row( $row ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
  <table cellpadding='0' cellspacing='0' width='100%'>
  <tr>
   <td width='99%' style='font-size:10px'>
	 <strong{$row['_class']}>
EOF;
//startif
if ( $row['task_locked'] > 0 )
{		
$IPBHTML .= <<<EOF
 <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_unlock&amp;task_id={$row['task_id']}'><img src='{$this->ipsclass->skin_acp_url}/images/lock_close.gif' border='0' alt='解除锁定' class='ipd' /></a>
EOF;
}//endif
$IPBHTML .= <<<EOF
	 {$row['task_title']}{$row['_title']}</strong>
	 <div style='color:gray'><em>{$row['task_description']}</em></div>
	   <div align='center' style='position:absolute;width:auto;display:none;text-align:center;background:#EEE;border:2px outset #555;padding:4px' id='pop{$row['task_id']}'>
		curl -s -o /dev/null {$this->ipsclass->vars['board_url']}/index.{$this->ipsclass->vars['php_ext']}?{$this->ipsclass->form_code}&amp;ck={$row['task_cronkey']}
	   </div>
   </td>
   <td width='1%' nowrap='nowrap'>
	<a href='#' onclick="toggleview('pop{$row['task_id']}');return false;" title='显示定时任务地址'><img src='{$this->ipsclass->skin_acp_url}/images/task_cron.gif' border='0' alt='定时任务' /></a>
	<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_run_now&amp;task_id={$row['task_id']}' title='现在执行任务 (ID 号: {$row['task_id']})'><img src='{$this->ipsclass->skin_acp_url}/images/{$row['_image']}'  border='0' alt='执行' /></a>
   </td>
  </tr>
 </table>
 </td>
 <td class='tablerow2'>{$row['_next_run']}</td>
 <td class='tablerow2'>{$row['task_minute']}</td>
 <td class='tablerow2'>{$row['task_hour']}</td>
 <td class='tablerow2'>{$row['task_month_day']}</td>
 <td class='tablerow2'>{$row['task_week_day']}</td>
 <td class='tablerow1'><img id="menu{$row['task_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$row['task_id']}",
  new Array( img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_edit&amp;task_id={$row['task_id']}'>编辑任务...</a>",
  			 img_password   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_unlock&amp;task_id={$row['task_id']}'>解除任务锁定...</a>",
  			 img_delete   + " <a href='#' onclick='confirm_action(\"{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;do=task_delete&amp;task_id={$row['task_id']}\"); return false;'>删除任务...</a>"
		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}


}


?>