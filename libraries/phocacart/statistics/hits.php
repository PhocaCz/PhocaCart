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
final class PhocacartStatisticsHits
{
	
	public function __construct() {

	}

	public static function productHit($productId = 0) {
		
		if ($productId == 0) {
			return false;
		}
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$additional_hits	= $paramsC->get( 'additional_hits', array() );
		
		
		// 1 ... product view
		if (!in_array(1, $additional_hits)) {
			return false;
		}
		
		
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();
		$ip		= PhocacartUtils::getIp();
		$date 	= JFactory::getDate();
		$item	= array();
		
		$q = 'SELECT a.id, a.hits'
			.' FROM #__phocacart_hits AS a';
		if (isset($user->id) && (int)$user->id > 0) {
			$q .= ' WHERE a.product_id = '.(int) $productId . ' AND a.user_id = '.(int)$user->id;
		} else if (isset($ip) && $ip != '') {
			$q .= ' WHERE a.product_id = '.(int) $productId . ' AND a.ip = '.$db->quote($ip);
		}
		$q .=' ORDER BY a.id'
		    .' LIMIT 1';
		$db->setQuery($q);
		$item = $db->loadAssoc();
		
		$q = '';
		if (empty($item)){
			if (isset($user->id) && (int)$user->id > 0) {
				$q = 'INSERT INTO #__phocacart_hits (product_id, user_id, ip, hits, type, date) VALUES ('.(int)$productId.', '.(int)$user->id.', '.$db->quote($ip).', 1, 1, '.$db->quote($date).')';
			} else if (isset($ip) && $ip != '') {
				$q = 'INSERT INTO #__phocacart_hits (product_id, ip, hits, type, date) VALUES ('.(int)$productId.', '.$db->quote($ip).', 1, 1, '.$db->quote($date).')';
			}

			$db->setQuery($q);
			$db->execute();
			return true;
		} else if (isset($item['id']) && (int)$item['id'] > 0) {
			$hits = (int)$item['hits'] + 1;
			if (isset($user->id) && (int)$user->id > 0) {
				
				$q = 'UPDATE #__phocacart_hits SET hits = '.(int)$hits.', date = '.$db->quote($date)
				.' WHERE product_id = '.(int)$productId
				.' AND user_id = '.(int)$user->id;
				$db->setQuery($q);
				$db->execute();
				return true;
			
			} else if (isset($ip) && $ip != '') {
				$q = 'UPDATE #__phocacart_hits SET hits = '.(int)$hits.', date = '.$db->quote($date)
				.' WHERE product_id = '.(int)$productId
				.' AND ip = '.$db->quote($ip);
				$db->setQuery($q);
				$db->execute();
				return true;
			}
			
		}
		return false;
	}
	
	public static function searchHit($search = '') {
		
		if ($search == '') {
			return false;
		}
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$additional_hits	= $paramsC->get( 'additional_hits', array() );
		
		// 2 ... search term
		if (!in_array(2, $additional_hits)) {
			return false;
		}
			
		
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();
		$ip		= PhocacartUtils::getIp();
		$date 	= JFactory::getDate();
		$item	= array();
		
		$q = 'SELECT a.id, a.hits'
			.' FROM #__phocacart_hits AS a';
		if (isset($user->id) && (int)$user->id > 0) {
			$q .= ' WHERE a.item = '.$db->quote($db->escape($search)) . ' AND a.user_id = '.(int)$user->id;
		} else if (isset($ip) && $ip != '') {
			$q .= ' WHERE a.item = '.$db->quote($db->escape($search)) . ' AND a.ip = '.$db->quote($ip);
		}
		$q .=' ORDER BY a.id'
		    .' LIMIT 1';
		$db->setQuery($q);
		$item = $db->loadAssoc();
		
		$q = '';
		if (empty($item)){
			if (isset($user->id) && (int)$user->id > 0) {
				$q = 'INSERT INTO #__phocacart_hits (item, user_id, ip, hits, type, date) VALUES ('.$db->quote($db->escape($search)).', '.(int)$user->id.', '.$db->quote($ip).', 1, 2, '.$db->quote($date).')';
			} else if (isset($ip) && $ip != '') {
				$q = 'INSERT INTO #__phocacart_hits (item, ip, hits, type, date) VALUES ('.$db->quote($db->escape($search)).', '.$db->quote($ip).', 1, 2, '.$db->quote($date).')';
			}

			$db->setQuery($q);
			$db->execute();
			return true;
		} else if (isset($item['id']) && (int)$item['id'] > 0) {
			$hits = (int)$item['hits'] + 1;
			if (isset($user->id) && (int)$user->id > 0) {
				
				$q = 'UPDATE #__phocacart_hits SET hits = '.(int)$hits.', date = '.$db->quote($date)
				.' WHERE item = '.$db->quote($db->escape($search))
				.' AND user_id = '.(int)$user->id;
				$db->setQuery($q);
				$db->execute();
				return true;
			
			} else if (isset($ip) && $ip != '') {
				$q = 'UPDATE #__phocacart_hits SET hits = '.(int)$hits.', date = '.$db->quote($date)
				.' WHERE item = '.$db->quote($db->escape($search))
				.' AND ip = '.$db->quote($ip);
				$db->setQuery($q);
				$db->execute();
				return true;
			}
			
		}
		return false;
	}
}
?>