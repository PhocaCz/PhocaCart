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

class PhocacartOrder
{
	public $downloadable_product;
	public $action_after_order;

	public function __construct() {
		$this->downloadable_product = 0;// if there will be at least one downloadable file in order, we will mark it to display 
										// right thank you message
		$this->action_after_order	= 1;// which action will be done after order - end, procceed to payment, ...
		
	}
	public function saveOrderMain($comment) {
		
		$msgSuffix			= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		$pC 				= PhocacartUtils::getComponentParameters();
		$min_order_amount	= $pC->get( 'min_order_amount', 0 );
		$stock_checkout		= $pC->get( 'stock_checkout', 0 );
		$stock_checking		= $pC->get( 'stock_checking', 0 );
		$unit_weight		= $pC->get( 'unit_weight', '' );
		$unit_volume		= $pC->get( 'unit_volume', '' );
		
		$uri 			= JFactory::getURI();
		$action			= $uri->toString();
		$app			= JFactory::getApplication();
		
		$user			= JFactory::getUser();
		$guest			= PhocacartUserGuestuser::getGuestUser();
		$cart			= new PhocacartCartRendercheckout();
		$cart->setFullItems();
		$fullItems 		= $cart->getFullItems();
		$currency		= PhocacartCurrency::getCurrency();
		
		if (empty($fullItems[0])) {
			$msg = JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY');
			$app->enqueueMessage($msg, 'error');
			return false;
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
			$couponO = new PhocacartCoupon();
			$couponO->setCoupon((int)$couponCart['id']);
			$coupon = $couponO->getCoupon();
		}
		if (!$coupon) {
			$coupon = $couponCart;
		}
		
		
		$cart->roundTotalAmount();
		
		$total		= $cart->getTotal();
		
		// --------------------
		// CHECK CAPTCHA
		// --------------------
		$enable_captcha_checkout	= PhocacartCaptcha::enableCaptchaCheckout();
		if ($enable_captcha_checkout) {
			if (!PhocacartCaptchaRecaptcha::isValid()) {
				$msg =JText::_('COM_PHOCACART_WRONG_CAPTCHA');
				$app->enqueueMessage($msg, 'error');
				return false;	
				// What happens when the CAPTCHA was entered incorrectly
				//$info = array();
				//$info['field'] = 'question_captcha';
				//return new JException(JText::_('COM_PHOCACART_WRONG_CAPTCHA' ), "105", E_USER_ERROR, $info, false);
			}
		}
		
		// --------------------
		// CHECK MINIMUM ORDER AMOUNT
		// --------------------
		if($min_order_amount > 0 && $total[0]['brutto'] < $min_order_amount) {
			$price = new PhocacartPrice();
			$price->setCurrency($currency->id);
			$priceFb = $price->getPriceFormat($total[0]['brutto']);
			$priceFm = $price->getPriceFormat($min_order_amount);
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_NOT_MET_UPDATE_CART_BEFORE_ORDERING');
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_IS') . ': '. $priceFm;
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_YOUR_ORDER_AMOUNT_IS') . ': '. $priceFb;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		// --------------------
		// CHECK STOCK VALIDITY
		// --------------------
		$stockValid		= $cart->getStockValid();
		if($stock_checking == 1 && $stock_checkout == 1 && $stockValid == 0) {
			$msg = JText::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING');
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		// --------------------
		// CHECK MIN QUANTITY
		// --------------------
		$minQuantityValid		= $cart->getMinimumQuantityValid();
		if($minQuantityValid == 0) {
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING');
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		// --------------------
		// CHECK MIN MULTIPLE QUANTITY
		// --------------------
		$minMultipleQuantityValid		= $cart->getMinimumMultipleQuantityValid();
		if($minMultipleQuantityValid == 0) {
			$msg = JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING');
			$app->enqueueMessage($msg, 'error');
			return false;
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
		$d['order_token']			= PhocacartUtils::getToken();
		$d['tax_calculation']		= $pC->get( 'tax_calculation', 0 );
		$d['unit_weight']			= $unit_weight;
		$d['unit_volume']			= $unit_volume;
		$d['discount_id']			= $cart->getCartDiscountId();
		
		// --------------------
		// CHECK PAYMENT AND SHIPPING - TEST IF THE ORDER HAS RIGHT SHIPPING AND PAYMENT METHOD
		// --------------------
		$shippingClass					= new PhocacartShipping();
		$paymentClass					= new PhocacartPayment();
		if ($guest) {
			$address = PhocacartUserGuestuser::getUserAddressGuest();
		} else {
			$address = PhocacartUser::getUserAddress($user->id);
		}
		
		$country = 0;
		if(isset($address[0]->type) && $address[0]->type == 0 && isset($address[0]->country) && (int)$address[0]->country > 0) {
			$country = (int)$address[0]->country;
		}
		$region = 0;
		if(isset($address[0]->type) && $address[0]->type == 0 && isset($address[0]->region) && (int)$address[0]->region > 0) {
			$region = (int)$address[0]->region;
		}

		$shippingMethods	= $shippingClass->getPossibleShippingMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $total[0]['weight'], $total[0]['max_length'], $total[0]['max_width'], $total[0]['max_height'], $shippingId);
		
		$shippingNotUsed = PhocacartShipping::isShippingNotUsed();
		
		if (empty($shippingMethods) && !$shippingNotUsed) {
			
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_SHIPPING_METHOD');
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		$paymentMethods	= $paymentClass->getPossiblePaymentMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $shippingId, $payment['id']);
		$paymentNotUsed = PhocacartPayment::isPaymentNotUsed();
		if (empty($paymentMethods) && !$paymentNotUsed) {
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_PAYMENT_METHOD');
			$app->enqueueMessage($msg, 'error');
			return false;
		
		}


		
		$row = JTable::getInstance('PhocacartOrder', 'Table', array());

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
				
				foreach($fullItems[1] as $k => $v) {
					
					
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
						$this->saveOrderDownloads($orderProductId, $v['id'], $row->id);
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
						
						$msg = JText::_('COM_PHOCACART_ORDER_NOT_EXECUTED_PRODUCT_NOT_ACCESSIBLE_OR_REQUIRED_ATTRIBUTE_OPTION_NOT_SELECTED');
						$app->enqueueMessage($msg, 'error');
						return false;
					
					}
				}
			}
			
			
			// DISCOUNTS
			$this->cleanTable('phocacart_order_discounts', $row->id);
			if ($total[2]['dnetto'] > 0) {
				$this->saveOrderDiscounts(JText::_('COM_PHOCACART_PRODUCT_DISCOUNT'), $total[2], $row->id);
			}
			if ($total[3]['dnetto'] > 0) {
				$this->saveOrderDiscounts(JText::_('COM_PHOCACART_CART_DISCOUNT'), $total[3], $row->id);
			}
			
			// COUPONS
			if (!empty($coupon)) {
				$this->cleanTable('phocacart_order_coupons', $row->id);
				$this->saveOrderCoupons($coupon, $total[4], $row->id);
			}
			
			
			// REWARD
			$this->cleanTable('phocacart_reward_points', $row->id);
			
			// REWARD DISCOUNT - user used the points to buy items
			if ($user->id > 0 && isset($total[0]['rewardproductusedtotal']) && (int)$total[0]['rewardproductusedtotal'] > 0) {
				$rewardProductTotal = -(int)$total[0]['rewardproductusedtotal'];
				$this->saveRewardPoints($user->id, $rewardProductTotal, $row->id, 0, -1);
			}
			// REWARD POINTS + user get the points when buying items
			if ($user->id > 0 && isset($total[0]['points_received']) && (int)$total[0]['points_received'] > 0) {
				$this->saveRewardPoints($user->id, (int)$total[0]['points_received'], $row->id, 0, 1);
			}
			
			
			// HISTORY AND ORDER STATUS
			$this->cleanTable('phocacart_order_history', $row->id);
			$notify = 0;
			$status = PhocacartOrderStatus::getStatus($d['status_id']);
			if (isset($status['email_customer'])) {
				$notify = $status['email_customer'];
			}
			$this->saveOrderHistory($d['status_id'], $notify, $user->id, $row->id);
			
			// BE AWARE***********
			// $d is newly defined so use d2
			// *******************
			
			
			// TOTAL
			if (!empty($total[0])) {
				$this->cleanTable('phocacart_order_total', $row->id);
				
				$ordering 				= 1;
				$d2						= array();
				$d2['order_id']			= $row->id;
				$d2['amount_currency']	= 0;

				
				if (isset($total[1]['netto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_SUBTOTAL');
					$d2['type']		= 'netto';
					$d2['amount']	= $total[1]['netto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				// Reward Discount
				if (isset($total[5]['dnetto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_REWARD_DISCOUNT');
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[5]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[5]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_REWARD_DISCOUNT');
					$d2['type']		= 'dbrutto';
					$d2['amount']	= '-'.$total[5]['dbrutto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 0;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				// Product Discount
				if (isset($total[2]['dnetto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_PRODUCT_DISCOUNT');
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[2]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[2]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_PRODUCT_DISCOUNT');
					$d2['type']		= 'dbrutto';
					$d2['amount']	= '-'.$total[2]['dbrutto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 0;
					$d2['item_id']	= 0;
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
					$d2['title']	= JText::_('COM_PHOCACART_CART_DISCOUNT');
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[3]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[3]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_CART_DISCOUNT');
					$d2['type']		= 'dbrutto';
					$d2['amount']	= '-'.$total[3]['dbrutto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 0;
					$d2['item_id']	= 0;
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
					$d2['title']	= JText::_('COM_PHOCACART_COUPON');
					if (isset($coupon['title']) && $coupon['title'] != '') {
						$d2['title'] = $coupon['title'];
					}
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[4]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[4]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_COUPON');
					if (isset($coupon['title']) && $coupon['title'] != '') {
						$d2['title'] = $coupon['title'];
					}
					$d2['type']		= 'dbrutto';
					$d2['amount']	= '-'.$total[4]['dbrutto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 0;
					$d2['item_id']	= 0;
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
					foreach($total[0]['tax'] as $k => $v) {
						if ($v['tax'] > 0) {
							$d2['title']	= $v['title'];
							$d2['type']		= 'tax';
							$d2['amount']	= $v['tax'];
							$d2['ordering']	= $ordering;
							$d2['published']= 1;
							$d2['item_id']	= 0;
							$this->saveOrderTotal($d2);
							$ordering++;
						}
					}
				}
				
				
				
				$d2['published']= 1;
				
				// Shipping
			
				if (!empty($shippingC)) {
					if (isset($shippingC['nettotxt']) && isset($shippingC['netto'])) {
						$d2['title']	= $shippingC['nettotxt'];
						$d2['type']		= 'snetto';
						$d2['amount']	= $shippingC['netto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['taxtxt']) && isset($shippingC['tax']) && $shippingC['tax'] > 0) {
						$d2['title']	= $shippingC['taxtxt'];
						$d2['type']		= 'stax';
						$d2['item_id']	= (int)$shippingC['taxid'];
						$d2['amount']	= $shippingC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['bruttotxt']) && isset($shippingC['brutto'])) {
						$d2['title']	= $shippingC['bruttotxt'];
						$d2['type']		= 'sbrutto';
						$d2['amount']	= $shippingC['brutto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
				}
				
				// Payment
				if (!empty($paymentC)) {
					if (isset($paymentC['nettotxt']) && isset($paymentC['netto'])) {
						$d2['title']	= $paymentC['nettotxt'];
						$d2['type']		= 'pnetto';
						$d2['amount']	= $paymentC['netto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['taxtxt']) && isset($paymentC['tax']) && $paymentC['tax']) {
						$d2['title']	= $paymentC['taxtxt'];
						$d2['type']		= 'ptax';
						$d2['item_id']	= (int)$paymentC['taxid'];
						$d2['amount']	= $paymentC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['bruttotxt']) && isset($paymentC['brutto'])) {
						$d2['title']	= $paymentC['bruttotxt'];
						$d2['type']		= 'pbrutto';
						$d2['amount']	= $paymentC['brutto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
				}
				
				
				// Rounding
				if (isset($total[0]['rounding'])) {
					$d2['title']			= JText::_('COM_PHOCACART_ROUNDING');
					$d2['type']				= 'rounding';
					$d2['amount']			= $total[0]['rounding'];
					$d2['amount_currency']	= $total[0]['rounding_currency'];
					$d2['ordering']			= $ordering;
					$d2['item_id']			= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				
				
				if (isset($total[0]['brutto'])) {
					$d2['title']			= JText::_('COM_PHOCACART_TOTAL');
					$d2['type']				= 'brutto';
					$d2['amount']			= $total[0]['brutto'];
					$d2['amount_currency']	= $total[0]['brutto_currency'];
					$d2['ordering']			= $ordering;
					$d2['item_id']			= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
			}
			
			// CHANGE STATUS
			// STOCK MOVEMENT (including a) Main Product, b) Product Variations method)
			$statusId = 1;// Pending PHSTATUS                       
			PhocacartOrderStatus::changeStatus($row->id, $statusId, $d['order_token']);// Notify user, notify others, emails send - will be decided in function
			
			// Proceed or not proceed to payment gateway - depends on payment method
			// By every new order - clean the proceed payment session
			$session 		= JFactory::getSession();
			$session->set('proceedpayment', array(), 'phocaCart');
			
			$proceed = PhocacartPayment::proceedToPaymentGateway($payment);
			
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
		$d['user_token']		= PhocacartUtils::getToken();
		$userGroups				= PhocacartGroup::getGroupsById((int)$d['user_id'], 1, 1);
		$d['user_groups']		= serialize($userGroups);
		
		unset($d['id']);// we do new autoincrement
		$row = JTable::getInstance('PhocacartOrderUsers', 'Table', array());
		
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
		$row = JTable::getInstance('PhocacartOrderProducts', 'Table', array());
		
		$checkP = PhocacartProduct::checkIfAccessPossible($d['id'], $d['catid']);
		
		if (!$checkP) {
			return false;
		}
		
		
		// Additional info
		$d['default_price'] 				= $d['default_price'];
		$d['default_tax_rate'] 				= $d['taxrate'];
		$d['default_tax_id'] 				= $d['taxid'];
		$d['default_tax_calculation_rate'] 	= $d['taxtcalctype'];
		$d['default_points_received'] 		= $d['default_points_received'];
	
		
		$d['status_id']			= 1;// pending
		$d['published']			= 1;
		$d['order_id'] 			= (int)$orderId;
		$d['product_id']		= (int)$d['id'];
		$d['category_id']		= (int)$d['catid'];
		$d['product_id_key']	= $d['idkey'];
		$d['stock_calculation']	= $d['stockcalculation'];
		unset($d['id']);// we do new autoincrement
		
		$d['tax'] = $d['tax']/$d['quantity'];// in database we store the items per item
		//$d['dtax'] = $d['dtax']/$d['quantity'];// in database we store the items per item
		
		
		// STOCK HANDLING
		// will be set in order status administrator\components\com_phocacart\libraries\phocacart\order\status.php
		//$stock = PhocacartStock::handleStockProduct($d['product_id'], $d['status_id'], $d['quantity'] );
		

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

						$row2 = JTable::getInstance('PhocacartOrderAttributes', 'Table', array());
						$d2 = array();
						$d2['order_id'] 		= (int)$orderId;
						$d2['product_id'] 		= (int)$d['product_id'];
						$d2['order_product_id']	= (int)$row->id;
						$d2['attribute_id']		= (int)$v2['aid'];
						$d2['option_id']		= (int)$v2['oid'];
						$d2['attribute_title']	= $v2['atitle'];
						$d2['option_title']		= $v2['otitle'];
						
						// Will be set order status
						// administrator\components\com_phocacart\libraries\phocacart\order\status.php
						// $stockA = PhocacartStock::handleStockAttributeOption($d2['option_id'], $d['status_id'], $d['quantity'] );
					
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
	
		$d = array();
		$d['order_id'] 			= (int)$orderId;
		$d['coupon_id']			= (int)$coupon['id'];
		$d['title']				= $coupon['title'];
		if (isset($coupon['code'])) {
			$d['code']			= $coupon['code'];
		}
		$d['amount']			= $totalC['dnetto'];// get the value from total
		$d['netto']				= $totalC['dnetto'];
		$d['brutto']			= $totalC['dbrutto'];
		$row = JTable::getInstance('PhocacartOrderCoupons', 'Table', array());	
		
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
	
	
	public function saveOrderDiscounts($discountTitle, $totalD, $orderId, $type = 0) {
	
		$d = array();
		$d['order_id'] 			= (int)$orderId;
		//$d['discount_id']		= (int)$discount['id'];
		$d['title']				= $discountTitle;
		$d['amount']			= $totalD['dnetto'];// get the value from total
		$d['netto']				= $totalD['dnetto'];
		$d['brutto']			= $totalD['dbrutto'];
		$row = JTable::getInstance('PhocacartOrderDiscounts', 'Table', array());	
		
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

		$row = JTable::getInstance('PhocacartOrderTotal', 'Table', array());
		
		//$d['published']				= 1;
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

		$row = JTable::getInstance('PhocacartOrderHistory', 'Table', array());
		
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

		$row = JTable::getInstance('PhocacartOrderDownloads', 'Table', array());
		
		
		$productItem 	= new PhocacartProduct();
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
	
	
	public function saveOrderProductDiscounts($orderProductId, $productId, $orderId, $k, $fullItems) {

		$db = JFactory::getDBO();
		
		if (isset($fullItems[2][$k]['discountproduct']) && $fullItems[2][$k]['discountproduct'] == 1) {
			
			//$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
			$query 	= ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = '. (int)$orderId . ' AND order_product_id = '.(int)$orderProductId. ' AND product_id = '.(int)$productId . ' AND type = 2';
			$db->setQuery($query);
			$db->execute();
			
			$amount 	= $fullItems[2][$k]['netto'];
			$netto 		= $fullItems[2][$k]['netto'];
			$brutto 	= $fullItems[2][$k]['brutto'];
			$tax 		= $fullItems[2][$k]['tax'];
			$final 		= $fullItems[2][$k]['final'];
			$quantity 	= $fullItems[2][$k]['quantity'];
			$catid 		= $fullItems[3][$k]['catid'];
			$query = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
			.' VALUES ('.(int)$orderId.', '.(int)$productId.', '.(int)$orderProductId.', '.$db->quote($k).', '.(int)$catid.', 2, '.$db->quote($fullItems[2][$k]['discountproducttitle']).', '.$amount.', '.$netto.', '.$brutto.', '.$tax.', '.$final.', '.(int)$quantity.', 0)';
			$db->setQuery($query);
			$db->execute();
		}
		
		if (isset($fullItems[3][$k]['discountcart']) && $fullItems[3][$k]['discountcart'] == 1) {
			
			//$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
			$query 	= ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = '. (int)$orderId . ' AND order_product_id = '.(int)$orderProductId. ' AND product_id = '.(int)$productId . ' AND type = 3';
			$db->setQuery($query);
			$db->execute();
			
			$amount 	= $fullItems[3][$k]['netto'];
			$netto 		= $fullItems[3][$k]['netto'];
			$brutto 	= $fullItems[3][$k]['brutto'];
			$tax 		= $fullItems[3][$k]['tax'];
			$final 		= $fullItems[3][$k]['final'];
			$quantity 	= $fullItems[3][$k]['quantity'];
			$catid 		= $fullItems[3][$k]['catid'];
			
			$query = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
			.' VALUES ('.(int)$orderId.', '.(int)$productId.', '.(int)$orderProductId.', '.(int)$fullItems[3][$k]['discountcartid'].', '.$db->quote($k).', '.(int)$catid.', 3, '.$db->quote($fullItems[3][$k]['discountcarttitle']).', '.$amount.', '.$netto.', '.$brutto.', '.$tax.', '.$final.', '.(int)$quantity.', 0)';
			$db->setQuery($query);
			$db->execute();
		}
		
		if (isset($fullItems[4][$k]['couponcart']) && $fullItems[4][$k]['couponcart'] == 1) {
			
			//$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
			$query 	= ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = '. (int)$orderId . ' AND order_product_id = '.(int)$orderProductId. ' AND product_id = '.(int)$productId . ' AND type = 4';
			$db->setQuery($query);
			$db->execute();
			
			$amount 	= $fullItems[4][$k]['netto'];
			$netto 		= $fullItems[4][$k]['netto'];
			$brutto 	= $fullItems[4][$k]['brutto'];
			$tax 		= $fullItems[4][$k]['tax'];
			$final 		= $fullItems[4][$k]['final'];
			$quantity 	= $fullItems[4][$k]['quantity'];
			$catid 		= $fullItems[4][$k]['catid'];
			$query = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
			.' VALUES ('.(int)$orderId.', '.(int)$productId.', '.(int)$orderProductId.', '.(int)$fullItems[4][$k]['couponcartid'].', '.$db->quote($k).', '.(int)$catid.', 4, '.$db->quote($fullItems[4][$k]['couponcarttitle']).', '.$amount.', '.$netto.', '.$brutto.', '.$tax.', '.$final.', '.(int)$quantity.', 0)';
			$db->setQuery($query);
			$db->execute();
		}
		
		
		return true;
	}
	
	
	public function saveRewardPoints($userId, $points, $orderId, $published = 0, $type = 0) {

		$row = JTable::getInstance('PhocacartRewardPoint', 'Table', array());
		
		$d 						= array();
		$d['date']				= gmdate('Y-m-d H:i:s');
		$d['published']			= (int)$published;
		$d['title']				= JText::_('COM_PHOCACART_ORDER_NUMBER') . ' '. self::getOrderNumber($orderId) . ' ('.$d['date'].')';
		$d['points']			= (int)$points;
		$d['user_id']			= (int)$userId;
		$d['order_id']			= (int)$orderId;
		$d['type']				= (int)$type;
		
		
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
	
	
	public function updateNumberOfSalesOfProduct($orderProductId, $productId, $orderId) {

		// We store the number of sales of one product directly to product table
		// because of saving SQL queries in frontend, to not run sql query for each product
		$db = JFactory::getDBO();
		$query = ' SELECT SUM(quantity) FROM #__phocacart_order_products'
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
	
	public static function getInvoiceNumber($orderId, $orderDate, $invoicePrefix = '', $invoiceNumberFormat = '', $invoiceNumberChars = 12) {
		
		$orderDate = date("Ymd", strtotime($orderDate));
		
		$iN = $invoiceNumberFormat;
	
		$iN = str_replace('{prefix}', $invoicePrefix, $iN);
		$iN = str_replace('{orderdate}', $orderDate, $iN);

		$pos = strpos($iN, '{orderid}');
		
		if ($pos === false) {
			$l1	= strlen($iN);
			$l2	= 0;
			//$l = $invoiceNumberChars - $l1 - $l2;
			$l = $l1;
			if ($l < 0) {$l = 0;}
			$iN 		= str_pad($iN, $invoiceNumberChars, '0', STR_PAD_RIGHT);
			
		} else {
			$l1			= strlen(str_replace('{orderid}', '', $iN));
			//$l2			= strlen($orderId);
			$l 			= $invoiceNumberChars - $l1;
			
			if ($l < 0) {$l = 0;}
			
			$orderId 	=  str_pad($orderId, $l, '0', STR_PAD_LEFT);
			$iN 		= str_replace('{orderid}', $orderId, $iN);
		}

		return $iN;
	
	}
}