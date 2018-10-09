<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


defined('_JEXEC') or die();
$d 					= $displayData;
$displayData 		= null;
$v 					= $d['attribute'];
$attributeIdName	= 'V'.$d['typeview'].'P'.(int)$d['product_id'].'A'.(int)$v->id;
$productIdName		= 'V'.$d['typeview'].'P'.(int)$d['product_id'];
$iconType			= PhocacartRenderIcon::getIconType();

$attr				= array();
$attr[]				= 'id="phItemAttribute'.$attributeIdName.'"';// ID
$attr[]				= 'class="ph-checkbox-attribute ph-item-input-set-attributes phj'. $d['typeview'].' phjProductAttribute '.$d['required']['class'].'"';// CLASS
$attr[]				= 'data-product-id="'. $d['product_id'].'"';// Product ID
$attr[]				= 'data-product-id-name="'. $productIdName.'"';// Product ID - Unique name between different views
$attr[]				= 'data-attribute-type="'. $v->type.'"';// Type of attribute (select, checkbox, color, image)
$attr[]				= 'data-attribute-id-name="'. $attributeIdName.'"';// Attribute ID - Unique name between different views and products
$attr[]				= 'data-type-view="'. $d['typeview'].'"';// In which view are attributes displayed: Category, Items, Item, Quick Item
$attr[]				= 'data-type-icon="'. $iconType.'"';// Which icons are used on the site (Bootstrap Glyphicons | Font Awesome | ...)	


echo '<div id="phItemBoxAttribute'.$attributeIdName.'">';
echo '<div '.implode(' ', $attr).'>';


// CHECKBOX COLOR CHECKBOX IMAGE
/*if ($v->type == 5 || $v->type == 6) {
	echo '<div class="ph-item-input-checkbox-color" data-toggle="buttons">';
}*/
		
foreach ($v->options as $k2 => $v2) {
	if($v2->operator == '=') {
		$operator = '';
	} else {
		$operator = $v2->operator;
	}
	$amount = $d['price']->getPriceFormat($v2->amount);
	

	// SET SOME VALUE? 
	$active = '';
	if ($v2->default_value == 1) {
		$active	= ' active';
	}
	
	$suffix =  ' ('.$operator.' '.$amount.')';
	if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 0 && $v2->amount < 0.01 && $v2->amount > -0.01) {
		$suffix = '';
	}	

	$maxLength = ' maxlength="'.PhocacartAttribute::getAttributeLength($v->type).'"';
	
	echo '<div><label class="btn phTextAttributeInput '.$active.'" style="background-color: '.strip_tags($v2->color).'">'.htmlspecialchars($v2->title). $suffix.'</label><br /><input type="text" name="attribute['.$v->id.']['.$v2->id.']" value="" '.$d['required']['attribute'].$maxLength.' /></div>';
}



echo '</div>';// end attribute box
echo '</div>';// end attribute
?>