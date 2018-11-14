<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartModelCheckout extends JModelForm
{
	//protected $data;
	protected $fields;
	protected $fieldsguest;

	public function getFields($billing = 1, $shipping = 1, $account = 0){
		if (empty($this->fields)) {
			$this->fields = PhocacartFormUser::getFormXml('', '_phs', $billing, $shipping, $account);//Fields in XML Format
		}
		return $this->fields;
	}
	
	public function getTable($type = 'PhocacartUser', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		
		if (empty($this->fields['xml'])) {
			$this->fields = $this->getFields();
		}
		$form = $this->loadForm('com_phocacart.checkout', (string)$this->fields['xml'], array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) {
			return false;
		}
		
		return $form;
	}
	
	protected function loadFormData() {
		$formData = (array) JFactory::getApplication()->getUserState('com_phocacart.checkout.data', array());
		
		if (empty($data)) {
			$formData = $this->getItem();
		}
		
		return $formData;
	}
	
	public function getItem($pk = null) {
		$app	= JFactory::getApplication();
		$user 	= PhocacartUser::getUser();
		$table 	= $this->getTable('PhocacartUser', 'Table');
		$tableS 	= $this->getTable('PhocacartUser', 'Table');
		
		// Billing
		if(isset($user->id) && (int)$user->id > 0) {
			$return = $table->load(array('user_id' => (int)$user->id, 'type' => 0));
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Shipping
		if(isset($user->id) && (int)$user->id > 0) {
			$returnS = $tableS->load(array('user_id' => (int)$user->id, 'type' => 1));
			if ($returnS === false && $tableS->getError()) {
				$this->setError($tableS->getError());
				return false;
			}
		}
		
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = \Joomla\Utilities\ArrayHelper::toObject($properties, 'JObject');
		
		$propertiesS = $tableS->getProperties(1);
		//$itemS = \Joomla\Utilities\ArrayHelper::toObject($propertiesS, 'JObject');
		
		//Add shipping data to billing and do both data package
		if(!empty($propertiesS) && is_object($item)) {
			foreach($propertiesS as $k => $v) {
				$newName = $k . '_phs';
				$item->$newName = $v;
			
			}
		
		}
		/*

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}*/

		return $item;
	}
	
	public function getData() {
		return PhocacartUser::getUserData();
	}
	
	public function saveAddress($data, $type = 0) {
		
		$app	= JFactory::getApplication();
		$user 	= PhocacartUser::getUser();
		
		
		if ((int)$user->id < 1) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}
		$data['user_id']	= (int)$user->id;
		$data['type']		= (int)$type;
		$row = $this->getTable('PhocacartUser', 'Table');

		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'type' => $type))) {
				// No data yet
			}
		}
		//$row->bind($data);

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');
		
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $row->id;
	}
	
	public function saveShipping($shippingId) {
		
		$app	= JFactory::getApplication();
		$user 	= PhocacartUser::getUser();
		
		if ((int)$user->id < 1) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}
		
		$data['shipping']	= (int)$shippingId;
		$data['user_id']	= (int)$user->id;
		
		$shipping 			= new PhocacartShipping();
		//$shipping->setType();
		$isValidShipping	= $shipping->checkAndGetShippingMethod($shippingId);
		if (!$isValidShipping) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_SHIPPING_METHOD_NOT_AVAILABLE'), 'error');
			return false;
		}
		
		$row = $this->getTable('PhocacartCart', 'Table');

		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				// No data yet
			}
		}
		
		if (empty($row->cart)) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_SHIPPING_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}
		
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		return $row->user_id;
	
	}
	
	public function savePaymentAndCouponAndReward($paymentId, $couponId, $reward) {
		$app	= JFactory::getApplication();
		$user 	= PhocacartUser::getUser();
		if ((int)$user->id < 1) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}
		
		$data['payment'] 	= (int)$paymentId;
		$data['coupon'] 	= (int)$couponId;
		$data['user_id']	= (int)$user->id;
		$data['reward'] 	= (int)$reward;
		
		$payment 			= new PhocacartPayment();
		//$payment->setType();
		$isValidPayment		= $payment->checkAndGetPaymentMethod($paymentId);
		if (!$isValidPayment) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PAYMENT_METHOD_NOT_AVAILABLE'), 'error');
			return false;
		}
		
		// Coupon has own rules in cart
		// Reward points have own rules in cart
		
		
		$row = $this->getTable('PhocacartCart', 'Table');
		
		
		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				// No data yet
			}
		}
		
		if (empty($row->cart)) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_PAYMENT_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}
		
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');
		
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		
		// Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		return $row->user_id;
	
	}
	
	/* 
	 *
	 * GUEST CHECKOUT
	 *
	 */
	
	public function getFieldsGuest(){
		if (empty($this->fieldsguest)) {
			$this->fieldsguest = PhocacartFormUser::getFormXml('', '_phs', 1, 1, 0, 1);//Fields in XML Format
		}
		return $this->fieldsguest;
	}
	
	public function getFormGuest($data = array(), $loadData = true) {
		
		if (empty($this->fieldsguest['xml'])) {
			$this->fieldsguest = $this->getFieldsGuest();
		}
		$form = $this->loadFormGuest('com_phocacart.checkout', (string)$this->fieldsguest['xml'], array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		
		return $form;
	}
	
	protected function loadFormGuest($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = \Joomla\Utilities\ArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		try
		{
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormDataGuest();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}
	
	protected function loadFormDataGuest() {
		$formData = (array) JFactory::getApplication()->getUserState('com_phocacart.checkout.data', array());
		
		if (empty($data)) {
			$formData = $this->getItemGuest();
		}
		
		return $formData;
	}
	
	public function getItemGuest($pk = null) {
		
		$guest 	= new PhocacartUserGuestuser();
		$item	= $guest->getAddress();

		return $item;
	}
	
	public function saveAddressGuest($data) {
		$guest	= new PhocacartUserGuestuser();
		$data['user_id']	= 0;
		$data['type']		= 0;
		if ($guest->storeAddress($data)) {
			return true;
		} else {
			return false;
		}

	}
	
	public function getDataGuest() {
		
		$guest	= new PhocacartUserGuestuser();
		$data	= $guest->getAddress();
		if (!empty($data)) {
			
			
			$dataN = PhocacartUser::convertAddressTwo($data, 0);
			
			$dataN[0]->countrytitle = null;
			$dataN[0]->regiontitle = null;
			$dataN[1]->countrytitle = null;
			$dataN[1]->regiontitle = null;
			if (isset($dataN[0]->country) && $dataN[0]->country > 0) {
				$dataN[0]->countrytitle = PhocacartCountry::getCountryById($dataN[0]->country);
			}
			if (isset($dataN[0]->region) && $dataN[0]->region > 0) {
				$dataN[0]->regiontitle = PhocacartRegion::getRegionById($dataN[0]->region);
			}
			if (isset($dataN[1]->country) && $dataN[1]->country > 0 ) {
				if (isset($dataN[0]->country) && $dataN[0]->country == $dataN[1]->country) {
					$dataN[1]->countrytitle = $dataN[0]->countrytitle;//great to save one sql query
					
				} else {
					$dataN[1]->countrytitle = PhocacartCountry::getCountryById($dataN[1]->country);
				}
			}
			if (isset($dataN[1]->region) && $dataN[1]->region > 0 ) {
				if (isset($dataN[0]->region) && $dataN[0]->region == $dataN[1]->region) {
					$dataN[1]->regiontitle = $dataN[0]->regiontitle;//great to save one sql query
				} else {
					$dataN[1]->regiontitle = PhocacartRegion::getRegionById($dataN[1]->region);
				}
			}

			return $dataN;
		}
		return false;
	}
	
	public function saveShippingGuest($shippingId) {
		
		if (PhocacartUserGuestuser::storeShipping((int)$shippingId)) {
			return true;
		}
		return false;
	}

	public function savePaymentAndCouponGuest($paymentId, $couponId) {
		
		
		if (PhocacartUserGuestuser::storePayment((int)$paymentId) &&  PhocacartUserGuestuser::storeCoupon((int)$couponId)) {
			return true;
		}
		return false;
	}	
	
	
}
?>
