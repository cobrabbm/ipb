<?php
/**
 * IDM Module v1.2.1
 * Action controller for install page
 */

class action_install
{
	var $install;
	
	function action_install( & $install )
	{
		$this->install =& $install;
	}
	
	// SQL - ADMIN - CONF > SETTINGS > ACPPERMS > TEMPLATES > OTHER [ Email Templates ] > Build Caches
	
	function run()
	{
		if ( isset( $this->install->ipsclass->input['helpfile'] ) && $this->install->ipsclass->input['helpfile'] )
		{
			$this->install->saved_data['helpfile'] = 1;
		}

		$this->install->get_version_latest();

		/* Switch */
		switch( $this->install->ipsclass->input['sub'] )
		{
			case 'sql':
				$this->install_sql();
			break;

			case 'finish':
				$this->install_finish();
			break;
			
			case 'settings':
				$this->install_settings();
			break;
			
			case 'templates':
				$this->install_templates();
			break;
			
			case 'protemplates':
				$this->install_protemplates();
			break;
			
			case 'caches':
				$this->install_caches();
			break;
			
			default:
				/* Output */
				$this->install->template->append( $this->install->template->install_page() );		
				$this->install->template->next_action = '?p=install&sub=sql';
				$this->install->template->hide_next   = 1;
			break;	
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Install: Pro Skin Templates
	/*-------------------------------------------------------------------------*/
	/**
	* Install pro skin templates
	*
	* @return void
	*/
	function install_protemplates()
	{
		$proskin = $this->install->ipsclass->DB->build_and_exec_query( array( 'select' => 'set_skin_set_id, set_image_dir', 'from' => 'skin_sets', 'where' => "set_key='ip.board_pro'" ) );
		
		if( !$proskin['set_skin_set_id'] OR !file_exists( ROOT_PATH . 'resources/idm/idm_pro_templates.xml' ) )
		{
			$this->install->template->append( $this->install->template->install_page_refresh( array( 'IP.Board Pro skin not found, skipping IP.Board Pro skin update...' ) ) );	
			$this->install->template->next_action = '?p=install&sub=caches';
			$this->install->template->hide_next   = 1;
			return;
		}
		
		//-----------------------------------------
		// Copy new images over...
		//-----------------------------------------
		
		if( is_dir( ROOT_PATH . 'resources/idm/idm_pro_images' ) )
		{
			$this->copy_folder( ROOT_PATH . 'resources/idm/idm_pro_images', ROOT_PATH . 'style_images/' . $proskin['set_image_dir'] );
		}
		
		//-----------------------------------------
		// Clear existing pro gallery templates
		//-----------------------------------------
		
		$this->install->ipsclass->DB->build_and_exec_query( array( 'delete' => 'skin_templates', 'where' => "group_name LIKE 'skin_downloads%' AND set_id={$proskin['set_skin_set_id']}" ) );
		
		//-----------------------------------------
		// Grab xml class
		//-----------------------------------------

		require_once( KERNEL_PATH.'class_xml.php' );
		$xml 				= new class_xml();
		$xml->lite_parser 	= 1;
		
		//-------------------------------
		// Get file contents
		//-------------------------------		
		
		$skin_content = file_get_contents( ROOT_PATH . 'resources/idm/idm_pro_templates.xml' );
		
		//-------------------------------
		// Unpack the datafile (TEMPLATES)
		//-------------------------------

		$xml->xml_parse_document( $skin_content );		
		
		//-------------------------------
		// (TEMPLATES)
		//-------------------------------
		
		if ( ! is_array( $xml->xml_array['templateexport']['templategroup']['template'][0] ) )
		{
			//-----------------------------------------
			// Ensure [0] is populated
			//-----------------------------------------
			
			$tmp = $xml->xml_array['templateexport']['templategroup']['template'];
			
			unset($xml->xml_array['templateexport']['templategroup']['template']);
			
			$xml->xml_array['templateexport']['templategroup']['template'][0] = $tmp;
		}
		
		if ( is_array( $xml->xml_array['templateexport']['templategroup']['template'] ) )
		{
			foreach( $xml->xml_array['templateexport']['templategroup']['template'] as $id => $entry )
			{
				$this->install->ipsclass->DB->allow_sub_select = 1;
				
				$this->install->ipsclass->DB->do_insert( 'skin_templates', array( 'func_data'       => $entry[ 'func_data' ]['VALUE'],
																		 'func_name'       => $entry[ 'func_name' ]['VALUE'],
																		 'section_content' => $entry[ 'section_content' ]['VALUE'],
																		 'group_name'      => $entry[ 'group_name' ]['VALUE'],
																		 'group_names_secondary'	=> $entry[ 'group_names_secondary' ]['VALUE'],
																		 'updated'         => time(),
																		 'set_id'          => $proskin['set_skin_set_id']
											  )                        );
			}
		}
		
		$output[] = "IP.Board Pro skin images and templates succesfully updated...";

		//-----------------------------------------
		// Next...
		//-----------------------------------------
		
		$this->install->template->append( $this->install->template->install_page_refresh( $output ) );	
		$this->install->template->next_action = '?p=install&sub=caches';
		$this->install->template->hide_next   = 1;
	}
	
	
	function copy_folder( $source, $dest )
	{
		if( is_file($source) ) 
		{
			return @copy($source, $dest);
		}
	 
	    if( !is_dir($dest) )
	    {
			@mkdir($dest);
		}
	 
		$dir = @opendir( $source );

	    while (false !== ( $file = @readdir( $dir ) ) )
		{
			if( $file == '.' OR $file == '..' ) 
			{
	            continue;
	        }
	 
			if( $dest !== $source . '/' . $file ) 
			{
	            $this->copy_folder( $source . '/' . $file , $dest . '/' . $file );
	        }
	    }
	 
		@closedir( $dir );

	    return true;
	}
	
	/*-------------------------------------------------------------------------*/
	// Installs the SQL
	/*-------------------------------------------------------------------------*/
	/**
	* Installs SQL schematic
	*
	* @return void
	*/
	function install_sql()
	{
		$SQL 	= array();
		$cnt 	= 0;
		$error	= array();
		$output	= array();
		
		$st = $this->install->ipsclass->input['st'] ? intval($this->install->ipsclass->input['st']) : 0;
		$dis_cnt = $this->install->ipsclass->input['dis'] ? intval($this->install->ipsclass->input['dis']) : 0;		

		if ( $this->install->current_version == '00000' )
		{
			if ( file_exists( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_tables.php' ) )
			{
				require_once( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_tables.php' );
			}
			if ( $this->install->ipsclass->DB->sql_can_fulltext() && file_exists( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_ftindexes.php' ) )
			{
				require_once( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_ftindexes.php' );
			}
			if ( file_exists( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_inserts.php' ) )
			{
				require_once( INS_ROOT_PATH.'installfiles/install/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_inserts.php' );
			}
		}									
		else
		{
			if ( file_exists( INS_ROOT_PATH.'installfiles/upg_'.$this->install->current_upgrade.'/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_updates.php' ) )
			{
				require_once ( INS_ROOT_PATH.'installfiles/upg_'.$this->install->current_upgrade.'/'.strtolower($this->install->ipsclass->vars['sql_driver']).'_updates.php' );
			}
		}

		// Create/Alter tables
		if ( count( $SQL ) > 0 )
		{
			$this->install->ipsclass->DB->return_die = 1;
			
			// 1000 Queries at a time....
			
			for( $i=$st;$i<$st+1000;$i++ )
			{
				$this->install->ipsclass->DB->allow_sub_select 	= 1;
				$this->install->ipsclass->DB->error				= '';
				
				if( $i > count($SQL) )
				{
					break;
				}
				
				if( !isset($SQL[$i]) OR empty($SQL[$i]) )
				{
					break;
				}				

				if ( ! $this->install->ipsclass->DB->query( $SQL[$i] ) )
		        {
		        	$error[] = $SQL[$i]."<br /><br />".$this->install->ipsclass->DB->error;
		        }

				$cnt++;
			}
		}
		
		$dis_cnt += $cnt;
		
		$output[] = "$cnt queries run (up to {$dis_cnt} total queries run so far)....";
		
		if ( count( $error ) > 0 )
		{
			$message = count($output) ? implode( "<br />", $output ) : "Proceeding with update";
			
			$this->install->template->warning( array_merge( array( $message ),
															array( 'Error in upgrade '.$this->install->versions[ $this->install->current_upgrade ]. ' (' . $this->install->current_upgrade . ')' ),
															array( "<span style='color:red'>".count($error).' errors found</span>' ),
															$error ) );
			$this->install->template->in_error   = 1;
			
			$next = $st + $cnt;
			$this->install->template->next_action = '?p=install&sub=sql&st=' . $next . '&dis=' . $dis_cnt;
			
			return;
		}

		//--------------------------------
		// Next page...
		//--------------------------------

		if( $cnt > 0 )
		{
			$next = $st + $cnt;
			$this->install->template->next_action = '?p=install&sub=sql&st=' . $next . '&dis=' . $dis_cnt;
			$this->install->template->append( $this->install->template->install_page_refresh( $output ) );
			$this->install->template->hide_next = 1;
		}
		else
		{
			$this->install->template->next_action = '?p=install&sub=finish';
			$this->install->template->append( $this->install->template->install_page_refresh( $output ) );
			$this->install->template->hide_next = 1;
		}			
	}

	/*-------------------------------------------------------------------------*/
	// Finishes the version
	/*-------------------------------------------------------------------------*/
	/**
	* Runs version upgrade script and finishes the version
	*
	* @return void
	*/
	function install_finish()
	{				
		$continue = 0;
		if ( $this->install->current_version == '00000' )
		{
			$upg_file = INS_ROOT_PATH.'installfiles/install/version_install.php';
		}
		else
		{
			$upg_file = INS_ROOT_PATH.'installfiles/upg_'.$this->install->current_upgrade.'/version_upgrade.php';
		}			

		if ( file_exists( $upg_file ) )
		{
			require_once( $upg_file );
			$upgrade = new version_upgrade( $this->install );
			$result = $upgrade->auto_run();
			
			if ( count( $this->install->error ) > 0 )
			{
				$this->install->template->warning( array_merge( array( 'Error in upgrade '.$this->install->versions[ $this->install->current_upgrade ]. ' (' . $this->install->current_upgrade . ')' ),
																$this->install->error ) );
				$this->install->template->hide_next   = 1;
				return;
			}
			
			if ( $result )
			{
				//------------------------------------------
				// Update DB
				//------------------------------------------
				$this->install->ipsclass->DB->do_insert( 'downloads_upgrade_history', array(	'idm_version_id'    => $this->install->current_upgrade,
																				  				'idm_version_human' => $this->install->versions[ $this->install->current_upgrade ],
																				  				'idm_upgrade_date'  => time(),
																				  				'idm_upgrade_mid'   => $this->install->saved_data['mid']
						     						   )                         	  );

				if ( $this->install->message ) $output[] = $this->install->message;
				$output[] = "Succesfully upgraded to version {$this->install->current_upgrade}";
			}
			else
			{
				if ( $this->install->message ) $output[] = $this->install->message;
				$continue = 1;
			}
		}
		else
		{
				//------------------------------------------
				// Update DB
				//------------------------------------------
				if ( $this->install->current_upgrade )
				{
					$this->install->ipsclass->DB->do_insert( 'downloads_upgrade_history', array(	'idm_version_id'    => $this->install->current_upgrade,
																					  				'idm_version_human' => $this->install->versions[ $this->install->current_upgrade ],
																					  				'idm_upgrade_date'  => time(),
																					  				'idm_upgrade_mid'   => $this->install->saved_data['mid']
							     						   )                         	  );
	
					$output[] = "Succesfully upgraded to version {$this->install->current_upgrade}";
				}
		}
			
		//-----------------------------------------
		// Next...
		//-----------------------------------------
		if ( $continue )
		{
			$this->install->template->next_action = '?p=install&sub=sql';
		}
		elseif ( $this->install->current_upgrade >= $this->install->last_poss_id || $this->install->current_upgrade == 0 )
		{
			$this->install->template->next_action = '?p=install&sub=settings';
		}
		else
		{
			$this->install->template->next_action = '?p=install&sub=sql';
		}
		$this->install->template->append( $this->install->template->install_page_refresh( $output ) );
		$this->install->template->hide_next   = 1;
	}
	
	/*-------------------------------------------------------------------------*/
	// Installs the settings
	/*-------------------------------------------------------------------------*/
	/**
	* Installs SQL schematic
	*
	* @return void
	*/
	function install_settings()
	{
		//-----------------------------------------
		// Install components
		//-----------------------------------------
		$output[] = "Updating component...";

		require_once( ROOT_PATH . 'sources/api/api_components.php' );
		$api =  new api_components();
		$api->ipsclass =& $this->install->ipsclass;
		$api->api_init();

		$api->acp_component_import( ROOT_PATH . 'resources/idm/idm_components.xml' );
		unset($api );
		
		
		//-----------------------------------------
		// Install tasks
		//-----------------------------------------
		$output[] = "Updating tasks...";

		require_once( ROOT_PATH . 'sources/api/api_tasks.php' );
		$api =  new api_tasks();
		$api->ipsclass =& $this->install->ipsclass;
		$api->api_init();

		$api->add_task( ROOT_PATH . 'resources/idm/idm_tasks.xml' );
		unset($api );
		
		
		//-----------------------------------------
		// Install ACP restrictions
		//-----------------------------------------
		$output[] = "Updating ACP restrictions...";

		require_once( ROOT_PATH . 'sources/action_admin/acppermissions.php' );
		$settings           =  new ad_acppermissions();
		$settings->ipsclass =& $this->install->ipsclass;
		$this->install->ipsclass->input['file_location'] = 'resources/idm/idm_acpperms.xml';
		$settings->acpperms_xml_import( 1 );


		//-----------------------------------------
		// Install FAQ
		//-----------------------------------------
		$output[] 	= "Updating FAQ information...";
		$xml 		= new class_xml();
		$xml->lite_parser = 1;

		$updatehelp = ( isset( $this->install->saved_data['helpfile'] ) && $this->install->saved_data['helpfile'] ) ? 0 : 1;
		
		$content = implode( "", file( ROOT_PATH . 'resources/idm/idm_faq.xml' ) );
		
		if( $content )
		{
			$xml->xml_parse_document( $content );
			
			foreach( $xml->xml_array['export']['group']['row'] as $id => $entry )
			{
				$newrow = array();
				foreach( $entry as $f => $data )
				{
					if ( $f == 'VALUE' or $f == 'id' )
					{
						continue;
					}
					
					$newrow[$f] = $entry[ $f ]['VALUE'];
				}
	
				if ( $newrow['title'] )
				{
					$cur_faq = $this->install->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'id', 'from' => 'faq', 'where' => "title = '".$this->install->ipsclass->DB->add_slashes( $newrow['title'] )."'" ) );

					if ( $cur_faq['id'] )
					{
						if ( $updatehelp )
						{
							$this->install->ipsclass->DB->do_update( 'faq', $newrow, "id = ".$cur_faq['id'] );
						}
					}
					else
					{
						$this->install->ipsclass->DB->do_insert( 'faq', $newrow );
					}
				}
			}
		}
		
		//-----------------------------------------
		// Install settings
		//-----------------------------------------
		$output[] = "Updating settings...";
		
		require_once( ROOT_PATH . 'sources/api/api_settings.php' );
		$api =  new api_settings();
		$api->ipsclass =& $this->install->ipsclass;
		$api->api_init();

		$api->update_settings( ROOT_PATH . 'resources/idm/idm_settings.xml' );
		
		//-----------------------------------------
		// Next...
		//-----------------------------------------
		
		$this->install->template->append( $this->install->template->install_page_refresh( $output ) );
		$this->install->template->next_action = '?p=install&sub=templates';
		$this->install->template->hide_next   = 1;
	}
	
	/*-------------------------------------------------------------------------*/
	// Install: Templates
	/*-------------------------------------------------------------------------*/
	/**
	* Install templates
	*
	* @return void
	*/
	function install_templates()
	{
		$this->install->ipsclass->DB->build_and_exec_query( array( 'delete' => 'skin_templates', 'where' => "group_name='skin_downloads' AND set_id=1" ) );
		
		//-----------------------------------------
		// Install skin templates / macros
		//-----------------------------------------
		require_once( ROOT_PATH . 'sources/api/api_skins.php' );
		$api =  new api_skins();
		$api->ipsclass =& $this->install->ipsclass;
		$api->api_init();

		$this->install->ipsclass->DB->allow_sub_select = 1;
		$output[] = "Inserting templates...";
		$api->skin_add_bits( ROOT_PATH . 'resources/idm/idm_templates.xml' );

		$this->install->ipsclass->DB->allow_sub_select = 1;
		$output[] = "Inserting macros...";
		$api->skin_add_macros( ROOT_PATH . 'resources/idm/idm_macro.xml' );

		//-----------------------------------------
		// Next...
		//-----------------------------------------
		
		$this->install->template->append( $this->install->template->install_page_refresh( $output ) );	
		$this->install->template->next_action = '?p=install&sub=protemplates';
		$this->install->template->hide_next   = 1;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Install: Caches
	/*-------------------------------------------------------------------------*/
	/**
	* Install Caches
	*
	* @return void
	*/
	function install_caches()
	{
		//-----------------------------------------
		// Do Caches
		//-----------------------------------------
		require_once( ROOT_PATH . 'sources/api/api_skins.php' );
		$api =  new api_skins();
		$api->ipsclass =& $this->install->ipsclass;
		$api->api_init();

		if ( isset( $this->install->ipsclass->input['sid'] ) )
		{
			if ( $this->install->ipsclass->input['sid'] == 0 )
			{
				$output = $this->install->cache_and_cleanup();
				$this->install->template->next_action = '?p=done';
			}
			else
			{
				$messages = $api->skin_rebuild_caches( intval( $this->install->ipsclass->input['sid'] ) );
				$output = $messages['messages'];
				$this->install->template->next_action = '?p=install&sub=caches&sid='.$messages['completed'];
			}
		}
		else
		{
				$messages = $api->skin_rebuild_caches( 0 );
				$output = $messages['messages'];
				$this->install->template->next_action = '?p=install&sub=caches&sid='.$messages['completed'];
		}
					
		//-----------------------------------------
		// Next...
		//-----------------------------------------
		
		$this->install->template->hide_next   = 1;		
		$this->install->template->append( $this->install->template->install_page_refresh( $output ) );		
	}

}

?>