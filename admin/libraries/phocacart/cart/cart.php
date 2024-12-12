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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

class PhocacartCart
{
    protected $items = array();
    protected $fullitems = array();
    protected $fullitemsgroup = array();// Group of one product: Product A (Option A) + Product A (Option B)
    protected $user = array();
    protected $vendor = array();
    protected $ticket = array();
    protected $unit = array();
    protected $section = array();
    protected $loyalty_card_number = '';
    protected $total = array();


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

    protected $coupon = array();
    protected $reward = array();
    protected $cartdiscount = array();
    protected $shipping = array();
    protected $payment = array();

    protected $stock = array();
    protected $minqty = array();
    protected $minmultipleqty = array();
    protected $pos = false;
    protected $type = array(0, 1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

    protected $removedproducts = array(); // Products which not more exit (or their attributes/options) are removed
    // if they were not removed previously - so e.g. when making an order
    // the order will be not placed by user will get error message
    protected $guest = 0;
    protected $instance = 1; // 1.cart 2.checkout 3.order

    public function __construct() {


        $app         = Factory::getApplication();
        $session     = Factory::getSession();
        $dUser       = PhocacartUser::defineUser($this->user, $this->vendor, $this->ticket, $this->unit, $this->section);
        $this->guest = PhocacartUserGuestuser::getGuestUser();



        $this->pos = PhocacartPos::isPos();

        $this->coupon['id']                = 0;
        $this->coupon['title']             = '';
        $this->coupon['valid']             = 0;
        $this->coupon['code']              = '';
        $this->reward['used']              = '';
        $this->cartdiscount['id']          = 0;
        $this->cartdiscount['title']       = '';
        $this->shipping['id']              = 0;
        $this->shipping['title']           = '';
        $this->shipping['method']          = '';
        $this->shipping['costs']           = 0;
        $this->shipping['image']           = '';
        $this->shipping['params_shipping'] = array();// For example shipping branch info
        $this->payment['id']               = 0;
        $this->payment['title']            = '';
        $this->payment['method']           = '';
        $this->payment['costs']            = 0;
        $this->payment['calculation_type'] = 0;
        $this->payment['image']            = '';
        $this->payment['params_payment']    = array();
        $this->stock['valid']              = 1;// check stock - products, attributes (no matter if stock checking is disabled or enabled)
        $this->minqty['valid']             = 1;// check minimum order quantity
        $this->minmultipleqty['valid']     = 1;// check minimum multiple order quantity


        // Admin info - Administrator asks for information about user's cart
        if ($app->getName() == 'administrator') {
            $userid                        = $app->input->get('userid', 0, 'int');
            $vendorid                  = $app->input->get('vendorid', 0, 'int');
            $ticketid                  = $app->input->get('ticketid', 0, 'int');
            $unitid                    = $app->input->get('unitid', 0, 'int');
            $sectionid                 = $app->input->get('sectionid', 0, 'int');
            $cartDb                    = PhocacartCartDb::getCartDb($userid, $vendorid, $ticketid, $unitid, $sectionid);
            $this->items               = $cartDb['cart'];
            $this->coupon['id']        = $cartDb['coupon'];
            $this->coupon['title']     = $cartDb['coupontitle'];
            $this->coupon['code']      = $cartDb['couponcode'];
            $this->shipping['id']      = $cartDb['shipping'];
            $this->shipping['title']   = $cartDb['shippingtitle'];
            $this->shipping['method']  = $cartDb['shippingmethod'];
            $this->shipping['image']   = $cartDb['shippingimage'];
            $this->shipping['params_shipping']   = $cartDb['params_shipping'];
            $this->payment['id']       = $cartDb['payment'];
            $this->payment['title']    = $cartDb['paymenttitle'];
            $this->payment['method']   = $cartDb['paymentmethod'];
            $this->payment['image']    = $cartDb['paymentimage'];
            $this->payment['params_payment']   = $cartDb['params_payment'];
            $this->reward['used']      = $cartDb['reward'];
            $this->loyalty_card_number = $cartDb['loyalty_card_number'];

            return;

        }

        // POS

        if ($this->pos && (int)$this->vendor->id > 0) {

            $cartDb = PhocacartCartDb::getCartDb((int)$this->user->id, (int)$this->vendor->id, (int)$this->ticket->id, (int)$this->unit->id, (int)$this->section->id);

            $this->items               = $cartDb['cart'];
            $this->coupon['id']        = $cartDb['coupon'];
            $this->coupon['title']     = $cartDb['coupontitle'];
            $this->coupon['code']      = $cartDb['couponcode'];
            $this->shipping['id']      = $cartDb['shipping'];
            $this->shipping['title']   = $cartDb['shippingtitle'];
            $this->shipping['method']  = $cartDb['shippingmethod'];
            $this->shipping['image']   = $cartDb['shippingimage'];
            $this->shipping['params_shipping']   = $cartDb['params_shipping'];
            $this->payment['id']       = $cartDb['payment'];
            $this->payment['title']    = $cartDb['paymenttitle'];
            $this->payment['method']   = $cartDb['paymentmethod'];
            $this->payment['image']    = $cartDb['paymentimage'];
            $this->payment['params_payment']   = $cartDb['params_payment'];
            $this->reward['used']      = $cartDb['reward'];
            $this->loyalty_card_number = $cartDb['loyalty_card_number'];


            // Don't care about session (use in session is customer, user in pos in db is vendor)


        } else if ((int)$this->user->id > 0) {
            // DATABASE - logged in user - Singleton because of not load data from database every time cart instance is loaded
            // 1. Not found in DATABASE - maybe user logged in now, so:
            // 2. We try to find the data in SESSION, if they are still in SESSION - load them to our cart class and then
            // 3. Store them to DATABASE as all loged in users have cart in database and:
            // 4. Remove them from SESSION as they are stored in DATABASE
            $cartDb = PhocacartCartDb::getCartDb((int)$this->user->id);// user logged in - try to get cart from db

            $this->items = $cartDb['cart'];

            $this->coupon['id']        = $cartDb['coupon'];
            $this->coupon['title']     = $cartDb['coupontitle'];
            $this->coupon['code']      = $cartDb['couponcode'];
            $this->shipping['id']      = $cartDb['shipping'];
            $this->shipping['title']   = $cartDb['shippingtitle'];
            $this->shipping['method']  = $cartDb['shippingmethod'];
            $this->shipping['image']   = $cartDb['shippingimage'];
            $this->shipping['params_shipping']   = $cartDb['params_shipping'];
            $this->payment['id']       = $cartDb['payment'];
            $this->payment['title']    = $cartDb['paymenttitle'];
            $this->payment['method']   = $cartDb['paymentmethod'];
            $this->payment['image']    = $cartDb['paymentimage'];
            $this->payment['params_payment']   = $cartDb['params_payment'];
            $this->reward['used']      = $cartDb['reward'];
            $this->loyalty_card_number = $cartDb['loyalty_card_number'];
            $sessionItems              = $session->get('cart', array(), 'phocaCart');


            if (empty($this->items)) {
                $this->items = $session->get('cart', array(), 'phocaCart');
                // COUPONMOVE - we can move the cart items to logged in user or guest checkout, so we do with coupon
                $this->coupon['id'] = $session->get('guestcoupon', false, 'phocaCart');
                if ((int)$this->coupon['id'] > 0) {
                    $couponInfo            = PhocacartCoupon::getCouponInfoById($this->coupon['id']);
                    $this->coupon['title'] = isset($couponInfo->title) ? $couponInfo->title : '';
                    $this->coupon['code']  = isset($couponInfo->code) ? $couponInfo->code : '';
                }

                if (!empty($this->items)) {
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
                    $session->set('guestcoupon', array(), 'phocaCart');// guestcoupon is variable for both: for still not logged in user or someone who enabled guest checkout
                }
            } else {
                // we have stored items in database from previously
                // and we have stored items in session now
                if (!empty($sessionItems)) {
                    // inform users and clean session
                    $message = Text::_('COM_PHOCACART_CART_UPDATED_BASED_ON_YOUR_PREVIOUS_VISIT');
                    $app->enqueueMessage($message, 'message');
                    $session->set('cart', array(), 'phocaCart');
                    $session->set('guestcoupon', array(), 'phocaCart');
                }

            }
        } else if ($this->guest) {
            $this->items = $session->get('cart', array(), 'phocaCart');

            $this->shipping['id'] = $session->get('guestshipping', false, 'phocaCart');

            $this->shipping['params_shipping'] = $session->get('guestshippingparams', false, 'phocaCart');


            if ((int)$this->shipping['id'] > 0) {
                $shippingObject = new PhocacartShipping();
                $shippingObject->setType($this->type);
                $shippingInfo             = $shippingObject->getShippingMethod((int)$this->shipping['id']);
                $this->shipping['title']  = isset($shippingInfo->title) ? $shippingInfo->title : '';
                $this->shipping['method'] = isset($shippingInfo->method) ? $shippingInfo->method : '';
                $this->shipping['image']  = isset($shippingInfo->image) ? $shippingInfo->image : '';
            }

            $this->payment['id'] = $session->get('guestpayment', false, 'phocaCart');
            $this->payment['params_payment'] = $session->get('guestpaymentparams', false, 'phocaCart');
            if ((int)$this->payment['id'] > 0) {
                $paymentObject = new PhocacartPayment();
                $paymentObject->setType($this->type);
                $paymentInfo             = $paymentObject->getPaymentMethod((int)$this->payment['id']);
                $this->payment['title']  = isset($paymentInfo->title) ? $paymentInfo->title : '';
                $this->payment['method'] = isset($paymentInfo->method) ? $paymentInfo->method : '';
                $this->payment['image']  = isset($paymentInfo->image) ? $paymentInfo->image : '';
            }

            $this->coupon['id'] = $session->get('guestcoupon', false, 'phocaCart');// COUPONMOVE
            if ((int)$this->coupon['id'] > 0) {
                $couponInfo            = PhocacartCoupon::getCouponInfoById($this->coupon['id']);
                $this->coupon['title'] = isset($couponInfo->title) ? $couponInfo->title : '';
                $this->coupon['code']  = isset($couponInfo->code) ? $couponInfo->code : '';
            }

            $this->loyalty_card_number = $session->get('guestloyaltycardnumber', false, 'phocaCart');
        } else {
            // SESSION - not logged in user
            $this->items        = $session->get('cart', array(), 'phocaCart');
            $this->coupon['id'] = $session->get('guestcoupon', false, 'phocaCart');// COUPONMOVE
            if ((int)$this->coupon['id'] > 0) {
                $couponInfo            = PhocacartCoupon::getCouponInfoById($this->coupon['id']);
                $this->coupon['title'] = isset($couponInfo->title) ? $couponInfo->title : '';
                $this->coupon['code']  = isset($couponInfo->code) ? $couponInfo->code : '';
            }
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
    public function setType($type = array(0, 1)) {

        $this->type = $type;
    }

    /*
     * Catid is only for information from which place the product was added to the cart
     * When making order, this will be recheck
     */
    public function addItems($id = 0, $catid = 0, $quantity = 0, $attributes = array(), $idKey = '') {

        $app = Factory::getApplication();

        if ($idKey != '') {
            // we get idkey as string - from checkout update or remove -  used in CHECKOUT
        } else {
            // we get id as int standard id of product - attributes can be listed in form - used in CATEGORY, ITEM, ITEMS
            //- $k = (int)$id . ':';



            $checkP = PhocacartProduct::checkIfAccessPossible($id, $catid, $this->type);

            if (!$checkP) {
                //$uri 			= Uri::getInstance();
                //$action			= $uri->toString();

                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_NO_RIGHTS_FOR_ORDERING_PRODUCT'), 'error');
                return false;
            }

            // Check if there is required attribute
            // In fact this should not happen in item Online Shop view, as this should be checked per html5 checking form
            // This is security check on server side - e.g. when testing attributes in items or category view
            // But in POS EAN and SKU can be added with help of Advanced stock management so check direct adding of SKU or EAN

            $checkedA = PhocacartAttribute::checkRequiredAttributes($id, $attributes);


            if (!$checkedA) {
                //$uri 			= Uri::getInstance();
                //$action			= $uri->toString();

                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_SELECTING_ATTRIBUTE_IS_REQUIRED'), 'error');


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
            if (isset($this->items[$k]['quantity']) && (int)$this->items[$k]['quantity'] > 0) {
                $oldQuantity = $this->items[$k]['quantity'];
                $this->items[$k]['quantity'] = $this->items[$k]['quantity'] + (int)$quantity;
            } else {
                $oldQuantity = 0;
                $this->items[$k]['quantity'] = (int)$quantity;
            }

            $this->updateItems($k, $this->items[$k], $oldQuantity, $this->items[$k]['quantity']);

            //$this->updateSubTotal();
            return true;
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART_QUANTITY_WAS_NOT_SET'), 'error');
        }
        return false;
    }


    /*
     * UPDATE - public function to update from CHECKOUT (update, remove buttons)
     */
    public function updateItemsFromCheckout($idKey = '', $quantity = 0) {

        // Don't check for quantity as it can be NULL
        if ($idKey != '') {
            if (isset($this->items[$idKey])) {

                $oldQuantity = $this->items[$idKey]['quantity'];

                if ((int)$quantity > 0) {
                    $this->items[$idKey]['quantity'] = (int)$quantity;
                    $newQuantity                     = $quantity;
                } else {
                    unset($this->items[$idKey]);
                    $newQuantity = 0;
                }

                if (!isset($this->items[$idKey])) {
                    // it was unset
                    //$this->updateItems($idKey, [], $oldQuantity, $newQuantity);
                    $this->updateItems($idKey, null, $oldQuantity, $newQuantity);
                } else {
                    $this->updateItems($idKey, $this->items[$idKey], $oldQuantity, $newQuantity);
                }

                return true;
            }
        }

        return false;
    }


    /*
     * UPDATE - protected internal function to update session or database
     */
    protected function updateItems(string $idKey, ?array $item, ?int $quantityOld, int $quantityNew) {

        $session = Factory::getSession();

        if ($this->pos && (int)$this->vendor->id > 0) {

            $this->updateItemsDb();

        } else if ((int)$this->user->id > 0) {

            $this->updateItemsDb();
            $session->set('cart', array(), 'phocaCart');

        } else {

            $session->set('cart', $this->items, 'phocaCart');
        }

        $this->updateShipping();
        $this->updatePayment();

        Dispatcher::dispatch(new Event\View\Cart\UpdateItems($idKey, $item, $quantityOld, $quantityNew));
    }

    /*
     * Check if after update shipping and payment method is still valid
     * If not remove it
     * If set in parameter to yes, remove it even it is valid
     */

    public function updateShipping() {


        $paramsC                       = PhocacartUtils::getComponentParameters();
        $change_remove_shipping_method = $paramsC->get('change_remove_shipping_method', 1);

        $currentShippingId = isset($this->shipping['id']) && $this->shipping['id'] > 0 ? (int)$this->shipping['id'] : 0;

        if ($this->pos && (int)$this->vendor->id > 0) {
            $typeUser = 0;
        } else if ((int)$this->user->id > 0) {
            $typeUser = 0;
        } else {
            $typeUser = 1;
        }


        // $change_remove_shipping_method is not a POS parameter
        // In POS we don't remove shpping or payment a priori but we always validate it
        if (!$this->pos && $change_remove_shipping_method == 1) {
            PhocacartShipping::removeShipping($typeUser);
            return;
        }

        if (!isset($this->total[0])) {
            $this->total[0] = array();
        }

        $isValidShipping = false;
        $shippingObject  = new PhocacartShipping();
        $shippingObject->setType($this->type);
        if ($currentShippingId > 0) {
            $isValidShipping = $shippingObject->checkAndGetShippingMethod((int)$currentShippingId, $this->total[0]);
        }

        if (!$isValidShipping) {
            PhocacartShipping::removeShipping($typeUser);

        }
    }

    public function updatePayment($shippingId = 0) {

        $paramsC                      = PhocacartUtils::getComponentParameters();
        $change_remove_payment_method = $paramsC->get('change_remove_payment_method', 1);


        $removeCoupon = 0;

        if ((int)$shippingId > 0) {

            // When we store shipping into database and immediately asking shipping id from database, we get empty result
            // this is why we need to know shipping ID here

            $currentShippingId = (int)$shippingId;
        } else {
            $currentShippingId = isset($this->shipping['id']) && $this->shipping['id'] > 0 ? (int)$this->shipping['id'] : 0;
        }

        $currentPaymentId = isset($this->payment['id']) && $this->payment['id'] > 0 ? (int)$this->payment['id'] : 0;

        if ($this->pos && (int)$this->vendor->id > 0) {
            $typeUser = 0;
        } else if ((int)$this->user->id > 0) {
            $typeUser = 0;
        } else {
            $typeUser = 1;
        }

        // $change_remove_payment_method is not a POS parameter
        // In POS we don't remove shpping or payment a priori but we always validate it
        if (!$this->pos && $change_remove_payment_method == 1) {
            PhocacartPayment::removePayment($typeUser, $removeCoupon);
            return;
        }

        if (!isset($this->total[0])) {
            $this->total[0] = array();
        }

        $isValidPayment = false;
        $paymentObject  = new PhocacartPayment();
        $paymentObject->setType($this->type);
        if ($currentPaymentId > 0) {
            $isValidPayment = $paymentObject->checkAndGetPaymentMethod((int)$currentPaymentId, $this->total[0], (int)$currentShippingId);
        }

        if (!$isValidPayment) {
            PhocacartPayment::removePayment($typeUser, $removeCoupon);
        }
    }


    /*
     * UPDATE - protected internal function to update only database
     */
    protected function updateItemsDb() {

        $db    = Factory::getDBO();
        $items = serialize($this->items);
        $date  = Factory::getDate();
        $now   = $date->toSql();

        // Update multiple cart (include vendor, ticket)
        $query = ' SELECT user_id, vendor_id, ticket_id, unit_id, section_id FROM #__phocacart_cart_multiple'
            . ' WHERE user_id = ' . (int)$this->user->id
            . ' AND vendor_id = ' . (int)$this->vendor->id
            . ' AND ticket_id = ' . (int)$this->ticket->id
            . ' AND unit_id = ' . (int)$this->unit->id
            . ' AND section_id = ' . (int)$this->section->id
            . ' ORDER BY user_id LIMIT 1';
        $db->setQuery($query);
        $result = $db->loadRow();

        if (!empty($result)) {

            $query = 'UPDATE #__phocacart_cart_multiple'
                . ' SET cart = ' . $db->quote($items) . ','
                . ' coupon = ' . (int)$this->coupon['id'] . ',' // COUPONMOVE
                . ' date = ' . $db->quote($now)
                . ' WHERE user_id = ' . (int)$this->user->id
                . ' AND vendor_id = ' . (int)$this->vendor->id
                . ' AND ticket_id = ' . (int)$this->ticket->id
                . ' AND unit_id = ' . (int)$this->unit->id
                . ' AND section_id = ' . (int)$this->section->id;


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
                // COUPONMOVE
                $query = 'INSERT INTO #__phocacart_cart_multiple (user_id, vendor_id, ticket_id, unit_id, section_id, cart, coupon, date)'
                    . ' VALUES (' . (int)$this->user->id . ', ' . (int)$this->vendor->id . ', ' . (int)$this->ticket->id . ', ' . (int)$this->unit->id . ', ' . (int)$this->section->id . ', ' . $db->quote($items) . ', ' . (int)$this->coupon['id'] . ', ' . $db->quote($now) . ');';
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
            if (!empty($this->items)) {

                $app                      = Factory::getApplication();
                $paramsC                  = PhocacartUtils::getComponentParameters();
                $tax_calculation          = $paramsC->get('tax_calculation', 0);
                $check_product_attributes = $paramsC->get('check_product_attributes', array(3));


                $price = new PhocacartPrice();
                $calc  = new PhocacartCartCalculation();
                $calc->setType($this->type);


                // CHECK ACCESS OF ALL ITEMS IN CART, CHECK IF ATTRIBUTES STILL EXIST
                // (e.g. when someone open his/her cart after product changes were made
                foreach ($this->items as $k => $v) {
                    $item   = explode(':', $k);
                    $itemId = $item[0];

                    // CHECK PRODUCT
                    $checkP = PhocacartProduct::checkIfAccessPossible((int)$itemId, (int)$v['catid'], $this->type);

                    if (!$checkP) {

                        if ($app->getName() == 'administrator') {
                            $app->enqueueMessage(
                            Text::_('COM_PHOCACART_ERROR_PRODUCT_STORED_IN_CUSTOMER_CART_NOT_EXISTS') . ' '
                            . Text::_('COM_PHOCACART_ERROR_PRODUCT_REMOVED_FROM_CUSTOMER_CART') . ' '
                            . Text::_('COM_PHOCACART_CUSTOMER_WILL_BE_INFORMED_OF_SITUATION_DURING_NEXT_VISIT_TO_STORE'), 'warning');
                        } else {
                            $app->enqueueMessage(
                            Text::_('COM_PHOCACART_ERROR_PRODUCT_STORED_IN_CART_NOT_EXISTS') . ' '
                            . Text::_('COM_PHOCACART_ERROR_PRODUCT_REMOVED_FROM_CART') . ' '
                            . Text::_('COM_PHOCACART_PLEASE_RECHECK_PRODUCTS_IN_YOUR_CART'), 'error');
                        }



                        $this->updateItemsFromCheckout($k, 0);
                        unset($this->items[$k]);

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
                            if (!$checkA) {
                                $app->enqueueMessage(
                                    Text::_('COM_PHOCACART_ERROR_ATTRIBUTE_OF_PRODUCT_STORED_IN_CART_NOT_EXISTS') . ' '
                                    . Text::_('COM_PHOCACART_ERROR_PRODUCT_REMOVED_FROM_CART') . ' '
                                    . Text::_('COM_PHOCACART_PLEASE_RECHECK_PRODUCTS_IN_YOUR_CART'), 'error');

                                $this->updateItemsFromCheckout($k, 0);
                                unset($this->items[$k]);
                                // In case this all happens when order is made - stop the order and inform user
                                $this->updateProductsRemoved($k);

                            }
                        }
                    }


                }
                if (empty($this->items)) {
                    return false;
                }
                // END ACCESS

                // --------------------
                // 1) Basic Calculation
                // --------------------
                $calc->calculateBasicProducts($this->fullitems[1], $this->fullitemsgroup[1], $this->total[1], $this->stock, $this->minqty, $this->minmultipleqty, $this->items);

                $options = PhocaCartUtils::getComponentParameters();
                if ($options->get('checkout_separate_by_owner')) {
                    usort($this->fullitems[1], function($a, $b) {
                        switch (true) {
                            case $a['owner_id'] === $b['owner_id']: return 0;
                            case !$a['owner_id']: return 1;
                            case !$b['owner_id']: return -1;
                            case $a['owner_ordering'] === $b['owner_ordering']: return strcmp($a['owner_name'], $b['owner_name']);
                            default: return $a['owner_ordering'] < $b['owner_ordering'] ? -1 : 1;
                        }
                    });
                }

                //$calc->round($this->total[1]);

                // Fixed subtotal amount
                $this->total[1]['subtotalnetto'] = $this->total[1]['netto'];
                $this->total[1]['subtotalbrutto'] = $this->total[1]['brutto'];

                $this->fullitems[0] = $this->fullitems[4] = $this->fullitems[3] = $this->fullitems[2]
                    = $this->fullitems[5] = $this->fullitems[1];

                $this->fullitemsgroup[0] = $this->fullitemsgroup[4] = $this->fullitemsgroup[3] = $this->fullitemsgroup[2]
                    = $this->fullitemsgroup[5] = $this->fullitemsgroup[1];

                $this->total[0] = $this->total[4] = $this->total[3] = $this->total[2]
                    = $this->total[5] = $this->total[1];

                // --------------------
                // 5) Reward Points
                // --------------------

                $calc->calculateRewardDiscounts($this->fullitems[5], $this->fullitemsgroup[5], $this->total[5], $this->reward);


                $this->fullitems[0] = $this->fullitems[4] = $this->fullitems[3] = $this->fullitems[2]
                    = $this->fullitems[5];

                $this->fullitemsgroup[0] = $this->fullitemsgroup[4] = $this->fullitemsgroup[3] = $this->fullitemsgroup[2]
                    = $this->fullitemsgroup[5];

                $this->total[0] = $this->total[4] = $this->total[3] = $this->total[2]
                    = $this->total[5];

                // Subtotal after 2) Discount
                $this->total[5]['dnetto']  = $this->total[1]['netto'] - $this->total[5]['netto'];
                $this->total[5]['dbrutto'] = $this->total[1]['brutto'] - $this->total[5]['brutto'];


                // --------------------
                // 2) Product Discount
                // --------------------
                $calc->calculateProductDiscounts($this->fullitems[2], $this->fullitemsgroup[2], $this->total[2]);

                //$calc->round($this->total[2]);

                $this->fullitems[0] = $this->fullitems[4] = $this->fullitems[3] = $this->fullitems[2];


                $this->fullitemsgroup[0] = $this->fullitemsgroup[4] = $this->fullitemsgroup[3] = $this->fullitemsgroup[2];
                $this->total[0]          = $this->total[4] = $this->total[3] = $this->total[2];


                // Subtotal after 2) Discount
                $this->total[2]['dnetto']  = $this->total[5]['netto'] - $this->total[2]['netto'];
                $this->total[2]['dbrutto'] = $this->total[5]['brutto'] - $this->total[2]['brutto'];


                // --------------------
                // 3) Cart Discount
                // --------------------


                $calc->calculateCartDiscounts($this->fullitems[3], $this->fullitemsgroup[3], $this->total[3], $this->cartdiscount);

                // 3b) Cart Discount - we need to divide fixed amount discount into products which meets the rules to get each discount
                if (!empty($this->total[3]['discountcartfixedamount'])) {
                    $calc->recalculateCartDiscounts($this->fullitems[3], $this->fullitemsgroup[3], $this->total[3]);
                }

                $this->fullitems[0]      = $this->fullitems[4] = $this->fullitems[3];
                $this->fullitemsgroup[0] = $this->fullitemsgroup[4] = $this->fullitemsgroup[3];
                $this->total[0]          = $this->total[4] = $this->total[3];


                // Subtotal after 3) Discount
                $this->total[3]['dnetto']  = $this->total[2]['netto'] - $this->total[3]['netto'];
                $this->total[3]['dbrutto'] = $this->total[2]['brutto'] - $this->total[3]['brutto'];

                $calc->roundFixedAmountDiscount($this->total[3]);// Last because now we know the dnetto


                // --------------------
                // 4) Cart Coupon
                // --------------------

                $calc->calculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4], $this->coupon);

                // 4b) Cart Coupon - we need to divide fixed amount coupon into products which meets the rules to get each coupon
                if (!empty($this->total[4]['couponcartfixedamount'])) {
                    $calc->recalculateCartCoupons($this->fullitems[4], $this->fullitemsgroup[4], $this->total[4]);
                }

                $this->fullitems[0]      = $this->fullitems[4];
                $this->fullitemsgroup[0] = $this->fullitemsgroup[4];
                $this->total[0]          = $this->total[4];

                // Subtotal after 4) Coupon
                $this->total[4]['dnetto']  = $this->total[3]['netto'] - $this->total[4]['netto'];
                $this->total[4]['dbrutto'] = $this->total[3]['brutto'] - $this->total[4]['brutto'];

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


                if ($this->coupon['code'] != '' && $this->coupon['valid'] === 0) {
                    // coupon is not valid and will be not counted
                    // but it was added to database so remove it from db
                    // E.g. user added a coupon when not logged in (e.g. he/she was in group "default"
                    // then he/she was logged in and after log in the coupon was still here but e.g.
                    // the user changed its group to "otherthandefault" and coupon does not meet rules
                    // it will be not counted in calculation but even we can remove it from db

                    // Remove it from database
                    $this->removeCouponDb();
                    // And after removing it and using the ide in instance - remove the instance
                    $this->coupon['id']    = 0;
                    $this->coupon['title'] = '';
                    $this->coupon['code']  = '';


                }


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


        $calc = new PhocacartCartCalculation();
        $calc->setType($this->type);
        $this->shipping['costs'] = isset($this->shipping['costs']) ? $this->shipping['costs'] : 0;
        $this->payment['costs']  = isset($this->payment['costs']) ? $this->payment['costs'] : 0;


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

        if (!empty($this->total[0]['taxrecapitulation'])) {
            return $this->total[0]['taxrecapitulation'];
        }
        return false;
    }

    public function getTotal() {

        $items = array('netto', 'brutto', 'subtotalnetto', 'subtotalbrutto', 'wdnetto','quantity', 'weight', 'length', 'width', 'height');
        foreach ($items as $k => $v) {
            if (!isset($this->total[0][$v])) {
                $this->total[0][$v] = 0;
            }
        }

        return $this->total;
    }

    public function getItems() {

        return $this->items;
    }

    public function getFullItems() {

        return $this->fullitems;
    }

    public function getCoupon() {

        $coupon          = array();
        $coupon['title'] = $this->coupon['title'];
        $coupon['id']    = $this->coupon['id'];

        // E.g. guest checkout
        if (isset($coupon['id']) && (int)$coupon['id'] > 0 && $coupon['title'] == '') {
            $cI = PhocacartCoupon::getCouponTitleById((int)$coupon['id']);
            if (isset($cI->title)) {
                $coupon['title'] = $cI->title;
            }
        }
        return $coupon;
    }

    public function getPaymentMethod() {


        $payment           = array();
        $payment['title']  = $this->payment['title'];
        $payment['method'] = $this->payment['method'];
        $payment['id']     = $this->payment['id'];
        $payment['image']  = $this->payment['image'];
        $payment['params_payment']  = $this->payment['params_payment'];

        // E.g. guest checkout
        if (isset($payment['id']) && (int)$payment['id'] > 0 && $payment['title'] == '' && $payment['method'] == '') {
            $paymentObject = new PhocacartPayment();
            $paymentObject->setType($this->type);
            $pI = $paymentObject->getPaymentMethod((int)$payment['id']);

            if (isset($pI->title)) {
                $payment['title'] = $pI->title;
            }
            if (isset($pI->method)) {
                $payment['method'] = $pI->method;
            }

            if (isset($pI->image)) {
                $payment['image'] = $pI->image;
            }
        }

        return $payment;
    }


    public function getShippingMethod() {


        $shipping           = array();
        $shipping['title']  = $this->shipping['title'];
        $shipping['method'] = $this->shipping['method'];
        $shipping['id']     = $this->shipping['id'];
        $shipping['image']  = $this->shipping['image'];
        $shipping['params_shipping']  = $this->shipping['params_shipping'];


        // E.g. guest checkout
        if (isset($shipping['id']) && (int)$shipping['id'] > 0 && $shipping['title'] == '' && $shipping['method'] == '') {
            $shippingObject = new PhocacartShipping();
            $shippingObject->setType($this->type);
            $sI = $shippingObject->getShippingMethod((int)$shipping['id']);
            if (isset($sI->title)) {
                $shipping['title'] = $sI->title;
            }
            if (isset($sI->method)) {
                $shipping['method'] = $sI->method;
            }

            if (isset($sI->image)) {
                $shipping['image'] = $sI->image;
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
        return isset($this->total[0]['points_needed']) ? $this->total[0]['points_needed'] : 0;
    }

    public function getRewardPointsReceived() {
        return isset($this->total[0]['points_received']) ? $this->total[0]['points_received'] : 0;
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

    public function addShippingCosts($shippingId = 0, $paymentId = 0) {


        //$app = Factory::getApplication();
        if ($shippingId == 0) {
            $shippingId = $this->shipping['id'];
        }

        $shippingObject = new PhocacartShipping();

        $shippingObject->setType($this->type);


        if (!isset($this->total[0])) {
            $this->total[0] = array();
        }

        $sI = $shippingObject->getShippingMethod((int)$shippingId, $this->total[0]);

        $shippingValid = $shippingObject->checkAndGetShippingMethod((int)$shippingId, $this->total[0]);
        if (!$shippingValid) {
            PhocacartShipping::removeShipping();// In case user has in cart shipping method which does not exists


            // Wait for payment costs
            //PhocacartPayment::removePayment();// It does not remove payment immediately (but after reload) or when ordering (order tests the conditions)
            //$app->enqueueMessage(Text::_('COM_PHOCACART_NO_SHIPPING_METHOD_FOUND'));
            unset($sI);
        }


        if (!empty($sI)) {

            $sI->freeshipping = 0;

            if (isset($this->total[0]['free_shipping']) && $this->total[0]['free_shipping'] == 1) {
                $sI->freeshipping = 1;
            }

            $price  = new PhocacartPrice();
            $priceI = $price->getPriceItemsShipping($sI->cost, $sI->cost_additional, $sI->calculation_type, $this->total[0], $sI->taxid, $sI->taxrate, $sI->taxcalculationtype, $sI->taxtitle, $sI->freeshipping, 1, 'SHIPPING_', $sI->taxhide);

            // CALCULATION
            $calc = new PhocacartCartCalculation();
            $calc->setType($this->type);
            $this->shipping['costs'] = $priceI;

            if (!isset($this->total[0]['free_shipping']) || (isset($this->total[0]['free_shipping']) && $this->total[0]['free_shipping'] != 1)) {

                $this->shipping['costs']['id']                 = $sI->id;
                $this->shipping['costs']['title']              = $sI->title;
                $this->shipping['costs']['title_lang']         = $sI->title;
                $this->shipping['costs']['title_lang_suffix']  = '';
                $this->shipping['costs']['title_lang_suffix2'] = '';
                $this->shipping['costs']['description']        = $sI->description;
                $this->shipping['costs']['image']              = $sI->image;
                $this->shipping['costs']['method']             = $sI->method;

                $this->shipping['costs']['params_shipping']    = !empty($this->shipping['params_shipping']) ? $this->shipping['params_shipping'] : array();
                $this->shipping['costs']['params']             = !empty($sI->params) ? $sI->params : null;

                // Update even the shipping info
                $this->shipping['id']     = $sI->id;
                $this->shipping['title']  = $sI->title;
                $this->shipping['method'] = $sI->method;
                $this->shipping['image']  = $sI->image;
            }

            $calc->calculateShipping($priceI, $this->total[0]);
            //$calc->round($this->total[0], 0);

        }
    }

    public function addPaymentCosts($paymentId = 0) {


        if ($paymentId == 0) {
            $paymentId = $this->payment['id'];
        }

        $paymentObject = new PhocacartPayment();
        $paymentObject->setType($this->type);
        $pI = $paymentObject->getPaymentMethod((int)$paymentId);

        $shippingId = 0;
        if (isset($this->shipping['id']) && (int)$this->shipping['id'] > 0) {
            $shippingId = $this->shipping['id'];
        } else if (isset($this->shipping['costs']['id']) && (int)$this->shipping['costs']['id'] > 0) {
            $shippingId = $this->shipping['costs']['id'];
        }

        if (!isset($this->total[0])) {
            $this->total[0] = array();
        }
        $paymentValid = $paymentObject->checkAndGetPaymentMethod((int)$paymentId, $this->total[0], $shippingId);

        if (!$paymentValid) {
            PhocacartPayment::removePayment();// In case user has in cart payment method which does not exists

            // Remove Shipping and Payment when updated
            unset($pI);
        }

        if (!empty($pI)) {

            $pI->freepayment = 0;

            if (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] == 1) {
                $pI->freepayment = 1;
            }

            $price  = new PhocacartPrice();
            $priceI = $price->getPriceItemsPayment($pI->cost, $pI->cost_additional, $pI->calculation_type, $this->total[0], $pI->taxid, $pI->taxrate, $pI->taxcalculationtype, $pI->taxtitle, $pI->freepayment, 1, 'PAYMENT_', $pI->taxhide);


            // CALCULATION
            $calc = new PhocacartCartCalculation();
            $calc->setType($this->type);
            $this->payment['costs'] = $priceI;

            if (!isset($this->total[0]['free_payment']) || (isset($this->total[0]['free_payment']) && $this->total[0]['free_payment'] != 1)) {

                $this->payment['costs']['id']                 = $pI->id;
                $this->payment['costs']['title']              = $pI->title;
                $this->payment['costs']['title_lang']         = $pI->title;
                $this->payment['costs']['title_lang_suffix']  = '';
                $this->payment['costs']['title_lang_suffix2'] = '';
                $this->payment['costs']['description']        = $pI->description;
                $this->payment['costs']['image']              = $pI->image;
                $this->payment['costs']['method']             = $pI->method;

                $this->payment['costs']['params_payment']    = !empty($this->payment['params_payment']) ? $this->payment['params_payment'] : array();
                $this->payment['costs']['params']             = !empty($pI->params) ? $pI->params : null;

                // Update even the payment info
                $this->payment['id']     = $pI->id;
                $this->payment['title']  = $pI->title;
                $this->payment['method'] = $pI->method;
                $this->payment['image']  = $pI->image;
            }
            $calc->calculatePayment($priceI, $this->total[0]);
            //$calc->round($this->total[0], 0);

        }
    }

    public function emptyCart() {
        $session = Factory::getSession();
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
            foreach ($this->fullitems[0] as $k => $v) {
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


    public function removeCouponDb() {

        $db   = Factory::getDBO();
        $date = Factory::getDate();
        $now  = $date->toSql();

        $query = 'UPDATE #__phocacart_cart_multiple'
            . ' SET coupon = ' . (int)$this->coupon['id'] . ', '
            . ' date = ' . $db->quote($now)
            . ' WHERE user_id = ' . (int)$this->user->id
            . ' AND vendor_id = ' . (int)$this->vendor->id
            . ' AND ticket_id = ' . (int)$this->ticket->id
            . ' AND unit_id = ' . (int)$this->unit->id
            . ' AND section_id = ' . (int)$this->section->id;


        $db->setQuery($query);
        $db->execute();

        return true;
    }


}

?>
