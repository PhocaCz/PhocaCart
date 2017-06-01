<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


class PhocacartPrice
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
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
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
			$currency	= PhocacartCurrency::getCurrency((int)$id);
		} else {
			$currency	= PhocacartCurrency::getCurrency();
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
	
	/*
	 * 1) the price can be negative - for example rounding
	 * 2) or we can force the negative - e.g. for discount
	 */
	
	public function getPriceFormat($price, $negative = 0) {
	
		
		if ($price < 0) {
			$negative = 1;
		}
		
		
		$price *= $this->exchange_rate;
		
		$price = $this->roundPrice($price);
		
		if ($negative) {
			$price = abs($price);
		}
		
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
		
		if ($negative) {
			return '- ' . $this->price_prefix . $price . $this->price_suffix;
		} else {
			return $this->price_prefix . $price . $this->price_suffix;
		}
		
	}
	
	public function getTaxFormat($tax, $taxCalculationType, $format = 1) {
	
		if ($format == 0) { // IS USED FOR PERCENTAGE IN VAT TITLE ... e.g. VAT(10%)
			if ($taxCalculationType == 2) { // FIX
				$tax = $tax + 0;
			} else { // Percentage
				$tax = ($tax + 0) .'%';
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
	
	public function getPriceItems($price, $taxId, $tax, $taxCalculationType, $taxTitle = '', $baseAmount = 0, $baseUnit = '', $zeroPrice = 0, $round = 1, $groupPrice = null) {
		
		// We need to round because if not
		// BRUTTO          0.15  ... 0.15
		// TAX             0.025 ... 0.03
		// NETTO           0.125 ... 0.13
		// BRUTTO IS WRONG 0.15  ... 0.16
		
		if ($groupPrice !== null) {
			$price = $groupPrice;
		}
		
		if ($round == 1) {$price = $this->roundPrice($price);}
	
		
		// Change TAX based on country or region
		$taxChangedA 				= PhocacartTax::changeTaxBasedOnRule($taxId, $tax, $taxCalculationType, $taxTitle);
		$tax 						= $this->roundPrice($taxChangedA['taxrate']);
		$taxTitle					= $taxChangedA['taxtitle'];
		$taxTitle					= JText::_($taxTitle);
		
		$priceO 					= array();
		$app						= JFactory::getApplication();
		$paramsC 					= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$tax_calculation			= $paramsC->get( 'tax_calculation', 0 );
		$display_unit_price			= $paramsC->get( 'display_unit_price', 1 );
		$zero_price_text			= $paramsC->get( 'zero_price_text', '' );
		$zero_price_label			= $paramsC->get( 'zero_price_label', '' );


		$priceO['taxtxt']			= $taxTitle;
		$priceO['taxcalc'] 			= $tax_calculation;
		$priceO['zero']				= 0;
		
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
				$priceO['tax']		= $priceO['netto'] * ($tax / 100);
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax']);}
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
			
				$priceO['tax']		= $priceO['brutto'] - ($priceO['brutto'] / (($tax / 100) + 1));
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax']);}
				$priceO['netto']	= $priceO['brutto'] - $priceO['tax'];
				//$priceO['netto']	= $priceO['brutto'] * 100 / ($tax + 100);
				//$priceO['tax']	= $priceO['brutto'] - $priceO['netto'];
				//$coefficient		= $tax / ($tax + 100);
				//$priceO['tax']	= $priceO['brutto'] * $coefficient; // POSIBLE TO DO - round e.g. to 4
				//$priceO['netto']	= $priceO['brutto'] - $priceO['tax'];
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
		
		// Unit price
		$priceO['base'] 		= '';
		$priceO['baseformat'] 	= '';
		if ($baseAmount > 0 && (int)$display_unit_price > 0) {
			$priceO['base'] 		= $priceO['brutto'] / $baseAmount;
			if ($round == 1) 		{ $priceO['base'] = $this->roundPrice($priceO['base']);}
			$priceO['baseformat'] 	= $this->getPriceFormat($priceO['base']).'/'.$baseUnit;
		}
		
		if ($price == 0 && $zeroPrice == 1) {
			if ($zero_price_text != '') {
				$priceO['nettoformat'] = $priceO['bruttoformat'] = $priceO['taxformat'] = JText::_($zero_price_text);
			}
			
			if ($zero_price_label == '0') {
				$priceO['nettotxt'] = $priceO['bruttotxt'] = $priceO['taxtxt'] = '';
			} else if ($zero_price_label != '') {
				$priceO['nettotxt'] = $priceO['bruttotxt'] = $priceO['taxtxt'] = JText::_($zero_price_label);
			}
			
		}
		
		if ($priceO['brutto'] == 0 && $priceO['netto'] == 0) {
			$priceO['zero'] = 1;
		}
		
		
		return $priceO;
	}
	
	public function getPriceItem($price, $groupPrice = null, $format = 1) {
		if ($groupPrice !== null) {
			$price = $groupPrice;
		}
		
		if ($format == 1) {
			$price = $this->getPriceFormat($price);
		}
		return $price;
	}
	
	/*
	 * $type ... price, shipping, payment
	 * ROUNDING - TAX EXCLUSIVE - ROUND UP
	 * ROUNDING - TAX INCLUSIVE - ROUND DOWN
	 */
	public function roundPrice($price, $type = 'price') {
		
		$app						= JFactory::getApplication();
		$paramsC 					= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$rounding_calculation		= $paramsC->get( 'rounding_calculation', 2 );
		/*$tax_calculation			= $paramsC->get( 'tax_calculation', 0 );
		$tax_calculation_shipping	= $paramsC->get( 'tax_calculation_shipping', 0 );
		$tax_calculation_payment	= $paramsC->get( 'tax_calculation_payment', 0 );
		
		switch($type) {
			
			case 'shipping':
				//        if tax_calculation_shipping == inclusive then rown down else round up
				$rounding = $tax_calculation_shipping == 2 ? 2 : 1;
			break;
			case 'payment':
				$rounding = $tax_calculation_payment == 2 ? 2 : 1;
			break;
			case 'price':
			default:
				$rounding = $tax_calculation == 2 ? 2 : 1;
			break;
			
		}*/
		$rounding = $rounding_calculation; // 1 ... down, 2 ... up
		
		return round($price, $this->price_decimals, $rounding);
	}
	
	public function getPriceItemsShipping($price, $calculationType, $total, $taxId, $tax, $taxCalculationType, $taxTitle = '', $freeShipping = 0, $round = 1, $langPrefix = 'SHIPPING_') {
		
		// PERCENTAGE PRICE OF SHIPPING
		// CALCULATED FROM TOTAL - PAYMENT - SHIPPING (TOTAL BEFORE SHIPPING AND PAYMENT PRICE)
		// $total[brutto] can be changed e.g. to netto, etc.
		if ($calculationType == 1 && isset($total['brutto'])) {
			$price = $total['brutto'] * $price / 100;
		}
		
		if ($round == 1) {$price = $this->roundPrice($price, 'shipping');}
		
		// Change TAX based on country or region
		$taxChangedA 				= PhocacartTax::changeTaxBasedOnRule($taxId, $tax, $taxCalculationType, $taxTitle);
		$tax 						= $this->roundPrice($taxChangedA['taxrate'], 'shipping');
		$taxTitle					= $taxChangedA['taxtitle'];
		
		$taxTitle					= JText::_($taxTitle);
		
		$priceO 					= array();
		
		// Define - the function always return all variables so we don't need to check them
		$priceO['nettoformat']		= '';
		$priceO['taxformat']		= '';
		$priceO['bruttoformat']		= '';
		$priceO['bruttotxt']		= '';
		$priceO['netto']			= 0;
		$priceO['tax']				= 0;
		$priceO['brutto']			= 0;
		$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');
		$priceO['taxtxt']			= '';
		$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
		$priceO['zero']				= 0;
		$priceO['freeshipping'] 	= 0;
		
		$app						= JFactory::getApplication();
		$paramsC 					= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$tax_calculation_shipping	= $paramsC->get( 'tax_calculation_shipping', 0 );
		
		
		
		
		// E.G. if coupon set the shipping costs to null - free shipping
		if ($freeShipping == 1) {
			$priceO['netto']			= 0;
			$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_FREE_SHIPPING');
			$priceO['tax']				= 0;
			$priceO['brutto'] 			= 0;
			$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_FREE_SHIPPING');
			$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
			$priceO['freeshipping'] 	= 1;
			$priceO['zero']				= 1;
			$priceO['title']			= JText::_('COM_PHOCACART_FREE_SHIPPING');
			$priceO['description']		= '';
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
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['tax']		= $priceO['netto'] * ($tax / 100);
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax'], 'shipping');}
				$priceO['brutto']	= $priceO['netto'] + $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';	
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');		
	
		// INCLUSIVE TAX
		} else if ($tax_calculation_shipping == 2) {
			$priceO['brutto'] = $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['netto']	= $priceO['brutto'] - $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				
				$priceO['tax']		= $priceO['brutto'] - ($priceO['brutto'] / (($tax / 100) + 1));
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax'], 'shipping');}
				$priceO['netto']	= $priceO['brutto'] - $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');	
		}
		
		
		if ($priceO['netto'] == $priceO['brutto']){
			$priceO['netto'] 		= false;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE');
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
		
		
		if ($priceO['brutto'] == 0 && $priceO['netto'] == 0) {
			$priceO['zero'] = 1;
		}
		
		/*
		$priceI['nettoformat']	= isset($priceI['nettoformat']) ? $priceI['nettoformat'] : '';
		$priceI['taxformat']	= isset($priceI['taxformat']) ? $priceI['taxformat'] : '';
		$priceI['bruttoformat']	= isset($priceI['bruttoformat']) ? $priceI['bruttoformat'] : '';
		$priceI['bruttotxt']	= isset($priceI['bruttotxt']) ? $priceI['bruttotxt'] : '';
		$priceI['taxtxt']		= isset($priceI['taxtxt']) ? $priceI['taxtxt'] : '';
		$priceI['bruttotxt']	= isset($priceI['bruttotxt']) ? $priceI['bruttotxt'] : '';
		$priceI['netto']		= isset($priceI['netto']) ? $priceI['netto'] : 0;
		$priceI['brutto']		= isset($priceI['brutto']) ? $priceI['brutto'] : 0;*/
		
		return $priceO;
	}

	
	public function getPriceItemsPayment($price, $calculationType, $total, $taxId, $tax, $taxCalculationType, $taxTitle = '', $freePayment = 0, $round = 1, $langPrefix = 'PAYMENT_') {
		
		
		// PERCENTAGE PRICE OF PAYMENT
		// CALCULATED FROM TOTAL - PAYMENT (TOTAL BEFORE PAYMENT PRICE)
		// $total[brutto] can be changed e.g. to netto, etc.
		if ($calculationType == 1 && isset($total['brutto'])) {
			$price = $total['brutto'] * $price / 100;
		}
		
		
		if ($round == 1) {$price = $this->roundPrice($price, 'payment');}
		
		
		// Change TAX based on country or region
		$taxChangedA 				= PhocacartTax::changeTaxBasedOnRule($taxId, $tax, $taxCalculationType, $taxTitle);
		$tax 						= $this->roundPrice($taxChangedA['taxrate'], 'payment');
		$taxTitle					= $taxChangedA['taxtitle'];
		
		$taxTitle					= JText::_($taxTitle);
		
		$priceO 					= array();
		$app						= JFactory::getApplication();
		$paramsC 					= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$tax_calculation_payment	= $paramsC->get( 'tax_calculation_payment', 0 );
		
		// Define - the function always return all variables so we don't need to check them
		$priceO['nettoformat']		= '';
		$priceO['taxformat']		= '';
		$priceO['bruttoformat']		= '';
		$priceO['bruttotxt']		= '';
		$priceO['netto']			= 0;
		$priceO['tax']				= 0;
		$priceO['brutto']			= 0;
		$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');
		$priceO['taxtxt']			= '';
		$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
		$priceO['zero']				= 0;
		$priceO['freepayment'] 		= 0;

		
		// E.G. if coupon set the shipping costs to null - free shipping
		if ($freePayment == 1) {
			$priceO['netto']			= 0;
			$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_FREE_PAYMENT');
			$priceO['tax']				= 0;
			$priceO['brutto'] 			= 0;
			$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_FREE_PAYMENT');
			$priceO['bruttoformat'] 	= $this->getPriceFormat($priceO['brutto']);
			$priceO['freepayment'] 		= 1;
			$priceO['zero']				= 1;
			$priceO['title']			= JText::_('COM_PHOCACART_FREE_PAYMENT');
			$priceO['description']		= '';
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
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
			
				$priceO['tax']		= $priceO['netto'] * ($tax / 100);
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax'], 'payment');}
				$priceO['brutto']	= $priceO['netto'] + $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';	
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');		
	
		// INCLUSIVE TAX
		} else if ($tax_calculation_payment == 2) {
			$priceO['brutto'] = $price;
			if ($taxCalculationType == 2) { // FIX
				$priceO['tax']		= $tax;
				$priceO['netto']	= $priceO['brutto'] - $tax;
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getPriceFormat($tax) . ')';
			} else { // Percentage
				$priceO['tax']		= $priceO['brutto'] - ($priceO['brutto'] / (($tax / 100) + 1));
				if ($round == 1) 	{ $priceO['tax'] = $this->roundPrice($priceO['tax'], 'payment');}
				$priceO['netto']	= $priceO['brutto'] - $priceO['tax'];
				$priceO['taxtxt']	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE') . ' ' . $taxTitle . ' (' . $this->getTaxFormat($tax, $taxCalculationType, 0) . ')';
			}
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');	
		}
		
		
		if ($priceO['netto'] == $priceO['brutto']){
			$priceO['netto'] 		= false;
			$priceO['bruttotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE');
			$priceO['nettotxt'] 	= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE');
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
		
		if ($priceO['brutto'] == 0 && $priceO['netto'] == 0) {
			$priceO['zero'] = 1;
		}
		
		return $priceO;
	}
	
	// STATIC PART
	/*
	 * Used for example by orders - we don't want to have current exchange rate
	 * but rate which was actual in date of order
	 */
	public static function getCurrencyAndRateByOrder($orderId = 0) {
		if ((int)$orderId > 0) {
			$db = JFactory::getDBO();
			$query = ' SELECT a.currency_id, a.currency_exchange_rate FROM #__phocacart_orders AS a'
			    .' WHERE a.id = '.(int) $orderId
				.' ORDER BY a.id';
			$db->setQuery($query);

			$currencyOrder = $db->loadObject();
			
			if (!empty($currencyOrder)) {
				return $currencyOrder;
			}
		
			return false;
		}
		
	}
	
	/* E.g. for payment methods, we need raw price converted by exchange rate
	*/
	public static function convertPriceDefaultToCurrentCurrency($price, $rate = 1) {
		$price *= $rate;
		return $price;
	}
	public static function convertPriceCurrentToDefaultCurrency($price, $rate = 1) {
		$price /= $rate;
		return $price;
	}	
	public static function cleanPrice($price) {
		$price = (float)$price;
		return $price + 0;
	}
}