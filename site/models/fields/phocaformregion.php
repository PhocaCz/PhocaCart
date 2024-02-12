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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldPhocaFormRegion extends FormField
{
	protected $type 		= 'PhocaFormRegion';

	protected function getInput() {

		$db = Factory::getDBO();


		$country = $this->form->getValue('country');
		$countryPhs = $this->form->getValue('country_phs');
		$countryPhb = $this->form->getValue('country_phb');
		$countryId = 0;
		if ($this->id == 'jform_region' && (int)$country > 0) {
			$countryId = (int)$country;
		}

		if ($this->id == 'jform_region_phs' && (int)$countryPhs > 0) {
			$countryId = (int)$countryPhs;
		}
		if ($this->id == 'jform_region_phb' && (int)$countryPhb > 0) {
			$countryId = (int)$countryPhb;
		}



		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_regions AS a'
		. ' WHERE a.published = 1';
		if ($countryId > 0) {
			$query .= ' AND a.country_id = '.(int)$countryId;
		}
		$query .= ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();


		// Set default value in case, there is no value
		// Check if the default value belongs to country
		if (($this->value == 0 || $this->value == '') && isset($this->default) && (int)$this->default > 0) {
			$queryR = 'SELECT a.id'
			. ' FROM #__phocacart_regions AS a'
			. ' WHERE a.published = 1';
			if ($countryId > 0) {
				$queryR .= ' AND a.country_id = '.(int)$countryId;
			}
			$queryR .= ' ORDER BY a.ordering';
			$db->setQuery( $queryR );
			$dataR = $db->loadColumn();

			if (!empty($dataR)) {

				if (in_array($this->default, $dataR)) {
					$this->value = $this->default;
				}
			}
		}


		$attr = '';
		$attr .= !empty($this->class) ? ' class="' . $this->class . ' form-select chosen-select ph-input-select-region"' : 'class="form-select chosen-select ph-input-select-region"';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';


		array_unshift($data, HTMLHelper::_('select.option', '', '-&nbsp;'.Text::_('COM_PHOCACART_SELECT_REGION').'&nbsp;-', 'value', 'text'));

		return HTMLHelper::_('select.genericlist',  $data,  $this->name, trim($attr), 'value', 'text', $this->value, $this->id );
	}
}
?>
