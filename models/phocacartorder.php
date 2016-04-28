<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartOrder extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	protected $fieldsbas; //Billing and Shipping

	// Billing and Shipping
	public function getFieldsBaS(){
		if (empty($this->fieldsbas)) {
			$this->fieldsbas = PhocaCartFormUser::getFormXml('_phb', '_phs', 1, 1, 0);
		}
		return $this->fieldsbas;
	}
	
	public function getFormBas($orderId) {
		
		$options			= array('control' => 'jform', 'load_data' => true);
		$options['control'] = JArrayHelper::getValue($options, 'control', false);
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');
		
		try {
			$form = JForm::getInstance('com_phocacart.order.bas', (string)$this->fieldsbas['xml'], $options, false, false);
			$order= new PhocaCartOrderView();
			$data = $order->getItemBaS($orderId);
			$this->preprocessForm($form, $data);
			$form->bind($data);
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	
	/* Order table */
	protected function canDelete($record) {
		return parent::canDelete($record);
	}
	
	protected function canEditState($record) {
		return parent::canEditState($record);
	}
	
	public function getTable($type = 'PhocaCartOrder', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartorder', 'phocacartorder', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacartorder.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	protected function prepareTable($table) {}
	
	
	public function save($data) {
		
		$app	= JFactory::getApplication();
		if (!JRequest::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$order	= new PhocaCartOrder();
		$jform 	= $app->input->get('jform', array(), 'array');
		$pform 	= $app->input->get('pform', array(), 'array');
		$aform 	= $app->input->get('aform', array(), 'array');
		$tform 	= $app->input->get('tform', array(), 'array');
		
		
		// Shipping, Billing
		if(!empty($jform)) {
			
			// Form Data
			$billing	= array();
			$shipping	= array();
			foreach($jform as $k => &$v) {
				$posB = strpos($k, '_phb');
				if ($posB === false) {
			
				} else {
					$k = str_replace('_phb', '', $k);
					$billing[$k] = $v;
				}
				
				$posS = strpos($k, '_phs');
				if ($posS === false) {
			
				} else {
					$k = str_replace('_phs', '', $k);
					$shipping[$k] = $v;
				}
			}
			
			
			$billingO 	= $this->storeOrderAddress($billing);
			$shippingO 	= $this->storeOrderAddress($shipping);
		}
		
		// Products
		if (!empty($pform)) {
			foreach ($pform as $k => $v) {
				$v['id'] = $k;
				if (isset($v['published'])) {
					$v['published'] = 1;
				} else {
					$v['published'] = 0;
				}
				$product = $this->storeOrderProducts($v);
			}
		}
		
		// Attributes
		if (!empty($aform)) {
			foreach ($aform as $k => $v) {
				$v['id'] = $k;
				$attribute = $this->storeOrderAttributes($v);
			}
		}
		
		// Total
		if (!empty($tform)) {
			foreach ($tform as $k => $v) {
				$v['id'] = $k;
				if (isset($v['published'])) {
					$v['published'] = 1;
				} else {
					$v['published'] = 0;
				}
				$total = $this->storeOrderTotal($v);
			}
		}
		// Main table
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		} else {
			$app->enqueueMessage('Wrong ID', 'message');
			return false;
		}

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		$table->modified = $date = JFactory::getDate()->toSql();
		$this->prepareTable($table);

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}
		
		// Store the history
		$notify 	= PhocaCartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id']); 
		$comment	= JText::_('COM_PHOCACART_ORDER_EDITED');
	
		PhocaCartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $comment);
		
		
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}
	
	
	public function storeOrderProducts($d) {
		$row = JTable::getInstance('PhocaCartOrderProducts', 'Table', array());

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
	}
	
	public function storeOrderAttributes($d) {
		$row = JTable::getInstance('PhocaCartOrderAttributes', 'Table', array());

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
	}
	
	public function storeOrderTotal($d) {
		$row = JTable::getInstance('PhocaCartOrderTotal', 'Table', array());

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
	}
	
	public function storeOrderAddress($d) {
		$row = JTable::getInstance('PhocaCartOrderUsers', 'Table', array());

		if (!$row->bind($d)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->check()) {
			throw new Exception($row->getError());
			return false;
		}
		
		if (!$row->store()) {
			throw new Exception($row->getError());
			return false;
		}
	}
	
	function delete(&$cid = array()) {
		
		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			
		
			
			// 1. DELETE ITEMS
			$query = 'DELETE FROM #__phocacart_orders'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			
			
			// 2. DELETE ATTRIBUTES
			$query = 'DELETE FROM #__phocacart_order_attributes'
					. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 3. DELETE COUPONS
			$query = 'DELETE FROM #__phocacart_order_coupons'
					. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 4. DELETE DOWNLOADS
			$query = 'DELETE FROM #__phocacart_order_downloads'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 5. DELETE HISTORY
			$query = 'DELETE FROM #__phocacart_order_history'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 6. DELETE PRODUCTS
			$query = 'DELETE FROM #__phocacart_order_products'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 7. DELETE TOTAL
			$query = 'DELETE FROM #__phocacart_order_total'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 8. DELETE USERS
			$query = 'DELETE FROM #__phocacart_order_users'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
		}
		return true;
	}
}
?>