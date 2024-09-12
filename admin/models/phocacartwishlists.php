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

class PhocaCartCpModelPhocacartWishlists extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'productname', 'productname',
				'cattitle', 'cattitle',
				'username', 'username',
				'date', 'a.date',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'ordering', 'a.ordering',
                'type','a.type',
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'ASC') {
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$type = $app->getUserStateFromRequest($this->context.'.filter.type', 'filter_type', '', 'string');
		$this->setState('filter.type', $type);

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.type');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__phocacart_wishlists` AS a');

		$query->select('p.title AS productname');
		$query->join('LEFT', '#__phocacart_products AS p ON p.id=a.product_id');

		$query->select('c.title AS cattitle, c.id AS catid');
		$query->join('LEFT', '#__phocacart_categories AS c ON c.id=a.category_id');

		$query->select('ua.id AS userid, ua.username AS username, ua.name AS name');
		$query->join('LEFT', '#__users AS ua ON ua.id=a.user_id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Filter by wishlist type.
		$type = $this->getState('filter.type');
		if (is_numeric($type)) {
			$query->where('a.type = '.(int)$type);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.' OR p.title LIKE '.$search.')');
			}
		}

		$orderCol	= $this->state->get('list.ordering', 'username');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}

