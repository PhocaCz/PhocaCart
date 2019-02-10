<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.view' );
/*
phocacart import('phocacart.cart.cart');
phocacart import('phocacart.cart.cartdb');
phocacart import('phocacart.cart.rendercart');
phocacart import('phocacart.currency.currency');
*/
 
class PhocaCartCpViewPhocaCartEditProductPointGroup extends JViewLegacy
{
	protected $t;
	protected $item;
	protected $itemhistory;
	protected $id;
	function display($tpl = null) {
		
		$app					= JFactory::getApplication();
		$this->id				= $app->input->get('id', 0, 'int');
		
		if ($this->id < 1) {
			echo JText::_('COM_PHOCACART_NO_PRODUCT_FOUND');
			return;
		}
		
		$this->t						= PhocacartUtils::setVars('product');
		$this->t['product']				= PhocacartProduct::getProduct((int)$this->id);
		$this->t['groups']				= PhocacartGroup::getGroupsById((int)$this->id, 3, 2);
		$this->t['product_groups']		= PhocacartGroup::getProductPointGroupsById((int)$this->id, 3, 2);
		


		$media = new PhocacartRenderAdminmedia();
	
		parent::display($tpl);
	}
}
?>