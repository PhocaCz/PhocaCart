<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldPhocaUnit extends FormField
{
	protected $type 		= 'PhocaUnit';

	protected function getInput() {

		$units = array();
		if ($this->element['table']) {
			$units = PhocacartUtilsSettings::getUnit(0, $this->element['table']);
		}

		$unitsA = array();
		foreach ($units as $k => $v) {
			$unitsA[$k] = Text::_($v[0]);
		}


		return HTMLHelper::_('select.genericlist',  $unitsA,  $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id );
	}
}
?>
