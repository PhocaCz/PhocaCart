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

class JFormFieldPhocaManufacturer extends FormField
{
	protected $type 		= 'PhocaManufacturer';

	protected function getInput() {

		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$class		= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="form-select"';
		$multiple	= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class		= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="form-select"';
		$attr		= '';
		$attr		.= $class . ' ';
		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}

		$attr 		.= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'" ' : ' ';

		$db = Factory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_manufacturers AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		if (!$multiple) {

			array_unshift($items, HTMLHelper::_('select.option', '', '- '.Text::_('COM_PHOCACART_SELECT_MANUFACTURER').' -', 'value', 'text'));
			return HTMLHelper::_('select.genericlist',  $items,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
		} else {


			$data               = $this->getLayoutData();
			$data['options']    = (array)$items;
			$data['value']      = $this->value;

			return $this->getRenderer($this->layout)->render($data);
		}

	}
}
?>
