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
 
class PhocaCartCpViewPhocaCartEditStatus extends JViewLegacy
{
	protected $t;
	protected $item;
	protected $itemhistory;
	protected $id;
	function display($tpl = null) {
		
		$app				= JFactory::getApplication();
		$this->id			= $app->input->get('id', 0, 'int');
		
		$this->t			= PhocacartUtils::setVars('cart');
		$this->item			= $this->get('Data');
		$this->itemhistory	= $this->get('HistoryData');
		
		

		$media = new PhocacartRenderAdminmedia();
	
		parent::display($tpl);
	}
}
?>