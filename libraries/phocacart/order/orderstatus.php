<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
phocacartimport('phocacart.path.route');

class PhocaCartOrderStatus
{
	private static $status = array();
	
	private function __construct(){}
	
	public static function getStatus( $id = 0) {
		
		if( !array_key_exists( $id, self::$status ) ) {
			
			$db = JFactory::getDBO();
			$query = ' SELECT a.title, a.stock_movements, a.email_customer, a.email_others, a.email_subject, a.email_text, a.email_send, a.download FROM #__phocacart_order_statuses AS a'
					.' WHERE a.id = '.(int)$id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$s = $db->loadObject();
	
			if (!empty($s) && isset($s->title) && $s->title != '') {
				self::$status[$id]['title']				= JText::_($s->title);
				self::$status[$id]['id']				= (int)$id;
				self::$status[$id]['stock_movements']	= $s->stock_movements;
				self::$status[$id]['email_customer']	= $s->email_customer;
				self::$status[$id]['email_others']		= $s->email_others;
				self::$status[$id]['email_subject']		= $s->email_subject;
				self::$status[$id]['email_text']		= $s->email_text;
				self::$status[$id]['email_send']		= $s->email_send;
				self::$status[$id]['download']			= $s->download;
				$query = 'SELECT a.title AS text, a.id AS value'
				. ' FROM #__phocacart_order_statuses AS a'
				. ' WHERE a.published = 1'
				. ' ORDER BY a.ordering';
				$db->setQuery( $query );
				$data = $db->loadObjectList();
				if (!empty($data)) {
					foreach ($data as $k => $v) {
						
						$v->text = JText::_($v->text);
					}
					
				}
				self::$status[$id]['data'] = $data;
				
				
			} else {
				self::$status[$id] = false;
			}
		}
		
		return self::$status[$id];
	}	
	
	public final function __clone() {
		JError::raiseWarning(500, 'Function Error: Cannot clone instance of Singleton pattern');// No JText - for developers only
		return false;
	}
	
	/*
	 * $orderToken ... token of order when there is no user (guest checkout)
	 * $notifyUser 0 ... no  1 ... yes 99 ... defined in order status settings
	 * $notifyOthers   0 ... no  1 ... yes 99 ... defined in order status settings
	 * $emailSend  0 ... no  1 ... order, 2 ... invoice, 3 ... delivery_note,  99 ... defined in order status settings
	 * $stockMovements  = ... no  + ... plus - ... minus 99 ... defined in order status settings
	 */
	
	public static function changeStatus( $orderId, $statusId, $orderToken = '', $notifyUser = 99, $notifyOthers = 99, $emailSend = 99, $stockMovements = '99') {
	
	
		
		// ORDER INFO
		$app 		= JFactory::getApplication();
		$order 		= new PhocaCartOrderView();
		$common		= $order->getItemCommon($orderId);
		$bas		= $order->getItemBaS($orderId, 1);
		$status 	= self::getStatus($statusId);
		
		$config		= JFactory::getConfig();
		
		// FIND THE RIGHT VALUES FOR VARIBALES - different if we are in frontend or backend
		$notifyUserV 	= false;
		$notifyOthersV	= false;
		$emailSendV		= false;
		$stockMovementsV= '';
		
		// 1) NOTIFY USER
		if ($notifyUser == 0) {
			$notifyUserV = false;
		} else if ($notifyUser == 1) {
			$notifyUserV = true;
		} else if ($notifyUser == 99) {
			if (isset($status['email_customer']) && (int)$status['email_customer'] > 0) {
				$notifyUserV = true;
			}
		}
		
		// 2) NOTIFY OTHERS
		if ($notifyOthers == 0) {
			$notifyOthersV = false;
		} else if ($notifyOthers == 1 ) {
			$notifyOthersV = true;
		} else if ($notifyOthers == 99) {
			if (isset($status['email_others']) && $status['email_others'] != '') {
				$notifyOthersV = true;
			}
		}

		// 3) EMAIL SEND
		if ($emailSend == 0) {
			$emailSendV = 0;
		} else if ($emailSend == 1) {
			$emailSendV = 1;
		} else if ($emailSend == 2) {
			$emailSendV = 2;
		} else if ($emailSend == 3) {
			$emailSendV = 3;
		} else if ($emailSend == 99) {
			if (isset($status['email_send']) && $status['email_send'] == 0) {
				$emailSendV = 0;
			} else if (isset($status['email_send']) && $status['email_send'] == 1) {
				$emailSendV = 1;
			} else if (isset($status['email_send']) && $status['email_send'] == 2) {
				$emailSendV = 2;
			} else if (isset($status['email_send']) && $status['email_send'] == 3) {
				$emailSendV = 3;
			}
		}
		


		
		// 4) STOCK MOVEMENTS
		if ($stockMovements == '0') {
			$stockMovementsV = '';
		} else if ($stockMovements == '+') {
			$stockMovementsV = '+';
	
		} else if ($stockMovements == '-') {
			$stockMovementsV = '-';
		} else if ($stockMovements == '99') {
			if (isset($status['stock_movements']) && $status['stock_movements'] == '=') {
				$stockMovementsV = '';
			} else if (isset($status['stock_movements']) && $status['stock_movements'] == '+') {
				$stockMovementsV = '+';
			} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
				$stockMovementsV = '-';
			}
		}
		
		
		
		// EMAIL
		$recipient 	= '';
		$bcc 		= '';
		$subject 	= '';
		$body 		= '';
		

		
		if ($notifyUserV) {
			
			if (!$app->isAdmin()){
				$user 			= JFactory::getUser();
				$guest			= PhocaCartGuestUser::getGuestUser();
				if (!$guest && $user->id != $common->user_id) {
					die ('COM_PHOCACART_NO_USER_ORDER_FOUND');
				}
			}
			
			if (isset($bas['b']['email_contact']) && $bas['b']['email_contact'] != '' && JMailHelper::isEmailAddress($bas['b']['email_contact'])) {
				$recipient = $bas['b']['email_contact'];
			} else if (isset($bas['b']['email']) && $bas['b']['email'] != '' && JMailHelper::isEmailAddress($bas['b']['email'])) {
				$recipient = $bas['b']['email'];
			} else if (isset($bas['s']['email_contact']) && $bas['s']['email_contact'] != '' && JMailHelper::isEmailAddress($bas['s']['email_contact'])) {
				$recipient = $bas['s']['email_contact'];
			} else if (isset($bas['s']['email']) && $bas['s']['email'] != '' && JMailHelper::isEmailAddress($bas['s']['email'])) {
				$recipient = $bas['s']['email'];
			}
		}
		
		if ($notifyOthersV) {
			if (isset($status['email_others']) && $status['email_others'] != '') {
				$bcc = explode(',', $status['email_others'] );
				if ($recipient == '') {
					if (isset($bcc[0]) && JMailHelper::isEmailAddress($bcc[0])) {
						$recipient = $bcc[0];
					}
				} 
			}
		}
		
		
		// STOCK MOVEMENTS
		if ($stockMovementsV == '+' || $stockMovementsV == '-') {
		
			//PhocaCart
			$orderV 		= new PhocaCartOrderView();
			$products		= $orderV->getItemProducts($orderId);
			$a = array();
			if (!empty($products)) {
				foreach ($products as $k => $v) {
					if ((int)$v->product_id > 0 && (int)$v->quantity > 0) {
						// Status ID will be ignored as we know the Stock Movement
						PhocaCartStock::handleStockProduct((int)$v->product_id, $statusId, (int)$v->quantity, $stockMovementsV);
					}
					if (!empty($v->attributes)) {
						foreach($v->attributes as $k2 => $v2) {
							if ((int)$v2->option_id > 0 && (int)$v2->productquantity  > 0) {
								// Status ID will be ignored as we know the Stock Movement / Quantity set by product not attribute
								PhocaCartStock::handleStockAttributeOption((int)$v2->option_id, $statusId, (int)$v2->productquantity, $stockMovementsV);
							}
						}
					}
					
				}
			}

		}
		
		// DOWNLOAD
		if (isset($status['download'])) {
			PhocaCartDownload::setStatusByOrder((int)$orderId, (int)$status['download']);
		}

		
		if ($recipient != '' && JMailHelper::isEmailAddress($recipient)) {
			
			$sitename 		= $config->get('sitename');
			
			$orderNr = PhocaCartOrder::getOrderNumber($orderId);
			if ($status['email_subject'] != '') {
				$subject = $status['email_subject'] .' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNr;
			} else if ($status['title'] != '') {
				$subject = $sitename. ' - ' .$status['title'].' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNr;
			}
			
			
			//if ($status['email_text'] != '') {
				$emptyBody = 0;
				if ($status['email_text'] == '') {
					$emptyBody = 1;
				}
				$body = $status['email_text'];
				
				// REPLACE
				$r = array();
				$r['email'] = $recipient;
				$name = '';
				if (isset($bas['b']['name_first']) && $bas['b']['name_first']) {
					$name = $bas['b']['name_first'];
				}
				if (isset($bas['b']['name_last']) && $bas['b']['name_last']) {
					if ($name != '') {
						$name = $name . ' '. $bas['b']['name_last'];
					} else {
						$name = $bas['b']['name_last'];
					}
				} 
				$r['name'] = $name;
				
				// Standard User get standard download page and order page
				if ($common->user_id > 0) {
					$r['orderlink'] = PhocaCartPath::getRightPathLink(PhocaCartRoute::getOrdersRoute());
					$r['downloadlink'] = PhocaCartPath::getRightPathLink(PhocaCartRoute::getDownloadRoute());
				} else {
					if (isset($common->order_token) && $common->order_token != '') {
						$r['orderlink'] = PhocaCartPath::getRightPathLink(PhocaCartRoute::getOrdersRoute() . '&o='.$common->order_token);
					}
					$products 	= $order->getItemProducts($orderId);
					
					$downloadO 	= '';
					if(!empty($products) && isset($common->order_token) && $common->order_token != '') {
						$downloadO	= '<p>&nbsp;</p><h4>'.JText::_('COM_PHOCACART_DOWNLOAD_LINKS').'</h4>';
						foreach ($products as $k => $v) {
							if ($v->download_published == 1) {
								$downloadO .= '<div><strong>'.$v->title.'</strong></div>';
								$downloadLink = PhocaCartPath::getRightPathLink(PhocaCartRoute::getDownloadRoute() . '&o='.$common->order_token.'&d='.$v->download_token);
								$downloadO .= '<div><a href="'.$downloadLink.'">'.$downloadLink.'</a></div>';
							}
						}
						$downloadO .= '<p>&nbsp;</p>';
					}
					$r['downloadlink'] = $downloadO;
				}
				
			
				
				$body 	= PhocaCartEmail::completeMail($body, $r);
				

				switch ($emailSendV) {
					case 1:
						$orderRender = new PhocaCartOrderRender();
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 1, 2, $orderToken);
					break;
					case 2:
						$orderRender = new PhocaCartOrderRender();
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 2, 2, $orderToken);
					break;
					case 3:
						$orderRender = new PhocaCartOrderRender();
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 3, 2, $orderToken);
					break;
				
				}
				
			//}

			
			// if $emptyBody is empty (1) then it means, that there is not custom text
			// so we can paste the order status message
			// it does not mean, the body is empty, it can be filled with invoice, order or delivery note
			// so this means:
			// body (empty) + invoice/receipt/delivery note --> add status message
			// body (custom text) + invoice/receipt/delivery --> don't add status message
			// body (empty) --> add status message
			// body (custom text) --> don't add status message
			
			/*if ($body == '') {
				$body = JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNr .' - '. JText::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'];
			}*/
			
			if ($emptyBody == 1) {
				$body = JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNr .' - '. JText::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'] . '<br>'. $body;
			}
			
			
				
			$notify = PhocaCartEmail::sendEmail('', '', $recipient, $subject, $body, true, null, $bcc);
			if ($notify) {
				if ($app->isAdmin()){
					$app->enqueueMessage(JText::_('COM_PHOCACART_EMAIL_IF_NO_ERROR_EMAIL_SENT'));
				}
			}
			return $notify;
		}
		return false;
	}
	
	/* Usually
	 * - order is saved - the status id in order table is set while saving order (libraries/order/order.php)
	 * - status is changed in administration - the status id in order table is set while changing status (models/phocacarteditstatus.php)
	 * - BUT e.g. if payment method returns status, the status in order needs to be changed - by this function
	 *
	 * method changeStatus - in fact check all possible conditions and cares about notify (send email)
	 * method changeStatusInOrderTable - changes the status directly in order table
	 *
	 * Mostly changeStatus is called when the status is changed in order table (saving order, changing status)
	 * but sometimes e.g. when Payment method set the response, we need to change teh status with help of this function
	 * in order table as in fact when making payment response nothing happen to order table - only status is changed
	 */
	 
	public static function changeStatusInOrderTable($orderId, $statusId) {
		$db 		= JFactory::getDBO();
		$query = ' UPDATE #__phocacart_orders SET status_id = '.(int)$statusId
					.' WHERE id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();
		return true;
	
	}
	
	public static function setHistory($id, $statusId, $notify, $comment) {
		
		$db 		= JFactory::getDBO();
		$user 		= JFactory::getUser();
		$userId		= 0;
		if (isset($user->id) && (int)$user->id > 0) {
			$userId = (int)$user->id;
		}
		$date = JFactory::getDate()->toSql();
	
		$valuesString 	= '('.(int)$id.', '.(int)$statusId.', '.(int)$notify.', '.$db->quote($comment).', '.$db->quote($date).', '.(int)$userId.')';
		$query = ' INSERT INTO #__phocacart_order_history (order_id, order_status_id, notify, comment, date, user_id)'
					.' VALUES '.(string)$valuesString;
		$db->setQuery($query);
		$db->execute();
		return true;
	}

	
	public static function getEmailSendSelectBox($value) {
		
		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			0 => JText::_('COM_PHOCACART_NOTHING'),
			1 => JText::_('COM_PHOCACART_ORDER'),
			2 => JText::_('COM_PHOCACART_INVOICE'),
			3 => JText::_('COM_PHOCACART_DELIVERY_NOTE')
		);
	
		return JHTML::_('select.genericlist',  $data,  'jform[email_send]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}
	
	public static function getStockMovementsSelectBox($value) {
		
		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'=' => JText::_('COM_PHOCACART_ITEMS_UNCHANGED'),
			'+' => JText::_('COM_PHOCACART_ITEMS_ADDED'),
			'-' => JText::_('COM_PHOCACART_ITEMS_SUBTRACTED')
		);
		
		if ($value == 0 || $value == '') {
			$value = '=';
		}
	
		return JHTML::_('select.genericlist',  $data,  'jform[stock_movements]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}
}
?>