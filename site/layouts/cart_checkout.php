<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$app 	= JFactory::getApplication();
$d 		= $displayData;
$price	= new PhocacartPrice();

$p['tax_calculation']			= $d['params']->get( 'tax_calculation', 0 );
$p['stock_checkout']			= $d['params']->get( 'stock_checkout', 0 );
$p['stock_checking']			= $d['params']->get( 'stock_checking', 0 );
$p['display_discount_product']	= $d['params']->get( 'display_discount_product', 1 );
//$p['min_quantity_calculation']	= $d['params']->get( 'min_quantity_calculation', 0 ); set in product xml - product options, not in global


// A) MINIMUM QUANTITY FOR GROUPS - MAIN PRODUCT
if (!empty($d['fullitemsgroup'][0])) {
	foreach($d['fullitemsgroup'][0] as $k => $v) {
		
		if (isset($v['minqtyvalid']) && $v['minqtyvalid'] == 0) {
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.JText::_('COM_PHOCACART_IS').': '.$v['minqty'].'</div>';
		
		}
		
		if (isset($v['minmultipleqtyvalid']) && $v['minmultipleqtyvalid'] == 0) {
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.JText::_('COM_PHOCACART_IS').': '.$v['minmultipleqty'].'</div>';
		
		}
	}
}

if (!empty($d['fullitems'][1])) {
	
	$r		= 'row-fluid';
	$cA 	= 'col-sm-12 col-md-12 col-xs-12';// whole row
	$cI 	= 'col-sm-2 col-md-2 col-xs-2';// image
	$cQ		= 'col-sm-2 col-md-2 col-xs-2';// quantity
	$cN 	= 'col-sm-2 col-md-2 col-xs-2';// netto
	$cT 	= 'col-sm-2 col-md-2 col-xs-2';// tax
	$cB 	= 'col-sm-2 col-md-2 col-xs-2';// brutto
	$cV		= ' ph-vertical-align';
	$cVRow	= ' ph-vertical-align-row';
	$cAT	= 'col-sm-10 col-md-10 col-xs-10';// attributes
	
	// Total summarization
	$cTotE = 'col-sm-6 col-md-6 col-xs-6'; // empty space
	$cTotT = 'col-sm-4 col-md-4 col-xs-4'; // title
	$cTotB = 'col-sm-2 col-md-2 col-xs-2'; // price
	if ((int)$p['tax_calculation'] > 0) {
		$cP 	= 'col-sm-2 col-md-2 col-xs-2';// - 4 (Tax, Netto)
	} else {
		$cP 	= 'col-sm-6 col-md-6 col-xs-6';// + 4 (Tax, Netto)
	}
	
	
	echo '<div class="ph-checkout-cart-box">';
	
	
	// HEADER
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cI.' ph-checkout-cart-image">'.JText::_('COM_PHOCACART_IMAGE').'</div>';
	echo '<div class="'.$cP.' ph-checkout-cart-product">'.JText::_('COM_PHOCACART_PRODUCT').'</div>';
	
	if ((int)$p['tax_calculation'] > 0) {
		echo '<div class="'.$cN.' ph-checkout-cart-netto">'.JText::_('COM_PHOCACART_PRICE_EXCL_TAX').'</div>';
	}
	
	echo '<div class="'.$cQ.' ph-checkout-cart-quantity">'.JText::_('COM_PHOCACART_QUANTITY').'</div>';
	
	if ((int)$p['tax_calculation'] > 0) {
		echo '<div class="'.$cT.' ph-checkout-cart-tax">'.JText::_('COM_PHOCACART_TAX').'</div>';
	}

	echo '<div class="'.$cB.' ph-checkout-cart-brutto">'.JText::_('COM_PHOCACART_PRICE').'</div>';
	echo '</div>'. "\n"; // end row
	

	// ROW
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>'. "\n"; // end row
	
	foreach($d['fullitems'][1] as $k => $v) {
		
		$link 				= PhocacartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);

		// Design only
		$lineThroughClass	= '';
		if ($p['display_discount_product'] == 1 && ($d['fullitems'][2][$k]['discountproduct'] || $d['fullitems'][3][$k]['discountcart'] || $d['couponvalid'])) {
			$lineThroughClass	= ' ph-line-through';
		}

		$image 		= '';
		if (isset($v['image']) && $v['image'] != '') {
			$thumbnail 	= PhocacartFileThumbnail::getThumbnailName($v['image'], 'small', 'productimage');
			if (isset($thumbnail->rel)) {
				$image = '<img src="'.JURI::base(true).'/'.$thumbnail->rel.'" alt="'.strip_tags($v['title']).'" />';
			}
		} else {
			$image = '<div class="ph-no-image"><span class="glyphicon glyphicon-ban-circle"</span></div>';
		}

		echo '<div class="'.$r.$cV.'">';
		echo '<div class="'.$cI.$cVRow.' ph-checkout-cart-image ph-row-image">'.$image.'</div>';
		echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title"><a href="'.$link.'">'.$v['title'].'</a>';
		echo '</div>';
		
		
		if ((int)$p['tax_calculation'] > 0) {
			echo '<div class="'.$cN.$cVRow.$lineThroughClass.' ph-checkout-cart-netto">'.$price->getPriceFormat($v['netto']).'</div>';
		}
		
		echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity">';
		
		echo '<form action="'.$d['linkcheckout'].'" class="form-inline" method="post">';
		echo '<div class="form-group">';
		echo '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		echo '<input type="hidden" name="catid" value="'.(int)$v['catid'].'">';
		echo '<input type="hidden" name="idkey" value="'.$v['idkey'].'">';
		echo '<input type="text" class="form-control ph-input-quantity ph-input-sm" name="quantity" value="'.$v['quantity'].'">';
		echo '<input type="hidden" name="task" value="checkout.update">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="return" value="'.$d['actionbase64'].'" />';
		//UPDATE
		echo ' <button class="btn btn-success btn-xs ph-btn" type="submit" name="action" value="update"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-refresh"></span></button>';
		//DELETE
		echo ' <button class="btn btn-danger btn-xs ph-btn" type="submit" name="action" value="delete"><span title="'.JText::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'" class="glyphicon glyphicon-trash"></span></button>';
		echo JHtml::_('form.token');
		echo '</div>';
		echo '</form>';
		

		echo '</div>';// end quantity
		
		if ((int)$p['tax_calculation'] > 0) {
			echo '<div class="'.$cT.$cVRow.$lineThroughClass.' ph-checkout-cart-tax">'.$price->getPriceFormat($v['tax'] * $v['quantity']).'</div>';
		}
		
		echo '<div class="'.$cB.$cVRow.$lineThroughClass.' ph-checkout-cart-brutto">'.$price->getPriceFormat($v['final']).'</div>';
		echo '</div>'. "\n"; // end row
		
		
		// ATTRIBUTES
		if (!empty($v['attributes'])) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cI.'"></div>';
			echo '<div class="'.$cAT.'">';
			echo '<ul class="ph-checkout-attribute-box">';
			foreach($v['attributes'] as $k2 => $v2) {
				if (!empty($v2)) {
					foreach($v2 as $k3 => $v3) {
						echo '<li class="ph-checkout-attribute-item"><span class="ph-small ph-cart-small-attribute">'.$v3['atitle'] . ' '.$v3['otitle'].'</span></li>';
					}
				}
			}
			echo '</ul>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		
		// DISCOUNT price for each product
		if ($p['display_discount_product'] == 1) {
			
			
			// REWARD DISCOUNT
			if($d['fullitems'][5][$k]['rewardproduct']) {
				
				$discountTitle = JText::_('COM_PHOCACART_REWARD_POINTS_PRICE');
				if (isset($d['fullitems'][5][$k]['rewardproducttitle']) && $d['fullitems'][5][$k]['rewardproducttitle'] != '') {
					$discountTitle = $d['fullitems'][5][$k]['rewardproducttitle'];
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.' '.$d['fullitems'][5][$k]['rewardproducttxtsuffix'].'</div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$price->getPriceFormat($d['fullitems'][5][$k]['netto']).'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$price->getPriceFormat($d['fullitems'][5][$k]['tax'] * $v['quantity']).'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$price->getPriceFormat($d['fullitems'][5][$k]['final']).'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// PRODUCT DISCOUNT
			if($d['fullitems'][2][$k]['discountproduct']) {
				
				$discountTitle = JText::_('COM_PHOCACART_PRODUCT_DISCOUNT_PRICE');
				if (isset($d['fullitems'][2][$k]['discountproducttitle']) && $d['fullitems'][2][$k]['discountproducttitle'] != '') {
					$discountTitle = $d['fullitems'][2][$k]['discountproducttitle'];
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$price->getPriceFormat($d['fullitems'][2][$k]['netto']).'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$price->getPriceFormat($d['fullitems'][2][$k]['tax'] * $v['quantity']).'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$price->getPriceFormat($d['fullitems'][2][$k]['final']).'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// CART DISCOUNT
			if($d['fullitems'][3][$k]['discountcart']) {
				
				$discountTitle = JText::_('COM_PHOCACART_CART_DISCOUNT_PRICE');
				if (isset($d['fullitems'][3][$k]['discountcarttitle']) && $d['fullitems'][3][$k]['discountcarttitle'] != '') {
					$discountTitle = $d['fullitems'][3][$k]['discountcarttitle'];
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$price->getPriceFormat($d['fullitems'][3][$k]['netto']).'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$price->getPriceFormat($d['fullitems'][3][$k]['tax'] * $v['quantity']).'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$price->getPriceFormat($d['fullitems'][3][$k]['final']).'</div>';
				echo '</div>'. "\n"; // end row
			}
			
			// CART COUPON
			if($d['couponvalid']) {

				$couponTitle = JText::_('COM_PHOCACART_COUPON');
				if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
					$couponTitle = $d['coupontitle'];
				}
				
				echo '<div class="'.$r.$cV.' ph-checkout-discount-row">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title">'.$couponTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto">'.$price->getPriceFormat($d['fullitems'][4][$k]['netto']).'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax">'.$price->getPriceFormat($d['fullitems'][4][$k]['tax'] * $v['quantity']).'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto">'.$price->getPriceFormat($d['fullitems'][4][$k]['final']).'</div>';
				echo '</div>'. "\n"; // end row
			}
		}
		
		
		

		// STOCK VALID
		if ($v['stockvalid'] == 0 && $p['stock_checkout'] == 1 && $p['stock_checking'] == 1) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_PRODUCT_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK').'</div>';

			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		// B) MINIMUM QUANTITY - PRODUCT VARIATIONS - EACH PRODUCT VARIATION
		// see cart/calculation class - it is explained why a) method is not used
		if ($v['minqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_THIS_PRODUCT_IS').': '.$v['minqty'].'</div>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
		
		if ($v['minmultipleqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'">';
			echo '<div class="alert alert-error ph-alert-small">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT').': '.$v['minmultipleqty'].'</div>';
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
	}
	

	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';

	
	
	
	// SUBTOTAL NETTO
	if ($d['total'][1]['netto'] !== 0) {

		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_SUBTOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-right">'.$price->getPriceFormat($d['total'][1]['netto']).'</div>';
		echo '</div>';// end row
	}
	
	// REWARD DISCOUNT
	if ($d['total'][5]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_REWARD_POINTS').$d['total'][5]['rewardproducttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-right">'.$price->getPriceFormat($d['total'][5]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// PRODUCT DISCOUNT
	if ($d['total'][2]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_PRODUCT_DISCOUNT').'</div>';
		echo '<div class="'.$cTotB.' ph-right">'.$price->getPriceFormat($d['total'][2]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// CART DISCOUNT
	if ($d['total'][3]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_CART_DISCOUNT').$d['total'][3]['discountcarttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-right">'.$price->getPriceFormat($d['total'][3]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	// COUPON
	if ($d['total'][4]['dnetto'] && $d['couponvalid']) {
		$couponTitle = JText::_('COM_PHOCACART_COUPON');
		if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
			$couponTitle = $d['coupontitle'];
		}
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.$couponTitle.$d['total'][4]['couponcarttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-coupon ph-right">'.$price->getPriceFormat($d['total'][4]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}
	
	

	// TAX
	if (!empty($d['total'][0]['tax'])) {
		foreach($d['total'][0]['tax'] as $k3 => $v3) {
			if($v3['tax'] !== 0 && $p['tax_calculation'] != 0) {
				
				echo '<div class="'.$r.'">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.'">'.$v3['title'].'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$price->getPriceFormat($v3['tax']).'</div>';
				echo '</div>';// end row
			}
		}
	}
	

	

	// SHIPPING
	// Add Shipping costs if there are some
	if (!empty($d['shippingcosts'])) {
		$sC = $d['shippingcosts'];

		if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt']) && $sC['nettotxt'] != '') {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$sC['nettotxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$sC['nettoformat'].'</div>';
			echo '</div>';// end row
		}
		
		if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt']) && $sC['taxtxt'] != '') {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$sC['taxtxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$sC['taxformat'].'</div>';
			echo '</div>';// end row
		}
		
		if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) && $sC['bruttotxt'] != '') || $sC['freeshipping'] == 1) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$sC['bruttotxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$sC['bruttoformat'].'</div>';
			echo '</div>';// end row
		}
	}
	
	// PAYMENT
	// Add Payment costs if there are some
	if (!empty($d['paymentcosts'])) {
		$pC = $d['paymentcosts'];

		if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '') {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$pC['nettotxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$pC['nettoformat'].'</div>';
			echo '</div>';// end row
		}
		
		if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '') {
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$pC['taxtxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$pC['taxformat'].'</div>';
			echo '</div>';// end row
		}
		
		if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freepayment'] == 1) {

			echo '<div class="'.$r.'">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.'">'.$pC['bruttotxt'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right">'.$pC['bruttoformat'].'</div>';
			echo '</div>';// end row
		}
	}
	
	
	// ROUNDING
	if ($d['total'][0]['rounding'] != 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_ROUNDING').'</div>';
		echo '<div class="'.$cTotB.' ph-right">'.$price->getPriceFormat($d['total'][0]['rounding']).'</div>';
		echo '</div>';// end row
	}
	
	// BRUTTO
	
	if ($d['total'][0]['brutto'] !== 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.'">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-cart-total ph-right">'.$price->getPriceFormat($d['total'][0]['brutto']).'</div>';
		echo '</div>';// end row
	}
	

	echo '</div>'. "\n"; // end checkout box
} else {
	echo '<div>'.JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
}
	

?>