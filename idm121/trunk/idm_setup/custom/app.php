<?php
/**
 * IDM Module v1.2.1
 * Invision Installer Framework
 */
 
class application_installer extends class_installer
{
	var $versions			= array();
	var $db_contents		= array();
	var $dir_contents		= array();
	var $error				= array();
	var $current_version	= '';
	var $last_poss_id		= '';
	
	/**
	 * application_installer::set_requirements
	 * 
	 * Sets the requirements for this app
	 *
	 */		
	function pre_process()
	{
		if ( isset( $this->ipsclass->input['p'] ) && $this->ipsclass->input['p'] != 'overview' )
		{
			$this->ipsclass->member = $this->get_member();

			if ( ! $this->ipsclass->member['id'] )
			{
				$this->template->page_current = 'login';
				$this->template->message = "You do not have access to this tool.";
			}
			
			if ( $this->ipsclass->return_md5_check() != $this->saved_data['securekey'] )
			{
				$this->template->page_current = 'login';
				$this->template->message = "You do not have access to this tool.";
			}
			
			if ( ! $this->ipsclass->member['g_access_cp'] )
			{
				$this->template->page_current = 'login';
				$this->template->message = "You must be an admin to access this upgrade script.";
			}
			$this->ipsclass->converge->converge_load_member( $this->ipsclass->member['email'] );
		}
	}

	/*-------------------------------------------------------------------------*/
	// Get the current version and the next version to upgrade to..
	/*-------------------------------------------------------------------------*/

	function get_version_latest()
	{
		$this->current_version = '';
		$this->current_upgrade = '';

		$this->db_contents = $this->get_db_structure();

        //--------------------------------
        // Get datafile
        //--------------------------------

        require_once( ROOT_PATH . 'resources/idm/version_history.php' );
        $this->versions = $import_versions; 

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

		ksort( $this->versions );

		foreach( $this->versions as $i => $a )
		{
			if ( $this->current_version == '00000' )
			{
				if ( $i > $this->current_upgrade )
				{
					$this->current_upgrade  = $i;
					$this->modules_to_run[0] = $this->versions[ $i ];
				}
			}
			elseif ( $i > $this->current_version )
			{
				if ( ! $this->current_upgrade )
				{
					$this->current_upgrade  = $i;
				}

				$this->modules_to_run[] = $this->versions[ $i ];
			}
			
			$this->last_poss_id = $i;
		}
	}

	/*-------------------------------------------------------------------------*/
	// GET INFO FROM THE DERTABASTIC
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

 		$dh = opendir( INS_ROOT_PATH.'installfiles' );

 		while ( $file = readdir( $dh ) )
 		{
			if ( is_dir( INS_ROOT_PATH.'installfiles/'.$file ) )
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

	
	/**
	 * application_installer::cache_and_cleanup
	 * 
	 * Final install step, allows for any remaining app specific functions
	 *
	 */		
	function cache_and_cleanup()
	{
		//-------------------------------------------------------------
		// Group Cache
		//-------------------------------------------------------------
		
		$output[] = "Rebuilding group cache...";
		
		require_once( ROOT_PATH.'sources/action_admin/groups.php' );
		$lib           =  new ad_groups();
		$lib->ipsclass =& $this->ipsclass;
		$lib->rebuild_group_cache();
		unset( $lib );
		
		//-------------------------------------------------------------
		// Mime-Types
		//-------------------------------------------------------------
		
		$output[] = "Rebuilding mime-types cache...";
		
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
		
		$output[] = "Rebuilding stats cache...";
		
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

		return $output;
	}
}

?>