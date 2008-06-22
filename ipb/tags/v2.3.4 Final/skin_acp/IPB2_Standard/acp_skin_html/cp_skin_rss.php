<?php

class cp_skin_rss {

var $ipsclass;


//===========================================================================
// RSS
//===========================================================================
function rss_export_overview($content, $page_links) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>RSS 导出源</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='90%'>RSS 标题</td>
  <td class='tablesubheader' width='5%' align='center'>开启</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
 </tr>
EOF;

if( $page_links != "" )
{
$IPBHTML .= <<<EOF
 <tr>
  <td class='tablesubheader' colspan='3' align='right'>
  	{$page_links}
  </td>
 </tr>
EOF;
}

$IPBHTML .= <<<EOF
 {$content}
EOF;

if( $page_links != "" )
{
$IPBHTML .= <<<EOF
 <tr>
  <td class='tablesubheader' colspan='3' align='right'>
  	{$page_links}
  </td>
 </tr>
EOF;
}

$IPBHTML .= <<<EOF
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
<script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_add   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssexport_add'>创建新 RSS 导出源...</a>",
  			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssexport_recache&amp;rss_export_id=all'>更新所有 RSS 导出缓存...</a>"
           ) );
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// RSS
//===========================================================================
function rss_export_overview_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
   <a target='_blank' href='{$this->ipsclass->vars['board_url']}/index.php?act=rssout&amp;id={$data['rss_export_id']}'><img src='{$this->ipsclass->skin_acp_url}/images/rss.png' border='0' alt='RSS' style='vertical-align:top' /></a>
   <strong>{$data['rss_export_title']}</strong>
 </td>
 <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['_enabled_img']}' border='0' alt='YN' class='ipd' /></td>
 <td class='tablerow1'><img id="menu{$data['rss_export_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['rss_export_id']}",
  new Array(
			img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssexport_edit&amp;rss_export_id={$data['rss_export_id']}'>编辑 RSS 导出源...</a>",
  			img_delete + " <a href='#' onclick='maincheckdelete(\"{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssexport_delete&amp;rss_export_id={$data['rss_export_id']}\");'>删除 RSS 导出源...</a>",
  			img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssexport_recache&amp;rss_export_id={$data['rss_export_id']}'>重建 RSS 导出源缓存...</a>"
  		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// RSS FORM
//===========================================================================
function rss_export_form($form, $title, $formcode, $button, $rssstream) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;rss_export_id={$rssstream['rss_export_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>$title</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出源 (Channel) 标题</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_title']}</td>
 </tr>
<tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出源 (Channel) 描述</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_desc']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出源图片</strong><div class='desctext'>在 RSS 阅览器中显示的图标</div></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_image']} <span class='desctext'>* Optional</span></td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出开启</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_enabled']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出包括最新回帖</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_include_post']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出条目序号</strong><div class='desctext'>导出 <em>n</em> 个主题</div></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_count']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出字段重排序</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_order']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 到处排序方式</strong></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_sort']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>导出以下论坛标题</strong><div class='desctext'>重要: 导出内容没有权限检查. 导出将包括所有选择的论坛而不进行权限检查.</div></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_forums']}</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>RSS 导出缓存更新频率</strong><div class='desctext'>更新 RSS 缓存间隔 <em>n</em> 分钟</div></td>
   <td width='60%' class='tablerow2'>{$form['rss_export_cache_time']} <span class='desctext'>分钟</span></td>
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
// RSS FORM
//===========================================================================
function rss_import_remove_articles_form( $rssstream, $article_count ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_remove_complete&amp;rss_import_id={$rssstream['rss_import_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<div class='tableborder'>
 <div class='tableheaderalt'>删除以下源的主题: {$rssstream['rss_import_title']}</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td colspan='2' class='tablerow1'>您将删除来自以下 RSS 源的主题. 该源主题共有 <strong>{$article_count} 个</strong> topic(s).</td>
 </tr>
 <tr>
   <td width='40%' class='tablerow1'><strong>删除最后 <em>n</em> 个导入的主题</strong><div class='desctext'>留空表示删除所有</div></td>
   <td width='60%' class='tablerow2'><input type='text' name='remove_count' value='10' /></td>
 </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='删除 (没有进一步的提示)' /></div>
</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// RSS FORM
//===========================================================================
function rss_import_form($form, $title, $formcode, $button, $rssstream) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF

<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
<script type='text/javascript'>
<!--
function enable_auth_boxes()
{
	auth_req = document.getElementById('rss_import_auth_userinfo');
	if( auth_req.style.display == 'none' )
	{
		auth_req.style.display = '';
	}
	else
	{
		auth_req.style.display = 'none';
	}
}

function do_validate()
{
	formobj = document.getElementById('rssimport_validate');
	formobj.value = "1";
	document.getElementById('rssimport_form').submit();
}
	
-->
</script>
<form id='rssimport_form' action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=$formcode&amp;rss_import_id={$rssstream['rss_import_id']}' method='post'>
<input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' />
<input id='rssimport_validate' type='hidden' name='rssimport_validate' value='0' />
<div class='tableborder'>
 <div class='tableheaderalt'>$title</div>
 <table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
   <td class='tablerow1'>
      <fieldset>
       <legend><strong>RSS 导入基本信息</strong></legend>
 		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 		<tr>
   		  <td width='40%' class='tablerow1'><strong>RSS 导入源标题</strong></td>
   		  <td width='60%' class='tablerow2'>{$form['rss_import_title']}</td>
 		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入源 URL</strong><div class='desctext'>必须为 RDF 或者 RSS 格式源</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_url']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入源字符编码</strong><div class='desctext'>例如: ISO-8859-1, UTF-8. 如果有任何疑问请使用 UTF-8.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_charset']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入开启</strong></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_enabled']}</td>
		</tr>
	   </table>
	 </fieldset>
  </td>
 </tr>
  <tr>
   <td class='tablerow1'>
      <fieldset>
       <legend><strong>RSS 导入 htaccess 认证</strong></legend>
 		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 		<tr>
   		  <td width='40%' class='tablerow1'><strong>该源需要 .htaccess 认证吗?</strong><div class='desctext'>大多数的源并不需要认证</div></td>
   		  <td width='60%' class='tablerow2'>{$form['rss_import_auth']}</td>
 		</tr>
		<tr>
		  <td colspan='2' width='100%' id='rss_import_auth_userinfo' {$form['rss_div_show']}>
		   <table cellpadding='0' cellspacing='0' border='0' width='100%'>
		    <tr>
		  		<td width='40%' class='tablerow1'><strong>RSS 导入源会员名称</strong></td>
		 		<td width='60%' class='tablerow2'>{$form['rss_import_auth_user']}</td>
		 	</tr>
		 	<tr>
		  		<td width='40%' class='tablerow1'><strong>RSS 导入源会员密码</strong></td>
		  		<td width='60%' class='tablerow2'>{$form['rss_import_auth_pass']}</td>
			</tr>
		  </table>
		 </td>
		</tr>
	   </table>
	 </fieldset>
  </td>
 </tr>
 <tr>
  <td class='tablerow1'>
  	<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:170px;display:none;z-index:100'></div>
      <fieldset>
       <legend><strong>RSS 导入内容</strong></legend>
 		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入到论坛</strong><div class='desctext'>选择一个论坛导入 RSS 条目为主题</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_forum_id']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入 HTML 保留</strong><div class='desctext'>如果开启这一选项, 所有 HTML 将会保留(包含可能不安全的标签) - 该论坛必须开启 HTML 编辑权限否则 HTML 标记将会在您编辑时被移除. 如果关闭这一选项, HTML 标签将会转换为可能的 BBCode 标签.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_allow_html']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入会员名称</strong><div class='desctext'>导入的 RSS 主题将会作为该会员的主题 (填写会员显示名称)</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_mid']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入增加会员主题数</strong><div class='desctext'>如果相应的论坛开启了主题计数, 这一选项将增加会员的帖子数目</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_inc_pcount']}</td>
		</tr>
		 <tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入添加原文链接</strong><div class='desctext'>BBCode 开启: {url} = 原文 URL 地址<br />填写完整这一选项将会在导入主题的后面添加原文链接</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_showlink']} <div class='desctext'>*留空表示不添加原文链接</div></td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入主题开启</strong><div class='desctext'>开启这一选项将导入主题将处于开启状态. 关闭这一选项导入主题将处于关闭状态.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_topic_open']}</td>
		</tr>
	    <tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入主题隐藏</strong><div class='desctext'>开启这一选项将导入主题将处于隐藏状态. 关闭这一选项导入主题将处于可见状态.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_topic_hide']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入主题前缀</strong><div class='desctext'>这一前缀将会添加到导入  RSS 主题的标题前面</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_topic_pre']}</td>
		</tr>
	   </table>
	 </fieldset>
	</td>
  </tr>
  <tr>
    <td class='tablerow1'>
      <fieldset>
       <legend><strong>RSS 导入设置</strong></legend>
 		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入主题数目</strong><div class='desctext'>每次更新导入 <em>n</em> 个主题. 导入资源将会集中更新.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_pergo']}</td>
		</tr>
		<tr>
		  <td width='40%' class='tablerow1'><strong>RSS 导入更新</strong><div class='desctext'>每隔 <em>n</em> 分钟进行一次导入主题更新. 最小间隔 30 分钟, 忽略导入时间.</div></td>
		  <td width='60%' class='tablerow2'>{$form['rss_import_time']}</td>
		</tr>
	   </table>
	 </fieldset>
	</td>
  </tr>
 </table>
 <div align='center' class='tablefooter'><input type='submit' class='realbutton' value='$button' /> &nbsp;&nbsp;&nbsp;
 										 <input type='button' class='realbutton' value='验证源有效性' onclick='do_validate();' /></div>
</div>
</form>
<script type="text/javascript">
	// INIT find names
	init_js( 'rssimport_form', 'rss_import_mid');
	// Run main loop
	var tmp = setTimeout( 'main_loop()', 10 );
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// RSS
//===========================================================================
function rss_import_overview($content, $page_links) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>快速验证 RSS 源?</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablerow1'><b>输入源地址:</b></td>
  <td class='tablerow2'><form action='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_validate' method='post'><input type='hidden' name='_admin_auth_key' value='{$this->ipsclass->_admin_auth_key}' /><input type='text' size='50' name='rss_url' value='http://' /> <input type='submit' class='realbutton' value='验证' /></form></td>
 </tr>
 </table>
</div>
<br />  
<div class='tableborder'>
 <div class='tableheaderalt'>RSS 导入源</div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='90%'>RSS 标题</td>
  <td class='tablesubheader' width='5%' align='center'>开启</td>
  <td class='tablesubheader' width='5%'><img id="menumainone" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
 </tr>
EOF;

if( $page_links != "" )
{
$IPBHTML .= <<<EOF
 <tr>
  <td class='tablesubheader' colspan='3' align='right'>
  	{$page_links}
  </td>
 </tr>
EOF;
}

$IPBHTML .= <<<EOF
 {$content}
EOF;

if( $page_links != "" )
{
$IPBHTML .= <<<EOF
 <tr>
  <td class='tablesubheader' colspan='3' align='right'>
  	{$page_links}
  </td>
 </tr>
EOF;
}

$IPBHTML .= <<<EOF
 </table>
 <div align='center' class='tablefooter'>&nbsp;</div>
</div>
<script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_add   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_add'>创建新 RSS 导入源...</a>",
  			 img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_recache&amp;rss_import_id=all'>更新所有 RSS 导入...</a>"
           ) );
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// RSS
//===========================================================================
function rss_validate_msg( $info ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
  <span class='{$info['class']}'>{$info['msg']}</span>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// RSS
//===========================================================================
function rss_import_overview_row( $data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>
   <a target='_blank' href='{$data['rss_import_url']}'><img src='{$this->ipsclass->skin_acp_url}/images/rss.png' border='0' alt='RSS' style='vertical-align:top' /></a>
   <strong>{$data['rss_import_title']}</strong>
 </td>
 <td class='tablerow2' align='center'><img src='{$this->ipsclass->skin_acp_url}/images/{$data['_enabled_img']}' border='0' alt='YN' class='ipd' /></td>
 <td class='tablerow1'><img id="menu{$data['rss_import_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$data['rss_import_id']}",
  new Array(
			img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_edit&amp;rss_import_id={$data['rss_import_id']}'>编辑 RSS 导入源...</a>",
  			img_delete   + " <a href='#' onclick='maincheckdelete(\"{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_delete&amp;rss_import_id={$data['rss_import_id']}\");'>删除 RSS 导入源...</a>",
  			img_delete   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_remove&amp;rss_import_id={$data['rss_import_id']}'>删除 RSS 导入主题...</a>",
  			img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_recache&amp;rss_import_id={$data['rss_import_id']}'>更新 RSS 导入主题...</a>",
  			img_item   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=rssimport_validate&amp;rss_id={$data['rss_import_id']}'>验证 RSS 源...</a>"
  		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}



}


?>