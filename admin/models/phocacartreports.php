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
				'type', 'a.type',
				'op.title'
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

		$reportType = $app->getUserStateFromRequest($this->context.'.filter.report_type', 'filter_report_type', '');
		$this->setState('filter.report_type', $reportType);

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
		$id	.= ':'.$this->getState('filter.report_type');
		$id	.= ':'.$this->getState('filter.order_status');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// STATUS DATE 1)
		// reportType = 1 - info about status and status date - a) status needs to be selected, b) this type needs to be selected then status date will be displayed
		// status date = first date when the status was changes. E.g. if system set the status to paid, print the date of paying.
		$reportType = $this->getState('filter.report_type', 0);



		if ($reportType == 2) {
			// PRODUCT STAT
			$query->select(
				$this->getState(
					'list.select',
					'op.id, op.order_id, op.product_id, op.product_id_key, op.title, op.netto, op.tax, op.brutto, op.quantity'
				)
			);
			$query->from('`#__phocacart_order_products` AS op');

			$query->join('LEFT', '#__phocacart_orders AS a ON op.order_id = a.id');


			// ATTRIBUTES, OPTIONS (fast)

			// POSTGRESQL - if compatibility is needed then it needs to be customized:
			// PostgreSQL uses json_agg or jsonb_agg instead of JSON_ARRAYAGG
			// PostgreSQL uses json_build_object or jsonb_build_object instead of JSON_OBJECT

			$query->select('JSON_ARRAYAGG(
				JSON_OBJECT(
					\'attribute_title\', at.attribute_title,
					\'option_title\', at.option_title
				)
			) as product_attributes');

			$query->join('LEFT', '#__phocacart_order_attributes AS at ON at.order_product_id = op.id');


			// DISCOUNTS
			$query->select('opd.netto AS opd_netto, opd.tax AS opd_tax, opd.brutto as opd_brutto, opd.quantity AS opd_quantity');

			//$query->join('LEFT', '#__phocacart_order_product_discounts AS opd ON opd.order_product_id = op.id');

		/*	$query->join('LEFT', '(SELECT opd1.order_product_id, opd1.netto, opd1.tax, opd1.brutto, opd1.quantity FROM #__phocacart_order_product_discounts opd1 WHERE opd1.type = (
        SELECT MAX(opd2.type)
        FROM #__phocacart_order_product_discounts opd2
        WHERE opd2.order_product_id = opd1.order_product_id
    )) AS opd ON opd.order_product_id = op.id');*/


			// MAX(type) ... type is the type of the sale (product_discount, cart_discount, coupon) so select the last one to know the final price for each product (after all sales)
			$query->join('LEFT', '#__phocacart_order_product_discounts AS opd
				ON opd.order_product_id = op.id
				AND opd.type = (
					SELECT MAX(type)
					FROM #__phocacart_order_product_discounts
					WHERE order_product_id = op.id
			)');



			// Filter by search in title
			$dateFrom = $this->getState('filter.date_from', PhocacartDate::getCurrentDate(30));
			$dateTo   = $this->getState('filter.date_to', PhocacartDate::getCurrentDate());

			if ($dateTo != '' && $dateFrom != '') {
				$dateFrom = $db->Quote($dateFrom);
				$dateTo   = $db->Quote($dateTo);
				$query->where('DATE(a.date) >= ' . $dateFrom . ' AND DATE(a.date) <= ' . $dateTo);
			}

			$currency = $this->getState('filter.currency', 0);
			if ($currency > 0) {
				$query->where('a.currency_id = ' . (int)$currency);
			}
			$shopType = $this->getState('filter.shop_type', 0);
			if ($shopType > 0) {
				$query->where('a.type = ' . (int)$shopType);
			}

			/*$reportType = $this->getState('filter.report_type', 0);
			if ($reportType > 0) {
				//$query->where('a.type = '.(int)$reportType );
			}*/

			$orderStatus = $this->getState('filter.order_status', 0);
			if ($orderStatus > 0) {
				$query->where('a.status_id = ' . (int)$orderStatus);
			}

			$query->group('a.id, op.product_id_key');


			$orderCol  = $this->state->get('list.ordering', 'a.date');
			$orderDirn = $this->state->get('list.direction', 'DESC');


			$query->order($db->escape($orderCol . ' ' . $orderDirn));


		} else {
			// DEFAULT


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
				. ' ou.address_1 AS user_address_1, ou.city AS user_city, ou.zip AS user_zip, co.title AS user_country');
			$query->join('LEFT', '#__phocacart_order_users AS ou ON a.id = ou.order_id AND ou.type = 0');
			$query->join('LEFT', '#__phocacart_countries AS co ON ou.country = co.id');


			// PAYMENT
			$query->select('p.title as payment_title');
			$query->join('LEFT', '#__phocacart_payment_methods AS p ON a.payment_id = p.id');


			// Filter by search in title
			$dateFrom = $this->getState('filter.date_from', PhocacartDate::getCurrentDate(30));
			$dateTo   = $this->getState('filter.date_to', PhocacartDate::getCurrentDate());

			if ($dateTo != '' && $dateFrom != '') {
				$dateFrom = $db->Quote($dateFrom);
				$dateTo   = $db->Quote($dateTo);
				$query->where('DATE(a.date) >= ' . $dateFrom . ' AND DATE(a.date) <= ' . $dateTo);
			}

			$currency = $this->getState('filter.currency', 0);
			if ($currency > 0) {
				$query->where('a.currency_id = ' . (int)$currency);
			}
			$shopType = $this->getState('filter.shop_type', 0);
			if ($shopType > 0) {
				$query->where('a.type = ' . (int)$shopType);
			}

			/*$reportType = $this->getState('filter.report_type', 0);
			if ($reportType > 0) {
				//$query->where('a.type = '.(int)$reportType );
			}*/

			$orderStatus = $this->getState('filter.order_status', 0);
			if ($orderStatus > 0) {
				$query->where('a.status_id = ' . (int)$orderStatus);

				// STATUS DATE 2)
				$query->select('oh.order_history_date');
				//$query->join('LEFT', '#__phocacart_order_history AS oh ON oh.order_id = a.id AND order_status_id = ' .(int)$orderStatus );

				$query->join('LEFT', '(SELECT order_id, MIN(date) AS order_history_date FROM #__phocacart_order_history WHERE order_status_id = ' . (int)$orderStatus . ' GROUP BY order_id) AS oh ON oh.order_id = a.id');
			}


			//- $query->group('DATE(a.date), a.id');
			//$query->group('DATE(a.date)');
			$query->group('a.id');


			$orderCol  = $this->state->get('list.ordering', 'a.date');

			// We use ordering for different statistics, so remove not used
			if ($orderCol == 'op.title') {
				$this->state->set('list.ordering', 'a.date');
				$orderCol  = $this->state->get('list.ordering', 'a.date');
			}

			$orderDirn = $this->state->get('list.direction', 'DESC');
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		//b dump(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
}
?>
