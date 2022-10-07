<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$d = $displayData;



// Be aware when overwriting this layout
// There are different combinations of buttons
// ATTRIBUTE REQUIRED/ATTRIBUTE NOT REQUIRED - BUTTON/ICON - STANDARD/AJAX
// THIS LAYOUT can be display twice for item - first for ICON, second for BUTTON - so only ICON have ID to run AJAX, etc.


if ($d['selectoptions'] == 1) {
	// ATTRIBUTE REQUIRED - One of the attributes is required, cannot add to cart when we don't display attributes in category/items view
	echo '<div class="'.$d['s']['c']['pull-right'].' ph-button-choose-variant-box">';


	if ($d['addtocart'] == 1) {

		echo '<a href="'.$d['link'].'" class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn phProductAddToCart'.$d['typeview'].$d['id'].' '.$d['class_btn'].'" role="button">';
		//echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span> ';
		echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart'], '', ' ');
		echo JText::_('COM_PHOCACART_CHOOSE_VARIANT'); // JText::_('COM_PHOCACART_SELECT_OPTIONS');
		echo '</a>';

	} else if ($d['addtocart'] == 4) {

		echo '<a href="'.$d['link'].'" class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn phProductAddToCart'.$d['typeview'].$d['id'].' '.$d['class_btn'].'" role="button" title="'.JText::_('COM_PHOCACART_CHOOSE_VARIANT') /* JText::_('COM_PHOCACART_SELECT_OPTIONS') */.'" data-bs-toggle="tooltip" data-placement="top">';
		//echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span>';
		echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart']);
		echo '</a>';
	}



	echo '</div>';
	echo '<div class="ph-cb"></div>';

} else {

	echo '<div class="'.$d['s']['c']['pull-right'].' ph-button-add-to-cart-box">';

	// BUTTON ATTRIBUTE NOT REQUIRED OR REQUIRED BUT we list the attributes in category/items view so we can submit
	$onClick = '';
	if ($d['addtocart'] == 1) { // Standard

		echo '<button type="submit" class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn phProductAddToCart'.$d['typeview'].$d['id'].' '.$d['class_btn'].'" '.$onClick.'>';
		//echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span> ';
		echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart'], '', ' ');
		echo Text::_('COM_PHOCACART_ADD_TO_CART');
		echo '</button>';

	} else if ($d['addtocart'] == 4) { // Icon Only
		echo '<button type="submit" class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn phProductAddToCart'.$d['typeview'].$d['id'].' '.$d['class_btn'].'" title="'.Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top" '.$onClick.'>';
		//echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span>';
		echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart']);
		echo '</button>';
	}



	echo '</div>';// pull right
	echo '<div class="ph-cb"></div>';
}


/*
if ($d['icon'] == 1) {
	// ICON ATTRIBUTE NOT REQUIRED
	if ($d['method'] == 0) {
		// STANDARD (add to cart method)
		$onClick = 'onclick="document.getElementById(\'phCartAddToCartButton'.(int)$d['id'].'\').submit();"';
	} else {
		// AJAX (add to cart method)
		$onClick = 'onclick="phEventClickFormAddToCart(\'phCartAddToCartButton'.(int)$d['id'].'\');"';
	}

	echo '<div class="ph-category-item-addtocart"><a href="javascript:void(0);" '.$onClick.' title="'.Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top">';
	echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span>';
	echo '</a>';
	echo '</div>';

} else {
	*/


/*
if ($d['attrrequired'] == 1) {
	// ATTRIBUTE REQUIRED - One of the attributes is required, cannot add to cart
	echo '<div class="ph-pull-right">';

	if ($d['icon'] == 1) {

		// ICON - ATTRIBUTE REQUIRED
		echo '<div class="ph-category-item-addtocart">';
		echo '<a href="'.$d['link'].'" title="'. Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top">';
		echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span>';
		echo '</a>';
		echo '</div>';

	} else {
		// BUTTON - ATTRIBUTE REQUIRED
		if ($d['addtocart'] == 1) {

			echo '<a href="'.$d['link'].'" class="btn btn-primary btn-sm ph-btn btn-small" role="button">';
			echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span> ';
			echo Text::_('COM_PHOCACART_ADD_TO_CART');
			echo '</a>';

		} else if ($d['addtocart'] == 4) {

			echo '<a href="'.$d['link'].'" class="btn btn-primary btn-sm ph-btn btn-small" role="button" title="'.Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top">';
			echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span>';
			echo '</a>';
		}
	}

	echo '</div>';

} else { */
	// ATTRIBUTE NOT REQUIRED
/*	if ($d['icon'] == 1) {
		// If icon then we need ID of form to run it per jquery
		echo '<form class="phItemCartBoxForm" id="phCartAddToCartIcon'.(int)$d['id'].'" action="'.$d['linkch'].'" method="post">';
	} else {
		// If button then we need ID of form to run it per jquery like by icon - because of loaded items per ajax
		echo '<form class="phItemCartBoxForm" id="phCartAddToCartButton'.(int)$d['id'].'" action="'.$d['linkch'].'" method="post">';
	}*/



/*	echo '<div class="ph-pull-right">';

	if ($d['icon'] == 1) {
		// ICON ATTRIBUTE NOT REQUIRED
		if ($d['method'] == 0) {
			// STANDARD (add to cart method)
			$onClick = 'onclick="document.getElementById(\'phCartAddToCartButton'.(int)$d['id'].'\').submit();"';
		} else {
			// AJAX (add to cart method)
			$onClick = 'onclick="phEventClickFormAddToCart(\'phCartAddToCartButton'.(int)$d['id'].'\');"';
		}

		echo '<div class="ph-category-item-addtocart"><a href="javascript:void(0);" '.$onClick.' title="'.Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top">';
		echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span>';
		echo '</a>';
		echo '</div>';

	} else {
		// BUTTON ATTRIBUTE NOT REQUIRED
		if ($d['method'] == 0) {
			// STANDARD (add to cart method)
			$onClick = '';
		} else {
			// AJAX (add to cart method)
			$onClick = 'onclick="phEventClickFormAddToCart(\'phCartAddToCartButton'.(int)$d['id'].'\');event.preventDefault();return false;"';
		}

		if ($d['addtocart'] == 1) {
			echo '<button type="submit" class="btn btn-primary btn-sm ph-btn btn-small" '.$onClick.'>';
			echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span> ';
			echo Text::_('COM_PHOCACART_ADD_TO_CART');
			echo '</button>';

		} else if ($d['addtocart'] == 4) {
			echo '<button type="submit" class="btn btn-primary btn-sm ph-btn btn-small" title="'.Text::_('COM_PHOCACART_ADD_TO_CART').'" data-bs-toggle="tooltip" data-placement="top" '.$onClick.'>';
			echo '<span class="'.PhocacartRenderIcon::getClass('shopping-cart').'"></span>';
			echo '</button>';
		}
	}

	echo '</div>';// pull right
	echo '<div class="clearfix"></div>';*/


//}
?>
