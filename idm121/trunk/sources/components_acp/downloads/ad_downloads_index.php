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
|   > Downloads Overview
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


class ad_downloads_index {

	var $base_url;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';	

	function auto_run()
	{
		$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':idx' );
		
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=overview', 'IP.Downloads Overview' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=overview";
		$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_idm_queries.php', 'sql_idm_queries' );

		$table_one = $this->ipsclass->adskin->start_table( "Overview" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"    		, "50%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          , "50%" );
		
		$disk = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'SUM(file_size) as total_size, SUM(file_downloads) as total_downloads, SUM(file_views) as total_views, COUNT(*) as total_files',
																'from'	=> "downloads_files"	)	);
																
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Diskspace</b>", $this->ipsclass->size_format( $disk['total_size'] ? $disk['total_size'] : 0 ) )		);
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Files</b>", ( $disk['total_files'] ? $disk['total_files'] : 0 ) )		);
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Downloads</b>", ( $disk['total_downloads'] ? $disk['total_downloads'] : 0 ) 	)		);
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Views</b>", ( $disk['total_views'] ? $disk['total_views'] : 0 ) 	)		);
		
		if( $this->ipsclass->vars['idm_logalldownloads'] )
		{
			$bw = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'SUM(dsize) as total_bw',	'from'	=> "downloads_downloads"	)	);
			
			$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Bandwidth Used</b>", $this->ipsclass->size_format( $bw['total_bw'] ? $bw['total_bw'] : 0 ) 	)		);
			
			$mo = date( "n" );
			$st_time = mktime( 0, 0, 0, $mo, 1, date("Y") );
			
			$cur_bw = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'SUM(dsize) as this_bw',	'from'	=> "downloads_downloads", 'where' => "dtime>{$st_time}"	)	);
			$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Current Month Bandwidth</b>", $this->ipsclass->size_format( $cur_bw['this_bw'] ? $cur_bw['this_bw'] : 0 ) 	)		);
		}
		else
		{
			$bw = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'SUM(file_downloads*file_size) as total_bw',	'from'	=> "downloads_files"	)	);
			
			$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Total Bandwidth Used</b>", $this->ipsclass->size_format( $bw['total_bw'] ? $bw['total_bw'] : 0 ) 	)		);
		}
		
		$this->ipsclass->DB->build_query( array( 'select' => 'file_id, file_name, file_size', 
												 'from' => 'downloads_files', 'order' => 'file_size DESC', 'limit' => array(0,1) )	);
		$this->ipsclass->DB->exec_query();
		
		$largest = $this->ipsclass->DB->fetch_row();
		
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Largest File</b> (".$this->ipsclass->size_format( $largest['file_size'] ? $largest['file_size'] : 0 ).")", $largest['file_id'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$largest['file_id']}' target='_blank'>{$largest['file_name']}</a>" : "<i>No files found</i>"	)		);

		$this->ipsclass->DB->build_query( array( 'select' => 'file_id, file_name, file_views', 
												 'from' => 'downloads_files', 'order' => 'file_views DESC', 'limit' => array(0,1) )	);
		$this->ipsclass->DB->exec_query();
		
		$views = $this->ipsclass->DB->fetch_row();
		
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Most Views</b> (".($views['file_views'] ? $views['file_views'] : 0).")", $views['file_id'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$views['file_id']}' target='_blank'>{$views['file_name']}</a>" : "<i>No files found</i>"	)		);

		$this->ipsclass->DB->build_query( array( 'select' => 'file_id, file_name, file_downloads', 
												 'from' => 'downloads_files', 'order' => 'file_downloads DESC', 'limit' => array(0,1) )	);
		$this->ipsclass->DB->exec_query();
		
		$dls = $this->ipsclass->DB->fetch_row();
		
		$table_one .= $this->ipsclass->adskin->add_td_row( array( "<b>Most Downloaded</b> (".($dls['file_downloads'] ? $dls['file_downloads'] : 0).")", $dls['file_id'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$dls['file_id']}' target='_blank'>{$dls['file_name']}</a>" : "<i>No files found</i>"	)		);
		
		$table_one .= $this->ipsclass->adskin->end_table();		

		$table_two = $this->ipsclass->adskin->start_table( "Information" );
		
		if( $this->ipsclass->vars['idm_online'] == 1 )
		{
			$image = "<img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' />";
		}
		else
		{
			$image = "<img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' />";
		}
		
		$table_two .= $this->ipsclass->adskin->add_td_row( array( "<b>System Online?</b>", "<center>".$image."</center>" )		);
		
		$table_two .= $this->ipsclass->adskin->add_td_row( array( "<b>Version?</b>", "<center>".DL_VERSION."</center>" 	)		);
		
		
		$table_two .= $this->ipsclass->adskin->end_table();
		
		$table_tre .= "<script type='text/javascript' src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
									<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:170px;display:none;z-index:1'></div>";

		$table_tre .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , "report"  ),
												                 			 2 => array( 'act'    , 'downloads'     ),
												                 			 3 => array( 'section', $this->ipsclass->section_code ),
												                 			 4 => array( 'req'	  , 'stats'	),
									                    ), "", "", "runReport"     );

		$this->ipsclass->adskin->td_header[] = array( '&nbsp;'   	, "60%" );
		$this->ipsclass->adskin->td_header[] = array( '&nbsp;' 		, "40%" );

		$table_tre .= $this->ipsclass->adskin->start_table( "Run Reports" );
		
		$table_tre .= $this->ipsclass->adskin->add_td_row( array( "<b>File Report</b><br />",
																				$this->ipsclass->adskin->form_input( "file", $this->ipsclass->input['file'] )
																	)	 	);
																	
		$table_tre .= $this->ipsclass->adskin->add_td_row( array( "<b>Member Report</b>",
																				"<input type='text' id='member' name='member' value='{$this->ipsclass->input['member']}' autocomplete='off' style='width:170px;' class='textinput' />"
																	)	 	);
		
		$table_tre .= $this->ipsclass->adskin->end_form( "Run Report" );
		$table_tre .= $this->ipsclass->adskin->end_table();
		
		$table_tre .= "<script type='text/javascript'>
									init_js( 'runReport', 'member', 'get-member-names');
									setTimeout( 'main_loop()', 10 );
								  </script>";
		
		$this->ipsclass->html .= "<table cellpadding='0' cellspacing='0' border='0' width='100%'><tr><td valign='top' width='50%' style='padding-right:2px;'>{$table_one}</td><td valign='top' width='50%' style='padding-left:2px;'>{$table_two}<br />{$table_tre}</td></tr></table>";
		
		$this->ipsclass->adskin->td_header[] = array( "File Name"   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "File Author"    	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Submitted"		, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Approved?"    	, "10%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Last 5 Files Submitted" );
		
		$this->ipsclass->DB->build_query( array(	'select'	=> 'f.file_id, f.file_open, f.file_name, f.file_submitter, f.file_submitted',
													'from'		=> array( 'downloads_files' => 'f' ),
													'order'		=> 'f.file_submitted DESC',
													'limit'		=> array( 0, 5 ),
													'add_join'	=> array(
																		array( 'type'		=> 'left',
																				'select'	=> 'm.members_display_name',
																				'from'		=> array( 'members' => 'm' ),
																				'where'		=> 'm.id=f.file_submitter'
																			)
																		)
										)		);
													
		$this->ipsclass->DB->exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row() )
		{
			$user_link	= $row['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$row['file_submitter']}' target='_blank'>{$row['members_display_name']}</a>" : "Guest";
			$img 		= $row['file_open'] ? 'aff_tick.png' : 'aff_cross.png';
			$date 		= $this->ipsclass->get_date( $row['file_submitted'], 'SHORT' );
		
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}'>{$row['file_name']}</a>",
																				 $user_link,
																				 $date,
																				 "<center><img src='{$this->ipsclass->skin_acp_url}/images/{$img}' border='0' alt='X' /></center>" 	)		);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->adskin->td_header[] = array( "File Name"   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "File Author"    	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Submitted"		, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Approve?"    	, "10%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Files Pending Approval" );
		
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.file_id, f.file_open, f.file_name, f.file_submitter, f.file_submitted', 
												 'from' 	=> array( 'downloads_files' => 'f' ), 
												 'where' 	=> 'f.file_open=0', 
												 'order' 	=> 'f.file_submitted ASC',
												 'add_join'	=> array(
												 					array( 'type'		=> 'left',
												 							'select'	=> 'm.members_display_name',
												 							'from'		=> array( 'members' => 'm' ),
												 							'where'		=> 'm.id=f.file_submitter',
												 						)
												 					)
										)		);
		$this->ipsclass->DB->exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row() )
		{
			$user_link	= $row['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$row['file_submitter']}' target='_blank'>{$row['members_display_name']}</a>" : "Guest";
			$date 		= $this->ipsclass->get_date( $row['file_submitted'], 'SHORT' );
		
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}'>{$row['file_name']}</a>",
																				 $user_link,
																				 $date,
																				 "<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=mod&&code=togglefile&id={$row['file_id']}' target='_blank'><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' /></a></center>" 	)		);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->adskin->td_header[] = array( "File Name"   	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "File Author"    	, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Submitted"		, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Remove?"    		, "10%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Files Reported Broken" );
		
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.file_id, f.file_open, f.file_name, f.file_submitter, f.file_submitted', 
												 'from' 	=> array( 'downloads_files' => 'f' ), 
												 'where' 	=> 'f.file_broken=1', 
												 'order' 	=> 'f.file_name ASC',
												 'add_join'	=> array(
												 					array( 'type'		=> 'left',
												 							'select'	=> 'm.members_display_name',
												 							'from'		=> array( 'members' => 'm' ),
												 							'where'		=> 'm.id=f.file_submitter',
												 						)
												 					)
										)		);
		$this->ipsclass->DB->exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row() )
		{
			$user_link	= $row['file_submitter'] ? "<a href='{$this->ipsclass->vars['board_url']}/index.php?showuser={$row['file_submitter']}' target='_blank'>{$row['members_display_name']}</a>" : "Guest";
			$date 		= $this->ipsclass->get_date( $row['file_submitted'], 'SHORT' );
		
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}'>{$row['file_name']}</a>",
																				 $user_link,
																				 $date,
																				 "<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=mod&&code=delete&id=59' target='_blank'><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' /></a></center>" 	)		);
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
}

?>