<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;


class JFormRuleAlphanumeric extends FormRule
{

	protected $regex = '/[^a-zA-Z0-9]+/i';

	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null){

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value)){
			return true;
		}

		// Uncomment for Possible string length rule
		/*
		if (StringHelper::strlen($value) > 10) {
			return new \UnexpectedValueException(Text::_('The maximum allowable string length is 10 characters'));
		}
		*/
		if (empty($this->regex)) {
			throw new \UnexpectedValueException(sprintf('%s has invalid regex.', get_class($this)));
		}

		if (JCOMPAT_UNICODE_PROPERTIES) {
			$this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers . 'u';
		}

        if (!preg_match( $this->regex . $this->modifiers , $value)) {
            return true;
        }

        return new \UnexpectedValueException(Text::_('COM_PHOCACART_ONLY_ALPHANUMERIC_CHARACTERS_ARE_ALLOWED'));

	}
}
