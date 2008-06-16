<?php

class cp_skin_bbcode_badword {

var $ipsclass;

//===========================================================================
// BBCODE: Wrapper
//===========================================================================
function bbcode_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF

 <div class="tableborder">
						<div class="tableheaderalt">
						<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
				  <td align='left' width='100%' style='font-weight:bold;font-size:12px;color:#FFF'>您的自定义 BBCode</td>
				  <td align='right' nowrap='nowrap' style='padding-right:6px'><img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
				  </tr>
				  </table> </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='45%'>标题</td>
  <td class='tablesubheader' width='50%'>标签</td>
  <td class='tablesubheader' width='5%'>选项</td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'><div class='fauxbutton-wrapper'><span class='fauxbutton'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bbcode_add'>添加新 BBCode</a></span></div></div>
</div>
<br />

 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bbcode_export'>导出 BBCode...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// BBCODE: Row
//===========================================================================
function bbcode_row( $row ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>{$row['bbcode_title']}</td>
 <td class='tablerow1'>{$row['bbcode_fulltag']}</td>
 <td class='tablerow2'><img id="menu{$row['bbcode_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$row['bbcode_id']}",
  new Array( img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bbcode_edit&id={$row['bbcode_id']}'>编辑 BBCode...</a>",
  			 img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bbcode_export&id={$row['bbcode_id']}'>导出 BBCode...</a>",
  			 img_delete   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bbcode_delete&id={$row['bbcode_id']}'>删除 BBCode...</a>"
		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}


//===========================================================================
// BADWORD: Wrapper
//===========================================================================
function badword_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF

 <div class="tableborder">
						<div class="tableheaderalt">
						<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
				  <td align='left' width='100%' style='font-weight:bold;font-size:12px;color:#FFF'>当前过滤器</td>
				  <td align='right' nowrap='nowrap' style='padding-right:6px'><img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
				  </tr>
				  </table> </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='40%'>之前</td>
  <td class='tablesubheader' width='40%'>之后</td>
  <td class='tablesubheader' width='15%'>方法</td>
  <td class='tablesubheader' width='5%'>选项</td>
 </tr>
 $content
 </table>
</div>
<br />
<div class="tableborder">
						<div class="tableheaderalt">添加新过滤器</div>

<table align="center" border="0" cellpadding="5" cellspacing="0" width="100%"><tbody><tr>
<td class="tablesubheader" align="center" width="40%">之前</td>
<td class="tablesubheader" align="center" width="40%">之后</td>
<td class="tablesubheader" align="center" width="20%">方法</td>
</tr>
<tr>

<td class="tablerow1" valign="middle" width="40%"><input name="before" value="" size="30" class="textinput" type="text"></td>
<td class="tablerow2" valign="middle" width="40%"><input name="after" value="" size="30" class="textinput" type="text"></td>
<td class="tablerow1" valign="middle" width="20%"><select name="match" class="dropdown">
<option value="1">精确</option>
<option value="0">模糊</option>
</select>

</td>
</tr>
<tr><td class="tablesubheader" colspan="3" align="center"><input value="添加过滤器" class="realbutton" accesskey="s" type="submit"></form></td></tr>
</table></div><br />

 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=badword_export'>导出词语过滤...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// BADWORD: Row
//===========================================================================
function badword_row( $row ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'>{$row['type']}</td>
 <td class='tablerow1'>{$row['replace']}</td>
 <td class='tablerow1'>{$row['method']}</td>
 <td class='tablerow2'><img id="menu{$row['wid']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='Options' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$row['wid']}",
  new Array( img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=badword_edit&id={$row['wid']}'>编辑词语过滤...</a>",
  			 img_delete   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=badword_remove&id={$row['wid']}'>删除词语过滤...</a>"
		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}


}


?>