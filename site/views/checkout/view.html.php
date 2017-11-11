<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');
class PhocaCartViewCheckout extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	protected $gu;
	protected $item;
	protected $data;// address data for output
	protected $form;// address data for form
	protected $fields;// fields loaded for data and form to check if required fields are filled out
	protected $cart;
	protected $s;
	protected $a;
	
	function display($tpl = null) {
		
		$document							= JFactory::getDocument();		
		$app								= JFactory::getApplication();
		$uri 								= JFactory::getURI();
		$this->u							= JFactory::getUser();
		$this->p							= $app->getParams();
		$this->a							= new PhocacartAccess();
		$guest								= PhocacartUserGuestuser::getGuestUser();
		$reward								= new PhocacartReward();
		
		$this->t['action']					= $uri->toString();
		$this->t['actionbase64']			= base64_encode($this->t['action']);
		$this->t['linkcheckout']			= JRoute::_(PhocacartRoute::getCheckoutRoute());
		
		$this->t['checkout_desc']			= $this->p->get( 'checkout_desc', '');
		$this->t['checkout_desc']			= PhocacartRenderFront::renderArticle($this->t['checkout_desc']);
		$this->t['stock_checkout']			= $this->p->get( 'stock_checkout', 0 );
		$this->t['stock_checking']			= $this->p->get( 'stock_checking', 0 );
		$this->t['guest_checkout']			= $this->p->get( 'guest_checkout', 0 );
		$this->t['icon_suffix']				= $this->p->get( 'icon_suffix', '-circle' );
		$this->t['display_shipping_desc']	= $this->p->get( 'display_shipping_desc', 0 );
		$this->t['display_payment_desc']	= $this->p->get( 'display_payment_desc', 0 );
		$this->t['zero_shipping_price']		= $this->p->get( 'zero_shipping_price', 1 );
		$this->t['zero_payment_price']		= $this->p->get( 'zero_payment_price', 1 );
		$this->t['checkout_scroll']			= $this->p->get( 'checkout_scroll', 1 );
		$this->t['enable_coupons']			= $this->p->get( 'enable_coupons', 1 );
		$this->t['enable_rewards']			= $this->p->get( 'enable_rewards', 1 );
		$this->t['checkout_icon_status']	= $this->p->get( 'checkout_icon_status', 1 );
		
		
		$this->t['enable_captcha_checkout']	= PhocacartCaptcha::enableCaptchaCheckout();
		
		$scrollTo							= '';
		
		
		// Cart
		$this->cart	= new PhocacartCartRendercheckout();
		$this->cart->setFullItems();

		if ((int)$this->u->id > 0) {
			$this->a->login = 1;
		} else if ($guest) {
			$this->a->login = 2;
		}
		
		// Is there even a shipping or payment
		$this->a->shippingnotused 	= PhocacartShipping::isShippingNotUsed();
		$this->a->paymentnotused	= PhocacartPayment::isPaymentNotUsed();
		
		
		
		// Numbers
		$this->t['nl'] = 1;// Login
		$this->t['na'] = 2;// Address
		$this->t['ns'] = 3;// Shipping
		$this->t['np'] = 4;// Payment
		if ($this->a->shippingnotused == 1) {
			$this->t['np'] = 3;
		}

		if ($this->a->login == 1 || $this->a->login == 2) {

			// =======
			// ADDRESS
			// =======
			$this->a->addressedit		= $app->input->get('addressedit', 0, 'int'); // Edit Address
		
			// GUEST
			if ($this->a->login == 2) {
				// Check if all form items are filled out by user, if yes, don't load the form and save some queries
				$this->fields	= $this->get('FieldsGuest'); // Fields will be loaded in every case				
				if ($this->a->addressedit == 0) {
					$this->data	= $this->get('DataGuest');
					$this->t['dataaddressoutput']	= PhocacartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u, 1);
				}
				//Some required field is not filled out 
				if (isset($this->t['dataaddressoutput']['filled']) && $this->t['dataaddressoutput']['filled'] == 1) {
					$this->a->addressadded = 1;
				} else {
					$this->a->addressadded = 0;
				}
				// Load form and fields to edit address
				if ($this->a->addressadded == 0 || $this->a->addressedit == 1) {
					$this->a->addressview 		= 0;
					$this->a->addressedit 		= 1;
					$scrollTo 					= 'phcheckoutaddressedit';
					$this->form					= $this->get('FormGuest');
					$this->t['dataaddressform']	= PhocacartUser::getAddressDataForm($this->form, $this->fields['array'], '', '', '_phs', 1);
				}
			// REGISTERED
			} else {
				// Check if all form items are filled out by user, if yes, don't load the form and save some queries
				$this->fields	= $this->get('Fields'); // Fields will be loaded in every case
				if ($this->a->addressedit == 0) {
					$this->data	= $this->get('Data');
					$this->t['dataaddressoutput']	= PhocacartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u);
				}
				//Some required field is not filled out 
				if (isset($this->t['dataaddressoutput']['filled']) && $this->t['dataaddressoutput']['filled'] == 1) {
					$this->a->addressadded = 1;
				} else {
					$this->a->addressadded = 0;
				}
				// Load form and fields to edit address
				if ($this->a->addressadded == 0 || $this->a->addressedit == 1) {
					$this->a->addressview 		= 0;
					$this->a->addressedit 		= 1;
					$this->form					= $this->get('Form');
					$this->t['dataaddressform']	= PhocacartUser::getAddressDataForm($this->form, $this->fields['array'], $this->u);
					$scrollTo 					= 'phcheckoutaddressedit';
				}
			}
			
			if ($this->a->addressadded == 1 && $this->a->addressedit == 0) {
				$this->a->addressview = 1;
				$scrollTo = 'phcheckoutaddressview';
			}
			
			
			// ====================
			// SHIPPING
			// ====================
			if ($this->a->addressadded == 1 && $this->a->addressedit == 0) {
			
				$this->a->shippingedit	= $app->input->get('shippingedit', 0, 'int'); // Edit Shipping
				$shippingId 			= $this->cart->getShippingId();// Shipping stored in cart or not?

				if (isset($shippingId) && (int)$shippingId > 0 && $this->a->shippingedit == 0) {
					// Shipping method is stored in cart, we can update the cart (add shipping costs to whole cart)
					$this->a->shippingadded 	= 1;
					$this->a->shippingview		= 1;
					$scrollTo 					= 'phcheckoutshippingview';
					$this->cart->addShippingCosts($shippingId);
					$this->t['shippingmethod'] = $this->cart->getShippingCosts();
					
				} else {
					// Shipping cost is not stored in cart, display possible shipping methods
					// We ask for total of cart because of amount rule
					$this->a->shippingadded 	= 0;
					$this->a->shippingview		= 0;
					$this->a->shippingedit		= 1;
					$scrollTo 					= 'phcheckoutshippingedit';
					$shipping					= new PhocacartShipping();
					$total						= $this->cart->getTotal();
					
					$country = 0;
					if(isset($this->t['dataaddressoutput']['bcountry']) && (int)$this->t['dataaddressoutput']['bcountry']) {
						$country = (int)$this->t['dataaddressoutput']['bcountry'];
					}
					
					$region = 0;
					if(isset($this->t['dataaddressoutput']['bregion']) && (int)$this->t['dataaddressoutput']['bregion']) {
						$region = (int)$this->t['dataaddressoutput']['bregion'];
					}
					
					
					$this->t['shippingmethods']	= $shipping->getPossibleShippingMethods($total[0]['netto'], $total[0]['brutto'], $total[0]['quantity'], $country, $region, $total[0]['weight'], $total[0]['max_length'], $total[0]['max_width'], $total[0]['max_height'], 0, $shippingId);
					
				}
			}
			
			// =================
			// PAYMENT (VOUCHER)
			// =================
			
			if ($this->a->addressadded == 1 && $this->a->addressedit == 0 && (($this->a->shippingadded == 1 && $this->a->shippingedit == 0) || $this->a->shippingnotused == 1)) {
				$this->a->paymentedit	= $app->input->get('paymentedit', 0, 'int'); // Edit Shipping
				$this->t['paymentmethod'] = $this->cart->getPaymentMethod();
				
				if (isset($this->t['paymentmethod']['id']) && (int)$this->t['paymentmethod']['id'] > 0 && $this->a->paymentedit == 0) {
					$this->cart->addPaymentCosts($this->t['paymentmethod']['id']);
					$this->t['paymentmethod'] 	= $this->cart->getPaymentCosts();
					$this->a->paymentadded 		= 1;
					$this->a->paymentview 		= 1;
					$scrollTo 					= 'phcheckoutpaymentview';
				} else {
					// Payment cost is not stored in cart, display possible payment methods
					// We ask for total of cart because of amount rule
					$this->a->paymentadded 		= 0;
					$this->a->paymentview		= 0;
					$this->a->paymentedit		= 1;
					$scrollTo 					= 'phcheckoutpaymentedit';
					$payment					= new PhocacartPayment();
					$shippingId 				= $this->cart->getShippingId();// Shipping stored in cart or not?
					$total						= $this->cart->getTotal();
					
					$country = 0;
					if(isset($this->t['dataaddressoutput']['bcountry']) && (int)$this->t['dataaddressoutput']['bcountry']) {
						$country = (int)$this->t['dataaddressoutput']['bcountry'];
					}
					
					$region = 0;
					if(isset($this->t['dataaddressoutput']['bregion']) && (int)$this->t['dataaddressoutput']['bregion']) {
						$region = (int)$this->t['dataaddressoutput']['bregion'];
					}
					
					$this->t['paymentmethods']	= $payment->getPossiblePaymentMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $shippingId, 0, $this->t['paymentmethod']['id']);
					
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
				}
			}
			
			PhocacartRenderJs::renderBillingAndShippingSame();
		}
		
		$this->cart->roundTotalAmount();
		

		
		// VIEW - CONFIRM - all items added 
		if (($this->a->login == 1 || $this->a->login == 2) && $this->a->addressview == 1 && $this->a->shippingview == 1 && $this->a->paymentview == 1) {
			$this->a->confirm = 1;
			
			// Custom "Confirm Order" Text
			$total							= $this->cart->getTotal();
			
			$this->t['confirm_order_text']	= PhocacartRenderFront::getConfirmOrderText($total[0]['brutto']);
		}
		
		// CART IS EMPTY
		// Don't allow to add or edit payment or shipping method, don't allow to confirm the order
		if (empty($this->cart->getItems())) {
			$this->a->shippingnotused 	= 1;
			$this->a->paymentnotused	= 1;
			$this->a->confirm 			= 0;
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

		$media = new PhocacartRenderMedia();
		$media->loadBootstrap();
		$media->loadChosen();
		$media->loadWindowPopup();
		

		//Scroll to 
		if ($this->t['checkout_scroll'] == 0) {
			$scrollTo = '';
		}
		if ($scrollTo == '') {
		} else if ($scrollTo == 'phcheckoutaddressedit' || $scrollTo == 'phcheckoutshippingedit' || $scrollTo == 'phcheckoutpaymentedit') {
			PhocacartRenderJs::renderJsScrollTo($scrollTo, 0);
		} else if ($scrollTo == 'phcheckoutpaymentview') {
			// last view - in fact phcheckoutconfirmedit
			PhocacartRenderJs::renderJsScrollTo($scrollTo, 1);
		} else {
			PhocacartRenderJs::renderJsScrollTo('', 0);
		}

		
		
		// Render the cart (here because it can be changed above - shipping can be added)
		//$total				= $this->cart->getTotal();
		$this->t['cartoutput']			= $this->cart->render();
		$this->t['stockvalid']			= $this->cart->getStockValid();
		$this->t['minqtyvalid']			= $this->cart->getMinimumQuantityValid();
		$this->t['minmultipleqtyvalid']	= $this->cart->getMinimumMultipleQuantityValid();

		$this->_prepareDocument();
		
		// Plugins ------------------------------------------
		JPluginHelper::importPlugin('pcv');
		$this->t['dispatcher']	= JEventDispatcher::getInstance();
		$this->t['event']		= new stdClass;
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterCart', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterCart = trim(implode("\n", $results));
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterLogin', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterLogin = trim(implode("\n", $results));
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterAddress', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterAddress = trim(implode("\n", $results));
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterShipping', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterShipping = trim(implode("\n", $results));
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterPayment', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterPayment = trim(implode("\n", $results));
		
		$results = $this->t['dispatcher']->trigger('onCheckoutAfterConfirm', array('com_phocacart.checkout', $this->a, &$this->p));
		$this->t['event']->onCheckoutAfterConfirm = trim(implode("\n", $results));
		
		// END Plugins --------------------------------------
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_CHECKOUT'));
	}
}
?>