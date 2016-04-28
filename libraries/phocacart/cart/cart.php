<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
phocacartimport('phocacart.user.user');
phocacartimport('phocacart.user.guestuser');
phocacartimport('phocacart.shipping.shipping');
phocacartimport('phocacart.payment.payment');
class PhocaCartCart
{
	protected $items     			= array();
	protected $fullitems			= array();
	protected $fullitemsgroup		= array();// Group of one product: Product A (Option A) + Product A (Option B)
	protected $user					= array();
	protected $total				= array();
	protected $shippingid			= 0;
	protected $shippingcosts		= 0;
	protected $paymentid			= 0;
	protected $paymenttitle			= '';
	protected $paymentmethod		= '';
	protected $paymentcosts			= 0;
	protected $couponid				= 0;
	protected $coupontitle			= '';
	protected $couponamount			= 0;
	protected $couponfreeshipping 	= 0;// Does the copoun cause free shipping
	protected $couponfreepayment 	= 0;// Does the copoun cause free payment
	protected $couponvalid 			= 0;// is the coupon valid - all three tests - basic, advanced, total
	protected $stockvalid			= 1;// check stock - products, attributes (no matter if stock checking is disabled or enabled)
	protected $minqtyvalid			= 1;// check minimum order quantity

	public function __construct() {
		$session 		= JFactory::getSession();
		$this->user		= JFactory::getUser();
		$app 			= JFactory::getApplication();
		$guest			= PhocaCartGuestUser::getGuestUser();
		
		//Define
		$this->total['netto']			= 0;
		$this->total['brutto']			= 0;
		$this->total['tax']				= array();
		$this->total['weight']			= 0;
		$this->total['volume']			= 0;
		$this->total['cnetto']			= 0;// Coupon Netto
		$this->total['cbrutto']			= 0;// Coupon Brutto
		$this->total['ctax']			= array();// Coupon Tax
		$this->total['coupontaxcount']	= 0;//Coupon statistics - if there is a fixed amount coupon, we need to count all products
											// and make a ration of the fixed amount to correctly divide the tax
											// e.g. 1 product has 10% VAT, second 20% - we need to divide the coupon right between products
		
		// Admin info
		if ($app->getName() == 'administrator') {
			$id				= $app->input->get('id', 0, 'int');
			$cartDb 		= PhocaCartCartDb::getCartDb($id);
			$this->items	= $cartDb['cart'];
		}
		
		if((int)$this->user->id > 0) {
			// DATABASE - logged in user - Singleton because of not load data from database every time cart instance is loaded
			// 1. Not found in DATABASE - maybe user logged in now, so:
			// 2. We try to find the data in SESSION, if they are still in SESSION - load them to our cart class and then
			// 3. Store them to DATABASE as all loged in users have cart in database and:
			// 4. Remove them from SESSION as they are stored in DATABASE
			$cartDb = PhocaCartCartDb::getCartDb($this->user->id);// user logged in - try to get cart from db
			$this->items 		= $cartDb['cart'];
			$this->shippingid	= $cartDb['shipping'];
			$this->paymentid	= $cartDb['payment'];
			$this->couponid		= $cartDb['coupon'];
			$this->paymenttitle	= $cartDb['paymenttitle'];
			$this->paymentmethod= $cartDb['paymentmethod'];
			$this->coupontitle	= $cartDb['coupontitle'];
		
			if(empty($this->items)) {
				$this->items	= $session->get('cart', array(), 'phocaCart');
				if(!empty($this->items)) {
					$this->updateItemsDb();
					$session->set('cart', array(), 'phocaCart');
				}
			}
		} else if ($guest) {
			$this->items 		= $session->get('cart', array(), 'phocaCart');
			$this->shippingid	= $session->get('guestshipping', false, 'phocaCart');
			$this->paymentid	= $session->get('guestpayment', false, 'phocaCart');
			$this->couponid		= $session->get('guestcoupon', false, 'phocaCart');
			//$this->paymenttitle	= $session->get('guestpaymenttitle', false, 'phocaCart');// will be discovered by ID
			//$this->coupontitle	= $session->get('guestcoupontitle', false, 'phocaCart');// will be discovered by ID
			
		} else {
			// SESSION - not logged in user
			$this->items	= $session->get('cart', array(), 'phocaCart');
			
		}
		
	}
	
	/*
	 * Catid is only for information from which place the product was added to the cart
	 * When making order, this will be recheck
	 */
	
	public function addItems($id = 0, $catid = 0, $quantity = 0, $attributes = array(), $idKey = '') {
		
		if ($idKey != '') {
			// we get idkey as string - from checkout update or remove -  used in CHECKOUT
		} else {
			// we get id as int standard id of product - attributes can be listed in form - used in CATEGORY, ITEM, ITEMS
			$k = (int)$id . ':';
			
			$checkP = PhocaCartProduct::checkIfAccessPossible($id, $catid);
			if (!$checkP) {	
				$uri 			= JFactory::getURI();
				$action			= $uri->toString();
				$app			= JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_NO_RIGHTS_FOR_ORDERING_PRODUCT'), 'error');
				$app->redirect($action);
				exit;
			}

			if (!empty($attributes)) {
				
				
				// Remove empty values, so items with empty values (add to cart item view) is the same
				// like item without any values (add to cart category view)
				foreach($attributes as $k2 => $v2) {
					
					// Check if there is required attribute
					// In fact this should not happen, as this should be checked per html5 checking form
					// This is only security check on server side
					// This is why we don't care about where the site goes redirect, as normal user will not get it
					$checkA = PhocaCartAttribute::checkIfRequired($k2, $v2);
					if (!$checkA) {
						
						$uri 			= JFactory::getURI();
						$action			= $uri->toString();
						$app			= JFactory::getApplication();
						$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_SELECTING_ATTRIBUTE_IS_REQUIRED'), 'error');
						$app->redirect($action);
						exit;
					}
					
					if ($v2 == 0 || $v2 == '') {
						unset($attributes[$k2]);
					}
				}
				if (!empty($attributes)) {
					$k .= base64_encode(serialize($attributes));
				}
			} else {
				$checkA = PhocaCartAttribute::checkIfExistsAndRequired($id);
				if (!$checkA) {
					$uri 			= JFactory::getURI();
					$action			= $uri->toString();
					$app			= JFactory::getApplication();
					$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_SELECTING_ATTRIBUTE_IS_REQUIRED'), 'error');
					$app->redirect($action);
					exit;
				}
			
			}
			$k .= ':';
		}
		
		if ($k != '' && (int)$catid > 0) {
			$this->items[$k]['catid'] = (int)$catid;
		}
		
		if ($k != '' && (int)$quantity > 0) {
			if(isset($this->items[$k]['quantity']) && (int)$this->items[$k]['quantity'] > 0) {
				
				$this->items[$k]['quantity'] = $this->items[$k]['quantity'] + (int)$quantity;
			} else {
				$this->items[$k]['quantity'] = (int)$quantity;
			}
			$this->updateItems();
			//$this->updateSubTotal();
			return true;
		}
		return false;
	}
	
	/*
	 * UPDATE - public function to update from CHECKOUT (update, remove buttons)
	 */
	 public function updateItemsFromCheckout($idKey = '', $quantity = 0) {
		// Don't check for quantity as it can be NULL
		
		if ($idKey != '' && (int)$quantity > 0 ) {
			$this->items[$idKey]['quantity'] = (int)$quantity;
			$this->updateItems();
			return true;
		} else if ($idKey != '' && (int)$quantity == 0) {
			unset($this->items[$idKey]);
			$this->updateItems();
			return true;
		}
	 
	 }
	
	
	/*
	 * UPDATE - protected internal function to update session or database
	 */
	protected function updateItems() {
	
		$session 		= JFactory::getSession();
		if((int)$this->user->id > 0) {
			$this->updateItemsDb();
			// if user changes the cart shipping method needs to be removed because it can be based on amount or region, etc.
			// payment is for now without dependency to address or to amount, don't update it
			PhocaCartShipping::removeShipping();
			PhocaCartPayment::removePayment();
			$session->set('cart', array(), 'phocaCart');
		} else {
			$session->set('cart', $this->items, 'phocaCart');
		}
	}
	
	/*
	 * UPDATE - protected internal function to update only database
	 */
	protected function updateItemsDb() {
		$db 	= JFactory::getDBO();
		$items	= serialize($this->items);
		$date 	= JFactory::getDate();
		$now	= $date->toSql();
		
		$query = 'INSERT INTO #__phocacart_cart (user_id, cart, date)'
				.' VALUES ('.(int)$this->user->id.', '.$db->quote($items).', '.$db->quote($now).')'
				.' ON DUPLICATE KEY UPDATE cart = VALUES (cart), date = VALUES (date)';
				
		$db->setQuery($query);
		$db->execute();
		return true;
	}
	/*
	public function getItems() {
	
		return $this->items;
	}
	
	public function updateSubTotal() {
	
	}*/
	
	/*
	 * Set full items - items in cart, total(netto, tax, brutto), coupon,
	 * We need to set it first, to count e.g. coupon
	 * After counting all possible factors (items, count of items, netto, taxes, brutto, coupon, shipping)
	 * we can render the cart (for example, free shipping by coupon needs to be set at the end as we check total netto amount)
	 */
	
	public function setFullItems() {
		if (empty($this->fullitems)) {
			if(!empty($this->items)) {
			
				$paramsC 			= JComponentHelper::getParams('com_phocacart');
				$tax_calculation	= $paramsC->get( 'tax_calculation', 0 );
				
				
				
				$price	= new PhocaCartPrice();
				// COUPON - basic check
				$couponI 	= array();
				$couponVB	= 0;// check basic
				$couponVA	= 0;// check advanced
				$couponVAT	= 0;// one product can be checked advanced, second not but if one of all is checked advanced
								// we need to manage it in total - so in total we accept when only e.g. one of all is checked advnaced
				$couponVT	= 0;// check total
				if (isset($this->couponid) && (int)$this->couponid > 0) {
					
					// couponI ... couponItems, couponVB ... couponValid basic check
					// Basic check: valid from, valid to, available qty, available user qty
					// Advanced check: product id, category id, total amount
					// Total amount is checked at the end, befor it, we need to test others
					$coupon 	= new PhocaCartCoupon();
					$coupon->setCoupon($this->couponid);
					$couponI 	= $coupon->getCoupon();        
					$couponVB 	= $coupon->checkCouponBasic(); 
					
					if ($couponVB && isset($couponI['free_shipping']) && $couponI['free_shipping'] == 1 ) {
						$this->couponfreeshipping  = 1;
					}
				}										 	   
				
				
				
				
				$countAllCouponItems = 0; // Count all items which can get coupon discount
				foreach($this->items as $k => $v) {
					
					$item 	= explode(':', $k);
					$itemId = $item[0];
					
					// Define
					$this->fullitems[$k]['id'] 			= (int)$itemId;
					$this->fullitems[$k]['idkey'] 		= (string)$k;
					$this->fullitems[$k]['netto'] 		= 0;
					$this->fullitems[$k]['brutto'] 		= 0;
					$this->fullitems[$k]['tax'] 		= 0;
					$this->fullitems[$k]['dnetto'] 		= 0;// Netto after discount
					$this->fullitems[$k]['dbrutto'] 	= 0;// Brutto after discount
					$this->fullitems[$k]['dtax'] 		= 0;// Tax after discount
					$this->fullitems[$k]['taxid'] 		= 0;
					$this->fullitems[$k]['taxtitle']	= '';
					$this->fullitems[$k]['weight']		= '';
					$this->fullitems[$k]['volume']		= '';
					$this->fullitems[$k]['quantity'] 	= (int)$v['quantity'];
					$fQ									= $this->fullitems[$k]['quantity'];
					
					$this->fullitems[$k]['dtype']		= '';// coupon - discount type
					$this->fullitems[$k]['damount']		= '';// coupon - discount amount, e.g. 10% or 20%
					$this->fullitems[$k]['discountapply']= 0;
					$this->fullitems[$k]['catid'] 		= 0;
					$this->fullitems[$k]['alias'] 		= '';
					$this->fullitems[$k]['sku'] 		= '';
					$this->fullitems[$k]['image'] 		= '';
					$this->fullitems[$k]['title'] 		= '';
					$this->fullitems[$k]['stock'] 		= 0; // database value set in product settings
					$this->fullitems[$k]['stockvalid'] 	= 1; // variable to inform if stock validity is ok
					$this->fullitems[$k]['minqty'] 		= 0; // database value set in product settings
					$this->fullitems[$k]['minqtyvalid'] = 1; // varible to inform if minimum order is ok
					
					// Group
					$this->fullitemsgroup[$itemId]['id']			= (int)$itemId;
					$this->fullitemsgroup[$itemId]['title']			= '';
					
											
					
					$attribs = array();
					if (!empty($item[1])) {
						$attribs = unserialize(base64_decode($item[1]));
					}
					
					$item2 = PhocaCartProduct::getProduct((int)$itemId, (int)$v['catid']);
					
					// Correct the tax rate - no tax calculation, no tax rate for each product
					if (!empty($item2) && $tax_calculation == 0) {
						$item2->taxrate = 0;
					}
					
					
					if (isset($item2->id) && (int)$item2->id > 0) {
						$this->fullitems[$k]['title'] 		= $item2->title;
						$this->fullitems[$k]['catid']		= $item2->catid;
						$this->fullitems[$k]['alias'] 		= $item2->alias;
						$this->fullitems[$k]['sku'] 		= $item2->sku;
						$this->fullitems[$k]['image'] 		= $item2->image;
						$priceI = $price->getPriceItems($item2->price, $item2->taxrate, $item2->taxcalctype, $item2->taxtitle);
						$this->fullitems[$k]['netto'] 		= $priceI['netto'];
						$this->fullitems[$k]['brutto'] 		= $priceI['brutto'];
						
						$this->fullitems[$k]['tax'] 		= $priceI['tax'];
						$this->fullitems[$k]['price'] 		= $item2->price;
						$this->fullitems[$k]['taxid'] 		= $item2->taxid;
						$this->fullitems[$k]['taxrate'] 	= $item2->taxrate;
						$this->fullitems[$k]['taxtitle'] 	= $item2->taxtitle;
						$this->fullitems[$k]['taxtcalctype']= $item2->taxcalctype;
						$this->fullitems[$k]['weight']		= $item2->weight;
						$this->fullitems[$k]['volume']		= $item2->volume;
						$this->fullitems[$k]['stock'] 		= $item2->stock;
						$this->fullitems[$k]['minqty'] 		= $item2->min_quantity;
						
						// Group
						$this->fullitemsgroup[$itemId]['minqty']	= $item2->min_quantity;
						$this->fullitemsgroup[$itemId]['title']		= $item2->title;
						
						//$this->fullitems[$k]['netto'] 		*= $fQ;
						$this->total['netto']				+= ($this->fullitems[$k]['netto'] * $fQ);
						//$this->total['netto']				*= $fQ;
					
						//$this->fullitems[$k]['brutto'] 		*= $fQ;
						$this->total['brutto']				+= ($this->fullitems[$k]['brutto'] * $fQ);
						//$this->total['brutto']				*= $fQ;
						//$this->fullitems[$k]['weight']		*= $fQ;
						$this->total['weight']				+= ($this->fullitems[$k]['weight'] * $fQ);
						//$this->total['weight']				*= $fQ;
						$this->total['volume']				+= ($this->fullitems[$k]['volume'] * $fQ);
						
						if (!isset($this->total['tax'][$item2->taxid]['tax'])) {
							$this->total['tax'][$item2->taxid]['tax'] 	= 0;// Define
						}
						
						// COUPON - FIXED AMOUNT STATISTIS
						if (!isset($this->total['tax'][$item2->taxid]['coupontaxcount'])) {
							$this->total['tax'][$item2->taxid]['coupontaxcount'] = 0;// Define
						}
						
						//$this->fullitems[$k]['tax'] *= $fQ;
						$this->total['tax'][$item2->taxid]['tax']	+= ($this->fullitems[$k]['tax'] *$fQ);
						
						
						$taxSuffix = '';
						if ($item2->taxcalctype == 1) {
							$taxSuffix = ' ('.$item2->taxrate.'%)';
						}
						$this->total['tax'][$item2->taxid]['title']	= $item2->taxtitle . $taxSuffix ;
						$this->total['tax'][$item2->taxid]['type']	= $item2->taxcalctype;
						$this->total['tax'][$item2->taxid]['rate']	= $item2->taxrate;
						
						
						
						// COUPON advanced check
						$couponVA = false;
						if (!empty($coupon)) {
							$couponVA = $coupon->checkCouponAdvanced($item2->id, $item2->catid);
							
							if ($couponVA) {
								$couponVAT = true;
							}
						}
						
						if ($couponVB && $couponVA) {
						
							// COUPON FIXED amount needs to be divided to products do a statisctis
							$this->total['coupontaxcount'] += ($this->fullitems[$k]['quantity']);// Count of all items
							$this->total['tax'][$item2->taxid]['coupontaxcount'] += $this->fullitems[$k]['quantity'];
							$countAllCouponItems				+= $this->fullitems[$k]['quantity'];
							$this->fullitems[$k]['discountapply'] = 1;
						
							// COUPON CALCULATION - 1. PERCENTAGE
							if ($couponI['calculation_type'] == 1 && $couponI['discount'] > 0) {
								$cD = $couponI['discount'];
								if (!isset($this->total['ctax'][$item2->taxid]['tax'])) {
									$this->total['ctax'][$item2->taxid]['tax'] 	= 0;
								}
								
								$cDB = $this->fullitems[$k]['brutto'] * $cD / 100; // Coupon discount
								$cDN = $this->fullitems[$k]['netto'] * $cD / 100;
								$cDT = $this->fullitems[$k]['tax'] * $cD / 100;
								
								//$this->total['netto']	 += $cDN;// we are displaying standard netto without coupon
								//$this->total['brutto']	 += $cDB;// the same with brutto
								
								//$this->total['tax'][$item2->taxid]['tax'] += $cDT;// but the tax is exctracted but we will display it in output
								
								
								$this->fullitems[$k]['dbrutto'] = $this->fullitems[$k]['brutto'] - $cDB;// Amount after discount
								$this->fullitems[$k]['dnetto'] 	= $this->fullitems[$k]['netto'] - $cDN;
								$this->fullitems[$k]['dtax'] 	= $this->fullitems[$k]['tax'] - $cDT;
								
								$this->total['cbrutto']	+= ($cDB * $fQ);// Total after discount
								$this->total['cnetto']	+= ($cDN * $fQ);
								
								
								$this->total['ctax'][$item2->taxid]['tax'] += ($cDT * $fQ);
								
								/*$this->total['cbrutto']	*= $fQ;// Count with quantity
								$this->total['cnetto']	*= $fQ;
								$this->total['ctax'][$item2->taxid]['tax'] *= $fQ;*/
								
								$this->fullitems[$k]['dtype']		= $couponI['calculation_type'];
								$this->fullitems[$k]['damount']		= $couponI['discount'];
							}
						}
					}
					
					// ATTRIBUTES
					if (!empty($attribs)) {
						foreach ($attribs as $k2 => $v2) {
							if ((int)$k2 > 0 && (int)$v2 > 0) {
								
								$attrib = PhocaCartAttribute::getAttributeValue((int)$v2, (int)$k2);
								
								if (isset($attrib->title) && isset($attrib->amount) && isset($attrib->operator)) {
									$priceA = $price->getPriceItems($attrib->amount, $item2->taxrate, $item2->taxcalctype, $item2->taxtitle);
									$fQ 	= (int)$this->fullitems[$k]['quantity'];
									// Price
									if ($attrib->operator == '-') {
										$this->fullitems[$k]['netto'] 	-= $priceA['netto'];
										if ($this->fullitems[$k]['netto'] < 0) {
											$this->fullitems[$k]['netto'] = 0;
										} else {
											$this->total['netto']			-= ($priceA['netto'] * $fQ);
										}
										
										$this->fullitems[$k]['brutto'] 	-= $priceA['brutto'];
										if ($this->fullitems[$k]['brutto'] < 0) {
											$this->fullitems[$k]['brutto'] = 0;
										} else {
											$this->total['brutto']			-= ($priceA['brutto'] * $fQ);
										}
										$this->fullitems[$k]['tax']		-= $priceA['tax'];
										if ($this->fullitems[$k]['tax'] < 0) {
											$this->fullitems[$k]['tax'] = 0;
										} else {
											$this->total['tax'][$item2->taxid]['tax']	-= ($priceA['tax'] * $fQ);
										}
									} else if ($attrib->operator == '+') {
										
										$this->fullitems[$k]['brutto'] 			+= $priceA['brutto'];// * multiply in render checkout
										$this->fullitems[$k]['netto'] 			+= $priceA['netto'];// * multiply in render checkout
										$this->fullitems[$k]['tax']				+= $priceA['tax'];// * multiply in render checkout
										$this->total['netto']					+= ($priceA['netto'] * $fQ );
										$this->total['brutto']					+= ($priceA['brutto'] * $fQ );
										$this->total['tax'][$item2->taxid]['tax']+= ($priceA['tax'] * $fQ );
										
									}
									

									// Coupon
									if ($couponVB && $couponVA) {
								
										// COUPON FIXED amount needs to be divided to products do a statisctis
										$this->total['coupontaxcount'] += ($fQ);
										$this->total['tax'][$item2->taxid]['coupontaxcount'] += $fQ;
										//$this->total['tax'][$item2->taxid]['taxrate'] = $fQ;
										
										// COUPON CALCULATION - 1. PERCENTAGE
										if ($couponI['calculation_type'] == 1 && $couponI['discount'] > 0) {
	
											$cDN 	= $priceA['netto'] *  $couponI['discount'] / 100;
											$cDB 	= $priceA['brutto'] * $couponI['discount'] / 100;
											$cDT 	= $priceA['tax'] *  $couponI['discount'] / 100;
											
											if ($attrib->operator == '-') {
												$this->total['cnetto']	-= ($cDN * $fQ);
												$this->total['cbrutto']	-= ($cDB * $fQ);
												$this->total['ctax'][$item2->taxid]['tax']	-= ($cDT * $fQ);
												
												
												$this->fullitems[$k]['dbrutto'] -= ($priceA['brutto'] - $cDB);// Correct coupon by attrib
												$this->fullitems[$k]['dnetto'] 	-= ($priceA['netto'] - $cDN);
												$this->fullitems[$k]['dtax']	-= ($priceA['tax'] - $cDT);
												
											} else if ($attrib->operator == '+') {
												
												$this->total['cnetto']	+= ($cDN * $fQ);
												$this->total['cbrutto']	+= ($cDB * $fQ);
												$this->total['ctax'][$item2->taxid]['tax']+= ($cDT * $fQ);
												
												$this->fullitems[$k]['dbrutto'] += ($priceA['brutto'] - $cDB);// Correct coupon by attrib
												$this->fullitems[$k]['dnetto'] 	+= ($priceA['netto'] - $cDN);
												$this->fullitems[$k]['dtax']	+= ($priceA['tax'] - $cDT);
									
											}
									
											
										}
									}
									
									// Weight
									if ($attrib->operator_weight == '-') {
										$this->fullitems[$k]['weight'] 	-= $attrib->weight;
										if ($this->fullitems[$k]['weight'] < 0) {
											$this->fullitems[$k]['weight'] = 0;
										} else {
											$this->total['weight']			-= ($attrib->weight * $this->fullitems[$k]['quantity']);
										}
									}  else if ($attrib->operator_weight == '+') {
										$this->fullitems[$k]['weight'] 		+= $attrib->weight;
										$this->total['weight']				+= ($attrib->weight * $this->fullitems[$k]['quantity']);
										
									}
									
									// Volume - not used now
									/*if ($attrib->operator_volume == '-') {
										$this->fullitems[$k]['volume'] 	-= $attrib->volume;
										if ($this->fullitems[$k]['volume'] < 0) {
											$this->fullitems[$k]['volume'] = 0;
										} else {
											$this->total['volume']			-= ($attrib->volume * $this->fullitems[$k]['quantity']);
										}
									}  else if ($attrib->operator_volume == '+') {
										$this->fullitems[$k]['volume'] 		+= $attrib->volume;
										$this->total['volume']				+= ($attrib->volume * $this->fullitems[$k]['quantity']);
										
									}*/
									
									// STOCK
									
									if ((int)$this->fullitems[$k]['quantity'] > (int)$attrib->stock) {
										$this->stockvalid = 0;// Global - some of the product is out of stock
										$this->fullitems[$k]['stockvalid'] = 0;// Current product is out of stock
									}
									
									// Attribute values
									$this->fullitems[$k]['attributes'][$attrib->aid]['aid'] 	= $attrib->aid;// Attribute Id
									$this->fullitems[$k]['attributes'][$attrib->aid]['atitle'] 	= $attrib->atitle;
									$this->fullitems[$k]['attributes'][$attrib->aid]['oid'] 	= $attrib->id;// Option Id
									$this->fullitems[$k]['attributes'][$attrib->aid]['otitle'] 	= $attrib->title;
								}
							}
							
						}
					}
					
					
					// Last place to influence $k
					
					// STOCK VALID CHECK
					if ((int)$this->fullitems[$k]['quantity'] > (int)$this->fullitems[$k]['stock']) {
						$this->stockvalid = 0;// Global - some of the product is out of stock
						$this->fullitems[$k]['stockvalid'] = 0;// Current product is out of stock
						
					}
					
					// ==============================
					// THERE CAN BE TWO METHODS HOW TO COUNT MINIMUM ORDER AMOUNT
					// a) every product is unique (Product A - Option A, Product A - Option B are two different products) - NOT USED
					// b) there are product groups (Product A- Option A, Product A - Option B is still one product - product A)
					
					// a)
					// MINIMUM QUANTITY - FOR ITEM
					/*if ((int)$this->fullitems[$k]['quantity'] < (int)$this->fullitems[$k]['minqty']) {
						$this->minqtyvalid = 0;
						$this->fullitems[$k]['minqtyvalid'] = 0;
					}*/
					
					// b)
					// MINIMUM QUANTITY - FOR GROUP (Group is the same product but with different options values)
					// we cannot define default so this needs be set here
					if (isset($this->fullitemsgroup[$itemId]['quantity'])) {
						$this->fullitemsgroup[$itemId]['quantity'] = $this->fullitemsgroup[$itemId]['quantity'] + $this->fullitems[$k]['quantity'];
					} else {
						$this->fullitemsgroup[$itemId]['quantity'] = $this->fullitems[$k]['quantity'];
					}
					
					if (!isset($this->fullitemsgroup[$itemId]['minqtyvalid'])) {
						$this->fullitemsgroup[$itemId]['minqtyvalid'] = 1;
					}
					
					if (((int)$this->fullitemsgroup[$itemId]['quantity']) < (int)$this->fullitemsgroup[$itemId]['minqty']) {
						$this->minqtyvalid = 0;
						$this->fullitemsgroup[$itemId]['minqtyvalid'] = 0;
					} else {
						// Set it back because we are in foreach
						$this->minqtyvalid = 1;
						$this->fullitemsgroup[$itemId]['minqtyvalid'] = 1;
					}
					
				}
				
				// COUPON total check
				$couponVT = false;
				if (!empty($coupon)) {
					$couponVT = $coupon->checkCouponTotal($this->total['netto'], $couponI['total_amount']);
				}
				
				
				if ($couponVT && $couponVB && $couponVAT) { // IMPORTANT we cannot subtract when coupon is not valid
					$this->couponvalid = 1;
					
					
					// COUPON CALCULATION - 0. FULL DISCOUNT
					if ($couponI['calculation_type'] == 0 && $couponI['discount'] > 0) {
						
						
						$this->total['cnetto']	+= $couponI['discount'];
						$this->total['cbrutto']	+= $couponI['discount'];
		
		
						// DIVIDE FULL FIXED COUPON TO TAX
						if(!empty($this->total['tax'])) {
							foreach($this->total['tax'] as $k => $v) {
								// Ratio - count of all items against product items
								
								if ((int)$this->total['coupontaxcount'] > 0) {
									$r = $this->total['tax'][$k]['coupontaxcount'] / $this->total['coupontaxcount'];
								} else {
									$r = 0;
								}
								
								// Fixed discount will be divided between the product items with help of ratio (still fixed)
								$rD= $couponI['discount'] * $r;
							
								// Coupon sale for each tax
								if ($this->total['tax'][$k]['type'] == 1) {
									$this->total['ctax'][$k]['tax'] = $rD *  $this->total['tax'][$k]['rate'] / 100;
									
									$this->total['cbrutto'] += $this->total['ctax'][$k]['tax'];
								} else if ($this->total['tax'][$k]['type'] == 2) {
									// if the tax is fixed, nothing changed in tax calculation, it will be always the same value
									//if (!isset($this->total['ctax'][$k]['tax'])) {
									//	$this->total['ctax'][$k]['tax'] = 0;
									//}
									//$this->total['ctax'][$k]['tax'] += $rD;
									//$this->total['cbrutto'] += $this->total['ctax'][$k]['tax'];
								}
								
							}
						
						}
						
						// DIVIDE FULL FIXED COUPON TO ITEMS
						if(!empty($this->fullitems)) {
							foreach($this->fullitems as $k => $v) {
								// Ratio - count of all items against product items
								
								if ($this->fullitems[$k]['discountapply'] == 0) {
									break;
								}
								if ((int)$this->fullitems[$k]['quantity'] > 0) {
									$r = $this->fullitems[$k]['quantity'] / $countAllCouponItems;
								} else {
									$r = 0;
								}
								
								
								// Fixed discount will be divided between the product items with help of ratio (still fixed)
								$rD		= $couponI['discount'] * $r;
								$rDI 	= $rD / $this->fullitems[$k]['quantity'];// Store discount for each item
								

								$priceI = $price->getPriceItems($this->fullitems[$k]['price'], $this->fullitems[$k]['taxrate'], $this->fullitems[$k]['taxtcalctype'], $this->fullitems[$k]['taxtitle']);
								
								// We count the discount from netto, so use it for percantage to count for brutto and tax
								$rDIP = $rDI * 100 / $priceI['netto'];
							
								$this->fullitems[$k]['dnetto'] 	= $priceI['netto'] - ($priceI['netto'] * $rDIP/100);
								$this->fullitems[$k]['dbrutto'] = $priceI['brutto'] - ($priceI['brutto'] * $rDIP/100);
								$this->fullitems[$k]['dtax'] 	= $priceI['tax'] - ($priceI['tax'] * $rDIP/100);
								if ($this->fullitems[$k]['dtax'] < 0) {
									$this->fullitems[$k]['dtax'] = 0;
								}
								// FIXED TAX?
								/*if ($this->total['tax'][$k]['type'] == 1) {
								} else if ($this->total['tax'][$k]['type'] == 2) {
								}*/
								
							}
						
						}
						
						

					}
					
				}
			}
		}
	}
	
	public function getTotal() {
		
		return $this->total;
	}
	
	public function getItems() {
		
		return $this->items;
	}
	
	public function getFullItems() {
		
		return $this->fullitems;
	}
	
	public function getPaymentMethod() {
		$payment = array();
		$payment['title'] 	= $this->paymenttitle;
		$payment['method'] 	= $this->paymentmethod;
		$payment['id']		= $this->paymentid;
		
		// E.g. guest checkout
		if (isset($payment['id']) && (int)$payment['id'] > 0 && $payment['title'] == '' && $payment['method'] == '') {
			$paymentNew	= new PhocaCartPayment();
			$pI	= $paymentNew->getPaymentMethod((int)$payment['id']);
			if (isset($pI->title)) {
				$payment['title'] 	= $pI->title;
			}
			if (isset($pI->method)) {
				$payment['method'] 	= $pI->method;
			}
		}
		return $payment;
	}
	
	public function getCoupon() {
		$coupon = array();
		$coupon['title'] 	= $this->coupontitle;
		$coupon['id']		= $this->couponid;
		
		// E.g. guest checkout
		if (isset($coupon['id']) && (int)$coupon['id'] > 0 && $coupon['title'] == '') {
			$cI = PhocaCartCoupon::getCouponTitleById((int)$coupon['id']);
			if (isset($cI->title)) {
				$coupon['title'] 	= $cI->title;
			}
			
		}
		return $coupon;
	}
	
	public function getShippingId() {
		return $this->shippingid;
	}
	

	
	public function getCouponTitle() {
		return $this->coupontitle;
	}
	
	public function getCouponValid() {
		return $this->couponvalid;
	}
	
	public function getShippingCosts() {
		return $this->shippingcosts;
	}
	
	public function getPaymentCosts() {
		return $this->paymentcosts;
	}
	
	public function getStockValid() {
		return $this->stockvalid;
	}
	
	public function getMinimumQuantityValid() {
		return $this->minqtyvalid;
	}
	
	public function addShippingCosts($shippingId = 0) {
		
		if ($shippingId == 0) {
			$shippingId = $this->shippingid;
		}

		$shipping	= new PhocaCartShipping();
		$sI	= $shipping->getShippingMethod((int)$shippingId);
		
		
		
		
		if(!empty($sI)) {
			// Coupon can cause free shipping
			$sI->freeshipping = 0;
			if ($this->couponvalid && $this->couponfreeshipping) {
				$sI->freeshipping = 1;
			}
		
			$price	= new PhocaCartPrice();
			$priceI = $price->getPriceItemsShipping($sI->cost, $sI->taxrate, $sI->taxcalctype, $sI->taxtitle, $sI->freeshipping);
			
			$this->shippingcosts = $priceI;
			$this->shippingcosts['title'] 		= $sI->title;
			$this->shippingcosts['description'] = $sI->description;
			
			if (isset($priceI['brutto']) && $priceI['brutto'] >  0) {
				$this->total['brutto'] += $priceI['brutto'];
			} else if (isset($priceI['netto']) && $priceI['netto'] >  0 && isset($priceI['tax']) && $priceI['tax'] >  0) {
				$this->total['brutto'] += ($priceI['netto'] + $priceI['tax']);
			}
		}
	}
	
	public function addPaymentCosts($paymentId = 0) {
		
		if ($paymentId == 0) {
			$paymentId = $this->paymentid;
		}
		
		$payment	= new PhocaCartPayment();
		$pI	= $payment->getPaymentMethod((int)$paymentId);
		
		
		
		if(!empty($pI)) {
			// Coupon can cause free shipping
			$pI->freepayment = 0;
			if ($this->couponvalid && $this->couponfreepayment) {
				$pI->freepayment = 1;
			}
		
			$price	= new PhocaCartPrice();
			$priceI = $price->getPriceItemsPayment($pI->cost, $pI->taxrate, $pI->taxcalctype, $pI->taxtitle, $pI->freepayment);
			
			$this->paymentcosts = $priceI;
			$this->paymentcosts['title'] = $pI->title;
			$this->paymentcosts['description'] = $pI->description;
			
			if (isset($priceI['brutto']) && $priceI['brutto'] >  0) {
				$this->total['brutto'] += $priceI['brutto'];
			} else if (isset($priceI['netto']) && $priceI['netto'] >  0 && isset($priceI['tax']) && $priceI['tax'] >  0) {
				$this->total['brutto'] += ($priceI['netto'] + $priceI['tax']);
			}
		}
	}
	
	public function emptyCart() {
		$session 		= JFactory::getSession();
		$session->set('cart', array(), 'phocaCart');
		if((int)$this->user->id > 0) {
			PhocaCartCartDb::emptyCartDb((int)$this->user->id);
		}
	}
}
?>