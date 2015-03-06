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
 
class PhocaCartCpViewPhocaCartCart extends JViewLegacy
{
	protected $t;
	protected $item;
	function display($tpl = null) {
		
		$this->t		= PhocaCartUtils::setVars('cart');
		$this->item		= $this->get('Data');
		
		
		JHTML::stylesheet( $this->t['s'] );
	
		parent::display($tpl);
	}
}
?>