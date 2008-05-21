<?php
/**
 * Invision Power Board
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
			$this->install->template->append( $this->install->template->login_page( "再继续操作前您必须输入一个会员名称" ) );
			$this->install->template->next_action = '?p=overview';
			return;
		}
		
		$this->install->ipsclass->input['username'] = str_replace( '|', '&#124;', $this->install->ipsclass->input['username'] );
	
		if ( empty($this->install->ipsclass->input['password']) )
		{
			$this->install->template->append( $this->install->template->login_page( "再继续操作前您必须输入一个会员密码" ) );
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
            $this->install->template->append( $this->install->template->login_page( "无法找到匹配的会员, 请检查您的输入" ) );
            $this->install->template->next_action = '?p=overview';
            return;
        }
    
        //----------------------------------
        // Load converge member
        //----------------------------------
        
        if( !$this->install->ipsclass->DB->field_exists( 'converge_id', 'members_converge' ) OR  
            ( isset($this->install->saved_data['vid']) AND $this->install->saved_data['vid'] < 10003 ) )
        {
            $pass = md5( $this->install->ipsclass->input['password'] );
            
            if ( $pass != $mem['password'] AND $pass != $mem['legacy_password'] )
            {
                $this->install->template->append( $this->install->template->login_page( "您输入的会员密码错误" ) );
                $this->install->template->next_action = '?p=overview';
                return;
            }
            else
            {
                if ($mem['g_access_cp'] != 1)
                {
                    $this->install->template->append( $this->install->template->login_page( "您没有进入后台管理的权限" ) );
                    $this->install->template->next_action = '?p=overview';
                    return;
                }
                else
                {
                    $this->install->ipsclass->member = $mem;
                    $this->install->saved_data['loginkey'] = $mem['password'] ? md5($mem['password']) : md5($mem['legacy_password']);
                    $this->install->saved_data['securekey'] = $this->install->ipsclass->return_md5_check();
                    $this->install->saved_data['mid'] = $mem['id'];
                }
            }                    
        }
        else
        {
            $this->install->ipsclass->converge->converge_load_member($mem['email']);
    
            if ( ! $this->install->ipsclass->converge->member['converge_id'] )
            {
                $this->install->template->append( $this->install->template->login_page( "无法找到匹配的会员, 请检查您的输入" ) );
                $this->install->template->next_action = '?p=overview';
                return;
            }

            //----------------------------------
            // Check converge pass
            //----------------------------------

            $pass = md5( $this->install->ipsclass->input['password'] );
    
            if ( $this->install->ipsclass->converge->converge_authenticate_member( $pass ) != TRUE )
            {
                $this->install->template->append( $this->install->template->login_page( "您输入的会员密码错误" ) );
                $this->install->template->next_action = '?p=overview';
                return;
            }
            else
            {
                if ($mem['g_access_cp'] != 1)
                {
                    $this->install->template->append( $this->install->template->login_page( "您没有进入后台管理的权限" ) );
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
        } 
			
		
		$this->install->get_version_latest();

		$error = array();
		if ( ! $this->install->last_poss_id )
		{
			$error[] = "发生一个错误, 如果有任何升级步骤未完成则无法确定您当前的系统版本";
		}
		if ( count($this->install->versions) == 0 )
		{
			$error[] = "无法定位升级文件: 'upg_{$this->install->last_poss_id}/version_history.php'";
		}
		if ( ! $this->install->current_version )
		{
			$error[] = "发生一个错误, 如果有任何升级步骤未完成则无法确定您当前的系统版本";
		}
		if ( $this->install->last_poss_id != $this->install->current_version )
		{
			$error[] = "Your installation is up to date; no upgrade required";
		}

		$checkfiles = array( ROOT_PATH     ."resources/ipb_templates.xml",
							 ROOT_PATH     ."resources/skinsets.xml",
							 ROOT_PATH     ."resources/macro.xml",
							 ROOT_PATH     ."resources/settings.xml",
							 ROOT_PATH     ."resources/acpperms.xml",
							 ROOT_PATH     ."resources/version_history.php",
							 ROOT_PATH     ."sources/sql",
							 KERNEL_PATH   ."class_converge.php",
							 KERNEL_PATH   ."class_xml.php",
							 KERNEL_PATH   ."class_db_".SQL_DRIVER.".php",
							 ROOT_PATH     ."conf_global.php",
						  );
						
		$timecheck  = array(
							 ROOT_PATH     ."resources/ipb_templates.xml",
							 ROOT_PATH     ."resources/skinsets.xml",
							 ROOT_PATH     ."resources/macro.xml",
							 ROOT_PATH     ."resources/settings.xml",
							 ROOT_PATH     ."resources/acpperms.xml",
							 ROOT_PATH     ."resources/version_history.php"
						 );
						  
		$writeable  = array( #ROOT_PATH."conf_global.php",
							 ROOT_PATH."cache/",
							 ROOT_PATH."cache/skin_cache/",
							 ROOT_PATH."style_images/"
						   );
		
		@filemtime( ROOT_PATH . 'sources/ipsclass.php' );
		
		foreach ( $checkfiles as $cf )
		{
			if ( ! file_exists($cf) )
			{
				$error[] = "无法定位文件 '$cf'.";
			}
		}
		
		foreach ( $writeable as $cf )
		{
			if ( ! is_writeable($cf) )
			{
				$error[] = "无法写入文件或文件夹 '$cf'. 请设置属性为 0777.";
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
			$this->ipsclass->boink_it( str_replace( 'upgrade', 'install', $this->ipsclass->my_getenv( 'PHP_SELF' ) ) );
		}
		else
		{
			$summary .= "从 ".$this->install->versions[ $this->install->current_version ]." 英文版 升级到 易维论坛 ".$this->install->versions[ $this->install->last_poss_id ]." 中文版";
		}
		
		//-----------------------------------------
		// Time check...
		//-----------------------------------------
		
		$summary .= "<br /><br /><strong>正在检查 'resources' 文件夹</strong>";
		$_flag    = 0;
		
		foreach ( $timecheck as $cf )
		{
			$_time = @filemtime( $cf );
			
			if ( $time_to_check AND $_time AND ( ( $time_to_check - (86400 * 7) ) > $_time ) )
			{
				$_flag    = 1;
				$summary .= "<div style='color:red'>&middot;" . str_replace( ROOT_PATH, '', $cf ) . ' 可能已经过时</div>';
			}
		}
		
		if ( $_flag )
		{
			$summary .= "<br /><div>请检查上面的文件以确认他们已经正确上传来自 ".$this->install->versions[ $this->install->last_poss_id ]." 的下载";
		}
		else
		{
			$summary .= "<div style='color:green'>所有文件验证成功</div>";
		}
			
		/* Page Output */
		$this->install->template->append( $this->install->template->overview_page( $current_version, $summary ) );		
		$this->install->template->next_action = '?p=eula';
		
	}
}

?>