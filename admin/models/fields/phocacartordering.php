<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

class JFormFieldPhocacartOrdering extends ListField
{
	protected $type = 'PhocacartOrdering';

    protected function getOptions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('ordering AS value, title AS text')
            ->order('ordering');

        $table = strtolower($this->element['table']);
        switch ($table) {
            case "payment":
                $query->from('#__phocacart_payment_methods');
                break;

            case "status":
                $query->from('#__phocacart_order_statuses');
                break;

            case "stockstatus":
                $query->from('#__phocacart_stock_statuses');
                break;

            case "country":
                $query->from('#__phocacart_countries');
                break;

            case "region":
                $query->from('#__phocacart_regions');
                break;

            case "zone":
                $query->from('#__phocacart_zones');
                break;

            case "currency":
                $query->from('#__phocacart_currencies');
                break;

            case "tag":
            case "label":
            $query->from('#__phocacart_tags');
                break;

            case "manufacturer":
                $query->from('#__phocacart_manufacturers');
                break;

            case "shipping":
                $query->from('#__phocacart_shipping_methods');
                break;

            case "specificationgroup":
                $query->from('#__phocacart_specification_groups');
                break;

            case "formfield":
                $query->from('#__phocacart_form_fields');
                break;

            case "user":
                $query->from('#__phocacart_users');
                break;

            case "order":
                $query->from('#__phocacart_orders');
                break;

            case "review":
                $query->from('#__phocacart_reviews');
                break;

            case "question":
                $query->from('#__phocacart_questions');
                break;

            case "submititem":
                $query->from('#__phocacart_submit_items');
                break;

            case "wishlist":
                $query->from('#__phocacart_wishlists');
                break;

            case "category":
                $query->from('#__phocacart_categories');
                break;

            case "section":
                $query->from('#__phocacart_sections');
                break;

            case "unit":
                $query->from('#__phocacart_units');
                break;

            case "tax":
                $query->from('#__phocacart_taxes');
                break;

            case "attribute":
                $query->from('#__phocacart_attributes');
                break;

            default:
                $query->from('#__phocacart_' . $table);
                break;
        }

        $db->setQuery($query);
        $options = $db->loadObjectList();

        foreach ($options as $option) {
            $option->text = Text::_($option->text);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
