<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view' );
/*
phocacart import('phocacart.cart.cart');
phocacart import('phocacart.cart.cartdb');
phocacart import('phocacart.cart.rendercart');
phocacart import('phocacart.currency.currency');
*/

class PhocaCartCpViewPhocaCartEditStatus extends HtmlView
{
	protected $t;
	protected $r;
	protected $item;
	protected $itemhistory;
	protected $id;
	function display($tpl = null) {

		$app				= Factory::getApplication();
		$this->id			= $app->input->get('id', 0, 'int');

		$this->t			= PhocacartUtils::setVars('cart');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->item			= $this->get('Data');
		$this->itemhistory	= $this->get('HistoryData');



		$media = new PhocacartRenderAdminmedia();
		PhocacartRenderAdminjs::renderHtmlAfterChange('#jform_status_id', '#phWarningNotify');

		parent::display($tpl);
	}
}
?>
