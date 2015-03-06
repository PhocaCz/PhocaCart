<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartPrice
{
	protected $price_decimals 		= 2;
	protected $price_dec_symbol		= '.';
	protected $price_thousands_sep	= '';
	protected $price_format			= 0;
	protected $price_currency_symbol= '';
	protected $price_prefix			= '';
	protected $price_suffix			= '';
	protected $exchange_rate		= 1;
	protected $currency;

	public function __construct() {
	
		$this->setCurrency();// allways set default, if needed set specific currency when you call setCurrencyMethod
	}
	
	public function setCurrency( $id = 0, $orderId = 0) {
	
		// We can set the exchange rate by:
		// a) the currency exchange rate set in currency (can change in time)
		// b) the currency exchange reat set in order (the order exchange rate should not change in time)
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart');
		$exchange_rate_order= $paramsC->get( 'exchange_rate_order', 0 );
		
		$currencyOrder = false;
		if ((int)$orderId > 0) {
			$currencyOrder = self::getCurrencyAndRateByOrder($orderId);
		}
		
		// 1) change the currency ID by order
		if (isset($currencyOrder->currency_id)) {
			$id = $currencyOrder->currency_id;
		}
	
		if ((int)$id > 0) {
			$currency	= PhocaCartCurrency::getCurrency($id);
		} else {
			$currency	= PhocaCartCurrency::getCurrency();
		}
		
		// 1) change the currency exchange rate by order
		if (isset($currencyOrder->currency_exchange_rate) && $exchange_rate_order == 0) {
			$currency->exchange_rate = $currencyOrder->currency_exchange_rate;
		}
		
		
		if (!empty($currency)) {
			$this->price_decimals 		= $currency->price_decimals;
			$this->price_dec_symbol		= $currency->price_dec_symbol;
			$this->price_thousands_sep	= $currency->price_thousands_sep;
			$this->price_format			= $currency->price_format;
			$this->price_currency_symbol= $currency->price_currency_symbol;
			$this->price_prefix			= $currency->price_prefix;
			$this->price_suffix			= $currency->price_suffix;
			$this->exchange_rate		= $currency->exchange_rate;
		}
	
	}
	
	
	
	public function getPriceFormat($price) {
	

		$price *= $this->exchange_rate;
		
		$price = number_format((double)$price, $this->price_decimals, $this->price_dec_symbol, $this->price_thousands_sep);
	
		switch($this->price_format) {
			case 1:
				$price = $price . $this->price_currency_symbol;
			break;
			
			case 2:
				$price = $this->price_currency_symbol . $price;
			break;
			
			case 3:
				$price = $this->price_currency_symbol . ' ' . $price;
			break;
			
			case 0:
			default:
				$price = $price . ' ' . $this->price_currency_symbol;
			break;
		}	
		return $this->price_prefix . $price . $this->price_suffix;
	}
	
	public function getTaxFormat($tax, $taxCalculationType, $format = 1) {
	
		if ($format == 0) { // IS USED FOR PERCENTAGE IN VAT TITLE ... e.g. VAT(10%)
			if ($taxCalculationType == 2) { // FIX
				$tax = $tax;
			} else { // Percentage
				$tax = $tax.'%';
			}
		} else { // IS USED FOR PERCENTAGE in CALCUTATION: ... VAT(10%) --> 10,00 %
			if ($taxCalculationType == 2) { // FIX
				$tax = number_format((double)$tax, $this->price_decimals, $this->price_dec_symbol, $this->price_thousands_sep);
			} else { // Percentage
				$tax = number_format((double)$tax, $this->price_decimals, $this->price_dec_symbol, $this->price_thousands_sep) . ' %';
			}
		}
		
		
		return $tax;
	}
	
	/*
	 * param format - format the price or not (add currency symbol, price decimals thousands separator, ...)
	 */
	
	public function getPriceItems($price, $tax, $taxCalculationType, $taxTitle = '') {
		
		$priceO 			= array();
		$paramsC 			= JComponentHelper::getParams('com_phocacart');
		$tax_calculation	= $paramsC->get( 'tax_calculation', 0 );

		$priceO['taxtxt']	= $taxTitle;
		
		// NO TAX
		if ($tax_calculation == 0) {
			$priceO['netto']		= $price;
			$priceO['tax']			= 0;
			$priceO['brutto'] 		= $price;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PRICE');
			
		// EXCLUSIVE TAX
		} else if ($tax_calculation == 1) {
			$priceO['netto'] 		= $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['brutto']	= $priceO['netto'] + $tax;
				$priceO['taxtxt']	= $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['tax']		= $priceO['netto'] * $tax / 100;
				$priceO['brutto']	= $priceO['netto'] + $priceO['tax'];
				$priceO['taxtxt']	= $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
				
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PRICE_EXCL_TAX');		
	
		// INCLUSIVE TAX
		} else if ($tax_calculation == 2) {
			$priceO['brutto'] = $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['netto']	= $priceO['brutto'] - $tax;
				$priceO['taxtxt']	= $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['netto']	= $priceO['brutto'] * 100 / ($tax + 100);
				$priceO['tax']		= $priceO['brutto'] - $priceO['netto'];
				$priceO['taxtxt']	= $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
				
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PRICE_EXCL_TAX');	
		}
		
		if ($priceO['netto'] == $priceO['brutto']){
			//$priceO['netto'] 		= false;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PRICE');
		}
		
	
		//if ($tax_calculation > 0) {
			if ($priceO['netto']) {
				$priceO['nettoformat']	= $this->getPriceFormat($priceO['netto']);
			}
			if ($priceO['tax']) {
				$priceO['taxformat']	= $this->getPriceFormat($priceO['tax']);
			}
		//}
		$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
		
		
		return $priceO;
	}
	
	public function getPriceItemsShipping($price, $tax, $taxCalculationType, $taxTitle = '', $freeShipping = 0) {
		
		
		$priceO 					= array();
		$paramsC 					= JComponentHelper::getParams('com_phocacart');
		$tax_calculation_shipping	= $paramsC->get( 'tax_calculation_shipping', 0 );
		
		
		$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_SHIPPING_PRICE_INCL_TAX');
		$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_SHIPPING_PRICE_EXCL_TAX');
		
		// E.G. if coupon set the shipping costs to null - free shipping
		if ($freeShipping == 1) {
			$priceO['netto']			= 0;
			$priceO['tax']				= 0;
			$priceO['brutto'] 			= 0;
			$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_FREE_SHIPPING');
			$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
			$priceO['freeshipping'] 	= 1;
			return $priceO;
		}
	
		$priceO['taxtxt']	= $taxTitle;
		
		// NO TAX
		if ($tax_calculation_shipping == 0) {
			$priceO['netto']		= $price;
			$priceO['tax']			= 0;
			$priceO['brutto'] 		= $price;
			
		
		// EXCLUSIVE TAX
		} else if ($tax_calculation_shipping == 1) {
			$priceO['netto'] 		= $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['brutto']	= $priceO['netto'] + $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_SHIPPING_PRICE') . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['tax']		= $priceO['netto'] * $tax / 100;
				$priceO['brutto']	= $priceO['netto'] + $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_SHIPPING_PRICE') . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';	
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE_EXCL_TAX');		
	
		// INCLUSIVE TAX
		} else if ($tax_calculation_shipping == 2) {
			$priceO['brutto'] = $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['netto']	= $priceO['brutto'] - $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_SHIPPING_PRICE') . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['netto']	= $priceO['brutto'] * 100 / ($tax + 100);
				$priceO['tax']		= $priceO['brutto'] - $priceO['netto'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_SHIPPING_PRICE') . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE_EXCL_TAX');	
		}
		
		
		if ($priceO['netto'] == $priceO['brutto']){
			$priceO['netto'] 		= false;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_SHIPPING_PRICE');
		}
		
		//if ($tax_calculation_shipping > 0) {
			if ($priceO['netto']) {
				$priceO['nettoformat']	= $this->getPriceFormat($priceO['netto']);
			}
			if ($priceO['tax']) {
				$priceO['taxformat']	= $this->getPriceFormat($priceO['tax']);
			}
		//}
		$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
		
		
		return $priceO;
	}

	
	public function getPriceItemsPayment($price, $tax, $taxCalculationType, $taxTitle = '', $freePayment = 0) {
		
		
		$priceO 					= array();
		$paramsC 					= JComponentHelper::getParams('com_phocacart');
		$tax_calculation_payment	= $paramsC->get( 'tax_calculation_payment', 0 );
		
		
		$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_INCL_TAX');
		$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_EXCL_TAX');
		
		// E.G. if coupon set the shipping costs to null - free shipping
		if ($freePayment == 1) {
			$priceO['netto']			= 0;
			$priceO['tax']				= 0;
			$priceO['brutto'] 			= 0;
			$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_FREE_PAYMENT');
			$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
			$priceO['freepayment'] 		= 1;
			return $priceO;
		}
	
		$priceO['taxtxt']	= $taxTitle;
		
		// NO TAX
		if ($tax_calculation_payment == 0) {
			$priceO['netto']		= $price;
			$priceO['tax']			= 0;
			$priceO['brutto'] 		= $price;
			
		
		// EXCLUSIVE TAX
		} else if ($tax_calculation_payment == 1) {
			$priceO['netto'] 		= $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['brutto']	= $priceO['netto'] + $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_PAYMENT_PRICE') . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['tax']		= $priceO['netto'] * $tax / 100;
				$priceO['brutto']	= $priceO['netto'] + $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_PAYMENT_PRICE') . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';	
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_EXCL_TAX');		
	
		// INCLUSIVE TAX
		} else if ($tax_calculation_payment == 2) {
			$priceO['brutto'] = $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['netto']	= $priceO['brutto'] - $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_PAYMENT_PRICE') . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['netto']	= $priceO['brutto'] * 100 / ($tax + 100);
				$priceO['tax']		= $priceO['brutto'] - $priceO['netto'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_PAYMENT_PRICE') . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE_EXCL_TAX');	
		}
		
		
		if ($priceO['netto'] == $priceO['brutto']){
			$priceO['netto'] 		= false;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_PAYMENT_PRICE');
		}
		
		//if ($tax_calculation_payment > 0) {
			if ($priceO['netto']) {
				$priceO['nettoformat']	= $this->getPriceFormat($priceO['netto']);
			}
			if ($priceO['tax']) {
				$priceO['taxformat']	= $this->getPriceFormat($priceO['tax']);
			}
		//}
		$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
		
		
		return $priceO;
	}
	
	
	/*
	 * Used for example by orders - we don't want to have currenct exchange rate
	 * but rate which was actual in date of order
	 */
	public static function getCurrencyAndRateByOrder($orderId = 0) {
		if ((int)$orderId > 0) {
			$db = JFactory::getDBO();
			$query = ' SELECT currency_id, currency_exchange_rate FROM #__phocacart_orders AS a'
			    .' WHERE a.id = '.(int) $orderId;
			$db->setQuery($query);

			$currencyOrder = $db->loadObject();
			
			if (!empty($currencyOrder)) {
				return $currencyOrder;
			}
		
			return false;
		}
		
	}
	
	// STATIC PART
	/* E.g. for payment methods, we need raw price converted by exchange rate
	*/
	public static function getRawPriceByCurrencyRate($price, $rate = 1) {
		$price *= $rate;
		return $price;
	}
	
	
}