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
class PhocacartEdit
{
	public static function store($id, $value) {
		
		$idA = explode(':', $id);//table:column:id
		
		$table 	= '';// No direct access to table - this is why tables are listed here
		$column = '';// No direct access to column - this is why columns are listed here
		$allowedTables = array(
			'#__phocacart_products',
			'#__phocacart_currencies',
			'#__phocacart_taxes',
			'#__phocacart_coupons',
			'#__phocacart_discounts'
		);
		$allowedColumns = array(
			'price', 'price_original', 'sku', 'stock', 'exchange_rate', 'tax_rate', 'discount'
		);
		
		if (isset($idA[0])) {
			$tableTest = '#__phocacart_'.$idA[0];
			if (in_array($tableTest, $allowedTables)) {
				$table = $tableTest;
			}
		}

		if (isset($idA[1])) {
			$columnTest = $idA[1];
			if (in_array($columnTest, $allowedColumns)) {
				$column = $columnTest;
			}
		}
		
		switch($column) {
			
			case 'price':
			case 'price_original':
			case 'exchange_rate':
			case 'tax_rate':
			case 'discount':
				$value = PhocacartUtils::replaceCommaWithPoint($value);
				$value = (float)$value;
			break;
			case 'stock':
				$value = (int)$value;
			break;
			
		}
			
		if ($table != '' && $column != '' && isset($idA[2]) && (int)$idA[2] > 0) {
			
			$idRow = (int)$idA[2];
			
			$db	= JFactory::getDBO();
			$q	= 'UPDATE '.$table.' SET '.$db->quoteName($column).' = '.$db->quote($value).' WHERE id = '.(int)$idRow;
	
			$db->setQuery($q);
			$db->execute();
			return $value;
		}
		return false;
	}
}
?>