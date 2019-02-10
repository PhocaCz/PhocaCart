<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartToken extends JFormField
{
	protected $type 		= 'PhocaCartToken';

	protected function getInput() {
		
		
		// Initialize variables.
		$html = array();
		
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$manager	= $this->element['manager'] ? $this->element['manager'] : '';
		
		$token = PhocacartUtils::getToken($manager);
	
		if ($this->value == '') {
			$this->value = htmlspecialchars((string)$token);
		}

		// Initialize JavaScript field attributes.
		$onchange = (string) $this->element['onchange'];
		$onchangeOutput = ' onChange="'.(string) $this->element['onchange'].'"';


		
		$html[] = '<div class="input-append">';
		$html[] = '<input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'. $this->value.'"' .
					' '.$class.$size.$disabled.$readonly.$onchangeOutput.$maxLength.' />';
		/*$html[] = '<a class="btn" title="'.JText::_('COM_PHOCADOWNLOAD_SET_TOKEN').'"'
					.' href="javascript:void(0);"'
					.' onclick="javascript:document.getElementById(\''.$this->id.'_id\').value = \''.$token.'\';return true;">'
					. JText::_('COM_PHOCADOWNLOAD_SET_TOKEN').'</a>';*/
		$html[] = '</div>'. "\n";
		return implode("\n", $html);
	
	}
}
?>