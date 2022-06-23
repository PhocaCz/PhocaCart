<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
jimport('joomla.form.formfield');

class JFormFieldPhocaColorText extends FormField
{
	public $type = 'PhocaColorText';

	protected function getInput() {
		$html 			= array();
		//$managerOutput	= $this->element['manager'] ? '&amp;manager='.(string)$this->element['manager'] : '';
		//$group 			= PhocacartUtilsSettings::getManagerGroup((string) $this->element['manager']);
		$textButton		= 'COM_PHOCACART_FORM_SELECT_COLOR';

		$document		= Factory::getDocument();

		HTMLHelper::stylesheet( 'media/com_phocacart/js/jcp/picker.css' );
		$document->addScript(Uri::root(true).'/media/com_phocacart/js/jcp/picker.js');

		$onchange 		= (string) $this->element['onchange'];
		$size     		= ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    		= ($v = $this->element['class']) ? ' class="' . $v . ' phColorText"' : 'class="text_area phColorText"';
		$required 		= ($v = $this->element['required']) ? ' required="required"' : '';


		HTMLHelper::_('jquery.framework');

		$idA = 'phColorText';
		$idAC = $idA.'PickerName'. $this->id;

		$html[] = '<span class="input-append input-group"><input type="text" ' . $required . ' id="' . $this->id . '" name="' . $this->name . '"'
			. ' value="' . $this->value . '"' . $size . $class . ' style="background-color: '.$this->value.'" />';
		// data-id does not work by dynamically added form fields so we need to get the id which is stored in input before the button
		$html[] = ' <a href="#'.$idAC.'" role="button" class="btn btn-primary '.$idA.'PickerButton">'
			. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a></span>';

		return implode("\n", $html);
	}
}
?>
