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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;

class PhocaCartControllerWishList extends FormController
{

	public function add() {

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}


		$app					= Factory::getApplication();
		$s 					    = PhocacartRenderStyle::getStyles();
		$item					= array();
		$item['id']				= $this->input->get( 'id', 0, 'int' );
		$item['catid']			= $this->input->get( 'catid', 0, 'int' );
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['wishlistview']	= $this->input->get( 'wishlistview', 0, 'int'  );

		$wishlist	= new PhocacartWishlist();
		$added		= $wishlist->addItem((int)$item['id'], (int)$item['catid']);
		//$catid		= PhocacartProduct::getCategoryByProductId((int)$item['id']);

		$o = $o2 = '';
		// Content of the wishlist list
		ob_start();
		echo $wishlist->renderList();
		$o = ob_get_contents();
		ob_end_clean();

		// Render the layout
		$d          = array();
		$d['s']	    = $s;
		$layoutW	= new FileLayout('popup_add_to_wishlist', null, array('component' => 'com_phocacart'));

		$d['link_wishlist'] = Route::_(PhocacartRoute::getWishListRoute((int)$item['id'], (int)$item['catid']));
		$d['link_continue'] = '';
		// We need to know if module is displayed on wishlist site
		// If yes and one item will be deleted per AJAX, we need to refresh wishlist site
		// If now and one item will be deleted per AJAX, everything is OK, nothing needs to be refreshed
		$d['wishlist_view'] 	= (int)$item['wishlistview'];

		if ($added) {
			$d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_WISH_LIST');
		} else {
			$d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_WISH_LIST');

			$mO = PhocacartRenderFront::renderMessageQueue();
			$d['info_msg_additional'] = $mO;
		}

		// Popup with info - Continue,Proceed to wishlist list
		//ob_start();
		$o2 = $layoutW->render($d);
		//$o2 = ob_get_contents();
		//ob_end_clean();

		$count = $wishlist->getWishListCountItems();

		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'popup'		=> $o2,
			'count'		=> $count);

		echo json_encode($response);
		return;
	}

	public function remove() {

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app 					= Factory::getApplication();
		$s 					    = PhocacartRenderStyle::getStyles();
		$item					= array();
		$item['id']				= $this->input->get( 'id', 0, 'int' );
		$item['catid']			= $this->input->get( 'catid', 0, 'int' );
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['wishlistview']	= $this->input->get( 'wishlistview', 0, 'int'  );

		$wishlist	= new PhocacartWishlist();
		$added		= $wishlist->removeItem((int)$item['id']);
		//$catid		= PhocacartProduct::getCategoryByProductId((int)$item['id']);

		$o = $o2 = '';
		// Content of the wishlist list
		ob_start();
		echo $wishlist->renderList();
		$o = ob_get_contents();
		ob_end_clean();

		// Render the layout
		$d          = array();
		$d['s']	    = $s;
		$layoutW	= new FileLayout('popup_remove_from_wishlist', null, array('component' => 'com_phocacart'));

		$d['link_wishlist'] = Route::_(PhocacartRoute::getWishListRoute((int)$item['id'], (int)$item['catid']));
		$d['link_continue'] = '';
		// We need to know if module is displayed on wishlist site
		// If yes and one item will be deleted per AJAX, we need to refresh wishlist site
		// If now and one item will be deleted per AJAX, everything is OK, nothing needs to be refreshed
		$d['wishlist_view'] 	= (int)$item['wishlistview'];



		if ($added) {
			$d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_WISH_LIST');
		} else {
			$d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_WISH_LIST');

			$mO = PhocacartRenderFront::renderMessageQueue();
			$d['info_msg_additional'] = $mO;
		}

		// Popup with info - Continue,Proceed to wishlist list
		//ob_start();
		$o2 = $layoutW->render($d);
		//$o2 = ob_get_contents();
		//ob_end_clean();

		$count = $wishlist->getWishListCountItems();

		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'popup'		=> $o2,
			'count'		=> $count);

		echo json_encode($response);
		return;
	}

}
?>
