<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartControllerWishList extends JControllerForm
{
	
	public function add() {
		
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		
		$app					= JFactory::getApplication();
		$item					= array();
		$item['id']				= $this->input->get( 'id', 0, 'int' );
		$item['catid']			= $this->input->get( 'catid', 0, 'int' );
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['wishlistview']	= $this->input->get( 'wishlistview', 0, 'int'  );
		
		$wishlist	= new PhocaCartWishList();
		$added		= $wishlist->addItem((int)$item['id'], (int)$item['catid']);
		//$catid		= PhocaCartProduct::getCategoryByProductId((int)$item['id']);
		
		$o = $o2 = '';
		// Content of the wishlist list
		ob_start();
		echo $wishlist->renderList();
		$o = ob_get_contents();
		ob_end_clean();
		
		// Render the layout
		$d = '';
		$layoutW	= new JLayoutFile('popup_add_to_wishlist', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		
		$d['link_wishlist'] = JRoute::_(PhocaCartRoute::getWishListRoute((int)$item['id']), (int)$item['catid']);
		$d['link_continue'] = '';
		// We need to know if module is displayed on wishlist site
		// If yes and one item will be deleted per AJAX, we need to refresh wishlist site
		// If now and one item will be deleted per AJAX, everything is OK, nothing needs to be refreshed
		$d['wishlist_view'] 	= (int)$item['wishlistview'];
		
		if ($added) {
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_ADDED_TO_WISH_LIST');
		} else {
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_WISH_LIST');
			
			$m = $app->getMessageQueue();
			$mO = '';
			 if (!empty($m)) {
			   $mO .= '<ul id="system-messages">';
			   foreach($m as $k => $v) {
				  $mO .=  '<li class="' . $v['type'] . ' ph-msg-error">' . $v['message'] . '</li>';      
			   }
			   $mO .=  '</ul>';
			}
			$d['info_msg_additional'] = $mO;
		}
		
		// Popup with info - Continue,Proceed to wishlist list
		ob_start();
		echo $layoutW->render($d);
		$o2 = ob_get_contents();
		ob_end_clean();
		
		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'popup'		=> $o2);

		echo json_encode($response);
		return;
	}
	
	public function remove() {
		
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		$app 					= JFactory::getApplication();
		$item					= array();
		$item['id']				= $this->input->get( 'id', 0, 'int' );
		$item['catid']			= $this->input->get( 'catid', 0, 'int' );
		$item['return']			= $this->input->get( 'return', '', 'string'  );
		$item['wishlistview']	= $this->input->get( 'wishlistview', 0, 'int'  );
		
		$wishlist	= new PhocaCartWishlist();
		$added		= $wishlist->removeItem((int)$item['id']);
		//$catid		= PhocaCartProduct::getCategoryByProductId((int)$item['id']);
		
		$o = $o2 = '';
		// Content of the wishlist list
		ob_start();
		echo $wishlist->renderList();
		$o = ob_get_contents();
		ob_end_clean();
		
		// Render the layout
		$d = '';
		$layoutW	= new JLayoutFile('popup_remove_from_wishlist', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		
		$d['link_wishlist'] = JRoute::_(PhocaCartRoute::getWishListRoute((int)$item['id']), (int)$item['catid']);
		$d['link_continue'] = '';
		// We need to know if module is displayed on wishlist site
		// If yes and one item will be deleted per AJAX, we need to refresh wishlist site
		// If now and one item will be deleted per AJAX, everything is OK, nothing needs to be refreshed
		$d['wishlist_view'] 	= (int)$item['wishlistview'];
		

		
		if ($added) {
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_WISH_LIST');
		} else {
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_WISH_LIST');
			
			$m = $app->getMessageQueue();
			$mO = '';
			 if (!empty($m)) {
			   $mO .= '<ul id="system-messages">';
			   foreach($m as $k => $v) {
				  $mO .=  '<li class="' . $v['type'] . ' ph-msg-error">' . $v['message'] . '</li>';      
			   }
			   $mO .=  '</ul>';
			}
			$d['info_msg_additional'] = $mO;
		}
		
		// Popup with info - Continue,Proceed to wishlist list
		ob_start();
		echo $layoutW->render($d);
		$o2 = ob_get_contents();
		ob_end_clean();
			
		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'popup'		=> $o2);
		
		echo json_encode($response);
		return;
	}
	
}
?>