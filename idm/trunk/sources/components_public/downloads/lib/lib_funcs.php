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
|   > Library: Various Functions
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 15, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

class lib_funcs
{
    /*-------------------------------------------------------------------------*/
    // Produce Internal error message
    /*-------------------------------------------------------------------------*/
    
    function produce_error( $lang_bit="", $text_already=0 )
    {
	    $message = "";
	    
	    if( $text_already == 1 )
	    {
		    if( !$lang_bit )
		    {
			    $message = $this->ipsclass->lang['generic_error'];
		    }
		    else
		    {
			    $message = $lang_bit;
		    }
	    }
	    else if( !$lang_bit )
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
	    
	    return $this->ipsclass->compiled_templates['skin_downloads']->error_box( $message );
    }
    
    
    /*-------------------------------------------------------------------------*/
    // Generate copyright
    /*-------------------------------------------------------------------------*/
    
	function return_copyright()
	{
		$version = ( isset( $this->ipsclass->vars['ipb_display_version'] ) && $this->ipsclass->vars['ipb_display_version'] != 0 ) ? DL_VERSION : '';
		
        if ($this->ipsclass->vars['ipb_copy_number'])
        {
        	$copyright = "";
        }
        else
        {
        	$copyright = "<!-- IDM Copyright Information -->
        				  <div align='center' class='copyright'>
        				  	Powered By IP.Downloads 
        				  " . $version . " &copy; ".date("Y")."&nbsp;IPS, Inc.";

        	if ( $this->ipsclass->vars['ipb_reg_show'] and $this->ipsclass->vars['ipb_reg_name'] )
        	{
        		$copyright .= "<br />Licensed to: ". $this->ipsclass->vars['ipb_reg_name'];
        	}

			$copyright .= "</div><!-- / Copyright -->";
        }

		return $copyright;
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Generate RSS link
    /*-------------------------------------------------------------------------*/
    
	function _get_synd_link()
	{
		$content = $this->ipsclass->compiled_templates['skin_global']->global_footer_synd_link( array( 'title' => $this->ipsclass->lang['idm_rss_title'],
																										'url'  => $this->ipsclass->vars['board_url']."/index.php?autocom=downloads&amp;rss=1" ) ) . "\n";
		$content = preg_replace( "#,(\s+)?$#s", "", $content );
		
		return $this->ipsclass->compiled_templates['skin_global']->global_footer_synd_wrapper( $content );
	}
	
	
    /*-------------------------------------------------------------------------*/
    // Rebuild the comment queue count for a file
    /*-------------------------------------------------------------------------*/
    
    function rebuild_pend_comment_cnt( $file_id=0 )
    {
	    if( !$file_id )
	    {
		    return;
	    }
	    
	    $file_id = intval($file_id);
	    
	    $comments = $this->ipsclass->DB->build_and_exec_query( array( 'select'	=> 'COUNT(*) as coms',
	    															  'from'	=> 'downloads_comments',
	    															  'where'	=> 'comment_fid=' . $file_id . ' AND comment_open=0'
	    													)		);
	    
	    $comments['coms'] = $comments['coms'] <= 0 ? 0 : $comments['coms'];
	    
	    $this->ipsclass->DB->do_update( 'downloads_files', array( 'file_pendcomments' => $comments['coms'] ), 'file_id=' . $file_id );
	    
	    return true;
    }
    
    
    /*-------------------------------------------------------------------------*/
    // Check permissions
    /*-------------------------------------------------------------------------*/
    
    function checkPerms( $file=array(), $modperm='modcanapp', $userperm='' )
    {
	    if( !is_array( $file ) OR !count( $file ) )
	    {
		    return false;
	    }
	    
		//-----------------------------------------
		// Got permission?
		//-----------------------------------------
		
		$moderator 	= $this->ipsclass->member['g_is_supmod'] ? true : false;
		
		$groups		= array( 'g' . $this->ipsclass->member['mgroup'] );
		
		if( $this->ipsclass->member['mgroup_others'] )
		{
			foreach( explode( ',', $this->ipsclass->member['mgroup_others'] ) as $omg )
			{
				$groups[] = 'g' . $omg;
			}
		}

		if( !$moderator )		
		{
			if( is_array( $this->catlib->cat_mods[ $file['file_cat'] ] ) )
			{
				if( count($this->catlib->cat_mods[ $file['file_cat'] ]) )
				{
					foreach( $this->catlib->cat_mods[ $file['file_cat'] ] as $k => $v )
					{
						if( $k == "m".$this->ipsclass->member['id'] )
						{
							if( $v[ $modperm ] )
							{
								$moderator = true;
							}
						}
						else if( in_array( $k, $groups ) )
						{
							if( $v[ $modperm ] )
							{
								$moderator = true;
							}
						}
					}
				}
			}
		}
		
		if( $userperm )
		{
			if( $file['id'] == $this->ipsclass->member['id'] && $this->ipsclass->vars[ $userperm ] )
			{
				$moderator = true;
			}
		}
		
		return $moderator;
	}
    
}
?>