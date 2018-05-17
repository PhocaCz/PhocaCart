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
	private static $currency		= array();

	public function __construct() {
	
		$this->setCurrency();// allways set default, if needed set specific currency when you call setCurrencyMethod
	}
	
	public function setCurrency( $id = 0, $orderId = 0) {
	
		
		// Price can be different for each currency and order
		// So there is a key which identifies default currency, other currency, default currency in order, other currency in order
		// one currency can have different exchange rates in order history, so two orders can have same currency but different exchange rate
		$key = base64_encode(serialize((int)$id . ':' . (int)$orderId));
		
		if( !array_key_exists( (string)$key, self::$currency )) {
			$app			= JFactory::getApplication();
			$paramsC 		= PhocacartUtils::getComponentParameters();
			$exchange_rate_order= $paramsC->get( 'exchange_rate_order', 0 );
			
			$currencyOrder = false;
			if ((int)$orderId > 0) {
				$currencyOrder = self::getCurrencyAndRateByOrder($orderId);
			}
			
			// 1) change the currency ID by order
			if (isset($currencyOrder->currency_id)) {
				$id 	= $currencyOrder->currency_id;
				$key 	= base64_encode(serialize((int)$id . ':' . (int)$orderId));
			}
			
			
			
			if ((int)$id > 0) {
				self::$currency[$key]	= PhocacartCurrency::getCurrency((int)$id, (int)$orderId);
			} else {
				self::$currency[$key]	= PhocacartCurrency::getCurrency(0, (int)$orderId);
			}
			
			// 1) change the currency exchange rate by order
			if (isset($currencyOrder->currency_exchange_rate) && $exchange_rate_order == 0) {
				
				self::$currency[$key]->exchange_rate = $currencyOrder->currency_exchange_rate;
			}
		}
		

		if (!empty(self::$currency[$key])) {
			$this->price_decimals 		= self::$currency[$key]->price_decimals;
			$this->price_dec_symbol		= self::$currency[$key]->price_dec_symbol;
			$this->price_thousands_sep	= self::$currency[$key]->price_thousands_sep;
			$this->price_format			= self::$currency[$key]->price_format;
			$this->price_currency_symbol= self::$currency[$key]->price_currency_symbol;
			$this->price_prefix			= self::$currency[$key]->price_prefix;
			$this->price_suffix			= self::$currency[$key]->price_suffix;
			$this->exchange_rate		= self::$currency[$key]->exchange_rate;
		}

	}
	
	/*
	 * 1) the price can be negative - for example rounding
	 * 2) or we can force the negative - e.g. for discount
	 */
	
	public function getPriceFormat($price, $negative = 0, $skipCurrencyConverting = 0) {
	
		
		
		if ($price < 0) {
			$negative = 1;
		}
		
		
		
	
		
		
		if ($skipCurrencyConverting == 0) {
			$price *= $this->exchange_rate;
		}
		
		// Round after exchange rate
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
		
		// If user is assigned to some group, the lowest price was selected from groups for all groups he is assigned
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
		//$paramsC 					= PhocacartUtils::getComponentParameters();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$tax_calculation			= $paramsC->get( 'tax_calculation', 0 );
		$display_unit_price			= $paramsC->get( 'display_unit_price', 1 );
		$zero_price_text			= $paramsC->get( 'zero_price_text', '' );
		$zero_price_label			= $paramsC->get( 'zero_price_label', '' );


		$priceO['taxtxt']			= $taxTitle;
		$priceO['taxcalc'] 			= $tax_calculation;// Set in Options: Brutto, Netto, None
		$priceO['taxcalctype'] 		= $taxCalculationType;// Set in Tax edit: Percentate, Fixed Amount
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
		
		if ($round == 1) {
			$priceO['netto']	= $this->roundPrice($priceO['netto']);
			$priceO['brutto']	= $this->roundPrice($priceO['brutto']);
			$priceO['tax']		= $this->roundPrice($priceO['tax']);
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
		//$paramsC 					= PhocacartUtils::getComponentParameters();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$rounding_calculation		= $paramsC->get( 'rounding_calculation', 1 );
		
		
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
		
		
		$rounding = $rounding_calculation; // 1 ... up, 2 ... down
		
		
		$priceR = round($price, $this->price_decimals, $rounding);
		
		return $priceR;
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
		$priceO['taxid']			= 0;
		$priceO['brutto']			= 0;
		$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_EXCL_TAX');
		$priceO['taxtxt']			= '';
		$priceO['bruttotxt'] 		= JText::_('COM_PHOCACART_'.$langPrefix.'PRICE_INCL_TAX');
		$priceO['zero']				= 0;
		$priceO['freeshipping'] 	= 0;
		
		$app						= JFactory::getApplication();
		//$paramsC 					= PhocacartUtils::getComponentParameters();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$tax_calculation_shipping	= $paramsC->get( 'tax_calculation_shipping', 0 );
		
		
		
		
		// E.G. if coupon set the shipping costs to null - free shipping
		if ($freeShipping == 1) {
			$priceO['netto']			= 0;
			$priceO['nettotxt'] 		= JText::_('COM_PHOCACART_FREE_SHIPPING');
			$priceO['tax']				= 0;
			$priceO['taxid']			= 0;
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
			$priceO['taxid']		= 0;
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
			$priceO['taxid']		= $taxId;
	
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
			$priceO['taxid']		= $taxId;
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
		
		if ($round == 1) {
			$priceO['netto']	= $this->roundPrice($priceO['netto']);
			$priceO['brutto']	= $this->roundPrice($priceO['brutto']);
			$priceO['tax']		= $this->roundPrice($priceO['tax']);
		}

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
		//$paramsC 					= PhocacartUtils::getComponentParameters();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$tax_calculation_payment	= $paramsC->get( 'tax_calculation_payment', 0 );
		
		// Define - the function always return all variables so we don't need to check them
		$priceO['nettoformat']		= '';
		$priceO['taxformat']		= '';
		$priceO['bruttoformat']		= '';
		$priceO['bruttotxt']		= '';
		$priceO['netto']			= 0;
		$priceO['tax']				= 0;
		$priceO['taxid']			= 0;
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
			$priceO['taxid']			= 0;
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
			$priceO['taxid']		= 0;
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
			$priceO['taxid']		= $taxId;			
	
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
			$priceO['taxid']		= $taxId;
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
		
		if ($round == 1) {
			$priceO['netto']	= $this->roundPrice($priceO['netto']);
			$priceO['brutto']	= $this->roundPrice($priceO['brutto']);
			$priceO['tax']		= $this->roundPrice($priceO['tax']);
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
	
	
	public function getPriceItemsChangedByAttributes(&$priceP, $attributes, $price, $item, $ajax = 0) {
		
		
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$display_unit_price	= $paramsC->get( 'display_unit_price', 1 );
		
		$fullAttributes		= array();// Array of integers only
		$thinAttributes		= array();// Array of full objects (full options object)
		if ($ajax == 1) {
			$fullAttributes = PhocacartAttribute::getAttributeFullValues($attributes);
			//$thinAttributes	= $attributes;
		} else {
			$fullAttributes = $attributes;
			//$thinAttributes = PhocacartAttribute::getAttributesSelectedOnly($attributes);
		}
		
		
		if (!empty($fullAttributes)) {
			foreach ($fullAttributes as $k => $v) {
				if (!empty($v->options)) {
					foreach($v->options as $k2 => $v2) {
						
						// Options:
						// a) STANDARD LOAD - we search for default values only to count them
						// b) AJAX LOAD - we search for selected values only (in this case attribute array only includes selected)
						// so if standard load - at start - we count only default values but when ajax reloads - e.g. some 
						// options were selected/deselected - we get only array with selected value so we count them all
						// EXAMPLE
						// Product A has option 1 and it is set as default at standard load - se we count only default_value
						// But when user add some new option, e.g. option 2 - we get this array (option1, option2) per ajax
						// and we count all the items in this array
						// STANDARD LOAD happens at rendering the page (including default values)
						// AJAX LOAD happens when user select or deselect attributes of options and ajax will be called
						// See: administrator\components\com_phocacart\libraries\phocacart\stock\stock.php
						// function getStockItemsChangedByAttributes - similar behaviour
						if ($ajax == 1 || ($ajax == 0 && isset($v2->default_value) && $v2->default_value == 1)) {
							if (isset($v2->title) && isset($v2->amount) && isset($v2->operator)) {
								$priceA = $price->getPriceItems($v2->amount, $item->taxid, $item->taxrate, $item->taxcalculationtype, $item->taxtitle);
								
								if ($v2->operator == '-') {
									$priceP['netto']	-= $priceA['netto'];
									$priceP['brutto']	-= $priceA['brutto'];
									$priceP['tax']		-= $priceA['tax'];
									
								} else if ($v2->operator == '+') {
									$priceP['netto']	+= $priceA['netto'] ;
									$priceP['brutto']	+= $priceA['brutto'] ;
									$priceP['tax']		+= $priceA['tax'] ;
									
								}
							}
						}
					}
					
				}
			}
		}

		
		// Standard Price - changed 
		$priceP['nettoformat'] 		= $price->getPriceFormat($priceP['netto']);
		$priceP['bruttoformat'] 	= $price->getPriceFormat($priceP['brutto']);
		$priceP['taxformat'] 		= $price->getPriceFormat($priceP['tax']);
		
	
		// Unit price
		$priceP['base'] 		= '';
		$priceP['baseformat'] 	= '';
		if (isset($item->unit_amount) && $item->unit_amount > 0 && isset($item->unit_unit) && (int)$display_unit_price > 0) {
			$priceP['base'] 		= $priceP['brutto'] / $item->unit_amount;
			$priceP['baseformat'] 	= $price->getPriceFormat($priceP['base']).'/'.$item->unit_unit;
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