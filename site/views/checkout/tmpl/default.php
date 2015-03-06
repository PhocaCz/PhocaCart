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


echo '<h1>';
if ($this->p->get('show_page_heading')) { 
	 $this->escape($this->p->get('page_heading'));
}
echo JText::_('COM_PHOCACART_CHECKOUT');
echo '</h1>';


if ( isset($this->t['checkout_desc']) && $this->t['checkout_desc'] != '') {
	echo '<div class="ph-desc">'. $this->t['checkout_desc']. '</div>';
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