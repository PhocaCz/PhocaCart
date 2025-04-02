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
class JFormFieldPhocaTax extends FormField
{
	protected $type 		= 'PhocaTax';

	protected function getInput() {

		$lang 		= Factory::getLanguage();
		$lang->load('com_phocacart');

		$db = Factory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value, tax_rate, calculation_type'
		. ' FROM #__phocacart_taxes AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		if (!empty($data)) {

			// This field can be loaded e.g. in plugins
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php')) {
				// Joomla 5 and newer
				require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');
			} else {
				// Joomla 4
				if (!class_exists('PhocaCartLoader')) {
					require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
				}
				JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');
				phocacartimport('phocacart.price.price');
			}


			$price 	= new PhocacartPrice();
			foreach($data as $k => $v) {
				$data[$k]->text = Text::_($v->text) . ' (' . $price->getTaxFormat($v->tax_rate, (int)$v->calculation_type) . ')';
			}
		}

		array_unshift($data, HTMLHelper::_('select.option', '', '- '.Text::_('COM_PHOCACART_SELECT_TAX').' -', 'value', 'text'));
		return HTMLHelper::_('select.genericlist',  $data,  $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id );
	}
}
?>
