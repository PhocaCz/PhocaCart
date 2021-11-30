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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PhocacartDate
{
	public static function getActiveDate($from, $to, $returnText = 0) {

		$db				= Factory::getDBO();
		$nullDate 		= $db->getNullDate();
		$now			= Factory::getDate();
		$config			= Factory::getConfig();
		$fromDate 		= Factory::getDate($from);
		$toDate 		= Factory::getDate($to);
		$tz 			= new DateTimeZone($config->get('offset'));
		$fromDate->setTimezone($tz);
		$toDate->setTimezone($tz);


		$status = 0;
		$text = '';
		if ( $now->toUnix() <= $fromDate->toUnix() ) {
			$status = 0;
			$text = '<span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_PENDING' ).'</span>';
		} else if ( ( $now->toUnix() <= $toDate->toUnix() || $to == $nullDate ) ) {
			$status = 1;
			$text = '<span class="label label-success badge bg-success">'.Text::_('COM_PHOCACART_ACTIVE' ).'</span>';
		} else if ( $now->toUnix() > $toDate->toUnix() ) {
			$status = 0;
			$text = '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_EXPIRED' ).'</span>';
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

		$user	= Factory::getUser();
		$config = Factory::getConfig();
		$date 	= Factory::getDate("NOW", 'UTC');
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
		$dateTime		= '';
		if (isset($splitDate[1])) {
			$dateTime = $splitDate[1];
		}

		$splitDate2 	= explode('-', $dateDate);
		$o['year'] 		= $splitDate2[0];
		$o['month'] 	= $splitDate2[1];
		$o['day'] 		= $splitDate2[2];

		if ($dateTime != '') {
			$splitDate3  = explode(':', $dateTime);
			$o['hour']   = $splitDate3[0];
			$o['minute'] = $splitDate3[1];
			$o['second'] = $splitDate3[2];
		} else {
			$o['hour']   = '00';
			$o['minute'] = '00';
			$o['second'] = '00';
		}

		return $o;
	}

	public static function activeDatabaseDate($date) {

	    switch($date) {
            case '0000-00-00 00:00:00':
            case '0000-00-00':
            case '0':
            case 0:
            case '':
            case false:
                return false;
            break;

            default:
                return true;
            break;

        }
    }
}
?>
