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
use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$layoutG	= new FileLayout('gift_voucher', null, array('component' => 'com_phocacart'));

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

// First, set default values

$defaultImage 			= '';
$defaultTitle 			= '';
$defaultDescription 	= '';
$defaultClassName 		= '';
$defaultDate 			= '';
$giftTypes				= array();
$giftSenderNameActive 	= 0;
$giftSenderMessageActive = 0;
$giftRecipientNameActive = 0;
if (!empty($d['gift_types'])) {
	$registry = new Registry;
	$registry->loadString($d['gift_types']);
	$giftTypes = $registry->toArray();

}

if (!empty($giftTypes)) {
	foreach($giftTypes as $k1 => $v1) {
		if (isset($v1['image']) && $v1['image'] != '') {
			$defaultImage =  $v1['image'];
		}
		if (isset($v1['class_name']) && $v1['class_name'] != '') {
			$defaultClassName = strip_tags($v1['class_name']);
		}
		if (isset($v1['expiration_date']) && $v1['expiration_date'] != '') {
			$defaultDate = $v1['expiration_date'];
		}
		if (isset($v1['description']) && $v1['description'] != '') {
			$defaultDescription = $v1['description'];
		}

		if (isset($v1['title']) && $v1['title'] != '') {
			$defaultTitle = $v1['title'];
		}
	}
}

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

    $maxLength = ' maxlength="' . PhocacartAttribute::getAttributeLength($v->type, $v2->type) . '"';



    echo '<div class="'. $d['s']['c']['row'].' ph-gift-box-form">';


	echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].'"><label class="' . $d['s']['c']['btn'] . ' phTextAttributeInput ' . $active . '" style="background-color: ' . strip_tags($v2->color) . '">' . htmlspecialchars($v2->title) . $suffix . $d['required']['span'] . '</label></div>';




	echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].'">';
	switch ($v2->type) {


		case 24: // GIFT TYPE

			if (!empty($giftTypes)) {
				$i = 0;

				foreach($giftTypes as $k3 => $v3){

					$id = str_replace('gift_types', '', $k3 );

					$title = '';
					if (isset($v3['title']) && $v3['title'] != '') {
						$title = $v3['title'];
						if ($i == 0) {
							$defaultTitle = $title;
						}
					}

					$image = '';
					if (isset($v3['image']) && $v3['image'] != '') {
						$image = /*Uri::base(true) . '/' .*/ $v3['image'];
						if ($i == 0) {
							$defaultImage = $image;
						}
					}

					$date = '';
					if (isset($v3['expiration_date']) && $v3['expiration_date'] != '') {
						$date = HTMLHelper::date($v3['expiration_date'], Text::_('DATE_FORMAT_LC3'));
						if ($i == 0) {
							$defaultDate = $date;
						}
					}

					$description = '';
					if (isset($v3['description']) && $v3['description'] != '') {
						$description = $v3['description'];
						if ($i == 0) {
							$defaultDescription = $description;
						}
					}

					$className = 'default';
					if (isset($v3['class_name']) && $v3['class_name'] != '') {
						$className = $v3['class_name'];
						if ($i == 0) {
							$defaultClassName = $className;
						}
					}

					$checked = '';

					if ($i == 0) {
						$checked = ' checked';
					}
					echo '<div class="'. $d['s']['c']['form-check'].' ph-radio-gift-box">';

					echo '<input class="phAOGift '. $d['s']['c']['form-check-input'].'"'
					.$checked
					.' data-type="phAOGiftType"'
					.' data-title="'. $title .'"'
					.' data-image="'. Uri::base(true) . '/' . $image .'"'
					.' data-date="'. $date .'"'
					.' data-description="'. base64_encode($description) .'"'
					.' data-class-name="'. $className .'"'
					.' type="radio" id="phGiftTypes'.$id.'" name="attribute[' . $v->id . '][' . $v2->id . ']" ' . $d['required']['attribute'] . $maxLength
					.' value="'.$id.'">';

					if (isset($v3['image_small']) && $v3['image_small'] != '') {
						echo '<div class="ph-radio-gift-image"><img src="'.Uri::base(true) . '/' . $v3['image_small'].'" alt="" /></div>';
					}

					echo '<label class="ph-radio-gift-title" for="phGiftTypes'.$id.'">';



					if (isset($v3['image_small']) && $v3['image_small'] != '') {
						echo $v3['title'];
					}

					echo '</label>';
					echo '</div>';
					$i++;

				}

			}


        break;

		case 23: // GIFT SENDER MESSAGE
            echo '<textarea class="phAOGift ph-attribute-textarea" data-type="phAOSenderMessage" name="attribute[' . $v->id . '][' . $v2->id . ']" ' . $d['required']['attribute'] . $maxLength . '></textarea>';
			$giftSenderMessageActive = 1;
        break;

		case 22: // GIFT Sender Name
            echo '<input class="phAOGift" data-type="phAOSenderName" type="text" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' />';
			$giftSenderNameActive = 1;
        break;
		case 21: // GIFT RECIPIENT EMAIL
            echo '<input class="phAOGift" data-type="phAORecipientEmail" type="email" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' />';

        break;

		case 20: // GIFT RECIPIENT Name
            echo '<input class="phAOGift" data-type="phAORecipientName" type="text" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' />';
			$giftRecipientNameActive = 1;
        break;

        default:

            echo '<input type="text" name="attribute[' . $v->id . '][' . $v2->id . ']" value="" ' . $d['required']['attribute'] . $maxLength . ' />';

        break;
    }
	echo '</div>';

    echo '</div>';
}


$d2 = array();
$d2['gift_class_name'] = $defaultClassName;
$d2['gift_image'] = $defaultImage;
$d2['gift_title'] = $defaultTitle;
$d2['gift_description'] = $defaultDescription;
$d2['discount'] = isset($d['priceitems']['bruttoformat']) ? $d['priceitems']['bruttoformat'] : '';
//$d2['valid_to'] = HTMLHelper::date($defaultDate, Text::_('DATE_FORMAT_LC3'));

if ($defaultDate == '' || $defaultDate == '0000-00-00 00:00:00') {
	$d2['valid_to'] = '';
} else {
	$d2['valid_to'] = HTMLHelper::date($defaultDate, Text::_('DATE_FORMAT_LC3'));
}

$d2['valid_from'] = '';
$d2['code'] = '';
$d2['gift_sender_name'] = $giftSenderNameActive == 1 ? '&nbsp;' : '';
$d2['gift_recipient_name'] = $giftRecipientNameActive == 1 ? '&nbsp;' : '';
$d2['gift_sender_message'] = $giftSenderMessageActive == 1 ? '&nbsp;' : '';
$d2['typeview'] = $d['typeview'];
$d2['product_id'] = $d['product_id'];
$d2['format']	= 'html';
if ($d['typeview'] == 'Item') {
	// Display only in Item View
	echo $layoutG->render($d2);
}


echo '</div>';// end attribute box
echo '</div>';// end attribute
?>
