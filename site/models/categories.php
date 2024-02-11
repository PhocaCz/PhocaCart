<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\Constants\ProductType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;

defined('_JEXEC') or die();

class PhocaCartModelCategories extends BaseDatabaseModel
{
	protected $categories = null;
	protected $category_ordering = null;

	public function __construct() {
		parent::__construct();
		$app	= Factory::getApplication();
		$this->setState('filter.language', $app->getLanguageFilter());
	}

	public function getCategoriesList($displaySubcategories = 0) {
		if ($this->categories === null) {
			$this->categories = [];

			$app = Factory::getApplication();
			$params = $app->getParams();
			if(!$params->get('show_categories', 1)) {
                return $this->categories;
            }

			$categoriesOrdering = $this->getCategoryOrdering();

			if ((int)$displaySubcategories > 0) {
				$id = -1; // display subcategories - -1 means to load all items
			} else {
				$id = 0;// display only parent categories
			}

			$query			= $this->getCategoriesListQuery($id, $categoriesOrdering);
			$categories 	= $this->_getList($query);

			if (!empty($categories)) {

				// Parent Only
				foreach ($categories as $k => $v) {
					if ($v->parent_id == 0) {
						$this->categories[$v->id] = $categories[$k];
					}
				}

				// Subcategories
				foreach ($categories as $k => $v) {
					if (isset($this->categories[$v->parent_id])) {
						$this->categories[$v->parent_id]->subcategories[] = $categories[$k];
						$this->categories[$v->parent_id]->numsubcat++;
					}
				}
			}
		}
		return $this->categories;
	}

    private function dispatchLoadColumns(array &$columns)
    {
        $pluginOptions = [];
        Dispatcher::dispatch(new Event\View\Categories\BeforeLoadColumns('com_phocacart.categories', $pluginOptions));

        $pluginColumns = $pluginOptions['columns'] ?? [];
        array_walk($pluginColumns, function($column) {
            return PhocacartText::filterValue($column, 'alphanumeric3');
        });

        $columns = array_merge($columns, $pluginColumns);
    }

	public function getCategoriesListQuery($id, $categoriesOrdering)
    {
		$user 		= PhocacartUser::getUser();
		$app		= Factory::getApplication();
		$params 	= $app->getParams();
        $db 		= $this->getDatabase();

        $where		= [];
        $join		= [];

        $join[] = 'LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;

        $where[] = 'c.published = 1';
        $where[] = 'c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';

		if ($display_categories = $params->get('display_categories', [])) {
			$where[] = 'c.id IN (' . implode(', ', $display_categories) . ')';
		}

		if ($hide_categories = $params->get('hide_categories', [])) {
			$where[] = 'c.id NOT IN (' . implode(', ', $hide_categories) . ')';
		}

		if ($id != -1) {
			$where[] = 'c.parent_id = ' . (int)$id;
		}

		if ($this->getState('filter.language')) {
			$where[] =  ' c.language IN (' . $db->quote($app->getLanguage()->getTag()) . ', ' . $db->quote('*') . ')';
		}

		$where[] = 'c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
		$where[] = '(gc.group_id IN (' . implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1)) . ') OR gc.group_id IS NULL)';

        $columns = ['c.id', 'c.title', 'c.alias', 'c.image', 'c.description', 'c.icon_class', 'c.parent_id'];
        $this->dispatchLoadColumns($columns);
		$columns = array_unique($columns);

        if (PhocacartUtilsSettings::isFullGroupBy()) {
            $groupBy = $columns;
            if (I18nHelper::isI18n()) {
                $groupBy[] = 'coalesce(i18n_c.title, c.title)';
                $groupBy[] = 'coalesce(i18n_c.alias, c.alias)';
                $groupBy[] = 'i18n_c.description';
            } else {
                $groupBy[] = 'c.title';
                $groupBy[] = 'c.alias';
                $groupBy[] = 'c.description';
            }
        } else {
            $groupBy = ['c.id'];
        }

        if (I18nHelper::isI18n()) {
            $join[] = I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'i18n_c', 'c');

            $columns[] = 'coalesce(i18n_c.title, c.title) as title';
            $columns[] = 'coalesce(i18n_c.alias, c.alias) as alias';
            $columns[] = 'i18n_c.description';
        } else {
            $columns[] = 'c.title';
            $columns[] = 'c.alias';
            $columns[] = 'c.description';
        }

        $columns[] = 'c.parent_id as parentid';
        $columns[] = '0 AS numsubcat';

		$query =  'SELECT ' . implode(', ', $columns)
		. ' FROM #__phocacart_categories AS c'
		. ' ' . implode(' ', $join)
		. ' WHERE ' . implode(' AND ', $where)
		. ' GROUP BY ' . implode(', ', $groupBy)
		. ' ORDER BY ' . $categoriesOrdering;

		return $query;
	}

	private function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= Factory::getApplication();
			$params 					= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocacartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}
}

