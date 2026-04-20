<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;

class JFormRulePhocaCartMaxlength extends FormRule
{


	public function test(SimpleXMLElement $element, $value, $group = null, Joomla\Registry\Registry $input = null, Joomla\CMS\Form\Form $form = null)
	{
		
        if (isset($element['maxlength'])) {
                $maxLength = (int)$element['maxlength'];
                $stringLength = (int)StringHelper::strlen($value);

                if ($stringLength > $maxLength) {

                    $app = Factory::getApplication();
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_VALUE_TOO_LONG' ), 'warning');
                    return false;

                }
        }
		return true;
	}
}
