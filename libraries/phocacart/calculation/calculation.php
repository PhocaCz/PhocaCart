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

class PhocacartCalculation
{
	
	public static function calculateDiscountPercentage($discount, $quantity, &$priceItems, &$total, $taxId = 0) {
		
		$price					= new PhocacartPrice();
		// $taxCalcType 1 ... percentage, 2 ... fixed amount (be aware it is not the tax calculation set in options: brutto, netto, none)
		$taxCalcType 			= $priceItems['taxcalctype'];
		
		$dB 					= $price->roundPrice($priceItems['brutto'] * $discount / 100);
		$dN 					= $price->roundPrice($priceItems['netto'] * $discount / 100);
		$dT 					= $price->roundPrice($priceItems['tax'] * $discount / 100);
		
		
		$priceItems['bruttodiscount'] 	= $taxCalcType == 2 ? $dN : $dB;
		$priceItems['nettodiscount'] 	= $dN;
		$priceItems['taxdiscount'] 		= $taxCalcType == 2 ? 0 : $dT;
		
		$priceItems['brutto'] 	-= $priceItems['bruttodiscount'];
		$priceItems['netto'] 	-= $priceItems['nettodiscount'];
		$priceItems['tax'] 		-= $priceItems['taxdiscount'];
		

		if (!empty($total)) {
			$total['brutto']				-= $taxCalcType == 2 ? $dN * $quantity : $dB * $quantity;
			$total['netto']					-= $dN * $quantity;
			$total['tax'][$taxId]['tax']	-= $taxCalcType == 2 ? 0 : $dT * $quantity;
			
			
		}
		
		return true;
		
	}
	
	public static function calculateDiscountFixedAmount($ratio, $quantity, &$priceItems, &$total, $taxId = 0) {
	
		$price					= new PhocacartPrice();
		
		// $taxCalcType type 1 ... percentage, 2 ... fixed amount (be aware it is not the tax calculation set in options: brutto, netto, none)
		$taxCalcType = $priceItems['taxcalctype'];
		

		$dB = $price->roundPrice($priceItems['brutto'] * $ratio/100);
		$dN = $price->roundPrice($priceItems['netto'] * $ratio/100);
		$dT = $price->roundPrice($priceItems['tax'] * $ratio/100);
		

		// Brutto If fixed VAT ($taxCalcType  == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
		if ($priceItems['brutto'] < $dB) {
			$priceItems['bruttodiscount'] 	= 0;
			$priceItems['brutto']			= 0;
		} else {
			$priceItems['bruttodiscount'] 	= $taxCalcType == 2 ? $dN : $dB;
			$priceItems['brutto'] 			-= $priceItems['bruttodiscount'];
		}
		
		// Netto
		if ($priceItems['netto'] < $dN) {
			$priceItems['nettodiscount'] 	= 0;
			$priceItems['netto'] 			= 0;
		} else {
			$priceItems['nettodiscount'] 	= $dN;
			$priceItems['netto'] 			-= $priceItems['nettodiscount'];
		}
		
		// Tax
		if ($priceItems['tax'] < $dT) {
			$priceItems['taxdiscount'] 		= 0;
			$priceItems['tax'] 				= 0;
		} else {
			$priceItems['taxdiscount'] 		= $taxCalcType == 2 ? 0 : $dT;
			$priceItems['tax'] 				-= $priceItems['taxdiscount'];
		}
		
		
		
		if (!empty($total)) {

			// Brutto
			if ($priceItems['brutto'] < $dB) {
				$total['brutto']		-= $taxCalcType == 2 ? $dN * $quantity : $priceItems['brutto'] * $quantity;
			} else {
				$total['brutto']		-= $taxCalcType == 2 ? $dN * $quantity : $dB * $quantity;
			}
			
			// Netto
			if ($priceItems['netto'] < $dN) {
				$total['netto']			-= $priceItems['netto'] * $quantity;
			} else {
				$total['netto']			-= $dN * $quantity;
			}
			
			// Tax
			if ($priceItems['tax'] < $dT) {
				$total['tax'][$taxId]['tax']	-= $taxCalcType == 2 ? 0 : $priceItems['tax'] * $quantity;
			} else {
				$total['tax'][$taxId]['tax']	-= $taxCalcType == 2 ? 0 : $dT * $quantity;
			}
			
		}
		
		return true;
	}
	
	
	public static function correctItemsIfNull(&$priceItems) {
	
		if ($priceItems['netto'] < 0 || $priceItems['netto'] == 0) {
			$priceItems['brutto'] 	= 0;
			$priceItems['tax'] 		= 0;
		}
		
		if ($priceItems['nettodiscount'] < 0 || $priceItems['nettodiscount'] == 0) {
			$priceItems['bruttodiscrount'] 	= 0;
			$priceItems['taxdiscount'] 		= 0;
		}
		
		return true;
	}
	
	public static function correctTotalIfNull(&$total, $taxId) {
		
		if ($total['netto']	< 0 || $total['netto'] == 0) {
			$total['brutto'] 				= 0;
			$total['tax'][$taxId]['tax'] 	= 0;
		}
		return true;
	}
	
	public static function formatItems(&$priceItems) {
		
		$price						= new PhocacartPrice();
		
		$priceItems['bruttoformat']	= $price->getPriceFormat($priceItems['brutto']);
		$priceItems['nettoformat']	= $price->getPriceFormat($priceItems['netto']);
		$priceItems['taxformat']	= $price->getPriceFormat($priceItems['tax']);
		
		return true;
		
	}
}
?>