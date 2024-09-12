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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class PhocaCartCpModelPhocacartOrder extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected $fieldsbas; //Billing and Shipping

	// Billing and Shipping
	public function getFieldsBaS() {
		if (empty($this->fieldsbas)) {
			$this->fieldsbas = PhocacartFormUser::getFormXml('_phb', '_phs', 1, 1, 0);
		}
		return $this->fieldsbas;
	}

	public function getFormBas($orderId) {
		$options = ['control' => 'jform', 'load_data' => true];
		Form::addFormPath(JPATH_COMPONENT . '/models/forms');
		Form::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try {
			$form = Form::getInstance('com_phocacart.order.bas', (string)$this->fieldsbas['xml'], $options, false, false);
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
	public function getTable($type = 'PhocacartOrder', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = [], $loadData = true) {
        /** @var Form $form */
		$form = $this->loadForm('com_phocacart.phocacartorder', 'phocacartorder', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form)) {
			return false;
		}

        PhocacartFormUser::loadAddressForm($form, true, true, false, true, false);

		return $form;
	}

    protected function preprocessForm(Form $form, $data, $group = 'content') {
        PhocacartFormUser::loadAddressForm($form, true, true, false, true, false);

        parent::preprocessForm($form, $data, $group);
    }

    protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartorder.data', []);

		if (empty($data)) {
			$data = $this->getItem();
            if (is_object($data)) {
                if ($data instanceof \Joomla\Registry\Registry) {
                    $data = $data->toArray();
                } else if ($data instanceof \Joomla\CMS\Object\CMSObject) {
                    $data = $data->getProperties();
                } else {
                    $data = (array)$data;
                }
            }
		}

        $orderView = new PhocacartOrderView();
        $addressData = $orderView->getItemBaS($data['id'], 1);
        $addressData['s']['ba_sa'] = $addressData['b']['ba_sa'];

        $data['billing_address'] = $addressData['b'];
        $data['shipping_address'] = $addressData['s'];

		return $data;
	}

	protected function prepareTable($table) {
		$table->currency_exchange_rate 			= PhocacartUtils::replaceCommaWithPoint($table->currency_exchange_rate);

		if ($table->tracking_date_shipped === '0' || $table->tracking_date_shipped === '') {
			$table->tracking_date_shipped = '0000-00-00 00:00:00';
		}
	}

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item) {
            $item->tracking_link = null;
            if ($item->shipping_id) {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select('tracking_link')
                    ->from('#__phocacart_shipping_methods')
                    ->where('id = ' . $item->shipping_id);
                $db->setQuery($query);
                $item->tracking_link = $db->loadResult();
            }
        }

        return $item;
    }

    public function save($data) {
		$app	= Factory::getApplication();
		if (!Session::checkToken('request')) {
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

        $data['billing_address']['ba_sa'] = $data['shipping_address']['ba_sa'];

        $this->storeOrderAddress($data['billing_address'], 0, $data['id']);
        $this->storeOrderAddress($data['shipping_address'], 1, $data['id']);

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

        //$data['section_id'] = $data['section_id'] ?: 0;
        //$data['unit_id'] = $data['unit_id'] ?: 0;
        //$data['ticket_id'] = $data['ticket_id'] ?: 0;

        $data['section_id'] = isset($data['section_id']) ? $data['section_id'] : 0;
        if ($data['section_id'] == '') {$data['section_id'] = 0;}
        $data['unit_id'] = isset($data['unit_id']) ? $data['unit_id'] : 0;
        $data['ticket_id'] = isset($data['ticket_id']) ? $data['ticket_id'] : 0;

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		$table->modified  = Factory::getDate()->toSql();
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

			$notify 	= PhocacartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id']);// Notify user, notify others, emails send - will be decided in function
			$comment	= Text::_('COM_PHOCACART_ORDER_EDITED');

			// Store the history
			PhocacartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $comment);
		}




		$cache = Factory::getCache($this->option);
		$cache->clean();

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}


	public function storeOrderProducts($d) {
		$row = Table::getInstance('PhocacartOrderProducts', 'Table', array());


		$d['netto'] 			= PhocacartUtils::replaceCommaWithPoint($d['netto']);
		$d['tax'] 				= PhocacartUtils::replaceCommaWithPoint($d['tax']);
		$d['brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['brutto']);

		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}
	}

	public function storeOrderAttributes($d) {
		$row = Table::getInstance('PhocacartOrderAttributes', 'Table', array());

		$d['option_value'] = urlencode($d['option_value']);

		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}
	}

	public function storeOrderTotal($d) {
		$row = Table::getInstance('PhocacartOrderTotal', 'Table', array());


		$d['amount'] 			= PhocacartUtils::replaceCommaWithPoint($d['amount']);

		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}
	}

	public function storeOrderTaxRecapitulation($d) {
		$row = Table::getInstance('PhocacartOrderTaxRecapitulation', 'Table', array());

		$d['amount_netto'] 				= PhocacartUtils::replaceCommaWithPoint($d['amount_netto']);
		$d['amount_tax'] 				= PhocacartUtils::replaceCommaWithPoint($d['amount_tax']);
		$d['amount_brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['amount_brutto']);
		if (isset($d['amount_brutto_currency'])) {
            $d['amount_brutto_currency'] = PhocacartUtils::replaceCommaWithPoint($d['amount_brutto_currency']);
        }
		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}
	}

	public function storeOrderAddress($d, $type = 0, $orderId = 0): bool {
		$row = Table::getInstance('PhocacartOrderUsers', 'Table', array());

		// it can happen that shipping (delivery) address was not created yet
		if ($type == 1) {
			if (!isset($d['id']) || (isset($d['id']) && (int)$d['id'] < 1)) {
				if ((int)$orderId < 1) {
					return false;
				}

				$d['order_id'] = (int)$orderId;
				$d['type'] = 1;
			}
		}

		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if ($row->country == '') {
			$row->country = 0;
		}
		if ($row->region == '') {
			$row->region = 0;
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}

		return true;
	}

	public function storeOrderProductDiscounts($d) {
		$row = Table::getInstance('PhocacartOrderProductDiscounts', 'Table', array());

		$d['netto'] 			= PhocacartUtils::replaceCommaWithPoint($d['netto']);
		$d['tax'] 			= PhocacartUtils::replaceCommaWithPoint($d['tax']);
		$d['brutto'] 			= PhocacartUtils::replaceCommaWithPoint($d['brutto']);

		if (!$row->bind($d)) {
			throw new Exception($row->getError());
		}

		if (!$row->check()) {
			throw new Exception($row->getError());
		}

		if (!$row->store()) {
			throw new Exception($row->getError());
		}
	}

	function delete(&$cid = array()) {
		if (count($cid)) {
			ArrayHelper::toInteger($cid);
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
