<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocacartParameterValues extends JModelList
{
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
				'published','a.published',
				'parameter_id', 'a.parameter_id',
				'count_products', 'a.count_products',
				'type', 'a.type'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'ASC') {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

/*		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);*/


		$parameterId = $app->getUserStateFromRequest($this->context.'.filter.parameter_id', 'filter_parameter_id', 'modules');
		$this->setState('filter.parameter_id', $parameterId);


		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		//$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		//$this->setState('filter.language', $language);

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);


		$parameterId = $app->getUserStateFromRequest($this->context.'.filter.parameter_id', 'filter_parameter_id');

		if ($parameterId == 0) {
		   $this->setState('filter.parameter_id', '');
		}
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		//$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.parameter_id');
		$id	.= ':'.$this->getState('filter.parametervalue_id');
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
		$query->from('`#__phocacart_parameter_values` AS a');

		// Join over the language
		//$query->select('l.title AS language_title');
		//$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');


		// Filter by access level.
/*		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}*/

		$query->select('p.title AS parameter_title, p.alias AS parameter_alias');
		$query->join('LEFT', '`#__phocacart_parameters` AS p ON p.id = a.parameter_id');



		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by category.
		$parameterId = $this->getState('filter.parameter_id');
		if (is_numeric($parameterId)) {
			$query->where('a.parameter_id = ' . (int) $parameterId);
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

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
}
?>