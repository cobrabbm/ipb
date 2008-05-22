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
|   > Library: Topic Posting
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 11, 2005 5:48 PM EST
|
|	> Module Version .01
|
+--------------------------------------------------------------------------
*/

class lib_topics
{
	var $ipsclass;
	var $func_mod;
	var $parser;
	var $han_editor;
	var $post;
	var $email;
	var $cfields	= "";
	
	var $forum		= array();
	var $topic		= array();
	
	var $base_url	= "";
	
	//-----------------------------------------------
	// Interface method
	//-----------------------------------------------	
	
	function sort_topic( $file, $category, $type = 'new', $mid_override = 0 )
	{
		$this->base_url = $this->ipsclass->vars['board_url'] . '/index.php?';
		$this->cfields	= '';
		
		$this->parser->parse_nl2br = 1;
		
		if( !$file['file_open'] )
		{
			return FALSE;
		}
		
		if( !array_key_exists( 'global_guestname', $this->ipsclass->lang ) )
		{
			$this->ipsclass->load_language( 'lang_global' );
		}
		
		if( !isset($this->ipsclass->cache['idm_cfields']) )
		{
			$this->ipsclass->init_load_cache( array( 'idm_cfields' ) );
		}
		
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
	    		if( $fields->cache_data[ $id ]['cf_topic'] )
	    		{
		    		$data = $data ? $data : $this->ipsclass->lang['cat_no_info'];
		    		
					$this->cfields .= '[b]' . $fields->field_names[ $id ] . '[/b]: ' . $data . "<br />";
				}
    		}
		}

		if( $category['coptions']['opt_topice'] == 1 )
		{
			if( $category['coptions']['opt_topicf'] )
			{
				require_once( ROOT_PATH.'sources/lib/func_mod.php' );
				$this->func_mod           =  new func_mod();
				$this->func_mod->ipsclass =& $this->ipsclass;
				
				require_once( ROOT_PATH."sources/classes/post/class_post.php" );
				$this->post           =  new class_post();
				$this->post->ipsclass =& $this->ipsclass;
				
				$this->post->email =& $this->email;
						
				$category['coptions']['opt_topics'] = str_replace( "{catname}", $category['cname'], $category['coptions']['opt_topics'] );
				$category['coptions']['opt_topicp'] = str_replace( "{catname}", $category['cname'], $category['coptions']['opt_topicp'] );
				
				if( !$mid_override )
				{
					$file['file_submitter'] = ( $type == 'new' ) ? $this->ipsclass->member['id'] : $file['file_submitter'];
				}
				
				if( $file['file_submitter'] == 0 AND !$file['file_submitter_name'] )
				{
					$file['file_submitter_name'] = $this->ipsclass->lang['global_guestname'];
				}
								
				if(	$type == 'new' )
				{
					$this->post_new_topic( $file, $category );
				}
				else
				{
					$this->post_update_topic( $file, $category );
				}
			}
		}
	}
	
	//-----------------------------------------------
	// API function to create a new topic
	//-----------------------------------------------
	
	function post_new_topic( $file, $category )
	{
		$this->forum = $this->ipsclass->forums->forum_by_id[ $category['coptions']['opt_topicf'] ]['inc_postcount'];
										
		$ttitle = $file['file_name'];
		
		if( $category['coptions']['opt_topicp'] )
		{
			$ttitle = $category['coptions']['opt_topicp'].$ttitle;
		}
		if( $category['coptions']['opt_topics'] )
		{
			$ttitle .= $category['coptions']['opt_topics'];
		}					
		
		$post_content = "";

		if( $category['coptions']['opt_topicss'] )
		{
			if( $file['file_ssname'] )
			{
				$post_content = "[center][img]{$this->base_url}autocom=downloads&req=display&code=sst&id={$file['file_id']}[/img][/center]<br />";
			}
			else if( $file['file_ssurl'] )
			{
				$post_content = "[center][img]{$file['file_ssurl']}[/img][/center]<br />";
			}
		}
		
		$post_content .= "[b]{$this->ipsclass->lang['t_filename']}[/b]: {$file['file_name']}<br />";
		
		if( $file['file_submitter'] )
		{
			$post_content .= "[b]{$this->ipsclass->lang['t_fileauthor']}[/b]: [url={$this->base_url}showuser={$file['file_submitter']}]{$file['file_submitter_name']}[/url]<br />";
		}
		else
		{
			$post_content .= "[b]{$this->ipsclass->lang['t_fileauthor']}[/b]: {$file['file_submitter_name']}<br />";
		}
		
		$post_content .= "[b]{$this->ipsclass->lang['t_submitted']}[/b]: ".$this->ipsclass->get_date( $file['file_submitted'], 'DATE', 1 )."<br />";
		$post_content .= "[b]{$this->ipsclass->lang['t_category']}[/b]: [url={$this->base_url}autocom=downloads&showcat={$file['file_cat']}]".$category['cname']."[/url]<br />";
		
		if( $this->cfields )
		{
			$post_content .= $this->cfields;
		}
		
		// Make sure this is added after the cfields
		$post_content .= "<br />";
		
		$post_content .= $file['file_desc'];
		
		$post_content .= "<br /><br />[url={$this->base_url}autocom=downloads&showfile={$file['file_id']}]{$this->ipsclass->lang['t_clickhere']}[/url]";
		
		if( $this->han_editor->method == 'rte' )
		{
			$post_content = $this->parser->pre_db_parse( $this->han_editor->class_editor->_rte_html_to_bbcode( $post_content ) );
		}
		else
		{
			$post_content = $this->parser->pre_db_parse( $post_content );
		}
		
		$post_content = $this->parser->pre_display_parse( $post_content );
		
		$this->topic = array(
				  'title'            => $ttitle,
				  'state'            => 'open',
				  'posts'            => 0,
				  'starter_id'       => $file['file_submitter'],
				  'starter_name'     => $file['file_submitter_name'],
				  'start_date'       => $file['file_submitted'],
				  'last_poster_id'   => $file['file_submitter'],
				  'last_poster_name' => $file['file_submitter_name'],
				  'last_post'        => $file['file_submitted'],
				  'icon_id'          => 0,
				  'author_mode'      => 1,
				  'poll_state'       => 0,
				  'last_vote'        => 0,
				  'views'            => 0,
				  'forum_id'         => $category['coptions']['opt_topicf'],
				  'approved'         => 1,
				  'pinned'           => 0 
			);
				  					
		$this->ipsclass->DB->do_insert( 'topics', $this->topic );
		$this->topic['tid']     = $this->ipsclass->DB->get_insert_id();

		$this->post->forum_tracker( $this->topic['forum_id'], $this->topic['tid'], $this->topic['title'], $this->forum['name'], $post_content );
		
		$post = array(
				'author_id'      => $file['file_submitter'],
				'use_sig'        => 1,
				'use_emo'        => 1,
				'ip_address'     => $this->ipsclass->ip_address,
				'post_date'      => $file['file_submitted'],
				'icon_id'        => 0,
				'post'           => $post_content,
				'author_name'    => $file['file_submitter_name'],
				'topic_id'       => $this->topic['tid'],
				'queued'         => 0,
				'post_htmlstate' => 2,
				'post_key'       => md5( microtime() ),
			 );
			 
		$this->ipsclass->DB->do_insert( 'posts', $post );
		$pid = $this->ipsclass->DB->get_insert_id();
		
		$this->func_mod->rebuild_topic( $this->topic['tid'], 0 );
		
		if ( $this->forum['inc_postcount'] )
		{
			$this->ipsclass->DB->simple_update( 'members', 'posts=posts+1', 'id='.intval( $mid ) );
			$this->ipsclass->DB->simple_exec();
		}
				
		$this->func_mod->forum_recount( $category['coptions']['opt_topicf'] );
		$this->func_mod->stats_recount();
			 
		$this->ipsclass->DB->do_update( "downloads_files", array( 'file_topicid' => $this->topic['tid'] ), "file_id=".$file['file_id'] );		
	}
	
	//-----------------------------------------------
	// API function to update an existing topic
	//-----------------------------------------------
		
	function post_update_topic( $file, $category )
	{
		$tid = $file['file_topicid'];

		if( $tid > 0 && $file['file_open'] )
		{
			$ttitle = $file['file_name'];
			
			if( $category['coptions']['opt_topicp'] )
			{
				$ttitle = $category['coptions']['opt_topicp'].$ttitle;
			}
			if( $category['coptions']['opt_topics'] )
			{
				$ttitle .= $category['coptions']['opt_topics'];
			}
										
			$this->ipsclass->DB->do_update( "topics", array( 'title' => $ttitle, 'forum_id' => $category['coptions']['opt_topicf'] ), "tid=".$tid );
			
			$firstpost = $this->ipsclass->DB->build_and_exec_query( array( 'select' 	=> 'topic_firstpost',
																			 'from'	  => 'topics',
																			 'where'  => 'tid='.$tid
																	)		);
			
			if( $firstpost['topic_firstpost'] )
			{
				$post_content = "";

				if( $category['coptions']['opt_topicss'] )
				{
					if( $file['file_ssname'] )
					{
						$post_content = "[center][img]{$this->base_url}autocom=downloads&req=display&code=sst&id={$file['file_id']}[/img][/center]<br />";
					}
					else if( $file['file_ssurl'] )
					{
						$post_content = "[center][img]{$file['file_ssurl']}[/img][/center]<br />";
					}
				}
				
				$post_content .= "[b]{$this->ipsclass->lang['t_filename']}[/b]: {$file['file_name']}<br />";
				
				if( $file['file_submitter'] )
				{
					$post_content .= "[b]{$this->ipsclass->lang['t_fileauthor']}[/b]: [url={$this->base_url}showuser={$file['file_submitter']}]{$file['file_submitter_name']}[/url]<br />";
				}
				else
				{
					$post_content .= "[b]{$this->ipsclass->lang['t_fileauthor']}[/b]: {$file['file_submitter_name']}<br />";
				}

				$post_content .= "[b]{$this->ipsclass->lang['t_submitted']}[/b]: ".$this->ipsclass->get_date( $file['file_submitted'], 'DATE', 1 )."<br />";
				$post_content .= "[b]{$this->ipsclass->lang['t_updated']}[/b]: [i]".$this->ipsclass->get_date( $file['file_updated'], 'DATE', 1 )."[/i]<br />";
				$post_content .= "[b]{$this->ipsclass->lang['t_category']}[/b]: [url={$this->base_url}autocom=downloads&showcat={$file['file_cat']}]".$category['cname']."[/url]<br />";
				
				if( $this->cfields )
				{
					$post_content .= $this->cfields;
				}
				
				// Make sure this is added after the cfields
				$post_content .= "<br />";
		
				$post_content .= $file['file_desc'];
				
				$post_content .= "<br /><br />[url={$this->base_url}autocom=downloads&showfile={$file['file_id']}]{$this->ipsclass->lang['t_clickhere']}[/url]";
				
				if( $this->han_editor->method == 'rte' )
				{
					$post_content = $this->parser->pre_db_parse( $this->han_editor->class_editor->_rte_html_to_bbcode( $post_content ) );
				}
				else
				{
					$post_content = $this->parser->pre_db_parse( $post_content );
				}
				
				$post_content = $this->parser->pre_display_parse( $post_content );

				$this->ipsclass->DB->do_update( "posts", array( 'post' => $post_content, 'edit_time' => $file['file_updated'], 'edit_name' => $file['file_submitter_name'] ), 'pid='.$firstpost['topic_firstpost'] );
				
				$this->func_mod->forum_recount( $category['coptions']['opt_topicf'] );
				$this->func_mod->stats_recount();							
			}
		}		
	}

}







?>