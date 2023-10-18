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

 /*
  * $reward['points_needed'] or $reward['needed']		... how many points are needed to buy the product
  * $reward['points_received'] or $reward['received'] 	... how many points do customer get when he/she buy the product
  * $reward['wantstouse'] 								... how many points wants the customer use when buying items
  * $reward['used'] 									... how many points were really used
  * $reward['usertotal'] 								... how many points user have in his/her account
  * $reward['usedtotal'] 								... how many points user used (and it was allowed) when ordering items
  */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PhocacartReward
{
	protected $reward;
	protected $total;

	public function __construct() {

		$this->total = array();
	}

	public function getTotalPointsByUserId($userId) {

		if ($userId > 0) {
			if (empty($this->total[$userId])) {

				$db = Factory::getDBO();

				$query = 'SELECT SUM(a.points) FROM #__phocacart_reward_points AS a'
					.' WHERE a.user_id = '.(int) $userId
					.' AND a.published = 1'
					.' GROUP BY a.user_id'
					.' ORDER BY a.id';
				$db->setQuery($query);

				$total = $db->loadResult();

				if (!$total) {
					$total = 0;
				}

				$this->total[$userId] = (int)$total;
			}

			return $this->total[$userId];
		}
		return 0;
	}



	public function checkReward($points, $msgOn = 0) {


		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$enable_rewards		= $paramsC->get( 'enable_rewards', 1 );

		$rewards				= array();
		$rewards['usertotal'] 	= 0;
		$rewards['wantstouse']	= (int)$points;
		$rewards['used']		= false;


		// 1. ENABLE REWARDS
		if ($enable_rewards == 0) {
			if ($msgOn == 1) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_REWARD_POINTS_DISABLED'), 'error');
			}
			return false;
		}

		// 2. USER
		$user 					= PhocacartUser::getUser();

		if ($user->id > 0) {
			$rewards['usertotal'] = $this->getTotalPointsByUserId($user->id);
		} else {
			if ($msgOn == 1) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_USER_NOT_FOUND'), 'error');
			}
			return false;
		}

		// 3. TOTAL
		if ($rewards['usertotal'] == $rewards['wantstouse']) {
			$rewards['used'] = $rewards['wantstouse'];
		} else if ($rewards['usertotal'] > $rewards['wantstouse']) {
			$rewards['used'] = $rewards['wantstouse'];
		} else if ($rewards['usertotal'] < $rewards['wantstouse']) {
			$rewards['used'] = $rewards['usertotal'];
		}

		return $rewards['used'];
	}

	public function calculatedRewardDiscountProduct(&$rewards) {

		$rewards['percentage']	= 0;
		$rewards['usedproduct']	= 0;


		if ($rewards['needed'] == $rewards['used']) {

			$rewards['usedproduct']	= $rewards['used'];
			$rewards['percentage'] 	= 100;
			$rewards['used']		= 0; // Rest

		} else if ($rewards['needed'] > $rewards['used']) {

			$rewards['usedproduct']	= $rewards['used'];
			$rewards['percentage']	= 100 * $rewards['usedproduct'] / $rewards['needed'];
			$rewards['used']		= $rewards['used'] - $rewards['usedproduct'];// Rest

		} else if ($rewards['used'] > $rewards['needed']) {

			$rewards['usedproduct']	= $rewards['needed'];
			$rewards['percentage'] 	= 100;
			$rewards['used'] 		= $rewards['used'] - $rewards['needed']; // Rest

		}

		$rewards['usedtotal'] += $rewards['usedproduct'];
	}

	public static function getPoints($points, $type = 'received', $groupPoints = null) {

		$pointsO 			= null;
		//$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$enable_rewards		= $paramsC->get( 'enable_rewards', 1 );

		if ($enable_rewards == 0) {
			return $pointsO;
		}
		if ($type == 'needed') {

			if ($points > 0) {
				$pointsO = $points;
			}
		} else if ($type == 'received') {

			if ($points > 0) {
				$pointsO = $points;
			}
			if ($groupPoints > 0) {
				$pointsO = $groupPoints;
			}
		}

		return $pointsO;

	}

	// STATIC PART
	public static function getTotalPointsByUserIdExceptCurrentOrder($userId, $orderId) {

		if ((int)$userId > 0 && (int)$orderId > 0) {

			$db = Factory::getDBO();

			$query = 'SELECT SUM(a.points) FROM #__phocacart_reward_points AS a'
				.' WHERE a.user_id = '.(int)$userId
				.' AND a.order_id <> '.(int)$orderId
				.' AND a.published = 1'
				.' GROUP BY a.order_id'
				.' ORDER BY a.id';
			$db->setQuery($query);

			$total = $db->loadResult();

			if (!$total) {
				$total = 0;
			}

			return (int)$total;
		}

		return 0;
	}

	public static function getTotalPointsByOrderId($orderId) {

		if ((int)$orderId > 0) {

			$db = Factory::getDBO();

			$query = 'SELECT SUM(a.points) FROM #__phocacart_reward_points AS a'
				.' WHERE a.order_id = '.(int)$orderId
			//	.' AND a.published = 1' Get all points (+/-) even they are not authorized yet (add info to customer how much point he can get)
				.' GROUP BY a.order_id'
				.' ORDER BY a.id';
			$db->setQuery($query);

			$total = $db->loadResult();

			if (!$total) {
				$total = 0;
			}

			return (int)$total;
		}

		return 0;
	}

    public static function getRewardPointsByOrderId($orderId) {

        if ((int)$orderId > 0) {

            $db = Factory::getDBO();

            $query = 'SELECT a.title, a.points, a.order_id, a.type, a.published FROM #__phocacart_reward_points AS a'
                .' WHERE a.order_id = '.(int)$orderId
                .' ORDER BY a.id';
            $db->setQuery($query);

            $points = $db->loadObjectList();

            if (!empty($points)) {
                return $points;
            }
        }

        return false;
    }
}
