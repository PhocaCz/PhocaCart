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
		$options = PhocaCartRegion::getRegionsByCountry($id);
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
	
	// Change pricebox
	function changepricebox($tpl = null){
			
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		$priceO 			= array();
		$paramsC 			= JComponentHelper::getParams('com_phocacart');
		//$tax_calculation	= $paramsC->get( 'tax_calculation', 0 );
		$display_unit_price	= $paramsC->get( 'display_unit_price', 1 );
		
		$app		= JFactory::getApplication();
		$attribute	= $app->input->get( 'attribute', '', 'array'  );
		$id			= $app->input->get( 'id', 0, 'int'  );
		$class		= $app->input->get( 'class', 0, 'string'  );
		
		// Sanitanize data
		$aA = array();
		if (!empty($attribute)) {
			foreach($attribute as $k => $v) {
				$aA[(int)$k] = (int)$v;
			}
		}

		/* TEST */
		/*$aA[30] = 59;
		$aA[31] = 61;
		$class="ph-item-price-box";
		
		$id = 2;*/
		
		
		if ((int)$id > 0) {
			$price	= new PhocaCartPrice();
			$item 	= PhocaCartProduct::getProduct((int)$id);// We don't need catid
			$priceO = array();
			
			if (!empty($item)) {
				
				$priceP = $price->getPriceItems($item->price, $item->taxrate, $item->taxcalctype, $item->taxtitle);
				
				// Main price of the product
				$priceO['netto']	= $priceP['netto'];
				$priceO['brutto']	= $priceP['brutto'];
				$priceO['tax']		= $priceP['tax'];
				
				// ATTRIBUTES
				if (!empty($aA)) {
					foreach ($aA as $k => $v) {
						if ((int)$k > 0 && (int)$v > 0) {
							
							$attrib = PhocaCartAttribute::getAttributeValue((int)$v, (int)$k);
							
							if (isset($attrib->title) && isset($attrib->amount) && isset($attrib->operator)) {
								$priceA = $price->getPriceItems($attrib->amount, $item->taxrate, $item->taxcalctype, $item->taxtitle);
								
								if ($attrib->operator == '-') {
									$priceP['netto']	-= $priceA['netto'];
									$priceP['brutto']	-= $priceA['brutto'];
									$priceP['tax']		-= $priceA['tax'];
									
								} else if ($attrib->operator == '+') {
									$priceP['netto']	+= $priceA['netto'] ;
									$priceP['brutto']	+= $priceA['brutto'] ;
									$priceP['tax']		+= $priceA['tax'] ;
									
								}
							}
						}			
					}
				}

				
				
				$d = array();
				$d['class']			= $class;
				// Original Price
				$d['priceitemsorig']['bruttoformat'] = '';
				if (isset($item->price_original) && $item->price_original != '' && (int)$item->price_original >0) {
					$d['priceitemsorig']['bruttoformat'] = $price->getPriceFormat($item->price_original);
				}
				
				// Standard Price - changed 
				$priceP['nettoformat'] 		= $price->getPriceFormat($priceP['netto']);
				$priceP['bruttoformat'] 	= $price->getPriceFormat($priceP['brutto']);
				$priceP['taxformat'] 		= $price->getPriceFormat($priceP['tax']);
				
			
				// Unit price
				$priceP['base'] 		= '';
				$priceP['baseformat'] 	= '';
				if (isset($item->unit_amount) && $item->unit_amount > 0 && isset($item->unit_unit) && (int)$display_unit_price > 0) {
					$priceP['base'] 		= $priceP['brutto'] / $item->unit_amount;
					$priceP['baseformat'] 	= $price->getPriceFormat($priceP['base']).'/'.$item->unit_unit;
				}
				
				$d['priceitems']		= $priceP;
				
				// Render the layout
				$layoutP	= new JLayoutFile('product_price', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
				ob_start();
				echo $layoutP->render($d);
				$o = ob_get_contents();
				ob_end_clean();
	
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
		
		//$catid	= PhocaCartProduct::getCategoryByProductId((int)$item['id']);
		
		//$cart	= new PhocaCartCart();
		$cart	= new PhocaCartRenderCart();// is subclass of PhocaCartCart, so we can use only subclass
		$added	= $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);
		//$catid	= PhocaCartProduct::getCategoryByProductId((int)$item['id']);
		$cart->setFullItems();
	
		$o = $o2 = '';
		// Content of the cart
		ob_start();
		echo $cart->render();
		$o = ob_get_contents();
		ob_end_clean();
		
		// Render the layout
		$d = '';
		$layoutP	= new JLayoutFile('popup_add_to_cart', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		
		$d['link_checkout'] = JRoute::_(PhocaCartRoute::getCheckoutRoute((int)$item['id'], (int)$item['catid']));
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
		ob_start();
		echo $layoutP->render($d);
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