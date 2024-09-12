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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;
use Phoca\PhocaCart\I18n\I18nListModelTrait;

class PhocaCartCpModelPhocaCartItems extends ListModel
{
    use I18nListModelTrait;

	protected $option 	= 'com_phocacart';
	protected $columns	= [];
	protected $columns_full	= [];

	public function __construct($config = array())
    {
		$paramsC = PhocacartUtils::getComponentParameters();
		$c = new PhocacartRenderAdmincolumns();

        $admin_columns_products = $paramsC->get('admin_columns_products', 'sku=E, image, title, published, categories, price=E, price_original=E, discount_percent, stock=E, access_level, language, association, hits, id');
        $admin_columns_products = explode(',', $admin_columns_products);

		$options                = array();
		$options['type']    	= 'data';
		$options['association'] = I18nHelper::associationsEnabled();

		if (!empty($admin_columns_products)) {
			foreach ($admin_columns_products as $k => $v) {
				$v = PhocacartText::parseDbColumnParameter($v);
				$data = $c->header($v, $options);
				if (isset($data['column']) && $data['column'] != '') {
					$this->columns[] = $data['column'];
					$this->columns_full[] = $data;
				}
			}
		}

		// Add ordering and fields needed for filtering (search tools)
		$config['filter_fields'] = array_merge(['pc.ordering', 'category_id', 'tag_id', 'manufacturer_id', 'owner_id', 'instock',  'published', 'language'], $this->columns);

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'ASC')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// ASSOCIATION
		$forcedLanguage = $app->input->getCmd('forcedLanguage', '');
		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}
		// Adjust the context to support forced languages.
		if ($forcedLanguage){
			$this->context .= '.' . $forcedLanguage;
		}

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', trim((string)$search));

		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

        $tagId = $app->getUserStateFromRequest($this->context.'.filter.tag_id', 'filter_tag_id', null, 'int');
        $this->setState('filter.tag_id', $tagId);

		$manId = $app->getUserStateFromRequest($this->context.'.filter.manufacturer_id', 'filter_manufacturer_id', null, 'int');
		$this->setState('filter.manufacturer_id', $manId);

		$ownerId = $app->getUserStateFromRequest($this->context.'.filter.owner_id', 'filter_owner_id', null, 'int');
		$this->setState('filter.owner_id', $ownerId);

		$inStock = $app->getUserStateFromRequest($this->context.'.filter.instock', 'filter_instock', null, 'int');
		$this->setState('filter.instock', $inStock);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language');
		$this->setState('filter.language', $language);

		// API filters
		$this->setState('filter.sku', $app->getUserState($this->context.'.filter.sku'));
		$this->setState('filter.gtin', $app->getUserState($this->context.'.filter.gtin'));

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);

		// ASSOCIATION
		if (!empty($forcedLanguage)) {
			$this->setState('filter.language', $forcedLanguage);
		}
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.category_id');
        $id	.= ':'.$this->getState('filter.tag_id');
		$id	.= ':'.$this->getState('filter.manufacturer_id');
		$id	.= ':'.$this->getState('filter.owner_id');
		$id	.= ':'.$this->getState('filter.instock');
    	$id .= ':'.$this->getState('filter.language');
		$id	.= ':'.$this->getState('filter.item_id');

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$paramsC 					    = PhocacartUtils::getComponentParameters();
		$search_matching_option_admin	= $paramsC->get( 'search_matching_option_admin', 'exact' );

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Needed columns everytime
		$col = [
			'a.id',
			'a.title',
			'a.alias',
			'a.alias',
			'a.checked_out',
			'a.checked_out_time',
			'a.published',
			'a.ordering',
			'a.featured',
			'a.language',
		];

		$col = array_merge($col, $this->columns);
		$col = array_unique($col);

		$columns	= implode(',', $col);
		$query
            ->select($this->getState('list.select', $columns))
            ->from('`#__phocacart_products` AS a');

		// Join over the language
		$query
            ->select('l.title AS language_title, l.image AS language_image')
            ->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query
            ->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query
            ->select('ag.title AS access_level')
		    ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over manufacturer
        $query
            ->select('pm.title AS manufacturer')
            ->join('LEFT', '#__phocacart_manufacturers AS pm ON pm.id = a.manufacturer_id');

		// ASSOCIATION
		// Join over the associations.
		if (I18nHelper::associationsEnabled()) {
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso2.id') . ')')
				->from($db->quoteName('#__associations', 'asso'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
				->where($db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id'))
				->where($db->quoteName('asso.context') . ' = ' . $db->quote('com_phocacart.item'));

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// When category is selected, we need to get info about selected category
		// When it is not selected, don't ask for it to make the query faster
		// pc.ordering is set as default ordering and it can be set (even igonered) even whey category not selected
		// is complicated but loads much faster
		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		// Filter by category
        $categoryId = $this->getState('filter.category_id');
		if ($orderCol == 'pc.ordering' || is_numeric($categoryId)) {
			// Ask only when really needed
			$query->select('pc.ordering');
			$query->join('LEFT', '#__phocacart_product_categories AS pc ON a.id = pc.product_id');
			$query->join('LEFT', '#__phocacart_categories AS c ON c.id = pc.category_id');
		}

		if (is_numeric($categoryId)) {
			$query->where('pc.category_id = ' . (int) $categoryId);
		}

        // Filter by tag
        $tagId = $this->getState('filter.tag_id');
        if (is_numeric($tagId)) {
            $query
                ->join('LEFT', '#__phocacart_tags_related AS tr ON a.id = tr.item_id')
                ->where('tr.tag_id = ' . (int)$tagId);
        }

        // Filter by manufacturer
		$manufacturerId = $this->getState('filter.manufacturer_id');
		if (is_numeric($manufacturerId)) {
			$query->where('a.manufacturer_id = ' . (int) $manufacturerId);
		}

        // Filter by owner
		$ownerId = $this->getState('filter.owner_id');
		if (is_numeric($ownerId)) {
			$query->where('a.owner_id = ' . (int) $ownerId);
		}

        // Filter by in stock
		$inStock = $this->getState('filter.instock');
		if (is_numeric($inStock)) {
			if ($inStock) {
				$query->where('a.stock > 0');
			} else {
				$query->where('a.stock <= 0');
			}
		}

		// Filter by language
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = ' . $db->quote($language));
		}

        // Filter by SKU (for API)
		if ($sku = $this->getState('filter.sku')) {
			$query->where('a.sku = ' . $db->quote($sku));
		}

        // Filter by GTIN (for API)
		if ($gtin = $this->getState('filter.gtin')) {
			$query->where('a.ean = ' . $db->quote($gtin));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
            $search = trim($search);
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
                $words	= explode(' ', $search);
                $words = array_filter($words);

				switch ($search_matching_option_admin) {
					case 'all':
					case 'any':
						$wheres = array();
						foreach ($words as $word) {
							$word			= $db->quote('%'.$db->escape($word, true).'%', false);
							$wheresSub 		= array();
							$wheresSub[]	= 'a.title LIKE '.$word;
							$wheresSub[]	= 'a.alias LIKE '.$word;
							$wheresSub[]	= 'a.metakey LIKE '.$word;
							$wheresSub[]	= 'a.metadesc LIKE '.$word;
							$wheresSub[]	= 'a.description LIKE '.$word;
							$wheresSub[]	= 'a.sku LIKE '.$word;
							$wheresSub[]	= 'a.ean LIKE '.$word;
							$wheresSub[]	= 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $word . ' OR ps.ean LIKE ' . $word . ') ';
							$wheres[]		= implode(' OR ', $wheresSub);
						}

						$query->where('((' . implode(($search_matching_option_admin == 'all' ? ') AND (' : ') OR ('), $wheres) . '))');

						break;

					case 'exact':
					default:
						$text		= $db->quote('%'.$db->escape(implode(' ', $words), true).'%', false);
						$wheresSub	= array();
						$wheresSub[]	= 'a.title LIKE '.$text;
						$wheresSub[]	= 'a.alias LIKE '.$text;
						$wheresSub[]	= 'a.metakey LIKE '.$text;
						$wheresSub[]	= 'a.metadesc LIKE '.$text;
						$wheresSub[]	= 'a.description LIKE '.$text;
						$wheresSub[]	= 'a.sku LIKE '.$text;
						$wheresSub[]	= 'a.ean LIKE '.$text;
						$wheresSub[]	= 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $text . ' OR ps.ean LIKE ' . $text . ') ';
						$query->where('((' . implode(') OR (', $wheresSub) . '))');

						break;
				}
			}
		}

		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getFilterForm($data = array(), $loadData = true)
	{
        $form = parent::getFilterForm($data, $loadData);
        $this->prepareI18nFilterForm($form);

        $field = $form->getField('fullordering', 'list');
		if (!empty($this->columns_full)) {
            foreach ($this->columns_full as $v) {
                if (isset($v['column']) && $v['column'] != '') {
                    //$field->addOption(Text::_($data['title']. '_ASC'), array('value' => $data['column'] . ' ASC'));
                    //$field->addOption(Text::_($data['title']. '_DESC'), array('value' => $data['column'] . ' DESC'));
                    // Save hundreds of strings in translation
                    // DEBUG Language can mark it as not translated erroneously

                    $field->addOption(Text::_($v['title']) . ' ' . Text::_('COM_PHOCACART_ASCENDING'), array('value' => $v['column'] . ' ASC'));
                    $field->addOption(Text::_($v['title']) . ' ' . Text::_('COM_PHOCACART_DESCENDING'), array('value' => $v['column'] . ' DESC'));
                }
            }
        }

		return $form;
	}

	public function getBatchForm(): Form
	{
		$form = $this->loadForm($this->context . '.batch', 'batch_item', ['control' => '', 'load_data' => false]);

        PhocacartFields::prepareBatchForm('com_phocacart.phocacartitem', $form);

        Dispatcher::dispatch(new Event\Feed\BatchForm('com_phocacart.phocacartitem', $form));

        return $form;
	}

}
