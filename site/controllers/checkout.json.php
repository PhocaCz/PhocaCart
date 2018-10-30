<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartControllerCheckout extends JControllerForm
{
	// Set Region
	public function setregion() {
	
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="alert alert-danger">' . JText::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			exit;
		}
		
		$app	= JFactory::getApplication();
		$id		= $app->input->get('countryid', 0, 'int');
		
		//$model = $this->getModel('checkout');
		//$options = $model->getRegions($id);
		$options = PhocacartRegion::getRegionsByCountry($id);
		$o = '';
		if(!empty($options)) {
			
			$o .= '<option value="">-&nbsp;'.JText::_('COM_PHOCACART_SELECT_REGION').'&nbsp;-</option>';
			foreach($options as $k => $v) {
				$o .= '<option value="'.$v->id.'">'.htmlspecialchars($v->title).'</option>';
			}
		}
		$response = array(
				'status' => '1',
				'content' => $o);
			echo json_encode($response);
			exit;
		
	}
	
	// Change stockbox
	function changepricebox($tpl = null){
			
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		

		$app		= JFactory::getApplication();
		$attribute	= $app->input->get( 'attribute', '', 'array'  );
		$id			= $app->input->get( 'id', 0, 'int'  );
		$class		= $app->input->get( 'class', '', 'string'  );
		$typeView	= $app->input->get( 'typeview', '', 'string'  );
		
		// Sanitanize data and do the same level for all attributes:
		$aA = PhocacartAttribute::sanitizeAttributeArray($attribute);


		/* TEST */
		/*$aA[30] = 59;
		$aA[31] = 61;
		$class="ph-item-price-box";
		
		$id = 2;*/
		
		
		if ((int)$id > 0) {
			$price	= new PhocacartPrice();
			$item 	= PhocacartProduct::getProduct((int)$id);// We don't need catid
			//$priceO = array();
			
			if (!empty($item)) {
				
				$priceP = $price->getPriceItems($item->price, $item->taxid, $item->taxrate, $item->taxcalculationtype, $item->taxtitle, 0, '', 1, 1, $item->group_price);
				
				$price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $item, 1);
				
				$d = array();
				$d['class']			= $class;
				$d['zero_price']		= 1;// Apply zero price if possible
				// Original Price
				$d['priceitemsorig']['bruttoformat'] = '';
				if (isset($item->price_original) && $item->price_original != '' && (int)$item->price_original >0) {
					$d['priceitemsorig']['bruttoformat'] = $price->getPriceFormat($item->price_original);
				}
				
				$d['priceitems']		= $priceP;
				$d['product_id']		= (int)$item->id;
				$d['typeview']			= $typeView;
				
		
				// Display discount price
				// Move standard prices to new variable (product price -> product discount)
				$d['priceitemsdiscount']	= $d['priceitems'];
				$d['discount'] 				= PhocacartDiscountProduct::getProductDiscountPrice($item->id, $d['priceitemsdiscount']);
				
				// Display cart discount (global discount) in product views - under specific conditions only
				// Move product discount prices to new variable (product price -> product discount -> product discount cart)
				$d['priceitemsdiscountcart']	= $d['priceitemsdiscount'];
				$d['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($item->id, $item->catid, $d['priceitemsdiscountcart']);
				
				// Render the layout
				$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
				//ob_start();
				$o = $layoutP->render($d);
				//$o = ob_get_contents();
				//ob_end_clean();
				
	
	
				$response = array(
					'status' => '1',
					'item' => $o);
				echo json_encode($response);
				return;
			}
		}
		
		$response = array(
		'status'	=> '0',
		'items'		=> '');	
		echo json_encode($response);
		return;
		
		
	}
	
	// Change stockbox
	function changestockbox($tpl = null){
			
			
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app		= JFactory::getApplication();
		$attribute	= $app->input->get( 'attribute', '', 'array'  );
		$id			= $app->input->get( 'id', 0, 'int'  );
		$class		= $app->input->get( 'class', '', 'string'  );
		$typeView	= $app->input->get( 'typeview', '', 'string'  );

		// Sanitanize data and do the same level for all attributes:
		$aA = PhocacartAttribute::sanitizeAttributeArray($attribute);


		if ((int)$id > 0) {
			
			$item 	= PhocacartProduct::getProduct((int)$id);// We don't need catid
			
			$stockStatus = array();
			$stock = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $item, 1);

			$o = '';
			if($stockStatus['stock_status'] || $stockStatus['stock_count']) {
				$layoutS	= new JLayoutFile('product_stock', null, array('component' => 'com_phocacart'));
				$d							= array();
				$d['class']					= $class;
				$d['product_id']			= (int)$id;
				$d['typeview']				= $typeView;
				$d['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($stockStatus);
				
				$o 							= $layoutS->render($d);
				
				//$stock						= (int)$stockStatus['stock_count'];// return stock anyway to enable disable add to cart button if set
			}
			
			
			
			$response = array(
				'status' => '1',
				'stock' => (int)$stock,
				'item' => $o);
			echo json_encode($response);
			return;
		}

		
		$response = array(
		'status'	=> '0',
		'items'		=> '');	
		echo json_encode($response);
		return;

	}
	
	// Add item to cart
	function add($tpl = null){
			
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['attribute']	= $this->input->get( 'attribute', array(), 'array'  );
		$item['checkoutview']	= $this->input->get( 'checkoutview', 0, 'int'  );
		
		
		
		//$catid	= PhocacartProduct::getCategoryByProductId((int)$item['id']);
		
		//$cart	= new PhocacartCart();
		$cart	= new PhocacartCartRendercart();// is subclass of PhocacartCart, so we can use only subclass
		
		// Get Phoca Cart Cart Module Parameters
		$module		= JModuleHelper::getModule('mod_phocacart_cart');
		$paramsM	= new JRegistry($module->params);
		$cart->params['display_image'] 			= $paramsM->get( 'display_image', 0 );
		$cart->params['display_checkout_link'] 	= $paramsM->get( 'display_checkout_link', 1 );
		
		
		$added	= $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);
	
		if (!$added) {
			
			$d 				= array();
			$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();;
			
			$layoutPE		= new JLayoutFile('popup_error', null, array('component' => 'com_phocacart'));
			$oE 			= $layoutPE->render($d);
			$response = array(
				'status' => '0',
				'popup'	=> $oE,
				'error' => $d['info_msg']);
			echo json_encode($response);
			return;
		}
	
		//$catid	= PhocacartProduct::getCategoryByProductId((int)$item['id']);
		$cart->setFullItems();
	
		$o = $o2 = '';
		// Content of the cart
		
		
		ob_start();
		echo $cart->render();
		$o = ob_get_contents();
		ob_end_clean();
		
		
		
		// Render the layout
		$d = array();
		$layoutP	= new JLayoutFile('popup_add_to_cart', null, array('component' => 'com_phocacart'));
		
		$d['link_checkout'] = JRoute::_(PhocacartRoute::getCheckoutRoute((int)$item['id'], (int)$item['catid']));
		$d['link_continue'] = '';
		// It can happen that add to cart button will be e.g. in module and when the module will be displayed on checkout site:
		// If yes and one item will be added per AJAX, we need to refresh checkout site
		// If now and one item will be added per AJAX, everything is OK, nothing needs to be refreshed
		$d['checkout_view'] 	= (int)$item['checkoutview'];
		
		if ($added) {
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART');
		} else {		
			$d['info_msg'] = JText::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART');	
		}
		
		// Popup with info - Continue,Proceed to Checkout
		//ob_start();
		$o2 = $layoutP->render($d);
		//$o2 = ob_get_contents();
		//ob_end_clean();
		
	
		$price	= new PhocacartPrice();
		$count	= $cart->getCartCountItems();
		$total	= 0;
		$totalA	= $cart->getCartTotalItems();
		if (isset($totalA[0]['brutto'])) {
			//$total = $price->getPriceFormat($totalA['fbrutto']); Set in Layout
			$total = $totalA[0]['brutto'];
		}
			
		$response = array(
			'status'	=> '1',
			'item'		=> $o,
			'popup'		=> $o2,
			'count'		=> $count,
			'total'		=> $total);
		
		echo json_encode($response);
		return;

		
	}
	
	// Add item to cart
	function update($tpl = null){
			
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		$msgSuffix			= '';
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['idkey']		= $this->input->get( 'idkey', '', 'string' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['ticketid']	= $this->input->get( 'ticketid', 0, 'int' );
		$item['quantity']	= $this->input->get( 'quantity', 0, 'int'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		$item['attribute']	= $this->input->get( 'attribute', array(), 'array'  );
		$item['checkoutview']	= $this->input->get( 'checkoutview', 0, 'int'  );
		$item['action']		= $this->input->get( 'action', '', 'string'  );
	
		if ((int)$item['idkey'] != '' && $item['action'] != '') {
		
			$cart	= new PhocacartCartRendercheckout();
			
			// Get Phoca Cart Cart Module Parameters
			$module		= JModuleHelper::getModule('mod_phocacart_cart');
			$paramsM	= new JRegistry($module->params);
			$cart->params['display_image'] 			= $paramsM->get( 'display_image', 0 );
			$cart->params['display_checkout_link'] 	= $paramsM->get( 'display_checkout_link', 1 );
			
			if ($item['action'] == 'delete') {
				$updated = $cart->updateItemsFromCheckout($item['idkey'], 0);
				
				if (!$updated) {
			
					$d 				= array();
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
					$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();;
					$layoutPE		= new JLayoutFile('popup_error', null, array('component' => 'com_phocacart'));
					$oE 			= $layoutPE->render($d);
					$response = array(
						'status' => '0',
						'popup'	=> $oE,
						'error' => $d['info_msg']);
					echo json_encode($response);
					return;
				}
				
				/*if ($updated) {
					$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'message');
				} else {
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
				}*/
			} else {// update
				$updated	= $cart->updateItemsFromCheckout($item['idkey'], (int)$item['quantity']);
				
				if (!$updated) {
			
					$d 				= array();
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
					$d['info_msg']	= PhocacartRenderFront::renderMessageQueue();;
					$layoutPE		= new JLayoutFile('popup_error', null, array('component' => 'com_phocacart'));
					$oE 			= $layoutPE->render($d);
					$response = array(
						'status' => '0',
						'popup'	=> $oE,
						'error' => $d['info_msg']);
					echo json_encode($response);
					return;
				}
				/*if ($updated) {
					$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED') .$msgSuffix , 'message');
				} else {
					$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
				}*/
			}
			
			$cart->setFullItems();
	
			$o = $o2 = '';
			
			ob_start();
			echo $cart->render();
			$o = ob_get_contents();
			ob_end_clean();
	
			$price	= new PhocacartPrice();
			$count	= $cart->getCartCountItems();
			$total	= 0;
			$totalA	= $cart->getCartTotalItems();
			if (isset($totalA[0]['brutto'])) {
				//$total = $price->getPriceFormat($totalA['fbrutto']); Set in Layout
				$total = $totalA[0]['brutto'];
			}
				
			$response = array(
				'status'	=> '1',
				'item'		=> $o,
				'popup'		=> $o2,
				'count'		=> $count,
				'total'		=> $total);
			
			echo json_encode($response);
			return;
		}
		
		$response = array(
			'status' => '0',
			'popup'	=> '',
			'error' => '');
		echo json_encode($response);
		return;
		
	}

}
?>