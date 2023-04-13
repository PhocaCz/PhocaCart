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

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class PhocacartPriceBulkprice
{

	public static function getItem($id) {



		$db = Factory::getDBO();
		$query = 'SELECT a.* FROM #__phocacart_bulk_prices AS a WHERE a.id = '.(int)$id .' LIMIT 1';
		$db->setQuery( $query );
		$item = $db->loadObject();

		if (isset($item->id) && (int)$item->id > 0 && isset($item->params) && $item->params != '') {

		    $registry = new Registry;
		    $registry->loadString($item->params);
			$item->params = $registry;

			$categories = $item->params->get('catid_multiple', array());

			if (!empty($categories)) {
			    $categoriesString = implode(',', $categories);

			    $query = 'SELECT a.id, a.title FROM #__phocacart_categories AS a WHERE a.id IN ('.$categoriesString.')';
			    $db->setQuery( $query );
		        $item->categories = $db->loadObjectList();
		        $item->categories_string = $categoriesString;
            }

			$wheres		= array();
			if (!empty($categories)) {
				$wheres[]	= ' c.id IN ('.$categoriesString.')';
				$lefts = array();
        		$lefts[] = ' #__phocacart_product_categories AS pc ON pc.product_id = p.id';
        		$lefts[] = ' #__phocacart_categories AS c ON c.id = pc.category_id';
			}


			// RUN - we get info about how many products can be affected (from products table)

        	$query = 'SELECT COUNT(p.id) FROM #__phocacart_products AS p'

			. (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
            . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');


        	$db->setQuery($query);
        	$count = $db->loadResult();

        	$item->productcount = $count;

        	// REVERT - we get info how many products were changes and can be reverted (from price history table)
		}

		return $item;

	}

	public static function setNewPrice($productId, $price, $params) {

        $newPrice                   = $price;
	    $amount                     = $params->get('amount', '');
		$operator                   = $params->get('operator', '-');
		$calculation_type           = $params->get('calculation_type', 1);
		$calculation_rounding       = $params->get('calculation_rounding', 2);// Bulk Price options - no, 0, 1, 2 digits


		$pC                     = PhocacartUtils::getComponentParameters();
        $rounding_calculation   = $pC->get('rounding_calculation', 1);// Global Phoca Cart options - round half up, round half down


		if ($calculation_type == 0) {
		    // FIXED AMOUNT
            if ($operator == '+') {
                $newPrice = $price + $amount;
            } else {
                $newPrice = $price - $amount;
            }

        } else {
		    // PERCENTAGE
            if ($operator == '+') {
                $newPrice = $price + ($price * $amount / 100);
            } else {
                $newPrice = $price - ($price * $amount / 100);
            }
        }

		if ($calculation_rounding > -1) {
            $newPrice = round($newPrice, (int)$calculation_rounding, $rounding_calculation);
        }

		if ($newPrice != $price) {

		    // Set new price
            $db    = Factory::getDBO();
            $query = 'UPDATE #__phocacart_products SET price = ' . $db->quote($newPrice) . ' WHERE id = ' . (int)$productId;
            $db->setQuery($query);
            $db->execute();

            // Set price history
        }


		return $newPrice;


    }

    public static function setNewOriginalPrice($productId, $price_original, $price, $params) {


	    $db = Factory::getDBO();
	    $original_price_change_run                 = $params->get('original_price_change_run', 0);



		if ($original_price_change_run == 0) {

		    // No change
		    return $price_original;

        } else if ($original_price_change_run == 1) {

		    // Current price becomes new original price
		    $query = 'UPDATE #__phocacart_products SET price_original = '.$db->quote($price).' WHERE id = '.(int)$productId;
		    $db->setQuery($query);
            $db->execute();
            return $price;

        } else if ($original_price_change_run == 2) {

		    // Original price will be emptied
            $query = 'UPDATE #__phocacart_products SET price_original = \'0\' WHERE id = '.(int)$productId;
		    $db->setQuery($query);
            $db->execute();
            return '';

        }




		return $price_original;


    }



	public static function setRevertPrice($productId, $bulkId, $price, $params) {

        $newPrice   = $price;
        $db         = Factory::getDBO();
        $wheres 	= array();

        $wheres[]	= ' p.product_id = '.(int)$productId;
        $wheres[]	= ' p.bulk_id = '.(int)$bulkId;
        $wheres[]	= ' p.type = 2';

        $q = 'SELECT p.current_price';
        $q .= ' FROM #__phocacart_product_price_history AS p';
        $q .= ' WHERE ' . implode(' AND ', $wheres);
        $q .= ' ORDER BY p.id';
        $q .= ' LIMIT 1';

        $db->setQuery($q);
        $newPrice = $db->loadResult();

		if ($newPrice != $price) {

		    // Set new price
            $query = 'UPDATE #__phocacart_products SET price = ' . $db->quote($newPrice) . ' WHERE id = ' . (int)$productId;
            $db->setQuery($query);
            $db->execute();
        }

		return $newPrice;
    }

    public static function setRevertOriginalPrice($productId, $bulkId, $priceOriginal, $params) {

        $newPrice   = $priceOriginal;
        $db         = Factory::getDBO();
        $wheres 	= array();

        $wheres[]	= ' p.product_id = '.(int)$productId;
        $wheres[]	= ' p.bulk_id = '.(int)$bulkId;
        $wheres[]	= ' p.type = 2';

        $q = 'SELECT p.current_price_original';
        $q .= ' FROM #__phocacart_product_price_history AS p';
        $q .= ' WHERE ' . implode(' AND ', $wheres);
        $q .= ' ORDER BY p.id';
        $q .= ' LIMIT 1';

        $db->setQuery($q);
        $newPrice = $db->loadResult();


		if ($newPrice != $priceOriginal) {

		    // Set new price
            $query = 'UPDATE #__phocacart_products SET price_original = ' . $db->quote($newPrice) . ' WHERE id = ' . (int)$productId;
            $db->setQuery($query);
            $db->execute();
        }

		return $newPrice;
    }

    public static function setStatus($id, $status) {

		$db = Factory::getDBO();
		$query = 'UPDATE #__phocacart_bulk_prices SET status = '.(int)$status.' WHERE id = '.(int)$id;
		$db->setQuery($query);
        $db->execute();

	}

	public static function removePriceHistoryItem($id) {

		$db = Factory::getDBO();
		$query = ' DELETE '
        .' FROM #__phocacart_product_price_history'
        .' WHERE bulk_id = '. (int)$id
        .' AND type = 2';
        $db->setQuery($query);
        $db->execute();

	}

	public static function removePriceHistoryItems($cid) {

		$db = Factory::getDBO();

		ArrayHelper::toInteger($cid);
		$cids = implode( ',', $cid );

		$query = ' DELETE '
        .' FROM #__phocacart_product_price_history'
        .' WHERE bulk_id IN ( '. $cids.' )'
        .' AND type = 2';
        $db->setQuery($query);
        $db->execute();

	}
}
