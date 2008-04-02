<?php
/**
 * Invision Power Board
 * Template Controller for installer framework
 */

class install_template
{
	var $page_title   = '';
	var $page_content = '';
	var $page_current = '';
	var $message	  = '';
	var $hide_next    = 0;	
	var $in_error	  = 0;

	var $install_pages = array();
	
	var $ipsclass;
	
	/**
	 * install_template::install_template
	 * 
	 * CONSTRUCTOR
	 *
	 */	
	function install_template( &$ipsclass )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$_pages         =  array();
		$this->ipsclass =& $ipsclass;
		
		//-----------------------------------------
		// Grab XML file and check
		//-----------------------------------------
		
		if ( file_exists( INS_ROOT_PATH . 'installfiles/sequence.xml' ) )
		{
			$config = implode( '', file( INS_ROOT_PATH . 'installfiles/sequence.xml' ) );
			$xml = new class_xml();
	
			$config = $xml->xml_parse_document( $config );
			
			//-----------------------------------------
			// Loop through and sort out settings...
			//-----------------------------------------

			foreach( $xml->xml_array['installdata']['action'] as $id => $entry )
			{
				$_pages[ $entry['position']['VALUE'] ] = array( 'file' => $entry['file']['VALUE'],
															    'menu' => $entry['menu']['VALUE'] );
			}
			
			ksort( $_pages );
			
			foreach( $_pages as $position => $data )
			{
				$this->install_pages[ $data['file'] ] = $data['menu'];
			}
		}
		
		$this->install_pages['done'] = '完成升级';
	   
		/* Set Current Page */
		$this->page_current = ( $this->ipsclass->input['p'] ) ? $this->ipsclass->input['p'] : 'login';
		
		if( ! $this->install_pages[$this->page_current] )
		{
			$this->page_current = 'login';	
		}
	}
	
	/**
	 * install_template::set_title
	 * 
	 * Sets the title for the current page
	 *
	 * @var string $title
	 */
	function set_title( $title )
	{
		$this->page_title = $title;	
	}

	/**
	 * install_template::append
	 * 
	 * Adds to the main body output
	 *
	 * @var string $add
	 */
	
	function append( $add )
	{
		$this->page_content .= $add;	
	}
	

	/**
	 * install_template::output
	 * 
	 * Builds page and sends to browser
	 *
	 */	
	function output()
	{
		/* Build Side Bar */
		$curr_reached   = 0;
		$this->progress = array();
		
		foreach( $this->install_pages as $key => $page )
		{
			if( $key == $this->page_current )
			{
				$this->progress[] = array( 'step_doing', $page );
				$curr_reached = 1;
			}
			else if( $curr_reached )
			{
				$this->progress[] = array( 'step_notdone', $page );
			}
			else 
			{
				$this->progress[] = array( 'step_done', $page );
			}
			
		}
		
		$this->page_template();
	}
	
	/***************************************************************
	 *
	 * HTML TEMPLATE FUNCTIONS
	 *
	 **************************************************************/
	
	// ------------------------------------------------------------
	// Main Template
	// ------------------------------------------------------------
	function page_template()
	{
echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>IPS 产品升级系统</title>
		<style type='text/css' media='all'>
			@import url('install.css');
		</style>
		<script type='text/javascript'>
			//<![CDATA[
		  		if (top.location != self.location) { top.location = self.location }
				var use_enhanced_js = 1;
			//]]>
		</script>
		<script type="text/javascript" src='ips_xmlhttprequest.js'></script>	
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<form id='install-form' action='index.php{$this->next_action}' method='post'>
		<input type='hidden' name='saved_data' value='{$this->saved_data}'>
		
		<div id='ipswrapper'>
		    <div class='main_shell'>

		 	    <h1><img src='images/package_icon.gif' align='absmiddle' />欢迎来到 IPB 产品升级向导</h1>
		 	    <div class='content_shell'>
		 	        <div class='package'>
		 	            <div>
		 	                <div class='install_info'>
		 	                    <h3>{$this->install_pages[$this->page_current]}</h3>
		 	                    		 	                    
    		 	                <ul id='progress'>

EOF;

foreach( $this->progress as $p )
{
echo "<li class='{$p[0]}'>{$p[1]}</li>";
}

echo <<<EOF
    		 	                </ul>
    		 	            </div>
		 	            
    		 	            <div class='content_wrap'>
    		 	                <div style='border-bottom: 1px solid #939393; padding-bottom: 4px;'>
    		 	                    <div class='float_img'>
    		 	                        <img src='images/box.gif' />
    		 	                    </div>

    		 	                    <div style='vertical-align: middle'>
    		 	                        <h2>{$this->product_name} 升级向导</h2>
    		 	                        <!--<strong>{$this->product_version}</strong>-->
    		 	                    </div>
    		 	                </div>
    		 	                <div style='clear:both'></div>

        		 	            {$this->page_content}        		 	          
            		 	        <br />        		 	            
    		 	            </div>
		 	            </div>
		 	            <br clear='all' />
    
		 	            <div class='hr'></div>
		 	            <div style='padding-top: 17px; padding-right: 15px; padding-left: 15px'>
		 	                <div style='float: left'>
		 	                    <input type='button' class='nav_button' value='取消升级' onclick="window.location='index.php';return false;">
		 	                </div>

		 	                <div style='float: right'>
EOF;

if( ! $this->hide_next )
{
if( $this->next_action == 'disabled' )
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='无法继续升级...' disabled='disabled'>
EOF;
}
else if( $this->in_error == 1 )
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='强行升级?'>
EOF;
}
else 
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='下一步 >'>
EOF;
}
}

echo <<<EOF
						</div>
		 	            </div>
		 	            <div style='clear: both;'></div>
		 	            <div class='copyright'>
		 	                &copy; 
EOF;
echo date("Y");
echo <<<EOF
 IPBChina.COM & Invision Power Services, Inc.
		 	            </div>
		 	        </div>

		 	    </div>
    		</div>
    	</div>
    	
		</form>
	
	</body>
</html>
EOF;
	}
	
	// ------------------------------------------------------------
	// Login Page Template
	// ------------------------------------------------------------	
	function login_page( $msg='' )
	{
		$output = "";
		if ( $msg )
		{
			$extra = "<div class='warning'>
		        		<div style='float: left; margin-right: 7px; margin-left: 5px'><img src='images/warning.gif' /></div>
						<p>{$msg}</p>
					  </div><br />";
		}


$output .= <<<EOF
        		 	            <br />
        		 	            <div>
        		 	                <div style='float: left; margin-right: 7px; margin-left: 5px;'>
        		 	                    <img src='images/wizard.gif' align='absmiddle' />
        		 	                </div>
        		 	                <div>
        		 	                    欢迎来到 {$this->product_name} 产品升级向导. 这一智能向导将会因到您完成下面的升级过程.
        		 	                </div>
        		 	            </div>
    <br/>{$extra}
    <h3>需要进行身份认证 - 请登录系统</h3>
    您必须以您的管理员身份登录才能进入升级系统.<br />
    <br />
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
			<td width='40%'  valign='middle'> 
EOF;

if( $this->ipsclass->login_type == 'username' )
{
$output .= <<<EOF
会员名称
EOF;
}
else
{
$output .= <<<EOF
邮件地址
EOF;
}
$output .= <<<EOF
:</td>
			<td width='60%'  valign='middle'><input type='text' style='width:100%' name='username' value='' class='sql_form'></td>
		</tr>
		<tr>
			<td width='40%'  valign='middle'>会员密码:</td>
			<td width='60%'  valign='middle'><input type='password' style='width:100%' name='password' value='' class='sql_form'></td>
		</tr>
	</table>
EOF;

	return $output;
	}
	
	// ------------------------------------------------------------
	// Overview Page Template
	// ------------------------------------------------------------	
	function overview_page( $current_version, $summary )
	{
return <<<EOF
        		 	            <br />
        		 	            <div>
        		 	                <div style='float: left; margin-right: 7px; margin-left: 5px;'>
        		 	                    <img src='images/wizard.gif' align='absmiddle' />
        		 	                </div>
        		 	                <div>
        		 	                    欢迎来到 {$this->product_name} 产品的升级向导. 这一智能向导将会因到您完成下面的升级过程.
        		 	                </div>
        		 	            </div>
    <br/>
    <h3>升级概况</h3>
    当前系统版本: $current_version.<br />
    即将升级版本: $summary<br />
    <br />

EOF;
	}

	// ------------------------------------------------------------
	// EULA Page Template
	// ------------------------------------------------------------
	function eula_page( $eula )
	{
return <<<EOF

<script language='javascript'>

check_eula = function()
{
	if( document.getElementById( 'eula' ).checked == true )
	{
		return true;
	}
	else
	{
		alert( '您必须同意授权协议才能进行下一步的安装' );
		return false;
	}
}

document.getElementById( 'install-form' ).onsubmit = check_eula;

</script>

请在进行下一步之前仔细阅读许可协议并且点击表示同意.<br /><br />

        		 	            
        		 	            <div class='eula'>
									$eula        		 	                
                                </div>
                                <input type='checkbox' name='eula' id='eula'><strong> 我同意许可协议</strong>


EOF;
	}
	
	
	// ------------------------------------------------------------
	// Install Page Splash Template
	// ------------------------------------------------------------		
	function install_page( $show_manual=0 )
	{
		$output = "";
		
$output = <<<EOF
<br />
升级系统已经准备好对您的 {$this->product_name} 产品进行升级. 请点击 <strong>开始</strong> 来启动自动的升级过程!<br /><br />
    <ul id='links'>
        <li><img src='images/link.gif' align='absmiddle' /> <input type='checkbox' name='helpfile' id='helpfile' value='1' checked='checked' /> 升级的同时更新我的帮助文件</li>
EOF;

if( $show_manual == 1 )
{
$output .= <<<EOF
        <li><img src='images/link.gif' align='absmiddle' /> <input type='checkbox' name='man' id='man' value='1' /> 向我展示每一步的手动数据升级指令以防升级过程中出现 PHP 页面执行时间超出. <b>警告:</b> 若您选择了这一选项, 系统将会给您展示手动数据升级指令, 这些指令必须在数据库管理软件中执行. 如果您完成这一过程有困难, 请给我们发送一个支持请求, 这样我们的技术人员将会帮助您完成相应的过程, 或者您也可以联系您的主机服务商以寻求帮助.</li>
EOF;
}

$output .= <<<EOF
    </ul>

<br /><br />
        		 	            
        		 	            <div style='float: right'>
        		 	                <input type='submit' class='nav_button' value='执行升级...'>
        		 	            </div>
EOF;

		return $output;
	}
	
	// ------------------------------------------------------------
	// Install Page Refresh Template
	// ------------------------------------------------------------		
	function install_page_refresh( $output=array() )
	{
$HTML = <<<EOF
<script type='text/javascript'>
//<![CDATA[
setTimeout("form_redirect()",2000);

function form_redirect()
{
	document.getElementById( 'install-form' ).submit();
}
//]]>
</script>
    		 	                <ul id='auto_progress'>
EOF;

foreach( $output as $l )
{
$HTML .= <<<EOF
    		 	                    <li><img src='images/check.gif' align='absmiddle' /> $l</li>
EOF;
}

$HTML .= <<<EOF
    		 	                </ul>
								<br />
								<div style='float: right'>
									<input type='submit' class='nav_button' value='点击这里如果跳转没有自动完成' />
								</div>
EOF;

		return $HTML;
	}
	
	// ------------------------------------------------------------
	// Install Progress Screen
	// ------------------------------------------------------------		
	function install_progress( $line )
	{
$HTML = <<<EOF
    		 	                <ul id='auto_progress'>
EOF;

foreach( $line as $l )
{
$HTML .= <<<EOF
    		 	                    <li><img src='images/check.gif' align='absmiddle' /> $l</li>
EOF;
}

$HTML .= <<<EOF
    		 	                </ul>
EOF;

		return $HTML;
	}
	
	
	// ------------------------------------------------------------
	// Install Skin Revert
	// ------------------------------------------------------------		
	function install_template_skinrevert( $skin_name="" )
	{
$HTML = <<<EOF
		<br /><h3><b>还原主题更改?</b></h3><br />
		在系统升级过程中, 经常会有主题模板的更改或者添加来修正错误.<br /><br />
		如果您不对主题更改进行还原操作, 您将无法看到新版的主题升级, 然而如果您对您的主题进行了修改那么执行还原操作将使得您 <i><b>丢失</b></i> 您的定制内容.<br /><br />
		如果您没有对您的主题进行过任何更改, 我们强烈建议您选择还原主题更改.<br /><br />
		如果您曾经安装过其他的主题文件, 或者您曾经对主题进行过大面积的更改, 我们建议您在系统后台运行主题差异比较操作来进行手动的修改.<br /><br />
		
		<h3>Do you wish to revert changes made to '<b>{$skin_name}</b>'?</h3>
            <ul id='links'>
                <li><img src='images/link.gif' align='absmiddle' /> <input type='radio' name='do' value='all' /> 还原我所有的主题修改</li>
                <li><img src='images/link.gif' align='absmiddle' /> <input type='radio' name='do' value='1' /> 还原 '{$skin_name}' 的主题修改</li>
                <li><img src='images/link.gif' align='absmiddle' /> <input type='radio' name='do' value='none' /> 不要还原我任何的主题修改</li>
                <li><img src='images/link.gif' align='absmiddle' /> <input type='radio' name='do' value='0' /> 不要还原 '{$skin_name}' 的主题修改</li>
            </ul>
EOF;

		return $HTML;
	}	
	
	// ------------------------------------------------------------
	// Install Done Screen
	// ------------------------------------------------------------		
	function install_done( $url )
	{
$HTML .= <<<EOF
        		 	            <br />
        		 	            <img src='images/install_done.gif' align='absmiddle' />&nbsp;&nbsp;<span class='done_text'>系统升级成功!</span><br /><br />
        		 	            祝贺, 您的 <a href='$url'>{$this->product_name}</a> 产品已经完成了升级可以正常使用了!<br /><br />
        		 	            您应当登录到管理后台并且运行位于 工具 &amp; 设置 菜单下的 '重建帖子内容' 工具和 统计 &amp; 重建菜单下的 '重建附件数据' 工具. 您也可以执行在 '清理工具' 菜单下的 2.1 -&gt; 2.2 工具来进行清理.
        		 	            <br /><br />下面是一些有用的链接提示.<br /><br /><br />
        		 	            <h3>有用的链接</h3>
        		 	            <ul id='links'>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://external.ipslink.com/ipboard22/landing/?p=clientarea'>客户专区</a></li>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://external.ipslink.com/ipboard22/landing/?p=docs-ipb'>官方文档</a></li>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://external.ipslink.com/ipboard22/landing/?p=forums'>官方论坛</a></li>
        		 	            </ul>
EOF;
		return $HTML;
	}
	
	// ------------------------------------------------------------
	// Warning Message Template
	// ------------------------------------------------------------	
	function warning( $messages )
	{
$HTML = <<<EOF
<br />
    <div class='warning'>
        <div style='float: left; margin-right: 7px; margin-left: 5px'><img src='images/warning.gif' /></div>
EOF;

foreach( $messages as $msg )
{
	$HTML .= "<p>$msg</p>";	
}

$HTML .= <<<EOF
    </div><br />
   
EOF;

		$this->append( $HTML );
	}
}

?>