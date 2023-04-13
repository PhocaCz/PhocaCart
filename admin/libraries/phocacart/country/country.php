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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
jimport('joomla.application.component.model');

class PhocacartCountry
{
	public static function getCountryById($countryId) {

		$db =Factory::getDBO();
		$query = 'SELECT title FROM #__phocacart_countries WHERE id = '.(int) $countryId. ' ORDER BY title LIMIT 1';
		$db->setQuery($query);
		$country = $db->loadColumn();
		if(isset($country[0])) {
			return (string)$country[0];
		}
		return '';
	}

	public static function getCountryByCode2($countryId) {

		$db =Factory::getDBO();
		$query = 'SELECT code2 FROM #__phocacart_countries WHERE id = '.(int) $countryId. ' ORDER BY code2 LIMIT 1';
		$db->setQuery($query);
		$country = $db->loadColumn();
		if(isset($country[0])) {
			return (string)$country[0];
		}
		return '';
	}

	public static function options() {

		$db = Factory::getDBO();
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
		} else if ($table == 'zone') {
			$t = '#__phocacart_zone_countries';
			$c = 'zone_id';
		}

		$db =Factory::getDBO();

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
		} else if ($table == 'zone') {
			$t = '#__phocacart_zone_countries';
			$c = 'zone_id';
		}

		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();

			if (!empty($countriesArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($countriesArray as $k => $v) {
					//$values[] = ' ('.(int)$id.', '.(int)$v[0].')'; NEW FORM
					// No multidimensional in J4
					$values[] = ' ('.(int)$id.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO '.$t.' ('.$c.', country_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	public static function getAllCountriesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {

		$db =Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_countries AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$countries = $db->loadObjectList();

		$countriesO = HTMLHelper::_('select.genericlist', $countries, $name, 'class="form-control" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

		return $countriesO;
	}

	public static function getAllCountries($order = 'id' ) {

		$db =Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_countries AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$countries = $db->loadObjectList();

		return $countries;
	}

	public static function getCountryFlag($code = '', $frontend = 0, $image = '', $width = '', $height = '') {

		if ($image != '') {
			$imageO = PhocacartImage::getImage($image, '', $width, $height);
			if ($imageO) {
				return $imageO;
			}
		}

		if ($code != '') {
			//$link	= '/media/mod_languages/images/'. strip_tags(strtolower($code)). '.gif';
			//$link	= '/media/com_phocacart/images/flags/'. strip_tags(strtolower($code)). '-22x14.png';
			$link	= '/media/com_phocacart/images/flags/'. strip_tags(strtolower($code)). '.png';

			$abs	= JPATH_ROOT . $link;

			if ($frontend == 1) {
				$rel	= Uri::base(true) . $link;
			} else {
				$rel	= str_replace('/administrator', '', Uri::base(true)) . $link;
			}

			if(File::exists($abs)) {
				return '<img src="'.$rel.'" alt="" />';
			}
		}
		return '';
	}
}
?>
