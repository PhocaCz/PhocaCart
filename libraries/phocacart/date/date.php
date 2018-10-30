<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartDate
{
	public static function getActiveDate($from, $to, $returnText = 0) {
		
		$db				= JFactory::getDBO();
		$nullDate 		= $db->getNullDate();
		$now			= JFactory::getDate();
		$config			= JFactory::getConfig();
		$fromDate 		= JFactory::getDate($from);
		$toDate 		= JFactory::getDate($to);
		$tz 			= new DateTimeZone($config->get('offset'));
		$fromDate->setTimezone($tz);
		$toDate->setTimezone($tz);
		
		$status = 0;
		if ( $now->toUnix() <= $fromDate->toUnix() ) {
			$status = 0;
			$text = '<span class="label label-warning">'.JText::_('COM_PHOCACART_PENDING' ).'</span>';
		} else if ( ( $now->toUnix() <= $toDate->toUnix() || $to == $nullDate ) ) {
			$status = 1;
			$text = '<span class="label label-success">'.JText::_('COM_PHOCACART_ACTIVE' ).'</span>';
		} else if ( $now->toUnix() > $toDate->toUnix() ) {
			$status = 0;
			$text = '<span class="label label-important label-danger">'.JText::_('COM_PHOCACART_EXPIRED' ).'</span>';
		}
		
		if ($returnText == 1) {
			return $text;
		} else {
			return $status;
		}
		return false;
	}
	
	public static function getDateDays($fromDate, $toDate) {
		
		$fromDate	= \DateTime::createFromFormat('Y-m-d', $fromDate);
		$toDate 	= \DateTime::createFromFormat('Y-m-d', $toDate);

		if ($fromDate == false || $toDate == false) {
			return array();
		}
		return new \DatePeriod($fromDate, new \DateInterval('P1D'), $toDate->modify('+1 day'));
	}
	
	public static function getCurrentDate($minusDays = 0) {
		
		$user	= JFactory::getUser();
		$config = JFactory::getConfig();
		$date 	= JFactory::getDate("NOW", 'UTC');
		$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));
		$date	= $date->format('Y-m-d', true, false);
		
		if ((int)$minusDays > 0) {
			$datePhp = new \DateTime($date);
			$datePhp->sub(new \DateInterval('P'.(int)$minusDays.'D'));
			$date = $datePhp->format('Y-m-d');
		}
	
		return $date;
	}
	
	public static function splitDate($date = false) {
		
		$o = array();
		if (!$date) {
			$date = date('Y-m-d H:i:s');
		}
		
		$splitDate 		= explode(' ', $date);
		$dateDate 		= $splitDate[0];
		$dateTime 		= $splitDate[1];
		
		$splitDate2 	= explode('-', $dateDate);
		$o['year'] 		= $splitDate2[0];
		$o['month'] 	= $splitDate2[1];
		$o['day'] 		= $splitDate2[2];
		
		$splitDate3 	= explode(':', $dateTime);
		$o['hour'] 		= $splitDate3[0];
		$o['minute'] 	= $splitDate3[1];
		$o['second'] 	= $splitDate3[2];
		
		return $o;
	}
}
?>