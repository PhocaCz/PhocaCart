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
use Joomla\CMS\Form\Form;

trait I18nAdminModelTrait
{
	private array $i18nFields = [];
    private string $i18nTable = '';

    private function loadI18nItem(object $item)
    {
        if (!I18nHelper::isI18n()) {
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

    private function loadI18nArray(?array $items, string $i18nTable, array $i18nFields): ?array
    {
        if (!I18nHelper::isI18n()) {
            return $items;
        }

        if (!$items) {
            return $items;
        }

        $db = Factory::getDbo();

        foreach ($items as &$item) {
            $query = $db->getQuery(true)
                ->select(array_merge($i18nFields, ['language']))
                ->from($i18nTable)
                ->where($db->quoteName('id') . ' = ' . $item['id']);

            $db->setQuery($query);
            $i18nData = $db->loadObjectList('language');

            foreach ($i18nFields as $field) {
                $defValue = $item[$field];

                $item[$field] = [];
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    $item[$field][$language->lang_code] = $i18nData[$language->lang_code]->$field ?? null;
                }

                if ($item[$field][I18nHelper::getDefLanguage()] === null || $item[$field][I18nHelper::getDefLanguage()] === '') {
                    $item[$field][I18nHelper::getDefLanguage()] = $defValue;
                }
            }
        }

        return $items;
    }

    private function prepareI18nData(array &$data): array
    {
        return I18nHelper::prepareI18nData($data, $this->i18nFields);
    }

    private function saveI18nData(int $id, array &$data): bool
    {
        return I18nHelper::saveI18nData($id, $data, $this->i18nTable);
    }

    private function deleteI18nData(array $ids): bool
    {
        return I18nHelper::deleteI18nData($ids, $this->i18nTable);
    }

    private function prepareI18nForm(Form $form): Form
    {
        if (I18nHelper::isI18n()) {
            $form->setFieldAttribute('language', 'type', 'hidden');
            $form->setFieldAttribute('language', 'default', '*');
        }

        return $form;
    }
}
