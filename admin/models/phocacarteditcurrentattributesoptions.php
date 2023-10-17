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
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditCurrentAttributesOptions extends ListModel
{
	protected	$option 		= 'com_phocacart';

	public function getData() {

		$app	= Factory::getApplication();
		$id		= $app->input->get('id', 0, 'int');

		$db = Factory::getDBO();
		$query = 'SELECT a.status_id'
		. ' FROM #__phocacart_orders AS a'
		. ' WHERE a.id = '.(int)$id
		. ' LIMIT 1';
		$db->setQuery( $query );
		$item = $db->loadObject();
		if (isset($item->status_id) && (int)$item->status_id > 0) {
			$status = PhocacartOrderStatus::getStatus($item->status_id);

			$status['select'] = HTMLHelper::_('select.genericlist',  $status['data'],  'jform[status_id]', 'class="form-select"', 'value', 'text', $item->status_id, 'jform_status_id' );
			return $status;
		}
		return array();

	}

	public function getHistoryData() {

		$app	= Factory::getApplication();
		$id		= $app->input->get('id', 0, 'int');

		if ((int)$id > 0) {
			$db = Factory::getDBO();
			$query = 'SELECT h.*,'
			. ' u.name AS user_name, u.username AS user_username,'
			. ' o.title as statustitle'
			. ' FROM #__phocacart_order_history AS h'
			. ' LEFT JOIN #__users AS u ON u.id = h.user_id'
			. ' LEFT JOIN #__phocacart_order_statuses AS o ON o.id = h.order_status_id'
			. ' WHERE h.order_id = '.(int)$id
			. ' ORDER BY h.date ASC';
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			return $items;
		}
	}

	public function editStatus($data) {

		$data['id']					= (int)$data['id'];
		$data['status_id']			= (int)$data['status_id'];
		$data['email_send']			= (int)$data['email_send'];
		$data['email_send_format']	= (int)$data['email_send_format'];
		$data['email_gift']			= isset($data['email_gift']) ? (int)$data['email_gift'] : 0;
		$row 				= $this->getTable('PhocacartOrder', 'Table');
		$user 				= Factory::getUser();

		if(isset($data['id']) && $data['id'] > 0) {
			if (!$row->load(array('id' => (int)$data['id']))) {
				// No data yet
			}
		}
		//$row->bind($data);

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->modified = $date = gmdate('Y-m-d H:i:s');


		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store the table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		// Store the history
		$db = Factory::getDBO();

		// EMAIL
		$notifyUser 	= 0;
		$notifyOther 	= 0;
		if (isset($data['notify_customer'])) {
			$notifyUser = 1;
		}
		if (isset($data['notify_others'])) {
			$notifyOther = 1;
		}

		// Set invoice data in case status can set invoice ID (before notify)
		PhocacartOrder::storeOrderReceiptInvoiceId((int)$data['id'], false, (int)$data['status_id'], array('I'));

		$notify = PhocacartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id'], '', $notifyUser, $notifyOther, (int)$data['email_send'], $data['stock_movements'], $data['change_user_group'], $data['change_points_needed'], $data['change_points_received'], (int)$data['email_send_format']);

		PhocacartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $data['comment_history']);




		return $row->id;
	}

	public function emptyHistory($id) {

		if ((int)$id > 0) {

			$db = Factory::getDBO();
			$query = 'DELETE FROM #__phocacart_order_history WHERE order_id = '.(int)$id;
			$db->setQuery( $query );

			if ($db->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}
}
?>
