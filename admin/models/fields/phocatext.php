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
use Phoca\PhocaCart\I18n\I18nHelper;

class JFormFieldPhocaText extends TextField
{
	protected $type 		= 'PhocaText';
	protected $layout       = 'phocacart.form.field.phocatext';

    protected bool $showCopyButton = false;
    protected bool $showLinkButton = false;

    protected bool $showTranslation = false;

    protected bool $i18n = false;

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
            case 'i18n':
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
            case 'i18n':
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

            if (I18nHelper::isI18n()) {
                $this->i18n = isset($this->element['i18n']) ? strtolower($this->element['i18n']) === 'true' : false;
                $this->multiple = $this->i18n;
            } else {
                $this->i18n = false;
            }
        }

        return $result;
    }

    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if ($this->i18n) {
            $data['value'] = I18nHelper::checkI18nValue($data['value'] ?? null);
        }

        $extraData = [
            'showCopyButton' => $this->showCopyButton,
            'showLinkButton' => $this->showLinkButton,
            'showTranslation' => $this->showTranslation,
            'i18n' => $this->i18n,
            'languages' => I18nHelper::getEditLanguages($this->i18n),
            'defLanguage' => I18nHelper::getDefLanguage(),
        ];

        return array_merge($data, $extraData);
    }
}
