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
		//$app->enqueueMessage(JText::_('COM_PHOCACART_PAYMENT_RECIEVED'), 'message');
		//$session->set('infomessage', 3, 'phocaCart');
		//$session->set('infomessage', 4, 'phocaCart');
		// NO message here, we have set the message during order and it stays unchanged as it is in session
		// the message will be deleted after it will be displayed in view
		$app->redirect($return);
	}
	
	// User gets info
	public function paymentcancel() {
		
		$app		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$session->set('proceedpayment', array(), 'phocaCart');
		//JSession::checkToken() or jexit( 'Invalid Token' );
		$return = PhocacartRoute::getInfoRoute();
		$session->set('infomessage', 5, 'phocaCart');
		//$app->enqueueMessage(JText::_('COM_PHOCACART_PAYMENT_CANCELED'), 'info');
		$app->redirect($return);
	}
	
	
	// Robot gets info
	public function paymentnotify() {
	
		
		$app 	= JFactory::getApplication();
		$type 	= $app->input->get('type', '', 'string');
		$pid 	= $app->input->get('pid', 0, 'int'); // payment id
		$uri	= JFactory::getUri();
		
		$dispatcher = JEventDispatcher::getInstance();
		$plugin = JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($type)));
		if ($plugin) {
			$dispatcher->trigger('PCPbeforeCheckPayment', array($pid));
		} else {
			
			JLog::add('Paypal Standard: '."Invalid HTTP request method. Type: " . $type . " Uri: " . $uri->toString(), 'com_phocacart');
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
		}
				
		exit;	
	}
	
}
?>