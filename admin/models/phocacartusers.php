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

class PhocaCartCpModelPhocacartUsers extends JModelList
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
				'u.email'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'u.name', $direction = 'ASC') {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$user = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
		$this->setState('filter.user_id', $user);

/*		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);*/



		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		//$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		//$this->setState('filter.language', $language);

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
		//$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.user_id_id');
		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$columns	= 'a.id, a.checked_out, a.name_last, a.name_first, a.address_1, a.city, a.ordering, a.email';
		$groupsFull	= $columns . ', ' . 'u.id, u.name, u.username, u.email, ou.user_id, c.date, c.user_id, c.vendor_id, c.ticket_id, c.unit_id, c.section_id, uc.name';
		$groupsFast	= 'a.id';
		$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


		$query->select($this->getState('list.select', 'DISTINCT' . ' ' .$columns));
		$query->from('`#__users` AS u');

		// Join over the language
		//$query->select('l.title AS language_title');
		//$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.


		$query->select('u.name AS user_name, u.username AS user_username, u.id as user_id, u.email AS user_email');
		$query->join('LEFT', '#__phocacart_users AS a ON a.user_id=u.id');

		$query->select('ou.user_id AS orderuserid');
		$query->join('LEFT', '#__phocacart_orders AS ou ON a.user_id=ou.user_id');


		// GROUP_CONCAT(c.date ORDER BY c.date) AS cartdate,
		$query->select('c.date AS cartdate, c.user_id as cartuserid, c.vendor_id as cartvendorid, c.ticket_id as cartticketid, c.unit_id as cartunitid, c.section_id as cartsectionid');
		$query->join('LEFT', '#__phocacart_cart_multiple AS c ON c.user_id = u.id');

		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');



		$query->select('GROUP_CONCAT(DISTINCT g.title ORDER BY g.title) AS usergroups');
		$query->join('LEFT', '#__phocacart_item_groups AS ug ON ug.item_id=u.id AND ug.type = 1');
		$query->join('LEFT', '#__phocacart_groups AS g ON g.id=ug.group_id');



		// Filter by access level.
/*		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}*/



		// Filter by published state.
		/*$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('u.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(u.published IN (0, 1))');
		}*/


		// List only payment or shipping data - to not duplicity the list
		//$query->where('a.type = 0');


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

		// We have two rows for one customer - for billing and shipping address, but we need to list only one
		// is type zero (not one) or type does not exists (for users who are not stored in phoca user table yet)
		$query->where('(a.type = 0 OR a.type IS NULL)');


		// ONLY AS CUSTOMERS
	//	$query->where('c.vendor_id = 0');

		//$query->where('u.name <> '.$db->quote('Super User'));
		$query->group($groups);

		$user = $this->getState('filter.user_id');

		if (!empty($user)){
			$query->select('u2.name AS user_name_selected');
			$query->join('LEFT', '#__users AS u2 ON u2.id=a.user_id');
			$query->where('( u.id = '.(int)$user.')');
		}

		$orderCol	= $this->state->get('list.ordering', 'u.name');
		$orderDirn	= $this->state->get('list.direction', 'u.name');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
}
?>
