<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$layoutC 	= new FileLayout('button_compare', null, array('component' => 'com_phocacart'));
$layoutW 	= new FileLayout('button_wishlist', null, array('component' => 'com_phocacart'));
$layoutQVB 	= new FileLayout('button_quickview', null, array('component' => 'com_phocacart'));
$layoutS	= new FileLayout('product_stock', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new FileLayout('product_order_quantity', null, array('component' => 'com_phocacart'));
$layoutR	= new FileLayout('product_rating', null, array('component' => 'com_phocacart'));
$layoutAI	= new FileLayout('button_add_to_cart_icon', null, array('component' => 'com_phocacart'));
$layoutIL	= new FileLayout('items_list', null, array('component' => 'com_phocacart'));
$layoutIGL	= new FileLayout('items_gridlist', null, array('component' => 'com_phocacart'));
$layoutIG	= new FileLayout('items_grid', null, array('component' => 'com_phocacart'));
$layoutAAQ	= new FileLayout('popup_container_iframe', null, array('component' => 'com_phocacart'));

// HEADER - NOT AJAX
if (!$this->t['ajax']) {
	echo '<div id="ph-pc-category-box" class="pc-view pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';
	echo $this->loadTemplate('header');
	echo $this->loadTemplate('pagination_top');
	echo '<div id="phItemsBox">';
}



// ITEMS a) items displayed by layout plugin, b) items displayed common way, c) no items found
if (!empty($this->items) && $this->t['pluginlayout']) {

	$pluginOptions = [];
	$dLA = [];

	Dispatcher::dispatch(new Event\Layout\Items\GetOptions('com_phocacart.items', $pluginOptions, [
		'pluginname' => $this->t['items_layout_plugin'],
	]));

	if (isset($pluginOptions['layouttype']) && $pluginOptions['layouttype'] != '') {
		$this->t['layouttype'] = PhocacartText::filterValue($pluginOptions['layouttype'], 'alphanumeric5');
	}

	$lt			= $this->t['layouttype'];
	$dLA['t'] 	= $this->t;
	$dLA['s'] 	= $this->s;

	echo '<div id="phItems" class="ph-items '.$lt.'">';

	Dispatcher::dispatch(new Event\Layout\Items\InsideLayout('com_phocacart.items', $this->items, $dLA, [
		'pluginname' => $this->t['items_layout_plugin'],
	]));

	echo $this->loadTemplate('pagination');

	echo '</div>'. "\n"; // end items

} else if (!empty($this->items)) {

	$price		= new PhocacartPrice;
	$col 		= PhocacartRenderFront::getColumnClass((int)$this->t['columns_cat']);
	$colMobile 	= PhocacartRenderFront::getColumnClass((int)$this->t['columns_cat_mobile']);

	$lt		= $this->t['layouttype'];
	$i		= 1; // Not equal Heights

	echo '<div id="phItems" class="ph-items '.$lt.'">';
	echo '<div class="'.PhocacartRenderFront::completeClass(array($this->s['c']['row'], $this->t['class_row_flex'], $this->t['class_lazyload'], $lt)).'">';

	foreach ($this->items as $v) {

		if (isset($v->taxhide)) {
			$registry = new Registry;
			$registry->loadString($v->taxhide);
			$v->taxhide = $registry->toArray();
		}

		// DIFF CATEGORY / ITEMS
		$this->t['categoryid'] = (int)$v->catid;

		$label 		= PhocacartRenderFront::getLabel($v->date, $v->sales, $v->featured);
		$link 		= Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));


		// Image data
		$attributesOptions 	= $this->t['hide_attributes_category'] == 0 ? PhocacartAttribute::getAttributesAndOptions((int)$v->id) : array();
		if (!isset($v->additional_image)) { $v->additional_image = '';}
		$image = PhocacartImage::getImageDisplay($v->image, $v->additional_image, $this->t['pathitem'], $this->t['switch_image_category_items'], $this->t['image_width_cat'], $this->t['image_height_cat'], '', $lt, $attributesOptions);


		// :L: IMAGE
		$dI	= array();
		if (isset($image['image']->rel) && $image['image']->rel != '') {
			$dI['t']				= $this->t;
			$dI['s']				= $this->s;
			$dI['product_id']		= (int)$v->id;
			$dI['layouttype']		= $lt;
            $dI['title']			= $v->title;
			$dI['image']			= $image;
			$dI['typeview']			= 'Items';
		}

		// :L: COMPARE
		$icon 				= array();
		$icon['compare'] 	= '';
		if ($this->t['display_compare'] == 1) {
			$d			= array();
			$d['s']		= $this->s;
			$d['linkc']	= $this->t['linkcomparison'];
			$d['id']	= (int)$v->id;
			$d['catid']	= $this->t['categoryid'];
			$d['return']= $this->t['actionbase64'];
			$d['method']= $this->t['add_compare_method'];
			$icon['compare'] = $layoutC->render($d);
		}

		// :L: WISHLIST
		$icon['wishlist'] = '';
		if ($this->t['display_wishlist'] == 1) {
			$d			= array();
			$d['s']		= $this->s;
			$d['linkw']	= $this->t['linkwishlist'];
			$d['id']	= (int)$v->id;
			$d['catid']	= $this->t['categoryid'];
			$d['return']= $this->t['actionbase64'];
			$d['method']= $this->t['add_wishlist_method'];
			$icon['wishlist'] = $layoutW->render($d);
		}

		// :L: QUICKVIEW
		$icon['quickview'] = '';
		if ($this->t['display_quickview'] == 1) {
			$d				= array();
			$d['s']			= $this->s;
			$d['linkqvb']	= Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
			$d['id']		= (int)$v->id;
			$d['catid']		= $this->t['categoryid'];
			$d['return']	= $this->t['actionbase64'];
			$icon['quickview'] = $layoutQVB->render($d);
		}

		// :L: PRICE
		$dP = array();
		$priceItems = array();
		if ($this->t['can_display_price']) {

			$dP['type'] = $v->type;// PRODUCTTYPE

			$priceItems	= $price->getPriceItems($v->price, $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, $v->unit_amount, $v->unit_unit, 1, 1, $v->group_price, $v->taxhide);

			$price->getPriceItemsChangedByAttributes($priceItems, $attributesOptions, $price, $v);
			$dP['priceitemsorig']= array();
			$dP['priceitems']	= $priceItems;

			if ($v->price_original != '' && $v->price_original > 0) {
				$dP['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype, '', 0, '', 0, 1, null, $v->taxhide);
			}
			//$dP['class']		= 'ph-category-price-box '.$lt;
			$dP['class']		= 'ph-category-price-box';// Cannot be dynamic as can change per ajax - this can cause jumping of boxes
			$dP['product_id']	= (int)$v->id;
			$dP['typeview']		= 'Items';
			$dP['subscription_scenario'] = isset($v->subscription_scenario) ? $v->subscription_scenario : null;

			// Display discount price
			// Move standard prices to new variable (product price -> product discount)
			$dP['priceitemsdiscount']		= $dP['priceitems'];
			$dP['discount'] 				= PhocacartDiscountProduct::getProductDiscountPrice($v->id, $dP['priceitemsdiscount']);

			// Display cart discount (global discount) in product views - under specific conditions only
			// Move product discount prices to new variable (product price -> product discount -> product discount cart)
			$dP['priceitemsdiscountcart']	= $dP['priceitemsdiscount'];
			$dP['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($v->id, $v->catid, $dP['priceitemsdiscountcart']);

			$dP['zero_price']		= 1;// Apply zero price if possible
		}


		// :L: LINK TO PRODUCT VIEW
		$dV = array();
		$dV['s'] = $this->s;
		$dV['display_view_product_button'] 	= $this->t['display_view_product_button'];
		if ((int)$this->t['display_view_product_button'] > 0) {
			$dV['link']							= $link;
			//$dV['display_view_product_button'] 	= $this->t['display_view_product_button'];
		}

		// :L: ADD TO CART
		$dA = $dA2 = $dA3 = $dA4 = $dAb = $dF = array();
		$icon['addtocart'] = '';

		// Different button or icons
		// Button can be hidden based on price This variable is used for displaying Ask Question
		$addToCartHidden = 0;// Design parameter - if there is no button (add to cart, paddle link, external link), used e.g. for displaying ask a question button

		// STOCK ===================================================
		// Set stock: product, variations, or advanced stock status
		$dSO 				= '';
		$dA['class_btn']	= '';
		$dA['class_icon']	= '';
		$dA['s']	        = $this->s;
		if ($this->t['display_stock_status'] == 2 || $this->t['display_stock_status'] == 3) {

			$stockStatus 				= array();
			$stock 						= PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $attributesOptions, $v);

			if ($this->t['hide_add_to_cart_stock'] == 1 && (int)$stock < 1) {
				$dA['class_btn'] 		= 'ph-visibility-hidden';// hide button
				$dA['class_icon']		= 'ph-display-none';// hide icon
				$addToCartHidden 			= 1;// used for displaying Ask Question
			}

			if($stockStatus['stock_status'] || $stockStatus['stock_count'] !== false) {
				$dS							= array();
				$dS['s']	                = $this->s;
				$dS['class']				= 'ph-category-stock-box';
				$dS['product_id']			= (int)$v->id;
				$dS['typeview']				= 'Category';
				$dS['stock_status_class']	= isset($stockStatus['stock_status_class']) ? $stockStatus['stock_status_class'] : '';
				$dS['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($stockStatus);
				$dSO = $layoutS->render($dS);
			}

			if($stockStatus['min_quantity']) {
				$dPOQ						= array();
				$dPOQ['s']	                = $this->s;
				$dPOQ['text']				= Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY');
				$dPOQ['status']				= $stockStatus['min_quantity'];
				$dSO .= $layoutPOQ->render($dPOQ);
			}

			if($stockStatus['min_multiple_quantity']) {
				$dPOQ						= array();
				$dPOQ['s']	                = $this->s;
				$dPOQ['text']				= Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY');
				$dPOQ['status']				= $stockStatus['min_multiple_quantity'];
				$dSO .= $layoutPOQ->render($dPOQ);
			}

			if($stockStatus['max_quantity']) {
				$dPOQ						= array();
				$dPOQ['s']	                = $this->s;
				$dPOQ['text']				= Text::_('COM_PHOCACART_MAXIMUM_ORDER_QUANTITY');
				$dPOQ['status']				= $stockStatus['max_quantity'];
				$dSO .= $layoutPOQ->render($dPOQ);
			}
		}
		// END STOCK ================================================


		// ------------------------------------
		// BUTTONS + ICONS
		// ------------------------------------
		// Prepare data for Add to cart button
		// - Add To Cart Standard Button
		// - Add to Cart Icon Button
		// - Add to Cart Icon Only
		if ((int)$this->t['category_addtocart'] == 1 || (int)$this->t['category_addtocart'] == 4 || $this->t['display_addtocart_icon'] == 1) {

			// FORM DATA
            $dF['s']	                = $this->s;
			$dF['linkch']				= $this->t['linkcheckout'];// link to checkout (add to cart)
			$dF['id']					= (int)$v->id;
			$dF['sku']					= isset($v->sku) ? $v->sku : '';
			$dF['ean']					= isset($v->ean) ? $v->ean : '';
			$dF['basepricenetto']       = isset($dP['priceitems']['nettocurrency']) ? $dP['priceitems']['nettocurrency'] : '';
            $dF['basepricetax']         = isset($dP['priceitems']['taxcurrency']) ? $dP['priceitems']['taxcurrency'] : '';
            $dF['basepricebrutto']      = isset($dP['priceitems']['bruttocurrency']) ? $dP['priceitems']['bruttocurrency'] : '';
            $dF['title']				= isset($v->title) ? $v->title : '';
			$dF['catid']				= $this->t['categoryid'];
			$dF['return']				= $this->t['actionbase64'];
			$dF['typeview']				= 'Items';
			$dA['addtocart']			= $this->t['category_addtocart'];
			$dA['addtocart_icon']		= $this->t['display_addtocart_icon'];

			// Both buttons + icon
			$dA['s']					= $this->s;
			$dA['id']					= (int)$v->id;
			$dA['link']					= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
			$dA['addtocart']			= $this->t['category_addtocart'];
			$dA['method']				= $this->t['add_cart_method'];
			$dA['typeview']				= 'Items';

			// ATTRIBUTES, OPTIONS
			$dAb['s']						= $this->s;
			$dAb['attr_options']			= $attributesOptions;
			$dAb['hide_attributes']			= $this->t['hide_attributes_category'];
			$dAb['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
			$dAb['remove_select_option_attribute']	= $this->t['remove_select_option_attribute'];
			$dAb['zero_attribute_price']	= $this->t['zero_attribute_price'];
			$dAb['stock_calculation']		= (int)$v->stock_calculation;
			$dAb['pathitem']				= $this->t['pathitem'];

			$dAb['product_id']				= (int)$v->id;
			$dAb['gift_types']				= $v->gift_types;
			$dAb['image_size']				= $image['size'];
			$dAb['typeview']				= 'Items';
			$dAb['price']					= $price;
			$dAb['priceitems']				= $priceItems;

			// Attribute is required and we don't display it in category/items view, se we need to redirect to detail view
			$dA['selectoptions']	= 0;
			if (isset($v->attribute_required) && $v->attribute_required == 1 && $this->t['hide_attributes_category'] == 1) {
				$dA['selectoptions']	= 1;
			}

			// Add To Cart as Icon
			if ($this->t['display_addtocart_icon'] == 1) {
				$icon['addtocart'] 	= $layoutAI->render($dA);

			}
		}


		// Type 3 is Product Price on Demand - there is no add to cart button except Quick View Button
		if ($v->type == 3 && (int)$this->t['category_addtocart'] != 104) {
			// PRODUCTTYPE - price on demand price cannot be added to cart
			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon except Quick View Button
			$dF = array();// Skip form
			$addToCartHidden = 1;
		} else if ($this->t['hide_add_to_cart_zero_price'] == 1 && $v->price == 0) {
			// Don't display Add to Cart in case the price is zero
			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form
			$addToCartHidden = 1;
		} else if ((int)$this->t['category_addtocart'] == 1 || (int)$this->t['category_addtocart'] == 4) {
			// ADD TO CART BUTTONS - we have data yet
		} else if ((int)$this->t['category_addtocart'] == 102 && (int)$v->external_id != '') {
			// EXTERNAL LINK PADDLE
			$dA2['t']				= $this->t;
			$dA2['s']				= $this->s;
			$dA2['external_id']		= (int)$v->external_id;
			$dA2['return']			= $this->t['actionbase64'];

			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form

		} else if ((int)$this->t['category_addtocart'] == 103 && $v->external_link != '') {
			// EXTERNAL LINK
			$dA3['t']				= $this->t;
			$dA3['s']				= $this->s;
			$dA3['external_link']	= $v->external_link;
			$dA3['external_text']	= $v->external_text;
			$dA3['return']			= $this->t['actionbase64'];

			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form

		} else if ((int)$this->t['category_addtocart'] == 104) {
			// QUICK VIEW
			$dA4				= array();
			$dA4['s']			= $this->s;
			$dA4['linkqvb']		= Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
			$dA4['id']			= (int)$v->id;
			$dA4['catid']		= $this->t['categoryid'];
			$dA4['return']		= $this->t['actionbase64'];
			$dA4['button'] 		= 1;

			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form

		} else {
			// ADD TO CART ICON ONLY (NO BUTTONS)
			$dA = array(); // Skip Standard Add to cart button
			// We remove the $dA completely, even for the icon, but the icon has the data already stored in $icon['addtocart']
			// so no problem with removing the data completely
			// $dA for button will be rendered
			// $dA for icon was rendered already
			// Do not skip the form here
			$addToCartHidden = 1;
		}
		// ---------------------------- END BUTTONS

		$dQ	= array();
		if (((int)$this->t['category_askquestion'] == 1) || ($this->t['category_askquestion'] == 2 && ((int)$this->t['category_addtocart'] == 0 || $addToCartHidden != 0))) {

			$dQ['s']			= $this->s;
			$dQ['id']			= (int)$v->id;
			$dQ['catid']		= $this->t['categoryid'];
			$dQ['popup']		= 0;
			$tmpl				= '';
			if ((int)$this->t['popup_askquestion'] > 0) {
				$dQ['popup']		= (int)$this->t['popup_askquestion'];
				$popupAskAQuestion	= (int)$this->t['popup_askquestion'];
				$tmpl				= 'tmpl=component';
			}
			$dQ['link']			=  Route::_(PhocacartRoute::getQuestionRoute($v->id, $v->catid, $v->alias, $v->catalias, $tmpl));
			$dQ['return']		= $this->t['actionbase64'];
		}


		// ======
		// RENDER
		// ======
		$dL 					= array();
		$dL['t']				= $this->t;
		$dL['s']				= $this->s;
		$dL['col']				= $col;
		$dL['col_mobile']		= $colMobile;
		$dL['link'] 			= $link;
		$dL['lt']				= $lt;// Layout Type
		$dL['layout']['dI']		= $dI;// Image
		$dL['layout']['dP']		= $dP;// Price
		$dL['layout']['dSO']	= $dSO;// Stock Output
		$dL['layout']['dF']		= $dF;// Form
		$dL['layout']['dAb']	= $dAb;// Attributes
		$dL['layout']['dV']		= $dV;// Link to Product View
		$dL['layout']['dA']		= $dA;// Button Add to Cart
		$dL['layout']['dA2']	= $dA2;// Button Buy now
		$dL['layout']['dA3']	= $dA3;// Button external link
		$dL['layout']['dA4']	= $dA4;// Button external link
		$dL['layout']['dQ']		= $dQ;// Ask A Question


		$dL['icon']				= $icon;// Icons
		// Additional class
		$classAdditional = [];
		if (PhocacartRenderFront::renderNewIcon($v->date, 1, 1)) {
			$classAdditional[] = 'pc-status-new';
		}
		if (PhocacartRenderFront::renderHotIcon($v->sales, 1, 1)) {
			$classAdditional[] = 'pc-status-hot';
		}
		if (PhocacartRenderFront::renderFeaturedIcon($v->featured, 1, 1)) {
			$classAdditional[] = 'pc-status-featured';
		}

		if (isset($dP['discount']) && $dP['discount']) {
            $classAdditional[] = 'pc-status-discount-product';
        }

        if (isset($dP['discountcart']) && $dP['discountcart']) {
            $classAdditional[] = 'pc-status-discount-cart';
        }

		$dL['class_additional'] = !empty($classAdditional) ? implode(' ', $classAdditional) : '';// Additional class
		$dL['product_header']	= PhocacartRenderFront::renderProductHeader($this->t['product_name_link'], $v, 'item', '', $lt);
        $dL['item']             = $v;
		//$dL['product_header'] .= '<div>SKU: '.$v->sku.'</div>';
		//$dL['product_header'] .= '<div>EAN: '.$v->ean.'</div>';

		// Events
		$results = Dispatcher::dispatch(new Event\View\Items\ItemAfterAddToCart('com_phocacart.items', $v, $this->p));
		$dL['event']['onCategoryItemsItemAfterAddToCart'] = trim(implode("\n", $results));

		// LABELS
		$dL['labels'] =  $label['new'] . $label['hot'] . $label['feat'];
		$tagLabelsOutput = PhocacartTag::getTagsRendered((int)$v->id, $this->t['category_display_labels']);
		if ($tagLabelsOutput != '') {
			$dL['labels'] .= $tagLabelsOutput;
		}

		// REVIEW - STAR RATING
		$dL['review'] = '';
		if ((int)$this->t['display_star_rating'] > 0) {
			$d							= array();
			$d['s']	                    = $this->s;
			$d['rating']				= isset($v->rating) && (int)$v->rating > 0 ? (int)$v->rating : 0;
			$d['size']					= 16;
			$d['display_star_rating']	= (int)$this->t['display_star_rating'];
			$dL['review'] = $layoutR->render($d);
		}

		// DESCRIPTION
		$dL['description'] = '';
		if ($this->t['cv_display_description'] == 1 && $v->description != '') {
			$dL['description'] = '<div class="ph-item-desc">' . HTMLHelper::_('content.prepare', $v->description) . '</div>';
		}

		// TAGS
		$dL['tags'] =  '';
		$tagsOutput = PhocacartTag::getTagsRendered((int)$v->id, $this->t['category_display_tags'], ', ');
		if ($tagsOutput != '') {
			$dL['tags'] .= $tagsOutput;
		}

		// MANUFACTURER
		$dL['manufacturer'] =  '';
		if ($this->t['category_display_manufacturer'] > 0 && (int)$v->manufacturerid > 0 && $v->manufacturertitle != '') {
			$dL['manufacturer'] .= PhocacartManufacturer::getManufacturerRendered((int)$v->manufacturerid, $v->manufacturertitle, $v->manufactureralias, $this->t['manufacturer_alias'], $this->t['category_display_manufacturer'], 0, '');
		}

		if ($lt == 'list') {
			echo $layoutIL->render($dL);
		} else if ( $lt == 'gridlist') {
			echo $layoutIGL->render($dL);
		} else  {
			echo $layoutIG->render($dL);
		}
		// --------------- END RENDER





		if ($i%(int)$this->t['columns_cat'] == 0) {
			echo '<div class="ph-cb '.$lt.'"></div>';
		}
		$i++;
	}

	echo '</div>';// end row (row-flex)
	echo '<div class="ph-cb '.$lt.'"></div>';

	echo $this->loadTemplate('pagination');

	echo '</div>'. "\n"; // end items
} else {

	echo '<div class="ph-no-items-found">'.Text::_('COM_PHOCACART_NO_ITEMS_FOUND').'</div>';
}


// FOOTER - NOT AJAX
if (!$this->t['ajax']) {

	echo '</div>';// end #phItemsBox
	echo '</div>';// end #ph-pc-category-box

	echo '<div id="phContainer"></div>';
	if (isset($popupAskAQuestion) && $popupAskAQuestion == 2) {

		echo '<div id="phContainerPopup">';
		$d						= array();
		$d['id']				= 'phAskAQuestionPopup';
		$d['title']				= Text::_('COM_PHOCACART_ASK_A_QUESTION');
		$d['icon']				= $this->s['i']['question-sign'];
		$d['t']					= $this->t;
		$d['s']					= $this->s;
		echo $layoutAAQ->render($d);
		echo '</div>';// end phContainerPopup
	}
	echo '<div>&nbsp;</div>';
	echo PhocacartUtilsInfo::getInfo();
}
?>
