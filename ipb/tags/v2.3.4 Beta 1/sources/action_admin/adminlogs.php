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
|   > $Date: 2007-03-29 18:12:27 -0400 (Thu, 29 Mar 2007) $
|   > $Revision: 914 $
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

class ad_adminlogs
{

	var $base_url;
	var $colours = array();
	
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
	var $perm_child = "adminlog";
	
	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, '管理员操作记录' );
		
		//-----------------------------------------
		// Make sure we're a root admin, or else!
		//-----------------------------------------
		
		if ($this->ipsclass->member['mgroup'] != $this->ipsclass->vars['admin_group'])
		{
			//$this->ipsclass->admin->error("抱歉, 只有系统管理员才能使用本功能");
		}
		
		$this->colours  = array(
								"cat"      => "green",
								"forum"    => "darkgreen",
								"mem"      => "red",
								'group'    => "purple",
								'mod'      => 'orange',
								'op'       => 'darkred',
								'help'     => 'darkorange',
								'modlog'   => 'steelblue',
				   			   );
		

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
	// Remove archived files
	//-----------------------------------------
	
	function view()
	{
		$start = intval($this->ipsclass->input['st']) >=0 ? intval($this->ipsclass->input['st']) : 0;
		
		$this->ipsclass->admin->page_detail = "查看某个管理员的所有记录";
		$this->ipsclass->admin->page_title  = "管理员操作记录";
		
		if ( ( !isset($this->ipsclass->input['search_string']) OR !$this->ipsclass->input['search_string'] ) AND ( !isset($this->ipsclass->input['mid']) OR !$this->ipsclass->input['mid'] ) )
		{
			$this->ipsclass->main_msg = "您必须输入搜索字串";
			$this->list_current();
			return;
		}
		
		if ( !isset($this->ipsclass->input['search_string']) OR !$this->ipsclass->input['search_string'] )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => 'COUNT(id) as count', 'from' => 'admin_logs', 'where' => "member_id=".intval($this->ipsclass->input['mid']) ) );
			$this->ipsclass->DB->simple_exec();
		
			$row = $this->ipsclass->DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&{$this->ipsclass->form_code}&mid={$this->ipsclass->input['mid']}&code=view";
			
			$this->ipsclass->DB->cache_add_query( 'adminlogs_view_one', array( 'mid' => intval($this->ipsclass->input['mid']), 'limit_a' => $start ) );
			$this->ipsclass->DB->cache_exec_query();
		
		}
		else
		{
			$this->ipsclass->input['search_string'] = urldecode($this->ipsclass->input['search_string']);
			
			if( $this->ipsclass->input['search_type'] == 'member_id' )
			{
				$dbq = "m.".$this->ipsclass->input['search_type']."='".$this->ipsclass->input['search_string']."'";
			}
			else
			{
				$dbq = "m.".$this->ipsclass->input['search_type']." LIKE '%".$this->ipsclass->input['search_string']."%'";
			}
			
			$row = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(m.id) as count', 'from' => 'admin_logs m', 'where' => $dbq ) );
			
			$row_count = $row['count'];
			
			$query = "&act=adminlog&code=view&search_type={$this->ipsclass->input['search_type']}&search_string=".urlencode($this->ipsclass->input['search_string']);
			
			$this->ipsclass->DB->cache_add_query( 'adminlogs_view_two', array( 'dbq' => $dbq, 'limit_a' => $start ) );
			$this->ipsclass->DB->cache_exec_query();
		}
		
		$links = $this->ipsclass->adskin->build_pagelinks( array( 'TOTAL_POSS'  => $row_count,
														  'PER_PAGE'    => 20,
														  'CUR_ST_VAL'  => $start,
														  'L_SINGLE'    => "单页",
														  'L_MULTI'     => "页码: ",
														  'BASE_URL'    => $this->ipsclass->base_url.$query,
														)
												 );
									  
		$this->ipsclass->admin->page_detail = "您可以查看或删除您的管理员的操作记录";
		$this->ipsclass->admin->page_title  = "管理员操作记录";
		
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "用户名"            , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "执行操作"        , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "操作时间"         , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "IP 地址"             , "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "保存管理记录" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic($links, 'center', 'tablesubheader');
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $row = $this->ipsclass->DB->fetch_row() )
			{
				$row['ctime'] = $this->ipsclass->admin->get_date( $row['ctime'], 'LONG' );
				
				$this->colours[$row['act']] = isset($this->colours[$row['act']]) ? $this->colours[$row['act']] : 'black';
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$row['members_display_name']}</b>",
														  "<span style='color:{$this->colours[$row['act']]}'>{$row['note']}</span>",
														  "{$row['ctime']}",
														  "{$row['ip_address']}",
												 )      );
			
			
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<center>没有结果</center>");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic($links, 'center', 'tablesubheader');
		
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
		if ($this->ipsclass->input['mid'] == "")
		{
			$this->ipsclass->admin->error("You did not select a member ID to remove by!");
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'admin_logs', 'where' => "member_id=".intval($this->ipsclass->input['mid']) ) );
		
		$this->ipsclass->boink_it($this->ipsclass->base_url."&{$this->ipsclass->form_code}");
	}
	
	
	//-----------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-----------------------------------------
	
	function list_current()
	{
		$form_array = array();
	
		$this->ipsclass->admin->page_detail = "您可以在这里查看或删除管理员在后台操作记录 (比如版块控制, 会员控制, 用户组控制, 帮助文件以及版主操作记录管理).";
		$this->ipsclass->admin->page_title  = "管理员操作记录";
		
		//-----------------------------------------
		// LAST FIVE ACTIONS
		//-----------------------------------------
		
		$this->ipsclass->DB->cache_add_query( 'adminlogs_view_list_current', array() );
		$this->ipsclass->DB->cache_exec_query();
		
		$this->ipsclass->adskin->td_header[] = array( "用户名"            , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "执行操作"        , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "操作时间"         , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "IP 地址"             , "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "最近的 5 条管理员操作记录" );
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $row = $this->ipsclass->DB->fetch_row() )
			{
			
				$row['ctime'] = $this->ipsclass->admin->get_date( $row['ctime'], 'LONG' );
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$row['name']}</b>",
														  "<span style='color:{$this->colours[$row['act']]}'>{$row['note']}</span>",
														  "{$row['ctime']}",
														  "{$row['ip_address']}",
												 )      );
			
			
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<center>No results</center>");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "用户名"            , "30%" );
		$this->ipsclass->adskin->td_header[] = array( "执行操作"       , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "查看该会员的所有记录"     , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "删除该会员的所有记录"   , "30%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "管理员操作记录统计" );
		
		$this->ipsclass->DB->cache_add_query( 'adminlogs_view_list_current_two', array() );
		$this->ipsclass->DB->cache_exec_query();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$r['name']}</b>",
													  "<center>{$r['act_count']}</center>",
													  "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=view&mid={$r['member_id']}'>查看</a></center>",
													  "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=remove&mid={$r['member_id']}'>删除</a></center>",
											 )      );
		}
			
		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , 'view'     ),
																			 2 => array( 'act'   , 'adminlog'       ),
																			 3 => array( 'section', $this->ipsclass->section_code ),
																	)      );
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "搜索管理员操作记录" );
		
		$form_array = array(
							  0 => array( 'note'      , '执行操作' ),
							  1 => array( 'ip_address',  'IP 地址'  ),
							  2 => array( 'member_id' , '会员 ID' ),
							  3 => array( 'act'        , 'ACT Setting'  ),
							  4 => array( 'code'       , 'CODE Setting'  ),
						   );
			
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索字串...</b>" ,
										  		  $this->ipsclass->adskin->form_input( "search_string")
								 )      );
								 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>搜索范围...</b>" ,
										  		  $this->ipsclass->adskin->form_dropdown( "search_type", $form_array)
								 )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("搜索");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		//-------------------------------
		
		$this->ipsclass->admin->output();
	
	}
	
	
	
}


?>
