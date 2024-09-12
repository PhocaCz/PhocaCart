<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\OrderingField;

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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/tables');

class PhocacartOrder
{
    public $downloadable_product;
    public $action_after_order;
    public $message_after_order;
    public $data_after_order;
    protected $type = array(0, 1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

    public function __construct() {
        $this->downloadable_product = 0;
        // if there will be at least one downloadable file in order, we will mark it to display
        // right thank you message
        $this->action_after_order  = 1;                                                                                                                                                                                            // which action will be done after order - end, procceed to payment, ...
        $this->message_after_order = array();// custom message array made by plugin
        $this->data_after_order     = array();

    }

    public function setType($type = array(0, 1)) {
        $this->type = $type;
    }

    public function saveOrderMain($data) {


        $msgSuffix            = '<span id="ph-msg-ns" class="ph-hidden"></span>';
        $pC                   = PhocacartUtils::getComponentParameters();
        $min_order_amount     = $pC->get('min_order_amount', 0);
        $stock_checkout       = $pC->get('stock_checkout', 0);
        $stock_checking       = $pC->get('stock_checking', 0);
        $unit_weight          = $pC->get('unit_weight', '');
        $unit_volume          = $pC->get('unit_volume', '');
        $unit_size              = $pC->get('unit_size', '');
        $order_language       = $pC->get('order_language', 0);
        $skip_shipping_method = $pC->get('skip_shipping_method', 0);
        $skip_payment_method  = $pC->get('skip_payment_method', 0);




        // LANGUAGES
        $lang     = Factory::getLanguage();
        $userLang = $lang->getTag();// Get language user uses in frontend

        $pLang       = new PhocacartLanguage();
        $defaultLang = $pLang->getDefaultLanguage(0);// Get default language of frontend

        if ($order_language == 0) {
            // If the order should be stored in default language force it and and the end change it back so user get right message
            $pLang->setLanguage($defaultLang);
        }


        // ORDER
        $d = array();


        $uri    = Uri::getInstance();
        $action = $uri->toString();
        $app    = Factory::getApplication();

        $user  = PhocacartUser::getUser();
        $guest = PhocacartUserGuestuser::getGuestUser();

        if ($guest && (int)$user->id < 1) {
            $address = PhocacartUserGuestuser::getUserAddressGuest();
        } else {
            $address = PhocacartUser::getUserAddress($user->id);
        }

        // VAT can be changed by plugin, this needs to be done before calculation start
        // 1. Check the VAT by external service if it is enabled in plugin
        if (!empty($address[0])) {
            // Event user e.g. check valid VAT and store information about it
            Dispatcher::dispatch(new Event\Tax\UserAddressBeforeSaveOrder('com_phocacart.order', $address[0]));
        }
        // 2. Use stored information and change the VAT (before calculation)


        // 3. Store information about the VAT check to order table.
        if (isset($address[0]->params_user)) {
            $d['params_user'] = $address[0]->params_user;
        }


        $cart  = new PhocacartCartRendercheckout();
        $cart->setInstance(3);//order
        $cart->setType($this->type);
        $cart->setFullItems();

        $fullItems = $cart->getFullItems();
        $currency  = PhocacartCurrency::getCurrency();



        if (empty($fullItems[0])) {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY');
            $app->enqueueMessage($msg, 'error');
            return false;
        }
        $shippingId = $cart->getShippingId();
        $cart->addShippingCosts($shippingId);
        $shippingC = $cart->getShippingCosts();
        $payment   = $cart->getPaymentMethod();
        $cart->addPaymentCosts($payment['id']);// validity of payment will be checked
        $paymentC   = $cart->getPaymentCosts();
        $couponCart = $cart->getCoupon();
        $coupon     = false;
        if (isset($couponCart['id']) && $couponCart['id'] > 0) {
            $couponO = new PhocacartCoupon();
            $couponO->setType($this->type);
            $couponO->setCoupon((int)$couponCart['id']);
            $coupon = $couponO->getCoupon();
        }
        if (!$coupon) {
            $coupon = $couponCart;
        }


        $cart->roundTotalAmount();

        $total = $cart->getTotal();


        // --------------------
        // TERMS AND CONDITIONS, PRIVACY
        // --------------------
        // checked in controller


        // --------------------
        // CHECK COUPON
        // --------------------

        if (isset($coupon['id']) && (int)$coupon['id'] > 0 && $cart->getCouponValid() == false) {
            $msg = Text::_('COM_PHOCACART_COUPON_INVALID_EXPIRED_REACHED_USAGE_LIMIT') . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            PhocacartPayment::removePayment(0, 1);
            return false;
        }


        // --------------------
        // CHECK OPENING TIMES
        // --------------------
        // Possible parameter: display message on info page: PhocacartTime::checkOpeningTimes(), Hide message on info page: PhocacartTime::checkOpeningTimes(0)
        if (PhocacartTime::checkOpeningTimes() == false) {
            // Message set in checkOpeningTimes() method
            return false;
        }
        // --------------------
        // CHECK GUEST USER
        // --------------------

        if ((!isset($user->id) || (isset($user->id) && $user->id < 1)) && $guest == false && !PhocacartPos::isPos()) {

            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_GUEST_CHECKOUT_DISABLED') . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        // --------------------
        // CHECK CAPTCHA
        // --------------------
       $pos			            = PhocacartPos::isPosView();
        $enable_captcha_checkout    = PhocacartCaptcha::enableCaptchaCheckout();
        if ($enable_captcha_checkout && !$pos) {
            if (!PhocacartCaptchaRecaptcha::isValid()) {
                if ($order_language == 0) {
                    $pLang->setLanguageBack($defaultLang);
                }
                $msg = Text::_('COM_PHOCACART_WRONG_CAPTCHA') . $msgSuffix;


                $app->enqueueMessage($msg, 'error');
                return false;
                // What happens when the CAPTCHA was entered incorrectly
                //$info = array();
                //$info['field'] = 'question_captcha';

            }
        }

        // --------------------
        // CHECK MINIMUM ORDER AMOUNT
        // --------------------
        if ($min_order_amount > 0 && $total[0]['brutto'] < $min_order_amount) {
            $price = new PhocacartPrice();
            $price->setCurrency($currency->id);
            $priceFb = $price->getPriceFormat($total[0]['brutto']);
            $priceFm = $price->getPriceFormat($min_order_amount);
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_NOT_MET_UPDATE_CART_BEFORE_ORDERING');
            $msg .= '<br />';
            $msg .= Text::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_IS') . ': ' . $priceFm;
            $msg .= '<br />';
            $msg .= Text::_('COM_PHOCACART_YOUR_ORDER_AMOUNT_IS') . ': ' . $priceFb . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        // --------------------
        // CHECK STOCK VALIDITY
        // --------------------
        $stockValid = $cart->getStockValid();
        if ($stock_checking == 1 && $stock_checkout == 1 && $stockValid == 0) {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        // --------------------
        // CHECK MIN QUANTITY
        // --------------------
        $minQuantityValid = $cart->getMinimumQuantityValid();
        if ($minQuantityValid == 0) {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        // --------------------
        // CHECK MIN MULTIPLE QUANTITY
        // --------------------
        $minMultipleQuantityValid = $cart->getMinimumMultipleQuantityValid();
        if ($minMultipleQuantityValid == 0) {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        // --------------------
        // CHECK IF PRODUCT OR ATTRIBUTES EXIST
        // --------------------
        $productsRemoved = $cart->getProductsRemoved();

        if (!empty($productsRemoved)) {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            // Message is set by cart class
            //$msg = Text::_('') . $msgSuffix;
            //$app->enqueueMessage($msg, 'error');
            return false;
        }



        $db = Factory::getDBO();

        //JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/tables');


        if ($guest) {
            $d['user_id'] = 0;
        } else {
            $d['user_id'] = (int)$user->id;
        }


        // SET STATUS
        // STATUS IS SET DIRECTLY
        // 1) here in order class - when ordering done $d['status_id']
        // 2) in phocacartorder.php model (administration - changing order)
        // 3) in phocacarteditstatus.php model (administration - changing status)
        // 4) or in e.g. payment methods through PhocacartOrderStatus::changeStatusInOrderTable method

        $statusId = $pC->get('default_order_status', 1);// Ordered (Pending) as default

        // Free Download
        // 1) All products are digital
        // 2) Order is zero price
        if (isset($total[0]['countdigitalproducts']) && isset($total[0]['countallproducts'])
            && (int)$total[0]['countdigitalproducts'] == $total[0]['countallproducts']
            && $total[0]['brutto'] == 0 && $total[0]['netto'] == 0) {
            $statusId = $pC->get('default_order_status_free_download', 1);// Ordered (Pending) as default

        }

        if (isset($payment['method'])) {
            Dispatcher::dispatch(new Event\Payment\BeforeSaveOrder($statusId, (int)$payment['id'], [
              'pluginname' => $payment['method']
            ]));

            $d['status_id'] = (int)$statusId; // e.g. by POS Cash we get automatically the status as completed
        } else {
            $d['status_id'] = $statusId;// no plugin or no event found
        }

        $d['type'] = PhocacartType::getTypeByTypeArray($this->type);

        // Data order
        $d['comment']    = isset($data['phcomment']) ? $data['phcomment'] : '';
        $d['privacy']    = isset($data['privacy']) ? (int)$data['privacy'] : '';
        $d['terms']      = isset($data['phcheckouttac']) ? (int)$data['phcheckouttac'] : '';
        $d['newsletter'] = isset($data['newsletter']) ? (int)$data['newsletter'] : 0;

        // Data POS
        $d['amount_pay']      = isset($data['amount_pay']) ? $data['amount_pay'] : 0;
        $d['amount_tendered'] = isset($data['amount_tendered']) ? $data['amount_tendered'] : 0;
        $d['amount_change']   = isset($data['amount_change']) ? $data['amount_change'] : 0;

        $d['published']              = 1;

        $d['shipping_id']            = (int)$shippingId;
        $shippingParams              = array();

        if ((int)$shippingId > 0 && isset($shippingC['params_shipping']) && !empty($shippingC['params_shipping']) && $shippingC['params_shipping'] != '') {
            $shippingParams = json_decode($shippingC['params_shipping'], true);
        }

        if ((int)$shippingId > 0 && isset($shippingC['method']) && $shippingC['method'] != '') {
            $shippingParams['method']= htmlspecialchars(strip_tags($shippingC['method']));
        }

        if ((int)$shippingId > 0 && isset($shippingC['title']) && $shippingC['title'] != '') {
            $shippingParams['title'] = htmlspecialchars(strip_tags($shippingC['title']));
        }

        $shippingParams['total_weight']     = $total[0]['weight'];
        $shippingParams['total_length']     = $total[0]['length'];
        $shippingParams['total_width']      = $total[0]['width'];
        $shippingParams['total_height']     = $total[0]['height'];
        $shippingParams['total_volume']     = $total[0]['volume'];



        $d['params_shipping']        = json_encode($shippingParams);

        $d['payment_id']             = (int)$payment['id'];
        $paymentParams               = array();
        if ((int)$payment['id'] > 0 && isset($paymentC['params_payment']) && !empty($paymentC['params_payment']) && $paymentC['params_payment'] != '') {
            $paymentParams = json_decode($paymentC['params_payment'], true);
        }

        if ((int)$payment['id'] > 0 && isset($paymentC['method']) && $payment['method'] != '') {
            $paymentParams['method'] = htmlspecialchars(strip_tags($payment['method']));
        }

        if ((int)$payment['id'] > 0 && isset($paymentC['title']) && $payment['title'] != '') {
            $paymentParams['title'] = htmlspecialchars(strip_tags($payment['title']));
        }


        $d['params_payment']         = json_encode($paymentParams);



        $d['coupon_id']              = (int)$coupon['id'];
        $d['currency_id']            = (int)$currency->id;
        $d['currency_code']          = $currency->code;
        $d['currency_exchange_rate'] = $currency->exchange_rate;
        $d['ip']                     = PhocacartUtils::getIp();//(!empty($_SERVER['REMOTE_ADDR'])) ? (string)$_SERVER['REMOTE_ADDR'] : '';
        $user_agent                  = PhocacartUtils::getUserAgent();//(!empty($_SERVER['HTTP_USER_AGENT'])) ? (string)$_SERVER['HTTP_USER_AGENT'] : '';
        $d['user_agent']             = substr($user_agent, 0, 200);
        $d['order_token']            = PhocacartUtils::getToken();
        $d['tax_calculation']        = $pC->get('tax_calculation', 0);
        $d['unit_weight']            = $unit_weight;
        $d['unit_volume']            = $unit_volume;
        $d['unit_size']              = $unit_size;
        $d['discount_id']            = $cart->getCartDiscountId();

        $d['vendor_id']           = $cart->getVendorId();
        $d['ticket_id']           = $cart->getTicketId();
        $d['unit_id']             = $cart->getUnitId();
        $d['section_id']          = $cart->getSectionId();
        $d['loyalty_card_number'] = $cart->getLoyaltyCartNumber();

        $d['user_lang']    = $userLang;
        $d['default_lang'] = $defaultLang;



        // --------------------
        // CHECK PAYMENT AND SHIPPING - TEST IF THE ORDER HAS RIGHT SHIPPING AND PAYMENT METHOD
        // --------------------
        $shippingClass = new PhocacartShipping();
        $shippingClass->setType($this->type);
        $paymentClass = new PhocacartPayment();
        $paymentClass->setType($this->type);



        $dataAddress             = array();
        $dataAddress['bcountry'] = isset($address[0]->country) && (int)$address[0]->country > 0 ? (int)$address[0]->country : 0;
        $dataAddress['bregion']  = isset($address[0]->region) && (int)$address[0]->region > 0 ? (int)$address[0]->region : 0;
        $dataAddress['scountry'] = isset($address[1]->country) && (int)$address[1]->country > 0 ? (int)$address[1]->country : 0;
        $dataAddress['sregion']  = isset($address[1]->region) && (int)$address[1]->region > 0 ? (int)$address[1]->region : 0;


        $country = $shippingClass->getUserCountryShipping($dataAddress);
        $region  = $shippingClass->getUserRegionShipping($dataAddress);
        $zip 	 = $shippingClass->getUserZipShipping($dataAddress);


        // Check Shipping method
        if ($shippingId > 0) {
            // 1) User selected some method
            //    - check if this method even exists
            //	  - and check if the selected method meets every criteria and rules to be selected
            //$shippingMethods	= $shippingClass->checkAndGetShippingMethod($shippingId); CANNOT BE USED BECAUSE OF DIFFERENT VARIABLES IN ORDER
            $shippingMethods = $shippingClass->getPossibleShippingMethods($total[0]['subtotalnetto'], $total[0]['subtotalbrutto'], $total[0]['quantity'], $country, $region, $zip, $total[0]['weight'], $total[0]['length'], $total[0]['width'], $total[0]['height'], $shippingId, 0);

        } else {
            // 2) No shipping method selected
            $shippingMethods = false;
        }

        $sOCh = array();// Shipping Options Checkout
        // PRODUCTTYPE - Digital products are even gift vouchers
        $sOCh['all_digital_products'] = isset($total[0]['countdigitalproducts']) && isset($total[0]['countallproducts']) && (int)$total[0]['countdigitalproducts'] == $total[0]['countallproducts'] ? 1 : 0;
        $shippingNotUsed              = PhocacartShipping::isShippingNotUsed($sOCh);// REVERSE


        $shippingNotFoundAllowProceed = false;
        if (empty($shippingMethods) && $skip_shipping_method == 3) {
            // In case no shipping method will be found for customer even all rules were applied - allow proceeding order without selecting shipping method
            // THIS CASE CAN BE VENDOR ERROR (wrong setting of shipping methods) OR PURPOSE - be aware when using $skip_shipping_method = 3
            // Cooperates with components/com_phocacart/views/checkout/view.html.php 230

            // Find all possible shipping methods (without shipping method selected) to see if there is really no rule to display any method
            $shippingtMethodsAllPossible = $shippingClass->getPossibleShippingMethods($total[0]['subtotalnetto'], $total[0]['subtotalbrutto'], $total[0]['quantity'], $country, $region, $zip, $total[0]['weight'], $total[0]['length'], $total[0]['width'], $total[0]['height'], 0, 0);


            if (empty($shippingtMethodsAllPossible)) {
                $shippingNotFoundAllowProceed = true;
            }
        }


        if (!empty($shippingMethods)) {
            // IS OK - some shipping method was selected
        } else if (empty($shippingMethods) && PhocacartPos::isPos()) {
            // IS OK - shipping method was not selected but we are in POS
        } else if (empty($shippingMethods) && $shippingNotUsed) {
            // IS OK - shipping method was not selected but there is none for selecting (shipping methods intentionally not used in shop)
            //         a) no shipping method is used
            //         b) or e.g. all items in cart are downloadable products and in Phoca Cart options is set that in such case shipping need not to be selected
            $shippingId = 0;// Needed for payment method check

        } else if ($shippingNotFoundAllowProceed) {
            // IS OK
            $shippingId = 0;
        } else {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_SHIPPING_METHOD');
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        $country = $paymentClass->getUserCountryPayment($dataAddress);
        $region  = $paymentClass->getUserRegionPayment($dataAddress);

        // Check Payment method
        if ($payment['id'] > 0) {
            // 1) User selected some method
            //    - check if this method even exists
            //	  - and check if the selected method meets every criteria and rules to be selected
            //$paymentMethods	= $paymentClass->checkAndGetPaymentMethod($payment['id']); CANNOT BE USED BECAUSE OF DIFFERENT VARIABLES IN ORDER
            $paymentMethods = $paymentClass->getPossiblePaymentMethods($total[0]['subtotalnetto'], $total[0]['subtotalbrutto'], $country, $region, $shippingId, $payment['id'], 0, $d['currency_id']);


        } else {
            // 2) No payment method selected
            $paymentMethods = false;
        }


        $pOCh                      = array();// Payment Options Checkout
        $pOCh['order_amount_zero'] = $total[0]['brutto'] == 0 && $total[0]['netto'] == 0 ? 1 : 0;
        $paymentNotUsed            = PhocacartPayment::isPaymentNotUsed($pOCh);// REVERSE

        $paymentNotFoundAllowProceed = false;
        if (empty($paymentMethods) && $skip_payment_method == 3) {
            // In case no payment method will be found for customer even all rules were applied - allow proceed order without payment
            // THIS CASE CAN BE VENDOR ERROR (wrong setting of shipping methods) OR PURPOSE - be aware when using $skip_shipping_method = 3
            // Cooperates with components/com_phocacart/views/checkout/view.html.php 270

            // Find all possible payments methods (without payment method selected) to see if there is really no rule to display any method
            $paymentMethodsAllPossible = $paymentClass->getPossiblePaymentMethods($total[0]['subtotalnetto'], $total[0]['subtotalbrutto'], $country, $region, $shippingId, 0, 0, $d['currency_id']);
            if (empty($paymentMethodsAllPossible)) {
                $paymentNotFoundAllowProceed = true;
            }
        }

        if (!empty($paymentMethods)) {
            // IS OK
        } else if (empty($paymentMethods) && PhocacartPos::isPos()) {
            // IS OK
        } else if (empty($paymentMethods) && $paymentNotUsed) {
            // IS OK
            $paymentId = 0;
        } else if ($paymentNotFoundAllowProceed) {
            // IS OK
            $paymentId = 0;
        } else {
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_PAYMENT_METHOD');
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        $row = Table::getInstance('PhocacartOrder', 'Table', array());


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_($row->getError()) . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        $row->date     = gmdate('Y-m-d H:i:s');
        $row->modified = $row->date;


        if (!$row->check()) {
            //throw new Exception($row->getError());
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_($row->getError()) . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->store()) {
            //throw new Exception($row->getError());
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }
            $msg = Text::_($row->getError()) . $msgSuffix;
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        // GET ID OF ORDER
        if ((int)$row->id > 0) {

            // Set Order Billing
            $orderBillingData = $this->saveOrderBilling($row->id, $row->date, $d['status_id']);

            // ADDRESS;
            //$address = PhocacartUser::getUserAddress($user->id); - set above
            // Type 0 is Billing Address
            if (isset($address[0]->type) && $address[0]->type == 0) {
                $this->cleanTable('phocacart_order_users', $row->id);
                $this->saveOrderUsers($address[0], $row->id);
            } else if (isset($address[1]->type) && $address[1]->type == 0) {
                $this->cleanTable('phocacart_order_users', $row->id);
                $this->saveOrderUsers($address[1], $row->id);
            }

            // Type 1 is Shipping Address
            if (isset($address[1]->type) && $address[1]->type == 1) {
                $this->saveOrderUsers($address[1], $row->id);
            } else if (isset($address[0]->type) && $address[0]->type == 1) {
                $this->saveOrderUsers($address[0], $row->id);
            }

            //PRODUCT
            if (!empty($fullItems[1])) {
                $this->cleanTable('phocacart_order_products', $row->id);
                $this->cleanTable('phocacart_order_attributes', $row->id);

                foreach ($fullItems[1] as $k => $v) {


                    // While saving:
                    // Check if attributes which are required were filled
                    // Check if products can be accessed (include their categories)


                    $orderProductId = $this->saveOrderProducts($v, $row->id);

                    /*
                    $v['id'] = product_id
                    $row->id = order_id
                    $orderProductId = order_product_id
                    */

                    if ($orderProductId > 0) {
                        // PRODUCT DISCOUNTS - we are here because we need Product ID and Order Product ID - both are different ids
                        $this->saveOrderProductDiscounts($orderProductId, $v['id'], $row->id, $k, $fullItems);
                    }

                    if ($orderProductId > 0) {
                        // DOWNLOAD - we are here because we need Product ID and Order Product ID - both are different ids

                        if (!isset($v['attributes'])) {
                            $v['attributes'] = false;
                        }
                        $this->saveOrderDownloads($orderProductId, $v['id'], $v['catid'], $row->id);
                    }


                    if ($orderProductId > 0) {
                        // DOWNLOAD - we are here because we need Product ID and Order Product ID - both are different ids

                        if (!isset($v['attributes'])) {
                            $v['attributes'] = false;
                        }
                        // User can buy gift coupons
                        // When user buys one the same coupon (quantity > 1) we need to create more coupons from one "product"
                        if (isset($v['quantity'])) {
                            for ($i = 1; $i <= (int)$v['quantity']; $i++) {
                                $this->saveOrderGiftCoupons($orderProductId, $v, $row->id, $k, $fullItems);
                            }
                        }


                    }


                    if ($orderProductId > 0) {
                        // UPDATE the number of sales of one product - to save sql queries in frontend
                        $this->updateNumberOfSalesOfProduct($orderProductId, $v['id'], $row->id);
                    }

                    if (!$orderProductId) {

                        // DELETE NEWLY CREATED ORDER WHEN FAIL (not accessible product, required option)
                        $this->deleteOrder($row->id);
                        $this->cleanTable('phocacart_order_products', $row->id);
                        $this->cleanTable('phocacart_order_attributes', $row->id);
                        $this->cleanTable('phocacart_order_users', $row->id);
                        $this->cleanTable('phocacart_order_product_discounts', $row->id);
                        $this->cleanTable('phocacart_order_discounts', $row->id);
                        $this->cleanTable('phocacart_order_coupons', $row->id);

                        $msg = Text::_('COM_PHOCACART_ORDER_NOT_EXECUTED_PRODUCT_NOT_ACCESSIBLE_OR_REQUIRED_ATTRIBUTE_OPTION_NOT_SELECTED');
                        $app->enqueueMessage($msg, 'error');
                        return false;

                    }
                }

            }


            // DISCOUNTS
            $this->cleanTable('phocacart_order_discounts', $row->id);
            if ($total[2]['dnetto'] > 0) {
                $this->saveOrderDiscounts(Text::_('COM_PHOCACART_PRODUCT_DISCOUNT'), $total[2], $row->id);
            }
            if ($total[3]['dnetto'] > 0) {
                $this->saveOrderDiscounts(Text::_('COM_PHOCACART_CART_DISCOUNT'), $total[3], $row->id);
            }

            // COUPONS
            if (!empty($coupon)) {
                $this->cleanTable('phocacart_order_coupons', $row->id);
                $this->saveOrderCoupons($coupon, $total[4], $row->id);
                PhocacartCoupon::storeCouponCount((int)$coupon['id']);
                PhocacartCoupon::storeCouponCountUser((int)$coupon['id'], $d['user_id']);
            }


            // REWARD
            $this->cleanTable('phocacart_reward_points', $row->id);

            // REWARD DISCOUNT - user used the points to buy items
            if ($user->id > 0 && isset($total[0]['rewardproductusedtotal']) && (int)$total[0]['rewardproductusedtotal'] > 0) {
                $rewardProductTotal = -(int)$total[0]['rewardproductusedtotal'];
                $this->saveRewardPoints($user->id, $rewardProductTotal, $orderBillingData, 0, -1);
            }
            // REWARD POINTS + user get the points when buying items
            if ($user->id > 0 && isset($total[0]['points_received']) && (int)$total[0]['points_received'] > 0) {
                $this->saveRewardPoints($user->id, (int)$total[0]['points_received'], $orderBillingData, 0, 1);
            }


            // HISTORY AND ORDER STATUS
            $this->cleanTable('phocacart_order_history', $row->id);
            $notify = 0;
            $status = PhocacartOrderStatus::getStatus($d['status_id']);
            if (isset($status['email_customer'])) {
                $notify = $status['email_customer'];
            }

            // If vendor makes and order in POS e.g. then store his/her as the one who made the change
            $userReal = Factory::getUser();

            $this->saveOrderHistory($d['status_id'], $notify, $userReal->id, $row->id);


            // BE AWARE***********
            // $d is newly defined so use d2
            // *******************


            // TOTAL
            if (!empty($total[0])) {
                $this->cleanTable('phocacart_order_total', $row->id);
                $ordering                 = 1;
                $d2                       = array();
                $d2['order_id']           = $row->id;
                $d2['amount_currency']    = 0;
                $d2['title_lang']         = '';
                $d2['title_lang_suffix']  = '';
                $d2['title_lang_suffix2'] = '';


                if (isset($total[1]['netto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_SUBTOTAL');
                    $d2['title_lang']         = 'COM_PHOCACART_SUBTOTAL';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    $d2['type']               = 'netto';
                    $d2['amount']             = $total[1]['netto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 1;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }

                // Reward Discount
                if (isset($total[5]['dnetto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_REWARD_DISCOUNT') . $total[5]['rewardproducttxtsuffix'];
                    $d2['title_lang']         = 'COM_PHOCACART_REWARD_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = $total[5]['rewardproducttxtsuffix'];
                    $d2['type']               = 'dnetto';
                    $d2['amount']             = '-' . $total[5]['dnetto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 1;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                if (isset($total[5]['dbrutto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_REWARD_DISCOUNT') . $total[5]['rewardproducttxtsuffix'];
                    $d2['title_lang']         = 'COM_PHOCACART_REWARD_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = $total[5]['rewardproducttxtsuffix'];
                    $d2['type']               = 'dbrutto';
                    $d2['amount']             = '-' . $total[5]['dbrutto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 0;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }

                // Product Discount
                if (isset($total[2]['dnetto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_PRODUCT_DISCOUNT');
                    $d2['title_lang']         = 'COM_PHOCACART_PRODUCT_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    $d2['type']               = 'dnetto';
                    $d2['amount']             = '-' . $total[2]['dnetto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 1;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                if (isset($total[2]['dbrutto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_PRODUCT_DISCOUNT');
                    $d2['title_lang']         = 'COM_PHOCACART_PRODUCT_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    $d2['type']               = 'dbrutto';
                    $d2['amount']             = '-' . $total[2]['dbrutto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 0;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                /*if (!empty($total[2]['tax'])) {
                    foreach($total[2]['tax'] as $k => $v) {
                        if ($v['tax'] > 0) {
                            $d2['title']	= $v['title'];
                            $d2['type']		= 'dtax';
                            $d2['amount']	= $v['tax'];
                            $d2['ordering']	= $ordering;
                            $this->saveOrderTotal($d2);
                            $ordering++;
                        }
                    }
                }*/


                // Cart Discount
                if (isset($total[3]['dnetto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_CART_DISCOUNT') . $total[3]['discountcarttxtsuffix'];
                    $d2['title_lang']         = 'COM_PHOCACART_CART_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = $total[3]['discountcarttxtsuffix'];
                    $d2['type']               = 'dnetto';
                    $d2['amount']             = '-' . $total[3]['dnetto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 1;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                if (isset($total[3]['dbrutto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_CART_DISCOUNT') . $total[3]['discountcarttxtsuffix'];
                    $d2['title_lang']         = 'COM_PHOCACART_CART_DISCOUNT';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = $total[3]['discountcarttxtsuffix'];
                    $d2['type']               = 'dbrutto';
                    $d2['amount']             = '-' . $total[3]['dbrutto'];
                    $d2['ordering']           = $ordering;
                    $d2['published']          = 0;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                /*if (!empty($total[3]['tax'])) {
                    foreach($total[3]['tax'] as $k => $v) {
                        if ($v['tax'] > 0) {
                            $d2['title']	= $v['title'];
                            $d2['type']		= 'dtax';
                            $d2['amount']	= $v['tax'];
                            $d2['ordering']	= $ordering;
                            $this->saveOrderTotal($d2);
                            $ordering++;
                        }
                    }
                }*/


                // Coupon Discount
                if (isset($total[4]['dnetto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_COUPON');
                    $d2['title_lang']         = 'COM_PHOCACART_COUPON';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    if (isset($coupon['title']) && $coupon['title'] != '') {
                        $d2['title']              = $coupon['title'] . $total[4]['couponcarttxtsuffix'];
                        $d2['title_lang']         = $coupon['title'];
                        $d2['title_lang_suffix']  = '';
                        $d2['title_lang_suffix2'] = $total[4]['couponcarttxtsuffix'];
                    }
                    $d2['type']      = 'dnetto';
                    $d2['amount']    = '-' . $total[4]['dnetto'];
                    $d2['ordering']  = $ordering;
                    $d2['published'] = 1;
                    $d2['item_id']   = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                if (isset($total[4]['dbrutto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_COUPON');
                    $d2['title_lang']         = 'COM_PHOCACART_COUPON';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    if (isset($coupon['title']) && $coupon['title'] != '') {
                        $d2['title']              = $coupon['title'] . $total[4]['couponcarttxtsuffix'];
                        $d2['title_lang']         = $coupon['title'];
                        $d2['title_lang_suffix2'] = $total[4]['couponcarttxtsuffix'];
                    }
                    $d2['type']      = 'dbrutto';
                    $d2['amount']    = '-' . $total[4]['dbrutto'];
                    $d2['ordering']  = $ordering;
                    $d2['published'] = 0;
                    $d2['item_id']   = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }
                /*if (!empty($total[4]['tax'])) {
                    foreach($total[4]['tax'] as $k => $v) {
                        if ($v['tax'] > 0) {
                            $d2['title']	= $v['title'];
                            $d2['type']		= 'dtax';
                            $d2['amount']	= $v['tax'];
                            $d2['ordering']	= $ordering;
                            $this->saveOrderTotal($d2);
                            $ordering++;
                        }
                    }
                }*/

                if (!empty($total[0]['tax'])) {


                    foreach ($total[0]['tax'] as $k => $v) {

                        if (isset($v['taxid']) && $v['taxid'] > 0) {

                            $v['taxcalc']      = (int)$d['tax_calculation'];
                            $displayPriceItems = PhocaCartPrice::displayPriceItems($v, 'order');
                            $published = 0;
                            if ($displayPriceItems['tax'] == 1) {
                                $published = 1;
                            }
                            $d2['title']              = $v['title'];
                            $d2['title_lang']         = $v['title'];
                            $d2['title_lang_suffix']  = '';
                            $d2['title_lang_suffix2'] = '';
                            $d2['type']               = 'tax';
                            $d2['amount']             = $v['tax'];
                            $d2['ordering']           = $ordering;
                            $d2['published']          = $published;
                            //$d2['item_id']	        = (int)$k;// ID (Type) of VAT (10% or 20%)
                            $taxKeyA         = PhocacartTax::getTaxIdsFromKey($k);
                            $d2['item_id']   = (int)$taxKeyA['id'];
                            $d2['item_id_c'] = (int)$taxKeyA['countryid'];
                            $d2['item_id_r'] = (int)$taxKeyA['regionid'];
                            $d2['item_id_p'] = (int)$taxKeyA['pluginid'];
                            $this->saveOrderTotal($d2);
                            $ordering++;

                        }
                    }
                }


                $d2['published'] = 1;

                // Shipping
                if (!empty($shippingC)) {

                    $published = 1;

                    if (isset($shippingC['taxtxt']) && isset($shippingC['tax']) && $shippingC['tax'] > 0) {
                        $shippingC['taxcalc']      = (int)$d['tax_calculation'];
                        $displayPriceItems = PhocaCartPrice::displayPriceItems($shippingC, 'order');
                        $published = 0;
                        if ($displayPriceItems['tax'] == 1) {
                            $published = 1;
                        }
                    }

                    if (isset($shippingC['nettotxt']) && isset($shippingC['netto'])) {
                        $d2['title']              = $shippingC['title'] . ' - ' . $shippingC['nettotxt'];
                        $d2['title_lang']         = $shippingC['title'];
                        $d2['title_lang_suffix']  = $shippingC['netto_title_lang'];
                        $d2['title_lang_suffix2'] = '';
                        $d2['type']               = 'snetto';
                        $d2['amount']             = $shippingC['netto'];
                        $d2['ordering']           = $ordering;
                        $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                        $d2['published']          = $published;
                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }

                    if (isset($shippingC['taxtxt']) && isset($shippingC['tax']) && $shippingC['tax'] > 0) {

                        $d2['title']              = $shippingC['title'] . ' - ' . $shippingC['taxtxt'];
                        $d2['title_lang']         = isset($shippingC['title']) && $shippingC['title'] != '' ? $shippingC['title'] : $shippingC['tax_title_lang'];
                        $d2['title_lang_suffix']  = $shippingC['tax_title_suffix'];
                        $d2['title_lang_suffix2'] = '(' . $shippingC['tax_title_suffix2'] . ')';
                        $d2['type']               = 'stax';
                        //$d2['item_id']	        = (int)$shippingC['taxid'];
                        $taxKeyA         = PhocacartTax::getTaxIdsFromKey($shippingC['taxkey']);
                        $d2['item_id']   = (int)$taxKeyA['id'];
                        $d2['item_id_c'] = (int)$taxKeyA['countryid'];
                        $d2['item_id_r'] = (int)$taxKeyA['regionid'];
                        $d2['item_id_p'] = (int)$taxKeyA['pluginid'];
                        $d2['amount']    = $shippingC['tax'];
                        $d2['ordering']  = $ordering;
                        $d2['published']          = $published;

                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }

                    if (isset($shippingC['bruttotxt']) && isset($shippingC['brutto'])) {
                        $d2['title']              = $shippingC['title'] . ' - ' . $shippingC['bruttotxt'];
                        $d2['title_lang']         = $shippingC['title'];
                        $d2['title_lang_suffix']  = $shippingC['brutto_title_lang'];
                        $d2['title_lang_suffix2'] = '';
                        $d2['type']               = 'sbrutto';
                        $d2['amount']             = $shippingC['brutto'];
                        $d2['ordering']           = $ordering;
                        $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                        //$d2['published']          = $published; // if vat is not published e.g. because it is null, unpublish even netto but not brutto
                        $d2['published']            = 1;
                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }
                }

                // Payment
                if (!empty($paymentC)) {

                    $published = 1;
                    if (isset($paymentC['taxtxt']) && isset($paymentC['tax']) && $paymentC['tax'] > 0) {
                        $paymentC['taxcalc']      = (int)$d['tax_calculation'];
                        $displayPriceItems = PhocaCartPrice::displayPriceItems($paymentC, 'order');
                        $published = 0;
                        if ($displayPriceItems['tax'] == 1) {
                            $published = 1;
                        }
                    }

                    if (isset($paymentC['nettotxt']) && isset($paymentC['netto'])) {
                        $d2['title']              = $paymentC['title'] . ' - ' . $paymentC['nettotxt'];
                        $d2['title_lang']         = $paymentC['title'];
                        $d2['title_lang_suffix']  = $paymentC['netto_title_lang'];
                        $d2['title_lang_suffix2'] = '';
                        $d2['type']               = 'pnetto';
                        $d2['amount']             = $paymentC['netto'];
                        $d2['ordering']           = $ordering;
                        $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                        $d2['published']          = $published;
                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }

                    if (isset($paymentC['taxtxt']) && isset($paymentC['tax']) && $paymentC['tax']) {
                        $d2['title']              = $paymentC['title'] . ' - ' . $paymentC['taxtxt'];
                        $d2['title_lang']         = isset($paymentC['title']) && $paymentC['title'] != '' ? $paymentC['title'] : $paymentC['tax_title_lang'];
                        $d2['title_lang_suffix']  = $paymentC['tax_title_suffix'];
                        $d2['title_lang_suffix2'] = '(' . $paymentC['tax_title_suffix2'] . ')';
                        $d2['type']               = 'ptax';
                        //$d2['item_id']	        = (int)$paymentC['taxid'];
                        $taxKeyA         = PhocacartTax::getTaxIdsFromKey($paymentC['taxkey']);
                        $d2['item_id']   = (int)$taxKeyA['id'];
                        $d2['item_id_c'] = (int)$taxKeyA['countryid'];
                        $d2['item_id_r'] = (int)$taxKeyA['regionid'];
                        $d2['item_id_p'] = (int)$taxKeyA['pluginid'];
                        $d2['amount']    = $paymentC['tax'];
                        $d2['ordering']  = $ordering;
                        $d2['published']          = $published;
                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }

                    if (isset($paymentC['bruttotxt']) && isset($paymentC['brutto'])) {
                        $d2['title']              = $paymentC['title'] . ' - ' . $paymentC['bruttotxt'];
                        $d2['title_lang']         = $paymentC['title'];
                        $d2['title_lang_suffix']  = $paymentC['brutto_title_lang'];
                        $d2['title_lang_suffix2'] = '';
                        $d2['type']               = 'pbrutto';
                        $d2['amount']             = $paymentC['brutto'];
                        $d2['ordering']           = $ordering;
                        $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                        //$d2['published']          = $published;// if vat is not published e.g. because it is null, unpublish even netto but not brutto
                        $d2['published']            = 1;
                        $this->saveOrderTotal($d2);
                        $ordering++;
                    }
                }

                $d2['published'] = 1;

                // Rounding
                if (isset($total[0]['rounding'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_ROUNDING');
                    $d2['title_lang']         = 'COM_PHOCACART_ROUNDING';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    $d2['type']               = 'rounding';
                    $d2['amount']             = $total[0]['rounding'];
                    $d2['amount_currency']    = $total[0]['rounding_currency'];
                    $d2['ordering']           = $ordering;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }


                // Brutto
                if (isset($total[0]['brutto'])) {
                    $d2['title']              = Text::_('COM_PHOCACART_TOTAL');
                    $d2['title_lang']         = 'COM_PHOCACART_TOTAL';
                    $d2['title_lang_suffix']  = '';
                    $d2['title_lang_suffix2'] = '';
                    $d2['type']               = 'brutto';
                    $d2['amount']             = $total[0]['brutto'];
                    $d2['amount_currency']    = $total[0]['brutto_currency'];
                    $d2['ordering']           = $ordering;
                    $d2['item_id']            = $d2['item_id_c'] = $d2['item_id_r'] = $d2['item_id_p'] = 0;
                    $this->saveOrderTotal($d2);
                    $ordering++;
                }


                // TAX RECAPITULATION
                if (isset($total[0]['taxrecapitulation'])) {


                    $this->cleanTable('phocacart_order_tax_recapitulation', $row->id);
                    $orderingTC     = 1;
                    $d3             = array();
                    $d3['order_id'] = $row->id;

                    if (!empty($total[0]['taxrecapitulation']['items'])) {

                        foreach ($total[0]['taxrecapitulation']['items'] as $kTc => $vTc) {
                            //$d3['item_id']					= (int)$kTc;
                            $taxKeyA                      = PhocacartTax::getTaxIdsFromKey($kTc);
                            $d3['item_id']                = (int)$taxKeyA['id'];
                            $d3['item_id_c']              = (int)$taxKeyA['countryid'];
                            $d3['item_id_r']              = (int)$taxKeyA['regionid'];
                            $d3['item_id_p']              = (int)$taxKeyA['pluginid'];
                            $d3['title']                  = $vTc['title'];
                            $d3['title_lang']             = $vTc['title_lang'];
                            $d3['title_lang_suffix']      = '';
                            $d3['title_lang_suffix2']     = $vTc['title_lang_suffix2'];
                            $d3['type']                   = 'tax';
                            $d3['amount_netto']           = $vTc['netto'];
                            $d3['amount_tax']             = $vTc['tax'];
                            $d3['amount_brutto']          = $vTc['brutto'];
                            $d3['amount_brutto_currency'] = $vTc['brutto_currency'];
                            $d3['ordering']               = $orderingTC;
                            $this->saveOrderTaxRecapitulation($d3);
                            $orderingTC++;

                        }
                    }

                    // Clean d3 for next rows
                    $d3['item_id']                = 0;
                    $d3['item_id_c']              = 0;
                    $d3['item_id_r']              = 0;
                    $d3['item_id_p']              = 0;
                    $d3['amount_netto']           = 0;
                    $d3['amount_tax']             = 0;
                    $d3['amount_brutto']          = 0;
                    $d3['amount_brutto_currency'] = 0;

                    $d3['title']                  = Text::_('COM_PHOCACART_ROUNDING');
                    $d3['title_lang']             = 'COM_PHOCACART_ROUNDING';
                    $d3['title_lang_suffix']      = '';
                    $d3['title_lang_suffix2']     = '';
                    $d3['type']                   = 'rounding';// Complete Rounding
                    $d3['amount_brutto']          = $total[0]['rounding'];
                    $d3['amount_brutto_currency'] = $total[0]['rounding_currency'];
                    $d3['ordering']               = $orderingTC;
                    $this->saveOrderTaxRecapitulation($d3);
                    $orderingTC++;


                    $d3['title']                  = Text::_('COM_PHOCACART_ROUNDING') . ' (' . Text::_('COM_PHOCACART_INCL_TAX_RECAPITULATION_ROUNDING') . ')';
                    $d3['title_lang']             = 'COM_PHOCACART_ROUNDING';
                    $d3['title_lang_suffix']      = 'COM_PHOCACART_INCL_TAX_RECAPITULATION_ROUNDING';
                    $d3['title_lang_suffix2']     = '';
                    $d3['type']                   = 'trcrounding';// Only Tax Recapitulation Rounding - tax recapitulation rounding is a part of whole rounding
                    $d3['amount_brutto']          = $total[0]['taxrecapitulation']['rounding'];
                    $d3['amount_brutto_currency'] = $total[0]['taxrecapitulation']['rounding_currency'];
                    $d3['ordering']               = $orderingTC;
                    $this->saveOrderTaxRecapitulation($d3);
                    $orderingTC++;

                    $d3['title']              = Text::_('COM_PHOCACART_TOTAL');
                    $d3['title_lang']         = 'COM_PHOCACART_TOTAL';
                    $d3['title_lang_suffix']  = '';
                    $d3['title_lang_suffix2'] = '';
                    $d3['type']               = 'brutto';
                    $d3['amount_netto']       = $total[0]['taxrecapitulation']['netto_incl_sp'];
                    $d3['amount_tax']         = $total[0]['taxrecapitulation']['tax'];
                    //$d3['amount_brutto']			= $total[0]['taxrecapitulation']['brutto'];
                    $d3['amount_brutto'] = $total[0]['taxrecapitulation']['brutto_incl_rounding'];
                    //$d3['amount_brutto_currency']	= $total[0]['taxrecapitulation']['brutto_currency'];
                    $d3['amount_brutto_currency'] = $total[0]['taxrecapitulation']['brutto_currency_incl_rounding'];
                    $d3['ordering']               = $orderingTC;
                    $this->saveOrderTaxRecapitulation($d3);
                    $orderingTC++;

                }

            }


            // EVENT Shipping
            if ((int)$shippingId > 0 && isset($shippingC['method']) && $shippingC['method'] != '') {
              Dispatcher::dispatch(new Event\Shipping\AfterSaveOrder('com_phocacart.library.order', [
                  'pluginname' => $shippingC['method'],
                  'id' => (int)$row->id,
              ]));
            }


            // CHANGE STATUS
            // STOCK MOVEMENT (including a) Main Product, b) Product Variations method)

            // Change Status is not setting the status, it is about do getting info about status for sending emails, checking stock,
            if ($guest) {
                // Don't check the user (status.php, render.php)
                PhocacartOrderStatus::changeStatus($row->id, $d['status_id'], $d['order_token']);// Notify user, notify others, emails send - will be decided in function
            } else {
                PhocacartOrderStatus::changeStatus($row->id, $d['status_id']);// Notify user, notify others, emails send - will be decided in function
            }


            // Proceed or not proceed to payment gateway - depends on payment method
            // By every new order - clean the proceed payment session
            $session = Factory::getSession();
            $session->set('proceedpayment', array(), 'phocaCart');

            $response                  = PhocacartPayment::proceedToPaymentGateway($payment);
            $proceed                   = $response['proceed'];
            $this->message_after_order = $response['message'];

            if ($proceed) {

                $proceedPayment['orderid'] = $row->id;
                $session->set('proceedpayment', $proceedPayment, 'phocaCart');

                if ($this->downloadable_product == 1) {
                    $this->action_after_order = 4; // PAYMENT/DOWNLOAD
                } else {
                    $this->action_after_order = 3;// PAYMENT/NO DOWNLOAD
                }

            } else {

                if ($this->downloadable_product == 1) {
                    $this->action_after_order = 2; // ORDER/DOWNLOAD
                } else {
                    $this->action_after_order = 1;// ORDER/NO DOWNLOAD
                }

            }


            $this->data_after_order['order_id']     = (int)$row->id;
            $this->data_after_order['order_token']  = $row->order_token;
            $this->data_after_order['user_id']      = (int)$row->user_id;
            $this->data_after_order['shipping_id']  = (int)$row->shipping_id;
            $this->data_after_order['payment_id']   = (int)$row->payment_id;

            $this->data_after_order['shipping_method']   = '';
            $shippingMethod = json_decode($row->params_shipping);
            if (isset($shippingMethod->method)) {
               $this->data_after_order['shipping_method']   = $shippingMethod->method;
            }
            $this->data_after_order['payment_method']   = '';
            $paymentMethod = json_decode($row->params_payment);
            if (isset($paymentMethod->method)) {
               $this->data_after_order['payment_method']   = $paymentMethod->method;
            }


            //return true;
            if ($order_language == 0) {
                $pLang->setLanguageBack($defaultLang);
            }

            // UPDATE NEWSLETTER INFO
            if ((int)$d['newsletter'] > 0 ) {

                $name    = '';
                $email   = '';
                $privacy = (int)$d['privacy'] == 1 ? 1 : 0;

                if (isset($user->name)) {
                    $name = $user->name;

                    // Guest Users (find some name in billing or shipping address)
                } else if (isset($address[0]->name_first) && isset($address[0]->name_last)) {
                    $name = $address[0]->name_first . ' ' . $address[0]->name_last;
                } else if (isset($address[1]->name_first) && isset($address[1]->name_last)) {
                    $name = $address[1]->name_first . ' ' . $address[1]->name_last;
                } else if (isset($address[0]->name_last)) {
                    $name = $address[0]->name_last;
                } else if (isset($address[1]->name_last)) {
                    $name = $address[1]->name_last;
                } else if (isset($address[0]->name_first)) {
                    $name = $address[0]->name_first;
                } else if (isset($address[1]->name_first)) {
                    $name = $address[1]->name_first;
                } else if (isset($address[0]->name)) {
                    $name = $address[0]->name;
                } else if (isset($address[1]->name)) {
                    $name = $address[1]->name;
                }


                if (isset($user->email)) {
                    $email = $user->email;

                    // Guest Users
                } else if (isset($address[0]->email)) {
                    $email = $address[0]->email;
                } else if (isset($address[1]->email)) {
                    $email = $address[1]->email;
                }


               if  ((int)$user->id > 0) {
                    PhocacartNewsletter::updateNewsletterInfoByUser((int)$user->id, 1);// Internal Phoca Cart Table
               }

                if ($name != '' && $email != '') {
                    PhocacartNewsletter::storeSubscriber($name, $email, $privacy);// External Phoca Email Table
                }
            }


            return $row->id;

        } else {
            return false;
        }

        return false;
    }

    public function saveOrderBilling($id, $date, $statusId) {
        // Delivery note has the same number like order
        // Receipt is not used when displaying in frontend
        return self::storeOrderReceiptInvoiceId($id, $date, $statusId, array('O', 'I', 'R'));

    }

    public function saveOrderUsers($d, $orderId) {


        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $d = (array)$d;
        if (!isset($d['id'])) {
            $d['id']      = 0;// Guest Checkout
            $d['user_id'] = 0;
        }
        $d['order_id']        = (int)$orderId;
        $d['user_address_id'] = (int)$d['id'];
        $d['user_token']      = PhocacartUtils::getToken();
        $userGroups           = PhocacartGroup::getGroupsById((int)$d['user_id'], 1, 1);
        $d['user_groups']     = serialize($userGroups);


        unset($d['id']);// we do new autoincrement
        $row = Table::getInstance('PhocacartOrderUsers', 'Table', array());


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        return true;
    }


    public function saveOrderProducts($d, $orderId) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $row = Table::getInstance('PhocacartOrderProducts', 'Table', array());

        $checkP = PhocacartProduct::checkIfAccessPossible($d['id'], $d['catid'], $this->type);


        if (!$checkP) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ACCESSIBLE'). ' - ' . Text::_('COM_PHOCACART_PRODUCT') . ': ' . $d['title'], 'error');
            return false;
        }


        // Possible feature - remove this additional check based on mostly design parameters - for now removed for POS

        // 1) canDisplayAddtocartAdvanced - Product on demand product cannot be ordered
        // canDisplayAddtocart (is a part of  1) - Display add to cart disabled or only for specific access level or only for specific group (checked previously in checkIfAccessPossible)
        // 2) canDisplayAddtocartPrice - Product with zero price cannot be added to cart (can be set in options)
        // 3) canDisplayAddtocartStock - Product witz zero stock cannot be added to cart (can be set in options)


        $itemP = PhocacartProduct::getProduct((int)$d['id'], $d['catid']);
        $d['attributes'] = !empty($d['attributes']) ? $d['attributes'] : array();
        if (!empty($itemP)) {
            if ($itemP->owner_id)
                $d['owner_id'] = $itemP->owner_id;
            $price  = new PhocacartPrice();
            $priceP = $price->getPriceItems($itemP->price, $itemP->taxid, $itemP->taxrate, $itemP->taxcalculationtype, $itemP->taxtitle, 0, '', 1, 1, $itemP->group_price, $itemP->taxhide);

            $aA     = $d['attributes'];// Sanitanized yet //PhocacartAttribute::sanitizeAttributeArray($d['attributes']);
            $price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $itemP, 1);


            $price->correctMinusPrice($priceP);
            $priceA = isset($priceP['brutto']) ? $priceP['brutto'] : 0;

            // Stock (don't display add to cart when stock is zero)
            $stockStatus = array();

            $stock       = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $itemP, 2);


            $rights                      = new PhocacartAccessRights();
            $can_display_addtocart       = $rights->canDisplayAddtocartAdvanced($itemP);
            $can_display_addtocart_price = $rights->canDisplayAddtocartPrice($itemP, $priceA);
            $can_display_addtocart_stock = $rights->canDisplayAddtocartStock($itemP, $stock);

            $pos = PhocacartPos::isPos();

            if ($pos) {
                // Don't check "design" parameters
            } else {

                if (!$can_display_addtocart) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ACCESSIBLE') . ' - ' . Text::_('COM_PHOCACART_PRODUCT') . ': ' . $d['title'], 'error');
                    return false;
                }

                if (!$can_display_addtocart_price) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRICE_IS_ZERO') . ' - ' . Text::_('COM_PHOCACART_PRODUCT') . ': ' . $d['title'], 'error');
                    return false;
                }

                if (!$can_display_addtocart_stock) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_STOCK_IS_EMPTY') . ' - ' . Text::_('COM_PHOCACART_PRODUCT') . ': ' . $d['title'], 'error');
                    return false;
                }
            }

        }

        // Additional info
        $d['default_price']    = $d['default_price'];
        $d['default_tax_rate'] = $d['taxrate'];

        $taxKeyA                           = PhocacartTax::getTaxIdsFromKey($d['taxkey']);
        $d['default_tax_id']               = (int)$taxKeyA['id'];
        $d['default_tax_id_c']             = (int)$taxKeyA['countryid'];
        $d['default_tax_id_r']             = (int)$taxKeyA['regionid'];
        $d['default_tax_id_p']             = (int)$taxKeyA['pluginid'];
        $d['default_tax_calculation_rate'] = $d['taxcalctype'];
        $d['default_points_received']      = $d['default_points_received'];


        //$d['status_id']			= 1;// pending
        $d['published']         = 1;
        $d['order_id']          = (int)$orderId;
        $d['product_id']        = (int)$d['id'];
        $d['category_id']       = (int)$d['catid'];
        $d['product_id_key']    = $d['idkey'];
        $d['stock_calculation'] = $d['stockcalculation'];
        unset($d['id']);// we do new autoincrement


        //$d['tax'] = $d['tax'] / $d['quantity'];// in database we store the items per item
        //$d['dtax'] = $d['dtax']/$d['quantity'];// in database we store the items per item


        // STOCK HANDLING
        // will be set in order status administrator\components\com_phocacart\libraries\phocacart\order\status.php
        //$stock = PhocacartStock::handleStockProduct($d['product_id'], $d['status_id'], $d['quantity'] );


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        //if ((int)$row->id > 0 && !empty($d['attributes'])) {
        if ((int)$row->id > 0) {
            //$this->cleanTable('phocacart_order_attributes', $orderId); NOT HERE, we are in foreach

            if (empty($d['attributes'])) {
                $d['attributes'] = array();
            }

            $checkedA = PhocacartAttribute::checkRequiredAttributes((int)$d['product_id'], $d['attributes']);


            if (!$checkedA) {
                return false;
            }


            foreach ($d['attributes'] as $k => $v) {
                if (!empty($v)) {
                    foreach ($v as $k2 => $v2) {

                        $row2                   = Table::getInstance('PhocacartOrderAttributes', 'Table', array());
                        $d2                     = array();
                        $d2['order_id']         = (int)$orderId;
                        $d2['product_id']       = (int)$d['product_id'];
                        $d2['order_product_id'] = (int)$row->id;
                        $d2['attribute_id']     = (int)$v2['aid'];
                        $d2['option_id']        = (int)$v2['oid'];
                        $d2['attribute_title']  = $v2['atitle'];
                        $d2['type']             = $v2['atype'];
                        $d2['option_title']     = $v2['otitle'];
                        $d2['option_value']     = $v2['ovalue'];
                        // $d2['option_download_file']	= $v2['odownloadfile'];


                        // Will be set order status
                        // administrator\components\com_phocacart\libraries\phocacart\order\status.php
                        // $stockA = PhocacartStock::handleStockAttributeOption($d2['option_id'], $d['status_id'], $d['quantity'] );

                        if (!$row2->bind($d2)) {
                            //throw new Exception($row2->getError());
                            $msg = Text::_($row2->getError());
                            $app->enqueueMessage($msg, 'error');
                            return false;
                        }

                        if (!$row2->check()) {
                            //throw new Exception($row2->getError());
                            $msg = Text::_($row2->getError());
                            $app->enqueueMessage($msg, 'error');
                            return false;
                        }


                        if (!$row2->store()) {
                            //throw new Exception($row2->getError());
                            $msg = Text::_($row2->getError());
                            $app->enqueueMessage($msg, 'error');
                            return false;
                        }

                    }
                }
            }
        }

        /*} else if ((int)$row->id > 0){
            // Empty attributes - check if product include some required attribute
            $checkA = PhocacartAttribute::checkIfExists AndRequired($d['product_id']);
            if (!$checkedA) {
                return false;
            }
        }*/


        return $row->id;

    }

    public function saveOrderCoupons($coupon, $totalC, $orderId) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $d              = array();
        $d['order_id']  = (int)$orderId;
        $d['coupon_id'] = (int)$coupon['id'];
        $d['title']     = $coupon['title'];
        if (isset($coupon['code'])) {
            $d['code'] = $coupon['code'];
        }
        $d['amount'] = $totalC['dnetto'];// get the value from total
        $d['netto']  = $totalC['dnetto'];
        $d['brutto'] = $totalC['dbrutto'];

        $row = Table::getInstance('PhocacartOrderCoupons', 'Table', array());

        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }
        return true;
    }


    public function saveOrderDiscounts($discountTitle, $totalD, $orderId, $type = 0) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $d             = array();
        $d['order_id'] = (int)$orderId;
        //$d['discount_id']		= (int)$discount['id'];
        $d['title']  = $discountTitle;
        $d['amount'] = $totalD['dnetto'];// get the value from total
        $d['netto']  = $totalD['dnetto'];
        $d['brutto'] = $totalD['dbrutto'];
        $row         = Table::getInstance('PhocacartOrderDiscounts', 'Table', array());

        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }
        return true;
    }

    public function saveOrderTotal($d) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $row = Table::getInstance('PhocacartOrderTotal', 'Table', array());

        //$d['published']				= 1;
        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        return true;
    }

    public function saveOrderTaxRecapitulation($d) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $row = Table::getInstance('PhocacartOrderTaxRecapitulation', 'Table', array());

        //$d['published']				= 1;
        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        return true;
    }

    public function saveOrderHistory($statusId, $notify, $userId, $orderId) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $row = Table::getInstance('PhocacartOrderHistory', 'Table', array());

        $d                    = array();
        $d['order_status_id'] = (int)$statusId;
        $d['notify']          = (int)$notify;
        $d['user_id']         = (int)$userId;
        $d['order_id']        = (int)$orderId;
        $d['date']            = gmdate('Y-m-d H:i:s');


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }
        return true;
    }

    public function saveOrderDownloads($orderProductId, $productId, $catId, $orderId) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $pC                                 = PhocacartUtils::getComponentParameters();
        $download_product_attribute_options = $pC->get('download_product_attribute_options', 0);

        $isDownloadableProduct          = 0;
        $forceOnlyDownloadFileAttribute = 0;

        $row = Table::getInstance('PhocacartOrderDownloads', 'Table', array());


        //$productItem 	= new PhocacartProduct();
        $product = PhocacartProduct::getProduct((int)$productId, (int)$catId, $this->type);

        // Additional download files
        $additionalDownloadFiles = PhocacartFileAdditional::getProductFilesByProductId((int)$productId, 1);

        // Attribute Option Download Files
        $attributeDownloadFiles = PhocacartAttribute::getAttributeOptionDownloadFilesByOrder($orderId, $productId, $orderProductId);


        // 1) download_file for ordered product
        // 2) download_file for ordered attribute option of each product

        $d                     = array();
        $d['order_id']         = (int)$orderId;
        $d['product_id']       = (int)$productId;
        $d['order_product_id'] = (int)$orderProductId;
        $d['title']            = $product->title;
        $d['download_hits']    = 0;
        $d['download_days']    = $product->download_days;
        $d['published']        = 0;
        $d['date']             = gmdate('Y-m-d H:i:s');
        $d['ordering']         = 0;

        // If Product includes attribute option download file, this means there can be two different products:
        // a) produt without any attribute selected
        // b) product with attribute selected
        // So if set in options and there is a download file for attribute option - the main product download file will be skipped

        if ($download_product_attribute_options == 1 && !empty($attributeDownloadFiles)) {
            foreach ($attributeDownloadFiles as $k => $v) {

                if (isset($v['download_file']) && $v['download_file'] != ''
                    && isset($v['download_folder']) && $v['download_folder'] != ''
                    && isset($v['download_token'])
                    && isset($v['attribute_id']) && $v['attribute_id'] > 0
                    && isset($v['option_id']) && $v['option_id'] > 0
                    && isset($v['order_option_id']) && $v['order_option_id'] > 0) {


                    // !!! Both conditions are OK
                    // 1) we don't want provide a download file for main product in case that the product including attribute option is ordered (PARAMETER SET)
                    // 2) and yes there is selected attribute option including download file ordered (CUSTOMER ORDERED PRODUCT WITH ATTRIBUTE OPTION)
                    $forceOnlyDownloadFileAttribute = 1;
                }
            }
        }

        // 1)
        if ($forceOnlyDownloadFileAttribute == 0 && isset($product->download_file) && $product->download_file != '') {
            $d['download_token']  = $product->download_token;
            $d['download_folder'] = $product->download_folder;
            $d['download_file']   = $product->download_file;
            $d['type']            = 1;

            $db = Factory::getDbo();
            //$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_order_downloads WHERE catid = '.(int)$orderId);
            $db->setQuery('SELECT MAX(ordering) FROM #__phocacart_order_downloads');
            $max           = $db->loadResult();
            $d['ordering'] = $max + 1;

            if (!$row->bind($d)) {
                //throw new Exception($row->getError());
                $msg = Text::_($row->getError());
                $app->enqueueMessage($msg, 'error');
                return false;
            }

            if (!$row->check()) {
                //throw new Exception($row->getError());
                $msg = Text::_($row->getError());
                $app->enqueueMessage($msg, 'error');
                return false;
            }

            if (!$row->store()) {
                //throw new Exception($row->getError());
                $msg = Text::_($row->getError());
                $app->enqueueMessage($msg, 'error');
                return false;
            }
            $isDownloadableProduct = 1;
        }

        // 2)

        if ($forceOnlyDownloadFileAttribute == 0 && !empty($additionalDownloadFiles)) {

            $d['ordering']        = $d['ordering'] + 1;
            $d['download_token']  = '';
            $d['download_folder'] = $product->download_folder;
            $d['download_file']   = '';
            $d['download_days']   = -1;
            $d['type']            = 2;

            foreach ($additionalDownloadFiles as $k => $v) {


                if (isset($v['download_file']) && $v['download_file'] != ''
                    && isset($d['download_folder']) && $d['download_folder'] != ''
                    && isset($v['download_token']) && isset($v['download_days'])) {


                    $d['download_token'] = $v['download_token'];
                    $d['download_file']  = $v['download_file'];
                    $d['download_days']  = $v['download_days'];

                    $d['title'] = $product->title;

                    $row = Table::getInstance('PhocacartOrderDownloads', 'Table', array());


                    if (!$row->bind($d)) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }

                    if (!$row->check()) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }

                    if (!$row->store()) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }
                    $isDownloadableProduct = 1;
                    $d['ordering']         = $d['ordering'] + 1;
                }
            }

        }

        // 3)
        if (!empty($attributeDownloadFiles)) {

            $d['ordering'] = $d['ordering'] + 1;
            $d['type']     = 3;
            foreach ($attributeDownloadFiles as $k => $v) {

                if (isset($v['download_file']) && $v['download_file'] != ''
                    && isset($v['download_folder']) && $v['download_folder'] != ''
                    && isset($v['download_token'])
                    && isset($v['attribute_id']) && $v['attribute_id'] > 0
                    && isset($v['option_id']) && $v['option_id'] > 0
                    && isset($v['order_option_id']) && $v['order_option_id'] > 0) {

                    $d['download_file']   = $v['download_file'];
                    $d['download_folder'] = $v['download_folder'];
                    $d['download_token']  = $v['download_token'];
                    $d['attribute_id']    = $v['attribute_id'];
                    $d['option_id']       = $v['option_id'];
                    $d['order_option_id'] = $v['order_option_id'];
                    $d['title']           = $product->title . ' (' . $v['attribute_title'] . ': ' . $v['option_title'] . ')';

                    $row = Table::getInstance('PhocacartOrderDownloads', 'Table', array());

                    if (!$row->bind($d)) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }

                    if (!$row->check()) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }

                    if (!$row->store()) {
                        //throw new Exception($row->getError());
                        $msg = Text::_($row->getError());
                        $app->enqueueMessage($msg, 'error');
                        return false;
                    }
                    $isDownloadableProduct = 1;
                    $d['ordering']         = $d['ordering'] + 1;


                }
            }
        }


        $this->downloadable_product = $isDownloadableProduct;
        return true;
    }



    public function saveOrderGiftCoupons($orderProductId, $v, $orderId, $k, $fullItems) {

        $app = Factory::getApplication();
        $db = Factory::getDBO();
        $d  = array();

        if (!isset($v['type'])) {
            return false;
        }

        if ($v['type'] != 4) {
            // Product type != Gift Voucher
            return false;
        }

        if (isset($v['brutto']) && $v['brutto'] > 0) {
            $d['discount'] = $v['brutto'];
        } else {
            return false;
        }




        $d['calculation_type']      = 0; // Fixed amount
        $d['type']                  = 0; // Coupon type (COMMON, ONLINE SHOP, POS)
        $d['coupon_type']           = 2; // Coupon type (DEFAULT COUPON | GIFT VOUCHER)
        $d['gift_type']             = -1; // Possible gift voucher type (BIRTHDAY, PRESENT, FESTIVE ...)
        $d['gift_order_id']         = (int)$orderId;
        $d['gift_product_id']       = (int)$v['id'];
        $d['gift_order_product_id'] = (int)$orderProductId;
        $d['gift_recipient_name']   = '';
        $d['gift_recipient_email']  = '';
        $d['gift_sender_name']      = '';
        $d['gift_sender_message']   = '';
        $d['available_quantity']    = 1;
        $d['published']             = 0;// will be published by order status
        $d['access']                = 1;
        $d['title']                 = Text::_('COM_PHOCACART_GIFT_VOUCHER');
        $d['alias']                 = PhocacartUtils::getAliasName($d['title']);
        $d['code']                  = PhocacartCoupon::generateCouponCode();

        $d['valid_from']            = gmdate('Y-m-d H:i:s');

        $product = PhocacartProduct::getProduct((int)$v['id'], (int)$v['catid'], $this->type);

        if (!empty($v['attributes'])) {
            // Attributes
            foreach ($v['attributes'] as $k2 => $v2) {

                if (!empty($v2)) {
                    // Options
                    foreach ($v2 as $k3 => $v3) {

                        // Check if attribute is type GIFT
                        if (!isset($v3['atype']) || (isset($v3['atype']) && $v3['atype'] != 20)) {
                            // if not continue with another attribute (not option)
                            continue 2;
                        }

                        if(isset($v3['otype'])) {
                            switch($v3['otype']) {

                                case 20:
                                    $d['gift_recipient_name'] = urldecode($v3['ovalue']);
                                break;

                                case 21:
                                    $d['gift_recipient_email'] = '';
                                    $v3['ovalue'] = urldecode($v3['ovalue']);
                                    if(MailHelper::isEmailAddress($v3['ovalue'])){
                                        $d['gift_recipient_email'] = $v3['ovalue'];
                                    }
                                break;

                                case 22:
                                    $d['gift_sender_name'] = urldecode($v3['ovalue']);
                                break;

                                case 23:
                                    $d['gift_sender_message'] = urldecode($v3['ovalue']);
                                break;

                                case 24:
                                    $d['gift_type'] = urldecode($v3['ovalue']);
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($product->gift_types)) {
            $registry = new Registry;
            $registry->loadString($product->gift_types);
            $giftTypes = $registry->toArray();

            if(isset($d['gift_type'])) {

                $giftType = 'gift_types' . (int)$d['gift_type'];
                if (isset($giftTypes[$giftType])) {
                    $giftTypeA = $giftTypes[$giftType];

                    if (isset($giftTypeA['title'])) {
                        $d['gift_title'] = $giftTypeA['title'];
                    }

                    if (isset($giftTypeA['description'])) {
                        $d['gift_description'] = $giftTypeA['description'];
                    }

                    if (isset($giftTypeA['image'])) {
                        $d['gift_image'] = $giftTypeA['image'];
                    }

                    if (isset($giftTypeA['expiration_date'])) {
                        $d['valid_to'] = $giftTypeA['expiration_date'];
                    }

                    if (isset($giftTypeA['class_name'])) {
                        $d['gift_class_name'] = $giftTypeA['class_name'];
                    }

                    if (isset($giftTypeA['title']) && $giftTypeA['title'] != '') {
                        $d['title'] = $d['title'] . ' - ' . $giftTypeA['title'];
                    }
                }
            }

        }


        $db->setQuery('SELECT MAX(ordering) FROM #__phocacart_coupons');
        $max           = $db->loadResult();
        $d['ordering'] = $max + 1;

        $row = Table::getInstance('PhocacartCoupon', 'Table', array());
        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        return true;
    }

    public function saveOrderProductDiscounts($orderProductId, $productId, $orderId, $k, $fullItems) {

        $db = Factory::getDBO();

        // REWARD DIVIDED INTO PRODUCTS
        if (isset($fullItems[5][$k]['rewardproduct']) && $fullItems[5][$k]['rewardproduct'] == 1) {

            //$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
            $query = ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = ' . (int)$orderId . ' AND order_product_id = ' . (int)$orderProductId . ' AND product_id = ' . (int)$productId . ' AND type = 5';
            $db->setQuery($query);
            $db->execute();

            $amount   = $fullItems[5][$k]['netto'];
            $netto    = $fullItems[5][$k]['netto'];
            $brutto   = $fullItems[5][$k]['brutto'];
            $tax      = $fullItems[5][$k]['tax'];
            $final    = $fullItems[5][$k]['final'];
            $quantity = $fullItems[5][$k]['quantity'];
            $catid    = $fullItems[5][$k]['catid'];
            $query    = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
                . ' VALUES (' . (int)$orderId . ', ' . (int)$productId . ', ' . (int)$orderProductId . ', ' . 0 . ', ' . $db->quote($k) . ', ' . (int)$catid . ', 5, ' . $db->quote($fullItems[5][$k]['rewardproducttitle']) . ', ' . $amount . ', ' . $netto . ', ' . $brutto . ', ' . $tax . ', ' . $final . ', ' . (int)$quantity . ', 0)';
            $db->setQuery($query);
            $db->execute();
        }

        // DISCOUNT PRODUCTS
        if (isset($fullItems[2][$k]['discountproduct']) && $fullItems[2][$k]['discountproduct'] == 1) {

            //$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
            $query = ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = ' . (int)$orderId . ' AND order_product_id = ' . (int)$orderProductId . ' AND product_id = ' . (int)$productId . ' AND type = 2';
            $db->setQuery($query);
            $db->execute();

            $amount   = $fullItems[2][$k]['netto'];
            $netto    = $fullItems[2][$k]['netto'];
            $brutto   = $fullItems[2][$k]['brutto'];
            $tax      = $fullItems[2][$k]['tax'];
            $final    = $fullItems[2][$k]['final'];
            $quantity = $fullItems[2][$k]['quantity'];
            $catid    = $fullItems[3][$k]['catid'];
            $query    = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
                . ' VALUES (' . (int)$orderId . ', ' . (int)$productId . ', ' . (int)$orderProductId . ', ' . $db->quote($k) . ', ' . (int)$catid . ', 2, ' . $db->quote($fullItems[2][$k]['discountproducttitle']) . ', ' . $amount . ', ' . $netto . ', ' . $brutto . ', ' . $tax . ', ' . $final . ', ' . (int)$quantity . ', 0)';
            $db->setQuery($query);
            $db->execute();
        }

        // DISCOUNT CART DIVEDED INTO PRODUCTS
        if (isset($fullItems[3][$k]['discountcart']) && $fullItems[3][$k]['discountcart'] == 1) {

            //$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
            $query = ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = ' . (int)$orderId . ' AND order_product_id = ' . (int)$orderProductId . ' AND product_id = ' . (int)$productId . ' AND type = 3';
            $db->setQuery($query);
            $db->execute();

            $amount   = $fullItems[3][$k]['netto'];
            $netto    = $fullItems[3][$k]['netto'];
            $brutto   = $fullItems[3][$k]['brutto'];
            $tax      = $fullItems[3][$k]['tax'];
            $final    = $fullItems[3][$k]['final'];
            $quantity = $fullItems[3][$k]['quantity'];
            $catid    = $fullItems[3][$k]['catid'];

            $query = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
                . ' VALUES (' . (int)$orderId . ', ' . (int)$productId . ', ' . (int)$orderProductId . ', ' . (int)$fullItems[3][$k]['discountcartid'] . ', ' . $db->quote($k) . ', ' . (int)$catid . ', 3, ' . $db->quote($fullItems[3][$k]['discountcarttitle']) . ', ' . $amount . ', ' . $netto . ', ' . $brutto . ', ' . $tax . ', ' . $final . ', ' . (int)$quantity . ', 0)';
            $db->setQuery($query);
            $db->execute();
        }

        // COUPON DIVIDED INTO PRODUCTS
        if (isset($fullItems[4][$k]['couponcart']) && $fullItems[4][$k]['couponcart'] == 1) {

            //$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
            $query = ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = ' . (int)$orderId . ' AND order_product_id = ' . (int)$orderProductId . ' AND product_id = ' . (int)$productId . ' AND type = 4';
            $db->setQuery($query);
            $db->execute();

            $amount   = $fullItems[4][$k]['netto'];
            $netto    = $fullItems[4][$k]['netto'];
            $brutto   = $fullItems[4][$k]['brutto'];
            $tax      = $fullItems[4][$k]['tax'];
            $final    = $fullItems[4][$k]['final'];
            $quantity = $fullItems[4][$k]['quantity'];
            $catid    = $fullItems[4][$k]['catid'];
            $query    = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
                . ' VALUES (' . (int)$orderId . ', ' . (int)$productId . ', ' . (int)$orderProductId . ', ' . (int)$fullItems[4][$k]['couponcartid'] . ', ' . $db->quote($k) . ', ' . (int)$catid . ', 4, ' . $db->quote($fullItems[4][$k]['couponcarttitle']) . ', ' . $amount . ', ' . $netto . ', ' . $brutto . ', ' . $tax . ', ' . $final . ', ' . (int)$quantity . ', 0)';
            $db->setQuery($query);
            $db->execute();
        }


        return true;
    }


    public function saveRewardPoints($userId, $points, $orderBillingData, $published = 0, $type = 0) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $row = Table::getInstance('PhocacartRewardPoint', 'Table', array());

        $d              = array();
        $d['date']      = $orderBillingData['date'];//gmdate('Y-m-d H:i:s');
        $d['published'] = (int)$published;
        $d['points']    = (int)$points;
        $d['user_id']   = (int)$userId;
        $d['order_id']  = (int)$orderBillingData['id'];
        $d['title']     = Text::_('COM_PHOCACART_ORDER_NUMBER') . ' ' . self::getOrderNumber($d['order_id'], $d['date'], $orderBillingData['order_number']) . ' (' . $d['date'] . ')';
        $d['type']      = (int)$type;


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }
        return true;
    }


    public function updateNumberOfSalesOfProduct($orderProductId, $productId, $orderId) {

        // We store the number of sales of one product directly to product table
        // because of saving SQL queries in frontend, to not run sql query for each product
        $db    = Factory::getDBO();
        $query = ' SELECT SUM(quantity) FROM #__phocacart_order_products'
            . ' WHERE product_id = ' . (int)$productId
            . ' LIMIT 0,1';
        $db->setQuery($query);
        $sum = $db->loadColumn();
        if (isset($sum[0])) {
            $query = ' UPDATE #__phocacart_products'
                . ' SET sales = ' . (int)$sum[0]
                . ' WHERE id = ' . (int)$productId;
            $db->setQuery($query);
            $db->execute();

        }
        return true;
    }

    public function isThereSomeDownloadableFile() {
        return $this->downloadable_product;
    }

    public function getActionAfterOrder() {
        return $this->action_after_order;
    }

    public function getMessageAfterOrder() {
        return $this->message_after_order;
    }

    public function getDataAfterOrder() {
        return $this->data_after_order;
    }


    private function cleanTable($table, $orderId) {
        if ($table != '') {
            $db    = Factory::getDBO();
            $query = ' DELETE FROM #__' . $table . ' WHERE order_id = ' . (int)$orderId;
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        return false;
    }

    private function deleteOrder($orderId) {
        //if ($table != '') {
        $db    = Factory::getDBO();
        $query = ' DELETE FROM #__phocacart_orders WHERE id = ' . (int)$orderId;
        $db->setQuery($query);
        $db->execute();
        return true;
        //}
        return false;
    }

    /* Static part */

    public static function getOrderStatus($statusId) {

        $db    = Factory::getDBO();
        $query = ' SELECT a.title FROM #__phocacart_order_statuses WHERE id = ' . (int)$statusId . ' ORDER BY a.title';
        $db->setQuery($query);
        $status = $db->loadAssoc();
    }

    public static function getOrderDate($orderId) {

        $db    = Factory::getDBO();
        $query = ' SELECT date FROM #__phocacart_orders WHERE id = ' . (int)$orderId . ' LIMIT 1';
        $db->setQuery($query);

        $date = $db->loadResult();

        return $date;
    }

    public static function getOrderBillingData($orderId) {
        $db    = Factory::getDBO();
        $query = ' SELECT id, date, order_number, receipt_number, invoice_number, invoice_prn, invoice_date, invoice_due_date'
            . ' FROM #__phocacart_orders WHERE id = ' . (int)$orderId . ' LIMIT 1';
        $db->setQuery($query);

        $orderBillingData = $db->loadAssoc();

        return $orderBillingData;
    }

    public static function getOrderCustomerData($orderId) {
        $db    = Factory::getDBO();
        $query = ' SELECT *'
            . ' FROM #__phocacart_order_users WHERE order_id = ' . (int)$orderId . ' ORDER BY type ASC LIMIT 2';
        $db->setQuery($query);

        $data = $db->loadAssocList();

        return $data;
    }


    /**
     * @param string $type order | receipt | invoice
     * @param $orderId
     * @param mixed $date
     * @return int|mixed
     *
     * Defines which numbers will be used, if set by auto increment or by year or by month
     * Example: auto increment can be 1250 but it is new year so based on year the number will be 1 (even autoincrement will be 1251)
     * Number can start each month/year at 1 because of accounting ... be aware the numbers need to include prefixes
     */
    public static function getNumberId($type = 'order', $orderId = 0, $date = false) {

        $paramsC = PhocacartUtils::getComponentParameters();

        switch ($type) {
            case 'invoice':
                $creating_numbers = $paramsC->get('invoice_creating_numbers', 'A');
                $column           = 'invoice_number_id';
            break;

            case 'receipt':
                $creating_numbers = $paramsC->get('receipt_creating_numbers', 'A');
                $column           = 'receipt_number_id';
            break;

            case 'queue':
                $creating_numbers = $paramsC->get('queue_creating_numbers', 'A');
                $column           = 'queue_number_id';
            break;

            case 'order':
            default:
                $creating_numbers = $paramsC->get('order_creating_numbers', 'A');
                $column           = 'order_number_id';
            break;
        }


        switch ($creating_numbers) {

            case 'Y':
                // Yearly based
                $date  = !$date ? self::getOrderDate($orderId) : $date;
                $time  = strtotime($date);
                $year  = date("Y", $time);
                $db    = Factory::getDBO();
                $query = ' SELECT MAX(' . $column . ') FROM #__phocacart_orders WHERE YEAR(date) = ' . (int)$year . ' ORDER BY date LIMIT 1';

                $db->setQuery($query);

                $id = $db->loadResult();
                if (!$id || $id == 0) {
                    return 1;
                } else {
                    $id++;
                    return $id;
                }
            break;

            case 'M':
                // Montly based
                $date  = !$date ? self::getOrderDate($orderId) : $date;
                $time  = strtotime($date);
                $year  = date("Y", $time);
                $month = date("m", $time);

                $db    = Factory::getDBO();
                $query = ' SELECT MAX(' . $column . ') FROM #__phocacart_orders WHERE YEAR(date) = ' . (int)$year . ' AND MONTH(date) = ' . (int)$month . ' ORDER BY date LIMIT 1';
                $db->setQuery($query);

                $id = $db->loadResult();
                if (!$id || $id == 0) {
                    return 1;
                } else {
                    $id++;
                    return $id;
                }
            break;

            case 'D':
                // Daily based
                $date  = !$date ? self::getOrderDate($orderId) : $date;
                $time  = strtotime($date);
                $year  = date("Y", $time);
                $month = date("m", $time);
                $day   = date("d", $time);

                $db    = Factory::getDBO();
                $query = ' SELECT MAX(' . $column . ') FROM #__phocacart_orders WHERE YEAR(date) = ' . (int)$year . ' AND MONTH(date) = ' . (int)$month . ' AND DAY(date) = ' . (int)$day . ' ORDER BY date LIMIT 1';
                $db->setQuery($query);

                $id = $db->loadResult();
                if (!$id || $id == 0) {
                    return 1;
                } else {
                    $id++;
                    return $id;
                }
            break;

            case 'A':
            default:
                // OrderId stay autoincrement
                return $orderId;
            break;

        }
    }

    public static function getOrderNumber($orderId, $date = false, $orderNumber = false, $orderNumberId = false) {

        if ($orderNumber) {
            return $orderNumber;// the number is stored in database yet
        }


        $paramsC               = PhocacartUtils::getComponentParameters();
        $order_number_format   = $paramsC->get('order_number_format', '{prefix}{year}{orderid}{suffix}');
        $order_number_prefix   = $paramsC->get('order_number_prefix', '');
        $order_number_suffix   = $paramsC->get('order_number_suffix', '');
        $order_id_length_order = $paramsC->get('order_id_length_order', '10');


        $date  = !$date ? self::getOrderDate($orderId) : $date;
        $dateO = PhocacartDate::splitDate($date);

        $id = $orderId;
        if ($orderNumberId && $orderNumberId > 0) {
            $id = $orderNumberId;// not autoincrement but specific ID based on year or month see parameter: invoice_creating_numbers
        }

        $id = str_pad($id, $order_id_length_order, '0', STR_PAD_LEFT);

        $o = str_replace('{orderid}', $id, $order_number_format);
        $o = str_replace('{prefix}', $order_number_prefix, $o);
        $o = str_replace('{suffix}', $order_number_suffix, $o);
        $o = str_replace('{year}', $dateO['year'], $o);
        $o = str_replace('{month}', $dateO['month'], $o);
        $o = str_replace('{day}', $dateO['day'], $o);

        return $o;
    }

    /**
     * @param $orderId
     * @param bool $date
     * @param bool $invoiceNumber   ... is human readable number
     * @param bool $invoiceNumberId ... is number id which must not be the same like autoincrement because of montly or yearly created numbers, see parameter: invoice_creating_numbers
     * @return bool|mixed
     */
    public static function getInvoiceNumber($orderId, $date = false, $invoiceNumber = false, $invoiceNumberId = false) {

        if ($invoiceNumber) {
            return $invoiceNumber;// the number is stored in database yet
        }


        $paramsC                 = PhocacartUtils::getComponentParameters();
        $invoice_number_format   = $paramsC->get('invoice_number_format', '{prefix}{year}{orderid}{suffix}');
        $invoice_number_prefix   = $paramsC->get('invoice_number_prefix', '');
        $invoice_number_suffix   = $paramsC->get('invoice_number_suffix', '');
        $order_id_length_invoice = $paramsC->get('order_id_length_invoice', '10');

        $date  = !$date ? self::getOrderDate($orderId) : $date;
        $dateO = PhocacartDate::splitDate($date);

        $id = $orderId;

        if ($invoiceNumberId && $invoiceNumberId > 0) {
            $id = $invoiceNumberId;// not autoincrement but specific ID based on year or month see parameter: invoice_creating_numbers
        } else {
            return '';
        }
        $id = str_pad($id, $order_id_length_invoice, '0', STR_PAD_LEFT);

        $o = str_replace('{orderid}', $id, $invoice_number_format);
        $o = str_replace('{prefix}', $invoice_number_prefix, $o);
        $o = str_replace('{suffix}', $invoice_number_suffix, $o);
        $o = str_replace('{year}', $dateO['year'], $o);
        $o = str_replace('{month}', $dateO['month'], $o);
        $o = str_replace('{day}', $dateO['day'], $o);

        return $o;

        /*	$order_date 			= $orderDate != ''				? $orderDate : self::getOrderDate($orderId);
            $invoice_prefix			= $invoicePrefix != '' 			? $invoicePrefix : $paramsC->get('invoice_prefix', '');
            $invoice_number_format	= $invoiceNumberFormat != ''	? $invoiceNumberFormat : $paramsC->get( 'invoice_number_format', '');
            $invoice_number_chars	= $invoiceNumberChars != '' 	? $invoiceNumberChars : $paramsC->get( 'invoice_number_chars', 12);



            $order_date = date("Ymd", strtotime($order_date));

            $iN = $invoice_number_format;

            $iN = str_replace('{prefix}', $invoice_prefix, $iN);
            $iN = str_replace('{orderdate}', $order_date, $iN);

            $pos = strpos($iN, '{orderid}');

            if ($pos === false) {
                $l1	= strlen($iN);
                $l2	= 0;
                //$l = $invoice_number_chars - $l1 - $l2;
                $l = $l1;
                if ($l < 0) {$l = 0;}
                $iN 		= str_pad($iN, $invoice_number_chars, '0', STR_PAD_RIGHT);

            } else {
                $l1			= strlen(str_replace('{orderid}', '', $iN));
                //$l2			= strlen($orderId);
                $l 			= $invoice_number_chars - $l1;

                if ($l < 0) {$l = 0;}

                $orderId 	=  str_pad($orderId, $l, '0', STR_PAD_LEFT);
                $iN 		= str_replace('{orderid}', $orderId, $iN);
            }

            return $iN;*/

    }

    public static function getReceiptNumber($orderId, $date = false, $receiptNumber = false, $receiptNumberId = false) {

        if ($receiptNumber) {
            return $receiptNumber;// the number is stored in database yet
        }

        $paramsC                 = PhocacartUtils::getComponentParameters();
        $receipt_number_format   = $paramsC->get('receipt_number_format', '{prefix}{year}{orderid}{suffix}');
        $receipt_number_prefix   = $paramsC->get('receipt_number_prefix', '');
        $receipt_number_suffix   = $paramsC->get('receipt_number_suffix', '');
        $order_id_length_receipt = $paramsC->get('order_id_length_receipt', '10');


        $date  = !$date ? self::getOrderDate($orderId) : $date;
        $dateO = PhocacartDate::splitDate($date);

        $id = $orderId;
        if ($receiptNumberId && $receiptNumberId > 0) {
            $id = $receiptNumberId;// not autoincrement but specific ID based on year or month see parameter: invoice_creating_numbers
        }

        $id = str_pad($id, $order_id_length_receipt, '0', STR_PAD_LEFT);

        $o = str_replace('{orderid}', $id, $receipt_number_format);
        $o = str_replace('{prefix}', $receipt_number_prefix, $o);
        $o = str_replace('{suffix}', $receipt_number_suffix, $o);
        $o = str_replace('{year}', $dateO['year'], $o);
        $o = str_replace('{month}', $dateO['month'], $o);
        $o = str_replace('{day}', $dateO['day'], $o);

        return $o;
    }

    public static function getPaymentReferenceNumber($orderId, $date = false, $prmNumber = false, $idNumberA = array()) {

        if ($prmNumber) {
            return $prmNumber;// the number is stored in database yet
        }


        $app                 = Factory::getApplication();
        $paramsC             = PhocacartUtils::getComponentParameters();
        $prn_number_format   = $paramsC->get('prn_number_format', '{prefix}{year}{orderid}{suffix}');
        $prn_number_prefix   = $paramsC->get('prn_number_prefix', '');
        $prn_number_suffix   = $paramsC->get('prn_number_suffix', '');
        $order_id_length_prn = $paramsC->get('order_id_length_prn', '10');
        $prn_number_id_basis = $paramsC->get('prn_number_id_basis', 'A');


        $date  = !$date ? self::getOrderDate($orderId) : $date;
        $dateO = PhocacartDate::splitDate($date);

        $id = $orderId;
        if (!empty($idNumberA)) {
            switch ($prn_number_id_basis) {

                case 'O':
                    if (isset($idNumberA['order']) && $idNumberA['order'] > 0) {
                        $id = (int)$idNumberA['order'];
                    } else {
                        // PRN can stay empty, e.g. in case
                        // - it is created from invoice number
                        // - but invoice was not created yet - waiting for changed status
                        return '';
                    }
                break;

                case 'I':
                    if (isset($idNumberA['invoice']) && $idNumberA['invoice'] > 0) {
                        $id = (int)$idNumberA['invoice'];
                    } else {
                        return '';
                    }
                break;

                case 'R':
                    if (isset($idNumberA['receipt']) && $idNumberA['receipt'] > 0) {
                        $id = (int)$idNumberA['receipt'];
                    } else {
                        return '';
                    }
                break;
                case 'A':
                default:
                    // Do nothing, ID is autoincrement - $orderId
                break;


            }
        }

        $id = str_pad($id, $order_id_length_prn, '0', STR_PAD_LEFT);

        $o = str_replace('{orderid}', $id, $prn_number_format);
        $o = str_replace('{prefix}', $prn_number_prefix, $o);
        $o = str_replace('{suffix}', $prn_number_suffix, $o);
        $o = str_replace('{year}', $dateO['year'], $o);
        $o = str_replace('{month}', $dateO['month'], $o);
        $o = str_replace('{day}', $dateO['day'], $o);

        return $o;
    }


    public static function getQueueNumber($orderId, $date = false, $queueNumber = false, $queueNumberId = false) {

        if ($queueNumber) {
            return $queueNumber;// the number is stored in database yet
        }


        $paramsC               = PhocacartUtils::getComponentParameters();
        $queue_number_format   = '{prefix}{queueid}{suffix}';
        $queue_number_prefix   = $paramsC->get('queue_number_prefix', '');
        $queue_number_suffix   = $paramsC->get('queue_number_suffix', '');
        $queue_number_length = $paramsC->get('queue_number_length', '5');


        $date  = !$date ? self::getOrderDate($orderId) : $date;
        $dateO = PhocacartDate::splitDate($date);

        $id = $orderId;
        if ($queueNumberId && $queueNumberId > 0) {
            $id = $queueNumberId;// not autoincrement but specific ID based on year or month or day see parameter: queue_creating_numbers
        }

        $id = str_pad($id, $queue_number_length, '0', STR_PAD_LEFT);

        $o = str_replace('{queueid}', $id, $queue_number_format);
        $o = str_replace('{prefix}', $queue_number_prefix, $o);
        $o = str_replace('{suffix}', $queue_number_suffix, $o);
        //$o = str_replace('{year}', $dateO['year'], $o);
        //$o = str_replace('{month}', $dateO['month'], $o);
        //$o = str_replace('{day}', $dateO['day'], $o);

        return $o;
    }

    public static function getInvoiceDueDate($orderId, $date = false, $dueDate = false, $formatOutput = '') {

        if ($dueDate) {
            if ($formatOutput != '') {
                return HTMLHelper::date($dueDate, $formatOutput);
            }
            return $dueDate;// the due date is stored in database yet
        }


        $paramsC               = PhocacartUtils::getComponentParameters();
        $invoice_due_date_days = $paramsC->get('invoice_due_date_days', 5);

        $date = !$date ? self::getOrderDate($orderId) : $date;

        $dateTime = new DateTime($date);
        $dateTime->add(new DateInterval('P' . (int)$invoice_due_date_days . 'D'));
        //return $dateTime->format('Y-m-d h:m:s');
        // default format output: 'DATE_FORMAT_LC4'
        if ($formatOutput != '') {
            return HTMLHelper::date($dateTime->format('Y-m-d h:m:s'), $formatOutput);
        } else {
            return $dateTime->format('Y-m-d h:m:s');
        }
    }

    public static function getInvoiceDate($orderId, $date = false, $formatOutput = '') {

        $date = !$date ? self::getOrderDate($orderId) : $date;

        $dateTime = new DateTime($date);

        if ($formatOutput != '') {
            return HTMLHelper::date($dateTime->format('Y-m-d h:m:s'), $formatOutput);
        } else {
            return $dateTime->format('Y-m-d h:m:s');
        }
    }

    /**
     *
     * @param string $oIdS - order IDs separated by comma
     * @return array
     *
     * Get all total items by selected order Ids
     * e.g. Order 1 -> total 1 (netto), total 2 (vat), total 3(brutton), etc.
     */

    public static function getItemsTotal($oIdS = '') {

        $db     = Factory::getDBO();
        $wheres = array();
        if ($oIdS != '') {
            $wheres[] = 'a.order_id IN (' . $oIdS . ')';
        }

        $query = 'SELECT a.id, a.order_id, a.item_id, a.item_id_c, a.item_id_r, a.item_id_p, a.title, a.title_lang, a.title_lang_suffix, a.title_lang_suffix, a.type, a.amount, a.amount_currency'
            . ' FROM #__phocacart_order_total AS a';
        if (!empty($wheres)) {
            $query .= ' WHERE ' . implode(' AND ', $wheres);
        }
        $db->setQuery($query);


        $items = $db->loadAssocList();
        return $items;

    }


    public static function getItemsTaxRecapitulation($oIdS = '') {

        $db     = Factory::getDBO();
        $wheres = array();
        if ($oIdS != '') {
            $wheres[] = 'a.order_id IN (' . $oIdS . ')';
        }

        $query = 'SELECT a.id, a.order_id, a.item_id, a.item_id_c, a.item_id_r, a.item_id_p, a.title, a.type, a.amount_netto, a.amount_tax, a.amount_brutto, a.amount_brutto_currency'
            . ' FROM #__phocacart_order_tax_recapitulation AS a';
        if (!empty($wheres)) {
            $query .= ' WHERE ' . implode(' AND ', $wheres);
        }
        $db->setQuery($query);


        $items = $db->loadAssocList();
        return $items;

    }

    /*
     * This method is used  when storing order but even when chaning status
     * Because invoice number can be set by status. E.g. invoice number will be set only when status is set to "completed"
     * When storing order and the status is pending, invoice number will be not set
     * This only applies to when invoice number is created based on month or year basis
     * Invoice number can be then created by chaning status - model phocacarteditstatus, model phocacartorder, or payment methods which call: PhocacartOrderStatus::changeStatusInOrderTable()
     *
     */

    public static function storeOrderReceiptInvoiceId($id, $date, $statusId, $docs = array()) {

        $app = Factory::getApplication();
        $db  = Factory::getDbo();

        $d       = array();
        $d['id'] = $id;


        // if we create the data by order status changes, we don't use new date but the one which exists in order yet
        // so when using this function from order statuses, the date is false to get the right date here
        $date      = !$date ? self::getOrderDate($id) : $date;
        $d['date'] = $date;
        // Don't change the date of order but change date for oder, receipt or delivery note number
        $dateNow = Factory::getDate()->toSql();

        // Will we create an invoice?
        $paramsC                       = PhocacartUtils::getComponentParameters();
        $invoice_creating_numbers      = $paramsC->get('invoice_creating_numbers', 'A');
        $invoice_creating_status_based = $paramsC->get('invoice_creating_status_based', 0);

        // Invoice is created by year or month basis - we can create invoice id by status
        if ($invoice_creating_numbers == 'Y' || $invoice_creating_numbers == 'M') {

            // It is enabled in parameters that invoice is made by status
            if ((int)$invoice_creating_status_based > 0) {

                // Check the status
                if ((int)$statusId > 0 && (int)$invoice_creating_status_based == (int)$statusId) {
                    // we can create invoice
                } else {
                    // don't create the invoice because it should be only created when
                    // specific status is set but it is not set yet

                    if (($key = array_search('I', $docs)) !== false) {
                        unset($docs[$key]);
                    }
                }
            }
        }

        if (!in_array('O', $docs) && !in_array('R', $docs) && !in_array('I', $docs)) {

            // This can happen when we changed the status and we need to check if invoice
            // should be created. By chaning status we are only asking to create invoice
            // number. But in case the order status does not fit the status set in options
            // invoice will be not created. And because we didn't ask for receipt number
            // and order number, nothing will be changed.
            return false;
        }


        $orderNumberId   = 0;
        $receiptNumberId = 0;
        $invoiceNumberId = 0;
        $queueNumberId   = 0;

        // Which numbers will be active, set by auto increment or by year or by month
        // Example: auto increment can be 1250 but it is new year so based on year the number will be 1 (even autoincrement will be 1251)
        if (in_array('O', $docs)) {

            // Order Number
            $d['date']            = $date;
            $d['order_number_id'] = PhocaCartOrder::getNumberId('order', $id, $dateNow);
            // Human readable numbers inclusive all prefixes, suffixes, etc.
            $d['order_number'] = PhocacartOrder::getOrderNumber($id, $dateNow, false, $d['order_number_id']);
            $orderNumberId     = $d['order_number_id'];

            // Queue Number
            $d['queue_number_id']   = PhocaCartOrder::getNumberId('queue', $id, $dateNow);
            $d['queue_number']      = PhocacartOrder::getQueueNumber($id, $dateNow, false, $d['queue_number_id']);
            $queueNumberId     = $d['queue_number_id'];
        }

        if (in_array('R', $docs)) {
            $d['date']              = $date;
            $d['receipt_number_id'] = PhocaCartOrder::getNumberId('receipt', $id, $dateNow);
            $d['receipt_number']    = PhocacartOrder::getReceiptNumber($id, $dateNow, false, $d['receipt_number_id']);
            $receiptNumberId        = $d['receipt_number_id'];
        }

        // If there are data yet, don't overwrite them
        // Order and Receipt are not changed by status, so this can be checked before invoice
        // and PRN
        $query = ' SELECT date, invoice_number_id, invoice_number, invoice_prn, invoice_date, invoice_due_date, invoice_time_of_supply, tracking_date_shipped, required_delivery_time  FROM #__phocacart_orders WHERE id = ' . (int)$id . ' ORDER BY id LIMIT 1';
        $db->setQuery($query);
        $orderData = $db->loadAssoc();

        // SEE: administrator/components/com_phocacart/tables/phocacartorder.php
        // some date columns cannot have null values, as null values will be transformed to 0000-00-00 00:00:00 in check function
        // instead of leaving yet stored data in database (values with null standardly do not change current data in database)
        // $d['date'] - defined previously
        $d['tracking_date_shipped'] = $orderData['tracking_date_shipped'];
        $d['invoice_date']          = $orderData['invoice_date'];
        $d['invoice_due_date']      = $orderData['invoice_due_date'];
        $d['invoice_time_of_supply']= $orderData['invoice_time_of_supply'];
        $d['required_delivery_time']= $orderData['required_delivery_time'];

        if (in_array('I', $docs)) {
            if (!isset($orderData['date']) || (isset($orderData['date']) && !PhocacartDate::activeDatabaseDate($orderData['date']))) {
                $d['date'] = $date;
            }


            if (!isset($orderData['invoice_number_id']) || (isset($orderData['invoice_number_id']) && (int)$orderData['invoice_number_id'] == 0)) {
                $d['invoice_number_id'] = PhocaCartOrder::getNumberId('invoice', $id, $dateNow);
                $invoiceNumberId        = $d['invoice_number_id'];
            } else {
                $d['invoice_number_id'] = (int)$orderData['invoice_number_id'];
                $invoiceNumberId        = $d['invoice_number_id'];
            }

            if (!isset($orderData['invoice_number']) || (isset($orderData['invoice_number']) && $orderData['invoice_number'] == '')) {

                if (isset($d['invoice_number_id']) && (int)$d['invoice_number_id'] > 0){

                    // Be aware - order made in last year, order status change in new year
                    // Be 100% - invoice numbers are not in conflict with invoice number from other year
                    $dateOrder = PhocacartOrder::getOrderDate($id);

                    $d['invoice_number'] = PhocacartOrder::getInvoiceNumber($id, $dateOrder, false, $d['invoice_number_id']);
                }

            }


            if (!isset($orderData['invoice_date']) || (isset($orderData['invoice_date']) && !PhocacartDate::activeDatabaseDate($orderData['invoice_date']))) {
                $d['invoice_date'] = $dateNow;
            }

            if (!isset($orderData['invoice_due_date']) || (isset($orderData['invoice_due_date']) && !PhocacartDate::activeDatabaseDate($orderData['invoice_due_date']))) {
                $d['invoice_due_date'] = PhocacartOrder::getInvoiceDueDate($id, $dateNow);
            }

            if (!isset($orderData['invoice_time_of_supply']) || (isset($orderData['invoice_time_of_supply']) && !PhocacartDate::activeDatabaseDate($orderData['invoice_time_of_supply']))) {
                $d['invoice_time_of_supply'] = $dateNow;
            }

        }

        // Create Payment Reference Number based on different numbers - parameter is used in getPaymentReferenceNumber function
        // So it even be created if there is no invoice number

        // Don't create it if it is created yet. E.g. status wants to create invoice
        // but PRN was created previously by order or receipt ID

        if (!isset($orderData['invoice_prn']) || (isset($orderData['invoice_prn']) && $orderData['invoice_prn'] == '')) {
            $idNumberA = array(
                'order' => $orderNumberId,
                'receipt' => $receiptNumberId,
                'invoice' => $invoiceNumberId
            );
            $prn       = PhocacartOrder::getPaymentReferenceNumber($id, $dateNow, false, $idNumberA);
            if ($prn != '') {
                // PRN is not included in database and it exits
                $d['invoice_prn'] = $prn;
            }
        }

        // Store the items
        // This method can be called by storing order or by chaning statuses, so
        // add only the variables which really should be added and don't
        // overwrite existing variables with empty values
        // This is why some $d array keys are not defined
        // E.g. if this method is called by chaning status, order and receipt keys are inactive

        $row = Table::getInstance('PhocacartOrder', 'Table', array());


        if (!$row->bind($d)) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        // SEE: administrator/components/com_phocacart/tables/phocacartorder.php
        // some date columns cannot have null values, as null values will be transformed to 0000-00-00 00:00:00 in check function
        // instead of leaving yet stored data in database (values with null standardly do not change current data in database)

        if (!$row->check()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }

        if (!$row->store()) {
            //throw new Exception($row->getError());
            $msg = Text::_($row->getError());
            $app->enqueueMessage($msg, 'error');
            return false;
        }


        if (!isset($d['order_number_id'])) {
            $d['order_number_id'] = 0;
        }
        if (!isset($d['receipt_number_id'])) {
            $d['receipt_number_id'] = 0;
        }
        if (!isset($d['invoice_number_id'])) {
            $d['invoice_number_id'] = 0;
        }
        if (!isset($d['queue_number_id'])) {
            $d['queue_number_id'] = 0;
        }

        if (!isset($d['order_number'])) {
            $d['order_number'] = '';
        }
        if (!isset($d['receipt_number'])) {
            $d['receipt_number'] = '';
        }
        if (!isset($d['invoice_number'])) {
            $d['invoice_number'] = '';
        }
        if (!isset($d['queue_number'])) {
            $d['queue_number'] = '';
        }
        return $d;
    }

    public static function getOrder($orderId, $orderToken, $orderUser) {

        $db                 = Factory::getDbo();
		$pC 			    = PhocacartUtils::getComponentParameters();
		$orderGuestAccess	= $pC->get( 'order_guest_access', 0 );
		if ($orderGuestAccess == 0) {
			$orderToken = '';
		}

        $wheres		= array();
		$wheres[] 	= ' o.published = 1';
		if ($orderToken != '') {
			$wheres[]	= ' o.order_token = '.$db->quote($orderToken);
		} else {
			$wheres[]	= ' o.user_id = '.(int)$orderUser;
		}
		$wheres[]	= ' t.type = '.$db->quote('brutto');

        $wheres[]	= ' o.id = '.(int)$orderId;

		$query = ' SELECT o.*,'
		.' os.title AS status_title,'
		.' t.amount AS total_amount,'
		.' s.id AS shippingid, s.title AS shippingtitle, s.code AS shippingcode, s.tracking_link as shippingtrackinglink, s.tracking_description as shippingtrackingdescription, os.orders_view_display as ordersviewdisplay,'
        .' p.id AS paymentid, p.title AS paymenttitle, p.code AS paymentcode'
		.' FROM #__phocacart_orders AS o'
		.' LEFT JOIN #__phocacart_order_statuses AS os ON os.id = o.status_id'
		.' LEFT JOIN #__phocacart_order_total AS t ON o.id = t.order_id'
		.' LEFT JOIN #__phocacart_shipping_methods AS s ON s.id = o.shipping_id'
        .' LEFT JOIN #__phocacart_payment_methods AS p ON p.id = o.payment_id'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY o.id'
        . ' LIMIT 1';

		$db->setQuery($query);
        $order = $db->loadAssoc();
        return $order;
	}

    public static function getOrderProducts($orderId) {

        $db         = Factory::getDbo();
        $wheres		= array();
        $wheres[]	= ' p.order_id = '.(int)$orderId;

		$query = ' SELECT p.*'
		.' FROM #__phocacart_order_products AS p'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY p.id';

		$db->setQuery($query);
        $orderProducts = $db->loadAssocList();

        return $orderProducts;
	}

    public static function getOrderUser($orderId) {

        $db         = Factory::getDbo();
        $wheres		= array();
        $wheres[]	= ' u.order_id = '.(int)$orderId;

		$query = ' SELECT u.*'
		.' FROM #__phocacart_order_users AS u'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY u.id';

		$db->setQuery($query);
        $orderProducts = $db->loadAssocList();

        return $orderProducts;
	}

    public static function getOrderTotal($orderId, $types = array()) {

        $db         = Factory::getDbo();
        $wheres		= array();
        $wheres[]	= ' t.order_id = '.(int)$orderId;

        if (!empty($types)) {
            $typesString = "'" . implode("','", $types) . "'";
            if ($typesString != '') {
                $wheres[] = ' t.type IN (' . $typesString . ')';
            }
        }

		$query = ' SELECT t.*'
		.' FROM #__phocacart_order_total AS t'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY t.id';

		$db->setQuery($query);
        $orderTotals = $db->loadAssocList('type');

        return $orderTotals;
	}

    public static function getOrderAttributesByOrderedProductId($orderedProductId) {

        $db         = Factory::getDbo();
        $wheres		= array();
        $wheres[]	= ' a.order_product_id = '.(int)$orderedProductId;

		$query = ' SELECT a.*'
		.' FROM #__phocacart_order_attributes AS a'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY a.id';

		$db->setQuery($query);
        $attributes = $db->loadAssocList();

        return $attributes;
	}

}
