<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartCountry
{	
	public static function getCountryById($countryId) {
		
		$db =JFactory::getDBO();
		$query = 'SELECT title FROM #__phocacart_countries WHERE id = '.(int) $countryId. ' ORDER BY title LIMIT 1';
		$db->setQuery($query);
		$country = $db->loadColumn();
		if(isset($country[0])) {
			return (string)$country[0];
		}
		return '';
	}
	
	public static function options() {

		$db = JFactory::getDBO();
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_countries AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$items = $db->loadObjectList();
	
		return $items;
	}
	
	public static function getCountries($id, $select = 0, $table = 'shipping') {
	
		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_countries';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_countries';
			$c = 'payment_id';
		}
		
		$db =JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT c.country_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_countries AS a'
				.' LEFT JOIN '.$t.' AS c ON a.id = c.country_id'
			    .' WHERE c.'.$c.' = '.(int) $id
				.' ORDER BY a.id';
		$db->setQuery($query);
		
		if ($select == 1) {
			$countries = $db->loadColumn();
		} else {
			$countries = $db->loadObjectList();
		}	
	
		return $countries;
	}
	
	/*
	 * used for shipping method rules
	 * used for payment method rules
	 */
	
	public static function storeCountries($countriesArray, $id, $table = 'shipping') {
	

		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_countries';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_countries';
			$c = 'payment_id';
		}
	
		if ((int)$id > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();
			
			if (!empty($countriesArray)) {
				
				$values 		= array();
				$valuesString 	= '';
				
				foreach($countriesArray as $k => $v) {
					$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO '.$t.' ('.$c.', country_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	public static function getAllCountriesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {
	
		$db =JFactory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_countries AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$countries = $db->loadObjectList();
		
		$countriesO = JHTML::_('select.genericlist', $countries, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);
		
		return $countriesO;
	}
}
?>