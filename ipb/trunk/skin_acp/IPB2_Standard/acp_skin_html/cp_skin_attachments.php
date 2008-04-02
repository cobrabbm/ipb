<?php

class cp_skin_attachments {

var $ipsclass;

//===========================================================================
// Attachments: Wrapper
//===========================================================================
function attach_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF

 <div class="tableborder">
						<div class="tableheaderalt">
						<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				  <tr>
				  <td align='left' width='100%' style='font-weight:bold;font-size:11px;color:#FFF'>您的附件类型</td>
				  <td align='right' nowrap='nowrap' style='padding-right:6px'><img id='menumainone' src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
				  </tr>
				  </table> </div>
 <table cellpadding='0' cellspacing='0' width='100%'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='20%'>扩展</td>
  <td class='tablesubheader' width='40%'>Mime-Type</td>
  <td class='tablesubheader' width='10%'>+帖子</td>
  <td class='tablesubheader' width='10%'>+头像</td>
  <td class='tablesubheader' width='5%'>选项</td>
 </tr>
 $content
 </table>
 <div align='center' class='tablefooter'><div class='fauxbutton-wrapper'><span class='fauxbutton'><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=attach_add'>添加新的附件类型</a></span></div></div>
</div>
<br />

 <script type="text/javascript">
  menu_build_menu(
  "menumainone",
  new Array( img_export   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=attach_export'>导出附件类型...</a>" ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Attachments: Row
//===========================================================================
function attach_row( $row ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td class='tablerow1'><img src='{$this->ipsclass->vars['board_url']}/style_images/{$row['_imagedir']}/{$row['atype_img']}' border='0' /></td>
 <td class='tablerow1'>.<strong>{$row['atype_extension']}</strong></td>
 <td class='tablerow1'>{$row['atype_mimetype']}</td>
 <td class='tablerow1'>{$row['apost_checked']}</td>
 <td class='tablerow1'>{$row['aphoto_checked']}</td>
 <td class='tablerow2'><img id="menu{$row['atype_id']}" src='{$this->ipsclass->skin_acp_url}/images/filebrowser_action.gif' border='0' alt='选项' class='ipd' /></td>
</tr>
<script type="text/javascript">
  menu_build_menu(
  "menu{$row['atype_id']}",
  new Array( img_edit   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=attach_edit&amp;id={$row['atype_id']}'>编辑附件类型...</a>",
  			 img_delete   + " <a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=attach_delete&amp;id={$row['atype_id']}'>删除附件类型...</a>"
		    ) );
 </script>
EOF;

//--endhtml--//
return $IPBHTML;
}



}


?>