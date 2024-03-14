<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

class PhocaCartControllerPos extends FormController
{

	// Add item to cart
	function add($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}



		$app					= Factory::getApplication();

		$paramsC 				= PhocacartUtils::getComponentParameters();
		$pos_payment_force	= $paramsC->get( 'pos_payment_force', 0 );
		$pos_shipping_force	= $paramsC->get( 'pos_shipping_force', 0 );

		if ((int)$pos_payment_force > 0) {
            $pos_payment_force = PhocacartPayment::isPaymentMethodActive($pos_payment_force) === true ? (int)$pos_payment_force : 0;
        }
        if ((int)$pos_shipping_force > 0) {
            $pos_shipping_force = PhocacartShipping::isShippingMethodActive($pos_shipping_force) === true ? (int)$pos_shipping_force : 0;
        }

		$item					= array();
		$item['id']				= $this->input->get( 'id', 0, 'int' );
		$item['catid']			= $this->input->get( 'catid', 0, 'int' );
		$item['ticketid']		= $this->input->get( 'ticketid', 0, 'int' );
		$item['unitid']			= $this->input->get( 'unitid', 0, 'int' );
		$item['sectionid']		= $this->input->get( 'sectionid', 0, 'int' );
		$item['quantity']		= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['attribute']		= $this->input->get( 'attribute', array(), 'array'  );
		$item['checkoutview']	= $this->input->get( 'checkoutview', 0, 'int'  );
		$item['sku']			= $this->input->get( 'sku', '', 'string' );


		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');

		// IMPORTANT
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES


		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);


		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}

		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}

		if ($item['sku'] != '') {

			$preferredSku = PhocacartPos::getPreferredSku();// Select if SKU, EAN, ISBN, etc.

			$productBySku = PhocacartProduct::getProductIdBySku($item['sku'], $preferredSku['name'], array(0,2));

			if (isset($productBySku['id']) && (int)$productBySku['id'] > 0 && isset($productBySku['catid']) && (int)$productBySku['catid'] > 0) {
				$item['id'] = (int)$productBySku['id'];
				$item['catid'] = (int)$productBySku['catid'];

				if (!empty($productBySku['attributes'])) {
					$item['attribute'] = $productBySku['attributes'];
				}
			} else {

				$response = array(
					'status' => '0',
					'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PRODUCT_NOT_FOUND') . '</div>');
				echo json_encode($response);
				return;

			}
		}



		$cart	= new PhocacartCartRendercheckout();
		$cart->setType(array(0,2));
		$cart->params['display_image'] 				= 1;
		$cart->params['display_checkout_link'] 		= 0;
		$cart->params['display_product_tax_info'] 	= 0;



		$added	= $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute'], '', array(0,2));

		if (!$added) {

			$d 				= array();
			$d['s']			= $s;
			$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();;
			$layoutPE		= new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
			$oE 			= $layoutPE->render($d);
			$response = array(
				'status' => '0',
				'popup'	=> $oE,
				'error' => '<div class="ph-result-txt ph-error-txt">' . $d['info_msg'] . '</div>');
			echo json_encode($response);
			return;
		}

		$cart->setFullItems();
		$cart->updateShipping();// will be decided if shipping or payment will be removed
        $cart->updatePayment();

		// When adding new product - shipping and payment is removed - don't add it again from not updated class (this $cart instance does not include the info about removed shipping and payment)
		// But there is an exception in case of forced payment or shipping
	//	if ((int)$pos_shipping_force > 0) {
			$shippingId = $cart->getShippingId();


			if (isset($shippingId) && (int)$shippingId > 0) {
				$cart->addShippingCosts($shippingId);
			}
	//	}

	//	if ((int)$pos_payment_force > 0) {
			$paymentId = $cart->getPaymentId();

			if (isset($paymentId) && (int)$paymentId > 0) {
				$cart->addPaymentCosts($paymentId);// validity of payment will be checked
			}
	//	}


		$cart->roundTotalAmount();

		$o = $o2 = '';

		ob_start();
		echo $cart->render();
		$o = ob_get_contents();
		ob_end_clean();

		$price	= new PhocacartPrice();
		$count	= $cart->getCartCountItems();
		$total	= 0;
		$totalA	= $cart->getCartTotalItems();
		if (isset($totalA[0]['brutto'])) {
			//$total = $price->getPriceFormat($totalA['fbrutto']); Set in Layout
			$total = $totalA[0]['brutto'];
		}




		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'message'	=> '<div class="ph-result-txt ph-success-txt">' . Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART') . '</div>',
			'popup'		=> $o2,
			'count'		=> $count,
			'total'		=> $total);

		echo json_encode($response);
		return;


	}







	// Add item to cart
	function update($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}




		$app				= Factory::getApplication();

		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['idkey']		= $this->input->get( 'idkey', '', 'string' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['ticketid']	= $this->input->get( 'ticketid', 0, 'int' );
		$item['unitid']		= $this->input->get( 'unitid', 0, 'int' );
		$item['sectionid']	= $this->input->get( 'sectionid', 0, 'int' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['attribute']	= $this->input->get( 'attribute', array(), 'array'  );
		$item['checkoutview']	= $this->input->get( 'checkoutview', 0, 'int'  );
		$item['action']		= $this->input->get( 'action', '', 'string'  );

		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES

		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}
		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}


		if ((int)$item['idkey'] != '' && $item['action'] != '') {

			$cart	= new PhocacartCartRendercheckout();
			$cart->setType(array(0,2));
			$cart->params['display_image'] 			= 1;
			$cart->params['display_checkout_link'] 	= 0;
			$cart->params['display_product_tax_info'] 	= 0;

			if ($item['action'] == 'delete') {
				$updated	= $cart->updateItemsFromCheckout($item['idkey'], 0);

				if (!$updated) {

					$d 				= array();
					$d['s']			= $s;
					$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
					$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();;
					$layoutPE		= new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
					$oE 			= $layoutPE->render($d);
					$response = array(
						'status' => '0',
						'popup'	=> $oE,
						'error' => $d['info_msg']);
					echo json_encode($response);

					return;
				}


				/*if ($updated) {
					$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'message');
				} else {
					$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
				}*/
			} else {// update
				$updated	= $cart->updateItemsFromCheckout($item['idkey'], (int)$item['quantity']);

				if (!$updated) {

					$d 				= array();
					$d['s']			= $s;
					$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
					$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();
					$layoutPE		= new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
					$oE 			= $layoutPE->render($d);
					$response = array(
						'status' => '0',
						'popup'	=> $oE,
						'error' => $d['info_msg']);
					echo json_encode($response);

					return;
				}
				/*if ($updated) {
					$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED') .$msgSuffix , 'message');
				} else {
					$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
				}*/
			}


			$cart->setFullItems();

			$cart->updateShipping();// will be decided if shipping or payment will be removed
        	$cart->updatePayment();

			$shippingId 	= $cart->getShippingId();

			if (isset($shippingId) && (int)$shippingId > 0) {
				$cart->addShippingCosts($shippingId);
			}

			$paymentMethod 	= $cart->getPaymentMethod();
			if (isset($paymentMethod['id']) && (int)$paymentMethod['id'] > 0) {
				$cart->addPaymentCosts($paymentMethod['id']);// validity of payment will be checked
			}


			$cart->roundTotalAmount();

			$o = $o2 = '';

			ob_start();
			echo $cart->render();
			$o = ob_get_contents();
			ob_end_clean();

			$price	= new PhocacartPrice();
			$count	= $cart->getCartCountItems();
			$total	= 0;
			$totalA	= $cart->getCartTotalItems();
			if (isset($totalA[0]['brutto'])) {
				//$total = $price->getPriceFormat($totalA['fbrutto']); Set in Layout
				$total = $totalA[0]['brutto'];
			}



			$message = $item['action'] == 'delete' ? Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') : Text::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED');
			$response = array(
				'status'	=> '1',
				'item'		=> $o,
				'message'	=> '<div class="ph-result-txt ph-success-txt">' . $message . '</div>',
				'popup'		=> $o2,
				'count'		=> $count,
				'total'		=> $total);

			echo json_encode($response);
			return;
		} else {



			// No action, no id - only refresh the cart (information about ticketid, unitid, sectionid set in cart)
			$cart	= new PhocacartCartRendercheckout();
			$cart->setType(array(0,2));
			$cart->params['display_image'] 			= 1;
			$cart->params['display_checkout_link'] 	= 0;
			$cart->params['display_product_tax_info'] 	= 0;
			// Ticket id set by ticket class
			$cart->setFullItems();




			$cart->updateShipping();// will be decided if shipping or payment will be removed
        	$cart->updatePayment();


        	$db 	= Factory::getDBO();
			$query = ' SELECT shipping FROM #__phocacart_cart_multiple AS a'
					.' WHERE a.vendor_id = 211';
			$db->setQuery($query);
			$vendor = $db->loadObject();


			$shippingId 	= $cart->getShippingId();

			if (isset($shippingId) && (int)$shippingId > 0) {
				$cart->addShippingCosts($shippingId);
			}

			$paymentMethod 	= $cart->getPaymentMethod();
			if (isset($paymentMethod['id']) && (int)$paymentMethod['id'] > 0) {
				$cart->addPaymentCosts($paymentMethod['id']);// validity of payment will be checked
			}



			$cart->roundTotalAmount();

			$o = $o2 = '';

			ob_start();
			echo $cart->render();
			$o = ob_get_contents();
			ob_end_clean();

			$price	= new PhocacartPrice();
			$count	= $cart->getCartCountItems();
			$total	= 0;
			$totalA	= $cart->getCartTotalItems();

			if (isset($totalA[0]['brutto'])) {
				//$total = $price->getPriceFormat($totalA['fbrutto']); Set in Layout
				$total = $totalA[0]['brutto'];
			}





			$response = array(
				'status'	=> '1',
				'item'		=> $o,
				'popup'		=> $o2,
				'count'		=> $count,
				'total'		=> $total);

			echo json_encode($response);
			return;
		}

		$response = array(
			'status' => '0',
			'popup'	=> '',
			'error' => '');
		echo json_encode($response);
		return;

	}



	function savecustomer($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app							= Factory::getApplication();

		$item							= array();
		$item['id']						= $this->input->get( 'id', 0, 'int' );
		$item['card']					= $this->input->get( 'card', '', 'string' );
		$item['loyalty_card_number'] 	= '';

		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}
		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}

		if ($item['card'] != '') {


			$userByCardId = PhocacartUser::getUserIdByCard($item['card']);

			if (isset($userByCardId) && (int)$userByCardId > 0) {
				$item['id'] = (int)$userByCardId;
			} else {
				$item['id'] = 0;
				$item['loyalty_card_number'] = $item['card'];

			}
		}





		$updated = PhocacartPos::updateUserCart($vendor->id, $ticket->id, $unit->id, $section->id, $item['id'], $item['loyalty_card_number']);


		if ($updated) {

			// Remove shipping because shipping methods can change while chaning users
			//PhocacartShipping::removeShippingAfterUpdate(0, 2);
			//PhocacartPayment::removePayment(0);
			$cart					= new PhocacartCartRendercheckout();
        	$cart->setType(array(0,2));
        	$cart->setFullItems();
        	$cart->updateShipping();// will be decided if shipping or payment will be removed
			$cart->updatePayment();

			if ($item['id'] > 0 && $item['loyalty_card_number'] == '') {
				$msg = Text::_('COM_PHOCACART_SUCCESS_CUSTOMER_SELECTED');
			} else if ($item['loyalty_card_number'] != '') {
				$msg = Text::_('COM_PHOCACART_SUCCESS_USER_NOT_FOUND_BY_LOYALTY_CARD_NUMBER');
				$msg .= '<br />' . Text::_('COM_PHOCACART_ANONYMOUS_USER_SET');
			} else {
				$msg = Text::_('COM_PHOCACART_SUCCESS_CUSTOMER_DESELECTED');
			}

			$response = array(
				'status' => '1',
				'message'=> '<div class="ph-result-txt ph-success-txt">' . $msg . '</div>');

			echo json_encode($response);
			return;
		} else {

			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_ERROR_CART_NOT_UPDATED') . '</div>');
			echo json_encode($response);
			return;
		}
	}


	public function saveshipping() {

	 	if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();

		$item		= array();
		$item['id']	= $this->input->get( 'id', 0, 'int'  );

		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}
		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}

		$model 	= $this->getModel('pos');
		if(!$model->saveShipping((int)$item['id'])) {
			$msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
			$app->enqueueMessage($msg, 'error');
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . PhocacartRenderFront::renderMessageQueue() . '</div>');
			echo json_encode($response);
			return;
		} else {
			$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
			$app->enqueueMessage($msg, 'message');


			$response = array(
				'status' => '1',
				'message' => '<div class="ph-result-txt ph-success-txt">' . PhocacartRenderFront::renderMessageQueue() . '</div>');
			echo json_encode($response);
			return;
		}
	}



	public function savepayment() {

	 	if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app				= Factory::getApplication();

		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int'  );
		$item['phcoupon']	= $this->input->get( 'phcoupon', '', 'string'  );
		$item['phreward']	= $this->input->get( 'phreward', '', 'int'  );
		$params 			= $app->getParams();

		$enable_coupons		= $params->get( 'enable_coupons', 2 );
		$enable_rewards		= $params->get( 'enable_rewards', 1 );

		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}
		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}

		// Coupon
		$couponId = 0;

		if (isset($item['phcoupon']) && $item['phcoupon'] != '' && $enable_coupons > 0) {

			$coupon = new PhocacartCoupon();
			$coupon->setType(array(0,2));
			$coupon->setCoupon(0, $item['phcoupon']);

			$couponTrue = $coupon->checkCoupon(1);// Basic Check - Coupon True does not mean it is valid

			$couponId 	= 0;


			if ($couponTrue) {
				$couponData = $coupon->getCoupon();
				if (isset($couponData['id']) && $couponData['id'] > 0) {
					$couponId = $couponData['id'];
				}
			}

			if(!$couponId) {
				$msg = Text::_('COM_PHOCACART_COUPON_INVALID_EXPIRED_REACHED_USAGE_LIMIT');
				$app->enqueueMessage($msg, 'error');
			} else {
				$msg = Text::_('COM_PHOCACART_COUPON_ADDED');
				$app->enqueueMessage($msg, 'message');
			}
		}

		$rewards 			= array();
		$rewards['used'] 	= 0;

		if (isset($item['phreward']) && $item['phreward'] != '' && $enable_rewards) {

			$reward 			= new PhocacartReward();
			$rewards['used']	= $reward->checkReward((int)$item['phreward'], 1);


			if($rewards['used'] === false) {
				$msg = Text::_('COM_PHOCACART_REWARD_POINTS_NOT_ADDED');
				$app->enqueueMessage($msg, 'error');
			} else {
				$msg = Text::_('COM_PHOCACART_REWARD_POINTS_ADDED');
				$app->enqueueMessage($msg, 'message');
			}

		}



		$model 	= $this->getModel('pos');


		if(!$model->savePaymentAndCouponAndReward((int)$item['id'], $couponId, $rewards['used'])) {
			$msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
			$app->enqueueMessage($msg, 'error');
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . PhocacartRenderFront::renderMessageQueue() . '</div>');
			echo json_encode($response);
			return;
		} else {
			$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
			$app->enqueueMessage($msg, 'message');
			$response = array(
				'status' => '1',
				'message' => '<div class="ph-result-txt ph-success-txt">' . PhocacartRenderFront::renderMessageQueue() . '</div>');
			echo json_encode($response);
			return;
		}
	}


	/*public function printserver() {

		$app				= Factory::getApplication();
		$item				= array();
		$params 			= $app->getParams();
		$pos_server_print	= $params->get( 'pos_server_print', 0 );
		$item['id']			= $this->input->get( 'id', 0, 'int'  );// Order ID

	}*/


	public function order() {


		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app				= Factory::getApplication();

		$item				= array();

		$item									= array();
		$item['amount_tendered']				= $this->input->get( 'phAmountTendered', '', 'string'  );
		$item['amount_pay']						= $this->input->get( 'phTotalAmount', '', 'string'  );
		//$item['amount_change']					= $this->input->get( 'phAmountChange', '', 'string'  );


		if ($item['amount_pay'] > 0 && $item['amount_tendered'] > 0) {
			$item['amount_change'] = $item['amount_tendered'] - $item['amount_pay'];
		} else if ($item['amount_pay'] > 0) {

			$item['amount_tendered']= 0;
			$item['amount_change']	= 0;
		} else {
			$item['amount_tendered']= 0;
			$item['amount_pay']		= 0;
			$item['amount_change']	= 0;
		}

		$params 			= $app->getParams();
		$pos_server_print	= $params->get( 'pos_server_print', 0 );

		// Controller name in Joomla! is not called "pos" - it includes task variable
		// so we need to set the name for controller to "pos"
		// so other parts of system (for example cart class know we are calling it from pos controller)
		$this->input->set('controller', 'pos');
		$s 					    = PhocacartRenderStyle::getStyles();//MUST BE SET AFTER $this->input->set('controller', 'pos'); TO GET RIGHT CLASSES
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (!isset($vendor->id) || (isset($vendor->id) && (int)$vendor->id < 1)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_PLEASE_LOGIN_ACCESS_POS') . '</div>');
			echo json_encode($response);
			return;
		}
		if (!PhocacartTicket::existsTicket((int)$vendor->id, (int)$ticket->id, (int)$unit->id, (int)$section->id)) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_TICKET_DOES_NOT_EXIST') . '</div>');
			echo json_encode($response);
			return;
		}





		$order = new PhocacartOrder();
		$order->setType(array(0,2));
		$orderMade = $order->saveOrderMain($item);



		if(!$orderMade) {
			$msg = '';
			if (!PhocacartUtils::issetMessage()){
				$msg = Text::_('COM_PHOCACART_ORDER_ERROR_PROCESSING');
			}
			$app->enqueueMessage($msg, 'error');
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . PhocacartRenderFront::renderMessageQueue() . '</div>');
			echo json_encode($response);
			return;

		} else {

			$cart = new PhocacartCart();

			// Before removing current cart after payment get the info about current vendor,ticket,unit,section
			// to create new empty ticket after payment
			$vendorId			= $cart->getVendorId();
			$ticketId			= $cart->getTicketId();
			$unitId				= $cart->getUnitId();
			$sectionId			= $cart->getSectionId();

			$cart->emptyCart();
			PhocacartUserGuestuser::cancelGuestUser();

			$action 	= $order->getActionAfterOrder();// Which action should be done
			$message	= $order->getMessageAfterOrder();// Custom message by payment plugin Payment/Download, Payment/No Download ...


			// Create empty ticket (with the same ticket, unit and section) after this current was removed
			PhocacartTicket::addNewVendorTicket($vendorId, $ticketId, $unitId, $sectionId);
		/*	$msg = '';
			if (!empty($message)) {
				foreach ($message as $k => $v) {
					print r($v);
				}
			}*/
			$msg = Text::_('COM_PHOCACART_ORDER_HAS_BEEN_SAVED_SUCCESSFULLY');


			// PRINT SERVER PRINT
			if ($pos_server_print == 1 || $pos_server_print == 3) {
				$order	= new PhocacartOrderRender();
				$o = $order->render((int)$orderMade, 4, 'raw', '', 1);

				try{

					$printPos = new PhocacartPosPrint(1);
					$printPos->printOrder($o);
					//echo Text::_('COM_PHOCACART_RECEIPT_SENT_TO_PRINTER');
				} catch(Exception $e) {
					$msg .= "<br />" . Text::_('COM_PHOCACART_ERROR'). ': '. $e->getMessage();
					/*$response = array(
					'status' => '1',
					'id'	=> (int)$orderMade,
					'message' => '<div class="ph-result-txt ph-error-txt">' .$msg . '</div>');
					echo json_encode($response);
					return;*/
				}


			}

			$session 	= Factory::getSession();
			if ($action == 4 || $action == 3) {
				// Ordered OK, but now we proceed to payment
				//$session->set('infoaction', $action, 'phocaCart');
				//$session->set('infomessage', $message, 'phocaCart');
				$response = array(
				'status' => '1',
				'id'	=> (int)$orderMade,
				'message' => '<div class="ph-result-txt ph-success-txt">' .$msg . '</div>');
				echo json_encode($response);
				return;
				//return true;
				// This message should stay
				// when order - the message is created
				// when payment - the message stays unchanged
				// after payment - it will be redirected to info view and there the message will be displayed and then deleted

			} else {
				// Ordered OK, but the payment method does not have any instruction to proceed to payment (e.g. cash on delivery)
				//$msg = Text::_('COM_PHOCACART_ORDER_SUCCESSFULLY_PROCESSED');
				// We produce not message but we redirect to specific view with message and additional instructions
				//$app->enqueueMessage($msg, 'message');
				$response = array(
				'status' => '1',
				'id'	=> (int)$orderMade,
				'message' => '<div class="ph-result-txt ph-success-txt">' .$msg . '</div>');
				echo json_encode($response);
				return;
				//$session->set('infoaction', $action, 'phocaCart');
				//$session->set('infomessage', $message, 'phocaCart');
				//$app->redirect(Route::_(PhocacartRoute::getInfoRoute(), false));
				return true;
			}
		}


	}
}
?>
