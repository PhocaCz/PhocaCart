<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$d               = $displayData;
$displayData     = null;
$v               = $d['attribute'];
$attributeIdName = 'V' . $d['typeview'] . 'P' . (int)$d['product_id'] . 'A' . (int)$v->id;
$productIdName   = 'V' . $d['typeview'] . 'P' . (int)$d['product_id'];
$iconType        = $d['s']['i']['icon-type'];
$price           = new PhocacartPrice();

$attr   = array();
$attr[] = 'id="phItemAttribute' . $attributeIdName . '"';// ID
$attr[] = 'class="ph-checkbox-attribute ph-item-input-set-attributes phj' . $d['typeview'] . ' phjProductAttribute ' . $d['required']['class'] . '"';// CLASS
$attr[] = 'data-product-id="' . $d['product_id'] . '"';// Product ID
$attr[] = 'data-product-id-name="' . $productIdName . '"';// Product ID - Unique name between different views
$attr[] = 'data-attribute-type="' . $v->type . '"';// Type of attribute (select, checkbox, color, image)
$attr[] = 'data-attribute-id-name="' . $attributeIdName . '"';// Attribute ID - Unique name between different views and products
$attr[] = 'data-type-view="' . $d['typeview'] . '"';// In which view are attributes displayed: Category, Items, Item, Quick Item
$attr[] = 'data-type-icon="' . $iconType . '"';// Which icons are used on the site (Bootstrap Glyphicons | Font Awesome | ...)


echo '<div id="phItemBoxAttribute' . $attributeIdName . '">';
echo '<div ' . implode(' ', $attr) . '>';


// CHECKBOX COLOR CHECKBOX IMAGE
/*if ($v->type == 5 || $v->type == 6) {
	echo '<div class="ph-item-input-checkbox-color" data-bs-toggle="buttons">';
}*/

foreach ($v->options as $k2 => $v2) {
	// Extends required part for some specific parameters
	$req = PhocacartRenderJs::renderRequiredParts((int)$v->id, (int)$v2->required );
	$d['required'] = $req;
    if ($v2->operator == '=') {
        $operator = '';
    } else {
        $operator = $v2->operator;
    }
    $amount = $d['price']->getPriceFormat($v2->amount);


    // SET SOME VALUE?
    $active = '';
    if ($v2->default_value == 1) {
        $active = ' active';
    }

    $suffix = ' (' . $operator . ' ' . $amount . ')';
    if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 0 && $price->roundPrice($v2->amount) < 0.01 && $price->roundPrice($v2->amount) > -0.01) {
        $suffix = '';
    } else if (isset($d['zero_attribute_price']) && $d['zero_attribute_price'] == 2) {
        $suffix = '';// hide always
    }

    $maxLength = ' maxlength="' . PhocacartAttribute::getAttributeLength($v->type) . '"';

	echo '<div class="'. $d['s']['c']['row'].' ph-gift-box-form">';
    echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].'"><label class="'  . ' phTextAttributeInput ' . $active . '" style="background-color: ' . strip_tags($v2->color) . '">' . htmlspecialchars($v2->title) . $suffix . $d['required']['span'] . '</label></div>';
	echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].'">';
    switch ($v->type) {
        case 10:
        case 11:
		case 23: // GIFT SENDER MESSAGE

            echo '<textarea class="ph-attribute-textarea" name="attribute[' . $v->id . '][' . $v2->id . ']" ' . $d['required']['attribute'] . $maxLength . '></textarea>';

        break;

        case 12:

        	HTMLHelper::_('jquery.framework');
			HTMLHelper::_('script', 'media/com_phocacart/js/jcp/picker.js', array('version' => 'auto'));
			HTMLHelper::_('stylesheet', 'media/com_phocacart/js/jcp/picker.css', array('version' => 'auto'));

        	$idA = 'phColorText';
			$idAC = $idA.'PickerName'. $v->id . 'Id'. $v2->id;

        	echo '<span class="input-append input-group">';
            echo '<input type="text" id="' . $idAC . '" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' class="text_area phColorText" />';
			echo ' <a href="javascript:void(0);" role="button" class="btn btn-primary '.$idA.'PickerButton" onclick="openPicker(\'' . $idAC . '\');">';
			echo '<span class="icon-list icon-white"></span> ';
			echo Text::_('COM_PHOCACART_FORM_SELECT_COLOR') . '</a>';
			echo '</span>';
        break;

        default:

            echo '<input type="text" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' />';

        break;
    }
    echo '</div>';
	echo '</div>';
}


echo '</div>';// end attribute box
echo '</div>';// end attribute
?>
