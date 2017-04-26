<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 				= $displayData;
$displayData 	= null;
$v 				= $d['attribute'];
?>
<div id="phItemBoxAttribute<?php echo (int)$v->id; ?>">
	<select id="phItemAttribute<?php echo (int)$v->id; ?>" name="attribute[<?php echo (int)$v->id; ?>]" class="form-control chosen-select ph-item-input-set-attributes" <?php echo $d['required']['attribute']; ?>>
		<option value=""><?php echo JText::_('COM_PHOCACART_SELECT_OPTION'); ?></option><?php

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
		if (isset($v2->image) && $v2->image != '') {
			$imageO 	= PhocacartImage::getThumbnailName($d['pathitem'], $v2->image, 'large');
			$linkO 		= JURI::base(true).'/'.$imageO->rel;
			if (JFile::exists($imageO->abs)) {
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
		$linkI 		= JURI::base(true).'/'.$d['pathitem']['orig_rel'].'/'.$v2->image_small;
		$attrO		.= ' data-image="'.strip_tags($linkI).'"';
	}
	
	// SELECTED SOME VALUE? 
	if ($v2->default_value == 1) {
		$attrO		.= ' selected="seleced"';
	}
	
	echo '<option '.$attrO.' value="'.$v2->id.'">'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')</option>';
}

?>
	</select>
</div>
<div id="phItemHiddenAttribute<?php echo (int)$v->id; ?>" style="display:none;"></div>