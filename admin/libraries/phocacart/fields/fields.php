<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;

class PhocacartFields
{

    private static function getProductFieldsGroups(): array
    {
        static $groups = null;
        if ($groups === null) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__fields_groups')
                ->where('context = ' . $db->quote('com_phocacart.phocacartitem'));
            $db->setQuery($query);
            $groups = $db->loadObjectList('id');
        }

        return $groups;
    }
    public static function getProductFields(object $product, bool $prepareValue = true): array
    {
        $fields = FieldsHelper::getFields('com_phocacart.phocacartitem', $product, $prepareValue);
        $fieldsGroups = self::getProductFieldsGroups();

        $groups = [];

        foreach ($fields as $field) {
            if (!isset($groups[$field->group_id])) {
                $group = [
                    'id' => $field->group_id,
                    'title' => $field->group_title,
                    'ordering' => null,
                    'access' => $field->group_access,
                    'state' => $field->group_state,
                    'note' => $field->group_note,
                    'fields' => [],
                ];

                if ($field->group_id && isset($fieldsGroups[$field->group_id])) {
                    $group['ordering'] = $fieldsGroups[$field->group_id]->ordering;
                }
                $groups[$field->group_id] = (object)$group;
            }

            $groups[$field->group_id]->fields[] = $field;
        }

        usort($groups, function(object $a, object $b) {
            if ($a->ordering === null) {
                return -1;
            } elseif ($a->ordering === null) {
                return 1;
            } elseif ($a->ordering === $b->ordering) {
                return 0;
            } else  {
                return $a->ordering > $b->ordering ? 1 : -1;
            }
        });

        return $groups;
    }

    public static function getAllFields($context = 'com_phocacart.phocacartitem'): array
    {
        static $fields = null;
        if ($fields === null) {
            $fields = FieldsHelper::getFields($context);
        }

        return $fields;
    }

    public static function getFieldId(int $id): ?object
    {
        $fields = array_filter(self::getAllFields(), function ($field) use ($id) {
            return $field->id === $id;
        });

        if ($fields) {
            return reset($fields);
        }

        return null;
    }

    public static function getAllFieldsValues(int $fieldId, bool $onlyAvailableProducts = false, string $lang = '', array $filterProducts = []): array
    {
        $field = self::getFieldId($fieldId);
        if (!$field) {
            return [];
        }

        $wheres   = [];
        $lefts    = [];
        $db       = Factory::getDbo();
        $wheres[] = 'fv.field_id = ' . $fieldId;

        $productTableAdded = 0;
        // TODO solve very slow join
        $onlyAvailableProducts = false;
        if ($onlyAvailableProducts) {
            if ($lang != '' && $lang != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('p.language', $lang);
            }

            $lefts[]           = ' #__phocacart_products AS p ON fv.item_id = p.id';
            $productTableAdded = 1;
            $rules             = PhocacartProduct::getOnlyAvailableProductRules();
            $wheres            = array_merge($wheres, $rules['wheres']);
            $lefts             = array_merge($lefts, $rules['lefts']);
        }

        if (!empty($filterProducts)) {
            $productIds = implode(',', $filterProducts);
            $wheres[]   = 'p.id IN (' . $productIds . ')';
            if (!$productTableAdded) {
                $lefts[] = ' #__phocacart_products AS p ON fv.item_id = p.id';
            }
        }

        $q = ' SELECT DISTINCT fv.value as title, fv.value as alias'
            . ' FROM  #__fields_values AS fv'
            . (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
            . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        $db->setQuery($q);

        $items = $db->loadObjectList();

        if ($field->type === 'list') {
            $options = [];
            foreach ($field->fieldparams->get('options') as $option) {
                $options[$option->value] = $option->name;
            }

            array_walk($items, function (&$item) use ($options) {
                if (isset($options[$item->alias]))
                    $item->title = $options[$item->alias];
            });
        }

        usort($items, function ($a, $b) {
            return strcmp($a->title, $b->title);
        });

        return $items;
    }

    public static function prepareBatchForm(string $context, Form $form)
    {
        $fields = FieldsHelper::getFields($context, new CMSObject());

        if (!$fields) {
            return true;
        }

        $batchFields = [];
        foreach ($fields as $field) {
            if ($field->params->get('allow_batch')) {
                $batchFields[] = $field;
            }
        }

        if (!$batchFields) {
            return true;
        }

        // Creating the dom
        $xml        = new \DOMDocument('1.0', 'UTF-8');
        $fieldsNode = $xml->appendChild(new \DOMElement('form'))->appendChild(new \DOMElement('fields'));
        $fieldsNode->setAttribute('name', 'batch');
        $fieldsNode = $fieldsNode->appendChild(new \DOMElement('fields'));
        $fieldsNode->setAttribute('name', 'com_fields');
        $fieldset = $fieldsNode->appendChild(new \DOMElement('fieldset'));
        $fieldset->setAttribute('name', 'params');

        foreach ($batchFields as $field) {
            try {
                $field->params->set('showon', '_custom_fields:1');
                Factory::getApplication()->triggerEvent('onCustomFieldsPrepareDom', [$field, $fieldset, $form]);
                //$form->setFieldAttribute($field->name, 'showon', '_custom_fields:1', $field->group);
            }
            catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $form->load($xml->saveXML());

        foreach ($batchFields as $field) {
            $form->setFieldAttribute($field->name, 'showon', '_custom_fields:1', $field->group ?? null);
        }

        return true;
    }

    public static function saveFieldValue(string $context, int $itemId, string $fieldName, $value): bool
    {
        $fields = self::getAllFields($context);

        /** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
        $model = Factory::getApplication()->bootComponent('com_fields')->getMVCFactory()
            ->createModel('Field', 'Administrator', ['ignore_request' => true]);

        // If no value set (empty) remove value from database
        if (is_array($value) ? !count($value) : !strlen($value)) {
            $value = null;
        }

        // JSON encode value for complex fields
        if (is_array($value) && (count($value, COUNT_NORMAL) !== count($value, COUNT_RECURSIVE) || !count(array_filter(array_keys($value), 'is_numeric')))) {
            $value = json_encode($value);
        }

        foreach ($fields as $field) {
            if ($field->name == $fieldName) {
                $model->setFieldValue($field->id, $itemId, $value);
                return true;
            }
        }

        return false;
    }
}

