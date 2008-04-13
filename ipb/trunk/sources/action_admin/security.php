<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2006-06-08 17:11:50 +0100 (Thu, 08 Jun 2006) $
|   > $Revision: 289 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Admin Logs Stuff
|   > Module written by Matt Mecham
|   > Date started: 11nd September 2002
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Mon 24th May 2004
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_security
{

	var $base_url;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "admin";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "security";
	
	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, 'IPB 安全中心' );
		
		//-----------------------------------------
		// LOAD HTML
		//-----------------------------------------
		
		$this->html = $this->ipsclass->acp_load_template('cp_skin_security');
		
		switch($this->ipsclass->input['code'])
		{
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->security_overview();
				break;
			case 'stronghold':
				$this->do_stronghold();
				break;
			case 'dynamic_images':
				$this->do_dynamic_images();
				break;
			case 'acplink':
				$this->do_acplink();
				break;
			case 'virus_check':
				$this->anti_virus_check();
				break;
			case 'deep_scan':
				$this->deep_scan();
				break;
			case 'list_admins':
				$this->list_admins();
				break;
			case 'htaccess':
				$this->do_htaccess();
				break;
			case 'confglobal':
				$this->do_confglobal();
				break;
			case 'acprename':
				$this->do_acprename();
				break;
				
			case 'acphtaccess':
				$this->acphtaccess_form();
				break;
			case 'acphtaccess_do':
				$this->acphtaccess_do();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// ACP HTACCESS: Step two
	/*-------------------------------------------------------------------------*/
	
	function acphtaccess_do()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$name = trim( $_POST['name'] );
		$pass = trim( $_POST['pass'] );
		
		$htaccess_pw   = "";
		$htaccess_auth = "";
		
		//-----------------------------------------
		// Check...
		//-----------------------------------------
		
		if ( ! $name or ! $pass )
		{
			$this->ipsclass->main_msg = "您必须完整填写表单";
			$this->acphtaccess_form();
			return;
		}
		
		//-----------------------------------------
		// Format files...
		//-----------------------------------------
		
		$htaccess_auth = "AuthType Basic\n"
					   . "AuthName \"IPB ACP\"\n"
					   . "AuthUserFile " . ROOT_PATH . IPB_ACP_DIRECTORY . "/.htpasswd\n"
				       . "Require valid-user\n";
				
		$htaccess_pw   = $name . ":" . crypt( $pass, base64_encode( $pass ) );
		
		if ( $FH = @fopen( ROOT_PATH . IPB_ACP_DIRECTORY . '/' . '.htpasswd', 'w' ) )
		{
			fwrite( $FH, $htaccess_pw );
			fclose( $FH );
			
			$FF = @fopen( ROOT_PATH . IPB_ACP_DIRECTORY . '/' . '.htaccess', 'w' );
			fwrite( $FF, $htaccess_auth );
			fclose( $FF );
			
			$this->ipsclass->main_msg = "验证文件已写入";
			$this->security_overview();
		}
		else
		{
			$this->ipsclass->html .= $this->html->htaccess_data( $htaccess_pw, $htaccess_auth );

			$this->ipsclass->admin->nav[] = array( '', 'ACP  .htaccess 文件' );

			$this->ipsclass->admin->output();
		}
		
		
	}
	
	/*-------------------------------------------------------------------------*/
	// ACP HTACCESS: Step One
	/*-------------------------------------------------------------------------*/
	
	function acphtaccess_form()
	{
		//-----------------------------------------
		// Show it
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->html->htaccess_form();
		
		$this->ipsclass->admin->nav[] = array( '', 'ACP .htaccess 文件' );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Rename ACP directory
	/*-------------------------------------------------------------------------*/
	
	function do_acprename()
	{
		//-----------------------------------------
		// Show it
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->html->rename_admin_dir();
		
		$this->ipsclass->admin->nav[] = array( '', '重命名 admin 目录' );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Change conf global
	/*-------------------------------------------------------------------------*/
	
	function do_confglobal()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$done = 0;
		
		//-----------------------------------------
		// 	Try...
		//-----------------------------------------
		
		if ( @chmod( ROOT_PATH . 'conf_global.php', 0444) )
		{
			$done = 1;
		}
		
		//-----------------------------------------
		// Wow, that was really hard. I deserve a
		// payraise after this function...
		//-----------------------------------------
		
		if ( $done )
		{
			$this->ipsclass->main_msg = "CHMOD  修改完毕.";
		}
		else
		{
			$this->ipsclass->main_msg = "<strong>无法完成处理.</strong><br />请您使用 FTP 客户端软件来修改 'conf_global.php'的 CHMOD 值到 0444.";
		}
		
		$this->security_overview();
	}
	
	/*-------------------------------------------------------------------------*/
	// Add htaccess to non IPB dirs
	/*-------------------------------------------------------------------------*/
	
	function do_htaccess()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$name = '.htaccess';
		$msg  = array();
		$dirs = array( ROOT_PATH . 'cache',
					   ROOT_PATH . 'skin_acp',
					   ROOT_PATH . 'style_avatars',
					   ROOT_PATH . 'style_emoticons',
					   ROOT_PATH . 'style_images',
					   ROOT_PATH . 'style_captcha',
					   ROOT_PATH . 'uploads' );

		$towrite = <<<EOF

#<ipb-protection>
<Files ~ "^.*\.(php|cgi|pl|php3|php4|php5|php6|phtml|shtml)">
    Order allow,deny
    Deny from all
</Files>
#</ipb-protection>
EOF;

		//-----------------------------------------
		// Do it!
		//-----------------------------------------
	
		foreach( $dirs as $directory )
		{
			if ( $FH = @fopen( $directory . '/'. $name, 'a+' ) )
			{
				fwrite( $FH, $towrite );
				fclose( $FH );
			
				$msg[] = "写入 .htaccess 到 $directory...";
			}
			else
			{
				$msg[] = "跳过 $directory, 无法写入...";
			}
		}
		
		//-----------------------------------------
		// Done...
		//-----------------------------------------
		
		$this->ipsclass->main_msg = implode( "<br />", $msg );
		$this->security_overview();
	}
	
	/*-------------------------------------------------------------------------*/
	// List admins
	/*-------------------------------------------------------------------------*/
	
	function list_admins()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$content = "";
		$groups  = array();
		$query   = "";
		$members = array();
		
		//-----------------------------------------
		// Get all admin groups...
		//-----------------------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' => '*',
											     'from'   => 'groups',
											  	 'where'  => 'g_access_cp > 0 AND g_access_cp IS NOT NULL' ) );
		
		$o = $this->ipsclass->DB->exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row( $o ) )
		{
			$_gid = intval( $row['g_id'] );
			
			# I hate looped queries, but this should be OK.
			
			$this->ipsclass->DB->build_query( array( 'select' => '*',
												     'from'   => 'members',
												  	 'where'  => "mgroup=" . $_gid ." OR mgroup_others LIKE '%,". $_gid .",%' OR mgroup_others='".$_gid."' OR mgroup_others LIKE '".$_gid.",%' OR mgroup_others LIKE '%,".$_gid."'",
												     'order'  => 'joined DESC' ) );

			$b = $this->ipsclass->DB->exec_query();
			
			while( $member = $this->ipsclass->DB->fetch_row( $b ) )
			{
				if ( ! $member['mgroup'] AND ! $member['mgroup_others'] )
				{
					continue;
				}
				
				$members[ $member['id'] ] = $member;
			}
			
			$groups[ $row['g_id'] ] = $row;
		}
		
		//-----------------------------------------
		// Generate list
		//-----------------------------------------
		
		foreach( $members as $id => $member )
		{
			$member['members_display_name'] = $member['members_display_name'] ? $member['members_display_name'] : $member['name'];
			$member['_mgroup']				= $this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_title'];
			$_tmp                           = array();
			$member['_joined']              = $this->ipsclass->get_date( $member['joined'], 'JOINED' );
			
			foreach( explode( ",", $member['mgroup_others'] ) as $gid )
			{
				if ( $gid )
				{
					$_tmp[] = $this->ipsclass->cache['group_cache'][ $gid ]['g_title'];
				}
			}
			
			$member['_mgroup_others'] = implode( ", ", $_tmp );
			
			$content .= $this->html->list_admin_row( $member );
		}
		
		//$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( "Members with ACP Access", "Below is a list of all members with access to your ACP.<br />If you do not recognize any, please remove their ACP access immediately." ) ."<br />";
		$this->ipsclass->html .= $this->html->list_admin_overview( $content );
		
		$this->ipsclass->admin->nav[] = array( '', '管理员列表' );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Deep scan
	/*-------------------------------------------------------------------------*/
	
	function deep_scan()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$filter          = trim( $this->ipsclass->input['filter'] );
		$file_count      = 0;
		$bad_count       = 0;
		$content         = "";
		$checked_content = "";
		$colors          = array( 0  => '#84ff00',
								  1  => '#84ff00',
								  2  => '#b5ff00',
								  3  => '#b5ff00',
								  4  => '#ffff00',
								  5  => '#ffff00',
								  6  => '#ffde00',
								  7  => '#ffde00',
								  8  => '#ff8400',
								  9  => '#ff8400',
								  10 => '#ff0000' );
							 
		//-----------------------------------------
		// Get class
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/classes/class_virus_checker.php' );
		$class_virus_checker           = new class_virus_checker();
		$class_virus_checker->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------
		// Run it...
		//-----------------------------------------
		
		$class_virus_checker->anti_virus_deep_scan( ROOT_PATH, '(php|cgi|pl|perl|php3|php4|php5|php6)' );
		
		//-----------------------------------------
		// Update...
		//-----------------------------------------
		
		$cache_array =& $this->ipsclass->cache['systemvars'];
		
		$cache_array['last_deepscan_check'] = time();
		
		$this->ipsclass->update_cache( array( 'name'  => 'systemvars',
											  'value' => $cache_array,
											  'array' => 1,
											  'donow' => 1 ) );
											
		//-----------------------------------------
		// Got any bad files?
		//-----------------------------------------
		
		if ( is_array( $class_virus_checker->bad_files ) and count( $class_virus_checker->bad_files ) )
		{
			foreach( $class_virus_checker->bad_files as $idx => $data )
			{
				$file_count++;
				
				$_data = array();
				$_info = stat( $data['file_path'] );
				
				$_data['size']        = filesize( $data['file_path'] );
				$_data['human']       = ceil( $_data['size'] / 1024 );
				$_data['mtime']       = $this->ipsclass->get_date( $_info['mtime'], 'SHORT' );
				$_data['score']       = $data['score'];
				$_data['left_width']  = $data['score'] * 5;
				$_data['right_width'] = 50 - $_data['left_width'];
				$_data['color']       = $colors[ $data['score'] ];
				
				if ( $data['score'] >= 7 )
				{
					$bad_score++;
				}
				
				if ( strstr( $filter, 'score' ) )
				{
					$_filter = intval( str_replace( 'score-', '', $filter ) );
					
					if ( $data['score'] < $_filter )
					{
						continue;
					}
				}
				else if ( $filter == 'large' )
				{
					if ( $_data['human'] < 55 )
					{
						continue;
					}
				}
				else if ( $filter == 'recent' )
				{
					if ( $_info['mtime'] < time() - 86400 * 30 )
					{
						continue;
					} 
				}
				else if ( $filter == 'all' )
				{
					
				}
				else
				{
					$filter = "";
				}
				
				if ( strtoupper( substr(PHP_OS, 0, 3) ) == 'WIN' )
				{
					$file_path = str_replace( ROOT_PATH, "",  $data['file_path'] );
					$file_path = str_replace( "\\", "/", $file_path );
					
					$data['file_path'] = str_replace( "/\\", "\\", $data['file_path'] );
				}				
				else
				{
					$file_path         = str_replace( ROOT_PATH.'/', '', $data['file_path'] );
					$data['file_path'] = str_replace( ROOT_PATH.'/', '', $data['file_path'] );
				}
				
				$content .= $this->html->deep_scan_bad_files_row( $file_path, $data['file_path'], $_data );
			}
			
			if ( $bad_score )
			{
				$this->ipsclass->html .= $this->ipsclass->skin_acp_global->warning_box( '所有可执行文件', '深度扫描发现了下列文件.<br /><strong>'.$bad_score.'</strong> 中有'.$file_count.' 个文件的得分高于 7/10 分.<br />如果您不确定它们的来源, 请立即检查.' ) . "<br />";
			}
			else
			{
				$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( '所有可执行文件', '深度扫描找到了 '.$file_count.' files.<br />如果您不确定它们的来源, 请立即检查.' ) . "<br />";
			}
			
			$this->ipsclass->html .= $this->html->deep_scan_bad_files_wrapper( $content );
		}
		
		//-----------------------------------------
		// Fix filter...
		//-----------------------------------------
		
		if ( $filter )
		{
			$this->ipsclass->html = preg_replace( "#(value=[\"']".preg_quote( $filter, '#' )."['\"])#i", "\\1 selected='selected'", $this->ipsclass->html );
		}
		
		$this->ipsclass->admin->nav[] = array( '', '深度扫描' );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Anti virus checker
	/*-------------------------------------------------------------------------*/
	
	function anti_virus_check()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$content         = "";
		$checked_content = "";
		$colors          = array( 0  => '#84ff00',
								  1  => '#84ff00',
								  2  => '#b5ff00',
								  3  => '#b5ff00',
								  4  => '#ffff00',
								  5  => '#ffff00',
								  6  => '#ffde00',
								  7  => '#ffde00',
								  8  => '#ff8400',
								  9  => '#ff8400',
								  10 => '#ff0000' );
							 
		//-----------------------------------------
		// Get class
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/classes/class_virus_checker.php' );
		$class_virus_checker           = new class_virus_checker();
		$class_virus_checker->ipsclass =& $this->ipsclass;
		
		//-----------------------------------------
		// Run it...
		//-----------------------------------------
		
		$class_virus_checker->run_scan();
		
		//-----------------------------------------
		// Update...
		//-----------------------------------------
		
		$cache_array =& $this->ipsclass->cache['systemvars'];
		
		$cache_array['last_virus_check'] = time();
		
		$this->ipsclass->update_cache( array( 'name'  => 'systemvars',
											  'value' => $cache_array,
											  'array' => 1,
											  'donow' => 1 ) );
											
		//-----------------------------------------
		// Got any bad files?
		//-----------------------------------------
		
		if ( is_array( $class_virus_checker->bad_files ) and count( $class_virus_checker->bad_files ) )
		{
			foreach( $class_virus_checker->bad_files as $idx => $data )
			{
				$_data = array();
				$_info = stat( $data['file_path'] );
				
				$_data['size']        = filesize( $data['file_path'] );
				$_data['human']       = ceil( $_data['size'] / 1024 );
				$_data['mtime']       = $this->ipsclass->get_date( $_info['mtime'], 'SHORT' );
				$_data['score']       = $data['score'];
				$_data['left_width']  = $data['score'] * 5;
				$_data['right_width'] = 50 - $_data['left_width'];
				$_data['color']       = $colors[ $data['score'] ];
				
				if ( strtoupper( substr(PHP_OS, 0, 3) ) == 'WIN' )
				{
					$root_path = str_replace( "/", "\\", ROOT_PATH );
					$file_path = str_replace( $root_path, "",  $data['file_path'] );
					$file_path = str_replace( "\\", "/", $file_path );
				}				
				else
				{
					$file_path = str_replace( ROOT_PATH, '', $data['file_path'] );
				}
				
				$content .= $this->html->anti_virus_bad_files_row( $file_path, $data['file_path'], $_data );
			}
			
			$this->ipsclass->html .= $this->ipsclass->skin_acp_global->warning_box( '可疑文件探测', '系统找到了下列可疑文件.<br />如果您不确定他们的来源, 请立即删除它们.' ) . "<br />";
			
			$this->ipsclass->html .= $this->html->anti_virus_bad_files_wrapper( $content );
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->skin_acp_global->information_box( '可以文件探测', '系统没有找到可疑文件.<br />请定期做扫描以保证您的系统安全' ) . "<br />";
		}
		
		//-----------------------------------------
		// Show checked folders...
		//-----------------------------------------
		
		if ( is_array( $class_virus_checker->checked_folders ) and count( $class_virus_checker->checked_folders ) )
		{
			foreach( $class_virus_checker->checked_folders as $name )
			{
				$checked_content .= $this->html->anti_virus_checked_row( str_replace( ROOT_PATH, '', $name ) );
			}
			
			$this->ipsclass->html .= $this->html->anti_virus_checked_wrapper( $checked_content );
		}
		
		$this->ipsclass->admin->nav[] = array( '', '可疑文件检查' );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
    // ACP LINK
    /*-------------------------------------------------------------------------*/
	
	function do_acplink()
	{
		//-----------------------------------------
		// Update the setting...
		//-----------------------------------------
		
		$this->update_setting( 'security_remove_acp_link', $this->ipsclass->vars['security_remove_acp_link'] ? 0 : 1 );
		
		//-----------------------------------------
		// Done..
		//-----------------------------------------
		
		$lang = $this->ipsclass->vars['security_remove_acp_link'] == 0 ? 'restored' : 'removed';
		
		$this->ipsclass->main_msg = "ACP link display {$lang}";
		$this->security_overview();
	}
	
	/*-------------------------------------------------------------------------*/
    // DYNAMIC IMAGES
    /*-------------------------------------------------------------------------*/
	
	function do_dynamic_images()
	{
		//-----------------------------------------
		// Update the setting...
		//-----------------------------------------
		
		$this->update_setting( 'allow_dynamic_img', $this->ipsclass->vars['allow_dynamic_img'] ? 0 : 1 );
		
		//-----------------------------------------
		// Done..
		//-----------------------------------------
		
		$lang = $this->ipsclass->vars['allow_dynamic_img'] == 0 ? 'disabled' : 'enabled';
		
		$this->ipsclass->main_msg = "Dynamic images {$lang}";
		$this->security_overview();
	}
	
	/*-------------------------------------------------------------------------*/
    // STRONG HOLD COOKIE
    /*-------------------------------------------------------------------------*/
	
	function do_stronghold()
	{
		//-----------------------------------------
		// Update the setting...
		//-----------------------------------------
		
		$this->update_setting( 'cookie_stronghold', $this->ipsclass->vars['cookie_stronghold'] ? 0 : 1 );
		
		//-----------------------------------------
		// Done..
		//-----------------------------------------
		
		$lang = $this->ipsclass->vars['cookie_stronghold'] == 0 ? 'disabled' : 'enabled';
		
		$this->ipsclass->main_msg = "Cookie 集中 {$lang}";
		$this->security_overview();
	}
	
	/*-------------------------------------------------------------------------*/
    // Update setting
    /*-------------------------------------------------------------------------*/
	
	function update_setting( $key, $value )
	{
		//-----------------------------------------
		// Check
		//-----------------------------------------
		
		if ( ! $key )
		{
			return FALSE;
		}
		
		//-----------------------------------------
		// Update DB
		//-----------------------------------------
		
		$this->ipsclass->DB->do_update( 'conf_settings', array( 'conf_value' => $value ), "conf_key='".$key."'" );
		
		//-----------------------------------------
		// Rebuild settings cache
		//-----------------------------------------
		
		require_once( ROOT_PATH . 'sources/action_admin/settings.php' );
		$settings           =  new ad_settings();
		$settings->ipsclass =& $this->ipsclass;
		
		$settings->setting_rebuildcache();
		
		//-----------------------------------------
		// Done...
		//-----------------------------------------
		
		return TRUE;
	}
	
	
	/*-------------------------------------------------------------------------*/
    // View current log in logs
    /*-------------------------------------------------------------------------*/
	
	function security_overview()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$content     = array( 'bad' => '', 'good' => '', 'ok' => '' );
		$cache_array =& $this->ipsclass->cache['systemvars'];
		
		//-----------------------------------------
		// Virus checker link
		//-----------------------------------------
		
		if ( intval($cache_array['last_virus_check']) < time() - 7 * 86400 )
		{
			$content['bad'] .= $this->html->security_item_bad(  'IPB 可疑文件检查',
			 													'本工具可以在 IPB 的安装目录检查可疑文件.<br />已经有一周以上没有运行本工具',
																'运行工具',
																$this->ipsclass->form_code_js.'&code=virus_check',
																'vchecker' );
														
		}
		else
		{
			$last_run 		  = $this->ipsclass->get_date( $cache_array['last_virus_check'], 'SHORT' );
			$content['good'] .= $this->html->security_item_good( 'IPB 可疑文件检查',
			 													 '本工具可以在 IPB 的安装目录检查可疑文件.<br />上次运行本工具: '.$last_run,
																 '运行工具',
																 $this->ipsclass->form_code_js.'&code=virus_check',
																 'vchecker' );
		}
		
		//-----------------------------------------
		// Deep scan link
		//-----------------------------------------
		
		if ( intval($cache_array['last_deepscan_check']) < time() - 30 * 86400 )
		{
			$content['bad'] .= $this->html->security_item_bad(  'IPB 可执行文件深度扫描',
			 													'本工具能找出并分析您的 IPB 安装目录中的可执行文件.<br />已经有一月以上没有运行本工具',
																'运行工具',
																$this->ipsclass->form_code_js.'&code=deep_scan',
																'deepscan' );
														
		}
		else
		{
			$last_run 		  = $this->ipsclass->get_date( $cache_array['last_deepscan_check'], 'SHORT' );
			$content['good'] .= $this->html->security_item_good(  'IPB 可执行文件深度扫描',
			 													  '本工具能找出并分析您的 IPB 安装目录中的可执行文件.<br />上次运行本工具: '.$last_run,
																  '运行工具',
																   $this->ipsclass->form_code_js.'&code=deep_scan',
																  'deepscan' );
		}
									  
		//-----------------------------------------
		// Get .htaccess settings
		//-----------------------------------------
		
		if ( strtoupper( substr(PHP_OS, 0, 3) ) !== 'WIN' )
		{
			$_extra = '';
			
			if ( ! is_writeable( ROOT_PATH . IPB_ACP_DIRECTORY ) )
			{
				$_extra = "<div style='color:red;font-weight:bold'>IPB 无法写入 .htaccess 文件到您的“admin”目录. 请先使用 FTP 客户端软件来修改此目录的 CHMOD 到 0777.</div>";
			}
			
			if ( ! file_exists( ROOT_PATH . IPB_ACP_DIRECTORY . '/.htaccess' ) )
			{
				$content['ok'] .= $this->html->security_item_ok(    'IPB ACP .htaccess 保护',
				 													'为使您的ACP更安全, 您可以为您的 "admin" 目录添加 HTTP 验证.<br />IPB 无法定位后台 .htaccess 文件.'. $_extra,
																	'更多信息',
																	$this->ipsclass->form_code_js.'&code=acphtaccess',
																	'acphtaccess' );
			}
			else
			{
				$content['good'] .= $this->html->security_item_good( 'IPB ACP .htaccess 保护',
				 											 		 '为使您的ACP更安全, 您可以为您的“admin”目录添加 HTTP 验证. <br />目前此目录没有 .htaccess 文件.'.$_extra,
																	 '更多信息',
																	 $this->ipsclass->form_code_js.'&code=acphtaccess',
																	 'acphtaccess' );
			}
			
			# Other htaccess protection
			if ( ! file_exists( ROOT_PATH . 'style_emoticons/.htaccess' ) )
			{
				$content['ok'] .= $this->html->security_item_ok( 'IPB PHP/CGI .htaccess 保护',
				 												 'IPB 将会写入 .htaccess 到没有 PHP 文件的文件夹中这样可以阻止非法的 PHP 和 CGI 文件运行.<br />IPB 无法定位 .htaccess 文件.',
																 '运行工具',
																 $this->ipsclass->form_code_js.'&code=htaccess',
																 'htaccess' );
			}
			else
			{
				$content['good'] .= $this->html->security_item_good( 'IPB .htaccess 保护',
				 											 		 'IPB 将会写入 .htaccess 到没有 PHP 文件的文件夹中这样可以阻止非法的 PHP 和 CGI 文件运行.<br />IPB 已经定位一些 .htaccess 文件.',
																	 '运行工具',
																	 $this->ipsclass->form_code_js.'&code=htaccess',
																	 'htaccess' );
			}
			
			//-----------------------------------------
			// Conf global
			//-----------------------------------------
			
			if ( is_writeable( ROOT_PATH . 'conf_global.php' ) )
			{
				$content['bad'] .= $this->html->security_item_bad( '使 "conf_global" 只读',
				 												   '论坛安装后, 您需要将“conf_global.php”文件的 CHMOD 为只读.<br />“conf_global.php”文件目前可写.',
																   '运行工具',
																   $this->ipsclass->form_code_js.'&code=confglobal',
																   'confglobal' );
															
			}
			else
			{
				$content['good'] .= $this->html->security_item_good(  'Make "conf_global" un-writeable',
				 												 	  '论坛安装后, 您需要将“conf_global.php”文件的 CHMOD 为只读. <br />“conf_global.php”文件目前不可写."',
																	   '更多信息',
																	   $this->ipsclass->form_code_js.'&code=confglobal',
																	   'confglobal' );
			}
		}
		
		//-----------------------------------------
		// Dynamic images
		//-----------------------------------------
		
		if ( ! $this->ipsclass->vars['allow_dynamic_img'] )
		{
			$content['good'] .= $this->html->security_item_good( '禁止动态图片',
			 												  	 'IPB 本工具可以禁止在论坛张贴动态图片, 动态图片可以运行隐藏的 JavaScript.<br />动态图片已经禁止.',
																 '立即执行',
																 $this->ipsclass->form_code_js.'&code=dynamic_images',
																 'dynamic_images' );
														
		}
		else
		{
			$content['bad'] .= $this->html->security_item_bad( '禁止动态图片',
			 											       'IPB 本工具可以禁止在论坛张贴动态图, 动态图片可以运行隐藏的 JavaScript.<br />动态图片<b>未禁止</b>.',
														       '立即执行',
														        $this->ipsclass->form_code_js.'&code=dynamic_images',
														       'dynamic_images' );
		}
		
		//-----------------------------------------
		// Strong hold cookie 
		//-----------------------------------------
		
		if ( ! $this->ipsclass->vars['cookie_stronghold'] )
		{
			$content['bad'] .= $this->html->security_item_bad(  '开启加密 Cookie 模式',
			 													'IPB 将会在会员的浏览器中加密保存 Cookie 文件以防止可能的盗用.<br />加密 Cookies 模式关闭.',
																'立即执行',
																$this->ipsclass->form_code_js.'&code=stronghold',
																'stronghold' );
														
		}
		else
		{
			$content['good'] .= $this->html->security_item_good( '开启加密 Cookie 模式',
			 													 'IPB 将会在会员的浏览器中加密保存 Cookie 文件以防止可能的盗用.<br />加密 Cookies 模式开启.',
																 '立即执行',
																 $this->ipsclass->form_code_js.'&code=stronghold',
																 'stronghold' );
		}
		
		//-----------------------------------------
		// Remove ACP link
		//-----------------------------------------
		
		if ( ! $this->ipsclass->vars['security_remove_acp_link'] )
		{
			$content['ok'] .= $this->html->security_item_ok( '移除管理面板链接',
			 												 'IPB 将会移除论坛头部 "管理面板" 的链接. 通常可以在您执行了更改 \'admin\' 名称后使用.<br />当前管理面板链接可见.',
															 '立即执行',
															 $this->ipsclass->form_code_js.'&code=acplink',
															 'acplink' );
														
		}
		else
		{
			$content['good'] .= $this->html->security_item_good( 'Remove ACP Link',
			 											 		'IPB 将会移除论坛头部 "管理面板" 的链接. 通常可以在您执行了更改 \'admin\' 名称后使用.<br />当前管理面板链接已移除.',
																 '立即执行',
																 $this->ipsclass->form_code_js.'&code=acplink',
																 'acplink' );
		}
		
		//-----------------------------------------
		// ACP directory renamed
		//-----------------------------------------
		
		if ( IPB_ACP_DIRECTORY == 'admin' )
		{
			$content['ok'] .= $this->html->security_item_ok( '重命名 \'admin\' 文件夹',
			 												 '默认 \'admin\' 可以在这里重命名以保护不被攻击.<br />当前后台文件夹没有重命名.',
															 '了解更多',
															 $this->ipsclass->form_code_js.'&code=acprename',
															 'acprename' );
														
		}
		else
		{
			$content['good'] .= $this->html->security_item_good( '重命名 \'admin\' 文件夹',
			 													 '默认 \'admin\' 可以在这里重命名以保护不被攻击.<br />当前后台文件夹已经重命名.',
																 '了解更多',
																 $this->ipsclass->form_code_js.'&code=acprename',
																 'acprename' );
		}
		
		
		//-----------------------------------------
		// Print...
		//-----------------------------------------
		
			
		$this->ipsclass->html .= $this->html->security_overview( $content );
		
		$this->ipsclass->admin->output();
	}
}


?>