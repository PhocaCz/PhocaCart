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
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );
/*
phocacart import('phocacart.cart.cart');
phocacart import('phocacart.cart.cartdb');
phocacart import('phocacart.cart.rendercart');
phocacart import('phocacart.currency.currency');
*/

class PhocaCartCpViewPhocaCartEditStockAdvanced extends HtmlView
{
	protected $t;
	protected $r;
	protected $item;
	protected $itemhistory;
	protected $id;
	function display($tpl = null) {

		$app					= Factory::getApplication();
		$this->id				= $app->input->get('id', 0, 'int');

		if ($this->id < 1) {
			echo '<div class="alert alert-error">';
		    echo Text::_('COM_PHOCACART_NO_PRODUCT_FOUND'). '<br/>';
			echo Text::_('COM_PHOCACART_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST');
		    echo '</div>';
			return;
		}

		$this->state	= $this->get('State');
		$this->state->set('phocacarteditstockadvanced.id', (int)$this->id);
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');

		$media = new PhocacartRenderAdminmedia();


		$this->t						= PhocacartUtils::setVars('editstockadvanced');
		$this->r						= new PhocacartRenderAdminview();
		/*$this->t['product']				= PhocacartProduct::getProduct((int)$this->id);
		$this->t['attr_options']		= PhocacartAttribute::getAttributesAndOptions((int)$this->id);
		$this->t['combinations']		= array();
		$this->t['combinations_data']	= array();

		if (!empty($this->t['product'])) {
			PhocacartAttribute::getCombinations( $this->t['product']->id, $this->t['product']->title,  $this->t['attr_options'], $this->t['combinations']);
			// Load data from database
			$this->t['combinations_data'] = PhocacartAttribute::getCombinationsDataByProductId($this->t['product']->id);

		}*/




		parent::display($tpl);
	}
}
?>
