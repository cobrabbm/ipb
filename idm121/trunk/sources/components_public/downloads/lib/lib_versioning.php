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
|   > Library: Versioning Control
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 11, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

class lib_versioning
{
	var $ipsclass;
	
	var $file_id		= 0;
	var $file_data		= array();
	var $versions		= array();
	
	var $error			= "";
	
	function init( $file_id = 0 )
	{
		if( $file_id > 0 )
		{
			$this->file_id = $file_id;
		}
		
		$this->file_id = intval( $this->file_id );
		
		if( !is_array($this->file_data) )
		{
			$this->file_data = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => 'file_id='.$this->file_id ) );
		}
		
		if( !is_array($this->file_data) )
		{
			$this->error = 'FILE_NOT_FOUND';
			return;
		}
	}
	
	
	function backup()
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		// Are we restricting the number of stored revisions?
		
		if( $this->ipsclass->vars['idm_versioning_limit'] > 0 )
		{
			$stored = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'count(*) as num', 'from' => 'downloads_filebackup', 'where' => 'b_fileid='.$this->file_id ) );
			
			if( $stored['num'] >= $this->ipsclass->vars['idm_versioning_limit'] )
			{
				$this->ipsclass->DB->build_and_exec_query( array( 'delete' 	=> 'downloads_filebackup', 
																	'where' => 'b_fileid='.$this->file_id, 
																	'order' => 'b_backup ASC', 
																	'limit' => array( ( $stored['num'] - $this->ipsclass->vars['idm_versioning_limit'] ) + 1 )
														)		);
			}
		}
		
		$to_insert = array( 'b_fileid'			=> $this->file_id,
							'b_filetitle'		=> $this->file_data['file_name'],
							'b_filedesc'		=> $this->file_data['file_desc'],
							'b_filename'		=> $this->file_data['file_filename'],
							'b_ssname'			=> $this->file_data['file_ssname'],
							'b_thumbname'		=> $this->file_data['file_thumb'],
							'b_filemime'		=> $this->file_data['file_mime'],
							'b_ssmime'			=> $this->file_data['file_ssmime'],
							'b_filemeta'		=> $this->file_data['file_meta'],
							'b_storage'			=> $this->file_data['file_storagetype'],
							'b_hidden'			=> 0,
							'b_backup'			=> time(),
							'b_updated'			=> $this->file_data['file_updated'],
							'b_fileurl'			=> $this->file_data['file_url'],
							'b_ssurl'			=> $this->file_data['file_ssurl'],
							'b_filereal'		=> $this->file_data['file_realname']
						  );
						  
		$this->ipsclass->DB->do_insert( 'downloads_filebackup', $to_insert );
		
		return TRUE;
	}
	
	
	function restore( $id = 0 )
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		$id = intval($id);
		
		if( !$id > 0 )
		{
			$this->error = 'NO_ID_PASSED';
			return;
		}			
		
		// Backup Current
		
		$to_insert = array( 'b_fileid'			=> $this->file_id,
							'b_filetitle'		=> $this->file_data['file_name'],
							'b_filedesc'		=> $this->file_data['file_desc'],
							'b_filename'		=> $this->file_data['file_filename'],
							'b_ssname'			=> $this->file_data['file_ssname'],
							'b_thumbname'		=> $this->file_data['file_thumb'],
							'b_filemime'		=> $this->file_data['file_mime'],
							'b_ssmime'			=> $this->file_data['file_ssmime'],
							'b_filemeta'		=> $this->file_data['file_meta'],
							'b_storage'			=> $this->file_data['file_storagetype'],
							'b_hidden'			=> 0,
							'b_backup'			=> time(),
							'b_updated'			=> $this->file_data['file_updated'],
							'b_fileurl'			=> $this->file_data['file_url'],
							'b_ssurl'			=> $this->file_data['file_ssurl'],
							'b_filereal'		=> $this->file_data['file_realname']
						  );
						  
		$this->ipsclass->DB->do_insert( 'downloads_filebackup', $to_insert );
		
		// Restore previous
		
		$restore = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => 'b_id='.$id.' AND b_fileid='.$this->file_id ) );
		
		if( !$restore['b_fileid'] )
		{
			$this->error = 'FILE_NOT_FOUND';
			return;
		}
		
		$this->ipsclass->DB->do_update( "downloads_files", array(	'file_updated'		=> $restore['b_updated'],
																	'file_name'			=> $restore['b_filetitle'],
																	'file_desc'			=> $restore['b_filedesc'],
																	'file_meta'			=> $restore['b_filemeta'],
																	'file_filename'		=> $restore['b_filename'],
																	'file_ssname'		=> $restore['b_ssname'],
																	'file_thumb'		=> $restore['b_thumbname'],
																	'file_mime'			=> $restore['b_filemime'],
																	'file_ssmime'		=> $restore['b_ssmime'],
																	'file_storagetype'	=> $restore['b_storage'],
																	'file_url'			=> $restore['b_fileurl'],
																	'file_ssurl'		=> $restore['b_ssurl'],
																	'file_realname'		=> $restore['b_filereal'],
																), "file_id={$this->file_id}" );
																
		$this->ipsclass->DB->do_delete( 'downloads_filebackup', "b_id=".$id );
		
		return TRUE;
	}
	
	
	function remove( $id = 0, $restore=array(), $current=array() )
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		$id = intval($id);
		
		if( !$id > 0 )
		{
			$this->error = 'NO_ID_PASSED';
			return;
		}	
		
		if( !is_array($restore) OR !count($restore) )
		{
			$restore = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => 'b_id='.$id.' AND b_fileid='.$this->file_id ) );
		}
		
		if( !is_array($current) OR !count($current) )
		{
			$current = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'file_filename, file_ssname, file_thumb', 'from' => 'downloads_files', 'where' => 'file_id='.$this->file_id ) );
		}
		
		if( !$restore['b_id'] )
		{
			$this->error = 'FILE_NOT_FOUND';
			return;
		}
		
		if( $current['file_filename'] != $restore['b_filename'] )
		{
			@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localfilepath'] ) ."/". $restore['b_filename'] );
		}

		if( $restore['b_ssname'] )
		{
			if( $current['file_ssname'] != $restore['b_ssname'] )
			{
				@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $restore['b_ssname'] );
			}

			if( $restore['b_thumbname'] )
			{
				if( $current['file_thumb'] != $restore['b_thumb'] )
				{
					@unlink( str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] ) ."/". $restore['b_thumb'] );
				}
			}
		}
		
		$this->ipsclass->DB->do_delete( "downloads_filebackup", "b_id=".$id );
		
		return TRUE;
	}
	
	
	function hide( $id = 0 )
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		$id = intval($id);
		
		if( !$id > 0 )
		{
			$this->error = 'NO_ID_PASSED';
			return;
		}	
		
		$this->ipsclass->DB->do_update( "downloads_filebackup", array( 'b_hidden' => 1 ), "b_id=".$id );
		
		return TRUE;
	}
	
	
	function unhide( $id = 0 )
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		$id = intval($id);
		
		if( !$id > 0 )
		{
			$this->error = 'NO_ID_PASSED';
			return;
		}	
		
		$this->ipsclass->DB->do_update( "downloads_filebackup", array( 'b_hidden' => 0 ), "b_id=".$id );
		
		return TRUE;
	}	
	
	
	function retrieve_versions( )
	{
		if( $this->file_data['file_storagetype'] != 'web' AND $this->file_data['file_storagetype'] != 'nonweb' )
		{
			$this->error = 'ONLY_LOCAL_BACKUP';
			return;
		}
		
		$versions = array();
		
		$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_filebackup', 'where' => 'b_fileid='.$this->file_id ) );
		$this->ipsclass->DB->exec_query();
		
		while( $r = $this->ipsclass->DB->fetch_row() )
		{
			$versions[ $r['b_updated'] ] = $r;
		}
		
		return $versions;
	}
		
				
							
							
	
}
?>