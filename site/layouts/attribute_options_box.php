<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
$layoutAtOS	= new FileLayout('attribute_options_select', null, array('component' => 'com_phocacart'));
$layoutAtOC	= new FileLayout('attribute_options_checkbox', null, array('component' => 'com_phocacart'));
$layoutAtOT	= new FileLayout('attribute_options_text', null, array('component' => 'com_phocacart'));
$layoutAtOG	= new FileLayout('attribute_options_gift', null, array('component' => 'com_phocacart'));

$d 				= $displayData;
$displayData 	= null;

if (!empty($d['attr_options']) && $d['hide_attributes'] != 1) {

	//PhocacartRenderJs::renderPhSwapImageInitialize($d['id'], $d['dynamic_change_image'], $d['init_type']);

	echo '<div class="ph-item-attributes-box" id="phItemAttributesBox">';
	echo '<h4 class="ph-available-options-title">'.Text::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';


	foreach ($d['attr_options'] as $k => $v) {




		// If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
		// Set jquery required validation, which should help to html 5 in case of checkboxes (see more info in the funtion)
		// TYPES SET for JQUERY require control: 4 5 6
		$req = PhocacartRenderJs::renderRequiredParts((int)$v->id, (int)$v->required );

		// OBSOLETE
		// HTML5 does not know to check checkboxes - if some value is set
		// CHECKBOX, CHECKBOX COLOR, CHECKBOX IMAGE
		/*if($v->type == 4 || $v->type == 5 || $v->type == 6) {
			//PhocacartRenderJs::renderCheckBoxRequired((int)$v->id, $d['init_type']);
			PhocacartRenderJs::renderCheckBoxRequired();
		}*/

		echo '<div class="ph-attribute-title">'.$v->title.$req['span'].'</div>';
		if(!empty($v->options)) {

			$d2							= $d;
			$d2['attribute']			= $v;
			$d2['required']				= $req;

            // EDIT PHOCACARTATTRIBUTE ATTRIBUTETYPE
			if ($v->type == 1 || $v->type == 2 || $v->type == 3 || $v->type == 13) {
				echo $layoutAtOS->render($d2);// SELECTBOX, SELECTBOX COLOR, SELECTBOX IMAGE, SELECTBOX TEXT
			} else if ($v->type == 4 || $v->type == 5 || $v->type == 6) {
				echo $layoutAtOC->render($d2);// CHECKBOX, CHECKBOX COLOR, CHECKBOX COLOR
			} else if ($v->type == 7 || $v->type == 8 || $v->type == 9 || $v->type == 10 || $v->type == 11 || $v->type == 12) {
				echo $layoutAtOT->render($d2);// TEXT, TEXT (COLOR PICKER)
			} else if ($v->type == 20) {
				echo $layoutAtOG->render($d2);// GIFT
			}
		}

		// SELECTBOX COLOR, SELECTBOX IMAGE SELECTBOX TEXT
		// OBSOLETE
		/*if ($v->type == 2 || $v->type == 3 || $v->type == 13) {
			echo PhocacartRenderJs::renderPhAttribute SelectBoxInitialize((int)$v->id, (int)$v->type, $d['typeview']);
		}*/

	}
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}



