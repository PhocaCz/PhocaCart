<?php
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

class PhocacartOrder
{
	public $downloadable_product;
	public $action_after_order;
	public $message_after_order;
	protected $type = array(0,1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

	public function __construct() {
		$this->downloadable_product = 0;// if there will be at least one downloadable file in order, we will mark it to display 
										// right thank you message
		$this->action_after_order	= 1;// which action will be done after order - end, procceed to payment, ...
		$this->message_after_order	= array();// custom message array made by plugin
		
	}
	
	public function setType($type = array(0,1)) {
		$this->type = $type;
	}
	public function saveOrderMain($data) {
		
		$msgSuffix			= '<span id="ph-msg-ns" class="ph-hidden"></span>';
		$pC 				= PhocacartUtils::getComponentParameters();
		$min_order_amount	= $pC->get( 'min_order_amount', 0 );
		$stock_checkout		= $pC->get( 'stock_checkout', 0 );
		$stock_checking		= $pC->get( 'stock_checking', 0 );
		$unit_weight		= $pC->get( 'unit_weight', '' );
		$unit_volume		= $pC->get( 'unit_volume', '' );
		
		$uri 			= \Joomla\CMS\Uri\Uri::getInstance();
		$action			= $uri->toString();
		$app			= JFactory::getApplication();
		
		$user			= PhocacartUser::getUser();
		$guest			= PhocacartUserGuestuser::getGuestUser();
		$cart			= new PhocacartCartRendercheckout();
		$cart->setInstance(3);//order
		$cart->setType($this->type);
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
		// TERMS AND CONDITIONS, PRIVACY
		// --------------------
		// checked in controller
		
		
		// --------------------
		// CHECK OPENING TIMES
		// --------------------
		
		if (PhocacartTime::checkOpeningTimes() == false) {
			// Message set in checkOpeningTimes() method
			return false;
		}
		// --------------------
		// CHECK GUEST USER
		// --------------------
		
		if ((!isset($user->id) || (isset($user->id) && $user->id < 1)) && $guest == false && !PhocacartPos::isPos()) {
			
			$msg =JText::_('COM_PHOCACART_GUEST_CHECKOUT_DISABLED') . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		// --------------------
		// CHECK CAPTCHA
		// --------------------
		$enable_captcha_checkout	= PhocacartCaptcha::enableCaptchaCheckout();
		if ($enable_captcha_checkout) {
			if (!PhocacartCaptchaRecaptcha::isValid()) {
				$msg =JText::_('COM_PHOCACART_WRONG_CAPTCHA') . $msgSuffix;
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
		if($min_order_amount > 0 && $total[0]['brutto'] < $min_order_amount) {
			$price = new PhocacartPrice();
			$price->setCurrency($currency->id);
			$priceFb = $price->getPriceFormat($total[0]['brutto']);
			$priceFm = $price->getPriceFormat($min_order_amount);
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_NOT_MET_UPDATE_CART_BEFORE_ORDERING');
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_MINIMUM_ORDER_AMOUNT_IS') . ': '. $priceFm;
			$msg .= '<br />';
			$msg .=JText::_('COM_PHOCACART_YOUR_ORDER_AMOUNT_IS') . ': '. $priceFb . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		// --------------------
		// CHECK STOCK VALIDITY
		// --------------------
		$stockValid		= $cart->getStockValid();
		if($stock_checking == 1 && $stock_checkout == 1 && $stockValid == 0) {
			$msg = JText::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		// --------------------
		// CHECK MIN QUANTITY
		// --------------------
		$minQuantityValid		= $cart->getMinimumQuantityValid();
		if($minQuantityValid == 0) {
			$msg = JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		// --------------------
		// CHECK MIN MULTIPLE QUANTITY
		// --------------------
		$minMultipleQuantityValid		= $cart->getMinimumMultipleQuantityValid();
		if($minMultipleQuantityValid == 0) {
			$msg = JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING') . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		// --------------------
		// CHECK IF PRODUCT OR ATTRIBUTES EXIST
		// --------------------
		$productsRemoved = $cart->getProductsRemoved();
		if(!empty($productsRemoved)) {
			// Message is set by cart class
			//$msg = JText::_('') . $msgSuffix;
			//$app->enqueueMessage($msg, 'error');
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
		
		
		// SET STATUS
		$statusId = $pC->get( 'default_order_status', 1 );// Ordered (Pending) as default
		
		// Free Download
		// 1) All products are digital
		// 2) Order is zero price
		if (isset($total[0]['countdigitalproducts']) && isset($total[0]['countallproducts'])
			&& (int)$total[0]['countdigitalproducts'] == $total[0]['countallproducts']
			&& $total[0]['brutto'] == 0 && $total[0]['netto'] == 0 ) {
			$statusId = $pC->get( 'default_order_status_free_download', 1 );// Ordered (Pending) as default	
				
		}
			
		//$dispatcher = J EventDispatcher::getInstance();
		$plugin = JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($payment['method'])));
		if ($plugin) {
			\JFactory::getApplication()->triggerEvent('PCPbeforeSaveOrder', array(&$statusId));
			$d['status_id']				= (int)$statusId;// e.g. by POS Cash we get automatically the status as completed
		} else {
			
			$d['status_id']				= $statusId;// no plugin or no event found
		}
		
	
		$d['type'] 					= PhocacartType::getTypeByTypeArray($this->type);
		
		// Data order
		$d['comment'] 				= isset($data['phcomment']) ? $data['phcomment'] : '';
		$d['privacy']				= isset($data['privacy']) ? (int)$data['privacy'] : '';
		
		// Data POS
		$d['amount_pay'] 			= isset($data['amount_pay']) ? $data['amount_pay'] : 0;
		$d['amount_tendered'] 		= isset($data['amount_tendered']) ? $data['amount_tendered'] : 0;
		$d['amount_change'] 		= isset($data['amount_change']) ? $data['amount_change'] : 0;
		
		$d['published']				= 1;
		$d['shipping_id']			= (int)$shippingId;
		$d['payment_id']			= (int)$payment['id'];
		$d['coupon_id']				= (int)$coupon['id'];
		$d['currency_id']			= (int)$currency->id;
		$d['currency_code']			= $currency->code;
		$d['currency_exchange_rate']= $currency->exchange_rate;	
		$d['ip']					= (!empty($_SERVER['REMOTE_ADDR'])) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		$user_agent 				= (!empty($_SERVER['HTTP_USER_AGENT'])) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
		$d['user_agent']			= substr($user_agent, 0, 200);
		$d['order_token']			= PhocacartUtils::getToken();
		$d['tax_calculation']		= $pC->get( 'tax_calculation', 0 );
		$d['unit_weight']			= $unit_weight;
		$d['unit_volume']			= $unit_volume;
		$d['discount_id']			= $cart->getCartDiscountId();
		
		$d['vendor_id']				= $cart->getVendorId();
		$d['ticket_id']				= $cart->getTicketId();
		$d['unit_id']				= $cart->getUnitId();
		$d['section_id']			= $cart->getSectionId();
		$d['loyalty_card_number']	= $cart->getLoyaltyCartNumber();
		
		
		
		// --------------------
		// CHECK PAYMENT AND SHIPPING - TEST IF THE ORDER HAS RIGHT SHIPPING AND PAYMENT METHOD
		// --------------------
		$shippingClass	= new PhocacartShipping();
		$shippingClass->setType($this->type);
		$paymentClass	= new PhocacartPayment();
		$paymentClass->setType($this->type);
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

		
		// Check Shipping method
		if ($shippingId > 0) {
			// 1) User selected some method
			//    - check if this method even exists
			//	  - and check if the selected method meets every criteria and rules to be selected
			//$shippingMethods	= $shippingClass->checkAndGetShippingMethod($shippingId); CANNOT BE USED BECAUSE OF DIFFERENT VARIABLES IN ORDER
			$shippingMethods	= $shippingClass->getPossibleShippingMethods($total[0]['netto'], $total[0]['brutto'], $total[0]['quantity'], $country, $region, $total[0]['weight'], $total[0]['max_length'], $total[0]['max_width'], $total[0]['max_height'], $shippingId, 0 );
			
		} else {
			// 2) No shipping method selected
			$shippingMethods 	= false;
		}
		
		$sOCh							= array();// Shipping Options Checkout
		$sOCh['all_digital_products']	= isset($total[0]['countdigitalproducts']) && isset($total[0]['countallproducts']) && (int)$total[0]['countdigitalproducts'] == $total[0]['countallproducts'] ? 1 : 0;
		$shippingNotUsed 				= PhocacartShipping::isShippingNotUsed($sOCh);// REVERSE
		
		
	
		
		if (!empty($shippingMethods)) {
			// IS OK - some shipping method was selected
		} else if (empty($shippingMethods) && PhocacartPos::isPos()) {
			// IS OK - shipping method was not selected but we are in POS
		} else if (empty($shippingMethods) && $shippingNotUsed) {
			// IS OK - shipping method was not selected but there is none for selecting (shipping methods intentionally not used in shop)
			//         a) no shipping method is used
			//         b) or e.g. all items in cart are downloadable products and in Phoca Cart options is set that in such case shipping need not to be selected
		} else {
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_SHIPPING_METHOD');
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		
		// Check Payment method
		if ($payment['id'] > 0) {
			// 1) User selected some method
			//    - check if this method even exists
			//	  - and check if the selected method meets every criteria and rules to be selected
			//$paymentMethods	= $paymentClass->checkAndGetPaymentMethod($payment['id']); CANNOT BE USED BECAUSE OF DIFFERENT VARIABLES IN ORDER
			$paymentMethods	= $paymentClass->getPossiblePaymentMethods($total[0]['netto'], $total[0]['brutto'], $country, $region, $shippingId, $payment['id'], 0, $this->type );
			
			
		} else {
			// 2) No shipping method selected
			$paymentMethods = false;
		}
		
		
		$pOCh 							= array();// Payment Options Checkout
		$pOCh['order_amount_zero']		= $total[0]['brutto'] == 0 && $total[0]['netto'] == 0 ? 1 : 0;
		$paymentNotUsed 				= PhocacartPayment::isPaymentNotUsed($pOCh);// REVERSE

		
		
		
		if (!empty($paymentMethods)) {
			// IS OK
		} else if (empty($paymentMethods) && PhocacartPos::isPos()) {
			// IS OK
		} else if (empty($paymentMethods) && $paymentNotUsed) {
			// IS OK
		} else {
			$msg = JText::_('COM_PHOCACART_PLEASE_SELECT_RIGHT_PAYMENT_METHOD');
			$app->enqueueMessage($msg, 'error');
			return false;
		}

		
		$row = JTable::getInstance('PhocacartOrder', 'Table', array());

		
		
		if (!$row->bind($d)) {
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg()) . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		$row->date 		= gmdate('Y-m-d H:i:s');
		$row->modified	= $row->date;
		
		
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg()) . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg()) . $msgSuffix;
			$app->enqueueMessage($msg, 'error');
			return false;
		}

			
		// GET ID OF ORDER
		if ($row->id > 0) {
			
			// Set Order Billing
			$orderBillingData = $this->saveOrderBilling($row->id, $row->date);
		
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
						$this->saveOrderDownloads($orderProductId, $v['id'], $v['catid'], $row->id);
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
				PhocacartCoupon::storeCouponCount((int)$coupon['id']);
				PhocacartCoupon::storeCouponCountUser((int)$coupon['id'], $d['user_id'] );
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
					$d2['title']	= JText::_('COM_PHOCACART_REWARD_DISCOUNT').$total[5]['rewardproducttxtsuffix'];
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[5]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[5]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_REWARD_DISCOUNT').$total[5]['rewardproducttxtsuffix'];
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
					$d2['title']	= JText::_('COM_PHOCACART_CART_DISCOUNT').$total[3]['discountcarttxtsuffix'];
					$d2['type']		= 'dnetto';
					$d2['amount']	= '-'.$total[3]['dnetto'];
					$d2['ordering']	= $ordering;
					$d2['published']= 1;
					$d2['item_id']	= 0;
					$this->saveOrderTotal($d2);
					$ordering++;
				}
				if (isset($total[3]['dbrutto'])) {
					$d2['title']	= JText::_('COM_PHOCACART_CART_DISCOUNT').$total[3]['discountcarttxtsuffix'];
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
						$d2['title'] = $coupon['title'].$total[4]['couponcarttxtsuffix'];
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
						$d2['title'] = $coupon['title'].$total[4]['couponcarttxtsuffix'];
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
							$d2['item_id']	= (int)$k;// ID (Type) of VAT (10% or 20%)
							$this->saveOrderTotal($d2);
							$ordering++;
						}
					}
				}
				
				
				
				$d2['published']= 1;
				
				// Shipping
			
				if (!empty($shippingC)) {
					if (isset($shippingC['nettotxt']) && isset($shippingC['netto'])) {
						$d2['title']	= $shippingC['title'] . ' - ' . $shippingC['nettotxt'];
						$d2['type']		= 'snetto';
						$d2['amount']	= $shippingC['netto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['taxtxt']) && isset($shippingC['tax']) && $shippingC['tax'] > 0) {
						$d2['title']	= $shippingC['title'] . ' - ' . $shippingC['taxtxt'];
						$d2['type']		= 'stax';
						$d2['item_id']	= (int)$shippingC['taxid'];
						$d2['amount']	= $shippingC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($shippingC['bruttotxt']) && isset($shippingC['brutto'])) {
						$d2['title']	= $shippingC['title'] . ' - ' . $shippingC['bruttotxt'];
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
						$d2['title']	= $paymentC['title'] . ' - ' . $paymentC['nettotxt'];
						$d2['type']		= 'pnetto';
						$d2['amount']	= $paymentC['netto'];
						$d2['ordering']	= $ordering;
						$d2['item_id']	= 0;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['taxtxt']) && isset($paymentC['tax']) && $paymentC['tax']) {
						$d2['title']	= $paymentC['title'] . ' - ' . $paymentC['taxtxt'];
						$d2['type']		= 'ptax';
						$d2['item_id']	= (int)$paymentC['taxid'];
						$d2['amount']	= $paymentC['tax'];
						$d2['ordering']	= $ordering;
						$this->saveOrderTotal($d2);
						$ordering++;
					}
					
					if (isset($paymentC['bruttotxt']) && isset($paymentC['brutto'])) {
						$d2['title']	= $paymentC['title'] . ' - ' . $paymentC['bruttotxt'];
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
			
			// Change Status is not setting the status, it is about do getting info about status for sending emails, checking stock, 
			if ($guest) {
				// Don't check the user (status.php, render.php)
				PhocacartOrderStatus::changeStatus($row->id, $d['status_id'], $d['order_token']);// Notify user, notify others, emails send - will be decided in function
			} else {
				PhocacartOrderStatus::changeStatus($row->id, $d['status_id']);// Notify user, notify others, emails send - will be decided in function
			}
			
		
			// Proceed or not proceed to payment gateway - depends on payment method
			// By every new order - clean the proceed payment session
			$session 		= JFactory::getSession();
			$session->set('proceedpayment', array(), 'phocaCart');
			
			$response 					= PhocacartPayment::proceedToPaymentGateway($payment);
			$proceed					= $response['proceed'];
			$this->message_after_order	= $response['message'];
			
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
			
			//return true;
			return $row->id;
			
		} else {
			return false;
		}
		
		return false;
	}
	
	public function saveOrderBilling($id, $date) {
		
		$d 						= array();
		$d['id'] 				= $id;
		$d['order_number']		= PhocacartOrder::getOrderNumber($id, $date);
		$d['receipt_number']	= PhocacartOrder::getReceiptNumber($id, $date);
		$d['invoice_number']	= PhocacartOrder::getInvoiceNumber($id, $date);
		$d['invoice_prn']		= PhocacartOrder::getPaymentReferenceNumber($id, $date);
		$d['date']				= $date;
		$d['invoice_date']		= $date;
		$d['invoice_due_date']	= PhocacartOrder::getInvoiceDueDate($id, $date);
		
		
		$row = JTable::getInstance('PhocacartOrder', 'Table', array());
		
		if (!$row->bind($d)) {
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		return $d;
		
		
	}
	
	public function saveOrderUsers($d, $orderId) {
	
	
		$d = (array)$d;
		if (!isset($d['id'])){
			$d['id'] = 0;// Guest Checkout
			$d['user_id'] = 0;
		}
		$d['order_id'] 			= (int)$orderId;
		$d['user_address_id']	= (int)$d['id'];
		$d['user_token']		= PhocacartUtils::getToken();
		$userGroups				= PhocacartGroup::getGroupsById((int)$d['user_id'], 1, 1);
		$d['user_groups']		= serialize($userGroups);
		
		
		unset($d['id']);// we do new autoincrement
		$row = JTable::getInstance('PhocacartOrderUsers', 'Table', array());
		
		
		if (!$row->bind($d)) {
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		return true;
	}
	
	
	public function saveOrderProducts($d, $orderId) {
		$row = JTable::getInstance('PhocacartOrderProducts', 'Table', array());
		
		$checkP = PhocacartProduct::checkIfAccessPossible($d['id'], $d['catid'], $this->type);


		if (!$checkP) {
			return false;
		}
		
		
		// Additional info
		$d['default_price'] 				= $d['default_price'];
		$d['default_tax_rate'] 				= $d['taxrate'];
		$d['default_tax_id'] 				= $d['taxid'];
		$d['default_tax_calculation_rate'] 	= $d['taxcalctype'];
		$d['default_points_received'] 		= $d['default_points_received'];
	
		
		//$d['status_id']			= 1;// pending
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
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		

		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
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

						$row2 = JTable::getInstance('PhocacartOrderAttributes', 'Table', array());
						$d2 = array();
						$d2['order_id'] 		= (int)$orderId;
						$d2['product_id'] 		= (int)$d['product_id'];
						$d2['order_product_id']	= (int)$row->id;
						$d2['attribute_id']		= (int)$v2['aid'];
						$d2['option_id']		= (int)$v2['oid'];
						$d2['attribute_title']	= $v2['atitle'];
						$d2['option_title']		= $v2['otitle'];
						$d2['option_value']		= $v2['ovalue'];
						
						// Will be set order status
						// administrator\components\com_phocacart\libraries\phocacart\order\status.php
						// $stockA = PhocacartStock::handleStockAttributeOption($d2['option_id'], $d['status_id'], $d['quantity'] );
					
						if (!$row2->bind($d2)) {
							//throw new Exception($db->getErrorMsg());
							$msg = JText::_($db->getErrorMsg());
							$app->enqueueMessage($msg, 'error');
							return false;
						}
						
						if (!$row2->check()) {
							//throw new Exception($row2->getError());
							$msg = JText::_($row2->getErrorMsg());
							$app->enqueueMessage($msg, 'error');
							return false;
						}
						
						
						if (!$row2->store()) {
							//throw new Exception($row2->getError());
							$msg = JText::_($row2->getErrorMsg());
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
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
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
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		return true;
	}
	
	public function saveOrderTotal($d) {

		$row = JTable::getInstance('PhocacartOrderTotal', 'Table', array());
		
		//$d['published']				= 1;
		if (!$row->bind($d)) {
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
	
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
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
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		return true;
	}
	
	public function saveOrderDownloads($orderProductId, $productId, $catId, $orderId) {

		$row = JTable::getInstance('PhocacartOrderDownloads', 'Table', array());
		
		
		//$productItem 	= new PhocacartProduct();
		$product		= PhocacartProduct::getProduct((int)$productId, (int)$catId, $this->type);
		
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
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		$this->downloadable_product = 1;
		return true;
	}
	
	
	public function saveOrderProductDiscounts($orderProductId, $productId, $orderId, $k, $fullItems) {

		$db = JFactory::getDBO();
		
		// REWARD DIVIDED INTO PRODUCTS
		if (isset($fullItems[5][$k]['rewardproduct']) && $fullItems[5][$k]['rewardproduct'] == 1) {
			
			//$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());
			$query 	= ' DELETE FROM #__phocacart_order_product_discounts WHERE order_id = '. (int)$orderId . ' AND order_product_id = '.(int)$orderProductId. ' AND product_id = '.(int)$productId . ' AND type = 5';
			$db->setQuery($query);
			$db->execute();
			
			$amount 	= $fullItems[5][$k]['netto'];
			$netto 		= $fullItems[5][$k]['netto'];
			$brutto 	= $fullItems[5][$k]['brutto'];
			$tax 		= $fullItems[5][$k]['tax'];
			$final 		= $fullItems[5][$k]['final'];
			$quantity 	= $fullItems[5][$k]['quantity'];
			$catid 		= $fullItems[5][$k]['catid'];
			$query = ' INSERT INTO #__phocacart_order_product_discounts (order_id, product_id, order_product_id, discount_id, product_id_key, category_id, type, title, amount, netto, brutto, tax, final, quantity, published)'
			.' VALUES ('.(int)$orderId.', '.(int)$productId.', '.(int)$orderProductId.', '. 0 .', '.$db->quote($k).', '.(int)$catid.', 5, '.$db->quote($fullItems[5][$k]['rewardproducttitle']).', '.$amount.', '.$netto.', '.$brutto.', '.$tax.', '.$final.', '.(int)$quantity.', 0)';
			$db->setQuery($query);
			$db->execute();
		}
		
		// DISCOUNT PRODUCTS
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
		
		// DISCOUNT CART DIVEDED INTO PRODUCTS
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
		
		// COUPON DIVIDED INTO PRODUCTS
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
	
	
	public function saveRewardPoints($userId, $points, $orderBillingData, $published = 0, $type = 0) {

		$row = JTable::getInstance('PhocacartRewardPoint', 'Table', array());
		
		$d 						= array();
		$d['date']				= $orderBillingData['date'];//gmdate('Y-m-d H:i:s');
		$d['published']			= (int)$published;
		$d['points']			= (int)$points;
		$d['user_id']			= (int)$userId;
		$d['order_id']			= (int)$orderBillingData['id'];
		$d['title']				= JText::_('COM_PHOCACART_ORDER_NUMBER') . ' '. self::getOrderNumber($d['order_id'], $d['date'], $orderBillingData['order_number']) . ' ('.$d['date'].')';
		$d['type']				= (int)$type;
		
		
		if (!$row->bind($d)) {
			//throw new Exception($db->getErrorMsg());
			$msg = JText::_($db->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		if (!$row->check()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
			return false;
		}
		
		
		if (!$row->store()) {
			//throw new Exception($row->getError());
			$msg = JText::_($row->getErrorMsg());
			$app->enqueueMessage($msg, 'error');
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
	
	public function getMessageAfterOrder() {
		return $this->message_after_order;
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
		//if ($table != '') {
			$db 	= JFactory::getDBO();
			$query 	= ' DELETE FROM #__phocacart_orders WHERE id = '. (int)$orderId;
			$db->setQuery($query);
			$db->execute();
			return true;
		//}
		return false;
	}
	
	/* Static part */
	
	public static function getOrderStatus($statusId) {
	
		$db 	= JFactory::getDBO();
		$query 	= ' SELECT a.title FROM #__phocacart_order_statuses WHERE id = '. (int)$statusId . ' ORDER BY a.title';
		$db->setQuery($query);
		$status = $db->loadAssoc();
	}
	
	public static function getOrderDate($orderId) {
	
		$db 	= JFactory::getDBO();
		$query 	= ' SELECT date FROM #__phocacart_orders WHERE id = '. (int)$orderId . ' LIMIT 1';
		$db->setQuery($query);
		
		$date = $db->loadResult();
		
		return $date;
	}
	
	public static function getOrderBillingData($orderId) {
		$db 	= JFactory::getDBO();
		$query 	= ' SELECT id, date, order_number, receipt_number, invoice_number, invoice_prn, invoice_date, invoice_due_date'
				. ' FROM #__phocacart_orders WHERE id = '. (int)$orderId . ' LIMIT 1';
		$db->setQuery($query);

		$orderBillingData = $db->loadAssoc();
	
		return $orderBillingData;
	}
	
	public static function getOrderCusomerData($orderId) {
		$db 	= JFactory::getDBO();
		$query 	= ' SELECT *'
				. ' FROM #__phocacart_order_users WHERE order_id = '. (int)$orderId . ' LIMIT 2';
		$db->setQuery($query);

		$data = $db->loadAssocList();

		return $data;
	}
	
	public static function getOrderNumber($orderId, $date = false, $orderNumber = false) {
		
		if ($orderNumber) {
			return $orderNumber;// the number is stored in database yet
		}
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$order_number_format	= $paramsC->get('order_number_format', '{prefix}{year}{orderid}{suffix}');
		$order_number_prefix	= $paramsC->get('order_number_prefix', '');
		$order_number_suffix	= $paramsC->get('order_number_suffix', '');
		$order_id_length_order	= $paramsC->get('order_id_length_order', '10');
		
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		$dateO 	= PhocacartDate::splitDate($date);	
		
		$id = str_pad($orderId, $order_id_length_order, '0', STR_PAD_LEFT);
		
		$o = str_replace('{orderid}', $id, $order_number_format);
		$o = str_replace('{prefix}', $order_number_prefix, $o);
		$o = str_replace('{suffix}', $order_number_suffix, $o);
		$o = str_replace('{year}', $dateO['year'], $o);
		$o = str_replace('{month}', $dateO['month'], $o);
		$o = str_replace('{day}', $dateO['day'], $o);
		
		return $o;
	}
	
	public static function getInvoiceNumber($orderId, $date = false, $invoiceNumber = false) {
		
		if ($invoiceNumber) {
			return $invoiceNumber;// the number is stored in database yet
		}
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$invoice_number_format	= $paramsC->get('invoice_number_format', '{prefix}{year}{orderid}{suffix}');
		$invoice_number_prefix	= $paramsC->get('invoice_number_prefix', '');
		$invoice_number_suffix	= $paramsC->get('invoice_number_suffix', '');
		$order_id_length_invoice= $paramsC->get('order_id_length_invoice', '10');
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		$dateO 	= PhocacartDate::splitDate($date);	
		
		$id = str_pad($orderId, $order_id_length_invoice, '0', STR_PAD_LEFT);
		
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
	
	public static function getReceiptNumber($orderId, $date = false, $receiptNumber = false) {
		
		if ($receiptNumber) {
			return $receiptNumber;// the number is stored in database yet
		}
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$receipt_number_format	= $paramsC->get('receipt_number_format', '{prefix}{year}{orderid}{suffix}');
		$receipt_number_prefix	= $paramsC->get('receipt_number_prefix', '');
		$receipt_number_suffix	= $paramsC->get('receipt_number_suffix', '');
		$order_id_length_receipt	= $paramsC->get('order_id_length_receipt', '10');
		
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		$dateO 	= PhocacartDate::splitDate($date);	
		
		$id = str_pad($orderId, $order_id_length_receipt, '0', STR_PAD_LEFT);
		
		$o = str_replace('{orderid}', $id, $receipt_number_format);
		$o = str_replace('{prefix}', $receipt_number_prefix, $o);
		$o = str_replace('{suffix}', $receipt_number_suffix, $o);
		$o = str_replace('{year}', $dateO['year'], $o);
		$o = str_replace('{month}', $dateO['month'], $o);
		$o = str_replace('{day}', $dateO['day'], $o);
		
		return $o;
	}
	
	public static function getPaymentReferenceNumber($orderId, $date = false, $prmNumber = false ) {
		
		if ($prmNumber) {
			return $prmNumber;// the number is stored in database yet
		}
		
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$prn_number_format		= $paramsC->get('prn_number_format', '{prefix}{year}{orderid}{suffix}');
		$prn_number_prefix		= $paramsC->get('prn_number_prefix', '');
		$prn_number_suffix		= $paramsC->get('prn_number_suffix', '');
		$order_id_length_prn	= $paramsC->get('order_id_length_prn', '10');
		
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		$dateO 	= PhocacartDate::splitDate($date);	
		
		$id = str_pad($orderId, $order_id_length_prn, '0', STR_PAD_LEFT);
		
		$o = str_replace('{orderid}', $id, $prn_number_format);
		$o = str_replace('{prefix}', $prn_number_prefix, $o);
		$o = str_replace('{suffix}', $prn_number_suffix, $o);
		$o = str_replace('{year}', $dateO['year'], $o);
		$o = str_replace('{month}', $dateO['month'], $o);
		$o = str_replace('{day}', $dateO['day'], $o);
		
		return $o;
	}
	
	public static function getInvoiceDueDate($orderId, $date = false, $dueDate = false, $formatOutput = '') {
		
		if ($dueDate) {
			if ($formatOutput != '') {
				return JHtml::date($dueDate, $formatOutput);
			}
			return $dueDate;// the due date is stored in database yet
		}
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$invoice_due_date_days	= $paramsC->get('invoice_due_date_days', 5);
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		
		$dateTime = new DateTime($date);
		$dateTime->add(new DateInterval('P'.(int)$invoice_due_date_days.'D'));
		//return $dateTime->format('Y-m-d h:m:s');
		// default format output: 'DATE_FORMAT_LC4'
		if ($formatOutput != '') {
			return JHtml::date($dateTime->format('Y-m-d h:m:s'), $formatOutput);
		} else {
			return $dateTime->format('Y-m-d h:m:s');
		}
	}
	
	public static function getInvoiceDate($orderId, $date = false, $formatOutput = '') {
		
		$date 	= !$date ? self::getOrderDate($orderId) : $date;
		
		$dateTime = new DateTime($date);
		
		if ($formatOutput != '') {
			return JHtml::date($dateTime->format('Y-m-d h:m:s'), $formatOutput);
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
		
		$db 	= JFactory::getDBO();
		$wheres	= array();
		if ($oIdS != '') {
			$wheres[] = 'a.order_id IN ('.$oIdS.')';
		}
		
		$query 	= 'SELECT a.id, a.order_id, a.item_id, a.title, a.type, a.amount, a.amount_currency'
				. ' FROM #__phocacart_order_total AS a';
		if (!empty($wheres)) {
			$query 	.= ' WHERE ' . implode( ' AND ', $wheres );
		}
		$db->setQuery($query);
		
		
		$items 	= $db->loadAssocList();
		return $items;
		
	}
	
}