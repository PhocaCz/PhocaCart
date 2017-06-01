<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;

// Be aware when overwriting this layout
// There are different combinations of buttons
// ATTRIBUTE REQUIRED/ATTRIBUTE NOT REQUIRED - BUTTON/ICON - STANDARD/AJAX
// THIS LAYOUT can be display twice for item - first for ICON, second for BUTTON - so only ICON have ID to run AJAX, etc.

if ($d['attrrequired'] == 1) {
	// ATTRIBUTE REQUIRED - One of the attributes is required, cannot add to cart
	echo '<div class="pull-right">';
	
	if ($d['icon'] == 1) {
		
		// ICON - ATTRIBUTE REQUIRED
		echo '<div class="ph-category-item-addtocart">';
		echo '<a href="'.$d['link'].'" title="'. JText::_('COM_PHOCACART_ADD_TO_CART').'">';
		echo '<span class="glyphicon glyphicon-shopping-cart"></span>';
		echo '</a>';
		echo '</div>';
		
	} else {
		// BUTTON - ATTRIBUTE REQUIRED
		if ($d['addtocart'] == 1) {
			
			echo '<a href="'.$d['link'].'" class="btn btn-primary btn-sm ph-btn" role="button">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span> ';
			echo JText::_('COM_PHOCACART_ADD_TO_CART');
			echo '</a>';
			
		} else if ($d['addtocart'] == 4) {
			
			echo '<a href="'.$d['link'].'" class="btn btn-primary btn-sm ph-btn" role="button" title="'.JText::_('COM_PHOCACART_ADD_TO_CART').'">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span>';
			echo '</a>';
		} 
	}
	
	echo '</div>';

} else { 
	// ATTRIBUTE NOT REQUIRED
	if ($d['icon'] == 1) {
		// If icon then we need ID of form to run it per jquery
		echo '<form class="phItemCartBoxForm" id="phCartAddToCart'.(int)$d['id'].'" action="'.$d['linkch'].'" method="post">';
	} else {
		// If not icon then we don't need ID form and we don't display ID because of not conflict with icon
		// On one page there can be BUTTON and ICON Add to Cart together so don't display ID for buttons to not conflict
		// with the same ID of icon ()
		echo '<form class="phItemCartBoxForm" action="'.$d['linkch'].'" method="post">';
	}
	
	?><input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
	<input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>">
	<input type="hidden" name="quantity" value="1">
	<input type="hidden" name="task" value="checkout.add">
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" /><?php
	
	echo '<div class="pull-right">';
	
	if ($d['icon'] == 1) {
		// ICON ATTRIBUTE NOT REQUIRED
		if ($d['method'] == 0) {
			// STANDARD (add to cart method)
			echo '<div class="ph-category-item-addtocart"><a href="javascript:void(0);" onclick="document.getElementById(\'phCartAddToCart'.(int)$d['id'].'\').submit();"  title="'.JText::_('COM_PHOCACART_ADD_TO_CART').'">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span>';
			echo '</a>';
			echo '</div>';
			
		} else {
			// AJAX (add to cart method)
			echo '<div class="ph-category-item-addtocart"><a href="javascript:void(0);" onclick="phEventClickFormAddToCart(\'phCartAddToCart'.(int)$d['id'].'\');"  title="'.JText::_('COM_PHOCACART_ADD_TO_CART').'">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span>';
			echo '</a>';
			echo '</div>';
			
		}
		
	} else {
		// BUTTON ATTRIBUTE NOT REQUIRED
		if ($d['addtocart'] == 1) {
			echo '<button class="btn btn-primary btn-sm ph-btn">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span> ';
			echo JText::_('COM_PHOCACART_ADD_TO_CART');
			echo '</button>';
			
		} else if ($d['addtocart'] == 4) {
			echo '<button class="btn btn-primary btn-sm ph-btn" title="'.JText::_('COM_PHOCACART_ADD_TO_CART').'">';
			echo '<span class="glyphicon glyphicon-shopping-cart"></span>';
			echo '</button>';
		}
	}
	
	echo '</div>';// pull right
	echo '<div class="clearfix"></div>';
	echo JHtml::_('form.token');
	echo '</form>';
}
?>