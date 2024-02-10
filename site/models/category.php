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
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\Constants\ProductType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;

defined('_JEXEC') or die();

class PhocaCartModelCategory extends BaseDatabaseModel
{
	protected $item 				= null;
	protected ?array $item_ordering = null;
	protected $layout_type			= null;
	protected $category 			= null;
	protected $subcategories 		= null;
	protected $category_ordering	= null;
	protected $pagination			= null;
	protected $total				= null;
	protected $ordering				= null;
	protected $category_layout_plugin	= '';

	public function __construct() {
		parent::__construct();

		$app					= Factory::getApplication();
		$paramsC 				= $app->getParams();
		$item_pagination		= $paramsC->get( 'item_pagination_default', '20' );
		$item_ordering			= $paramsC->get( 'item_ordering', 1 );
		$layout_type			= $paramsC->get( 'layout_type', 'grid' );

		$this->category_layout_plugin	= $paramsC->get( 'category_layout_plugin', '' );

		$limit					= PhocacartPagination::getMaximumLimit($app->getUserStateFromRequest('com_phocacart.limit', 'limit', $item_pagination, 'int'));

		$this->setState('limit', $limit);
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', $app->input->get('filter_order', 'ordering'));
		$this->setState('filter_order_dir', $app->input->get('filter_order_Dir', 'ASC'));
		$this->setState('itemordering', $app->getUserStateFromRequest('com_phocacart.itemordering', 'itemordering', $item_ordering, 'int'));
		$this->setState('layouttype', $app->getUserStateFromRequest('com_phocacart.layouttype', 'layouttype', $layout_type, 'string'));
	}

	public function getLayoutType() {
		$layoutType 	= $this->getState('layouttype');
		$layoutType		= PhocacartRenderFront::getLayoutType($layoutType);
		return $layoutType;
	}

	public function getPagination($categoryId) {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocacartPagination( $this->getTotal($categoryId), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}

	function getOrdering() {
		if(empty($this->ordering)) {
			$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 0);
		}
		return $this->ordering;
	}

	public function getTotal($categoryId) {
		if (empty($this->total)) {
			$query = $this->getItemListQuery($categoryId, 1);
			$this->total = $this->_getListCount($query);
		}
		return $this->total;
	}

	public function getItemList($categoryId) {
		if (empty($this->item)) {
			$query			= $this->getItemListQuery( $categoryId);
			$this->item		= $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->item;
	}

	public function getCategory($categoryId) {
		if (empty($this->category)) {
			$query					= $this->getCategoriesQuery($categoryId);
			$this->category 		= $this->_getList($query, 0, 1);
		}

		return $this->category;
	}

	public function getSubcategories($categoryId) {
		if (empty($this->subcategories)) {
			$query					= $this->getCategoriesQuery( $categoryId, true);
			$this->subcategories 	= $this->_getList( $query );
		}
		return $this->subcategories;
	}

    private function dispatchCategoryLayout(array &$ordering, array &$columns): void
    {
        if (!$this->category_layout_plugin) {
            return;
        }

        $plugin = PhocacartText::filterValue($this->category_layout_plugin, 'alphanumeric2');
        $pluginOptions = [];

        Dispatcher::dispatch(new Event\Layout\Category\GetOptions('com_phocacart.category', $pluginOptions, [
            'pluginname' => $plugin,
		]));

        $pluginOrdering = PhocacartText::filterValue($pluginOptions['ordering'] ?? '', 'alphanumeric5');
        $pluginColumns = $pluginOptions['columns'] ?? [];
        array_walk($pluginColumns, function($column) {
           return PhocacartText::filterValue($column, 'alphanumeric3');
        });

        if ($pluginOrdering) {
            array_unshift($ordering, $pluginOrdering);
        }

        $columns = array_merge($columns, $pluginColumns);
    }

    private function dispatchLoadColumns(array &$columns)
    {
        $pluginOptions = [];
        Dispatcher::dispatch(new Event\View\Category\BeforeLoadColumns('com_phocacart.category', $pluginOptions));

        $pluginColumns = $pluginOptions['columns'] ?? [];
        array_walk($pluginColumns, function($column) {
            return PhocacartText::filterValue($column, 'alphanumeric3');
        });

        $columns = array_merge($columns, $pluginColumns);
    }

	protected function getItemListQuery($categoryId, bool $isCountQuery = false) {
		$app		= Factory::getApplication();
		$user 		= PhocacartUser::getUser();
        $lang 		= $app->getLanguage()->getTag();
        $db 		= $this->getDatabase();

        $categoryId = max((int) $categoryId, 0);

		$params 	= $app->getParams();
		$join	    = [];
        $where		= [];

        $where[] = 'a.published = 1';
        $where[] = 'c.published = 1';

		if (!$params->get('sql_products_skip_category_type', false)) {
            $where[] = 'c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';
        }

		if ($this->getState('filter.language')) {
			$where[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$where[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if (!$params->get('sql_products_skip_access', false)) {
            $userLevels	= $user->getAuthorisedViewLevels();
			$where[] = 'c.access IN (' . implode(', ', $userLevels) . ')';
			$where[] = 'a.access IN (' . implode(', ', $userLevels) . ')';
        }

        $userGroups = PhocacartGroup::getGroupsById($user->id, GroupType::User, 1);
		if (!$params->get('sql_products_skip_group', false)) {
			$where[] = '(ga.group_id IN (' . implode(', ', $userGroups) . ') OR ga.group_id IS NULL)';
			$where[] = '(gc.group_id IN (' . implode(', ', $userGroups) . ') OR gc.group_id IS NULL)';
		}

		if ($params->get( 'hide_products_out_of_stock', false)) {
			$where[] = 'a.stock > 0';
		}

        $subWherePcCat 	= '';
        if ($categoryId) {
            $subWherePcCat 		= ' AND pc.category_id = ' . $categoryId;

            // Display products not only from current category but even from all subcategories
            if ($params->get('display_products_all_subcategories', false)) {
                $categoryChildrenId = PhocacartCategoryMultiple::getCategoryChildrenString($categoryId, (string)$categoryId);
                if ($categoryChildrenId) {
                    $where[] = 'c.id IN (' . $categoryChildrenId . ')';
                    $subWherePcCat = 'AND pc.category_id IN (' . $categoryChildrenId . ')';
                } else {
                    $where[] = 'c.id = ' . $categoryId;
                }
            } else {
                $where[] = 'c.id = ' . $categoryId;
            }
        }

        $join[] = 'LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id ' . $subWherePcCat;
        $join[] = 'LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
        $join[] = 'LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';

        if (!$params->get('sql_products_skip_attributes', false)) {
            $join[] = 'LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
        }

        if (!$params->get('sql_products_skip_group', false)) {
            $join[] = 'LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
            $join[] = 'LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
        }

		if ($isCountQuery) {
			$query = 'SELECT a.id'
			. ' FROM #__phocacart_products AS a'
			. ' ' . implode(' ', $join)
			. ' WHERE ' . implode(' AND ', $where)
			. ' GROUP BY a.id';
		} else {
			$join[] = 'LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';

			if (!$params->get('sql_products_skip_tax', false)) {
				$join[] = 'LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
			}

			if (!$params->get('sql_products_skip_group', false)) {
				// user is in more groups, select lowest price by best group
				$join[] = 'LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . implode(', ', $userGroups) . ') AND type = 3)';
				// user is in more groups, select highest points by best group
				$join[] = 'LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . implode(', ', $userGroups) . ') AND type = 3)';
			}

            $ordering = PhocacartOrdering::getOrdering($this->getState('itemordering'));
            $columns = [
                'a.id', 'a.image', 'a.unit_amount', 'a.unit_unit',
                'a.sku', 'a.ean', 'a.upc', 'a.type', 'a.points_received', 'a.price_original',
                'a.stock', 'a.stock_calculation', 'a.min_quantity', 'a.min_multiple_quantity',
                'a.stockstatus_a_id', 'a.stockstatus_n_id','a.date', 'a.sales', 'a.featured',
                'a.external_id', 'a.unit_amount', 'a.unit_unit', 'a.external_link', 'a.external_text', 'a.price', 'a.gift_types',
            ];

            $this->dispatchCategoryLayout($ordering, $columns);
            $this->dispatchLoadColumns($columns);

            $columns = array_unique($columns);

            if (PhocacartUtilsSettings::isFullGroupBy()) {
                $groupBy = $columns;
                if (!$params->get('sql_products_skip_tax', false)) {
                    $groupBy[] = 't.id';
                    $groupBy[] = 't.tax_rate';
                    $groupBy[] = 't.calculation_type';
                    $groupBy[] = 't.title';
                }

                if (!$params->get('sql_products_skip_attributes', false)) {
                    $groupBy[] = 'at.required';
                }

                $groupBy[] = 'a.title';
                $groupBy[] = 'a.alias';
                $groupBy[] = 'a.description';

                if (I18nHelper::isI18n()) {
                    $groupBy[] = 'i18n_a.title';
                    $groupBy[] = 'i18n_a.alias';
                    $groupBy[] = 'i18n_a.description';
                }
            } else {
                $groupBy = ['a.id'];
            }

            if (I18nHelper::isI18n()) {
                $join[] = 'LEFT JOIN #__phocacart_products_i18n AS i18n_a ON i18n_a.id = a.id AND i18n_a.language = ' . $db->quote($lang);
                $columns[] = 'coalesce(i18n_a.title, a.title) AS title';
                $columns[] = 'coalesce(i18n_a.alias, a.alias) AS alias';
                $columns[] = 'i18n_a.description';
            } else {
                $columns[] = 'a.title';
                $columns[] = 'a.alias';
                $columns[] = 'a.description';
            }

            if ($params->get('switch_image_category_items', false)) {
                $columns[] = '(SELECT im.image FROM #__phocacart_product_images im WHERE im.product_id = a.id ORDER BY im.ordering LIMIT 1) as additional_image';
            }

            $columns[] = 'GROUP_CONCAT(DISTINCT c.id) AS catid';
            $columns[] = 'GROUP_CONCAT(DISTINCT c.title) AS cattitle';
            $columns[] = 'GROUP_CONCAT(DISTINCT c.alias) AS catalias';

			if (!$params->get('sql_products_skip_tax', false)) {
                $columns[] = 't.id as taxid';
                $columns[] = 't.tax_rate as taxrate';
                $columns[] = 't.calculation_type as taxcalculationtype';
                $columns[] = 't.title as taxtitle';
                $columns[] = 't.tax_hide as taxhide';
			} else {
                $columns[] = 'NULL as taxid';
                $columns[] = 'NULL as taxrate';
                $columns[] = 'NULL as taxcalculationtype';
                $columns[] = 'NULL as taxtitle';
                $columns[] = 'NULL as taxhide';
			}

			if (!$params->get('sql_products_skip_attributes', false)) {
                $columns[] = 'at.required AS attribute_required';
            } else {
                $columns[] = '0 AS attribute_required';
            }

			if (!$params->get('sql_products_skip_group', false)) {
                $columns[] = 'MIN(ppg.price) as group_price';
                $columns[] = 'MAX(pptg.points_received) as group_points_received';
            } else {
                $columns[] = 'NULL as group_price';
                $columns[] = 'NULL as group_points_received';
            }

            $columns[] = 'm.id as manufacturerid';
            $columns[] = 'm.title as manufacturertitle';
            $columns[] = 'm.alias as manufactureralias';
            $columns[] = 'AVG(r.rating) AS rating';

			$query = 'SELECT ' . implode(', ', $columns)
			. ' FROM #__phocacart_products AS a'
			. ' ' . implode(' ', $join)
			. ' WHERE ' . implode(' AND ', $where)
			. ' GROUP BY ' . implode(', ', $groupBy)
			. ' ORDER BY ' . implode(', ', $ordering);
		}

		return $query;
	}

	protected function getCategoriesQuery($categoryId, bool $isSubcategoriesQuery = false) {
		$user 		= PhocacartUser::getUser();
        $lang 		= Factory::getApplication()->getLanguage()->getTag();
        $db 		= $this->getDatabase();

        $where		= [];
        $join		= [];

        $join[] = 'LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;

        if (I18nHelper::isI18n()) {
            $join[] = 'LEFT JOIN #__phocacart_categories_i18n AS i18n_c ON i18n_c.id = c.id AND i18n_c.language = ' . $db->quote($lang);
        }

		if ($isSubcategoriesQuery) {
			$where[] = 'c.parent_id = ' . (int)$categoryId;
			$categoryOrdering 	= $this->getCategoryOrdering();
		} else {
			$where[] = 'c.id = ' . (int)$categoryId;
            $join[] = 'LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.parent_id';
            if (I18nHelper::isI18n()) {
                $join[] = 'LEFT JOIN #__phocacart_categories_i18n AS i18n_cc ON i18n_cc.id = cc.id AND i18n_cc.language = ' . $db->quote($lang);
            }
		}

		$where[] = 'c.published = 1';
		$where[] = 'c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';
		$where[] = 'c.access IN ('. implode (',', $user->getAuthorisedViewLevels()) . ')';
		$where[] = '(gc.group_id IN (' . implode (',', PhocacartGroup::getGroupsById($user->id,  GroupType::User, 1)) . ') OR gc.group_id IS NULL)';

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getApplication()->getLanguage()->getTag();
			$where[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if ($isSubcategoriesQuery) {
            if (I18nHelper::isI18n()) {
                $columns    = 'c.id, c.parent_id, coalesce(i18n_c.title, c.title) as title, i18n_c.title_long, coalesce(i18n_c.alias, c.alias) as alias, c.image';
                $groupsFull = 'c.id, c.parent_id, coalesce(i18n_c.title, c.title), i18n_c.title_long, coalesce(i18n_c.alias, c.alias), c.image';
            } else {
                $columns    = 'c.id, c.parent_id, c.title, c.title_long, c.alias, c.image';
                $groupsFull = 'c.id, c.parent_id, c.title, c.title_long, c.alias, c.image';
            }
			$groupsFast	= 'c.id';
			$groupBy	= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query = 'SELECT ' . $columns
				. ' FROM #__phocacart_categories AS c'
				. ' ' . implode(' ', $join)
				. ' WHERE ' . implode( " AND ", $where)
				. ' GROUP BY ' . $groupBy
				. ' ORDER BY ' . $categoryOrdering;
		} else {
            if (I18nHelper::isI18n()) {
                $columns	= 'c.id, c.parent_id, coalesce(i18n_c.title, c.title) as title, i18n_c.title_long, coalesce(i18n_c.alias, c.alias) as alias, c.image,'
                    . ' i18n_c.description, i18n_c.metatitle, i18n_c.metakey, i18n_c.metadesc, c.metadata,'
                    . ' coalesce(i18n_cc.title, cc.title) as parenttitle, c.parent_id as parentid, coalesce(i18n_cc.alias, cc.alias) as parentalias';
            } else {
                $columns	= 'c.id, c.parent_id, c.title, c.title_long, c.alias, c.image, c.description, c.metatitle, c.metakey, c.metadesc, c.metadata,'
                    . ' cc.title as parenttitle, c.parent_id as parentid, cc.alias as parentalias';
            }
			$query = ' SELECT ' . $columns
				. ' FROM #__phocacart_categories AS c'
                . ' ' . implode(' ', $join)
				. ' WHERE ' . implode( ' AND ', $where )
				. ' ORDER BY c.ordering';
		}

		return $query;
	}

	protected function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= Factory::getApplication();
			$params						= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocacartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}

	public function hit($pk = 0) {
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount) {
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('cateogry.id');

			$table = Table::getInstance('PhocacartCategory', 'Table');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
