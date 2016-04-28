<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartOrder
{
	public $downloadable_product;
	public $action_after_order;

	public function __construct() {
		$this->downloadable_product = 0;// if there will be at least one downloadable file in order, we will mark it to display 
										// right thank you message
		$this->action_after_order	= 1;// which action will be done after order - end, procceed to payment, ...
		
	}
	public function saveOrder($comment) {
		
		$msgSuffix			= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		$pC 				= JComponentHelper::getParams('com_phocacart') ;
		$min_order_amount	= $pC->get( 'min_order_amount', 0 );
		$stock_checkout		= $pC->get( 'stock_checkout', 0 );
		$stock_checking		= $pC->get( 'stock_checking', 0 );
		$unit_weight		= $pC->get( 'unit_weight', '' );
		$unit_volume		= $pC->get( 'unit_volume', '' );
		
		$uri 			= JFactory::getURI();
		$action			= $uri->toString();
		$app			= JFactory::getApplication();
		
		$user			= JFactory::getUser();
		$guest			= PhocaCartGuestUser::getGuestUser();
		$cart			= new PhocaCartRenderCheckout();
		$cart->setFullItems();
		$items 			= $cart->getFullItems();
		$currency		= PhocaCartCurrency::getCurrency();
		
		if (!$items) {
			$msg = JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		}
		$shippingId	= $cart->getShippingId();
		$cart->addShippingCosts($shippingId);
		$shippingC		= $cart->getShippingCosts();
		$payment		= $cart->getPaymentMethod();
		$cart->addPaymentCosts($payment['id']);
		$paymentC		= $cart->getPaymentCosts();
		$couponCart		= $cart->getCoupon();
		$coupon			= false;
		if (isset($couponCart['id']) && $couponCart['id'] > 0) {
			$couponO = new PhocaCartCoupon();
			$couponO->setCoupon((int)$couponCart['id']);
			$coupon = $couponO->getCoupon();
		}
		if (!$coupon) {
			$coupon = $couponCart;
		}
		
		
		
		
		$total		= $cart->getTotal();
		
		
		// --------------------
		// CHECK MINIMUM ORDER AMOUNT
		// --------------------
		if($min_order_amount > 0 && $total['brutto'] < $min_order_amount) {
			$price = new PhocaCartPrice();
			$price->setCurrency($currency->id);
			$priceFb = $price->getPriceFormat($total['brutto']);
			$priceFm = $price->getPriceFormat($min_order_amount);
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_NOT_MET_UPDATE_CART_BEFORE_ORDERING');
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_IS') . ': '. $priceFm;
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_YOUR_ORDER_AMOUNT_IS') . ': '. $priceFb;
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		}
		
		
		// --------------------
		// CHECK STOCK VALIDITY
		// --------------------
		$stockValid		= $cart->getStockValid();
		if($stock_checking == 1 && $stock_checkout == 1 && $stockValid == 0) {
			$msg = JText::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		}
		
		// --------------------
		// CHECK MIN QUANTITY
		// --------------------
		$minQuantityValid		= $cart->getMinimumQuantityValid();
		if($minQuantityValid == 0) {
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		}
		
		
		$db 		= JFactory::getDBO();
		
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_phocacart/tables');
		
		// ORDER
		$d = array();
		if ($guest) {
			$d['user_id'] 				= 0;
		} else {
			$d['user_id'] 				= (int)$user->id;
		}
		$d['status_id']				= 1;// Ordered (Pending)
		$d['published']				= 1;
		$d['shipping_id']			= (int)$shippingId;
		$d['payment_id']			= (int)$payment['id'];
		$d['coupon_id']				= (int)$coupon['id'];
		$d['currency_id']			= (int)$currency->id;
		$d['currency_code']			= $currency->code;
		$d['currency_exchange_rate']= $currency->exchange_rate;	
		$d['comment'] 				= $comment;
		$d['ip']					= (!empty($_SERVER['REMOTE_ADDR'])) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		$user_agent 				= (!empty($_SERVER['HTTP_USER_AGENT'])) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
		$d['user_agent']			= substr($user_agent, 0, 200);
		$d['order_token']			= PhocaCartUtils::getToken();
		$d['tax_calculation']		= $pC->get( 'tax_calculation', 0 );
		$d['unit_weight']			= $unit_weight;
		$d['unit_volume']			= $unit_volume;
		
		// --------------------
		// CHECK PAYMENT AND SHIPPING - TEST IF THE ORDER HAS RIGHT SHIPPING AND PAYMENT METHOD
		// --------------------
		$shippingClass					= new PhocaCartShipping();
		$paymentClass					= new PhocaCartPayment();
		if ($guest) {
			$address = PhocaCartGuestUser::getUserAddressGuest();
		} else {
			$address = PhocaCartUser::getUserAddress($user->id);
		}
		
		$country = 0;
		if(isset($address[0]->type) && $address[0]->type == 0 && isset($address[0]->country) && (int)$address[0]->country > 0) {
			$country = (int)$address[0]->country;
		}
		$region = 0;
		if(isset($address[0]->type) && $address[0]->type == 0 && isset($address[0]->region) && (int)$address[0]->region > 0) {
			$region = (int)$address[0]->region;
		}

		$shippingMethods	= $shippingClass->getPossibleShippingMethods($total['netto'], $total['brutto'], $country, $region, $total['weight'], $shippingId);
		
		$shippingNotUsed = PhocaCartShipping::isShippingNotUsed();
		
		if (empty($shippingMethods) && !$shippingNotUsed) {
			
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_SHIPPING_METHOD');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		}
		
		$paymentMethods	= $paymentClass->getPossiblePaymentMethods($total['netto'], $total['brutto'], $country, $region, $shippingId, $payment['id']);
		$paymentNotUsed = PhocaCartPayment::isPaymentNotUsed();
		if (empty($paymentMethods) && !$paymentNotUsed) {
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_PAYMENT_METHOD');
			$app->enqueueMessage($msg.$msgSuffix, 'error');
			$app->redirect($action);
			exit;
		
		}

	
		
		$row = JTable::getInstance('PhocaCartOrder', 'Table', array());

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		$row->date 		= gmdate('Y-m-d H:i:s');
		$row->modified	= $row->date;
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}

			
		// GET ID OF ORDER
		if ($row->id > 0) {
		
			// ADDRESS;
			//$address = PhocaCartUser::getUserAddress($user->id); - set above
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
			
			if (!empty($items)) {
				$this->cleanTable('phocacart_order_products', $row->id);
				$this->cleanTable('phocacart_order_attributes', $row->id);
				foreach($items as $k => $v) {
					// While saving:
					// Check if attributes which are required were filled
					// Check if products can be accessed (include their categories)
					
					$checkPA = $this->saveOrderProducts($v, $row->id);
					if (!$checkPA) {
						
						// DELETE NEWLY CREATED ORDER WHEN FAIL (not accessible product, required option)
						$this->deleteOrder($row->id);
						$this->cleanTable('phocacart_order_products', $row->id);
						$this->cleanTable('phocacart_order_attributes', $row->id);
						$this->cleanTable('phocacart_order_users', $row->id);
						
						$app->enqueueMessage(JText::_('COM_PHOCACART_ORDER_NOT_EXECUTED_PRODUCT_NOT_ACCESSIBLE_OR_REQUIRED_ATTRIBUTE_OPTION_NOT_SELECTED').$msgSuffix, 'error');
						$app->redirect($action);
						exit;
					
					}
				}
			}
			
			
			
			// COUPONS
			if (!empty($coupon)) {
				$this->cleanTable('phocacart_order_coupons', $row->id);
				$this->saveOrderCoupons($coupon, $total, $row->id);
			}
			
			// HISTORY AND ORDER STATUS
			$this->cleanTable('phocacart_order_history', $row->id);
			$notify = 0;
			$status = PhocaCartOrderStatus::getStatus($d['status_id']);
			if (isset($status['email_customer'])) {
				$notify = $status['email_customer'];
			}
			$this->saveOrderHistory($d['status_id'], $notify, $user->id, $row->id);
			
			// BE AWARE***********
			// $d is newly defined so use d2
			// *******************
			
			// TOTAL
			if (!empty($total)) {
				$this->cleanTable('phocacart_order_total', $row->id);
				
				$ordering 		= 1;
				$d2				= array();
				$d2['order_id']	= $row->id;
				if (isset($total['netto'])) {
				
					// Discount
					$discount = 0;
					if (isset($total['cnetto'])) {
						$discount = $total['cnetto'];
					}
				
					$d2['title']		= JText::_('COM_PHOCACART_SUBTOTAL');
					$d2['type']		= 'netto';
					$d2['amount']	= $total['netto'];
					$d2['ordering']	= $ordering;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				if (isset($total['cnetto'])) {
					$d2['title']		= JText::_('COM_PHOCACART_COUPON');
					//$d2['title']		= JText::_('COM_PHOCACART_COUPON_EXCL_TAX');
					
					if (isset($coupon['title']) && $coupon['title'] != '') {
						$d2['title'] = $coupon['title'];// . ' ' . JText::_('COM_PHOCACART_EXCL_TAX_SUFFIX');
					}
					
					$d2['type']		= 'cnetto';
					$d2['amount']	= '-' . $total['cnetto'];
					$d2['ordering']	= $ordering;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				if (isset($total['cbrutto'])) {
					$d2['title']		= JText::_('COM_PHOCACART_COUPON');
					//$d2['title']		= JText::_('COM_PHOCACART_COUPON_INCL_TAX');
					if (isset($coupon['title']) && $coupon['title'] != '') {
						$d2['title'] = $coupon['title'];// . ' ' . JText::_('COM_PHOCACART_INCL_TAX_SUFFIX');
					}
					$d2['type']		= 'cbrutto';
					$d2['amount']	= '-' . $total['cbrutto'];
					$d2['ordering']	= $ordering;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				if (!empty($total['tax'])) {
					foreach($total['tax'] as $k => $v) {
					
						// Discount
						$discount = 0;
						if (isset($total['ctax'][$k]['tax'])) {
							$discount = $total['ctax'][$k]['tax'];
						}
					
						if ($v['tax'] > 0) {
							$d2['title']		= $v['title'];
							$d2['type']		= 'tax';
							$d2['amount']	= $v['tax'] - $discount;
							$d2['ordering']	= $ordering;
							$this->saveOrderTotal($d2);
							$ordering++;
						}
					}
				}
				if (!empty($shippingC)) {
					if (isset($shippingC['nettotxt']) && isset($shippingC['netto'])) {
						$d2['title']		= $shippingC['nettotxt'];
						$d2['type']		= 'snetto';
						$d2['amount']	= $shippingC['netto'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['taxtxt']) && isset($shippingC['tax'])) {
						$d2['title']		= $shippingC['taxtxt'];
						$d2['type']		= 'stax';
						$d2['amount']	= $shippingC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['bruttotxt']) && isset($shippingC['brutto'])) {
						$d2['title']		= $shippingC['bruttotxt'];
						$d2['type']		= 'sbrutto';
						$d2['amount']	= $shippingC['brutto'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
				}
				
				if (!empty($paymentC)) {
					if (isset($paymentC['nettotxt']) && isset($paymentC['netto'])) {
						$d2['title']		= $paymentC['nettotxt'];
						$d2['type']		= 'pnetto';
						$d2['amount']	= $paymentC['netto'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['taxtxt']) && isset($paymentC['tax'])) {
						$d2['title']		= $paymentC['taxtxt'];
						$d2['type']		= 'ptax';
						$d2['amount']	= $paymentC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['bruttotxt']) && isset($paymentC['brutto'])) {
						$d2['title']		= $paymentC['bruttotxt'];
						$d2['type']		= 'pbrutto';
						$d2['amount']	= $paymentC['brutto'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
				}
				
				if (isset($total['brutto'])) {
					
					// Discount
					$discount = 0;
					if (isset($total['cbrutto'])) {
						$discount = $total['cbrutto'];
					}
					
					$d2['title']		= JText::_('COM_PHOCACART_TOTAL');
					$d2['type']		= 'brutto';
					$d2['amount']	= $total['brutto'] - $discount;
					$d2['ordering']	= $ordering;
				
					$this->saveOrderTotal($d2);
					$ordering++;
				}
			}
			
			
			$statusId = 1;// Pending PHSTATUS                       
			PhocaCartOrderStatus::changeStatus($row->id, $statusId, $d['order_token']);// Notify user, notify others, emails send - will be decided in function
			
			// Proceed or not proceed to payment gateway - depends on payment method
			// By every new order - clean the proceed payment session
			$session 		= JFactory::getSession();
			$session->set('proceedpayment', array(), 'phocaCart');
			
			$proceed = PhocaCartPayment::proceedToPaymentGateway($payment);
			
			if ($proceed) {

				$proceedPayment['orderid'] = $row->id;
				$session->set('proceedpayment', $proceedPayment, 'phocaCart');
			
				if ($this->downloadable_product == 1) {
					$this->action_after_order	= 4; // PAYMENT/DOWNLOAD
				} else {
					$this->action_after_order	= 3;// PAYMENT/NO DOWNLOAD
				}
				
			} else {
			
				if ($this->downloadable_product == 1) {
					$this->action_after_order	= 2; // ORDER/DOWNLOAD
				} else {
					$this->action_after_order	= 1;// ORDER/NO DOWNLOAD
				}
			
			}
			
			return true;
			
		} else {
			return false;
		}
		
		return true;
	}
	
	public function saveOrderUsers($d, $orderId) {
	
	
		$d = (array)$d;
		if (!isset($d['id'])){
			$d['id'] = 0;// Guest Checkout
		}
		$d['order_id'] 			= (int)$orderId;
		$d['user_address_id']	= (int)$d['id'];
		$d['user_token']		= PhocaCartUtils::getToken();
		unset($d['id']);// we do new autoincrement
		$row = JTable::getInstance('PhocaCartOrderUsers', 'Table', array());
		
		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
		return true;
	}
	
	
	public function saveOrderProducts($d, $orderId) {
		$row = JTable::getInstance('PhocaCartOrderProducts', 'Table', array());
		
		$checkP = PhocaCartProduct::checkIfAccessPossible($d['id'], $d['catid']);
		if (!$checkP) {
			return false;
		}
		
		
		$d['status_id']			= 1;// pending
		$d['published']			= 1;
		$d['order_id'] 			= (int)$orderId;
		$d['product_id']		= (int)$d['id'];
		$d['category_id']		= (int)$d['catid'];
		$d['product_id_key']	= $d['idkey'];
		unset($d['id']);// we do new autoincrement
		
		$d['tax'] = $d['tax']/$d['quantity'];// in database we store the items per item
		$d['dtax'] = $d['dtax']/$d['quantity'];// in database we store the items per item
		
		
		// STOCK HANDLING
		// will be set in order status
		//$stock = PhocaCartStock::handleStockProduct($d['product_id'], $d['status_id'], $d['quantity'] );
		

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		

		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
		
	
		
		if ($row->id > 0 && !empty($d['attributes'])) {
			//$this->cleanTable('phocacart_order_attributes', $orderId); NOT HERE, we are in foreach
			
			foreach ($d['attributes'] as $k => $v) {
				
				$checkA = PhocaCartAttribute::checkIfRequired($v['aid'], $v['oid']);
				if (!$checkA) {
					return false;
				}
				
				$row2 = JTable::getInstance('PhocaCartOrderAttributes', 'Table', array());
				$d2 = array();
				$d2['order_id'] 		= (int)$orderId;
				$d2['product_id'] 		= (int)$d['product_id'];
				$d2['order_product_id']	= (int)$row->id;
				$d2['attribute_id']		= (int)$v['aid'];
				$d2['option_id']		= (int)$v['oid'];
				$d2['attribute_title']	= $v['atitle'];
				$d2['option_title']		= $v['otitle'];
				
				// Will be set order status
				//$stockA = PhocaCartStock::handleStockAttributeOption($d2['option_id'], $d['status_id'], $d['quantity'] );
			
				if (!$row2->bind($d2)) {
					throw new Exception($db->getErrorMsg());
					return false;
				}
				
				if (!$row2->check()) {
					throw new Exception($row2->getError());
					return false;
				}
				
				
				if (!$row2->store()) {
					throw new Exception($row2->getError());
					return false;
				}
			}
		} else if ($row->id > 0){
			// Empty attributes - check if product include some required attribute
			$checkA = PhocaCartAttribute::checkIfExistsAndRequired($d['product_id']);
			if (!$checkA) {
				return false;
			}
		}
		
		
		
		if ($row->id > 0) {
			// DOWNLOAD - we are here because we need Product ID and Order Product ID - both are different ids
			$this->saveOrderDownloads($row->id, $d['product_id'], $d['order_id']);
		}
		
		if ($row->id > 0) {
			// UPDATE the number of sales of one product - to save sql queries in frontend
			$this->updateNumberOfSalesOfProduct($row->id, $d['product_id'], $d['order_id']);
		}

		return true;
	
	}
	
	public function saveOrderCoupons($coupon, $total, $orderId) {
	
		$d = array();
		$d['order_id'] 			= (int)$orderId;
		$d['coupon_id']			= (int)$coupon['id'];
		$d['title']				= $coupon['title'];
		if (isset($coupon['code'])) {
			$d['code']			= $coupon['code'];
		}
		$d['amount']			= $total['cnetto'];// get the value from total
		$d['netto']				= $total['cnetto'];
		$d['brutto']			= $total['cbrutto'];
		$row = JTable::getInstance('PhocaCartOrderCoupons', 'Table', array());	
		
		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
		return true;
	}
	
	public function saveOrderTotal($d) {

		$row = JTable::getInstance('PhocaCartOrderTotal', 'Table', array());
		
		$d['published']				= 1;
		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
	
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}

		return true;
	}
	
	public function saveOrderHistory($statusId, $notify, $userId, $orderId) {

		$row = JTable::getInstance('PhocaCartOrderHistory', 'Table', array());
		
		$d 						= array();
		$d['order_status_id']	= (int)$statusId;
		$d['notify']			= (int)$notify;
		$d['user_id']			= (int)$userId;
		$d['order_id']			= (int)$orderId;
		$d['date']				= gmdate('Y-m-d H:i:s');;
		
		
		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
		return true;
	}
	
	public function saveOrderDownloads($orderProductId, $productId, $orderId) {

		$row = JTable::getInstance('PhocaCartOrderDownloads', 'Table', array());
		
		
		$productItem 	= new PhocaCartProduct();
		$product		= $productItem->getProduct((int)$productId);
		if (!isset($product->download_file) || (isset($product->download_file) && $product->download_file == '' )) {
			return true;// No defined file, no item in download order
		}
		
		$d 						= array();
		$d['order_id']			= (int)$orderId;
		$d['product_id']		= (int)$productId;
		$d['order_product_id']	= (int)$orderProductId;
		$d['title']				= $product->title;
		$d['download_token']	= $product->download_token;
		$d['download_folder']	= $product->download_folder;
		$d['download_file']		= $product->download_file;
		$d['download_hits']		= 0;
		$d['published']			= 0;
		$d['date']				= gmdate('Y-m-d H:i:s');
		
		$d['ordering']			= 0;
		$db = JFactory::getDbo();
		//$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_order_downloads WHERE catid = '.(int)$orderId);
		$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_order_downloads');
		$max = $db->loadResult();
		$d['ordering'] = $max+1;
		
		
		
		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
		
		$this->downloadable_product = 1;
		return true;
	}
	
	
	public function updateNumberOfSalesOfProduct($orderProductId, $productId, $orderId) {

		// We store the number of sales of one product directly to product table
		// because of saving SQL queries in frontend, to not run sql query for each product
		$db = JFactory::getDBO();
		$query = ' SELECT sum(quantity) FROM #__phocacart_order_products'
			    .' WHERE product_id = '.(int) $productId
				.' LIMIT 0,1';
		$db->setQuery($query);
		$sum = $db->loadColumn();
		if (isset($sum[0])){
			$query = ' UPDATE #__phocacart_products'
			    .' SET sales = '.(int)$sum[0]
				.' WHERE id = '.(int)$productId;
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
	
	
	
	
	private function cleanTable ($table, $orderId) {
		if ($table != '') {
			$db 	= JFactory::getDBO();
			$query 	= ' DELETE FROM #__'.$table.' WHERE order_id = '. (int)$orderId;
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		return false;
	}
	
	private function deleteOrder ($orderId) {
		if ($table != '') {
			$db 	= JFactory::getDBO();
			$query 	= ' DELETE FROM #__phocacart_orders WHERE id = '. (int)$orderId;
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		return false;
	}
	
	/* Static part */
	
	public static function getOrderStatus($statusId) {
	
		$db 	= JFactory::getDBO();
		$query 	= ' SELECT a.title FROM #__phocacart_order_statuses WHERE id = '. (int)$statusId . ' ORDER BY a.title';
		$db->setQuery($query);
		$status = $db->loadAssoc();
	}
	
	public static function getOrderNumber($orderId) {
		return str_pad($orderId, '10', '0', STR_PAD_LEFT);
	}
	
	public static function getInvoiceNumber($orderId, $invoicePrefix) {
		$l = strlen($invoicePrefix);
		$n = 10 - $l;
		if ($n < 0) {$n = 0;}
		return $invoicePrefix . str_pad($orderId, $n, '0', STR_PAD_LEFT);
	
	}
}