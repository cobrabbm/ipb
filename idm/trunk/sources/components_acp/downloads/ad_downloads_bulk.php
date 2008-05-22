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
|   > Bulk Import Manager
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .03
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class bulk_admin {

	var $ipsclass;
	var $catlib;
	
	
	var $valid_types		= array();
	var $image_dir			= '';
	var $lasttime			= 0;
	

	function init()
	{
		$this->ipsclass->init_load_cache( array( 'idm_mimetypes' ) );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'set_image_dir', 'from' => 'skin_sets', 'where' => 'set_default=1' ) );
		$this->ipsclass->DB->simple_exec();
		
		$image_set = $this->ipsclass->DB->fetch_row();
		$this->image_dir = $image_set['set_image_dir'];
		
		switch( $this->ipsclass->input['code'] )
		{
			case 'bulkZip':
				switch( $this->ipsclass->input['op'] )
				{
					case 'del':
						$this->deleteZipFile();
					break;
					
					case 'upload':
						$this->uploadZipFile();
					break;
					
					case 'zipListAll':
						$this->zipListAll();
					break;
					
					case 'zipIndexAdd':
						$this->zipIndexAdd();
					break;
					
					default:
						$this->zipFile();
					break;
				}
			break;

			case 'bulkDir':
			default:
				switch( $this->ipsclass->input['op'] )
				{
					case 'doBulkAdd':
						$this->doBulkAdd();
					break;
					
					case 'viewDir':
						$this->bulkViewDir();
					break;
					
					case 'viewDirFiles':
						$this->bulkViewFiles();
					break;
					
					default:
						$this->bulkAddForm();
					break;
				}
			break;
		}
		
		$this->ipsclass->admin->output();
	}


	/*-------------------------------------------------------------------------*/
	// Tools -> Zip File Upload
	/*-------------------------------------------------------------------------*/

	function uploadZipFile()
	{				
		require_once KERNEL_PATH . 'class_upload.php';
		$upload = new class_upload();
		
		$upload->upload_form_field 	= 'zipup';
		$upload->allowed_file_ext	= array( 'zip' );
		$upload->out_file_dir 		= $this->ipsclass->vars['upload_dir'];
		
		$upload->upload_process();
		
		if ( $upload->error_no )
		{
			switch( $upload->error_no )
			{		
				case 1:
					$this->ipsclass->main_msg = 'No file was found to upload';
				break;
				
				case 2:
					$this->ipsclass->main_msg = 'Only zip files are supported with this tool';
				break;
				
				case 4:
					$this->ipsclass->main_msg = 'There was a problem attempting to move the file to: ' . $this->ipsclass->vars['upload_dir'];
				break;
				
				default:
					$this->ipsclass->main_msg = 'There was a problem attempting to upload the file (it may be too large to upload on your server, for example)';
				break;
			}
		}
		else
		{
			$this->ipsclass->main_msg = 'Upload completed succesfully!';
		}
		
		$this->zipFile();
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Delete zip file
	/*-------------------------------------------------------------------------*/
	
	function deleteZipFile()
	{
	 	@unlink( $this->ipsclass->vars['upload_dir'] . '/' . $this->ipsclass->input['zip'] );
	 	
	 	$this->ipsclass->main_msg = 'The zip file has been deleted';
	 	
	 	$this->zipFile();
 	}
	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Zip Import Start
	/*-------------------------------------------------------------------------*/

	function zipFile()
	{
		$this->ipsclass->admin->page_title	= 'ZIP Import';

		//-----------------------------
		// Get the zip library
		//-----------------------------
		
		require( DL_PATH . 'lib/pclzip.lib.php' );
		
		$this->ipsclass->adskin->td_header[] = array( '&nbsp;'			, '5%' );
		$this->ipsclass->adskin->td_header[] = array( 'Archive Name' 	, '40%' );
		$this->ipsclass->adskin->td_header[] = array( 'File Count'		, '10%' );
		$this->ipsclass->adskin->td_header[] = array( 'Archive Size'	, '10%' );
		$this->ipsclass->adskin->td_header[] = array( 'Options'			, '35%' );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Listing zip files in directory: ' . $this->ipsclass->vars['upload_dir'] );

		$cat	= $this->ipsclass->input['cat']		? '&amp;cat=' . $this->ipsclass->input['cat']		: '';

		//-----------------------------
		// Find zip files
		//-----------------------------
		
		if( is_dir( $this->ipsclass->vars['upload_dir'] ) ) 
		{
		 	if( $dh = opendir( $this->ipsclass->vars['upload_dir'] ) )
		 	{
			 	while( ( $file = readdir( $dh ) ) !== FALSE )
			 	{
					if( strtolower( array_pop( explode( ".", $file ) ) ) == 'zip' )
					{
						$zip = new PclZip( $this->ipsclass->vars['upload_dir'] . '/' . $file );
						$info = $zip->properties();

						$comment = $info['comment'] ? '<br /><i>Comment: ' . $info['comment'] . '</i>' : '';

						$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																							"<center><img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->image_dir}/folder_mime_types/zip.gif' /></center>",
																							$file . $comment,
																							'<center>' . $info['nb'] . '</center>',
																							$this->ipsclass->size_format( filesize( $this->ipsclass->vars['upload_dir'] . '/' .  $file ) ),
																							"<center><a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkZip&amp;op=zipListAll&amp;zip={$file}' class='fauxbutton'>Import Files</a>&nbsp;<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkZip&amp;op=del&amp;zip={$file}' class='fauxbutton'><font color='red'>Delete Zip File</font></a></center>",
									 												)	 	);					
					}						 
			 	}
			 	
			 	closedir($dh);
		 	}
		}
		else
		{
		 	$this->ipsclass->admin->error( 'We could not find your configured uploads directory' );
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		if ( SAFE_MODE_ON OR $this->ipsclass->vars['safe_mode_skins'] )
		{
			$this->ipsclass->html .= sprintf( '<b>SAFE MODE ON:</b> The upload functions will not operate as you are running PHP in safe mode.  You will need to upload your zip files via FTP to the following directory: %s', $dir );
		}
		else
		{
		 	$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 
																				1 => array( 'code' 		, 'bulkZip' 	),
																				2 => array( 'act'		, 'downloads'	),
																				3 => array( 'req'		, 'tools'		),
																				4 => array( 'op' 		, 'upload' ),
																				5 => array( 'section'	, $this->ipsclass->section_code ),
													 					) , "uploadform", " enctype='multipart/form-data'"	);

			$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Upload a zip file' );
			
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<input type='file' name='zipup' size='30' />" ) );
								 
			$this->ipsclass->html .= $this->ipsclass->adskin->end_form( 'Upload' );
			$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		}
	}

	/*-------------------------------------------------------------------------*/
	// Tools -> Zip Import List Files
	/*-------------------------------------------------------------------------*/

	function zipListAll()
	{
		$zip = trim($this->ipsclass->input['zip']);

		$this->ipsclass->admin->page_title	= sprintf( 'Listing files in &#39;%s&#39;', $zip );

		$chkall = "<input id='checkall' type='checkbox' onclick='toggleselectall();' title='Check/Uncheck all' />";
		
		$this->ipsclass->adskin->td_header[] = array( $chkall			, '15%' );
		$this->ipsclass->adskin->td_header[] = array( '&nbsp;'			, '5%' );
		$this->ipsclass->adskin->td_header[] = array( 'File Name'		, '40%' );
		$this->ipsclass->adskin->td_header[] = array( 'File Size'		, '30%' );
		$this->ipsclass->adskin->td_header[] = array( 'Imported?'		, '10%' );

		$this->ipsclass->html .= $this->zip_js();

		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 
																			1 => array( 'code' 		, 'bulkZip' ),
								 											2 => array( 'act'		, 'downloads'	 ),
																			3 => array( 'req' 		, 'tools' ),
																			4 => array( 'zip'		, $zip ),
																			5 => array( 'op'		, 'zipIndexAdd' ),
																			6 => array( 'section'	, $this->ipsclass->section_code ) 
																	), 'importZip', '', 'importZip' );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'The following files were found in the zip' );

		//-----------------------------
		// Get the zip library
		//-----------------------------
		
		$contents = '';
		
		require( DL_PATH . 'lib/pclzip.lib.php' );		
		$zip = new PclZip( $this->ipsclass->vars['upload_dir'] . '/' . $zip );

		$contents = $zip->listContent();

		if( is_array($contents) AND count($contents) )
		{
			foreach( $contents as $file )
			{
				//-----------------------------
			 	// Is this a valid image type?
			 	//-----------------------------
			 	
			 	$type = strtolower( array_pop( explode( ".", $file['filename'] ) ) );
	
			 	if( !array_key_exists( $type, $this->ipsclass->cache['idm_mimetypes'] ) )
			 	{
				 	continue;
			 	}
	
			 	//-----------------------------
			 	// Folders inside zip?
			 	//-----------------------------
			 	
			 	$file['filename'] = array_pop( explode( "/", $file['filename'] ) );

			 	$this->ipsclass->DB->build_query( array( 'select' => 'file_id, file_size', 'from' => 'downloads_files', 'where' => "file_realname='".$this->ipsclass->parse_clean_value( $file['filename'] )."'" ) );
				$this->ipsclass->DB->exec_query();
	
			 	if( $this->ipsclass->DB->get_num_rows() )
			 	{
				 	$i = $this->ipsclass->DB->fetch_row();
	
				 	$txt = ( $i['file_size'] == $file['size'] ) ? 'Yes' : 'Maybe';
	
				 	$in_idm = "<a href='{$this->ipsclass->vars['board_url']}/index.php?autocom=downloads&amp;showfile={$i['file_id']}' target='_blank'>{$txt}</a>";
				 	$chk = 0;
			 	}
			 	else
			 	{
				 	$in_idm = 'No';
				 	$chk = 1;
				}
				
			 	$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																					'<center>'.$this->ipsclass->adskin->form_checkbox( 'extract_'.$file['index'], $chk, $file['index'] ).'</center>',
																					"<center><img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->image_dir}/" . $this->ipsclass->cache['idm_mimetypes'][ $type ]['mime_img'] . "' /></center>",
																					$file['filename'],
																					$this->ipsclass->size_format( $file['size'] ),
																					'<center>' . $in_idm . '</center>',
																			)	 	);				 
			}
		}

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'Checked files will be imported', 'left', 'tablerow4' );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		$this->ipsclass->adskin->td_header[] = array( '{none}', '40%' );
		$this->ipsclass->adskin->td_header[] = array( '{none}', '60%' );	
				
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Import Options' );
	 
		//-----------------------------
		// Category Selector
		//-----------------------------
		
		$categories 	= $this->catlib->cat_jump_list( 1, 'none', array( $this->ipsclass->input['cat'] ) );
		$category_opts	= '';
		
		if( count($categories) )
		{
			foreach( $categories as $cat )
			{
				$category_opts .= "<option value='{$cat[0]}'>{$cat[1]}</option>\n";
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																			'<b>Select a category</b><div class=\'desctext\'>All of the files will be imported into the selected category</div>',
																			"<select name='cat'>{$category_opts}</select>",
								 									)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
																			'<b>File Owner</b><div class=\'desctext\'>All of the files will be assigned to the specified user (begin typing in a username and then select the user from the drop down)</div>', 
																			"<input type='text' id='mem_name' name='mem_name' value='' autocomplete='off' style='width:210px;' class='textinput' />" 
																	) 		);
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( 'Import' );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		
		$this->ipsclass->html .= "<script type='text/javascript'>
									 // INIT find names
									 init_js( 'importZip', 'mem_name', 'get-member-names' );
									 // Run main loop
									 var tmp = setTimeout( 'main_loop()', 10 );
								 </script>";

	}
	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Recursive Folder Lookup (internal)
	/*-------------------------------------------------------------------------*/

	function _recurse_folder_lookup( $files_dir, $files=array() )
	{
		$files_dir = preg_replace( "#/$#", "", $files_dir );
		
		$dh = opendir( $files_dir );
		
		while( ( $filename = readdir( $dh ) ) !== FALSE )
		{
		 	if ( ( $filename != '.' ) && ( $filename != '..' ) )
		 	{
		 		@chmod( $files_dir . '/' . $filename, 0777 );
		 	
		 		if ( is_dir( $files_dir . '/' . $filename ) )
		 		{
		 			$files = array_merge( $files, $this->_recurse_folder_lookup( $files_dir . '/' . $filename, $files ) );
		 		}
		 		else
		 		{
					$type = strtolower( array_pop( explode( ".", $filename ) ) );
					
					if( array_key_exists( $type, $this->ipsclass->cache['idm_mimetypes'] ) )
					{
						$files[] = $files_dir . '/' . $filename;
					}
			 	}
		 	}
		}
		
		closedir( $dh );
		
		return $files;
	}
	

	/*-------------------------------------------------------------------------*/
	// Tools -> Add Zip Files
	/*-------------------------------------------------------------------------*/

	function zipIndexAdd()
	{
		if( !$this->ipsclass->input['cat'] )
		{
		 	$this->ipsclass->admin->error( 'You did not select a category to import the files into' );
		}

		$this->ipsclass->input['num'] 		= intval($this->ipsclass->input['num']) > 0 ? intval($this->ipsclass->input['num']) : 10;
		$this->ipsclass->input['st'] 		= intval($this->ipsclass->input['st']) > 0 ? intval($this->ipsclass->input['st']) : 0;
		$this->ipsclass->input['remove']	= 0;

		if( empty( $this->ipsclass->input['mem_name'] ) )
		{
			$this->ipsclass->admin->error( 'There was no member name set to import the files under' );
		}
		
		$dir	 	= $this->ipsclass->vars['upload_dir'] . '/';
		$files_dir 	= $dir . 'temp/';

		if( !$this->ipsclass->input['st'] )
		{
			$extract = array();
			
			foreach( $this->ipsclass->input as $k => $v )
			{
				if( preg_match( "/^extract_(\d+?)$/", $k, $matches ) )
				{
					$extract[] = $v;
				}
			}
			
			if( !count($extract) )
			{
			 	$this->ipsclass->admin->error( 'There were no files selected to import' );
			}
		

			$zip	 	= $this->ipsclass->input['zip'];
			
			if ( file_exists( $files_dir ) )
			{
				$this->ipsclass->admin->rm_dir( $files_dir );
			}
			
			@mkdir( $files_dir );
			@chmod( $files_dir, 0777 );
	
			//-----------------------------
			// Get the zip library
			//-----------------------------
			
			require( DL_PATH . 'lib/pclzip.lib.php' );	 
			$zip = new PclZip( $dir . $zip );
	
			foreach( $extract as $idx )
			{
			 	$zip->extractByIndex( $idx, PCLZIP_OPT_PATH, $files_dir );
			}
		}

		//-----------------------------
		// Take a look in the directory
		//-----------------------------
		
		$zipfiles 	= $this->_recurse_folder_lookup( $files_dir );
		$files		= array();
		
		if( is_array($zipfiles) AND count($zipfiles) )
		{
			foreach( $zipfiles as $id => $name )
			{
				if( !in_array( str_replace( $files_dir, '', $name ), $files ) )
				{
					$files[] = str_replace( $files_dir, '', $name );
				}
			}
		}

		$mem		= array();
		$category	= array();
		
		$this->ipsclass->input['cat'] = intval($this->ipsclass->input['cat']);
		
	 	$category = $this->catlib->cat_lookup[ $this->ipsclass->input['cat'] ];
	 	
	 	if( !$category['coptions']['opt_disfiles'] )
	 	{
		 	$this->ipsclass->admin->error( 'Files are not displayed in this category - please choose a different category or edit this category to display files' );
	 	}

		
		if ( !count( $mem ) AND $this->ipsclass->input['mem_name'] )
		{
			$mem = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'id as member_id, members_display_name',
																	 'from'		=> 'members',
																	 'where' 	=> "members_l_display_name='".strtolower($this->ipsclass->input['mem_name'])."'" 
															) 		);

			if( !$mem['member_id'] )
			{
				$this->ipsclass->admin->error( 'Could not load the member to import the files under' );
			}
		}
		
		//-----------------------------
		// Start importing
		//-----------------------------

		$processed = $this->processFiles( $category, $mem, $files, $files_dir );
		
		if( $processed == 0 )
		{
			// All done
			
			$this->catlib->rebuild_fileinfo( $category['cid'] );
			$this->catlib->rebuild_cat_cache();
			$this->catlib->rebuild_stats_cache();
			
			$this->ipsclass->admin->rm_dir( $files_dir );
		
			$this->ipsclass->main_msg = $this->ipsclass->input['st'] . ' files from the zip have been imported succesfully';
		
			$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url . '&amp;' .  $this->ipsclass->form_code . '&amp;code=bulkZip' );
		}
		else
		{
			$this->ipsclass->input['st'] += $processed;
			
			$this->ipsclass->admin->redirect( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkZip&amp;op=zipIndexAdd&amp;cat={$this->ipsclass->input['cat']}&amp;mem_name={$this->ipsclass->input['mem_name']}&amp;num={$this->ipsclass->input['num']}&amp;st={$this->ipsclass->input['st']}", 
												'<b>' . sprintf( 'Up to %s files imported, moving on to the next batch...', $this->ipsclass->input['st'] ) . '</b>' );
		}
		
		/*$this->catlib->rebuild_fileinfo( $category['cid'] );
		$this->catlib->rebuild_cat_cache();
		$this->catlib->rebuild_stats_cache();
					
		

		$this->ipsclass->main_msg = $processed . ' files from the zip have been imported succesfully';
		
		$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url . '&amp;' . $this->ipsclass->form_code . '&amp;code=bulkZip' );*/		 
	}
	
	



	
	
	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Directory Bulk Import Form
	/*-------------------------------------------------------------------------*/

	function bulkAddForm()
	{	
		$exclude = array( '.', '..' );
		
		$this->ipsclass->input['lookin'] = str_replace( "&#46;", ".", $this->ipsclass->input['lookin'] );
		
		$dir = $this->ipsclass->input['lookin'] ? $this->ipsclass->input['lookin'] : '../';

		$dirs = array();

		if( is_dir( $dir ) )
		{
			if( $dh = @opendir( $dir ) )
			{
				while( false !== ( $file = @readdir( $dh ) ) )
				{
					if( is_dir( $dir . $file ) && ! in_array( $file, $exclude ) )
					{
						$dirs[] = $dir . $file;	
					}
				}

				@closedir( $dh );
			}
		}

		$this->ipsclass->admin->page_title	= 'Bulk Import: Directory Browser';

		$this->ipsclass->adskin->td_header[] = array( '&nbsp;'			, '5%' );
		$this->ipsclass->adskin->td_header[] = array( 'Directory'		, '60%' );
		$this->ipsclass->adskin->td_header[] = array( 'Files'			, '5%' );
		$this->ipsclass->adskin->td_header[] = array( 'Size'			, '10%' );
		$this->ipsclass->adskin->td_header[] = array( 'Importable'		, '5%' );
		$this->ipsclass->adskin->td_header[] = array( 'View'			, '5%' );		
		$this->ipsclass->adskin->td_header[] = array( 'Import'			, '10%' );		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Directory to import from' );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic( 'Current Directory: ' . $dir, 'left', 'tablerow4' );				

		if( $dir == "../" )
		{
			$up_a_dir = "../../";
		}
		else if( $dir == '../../' )
		{
			$up_a_dir = $dir;
		}
		else
		{
			if( substr( $dir, -1, 1 ) == '/' )
			{
				$dir = substr( $dir, 0, -1 );
			}
			
			$so_far_dirs = explode( '/', $dir );
			
			array_pop($so_far_dirs);
			array_shift($so_far_dirs);
			
			$up_a_dir = '../' . implode( '/', $so_far_dirs ) . '/';
		}
		
		if( substr( $up_a_dir, -2, 2 ) == '//' )
		{
			$up_a_dir = substr( $up_a_dir, 0, -1 );
		}		
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
																			'&nbsp;', 
																			"<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkDir&amp;lookin={$up_a_dir}' title='Click here to look in the parent directory'>Go up a directory</a>", 
																			'&nbsp;', 
																			'&nbsp;', 
																			'&nbsp;', 
																			'&nbsp;', 
																			'&nbsp;', 
																	) 	);		 

		foreach( $dirs as $dir )
		{
			$dhf		= opendir( $dir );

			$count	 	= 0;
			$size	 	= 0;
			$importable = 1;
			
			$dir_clean 	= array_pop( explode( '/', $dir ) );

			while( ( $file = readdir( $dhf ) ) !== false )
			{
				if( is_dir( $file ) )
				{
					continue;
				}
				
				if( in_array( $file, $exclude ) OR ! $this->is_valid_type( $file ) )
				{
					continue;	
				}
				
				$count++;
				
				$size += @filesize( $dir . '/' . $file );
				
				if( ! is_writeable( $dir . '/' . $file ) )
				{
					$importable = 0;	
				}
			}
			
			closedir( $dhf );
			
			if( ! is_writeable( $dir ) )
			{
				$importable = 0;	
			}
			
			if( $importable == 1 AND $count > 0 )
			{
				$import 	= "<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkDir&amp;op=viewDir&amp;directory={$dir}' title='Click here to import files from this directory'>Import</a>";
				$view 		= "<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkDir&amp;op=viewDirFiles&amp;viewdir={$dir}' title='Click here to view the importable files in this directory'><img src='{$this->ipsclass->adskin->img_url}/images/skin_visible.gif' /></a>";
				$importable = "<img src='{$this->ipsclass->adskin->img_url}/images/icon_can_write.gif' title='Files can be imported from this directory'>";	
			}
			else
			{
				$import 	= '&nbsp;';	
				$view 		= "<img src='{$this->ipsclass->adskin->img_url}/images/skin_invisible.gif' />";	
				
				if( $importable == 1 )
				{
					$importable = "<img src='{$this->ipsclass->adskin->img_url}/images/icon_can_write.gif' title='Files can be imported from this directory' />";	
				}
				else
				{
					$importable = "<img src='{$this->ipsclass->adskin->img_url}/images/icon_cannot_write.gif' title='Can not import because the directory/file permissions are wrong' />";	
				}
			}

			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
																	"<img src='{$this->ipsclass->adskin->img_url}/images/folder.gif' />", 
																	"<a href='{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkDir&amp;lookin={$dir}/' title='Click here to look in this directory'>{$dir_clean}</a>", 
																	'<center>' . $count . '</center>', 
																	'<center>' . $this->ipsclass->size_format( $size ) . '</center>', 
																	'<center>' . $importable . '</center>', 
																	'<center>' . $view . '</center>',
																	'<center>' . $import . '</center>', 
																		) 		);	
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	}
	


	/*-------------------------------------------------------------------------*/
	// Tools -> Directory Bulk Import
	/*-------------------------------------------------------------------------*/

	function bulkViewFiles()
	{
		$dir = str_replace( "&#46;", ".", $this->ipsclass->input['viewdir'] );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Importable Files' );
		
		$files = array();

		if( is_dir( $dir ) )
		{
			if( $dh = opendir( $dir ) )
			{
				while( ( $file = readdir( $dh ) ) !== FALSE )
				{
					if( $this->is_valid_type( $file ) )
					{
						$files[] = $file;	
					}
				}
				closedir( $dh );
			}
		}
		
		if( count( $files ) )
		{
			foreach( $files as $file )
			{
				$image = $this->ipsclass->cache['idm_mimetypes'][ strtolower( array_pop( explode( ".", $file ) ) ) ]['mime_img'];

				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 	
																						"<img src='{$this->ipsclass->vars['board_url']}/style_images/{$this->image_dir}/{$image}' border='0' alt='Mime Type Icon' />",
																						"{$dir}/{$file}"
																			) 		);
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 'No files found' ) );
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
	}	



	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Directory Bulk Import View Dir
	/*-------------------------------------------------------------------------*/

	function bulkViewDir()
	{
		$this->ipsclass->admin->page_title	= 'Bulk Import: Options';
		
		$this->ipsclass->html .= $this->zip_js();

		$this->ipsclass->adskin->td_header[] = array( '{none}', '40%' );
		$this->ipsclass->adskin->td_header[] = array( '{none}', '60%' );
		
		$this->ipsclass->input['directory'] = str_replace( "&#46;", ".", $this->ipsclass->input['directory'] );

		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code' 	, 'bulkDir'		),
																			 2 => array( 'act'		, 'downloads' 	),
																			 3 => array( 'op'		, 'doBulkAdd'	),
																			 4 => array( 'dir'		, $this->ipsclass->input['directory'] ),
																			 5 => array( 'section'	, $this->ipsclass->section_code ),
																			 6 => array( 'req'		, 'tools' 		),
							 										), 'importDir', '', 'importDir'		);

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( 'Import Options' );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																			'<b>How many files per cycle?</b><div class=\'desctext\'>Setting this too high can cause the process to timeout</div>',
																			$this->ipsclass->adskin->form_simple_input( 'num', 20 ),
								 									)	 	);
								 									
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																			'<b>Do you wish to remove the files after?</b><div class=\'desctext\'>Selecting &#39;no&#39; will copy the files instead of moving them</div>',
																			$this->ipsclass->adskin->form_yes_no( 'remove', 1 ),
								 									)	 	);								 									

		//-----------------------------
		// Category Selector
		//-----------------------------
		
		$categories 	= $this->catlib->cat_jump_list( 1, 'none', array( $this->ipsclass->input['cat'] ) );
		$category_opts	= '';
		
		if( count($categories) )
		{
			foreach( $categories as $cat )
			{
				$category_opts .= "<option value='{$cat[0]}'>{$cat[1]}</option>\n";
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array(
																			'<b>Select a category</b><div class=\'desctext\'>All of the files will be imported into the selected category</div>',
																			"<select name='cat'>{$category_opts}</select>",
								 									)	 	);

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( 
																			'<b>File Owner</b><div class=\'desctext\'>All of the files will be assigned to the specified user (begin typing in a username and then select the user from the drop down)</div>', 
																			"<input type='text' id='mem_name' name='mem_name' value='' autocomplete='off' style='width:210px;' class='textinput' />" 
																	) 		);
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( 'Import Files' );
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		
		$this->ipsclass->html .= "<script type='text/javascript'>
									 // INIT find names
									 init_js( 'importDir', 'mem_name', 'get-member-names' );
									 // Run main loop
									 var tmp = setTimeout( 'main_loop()', 10 );
								 </script>";

	}
	
	
	/*-------------------------------------------------------------------------*/
	// Tools -> Directory Bulk Import Add
	/*-------------------------------------------------------------------------*/

	function doBulkAdd()
	{
		//-----------------------------
		// Let's check the input first
		//-----------------------------
		
		if( !$this->ipsclass->input['cat'] )
		{
		 	$this->ipsclass->admin->error( 'You did not specify the category to import the files to' );
		}

		if( ! $this->ipsclass->input['dir'] )
		{
		 	$this->ipsclass->admin->error( 'We could not find the directory to import the files from' );
		}

		$this->ipsclass->input['dir'] = str_replace( '&#46;', '.', $this->ipsclass->input['dir'] );
		$this->ipsclass->input['dir'] = str_replace( '&#092;', '\\', $this->ipsclass->input['dir'] );

		if( ! is_dir( $this->ipsclass->input['dir'] ) )
		{
		 	$this->ipsclass->admin->error( 'The directory you specified does not exist' );
		}

		if( ! is_writable( $this->ipsclass->input['dir'] ) )
		{
		 	$this->ipsclass->admin->error( 'Please CHMOD the directory and the files in it to 0777' );
		}
		
		$this->ipsclass->input['num'] 		= intval($this->ipsclass->input['num']) > 0 ? intval($this->ipsclass->input['num']) : 5;
		$this->ipsclass->input['st'] 		= intval($this->ipsclass->input['st']) > 0 ? intval($this->ipsclass->input['st']) : 0;
		$this->ipsclass->input['remove']	= intval($this->ipsclass->input['remove']) > 0 ? 1 : 0;

		$files = array();
		
		$dh = opendir( $this->ipsclass->input['dir'] );

		while( ( $filename = readdir( $dh ) ) !== false )
		{
		 	if( ( $filename != "." ) && ( $filename != ".." ) )
		 	{
				if( $this->is_valid_type( $filename ) )
			 	{
					$files[] = $filename;
			 	}
		 	}
		}
		
		closedir( $dh );
		
		//-----------------------------
		// Get the category information
		//-----------------------------
		
		$mem		= array();
		$category	= array();
		
		$this->ipsclass->input['cat'] = intval($this->ipsclass->input['cat']);
		
	 	$category = $this->catlib->cat_lookup[ $this->ipsclass->input['cat'] ];
	 	
	 	if( !$category['coptions']['opt_disfiles'] )
	 	{
		 	$this->ipsclass->admin->error( 'Files are not displayed in this category - please choose a different category or edit this category to display files' );
	 	}

		
		if ( !count( $mem ) AND $this->ipsclass->input['mem_name'] )
		{
			$mem = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'id as member_id, members_display_name',
																	 'from'		=> 'members',
																	 'where' 	=> "members_l_display_name='".strtolower($this->ipsclass->input['mem_name'])."'" 
															) 		);

			if( !$mem['member_id'] )
			{
				$this->ipsclass->admin->error( 'We could not find the member you entered in as the file owner' );
			}
		}
		
		$processed = $this->processFiles( $category, $mem, $files, $this->ipsclass->input['dir'] . '/' );
		
		if( $processed == 0 )
		{
			// All done
			
			$this->catlib->rebuild_fileinfo( $category['cid'] );
			$this->catlib->rebuild_cat_cache();
			$this->catlib->rebuild_stats_cache();
		
			$this->ipsclass->main_msg = $this->ipsclass->input['st'] . ' total files imported from directory succesfully';
		
			$this->ipsclass->admin->redirect_noscreen( $this->ipsclass->base_url . '&amp;' .  $this->ipsclass->form_code . '&amp;code=bulkDir' );
		}
		else
		{
			$this->ipsclass->input['st'] += $processed;
			
			$this->ipsclass->admin->redirect( "{$this->ipsclass->base_url}&amp;{$this->ipsclass->form_code}&amp;code=bulkDir&amp;op=doBulkAdd&amp;dir={$this->ipsclass->input['dir']}&amp;cat={$this->ipsclass->input['cat']}&amp;mem_name={$this->ipsclass->input['mem_name']}&amp;num={$this->ipsclass->input['num']}&amp;st={$this->ipsclass->input['st']}&amp;remove={$this->ipsclass->input['remove']}", 
												'<b>' . sprintf( 'Up to %s files imported, moving on to the next batch...', $this->ipsclass->input['st'] ) . '</b>' );
		}
	}
	
	
	function processFiles( $category, $mem, $files, $startDir='' )
	{
		$this->ipsclass->load_language( 'lang_downloads' );
		
		//-----------------------------
		// Start importing
		//-----------------------------
		
		$trans 		= array();
		$processed 	= 0;
		$i 			= 0;
		
		
		//-----------------------------
		// FTP Storage?
		//-----------------------------
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'ftp' )
		{
			if( $this->ipsclass->vars['idm_remoteurl'] AND
				$this->ipsclass->vars['idm_remoteport'] AND
				$this->ipsclass->vars['idm_remoteuser'] AND
				$this->ipsclass->vars['idm_remotepass'] AND
				$this->ipsclass->vars['idm_remotefilepath'] )
			{
				$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 999999 );
				
				if( !$conid )
				{
					$this->ipsclass->admin->error( "Could not connect to the remote FTP server" );
				}
				
				$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
				
				if( !$check )
				{
					$this->ipsclass->admin->error( "Could not login to the remote FTP server" );
				}
			}
			else
			{
				$this->ipsclass->admin->error( "You have not entered in all of the required FTP storage information" );
			}
		}
		
		if( $category['coptions']['opt_topice'] == 1 )
		{
			//-----------------------------------------
			// Load email library
			//-----------------------------------------
			
			require_once( ROOT_PATH."sources/classes/class_email.php" );
			$email = new emailer();
			$email->ipsclass =& $this->ipsclass;
			$email->email_init();
			
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
			$parser->parse_bbcode = 1;
			$parser->parse_smilies = 1;			
			
			require_once( DL_PATH.'lib/lib_topics.php' );
			$lib_topics = new lib_topics();
			$lib_topics->ipsclass 	=& $this->ipsclass;
			$lib_topics->email		=& $email;
			$lib_topics->parser		=& $parser;
			$lib_topics->han_editor	=& $han_editor;	
		}	
		
		if( is_array( $files ) )
		{
		 	foreach( $files as $file )
		 	{
			 	$i++;
			 	
			 	if( $this->ipsclass->input['remove'] )
			 	{
				 	if( $i > $this->ipsclass->input['num'] )
				 	{
					 	break;
				 	}
			 	}
			 	else
			 	{
				 	if( $i <= $this->ipsclass->input['st'] )
				 	{
					 	continue;
				 	}
				 	
				 	if( $i > ($this->ipsclass->input['num'] + $this->ipsclass->input['st']) )
				 	{
					 	break;
				 	}
			 	}
			 	
			 	$processed++;
			 	
			 	$temp	= array( 	'file_name'			=> strstr( $file, '/' ) ? array_pop( explode( '/', $file ) ) : $file,
			 						'file_cat'			=> $category['cid'],
			 						'file_open'			=> 1,
			 						'file_submitted'	=> time(),
			 						'file_updated'		=> time(),
									'file_size'			=> @filesize( $this->ipsclass->input['dir'] . '/' . $file ),
									'file_filename'		=> strstr( $file, '/' ) ? array_pop( explode( '/', $file ) ) : $file,
									'file_desc'			=> $this->ipsclass->lang['imported_desc'],
									'file_mime'			=> $this->ipsclass->cache['idm_mimetypes'][ strtolower( array_pop( explode( ".", $file ) ) ) ]['mime_id'],
									'file_submitter'	=> $mem['member_id'],
									'file_ipaddress'	=> $this->ipsclass->ip_address,
									'file_new'			=> 0,
									'file_realname'		=> strstr( $file, '/' ) ? array_pop( explode( '/', $file ) ) : $file,
								);
								
				if( $temp['file_submitted'] <= $this->lasttime AND $this->lasttime > 0 )
				{
					$temp['file_submitted'] = $this->lasttime + 1;
					$temp['file_updated']   = $this->lasttime + 1;
				}
				
				$this->lasttime = $temp['file_submitted'];
				
				$soundex_arr = explode( " ", $temp['file_desc'] );
				$soundex_arr = array_merge( $soundex_arr, explode( " ", $temp['file_name'] ) );
				
				$soundex_final = array();
				if( count($soundex_arr) > 0 )
				{
					foreach( $soundex_arr as $key => $value )
					{
						if( strlen( $value ) > 3 )
						{
							$soundex_final[] = soundex($value);
						}
					}
				}
				
				$temp['file_meta'] = "";
				
				if( count($soundex_final) > 0 )
				{
					$temp['file_meta'] = ",".implode( ",", $soundex_final ).",";
				}

				switch( $this->ipsclass->vars['idm_filestorage'] )
				{
					case 'web':
					case 'nonweb':
						if( $this->ipsclass->input['remove'] )
						{
							if( !@rename( $startDir . $file, str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". ( strstr( $file, '/' ) ? array_pop( explode( '/', $file ) ) : $file ) ) )
							{
								$this->ipsclass->admin->error( "We could not move the file to the storage directory, please doublecheck the directory permissions" );
							}
						}
						else
						{//{print $startDir . $file.'<br>'.str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $file;exit;
							if( !@copy( $startDir . $file, str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". ( strstr( $file, '/' ) ? array_pop( explode( '/', $file ) ) : $file ) ) )
							{
								$this->ipsclass->admin->error( "We could not move the file to the storage directory, please doublecheck the directory permissions" );
							}
						}
							
						@chmod( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $file, 0777 );
						
						$temp['file_storagetype'] = $this->ipsclass->vars['idm_filestorage'];
					break;
									
					case 'ftp':
						$ftp_do_file_upload = @ftp_put( $conid, $this->ipsclass->vars['idm_remotefilepath']."/".$temp['file_realname'], $startDir . $file, FTP_BINARY );
						
						if( $this->ipsclass->input['remove'] )
						{
							@unlink( $startDir . $file );
						}
						
						$temp['file_storagetype'] = "ftp";
						break;
						
					case 'db':
						// Get file data first
						$filedata = base64_encode( file_get_contents( $startDir . $file ) );

						if( $this->ipsclass->input['remove'] )
						{
							@unlink( $startDir . $file );
						}
						
						$temp['file_storagetype'] = "db";
						break;
				}				

				$this->ipsclass->DB->do_insert( 'downloads_files', $temp );
				
				$temp['file_id']	= $this->ipsclass->DB->get_insert_id();
				
				if( $category['ccfields'] )
				{
					$this->ipsclass->DB->do_insert( 'downloads_ccontent', array( 'file_id' => $temp['file_id'] ) );
				}

				if( $this->ipsclass->vars['idm_filestorage'] == 'db' )
				{
					$this->ipsclass->DB->do_insert( "downloads_filestorage", array( 'storage_id' 	=> $temp['file_id'],
																					'storage_file'	=> $filedata 
													)							);
				}
				
				$temp['file_submitter_name'] = $mem['members_display_name'];

				if( $category['coptions']['opt_topice'] == 1 )
				{
					$lib_topics->sort_topic( $temp, $category, 'new', 1 );
				}
				
				usleep( 10 );
		 	}
		}
		
		if( $this->ipsclass->vars['idm_filestorage'] == 'ftp' )
		{
			@ftp_close( $conid );
		}
		
		return $processed;
	}
	
	
	
	function is_valid_type( $filename )
	{
		if( !count($this->valid_types) )
		{
			if( count( $this->ipsclass->cache['idm_mimetypes'] ) )
			{
				foreach( $this->ipsclass->cache['idm_mimetypes'] as $k => $v )
				{
					$this->valid_types[] = $v['mime_extension'];
				}
			}
		}
		
		$type = strtolower( array_pop( explode( ".", $filename ) ) );

		return in_array( $type, $this->valid_types );
	}
	
	
	function zip_js() 
	{
		$IPBHTML = "";
		
		$IPBHTML .= <<<EOF
		
		<script type="text/javascript" src='{$this->ipsclass->vars['board_url']}/jscripts/ipb_xhr_findnames.js'></script>
		<div id='ipb-get-members' style='border:1px solid #000; background:#FFF; padding:2px;position:absolute;width:210px;display:none;z-index:100'></div>
		
		<script type='text/javascript'>						
		var toggleon = 0;
		
		function toggleselectall()
		{
			if ( toggleon )
			{
				toggleon = 0;
				dotoggleselectall(0);
				
				document.getElementById('checkall').checked = false;
			}
			else
			{
				toggleon = 1;
				dotoggleselectall(1);
				
				document.getElementById('checkall').checked = true;
			}
		}
		
		function dotoggleselectall(selectall)
		{	
			var fmobj = document.importZip;
			for (var i=0;i<fmobj.elements.length;i++)
			{
				var e = fmobj.elements[i];
				
				if (e.type=='checkbox')
				{
					if ( selectall ) {
						e.checked = true;
					} else {
						e.checked = false;
					}
				}
			}
		}
		</script>
EOF;
		
		//--endhtml--//
		return $IPBHTML;
	}
}
?>