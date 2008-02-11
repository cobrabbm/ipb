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
		
		$this->install_pages['done'] = '安装结束';
	   
		/* Set Current Page */
		$this->page_current = ( $this->ipsclass->input['p'] ) ? $this->ipsclass->input['p'] : 'requirements';
		
		if( ! $this->install_pages[$this->page_current] )
		{
			$this->page_current = 'requirements';	
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
		$this->saved_data = urlencode($this->saved_data);
		
echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>IPS 安装程序</title>
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

		 	    <h1><img src='images/package_icon.gif' align='absmiddle' /> 欢迎来到 IPS 产品安装程序</h1>
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
    		 	                        <h2>{$this->product_name} 安装</h2>
    		 	                        <strong>{$this->product_version}</strong>
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
		 	                    <input type='button' class='nav_button' value='取消安装' onclick="window.location='index.php';return false;" />
		 	                </div>

		 	                <div style='float: right'>
EOF;

if( ! $this->hide_next )
{
if( $this->next_action == 'disabled' )
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='安装无法继续...' disabled='disabled' />
EOF;
}
else 
{
if( !$this->next_action )
{
	$back = $this->ipsclass->my_getenv('HTTP_REFERER');
	
echo <<<EOF
	<input type='button' class='nav_button' value='< 上一步' onclick="window.location='{$back}';return false;" />
EOF;
}
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='下一步 >' />
EOF;
}
}

$date = date("Y");

echo <<<EOF
						</div>
		 	            </div>
		 	            <div style='clear: both;'></div>
		 	            <div class='copyright'>
		 	                &copy; 
EOF;
echo date("Y");
echo <<<EOF
 IPBChina.COM Team & Invision Power Services, Inc.  
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
	// Requirements Page Template
	// ------------------------------------------------------------	
	function requirements_page( $php_version, $sql_version )
	{
return <<<EOF
        		 	            <div>

        		 	                <div style='float: left; margin-right: 7px; margin-left: 5px;'>
        		 	                    <img src='images/wizard.gif' align='absmiddle' />
        		 	                </div>
        		 	                <div>
        		 	                    欢迎来到 {$this->product_name} 产品的安装向导. 这一向导程序将引导您完成安装过程.
        		 	                </div>
        		 	            </div>
    <br/><br/>
    		 	            
    <h3>系统需求</h3>

    <br />
    <strong>PHP:</strong> v{$php_version} 或更高版本<br />
    <strong>SQL:</strong> mySQL v$sql_version 或更高版本
    <br /><br />
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
	// Address Page Template
	// ------------------------------------------------------------	
	function address_page( $dir, $url )
	{
return <<<EOF
<div id='warn-message' style='display:none;'><center><div id='warn-message-content'></div></center></div>

        		 	            <fieldset>
        		 	                <legend><img src='images/addresses.gif' align='absmiddle' />&nbsp; 论坛地址设置</legend>

        		 	                <table style='width: 100%; border: 0px; padding:0px' cellspacing='0'>
            		 	                <tr>
            		 	                    <td width='30%' class='title'>安装位置:</td>
            		 	                    <td width='70%' class='content'><input type='text' class='sql_form' name='install_dir' value='{$dir}'></td>
            		 	                </tr>

        		 	                	<tr>
            		 	                    <td width='30%' class='title'>访问地址:</td>
            		 	                    <td width='70%' class='content'><input type='text' class='sql_form' name='install_url' value='{$url}'></td>
            		 	                </tr>
            		 	            </table>
            		 	        </fieldset>
EOF;
	}
	
	// ------------------------------------------------------------
	// DB Check Page Template
	// ------------------------------------------------------------		
	function db_check_page( $drivers=array() )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$_drivers = '';
		
		foreach ($drivers as $k => $v)
		{
			$selected  = ($v == "mysql") ? " selected='selected'" : "";
			$_drivers .= "<option value='".$v."'".$selected.">".strtoupper($v)."</option>\n";
		}
		
return <<<EOF
<div class='info' style='margin-top: 4px;'>
        		 	                <div class='float_img'><img src='images/help.gif' /></div>

        		 	                <div>请选择下面您所使用的数据库引擎.</div>
        		 	            </div>
        		 	            
        		 	            <fieldset>
        		 	                <legend><img src='images/db.gif' align='absmiddle' />&nbsp; 数据库引擎</legend>
        		 	                <table style='width: 100%; border: 0px; padding:0px' cellspacing='0'>
										<tr>
            		 	                    <td width='30%' class='title'>SQL Driver:</td>
            		 	                    <td width='70%' class='content'>
            		 	                    	<select name='sql_driver' class='sql_form'>$_drivers</select>
            		 	                    </td>
            		 	                </tr>
            		 	            </table>
            		 	        </fieldset>

EOF;
	}
	// ------------------------------------------------------------
	// DB Page Template
	// ------------------------------------------------------------		
	function db_page()
	{
		$prefix = $_REQUEST['db_pre'] ? $_REQUEST['db_pre'] : INS_DEFAULT_SQL_PREFIX;
		
return <<<EOF
<div class='info' style='margin-top: 4px;'>
    <div class='float_img'><img src='images/help.gif' /></div>

    <div>如果您对任何的设置有疑问请您咨询空间服务商. 在进行下一步之前您必须首先创建一个数据库..</div>
</div>

<fieldset>
    <legend><img src='images/db.gif' align='absmiddle' />&nbsp; 数据库设置</legend>
    <table style='width: 100%; border: 0px; padding:0px' cellspacing='0'>
		<!--{TOP.SQL}-->
        <tr>
            <td width='30%' class='title'>数据库主机:</td>
            <td width='70%' class='content'>
            	<input type='text' class='sql_form' value='localhost' name='db_host'>
            </td>
        </tr>
        <tr>
            <td class='title'>数据库名称:</td>
            <td class='content'>
            	<input type='text' class='sql_form' name='db_name' value='{$_REQUEST['db_name']}'>
            </td>
        </tr>
        <tr>
            <td class='title'>数据库用户:</td>
            <td class='content'>
            	<input type='text' class='sql_form' name='db_user' value='{$_REQUEST['db_user']}'>
            </td>
        </tr>
        <tr>
            <td class='title'>数据库密码:</td>
            <td class='content'>
            	<input type='password' class='sql_form' name='db_pass'>
            </td>
        </tr>
        <tr>
            <td class='title'>数据表前缀:</td>
            <td class='content'>
            	<input type='text' class='sql_form' name='db_pre' value='$prefix'>
            </td>
        </tr>
<!--{EXTRA.SQL}-->
    </table>
</fieldset>

EOF;
	}
	
	// ------------------------------------------------------------
	// Admin Page Template
	// ------------------------------------------------------------		
	function admin_page()
	{
return <<<EOF
								<fieldset>
        		 	                <legend><img src='images/admin.gif' align='absmiddle' />&nbsp; 管理员账号设置</legend>
        		 	                <table style='width: 100%; border: 0px; padding:0px' cellspacing='0'>
            		 	                <tr>
            		 	                    <td width='30%' class='title'>会员名称:</td>

            		 	                    <td width='70%' class='content'><input type='text' class='sql_form' name='username'></td>
            		 	                </tr>
            		 	                <tr>
            		 	                    <td class='title'>会员密码:</td>
            		 	                    <td class='content'><input type='password' class='sql_form' name='password'></td>
            		 	                </tr>
            		 	                <tr>
            		 	                    <td class='title'>确认密码:</td>

            		 	                    <td class='content'><input type='password' class='sql_form' name='confirm_password'></td>
            		 	                </tr>
            		 	                <tr>
            		 	                    <td class='title'>邮件地址:</td>
            		 	                    <td class='content'><input type='text' class='sql_form' name='email'></td>
            		 	                </tr>
            		 	            </table>
            		 	        </fieldset>

EOF;
	}
	
	// ------------------------------------------------------------
	// Install Page SPlash Template
	// ------------------------------------------------------------		
	function install_page()
	{
return <<<EOF
安装程序已经准备好安装 {$this->product_name} 产品. 请点击 <strong>开始</strong> 来启动自动的安装进程!<br /><br />

        		 	            
        		 	            <div style='float: right'>
        		 	                <input type='submit' class='nav_button' value='开始安装...'>
        		 	            </div>
EOF;
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
	// Install Done Screen
	// ------------------------------------------------------------		
	function install_done( $url, $install_locked )
	{
		$extra = '';
		
		if ( ! $install_locked )
		{
			$extra = "<div class='warning'>
		        		<div style='float: left; margin-right: 7px; margin-left: 5px'><img src='images/warning.gif' /></div>
						<p>安装没有锁定<br />请立即删除 'install/index.php' 文件!</p>
					  </div>";
		}
		
$HTML .= <<<EOF
        		 	            <br />

        		 	            <img src='images/install_done.gif' align='absmiddle' />&nbsp;&nbsp;<span class='done_text'>安装成功!</span><br /><br />
        		 	            祝贺您的 <a href='$url'>{$this->product_name}</a> 已经可以使用了! 下面是一些有用的链接提示.<br /><br /><br />
        		 	            $extra
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
