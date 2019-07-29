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

// Add to Cart Form needs to be loaded in default view
//echo $layoutPFS->render($dF);// Start Form
//echo $layoutPFE->render();// End Form


if ($d['selectoptions'] == 1) {
	// ICON ATTRIBUTE REQUIRED AND ATTRIBUTES NOT DISPLAYED - so we should redirect to detail view

	echo '<div class="ph-category-item-addtocart">';
	echo '<a href="'.$d['link'].'" title="'. JText::_('COM_PHOCACART_CHOOSE_VARIANT') /* JText::_('COM_PHOCACART_SELECT_OPTIONS') */.'" data-toggle="tooltip" data-placement="top">';
	echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span>';
	echo '</a>';
	echo '</div>';

} else {
	// ICON ATTRIBUTE IS REQUIRED/IS NOT REQUIRED BUT ATTRIBUTES ARE LISTED IN CATEGROY/TEMS VIEW

	$onClick = 'onclick="jQuery(\'#phCartAddToCartButton'.(int)$d['id'].'\').find(\':submit\').click();return false;"';

	echo '<div class="ph-category-item-addtocart phProductAddToCartIcon'.$d['typeview'].(int)$d['id'].' '.$d['class_icon'].'"><a href="javascript:void(0);" '.$onClick.' title="'.JText::_('COM_PHOCACART_ADD_TO_CART').'" data-toggle="tooltip" data-placement="top">';
	echo '<span class="'.$d['s']['i']['shopping-cart'].'"></span>';
	echo '</a>';
	echo '</div>';
}
