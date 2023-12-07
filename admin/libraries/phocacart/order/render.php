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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

class PhocacartOrderRender
{
	public function __construct() {}

	public function render($id, $type = 1, $format = 'html', $token = '', $pos = 0) {

		$paramsC 					= PhocacartUtils::getComponentParameters();
		$pdf_invoice_qr_code		= $paramsC->get( 'pdf_invoice_qr_code', '' );

		// If frontend user orders: user login needed or token
		// If frontend POS: vendor login needed

		// isPosView - we are directly in POS view
		// $pos - we are not in POS view but we ask order view from pos view

		if (($pos && !PhocacartPos::isPosEnabled()) && PhocacartPos::isPosView() && !PhocacartPos::isPosEnabled()) {
			die (Text::_('COM_PHOCACART_POS_IS_DISABLED'));
		}

		if ($id < 1) {
			return Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
		}

		$d 					= array();
		$d['params'] 		= PhocacartUtils::getComponentParameters();
		$d['type']			= $type;
		$d['format']		= $format;

		$layout 			= new FileLayout('order', null, array('client' => 0));
		$defaultTemplate 	= PhocacartUtils::getDefaultTemplate();

		if ($defaultTemplate != '') {
			$layout->addIncludePath(JPATH_SITE . '/templates/'.$defaultTemplate.'/html/layouts/com_phocacart');
		}

		$app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('com_phocacart.main', 'com_phocacart/main.css', array('version' => 'auto'));
		//HTMLHelper::stylesheet('media/com_phocacart/css/main.css' );

		$app 			= Factory::getApplication();
		$order			= new PhocacartOrderView();
		$d['common']	= $order->getItemCommon($id);
		$d['price'] 	= new PhocacartPrice();

		$d['price']->setCurrency($d['common']->currency_id);




		// Access rights actions ignored in administration
		if (!$app->isClient('administrator')){

			if ($pos || PhocacartPos::isPosView()) {
				$user	= $vendor = $ticket = $unit = $section = array();
				$dUser 	= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

				if ((int)$vendor->id < 1) {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Vendor not found (1)');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if (!isset($d['common']->vendor_id)) {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Vendor not found (2)');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ($vendor->id != $d['common']->vendor_id) {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Vendor doesn\'t match');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}


			} else {
				$user = PhocacartUser::getUser();


				// Frontend displaying rules
				// Frontend, view = orders view or view = order view
				// Order status can prevent from displaying some documents (can be set for each order status, parameter: orders_view_diplay
				// So this rules only applies to frontend orders or order view (and of course then all other rules are applied too, like user or token
				$app				= Factory::getApplication();
				$view				= $app->input->get('view', '', 'string');
				$option				= $app->input->get('option', '', 'string');

				$displayInOrderView = true;
				if ($option == 'com_phocacart' && ($view == 'orders' || $view == 'order')) {
					$displayDocument = json_decode($d['common']->ordersviewdisplay, true);
					if (!in_array($type, $displayDocument)) {
						$displayInOrderView = false;
					}
				}




				// We are in orders/order view and order status cannot display current document
				if ($displayInOrderView == false) {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Order status does not allow this document to be displayed in Order/Orders View');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}

				if ((int)$user->id < 1 && $token == '') {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'User not found (1)');

					// Add some debug here including debug_print_backtrace(); too see the flow which comes to this error - if from status or from view

					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if (!isset($d['common']->user_id) && $token == '') {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'User not found (2)');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ($user->id != $d['common']->user_id && $token == '') {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'User doesn\'t match');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ((int)$user->id < 1 && $token != '' && ($token != $d['common']->order_token)) {
					PhocacartLog::add(2, 'Render Order - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Token doesn\'t match');
					die (Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
			}

		}

		$d['bas']				= $order->getItemBaS($id, 1);
		$d['products'] 			= $order->getItemProducts($id);
		$d['discounts']			= $order->getItemProductDiscounts($id, 0);
		$d['total'] 			= $order->getItemTotal($id, 1);
		$d['taxrecapitulation'] = $order->getItemTaxRecapitulation($id);

		// Prepare variables for possible replace in different text parts, e.g.:
        // - QR code on invoice
        // - TOP, BOTTOM, MIDDLE Description
        // user per function: PhocacartText::completeText($pdf_invoice_qr_code, $d['preparereplace'], 1);
		$d['preparereplace']    = PhocacartText::prepareReplaceText($order, $id, $d['common'], $d['bas']);

		// QR CODE IN PDF
		$d['qrcode']	= '';
		// QR code can be rendered even outsite PDF
		$d['qrcode'] 	= PhocacartText::completeText($pdf_invoice_qr_code, $d['preparereplace'], 1);
		if ($type == 2 && $format == 'pdf') {
			//$d['qrcode'] 	= PhocacartText::completeText($pdf_invoice_qr_code, $d['preparereplace'], 1);
			/*if (isset($d['bas']['b'])) {
				$d['qrcode'] 	= PhocacartText::completeTextFormFields($d['qrcode'], $d['bas']['b'], 1);
			}
			if (isset($d['bas']['s'])) {
				$d['qrcode'] = PhocacartText::completeTextFormFields($d['qrcode'], $d['bas']['s'], 2);
			}*/

			if (!isset($d['bas']['b'])) {
				$d['bas']['b'] = array();
			}

			if (!isset($d['bas']['s'])) {
				$d['bas']['s'] = array();
			}

			if (isset($d['bas']['s'])) {
				$d['qrcode'] = PhocacartText::completeTextFormFields($d['qrcode'], $d['bas']['b'], $d['bas']['s']);
			}
		}
		//if ($type == 4 && $format == 'raw') {
			// POS RECEIPT IS MANAGED BY SPECIFIC RULES
			// array instead of output will be returned
			//return $layout->render($d);

		//} else {


			return $layout->render($d);
		//}


	}
}

?>
