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
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class PhocacartFields
{
  public static function getAllFields(): array
  {
    static $fields = null;
    if ($fields === null) {
      $fields = FieldsHelper::getFields('com_phocacart.phocacartitem');
    }
    return $fields;
  }

	public static function getAllFieldsValues($fieldId, $onlyAvailableProducts = 0, $lang = '', $filterProducts = [])
	{
		$wheres = [];
		$lefts	= [];
		$db 		= Factory::getDbo();

		$wheres[]	= 'fv.field_id = ' . (int)$fieldId;

		$productTableAdded = 0;
		if ($onlyAvailableProducts) {
			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' #__phocacart_products AS p ON fv.item_id = p.id';
			$productTableAdded = 1;
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
		}

		if (!empty($filterProducts)) {
			$productIds = implode (',', $filterProducts);
			$wheres[]	= 'p.id IN ('.$productIds.')';
			if (!$productTableAdded) {
				$lefts[] = ' #__phocacart_products AS p ON fv.item_id = p.id';
			}
		}

		$q = ' SELECT DISTINCT fv.value as title, fv.value as alias'
			.' FROM  #__fields_values AS fv'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			.' ORDER BY fv.value';

		$db->setQuery($q);

		$items = $db->loadObjectList();

		return $items;
	}

}

