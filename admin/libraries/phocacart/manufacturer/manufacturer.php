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
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartManufacturer
{


	public static function getAllManufacturers($ordering = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = array(), $limitCount = -1) {

		$db 			= Factory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 4);

		$wheres		= array();
		$lefts		= array();

		$columns		= 'm.id, m.image, m.description, m.count_products';
		/*$groupsFull		= $columns;
		$groupsFast		= 'm.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/

		$columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'm', '', '', ',');


		$wheres[]	= ' m.published = 1';

		if ($lang != '' && $lang != '*') {

			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('m.language', $lang);
		}

		$productTableAdded = 0;

		if ($onlyAvailableProducts == 1) {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' #__phocacart_products AS p ON m.id = p.manufacturer_id';
			$productTableAdded = 1;
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
		} else {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
				$lefts[] = ' #__phocacart_products AS p ON m.id = p.manufacturer_id';
				$productTableAdded = 1;
			}
		}

		if (!empty($filterProducts)) {
			$productIds = implode (',', $filterProducts);
			$wheres[]	= 'p.id IN ('.$productIds.')';
			if ($productTableAdded == 0) {
                $lefts[] = ' #__phocacart_products AS p ON m.id = p.manufacturer_id';
            }
		}

		if ((int)$limitCount > -1) {
		    $wheres[] = " m.count_products > ".(int)$limitCount;
		}

		$q = ' SELECT DISTINCT '.$columns
			.' FROM  #__phocacart_manufacturers AS m'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			//.' GROUP BY '.$groups
			.' ORDER BY '.$orderingText;

		$db->setQuery($q);

		$items = $db->loadObjectList();

		return $items;
	}

	public static function getManufacturers($itemId, $select = 0) {

		$db = Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT a.id';
		} else if ($select == 2){
			$query = 'SELECT a.id, a.alias ';
		} else {
			$query = 'SELECT a.id, a.title, a.alias';
		}
		$query .= ' FROM #__phocacart_manufacturers AS a'
				.' LEFT JOIN #__phocacart_products AS p ON a.id = p.manufacturer_id'
				.' WHERE p.id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);
		if ($select == 1) {
			$mans = $db->loadColumn();
		} else {
			$mans = $db->loadObjectList();
		}

		return $mans;
	}

	public static function getManufacturersByIds($cids) {

		$db = Factory::getDBO();
        if ($cids != '') {//cids is string separated by comma

            $query = 'SELECT a.id FROM #__phocacart_manufacturers AS a'
                . ' LEFT JOIN #__phocacart_products AS p ON a.id = p.manufacturer_id'
                . ' WHERE p.id IN (' . $cids . ')'
                . ' ORDER BY a.id';

            $db->setQuery($query);
            $tags = $db->loadColumn();
            $tags = array_unique($tags);

            return $tags;
        }
        return array();
	}

	public static function getManufacturerRendered($id, $title, $alias, $manufacturerAlias, $type = 1, $catId = 0, $catAlias = '') {
		if ($type == 1 && (int)$id > 0 && $title != '') {

			$link = PhocacartRoute::getItemsRoute();
			$link = $link . PhocacartRoute::getItemsRouteSuffix($manufacturerAlias, $id, $alias);
			return '<a href="'.Route::_($link).'" >'.$title.'</a>';
		} else {
			return $title;
		}
	}

	public static function getActiveManufacturers($items, $ordering, $manufacturerAlias = 'manufacturer') {

	    $db     = Factory::getDbo();
	    $o      = array();
        $wheres = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 4);//m
        if ($items != '') {
            $wheres[] = 'm.id IN (' . $items . ')';
            $q = 'SELECT DISTINCT '.I18nHelper::sqlCoalesce(['title'], 'm').', '
				.I18nHelper::sqlCoalesce(['alias'], 'm', '', 'concatid').', '
				.$db->quote($manufacturerAlias).' AS parameteralias, '.$db->quote(ucfirst($manufacturerAlias)).' AS parametertitle FROM #__phocacart_manufacturers AS m'
				. I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm')
				. (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
                . ' GROUP BY m.alias, m.title'
                . ' ORDER BY ' . $ordering;

            $db->setQuery($q);
            $o = $db->loadAssocList();

        }
        return $o;
    }

}
?>
