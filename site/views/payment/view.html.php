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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

jimport( 'joomla.application.component.view');
class PhocaCartViewPayment extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;

	function display($tpl = null) {


		$document					= Factory::getDocument();
		$app						= Factory::getApplication();
		$uri 						= Uri::getInstance();
		$this->u					= PhocacartUser::getUser();
		$this->p					= $app->getParams();

		$session 					= Factory::getSession();
		$this->t['proceedpayment'] 	= $session->get('proceedpayment', array(), 'phocaCart');

		$order 			= new PhocacartOrderView();
		$payment		= new PhocacartPayment();

		$id				= 0;
		if (isset($this->t['proceedpayment']['orderid'])) {
			$id	= (int)$this->t['proceedpayment']['orderid'];
		}

		if ($id > 0) {

			$o['common']	= $order->getItemCommon($id);
			$o['bas']		= $order->getItemBaS($id, 1);
			$o['products'] 	= $order->getItemProducts($id);
			$o['total'] 	= $order->getItemTotal($id);


			$form = '';
			if (isset($o['common']->payment_id) && (int)$o['common']->payment_id > 0) {
				$paymentO = $payment->getPaymentMethod((int)$o['common']->payment_id );

				if (isset($paymentO->method)) {
					Dispatcher::dispatch(new Event\Payment\BeforeSetPaymentForm($form, $this->p, $paymentO->params, $o, [
						'pluginname' => $paymentO->method,
					]));
				}
			}

			//$session->set('proceedpayment', array(), 'phocaCart');
			$this->t['o'] = $form;
		} else {
			// No order set, no payment - this should not happen but if, then just repeat thank you
			//$this->t['o'] =  '<div>'.Text::_('COM_PHOCACART_ORDER_SUCCESSFULLY_PROCESSED').'</div>';
			$this->t['o'] = '';
		}

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
        $media->loadSpec();
		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_PAYMENT'));
	}
}
?>
