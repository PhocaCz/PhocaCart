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

class JFormFieldPhocacartCountry extends FormField
{
	protected $type 		= 'PhocacartCountry';

	protected function getInput() {

		$db = Factory::getDBO();
		$attr 		= '';
		$attr		.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr		.= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$attr		.= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : 'class="form-select"';
		$attr		.= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$attr		.= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr 		.= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'" ' : ' ';
		$multiple	= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$manager	= $this->element['manager'] ? $this->element['manager'] : '';

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_countries AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();


		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;


		array_unshift($data, HTMLHelper::_('select.option', '', '- '.Text::_('COM_PHOCACART_SELECT_COUNTRY').' -', 'value', 'text'));

		return HTMLHelper::_('select.genericlist',  $data,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
	}
}
?>
