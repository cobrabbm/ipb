<?php
/**
 * Invision Power Board
 * Action controller for admin page
 */

class action_admin
{
	var $install;
	
	function action_admin( & $install )
	{
		$this->install =& $install;
	}
	
	function run()
	{
		/* Check input? */
		if( $this->install->ipsclass->input['sub'] == 'check' )
		{
			if( ! $this->install->ipsclass->input['username'] )
			{
				$errors[] = '������ָ��һ������Ա�û�����';	
			}
			
			if( ! $this->install->ipsclass->input['password'] )
			{
				$errors[] = '������ָ��һ������Ա�û�����';	
			}
			else 
			{
				if( $this->install->ipsclass->input['password'] != $this->install->ipsclass->input['confirm_password']	)
				{
					$errors[] = '����Ա�������벻ƥ��';	
				}
			}
			
			if( ! $this->install->ipsclass->input['email'] )
			{
				$errors[] = '������ָ��һ������Ա�ʼ���ַ';	
			}
			
			if( is_array( $errors ) )
			{
				$this->install->template->warning( $errors );	
			}
			else 
			{
				/* Save Form Data */
				$this->install->saved_data['admin_user']  = $this->install->ipsclass->input['username'];
				$this->install->saved_data['admin_pass']  = $this->install->ipsclass->input['password'];
				$this->install->saved_data['admin_email'] = $this->install->ipsclass->input['email'];

				/* Next Action */
				$this->install->template->page_current = 'install';
				require_once( INS_ROOT_PATH . 'core/actions/install.php' );	
				$action = new action_install( &$this->install );
				$action->run();
				return;				
			}		
		}

		/* Output */
		$this->install->template->append( $this->install->template->admin_page() );		
		$this->install->template->next_action = '?p=admin&sub=check';
	}
}

?>
