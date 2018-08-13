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
			die (JText::_('COM_PHOCACART_POS_IS_DISABLED'));
		}
	
		if ($id < 1) {
			return JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
		}
		
		$d 					= array();
		$d['params'] 		= PhocacartUtils::getComponentParameters();
		$d['type']			= $type;
		$d['format']		= $format;
		
		$layout 			= new \Joomla\CMS\Layout\FileLayout('order', null, array('client' => 0));
		$defaultTemplate 	= PhocacartUtils::getDefaultTemplate();
		
		if ($defaultTemplate != '') {
			$layout->addIncludePath(JPATH_SITE . '/templates/'.$defaultTemplate.'/html/layouts/com_phocacart');
		}
		
		JHtml::stylesheet('media/com_phocacart/css/main.css' );
		
		$app 			= JFactory::getApplication();
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
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if (!isset($d['common']->vendor_id)) {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ($vendor->id != $d['common']->vendor_id) {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				
				
			} else {
				$user = PhocacartUser::getUser();
			
				if ((int)$user->id < 1 && $token == '') {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if (!isset($d['common']->user_id) && $token == '') {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ($user->id != $d['common']->user_id && $token == '') {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
				if ((int)$user->id < 1 && $token != '' && ($token != $d['common']->order_token)) {
					die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
				}
			}
			
		}
		
		$d['bas']		= $order->getItemBaS($id, 1);
		$d['products'] 	= $order->getItemProducts($id);
		$d['discounts']	= $order->getItemProductDiscounts($id, 0);
		$d['total'] 	= $order->getItemTotal($id, 1);
		
		// QR CODE IN PDF
		$d['qrcode']	= '';
		if ($type == 2 && $format == 'pdf') {
			$r				= PhocacartText::prepareReplaceText($order, $id, $d['common'], $d['bas']);
			$d['qrcode'] 	= PhocacartText::completeText($pdf_invoice_qr_code, $r, 1);
			
		
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