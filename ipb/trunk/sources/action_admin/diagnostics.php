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
|   > $Date: 2006-03-23 07:34:25 -0500 (Thu, 23 Mar 2006) $
|   > $Revision: 177 $
|   > $Author: brandon $
+---------------------------------------------------------------------------
|
|   > Diagnostics Center
|   > Module written by Brandon Farber
|   > Date started: 19th April 2006
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_diagnostics
{
	var $base_url;
	var $dir_split = "/";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "help";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "diag";
	
	function auto_run()
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html = $this->ipsclass->acp_load_template( 'cp_skin_diagnostics' );
		
		//-----------------------------------------
		// Set default nav
		//-----------------------------------------
		
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '诊断' );
		
		if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' )
		{
			$this->dir_split = "\\";
		}
		
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'dbindex':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->db_index_check();
				break;
				
			case 'dbchecker':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->db_check();
				break;			
				
			case 'whitespace':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->whitespace_check();
				break;
				
			case 'filepermissions':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->permissions_check();
				break;
				
			case 'fileversions':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->version_check();
				break;
				
			//-----------------------------------------
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':do' );
				$this->list_functions();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Version Check
	/*-------------------------------------------------------------------------*/
	
	function version_check()
	{
		$this->ipsclass->admin->page_detail = "运行不同版本的文件将造成不可预料的后果. 例如, 2.1 版本的文件和 2.0 版本的文件互不不兼容. 如果有任何文件在下面以红色列出, 建议您上传一个最新的版本.";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		$this->ipsclass->admin->nav[] = array( '', '版本检查结果' );
		set_time_limit(0);
		
		$dir 	= preg_replace( "#^(.+?)\/$#", "\\1", ROOT_PATH );
		
		$file_versions   = array();
		$upgrade_history = array();
		$latest_version  = array( 'upgrade_version_id' => '' );
		$file_versions   = $this->version_recur_dir( $dir );
		
		$this->ipsclass->adskin->td_header[] = array( ""  , "30%" );
		$this->ipsclass->adskin->td_header[] = array( ""  , "70%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "版本信息" );
		
   		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'upgrade_history', 'order' => 'upgrade_version_id DESC', 'limit' => array(0, 5) ) );
   		$this->ipsclass->DB->simple_exec();
   		
   		while( $r = $this->ipsclass->DB->fetch_row() )
   		{
   			if ( $r['upgrade_version_id'] > $latest_version['upgrade_version_id'] )
   			{
   				$latest_version = $r;
   			}
   			
   			$upgrade_history[] = $r;
   		}
   		
		//-----------------------------------------
		// Got real version number?
		//-----------------------------------------
		
		if ( $this->ipsclass->version == 'v<{%dyn.down.var.human.version%}>' )
		{
			$this->ipsclass->version = 'v'.$latest_version['upgrade_version_human'];
		}
		
		if ( $this->ipsclass->acpversion == '<{%dyn.down.var.long_version_id%}>' )
		{
			$this->ipsclass->acpversion = $latest_version['upgrade_version_id'];
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "IPB 版本", $this->ipsclass->version.' (ID: '.$this->ipsclass->acpversion.')' ) );
		
		if( !count($upgrade_history) )
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "升级历史", '<i>不可用</i>' ) );
		}
		else
		{
			foreach( $upgrade_history as $history_row )
			{
				$history_row['_date'] = $this->ipsclass->get_date( $history_row['upgrade_date'], 'SHORT' );
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "Upgrade {$history_row['_date']}", "{$history_row['upgrade_version_human']} ({$history_row['upgrade_version_id']})" ) );
			}
		}
				
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();	
		
		$this->ipsclass->adskin->td_header[] = array( "文件"  	, "70%" );
		$this->ipsclass->adskin->td_header[] = array( "版本" , "30%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "版本检查结果" );
		
		if( is_array($file_versions) && count($file_versions) )
		{		
			foreach( $file_versions as $file => $version )
			{
				if( 'v'.$version == $this->ipsclass->version )
				{
					$version = "<span class='rss-feed-valid'>{$version}</span>";
				}
				else
				{
					$version = "<span class='rss-feed-invalid'>{$version}</span>";
				}
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $file, $version ) );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( " 发生错误 - 无法读取文件" ) );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// Version check, recursive
	/*-------------------------------------------------------------------------*/
	
	function version_recur_dir($dir)
	{
		$skip_dirs = array( 'jscripts', 'cache', 'ssi_templates', 'style_avatars', 'style_emoticons', 'style_images', 'upgrade', 'uploads', 'images', 'i18n', 'PEAR', 'components_acp',
							 'components_init', 'components_location', 'components_ucp', 'components_public' );
		$skip_files = array( 'conf_global.php', 'conf.php', $this->ipsclass->vars['sql_driver'].'_fulltext.php', $this->ipsclass->vars['sql_driver'].'_tables.php',
								$this->ipsclass->vars['sql_driver'].'_inserts.php', $this->ipsclass->vars['sql_driver'].'_install.php' );
		
		$files	= array();
		$dh		= @opendir($dir);
			
		while (false !== ($file = readdir($dh)))
		{
	    	if ( preg_match( "#^[_\.]#", $file ) )
	    	{
		    	continue;
		    }
		    
			if ( $file != '.' && $file != '..' )
			{
				$newpath = $dir.$this->dir_split.$file;
				$level = explode( $this->dir_split, $newpath );
	
				if ( is_dir($newpath) && !in_array( $file, $skip_dirs ) && ($newpath.'../' != 'modules') )
				{
					$files = array_merge( $files, $this->version_recur_dir($newpath) );
				}
				else
				{
					if ( strpos( $file, ".php" ) !== false && !is_dir( $newpath ) && !in_array( $file, $skip_files ) )
					{
						$file = file_get_contents($newpath);

						preg_match( "#Invision Power Board v(.+?)\s+?#i", $file, $matches);
						
						$files[$newpath] = isset($matches[1]) ? $matches[1] : '';
					}
			  	}
			}
		}
		
		closedir($dh);
		return $files;
	}
	
	/*-------------------------------------------------------------------------*/
	// Permission Checks
	/*-------------------------------------------------------------------------*/
	
	function permissions_check()
	{
		$this->ipsclass->admin->page_detail = "一些文件和文件夹必须具有适当的访问许可, IPB 才能在您的磁盘上写入数据. 在 Windows 系统, 您必须确保它们具有“读取/写入”许可, 在 Unix/Linux 系统, 您必须确保文件为“CHMOD 777”.";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		$this->ipsclass->admin->nav[] = array( '', '访问许可检测结果' );
		
		$checkdirs = array( 'style_images', 'style_emoticons', 'cache', 'cache'.$this->dir_split.'skin_cache', 'cache'.$this->dir_split.'lang_cache', 'uploads' );
		$langfiles = array( 'lang_boards', 'lang_buddy', 'lang_calendar', 'lang_emails', 'lang_email_content', 'lang_error',
								'lang_forum', 'lang_global', 'lang_help', 'lang_legends', 'lang_login', 'lang_mlist',
							 	'lang_mod', 'lang_msg', 'lang_online', 'lang_portal', 'lang_post', 'lang_printpage',
							 	'lang_profile', 'lang_register', 'lang_search', 'lang_stats', 'lang_subscriptions',
							 	'lang_topic', 'lang_ucp', 'lang_chatpara' , 'lang_editors', 'lang_chatsigma',
							 	'acp_lang_acpperms', 'acp_lang_member', 'acp_lang_portal', 'lang_tar'
						   );

		$root_dir 	= preg_replace( "#^(.+?)\/$#", "\\1".$this->dir_split, ROOT_PATH );
	
		//-----------------------------------------		
		// Get language directories
		//-----------------------------------------
				
		$this->ipsclass->init_load_cache( array( 'languages' ) );
		
		if( is_array( $this->ipsclass->cache['languages'] ) && count( $this->ipsclass->cache['languages'] ) )
		{
			foreach( $this->ipsclass->cache['languages'] as $v )
			{
				$checkdirs[] = 'cache'.$this->dir_split.'lang_cache'.$this->dir_split.$v['ldir'];
				
				foreach( $langfiles as $filename )
				{
					$checkdirs[] = 'cache'.$this->dir_split.'lang_cache'.$this->dir_split.$v['ldir'].$this->dir_split.$filename.'.php';
				}
			}
		}
		else
		{
			$this->ipsclass->DB->build_query( array( 'select' => 'ldir', 'from' => 'languages' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $v = $this->ipsclass->DB->fetch_row() )
			{
				$checkdirs[] = 'cache'.$this->dir_split.'lang_cache'.$this->dir_split.$v['ldir'];
				
				foreach( $langfiles as $filename )
				{
					$checkdirs[] = 'cache'.$this->dir_split.'lang_cache'.$this->dir_split.$v['ldir'].$this->dir_split.$filename.'.php';
				}				
			}
		}
		
		//-----------------------------------------		
		// Get emoticon directories
		//-----------------------------------------
				
		if( is_array( $this->ipsclass->cache['emoticons'] ) && count( $this->ipsclass->cache['emoticons'] ) )
		{
			foreach( $this->ipsclass->cache['emoticons'] as $v )
			{
				$checkdirs[] = 'style_emoticons'.$this->dir_split.$v['emo_set'];
			}
		}
		else
		{
			$this->ipsclass->DB->build_query( array( 'select' => 'emo_set', 'from' => 'emoticons' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $v = $this->ipsclass->DB->fetch_row() )
			{
				$checkdirs[] = 'style_emoticons'.$this->dir_split.$v['emo_set'];
			}
		}
		
		//-----------------------------------------		
		// Get skin directories
		//-----------------------------------------
				
		$skin_dirs = array();
		
		if( is_array( $this->ipsclass->cache['skin_id_cache'] ) && count( $this->ipsclass->cache['skin_id_cache'] ) )
		{
			foreach( $this->ipsclass->cache['skin_id_cache'] as $k => $v )
			{
				if( $k == 1 && !IN_DEV )
				{
					continue;
				}
				
				$checkdirs[] = 'cache'.$this->dir_split.'skin_cache'.$this->dir_split.'cacheid_'.$v['set_skin_set_id'];
				$skin_dirs[] = $v['set_skin_set_id'];
			}
		}
		else
		{
			$this->ipsclass->DB->build_query( array( 'select' => 'set_skin_set_id', 'from' => 'skin_sets' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $v = $this->ipsclass->DB->fetch_row() )
			{
				$checkdirs[] = 'cache'.$this->dir_split.'skin_cache'.$this->dir_split.'cacheid_'.$v['set_skin_set_id '];
				$skin_dirs[] = $v['set_skin_set_id'];
			}
		}
		
		//-----------------------------------------		
		// Get skin files
		//-----------------------------------------
		
		$this->ipsclass->DB->load_cache_file( ROOT_PATH.'sources/sql/'.SQL_DRIVER.'_extra_queries.php', 'sql_extra_queries' );
		
		$this->ipsclass->DB->cache_add_query( 'diag_distinct_skins', array(), 'sql_extra_queries' );
		$this->ipsclass->DB->cache_exec_query();
		
		while( $v = $this->ipsclass->DB->fetch_row() )
		{
			foreach( $skin_dirs as $dir )
			{
				$checkdirs[] = 'cache'.$this->dir_split.'skin_cache'.$this->dir_split.'cacheid_'.$dir.$this->dir_split.$v['group_name'].'.php';
			}
		}
				
		set_time_limit(0);
		
		$checkdirs 	= array_unique($checkdirs);
		$output 	= array();
		
		foreach( $checkdirs as $dir_to_check )
		{
			if( !file_exists( $root_dir.$dir_to_check ) )
			{
				# Could be skin files from custom skins for components they don't own
				# or they could be using safe_mode skins
				# Make sure skin_cache still shows up though...
				
				if( !strpos( $dir_to_check, 'skin_' ) OR !strpos( $dir_to_check, '.php' ) )
				{
					$output[] = "<span class='rss-feed-invalid'>无法找到文件或文件夹 ".$root_dir.$dir_to_check."</span>";
				}
			}
			else if( !is_writeable( $root_dir.$dir_to_check ) )
			{
				$output[] = "<span class='rss-feed-invalid'>下列文件或文件夹不可写 ".$root_dir.$dir_to_check."</span>";
			}
			else if( is_writeable( $root_dir.$dir_to_check ) )
			{
				$output[] = "<span class='rss-feed-valid'>".$root_dir.$dir_to_check." 可写</span>";
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "访问许可检测结果" );
		
		if( is_array($output) && count($output) )
		{		
			foreach( $output as $html_row )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $html_row ) );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "oooops!!! 找不到任何要检测的文件或文件夹." ) );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	
	/*-------------------------------------------------------------------------*/
	// WHITE SPACE CHECK
	/*-------------------------------------------------------------------------*/
	
	function whitespace_check()
	{
		$this->ipsclass->admin->page_detail = "如果在 IPB 文件得 PHP 标记前后有空格, 将会导致白屏或页面畸形. 如果您发现这样的问题, 您必须打开这些文件, 在文件的开头或结尾删除这些额外的空格或回车换行符.";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		$this->ipsclass->admin->nav[] = array( '', '空白检测结果' );
		
		set_time_limit(0);
		
		$dir 	= preg_replace( "#^(.+?)\/$#", "\\1", ROOT_PATH );
		
		$files_with_junk = array();
		$files_with_junk = $this->recur_dir( $dir );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "空白检测结果" );
		
		if( is_array($files_with_junk) && count($files_with_junk) )
		{		
			foreach( $files_with_junk as $html_row )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $html_row." 在文件开头或结尾有空格" ) );
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( " 所有文件都没有问题" ) );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	
	function recur_dir($dir)
	{
		$skip_dirs = array( 'uploads', 'style_images', 'gallery_setup', 'blog_setup', 'style_emoticons', 'style_avatars',
							'jscripts', 'clientscripts', 'images', 'acp_js_skin' );

		$files	= array();
		$dh		= @opendir($dir);
			
		while (false !== ($file = readdir($dh)))
		{
	    	if ( preg_match( "#^[_\.]#", $file ) )
	    	{
		    	continue;
		    }
		    
			if ( $file != '.' && $file != '..' )
			{
				$newpath = $dir.$this->dir_split.$file;
				$level = explode( $this->dir_split, $newpath );
	
				if ( is_dir($newpath) && !in_array( $file, $skip_dirs ) )
				{
						$files = array_merge( $files, $this->recur_dir($newpath) );
				}
				else
				{
					if ( strpos( $file, ".php" ) !== false && !is_dir( $newpath ) )
					{
						$file = file_get_contents($newpath);

						$current_length = strlen($file);
						
						$file = trim($file);
						
						$actual_length  = strlen($file);
						
						if ( $current_length != $actual_length )
						{
							$files[] = $newpath;
						}
					}
			  	}
			}
		}
		
		closedir($dh);
		return $files;
	}
	
	/*-------------------------------------------------------------------------*/
	// CHECK DB INDEXES
	/*-------------------------------------------------------------------------*/
	
	function db_index_check()
	{
		$this->ipsclass->admin->page_detail = "数据库索引使您的数据库效率更高.  如果有索引丢失,  您的论坛运行速度将减慢.  所以, 请修复检测到得错误.";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		$this->ipsclass->admin->nav[] = array( '', '数据库索引核对结果' );
		
		//-----------------------------------------		
		// Fixing something?
		//-----------------------------------------
		
		$queries_to_run = array();
		
		foreach( $this->ipsclass->input as $k => $v )
		{
			if( preg_match( "/^query(\d+)$/", $k, $matches ) )
			{
				$queries_to_run[] = $v;
			}
		}
		
		if( isset($this->ipsclass->input['query']) AND $this->ipsclass->input['query'] )
		{
			$queries_to_run[] = $this->ipsclass->input['query'];
		}
				
		if( count($queries_to_run) > 0 )
		{
			foreach( $queries_to_run as $the_query )
			{
				$sql = trim( urldecode( base64_decode($the_query) ) );
				
				if ( preg_match( "/^(DROP|FLUSH)/i", trim($sql) ) )
				{
					$this->ipsclass->main_msg = "Sorry, those queries are not allowed for your safety";
					
					continue;
				}
				else if ( preg_match( "/^(?!SELECT)/i", preg_replace( "#\s{1,}#s", "", $sql ) ) and preg_match( "/admin_login_logs/i", preg_replace( "#\s{1,}#s", "", $sql ) ) )
				{
					$this->ipsclass->main_msg = "Sorry, those queries are not allowed for your safety.  Please use the 'Fix Manually' option for this query.";
					
					continue;			
				}
				else
				{
					$this->ipsclass->DB->return_die = 1;
				
					$this->ipsclass->DB->query($sql,1);
				
					if( $this->ipsclass->DB->error != "" )
					{
						$this->ipsclass->main_msg .= "<span style='color:red;'>SQL 错误</span><br />{$this->ipsclass->DB->error}<br />";
					}
					else
					{
						$this->ipsclass->main_msg .= "Query: ".htmlspecialchars($sql)."<br />实行成功<br />";
					}
					
					$this->ipsclass->DB->error  = "";
					$this->ipsclass->DB->failed = 0;
				}
			}
		}		
		
		//-----------------------------------------		
		// Get current table definitions
		//-----------------------------------------
		
		if( !file_exists( ROOT_PATH."/install/sql/{$this->ipsclass->vars['sql_driver']}_tables.php" ) )
		{
			$this->ipsclass->admin->error( "您必须上传 /install/sql/{$this->ipsclass->vars['sql_driver']}_tables.php 才能运行本工具" );
		}

		//require ROOT_PATH."sources/action_admin/sql_{$this->ipsclass->vars['sql_driver']}.php";
		require_once( KERNEL_PATH . 'db_lib/' . strtolower($this->ipsclass->vars['sql_driver']) . '_tools.php' );
		require_once( ROOT_PATH . 'install/sql/' . strtolower($this->ipsclass->vars['sql_driver']) . '_tables.php' );
		$db_tools = new db_tools( $this->ipsclass );
		
		$output = array();
		
		if ( !$output = $db_tools->db_index_diag( $TABLE ) )
		{
			$this->ipsclass->admin->error( "There was an error and we could not process your " . strtolower($this->ipsclass->vars['sql_driver']) . "_tables.php file." );
		}
		
		if( $output['error_count'] > 0 )
		{
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			
			$this->ipsclass->html .= $this->html->dbindexer_javascript();
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "警告: 发现错误" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( array( "<span class='rss-feed-invalid'>您的数据库有错误. 您可以在下面看到它们, 您也可以点击 <a href='#' onclick='fix_all_dberrors();'>这里</a> 来修复这些错误.", 2 ) ) );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		else
		{
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "没有发现错误" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( array( "<span class='rss-feed-valid'>您的数据库没有错误.", 2 ) ) );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}	
		
		$this->ipsclass->adskin->td_header[] = array( "表"    	, "20%" );
		$this->ipsclass->adskin->td_header[] = array( "状态"  , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "修复"       	, "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "数据表核对结果" );
		
		$i = 0;
		
		foreach( $output['results'] as $data )
		{
			if( $data['status'] == 'error' )
			{
				$popup_div = "<div style='border: 2px outset rgb(85, 85, 85); padding: 4px; background: rgb(238, 238, 238) none repeat scroll 0%; position: absolute; width: auto; display: none; text-align: center; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;' id='{$i}' align='center'>{$data['fixsql']}</div>";
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<span style='color:red'>{$data['table']}</span>",
																						"<span style='color:red'>{$data['index']}<br />{$data['message']}</span>",
																						"<center><script type='text/javascript'>all_queries[{$i}] = '".base64_encode($data['fixsql'])."';</script><a href='{$this->ipsclass->base_url}&amp;section=help&amp;act=diag&amp;code=dbindex&amp;query=".urlencode(base64_encode($data['fixsql']))."'><b>Fix Automatically</b></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href'#' onclick=\"toggleview('{$i}');return false;\" style='cursor: pointer;'><b>Fix Manually</b></a><br />{$popup_div}</center>"
																			   ) 	  );
				$i++;
			}
			else
			{
				$this->ipsclass->html .=  $this->ipsclass->adskin->add_td_row( array( "<span style='color:green'>{$data['table']}</span>",
																						"<span style='color:green'>{$data['index']}</span>",
																						"&nbsp;"
																			   ) 	  );
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
    }
    
    /*-------------------------------------------------------------------------*/
	// DB TABLES
	/*-------------------------------------------------------------------------*/
	
	function db_check()
	{
		$this->ipsclass->admin->page_detail = "If you are missing necessary database tables, or columns in those database tables, you will see database errors (or missing data) when browsing IPB.  Please use the links or queries provided below to remedy any missing columns or tables.";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		$this->ipsclass->admin->nav[] = array( '', '数据表核对结果' );
		
		//-----------------------------------------		
		// Fixing something?
		//-----------------------------------------
		
		$queries_to_run = array();
		
		foreach( $this->ipsclass->input as $k => $v )
		{
			if( preg_match( "/^query(\d+)$/", $k, $matches ) )
			{
				$queries_to_run[] = $v;
			}
		}
		
		if( isset($this->ipsclass->input['query']) AND $this->ipsclass->input['query'] )
		{
			$queries_to_run[] = $this->ipsclass->input['query'];
		}
				
		if( count($queries_to_run) > 0 )
		{
			foreach( $queries_to_run as $the_query )
			{
				$sql = trim( urldecode( base64_decode($the_query) ) );
				
				if ( preg_match( "/^(DROP|FLUSH)/i", trim($sql) ) )
				{
					$this->ipsclass->main_msg = "Sorry, those queries are not allowed for your safety";
					
					continue;
				}
				else if ( preg_match( "/^(?!SELECT)/i", preg_replace( "#\s{1,}#s", "", $sql ) ) and preg_match( "/admin_login_logs/i", preg_replace( "#\s{1,}#s", "", $sql ) ) )
				{
					$this->ipsclass->main_msg = "Sorry, those queries are not allowed for your safety.  Please use the 'Fix Manually' option for this query.";
					
					continue;			
				}
				else
				{
					$this->ipsclass->DB->return_die = 1;
				
					$this->ipsclass->DB->query($sql,1);
				
					if( $this->ipsclass->DB->error != "" )
					{
						$this->ipsclass->main_msg .= "<span style='color:red;'>SQL Error</span><br />{$this->ipsclass->DB->error}<br />";
					}
					else
					{
						$this->ipsclass->main_msg .= "Query: ".htmlspecialchars($sql)."<br />Executed Successfully<br />";
					}
					
					$this->ipsclass->DB->error  = "";
					$this->ipsclass->DB->failed = 0;
				}
			}
		}		
		
		//-----------------------------------------		
		// Get current table definitions
		//-----------------------------------------
		
		if( !file_exists( ROOT_PATH."/install/sql/{$this->ipsclass->vars['sql_driver']}_tables.php" ) )
		{
			$this->ipsclass->admin->error( "You must upload /install/sql/{$this->ipsclass->vars['sql_driver']}_tables.php from the IPB installation package for your current version to run this tool" );
		}

		//require ROOT_PATH."sources/action_admin/sql_{$this->ipsclass->vars['sql_driver']}.php";
		require_once( KERNEL_PATH . 'db_lib/' . strtolower($this->ipsclass->vars['sql_driver']) . '_tools.php' );
		require_once( ROOT_PATH . 'install/sql/' . strtolower($this->ipsclass->vars['sql_driver']) . '_tables.php' );
		$db_tools = new db_tools( $this->ipsclass );
		
		$output = array();
		
		if ( !$output = $db_tools->db_table_diag( $TABLE ) )
		{
			$this->ipsclass->admin->error( "There was an error and we could not process your " . strtolower($this->ipsclass->vars['sql_driver']) . "_tables.php file." );
		}
		
		if( $output['error_count'] > 0 )
		{
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			
			$this->ipsclass->html .= $this->html->dbchecker_javascript();
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "警告: 发现错误" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( array( "<span class='rss-feed-invalid'>您的数据库有错误. 您可以在下面看到它们, 您也可以点击 <a href='#' onclick='fix_all_dberrors();'>这里</a> 来修复这些错误.", 2 ) ) );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		else
		{
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			$this->ipsclass->adskin->td_header[] = array( "{none}"    	, "50%" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "没有错误" );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( array( "<span class='rss-feed-valid'>您的数据库索引没有错误.", 2 ) ) );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}			
		
		$this->ipsclass->adskin->td_header[] = array( "表"    	, "20%" );
		$this->ipsclass->adskin->td_header[] = array( "索引"  , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "修复"       	, "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "数据库索引核对结果" );
		
		$good_img = "<img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='YN' class='ipd' />";
		$bad_img  = "<img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='YN' class='ipd' />";
				
		$i = 0;
		
		foreach( $output['results'] as $data )
		{
			if( $data['status'] == 'error' )
			{
				$popup_div = "<div style='border: 2px outset rgb(85, 85, 85); padding: 4px; background: rgb(238, 238, 238) none repeat scroll 0%; position: absolute; width: auto; display: none; text-align: center; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;' id='{$i}' align='center'>{$data['fixsql']}</div>";
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<span style='color:red'>{$data['table']}</span>",
																						"<span style='color:red'>{$good_img}</span>",
																						"<center><script type='text/javascript'>all_queries[{$i}] = '".base64_encode($data['fixsql'])."';</script><a href='{$this->ipsclass->base_url}&amp;section=help&amp;act=diag&amp;code=dbchecker&amp;query=".urlencode(base64_encode($data['fixsql']))."'><b>Fix Automatically</b></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href'#' onclick=\"toggleview('{$i}');return false;\" style='cursor: pointer;'><b>Fix Manually</b></a><br />{$popup_div}</center>"
																			   ) 	  );
				$i++;
			}
			else
			{
				$this->ipsclass->html .=  $this->ipsclass->adskin->add_td_row( array( "<span style='color:green'>{$data['table']}</span>",
																						"<span style='color:green'>{$good_img}</span>",
																						"&nbsp;"
																			   ) 	  );
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
    }    
	
	/*-------------------------------------------------------------------------*/
	// SHOW FUNCTIONS
	/*-------------------------------------------------------------------------*/
	
	function list_functions()
	{
		$this->ipsclass->admin->page_detail = "在这里您可以对您的 IPB 论坛运行诊断工具";
		$this->ipsclass->admin->page_title  = "IPB 诊断";
		
		//-----------------------------------------
		// PHP INFO?
		//-----------------------------------------
		
		if ( isset($this->ipsclass->input['phpinfo']) AND $this->ipsclass->input['phpinfo'] )
		{
			@ob_start();
			phpinfo();
			$parsed = @ob_get_contents();
			@ob_end_clean();
			
			preg_match( "#<body>(.*)</body>#is" , $parsed, $match1 );
			
			$php_body  = $match1[1];
			
			# PREVENT WRAP: Most cookies
			$php_body  = str_replace( "; " , ";<br />"   , $php_body );
			# PREVENT WRAP: Very long string cookies
			$php_body  = str_replace( "%3B", "<br />"    , $php_body );
			# PREVENT WRAP: Serialized array string cookies
			$php_body  = str_replace( ";i:", ";<br />i:" , $php_body );
			# PREVENT WRAP: LS_COLORS env
			$php_body  = str_replace( ":*.", "<br />:*." , $php_body );
			# PREVENT WRAP: PATH env
			$php_body  = str_replace( "bin:/", "bin<br />:/" , $php_body );
			# PREVENT WRAP: Cookie %2C split
			$php_body  = str_replace( "%2C", "%2C<br />" , $php_body );
			#PREVENT WRAP: Cookie , split
			$php_body  = preg_replace( "#,(\d+),#", ",<br />\\1," , $php_body );
			
			
			$php_style = "<style type='text/css'>
						  .center {text-align: center;}
						  .center table { margin-left: auto; margin-right: auto; text-align: left; }
						  .center th { text-align: center; }
						  h1 {font-size: 150%;}
						  h2 {font-size: 125%;}
						  .p {text-align: left;}
						  .e {background-color: #ccccff; font-weight: bold;}
						  .h {background-color: #9999cc; font-weight: bold;}
						  .v {background-color: #cccccc; white-space: normal;}
						  </style>\n";
						  
			$this->ipsclass->html = $php_style . $php_body;
			$this->ipsclass->admin->output();
		}

		$this->ipsclass->DB->sql_get_version();		
		$sql_version = strtoupper(SQL_DRIVER)." ".$this->ipsclass->DB->true_version;
		
		$php_version = phpversion()." (".@php_sapi_name().")  ( <a href='{$this->ipsclass->base_url}&section=help&act=diag&phpinfo=1'>PHP INFO</a> )";
		$server_software = php_uname();
		
		$load_limit = "--";
        $server_load_found = 0;
        
        //-----------------------------------------
        // Check cache first...
        //-----------------------------------------
        
        if( $this->ipsclass->cache['systemvars']['loadlimit'] )
        {
	        $loadinfo = explode( "-", $this->ipsclass->cache['systemvars']['loadlimit'] );
	        
	        if ( intval($loadinfo[1]) > (time() - 10) )
	        {
		        //-----------------------------------------
		        // Cache is less than 10 secs old, use it
		        //-----------------------------------------
		        
		        $server_load_found = 1;
		        
    			$load_limit = $loadinfo[0];
			}
		}
	        
        //-----------------------------------------
        // No cache or it's old, check real time
        //-----------------------------------------
		
		if( !$server_load_found )
		{
	        # @ supressor fixes warning in >4.3.2 with open_basedir restrictions
	        
        	if ( @file_exists('/proc/loadavg') )
        	{
        		if ( $fh = @fopen( '/proc/loadavg', 'r' ) )
        		{
        			$data = @fread( $fh, 6 );
        			@fclose( $fh );
        			
        			$load_avg = explode( " ", $data );
        			
        			$load_limit = trim($load_avg[0]);
        		}
        	}
        	else if( strstr( strtolower(PHP_OS), 'win' ) )
        	{
		        /*---------------------------------------------------------------
		        | typeperf is an exe program that is included with Win NT,
		        |	XP Pro, and 2K3 Server.  It can be installed on 2K from the
		        |	2K Resource kit.  It will return the real time processor
		        |	Percentage, but will take 1 second processing time to do so.
		        |	This is why we shall cache it, and check only every 2 mins.
		        |
		        |	Can also be obtained from COM, but it's extremely slow...
		        ---------------------------------------------------------------*/
	        	
	        	$serverstats = @shell_exec("typeperf \"Processor(_Total)\% Processor Time\" -sc 1");
	        	
	        	if( $serverstats )
	        	{
					$server_reply = explode( "\n", str_replace( "\r", "", $serverstats ) );
					$serverstats = array_slice( $server_reply, 2, 1 );
					
					$statline = explode( ",", str_replace( '"', '', $serverstats[0] ) );
					
					$load_limit = round( $statline[1], 4 );
				}
			}
        	else
        	{
				if ( $serverstats = @exec("uptime") )
				{
					preg_match( "/(?:averages)?\: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load );
					
					$load_limit = $load[1];
				}
			}
			
			if( $load_limit )
			{
				$this->ipsclass->cache['systemvars']['loadlimit'] = $load_limit."-".time();
				
				$this->ipsclass->update_cache(  array( 'name' => 'systemvars', 'array' => 1, 'deletefirst' => 0 ) );
			}
		}
		
		$total_memory = $avail_memory = "--";

		if( strstr( strtolower(PHP_OS), 'win' ) )
		{
			$mem = @shell_exec('systeminfo');
			
			if( $mem )
			{
				$server_reply = explode( "\n", str_replace( "\r", "", $mem ) );
				
				if( count($server_reply) )
				{
					foreach( $server_reply as $info )
					{
						if( strstr( $info, "统计物理内存" ) )
						{
							$total_memory =  trim( str_replace( ":", "", strrchr( $info, ":" ) ) );
						}
						
						if( strstr( $info, "可用物理内存" ) )
						{
							$avail_memory =  trim( str_replace( ":", "", strrchr( $info, ":" ) ) );
						}
					}
				}
			}
		}
		else
		{
			$mem = @shell_exec("free -m");
			$server_reply = explode( "\n", str_replace( "\r", "", $mem ) );
			$mem = array_slice( $server_reply, 1, 1 );
			$mem = preg_split( "#\s+#", $mem[0] );

			$total_memory = $mem[1].' MB';
			$avail_memory = $mem[3].' MB';
		}
		
		$disabled_functions = @ini_get('disable_functions') ? str_replace( ",", ", ", @ini_get('disable_functions') ) : "<i>no information</i>";
		
   		//-----------------------------------------
   		// Upgrade history?
   		//-----------------------------------------
   		
		$upgrade_history 	= array();
   		$latest_version 	= array( 'upgrade_version_id' => NULL );
   		
   		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'upgrade_history', 'order' => 'upgrade_version_id DESC', 'limit' => array(0, 5) ) );
   		$this->ipsclass->DB->simple_exec();
   		
   		while( $r = $this->ipsclass->DB->fetch_row() )
   		{
   			if ( $r['upgrade_version_id'] > $latest_version['upgrade_version_id'] )
   			{
   				$latest_version = $r;
   			}
   			
   			$upgrade_history[] = $r;
   		}
   		
		//-----------------------------------------
		// Got real version number?
		//-----------------------------------------
		
		$this->ipsclass->version = 'v'.$latest_version['upgrade_version_human'];
		$this->ipsclass->vn_full = ( isset($latest_version['upgrade_notes']) AND $latest_version['upgrade_notes'] ) ? $latest_version['upgrade_notes'] : $this->ipsclass->vn_full;

		//-----------------------------------------
		// Version History
		//-----------------------------------------
		
		foreach( $upgrade_history as $r )
		{
			$r['_date'] = $this->ipsclass->get_date( $r['upgrade_date'], 'SHORT' );
			
			$thiscontent .= $this->html->acp_version_history_row( $r );
		}
		
		$this->ipsclass->adskin->td_header[] = array( "{none}"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "{none}" 	, "60%" );		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "系统概况" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "IPB 版本",
																				$this->ipsclass->version . " (ID:" . $this->ipsclass->vn_full . ")" ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( strtoupper(SQL_DRIVER)." Version",
																				$sql_version ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "PHP 版本",
																				$php_version ) );
																				
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "禁止的 PHP 函数",
																				$disabled_functions ) );																				

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "安全模式",
																				SAFE_MODE_ON == 1 ? "<span style='color:red;font-weight:bold;'>ON</span>" : "<span style='color:green;font-weight:bold;'>OFF</span>" ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "服务器软件",
																				$server_software ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "Current Server Load",
																				$load_limit ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "服务器内存总数",
																				$total_memory ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "可用物理内存",
																				$avail_memory ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "系统进程" );

		
		if( strstr( strtolower(PHP_OS), 'win' ) )
		{
			$tasks = @shell_exec( "tasklist" );
			$tasks = str_replace( " ", "&nbsp;", $tasks );
		}
		else
		{
			$tasks = @shell_exec( "top -b -n 1" );
			$tasks = str_replace( " ", "&nbsp;", $tasks );
		}

		if( !$tasks )
		{
			$tasks = "<i>无法获得进程信息</i>";
		}
		else
		{
			/* UTF-8 Char set - Skylook @ IPBChina.CON
			if ( strtolower($this->ipsclass->vars['gb_char_set']) != 'utf-8' )
			{
				$tasks = $this->ipsclass->txt_convert_charsets( $tasks, 'UTF-8' );
			}	
			*/
			$tasks = "<pre>".$tasks."</pre>";
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $tasks ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= $this->html->acp_version_history_wrapper( $thiscontent );
		
		//-----------------------------------------		
		// File Version Checker
		//-----------------------------------------
		/*
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'fileversions' ),
												                 			 2 => array( 'act'   , 'diag' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File Version Checker" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "This tool will check your file versions to verify they match.  Running different versions of core files can result in unexpected behavior." ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Run Tool');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------		
		// Whitespace Checker
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'whitespace' ),
												                 			 2 => array( 'act'   , 'diag' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Whitespace Checker" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "If your php files have whitespace at the beginning or end of the file, it can cause certain issues with IPB.  This tool will check for any files that have this problem." ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Run Tool');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------		
		// File Permissions Checker
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'filepermissions' ),
												                 			 2 => array( 'act'   , 'diag' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File Permissions Checker" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "This tool will check your file and folder permissions to verify those that need to be writeable are.  If permissions are not set properly your skins, languages and other board elements may not be saved correctly." ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Run Tool');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------		
		// DB Table/Column Checker
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'dbchecker' ),
												                 			 2 => array( 'act'   , 'diag' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Database Table/Column Checker" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "This tool will check your database tables to verify they are up to date and accurate." ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Run Tool');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();			
		
		//-----------------------------------------		
		// DB Index Checker
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'dbindex' ),
												                 			 2 => array( 'act'   , 'diag' ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
									                    			 )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Database Index Checker" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "This tool will check your database indexes to ensure they are correct.  If databases indexes are not set properly, your database may not run as fast or as efficient as it should." ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form('Run Tool');
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();		
		*/
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	
	}
	
	
}


?>