<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

jimport('joomla.application.component.model');

class PhocaCartModelCheckout extends FormModel
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
		return Table::getInstance($type, $prefix, $config);
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
		$formData = (array) Factory::getApplication()->getUserState('com_phocacart.checkout.data', array());

		if (empty($data)) {
			$formData = $this->getItem();
		}

		return $formData;
	}

	public function getItem($pk = null) {
		$app	= Factory::getApplication();
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
		$item = ArrayHelper::toObject($properties, CMSObject::class);

		$propertiesS = $tableS->getProperties(1);
		//$itemS = JArrayHelper::toObject($propertiesS, 'stdClass');

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
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}*/

		return $item;
	}

	public function getData() {
		return PhocacartUser::getUserData();
	}

	public function saveAddress($data, $type = 0) {

		$app		= Factory::getApplication();
		$user 		= PhocacartUser::getUser();
		$typeView 	= $app->input->get('typeview', '');


		if ((int)$user->id < 1) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}


		// Email cannot be changed in checkout or in user account (form address), only in user account profile
        if (isset($data['email'])) {
			if (isset($user->email) && $user->email != '') {
				$data['email'] = $user->email;
			} else {

				//unset($data['email']);
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_EMAIL_NOT_EXISTS'), 'error');
				return false;
			}
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
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');


		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Event user e.g. check valid VAT and store information about it
		$pluginLayout 	= PluginHelper::importPlugin('pct');
		if ($pluginLayout) {

			if ($typeView == 'account') {
				// Account
				Dispatcher::dispatch(new Event\Tax\UserAddressBeforeSaveAccount('com_phocacart.account',$row));
			} else {
				// Checkout
				Dispatcher::dispatch(new Event\Tax\UserAddressBeforeSaveCheckout('com_phocacart.checkout',$row));
			}

		}

		// Store the table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}



		return $row->id;
	}

	public function saveShipping($shippingId, $shippingParams = array()) {

		$app	= Factory::getApplication();
		$user 	= PhocacartUser::getUser();

		if ((int)$user->id < 1) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}

		$data['shipping']	= (int)$shippingId;
		$data['user_id']	= (int)$user->id;

		$shipping 			= new PhocacartShipping();
		//$shipping->setType();
		$isValidShipping	= $shipping->checkAndGetShippingMethod($shippingId);
		if (!$isValidShipping) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_SHIPPING_METHOD_NOT_AVAILABLE'), 'error');
			return false;
		}

		$row = $this->getTable('PhocacartCart', 'Table');

		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				// No data yet
			}
		}

		if (empty($row->cart)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_SHIPPING_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}


		// Store information from Shipping method e.g. info about Branch (but test all the params)
		$data['params_shipping'] = '';
		if (!empty($shippingParams)){

			if (isset($isValidShipping[0]->method) && $isValidShipping[0]->method != ''){
				Dispatcher::dispatch(new Event\Shipping\CheckShippingBranchFormFields('com_phocacart.checkout', $shippingParams, $isValidShipping[0], [
					'pluginname' => $isValidShipping[0]->method,
				]));
				$data['params_shipping']	= json_encode($shippingParams);
			}
		}



		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return $row->user_id;

	}

	public function savePaymentAndCouponAndReward($paymentId, $couponId, $reward, $paymentParams = []) {
		$app	= Factory::getApplication();
		$user 	= PhocacartUser::getUser();
		if ((int)$user->id < 1) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}

		$data['user_id']	= (int)$user->id;
		$data['payment'] 	= (int)$paymentId;

		if ((int)$couponId === -1) {
			// Coupon was not sent in form, only payment, Don't change the coupon
		} else {
			$data['coupon'] 	= (int)$couponId;
		}

		if ((int)$reward === -1) {
			// Reward points was not sent in form, only payment, Don't change the reward points
		} else {
			$data['reward'] 	= (int)$reward;
		}


		$payment 			= new PhocacartPayment();
		//$payment->setType();
		$isValidPayment		= $payment->checkAndGetPaymentMethod($paymentId);
		if (!$isValidPayment) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PAYMENT_METHOD_NOT_AVAILABLE'), 'error');
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
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_PAYMENT_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}

		// Store information from Payment method e.g. info about Branch (but test all the params)
		$data['params_payment'] = '';
		if (!empty($paymentParams)){

			if (isset($isValidPayment[0]->method) && $isValidPayment[0]->method != ''){
				Dispatcher::dispatch(new Event\Payment\CheckPaymentBranchFormFields('com_phocacart.checkout', $paymentParams, $isValidPayment[0], [
					'pluginname' => $isValidPayment[0]->method,
				]));
				$data['params_payment']	= json_encode($paymentParams);
			}
		}



		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}


		// Store the table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return $row->user_id;

	}

	public function saveCoupon($couponId) {
		$app	= Factory::getApplication();
		$user 	= PhocacartUser::getUser();
		if ((int)$user->id < 1) {
			// This should not happen as the user is controlled in controller (because of different return messages for standard checkout or guest checkout
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}


		$data['coupon'] 	= (int)$couponId;
		$data['user_id']	= (int)$user->id;

		// Coupon has own rules in cart
		$row = $this->getTable('PhocacartCart', 'Table');

		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				// No data yet
			}
		}

		// Possible feature request ceck for if cart is empty
		/*if (empty($row->cart)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_PAYMENT_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}*/

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}


		// Store the table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		return $row->user_id;

	}

	public function saveRewardPoints($reward) {
		$app	= Factory::getApplication();
		$user 	= PhocacartUser::getUser();
		if ((int)$user->id < 1) {
			// This should not happen as the user is controlled in controller (because of different return messages for standard checkout or guest checkout
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_USER_NOT_LOGGED_IN'), 'error');
			return false;
		}


		$data['user_id']	= (int)$user->id;
		$data['reward'] 	= (int)$reward;

		// Reward points have own rules in cart
		$row = $this->getTable('PhocacartCart', 'Table');

		if(isset($user->id) && $user->id > 0) {
			if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				// No data yet
			}
		}

		// Possible feature request ceck for if cart is empty
		/*if (empty($row->cart)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_PAYMENT_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}*/

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}


		// Store the table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
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
		$options['control'] = ArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		Form::addFormPath(JPATH_COMPONENT . '/models/forms');
		Form::addFieldPath(JPATH_COMPONENT . '/models/fields');
		Form::addFormPath(JPATH_COMPONENT . '/model/form');
		Form::addFieldPath(JPATH_COMPONENT . '/model/field');

		try
		{
			$form = Form::getInstance($name, $source, $options, false, $xpath);

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
		$formData = (array) Factory::getApplication()->getUserState('com_phocacart.checkout.data', array());

		if (empty($formData)) {
			$formData = $this->getItemGuest();
		}

		if ($formData === false) {
			// $this->preprocessForm needs array or object not bool
			$formData = array();
		}

		return $formData;
	}

	public function getItemGuest($pk = null) {

		//$guest 	= new PhocacartUserGuestuser();
		//$item	= $guest->getAddress();
		$item = PhocacartUserGuestuser::getAddress();

		return $item;
	}

	public function saveAddressGuest($data) {
		$data['user_id']	= 0;
		$data['type']		= 0;

		if (!isset($data['params_user'])) {
			$data['params_user'] = '';
		}

		// Event user e.g. check valid VAT and store information about it
		Dispatcher::dispatch(new Event\Tax\GuestUserAddressBeforeSaveCheckout('com_phocacart.checkout', $data));

		if (PhocacartUserGuestuser::storeAddress($data)) {
			return true;
		} else {
			return false;
		}

	}

	public function getDataGuest() {
		$data	= PhocacartUserGuestuser::getAddress();
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

	public function saveShippingGuest($shippingId, $shippingParams = array()) {

		$app = Factory::getApplication();
		$shipping 			= new PhocacartShipping();
		$isValidShipping	= $shipping->checkAndGetShippingMethod($shippingId);

		if (!$isValidShipping) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_SHIPPING_METHOD_NOT_AVAILABLE'), 'error');
			return false;
		}

		if (PhocacartUserGuestuser::storeShipping((int)$shippingId)) {
			// Store information from Shipping method e.g. info about Branch (but test all the params)
			$dataShippingParams = '';
			if (!empty($shippingParams)){

				if (isset($isValidShipping[0]->method) && $isValidShipping[0]->method != ''){
					Dispatcher::dispatch(new Event\Shipping\CheckShippingBranchFormFields('com_phocacart.checkout',$shippingParams, $isValidShipping[0], [
						'pluginname' => $isValidShipping[0]->method,
					]));

					$dataShippingParams	= json_encode($shippingParams);
				}
			}

			PhocacartUserGuestuser::storeShippingParams($dataShippingParams);

			return true;
		}
		return false;
	}

	public function savePaymentAndCouponGuest($paymentId, $couponId, $paymentParams = []) {

		$app = Factory::getApplication();
		$payment 			= new PhocacartPayment();
		//$payment->setType();
		$isValidPayment		= $payment->checkAndGetPaymentMethod($paymentId);
		if (!$isValidPayment) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PAYMENT_METHOD_NOT_AVAILABLE'), 'error');
			return false;
		}

		if ($couponId === -1) {
			// we ignore storing the coupon (it is not a part of payment form)
			if (PhocacartUserGuestuser::storePayment((int)$paymentId)) {
				return true;
			}
		} else {
			if (PhocacartUserGuestuser::storePayment((int)$paymentId) &&  PhocacartUserGuestuser::storeCoupon((int)$couponId)) {

			// Store information from Payment method e.g. info about Branch (but test all the params)
			$dataPaymentParams = '';
			if (!empty($paymentParams)){

				if (isset($isValidPayment[0]->method) && $isValidPayment[0]->method != ''){
					Dispatcher::dispatch(new Event\Payment\CheckPaymentBranchFormFields('com_phocacart.checkout',$paymentParams, $isValidPayment[0], [
						'pluginname' => $isValidPayment[0]->method,
					]));

					$dataPaymentParams	= json_encode($paymentParams);
				}
			}

			PhocacartUserGuestuser::storePaymentParams($dataPaymentParams);


				return true;
			}
		}

		return false;
	}

	public function saveCouponGuest($couponId) {

		if (PhocacartUserGuestuser::storeCoupon((int)$couponId)) {
			return true;
		}
		return false;
	}


}
?>
