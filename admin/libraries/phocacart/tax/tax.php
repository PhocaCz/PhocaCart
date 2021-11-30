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

class PhocacartTax
{

	public static function getAllTaxes() {



			$db = Factory::getDBO();
			$q = 'SELECT t.id, t.title, t.tax_rate'
			. ' FROM #__phocacart_taxes as t'
			//. ' LEFT JOIN #__phocacart_tax_countries AS tc ON tc.tax_id = t.id AND tc.country_id = '.(int)$countryId
			//. ' WHERE tc.country_id = '.(int)$id
			. ' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$items = $db->loadAssocList('id');
			return $items;

	}

	public static function getAllTaxesIncludingCountryRegion() {

			// Tax key = IDTAX:IDCOUNTRYTAX:IDREGIONTAX
			$db = Factory::getDBO();
			$q 	= 'SELECT CONCAT_WS(\':\', t.id, 0, 0) as tkey, t.id, t.title, t.tax_rate, t.calculation_type FROM #__phocacart_taxes AS t ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$itemsT = $db->loadAssocList('tkey');

			$q 	= 'SELECT CONCAT_WS(\':\', tc.tax_id, tc.id, 0) as tkey, tc.id, tc.title, tc.tax_id, tc.tax_rate, t.calculation_type FROM #__phocacart_tax_countries AS tc'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = tc.tax_id'
				.' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$itemsC = $db->loadAssocList('tkey');

			$q 	= 'SELECT CONCAT_WS(\':\', tr.tax_id, 0, tr.id) as tkey, tr.id, tr.title, tr.tax_id, tr.tax_rate, t.calculation_type FROM #__phocacart_tax_regions AS tr'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = tr.tax_id'
				.' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$itemsR = $db->loadAssocList('tkey');

			$items = array_merge($itemsT, $itemsC, $itemsR);
			return $items;

	}
	// All taxes by country
	public static function getTaxesByCountry($countryId) {


		if ((int)$countryId > 0) {
			$db = Factory::getDBO();
			$q = 'SELECT t.id, t.title, t.ordering, t.tax_rate, tc.id as tcr_id, tc.title as tcr_title, tc.alias as tcr_alias, tc.tax_rate as tcr_tax_rate'
			. ' FROM #__phocacart_taxes as t'
			. ' LEFT JOIN #__phocacart_tax_countries AS tc ON tc.tax_id = t.id AND tc.country_id = '.(int)$countryId
			//. ' WHERE tc.country_id = '.(int)$id
			//. ' WHERE tc.tax_rate > -1'
			. ' ORDER BY t.ordering ASC';
			$db->setQuery($q) ;
			$items = $db->loadObjectList();

			return $items;
		}
	}

	// All taxes by region
	public static function getTaxesByRegion($regionId) {


		if ((int)$regionId > 0) {
			$db = Factory::getDBO();
			$q = 'SELECT t.id, t.title, t.ordering, t.tax_rate, tr.id as tcr_id, tr.title as tcr_title, tr.alias as tcr_alias, tr.tax_rate as tcr_tax_rate'
			. ' FROM #__phocacart_taxes as t'
			. ' LEFT JOIN #__phocacart_tax_regions AS tr ON tr.tax_id = t.id AND tr.region_id = '.(int)$regionId
			//. ' WHERE tc.country_id = '.(int)$id
			//. ' WHERE tr.tax_rate > -1'
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
		$taxChangedA['taxrate']		= '';// the rate can be 0 and zero is OK
		$taxChangedA['taxtitle']	= '';
		$taxChangedA['taxcountryid']= 0;
		$taxChangedA['taxregionid']= 0;


		if ((int)$taxId > 0 && $countryId > 0) {
			$db = Factory::getDBO();
			$q = 'SELECT tc.id, tc.title, tc.tax_rate'
			. ' FROM #__phocacart_tax_countries as tc'
			. ' WHERE tc.country_id = '.(int)$countryId
			. ' AND tc.tax_id = '.(int)$taxId
			. ' AND tc.tax_rate > -1'
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$taxO = $db->loadObject();

			if (isset($taxO->tax_rate) && $taxO->tax_rate != '') {
				$taxChangedA['taxrate']	= $taxO->tax_rate;
			}

			if (isset($taxO->title) && $taxO->title != '') {
				$taxChangedA['taxtitle']	= $taxO->title;
			}

			if (isset($taxO->id) && $taxO->id != '') {
				$taxChangedA['taxcountryid']	= $taxO->id;
			}

			// CONDITIONS:
			// $taxChangedA['taxrate'] > 0 ... not used - the rate can be 0
			// $taxChangedA['taxtitle'] ... not used - the title can be empty
			// if ($taxChangedA['taxrate'] > 0 && $taxChangedA['taxtitle'] != '') { // the rate can be 0
			//
			if ($taxChangedA['taxrate'] != '') {
				return $taxChangedA;// if 0, it is valid
			}

		}
		return false;
	}

	// Selected tax by country
	public static function getTaxByRegion($taxId) {

		$regionId 					= (int)self::getUserRegionId();
		$taxChangedA				= array();
		$taxChangedA['taxrate']		= '';// the rate can be 0 and zero is OK
		$taxChangedA['taxtitle']	= '';
		$taxChangedA['taxcountryid']= 0;
		$taxChangedA['taxregionid']= 0;

		// tax rate -1 means that the tax is not used but it exists yet (will be not completely removed because of the used ID in system
		// for example if the country tax is specific and has ID 10 and it will be deleted - then it still exists but with -1 as tax rate
		// when such tax will be newly recreated it gets the same ID as it has previously - which will unique tax rates for country in history
		// even if the tax rate changes it uniques the tax type for each country/region
		if ((int)$taxId > 0 && $regionId > 0) {
			$db = Factory::getDBO();
			$q = 'SELECT tr.id, tr.title, tr.tax_rate'
			. ' FROM #__phocacart_tax_regions as tr'
			. ' WHERE tr.region_id = '.(int)$regionId
			. ' AND tr.tax_id = '.(int)$taxId
			. ' AND tr.tax_rate > -1'
			. ' LIMIT 1';
			$db->setQuery($q) ;
			$taxO = $db->loadObject();

			if (isset($taxO->tax_rate) && $taxO->tax_rate != '') {
				$taxChangedA['taxrate']	= $taxO->tax_rate;
			}

			if (isset($taxO->title) && $taxO->title != '') {
				$taxChangedA['taxtitle']	= $taxO->title;
			}

			if (isset($taxO->id) && $taxO->id != '') {
				$taxChangedA['taxregionid']	= $taxO->id;
			}

			// CONDITIONS:
			// $taxChangedA['taxrate'] > 0 ... not used - the rate can be 0
			// $taxChangedA['taxtitle'] ... not used - the title can be empty
			// if ($taxChangedA['taxrate'] > 0 && $taxChangedA['taxtitle'] != '') { // the rate can be 0
			//
			if ($taxChangedA['taxrate'] != '') {
				return $taxChangedA;// if 0, it is valid
			}

		}
		return false;
	}

	public static function changeTaxBasedOnRule($taxId, $tax, $taxCalculationType, $taxTitle) {

		$taxChangedA 					= array();
		$taxChangedA['taxrate']			= $tax;
		$taxChangedA['taxtitle']		= $taxTitle;
		$taxChangedA['taxcountryid']	= 0;
		$taxChangedA['taxregionid']		= 0;



		//$app						= JFactory::getApplication();
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
			// If country or region based tax does not have title, set the default one
			if ($taxChangedA && $taxChangedA['taxtitle'] == '') {$taxChangedA['taxtitle'] = $taxTitle;}
		} else {
			// Region prioritized
			$taxChangedA = self::getTaxByRegion($taxId);
			//Not found - try to find country
			if (!$taxChangedA) {
				$taxChangedA = self::getTaxByCountry($taxId);
			}
			// If country or region based tax does not have title, set the default one
			if ($taxChangedA && $taxChangedA['taxtitle'] == '') {$taxChangedA['taxtitle'] = $taxTitle;}
		}

		// Nothing found - back to default:
		if (!$taxChangedA) {
			$taxChangedA['taxrate']		= $tax;
			$taxChangedA['taxtitle']	= $taxTitle;
			$taxChangedA['taxcountryid']	= 0;
			$taxChangedA['taxregionid']		= 0;
		}


		return $taxChangedA;
	}

	public static function getUserCountryId() {

		// 1. We get information about country stored by user
		// 2. Possible improvement get country by IP (but possible problem)

		$user 				= PhocacartUser::getUser();

		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$dynamic_tax_rate	= $paramsC->get( 'dynamic_tax_rate', 0 );

		$type = 0;
		if ($dynamic_tax_rate == 1) {
			$type = 0;// BILLING
		} else if ($dynamic_tax_rate == 2) {
			$type = 1;// SHIPPING
		}

		if (isset($user->id) && (int)$user->id > 0 && (int)$dynamic_tax_rate > 0) {
			$db = Factory::getDBO();
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
		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$dynamic_tax_rate	= $paramsC->get( 'dynamic_tax_rate', 0 );

		$type = 0;
		if ($dynamic_tax_rate == 1) {
			$type = 0;// BILLING
		} else if ($dynamic_tax_rate == 2) {
			$type = 1;// SHIPPING
		}

		if (isset($user->id) && (int)$user->id > 0 && (int)$dynamic_tax_rate > 0) {
			$db = Factory::getDBO();
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

	/**
	 * In case that the tax is overriden by country or region we need to identify it, this is why we don't use tax id as key but whole key: tax id:country tax id:region tax id
	 * @param unknown $id
	 * @param number $countryId
	 * @param number $regionId
	 * @return string
	 */

	public static function getTaxKey($id, $countryId = 0, $regionId = 0) {

		$key = (int)$id . ':';
		$key .= (int)$countryId. ':';
		$key .= (int)$regionId;

		return $key;

	}

	public static function getTaxIdsFromKey($taxKey) {

		$tax = array();
		$tax['id'] 			= 0;
		$tax['countryid']	= 0;
		$tax['regionid']	= 0;
		if ($taxKey != '') {

			$taxKeyA = explode(':', $taxKey);
			if (isset($taxKeyA[0])) { $tax['id'] 		= (int)$taxKeyA[0];}
			if (isset($taxKeyA[1])) { $tax['countryid'] = (int)$taxKeyA[1];}
			if (isset($taxKeyA[2])) { $tax['regionid']	= (int)$taxKeyA[2];}
		}

		return $tax;

	}
}
