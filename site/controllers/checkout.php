<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

class PhocaCartControllerCheckout extends FormController
{
    public $t;

    /*
     * Add product to cart
     */
    public function add() {

        Session::checkToken() or jexit('Invalid Token');

        $app               = Factory::getApplication();
        $item              = array();
        $item['id']        = $this->input->get('id', 0, 'int');
        $item['catid']     = $this->input->get('catid', 0, 'int');
        $item['quantity']  = $this->input->get('quantity', 0, 'int');
        $item['return']    = $this->input->get('return', '', 'string');
        $item['attribute'] = $this->input->get('attribute', array(), 'array');


        if ((int)$item['id'] > 0) {

            $itemP = PhocacartProduct::getProduct((int)$item['id'], $item['catid']);

            if (!empty($itemP)) {

                // Price (don't display add to cart when price is zero)
                $price  = new PhocacartPrice();
                $priceP = $price->getPriceItems($itemP->price, $itemP->taxid, $itemP->taxrate, $itemP->taxcalculationtype, $itemP->taxtitle, 0, '', 1, 1, $itemP->group_price, $itemP->taxhide);
                $aA     = PhocacartAttribute::sanitizeAttributeArray($item['attribute']);
                $price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $itemP, 1);
                $price->correctMinusPrice($priceP);
                $priceA = isset($priceP['brutto']) ? $priceP['brutto'] : 0;

                // Stock (don't display add to cart when stock is zero)
                $stockStatus = array();

                $stock       = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $itemP, 1);

                $rights                                 = new PhocacartAccessRights();
                $this->t['can_display_addtocart']       = $rights->canDisplayAddtocartAdvanced($itemP);
                $this->t['can_display_addtocart_price'] = $rights->canDisplayAddtocartPrice($itemP, $priceA);
                $this->t['can_display_addtocart_stock'] = $rights->canDisplayAddtocartStock($itemP, $stock);

                if (!$this->t['can_display_addtocart']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $app->redirect(base64_decode($item['return']));
                }

                if (!$this->t['can_display_addtocart_price']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRICE_IS_ZERO'), 'error');
                    $app->redirect(base64_decode($item['return']));
                }

                if (!$this->t['can_display_addtocart_stock']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $app->enqueueMessage(Text::_('COM_PHOCACART_STOCK_IS_EMPTY'), 'error');
                    $app->redirect(base64_decode($item['return']));
                }


                $cart = new PhocacartCart();
                $added = $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);

                if ($added) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART'), 'message');
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
                }
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_FOUND'), 'error');
            }
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_SELECTED'), 'error');
        }

        //$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
        $app->redirect(base64_decode($item['return']));
    }

    /*
     * Change currency
     */
    public function currency() {
        Session::checkToken() or jexit('Invalid Token');
        $app            = Factory::getApplication();
        $item           = array();
        $item['id']     = $this->input->get('id', 0, 'int');
        $item['return'] = $this->input->get('return', '', 'string');

        //$currency = new PhocacartCurrency();
        //$currency->setCurrentCurrency((int)$item['id']);

        PhocacartCurrency::setCurrentCurrency((int)$item['id']);

        $app->redirect(base64_decode($item['return']));
    }

    /*
     * Save billing and shipping address
     */

    public function saveaddress() {

        Session::checkToken() or jexit('Invalid Token');
        $app                    = Factory::getApplication();
        $item                   = array();
        $item['return']         = $this->input->get('return', '', 'string');
        $item['jform']          = $this->input->get('jform', array(), 'array');
        $item['phcheckoutbsas'] = $this->input->get('phcheckoutbsas', false, 'string');



        $paramsC                       = PhocacartUtils::getComponentParameters();
        $delivery_billing_same_enabled = $paramsC->get('delivery_billing_same_enabled', 0);

        if ((int)$delivery_billing_same_enabled == -1) {
            // if some shipping rule is based on shipping address and "delivery_billing_same_enabled" parameter is completery removed
            // the check all the shipping rules completely
            $item['phcheckoutbsas'] = false;
        }

        $guest     = PhocacartUserGuestuser::getGuestUser();
        $error     = 0;
        $msgSuffix = '<span id="ph-msg-ns" class="ph-hidden"></span>';
        if (!empty($item['jform'])) {

            // Form Data
            $billing     = array();
            $shipping    = array();
            $shippingPhs = array();// shipping including postfix



            // Get all checkboxes, because they are not sent in POST if they are set to not checked CHECKBOXMISSING
            $checkboxes = PhocacartUser::getAllCheckboxesFromFormFields();

            if (!empty($checkboxes)) {
                foreach($checkboxes as $k => $v) {
                    if ($v->display_billing == 1) {
                        $title = $v->title;

                        if(!isset($item['jform'][$title])) {
                            $item['jform'][$title] = false;
                        }
                    }

                    if ($v->display_shipping == 1) {
                        $title = $v->title. '_phs';

                        if(!isset($item['jform'][$title])) {
                            $item['jform'][$title] = false;
                        }
                    }
                }
            }
            // ---

            $bas         = PhocacartUser::convertAddressTwo($item['jform']);
            $billing     = $bas[0];
            $shipping    = $bas[1];
            $shippingPhs = $bas[2];


            // Form Items
            $fI    = new PhocacartFormItems();
            $items = $fI->getFormItems(1, 1, 0);
            $model = $this->getModel('checkout');
            $form  = $model->getForm();

            if (empty($form)) {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NO_FORM_LOADED') . $msgSuffix, 'error');
                $app->redirect(base64_decode($item['return']));
                return false;
            }


            // Which filds will be validated or required
            // Reqiuired and Validate is handled differently because if shipping address is d
            if (!empty($form->getFieldset('user'))) {

                foreach ($form->getFieldset('user') as $field) {

                    $name = $field->fieldname;

                    if ($field->fieldname == 'email' || $field->fieldname == 'email_phs') {
                        // This is not a registration: Checkout or Account (first form without option to change email)
                        // Email is not stored by registered users
                        // Email by guests can be the same like stored in database (e.g. guest orders without login)
                        $form->setFieldAttribute($field->fieldname, 'unique', 'false');
                    }

                    if (strtolower($field->type) == 'checkbox') {
                        // when checkbox is unchecked, it is not sent in form
                        // so we need to create it with false
                        $fieldName = $field->fieldname;
                        if(!isset($item['jform'][$fieldName])) {

                            $item['jform'][$fieldName] = false;
                        }
                    }




                    if (isset($billing[$name])) {
                        // such field exists in billing, require it if set in rules, validate
                    } else if (isset($shippingPhs[$name])) {
                        // such field exists in shipping, require it if set in rules, validate


                        // Don't check the shipping as it is not required
                        if ($item['phcheckoutbsas']) {

                            // CHECKBOX IS ON
                            $billing['ba_sa']  = 1;
                            $shipping['ba_sa'] = 1;

                            $form->setFieldAttribute($field->fieldname, 'required', 'false');
                            $form->setFieldAttribute($field->fieldname, 'validate', '');
                        } else {
                            // CHECKBOX IS OFF
                            $billing['ba_sa']  = 0;
                            $shipping['ba_sa'] = 0;

                        }

                    } else {

                        // such field does not exist, don't require it, don't validate
                        $form->setFieldAttribute($field->fieldname, 'required', 'false');
                        $form->setFieldAttribute($field->fieldname, 'validate', '');

                    }

                    if ((int)$field->maxLength > 0) {
                        $form->setFieldAttribute($field->fieldname, 'validate', 'PhocaCartMaxlength');
                    }
                }
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NO_FORM_LOADED') . $msgSuffix, 'error');
                $app->redirect(base64_decode($item['return']));
                return false;
            }

        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_NO_DATA_STORED') . $msgSuffix, 'error');// Not used: COM_PHOCACART_ERROR_NO_DATA_STORED
            $app->redirect(base64_decode($item['return']));
            return false;                                                            // as in fact this can be possible
        }                                                                            // that admin does not require any data


        $data = $item['jform'];
        $data = $model->validate($form, $data);

        if ($data === false) {

            $errors = $model->getErrors();

            for ($i = 0, $n = count($errors); $i < $n && $i < 20; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }

            }

            $this->setRedirect(base64_decode($item['return']));
            return false;
        }

        if ($guest) {
            if ($item['phcheckoutbsas']) {
                $item['jform']['ba_sa'] = 1;
                foreach ($item['jform'] as $k => $v) {
                    $pos = strpos($k, '_phs');
                    if ($pos === false) {

                    } else {
                        unset($item['jform'][$k]);
                    }
                }
            }

            if (!$model->saveAddressGuest($item['jform'])) {
                $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg . $msgSuffix, 'error');
                $error = 1;
            }

        } else {


            if (!empty($billing)) {
                if (!$model->saveAddress($billing)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                    $error = 1;
                } else {
                    //$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    //$app->enqueueMessage($msg, 'message');
                    // Waiting for shipping
                }
                //$app->redirect(base64_decode($item['return']));
            }

            // Don't store shipping address when delivery and billing address is the same
            if (!empty($shipping) && !$item['phcheckoutbsas']) {
                if (!$model->saveAddress($shipping, 1)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                    $error = 1;
                } else {
                    //$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    //$app->enqueueMessage($msg, 'message');
                    // Waiting for shipping
                }
                //$app->redirect(base64_decode($item['return']));
            }
        }


        // Remove shipping because shipping methods can change while chaning address
        $cart = new PhocacartCartRendercheckout();
        $cart->setType(array(0, 1));
        $cart->setFullItems();
        $cart->updateShipping();// will be decided if shipping or payment will be removed
        $cart->updatePayment();

        $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
        if ($error != 1) {
            $app->enqueueMessage($msg, 'message');
        }

        $app->redirect(base64_decode($item['return']));
    }


    /*
    * Save shipping method
    */

    public function saveshipping() {

        Session::checkToken() or jexit('Invalid Token');
        $app                   = Factory::getApplication();
        $item                  = array();
        $item['return']        = $this->input->get('return', '', 'string');
        $item['phshippingopt'] = $this->input->get('phshippingopt', array(), 'array');
        // Must be array because one shipping plugin can be used in more methods
        $item['phshippingmethodfield'] = $this->input->get('phshippingmethodfield', array(), 'array');
        $guest                 = PhocacartUserGuestuser::getGuestUser();
        $msgSuffix             = '<span id="ph-msg-ns" class="ph-hidden"></span>';

        $checkPayment = 0;
        $idShipping = 0;
        if (!empty($item['phshippingopt']) && isset($item['phshippingopt'][0]) && (int)$item['phshippingopt'][0] > 0) {

            $model = $this->getModel('checkout');

            $idShipping = (int)$item['phshippingopt'][0];
            $shippingParams = isset($item['phshippingmethodfield'][$idShipping]) ? $item['phshippingmethodfield'][$idShipping] : [];


            if ($guest) {
                if (!$model->saveShippingGuest($idShipping, $shippingParams)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    $app->enqueueMessage($msg, 'message');
                    $checkPayment = 1;
                }

            } else {
                if (!$model->saveShipping($idShipping, $shippingParams)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    $app->enqueueMessage($msg, 'message');
                    $checkPayment = 1;
                }
            }

        } else {
            $msg = Text::_('COM_PHOCACART_NO_SHIPPING_METHOD_SELECTED');
            $app->enqueueMessage($msg . $msgSuffix, 'error');
        }


        // CHECK PAYMENT
        if ($checkPayment == 1) {
            //PhocacartPayment::removePayment($guest, 0);// Don't remove coupon by guests
            $cart = new PhocacartCartRendercheckout();
            $cart->setInstance(2);//checkout
            $cart->setType(array(0, 1));
            $cart->setFullItems();
            $cart->updatePayment($idShipping);// check payment in cart if it is valid
        }


        $app->redirect(base64_decode($item['return']));
    }

    /*
     * Save payment method and coupons
     */

    public function savepayment() {

        Session::checkToken() or jexit('Invalid Token');
        $app                  = Factory::getApplication();
        $item                 = array();
        $item['return']       = $this->input->get('return', '', 'string');
        $item['phpaymentopt'] = $this->input->get('phpaymentopt', array(), 'array');
        // Must be array because one shipping plugin can be used in more methods
        $item['phpaymentmethodfield'] = $this->input->get('phpaymentmethodfield', array(), 'array');
        $item['phcoupon']     = $this->input->get('phcoupon', -1, 'string');// -1 ... no form data, '' ... form data yes but empty (e.g. when removing coupon)
        $item['phreward']     = $this->input->get('phreward', -1, 'int');   // -1 ... no form data, 0 ... form data yes but it is set to not use points (0)
        $guest                = PhocacartUserGuestuser::getGuestUser();
        $user                 = PhocacartUser::getUser();
        $params               = $app->getParams();
        $msgSuffix            = '<span id="ph-msg-ns" class="ph-hidden"></span>';
        $guest_checkout       = $params->get('guest_checkout', 0);
        $enable_coupons       = $params->get('enable_coupons', 2);

        // Coupon
        // 1) we save payment without coupon form --> phcoupon = -1 ==> $couponId = -1 (in model the coupon will be ignored when saving to not change current value
        // 2) we save payment with coupon form and ask the coupon class for $couponId
        // 2a) $couponId == -2 ... empty string was set which means to remove coupon ==> $couponId = 0
        // 2b) $couponId == 0 ... coupon is not valid ==> $couponId = 0
        // 2c) $couponId > 0 ... coupon is valid ==> $couponId > 0
        //
        // What is the difference between 2a) and 2b) - in database there is no difference but we need to differentiate messages for the customers (coupon empty vs. coupon not valid)
        // IMPORTANT:
        // $item['phcoupon'] = -1 ... coupon is not included in sent payment form
        // $couponId = -1 ... coupon will be ignored in model when saving to database because to not change the current value
        // $coupoiId = -2 ... coupon was included in sent payment form but it was empty (empty means that user just want to remove it), we need -2 for message only, in database we set it to 0

        $idPayment = 0;
        if (!empty($item['phpaymentopt']) && isset($item['phpaymentopt'][0]) && (int)$item['phpaymentopt'][0] > 0) {

            $idPayment = (int)$item['phpaymentopt'][0];
            $paymentParams = isset($item['phpaymentmethodfield'][$idPayment]) ? $item['phshippingmethodfield'][$idPayment] : [];

            // Coupon
            if ($item['phcoupon'] === -1) {
                $couponId = -1;// coupon data was not sent in the form, don't touch its data in db
            } else {
                $msgExists = 0;
                $couponId  = $this->getCouponIdByCouponCode($item['phcoupon']);

                // Coupons disabled
                if ($enable_coupons == 0 && $item['phcoupon'] != '' && $item['phcoupon'] !== -1) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_APPLYING_COUPONS_IS_DISABLED') . $msgSuffix, 'error');
                    $couponId  = 0;// Remove coupon
                    $msgExists = 1;//
                }

                // Cupon only allowed for logged in users or guest checkout
                // Guest Checkout is still not enabled so we have message for a) not logged in users or b) not started guest checkout users
                if ($enable_coupons == 2) {
                    if (!$guest) {
                        if ((int)$user->id < 1) {
                            if ($guest_checkout == 1) {
                                $app->enqueueMessage(Text::_('COM_PHOCACART_PLEASE_LOG_IN_OR_ENABLE_GUEST_CHECKOUT_TO_APPLY_COUPON_FIRST') . $msgSuffix, 'error');
                                $msgExists = 1;
                            } else {
                                $app->enqueueMessage(Text::_('COM_PHOCACART_PLEASE_LOG_IN_TO_APPLY_COUPON_FIRST') . $msgSuffix, 'error');
                                $msgExists = 1;
                            }
                            $couponId = 0;
                        }
                    }
                }

                if ($couponId === -2) {
                    // Coupon code is empty which means we remove the coupon code
                    $msg = Text::_('COM_PHOCACART_COUPON_NOT_SET');
                    $app->enqueueMessage($msg, 'message');
                    $couponId = 0;// Remove coupon
                } else if (!$couponId) {
                    // Coupon code just not valid
                    if ($msgExists == 1) {
                        // error message set so don't add another message
                    } else {
                        $msg = Text::_('COM_PHOCACART_COUPON_INVALID_EXPIRED_REACHED_USAGE_LIMIT');
                        $app->enqueueMessage($msg . $msgSuffix, 'error');
                    }


                    $couponId = 0;// Possible feature request - couponId can be set to -1 to be ignored when saving. E.g. not valied coupon will not remove previously added valid coupon
                } else {
                    // Coupon code successfuly tested
                    $msg = Text::_('COM_PHOCACART_COUPON_ADDED');
                    $app->enqueueMessage($msg, 'message');
                }
            }


            // Reward Points
            if ($item['phreward'] === -1) {
                $rewards['used'] = -1;// reward points not sent in the form, don't touch its data in db
            } else {

                $rewards = $this->getRewardPointsByRewardPointsCode($item['phreward']);
                if ($rewards['used'] === false) {
                    $msg = Text::_('COM_PHOCACART_REWARD_POINTS_NOT_ADDED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $msg = Text::_('COM_PHOCACART_REWARD_POINTS_ADDED');
                    $app->enqueueMessage($msg, 'message');
                }
            }


            $model = $this->getModel('checkout');

            if ($guest) {
                // 1) GUEST
                // Guest enabled
                if (!$model->savePaymentAndCouponGuest($idPayment, $couponId, $paymentParams)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    $app->enqueueMessage($msg, 'message');
                }
            } else if ((int)$user->id < 1) {

                // 2) PRE-GUEST/PRE-LOGIN - NOT LOGGED IN OR STILL NOT ENABLED GUEST CHECKOUT
                // Guest not enabled yet MOVECOUPON
                if (!$model->savePaymentAndCouponGuest($idPayment, $couponId, $paymentParams)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');

                } else {
                    $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    $app->enqueueMessage($msg, 'message');

                }

            } else {
                // 3) LOGGED IN USER
                if (!$model->savePaymentAndCouponAndReward($idPayment, $couponId, $rewards['used'], $paymentParams)) {
                    $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                    $app->enqueueMessage($msg, 'message');
                }
            }


        } else {
            $msg = Text::_('COM_PHOCACART_NO_PAYMENT_METHOD_SELECTED');
            $app->enqueueMessage($msg . $msgSuffix, 'error');
        }
        $app->redirect(base64_decode($item['return']));
    }

    /*
     * Save coupon only
     */

    public function savecoupon() {


        /* There are following situations:
        a) user is not logged in and will log in - regarding coupon user is taken as guest checkout (internally in session - so even guest checkout is disabled)
        b) user is not logged in and will enable guest checkout - regarding coupon user is taken as guestcheckou (internally in session - so even guest checkout is disabled)
        c) user is logged in
        d) user enabled guest checkout
        */

        Session::checkToken() or jexit('Invalid Token');
        $app              = Factory::getApplication();
        $item             = array();
        $item['return']   = $this->input->get('return', '', 'string');
        $item['phcoupon'] = $this->input->get('phcoupon', '', 'string');
        $guest            = PhocacartUserGuestuser::getGuestUser();
        $user             = PhocacartUser::getUser();
        $params           = $app->getParams();
        $msgSuffix        = '<span id="ph-msg-ns" class="ph-hidden"></span>';
        $guest_checkout   = $params->get('guest_checkout', 0);
        $enable_coupons   = $params->get('enable_coupons', 2);


        // Coupons disabled
        if ($enable_coupons == 0) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_APPLYING_COUPONS_IS_DISABLED'), 'error');
            $app->redirect(base64_decode($item['return']));
        }


        // Cupon only allowed for logged in users or guest checkout
        // Guest Checkout is still not enabled so we have message for a) not logged in users or b) not started guest checkout users
        if ($enable_coupons == 2) {
            if (!$guest) {
                if ((int)$user->id < 1) {
                    if ($guest_checkout == 1) {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_PLEASE_LOG_IN_OR_ENABLE_GUEST_CHECKOUT_TO_APPLY_COUPON_FIRST'), 'error');
                    } else {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_PLEASE_LOG_IN_TO_APPLY_COUPON_FIRST'), 'error');
                    }
                    $app->redirect(base64_decode($item['return']));
                }
            }
        }

        $couponId = $this->getCouponIdByCouponCode($item['phcoupon']);

        $msgError = 0;
        if ($couponId === -2) {
            // Coupon code is empty which means we remove the coupon code
            $couponMessage = Text::_('COM_PHOCACART_COUPON_NOT_SET');
            $couponId      = 0;
        } else if (!$couponId) {
            // Coupon code just not valid
            $couponMessage = Text::_('COM_PHOCACART_COUPON_INVALID_EXPIRED_REACHED_USAGE_LIMIT');
            $couponId      = 0;
            $msgError      = 1;
        } else {
            // Coupon code successfuly tested
            $couponMessage = Text::_('COM_PHOCACART_COUPON_ADDED');
        }


        $model = $this->getModel('checkout');

        if ($guest) {
            // 1) GUEST
            // Guest enabled
            if (!$model->saveCouponGuest($couponId)) {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg . $msgSuffix, 'error');

            } else {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');

                $app->enqueueMessage($msg, 'message');
            }
        } else if ((int)$user->id < 1) {

            // 2) PRE-GUEST/PRE-LOGIN - NOT LOGGED IN OR STILL NOT ENABLED GUEST CHECKOUT
            // Guest not enabled yet MOVECOUPON
            if (!$model->saveCouponGuest($couponId)) {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg . $msgSuffix, 'error');

            } else {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                if ($msgError == 1) {
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $app->enqueueMessage($msg, 'message');
                }

            }

        } else {

            // 3) LOGGED IN USER
            if (!$model->saveCoupon($couponId)) {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg . $msgSuffix, 'error');
            } else {
                $msg = $couponMessage != '' ? $couponMessage : Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                if ($msgError == 1) {
                    $app->enqueueMessage($msg . $msgSuffix, 'error');
                } else {
                    $app->enqueueMessage($msg, 'message');
                }
            }
        }

        $app->redirect(base64_decode($item['return']));
    }

    /*
     * return:
     * couponId = -2 (couponId = '') ... coupon code is empty, e.g. when removing it (we use not dynamic variable)
     * couponId = 0 ... coupon code is not valid
     * couponId > 0 ... coupon code is valid
     */


    public function getCouponIdByCouponCode($code) {

        $app            = Factory::getApplication();
        $params         = $app->getParams();
        $enable_coupons = $params->get('enable_coupons', 2);

        $couponId   = -2;
        $couponTrue = false;
        if (isset($code) && $code != '' && $enable_coupons > 0) {

            $coupon = new PhocacartCoupon();
            $coupon->setType(array(0, 1));
            $coupon->setCoupon(0, $code);
            //$couponTrue = $coupon->checkCoupon(1);// Basic Check - Coupon True does not mean it is valid - only basic check done, whole check happens in order
            //$couponTrue = $coupon->checkCoupon();// Complete Check - mostly coupon is added at the end so do complete check - can be changed to basic - no items, no categories can be checked

            $cart = new PhocacartCartRendercheckout();
            $cart->setInstance(2);//checkout
            $cart->setType(array(0, 1));
            $cart->setFullItems();
            $fullItems = $cart->getFullItems();
            $total     = $cart->getTotal();

            //$couponTrue		= $cart->getCouponValid();// cart itself cannot say us if the coupon is valid, because this coupon was still not added to the cart

            if (!empty($fullItems[4]) && !empty($total[4])) {
                foreach ($fullItems[4] as $k => $v) {
                    $validCoupon = $coupon->checkCoupon(0, $v['id'], $v['catid'], $total[4]['quantity'], $total[4]['netto']);

                    // !!! VALID COUPON
                    // In case the coupon is valid at least for one product or one category it is then valid
                    // and will be divided into valid products/categories
                    // As global we mark it as valid - so change the valid coupon variable only in case it is valid
                    if ($validCoupon == 1) {
                        $couponTrue = $validCoupon;
                        break;
                    }
                }
            }

            $couponId = 0;
            if ($couponTrue) {
                $couponData = $coupon->getCoupon();
                if (isset($couponData['id']) && $couponData['id'] > 0) {
                    $couponId = $couponData['id'];
                }
            }
        }

        return $couponId;
    }


    public function saverewardpoints() {


        Session::checkToken() or jexit('Invalid Token');
        $app              = Factory::getApplication();
        $item             = array();
        $item['return']   = $this->input->get('return', '', 'string');
        $item['phreward'] = $this->input->get('phreward', '', 'int');
        $guest            = PhocacartUserGuestuser::getGuestUser();
        //$user 	                = PhocacartUser::getUser();
        //$params 					= $app->getParams();
        $msgSuffix = '<span id="ph-msg-ns" class="ph-hidden"></span>';


        // Reward Points
        $rewards = $this->getRewardPointsByRewardPointsCode($item['phreward']);

        if ($rewards['used'] === false) {
            $rewardMessage = Text::_('COM_PHOCACART_REWARD_POINTS_NOT_ADDED');
        } else {

            if ($rewards['used'] === 0) {
                $rewardMessage = Text::_('COM_PHOCACART_REWARD_POINTS_REMOVED');
            } else {
                $rewardMessage = Text::_('COM_PHOCACART_REWARD_POINTS_ADDED');
            }

        }

        $model = $this->getModel('checkout');

        if ($guest) {

        } else {

            if (!$model->saveRewardPoints($rewards['used'])) {
                $msg = $rewardMessage != '' ? $rewardMessage : Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg . $msgSuffix, 'error');
            } else {
                $msg = $rewardMessage != '' ? $rewardMessage : Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                $app->enqueueMessage($msg, 'message');
            }
        }

        $app->redirect(base64_decode($item['return']));
    }


    public function getRewardPointsByRewardPointsCode($points) {

        $app            = Factory::getApplication();
        $params         = $app->getParams();
        $enable_rewards = $params->get('enable_rewards', 1);


        $rewards         = array();
        $rewards['used'] = 0;

        if (isset($points) && $points != '' && $enable_rewards) {

            $reward          = new PhocacartReward();
            $rewards['used'] = $reward->checkReward((int)$points, 1);
        }

        return $rewards;

    }


    /*
     * Update or delete from cart
     */
    public function update() {

        Session::checkToken() or jexit('Invalid Token');
        $app              = Factory::getApplication();
        $item             = array();
        $item['id']       = $this->input->get('id', 0, 'int');
        $item['catid']    = $this->input->get('catid', 0, 'int');
        $item['idkey']    = $this->input->get('idkey', '', 'string');
        $item['quantity'] = $this->input->get('quantity', 0, 'int');
        $item['return']   = $this->input->get('return', '', 'string');
        $item['action']   = $this->input->get('action', '', 'string');
        $msgSuffix        = '<span id="ph-msg-ns" class="ph-hidden"></span>';



        $rights                           = new PhocacartAccessRights();
        $itemProduct                      = PhocacartProduct::getProduct($item['id'], $item['catid']);
        $this->t['can_display_addtocart'] = $rights->canDisplayAddtocartAdvanced($itemProduct);


        if (!$this->t['can_display_addtocart']) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
            $app->redirect(base64_decode($item['return']));
        }

        if ((int)$item['idkey'] != '' && $item['action'] != '') {

            $cart = new PhocacartCart();
            if ($item['action'] == 'delete') {
                $updated = $cart->updateItemsFromCheckout($item['idkey'], 0);
                if ($updated) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'message');
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
                }
            } else {// update
                $updated = $cart->updateItemsFromCheckout($item['idkey'], (int)$item['quantity']);
                if ($updated) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED') . $msgSuffix, 'message');
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED') . $msgSuffix, 'error');
                }
            }
        }

        //$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
        $app->redirect(base64_decode($item['return']));
    }
    /*
     public function saveshipping() {

        Session::checkToken() or jexit( 'Invalid Token' );
        $app					= Factory::getApplication();
        $item					= array();
        $item['return']			= $this->input->get( 'return', '', 'string'  );
        $item['phshippingopt']	= $this->input->get( 'phshippingopt', array(), 'array'  );


        if(!empty($item['phshippingopt']) && isset($item['phshippingopt'][0]) && (int)$item['phshippingopt'][0] > 0) {

            $model 	= $this->getModel('checkout');
            if(!$model->saveShipping((int)$item['phshippingopt'][0])) {
                $msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
                $app->enqueueMessage($msg, 'error');
            } else {
                $msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
                $app->enqueueMessage($msg, 'message');
            }

        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_NO_SHIPPING_METHOD_SELECTED'), 'error');
        }
        $app->redirect(base64_decode($item['return']));
    }
    */
    /*
     * Make an order
     */

    public function order() {

        Session::checkToken() or jexit('Invalid Token');
        $pC                                = PhocacartUtils::getComponentParameters();
        $display_checkout_privacy_checkbox = $pC->get('display_checkout_privacy_checkbox', 0);
        $display_checkout_toc_checkbox     = $pC->get('display_checkout_toc_checkbox', 2);

        $app                   = Factory::getApplication();
        $item                  = array();
        $item['return']        = $this->input->get('return', '', 'string');
        $item['phcheckouttac'] = $this->input->get('phcheckouttac', false, 'string');
        $item['privacy']       = $this->input->get('privacy', false, 'string');
        $item['newsletter']    = $this->input->get('newsletter', false, 'string');
        $item['phcomment']     = $this->input->get('phcomment', '', 'string');
        $msgSuffix             = '<span id="ph-msg-ns" class="ph-hidden"></span>';

        $item['privacy']       = $item['privacy'] ? 1 : 0;
        $item['phcheckouttac'] = $item['phcheckouttac'] ? 1 : 0;
        $item['newsletter']    = $item['newsletter'] ? 1 : 0;


        if ($display_checkout_privacy_checkbox == 2 && $item['privacy'] == 0) {
            $msg = Text::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_PRIVACY_TERMS_AND_CONDITIONS');
            $app->enqueueMessage($msg . $msgSuffix, 'error');
            $app->redirect(base64_decode($item['return']));
            return false;

        }

        if ($display_checkout_toc_checkbox == 2 && $item['phcheckouttac'] == 0) {
            $msg = Text::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_TERMS_AND_CONDITIONS');
            $app->enqueueMessage($msg . $msgSuffix, 'error');
            $app->redirect(base64_decode($item['return']));
            return false;
        }


        $order     = new PhocacartOrder();
        $orderMade = $order->saveOrderMain($item);


        if (!$orderMade) {
            $msg = '';
            if (!PhocacartUtils::issetMessage()) {
                $msg = Text::_('COM_PHOCACART_ORDER_ERROR_PROCESSING');
            }
            $app->enqueueMessage($msg . $msgSuffix, 'error');
            $app->redirect(base64_decode($item['return']));
            return true;
        } else {

            // Lets decide Payment plugin if the cart will be emptied or not
            $cart           = new PhocacartCart();
            $paymentMethod 	= $cart->getPaymentMethod();
            $pluginData     = array();
            $pluginData['emptycart'] = true;
            if (isset($paymentMethod['id']) && (int)$paymentMethod['id'] > 0) {

                $payment		= new PhocacartPayment();
                $paymentO       = $payment->getPaymentMethod((int)$paymentMethod['id']);

                if (isset($paymentO->method)) {
                    $proceed 					= '';
                    Dispatcher::dispatch(new Event\Payment\BeforeEmptyCartAfterOrder($proceed, $pluginData, $pC, $paymentO->params, $order, [
                      'pluginname' 	=> $paymentO->method,
                    ]));
                }
            }

            if ($pluginData['emptycart'] === true) {
                $cart->emptyCart();
                PhocacartUserGuestuser::cancelGuestUser();
            }



            $action     = $order->getActionAfterOrder(); // Which action should be done
            $message    = $order->getMessageAfterOrder();// Custom message by payment plugin Payment/Download, Payment/No Download ...
            $dataOrder  = $order->getDataAfterOrder();// Order ID, Token, payment ID, shipping ID ... different data for info view

            $session = Factory::getSession();
            if ($action == 4 || $action == 3) {
                // Ordered OK, but now we proceed to payment
                $session->set('infoaction', $action, 'phocaCart');
                $session->set('infomessage', $message, 'phocaCart');
                $session->set('infodata', $dataOrder, 'phocaCart');
                $app->redirect(Route::_(PhocacartRoute::getPaymentRoute(), false));
                return true;
                // This message should stay
                // when order - the message is created
                // when payment - the message stays unchanged
                // after payment - it will be redirected to info view and there the message will be displayed and then deleted

            } else {
                // Ordered OK, but the payment method does not have any instruction to proceed to payment (e.g. cash on delivery)
                //$msg = Text::_('COM_PHOCACART_ORDER_SUCCESSFULLY_PROCESSED');
                // We produce not message but we redirect to specific view with message and additional instructions
                //$app->enqueueMessage($msg, 'message');

                $session->set('infoaction', $action, 'phocaCart');
                $session->set('infomessage', $message, 'phocaCart');
                $session->set('infodata', $dataOrder, 'phocaCart');
                $app->redirect(Route::_(PhocacartRoute::getInfoRoute(), false));
                return true;
            }
        }


    }

    public function setguest() {

        Session::checkToken() or jexit('Invalid Token');
        $app            = Factory::getApplication();
        $item           = array();
        $item['id']     = $this->input->get('id', 0, 'int');
        $item['return'] = $this->input->get('return', '', 'string');
        $msgSuffix      = '<span id="ph-msg-ns" class="ph-hidden"></span>';


        //$guest = new PhocacartUserGuestuser();
        //$set = $guest->setGuestUser((int)$item['id']);
        $set = PhocacartUserGuestuser::setGuestUser((int)$item['id']);
        if ((int)$item['id'] == 1) {
            if ($set) {
                $app->enqueueMessage(Text::_('COM_PHOCACART_YOU_PROCEEDING_GUEST_CHECKOUT') . $msgSuffix, 'message');
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_DURING_PROCEEDING_GUESTBOOK_CHECKOUT') . $msgSuffix, 'error');
            }
        } else {
            if ($set) {
                $app->enqueueMessage(Text::_('COM_PHOCACART_GUEST_CHECKOUT_CANCELED') . $msgSuffix, 'message');
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_DURING_CANCELING_GUESTBOOK_CHECKOUT') . $msgSuffix, 'error');
            }
        }
        //$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
        $app->redirect(base64_decode($item['return']));
    }
    /*
    public function compareadd() {

        Session::checkToken() or jexit( 'Invalid Token' );
        $app				= Factory::getApplication();
        $item				= array();
        $item['id']			= $this->input->get( 'id', 0, 'int' );
        $item['return']		= $this->input->get( 'return', '', 'string'  );

        $compare	= new PhocacartCompare();
        $added	= $compare->addItem((int)$item['id']);
        if ($added) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_COMPARISON_LIST'), 'message');
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_COMPARISON_LIST'), 'error');
        }
        //$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
        $app->redirect(base64_decode($item['return']));
    }

        public function compareremove() {

        Session::checkToken() or jexit( 'Invalid Token' );
        $app				= Factory::getApplication();
        $item				= array();
        $item['id']			= $this->input->get( 'id', 0, 'int' );
        $item['return']		= $this->input->get( 'return', '', 'string'  );

        $compare	= new PhocacartCompare();
        $added	= $compare->removeItem((int)$item['id']);
        if ($added) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_COMPARISON_LIST'), 'message');
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_COMPARISON_LIST'), 'error');
        }
        //$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
        $app->redirect(base64_decode($item['return']));
    }*/

}

?>
