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
use Joomla\CMS\Language\Text;

class JFormFieldPhocaPlaceholder extends FormField
{
	protected $type 		= 'PhocaPlaceholder';

    protected function getLabel()
    {
        if (!$this->class) {
            return '';
        }

        if ($description = (string)$this->element['description']) {
            $class = (string)$this->element['contentClass'];
            $class = $class ? ' class="' . $class . '"' : '';
            $description = '<div' . $class . '>' . Text::_($description) . '</div>';
        }

        return '</div><div id="' . $this->class . '" class="' . $this->class . ' w-100">' . $description;
    }

    protected function getInput()
    {
        return '';
    }
}
