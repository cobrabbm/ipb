<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Forum permissions mappings
 * Last Updated: $Date: 2010-04-26 14:40:44 -0400 (Mon, 26 Apr 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6179 $ 
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Member Synchronization extensions
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage  Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6179 $ 
 **/
class forumsMemberSync
{
	/**
	 * Registry reference
	 *
	 * @access	public
	 * @var		object
	 */
	public $registry;
	
	/**
	 * CONSTRUCTOR
	 *
	 * @access	public
	 * @return	void
	 **/
	public function __construct()
	{
		$this->registry = ipsRegistry::instance();
	}
	
	/**
	 * This method is run when a member is flagged as a spammer
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onSetAsSpammer( $member )
	{

	}
	
	/**
	 * This method is run when a member is un-flagged as a spammer
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onUnSetAsSpammer( $member )
	{

	}
	
	/**
	 * This method is run when a new account is created
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onCreateAccount( $member )
	{

	}
	
	/**
	 * This method is run when the register form is displayed to a user
	 *
	 * @access	public
	 * @return	void
	 **/
	public function onRegisterForm()
	{

	}
	
	/**
	 * This method is ren when a user successfully logs in
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onLogin( $member )
	{

	}
	
	/**
	 * This method is called after a member account has been removed
	 *
	 * @access	public
	 * @param	string	$ids	SQL IN() clause
	 * @return	void
	 **/
	public function onDelete( $mids )
	{

	}
	
	/**
	 * This method is called after a member's account has been merged into another member's account
	 *
	 * @access	public
	 * @param	array	$member		Member account being kept
	 * @param	array	$member2	Member account being removed
	 * @return	void
	 **/
	public function onMerge( $member, $member2 )
	{

	}
	
	/**
	 * This method is run after a users email address is successfully changed
	 *
	 * @param  integer  $id         Member ID
	 * @param  string   $new_email  New email address
	 * @param  string	$old_email	Old email address
	 * @return void
	 **/
	public function onEmailChange( $id, $new_email, $old_email )
	{

	}
	
	/**
	 * This method is run after a users password is successfully changed
	 *
	 * @access	public
	 * @param	integer	$id						Member ID
	 * @param	string	$new_plain_text_pass	The new password
	 * @return	void
	 **/
	public function onPassChange( $id, $new_plain_text_pass )
	{

	}
	
	/**
	 * This method is run after a users profile is successfully updated
	 * $member will contain EITHER 'member_id' OR 'email' depending on what data was passed to
	 * IPSMember::save().
	 *
	 * @access	public
	 * @param	array 	$member		Array of values that were changed
	 * @return	void
	 **/
	public function onProfileUpdate( $member )
	{

	}
	
	/**
	 * This method is run after a users group is successfully changed
	 *
	 * @access	public
	 * @param	integer	$id			Member ID
	 * @param	integer	$new_group	New Group ID
	 * @param	integer	$old_group	Old Group ID
	 * @return	void
	 **/
	public function onGroupChange( $id, $new_group, $old_group )
	{

	}
	
	/**
	 * This method is run after a users display name is successfully changed
	 *
	 * @access	public
	 * @param	integer	$id			Member ID
	 * @param	string	$new_name	New display name
	 * @return	void
	 **/
	public function onNameChange( $id, $new_name )
	{

	}
	
	/**
	 * This method is run when a user logs out
	 *
	 * @access	public
	 * @param	array 	$member	Array of member data
	 * @return	void
	 **/
	public function onLogOut( $member )
	{
		
	}
}