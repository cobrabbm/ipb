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
|   > Library: Image Thumbnail Generation
|   > Script written by Brandon Farber
|   > Script Started: Oct 24, 2005 5:48 PM EST
|	> Last Updated On: Nov 11, 2005 5:48 PM EST
|
|	> Module Version .04
|
+--------------------------------------------------------------------------
*/

class lib_thumb
{

	var $in_type          = 'file';
	var $out_type         = 'file';
	var $out_file_name    = '';
	var $out_file_dir     = '';
	var $in_file_dir      = '.';
	var $in_file_name     = '';
	var $in_file_complete = '';
	var $desired_width    = 0;
	var $desired_height   = 0;
	var $gd_version       = 2;
	var $image_type       = '';
	var $file_extension   = '';
	
	var $do_copy		  = 0;
	var $cpy_txt		  = 'Copyright %s';
	var $do_water		  = 0;
	var $water_path		  = '';
	
	var $fullsize		  = 0;
	
	
	/*-------------------------------------------------------------------------*/
	// Clean paths
	/*-------------------------------------------------------------------------*/

	function clean_paths()
	{
		$this->in_file_dir  = preg_replace( "#/$#", "", $this->in_file_dir );
		$this->out_file_dir = preg_replace( "#/$#", "", $this->out_file_dir );
		
		if ( $this->in_file_dir and $this->in_file_name )
		{
			$this->in_file_complete = $this->in_file_dir.'/'.$this->in_file_name;
		}
		else
		{
			$this->in_file_complete = $this->in_file_name;
		}
		
		if ( ! $this->out_file_dir )
		{
			$this->out_file_dir = $this->in_file_dir;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// GENERATE THUMBNAIL
	/*-------------------------------------------------------------------------*/
	
	function generate_thumbnail()
	{
		$return = array();
		$image  = "";
		$thumb  = "";
		
		//-----------------------------------
		// Set up paths
		//-----------------------------------
		
		$this->clean_paths();
		
		$remap  = array( 1 => 'GIF', 2 => 'JPG', 3 => 'PNG', 4 => 'SWF', 5 => 'PSD', 6 => 'BMP' );
		
		if ( $this->desired_width and $this->desired_height )
		{
			$img_size = array();
			
			if ( $this->in_type == 'file' )
			{
				$img_size = @getimagesize( $this->in_file_complete );
			}
			
			if ( $img_size[0] < 1 and $img_size[1] < 1 )
			{
				$img_size    = array();
				$img_size[0] = $this->desired_width;
				$img_size[1] = $this->desired_height;
				
				$return['thumb_width']    = $this->desired_width;
				$return['thumb_height']   = $this->desired_height;
				
				if ( $this->out_type == 'file' )
				{
					$return['thumb_location'] = $this->in_file_name;
					return $return;
				}
				else
				{
					//----------------------------------------------------
					// Show image
					//----------------------------------------------------
					
					$this->show_non_gd();
				}
			}
			
			//----------------------------------------------------
			// Do we need to scale?
			//----------------------------------------------------
			
			if ( ( $img_size[0] > $this->desired_width ) OR ( $img_size[1] > $this->desired_height ) OR $this->fullsize == 1 )
			{ 
				$im = $this->scale_image( array(
												 'max_width'  => $this->desired_width,
												 'max_height' => $this->desired_height,
												 'cur_width'  => $img_size[0],
												 'cur_height' => $img_size[1]
										)      );
									   
				$return['thumb_width']   = $im['img_width'];
				$return['thumb_height']  = $im['img_height'];
				
				//-----------------------------------------------
				// May as well scale properly.
				//-----------------------------------------------
				
				if ( $im['img_width'] )
				{
					$this->desired_width = $im['img_width'];
				}
				
				if ( $im['img_height'] )
				{
					$this->desired_height = $im['img_height'];
				}

				//-----------------------------------------------
				// GD functions available?
				//-----------------------------------------------
				
				if ( $remap[ $img_size[2] ] == 'GIF' )
				{
					if ( function_exists( 'imagecreatefromgif') )
					{
						if ( $image = @imagecreatefromgif( $this->in_file_complete ) )
						{
							$this->image_type = 'gif';
						}
						else
						{
							if ( $this->out_type == 'file' )
							{
								$return['thumb_width']    = $this->desired_width;
								$return['thumb_height']   = $this->desired_height;
								$return['thumb_location'] = $this->in_file_name;
							}
							else
							{
								//-----------------------------------------------
								// Show Image..
								//-----------------------------------------------
								
								$this->show_non_gd();
								
							}
						}
					}
				}
				else if ( $remap[ $img_size[2] ] == 'PNG' )
				{
					if ( function_exists( 'imagecreatefrompng') )
					{
						if ( $image = @imagecreatefrompng( $this->in_file_complete ) )
						{
							$this->image_type = 'png';
						}
						else
						{
							if ( $this->out_type == 'file' )
							{
								$return['thumb_width']    = $this->desired_width;
								$return['thumb_height']   = $this->desired_height;
								$return['thumb_location'] = $this->in_file_name;
							}
							else
							{
								//-----------------------------------------------
								// Show Image..
								//-----------------------------------------------
								
								$this->show_non_gd();
								
							}
						}
					}
				}
				else if ( $remap[ $img_size[2] ] == 'JPG' )
				{
					if ( function_exists( 'imagecreatefromjpeg') )
					{
						if ( $image = @imagecreatefromjpeg( $this->in_file_complete ) )
						{
							$this->image_type = 'jpg';
						}
						else
						{
							if ( $this->out_type == 'file' )
							{
								$return['thumb_width']    = $this->desired_width;
								$return['thumb_height']   = $this->desired_height;
								$return['thumb_location'] = $this->in_file_name;
							}
							else
							{
								//-----------------------------------------------
								// Show Image..
								//-----------------------------------------------
								
								$this->show_non_gd();
								
							}
						}
					}
				}
				
				//----------------------------------------------------
				// Did we get a return from imagecreatefrom?
				//----------------------------------------------------
				
				if ( $image )
				{
					if ( $this->gd_version == 1 )
					{
						$thumb = @imagecreate( $im['img_width'], $im['img_height'] );
						@imagecopyresized( $thumb, $image, 0, 0, 0, 0, $im['img_width'], $im['img_height'], $img_size[0], $img_size[1] );
					}
					else
					{
						$thumb = @imagecreatetruecolor( $im['img_width'], $im['img_height'] );
						@imagecopyresampled($thumb, $image, 0, 0, 0, 0, $im['img_width'], $im['img_height'], $img_size[0], $img_size[1] );
					}
					
					if( $this->do_water && $this->water_path )
					{
						$this->water_path = str_replace( '{root_path}', substr(ROOT_PATH,0,-1), $this->water_path );
						
						if( file_exists( $this->water_path ) )
						{
							$water_sizes = getimagesize( $this->water_path );
							
				            $temp = explode( ".", $this->water_path );
				            $type = strtolower( array_pop( $temp ) );
				            
				            if( $type == 'jpg' || $type == 'jpeg' )
				            {
				                $mark = imagecreatefromjpeg( $this->water_path );
				            }
				            else if( $type == 'gif' )
				            {
				                $mark = imagecreatefromgif( $this->water_path );
				            }
				            else if( $type == 'png' )
				            {
				                $mark = imagecreatefrompng( $this->water_path );
				            }
	
				            imagecopy( $thumb, $mark, intval($im['img_width'] - $water_sizes[0]), 0, 0, 0, $water_sizes[0], $water_sizes[1] );
				            imagedestroy( $mark );
			            }
		            }
					else if( $this->do_copy  )
					{
						$txt 	= $this->cpy_txt;
						if( strstr( $txt, '%s' ) )
						{
							$txt = sprintf( $txt, date("Y") );
						}
						$color  = imagecolorallocate($thumb,255,255,255);
						$maxwidth = $im['img_width'] - 10;
						$this->add_copyright($thumb,3,$txt,$color,$maxwidth,"right","bottom","5");
					}
					
					//-----------------------------------------------
					// Saving?
					//-----------------------------------------------
					
					if ( $this->out_type == 'file' )
					{
						if ( ! $this->out_file_name )
						{
							//-----------------------------------------------
							// Remove file extension...
							//-----------------------------------------------
							
							$this->out_file_name = preg_replace( "/^(.*)\..+?$/", "\\1", $this->in_file_name ) . '_thumb';
						}
						
						if ( function_exists( 'imagepng' ) AND $this->image_type == 'png' )
						{
							$this->file_extension = 'png';
							@imagepng( $thumb, $this->out_file_dir."/".$this->out_file_name.'.png' );
							@chmod( $this->out_file_dir."/".$this->out_file_name.'.png', 0777 );
							@imagedestroy( $thumb );
							@imagedestroy( $image );
							$return['thumb_location'] = $this->out_file_name.'.png';
							return $return;
						}
						else if ( function_exists( 'imagejpeg' ) )
						{
							$this->file_extension = 'jpg';
							@imagejpeg( $thumb, $this->out_file_dir."/".$this->out_file_name.'.jpg' );
							@chmod( $this->out_file_dir."/".$this->out_file_name.'.jpg', 0777 );
							@imagedestroy( $thumb );
							@imagedestroy( $image );
							$return['thumb_location'] = $this->out_file_name.'.jpg';
							return $return;
						}
						else
						{
							//--------------------------------------
							// Can't save...
							//--------------------------------------
							
							$return['thumb_width']    = $this->desired_width;
							$return['thumb_height']   = $this->desired_height;
							$return['thumb_location'] = $this->in_file_name;
							
							return $return;
						}
					}
					else
					{
						//-----------------------------------------------
						// Show image
						//-----------------------------------------------
						
						$this->show_image( $thumb, $this->image_type );
					}
				}
				else
				{
					//----------------------------------------------------
					// Could not GD, return..
					//----------------------------------------------------
					
					if ( $this->out_type == 'file' )
					{
						$return['thumb_width']    = $this->desired_width;
						$return['thumb_height']   = $this->desired_height;
						$return['thumb_location'] = $this->in_file_name;
					}
					else
					{
						//-----------------------------------------------
						// Show Image..
						//-----------------------------------------------
						
						$this->show_non_gd();
						
					}
				
					return $return;
				}
			}
			//----------------------------------------------------
			// No need to scale..
			//----------------------------------------------------
			else
			{ 
				if ( $this->out_type == 'file' )
				{
					$return['thumb_width']    = $img_size[0];
					$return['thumb_height']   = $img_size[1];
					
					return $return;
				}
				else
				{
					//-----------------------------------------------
					// Show Image..
					//-----------------------------------------------
					
					$this->show_non_gd();
					
				}
			}
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Add copyright to thumbnail/screenshot
	/*-------------------------------------------------------------------------*/
		
	function add_copyright($image, $font, $text, $color, $maxwidth, $alignment, $valign, $padding="5")
	{
		$fontwidth = imagefontwidth($font);
		$fontheight = imagefontheight($font);

		$margin = floor($padding + $drop)/2; 

		if ( $maxwidth != NULL )
		{
			$maxcharsperline = floor( ($maxwidth - ($margin * 2)) / $fontwidth);
			$text = wordwrap( $text, $maxcharsperline, "\n", 1 );
		}

		$lines = explode("\n", $text);

		switch( $valign )
		{
			case "center":
				$y = (imagesy($image) - ($fontheight * sizeof($lines)))/2;
				break;

			case "bottom":
				$y = imagesy($image) - (($fontheight * sizeof($lines)) + $margin);
				break;

			default:
				$y = $margin;
				break;
		}
		
		$rect_back = imagecolorallocate( $image, 0,0,0 );
		
		switch( $alignment )
		{
			case "right":
				while (list($numl, $line) = each($lines)) {
					imagefilledrectangle( $image, (imagesx($image) - $fontwidth*strlen($line))-$margin, $y, imagesx($image)-1, imagesy($image)-1, $rect_back );
					imagestring($image, $font, (imagesx($image) - $fontwidth*strlen($line))-$margin, $y, $line, $color);
					$y += $fontheight;
				}
				break;

			case "center":
				while (list($numl, $line) = each($lines)) {
					imagefilledrectangle( $image, floor((imagesx($image) - $fontwidth*strlen($line))/2), $y, imagesx($image), imagesy($image), $rect_back );
					imagestring($image, $font, floor((imagesx($image) - $fontwidth*strlen($line))/2), $y, $line, $color);
					$y += $fontheight;
				}
			break;

			default:
				while (list($numl, $line) = each($lines)) {
					imagefilledrectangle( $image, $margin, $y, imagesx($image), imagesy($image), $rect_back );
					imagestring($image, $font, $margin, $y, $line, $color);
					$y += $fontheight;
				}
			break;
		}
		
		return TRUE;
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Show GD image
	/*-------------------------------------------------------------------------*/
	
	function show_image( $thumb, $type )
	{
		ob_end_clean();
		
		if ( $type == 'gif' )
		{
			@header('Content-type: image/gif');
			@imagegif( $thumb );
		}
		else if ( $type == 'png' )
		{
			@header('Content-Type: image/png' );
			@imagepng( $thumb );
		}
		else
		{
			@header('Content-Type: image/jpeg' );
			@imagejpeg( $thumb );
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Show non GD image
	/*-------------------------------------------------------------------------*/
	
	function show_non_gd()
	{
		$file_extension = preg_replace( "/^(.+?)\.(\w+)$/", "\\2", $this->in_file_name );
		$file_extension = strtolower( $file_extension );
		$file_extension = $file_extension == 'jpeg' ? 'jpg' : $file_extension;
		
		if ( strstr( ' gif jpg png ', ' '.$file_extension.' ' ) )
		{
			if ( $data = @file_get_contents( $this->in_file_complete ) )
			{
				$the_image = @imagecreate( $this->desired_width, $this->desired_height );
				@imagecopyresized( $the_image, $data, 0, 0, 0, 0, $this->desired_width, $this->desired_height, $this->desired_width, $this->desired_height );
				
				$this->show_image( $the_image, $file_extension );
			}
		}
	}
	
	
	/*-------------------------------------------------------------------------*/
	// Return scaled down image
	/*-------------------------------------------------------------------------*/
	
	function scale_image($arg)
	{
		$ret = array(
					  'img_width'  => $arg['cur_width'],
					  'img_height' => $arg['cur_height']
					);
		
		if ( $arg['cur_width'] > $arg['max_width'] )
		{
			$ret['img_width']  = $arg['max_width'];
			$ret['img_height'] = ceil( ( $arg['cur_height'] * ( ( $arg['max_width'] * 100 ) / $arg['cur_width'] ) ) / 100 );
			$arg['cur_height'] = $ret['img_height'];
			$arg['cur_width']  = $ret['img_width'];
		}
		
		if ( $arg['cur_height'] > $arg['max_height'] )
		{
			$ret['img_height']  = $arg['max_height'];
			$ret['img_width']   = ceil( ( $arg['cur_width'] * ( ( $arg['max_height'] * 100 ) / $arg['cur_height'] ) ) / 100 );
		}
		
	
		return $ret;
	}
	
	
}

?>