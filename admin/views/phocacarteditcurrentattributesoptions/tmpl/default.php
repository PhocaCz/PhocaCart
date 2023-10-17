<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


if ($this->p['typeview'] == 'option') {
	$parentTitle = '';
	if ($this->p['parentattributetitle'] != '') {
		$parentTitle =  ' ('.$this->p['parentattributetitle'].')';
	}

	echo '<h4>'.Text::_('COM_PHOCACART_ACTIVE_OPTIONS_IN_SYSTEM_FOR_CURRENT_ATTRIBUTE') . $parentTitle .'</h4>';

	if (!empty($this->items)) {

		$titleField = str_replace('_current_options', '_title', $this->p['field']);
		$aliasField = str_replace('_current_options', '_alias', $this->p['field']);
		$imageField = str_replace('_current_options', '_image', $this->p['field']);
		$imageSmallField = str_replace('_current_options', '_image_small', $this->p['field']);
		$imageMediumField = str_replace('_current_options', '_image_medium', $this->p['field']);
		$colorField = str_replace('_current_options', '_color', $this->p['field']);

		echo '<div class="ph-attributes-options-box">';

		foreach ($this->items as $k => $v) {
			$onclick= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($titleField, 'alphanumeric2').'\', \'' .$v['title'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($aliasField, 'alphanumeric2').'\', \'' .$v['alias'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($imageField, 'alphanumeric2').'\', \'' .$v['image'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($imageSmallField, 'alphanumeric2').'\', \'' .$v['image_small'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($imageMediumField, 'alphanumeric2').'\', \'' .$v['image_medium'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($colorField, 'alphanumeric2').'\', \'' .$v['color'].'\');';


			echo '<a href="#" onclick="'.$onclick.'"><span class="badge bg-primary">'.$v['title'].'</span></a>';
		}
		echo '</div>';
	} else {
		echo '<div>'.Text::_('COM_PHOCACART_NO_ACTIVE_OPTIONS_FOUND_FOR_ATTRIBUTE'). '' . $parentTitle .'</div>';
	}



} else {
	echo '<h4>'.Text::_('COM_PHOCACART_ACTIVE_ATTRIBUTES_IN_SYSTEM').'</h4>';

	if (!empty($this->items)) {

		$titleField = str_replace('_current_attributes', '_title', $this->p['field']);
		$aliasField = str_replace('_current_attributes', '_alias', $this->p['field']);

		echo '<div class="ph-attributes-options-box">';

		foreach ($this->items as $k => $v) {
			$onclick= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($titleField, 'alphanumeric2').'\', \'' .$v['title'].'\');';
			$onclick .= 'if (window.parent) window.parent.phAddValueCurrentAttributesOptions(\''.PhocacartText::filterValue($aliasField, 'alphanumeric2').'\', \'' .$v['alias'].'\');';
			echo '<a href="#" onclick="'.$onclick.'"><span class="badge bg-primary">'.$v['title'].'</span></a>';
		}
		echo '</div>';
	} else {
		echo '<div>' . Text::_('COM_PHOCACART_NO_ACTIVE_ATTRIBUTES_FOUND') . '</div>';
	}


}

?>
