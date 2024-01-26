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

use Joomla\CMS\Form\Field\EditorField;
use Joomla\CMS\Language\LanguageHelper;

class JFormFieldPhocaEditor extends EditorField
{
	public $type 		= 'PhocaEditor';

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
            case 'i18n':
                return $this->$name;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        switch ($name) {
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
            $params = PhocacartUtils::getComponentParameters();
            if ($params->get('i18n')) {
                $this->i18n = isset($this->element['i18n']) ? strtolower($this->element['i18n']) === 'true' : false;
            } else {
                $this->i18n = false;
            }

            $this->multiple = $this->i18n;
            if ($this->i18n) {
                $this->renderLayout = 'phocacart.form.renderi18nfield';
            }
        }

        return $result;
    }

    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if ($this->i18n) {
            $languages = LanguageHelper::getLanguages();
        } else {
            $languages = [
                (object)[
                    'lang_code' => null,
                ]
            ];
        }

        $extraData = [
            'i18n' => $this->i18n,
            'languages' => $languages,
        ];

        return array_merge($data, $extraData);
    }

    protected function getInput()
    {
        if ($this->i18n) {
            $languages = LanguageHelper::getLanguages();
        } else {
            $languages = [
                (object)[
                    'lang_code' => null,
                ]
            ];
        }

        $params = [
            'autofocus' => $this->autofocus,
            'readonly'  => $this->readonly || $this->disabled,
            'syntax'    => (string) $this->element['syntax'],
        ];

        $editor = $this->getEditor();
        $editors = [];
        foreach ($languages as $language) {
            $editors[$language->lang_code] = $editor->display(
                $this->name . ($this->i18n ? '[' . $language->lang_code . ']' : ''),
                htmlspecialchars($this->i18n ? $this->value[$language->lang_code] : $this->value, ENT_COMPAT, 'UTF-8'),
                $this->width,
                $this->height,
                $this->columns,
                $this->rows,
                $this->buttons ? (\is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false,
                $this->id . ($this->i18n ? '-' . $language->lang_code : ''),
                $this->asset,
                $this->form->getValue($this->authorField),
                $params
            );
        }

        return $editors;
    }
}
