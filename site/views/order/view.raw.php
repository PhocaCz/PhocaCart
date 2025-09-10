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
		$format				= $app->getInput()->get('format', '', 'string');
		$token				= $app->getInput()->get('o', '', 'string');
		$pos				= $app->getInput()->get('pos', '', '0');
		$print_server		= $app->getInput()->get('printserver', '', '0');

		$orderGuestAccess	= $this->p->get( 'order_guest_access', 0 );
		$pos_server_print	= $this->p->get( 'pos_server_print', 0 );

		if ($orderGuestAccess == 0) {
			$token = '';
		}


		$order	= new PhocacartOrderRender();
		$o = $order->render($id, $type, $format, $token, $pos);




		if ($pos == 1 && $type == 4) {

			// PRINT SERVER PRINT
			if ($print_server == 1 && ($pos_server_print == 2 || $pos_server_print == 3)) {

				try{

					$printPos = new PhocacartPosPrint(1);
					$printPos->printOrder($o);
					echo '<div class="ph-result-txt ph-success-txt">'.Text::_('COM_PHOCACART_RECEIPT_SENT_TO_PRINTER'). '</div>';
				} catch(Exception $e) {
					echo '<div class="ph-result-txt ph-error-txt">'.Text::_('COM_PHOCACART_ERROR'). ": ". $e->getMessage(). '</div>';
				}
			} else {
				// RECEIPT IN HTML
				$o = str_replace("\n", '', $o); // produce html output in PRE and CODE tag without new rows ("\n");
				echo '<div class="phPrintInBox">'.$o.'</div>'; // --> components\com_phocacart\views\pos\tmpl\default_main_content_order.php
			}

		} else {
			echo '<div class="phPrintInBox">'.$o.'</div>'; // --> components\com_phocacart\views\pos\tmpl\default_main_content_order.php
		}
	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_ORDER'));
	}
}
?>
