<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Registration AJAX routines
 * Last Updated: $Date: 2010-06-04 21:14:40 -0400 (Fri, 04 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Who knows...
 * @version		$Revision: 6465 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_ajax_register extends ipsAjaxCommand 
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
    	switch( ipsRegistry::$request['do'] )
    	{
			case 'check-display-name':
    			$this->checkDisplayName( 'members_display_name' );
    			break;
			case 'check-user-name':
    			$this->checkDisplayName( 'name' );
    			break;
    		case 'check-email-address':
    			$this->checkEmail();
    			break;
    	}
	}
	
	/**
	 * Check the email address
	 *
	 * @access	public
	 * @return	void		[Outputs to screen]
	 */
	public function checkEmail()
    {
    	//-----------------------------------------
    	// INIT
    	//-----------------------------------------

		$email	= '';
		
		if( is_string($_REQUEST['email']) )
		{
    		$email	= strtolower( IPSText::parseCleanValue( rawurldecode( $_REQUEST['email'] ) ) );
		}
		
		if( !$email )
		{
			$this->returnString('found');
		}
    	
    	if( !IPSText::checkEmailAddress( $email ) )
    	{
    		$this->returnString('found');
    	}

    	//-----------------------------------------
    	// Got the member?
		//-----------------------------------------
    	
    	if ( ! IPSMember::checkByEmail( $email ) )
 		{
 			//-----------------------------------------
			// Load ban filters
			//-----------------------------------------
			
			$this->DB->build( array( 'select' => '*', 'from' => 'banfilters' ) );
			$this->DB->execute();
			
			while( $r = $this->DB->fetch() )
			{
				$banfilters[ $r['ban_type'] ][] = $r['ban_content'];
			}
			
			//-----------------------------------------
			// Are they banned [EMAIL]?
			//-----------------------------------------
			
			if ( is_array( $banfilters['email'] ) and count( $banfilters['email'] ) )
			{
				foreach ( $banfilters['email'] as $memail )
				{
					$memail = str_replace( "\*", '.*' , preg_quote($memail, "/") );
					
					if ( preg_match( "/^{$memail}$/", $email ) )
					{
						$this->returnString('banned');
						break;
					}
				}
			}
			
			//-----------------------------------------
			// Load handler...
			//-----------------------------------------
			
			require_once( IPS_ROOT_PATH . 'sources/handlers/han_login.php' );
			$han_login		=  new han_login( $this->registry );
			$han_login->init();
			
			if( $han_login->emailExistsCheck( $email ) )
			{
				$this->returnString('found');
			}
		
    		$this->returnString('notfound');
    	}
    	else
    	{
    		$this->returnString('found');
    	}
    }
    
	/**
	 * Check the name or display name
	 *
	 * @access	public
	 * @return	void		[Outputs to screen]
	 */
	public function checkDisplayName( $field='members_display_name' )
    {
    	//-----------------------------------------
    	// INIT
    	//-----------------------------------------
    	
    	$this->registry->class_localization->loadLanguageFile( array( 'public_register' ) );
    	$name	= '';
    	$member = array();
    	
    	if( is_string($_POST['name']) )
    	{
			$name	= strtolower( trim( rawurldecode( $_POST['name'] ) ) );
		}
		
		if( !$name )
		{
			$this->returnString( sprintf( ipsRegistry::getClass( 'class_localization' )->words['reg_error_no_name'], ipsRegistry::$settings['max_user_name_length'] ) );
		}
		
		/* Bug where Twitter sets a username but you can't use it when you try and sign up with a new account using username as display name */
		if ( ! $this->memberData['member_id'] AND $this->request['mpid'] )
		{
			$reg = $this->DB->buildAndFetch( array( 'select'	=> '*',
													'from'		=> 'members_partial',
													'where'		=> "partial_member_id=" . intval( $this->request['mpid'] ) ) );
													
			if ( $reg['partial_member_id'] )
			{
				$member = IPSMember::load( $reg['partial_member_id'], 'all' );
			}
		}

		/* Check the username */
		$user_check = IPSMember::getFunction()->cleanAndCheckName( $name, $member, $field );

		$errorField	= $field == 'members_display_name' ? 'dname' : 'username';
		$nameField	= $field == 'members_display_name' ? 'members_display_name' : 'username';

		if( is_array( $user_check['errors'][ $errorField ] ) && count( $user_check['errors'][ $errorField ] ) )
		{
			$this->returnString( ipsRegistry::getClass( 'class_localization' )->words[ $user_check['errors'][ $errorField ][0] ] ? ipsRegistry::getClass( 'class_localization' )->words[ $user_check['errors'][ $errorField ][0] ] : $user_check['errors'][ $errorField ][0] );
			return;
		}
		else if( $user_check['errors'][ $errorField ] )
		{
			$this->returnString( ipsRegistry::getClass( 'class_localization' )->words[ $user_check['errors'][ $errorField ] ] ? ipsRegistry::getClass( 'class_localization' )->words[ $user_check['errors'][ $errorField ] ] : $user_check['errors'][ $errorField ] );
		}
		else
		{
			$this->returnString('notfound');
		}

    }
	
}