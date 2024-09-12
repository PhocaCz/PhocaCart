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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
class PhocacartOrdering
{
	public static function getOrderingText ($ordering, $type = 0) {

		switch ($type) {
			case 1:// CATEGORY
				switch ((int)$ordering) {
					case 2: $orderingOutput	= 'c.ordering DESC';break;
					case 3: $orderingOutput	= 'c.title ASC'; break;
					case 4:$orderingOutput	= 'c.title DESC';break;
					case 5:$orderingOutput	= 'c.date ASC';break;
					case 6:$orderingOutput	= 'c.date DESC';break;
					case 7:$orderingOutput	= 'c.count_products ASC';break;
					case 8:$orderingOutput	= 'c.count_products DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1: default: $orderingOutput = 'c.ordering ASC'; break;
				}
			break;

			case 2:// ORDERS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'o.ordering DESC';break;
					case 3:$orderingOutput	= 'o.title ASC';break;
					case 4:$orderingOutput	= 'o.title DESC';break;
					case 5:$orderingOutput	= 'o.price ASC';break;
					case 6:$orderingOutput	= 'o.price DESC';break;
					case 7:$orderingOutput	= 'o.date ASC';break;
					case 8:$orderingOutput	= 'o.date DESC';break;
					case 1:default:$orderingOutput = 'o.ordering ASC';break;
				}
			break;

			case 3:// TAGS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 't.ordering DESC';break;
					case 3:$orderingOutput	= 't.title ASC';break;
					case 4:$orderingOutput	= 't.title DESC';break;
					case 5:$orderingOutput	= 't.id ASC';break;
					case 6:$orderingOutput	= 't.id DESC';break;
					case 7:$orderingOutput	= 't.count_products ASC';break;
					case 8:$orderingOutput	= 't.count_products DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 't.ordering ASC';break;
				}
			break;

			case 4:// MANUFACTURERS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'm.ordering DESC';break;
					case 3:$orderingOutput	= 'm.title ASC';break;
					case 4:$orderingOutput	= 'm.title DESC';break;
					case 5:$orderingOutput	= 'm.id ASC';break;
					case 6:$orderingOutput	= 'm.id DESC';break;
					case 7:$orderingOutput	= 'm.count_products ASC';break;
					case 8:$orderingOutput	= 'm.count_products DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'm.ordering ASC';break;
				}
			break;

			case 5:// ATTRIBUTES
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'at.id DESC';break;
					case 3:$orderingOutput	= 'at.title ASC, v.title ASC';break;
					case 4:$orderingOutput	= 'at.title DESC, v.title DESC';break;
					case 5:$orderingOutput	= 'v.id ASC';break;
					case 6:$orderingOutput	= 'v.id DESC';break;
					case 7:$orderingOutput	= 'at.ordering, v.ordering ASC';break;
					case 8:$orderingOutput	= 'at.ordering, v.ordering DESC';break;
					case 1:default:$orderingOutput = 'at.id ASC';break;
				}
			break;

			case 6:// SPECIFICATION
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 's.id DESC';break;
					case 3:$orderingOutput	= 's.title ASC, s.value ASC';break;
					case 4:$orderingOutput	= 's.title DESC, s.value DESC';break;
					case 1:default:$orderingOutput = 's.id ASC';break;
				}
			break;

			case 7:// CUSTOMERS (POS)
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'a.name DESC';break;
					case 1:default:$orderingOutput = 'a.name ASC';break;
				}
			break;

			case 8:// UNITS (POS)
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'a.title DESC';break;
					case 1:default:$orderingOutput = 'a.title ASC';break;
				}
			break;

			case 9:// SHIPPING METHODS (POS)
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'a.title DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'a.title ASC';break;
				}
			break;

			case 10:// PAYMENT METHODS (POS)
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'a.title DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'a.title ASC';break;
				}
			break;

			case 11:// PAYMENT METHODS (POS)
				switch ((int)$ordering) {
					case 3:$orderingOutput	= 'a.id ASC';break;
					case 3:$orderingOutput	= 'a.id DESC';break;
					case 1:$orderingOutput	= 'a.date ASC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 2:default:$orderingOutput = 'a.date DESC';break;
				}
			break;

			case 12:// PARAMETERS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'pp.ordering DESC';break;
					case 3:$orderingOutput	= 'pp.title ASC';break;
					case 4:$orderingOutput	= 'pp.title DESC';break;
					case 5:$orderingOutput	= 'pp.id ASC';break;
					case 6:$orderingOutput	= 'pp.id DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'pp.ordering ASC';break;
				}
			break;

			case 13:// PARAMETER VALUES
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'pv.ordering DESC';break;
					case 3:$orderingOutput	= 'pv.title ASC';break;
					case 4:$orderingOutput	= 'pv.title DESC';break;
					case 5:$orderingOutput	= 'pv.id ASC';break;
					case 6:$orderingOutput	= 'pv.id DESC';break;
					case 7:$orderingOutput	= 'pv.count_products ASC';break;
					case 8:$orderingOutput	= 'pv.count_products DESC';break;
					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'pv.ordering ASC';break;
				}
			break;

			default://PRODUCTS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'c.id, pc.ordering DESC';break;
					case 3:$orderingOutput	= 'a.title ASC';break;
					case 4:$orderingOutput	= 'a.title DESC';break;
					case 5:$orderingOutput	= 'a.price ASC';break;
					case 6:$orderingOutput	= 'a.price DESC';break;
					case 7:$orderingOutput	= 'a.date ASC';break;
					case 8:$orderingOutput	= 'a.date DESC';break;
					case 21:$orderingOutput	= 'a.date_update ASC';break;
					case 22:$orderingOutput	= 'a.date_update DESC';break;
					case 9:$orderingOutput	= 'rating ASC';break;
					case 10:$orderingOutput	= 'rating DESC';break;


					case 11:$orderingOutput = 'a.id ASC';break;
					case 12:$orderingOutput = 'a.id DESC';break;
					case 13:$orderingOutput = 'a.sales ASC';break;
					case 14:$orderingOutput = 'a.sales DESC';break;
					case 15:$orderingOutput = 'a.hits ASC';break;
					case 16:$orderingOutput = 'a.hits DESC';break;
					case 17:$orderingOutput = 'ah.hits ASC';break;
					case 18:$orderingOutput = 'ah.hits DESC';break;

					case 19:$orderingOutput = 'a.sku ASC';break;
					case 20:$orderingOutput = 'a.sku DESC';break;

					case 23:$orderingOutput	= 'a.stock ASC';break;
					case 24:$orderingOutput	= 'a.stock DESC';break;
					case 25:$orderingOutput	= 'a.featured ASC';break;
					case 26:$orderingOutput	= 'a.featured DESC';break;

					case 99:$orderingOutput	= 'RAND()';break;
					case 1:default:$orderingOutput = 'c.id, pc.ordering ASC';break;
				}
			break;
		}
		return $orderingOutput;
	}

	public static function renderOrderingFront( $selected, $type = 0) {

		switch($type) {
			case 1:
				$typeOrdering 	= self::getOrderingCategoryArray();
				$ordering		= 'catordering';
			break;

			// POS Customers
			case 7:
				$typeOrdering 	= self::getOrderingUserArray();
				$ordering		= 'itemordering';// we use one view for items,customers,shipping,payment, ...
			break;

			// POS Units
		/*	case 8:
				$typeOrdering 	= self::getOrderingUserArray();
				$ordering		= 'itemordering';// we use one view for items,customers,shipping,payment, ...
			break; */

			// POS Shipping methods
			case 9:
				$typeOrdering 	= self::getOrderingShippingMethodArray();
				$ordering		= 'itemordering';// we use one view for items,customers,shipping,payment, ...
			break;

			// POS Payment methods
			case 10:
				$typeOrdering 	= self::getOrderingPaymentMethodArray();
				$ordering		= 'itemordering';// we use one view for items,customers,shipping,payment, ...
			break;

			// POS Orders
			case 11:
				$typeOrdering 	= self::getOrderingOrdersArray();
				$ordering		= 'itemordering';// we use one view for items,customers,shipping,payment, ...
			break;

			default:
				$typeOrdering 	= self::getOrderingItemArray(1);
				$ordering		= 'itemordering';
			break;
		}

		$s = PhocacartRenderStyle::getStyles();

		$html 	= HTMLHelper::_('select.genericlist',  $typeOrdering, $ordering, 'class="'.$s['c']['inputbox.form-select'].'" size="1" onchange="phEventChangeFormPagination(this.form, this)"', 'value', 'text', $selected, $ordering);

		return $html;
	}

	public static function getOrderingItemArray($frontend = 0) {

		$paramsC 					= PhocacartUtils::getComponentParameters();

		if ($frontend == 1) {
			$ordering_asc_desc_arrows 	= $paramsC->get('ordering_asc_desc_arrows', 0);
			$item_ordering_values 		= $paramsC->get('item_ordering_values', '1,2,3,4,5,6,7,8,21,22,9,10,19,20');
		} else {
			$ordering_asc_desc_arrows 	= 0;
			$item_ordering_values 		= '1,2,3,4,5,6,7,8,21,22,9,10,19,20';
		}

		if ($ordering_asc_desc_arrows == 1) {
			$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_ORDERING') . " &nbsp;" . "&#8679;",
				2 => Text::_('COM_PHOCACART_ORDERING') . " &nbsp;" .  "&#8681;",
				3 => Text::_('COM_PHOCACART_TITLE'). " &nbsp;" .  "&#8679;",
				4 => Text::_('COM_PHOCACART_TITLE'). " &nbsp;" .  "&#8681;",
				5 => Text::_('COM_PHOCACART_PRICE'). " &nbsp;" .  "&#8679;",
				6 => Text::_('COM_PHOCACART_PRICE'). " &nbsp;" .  "&#8681;",
				7 => Text::_('COM_PHOCACART_DATE_ADDED'). " &nbsp;" .  "&#8679;",
				8 => Text::_('COM_PHOCACART_DATE_ADDED'). " &nbsp;" .  "&#8681;",
				21 => Text::_('COM_PHOCACART_DATE_UPDATED'). " &nbsp;" .  "&#8679;",
				22 => Text::_('COM_PHOCACART_DATE_UPDATED'). " &nbsp;" .  "&#8681;",
				9 => Text::_('COM_PHOCACART_RATING'). " &nbsp;" .  "&#8679;",
				10 => Text::_('COM_PHOCACART_RATING'). " &nbsp;" .  "&#8681;",
				19 => Text::_('COM_PHOCACART_SKU'). " &nbsp;" .  "&#8679;",
				20 => Text::_('COM_PHOCACART_SKU'). " &nbsp;" .  "&#8681;",
				13 => Text::_('COM_PHOCACART_MOST_POPULAR'). " &nbsp;" .  "&#8679;",
				14 => Text::_('COM_PHOCACART_MOST_POPULAR'). " &nbsp;" .  "&#8681;",
				15 => Text::_('COM_PHOCACART_MOST_VIEWED'). " &nbsp;" .  "&#8679;",
				16 => Text::_('COM_PHOCACART_MOST_VIEWED'). " &nbsp;" .  "&#8681;",
				23 => Text::_('COM_PHOCACART_STOCK'). " &nbsp;" .  "&#8679;",
				24 => Text::_('COM_PHOCACART_STOCK'). " &nbsp;" .  "&#8681;",
				25 => Text::_('COM_PHOCACART_FEATURED'). " &nbsp;" .  "&#8679;",
				26 => Text::_('COM_PHOCACART_FEATURED'). " &nbsp;" .  "&#8681;"
			);

		} else {
			$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_ORDERING_ASC'),
				2 => Text::_('COM_PHOCACART_ORDERING_DESC'),
				3 => Text::_('COM_PHOCACART_TITLE_ASC'),
				4 => Text::_('COM_PHOCACART_TITLE_DESC'),
				5 => Text::_('COM_PHOCACART_PRICE_ASC'),
				6 => Text::_('COM_PHOCACART_PRICE_DESC'),
				7 => Text::_('COM_PHOCACART_DATE_ADDED_ASC'),
				8 => Text::_('COM_PHOCACART_DATE_ADDED_DESC'),
				21 => Text::_('COM_PHOCACART_DATE_UPDATED_ASC'),
				22 => Text::_('COM_PHOCACART_DATE_UPDATED_DESC'),
				9 => Text::_('COM_PHOCACART_RATING_ASC'),
				10 => Text::_('COM_PHOCACART_RATING_DESC'),
				19 => Text::_('COM_PHOCACART_SKU_ASC'),
				20 => Text::_('COM_PHOCACART_SKU_DESC'),
				13 => Text::_('COM_PHOCACART_MOST_POPULAR_ASC'),
				14 => Text::_('COM_PHOCACART_MOST_POPULAR_DESC'),
				15 => Text::_('COM_PHOCACART_MOST_VIEWED_ASC'),
				16 => Text::_('COM_PHOCACART_MOST_VIEWED_DESC'),
				23 => Text::_('COM_PHOCACART_STOCK_ASC'),
				24 => Text::_('COM_PHOCACART_STOCK_DESC'),
				25 => Text::_('COM_PHOCACART_FEATURED_ASC'),
				26 => Text::_('COM_PHOCACART_FEATURED_DESC')
			);
		}

		$itemOrderingValuesA = explode(',', $item_ordering_values);

		//$itemOrdering = array_intersect_key($itemOrdering, $itemOrderingValues);
		$validItemOrdering = array();
		foreach ($itemOrderingValuesA as $k => $v) {
			if (isset($itemOrdering[$v])) {
				$validItemOrdering[$v] = $itemOrdering[$v];
			}
		}

		return $validItemOrdering;
	}

	public static function getOrderingUserArray() {
		$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_NAME_ASC'),
				2 => Text::_('COM_PHOCACART_NAME_DESC'));
		return $itemOrdering;
	}

	public static function getOrderingShippingMethodArray() {
		$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_TITLE_ASC'),
				2 => Text::_('COM_PHOCACART_TITLE_DESC'));
		return $itemOrdering;
	}

	public static function getOrderingPaymentMethodArray() {
		$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_TITLE_ASC'),
				2 => Text::_('COM_PHOCACART_TITLE_DESC'));
		return $itemOrdering;
	}

	public static function getOrderingOrdersArray() {
		$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_DATE_ASC'),
				2 => Text::_('COM_PHOCACART_DATE_DESC'),
				3 => Text::_('COM_PHOCACART_ID_ASC'),
				4 => Text::_('COM_PHOCACART_ID_DESC'));
		return $itemOrdering;
	}

	/*public static function getOrderingCategoryArray() {
		$itemOrdering	= array(
				1 => Text::_('COM_PHOCACART_ORDERING_ASC'),
				2 => Text::_('COM_PHOCACART_ORDERING_DESC'),
				3 => Text::_('COM_PHOCACART_TITLE_ASC'),
				4 => Text::_('COM_PHOCACART_TITLE_DESC'),
				5 => Text::_('COM_PHOCACART_DATE_ASC'),
				6 => Text::_('COM_PHOCACART_DATE_DESC'),
				//7 => Text::_('COM_PHOCACART_ID_ASC'),
				//8 => Text::_('COM_PHOCACART_ID_DESC'),
				11 => Text::_('COM_PHOCACART_COUNT_ASC'),
				12 => Text::_('COM_PHOCACART_COUNT_DESC'),
				13 => Text::_('COM_PHOCACART_AVERAGE_ASC'),
				14 => Text::_('COM_PHOCACART_AVERAGE_DESC'),
				15 => Text::_('COM_PHOCACART_HITS_ASC'),
				16 => Text::_('COM_PHOCACART_HITS_DESC'));
		return $itemOrdering;
	}*/


	public static function getOrderingCombination($orderingItem = 0, $orderingCat = 0) {

		$itemOrdering	= '';
		$catOrdering	= '';
		$ordering		= '';

		if ($orderingItem > 0) {
			$itemOrdering 	= PhocacartOrdering::getOrderingText($orderingItem,0);

		}
		if ($orderingCat > 0) {
			$catOrdering 	= PhocacartOrdering::getOrderingText($orderingCat,1);
		}

		if ($catOrdering != '' && $itemOrdering == '') {
			$ordering = $catOrdering;
		}
		if ($catOrdering == '' && $itemOrdering != '') {
			$ordering = $itemOrdering;
		}

		if ($catOrdering != '' && $itemOrdering != '') {
			$ordering = $catOrdering . ', '.$itemOrdering;
		}
		return $ordering;

	}
}
?>
