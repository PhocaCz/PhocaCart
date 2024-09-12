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

class PhocacartCurrency
{
	//private static $currency 				= false;
	private static $defaultcurrency			= false;
	private static $defaultcurrencycode		= false;
	private static $defaultcurrencyarray	= false;
	private static $allcurrencies			= false;
	private static $currency				= array();
	private static $currencyvalid			= array();

	private function __construct(){}


	/*public static function getCurrency() {

		if(self::$currency === false){
			$session 		= Factory::getSession();
			$currencyId		= $session->get('currency', 0, 'phocaCart');
			if ((int)$currencyId < 1) {
				$currencyId = self::getDefaultCurrency();
			}

			$db = Factory::getDBO();
			$query = ' SELECT a.* FROM #__phocacart_currencies AS a'
					.' WHERE a.id = '.(int)$currencyId;
			$db->setQuery($query);
			$c = $db->loadObject();

			if (!empty($c)) {
				self::$currency = $c;
			} else {
				self::$currency = false;
			}
		}
		return self::$currency;
	}*/

	public static function getCurrency( $id = 0, $orderId = 0 ) {

		// Order currency is never stored in session
		// so we use id instead of key
		$session 	= Factory::getSession();
		if( $id == 0 ) {
			$id			= $session->get('currency', 0, 'phocaCart');

			// check only session currencies, not database - as they can be order currencies
			$isValid	= self::isCurrencyValid($id);
			if (!$isValid) {
				$id = 0;
			}
		}

		if ((int)$id < 1) {
			$id = self::getDefaultCurrency();
			$session->set('currency', (int)$id, 'phocaCart');
		}

		$id 	= (int)$id;
		// Price can be different for each currency and order
		// So there is a key which identifies default currency, other currency, default currency in order, other currency in order
		// one currency can have different exchange rates in order history, so two orders can have same currency but different exchange rate
		$key 	= base64_encode(serialize((int)$id . ':' . (int)$orderId));

		if( !array_key_exists( (string)$key, self::$currency ) ) {


			$db = Factory::getDBO();
			$query = ' SELECT a.* FROM #__phocacart_currencies AS a'
					.' WHERE a.id = '.(int)$id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$c = $db->loadObject();

			if (!empty($c)) {
				self::$currency[$key] = $c;
			} else {
				self::$currency[$key] = false;
			}
		}

		return self::$currency[$key];

	}

	public static function isCurrencyValid($id) {

		if ((int)$id > 0) {

			if(!array_key_exists((int)$id, self::$currencyvalid)) {

				$db = Factory::getDBO();
				$query = ' SELECT a.id FROM #__phocacart_currencies AS a'
					.' WHERE a.id = '.(int)$id
					.' AND a.published = 1'
					.' ORDER BY a.id';
				$db->setQuery($query);
				$c = $db->loadResult();

				if (isset($c) && (int)$c > 0) {
					self::$currencyvalid[$id] = true;
				} else {
					self::$currencyvalid[$id] = false;
				}
			}

			return self::$currencyvalid[$id];

		}
		return false;
	}

	public static function getDefaultCurrency() {

		if(self::$defaultcurrency === false){
			$db = Factory::getDBO();
			$query = ' SELECT a.id FROM #__phocacart_currencies AS a'
					.' WHERE a.exchange_rate = 1'
					.' ORDER BY a.id';
			$db->setQuery($query);
			$c = $db->loadObject();

			if (!empty($c->id)) {
				self::$defaultcurrency = (int)$c->id;
			} else {
				self::$defaultcurrency = false;
			}
		}
		return self::$defaultcurrency;
	}

	/*
	 * XML Feed only
	 */
	public static function getDefaultCurrencyCode() {

		if(self::$defaultcurrencycode === false){
			$db = Factory::getDBO();
			$query = ' SELECT a.code FROM #__phocacart_currencies AS a'
					.' WHERE a.exchange_rate = 1'
					.' ORDER BY a.code';
			$db->setQuery($query);
			$c = $db->loadObject();

			if (!empty($c->code)) {
				self::$defaultcurrencycode = $c->code;
			} else {
				self::$defaultcurrencycode = false;
			}
		}
		return self::$defaultcurrencycode;
	}

	/*
	 * Administration only - currency and currencies view
	 */
	public static function getDefaultCurrencyArray() {

		if(self::$defaultcurrencyarray === false){
			$db = Factory::getDBO();
			$query = ' SELECT a.id, a.title, a.code, a.exchange_rate FROM #__phocacart_currencies AS a'
					.' WHERE a.exchange_rate = 1'
					.' ORDER BY a.code';
			$db->setQuery($query);
			$c = $db->loadAssoc();

			if (!empty($c)) {
				self::$defaultcurrencyarray = $c;
			} else {
				self::$defaultcurrencyarray = false;
			}
		}
		return self::$defaultcurrencyarray;
	}

	public static function getAllCurrencies() {

		if(self::$allcurrencies === false){
			$db = Factory::getDBO();
			$query = ' SELECT a.id, a.id as value, CONCAT_WS(\'\', a.title, \' (\', a.code, \')\') as text, a.title, a.alias, a.code, a.image FROM #__phocacart_currencies AS a'
				.' WHERE a.published = 1'
				.' ORDER BY a.id';
			$db->setQuery($query);
			$c = $db->loadObjectList();

			if (!empty($c)) {
				self::$allcurrencies = $c;
			} else {
				self::$allcurrencies = false;
			}
		}
		return self::$allcurrencies;
	}

	public static function setCurrentCurrency($currencyId) {
		$session 		= Factory::getSession();
		$session->set('currency', (int)$currencyId, 'phocaCart');
	}

	/**
	 * @param string $attrs - HTML attributes of en select box
	 * @return mixed|string
	 */
	public static function getCurrenciesSelectBox(string $attrs = '') {
		$session 	= Factory::getSession();
		$active		= $session->get('currency', 0, 'phocaCart');
		if ((int)$active < 1) {
			$active = self::getDefaultCurrency();
		}
		$currencies = self::getAllCurrencies();
		$o = '';
		if (!empty($currencies)) {
			$o = HTMLHelper::_('select.genericlist',  $currencies, 'id', 'class="form-select chosen-select ph-input-select-currencies" ' . $attrs, 'value', 'text', $active);
		}
		return $o;
	}

	public static function getCurrenciesArray() {
		$session 	= Factory::getSession();
		$active		= $session->get('currency', 0, 'phocaCart');
		if ((int)$active < 1) {
			$active = self::getDefaultCurrency();
		}
		$currencies = self::getAllCurrencies();

		if (!empty($currencies)) {
			foreach($currencies as $k => $v) {
				if ($v->value == $active) {
					$v->active = 1;
				} else {
					$v->active = 0;
				}
			}
		}

		return $currencies;
	}

	public static function getCurrenciesListBox() {
		$session 	= Factory::getSession();
		$active		= $session->get('currency', 0, 'phocaCart');
		if ((int)$active < 1) {
			$active = self::getDefaultCurrency();
		}
		$currencies = self::getAllCurrencies();
		$o			= '';

		if (!empty($currencies)) {
			$o .= '<ul class="ph-input-list-currencies">';

			foreach ($currencies as $k => $v) {
				$class = '';
				if ($v->value == $active) {
					$class = 'class="active"';
				}
				$o .= '<li rel="'.$v->value.'" '.$class.'>'.$v->text.'</li>';
			}
			$o .= '</ul>';
		}

		return $o;
	}

	public static function getCurrencyRelation($currentCurrency, $defaultCurrency){

		$o = '';
		if (isset($currentCurrency['id']) && isset($defaultCurrency['id'])) {

			if ($currentCurrency['id'] == $defaultCurrency['id']) {
				return '';
			}

			if (isset($currentCurrency['exchange_rate']) && isset($defaultCurrency['exchange_rate'])) {
				if ($currentCurrency['exchange_rate'] > 0) {
					$o .= '<div class="col">1 '.$defaultCurrency['code'] . ' = '. PhocacartPrice::cleanPrice($currentCurrency['exchange_rate']). ' '. $currentCurrency['code'].'</div>';
				}
				if ($currentCurrency['exchange_rate'] > 0) {
					$o .= '<div class="col">1 '.$currentCurrency['code'] . ' = '. PhocacartPrice::cleanPrice(round((1 / $currentCurrency['exchange_rate']), 8))  . ' '. $defaultCurrency['code'].'</div>';
				}
				return $o;
			}
		}

		return '';

	}

	public static function getCurrentCurrencyRateIfNotDefault() {
		$currency = self::getCurrency();
		if (isset($currency->id) && (int)$currency->id > 0 && isset($currency->exchange_rate) && $currency->exchange_rate != 1) {
			return $currency->exchange_rate;
		} else {
			return 0;
		}
	}

	/* Payment method rule, possible shipping method rule */

	public static function storeCurrencies($currencyArray, $id, $table = 'payment') {

		if ($table == 'payment') {
			$t = '#__phocacart_payment_method_currencies';
			$c = 'payment_id';
		}

		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();


			if (!empty($currencyArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($currencyArray as $k => $v) {
					//$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
					// No multidimensional in J4
					$values[] = ' ('.(int)$id.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO '.$t.' ('.$c.', currency_id)'
								.' VALUES '.(string)$valuesString;



					$db->setQuery($query);
					$db->execute();
				}
			}
		}

	}

	/* Select currency in payment method rule */

	public static function getFieldCurrencies($id, $select = 0, $table = 'payment') {

		/*if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_regions';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_regions';
			$c = 'payment_id';
		}  else if ($table == 'zone') {
			$t = '#__phocacart_zone_regions';
			$c = 'zone_id';
		}*/

		$t = '#__phocacart_payment_method_currencies';
		$c = 'payment_id';

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT c.currency_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_currencies AS a'
				.' LEFT JOIN '.$t.' AS c ON a.id = c.currency_id'
			    .' WHERE c.'.$c.' = '.(int) $id;
		$db->setQuery($query);
		if ($select == 1) {
			$items = $db->loadColumn();
		} else {
			$items = $db->loadObjectList();
		}

		return $items;
	}


	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}

?>
