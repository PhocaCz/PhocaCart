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
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Filesystem\File;
$d 					= $displayData;
$displayData 		= null;
$v 					= $d['attribute'];
$attributeIdName	= 'V'.$d['typeview'].'P'.(int)$d['product_id'].'A'.(int)$v->id;
$productIdName		= 'V'.$d['typeview'].'P'.(int)$d['product_id'];
$iconType			= $d['s']['i']['icon-type'];
$price				= new PhocacartPrice();

$attr				= array();
$attr[]				= 'id="phItemAttribute'.$attributeIdName.'"';// ID
$attr[]				= 'class="form-select chosen-select ph-item-input-set-attributes phj'. $d['typeview'].' phjProductAttribute"';// CLASS
$attr[]				= $d['required']['attribute'];
$attr[]				= 'name="attribute['.(int)$v->id.']"';
$attr[]				= 'data-product-id="'. $d['product_id'].'"';// Product ID
$attr[]				= 'data-product-id-name="'. $productIdName.'"';// Product ID - Unique name between different views
$attr[]				= 'data-attribute-type="'. $v->type.'"';// Type of attribute (select, checkbox, color, image)
$attr[]				= 'data-attribute-id-name="'. $attributeIdName.'"';// Attribute ID - Unique name between different views and products
$attr[]				= 'data-type-view="'. $d['typeview'].'"';// In which view are attributes displayed: Category, Items, Item, Quick Item
$attr[]				= 'data-type-icon="'. $iconType.'"';// Which icons are used on the site (Bootstrap Glyphicons | Font Awesome | ...)
$attr[]				= 'data-required="'.$d['required']['required'].'"';
$attr[]				= 'data-alias="'.htmlspecialchars($v->alias).'"';

// Display attribute title
//echo '<div>'.$v->title.'</div>';

echo '<div id="phItemBoxAttribute'.$attributeIdName.'">';
echo '<select '.implode(' ', $attr).'>';
if ((int)$d['required']['required'] == 1 && (int)$d['remove_select_option_attribute'] == 1) {
	// If the attribute is requried, there cannot be select option in select box (to select no value)
	// this is not a problem for "add to cart" button as it just checks for the selected options
	// but for ajaxes like chaning price or stock in item view, this is why this option is not displayed when attribute required
} else {
	echo '<option value="">'. Text::_('COM_PHOCACART_SELECT_OPTION').'</option>';
}

foreach ($v->options as $k2 => $v2) {
	if($v2->operator == '=') {
		$operator = '';
	} else {
		$operator = $v2->operator;
	}
	$amount = $d['price']->getPriceFormat($v2->amount);

	// Switch large image
	$attrO		= '';

	if ($d['dynamic_change_image'] == 1) {
		if ($d['image_size'] == 'large' && isset($v2->image) && $v2->image != '') {
			$imageO 	= PhocacartImage::getThumbnailName($d['pathitem'], $v2->image, $d['image_size']);
		} else if ($d['image_size'] == 'medium' && isset($v2->image) && $v2->image != '') {
			$imageO 	= PhocacartImage::getThumbnailName($d['pathitem'], $v2->image, $d['image_size']);
		}

		if (isset($imageO->rel) && $imageO->rel != '') {
			$linkO 		= Uri::base(true).'/'.$imageO->rel;

			if (File::exists($imageO->abs)) {
				$attrO		.= 'data-image-option="'.htmlspecialchars($linkO).'"';
			}
		}

	}

	// SELECTBOX COLOR
	if ($v->type == 2 && isset($v2->color) && $v2->color != '') {
		$attrO		.= ' data-color="'.strip_tags($v2->color).'"';
	}

	// SELECTBOX IMAGE
	if ($v->type == 3 && isset($v2->image_small) && $v2->image_small != '') {
		$linkI 		= Uri::base(true).'/'.$d['pathitem']['orig_rel'].'/'.$v2->image_small;
		$attrO		.= ' data-image="'.strip_tags($linkI).'"';
	}

	// SELECTBOX TEXT
	if ($v->type == 13 && isset($v2->title)) {
		$attrO		.= ' data-text="'.strip_tags(htmlspecialchars($v2->title)).'"';
	}

	// SELECTED SOME VALUE?
	if ($v2->default_value == 1) {
		$attrO		.= ' selected="seleced"';
	}

	$suffix =  ' ('.$operator.' '.$amount.')';
	if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 0 && $price->roundPrice($v2->amount) < 0.01 && $price->roundPrice($v2->amount) > -0.01) {
		$suffix = '';
	} else if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 2) {
		$suffix = '';// hide always
	}

	echo '<option '.$attrO.' value="'.$v2->id.'" data-value-alias="'.htmlspecialchars($v2->alias).'">'.htmlspecialchars($v2->title).$suffix.'</option>';
}

echo '</select>';// end select box
echo '</div>';// end attribute
echo '<div id="phItemHiddenAttribute'.$attributeIdName.'" style="display:none;"></div>';

?>
