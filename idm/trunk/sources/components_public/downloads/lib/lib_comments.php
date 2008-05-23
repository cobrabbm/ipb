<?php
/*
+--------------------------------------------------------------------------
|	Invision Download Manager Module v1.2.1
|	========================================
|	by Brandon Farber
|	(c) 2001 - 2005 Invision Power Services
|	========================================
+---------------------------------------------------------------------------
|   > $Date: 2007-08-08 16:22:53 -0400 (Wed, 08 Aug 2007) $
|   > $Revision: 406 $
|	> $Author: bfarber $
|	> Main Module
|	> $Id: lib_comments.php 406 2007-08-08 20:22:53Z bfarber $
+---------------------------------------------------------------------------
*/


/**
* Library/Comment View
*
* Handles comment display
*
* @package		Download Manager
* @subpackage 	Library
* @author		Brandon Farber
* @version		1.2.1
* @since 		2.0
*/

class lib_comments
{
	# Objects
	var $ipsclass;
	var $catlib;
	var $parser;
	var $funcs;
	var $custom_fields;
	
	var $output;

	var $qpids			= array();
	var $pids			= array();
	var $data			= array();
	var $info			= array();
	var $cached_members = array();
	
	var $mod			= false;
	var $no_comments 	= false;
	
	var $post_count		= 0;

	function init()
	{	
		$this->ipsclass->load_template( 'skin_topic' );
        $this->ipsclass->load_language('lang_topic');
        
        if ( !isset( $this->ipsclass->cache['ranks'] ) )
        {
        	$this->ipsclass->init_load_cache( array( 'ranks' ) );
        }
		
		if( !is_object($this->parser) )
		{
			require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
			$this->parser				 		= new parse_bbcode();
			$this->parser->ipsclass		 		=& $this->ipsclass;
			$this->parser->allow_update_caches 	= 1;
			$this->parser->bypass_badwords 		= intval($this->ipsclass->member['g_bypass_badwords']);
		}

		if ( $this->ipsclass->vars['custom_profile_topic'] == 1 )
		{
			require_once( ROOT_PATH.'sources/classes/class_custom_fields.php' );
			$this->custom_fields 				= new custom_fields( $this->ipsclass->DB );

			$this->custom_fields->member_id 	= $this->ipsclass->member['id'];
			$this->custom_fields->cache_data 	= $this->ipsclass->cache['profilefields'];
			$this->custom_fields->admin	 		= intval($this->ipsclass->member['g_access_cp']);
			$this->custom_fields->supmod		= intval($this->ipsclass->member['g_is_supmod']);
		}
		
		$this->qpids = $this->ipsclass->my_getcookie( 'idm_pids' );
		
		$this->ipsclass->input['selectedpids'] = $this->ipsclass->my_getcookie( 'idmmodpids' );
		
		$this->ipsclass->input['selectedpidcount'] = intval( count( preg_split( "/,/", $this->ipsclass->input['idmmodpids'], -1, PREG_SPLIT_NO_EMPTY ) ) );
		
		if( $this->ipsclass->input['selectedpidcount'] > 0 )
		{
			$this->ipsclass->lang['mod_button'] .= ' (' . $this->ipsclass->input['selectedpidcount'] . ')';
		}
		
		$this->ipsclass->my_setcookie('idmmodpids', '', 0);		
	}

	/**
	* comment_view::get()
	*
	* Mostly borrowed from Gallery
	*
	* @author Brandon Farber
	* @author Joshua Williams
	* @author Matt Mecham
	* @return HTML output
	**/
	function return_file_comments( $ident )
	{
		if( is_numeric($ident) )
		{
			// File id - probably from popup window
			$file = $this->ipsclass->DB->build_and_exec_query( array( 'select' => '*', 'from' => 'downloads_files', 'where' => 'file_id=' . intval($ident) )	);

			$category = $this->catlib->cat_lookup[$file['file_cat']];
		}
		else if( is_array($ident) )
		{
			// Request from display.php...ident is the $file array
			$file = $ident;
			
			$category = $this->catlib->cat_lookup[$file['file_cat']];
		}
		else
		{
			return '';
		}
		
		if( !$file['file_id'] )
		{
			return '';
		}
		
		if( count($this->catlib->member_access['show']) == 0 )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		else if( ! in_array( $file['file_cat'], $this->catlib->member_access['view'] ) )
		{
			$this->output .= $this->funcs->produce_error( 'no_comments_perms' );
			return;
		}
		
		//-----------------------------------------
		// Grab the posts we'll need
		//-----------------------------------------

		$st = intval( $this->ipsclass->input['st'] ) > 0 ? intval( $this->ipsclass->input['st'] ) : 0;

		//-----------------------------------------
		// Moderator?
		//-----------------------------------------

		$this->mod = $this->funcs->checkPerms( $file, 'modcancomments' );
		
		if( $this->mod )
		{
			$limiter  = "";
			$limiter2 = "";
		}
		else
		{
			$limiter  = " AND comment_open=1";
			$limiter2 = " AND c.comment_open=1";
		}
		
		if( $this->ipsclass->input['filter'] == 'que' )
		{
			$orderby = "c.comment_open ASC, c.comment_date ASC";
		}
		else
		{
			$orderby = "c.comment_date ASC";
		}
		
		$max_num = $this->ipsclass->vars['idm_comments_num'] ? $this->ipsclass->vars['idm_comments_num'] : 10;

		$max = $this->ipsclass->DB->simple_exec_query( array( 'select' => "COUNT(*) as total_comments",
															  'from'   => "downloads_comments",
															  'where'  => "comment_fid={$file['file_id']}{$limiter}"
									  				  )		);

		if( !$max['total_comments'] )
		{
			return '';
		}
		
		if( $this->ipsclass->vars['idm_comment_display'] == 'pop' )
		{
			$base_url = $this->ipsclass->base_url."autocom=downloads&amp;req=comments&amp;code=pop_com&amp;file={$file['file_id']}";
		}
		else
		{
			$base_url = $this->ipsclass->base_url."autocom=downloads&amp;showfile={$file['file_id']}";
		}

		$links = $this->ipsclass->build_pagelinks(  array(  'TOTAL_POSS'	=> $max['total_comments'],
															'PER_PAGE'		=> $max_num,
															'CUR_ST_VAL'	=> $st,
															'L_SINGLE'		=> "",
															'BASE_URL'		=> $base_url
									  )		  );			
		
		$collapsed_ids = ','.$this->ipsclass->my_getcookie('collapseprefs').',';
		
		$show['div_fo'] = 'show';
		$show['div_fc'] = 'none';
				
		if ( strstr( $collapsed_ids, ',idm_comm,' ) )
		{
			$show['div_fo'] = 'none';
			$show['div_fc'] = 'show';
		}

		$this->output .= $this->ipsclass->compiled_templates['skin_downloads']->comments_block( $show, $file['file_id'], $links, $this->mod );
		
        if ( $this->ipsclass->vars['custom_profile_topic'] == 1 )
        {
			$this->ipsclass->DB->simple_construct( array( 'select'	=> "c.*",
								          'from'	=> array('downloads_comments' => 'c'),
							              'add_join'=> array( 0 => array( 'select' => 'm.id, m.mgroup, m.email, m.joined, m.posts, m.last_visit, m.last_activity, m.login_anonymous, m.title, m.hide_email, m.warn_level, m.warn_lastwarn, m.members_display_name',
														                  'from'   => array( 'members' => 'm' ),
														                  'where'  => "c.comment_mid=m.id",
														                  'type'   => 'left'
																		),
							              					  1 => array( 'select' => 'me.msnname, me.aim_name, me.icq_number, me.signature, me.website, me.yahoo, me.location, me.avatar_location, me.avatar_type, me.avatar_size',
														                  'from'   => array( 'member_extra' => 'me' ),
														                  'where'  => "me.id=m.id",
														                  'type'   => 'left'
																		),
							              					  2 => array( 'select' => 'pc.*',
														                  'from'   => array( 'pfields_content' => 'pc' ),
														                  'where'  => "c.comment_mid=pc.member_id",
														                  'type'   => 'left'
																		),
							              					  3 => array( 'select' => 'pp.*',
														                  'from'   => array( 'profile_portal' => 'pp' ),
														                  'where'  => "m.id=pp.pp_member_id",
														                  'type'   => 'left'
																		),
															),
	            						  'where'	=> "c.comment_fid={$file['file_id']}{$limiter2}",
	            						  'order'	=> $orderby,
	            						  'limit'	=> array($st, $max_num)
							     )		);
		}
		else
		{
			$this->ipsclass->DB->simple_construct( array( 'select'	=> "c.*",
								          'from'	=> array('downloads_comments' => 'c'),
							              'add_join'=> array( 0 => array( 'select' => 'm.id, m.mgroup, m.email, m.joined, m.posts, m.last_visit, m.last_activity, m.login_anonymous, m.title, m.hide_email, m.warn_level, m.warn_lastwarn, m.members_display_name',
														                  'from'   => array( 'members' => 'm' ),
														                  'where'  => "c.comment_mid=m.id",
														                  'type'   => 'left'
																		),
							              					  1 => array( 'select' => 'me.msnname, me.aim_name, me.icq_number, me.signature, me.website, me.yahoo, me.location, me.avatar_location, me.avatar_type, me.avatar_size',
														                  'from'   => array( 'member_extra' => 'me' ),
														                  'where'  => "me.id=m.id",
														                  'type'   => 'left'
																		),
							              					  2 => array( 'select' => 'pp.*',
														                  'from'   => array( 'profile_portal' => 'pp' ),
														                  'where'  => "m.id=pp.pp_member_id",
														                  'type'   => 'left'
																		),
															),
	            						  'where'	=> "c.comment_fid={$file['file_id']}{$limiter2}",
	            						  'order'	=> $orderby,
	            						  'limit'	=> array($st, $max_num)
							     )		);
		}
		
		$outer = $this->ipsclass->DB->simple_exec();
		
		$comment_output 		= "";

		//-----------------------------------------
		// Format and print out the topic list
		//-----------------------------------------

		while ( $row = $this->ipsclass->DB->fetch_row( $outer ) )
		{
			$comment = $this->parse_row( $row );

			$comment_output .= $this->ipsclass->compiled_templates['skin_downloads']->comments_display( $comment, $this->mod );

			//-----------------------------------------
			// Are we giving this bloke a good ignoring?
			//-----------------------------------------

			if ( $this->ipsclass->member['ignored_users'] )
			{
				if ( strstr( $this->ipsclass->member['ignored_users'], ','.$comment['id'].',' ) and $this->ipsclass->input['cid'] != $comment['comment_id'] )
				{
					if ( ! strstr( $this->ipsclass->vars['cannot_ignore_groups'], ','.$comment['mgroup'].',' ) )
					{
						$comment_output .= $this->ipsclass->compiled_templates['skin_downloads']->comment_ignored( $comment );
						continue;
					}
				}
			}
		}

		//-----------------------------------------
		// Print the footer
		//-----------------------------------------

		$this->output = str_replace( "<!--COMMENTS-->", $comment_output, $this->output );
		
		return $this->output;

	}

	/*-------------------------------------------------------------------------*/
	// Parse post
	/*-------------------------------------------------------------------------*/

	function parse_row( $row = array() )
	{
		if ($row['comment_mid'] != 0)
		{
			$row['name_css'] = 'normalname';
			$row = $this->parse_member( $row );
		}
		else
		{
			//-----------------------------------------
			// It's definitely a guest...
			//-----------------------------------------

			$row = array_merge( $row, $this->ipsclass->set_up_guest( $this->ipsclass->lang['global_guestname'] ) );
			$row['name_css'] = 'unreg';
		}

		//-----------------------------------------

		if ( ! $row['comment_open'] )
		{
			$row['post_css'] = $this->post_count % 2 ? 'post1shaded' : 'post2shaded';
			$row['altrow']	= 'row4shaded';
		}
		else
		{
			$row['post_css'] = $this->post_count % 2 ? 'post1' : 'post2';
			$row['altrow']	= 'row4';
		}

		//-----------------------------------------

		if ( ($row['comment_append_edit'] == 1) && ($row['comment_edit_time'] != "") && ($row['comment_edit_name'] != "") )
		{
			$e_time = $this->ipsclass->get_date( $row['comment_edit_time'] , 'LONG' );

			$row['comment_text'] .= "<br /><br /><span class='edit'>".sprintf($this->ipsclass->lang['edited_by'], $row['comment_edit_name'], $e_time)."</span>";
		}

		//-----------------------------------------

		if (!$this->ipsclass->member['view_img'])
		{
			//-----------------------------------------
			// unconvert smilies first, or it looks a bit crap.
			//-----------------------------------------

			$row['comment_text'] = preg_replace( "#<!--emo&(.+?)-->.+?<!--endemo-->#", "\\1" , $row['comment_text'] );

			$row['comment_text'] = preg_replace( "/<img src=[\"'](.+?)[\"'].+?".">/", "(IMG:<a href='\\1' target='_blank'>\\1</a>)", $row['comment_text'] );
		}

		//-----------------------------------------
		// Online, offline?
		//-----------------------------------------

		if ( $row['comment_mid'] )
		{
			$time_limit = time() - $this->ipsclass->vars['au_cutoff'] * 60;

			$row['online_status_indicator'] = '<{PB_USER_OFFLINE}>';

			list( $be_anon, $loggedin ) = explode( '&', $row['login_anonymous'] );

			if ( ( $row['last_visit'] > $time_limit or $row['last_activity'] > $time_limit ) AND $be_anon != 1 AND $loggedin == 1 )
			{
				$row['online_status_indicator'] = '<{PB_USER_ONLINE}>';
			}
		}
		else
		{
			$row['online_status_indicator'] = '';
		}

		//-----------------------------------------
		// Multi Quoting?
		//-----------------------------------------

		$row['mq_start_image'] = $this->ipsclass->compiled_templates[ 'skin_downloads' ]->mq_image_add($row['comment_id']);

		if ( $this->qpids )
		{
			if ( strstr( ','.$this->qpids.',', ','.$row['comment_id'].',' ) )
			{
				$row['mq_start_image'] = $this->ipsclass->compiled_templates[ 'skin_downloads' ]->mq_image_remove($row['comment_id']);
			}
		}

		//-----------------------------------------
		// Multi PIDS?
		//-----------------------------------------

		if ( $this->mod )
		{
			$row['pid_start_image'] = $this->ipsclass->compiled_templates[ 'skin_downloads' ]->pid_image_unselected($row['comment_id']);

			if ( $this->ipsclass->input['selectedpids'] )
			{
				if ( strstr( ','.$this->ipsclass->input['selectedpids'].',', ','.$row['comment_id'].',' ) )
				{
					$row['pid_start_image'] = $this->ipsclass->compiled_templates[ 'skin_downloads' ]->pid_image_selected($row['comment_id']);
				}
			}
		}
		
		$row['comment_date']	= $this->ipsclass->get_date( $row['comment_date'], 'LONG' );

		//-----------------------------------------
		// Get buttons..
		//-----------------------------------------

		$row['delete_button'] 	= ( $this->mod OR ( $this->ipsclass->vars['idm_comment_delete'] AND $this->ipsclass->member['id'] == $comment['comment_mid'] AND $this->ipsclass->member['id'] > 0 ) ) ? 1 : 0;

		$row['edit_button']		= ( $this->mod OR ( $this->ipsclass->vars['idm_comment_edit'] AND $this->ipsclass->member['id'] == $comment['comment_mid'] AND $this->ipsclass->member['id'] > 0 ) ) ? 1 : 0;
		
		$row['approve_button']	= ( $this->mod AND $row['comment_open'] == 0 ) ? 1 : 0;

		$row['ip_address']		= $this->view_ip($row);

		$row['report_button']	= ( ( $this->ipsclass->vars['disable_reportpost'] != 1 ) AND ( $this->ipsclass->member['id'] OR $this->ipsclass->vars['idm_guest_report'] ) ) ? 1 : 0;

		//-----------------------------------------
		// Parse HTML tag on the fly
		//-----------------------------------------

		$this->parser->parse_html 		= 0;
		$this->parser->parse_wordwrap 	= $this->ipsclass->vars['post_wordwrap'];
		$this->parser->parse_nl2br		= 1;
		$this->parser->parse_bbcode		= 1;
		$this->parser->parse_smilies	= $row['use_emo'];

		$row['comment_text'] = $this->parser->pre_display_parse( $row['comment_text'] );

		//-----------------------------------------
		// Siggie stuff
		//-----------------------------------------

		$row['signature'] = "";
		
		if (isset($row['signature']) AND $row['signature'] AND  $this->ipsclass->member['view_sigs'])
		{
			if ($row['use_sig'] == 1)
			{
				$row['signature'] = $this->ipsclass->compiled_templates['skin_global']->signature_separator( $row['signature'] );
			}
		}

		//-----------------------------------------
		// Post number
		//-----------------------------------------

		$this->post_count++;
		
		$row['post_count'] 	= intval($this->ipsclass->input['st']) + $this->post_count;
		$row['file'] 		= intval( $this->ipsclass->input['showfile'] );

		return $row;
	}


	/*-------------------------------------------------------------------------*/
	// Parse the member info
	/*-------------------------------------------------------------------------*/

	function parse_member( $member=array() )
	{
		$group_name         = $this->ipsclass->make_name_formatted( $this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_title'], $member['mgroup'] );
		$member['avatar'] 	= $this->ipsclass->get_avatar( $member['avatar_location'], $this->ipsclass->member['view_avs'], $member['avatar_size'], $member['avatar_type'] );
		$member['name'] 	= $member['members_display_name'] ? $member['members_display_name'] : $member['name'];
		
		$pips = 0;

		if( is_array($this->ipsclass->cache['ranks']) AND count($this->ipsclass->cache['ranks']) )
		{
			foreach($this->ipsclass->cache['ranks'] as $k => $v)
			{
				if ($member['posts'] >= $v['POSTS'])
				{
					if (!$member['title'])
					{
						$member['title'] = $this->ipsclass->cache['ranks'][ $k ]['TITLE'];
					}
	
					$pips = $v['PIPS'];
					break;
				}
			}
		}

		if ( $this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_icon'] )
		{
			$member['member_rank_img'] = $this->ipsclass->compiled_templates[ 'skin_topic' ]->member_rank_img($this->ipsclass->cache['group_cache'][ $member['mgroup'] ]['g_icon']);
		}
		else if ( $pips )
		{
			if ( is_numeric( $pips ) )
			{
				for ($i = 1; $i <= $pips; ++$i)
				{
					$member['member_rank_img'] .= "<{A_STAR}>";
				}
			}
			else
			{
				$member['member_rank_img'] = $this->ipsclass->compiled_templates[ 'skin_topic' ]->member_rank_img( 'style_images/<#IMG_DIR#>/folder_team_icons/'.$pips );
			}
		}

		$member['member_joined']   = $this->ipsclass->compiled_templates['skin_topic']->member_joined( $this->ipsclass->get_date( $member['joined'], 'JOINED' ) );
		$member['member_group']    = $this->ipsclass->compiled_templates['skin_topic']->member_group( $group_name );
		$member['member_posts']    = $this->ipsclass->compiled_templates['skin_topic']->member_posts( $this->ipsclass->do_number_format($member['posts']) );
		$member['member_number']   = $this->ipsclass->compiled_templates['skin_topic']->member_number( $this->ipsclass->do_number_format($member['id']) );
		$member['profile_icon']    = $this->ipsclass->compiled_templates['skin_topic']->member_icon_profile( $member['id'] );
		$member['message_icon']    = $this->ipsclass->compiled_templates['skin_topic']->member_icon_msg( $member['id'] );
		$member['member_location'] = $member['location'] ? $this->ipsclass->compiled_templates['skin_topic']->member_location( $member['location'] ) : '';
		$member['email_icon']      = ! $member['hide_email'] ? $this->ipsclass->compiled_templates['skin_topic']->member_icon_email( $member['id'] ) : '';
		$member['addresscard']     = $member['id'] ? $this->ipsclass->compiled_templates['skin_topic']->member_icon_vcard( $member['id'] ) : '';
		
		$this->parser->parse_html   = intval($this->ipsclass->vars['sig_allow_html']);
		$this->parser->parse_nl2br  = 1;
		$this->parser->parse_bbcode	= intval($this->ipsclass->vars['sig_allow_ibc']);

		$member['signature'] = $this->parser->pre_display_parse($member['signature']);

		//-----------------------------------------
		// Warny porny?
		//-----------------------------------------
		
		$member['warn_percent']	= NULL;
		$member['warn_img']		= NULL;
		$member['warn_text']	= NULL;
		$member['warn_add']		= NULL;
		$member['warn_minus']	= NULL;
		
		if ( $this->ipsclass->vars['warn_on'] and ( ! strstr( ','.$this->ipsclass->vars['warn_protected'].',', ','.$member['mgroup'].',' ) ) )
		{
			if ( ( isset($this->ipsclass->member['_moderator'][ $this->topic['forum_id'] ]['allow_warn'])
				AND $this->ipsclass->member['_moderator'][ $this->topic['forum_id'] ]['allow_warn'] )
				OR ( $this->ipsclass->member['g_is_supmod'] == 1 )
				OR ( $this->ipsclass->vars['warn_show_own'] and ( $this->ipsclass->member['id'] == $member['id'] ) ) 
			   )
			{
				// Work out which image to show.
				
				if ( ! $this->ipsclass->vars['warn_show_rating'] )
				{
					if ( $member['warn_level'] <= $this->ipsclass->vars['warn_min'] )
					{
						$member['warn_img']     = '<{WARN_0}>';
						$member['warn_percent'] = 0;
					}
					else if ( $member['warn_level'] >= $this->ipsclass->vars['warn_max'] )
					{
						$member['warn_img']     = '<{WARN_5}>';
						$member['warn_percent'] = 100;
					}
					else
					{
						
						$member['warn_percent'] = $member['warn_level'] ? sprintf( "%.0f", ( ($member['warn_level'] / $this->ipsclass->vars['warn_max']) * 100) ) : 0;
						
						if ( $member['warn_percent'] > 100 )
						{
							$member['warn_percent'] = 100;
						}
						
						if ( $member['warn_percent'] >= 81 )
						{
							$member['warn_img'] = '<{WARN_5}>';
						}
						else if ( $member['warn_percent'] >= 61 )
						{
							$member['warn_img'] = '<{WARN_4}>';
						}
						else if ( $member['warn_percent'] >= 41 )
						{
							$member['warn_img'] = '<{WARN_3}>';
						}
						else if ( $member['warn_percent'] >= 21 )
						{
							$member['warn_img'] = '<{WARN_2}>';
						}
						else if ( $member['warn_percent'] >= 1 )
						{
							$member['warn_img'] = '<{WARN_1}>';
						}
						else
						{
							$member['warn_img'] = '<{WARN_0}>';
						}
					}
					
					if ( $member['warn_percent'] < 1 )
					{
						$member['warn_percent'] = 0;
					}
					
					$member['warn_text']  = $this->ipsclass->compiled_templates['skin_topic']->warn_level_warn($member['id'], $member['warn_percent'] );
				}
				else
				{
					// Ratings mode..
					
					$member['warn_text']  = $this->ipsclass->lang['tt_rating'];
					$member['warn_img']   = $this->ipsclass->compiled_templates['skin_topic']->warn_level_rating($member['id'], $member['warn_level'], $this->ipsclass->vars['warn_min'], $this->ipsclass->vars['warn_max']);
				}
								
				if ( ( isset($this->ipsclass->member['_moderator'][ $this->topic['forum_id'] ]['allow_warn']) AND $this->ipsclass->member['_moderator'][ $this->topic['forum_id'] ]['allow_warn'] ) or $this->ipsclass->member['g_is_supmod'] == 1 )
				{
					$member['warn_add']   = "<a href='{$this->ipsclass->base_url}act=warn&amp;type=add&amp;mid={$member['id']}&amp;t={$this->topic['tid']}&amp;st=".intval($this->ipsclass->input['st'])."' title='{$this->ipsclass->lang['tt_warn_add']}'><{WARN_ADD}></a>";
					$member['warn_minus'] = "<a href='{$this->ipsclass->base_url}act=warn&amp;type=minus&amp;mid={$member['id']}&amp;t={$this->topic['tid']}&amp;st=".intval($this->ipsclass->input['st'])."' title='{$this->ipsclass->lang['tt_warn_minus']}'><{WARN_MINUS}></a>";
				}
			}
		}
		
		//-----------------------------------------
		// Profile fields stuff
		//-----------------------------------------
		
		$member['custom_fields'] = "";
		
		if ( $this->ipsclass->vars['custom_profile_topic'] == 1 )
		{
			if ( $this->custom_fields )
			{
				$this->custom_fields->member_data = $member;
				$this->custom_fields->init_data();
				$this->custom_fields->parse_to_view( 1 );
				
				if ( count( $this->custom_fields->out_fields ) )
				{
					foreach( $this->custom_fields->out_fields as $i => $data )
					{
						if ( $data )
						{
							$member['custom_fields'] .= "\n".$this->custom_fields->method_format_field_for_topic_view( $i );
						}
					}
				}
			}
		}
		
		//-----------------------------------------
		// Photo and such
		//-----------------------------------------
		
		$member = $this->ipsclass->member_set_information( $member, 0, 0 );
		
		//-----------------------------------------
		// Fix up the membername so it links to the members profile
		//-----------------------------------------

		if ($member['id'])
		{
			$member['members_display_name'] = "<a href='{$this->ipsclass->base_url}showuser={$member['id']}'>{$member['members_display_name']}</a>";
		}
		
		return $member;
	}

	function view_ip($row)
	{
		if( $this->ipsclass->member['g_is_supmod'] == 1 )
		{
			$row['ip_address'] = $row['mgroup'] == $this->ipsclass->vars['admin_group']
								? "[ <i>{$this->ipsclass->lang['ipis_private']}</i> ]"
								: "[ <a href='{$this->ipsclass->base_url}act=UserCP&amp;CODE=doiptool&amp;iptool=resolve&amp;ip={$row['ip_address']}' target='_blank'>{$row['ip_address']}</a> ]";

			return $this->ipsclass->compiled_templates[ 'skin_topic' ]->ip_show($row['ip_address']);
		}
	}

}
?>