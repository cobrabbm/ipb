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
|   > Search Script - Includes all by author, last 10, since last visit
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
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

class idm_search
{
	var $ipsclass;
	var $catlib;

	var $output			= "";
	var $nav 			= "";
	var $page_title 	= "";

	var $sep_char 		= '<{ACTIVE_LIST_SEP}>';
	var $total_active	= 0;
	var $canadd			= 0;
	var $canmod			= 0;
	var $didsearch		= 0;
	
	var $limit;
	
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
	    // Global navigation bar/title
	    $this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=downloads'>".$this->ipsclass->lang['idm_navbar'].'</a>';
		$this->page_title = $this->ipsclass->vars['board_name']." -> ".$this->ipsclass->lang['idm_pagetitle'];
		
		// Setup access permissions
		$this->catlib->get_member_cat_perms();
		
		// Correct our files-per-page dropdown
		if( $this->ipsclass->vars['idm_ddfilesperpage'] )
		{
			$perpage = explode( ",", $this->ipsclass->vars['idm_ddfilesperpage'] );
			if( count( $perpage ) )
			{
				$this->sort_num = $perpage;
				unset($perpage);
			}
		}
		
		// Can we see any categories?
		if( count($this->catlib->member_access['view']) == 0 )
		{
			$this->produce_error( 'no_downloads_permissions' );
		}
		else
		{
			$canadd = 0;
			$canmod = 0;
			
			if( count( $this->catlib->member_access['view'] ) > 0 )
			{
				$this->canadd = 1;
			}
			
			if( $this->ipsclass->member['g_is_supmod'] )
			{
				$this->canmod = 1;
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
						
			switch( $this->ipsclass->input['code'] )
			{
				case 'search_form':
					$this->display_form( );
					break;
				
				case 'do_search':
					$this->do_search( );
					break;
					
				case 'all_author':
					if( $this->ipsclass->input['id'] )
					{
						$this->ipsclass->input['mid'] = $this->ipsclass->input['id'];
					}
					$this->ipsclass->input['search_andor']  = 'AND';
					$this->ipsclass->input['search_author'] = 1;
					$this->do_search( );
					break;
					
				case 'last_ten':
					$this->ipsclass->input['num'] = 10;
					$this->limit = 10;
					$this->do_search();
					break;
					
				case 'last_visit':
					$this->ipsclass->input['search_andor']  = 'AND';
					$this->ipsclass->input['search_date_1'] = date( 'n/j/Y g:i A', $this->ipsclass->member['last_visit'] );
					$this->ipsclass->input['search_date_2'] = date( 'n/j/Y g:i A', time() - $this->ipsclass->member['time_offset'] );
					$this->do_search();
					break;
				
				default:
					$this->display_form( );
					break;
			}
			
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod );
		}
		
		$this->get_page_end();
		
		// Print the output
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 1, NAV => $this->nav ) );
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Search Form
    /*-------------------------------------------------------------------------*/
    	
	function display_form( )
	{
		if( !$this->didsearch )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod );		
		}
		
		$get_cats = $this->catlib->cat_jump_list(1, 'view');
		
		$thecats = ( is_array($this->ipsclass->input['search_cat']) AND !in_array( "0", $this->ipsclass->input['search_cat'] ) ) ? $this->ipsclass->input['search_cat'] : array();
		
		if( ! is_array($thecats) )
		{
			$thecats = array();
		}
		
		if( count( $thecats ) == 0 )
		{
			$cat_options = "<option value='0' selected='selected'>{$this->ipsclass->lang['search_allcats']}</option>";
		}
		else
		{
			$cat_options = "<option value='0'>{$this->ipsclass->lang['search_allcats']}</option>";
		}
		
		foreach( $get_cats as $k => $v )
		{
			$selected = "";
			if( in_array($v[0], $thecats) )
			{
				$selected = " selected='selected'";
			}
			$disabled = "";
			if( $v[2] != '' )
			{
				$disabled = $v[2];
			}
			
			$cat_options .= "<option value='{$v[0]}'{$selected}{$disabled}>{$v[1]}</option>";
		}
		
		$sort_by  = ( $this->ipsclass->input['sort_by'] && in_array( $this->ipsclass->input['sort_by'], $this->sort_by ) ) ? $this->ipsclass->input['sort_by'] : 'DESC';
		$sort_key = ( $this->ipsclass->input['sort_key'] && array_key_exists( $this->ipsclass->input['sort_key'], $this->sort_key ) ) ? $this->ipsclass->input['sort_key'] : 'file_updated';
		
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
		
		$andor['and'] = $this->ipsclass->input['search_andor'] == 'AND' ? " selected='selected'" : "";
		$andor['or'] = $this->ipsclass->input['search_andor'] == 'OR' ? " selected='selected'" : "";
		
    	$cfield_output = "";
    	
		if( is_array($this->ipsclass->cache['idm_cfields']) AND count($this->ipsclass->cache['idm_cfields']) )
		{
			$this->ipsclass->load_template( 'skin_downloads_submit' );
			
    		require_once( DL_PATH.'lib/lib_cfields.php' );
    		$fields = new lib_cfields();
    		$fields->ipsclass =& $this->ipsclass;
    	
    		$fields->cache_data = $this->ipsclass->cache['idm_cfields'];
    	
    		$fields->init_data();
    		$fields->parse_to_edit();
    		
    		foreach( $fields->out_fields as $id => $data )
    		{
	    		$data = $this->ipsclass->input['field_' . $id];

    			if ( !$fields->cache_data[ $id ]['cf_search'] )
				{
					continue;
				}
    		
    			if ( $fields->cache_data[ $id ]['cf_type'] == 'drop' )
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_dropdown( 'field_'.$id, $data );
				}
				else if ( $fields->cache_data[ $id ]['cf_type'] == 'area' )
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_textarea( 'field_'.$id, $data );
				}
				else
				{
					$form_element = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_textinput( 'field_'.$id, $data );
				}
			
				$form_fields[] = array( 'name' => $fields->field_names[ $id ], 'desc' => $fields->field_desc[ $id ], 'form_element' => $form_element, 'req' => $fields->cache_data[ $id ]['cf_not_null'] );
				//$cfield_output .= $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_display( $fields->field_names[ $id ], $fields->field_desc[ $id ], $form_element, $ftype );
    		}

   			$cfield_display = $this->ipsclass->compiled_templates['skin_downloads_submit']->cfield_wrapper( $form_fields );
		}

		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->search_form( $cat_options, $sortnum_options, $sortkey_options, $sortby_options, $andor );
		
		$this->output = str_replace( "<!--CUSTOM_FIELDS-->", $cfield_display, $this->output );
		
		$this->nav[] = $this->ipsclass->lang['searchpage_nav'];
		$this->page_title .= " -> ".$this->ipsclass->lang['searchpage_nav'];
		
		return;
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Do Search
    // - Input fields: search_cat (category id), search_author (author id),
    //		search_date_1 (member submitted start date of submissions),
    //		search_date_2 (member submitted end date of submissions),
    //		search_keywords (text keywords), sort_num (limit number of results),
    //		sort_by (asc, desc), search_andor (all must match or any can match),
    //		sort_key (order by clause)
    /*-------------------------------------------------------------------------*/
    	
	function do_search( )
	{
		$query_bits = array();
		$url_bits	= array();
		
		if( $this->ipsclass->input['search_cat'] != "" AND !is_array($this->ipsclass->input['search_cat']) )
		{
			$this->ipsclass->input['search_cat'] = explode( ",", $this->ipsclass->input['search_cat'] );
		}
		
		//======================================================
		// We're gonna need 2 arrays
		//	- Cats we want to see and can approve
		//	- Cats we want to see and cannot approve
		//======================================================
		
		$perm_cats		= array();
		$perm_a_cats	= array();
		
		$true_cats		= array();
		$true_a_cats	= array();		
		
		//======================================================
		// Loop through all cats
		//======================================================

		foreach( $this->catlib->cat_lookup as $k => $v )
		{
			//======================================================
			// Can we access?
			//======================================================

			if( in_array( intval($k), $this->catlib->member_access['view'] ) )
			{
				//======================================================
				// Can we approve?
				//======================================================

				if( $this->ipsclass->member['g_is_supmod'] )
				{
					$cantog   = 1;
				}
				else
				{
					if( is_array( $this->catlib->cat_mods[ $k ] ) )
					{
						if( count($this->catlib->cat_mods[ $k ]) )
						{
							foreach( $this->catlib->cat_mods[ $k ] as $k1 => $v1 )
							{
								if( $k1 == "m".$this->ipsclass->member['id'] )
								{
									if( $v1['modcanapp'] )
									{
										$cantog = 1;
									}
								}
								else if( $k1 == "g".$this->ipsclass->member['mgroup'] )
								{
									if( $v1['modcanapp'] )
									{
										$cantog = 1;
									}
								}
							}
						}
					}
				}
				
				if( $cantog == 1 )
				{
					$perm_a_cats[] = $k;
				}
				else
				{
					$perm_cats[] = $k;
				}	
			}		
		}
		
		//======================================================
		// Are we searching a cat?
		// If so, remove any cats from our approve list...
		//======================================================		

		if( is_array($this->ipsclass->input['search_cat']) AND count($this->ipsclass->input['search_cat']) > 0 )
		{
			foreach( $this->ipsclass->input['search_cat'] as $k => $v )
			{
				//======================================================
				// Searching "All Categories"?
				//======================================================
				
				if( $v != '0' )
				{
					if( in_array( $v, $perm_a_cats ) )
					{
						$true_a_cats[] = $v;
					}
					else if( in_array( $v, $perm_cats ) )
					{
						$true_cats[] = $v;
					}
				}
			}
			
			if( !count($true_a_cats) AND !count($true_cats) )
			{
				$true_a_cats 	= $perm_a_cats;
				$true_cats		= $perm_cats;
			}
			
			$url_bits[]		= "search_cat=".implode( ",", array_unique($true_cats) );
		}
		else
		{
			$true_a_cats 	= $perm_a_cats;
			$true_cats		= $perm_cats;
		}
		
		if( count( $true_a_cats ) )
		{
			if( count($true_cats) )
			{
				$query_bits[] 	= "f.file_cat IN (".implode( ",", array_unique($true_a_cats) ).") OR ( f.file_cat IN (".implode( ",", array_unique($true_cats) ).") AND ( f.file_open=1 OR f.file_submitter={$this->ipsclass->member['id']} ) )";
			}
			else
			{
				$query_bits[] 	= "f.file_cat IN (".implode( ",", array_unique($true_a_cats) ).")";
			}
		}
		else
		{
			$query_bits[] 	= "f.file_cat IN (".implode( ",", array_unique($true_cats) ).") AND ( f.file_open=1 OR f.file_submitter={$this->ipsclass->member['id']} )";
		}
		
		if( $this->ipsclass->input['search_author'] )
		{
			if( $this->ipsclass->input['mid'] )
			{
				// Get name
				$name = $this->ipsclass->DB->build_and_exec_query( array( 'select' => 'members_display_name', 'from' => 'members', 'where' => 'id=' . intval($this->ipsclass->input['mid']) ) );
				$mid = "f.file_submitter=".intval($this->ipsclass->input['mid']);
				$url_bits[] = "search_author=" . $name['members_display_name'];
				
				$this->ipsclass->input['search_author'] = $name['members_display_name'];
			}
			else
			{
				$tmid = array();
				
				// Get the mid
				$this->ipsclass->DB->build_query( array( 'select' => 'id', 'from' => 'members', 'where' => "members_display_name LIKE '%{$this->ipsclass->input['search_author']}%' OR  name LIKE '%{$this->ipsclass->input['search_author']}%'" ) );
				$this->ipsclass->DB->exec_query();
				
				while( $row = $this->ipsclass->DB->fetch_row() )
				{
					$tmid[] = "f.file_submitter=".$row['id'];
				}
				
				if( count($tmid) > 0 )
				{
					$mid = implode( " OR ", $tmid );
				}
				
				$url_bits[] = "search_author=".trim(urlencode($this->ipsclass->input['search_author']));
			}
			if( $mid )
			{
				$query_bits[] = $mid;
			}
		}
		
		if( $this->ipsclass->input['search_date_1'] )
		{
			$url_bits[] = 'search_date_1='.trim($this->ipsclass->input['search_date_1']);
			
			$start_time = @strtotime($this->ipsclass->input['search_date_1']);
			if( !$start_time )
			{
				$this->produce_error( 'search_bad_time' );
				$this->display_form();
				return;
			}
			
			if( $this->ipsclass->input['search_date_2'] )
			{
				$url_bits[] = 'search_date_2='.trim($this->ipsclass->input['search_date_2']);
				
				$end_time = @strtotime($this->ipsclass->input['search_date_2']);
			}
			else
			{
				$end_time = time();
			}
			
			if( !$end_time )
			{
				$this->produce_error( 'search_bad_time' );
				$this->display_form();
				return;
			}
			
			$query_bits[] = "(f.file_submitted BETWEEN {$start_time} AND {$end_time} OR f.file_updated BETWEEN {$start_time} AND {$end_time})";
		}
		else if( $this->ipsclass->input['search_date_2'] && !$this->ipsclass->input['search_date_1'] )
		{
			$url_bits[] = 'search_date_2='.trim($this->ipsclass->input['search_date_2']);
			
			$start_time = 0;
			$end_time = @strtotime($this->ipsclass->input['search_date_2']);
			
			if( !$end_time )
			{
				$this->produce_error( 'search_bad_time' );
				$this->display_form();
				return;
			}
			
			$query_bits[] = "(f.file_submitted BETWEEN {$start_time} AND {$end_time} OR f.file_updated BETWEEN {$start_time} AND {$end_time})";
		}			
		
		$this->ipsclass->input['search_keywords'] = trim($this->ipsclass->input['search_keywords']);
		
		if( $this->ipsclass->input['search_keywords'] != '' )
		{
			$url_bits[] = 'search_keywords='.urlencode($this->ipsclass->input['search_keywords']);
			
			$search_in = ( in_array( trim( strtolower($this->ipsclass->input['search_in']) ), array( 'both', 'titles', 'desc' ) ) ) ? trim( strtolower($this->ipsclass->input['search_in']) ) : 'both';
			
			$url_bits[] = 'search_in=' . $search_in;
			
			if( $this->ipsclass->vars['idm_searchmethod'] == 'soundex' )
			{
				$keywords = explode( " ", $this->ipsclass->input['search_keywords'] );
				
				$soundex = array();
				foreach( $keywords as $k => $v )
				{
					$soundex[] = soundex($v);
				}
				
				if( count($soundex) > 0 )
				{
					$to_query = array();
					foreach( $soundex as $k => $v )
					{
						$to_query[] = "f.file_meta LIKE '%,{$v},%'";
					}
					
					if( count($to_query) > 0 )
					{
						$query_bits[] = "(".implode( " OR ", $to_query ).")";
					}
				}
			}
			else if( $this->ipsclass->vars['idm_searchmethod'] == 'manual' )
			{
				if( $search_in == 'both' )
				{
					$query_bits[] = "(f.file_name LIKE '%{$this->ipsclass->input['search_keywords']}%' OR f.file_desc LIKE '%{$this->ipsclass->input['search_keywords']}%')";
				}
				else if( $search_in == 'titles' )
				{
					$query_bits[] = "(f.file_name LIKE '%{$this->ipsclass->input['search_keywords']}%')";
				}
				else if( $search_in == 'desc' )
				{
					$query_bits[] = "(f.file_desc LIKE '%{$this->ipsclass->input['search_keywords']}%')";
				}
			}
			else
			{
				if( $search_in == 'both' )
				{
					$query_bits[] = "MATCH (f.file_name,f.file_desc) AGAINST ('{$this->ipsclass->input['search_keywords']}' IN BOOLEAN MODE)";
				}
				else if( $search_in == 'titles' )
				{
					$query_bits[] = "MATCH (f.file_name) AGAINST ('{$this->ipsclass->input['search_keywords']}' IN BOOLEAN MODE)";
				}
				else if( $search_in == 'desc' )
				{
					$query_bits[] = "MATCH (f.file_desc) AGAINST ('{$this->ipsclass->input['search_keywords']}' IN BOOLEAN MODE)";
				}
			}
		}
		
		// Custom Fields?
		
		$add_cfields 	= 0;
		$cfields		= array();
		
		foreach( $this->ipsclass->input as $k => $v )
		{
			if( preg_match( "/^field_(\d+)$/", $k, $matches ) AND $v )
			{
				$cfields[ $matches[1] ] = $v;
			}
		}
		
		if( count($cfields) )
		{
			$add_cfields	= 1;
			
			foreach( $cfields as $cfid => $check )
			{
				$query_bits[]	= "cf.field_{$cfid} LIKE '%{$check}%'";
				$url_bits[]		= "field_{$cfid}=" . urlencode($check);
			}
		}
		
		$st = $this->ipsclass->input['st'] ? intval($this->ipsclass->input['st']) : 0;
		
		$num = $this->ipsclass->input['num'] ? intval($this->ipsclass->input['num']) : $this->ipsclass->vars['idm_filesperpage'];
		if( ! in_array( $num, $this->sort_num ) )
		{
			$num = $this->ipsclass->vars['idm_filesperpage'];
		}
		
		$sort_by  = ( $this->ipsclass->input['sort_by'] && in_array( $this->ipsclass->input['sort_by'], $this->sort_by ) ) ? $this->ipsclass->input['sort_by'] : 'DESC';
		
		$this->ipsclass->input['sort_key'] = str_replace( "f.", "", $this->ipsclass->input['sort_key'] );
		
		$sort_key = ( $this->ipsclass->input['sort_key'] && array_key_exists( $this->ipsclass->input['sort_key'], $this->sort_key ) ) ? "f.".$this->ipsclass->input['sort_key'] : "f.file_updated";
			
		$andor = ($this->ipsclass->input['search_andor'] AND in_array( strtoupper($this->ipsclass->input['search_andor']), array( 'AND', 'OR' ) )) ? strtoupper($this->ipsclass->input['search_andor']) : 'OR';

		$query_string = "";
		if( count($query_bits) > 0 )
		{
			$query_string = implode( " {$andor} ", $query_bits );
		}
		else
		{
			if( $this->ipsclass->input['code'] == 'last_ten' )
			{
				$query_string = "file_updated > 0";
			}
			else
			{
				$this->produce_error( 'search_no_terms' );
				$this->display_form();
				return;
			}
		}
		
		if( $this->limit )
		{
			$count['max'] = $this->limit;
			$num = $this->limit;
		}
		else
		{
			$this->ipsclass->DB->cache_add_query( 'search_get_count', array( 'where' => $query_string, 'addcfields' => $add_cfields ), 'sql_idm_queries'	);
			$this->ipsclass->DB->cache_exec_query();
	
			$count = $this->ipsclass->DB->fetch_row();
		}
					
		if( $count['max'] == 0 )
		{
			$this->produce_error( 'no_search_results' );
			$this->display_form();
			return;
		}		
				
		$page_links	= $this->ipsclass->build_pagelinks( array(  'TOTAL_POSS'  => $count['max'],
													   			'PER_PAGE'    => $num,
													   			'CUR_ST_VAL'  => $st,
													   			'L_SINGLE'    => '&nbsp;',
													   			'BASE_URL'    => $this->ipsclass->base_url."autocom=downloads&amp;req=search&amp;code=do_search&amp;sort_by={$sort_by}&amp;sort_key={$sort_key}&amp;num={$num}&amp;search_andor={$andor}&amp;".
													   				implode( "&amp;", $url_bits ),
													  )	  	 );
													  
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->global_bar( $this->canadd, $this->canmod, $page_links );
		
		$this->didsearch = 1;													  

		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_top( $this->ipsclass->lang['search_results'] );
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_listing_thbar();

		$this->ipsclass->DB->cache_add_query( 'search_get_results', array( 	'sort_by'    	=> $sort_by,
																			'sort_key' 		=> $sort_key,
																			'where'			=> $query_string,
																			'limita'		=> $st,
																			'limitb'		=> $num,
																			'addcfields'	=> $add_cfields,
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
				$row['file_updateddis'] = "";
			}
			
			$row['members_display_name'] = $row['members_display_name'] ? $row['members_display_name'] : $this->ipsclass->lang['global_guestname'];
			
			$row['submitter'] = $this->ipsclass->make_profile_link( $row['members_display_name'], $row['file_submitter'] );
			$row['filename']  = $this->ipsclass->compiled_templates['skin_downloads']->file_link( $row['file_name'], $row['file_id'] );
			
			$row['categoryinfo'] = array( 'cid' => $row['file_cat'], 'cname' => $this->catlib->cat_lookup[ $row['file_cat'] ]['cname'] );
			
			$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->file_row_withoutss( $row );
		}
	
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->search_bottom( $page_links );
		$this->display_form( );
		
		$this->nav[] = $this->ipsclass->lang['search_results'];
			
		$this->page_title .= " -> ".$this->ipsclass->lang['search_results'];
		return;
	}
	

    /*-------------------------------------------------------------------------*/
    // Produce Internal error message
    /*-------------------------------------------------------------------------*/
    
    function produce_error( $lang_bit="" )
    {
	    $message = "";
	    
	    if( !$lang_bit )
	    {
		    $message = $this->ipsclass->lang['generic_error'];
	    }
	    else if( ! array_key_exists( $lang_bit, $this->ipsclass->lang ) )
	    {
		    $message = $this->ipsclass->lang['generic_error'];
	    }
	    else
	    {
		    $message = $this->ipsclass->lang[ $lang_bit ];
	    }
	    
	    $this->output .= $this->ipsclass->compiled_templates['skin_downloads']->error_box( $message, 1 );
	    return;
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
		
		$latest_file = $this->ipsclass->cache['idm_stats']['latest_fid'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;showfile={$this->ipsclass->cache['idm_stats']['latest_fid']}'>{$this->ipsclass->cache['idm_stats']['latest_fname']}</a>" : $this->ipsclass->lang['stats_noneyet'];
		$latest_author = $this->ipsclass->cache['idm_stats']['latest_mid'] ? "<a href='{$this->ipsclass->base_url}showuser={$this->ipsclass->cache['idm_stats']['latest_mid']}'>{$this->ipsclass->cache['idm_stats']['latest_mname']}</a>" : $this->ipsclass->lang['stats_noneyet'];
		$latest_date = $this->ipsclass->get_date( $this->ipsclass->cache['idm_stats']['latest_date'], 'LONG' );
		
		$stats_lang .= sprintf( $this->ipsclass->lang['stats_latestfile'], $latest_file , $latest_author , $latest_date )."<br />";
		
		$show['stats'] = $stats_lang;
		
		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->page_end( $show );

		if ( $this->ipsclass->vars['idm_displayactive'] )
		{
			$this->output = str_replace( "<!--IDM.ACTIVEUSERS-->", $active_users_html, $this->output );
		}
		
		if( $this->ipsclass->vars['idm_enablerandom'] )
		{
			$count = $this->ipsclass->vars['idm_randomfiles'] > 0 ? $this->ipsclass->vars['idm_randomfiles'] : 5;
			$randcnt = $count*2; // Get double to be safe?
			$rand_ids = array();
			
			$widths = round(100/$count);
			
			$this->ipsclass->DB->build_query( array( 'select' => 'file_id,file_cat,file_name,file_ssname',
														'from'	=> 'downloads_files',
														'where' => "file_open=1",
														'order' => 'RAND() DESC',
														'limit' => array(0,$count)
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
					
					$row['screenshot_spot'] = $row['file_ssname'] ? "<a href='{$this->ipsclass->base_url}autocom=downloads&amp;showfile={$row['file_id']}'><img src='{$this->ipsclass->base_url}autocom=downloads&amp;req=display&amp;code=ss&amp;id={$row['file_id']}' border='0' alt='{$this->ipsclass->lang['filescreenshot']}' /></a>"
																	: "<i>{$this->ipsclass->lang['catdis_ssnone']}</i>";
					
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

}

?>