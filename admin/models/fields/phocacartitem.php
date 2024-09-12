<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\ContentType\ContentTypeHelper;

require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');
Factory::getApplication()->getLanguage()->load('com_phocacart');

class JFormFieldPhocacartItem extends ListField
{
    protected $type = 'PhocaCartItem';
    protected $layout = 'phocacart.form.field.item';

    protected function getOptions()
    {
        $options = [];

        if ($this->value) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('id AS value, title AS text')
                ->from('#__phocacart_products')
                ->where('id = ' . (int)$this->value);
            $db->setQuery($query);
            $options = $db->loadObjectList();
        }

        return array_merge(parent::getOptions(), $options);
    }

    protected function getRenderer($layoutId = 'default')
    {
        // Make field usable outside of Phoca Cart component
        $renderer = parent::getRenderer($layoutId);
        $renderer->addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/layouts');

        return $renderer;
    }
}

