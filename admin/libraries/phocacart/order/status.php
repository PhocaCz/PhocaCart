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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;

/*
phocacart import('phocacart.path.route');
*/


class PhocacartOrderStatus
{
	private static $status = array();

	private function __construct(){}

	public static function getStatus( $id = 0) {

		if( !array_key_exists( $id, self::$status ) ) {

			$db = Factory::getDBO();
			$query = ' SELECT a.title, a.stock_movements, a.change_user_group, a.change_points_needed, a.change_points_received,'
					.' a.email_customer, a.email_others, a.email_subject, a.email_subject_others, a.email_text, a.email_footer,'
					.' a.email_text_others, a.email_send, a.email_send_format, a.email_attachments, a.orders_view_display, a.download, a.email_downloadlink_description,'
					.' a.activate_gift, a.email_gift, a.email_subject_gift_sender, a.email_text_gift_sender, a.email_subject_gift_recipient, a.email_text_gift_recipient, a.email_gift_format'
					.' FROM #__phocacart_order_statuses AS a'
					.' WHERE a.id = '.(int)$id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$s = $db->loadObject();

			if (!empty($s) && isset($s->title) && $s->title != '') {
				self::$status[$id]['title']						= Text::_($s->title);
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
				self::$status[$id]['email_send_format']			= $s->email_send_format;
				self::$status[$id]['email_attachments']			= $s->email_attachments;
				self::$status[$id]['activate_gift']				= $s->activate_gift;
				self::$status[$id]['email_gift']				= $s->email_gift;
				self::$status[$id]['email_subject_gift_sender']	= $s->email_subject_gift_sender;
				self::$status[$id]['email_text_gift_sender']	= $s->email_text_gift_sender;
				self::$status[$id]['email_subject_gift_recipient']	= $s->email_subject_gift_recipient;
				self::$status[$id]['email_text_gift_recipient']	= $s->email_text_gift_recipient;
				self::$status[$id]['email_gift_format']			= $s->email_gift_format;
				self::$status[$id]['orders_view_display']		= $s->orders_view_display;
				self::$status[$id]['download']					= $s->download;
				self::$status[$id]['email_downloadlink_description']= $s->email_downloadlink_description;
				$query = 'SELECT a.title AS text, a.id AS value'
				. ' FROM #__phocacart_order_statuses AS a'
				. ' WHERE a.published = 1'
				. ' ORDER BY a.ordering';
				$db->setQuery( $query );
				$data = $db->loadObjectList();
				if (!empty($data)) {
					foreach ($data as $k => $v) {

						$v->text = Text::_($v->text);
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
	 * 3) payment method contact server to change status (payment method does not identify as user - $user = Factory::getUser())
	 *
	 * $notifyUser 0 ... no  1 ... yes 99 ... defined in order status settings
	 * $notifyOthers   0 ... no  1 ... yes 99 ... defined in order status settings
	 * $emailSend  0 ... no  1 ... order, 2 ... invoice, 3 ... delivery_note,  99 ... defined in order status settings
	 * $emailSend  0 ... html  1 ... pdf, 2 ... both,  99 ... defined in order status settings
	 * $stockMovements  = ... no  + ... plus - ... minus 99 ... defined in order status settings
	 * $emailSendGift 0 ... no 1 ... Buyer 2 ... Recipient 3 ... Both
	 */

	public static function changeStatus( $orderId, $statusId, $orderToken = '', $notifyUser = 99, $notifyOthers = 99, $emailSend = 99, $stockMovements = '99', $changeUserGroup = '99', $changePointsNeeded = '99', $changePointsReceived = '99', $emailSendFormat = '99') {





		// ORDER INFO
		$pos		= PhocacartPos::isPos();
		$order 		= new PhocacartOrderView();
		$common		= $order->getItemCommon($orderId);
		$orderNumber= PhocacartOrder::getOrderNumber($orderId, $common->date, $common->order_number);
		$bas		= $order->getItemBaS($orderId, 1);
		//$totalBrutto= $order->getItemTotal($orderId, 0, 'brutto');
		$status 	= self::getStatus($statusId);

		$config		= Factory::getConfig();

		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		//$invoice_prefix		= $paramsC->get('invoice_prefix', '');
		$email_send_format	= isset($status['email_send_format']) ? $status['email_send_format'] : 0;

		// FIND THE RIGHT VALUES FOR VARIBALES - different if we are in frontend or backend
		$notifyUserV 	= false;
		$notifyOthersV	= false;
		$emailSendV		= false;
		//$emailSendGiftV = false;
		$stockMovementsV= '';


		// 1) NOTIFY USER
		if ($notifyUser == 0) {
			$notifyUserV = false;
		} else if ($notifyUser == 1) {
			$notifyUserV = true;
		} else if ($notifyUser == 99) {
			if (isset($status['email_customer']) && (int)$status['email_customer'] > 0) {

				if ((int)$status['email_customer'] == 1) {
					$notifyUserV = true;
				} else if ((int)$status['email_customer'] == 2 && !$pos) {
					$notifyUserV = true;// Don't send email from POS if the send email parameter is set to: Yes (excluding POS)
				}

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

		// 3) EMAIL SEND
		if ($emailSendFormat == 0) {
			$emailSendFormatV = 0;
		} else if ($emailSendFormat == 1) {
			$emailSendFormatV = 1;
		} else if ($emailSendFormat == 2) {
			$emailSendFormatV = 2;
		} else if ($emailSendFormat == 99) {
			if (isset($status['email_send_format']) && $status['email_send_format'] == 0) {
				$emailSendFormatV = 0;
			} else if (isset($status['email_send_format']) && $status['email_send_format'] == 1) {
				$emailSendFormatV = 1;
			} else if (isset($status['email_send_format']) && $status['email_send_format'] == 2) {
				$emailSendFormatV = 2;
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

	/*	Email send gift voucher works together with email send gift voucher body or subject
	    and it is possible that such objects will not exist in other statuses, so don't set gift voucher
	    manually when there is no certainty, emails are ready.

		// 8) Email Send Gift
		if ($emailSendGift == 0) {
			$emailSendGiftV = 0;
		} else if ($emailSendGift == 1) {
			$emailSendGiftV = 1;
		} else if ($emailSendGift == 2) {
			$emailSendGiftV = 2;
		} else if ($emailSendGift == 3) {
			$emailSendGiftV = 3;
		} else if ($emailSendGift == 99) {
			if (isset($status['email_gift']) && $status['email_gift'] == 0) {
				$emailSendGiftV = 0;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 1) {
				$emailSendGiftV = 1;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 2) {
				$emailSendGiftV = 2;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 3) {
				$emailSendGiftV = 3;
			}
		}

		// 9) Email Send Gift Format
		if ($emailSendGiftFormat == 0) {
			$emailSendGiftFormatV = 0;
		} else if ($emailSendGiftFormat == 1) {
			$emailSendGiftFormatV = 1;
		} else if ($emailSendGiftFormat == 2) {
			$emailSendGiftFormatV = 2;
		} else if ($emailSendGiftFormat == 3) {
			$emailSendGiftFormatV = 3;
		} else if ($emailSendGiftFormat == 99) {
			if (isset($status['email_gift']) && $status['email_gift'] == 0) {
				$emailSendGiftFormatV = 0;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 1) {
				$emailSendGiftFormatV = 1;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 2) {
				$emailSendGiftFormatV = 2;
			} else if (isset($status['email_gift']) && $status['email_gift'] == 3) {
				$emailSendGiftFormatV = 3;
			}
		}
		*/



		// EMAIL
		$recipient 					= '';// Customer/Buyer
		$recipientOthers			= '';// others
		$buyerEmail 		= '';// Customer/Buyer who should get GIFT VOUCHER per new email
		$recipientsEmails 	= array();// Recipients who should get GIFT VOUCHER per new email (in case byer buys gift voucher and send it directly to recipient)
		$bcc 						= '';
		$subject 					= '';
		$body 						= '';



		if ($notifyUserV) {

			$canSend = self::canSendEmail($orderToken, $common);

			// Payment method returns status
			if ($canSend == 0) {
				PhocacartLog::add(2, 'Order Status - Notify - ERROR', (int)$orderId, Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND'));

				// Don't die here because even if we cannot send email to customer we can send email to others
				// $recipient == '' so no email will be sent to recipient
				//die (Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND'));
			} else {
				$recipient = self::getRecipient($bas);
			}
		}

		if ($notifyOthersV) {
			if (isset($status['email_others']) && $status['email_others'] != '') {
				$bcc = explode(',', $status['email_others'] );
				//if ($recipient == '') {
					if (isset($bcc[0]) && MailHelper::isEmailAddress($bcc[0])) {
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
					} else if ((int)$v->stock_calculation == 2 || (int)$v->stock_calculation == 3) {

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
			$db			= Factory::getDBO();

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
			$db			= Factory::getDBO();
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

		// PDF Feature
		$pdfV                  = self::handlePDFExtensions($orderId);
		$attachmentContent     = '';
		$attachmentName        = '';



		// ------------------------
		// BUILD EMAIL for customer or others
		// ------------------------

		// Set language of order for the customer
		$pLang = new PhocacartLanguage();


		if (($recipient != '' && MailHelper::isEmailAddress($recipient)) || ($recipientOthers != '' && MailHelper::isEmailAddress($recipientOthers))) {

			$sitename = $config->get('sitename');


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
			$r = PhocacartText::prepareReplaceText($order, $orderId, $common, $bas, $status);



			$r['email']        = $recipient;// Overwrites the $r
			$r['email_others'] = $recipientOthers;
			$r['name_others']  = '';


			// EMAIL CUSTOMER
			if ($status['email_subject'] != '') {

				$emailSubject = PhocacartText::completeText($status['email_subject'], $r, 1);
				$subject      = $emailSubject;// .' ' . Text::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			} else if ($status['title'] != '') {

				$subject = $sitename . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $r['ordernumber'];
			}
			// EMAIL OTHERS
			if ($status['email_subject_others'] != '') {
				$emailSubjectO = PhocacartText::completeText($status['email_subject_others'], $r, 2);
				$subjectOthers = $emailSubjectO;// .' ' . Text::_('COM_PHOCACART_ORDER_NR'). ': '.$r['ordernumber'];
			} else if ($status['title'] != '') {
				$subjectOthers = $sitename . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $r['ordernumber'];
			}


			if (!isset($bas['b'])) {
				$bas['b'] = array();
			}
			if (!isset($bas['s'])) {
				$bas['s'] = array();
			}


			// COMPLETE BODY
			$body       = PhocacartText::completeText($body, $r, 1);
			$bodyOthers = PhocacartText::completeText($bodyOthers, $r, 2);

			//$body 			= PhocacartText::completeTextFormFields($body, $bas['b'], 1);
			//$bodyOthers 	= PhocacartText::completeTextFormFields($bodyOthers, $bas['b'], 1);

			//$body 			= PhocacartText::completeTextFormFields($body, $bas['s'], 2);
			//$bodyOthers 	= PhocacartText::completeTextFormFields($bodyOthers, $bas['s'], 2);
			$body       = PhocacartText::completeTextFormFields($body, $bas['b'], $bas['s']);
			$bodyOthers = PhocacartText::completeTextFormFields($bodyOthers, $bas['b'], $bas['s']);



			// All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
			// which language version the customer got
			$pLang->setLanguage($common->user_lang);


			switch ($emailSendV) {
				case 1:

					$orderRender = new PhocacartOrderRender();

					if ($emailSendFormatV == 0 || $emailSendFormatV == 2) {
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 1, 'mail', $orderToken);

						$bodyOthers .= "<br><br>";
						$bodyOthers .= $orderRender->render($orderId, 1, 'mail', $orderToken);
					}

					if ($pdfV['pdf'] == 1 && ($emailSendFormatV == 1 || $emailSendFormatV == 2)) {
						$staticData = array();
						//$orderNumber				= PhocacartOrder::getOrderNumber($orderId, $common->date);
						$orderNumber            = PhocacartOrder::getOrderNumber($orderId, $common->date, $common->order_number);
						$staticData['option']   = 'com_phocacart';
						$staticData['title']    = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber;
						$staticData['file']     = '';// Must be empty to not save the pdf to server
						$staticData['filename'] = strip_tags(Text::_('COM_PHOCACART_ORDER') . '_' . $orderNumber) . '.pdf';
						$staticData['subject']  = '';
						$staticData['keywords'] = '';

						$staticData['output']   = $orderRender->render($orderId, 1, 'pdf', $orderToken);
						$staticData['pdf_destination'] = 'S';

						$attachmentContent      = PhocaPDFRender::renderPDF('', $staticData);
						$attachmentName         = $staticData['filename'];

					}

				break;
				case 2:

					$orderRender = new PhocacartOrderRender();

					$invoiceNumber = PhocacartOrder::getInvoiceNumber($orderId, $common->date, $common->invoice_number);


					// If invoice is not created yet, it cannot be sent
					if ($invoiceNumber == '') {
						PhocacartLog::add(3, 'Status changed - sending email: The invoice should have been attached to the email, but it doesn not exist yet. Check order status settings and billing settings.', $orderId, 'Order ID: ' . $orderId . ', Status ID: ' . $statusId);
					} else {
						if ($emailSendFormatV == 0 || $emailSendFormatV == 2) {
							$body .= "<br><br>";
							$body .= $orderRender->render($orderId, 2, 'mail', $orderToken);

							$bodyOthers .= "<br><br>";
							$bodyOthers .= $orderRender->render($orderId, 2, 'mail', $orderToken);
						}

						if ($pdfV['pdf'] == 1 && ($emailSendFormatV == 1 || $emailSendFormatV == 2)) {
							$staticData = array();

							$staticData['option']   = 'com_phocacart';
							$staticData['title']    = Text::_('COM_PHOCACART_INVOICE_NR') . ': ' . $invoiceNumber;
							$staticData['file']     = '';// Must be empty to not save the pdf to server
							$staticData['filename'] = strip_tags(Text::_('COM_PHOCACART_INVOICE') . '_' . $invoiceNumber) . '.pdf';
							$staticData['subject']  = '';
							$staticData['keywords'] = '';
							$staticData['output']   = $orderRender->render($orderId, 2, 'pdf', $orderToken);
							$staticData['pdf_destination'] = 'S';
							$attachmentContent      = PhocaPDFRender::renderPDF('', $staticData);
							$attachmentName         = $staticData['filename'];

						}
					}

				break;
				case 3:
					$orderRender = new PhocacartOrderRender();

					if ($emailSendFormatV == 0 || $emailSendFormatV == 2) {
						$body .= "<br><br>";
						$body .= $orderRender->render($orderId, 3, 'mail', $orderToken);

						$bodyOthers .= "<br><br>";
						$bodyOthers .= $orderRender->render($orderId, 3, 'mail', $orderToken);
					}

					if ($pdfV['pdf'] == 1 && ($emailSendFormatV == 1 || $emailSendFormatV == 2)) {
						$staticData = array();
						//$orderNumber				= PhocacartOrder::getOrderNumber($orderId);
						$orderNumber            = PhocacartOrder::getOrderNumber($orderId, $common->date, $common->order_number);
						$staticData['option']   = 'com_phocacart';
						$staticData['title']    = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber;
						$staticData['file']     = '';// Must be empty to not save the pdf to server
						$staticData['filename'] = strip_tags(Text::_('COM_PHOCACART_ORDER') . '_' . $orderNumber) . '.pdf';
						$staticData['subject']  = '';
						$staticData['keywords'] = '';
						$staticData['output']   = $orderRender->render($orderId, 3, 'pdf', $orderToken);
						$staticData['pdf_destination'] = 'S';
						$attachmentContent      = PhocaPDFRender::renderPDF('', $staticData);
						$attachmentName         = $staticData['filename'];
					}


				break;

			}

			// Email Footer
			$body .= '<br><br>' . PhocacartText::completeText($status['email_footer'], $r, 1);

			$pLang->setLanguageBack();


			// CUSTOMER
			self::handleLangPlugin($pLang, $common, $subject);
			self::handleLangPlugin($pLang, $common, $body);

			// OTHERS
			self::handleLangPluginOthers($subjectOthers);
			self::handleLangPluginOthers($bodyOthers);

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
				$body = Text::_('COM_PHOCACART_ORDER_NR'). ': '.$orderNumber .' - '. Text::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': '.$status['title'];
			}*/

			$notify = 0;
			// ---------
			// CUSTOMERS
			if ($recipient != '' && MailHelper::isEmailAddress($recipient)) {
				if ($emptyBody == 1) {
					$body = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . Text::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': ' . $status['title'] . '<br>' . $body;
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

						$attachment     = array();
						$pathAttachment = PhocacartPath::getPath('attachmentfile');

						foreach ($attachmentA as $k => $v) {
							if (isset($v['file_attachment']) && $v['file_attachment'] != '') {

								$pathAttachmentFile = $pathAttachment['orig_abs_ds'] . $v['file_attachment'];

								if (Joomla\CMS\Filesystem\File::exists($pathAttachmentFile)) {
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
			if ($recipientOthers != '' && MailHelper::isEmailAddress($recipientOthers)) {
				if ($emptyBodyOthers == 1) {
					$bodyOthers = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . Text::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO') . ': ' . $status['title'] . '<br>' . $bodyOthers;
				}

				$attachment   = null;
				$notifyOthers = PhocacartEmail::sendEmail('', '', $recipientOthers, $subjectOthers, $bodyOthers, true, null, $bcc, $attachment, $attachmentContent, $attachmentName);


			}


		}


		// ------------------------
		// BUILD EMAIL for GIFT buyer and GIFT recipients
		// ------------------------

		$layoutG	= new FileLayout('gift_voucher', null, array('component' => 'com_phocacart', 'client' => 0));

		$bodyRecipient 			= array();// body for all recipients - each recipient has own body
		$attachmentRecipient 	= array();// attachment for all recipients - each recipient has own attachment (for example PDF with generaded coupons)
		$buyerBody 				= ''; // buyer of gift coupons has another body
		$attachmentBuyer 		= ''; // buyer of gift coupons gets all coupons - not like recipients - recipients only get own coupons

		// Set language of order for the customer
		$pLang = new PhocacartLanguage();
		$price = new PhocacartPrice();

		$gifts = array();
		$activateGifts = array();

		if ((int)$status['email_gift'] > 0 || (isset($status['activate_gift']) && $status['activate_gift'] == 1)) {

			// Get all Gifts stored for this order
			if (isset($common->id) && (int)$common->id > 0) {
				$gifts = PhocacartCoupon::getGiftsByOrderId($common->id);

				// Do we have activate the gift coupons?
				if ($status['activate_gift'] == 1) {
					PhocaCartCoupon::activateAllGiftsByOrderId($common->id);
				}

				foreach($gifts as $k => $v) {

					// 2) Do we have some recipients?
					// One order can include more gifts
					// And one order can include more recipients - e.g. two gifts for different users will be bought in one order
					if (($status['email_gift'] == 2 || $status['email_gift'] == 3) && isset($v['gift_recipient_email']) && MailHelper::isEmailAddress($v['gift_recipient_email'])){
						$recipientUnique = $v['gift_recipient_email'];
						$recipientsEmails[$recipientUnique]	= $recipientUnique;
					}
				}
			}
		}

		// Can we send the email to buyer email
		if (!empty($gifts) && ($status['email_gift'] == 1 || $status['email_gift'] == 3)) {

			$canSend = self::canSendEmail($orderToken, $common);

			// Payment method returns status
			if ($canSend == 0) {
				PhocacartLog::add(2, 'Order Status - Notify - ERROR', (int)$orderId, Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND') . ' ' . Text::_('COM_PHOCACART_GIFT_VOUCHER') );
			} else {
				$buyerEmail = self::getRecipient($bas);
				if (!MailHelper::isEmailAddress($buyerEmail)) {
					$buyerEmail = '';
				}
			}

		}

		$giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHER');

		// Build email or paste gift vouchers - do them when at least one should get the email with gift voucher
		if (!empty($gifts) && ($buyerEmail != ''|| !empty($recipientsEmails))) {

			// Part for buyer only
			if ($buyerEmail != '') {
				$sitename       = $config->get('sitename');
				$buyerEmptyBody = 0;
				if ($status['email_text_gift_sender'] == '') {
					$buyerEmptyBody = 1;
				}
				$buyerBody = $status['email_text_gift_sender'];

				$r          = PhocacartText::prepareReplaceText($order, $orderId, $common, $bas, $status);
				$r['email'] = $buyerEmail;// Overwrites the $r


				if (count($gifts) > 1) {
					$giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHERS');
				}

				if ($status['email_subject_gift_sender'] != '') {
					$buyerSubject = PhocacartText::completeText($status['email_subject_gift_sender'], $r, 1);
				} else if ($status['title'] != '') {
					$buyerSubject = $sitename . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $r['ordernumber'] . ' - ' . $giftVoucherText;
				}

				if (!isset($bas['b'])) {
					$bas['b'] = array();
				}
				if (!isset($bas['s'])) {
					$bas['s'] = array();
				}

				$buyerBody = PhocacartText::completeText($buyerBody, $r, 1);
				$buyerBody = PhocacartText::completeTextFormFields($buyerBody, $bas['b'], $bas['s']);

				// All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
				// which language version the customer got
				$pLang->setLanguage($common->user_lang);
			}

			// Prepare PDF
			if ($pdfV['pdf'] == 1 && ($status['email_gift_format'] == 1 || $status['email_gift_format'] == 2)) {

				$staticData = array();
				//$orderNumber				= PhocacartOrder::getOrderNumber($orderId, $common->date);
				$orderNumber            = PhocacartOrder::getOrderNumber($orderId, $common->date, $common->order_number);
				$staticData['option']   = 'com_phocacart';
				$staticData['title']    = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText;
				$staticData['file']     = '';// Must be empty to not save the pdf to server
				$staticData['filename'] = strip_tags($giftVoucherText . '_' . $orderNumber) . '.pdf';
				$staticData['subject']  = '';
				$staticData['keywords'] = '';
				$staticData['output']   = '';


				// Initialize PDF for buyer which gets all the coupons
				// we need to initilaize PDF here because we need tcpdf classed in template output
				$pdf      = new stdClass();
				$content  = new stdClass();
				$document = new stdClass();
				PhocaPDFRender::initializePDF($pdf, $content, $document, $staticData);

			}


			// Start to prepare gift vouchers (HTML or PDF) based on recipients
			foreach ($gifts as $k => $v) {


				$recipientUnique = $v['gift_recipient_email'];

				// Gift voucher rendered to mail: create body for buyer but even for all gift voucher recipients
				if ($status['email_gift_format'] == 0 || $status['email_gift_format'] == 2) {

					$d               = $v;
					$d['typeview']   = 'Order';
					$d['product_id'] = $v['gift_product_id'];

					$d['discount']   = $price->getPriceFormat($v['discount']);
					$d['valid_from'] = HTMLHelper::date($v['valid_from'], Text::_('DATE_FORMAT_LC3'));
					$d['valid_to']   = HTMLHelper::date($v['valid_to'], Text::_('DATE_FORMAT_LC3'));
					$d['format']     = 'mail';

					$layputOutput = $layoutG->render($d);

					// Render each coupon to buyer body
					$buyerBody .= $layputOutput;;
					$buyerBody .= '<div>&nbsp;</div>';

					// Render each coupon to each recipient body
					if (!isset($bodyRecipient[$recipientUnique])) {
						$bodyRecipient[$recipientUnique]                     = array();// Each recipient will have own body
						$bodyRecipient[$recipientUnique]['body_initialized'] = true;
						$bodyRecipient[$recipientUnique]['data'] 			 = $v;
						$bodyRecipient[$recipientUnique]['output']           = '';
					}

					if (isset($bodyRecipient[$recipientUnique]['body_initialized']) && $bodyRecipient[$recipientUnique]['body_initialized']) {
						$bodyRecipient[$recipientUnique]['output'] .= $layputOutput;
						$bodyRecipient[$recipientUnique]['output'] .= '<div>&nbsp;</div>';
					}
				} else {
					// We don't send the voucher in email body but e.g. only as PDF so we still need to initiate mail body for recipient
					if (!isset($bodyRecipient[$recipientUnique])) {
						$bodyRecipient[$recipientUnique]                     = array();// Each recipient will have own body
						$bodyRecipient[$recipientUnique]['body_initialized'] = true;
						$bodyRecipient[$recipientUnique]['data'] 			 = $v;
						$bodyRecipient[$recipientUnique]['output']           = '';
					}
				}

				if ($pdfV['pdf'] == 1 && ($status['email_gift_format'] == 1 || $status['email_gift_format'] == 2)) {

					$d               = $v;
					$d['typeview']   = 'Order';
					$d['product_id'] = $v['gift_product_id'];

					$d['discount']   = $price->getPriceFormat($v['discount']);
					$d['valid_from'] = HTMLHelper::date($v['valid_from'], Text::_('DATE_FORMAT_LC3'));
					$d['valid_to']   = HTMLHelper::date($v['valid_to'], Text::_('DATE_FORMAT_LC3'));
					$d['format']     = 'pdf';

					// Render each coupon to buyer PDF
					$d['pdf_instance'] = $pdf;// we need tcpdf instance in output to use different tcpdf functions
					$attachmentBuyer   .= $layoutG->render($d);


					// Because of token in tcpdf, each recipient needs own tcpdf instance
					// Initialize PDF for each recipient
					// we need to initilaize PDF here because we need tcpdf classed in template output
					if (!isset($attachmentRecipient[$recipientUnique]['pdf_initialized'])) {
						$attachmentRecipient[$recipientUnique]                    = array();
						$attachmentRecipient[$recipientUnique]['pdf_initialized'] = true;
						$attachmentRecipient[$recipientUnique]['pdf']             = new stdClass();
						$attachmentRecipient[$recipientUnique]['content']         = new stdClass();
						$attachmentRecipient[$recipientUnique]['document']        = new stdClass();
						$attachmentRecipient[$recipientUnique]['count']           = 0;
						$attachmentRecipient[$recipientUnique]['output']          = '';
						PhocaPDFRender::initializePDF($attachmentRecipient[$recipientUnique]['pdf'], $attachmentRecipient[$recipientUnique]['content'], $attachmentRecipient[$recipientUnique]['document'], $staticData);
					}

					if (isset($attachmentRecipient[$recipientUnique]['pdf_initialized']) && $attachmentRecipient[$recipientUnique]['pdf_initialized']) {
						$d['pdf_instance']                               = $attachmentRecipient[$recipientUnique]['pdf'];// we need tcpdf instance in output to use different tcpdf functions
						$attachmentRecipient[$recipientUnique]['output'] .= $layoutG->render($d);
						$attachmentRecipient[$recipientUnique]['count']++;
					}
				}
			}

			// Send mail to buyer
			if ($buyerEmail != '' && $attachmentBuyer != '') {


				$staticData['pdf_destination'] = 'S';
				$staticData['output']          = $attachmentBuyer;
				$buyerAttachmentContent        = PhocaPDFRender::renderInitializedPDF($pdf, $content, $document, $staticData);
				$buyerAttachmentName           = $staticData['filename'];

				$pLang->setLanguageBack();

				// CUSTOMER
				self::handleLangPlugin($pLang, $common, $buyerSubject);
				self::handleLangPlugin($pLang, $common, $buyerBody);

				if ($buyerEmptyBody == 1) {
					$buyerBody = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText . '<br>' . $buyerBody;
				}

				$attachment = null;
				$notifyGift = PhocacartEmail::sendEmail('', '', $buyerEmail, $buyerSubject, $buyerBody, true, null, null, $attachment, $buyerAttachmentContent, $buyerAttachmentName);

				if ($notifyGift) {
				} else {
					PhocacartLog::add(2, 'Order Status - Notify - ERROR - Gift voucher not sent', (int)$orderId, 'Email with gift voucher not sent to buyer (' . $buyerEmail . ')');
				}
			}


			// Send mail to all recipients


			if (!empty($recipientsEmails)) {
				foreach ($recipientsEmails as $k => $v) {

					if (isset($bodyRecipient[$k]['output']) /*&& $bodyRecipient[$k]['output'] != '' - body (built by voucher) can be empty if we set the voucher in PDF only*/) {

						$sitename           = $config->get('sitename');
						$recipientEmptyBody = 0;
						if ($status['email_text_gift_recipient'] == '') {
							$recipientEmptyBody = 1;
						}
						$recipientBody = $status['email_text_gift_recipient'];

						$r                         = PhocacartText::prepareReplaceText($order, $orderId, $common, $bas, $status);
						$r['email_gift_recipient'] = $v;// Overwrites the $r
						$r['name_gift_recipient'] 	= isset($bodyRecipient[$k]['data']['gift_recipient_name']) ? $bodyRecipient[$k]['data']['gift_recipient_name'] : '';
						$r['name_gift_sender'] 		= isset($bodyRecipient[$k]['data']['gift_sender_name']) ? $bodyRecipient[$k]['data']['gift_sender_name'] : '';
						$r['valid_to_gift'] 		= isset($bodyRecipient[$k]['data']['valid_to']) ? HTMLHelper::date($bodyRecipient[$k]['data']['valid_to'], Text::_('DATE_FORMAT_LC1')) : '';


						if (isset($attachmentRecipient[$k]['count']) && (int)$attachmentRecipient[$k]['count'] > 1) {
							$giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHERS');
						}

						if ($status['email_subject_gift_recipient'] != '') {
							$recipientSubject = PhocacartText::completeText($status['email_subject_gift_recipient'], $r, 3);
						} else if ($status['title'] != '') {
							$recipientSubject = $sitename . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $r['ordernumber'] . ' - ' . $giftVoucherText;
						}

						if (!isset($bas['b'])) {
							$bas['b'] = array();
						}
						if (!isset($bas['s'])) {
							$bas['s'] = array();
						}

						if (isset($bodyRecipient[$k]['output']) /*&& $bodyRecipient[$k]['output'] != ''*/) {
							$recipeintBody = $recipientBody . $bodyRecipient[$k]['output'];
							$recipientBody = PhocacartText::completeText($recipientBody, $r, 3);
							$recipientBody = PhocacartText::completeTextFormFields($recipientBody, $bas['b'], $bas['s']);
						}


						$recipientAttachmentContent = '';
						$recipientAttachmentName    = '';

						if (isset($attachmentRecipient[$k]['output']) && $attachmentRecipient[$k]['output'] != '' && isset($attachmentRecipient[$k]['pdf']) && $attachmentRecipient[$k]['content'] && $attachmentRecipient[$k]['document']) {

							// Initialize new PDF for each recipient
							$pdf                  = new stdClass();
							$content              = new stdClass();
							$document             = new stdClass();
							$statidData['output'] = '';
							PhocaPDFRender::initializePDF($pdf, $content, $document, $staticData);

							$staticData['pdf_destination'] = 'S';
							$staticData['output']          = $attachmentRecipient[$k]['output'];
							$recipientAttachmentContent    = PhocaPDFRender::renderInitializedPDF($attachmentRecipient[$k]['pdf'], $attachmentRecipient[$k]['content'], $attachmentRecipient[$k]['document'], $staticData);
							$recipientAttachmentName       = $staticData['filename'];
						}

						$pLang->setLanguageBack();

						// CUSTOMER
						self::handleLangPlugin($pLang, $common, $recipientSubject);
						self::handleLangPlugin($pLang, $common, $recipientBody);

						if ($recipientEmptyBody == 1) {
							$recipientBody = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText . '<br>' . $recipientBody;
						}


						$attachment = null;
						$notifyGift = PhocacartEmail::sendEmail('', '', $v, $recipientSubject, $recipientBody, true, null, null, $attachment, $recipientAttachmentContent, $recipientAttachmentName);

						if ($notifyGift) {
						} else {
							PhocacartLog::add(2, 'Order Status - Notify - ERROR - Gift voucher not sent', (int)$orderId, 'Email with gift voucher not sent to recipient (' . $v . ')');
						}
					}
				}
			}
		}

		// --------------------------------
		// BACK TO MAIN NOTIFY FUNCTION
		// --------------------------------

		if (($recipient != '' && MailHelper::isEmailAddress($recipient)) || ($recipientOthers != '' && MailHelper::isEmailAddress($recipientOthers))) {
			// Notify is based only on customer email
			if ($recipient != '' && MailHelper::isEmailAddress($recipient)) {
				if ($notify) {
					if ($app->isClient('administrator')){
						$app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_IF_NO_ERROR_EMAIL_SENT'));
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
		$db 		= Factory::getDBO();
		$query = ' UPDATE #__phocacart_orders SET status_id = '.(int)$statusId
					.' WHERE id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();

		// Set invoice data in case status can set invoice ID
		PhocacartOrder::storeOrderReceiptInvoiceId((int)$orderId, false, (int)$statusId, array('I'));

		return true;

	}

	public static function setHistory($id, $statusId, $notify, $comment) {

		$db 		= Factory::getDBO();
		//$user 		= PhocacartUser::getUser();
		$user		= Factory::getUser();// Logged in user, does not matter if customer|vendor|admin
		$userId		= 0;

		if (isset($user->id) && (int)$user->id > 0) {
			$userId = (int)$user->id;
		}
		$date = Factory::getDate()->toSql();

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
			0 => Text::_('COM_PHOCACART_NOTHING'),
			1 => Text::_('COM_PHOCACART_ORDER'),
			2 => Text::_('COM_PHOCACART_INVOICE'),
			3 => Text::_('COM_PHOCACART_DELIVERY_NOTE')
		);

		return HTMLHelper::_('select.genericlist',  $data,  'jform[email_send]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

/*
	public static function getEmailSendGiftSelectBox($value) {
		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			0 => Text::_('COM_PHOCACART_NO_ONE'),
			1 => Text::_('COM_PHOCACART_YES_BUYER'),
			2 => Text::_('COM_PHOCACART_YES_RECIPIENT'),
			3 => Text::_('COM_PHOCACART_YES_BUYER_AND_RECIPIENT')
		);

		return HTMLHelper::_('select.genericlist',  $data,  'jform[email_gift]', 'class="form-control"', 'value', 'text', $value, $data[$value] );

	}
*/
	public static function getEmailSendFormatSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			0 => Text::_('COM_PHOCACART_HTML'),
			1 => Text::_('COM_PHOCACART_PDF'),
			2 => Text::_('COM_PHOCACART_BOTH')
		);

		return HTMLHelper::_('select.genericlist',  $data,  'jform[email_send_format]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

	public static function getStockMovementsSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'=' => Text::_('COM_PHOCACART_ITEMS_UNCHANGED'),
			'+' => Text::_('COM_PHOCACART_ITEMS_ADDED'),
			'-' => Text::_('COM_PHOCACART_ITEMS_SUBTRACTED')
		);

		if ($value == 0 || $value == '') {
			$value = '=';
		}

		return HTMLHelper::_('select.genericlist',  $data,  'jform[stock_movements]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangeUserGroupSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => Text::_('COM_PHOCACART_USER_GROUP_UNCHANGED'),
			'1' => Text::_('COM_PHOCACART_USER_GROUP_CHANGED')
		);

		if ($value == '') {
			$value = 0;
		}

		return HTMLHelper::_('select.genericlist',  $data,  'jform[change_user_group]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangeChangePointsNeededSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => Text::_('COM_PHOCACART_REWARD_POINTS_UNCHANGED'),
			'1' => Text::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_APPROVED'),
			'2' => Text::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_NOT_APPROVED')
		);

		if ($value == '') {
			$value = 0;
		}

		return HTMLHelper::_('select.genericlist',  $data,  'jform[change_points_needed]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

	public static function getChangePointsReceivedSelectBox($value) {

		// see: administrator/components/com_phocacart/models/forms/phocacartstatus.xml
		$data = array(
			'0' => Text::_('COM_PHOCACART_REWARD_POINTS_UNCHANGED'),
			'1' => Text::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_APPROVED'),
			'2' => Text::_('COM_PHOCACART_REWARD_POINTS_CHANGED_CHANGE_NOT_APPROVED')
		);

		if ($value == '') {
			$value = 0;
		}

		return HTMLHelper::_('select.genericlist',  $data,  'jform[change_points_received]', 'class="form-select"', 'value', 'text', $value, $data[$value] );
	}

	public static function getOrderStatuses() {

		$db 		= Factory::getDBO();
		$query = 'SELECT a.title AS text, a.id AS value'
				. ' FROM #__phocacart_order_statuses AS a'
				. ' WHERE a.published = 1'
				. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		if (!empty($data)) {
			foreach ($data as $k => $v) {
				$v->text = Text::_($v->text);
			}
		}

		return $data;
	}

	public static function canSendEmail($orderToken, $common) {

		$app = Factory::getApplication();

		if (!$app->isClient('administrator')) {

			// Frontend
			// Check if we can send email to customer
			$canSend = 0;
			$user    = PhocacartUser::getUser();
			$guest   = PhocacartUserGuestuser::getGuestUser();

			// $orderToken is set in case we will not check the user:
			// - in case of guest users
			// - in case of payment method server contacts the server to change the status
			if ($orderToken != '' && $orderToken == $common->order_token && $guest) {
				$canSend = 1;// User is guest - not logged in user run this script
				//PhocacartLog::add(4, 'CHECK', (int)$orderId, 'Guest User');
			} else if ($orderToken != '' && $orderToken == $common->order_token) {
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

		return $canSend;
	}

	public static function getRecipient($bas) {

		$recipient = '';
		if (isset($bas['b']['email_contact']) && $bas['b']['email_contact'] != '' && MailHelper::isEmailAddress($bas['b']['email_contact'])) {
			$recipient = $bas['b']['email_contact'];
		} else if (isset($bas['b']['email']) && $bas['b']['email'] != '' && MailHelper::isEmailAddress($bas['b']['email'])) {
			$recipient = $bas['b']['email'];
		} else if (isset($bas['s']['email_contact']) && $bas['s']['email_contact'] != '' && MailHelper::isEmailAddress($bas['s']['email_contact'])) {
			$recipient = $bas['s']['email_contact'];
		} else if (isset($bas['s']['email']) && $bas['s']['email'] != '' && MailHelper::isEmailAddress($bas['s']['email'])) {
			$recipient = $bas['s']['email'];
		}

		return $recipient;
	}

	public static function handlePDFExtensions($orderId) {

		$pdfV                  = array();
		$pdfV['plugin-pdf']    = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		$pdfV['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
		$pdfV['pdf']           = 0;


		if ($pdfV['plugin-pdf'] == 1 && $pdfV['component-pdf'] == 1) {
			if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php')) {
				require_once(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php');
			} else {
				PhocacartLog::add(2, 'Order Status - Notify - ERROR (PDF Class)', (int)$orderId, 'Render PDF file could not be found in system');
				throw new Exception('Error - Phoca PDF Helper - Render PDF file could not be found in system', 500);
				return false;
			}
			$pdfV['pdf'] = 1;
		}

		return $pdfV;
	}


	public static function handleLangPlugin($pLang, $common, &$object) {

		PluginHelper::importPlugin('system');
		PluginHelper::importPlugin('plgSystemMultilanguagesck');

		// CUSTOMER
		if (isset($common->user_lang) && $common->user_lang != '' && $common->user_lang != '*') {
			$pLang->setLanguage($common->user_lang);

            if ($object) {
                // Run content plugins e.g. because of translation
                // Disable emailclock for PDF | MAIL
                //if ($d['format'] == 'pdf' || $d['format'] == 'mail') {
                $object = '{emailcloak=off}' . $object;
                //}
                $object = HTMLHelper::_('content.prepare', $object);
            }

			Dispatcher::dispatchChangeText($object);

			// Set language back to default
			$pLang->setLanguageBack();

		} else {
            if ($object) {
                // Run content plugins e.g. because of translation
                // Disable emailclock for PDF | MAIL
                //if ($d['format'] == 'pdf' || $d['format'] == 'mail') {
                $object = '{emailcloak=off}' . $object;
                //}
                $object = HTMLHelper::_('content.prepare', $object);
            }

			Dispatcher::dispatchChangeText($object);
		}
	}

	public static function handleLangPluginOthers(&$object) {
		PluginHelper::importPlugin( 'system' );
		PluginHelper::importPlugin('plgSystemMultilanguagesck');

        if ($object) {
            // Run content plugins e.g. because of translation
            // Disable emailclock for PDF | MAIL
            //if ($d['format'] == 'pdf' || $d['format'] == 'mail') {
            $object = '{emailcloak=off}' . $object;
            //}
            $object = HTMLHelper::_('content.prepare', $object);
        }

		Dispatcher::dispatchChangeText($object);
	}
}
