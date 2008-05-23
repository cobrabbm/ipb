<?php
/**
 * IDM Module v1.2.0
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
		
		$this->install_pages['done'] = 'Finish';
	   
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
		<title>IPS Installer</title>
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
	</head>
	<body>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<form id='install-form' action='index.php{$this->next_action}' method='post'>
		<input type='hidden' name='saved_data' value='{$this->saved_data}'>
		
		<div id='ipswrapper'>
		    <div class='main_shell'>

		 	    <h1><img src='images/package_icon.gif' align='absmiddle' /> Welcome to the IPS Product Installer</h1>
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
    		 	                        <h2>{$this->product_name} Installation</h2>
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
		 	                   <input type='button' class='nav_button' value='Cancel' onclick="window.location='index.php';return false;" />
		 	                </div>

		 	                <div style='float: right'>
EOF;

if( ! $this->hide_next )
{
if( $this->next_action == 'disabled' )
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='Install can not continue...' disabled='disabled' />
EOF;
}
else if( $this->in_error == 1 )
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='Continue regardless?' />
EOF;
}
else 
{
echo <<<EOF
		 	                    <input type='submit' class='nav_button' value='Next &gt;' />
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
 Invision Power Services, Inc.
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
        		 	                    Welcome to the installer for {$this->product_name}. This wizard will guide you through the installation process.
        		 	                </div>
        		 	            </div>
    <br/>{$extra}
    <h3>Verification Required - Please Log In</h3>
    You must log in with your forums administrative log in details to access the IP.Downloads installation system.<br />
    <br />
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
			<td width='40%'  valign='middle'>Your Forum 
EOF;

if( $this->ipsclass->login_type == 'username' )
{
	$output .= "Username";
}
else
{
	$output .= "Email Address";
}

$output .= <<<EOF
:</td>
			<td width='60%'  valign='middle'><input type='text' style='width:100%' name='username' value='' class='sql_form' /></td>
		</tr>
		<tr>
			<td width='40%'  valign='middle'>Your Forum Password:</td>
			<td width='60%'  valign='middle'><input type='password' style='width:100%' name='password' value='' class='sql_form' /></td>
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
        		 	                    Welcome to the installer for {$this->product_name}. This wizard will guide you through the installation process.
        		 	                </div>
        		 	            </div>
    <br/>
    <h3>Installation summary</h3>
    Current version: $current_version.<br />
    This script will: $summary<br />
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
		alert( 'You must agree to the license before continuing' );
		return false;
	}
}

document.getElementById( 'install-form' ).onsubmit = check_eula;

</script>

Please read and agree to the End User License Agreement before continuing.<br /><br />

        		 	            
        		 	            <div class='eula'>
									$eula        		 	                
                                </div>
                                <input type='checkbox' name='eula' id='eula' /><strong> I agree to the license agreement</strong>


EOF;
	}
	
	
	// ------------------------------------------------------------
	// Install Page Splash Template
	// ------------------------------------------------------------		
	function install_page()
	{
return <<<EOF
<br />
The installer is now ready to complete the installation of your {$this->product_name}. Click <strong>Start</strong> to 
begin the automatic process!<br /><br />
<input type='checkbox' name='helpfile' id='helpfile' value='1'>Do <strong>NOT</strong> update my help files if changes are found.
<br /><br />
        		 	            
        		 	            <div style='float: right'>
        		 	                <input type='submit' class='nav_button' value='Start installation...' />
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
									<input type='submit' class='nav_button' value='Click here if not forwarded' />
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
	function install_done( $url )
	{
$HTML .= <<<EOF
        		 	            <br />
        		 	            <img src='images/install_done.gif' align='absmiddle' />&nbsp;&nbsp;<span class='done_text'>Installation complete!</span><br /><br />
        		 	            Congratulations, your <a href='$url'>{$this->product_name}</a> is now installed and ready to use! Below are some 
        		 	            links you may find useful.<br /><br /><br />
        		 	            <h3>Useful Links</h3>
        		 	            <ul id='links'>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://grinderz.org/v'>Verify Release</a></li>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://grinderz.org'>Blog for Scripts Profs</a></li>
        		 	                <li><img src='images/link.gif' align='absmiddle' /> <a href='http://google.com'>Google</a></li>
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