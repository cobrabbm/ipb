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
|   > Upgrade Module: Core functions
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .02
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class core_functions extends info
{
	var $ipsclass;

	/*-------------------------------------------------------------------------*/
	// CONSTRUCTOR
	/*-------------------------------------------------------------------------*/

	function core_functions()
	{

	}

	/*-------------------------------------------------------------------------*/
	// FINISH _EVERYTHING_
	/*-------------------------------------------------------------------------*/

	function upgrade_complete()
	{

		$this->ipsclass->template->content .= "
			<div class='tableborder'>
			 <div class='maintitle'>IDM Upgrade Complete!</div>
			 <div class='tdrow1' style='padding:6px'>You have now been upgraded!
			 <br /><br />
			 You may want to disable permissions on the script 'idm_setup/index.php' to increase security or rename the 'idm_setup' directory.
			 </div>
			</div>
			";

		$this->ipsclass->template->output();
	}

	/*-------------------------------------------------------------------------*/
	// REBUILD SETTINGS
	/*-------------------------------------------------------------------------*/

	function rebuild_settings()
	{
		if ( ! @file_exists( THIS_PATH.'idm_components.xml' ) )
		{
			$this->login_screen( "Could not find required file: idm_components.xml" );
		}

		$content = implode( '', @file( THIS_PATH.'idm_components.xml' ) );

		//-----------------------------------------
		// Get current components.
		//-----------------------------------------

		$this->ipsclass->DB->simple_construct( array( 'select' => 'com_id, com_section',
													  'from'   => 'components',
													  'order'  => 'com_id' ) );

		$this->ipsclass->DB->simple_exec();

		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$cur_components[ $r['com_section'] ] = $r['com_id'];
		}

		//-----------------------------------------
		// Get xml mah-do-dah
		//-----------------------------------------

		require( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();

		//-----------------------------------------
		// Unpack the datafile
		//-----------------------------------------

		$xml->xml_parse_document( $content );

		//-----------------------------------------
		// pArse
		//-----------------------------------------

		$fields = array( 'com_title'   , 'com_description', 'com_author' , 'com_url', 'com_version', 'com_menu_data',
						 'com_enabled' , 'com_safemode'   , 'com_section', 'com_filename', 'com_url_uri', 'com_url_title' );

		if ( ! is_array( $xml->xml_array['componentexport']['componentgroup']['component'][0]  ) )
		{
			//-----------------------------------------
			// Ensure [0] is populated
			//-----------------------------------------

			$tmp = $xml->xml_array['componentexport']['componentgroup']['component'];

			unset($xml->xml_array['componentexport']['componentgroup']['component']);

			$xml->xml_array['componentexport']['componentgroup']['component'][0] = $tmp;
		}

		foreach( $xml->xml_array['componentexport']['componentgroup']['component'] as $id => $entry )
		{
			$newrow = array();

			foreach( $fields as $f )
			{
				$newrow[$f] = $entry[ $f ]['VALUE'];
			}

			$this->ipsclass->DB->force_data_type = array( 'com_version' => 'string' );

			if ( $cur_components[ $entry['com_section']['VALUE'] ] )
			{
				//-----------------------------------------
				// Update
				//-----------------------------------------

				$this->ipsclass->DB->do_update( 'components', $newrow, 'com_id='.$cur_components[ $entry['com_section']['VALUE'] ] );
				$updated++;
			}
			else
			{
				//-----------------------------------------
				// INSERT
				//-----------------------------------------

				$newrow['com_date_added'] = time();

				$this->ipsclass->DB->do_insert( 'components', $newrow );
				$inserted++;
			}
		}

		//------------------------------------------
		// Rebuild the Blog settings
		//------------------------------------------
		$updated     = 0;
		$inserted    = 0;
		$need_update = array();

		if ( ! @file_exists( THIS_PATH.'idm_settings.xml' ) )
		{
			$this->redirect( "index.php?act=recache&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "No settings to import or update, proceeding to rebuild caches..." );
		}

		$content = implode( '', @file( THIS_PATH.'idm_settings.xml' ) );

		//-------------------------------
		// Get current settings.
		//-------------------------------

		$cur_settings = array();

		$this->ipsclass->DB->simple_construct( array( 'select' => 'conf_id, conf_key',
									  'from'   => 'conf_settings',
									  'order'  => 'conf_id' ) );

		$this->ipsclass->DB->simple_exec();

		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$cur_settings[ $r['conf_key'] ] = $r['conf_id'];
		}

		//-------------------------------
		// Get xml mah-do-dah
		//-------------------------------

		unset ( $xml );

		$xml = new class_xml();

		//-----------------------------------------
		// Get current titles
		//-----------------------------------------

		$this->setting_get_groups();

		//-------------------------------
		// Unpack the datafile
		//-------------------------------

		$xml->xml_parse_document( $content );

		//-----------------------------------------
		// pArse
		//-----------------------------------------

		$fields = array( 'conf_title'   , 'conf_description', 'conf_group'    , 'conf_type'    , 'conf_key'        , 'conf_default',
						 'conf_extra'   , 'conf_evalphp'    , 'conf_protected', 'conf_position', 'conf_start_group', 'conf_end_group',
						 'conf_help_key', 'conf_add_cache'  , 'conf_title_keyword' );

		$setting_fields = array( 'conf_title_keyword', 'conf_title_title', 'conf_title_desc', 'conf_title_noshow' );

		//-----------------------------------------
		// Fix up...
		//-----------------------------------------

		if ( ! is_array( $xml->xml_array['settingexport']['settinggroup']['setting'][0]  ) )
		{
			//-----------------------------------------
			// Ensure [0] is populated
			//-----------------------------------------

			$tmp = $xml->xml_array['settingexport']['settinggroup']['setting'];

			unset($xml->xml_array['settingexport']['settinggroup']['setting']);

			$xml->xml_array['settingexport']['settinggroup']['setting'][0] = $tmp;
		}

		//-----------------------------------------
		// Loop through and sort out settings...
		//-----------------------------------------

		foreach( $xml->xml_array['settingexport']['settinggroup']['setting'] as $id => $entry )
		{
			$newrow = array();

			//-----------------------------------------
			// Is setting?
			//-----------------------------------------

			if ( ! $entry['conf_is_title']['VALUE'] )
			{
				foreach( $fields as $f )
				{
					$newrow[$f] = $entry[ $f ]['VALUE'];
				}

				$new_settings[] = $newrow;
			}

			//-----------------------------------------
			// Is title?
			//-----------------------------------------

			else
			{
				foreach( $setting_fields as $f )
				{
					$newrow[$f] = $entry[ $f ]['VALUE'];
				}

				$new_titles[] = $newrow;
			}
		}

		//-----------------------------------------
		// Sort out titles...
		//-----------------------------------------

		if ( is_array( $new_titles ) and count( $new_titles ) )
		{
			foreach( $new_titles as $idx => $data )
			{
				if ( $data['conf_title_title'] AND $data['conf_title_keyword'] )
				{
					//-----------------------------------------
					// Get ID based on key
					//-----------------------------------------

					$conf_id = $this->setting_groups_by_key[ $data['conf_title_keyword'] ]['conf_title_id'];

					$save = array( 'conf_title_title'   => $data['conf_title_title'],
								   'conf_title_desc'    => $data['conf_title_desc'],
								   'conf_title_keyword' => $data['conf_title_keyword'],
								   'conf_title_noshow'  => $data['conf_title_noshow']  );

					//-----------------------------------------
					// Not got a row, insert first!
					//-----------------------------------------

					if ( ! $conf_id )
					{
						$this->ipsclass->DB->do_insert( 'conf_settings_titles', $save );
						$conf_id = $this->ipsclass->DB->get_insert_id();

					}
					else
					{
						//-----------------------------------------
						// Update...
						//-----------------------------------------

						$this->ipsclass->DB->do_update( 'conf_settings_titles', $save, 'conf_title_id='.$conf_id );
					}

					//-----------------------------------------
					// Update settings cache
					//-----------------------------------------

					$save['conf_title_id']                                      = $conf_id;
					$this->setting_groups_by_key[ $save['conf_title_keyword'] ] = $save;
					$this->setting_groups[ $save['conf_title_id'] ]             = $save;

					//-----------------------------------------
					// Remove need update...
					//-----------------------------------------

					$need_update[] = $conf_id;
				}
			}
		}

		//-----------------------------------------
		// Sort out settings
		//-----------------------------------------

		if ( is_array( $new_settings ) and count( $new_settings ) )
		{
			foreach( $new_settings as $idx => $data )
			{
				//-----------------------------------------
				// Make PHP slashes safe
				//-----------------------------------------

				$data['conf_evalphp'] = str_replace( '\\', '\\\\', $data['conf_evalphp'] );

				//-----------------------------------------
				// Now assign to the correct ID based on
				// our title keyword...
				//-----------------------------------------

				$data['conf_group'] = $this->setting_groups_by_key[ $data['conf_title_keyword'] ]['conf_title_id'];

				//-----------------------------------------
				// Remove from array
				//-----------------------------------------

				unset( $data['conf_title_keyword'] );

				if ( $cur_settings[ $data['conf_key'] ] )
				{
					//-----------------------------------------
					// Update
					//-----------------------------------------

					$this->ipsclass->DB->do_update( 'conf_settings', $data, 'conf_id='.$cur_settings[ $data['conf_key'] ] );
					$updated++;
				}
				else
				{
					//-----------------------------------------
					// INSERT
					//-----------------------------------------

					$this->ipsclass->DB->do_insert( 'conf_settings', $data );
					$inserted++;
				}
			}
		}

		//-----------------------------------------
		// Update group counts...
		//-----------------------------------------

		if ( count( $need_update ) )
		{
			foreach( $need_update as $id )
			{
				$conf = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'count(*) as count', 'from' => 'conf_settings', 'where' => 'conf_group='.$id ) );

				$count = intval($conf['count']);

				$this->ipsclass->DB->do_update( 'conf_settings_titles', array( 'conf_title_count' => $count ), 'conf_title_id='.$id );
			}
		}

		//-----------------------------------
		// Boink..
		//-----------------------------------

		$this->redirect( "index.php?act=recache&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "{$updated} settings updated, {$inserted} settings inserted, proceeding to rebuild caches..." );

	}

	/*-------------------------------------------------------------------------*/
	// REBUILD TEMPLATES
	/*-------------------------------------------------------------------------*/

	function rebuild_templates_cache()
	{
		//-----------------------------------
		// Get ACP library
		//-----------------------------------

		require_once( ROOT_PATH.'sources/lib/admin_cache_functions.php' );
		$acp = new admin_cache_functions();
		$acp->ipsclass =& $this->ipsclass;

		$row = $this->ipsclass->DB->simple_exec_query ( array ( 'select' => 'conf_value, conf_default', 'from' => 'conf_settings', 'where' => "conf_key='ipb_img_url'" ) );
		$this->ipsclass->vars['ipb_img_url'] = $row['conf_value'] != "" ? $row['conf_value'] : $row['conf_default'];
		if ( $this->ipsclass->vars['ipb_img_url'] == "{blank}" )
		{
			$this->ipsclass->vars['ipb_img_url'] = "";
		}

		$justdone = intval($this->ipsclass->input['justdone']);
		$justdone = $justdone ? $justdone : 1;

		//-----------------------------------
		// Get skins
		//-----------------------------------

		$this->ipsclass->DB->simple_construct( array( 'select' => '*',
													  'from'   => 'skin_sets',
													  'where'  => 'set_skin_set_id > '.$justdone,
													  'order'  => 'set_skin_set_id',
													  'limit'  => array( 0, 1 )
						     )      );

		$this->ipsclass->DB->simple_exec();

		//-----------------------------------
		// Got a biggun?
		//-----------------------------------

		$r = $this->ipsclass->DB->fetch_row();

		if ( $r['set_skin_set_id'] )
		{
			$acp->_rebuild_all_caches( array($r['set_skin_set_id']) );

			$extra = implode( "<br />", $acp->messages );

			$this->redirect( "index.php?act=templatescache&justdone={$r['set_skin_set_id']}&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "Rebuilt cache for skin set {$r['set_name']}<br />{$extra}<br />Proceeding to the next skin..." );
		}
		else
		{
			$this->redirect( "index.php?act=finish&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "No more skins to rebuild..." );
		}
	}

	/*-------------------------------------------------------------------------*/
	// REBUILD TEMPLATES
	/*-------------------------------------------------------------------------*/

	function rebuild_templates()
	{
		//-----------------------------------
		// Get XML
		//-----------------------------------

		require_once( KERNEL_PATH.'class_xml.php' );

		$xml = new class_xml();

		//-----------------------------------
		// Get XML file (TEMPLATES)
		//-----------------------------------

		$xmlfile = ROOT_PATH.'idm_templates.xml';

		$setting_content = implode( "", file($xmlfile) );

		//-------------------------------
		// Unpack the datafile (TEMPLATES)
		//-------------------------------

		$xml->xml_parse_document( $setting_content );

		//-------------------------------
		// (TEMPLATES)
		//-------------------------------

		if ( ! is_array( $xml->xml_array['templateexport']['templategroup']['template'] ) )
		{
			$this->login_screen( "Error with idm_templates.xml - could not process XML properly" );
		}

		foreach( $xml->xml_array['templateexport']['templategroup']['template'] as $id => $entry )
		{

			$row = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'suid',
																  'from'   => 'skin_templates',
																  'where'  => "group_name='{$entry['group_name']['VALUE']}' AND func_name='{$entry['func_name']['VALUE']}' and set_id=1"
														 )      );

			if ( $row['suid'] )
			{
				$this->ipsclass->DB->do_update( 'skin_templates', array( 'func_data'       => $entry[ 'func_data' ]['VALUE'],
																		 'section_content' => $entry[ 'section_content' ]['VALUE'],
																		 'updated'         => time()
																	   )
											    , 'suid='.$row['suid'] );
			}
			else
			{
				$this->ipsclass->DB->do_insert( 'skin_templates', array( 'func_data'       => $entry[ 'func_data' ]['VALUE'],
																		 'func_name'       => $entry[ 'func_name' ]['VALUE'],
																		 'section_content' => $entry[ 'section_content' ]['VALUE'],
																		 'group_name'      => $entry[ 'group_name' ]['VALUE'],
																		 'updated'         => time(),
																		 'set_id'          => 1
											  )                        );
			}
		}

		//-------------------------------
		// MACROS
		//-------------------------------

		unset($xml);
		$xml = new class_xml();

		$xmlfile = THIS_PATH.'idm_macro.xml';

		$setting_content = implode( "", file($xmlfile) );

		//-------------------------------
		// Unpack the datafile (MACRO)
		//-------------------------------

		$xml->xml_parse_document( $setting_content );

		//-------------------------------
		// (MACRO)
		//-------------------------------

		if ( ! is_array( $xml->xml_array['macroexport']['macrogroup']['macro'] ) )
		{
			$this->login_screen( "Error with idm_macro.xml - could not process XML properly" );
		}

		foreach( $xml->xml_array['macroexport']['macrogroup']['macro'] as $id => $entry )
		{
			$row = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'macro_id',
																  'from'   => 'skin_macro',
																  'where'  => "macro_value='{$entry['macro_value']['VALUE']}' and macro_set=1"
										 )      );
			if ( $row['macro_id'] )
			{
				$this->ipsclass->DB->do_update( 'skin_macro', array( 'macro_replace' => $entry['macro_replace']['VALUE'] ), "macro_value='{$entry['macro_value']['VALUE']}' and macro_set=1" );
			}
			else
			{
				$this->ipsclass->DB->do_insert( 'skin_macro', array( 'macro_value'		=> $entry['macro_value']['VALUE'],
																	 'macro_replace'	=> $entry['macro_replace']['VALUE'],
																	 'macro_set'		=> 1 ) );
			}
		}

		//-----------------------------------
		// Boink..
		//-----------------------------------

		$this->redirect( "index.php?act=templatescache&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "Master templates rebuilt, proceeding to recache templates..." );
	}

	/*-------------------------------------------------------------------------*/
	// REBUILD CACHES
	/*-------------------------------------------------------------------------*/

	function rebuild_caches()
	{
		//-------------------------------------------------------------
		// SETTINGS
		//-------------------------------------------------------------

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

			$this->ipsclass->cache['settings'][ $r['conf_key'] ] = $this->ipsclass->txt_safeslashes($value);
		}

		$this->ipsclass->update_cache( array( 'name' => 'settings', 'array' => 1, 'deletefirst' => 1 ) );


		//-------------------------------------------------------------
		// COMPONENTS
		//-------------------------------------------------------------

		$this->ipsclass->cache['components'] = array();

		$this->ipsclass->DB->simple_construct( array( 'select' => 'com_id,com_enabled,com_section,com_filename,com_url_uri,com_url_title,com_position',
													  'from'   => 'components',
													  'where'  => 'com_enabled=1',
													  'order'  => 'com_position ASC' ) );
		$this->ipsclass->DB->simple_exec();

		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['components'][] = $r;
		}

		$this->ipsclass->update_cache( array( 'name' => 'components', 'array' => 1, 'deletefirst' => 1 ) );
		
		//-------------------------------------------------------------
		// Mime-Types
		//-------------------------------------------------------------
		
		$this->ipsclass->cache['idm_mimetypes'] = array();
			
		$this->ipsclass->DB->simple_construct( array( 'select' => 'mime_id,mime_extension,mime_mimetype,mime_file,mime_screenshot,mime_inline,mime_img', 'from' => 'downloads_mime', 'where' => "mime_screenshot=1 OR mime_file=1" ) );
		$this->ipsclass->DB->simple_exec();
	
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_mimetypes'][ $r['mime_extension'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_mimetypes', 'array' => 1, 'deletefirst' => 1 ) );		
		
		//-----------------------------
		// IDM Stats
		//-----------------------------
		
		$this->ipsclass->cache['idm_stats'] = array();
		
		//-----------------------------
		// Get total file count
		//-----------------------------
				
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'COUNT(file_id) as files',
												 'from'		=> 'downloads_files',
												 'where'	=> 'file_open=1'
										)		);
		$this->ipsclass->DB->exec_query();
		
		$filecnt = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->cache['idm_stats']['total_files'] = $filecnt['files'];
		
		$this->ipsclass->DB->free_result();
		
		//-----------------------------
		// Get total category count
		//-----------------------------
		
		$cnt = $this->ipsclass->DB->simple_exec_query( array( 'select' 	=> 'COUNT(cid) as cats',
												 'from'		=> 'downloads_categories',
												 'where'	=> 'copen=1'
										)		);
		
		$this->ipsclass->cache['idm_stats']['total_categories'] = $cnt['cats'];
		
		//-----------------------------
		// Get total download count
		//-----------------------------
				
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'SUM(file_downloads) as dls',
												 'from'		=> 'downloads_files',
												 'where'	=> 'file_open=1'
										)		);
		$this->ipsclass->DB->exec_query();
		
		$dlcnt = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->cache['idm_stats']['total_downloads'] = $dlcnt['dls'];
		
		$this->ipsclass->DB->free_result();		
		
		//-----------------------------
		// Get distinct author count
		//-----------------------------		
				
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'COUNT(DISTINCT(file_submitter)) as authors',
												 'from'		=> 'downloads_files',
												 'where'	=> 'file_open=1'
										)		);
		$this->ipsclass->DB->exec_query();
		
		$authors = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->cache['idm_stats']['total_authors'] = $authors['authors'];
		
		$this->ipsclass->DB->free_result();
		
		//-----------------------------
		// Get latest file info
		//-----------------------------
		
		$this->ipsclass->DB->build_query( array( 'select' 	=> 'f.file_id, f.file_name, f.file_submitter, f.file_submitted, m.members_display_name',
												 'from'		=> 'downloads_files f LEFT JOIN ibf_members m ON (m.id=f.file_submitter)',
												 'where'	=> 'f.file_open=1',
												 'order'	=> 'f.file_submitted DESC',
												 'limit'	=> array(0,1)
										)		);
		$this->ipsclass->DB->exec_query();
		
		$fileinfo = $this->ipsclass->DB->fetch_row();
		
		$this->ipsclass->cache['idm_stats']['latest_fid'] 	= $fileinfo['file_id'];
		$this->ipsclass->cache['idm_stats']['latest_fname'] = $fileinfo['file_name'];
		$this->ipsclass->cache['idm_stats']['latest_mid'] 	= $fileinfo['file_submitter'];
		$this->ipsclass->cache['idm_stats']['latest_mname'] = $fileinfo['members_display_name'];
		$this->ipsclass->cache['idm_stats']['latest_date'] 	= $fileinfo['file_submitted'];
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_stats', 'array' => 1, 'deletefirst' => 1, 'donow' => 0 ) );

		$this->redirect( "index.php?act=templates&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "Caches rebuilt, proceeding to rebuild templates..." );
	}

	/*-------------------------------------------------------------------------*/
	// MODULE RUN - Look for next, or finish up...
	/*-------------------------------------------------------------------------*/

	function module_complete()
	{
		//------------------------------------------
		// Update DB
		//------------------------------------------

		$this->ipsclass->DB->do_insert( 'downloads_upgrade_history', array(	'idm_version_id'    => $this->current_upgrade,
												  		'idm_version_human' => $this->versions[ $this->current_upgrade ],
												  		'idm_upgrade_date'  => time(),
												  		'idm_upgrade_mid'   => $this->ipsclass->input['mid']
				      )                         	 );

		//------------------------------------------
		// Anymore to run?
		//------------------------------------------

		if ( $this->last_poss_id != $this->current_upgrade )
		{
			$this->redirect( "index.php?act=work&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}", "Upgrade module complete, moving on to the next upgrade module...." );
		}
		else
		{
			$this->ipsclass->template->content .= "
			<div class='tableborder'>";

			if ( $this->current_version == '00000' )
			{
				$this->ipsclass->template->content .= "
			 <div class='maintitle'>Invision Download Manager Installation Complete</div>
			 <div class='tdrow1' style='padding:6px'>You are now running version {$this->versions[$this->current_upgrade]}";
			}
			else
			{
				$this->ipsclass->template->content .= "
			 <div class='maintitle'>Invision Download Manager Upgrade Complete</div>
			 <div class='tdrow1' style='padding:6px'>You have now been upgraded from {$this->versions[$this->current_version]} to {$this->versions[$this->current_upgrade]}";
			}

			$this->ipsclass->template->content .= "
			 <br /><br />
			 The next few final steps will check for updated settings and recache your cached data (components, settings, etc.) and rebuild your master templates to ensure
			 that all the template additions and changes are updated.
			 <br /><br />
			 <div align='center'><span style='font-weight:bold;font-size:14px'>&raquo; <a href='index.php?act=settings&loginkey={$this->ipsclass->input['loginkey']}&securekey={$this->ipsclass->input['securekey']}&mid={$this->ipsclass->input['mid']}'>Proceed...</a></span></div>
			 </div>
			</div>
			";

			$this->ipsclass->template->output();

		}
	}

	/*-------------------------------------------------------------------------*/
	// Redirect
	/*-------------------------------------------------------------------------*/

	function redirect($url, $text, $time=2)
	{
		$this->ipsclass->template->content .= "<meta http-equiv='refresh' content=\"{$time}; url={$url}\">
										 <div class='tableborder'>
										 <div class='maintitle'>Redirecting</div>
										 <div class='tdrow1' style='padding:8px'>
										  <div style='font-size:12px'>$text
										  <br />
										  <br />
										  <center><a href='{$url}'>Click here if not redirected...</a></center>
										  </div>
										 </div>
										</div>";

		$this->ipsclass->template->output("Redirecting...");
	}

	/*-------------------------------------------------------------------------*/
	// SHOW LOG IN SCREEN
	/*-------------------------------------------------------------------------*/

	function login_screen($msg='')
	{
		if ( ! file_exists( ROOT_PATH.'idm_templates.xml' ) )
		{
			$msg .= "<div><b>Cannot locate XML templates</b><br />This should be located in your forums root directory and is called 'idm_templates.xml'. Please ensure this file is uploaded (recreating the file structure if needed) before continuing.
					 <br /><strong>Failure to upload this file will mean that your templates will not be updated.</strong></div>";
		}

		if ( $msg != "" )
		{
			$msg = "<div class='warnbox'>$msg</div><br />";
		}

		$this->ipsclass->template->content .= "
				<form action='index.php?act=login' method='post' name='theAdminForm'>
				{$msg}
				<div>
				<strong>You must log in with your forums administrative log in details to access the upgrade system.<br />";

		if ( $this->current_version > '00000' )
		{
			$this->ipsclass->template->content .= "Upgrading from {$this->versions[$this->current_version]} to  {$this->versions[$this->current_upgrade]}";
		}
		else
		{
			$this->ipsclass->template->content .= "New installation of version {$this->versions[$this->current_upgrade]}";
		}

		$this->ipsclass->template->content .= "
				</strong>
				</div>
				<br />
				<div class='tableborder'>
				<div class='maintitle'>Verification Required - Please Log In</div>
				<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
				<tr>
				<td class='tdrow1'  width='40%'  valign='middle'>Your Forums Username:</td>
				<td class='tdrow2'  width='60%'  valign='middle'><input type='text' style='width:100%' name='username' value=''></td>
				</tr>
				<tr>
				<td class='tdrow1'  width='40%'  valign='middle'>Your Forums Password:</td>
				<td class='tdrow2'  width='60%'  valign='middle'><input type='password' style='width:100%' name='password' value=''></td>
				</tr>
				<tr>
				<td class='pformstrip' colspan='2'><div align='center'><input type='submit' value='Log in' id='button' accesskey='s'></div></td>
				</tr>
				</table>
				</div>
				</form>";

		$this->ipsclass->template->output("Log In");

	}

	/*-------------------------------------------------------------------------*/
	// Authorise da membah
	/*-------------------------------------------------------------------------*/

	function get_member()
	{
		$member = array( 'id' => 0 );

		$this->loginkey  = $this->check_md5( $this->ipsclass->input['loginkey']  );
		$this->securekey = $this->check_md5( $this->ipsclass->input['securekey'] );
		$this->member_id = trim(intval($this->ipsclass->input['mid'] ) );

		if ( ! $this->loginkey or ! $this->securekey )
		{
			return $member;
		}

		$this->ipsclass->DB->query( "SELECT m.*, g.* FROM ibf_members m
					  LEFT JOIN ibf_groups g ON ( m.mgroup=g.g_id )
					 WHERE member_login_key='{$this->loginkey}' and id='{$this->member_id}'" );

		$member = $this->ipsclass->DB->fetch_row();

		return $member;
	}

	/*-------------------------------------------------------------------------*/
	// Get the current version and the next version to upgrade to..
	/*-------------------------------------------------------------------------*/

	function get_version_latest()
	{
		$this->current_version = '';
		$this->current_upgrade = '';

		//------------------------------------------
		// Copy & pop DB array and get next
		// upgrade script
		//------------------------------------------

		$tmp = $this->db_contents;

		$this->current_version = array_pop( $tmp );

		if ( !$this->current_version )
		{
			$this->current_version = '00000';
		}

		//------------------------------------------
		// Get the next upgrade script
		//------------------------------------------

		ksort( $this->dir_contents );

		foreach( $this->dir_contents as $i => $a )
		{
			if ( $this->current_version == '00000' )
			{
				if ( $a > $this->current_upgrade )
				{
					$this->current_upgrade  = $a;
					$this->modules_to_run[0] = $this->versions[ $a ];
				}
			}
			elseif ( $a > $this->current_version )
			{
				if ( ! $this->current_upgrade )
				{
					$this->current_upgrade  = $a;
				}

				$this->modules_to_run[] = $this->versions[ $a ];
			}
		}
	}

	/*-------------------------------------------------------------------------*/
	// GET INFO FROM THE DATABASE
	/*-------------------------------------------------------------------------*/

	function get_db_structure()
	{
		$vers = array();

		if ( $this->ipsclass->DB->field_exists ( "idm_upgrade_id", "downloads_upgrade_history" ) )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_upgrade_history', 'order' =>  'idm_version_id ASC' ) );
			$this->ipsclass->DB->simple_exec();

			while( $r = $this->ipsclass->DB->fetch_row() )
			{
				$vers[ $r['idm_version_id'] ] = $r['idm_version_id'];
			}
		}

		return $vers;
	}

	/*-------------------------------------------------------------------------*/
	// Get dir structure..
	/*-------------------------------------------------------------------------*/

	function get_dir_structure()
	{
		$return = array();

		//------------------------------------------
 		// Get the folder names
 		//------------------------------------------

 		$dh = opendir( THIS_PATH );

 		while ( $file = readdir( $dh ) )
 		{
			if ( is_dir( THIS_PATH."/".$file ) )
			{
				if ( $file != "." && $file != ".." )
				{
					if ( strstr( $file, 'upg_' ) )
					{
						$tmp = str_replace( "upg_", "", $file );
						$return[ $tmp ] = $tmp;
					}
				}
			}
 		}

 		closedir( $dh );

 		sort($return);

 		return $return;
	}

	/*-------------------------------------------------------------------------*/
	// Check to see if its a 'real' MD5
	/*-------------------------------------------------------------------------*/

	function check_md5($t)
	{
		$t = preg_replace( "#[^a-z0-9]#", "", trim($t) );

		if ( strlen($t) != 32 )
		{
			return '';
		}
		else
		{
			return $t;
		}
	}

	//-----------------------------------------
	// Setting get cache
	//-----------------------------------------

	function setting_get_groups()
	{
		$this->setting_groups = array();

		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'conf_settings_titles', 'order' => 'conf_title_title' ) );
		$this->ipsclass->DB->simple_exec();

		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->setting_groups[ $r['conf_title_id'] ]             = $r;
			$this->setting_groups_by_key[ $r['conf_title_keyword'] ] = $r;
		}
	}

}


?>