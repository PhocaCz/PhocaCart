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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditStockAdvanced extends AdminModel
{
	protected	$option 		        = 'com_phocacart';
	protected 	$text_prefix	        = 'com_phocacart';
	public      $typeAlias 		        = 'com_phocacart.phocacartproductstock';



	public function getTable($type = 'PhocacartProductStock', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartproductstock', 'phocacartproductstock', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartproductstock.data', array());

		if (empty($data)) {
			$data = $this->getItem();

		}

		return $data;
	}


	public function getItem($pk = null) {

		$app					= Factory::getApplication();
		$productId				= $app->input->get('id', 0, 'int');



		// TO DO BE AWARE
		// Remove this part when this problem will be solved
		// https://github.com/joomla/joomla-cms/issues/35811


		// START TEMP CODE
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the \JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, CMSObject::class);


		if (property_exists($item, 'params'))
		{
			$registry = new Registry($item->params);
			$item->params = $registry->toArray();
		}
		// END TEMP CODE

		// FROM:
		//if ($item) {
		//TO:
		//if ($item = parent::getItem($pk)) {
		if ($item) {
			if ($productId > 0) {
				$product           = PhocacartProduct::getProduct((int)$productId);
				$attr_options      = PhocacartAttribute::getAttributesAndOptions((int)$productId);
				$combinations      = array();
				$combinations_data = array();


				if (!empty($product)) {
					PhocacartAttribute::getCombinations($product->id, $product->title, $attr_options, $combinations);
					// Load data from database
					$combinations_data = PhocacartAttribute::getCombinationsDataByProductId($product->id);

				}

				if (!empty($combinations)) {
					ksort($combinations);

					foreach ($combinations as $k => $v) {

						if (isset($combinations_data[$v['product_key']]['stock'])) {
							$combinations[$v['product_key']]['stock'] = $combinations_data[$v['product_key']]['stock'];
						}

						if (isset($combinations_data[$v['product_key']]['price'])) {
							$price                                    = $combinations_data[$v['product_key']]['price'];
							$combinations[$v['product_key']]['price'] = PhocacartPrice::cleanPrice($price);
						}

						if (isset($combinations_data[$v['product_key']]['sku'])) {
							$combinations[$v['product_key']]['sku'] = $combinations_data[$v['product_key']]['sku'];
						}

						if (isset($combinations_data[$v['product_key']]['ean'])) {
							$combinations[$v['product_key']]['ean'] = $combinations_data[$v['product_key']]['ean'];
						}

						if (isset($combinations_data[$v['product_key']]['image'])) {
							$combinations[$v['product_key']]['image'] = $combinations_data[$v['product_key']]['image'];
						}

						if (isset($v['product_key'])) {
							$combinations[$v['product_key']]['product_key'] = $v['product_key'];
						}
						if (isset($v['product_id'])) {
							$combinations[$v['product_key']]['product_id'] = $v['product_id'];
						}
						if (isset($v['product_id']) && isset($v['attributes'])) {
							$combinations[$v['product_key']]['attributes'] = PhocacartProduct::getProductKey($v['product_id'], $v['attributes'], 0);
						}


					}

					$item->set('product_stock', $combinations);

				}
			}
		}


		return $item;
	}

	protected function prepareTable($table) {
		jimport('joomla.filter.output');

		$table->price 					= PhocacartUtils::replaceCommaWithPoint($table->price);


	}


	public function save($data/*, $productId*/) {

		$app					= Factory::getApplication();
		$productId				= $app->input->get('id', 0, 'int');



		if (!empty($data['product_stock'])) {

			$notDeleteItems = array();

			foreach($data['product_stock'] as $k => $v) {
				$row = $this->getTable('PhocacartProductStock', 'Table');

				if(isset($v['product_key']) && $v['product_key'] != '') {
					if (!$row->load(array('product_key' => $v['product_key']))) {
						// No data yet
					}
				}

				$v['stock'] = PhocacartUtils::getIntFromString($v['stock']);
				$v['price'] = PhocacartUtils::getDecimalFromString($v['price']);

				if (!$row->bind($v)) {
					$this->setError($row->getError());
					return false;
				}

				if (!$row->check()) {
					$this->setError($row->getError());
					return false;
				}


				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}

				if (isset($row->id) && (int)$row->id > 0) {
					$notDeleteItems[] = (int)$row->id;
				}

			}

			if (!empty($notDeleteItems)) {
				$notDeleteItemsString = implode(',', $notDeleteItems);
				$query = ' DELETE '
						.' FROM #__phocacart_product_stock'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteItemsString.')';
			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_stock'
						.' WHERE product_id = '. (int)$productId;
			}

			$this->_db->setQuery($query);
			$this->_db->execute();

		}

		return true;
	}



	/*public function getData() {

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

			$status['select'] = HTMLHelper::_('select.genericlist',  $status['data'],  'jform[status_id]', 'class="form-control"', 'value', 'text', $item->status_id, 'jform_status_id' );
			return $status;
		}
		return array();

	}*/
	/*
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
	}*/




	/*
	public function editStatus($data) {

		$data['id']			= (int)$data['id'];
		$data['status_id']	= (int)$data['status_id'];
		$data['email_send']	= (int)$data['email_send'];
		$row 				= $this->getTable('PhocacartOrder', 'Table');
		$user 				= PhocacartUser::getUser();

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



		$notify = PhocacartOrderStatus::changeStatus((int)$data['id'], (int)$data['status_id'], '', $notifyUser, $notifyOther, (int)$data['email_send'], $data['stock_movements']);

		PhocacartOrderStatus::setHistory((int)$data['id'], (int)$data['status_id'], (int)$notify, $data['comment']);


		return $row->id;
	}*/
	/*
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
	}*/
}
?>
