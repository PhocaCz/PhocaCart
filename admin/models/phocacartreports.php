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

class PhocaCartCpModelPhocacartReports extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'date', 'a.date',
				'order_number', 'a.order_number',
				'currency_code', 'a.currency_code',
				'type', 'a.type'
			);
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.date', $direction = 'DESC')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

/*		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);*/

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$currency = $app->getUserStateFromRequest($this->context.'.filter.currency', 'filter_currency', '');
		$this->setState('filter.currency', $currency);


		$shopType = $app->getUserStateFromRequest($this->context.'.filter.shop_type', 'filter_shop_type', '');
		$this->setState('filter.shop_type', $shopType);

		$orderStatus = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '');
		$this->setState('filter.order_status', $orderStatus);

		//$order = $app->getUserStateFromRequest($this->context.'.filter.order', 'filter_order', '');
		//$this->setState('filter.order', $order);

		$date_from = $app->getUserStateFromRequest($this->context.'.filter.date_from', 'filter_date_from', PhocacartDate::getCurrentDate(30), 'string');
		$this->setState('filter.date_from', $date_from);

		$date_to = $app->getUserStateFromRequest($this->context.'.filter.date_to', 'filter_date_to', PhocacartDate::getCurrentDate(), 'string');
		$this->setState('filter.date_to', $date_to);



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
		//$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.date_from');
		$id	.= ':'.$this->getState('filter.date_to');
		$id	.= ':'.$this->getState('filter.currency');
		$id	.= ':'.$this->getState('filter.shop_type');
		$id	.= ':'.$this->getState('filter.order_status');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				//- 'a.id, DATE(a.date) AS date_only, COUNT(DATE(a.date)) AS count_orders'
				//'DATE(a.date) AS date_only, COUNT(DATE(a.date)) AS count_orders'
				'a.id, a.date, a.order_number, a.currency_id, a.currency_code, a.currency_exchange_rate, a.type, a.payment_id'
			)
		);
		$query->from('`#__phocacart_orders` AS a');

		//$query->select('SUM(t.amount) AS order_amount');
		//$query->join('LEFT', '#__phocacart_order_total AS t ON a.id=t.order_id');
		//$query->where('t.type = \'brutto\'' );

		// Filter by order status
	/*	$whereOrderStatus = '';
		if (!PhocacartStatistics::setWhereByOrderStatus($whereOrderStatus)) {
			$dummyQuery = 'SELECT "" AS date_only, 0 AS count_orders FROM `#__phocacart_orders`';
			return $dummyQuery;
		}
		if ($whereOrderStatus != '') {
			$query->where( $whereOrderStatus );
		}*/

		// TOTAL
		/*$query->select('tn.amount AS total_netto');
		$query->join('LEFT', '#__phocacart_order_total AS tn ON a.id = tn.order_id');
		$query->where('tn.type = '.$db->quote('netto'));

		$query->select('SUM(tv.amount) AS total_vat');
		$query->join('LEFT', '#__phocacart_order_total AS tv ON a.id = tv.order_id');
		$query->where('tv.type = '.$db->quote('tax'));

		$query->select('tb.amount AS total_brutto, tb.amount_currency AS total_brutto_currency');
		$query->join('LEFT', '#__phocacart_order_total AS tb ON a.id = tb.order_id');
		$query->where('tb.type = '.$db->quote('brutto'));*/

		// USERS
		$query->select('ou.name_first AS user_name_first, ou.name_last AS user_name_last, ou.company AS user_company, ou.vat_1 AS user_vat_1,'
		.' ou.address_1 AS user_address_1, ou.city AS user_city, ou.zip AS user_zip, co.title AS user_country');
		$query->join('LEFT', '#__phocacart_order_users AS ou ON a.id = ou.order_id AND ou.type = 0');
		$query->join('LEFT', '#__phocacart_countries AS co ON ou.country = co.id');


		// PAYMENT
		$query->select('p.title as payment_title');
		$query->join('LEFT', '#__phocacart_payment_methods AS p ON a.payment_id = p.id');



		// Filter by search in title
		$dateFrom = $this->getState('filter.date_from', PhocacartDate::getCurrentDate(30));
		$dateTo = $this->getState('filter.date_to', PhocacartDate::getCurrentDate());


		if ($dateTo != '' && $dateFrom != '') {
			$dateFrom 	= $db->Quote($dateFrom);
			$dateTo 	= $db->Quote($dateTo);
			$query->where('DATE(a.date) >= '.$dateFrom.' AND DATE(a.date) <= '.$dateTo );
		}

		$currency = $this->getState('filter.currency', 0);
		if ($currency > 0) {
			$query->where('a.currency_id = '.(int)$currency );
		}
		$shopType = $this->getState('filter.shop_type', 0);
		if ($shopType > 0) {
			$query->where('a.type = '.(int)$shopType );
		}

		$orderStatus = $this->getState('filter.order_status', 0);
		if ($orderStatus > 0) {
			$query->where('a.status_id = '.(int)$orderStatus );
		}


		//- $query->group('DATE(a.date), a.id');
		//$query->group('DATE(a.date)');
		$query->group('a.id');



		$orderCol	= $this->state->get('list.ordering', 'a.date');
		$orderDirn	= $this->state->get('list.direction', 'DESC');

		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
}
?>
