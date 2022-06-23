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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

class PhocacartTime
{
	public static function getTimeType($type = 1, $format = 0) {

		$t = '';
		$c = '';
		switch ($type) {

			case 2:
				$t = Text::_('COM_PHOCACART_CLOSING_HOURS');
				$c = "label label-important label-danger badge bg-danger";
			break;

			case 3:
				$t = Text::_('COM_PHOCACART_CLOSING_DAYS');
				$c = "label label-important label-danger badge bg-danger";
			break;
			case 1:
			default:
				$t = Text::_('COM_PHOCACART_OPENING_HOURS');
				$c = "label label-important label-success";
			break;

		}

		if ($t != '' && $format == 1) {

			return '<span class="'.$c.'">'.$t.'</span>';
		}

		return $t;
	}



	public static function getDayOrDate($day, $date) {

		$a 			= array();
		$a['date']	= '';
		$a['day'] 	= '';
		$dateFormat = '0000-00-00 00:00:00';

		if ($date != '' && $date != $dateFormat && $day == '') {
			$datePhp = new \DateTime($date);
			$a['date'] = $datePhp->format('Y-m-d');
			$a['day'] 	= '';
		} else if ($date != '' && $date != $dateFormat && $day != '') {
			//$dateTime 	= \DateTime::createFromFormat('Y-m-d  H:i:s', $date);
			$datePhp = new \DateTime($date);
			$a['date'] = $datePhp->format('Y-m-d');
			$a['day'] 	= '';
		} else if ($day != '' && ($date == '' || $date == $dateFormat)) {

			$dateClass = new Date();
			$a['date'] 	= '';
			$a['day'] 	= $dateClass->dayToString($day);
		}

		return $a;
	}

	public static function getTime($hour, $minute, $type = 0) {

		if ($type == 3) {
			return '';// Closing days don't have any time calculation
		}

		$time = '';
		if ($hour > -1) {
			$time .= str_pad($hour, 2, '0', STR_PAD_LEFT) . ':';

			if ($minute > -1) {
				$time = $time . str_pad($minute, 2, '0', STR_PAD_LEFT);// . ':00';
			} else {
				$time = $time . '00';//. '00:00';
			}
		}

		return $time;
	}

	/**
	 * Test Opening Hours, Closing Hours, Closing Days
	 *
	 * PRIORITY to test:
	 * (3) CLOSING DAYS ->
	 * (2) CLOSING HOURS ->
	 * (1) OPENING HOURS
	 *
	 * PRIORITY (DAY or DATE):
	 * (1) DATE ->
	 * (2) DAY
	 * (if date and day are set, there must be a priority, what should be compared as first - the date
	 * @return boolean
	 */
	public static function checkOpeningTimes($renderMessage = 1) {

		// Possible feature
		// calculate next open time
		// we are next open at:

		$paramsC 						= PhocacartUtils::getComponentParameters();
		$checking_opening_times			= $paramsC->get( 'checking_opening_times', 0 );
		$store_closed_checkout_message	= $paramsC->get( 'store_closed_checkout_message', 0 );

		$orderAllowed = false;// As default ==> 2 Order not possible when closed
		if ($checking_opening_times == 0) {
			return true;
		} else if ($checking_opening_times == 1) {
			$orderAllowed = true;
		}

		$msg 		= PhocacartRenderFront::renderArticle((int)$store_closed_checkout_message, 'html', Text::_('COM_PHOCACART_SHOP_IS_NOT_CURRENTLY_OPEN'));
		$msgType 	= 'error';

		$app			= Factory::getApplication();
		$config 		= Factory::getConfig();
		$date 			= Factory::getDate("NOW", 'UTC');
		$date->setTimezone(new DateTimeZone($config->get('offset')));
		$currentDay		= $date->format('w', true, false);
		$currentTime	= $date->format('H:i', true, false);
		$currentDate	= $date->format('Y-m-d', true, false);


		$db		= Factory::getDbo();
		$q = ' SELECT a.id, a.title, a.type, a.day, a.date, a.hour_from, a.minute_from, a.hour_to, a.minute_to FROM #__phocacart_opening_times AS a';
		$q .= ' WHERE a.published = 1';
		$q .= ' AND (DATE(a.date) = '.$db->quote($currentDate).' OR a.day = '.(int)$currentDay. ')';
		$q .= ' ORDER BY a.type DESC'; // Priority: Closing Days (3) -> Closing Hours (2) -> Opening Hours (1)
		$db->setQuery($q);
		$days = $db->loadAssocList();



		if (!empty($days)) {
			foreach($days as $k => $v) {

				// 1. Test CLOSING DAYS (3)
				if ($v['type'] == 3 && $v['date'] != '' && $v['date'] != '0000-00-00 00:00:00' && strtotime($currentDate) ==  strtotime($v['date'])) {
					if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
					return $orderAllowed;
				} else if ($v['type'] == 3 && $v['day'] > -1 && (int)$currentDay == (int)$v['day']) {
                    if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
					return $orderAllowed;
				}

				$timeFrom 	= PhocacartTime::getTime($v['hour_from'], $v['minute_from']);
				$timeTo 	= PhocacartTime::getTime($v['hour_to'], $v['minute_to']);

				// 2. Test CLOSING HOURS (2)
				if ($v['type'] == 2 && $v['date'] != '' && $v['date'] != '0000-00-00 00:00:00') {
					if ($currentTime >= $timeFrom && $currentTime <= $timeTo) {
                        if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
						return $orderAllowed;
					}
				} else if ($v['type'] == 2 && $v['day'] > -1) {
					if ($currentTime >= $timeFrom && $currentTime <= $timeTo) {
                        if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
						return $orderAllowed;
					}
				}


				// 3. Test OPENING HOURS (1)
				if ($v['type'] == 1 && $v['date'] != '' && $v['date'] != '0000-00-00 00:00:00') {
					if ($currentTime >= $timeFrom && $currentTime <= $timeTo) {
						// continue to check other times
					} else {
                        if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
						return $orderAllowed;
					}
				} else if ($v['type'] == 1 && $v['day'] > -1) {

					if ($currentTime >= $timeFrom && $currentTime <= $timeTo) {
						// continue to check other times
					} else {
                        if ($renderMessage) {$app->enqueueMessage($msg, $msgType);}
						return $orderAllowed;
					}
				}

			}

		}

		return true;
	}


	public static function getOpeningTimesMessage() {


        $paramsC 						= PhocacartUtils::getComponentParameters();
        $checking_opening_times			= $paramsC->get( 'checking_opening_times', 0 );
        $store_closed_checkout_message	= $paramsC->get( 'store_closed_checkout_message', 0 );

        $orderAllowed = false;// As default ==> 2 Order not possible when closed
        if ($checking_opening_times == 0) {
            return '';
        } else if ($checking_opening_times == 1) {
            $orderAllowed = true;
        }

        if ($orderAllowed) {
            return PhocacartRenderFront::renderArticle((int)$store_closed_checkout_message, 'html', Text::_('COM_PHOCACART_SHOP_IS_NOT_CURRENTLY_OPEN'));
        }
    }



}
