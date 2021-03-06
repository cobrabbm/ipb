<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Sets up SEO templates
 * Last Updated: $Date: 2010-04-13 07:32:04 -0400 (Tue, 13 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6099 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$_SEOTEMPLATES = array(
						'cal_week'		  => array( 
											'app'			=> 'calendar',
											'allowRedirect' => 1,
											'out'			=> array( '#app=calendar(?:&|&amp;)module=calendar(?:&|&amp;)cal_id=(\d+?)(?:&|&amp;)do=showweek(?:&|&amp;)week=(\d+?)(?:&|$)#i', 'calendar/\\1/week-\\2' ),
											'in'			=> array( 
																		'regex'		=> "#/calendar/(\d+?)/week-(\d+?)(/|$)#i",
																		'matches'	=> array( array( 'app'     , 'calendar' ),
																		 					  array( 'module'  , 'calendar' ),
																		  					  array( 'do'      , 'showweek' ),
																							  array( 'cal_id'  , '$1' ),
																							  array( 'week', '$2' ) )
																	)  ),
												
						'event'			   => array( 
											'app'			=> 'calendar',
											'allowRedirect' => 1,
											'out'			=> array( '#app=calendar(?:&|&amp;)module=calendar(?:&|&amp;)cal_id=(\d+?)(?:&|&amp;)do=showevent(?:&|&amp;)event_id=(\d+?)(?:&|$)#i', 'calendar/\\1/event-\\2' ),
											'in'			=> array( 
																		'regex'		=> "#/calendar/(\d+?)/event-(\d+?)(/|$)#i",
																		'matches'	=> array( array( 'app'     , 'calendar' ),
																		 					  array( 'module'  , 'calendar' ),
																		  					  array( 'do'      , 'showevent' ),
																							  array( 'cal_id'  , '$1' ),
																							  array( 'event_id', '$2' ) )
																	)  ),
						
						'cal_day'			   => array( 
											'app'			=> 'calendar',
											'allowRedirect' => 1,
											'out'			=> array( '#app=calendar(?:&|&amp;)module=calendar(?:&|&amp;)cal_id=(.+?)(?:&|&amp;)do=showday(?:&|&amp;)y=(.+?)&amp;m=(.+?)&amp;d=(.+?)(?:&|$)#i', 'calendar/\\1/day-\\2-\\3-\\4' ),
											'in'			=> array( 
																		'regex'		=> "#/calendar/(\d+?)/day-(\d+?)-(\d+?)-(\d+?)(/|$)#i",
																		'matches'	=> array( array( 'app'     , 'calendar' ),
																		 					  array( 'module'  , 'calendar' ),
																		  					  array( 'do'      , 'showday' ),
																							  array( 'cal_id'  , '$1' ),
																							  array( 'y'       , '$2' ),
																							  array( 'm'       , '$3' ),
																							  array( 'd'       , '$4' ) )
																	)  ),
						
						'app=calendar'		=> array( 
											'app'			=> 'calendar',
											'allowRedirect' => 1,
											'out'			=> array( '#app=calendar$#i', 'calendar/' ),
											'in'			=> array( 
																		'regex'		=> "#/calendar(/|$|\?)#i",
																		'matches'	=> array( array( 'app', 'calendar' ) )
																	) 
														),
					);
