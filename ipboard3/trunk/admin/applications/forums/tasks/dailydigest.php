<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Task: Send the daily digest emails
 * Last Updated: $LastChangedDate: 2010-03-29 08:11:02 -0400 (Mon, 29 Mar 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 6023 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	* Parent task manager class
	*
	* @access	protected
	* @var		object
	*/
	protected $class;

	/**
	* This task data
	*
	* @access	protected
	* @var		array
	*/
	protected $task			= array();

	/**
	* Prevent logging
	*
	* @access	protected
	* @var		boolean
	*/
	protected $restrict_log	= false;
	
	/**
	* Registry Object Shortcuts
	*/
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	
	/**
	* Constructor
	*
	* @access	public
	* @param 	object		ipsRegistry reference
	* @param 	object		Parent task class
	* @param	array 		This task data
	* @return	void
	*/
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	=  $registry;
		$this->DB		=  $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		=  $this->registry->getClass('class_localization');
		$this->member	=  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	=  $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	* Run this task
	*
	* @access	public
	* @return	void
	*/
	public function runTask()
	{
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
		
		//-----------------------------------------
		// Send daily digests...
		//-----------------------------------------
		
		require ( IPSLib::getAppDir( 'forums' ) . '/sources/classes/digest.php' );
		$digest           = new digestLibrary( $this->registry );
		$digest->digest_time	= 'daily';
		$digest->digest_type	= 'topic';
		$digest->runDigest();
		
		$digest->digest_time	= 'daily';
		$digest->digest_type	= 'forum';
		$digest->runDigest();
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, $this->lang->words['task_dailydigest'] );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}

}