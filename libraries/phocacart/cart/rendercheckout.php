<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
phocacartimport('phocacart.cart.cart');
phocacartimport('phocacart.price.price');
class PhocaCartRenderCheckout extends PhocaCartCart
{
	protected $fullitems;
	protected $total;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function render() {
		
		$pC 					= JComponentHelper::getParams('com_phocacart') ;
		$p['tax_calculation']	= $pC->get( 'tax_calculation', 0 );
		$p['stock_checkout']	= $pC->get( 'stock_checkout', 0 );
		$p['stock_checking']	= $pC->get( 'stock_checking', 0 );
		$uri 					= JFactory::getURI();
		$url['action']			= $uri->toString();
		$url['actionbase64']	= base64_encode($url['action']);
		$url['linkcheckout']	= JRoute::_(PhocaCartRoute::getCheckoutRoute());
		
		$c	= 4;// Number of columns
	
		$price	= new PhocaCartPrice();
		$o		= array();
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}
		
		
		// MINIMUM QUANTITY FOR GROUPS
		if (!empty($this->fullitemsgroup)) {
			foreach($this->fullitemsgroup as $k => $v) {
				if (isset($v['minqtyvalid']) && $v['minqtyvalid'] == 0) {
					$o[] = '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.JText::_('COM_PHOCACART_IS').': '.$v['minqty'].'</div>';
				
				}
			}
		}


		if (!empty($this->fullitems)) {
			$o[] = '<table class="ph-checkout-cart-box">';

			// HEADER
			$o[] = '<tr>';
			$o[] = '<th class="ph-checkout-cart-image">'.JText::_('COM_PHOCACART_IMAGE').'</th>';
			$o[] = '<th class="ph-checkout-cart-product">'.JText::_('COM_PHOCACART_PRODUCT').'</th>';
			
			if ((int)$p['tax_calculation'] > 0) {
				$o[] = '<th class="ph-checkout-cart-netto">'.JText::_('COM_PHOCACART_PRICE_EXCL_TAX').'</th>';
				$c += 1;
			}
			
			$o[] = '<th class="ph-checkout-cart-quantity">'.JText::_('COM_PHOCACART_QUANTITY').'</th>';
			
			if ((int)$p['tax_calculation'] > 0) {
				$o[] = '<th class="ph-checkout-cart-tax">'.JText::_('COM_PHOCACART_TAX').'</th>';
				$c += 1;
			}
			
			$o[] = '<th class="ph-checkout-cart-brutto">'.JText::_('COM_PHOCACART_PRICE').'</th>';
			$o[] = '</tr>';
		
			
			$o[] = '<tr><td colspan="'.$c.'"><div class="ph-hr"></div></td></tr>';
			
			foreach($this->fullitems as $k => $v) {
			
				$link = PhocaCartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);
				if ($v['netto']) {
					$priceItem = (int)$v['quantity'] * $v['netto'];
				} else {
					$priceItem = (int)$v['quantity'] * $v['brutto'];
				}
				$priceItem = $price->getPriceFormat($priceItem);
				
				$image 		= '';
				if (isset($v['image']) && $v['image'] != '') {
					$thumbnail 	= PhocaCartFileThumbnail::getThumbnailName($v['image'], 'small', 'productimage');
					if (isset($thumbnail->rel)) {
						$image = '<img src="'.JURI::base(true).'/'.$thumbnail->rel.'" alt="'.strip_tags($v['title']).'" />';
					}
				} else {
					$image = '<div class="ph-no-image"><span class="glyphicon glyphicon-ban-circle"</span></div>';
				}
				
				$o[] = '<tr>';
				$o[] = '<td class="ph-checkout-cart-image">'.$image.'</td>';
				
				$o[] = '<td class="ph-checkout-cart-title">';
				$o[] = '<a href="'.$link.'">'.$v['title'].'</a>';
				$o[] = '</td>';
				
				
				if ((int)$p['tax_calculation'] > 0) {
					$o[] = '<td class="ph-checkout-cart-netto">'.$price->getPriceFormat($v['netto']).'</td>';
				}
				$o[] = '<td class="ph-checkout-cart-quantity">';;
				
				
				$o[] = '<form action="'.$url['linkcheckout'].'" class="form-inline" method="post">';
				$o[] = '<div class="form-group">';
				$o[] = '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
				$o[] = '<input type="hidden" name="catid" value="'.(int)$v['catid'].'">';
				$o[] = '<input type="hidden" name="idkey" value="'.$v['idkey'].'">';
				$o[] = '<input type="text" class="form-control ph-input-quantity ph-input-sm" name="quantity" value="'.$v['quantity'].'">';
				$o[] = '<input type="hidden" name="task" value="checkout.update">';
				$o[] = '<input type="hidden" name="tmpl" value="component" />';
				$o[] = '<input type="hidden" name="option" value="com_phocacart" />';
				$o[] = '<input type="hidden" name="return" value="'.$url['actionbase64'].'" />';
				//UPDATE
				$o[] = ' <button class="btn btn-success btn-xs ph-btn" role="button" type="submit" name="action" value="update"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-refresh"></span></button>';
				//DELETE
				$o[] = ' <button class="btn btn-danger btn-xs ph-btn" role="button" type="submit" name="action" value="delete"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-trash"></span></button>';
				$o[] = JHtml::_('form.token');
				$o[] = '</div>';
				$o[] = '</form>';
				
				
				
				$o[] = '</td>';
				if ((int)$p['tax_calculation'] > 0) {
					$o[] = '<td class="ph-checkout-cart-tax">'.$price->getPriceFormat($v['tax'] * $v['quantity']).'</td>';
				}
				$o[] = '<td class="ph-checkout-cart-brutto">'.$priceItem.'</td>';
				$o[] = '</tr>';
				
				// ATTRIBUTES
				if (!empty($v['attributes'])) {
					$c1 = $c - 1;
					$o[] = '<tr><td></td><td colspan="'. $c1 .'"><ul>';
					foreach($v['attributes'] as $k2 => $v2) {
						$o[] = '<li><span class="ph-small ph-cart-small-attribute">'.$v2['atitle'] . ' '.$v2['otitle'].'</span></li>';
					}
					$o[] = '</ul></td></tr>';
				}
				
				// STOCK VALID
				if ($v['stockvalid'] == 0 && $p['stock_checkout'] == 1 && $p['stock_checking'] == 1) {
					$c1 = $c - 1;
					
					$o[] = '<tr><td></td><td colspan="'. $c1 .'">';
					$o[] = '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_PRODUCT_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK').'</div>';
					$o[] = '</td></tr>';
				}
				
				// MINIMUM QUANTITY
				// a) see cart class - it is explained why a) method is not used
				/*if ($v['minqtyvalid'] == 0) {
					$c1 = $c - 1;
					
					$o[] = '<tr><td></td><td colspan="'. $c1 .'">';
					$o[] = '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_THIS_PRODUCT_IS').': '.$v['minqty'].'</div>';
					$o[] = '</td></tr>';
				}*/
				// b) - is set above
				
				
				
				
				
			}
			
			$o[] = '<tr><td colspan="'.$c.'"><div class="ph-hr"></div></td></tr>';
			

			// SUBTOTAL
			if (empty($this->total)) {
				$this->total = $this->getTotal();
			}
			
			
			
			// COLSPAN
			$c2 = $c - 4;
			if ($c2 == 0) {$c2 = 1; $c++;}
			$c3 = $c - 3; 
			
			
			// SUBTOTAL NETTO
			if ($this->total['netto']) {
				$o[] = '<tr><td colspan="'.$c3.'"></td>';
				$o[] = '<td colspan="'.$c2.'">'.JText::_('COM_PHOCACART_SUBTOTAL').'</td>';
				$o[] = '<td class="ph-checkout-total-amount">'.$price->getPriceFormat($this->total['netto']).'</td></tr>';
			}
			
			// COUPON NETTO
			// COUPONTITLE
			if (empty($this->coupontitle)) {
				$this->coupontitle = $this->getCouponTitle();
			}
		
			if ($this->couponvalid && isset($this->total['cnetto']) && (int)$this->total['cnetto'] > 0) {
				$couponTitle = JText::_('COM_PHOCACART_COUPON');
				if (isset($this->coupontitle) && $this->coupontitle != '') {
					$couponTitle = $this->coupontitle;
				}
				$o[] = '<tr><td colspan="'.$c3.'"></td>';
				$o[] = '<td colspan="'.$c2.'">'.$couponTitle.'</td>';
				$o[] = '<td class="ph-checkout-total-coupon">- '.$price->getPriceFormat($this->total['cnetto']).'</td></tr>';
			}
		
			// TAX
			if (!empty($this->total['tax'])) {
				foreach($this->total['tax'] as $k3 => $v3) {
					if((int)$v3['tax'] > 0) {
						
						// COUPON TAX
						$tax = $v3['tax'];
						if ($this->couponvalid && isset($this->total['ctax'][$k3]['tax']) && (int)$this->total['ctax'][$k3]['tax'] > 0) {
							$tax = $v3['tax'] - $this->total['ctax'][$k3]['tax'];
						}
						
						$o[] = '<tr><td colspan="'.$c3.'"></td>';
						$o[] = '<td colspan="'.$c2.'">'.$v3['title'].'</td>';
						$o[] = '<td class="ph-checkout-total-amount">'.$price->getPriceFormat($tax).'</td></tr>';
					}
				}
			}
			
			// SHIPPING
			// Add Shipping costs if there are some
			if (!empty($this->shippingcosts)) {
				$sC = $this->shippingcosts;

				if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt']) && $sC['nettotxt'] != '') {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$sC['nettotxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$sC['nettoformat'].'</td></tr>';
				}
				
				if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt']) && $sC['taxtxt'] != '') {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$sC['taxtxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$sC['taxformat'].'</td></tr>';
				}
				
				if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) && $sC['bruttotxt'] != '') || $sC['freeshipping'] == 1) {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$sC['bruttotxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$sC['bruttoformat'].'</td></tr>';
				}
			}
			
			// PAYMENT
			
			// Add Payment costs if there are some
			if (!empty($this->paymentcosts)) {
				$pC = $this->paymentcosts;

				if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '') {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$pC['nettotxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$pC['nettoformat'].'</td></tr>';
				}
				
				if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '') {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$pC['taxtxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$pC['taxformat'].'</td></tr>';
				}
				
				if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freepayment'] == 1) {
					$o[] = '<tr><td colspan="'.$c3.'"></td>';
					$o[] = '<td colspan="'.$c2.'">'.$pC['bruttotxt'].'</td>';
					$o[] = '<td class="ph-checkout-total-amount">'.$pC['bruttoformat'].'</td></tr>';
				}
			}
			
			// BRUTTO
			if ($this->total['brutto']) {
			
				// COUPON BRUTTO
				$brutto = $this->total['brutto'];
				if ($this->couponvalid && isset($this->total['cbrutto']) && (int)$this->total['cbrutto'] > 0) {
					$brutto = $this->total['brutto'] - $this->total['cbrutto'];
				}
				
				$o[] = '<tr><td colspan="'.$c3.'"></td>';
				$o[] = '<td colspan="'.$c2.'">'.JText::_('COM_PHOCACART_TOTAL').'</td>';
				$o[] = '<td class="ph-checkout-total-amount ph-cart-total">'.$price->getPriceFormat($brutto).'</td></tr>';
			}
			
			$o[] = '</table>'. "\n";
		} else {
			$o[] = '<div>'.JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
		}
		
		return implode( "\n", $o );
	}
}
?>