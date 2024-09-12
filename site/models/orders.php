<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
jimport('joomla.application.component.model');

class PhocaCartModelOrders extends BaseDatabaseModel
{
	protected $orders 				= null;
	protected $orders_ordering		= null;
	protected $pagination			= null;
	protected $total				= null;

	public function __construct() {
		parent::__construct();

		$app		= Factory::getApplication();
		$config 	= Factory::getConfig();
		//$paramsC 	= PhocacartUtils::getComponentParameters() ;
		$paramsC 	= $app->getParams();
		$defaultP	= (int)$paramsC->get( 'default_pagination', '20' );
		$this->setState('limit', $app->getUserStateFromRequest('com_phocacart.orders.limit', 'limit', $defaultP, 'int'));
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', Factory::getApplication()->input->get('filter_order', 'ordering'));
		$this->setState('filter_order_dir', Factory::getApplication()->input->get('filter_order_Dir', 'ASC'));

	}

	public function getPagination($userId) {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocacartPagination( $this->getTotal($userId), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}

	public function getTotal() {
		if (empty($this->total)) {
			$query = $this->getOrderListQuery();
			$this->total = $this->_getListCount($query);
		}
		return $this->total;
	}

	public function getOrderList() {
		if (empty($this->orders)) {
			$query			= $this->getOrderListQuery();
			$this->orders	= $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->orders;
	}

	protected function getOrderListQuery() {

		$app				= Factory::getApplication();
		$params 			= $app->getParams();
		$u					= PhocacartUser::getUser();
		$token				= $app->input->get('o', '', 'string');
		$orderGuestAccess	= $params->get( 'order_guest_access', 0 );
		if ($orderGuestAccess == 0) {
			$token = '';
		}
		$wheres		= array();
		$wheres[] 	= ' o.published = 1';
		if ($token != '') {
			$wheres[]	= ' o.order_token = '.$this->_db->quote($token);
		} else {
			$wheres[]	= ' o.user_id = '.(int)$u->id;
		}
		$wheres[]	= ' t.type = '.$this->_db->quote('brutto');

		$ordering = $this->getOrderOrdering();
		$query = ' SELECT o.*,'
		.' os.title AS status_title,'
		.' t.amount AS total_amount,'
		.' s.id AS shippingid, s.title AS shippingtitle, s.tracking_link as shippingtrackinglink, s.tracking_description as shippingtrackingdescription, os.orders_view_display as ordersviewdisplay'
		.' FROM #__phocacart_orders AS o'
		.' LEFT JOIN #__phocacart_order_statuses AS os ON os.id = o.status_id'
		.' LEFT JOIN #__phocacart_order_total AS t ON o.id = t.order_id'
		.' LEFT JOIN #__phocacart_shipping_methods AS s ON s.id = o.shipping_id'
		.' WHERE ' . implode( ' AND ', $wheres )
		.' ORDER BY '.$ordering;

		return $query;
	}

	protected function getOrderOrdering() {
		if (empty($this->orders_ordering)) {
			$app						= Factory::getApplication();
			$params						= $app->getParams();
			$ordering					= $params->get( 'order_ordering', 8 );
			$this->orders_ordering 		= PhocacartOrdering::getOrderingText($ordering, 2);
		}
		return $this->orders_ordering;
	}
}
?>
