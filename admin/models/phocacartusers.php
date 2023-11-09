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
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocacartUsers extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_username', 'u.username',
				'user_name', 'u.name',
				'name_first', 'a.name_first',
				'name_last', 'a.name_last',
				'address_1', 'a.address_1',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'ordering', 'a.ordering',
				'published','a.published',
				'user_id', 'a.user_id',
				'user_name_selected',
				'u.email', 'u.id'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'u.name', $direction = 'ASC') {
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$user = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
		$this->setState('filter.user_id', $user);

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);

		// Let the parent do the filtering but to close the filter fields we need "" instead of 0 for users
		$user = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
		if ($user == 0) {
			$this->setState('filter.user_id', '');
		}
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.user_id');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.user_id_id');
		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$columns	= 'a.id, a.checked_out, a.name_last, a.name_first, a.address_1, a.city, a.ordering, a.email';

		$query->select($this->getState('list.select', '' . ' ' .$columns))
			->select('u.name AS user_name, u.username AS user_username, u.id as user_id, u.email AS user_email')
			->from('`#__users` AS u')
			->join('LEFT', '#__phocacart_users AS a ON a.user_id=u.id AND a.type = 1');

		$query->select('c.date AS cartdate, c.user_id as cartuserid, c.vendor_id as cartvendorid, c.ticket_id as cartticketid, c.unit_id as cartunitid, c.section_id as cartsectionid');
		$query->join('LEFT', '#__phocacart_cart_multiple AS c ON c.user_id = u.id');

		// Has order
		$query->select('(SELECT ou.user_id FROM #__phocacart_orders ou WHERE ou.user_id = a.user_id LIMIT 1) AS orderuserid');

		// POS
		$query->select('uv.name AS vendor_name, uv.username AS vendor_username');
		$query->join('LEFT', '#__users AS uv ON uv.id=c.vendor_id');

		$query->select('sc.title AS section_name');
		$query->join('LEFT', '#__phocacart_sections AS sc ON sc.id=c.section_id');

		$query->select('un.title AS unit_name');
		$query->join('LEFT', '#__phocacart_units AS un ON un.id=c.unit_id');

		// Checked out user
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Usergroups
		$query->select('(SELECT GROUP_CONCAT(DISTINCT g.title ORDER BY g.title) FROM #__phocacart_item_groups ug LEFT JOIN #__phocacart_groups AS g ON g.id=ug.group_id WHERE ug.item_id=u.id AND ug.type = 1) AS usergroups');

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
				$query->where('( u.name LIKE '.$search.' OR u.username LIKE '.$search.' OR a.name_last LIKE '.$search.')');
			}
		}

		$user = $this->getState('filter.user_id');

		if (!empty($user)){
			$query->select('u2.name AS user_name_selected');
			$query->join('LEFT', '#__users AS u2 ON u2.id=a.user_id');
			$query->where('( u.id = '.(int)$user.')');
		}

		$orderCol	= $this->state->get('list.ordering', 'u.name');
		$orderDirn	= $this->state->get('list.direction', 'u.name');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}
