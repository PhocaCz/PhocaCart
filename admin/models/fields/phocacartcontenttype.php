<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

class JFormFieldPhocaCartContentType extends ListField
{
	protected $type = 'PhocaCartContentType';
    protected string $context = 'category';

	protected function getOptions() {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('title AS text, id AS value')
            ->from('#__phocacart_content_types')
            ->where('context = ' . $db->quote($this->context))
            ->order('ordering, id');

        $db->setQuery($query);
        $options = $db->loadObjectList();

        array_walk($options, function($option) {
            $option->text = Text::_($option->text);
        });

		return array_merge(parent::getOptions(), $options);
	}

    public function __get($name)
    {
        switch ($name) {
            case 'context':
                return $this->$name;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'context':
                $this->$name = strtolower((string)$value);
                break;

            default:
                parent::__set($name, $value);
        }
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->context = $this->element['context'] ?? $this->context;
            $this->context = strtolower($this->context);
        }

        return $result;
    }

}
