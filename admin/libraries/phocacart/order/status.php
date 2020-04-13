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
/*
phocacart import('phocacart.path.route');
*/


class PhocacartOrderStatus
{
	private static $status = array();

	private function __construct(){}

	public static function getStatus( $id = 0) {

		if( !array_key_exists( $id, self::$status ) ) {

			$db = JFactory::getDBO();
			$query = ' SELECT a.title, a.stock_movements, a.change_user_group, a.change_points_needed, a.change_points_received, a.email_customer, a.email_others, a.email_subject, a.email_subject_others, a.email_text, a.email_footer, a.email_text_others, a.email_send, a.email_attachments, a.orders_view_display, a.download FROM #__phocacart_order_statuses AS a'
					.' WHERE a.id = '.(int)$id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$s = $db->loadObject();

			if (!empty($s) && isset($s->title) && $s->title != '') {
				self::$status[$id]['title']						= JText::_($s->title);
				self::$status[$id]['id']						= (int)$id;
				self::$status[$id]['stock_movements']			= $s->stock_movements;
				self::$status[$id]['change_user_group']			= $s->change_user_group;
				self::$status[$id]['change_points_needed']		= $s->change_points_needed;
				self::$status[$id]['change_points_received']	= $s->change_points_received;
				self::$status[$id]['email_customer']			= $s->email_customer;
				self::$status[$id]['email_others']				= $s->email_others;
				self::$status[$id]['email_subject']				= $s->email_subject;
				self::$status[$id]['email_subject_others']		= $s->email_subject_others;
				self::$status[$id]['email_text']				= $s->email_text;
				self::$status[$id]['email_footer']				= $s->email_footer;
				self::$status[$id]['email_text_others']			= $s->email_text_others;
				self::$status[$id]['email_send']				= $s->email_send;
				self::$status[$id]['email_attachments']			= $s->email_attachments;
				self::$status[$id]['orders_view_display']		= $s->orders_view_display;
				self::$status[$id]['download']					= $s->download;
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
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}

	/*
	 * $orderToken ... token of order when there is no user - means that the method will not check the user in such case
	 * 1) guest checkout - token is active, user will be not checked when user calls this script (e.g. when ordering)
	 * 2) or payment method server contacts server - token is active, user will be not checked (user is not the one who calls the script but payment method)
	 *
	 * User will be not checked when:
	 * 1) status is changed in administration (vendor in admin is the the shopper user)
	 * 2) guest user makes the order (there is nothing to check)
	 * 3) payment method contact server to change status (payment method does not identify as user - $user = JFactory::getUser())
	 *
	 * $notifyUser 0 ... no  1 ... yes 99 ... defined in order status settings
	 * $notifyOthers   0 ... no  1 ... yes 99 ... defined in order status settings
	 * $emailSend  0 ... no  1 ... order, 2 ... invoice, 3 ... delivery_note,  99 ... defined in order status settings
	 * $stockMovements  = ... no  + ... plus - ... minus 99 ... defined in order status settings
	 */

	public static function changeStatus( $orderId, $statusId, $orderToken = '', $notifyUser = 99, $notifyOthers = 99, $emailSend = 99, $stockMovements = '99', $changeUserGroup = '99', $changePointsNeeded = '99', $changePointsReceived = '99') {





		// ORDER INFO
		$app 		= JFactory::getApplication();
		$order 		= new PhocacartOrderView();
		$common		= $order->getItemCommon($orderId);
		$orderNumber= PhocacartOrder::getOrderNumber($orderId, $common->date);
		$bas		= $order->getItemBaS($orderId, 1);
		//$totalBrutto= $order->getItemTotal($orderId, 0, 'brutto');
		$status 	= self::getStatus($statusId);

		$config		= JFactory::getConfig();

		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		//$invoice_prefix		= $paramsC->get('invoice_prefix', '');
		$attachment_format	= $paramsC->get('attachment_format', 0 );

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

		// 5) User Group Change
		if ($changeUserGroup == 0) {
			$changeUserGroupV = 0;
		} else if ($changeUserGroup == 1) {
			$changeUserGroupV = 1;
		} else if ($changeUserGroup == '99') {
			if (isset($status['change_user_group']) && $status['change_user_group'] == 0) {
				$changeUserGroupV = 0;
			} else if (isset($status['change_user_group']) && $status['change_user_group'] == 1) {
				$changeUserGroupV = 1;
			}
		}



		// 6) Reward Points Needed
		if ($changePointsNeeded == 0) {
			$changePointsNeededV = 0;
		} else if ($changePointsNeeded == 1) {
			$changePointsNeededV = 1;
		} else if ($changePointsNeeded == 2) {
			$changePointsNeededV = 2;
		} else if ($changePointsNeeded == '99') {
			if (isset($status['change_points_needed']) && $status['change_points_needed'] == 0) {
				$changePointsNeededV = 0;
			} else if (isset($status['change_points_needed']) && $status['change_points_needed'] == 1) {
				$changePointsNeededV = 1;
			} else if (isset($status['change_points_needed']) && $status['change_points_needed'] == 2) {
				$changePointsNeededV = 2;
			}
		}

		// 7) Reward Points Received
		if ($changePointsReceived == 0) {
			$changePointsReceivedV = 0;
		} else if ($changePointsReceived == 1) {
			$changePointsReceivedV = 1;
		} else if ($changePointsReceived == 2) {
			$changePointsReceivedV = 2;
		} else if ($changePointsReceived == '99') {
			if (isset($status['change_points_received']) && $status['change_points_received'] == 0) {
				$changePointsReceivedV = 0;
			} else if (isset($status['change_points_received']) && $status['change_points_received'] == 1) {
				$changePointsReceivedV = 1;
			} else if (isset($status['change_points_received']) && $status['change_points_received'] == 2) {
				$changePointsReceivedV = 2;
			}
		}



		// EMAIL
		$recipient 			= '';
		$recipientOthers	= '';
		$bcc 		= '';
		$subject 	= '';
		$body 		= '';



		if ($notifyUserV) {

			if (!$app->isClient('administrator')){

				// Frontend
				// Check if we can send email to customer
				$canSend		= 0;
				$user 			= PhocacartUser::getUser();
				$guest			= PhocacartUserGuestuser::getGuestUser();

				// $orderToken is set in case we will not check the user:
				// - in case of guest users
				// - in case of payment method server contacts the server to change the status
				if ($orderToken != '' && $orderToken == $common->order_token && $guest) {
					$canSend = 1;// User is guest - not logged in user run this script
					//PhocacartLog::add(4, 'CHECK', (int)$orderId, 'Guest User');
				} else if ($orderToken != '' && $orderToken == $common->order_token ) {
					$canSend = 1;// Payment method server returned status which will change order status - payment method runs this script
					//PhocacartLog::add(4, 'CHECK', (int)$orderId, 'Payment method');
				} else if ($user->id == $common->user_id) {
					$canSend = 1;// User is the customer who made the order
					//PhocacartLog::add(4, 'CHECK', (int)$orderId, 'Registered User');
				}

			} else {
				// Backend
				$canSend = 1;
			}

			// Payment method returns status
			if ($canSend == 0) {
				PhocacartLog::add(2, 'Order Status - Notify - ERROR', (int)$orderId, JText::_('COM_PHOCACART_NO_USER_ORDER_FOUND'));

				// Don't die here because even if we cannot send email to customer we can send email to others
				// $recipient == '' so no email will be sent to recipient
				//die (JText::_('COM_PHOCACART_NO_USER_ORDER_FOUND'));
			} else {

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
		}

		if ($notifyOthersV) {
			if (isset($status['email_others']) && $status['email_others'] != '') {
				$bcc = explode(',', $status['email_others'] );
				//if ($recipient == '') {
					if (isset($bcc[0]) && JMailHelper::isEmailAddress($bcc[0])) {
						$recipientOthers = $bcc[0];
					}
				//}
			}
		}


		// STOCK MOVEMENTS
		if ($stockMovementsV == '+' || $stockMovementsV == '-') {

			//Phocacart
			$orderV 		= new PhocacartOrderView();
			$products		= $orderV->getItemProducts($orderId);

			$a = array();
			if (!empty($products)) {
				foreach ($products as $k => $v) {

					// See: https://www.phoca.cz/documents/116-phoca-cart-component/932-stock-handling or
					//		https://www.phoca.cz/documents/116-phoca-cart-component/932-stock-handling
					if ((int)$v->stock_calculation == 1) {
						// =====================
						// b) Product Variations
						// In case of b) Product Variations - main product is one of many product variations
						if (!empty($v->attributes)) {
							foreach($v->attributes as $k2 => $v2) {
								if ((int)$v2->option_id > 0 && (int)$v2->productquantity  > 0) {
									// Status ID will be ignored as we know the Stock Movement / Quantity set by product not attribute
									PhocacartStock::handleStockAttributeOption((int)$v2->option_id, $statusId, (int)$v2->productquantity, $stockMovementsV);
								}
							}
						} else {
							if ((int)$v->product_id > 0 && (int)$v->quantity > 0) {
								// Status ID will be ignored as we know the Stock Movement
								PhocacartStock::handleStockProduct((int)$v->product_id, $statusId, (int)$v->quantity, $stockMovementsV);
							}
						}
					} else if ((int)$v->stock_calculation == 2) {

						// ============================
						// c) Advanced Stock Management
						if ((int)$v->product_id_key > 0 && (int)$v->quantity > 0) {
							// Status ID will be ignored as we know the Stock Movement
							PhocacartStock::handleStockProductKey($v->product_id_key, $statusId, (int)$v->quantity, $stockMovementsV);
						}

					} else {
						// ===============
						// a) Main Product
						if ((int)$v->product_id > 0 && (int)$v->quantity > 0) {
							// Status ID will be ignored as we know the Stock Movement
							PhocacartStock::handleStockProduct((int)$v->product_id, $statusId, (int)$v->quantity, $stockMovementsV);
						}

						if (!empty($v->attributes)) {
							foreach($v->attributes as $k2 => $v2) {
								if ((int)$v2->option_id > 0 && (int)$v2->productquantity  > 0) {
									// Status ID will be ignored as we know the Stock Movement / Quantity set by product not attribute
									PhocacartStock::handleStockAttributeOption((int)$v2->option_id, $statusId, (int)$v2->productquantity, $stockMovementsV);
								}
							}
						}
					}

				}
			}

		}

		// Change user group by changing of status
		if (($changeUserGroupV == 0 || $changeUserGroupV == 1) && (int)$common->user_id > 0) {
			PhocacartGroup::changeUserGroupByRule($common->user_id);
		}


		// POINTS NEEDED
		if ($changePointsNeededV == 1 || $changePointsNeededV == 2) {

			$published 	= $changePointsNeededV == 1 ? 1 : 0;
			$db			= JFactory::getDBO();

			$q = ' SELECT id '
				.' FROM #__phocacart_reward_points'
				.' WHERE order_id = '. (int)$orderId
				.' AND type = -1'
				.' ORDER BY id';

			$db->setQuery($q);

			$idExists = $db->loadResult();



			if ((int)$idExists > 0) {
				$query = 'UPDATE #__phocacart_reward_points SET'
					.' published = '.(int)$published
					.' WHERE id = '.(int)$idExists;
				$db->setQuery($query);
				$db->execute();
			}
		}

		// POINTS RECEIVED
		if ($changePointsReceivedV == 1 || $changePointsReceivedV == 2) {

			$published 	= $changePointsReceivedV == 1 ? 1 : 0;
			$db			= JFactory::getDBO();
			$q = ' SELECT id '
				.' FROM #__phocacart_reward_points'
				.' WHERE order_id = '. (int)$orderId
				.' AND type = 1'
				.' ORDER BY id';
			$db->setQuery($q);
			$idExists = $db->loadResult();


			if ((int)$idExists > 0) {
				$query = 'UPDATE #__phocacart_reward_points SET'
					.' published = '.(int)$published
					.' WHERE id = '.(int)$idExists;
				$db->setQuery($query);
				$db->execute();
			}
		}


		// DOWNLOAD
		if (isset($status['download'])) {
			PhocacartDownload::setStatusByOrder((int)$orderId, (int)$status['download']);
		}


		// ------------------------
		// BUILD EMAIL for customer or others
		// ------------------------

		// Set language of order for the customer
		$pLang = new PhocacartLanguage();


		if (($recipient != '' && JMailHelper::isEmailAddress($recipient)) || ($recipientOthers != '' && JMailHelper::isEmailAddress($recipientOthers))) {

			$sitename 		= $config->get('sitename');


			//if ($status['email_text'] != '') {

			// EMAIL CUSTOMER
			$emptyBody = 0;
			if ($status['email_text'] == '') {
				$emptyBody = 1;
			}
			$body = $status['email_text'];
			// EMAIL OTHERS
			$emptyBodyOthers = 0;
			if ($status['email_text_others'] == '') {
				$emptyBodyOthers = 1;
			}
			$bodyOthers = $status['email_text_others'];

			// REPLACE
			$r = PhocacartText::prepareReplaceText($order, $orderId, $common, $bas);

			$r['email'] 		= $recipient;// Overwrites the $r
			$r['email_others'] 	= $recipientOthers;
			$r['name_others'] = '';



			// EMAIL CUSTOMER
			if ($status['email_subject'] != '') {

				$emailSubject = PhocacartText::completeText($status['email_subject'], $r, 1);
				$subject = $emailSubject;// .' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			} else if ($status['title'] != '') {

				$subject = $sitename. ' - ' .$status['title'].' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			}
			// EMAIL OTHERS
			if ($status['email_subject_others'] != '') {
				$emailSubjectO = PhocacartText::completeText($status['email_subject_others'], $r, 2);
				$subjectOthers = $emailSubjectO;// .' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			} else if ($status['title'] != '') {
				$subjectOthers = $sitename. ' - ' .$status['title'].' ' . JText::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			}


			if (!isset($bas['b'])) {
				$bas['b'] = array();
			}
			if (!isset($bas['s'])) {
				$bas['s'] = array();
			}


			// COMPLETE BODY
			$body 			= PhocacartText::completeText($body, $r, 1);
			$bodyOthers 	= PhocacartText::completeText($bodyOthers, $r, 2);

			$body 			= PhocacartText::completeTextFormFields($body, $bas['b'], 1);
			$bodyOthers 	= PhocacartText::completeTextFormFields($bodyOthers, $bas['b'], 1);

			$body 			= PhocacartText::completeTextFormFields($body, $bas['s'], 2);
			$bodyOthers 	= PhocacartText::completeTextFormFields($bodyOthers, $bas['s'], 2);

			// PDF
			$pdfV					= array();
			$attachmentContent		= '';
			$attachmentName			= '';
			$pdfV['plugin-pdf']		= PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
			$pdfV['component-pdf']	= PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
			$pdfV['pdf']			= 0;






			if ($pdfV['plugin-pdf'] == 1 && $pdfV['component-pdf'] == 1) {
				if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_phocapdf/helpers/phocapdfrender.php')) {
					require_once(JPATH_ADMINISTRATOR.'/components/com_phocapdf/helpers/phocapdfrender.php');
				} else {
					PhocacartLog::add(2, 'Order Status - Notify - ERROR (PDF Class)', (int)$orderId, 'Render PDF file could not be found in system');
					throw new Exception('Error - Phoca PDF Helper - Render PDF file could not be found in system', 500);
					return false;
				}
				$pdfV['pdf'] = 1;
			}


			// All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
			// which language version the customer got
			$pLang->setLanguage($common->user_lang);




			switch ($emailSendV) {
				case 1:

					$orderRender = new PhocacartOrderRender();

					if ($attachment_format == 0 || $attachment_format == 2) {
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 1, 'mail', $orderToken);

						$bodyOthers .= "<br><br>";
						$bodyOthers .= $orderRender->render($orderId, 1, 'mail', $orderToken);
					}

					if ($pdfV['pdf'] == 1 && ($attachment_format == 1 || $attachment_format == 2)) {
						$staticData					= array();
						//$orderNumber				= PhocacartOrder::getOrderNumber($orderId, $common->date);
						$staticData['option']		= 'com_phocacart';
						$staticData['title']		= JText::_('COM_PHOCACART_ORDER_NR'). ': '. $orderNumber;
						$staticData['file']			= '';// Must be empty to not save the pdf to server
						$staticData['filename']		= strip_tags(JText::_('COM_PHOCACART_ORDER'). '_'. $orderNumber).'.pdf';
						$staticData['subject']		= '';
						$staticData['keywords']		= '';
						$staticData['output']		= $orderRender->render($orderId, 1, 'pdf', $orderToken);
						$attachmentContent 			= PhocaPDFRender::renderPDF('', $staticData);
						$attachmentName 			= $staticData['filename'];
					}

				break;
				case 2:
					$orderRender = new PhocacartOrderRender();

					$invoiceNumber				= PhocacartOrder::getInvoiceNumber($orderId, $common->date, $common->invoice_number);


					// If invoice is not created yet, it cannot be sent
					if ($invoiceNumber == '') {
						PhocacartLog::add(3, 'Status changed - sending email: The invoice should have been attached to the email, but it doesn not exist yet. Check order status settings and billing settings.', $orderId, 'Order ID: '. $orderId.', Status ID: '.$statusId);
					} else {
						if ($attachment_format == 0 || $attachment_format == 2) {
							$body .= "<br><br>";
							$body .= $orderRender->render($orderId, 2, 'mail', $orderToken);

							$bodyOthers .= "<br><br>";
							$bodyOthers .= $orderRender->render($orderId, 2, 'mail', $orderToken);
						}

						if ($pdfV['pdf'] == 1 && ($attachment_format == 1 || $attachment_format == 2)) {
							$staticData = array();

							$staticData['option'] = 'com_phocacart';
							$staticData['title'] = JText::_('COM_PHOCACART_INVOICE_NR') . ': ' . $invoiceNumber;
							$staticData['file'] = '';// Must be empty to not save the pdf to server
							$staticData['filename'] = strip_tags(JText::_('COM_PHOCACART_INVOICE') . '_' . $invoiceNumber) . '.pdf';
							$staticData['subject'] = '';
							$staticData['keywords'] = '';
							$staticData['output'] = $orderRender->render($orderId, 2, 'pdf', $orderToken);
							$attachmentContent = PhocaPDFRender::renderPDF('', $staticData);
							$attachmentName = $staticData['filename'];

						}
					}

				break;
				case 3:
					$orderRender = new PhocacartOrderRender();

					if ($attachment_format == 0 || $attachment_format == 2) {
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 3, 'mail', $orderToken);

						$bodyOthers .= "<br><br>";
						$bodyOthers .= $orderRender->render($orderId, 3, 'mail', $orderToken);
					}

					if ($pdfV['pdf'] == 1 && ($attachment_format == 1 || $attachment_format == 2)) {
						$staticData					= array();
						$orderNumber				= PhocacartOrder::getOrderNumber($orderId);
						$staticData['option']		= 'com_phocacart';
						$staticData['title']		= JText::_('COM_PHOCACART_ORDER_NR'). ': '. $orderNumber;
						$staticData['file']			= '';// Must be empty to not save the pdf to server
						$staticData['filename']		= strip_tags(JText::_('COM_PHOCACART_ORDER'). '_'. $orderNumber).'.pdf';
						$staticData['subject']		= '';
						$staticData['keywords']		= '';
						$staticData['output']		= $orderRender->render($orderId, 3, 'pdf', $orderToken);
						$attachmentContent 			= PhocaPDFRender::renderPDF('', $staticData);
						$attachmentName 			= $staticData['filename'];
					}



				break;

			}

			// Email Footer
			$body .= '<br><br>'.PhocacartText::completeText($status['email_footer'], $r, 1);

			$pLang->setLanguageBack();




			JPluginHelper::importPlugin( 'system' );
			//$dispatcher = J EventDispatcher::getInstance();
			JPluginHelper::importPlugin('plgSystemMultilanguagesck');


			// CUSTOMER
			if (isset($common->user_lang) && $common->user_lang != '' && $common->user_lang != '*') {


				$pLang->setLanguage($common->user_lang);

				\JFactory::getApplication()->triggerEvent('onChangeText', array(&$subject));
				\JFactory::getApplication()->triggerEvent('onChangeText', array(&$body));


				// Set language back to default
				$pLang->setLanguageBack();


			} else {
				\JFactory::getApplication()->triggerEvent('onChangeText', array(&$subject));
				\JFactory::getApplication()->triggerEvent('onChangeText', array(&$body));
			}

			// OTHERS
			\JFactory::getApplication()->triggerEvent('onChangeText', array(&$subjectOthers));
			\JFactory::getApplication()->triggerEvent('onChangeText', array(&$bodyOthers));



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
				$body = JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNumber .' - '. JText::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'];
			}*/

			$notify = 0;
			// ---------
			// CUSTOMERS
			if ($recipient != '' && JMailHelper::isEmailAddress($recipient)) {
				if ($emptyBody == 1) {
					$body = JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNumber .' - '. JText::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'] . '<br>'. $body;
				}

				// Notify
				// 1 ... sent
				// 0 ... not sent
				// -1 ... not sent (error)


				// Additional attachments
				$attachment = null;
				if (isset($status['email_attachments']) && !empty($status['email_attachments'])) {
					$attachmentA = json_decode($status['email_attachments'], true);

					if (!empty($attachmentA)) {

						$attachment = array();
						$pathAttachment = PhocacartPath::getPath('attachmentfile');

						foreach ($attachmentA as $k => $v) {
							if (isset($v['file_attachment']) && $v['file_attachment'] != '') {

								$pathAttachmentFile = $pathAttachment['orig_abs_ds'] . $v['file_attachment'];

								if (Joomla\CMS\Filesystem\File::exists($pathAttachmentFile)){
									$attachment[] = $pathAttachmentFile;
								}
							}
						}
					}
				}


				$notify = PhocacartEmail::sendEmail('', '', $recipient, $subject, $body, true, null, null, $attachment, $attachmentContent, $attachmentName);

			}

			// ------
			// OTHERS
			if ($recipientOthers != '' && JMailHelper::isEmailAddress($recipientOthers)) {
				if ($emptyBodyOthers == 1) {
					$bodyOthers = JText::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNumber .' - '. JText::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'] . '<br>'. $bodyOthers;
				}

				$attachment = null;
				$notifyOthers = PhocacartEmail::sendEmail('', '', $recipientOthers, $subjectOthers, $bodyOthers, true, null, $bcc, $attachment, $attachmentContent, $attachmentName);


			}



			// Notify is based only on customer email
			if ($recipient != '' && JMailHelper::isEmailAddress($recipient)) {
				if ($notify) {
					if ($app->isClient('administrator')){
						$app->enqueueMessage(JText::_('COM_PHOCACART_EMAIL_IF_NO_ERROR_EMAIL_SENT'));
					}
					return $notify;// 1
				} else {
					return -1;
				}
			}

		}

		return false;// 0
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
	 * but sometimes e.g. when Payment method set the response, we need to change the status with help of this function
	 * in order table as in fact when making payment response nothing happen to order table - only status is changed
	 * Payment method runs both scripts: changeStatus - to send notify emails, set stock, etc. and changeStatusInOrderTable to check the status in table
	 */

	public static function changeStatusInOrderTable($orderId, $statusId) {
		$db 		= JFactory::getDBO();
		$query = ' UPDATE #__phocacart_orders SET status_id = '.(int)$statusId
					.' WHERE id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();

		// Set invoice data in case status can set invoice ID
		PhocacartOrder::storeOrderReceiptInvoiceId((int)$orderId, gmdate('Y-m-d H:i:s'), (int)$statusId, array('I'));

		return true;

	}

	public static function setHistory($id, $statusId, $notify, $comment) {

		$db 		= JFactory::getDBO();
		$user 		= PhocacartUser::getUser();
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

		return JHtml::_('select.genericlist',  $data,  'jform[email_send]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
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

		return JHtml::_('select.genericlist',  $data,  'jform[stock_movements]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangeUserGroupSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => JText::_('COM_PHOCACART_USER_GROUP_UNCHANGED'),
			'1' => JText::_('COM_PHOCACART_USER_GROUP_CHANGED')
		);

		if ($value == '') {
			$value = 0;
		}

		return JHtml::_('select.genericlist',  $data,  'jform[change_user_group]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangeChangePointsNeededSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => JText::_('COM_PHOCACART_REWARD_POINTS_UNCHANGED'),
			'1' => JText::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_APPROVED'),
			'2' => JText::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_NOT_APPROVED')
		);

		if ($value == '') {
			$value = 0;
		}

		return JHtml::_('select.genericlist',  $data,  'jform[change_points_needed]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangePointsReceivedSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => JText::_('COM_PHOCACART_REWARD_POINTS_UNCHANGED'),
			'1' => JText::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_APPROVED'),
			'2' => JText::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_NOT_APPROVED')
		);

		if ($value == '') {
			$value = 0;
		}

		return JHtml::_('select.genericlist',  $data,  'jform[change_points_received]', 'class="inputbox"', 'value', 'text', $value, $data[$value] );
	}
}
?>
