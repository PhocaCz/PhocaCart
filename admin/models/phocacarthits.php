<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartHits extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'hits', 'a.hits',
				'product_id','a.product_id',
				'item','a.item',
				'user_id', 'a.user_id',
				'ip', 'a.ip',
				'date', 'a.date'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.date', $direction = 'DESC') {
		$app = Factory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

	/*	$userId = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id', null);
		$this->setState('filter.user_id', $userId);

		$productId = $app->getUserStateFromRequest($this->context.'.filter.product_id', 'filter_product_id', null);
		$this->setState('filter.product_id', $productId);
	*/
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		//$id	.= ':'.$this->getState('filter.product_id');
		//$id	.= ':'.$this->getState('filter.user_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$columns	= 'a.id, a.user_id, a.product_id, a.item, a.ip, a.type, a.hits, a.date';
		//$groupsFull	= $columns;
		//$groupsFast	= 'a.id';
		//$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

		$query->select($this->getState('list.select', $columns));
		$query->from('`#__phocacart_hits` AS a');

		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		$query->select('u.name AS user_name, u.username AS user_username');
		$query->join('LEFT', '#__users AS u ON u.id = a.user_id');


		$query->select('p.title AS product_title, p.alias AS product_alias');
		$query->join('LEFT', '#__phocacart_products AS p ON p.id = a.product_id');


	/*	// Filter by product.
		$productId = $this->getState('filter.product_id');
		if (is_numeric($productId)) {
			$query->where('a.product_id = ' . (int) $productId);
		}

		// Filter by user.
		$userId = $this->getState('filter.user_id');
		if (is_numeric($userId)) {
			$query->where('a.user_id = ' . (int) $userId);
		}

	*/

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
				$query->where('( p.title LIKE '.$search.' OR p.alias LIKE '.$search
				.' OR u.name LIKE '.$search.' OR u.username LIKE '.$search
				.' OR a.item LIKE '.$search.')');
			}
		}

		//$query->group('a.id');

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'date');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));

		return $query;
	}

}
?>
