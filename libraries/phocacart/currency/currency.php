<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCurrency
{
	//private static $currency 		= false;
	private static $defaultcurrency		= false;
	private static $defaultcurrencycode	= false;
	private static $allcurrencies		= false;
	private static $currency			= array();
	
	private function __construct(){}
	
	
	/*public static function getCurrency() {
		
		if(self::$currency === false){
			$session 		= JFactory::getSession();
			$currencyId		= $session->get('currency', 0, 'phocaCart');
			if ((int)$currencyId < 1) {
				$currencyId = self::getDefaultCurrency();
			}
			
			$db = JFactory::getDBO();
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
	
	public static function getCurrency( $id = 0 ) {
		
		$session 	= JFactory::getSession();
		if( $id == 0 ) {
			$id		= $session->get('currency', 0, 'phocaCart');
			
		}
		
		if ((int)$id < 1) {
			$id = self::getDefaultCurrency();
			$session->set('currency', (int)$id, 'phocaCart');
		}
		
		$id = (int)$id;
		
		if( !array_key_exists( (int)$id, self::$currency ) ) {
		
			
			$db = JFactory::getDBO();
			$query = ' SELECT a.* FROM #__phocacart_currencies AS a'
					.' WHERE a.id = '.(int)$id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$c = $db->loadObject();
			
			if (!empty($c)) {
				self::$currency[$id] = $c;
			} else {
				self::$currency[$id] = false;
			}
		}
		
		return self::$currency[$id];
		
	}
	
	public static function getDefaultCurrency() {
		
		if(self::$defaultcurrency === false){
			$db = JFactory::getDBO();
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
	
	public static function getDefaultCurrencyCode() {
		
		if(self::$defaultcurrencycode === false){
			$db = JFactory::getDBO();
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
	
	public static function getAllCurrencies() {
		
		if(self::$allcurrencies === false){
			$db = JFactory::getDBO();
			$query = ' SELECT a.id as value, CONCAT_WS(\'\', a.title, \' (\', a.code, \')\') as text FROM #__phocacart_currencies AS a'
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
		$session 		= JFactory::getSession();
		$session->set('currency', (int)$currencyId, 'phocaCart');
	}
	
	public static function getCurrenciesSelectBox() {
		$session 	= JFactory::getSession();
		$active		= $session->get('currency', 0, 'phocaCart');
		if ((int)$active < 1) {
			$active = self::getDefaultCurrency();
		}
		$currencies = self::getAllCurrencies();
		$o = JHTML::_('select.genericlist',  $currencies, 'id', 'class="form-control chosen-select ph-input-select-currencies"', 'value', 'text', $active);
		return $o;
	}
	
	public static function getCurrenciesListBox() {
		$session 	= JFactory::getSession();
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
	
	
	public final function __clone() {
		JError::raiseWarning(500, 'Function Error: Cannot clone instance of Singleton pattern');// No JText - for developers only
		return false;
	}
}
/*
?>

class PhocaCartCurrency
{
	protected $currency     	= 0;

	public function __construct() {
		$session 		= JFactory::getSession();
		$this->currency	= $session->get('currency', 0, 'phocaCart');
		if ((int)$this->currency < 1) {
			$this->currency = $this->getDefaultCurrency();
		}
	}
	
	public function getDefaultCurrency() {
		$db = JFactory::getDBO();
		$query = ' SELECT a.id FROM #__phocacart_currencies AS a'
					.' WHERE a.exchange_rate = 1';
		$db->setQuery($query);
		$currency = $db->loadObject();
		if (isset($currency->id)) {
			return $currency->id;
		} else {
			return false;
		}	
	}
	
	public function getCurrentCurrency() {
		return $this->currency;
	}
	
	public function setCurrentCurrency($currencyId) {
		$session 		= JFactory::getSession();
		$session->set('currency', (int)$currencyId, 'phocaCart');
	}
	
	public function getCurrencyData($currencyId) {
		$db = JFactory::getDBO();
		$query = ' SELECT a.* FROM #__phocacart_currencies AS a'
				.' WHERE a.id = '.(int)$currencyId;
		$db->setQuery($query);
		$currency = $db->loadObject();
		
		if (!empty($currency)) {
			return $currency;
		} else {
			return false;
		}
	}
	
	public function getAllCurrencies() {
		$db = JFactory::getDBO();
		$query = ' SELECT a.id as value, CONCAT_WS(\'\', a.title, \' (\', a.code, \')\') as text FROM #__phocacart_currencies AS a'
				.' WHERE a.published = 1';
		$db->setQuery($query);
		$currencies = $db->loadObjectList();
		if (!empty($currencies)) {
			return $currencies;
		} else {
			return false;
		}
	}
	
	public function getCurrenciesSelectBox() {
		$currencies = $this->getAllCurrencies();
		$active		= $this->currency;
		$o = JHTML::_('select.genericlist',  $currencies, 'id', 'class="form-control chosen-select ph-input-select-currencies"', 'value', 'text', $active);
		return $o;
	}
	
		/*
	public static function getDefaultCurrency() {
		
		$db = JFactory::getDBO();
		$query = ' SELECT a.id FROM #__phocacart_currencies AS a'
					.' WHERE a.exchange_rate = 1';
		$db->setQuery($query);
		$currency = $db->loadObject();
		if (isset($currency->id)) {
			return $currency->id;
		} else {
			return false;
		}	
	}*/
	
	/*
	public static function getAllCurrencies() {
		$db = JFactory::getDBO();
		$query = ' SELECT a.id as value, CONCAT_WS(\'\', a.title, \' (\', a.code, \')\') as text FROM #__phocacart_currencies AS a'
				.' WHERE a.published = 1';
		$db->setQuery($query);
		$currencies = $db->loadObjectList();
		if (!empty($currencies)) {
			return $currencies;
		} else {
			return false;
		}
	}
}*/
?>