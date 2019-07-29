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
	protected $vendor				= array();
	protected $ticket				= array();
	protected $unit					= array();
	protected $section				= array();
	protected $loyalty_card_number	= '';
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
	protected $pos					= false;
	protected $type					= array(0,1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

	protected $removedproducts		= array(); // Products which not more exit (or their attributes/options) are removed
											   // if they were not removed previously - so e.g. when making an order
											   // the order will be not placed by user will get error message

	protected $instance				= 1; // 1.cart 2.checkout 3.order
	public function __construct() {


		$app 			= JFactory::getApplication();
		$session 		= JFactory::getSession();
		$dUser			= PhocacartUser::defineUser($this->user, $this->vendor, $this->ticket, $this->unit, $this->section);
		$guest			= PhocacartUserGuestuser::getGuestUser();

		$this->pos		= PhocacartPos::isPos();

		$this->coupon['id']					= 0;
		$this->coupon['title']				= '';
		$this->coupon['valid']				= 0;
		$this->coupon['code']				= '';
		$this->reward['used']				= '';
		$this->cartdiscount['id']			= 0;
		$this->cartdiscount['title']		= '';
		$this->shipping['id']				= 0;
		$this->shipping['title']			= '';
		$this->shipping['method']			= '';
		$this->shipping['costs']			= 0;
		$this->payment['id']				= 0;
		$this->payment['title']				= '';
		$this->payment['method']			= '';
		$this->payment['costs']				= 0;
		$this->payment['calculation_type']	= 0;
		$this->stock['valid']				= 1;// check stock - products, attributes (no matter if stock checking is disabled or enabled)
		$this->minqty['valid']				= 1;// check minimum order quantity
		$this->minmultipleqty['valid']		= 1;// check minimum multiple order quantity




		// Admin info - Administrator asks for information about user's cart
		if ($app->getName() == 'administrator') {
			$id				= $app->input->get('id', 0, 'int');
			$cartDb 		= PhocacartCartDb::getCartDb($id);
			$this->items	= $cartDb['cart'];
			return;

		}

		// POS

		if ($this->pos &&(int)$this->vendor->id > 0) {

			$cartDb = PhocacartCartDb::getCartDb((int)$this->user->id, (int)$this->vendor->id, (int)$this->ticket->id, (int)$this->unit->id, (int)$this->section->id);

			$this->items 				= $cartDb['cart'];
			$this->coupon['id']			= $cartDb['coupon'];
			$this->coupon['title']		= $cartDb['coupontitle'];
			$this->coupon['code']		= $cartDb['couponcode'];
			$this->shipping['id']		= $cartDb['shipping'];
			$this->shipping['title']	= $cartDb['shippingtitle'];
			$this->shipping['method']	= $cartDb['shippingmethod'];
			$this->payment['id']		= $cartDb['payment'];
			$this->payment['title']		= $cartDb['paymenttitle'];
			$this->payment['method']	= $cartDb['paymentmethod'];
			$this->reward['used']		= $cartDb['reward'];
			$this->loyalty_card_number	= $cartDb['loyalty_card_number'];


			// Don't care about session (use in session is customer, user in pos in db is vendor)



		} else if((int)$this->user->id > 0) {
			// DATABASE - logged in user - Singleton because of not load data from database every time cart instance is loaded
			// 1. Not found in DATABASE - maybe user logged in now, so:
			// 2. We try to find the data in SESSION, if they are still in SESSION - load them to our cart class and then
			// 3. Store them to DATABASE as all loged in users have cart in database and:
			// 4. Remove them from SESSION as they are stored in DATABASE
			$cartDb = PhocacartCartDb::getCartDb((int)$this->user->id);// user logged in - try to get cart from db
			$this->items 				= $cartDb['cart'];

			$this->coupon['id']			= $cartDb['coupon'];
			$this->coupon['title']		= $cartDb['coupontitle'];
			$this->coupon['code']		= $cartDb['couponcode'];
			$this->shipping['id']		= $cartDb['shipping'];
			$this->shipping['title']	= $cartDb['shippingtitle'];
			$this->shipping['method']	= $cartDb['shippingmethod'];
			$this->payment['id']		= $cartDb['payment'];
			$this->payment['title']		= $cartDb['paymenttitle'];
			$this->payment['method']	= $cartDb['paymentmethod'];
			$this->reward['used']		= $cartDb['reward'];
			$this->loyalty_card_number	= $cartDb['loyalty_card_number'];
			$sessionItems = $session->get('cart', array(), 'phocaCart');




			if(empty($this->items)) {
				$this->items	= $session->get('cart', array(), 'phocaCart');
				if(!empty($this->items)) {
					$this->updateItemsDb();
					// Very important - clean the static variable
					// because more instances of cart are loading the static variable once
					// So if the FIRST instance loaded empty cart
					// and then has filled this empty cart with data from session
					// SECOND instance must load the static variables newly because if it does not do
					// then it get the same data like FIRST instance (empty cart) But the cart
					// was filled in with data from session so there will be contradiction
					// between instances (FIRST INSTANCE - EMPTY CART BUT REFILLED BY SESSION = FULL CART, SECOND INSTANCE EMPTY CART SET BY FIRST INSTANCE)
					PhocacartCartDb::clearCartDbVariable((int)$this->user->id);
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
			$this->items 				= $session->get('cart', array(), 'phocaCart');
			$this->shipping['id']		= $session->get('guestshipping', false, 'phocaCart');
			$this->payment['id']		= $session->get('guestpayment', false, 'phocaCart');
			$this->coupon['id']			= $session->get('guestcoupon', false, 'phocaCart');
			$this->loyalty_card_number	= $session->get('guestloyaltycardnumber', false, 'phocaCart');
		} else {
			// SESSION - not logged in user
			$this->items	= $session->get('cart', array(), 'phocaCart');
		}


	}

	/*
	 * 1. cart
	 * 2. checkout
	 * 3. order
	 */
	public function setInstance($type) {
		$this->instance = (int)$type;
	}

	/*
	 * 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)
	 */
	public function setType ($type = array(0,1)) {
		$this->type = $type;
	}

	/*
	 * Catid is only for information from which place the product was added to the cart
	 * When making order, this will be recheck
	 */
	public function addItems($id = 0, $catid = 0, $quantity = 0, $attributes = array(), $idKey = '') {

		$app			= JFactory::getApplication();

		if ($idKey != '') {
			// we get idkey as string - from checkout update or remove -  used in CHECKOUT
		} else {
			// we get id as int standard id of product - attributes can be listed in form - used in CATEGORY, ITEM, ITEMS
			///$k = (int)$id . ':';


			$checkP = PhocacartProduct::checkIfAccessPossible($id, $catid, $this->type);

			if (!$checkP) {
				//$uri 			= \Joomla\CMS\Uri\Uri::getInstance();
				//$action			= $uri->toString();

				$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_NO_RIGHTS_FOR_ORDERING_PRODUCT'), 'error');
				return false;
			}

			// Check if there is required attribute
			// In fact this should not happen in item view, as this should be checked per html5 checking form
			// This is security check on server side - e.g. when testing attributes in items or category view

			$checkedA = PhocacartAttribute::checkRequiredAttributes($id, $attributes);

			if (!$checkedA) {
				//$uri 			= \Joomla\CMS\Uri\Uri::getInstance();
				//$action			= $uri->toString();

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
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_QUANTITY_WAS_NOT_SET'), 'error');
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
		if($this->pos && (int)$this->vendor->id > 0) {

			$this->updateItemsDb();
			// if user changes the cart shipping method needs to be removed because it can be based on amount or region, etc.
			// payment is for now without dependency to address or to amount, don't update it
			PhocacartShipping::removeShipping();
			PhocacartPayment::removePayment();
		} else if((int)$this->user->id > 0) {
			$this->updateItemsDb();
			// if user changes the cart shipping method needs to be removed because it can be based on amount or region, etc.
			// payment is for now without dependency to address or to amount, don't update it
			PhocacartShipping::removeShipping();
			PhocacartPayment::removePayment();
			$session->set('cart', array(), 'phocaCart');
		} else {

			PhocacartShipping::removeShipping(1);// session for shipping even removed
			PhocacartPayment::removePayment(1);// session for payment even removed
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

		// Update multiple cart (include vendor, ticket)
		$query = ' SELECT user_id, vendor_id, ticket_id, unit_id, section_id FROM #__phocacart_cart_multiple'
				.' WHERE user_id = '.(int)$this->user->id
				.' AND vendor_id = '.(int)$this->vendor->id
				.' AND ticket_id = '.(int)$this->ticket->id
				.' AND unit_id = '.(int)$this->unit->id
				.' AND section_id = '.(int)$this->section->id
				.' ORDER BY user_id LIMIT 1';
		$db->setQuery($query);
		$result = $db->loadRow();
		if (!empty($result)) {

			$query = 'UPDATE #__phocacart_cart_multiple'
			.' SET cart = '.$db->quote($items).','
			.' date = '.$db->quote($now)
			.' WHERE user_id = '.(int)$this->user->id
			.' AND vendor_id = '.(int)$this->vendor->id
			.' AND ticket_id = '.(int)$this->ticket->id
			.' AND unit_id = '.(int)$this->unit->id
			.' AND section_id = '.(int)$this->section->id;

			$db->setQuery($query);
			$db->execute();
		} else {
			if ((int)$this->user->id == 0 && (int)$this->vendor->id == 0) {
				// Not possible now
				// guests do not store cart to database
				// if userid == 0: 1) guest (not possible) 2) vendor uses pos (vendor must be > 0)
				// if vendorid == 0: 1) standard eshop - userid must be > 0)
				// ticket can be always zero
			} else {

				$query = 'INSERT INTO #__phocacart_cart_multiple (user_id, vendor_id, ticket_id, unit_id, section_id, cart, date)'
				.' VALUES ('.(int)$this->user->id.', '.(int)$this->vendor->id.', '.(int)$this->ticket->id.', '.(int)$this->unit->id.', '.(int)$this->section->id.', '.$db->quote($items).', '.$db->quote($now).');';
				$db->setQuery($query);
				$db->execute();
			}

		}

		// Update single cart (no vendor, no ticket)
		/*$query = 'INSERT INTO #__phocacart_cart (user_id, cart, date)'
				.' VALUES ('.(int)$this->user->id.', '.$db->quote($items).', '.$db->quote($now).')'
				.' ON DUPLICATE KEY UPDATE cart = VALUES (cart), date = VALUES (date)';

		$db->setQuery($query);
		$db->execute();*/
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

				$app							= JFactory::getApplication();
				$paramsC 						= PhocacartUtils::getComponentParameters();
				$tax_calculation				= $paramsC->get( 'tax_calculation', 0 );
				$check_product_attributes		= $paramsC->get( 'check_product_attributes', array(3) );


				$price	= new PhocacartPrice();
				$calc 	= new PhocacartCartCalculation();
				$calc->setType($this->type);


				// CHECK ACCESS OF ALL ITEMS IN CART, CHECK IF ATTRIBUTES STILL EXIST
				// (e.g. when someone open his/her cart after product changes were made
				foreach($this->items as $k => $v) {
					$item 	= explode(':', $k);
					$itemId = $item[0];

					// CHECK PRODUCT
					$checkP = PhocacartProduct::checkIfAccessPossible((int)$itemId, (int)$v['catid'], $this->type);

					if (!$checkP){
						$app->enqueueMessage(
							JText::_('COM_PHOCACART_ERROR_PRODUCT_STORED_IN_CART_NOT_EXISTS'). ' '
							. JText::_('COM_PHOCACART_ERROR_PRODUCT_REMOVED_FROM_CART'). ' '
							. JText::_('COM_PHOCACART_PLEASE_RECHECK_PRODUCTS_IN_YOUR_CART'), 'error');
						unset($this->items[$k]);
						$this->updateItemsFromCheckout($k, 0);
						// In case this all happens when order is made - stop the order and inform user
						$this->updateProductsRemoved($k);

					} else {
						// Product access is OK - product still in cart, check the attributes and options

						// ATTRIBUTE AND OPTIONS CHECK
						// Check if attributes and options of stored products in cart are available (no change between ordering)
						// Takes a lot of resources, so it will be checked when making an order as default ($check_product_attributes = 3)
						// ATTRIBUTES
						if (in_array($this->instance, $check_product_attributes)) {

							$attribs = array();
							if (!empty($item[1])) {
								$attribs = unserialize(base64_decode($item[1]));
							}
							$checkA = PhocacartProduct::checkIfProductAttributesOptionsExist((int)$itemId, $k, (int)$v['catid'], $this->type, $attribs);
							if (!$checkA){
								$app->enqueueMessage(
									JText::_('COM_PHOCACART_ERROR_ATTRIBUTE_OF_PRODUCT_STORED_IN_CART_NOT_EXISTS'). ' '
									. JText::_('COM_PHOCACART_ERROR_PRODUCT_REMOVED_FROM_CART'). ' '
									. JText::_('COM_PHOCACART_PLEASE_RECHECK_PRODUCTS_IN_YOUR_CART'), 'error');
								unset($this->items[$k]);
								$this->updateItemsFromCheckout($k, 0);
								// In case this all happens when order is made - stop the order and inform user
								$this->updateProductsRemoved($k);

							}
						}
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
				$this->total[0] 			= $this->total[4]			= $this->total[3] 	= $this->total[2];


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

				$this->fullitems[0] 		= $this->fullitems[4] 		= $this->fullitems[3];
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4] 	= $this->fullitemsgroup[3];
				$this->total[0] 			= $this->total[4]			= $this->total[3];





				// Subtotal after 3) Discount
				$this->total[3]['dnetto']	= $this->total[2]['netto'] - $this->total[3]['netto'];
				$this->total[3]['dbrutto']	= $this->total[2]['brutto'] - $this->total[3]['brutto'];

				$calc->roundFixedAmountDiscount($this->total[3]);// Last because now we know the dnetto


				// --------------------
				// 4) Cart Coupon
				// --------------------

				$calc->calculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4], $this->coupon);

				// 4b) Cart Coupon - we need to divide fixed amount coupon into products which meets the rules to get each coupon
				if (!empty($this->total[4]['couponcartfixedamount'])) {
					$calc->recalculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4]);
				}

				$this->fullitems[0] 		= $this->fullitems[4];
				$this->fullitemsgroup[0] 	= $this->fullitemsgroup[4];
				$this->total[0] 			= $this->total[4];

				// Subtotal after 4) Coupon
				$this->total[4]['dnetto']	= $this->total[3]['netto'] - $this->total[4]['netto'];
				$this->total[4]['dbrutto']	= $this->total[3]['brutto'] - $this->total[4]['brutto'];

				$calc->roundFixedAmountCoupon($this->total[4]);



				//Subtotal after all discounts
				$this->total[0]['wdnetto'] = $this->total[1]['netto'] - $this->total[5]['dnetto'] - $this->total[2]['dnetto'] - $this->total[3]['dnetto'] - $this->total[4]['dnetto'];
				//$this->total[0]['subtotalafterdiscounts'] = $this->total[0]['netto'] - $this->total[5]['dnetto'] - $this->total[2]['dnetto'] - $this->total[3]['dnetto'] - $this->total[4]['dnetto'];


				//$calc->taxRecapitulation($this->total[0]);

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

	/**
	 * This is a final cart function
	 * It is called roundTotalAmount() because of backward compatibility
	 * but is the place to do last checks in cart
	 *
	 */

	public function roundTotalAmount() {


		$calc 						= new PhocacartCartCalculation();
		$calc->setType($this->type);
		$this->shipping['costs'] 	= isset($this->shipping['costs']) ? $this->shipping['costs'] : 0;
		$this->payment['costs'] 	= isset($this->payment['costs']) ? $this->payment['costs'] : 0;


		// 1) CORRECT TOTAL ITEMS (Rounding), CORRECT CURRENCY TOTAL ITEMS (Rounding for each item)
		$calc->correctTotalItems($this->total, $this->shipping['costs'], $this->payment['costs']);
		// 2) MAKE TAX RECAPITULATION and correct total by tax recapitulation if asked
		$calc->taxRecapitulation($this->total[0], $this->shipping['costs'], $this->payment['costs']);
		// 3) CORRECT TOTAL ITEMS (Rounding), CORRECT CURRENCY TOTAL ITEMS (Rounding for each item) - AGAIN WHEN TOTAL CHANGED BY TAX RECAPITULATION
		$options = array();

		$options['brutto_currency_set'] = 1; // Brutto currency exists yet, so don't create it again from "brutto * currencyRating"
		$calc->correctTotalItems($this->total, $this->shipping['costs'], $this->payment['costs'], $options);
		// 4) ROUND TOTAL AMOUNT IF ASKED (e.g. 95.67 => 96)


		$calc->roundTotalAmount($this->total[0]);



		// ROUNDING VS: TRCROUNDING
		//          	NETTO		(Payment,Shipping incl. Tax)	Rounding	Brutto
		// Rounding TC:	1370.79 + 	25.94 + 						0.14 = 		1396,87
		// Rounding: 	1370.83 + 	25.94 + 						0.10 = 		1396,87

		// 0.14 - 0.10 = 0.04
		//				1370.79 + 0.10 + 0.04 + 25.94 = 1396,87
	}

	public function getTaxRecapitulation() {

		if(!empty($this->total[0]['taxrecapitulation'])) {
			return $this->total[0]['taxrecapitulation'];
		}
		return false;
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
			$paymentNew->setType($this->type);
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


	public function getShippingMethod() {



		$shipping = array();
		$shipping['title'] 	= $this->shipping['title'];
		$shipping['method'] = $this->shipping['method'];
		$shipping['id']		= $this->shipping['id'];



		// E.g. guest checkout
		if (isset($shipping['id']) && (int)$shipping['id'] > 0 && $shipping['title'] == '' && $shipping['method'] == '') {
			$shippingNew	= new PhocacartShipping();
			$shippingNew->setType($this->type);
			$pI	= $shippingNew->getShippingMethod((int)$shipping['id']);
			if (isset($pI->title)) {
				$shipping['title'] 	= $pI->title;
			}
			if (isset($pI->method)) {
				$shipping['method'] = $pI->method;
			}
		}

		return $shipping;
	}

	public function getShippingId() {
		return $this->shipping['id'];
	}

	public function getPaymentId() {
		return $this->payment['id'];
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
		return isset($this->shipping['costs']) ? $this->shipping['costs'] : false;
	}

	public function getPaymentCosts() {

		return isset($this->payment['costs']) ? $this->payment['costs'] : false;
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

	public function getProductsRemoved() {
		return $this->removedproducts;
	}

	public function updateProductsRemoved($k) {
		$this->removedproducts[] = $k;
	}

	public function getVendorId() {
		if (isset($this->vendor->id) && (int)$this->vendor->id > 0) {
			return $this->vendor->id;
		} else {
			return 0;
		}
	}
	public function getTicketId() {
		if (isset($this->ticket->id) && (int)$this->ticket->id > 0) {
			return $this->ticket->id;
		} else {
			return 0;
		}
	}
	public function getUnitId() {
		if (isset($this->unit->id) && (int)$this->unit->id > 0) {
			return $this->unit->id;
		} else {
			return 0;
		}
	}
	public function getSectionId() {
		if (isset($this->section->id) && (int)$this->section->id > 0) {
			return $this->section->id;
		} else {
			return 0;
		}
	}

	public function getLoyaltyCartNumber() {
		return $this->loyalty_card_number;
	}

	public function addShippingCosts($shippingId = 0) {



		//$app = JFactory::getApplication();
		if ($shippingId == 0) {
			$shippingId = $this->shipping['id'];
		}

		$shipping	= new PhocacartShipping();

		$shipping->setType($this->type);
		$sI	= $shipping->getShippingMethod((int)$shippingId);


		$shippingValid 	= $shipping->checkAndGetShippingMethodInsideCart((int)$shippingId, $this->total[0]);
		if (!$shippingValid) {
			PhocacartShipping::removeShipping();// In case user has in cart shipping method which does not exists
			PhocacartPayment::removePayment();// It does not remove payment immediately (but after reload) or when ordering (order tests the conditions)
			//$app->enqueueMessage(JText::_('COM_PHOCACART_NO_SHIPPING_METHOD_FOUND'));
			unset($sI);
		}


		if(!empty($sI)) {

			$sI->freeshipping = 0;

			if ($this->total[0]['free_shipping'] == 1) {
				$sI->freeshipping = 1;
			}

			$price	= new PhocacartPrice();
			$priceI = $price->getPriceItemsShipping($sI->cost, $sI->cost_additional, $sI->calculation_type, $this->total[0], $sI->taxid, $sI->taxrate, $sI->taxcalculationtype, $sI->taxtitle, $sI->freeshipping, 1);

			// CALCULATION
			$calc 						= new PhocacartCartCalculation();
			$calc->setType($this->type);
			$this->shipping['costs'] 	= $priceI;

			if ($this->total[0]['free_shipping'] != 1) {

				$this->shipping['costs']['id'] 		= $sI->id;
				$this->shipping['costs']['title'] 		= $sI->title;
				$this->shipping['costs']['title_lang'] 		= $sI->title;
				$this->shipping['costs']['title_lang_suffix'] 		= '';
				$this->shipping['costs']['title_lang_suffix2'] 		= '';
				$this->shipping['costs']['description'] = $sI->description;
				$this->shipping['costs']['image'] 		= $sI->image;

				// Update even the shipping info
				$this->shipping['id'] = $sI->id;
				$this->shipping['title'] = $sI->title;
				$this->shipping['method'] = $sI->method;
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
		$payment->setType($this->type);
		$pI	= $payment->getPaymentMethod((int)$paymentId);

		$shippingId		= 0;
		if (isset($this->shipping['id']) && (int)$this->shipping['id'] > 0) {
			$shippingId		= $this->shipping['id'];
		} else if (isset($this->shipping['costs']['id']) && (int)$this->shipping['costs']['id'] > 0) {
			$shippingId		= $this->shipping['costs']['id'];
		}

		$paymentValid 	= $payment->checkAndGetPaymentMethodInsideCart((int)$paymentId, $this->total[0], $shippingId);

		if (!$paymentValid) {
			PhocacartPayment::removePayment();// In case user has in cart payment method which does not exists

			// Remove Shipping and Payment when updated
			unset($pI);
		}

		if(!empty($pI)) {

			$pI->freepayment = 0;

			if (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] == 1) {
				$pI->freepayment = 1;
			}

			$price	= new PhocacartPrice();
			$priceI = $price->getPriceItemsPayment($pI->cost, $pI->cost_additional, $pI->calculation_type, $this->total[0], $pI->taxid, $pI->taxrate, $pI->taxcalculationtype, $pI->taxtitle, $pI->freepayment, 1);


			// CALCULATION
			$calc 						= new PhocacartCartCalculation();
			$calc->setType($this->type);
			$this->payment['costs'] 	= $priceI;

			if (!isset($this->total[0]['free_payment']) || (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] != 1)) {
				$this->payment['costs']['id'] 			= $pI->id;
				$this->payment['costs']['title'] 		= $pI->title;
				$this->payment['costs']['title_lang'] 		= $pI->title;
				$this->payment['costs']['title_lang_suffix'] 		= '';
				$this->payment['costs']['title_lang_suffix2'] 		= '';
				$this->payment['costs']['description'] 	= $pI->description;
				$this->payment['costs']['image'] 		= $pI->image;

				// Update even the shipping info
				$this->payment['id'] = $pI->id;
				$this->payment['title'] = $pI->title;
				$this->payment['method'] = $pI->method;
			}
			$calc->calculatePayment($priceI, $this->total[0]);
			//$calc->round($this->total[0], 0);

		}
	}

	public function emptyCart() {
		$session 		= JFactory::getSession();
		$session->set('cart', array(), 'phocaCart');
		//if((int)$this->user->id > 0) {
			// this function to empty cart database is not use in POS, so always set ticketid, unitid and sectionid to 1
			//PhocacartCartDb::emptyCartDb((int)$this->user->id);
		//}

		PhocacartCartDb::emptyCartDb((int)$this->user->id, (int)$this->vendor->id, (int)$this->ticket->id, (int)$this->unit->id, (int)$this->section->id);
	}

	public function getCartCountItems() {


		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}

		$count = 0;
		if (!empty($this->fullitems[0])) {
			foreach($this->fullitems[0] as $k => $v) {
				if (isset($v['quantity']) && (int)$v['quantity'] > 0) {
					$count += (int)$v['quantity'];
				}
			}
		}
		return $count;
	}


	public function getCartTotalItems() {

		// SUBTOTAL
		if (empty($this->total)) {
			$this->total = $this->getTotal();
		}

		// COUPONTITLE
		if (empty($this->coupontitle)) {
			$this->coupon['title'] = $this->getCouponTitle();
		}
		return $this->total;
	}


}
?>
