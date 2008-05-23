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
|   > $Revision: 55 $
|   > $Date: 2005-11-23 17:34:04 +0000 (Wed, 23 Nov 2005) $
+---------------------------------------------------------------------------
|
|   > TRAFFIC FUNCTIONS
|   > Script written by Matt Mecham
|   > Date started: 12:12 Friday 28th January 2005
|
+---------------------------------------------------------------------------
*/


class lib_traffic
{
	# Global
	var $ipsclass;
	var $root_path;
	
	var $BROWSERS;
	var $OS;
	var $COUNTRIES;
	var $LEGACY;
	
	var $stored_ips = array();
	
	/*-------------------------------------------------------------------------*/
	// Constructor
	/*-------------------------------------------------------------------------*/

	function lib_traffic( $root_path="" )
	{
		# Task manager goes nuts
		if ( $root_path )
		{
			$this->root_path = $root_path;
		}
		else if ( DL_PATH )
		{
			$this->root_path = DL_PATH;
		}
		else
		{
			$this->root_path = "./";
		}
	}
	
	/*-------------------------------------------------------------------------*/
	// Loads libs
	/*-------------------------------------------------------------------------*/
	
	function load_libraries()
	{
		require_once( $this->root_path.'lib/lib_traffic_os.php' );
		require_once( $this->root_path.'lib/lib_traffic_browsers.php' );
		require_once( $this->root_path.'lib/lib_traffic_countries.php' );
		
		$this->BROWSERS    = $BROWSERS;
		$this->OS          = $OS;
		$this->COUNTRIES   = $COUNTRIES;
		$this->COUNTRYLANG = $COUNTRYLANG;
		
		unset( $BROWSERS );
		unset( $OS );
		unset( $COUNTRIES );
		unset( $COUNTYLANG );
		
		$this->KNOWNISP = array( 'cable.ntl.com'      => 'uk',
								 '.btcentralplus.com' => 'uk',
								 '.server.ntli.net'   => 'uk',
								 '.verizon.net'       => 'us' );
		
		$this->LEGACY   = array( "ac", "aero", "ag", "arpa", "as", "biz", "cc", "cd", "com", "coop", "cx", "edu", "eu", "gb", "gov", "gs", "info",
    						     "int", "mil", "ms", "museum", "name", "net", "nu", "org", "pro", "sc", "st", "su", "tk", "to", "tv", "vu", "ws" );
	}
	
	/*-------------------------------------------------------------------------*/
	// Get icon image
	/*-------------------------------------------------------------------------*/
	
	function get_item_image( $type, $title )
	{
		if ( ! array( $this->BROWSERS ) or ! count( $this->BROWSERS ) )
		{
			$this->load_libraries();
		}
		
		switch( $type )
		{
			case 'browsers':
				return 'browser_'. $this->BROWSERS[$title]['b_icon'] .'.png';
				break;
			case 'os':
				return 'os_'     . $this->OS[$title]['b_icon']       .'.png';
				break;
			case 'countrylang':
				return $this->COUNTRYLANG[$title];
				break;
		}
	}
	

	
	/*-------------------------------------------------------------------------*/
	// Return the important stat data for insert/chart display
	/*-------------------------------------------------------------------------*/
	
	function return_stat_data( $raw_data )
	{
		$log_entry = array();
		
		//-----------------------------------------
		// Get robot, browser, OS
		//-----------------------------------------
		
		$tmp = $this->_get_browser_and_os( $raw_data );
		
		$log_entry['stat_browser']      = $tmp['stat_browser'];
		$log_entry['stat_browsers']     = $tmp['stat_browser'];
		$log_entry['stat_browser_key']  = $tmp['stat_browser_key'];
		$log_entry['stat_browsers_key'] = $tmp['stat_browser_key'];
		$log_entry['stat_os']           = $tmp['stat_os'];
		$log_entry['stat_os_key']       = $tmp['stat_os_key'];
		$log_entry['stat_ip_address']   = $raw_data['log_ip_address'];
		
		//-----------------------------------------
		// Get country
		//-----------------------------------------
		
		if ( $raw_data['log_ip_address'] == '127.0.0.1' )
		{
			$log_entry['stat_country'] = 'localhost';
		}
		else
		{
			$hostname = @gethostbyaddr( $raw_data['dip'] );
			$host_ext = strtolower( substr( $hostname, (strrpos($hostname, ".") + 1) ) );
			
			if ( in_array( $host_ext, $this->COUNTRIES ) )
			{
				$log_entry['stat_country'] = $host_ext;
			}
			
			//-----------------------------------------
			// OK, look up in DB
			//-----------------------------------------
			
			if ( ! $log_entry['stat_country'] )
			{
				$long_ip_address = sprintf("%u", ip2long($raw_data['dip']));
				
				//-----------------------------------------
				// Got this IP already?
				//-----------------------------------------
				
				if ( $this->stored_ips[ $long_ip_address ] )
				{
					$log_entry['stat_country'] = $this->stored_ips[ $long_ip_address ];
				}
				else
				{
					$result = $this->ipsclass->DB->simple_exec_query( array( 'select' => 'country',
																			 'from'   => 'downloads_ip2ext',
																			 'where'  => 'ip < '.$long_ip_address,
																			 'order'  => 'ip desc',
																			 'limit'  => array( 0, 1 )
																	)      );
																
					if ( $result['country'] )
					{
						$log_entry['stat_country']            = $result['country'];
						$this->stored_ips[ $long_ip_address ] = $result['country'];
					}
				}
			}
			
			//-----------------------------------------
			// OK, try ISP extension
			//-----------------------------------------
			
			if ( ! $log_entry['stat_country'] )
			{
				if ( in_array( $host_ext, $this->LEGACY ) )
				{
					$log_entry['stat_country'] = $host_ext;
				}
				else
				{
					$log_entry['stat_country'] = 'unknown';
				}
			}
		}
		
		//-----------------------------------------
		// Others...
		//-----------------------------------------
		
		$log_entry['stat_date'] 	= $raw_data['dtime'];
		$log_entry['stat_file'] 	= $raw_data['dfid'];
		$log_entry['stat_filesize'] = $raw_data['dsize'];
		$log_entry['stat_member'] 	= $raw_data['dmid'];
		
		return $log_entry;
	}
		
	

	/*-------------------------------------------------------------------------*/
	// Returns browser and OS info
	/*-------------------------------------------------------------------------*/
	
	function _get_browser_and_os( $raw_data )
	{
		$return   = array();
		
		//-----------------------------------------
		// Check for browser
		//-----------------------------------------
		
		foreach( $this->BROWSERS as $title => $array )
		{
			foreach( $array['b_regex'] as $left => $right )
			{
				if ( ! eregi( "$left", $raw_data['dua'], $matches ) )
				{
					continue;
				}
				else
				{
					//-----------------------------------------
					// Okay, we got a match - finalize
					//-----------------------------------------
					
					if ( preg_match( "/\\\\[0-9]{1}/", $right ) )
					{
						 $version = ' '.preg_replace(":\\\\([0-9]{1}):e", "\$matches[\\1]", $right);
					}
					else
					{
						$version = "";
					}
				
					$return['stat_browser']      = $array['b_title'].stripslashes($version);
					$return['stat_browser_key']  = $title;
					break 2;
				}
			}
		}
		
		//-----------------------------------------
		// Check for OS
		//-----------------------------------------
		
		foreach( $this->OS as $title => $array )
		{
			foreach( $array['b_regex'] as $left => $right )
			{
				if ( ! eregi( "$left", $raw_data['dua'], $matches ) )
				{
					continue;
				}
				else
				{
					//-----------------------------------------
					// Okay, we got a match - finalize
					//-----------------------------------------
					
					if ( preg_match( "/\\\\[0-9]{1}/", $right ) )
					{
						 $version = ' '.preg_replace(":\\\\([0-9]{1}):e", "\$matches[\\1]", $right);
					}
					else
					{
						$version = "";
					}
					
					$return['stat_os']      = $array['b_title'].stripslashes($version);
					$return['stat_os_key']  = $title;
					break 2;
				}
			}
		}
		
		return $return;
	}


}
?>