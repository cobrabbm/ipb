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
|   > Library: Custom Downloads Fields
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .08
|
+--------------------------------------------------------------------------
*/

class lib_cats
{
	var $cat_cache			= array();
	var $cat_lookup			= array();
	var $parent_lookup		= array();
	
	var $init				= 0;
	var $init_failed		= 0;
	
	var $ipsclass           = "";
	
	var $exclude_from_list	= "";
	
	var $member_access		= array();
	var $mem_mods			= array();
	var $cat_mods			= array();
	var $group_mods			= array();
	
	/*-------------------------------------------------------------------------*/
	// Normal init - used for the site and non-essential uses
	// -- tries not to pull from db unless cache_store record is whacked out
	/*-------------------------------------------------------------------------*/
	
	function normal_init()
	{
		if ( ! $this->init )
		{
			//-----------------------------------------
			// Cache data...
			//-----------------------------------------
			
			if ( ! is_array( $this->ipsclass->cache['idm_cats'] ) )
			{
				$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'cache_store', 'where' => "cs_key='idm_cats'" ) );
				$this->ipsclass->DB->simple_exec();
				$cached_info = $this->ipsclass->DB->fetch_row();
				$this->ipsclass->cache['idm_cats'] = unserialize( $this->ipsclass->txt_stripslashes( $cached_info['cs_value'] ) );
			}
			
			if( is_array($this->ipsclass->cache['idm_cats']) AND count( $this->ipsclass->cache['idm_cats'] ) > 0 )
			{
				foreach( $this->ipsclass->cache['idm_cats'] as $parentid => $cid )
				{
					foreach( $cid as $catid => $info )
					{
						$this->cat_cache[ $parentid ][ $catid ]			= $info;
						$this->subcat_lookup[ $parentid ][] 			= $catid;
						$this->parent_lookup[ $catid ] 					= $info['cparent'];
						$this->cat_lookup[ $catid ]						= $info;
					}
				}
			}
		}
		
		if( empty( $this->cat_cache ) )
		{
			// Uh-oh...normal init failed
			// something wrong with cache store..
			$this->init_failed = 1;
			$this->full_init();
		}
		
		$this->build_moderators(1);
		
		$this->init = 1;
	}

	/*-------------------------------------------------------------------------*/
	// Full init - used for the acp and in case of a cache crash
	/*-------------------------------------------------------------------------*/
		
	function full_init()
	{
		if ( ! $this->init )
		{
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_categories', 'order' => 'cposition ASC' ) );
			$this->ipsclass->DB->simple_exec();
			
			while( $cat = $this->ipsclass->DB->fetch_row() )
			{
				$cat['cfileinfo']	= unserialize( $this->ipsclass->txt_stripslashes( $cat['cfileinfo'] ) );
				$cat['coptions']	= unserialize( $this->ipsclass->txt_stripslashes( $cat['coptions'] ) );
				$cat['cperms'] 		= unserialize( $this->ipsclass->txt_stripslashes( $cat['cperms'] ) );
			
				$this->ipsclass->cache['idm_cats'][ $cat['cparent'] ][ $cat['cid'] ] = $cat;
				unset($cat['cperms']);
			}
			
			if( is_array($this->ipsclass->cache['idm_cats']) AND count( $this->ipsclass->cache['idm_cats'] ) > 0 )
			{
				if( $this->init_failed )
				{
					$this->ipsclass->update_cache( array( 'name' => 'idm_cats', 'array' => 1, 'deletefirst' => 1 ) );
					$this->init_failed = 0;
				}
				
				foreach( $this->ipsclass->cache['idm_cats'] as $parentid => $cid )
				{
					foreach( $cid as $catid => $info )
					{
						$this->cat_cache[ $parentid ][ $catid ]			= $info;
						$this->subcat_lookup[ $parentid ][] 			= $catid;
						$this->parent_lookup[ $catid ] 					= $info['cparent'];
						$this->cat_lookup[ $catid ]						= $info;
					}
				}
			}
		}
		
		$this->build_moderators(0);
		
		$this->init = 1;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Build moderators array
	/*-------------------------------------------------------------------------*/
		
	function build_moderators( $try_cache=1 )
	{
		if( $try_cache == 1 )
		{
			if( is_array($this->ipsclass->cache['idm_mods']) )
			{
				if( count($this->ipsclass->cache['idm_mods']) )
				{
					foreach( $this->ipsclass->cache['idm_mods'] as $k => $v )
					{
						$temp = explode( ":", $v['modgmid'] );
						
						if( $v['modtype'] == 0 )
						{
							$v['group_name'] = $temp[1];
							$v['group_id']   = $temp[0];
							
							$this->group_mods[ $v['group_id'] ][] = $v;
						}
						else
						{
							$v['mem_name'] = $temp[1];
							$v['mem_id']   = $temp[0];
							
							$this->mem_mods[ $v['mem_id'] ][] = $v;
						}
						
						$cats = explode( ",", $v['modcats'] );
						
						if( count($cats) )
						{
							foreach( $cats as $j => $l )
							{
								$key = "";
								
								if( $v['modtype'] == 0 )
								{
									$key = "g".$v['group_id'];
								}
								else
								{
									$key = "m".$v['mem_id'];
								}
								
								$this->cat_mods[ $l ][$key] = $v;
							}
						}
						
						unset($temp);
					}
				}
			}
		}
		
		if( !isset($this->ipsclass->cache['idm_mods']) )
		{
			$this->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'downloads_mods' ) );
			$this->ipsclass->DB->exec_query();
			
			while( $v = $this->ipsclass->DB->fetch_row() )
			{
				$temp = explode( ":", $v['modgmid'] );
				
				if( $v['modtype'] == 0 )
				{
					$v['group_name'] = $temp[1];
					$v['group_id']   = $temp[0];
					
					$this->group_mods[ $v['group_id'] ][] = $v;
				}
				else
				{
					$v['mem_name'] = $temp[1];
					$v['mem_id']   = $temp[0];
					
					$this->mem_mods[ $v['mem_id'] ][] = $v;
				}
				
				$cats = explode( ",", $v['modcats'] );
				
				if( count($cats) )
				{
					foreach( $cats as $j => $l )
					{
						$key = "";
						
						if( $v['modtype'] == 0 )
						{
							$key = "g".$v['group_id'];
						}
						else
						{
							$key = "m".$v['mem_id'];
						}
						
						$this->cat_mods[ $l ][$key] = $v;
					}
				}
				
				unset($temp);
			}
		}
	}
		
			
	/*-------------------------------------------------------------------------*/
	// Get parent ids of a catid recursively
	/*-------------------------------------------------------------------------*/
		
	function get_parents( $catid, $parent_ids=array() )
	{
		if( is_array($this->cat_lookup[ $catid ]) )
		{
			if( $this->parent_lookup[ $catid ] > 0 )
			{
				$parent_ids = $this->get_parents( $this->parent_lookup[ $catid ], $parent_ids );
				$parent_ids[] = $this->parent_lookup[ $catid ];
			}
		}
		
		
		return array_unique($parent_ids);
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Get children ids of a catid recursively
	/*-------------------------------------------------------------------------*/
		
	function get_children( $catid, $child_ids=array() )
	{
		if ( is_array( $this->cat_cache[ $catid ] ) )
		{
			$final_ids = array_merge( $child_ids, $this->subcat_lookup[ $catid ]);

			foreach( $this->cat_cache[ $catid ] as $id => $data )
			{
				$subchild_ids = $this->get_children( $data['cid'], $final_ids );
				
				if( is_array($subchild_ids) AND count($subchild_ids) )
				{
					$final_ids = array_unique(array_merge($final_ids, $subchild_ids));
				}
			}
		}
		
		return $final_ids;
	}	
	
	
	/*-------------------------------------------------------------------------*/
	// Category cache rebuilding - does not rebuild latest file info
	/*-------------------------------------------------------------------------*/
		
	function rebuild_cat_cache( )
	{
		$this->ipsclass->cache['idm_cats'] = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*',
														'from'   => 'downloads_categories',
														'order'  => 'cparent, cposition'
								   			)      );
		$this->ipsclass->DB->simple_exec();
		
		while( $cat = $this->ipsclass->DB->fetch_row() )
		{
			$cat['cfileinfo']	= unserialize( $this->ipsclass->txt_stripslashes( $cat['cfileinfo'] ) );
			$cat['coptions']	= unserialize( $this->ipsclass->txt_stripslashes( $cat['coptions'] ) );
			$cat['cperms'] 		= unserialize( $this->ipsclass->txt_stripslashes( $cat['cperms'] ) );
			
			$this->ipsclass->cache['idm_cats'][ $cat['cparent'] ][ $cat['cid'] ] = $cat;
			unset($cat['cperms']);
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_cats', 'array' => 1, 'deletefirst' => 1, 'donow' => 1 ) );		
	}
	
	
	function rebuild_fileinfo( $catid='all' )
	{
		$stats_array = array( 'total_views' => 0, 'total_files' => 0, 'total_downloads' => 0, 'date' => 0 );
		
		if( $catid != 'all' )
		{
			if( $catid == 0 )
			{
				return;
			}
			
			$children = $this->get_children( $catid );
			$children_string = "";
			
			if( is_array($children) AND count($children) > 0 )
			{
				$children_string = implode(",", $children);
			}
			
			if( $children_string )
			{
				$final_string = $catid.",".$children_string;
			}
			else
			{
				$final_string = $catid;
			}
	 		
			$this->ipsclass->DB->cache_add_query( "category_resynch", array( 'cats' => $final_string ), "sql_idm_queries" );
			$this->ipsclass->DB->cache_exec_query();
			
			$stats = $this->ipsclass->DB->fetch_row();
			
			$stats_array['total_views'] 	= $stats['views'];
			$stats_array['total_downloads'] = $stats['downloads'];
			$stats_array['total_files'] 	= $stats['files'];
			
			$this->ipsclass->DB->cache_add_query( "category_pending_files", array( 'cats' => $final_string ), "sql_idm_queries" );
			$this->ipsclass->DB->cache_exec_query();
			
			$pend = $this->ipsclass->DB->fetch_row();
			
			$stats_array['pending_files'] 	= $pend['files'];			
			
			$this->ipsclass->DB->cache_add_query( "category_getlatest", array( 'cats' => $final_string ), "sql_idm_queries" );
			$this->ipsclass->DB->cache_exec_query();
			
			while( $stats = $this->ipsclass->DB->fetch_row() )
			{
				if( $stats['file_updated'] > $stats_array['date'] )
				{
					$stats_array['date'] 	= $stats['file_updated'];
					$stats_array['mid'] 	= $stats['file_submitter'];
					$stats_array['fid'] 	= $stats['file_id'];
					$stats_array['fname']	= $stats['file_name'];
					$stats_array['mname']	= $stats['members_display_name'];
					$stats_array['updated'] = 1;
				}
	
				if( $stats['file_submitted'] >= $stats_array['date'] )
				{
					$stats_array['date'] 	= $stats['file_submitted'];
					$stats_array['mid'] 	= $stats['file_submitter'];
					$stats_array['fid'] 	= $stats['file_id'];
					$stats_array['fname']	= $stats['file_name'];
					$stats_array['mname']	= $stats['members_display_name'];
					$stats_array['updated'] = 0;
				}			
			}
			
	 		$this->ipsclass->DB->do_update( 'downloads_categories', array( 'cfileinfo' => $this->ipsclass->txt_safeslashes(serialize($stats_array)) ), 'cid='.$catid );
	 		
	 		// Recursively rebuild for parents
	 		if( $this->cat_lookup[$catid]['cparent'] != 0 )
	 		{
	 			$this->rebuild_fileinfo( $this->cat_lookup[$catid]['cparent'] );
 			}
			
 		}
 		else
 		{
	 		foreach( $this->cat_lookup as $catid => $catdata )
	 		{
		 		$stats_array = array( 'total_views' => 0, 'total_files' => 0, 'total_downloads' => 0, 'date' => 0 );
		 		
				$children = $this->get_children( $catid );
				$children_string = "";
				
				if( count($children) > 0 )
				{
					$children_string = implode(",", $children);
				}
				
				if( $children_string )
				{
					$final_string = $catid.",".$children_string;
				}
				else
				{
					$final_string = $catid;
				}
		 		
				$this->ipsclass->DB->cache_add_query( "category_resynch", array( 'cats' => $final_string ), "sql_idm_queries" );
				$this->ipsclass->DB->cache_exec_query();
				
				$stats = $this->ipsclass->DB->fetch_row();
				
				$stats_array['total_views'] 	= $stats['views'];
				$stats_array['total_downloads'] = $stats['downloads'];
				$stats_array['total_files'] 	= $stats['files'];
				
				$this->ipsclass->DB->cache_add_query( "category_pending_files", array( 'cats' => $final_string ), "sql_idm_queries" );
				$this->ipsclass->DB->cache_exec_query();
				
				$pend = $this->ipsclass->DB->fetch_row();
				
				$stats_array['pending_files'] 	= $pend['files'];
				
				$this->ipsclass->DB->cache_add_query( "category_getlatest", array( 'cats' => $final_string ), "sql_idm_queries" );
				$this->ipsclass->DB->cache_exec_query();
				
				while( $stats = $this->ipsclass->DB->fetch_row() )
				{
					if( $stats['file_updated'] > $stats_array['date'] AND $stats['file_updated'] > $stats['file_submitted'] )
					{
						$stats_array['date'] 	= $stats['file_updated'];
						$stats_array['mid'] 	= $stats['file_submitter'];
						$stats_array['fid'] 	= $stats['file_id'];
						$stats_array['fname']	= $stats['file_name'];
						$stats_array['mname']	= $stats['members_display_name'];
						$stats_array['updated'] = 1;
					}
		
					if( $stats['file_submitted'] > $stats_array['date'] )
					{
						$stats_array['date'] 	= $stats['file_submitted'];
						$stats_array['mid'] 	= $stats['file_submitter'];
						$stats_array['fid'] 	= $stats['file_id'];
						$stats_array['fname']	= $stats['file_name'];
						$stats_array['mname']	= $stats['members_display_name'];
						$stats_array['updated'] = 0;
					}			
				}
				
		 		$this->ipsclass->DB->do_update( 'downloads_categories', array( 'cfileinfo' => $this->ipsclass->txt_safeslashes(serialize($stats_array)) ), 'cid='.$catid );
			}
		}
 		
 		$this->rebuild_cat_cache();
 		return TRUE;
	}
	
	
	function rebuild_stats_cache()
	{
		//-----------------------------
		// INIT
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
		
		$cnt = count( $this->cat_lookup);
		
		$this->ipsclass->cache['idm_stats']['total_categories'] = $cnt;
		
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
		return TRUE;
	}
	
	
	function rebuild_mod_cache( )
	{
		$this->ipsclass->cache['idm_mods'] = array();
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*',
														'from'   => 'downloads_mods',
														'order'  => 'modid'
								   			)      );
		$this->ipsclass->DB->simple_exec();
		
		while( $mod = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['idm_mods'][ $mod['modid'] ] = $mod;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'idm_mods', 'array' => 1, 'deletefirst' => 1, 'donow' => 1 ) );		
	}	

		
	/*-------------------------------------------------------------------------*/
	// Get nav bar entries for categories recursively
	/*-------------------------------------------------------------------------*/
		
	function get_nav( $catid, $querybit='autocom=downloads&amp;showcat=', $acp=0 )
	{
		if( $acp == 1 )
		{
			$nav_array[] = "<a href='".$this->ipsclass->base_url.$querybit.$catid."'>{$this->cat_lookup[ $this->cat_lookup[$catid]['cparent'] ]['cname']}</a>";
		}
		else
		{
			$nav_array[] = "<a href='".$this->ipsclass->base_url.$querybit.$catid."'>{$this->cat_lookup[ $catid ]['cname']}</a>";
		}
		
		$parent_ids = $this->get_parents( $catid );
		
		if ( is_array($parent_ids) and count($parent_ids) )
		{
			$parent_ids = array_reverse($parent_ids);
			
			foreach( $parent_ids as $id )
			{
				if( $id > 0 )
				{
					if( $acp == 1 )
					{
						$nav_array[] = "<a href='".$this->ipsclass->base_url.$querybit.$this->cat_lookup[ $id ]['cid']."'>{$this->cat_lookup[ $this->cat_lookup[$id]['cparent'] ]['cname']}</a>";	
					}
					else
					{
						$nav_array[] = "<a href='".$this->ipsclass->base_url.$querybit.$this->cat_lookup[ $id ]['cid']."'>{$this->cat_lookup[ $id ]['cname']}</a>";
					}
				}
			}
		}
		
		return array_reverse($nav_array);
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Find all cats using a given mime mask
	/*-------------------------------------------------------------------------*/
		
	function get_cats_mimemask( $mask_id )
	{
		$return_ids = array();
		
		if( ! $mask_id )
		{
			return;
		}
		
		if( ! is_array( $this->cat_lookup ) )
		{
			return;
		}
		
		if( ! count( $this->cat_lookup ) )
		{
			return;
		}
		
		foreach( $this->cat_lookup as $catid => $catinfo )
		{
			if( $catinfo['coptions']['opt_mimemask'] == $mask_id )
			{
				$return_ids[] = $catid;
			}
		}
		
		return $return_ids;
	}
	
	/*-------------------------------------------------------------------------*/
	// Find all cats using a given custom field
	/*-------------------------------------------------------------------------*/
		
	function get_cats_cfield( $field_id )
	{
		$return_ids = array();
		
		if( ! $field_id )
		{
			return;
		}
		
		if( ! is_array( $this->cat_lookup ) )
		{
			return;
		}
		
		if( ! count( $this->cat_lookup ) )
		{
			return;
		}
		
		foreach( $this->cat_lookup as $catid => $catinfo )
		{
			$cfields = explode( ',', $catinfo['ccfields'] );
			
			if( in_array( $field_id, $cfields ) )
			{
				$return_ids[] = $catid;
			}
		}
		
		return $return_ids;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Category drop down/multi-select list generation
	// -- returns only the options entry, make your own select code
	/*-------------------------------------------------------------------------*/
	
	function cat_jump_list($restrict=0, $live='none', $sel=array() )
	{
		if ( $restrict != 1 )
		{	
			$jump_array[] = array( '0', '(Root Category)' );
		}
		else
		{
			$jump_array = array();
		}

		if( count( $this->cat_cache[0] ) > 0 )
		{
			foreach( $this->cat_cache[0] as $id => $cat_data )
			{
				$disabled = "";
				
				if( $live != 'none' )
				{
					if( $cat_data['copen'] == 0 AND !$this->ipsclass->member['g_access_cp'] )
					{
						continue;
					}
					
					if( ! in_array( $cat_data['cid'], $this->member_access[ $live ] ) )
					{
						if( in_array( $cat_data['cid'], $this->member_access[ 'show' ] ) )
						{
							$disabled = " disabled='disabled'";
						}
						else
						{
							continue;
						}
					}

					if( is_array($sel) AND count($sel) )
					{
						if( in_array( $cat_data['cid'], $sel ) && !$disabled)
						{
							$disabled = " selected='selected'";
						}
					}
					else if( $this->ipsclass->input['c'] == $cat_data['cid'] && !$disabled)
					{
						$disabled = " selected='selected'";
					}
				}
					
				$jump_array[] = array( $cat_data['cid'], $cat_data['cname'], $disabled );
			
				$depth_guide = "--";
			
				if ( is_array( $this->cat_cache[ $cat_data['cid'] ] ) )
				{
					foreach( $this->cat_cache[ $cat_data['cid'] ] as $id => $cat_data )
					{
						$disabled = "";
						
						if( $live != 'none' )
						{
							if( $cat_data['copen'] == 0 AND !$this->ipsclass->member['g_access_cp'] )
							{
								continue;
							}
												
							if( ! in_array( $cat_data['cid'], $this->member_access[ $live ] ) )
							{
								if( in_array( $cat_data['cid'], $this->member_access[ 'show' ] ) )
								{
									$disabled = " disabled='disabled'";
								}
								else
								{
									continue;
								}
							}
							
							if( is_array($sel) AND count($sel) )
							{
								if( in_array( $cat_data['cid'], $sel ) && !$disabled)
								{
									$disabled = " selected='selected'";
								}
							}
							else if( $this->ipsclass->input['c'] == $cat_data['cid'] && !$disabled)
							{
								$disabled = " selected='selected'";
							}
						}
						$jump_array[] = array( $cat_data['cid'], $depth_guide.$cat_data['cname'], $disabled );
						$jump_array = $this->cat_jump_list_internal( $cat_data['cid'], $jump_array, $depth_guide . "--", $live, $sel );
					}
				}
			}
		}
		
		return $jump_array;
	}
	
	/*-------------------------------------------------------------------------*/
	// INTERNAL - Used to assist in generating the option html tags recursively
	/*-------------------------------------------------------------------------*/
	
	function cat_jump_list_internal($root_id, $jump_array=array(), $depth_guide="", $live='none', $sel=array() )
	{
		if ( is_array( $this->cat_cache[ $root_id ] ) )
		{
			foreach( $this->cat_cache[ $root_id ] as $id => $cat_data )
			{
				$disabled = "";
				
				if( $live != 'none' )
				{
					if( $cat_data['copen'] == 0 AND !$this->ipsclass->member['g_access_cp'] )
					{
						continue;
					}
										
					if( ! in_array( $cat_data['cid'], $this->member_access[ $live ] ) )
					{
						if( in_array( $cat_data['cid'], $this->member_access[ 'show' ] ) )
						{
							$disabled = " disabled='disabled'";
						}
						else
						{
							continue;
						}
					}
					
					if( is_array($sel) AND count($sel) )
					{
						if( in_array( $cat_data['cid'], $sel ) && !$disabled)
						{
							$disabled = " selected='selected'";
						}
					}
					else if( $this->ipsclass->input['c'] == $cat_data['cid'] && !$disabled)
					{
						$disabled = " selected='selected'";
					}					
				}
								
				$jump_array[] = array( $cat_data['cid'], $depth_guide.$cat_data['cname'], $disabled );
				$jump_array = $this->cat_jump_list_internal( $cat_data['cid'], $jump_array, $depth_guide . "--", $live, $sel );
			}
		}
		
		return $jump_array;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Compile category permissions based on forum perm masks
	/*-------------------------------------------------------------------------*/
		
	function compile_cat_perms()
	{
		$r_array = array( 'SHOW' => '', 'VIEW' => '', 'ADD' => '', 'DOWNLOAD' => '', 'RATE' => '', 'COMMENT' => '', 'AUTO' => '' );
		
		if ($this->ipsclass->input['SHOW_ALL'] == 1)
		{
			$r_array['SHOW'] = '*';
		}
		
		if ($this->ipsclass->input['VIEW_ALL'] == 1)
		{
			$r_array['VIEW'] = '*';
		}
		
		if ($this->ipsclass->input['ADD_ALL'] == 1)
		{
			$r_array['ADD'] = '*';
		}
		
		if ($this->ipsclass->input['DOWNLOAD_ALL'] == 1)
		{
			$r_array['DOWNLOAD'] = '*';
		}
		
		if ($this->ipsclass->input['RATE_ALL'] == 1)
		{
			$r_array['RATE'] = '*';
		}
		
		if ($this->ipsclass->input['COMMENT_ALL'] == 1)
		{
			$r_array['COMMENT'] = '*';
		}
		
		if ($this->ipsclass->input['AUTO_ALL'] == 1)
		{
			$r_array['AUTO'] = '*';
		}		
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'perm_id, perm_name', 'from' => 'forum_perms', 'order' => "perm_id" ) );
		$this->ipsclass->DB->simple_exec();
		
		while ( $data = $this->ipsclass->DB->fetch_row() )
		{
			if ($r_array['SHOW'] != '*')
			{
				if ($this->ipsclass->input[ 'SHOW_'.$data['perm_id'] ] == 1)
				{
					$r_array['SHOW'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['VIEW'] != '*')
			{
				if ($this->ipsclass->input[ 'VIEW_'.$data['perm_id'] ] == 1)
				{
					$r_array['VIEW'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['ADD'] != '*')
			{
				if ($this->ipsclass->input[ 'ADD_'.$data['perm_id'] ] == 1)
				{
					$r_array['ADD'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['DOWNLOAD'] != '*')
			{
				if ($this->ipsclass->input[ 'DOWNLOAD_'.$data['perm_id'] ] == 1)
				{
					$r_array['DOWNLOAD'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['RATE'] != '*')
			{
				if ($this->ipsclass->input[ 'RATE_'.$data['perm_id'] ] == 1)
				{
					$r_array['RATE'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['COMMENT'] != '*')
			{
				if ($this->ipsclass->input[ 'COMMENT_'.$data['perm_id'] ] == 1)
				{
					$r_array['COMMENT'] .= $data['perm_id'].",";
				}
			}
			//-----------------------------------------
			if ($r_array['AUTO'] != '*')
			{
				if ($this->ipsclass->input[ 'AUTO_'.$data['perm_id'] ] == 1)
				{
					$r_array['AUTO'] .= $data['perm_id'].",";
				}
			}
		}
		
		$r_array['SHOW']   		= preg_replace( "/,$/", "", $r_array['SHOW']   );
		$r_array['VIEW']   		= preg_replace( "/,$/", "", $r_array['VIEW']   );
		$r_array['ADD']		    = preg_replace( "/,$/", "", $r_array['ADD']    );
		$r_array['DOWNLOAD']	= preg_replace( "/,$/", "", $r_array['DOWNLOAD']  );
		$r_array['RATE']    	= preg_replace( "/,$/", "", $r_array['RATE']    );
		$r_array['COMMENT']    	= preg_replace( "/,$/", "", $r_array['COMMENT']    );
		$r_array['AUTO']    	= preg_replace( "/,$/", "", $r_array['AUTO']    );
		
		return $r_array;
	}	
	
	
	/*-------------------------------------------------------------------------*/
	// Find me the open categories, either global or subcats of a given cid
	/*-------------------------------------------------------------------------*/
		
	function get_open_cats( $catid="" )
	{
		$open_cats = array();
		
		if( ! $catid )
		{
			foreach( $this->cat_lookup as $cid => $cinfo )
			{
				if( $cinfo['copen'] == 1 OR $this->ipsclass->member['g_access_cp'] )
				{
					$open_cats[] = $cid;
				}
			}
		}
		else
		{
			foreach( $this->subcat_lookup[$catid] as $blank_key => $cid )
			{
				if( $this->cat_lookup[$cid]['copen'] == 1 OR $this->ipsclass->member['g_access_cp'] )
				{
					$open_cats[] = $cid;
				}
			}
		}
		
		return $open_cats;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Sort permissions into a nice perrty array
	/*-------------------------------------------------------------------------*/
	
	function get_member_cat_perms( $memid="" )
	{
		$no_update = 0;
		
		$member_perms = array(	'show' 		=> array(),
								'view' 		=> array(),
								'add'		=> array(),
								'download'	=> array(),
								'rate'		=> array(),
								'comment'	=> array(),
								'auto'		=> array() );

		$member_masks = array();
		
		$open_cats = $this->get_open_cats();

		if( !$memid )
		{
			if( $this->ipsclass->member['org_perm_id'] )
			{
				$member_masks = explode( ",", $this->ipsclass->trim_leading_comma($this->ipsclass->trim_trailing_comma($this->ipsclass->member['org_perm_id'])) );
			}
			else
			{
				if( strpos( $this->ipsclass->member['g_perm_id'], "," ) )
				{
					$member_masks = explode( ",", $this->ipsclass->member['g_perm_id'] );
				}
				else
				{
					$member_masks[] = $this->ipsclass->member['g_perm_id'];
				}
			}
		}
		else
		{
			$no_update = 1;
			
			$groups = $this->ipsclass->DB->simple_exec_query( array( 'select' 		=> 'mgroup, org_perm_id, mgroup_others',
															  		 'from'		=> 'members',
															  		 'where'		=> 'id='.intval($memid),
															)		);

			if( !$groups['org_perm_id'] )
			{
				$in_string = $groups['mgroup'];
				
				if( strpos($groups['mgroup_others'],",") )
				{
					$in_string .= ",".$groups['mgroup_others'];
				}
				
				$this->ipsclass->DB->simple_construct( array( 'select' 	=> 'g_perm_id',
															  'from'		=> 'groups',
															  'where'		=> "g_id IN({$in_string})",
													 )		);
				$this->ipsclass->DB->exec_query();
				
				while( $masks = $this->ipsclass->DB->fetch_row() )
				{
					$these_masks = array();
					if( strpos( $masks['g_perm_id'], "," ) )
					{
						$these_masks = explode( ",", $masks['g_perm_id'] );
						$member_masks = array_merge( $member_masks, $these_masks );
					}
					else
					{
						$member_masks[] = $masks['g_perm_id'];
					}
				}
			}
			else
			{
				$member_masks = explode( ",", $this->ipsclass->trim_leading_comma($this->ipsclass->trim_trailing_comma($groups['org_perm_id'])) );
			}
		}
		
		foreach( $this->cat_lookup as $cid => $cinfo )
		{
			if( ! in_array( $cid, $open_cats ) )
			{
				continue;
			}
			
			foreach( $cinfo['cperms'] as $k => $v )
			{
				if( $v == '*' )
				{
					$member_perms[$k][] = $cid;
				}
				else if ($v == '' )
				{
					continue;
				}
				else
				{
					$forum_masks = explode( ",", $v );
					
					foreach( $forum_masks as $key => $value )
					{
						if( in_array( $value, $member_masks ) )
						{
							$member_perms[$k][] = $cid;
						}
					}
				}
			}
		}
		
		foreach( $member_perms as $k => $v )
		{
			if( is_array( $member_perms[$k] ) )
			{
				$member_perms[$k] = array_unique($member_perms[$k]);
			}
		}
		
		if( $no_update == 0 )
		{
			$this->member_access = $member_perms;
			return TRUE;
		}
		else
		{
			return $member_perms;
		}
	}

}


?>