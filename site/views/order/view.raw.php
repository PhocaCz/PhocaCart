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
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event\Invoice\RenderElectronicInvoice;
jimport( 'joomla.application.component.view');

class PhocaCartViewOrder extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	public function display($tpl = null) {

		$app				= Factory::getApplication();
		$this->p 			= $app->getParams();
		$this->s            = PhocacartRenderStyle::getStyles();
		$id					= $app->getInput()->get('id', 0, 'int');
		$type				= $app->getInput()->get('type', 0, 'int');
		$subtype			= $app->getInput()->get('subtype', '', 'string');
		$format				= $app->getInput()->get('format', '', 'string');
		$token				= $app->getInput()->get('o', '', 'string');
		$pos				= $app->getInput()->get('pos', '', '0');
		$print_server		= $app->getInput()->get('printserver', '', '0');

		$orderGuestAccess	= $this->p->get( 'order_guest_access', 0 );
		$pos_server_print	= $this->p->get( 'pos_server_print', 0 );

		if ($orderGuestAccess == 0) {
			$token = '';
		}

		if ($type == 5 && $subtype != '') {
			$this->renderElectronicInvoice($id, $subtype, $token);
			return;
		}

		$order	= new PhocacartOrderRender();
		$o = $order->render($id, $type, $format, $token, $pos);

		if ($pos == 1 && $type == 4) {

			if ($print_server == 1 && ($pos_server_print == 2 || $pos_server_print == 3)) {

				try{

					$printPos = new PhocacartPosPrint(1);
					$printPos->printOrder($o);
					echo '<div class="ph-result-txt ph-success-txt">'.Text::_('COM_PHOCACART_RECEIPT_SENT_TO_PRINTER'). '</div>';
				} catch(Exception $e) {
					echo '<div class="ph-result-txt ph-error-txt">'.Text::_('COM_PHOCACART_ERROR'). ": ". $e->getMessage(). '</div>';
				}
			} else {
				$o = str_replace("\n", '', $o);
				echo '<div class="phPrintInBox">'.$o.'</div>';
			}

		} else {
			echo '<div class="phPrintInBox">'.$o.'</div>';
		}
	}

	protected function renderElectronicInvoice($id, $subtype, $token) {
		$app = Factory::getApplication();

		if ($id < 1) {
			echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
			return;
		}

		$user = PhocacartUser::getUser();
		$orderView = new PhocacartOrderView();
		$common = $orderView->getItemCommon($id);

		if (!$common || empty($common->order_number)) {
			echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
			return;
		}

		if (!$app->isClient('administrator')) {
			if ((int)$user->id < 1 && $token == '') {
				PhocacartLog::add(2, 'Render E-Invoice - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'User not found');
				echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
				return;
			}
			if ($user->id != $common->user_id && $token == '') {
				PhocacartLog::add(2, 'Render E-Invoice - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'User doesn\'t match');
				echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
				return;
			}
			if ((int)$user->id < 1 && $token != '' && ($token != $common->order_token)) {
				PhocacartLog::add(2, 'Render E-Invoice - ERROR', (int)$id, Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND') . 'Token doesn\'t match');
				echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
				return;
			}
		}

		$price = new PhocacartPrice();
		$price->setCurrency($common->currency_id);

		$orderData = [
			'common' => $common,
			'bas' => $orderView->getItemBaS($id, 1),
			'products' => $orderView->getItemProducts($id),
			'total' => $orderView->getItemTotal($id, 1),
			'taxrecapitulation' => $orderView->getItemTaxRecapitulation($id),
			'price' => $price,
			'params' => PhocacartUtils::getComponentParameters(),
		];

		$output = null;
		$results = Dispatcher::dispatch(new RenderElectronicInvoice($orderData, [
			'pluginname' => $subtype,
			'orderData' => $orderData,
		]));

		if (!empty($results)) {
			foreach ($results as $result) {
				if (is_array($result) && !empty($result['content'])) {
					$output = $result;
					break;
				}
			}
		}

		if ($output && !empty($output['content'])) {
			$filename = $output['filename'] ?? 'invoice.xml';
			$contentType = $output['contentType'] ?? 'application/xml';

			$app->setHeader('Content-Type', $contentType . '; charset=UTF-8');
			$app->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
			$app->setHeader('Cache-Control', 'no-cache, must-revalidate');
			$app->setHeader('Pragma', 'public');

			echo $output['content'];
		} else {
			echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
		}
	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_ORDER'));
	}
}
?>
