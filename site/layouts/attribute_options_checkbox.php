<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/* COLOR OR IMAGE CHECKBOXES
 * BE AWARE BOOTSTRAP DATA TOGGLE BUTTONS ARE USED
 * IF YOU LOAD OBSOLETE BOOTSTRAP Javascript (bootstrap.min.js)
 * IT CAN BE IN CONFLICT AND THIS FEATURE WILL NOT WORK
 * Use Phoca Upgrade System plugin and remove obsolete bootstrap.min.js with help of this plugin
 */
defined('_JEXEC') or die();

$d 					= $displayData;
$displayData 		= null;
$v 					= $d['attribute'];
$attributeIdName	= 'V'.$d['typeview'].'P'.(int)$d['product_id'].'A'.(int)$v->id;
$productIdName		= 'V'.$d['typeview'].'P'.(int)$d['product_id'];
$iconType			= $d['s']['i']['icon-type'];
$price				= new PhocacartPrice();


$attr				= array();
$attr[]				= 'id="phItemAttribute'.$attributeIdName.'"';// ID
$attr[]				= 'class="ph-checkbox-attribute ph-item-input-set-attributes phj'. $d['typeview'].' phjProductAttribute '.$d['required']['class'].'"';// CLASS
$attr[]				= 'data-product-id="'. $d['product_id'].'"';// Product ID
$attr[]				= 'data-product-id-name="'. $productIdName.'"';// Product ID - Unique name between different views
$attr[]				= 'data-attribute-type="'. $v->type.'"';// Type of attribute (select, checkbox, color, image)
$attr[]				= 'data-attribute-id-name="'. $attributeIdName.'"';// Attribute ID - Unique name between different views and products
$attr[]				= 'data-type-view="'. $d['typeview'].'"';// In which view are attributes displayed: Category, Items, Item, Quick Item
$attr[]				= 'data-type-icon="'. $iconType.'"';// Which icons are used on the site (Bootstrap Glyphicons | Font Awesome | ...)
$attr[]				= 'data-required="'.$d['required']['required'].'"';
$attr[]				= 'data-alias="'.htmlspecialchars($v->alias).'"';


echo '<div id="phItemBoxAttribute'.$attributeIdName.'">';
echo '<div '.implode(' ', $attr).'>';


// CHECKBOX COLOR CHECKBOX IMAGE
if ($v->type == 5 || $v->type == 6) {
	echo '<div class="ph-item-input-checkbox-color btn-group-toggle" data-toggle="buttons">';
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

	$suffix =  ' ('.$operator.' '.$amount.')';
	if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 0 && $price->roundPrice($v2->amount) < 0.01 && $price->roundPrice($v2->amount) > -0.01) {
		$suffix = '';// hide only if price is zero
	} else if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 2) {
		$suffix = '';// hide always
	}

	if ($v->type == 4) { // CHECKBOX STANDARD

		echo '<div class="'.$d['s']['c']['checkbox'].' ph-checkbox"><label><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].' data-value-alias="'.htmlspecialchars($v2->alias).'" />'.htmlspecialchars($v2->title).$suffix.'</label></div>';//<br />';

	} else if ($v->type == 5 && isset($v2->color) && $v2->color != '') { // CHECKBOX COLOR

		$attrO	.= ' data-color="'.strip_tags($v2->color).'"';
		echo '<label class="btn phCheckBoxButton phCheckBoxColor '.$active.'" style="background-color: '.strip_tags($v2->color).'"><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].' autocomplete="off" data-value-alias="'.htmlspecialchars($v2->alias).'" /><span class="'.$d['s']['i']['ok'].'" title="'.htmlspecialchars($v2->title). $suffix .'"></span></label> ';

	} else if ($v->type == 6 && isset($v2->image_small) && $v2->image_small != '') {// CHECKBOX IMAGE

		$linkI 		= JURI::base(true).'/'.$d['pathitem']['orig_rel'].'/'.$v2->image_small;
		echo '<label class="'.$d['s']['c']['btn'].' phCheckBoxButton phCheckBoxImage '.$active.'"><input type="checkbox" '.$attrO.' name="attribute['.$v->id.']['.$v2->id.']" value="'.$v2->id.'" '.$d['required']['attribute'].'  autocomplete="off" data-value-alias="'.htmlspecialchars($v2->alias).'" /><span class="'.$d['s']['i']['ok'].'"></span><img src="'.strip_tags($linkI).'" title="'.htmlspecialchars($v2->title). $suffix.'" alt="'.htmlspecialchars($v2->title).'" /></label>';

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
