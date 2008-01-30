<?php
/**
 * Invision Power Board
 * Invision Installer Framework
 */

@set_time_limit(0);

//--------------------------------
// Setup
//--------------------------------

require( "./core/conf.php" );

//--------------------------------
// Define Constants
//--------------------------------

$INFO = array();

//--------------------------------
// Load our classes
//--------------------------------

require INS_ROOT_PATH   . 'core/ipsclass.php';
require INS_KERNEL_PATH . 'class_converge.php';
require INS_ROOT_PATH   . 'core/template.php';
require_once INS_KERNEL_PATH . 'class_xml.php';
require INS_ROOT_PATH   . 'core/class_tar.php';

# Initiate super-class
$ipsclass           = new ipsclass();

//--------------------------------
//  Set up our vars
//--------------------------------

$ipsclass->parse_incoming();

$ipsclass->initiate_ipsclass();	

//--------------------------------
// Setup Main Installer Class
//--------------------------------

require( INS_ROOT_PATH . 'core/class_installer.php' );
require( INS_ROOT_PATH . 'custom/app.php' );

$install = new application_installer();
$install->ipsclass =& $ipsclass;
$install->read_config();

$install->template = new install_template( $ipsclass );
$install->template->product_name         = $install->product_name;
$install->template->product_version      = $install->product_version;
$install->template->product_long_version = $install->product_long_version;

//--------------------------------
//  Saved Data
//--------------------------------
$install->saved_data = unserialize( urldecode( stripslashes( $_POST['saved_data'] ) ) );

//--------------------------------
// Test lock
//--------------------------------

if ( $ipsclass->input['p'] != 'done' )
{
	$got_lock = $install->check_lock();
	
	if ( $got_lock )
	{
		$install->template->hide_next = 1;
		$install->template->warning( array( "该安装系统已经锁定. 如果您想要重新安装, 请通过 FTP 删除 'installfiles/lock.php' 文件然后重新刷新本页." ) );
		$install->template->output( $install->product_name, $install->product_version );
		
		exit();
	}
}

//--------------------------------	
// Make sure we've renamed conf_global.php
//--------------------------------	

if ( file_exists( INS_DOC_ROOT_PATH.'conf_global.php.dist') AND ! file_exists( INS_DOC_ROOT_PATH.'conf_global.php') )
{
	if ( ! @rename( INS_DOC_ROOT_PATH.'conf_global.php.dist', INS_DOC_ROOT_PATH.'conf_global.php' ) )
	{
		$install->template->hide_next = 1;
		$install->template->warning( array( "<strong>在进行下一步之前您必须重命名文件 'conf_global.<b style='color:red'>php.dist</b>' 为 'conf_global.<b style='color:red'>php</b>'.</strong>
					  						 这一文件可以通过 FTP 访问在论坛的根目录下找到, 和 'admin.php' 与 'index.php' 在同一个文件夹." ) );
		$install->template->output( $install->product_name, $install->product_version );
		exit();
	}
}

// -------------------------------
// Run Install Step
// -------------------------------

$install->pre_process();
$install->process();
$install->post_process();

// -------------------------------
// Output
// -------------------------------
$install->template->saved_data = serialize( $install->saved_data );
$install->template->output( $install->product_name, $install->product_version );

?>