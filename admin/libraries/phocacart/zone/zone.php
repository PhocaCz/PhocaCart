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
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.application.component.model');

class PhocacartZone
{
	public static function getZones($id, $select = 0, $table = 'shipping') {

		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_zones';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_zones';
			$c = 'payment_id';
		}

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT c.zone_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_zones AS a'
				.' LEFT JOIN '.$t.' AS c ON a.id = c.zone_id'
			    .' WHERE c.'.$c.' = '.(int) $id
				.' ORDER BY a.id';
		$db->setQuery($query);

		if ($select == 1) {
			$zones = $db->loadColumn();
		} else {
			$zones = $db->loadObjectList();
		}

		return $zones;
	}

	public static function getAllZonesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {

		$db =Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_zones AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$zones = $db->loadObjectList();

		$zonesO = HTMLHelper::_('select.genericlist', $zones, $name, 'class="form-control" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

		return $zonesO;
	}

	public static function getAllZones($order = 'id' ) {

		$db =Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_zones AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$zones = $db->loadObjectList();

		return $zones;
	}

	/*
	 * used for shipping method rules
	 * used for payment method rules
	 */

	public static function storeZones($zonesArray, $id, $table = 'shipping') {


		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_zones';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_zones';
			$c = 'payment_id';
		}

		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();

			if (!empty($zonesArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($zonesArray as $k => $v) {
					//$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
					// No multidimensional in J4
					$values[] = ' ('.(int)$id.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',',$values);

					$query = ' INSERT INTO '.$t.' ('.$c.', zone_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	public static function getCountries($id = 0) {
		if ($id > 0) {
			$db =Factory::getDBO();
			$q = ' SELECT a.country_id FROM #__phocacart_zone_countries AS a'
			    .' WHERE a.zone_id = '.(int) $id
				.' ORDER BY a.zone_id';
			$db->setQuery($q);
			$countries = $db->loadColumn();
			return $countries;
		}
		return false;
	}

	public static function getRegions($id = 0) {
		if ($id > 0) {
			$db =Factory::getDBO();
			$q = ' SELECT a.region_id FROM #__phocacart_zone_regions AS a'
			    .' WHERE a.zone_id = '.(int) $id
				.' ORDER BY a.zone_id';
			$db->setQuery($q);
			$regions = $db->loadColumn();
			return $regions;
		}
		return false;
	}

	public static function isCountryOrRegionIncluded($zones, $country, $region){

		if (!empty($zones)) {
			foreach($zones as $k => $v) {
				// Get all countries from current zone - zones which are set as rules in shipping rule
				$countries = self::getCountries((int)$v);
				// Is user's country included in country which is included in selected Zone
				if (in_array((int)$country, $countries)) {
					return true;
				}

				// Get all regions from current zone - zones which are set as rules in shipping rule
				$regions = self::getRegions((int)$v);
				// Is user's region included in region which is included in selected Zone
				if (in_array((int)$region, $regions)) {
					return true;
				}
			}

			/*
			// POSSIBLE SOLUTION Countries and regions not in one foreach, first we test countries than regions
			foreach($zones as $k => $v) {
				// Get all regions from current zone - zones which are set as rules in shipping rule
				$regions = self::getRegions((int)$v);
				// Is user's region included in region which is included in selected Zone
				if (in_array((int)$region, $regions)) {
					return true;
				}
			}*/
		}

		return false;
	}

}
?>
