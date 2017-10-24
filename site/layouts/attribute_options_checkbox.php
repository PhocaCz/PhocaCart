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

$attr				= array();
$attr[]				= 'id="phItemAttribute'.$attributeIdName.'"';// ID
$attr[]				= 'class="ph-checkbox-attribute ph-item-input-set-attributes phj'. $d['typeview'].' phjProductAttribute '.$d['required']['class'].'"';// CLASS
$attr[]				= 'data-product-id="'. $d['product_id'].'"';// Product ID
$attr[]				= 'data-product-id-name="'. $productIdName.'"';// Product ID - Unique name between different views
$attr[]				= 'data-attribute-type="'. $v->type.'"';// Type of attribute (select, checkbox, color, image)
$attr[]				= 'data-attribute-id-name="'. $attributeIdName.'"';// Attribute ID - Unique name between different views and products
$attr[]				= 'data-type-view="'. $d['typeview'].'"';// In which view are attributes displayed: Category, Items, Item, Quick Item	


echo '<div id="phItemBoxAttribute'.$attributeIdName.'">';
echo '<div '.implode(' ', $attr).'>';


// CHECKBOX COLOR CHECKBOX IMAGE
if ($v->type == 5 || $v->type == 6) {
	echo '<div class="ph-item-input-checkbox-color" data-toggle="buttons">';
}
		
foreach ($v->options as $k2 => $v2) {
	if($v2->operator == '=') {
		$operator = '';
	} else {
		$operator = $v2->operator;
	}
	$amount = $d['price']->getPriceFormat($v2->amount);
	
	// Switch large image
	// BE AWARE when checkbox used, and two images will be selected, only the first will be displayed
	// of course not both can be displayed
	$attrO		= '';
	if ($d['dynamic_change_image'] == 1) {
		if (isset($v2->image) && $v2->image != '') {
			$imageO 	= PhocacartImage::getThumbnailName($d['pathitem'], $v2->image, $d['image_size']);
			$linkO 		= JURI::base(true).'/'.$imageO->rel;
			if (JFile::exists($imageO->abs)) {
				$attrO		.= 'data-image-option="'.htmlspecialchars($linkO).'"';
			}
		}
	}
	
	// SET SOME VALUE? 
	$active = '';
	if ($v2->default_value == 1) {
		$attrO		.= ' checked="checked"';// color and image checkboxes based on opacity
		$active	= ' active';
	}
	
	if ($v->type == 4) { // CHECKBOX STANDARD
		
		echo '<div class="checkbox ph-checkbox"><label><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].' />'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')</label></div><br />';
		
	} else if ($v->type == 5 && isset($v2->color) && $v2->color != '') { // CHECKBOX COLOR
		
		$attrO	.= ' data-color="'.strip_tags($v2->color).'"';
		echo '<label class="btn phCheckBoxButton phCheckBoxColor '.$active.'" style="background-color: '.strip_tags($v2->color).'"><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].' autocomplete="off"  /><span class="glyphicon glyphicon-ok" title="'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')'.'"></span></label> ';
		
	} else if ($v->type == 6 && isset($v2->image_small) && $v2->image_small != '') {// CHECKBOX IMAGE

		$linkI 		= JURI::base(true).'/'.$d['pathitem']['orig_rel'].'/'.$v2->image_small;
		echo '<label class="btn phCheckBoxButton phCheckBoxImage '.$active.'"><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].'  autocomplete="off"  /><span class="glyphicon glyphicon-ok"></span><img src="'.strip_tags($linkI).'" title="'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')'.'" alt="'.htmlspecialchars($v2->title).'" /></label>';

	}
}

// CHECKBOX COLOR
if ($v->type == 5 || $v->type == 6) {
	echo '</div>';// end button group toggle buttons ph-item-input-checkbox-color
}

echo '</div>';// end attribute box
echo '</div>';// end attribute
echo '<div id="phItemHiddenAttribute'.$attributeIdName.'" style="display:none;"></div>';
?>