<?php

class cp_skin_components {

var $ipsclass;

//===========================================================================
// Member: validating
//===========================================================================
function welcome_page() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tablesubheader'>易维论坛信息</div>
 <div class='tablerow1'>
 这一部分被保留用来添加论坛组件信息, 比如博客、相册、聊天室等等组件.
 </div>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}



}

?>