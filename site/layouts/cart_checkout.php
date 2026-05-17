<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$layoutI	= new FileLayout('image', null, array('component' => 'com_phocacart'));
$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));
$layoutSC	= new FileLayout('subscription_cart', null, array('component' => 'com_phocacart'));

$app 		= Factory::getApplication();
$d 			= $displayData;
$price		= new PhocacartPrice();
$msgSuffix	= '<span id="ph-msg-ns" class="ph-hidden"></span>';
$p = array();
$p['tax_calculation']					= $d['params']->get( 'tax_calculation', 0 );
$p['display_zero_tax']					= $d['params']->get( 'display_zero_tax', 0 );
$p['stock_checkout']					= $d['params']->get( 'stock_checkout', 0 );
$p['stock_checking']					= $d['params']->get( 'stock_checking', 0 );
$p['display_discount_product']			= $d['params']->get( 'display_discount_product', 1 );
$p['display_discount_price_product']	= $d['params']->get( 'display_discount_price_product', 1 );
$p['zero_shipping_price_calculation']	= $d['params']->get( 'zero_shipping_price_calculation', 0 );
$p['zero_payment_price_calculation']	= $d['params']->get( 'zero_payment_price_calculation', 0 );
$p['display_reward_points_receive_info']= $d['params']->get( 'display_reward_points_receive_info', 0 );
$p['display_webp_images']				= $d['params']->get( 'display_webp_images', 0 );
$p['display_zero_total']			    = $d['params']->get( 'display_zero_total', 0 );
$p['checkout_separate_by_owner']		= $d['params']->get( 'checkout_separate_by_owner', 0 );
$p['tax_calculation_sales']	 			= $d['params']->get( 'tax_calculation_sales', 1);
$p['tax_calculation_sales_change_subtotal']= $d['params']->get( 'tax_calculation_sales_change_subtotal', 0);


//$p['min_quantity_calculation']	= $d['params']->get( 'min_quantity_calculation', 0 ); set in product xml - product options, not in global

// POS
$task 			= $d['pos'] == true ? 'pos.update' : 'checkout.update';
$inputNumber	= $d['pos'] == true ? 'number' : 'text';
$displayTax		= true;// Specific settings for POS - to make smaller width of cart

// A) MINIMUM QUANTITY FOR GROUPS - MAIN PRODUCT
if (!empty($d['fullitemsgroup'][0])) {
	foreach($d['fullitemsgroup'][0] as $k => $v) {

		if (isset($v['minqtyvalid']) && $v['minqtyvalid'] == 0) {
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.Text::_('COM_PHOCACART_IS').': '.$v['minqty']. $msgSuffix));
		}

		if (isset($v['minmultipleqtyvalid']) && $v['minmultipleqtyvalid'] == 0) {
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.Text::_('COM_PHOCACART_IS').': '.$v['minmultipleqty']. $msgSuffix ));
		}

		if (isset($v['maxqtyvalid']) && $v['maxqtyvalid'] == 0) {
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MAXIMUM_ORDER_QUANTITY_FOR_PRODUCT'). ' '.$v['title']. ' '.Text::_('COM_PHOCACART_IS').': '.$v['maxqty']. $msgSuffix));
		}
	}
}

if (!empty($d['fullitems'][1])) {

	$r		= $d['s']['c']['row'];
	$cA 	= $d['s']['c']['col.xs12.sm12.md12'];// whole row
	$cI 	= $d['s']['c']['col.xs2.sm2.md2'];// image
	$cQ		= $d['s']['c']['col.xs2.sm2.md2'];// quantity
	$cN 	= $d['s']['c']['col.xs2.sm2.md2'];// netto
	$cT 	= $d['s']['c']['col.xs2.sm2.md2'];// tax
	$cB 	= $d['s']['c']['col.xs2.sm2.md2'];// brutto
	$cV		= ' ph-vertical-align';
	$cVRow	= ' ph-vertical-align-row';
	$cAT	= $d['s']['c']['col.xs10.sm10.md10'];// attributes

	// Total summarization
	$cTotE = $d['s']['c']['col.xs0.sm6.md6']; // empty space
	$cTotT = $d['s']['c']['col.xs8.sm4.md4']; // title
	$cTotB = $d['s']['c']['col.xs4.sm2.md2']; // price
	if ((int)$p['tax_calculation'] > 0) {
		$cP 	= $d['s']['c']['col.xs2.sm2.md2'];// title - 4 (Tax, Netto)
	} else {
		$cP 	= $d['s']['c']['col.xs6.sm6.md6'];// title + 4 (Tax, Netto)
	}

	if ($d['pos']) {

		// HIDE TAX for POS
		$displayTax = false;

		$cI 	= $d['s']['c']['col.xs0.sm0.md0'];// image (display: none in css)
		//$cQ		= 'col-sm-3 col-md-3 col-xs-3';// quantity
		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			$cP 	= $d['s']['c']['col.xs2.sm2.md2'];// - 4 (Tax, Netto)
		} else {
			$cP 	= $d['s']['c']['col.xs6.sm6.md6'];// + 4 (Tax, Netto)
		}
		$cQ		= $d['s']['c']['col.xs3.sm3.md3'].' ph-pd-zero';// quantity
		$cN 	= $d['s']['c']['col.xs3.sm3.md3'];// netto
		$cT 	= $d['s']['c']['col.xs3.sm3.md3'];// tax
		$cB 	= $d['s']['c']['col.xs3.sm3.md3'];// brutto

		$cAT	= $d['s']['c']['col.xs12.sm12.md12'];// attributes

		// Total summarization
		$cTotE = $d['s']['c']['col.xs0.sm0.md0']; // empty space
		$cTotT = $d['s']['c']['col.xs8.sm8.md8']; // title
		$cTotB = $d['s']['c']['col.xs4.sm4.md4']; // price

		$cV		= '';
		$cVRow	= '';



	}


	echo '<div class="ph-checkout-cart-box">';


	// HEADER
	echo '<div class="'.$r.' ph-checkout-cart-row-header">';
	echo '<div class="'.$cI.' ph-checkout-cart-image" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_IMAGE')).'">'.Text::_('COM_PHOCACART_IMAGE').'</div>';
	echo '<div class="'.$cP.' ph-checkout-cart-product" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRODUCT')).'">'.Text::_('COM_PHOCACART_PRODUCT').'</div>';

	if ((int)$p['tax_calculation'] > 0 && $displayTax) {
		echo '<div class="'.$cN.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.Text::_('COM_PHOCACART_PRICE_EXCL_TAX').'</div>';
	}

	echo '<div class="'.$cQ.' ph-checkout-cart-quantity" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_QUANTITY')).'">'.Text::_('COM_PHOCACART_QUANTITY').'</div>';

	if ((int)$p['tax_calculation'] > 0 && $displayTax) {
		echo '<div class="'.$cT.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.Text::_('COM_PHOCACART_TAX').'</div>';
	}

	echo '<div class="'.$cB.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE')).'">'.Text::_('COM_PHOCACART_PRICE').'</div>';
	echo '</div>'. "\n"; // end row


	// ROW
	echo '<div class="'.$r.' ph-checkout-cart-row-hr">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>'. "\n"; // end row

	$lastOwner = null;
	foreach($d['fullitems'][1] as $k => $v) {
		if ($p['checkout_separate_by_owner'] && ($lastOwner !== $v['owner_id'])) {
			echo '<div class="'.$r.' ph-cart-cart-row-vendor">';
			echo '<div class="'.$cA.'"><h5>' . ($v['owner_name'] ? Text::sprintf('COM_PHOCACART_CART_VENDOR', $v['owner_name']) : Text::_('COM_PHOCACART_CART_UNKNOWN_VENDOR')) . '</h5></div>';
			echo '</div>';
			$lastOwner = $v['owner_id'];
		}


		$link 				= PhocacartRoute::getItemRoute((int)$v['id'], (int)$v['catid'], $v['alias']);

		// Design only
		$lineThroughClass	= '';
		if ($p['display_discount_product'] == 1 && ($d['fullitems'][2][$k]['discountproduct'] || $d['fullitems'][3][$k]['discountcart'] || $d['couponvalid'])) {
			$lineThroughClass	= ' ph-line-through';
		}


		if (isset($v['image']) && $v['image'] != '') {

			if (empty($v['attributes'])){ $v['attributes'] = array();}
			$image = PhocacartImage::getImageDisplay($v['image'], '', $d['pathitem'], '', '', '', 'small', '', $v['attributes'], 2);

			if (isset($image['image']->rel)) {

				$d2								= array();
				$d2['t']['display_webp_images']	= $p['display_webp_images'];
				$d2['src']						= Uri::base(true).'/'.$image['image']->rel;
				$d2['srcset-webp']				= Uri::base(true).'/'.$image['image']->rel_webp;
				$d2['alt-value']				= PhocaCartImage::getAltTitle($v['title'], $image['image']->rel);
				$d2['class']					= PhocacartRenderFront::completeClass(array($d['s']['c']['img-responsive'], 'ph-img-cart-checkout'));

				$imageOutput = $layoutI->render($d2);
			}
		} else {
			$imageOutput = '<div class="ph-no-image">'.PhocacartRenderIcon::icon($d['s']['i']['ban']).'</div>';
		}

		echo '<div class="'.$r.$cV.' ph-checkout-cart-row-item">';
		echo '<div class="'.$cI.$cVRow.' ph-checkout-cart-image ph-row-image" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_IMAGE')).'">'.$imageOutput.'</div>';
		echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRODUCT')).'"><a href="'.Route::_($link).'">'.$v['title'].'</a>';


		if (isset($v['subscription_scenario']) && !empty($v['subscription_scenario'])) {
			$dSC      = [];
			$dSC['s'] = $d['s'];
			$dSC['scenario'] = $v['subscription_scenario'];
			echo $layoutSC->render($dSC);
		}

		echo '</div>';


		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			echo '<div class="'.$cN.$cVRow.$lineThroughClass.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.$price->getPriceFormat($v['netto']).'</div>';
		}

		echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_QUANTITY')).'">';

		echo '<form action="'.$d['linkcheckout'].'" class="'.$d['s']['c']['form-inline'].' phItemCartUpdateBoxForm" method="post">';
		echo '<div class="'.$d['s']['c']['input-group'].'">';
		echo '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		echo '<input type="hidden" name="catid" value="'.(int)$v['catid'].'">';
		echo '<input type="hidden" name="idkey" value="'.$v['idkey'].'">';
		echo '<input type="hidden" name="ticketid" value="'.(int)$d['ticketid'].'">';
		echo '<input type="hidden" name="unitid" value="'.(int)$d['unitid'].'">';
		echo '<input type="hidden" name="sectionid" value="'.(int)$d['sectionid'].'">';
		echo '<input type="'.$inputNumber.'" class="'.$d['s']['c']['inputbox.form-control'].' ph-input-quantity '.$d['s']['c']['input-xsmall'].'" name="quantity" value="'.$v['quantity'].'">';
		echo '<input type="hidden" name="task" value="'.$task.'">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="return" value="'.$d['actionbase64'].'" />';
		//UPDATE
		echo ' <button class="'.$d['s']['c']['btn.btn-success.btn-sm'].' ph-btn" type="submit" name="action" value="update">'.PhocacartRenderIcon::icon($d['s']['i']['refresh'], 'title="'.Text::_('COM_PHOCACART_UPDATE_QUANTITY_IN_CART').'"').'</button>';
		//DELETE
		echo ' <button class="'.$d['s']['c']['btn.btn-danger.btn-sm'].' ph-btn" type="submit" name="action" value="delete">'.PhocacartRenderIcon::icon($d['s']['i']['trash'], 'title="'.Text::_('COM_PHOCACART_REMOVE_PRODUCT_FROM_CART').'"').'</button>';
		echo HTMLHelper::_('form.token');
		echo '</div>';
		echo '</form>';


		echo '</div>';// end quantity

		if ((int)$p['tax_calculation'] > 0 && $displayTax) {
			echo '<div class="'.$cT.$cVRow.$lineThroughClass.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.$price->getPriceFormat($v['tax'] * $v['quantity']).'</div>';
		}

		echo '<div class="'.$cB.$cVRow.$lineThroughClass.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE')).'">'.$price->getPriceFormat($v['final']).'</div>';
		echo '</div>'. "\n"; // end row


		// ATTRIBUTES
		if (!empty($v['attributes'])) {

			echo '<div class="'.$r.' ph-checkout-cart-row-attributes">';
			echo '<div class="'.$cI.'"></div>';
			echo '<div class="'.$cAT.'">';
			echo '<ul class="ph-checkout-attribute-box">';
			foreach($v['attributes'] as $k2 => $v2) {
				if (!empty($v2)) {
					foreach($v2 as $k3 => $v3) {
						echo '<li class="ph-checkout-attribute-item"><span class="ph-small ph-cart-small-attribute">'.$v3['atitle'] . ' '.$v3['otitle'].'</span>';
						if (isset($v3['ovalue']) && urldecode($v3['ovalue']) != '') {
							echo ': <span class="ph-small ph-cart-small-attribute">'.htmlspecialchars(urldecode($v3['ovalue']), ENT_QUOTES, 'UTF-8').'</span>';
						}
						echo '</li>';
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
			if($d['fullitems'][5][$k]['rewardproduct'] && $p['display_discount_price_product'] > 0) {

				$discountTitle = Text::_('COM_PHOCACART_REWARD_POINTS_PRICE');
				if (isset($d['fullitems'][5][$k]['rewardproducttitle']) && $d['fullitems'][5][$k]['rewardproducttitle'] != '') {
					$discountTitle = $d['fullitems'][5][$k]['rewardproducttitle'];
				}

				$rewardNetto 	= $price->getPriceFormat($d['fullitems'][5][$k]['netto']);
				$rewardTax 		= $price->getPriceFormat($d['fullitems'][5][$k]['tax'] * $v['quantity']);
				$rewardFinal 	= $price->getPriceFormat($d['fullitems'][5][$k]['final']);


				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][5][$k]['finaldiscount'])) {
					$rewardNetto 	= $price->getPriceFormat($d['fullitems'][5][$k]['nettodiscount'], 1);
					$rewardTax 		= $price->getPriceFormat($d['fullitems'][5][$k]['taxdiscount'] * $v['quantity'], 1);
					$rewardFinal 	= $price->getPriceFormat($d['fullitems'][5][$k]['finaldiscount'], 1);
				}

				echo '<div class="'.$r.$cV.' ph-checkout-discount-row ph-checkout-cart-row-discount-reward">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TITLE')).'">'.$discountTitle.' '.$d['fullitems'][5][$k]['rewardproducttxtsuffix'].'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.$rewardNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.$rewardTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_INCL_TAX')).'">'.$rewardFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}

			// PRODUCT DISCOUNT

			if($d['fullitems'][2][$k]['discountproduct'] && (($p['display_discount_price_product'] == 1 && $d['fullitems'][2][$k]['netto'] > 0) || $p['display_discount_price_product'] == 2)) {

				$discountTitle = Text::_('COM_PHOCACART_PRODUCT_DISCOUNT_PRICE');
				if (isset($d['fullitems'][2][$k]['discountproducttitle']) && $d['fullitems'][2][$k]['discountproducttitle'] != '') {
					$discountTitle = $d['fullitems'][2][$k]['discountproducttitle'];
				}

				$productNetto 	= $price->getPriceFormat($d['fullitems'][2][$k]['netto']);
				$productTax 	= $price->getPriceFormat($d['fullitems'][2][$k]['tax'] * $v['quantity']);
				$productFinal 	= $price->getPriceFormat($d['fullitems'][2][$k]['final']);


				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][2][$k]['finaldiscount'])) {
					$productNetto 	= $price->getPriceFormat($d['fullitems'][2][$k]['nettodiscount'], 1);
					$productTax 	= $price->getPriceFormat($d['fullitems'][2][$k]['taxdiscount'] * $v['quantity'], 1);
					$productFinal 	= $price->getPriceFormat($d['fullitems'][2][$k]['finaldiscount'], 1);
				}

				echo '<div class="'.$r.$cV.' ph-checkout-discount-row ph-checkout-cart-row-discount-product">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TITLE')).'">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.$productNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.$productTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_INCL_TAX')).'">'.$productFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}

			// CART DISCOUNT

			if($d['fullitems'][3][$k]['discountcart'] && (($p['display_discount_price_product'] == 1 && $d['fullitems'][3][$k]['netto'] > 0) || $p['display_discount_price_product'] == 1)) {

				$discountTitle = Text::_('COM_PHOCACART_CART_DISCOUNT_PRICE');
				if (isset($d['fullitems'][3][$k]['discountcarttitle']) && $d['fullitems'][3][$k]['discountcarttitle'] != '') {
					$discountTitle = $d['fullitems'][3][$k]['discountcarttitle'];
				}

				$cartNetto 	= $price->getPriceFormat($d['fullitems'][3][$k]['netto']);
				$cartTax 	= $price->getPriceFormat($d['fullitems'][3][$k]['tax'] * $v['quantity']);
				$cartFinal 	= $price->getPriceFormat($d['fullitems'][3][$k]['final']);

				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][3][$k]['finaldiscount'])) {
					$cartNetto 	= $price->getPriceFormat($d['fullitems'][3][$k]['nettodiscount'], 1);
					$cartTax 	= $price->getPriceFormat($d['fullitems'][3][$k]['taxdiscount'] * $v['quantity'], 1);
					$cartFinal 	= $price->getPriceFormat($d['fullitems'][3][$k]['finaldiscount'], 1);
				}

				echo '<div class="'.$r.$cV.' ph-checkout-discount-row ph-checkout-cart-row-discount-cart">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TITLE')).'">'.$discountTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.$cartNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.$cartTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_INCL_TAX')).'">'.$cartFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}

			// CART COUPON
			if($d['couponvalid'] && $d['fullitems'][4][$k]['couponcart'] && $p['display_discount_price_product'] > 0) {

				$couponTitle = Text::_('COM_PHOCACART_COUPON');
				if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
					$couponTitle = $d['coupontitle'];
				}

				$couponNetto 	= $price->getPriceFormat($d['fullitems'][4][$k]['netto']);
				$couponTax 		= $price->getPriceFormat($d['fullitems'][4][$k]['tax'] * $v['quantity']);
				$couponFinal 	= $price->getPriceFormat($d['fullitems'][4][$k]['final']);

				if ($p['display_discount_price_product'] == 2 && isset($d['fullitems'][4][$k]['finaldiscount'])) {
					$couponNetto 	= $price->getPriceFormat($d['fullitems'][4][$k]['nettodiscount'], 1);
					$couponTax 		= $price->getPriceFormat($d['fullitems'][4][$k]['taxdiscount'] * $v['quantity'], 1);
					$couponFinal 	= $price->getPriceFormat($d['fullitems'][4][$k]['finaldiscount'], 1);
				}

				echo '<div class="'.$r.$cV.' ph-checkout-discount-row ph-checkout-cart-row-discount-coupon">';
				echo '<div class="'.$cI.$cVRow.'"></div>';
				echo '<div class="'.$cP.$cVRow.' ph-checkout-cart-title" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TITLE')).'">'.$couponTitle.'</div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cN.$cVRow.' ph-checkout-cart-netto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_EXCL_TAX')).'">'.$couponNetto.'</div>';
				}
				echo '<div class="'.$cQ.$cVRow.' ph-checkout-cart-quantity"></div>';
				if ((int)$p['tax_calculation'] > 0 && $displayTax) {
					echo '<div class="'.$cT.$cVRow.' ph-checkout-cart-tax" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_TAX')).'">'.$couponTax.'</div>';
				}
				echo '<div class="'.$cB.$cVRow.' ph-checkout-cart-brutto" data-pc-label="'.htmlspecialchars(Text::_('COM_PHOCACART_PRICE_INCL_TAX')).'">'.$couponFinal.'</div>';
				echo '</div>'. "\n"; // end row
			}
		}




		// STOCK VALID
		if ($v['stockvalid'] == 0 && $p['stock_checkout'] == 1 && $p['stock_checking'] == 1) {

			echo '<div class="'.$r.' ph-checkout-cart-row-stock">';
			echo '<div class="'.$cA.'">';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_PRODUCT_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK'), 'class' => 'ph-alert-small'));
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}

		// B) MINIMUM QUANTITY - PRODUCT VARIATIONS - EACH PRODUCT VARIATION
		// see cart/calculation class - it is explained why a) method is not used
		if ($v['minqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.' ph-checkout-cart-row-min-quantity">';
			echo '<div class="'.$cA.'">';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_FOR_THIS_PRODUCT_IS').': '.$v['minqty'], 'class' => 'ph-alert-small'));
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}

		if ($v['minmultipleqtyvalid'] == 0 && ($v['minqtycalculation'] == 1 || $v['minqtycalculation'] == 2)) {
			echo '<div class="'.$r.' ph-checkout-cart-row-min-multiple-quantity">';
			echo '<div class="'.$cA.'">';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_FOR_PRODUCT').': '.$v['minmultipleqty'], 'class' => 'ph-alert-small'));
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}

		// B) MAXIMUM QUANTITY - PRODUCT VARIATIONS - EACH PRODUCT VARIATION
		// see cart/calculation class - it is explained why a) method is not used
		if ($v['maxqtyvalid'] == 0 && ($v['maxqtycalculation'] == 1 || $v['maxqtycalculation'] == 2)) {
			echo '<div class="'.$r.' ph-checkout-cart-row-min-quantity">';
			echo '<div class="'.$cA.'">';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MAXIMUM_ORDER_QUANTITY_FOR_THIS_PRODUCT_IS').': '.$v['maxqty'], 'class' => 'ph-alert-small'));
			echo '</div>';
			echo '</div>'. "\n"; // end row
		}
	}


	// ROW
	echo '<div class="'.$r.' ph-checkout-cart-row-hr">';
	echo '<div class="'.$cA.'"><div class="ph-hr"></div></div>';
	echo '</div>'. "\n"; // end row



	// SUBTOTAL NETTO
	if ($d['total'][1]['netto'] !== 0) {

		echo '<div class="'.$r.' ph-cart-subtotal-box ph-checkout-cart-row-subtotal-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-subtotal-netto-txt">'.Text::_('COM_PHOCACART_SUBTOTAL').'</div>';

		if ($p['tax_calculation_sales'] == 2 && $p['tax_calculation_sales_change_subtotal'] == 1) {

			// $d['total'][4]['rounding'] is not the final rounding but rounding after discounts
			//$subTotalFromBrutto = $d['total'][0]['subtotalbrutto'] - $d['total'][0]['taxsum'] - $d['total'][4]['rounding'];
			// This method is used, because tax calculation can change the subtotal/total calculation
			$dBrutto = $d['total'][2]['dbrutto'] + $d['total'][3]['dbrutto'] + $d['total'][4]['dbrutto'] + $d['total'][5]['dbrutto'];
			$subTotalFromBrutto = $d['total'][0]['brutto'] + $dBrutto - $d['total'][0]['taxsum'] - $d['total'][0]['rounding'];
 			echo '<div class="'.$cTotB.' ph-right ph-cart-subtotal-netto">'.$price->getPriceFormat($subTotalFromBrutto).'</div>';

		} else {
			echo '<div class="' . $cTotB . ' ph-right ph-cart-subtotal-netto">' . $price->getPriceFormat($d['total'][1]['netto']) . '</div>';
		}
		echo '</div>';// end row
	}

	// REWARD DISCOUNT
	if ($d['total'][5]['dnetto']) {
		echo '<div class="'.$r.' ph-cart-reward-discount-box ph-checkout-cart-row-discount-reward-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-reward-discount-txt">'.Text::_('COM_PHOCACART_REWARD_POINTS').$d['total'][5]['rewardproducttxtsuffix'].'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-reward-discount">'.$price->getPriceFormat($d['total'][5]['dnetto'], 1).'</div>';
		echo '</div>';// end row
	}

	// PRODUCT DISCOUNT

	if ($d['total'][2]['dnetto'] || $d['total'][2]['dbrutto']) {
		echo '<div class="'.$r.' ph-cart-product-discount-box ph-checkout-cart-row-discount-product-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-product-discount-txt">'.Text::_('COM_PHOCACART_PRODUCT_DISCOUNT').'</div>';
		if ($p['tax_calculation_sales'] == 2) {
			echo '<div class="' . $cTotB . ' ph-right ph-cart-product-discount">' . $price->getPriceFormat($d['total'][2]['dbrutto'], 1) . '</div>';
		} else {
			echo '<div class="' . $cTotB . ' ph-right ph-cart-product-discount">' . $price->getPriceFormat($d['total'][2]['dnetto'], 1) . '</div>';
		}
		echo '</div>';// end row
	}

	// CART DISCOUNT

	if ($d['total'][3]['dnetto'] || $d['total'][3]['dbrutto']) {
	    echo '<div class="'.$r.' ph-cart-discount-box ph-checkout-cart-row-discount-cart-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-cart-discount-txt">'.Text::_('COM_PHOCACART_CART_DISCOUNT').$d['total'][3]['discountcarttxtsuffix'].'</div>';
		if ($p['tax_calculation_sales'] == 2) {
			echo '<div class="'.$cTotB.' ph-right ph-cart-cart-discount">'.$price->getPriceFormat($d['total'][3]['dbrutto'], 1).'</div>';
		} else {
			echo '<div class="'.$cTotB.' ph-right ph-cart-cart-discount">'.$price->getPriceFormat($d['total'][3]['dnetto'], 1).'</div>';
		}

		echo '</div>';// end row
	}

	// COUPON

	if (($d['total'][4]['dnetto'] || $d['total'][4]['dbrutto']) && $d['couponvalid']) {
		$couponTitle = Text::_('COM_PHOCACART_COUPON');
		if (isset($d['coupontitle']) && $d['coupontitle'] != '') {
			$couponTitle = $d['coupontitle'];
		}
		echo '<div class="'.$r.' ph-cart-coupon-box ph-checkout-cart-row-discount-coupon-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-coupon-txt">'.$couponTitle.$d['total'][4]['couponcarttxtsuffix'].'</div>';
		if ($p['tax_calculation_sales'] == 2) {
			echo '<div class="' . $cTotB . ' ph-checkout-total-coupon ph-right ph-cart-coupon">' . $price->getPriceFormat($d['total'][4]['dbrutto'], 1) . '</div>';
		} else {
			echo '<div class="' . $cTotB . ' ph-checkout-total-coupon ph-right ph-cart-coupon">' . $price->getPriceFormat($d['total'][4]['dnetto'], 1) . '</div>';
		}
		echo '</div>';// end row
	}


	/*
	// SUBTOTAL AFTER DISCOUNTS
	if ($d['total'][0]['wdnetto']) {

		echo '<div class="'.$r.' ph-cart-subtotal-box">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-subtotal-netto-txt">'.Text::_('COM_PHOCACART_SUBTOTAL_AFTER_DISCOUNTS').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-subtotal-netto">'.$price->getPriceFormat($d['total'][0]['wdnetto']).'</div>';
		echo '</div>';// end row
	}
	*/

	// TAX
	if (!empty($d['total'][0]['tax'])) {
		foreach($d['total'][0]['tax'] as $k3 => $v3) {
			//if($v3['tax'] !== 0 && ($v3['tax'] != 0 xor (int)$displayZeroTax == 1) && (int)$p['tax_calculation'] != 0) {
			$v3['taxcalc'] = (int)$p['tax_calculation'];
			$displayPriceItems = PhocaCartPrice::displayPriceItems($v3, 'checkout');
				if ($displayPriceItems['tax'] == 1) {
				echo '<div class="' . $r . ' ph-cart-tax-box ph-checkout-cart-row-tax ph-checkout-cart-row-subtotal">';
				echo '<div class="' . $cTotE . '"></div>';
				echo '<div class="' . $cTotT . ' ph-cart-tax-txt">' . $v3['title'] . '</div>';
				echo '<div class="' . $cTotB . ' ph-checkout-total-amount ph-right ph-cart-tax">' . $price->getPriceFormat($v3['tax']) . '</div>';
				echo '</div>';// end row

			}
		}
	}




	// SHIPPING
	// Add Shipping costs if there are some
	if (!empty($d['shippingcosts'])) {
		$sC = $d['shippingcosts'];

		if ($p['zero_shipping_price_calculation'] == -1 && $sC['zero'] == 1) {
			// Hide completely
		} else 	if ($p['zero_shipping_price_calculation'] == 0 && $sC['zero'] == 1) {
			// Display blank price field
			echo '<div class="'.$r.' ph-cart-shipping-box ph-checkout-cart-row-shipping-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping"></div>';
			echo '</div>';// end row
		} else if ($p['zero_shipping_price_calculation'] == 2 && $sC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.' ph-cart-shipping-box ph-checkout-cart-row-shipping-box">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-shipping-txt">'.$sC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping">'.Text::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {

			$displayPriceItems = PhocaCartPrice::displayPriceItems($sC, 'checkoutshipping');
			if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt'])/* && $sC['nettotxt'] != '' can be empty */) {
				if ($displayPriceItems['netto'] == 1) {
					echo '<div class="' . $r . ' ph-cart-shipping-box ph-checkout-cart-row-shipping-box-netto ph-checkout-cart-row-subtotal">';
					echo '<div class="' . $cTotE . '"></div>';
					echo '<div class="' . $cTotT . ' ph-cart-shipping-netto-txt">' . $sC['title'] . PhocacartUtils::addSeparator($sC['nettotxt']) . '</div>';
					echo '<div class="' . $cTotB . ' ph-checkout-total-amount ph-right ph-cart-shipping-netto">' . $sC['nettoformat'] . '</div>';
					echo '</div>';// end row
				}
			}

			if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt'])/* && $sC['taxtxt'] != '' can be empty */) {
				if ($displayPriceItems['tax'] == 1) {
					echo '<div class="' . $r . ' ph-cart-shipping-box ph-checkout-cart-row-shipping-box-tax ph-checkout-cart-row-subtotal">';
					echo '<div class="' . $cTotE . '"></div>';
					echo '<div class="' . $cTotT . ' ph-cart-shipping-tax-txt">' . $sC['title'] . PhocacartUtils::addSeparator($sC['taxtxt']) . '</div>';
					echo '<div class="' . $cTotB . ' ph-checkout-total-amount ph-right ph-cart-shipping-tax">' . $sC['taxformat'] . '</div>';
					echo '</div>';// end row
				}
			}

			if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) /* && $sC['bruttotxt'] != '' - can be empty */) || $sC['freeshipping'] == 1) {

				echo '<div class="'.$r.' ph-cart-shipping-box ph-checkout-cart-row-shipping-box-brutto ph-checkout-cart-row-subtotal">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-shipping-brutto-txt">'.$sC['title']. PhocacartUtils::addSeparator($sC['bruttotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-shipping-brutto">'.$sC['bruttoformat'].'</div>';
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
			echo '<div class="'.$r.' ph-cart-payment-box ph-checkout-cart-row-payment-box ph-checkout-cart-row-subtotal">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment"></div>';
			echo '</div>';// end row
		} else if ($p['zero_payment_price_calculation'] == 2 && $pC['zero'] == 1) {
			// Display free text
			echo '<div class="'.$r.' ph-cart-payment-box ph-checkout-cart-row-payment-box ph-checkout-cart-row-subtotal">';
			echo '<div class="'.$cTotE.'"></div>';
			echo '<div class="'.$cTotT.' ph-cart-payment-txt">'.$pC['title'].'</div>';
			echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment">'.Text::_('COM_PHOCACART_FREE').'</div>';
			echo '</div>';// end row
		} else {

			$displayPriceItems = PhocaCartPrice::displayPriceItems($pC, 'checkoutpayment');
			if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt'])/* && $pC['nettotxt'] != '' can be empty */) {

				if ($displayPriceItems['netto'] == 1) {
					echo '<div class="' . $r . ' ph-cart-payment-box ph-checkout-cart-row-payment-box-netto ph-checkout-cart-row-subtotal">';
					echo '<div class="' . $cTotE . '"></div>';
					echo '<div class="' . $cTotT . ' ph-cart-payment-netto-txt">' . $pC['title'] . PhocacartUtils::addSeparator($pC['nettotxt']) . '</div>';
					echo '<div class="' . $cTotB . ' ph-checkout-total-amount ph-right ph-cart-payment-netto">' . $pC['nettoformat'] . '</div>';
					echo '</div>';// end row
				}
			}

			if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt'])/* && $pC['taxtxt'] != '' can be empty */) {
				if ($displayPriceItems['tax'] == 1) {
					echo '<div class="' . $r . ' ph-cart-payment-box ph-checkout-cart-row-payment-box-tax ph-checkout-cart-row-subtotal">';
					echo '<div class="' . $cTotE . '"></div>';
					echo '<div class="' . $cTotT . ' ph-cart-payment-tax-txt">' . $pC['title'] . PhocacartUtils::addSeparator($pC['taxtxt']) . '</div>';
					echo '<div class="' . $cTotB . ' ph-checkout-total-amount ph-right ph-cart-payment-tax">' . $pC['taxformat'] . '</div>';
					echo '</div>';// end row
				}
			}

			if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt'])/* && $pC['bruttotxt'] != '' can be empty */) || $pC['freepayment'] == 1) {

				echo '<div class="'.$r.' ph-cart-payment-box ph-checkout-cart-row-payment-box-brutto ph-checkout-cart-row-subtotal">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-payment-brutto-txt">'.$pC['title']. PhocacartUtils::addSeparator($pC['bruttotxt']).'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-payment-brutto">'.$pC['bruttoformat'].'</div>';
				echo '</div>';// end row
			}
		}
	}

	// Posible feature request
	// Sum all taxes together: Product + Shipping + Payment
	// Different rates will be of course not be added together

	/*
	// SUM OF ALL TAXES ON ONE LINE
	if (!empty($d['total'][0]['tax'])) {
		foreach($d['total'][0]['tax'] as $k3 => $v3) {
			if($v3['tax'] !== 0 && $v3['tax'] != 0 && $p['tax_calculation'] != 0) {
				$tax = $v3['tax'];
				// Add shipping method taxes to sum of all taxes
				if (isset($sC['taxkey']) && $sC['taxkey'] == $k3) {
					if (isset($sC['tax']) && $sC['tax'] > 0) {
						$tax = $tax + $sC['tax'];
					}

				}
				// Add payment method taxes to sum of all taxes
				if (isset($pC['taxkey']) && $pC['taxkey'] == $k3) {
					if (isset($pC['tax']) && $pC['tax'] > 0) {
						$tax = $tax + $pC['tax'];
					}

				}

				echo '<div class="'.$r.' ph-cart-tax-box">';
				echo '<div class="'.$cTotE.'"></div>';
				echo '<div class="'.$cTotT.' ph-cart-tax-txt">SUM OF ALL TAXES - '.$v3['title'].'</div>';
				echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-right ph-cart-tax">'.$price->getPriceFormat($tax).'</div>';
				echo '</div>';// end row
			}
		}
	}
	*/



	// ROUNDING | ROUNDING CURRENCY

	if ($d['total'][0]['rounding_currency'] !== 0) {

		echo '<div class="'.$r.' ph-cart-currency-box ph-checkout-cart-row-currency-rounding-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-rounding-currency-txt">'.Text::_('COM_PHOCACART_ROUNDING_CURRENCY').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-rounding-currency">'.$price->getPriceFormat($d['total'][0]['rounding_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	} else if ($d['total'][0]['rounding'] !== 0) {

		echo '<div class="'.$r.' ph-cart-currency-box ph-checkout-cart-row-currency-rounding-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-rounding-txt">'.Text::_('COM_PHOCACART_ROUNDING').'</div>';
		echo '<div class="'.$cTotB.' ph-right ph-cart-rounding">'.$price->getPriceFormat($d['total'][0]['rounding']).'</div>';
		echo '</div>';// end row
	}

	// BRUTTO (Because of rounding currency we need to display brutto in currency which is set)
	//if (!($price->roundPrice($d['total'][0]['brutto_currency']) > -0.01 && $price->roundPrice($d['total'][0]['brutto_currency'] < 0.01)) == 1) {
	if ($d['total'][0]['brutto_currency'] !== 0) {
		echo '<div class="'.$r.' ph-cart-currency-box ph-checkout-cart-row-currency-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-brutto-currency-txt">'.Text::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-cart-total ph-right ph-cart-brutto-currency">'.$price->getPriceFormat($d['total'][0]['brutto_currency'], 0, 1).'</div>';
		echo '</div>';// end row
	//} else if (!($price->roundPrice($d['total'][0]['brutto']) > -0.01 && $price->roundPrice($d['total'][0]['brutto'] < 0.01)) == 1) {
	} else if ($d['total'][0]['brutto'] !== 0 || ($d['total'][0]['brutto'] === 0 && $p['display_zero_total'] == 1)) {
		echo '<div class="'.$r.' ph-cart-total-box ph-checkout-cart-row-total-box ph-checkout-cart-row-subtotal">';
		echo '<div class="'.$cTotE.'"></div>';
		echo '<div class="'.$cTotT.' ph-cart-total-txt">'.Text::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="'.$cTotB.' ph-checkout-total-amount ph-cart-total ph-right">'.$price->getPriceFormat($d['total'][0]['brutto']).'</div>';
		echo '</div>';// end row

	}


	// ---
	// Tax Recapitulation Possible part to display TC

	// bdump(); comment;
	/*if(!empty($d['total'][0]['taxrecapitulation']['items'])) {

		echo '<table class="pc-tax-recapitulation">';

		echo '<tr><th>'.Text::_('COM_PHOCACART_TAX_TITLE').'</th><th>'.Text::_('COM_PHOCACART_TAX_BASIS').'</th><th>'.Text::_('COM_PHOCACART_TAX_TAX').'</th><th>'.Text::_('COM_PHOCACART_TAX_TOTAL').'</th></tr>';

		if ($d['total'][0]['brutto_currency'] !== 0) {

			foreach($d['total'][0]['taxrecapitulation']['items'] as $k => $v) {
				echo '<tr><td>'.$v['title'].'</td><td>'.$price->getPriceFormat($v['netto']).'</td><td>'.$price->getPriceFormat($v['tax']).'</td><td>'.$price->getPriceFormat($v['brutto_currency'], 0, 1).' '.'</td></tr>';
			}
			if ($d['total'][0]['taxrecapitulation']['rounding_currency'] > 0 && $d['total'][0]['taxrecapitulation']['corrected_currency'] == 1) {
				echo '<tr><td>'.Text::_('COM_PHOCACART_ROUNDING').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['taxrecapitulation']['rounding_currency'], 0, 1).'</td></tr>';
				echo '<tr><td>'.Text::_('COM_PHOCACART_TOTAL').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['brutto_currency'], 0, 1).'</td></tr>';
			}

		} else {


		$b = 0; $c = 0;
			foreach($d['total'][0]['taxrecapitulation']['items'] as $k => $v) {
				echo '<tr><td>'.$v['title'].'</td><td>'.$price->getPriceFormat($v['netto']).'</td><td>'.$price->getPriceFormat($v['tax']).'</td><td>'.$price->getPriceFormat($v['brutto']).' '.'</td></tr>';
				$b += $v['brutto'];
			}

			if (!($price->roundPrice($d['total'][0]['taxrecapitulation']['rounding']) > -0.01 && $price->roundPrice($d['total'][0]['taxrecapitulation']['rounding'] < 0.01)) == 1) {

				//echo '<tr><td>'.Text::_('COM_PHOCACART_ROUNDING').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['taxrecapitulation']['rounding_currency'], 0, 1).' '.'</td></tr>';
				//- *$price->getPriceFormat($d['total'][0]['taxrecapitulation']['rounding_currency'])*//*.
				$c = $d['total'][0]['taxrecapitulation']['rounding'];
			}
			//echo '<tr><td>'.Text::_('COM_PHOCACART_ROUNDING_B').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['brutto'] - $b).'</td></tr>';

			echo '<tr><td>'.Text::_('COM_PHOCACART_ROUNDING').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['taxrecapitulation']['rounding'] ).'</td></tr>';
			echo '<tr><td>'.Text::_('COM_PHOCACART_TOTAL').'</td><td colspan="3">'.$price->getPriceFormat($d['total'][0]['brutto']);

			//echo ' '.$price->getPriceFormat($d['total'][0]['brutto_currency']).' <b>'.$b.'</b> <b>'.($b + $c);
			echo '</b></td></tr>';

		}

		echo '</table>';
	}*/

	// END TAX RECAPITULATION
	// ---

	// Possible points received

	if ($p['display_reward_points_receive_info'] == 1 && isset($d['total'][0]['points_received']) && $d['total'][0]['points_received'] > 0) {

		echo '<div class="ph-ceckout-points-received">'.Text::_('COM_PHOCACART_POINTS_RECEIVED_FOR_THIS_PURCHASE').': ' .$d['total'][0]['points_received'].'</div>';
	}


	echo '</div>'. "\n"; // end checkout box
} else {

	if ($d['pos']) {
		echo '<div class="ph-cart-icon">'.PhocacartRenderIcon::icon($d['s']['i']['shopping-cart']) .'</div>';
	}
	echo '<div class="ph-cart-empty">'.Text::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY').'</div>';
}


?>
