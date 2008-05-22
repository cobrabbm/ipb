<?php
/**
 * IDM Module v1.2.1
 * Action controller for requirements page
 */

class action_overview
{
	var $install;
	
	function action_overview( & $install )
	{
		$this->install =& $install;
		
		$this->install->ipsclass->login_type = 'username';
		
		if( $this->install->ipsclass->DB->field_exists( "conf_id", "conf_settings" ) )
		{
			$this->install->ipsclass->DB->build_query( array( 'select' => '*', 'from' => 'conf_settings', 'where' => "conf_key IN('ipbli_usertype','converge_login_method')", 'order' => 'conf_key ASC' ) );
			$this->install->ipsclass->DB->exec_query();
			
			while( $r = $this->install->ipsclass->DB->fetch_row() )
			{
				$r['conf_value'] = $r['conf_value'] ? $r['conf_value'] : $r['conf_default'];
				
				if( $r['conf_value'] )
				{
					$this->install->ipsclass->login_type = $r['conf_value'];
				}
			}
		}
	}
	
	function run()
	{
		//----------------------------------
		// We must have submitted the form
		// time to check some details.
		//----------------------------------
	
		if ( empty($this->install->ipsclass->input['username']) )
		{
			$this->install->template->append( $this->install->template->login_page( "You must enter a username before proceeding" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}
	
		if ( empty($this->install->ipsclass->input['password']) )
		{
			$this->install->template->append( $this->install->template->login_page( "You must enter a password before proceeding" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}

		//----------------------------------
		// Attempt to get the details from the
		// DB
		//----------------------------------
	
        if( $this->install->ipsclass->login_type == 'username' )
        {
        	$this->install->ipsclass->DB->query("SELECT m.*, g.* FROM ibf_members m LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup) WHERE LOWER(name)='".strtolower($this->install->ipsclass->input['username'])."'");
    	}
    	else
    	{
	    	$this->install->ipsclass->DB->query("SELECT m.*, g.* FROM ibf_members m LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup) WHERE LOWER(email)='".strtolower($this->install->ipsclass->input['username'])."'");
    	}

		$mem = $this->install->ipsclass->DB->fetch_row();

		//----------------------------------
		// Get perms
		//----------------------------------
		if ( empty($mem['id']) )
		{
			$this->install->template->append( $this->install->template->login_page( "Could not find a record matching that username, please check the spelling" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}
	
		//----------------------------------
		// Load converge member
		//----------------------------------
		$this->install->ipsclass->converge->converge_load_member($mem['email']);

		if ( ! $this->install->ipsclass->converge->member['converge_id'] )
		{
			$this->install->template->append( $this->install->template->login_page( "Could not find a record matching that username, please check the spelling" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}

		//----------------------------------
		// Check converge pass
		//----------------------------------
	
		$pass = md5( $this->install->ipsclass->input['password'] );
		if ( $this->install->ipsclass->converge->converge_authenticate_member( $pass ) != TRUE )
		{
			$this->install->template->append( $this->install->template->login_page( "The password you entered is not correct" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}
		else
		{
			if ($mem['g_access_cp'] != 1)
			{
				$this->install->template->append( $this->install->template->login_page( "You do not have access to the administrative CP" ) );
				$this->install->template->next_action = '?p=overview';
				return;
			}
			else
			{
				$this->install->ipsclass->member = $mem;
				$this->install->saved_data['loginkey'] = $mem['member_login_key'];
				$this->install->saved_data['securekey'] = $this->install->ipsclass->return_md5_check();
				$this->install->saved_data['mid'] = $mem['id'];
			}
		}
		
		$this->install->get_version_latest();

		$error = array();
		if ( ! $this->install->last_poss_id )
		{
			$error[] = "An error has occured, we are unable to determine the current version or if there are any required upgrade files left to run";
		}
		if ( count($this->install->versions) == 0 )
		{
			$error[] = "Could not locate the required upgrade script: 'upg_{$this->install->last_poss_id}/version_history.php'";
		}
		if ( ! $this->install->current_version )
		{
			$error[] = "An error has occured, we are unable to determine the current version or if there are any required upgrade files left to run";
		}
		if ( $this->install->last_poss_id <= $this->install->current_version )
		{
			$error[] = "Your installation is up to date; no upgrade required";
		}

		// Required files
		$files = array( ROOT_PATH.'resources/idm/idm_components.xml',
						ROOT_PATH.'resources/idm/idm_macro.xml',
						ROOT_PATH.'resources/idm/idm_settings.xml',
						ROOT_PATH.'resources/idm/idm_templates.xml' );
		foreach ( $files as $file )
		{
			if ( ! file_exists( $file ) )
			{
				$error[] = 'Missing file: '.$file;
			}
		}
		
		if ( count ( $error ) )
		{
			$this->install->template->warning( $error );
			$this->install->template->next_action = '';
			$this->install->template->hide_next   = 1;		
			return;
		}

		$current_version = $this->install->current_version == '00000' ? 'not installed' : $this->install->versions[ $this->install->current_version ];
		if ( $this->install->current_version == '00000' )
		{
			$summary = "New installation of version ".$this->install->versions[ $this->install->last_poss_id ];
		}
		else
		{
			$summary = "Upgrade from ".$this->install->versions[ $this->install->current_version ]." to ".$this->install->versions[ $this->install->last_poss_id ];
		}
				
		/* Page Output */
		$this->install->template->append( $this->install->template->overview_page( $current_version, $summary ) );		
		$this->install->template->next_action = '?p=eula';
		
	}
}

?>