<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Associations;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;
use Phoca\PhocaCart\I18n\I18nHelper;
use Phoca\PhocaCart\MVC\Model\AdminModelTrait;

class PhocaCartCpModelPhocacartManufacturer extends AdminModel
{
    use I18nAdminModelTrait, AdminModelTrait;

    protected	$option 		= 'com_phocacart';
    protected $text_prefix	= 'com_phocacart';
    public $typeAlias = 'com_phocacart.phocacartmanufacturer';
    protected   $associationsContext    = 'com_phocacart.manufacturer';	// ASSOCIATION

    public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $formFactory);

        $this->i18nTable = '#__phocacart_manufacturers_i18n';
        $this->i18nFields = [
            'title',
            'alias',
            'title_long',
            'link',
            'description',
            'metatitle',
            'metakey',
            'metadesc',
        ];
    }

    public function getTable($type = 'PhocacartManufacturer', $prefix = 'Table', $config = array()) {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true) {
        $form = $this->loadForm('com_phocacart.phocacartmanufacturer', 'phocacartmanufacturer', array('control' => 'jform', 'load_data' => $loadData));
        return $this->prepareI18nForm($form);
    }

    public function getItem($pk = null) {
        if ($item = parent::getItem($pk)) {
            // Convert the metadata field to Registry
            if (isset($item->metadata)) {
                $registry = new Registry;
                $registry->loadString($item->metadata);
                $item->metadata = $registry->toArray();
            }

            // ASSOCIATION
            // Load associated manufacturers
            if (I18nHelper::associationsEnabled()) {
                $item->associations = [];

                if ($item->id) {
                    $associations = Associations::getAssociations('com_phocacart', '#__phocacart_manufacturers', $this->associationsContext, $item->id, 'id', 'alias', false);

                    foreach ($associations as $tag => $association){
                        $item->associations[$tag] = $association->id;
                    }
                }
            }
        }

        return $item;
    }

    protected function loadFormData() {
        $data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartmanufacturer.data', array());

        if (empty($data)) {
            $data = $this->getItem();
            $this->loadI18nItem($data);
        }

        $this->preprocessData('com_phocacart.phocacartmanufacturer', $data);

        return $data;
    }

    protected function prepareTable($table) {
        jimport('joomla.filter.output');

        $table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->alias		= ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->title);
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = Factory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__phocacart_manufacturers');
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }

    public function save($data) {
        $i18nData = $this->prepareI18nData($data);

        if (parent::save($data)) {
            $savedId = $this->getState($this->getName().'.id');
            $this->saveI18nData($savedId, $i18nData);

            if ((int)$savedId > 0) {
                PhocacartCount::setProductCount(array(0 => (int)$savedId), 'manufacturer', 1);
            }

            return true;
        } else {
            return false;
        }
    }

    public function featured($pks, $value = 0) {
        // Sanitize the ids.
        $pks = (array) $pks;
        ArrayHelper::toInteger($pks);

        if (empty($pks)) {
            $this->setError(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'));
            return false;
        }

        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__phocacart_manufacturers'))
                ->set('featured = ' . (int) $value)
                ->where('id IN (' . implode(',', $pks) . ')');
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();

        return true;
    }

    protected function preprocessForm(Form $form, $data, $group = 'content') {
        if (I18nHelper::associationsEnabled()){
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'Modal_Phocacartmanufacturer');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
        }

        parent::preprocessForm($form, $data, $group);
    }

    public function delete(&$pks)
    {
        if (!parent::delete($pks)) {
            return false;
        }

        $query = $this->getQuery()
            ->update('#__phocacart_products')
            ->set([
                'manufacturer_id = 0',
            ])
            ->whereIn('manufacturer_id', $pks);

        $this->executeQuery($query);
        return $this->deleteI18nData($pks);
    }
}
