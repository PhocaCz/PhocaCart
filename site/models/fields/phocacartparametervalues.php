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
use Joomla\CMS\Form\FormField;

require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/bootstrap.php');

class JFormFieldPhocaCartParameterValues extends FormField
{
	protected $type 		= 'PhocaCartParameterValues';

	public function __construct($form = null)
	{
		parent::__construct($form);

		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_phocacart');
	}

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
