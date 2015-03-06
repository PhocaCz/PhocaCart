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
phocacartimport('phocacart.cart.cart');
phocacartimport('phocacart.cart.cartdb');
phocacartimport('phocacart.cart.rendercart');
phocacartimport('phocacart.currency.currency');
 
class PhocaCartCpViewPhocaCartEditStatus extends JViewLegacy
{
	protected $t;
	protected $item;
	protected $itemhistory;
	protected $id;
	function display($tpl = null) {
		
		$app				= JFactory::getApplication();
		$this->id			= $app->input->get('id', 0, 'int');
		
		$this->t			= PhocaCartUtils::setVars('cart');
		$this->item			= $this->get('Data');
		$this->itemhistory	= $this->get('HistoryData');
		
		

		JHTML::stylesheet( $this->t['s'] );
	
		parent::display($tpl);
	}
}
?>