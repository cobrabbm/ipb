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
|   > Admin Script: Miscellaneous Tools
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .06
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_tools {

	var $func;
	var $modfunc;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';

	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=tools', 'IP.Downloads Tools' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=tools";
		
		require_once( DL_PATH . 'lib/lib_cats.php' );
		$this->func = new lib_cats( );
		$this->func->ipsclass =& $this->ipsclass;
		$this->func->full_init();
		
		$this->ipsclass->DB->load_cache_file( ROOT_PATH . 'sources/sql/'.SQL_DRIVER.'_idm_queries.php', 'sql_idm_queries' );

		switch($this->ipsclass->input['code'])
		{
			case 'main':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->main_screen();
				break;
					
			//-----------------------------------------		
			case 'check_topics':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->check_topics();
				break;
			case 'do_topics':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_topics();
				break;
				
			//-----------------------------------------		
			case 'bulkDir':
			case 'bulkZip':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				require_once( DL_ADMIN_PATH . 'ad_downloads_bulk.php' );
				$bulk = new bulk_admin();
				$bulk->ipsclass	=& $this->ipsclass;
				$bulk->catlib	=& $this->func;
				
				$bulk->init();
				break;
				
			//-----------------------------------------
			case 'do_cats':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_cats();
				break;
			case 'do_caches':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_caches();
				break;				
			//-----------------------------------------
			case 'recount_dlcounts':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->recount_dls();
				break;
			//-----------------------------------------
			case 'purge':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->purge_subs();
				break;
			//-----------------------------------------
			case 'thumbs':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->rebuild_thumbnails();
				break;
			//-----------------------------------------		
			case 'check_orph':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->check_orphaned();
				break;
			case 'do_orph':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_orphaned();
				break;
			//-----------------------------------------		
			case 'check_broken':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->check_broken();
				break;
			case 'do_broken':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_broken();
				break;	
			//-----------------------------------------		
			case 'templates':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->do_templates();
				break;

			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':tools' );
				$this->main_screen();
				break;
		}
	}
	
	//-----------------------------------------
	// Tools Index Page
	//-----------------------------------------	
	
	function main_screen()
	{
		$this->ipsclass->admin->page_title   = "Download Manager Tools";
		$this->ipsclass->admin->page_detail  = "You can run various tools for your IP.Downloads from this page";
		
		$this->ipsclass->adskin->td_header[] = array( "Tool Title"    		, "70%" );
		$this->ipsclass->adskin->td_header[] = array( "Operations"          , "30%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File Management and Maintenance" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Bulk Import</b><br /><span class='forumdesc'>Bulk import files into your Download Manager</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=bulkDir'>From Directory...</a> | <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=bulkZip'>From Zip File...</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Topic Checker</b><br /><span class='forumdesc'>You can use this tool to check for missing topics or files that do not have topics</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=check_topics'>Check Topics</a> | <a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=do_topics&all=1&limit=20'>Fix All Topics</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Category Latest File Information</b><br /><span class='forumdesc'>You can use this tool to update all of the latest file submission information for all categories.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=do_cats'>Update All Categories</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Update All Caches</b><br /><span class='forumdesc'>This tool will update all of the caches used by the Download Manager.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=do_caches'>Update All Caches</a></center>"
																	)		);	
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Recount Download Counts</b><br /><span class='forumdesc'>This tool will count the number of logged downloads for each file and update the file download counts appropriately.<br /><br /><b>WARNING:</b> If you have not had the advanced download logging enabled, or if you disabled it, this will almost certainly reset the download counts for your files.  THERE IS NO UNDOING THIS.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=recount_dlcounts'>Recount the Download Counts</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Purge All File Subscriptions</b><br /><span class='forumdesc'>This tool will clear out all file subscriptions so members do not receive automatic notifications on file updates, unless they re-subscribe to the file.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=purge'>Purge All Subscriptions</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Rebuild All Thumbnails</b><br /><span class='forumdesc'>This tool will rebuild all of your file thumbnails 20 at a time, using the current watermark, copyright-stamping, and thumbnail dimension settings.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=thumbs'>Rebuild All Thumbnails</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Fix Orphaned Files</b><br /><span class='forumdesc'>This tool will search for orphaned files (files that exist but do not have any record in the database to match the file) and allow you to remove them if you wish.  This tool only works for files stored locally or in the database (it does not work for FTP).<br /><br /><b>WARNING:</b> This is moderately resource intensive.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=check_orph'>Find Orphaned Files</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Fix Broken Files, Thumbnails, and Screenshots</b><br /><span class='forumdesc'>This tool will search for files in your database that cannot be found on disk (or stored in the database).  You can only use this tool if you are using local storage of files, or database storage of files.  You will be presented with the option to edit, hide, or delete files, and edit, hide, delete, or remove screenshot/thumbnail value for broken screenshots or thumbnails.<br /><br /><b>WARNING:</b> This is relatively resource intensive, depending upon your server and how many files you have.</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=check_broken'>Find Broken Files &amp; Images</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Rebuild Master IP.Downloads Templates</b><br /><span class='forumdesc'>This tool will rebuild all of your master IP.Downloads HTML Templates from the idm_templates.xml file</span>",
																		"<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=templates'>Rebuild Templates</a></center>"
																	)		);
																	
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	}
	
	
	//-----------------------------------------
	// Check for broken files
	//-----------------------------------------
		
	function check_broken()
	{
		if( $this->ipsclass->vars['idm_filestorage'] == 'ftp' )
		{
			$this->ipsclass->main_msg = "This tool is not available if you are using FTP to store your files";
			$this->main_screen();
			return;
		}
		
		// Could take a minute or two or ten I suppose ;)
		set_time_limit(0);
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'do_broken'	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'tools'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													             			 5 => array( 'type'	  , 'file' ),
													    			)      );
													    					
		$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "80%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "File records where the actual file could not be found" );		
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'db' )
		{
			$this->ipsclass->DB->build_query( array( 'select' => 'f.file_id, f.file_name, f.file_storagetype',
													 'from' => array( 'downloads_files' => 'f' ),
													 'add_join' => array( 0 => array( 'select' => 's.*',
													 								  'from' => array( 'downloads_filestorage' => 's' ),
													 								  'where' => 's.storage_id=f.file_id',
													 								  'type' => 'left' )	),
													 'where' => "f.file_storagetype='db' AND ((s.storage_file='') OR (s.storage_ss='' AND f.file_ssname!='') OR (s.storage_thumb='' AND f.file_thumb!=''))"	)	);
			$this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows() )
			{
				$files = array();
				$screenshots = array();
				$thumbs = array();
				
				while( $row = $this->ipsclass->DB->fetch_row() )
				{
					if( $row['storage_file'] == '' )
					{
						$files[] = $row;
					}
					if( $row['storage_ss'] == '' )
					{
						$screenshots[] = $row;
					}
					if( $row['storage_thumb'] == '' )
					{
						$thumbs[] = $row;
					}
				}
			}
			
			if( count($files) )
			{
				foreach($files as $k => $row )
				{
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}' target='_blank'>{$row['file_name']}</a></b>",
																							"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$row['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='file_{$row['file_id']}' value='1' /></center>"
																			)		);
				}
			}
			else
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);
			}
			
			$this->ipsclass->html .= "<tr><td align='right' class='pformstrip' colspan='2' >With Selected: <select name='action' style='vertical-align:middle;'><option value='del'>Delete Files</option>
										<option value='hide'>Hide (Unapprove) Files</option></select>&nbsp;<input type='submit' value='Go &raquo;' id='button' style='vertical-align:middle;'></td></tr></form>";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'do_broken'	),
													             			 	 2 => array( 'act'    , 'downloads'     ),
													             			 	 3 => array( 'req'	  , 'tools'	),
													             			 	 4 => array( 'section', $this->ipsclass->section_code ),
													             			 	 5 => array( 'type'	  , 'imgs' ),
													    			)      );
													    				
			$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "80%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "20%" );

			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Screenshot/Thumbnail records where the actual file could not be found" );		
			
			
			if( count($screenshots) )
			{
				foreach( $screenshots as $k => $row )
				{
					$cnt++;
					
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Screenshot: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}' target='_blank'>{$row['file_name']}</a></b>",
																							"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$row['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='ss_{$row['file_id']}' value='1' /></center>"
																			)		);
				}
			}
			
			if( count($thumbs) )
			{
				foreach( $thumbs as $k => $row )
				{
					$cnt++;
					
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Thumbnail: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$row['file_id']}' target='_blank'>{$row['file_name']}</a></b>",
																							"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$row['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='thumb_{$row['file_id']}' value='1' /></center>"
																			)		);
				}
			}			
			
			if( $cnt == 0 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);
			}
			
			$this->ipsclass->html .= "<tr><td align='right' class='pformstrip' colspan='2' >With Selected: <select name='action' style='vertical-align:middle;'><option value='del'>Delete Files</option>
										<option value='hide'>Hide (Unapprove) Files</option><option value='rem'>Remove Images from File</option></select>&nbsp;<input type='submit' value='Go &raquo;' id='button' style='vertical-align:middle;'></td></tr></form>";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();			
		}
		else
		{
			$count 	    = 0;
			$the_files  = array();
			$the_images = array();
			$the_thumbs = array();
			$real_files = array();
			$real_imgs  = array();
			$real_thumbs = array();
			
			if( is_dir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ) )
			{
				if( $dh = opendir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ) )
				{
					while (($file = readdir($dh)) !== false)
					{
						if( !is_dir($file) )
						{
							$the_files[] = $file;
						}
					}
					
					closedir( $dh );
				}
			}
			
			$this->ipsclass->DB->build_query( array( 'select' => 'file_id, file_name, file_filename, file_thumb, file_ssname, file_ssurl, file_url', 'from' => 'downloads_files' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				if( $r['file_url'] == '' )
				{
					$real_files[$r['file_filename']] = $r;
				}
				
				if( $r['file_ssurl'] == '' )
				{
					if( $r['file_thumb'] )
					{
						$real_thumbs[$r['file_thumb']]   = $r;
					}
					
					if( $r['file_ssname'] )
					{
						$real_imgs[$r['file_ssname']]    = $r;
					}
				}
			}
			
			if( count($real_files) )
			{
				foreach( $real_files as $filename => $file )
				{
					if( !in_array( $filename, $the_files ) )
					{
						$cnt++;
						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$file['file_id']}' target='_blank'>{$file['file_name']}</a></b>",
																					"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$file['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='file_{$file['file_id']}' value='1' /></center>"
																				)		);
					}
				}
			}
			
			if( $cnt == 0 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);				
			}
			
			$this->ipsclass->html .= "<tr><td align='right' class='pformstrip' colspan='2' >With Selected: <select name='action' style='vertical-align:middle;'><option value='del'>Delete Files</option>
										<option value='hide'>Hide (Unapprove) Files</option></select>&nbsp;<input type='submit' value='Go &raquo;' id='button' style='vertical-align:middle;'></td></tr></form>";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'do_broken'	),
													             			 	 2 => array( 'act'    , 'downloads'     ),
													             			 	 3 => array( 'req'	  , 'tools'	),
													             			 	 4 => array( 'section', $this->ipsclass->section_code ),
													             			 	 5 => array( 'type'	  , 'imgs' ),
													    			)      );
													    				
			$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "80%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "20%" );

			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Screenshot/Thumbnail records where the actual file could not be found" );		
			
			$cnt = 0;
			
			if( is_dir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ) )
			{
				if( $dh = opendir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ) )
				{
					while (($file = readdir($dh)) !== false)
					{
						if( !is_dir($file) )
						{
							if( preg_match( "/^thumb-(.+?)$/", $file, $matches ) )
							{
								$the_thumbs[] = $file;
							}
							else
							{
								$the_images[] = $file;
							}
						}
					}
					
					closedir( $dh );
				}
			}
			
			if( count($real_imgs) )
			{
				foreach( $real_imgs as $filename => $file )
				{
					if( !in_array( $filename, $the_images ) )
					{
						$cnt++;
						
						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Screenshot: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$file['file_id']}' target='_blank'>{$file['file_name']}</a></b>",
																					"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$file['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='ss_{$file['file_id']}' value='1' /></center>"
																				)		);
					}
				}
			}
			
			if( count($real_thumbs) )
			{
				foreach( $real_thumbs as $filename => $file )
				{
					if( !in_array( $filename, $the_thumbs ) )
					{
						$cnt++;
						
						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Thumbnail: <a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&showfile={$file['file_id']}' target='_blank'>{$file['file_name']}</a></b>",
																					"<center><a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&req=submit&code=edit_main&id={$file['file_id']}' target='_blank'>Edit File</a>&nbsp;<input type='checkbox' checked='checked' name='thumb_{$file['file_id']}' value='1' /></center>"
																				)		);
					}
				}
			}						
			
			if( $cnt == 0 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);
			}
			
			$this->ipsclass->html .= "<tr><td align='right' class='pformstrip' colspan='2' >With Selected: <select name='action' style='vertical-align:middle;'><option value='del'>Delete Files</option>
										<option value='hide'>Hide (Unapprove) Files</option><option value='rem'>Remove Images from File</option></select>&nbsp;<input type='submit' value='Go &raquo;' id='button' style='vertical-align:middle;'></td></tr></form>";
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();			
		}
		
		$this->ipsclass->admin->output();
	}
	
	
	//-----------------------------------------
	// Fix broken files
	//-----------------------------------------
		
	function do_broken( )
	{
		$thumbs = array();
		$files  = array();
		$ss 	= array();
		$cnt 	= 0;
		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^file_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$files[] = $match[1];
 				}
 			}
 			if ( preg_match( "/^ss_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$ss[] = $match[1];
 				}
 			} 			
 			if ( preg_match( "/^thumb_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$thumbs[] = $match[1];
 				}
 			} 			
 		}
 		
		require_once( ROOT_PATH.'sources/lib/func_mod.php' );
        $this->modfunc = new func_mod();
        $this->modfunc->ipsclass =& $this->ipsclass;
 		
 		if( $this->ipsclass->input['type'] == 'file' )
 		{
	 		if( count($files) )
	 		{
		 		$cnt = count($files);
		 		
				$str = implode( ",", $files );
				
		 		switch( $this->ipsclass->input['action'] )
		 		{
			 		case 'del':
			 			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => "file_id IN({$str})" )	);
			 			$this->ipsclass->DB->exec_query();
			 			
			 			while( $row = $this->ipsclass->DB->fetch_row() )
			 			{
				 			if( $row['file_storagetype'] == 'web' OR $row['file_storagetype'] == 'nonweb' )
				 			{
			 					@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $row['file_filename'] );
			 					
								if( $row['file_ssname'] )
								{
									@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_ssname'] );
									if( $row['file_thumb'] )
									{
										@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_thumb'] );
									}
								}
							}
							
							//-----------------------------------------
							// Delete the topic if appropriate
							//-----------------------------------------		
						
							if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topice'] )
							{
								if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'] )
								{
									if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicd'] )
									{
										$tid = $row['file_topicid'];
										
										if( $tid > 0 )
										{
									        $this->modfunc->init($this->func->cat_lookup[$rowe['file_cat']]['coptions']['opt_topicf']);
									        
											$this->ipsclass->DB->simple_construct( array( 'select' => 'tid, forum_id', 'from' => 'topics', 'where' => "state='link' AND moved_to='".$tid.'&'.$this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf']."'" ) );
											$this->ipsclass->DB->simple_exec();
											
											if ( $linked_topic = $this->ipsclass->DB->fetch_row() )
											{
												$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'topics', 'where' => "tid=".$linked_topic['tid'] ) );
												
												$this->modfunc->forum_recount($linked_topic['forum_id']);
											}
											
											$this->modfunc->topic_delete($tid);
											$this->modfunc->add_moderate_log($this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'], $tid, '', $row['file_name'], "Deleted an IP.Downloads Auto-Generated topic");
										}
									}
								}
							}
						}
						
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_favorites', 'where' => "ffid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_ccontent', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_comments', 'where' => "comment_fid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_files', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_filestorage', 'where' => "storage_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_fileviews', 'where' => "view_fid IN({$str})" ) );
						
						break;
						
					case 'hide':
						$this->ipsclass->DB->do_update( "downloads_files", array( 'file_open' => 0 ), "file_id IN ({$str})" );
						break;
						
					default:
						$this->ipsclass->main_msg = "You did not select any action to apply to the files";
						$this->check_broken();
						return;
						break;
						
				}
				
				$this->func->rebuild_fileinfo( 'all' );
				$this->func->rebuild_stats_cache();
			}
			else
			{
				$this->ipsclass->main_msg = "You did not select any files";
				$this->check_broken();
				return;
			}
		}
		else
		{
	 		if( count($ss) )
	 		{
		 		$cnt = count($ss);
		 		
				$str = implode( ",", $ss );
				
		 		switch( $this->ipsclass->input['action'] )
		 		{
			 		case 'del':
			 			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => "file_id IN({$str})" )	);
			 			$this->ipsclass->DB->exec_query();
			 			
			 			while( $row = $this->ipsclass->DB->fetch_row() )
			 			{
				 			if( $row['file_storagetype'] == 'web' OR $row['file_storagetype'] == 'nonweb' )
				 			{
			 					@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $row['file_filename'] );
			 					
								if( $row['file_ssname'] )
								{
									@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_ssname'] );
									if( $row['file_thumb'] )
									{
										@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_thumb'] );
									}
								}
							}
							
							//-----------------------------------------
							// Delete the topic if appropriate
							//-----------------------------------------		
						
							if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topice'] )
							{
								if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'] )
								{
									if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicd'] )
									{
										$tid = $row['file_topicid'];
										
										if( $tid > 0 )
										{
									        $this->modfunc->init($this->func->cat_lookup[$rowe['file_cat']]['coptions']['opt_topicf']);
									        
											$this->ipsclass->DB->simple_construct( array( 'select' => 'tid, forum_id', 'from' => 'topics', 'where' => "state='link' AND moved_to='".$tid.'&'.$this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf']."'" ) );
											$this->ipsclass->DB->simple_exec();
											
											if ( $linked_topic = $this->ipsclass->DB->fetch_row() )
											{
												$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'topics', 'where' => "tid=".$linked_topic['tid'] ) );
												
												$this->modfunc->forum_recount($linked_topic['forum_id']);
											}
											
											$this->modfunc->topic_delete($tid);
											$this->modfunc->add_moderate_log($this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'], $tid, '', $row['file_name'], "Deleted an IP.Downloads Auto-Generated topic");
										}
									}
								}
							}
						}
						
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_favorites', 'where' => "ffid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_ccontent', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_comments', 'where' => "comment_fid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_files', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_filestorage', 'where' => "storage_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_fileviews', 'where' => "view_fid IN({$str})" ) );
						
						break;
						
					case 'hide':
						$this->ipsclass->DB->do_update( "downloads_files", array( 'file_open' => 0 ), "file_id IN ({$str})" );
						break;
						
					case 'rem':
						$this->ipsclass->DB->do_update( "downloads_files", array( 'file_ssname' => '' ), "file_id IN ({$str})" );
						break;
						
					default:
						$this->ipsclass->main_msg = "You did not select any action to apply to the images";
						$this->check_broken();
						return;
						break;
				}
			}
						
	 		if( count($thumbs) )
	 		{
		 		$cnt += count($thumbs);
		 		
				$str = implode( ",", $thumbs );
				
		 		switch( $this->ipsclass->input['action'] )
		 		{
			 		case 'del':
			 			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => "file_id IN({$str})" )	);
			 			$this->ipsclass->DB->exec_query();
			 			
			 			while( $row = $this->ipsclass->DB->fetch_row() )
			 			{
				 			if( $row['file_storagetype'] == 'web' OR $row['file_storagetype'] == 'nonweb' )
				 			{
			 					@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $row['file_filename'] );
			 					
								if( $row['file_ssname'] )
								{
									@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_ssname'] );
									if( $row['file_thumb'] )
									{
										@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $row['file_thumb'] );
									}
								}
							}
							
							//-----------------------------------------
							// Delete the topic if appropriate
							//-----------------------------------------		
						
							if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topice'] )
							{
								if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'] )
								{
									if( $this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicd'] )
									{
										$tid = $row['file_topicid'];
										
										if( $tid > 0 )
										{
									        $this->modfunc->init($this->func->cat_lookup[$rowe['file_cat']]['coptions']['opt_topicf']);
									        
											$this->ipsclass->DB->simple_construct( array( 'select' => 'tid, forum_id', 'from' => 'topics', 'where' => "state='link' AND moved_to='".$tid.'&'.$this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf']."'" ) );
											$this->ipsclass->DB->simple_exec();
											
											if ( $linked_topic = $this->ipsclass->DB->fetch_row() )
											{
												$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'topics', 'where' => "tid=".$linked_topic['tid'] ) );
												
												$this->modfunc->forum_recount($linked_topic['forum_id']);
											}
											
											$this->modfunc->topic_delete($tid);
											$this->modfunc->add_moderate_log($this->func->cat_lookup[$row['file_cat']]['coptions']['opt_topicf'], $tid, '', $row['file_name'], "Deleted an IP.Downloads Auto-Generated topic");
										}
									}
								}
							}
						}
						
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_favorites', 'where' => "ffid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_ccontent', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_comments', 'where' => "comment_fid IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_files', 'where' => "file_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_filestorage', 'where' => "storage_id IN({$str})" ) );
						$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'downloads_fileviews', 'where' => "view_fid IN({$str})" ) );
						
						break;
						
					case 'hide':
						$this->ipsclass->DB->do_update( "downloads_files", array( 'file_open' => 0 ), "file_id IN ({$str})" );
						break;
						
					case 'rem':
						$this->ipsclass->DB->do_update( "downloads_files", array( 'file_thumb' => '' ), "file_id IN ({$str})" );
						break;						
						
					default:
						$this->ipsclass->main_msg = "You did not select any action to apply to the images";
						$this->check_broken();
						return;
						break;
				}
			}
			
			$this->func->rebuild_fileinfo( 'all' );
			$this->func->rebuild_stats_cache();
		}
			
		$this->ipsclass->admin->save_log("{$cnt} broken files, screenshots or thumbnails have been fixed.");
		$this->ipsclass->main_msg = "{$cnt} broken files, screenshots or thumbnails have been fixed.";
		$this->main_screen();
	}	
	
	
	//-----------------------------------------
	// Check for orphaned files
	//-----------------------------------------
		
	function check_orphaned()
	{
		if( $this->ipsclass->vars['idm_filestorage'] == 'ftp' )
		{
			$this->ipsclass->main_msg = "This tool is not available if you are using FTP to store your files";
			$this->main_screen();
			return;
		}
		
		// Could take a minute or two ;)
		set_time_limit(0);
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'do_orph'	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'tools'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
													    					
		$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "80%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "20%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Files found that do not have a corresponding record in the database" );		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_subtitle( "If you run this tool, and there are still files on this page afterwards, you will need to manually delete them from your site using an FTP program", 'subtitle', 2 );		
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'db' )
		{
			$this->ipsclass->DB->build_query( array( 'select' => 's.*',
													 'from' => array( 'downloads_filestorage' => 's' ),
													 'add_join' => array( 0 => array( 'select' => 'f.file_id',
													 								  'from' => array( 'downloads_files' => 'f' ),
													 								  'where' => 'f.file_id=s.storage_id',
													 								  'type' => 'left' )	),
													 'where' => "f.file_id=0 OR f.file_id=''"	)	);
			$this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows() )
			{
				while( $row = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>ID: {$row['storage_id']}</b>",
																					"<center><input type='checkbox' checked='checked' name='id_{$row['storage_id']}' value='1' /></center>"
																				)		);
				}		
			}
			else
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);
			}
		}
		else
		{
			$count 	    = 0;
			$the_files  = array();
			$the_images = array();
			$real_files = array();
			$real_imgs  = array();
			
			if( is_dir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ) )
			{
				if( $dh = opendir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ) )
				{
					while (($file = readdir($dh)) !== false)
					{
						if( !is_dir($file) && $file != "index.html" )
						{
							$the_files[] = $file;
						}
					}
					
					closedir( $dh );
				}
			}
			
			$this->ipsclass->DB->build_query( array( 'select' => 'file_filename, file_thumb, file_ssname', 'from' => 'downloads_files' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				$real_files[] = $r['file_filename'];
				$real_imgs[]  = $r['file_thumb'];
				$real_imgs[]  = $r['file_ssname'];
			}
			
			if( count($the_files) )
			{
				foreach( $the_files as $file )
				{
					if( !in_array( $file, $real_files ) )
					{
						$cnt++;
						
						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>File: {$file}</b>",
																					"<center><input type='checkbox' checked='checked' name='file_{$file}' value='1' /></center>"
																				)		);
					}
				}
			}
			
			if( is_dir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ) )
			{
				if( $dh = opendir( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ) )
				{
					while (($file = readdir($dh)) !== false)
					{
						if( !is_dir($file) && $file != "index.html" )
						{
							$the_images[] = $file;
						}
					}
					
					closedir( $dh );
				}
			}
			
			if( count($the_images) )
			{
				foreach( $the_images as $file )
				{
					if( !in_array( $file, $real_imgs ) )
					{
						$cnt++;
						
						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Screenshot/Thumbnail: {$file}</b>",
																					"<center><input type='checkbox' checked='checked' name='ss_{$file}' value='1' /></center>"
																				)		);
					}
				}
			}			
			
			if( $cnt == 0 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>"
																					)		);
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form_standalone("Remove Selected Files");
		
		$this->ipsclass->admin->output();
	}
	
	
	function do_orphaned( )
	{
		$ids = array();
		$files = array();
		$ss = array();
		$cnt = 0;
		
 		foreach ($this->ipsclass->input as $key => $value)
 		{
 			if ( preg_match( "/^file_(.+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
	 				$pos = strrpos( $match[1], "_" );
	 				$match[1]{$pos} = ".";
	 				
 					$files[] = $match[1];
 				}
 			}
 			if ( preg_match( "/^ss_(.+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
	 				$pos = strrpos( $match[1], "_" );
	 				$match[1]{$pos} = ".";
	 					 				
 					$ss[] = $match[1];
 				}
 			} 			
 			if ( preg_match( "/^id_(\d+)$/", $key, $match ) )
 			{
 				if ($this->ipsclass->input[$match[0]])
 				{
 					$ids[] = $match[1];
 				}
 			} 			
 		}
 		
 		if( count($ids) )
 		{
	 		$cnt += count($ids);
	 		
	 		$string = implode( ",", $ids );
	 		
	 		$this->ipsclass->DB->do_delete( 'downloads_filestorage', 'storage_id IN('.$string.')' );
 		}
 		
 		if( count($files) )
 		{
	 		$path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] )."/";
	 		
	 		foreach( $files as $file )
	 		{
		 		$cnt++;
		 		
		 		@unlink( $path.$file );
	 		}
 		}
 		
 		if( count($ss) )
 		{
	 		$path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] )."/";
	 		
	 		foreach( $ss as $file )
	 		{
		 		$cnt++;
		 		
		 		@unlink( $path.$file );
	 		}
 		} 		
 		
		$this->ipsclass->admin->save_log("{$cnt} orphaned files, screenshots or thumbnails have been removed.");
		$this->ipsclass->main_msg = "{$cnt} orphaned files, screenshots or thumbnails have been removed.";
		$this->main_screen();
	}
		
	
	//-----------------------------------------
	// Check for missing/broken topics
	//-----------------------------------------
		
	function check_topics()
	{
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'do_topics'	),
													             			 2 => array( 'act'    , 'downloads'     ),
													             			 3 => array( 'req'	  , 'tools'	),
													             			 4 => array( 'section', $this->ipsclass->section_code ),
													    			)      );
													    					
		$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "70%" );
		$this->ipsclass->adskin->td_header[] = array( "Topic"          		, "20%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "10%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Files that have a topic linked, but it is invalid, or in the trash can" );		
		
		$this->ipsclass->DB->build_query( array( 'select'	=> 'f.*',
												 'from'		=> array( 'downloads_files' => 'f' ),
												 'where'	=> 'f.file_open=1 AND f.file_topicid <> 0',
												 'add_join'	=> array(
												 				array(
												 					'type'		=> 'left',
												 					'select'	=> 't.tid, t.forum_id',
												 					'where'		=> 't.tid=f.file_topicid',
												 					'from'		=> array( 'topics' =>'t' )
												 					),
												 				array(
												 					'type'		=> 'left',
												 					'select'	=> 'm.members_display_name',
												 					'where'		=> 'm.id=f.file_submitter',
												 					'from'		=> array( 'members' =>'m' )
												 					)
												 				)
										)		);
		$this->ipsclass->DB->exec_query();
		
		$displayed = 0;
		
		if( $this->ipsclass->DB->get_num_rows() )
		{
			while( $row = $this->ipsclass->DB->fetch_row() )
			{
				if( $this->ipsclass->vars['forum_trash_can_id'] == $row['forum_id'] )
				{
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$row['file_name']}</b>",
																					"<center>{$row['file_topicid']}</center>",
																					"<center><input type='checkbox' checked='checked' name='file_{$row['file_id']}' value='1' /></center>"
																				)		);
																				
					$displayed++;
				}
				else if( !$row['tid'] )
				{
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$row['file_name']}</b>",
																					"<center>{$row['file_topicid']}</center>",
																					"<center><input type='checkbox' checked='checked' name='file_{$row['file_id']}' value='1' /></center>"
																				)		);
																				
					$displayed++;
				}
			}
			
			if( $displayed == 0 )
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																						"<center>--</center>",
																						"<center>--</center>"
																					)		);
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																					"<center>--</center>",
																					"<center>--</center>"
																				)		);
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$categories = array();
		
		if( count($this->func->cat_lookup) > 0 )
		{
			foreach( $this->func->cat_lookup as $k => $v )
			{
				if( $v['coptions']['opt_topice'] == 1 )
				{
					$categories[] = $k;
				}
			}
		}
		
		if( count($categories) )
		{
			$this->ipsclass->adskin->td_header[] = array( "File Name"    		, "70%" );
			$this->ipsclass->adskin->td_header[] = array( "Topic"          		, "20%" );
			$this->ipsclass->adskin->td_header[] = array( "&nbsp;"          	, "10%" );			
			
			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Files that have no topic linked, but category calls for topic to be generated" );
			
			$sql_string = implode( ",", $categories);
			
			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => "file_open=1 AND (file_topicid=0 OR file_topicid='') AND file_cat IN({$sql_string})" ) );
			$this->ipsclass->DB->exec_query();
			
			if( $this->ipsclass->DB->get_num_rows() )
			{
				while( $row = $this->ipsclass->DB->fetch_row() )
				{
					$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>{$row['file_name']}</b>",
																					"<center>{$row['file_topicid']}</center>",
																					"<center><input type='checkbox' checked='checked' name='file_{$row['file_id']}' value='1' /></center>"
																				)		);
				}
			}
			else
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>NONE FOUND</b>",
																					"<center>--</center>",
																					"<center>--</center>"
																				)		);
			}
			
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
		
		$this->ipsclass->html .= "<div class='tableborder'><div align='center' class='tablesubheader'>Create topics for selected files <input type='text' size='2' name='limit' value='10' /> at a time <input type='submit' value='GO&raquo;'></div></div></form>";//$this->ipsclass->adskin->end_form_standalone("Create Topics for Selected Files in intervals of <input type='text' size='2' name='limit' value='10' /> at a time");
		
		$this->ipsclass->admin->output();
	}
	
	
	function do_cats()
	{
		$this->func->rebuild_fileinfo('all');
		$this->ipsclass->admin->save_log("The latest file submission information for all IP.Downloads categories has been updated");
		$this->ipsclass->main_msg = "The latest file submission information for all IP.Downloads categories has been updated";
		$this->main_screen();
	}
	
	
	function purge_subs()
	{
		$this->ipsclass->DB->do_update( "downloads_files", array('file_sub_mems' => ''), "file_sub_mems!=''" );
		
		$this->ipsclass->admin->save_log("All file subscriptions have been purged");
		$this->ipsclass->main_msg = "All file subscriptions have been purged";
		$this->main_screen();
	}	
	
	
	function recount_dls()
	{
		if( $this->ipsclass->vars['idm_logalldownloads'] == 0 )
		{
			$this->ipsclass->main_msg = "You must have the setting to Log All Downloads enabled if you wish to use this tool.  Operation aborted.";
			$this->main_screen();
			return;
		}
		
		$ids = array();
		
		$this->ipsclass->DB->cache_add_query( 'tools_recount_dls', array(), 'sql_idm_queries' );
		$outer = $this->ipsclass->DB->cache_exec_query();
		
		while( $row = $this->ipsclass->DB->fetch_row($outer) )
		{
			if( !$row['dfid'] )
			{
				continue;
			}
			
			$ids[] = $row['dfid'];
			
			$this->ipsclass->DB->do_update( "downloads_files", array('file_downloads' => $row['cnt']), "file_id=".$row['dfid'] );
		}
		
		if( count($ids) )
		{
			$file_ids = implode( ",", $ids );
			$this->ipsclass->DB->do_update( "downloads_files", array('file_downloads' => 0), "file_id NOT IN({$file_ids})" );
		}
		
		$this->func->rebuild_fileinfo('all');
		$this->ipsclass->admin->save_log("All file download counts have been recounted");
		$this->ipsclass->main_msg = "All file download counts have been recounted";
		$this->main_screen();
	}	
	
	
	function do_caches()
	{
		// Stats
		$this->func->rebuild_stats_cache();
		
		// Categories
		$this->func->rebuild_cat_cache();
		
		// Custom Fields
		$this->ipsclass->cache['idm_cfields'] = array();
				
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'order' => 'cf_position' ) );
						 
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_cfields'][ $r['cf_id'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_cfields', 'array' => 1, 'deletefirst' => 1 ) );
		
		// Mime-types
		$this->ipsclass->cache['idm_mimetypes'] = array();
			
		$this->ipsclass->DB->simple_construct( array( 'select' => 'mime_id,mime_extension,mime_mimetype,mime_file,mime_screenshot,mime_inline,mime_img', 'from' => 'downloads_mime', 'where' => "mime_screenshot<>0 OR mime_file<>0" ) );
		$this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_mimetypes'][ $r['mime_extension'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_mimetypes', 'array' => 1, 'deletefirst' => 1 ) );
		
		// Moderators
		$this->func->rebuild_mod_cache();
		
		$this->ipsclass->admin->save_log("All of the caches used by the IP.Downloads have been updated");
		$this->ipsclass->main_msg = "All of the caches used by the IP.Downloads have been updated";
		$this->main_screen();
	}	
	
	
	function do_topics()
	{
		// Set limit to do at a time...
		$this->ipsclass->input['limit'] = $this->ipsclass->input['limit'] ? intval($this->ipsclass->input['limit']) : 20;
		
		//-----------------------------------------
		// Load and config the std/rte editors
		//-----------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_editor.php" );
		$han_editor = new han_editor();
		$han_editor->ipsclass =& $this->ipsclass;
		$han_editor->init();
		
		//-----------------------------------------
		// Load and config the post parser
		//-----------------------------------------

		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
		$parser = new parse_bbcode();
		$parser->ipsclass =& $this->ipsclass;
		$parser->allow_update_caches = 1;
		$parser->bypass_badwords = intval($this->ipsclass->member['g_bypass_badwords']);
		
		//-----------------------------------------
		// Load and config email lib
		//-----------------------------------------
				
		require_once( ROOT_PATH."sources/classes/class_email.php" );
		$email = new emailer();
		$email->ipsclass =& $this->ipsclass;
		$email->email_init();
		
		$this->ipsclass->load_language('lang_downloads');
		
		//-----------------------------------------
		// And finally, the topic lib
		//-----------------------------------------
				
		require_once( DL_PATH."lib/lib_topics.php" );
		$lib_topics = new lib_topics();
		$lib_topics->ipsclass 	=& $this->ipsclass;
		$lib_topics->email		=& $email;
		$lib_topics->parser		=& $parser;
		$lib_topics->han_editor	=& $han_editor;
		
		//-----------------------------------------
		// Let's begin...
		//-----------------------------------------
				
		$tids 		= array();
		$fixed 		= $this->ipsclass->input['fixed'] ? intval($this->ipsclass->input['fixed']) : 0;
		$fix_dis 	= $this->ipsclass->input['fixdis'] ? intval($this->ipsclass->input['fixdis']) : 0;
		$cnt		= 0;
		
		if( $this->ipsclass->input['all'] == 1 )
		{
			$this->ipsclass->DB->build_query( array( 'select'	=> 'f.*',
													 'from'		=> array( 'downloads_files' => 'f' ),
													 'where'	=> 'f.file_open=1 AND f.file_topicid <> 0',
													 'limit'	=> array( $fixed, $this->ipsclass->input['limit'] ),
													 'add_join'	=> array(
													 				array(
													 					'type'		=> 'left',
													 					'select'	=> 't.tid, t.forum_id',
													 					'where'		=> 't.tid=f.file_topicid',
													 					'from'		=> array( 'topics' =>'t' )
													 					),
													 				array(
													 					'type'		=> 'left',
													 					'select'	=> 'm.members_display_name',
													 					'where'		=> 'm.id=f.file_submitter',
													 					'from'		=> array( 'members' =>'m' )
													 					)
													 				)
											)		);
			$outer = $this->ipsclass->DB->cache_exec_query();
			
			if( $this->ipsclass->DB->get_num_rows($outer) )
			{
				while( $row = $this->ipsclass->DB->fetch_row($outer) )
				{
					$forum = array();
					$do_fix = 0;
					$fixed++;
					
					$category = $this->func->cat_lookup[ $row['file_cat'] ];
					
					if( $this->ipsclass->vars['forum_trash_can_id'] == $row['forum_id'] )
					{
						$do_fix = 1;
					}
					
					if( !$row['tid'] )
					{
						$do_fix = 1;
					}
					
					if( $do_fix == 1 )
					{					
						if( !$category['coptions']['opt_topicf'] )
						{
							continue;
						}
						
						$row['file_submitter_name'] = $row['members_display_name'];
						
						$lib_topics->sort_topic( $row, $category, 'new', 1 );

						$fix_dis++;
					}
				}

				$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=do_topics&all=1&limit={$this->ipsclass->input['limit']}&fixed={$fixed}&fixdis={$fix_dis}", "{$fix_dis} topics created so far...", 0, 2 );
			}
			else
			{
				$categories = array();
			
				if( count($this->func->cat_lookup) > 0 )
				{
					foreach( $this->func->cat_lookup as $k => $v )
					{
						if( $v['coptions']['opt_topice'] == 1 )
						{
							$categories[] = $k;
						}
					}
				}
			
				if( count($categories) )
				{
					$sql_string = implode( ",", $categories);
					
					$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.*', 
															 'from' 	=> array( 'downloads_files' => 'f' ),
															 'where' 	=> "f.file_open=1 AND f.file_topicid < 1 AND f.file_cat IN({$sql_string})",
															 'add_join'	=> array(
															 					array(
															 						'type'		=> 'left',
															 						'select'	=> 'm.members_display_name',
															 						'from'		=> array( 'members' => 'm' ),
															 						'where'		=> 'm.id=f.file_submitter'
															 						)
															 					)
													)		);
	
					$outer = $this->ipsclass->DB->exec_query();
					
					if( $this->ipsclass->DB->get_num_rows($outer) )
					{
						while( $row = $this->ipsclass->DB->fetch_row($outer) )
						{
							$category = $this->func->cat_lookup[ $row['file_cat'] ];
							
							if( !$category['coptions']['opt_topicf'] )
							{
								continue;
							}
							
							$row['file_submitter_name'] = $row['members_display_name'];
							
							$lib_topics->sort_topic( $row, $category, 'new', 1 );
													
							$fixed++;						
							$cnt++;

							if( $cnt >= $this->ipsclass->input['limit'] )
							{
								$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=do_topics&all=1&limit=20&fixed={$fixed}", "{$fixed} topics created so far...", 0, 2 );
							}
	
						}
					}
				}
				
				if( $cnt == 0 )
				{
					$this->func->rebuild_fileinfo( 'all' );
					$this->func->rebuild_cat_cache();
					$this->ipsclass->admin->save_log("{$fix_dis} IP.Downloads auto-created topics created");
					$this->ipsclass->main_msg = "{$fix_dis} IP.Downloads auto-created topics created";
					$this->main_screen();
				}
			}
		} // End of fix all	
		else
		{
			$query_string 	= "";
			$completed_ids 	= array();
			
	 		foreach ($this->ipsclass->input as $key => $value)
	 		{
	 			if ( preg_match( "/^file_(\d+)$/", $key, $match ) )
	 			{
	 				if ($this->ipsclass->input[$match[0]])
	 				{
	 					$ids[ $match[1] ] = $this->ipsclass->input[$match[0]];
	 				}
	 			}
	 		}
	 		
	 		if( count($ids) )
	 		{
		 		foreach( $ids as $key => $value )
		 		{
			 		if( $value == 0 )
			 		{
				 		continue;
			 		}
			 		
					$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.*', 
															 'from' 	=> array( 'downloads_files' => 'f' ),
															 'where' 	=> "f.file_open=1 AND f.file_id={$key}",
															 'add_join'	=> array(
															 					array(
															 						'type'		=> 'left',
															 						'select'	=> 'm.members_display_name',
															 						'from'		=> array( 'members' => 'm' ),
															 						'where'		=> 'm.id=f.file_submitter'
															 						)
															 					)
													)		);

					$outer = $this->ipsclass->DB->exec_query();
					
					if( $this->ipsclass->DB->get_num_rows($outer) )
					{
						while( $row = $this->ipsclass->DB->fetch_row($outer) )
						{
							$category = $this->func->cat_lookup[ $row['file_cat'] ];
							
							if( !$category['coptions']['opt_topicf'] )
							{
								continue;
							}
							
							$row['file_submitter_name'] = $row['members_display_name'];
							
							$lib_topics->sort_topic( $row, $category, 'new', 1 );
									
							$fixed++;	
							$completed_ids[] = $key;
							$cnt++;

							if( $cnt >= $this->ipsclass->input['limit'] )
							{
								$to_querys = array();

								foreach( $ids as $k => $v )
								{
									if( !in_array( $k, $completed_ids ) )
									{
										if( $v == 1 )
										{
											$to_querys[] = $k;
										}
									}
								}
								
								if( count($to_querys) )
								{
									$this->ipsclass->DB->do_update( "downloads_files", array( 'file_topicid' => -1 ), "file_id IN (".implode( ",", $to_querys ).")" );
								}
								
								$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=do_topics&limit={$this->ipsclass->input['limit']}&fixed={$fixed}", "{$fixed} topics created so far...", 0, 2 );
							}
						}
					}
				}
			}
			else
			{
				$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.*', 
														 'from' 	=> array( 'downloads_files' => 'f' ),
														 'where' 	=> "f.file_open=1 AND f.file_topicid=-1",
														 'limit'	=> array( $this->ipsclass->input['limit'] ),
														 'add_join'	=> array(
														 					array(
														 						'type'		=> 'left',
														 						'select'	=> 'm.members_display_name',
														 						'from'		=> array( 'members' => 'm' ),
														 						'where'		=> 'm.id=f.file_submitter'
														 						)
														 					)
												)		);
	
				$outer = $this->ipsclass->DB->exec_query();
				
				if( $this->ipsclass->DB->get_num_rows($outer) )
				{
					while( $row = $this->ipsclass->DB->fetch_row($outer) )
					{
						$category = $this->func->cat_lookup[ $row['file_cat'] ];
						
						if( !$category['coptions']['opt_topicf'] )
						{
							continue;
						}
						
						$row['file_submitter_name'] = $row['members_display_name'];
						
						$lib_topics->sort_topic( $row, $category, 'new', 1 );
												
						$fixed++;	
						$completed_ids[] = $key;
						$cnt++;

						if( $cnt >= $this->ipsclass->input['limit'] )
						{
							$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=do_topics&limit={$this->ipsclass->input['limit']}&fixed={$fixed}", "{$fixed} topics created so far...", 0, 2 );
						}
	
					}
				}
			}				
		}	
		
		$this->func->rebuild_fileinfo( 'all' );
		$this->func->rebuild_cat_cache();
		$this->ipsclass->admin->save_log("{$fixed} IP.Downloads auto-created topics created");
		$this->ipsclass->main_msg = "{$fixed} IP.Downloads auto-created topics created";
		$this->main_screen();
	}
	
	
	function do_templates()
	{		
		//-----------------------------------------
		// Template here?
		//-----------------------------------------
		
		if ( ! file_exists( ROOT_PATH.'resources/idm/idm_templates.xml' ) )
		{
			$this->ipsclass->admin->error( "idm_templates.xml cannot be found in the resources/idm/ directory. Please check and re-upload or try again" );
		}
		
		require ROOT_PATH.'sources/api/api_skins.php';
		
		$api 			=  new api_skins();
		$api->ipsclass 	=& $this->ipsclass;
		
		$api->skin_add_bits( ROOT_PATH . 'resources/idm/idm_templates.xml' );
		
		if( count($api->error) )
		{
			$this->ipsclass->main_msg = "There was a problem processing the xml skin import.  The templates have not been rebuilt.";
		}
		else
		{
			$this->ipsclass->main_msg = "The XML file was imported succesfully.  You may want to navigate to Look & Feel -&gt; Skin Tools and click the 'Rebuild All' link to rebuild all of your skin caches.";
		}
		
		$this->main_screen();
	}	
	
	
	function rebuild_thumbnails()
	{
		// Set limit to do at a time...
		$limit = 20;
		
		// Load up the thumbnail library
		require_once( DL_PATH.'lib/lib_thumb.php' );
		$image = new lib_thumb();

		$image->in_type        = 'file';
		$image->out_type       = 'file';
		
		if( $this->ipsclass->vars['idm_addwatermark'] )
		{
			$image->out_file_dir   = $upload->out_file_dir;

			$image->gd_version     = $this->ipsclass->vars['gd_version'];
			$image->do_water	   = $this->ipsclass->vars['idm_addwatermark'];
			$image->water_path	   = $this->ipsclass->vars['idm_watermarkpath'];
		}
		else if( $this->ipsclass->vars['idm_addcopyright'] )
		{
			$image->out_file_dir   = $upload->out_file_dir;

			$image->gd_version     = $this->ipsclass->vars['gd_version'];
			$image->do_copy		   = $this->ipsclass->vars['idm_addcopyright'];
			$image->cpy_txt		   = $this->ipsclass->vars['idm_copyrighttext'];
		}
		
		//-----------------------------------------
		// Let's begin...
		//-----------------------------------------
				
		$fixed = $this->ipsclass->input['fixed'] ? intval($this->ipsclass->input['fixed']) : 0;
		
		$this->ipsclass->DB->cache_add_query( 'files_get_thumbs', array( 'limita' => $fixed, 'limitb' => $limit ), 'sql_idm_queries'	);
		$outer = $this->ipsclass->DB->cache_exec_query();
			
		if( $this->ipsclass->DB->get_num_rows($outer) )
		{
			while( $row = $this->ipsclass->DB->fetch_row($outer) )
			{
				$category = $this->func->cat_lookup[ $row['file_cat'] ];
				
				if( $category['coptions']['opt_thumb_x'] && $category['coptions']['opt_thumb_y'] )
				{
					switch( $row['file_storagetype'] )
					{
						case 'web':
						case 'nonweb':
							if( $row['file_thumb'] )
							{
								@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ).$row['file_thumb'] );
							}
							
							$image->in_file_dir    = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] );
							$image->out_file_dir    = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] );
							$image->in_file_name   = $row['file_ssname'];
							break;
							
						case 'db':
							$fh = @fopen( $this->ipsclass->vars['upload_dir'].$row['file_ssname'], 'wb' );
							@fputs ($fh, $row['storage_ss'], strlen($row['storage_ss']) );
							@fclose($fh);
							
							$image->in_file_dir		= $this->ipsclass->vars['upload_dir'];
							$image->out_file_dir	= $this->ipsclass->vars['upload_dir'];
							$image->in_file_name	= $row['file_ssname'];
							break;
							
						case 'ftp':
							if( $this->ipsclass->vars['idm_remoteurl'] AND
								$this->ipsclass->vars['idm_remoteport'] AND
								$this->ipsclass->vars['idm_remoteuser'] AND
								$this->ipsclass->vars['idm_remotepass'] AND
								$this->ipsclass->vars['idm_remotefilepath'] )
							{
								$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 999999 );
								if( !$conid )
								{
									continue;
								}
								$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
								if( !$check )
								{
									continue;
								}
								
								@ftp_pasv( $conid, TRUE );
								
								if( !@ftp_get( $conid, $this->ipsclass->vars['upload_dir'].$row['file_ssname'], $this->ipsclass->vars['idm_remotesspath']."/".$row['file_ssname'], FTP_BINARY ) )
								{
									continue;
								}
								@ftp_close( $conid);
								
								$image->in_file_dir    = $this->ipsclass->vars['upload_dir'];
								$image->out_file_dir    = $this->ipsclass->vars['upload_dir'];
								$image->in_file_name   = $row['file_ssname'];
							}
							break;
					}						
							
					$image->desired_width  = $category['coptions']['opt_thumb_x'];
					$image->desired_height = $category['coptions']['opt_thumb_y'];
					
					$image->out_file_name  = 'thumb-'.$row['file_ssname'];
					$return = $image->generate_thumbnail();
					
					$thumb = $return['thumb_location'];
					
					if( $thumb == $row['file_ssname'] )
					{
						// Not a thumbnail, just getting the image we sent returned
						$thumb = "";
					}
					
					if( $thumb )
					{
						switch( $row['file_storagetype'] )
						{
							case 'web':
							case 'nonweb':
								$this->ipsclass->DB->do_update( "downloads_files", array( 'file_thumb' => $thumb ), "file_id=".$row['file_id'] );
								break;
								
							case 'ftp':
								if( $this->ipsclass->vars['idm_remoteurl'] AND
									$this->ipsclass->vars['idm_remoteport'] AND
									$this->ipsclass->vars['idm_remoteuser'] AND
									$this->ipsclass->vars['idm_remotepass'] AND
									$this->ipsclass->vars['idm_remotefilepath'] )
								{
									$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 999999 );
									if( !$conid )
									{
										continue;
									}
									$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
									if( !$check )
									{
										continue;
									}
									
									@ftp_delete( $conid, $row['file_thumb'] );
									@ftp_pasv( $conid, TRUE );
									
									if( $this->ipsclass->vars['idm_remotesspath'] )
									{
										$ftp_do_thumb_upload 	= @ftp_nb_put( $conid, $this->ipsclass->vars['idm_remotesspath']."/{$thumb}", $this->ipsclass->vars['upload_dir']."/{$thumb}", FTP_BINARY );
									}
									
									@unlink( $this->ipsclass->vars['upload_dir']."/{$thumb}" );
									
									$this->ipsclass->DB->do_update( "downloads_files", array( 'file_thumb' => $thumb ), "file_id=".$row['file_id'] );
								}
								break;
								
							case 'db':
								// Get file data first
								$filedata = base64_encode( addslashes( trim( file_get_contents( $this->ipsclass->vars['upload_dir']."/".$thumb ) ) ) );
								
								if( $filedata == '' )
								{
									continue;
								}
								
								$this->ipsclass->DB->do_update( "downloads_filestorage", array( 'storage_thumb'	=> $filedata ), "storage_id=".$row['file_id']	);

								@unlink($this->ipsclass->vars['upload_dir']."/".$thumb);
								
								break;
						}
					}
				}
				$fixed++;
			}
			$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=thumbs&all=1&limit=20&fixed={$fixed}", "{$fixed} thumbnails fixed so far...", 0, 2 );
		}
		else
		{
			$this->ipsclass->admin->save_log("IP.Downloads thumbnails regenerated");
			$this->ipsclass->admin->redirect( $this->ipsclass->base_url."&{$this->ipsclass->form_code}", "All thumbnails have been re-generated", 0, 2 );
		}
	}	
		
}

?>