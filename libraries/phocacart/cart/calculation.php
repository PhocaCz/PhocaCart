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

class PhocacartCartCalculation
{
	
	public $correctsubtotal			= 1;// because of rounding 0.25 -> 0.3 or 0.2 it can come to difference e.g. 0.01
	
	public function __construct() {}
	
	
	// ==============
	// BASIC PRODUCT
	// ==============
	public function calculateBasicProducts(&$fullItems, &$fullItemsGroup, &$total, &$stock, &$minqty, &$minmultipleqty, $items) {
		
		$app						= JFactory::getApplication();
		$paramsC					= PhocacartUtils::getComponentParameters();
		$tax_calculation			= $paramsC->get( 'tax_calculation', 0 );
		// Moved to product parameters
		//$min_ quantity_calculation	= $paramsC->get( 'min_ quantity_calculation', 0 );
		//$stock_ calculation	= $paramsC->get( 'stock_ calculation', 0 );
		$price						= new PhocacartPrice();
		
		
		$total['netto']				= 0;
		$total['brutto']			= 0;
		$total['brutto_currency']	= 0;
		$total['tax']				= array();
		$total['weight']			= 0;
		$total['volume']			= 0;
		$total['dnetto']			= 0;	
		$total['quantity']			= 0;
		
		$total['max_length']		= 0;
		$total['max_width']			= 0;
		$total['max_height']		= 0;
		
		$total['points_needed']		= 0;
		$total['points_received']	= 0;
		
		// Free shipping or payment
		$total['free_shipping']				= 0;
		$total['free_payment']				= 0;
		
		// Discount fixed amount
		$total['discountcartfixedamount']	= array();
		$total['couponcartfixedamount']		= array();
		$total['discountcarttxtsuffix']		= '';
		$total['couponcarttxtsuffix']		= '';
		
		$total['rewardproductusedtotal']	= '';
		$total['rewardproducttxtsuffix']	= '';
		
		// OPTIONS (VARIANTS) QUANTITY
		// The same option can be in different items
		$optionsQuantity							= array();
		
		// Rounding
		$total['rounding']			= 0;
		$total['rounding_currency']	= 0;
		
		foreach($items as $k => $v) {
					
			$item 	= explode(':', $k);
			$itemId = $item[0];
			
			
			// Define
			$fullItems[$k]['id'] 					= (int)$itemId;
			$fullItems[$k]['idkey'] 				= (string)$k;
			
			$fullItems[$k]['netto'] 				= 0;
			$fullItems[$k]['brutto'] 				= 0;
			$fullItems[$k]['tax'] 					= 0;
			$fullItems[$k]['final'] 				= 0;// netto or brutto * quantity

			$fullItems[$k]['taxid'] 				= 0;
			$fullItems[$k]['taxtitle']				= '';
			$fullItems[$k]['weight']				= '';
			$fullItems[$k]['volume']				= '';
			$fullItems[$k]['quantity'] 				= $fQ	= (int)$v['quantity'];// Quantity of product - one product

			$fullItems[$k]['catid'] 				= 0;
			$fullItems[$k]['alias'] 				= '';
			$fullItems[$k]['sku'] 					= '';
			$fullItems[$k]['image'] 				= '';
			$fullItems[$k]['title'] 				= '';
			$fullItems[$k]['stock'] 				= 0; // database value set in product settings
			$fullItems[$k]['stockvalid'] 			= 1; // variable to inform if stock validity is ok
			$fullItems[$k]['stockcalculation'] 		= 0; 
			$fullItems[$k]['minqty'] 				= 0; // database value set in product settings
			$fullItems[$k]['minmultipleqty'] 		= 0;
			$fullItems[$k]['minqtyvalid'] 			= 1; // variable to inform if minimum order is ok
			$fullItems[$k]['minmultipleqtyvalid']	= 1;
			
			// DISCOUNTS (Product, Cart, Voucher) / Fixed amount / Percentage
			$fullItems[$k]['discountproduct']		= 0;
			$fullItems[$k]['discountproducttitle']	= '';
			$fullItems[$k]['discountcart']			= 0;
			$fullItems[$k]['discountcarttitle']		= '';
			$fullItems[$k]['discountcartfixedid']	= 0;
			$fullItems[$k]['discountcartid']		= 0;
		
			
			$fullItems[$k]['couponcart'] 			= 0;
			$fullItems[$k]['couponcarttitle'] 		= '';
			$fullItems[$k]['couponcartfixedid']		= 0;
			$fullItems[$k]['couponcartid']			= 0;
			
			$fullItems[$k]['rewardproduct'] 		= 0;
			$fullItems[$k]['rewardproducttitle'] 	= JText::_('COM_PHOCACART_REWARD_POINTS');
			$fullItems[$k]['rewardproductpoints']	= 0;
			$fullItems[$k]['rewardproducttxtsuffix']= '';
			$fullItems[$k]['points_needed']			= 0;
			$fullItems[$k]['points_received']		= 0;
			
			
			// GROUP QUANTITY
			// Get quantity of a group. Group is sum of all product variations
			// - explained in PhocacartDiscountProduct::getProductDiscount
			
			
			$fullItemsGroup[$itemId]['id']						= (int)$itemId;
			$fullItemsGroup[$itemId]['title']					= '';
			if (isset($fullItemsGroup[$itemId]['quantity'])) {
				$fullItemsGroup[$itemId]['quantity']	+= (int)$v['quantity'];
			} else {
				$fullItemsGroup[$itemId]['quantity']	= (int)$v['quantity'];
				
			}
		
			$total['quantity']						+= (int)$v['quantity'];
			
			// ATTRIBUTES	
			$attribs = array();
			if (!empty($item[1])) {
				$attribs = unserialize(base64_decode($item[1]));
			}
			
			// ITEM D - product info from database
			$itemD = PhocacartProduct::getProduct((int)$itemId, (int)$v['catid']);

			// Correct the tax rate - no tax calculation, no tax rate for each product
			if (!empty($itemD) && $tax_calculation == 0) {
				$itemD->taxrate = 0;
			}
			
			if (isset($itemD->id) && (int)$itemD->id > 0) {
				$fullItems[$k]['title'] 		= $itemD->title;
				$fullItems[$k]['catid']			= $itemD->catid;
				$fullItems[$k]['alias'] 		= $itemD->alias;
				$fullItems[$k]['sku'] 			= $itemD->sku;
				$fullItems[$k]['image'] 		= $itemD->image;
			
				$priceI = $price->getPriceItems($itemD->price, $itemD->taxid, $itemD->taxrate, $itemD->taxcalculationtype, $itemD->taxtitle, 0, '', 0, 1, $itemD->group_price);
				
			
				
				$fullItems[$k]['netto'] 			= $priceI['netto'];
				$fullItems[$k]['brutto'] 			= $priceI['brutto'];
				$fullItems[$k]['tax'] 				= $priceI['tax'];
				$fullItems[$k]['default_price']		= $itemD->price;
			
				$fullItems[$k]['price'] 			= $price->getPriceItem($itemD->price, $itemD->group_price, 0);
				$fullItems[$k]['taxid'] 			= $itemD->taxid;
				$fullItems[$k]['taxrate'] 			= $itemD->taxrate;
				$fullItems[$k]['taxtitle'] 			= JText::_($itemD->taxtitle);
				$fullItems[$k]['taxtcalctype']		= $itemD->taxcalculationtype;
				$fullItems[$k]['weight']			= $itemD->weight;
				$fullItems[$k]['volume']			= $itemD->volume;
				$fullItems[$k]['stock'] 			= $itemD->stock;
				$fullItems[$k]['stockadvanced'] 	= 0;
				$fullItems[$k]['stockcalculation']	= $itemD->stock_calculation;
				$fullItems[$k]['minqty'] 			= $itemD->min_quantity;
				$fullItems[$k]['minmultipleqty'] 	= $itemD->min_multiple_quantity;
				$fullItems[$k]['minqtycalculation'] = $itemD->min_quantity_calculation;
				
				$fullItems[$k]['default_points_received']	= $itemD->points_received;
				$pointsN = PhocacartReward::getPoints($itemD->points_needed, 'needed');
				$pointsR = PhocacartReward::getPoints($itemD->points_received, 'received', $itemD->group_points_received);
				$fullItems[$k]['points_needed']		= $pointsN;
				$fullItems[$k]['points_received']	= $pointsR;
				
				// Group
				$fullItemsGroup[$itemId]['minqty']				= $itemD->min_quantity;
				$fullItemsGroup[$itemId]['minmultipleqty']		= $itemD->min_multiple_quantity;
				$fullItemsGroup[$itemId]['title']				= $itemD->title;
				$fullItemsGroup[$itemId]['minqtyvalid'] 		= 1;
				$fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
				
				
				
				
				// Advanced Stock Calculation
				if ($fullItems[$k]['stockcalculation'] == 2) {
					$fullItems[$k]['stockadvanced'] = PhocacartAttribute::getCombinationsStockByKey($k);	
				} 
				
				// Total
				$total['netto']					+= ($fullItems[$k]['netto'] * $fQ);
				$total['brutto']				+= ($fullItems[$k]['brutto'] * $fQ);
				$total['weight']				+= ($fullItems[$k]['weight'] * $fQ);
				$total['volume']				+= ($fullItems[$k]['volume'] * $fQ);
				
				$total['max_length']		= $itemD->length > $total['max_length'] ? $itemD->length : $total['max_length'];
				$total['max_width']			= $itemD->width > $total['max_width'] ? $itemD->width : $total['max_width'];
				$total['max_height']		= $itemD->height > $total['max_height'] ? $itemD->height : $total['max_height'];
				
				$total['points_needed']			+= ($fullItems[$k]['points_needed'] * $fQ);
				$total['points_received']		+= ($fullItems[$k]['points_received'] * $fQ);
				
				if (!isset($total['tax'][$itemD->taxid]['tax'])) {
					$total['tax'][$itemD->taxid]['tax'] 	= 0;// Define
				}

				$total['tax'][$itemD->taxid]['tax']	+= ($fullItems[$k]['tax'] *$fQ);
				$taxSuffix = '';
				if ($itemD->taxcalculationtype == 1) {
					$taxSuffix = ' ('.($price->getTaxFormat($itemD->taxrate, $itemD->taxcalculationtype, 0)).')';
				}
				
				$total['tax'][$itemD->taxid]['title']	= JText::_($itemD->taxtitle) . $taxSuffix;
				$total['tax'][$itemD->taxid]['type']	= $itemD->taxcalculationtype;
				$total['tax'][$itemD->taxid]['rate']	= $itemD->taxrate;
				

				// ==========
				// ATTRIBUTES
				// ==========
				//
				// Stock handling - one variant can be set in e.g. two products, so we need to count attributes stock:
				
				
				
				
				if (!empty($attribs)) {
					foreach ($attribs as $k2 => $v2) {
						
						// Make array from all attributes even they are not multiple - to go through the foreach
						if (!is_array($v2)) {
							$v2 = array(0 => $v2);
						}
						
						if(!empty($v2)) {
							// Be aware the k3 is not the key of attribute
							// this is the k2
							foreach ($v2 as $k3 => $v3) {
								
								if ((int)$k2 > 0 && (int)$v3 > 0) {
									
									$attrib = PhocacartAttribute::getAttributeValue((int)$v3, (int)$k2);
									

									if (isset($attrib->title) && isset($attrib->amount) && isset($attrib->operator)) {
										
										// If there is fixed VAT - don't change it in attributes - it is just fix - set taxtrate to 0
										if ($itemD->taxcalculationtype == 2) {
											$priceA = $price->getPriceItems($attrib->amount, $itemD->taxid, 0, $itemD->taxcalculationtype, $itemD->taxtitle);
										} else {
											$priceA = $price->getPriceItems($attrib->amount, $itemD->taxid, $itemD->taxrate, $itemD->taxcalculationtype, $itemD->taxtitle);
										}
										
								
										//$fQ 	= (int)$fullItems[$k]['quantity'];
										// Price
										if ($attrib->operator == '-') {
											
											$fullItems[$k]['netto'] 	-= $priceA['netto'];
											$fullItems[$k]['netto'] < 0 ? $fullItems[$k]['netto'] = 0 : $total['netto']	-= ($priceA['netto'] * $fQ);
											$fullItems[$k]['brutto'] 	-= $priceA['brutto'];
											$fullItems[$k]['brutto'] < 0 ? $fullItems[$k]['brutto'] = 0 : $total['brutto'] -= ($priceA['brutto'] * $fQ);
											$fullItems[$k]['tax']		-= $priceA['tax'];
											$fullItems[$k]['tax'] < 0 ? $fullItems[$k]['tax'] = 0 : $total['tax'][$itemD->taxid]['tax'] -= ($priceA['tax'] * $fQ);
											
										} else if ($attrib->operator == '+') {
											
											$fullItems[$k]['brutto'] 			+= $priceA['brutto'];// * multiply in render checkout
											$fullItems[$k]['netto'] 			+= $priceA['netto'];// * multiply in render checkout
											$fullItems[$k]['tax']				+= $priceA['tax'];// * multiply in render checkout
											$total['netto']						+= ($priceA['netto'] * $fQ );
											$total['brutto']					+= ($priceA['brutto'] * $fQ );
											$total['tax'][$itemD->taxid]['tax']	+= ($priceA['tax'] * $fQ );
											
										}
										
										// Weight
										if ($attrib->operator_weight == '-') {
											$fullItems[$k]['weight'] 	-= $attrib->weight;
											$fullItems[$k]['weight'] < 0 ? $fullItems[$k]['weight'] = 0 : $total['weight']			-= ($attrib->weight * $fullItems[$k]['quantity']);
										}  else if ($attrib->operator_weight == '+') {
											$fullItems[$k]['weight'] 	+= $attrib->weight;
											$total['weight']			+= ($attrib->weight * $fullItems[$k]['quantity']);
										}

										// Volume - not used now
									/*	if ($attrib->operator_volume == '-') {
											$fullItems[$k]['volume'] 	-= $attrib->volume;
											$fullItems[$k]['volume'] < 0 ? $fullItems[$k]['volume'] = 0 : $total['volume']			-= ($attrib->volume * $fullItems[$k]['quantity']);
										}  else if ($attrib->operator_volume == '+') {
											$fullItems[$k]['volume'] 	+= $attrib->volume;
											$total['volume']			+= ($attrib->volume * $fullItems[$k]['quantity']);
										} */
										
									
										
										
										if (isset($optionsQuantity[$attrib->id])) {
											$optionsQuantity[$attrib->id] += (int)$fQ;
											
										} else {
											$optionsQuantity[$attrib->id] = (int)$fQ;
										
										}
										// STOCK-1 ... we count each product variation separately
										if ($fullItems[$k]['stockcalculation'] == 1 && (int)$optionsQuantity[$attrib->id] > (int)$attrib->stock) {
											$total['stockvalid'] 					= 0;
											$fullItems[$k]['stockvalid'] 			= 0;
										}
			
										
										// Attribute values
										$fullItems[$k]['attributes'][$attrib->aid][$k3]['aid'] 		= $attrib->aid;// Attribute Id
										$fullItems[$k]['attributes'][$attrib->aid][$k3]['atitle'] 	= $attrib->atitle;
										$fullItems[$k]['attributes'][$attrib->aid][$k3]['oid'] 		= $attrib->id;// Option Id
										$fullItems[$k]['attributes'][$attrib->aid][$k3]['otitle'] 	= $attrib->title;
									}
								}
							}
						}
					}
				} 
				
		
				
				
				// ==============================
				// MINIUM ORDER AMOUNT 
				// ==============================
				// THERE CAN BE THREE METHODS HOW TO COUNT MINIMUM ORDER AMOUNT
				// a) every product is unique (Product A - Option A, Product A - Option B are two different products) 
				// b) there are product groups (Product A- Option A, Product A - Option B is still one product - product A)
				// c) advanced stock management - in this case it is the same like a)
				
				if ($fullItems[$k]['minqtycalculation'] == 1 || $fullItems[$k]['minqtycalculation'] == 2) {
					// a)
					// MINIMUM QUANTITY - FOR ITEM - PRODUCT VARIATION - each product variation
					if ((int)$fullItems[$k]['quantity'] < (int)$fullItems[$k]['minqty']) {
						$minqty['valid'] 				= 0;
						$fullItems[$k]['minqtyvalid'] 	= 0;
					}
					
					if ((int)$fullItems[$k]['minmultipleqty'] == 0) {
						// Do not modulo by zero
						// Set it back because we are in foreach
						$minmultipleqty['valid']	 			= 1;
						$fullItems[$k]['minmultipleqtyvalid'] 	= 1;
					} else if (((int)$fullItems[$k]['quantity']) % (int)$fullItems[$k]['minmultipleqty'] != 0) {
						$minmultipleqty['valid']	 			= 0;
						$fullItems[$k]['minmultipleqtyvalid'] 	= 0;
					}
				
				} else {
					
					// b)
					// MINIMUM QUANTITY - FOR GROUP (Group is the same product but with different options values) - MAIN PRODUCT
					if (empty($fullItemsGroup[$itemId]['minqty'])) {
						$minqty['valid'] 						= 1;
						$fullItemsGroup[$itemId]['minqtyvalid'] = 1;					
					} else if ((int)$fullItemsGroup[$itemId]['quantity'] < (int)$fullItemsGroup[$itemId]['minqty']) {
						$minqty['valid'] 						= 0;
						$fullItemsGroup[$itemId]['minqtyvalid'] = 0;
					} else {
						// Set it back because we are in foreach
						$minqty['valid'] 						= 1;
						$fullItemsGroup[$itemId]['minqtyvalid'] = 1;
					}
					
					// MINIMUM MULTIPLE QUANTITY
					if (empty($fullItemsGroup[$itemId]['minmultipleqty'])) {
						$minmultipleqty['valid']	 					= 1;
						$fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
					} else if ($fullItemsGroup[$itemId]['minmultipleqty'] == 0) {
						// Do not modulo by zero
						// Set it back because we are in foreach
						$minmultipleqty['valid']	 					= 1;
						$fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
					} else if (((int)$fullItemsGroup[$itemId]['quantity']) % (int)$fullItemsGroup[$itemId]['minmultipleqty'] != 0) {
						$minmultipleqty['valid']	 					= 0;
						$fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 0;
					} else {
						// Set it back because we are in foreach
						$minmultipleqty['valid']	 					= 1;
						$fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
					}
				}
				
				
				
				// ==============================
				// STOCK VALID
				// ==============================
				
				// The difference between STOCK-1, STOCK-0
				// b) STOCK-0 - There is only one main product even it is divided into more product variations
				//         - so we count only main product - group
				// a) STOCK-1 - Each product variation is one product but this means that product without any variation
				//         - is in one one of the product varitation:
				// Product 1 Option A - one product
				// Product 1 Option B - one product
				// Product 1 (no options) - one product - as sum there are 3 products
				// c) STOCK-2 - advanced stock management
				// Product 1 Option A - one product
				// Product 1 Option B - one product
				// Product 1 (no options) - one product - as sum there are 3 products
				// Product 1 Option A + Option B - one product
				
				// STOCK-2 ... we count main product as own product variation - only in case it does not have any attributes
				//         ... but combination of attributes can create different products
				
				if ($fullItems[$k]['stockcalculation'] == 2 && (int)$fullItems[$k]['quantity'] > (int)$fullItems[$k]['stockadvanced']) {
					$stock['valid'] 				= 0;// Global - some of the product is out of stock
					$fullItems[$k]['stockvalid'] 	= 0;// Current product is out of stock
					
				}
				
				// STOCK-1 ... we count main product as own product variation - only in case it does not have any attributes
				//             variations of product are checked in ohter place (cca line 271)
				// THIS IS DIVEDED RULE - ONE HERE, SECOND ABOVE IN ATTRIBUTES FOREACH
				if ($fullItems[$k]['stockcalculation'] == 1 && empty($fullItems[$k]['attributes']) && (int)$fullItems[$k]['quantity'] > (int)$fullItems[$k]['stock']) {
					$stock['valid'] 				= 0;// Global - some of the product is out of stock
					$fullItems[$k]['stockvalid'] 	= 0;// Current product is out of stock
					
				}
				
				// STOCK-0 ... we count main product as group: Product 1 Option A ... 5 + Product 1 Option B ... 5 = 10
				if ($fullItems[$k]['stockcalculation'] == 0 && (int)$fullItemsGroup[$itemId]['quantity'] > (int)$fullItems[$k]['stock']) {
					$stock['valid'] 				= 0;// Global - some of the product is out of stock
					$fullItems[$k]['stockvalid'] 	= 0;// Current product is out of stock
					
				}

				$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
				
			}
			
			if ($this->correctsubtotal) {
				$this->correctSubTotal($fullItems[$k], $total);
			}
	
		}

	}
	
	
	// ================
	// REWARD POINTS
	// ================
	public function calculateRewardDiscounts(&$fullItems, &$fullItemsGroup, &$total, $rewardCart) {
		
		$price		= new PhocacartPrice();
		$reward 	= new PhocacartReward();
		$rewards	= array();
		$rewards['used']		= $reward->checkReward((int)$rewardCart['used']);
		$rewards['usedtotal']	= 0;
		
		
		foreach($fullItems as $k => $v) {

			if (isset($v['points_needed']) && (int)$v['points_needed'] > 0) {
				
				$groupId 			= $v['id'];
				$taxId				= $v['taxid'];
				$fQ					= $v['quantity'];
				$tCt				= $v['taxtcalctype'];
				
				$rewards['needed']		= $fQ * $v['points_needed'];
				
				$reward->calculatedRewardDiscountProduct($rewards);
				
				if (isset($rewards['percentage']) && $rewards['percentage'] > 0) {
					
					$fullItems[$k]['rewardproduct'] 		= 1;
					$fullItems[$k]['rewardproductpoints'] 	= $rewards['usedproduct'];
					$fullItems[$k]['rewardproducttitle'] 	= JText::_('COM_PHOCACART_REWARD_POINTS');
					
					
					$dB = $price->roundPrice($v['brutto'] * $rewards['percentage'] / 100);
					$dN = $price->roundPrice($v['netto'] * $rewards['percentage'] / 100);
					$dT = $price->roundPrice($v['tax'] * $rewards['percentage'] / 100);
					
					///$dB = $v['brutto'] * $rewards['percentage'] / 100;
					///$dN = $v['netto'] * $rewards['percentage'] / 100;
					///$dT = $v['tax'] * $rewards['percentage'] / 100;
					

					// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
					$fullItems[$k]['brutto'] 		-= $tCt == 2 ? $dN : $dB;
					$fullItems[$k]['netto'] 		-= $dN;
					$fullItems[$k]['tax'] 			-= $tCt == 2 ? 0 : $dT;
					
					$total['brutto']				-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
					$total['netto']					-= $dN * $fQ;
					$total['tax'][$taxId]['tax']	-= $tCt == 2 ? 0 : $dT * $fQ;
					
					
					$fullItems[$k]['rewardproducttxtsuffix'] 	= ' ('.$rewards['usedproduct'].')';
					$total['rewardproducttxtsuffix'] 			= ' ('.$rewards['usedtotal'].')';
					$total['rewardproductusedtotal']			= $rewards['usedtotal'];
					
					
					
					if ($fullItems[$k]['netto'] < 0 || $fullItems[$k]['netto'] == 0) {
						$fullItems[$k]['brutto'] 	= 0;
						$fullItems[$k]['tax'] 		= 0;
					}
					if ($total['netto']	< 0 || $total['netto'] == 0) {
						$total['brutto'] 				= 0;
						$total['tax'][$taxId]['tax'] 	= 0;
					}
				}
			
				$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
				
				if ($this->correctsubtotal) {
					$this->correctSubTotal($fullItems[$k], $total);
				}
			}
		}
	}
	
	
	
	// ================
	// PRODUCT DISCOUNT
	// ================
	public function calculateProductDiscounts(&$fullItems, &$fullItemsGroup, &$total) {
		
		
		$price	= new PhocacartPrice();
		
		
		foreach($fullItems as $k => $v) {

			// Get quantity of a group. Group is sum of all product variations
			// - explained in PhocacartDiscountProduct::getProductDiscount
			$groupId 			= $v['id'];
			$v['quantitygroup'] = $v['quantity'];// define
			if (isset($fullItemsGroup[$groupId]['quantity'])) {
				$v['quantitygroup'] = $fullItemsGroup[$groupId]['quantity'];
			}
			$taxId				= $v['taxid'];
			$fQ					= $v['quantity'];
			$tCt				= $v['taxtcalctype'];
			
			$discount 			= PhocacartDiscountProduct::getProductDiscount($v['id'], $v['quantitygroup'], $v['quantity']);
			
			if (isset($discount['discount']) && isset($discount['calculation_type'])) {
				
				$fullItems[$k]['discountproduct'] 		= 1;
				$fullItems[$k]['discountproducttitle'] 	= $discount['title'];
				
				if ($discount['calculation_type'] == 0) {
					// ------------
					// FIXED AMOUNT
					// ------------
					
					if ($v['netto'] > 0) {
						//$r = $discount['discount'] / $fQ * 100 / $v['netto'];// Ratio to use it for brutto and tax
						// PRODUCT DISCOUNT - DON'T DIVIDE IT INTO QUANTITY 
						//if you set 500 fixed amount as discount - it applies to each quantity
						$r = $discount['discount'] * 100 / $v['netto'];
					} else {
						$r = 0;
					}
					
					$dB = $price->roundPrice($v['brutto'] * $r/100);
					$dN = $price->roundPrice($v['netto'] * $r/100);
					$dT = $price->roundPrice($v['tax'] * $r/100);
					
					///$dB = $v['brutto'] * $r/100;
					///$dN = $v['netto'] * $r/100;
					///$dT = $v['tax'] * $r/100;
					
					
				
					// If fixed amount discount is larger than the price, price will be 0
					// If fixed amount discount is larger than price, then total - whole price
					// because maximum discount is the price itself - we cannot go to minus
					
					// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
					if ($fullItems[$k]['brutto'] < $dB) {
						$fullItems[$k]['brutto'] = 0;
						$total['brutto']		-=  $tCt == 2 ? $dN * $fQ : $v['brutto'] * $fQ;
					} else {
						$fullItems[$k]['brutto'] 	-= $tCt == 2 ? $dN : $dB;
						$total['brutto']			-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
					}
					
					if ($fullItems[$k]['netto'] < $dN) {
						$fullItems[$k]['netto'] = 0;
						$total['netto']			-= $v['netto'] * $fQ;
					} else {
						$fullItems[$k]['netto'] 	-= $dN;
						$total['netto']				-= $dN * $fQ;
						
					}
					
					if ($fullItems[$k]['tax'] < $dT) {
						$fullItems[$k]['tax'] = 0;
						$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $v['tax'] * $fQ;
					} else {
						$fullItems[$k]['tax'] 		-= $tCt == 2 ? 0 : $dT;
						$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $dT * $fQ;
					}
					

	
	
				} else {
					// ------------
					// PERCENTAGE
					// ------------
					$dB = $price->roundPrice($v['brutto'] * $discount['discount'] / 100);
					$dN = $price->roundPrice($v['netto'] * $discount['discount'] / 100);
					$dT = $price->roundPrice($v['tax'] * $discount['discount'] / 100);
					
					///$dB = $v['brutto'] * $discount['discount'] / 100;
					///$dN = $v['netto'] * $discount['discount'] / 100;
					///$dT = $v['tax'] * $discount['discount'] / 100;
					
					// Fixed VAT, not percentage
					
					
					/*$fullItems[$k]['brutto'] 		-= $dB;
					$fullItems[$k]['netto'] 		-= $dN;
					$fullItems[$k]['tax'] 			-= $dT;
					
					$total['brutto']				-= $dB * $fQ;
					$total['netto']					-= $dN * $fQ;
					$total['tax'][$taxId]['tax']	-= $dT * $fQ;*/
					
					// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
					$fullItems[$k]['brutto'] 		-= $tCt == 2 ? $dN : $dB;
					$fullItems[$k]['netto'] 		-= $dN;
					$fullItems[$k]['tax'] 			-= $tCt == 2 ? 0 : $dT;
					
					$total['brutto']				-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
					$total['netto']					-= $dN * $fQ;
					$total['tax'][$taxId]['tax']	-= $tCt == 2 ? 0 : $dT * $fQ;
					
				}
				
				
				
				if ($fullItems[$k]['netto'] < 0 || $fullItems[$k]['netto'] == 0) {
					$fullItems[$k]['brutto'] 	= 0;
					$fullItems[$k]['tax'] 		= 0;
				}
				if ($total['netto']	< 0 || $total['netto'] == 0) {
					$total['brutto'] 				= 0;
					$total['tax'][$taxId]['tax'] 	= 0;
				}
				
			}
			
			$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
			
			if ($this->correctsubtotal) {
				$this->correctSubTotal($fullItems[$k], $total);
			}
		}
	}
	
	// =============
	// CART DISCOUNT
	// =============
	public function calculateCartDiscounts(&$fullItems, &$fullItemsGroup, &$total, &$cartDiscount) {
		
		$price	= new PhocacartPrice();

		foreach($fullItems as $k => $v) {
			
			$groupId 			= $v['id'];
			$taxId				= $v['taxid'];
			$fQ					= $v['quantity'];
			$tCt				= $v['taxtcalctype'];
			
			$discount = PhocacartDiscountCart::getCartDiscount($v['id'], $v['catid'], $total['quantity'], $total['netto']);
			
			$discountId					= $discount['id'];
			$discountAmount				= $discount['discount'];
		
				
			if ($discount['free_shipping'] == 1) {
				$total['free_shipping']		= $discount['free_shipping'];
			}
			if ($discount['free_payment'] == 1) {
				$total['free_payment']		= $discount['free_payment'];
			}
			
			if (isset($discount['discount']) && isset($discount['calculation_type'])) {

				$fullItems[$k]['discountcart'] 			= 1;
				$fullItems[$k]['discountcarttitle'] 	= $discount['title'];
				$fullItems[$k]['discountcartid'] 		= $discount['id'];
				
				if ($discount['calculation_type'] == 0) {
					// ------------
					// FIXED AMOUNT
					// ------------
					
					// We need to divide fixed discount amount to products which meet the discount ID rule
					// There can be two products in the cart and each can meet other discount rules
					if (isset($total['discountcartfixedamount'][$discountId]['quantity'])) {
						$total['discountcartfixedamount'][$discountId]['quantity'] += $fQ;
					} else {
						$total['discountcartfixedamount'][$discountId]['quantity'] = $fQ;
					}
					
					if (isset($total['discountcartfixedamount'][$discountId]['netto'])) {
						$total['discountcartfixedamount'][$discountId]['netto'] += $v['netto'] * $fQ;
					} else {
						$total['discountcartfixedamount'][$discountId]['netto'] = $v['netto'] * $fQ;
					}
					
					$total['discountcartfixedamount'][$discountId]['discount'] 	= $discountAmount;
					$fullItems[$k]['discountcartfixedid'] 						= $discountId;
					
					
				} else {
					// ------------
					// PERCENTAGE
					// ------------
					$dB = $price->roundPrice($v['brutto'] * $discount['discount'] / 100);
					$dN = $price->roundPrice($v['netto'] * $discount['discount'] / 100);
					$dT = $price->roundPrice($v['tax'] * $discount['discount'] / 100);
					
					///$dB = $v['brutto'] * $discount['discount'] / 100;
					///$dN = $v['netto'] * $discount['discount'] / 100;
					///$dT = $v['tax'] * $discount['discount'] / 100;
					
					
					// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
					$fullItems[$k]['brutto'] 		-= $tCt == 2 ? $dN : $dB;
					$fullItems[$k]['netto'] 		-= $dN;
					$fullItems[$k]['tax'] 			-= $tCt == 2 ? 0 : $dT;
					
					$total['brutto']				-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
					$total['netto']					-= $dN * $fQ;
					$total['tax'][$taxId]['tax']	-= $tCt == 2 ? 0 : $dT * $fQ;
					
					$total['discountcarttxtsuffix'] = ' ('.$price->cleanPrice($discount['discount']).' %)';
					
				}
				
				$cartDiscount['id'] 	= $discount['id'];
				$cartDiscount['title']	= $discount['title'];
			
			}
			
			$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
			
			if ($this->correctsubtotal) {
				$this->correctSubTotal($fullItems[$k], $total);
			}
		}
		
	}
	
	/*
	 * Used only for fixed amount of discount
	 */
	public function recalculateCartDiscounts(&$fullItems, &$fullItemsGroup, &$total) {
		
		$price	= new PhocacartPrice();
		
		foreach($fullItems as $k => $v) {
			
			$fQ		= $v['quantity'];
			$taxId	= $v['taxid'];
			$dF 	= $v['discountcartfixedid'];
			$tCt	= $v['taxtcalctype'];
			
			if (isset($total['discountcartfixedamount'][$dF]['discount']) && isset($total['discountcartfixedamount'][$dF]['netto'])) {
				
				$dPRel = $total['discountcartfixedamount'][$dF]['discount'] / $total['discountcartfixedamount'][$dF]['netto'];
				// CART DISCOUNT - DIVIDE IT INTO QUANTITY 
				$dPFix = $dPRel * $v['netto'] * $fQ;
				
				if ($v['netto'] > 0) {
					$r = $dPFix * 100 / $v['netto'] / $fQ;// Ratio to use it for brutto and tax but you need to divide it into qunatity
				} else {
					$r = 0;
				}
				

				$dB = $price->roundPrice($v['brutto'] * $r/100);
				$dN = $price->roundPrice($v['netto'] * $r/100);
				$dT = $price->roundPrice($v['tax'] * $r/100);
				
				///$dB = $v['brutto'] * $r/100;
				///$dN = $v['netto'] * $r/100;
				///$dT = $v['tax'] * $r/100;
				
				// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
				if ($fullItems[$k]['brutto'] < $dB) {
					$fullItems[$k]['brutto'] = 0;
					$total['brutto']		-=  $tCt == 2 ? $dN * $fQ : $v['brutto'] * $fQ;
				} else {
					$fullItems[$k]['brutto'] 	-= $tCt == 2 ? $dN : $dB;
					$total['brutto']			-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
				}
				
				if ($fullItems[$k]['netto'] < $dN) {
					$fullItems[$k]['netto'] = 0;
					$total['netto']			-= $v['netto'] * $fQ;
				} else {
					$fullItems[$k]['netto'] 	-= $dN;
					$total['netto']				-= $dN * $fQ;
					
				}
				
				if ($fullItems[$k]['tax'] < $dT) {
					$fullItems[$k]['tax'] = 0;
					$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $v['tax'] * $fQ;
				} else {
					$fullItems[$k]['tax'] 		-= $tCt == 2 ? 0 : $dT;
					$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $dT * $fQ;
				}	
				
				
				if ($fullItems[$k]['netto'] < 0 || $fullItems[$k]['netto'] == 0) {
					$fullItems[$k]['brutto'] 	= 0;
					$fullItems[$k]['tax'] 		= 0;
				}
				if ($total['netto']	< 0 || $total['netto'] == 0) {
					$total['brutto'] 				= 0;
					$total['tax'][$taxId]['tax'] 	= 0;
				}
			}
			
			$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
			
			if ($this->correctsubtotal) {
				//$this->correctSubTotal($fullItems[$k], $total);
			}
		}
	}
	
	// ============
	// CART COUPON
	// ============
	/*
	 * $coupon ... coupon ID and Title set in checkout
	 * $couponO ... coupon object
	 * $couponDb ... all the information from coupon
	 * $validCoupon ... is or isn't valid TRUE/FALSE
	 */
	public function calculateCartCoupons(&$fullItems, &$fullItemsGroup, &$total, &$coupon) {
		
		$couponO 	= new PhocacartCoupon();
		$couponO->setCoupon($coupon['id']);
		$couponDb	= $couponO->getCoupon();
		$price		= new PhocacartPrice();
		
		foreach($fullItems as $k => $v) {
			
			$groupId 			= $v['id'];
			$taxId				= $v['taxid'];
			$fQ					= $v['quantity'];
			$tCt				= $v['taxtcalctype'];

			$validCoupon = $couponO->checkCoupon(0, $v['id'], $v['catid'], $total['quantity'], $total['netto']);
			
			if ($validCoupon) {
				$validCouponId					= $couponDb['id'];
				$validCouponAmount				= $couponDb['discount'];
				
				if ($couponDb['free_shipping'] == 1) {
					$total['free_shipping']		= $couponDb['free_shipping'];
				}
				if ($couponDb['free_payment'] == 1) {
					$total['free_payment']		= $couponDb['free_payment'];
				}
				
				if (isset($couponDb['discount']) && isset($couponDb['calculation_type'])) {
					
					$fullItems[$k]['couponcart'] 		= 1;
					$fullItems[$k]['couponcarttitle'] 	= $couponDb['title'];
					$fullItems[$k]['couponcartid'] 		= $couponDb['id'];
					
					
					if ($couponDb['calculation_type'] == 0) {
						// ------------
						// FIXED AMOUNT
						// ------------
						
						// We need to divide fixed couponDb amount to products which meet the couponDb ID rule
						// There can be two products in the cart and each can meet other couponDb rules
						if (isset($total['couponcartfixedamount'][$validCouponId]['quantity'])) {
							$total['couponcartfixedamount'][$validCouponId]['quantity'] += $fQ;
						} else {
							$total['couponcartfixedamount'][$validCouponId]['quantity'] = $fQ;
						}
						
						if (isset($total['couponcartfixedamount'][$validCouponId]['netto'])) {
							$total['couponcartfixedamount'][$validCouponId]['netto'] += $v['netto'] * $fQ;
						} else {
							$total['couponcartfixedamount'][$validCouponId]['netto'] = $v['netto'] * $fQ;
						}
						
						$total['couponcartfixedamount'][$validCouponId]['discount'] = $validCouponAmount;
						$fullItems[$k]['couponcartfixedid'] 						= $validCouponId;
						
						
					} else {
						// ------------
						// PERCENTAGE
						// ------------
						$dB = $price->roundPrice($v['brutto'] * $couponDb['discount'] / 100);
						$dN = $price->roundPrice($v['netto'] * $couponDb['discount'] / 100);
						$dT = $price->roundPrice($v['tax'] * $couponDb['discount'] / 100);
						
						///$dB = $v['brutto'] * $couponDb['discount'] / 100;
						///$dN = $v['netto'] * $couponDb['discount'] / 100;
						///$dT = $v['tax'] * $couponDb['discount'] / 100;
						
						// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
						$fullItems[$k]['brutto'] 		-= $tCt == 2 ? $dN : $dB;
						$fullItems[$k]['netto'] 		-= $dN;
						$fullItems[$k]['tax'] 			-= $tCt == 2 ? 0 : $dT;
						
						$total['brutto']				-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
						$total['netto']					-= $dN * $fQ;
						$total['tax'][$taxId]['tax']	-= $tCt == 2 ? 0 : $dT * $fQ;
						
						$total['couponcarttxtsuffix'] = ' ('.$price->cleanPrice($couponDb['discount']).' %)';
					}
				}
			}
			
			$coupon['valid'] 		= $validCoupon;
			$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
			
			if ($this->correctsubtotal) {
				$this->correctSubTotal($fullItems[$k], $total);
			}
		}
	}
	
	/*
	 * Used only for fixed amount of coupon
	 */
	public function recalculateCartCoupons(&$fullItems, &$fullItemsGroup, &$total) {
		
		$price	= new PhocacartPrice();
		
		foreach($fullItems as $k => $v) {
			
			$fQ		= $v['quantity'];
			$taxId	= $v['taxid'];
			$dF 	= $v['couponcartfixedid'];
			$tCt	= $v['taxtcalctype'];
			
			if (isset($total['couponcartfixedamount'][$dF]['discount']) && isset($total['couponcartfixedamount'][$dF]['netto'])) {
				
				if ($total['couponcartfixedamount'][$dF]['netto'] > 0) {
					$dPRel = $total['couponcartfixedamount'][$dF]['discount'] / $total['couponcartfixedamount'][$dF]['netto'];
				} else {
					$dPRel = 0;
				}
				// CART DISCOUNT - DIVIDE IT INTO QUANTITY 
				$dPFix = $dPRel * $v['netto'] * $fQ;
				
				if ($v['netto'] > 0) {
					$r = $dPFix * 100 / $v['netto'] / $fQ;// Ratio to use it for brutto and tax but you need to divide it into qunatity
				} else {
					$r = 0;
				}
				

				$dB = $price->roundPrice($v['brutto'] * $r/100);
				$dN = $price->roundPrice($v['netto'] * $r/100);
				$dT = $price->roundPrice($v['tax'] * $r/100);
				
				///$dB = $v['brutto'] * $r/100;
				///$dN = $v['netto'] * $r/100;
				///$dT = $$v['tax'] * $r/100;
				
	
				// If fixed VAT ($tCt == 2) then we cannot reduce the VAT, so we cannot reduce BRUTTO
				if ($fullItems[$k]['brutto'] < $dB) {
					$fullItems[$k]['brutto'] = 0;
					$total['brutto']		-=  $tCt == 2 ? $dN * $fQ : $v['brutto'] * $fQ;
				} else {
					$fullItems[$k]['brutto'] 	-= $tCt == 2 ? $dN : $dB;
					$total['brutto']			-= $tCt == 2 ? $dN * $fQ : $dB * $fQ;
				}
				
				if ($fullItems[$k]['netto'] < $dN) {
					$fullItems[$k]['netto'] = 0;
					$total['netto']			-= $v['netto'] * $fQ;
				} else {
					$fullItems[$k]['netto'] 	-= $dN;
					$total['netto']				-= $dN * $fQ;
					
				}
				
				if ($fullItems[$k]['tax'] < $dT) {
					$fullItems[$k]['tax'] = 0;
					$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $v['tax'] * $fQ;
				} else {
					$fullItems[$k]['tax'] 		-= $tCt == 2 ? 0 : $dT;
					$total['tax'][$taxId]['tax']-= $tCt == 2 ? 0 : $dT * $fQ;
				}

				
				
				
				if ($fullItems[$k]['netto'] < 0 || $fullItems[$k]['netto'] == 0) {
					$fullItems[$k]['brutto'] 	= 0;
					$fullItems[$k]['tax'] 		= 0;
				}
				if ($total['netto']	< 0 || $total['netto'] == 0) {
					$total['brutto'] 				= 0;
					$total['tax'][$taxId]['tax'] 	= 0;
				}
			}
			
			
			
			$fullItems[$k]['final']	= $fullItems[$k]['netto'] ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;
			
			
			if ($this->correctsubtotal) {
				$this->correctSubTotal($fullItems[$k], $total);
			}
		
		}
	}
	
	
	// Correct rounding errors
	// When we count the tax and quantity, we need to do without rounding because of VAT
	// This is why we run $price->getPriceItems without rounding Output
	// Ok, now rounding, this means, we can get difference e.g. 0.01 so such we need to correct
	// - in total, even on the row
	// This can happen when using Exclusive Tax - this is common problem with exclusive tax counting
	
	public function correctSubTotal(&$item, &$total) {
	
		// Fixed VAT
		if($item['taxtcalctype'] == 2) {
			//return;
		}
		$price				= new PhocacartPrice();
		$quantityCorrect	= $item['quantity'] > 0 ? $item['quantity'] : 1;
		$nettoNotRounded	= $item['netto'] * $quantityCorrect;
		$taxNotRounded		= $item['tax'] * $quantityCorrect;
		
		$nettoRounded 	= $price->roundPrice($item['netto'] * $quantityCorrect);
		$bruttoRounded 	= $price->roundPrice($item['brutto'] * $quantityCorrect);
		$taxRounded 		= $price->roundPrice($item['tax'] * $quantityCorrect);
		
		///$nettoRounded 		= $item['netto'] * $quantityCorrect;
		///$bruttoRounded 		= $item['brutto'] * $quantityCorrect;
		///$taxRounded 		= $item['tax'] * $quantityCorrect;
		
		$nettoRoundedCorrected = $bruttoRounded - $taxRounded;
		
		if ($nettoNotRounded > 0) {
			if (abs(($nettoRoundedCorrected - $nettoNotRounded)/$nettoNotRounded) < 0.00001) {	
				// the floats are the same
			} else {
				
				$item['netto'] 	= $nettoRoundedCorrected / $quantityCorrect;
				$total['netto'] = $total['netto'] + ($nettoRoundedCorrected / $item['quantity']) - ($nettoNotRounded / $quantityCorrect);
				
				if (!empty($total['tax'])) {
					foreach($total['tax'] as $kT => $vT) {
						if ($kT == $item['taxid']) {
							$total['tax'][$kT]['tax'] = $vT['tax'] + ($taxRounded / $quantityCorrect) - ($taxNotRounded / $quantityCorrect);
						}	
					}	
				}		
			}
		}
	}
	/*
	public function correctSubTotalAll(&$fullItems, &$total) {
	
		foreach($fullItems as $k => $v) {
			$price				= new PhocacartPrice();
			$quantityCorrect	= $v['quantity'] > 0 ? $v['quantity'] : 1;
			$nettoNotRounded	= $v['netto'] * $quantityCorrect;
			$taxNotRounded		= $v['tax'] * $quantityCorrect;
			$nettoRounded 		= $price->roundPrice($v['netto'] * $quantityCorrect);
			$bruttoRounded 		= $price->roundPrice($v['brutto'] * $quantityCorrect);
			$taxRounded 		= $price->roundPrice($v['tax'] * $quantityCorrect);
			
			$nettoRoundedCorrected = $bruttoRounded - $taxRounded;
			
			if ($nettoNotRounded > 0) {
				if (abs(($nettoRoundedCorrected - $nettoNotRounded)/$nettoNotRounded) < 0.00001) {	
					// the floats are the same
				} else {
					
					$fullItems[$k]['netto'] = $nettoRoundedCorrected / $quantityCorrect;
					$total['netto'] = $total['netto'] + ($nettoRoundedCorrected / $v['quantity']) - ($nettoNotRounded / $quantityCorrect);
					
					if (!empty($total['tax'])) {
						foreach($total['tax'] as $kT => $vT) {
							if ($kT == $v['taxid']) {
								$total['tax'][$kT]['tax'] = $vT['tax'] + ($taxRounded / $quantityCorrect) - ($taxNotRounded / $quantityCorrect);
							}	
						}	
					}		
				}
			}
		}
	}*/
	
	
	public function roundFixedAmountDiscount(&$total) {
		
		
		$app							= JFactory::getApplication();
		$paramsC 						= PhocacartUtils::getComponentParameters();
		$rounding_calculation_fad		= $paramsC->get( 'rounding_calculation_fixed_amount_discount', -1 );
		
		if ($rounding_calculation_fad < 0) {
			return;
		}
		
		$discount = 0;
		if (!empty($total['discountcartfixedamount'])) {
			foreach($total['discountcartfixedamount'] as $k => $v) {
				if (isset($v['discount'])) {
					$discount = $v['discount'];
				}
			} 
		}
		
		
		if (isset($total['dnetto']) && $total['dnetto'] > 0 && $discount > 0) {
			$dif = $discount - $total['dnetto'];
			
			if ($dif > 0) {
				$total['rounding']	+= $dif;
				$total['dnetto'] = $discount;
			} else if ($dif < 0) {
				$total['rounding']	+= $dif;
				$total['dnetto'] = $discount;
			}
			
		}
		return;
	}
	
	public function roundFixedAmountCoupon(&$total) {
		
		$app							= JFactory::getApplication();
		$paramsC 						= PhocacartUtils::getComponentParameters();
		$rounding_calculation_fac		= $paramsC->get( 'rounding_calculation_fixed_amount_coupon', -1 );
		
		if ($rounding_calculation_fac < 0) {
			return;
		}
		
		$discount = 0;
		if (!empty($total['couponcartfixedamount'])) {
			foreach($total['couponcartfixedamount'] as $k => $v) {
				if (isset($v['discount'])) {
					$discount = $v['discount'];
				}
			} 
		}
		
		if (isset($total['dnetto']) && $total['dnetto'] > 0 && $discount > 0) {
			$dif = $discount - $total['dnetto'];
			
			if ($dif > 0) {
				$total['rounding']	+= $dif;
				$total['dnetto'] = $discount;
			} else if ($dif < 0) {
				$total['rounding']	+= $dif;
				$total['dnetto'] = $discount;
			}
			
		}
		return;
	}
	
	
	public function roundTotalAmount(&$total, $bruttoReal, $bruttoCurrency) {
		
		$price 							= new PhocacartPrice();
		$app							= JFactory::getApplication();
		$paramsC 						= PhocacartUtils::getComponentParameters();
		$rounding_calculation			= $paramsC->get( 'rounding_calculation', 1 );
		$rounding_calculation_total		= $paramsC->get( 'rounding_calculation_total', 2 );
		$currencyRate 					= PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();
		
		if (!isset($total['brutto'])) {
			return false;
		}
		
		// Brutto and Rounding in order currency
		$total['rounding_currency']		= 0;
		$total['brutto_currency']		= 0;
		
		// ------------------------
		// 1) NO ROUNDING - CORRECTION ONLY
		// ------------------------
		// 1a) CORRECT BRUTTO - DEFAULT CURRENCY
		if ($total['brutto'] != $bruttoReal) {
			$total['brutto'] = $price->roundPrice($bruttoReal);
		}
		
		// 1b) CORRECT BRUTTO - ORDER CURRENCY
		if ($rounding_calculation_total == -1 && $currencyRate > 0) {
			$totalBruttoCurrency 		= $price->roundPrice($total['brutto'] * $currencyRate);
			if ($totalBruttoCurrency != $bruttoCurrency) {
				$total['rounding_currency']	+= ($totalBruttoCurrency - $bruttoCurrency);
			}
			$total['brutto_currency'] 	= $totalBruttoCurrency;
		}
		
		if ($rounding_calculation_total == -1) {
			return;
		}
		
		// ------------------------
		// 2) ROUNDING
		// ------------------------
		// !Important
		// Each currency has own total rounding and brutto
		if ($rounding_calculation_total > - 1) {
			
			// 2a) ROUNDING ORDER CURRENCY
			if ($currencyRate > 0) {
				
				$totalBruttoCurrency 		= $total['brutto'] * $currencyRate;
				$totalBruttoCurrencyRound	= round($totalBruttoCurrency , (int)$rounding_calculation_total, $rounding_calculation);
				$bruttoCurrency 			= round($bruttoCurrency, 2, $rounding_calculation);
				if ($totalBruttoCurrency != $bruttoCurrency) {
					$total['rounding_currency']	+= ($totalBruttoCurrencyRound - $bruttoCurrency);
				}
				$total['brutto_currency'] 	= $price->roundPrice($totalBruttoCurrencyRound);

			} else {

				// 2b) ROUNDING DEFAULT CURRENCY
				$brutto = round($total['brutto'], (int)$rounding_calculation_total, $rounding_calculation);
				if ($brutto != $total['brutto']) {
					$total['rounding']		+= ($brutto - $total['brutto']);
				}
				$total['brutto']			= $price->roundPrice($brutto);
				$total['brutto_currency'] 	= 0;
				$total['rounding_currency']	= 0;
				
			}	
		}
		
		
		
		// Wrong subtraction of floats
		// We compare $d['total'][0]['rounding'] != 0 so 100 -100 = 1.4210854715202E-14, round fixed it 
		//$total['rounding'] = round($total['rounding'], 2, $rounding_calculation);

	}
	/*
	public function resetVariables(&$fullItems, $levels = array()) {
		if (!empty($levels)) {
			foreach($levels as $k => $v) {
				if (!empty($fullItems[$k])) {
					foreach($fullItems[$k] as $k => $v) {
						$fullItems[$k]['discountproduct'] 		= 0;
						$fullItems[$k]['discountcartfixedid'] 	= 0;
						$fullItems[$k]['discounttitle'] 		= '';
					}
				}
			}
		}
	}*/
	
	
	public function calculateShipping($priceI, &$total) {
	
		if (!isset($total['brutto'])) {
			return false;
		}
		if (isset($priceI['brutto']) && $priceI['brutto'] >  0) {
			$total['brutto'] += $priceI['brutto'];
		} else if (isset($priceI['netto']) && $priceI['netto'] >  0 && isset($priceI['tax']) && $priceI['tax'] >  0) {
			$total['brutto'] += ($priceI['netto'] + $priceI['tax']);
		}
	}
	
	public function calculatePayment($priceI, &$total) {
	
		if (!isset($total['brutto'])) {
			return false;
		}
		
		if (isset($priceI['brutto']) && $priceI['brutto'] >  0) {
			$total['brutto'] += $priceI['brutto'];
		} else if (isset($priceI['netto']) && $priceI['netto'] >  0 && isset($priceI['tax']) && $priceI['tax'] >  0) {
			$total['brutto'] += ($priceI['netto'] + $priceI['tax']);
		}
	}
}
?>