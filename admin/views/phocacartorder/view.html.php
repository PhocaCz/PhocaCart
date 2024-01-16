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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartOrder extends HtmlView
{
	protected $state;
	protected $item;
	protected $itemcommon;
	protected $itemproducts;
	protected $itemtotal;
	protected $form;
	protected $fieldsbas;
	protected $formbas;
	protected $t;
	protected $r;
	protected $u;
	protected $pr;
	protected $p;


	public function display($tpl = null) {

		$paramsC 					    		= PhocacartUtils::getComponentParameters();
		$this->p['order_language_variables']	= $paramsC->get( 'order_language_variables', 0 );

		$this->t		= PhocacartUtils::setVars('order');
		$this->r		= new PhocacartRenderAdminview();
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$model 			= $this->getModel();
		$this->u		= Factory::getUser($this->item->user_id);
		$order			= new PhocacartOrderView();
		$this->pr		= new PhocacartPrice();
		$this->pr->setCurrency($this->item->currency_id, $this->item->id);


		$this->fieldsbas				= $model->getFieldsBaS();
		$this->formbas					= $model->getFormBaS($this->item->id);
		$this->itemcommon				= $order->getItemCommon($this->item->id);
		$this->itemproducts 			= $order->getItemProducts($this->item->id);
		$this->itemproductdiscounts 	= $order->getItemProductDiscounts($this->item->id);

		$this->itemtotal 				= $order->getItemTotal($this->item->id);
		$this->itemtaxrecapitulation 	= $order->getItemTaxRecapitulation($this->item->id);

		$this->itembas					= $order->getItemBaS($this->item->id, 1);


		$media = new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		Factory::getApplication()->input->set('hidemainmenu', true);
		$bar 		= Toolbar::getInstance('toolbar');
		$user		= Factory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$class		= ucfirst($this->t['tasks']).'Helper';
		$canDo		= $class::getActions($this->t, $this->state->get('filter.order_id'));

		$text = $isNew ? Text::_( $this->t['l'] . '_NEW' ) : Text::_($this->t['l'] . '_EDIT');
		ToolbarHelper::title(   Text::_( $this->t['l'] . '_ORDER' ).': <small><small>[ ' . $text.' ]</small></small>' , 'cart');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			ToolbarHelper::apply($this->t['task'].'.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save($this->t['task'].'.save', 'JTOOLBAR_SAVE');
			//JToolbarHelper::addNew($this->t['task'].'.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}

		if (empty($this->item->id))  {
			ToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			ToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CLOSE');
		}
		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}
}
?>
