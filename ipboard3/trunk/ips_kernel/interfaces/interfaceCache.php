<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * This class acts as a cache layer, allowing you to store and retrieve data in
 *	external cache sources such as memcache or APC
 * Last Updated: $Date: 2010-01-15 10:18:25 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Kernel
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 370 $
 *
 */

interface interfaceCache
{
    /**
	* Disconnect from remote cache store
	*
	* @return	boolean		Disconnect successful
	*/
	public function disconnect();
	
    /**
	* Put data into remote cache store
	*
	* @param	string		Cache unique key
	* @param	string		Cache value to add
	* @param	integer		[Optional] Time to live
	* @return	boolean		Cache update successful
	*/
	public function putInCache( $key, $value, $ttl=0 );
	
    /**
	* Update value in remote cache store
	*
	* @param	string		Cache unique key
	* @param	string		Cache value to set
	* @param	integer		[Optional] Time to live
	* @return	boolean		Cache update successful
	*/
	public function updateInCache( $key, $value, $ttl=0 );
	
    /**
	* Retrieve a value from remote cache store
	*
	* @param	string		Cache unique key
	* @return	mixed		Cached value
	*/
	public function getFromCache( $key );
	
    /**
	* Remove a value in the remote cache store
	*
	* @param	string		Cache unique key
	* @return	boolean		Cache removal successful
	*/
	public function removeFromCache( $key );

}

?>