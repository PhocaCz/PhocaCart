<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// All is solved in the rendercart and cart (include the asked userid)
if (isset($this->item->user_id) && (int)$this->item->user_id > 0) {

	$link	= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartcart&tmpl=component&&id='.(int)$this->item->user_id);
	$cart	= new PhocaCartRenderCart();
	$cart->setFullItems();
	echo '<div class="ph-cart-info-box">';
	echo $cart->render();
	echo '</div>';

	echo '<div class="ph-last-cart-activity">'.JText::_('COM_PHOCACART_LAST_CART_ACTIVITY').': '.$this->item->date.'</div>';
	$userName = $this->item->user_name;
	if (isset($this->item->user_username)) {
		$userName .= ' <small>('.$this->item->user_username.')</small>';
	}
	echo '<div class="ph-cart-info-user">'.JText::_('COM_PHOCACART_USER').': '.$userName.'</div>';

	echo '<form action="'.$link.'" method="post">';
	echo '<input type="hidden" name="id" value="'.(int)$this->item->user_id.'">';
	echo '<input type="hidden" name="task" value="phocacartcart.emptycart">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="icon-delete"></span> '.JText::_('COM_PHOCACART_EMPTY_USER_CART').'</button>';
	echo '</div>';
	echo JHtml::_('form.token');
	echo '</form>';
} else {
	echo '<div class="ph-cart-info-user">'.JText::_('COM_PHOCACART_NO_ACTIVE_CART_FOR_THIS_USER').'</div>';
}

	
?>
