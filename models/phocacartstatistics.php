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
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartStatistics extends JModelList
{
	protected $option 	= 'com_phocacart';	
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id'
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

/*		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);*/

		$state = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $state);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);
		
		$date_from = $app->getUserStateFromRequest($this->context.'.filter.date_from', 'filter_date_from', PhocaCartDate::getCurrentDate(30), 'string');
		$this->setState('filter.date_from', $date_from);
		
		$date_to = $app->getUserStateFromRequest($this->context.'.filter.date_to', 'filter_date_to', PhocaCartDate::getCurrentDate(), 'string');
		$this->setState('filter.date_to', $date_to);

		
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_phocacart');
		$this->setState('params', $params);
		
		

		// List state information.
		parent::populateState('a.date', 'desc');
	}
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		//$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.date_from');
		$id	.= ':'.$this->getState('filter.date_to');

		return parent::getStoreId($id);
	}
	
	protected function getListQuery() {
		
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.id, DATE(a.date) AS date_only, COUNT(DATE(a.date)) AS count_orders'
			)
		);
		$query->from('`#__phocacart_orders` AS a');
		
		$query->select('SUM(t.amount) AS order_amount');
		$query->join('LEFT', '#__phocacart_order_total AS t ON a.id=t.order_id');
		$query->where('t.type = \'brutto\'' );
	

		// Filter by search in title
		$dateFrom = $this->getState('filter.date_from', PhocaCartDate::getCurrentDate(30));
		$dateTo = $this->getState('filter.date_to', PhocaCartDate::getCurrentDate());


		if ($dateTo != '' && $dateFrom != '') {
			$dateFrom 	= $db->Quote($dateFrom);
			$dateTo 	= $db->Quote($dateTo);
			$query->where('DATE(a.date) >= '.$dateFrom.' AND DATE(a.date) <= '.$dateTo );
		}
		$query->group('DATE(a.date)');

		$query->order($db->escape('a.date ASC'));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
}
?>