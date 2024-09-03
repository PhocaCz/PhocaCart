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
use Joomla\Database\QueryInterface;

jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocacartOrders extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'order_number', 'order_number',
				'user_username','user_username',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'status_id', 'a.status_id',
				'date', 'a.date',
				'total_amount', 'total_amount',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'published','a.published',
				'payment_id', 'a.payment_id',
				'shipping_id', 'a.shipping_id'
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

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

		$status = $app->getUserStateFromRequest($this->context.'.filter.status_id', 'filter_status_id', '');
		$this->setState('filter.status_id', $status);

		$payment = $app->getUserStateFromRequest($this->context.'.filter.payment_id', 'filter_payment_id', '');
		$this->setState('filter.payment_id', $payment);

		$shipping = $app->getUserStateFromRequest($this->context.'.filter.shipping_id', 'filter_shipping_id', '');
		$this->setState('filter.shipping_id', $shipping);

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
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.status_id');
		$id	.= ':'.$this->getState('filter.payment_id');
		$id	.= ':'.$this->getState('filter.shipping_id');

		return parent::getStoreId($id);
	}

	private function listQueryFilter(QueryInterface $query)
	{
		$db = $this->getDbo();

		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		$status = (int)$this->getState('filter.status_id');
		if (!empty($status)) {

			if ($status != '' && $status > 0) {
				$query->where('a.status_id = '.$status);
			}

		}

		$payment = (int)$this->getState('filter.payment_id');
		if (!empty($payment)) {

			if ($payment != '' && $payment > 0) {
				$query->where('a.payment_id = '.$payment);
			}

		}

		$shipping = (int)$this->getState('filter.shipping_id');
		if (!empty($shipping)) {

			if ($shipping != '' && $shipping > 0) {
				$query->where('a.shipping_id = '.$shipping);
			}

		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '. (int)substr($search, 3));
			} elseif (is_numeric($search)) {
				// Searching numeric value, so we search oonly ID and order numbers. This is must faster than numeric search
				$searchInP = [];
				$searchInP[] = 'a.id = '. (int)$search;
				//$searchInP[] = 'match(a.order_number, a.receipt_number, a.invoice_number) against (' . $db->Quote($search) . ')' ;

				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$searchInP[] = 'a.order_number LIKE '. $search;
				$searchInP[] = 'a.receipt_number LIKE '. $search;
				$searchInP[] = 'a.invoice_number LIKE '. $search;

				$query->where('('.implode(' OR ', $searchInP).')');
			} else {
				$searchInUser = array('name_first', 'name_middle', 'name_last', 'name_degree', 'company', 'vat_1', 'vat_2', 'address_1', 'address_2', 'city', 'zip', 'email', 'email_contact', 'phone_1', 'phone_2', 'phone_mobile' );

				$searchInP =  [];
				$searchInP[] = 'a.id in (' .
					'select order_id from #__phocacart_order_users where match(' . implode(', ', $searchInUser) . ') against (' . $db->Quote($search) . ')'.
					')';

				$query->where('('.implode(' OR ', $searchInP).')');

				/*
				$searchIn = array('name_first', 'name_middle', 'name_last', 'name_degree', 'company', 'vat_1', 'vat_2', 'address_1', 'address_2', 'city', 'zip', 'email', 'email_contact', 'phone_1', 'phone_2', 'phone_mobile', 'fax' );

				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$searchInP =  array();

				$searchInP[] = 'a.order_number LIKE '. $search;
				$searchInP[] = 'a.receipt_number LIKE '. $search;
				$searchInP[] = 'a.invoice_number LIKE '. $search;
				$searchInP[] = 'a.comment LIKE '. $search;
				$searchInP[] = 'co0.title LIKE '. $search;
				$searchInP[] = 'co1.title LIKE '. $search;
				$searchInP[] = 're0.title LIKE '. $search;
				$searchInP[] = 're1.title LIKE '. $search;
				foreach($searchIn as $k => $v) {
					$searchInP[] = 'us0.'.$v . ' LIKE '. $search;// search in billing address
					$searchInP[] = 'us1.'.$v . ' LIKE '. $search;// search in shipping address
				}

				$query->where('('.implode(' OR ', $searchInP).')');
				*/
			}
		}
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, a.id as ordernumber'
			)
		);
		$query->from('`#__phocacart_orders` AS a');

		$query->select('u.name AS user_name, u.username AS user_username');
		$query->join('LEFT', '#__users AS u ON u.id=a.user_id');

		$query->select('uv.name AS vendor_name, uv.username AS vendor_username');
		$query->join('LEFT', '#__users AS uv ON uv.id=a.vendor_id');


		$query->select('sc.title AS section_name');
		$query->join('LEFT', '#__phocacart_sections AS sc ON sc.id=a.section_id');

		$query->select('un.title AS unit_name');
		$query->join('LEFT', '#__phocacart_units AS un ON un.id=a.unit_id');


		$query->select('os.title AS status_title, os.params AS status_params');
		$query->join('LEFT', '#__phocacart_order_statuses AS os ON os.id = a.status_id');

		$query->select('sm.title AS shipping_name');
		$query->join('LEFT', '#__phocacart_shipping_methods AS sm ON sm.id=a.shipping_id');

		$query->select('pm.title AS payment_name');
		$query->join('LEFT', '#__phocacart_payment_methods AS pm ON pm.id=a.payment_id');

		$query->select('t.amount AS total_amount, t.amount_currency AS total_amount_currency');
		$query->join('LEFT', '#__phocacart_order_total AS t ON a.id = t.order_id AND (t.type = '.$db->quote('brutto').' OR t.type = \'\')');
		// $query->where('(t.type = '.$db->quote('brutto').' OR t.type = \'\' OR t.type IS NULL)');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		$this->listQueryFilter($query);

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getTotal()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('count(*)')
			->from('`#__phocacart_orders` AS a');

		$this->listQueryFilter($query);

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}
}

