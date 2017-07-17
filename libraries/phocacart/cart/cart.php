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
/*
phocacart import('phocacart.user.user');
phocacart import('phocacart.user.guestuser');
phocacart import('phocacart.shipping.shipping');
phocacart import('phocacart.payment.payment');
*/
class PhocacartCart
{
	protected $items     			= array();
	protected $fullitems			= array();
	protected $fullitemsgroup		= array();// Group of one product: Product A (Option A) + Product A (Option B)
	protected $user					= array();
	protected $total				= array();
	
	
	/*protected $shippingid			= 0;
	protected $shippingcosts		= 0;
	protected $paymentid			= 0;
	protected $paymenttitle			= '';
	protected $paymentmethod		= '';
	protected $paymentcosts			= 0;*/
	/*protected $couponid				= 0;
	protected $coupontitle			= '';
	protected $couponamount			= 0;
	protected $couponfreeshipping 	= 0;// Does the copoun cause free shipping
	protected $couponfreepayment 	= 0;// Does the copoun cause free payment
	protected $couponvalid 			= 0;// is the coupon valid - all three tests - basic, advanced, total*/
	
	protected $coupon				= array();
	protected $reward				= array();
	protected $cartdiscount			= array();
	protected $shipping				= array();
	protected $payment				= array();
	
	protected $stock				= array();
	protected $minqty				= array();
	protected $minmultipleqty		= array();

	
	
	public function __construct() {
		
		$app 			= JFactory::getApplication();
		$session 		= JFactory::getSession();
		$this->user		= JFactory::getUser();
		$guest			= PhocacartUserGuestuser::getGuestUser();
		
		$this->coupon['id']			= 0;
		$this->coupon['title']		= '';
		$this->coupon['valid']		= 0;
		$this->coupon['code']		= '';
		
		$this->reward['used']		= '';
		
		$this->cartdiscount['id']	= 0;
		$this->cartdiscount['title']= '';
		
		$this->shipping['id']		= 0;
		$this->shipping['costs']	= 0;
		
		$this->payment['id']				= 0;
		$this->payment['title']				= '';
		$this->payment['method']			= '';
		$this->payment['costs']				= 0;
		$this->payment['calculation_type']	= 0;
		
		
		$this->stock['valid']			= 1;// check stock - products, attributes (no matter if stock checking is disabled or enabled)
		$this->minqty['valid']			= 1;// check minimum order quantity
		$this->minmultipleqty['valid']	= 1;// check minimum multiple order quantity
		

		
		// Admin info
		if ($app->getName() == 'administrator') {
			$id				= $app->input->get('id', 0, 'int');
			$cartDb 		= PhocacartCartDb::getCartDb($id);
			$this->items	= $cartDb['cart'];
		}
		
		if((int)$this->user->id > 0) {
			// DATABASE - logged in user - Singleton because of not load data from database every time cart instance is loaded
			// 1. Not found in DATABASE - maybe user logged in now, so:
			// 2. We try to find the data in SESSION, if they are still in SESSION - load them to our cart class and then
			// 3. Store them to DATABASE as all loged in users have cart in database and:
			// 4. Remove them from SESSION as they are stored in DATABASE
			$cartDb = PhocacartCartDb::getCartDb($this->user->id);// user logged in - try to get cart from db
			$this->items 			= $cartDb['cart'];
			$this->coupon['id']		= $cartDb['coupon'];
			$this->coupon['title']	= $cartDb['coupontitle'];
			$this->coupon['code']	= $cartDb['couponcode'];
			$this->shipping['id']	= $cartDb['shipping'];
			$this->payment['id']	= $cartDb['payment'];
			$this->payment['title']	= $cartDb['paymenttitle'];
			$this->payment['method']= $cartDb['paymentmethod'];
			$this->reward['used']	= $cartDb['reward'];
			$sessionItems = $session->get('cart', array(), 'phocaCart');
			
			if(empty($this->items)) {
				$this->items	= $session->get('cart', array(), 'phocaCart');
				if(!empty($this->items)) {
					$this->updateItemsDb();
					$session->set('cart', array(), 'phocaCart');
				}
			} else {
				// we have stored items in database from previously
				// and we have stored items in session now
				if(!empty($sessionItems)) {
					// inform users and clean session
					$message = JText::_( 'COM_PHOCACART_CART_UPDATED_BASED_ON_YOUR_PREVIOUS_VISIT' );
					$app->enqueueMessage($message, 'message');
					$session->set('cart', array(), 'phocaCart');
				}
				
			}
		} else if ($guest) {
			$this->items 			= $session->get('cart', array(), 'phocaCart');
			$this->shipping['id']	= $session->get('guestshipping', false, 'phocaCart');
			$this->payment['id']	= $session->get('guestpayment', false, 'phocaCart');
			$this->coupon['id']		= $session->get('guestcoupon', false, 'phocaCart');
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
			///$k = (int)$id . ':';
			
			$checkP = PhocacartProduct::checkIfAccessPossible($id, $catid);
			
			if (!$checkP) {	
				$uri 			= JFactory::getURI();
				$action			= $uri->toString();
				$app			= JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_NO_RIGHTS_FOR_ORDERING_PRODUCT'), 'error');
				return false;
			}
			
			// Check if there is required attribute
			// In fact this should not happen in item view, as this should be checked per html5 checking form
			// This is security check on server side - e.g. when testing attributes in items or category view

			$checkedA = PhocacartAttribute::checkRequiredAttributes($id, $attributes);

			if (!$checkedA) {
				$uri 			= JFactory::getURI();
				$action			= $uri->toString();
				$app			= JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_SELECTING_ATTRIBUTE_IS_REQUIRED'), 'error');
				return false;
			}
			
			
			
			/*if (!empty($attributes)) {

				// Remove empty values, so items with empty values (add to cart item view) is the same
				// like item without any values (add to cart category view)
				foreach($attributes as $k2 => $v2) {
					if ($v2 == 0 || $v2 == '') {
						unset($attributes[$k2]);
					}
				}
				if (!empty($attributes)) {
					$k .= base64_encode(serialize($attributes));
				}
			}
			$k .= ':';*/
			$k = PhocacartProduct::getProductKey($id, $attributes);
			
			
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
			PhocacartShipping::removeShipping();
			PhocacartPayment::removePayment();
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
	
	
	public function updateSubTotal() {}
	*/
	
	
	/*
	 * Set full items - items in cart, total(netto, tax, brutto), coupon,
	 * We need to set it first, to count e.g. coupon
	 * After counting all possible factors (items, count of items, netto, taxes, brutto, coupon, shipping)
	 * we can render the cart (for example, free shipping by coupon needs to be set at the end as we check total netto amount)
	 */
	public function setFullItems() {
		
		// posible to do fullitems[1];
		if (empty($this->fullitems)) {
			if(!empty($this->items)) {
			
				$app				= JFactory::getApplication();
				$paramsC 			= PhocacartUtils::getComponentParameters();
				$tax_calculation	= $paramsC->get( 'tax_calculation', 0 );
				
				$price	= new PhocacartPrice();
				$calc 	= new PhocacartCartCalculation();
				
				
				// CHECK ACCESS OF ALL ITEMS IN CART
				foreach($this->items as $k => $v) {
					$item 	= explode(':', $k);
					$itemId = $item[0];
					$checkP = PhocacartProduct::checkIfAccessPossible((int)$itemId, (int)$v['catid']);
			
					if (!$checkP){
						unset($this->items[$k]);
					}
				}
				if(empty($this->items)) {
					return false;
				}
				// END ACCESS
				
				// --------------------
				// 1) Basic Calculation
				// --------------------
				$calc->calculateBasicProducts($this->fullitems[1], $this->fullitemsgroup[1], $this->total[1], $this->stock, $this->minqty, $this->minmultipleqty, $this->items);
				
				//$calc->round($this->total[1]);
			
				$this->fullitems[0] 		= $this->fullitems[4] 		= $this->fullitems[3] 		= $this->fullitems[2] 		
											= $this->fullitems[5]		= $this->fullitems[1];
				
				$this->fullitemsgroup[0]	= $this->fullitemsgroup[4] 	= $this->fullitemsgroup[3] 	= $this->fullitemsgroup[2] 	
											= $this->fullitemsgroup[5]	= $this->fullitemsgroup[1];
											
				$this->total[0] 			= $this->total[4] 			= $this->total[3] 			= $this->total[2]
											= $this->total[5]			= $this->total[1];

											
											
										
				// --------------------
				// 5) Reward Points
				// --------------------
				$calc->calculateRewardDiscounts($this->fullitems[5], $this->fullitemsgroup[5], $this->total[5], $this->reward);
				
				
				$this->fullitems[0] 		= $this->fullitems[4]		= $this->fullitems[3] 		= $this->fullitems[2]
											= $this->fullitems[5];
											
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4]	= $this->fullitemsgroup[3] 	= $this->fullitemsgroup[2]
											= $this->fullitemsgroup[5];
											
				$this->total[0] 			= $this->total[4]			= $this->total[3] 			= $this->total[2]
											= $this->total[5];
											
				// Subtotal after 2) Discount
				$this->total[5]['dnetto']	= $this->total[1]['netto'] - $this->total[5]['netto'];
				$this->total[5]['dbrutto']	= $this->total[1]['brutto'] - $this->total[5]['brutto'];
		
		
		
		
				// --------------------
				// 2) Product Discount
				// --------------------
				$calc->calculateProductDiscounts($this->fullitems[2], $this->fullitemsgroup[2], $this->total[2]);
				
				//$calc->round($this->total[2]);
				
				$this->fullitems[0] 		= $this->fullitems[4]		= $this->fullitems[3] 		= $this->fullitems[2];				
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4]	= $this->fullitemsgroup[3] 	= $this->fullitemsgroup[2];
				$this->total[0] 			= $this->total[4]			= $this->total[3] 			= $this->total[2];

				// Reset variables (after copying we need to clear some of them)
				
				
				// Subtotal after 2) Discount
				$this->total[2]['dnetto']	= $this->total[5]['netto'] - $this->total[2]['netto'];
				$this->total[2]['dbrutto']	= $this->total[5]['brutto'] - $this->total[2]['brutto'];
				
				
				
				// --------------------
				// 3) Cart Discount
				// --------------------
				$calc->calculateCartDiscounts($this->fullitems[3], $this->fullitemsgroup[3], $this->total[3], $this->cartdiscount);
				
				// 3b) Cart Discount - we need to divide fixed amount discount into products which meets the rules to get each discount
				if (!empty($this->total[3]['discountcartfixedamount'])) {
					$calc->recalculateCartDiscounts($this->fullitems[3], $this->fullitemsgroup[3], $this->total[3]);
				}

				//$calc->round($this->total[3]);
				
				// Subtotal after 3) Discount
				$this->total[3]['dnetto']	= $this->total[2]['netto'] - $this->total[3]['netto'];
				$this->total[3]['dbrutto']	= $this->total[2]['brutto'] - $this->total[3]['brutto'];
				
				$calc->roundFixedAmountDiscount($this->total[3]);
				
				$this->fullitems[0] 		= $this->fullitems[4] 		= $this->fullitems[3];
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4] 	= $this->fullitemsgroup[3];
				$this->total[0] 			= $this->total[4]			= $this->total[3];
				
				
				
				// --------------------
				// 4) Cart Coupon
				// --------------------
					
				$calc->calculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4], $this->coupon);
				
				// 4b) Cart Coupon - we need to divide fixed amount coupon into products which meets the rules to get each coupon
				if (!empty($this->total[4]['couponcartfixedamount'])) {
					$calc->recalculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4]);
				}

				
				//$calc->round($this->total[4], 4);
				

				
				// Subtotal after 4) Coupon
				$this->total[4]['dnetto']	= $this->total[3]['netto'] - $this->total[4]['netto'];
				$this->total[4]['dbrutto']	= $this->total[3]['brutto'] - $this->total[4]['brutto'];
				
				$calc->roundFixedAmountCoupon($this->total[4]);
				
				
				$this->fullitems[0] 		= $this->fullitems[4];
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4];
				$this->total[0] 			= $this->total[4];
				
				
				//$calc->round($this->total[0], 0);
				$calc->roundFixedAmountCoupon($this->total[4]);

				
				
				/*foreach($this->fullitems[0] as $k => $v) {
					$item 	= explode(':', $k);
					$attribs = unserialize(base64_decode($item[1]));
					
				}*/
				
				// TOTAL still not ready
				// + Shipping addShippingCosts()
				// + Payment addPaymentCosts()
				

					
			}
		}
	}
	
	public function roundTotalAmount() {
		
		$price		= new PhocacartPrice();
		$calc 		= new PhocacartCartCalculation();
		$currency 	= PhocacartCurrency::getCurrency();
		$cr			= $currency->exchange_rate;
		$total 		= 0; // total in default currency
		$totalC		= 0; // total in order currency
		
	
		
		// Subtotal
		if (isset($this->total[1]['netto'])) {
			$total += $price->roundPrice($this->total[1]['netto']);
			$totalC += $price->roundPrice($this->total[1]['netto'] * $cr);
			
		}
		
		// - Reward points
		if (isset($this->total[5]['dnetto'])) {
			$total -= $price->roundPrice($this->total[5]['dnetto']);
			$totalC -= $price->roundPrice($this->total[5]['dnetto'] * $cr);
			
		}
		
		// - Product Discount
		if (isset($this->total[2]['dnetto'])) {
			$total -= $price->roundPrice($this->total[2]['dnetto']);
			$totalC -= $price->roundPrice($this->total[2]['dnetto'] * $cr);
			
		}
		
		// - Discount cart
		if (isset($this->total[3]['dnetto'])) {
			$total -= $price->roundPrice($this->total[3]['dnetto']);
			$totalC -= $price->roundPrice($this->total[3]['dnetto'] * $cr);
		}
		
		// - Coupon cart
		if (isset($this->total[4]['dnetto'])) {
			$total -= $price->roundPrice($this->total[4]['dnetto']);
			$totalC -= $price->roundPrice($this->total[4]['dnetto'] * $cr);
		}
		
		// + VAT
		if (!empty($this->total[0]['tax'])) {
			foreach ($this->total[0]['tax'] as $k => $v) {
				$total += $price->roundPrice($v['tax']);
				$totalC += $price->roundPrice($v['tax'] * $cr);
			}
		}
		
		// + Shipping Costs
		if (isset($this->shipping['costs']['brutto'])) {
			$total += $price->roundPrice($this->shipping['costs']['brutto']);
			$totalC += $price->roundPrice($this->shipping['costs']['brutto'] * $cr);
		}
		
		// + Payment Costs
		if (isset($this->payment['costs']['brutto'])) {
			$total += $price->roundPrice($this->payment['costs']['brutto']);
			$totalC += $price->roundPrice($this->payment['costs']['brutto'] * $cr);
		}
		
	
		$calc->roundTotalAmount($this->total[0], $total, $totalC);
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
	
	public function getCoupon() {
		
		$coupon = array();
		$coupon['title'] 	= $this->coupon['title'];
		$coupon['id']		= $this->coupon['id'];
		
		// E.g. guest checkout
		if (isset($coupon['id']) && (int)$coupon['id'] > 0 && $coupon['title'] == '') {
			$cI = PhocacartCoupon::getCouponTitleById((int)$coupon['id']);
			if (isset($cI->title)) {
				$coupon['title'] 	= $cI->title;
			}	
		}
		return $coupon;
	}
	
	public function getPaymentMethod() {
		
		$payment = array();
		$payment['title'] 	= $this->payment['title'];
		$payment['method'] 	= $this->payment['method'];
		$payment['id']		= $this->payment['id'];
		
		// E.g. guest checkout
		if (isset($payment['id']) && (int)$payment['id'] > 0 && $payment['title'] == '' && $payment['method'] == '') {
			$paymentNew	= new PhocacartPayment();
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
	
	public function getShippingId() {
		return $this->shipping['id'];
	}

	public function getCouponTitle() {
		return $this->coupon['title'];
	}
	
	public function getCouponCode() {
		return $this->coupon['code'];
	}
	
	public function getCartDiscountTitle() {
		return $this->cartdiscount['title'];
	}
	
	public function getCartDiscountId() {
		return $this->cartdiscount['id'];
	}

	
	public function getCouponValid() {
		return $this->coupon['valid'];
	}
	
	public function getShippingCosts() {
		return $this->shipping['costs'];
	}
	
	public function getPaymentCosts() {
		return $this->payment['costs'];
	}
	
	public function getStockValid() {
		return $this->stock['valid'];
	}
	
	public function getMinimumQuantityValid() {
		return $this->minqty['valid'];
	}
	
	public function getMinimumMultipleQuantityValid() {
		return $this->minmultipleqty['valid'];
	}
	
	public function getRewardPointsNeeded() {
		return $this->total[0]['points_needed'];
	}
	
	public function getRewardPointsReceived() {
		return $this->total[0]['points_received'];
	}
	public function getRewardPointsUsed() {
		return $this->reward['used'];
	}
	
	
	public function addShippingCosts($shippingId = 0) {
		
		if ($shippingId == 0) {
			$shippingId = $this->shipping['id'];
		}

		$shipping	= new PhocacartShipping();
		$sI	= $shipping->getShippingMethod((int)$shippingId);

		if(!empty($sI)) {
			
			$sI->freeshipping = 0;
			
			if ($this->total[0]['free_shipping'] == 1) {
				$sI->freeshipping = 1;
			}
		
			$price	= new PhocacartPrice();
			$priceI = $price->getPriceItemsShipping($sI->cost, $sI->calculation_type, $this->total[0], $sI->taxid, $sI->taxrate, $sI->taxcalculationtype, $sI->taxtitle, $sI->freeshipping);
			
			// CALCULATION
			$calc 						= new PhocacartCartCalculation();
			$this->shipping['costs'] 	= $priceI;
			
			if ($this->total[0]['free_shipping'] != 1) {
				$this->shipping['costs']['title'] 		= $sI->title;
				$this->shipping['costs']['description'] = $sI->description;
			}
			$calc->calculateShipping($priceI, $this->total[0]);
			//$calc->round($this->total[0], 0);

		}
	}
	
	public function addPaymentCosts($paymentId = 0) {
		
		if ($paymentId == 0) {
			$paymentId = $this->payment['id'];
		}
		
		$payment	= new PhocacartPayment();
		$pI	= $payment->getPaymentMethod((int)$paymentId);

		if(!empty($pI)) {
			
			$pI->freepayment = 0;
			
			if (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] == 1) {
				$pI->freepayment = 1;
			}
		
			$price	= new PhocacartPrice();
			$priceI = $price->getPriceItemsPayment($pI->cost, $pI->calculation_type, $this->total[0], $pI->taxid, $pI->taxrate, $pI->taxcalculationtype, $pI->taxtitle, $pI->freepayment);
			
			
			// CALCULATION
			$calc 						= new PhocacartCartCalculation();
			$this->payment['costs'] 	= $priceI;
			
			if (!isset($this->total[0]['free_payment']) || (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] != 1)) {
				$this->payment['costs']['title'] 		= $pI->title;
				$this->payment['costs']['description'] 	= $pI->description;
			}
			$calc->calculatePayment($priceI, $this->total[0]);
			//$calc->round($this->total[0], 0);
			
		}
	}
	
	public function emptyCart() {
		$session 		= JFactory::getSession();
		$session->set('cart', array(), 'phocaCart');
		if((int)$this->user->id > 0) {
			PhocacartCartDb::emptyCartDb((int)$this->user->id);
		}
	}
}
?>