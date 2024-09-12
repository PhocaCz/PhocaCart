<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class PhocaCartCpModelPhocaCartEditStatus extends AdminModel
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
		if ($item->status_id > 0) {
			$status = PhocacartOrderStatus::getStatus($item->status_id);

			$status['select'] = HTMLHelper::_('select.genericlist',  $status['data'],  'jform[status_id]', 'class="form-select"', 'value', 'text', $item->status_id, 'jform_status_id' );
			return $status;
		}

		return [];
	}

	public function getHistoryData() {
		$app	= Factory::getApplication();
		$id		= $app->input->get('id', 0, 'int');

		if ((int)$id > 0) {
			$db = Factory::getDBO();
			$query = 'SELECT h.*,'
			. ' u.name AS user_name, u.username AS user_username,'
			. ' o.title as statustitle, o.params as status_params'
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
        $order = $this->getTable('PhocacartOrder', 'Table');
        if (!$order->load(['id' => (int)$data['id']])) {
            return false;
        }

        $statusChanged = $data['status_id'] !== '';
        $commentChanged = !!trim($data['comment_history']);
        $trackingChanged = !!trim($data['tracking_number']);
        $trackingDateChanged = !!trim($data['tracking_date_shipped']);

        if ($statusChanged || $commentChanged || $trackingChanged || $trackingDateChanged) {
            if ($statusChanged) {
                $status = PhocacartOrderStatus::getStatus($data['status_id']);
            } else {
                $status = PhocacartOrderStatus::getStatus($order->status_id);
            }

            $orderData = [];
            if ($statusChanged || $commentChanged) {
                $orderData['status_id']         = $status['id'];
                $orderData['email_send']        = $data['email_send'] !== '' ? $data['email_send'] : $status['email_send'];
                $orderData['email_send_format'] = $data['email_send_format'] !== '' ? $data['email_send_format'] : $status['email_send_format'];
                $orderData['email_gift']        = $status['email_gift'];
            }

            if ($trackingChanged) {
                $orderData['tracking_number']        = $data['tracking_number'];
            }

            if ($trackingDateChanged) {
                $orderData['tracking_date_shipped'] = $data['tracking_date_shipped'];
            }

            if (!$order->bind($orderData)) {
                $this->setError($order->getError());

                return false;
            }

            $order->modified = gmdate('Y-m-d H:i:s');


            if (!$order->check()) {
                $this->setError($order->getError());

                return false;
            }

            if (!$order->store()) {
                $this->setError($order->getError());

                return false;
            }

            if ($statusChanged || $commentChanged) {
                PhocacartOrder::storeOrderReceiptInvoiceId($order->id, false, $status['id'], ['I']);
                
                $notifyUser           = $data['notify_customer'] !== '' ? !!$data['notify_customer'] : !!$status['email_customer'];
                $notifyOther          = $data['notify_others'] !== '' ? !!$data['notify_others'] : !!$status['email_others'];
                $emailSend            = $data['email_send'] !== '' ? $data['email_send'] : $status['email_send'];
                $stockMovements       = $data['stock_movements'] !== '' ? $data['stock_movements'] : $status['stock_movements'];
                $changeUserGroup      = $data['change_user_group'] !== '' ? $data['change_user_group'] : $status['change_user_group'];
                $changePointsNeeded   = $data['change_points_needed'] !== '' ? $data['change_points_needed'] : $status['change_points_needed'];
                $changePointsReceived = $data['change_points_received'] !== '' ? $data['change_points_received'] : $status['change_points_received'];
                $emailSendFormat      = $data['email_send_format'] !== '' ? $data['email_send_format'] : $status['email_send_format'];

                $notify = PhocacartOrderStatus::changeStatus($order->id, $status['id'], '', $notifyUser, $notifyOther,
                    $emailSend, $stockMovements, $changeUserGroup, $changePointsNeeded, $changePointsReceived, $emailSendFormat
                );

                PhocacartOrderStatus::setHistory($order->id, $status['id'], (int) $notify, trim($data['comment_history']));
            }
        }

        return true;
	}

	public function emptyHistory($id): bool {
		if ((int)$id > 0) {
			$db = Factory::getDBO();
			$db->setQuery('DELETE FROM #__phocacart_order_history WHERE order_id = '.(int)$id);

			return $db->execute();
		}

		return false;
	}

	public function getForm($data = [], $loadData = true)
	{
		return $this->loadForm('com_phocacart.phocacartorderstatus', 'phocacartorderstatus', ['control' => 'jform', 'load_data' => $loadData]);
	}

    protected function loadFormData() {
        $app	= Factory::getApplication();
        $id		= $app->input->get('id', 0, 'int');

        return [
            'id' => $id,
        ];
    }
}
