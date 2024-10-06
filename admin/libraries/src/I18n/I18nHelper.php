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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

abstract class I18nHelper
{
    public static function isI18n(): bool
    {
        static $isI18n = null;

        if ($isI18n === null) {
            $params = ComponentHelper::getParams('com_phocacart');
            $isI18n = !!$params->get('i18n');
        }

        return $isI18n;
    }

    public static function useI18n(): bool
    {
        static $useI18n = null;

        if ($useI18n === null) {
            $useI18n = Factory::getApplication()->isClient('site') && self::isI18n();
        }

        return $useI18n;
    }

    public static function getI18nLanguages(): array
    {
        $languages = LanguageHelper::getContentLanguages([0, 1], true, 'lang_code', 'ordering', 'asc');
        $defLanguage = self::getDefLanguage();
        uasort($languages, function($a, $b) use ($defLanguage) {
            if ($a->lang_code === $defLanguage) return -1;
            if ($b->lang_code === $defLanguage) return 1;
            if ($a->ordering === $b->ordering) return 0;
            return $a->ordering > $b->ordering ? 1 : -1;
        });
        return $languages;
    }

    public static function getDefLanguage(): string
    {
        static $defLanguage = null;

        if ($defLanguage === null) {
            $params = ComponentHelper::getParams('com_phocacart');
            $defLanguage = $params->get('i18n_language', ComponentHelper::getParams('com_languages')->get('site', 'en-GB'));
        }

        return $defLanguage;
    }

    public static function getI18nLanguage(): string
    {
        static $i18nLanguage = null;

        if ($i18nLanguage === null) {
            $i18nLanguage = Factory::getApplication()->getLanguage()->getTag();
        }

        return $i18nLanguage;
    }

    public static function prepareI18nData(array &$data, array $i18nFields): array
    {
        if (!self::isI18n()) {
            return [];
        }

        $i18nData = [];

        foreach (self::getI18nLanguages() as $language) {
            $i18nData[$language->lang_code] = [];
        }

        foreach ($i18nFields as $field) {
            if ($value = $data[$field] ?? null) {
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    $i18nData[$language->lang_code][$field] = $value[$language->lang_code] ?? null;
                }

                $data[$field] = $value[I18nHelper::getDefLanguage()] ?? null;
            }
        }

        return $i18nData;
    }

    public static function saveI18nData(int $id, array &$data, string $i18nTable): bool
    {
        if (!I18nHelper::isI18n()) {
            return true;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->delete($db->quoteName($i18nTable))
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

            // Specifications has alias also for value
            if (array_key_exists('alias_value', $fields) && !$fields['alias_value'] && array_key_exists('value', $fields) && $fields['value']) {
                $fields['alias_value'] = ApplicationHelper::stringURLSafe($fields['value']);
            }

            $fields = (object)$fields;
            $db->insertObject($i18nTable, $fields);
        }

        return true;
    }

    public static function deleteI18nData(array $ids, string $i18nTable): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->delete($i18nTable)
            ->whereIn($db->quoteName('id'), $ids);

        $db->setQuery($query);
        return $db->execute();
    }

    public static function checkI18nValue($value)
    {
        if (!self::isI18n()) {
            return $value;
        }

        if (!is_array($value)) {
            $defValue = $value;
            $languages = self::getI18nLanguages();

            $value = [];
            foreach ($languages as $language) {
                $value[$language->lang_code] = null;
            }

            $value[self::getDefLanguage()] = $defValue;
        }

        return $value;
    }

    public static function getEditLanguages(bool $useI18n)
    {
        if (!$useI18n || !self::isI18n()) {
            return [
                (object)[
                    'lang_code' => null,
                ]
            ];
        }

        return I18nHelper::getI18nLanguages();
    }

    public static function sqlJoin(string $i18nTable, string $mainTableAlias = 'a'): string
    {
        // space at the beginning and end in purpose, do not delete
        if (!self::useI18n()) {
            return ' ';
        }

        $i18nTableAlias = 'i18n_'.$mainTableAlias;

        $db = Factory::getDbo();
        return ' LEFT JOIN ' . $i18nTable . ' AS ' . $i18nTableAlias . ' ON ' . $i18nTableAlias . '.id = ' . $mainTableAlias . '.id AND ' . $i18nTableAlias . '.language = ' . $db->quote(self::getI18nLanguage()) . ' ';
    }


    /**
     * @param array $columns            List of columns to be loaded in specific langauge
     * @param string $tableAlias        Main table alias
     * @param string $columnAliasPrefix Final column alias prefix, e.g. "shipping" will be "shippingtitle" based on column: coalesce(i18n_s.title, s.title) as shippingtitle
     * @param string $type              Different types like default, GROUP_CONCAT(...), GROUP_CONCAT(DISTINCT ...)
     * @param string $prefix            If this SQL entry is a part of whole SQL query, add e.g. "," as prefix
     * @param string $suffix            If this SQL entry is a part of whole SQL query, add e.g. "," as suffix
     * @param bool $skipAs              from: COALESCE(i18n_s.title, s.title) AS title to: COALESCE(i18n_s.title, s.title) - in different seach clauses
     *
     * @return string
     *
     * @since 5.0.0
     */
    public static function sqlCoalesce(array $columns, string $mainTableAlias = 'a', string $columnAliasPrefix = '', string $type = '', string $prefix = '', string $suffix = '', bool $skipAs = false, $forceI18n = false): string
    {

        $i18nTableAlias  = 'i18n_'. $mainTableAlias;
        $columnsFallback = ['title', 'alias', 'alias_value'];// Could be possible parameter in options - these columns get coalesce

        $output = '';
        $output .= $prefix;

        switch($type) {
            case 'groupconcat':
                $columnPrefix = 'GROUP_CONCAT(';
                $columnSuffix = ')';
            break;

            case 'groupconcatdistinct':
                $columnPrefix = 'GROUP_CONCAT(DISTINCT ';
                $columnSuffix = ')';
            break;

            case 'concatid':
                // I18nHelper::sqlCoalesce(['alias'], 'm', '', 'concatid')
                // CONCAT(m.id, \'-\', COALESCE(i18n_m.alias, m.alias)) AS alias
                $columnPrefix = 'CONCAT('.$mainTableAlias.'.id, \'-\', ';
                $columnSuffix = ')';
            break;

            case 'concatparameters':
                // I18nHelper::sqlCoalesce(['alias'], 'm', '', 'concatid')
                // CONCAT(\'s[\', COALESCE(i18n_s.alias, s.alias), \']\')  AS parameteralias
                $columnPrefix = 'CONCAT(\'s[\', ';
                $columnSuffix = ', \']\')';
            break;
            case 'concatparametera':
                $columnPrefix = 'CONCAT(\'a[\', ';
                $columnSuffix = ', \']\')';
            break;
            case '':
            default:
                $columnPrefix = '';
                $columnSuffix = '';
            break;

        }

        $useI18n = self::useI18n();
        $columnsOutput = [];
        if (!empty($columns)) {
            foreach($columns as $column) {
                if ($useI18n || $forceI18n) {

                    if (in_array($column, $columnsFallback)) {
                        $columnsOutput[] = $columnPrefix . 'COALESCE(' . $i18nTableAlias  . '.' . $column. ', ' . $mainTableAlias. '.' . $column . ')' . $columnSuffix
                            . (!$skipAs ? ' AS ' . $columnAliasPrefix . $column : '');
                    } else {
                        $columnsOutput[] = $columnPrefix . $i18nTableAlias  . '.' . $column . $columnSuffix
                            . (!$skipAs ? ' AS ' . $columnAliasPrefix . $column : '');
                    }

                } else {
                    $columnsOutput[] =  $columnPrefix . $mainTableAlias. '.' . $column . $columnSuffix
                        . (!$skipAs ? ' AS ' . $columnAliasPrefix . $column : '');
                }
            }

            $output .= implode(', ', $columnsOutput);
        }


        $output .= $suffix;

        return $output;
    }

    public static function query(QueryInterface $query, string $i18nTable, array $fallbackColumns, array $additionalColumns = [], string $mainTableAlias = 'a', ?string $lang = null): void
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $i18nAlias = 'i18n_' . $mainTableAlias;
        if (!$lang || $lang === '*') {
            $lang = self::getI18nLanguage();
        }

        if (self::isI18n()) {
            $query->join('LEFT', $db->quoteName($i18nTable, $i18nAlias),
                $i18nAlias . '.id = ' . $mainTableAlias . '.id AND ' . $i18nAlias . '.language = ' . $db->quote($lang));

            foreach ($fallbackColumns as $column => $alias) {
                $query->select('coalesce(' . $i18nAlias . '.' . $column . ', ' . $mainTableAlias . '.' . $column .') AS ' . ($alias ?: $column));
            }

            foreach ($additionalColumns as $column => $alias) {
                $query->select($i18nAlias . '.' . $column .' AS ' . ($alias ?: $column));
            }
        } else {
            foreach ($fallbackColumns as $column => $alias) {
                $query->select($mainTableAlias . '.' . $column .' AS ' . ($alias ?: $column));
            }

            foreach ($additionalColumns as $column => $alias) {
                $query->select($mainTableAlias . '.' . $column .' AS ' . ($alias ?: $column));
            }
        }
    }

    public static function getEditorIcon($langCode, $value): string
    {
        $defLanguage = self::getDefLanguage();
        $i18nValue = $value[$langCode];
        $defValue = $value[$defLanguage];
        $description = 'aria-description="' . self::getEditorIconTitle($langCode, $value). '"';
        if ($langCode === $defLanguage) {
            return '<span class="icon icon-language text-info" ' . $description. '></span>';
        } elseif (!$defValue && !$i18nValue) {
            return '<span class="icon fa fa-ban" ' . $description. '></span>';
        } elseif ($defValue && $i18nValue) {
            return '<span class="icon icon-ok text-success" ' . $description. '></span>';
        } elseif ($defValue) {
            return '<span class="icon icon-error text-danger" ' . $description. '></span>';
        } else {
            return '<span class="icon icon-error text-warning" ' . $description. '></span>';
        }
    }

    public static function getEditorIconTitle($langCode, $value): string
    {
        $defLanguage = self::getDefLanguage();
        $i18nValue = $value[$langCode];
        $defValue = $value[$defLanguage];
        if ($langCode === $defLanguage) {
            return Text::_('COM_PHOCACART_I18N_DEF_LANGUAGE');
        } elseif (!$defValue && !$i18nValue) {
            return Text::_('COM_PHOCACART_I18N_EMPTY');
        } elseif ($defValue && $i18nValue) {
            return Text::_('COM_PHOCACART_I18N_TRANSLATED');
        } elseif (!$defValue) {
            return Text::_('COM_PHOCACART_I18N_MISSING_ORIGINAL');
        } else {
            return Text::_('COM_PHOCACART_I18N_MISSING_TRANSLATION');
        }
    }

    public static function associationsEnabled(): bool
    {
        return !self::isI18n() && Associations::isEnabled();
    }
}
