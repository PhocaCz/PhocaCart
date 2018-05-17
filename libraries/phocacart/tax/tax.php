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

class PhocacartTax
{
	// All taxes by country
	public static function getTaxesByCountry($countryId) {
	
		
		if ((int)$countryId > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT t.id, t.title, t.ordering, t.tax_rate, tc.id as tcr_id, tc.title as tcr_title, tc.alias as tcr_alias, tc.tax_rate as tcr_tax_rate'
			. ' FROM #__phocacart_taxes as t'
			. ' LEFT JOIN #__phocacart_tax_countries AS tc ON tc.tax_id = t.id AND tc.country_id = '.(int)$countryId
			//. ' WHERE tc.country_id = '.(int)$id
			. ' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$items = $db->loadObjectList();
			
			return $items;
		}
	}
	
	// All taxes by region
	public static function getTaxesByRegion($regionId) {
	
		
		if ((int)$regionId > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT t.id, t.title, t.ordering, t.tax_rate, tr.id as tcr_id, tr.title as tcr_title, tr.alias as tcr_alias, tr.tax_rate as tcr_tax_rate'
			. ' FROM #__phocacart_taxes as t'
			. ' LEFT JOIN #__phocacart_tax_regions AS tr ON tr.tax_id = t.id AND tr.region_id = '.(int)$regionId
			//. ' WHERE tc.country_id = '.(int)$id
			. ' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$items = $db->loadObjectList();
			
			return $items;
		}
	}
	
	// Selected tax by country
	public static function getTaxByCountry($taxId) {
		
		$countryId 					= (int)self::getUserCountryId();
		$taxChangedA				= array();
		$taxChangedA['taxrate']		= 0;
		$taxChangedA['taxtitle']	= '';
		
		if ((int)$taxId > 0 && $countryId > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT tc.id, tc.title, tc.tax_rate'
			. ' FROM #__phocacart_tax_countries as tc'
			. ' WHERE tc.country_id = '.(int)$countryId
			. ' AND tc.tax_id = '.(int)$taxId
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$taxO = $db->loadObject();
			
			if (isset($taxO->tax_rate) && $taxO->tax_rate != '') {
				$taxChangedA['taxrate']	= $taxO->tax_rate;
			}
			
			if (isset($taxO->title) && $taxO->title != '') {
				$taxChangedA['taxtitle']	= $taxO->title;
			}
			
			if ($taxChangedA['taxrate'] > 0 && $taxChangedA['taxtitle'] != '') {
				return $taxChangedA;
			}

		}
		return false;
	}
	
	// Selected tax by country
	public static function getTaxByRegion($taxId) {
		
		$regionId 					= (int)self::getUserRegionId();
		$taxChangedA				= array();
		$taxChangedA['taxrate']		= 0;
		$taxChangedA['taxtitle']	= '';
		
		if ((int)$taxId > 0 && $regionId > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT tr.id, tr.title, tr.tax_rate'
			. ' FROM #__phocacart_tax_regions as tr'
			. ' WHERE tr.region_id = '.(int)$regionId
			. ' AND tr.tax_id = '.(int)$taxId
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$taxO = $db->loadObject();
			
			if (isset($taxO->tax_rate) && $taxO->tax_rate != '') {
				$taxChangedA['taxrate']	= $taxO->tax_rate;
			}
			
			if (isset($taxO->title) && $taxO->title != '') {
				$taxChangedA['taxtitle']	= $taxO->title;
			}
			
			if ($taxChangedA['taxrate'] > 0 && $taxChangedA['taxtitle'] != '') {
				return $taxChangedA;
			}

		}
		return false;
	}
	
	public static function changeTaxBasedOnRule($taxId, $tax, $taxCalculationType, $taxTitle) {
		
		$taxChangedA 				= array();
		$taxChangedA['taxrate']		= $tax;
		$taxChangedA['taxtitle']	= $taxTitle;
		
		$app						= JFactory::getApplication();
		//$paramsC 					= PhocacartUtils::getComponentParameters();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$dynamic_tax_rate			= $paramsC->get( 'dynamic_tax_rate', 0 );
		$dynamic_tax_rate_priority	= $paramsC->get( 'dynamic_tax_rate_priority', 1 );// country prioritized
		
		if ($dynamic_tax_rate == 0) {
			return $taxChangedA;
		}
		
		if ($dynamic_tax_rate_priority == 1) {
			// Country prioritized
			$taxChangedA = self::getTaxByCountry($taxId);
			//Not found - try to find region
			if (!$taxChangedA) {
				$taxChangedA = self::getTaxByRegion($taxId);
			}
		} else {
			// Region prioritized
			$taxChangedA = self::getTaxByRegion($taxId);
			//Not found - try to find country
			if (!$taxChangedA) {
				$taxChangedA = self::getTaxByCountry($taxId);
			}
		}

		// Nothing found - back to default:
		if (!$taxChangedA) {
			$taxChangedA['taxrate']		= $tax;
			$taxChangedA['taxtitle']	= $taxTitle;
		}
		return $taxChangedA;
	}
	
	public static function getUserCountryId() {
		
		// 1. We get information about country stored by user
		// 2. Possible improvement get country by IP (but possible problem)
		
		$user 				= PhocacartUser::getUser();
		$app				= JFactory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$dynamic_tax_rate	= $paramsC->get( 'dynamic_tax_rate', 0 );
		
		$type = 0;
		if ($dynamic_tax_rate == 1) {
			$type = 0;// BILLING
		} else if ($dynamic_tax_rate == 2) {
			$type = 1;// SHIPPING
		}
		
		if (isset($user->id) && (int)$user->id > 0 && (int)$dynamic_tax_rate > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT country'
			. ' FROM #__phocacart_users'
			. ' WHERE user_id = '.(int)$user->id
			. ' AND type = '.(int)$type
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$countryId = $db->loadResult();
			if ((int)$countryId > 0) {
				return $countryId;
			}
		}
		
		return 0;
	}
	
	public static function getUserRegionId() {
		
		// 1. We get information about country stored by user
		// 2. Possible improvement get country by IP (but possible problem)
		
		$user 				= PhocacartUser::getUser();
		$app				= JFactory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$dynamic_tax_rate	= $paramsC->get( 'dynamic_tax_rate', 0 );
		
		$type = 0;
		if ($dynamic_tax_rate == 1) {
			$type = 0;// BILLING
		} else if ($dynamic_tax_rate == 2) {
			$type = 1;// SHIPPING
		}
		
		if (isset($user->id) && (int)$user->id > 0 && (int)$dynamic_tax_rate > 0) {
			$db = JFactory::getDBO();
			$q = 'SELECT region'
			. ' FROM #__phocacart_users'
			. ' WHERE user_id = '.(int)$user->id
			. ' AND type = '.(int)$type
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$regionId = $db->loadResult();
			if ((int)$regionId > 0) {
				return $regionId;
			}
		}
		
		return 0;
	}
}