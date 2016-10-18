<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-checkout-box" class="pc-checkout-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocaCartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_CHECKOUT')));

if ( isset($this->t['checkout_desc']) && $this->t['checkout_desc'] != '') {
	// Checkout description
	echo '<div class="ph-desc">'. JHTML::_('content.prepare', $this->t['checkout_desc']). '</div>';
}


echo $this->loadTemplate('cart');
echo $this->loadTemplate('login');
echo $this->loadTemplate('address');
echo $this->loadTemplate('shipping');
echo $this->loadTemplate('payment');
echo $this->loadTemplate('confirm');



echo '</div>';// end ph-pc-checkout-box
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>