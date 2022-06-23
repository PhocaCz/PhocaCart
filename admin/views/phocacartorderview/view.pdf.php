<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartOrderView extends HtmlView
{

	protected $t;
	protected $r;

	public function display($tpl = null) {


		$app			= Factory::getApplication();
		$this->t		= PhocacartUtils::setVars('orderview');
		$this->r		= new PhocacartRenderAdminview();
		$id				= $app->input->get('id', 0, 'int');
		$type			= $app->input->get('type', 0, 'int');
		$format			= $app->input->get('format', '', 'string');

		$orderBillingData	= PhocacartOrder::getOrderBillingData($id);




		$paramsC 		= PhocacartUtils::getComponentParameters();
		//$invoice_prefix	= $paramsC->get( 'invoice_prefix', '');

		$order	= new PhocacartOrderRender();
		$o = $order->render($id, $type, $format);



		switch($type) {
			case 2:
				$invoiceNumber	= PhocacartOrder::getInvoiceNumber($id, $orderBillingData['date'], $orderBillingData['invoice_number']);

				$title			= Text::_('COM_PHOCACART_INVOICE_NR'). ': '. $invoiceNumber;
			break;
			case 1:
			case 3:
			default:
				$orderNumber	= PhocacartOrder::getOrderNumber($id, $orderBillingData['date'], $orderBillingData['order_number']);
				$title			= Text::_('COM_PHOCACART_ORDER_NR'). ': '. $orderNumber;
			break;
		}

		// Set title here, if customized in pdf plugin parameters, it overwrites this title - this is only default title
		$this->document->setTitle($title);

		echo $o;

		// PDF document name
		$this->document->setName($title);


		//$media = new PhocacartRenderAdminmedia();

		//parent::display($tpl);
	}

}
?>
