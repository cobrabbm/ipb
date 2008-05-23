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
|   > Downloads Group Restriction Settings
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 10, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_downloads_groups {

	var $base_url;
	var $func;
	
	var $perm_main	= 'components';
	var $perm_child = 'downloads';	

	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( 'section=components&act=downloads&req=groups', 'IP.Downloads Group Restrictions' );
		$this->ipsclass->form_code = $this->ipsclass->form_code."&req=groups";
				

		switch($this->ipsclass->input['code'])
		{
			case 'edit':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':groups' );
				$this->main_form();
				break;
				
			case 'save':
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':groups' );
				$this->main_save();
				break;
				
			default:
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':groups' );
				$this->main_screen();
				break;
		}
		
	}
	
	//-----------------------------------------
	//
	// Rebuild cache
	//
	//-----------------------------------------
	
	function rebuild_cache()
	{
		$this->ipsclass->cache['group_cache'] = array();
				
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'groups' ) );
						 
		$this->ipsclass->DB->simple_exec();
		
		while ( $r = $this->ipsclass->DB->fetch_row() )
		{
			$this->ipsclass->cache['group_cache'][ $r['g_id'] ] = $r;
		}
		
		$this->ipsclass->update_cache( array( 'name' => 'group_cache', 'array' => 1, 'deletefirst' => 1 ) );	
	}
	

	
	//-----------------------------------------
	//
	// Save changes to DB
	//
	//-----------------------------------------
	
	function main_save()
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		
		if ( ! $this->ipsclass->input['id'] )
		{
			$this->ipsclass->admin->error("No group id was passed to edit.");
		}
		
		//-----------------------------------------
		// Get group from db
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'g_id, g_title', 'from' => 'groups', 'where' => "g_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		$group = $this->ipsclass->DB->fetch_row();

		if( !$group['g_id'] )
		{
			$this->ipsclass->admin->error("Group could not be found.");
		}
		
		$db_string = array( 'enabled' 			=> intval($this->ipsclass->input['enabled']),
						    'daily_bw' 			=> intval($this->ipsclass->input['daily_bw']),
						    'weekly_bw' 		=> intval($this->ipsclass->input['weekly_bw']),
						    'monthly_bw' 		=> intval($this->ipsclass->input['monthly_bw']),
						    'daily_dl' 			=> intval($this->ipsclass->input['daily_dl']),
						    'weekly_dl' 		=> intval($this->ipsclass->input['weekly_dl']),
						    'monthly_dl' 		=> intval($this->ipsclass->input['monthly_dl']),
						    'min_posts' 		=> intval($this->ipsclass->input['min_posts']),
						    'posts_per_dl'		=> intval($this->ipsclass->input['posts_per_dl']),
						    'limit_sim'			=> intval($this->ipsclass->input['limit_sim']),
						  );
		
		$restrictions = serialize( $db_string );
		
		$this->ipsclass->DB->do_update( 'groups', array( 'idm_restrictions' => $restrictions ), 'g_id='.$this->ipsclass->input['id'] );
			
		$this->rebuild_cache();
			
		$this->ipsclass->main_msg = "Group Restrictions Edited";
		$this->main_screen();
	}
	
	
	//-----------------------------------------
	//
	// Add / edit group
	//
	//-----------------------------------------
	
	function main_form()
	{
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		
		if ( ! $this->ipsclass->input['id'] )
		{
			$this->ipsclass->admin->error("No group id was passed to edit.");
		}
		
		//-----------------------------------------
		// Get group from db
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_construct( array( 'select' => 'g_id, g_title, idm_restrictions', 'from' => 'groups', 'where' => "g_id=".intval($this->ipsclass->input['id']) ) );
		$this->ipsclass->DB->simple_exec();
		
		$group = $this->ipsclass->DB->fetch_row();
		
		$restrictions = is_array( @unserialize( stripslashes( $group['idm_restrictions'] ) ) ) ? unserialize( stripslashes( $group['idm_restrictions'] ) ) : array();
		$group = array_merge( $group, $restrictions );

		if( !$group['g_id'] )
		{
			$this->ipsclass->admin->error("Group could not be found.");
		}
		
		$this->ipsclass->admin->page_title = "Editing Restrictions for group ".$group['g_title'];
		
		//-----------------------------------------
		// Wise words
		//-----------------------------------------
		
		$this->ipsclass->admin->page_detail = "Please double check the information before submitting the form.  All information can be 'mixed and matched' - that is, you can configure the settings you do want to use, and set those that you don't want to use to 0.";
		
		//-----------------------------------------
		// Start form
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'   , 'save'  ),
												                 			 2 => array( 'act'    , 'downloads'     ),
												                 			 3 => array( 'id'     , $this->ipsclass->input['id']   ),
												                 			 4 => array( 'section', $this->ipsclass->section_code ),
												                 			 5 => array( 'req'	  , 'groups'	),
									                    )     );
		
		//-----------------------------------------
		// Tbl (no ae?)
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "60%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "40%" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Group Restrictions: {$group['g_title']}" );
		
		/*	Fields:
				intval($rs['daily_bw']),
				intval($rs['daily_dl']),
				intval($rs['weekly_bw']),
				intval($rs['weekly_dl']),
				intval($rs['monthly_bw']),
				intval($rs['monthly_dl']),
				intval($rs['min_posts']),
				intval($rs['posts_per_dl']),
		*/
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Enable Restrictions</b><div class='graytext'>You must set this to yes to enable any restrictions on this group.</div>" ,
												                 $this->ipsclass->adskin->form_yes_no("enabled", $group['enabled'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum Simultaneous Downloads</b><div class='graytext'>Number of files a particular user can download at the same time.</div>" ,
												                 $this->ipsclass->adskin->form_input("limit_sim", $group['limit_sim'] )
									                    )      );
									     
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>How many posts required to download?</b><div class='graytext'>If you wish to require members in this group to have a certain number of posts before they can download, set it here.</div>" ,
												                 $this->ipsclass->adskin->form_input("min_posts", $group['min_posts'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>How many posts per download?</b><div class='graytext'>If you wish to require members in this group to post a certain number of posts in between each download, set it here.</div>" ,
												                 $this->ipsclass->adskin->form_input("posts_per_dl", $group['posts_per_dl'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum bandwidth usage per day?</b><div class='graytext'>If you wish to limit the amount of bandwidth each user can use (on a daily basis), enter the amount (in kB).</div>" ,
												                 $this->ipsclass->adskin->form_input("daily_bw", $group['daily_bw'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum bandwidth usage per week?</b><div class='graytext'>If you wish to limit the amount of bandwidth each user can use (on a weekly basis), enter the amount (in kB).</div>" ,
												                 $this->ipsclass->adskin->form_input("weekly_bw", $group['weekly_bw'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum bandwidth usage per month?</b><div class='graytext'>If you wish to limit the amount of bandwidth each user can use (on a monthly basis), enter the amount (in kB).</div>" ,
												                 $this->ipsclass->adskin->form_input("monthly_bw", $group['monthly_bw'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum downloads per day?</b><div class='graytext'>Do you wish to limit the number of files each user can download (on a daily basis)?</div>" ,
												                 $this->ipsclass->adskin->form_input("daily_dl", $group['daily_dl'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum downloads per week?</b><div class='graytext'>Do you wish to limit the number of files each user can download (on a weekly basis)?</div>" ,
												                 $this->ipsclass->adskin->form_input("weekly_dl", $group['weekly_dl'] )
									                    )      );
									                    
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Maximum downloads per month?</b><div class='graytext'>Do you wish to limit the number of files each user can download (on a monthly basis)?</div>" ,
												                 $this->ipsclass->adskin->form_input("monthly_dl", $group['monthly_dl'] )
									                    )      );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form( 'Update' );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
			
	}
	

	//-----------------------------------------
	//
	// Show "Management Screen
	//
	//-----------------------------------------
	
	function main_screen()
	{
		$this->ipsclass->admin->page_title   = "Download Restrictions";
		
		$this->ipsclass->admin->page_detail  = "This page allows you to control download restrictions, such as how many files or how much bandwidth, members of each group can access over specified time periods.";
		
		$this->ipsclass->adskin->td_header[] = array( "Group"    		, "30%" );
		$this->ipsclass->adskin->td_header[] = array( "Restricted"     	, "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Max sim."		, "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Daily (bw)"     	, "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Daily (dl)"     	, "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Weekly (bw)"     , "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Weekly (dl)"     , "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Monthly (bw)"    , "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Monthly (dl)"    , "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Min Posts"    	, "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Posts per DL"    , "5%" );
		$this->ipsclass->adskin->td_header[] = array( "Edit"           	, "15%" );

		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Group Restriction Overview" );
						   
		$this->ipsclass->DB->simple_construct( array( 'select' => 'g_id, g_title, idm_restrictions, prefix, suffix', 'from' => 'groups', 'order' => 'g_title' ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				$rs 	= is_array( @unserialize( stripslashes( $r['idm_restrictions'] ) ) ) ? unserialize( stripslashes( $r['idm_restrictions'] ) ) : array();
				
				$enable	= $rs['enabled'] ? "<center><img src='{$this->ipsclass->skin_acp_url}/images/aff_tick.png' border='0' alt='X' /></center>" : "<center><img src='{$this->ipsclass->skin_acp_url}/images/aff_cross.png' border='0' alt='X' /></center>";
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<a href='{$this->ipsclass->vars['board_url']}/index.php?act=Members&max_results=30&showall=1&filter={$r['g_id']}&sort_order=asc&sort_key=members_display_name&st=0' target='_blank'>{$r['prefix']}{$r['g_title']}{$r['suffix']}</a>" ,
																		 $enable,
																		 intval($rs['limit_sim']),
																		 intval($rs['daily_bw']),
																		 intval($rs['daily_dl']),
																		 intval($rs['weekly_bw']),
																		 intval($rs['weekly_dl']),
																		 intval($rs['monthly_bw']),
																		 intval($rs['monthly_dl']),
																		 intval($rs['min_posts']),
																		 intval($rs['posts_per_dl']),
																		 "<center><a href='{$this->ipsclass->base_url}&{$this->ipsclass->form_code}&code=edit&id=".$r['g_id']."'>Edit</a></center>",
															)      );
				$max_pos++;
			}
		}
		else
		{
			$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("Umm...you have no groups?  I'd submit a ticket if you see this error.", "center", "tablerow1");
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();

	}
}


?>