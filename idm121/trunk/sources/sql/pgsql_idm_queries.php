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
|   > PGSQL abstracted DB queries
|   > Script written by Remco Wilting
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/



class sql_idm_queries extends db_driver_pgsql
{

     var $db  = "";
     var $tbl = "";

    /*========================================================================*/
    // Set up...
    /*========================================================================*/

    function sql_idm_queries( &$obj )
    {
    	$this->db = &$obj;

    	if ( ! isset($this->db->obj['sql_tbl_prefix']) )
    	{
    		$this->db->obj['sql_tbl_prefix'] = '".SQL_PREFIX."';
    	}

    	$this->tbl = $this->db->obj['sql_tbl_prefix'];
    }

    /*========================================================================*/


    function tools_recount_dls()
    {
	    return "SELECT COUNT(*) as cnt, dfid FROM ".SQL_PREFIX."downloads_downloads GROUP BY dfid";
    }

    function category_get_total_filecount( $a )
    {
	    return "SELECT COUNT(*) as max
	    		FROM ".SQL_PREFIX."downloads_files
	    		WHERE file_cat='{$a['category']}' {$a['open']}";
	}


    function category_get_files( $a )
    {
	    return "SELECT f.file_id, f.file_cat, f.file_name, f.file_views, f.file_open, f.file_downloads, f.file_submitted, f.file_updated, f.file_submitter, f.file_rating,
	    			   f.file_ssname, f.file_ssurl, f.file_thumb, f.file_storagetype, f.file_pendcomments, m.members_display_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE f.file_cat='{$a['category']}' {$a['open']} AND f.file_placeholder=0
	    		ORDER BY {$a['extra_sort']} f.{$a['sort_key']} {$a['sort_by']}
	    		LIMIT {$a['limitb']} OFFSET {$a['limita']}";
    }


    function mod_get_pending( $a )
    {
	    return "SELECT f.file_id, f.file_cat, f.file_name, f.file_views, f.file_open, f.file_downloads, f.file_submitted, f.file_updated, f.file_submitter, f.file_rating,
	    			   f.file_ssname, f.file_thumb, f.file_storagetype, m.members_display_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE f.file_open=0 AND f.file_placeholder=0 {$a['limiter']}";
    }


    function mod_get_broken( $a )
    {
	    return "SELECT f.file_id, f.file_cat, f.file_name, f.file_views, f.file_open, f.file_downloads, f.file_submitted, f.file_updated, f.file_submitter, f.file_rating,
	    			   f.file_ssname, f.file_thumb, f.file_storagetype, m.members_display_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE f.file_broken=1 AND f.file_placeholder=0 {$a['limiter']}";
    }


    function mod_get_both( $a )
    {
	    return "SELECT f.file_id, f.file_cat, f.file_name, f.file_views, f.file_open, f.file_broken, f.file_downloads, f.file_submitted, f.file_updated, f.file_submitter, f.file_rating,
	    			   f.file_ssname, f.file_thumb, f.file_storagetype, m.members_display_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE (f.file_broken=1 OR f.file_open=0) AND f.file_placeholder=0 {$a['limiter']}";
    }


    function category_resynch( $a )
    {
	    return "SELECT COUNT(file_id) as files, SUM(file_downloads) as downloads, SUM(file_views) as views
	    		FROM ".SQL_PREFIX."downloads_files
	    		WHERE file_cat IN ({$a['cats']}) AND file_open=1";
    }


    function category_pending_files( $a )
    {
	    return "SELECT COUNT(*) as files
	    		FROM ".SQL_PREFIX."downloads_files
	    		WHERE file_cat IN ({$a['cats']}) AND file_open=0";
    }


    function category_getlatest( $a )
    {
	    return "SELECT f.file_id, f.file_cat, f.file_name, f.file_updated, f.file_submitted, f.file_submitter, m.members_display_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE f.file_cat IN ({$a['cats']}) AND f.file_open=1 AND f.file_placeholder=0
	    		ORDER BY f.file_updated DESC, f.file_submitted DESC";
    }


    function file_getss( $a )
    {
	    return "SELECT f.file_storagetype, f.file_ssname, f.file_thumb, s.storage_ss, s.storage_thumb, m.mime_mimetype
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."downloads_mime m ON (f.file_ssmime=m.mime_id)
	    		LEFT JOIN ".SQL_PREFIX."downloads_filestorage s ON (s.storage_id=f.file_id)
	    		WHERE f.file_id={$a['file_id']}";
    }


    function get_monster_file( $a )
    {
	    return "SELECT f.*, m.mime_mimetype, m.mime_img, m.mime_extension, mem.members_display_name, mim.members_display_name as approver_name
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."downloads_mime m ON (f.file_mime=m.mime_id)
	    		LEFT JOIN ".SQL_PREFIX."members mem ON (mem.id=f.file_submitter)
	    		LEFT JOIN ".SQL_PREFIX."members mim ON (mim.id=f.file_approver)
	    		WHERE f.file_id={$a['file_id']} AND f.file_placeholder=0";
	}


    function search_get_count( $a )
    {
	    $query = "SELECT COUNT(*) as max
	    		  FROM ".SQL_PREFIX."downloads_files f";

		if( $a['addcfields'] )
		{
			$query .= " LEFT JOIN ".SQL_PREFIX."downloads_ccontent cf ON (cf.file_id=f.file_id) ";
		}

	    $query .= "	WHERE ({$a['where']})";

		return $query;
	}


    function search_get_results( $a )
    {
	    $query = "SELECT f.*, m.members_display_name
	    		  FROM ".SQL_PREFIX."downloads_files f
	    		  LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)";

		if( $a['addcfields'] )
		{
			$query .= " LEFT JOIN ".SQL_PREFIX."downloads_ccontent cf ON (cf.file_id=f.file_id) ";
		}

		$query .= " WHERE ({$a['where']})
	    			ORDER BY {$a['sort_key']} {$a['sort_by']}
	    			LIMIT {$a['limita']},{$a['limitb']}";

		return $query;
    }


    function get_download_info( $a )
    {
	    return "SELECT f.*, m.mime_mimetype, m.mime_extension
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."downloads_mime m ON (f.file_mime=m.mime_id)
	    		WHERE f.file_id={$a['file_id']}";
	}


    function get_download_info_db( $a )
    {
	    return "SELECT f.*, m.mime_mimetype, m.mime_extension, s.*
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."downloads_mime m ON (f.file_mime=m.mime_id)
	    		LEFT JOIN ".SQL_PREFIX."downloads_filestorage s ON (s.storage_id=f.file_id)
	    		WHERE f.file_id={$a['file_id']}";
	}



    function acp_get_stats( $a=array() )
    {
	    if( $a['groupby'] == 'dtime' )
	    {
		    return "SELECT min(d.did) as did, {$a['type']}, min(d.{$a['groupby']}) as indicator, to_char(to_timestamp(event_unix_from),'MM')||' '||to_char(to_timestamp(event_unix_from),'YYYY') as dtime, f.file_name
	    			FROM ".SQL_PREFIX."downloads_downloads d
	    			LEFT JOIN ".SQL_PREFIX."downloads_files f ON(f.file_id=d.dfid)
	    			GROUP BY to_char(to_timestamp(event_unix_from),'MM')||' '||to_char(to_timestamp(event_unix_from),'YYYY'), f.file_name
	    			ORDER BY num DESC
	    			LIMIT {$a['limit']}";
    	}
    	else
    	{
    		return "SELECT d.*, sq.num, sq.indicator, f.file_name
    		        FROM ".SQL_PREFIX."downloads_downloads d
    		        INNER JOIN ( SELECT {$a['groupby']} as indicator, {$a['type']}, min(did) as mindid
    		                     FROM ".SQL_PREFIX."downloads_downloads
    		                     GROUP BY {$a['groupby']} ) as sq ON d.did=sq.mindid
    				LEFT JOIN ".SQL_PREFIX."downloads_files f ON(f.file_id=d.dfid)
	    			ORDER BY num DESC
	    			LIMIT {$a['limit']}";
    	}
    }


	function files_get_thumbs( $a=array() )
	{
	    return "SELECT f.*, s.*
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."downloads_filestorage s ON (s.storage_id=f.file_id)
	    		WHERE f.file_ssname!=''
	    		LIMIT {$a['limitb']} OFFSET {$a['limita']}";
    }


    function get_top_ten_submitters( $a=array() )
    {
	    return "SELECT f.*, ps.submissions as submissions, ps.last_id as last_id, m.id, m.members_display_name, m.last_activity
	    		FROM ".SQL_PREFIX."downloads_files f,
	    		".SQL_PREFIX."members m,
	    		(SELECT COUNT(file_id) as submissions, MAX(file_id) as last_id FROM ".SQL_PREFIX."downloads_files
	    		GROUP BY file_submitter) ps
	    		WHERE f.file_id=ps.last_id AND m.id=f.file_submitter
	    		ORDER BY submissions DESC
	    		LIMIT 10";
    }

    function get_top_ten_submitters_40_first( $a=array() )
    {
	    return "SELECT COUNT(file_id) as submissions, MAX(file_id) as last_id, file_submitter
	    		FROM ".SQL_PREFIX."downloads_files
	    		GROUP BY file_submitter
	    		LIMIT 10";
    }

    function get_top_ten_submitters_40_second( $a=array() )
    {
	    return "SELECT f.*, m.id, m.members_display_name, m.last_activity
	    		FROM ".SQL_PREFIX."downloads_files f
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=f.file_submitter)
	    		WHERE f.file_id IN({$a['ids_to_pull']})";
    }

    function get_top_ten_downloaders( $a=array() )
    {
	    return "SELECT f.*, d.dfid, d.dtime, d.did, ps.downloads as downloads, ps.the_id as the_id, m.id, m.members_display_name, m.last_activity
	    		FROM ".SQL_PREFIX."downloads_files f,
	    		".SQL_PREFIX."members m,
	    		(SELECT COUNT(did) as downloads, MAX(did) as the_id, dmid FROM ".SQL_PREFIX."downloads_downloads
	    		GROUP BY dmid) ps,
	    		".SQL_PREFIX."downloads_downloads d
	    		WHERE d.did=ps.the_id AND f.file_id=d.dfid AND m.id=ps.dmid
	    		ORDER BY downloads DESC
	    		LIMIT 10";
    }

    function get_top_ten_downloaders_40_first( $a=array() )
    {
	    return "SELECT COUNT(did) as downloads, MAX(did) as the_id, dmid
	    		FROM ".SQL_PREFIX."downloads_downloads
	    		GROUP BY dmid
	    		LIMIT 10";
    }

    function get_top_ten_downloaders_40_second( $a=array() )
    {
	    return "SELECT f.*, d.dfid, d.dtime, d.did, d.dmid, m.id, m.members_display_name, m.last_activity
	    		FROM ".SQL_PREFIX."downloads_downloads d
	    		LEFT JOIN ".SQL_PREFIX."downloads_files f ON (f.file_id=d.dfid)
	    		LEFT JOIN ".SQL_PREFIX."members m ON (m.id=d.dmid)
	    		WHERE d.did IN({$a['ids_to_pull']})";
    }

	function get_mod_groups( $a )
	{
		return "SELECT g.g_id, m.members_display_name, m.id, m.email
				FROM ".SQL_PREFIX."groups as g
		        LEFT JOIN ".SQL_PREFIX."members m ON (m.mgroup = g.g_id OR m.mgroup_others LIKE '%,'||g.g_id||',%')
		        WHERE g.g_is_supmod=1
		        GROUP BY g.g_id
		        ORDER BY g.g_title";
	}

} // end class


?>