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
|	> Last Updated On: Nov 11, 2005 5:48 PM EST
|
|	> Module Version .03
|
+--------------------------------------------------------------------------
*/

class lib_cfields
{
	var $file_id   	  = 0;
	var $cat_id		  = 0;
	var $init         = 0;
	var $in_fields    = array();
	var $out_fields   = array();
	var $out_chosen   = array();
	var $tmp_fields   = array();
	var $use_fields	  = array();
	var $cache_data   = "";
	var $file_data    = array();
	var $field_names  = array();
	var $field_desc   = array();
	var $kill_html    = 0;
	var $error_fields = array( 'toobig' => array(), 'empty' => array(), 'invalid' => array() );
	
	var $ipsclass     = "";
	var $parser;
	var $editor;
	
	/*-------------------------------------------------------------------------*/
	// Init (check, load cache)
	/*-------------------------------------------------------------------------*/
	
	function init_data()
	{
		if ( ! $this->init )
		{
			//-----------------------------------------
			// Cache data...
			//-----------------------------------------
			
			if ( ! is_array( $this->cache_data ) )
			{
				$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'downloads_cfields', 'order' => 'cf_position' ) );
				$this->ipsclass->DB->simple_exec();
				
				while ( $r = $this->ipsclass->DB->fetch_row() )
				{
					$this->cache_data[ $r['cf_id'] ] = $r;
				}
			}
			
			if( $this->cat_id )
			{
				$this->use_fields = explode( ",", $this->cat_id );
				
				if( !is_array($this->use_fields) OR !count($this->use_fields) )
				{
					$this->use_fields = array();
				}
			}
			
			//-----------------------------------------
			// Get names...
			//-----------------------------------------
			
			if ( is_array($this->cache_data) and count($this->cache_data) )
			{
				foreach( $this->cache_data as $id => $data )
				{
					if( count($this->use_fields) )
					{
						if( !in_array( $id, $this->use_fields ) )
						{
							continue;
						}
					}
					
					$this->field_names[ $id ] = $data['cf_title'];
					$this->field_desc[ $id ]  = $data['cf_desc'];
				}
			}
		}
		
		$this->out_fields = array();
		$this->tmp_fields = array();
		$this->out_chosen = array();
		
		//-----------------------------------------
		// Get member...
		//-----------------------------------------
		
		if ( ! count( $this->file_data ) and $this->file_id )
		{
			$this->file_data = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'downloads_ccontent', 'where' => 'file_id='.intval($this->file_id) ) );
		}
		
		if ( count( $this->file_data ) )
		{
			$this->file_id = $this->file_data['id'];
		}
		
		//-----------------------------------------
		// Parse into in fields
		//-----------------------------------------
		
		if ( is_array($this->cache_data) and count( $this->cache_data ) )
		{
			foreach( $this->cache_data as $id => $data )
			{
				if( count($this->use_fields) )
				{
					if( !in_array( $id, $this->use_fields ) )
					{
						continue;
					}
				}
									
				$this->in_fields[ $id ] = $this->file_data['field_'.$id];
			}
		}
		
		$this->init = 1;
	}
	
	/*-------------------------------------------------------------------------*/
	// Generate for saving
	/*-------------------------------------------------------------------------*/
	
	function parse_to_save( $post='field_' )
	{
		if ( is_array($this->cache_data) and count($this->cache_data) )
		{
			foreach( $this->cache_data as $i => $row )
			{
				if( count($this->use_fields) )
				{
					if( !in_array( $i, $this->use_fields ) )
					{
						continue;
					}
				}

				$this->tmp_fields[ $i ] = $row;
			}
		}
		
		//-----------------------------------------
		// Grab editable fields...
		//-----------------------------------------
		
		if ( is_array($this->tmp_fields) and count($this->tmp_fields) )
		{
			foreach( $this->tmp_fields as $i => $row )
			{
				//-----------------------------------------
				// Too big?
				//-----------------------------------------
				
				if ( $this->cache_data[$i]['cf_max_input'] and strlen( $_POST[ $post.$i ] ) > $this->cache_data[$i]['cf_max_input'] )
				{
					$this->error_fields['toobig'][] = $row;
				}
				
				//-----------------------------------------
				// Required and NULL?
				//-----------------------------------------
				
				if ( $this->cache_data[$i]['cf_not_null'] and trim($_POST[ $post.$i ]) == "" )
				{
					$this->error_fields['empty'][] = $row;
				}
				
				//-----------------------------------------
				// Invalid format?
				//-----------------------------------------
				
				if ( trim($this->cache_data[$i]['cf_input_format']) and $_POST[ $post.$i ] )
				{
					$regex = str_replace( 'n', '\\d', preg_quote( $this->cache_data[$i]['cf_input_format'], "#" ) );
					$regex = str_replace( 'a', '\\w', $regex );
					
					if ( ! preg_match( "#^".$regex."$#i", trim($_POST[ $post.$i ]) ) )
					{
						$this->error_fields['invalid'][] = $row;
					}
				}
				
				if( is_object( $this->parser ) )
				{
					$_POST[ $post.$i ] = $this->ipsclass->clean_evil_tags( $_POST[ $post.$i ] );
					$_POST[ $post.$i ] = $this->parser->pre_db_parse( $this->editor->process_raw_post( $post.$i ) );
				}
				
				$this->out_fields[ $post.$i ] = $this->method_format_text_to_save( $_POST[ $post.$i ] );				
			}
		}
	
	}
	
	/*-------------------------------------------------------------------------*/
	// Generate for viewing
	/*-------------------------------------------------------------------------*/
	
	function parse_to_view(  )
	{
		if ( is_array( $this->cache_data ) and count( $this->cache_data ) )
		{
			foreach( $this->cache_data as $i => $row )
			{
				if( count($this->use_fields) )
				{
					if( !in_array( $i, $this->use_fields ) )
					{
						continue;
					}
				}

				$this->tmp_fields[ $i ] = $row;
			}
		}
		
		$this->method_parse_out_fields('view');
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Generate for editing
	/*-------------------------------------------------------------------------*/
	
	function parse_to_edit()
	{
		if ( is_array($this->cache_data) and count($this->cache_data) )
		{
			foreach( $this->cache_data as $i => $row )
			{
				if( count($this->use_fields) )
				{
					if( !in_array( $i, $this->use_fields ) )
					{
						continue;
					}
				}

				$this->tmp_fields[ $i ] = $row;
			}
		}
		
		$this->method_parse_out_fields('edit');
	}
	
	/*-------------------------------------------------------------------------*/
	// Method: Parse out_fields
	/*-------------------------------------------------------------------------*/
	
	function method_parse_out_fields($type='view')
	{
		foreach( $this->tmp_fields as $i => $row )
		{
			if ($row['cf_type'] == 'drop')
			{ 
				$carray = explode( '|', trim( $row['cf_content'] ) );
				
				foreach( $carray as $entry )
				{
					$value = explode( '=', $entry );
					
					$ov = trim($value[0]);
					$td = trim($value[1]);
					
					if ( $type == 'view' )
					{
						if ( $this->in_fields[ $row['cf_id'] ] == $ov)
						{
							$this->out_fields[ $row['cf_id'] ] = $td;
							$this->out_chosen[ $row['cf_id'] ] = $ov;
						}
						else if ( $this->in_fields[ $row['cf_id'] ] == "" )
						{
						   $this->out_fields[ $row['cf_id'] ] = '';
						   $this->out_chosen[ $row['cf_id'] ] = '';
						}
					}
					else if ( $type == 'edit' )
					{
						if( $this->ipsclass->input[ 'field_'.$row['cf_id'] ] )
						{
							$this->in_fields[ $row['cf_id'] ] = $this->ipsclass->input[ 'field_'.$row['cf_id'] ];
						}
						
						if ( $this->in_fields[ $row['cf_id'] ] == $ov and $this->in_fields[ $row['cf_id'] ])
						{
							$this->out_fields[ $row['cf_id'] ] .= "<option value='{$ov}' selected='selected'>{$td}</option>\n";
						}
						else
						{
							$this->out_fields[ $row['cf_id'] ] .= "<option value='{$ov}'>{$td}</option>\n";
						}
					}
				}
			}
			else
			{
				if ( $type == 'view' )
				{
					$this->out_fields[ $row['cf_id'] ] = $this->method_make_safe_for_view( $this->in_fields[ $row['cf_id'] ] );
				}
				else
				{
					if( $type == 'edit' )
					{
						if( is_object( $this->parser ) )
						{
							$this->in_fields[ $row['cf_id'] ] = $this->ipsclass->clean_evil_tags( $this->ipsclass->input['field_'.$row['cf_id'] ] ? $this->ipsclass->input['field_'.$row['cf_id'] ] : $this->in_fields[ $row['cf_id'] ] );
							$this->in_fields[ $row['cf_id'] ] = $this->parser->pre_edit_parse( $this->ipsclass->input['field_'.$row['cf_id'] ] ? $this->ipsclass->input['field_'.$row['cf_id'] ] : $this->in_fields[ $row['cf_id'] ] );
						}
					}
					
					$this->out_fields[ $row['cf_id'] ] = $this->method_make_safe_for_form( $this->in_fields[ $row['cf_id'] ] );
				}
			}
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Method: format text to save
	/*-------------------------------------------------------------------------*/
	
	function method_format_text_to_save( $t )
	{
		$t = str_replace( "<br>"  , "\n", $t );
		$t = str_replace( "<br />", "\n", $t );
		$t = str_replace( "&#39;" , "'" , $t );
		
		if ( @get_magic_quotes_gpc() )
		{
			$t = stripslashes($t);
		}
		
		return $t;
	}
	
	/*-------------------------------------------------------------------------*/
	// Method: format_field_for_edit
	/*-------------------------------------------------------------------------*/
	
	function method_format_content_for_edit( $c )
	{
		return str_replace( '|', "\n", $c );
	}
	
	/*-------------------------------------------------------------------------*/
	// Method: format_field_for_file_view
	/*-------------------------------------------------------------------------*/
	
	function method_format_field_for_file_view( $i )
	{
		$out = $this->out_fields[$i];
		
		$tmp = $this->cache_data[$i]['cf_file_format'];
		
		$tmp = str_replace( '{title}'  , $this->field_names[$i], $tmp );
		$tmp = str_replace( '{key}'    , $this->out_chosen[$i] , $tmp );
		$tmp = str_replace( '{content}', $out                  , $tmp );
		
		return $tmp;
	}
	
	/*-------------------------------------------------------------------------*/
	// Method: format_field_for_save
	/*-------------------------------------------------------------------------*/
	
	function method_format_content_for_save( $c )
	{
		$c = str_replace( "\r"   , "\n", $c );
		$c = str_replace( "&#39;", "'" , $c );
		return str_replace( "\n", '|', str_replace( "\n\n", "\n", trim($c) ) );
	}
	
	/*-------------------------------------------------------------------------*/
	// Make safe for form viewing
	/*-------------------------------------------------------------------------*/
	
	function method_make_safe_for_form( $t )
	{
		return str_replace( "'", "&#39;", $t );
	}
	
	/*-------------------------------------------------------------------------*/
	// Make safe for other viewing (profile, etc)
	/*-------------------------------------------------------------------------*/
	
	function method_make_safe_for_view( $t )
	{
		$t = $this->ipsclass->clean_evil_tags( $t );
		
		if ( $this->kill_html )
		{
			$t = htmlspecialchars( $t );
			$t = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $t );
		}
		
		return $t;
	}
	
	

}


?>