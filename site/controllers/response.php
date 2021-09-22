<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.log.log');
JLog::addLogger( array('text_file' => 'com_phocacart_error_log.php'), JLog::ALL, array('com_phocacart'));

class PhocaCartControllerResponse extends JControllerForm
{

	// User gets info
	public function paymentrecieve() {

		$app		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$session->set('proceedpayment', array(), 'phocaCart');

		//JSession::checkToken() or jexit( 'Invalid Token' );
		$return = PhocacartRoute::getInfoRoute();
		//$app->enqueueMessage(JText::_('COM_PHOCACART_PAYMENT_RECEIVED'), 'message');
		//$session->set('infoaction', 3, 'phocaCart');
		//$session->set('infoaction', 4, 'phocaCart');
		// NO message here, we have set the message during order and it stays unchanged as it is in session
		// the message will be deleted after it will be displayed in view

		$type 		= $app->input->get('type', '', 'string');
		$mid 		= $app->input->get('mid', 0, 'int'); // message id - possible different message IDs

		$message	= array();
		//$dispatcher = J EventDispatcher::getInstance();
		$plugin 	= JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($type)));
		if ($plugin) {
			$eventData 					= array();
            $eventData['pluginname'] 	= htmlspecialchars(strip_tags($type));
			\JFactory::getApplication()->triggerEvent('PCPafterRecievePayment', array($mid, &$message, $eventData));
		}

		if (!empty($message)) {
			$session->set('infomessage', $message, 'phocaCart');
		}
		$app->redirect($return);
	}

	// User gets info
	public function paymentcancel() {

		$app		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$session->set('proceedpayment', array(), 'phocaCart');
		//JSession::checkToken() or jexit( 'Invalid Token' );

		$type 		= $app->input->get('type', '', 'string');
		$mid 		= $app->input->get('mid', 0, 'int'); // message id - possible different message IDs
		$message	= array();
		//$dispatcher = J EventDispatcher::getInstance();
		$plugin 	= JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($type)));
		if ($plugin) {
			$eventData 					= array();
            $eventData['pluginname'] 	= htmlspecialchars(strip_tags($type));
			\JFactory::getApplication()->triggerEvent('PCPafterCancelPayment', array($mid, &$message, $eventData));
		}

		$return = PhocacartRoute::getInfoRoute();
		$session->set('infoaction', 5, 'phocaCart');
		$session->set('infomessage', $message, 'phocaCart');
		//$app->enqueueMessage(JText::_('COM_PHOCACART_PAYMENT_CANCELED'), 'info');
		$app->redirect($return);
	}


	// Robot gets info
	public function paymentnotify() {

		$app 	= JFactory::getApplication();
		$type 	= $app->input->get('type', '', 'string');
		$pid 	= $app->input->get('pid', 0, 'int'); // payment id
		$uri	= \Joomla\CMS\Uri\Uri::getInstance();

		//$dispatcher = J EventDispatcher::getInstance();
		$plugin = JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($type)));
		if ($plugin) {
			$eventData 					= array();
            $eventData['pluginname'] 	= htmlspecialchars(strip_tags($type));
			\JFactory::getApplication()->triggerEvent('PCPbeforeCheckPayment', array($pid, $eventData));
		} else {

			JLog::add('Payment method: '."Invalid HTTP request method. Type: " . $type . " Uri: " . $uri->toString(), 'com_phocacart');
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
		}

		exit;
	}


	public function paymentwebhook() {

		$app 	= JFactory::getApplication();
		$type 	= $app->input->get('type', '', 'string');
		$pid 	= $app->input->get('pid', 0, 'int'); // payment id
		$uri	= \Joomla\CMS\Uri\Uri::getInstance();

		//$dispatcher = J EventDispatcher::getInstance();
		$plugin = JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($type)));
		if ($plugin) {
			$eventData 					= array();
            $eventData['pluginname'] 	= htmlspecialchars(strip_tags($type));
			\JFactory::getApplication()->triggerEvent('PCPonPaymentWebhook', array($pid, $eventData));
		} else {

			JLog::add('Payment method: '."Invalid HTTP request method. Type: " . $type . " Uri: " . $uri->toString(), 'com_phocacart');
			header('Allow: POST', true, 405);
			throw new Exception("Invalid HTTP request method.");
		}
		exit;
	}

}
?>
