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
class PhocaCartRenderCart extends PhocaCartCart
{

	protected $fullitems;
	protected $total;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function render() {
		
		$price	= new PhocaCartPrice();
		$app 	= JFactory::getApplication();
		$o		= array();
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}
	
		
		if (!empty($this->fullitems)) {
			$o[] = '<table class="ph-cart-small-box">';
			/*echo '<tr>';
			echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_PRODUCT').'</span></td>';
			echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_COUNT').'</span></td>';
			echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_PRICE').'</span></td>';
			echo '</tr>';*/
			
			$o[] = '<tr>';
			$o[] = '<td colspan="2" class="ph-small">'. count($this->fullitems).' '.JText::_('COM_PHOCACART_ITEM_S').'</td>';
			$o[] = '<td class="ph-small ph-right">';
			if (isset($this->total['brutto'])) {
				$o[] = $price->getPriceFormat($this->total['brutto']);
			}
			$o[] = '</td>';
			$o[] = '</tr>';
			
			$o[] = '<tr><td colspan="3"><div class="ph-hr"></div></td></tr>';
			
			foreach($this->fullitems as $k => $v) {
			
				$link = PhocaCartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);
				if ($v['netto']) {
					$priceItem = (int)$v['quantity'] * $v['netto'];
				} else {
					$priceItem = (int)$v['quantity'] * $v['brutto'];
				}
				$priceItem = $price->getPriceFormat($priceItem);
				$o[] = '<tr>';
				$o[] = '<td class="ph-small ph-cart-small-quantity">'.$v['quantity'].'x </td>';
				$o[] = '<td class="ph-small ph-cart-small-title">';
				
				$o[] = '<a href="'.$link.'">'.$v['title'].'</a>';
				$o[] = '</td>';
				$o[] = '<td class="ph-small ph-cart-small-price ph-right">'.$priceItem.'</td>';
				$o[] = '</tr>';
				
				if (!empty($v['attributes'])) {
					$o[] = '<tr><td colspan="3"><ul>';
					foreach($v['attributes'] as $k2 => $v2) {
						
						$o[] = '<li><span class="ph-small ph-cart-small-attribute">'.$v2['atitle'] . ' '.$v2['otitle'].'</span></li>';
					}
					$o[] = '</ul></td></tr>';
				}
			}
			
			$o[] = '<tr><td colspan="3"><div class="ph-hr"></div></td></tr>';
			
			// SUBTOTAL
			if (empty($this->total)) {
				$this->total = $this->getTotal();
			}
			
			// SUBTOTAL NETTO
			if ($this->total['netto']) {
				$o[] = '<tr><td colspan="2" class="ph-small">'.JText::_('COM_PHOCACART_SUBTOTAL').'</td>';
				$o[] = '<td class="ph-small ph-right">'.$price->getPriceFormat($this->total['netto']).'</td></tr>';
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
				$o[] = '<tr>';
				$o[] = '<td colspan="2" class="ph-small">'.$couponTitle.'</td>';
				$o[] = '<td class="ph-small ph-right">- '.$price->getPriceFormat($this->total['cnetto']).'</td></tr>';
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
						
						$o[] = '<tr>';
						$o[] = '<td colspan="2" class="ph-small">'.$v3['title'].'</td>';
						$o[] = '<td class="ph-small ph-right">'.$price->getPriceFormat($tax).'</td></tr>';
					}
				}
			}
			//SHIPPING
			// Add Shipping costs if there are some
			if (!empty($this->shippingcosts)) {
				$sC = $this->shippingcosts;

				if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt']) && $sC['nettotxt'] != '') {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$sC['nettotxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$sC['nettoformat'].'</td></tr>';
				}
				
				if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt']) && $sC['taxtxt'] != '') {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$sC['taxtxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$sC['taxformat'].'</td></tr>';
				}
				
				if (isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) && $sC['bruttotxt'] != '') {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$sC['bruttotxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$sC['bruttoformat'].'</td></tr>';
				}
			}
			
			// PAYMENT
			
			// Add Payment costs if there are some
			if (!empty($this->paymentcosts)) {
				$pC = $this->paymentcosts;

				if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '') {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$pC['nettotxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$pC['nettoformat'].'</td></tr>';
				}
				
				if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '') {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$pC['taxtxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$pC['taxformat'].'</td></tr>';
				}
				
				if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freepayment'] == 1) {
					$o[] = '<tr>';
					$o[] = '<td colspan="2" class="ph-small">'.$pC['bruttotxt'].'</td>';
					$o[] = '<td class="ph-small ph-right">'.$pC['bruttoformat'].'</td></tr>';
				}
			}
			
			// BRUTTO
			if ($this->total['brutto']) {
			
				// COUPON BRUTTO
				$brutto = $this->total['brutto'];
				if ($this->couponvalid && isset($this->total['cbrutto']) && (int)$this->total['cbrutto'] > 0) {
					$brutto = $this->total['brutto'] - $this->total['cbrutto'];
				}
				
				$o[] = '<tr>';
				$o[] = '<td colspan="2" class="ph-small">'.JText::_('COM_PHOCACART_TOTAL').'</td>';
				$o[] = '<td class="ph-small ph-right ph-b">'.$price->getPriceFormat($brutto).'</td></tr>';
			}
			
			$o[] = '</table>'. "\n";
		} else {
			$o[] = '<div>'.JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
		}
		
		if ($app->getName() != 'administrator') {
			$linkCheckout = JRoute::_(PhocaCartRoute::getCheckoutRoute());
			$o[] = '<div class="ph-small ph-right ph-u ph-cart-link-checkout"><a href="'.$linkCheckout.'">'.JText::_('COM_PHOCACART_VIEW_CART_CHECKOUT').'</a></div>';
		}
		
		return implode( "\n", $o );
	}
}
?>