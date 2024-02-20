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
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Exception\WatchDogException;
use Phoca\PhocaCart\Tools\WatchDog;

class PhocaCartControllerWishList extends FormController
{

	public function add() {

		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );

		$wishlist	= new PhocacartWishlist();
		$added		= $wishlist->addItem((int)$item['id'], (int)$item['catid']);
		if ($added) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_WISH_LIST'), 'message');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_WISH_LIST'), 'error');
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}

		public function remove() {

		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );

		$wishlist	= new PhocacartWishlist();
		$added	= $wishlist->removeItem((int)$item['id']);
		if ($added) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_WISH_LIST'), 'message');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_WISH_LIST'), 'error');
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}

	public function setwatchdog()
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $params = PhocacartUtils::getComponentParameters();

        $id = $this->input->get('id', 0, 'int');
        $catid = $this->input->get( 'catid', 0, 'int' );
        $return = $this->input->get('return', '', 'string');

        if ($return) {
            $return = base64_decode($return);
        } else {
            $return = Route::_(PhocacartRoute::getItemRoute($id, $catid));
        }

        $return = Route::_($return);

        if (!$params->get('watchdog_enable', 0)) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_WATCHDOG_NOT_ENABLED'), 'error');
            $this->setRedirect($return);
            $this->redirect();
            return;
        }

        if (!$user || $user->guest) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_WATCHDOG_LOGIN'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode($return), false));
            $this->redirect();
            return;
        }

        $product = PhocacartProduct::getProductByProductId($id);
        if (!$product) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_WATCHDOG_PRODUCT'), 'error');
            $this->setRedirect(Route::_('inex.php?option=com_phocacart'));
            $this->redirect();
            return;
        }

        if (WatchDog::has($product->id)) {
            $app->enqueueMessage(Text::sprintf('COM_PHOCACART_ERROR_WATCHDOG_ALREADY_SET', $product->title), 'warning');
            $this->setRedirect($return);
            $this->redirect();
            return;
        }

        if (WatchDog::count() >= $params->get('watchdog_limit', 20)) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_WATCHDOG_LIMIT'), 'error');
            $this->setRedirect($return);
            $this->redirect();
            return;
        }

        try {
            WatchDog::set($product->id, $catid ?: $product->catid);
            $app->enqueueMessage(Text::sprintf('COM_PHOCACART_WATCHDOG_SET', $product->title), 'success');
        } catch (WatchDogException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect($return);
        $this->redirect();
    }
}

