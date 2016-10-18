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

class PhocaCartViewOrder extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	public function display($tpl = null) {
	
		$app				= JFactory::getApplication();
		$this->p 			= $app->getParams();
		$id					= $app->input->get('id', 0, 'int');
		$type				= $app->input->get('type', 0, 'int');
		$format				= $app->input->get('format', '', 'string');
		$token				= $app->input->get('o', '', 'string');

		$invoice_prefix		= $this->p->get( 'invoice_prefix', '');		
		$orderGuestAccess	= $this->p->get( 'order_guest_access', 0 );
		
		if ($orderGuestAccess == 0) {
			$token = '';
		}
		$order	= new PhocaCartOrderRender();
		$o = $order->render($id, $type, $format, $token);
		
		//$media = new PhocaCartRenderMedia();
		
		switch($type) {
			case 2:
				$invoiceNumber	= PhocaCartOrder::getInvoiceNumber($id, $invoice_prefix);
				$title			= JText::_('COM_PHOCACART_INVOICE_NR'). ': '. $invoiceNumber;
			break;
			case 1:
			case 3:
			default:
				$orderNumber	= PhocaCartOrder::getOrderNumber($id);
				$title			= JText::_('COM_PHOCACART_ORDER_NR'). ': '. $orderNumber;
			break;
		}
		
		$this->document->setTitle($title);
		
		echo $o;
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>