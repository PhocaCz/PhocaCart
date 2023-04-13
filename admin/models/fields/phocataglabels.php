<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\Field\ListField;

class JFormFieldPhocaTaglabels extends ListField
{
	protected $type 		= 'PhocaTaglabels';

	protected function getOptions()
	{
		$options = (array)PhocacartTag::getAllTagsList('id', 1);
		return array_merge(parent::getOptions(), $options);
	}
/*
	protected function getInput() {

		$id 	= (int) $this->form->getValue('id');
		$javascript		= '';
		$required		= $this->required;// accept dynamically added required
		$multiple		= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class			= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="form-select"';
		$attr			= '';
		$attr			.= $class . ' ';

		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}
		$attr			.= $javascript . ' ';



		$activeTags = array();
		if ((int)$id > 0) {
			$activeTags	= PhocacartTag::getTagLabels($id, 1);
		}

		$tags 				= PhocacartTag::getAllTagsList('id', 1);
		$data               = $this->getLayoutData();
		$data['options']    = (array)$tags;
		$data['value']      = $activeTags;

		return $this->getRenderer($this->layout)->render($data);

		//return PhocacartTag::getAllTagsSelectBox($this->name, $this->id, $activeTags, NULL, 'id', 1, $attr );
	}*/
}
?>
