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
		$id					= $app->input->get('id', 0, 'int');
		$type				= $app->input->get('type', 0, 'int');
		$format				= $app->input->get('format', '', 'string');
		$token				= $app->input->get('o', '', 'string');
		$pos				= $app->input->get('pos', '', '0');

		$orderGuestAccess	= $this->p->get( 'order_guest_access', 0 );

		if ($orderGuestAccess == 0) {
			$token = '';
		}
		$order	= new PhocacartOrderRender();
		$o = $order->render($id, $type, $format, $token, $pos);

		$media = PhocacartRenderMedia::getInstance('main');

		echo $o;

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_ORDER'));
	}
}
?>
