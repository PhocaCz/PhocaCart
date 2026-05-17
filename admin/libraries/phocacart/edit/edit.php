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


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Table\Table;
class PhocacartEdit
{
	public static function store(&$options) {

		$user 		= Factory::getUser();
		$canCreate  = $user->authorise('core.create', 'com_phocacart');
        $canEdit    = $user->authorise('core.edit', 'com_phocacart');


        $paramsC = PhocacartUtils::getComponentParameters();
        $admin_eip_title = $paramsC->get('admin_eip_title', 4);


        if ($canCreate || $canEdit) {
		} else {
        	$options['msg'] = Text::_('COM_PHOCACART_NO_RIGHTS_EDIT_ITEMS');
        	return false;
		}


		$idA = explode(':', $options['id']);//table:column:id

		$tableDb 	= '';// No direct access to table - this is why tables are listed here
		$tableDbName = '';
		$column = '';// No direct access to column - this is why columns are listed here
		$allowedTables = array(
			'#__phocacart_products' => 'PhocaCartItem',
			'#__phocacart_currencies' => 'PhocacartCurrency',
			'#__phocacart_taxes' => 'PhocacartTax',
			'#__phocacart_coupons' => 'PhocacartCoupon',
			'#__phocacart_discounts' => 'PhocacartDiscount',
            '#__phocacart_payment_methods' => 'PhocacartPayment',
            '#__phocacart_shipping_methods' => 'PhocacartShipping'
		);
		$allowedColumns = array(
			'price', 'price_original', 'title', 'sku', 'hits', 'stock', 'exchange_rate', 'exchange_rate_reverse', 'tax_rate', 'discount', 'cost',

			'upc', 'ean', 'jan', 'isbn', 'mpn', 'serial_number', 'registration_key', 'external_id', 'external_key', 'external_link',
			'external_text', 'external_link2', 'external_text2', 'min_quantity', 'min_multiple_quantity', 'max_quantity', 'unit_amount', 'unit_unit',
			'length', 'width', 'height', 'weight', 'volume', 'points_needed', 'points_received', 'description', 'description_long', 'features',
			'video', 'type_feed', 'type_category_feed', 'metakey', 'metadesc','metatitle', 'special_parameter'
		);


		// Alias can be edited
		if ($admin_eip_title == 3 || $admin_eip_title == 4) {
			$allowedColumns[] = 'alias';
		}


		$requiredColumns = array(
			'title', 'alias'
		);
		/* This can be specified for different tables
		 * if ($tableDb == 'products') {
			$requiredColumns = array(
				'title', 'alias'
			);
		}*/


		if (isset($idA[0])) {
			$tableDbTest = '#__phocacart_'. PhocacartText::filterValue($idA[0], 'alphanumeric2');
			if (array_key_exists ($tableDbTest, $allowedTables)) {
				$tableDb = $tableDbTest;
				$tableDbName = $allowedTables[$tableDbTest];
			}
		}




		if (isset($idA[1])) {
			$columnTest = $idA[1];

			if (in_array($columnTest, $allowedColumns)) {
				$column = PhocacartText::filterValue($columnTest, 'alphanumeric2');
			}

			if (in_array($columnTest, $requiredColumns)) {
				if ($options['value'] == '') {
					$options['msg'] = Text::_('COM_PHOCACART_VALUE_CANNOT_BE_EMPTY');
        			return false;
				}

			}
		}


		switch($column) {

			case 'price':
			case 'price_original':
			case 'exchange_rate':
			case 'tax_rate':
			case 'discount':
            case 'cost':
				$options['value'] = PhocacartUtils::replaceCommaWithPoint($options['value']);
				$options['value'] = (float)$options['value'];
			break;
			case 'exchange_rate_reverse':
				$options['value'] = PhocacartUtils::replaceCommaWithPoint($options['value']);
				$options['value'] = (float)$options['value'];
				$options['value_result'] = $options['value'];
				if ($options['value'] != 0) {
					$options['value'] = 1 / $options['value'];
				}
				$column = 'exchange_rate';
				break;
			case 'stock':
				$options['value'] = (int)$options['value'];
			break;

			case 'title':
			case 'alias':
				$options['value'] = strip_tags($options['value']);
			break;

		}

		if ($tableDb == '') {
			$options['msg'] = Text::_('COM_PHOCACART_TABLE_EMPTY_OR_NOT_ALLOWED');
			return false;
		}

		if ($column == '') {
			$options['msg'] = Text::_('COM_PHOCACART_COLUMN_EMPTY_OR_NOT_ALLOWED');
			return false;
		}


		if ($tableDbName != '' && $tableDb != '' && $column != '' && isset($idA[2]) && (int)$idA[2] > 0) {

			$idRow = (int)$idA[2];


			// TEST CHECKOUT
			$user = Factory::getUser();

			// Get an instance of the row to checkout.
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/tables');
			$table = Table::getInstance($tableDbName, 'Table');

			if (!$table->load($idRow)) {
				$options['msg'] = $table->getError();
				//throw new RuntimeException($tableDb->getError());
				return false;
			}

			// Check if this is the user having previously checked out the row.
			if ($table->checked_out > 0 && $table->checked_out != $user->get('id')) {
				$options['msg'] = Text::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH');
				//throw new RuntimeException(Text::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
				return false;
			}

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $idRow)) {
				$options['msg'] = $table->getError();
				//throw new RuntimeException($tableDb->getError());
				return false;
			}

			// DATA
			$data = array();
			$db	= Factory::getDBO();
			$data[$column]  = $options['value'];

			if ($column == 'title') {
				// Update even alias it this is set in options
				// Alias can be overwritten by title
				if ($admin_eip_title == 2 || $admin_eip_title == 4) {

					$options['valuecombined'] = strip_tags(PhocacartUtils::getAliasName($options['value']));
					if (isset($idA[0])) {
						$options['idcombined'] = strip_tags($idA[0]) . ':alias:' . (int)$idRow;
					}
					$data['alias'] = $options['valuecombined'];
				}
			}

			// After saving the item will be free
			$data['checked_out'] = 0;
			$data['checked_out_time'] = '0000-00-00 00:00:00';

			if (!$table->bind($data)) {
				$options['msg'] = $table->getError();
				return false;
			}

			if (!$table->check()) {
				$options['msg'] = $table->getError();
				return false;
			}

			if (!$table->store()) {
				$options['msg'] = $table->getError();
				return false;
			}

			if (isset($options['value_result'])) {
				$options['value'] = $options['value_result'];
			}

			// Update product price history and product group price
			if ($tableDbName == 'PhocaCartItem' && $column == 'price') {

				// Update price history
				PhocacartPriceHistory::storePriceHistoryById((int)$idRow, $data['price']);
				// Update group price
				PhocacartGroup::updateGroupProductPriceById((int)$idRow, $data['price']);
			}
			/*
			$db	= Factory::getDBO();
			$q	= 'UPDATE '.$tableDb.' SET '.$db->quoteName($column).' = '.$db->quote($options['value']).' WHERE id = '.(int)$idRow;

			$db->setQuery($q);
			$db->execute();

			if ($column == 'title') {

				// Update even alias
				$column = 'alias';
				$options['valuecombined'] = strip_tags(PhocacartUtils::getAliasName($options['value']));
				if (isset($idA[0])) {
					$options['idcombined'] = strip_tags($idA[0]).':alias:' . (int)$idRow;
				}
				$q	= 'UPDATE '.$tableDb.' SET '.$db->quoteName($column).' = '.$db->quote($options['valuecombined']).',  WHERE id = '.(int)$idRow;

				$db->setQuery($q);
				$db->execute();
			}
			*/


			return true;
		} else {
			$options['msg'] = Text::_('COM_PHOCACART_TABLE_OR_COLUMN_EMPTY');
		}
		return false;
	}
}
?>
