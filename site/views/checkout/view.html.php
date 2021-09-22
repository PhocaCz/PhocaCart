<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.view');

class PhocaCartViewCheckout extends JViewLegacy
{
    protected $t;
    protected $r;
    protected $p;
    protected $u;
    protected $gu;
    protected $item;
    protected $data;  // address data for output
    protected $form;  // address data for form
    protected $fields;// fields loaded for data and form to check if required fields are filled out
    protected $cart;
    protected $s;
    protected $a;

    function display($tpl = null) {


        $document = JFactory::getDocument();
        $app      = JFactory::getApplication();
        $uri      = \Joomla\CMS\Uri\Uri::getInstance();
        $this->u  = PhocacartUser::getUser();
        $this->p  = $app->getParams();
        $this->a  = new PhocacartAccess();
        $this->s  = PhocacartRenderStyle::getStyles();
        $guest    = PhocacartUserGuestuser::getGuestUser();
        $reward   = new PhocacartReward();

        $this->t['action']       = $uri->toString();
        $this->t['actionbase64'] = base64_encode($this->t['action']);
        $this->t['linkcheckout'] = JRoute::_(PhocacartRoute::getCheckoutRoute());

        $this->t['checkout_desc']              = $this->p->get('checkout_desc', '');
        $this->t['checkout_desc']              = PhocacartRenderFront::renderArticle($this->t['checkout_desc']);
        $this->t['stock_checkout']             = $this->p->get('stock_checkout', 0);
        $this->t['stock_checking']             = $this->p->get('stock_checking', 0);
        $this->t['guest_checkout']             = $this->p->get('guest_checkout', 0);
        $this->t['guest_checkout_auto_enable'] = $this->p->get('guest_checkout_auto_enable', 0);
        $this->t['icon_suffix']                = $this->p->get('icon_suffix', '-circle');
        $this->t['display_shipping_desc']      = $this->p->get('display_shipping_desc', 0);
        $this->t['display_payment_desc']       = $this->p->get('display_payment_desc', 0);
        $this->t['zero_shipping_price']        = $this->p->get('zero_shipping_price', 1);
        $this->t['zero_payment_price']         = $this->p->get('zero_payment_price', 1);
        $this->t['checkout_scroll']            = $this->p->get('checkout_scroll', 1);
        $this->t['enable_coupons']             = $this->p->get('enable_coupons', 2);
        $this->t['enable_rewards']             = $this->p->get('enable_rewards', 1);
        $this->t['checkout_icon_status']       = $this->p->get('checkout_icon_status', 1);
        $this->t['display_webp_images']        = $this->p->get('display_webp_images', 0);

        $this->t['skip_shipping_method']              = $this->p->get('skip_shipping_method', 0);
        $this->t['skip_payment_method']               = $this->p->get('skip_payment_method', 0);
        $this->t['automatic_shipping_method_setting'] = $this->p->get('automatic_shipping_method_setting', 0);
        $this->t['automatic_payment_method_setting']  = $this->p->get('automatic_payment_method_setting', 0);
        $this->t['display_apply_coupon_form']         = $this->p->get('display_apply_coupon_form', 1);
        $this->t['display_apply_reward_points_form']  = $this->p->get('display_apply_reward_points_form', 1);

        $this->t['$delivery_billing_same_enabled']     = $this->p->get('delivery_billing_same_enabled', 0);


        // Message set in Openting Times class
        PhocacartTime::checkOpeningTimes();

        // Enable guest checkout automatically
        if ($this->t['guest_checkout'] == 1 && $this->t['guest_checkout_auto_enable'] == 1) {
            //$guest	= new PhocacartUserGuestuser();
            //$guest->setGuestUser(1);
            $guest = PhocacartUserGuestuser::setGuestUser(1);
        }


        // Terms and Conditions
        $this->t['display_checkout_toc_checkbox'] = $this->p->get('display_checkout_toc_checkbox', 2);
        if ($this->t['display_checkout_toc_checkbox'] > 0) {
            $this->t['terms_conditions_custom_label_text'] = $this->p->get('terms_conditions_custom_label_text', 0);
            $linkTerms                                     = JRoute::_(PhocacartRoute::getTermsRoute(0, 0, 'tmpl=component'));
            $defaultText                                   = JText::_('COM_PHOCACART_I_HAVE_READ_AND_AGREE_TO_THE') . ' <a href="' . $linkTerms . '" onclick="phWindowPopup(this.href, \'phWindowPopupTerms\', 2, 1.6);return false;" >' . JText::_('COM_PHOCACART_TERMS_AND_CONDITIONS') . '</a>';
            $this->t['terms_conditions_label_text']        = PhocacartRenderFront::renderArticle((int)$this->t['terms_conditions_custom_label_text'], 'html', $defaultText);
        }

        // Checkout Privacy checkbox
        $this->t['display_checkout_privacy_checkbox'] = $this->p->get('display_checkout_privacy_checkbox', 0);
        if ($this->t['display_checkout_privacy_checkbox'] > 0) {
            $this->t['checkout_privacy_checkbox_label_text'] = $this->p->get('checkout_privacy_checkbox_label_text', 0);
            $this->t['checkout_privacy_checkbox_label_text'] = PhocacartRenderFront::renderArticle((int)$this->t['checkout_privacy_checkbox_label_text'], 'html', '');
        }

        // Newsletter
        $this->t['display_checkout_newsletter_checkbox'] = $this->p->get('display_checkout_newsletter_checkbox', 0);
        if ($this->t['display_checkout_newsletter_checkbox'] > 0) {
            $this->t['checkout_newsletter_checkbox_label_text'] = $this->p->get('checkout_newsletter_checkbox_label_text', 0);
            $this->t['checkout_newsletter_checkbox_label_text'] = PhocacartRenderFront::renderArticle((int)$this->t['checkout_newsletter_checkbox_label_text'], 'html', '');
        }

        $this->t['enable_captcha_checkout'] = PhocacartCaptcha::enableCaptchaCheckout();

        $scrollTo = '';


        // Not ready yet
        // Checkout cart can be changed by ajax
        // But not module cart, no shipping, no payment is refreshed, no plus/minus (touchspin.js) refreshed
        //PhocacartRenderJs::renderAjaxUpdateCart();// used only in POS


        // Cart
        $this->cart = new PhocacartCartRendercheckout();
        $this->cart->setFullItems();


        if ((int)$this->u->id > 0) {
            $this->a->login = 1;
        } else if ($guest) {
            $this->a->login = 2;
        }


        // Shipping and Payment rules will be checked including rounding
        $this->cart->roundTotalAmount();

        // Is there even a shipping or payment (or is active based on criterias)
        $total = $this->cart->getTotal();
        $sOCh  = array();// Shipping Options Checkout
        // PRODUCTTYPE
        $sOCh['all_digital_products'] = isset($total[0]['countdigitalproducts']) && isset($total[0]['countallproducts']) && (int)$total[0]['countdigitalproducts'] == $total[0]['countallproducts'] ? 1 : 0;
        $pOCh                         = array();// Payment Options Checkout

        $pOCh['order_amount_zero'] = 1;
        $pOCh['order_amount_zero'] = $total[0]['brutto'] == 0 && $total[0]['netto'] == 0 ? 1 : 0;


        $this->a->shippingnotused = PhocacartShipping::isShippingNotUsed($sOCh);
        $this->a->paymentnotused  = PhocacartPayment::isPaymentNotUsed($pOCh);


        // COUPONS - Coupon can be added in payment method or below calculation
        $this->t['couponcodevalue'] = '';
        if ($this->cart->getCouponCode() != '') {
            $this->t['couponcodevalue'] = $this->cart->getCouponCode();

        }

        // REWARD POINTS - reward points can be added in payment method or below the calculation
        $this->t['rewards']          = array();
        $this->t['rewards']['apply'] = false;
        if ($this->t['enable_rewards']) {
            if ($this->u->id > 0) {
                $this->t['rewards']['needed']    = $this->cart->getRewardPointsNeeded();
                $this->t['rewards']['usertotal'] = $reward->getTotalPointsByUserId($this->u->id);

                $this->t['rewards']['usedvalue'] = '';
                if ($this->cart->getRewardPointsUsed() != '' && (int)$this->cart->getRewardPointsUsed() > 0) {
                    $this->t['rewards']['usedvalue'] = $this->cart->getRewardPointsUsed();
                }

                if ($this->t['rewards']['usertotal'] > 0) {
                    $this->t['rewards']['text']  = '<small>(' . JText::_('COM_PHOCACART_AVAILABLE_REWARD_POINTS') . ': ' . (int)$this->t['rewards']['usertotal'] . ', ' . JText::_('COM_PHOCACART_MAXIMUM_REWARD_POINTS_TO_USE') . ': ' . (int)$this->t['rewards']['needed'] . ')</small>';
                    $this->t['rewards']['apply'] = true;
                }
            }
        }

        // Numbers
        $this->t['nl'] = 1;                     // Login
        $this->t['na'] = 2;                     // Address
        $this->t['ns'] = 3;                     // Shipping
        $this->t['np'] = 4;                     // Payment
        if ($this->a->shippingnotused == 1) {
            $this->t['np'] = 3;
        }

        if ($this->a->login == 1 || $this->a->login == 2) {

            // =======
            // ADDRESS
            // =======
            $this->t['dataaddressoutput'] = array();

            $this->a->addressedit = $app->input->get('addressedit', 0, 'int'); // Edit Address

            // GUEST
            if ($this->a->login == 2) {
                // Check if all form items are filled out by user, if yes, don't load the form and save some queries
                $this->fields = $this->get('FieldsGuest'); // Fields will be loaded in every case
                if ($this->a->addressedit == 0) {
                    $this->data                   = $this->get('DataGuest');
                    $this->t['dataaddressoutput'] = PhocacartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u, 1);
                }
                //Some required field is not filled out
                if (isset($this->t['dataaddressoutput']['filled']) && $this->t['dataaddressoutput']['filled'] == 1) {
                    $this->a->addressadded = 1;
                } else {
                    $this->a->addressadded = 0;
                }
                // Load form and fields to edit address
                if ($this->a->addressadded == 0 || $this->a->addressedit == 1) {
                    $this->a->addressview       = 0;
                    $this->a->addressedit       = 1;
                    $scrollTo                   = 'phcheckoutaddressedit';
                    $this->form                 = $this->get('FormGuest');
                    $this->t['dataaddressform'] = PhocacartUser::getAddressDataForm($this->form, $this->fields['array'], '', '', '_phs', 1);
                }
                // REGISTERED
            } else {
                // Check if all form items are filled out by user, if yes, don't load the form and save some queries
                $this->fields = $this->get('Fields'); // Fields will be loaded in every case


                if ($this->a->addressedit == 0) {
                    $this->data = $this->get('Data');

                    $this->t['dataaddressoutput'] = PhocacartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u);

                }

                //Some required field is not filled out
                if (isset($this->t['dataaddressoutput']['filled']) && $this->t['dataaddressoutput']['filled'] == 1) {
                    $this->a->addressadded = 1;
                } else {
                    $this->a->addressadded = 0;
                }
                // Load form and fields to edit address
                if ($this->a->addressadded == 0 || $this->a->addressedit == 1) {
                    $this->a->addressview       = 0;
                    $this->a->addressedit       = 1;
                    $this->form                 = $this->get('Form');
                    $this->t['dataaddressform'] = PhocacartUser::getAddressDataForm($this->form, $this->fields['array'], $this->u);
                    $scrollTo                   = 'phcheckoutaddressedit';
                }
            }

            if ($this->a->addressadded == 1 && $this->a->addressedit == 0) {
                $this->a->addressview = 1;
                $scrollTo             = 'phcheckoutaddressview';
            }


            // ====================
            // SHIPPING
            // ====================
            $shipping              = new PhocacartShipping();
            $country               = $shipping->getUserCountryShipping($this->t['dataaddressoutput']);
            $region                = $shipping->getUserRegionShipping($this->t['dataaddressoutput']);
            $zip 	                = $shipping->getUserZipShipping($this->t['dataaddressoutput']);
            $this->a->shippingadded = 0;
            $this->a->shippingedit = $app->input->get('shippingedit', 0, 'int'); // Edit Shipping
            $shippingId            = $this->cart->getShippingId();               // Shipping stored in cart or not?


            if (isset($shippingId) && (int)$shippingId > 0 && $this->a->shippingedit == 0) {
                // Shipping method is stored in cart, we can update the cart (add shipping costs to whole cart)
                $this->a->shippingadded = 1;
                $this->a->shippingview  = 1;
                $scrollTo               = 'phcheckoutshippingview';
                $this->cart->addShippingCosts($shippingId);
                $this->t['shippingmethod'] = $this->cart->getShippingCosts();

                // If "automatic_shipping_method_setting" is set to yes, this means that the shipping method will be set automatically in case:
                // - there is only one shipping method available or only one meets the criteria
                // - and the parameter is set to yes
                // It is not possible to edit the method because when switching to edit, the method will be set automatically as wished by enabling the parameter
                // and redirect outside the editing mode
                if ($this->t['automatic_shipping_method_setting'] == 1) {
                    //- $shipping					= new PhocacartShipping();
                    $this->t['shippingmethods'] = $shipping->getPossibleShippingMethods($total[0]['netto'], $total[0]['brutto'], $total[0]['quantity'], $country, $region, $zip, $total[0]['weight'], $total[0]['length'], $total[0]['width'], $total[0]['height'], 0, $shippingId);
                    if (!empty($this->t['shippingmethods']) && count($this->t['shippingmethods']) == 1) {
                        $this->a->shippingdisplayeditbutton = 0;
                    }
                }


                // Shipping method which was selected is not more active - display edit again
                if ($this->t['shippingmethod'] == 0) {
                    $this->a->shippingadded = 0;
                    $this->a->shippingedit = 1;
                    $shippingId = 0;
                }

            }

            if (($this->a->shippingedit == 1 || $this->a->shippingadded == 0) && $this->a->addressadded == 1 && $this->a->addressedit == 0 && $this->a->paymentedit == 0) {
                // Shipping cost is not stored in cart, display possible shipping methods
                // We ask for total of cart because of amount rule
                $this->a->shippingadded = 0;
                $this->a->shippingview  = 0;
                $this->a->shippingedit  = 1;
                $scrollTo               = 'phcheckoutshippingedit';
                //- $shipping					= new PhocacartShipping();
                //$shipping->setType();
                $total = $this->cart->getTotal();


                $this->t['shippingmethods'] = $shipping->getPossibleShippingMethods($total[0]['netto'], $total[0]['brutto'], $total[0]['quantity'], $country, $region, $zip, $total[0]['weight'], $total[0]['length'], $total[0]['width'], $total[0]['height'], 0, $shippingId);//$shippingId = 0 so all possible shipping methods will be listed


                // If there is only one valid shipping method and it is set in parameter we can directly store this method so user does not need to add it
                // When setting the shipping method then the cart needs to be "refreshed", shipping costs needs to be added and info about shpping id
                // must be updated because of payment rules (one of payment rule is shipping)
                if (!empty($this->t['shippingmethods']) && count($this->t['shippingmethods']) == 1 && $this->t['automatic_shipping_method_setting'] == 1) {
                    if (isset($this->t['shippingmethods'][0]->id) && (int)$this->t['shippingmethods'][0]->id > 0) {

                        $shippingStored = 0;
                        if ($this->a->login == 1 && isset($this->u->id) && $this->u->id > 0) {
                            if ($shipping->storeShippingRegistered($this->t['shippingmethods'][0]->id, $this->u->id)) {
                                $shippingStored = 1;
                            }
                        } else if ($this->a->login == 2) {
                            if (PhocacartUserGuestuser::storeShipping((int)$this->t['shippingmethods'][0]->id)) {
                                $shippingStored = 1;
                            }
                        }

                        if ($shippingStored == 1) {
                            $shippingId = (int)$this->t['shippingmethods'][0]->id;// will be used for payment - updated now
                            $this->cart->addShippingCosts($shippingId);           // add the costs to cart so it has updated information

                            $this->t['shippingmethod'] = $this->cart->getShippingCosts();

                            $this->a->shippingadded             = 1;
                            $this->a->shippingview              = 1;
                            $this->a->shippingedit              = 0;
                            $this->a->shippingdisplayeditbutton = 0;
                            $scrollTo                           = 'phcheckoutpaymentedit';


                        }
                    }
                }


                // No shipping method found even all rules were applied and shipping methods were searched
                // THIS CASE CAN BE VENDOR ERROR (wrong setting of shipping methods) OR PURPOSE - be aware when using $skip_shipping_method = 3
                // Skip adding/selecting shipping method and allow customer proceeding the order? (depends on parameter: $this->t['skip_shipping_method'])
                // Must be implemented here because now we know information about total, shipping and address we need for deciding about shipping method
                // Must cooperate with administrator/components/com_phocacart/libraries/phocacart/order/order.php cca 402
                // In this case $this->t['shippingmethod']['id'] is even null, so we don't need to ask $shipping->getPossibleShippingMethods for outcomes with not selected shipping method

                if (empty($this->t['shippingmethods']) && $this->t['skip_shipping_method'] == 3) {
                    $this->a->shippingnotused = 1;
                };

            }
            //- }

            // =================
            // PAYMENT (VOUCHER)
            // =================


            $payment = new PhocacartPayment();
            $country = $payment->getUserCountryPayment($this->t['dataaddressoutput']);
            $region  = $payment->getUserRegionPayment($this->t['dataaddressoutput']);
            $this->a->paymentadded    = 0;
            $this->a->paymentedit     = $app->input->get('paymentedit', 0, 'int'); // Edit Shipping
            $this->t['paymentmethod'] = $this->cart->getPaymentMethod();
            $paymentMethodId            = isset($this->t['paymentmethod']['id']) && (int)$this->t['paymentmethod']['id'] > 0 ? (int)$this->t['paymentmethod']['id']: 0;


            if ((int)$paymentMethodId  > 0 && $this->a->paymentedit == 0) {
                $this->cart->addPaymentCosts($paymentMethodId);// validity of payment will be checked
                $this->t['paymentmethod'] = $this->cart->getPaymentCosts();
                $this->a->paymentadded    = 1;
                $this->a->paymentview     = 1;
                $scrollTo                 = 'phcheckoutpaymentview';


                // If "automatic_payment_method_setting" is set to yes, this means that the payment method will be set automatically in case:
                // - there is only one payment method available or only one meets the criteria
                // - and the parameter is set to yes
                // It is not possible to edit the method because when switching to edit, the method will be set automatically as wished by enabling the parameter
                // and redirect outside the editing mode
                if ($this->t['automatic_payment_method_setting'] == 1) {
                    //$payment					= new PhocacartPayment();
                    $shippingId                = $this->cart->getShippingId();// Shipping stored in cart or not?
                    $this->t['paymentmethods'] = $payment->getPossiblePaymentMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $shippingId, 0, $paymentMethodId);
                    if (!empty($this->t['paymentmethods']) && count($this->t['paymentmethods']) == 1) {
                        $this->a->paymentdisplayeditbutton = 0;
                    }
                }

                // Payment method which was selected is not more active - display edit again
                if ($this->t['paymentmethod'] == 0) {
                    $paymentMethodId = 0;
                    $this->a->paymentadded  = 0;
                    $this->a->paymentedit = 1;
                }
            }

            if (($this->a->paymentedit == 1 || $this->a->paymentadded == 0) && $this->a->addressadded == 1 && $this->a->addressedit == 0 && (($this->a->shippingadded == 1 && $this->a->shippingedit == 0) || $this->a->shippingnotused == 1)) {


                // Payment cost is not stored in cart, display possible payment methods
                // We ask for total of cart because of amount rule
                $this->a->paymentadded = 0;
                $this->a->paymentview  = 0;
                $this->a->paymentedit  = 1;
                $scrollTo              = 'phcheckoutpaymentedit';
                //$payment					= new PhocacartPayment();
                $shippingId = $this->cart->getShippingId();// Shipping stored in cart or not?

                $total = $this->cart->getTotal();


                $this->t['paymentmethods'] = $payment->getPossiblePaymentMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $shippingId, 0, $paymentMethodId);


                // If there is only one valid payment method and it is set in parameter we can directly store this method so user does not need to add it
                // When setting the payment method then the cart needs to be "refreshed", payment costs needs to be added and info about shpping id
                // must be updated because of payment rules (one of payment rule is payment)
                if (!empty($this->t['paymentmethods']) && count($this->t['paymentmethods']) == 1 && $this->t['automatic_payment_method_setting'] == 1) {
                    if (isset($this->t['paymentmethods'][0]->id) && (int)$this->t['paymentmethods'][0]->id > 0) {

                        $paymentStored = 0;
                        if ($this->a->login == 1 && isset($this->u->id) && $this->u->id > 0) {
                            if ($payment->storePaymentRegistered($this->t['paymentmethods'][0]->id, $this->u->id)) {
                                $paymentStored = 1;
                            }
                        } else if ($this->a->login == 2) {
                            if (PhocacartUserGuestuser::storePayment((int)$this->t['paymentmethods'][0]->id)) {
                                $paymentStored = 1;
                            }
                        }

                        if ($paymentStored == 1) {
                            $paymentId = (int)$this->t['paymentmethods'][0]->id;// will be used for payment - updated now
                            $this->cart->addPaymentCosts($paymentId);           // add the costs to cart so it has updated information // validity of payment will be checked
                            $this->t['paymentmethod'] = $this->cart->getPaymentCosts();

                            $this->a->paymentadded             = 1;
                            $this->a->paymentview              = 1;
                            $this->a->paymentedit              = 0;
                            $this->a->paymentdisplayeditbutton = 0;
                            $scrollTo                          = 'phcheckoutpaymentview';
                        }
                    }
                }

                // No payment method found even all rules were applied and payment methods were searched
                // THIS CASE CAN BE VENDOR ERROR (wrong setting of shipping methods) OR PURPOSE - be aware when using $skip_shipping_method = 3
                // Skip adding/selecting payment method and allow customer proceeding the order? (depends on parameter: $this->t['skip_shipping_method'])
                // Must be implemented here because now we know information about total, shipping and address we need for deciding about payment method
                // Must cooperate with administrator/components/com_phocacart/libraries/phocacart/order/order.php cca 402
                // In this case $this->t['paymentmethod']['id'] is even null, so we don't need to ask $payment->getPossiblePaymentMethods for outcomes with not selected payment method

                if (empty($this->t['paymentmethods']) && $this->t['skip_payment_method'] == 3) {
                    $this->a->paymentnotused = true;
                };


                /*	BOTH COUPON OR REWARD POINTS CAN BE SET BEFORE PAYMENT
                    // COUPON
                    $this->t['couponcodevalue'] = '';
                    if ($this->cart->getCouponCode() != '') {
                        $this->t['couponcodevalue'] = $this->cart->getCouponCode();
                    }

                    // REWARD POINTS
                    $this->t['rewards'] 			= array();
                    $this->t['rewards']['apply'] 	= false;
                    if ($this->t['enable_rewards']) {
                        if ($this->u->id > 0) {
                            $this->t['rewards']['needed'] = $this->cart->getRewardPointsNeeded();
                            $this->t['rewards']['usertotal'] = $reward->getTotalPointsByUserId($this->u->id);

                            $this->t['rewards']['usedvalue'] = '';
                            if ($this->cart->getRewardPointsUsed() != '' && (int)$this->cart->getRewardPointsUsed() > 0) {
                                $this->t['rewards']['usedvalue'] = $this->cart->getRewardPointsUsed();
                            }

                            if ($this->t['rewards']['usertotal'] > 0) {
                                $this->t['rewards']['text'] = '<small>('.JText::_('COM_PHOCACART_AVAILABLE_REWARD_POINTS').': '.(int)$this->t['rewards']['usertotal'].', '.JText::_('COM_PHOCACART_MAXIMUM_REWARD_POINTS_TO_USE').': '.(int)$this->t['rewards']['needed'].')</small>';
                                $this->t['rewards']['apply'] 	= true;
                            }
                        }
                    }
                */
            }
            //- }

            //- PhocacartRenderJs::renderBillingAndShippingSame();
        }

        //  Rounding set before checking shipping and payment method
        //	$this->cart->roundTotalAmount();


        // CART IS EMPTY - MUST BE CHECKED BEFOR CONFIRM
        // Don't allow to add or edit payment or shipping method, don't allow to confirm the order
        if (empty($this->cart->getItems())) {
            $this->a->shippingnotused = 1;
            $this->a->paymentnotused  = 1;
            $this->a->confirm         = 0;
        }

        if ($this->a->shippingnotused == 1) {
            $this->a->shippingview = 1;
            if ($scrollTo == 'phcheckoutshippingedit') {
                $scrollTo = '';
            }
        }
        if ($this->a->paymentnotused == 1) {
            $this->a->paymentview = 1;
            if ($scrollTo == 'phcheckoutpaymentedit') {
                $scrollTo = '';
            }
        }


        // VIEW - CONFIRM - all items added
        if (($this->a->login == 1 || $this->a->login == 2) && $this->a->addressview == 1 && $this->a->shippingview == 1 && $this->a->paymentview == 1) {
            $this->a->confirm = 1;

            // Custom "Confirm Order" Text
            $total                         = $this->cart->getTotal();
            $totalBrutto                   = isset($total[0]['brutto']) ? $total[0]['brutto'] : 0;
            $this->t['confirm_order_text'] = PhocacartRenderFront::getConfirmOrderText($totalBrutto);
        }


        $media = PhocacartRenderMedia::getInstance('main');
        $media->loadBase();
        $media->loadChosen();
        $media->loadWindowPopup();

        $media->loadTouchSpin('quantity', $this->s['i']);
        //PhocacartRenderJs::renderAjaxUpdateCart(); used only in POS

        //Scroll to
        if ($this->t['checkout_scroll'] == 0) {
            $scrollTo = '';
        }
        if ($scrollTo == '') {
        } else if ($scrollTo == 'phcheckoutaddressedit' || $scrollTo == 'phcheckoutshippingedit' || $scrollTo == 'phcheckoutpaymentedit') {
            PhocacartRenderJs::renderJsScrollTo($scrollTo, 2);
        } else if ($scrollTo == 'phcheckoutpaymentview') {
            // last view - in fact phcheckoutconfirmedit
            PhocacartRenderJs::renderJsScrollTo($scrollTo, 1);
        } else {
            PhocacartRenderJs::renderJsScrollTo('', 2);
        }


        // Render the cart (here because it can be changed above - shipping can be added)
        //$total				= $this->cart->getTotal();
        $this->t['cartoutput'] = $this->cart->render();

        $this->t['cartempty'] = $this->cart->getCartCountItems() > 0 ? false : true;

        $this->t['stockvalid']          = $this->cart->getStockValid();
        $this->t['minqtyvalid']         = $this->cart->getMinimumQuantityValid();
        $this->t['minmultipleqtyvalid'] = $this->cart->getMinimumMultipleQuantityValid();

        $media->loadSpec();

        $this->_prepareDocument();

        // Plugins ------------------------------------------
        $this->t['total'] = $total;
        JPluginHelper::importPlugin('pcv');
        //$this->t['dispatcher']	= J EventDispatcher::getInstance();
        $this->t['event'] = new stdClass;

        $results                               = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterCart', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterCart = trim(implode("\n", $results));

        $results                                = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterLogin', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterLogin = trim(implode("\n", $results));

        $results                                  = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterAddress', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterAddress = trim(implode("\n", $results));

        $results                                   = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterShipping', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterShipping = trim(implode("\n", $results));

        $results                                  = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterPayment', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterPayment = trim(implode("\n", $results));

        $results                                  = \JFactory::getApplication()->triggerEvent('PCVonCheckoutAfterConfirm', array('com_phocacart.checkout', $this->a, &$this->p, $this->t['total']));
        $this->t['event']->onCheckoutAfterConfirm = trim(implode("\n", $results));

        // END Plugins --------------------------------------

        $media->loadSpec();


        parent::display($tpl);


    }

    protected function _prepareDocument() {
        PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_CHECKOUT'));
    }
}

?>
