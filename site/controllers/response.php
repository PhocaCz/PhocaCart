<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

jimport('joomla.log.log');

Log::addLogger( array('text_file' => 'com_phocacart_error_log.php'), Log::ALL, array('com_phocacart'));

class PhocaCartControllerResponse extends FormController
{

	// User gets info
	public function paymentrecieve() {

		$app		= Factory::getApplication();
		$session 	= Factory::getSession();
		$session->set('proceedpayment', array(), 'phocaCart');

		//JSession::checkToken() or jexit( 'Invalid Token' );
		$return = PhocacartRoute::getInfoRoute();
		//$app->enqueueMessage(Text::_('COM_PHOCACART_PAYMENT_RECEIVED'), 'message');
		//$session->set('infoaction', 3, 'phocaCart');
		//$session->set('infoaction', 4, 'phocaCart');
		// NO message here, we have set the message during order and it stays unchanged as it is in session
		// the message will be deleted after it will be displayed in view

		$type = $app->input->getCmd('type');
		$mid = $app->input->getInt('mid', 0); // message id - possible different message IDs

		$message = [];
		Dispatcher::dispatch(new Event\Payment\AfterRecievePayment($mid, $message, [
			'pluginname' => $type,
		]));

		if (!empty($message)) {
			$session->set('infomessage', $message, 'phocaCart');
		}
		$app->redirect(Route::_($return));
	}

	// User gets info
	public function paymentcancel() {

		$app		= Factory::getApplication();
		$session 	= Factory::getSession();
		$session->set('proceedpayment', array(), 'phocaCart');
		//JSession::checkToken() or jexit( 'Invalid Token' );

		$type = $app->input->getCmd('type');
		$mid = $app->input->getInt('mid', 0); // message id - possible different message IDs

		$message	= [];
		Dispatcher::dispatch(new Event\Payment\AfterCancelPayment($mid,$message, [
			'pluginname' => $type,
		]));

		$return = PhocacartRoute::getInfoRoute();

		$session->set('infoaction', 5, 'phocaCart');
		$session->set('infomessage', $message, 'phocaCart');
		//$app->enqueueMessage(Text::_('COM_PHOCACART_PAYMENT_CANCELED'), 'info');
		$app->redirect(Route::_($return));
	}


	// Robot gets info
	public function paymentnotify()
	{
		$app 	= Factory::getApplication();
		$type = $app->input->getCmd('type');
		$pid = $app->input->getInt('pid', 0); // payment id

		$plugin = PluginHelper::importPlugin('pcp', $type);
		if ($plugin) {
			Dispatcher::dispatch(new Event\Payment\BeforeCheckPayment($pid, [
				'pluginname' => $type,
			]));
		} else {
			$uri	= Uri::getInstance();
			Log::add('Payment method: '."Invalid HTTP request method. Type: " . $type . " Uri: " . $uri->toString(), 'com_phocacart');
			header('Allow: POST', true, 405);
      throw new Exception("Invalid HTTP request method.");
		}

		exit;
	}


	public function paymentwebhook()
	{
		$app 	= Factory::getApplication();
		$type = $app->input->getCmd('type');
		$pid 	= $app->input->getInt('pid', 0); // payment id

		$plugin = PluginHelper::importPlugin('pcp', $type);
		if ($plugin) {
			Dispatcher::dispatch(new Event\Payment\Webhook($pid, [
				'pluginname' => $type,
			]));
		} else {
			$uri	= Uri::getInstance();
			Log::add('Payment method: '."Invalid HTTP request method. Type: " . $type . " Uri: " . $uri->toString(), 'com_phocacart');
			header('Allow: POST', true, 405);
			throw new Exception("Invalid HTTP request method.");
		}

		exit;
	}

}

