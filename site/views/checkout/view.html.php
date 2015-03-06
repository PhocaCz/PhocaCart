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
		
		$document					= JFactory::getDocument();		
		$app						= JFactory::getApplication();
		$uri 						= JFactory::getURI();
		$this->u					= JFactory::getUser();
		$this->p					= $app->getParams();
		$this->a					= new PhocaCartAccess();
		$guest						= PhocaCartGuestUser::getGuestUser();
		
		$this->t['action']			= $uri->toString();
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkcheckout']	= JRoute::_(PhocaCartRoute::getCheckoutRoute());
		
		$this->t['checkout_desc']	= $this->p->get( 'checkout_desc', '');
		$this->t['load_bootstrap']	= $this->p->get( 'load_bootstrap', 0 );
		$this->t['stock_checkout']	= $this->p->get( 'stock_checkout', 0 );
		$this->t['guest_checkout']	= $this->p->get( 'guest_checkout', 0 );
		$scrollTo					= '';
		
		/*$cart	= new PhocaCartRenderCheckout();
echo '<div class="alert alert-info">';
echo $cart->render();
echo '</div>';*/

		// Style
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		JHtml::_('jquery.framework', false);
		if ($this->t['load_bootstrap'] == 1) {
			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			//$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		
		// Cart
		$this->cart	= new PhocaCartRenderCheckout();
		$this->cart->setFullItems();
		
		
		
		if ((int)$this->u->id > 0) {
			$this->a->login = 1;
		} else if ($guest) {
			$this->a->login = 2;
		}
		
		
		// Is there even a shipping or payment
		$this->a->shippingnotused 	= PhocaCartShipping::isShippingNotUsed();
		$this->a->paymentnotused	= PhocaCartPayment::isPaymentNotUsed();
		// Numbers
		$this->t['nl'] = 1;
		$this->t['na'] = 2;
		$this->t['ns'] = 3;
		$this->t['np'] = 4;
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
					$this->t['dataaddressoutput']	= PhocaCartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u, 1);
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
					$this->t['dataaddressform']	= PhocaCartUser::getAddressDataForm($this->form, $this->fields['array'], '', '', '_phs', 1);
				}
			// REGISTERED
			} else {
				// Check if all form items are filled out by user, if yes, don't load the form and save some queries
				$this->fields	= $this->get('Fields'); // Fields will be loaded in every case
				if ($this->a->addressedit == 0) {
					$this->data	= $this->get('Data');
					$this->t['dataaddressoutput']	= PhocaCartUser::getAddressDataOutput($this->data, $this->fields['array'], $this->u);
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
					$this->t['dataaddressform']	= PhocaCartUser::getAddressDataForm($this->form, $this->fields['array'], $this->u);
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
					$shipping					= new PhocaCartShipping();
					$total						= $this->cart->getTotal();
					
					$country = 0;
					if(isset($this->t['dataaddressoutput']['bcountry']) && (int)$this->t['dataaddressoutput']['bcountry']) {
						$country = (int)$this->t['dataaddressoutput']['bcountry'];
					}
					$region = 0;
					if(isset($this->t['dataaddressoutput']['bregion']) && (int)$this->t['dataaddressoutput']['bregion']) {
						$region = (int)$this->t['dataaddressoutput']['bregion'];
					}
					$this->t['shippingmethods']	= $shipping->getPossibleShippingMethods($total['netto'], $total['brutto'], $country, $region, $total['weight']);
					
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
					$payment					= new PhocaCartPayment();
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
					$this->t['paymentmethods']	= $payment->getPossiblePaymentMethods($total['netto'], $total['brutto'], $country, $region, $shippingId);
				}
			}
			
	
			//CHOSEN
			$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
			$document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
			$js = "\n". 'jQuery(document).ready(function(){';
			$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
			$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
			$js .= '});'."\n";
			$document->addScriptDeclaration($js);
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
			
			PhocaCartRenderJs::renderBillingAndShippingSame();
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
		}
		
		
 
		
		
		//Scroll to 
		if ($scrollTo == '') {
		} else if ($scrollTo == 'phcheckoutaddressedit' || $scrollTo == 'phcheckoutshippingedit' || $scrollTo == 'phcheckoutpaymentedit') {
			$js = "\n"
			. 'jQuery(function() {
				if (jQuery("#ph-msg-ns").length > 0){
					jQuery(document).scrollTop( jQuery("#system-message").offset().top );
					//jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );
				} else {
					jQuery(document).scrollTop( jQuery("#'.$scrollTo.'").offset().top );
					//jQuery(\'html, body\').animate({scrollTop: jQuery("#'.$scrollTo.'").offset().top}, 1500 );
				}
			});';

			$document->addScriptDeclaration($js);
		} else if ($scrollTo == 'phcheckoutpaymentview') {
			// last view - in fact phcheckoutconfirmedit
			$js = "\n"
			. 'jQuery(function() {
				if (jQuery("#ph-msg-ns").length > 0){
					jQuery(document).scrollTop( jQuery("#system-message").offset().top );
					//jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );
				} else {
					//jQuery(document).scrollTop( jQuery("#'.$scrollTo.'").offset().top );
					jQuery(\'html,body\').animate({scrollTop: jQuery("#'.$scrollTo.'").offset().top}, 1500 );
				}
			});';
			$document->addScriptDeclaration($js);
		} else {
			/*$js = "\n". 'jQuery(function() {
				jQuery(\'html, body\').animate({ scrollTop: jQuery("#'.$scrollTo.'").offset().top}, 1000);
			});';*/
			$js = "\n"
			. 'jQuery(function() {
				if (jQuery("#ph-msg-ns").length > 0){
					jQuery(document).scrollTop( jQuery("#system-message").offset().top );
					//jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );
				}
			});';
			$document->addScriptDeclaration($js);
		}
		
		
		
		
		// Render the cart (here because it can be changed above - shipping can be added)
		//$total						= $this->cart->getTotal();
	
		
		$this->t['cartoutput']	= $this->cart->render();
		$this->t['stockvalid']	= $this->cart->getStockValid();
		$this->t['minqtyvalid']	= $this->cart->getMinimumQuantityValid();
		

		$this->_prepareDocument();
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>