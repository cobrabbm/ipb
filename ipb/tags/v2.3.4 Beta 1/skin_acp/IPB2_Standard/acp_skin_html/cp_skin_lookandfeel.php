<?php

class cp_skin_lookandfeel {

var $ipsclass;


//===========================================================================
//  LOOK AND FEEL: SKIN DIFF
//===========================================================================
function skin_cache_settings( $form, $template ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form id='mainform' action='{$this->ipsclass->base_url}&amp;act=rtempl&amp;code=cache_settings_save&amp;suid={$template['suid']}' method='POST'>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>缓存设置: {$template['func_name']}</td>
  <td align='right' width='5%' nowrap='nowrap'>
  &nbsp;
 </td>
 </tr>
</table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablerow1' width='60%' valign='top'><strong>原始缓存文件</strong><div class='desctext'>该模板项的原始缓存文件</div></td>
  <td class='tablerow1' width='40%'><strong>{$form['_title']}</strong></td>
 </tr>
 <tr>
  <td class='tablerow1' width='60%' valign='top'><strong>二级缓存文件</strong><div class='desctext'>选择一个或更多该模板项的二级缓存文件</div></td>
  <td class='tablerow1' width='40%'>{$form['group_names_secondary']}</td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' value=' 保存 ' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function template_bits_bit_row_image( $id, $image ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<img id='img-item-{$id}' src='{$this->ipsclass->skin_acp_url}/images/{$image}' border='0' alt='' />
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function template_bits_overview_row_normal( $group, $folder_blob, $count_string ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tablerow1' id='dv-{$group['group_name']}'>
 <div style='float:right'>
  ($count_string)&nbsp;{$group['easy_preview']}
 </div>
 <div align='left'>
   <img src='{$this->ipsclass->skin_acp_url}/images/folder.gif' alt='模板用户组' style='vertical-align:middle' />
   {$folder_blob}&nbsp;<a style='font-size:11px' id='gn-{$group['group_name']}' onclick='template_load_bits("{$group['group_name']}", event)' title='{$group['easy_desc']}' href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=template-bits-list&id={$group['_id']}&p={$group['_p']}&group_name={$group['group_name']}&'>{$group['easy_name']}</a>
   <span id='match-{$group['group_name']}'></span>
 </div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function template_bits_bit_row( $sec, $custom_bit, $remove_button, $altered_image ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tablerow1' id='dvb-{$sec['func_name']}' title='点击这一排和其他的排来同时编辑多个模板' onclick='parent.template_toggle_bit_row("{$sec['func_name']}")' >
 <div style='float:right;width:auto;'>
  $remove_button
  <a style='text-decoration:none' title='文本模式预览模板项' href='#' onclick='pop_win("act=rtempl&code=preview&suid={$sec['suid']}&type=text"); parent.template_cancel_bubble( event, true );'><img src='{$this->ipsclass->skin_acp_url}/images/te_text.gif' border='0' alt='文本模式预览'></a>
  <a style='text-decoration:none' title='HTML 模式预览模板项' href='#' onclick='pop_win("act=rtempl&code=preview&suid={$sec['suid']}&type=css");  parent.template_cancel_bubble( event, true );'><img src='{$this->ipsclass->skin_acp_url}/images/te_html.gif' border='0' alt='HTML 模式预览'>&nbsp;</a>
 </div>
 <div align='left'>
   <img src='{$this->ipsclass->skin_acp_url}/images/file.gif' title='Template Set:{$sec['set_id']}' alt='模板' style='vertical-align:middle' />
   {$altered_image}
   <a id='bn-{$sec['func_name']}' onclick='parent.template_load_editor("{$sec['func_name']}", event)' href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=template-edit-bit&bitname={$sec['func_name']}&p={$sec['_p']}&id={$sec['_id']}&group_name={$sec['group_name']}&type=single' title='模板项名称: {$sec['func_name']}'>{$sec['easy_name']}</a>
   {$custom_bit}
 </div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function template_bits_bit_overview( $group, $content, $add_button ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tablerow3'>
 <div style='float:right;padding-top:3px'><strong>{$group['easy_name']}</strong></div>
 <div>
  <a href='#' onclick="parent.template_close_bits(); return false;" title='关闭窗口'><img src='{$this->ipsclass->skin_acp_url}/images/skineditor_close.gif' border='0' alt='关闭' /></a>&nbsp;
  <!--<a href='#' onclick="toggleselectall(); return false;" title='Check/Uncheck all'><img src='{$this->ipsclass->skin_acp_url}/images/skineditor_tick.gif' border='0' alt='Check/Uncheck all' /></a>-->
 </div>
</div>
<div id='template-bits-container'>
{$content}
</div>
 <div style='background:#CCC'>
   <div align='left' style='padding:5px;margin-left:25px'>
   <div style='float:right'>$add_button</div>
   <div><input type='button' onclick='parent.template_load_bits_to_edit("{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=template-edit-bit&id={$this->ipsclass->input['id']}&p={$this->ipsclass->input['p']}&group_name={$group['group_name']}&type=multiple")' class='realbutton' value='编辑所选' /></div>
 </div>
</div>
<script type="text/javascript">
//<![CDATA[
parent.template_bits_onload();
//]]>
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
//  LOOK AND FEEL: TEMPLATES
//===========================================================================
function template_bits_overview($content, $javascript) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript">
//<![CDATA[
var lang_matches = "matches";
$javascript
//]]>
</script>
<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
<script type="text/javascript" src='{$this->ipsclass->skin_acp_url}/acp_template.js'></script>
<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:210px;display:none;z-index:10'></div>
<div class='tableborder'>
 <div class='tableheaderalt'>
  <table cellpadding='0' cellspacing='0' border='0' width='100%'>
  <tr>
  <td align='left' width='100%'>
   <div id='quick-search-box'>
    <form id='quick-search-form'>
     <input type='text' size='20' class='realwhitebutton' style='width:210px' name='searchkeywords' id='entered_template' autocomplete="off" value='' />&nbsp;<input type='button' onclick='template_find_bits(event)' class='realbutton' value='查找模板项' />
    </form>
   </div>
  </td>
  </tr>
  </table>
</div>
<div id='template-edit' style='height:0px;width:100%;display:none;z-index:1'><iframe id='te-iframe' name='te-iframe' onload='template_iframe_loaded( "te" )' style='width:0;height:0px;display:none' src='javascript:;'></iframe></div>
<table id='template-main-wrap' width='100%'>
	<tr>
   		<td valign='top' id='template-sections' style='width:100%;height:476px;max-height:476px;z-index:3;'>
    		<div style='margin:0px;padding:0px;width:100%;overflow:auto;height:476px;max-height:476px;'>
	 			$content
			</div>
   		</td>
   		
   		<td valign='top' id='template-bits' style='width:0%;display:none;height:476px;max-height:476px;'>
   			<iframe id='tb-iframe' name='tb-iframe' onload='template_iframe_loaded( "tb" )' style='width:0%;display:none;height:476px;max-height:476px;' src='javascript:;'></iframe>
  		</td>
 	</tr>
 	<tr>
 		<td colspan='2' align='center' class='tablefooter'>&nbsp;</td>
 	</tr>
</table>
<br clear='all' />
<br />
<div style='padding:4px;'><strong>子模板设置菜单说明:</strong><br />
<img id='img-altered' src='{$this->ipsclass->skin_acp_url}/images/skin_item_altered.gif' border='0' alt='+' title='已修改' /> 这一模板条目已经修改.
<br /><img id='img-unaltered' src='{$this->ipsclass->skin_acp_url}/images/skin_item_unaltered.gif' border='0' alt='-' title='未修改' /> 这一模板条目尚未修改.
<br /><img id='img-inherited' src='{$this->ipsclass->skin_acp_url}/images/skin_item_inherited.gif' border='0' alt='|' title='继承父模板设置' /> 这一模板条目继承父模板设置.
</div>
<script type='text/javascript'>
//<![CDATA[
// INIT images
img_revert_blank = '{$this->ipsclass->skin_acp_url}/images/blank.gif';
img_revert_real  = '{$this->ipsclass->skin_acp_url}/images/te_revert.gif';
img_revert_width  = 44;
img_revert_height = 16;

template_init();
// INIT find names
init_js( 'quick-search-form', 'entered_template', 'get-template-names&id={$this->ipsclass->input['id']}' );
// Run main loop
setTimeout( 'main_loop()', 10 );
//]]>
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
//  LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_sets_overview($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>皮肤设置</td>
  <td align='right' width='5%' nowrap='nowrap'>
   <img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /> &nbsp;
 </td>
 </tr>
</table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 $content
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
<br />
<div><strong>子模板设置菜单说明:</strong><br />
<img src='{$this->ipsclass->skin_acp_url}/images/skin_item_altered.gif' border='0' alt='+' title='已修改' /> 这一模板条目已经修改.
<br /><img src='{$this->ipsclass->skin_acp_url}/images/skin_item_unaltered.gif' border='0' alt='-' title='未修改' /> 这一模板条目尚未修改.
<br /><img src='{$this->ipsclass->skin_acp_url}/images/skin_item_inherited.gif' border='0' alt='|' title='继承父模板设置' /> 这一模板条目继承父模板设置.
</div>
<div id='menumainone_menu' style='display:none' class='popupmenu'>
	<form action='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=addset&id=-1' method='POST'>
	<div align='center'><strong>创建新的模板设置</strong></div>
	<div align='center'><input type='text' name='set_name' size='20' value='输入模板设置名称' onfocus='this.value=""'></center></div>
	<div align='center'><input type='submit' value='Go' class='realdarkbutton' /></div>
	</form>
</div>
<script type="text/javascript">
	ipsmenu.register( "menumainone" );
</script>
EOF;
if ( IN_DEV == 1 )
{
$IPBHTML .= <<<EOF
<br />
<div align='center'>
  DEV: <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=exportmaster'>导出主体 HTML</a>
  &middot; <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=exportbitschoose'>导出模板项</a>
  &middot; <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=exportmacro'>导出主体标签</a>
</div>
EOF;
}

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_sets_overview_row( $r, $forums, $hidden, $default, $menulist, $i_sets, $no_sets, $folder_icon, $line_image, $css_extra ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
   <!--$i_sets,$no_sets-->{$line_image}<!--ID:{$r['set_skin_set_id']}--><img src='{$this->ipsclass->skin_acp_url}/images/{$folder_icon}' border='0' alt='皮肤' style='vertical-align:middle' />
   <strong style='{$css_extra}'>{$r['set_name']}</strong>
 </td>
 <td class='tablerow1' width='5%' nowrap='nowrap' align='center'>{$forums} {$hidden} {$default}</td>
 <td class='tablerow1' width='5%'><img id="menu{$r['set_skin_set_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$r['set_skin_set_id']}",
  new Array(
			$menulist
  		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_sets_overview_row_menulist( $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
img_edit   + " <!--ALTERED.wrappper--><a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=wrap&code=edit&id={$r['set_skin_set_id']}&p={$r['set_skin_set_parent']}'>编辑论坛页眉 & 页脚</a>",
img_edit   + " <!--ALTERED.templates--><a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=templ&code=template-sections-list&id={$r['set_skin_set_id']}&p={$r['set_skin_set_parent']}'>编辑模板 HTML 项</a>",
img_edit   + " <!--ALTERED.css--><a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=style&code=edit&id={$r['set_skin_set_id']}&p={$r['set_skin_set_parent']}'>编辑模板风格 (CSS 高级模式)</a>",
img_edit   + " <!--ALTERED.css--><a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=style&code=colouredit&id={$r['set_skin_set_id']}&p={$r['set_skin_set_parent']}'>编辑模板颜色 (CSS 简易模式)</a>",
img_edit   + " <!--ALTERED.macro--><a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=image&code=edit&id={$r['set_skin_set_id']}&p={$r['set_skin_set_parent']}'>编辑主体标签替换</a>",
img_edit   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&id={$r['set_skin_set_id']}'>编辑模板设置...</a>",
EOF;
if ( $r['set_skin_set_id'] != 1 )
{
$IPBHTML .= <<<EOF

img_item   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=revertallform&id={$r['set_skin_set_id']}'>还原所有自定义内容...</a>",
img_export   + " <a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=import&code=showexportpage&id={$r['set_skin_set_id']}'>导出模板设置...</a>",
img_view   + " <a href='{$this->ipsclass->base_url}&section={$this->ipsclass->section_code}&act=skindiff&code=skin_diff_from_skin&skin_id={$r['set_skin_set_id']}'>生成 HTML 差异报告...</a>",

img_delete   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=remove&id={$r['set_skin_set_id']}'>删除模板设置...</a>",
EOF;
}
if ( $r['set_skin_set_id'] != 1 AND ! $r['set_skin_set_parent'] )
{
$IPBHTML .= <<<EOF

img_add   + " <a  href='#' onclick=\"addnewpop('{$r['set_skin_set_id']}','menu_{$r['set_skin_set_id']}')\">添加新子模板设置...</a>",
EOF;
}

/* 
This line will give you a link to run CSS diff reports

img_view   + " <a href='#' onclick='ipsclass.pop_up_window(\"{$this->ipsclass->base_url}&act=rtempl&code=css_diff&id={$r['set_skin_set_id']}\", 800, 600 )'>Generate CSS Differences Report</a>",

However this is a very resource intensive operation, and should only be done on a development server
*/

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// SKIN REMAP FORM
//===========================================================================
function skin_remap_form( $form, $title, $formcode, $button, $remap ) {

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
//]]>
</script>
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;map_id={$remap['map_id']}' id='mainform' method='POST'>
<div class='tabwrap'>
	<div id='tabtab-1' class='taboff'>通用设置</div>
</div>
<div class='tabclear'>$title</div>
<div class='tableborder'>
<div id='tabpane-1' class='formmain-background'>
	<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	 <tr>
	   <td>
		<fieldset class='formmain-fieldset'>
		    <legend><strong>通用设置</strong></legend>
		     <table cellpadding='0' cellspacing='0' border='0' width='100%'>
			  <tr>
			   <td width='40%' class='tablerow1'><strong>标题</strong><div class='desctext'>这个标题仅仅用来给您的模板项进行区别.</div></td>
			   <td width='60%' class='tablerow2'>{$form['map_title']}</td>
			  </tr>
			  <tr>
			   <td width='40%' class='tablerow1'><strong>类型</strong><div class='desctext'>"包含" 将把任何位置出现该字符串作为匹配. "精确" 将仅仅匹配字符串的完整出现.</div></td>
			   <td width='60%' class='tablerow2'>{$form['map_match_type']}</td>
			  </tr>
			  <tr>
			   <td width='40%' class='tablerow1'><strong>URL 地址</strong><div class='desctext'>输入字符串来进行查询.</div></td>
			   <td width='60%' class='tablerow2'>{$form['map_url']}</td>
			  </tr>
			  <tr>
			   <td width='40%' class='tablerow1'><strong>模板设置</strong><div class='desctext'>皮肤设置映射到.</div></td>
			   <td width='60%' class='tablerow2'><select name='map_skin_set_id'>{$form['skin_list']}</select></td>
			  </tr>
			 </table>
		 </fieldset>
		</td>
	</tr>
	</table>
</div>
<div align='center' class='tablefooter'>
 	<div class='formbutton-wrap'>
 		<div id='button-save'><img src='{$this->ipsclass->skin_acp_url}/images/icons_form/save.gif' border='0' alt='Save'  title='Save' class='ipd-alt' /> $button</div>
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
//  LOOK AND FEEL: SKIN REMAP: MAIN
//===========================================================================
function skin_remap_overview($remaps=array()) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->skin_acp_url}/acp_template.js'></script>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>模板重测图</td>
 </tr>
</table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='40%'>标题</td>
  <td class='tablesubheader' width='20%'>模板设置</a>
  <td class='tablesubheader' width='30%'>增加项</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></a>
 </tr>
EOF;
if ( count( $remaps ) )
{
	foreach( $remaps as $data )
	{
$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><img src='{$this->ipsclass->skin_acp_url}/images/folder_components/skinremap/remap_row.png' border='0' class='ipd' /></td>
 <td class='tablerow1'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=remap_edit&amp;map_id={$data['map_id']}'><strong>{$data['map_title']}</strong></a></td>
 <td class='tablerow1'>{$data['_name']}</td>
 <td class='tablerow1' nowrap='nowrap'>{$data['_date']}</td>
 <td class='tablerow1' width='5%'><img id="menu{$data['map_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['map_id']}",
  new Array(
			img_view     + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=remap_edit&amp;map_id={$data['map_id']}'>编辑重测图...</a>",
			img_delete   + " <a href='#' onclick=\"checkdelete('{$this->ipsclass->form_code}&code=remap_remove&map_id={$data['map_id']}')\">删除重测图...</a>"
			  		    ) );
 </script>
EOF;
	}
}
else
{
$IPBHTML .= <<<EOF
	<tr>
	 <td class='tablerow1' colspan='5'><em>当前没有已经建立的 URL 重测图.</em></td>
	</tr>
EOF;
}
$IPBHTML .= <<<EOF
 </table>
 <div align='right' class='tablefooter'>&nbsp;</div>
</div>
<script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( 
  			 img_add + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=remap_add'>添加新的 URL 重测图...</a>"
           ) );
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
//  LOOK AND FEEL: SKIN DIFF: MAIN
//===========================================================================
function skin_diff_main_overview($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->skin_acp_url}/acp_template.js'></script>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>皮肤差异报告</td>
 </tr>
</table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='90%'><strong>差异标题</strong></td>
  <td class='tablesubheader' width='5%'>已创建</a>
  <td class='tablesubheader' width='5%'>&nbsp;</a>
 </tr>
 $content
 </table>
 <div align='right' class='tablefooter'>&nbsp;</div>
</div>
<br />
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=skin_diff' enctype='multipart/form-data' method='POST'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>创建新的皮肤差异报告</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td class='tablerow1'><strong>输入一个新的差异报告标题</strong><div class='desctext'>这一标题仅仅用来对您的差异报告进行区分</div></td>
  <td class='tablerow2'><input class='textinput' type='text' size='30' name='diff_session_title' /></td>
 </tr>
 <tr>
  <td class='tablerow1'><strong>跳过所有新建和已删除模板项?</strong><div class='desctext'>如果您正在从一个旧的 IPB 版本比较 ipb_templates.xml 文件, 则您可能需要禁用这一选项. 如果您正在从一个自定义的模板设置 XML 文件进行比较, 您可能需要启用这一选项.</div></td>
  <td class='tablerow2'><input class='textinput' type='checkbox' value='1' name='diff_session_ignore_missing' /></td>
 </tr>
 <tr>
  <td class='tablerow1'><strong>从您的电脑中选择一个有效的 XML 皮肤差异文件.</strong><div class='desctext'>这一文件将和您的主 HTML 模板进行比较 - 因此请在运行比较工具前确认它们是最新的版本</div></td>
  <td class='tablerow2'><input class='textinput' type='file' size='30' name='FILE_UPLOAD' /></td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='导入' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_diff_main_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'> <strong>{$data['diff_session_title']}</strong></td>
 <td class='tablerow1' width='5%' nowrap='nowrap' align='center'>{$data['_date']}</td>
 <td class='tablerow1' width='5%'><img id="menu{$data['diff_session_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['diff_session_id']}",
  new Array(
			img_view   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=skin_diff_view&amp;diff_session_id={$data['diff_session_id']}'>查看差异报告...</a>",
			img_delete   + " <a href='#' onclick=\"checkdelete('{$this->ipsclass->form_code}&code=skin_diff_remove&diff_session_id={$data['diff_session_id']}')\">删除差异报告...</a>",
			img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=skin_diff_export&amp;diff_session_id={$data['diff_session_id']}'>创建 HTML 导出报告...</a>"
  		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
//  LOOK AND FEEL: SKIN DIFF
//===========================================================================
function skin_diff_overview($content, $missing, $changed) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type="text/javascript" src='{$this->ipsclass->skin_acp_url}/acp_template.js'></script>
<div class='tableborder'>
 <div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>皮肤差异</td>
  <td align='right' width='5%' nowrap='nowrap'>
  &nbsp;
 </td>
 </tr>
</table>
 </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='90%'><strong>模板项名称</strong></td>
  <td class='tablesubheader' width='5%'>差异</a>
  <td class='tablesubheader' width='5%'>大小</a>
  <td class='tablesubheader' width='5%'>&nbsp;</a>
 </tr>
 $content
 </table>
 <div align='right' class='tablefooter'>$missing 个新模板项和 $changed 个更改模板项</div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES: NEW GROUP
//===========================================================================
function skin_diff_row_newgroup( $group_name ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td colspan='4' class='tablerow3'>
   <strong>{$group_name}</strong>
 </td>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_diff_row( $template_bit_name, $template_bit_size, $template_bit_id, $diff_is, $template_bit_id_safe ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
   <strong>{$template_bit_name}</strong>
 </td>
 <td class='tablerow1' width='5%' nowrap='nowrap' align='center'>{$diff_is}</td>
 <td class='tablerow1' width='5%' nowrap='nowrap' align='center'>{$template_bit_size}</td>
 <td class='tablerow1' width='5%'><img id="menu{$template_bit_id_safe}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$template_bit_id_safe}",
  new Array(
			img_view   + " <a href='#' onclick=\"return template_view_diff('$template_bit_id')\">查看差异...</a>"
  		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_css_view_bit( $diff ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
$diff
</div>
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
 <span class='diffred'>删除 HTML 语句</span> &middot; <span class='diffgreen'>添加 HTML 语句</span>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_diff_view_bit( $template_bit_name, $template_group, $content ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
<strong>$template_group &gt; $template_bit_name</strong>
</div>
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
$content
</div>
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
 <span class='diffred'>删除 HTML 语句</span> &middot; <span class='diffgreen'>添加 HTML 语句</span>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_diff_export_row( $func_name, $func_group, $content ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
<h2>$func_group <span style='color:green'>&gt;</span> $func_name</h2>
<hr>
$content
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// LOOK AND FEEL: TEMPLATES
//===========================================================================
function skin_diff_export_overview( $content, $missing, $changed, $title, $date ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<html>
 <head>
  <title>$title export</title>
  <style type="text/css">
   BODY
   {
   	font-family: verdana;
   	font-size:11px;
   	color: #000;
   	background-color: #CCC;
   }
   
   del,
   .diffred
   {
	   background-color: #D7BBC8;
	   text-decoration:none;
   }
   
   ins,
   .diffgreen
   {
	   background-color: #BBD0C8;
	   text-decoration:none;
   }
   
   h1
   {
   	font-size: 18px;
   }
   
   h2
   {
   	font-size: 18px;
   }
  </style>
 </head>
<body>
  <div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
  <h1>$title (Exported: $date)</h1>
  <strong>$missing 个新模板项和 $changed 个更改模板项</strong>
  </div>
  <br />
  $content
  <br />
  <div style='padding:4px;border:1px solid #000;background-color:#FFF;margin:4px;'>
   <span class='diffred'>删除 HTML 语句</span> &middot; <span class='diffgreen'>添加 HTML 语句</span>
  </div>
</body>
<html>
EOF;

//--endhtml--//
return $IPBHTML;
}


function emoticon_overview_wrapper_addform( )
{
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<script type='text/javascript'>
function addfolder()
{
	document.macroform.emoset.value       = '';
	document.macroform.code.value         = 'emo_setadd';
	document.macroform.submitbutton.value = '新建文件夹';
	scroll(0,0);
	togglediv( 'popbox', 1 );
	return false;
}

function editfolder(id)
{
	document.macroform.submitbutton.value = '编辑文件夹名称';
	document.macroform.id.value     = id;
	document.macroform.code.value   = 'emo_setedit';
	document.macroform.emoset.value = id;
	scroll(0,0);
	togglediv( 'popbox', 1 );
	return false;
}
</script>
<div align='center' style='position:absolute;display:none;text-align:center' id='popbox'>
 <form name='macroform' action='{$this->ipsclass->base_url}' method='post'>
 <input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
 <input type='hidden' name='act'     value='emoticons' />
 <input type='hidden' name='section' value='lookandfeel' />
 <input type='hidden' name='code'    value='emo_setadd' />
 <input type='hidden' name='id' value='' />

 <table cellspacing='0' width='500' align='center' cellpadding='6' style='background:#EEE;border:2px outset #555;'>
 <tr>
  <td width='1%' nowrap='nowrap' valign='top' align='center'>
   <b>文件夹名称 (只允许数字和字母)</b><br><input class='textinput' name='emoset' type='text' size='40' />
   <br /><br />
   <center><input type='submit' class='realbutton' value='新建文件夹' name='submitbutton' /> <input type='button' class='realdarkbutton' value='关闭' onclick="togglediv('popbox');" /></center>
  </td>
 </tr>

 </table>
 </form>
</div>

EOF;

return $IPBHTML;
}


function emoticon_overview_wrapper( $content )
{
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}' method='post' name='uploadform'  enctype='multipart/form-data' id='uploadform'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<input type='hidden' name='code' value='emo_upload'>
<input type='hidden' name='act' value='emoticons'>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000000'>
<input type='hidden' name='dir_default' value='1'>
<input type='hidden' name='section' value='lookandfeel'>
<div class='tableborder'>
<div class='tableheaderalt'>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td align='left' width='95%' style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>当前表情文件夹</td>
  <td align='right' width='5%' nowrap='nowrap'>
   <img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /> &nbsp;
 </td>
 </tr>
</table>
</div>

<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'><tr>
<td class='tablesubheader' width='50%' align='center'>表情文件夹</td>

<td class='tablesubheader' width='5%' align='center'>上传</td>
<td class='tablesubheader' width='20%' align='center'># 硬盘文件夹 </td>
<td class='tablesubheader' width='20%' align='center'># 表情组</td>
<td class='tablesubheader' width='5%' align='center'>选项</td>
</tr>

{$content}

</table>
</div>
<br />

EOF;

if( SAFE_MODE_ON )
{
$IPBHTML .= <<<EOF
	</form>
EOF;
}
else
{
$IPBHTML .= <<<EOF
<div class='tableborder'>
	 <div class='tableheaderalt'>上传表情图标</div>
	 <table width='100%' border='0' cellpadding='4' cellspacing='0'>
	 <tr>
	  <td width='50%' class='tablerow1' align='center'><input type='file' value='' class='realbutton' name='upload_1' size='30' /></td>

	  <td width='50%' class='tablerow2' align='center'><input type='file' class='realbutton' name='upload_2' size='30' /></td>
	 </tr>
	 <tr>
	  <td width='50%' class='tablerow1' align='center'><input type='file' class='realbutton' name='upload_3' size='30' /></td>
	  <td width='50%' class='tablerow2' align='center'><input type='file' class='realbutton' name='upload_4' size='30' /></td>
	 </tr>
	 </table>
	 <div class='tablesubheader' align='center'><input type='submit' value='上传表情图标到所选文件夹' class='realdarkbutton' /></form></div>
</div>
EOF;
}

$IPBHTML .= <<<EOF

<script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( 
  			 img_add + " <a href='#' onclick='addfolder(); return false;' style='color:#000;'><strong>新建文件夹</strong></a>"
           ) );
</script>
EOF;

return $IPBHTML;
}


function emoticon_overview_row( $data=array() )
{
	
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	<tr>
	 
	 <td class='tablerow2' valign='middle'>
	 	<div style='width:auto;float:right;'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['icon']}' title='{$data['title']}' alt='{$data['icon']}' /></div>
	 	{$data['line_image']}<img src='{$this->ipsclass->skin_acp_url}/images/emoticon_folder.gif' border='0'>&nbsp;<a href='{$this->ipsclass->base_url}&section=lookandfeel&amp;act=emoticons&code=emo_manage&id={$data['dir']}' title='编辑这个表情设置'><b>{$data['dir']}</b></a>
	 </td>

	 <td class='tablerow1' valign='middle'><center>{$data['checkbox']}</center></td>
	 <td class='tablerow2' valign='middle'><center>{$data['count']}</center></td>
	 <td class='tablerow1' valign='middle'><center>{$data['dir_count']}</center></td>
	 <td class='tablerow2' valign='middle'><center><img id="menu{$data['dir']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
	</tr>
	
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['dir']}",
  new Array(
			img_item   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=emo_manage&id={$data['dir']}'>{$data['link_text']}...</a>",
EOF;

if( $data['dir'] != 'default' OR IN_DEV == 1 )
{
$IPBHTML .= <<<EOF
  			img_edit   + " <a href='#' onclick=\"editfolder('{$data['dir']}')\">编辑文件夹名称...</a>",
  			img_delete   + " <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=emo_setremove&id={$data['dir']}'>删除文件夹...</a>"
EOF;
}
else
{
$IPBHTML .= <<<EOF
	img_delete + " <i>默认表情不可删除</i>"
EOF;
}

$IPBHTML .= <<<EOF
  		    ) );
 </script>
 
EOF;

return $IPBHTML;
}

}


?>