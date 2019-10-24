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

class PhocacartPriceHistory
{

	public static function storePriceHistoryById($productId, $price) {

		$date		= JFactory::getDate();
		$dateNow 	= $date->toSql();

		$db = JFactory::getDBO();

		$query = 'SELECT a.id, a.price, a.date'
				.' FROM #__phocacart_product_price_history AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' AND a.date = (SELECT MAX(date) FROM #__phocacart_product_price_history WHERE product_id = '.(int)$productId.')'
				.' ORDER BY a.id';

		$db->setQuery($query);
		$history = $db->loadAssoc();

		$price		= PhocacartUtils::replaceCommaWithPoint($price);

		if (isset($history['price']) && $history['price'] == $price) {
			// Do nothing
		} else if (isset($history['date']) && isset($history['id'])) {

			$dateDb2 	= JHtml::_('date', $history['date'], 'Y-m-d');
			$dateNow2	= JHtml::_('date', $dateNow , 'Y-m-d');
			if ($dateDb2 == $dateNow2) {
				$query = ' UPDATE #__phocacart_product_price_history SET price = '.$db->quote($price)
						.' WHERE id = '.(int)$history['id'];
				$db->setQuery($query);
				$db->execute();
			} else {
				$query = ' INSERT INTO #__phocacart_product_price_history (product_id, date, price)'
					.' VALUES ('.(int)$productId.', NOW(), '.$db->quote($price).');';
				$db->setQuery($query);
				$db->execute();
			}
		} else if (empty($history)) {
			$query = ' INSERT INTO #__phocacart_product_price_history (product_id, date, price)'
					.' VALUES ('.(int)$productId.', NOW(), '.$db->quote($price).');';
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/*
	 * Difference to automatic way of adding history prices:
	 * custom - the same price can be used for more dates - we don't ask for latest date
	 * automatic - when the same price then is is not written to database - we ask for latest date so we can compare and detect
	 *             that the latest price is the same and in such case we don't add it to the database
	 */
	public static function storePriceHistoryCustomById($data, $productId) {

		$db 				= JFactory::getDBO();
		$notDeleteIds 		= array();


		if (!empty($data)) {

		    $i = 1;
			foreach ($data as $k => $v) {


				if (isset($v['date']) && isset($v['price']) && (float)$v['price'] > 0) {
					$date		= JFactory::getDate($v['date']);
					$dateDb 	= $date->toSql();

					$query = 'SELECT a.id'
					.' FROM #__phocacart_product_price_history AS a'
					.' WHERE a.product_id = '.(int) $productId
					.' AND DATE_FORMAT(a.date, \'%Y-%m-%d\') = DATE_FORMAT('.$db->quote($dateDb).', \'%Y-%m-%d\')'
					.' ORDER BY a.id';
					$db->setQuery($query);
					$history = $db->loadAssocList();

					// Remove duplicates
					if (!empty($history)) {
						foreach($history as $k2 => $v2) {
							if ((int)$k2 > 0 && (int)$v2['id'] > 0) {
								$query = ' DELETE '
								.' FROM #__phocacart_product_price_history'
								.' WHERE product_id = '. (int)$productId
								.' AND id = '.(int)$v2['id'];
								$db->setQuery($query);
								$db->execute();
							}
						}
					}

					if (isset($history[0]['id']) && (int)$history[0]['id']) {
						$query = ' UPDATE #__phocacart_product_price_history SET price = '.$db->quote($v['price']) . ', ordering = '.$i
						.' WHERE id = '.(int)$history[0]['id'];
						$db->setQuery($query);
						$db->execute();

						$i++;
						$notDeleteIds[]	= (int)$history[0]['id'];

					} else {

						$query = ' INSERT INTO #__phocacart_product_price_history (product_id, date, price, ordering)'
							.' VALUES ('.(int)$productId.', '.$db->quote($dateDb).', '.$db->quote($v['price']).', '.$i.');';
						$db->setQuery($query);
						$db->execute();

						$i++;
						$newIdA 		= $db->insertid();
						$notDeleteIds[]	= (int)$newIdA;
					}
				}

			}


			// Remove all ids except the active
			if (!empty($notDeleteIds)) {
				$notDeleteIdsString = implode($notDeleteIds, ',');
				$query = ' DELETE '
						.' FROM #__phocacart_product_price_history'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteIdsString.')';

			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_price_history'
						.' WHERE product_id = '. (int)$productId;
			}
			$db->setQuery($query);
			$db->execute();



			return true;
		} else {

		    $query = ' DELETE '
						.' FROM #__phocacart_product_price_history'
						.' WHERE product_id = '. (int)$productId;
		    $db->setQuery($query);
			$db->execute();

			return true;
        }
		return false;
	}

	public static function getPriceHistoryById($productId, $limit = 10, $admin = 0) {

		$date		= JFactory::getDate();
		$dateNow 	= $date->toSql();
		$db 		= JFactory::getDBO();


		$query = 'SELECT a.id, a.product_id, a.price, a.date'
				.' FROM #__phocacart_product_price_history AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.date DESC';// set latest e.g. 10 items
		if ((int)$limit > 0 ) {
			$query .= ' LIMIT '.(int)$limit;
		}

		$db->setQuery($query);
		$history = $db->loadAssocList();

		if ($admin == 1) {
			return $history;
		}

		// We need to get the outcome from latest to history so we get the e.g. latest 10 items
		// but for displaying we need to start from start
		$history = array_reverse($history);



		$query = 'SELECT a.price'
				.' FROM #__phocacart_products AS a'
			    .' WHERE a.id = '.(int) $productId
				.' ORDER BY a.id';

		$db->setQuery($query);
		$todayDb = $db->loadAssoc();
		$today	= array();
		if (isset($todayDb['price']) && $todayDb['price'] > 0) {
			$today[0]['id'] 	= 0;
			$today[0]['date']	= $dateNow;
			$today[0]['price']	= $todayDb['price'];

		}

		// Join today with price history
		$c = count($history);
		$c--;
		if (isset($history[$c]['date'])) {
			$dateDb2 	= JHtml::_('date', $history[$c]['date'], 'Y-m-d');
			$dateNow2	= JHtml::_('date', $dateNow , 'Y-m-d');
			// Date in price history is the same like today's price, so take today's price
			if ($dateDb2 == $dateNow2 && isset($history[$c])) {
				unset($history[$c]);
			}
		}

		if (!empty($history) && !empty($today)) {
			$new = array_merge($history, $today);
		} else if (empty($history) && !empty($today)) {
			$new = $today;
		} else if (!empty($history) && empty($today)) {
			$new = $history;
		} else {
			$new = array();
		}

		// correct the count of items in case the current date was added to the prict history list
		$c2 = count($new);
		if ($c2 > $limit && isset($new[0])) {
			unset($new[0]);
		}

		return $new;
	}


	public static function getPriceHistoryChartById($productId) {

		$data = array();
		$dataX = $dataY = array();
		$history = self::getPriceHistoryById($productId);

		if (!empty($history)) {
			foreach($history as $k => $v) {

				$dataY[] = '\'' . $v['price'] . '\'';
				$dataX[] = '\'' . JHtml::_('date', $v['date'] , JText::_('DATE_FORMAT_LC3')) .'\'';
			}
		}

		$data['x'] = '';
		if (!empty($dataX)) {
			$data['x'] = implode(',', $dataX);
		}
		$data['y'] = '';
		if (!empty($dataY)) {
			$data['y'] = implode(',', $dataY);
		}

		if (!empty($data['x']) && !empty($data['y'])) {
			$s = new PhocacartStatistics();

			$s->renderChartJsLine2('phChartAreaLine', $data['y'], JText::_('COM_PHOCACART_PRICE'), $data['x']);
			$s->setFunction('phChartAreaLine', 'Line');
			$s->renderFunctions();
			return true;
		}
		return false;
	}

}
