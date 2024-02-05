<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\I18n;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Registry\Registry;

trait I18nAdminModelTrait
{
	private array $i18nFields = [];
    private string $i18nTable = '';

    private function getComponentParams(): Registry
    {
        static $params = null;

        if ($params === null) {
            $params = \PhocacartUtils::getComponentParameters();
        }

        return $params;
    }

    private function isI18n(): bool
    {
        return $this->getComponentParams()->get('i18n');
    }

    private function loadI18nItem(object $item)
    {
        if (!$this->isI18n()) {
            return;
        }

        if (!($item->id ?? null)) {
            return;
        }

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select(array_merge($this->i18nFields, ['language']))
            ->from($this->i18nTable)
            ->where($db->quoteName('id') . ' = ' . $item->id);

        $db->setQuery($query);
        $i18nData = $db->loadObjectList('language');

        foreach ($this->i18nFields as $field) {
            $defValue = $item->$field;

            $item->$field = [];
            foreach (I18nHelper::getI18nLanguages() as $language) {
                $item->$field[$language->lang_code] = $i18nData[$language->lang_code]->$field ?? null;
            }

            if ($item->$field[I18nHelper::getDefLanguage()] === null || $item->$field[I18nHelper::getDefLanguage()] === '') {
                $item->$field[I18nHelper::getDefLanguage()] = $defValue;
            }
        }
    }

    private function prepareI18nData(array &$data): array
    {
        if (!$this->isI18n()) {
            return [];
        }

        $i18nData = [];

        foreach (I18nHelper::getI18nLanguages() as $language) {
            $i18nData[$language->lang_code] = [];
        }

        foreach ($this->i18nFields as $field) {
            if ($value = $data[$field] ?? null) {
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    $i18nData[$language->lang_code][$field] = $value[$language->lang_code] ?? null;
                }

                $data[$field] = $value[I18nHelper::getDefLanguage()] ?? null;
            }
        }

        return $i18nData;
    }

    private function saveI18nData(int $id, array &$data): bool
    {
        if (!$this->isI18n()) {
            return true;
        }

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->delete($db->quoteName($this->i18nTable))
            ->where([
                $db->quoteName('id') . ' = ' . $id
            ]);

        $db->setQuery($query);
        if (!$db->execute()) {
            // TODO error
            return false;
        }

        foreach ($data as $language => $fields) {
            $fields['id'] = $id;
            $fields['language'] = $language;

            foreach ($fields as &$field) {
                if (!$field) {
                    $field = null;
                }
            }

            if (array_key_exists('alias', $fields) && !$fields['alias'] && array_key_exists('title', $fields) && $fields['title']) {
                $fields['alias'] = ApplicationHelper::stringURLSafe($fields['title']);
            }

            $fields = (object)$fields;
            $db->insertObject($this->i18nTable, $fields);
        }

        return true;
    }

}
