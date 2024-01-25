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

use Joomla\CMS\Form\Field\TextField;

class JFormFieldPhocaText extends TextField
{
	protected $type 		= 'PhocaText';
	protected $layout       = 'phocacart.form.field.phocatext';

    protected bool $showCopyButton = false;
    protected bool $showLinkButton = false;

    protected bool $showTranslation = false;

    protected function getRenderer($layoutId = 'default')
    {
        $renderer = parent::getRenderer($layoutId);
        $renderer->addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/layouts');
        return $renderer;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'showCopyButton':
            case 'showLinkButton':
            case 'showTranslation':
                return $this->$name;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'showCopyButton':
            case 'showLinkButton':
            case 'showTranslation':
                $this->$name = strtolower($value) === 'true';
                break;

            default:
                parent::__set($name, $value);
        }
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result == true) {
            $this->showCopyButton = isset($this->element['showCopyButton']) ? strtolower($this->element['showCopyButton']) === 'true' : false;
            $this->showLinkButton = isset($this->element['showLinkButton']) ? strtolower($this->element['showLinkButton']) === 'true' : false;
            $this->showTranslation = isset($this->element['showTranslation']) ? strtolower($this->element['showTranslation']) === 'true' : false;
        }

        return $result;
    }

    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $extraData = [
            'showCopyButton' => $this->showCopyButton,
            'showLinkButton' => $this->showLinkButton,
            'showTranslation' => $this->showTranslation,
        ];

        return array_merge($data, $extraData);
    }
}
