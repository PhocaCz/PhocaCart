<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\I18n\I18nHelper;
use Phoca\PhocaCart\I18n\I18nListModelTrait;

class PhocaCartCpModelPhocacartManufacturers extends ListModel
{
    use I18nListModelTrait;

	protected $option 	= 'com_phocacart';

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'published','a.published',
				'count_products', 'a.count_products'
			);
		}

		// ASSOCIATION
		if (I18nHelper::associationsEnabled()){
			$config['filter_fields'][] = 'association';
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'ASC') {
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// ASSOCIATION
		$forcedLanguage = $app->input->getCmd('forcedLanguage');
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
		$this->setState('filter.search', $search);

/*		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);*/



		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '', 'string');
		$this->setState('filter.language', $language);

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
		//$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.manufacturer_id');
		$id .= ':'.$this->getState('filter.language');
		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__phocacart_manufacturers` AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image');
		$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');


		if (I18nHelper::associationsEnabled()) {
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso2.id') . ')')
				->from($db->quoteName('#__associations', 'asso'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
				->where($db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id'))
				->where($db->quoteName('asso.context') . ' = ' . $db->quote('com_phocacart.manufacturer'));

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
		}

		// Filter by access level.
/*		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}*/


		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}


		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('( a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}

