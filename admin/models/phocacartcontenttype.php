<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocaCartCpModelPhocacartContentType extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function getTable($type = 'PhocacartContentType', $prefix = 'Table', $config = [])
    {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
    {
		return $this->loadForm('com_phocacart.phocacartcontenttype', 'phocacartcontenttype', ['control' => 'jform', 'load_data' => $loadData]);
	}

	protected function loadFormData()
    {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartcontenttype.data', []);
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	protected function prepareTable($table)
    {
		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = $this->getDatabase();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_content_types');
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

    public function save($data)
    {
        if ($data['context'] == 'attribute') {
            $values = $data['params']['attribute'] ?? [];

            if (I18nHelper::isI18n()) {
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    if (empty($values['title'][$language->lang_code])) {
                        $values['title'][$language->lang_code] = Factory::getDate()->format("Y-m-d-H-i-s");
                    }

                    if (empty($values['alias'][$language->lang_code])) {
                        $values['alias'][$language->lang_code] = $values['title'][$language->lang_code];
                    }

                    $values['alias'][$language->lang_code] = PhocacartUtils::getAliasName($values['alias'][$language->lang_code]);
                }
            } else {
                if (empty($values['title'])) {
                    $values['title'] = Factory::getDate()->format("Y-m-d-H-i-s");
                }

                if (empty($values['alias'])) {
                    $values['alias'] = $values['title'];
                }

                $values['alias'] = PhocacartUtils::getAliasName($values['alias']);
            }

            $data['params']['attribute'] = $values;
        }

        if (parent::save($data)) {
            if ($data['context'] == 'attribute') {
                if ($this->getState($this->getName() . '.new')) {
                    return true;
                }
                $id = $this->getState($this->getName() . '.id');
                $db = $this->getDatabase();
                $values = $data['params']['attribute'] ?? [];
                $fields = [
                    'required = ' . (int)$values['required'],
                    'is_filter = ' . (int)$values['is_filter'],
                    'type = ' . (int)$values['type'],
                ];
                if (I18nHelper::isI18n()) {
                    $fields += [
                        'title = ' . $db->quote($values['title'][I18nHelper::getDefLanguage()] ?? ''),
                        'alias = ' . $db->quote($values['alias'][I18nHelper::getDefLanguage()] ?? ''),
                    ];
                } else {
                    $fields += [
                        'title = ' . $db->quote($values['title']),
                        'alias = ' . $db->quote($values['alias']),
                    ];
                }

                $query = $db->getQuery(true)
                    ->update('#__phocacart_attributes')
                    ->set($fields)
                    ->where('attribute_template = ' . $id);
                $db->setQuery($query);
                $db->execute();

                if (I18nHelper::isI18n()) {
                    foreach (I18nHelper::getI18nLanguages() as $language) {
                        $fields = [
                            'i18n.title = ' . $db->quote($values['title'][$language->lang_code] ?? ''),
                            'i18n.alias = ' . $db->quote($values['alias'][$language->lang_code] ?? ''),
                        ];

                        $query = $db->getQuery(true)
                            ->update('#__phocacart_attributes_i18n AS i18n')
                            ->join('INNER', '#__phocacart_attributes AS a', 'a.id = i18n.id')
                            ->set($fields)
                            ->where('a.attribute_template = ' . $id)
                            ->where('i18n.language = ' . $db->quote($language->lang_code));
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }

            return true;
        }

        return false;
    }
}
