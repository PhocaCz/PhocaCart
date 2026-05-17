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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

require_once (JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');

class JFormFieldPhocacartOrderstatus extends ListField
{
	protected $type 		= 'PhocacartOrderstatus';

	protected function getOptions()
	{
        // This form field is even used in plugins, so load the lang
        $lang 		= Factory::getLanguage();
        $lang->load('com_phocacart');
        $lang->load('com_phocacart.sys');

        $db = Factory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value'
			. ' FROM #__phocacart_order_statuses AS a'
			. ' WHERE a.published = 1'
			. ' ORDER BY a.ordering';
		$db->setQuery($query);
		$options = $db->loadObjectList();

        foreach ($options as $option) {
            $option->text = Text::_($option->text);
        }

        if ($this->multiple) {
           if($this->layout == "joomla.form.field.list-fancy-select"){
               // no additional values
            } else {
                $options = array_merge([
                    (object)['value' => 0, 'text' => Text::_('COM_PHOCACART_NONE')],
                    (object)['value' => -1, 'text' => Text::_('COM_PHOCACART_ALL')],
                ], $options);
            }
        } else {
            $type = $this->element['typemethod'] ?? 0;
            if ($type == 1) {
                $options = array_merge([
                    (object)['value' => 0, 'text' => Text::_('COM_PHOCACART_NONE')],
                ], $options);
            } else if ($type == 2) {
                $options = array_merge([
                    (object)['value' => '', 'text' => ' - ' . Text::_('COM_PHOCACART_OPTION_SELECT_ORDER_STATUS') . ' - '],
                ], $options);
            }
        }

		return array_merge(parent::getOptions(), $options);
	}
}

