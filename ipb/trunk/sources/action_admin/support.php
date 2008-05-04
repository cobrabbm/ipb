<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2006 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|   > $Date: 2006-03-23 07:34:25 -0500 (Thu, 23 Mar 2006) $
|   > $Revision: 177 $
|   > $Author: brandon $
+---------------------------------------------------------------------------
|
|   > Support Module
|   > Module written by Brandon Farber
|   > Date started: 19th April 2006
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class ad_support
{
	var $base_url;
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "help";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "support";
	
	function auto_run()
	{
		$this->ipsclass->admin->nav[] = array( $this->ipsclass->form_code, 'Support' );
		
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'doctor':
				$this->ipsclass->admin->page_detail = "请利用我们的文档来学习如何使用 IPB 论坛程序的功能.";
				$this->ipsclass->admin->page_title  = "文档";
				
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( 'http://external.ipslink.com/ipboard22/landing/?p=docs-ipb' );
				break;
			break;
			
			case 'kb':
				$this->ipsclass->admin->page_detail = "请利用我们的知识库来解决您使用过程中的一些常规问题.您也可以在这里学习如何更好地利用此软件资源.";
				$this->ipsclass->admin->page_title  = "帮助和支持";
				
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'http://external.ipslink.com/ipboard22/landing/?p=kb' );
				break;
				
			case 'support':
				$this->ipsclass->admin->page_detail = "如果您遇到 IPB 软件方面的错误不能解决,需要离线支援,您可以使用我们的申请系统提交一份援助申请.您的申请处理时间大概为24-48小时.<br /><br /><i>请随申请提交一份您的有效联系方式.</i>";
				$this->ipsclass->admin->page_title  = "帮助和支持";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'https://www.invisionpower.com/customer/index.php?&module=clientarea&section=tickets' );
				break;			
				
			case 'resources':
				$this->ipsclass->admin->page_detail = "resources.invisionpower.com 是一个客户之间相互交流的站点,您可以在这里找到很多有用的文章,模板,主题和图片.需要提醒的是, resources.invisionpower.com 仅仅只是客户之间交流的平台,并不是 IPS 官方支持系统.如果您想为您的论坛注入新的活力,这是一个好去处.";
				$this->ipsclass->admin->page_title  = "帮助和支持";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'http://resources.invisionpower.com' );
				break;
				
			case 'contact':
				$this->ipsclass->admin->page_detail = "您可以在下面找到我们正确的联系方式和正常的工作时间.";
				$this->ipsclass->admin->page_title  = "帮助和支持";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'http://external.ipslink.com/ipboard22/landing/?p=contact' );
				break;
				
			case 'features':
				$this->ipsclass->admin->page_detail = "如果您想要定制一项功能,或者在其他地方已经发现了此项功能,您也可以使用此论坛来反馈给 IPB.";
				$this->ipsclass->admin->page_title  = "帮助和支持";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'http://external.ipslink.com/ipboard22/landing/?p=suggestfeatures' );
				break;
				
			case 'bugs':
				$this->ipsclass->admin->page_detail = "您可以提交并且跟踪查看所有用户给我们提交的 IPB 程序缺陷信息.";
				$this->ipsclass->admin->page_title  = "帮助和支持";
							
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':查看' );
				$this->ipsclass->admin->show_inframe( 'http://forums.invisionpower.com/index.php?autocom=bugtracker&code=show_project&product_id=2' );
				break;				
				
				
			//-----------------------------------------
			default:
				$this->ipsclass->admin->page_detail = "如果您遇到 IPB 软件方面的错误不能解决,需要离线支援,您可以使用我们的申请系统提交一份援助申请.您的申请处理时间大概为24-48小时.<br /><br /><i>请随申请提交一份您的有效联系方式.</i>";
				$this->ipsclass->admin->page_title  = "帮助和支持";
			
				$this->ipsclass->admin->cp_permission_check( $this->perm_main.'|'.$this->perm_child.':view' );
				$this->ipsclass->admin->show_inframe( 'https://www.invisionpower.com/customer/index.php?&module=clientarea&section=tickets' );
				break;
		}
	}
	
}


?>