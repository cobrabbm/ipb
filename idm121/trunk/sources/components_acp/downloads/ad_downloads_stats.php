<?php
/*
+--------------------------------------------------------------------------
|   Invision Download Manager
|   ========================================
|   by Brandon Farber
|   (c) 2005 - 2006 Invision Power Services
|   ========================================
+---------------------------------------------------------------------------
|
|   > Downloads Statistics
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_stats {

	var $ipsclass;
	var $traffic;
	var $limit		= 50;
	var $type;
	var $img_dir	= '1'; //Change if you want to pull images from a different style_images directory
	var $text		= array( 'fid' => "File", 'os' => "Operating System", 'browsers' => "Browser", 'ip' => "IP Address", 'time' => "Date Period", 'country' => "Country" );
	var $acceptable = array( 'browsers', 'ip', 'os', 'country', 'fid', 'time' );
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';	

	function auto_run()
	{
		$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':stats' );
		
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=stats', 'IP.Downloads Statistics' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=stats";
		$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_idm_queries.php', 'sql_idm_queries' );
		
		require_once( DL_PATH.'lib/lib_traffic.php' );
		$this->traffic = new lib_traffic();
		$this->traffic->ipsclass =& $this->ipsclass;
		$this->traffic->load_libraries();
		
		if( $this->ipsclass->input['code'] == "report" )
		{
			$this->run_report();
			return;
		}
		
		$this->limit = $this->ipsclass->input['limit'] ? intval($this->ipsclass->input['limit']) : 10;
		$this->type	 = $this->ipsclass->input['type'] == 'bw' ? "SUM(dsize) as num" : "COUNT(*) as num";

		if( $this->ipsclass->input['pieimg'] )
		{
			$this->get_pie_image();
			exit;
		}
		else if( $this->ipsclass->input['barimg'] )
		{
			$this->get_bar_image();
			exit;
		}
		
		$groupby = in_array( $this->ipsclass->input['groupby'], $this->acceptable ) ? $this->ipsclass->input['groupby'] : 'browsers';
		
		$selected = array();
		$selected[$groupby] = "selected='selected' ";
		
		$selected_t = array();
		$selected_t[$this->ipsclass->input['type']] = "selected='selected' ";
		
		$header_bar = "<form action='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}' method='post'>
						Show <select name='type' style='vertical-align:middle;'>
						<option {$selected_t['dl']}value='dl'>Download</option>
						<option {$selected_t['bw']}value='bw'>Bandwidth</option>
						</select>
						stats, grouping results by 
						<select name='groupby' style='vertical-align:middle;'>
						<option {$selected['browsers']}value='browsers'>Browser</option>
						<option {$selected['country']}value='country'>Country</option>
						<option {$selected['fid']}value='fid'>File</option>
						<option {$selected['os']}value='os'>Operating System</option>
						<option {$selected['ip']}value='ip'>IP Address</option>
						<option {$selected['time']}value='time'>Month</option>
						</select> and limiting results to top <input type='text' size='3' value='{$this->limit}' name='limit' />&nbsp;<input type='submit' value='Update' style='vertical-align:middle;' /></form>";
		
		$this->ipsclass->adskin->td_header[] = array( $header_bar   	, "100%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Statistics" );
		
		$this->ipsclass->DB->cache_add_query( 'acp_get_stats', array( 'groupby' => 'd'.$groupby, 'limit' => $this->limit, 'type' => $this->type ), 'sql_idm_queries'	);
		$outer = $this->ipsclass->DB->cache_exec_query();			

		$results = array();
		$i = 1;
		
		while( $row = $this->ipsclass->DB->fetch_row($outer) )
		{
			if( $row['num'] > 0 )
			{
				$results[ $row['num'].$i ] = $row;
				$i++;
			}
		}
		
		$totals = array_keys($results);
		$total  = 0;
		$cnt = 1;
		if( count($totals) )
		{
			foreach( $totals as $k => $v )
			{
				$total += substr( $v, 0, -(strlen($cnt)) );
				$cnt++;
			}
		}
		
		$table = "<table cellpadding='0' cellspacing='1' border='0' width='100%'>\n";
		
		if( count($results) > 0 )
		{
			$cnt = 1;
			foreach( $results as $k => $row )
			{
				$pip = "";
				$img = "";
				
				if( $groupby == 'browsers' OR $groupby == 'os' OR $groupby == 'country' )
				{
					$data = $this->traffic->return_stat_data( $row );
					
					if( $groupby == 'country' )
					{
						$imgfile = 'ext_'.$data['stat_country'].'.png';
					}
					else
					{
						$imgfile = $this->traffic->get_item_image( $groupby, $data["stat_{$groupby}_key"] );
					}
					
					$img = "<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/".$imgfile."' border='0' alt='Img' />";
					
					if( $groupby == 'country' )
					{
						$text = $this->traffic->get_item_image( 'countrylang', $data['stat_country'] );
					}
					else
					{
						$text = $data['stat_'.$groupby];
					}
				}
				else
				{
					$img = "";
					if( $groupby == 'fid' )
					{
						if( $row['file_name'] == '' )
						{
							$text = "[This file was deleted]";
						}
						else
						{
							$text = "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['dfid']}' target='_blank'>{$row['file_name']}</a>";
						}
					}
					else
					{
						$text = $row['d'.$groupby];
					}
				}
				
				if( $total > 0 )
				{
					$width = substr( $k, 0, -(strlen($cnt)) ) == 0 ? 0 : substr($k,0,-(strlen($cnt)))/$total*100;
					//$width = sprintf( '%.2f' , $width );
					$width = $width > 0 ? intval($width * 2) : 0;
					$pip = "<img src='{$this->ipsclass->skin_acp_url}/images/bar_left.gif' border='0' height='11' alt='Img' /><img src='{$this->ipsclass->skin_acp_url}/images/bar.gif' border='0' width='{$width}' height='11' alt='Img' /><img src='{$this->ipsclass->skin_acp_url}/images/bar_right.gif' border='0' height='11' alt='Img' />";
				}
				
				if( $this->ipsclass->input['type'] == 'bw' )
				{
					$row['num'] = $this->ipsclass->size_format($row['num']);
				}
				
				$table .= "<tr><td align='center' width='5%'>{$img}</td><td align='left' width='20%' nowrap='nowrap'>{$text}</td><td align='left'>{$pip} [ {$row['num']} ]</td></tr>\n";
				$cnt++;
			}
		}
		
		$table .= "</table>\n";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $table ) );				
		
		if( $cnt > 1 )
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<center><img src='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&pieimg={$groupby}&limit={$this->limit}&type={$this->ipsclass->input['type']}' border='0' alt='Pie Chart' /></center>" ) );
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<center><img src='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&barimg={$groupby}&limit={$this->limit}&type={$this->ipsclass->input['type']}' border='0' alt='Bar Chart' /></center>" ) );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		

		$this->ipsclass->html .= "<script type='text/javascript' src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
									<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:210px;display:none;z-index:1'></div>";

		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , "report"  ),
												                 			 2 => array( 'act'    , 'downloads'     ),
												                 			 3 => array( 'section', $this->ipsclass->section_code ),
												                 			 4 => array( 'req'	  , 'stats'	),
									                    ), "", "", "runReport"     );

		$this->ipsclass->adskin->td_header[] = array( '&nbsp;'   	, "60%" );
		$this->ipsclass->adskin->td_header[] = array( '&nbsp;' 		, "40%" );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Run Reports" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Report</b><br /><span class='forumdesc'>Type in a file name or part of a file name to run a report on the file</span>",
																				$this->ipsclass->adskin->form_input( "file", $this->ipsclass->input['file'] )
																	)	 	);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Member Report</b><br /><span class='forumdesc'>Type in a member (display) name to run a report on the member</span>",
																				"<input type='text' id='member' name='member' value='{$this->ipsclass->input['member']}' autocomplete='off' style='width:210px;' class='textinput' />"
																	)	 	);
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( "Run Report" );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= "<script type='text/javascript'>
									init_js( 'runReport', 'member', 'get-member-names');
									setTimeout( 'main_loop()', 10 );
								  </script>";


		$this->ipsclass->adskin->td_header[] = array( 'File Name'   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( 'File Submitter' 	, "20%" );
		$this->ipsclass->adskin->td_header[] = array( 'Submitted On'   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( 'Downloads'   	, "20%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Top 10 Downloaded Files" );
		
		$this->ipsclass->DB->simple_construct( array( 'select' 	=> 'f.*',
												 'from'   	=> array('downloads_files' => 'f'),
									             'add_join'	=> array( 0 => array(
									             								  'select' => 'm.members_display_name',
																                  'from'   => array( 'members' => 'm' ),
																                  'where'  => "f.file_submitter=m.id",
																                  'type'   => 'left'
																	)			),
												 'where'	=> 'f.file_open=1',
												 'order'	=> 'f.file_downloads DESC',
												 'limit'	=> array(0,10)	)	);
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$user_link	= $r['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['file_submitter']}' target='_blank'>{$r['members_display_name']}</a>" : "Guest";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$r['file_id']}' target='_blank'>{$r['file_name']}</a>",
																				 $user_link,
																				 $this->ipsclass->get_date( $r['file_submitted'], 'SHORT' ),
																				 $r['file_downloads']
																		)	 	);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->adskin->td_header[] = array( 'File Name'   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( 'File Submitter' 	, "20%" );
		$this->ipsclass->adskin->td_header[] = array( 'Submitted On'   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( 'Views'   		, "20%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Top 10 Viewed Files" );
		
		$this->ipsclass->DB->simple_construct( array( 'select' 	=> 'f.*',
												 'from'   	=> array('downloads_files' => 'f'),
									             'add_join'	=> array( 0 => array(
									             								  'select' => 'm.members_display_name',
																                  'from'   => array( 'members' => 'm' ),
																                  'where'  => "f.file_submitter=m.id",
																                  'type'   => 'left'
																	)			),
												 'where'	=> 'f.file_open=1',
												 'order'	=> 'f.file_views DESC',
												 'limit'	=> array(0,10)	)	);
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$user_link	= $r['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['file_submitter']}' target='_blank'>{$r['members_display_name']}</a>" : "Guest";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$r['file_id']}' target='_blank'>{$r['file_name']}</a>",
																				 $user_link,
																				 $this->ipsclass->get_date( $r['file_submitted'], 'SHORT' ),
																				 $r['file_views']
																		)	 	);
		}
		
		$outer = "";

		if( $this->ipsclass->vars['sql_driver'] != 'mysql' )
		{
			$this->ipsclass->DB->cache_add_query( 'get_top_ten_submitters', array(), 'sql_idm_queries'	);
			$outer = $this->ipsclass->DB->cache_exec_query();		
		}
		else
		{
			$this->ipsclass->DB->sql_get_version();
			
			$version = $this->ipsclass->DB->true_version ? substr( $this->ipsclass->DB->true_version, 0, 3 ) : 3;
			
			if( $version >= '4.1' )
			{
				$this->ipsclass->DB->allow_sub_select = 1;
				
				$this->ipsclass->DB->cache_add_query( 'get_top_ten_submitters', array(), 'sql_idm_queries'	);
				$outer = $this->ipsclass->DB->cache_exec_query();
			}
			else
			{
				$this->ipsclass->DB->cache_add_query( 'get_top_ten_submitters_40_first', array(), 'sql_idm_queries'	);
				$first = $this->ipsclass->DB->cache_exec_query();
				
				$unique_authors = array();
				$ids = array();
				
				while( $submitters = $this->ipsclass->DB->fetch_row($first) )
				{
					$unique_authors[$submitters['file_submitter']] = $submitters;
					
					$ids[] = $submitters['last_id'];
				}
				
				if( count( $ids ) )
				{
					$to_second = array( 'ids_to_pull' => implode( ",", $ids ) );
					
					$this->ipsclass->DB->cache_add_query( 'get_top_ten_submitters_40_second', $to_second, 'sql_idm_queries'	);
					$outer = $this->ipsclass->DB->cache_exec_query();
				}
			}
		}		

		if( $outer )
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
			
			$this->ipsclass->adskin->td_header[] = array( 'Member'   		, "20%" );
			$this->ipsclass->adskin->td_header[] = array( 'Submissions' 	, "10%" );
			$this->ipsclass->adskin->td_header[] = array( 'Last Submission' , "50%" );
			$this->ipsclass->adskin->td_header[] = array( 'Last Activity'  	, "20%" );
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Top 10 Submitters" );
					
			while( $r = $this->ipsclass->DB->fetch_row( $outer ) )
			{
				if( $version < '4.1' )
				{
					$r['submissions'] = $unique_authors[$r['id']]['submissions'];
				}
				
				$user_link	= $r['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['file_submitter']}' target='_blank'>{$r['members_display_name']}</a>" : "Guest";
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $user_link,
																					 $r['submissions'],
																					 "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$r['file_id']}' target='_blank'>{$r['file_name']}</a> (Submitted on ".$this->ipsclass->get_date( $r['file_submitted'], 'TINY' ).")",
																					 $this->ipsclass->get_date( $r['last_activity'], 'TINY' )
																			)	 	);
			}
	
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		$this->ipsclass->adskin->td_header[] = array( 'Member'   		, "20%" );
		$this->ipsclass->adskin->td_header[] = array( 'Downloads' 		, "10%" );
		$this->ipsclass->adskin->td_header[] = array( 'Last Download' 	, "50%" );
		$this->ipsclass->adskin->td_header[] = array( 'Last Activity'  	, "20%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Top 10 Downloaders" );
		
		if( $this->ipsclass->vars['sql_driver'] != 'mysql' )
		{
			$this->ipsclass->DB->cache_add_query( 'get_top_ten_downloaders', array(), 'sql_idm_queries'	);
			$outer = $this->ipsclass->DB->cache_exec_query();	
		}
		else
		{
			$this->ipsclass->DB->sql_get_version();
			
			$version = $this->ipsclass->DB->true_version ? substr( $this->ipsclass->DB->true_version, 0, 3 ) : 3;
					
			if( $version >= '4.1' )
			{
				$this->ipsclass->DB->allow_sub_select = 1;
				
				$this->ipsclass->DB->cache_add_query( 'get_top_ten_downloaders', array(), 'sql_idm_queries'	);
				$outer = $this->ipsclass->DB->cache_exec_query();
			}
			else
			{
				$this->ipsclass->DB->cache_add_query( 'get_top_ten_downloaders_40_first', array(), 'sql_idm_queries'	);
				$first = $this->ipsclass->DB->cache_exec_query();
				
				$unique_dlers = array();
				$ids = array();
				
				while( $downloaders = $this->ipsclass->DB->fetch_row($first) )
				{
					$unique_dlers[$downloaders['dmid']] = $downloaders;
					
					$ids[] = $downloaders['the_id'];
				}
				
				if( count( $ids ) )
				{
					$to_second = array( 'ids_to_pull' => implode( ",", $ids ) );
					
					$this->ipsclass->DB->cache_add_query( 'get_top_ten_downloaders_40_second', $to_second, 'sql_idm_queries'	);
					$outer = $this->ipsclass->DB->cache_exec_query();
				}
			}
		}				
		
		while( $r = $this->ipsclass->DB->fetch_row($outer) )
		{
			if( $version < '4.1' )
			{
				$r['downloads'] = $unique_dlers[$r['id']]['downloads'];
			}
			
			$user_link	= $r['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$r['id']}' target='_blank'>{$r['members_display_name']}</a>" : "Guest";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $user_link,
																				 $r['downloads'],
																				 "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$r['file_id']}' target='_blank'>{$r['file_name']}</a> (Downloaded on ".$this->ipsclass->get_date( $r['dtime'], 'TINY' ).")",
																				 $this->ipsclass->get_date( $r['last_activity'], 'TINY' )
																		)	 	);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();				
		
		$this->ipsclass->admin->output();
	}
	
	
	function run_report()
	{
		if( $this->ipsclass->input['viewfile'] )
		{
			$this->gen_file_report( $this->ipsclass->input['viewfile'] );
			return;
		}
		
		if( $this->ipsclass->input['viewmember'] )
		{
			$this->gen_member_report( $this->ipsclass->input['viewmember'] );
			return;
		}
		
		if( $this->ipsclass->input['file'] )
		{
			$this->ipsclass->admin->page_title 	= "Running file report for file: {$this->ipsclass->input['file']}";
			$this->ipsclass->admin->page_detail = "Please double check the information before submitting the form.";
			
			// Check for file existence
			
			$this->ipsclass->DB->build_query( array( 'select'		=> 'f.*',
														'from'		=> array( 'downloads_files' => 'f' ),
														'where'		=> "LOWER(f.file_name) LIKE '%".strtolower($this->ipsclass->input['file'])."%'",
														'add_join'	=> array(
																			array( 'select'		=> 'm.members_display_name',
																					'from'		=> array( 'members' => 'm' ),
																					'where'		=> 'm.id=f.file_submitter',
																					'type'		=> 'left'
																				)
																			)
											)		);

			$outer = $this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows($outer) < 1 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "0 Results" );
				
			 	$rows = $this->ipsclass->adskin->add_td_row( array(
																"No results found, please try to broaden your search",
															)		);
															
				$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
				$this->ipsclass->admin->output();
			}
			else if( $this->ipsclass->DB->get_num_rows($outer) > 1 )
			{
				$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "30%" );
				$this->ipsclass->adskin->td_header[] = array( "Downloads" 			, "10%" );
				$this->ipsclass->adskin->td_header[] = array( "Views" 				, "10%" );
				$this->ipsclass->adskin->td_header[] = array( "Submitted By"		, "25%" );
				$this->ipsclass->adskin->td_header[] = array( "Submitted" 			, "25%" );
		
			 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $this->ipsclass->DB->get_num_rows($outer)." Results" );
	
			 	while( $i = $this->ipsclass->DB->fetch_row( $outer ) )
			 	{
				 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
										 										"<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['file_id']}'><b>{$i['file_name']}</b></a>",
																				$i['file_downloads'],
																				$i['file_views'],
																				"<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$i['file_submitter']}'><b>{$i['members_display_name']}</b></a>",
																				$this->ipsclass->get_date( $i['file_submitted'], 'SHORT' )
				 																)		);
			 	}
	
				$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
				
			 	$this->ipsclass->admin->output();
			}
			else
			{
			 	$i = $this->ipsclass->DB->fetch_row($outer);
			 	$this->gen_file_report( $i['file_id'] );
			}
				
			return;							
		}
		else if( $this->ipsclass->input['member'] )
		{
			$this->ipsclass->admin->page_title 	= "Running member report for member: {$this->ipsclass->input['member']}";
			$this->ipsclass->admin->page_detail = "Please double check the information before submitting the form.";

			// Check for member existence
			
			$this->ipsclass->DB->build_query( array( 'select'	=> 'id, members_display_name',
														'from'	=> 'members',
														'where'	=> "members_l_display_name LIKE '%".strtolower($this->ipsclass->input['member'])."%'" 
											)		);

			$outer = $this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows($outer) < 1 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "0 Results" );
				
			 	$rows = $this->ipsclass->adskin->add_td_row( array(
																"No results found, please try to broaden your search",
															)		);
															
				$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
				$this->ipsclass->admin->output();
			}
			else if( $this->ipsclass->DB->get_num_rows($outer) > 1 )
			{
				$this->ipsclass->adskin->td_header[] = array( "Member Name"    		, "50%" );
				$this->ipsclass->adskin->td_header[] = array( "Downloads" 			, "25%" );
				$this->ipsclass->adskin->td_header[] = array( "Submissions" 		, "25%" );
		
			 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $this->ipsclass->DB->get_num_rows($outer)." Results" );
	
			 	while( $i = $this->ipsclass->DB->fetch_row( $outer ) )
			 	{
				 	$downloads 	= $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as dls', 'from' => 'downloads_downloads', 'where' => "dmid={$i['id']}" ) );
				 	$subs 		= $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(*) as files', 'from' => 'downloads_files', 'where' => "file_submitter={$i['id']}" ) );
				 	
				 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
										 										"<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$i['id']}'><b>{$i['members_display_name']}</b></a>",
																				$downloads['dls'],
																				$subs['files'],
				 																)		);
			 	}
	
				$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
				
			 	$this->ipsclass->admin->output();
			}
			else
			{
			 	$i = $this->ipsclass->DB->fetch_row($outer);
			 	$this->gen_member_report( $i['id'] );
			}
				
			return;	
		}
		else
		{
			$this->ipsclass->admin->error("No file or member found to run report against");
		}
	}
	
	function get_pie_image()
	{
		$groupby = in_array( $this->ipsclass->input['pieimg'], $this->acceptable ) ? $this->ipsclass->input['pieimg'] : 'browsers';
		
		$this->ipsclass->DB->cache_add_query( 'acp_get_stats', array( 'groupby' => 'd'.$groupby, 'limit' => $this->limit, 'type' => $this->type ), 'sql_idm_queries'	);
		$outer = $this->ipsclass->DB->cache_exec_query();
		
		$num = 0;	
		$records = array();
		
		while( $row = $this->ipsclass->DB->fetch_row($outer) )
		{
			if( $row['num'] > 0 )
			{
				if( $groupby == 'browsers' OR $groupby == 'os' OR $groupby == 'country' )
				{
					$data = $this->traffic->return_stat_data( $row );
					$key = $data['stat_'.$groupby];
				}
				else
				{
					if( $groupby == 'fid' )
					{
						$key = $row['file_name'] ? $row['file_name'] : "[This file was deleted]";
					}
					else
					{
						$key = $row['d'.$groupby];
					}
				}
				
				$records[$key] = $row['num'];
				$num++;
			}
		}
		
		if( $this->ipsclass->input['type'] == 'bw' )
		{
			$title = "Bandwidth Usage";
		}
		else
		{
			$title = "Downloads";
		}
		
		require_once( DL_PATH.'lib/class_chart.php' );
		$pie = new class_chart();
		$pie->chart_init( array( 'width' => 650, 'height' => $num < 14 ? '' : $num*25, 'title' => "{$title} by {$this->text[$groupby]}", 'font' => DL_PATH.'lib/streetcornerbold.ttf' ) );
		$pie->piechart_draw( $records );
	}
	
	function get_bar_image()
	{
		$groupby = in_array( $this->ipsclass->input['barimg'], $this->acceptable ) ? $this->ipsclass->input['barimg'] : 'browsers';
		
		$this->ipsclass->DB->cache_add_query( 'acp_get_stats', array( 'groupby' => 'd'.$groupby, 'limit' => $this->limit, 'type' => $this->type ), 'sql_idm_queries'	);
		$outer = $this->ipsclass->DB->cache_exec_query();
		
		$records = array();
		$num = 0;
		
		while( $row = $this->ipsclass->DB->fetch_row($outer) )
		{
			if( $row['num'] > 0 )
			{
				if( $groupby == 'browsers' OR $groupby == 'os' OR $groupby == 'country' )
				{
					$data = $this->traffic->return_stat_data( $row );
					$key = $data['stat_'.$groupby];
				}
				else
				{
					if( $groupby == 'fid' )
					{
						$key = $row['file_name'] ? $row['file_name'] : "[This file was deleted]";
					}
					else
					{
						$key = $row['d'.$groupby];
					}
				}
				
				if( $this->ipsclass->input['type'] == 'bw' )
				{
					$row['num'] = $this->ipsclass->size_format($row['num']);
				}				
				
				$records[$key] = $row['num'];
				$num++;
			}
		}

		if( $this->ipsclass->input['type'] == 'bw' )
		{
			$title = "Bandwidth Usage";
		}
		else
		{
			$title = "Downloads";
		}		
		
		require_once( DL_PATH.'lib/class_chart.php' );
		$pie = new class_chart();
		$pie->chart_init( array( 'width' => 650, 'height' => $num < 14 ? '400' : $num*25, 'title' => "{$title} by {$this->text[$groupby]}", 'font' => DL_PATH.'lib/streetcornerbold.ttf' ) );
		$pie->barchart_draw( $records );
	}	
	
	
	function gen_member_report( $mid )
	{
		$mid = intval( $mid );
		
		$member = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'id, members_display_name',
																	'from'		=> 'members',
																	'where' 	=> "id={$mid}" 
															) 		);

		if( !$member['id'] )
		{
			$this->ipsclass->admin->error( "The member could not be found." );
		}
				
		// Let's grab some overall total stuff first
		$stats = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'SUM( file_size ) as total_size, AVG( file_size ) as total_avg_size, COUNT( file_size ) as total_uploads',
													 				'from'	=> 'downloads_files' 
														) 		);

		$stats = array_merge( $stats,
								$this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'SUM( dsize ) as total_transfer, COUNT( dsize ) as total_viewed',
																					'from'	=> 'downloads_downloads' 
																		) 		)
							);

		$stats = array_merge( $stats,
								$this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'SUM( file_size ) as user_size, AVG( file_size ) as user_avg_size, COUNT( file_size ) as user_uploads',
																					'from'	=> 'downloads_files',
																					'where' => "file_submitter={$mid}" 
																		) 		)
							);

		$stats = array_merge( $stats,
								$this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'SUM( dsize ) as user_transfer, COUNT( dsize ) as user_viewed',
																					'from'	=> 'downloads_downloads',
																					'where' => "dmid={$mid}" 
																		) 		)
							);
		
		// Page Information
		$this->ipsclass->admin->page_title	= "Member Report for {$member['members_display_name']}";
		$this->ipsclass->admin->page_detail = "Here you will find detailed statistcs related to diskspace usage and bandwidth usage";

		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"	, "25%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"	, "25%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"	, "25%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"	, "25%" );

		//+-----------------------------------------------------------

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "IP.Downloads Usage Overview" );

		$dp_percent = $stats['total_size'] 		? ( round( $stats['user_size'] / $stats['total_size'], 2 ) * 100 ).'%' 			: '0%';
		$up_percent = $stats['total_uploads'] 	? ( round( $stats['user_uploads'] / $stats['total_uploads'], 2 ) * 100 ).'%' 	: '0%';

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'Submissions Overview', 'left', 'tablerow4' );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Diskspace for {$member['members_display_name']}</b>", 
																				$this->ipsclass->size_format( $stats['user_size'] ),
																				"<b>Percent of all diskspace used</b>", 
																				$dp_percent,
								 									)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Uploads for {$member['members_display_name']}</b>", 
																				$stats['user_uploads'],
																				"<b>Percent of all uploads</b>", 
																				$up_percent,
								 									)	 	);
								 									
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Average File Size for {$member['members_display_name']}</b>", 
																				$this->ipsclass->size_format( $stats['user_avg_size'] ),
																				"<b>Average File Size of all users</b>", 
																				$this->ipsclass->size_format( $stats['total_avg_size'] ),
								 									)	 	);
								 									

		if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
		 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'Bandwidth Overview', 'left', 'tablerow4' );

		 	$tr_percent = $stats['total_transfer'] 	? ( round( $stats['user_transfer'] / $stats['total_transfer'], 2 ) * 100 ).'%' 	: '0%';
		 	$vi_percent = $stats['total_viewed']	? ( round( $stats['user_viewed'] / $stats['total_viewed'], 2 ) * 100 ).'%' 		: '0%';

		 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Transfer for {$member['members_display_name']}</b>", 
		 																			$this->ipsclass->size_format( $stats['user_transfer'] ),
																					"<b>Percent of all transfers</b>", 
																					$tr_percent,
									 									)	 	);

		 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Downloads</b>", 
		 																			$stats['user_viewed'],
																					"<b>Percent of all downloads</b>", 
																					$vi_percent,
									 									)	 	);

		 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		else
		{
		 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		
	 	$this->ipsclass->adskin->td_header[] = array( "File Name"				, "30%" );
	 	$this->ipsclass->adskin->td_header[] = array( "File Size"	 			, "10%" );
	 	$this->ipsclass->adskin->td_header[] = array( "Downloads"				, "10%" );
	 	$this->ipsclass->adskin->td_header[] = array( "% of Downloads"			, "15%" );
	 	$this->ipsclass->adskin->td_header[] = array( "Views"	 				, "7%" );
	 	$this->ipsclass->adskin->td_header[] = array( "Rating"					, "8%" );
	 	$this->ipsclass->adskin->td_header[] = array( "Broken"					, "10%" );
	 	$this->ipsclass->adskin->td_header[] = array( "Local"					, "10%" );


	 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "User Submissions" );

	 	$this->ipsclass->DB->build_query( array( 'select'		=> '*',
	 												'from'		=> 'downloads_files',
	 												'where'		=> "file_submitter={$mid}",
	 												'order'		=> 'file_submitted DESC',
	 									)		); 
	 	$outer = $this->ipsclass->DB->cache_exec_query();

	 	while( $i = $this->ipsclass->DB->fetch_row($outer) )
	 	{
		 	$dp_percent = $stats['total_viewed'] 	? round( $i['file_downloads'] / $stats['total_viewed'], 2 ) * 100 : 0;
		 	
		 	$broken = $i['file_broken'] ? "<img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' />" : "<img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' />";
		 	$local 	= $i['file_url'] 	? "<img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' />" : "<img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' />";
		 	
		 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['file_id']}' title='View file report'><b>{$i['file_name']}</b></a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['file_id']}' title='View file report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by file'></a>",
																					$this->ipsclass->size_format( $i['file_size'] ),
																					$i['file_downloads'],
																					$dp_percent . '%',
																					$i['file_views'],
																					$i['file_rating'],
																					$broken,
																					$local,
																		)		);
	 	}

	 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	 	
	 	if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
		 	$this->ipsclass->adskin->td_header[] = array( "File"					, "25%" );
		 	$this->ipsclass->adskin->td_header[] = array( "File Size"	 			, "8%" );
		 	$this->ipsclass->adskin->td_header[] = array( "User transfer %"			, "12%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Downloads"	 			, "8%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Browser"					, "15%" );
		 	$this->ipsclass->adskin->td_header[] = array( "OS"						, "15%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Country"					, "10%" );
		 	$this->ipsclass->adskin->td_header[] = array( "IP Address"				, "10%" );

		 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "User Downloads" );

		 	$this->ipsclass->DB->build_query( array( 'select'		=> 'd.*',
		 												'from'		=> array( 'downloads_downloads' => 'd' ),
		 												'where'		=> "dmid={$mid}",
		 												'order'		=> 'dtime DESC',
		 												'add_join'	=> array(
		 																	array( 'select'		=> 'f.file_name, f.file_downloads',
		 																			'from'		=> array( 'downloads_files' => 'f' ),
		 																			'where'		=> 'f.file_id=d.dfid',
		 																			'type'		=> 'left',
		 																		)
		 																	)
		 									)		); 
		 	$outer = $this->ipsclass->DB->cache_exec_query();

		 	while( $i = $this->ipsclass->DB->fetch_row($outer) )
		 	{
			 	$dp_percent = $stats['user_transfer'] 	? round( $i['dsize'] / $stats['user_transfer'], 2 ) * 100 : 0;
			 	
			 	$data 		= $this->traffic->return_stat_data( $i );
			 	
			 	$country_img	= 'ext_'.$data['stat_country'].'.png';
			 	$country_txt	= $this->traffic->get_item_image( 'countrylang', $data['stat_country'] );
			 	
			 	$browser_img	= $this->traffic->get_item_image( 'browsers', $data['stat_browser_key'] );
			 	$browser_txt	= $data['stat_browser'];
			 	
			 	$os_img			= $this->traffic->get_item_image( 'os', $data['stat_os_key'] );
			 	$os_txt			= $data['stat_os'];
				
			 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['dfid']}' title='View file report'><b>{$i['file_name']}</b></a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['dfid']}' title='View file report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by file'></a>",
																						$this->ipsclass->size_format( $i['dsize'] ),
																						$dp_percent . '%',
																						$i['file_downloads'],
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$browser_img}' border='0' alt='Img' /> (<i>{$browser_txt}</i>)",
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$os_img}' border='0' alt='Img' /> (<i>{$os_txt}</i>)",
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$country_img}' border='0' alt='Img' /> (<i>{$country_txt}</i>)",
																						$i['dip'],
																			)		);
		 	}

		 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	 	}
	 	

	 	$this->ipsclass->DB->build_query( array( 'select'		=> 'fav.*',
	 												'from'		=> array( 'downloads_favorites' => 'fav' ),
	 												'where'		=> "fmid={$mid}",
	 												'order'		=> 'fid ASC',
	 												'add_join'	=> array(
	 																	array( 'select'		=> 'f.file_size, f.file_name, f.file_downloads, f.file_views',
	 																			'from'		=> array( 'downloads_files' => 'f' ),
	 																			'where'		=> 'f.file_id=fav.ffid',
	 																			'type'		=> 'left',
	 																		)
	 																	)
	 									)		); 
	 	$outer = $this->ipsclass->DB->cache_exec_query();
	 	
	 	if( $this->ipsclass->DB->get_num_rows($outer) )
	 	{
		 	$this->ipsclass->adskin->td_header[] = array( "File"					, "30%" );
		 	$this->ipsclass->adskin->td_header[] = array( "File Size"	 			, "10%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Downloads"				, "10%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Last Updated"	 		, "20%" );
		 	$this->ipsclass->adskin->td_header[] = array( "User Downloads"			, "15%" );
		 	$this->ipsclass->adskin->td_header[] = array( "% Downloads"				, "15%" );
	
		 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "User Favorites" );
	
	
		 	while( $i = $this->ipsclass->DB->fetch_row($outer) )
		 	{
			 	$d_data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'COUNT(did) as num', 'from' => 'downloads_downloads', 'where' => "dmid={$mid} AND dfid={$i['ffid']}" ) );
			 	
			 	$dp_percent = $stats['user_viewed'] 	? round( $d_data['num'] / $stats['user_viewed'], 2 ) * 100 : 0;
				
			 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['ffid']}' title='View file report'><b>{$i['file_name']}</b></a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewfile={$i['ffid']}' title='View file report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by file'></a>",
																						$this->ipsclass->size_format( $i['file_size'] ),
																						$i['file_downloads'],
																						$this->ipsclass->get_date( $i['fupdated'], 'SHORT' ),
																						$d_data['num'],
																						$dp_percent . '%',
																			)		);
		 	}
	
		 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	 	}

		$this->ipsclass->admin->output();
	}
	
	
	function gen_file_report( $fid )
	{
		$fid = intval( $fid );

		if( $this->ipsclass->input['change'] == 1 )
		{
			$name = trim($this->ipsclass->input['member']);
			
			$member = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'id', 'from' => 'members', 'where' => "members_display_name='{$name}'" ) );
			
			if( $member['id'] )
			{
				$this->ipsclass->DB->do_update( 'downloads_files', array( 'file_submitter' => $member['id'] ), "file_id={$fid}" );
			}
		}
		
		$file = $this->ipsclass->DB->build_and_exec_query( array( 'select' 		=> 'f.*',
																	'from'		=> array( 'downloads_files' => 'f' ),
																	'where' 	=> "f.file_id={$fid}",
																	'add_join'	=> array(
																						array( 'select'		=> 'm.members_display_name',
																								'from'		=> array( 'members' => 'm' ),
																								'where'		=> 'm.id=f.file_submitter',
																								'type'		=> 'left'
																							),
																						array( 'select'		=> 'mm.members_display_name as app_name',
																								'from'		=> array( 'members' => 'mm' ),
																								'where'		=> 'mm.id=f.file_approver',
																								'type'		=> 'left'
																							),
																						array( 'select'		=> 'mime.mime_extension',
																								'from'		=> array( 'downloads_mime' => 'mime' ),
																								'where'		=> 'mime.mime_id=f.file_mime',
																								'type'		=> 'left'
																							),
																						array( 'select'		=> 'c.cname',
																								'from'		=> array( 'downloads_categories' => 'c' ),
																								'where'		=> 'c.cid=f.file_cat',
																								'type'		=> 'left'
																							),
																						)
															) 		);

		if( !$file['file_id'] )
		{
			$this->ipsclass->admin->error( "The file could not be found." );
		}
		
		if( $this->ipsclass->input['change'] == 1 AND $file['file_topicid'] > 0 )
		{
			$topic = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'topics', 'where' => 'tid=' . $file['file_topicid'] ) );
			
			if( $topic['tid'] )
			{
				$this->ipsclass->DB->do_update( 'posts', array( 'author_id' => $member['id'], 'author_name' => $file['members_display_name'] ), 'pid=' . $topic['topic_firstpost'] );
				
				$this->ipsclass->forums->forums_init();
				
				require_once( ROOT_PATH . 'sources/lib/func_mod.php' );
				$mod 			= new func_mod();
				$mod->ipsclass	=& $this->ipsclass;
				$mod->init( $this->ipsclass->forums->forum_by_id[ $topic['forum_id'] ], $topic );
				$mod->rebuild_topic( $topic['tid'], 1 );
			}
		}			
		
		// Page Information
		$this->ipsclass->admin->page_title	= "file Report for {$file['file_name']}";
		$this->ipsclass->admin->page_detail = "Here you will find detailed statistcs related to diskspace usage and bandwidth usage";

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File Overview" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'General Information', 'left', 'tablerow4', 4 );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Uploaded By</b>"	, 
																				"<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$file['file_submitter']}' title='View member report'>{$file['members_display_name']}</a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$file['file_submitter']}' title='View member report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by sender name'></a>",
																				"<b>Approved By</b>"	, 
																				$file['app_name'] ? "<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$file['file_approver']}' title='View member report'>{$file['app_name']}</a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$file['file_approver']}' title='View member report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by sender name'></a>" : '<i>N/A</i>',
								 									)	 	);
								 									
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Name</b>", 
																				$file['file_name'],
																				"<b>In Category</b>", 
																				$file['cname'],
								 									)	 	);
								 									
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Approved</b>"	, 
																				$file['file_open'] ? 'Yes' : 'No',
																				"<b>Broken</b>"	, 
																				$file['file_broken'] ? 'Yes' : 'No',
								 									)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Size</b>", 
																				$this->ipsclass->size_format( $file['file_size'] ),
																				"<b>File Type</b>", 
																				$file['mime_extension'],
								 									)	 	);


		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Submitted</b>", 
																				$this->ipsclass->get_date( $file['file_submitted'], 'LONG' ),
																				"<b>Last Updated</b>", 
																				$this->ipsclass->get_date( $file['file_updated'], 'LONG' ),
								 									)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Downloads</b>", 
																				$file['file_downloads'],
																				"<b>Views</b>", 
																				$file['file_views'],
								 									)	 	);

		$votes = unserialize( stripslashes( $file['file_votes'] ) );
		$total_votes = is_array($votes) ? count($votes) : 0;
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File Rating</b>", 
																				$file['file_rating'],
																				"<b>Number of Ratings</b>",
																				$total_votes,
								 									)	 	);

		$fav = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'COUNT(ffid) AS total',
																	'from'	=> 'downloads_favorites',
																	'where' => "ffid={$file['file_id']}" 
														) 		);

		$subs = unserialize( stripslashes( $file['file_sub_mems'] ) );
		$total_subs = is_array($subs) ? count($subs) : 0;
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Number of times saved as favorite</b>", 
																				intval($fav['total']),
																				"<b>Number of members subscribed</b>", 
																				intval($total_subs),
								 									)	 	);

		if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
		 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'Bandwidth and Downloads Overview', 'left', 'tablerow4' );

		 	$bandwidth = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'COUNT( * ) AS downloads, SUM( dsize ) AS transfer',
																			 'from'		=> 'downloads_downloads',
																			 'where' 	=> "dfid='{$file['file_id']}'" 
																) 		);

			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Downloads</b>"	, 
																				intval($bandwidth['downloads']),
																				"<b>Bandwidth Usage</b>" , 
																				$this->ipsclass->size_format( intval($bandwidth['transfer']) ),
								 										)	 	);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
				
		$this->ipsclass->html .= "<script type='text/javascript' src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
									<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:170px;display:none;z-index:1'></div>";
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , "report"  ),
														                 			 2 => array( 'act'    , 'downloads'     ),
														                 			 3 => array( 'section', $this->ipsclass->section_code ),
														                 			 4 => array( 'req'	  , 'stats'	),
														                 			 5 => array( 'change' , 1 ),
														                 			 6 => array( 'viewfile', $file['file_id'] ),
											                    ), "", "", "runReport"     );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Change File Owner" );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Change to..</b>",
																				"<input type='text' id='member' name='member' value='{$this->ipsclass->input['member']}' autocomplete='off' style='width:170px;' class='textinput' />"
																	)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( "Change the owner" );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= "<script type='text/javascript'>
									init_js( 'runReport', 'member', 'get-member-names');
									setTimeout( 'main_loop()', 10 );
								  </script>";

	 	if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
		 	$this->ipsclass->adskin->td_header[] = array( "Member"					, "30%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Date"	 				, "20%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Browser"					, "15%" );
		 	$this->ipsclass->adskin->td_header[] = array( "OS"						, "15%" );
		 	$this->ipsclass->adskin->td_header[] = array( "Country"					, "10%" );
		 	$this->ipsclass->adskin->td_header[] = array( "IP Address"				, "10%" );

		 	$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File Downloads" );

		 	$this->ipsclass->DB->build_query( array( 'select'		=> 'd.*',
		 												'from'		=> array( 'downloads_downloads' => 'd' ),
		 												'where'		=> "dfid={$fid}",
		 												'order'		=> 'dtime DESC',
		 												'add_join'	=> array(
		 																	array( 'select'		=> 'm.members_display_name',
		 																			'from'		=> array( 'members' => 'm' ),
		 																			'where'		=> 'm.id=d.dmid',
		 																			'type'		=> 'left',
		 																		)
		 																	)
		 									)		); 
		 	$outer = $this->ipsclass->DB->cache_exec_query();

		 	while( $i = $this->ipsclass->DB->fetch_row($outer) )
		 	{
			 	$dp_percent = $stats['user_transfer'] 	? round( $i['dsize'] / $stats['user_transfer'], 2 ) * 100 : 0;
			 	
			 	$data 		= $this->traffic->return_stat_data( $i );
			 	
			 	$country_img	= 'ext_'.$data['stat_country'].'.png';
			 	$country_txt	= $this->traffic->get_item_image( 'countrylang', $data['stat_country'] );
			 	
			 	$browser_img	= $this->traffic->get_item_image( 'browsers', $data['stat_browser_key'] );
			 	$browser_txt	= $data['stat_browser'];
			 	
			 	$os_img			= $this->traffic->get_item_image( 'os', $data['stat_os_key'] );
			 	$os_txt			= $data['stat_os'];
				
			 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$i['dmid']}' title='View member report'><b>{$i['members_display_name']}</b></a> <a href='{$this->ipsclass->base_url}&section=components&act=downloads&req=stats&code=report&viewmember={$i['dmid']}' title='View member report'><img src='{$this->ipsclass->adskin->img_url}/images/acp_search.gif' border='0' alt='..by member'></a>",
																						$this->ipsclass->get_date( $i['dtime'], 'SHORT' ),
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$browser_img}' border='0' alt='Img' /> (<i>{$browser_txt}</i>)",
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$os_img}' border='0' alt='Img' /> (<i>{$os_txt}</i>)",
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->img_dir}/folder_traffic_images/{$country_img}' border='0' alt='Img' /> (<i>{$country_txt}</i>)",
																						$i['dip'],
																			)		);
		 	}

		 	$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	 	}
	 	
		$this->ipsclass->admin->output();
	}
	
}

?>