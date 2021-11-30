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

	public static function calculateDiscountPercentage($discount, $quantity, &$priceItems, &$total, $taxKey = '') {

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
			$total['tax'][$taxKey]['tax']	-= $taxCalcType == 2 ? 0 : $dT * $quantity;
			$total['tax'][$taxKey]['netto']	-= $taxCalcType == 2 ? 0 : $dN * $quantity;
			$total['tax'][$taxKey]['brutto']	-= $taxCalcType == 2 ? 0 : $dB * $quantity;
		}

		return true;

	}

	public static function calculateDiscountFixedAmount($ratio, $quantity, &$priceItems, &$total, $taxKey = '') {



		$price					= new PhocacartPrice();

		// $taxCalcType type 1 ... percentage, 2 ... fixed amount (be aware it is not the tax calculation set in options: brutto, netto, none)
		$taxCalcType = $priceItems['taxcalctype'];


		// Reset info about discount for each step (reward points, product discount, cart discount, coupon)
		$priceItems['bruttodiscount'] 	= 0;
		$priceItems['nettodiscount'] 	= 0;
		$priceItems['taxdiscount'] 		= 0;


		$dB = $price->roundPrice($priceItems['brutto'] * $ratio/100);
		$dN = $price->roundPrice($priceItems['netto'] * $ratio/100);
		$dT = $price->roundPrice($priceItems['tax'] * $ratio/100);


		// Price before discount
		$pbD = array();
		$pbD['brutto'] 	= $priceItems['brutto'];
		$pbD['netto'] 	= $priceItems['netto'];
		$pbD['tax'] 	= $priceItems['tax'];



		// Brutto If fixed VAT ($taxCalcType  == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
		if ($priceItems['brutto'] < $dB) {
			$priceItems['bruttodiscount'] 	= $priceItems['brutto'];
			$priceItems['brutto']			= 0;
		} else {
			$priceItems['bruttodiscount'] 	= $taxCalcType == 2 ? $dN : $dB;
			$priceItems['brutto'] 			-= $priceItems['bruttodiscount'];
		}

		// Netto
		if ($priceItems['netto'] < $dN) {
			$priceItems['nettodiscount'] 	= $priceItems['netto'];
			$priceItems['netto'] 			= 0;
		} else {
			$priceItems['nettodiscount'] 	= $dN;
			$priceItems['netto'] 			-= $priceItems['nettodiscount'];
		}

		// Tax
		if ($priceItems['tax'] < $dT) {
			$priceItems['taxdiscount'] 		= $priceItems['tax'];

			$priceItems['tax'] 				= 0;
		} else {
			$priceItems['taxdiscount'] 		= $taxCalcType == 2 ? 0 : $dT;
			$priceItems['tax'] 				-= $priceItems['taxdiscount'];

		}



		if (!empty($total)) {


			// Possible TO DO - add different condition for netto and brutto
			/*
			 * if ($taxCalcType == 2) {
				if ($pbD['netto'] < $dB) {
			} else {
				if ($pbD['brutto'] < $dB) {
			}
			*/

			// Brutto
			if ($pbD['brutto'] < $dB) {
				$total['brutto']				-= $taxCalcType == 2 ? $pbD['netto'] * $quantity : $pbD['brutto'] * $quantity;
				$total['tax'][$taxKey]['brutto']	-= $taxCalcType == 2 ? $pbD['netto'] * $quantity : $pbD['brutto'] * $quantity;
			} else {
				$total['brutto']				-= $taxCalcType == 2 ? $dN * $quantity : $dB * $quantity;
				$total['tax'][$taxKey]['brutto']	-= $taxCalcType == 2 ? $dN * $quantity : $dB * $quantity;
			}

			// Netto
			if ($pbD['netto'] < $dN) {
				$total['netto']					-= $pbD['netto'] * $quantity;
				$total['tax'][$taxKey]['netto']	-= $pbD['netto'] * $quantity;


			} else {


				$total['netto']					-= $dN * $quantity;
				$total['tax'][$taxKey]['netto']	-= $dN * $quantity;

			}


			// Tax
			if ($pbD['tax'] < $dT) {
				$total['tax'][$taxKey]['tax']	-= $taxCalcType == 2 ? 0 : $pbD['tax'] * $quantity;
			} else {
				$total['tax'][$taxKey]['tax']	-= $taxCalcType == 2 ? 0 : $dT * $quantity;
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

	public static function correctTotalIfNull(&$total, $taxKey) {

		if ($total['netto']	< 0 || $total['netto'] == 0) {
			$total['brutto'] 				= 0;
			$total['tax'][$taxKey]['tax'] 	= 0;
			$total['tax'][$taxKey]['netto'] 	= 0;
			$total['tax'][$taxKey]['brutto'] = 0;
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
