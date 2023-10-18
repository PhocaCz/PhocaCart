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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

use Joomla\CMS\Uri\Uri;

class PhocaCartControllerPos extends FormController
{

	public function addticket() {

		Session::checkToken() or jexit( 'Invalid Token' );
		$app 				= Factory::getApplication();
		$session 			= Factory::getSession();
		$item				= array();
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['unitid']		= $this->input->get( 'unitid', 0, 'int'  );
		$item['sectionid']	= $this->input->get( 'sectionid', 0, 'int'  );
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (isset($vendor->id) && (int)$vendor->id > 0) {
			$lastTicket = PhocacartTicket::getLastVendorTicket((int)$vendor->id, (int)$item['unitid'], (int)$item['sectionid']);

			if (!isset($lastTicket) || (isset($lastTicket)&& (int)$lastTicket == 0)) {
				// Create the default ticket: 1
				$added = PhocaCartTicket::addNewVendorTicket((int)$vendor->id, 1, (int)$item['unitid'], (int)$item['sectionid']);
				if ($added) {
					$lastTicket = 1;

				}
			}
			if (isset($lastTicket) && (int)$lastTicket > 0) {
				$ticket = $lastTicket + 1;

				$added = PhocaCartTicket::addNewVendorTicket((int)$vendor->id, (int)$ticket, (int)$item['unitid'], (int)$item['sectionid']);
				if ($added) {
					$url = base64_decode($item['return']);
					$uri = Uri::getInstance(base64_decode($item['return']));
					//$oldTicketId = $uri->getVar('ticketid');
					$uri->setVar('ticketid', $ticket);
					$app->redirect($uri->toString());
					return true;
				}
			}
		}
		$app->redirect(base64_decode($item['return']));
	}

	public function removeticket() {

		Session::checkToken() or jexit( 'Invalid Token' );
		$app 				= Factory::getApplication();
		$session 			= Factory::getSession();
		$item				= array();
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['ticketid']	= $this->input->get( 'ticketid', 0, 'int'  );
		$item['unitid']		= $this->input->get( 'unitid', 0, 'int'  );
		$item['sectionid']	= $this->input->get( 'sectionid', 0, 'int'  );
		$user				= $vendor = $ticket = $unit	= $section = array();
		$dUser				= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section, 1);

		if (isset($vendor->id) && (int)$vendor->id > 0) {

			//if (isset($lastTicket) && (int)$lastTicket > 0) {

				$removed = PhocaCartTicket::removeVendorTicket((int)$vendor->id, (int)$item['ticketid'], (int)$item['unitid'], (int)$item['sectionid']);
				if ($removed) {
					$url = base64_decode($item['return']);
					$uri = Uri::getInstance(base64_decode($item['return']));
					//$oldTicketId = $uri->getVar('ticketid');
					$uri->setVar('ticketid', 1);
					$app->redirect($uri->toString());
					return true;
				}
			//}
		}
		$app->redirect(base64_decode($item['return']));

	}

	/*
	 * Add product to cart
	 * see pos.json
	 */
	public function add() {

		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['attribute']	= $this->input->get( 'attribute', array(), 'array'  );

		/*
		$cart	= new PhocacartCart();

		$added	= $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);

		if ($added) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART'), 'message');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));*/

		$app->redirect(base64_decode($item['return']));
	}


}
?>
