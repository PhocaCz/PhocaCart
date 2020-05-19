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

class PhocaCartCpModelPhocacartOrder extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected $fieldsbas; //Billing and Shipping

	// Billing and Shipping
	public function getFieldsBaS(){
		if (empty($this->fieldsbas)) {
			$this->fieldsbas = PhocacartFormUser::getFormXml('_phb', '_phs', 1, 1, 0);
		}
		return $this->fieldsbas;
	}

	public function getFormBas($orderId) {

		$options			= array('control' => 'jform', 'load_data' => true);
		$options['control'] = \Joomla\Utilities\ArrayHelper::getValue($options, 'control', false);
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		try {
			$form = JForm::getInstance('com_phocacart.order.bas', (string)$this->fieldsbas['xml'], $options, false, false);
			$order= new PhocacartOrderView();
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

	public function getTable($type = 'PhocacartOrder', $prefix = 'Table', $config = array()) {
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

	protected function prepareTable($table) {
		$table->currency_exchange_rate 			= PhocacartUtils::replaceCommaWithPoint($table->currency_exchange_rate);
	}


	public function save($data) {

		$app	= JFactory::getApplication();
		if (!JSession::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}

		$order	= new PhocacartOrder();
		$jform 	= $app->input->get('jform', array(), 'array');
		$pform 	= $app->input->get('pform', array(), 'array');
		$aform 	= $app->input->get('aform', array(), 'array');
		$tform 	= $app->input->get('tform', array(), 'array');
		$dform 	= $app->input->get('dform', array(), 'array');
		$tcform	= $app->input->get('tcform', array(), 'array');




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

		// Discount Products
		if (!empty($dform)) {
			foreach ($dform as $k => $v) {
				$v['id'] 		= $k;
				if (isset($v['published'])) {
					$v['published'] = 1;
				} else {
					$v['published'] = 0;
				}
				$discount = $this->storeOrderProductDiscounts($v);
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

		// Tax Recapitulation
		if (!empty($tcform)) {
			foreach ($tcform as $k => $v) {
				$v['id'] = $k;
				$tc = $this->storeOrderTaxRecapitulation($v);
			}
		}


		// Main table
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		$currentStatus 	= 0;
		$newStatus 		= $data['status_id'];
		if ($pk > 0) {
			$table->load($pk);

			if (isset($table->status_id) && (int)$table->status_id > 0) {
				$currentStatus = (int)$table->status_id;
			}
			$isNew = false;
		} else {
			$app->enqueueMessage('Wrong ID', 'message');
			return false;
		}

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		$table->modified  = JFactory::getDate()->toSql();
		$date = $table->date;


		$this->prepareTable($table);

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Change status only if it really changed when editing

		if ((int)$currentStatus == (int)$newStatus) {
			// Status still the same, don't send email, don't change history
		} else {

			// Set invoice data in case status can set invoice ID (before notify)
			PhocacartOrder::storeOrderReceiptInvoiceId((int)$data['id'], $date, (int)$data['status_id'], array('I'));

			$notify 	= PhocacartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id']);
			$comment	= JText::_('COM_PHOCACART_ORDER_EDITED');

			// Store the history
			PhocacartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $comment);

		}




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
		$row = JTable::getInstance('PhocacartOrderProducts', 'Table', array());


		$d['netto'] 			= PhocacartUtils::replaceCommaWithPoint($d['netto']);
		$d['tax'] 				= PhocacartUtils::replaceCommaWithPoint($d['tax']);
		$d['brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['brutto']);

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
		$row = JTable::getInstance('PhocacartOrderAttributes', 'Table', array());


		$d['option_value'] = urlencode($d['option_value']);

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
		$row = JTable::getInstance('PhocacartOrderTotal', 'Table', array());


		$d['amount'] 			= PhocacartUtils::replaceCommaWithPoint($d['amount']);

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

	public function storeOrderTaxRecapitulation($d) {
		$row = JTable::getInstance('PhocacartOrderTaxRecapitulation', 'Table', array());

		$d['amount_netto'] 				= PhocacartUtils::replaceCommaWithPoint($d['amount_netto']);
		$d['amount_tax'] 				= PhocacartUtils::replaceCommaWithPoint($d['amount_tax']);
		$d['amount_brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['amount_brutto']);
		if (isset($d['amount_brutto_currency'])) {
            $d['amount_brutto_currency'] = PhocacartUtils::replaceCommaWithPoint($d['amount_brutto_currency']);
        }
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
		$row = JTable::getInstance('PhocacartOrderUsers', 'Table', array());

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

	public function storeOrderProductDiscounts($d) {
		$row = JTable::getInstance('PhocacartOrderProductDiscounts', 'Table', array());

		$d['netto'] 			= PhocacartUtils::replaceCommaWithPoint($d['netto']);
		$d['tax'] 			= PhocacartUtils::replaceCommaWithPoint($d['tax']);
		$d['brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['brutto']);

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
			\Joomla\Utilities\ArrayHelper::toInteger($cid);
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

			// 3. DELETE DISCOUNTS
			$query = 'DELETE FROM #__phocacart_order_discounts'
					. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 4. DELETE COUPONS
			$query = 'DELETE FROM #__phocacart_order_coupons'
					. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 5. DELETE DOWNLOADS
			$query = 'DELETE FROM #__phocacart_order_downloads'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 6. DELETE HISTORY
			$query = 'DELETE FROM #__phocacart_order_history'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 7. DELETE PRODUCTS
			$query = 'DELETE FROM #__phocacart_order_products'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 8. DELETE PRODUCT Discounts
			$query = 'DELETE FROM #__phocacart_order_product_discounts'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 9. DELETE TOTAL
			$query = 'DELETE FROM #__phocacart_order_total'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 10. DELETE RECAPITULATION
			$query = 'DELETE FROM #__phocacart_order_tax_recapitulation'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 11. DELETE USERS
			$query = 'DELETE FROM #__phocacart_order_users'
				. ' WHERE order_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

		}
		return true;
	}
}
?>
