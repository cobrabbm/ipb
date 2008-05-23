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
|   > Display Script - File + Category
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .03
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class idm_display
{
	var $ipsclass;
	var $catlib;
	var $funcs;

	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";
	
	var $hascats		= 0;
	var $hasfiles		= 0;
	var $canadd			= 0;
	var $canmod			= 0;

	var $sep_char 		= '<{ACTIVE_LIST_SEP}>';
	var $total_active	= 0;
	
	var $sort_num		= array( 5, 10, 15, 20, 25 );
	
	var $sort_by		= array( 'A-Z'			=> 'ASC',
								 'Z-A'			=> 'DESC' );
								 
	var $sort_key		= array( 'file_downloads'	=> 'downloads',
								 'file_submitted'	=> 'submitted',
								 'file_name'		=> 'title',
								 'file_views'		=> 'views',
								 'file_rating'		=> 'rating',
								 'file_updated'		=> 'updated');

    /*-------------------------------------------------------------------------*/
	// Our run_me function
    /*-------------------------------------------------------------------------*/

	function run_me()
	{
		//-------------------------------------------
		// Page title and navigation bar
		//-------------------------------------------
		
	    $this->nav[] 		= "<a href='{$this->ipsclass->base_url}autocom=downloads'>" . $this->ipsclass->lang['idm_navbar'] . '</a>';
		$this->page_title 	= $this->ipsclass->vars['board_name'] . " -> " . $this->ipsclass->lang['idm_pagetitle'];
				
		//-------------------------------------------
		// Files per page dropdown
		//-------------------------------------------
		
		if( $this->ipsclass->vars['idm_ddfilesperpage'] )
		{
			$perpage = explode( ",", $this->ipsclass->vars['idm_ddfilesperpage'] );

			if( count( $perpage ) )
			{
				$this->sort_num = $perpage;
				unset($perpage);
			}
		}
		
		//-------------------------------------------
		// Moderation ids
		//-------------------------------------------
		
		$this->ipsclass->input['selectedfileids'] = $this->ipsclass->my_getcookie( 'modfileids' );
		
		$this->ipsclass->input['selectedfilecount'] = intval( count( preg_split( "/,/", $this->ipsclass->input['selectedfileids'], -1, PREG_SPLIT_NO_EMPTY ) ) );
		
		if( $this->ipsclass->input['selectedfilecount'] > 0 )
		{
			$this->ipsclass->lang['mod_button'] .= ' (' . $this->ipsclass->input['selectedfilecount'] . ')';
		}
		
		if( !isset($this->ipsclass->input['showcat']) )
		{
			$this->ipsclass->my_setcookie('modfileids', '', 0);	
		}
		
		//-------------------------------------------
		// Check permissions
		//-------------------------------------------
		
		if( count($this->catlib->member_access['show']) == 0 AND $this->ipsclass->input['code'] != 'sst' )
		{
			if( count($this->catlib->cat_lookup) == 0 )
			{
				$this->output .= $this->funcs->produce_error( 'no_downloads_cats_created' );
			}
			else
			{
				$this->output .= $this->funcs->produce_error( 'no_downloads_permissions' );
			}
		}
		else
		{
			if( count( $this->catlib->member_access['add'] ) > 0 )
			{
				$this->canadd = 1;
			}
			
			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$this->canmod = 1;
			}
			else
			{
				if( $this->ipsclass->input['code'] == 'cat' )
				{
					if( is_array( $this->catlib->cat_mods[ $this->ipsclass->input['id'] ] ) )
					{
						if( count($this->catlib->cat_mods[ $this->ipsclass->input['id'] ]) )
						{
							foreach( $this->catlib->cat_mods[ $this->ipsclass->input['id'] ] as $k => $v )
							{
								if( $k == "m".$this->ipsclass->member['id'] )
								{
									if( $v['modcanapp'] )
									{
										$this->canmod 	= 1;
									}
								}
								else if( $k == "g".$this->ipsclass->member['mgroup'] )
								{
									if( $v['modcanapp'] )
									{
										$this->canmod 	= 1;
									}
								}
							}
						}
					}
				}
				else
				{				
					if( is_array( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] ) )
					{
						if( count($this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ]) )
						{
							foreach( $this->catlib->group_mods[ $this->ipsclass->member['mgroup'] ] as $k => $v )
							{
								if( $v['modcanapp'] )
								{
									$this->canmod = 1;
								}
								if( $v['modcanbrok'] )
								{
									$this->canmod = 1;
								}
							}
						}
					}
					else if( is_array( $this->catlib->mem_mods[ $this->ipsclass->member['id'] ] ) )
					{
						if( count($this->catlib->mem_mods[ $this->ipsclass->member['id'] ]) )
						{
							foreach( $this->catlib->mem_mods[$this->ipsclass->member['id'] ] as $k => $v )
							{
								if( $v['modcanapp'] )
								{
									$this->canmod = 1;
								}
								if( $v['modcanbrok'] )
								{
									$this->canmod = 1;
								}
							}
						}
					}
				}
			}			
			
			switch( $this->ipsclass->input['code'] )
			{
				case 'cat':
					$this->display_category( $this->ipsclass->input['id'] );
					break;
				
				case 'file':
					$this->display_file( $this->ipsclass->input['id'] );
					break;
					
				case 'ss':
					$this->show_screenshot();
					break;
					
				case 'sst':
					// This is here so we can adjust how screenshots pulled from topics should be
					// handled should we need to in the future.
					$this->show_screenshot();
					break;					
				
				default:
					$this->display_category( 0 );
					break;
			}
		}
		
		$this->get_page_end();
		
		//-------------------------------------------
		// Hamesh suggestion for SEO reasons
		//-------------------------------------------
		
		$this->page_title = strip_tags( implode( ' -> ', array_reverse( $this->nav ) ) );
		
		//-------------------------------------------
		// Output
		//-------------------------------------------
		
    	$this->ipsclass->print->add_output( $this->output );
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'NAV' => $this->nav, 'JS' => $this->ipsclass->compiled_templates['skin_global']->get_rte_css() ) );
	}
	
	
    /*-------------------------------------------------------------------------*/
    // File Display
    /*-------------------------------------------------------------------------*/
    	
	function display_file( $file_id=0 )
	{
		if( $file_id == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'file_not_found' );
			return;
		}
		
		$file_id = intval($file_id);
		
		$this->ipsclass->DB->cache_add_query( 'get_monster_file', array( 'file_id'		=> $file_id	), 'sql_idm_queries'	);
		$this->ipsclass->DB->cache_exec_query();

		$file = $this->ipsclass->DB->fetch_row();
		
		if( !$file['file_id'] )
		{
			$this->output .= $this->funcs->produce_error( 'file_not_found' );
			return;
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod );		
		
		if( $file['file_submitter'] == 0 OR !$file['members_display_name'] )
		{
			$file['members_display_name'] = $this->ipsclass->lang['idm_guestlang'];
		}
		else
		{
			$file['members_display_name'] = "<a href='{$this->ipsclass->base_url}showuser={$file['file_submitter']}'>{$file['members_display_name']}</a>";
		}
		
		$category = $this->catlib->cat_lookup[$file['file_cat']];	
		
		$file['cdisclaimer'] = $category['cdisclaimer'] ? 1 : 0;	
		
		require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
		$parser = new parse_bbcode();
		$parser->ipsclass =& $this->ipsclass;
		$parser->allow_update_caches = 1;
		$parser->bypass_badwords = intval($this->ipsclass->member['g_bypass_badwords']);		
		$parser->parse_html = $category['coptions']['opt_html'];
		$parser->parse_bbcode = $category['coptions']['opt_bbcode'];
		$parser->parse_nl2br = 1;

		$file['file_desc'] = $parser->pre_display_parse( $file['file_desc'] );
		
		if( $file['file_broken_reason'] )
		{
			$file['file_broken_reason'] = $parser->pre_display_parse( $file['file_broken_reason'] );
		}
		
		if( ! in_array( $file['file_cat'], $this->catlib->member_access['view'] ) )
		{
			if( $category['coptions']['opt_noperm_view'] )
			{
				$this->output .= $this->funcs->produce_error( $category['coptions']['opt_noperm_view'], 1 );
			}
			else
			{
				$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			}
			return;
		}
		
		if( $this->ipsclass->member['g_is_supmod'] )
		{
			$cantog   = 1;
		}
		else
		{
			if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcanapp'] )
							{
								$cantog = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcanapp'] )
							{
								$cantog = 1;
							}
						}
					}
				}
			}
		}
		
		if( $file['file_open'] == 0 )
		{
			if( $cantog == 0 AND $this->ipsclass->member['id'] != $file['file_submitter'] )
			{
				$this->output .= $this->funcs->produce_error( 'file_not_found' );
				return;
			}
		}
		
		$file['submit_date'] = $this->ipsclass->get_date( $file['file_submitted'], 'LONG' );
		
		$file['update_date'] = $this->ipsclass->get_date( $file['file_updated'], 'LONG' );
		
		$file['formatted_size'] = $this->ipsclass->size_format( $file['file_size'] );
		
		/*
			Get estimated download times
			8 bits = 1 byte
			56kbps, 768kbps, 1.5Mbps, 3Mbps, 10Mbps
		*/
		
		$bit_count = $file['file_size'] * 8;

		$file['56k_speed'] 		= $this->time_remaining($bit_count / 57344);
		$file['dsl_speed']		= $this->time_remaining($bit_count / 786432);
		$file['t1_speed']		= $this->time_remaining($bit_count / 1572864);
		$file['cable_speed']	= $this->time_remaining($bit_count / 3072000);
		$file['fios_speed']		= $this->time_remaining($bit_count / 10485760);
		
		$rel 	 				= $this->ipsclass->vars['idm_ss_popup'] == 'l' ? ' rel="lightbox"' : '';
		
		$file['screenshot_display'] = $file['file_ssname'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=display&amp;code=ss&amp;full=1&amp;id={$file['file_id']}' onclick=\"PopUp('{$this->ipsclass->base_url}autocom=downloads&amp;req=display&amp;code=ss&amp;full=1&amp;id={$file['file_id']}','SS','640','480',1,1,1); return false;\"{$rel}>{$this->ipsclass->lang['ss_clickhere']}</a>"
															: ( $file['file_ssurl'] ? "<a href='{$file['file_ssurl']}' onclick=\"PopUp('{$file['file_ssurl']}','SS','640','480',1,1,1); return false;\"{$rel}>{$this->ipsclass->lang['ss_clickhere']}</a>" : "<i>{$this->ipsclass->lang['ss_notavail']}</i>" );

		$file['the_approver'] = "";
		if( $this->ipsclass->vars['idm_show_approver'] )
		{
			if( $file['file_approver'] )
			{
				$file['approve_date']  = $this->ipsclass->get_date( $file['file_approvedon'], 'LONG' );
				$file['show_approver'] = 1;
			}
		}
		else if( $cantog )
		{
			if( $file['file_approver'] )
			{
				$file['approve_date'] = $this->ipsclass->get_date( $file['file_approvedon'], 'LONG' );
				$file['show_approver'] = 1;
			}
		}			
		
		if( $category['coptions']['opt_topice'] )
		{
			if( $file['file_topicid'] )
			{
				$topiclink = "<a href='{$this->ipsclass->base_url}showtopic={$file['file_topicid']}'>{$this->ipsclass->lang['file_showtopic']}</a>";
			}
			else
			{
				$topiclink = $this->ipsclass->lang['ss_notavail'];
			}
		}
		
		$comments_link = "";
		
		if( $category['coptions']['opt_comments'] )
		{
			if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
			{
				$iscommod = 0;
				
				if( $this->ipsclass->member['g_is_supmod'] )
				{
					$iscommod = 1;
				}
				else if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
				{
					if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
					{
						foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
						{
							if( $k == "m".$this->ipsclass->member['id'] )
							{
								if( $v['modcancomments'] )
								{
									$iscommod = 1;
								}
							}
							else if( $k == "g".$this->ipsclass->member['mgroup'] )
							{
								if( $v['modcancomments'] )
								{
									$iscommod = 1;
								}
							}
						}
					}
				}

				if( $iscommod )
				{
					$limiter  = "";
				}
				else
				{
					$limiter  = " AND comment_open=1";
				}
				
				$max = $this->ipsclass->DB->simple_exec_query( array( 'select' => "COUNT(*) as total_comments",
																	  'from'   => "downloads_comments",
																	  'where'  => "comment_fid={$file['file_id']}{$limiter}"
											  				  )		);
											  				  
				$this->ipsclass->lang['file_compop'] = sprintf( $this->ipsclass->lang['file_compop'], $max['total_comments'] );
									  				  				
				$comments_link = "<a href='#' onclick=\"javascript:PopUp('{$this->ipsclass->base_url}autocom=downloads&amp;req=comments&amp;code=pop_com&amp;file={$file['file_id']}','COM','700','480',1,1,1)\">{$this->ipsclass->lang['file_compop']}</a>";
			}
		}		
		
		if( in_array( $file['file_cat'], $this->catlib->member_access['rate'] ) )
		{
			$file['_allow_rate'] = 1;
		}
		
		$this->ipsclass->load_language( 'lang_topic' );
		$file['_rate_img']      = $this->ipsclass->compiled_templates['skin_downloads']->file_rating_image( 0 );
		
		if( !is_null($file['file_votes']) AND $file['file_votes'] != '' AND $file['file_votes'] != '0' )
		{
			$votes = unserialize(stripslashes($file['file_votes']));
			
			if( is_array($votes) AND count($votes) > 0 )
			{
				foreach( $votes as $k => $v )
				{
					if( $k == $this->ipsclass->member['id'] )
					{			
						$file['_rating_value'] 	= $v;
						$file['_allow_rate']	= 0;
					}
				}
			}
		}
		
		$file['_rating_value'] 	= $file['_rating_value'] ? $file['_rating_value'] : $this->ipsclass->lang['you_have_not_rated'];
		
		$file['_rate_int'] 		= $file['file_rating'];
		$file['_rate_cnt']		= count($votes);
		
		$file['_rate_img']  	= $this->ipsclass->compiled_templates['skin_downloads']->file_rating_image($file['_rate_int'] );
		
		$file['vote_panel'] 	= $this->ipsclass->compiled_templates['skin_downloads']->vote_pips( $file );

		
    	$cfield_output 			= array();
    	$file['custom_block'] 	= "";
    	
		if( $category['ccfields'] )
		{
    		require_once( DL_PATH.'lib/lib_cfields.php' );
    		$fields = new lib_cfields();
    		$fields->ipsclass =& $this->ipsclass;
    	
    		$fields->file_id   	= $file['file_id'];
    		$fields->cat_id		= $category['ccfields'];
    		
    		$fields->cache_data = $this->ipsclass->cache['idm_cfields'];
    	
    		$fields->init_data();
    		$fields->parse_to_view();
    		
    		foreach( $fields->out_fields as $id => $data )
    		{
	    		$data = $data ? $data : $this->ipsclass->lang['cat_no_info'];
	    		
				$cfield_output[] = array( 'title' => $fields->field_names[ $id ], 'data' => $data );
    		}
		}
		
		if( $category['ccfields'] OR $category['coptions']['opt_topice'] OR $comments_link )
		{
			$file['custom_block'] = $this->ipsclass->compiled_templates['skin_downloads']->cfield_file_wrapper( $topiclink, $cfield_output, $comments_link );
		}
		
		$canedit = 0;
		$canapp  = 0;
		$candel  = 0;
		$canbrok = 0;
		
		if( $this->ipsclass->member['id'] == $file['file_submitter'] && $this->ipsclass->vars['idm_allow_edit'] )
		{
			$canedit = 1;
		}
		
		if( $this->ipsclass->member['id'] == $file['file_submitter'] && $this->ipsclass->vars['idm_allow_delete'] )
		{
			$candel  = 1;
		}		
		
		if( $this->ipsclass->member['g_is_supmod'] )
		{
			$canedit = 1;
			$candel  = 1;
			$canapp  = 1;
			$canbrok = 1;
		}
		else
		{
			if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
							}
							if( $v['modcanapp'] )
							{
								$canapp = 1;
							}
							if( $v['modcandel'] )
							{
								$candel = 1;
							}
							if( $v['modcanbrok'] )
							{
								$canbrok = 1;
							}
						}
						else if( $k == "g".$this->ipsclass->member['mgroup'] )
						{
							if( $v['modcanedit'] )
							{
								$canedit = 1;
							}
							if( $v['modcanapp'] )
							{
								$canapp = 1;
							}
							if( $v['modcandel'] )
							{
								$candel = 1;
							}
							if( $v['modcanbrok'] )
							{
								$canbrok = 1;
							}							
						}
					}
				}
			}
		}							
		
		if( $file['file_submitter'] )
		{
			$operations = array( "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=search&amp;code=all_author&amp;id={$file['file_submitter']}'>{$this->ipsclass->lang['findallbymem']}</a>" );
		}
		else
		{
			$operations = array();
		}
		
		$old_versions = array();
		
		if( $this->ipsclass->vars['idm_versioning'] )
		{
			require_once( DL_PATH.'lib/lib_versioning.php' );
			$versions 				= new lib_versioning();
			$versions->ipsclass 	=& $this->ipsclass;
			
			$versions->file_id 		= $file['file_id'];
			$versions->file_data	= $file;
			
			$versions->init();
			$old_versions = $versions->retrieve_versions();
		}			
		
		if( $this->ipsclass->member['id'] )
		{
			if( $file['file_broken'] == 0 )
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=broken&amp;id={$file['file_id']}'>{$this->ipsclass->lang['reportbroken']}</a>";
			}
			
			if( $file['file_broken'] == 1 AND $canbrok == 1 )
			{
				$file['mod_canbrok']	= 1;
				$broken_info			= explode( "|", $file['file_broken_info'] );
				
				if( count($broken_info) )
				{
					$this->ipsclass->lang['file_broken_info'] = sprintf( $this->ipsclass->lang['file_broken_info'], $this->ipsclass->base_url . 'showuser=' . $broken_info[0], $broken_info[1], $this->ipsclass->get_date( $broken_info[2], 'SHORT' ) );
				}
				
				$operations[] 			= "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=notbroken&amp;id={$file['file_id']}'>{$this->ipsclass->lang['unreportbroken']}</a>";
			}
			
			if( $canedit )
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=submit&amp;code=edit_main&amp;id={$file['file_id']}'>{$this->ipsclass->lang['editfile']}</a>";
			}
			
			if( $candel )
			{
				$operations[] = "<a href='#' onclick=\"javascript:single_delete('{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=delete&amp;return=1&amp;id={$file['file_id']}')\">{$this->ipsclass->lang['deletefile']}</a>";
			}
			
			if( $candel AND ( count($old_versions) > 0 AND $this->ipsclass->vars['idm_versioning'] ) )
			{
				// We need to get the latest id without messing up the old_versions array
				ksort($old_versions);
				$new_old_versions = $old_versions;
				$last = array_pop( $new_old_versions );
				unset($new_old_versions);
				
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=versions&amp;id={$file['file_id']}&amp;do=restore&amp;rid={$last['b_id']}'>{$this->ipsclass->lang['restorefile']}</a>";
			}			
			
			if( $canapp && $file['file_open'] == 1 )
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=togglefile&amp;return=1&amp;id={$file['file_id']}'>{$this->ipsclass->lang['unnapprovefile']}</a>";
			}
			else if( $canapp && $file['file_open'] == 0 )
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=mod&amp;code=togglefile&amp;return=1&amp;id={$file['file_id']}'>{$this->ipsclass->lang['approvefile']}</a>";
			}
			
			$this->ipsclass->DB->build_query( array( 'select' => 'COUNT(fid) as cnt',
													 'from'	  => 'downloads_favorites',
													 'where'  => "ffid={$file['file_id']} AND fmid={$this->ipsclass->member['id']}"
											)		);
			$this->ipsclass->DB->exec_query();
			
			$row = $this->ipsclass->DB->fetch_row();
			
			if( $row['cnt'] > 0 )
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_favs&amp;return=1&amp;code=do_remove&amp;rm_{$file['file_id']}=1'>{$this->ipsclass->lang['removefavs']}</a>";
			}
			else
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_favs&amp;return=1&amp;code=addfavs&amp;id={$file['file_id']}'>{$this->ipsclass->lang['addtofavs']}</a>";
			}
			
			$members = array();
			
			if( !is_null($file['file_sub_mems']) AND $file['file_sub_mems'] != '' )
			{
				// Get rid of the extra commas
				$file['file_sub_mems'] = $this->ipsclass->clean_perm_string( $file['file_sub_mems'] );
				
				$members = explode( ",", $file['file_sub_mems'] );
				if( in_array( $this->ipsclass->member['id'], $members ) )
				{
					$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_subs&amp;return=1&amp;code=do_remove&amp;rm_{$file['file_id']}=1'>{$this->ipsclass->lang['already_subscribed']}</a>";
				}
				else
				{
					$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_subs&amp;return=1&amp;code=addsub&amp;id={$file['file_id']}'>{$this->ipsclass->lang['subtofile']}</a>";
				}
			}
			else
			{
				$operations[] = "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;req=ucp_subs&amp;return=1&amp;code=addsub&amp;id={$file['file_id']}'>{$this->ipsclass->lang['subtofile']}</a>";
			}
		}
		
		$operations_html = "<ul>";
		
		foreach( $operations as $k => $v )
		{
			$operations_html .= "<li>{$v}</li>";
		}
		
		$operations_html .= "</ul>";
		
		$file['ops_block'] = $this->ipsclass->compiled_templates['skin_downloads']->operations_block( $operations_html );				
																	
		// Update views now or later?		
		if( $this->ipsclass->vars['idm_updateviews'] == 1 )
		{
			$this->ipsclass->DB->do_shutdown_update( "downloads_files", array( 'file_views' => $file['file_views']+1 ), "file_id=".$file['file_id'] );
			$this->catlib->rebuild_fileinfo($file['file_cat']);
		}
		else
		{
			$this->ipsclass->DB->do_shutdown_insert( "downloads_fileviews", array( 'view_fid' => $file['file_id'] ) );
		}
		
		$this->nav = array_merge( $this->nav, $this->catlib->get_nav( $file['file_cat'] ) );
		$this->nav[] = $file['file_name'];
			
		$this->page_title .= " -> ".$this->catlib->cat_lookup[ $file['file_cat'] ]['cname']." -> {$file['file_name']}";		
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_information( $file );
		
		if( $category['coptions']['opt_filess'] )
		{
			$this->output = str_replace( "<!--SCREENSHOT_DISPLAY-->", $this->ipsclass->compiled_templates['skin_downloads']->screenshot_inline( array( 'file_ssname' => $file['file_ssname'], 'file_ssurl' => $file['file_ssurl'], 'file_id' => $file['file_id'], 'thumb_x' => $category['coptions']['opt_thumb_x'] ) ), $this->output );
		}
		
		if( $this->ipsclass->vars['idm_versioning'] AND count($old_versions) > 0 )
		{
			$versions = array();
			
			krsort($old_versions); // Sort by date, newest to oldest
			
			foreach( $old_versions as $v )
			{
				$v['css_1'] = 'row1';
				$v['css_2'] = 'row2';
				
				if( $v['b_hidden'] == 1 )
				{
					$v['css_1'] = 'row2shaded';
					$v['css_2'] = 'row4shaded';
				}
				
				$v['b_updated_formatted'] = $this->ipsclass->get_date( $v['b_updated'], 'SHORT' );
				
				if( $v['b_filereal'] )
				{
					$v['b_filename'] = $v['b_filereal'];
				}
				else
				{
					$v['b_filename'] = str_replace( $v['b_fileid']."-", "", $v['b_filename'] );
					$v['b_filename'] = preg_replace( "/^\d{10,11}\-(.+?)$/", "\\1", $v['b_filename'] );
				}
				
				$versions[] = $v;
			}
			
			$this->output = str_replace( "<!--VERSION_DISPLAY-->", $this->ipsclass->compiled_templates['skin_downloads']->versions_row( $versions, $candel, $canapp ), $this->output );
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod, '', ($category['coptions']['opt_comments'] AND $this->ipsclass->vars['idm_comment_display'] != 'pop' ) ? 1 : 0 );
		
		if( $category['coptions']['opt_comments'] )
		{
			if( $this->ipsclass->vars['idm_comment_display'] == 'in' )
			{
				$this->ipsclass->load_language( 'lang_topic' );
				
				$sqr 	= isset($this->ipsclass->member['_cache']['qr_open']) ? $this->ipsclass->member['_cache']['qr_open'] : 0;
				$show 	= $sqr == 1 ? '' : 'none';
							
				$this->output = str_replace( '<!--IBF.QUICK_REPLY_OPEN-->' 	, $this->ipsclass->compiled_templates[ 'skin_downloads' ]->quick_reply_box_open( $show, $this->ipslcass->md5_check ), $this->output );
			
				require_once( DL_PATH . 'lib/lib_comments.php' );
				$comments 			= new lib_comments();
				$comments->ipsclass =& $this->ipsclass;
				$comments->parser	=& $parser;
				$comments->catlib	=& $this->catlib;
				$comments->funcs	=& $this->funcs;
				$comments->init();
				
				$comment_output = $comments->return_file_comments( $file );
				
				if( $comment_output )
				{
					$this->output = str_replace( '<!--COMMENTS-->', $comment_output, $this->output );
				}
				
				if( $this->mod AND !$comments->no_comments )
				{
					$this->output = str_replace( '<!--IDM_MOD_COMMENTS-->', $comments->mod_form(), $this->output );
				}
			}
		}
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Category Display (and index page also, technically)
    /*-------------------------------------------------------------------------*/
    	
	function display_category( $catid=0 )
	{
		$catid = $catid ? $catid : 0;
		
		if( $catid == 0 )
		{
			if( count($this->catlib->cat_cache[ $catid ]) == 0 )
			{
				$this->output .= $this->funcs->produce_error( 'no_downloads_categories' );
				return;
			}
		}
		
		$lang_bit = $catid == 0 ? $this->ipsclass->lang['category_main_header'] : $this->catlib->cat_lookup[$catid]['cname']." <{CAT_IMG}> ".$this->ipsclass->lang['subcategory_main_header'];

		$collapsed_ids = ','.$this->ipsclass->my_getcookie('collapseprefs').',';
		
		$show['div_fo'] = 'show';
		$show['div_fc'] = 'none';
				
		if ( strstr( $collapsed_ids, ",idm_cats," ) )
		{
			$show['div_fo'] = 'none';
			$show['div_fc'] = 'show';
		}
		
		$category = $this->catlib->cat_lookup[$catid];
		
		$cat_cnt = 0;
		
		if( count( $this->catlib->cat_cache[ $catid ] ) > 0 )
		{
			$category_html = "";
			
			$this->canmod = 0;

			foreach( $this->catlib->cat_cache[ $catid ] as $cid => $cinfo )
			{
				if( in_array( $cid, $this->catlib->member_access['show'] ) )
				{
					$canapp = 0;

					if( $this->ipsclass->member['g_is_supmod'] )
					{
						$canapp  		= 1;
						$this->canmod 	= 1;
					}
					else
					{
						if( is_array( $this->catlib->cat_mods[ $cid ] ) )
						{
							if( count($this->catlib->cat_mods[ $cid ]) )
							{
								foreach( $this->catlib->cat_mods[ $cid ] as $k => $v )
								{
									if( $k == "m".$this->ipsclass->member['id'] )
									{
										if( $v['modcanapp'] )
										{
											$canapp 		= 1;
											$this->canmod 	= 1;
										}
									}
									else if( $k == "g".$this->ipsclass->member['mgroup'] )
									{
										if( $v['modcanapp'] )
										{
											$canapp 		= 1;
											$this->canmod 	= 1;
										}
									}
								}
							}
						}
					}

					$cinfo['cname'] = $this->ipsclass->compiled_templates['skin_downloads']->cat_link( $cinfo['cid'], $cinfo['cname'] );
					$cinfo['latest_file'] = "";
					
					if( is_array( $cinfo['cfileinfo'] ) AND count($cinfo['cfileinfo']) )
					{
						if( $cinfo['cfileinfo']['fid'] > 0 )
						{
							$cinfo['cfileinfo']['fdate'] 	= $cinfo['cfileinfo']['date'] > 0 ? $this->ipsclass->get_date( $cinfo['cfileinfo']['date'], 'LONG' ) : "--";
							$cinfo['cfileinfo']['author']  	= $cinfo['cfileinfo']['mid'] > 0 ? $this->ipsclass->make_profile_link( $cinfo['cfileinfo']['mname'], $cinfo['cfileinfo']['mid'] ) : $this->ipsclass->lang['idm_guestlang'];
							$cinfo['cfileinfo']['datelang']	= $cinfo['cfileinfo']['updated'] == 0 ? $this->ipsclass->lang['catfile_date'] : $this->ipsclass->lang['catfile_date1'];
							$cinfo['cfileinfo']['file']		= $cinfo['cfileinfo']['fid'] > 0 ? $this->ipsclass->compiled_templates['skin_downloads']->file_link( $cinfo['cfileinfo']['fname'], $cinfo['cfileinfo']['fid'] ) : $this->ipsclass->lang['cat_no_info'];
							$cinfo['latest_file']			= $this->ipsclass->compiled_templates['skin_downloads']->latest_file_info( $cinfo['cfileinfo'] );
						}
						else
						{
							$cinfo['latest_file']			= $this->ipsclass->lang['cat_no_info'];
						}
					}
					else
					{
						$cinfo['latest_file']			= $this->ipsclass->lang['cat_no_info'];
					}
					
					$cinfo['cfileinfo']['total_views']	= $cinfo['cfileinfo']['total_views'] ? $cinfo['cfileinfo']['total_views'] : 0;
					$cinfo['cfileinfo']['total_files']	= $cinfo['cfileinfo']['total_files'] ? $cinfo['cfileinfo']['total_files'] : 0;
					$cinfo['cfileinfo']['total_downloads'] = $cinfo['cfileinfo']['total_downloads'] ? $cinfo['cfileinfo']['total_downloads'] : 0;
					
					$cinfo['subcategories'] = "";
					
					if( count($this->catlib->subcat_lookup[$cid]) > 0 )
					{
						$sub_links = array();
						
						foreach( $this->catlib->subcat_lookup[$cid] as $blank_key => $subcat_id )
						{
							if( in_array( $subcat_id, $this->catlib->member_access['show'] ) )
							{
								$subcat_data = $this->catlib->cat_lookup[ $subcat_id ];
							
								if ( is_array( $subcat_data ) )
								{
									$sub_links[] = $this->ipsclass->compiled_templates['skin_downloads']->cat_link( $subcat_data['cid'], $subcat_data['cname'] );
								}
							}
						}
						
						if( count( $sub_links ) )
						{
							$cinfo['subcategories'] = "<br /><br />".$this->ipsclass->lang['catinfo_subcats'].implode( $sub_links, $this->sep_char." " );
						}
					}
					
					$cinfo['pending'] = ($cinfo['cfileinfo']['pending_files'] > 0 && $canapp) ? $this->ipsclass->compiled_templates['skin_downloads']->que_indicator( "showcat={$cid}&amp;filter=que", $this->ipsclass->lang['cat_qued'], $cinfo['cfileinfo']['pending_files'] ) : '';
					
					$category_rows[] = $cinfo;
					$cat_cnt++;
				}
			}

			if( $cat_cnt > 0 )
			{
				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod );

				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->categories( $show, $lang_bit, $category_rows);
				
				$this->hascats = 1;
				unset($category_html);
			}
			else
			{
				if( !in_array( $catid, $this->catlib->member_access['show'] ) )
				{
					$this->output .= $this->funcs->produce_error('no_permitted_categories');
					return;
				}
			}
		}
		
		if( !$this->hascats && !in_array( $catid, $this->catlib->member_access['show'] ) )
		{
			if( $category['coptions']['opt_noperm_view'] )
			{
				$this->output .= $this->funcs->produce_error( $category['coptions']['opt_noperm_view'], 1 );
			}
			else
			{
				$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			}
			return;
		}			
		
		if( $catid > 0 )
		{
			if( in_array( $catid, $this->catlib->member_access['show'] ) && $category['coptions']['opt_disfiles'] )
			{
				// We can view files in this category

				$sort_by  = ( $this->ipsclass->input['sort_by'] && in_array( $this->ipsclass->input['sort_by'], $this->sort_by ) ) ? $this->ipsclass->input['sort_by'] : ( $category['coptions']['opt_sortby'] ? $this->sort_by[$category['coptions']['opt_sortby']] : 'DESC' );
				$sort_key = ( $this->ipsclass->input['sort_key'] && array_key_exists( $this->ipsclass->input['sort_key'], $this->sort_key ) ) ? $this->ipsclass->input['sort_key'] : ( $category['coptions']['opt_sortorder'] ? 'file_'.$category['coptions']['opt_sortorder'] : 'file_updated' );
				
				$num = $this->ipsclass->input['num'] ? intval($this->ipsclass->input['num']) : $this->ipsclass->vars['idm_filesperpage'];
				if( ! in_array( $num, $this->sort_num ) )
				{
					$num = $this->ipsclass->vars['idm_filesperpage'];
				}		
				
				$sortnum_options = "";
				foreach( $this->sort_num as $k => $v )
				{
					$selected = "";
					if( $v == $num )
					{
						$selected = " selected='selected'";
					}
					
					$lang = $v > 0 ? $v : $this->ipsclass->lang['search_nolimit'];
					
					$sortnum_options .= "<option value='{$v}'{$selected}>{$lang}</option>";
				}
				
				$sortkey_options = "";
				foreach( $this->sort_key as $k => $v )
				{
					$selected = "";
					if( $k == $sort_key )
					{
						$selected = " selected='selected'";
					}
					
					$lang = isset($this->ipsclass->lang['sortby_' . $v ]) ? $this->ipsclass->lang['sortby_' . $v ] : $v;
					
					$sortkey_options .= "<option value='{$k}'{$selected}>{$lang}</option>";
				}
				
				$sortby_options = "";
				foreach( $this->sort_by as $k => $v )
				{
					$selected = "";
					if( $v == $sort_by )
					{
						$selected = " selected='selected'";
					}
					
					$sortby_options .= "<option value='{$v}'{$selected}>{$k}</option>";
				}
				
				$sort['cid'] = $catid;
				$sort['key'] = $sortkey_options;
				$sort['by']  = $sortby_options;
				$sort['num'] = $sortnum_options;
				
				$st = $this->ipsclass->input['st'] ? intval($this->ipsclass->input['st']) : 0;
				$st = $this->ipsclass->input['dosort'] ? 0 : $st;
				
				$canapp = 0;
				$cancomments = 0;
				
				if( $this->ipsclass->member['g_is_supmod'] )
				{
					$canapp = 1;
					$cancomments = 1;
				}
				else
				{
					if( is_array( $this->catlib->cat_mods[ $catid ] ) )
					{
						if( count($this->catlib->cat_mods[ $catid ]) )
						{
							foreach( $this->catlib->cat_mods[ $catid ] as $k => $v )
							{
								if( $k == "m".$this->ipsclass->member['id'] )
								{
									if( $v['modcanapp'] )
									{
										$canapp = 1;
									}
									if( $v['modcancomments'] )
									{
										$cancomments = 1;
									}
								}
								else if( $k == "g".$this->ipsclass->member['mgroup'] )
								{
									if( $v['modcanapp'] )
									{
										$canapp = 1;
									}
									if( $v['modcancomments'] )
									{
										$cancomments = 1;
									}										
								}
							}
						}
					}
				}
				
				if( $canapp )
				{
					$openkey = "";
					$openkey1 = "";
					
					if ( $this->ipsclass->input['filter'] == 'que' )
					{
						$extrasort = "f.file_open ASC,";
					}
					else
					{
						$extrasort = "";
					}
				}
				else
				{
					$openkey = "AND ( file_open=1 OR file_submitter={$this->ipsclass->member['id']} )";
					$openkey1 = "AND ( f.file_open=1 OR f.file_submitter={$this->ipsclass->member['id']} )";
					$extrasort = "";
				}
				
				$this->ipsclass->DB->cache_add_query( 'category_get_total_filecount', array( 'category'		=> $category['cid'], 'open' => $openkey	), 'sql_idm_queries'	);
				$this->ipsclass->DB->cache_exec_query();

				$file_count = $this->ipsclass->DB->fetch_row();
				
				$page_links
					= $this->ipsclass->build_pagelinks( array(  'TOTAL_POSS'  => $file_count['max'],
													   			'PER_PAGE'    => $num,
													   			'CUR_ST_VAL'  => $st,
													   			'L_SINGLE'    => '&nbsp;',
													   			'BASE_URL'    => $this->ipsclass->base_url."autocom=downloads&amp;showcat=".$category['cid']."&amp;sort_by={$sort_by}&amp;sort_key={$sort_key}&amp;num={$num}",
													  )	  	 );

				if( !$this->hascats )
				{
					$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod, $page_links );							
				}
				
				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_top( $category['cname'], $this->hascats ? $page_links : '', $this->canmod, 'modform', 'app', $this->ipsclass->input['selectedfileids'], 'multimod' );
				
				if( $category['coptions']['opt_catss'] == 0 )
				{
					$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_thbar( $this->canmod );
				}
				
				if( $file_count['max'] == 0 )
				{
					$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_empty();
				}
				else
				{
					$this->ipsclass->DB->cache_add_query( 'category_get_files', array( 	'sort_by'    	=> $sort_by,
																						'sort_key' 		=> $sort_key,
																						'category'		=> $category['cid'],
																						'limita'		=> $st,
																						'limitb'		=> $num,
																						'open'			=> $openkey1,
																						'extra_sort'	=> $extrasort,
														)	, 'sql_idm_queries'		 );
					$this->ipsclass->DB->cache_exec_query();
					
					while( $row = $this->ipsclass->DB->fetch_row() )
					{
						$row['css'] = $row['file_open'] == 1 ? 'row2' : 'row2shaded';
						
						$row['file_submitteddis'] = $this->ipsclass->get_date( $row['file_submitted'], 'LONG' );
						
						if( $row['file_updated'] > $row['file_submitted'] )
						{
							$row['file_updateddis'] = "<strong>".$this->ipsclass->lang['catdis_updated']."</strong>".$this->ipsclass->get_date( $row['file_updated'], 'LONG' );
						}
						else
						{
							$row['file_updateddis'] = "&nbsp;";
						}
						
						if( $row['file_submitter'] == 0 OR !$row['members_display_name'] )
						{
							$row['members_display_name'] = $this->ipsclass->lang['idm_guestlang'];
						}
						
						$row['submitter'] = $this->ipsclass->make_profile_link( $row['members_display_name'], $row['file_submitter'] );
						$row['filename']  = $this->ipsclass->compiled_templates['skin_downloads']->file_link( $row['file_name'], $row['file_id'] );
						
						$row['comm_pending'] = ($row['file_pendcomments'] > 0 && $cancomments) ? "<br />".$this->ipsclass->compiled_templates['skin_downloads']->que_indicator( "showfile={$row['file_id']}&amp;filter=que#comments", $this->ipsclass->lang['com_qued'], $row['file_pendcomments'] ) : '';
						
						$row['un'] = 'un';
						
						if( $this->canmod )
						{
							if ( $this->ipsclass->input['selectedfileids'] )
							{
								if ( strstr( ','.$this->ipsclass->input['selectedfileids'].',', ','.$row['file_id'].',' ) )
								{
									$row['un'] = '';
								}
							}
						}
						
						if( $category['coptions']['opt_catss'] )
						{
							$thumbnail = "";
							$row['thumb_sizes'] = "";
							
							if( $row['file_thumb'] )
							{
								$thumbnail = $row['file_thumb'];
							}
							else
							{
								if( $category['coptions']['opt_thumb_x'] AND $category['coptions']['opt_thumb_y'] )
								{
									$row['thumb_sizes'] = "width='{$category['coptions']['opt_thumb_x']}' height='{$category['coptions']['opt_thumb_y']}' ";
								}
								$thumbnail = $row['file_ssname'];
							}
							
							$row['thumbnail'] = $thumbnail ? $this->ipsclass->base_url."autocom=downloads&amp;req=display&amp;code=ss&amp;id={$row['file_id']}" : "";
							
							if( $row['file_storagetype'] == 'ftp' )
							{
								$row['thumbnail'] = $thumbnail ? $this->ipsclass->vars['idm_remotessurl']."/".$thumbnail : "";
							}
							
							$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_row_withss( $row , $this->canmod );
						}
						else
						{
							$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_row_withoutss( $row , $this->canmod );
						}
							
					}
				}
				
				$option = '';
				
				if( $canapp )
				{
					$option  = "<option value='app'>{$this->ipsclass->lang['modact_approve']}</option>";
					$option .= "<option value='unapp'>{$this->ipsclass->lang['modact_unapprove']}</option>";
					$option .= "<option value='move'>{$this->ipsclass->lang['modact_move']}</option>";
				}
				
				$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_bottom( $page_links, $this->canmod, $option, $sort );
				
				$this->hasfiles = 1;
		 	}
		}
		
		if( !$this->hascats && !$this->hasfiles )
		{
			if( $category['coptions']['opt_noperm_view'] )
			{
				$this->output .= $this->funcs->produce_error( $category['coptions']['opt_noperm_view'], 1 );
			}
			else
			{
				$this->output .= $this->funcs->produce_error( 'no_permitted_categories' );
			}
			return;
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod, $this->hasfiles ? $page_links : '&nbsp;', 'top' );		
		
		if( $catid > 0 )
		{
			$this->nav = array_merge( $this->nav, $this->catlib->get_nav( $catid ) );
			
			$this->page_title .= " -> ".$this->catlib->cat_lookup[ $catid ]['cname'];
		}
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Screenshots Display (hides url so no direct access)
    /*-------------------------------------------------------------------------*/
    
	function show_screenshot()
	{
		// Don't update session..
		$this->ipsclass->DB->obj['shutdown_queries'] = array();
		
		if(! $this->ipsclass->input['id'] )
		{
			// No id?
			exit;
		}
		
		ob_end_clean();
		
		$file_id = intval($this->ipsclass->input['id']);
		
		$this->ipsclass->DB->cache_add_query( 'file_getss', array( 'file_id'		=> $file_id	), 'sql_idm_queries'	);
		$this->ipsclass->DB->cache_exec_query();

		$ss = $this->ipsclass->DB->fetch_row();
		
		switch( $ss['file_storagetype'] )
		{
			case 'web':
			case 'nonweb':
				if( $ss['file_thumb'] )
				{
					if( $this->ipsclass->input['full'] )
					{
						$thumb = $ss['file_ssname'];
					}
					else
					{
						$thumb = $ss['file_thumb'];
					}
				}
				else if( $ss['file_ssname'] )
				{
					$thumb = $ss['file_ssname'];
				}
				else
				{
					// No screenshot anyways
					print $this->ipsclass->lang['catdis_ssnone'];
					exit;
				}
				
				if( $ss['file_storagetype'] == 'web' )
				{
					$path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->ipsclass->vars['idm_localsspath'] )."/";
				}
				else
				{
					$path = $this->ipsclass->vars['idm_localsspath']."/";
				}
				
				if( $this->ipsclass->input['full'] AND ($this->ipsclass->vars['idm_addwatermark'] OR $this->ipsclass->vars['idm_addcopyright']) )
				{
					$img_size = @getimagesize( $path.$thumb );
					
					require_once( DL_PATH.'lib/lib_thumb.php' );
					$image = new lib_thumb();
		
					$image->in_type        = 'file';
					$image->out_type       = 'TEMP';
					$image->desired_width  = $img_size[0];
					$image->desired_height = $img_size[1];	
					$image->fullsize	   = 1;				
					
					$image->in_file_dir    = $path;
					$image->in_file_name   = $thumb;					
					$image->gd_version     = $this->ipsclass->vars['gd_version'];
					$image->do_water	   = $this->ipsclass->vars['idm_addwatermark'];
					$image->water_path	   = $this->ipsclass->vars['idm_watermarkpath'];
					$image->do_copy		   = $this->ipsclass->vars['idm_addcopyright'];
					$image->cpy_txt		   = $this->ipsclass->vars['idm_copyrighttext'];
					
					$img = $image->generate_thumbnail();
					exit;
				}
				else
				{									
					if( file_exists($path.$thumb) )
					{
						
						header( "Content-Type: ".$ss['mime_mimetype'] );
						header( "Content-Disposition: inline; filename=\"".$thumb."\"" );
						header( "Content-Length: ".(string)(filesize( $path.$thumb ) ) );
						
						//-----------------------------------------
						// Open and display the file..
						//-----------------------------------------
						
						$fh = fopen( $path.$thumb, 'rb' );
						fpassthru( $fh );
						@fclose( $fh );
						exit();
					}
				}
				break;
				
			case 'db':
				if( $ss['storage_thumb'] )
				{
					if( $this->ipsclass->input['full'] )
					{
						$content = stripslashes(base64_decode($ss['storage_ss']));
					}
					else
					{
						$content = stripslashes(base64_decode($ss['storage_thumb']));
					}
				}
				else if( $ss['storage_ss'] )
				{
					$content = stripslashes(base64_decode($ss['storage_ss']));
				}
				
				if( !$content )
				{
					// No screenshot anyways
					ob_end_clean();
					print $this->ipsclass->lang['catdis_ssnone'];
					exit;
				}	
				
				if( $this->ipsclass->input['full'] AND ($this->ipsclass->vars['idm_addwatermark'] OR $this->ipsclass->vars['idm_addcopyright']) )
				{
					$fh = @fopen( $this->ipsclass->vars['upload_dir'].$ss['file_name'], 'wb' );
					@fputs ($fh, $contnet, strlen($contnet) );
					@fclose($fh);
					
					$img_size = @getimagesize($this->ipsclass->vars['upload_dir'].$ss['file_name']);
					
					require_once( DL_PATH.'lib/lib_thumb.php' );
					$image = new lib_thumb();
		
					$image->in_type        = 'file';
					$image->out_type       = 'TEMP';
					$image->desired_width  = $img_size[0];
					$image->desired_height = $img_size[1];	
					$image->fullsize	   = 1;				
					
					$image->in_file_dir    = $this->ipsclass->vars['upload_dir'];
					$image->in_file_name   = $ss['file_name'];					
					$image->gd_version     = $this->ipsclass->vars['gd_version'];
					$image->do_water	   = $this->ipsclass->vars['idm_addwatermark'];
					$image->water_path	   = $this->ipsclass->vars['idm_watermarkpath'];
					$image->do_copy		   = $this->ipsclass->vars['idm_addcopyright'];
					$image->cpy_txt		   = $this->ipsclass->vars['idm_copyrighttext'];
					$img = $image->generate_thumbnail();
					@unlink($this->ipsclass->vars['upload_dir'].$ss['file_name']);
					exit;
				}
				else
				{	
					header( "Content-Type: ".$ss['mime_mimetype'] );
					header( "Content-Disposition: inline; filename=\"".$thumb."\"" );
					header( "Content-Length: ".(string)(strlen( $content ) ) );
					
					print $content;
					exit;
				}
				break;
				
			case 'ftp':
				if( $ss['file_thumb'] )
				{
					if( $this->ipsclass->input['full'] )
					{
						$thumb = $ss['file_ssname'];
					}
					else
					{
						$thumb = $ss['file_thumb'];
					}
				}
				else if( $ss['file_ssname'] )
				{
					$thumb = $ss['file_ssname'];
				}
				else
				{
					// No screenshot anyways
					ob_end_clean();
					print $this->ipsclass->lang['catdis_ssnone'];
					exit;
				}
				
				if( $this->ipsclass->vars['idm_remoteurl'] AND
					$this->ipsclass->vars['idm_remoteport'] AND
					$this->ipsclass->vars['idm_remoteuser'] AND
					$this->ipsclass->vars['idm_remotepass'] AND
					$this->ipsclass->vars['idm_remotefilepath'] )
				{
					$conid = @ftp_connect( $this->ipsclass->vars['idm_remoteurl'], $this->ipsclass->vars['idm_remoteport'], 999999 );
					if( !$conid )
					{
						exit;
					}
					
					$check = @ftp_login( $conid, $this->ipsclass->vars['idm_remoteuser'], $this->ipsclass->vars['idm_remotepass'] );
					if( !$check )
					{
						exit;
					}
					
					@ftp_pasv( $conid, TRUE );
					
					if( !@ftp_get( $conid, $this->ipsclass->vars['upload_dir'].'/'.$thumb, $this->ipsclass->vars['idm_remotesspath']."/".$thumb, FTP_BINARY ) )
					{
						exit;
					}
					@ftp_close( $conid);
				}
				
				if( file_exists($this->ipsclass->vars['upload_dir'].'/'.$thumb) )
				{
					if( $this->ipsclass->input['full'] AND ($this->ipsclass->vars['idm_addwatermark'] OR $this->ipsclass->vars['idm_addcopyright']) )
					{
						$img_size = getimagesize($this->ipsclass->vars['upload_dir'].'/'.$thumb);
						
						require_once( DL_PATH.'lib/lib_thumb.php' );
						$image = new lib_thumb();
			
						$image->in_type        = 'file';
						$image->out_type       = 'TEMP';
						$image->desired_width  = $img_size[0];
						$image->desired_height = $img_size[1];
						$image->fullsize	   = 1;					
						
						$image->in_file_dir    = $this->ipsclass->vars['upload_dir'];
						$image->in_file_name   = $thumb;					
						$image->gd_version     = $this->ipsclass->vars['gd_version'];
						$image->do_water	   = $this->ipsclass->vars['idm_addwatermark'];
						$image->water_path	   = $this->ipsclass->vars['idm_watermarkpath'];
						$image->do_copy		   = $this->ipsclass->vars['idm_addcopyright'];
						$image->cpy_txt		   = $this->ipsclass->vars['idm_copyrighttext'];
						$img = $image->generate_thumbnail();
					}
					else
					{
						header( "Content-Type: ".$ss['mime_mimetype'] );
						header( "Content-Disposition: inline; filename=\"".$thumb."\"" );
						header( "Content-Length: ".(string)(filesize( $this->ipsclass->vars['upload_dir'].'/'.$thumb ) ) );
						
						//-----------------------------------------
						// Open and display the file..
						//-----------------------------------------
						
						$fh = fopen( $this->ipsclass->vars['upload_dir'].'/'.$thumb, 'rb' );
						fpassthru( $fh );
						@fclose( $fh );
					}
					
					@unlink( $this->ipsclass->vars['upload_dir'].'/'.$thumb );
					exit();
				}
				break;					
		}
		// Still here?
		exit;
	}
	
	
	
	
    /*-------------------------------------------------------------------------*/
    // Get page ending
    /*-------------------------------------------------------------------------*/
    	
	function get_page_end()
	{
		$collapsed_ids = ','.$this->ipsclass->my_getcookie('collapseprefs').',';
		
		$show['div_fo'] = 'show';
		$show['div_fc'] = 'none';
		$show1['div_fo'] = 'show';
		$show1['div_fc'] = 'none';
				
		if ( strstr( $collapsed_ids, ',idm_stats,' ) )
		{
			$show['div_fo'] = 'none';
			$show['div_fc'] = 'show';
		}
		
		if ( strstr( $collapsed_ids, ',idm_rand,' ) )
		{
			$show1['div_fo'] = 'none';
			$show1['div_fc'] = 'show';
		}		
		
		$active_users_html = $this->get_active_users();
		
		$show['mini_active'] 	= $this->total_active;
		$show['mini_files']		= intval($this->ipsclass->cache['idm_stats']['total_files']);
		$show['mini_downloads']	= intval($this->ipsclass->cache['idm_stats']['total_downloads']);
		
		$stats_lang = sprintf( $this->ipsclass->lang['stats_totalfiles'], $this->ipsclass->cache['idm_stats']['total_files'], $this->ipsclass->cache['idm_stats']['total_categories'] )."<br />";
		$stats_lang .= sprintf( $this->ipsclass->lang['stats_totalauthors'], $this->ipsclass->cache['idm_stats']['total_authors'] )."<br />";
		$stats_lang .= sprintf( $this->ipsclass->lang['stats_totaldls'], intval($this->ipsclass->cache['idm_stats']['total_downloads']) )."<br />";
		
		$latest_files = array();
		
		if( count($this->catlib->cat_lookup) )
		{
			foreach( $this->catlib->cat_lookup as $k => $v )
			{
				if( in_array( $k, $this->catlib->member_access['show'] ) )
				{
					if( $v['cfileinfo']['date'] > 0 )
					{
						$latest_files[ $v['cfileinfo']['date'] ] = $v['cfileinfo'];
					}
				}
			}
		}
		
		krsort($latest_files);

		$latest = count($latest_files) ? array_shift($latest_files) : array();

		unset($latest_files);
		
		$latest_file = $latest['fid'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;showfile={$latest['fid']}'>{$latest['fname']}</a>" : $this->ipsclass->lang['stats_noneyet'];
		$latest_author = $latest['mid'] ? "<a href='{$this->ipsclass->base_url}showuser={$latest['mid']}'>{$latest['mname']}</a>" : ( $latest['fid'] ? $this->ipsclass->lang['global_guestname'] : $this->ipsclass->lang['stats_noneyet'] );
		$latest_date = $this->ipsclass->get_date( $latest['date'], 'LONG' );
		
		$stats_lang .= sprintf( $this->ipsclass->lang['stats_latestfile'], $latest_file , $latest_author , $latest_date )."<br />";
		
		$show['stats'] = $stats_lang;
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->page_end( $show );

		if ( $this->ipsclass->vars['idm_displayactive'] )
		{
			$this->output = str_replace( "<!--IDM.ACTIVEUSERS-->", $active_users_html, $this->output );
		}
		
		if( $this->ipsclass->vars['idm_enablerandom'] AND count($this->catlib->member_access['view']) )
		{
			$count = $this->ipsclass->vars['idm_randomfiles'] > 0 ? $this->ipsclass->vars['idm_randomfiles'] : 5;
			$randcnt = $count*2; // Get double to be safe?
			$rand_ids = array();
			
			$widths = round(100/$count);
			
			$this->ipsclass->DB->build_query( array( 'select' 		=> 'f.file_id,f.file_cat,f.file_name,f.file_ssname,f.file_ssurl',
														'from'		=> array( 'downloads_files' => 'f'),
														'where' 	=> "f.file_open=1 AND c.copen=1 AND c.cid IN (" . implode( ',', $this->catlib->member_access['view'] ) . ")",
														'order' 	=> 'RAND() DESC',
														'limit' 	=> array(0,$count),
														'add_join'	=> array(
																			array(
																					'from'	=> array( 'downloads_categories' => 'c' ),
																					'where'	=> 'c.cid=f.file_cat',
																					'type'	=> 'left'
																				)
																			)
											)		);
			$this->ipsclass->DB->exec_query();
			
			$blocks = "";
			
			$actual_count = 0;
			while( $row = $this->ipsclass->DB->fetch_row() )
			{
				$row['width'] = $widths;
				
				if( in_array($row['file_cat'], $this->catlib->member_access['view']) )
				{
					$row['file_category'] = $this->catlib->cat_lookup[ $row['file_cat'] ]['cname'];
					
					$row['width'] = round(100/$this->ipsclass->vars['idm_randomfiles']);//$this->catlib->cat_lookup[ $row['file_cat'] ]['coptions']['opt_thumb_x'];

					$row['screenshot_spot'] = $row['file_ssname'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;showfile={$row['file_id']}'><img src='{$this->ipsclass->base_url}autocom=downloads&amp;req=display&amp;code=ss&amp;id={$row['file_id']}' border='0' alt='{$this->ipsclass->lang['filescreenshot']}' /></a>"
																	: ( $row['file_ssurl'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;showfile={$row['file_id']}'><img src='{$row['file_ssurl']}' border='0' alt='{$this->ipsclass->lang['filescreenshot']}' width='{$row['width']}' /></a>" : "<i>{$this->ipsclass->lang['catdis_ssnone']}</i>" );
					
					$block_data .= $this->ipsclass->compiled_templates['skin_downloads']->random_file_block( $row );
					
					$actual_count++;
				}
			}
			
			if( $block_data != '' )
			{
				for( $i=$actual_count;$i<$count;$i++)
				{
					$block_data .= $this->ipsclass->compiled_templates['skin_downloads']->random_file_block( array('width' => $widths) );
				}
				
				$random_html = $this->ipsclass->compiled_templates['skin_downloads']->random_file_row( $block_data, $show1 );
				
				$this->output = str_replace( "<!--RANDOM_FILES-->", $random_html, $this->output );
			}
		}
												
		
	}		

    /*-------------------------------------------------------------------------*/
    // Get active users
    /*-------------------------------------------------------------------------*/

    function get_active_users()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------

		$cut_off = time() - ( ($this->ipsclass->vars['au_cutoff'] != "") ? $this->ipsclass->vars['au_cutoff'] * 60 : 900 );
		$rows    = array();
		$active = array( 'TOTAL'   => 0 ,
						 'NAMES'   => "",
						 'GUESTS'  => 0 ,
						 'MEMBERS' => 0 ,
						 'ANON'    => 0 ,
					   );
		$ar_time = time();
		$cached = array();

		//-----------------------------------------
		// Get the users from the DB
		//-----------------------------------------
		
		if ( $this->ipsclass->member['id'] )
		{
			$rows = array( $ar_time => array(
											  'login_type'   => substr($this->ipsclass->member['login_anonymous'],0, 1),
											  'running_time' => $ar_time,
											  'member_id'    => $this->ipsclass->member['id'],
											  'member_name'  => $this->ipsclass->member['members_display_name'],
											  'member_group' => $this->ipsclass->member['mgroup'] 
											) 
						);
		}

		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, member_id, member_name, login_type, running_time, member_group',
													  'from'   => 'sessions',
													  'where'  => "location='mod:downloads' AND running_time > $cut_off",
											 )      );

		$this->ipsclass->DB->simple_exec();

		//-----------------------------------------
		// Fetch users into $rows
		//-----------------------------------------

		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			if ( $r['member_id'] > 0 && $r['member_id'] == $this->ipsclass->member['id'] )
			{
				continue;
			}
			
			$rows[ $r['running_time'].'.'.$r['id'] ] = $r;
		}

		//-----------------------------------------
		// Sort by time - oldest first
		//-----------------------------------------		
		
		krsort( $rows );

		foreach ( $rows as $result )
		{
			$last_date = $this->ipsclass->get_time( $result['running_time'] );

			//-----------------------------------------
			// Bot?
			//-----------------------------------------

			if ( strstr( $result['id'], '_session' ) )
			{
				//-----------------------------------------
				// Seen bot of this type yet?
				//-----------------------------------------

				$botname = preg_replace( '/^(.+?)=/', "\\1", $result['id'] );

				if ( ! $cached[ $result['member_name'] ] )
				{
					if ( $this->ipsclass->vars['spider_anon'] )
					{
						if ( $this->ipsclass->member['mgroup'] == $this->ipsclass->vars['admin_group'] )
						{
							$active['NAMES'] .= "{$result['member_name']}*{$this->sep_char} \n";
						}
					}
					else
					{
						$active['NAMES'] .= "{$result['member_name']}{$this->sep_char} \n";
					}

					$cached[ $result['member_name'] ] = 1;
				}
				else
				{
					//-----------------------------------------
					// Yup, count others as guest
					//-----------------------------------------

					$active['GUESTS']++;
				}
			}

			//-----------------------------------------
			// Guest?
			//-----------------------------------------

			else if ( ! $result['member_id'] )
			{
				$active['GUESTS']++;
			}

			//-----------------------------------------
			// Member?
			//-----------------------------------------

			else
			{
				if ( empty( $cached[ $result['member_id'] ] ) )
				{
					$cached[ $result['member_id'] ] = 1;

					$result['prefix'] = $this->ipsclass->cache['group_cache'][ $result['member_group'] ]['prefix'];
					$result['suffix'] = $this->ipsclass->cache['group_cache'][ $result['member_group'] ]['suffix'];

					if ($result['login_type'])
					{
						if ( ($this->ipsclass->member['mgroup'] == $this->ipsclass->vars['admin_group']) and ($this->ipsclass->vars['disable_admin_anon'] != 1) )
						{
							$active['NAMES'] .= "<a href='{$this->ipsclass->base_url}showuser={$result['member_id']}' title='$last_date'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*{$this->sep_char} \n";
							$active['ANON']++;
						}
						else
						{
							$active['ANON']++;
						}
					}
					else
					{
						$active['MEMBERS']++;
						$active['NAMES'] .= "<a href='{$this->ipsclass->base_url}showuser={$result['member_id']}' title='$last_date'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>{$this->sep_char} \n";
					}
				}
			}
		}

		$active['NAMES'] = preg_replace( "/".preg_quote($this->sep_char)."$/", "", trim($active['NAMES']) );

		$active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];
		$this->total_active = $active['TOTAL'];
		
		$this->ipsclass->lang['active_users_header'] = sprintf( $this->ipsclass->lang['active_users_header'], $this->ipsclass->vars['au_cutoff'] );

		return $this->ipsclass->compiled_templates['skin_downloads']->active_users( $active );
	}

	
	function time_remaining( $seconds )
	{
		$hours 	= '';
		$mins	= '';
		$secs	= '';
		
		if( $seconds > 3600 )
		{
			$hours = floor($seconds / 3600);
			$seconds -= $hours * 3600;
			
			$hours = $hours . $this->ipsclass->lang['dl_hours'];
		}
		
		if( $seconds > 60 )
		{
			$mins = floor($seconds / 60);
			$seconds -= $mins * 60;
			
			$mins = $mins . $this->ipsclass->lang['dl_minutes'];
		}
		
		if( $seconds > 0 )
		{
			$secs = ceil($seconds);
			
			$secs = $secs . $this->ipsclass->lang['dl_seconds'];
		}
		
		return $hours . $mins . $secs;
	}
		
}

?>