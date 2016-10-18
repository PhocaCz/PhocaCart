<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartOrderView extends JViewLegacy
{
	
	protected $t;
	
	public function display($tpl = null) {
		
		$app			= JFactory::getApplication();
		$this->t		= PhocaCartUtils::setVars('orderview');
		$id				= $app->input->get('id', 0, 'int');
		$type			= $app->input->get('type', 0, 'int');
		$format			= $app->input->get('format', '', 'string');


		$paramsC 		= JComponentHelper::getParams('com_phocacart');
		$invoice_prefix	= $paramsC->get( 'invoice_prefix', '');
		
		$order	= new PhocaCartOrderRender();
		$o = $order->render($id, $type, $format);
		
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
		
		// Set title here, if customized in pdf plugin parameters, it overwrites this title - this is only default title
		$this->document->setTitle($title);
	
		echo $o;

		
		
		//JHTML::stylesheet( $this->t['s'] );

		//parent::display($tpl);	
	}
	
}
?>
