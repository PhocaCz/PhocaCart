<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutI	= new JLayoutFile('image', null, array('component' => 'com_phocacart'));

$d 		= $displayData;
$price	= new PhocacartPrice();
$app 	= JFactory::getApplication();

// Component parameters params
// Module parameters paramsmodule
$p = array();
$p['tax_calculation']					= $d['params']->get( 'tax_calculation', 0 );
$p['stock_checkout']					= $d['params']->get( 'stock_checkout', 0 );
$p['stock_checking']					= $d['params']->get( 'stock_checking', 0 );
$p['display_discount_product']			= $d['params']->get( 'display_discount_product', 1 );
$p['zero_shipping_price_calculation']	= $d['params']->get( 'zero_shipping_price_calculation', 0 );
$p['zero_payment_price_calculation']	= $d['params']->get( 'zero_payment_price_calculation', 0 );
$p['display_webp_images']				= $d['params']->get( 'display_webp_images', 0 );

if (!empty($d['fullitems'])) {


	// CLASS NAMES
	// Display image next to product title, one more column
	if (isset($d['paramsmodule']['display_image']) && $d['paramsmodule']['display_image'] == 1) {
		//$c2 = 3;// Colspans of table;
		//$c3 = 4;
		$cI 	= 'col-sm-2 col-md-2';// +2
		$cX 	= 'col-sm-1 col-md-1';
		$cXT 	= 'col-sm-5 col-md-5';// -1
		$cXP 	= 'col-sm-4 col-md-4';// -1
		$cS		= '-i';
	} else {
		//$c2 = 2;
		//$c3 = 3;
		$cI 	= '';
		$cX 	= 'col-sm-1 col-md-1'; // X Emtpy space
		$cXT 	= 'col-sm-6 col-md-6'; // T Text/Title
		$cXP 	= 'col-sm-5 col-md-5'; // P Price
		$cS		= ''; // S Suffix
	}
	//$r	= 'row-fluid';
	$r	= 'row';
	$cT = 'col-sm-7 col-md-7';
	$cP = 'col-sm-5 col-md-5';
	$cA = 'col-sm-12 col-md-12';

	///echo '<table class="ph-cart-small-box">';
	echo '<div class="ph-cart-small-box">';
	//echo '<div class="container">';

	/*
	HEAD
	echo '<tr>';
	echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_PRODUCT').'</span></td>';
	echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_COUNT').'</span></td>';
	echo '<td><span class="ph-small">'.JText::_('MOD_PHOCACART_CART_PRICE').'</span></td>';
	echo '</tr>';*/

	///echo '<tr>';
	///echo '<td colspan="'.$c2.'" class="ph-small">'. count($d['fullitems']).' '.JText::_('COM_PHOCACART_ITEM_S').'</td>';
	///echo '<td class="ph-small ph-right">';
	if (isset($d['countitems'])) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small">'. $d['countitems'].' '.JText::_('COM_PHOCACART_ITEM_S').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right">';
	}

	if (isset($d['total']['brutto'])) {
		echo $price->getPriceFormat($d['total']['brutto']);
	}

	///echo '</td>';
	//echo '</tr>';
	echo '</div>';
	echo '</div>';// end row


	//echo '<tr><td colspan="'.$c3.'"><div class="ph-hr"></div></td></tr>';
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>';// end row

	foreach($d['fullitems'][1] as $k => $v) {

		$link = PhocacartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);
		if ($v['netto']) {
			$priceItem = (int)$v['quantity'] * $v['netto'];
		} else {
			$priceItem = (int)$v['quantity'] * $v['brutto'];
		}
		$priceItem = $price->getPriceFormat($priceItem);


		///echo '<tr>';
		echo '<div class="'.$r.'">';
		// Display image next to product title
		if (isset($d['paramsmodule']['display_image']) && $d['paramsmodule']['display_image'] == 1) {
			if (isset($v['image']) && $v['image'] != '') {


				if (empty($v['attributes'])){ $v['attributes'] = array();}
				$image = PhocacartImage::getImageDisplay($v['image'], '', $d['pathitem'], '', '', '', 'small', '', $v['attributes'], 2);


				if (isset($image['image']->rel)) {
					echo '<div class="'.$cI.' ph-small ph-mod-cart-image">';

                    $d2								= array();
                    $d2['t']['display_webp_images']	= $p['display_webp_images'];
                    $d2['src']						= JURI::base(true).'/'.$image['image']->rel;
                    $d2['srcset-webp']				= JURI::base(true).'/'.$image['image']->rel_webp;
                    $d2['alt-value']				= PhocaCartImage::getAltTitle($v['title'], $image['image']->rel);
                    $d2['class']					= PhocacartRenderFront::getClass(array('img-responsive', 'ph-img-cart-cart'));

                    echo $layoutI->render($d2);


					echo '</div>';
				}


			}
		}

		///echo '<td class="ph-small ph-cart-small-quantity">'.$v['quantity'].'x </td>';
		///echo '<td class="ph-small ph-cart-small-title">';
		///echo '<a href="'.$link.'">'.$v['title'].'</a>';
		///echo '</td>';

		///echo '<td class="ph-small ph-cart-small-price ph-right">'.$priceItem.'</td>';
		///echo '</tr>';

		echo '<div class="'.$cX.' ph-small ph-cart-small-quantity">'.$v['quantity'].'x </div>';
		echo '<div class="'.$cXT.' ph-small ph-cart-small-title">';

		// No link in admin
		if ($d['client'] == 1) {
			echo $v['title'];
		} else {
			echo '<a href="'.$link.'">'.$v['title'].'</a>';
		}

		echo '</div>';

		echo '<div class="'.$cXP.' ph-small ph-cart-small-price ph-right">'.$priceItem.'</div>';
		echo '</div>';// end row



		if (!empty($v['attributes'])) {
			///echo '<tr>';
			///echo '<td colspan="'.$c3.'"><ul>';
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cA.'"><ul class="ph-cart-attribute-box'.$cS.'">';

			foreach($v['attributes'] as $k2 => $v2) {

				if (!empty($v2)) {
					foreach($v2 as $k3 => $v3) {
						echo '<li class="ph-cart-attribute-item'.$cS.'"><span class="ph-small ph-cart-small-attribute">'.$v3['atitle'] . ' '.$v3['otitle'].'</span>';

						if (isset($v3['ovalue']) && urldecode($v3['ovalue']) != '') {
							echo ': <span class="ph-small ph-cart-small-attribute">'.htmlspecialchars(urldecode($v3['ovalue']), ENT_QUOTES, 'UTF-8').'</span>';
						}
						echo '</li>';
					}
				}

			}

			///echo '</ul></td>';
			///echo '</tr>';
			echo '</ul></div>';
			echo '</div>';// end row


		}
	}

	///echo '<tr><td colspan="'.$c3.'"><div class="ph-hr"></div></td></tr>';
	echo '<div class="'.$r.'">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>';// end row


	// SUBTOTAL NETTO
	if ($d['total'][1]['netto'] !== 0) {
		///echo '<tr>';
		///echo '<td colspan="'.$c2.'" class="ph-small">'.JText::_('COM_PHOCACART_SUBTOTAL').'</td>';
		///echo '<td class="ph-small ph-right">'.$price->getPriceFormat($d['total']['netto']).'</td>';
		///echo '</tr>';
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-subtotal-netto-txt">'.JText::_('COM_PHOCACART_SUBTOTAL').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-subtotal-netto">'.$price->getPriceFormat($d['total'][1]['netto']).'</div>';
		echo '</div>';// end row
	}

	// REWARD DISCOUNT
	if ($d['total'][5]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-reward-discount-txt">'.JText::_('COM_PHOCACART_REWARD_POINTS').$d['total'][5]['rewardproducttxtsuffix'].'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-reward-discount">'.$price->getPriceFormat($d['total'][5]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}

	// PRODUCT DISCOUNT
	if ($d['total'][2]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-product-discount-txt">'.JText::_('COM_PHOCACART_PRODUCT_DISCOUNT').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-product-discount">'.$price->getPriceFormat($d['total'][2]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}

	// CART DISCOUNT
	if ($d['total'][3]['dnetto']) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-cart-discount-txt">'.JText::_('COM_PHOCACART_CART_DISCOUNT').$d['total'][3]['discountcarttxtsuffix'].'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-cart-discount">'.$price->getPriceFormat($d['total'][3]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}

	// COUPON
	if ($d['total'][4]['dnetto'] && $d['couponvalid']) {
		$couponTitle = JText::_('COM_PHOCACART_COUPON');
		if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
			$couponTitle = $d['coupontitle'];
		}
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-coupon-txt">'.$couponTitle.$d['total'][4]['couponcarttxtsuffix'].'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-coupon">'.$price->getPriceFormat($d['total'][4]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}



	// TAX
	if (!empty($d['total'][0]['tax'])) {
		foreach($d['total'][0]['tax'] as $k3 => $v3) {
			if($v3['tax'] > 0) {

				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-tax-txt">'.$v3['title'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-tax">'.$price->getPriceFormat($v3['tax']).'</div>';
				echo '</div>';// end row
			}
		}
	}




	//SHIPPING
	// Add Shipping costs if there are some
	if (!empty($d['shippingcosts'])) {
		$sC = $d['shippingcosts'];

		if ($p['zero_shipping_price_calculation'] == -1 && $sC['zero'] == 1) {
			// Hide completely
		} else 	if ($p['zero_shipping_price_calculation'] == 0 && $sC['zero'] == 1) {
			// Display blank price field
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-shipping"></div>';
			echo '</div>';// end row

		} else if ($p['zero_shipping_price_calculation'] == 2 && $sC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-shipping">'.JText::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {

			if ($sC['title'] != '') {
				$sC['title'] = $sC['title']. ' - ';
			}
			if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt']) && $sC['nettotxt'] != '') {
				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-shipping-netto-txt">'.$sC['title'].$sC['nettotxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-shipping-netto">'.$sC['nettoformat'].'</div>';
				echo '</div>';// end row
			}

			if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt']) && $sC['taxtxt'] != '') {
				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-shipping-tax-txt">'.$sC['title'].$sC['taxtxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-shipping-tax">'.$sC['taxformat'].'</div>';
				echo '</div>';// end row
			}

			if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) && $sC['bruttotxt'] != '') || $sC['freeshipping'] == 1) {
				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-shipping-brutto-txt">'.$sC['title'].$sC['bruttotxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-shipping-brutto">'.$sC['bruttoformat'].'</div>';
				echo '</div>';// end row
			}
		}
	}

	// PAYMENT

	// Add Payment costs if there are some
	if (!empty($d['paymentcosts'])) {
		$pC = $d['paymentcosts'];

		if ($p['zero_payment_price_calculation'] == -1 && $pC['zero'] == 1) {
			// Hide completely
		} else 	if ($p['zero_payment_price_calculation'] == 0 && $pC['zero'] == 1) {
			// Display blank price field
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-payment"></div>';
			echo '</div>';// end row

		} else if ($p['zero_payment_price_calculation'] == 2 && $pC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-payment">'.JText::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {

			if ($pC['nettotxt'] != '') {
				$pC['title'] = $pC['title']. ' - ';
			}

			if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '') {

				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-payment-netto-txt">'.$pC['title']. $pC['nettotxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-payment-netto">'.$pC['nettoformat'].'</div>';
				echo '</div>';// end row
			}

			if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '') {

				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-payment-tax-txt">'.$pC['title']. $pC['taxtxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-payment-tax">'.$pC['taxformat'].'</div>';
				echo '</div>';// end row
			}

			if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freepayment'] == 1) {

				echo '<div class="'.$r.'">';
				echo '<div class="'.$cT.' ph-small ph-cart-payment-brutto-txt">'.$pC['title']. $pC['bruttotxt'].'</div>';
				echo '<div class="'.$cP.' ph-small ph-right ph-cart-payment-brutto">'.$pC['bruttoformat'].'</div>';
				echo '</div>';// end row
			}
		}

		//////////////
/*
		if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '') {
			///echo '<tr>';
			///echo '<td colspan="'.$c2.'" class="ph-small">'.$pC['nettotxt'].'</td>';
			///echo '<td class="ph-small ph-right">'.$pC['nettoformat'].'</td>';
			///echo '</tr>';
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-netto-txt">'.$pC['nettotxt'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-netto">'.$pC['nettoformat'].'</div>';
			echo '</div>';// end row
		}

		if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '') {
			///echo '<tr>';
			///echo '<td colspan="'.$c2.'" class="ph-small">'.$pC['taxtxt'].'</td>';
			///echo '<td class="ph-small ph-right">'.$pC['taxformat'].'</td>';
			///echo '</tr>';
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-tax-txt">'.$pC['taxtxt'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-tax">'.$pC['taxformat'].'</div>';
			echo '</div>';// end row
		}

		if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freepayment'] == 1) {
			///echo '<tr>';
			///echo '<td colspan="'.$c2.'" class="ph-small">'.$pC['bruttotxt'].'</td>';
			///echo '<td class="ph-small ph-right">'.$pC['bruttoformat'].'</td>';
			///echo '</tr>';
			echo '<div class="'.$r.'">';
			echo '<div class="'.$cT.' ph-small ph-cart-brutto-txt">'.$pC['bruttotxt'].'</div>';
			echo '<div class="'.$cP.' ph-small ph-right ph-cart-brutto">'.$pC['bruttoformat'].'</div>';
			echo '</div>';// end row
		}
		*/
		//////////////
	}

	// ROUNDING | ROUNDING CURRENCY
	if ($d['total'][0]['rounding_currency'] != 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-rounding-currency-txt">'.JText::_('COM_PHOCACART_ROUNDING_CURRENCY').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-rounding-currency">'.$price->getPriceFormat($d['total'][0]['rounding_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	} else if ($d['total'][0]['rounding'] != 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-rounding-txt">'.JText::_('COM_PHOCACART_ROUNDING').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-cart-rounding">'.$price->getPriceFormat($d['total'][0]['rounding']).'</div>';
		echo '</div>';// end row
	}


	// BRUTTO (Because of rounding currency we need to display brutto in currency which is set)
	//if (!($price->roundPrice($d['total'][0]['brutto_currency']) > -0.01 && $price->roundPrice($d['total'][0]['brutto_currency'] < 0.01)) == 1) {
	if ($d['total'][0]['brutto_currency'] !== 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-brutto-currency-txt">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-b ph-cart-brutton-currency">'.$price->getPriceFormat($d['total'][0]['brutto_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	//} else if (!($price->roundPrice($d['total'][0]['brutto']) > -0.01 && $price->roundPrice($d['total'][0]['brutto'] < 0.01)) == 1) {
	} else if ($d['total'][0]['brutto'] !== 0) {
		echo '<div class="'.$r.'">';
		echo '<div class="'.$cT.' ph-small ph-cart-total-txt">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cP.' ph-small ph-right ph-b ph-cart-total">'.$price->getPriceFormat($d['total'][0]['brutto']).'</div>';
		echo '</div>';// end row
	}



	///echo '</table>'. "\n";
	//echo '</div>'; // end container
	echo '</div>'. "\n"; // end small box

} else {
	echo '<div>'.JText::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
}

if ($app->getName() != 'administrator') {


	$linkCheckout		= JRoute::_(PhocacartRoute::getCheckoutRoute());
	$linkCheckoutHtml	= '<div class="ph-small ph-right ph-u ph-cart-link-checkout"><a href="'.$linkCheckout.'">'.JText::_('COM_PHOCACART_VIEW_CART_CHECKOUT').'</a></div>';

	if (isset($d['paramsmodule']['display_checkout_link']) && $d['paramsmodule']['display_checkout_link'] == 1) {
		echo $linkCheckoutHtml;
	} else if (isset($d['paramsmodule']['display_checkout_link']) && $d['paramsmodule']['display_checkout_link'] == 2 && !empty($d['fullitems'])) {
		echo $linkCheckoutHtml;
	}
}
?>
