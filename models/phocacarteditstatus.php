<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditStatus extends JModelList
{
	protected	$option 		= 'com_phocacart';
	
	public function getData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		
		$db = JFactory::getDBO();
		$query = 'SELECT a.status_id'
		. ' FROM #__phocacart_orders AS a'
		. ' WHERE a.id = '.(int)$id
		. ' LIMIT 1';
		$db->setQuery( $query );
		$item = $db->loadObject();
		if (isset($item->status_id) && (int)$item->status_id > 0) {
			$status = PhocaCartOrderStatus::getStatus($item->status_id);
			
			$status['select'] = JHTML::_('select.genericlist',  $status['data'],  'jform[status_id]', 'class="inputbox"', 'value', 'text', $item->status_id, 'jform_status_id' );
			return $status;
		}
		return array();	

	}
	
	public function getHistoryData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		
		if ((int)$id > 0) {
			$db = JFactory::getDBO();
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
		
		$data['id']			= (int)$data['id'];
		$data['status_id']	= (int)$data['status_id'];
		$data['email_send']	= (int)$data['email_send'];
		$row 				= $this->getTable('PhocaCartOrder', 'Table');
		$user 				= JFactory::getUser();

		if(isset($data['id']) && $data['id'] > 0) {
			if (!$row->load(array('id' => (int)$data['id']))) {
				// No data yet
			}
		}
		//$row->bind($data);

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->modified = $date = gmdate('Y-m-d H:i:s');
		

		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Store the history
		$db = JFactory::getDBO();
		
		// EMAIL
		$notifyUser 	= 0;
		$notifyOther 	= 0;
		if (isset($data['notify_customer'])) {
			$notifyUser = 1;
		}
		if (isset($data['notify_others'])) {
			$notifyOther = 1;
		}
		
		$notify = PhocaCartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id'], '', $notifyUser, $notifyOther, (int)$data['email_send'], $data['stock_movements']); 
		
		PhocaCartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $data['comment']);
		
		
		return $row->id;
	}
	
	public function emptyHistory($id) {
		
		if ((int)$id > 0) {
		
			$db = JFactory::getDBO();
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