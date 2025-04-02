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
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartOrdering
{
	public static function getOrdering($ordering, $type = 0, bool $usei18n = false): array {
		$usei18n = $usei18n && I18nHelper::useI18n();
		switch ($type) {
			case 1:// CATEGORY
				switch ((int)$ordering) {
					case 2: return ['c.ordering DESC'];
					case 3: return ['c.title ASC'];
					case 4: return ['c.title DESC'];
					case 5: return ['c.date ASC'];
					case 6: return ['c.date DESC'];
					case 7: return ['c.count_products ASC'];
					case 8: return ['c.count_products DESC'];
					case 99: return ['RAND()'];
					case 1: default:  return ['c.ordering ASC'];
				}

			case 2:// ORDERS
				switch ((int)$ordering) {
					case 2: return ['o.ordering DESC'];
					case 3: return ['o.title ASC'];
					case 4: return ['o.title DESC'];
					case 5: return ['o.price ASC'];
					case 6: return ['o.price DESC'];
					case 7: return ['o.date ASC'];
					case 8: return ['o.date DESC'];
					case 1: default: return ['o.ordering ASC'];
				}

			case 3:// TAGS
				switch ((int)$ordering) {
					case 2: return ['t.ordering DESC'];
					case 3: return ['t.title ASC'];
					case 4: return ['t.title DESC'];
					case 5: return ['t.id ASC'];
					case 6: return ['t.id DESC'];
					case 7: return ['t.count_products ASC'];
					case 8: return ['t.count_products DESC'];
					case 99: return ['RAND()'];
					case 1: default: return ['t.ordering ASC'];
				}

			case 4:// MANUFACTURERS
				switch ((int)$ordering) {
					case 2: return ['m.ordering DESC'];
					case 3: return ['m.title ASC'];
					case 4: return ['m.title DESC'];
					case 5: return ['m.id ASC'];
					case 6: return ['m.id DESC'];
					case 7: return ['m.count_products ASC'];
					case 8: return ['m.count_products DESC'];
					case 99: return ['RAND()'];
					case 1: default: return ['m.ordering ASC'];
				}

			case 5:// ATTRIBUTES
				switch ((int)$ordering) {
					case 2: return ['at.id DESC'];
					case 3: return ['at.title ASC', 'v.title ASC'];
					case 4: return ['at.title DESC', 'v.title DESC'];
					case 5: return ['v.id ASC'];
					case 6: return ['v.id DESC'];
					case 7: return ['at.ordering', 'v.ordering ASC'];
					case 8: return ['at.ordering', 'v.ordering DESC'];
					case 1: default: return ['at.id ASC'];
				}

			case 6:// SPECIFICATION
				switch ((int)$ordering) {
					case 2: return ['s.id DESC'];
					case 3: return ['s.title ASC', 's.value ASC'];
					case 4: return ['s.title DESC', 's.value DESC'];
					case 1: default: return ['s.id ASC'];
				}

			case 7:// CUSTOMERS (POS)
				switch ((int)$ordering) {
					case 2: return ['a.name DESC'];
					case 1: default: return ['a.name ASC'];
				}

			case 8:// UNITS (POS)
				switch ((int)$ordering) {
					case 2: return ['a.title DESC'];
					case 1:default: return ['a.title ASC'];
				}

			case 9:// SHIPPING METHODS (POS)
				switch ((int)$ordering) {
					case 2: return ['a.title DESC'];
					case 99: return ['RAND()'];
					case 1:default: return ['a.title ASC'];
				}

			case 10:// PAYMENT METHODS (POS)
				switch ((int)$ordering) {
					case 2: return ['a.title DESC'];
					case 99: return ['RAND()'];
					case 1: default: return ['a.title ASC'];
				}

			case 11:// PAYMENT METHODS (POS)
				switch ((int)$ordering) {
					case 3: return ['a.id ASC'];
					case 4: return ['a.id DESC'];
					case 1: return ['a.date ASC'];
					case 99: return ['RAND()'];
					case 2:default: return ['a.date DESC'];
				}

			case 12:// PARAMETERS
				switch ((int)$ordering) {
					case 2: return ['pp.ordering DESC'];
					case 3: return ['pp.title ASC'];
					case 4: return ['pp.title DESC'];
					case 5: return ['pp.id ASC'];
					case 6: return ['pp.id DESC'];
					case 99: return ['RAND()'];
					case 1: default: return ['pp.ordering ASC'];
				}

			case 13:// PARAMETER VALUES
				switch ((int)$ordering) {
					case 2: return ['pv.ordering DESC'];
					case 3: return $usei18n ? ['coalecse(i18n_pv.title, pv.title) ASC'] : ['pv.title ASC'];
					case 4: return $usei18n ? ['coalecse(i18n_pv.title, pv.title) DESC'] : ['pv.title DESC'];
					case 5: return ['pv.id ASC'];
					case 6: return ['pv.id DESC'];
					case 7: return ['pv.count_products ASC'];
					case 8: return ['pv.count_products DESC'];
					case 99: return ['RAND()'];
					case 1:default: return ['pv.ordering ASC'];
				}

			default://PRODUCTS
				switch ((int)$ordering) {
					// in items view, this orders by category id even no category is selected
					//case 2: return ['c.id', 'pc.ordering DESC'];
					case 2: return ['pc.ordering DESC'];
					case 3: return $usei18n ? ['coalesce(i18n_a.title, a.title) ASC'] : ['a.title ASC'];
					case 4: return ['a.title DESC'];
					case 5: return ['a.price ASC'];
					case 6: return ['a.price DESC'];
					case 7: return ['a.date ASC'];
					case 8: return ['a.date DESC'];
					case 21: return ['a.date_update ASC'];
					case 22: return ['a.date_update DESC'];
					case 9: return ['rating ASC'];
					case 10: return ['rating DESC'];


					case 11: return ['a.id ASC'];
					case 12: return ['a.id DESC'];
					case 13: return ['a.sales ASC'];
					case 14: return ['a.sales DESC'];
					case 15: return ['a.hits ASC'];
					case 16: return ['a.hits DESC'];
					case 17: return ['ah.hits ASC'];
					case 18: return ['ah.hits DESC'];

					case 19: return ['a.sku ASC'];
					case 20: return ['a.sku DESC'];

					case 23: return ['a.stock ASC'];
					case 24: return ['a.stock DESC'];
					case 25: return ['a.featured ASC'];
					case 26: return ['a.featured DESC'];

					case 99: return ['RAND()'];
					// in items view, this orders by category id even no category is selected
					//case 1: default: return ['c.id', 'pc.ordering ASC'];
					case 1: default: return ['pc.ordering ASC'];
				}
		}
	}

	public static function getOrderingText($ordering, $type = 0, bool $usei18n = false) {
		return implode(', ', self::getOrdering($ordering, $type, $usei18n));
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
