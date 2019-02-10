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
 
class PhocaCartCpViewPhocacartCart extends JViewLegacy
{
	protected $t;
	protected $item;
	function display($tpl = null) {
		
		$this->t		= PhocacartUtils::setVars('cart');
		$this->item		= $this->get('Data');
		
		
		$media = new PhocacartRenderAdminmedia();
	
		parent::display($tpl);
	}
}
?>