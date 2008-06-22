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
|   > $Date: 2007-03-28 18:08:28 -0400 (Wed, 28 Mar 2007) $
|   > $Revision: 910 $
|   > $Author: bfarber $
+---------------------------------------------------------------------------
|
|   > Spider (MAN) Logs
|   > Module written by Matt Mecham
|   > Date started: 28th May 2003
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_spiderlogs {

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
	var $perm_child = "spiderlog";


	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '搜索引擎蜘蛛记录' );
		
		//-----------------------------------------
		// Get bot names
		//-----------------------------------------
		
		foreach( explode( "\n", $this->ipsclass->vars['search_engine_bots'] ) as $bot )
		{
			list($ua, $n) = explode( "=", $bot );
			
			$this->bot_map[ strtolower($ua) ] = $n;
		}
		
		switch($this->ipsclass->input['code'])
		{
			case 'view':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->view();
				break;
				
			case 'remove':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':remove' );
				$this->remove();
				break;
				
			//-----------------------------------------
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':' );
				$this->list_current();
				break;
		}
		
	}
	
	//-----------------------------------------
	// View Logs
	//-----------------------------------------
	
	function view()
	{
		$start = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		
		$this->ipsclass->admin->page_detail = "查看搜索引擎蜘蛛的所有操作记录";
		$this->ipsclass->admin->page_title  = "搜索引擎记录管理";
		
		$botty = urldecode($this->ipsclass->input['bid']);
		$botty = str_replace( "&#33;", "!", $botty );
	
		if ( !isset($this->ipsclass->input['search_string']) OR $this->ipsclass->input['search_string'] == "" )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'COUNT(sid) as count', 'from' => 'spider_logs', 'where' => "bot='$botty'" ) );
			$this->ipsclass->DB->simple_exec();
		
			$row = $this->ipsclass->DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&{$this->ipsclass->form_code}&bid={$this->ipsclass->input['bid']}&code=view";
			
			$this->ipsclass->DB->simple_construct( array( 'select' => '*',
										  'from'   => 'spider_logs',
										  'where'  => "bot='$botty'",
										  'order'  => 'entry_date DESC',
										  'limit'  => array( $start, 20 ) ) );
			$this->ipsclass->DB->simple_exec();
		}
		else
		{
			$this->ipsclass->input['search_string'] = urldecode($this->ipsclass->input['search_string']);
			
			$this->ipsclass->DB->simple_construct( array( 'select' => 'COUNT(sid) as count', 'from' => 'spider_logs', 'where' => "query_string LIKE '%{$this->ipsclass->input['search_string']}%'" ) );
			$this->ipsclass->DB->simple_exec();
			
			$row = $this->ipsclass->DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&{$this->ipsclass->form_code}&code=view&search_string=".urlencode($this->ipsclass->input['search_string']);
			
			$this->ipsclass->DB->simple_construct( array( 'select' => '*',
										  'from'   => 'spider_logs',
										  'where'  => "query_string LIKE '%{$this->ipsclass->input['search_string']}%'",
										  'order'  => 'entry_date DESC',
										  'limit'  => array( $start, 20 ) ) );
			$this->ipsclass->DB->simple_exec();
		}
		
		$links = $this->ipsclass->adskin->build_pagelinks( array( 'TOTAL_POSS'  => $row_count,
											   'PER_PAGE'    => 20,
											   'CUR_ST_VAL'  => $start,
											   'L_SINGLE'    => "单页",
											   'L_MULTI'     => "页码: ",
											   'BASE_URL'    => $this->ipsclass->base_url.$query,
											 )
									  );
									  
		$this->ipsclass->admin->page_detail = "您可以在这里查看或删除搜索引擎机器人的操作记录";
		$this->ipsclass->admin->page_title  = "搜索引擎记录管理";
		
        //-----------------------------------------
		// Show form!
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "机器人名称"            , "15%" );
		$this->ipsclass->adskin->td_header[] = array( "查询字串"        , "15%" );
		$this->ipsclass->adskin->td_header[] = array( "操作时间"      , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "IP 地址"          , "10%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "保存的搜索引擎记录" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic($links, 'right', 'tablesubheader');
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $row = $this->ipsclass->DB->fetch_row() )
			{
				$extra = "";
				
				if ( preg_match( '#lo-fi#i', $row['query_string'] ) )
				{
					$extra = '(Lo-Fi)';
					
					$query_string_html = $extra . ' ' . $row['query_string'];
				}
				else
				{
					$query_string_html = "<a href='{$this->ipsclass->vars['board_url']}/index.{$this->ipsclass->vars['php_ext']}?{$row['query_string']}' target='_blank'>{$row['query_string']}</a>";
				}
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>".$this->bot_map[ strtolower($row['bot']) ]."</b>",
																		 $query_string_html,
																		 $this->ipsclass->admin->get_date( $row['entry_date'], 'LONG' ),
																		 "{$row['ip_address']}",
																)      );
			
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<center>没有记录</center>");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic($links, 'right', 'tablesubheader');
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->admin->output();
		
	}
	
	//-----------------------------------------
	// Remove archived files
	//-----------------------------------------
	
	function remove()
	{
		if ($this->ipsclass->input['bid'] == "")
		{
			$this->ipsclass->admin->error("You did not select a bot to remove by!");
		}
		
		$botty = urldecode($this->ipsclass->input['bid']);
		$botty = str_replace( "&#33;", "!", $botty );
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'spider_logs', 'where' => "bot='$botty'" ) );
		
		$this->ipsclass->admin->save_log("删除搜索引擎记录");
		
		$this->ipsclass->boink_it($this->ipsclass->base_url."&{$this->ipsclass->form_code}");
		exit();
	}
	
	
	//-----------------------------------------
	// SHOW ALL BOTS
	//-----------------------------------------
	
	function list_current()
	{
		$form_array = array();
	
		$this->ipsclass->admin->page_detail = "您可以在这里查看或删除搜索引擎蜘蛛的操作记录";
		$this->ipsclass->admin->page_title  = "搜索引擎记录管理";

		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "机器人名称"            , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "访问次数"                , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "最后访问时间"            , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "查看所有记录"     , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "删除所有记录"   , "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "保存的搜索引擎记录" );
		
									  
		$this->ipsclass->DB->cache_add_query( 'spiderlogs_list_current', array() );
		$this->ipsclass->DB->cache_exec_query();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$url_butt = urlencode($r['bot']);
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $this->bot_map[ strtolower($r['bot']) ],
																	 "<center>{$r['cnt']}</center>",
																	  $this->ipsclass->admin->get_date( $r['entry_date'], 'SHORT' ),
																	 "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=view&bid={$url_butt}'>View</a></center>",
																	 "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=remove&bid={$url_butt}'>Remove</a></center>",
															)      );
		}
			
		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'view'     ),
																 2 => array( 'act'   , 'spiderlog'       ),
																 4 => array( 'section', $this->ipsclass->section_code ),
														)      );
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "搜索记录" );
			
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索条件...</b>" ,
										  		  $this->ipsclass->adskin->form_input( "search_string").'... 请输入查询条件中的关键字'
								 )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("搜索");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->admin->output();
	
	}
	
	
	
}


?>