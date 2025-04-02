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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
class JFormFieldPhocaStockstatus extends FormField
{
	protected $type 		= 'PhocaStockstatus';

	protected function getInput() {
		$db = Factory::getDBO();

		$man	= (string) $this->element['manager'];

		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_stock_statuses AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		// DEFAULT VALUES
	/*	if ($man == 'a' && $this->value == 0) {
			$this->value = 2; // set default value for products in stock
		} else if ($man == 'n' && $this->value == 0) {
			$this->value = 1;// set default value when there is no product in stock
		} */

		if (!empty($data)) {
			foreach($data as $k => $v) {
				$v->text = Text::_($v->text);
			}
		}

		array_unshift($data, HTMLHelper::_('select.option', '0', '- '.Text::_('COM_PHOCACART_SELECT_STOCK_STATUS').' -', 'value', 'text'));
		return HTMLHelper::_('select.genericlist',  $data,  $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id );
	}
}
?>
