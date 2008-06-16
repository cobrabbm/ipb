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
|   > $Date: 2007-06-19 11:26:06 -0400 (Tue, 19 Jun 2007) $
|   > $Revision: 1046 $
|   > $Author: matt $
+---------------------------------------------------------------------------
|
|   > Administration Module
|   > Module written by Matt Mecham
|   > Date started: 27th January 2004
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

class ad_administration
{
	var $ipsclass;
	var $html;
	var $map;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "tools";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "admin";
	
	function auto_run()
	{
		//-----------------------------------------
		// Require and RUN !! THERES A BOMB
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "您可以在这里查看并更新缓存的内容.";
		$this->ipsclass->admin->page_title  = "缓存管理";
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '缓存管理' );
		
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html = $this->ipsclass->acp_load_template('cp_skin_tools');
		
		//-----------------------------------------
		// Map
		//-----------------------------------------
		
		$this->map = array( 'forum_cache'   => '版块信息和数据',
							'group_cache'   => '用户组信息和数据',
							'systemvars'    => '系统运行变量',
							'skin_id_cache' => '主题信息和数据',
							'moderators'    => '版主信息和数据',
							'stats'         => '论坛统计, 例如帖子数等',
							'ranks'         => '会员头衔和等级信息',
							'profilefields' => '自定义数据信息',
							'birthdays'     => '会员生日',
							'calendar'      => '未来的日历事件',
							'calendars'		=> '您添加的日历',
							'multimod'      => '主题批处理',
							'bbcode'        => "自定义 BBCode",
							'settings'      => "论坛设置和变量",
							'emoticons'     => '论坛表情图标',
							'badwords'      => '论坛过滤',
							'languages'     => '语言包',
							'banfilters'    => '屏蔽的 IP 地址',
							'attachtypes'   => '附件类型',
							'announcements' => '公告缓存',
							'components'    => '开启的组件',
						  );
		
		//-----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch($this->ipsclass->input['code'])
		{
			case 'cacheend':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->cache_end();
				break;
				
			case 'viewcache':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->view_cache();
				break;
			
			case 'cache_update_all':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->ipsclass->admin->output_multiple_redirect_init( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code_js.'&code=cache_update_all_process&id=0' );
				break;
				
			case 'cache_update_all_process':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':recache' );
				$this->cache_update_all_process();
				break;
		
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->cache_start();
				break;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Process....
	/*-------------------------------------------------------------------------*/
	
	function cache_update_all_process()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$id         = intval( $this->ipsclass->input['id'] );
		$cache_name = '';
		$count      = 0;
		$img        = '<img src="'.$this->ipsclass->skin_acp_url.'/images/aff_tick_small.png" border="0" alt="-" /> ';
		
		//-----------------------------------------
		// Get cache name
		//-----------------------------------------
		
		foreach( $this->map as $name => $desc )
		{
			if ( $count == $id )
			{
				$cache_name = $name;
				break;
			}
			
			$count++;
		}
		
		//-----------------------------------------
		// Do what, now?
		//-----------------------------------------
		
		$id++;
		
		if ( $cache_name )
		{
			$this->cache_end( $cache_name, 1 );
			
			$this->ipsclass->admin->output_multiple_redirect_hit( $this->ipsclass->base_url.'&'.$this->ipsclass->form_code_js.'&code=cache_update_all_process&id='.$id, $img.$cache_name.' processed...' );
		}
		else
		{
			$this->ipsclass->admin->output_multiple_redirect_done();
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// DO VIEW
	/*-------------------------------------------------------------------------*/
	
	function view_cache()
	{
		if ( ! $this->ipsclass->input['cache'] )
		{
			$this->ipsclass->main_msg = "用户 ID 不存在,请重试";
			$this->cache_start();
		}
		
		$row = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'cache_store', 'where' => "cs_key='{$this->ipsclass->input['cache']}'" ) );
		
		ob_start();
		if ( $row['cs_array'] )
		{
			print_r( unserialize($this->ipsclass->txt_stripslashes($row['cs_value'])) );
		}
		else
		{
			print $row['cs_value'];
		}
		
		$out = ob_get_contents();
		ob_end_clean();
		
		$this->ipsclass->html = "<pre>".htmlspecialchars($out)."</pre>";
		
		$this->ipsclass->admin->print_popup();
	}
		
	/*-------------------------------------------------------------------------*/
	// DO UPDATE
	/*-------------------------------------------------------------------------*/
	
	function cache_end( $cache_name='', $dontcancel=0 )
	{
		if ( ! $cache_name )
		{
			$cache_name = $this->ipsclass->input['cache'];
		}
		
		switch ( $cache_name )
		{
			//-----------------------------------------
			// Forum cache
			//-----------------------------------------
			
			case 'forum_cache':
			
				$this->ipsclass->update_forum_cache();
				
				$this->ipsclass->main_msg = '版块缓存已更新';
				break;
				
			//-----------------------------------------
			// Group Cache
			//-----------------------------------------
			
			case 'group_cache':
				$this->ipsclass->cache['group_cache'] = array();
			
				$this->ipsclass->DB->simple_construct( array( 'select' => "*",
															  'from'   => 'groups'
													 )      );
				
				$this->ipsclass->DB->simple_exec();
				
				while ( $i = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->cache['group_cache'][ $i['g_id'] ] = $i;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'group_cache', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '用户组缓存已更新';
				break;
				
			//-----------------------------------------
			// Systemvars
			//-----------------------------------------
			
			case 'systemvars':
				$this->ipsclass->cache['systemvars'] = ( is_array( $this->ipsclass->cache['systemvars'] ) ) ? $this->ipsclass->cache['systemvars'] : array();
				
				require_once( ROOT_PATH.'sources/lib/func_taskmanager.php' );
				$task = new func_taskmanager();
				$task->ipsclass =& $this->ipsclass;
				$task->save_next_run_stamp();
				
				$result = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as cnt', 'from' => 'mail_queue' ) );
				
				$this->ipsclass->cache['systemvars']['mail_queue'] = intval( $result['cnt'] );
				
		        if ($this->ipsclass->vars['load_limit'] > 0)
		        {
			        # @ supressor fixes warning in >4.3.2 with open_basedir restrictions
			        
		        	if ( @file_exists('/proc/loadavg') )
		        	{
		        		if ( $fh = @fopen( '/proc/loadavg', 'r' ) )
		        		{
		        			$data = @fread( $fh, 6 );
		        			@fclose( $fh );
		        			
		        			$load_avg = explode( " ", $data );
		        			
		        			$this->ipsclass->server_load = trim($load_avg[0]);
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
							
							$this->ipsclass->server_load = round( $statline[1], 4 );
						}
					}
		        	else
		        	{
						if ( $serverstats = @exec("uptime") )
						{
							preg_match( "/(?:averages)?\: ([0-9\.]+)[^0-9\.]+([0-9\.]+)[^0-9\.]+([0-9\.]+)\s*/", $serverstats, $load );
							
							$this->ipsclass->server_load = $load[1];
						}
					}
					
					if( $this->ipsclass->server_load )
					{
						$this->ipsclass->cache['systemvars']['loadlimit'] = $this->ipsclass->server_load."-".time();
					}
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'systemvars', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '系统变量缓存已更新';
				break;
				
			//-----------------------------------------
			// Skin ID cache
			//-----------------------------------------
			
			case 'skin_id_cache':
				require_once( ROOT_PATH.'sources/lib/admin_cache_functions.php' );
    			$admin = new admin_cache_functions();
    			$admin->ipsclass =& $this->ipsclass;
    			$admin->_rebuild_skin_id_cache();
				
				$this->ipsclass->main_msg = '主题缓存已更新';
				break;
				
			//-----------------------------------------
			// Moderators
			//-----------------------------------------
			
			case 'moderators':
			
				$this->ipsclass->cache['moderators'] = array();
				
				require_once( ROOT_PATH.'sources/action_admin/moderator.php' );
				$this->mod           =  new ad_moderator();
				$this->mod->ipsclass =& $this->ipsclass;
				
				$this->mod->rebuild_moderator_cache();
				
				$this->ipsclass->main_msg = '版主缓存已更新';
				break;
				
			//-----------------------------------------
			// Stats
			//-----------------------------------------
			
			case 'stats':
			
				
				
				
				$this->ipsclass->main_msg = '统计缓存已更新';
				break;
				
			//-----------------------------------------
			// Ranks
			//-----------------------------------------
			
			case 'ranks':
			
				$this->ipsclass->cache['ranks'] = array();
        	
				$this->ipsclass->DB->simple_construct( array( 'select' => 'id, title, pips, posts',
															  'from'   => 'titles',
															  'order'  => "posts DESC",
													)      );
									
				$this->ipsclass->DB->simple_exec();
							
				while ($i = $this->ipsclass->DB->fetch_row())
				{
					$this->ipsclass->cache['ranks'][ $i['id'] ] = array(
																  'TITLE' => $i['title'],
																  'PIPS'  => $i['pips'],
																  'POSTS' => $i['posts'],
																);
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'ranks', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '会员等级缓存已更新';
				break;
				
			//-----------------------------------------
			// Profile Fields
			//-----------------------------------------
			
			case 'profilefields':
			
				$this->ipsclass->cache['profilefields'] = array();
				
				$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'pfields_data', 'order' => 'pf_position' ) );
								 
				$this->ipsclass->DB->simple_exec();
				
				while ( $r = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->cache['profilefields'][ $r['pf_id'] ] = $r;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'profilefields', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '会员附加信息缓存已更新';
				break;
				
			//-----------------------------------------
			// Calendar
			//-----------------------------------------
			
			case 'calendar':
			
				require_once( ROOT_PATH.'sources/lib/func_taskmanager.php' );
				$func = new func_taskmanager();
				$func->ipsclass =& $this->ipsclass;
				
				require_once( ROOT_PATH.'sources/tasks/calendarevents.php' );
				
				$task = new task_item();
				$task->register_class( $func );
				$task->restrict_log = 1;
				$task->run_task();
				
				$this->ipsclass->main_msg = '日历事件缓存已更新';
				break;
				
			case 'calendars':
			
				require_once( ROOT_PATH.'sources/action_admin/calendars.php' );
				$cal = new ad_calendars();
				$cal->ipsclass =& $this->ipsclass;
				$cal->calendars_rebuildcache();
				
				$this->ipsclass->main_msg = '日历缓存已更新';
				break;				
				
			//-----------------------------------------
			// Birthdays
			//-----------------------------------------
			
			case 'birthdays':
			
				require_once( ROOT_PATH.'sources/lib/func_taskmanager.php' );
				$func = new func_taskmanager();
				$func->ipsclass =& $this->ipsclass;
				
				require_once( ROOT_PATH.'sources/tasks/calendarevents.php' );
				
				$task = new task_item();
				$task->register_class( $func );
				$task->restrict_log = 1;
				$task->run_task();
				
				$this->ipsclass->main_msg = '生日缓存已更新';
				break;
				
			//-----------------------------------------
			// Multimoderation
			//-----------------------------------------
			
			case 'multimod':
			
				$this->ipsclass->cache['multimod'] = array();
        	
				$this->ipsclass->DB->simple_construct( array(
										 'select' => '*',
										 'from'   => 'topic_mmod',
										 'order'  => 'mm_title'
								 )      );
									
				$this->ipsclass->DB->simple_exec();
							
				while ($i = $this->ipsclass->DB->fetch_row())
				{
					$this->ipsclass->cache['multimod'][ $i['mm_id'] ] = $i;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'multimod', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '主题批处理缓存已更新';
				break;
				
			//-----------------------------------------
			// BBCODE
			//-----------------------------------------
			
			case 'bbcode':
				
				require_once( ROOT_PATH.'sources/action_admin/bbcode.php' );
				$this->bbcode           = new ad_bbcode();
				$this->bbcode->ipsclass =& $this->ipsclass;
				
				$this->bbcode->bbcode_rebuildcache();
			
				$this->ipsclass->main_msg = 'BBCode 缓存已更';
				break;
				
			//-----------------------------------------
			// SETTINGS
			//-----------------------------------------
			
			case 'settings':
				
				$this->ipsclass->cache['settings'] = array();
			
				$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'conf_settings', 'where' => 'conf_add_cache=1' ) );
				$info = $this->ipsclass->DB->simple_exec();
			
				while ( $r = $this->ipsclass->DB->fetch_row($info) )
				{
					$value = $r['conf_value'] != "" ?  $r['conf_value'] : $r['conf_default'];
			
					if ( $value == '{blank}' )
					{
						$value = '';
					}
			
					$this->ipsclass->cache['settings'][ $r['conf_key'] ] = $value;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'settings', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '设置缓存已更新';
				break;
				
			//-----------------------------------------
			// EMOTICONS
			//-----------------------------------------
			
			case 'emoticons':
				
				require_once( ROOT_PATH.'sources/action_admin/emoticons.php' );
				$this->emoticons           = new ad_emoticons();
				$this->emoticons->ipsclass =& $this->ipsclass;
				
				$this->emoticons->emoticon_rebuildcache();
			
				$this->ipsclass->main_msg = '表情图标缓存已更新';
				break;
				
			//-----------------------------------------
			// BADWORDS
			//-----------------------------------------
			
			case 'badwords':
				
				require_once( ROOT_PATH.'sources/action_admin/banandbadword.php' );
				$this->banandbadword           = new ad_banandbadword();
				$this->banandbadword->ipsclass =& $this->ipsclass;
				
				$this->banandbadword->badword_rebuildcache();
				
				$this->ipsclass->main_msg = '过滤缓存已更新';
				break;
			
			//-----------------------------------------
			// LANGUAGES
			//-----------------------------------------
			
			case 'languages':
			
				$this->ipsclass->cache['languages'] = array();
		
				$this->ipsclass->DB->simple_construct( array( 'select' => 'ldir,lname', 'from' => 'languages' ) );
				$this->ipsclass->DB->simple_exec();
				
				while ( $r = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->cache['languages'][] = $r;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'languages', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '语言包缓存已更新';
				break;
			
			//-----------------------------------------
			// BAN FILTERS
			//-----------------------------------------
				
			case 'banfilters':
				
				require_once( ROOT_PATH.'sources/action_admin/banandbadword.php' );
				$this->banandbadword           = new ad_banandbadword();
				$this->banandbadword->ipsclass =& $this->ipsclass;
				
				$this->banandbadword->ban_rebuildcache();
				
				$this->ipsclass->main_msg = '屏蔽缓存已更新';
				break;
			
			//-----------------------------------------
			// ATTACHMENT TYPES
			//-----------------------------------------
				
			case 'attachtypes':
				$this->ipsclass->cache['attachtypes'] = array();
			
				$this->ipsclass->DB->simple_construct( array( 'select' => 'atype_extension,atype_mimetype,atype_post,atype_photo,atype_img', 'from' => 'attachments_type', 'where' => "atype_photo=1 OR atype_post=1" ) );
				$this->ipsclass->DB->simple_exec();
			
				while ( $r = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->cache['attachtypes'][ $r['atype_extension'] ] = $r;
				}
				
				$this->ipsclass->update_cache( array( 'name' => 'attachtypes', 'array' => 1, 'deletefirst' => 1 ) );
				
				$this->ipsclass->main_msg = '附件类型缓存已更新';
				break;
				
			//-----------------------------------------
			// Announcements
			//-----------------------------------------
				
			case 'announcements':
			
				require_once( ROOT_PATH.'sources/action_public/announcements.php' );
				$announcements = new announcements();
				$announcements->ipsclass =& $this->ipsclass;
				$announcements->announce_recache();
				
				$this->ipsclass->main_msg = '公告缓存已更新';
				break;
				
			//-----------------------------------------
			// BBCODE
			//-----------------------------------------
			
			case 'components':
				
				require_once( ROOT_PATH.'sources/action_admin/components.php' );
				$this->components           = new ad_components();
				$this->components->ipsclass =& $this->ipsclass;
				
				$this->components->components_rebuildcache();
			
				$this->ipsclass->main_msg = '组件缓存已更新';
				break;
					
			default:
				$this->ipsclass->main_msg = '没有指定要更新的缓存';
				break;
		}
		
		if ( ! $dontcancel )
		{
			$this->cache_start();
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// SHOW CACHE FORM
	/*-------------------------------------------------------------------------*/
	
	function cache_start()
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$used    = array();
		$content = "";
		
		//-----------------------------------------
		// SHOW CACHES
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'cache_store', 'order' => 'cs_key' ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $row = $this->ipsclass->DB->fetch_row() )
		{
			if ( ! in_array( $row['cs_key'], array_keys( $this->map ) ) )
			{
				continue;
			}
			
			$used[ $row['cs_key'] ] = $row['cs_key'];
			$row['_size']           = ceil( intval( strlen( $row['cs_value'] ) ) / 1024 );
			$row['_desc']           = $this->map[ $row['cs_key'] ];
			
			$content .= $this->html->cache_row( $row );
		}
		
		if ( count( $used ) != count( $this->map ) )
		{
			foreach( $this->map as $k => $v )
			{
				if ( in_array( $k, array_keys( $used ) ) )
				{
					continue;
				}
				else
				{
					$row['cs_key']   = $k;
					$row['cs_value'] = $v;
					$row['_size']    = 0;
					$row['_desc']    = $this->map[ $row['cs_key'] ];
					
					$content .= $this->html->cache_row( $row );
				}
			}
		}
		
		$this->ipsclass->html .= $this->html->cache_overview( $content );
		
		$this->ipsclass->admin->output();
	}
	
	/*-------------------------------------------------------------------------*/
	// PERL
	/*-------------------------------------------------------------------------*/
	
	function perly_length_sort($a, $b)
	{
		if ( strlen($a['typed']) == strlen($b['typed']) )
		{
			return 0;
		}
		return ( strlen($a['typed']) > strlen($b['typed']) ) ? -1 : 1;
	}
	
	function perly_word_sort($a, $b)
	{
		if ( strlen($a['type']) == strlen($b['type']) )
		{
			return 0;
		}
		return ( strlen($a['type']) > strlen($b['type']) ) ? -1 : 1;
	}
	
	
	
	
	
	
}


?>