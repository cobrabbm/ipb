<?php
/**
 * IDM Module v1.2.1
 * Action controller for done page
 */

class action_done
{
	var $install;
	
	function action_done( & $install )
	{
		$this->install =& $install;
	}
	
	function run()
	{
		require ROOT_PATH.'init.php';
		
		//-----------------------------------------
		// Show page
		//-----------------------------------------
		
		$this->install->template->append( $this->install->template->install_done( $this->install->ipsclass->vars['board_url'].'/'.IPB_ACP_DIRECTORY.'/index.php' ) );
		$this->install->template->next_action = '';
		$this->install->template->hide_next   = 1;		
	}
}

?>