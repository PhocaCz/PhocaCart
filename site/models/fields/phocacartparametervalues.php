<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

if (! class_exists('PhocacartParameter')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/parameter/parameter.php');
}

$lang = Factory::getLanguage();
$lang->load('com_phocacart');

use Joomla\CMS\Form\FormField;
defined('_JEXEC') or die();

class JFormFieldPhocaCartParameterValues extends FormField
{
	protected $type 		= 'PhocaCartParameterValues';

	protected function getInput() {

		$id = (int) $this->form->getValue('id');
		$parameterId = (int)$this->element['parameterid'];

		$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : ' class="form-control"';
		$attr 	.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		//$attr 	.= ((string) $this->element['required'] == 'true') ? ' required aria-required="true"' : '';
		$attr 	.= $this->required ? ' required aria-required="true"' : '';
		$attr 	.= ((string) $this->element['multiple'] == 'true') ? ' multiple="multiple"' : '';

		$activeParameters = array();
		if ((int)$id > 0) {
			$activeParameters	= PhocacartParameter::getParameterValues($id, $parameterId, 1);
		}


		return PhocacartParameter::getAllParameterValuesSelectBox($this->name, $this->id, $parameterId, $activeParameters, $attr, 'a.id');
	}
}
?>
