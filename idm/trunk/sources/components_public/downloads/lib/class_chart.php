<?php

/*
+---------------------------------------------------------------------------
|   Invision Power Dynamic v1.0.0
|   ========================================
|   by Matthew Mecham
|   (c) 2004 Invision Power Services
|   ========================================
+---------------------------------------------------------------------------
|   INVISION POWER DYNAMIC IS NOT FREE SOFTWARE!
+---------------------------------------------------------------------------
|   > $Id$
|   > $Revision: 4 $
|   > $Date: 2005-10-10 14:21:32 +0100 (Mon, 10 Oct 2005) $
+---------------------------------------------------------------------------
|
|   > 2D/3D Pie Chart Generator
|   > Script written by Remco Wilting & Matt Mecham
|   > Date started: Monday 31st January 2005
|
+---------------------------------------------------------------------------
*/

//+---------------------------------------------------------------------------
// USAGE:
// $pie = new class_piechart();
// $pie->piechart_init( array( 'width'  => 600,
//  						   'title'  => 'Pie Chart',
//							   'font'   => 'c:/windows/fonts/arial.ttf' ) );
// $pie->piechart_draw( array( 'slice1' => 58, 'slice2' => 98 ) );
//+---------------------------------------------------------------------------


class class_chart
{
	//-----------------------------------------
	// INIT Vars
	//-----------------------------------------
	
	var $options     = array();
	var $use_ttf     = 0;
	var $pies        = array();
	
	# Holds pre-set colors
	var $colors      = array();
	
	# Holds used colors
	var $used_colors = array( 0 => '0,0,0' );
	
	# Non TTF font sizes
	var $fx       = array(0,5,6,7,8,9);
	var $fy       = array(0,7,8,10,14,11);
	var $fontsize = 3;
	var $black;
	
	/*-------------------------------------------------------------------------*/
	// Initiate a chart, resets variables, etc.
	/*-------------------------------------------------------------------------*/
	
	function chart_init( $options=array() )
	{
		//-----------------------------------------
		// Map to class array
		//-----------------------------------------
		
		$this->options = $options;
		
		$this->options['titlecolor']  = isset($this->options['titlecolor'])  ? $this->options['titlecolor']     : "#000000";
		$this->options['title'] 	  = isset($this->options['title'])       ? $this->options['title']          : "Chart";
		$this->options['titleshadow'] = isset($this->options['titleshadow']) ? $this->options['titleshadow']    : "#AAAAAA";
		$this->options['titlesize']	  = isset($this->options['titlesize'])   ? $this->options['titlesize']      : 16;
		$this->options['width']       = isset($this->options['width'])       ? intval($this->options['width'])  : 600;
		$this->options['height']      = isset($this->options['height'])      ? intval($this->options['height']) : 400;
		$this->options['charttype']   = isset($this->options['charttype'])   ? $this->options['charttype']      : '3D';
		$this->options['bgcolor']     = isset($this->options['bgcolor'])     ? $this->options['bgcolor']        : "#FFFFFF";
		$this->options['textcolor']   = isset($this->options['textcolor'])   ? $this->options['textcolor']      : "#FFFFFF";
		
		//-----------------------------------------
		// Check font
		//-----------------------------------------
		
		if ( $this->options['font'] and is_readable( $this->options['font'] ) )
		{
			$this->use_ttf = 1;
		}
		
		if ( !function_exists('imagettfbbox') )
		{
			// Stewart-recommended fix for users with GD but without FreeType support
			
			$this->use_ttf = 0;
		}
		
		//-----------------------------------------
		// Generate some standard "nice" colours
		//-----------------------------------------
		
		$this->color[] = '80,120,200';
		$this->color[] = '160,80,160';
		$this->color[] = '0,120,80';	
		$this->color[] = '240,160,60';	
		$this->color[] = '40,160,240';	
		$this->color[] = '200,100,100';	
		$this->color[] = '100,200,100';	
		$this->color[] = '240,200,100';	
	}
	
	/*-------------------------------------------------------------------------*/
	// Generate Pie Charts
	/*-------------------------------------------------------------------------*/
	
	function piechart_draw( $data = array() )
	{
		//-----------------------------------------
		// Map data into PIE array
		//-----------------------------------------
		
		if ( is_array( $data ) && count( $data ) > 0 )
		{
			$total  = array_sum( $data );
			$start  = 0;
			$i = 0;
			foreach ( $data as $key => $value )
			{
				$this->pies[] = array( 'start' => $start>360?360:$start,
									   'end'   => $start + round(($value / $total) * 360, 0 ) > 360 ? 360 : $start + round(($value / $total) * 360, 0 ) ,
									   'perc'  => round(($value / $total)*100,1),
									   'name'  => $key );
									   
				$start = $start + round(($value / $total) * 360, 0 );
				$i++;
			}
			$this->pies[$i-1]['end'] = 360;
		}
		
		//-----------------------------------------
		// Draw the slices
		//-----------------------------------------
		
		if ( $this->pies )
		{
			//-----------------------------------------
			// Title TTF
			//-----------------------------------------
			
			if ( $this->use_ttf )
			{
				$txtsize = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$titlesize = $txtsize[1] - $txtsize[5];
			}
			else
			{
				$titlesize = imagefontheight( 5 );
			}
			
			//-----------------------------------------
			// Get true height
			//-----------------------------------------
			
			# the height is depending on the legend size
			$legendx = 0;
			foreach ( $this->pies as $key => $pie )
			{
				if ( $this->use_ttf )
				{
					$txtsize = imagettfbbox("10", 0, $this->options['font'], $pie['name'].' ('.$pie['perc']."%)" );
					$legendx = ($txtsize[2]-$txtsize[0]) > $legendx ?  $txtsize[2]-$txtsize[0]  : $legendx;
				}
				else
				{
					$txtsize = strlen($pie['name'].' ('.$pie['perc']."%)") * imagefontwidth($this->fontsize);
					$legendx = ($txtsize > $legendx) ?  $txtsize : $legendx;
				}
			}
			$legendx = $this->options['width'] - ($legendx + 25);

			if( !$this->options['height'] )
			{
				if ( $this->options['charttype'] == '3D' )
				{
					$this->options['height'] = round( $legendx * 0.5 + 60 + $titlesize, 0 );
				}
				else
				{
					$this->options['height'] = round( $legendx * 0.9 + 40 + $titlesize, 0 );
				}
			}
			
			//-----------------------------------------
			// Start GD process
			//-----------------------------------------
			
			$image = imagecreatetruecolor( $this->options['width'], $this->options['height'] );
			
			if ( function_exists('imageantialias') )
			{
				@imageantialias( $image, TRUE );
			}
			
			//-----------------------------------------
			// Allocate BG color
			//-----------------------------------------
			
			$bgcolor = imagecolorallocate($image, hexdec(substr($this->options['bgcolor'],1,2)), hexdec(substr($this->options['bgcolor'],3,2)), hexdec(substr($this->options['bgcolor'],5,2)));
			
			imagefilledrectangle( $image, 0, 0, $this->options['width'], $this->options['height'], $bgcolor );
			
			//-----------------------------------------
			// Allocate text and shadow cols
			//-----------------------------------------
			
			$textcolor   = imagecolorallocate( $image, hexdec(substr($this->options['titlecolor'],1,2)), hexdec(substr($this->options['titlecolor'],3,2)), hexdec(substr($this->options['titlecolor'],5,2)));
			$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
			
			//-----------------------------------------
			// Generate title w/shadow
			//-----------------------------------------
			
			if ( $this->use_ttf )
			{
				$txtsize     = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$textx       = round($this->options['width']/2,0) - round(($txtsize[2]-$txtsize[0])/2,0);
				$texty       = 20;
		
				imagettftext($image, $this->options['titlesize'], 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $this->options['title']);
				imagettftext($image, $this->options['titlesize'], 0, $textx  , $texty  , $textcolor  , $this->options['font'], $this->options['title']);
			}
			else
			{
				$textx       = round($this->options['width']/2,0) - round((imagefontwidth($this->fontsize)*strlen($this->options['title']))/2,0);
				$texty       = 20 - imagefontheight(5);
				
				imagestring( $image, 5, $textx+1, $texty+1, $this->options['title'], $shadowcolor );
				imagestring( $image, 5, $textx, $texty, $this->options['title'], $textcolor   );
			}
			
			//-----------------------------------------
			// Work out legend position
			//-----------------------------------------
			
			$ci      = 0;
			$legendx = 0;
			$legendy = 0;
			$maxtxty = 10;
			
			foreach ( $this->pies as $key => $pie )
			{
				if ( $this->use_ttf )
				{
					$txtsize = imagettfbbox("10", 0, $this->options['font'], $pie['name'].' ('.$pie['perc']."%)" );
					$legendx = ($txtsize[2]-$txtsize[0]) > $legendx ?  $txtsize[2]-$txtsize[0]  : $legendx;
					$maxtxty = ($txtsize[1]-$txtsize[7]) > $maxtxty ? ($txtsize[1]-$txtsize[7]) : $maxtxty;
				}
				else
				{
					$txtsize = strlen($pie['name'].' ('.$pie['perc']."%)") * imagefontwidth($this->fontsize);
					$legendx = ($txtsize > $legendx) ?  $txtsize : $legendx;
					$maxtxty = imagefontheight($this->fontsize);
				}

				$ci ++;
				
			}				
			$legendx = $this->options['width'] - ($legendx + 25);
			$legendy = round( ($this->options['height'] / 2) - ((($maxtxty+5) * $ci) / 2) );
			
			//-----------------------------------------
			// Do shade
			//-----------------------------------------
				
			$textcolor   = imagecolorallocate( $image, 0, 0, 0 );
			$shadowcolor = imagecolorallocate( $image, 170, 170, 170);
			$ypos        = $legendy;
			$ci          = 0;
			$color       = array();
			
			//-----------------------------------------
			// Draw legends
			//-----------------------------------------
			
			foreach ( $this->pies as $key => $pie )
			{
				//-----------------------------------------
				// Get Pie slice colors
				//-----------------------------------------
				
				$color[ $ci ] = explode( ",", $this->_get_slice_color() );
			
				$piecolor = imagecolorallocate( $image, $color[$ci][0], $color[$ci][1], $color[$ci][2] );
				imagefilledrectangle( $image, $legendx+5, $ypos+7  , $legendx+15 , $ypos+17, $piecolor );
				
				if ( $this->use_ttf )
				{
					$txty = $ypos + 5 + $maxtxty/2;
					imagettftext($image, "10", 0, $legendx+20+1, $txty+5+1, $shadowcolor, $this->options['font'], $pie['name'].' ('.$pie['perc']."%)");
					imagettftext($image, "10", 0, $legendx+20  , $txty+5  , $textcolor  , $this->options['font'], $pie['name'].' ('.$pie['perc']."%)");
				}
				else
				{
					ImageString( $image, $this->fontsize, $legendx+20 , $ypos+7, $pie['name'].' ('.$pie['perc']."%)", $this->black);
				}

				$ypos = $ypos + $maxtxty + 5;
				
				imagecolordeallocate( $image, $piecolor );
				$ci ++;
			}
			
			imagerectangle( $image, $legendx, $legendy, $this->options['width']-1, $ypos + 5, $textcolor );
			
			//-----------------------------------------
			// Slice of pie, anyone?
			//-----------------------------------------
			
			$midx  = round( $legendx / 2, 0);
			$midy  = round( ( $this->options['height'] - $titlesize ) / 2, 0) + $titlesize;
			$sizex = round($legendx / 100 * 90, 0);

			if ( $this->options['charttype'] == '3D' )
			{
				$sizey = round($legendx / 100 * 50, 0);
				
				//-----------------------------------------
				// Make the 3D effect
				//-----------------------------------------
				
				for ( $i = $midy+20; $i > $midy; $i-- )
				{
					$ci = 0;
					
					foreach ( $this->pies as $key => $pie )
					{
						if ( $pie['start'] > 180 )
						{
							# Can't see shadow, so don't bother
							continue;
						}
						
						$shadowcolor = imagecolorallocate( $image, ($color[$ci][0]-50)<0?0:$color[$ci][0]-50, ($color[$ci][1]-50)<0?0:$color[$ci][1]-50, ($color[$ci][2]-50)<0?0:$color[$ci][2]-50 );
						imagefilledarc($image, $midx, $i, $sizex, $sizey, $this->pies[$ci]['start'], $this->pies[$ci]['end'], $shadowcolor, IMG_ARC_PIE);
						imagecolordeallocate( $image, $shadowcolor );
						$ci++;
					}
				}
			}
			else
			{
				$sizey = round($legendx / 100 * 90, 0);
			}
											
			//-----------------------------------------
			// Slice
			//-----------------------------------------
			
			$ci = 0;
			
			foreach ( $this->pies as $key => $pie )
			{
				$piecolor = imagecolorallocate( $image, $color[$ci][0], $color[$ci][1], $color[$ci][2] );
				imagefilledarc($image, $midx, $midy, $sizex, $sizey, $this->pies[$ci]['start'], $this->pies[$ci]['end'], $piecolor, IMG_ARC_PIE);
				imagecolordeallocate( $image, $piecolor );
				$ci++;
			}
			
			//-----------------------------------------
			// text
			//-----------------------------------------
			
			$textcolor  = ImageColorAllocate($image, hexdec(substr($this->options['textcolor'],1,2)), hexdec(substr($this->options['textcolor'],3,2)), hexdec(substr($this->options['textcolor'],5,2)));

			$ci = 0;
			
			foreach ( $this->pies as $key => $pie )
			{
				$textx = $midx + cos(deg2rad($pie['start']+($pie['end']-$pie['start'])/2))*($sizex/3);
				$texty = $midy + sin(deg2rad($pie['start']+($pie['end']-$pie['start'])/2))*($sizey/3);

				$shadowcolor = imagecolorallocate( $image, ($color[$ci][0]-50)<0?0:$color[$ci][0]-50, ($color[$ci][1]-50)<0?0:$color[$ci][1]-50, ($color[$ci][2]-50)<0?0:$color[$ci][2]-50 );

				if ( $this->use_ttf )
				{
					$txtsize     = imagettfbbox("10", 0, $this->options['font'], $pie['perc']."%" );
					$textx       = $textx - round(($txtsize[2]-$txtsize[0])/2,0);
					$texty       = $texty + round(($txtsize[3]-$txtsize[1])/2,0);
					
					imagettftext($image, "10", 0, $textx-1, $texty-1, $shadowcolor, $this->options['font'], $pie['perc']."%");
					imagettftext($image, "10", 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $pie['perc']."%");
					imagettftext($image, "10", 0, $textx+2, $texty+2, $shadowcolor, $this->options['font'], $pie['perc']."%");
					imagettftext($image, "10", 0, $textx, $texty, $textcolor, $this->options['font'], $pie['perc']."%");
				}
				else
				{
					$textx       = $textx - round(imagefontwidth($this->fontsize)*strlen($pie['perc']."%")/2,0);
					$texty       = $texty - round(imagefontheight($this->fontsize)/2,0);
					
					imagestring($image, $this->fontsize, $textx-1, $texty-1, $pie['perc']."%", $shadowcolor);
					imagestring($image, $this->fontsize, $textx+1, $texty+1, $pie['perc']."%", $shadowcolor);
					imagestring($image, $this->fontsize, $textx+2, $texty+2, $pie['perc']."%", $shadowcolor);
					imagestring($image, $this->fontsize, $textx, $texty, $pie['perc']."%", $textcolor);
				}

				imagecolordeallocate( $image, $shadowcolor );
				
				$ci++;
			}
			
			//-----------------------------------------
			// Flush image
			//-----------------------------------------
			
			header('Content-type: image/png');
			imagepng($image);
			imagedestroy($image);
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Generate Bar Charts
	/*-------------------------------------------------------------------------*/
	
	function barchart_draw( $data = array() )
	{
		//-----------------------------------------
		// Draw the bars
		//-----------------------------------------
		
		if ( is_array( $data ) && count( $data ) > 0 )
		{

			//-----------------------------------------
			// Calculate the sizes of the bars
			//-----------------------------------------

			$maxvalue 	= 0;
			$maxdisplay	= "";

			foreach ( $data as $key => $value )
			{
				$value_dis = $value;
				
				if( strpos( strtolower($value), "m" ) )
				{
					$value = intval( $value ) * 1048576;
				}
				else if( strpos( strtolower($value), "k" ) )
				{
					$value = intval( $value ) * 1024;
				}
				else
				{
					$value = intval($value);
				}
							
				if( strnatcmp($value,$maxvalue) > 0 )
				{
					$maxvalue = $value;
					$maxdisplay	= $value_dis;
				}

				$xaxissize = 0;

				if ( $this->use_ttf )
				{
					$textsize = imagettfbbox(10, 45, $this->options['font'], $key );
					$xaxissize = ($textsize[1] - $textsize[5]) > $xaxissize ? $textsize[1] - $textsize[5] : $xaxissize;
				}
				else
				{
					$textsize = imagefontwidth( $this->fontsize ) * strlen( $key );
					$xaxissize = $textsize > $xaxissize ? $textsize : $xaxissize;
				}				
			}
			$numbars = count( $data );
			
			//-----------------------------------------
			// Title and axis sizes
			//-----------------------------------------
			if ( $this->use_ttf )
			{
				$txtsize = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$titlesize = $txtsize[1] - $txtsize[5] + 20;
				$txtsize    = imagettfbbox(10, 0, $this->options['font'], $maxdisplay );
				$yaxissize	= $txtsize[2]-$txtsize[0]+8;
			}
			else
			{
				$titlesize = imagefontheight( 5 ) + 20;
				$yaxissize	= imagefontwidth($this->fontsize)*strlen($maxdisplay)+8;
			}

			$barxsize = round( ( $this->options['width'] * 0.9 - $yaxissize ) / $numbars , 0);
			$barysize = round( $this->options['height'] * 0.9, 0 ) - $titlesize - $xaxissize;

			if ( $this->options['charttype'] == '3D' )
			{
				$barysize = $barysize - 20;
			}
			
			//-----------------------------------------
			// Start GD process
			//-----------------------------------------
			
			$image = imagecreatetruecolor( $this->options['width'], $this->options['height'] );
			
			if ( function_exists('imageantialias') )
			{
				@imageantialias( $image, TRUE );
			}
			
			//-----------------------------------------
			// Allocate BG color
			//-----------------------------------------
			
			$bgcolor = imagecolorallocate($image, hexdec(substr($this->options['bgcolor'],1,2)), hexdec(substr($this->options['bgcolor'],3,2)), hexdec(substr($this->options['bgcolor'],5,2)));
			
			imagefilledrectangle( $image, 0, 0, $this->options['width'], $this->options['height'], $bgcolor );
			
			//-----------------------------------------
			// Allocate text and shadow cols
			//-----------------------------------------
			
			$textcolor   = imagecolorallocate( $image, hexdec(substr($this->options['titlecolor'],1,2)), hexdec(substr($this->options['titlecolor'],3,2)), hexdec(substr($this->options['titlecolor'],5,2)));
			$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
			
			//-----------------------------------------
			// Generate title w/shadow
			//-----------------------------------------
			
			if ( $this->use_ttf )
			{
				$txtsize     = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$textx       = round($this->options['width']/2,0) - round(($txtsize[2]-$txtsize[0])/2,0);
				$texty       = $txtsize[1]-$txtsize[5];
		
				imagettftext($image, $this->options['titlesize'], 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $this->options['title']);
				imagettftext($image, $this->options['titlesize'], 0, $textx  , $texty  , $textcolor  , $this->options['font'], $this->options['title']);
			}
			else
			{
				$textx       = round($this->options['width']/2,0) - round((imagefontwidth($this->fontsize)*strlen($this->options['title']))/2,0);
				$texty       = 20 - imagefontheight(5);
				
				imagestring( $image, 5, $textx+1, $texty+1, $this->options['title'], $shadowcolor );
				imagestring( $image, 5, $textx, $texty, $this->options['title'], $textcolor   );
			}

			//-----------------------------------------
			// axes (and eagles?)
			//-----------------------------------------
			
			$black = imagecolorallocate( $image, 0, 0, 0 );
			$zerox = round( $this->options['width'] * 0.05, 0) + $yaxissize;
			$topx = round( $this->options['width'] * 0.95, 0);
			$zeroy = round( $this->options['height'] * 0.90, 0 ) - $xaxissize;
			$topy = $titlesize + ( $this->options['charttype'] == '3D' ? 20 : 0 );

			imageline( $image, $zerox - 5, $zeroy, $topx, $zeroy, $black );
			imageline( $image, $zerox, $topy, $zerox, $zeroy, $black );
			imageline( $image, $zerox - 5, $topy, $zerox, $topy, $black );
			
			if ( $this->use_ttf )
			{
				$txtsize    = imagettfbbox(10, 0, $this->options['font'], $maxdisplay );
				$textx      = round($this->options['width']*0.05,0);
				$texty		= $topy + round( ( $txtsize[1] - $txtsize[5] ) / 2, 0 ); 
				imagettftext($image, 10, 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $maxdisplay);
				imagettftext($image, 10, 0, $textx  , $texty  , $textcolor  , $this->options['font'], $maxdisplay);

				$txtsize    = imagettfbbox(10, 0, $this->options['font'], 0 );
				$textx      = $zerox - 8 - $txtsize[2]-$txtsize[0];
				$texty		= $zeroy + round( ( $txtsize[1] - $txtsize[5] ) / 2, 0 ); 
				imagettftext($image, 10, 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], 0);
				imagettftext($image, 10, 0, $textx  , $texty  , $textcolor  , $this->options['font'], 0);
			}
			else
			{
				$textx       = round($this->options['width']*0.05,0);
				$texty		= $topy - round( imagefontheight($this->fontsize) / 2, 0 ); 
				imagestring( $image, $this->fontsize, $textx+1, $texty+1, $maxdisplay, $shadowcolor );
				imagestring( $image, $this->fontsize, $textx, $texty, $maxdisplay, $textcolor   );

				$textx      = $zerox - 8 - imagefontwidth($this->fontsize);
				$texty		= $zeroy - round( imagefontheight($this->fontsize) / 2, 0 ); 
				imagestring( $image, $this->fontsize, $textx+1, $texty+1, 0, $shadowcolor );
				imagestring( $image, $this->fontsize, $textx, $texty, 0, $textcolor   );
			}

			//-----------------------------------------
			// Candybar?
			//-----------------------------------------

			$i = 0;

			foreach ( $data as $key => $value )
			{
				//-----------------------------------------
				// Find out the bar location and size
				//-----------------------------------------
				
				$value_dis = $value;
				
				if( strpos( strtolower($value), "m" ) )
				{
					$value = intval( $value ) * 1048576;
				}
				else if( strpos( strtolower($value), "k" ) )
				{
					$value = intval( $value ) * 1024;
				}
				else
				{
					$value = intval($value);
				}

				$x1 = $zerox + round( $barxsize * 0.05, 0 ) + $i * ( $barxsize ) + ( $this->options['charttype'] == '3D' ? 10 : 0 );
				$x2 = $x1 + round( $barxsize * 0.9 - ( $this->options['charttype'] == '3D' ? 20 : 0 ) , 0);
				$y1 = $zeroy - round( ($value / $maxvalue) * $barysize, 0);
				$y2 = $zeroy;

				$endx = $zerox + ($i + 1) * $barxsize;
				imageline( $image, $endx, $y2-5, $endx, $y2, $black );
				
				//-----------------------------------------
				// Get me a nice color will ya				
				//-----------------------------------------
				
				$color[ $i ] = explode( ",", $this->_get_slice_color() );
				
				if ( $this->options['charttype'] == '3D' )
				{
				
					//-----------------------------------------
					// Make the 3D effect
					//-----------------------------------------
				
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );
					$shadowsize = ($x2-$x1)/2 > 20 ? 20 : Round(($x2-$x1)/2, 0);
					for ( $j = $shadowsize; $j > 0; $j-- )
					{
						imageline( $image, $x2 + $j, $y1 - $j, $x2 + $j, $y2 - $j, $shadowcolor );
					}
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-25)<0?0:$color[$i][0]-25, ($color[$i][1]-25)<0?0:$color[$i][1]-25, ($color[$i][2]-25)<0?0:$color[$i][2]-25 );
					for ( $j = $shadowsize; $j > 0; $j-- )
					{
						imageline( $image, $x1 + $j, $y1 - $j, $x2 + $j, $y1 - $j, $shadowcolor );
					}
					imagecolordeallocate( $image, $shadowcolor );
				}
											
				//-----------------------------------------
				// Bar
				//-----------------------------------------
			
				$barcolor = imagecolorallocate( $image, $color[$i][0], $color[$i][1], $color[$i][2] );
				imagefilledrectangle( $image, $x1, $y1, $x2, $y2, $barcolor );
				imagecolordeallocate( $image, $barcolor );
				
				//-----------------------------------------
				// text
				//-----------------------------------------
			
				$textcolor  = ImageColorAllocate($image, hexdec(substr($this->options['textcolor'],1,2)), hexdec(substr($this->options['textcolor'],3,2)), hexdec(substr($this->options['textcolor'],5,2)));
	
				$textx = $x1 + round( ($x2-$x1) /2, 0);
				$texty = $y1 + round( ($y2-$y1) /2, 0);
	
				if ( $this->use_ttf )
				{
					$txtsize     = imagettfbbox("10", 0, $this->options['font'], $value_dis );
					$textx       = $textx - round(($txtsize[2]-$txtsize[0])/2,0);
					$texty       = $texty + round(($txtsize[1]-$txtsize[5])/2,0);
					$texty		 = ($texty > $zeroy-2) ? $zeroy-2 : $texty;
					
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );
					imagettftext($image, "10", 0, $textx-1, $texty-1, $shadowcolor, $this->options['font'], $value_dis);
					imagettftext($image, "10", 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $value_dis);
					imagettftext($image, "10", 0, $textx+2, $texty+2, $shadowcolor, $this->options['font'], $value_dis);
					imagettftext($image, "10", 0, $textx, $texty, $textcolor, $this->options['font'], $value_dis);
	
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
					$textsize = imagettfbbox(10, 45, $this->options['font'], $key );
					$textx = $x1 + round( ($x2-$x1) /2, 0);
					$textx       = $textx - $textsize[4];
					$texty       = $zeroy + $textsize[1]-$textsize[5];
					imagettftext($image, "10", 45, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $key);
					imagettftext($image, "10", 45, $textx, $texty, $black, $this->options['font'], $key);
					imagecolordeallocate( $image, $shadowcolor );
				}
				else
				{
					$textx       = $textx - round(imagefontwidth($this->fontsize)*strlen($value_dis)/2,0);
					$texty       = $texty - round(imagefontheight($this->fontsize)/2,0);
					$texty		 = ($texty > $zeroy-imagefontheight($this->fontsize)-2) ? $zeroy-imagefontheight($this->fontsize)-2 : $texty;
					
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );
					imagestring($image, $this->fontsize, $textx-1, $texty-1, $value_dis, $shadowcolor);
					imagestring($image, $this->fontsize, $textx+1, $texty+1, $value_dis, $shadowcolor);
					imagestring($image, $this->fontsize, $textx+2, $texty+2, $value_dis, $shadowcolor);
					imagestring($image, $this->fontsize, $textx, $texty, $value_dis, $textcolor);
	
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
					$textx = $x1 + round( ($x2-$x1) /2, 0);
					$textx       = $textx - imagefontheight($this->fontsize);
					$texty       = $zeroy + 2 + imagefontwidth($this->fontsize)*strlen($key);
					imagestringup($image, $this->fontsize, $textx+1, $texty+1, $key, $shadowcolor);
					imagestringup($image, $this->fontsize, $textx, $texty, $key, $black);
					imagecolordeallocate( $image, $shadowcolor );
				}
			
				$i++;
			}

			//-----------------------------------------
			// Flush image
			//-----------------------------------------
			
			header('Content-type: image/png');
			imagepng($image);
			imagedestroy($image);
		}
	}

	/*-------------------------------------------------------------------------*/
	// Generate Horizontal Bar Charts
	/*-------------------------------------------------------------------------*/
	
	function hbarchart_draw( $data = array() )
	{
		//-----------------------------------------
		// Draw the bars
		//-----------------------------------------
		
		if ( is_array( $data ) && count( $data ) > 0 )
		{

			//-----------------------------------------
			// Calculate the sizes of the bars
			//-----------------------------------------

			$maxvalue = 0;
			foreach ( $data as $key => $value )
			{
				$maxvalue = ($value > $maxvalue) ? $value : $maxvalue;

				$yaxissize = 0;
				if ( $this->use_ttf )
				{
					$textsize = imagettfbbox(10, 0, $this->options['font'], $key );
					$yaxissize = ($textsize[2] - $textsize[0]) > $yaxissize ? $textsize[2] - $textsize[0] : $yaxissize;
				}
				else
				{
					$textsize = imagefontwidth($this->fontsize)*strlen($key);
					$yaxissize = $textsize > $yaxissize ? $textsize : $yaxissize;
				}
				$yaxissize += round( 0.025 * $this->options['width'] );			
			}
			$numbars = count( $data );

			//-----------------------------------------
			// Title and axis sizes
			//-----------------------------------------
			
			if ( $this->use_ttf )
			{
				$txtsize = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$titlesize = $txtsize[1] - $txtsize[5] + 20;
				$txtsize    = imagettfbbox(10, 0, $this->options['font'], $maxvalue );
				$xaxissize	= $txtsize[1]-$txtsize[5]+8;
			}
			else
			{
				$titlesize = imagefontheight( 5 ) + 20;
				$xaxissize	= imagefontheight($this->fontsize)+8;
			}

			$barxsize = round( ( $this->options['width'] * 0.9 - $yaxissize ), 0);
			$barysize = round( ( $this->options['height'] * 0.9 - $titlesize - $xaxissize) / $numbars, 0 );

			if ( $this->options['charttype'] == '3D' )
			{
				$barxsize = $barxsize - 20;
			}
			
			//-----------------------------------------
			// Start GD process
			//-----------------------------------------
			
			$image = imagecreatetruecolor( $this->options['width'], $this->options['height'] );
			
			if ( function_exists('imageantialias') )
			{
				@imageantialias( $image, TRUE );
			}
			
			//-----------------------------------------
			// Allocate BG color
			//-----------------------------------------
			
			$bgcolor = imagecolorallocate($image, hexdec(substr($this->options['bgcolor'],1,2)), hexdec(substr($this->options['bgcolor'],3,2)), hexdec(substr($this->options['bgcolor'],5,2)));
			
			imagefilledrectangle( $image, 0, 0, $this->options['width'], $this->options['height'], $bgcolor );
			
			//-----------------------------------------
			// Allocate text and shadow cols
			//-----------------------------------------
			
			$textcolor   = imagecolorallocate( $image, hexdec(substr($this->options['titlecolor'],1,2)), hexdec(substr($this->options['titlecolor'],3,2)), hexdec(substr($this->options['titlecolor'],5,2)));
			$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
			
			//-----------------------------------------
			// Generate title w/shadow
			//-----------------------------------------
			
			if ( $this->use_ttf )
			{
				$txtsize     = imagettfbbox($this->options['titlesize'], 0, $this->options['font'], $this->options['title'] );
				$textx       = round($this->options['width']/2,0) - round(($txtsize[2]-$txtsize[0])/2,0);
				$texty       = $txtsize[1]-$txtsize[5];
		
				imagettftext($image, $this->options['titlesize'], 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $this->options['title']);
				imagettftext($image, $this->options['titlesize'], 0, $textx  , $texty  , $textcolor  , $this->options['font'], $this->options['title']);
			}
			else
			{
				$textx       = round($this->options['width']/2,0) - round((imagefontwidth($this->fontsize)*strlen($this->options['title']))/2,0);
				$texty       = 20 - imagefontheight(5);
				
				imagestring( $image, 5, $textx+1, $texty+1, $this->options['title'], $shadowcolor );
				imagestring( $image, 5, $textx, $texty, $this->options['title'], $textcolor   );
			}

			//-----------------------------------------
			// axes (and eagles?)
			//-----------------------------------------
			
			$black = imagecolorallocate( $image, 0, 0, 0 );
			$zerox = round( $this->options['width'] * 0.05, 0) + $yaxissize;
			$topx = round( $this->options['width'] * 0.95, 0);
			$zeroy = round( $this->options['height'] * 0.90, 0 ) - $xaxissize;
			$topy = $titlesize;

			imageline( $image, $zerox, $zeroy, $topx, $zeroy, $black );
			imageline( $image, $zerox, $topy, $zerox, $zeroy+5, $black );
			imageline( $image, $topx, $zeroy, $topx, $zeroy+5, $black );
			
			if ( $this->use_ttf )
			{
				$txtsize    = imagettfbbox(10, 0, $this->options['font'], $maxvalue );
				$textx      = $topx - round( ( $txtsize[2] - $txtsize[0] ) / 2, 0);
				$texty		= $zeroy + $txtsize[1] - $txtsize[5] + 6; 
				imagettftext($image, 10, 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $maxvalue);
				imagettftext($image, 10, 0, $textx  , $texty  , $textcolor  , $this->options['font'], $maxvalue);

				$txtsize    = imagettfbbox(10, 0, $this->options['font'], 0 );
				$textx      = $zerox - round( ( $txtsize[2] - $txtsize[0] ) / 2, 0);
				$texty		= $zeroy + $txtsize[1] - $txtsize[5] + 6; 
				imagettftext($image, 10, 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], 0);
				imagettftext($image, 10, 0, $textx  , $texty  , $textcolor  , $this->options['font'], 0);
			}
			else
			{
				$textx       = $topx - round( imagefontwidth($this->fontsize)*strlen($maxvalue) / 2);
				$texty		= $zeroy + 5 ; 
				imagestring( $image, $this->fontsize, $textx+1, $texty+1, $maxvalue, $shadowcolor );
				imagestring( $image, $this->fontsize, $textx, $texty, $maxvalue, $textcolor   );

				$textx      = $zerox - round(imagefontwidth($this->fontsize)/2,0);
				$texty		= $zeroy + 5; 
				imagestring( $image, $this->fontsize, $textx+1, $texty+1, 0, $shadowcolor );
				imagestring( $image, $this->fontsize, $textx, $texty, 0, $textcolor   );
			}

			//-----------------------------------------
			// Candybar?
			//-----------------------------------------

			$i = 0;

			foreach ( $data as $key => $value )
			{
				//-----------------------------------------
				// Find out the bar location and size
				//-----------------------------------------

				$x1 = $zerox;
				$x2 = $zerox + round( ($value / $maxvalue) * $barxsize, 0);
				$y1 = $topy + round( $barysize * 0.05, 0 ) + $i * ( $barysize ) + ( $this->options['charttype'] == '3D' ? 10 : 0 );
				$y2 = $y1 + round( $barysize * 0.9 - ( $this->options['charttype'] == '3D' ? 20 : 0 ) , 0);

				$endy = $topy + $i * $barysize;
				imageline( $image, $zerox-5, $endy, $zerox, $endy, $black );
				
				//-----------------------------------------
				// Get me a nice color will ya				
				//-----------------------------------------
				
				$color[ $i ] = explode( ",", $this->_get_slice_color() );
				
				//-----------------------------------------
				// Bar
				//-----------------------------------------
			
				$barcolor = imagecolorallocate( $image, $color[$i][0], $color[$i][1], $color[$i][2] );
				imagefilledrectangle( $image, $x1, $y1, $x2, $y2, $barcolor );
				imagecolordeallocate( $image, $barcolor );

				if ( $this->options['charttype'] == '3D' )
				{
				
					//-----------------------------------------
					// Make the 3D effect
					//-----------------------------------------
				
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );
					$shadowsize = ($y2-$y1)/2 > 20 ? 20 : Round(($y2-$y1)/2, 0);
					for ( $j = $shadowsize; $j > 0; $j-- )
					{
						imageline( $image, $x2 + $j, $y1 - $j, $x2 + $j, $y2 - $j, $shadowcolor );
					}
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-25)<0?0:$color[$i][0]-25, ($color[$i][1]-25)<0?0:$color[$i][1]-25, ($color[$i][2]-25)<0?0:$color[$i][2]-25 );
					for ( $j = $shadowsize; $j > 0; $j-- )
					{
						imageline( $image, $x1 + $j, $y1 - $j, $x2 + $j, $y1 - $j, $shadowcolor );
					}
					imagecolordeallocate( $image, $shadowcolor );
				}
				
				//-----------------------------------------
				// text
				//-----------------------------------------
			
				$textcolor  = ImageColorAllocate($image, hexdec(substr($this->options['textcolor'],1,2)), hexdec(substr($this->options['textcolor'],3,2)), hexdec(substr($this->options['textcolor'],5,2)));

				$textx = $x1 + round( ($x2-$x1) /2, 0);
				$texty = $y1 + ( $y2 - $y1 ) / 2;

				if ( $this->use_ttf )
				{
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );
					$txtsize     = imagettfbbox("10", 0, $this->options['font'], $value );
					$textx       = $textx - round(($txtsize[2]-$txtsize[0])/2,0);
					$textx		 = ($textx < $zerox+2)?$zerox+2:$textx;
					$texty       = $texty + round(($txtsize[1]-$txtsize[7])/2, 0);
					
					imagettftext($image, "10", 0, $textx-1, $texty-1, $shadowcolor, $this->options['font'], $value);
					imagettftext($image, "10", 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $value);
					imagettftext($image, "10", 0, $textx+2, $texty+2, $shadowcolor, $this->options['font'], $value);
					imagettftext($image, "10", 0, $textx, $texty, $textcolor, $this->options['font'], $value);
	
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
					$textsize = imagettfbbox(10, 0, $this->options['font'], $key );
					$textx		 = $zerox - ($textsize[2] - $textsize[0]) - 5;
					$texty       = $y1 + round( (($y2 - $y1) / 2) + ( $textsize[1]-$textsize[5] ) / 2, 0);
					imagettftext($image, "10", 0, $textx+1, $texty+1, $shadowcolor, $this->options['font'], $key);
					imagettftext($image, "10", 0, $textx, $texty, $black, $this->options['font'], $key);
					imagecolordeallocate( $image, $shadowcolor );

				}
				else
				{
					$shadowcolor = imagecolorallocate( $image, ($color[$i][0]-50)<0?0:$color[$i][0]-50, ($color[$i][1]-50)<0?0:$color[$i][1]-50, ($color[$i][2]-50)<0?0:$color[$i][2]-50 );

					$textx       = $textx - round(imagefontwidth($this->fontsize)*strlen($value)/2,0);
					$textx		 = ($textx < $zerox+2)?$zerox+2:$textx;
					$texty       = $texty - round(imagefontheight($this->fontsize)/2,0);
					
					imagestring($image, $this->fontsize, $textx-1, $texty-1, $value, $shadowcolor);
					imagestring($image, $this->fontsize, $textx+1, $texty+1, $value, $shadowcolor);
					imagestring($image, $this->fontsize, $textx+2, $texty+2, $value, $shadowcolor);
					imagestring($image, $this->fontsize, $textx, $texty, $value, $textcolor);
	
					imagecolordeallocate( $image, $shadowcolor );

					$shadowcolor = imagecolorallocate( $image, hexdec(substr($this->options['titleshadow'],1,2)), hexdec(substr($this->options['titleshadow'],3,2)), hexdec(substr($this->options['titleshadow'],5,2)));
					$textx       = $zerox - imagefontwidth($this->fontsize)*strlen($key) - 5;
					$texty       = $y1 + round( (($y2 - $y1) / 2) - imagefontheight($this->fontsize) / 2, 0);
					imagestring($image, $this->fontsize, $textx+1, $texty+1, $key, $shadowcolor);
					imagestring($image, $this->fontsize, $textx, $texty, $key, $black);
					imagecolordeallocate( $image, $shadowcolor );
				}
			
				$i++;
			}

			//-----------------------------------------
			// Flush image
			//-----------------------------------------
			
			header('Content-type: image/png');
			imagepng($image);
			imagedestroy($image);
		}
	}

	/*-------------------------------------------------------------------------*/
	// Get a random color for the slice
	/*-------------------------------------------------------------------------*/
	
	function _get_slice_color()
	{
		# Remove 0,0,0 from count
		$used_count = count( $this->used_colors ) - 1;
		$return     = "";
		
		# Used all std cols
		if ( $used_count < 8 )
		{
			$this->used_colors[] = $this->color[ $used_count ];
			$return = $this->color[ $used_count ];
		}
		else
		{
			# 0-12 for each RGB bit == 1728 poss col. combinations
		   for ( $i = 0 ; $i <= 1728 ; $i++ )
		   {
			   $r = rand( 0, 12 ) * 20;
			   $g = rand( 0, 12 ) * 20;
			   $b = rand( 0, 12 ) * 20;
			   
			   $return = "$r,$g,$b";
			   
			   if ( ! in_array( $return, $this->used_colors ) )
			   {
				   $this->used_colors[] = $return;
				   break;
			   }
			   else
			   {
				   continue;
			   }
		   }
		}
		
		return $return;
	}

}

?>