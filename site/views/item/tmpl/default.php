<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutC 	= new JLayoutFile('button_compare', null, array('component' => 'com_phocacart'));
$layoutW 	= new JLayoutFile('button_wishlist', null, array('component' => 'com_phocacart'));
$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
$layoutS	= new JLayoutFile('product_stock', null, array('component' => 'com_phocacart'));
$layoutA	= new JLayoutFile('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new JLayoutFile('button_external_link', null, array('component' => 'com_phocacart'));
$layoutQ	= new JLayoutFile('button_ask_question', null, array('component' => 'com_phocacart'));
$layoutPD	= new JLayoutFile('button_public_download', null, array('component' => 'com_phocacart'));
$layoutEL	= new JLayoutFile('link_external_link', null, array('component' => 'com_phocacart'));
$layoutAB	= new JLayoutFile('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new JLayoutFile('product_order_quantity', null, array('component' => 'com_phocacart'));
$layoutSZ	= new JLayoutFile('product_size', null, array('component' => 'com_phocacart'));
$layoutI	= new JLayoutFile('image', null, array('component' => 'com_phocacart'));

echo '<div id="ph-pc-item-box" class="pc-item-view'.$this->p->get( 'pageclass_sfx' ).'">';


if (isset($this->category[0]->id) && ($this->t['display_back'] == 2 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->id > 0) {
		$linkUp = JRoute::_(PhocacartRoute::getCategoryRoute($this->category[0]->id, $this->category[0]->alias));
		$linkUpText = $this->category[0]->title;
	} else {
		$linkUp 	= false;
		$linkUpText = false;
	}

	if ($linkUp && $linkUpText) {
		echo '<div class="ph-top">'
		.'<a class="'.$this->s['c']['btn.btn-success'].'" title="'.$linkUpText.'" href="'. $linkUp.'" >'
        .'<span class="'.$this->s['i']['back-category'].'"></span> '.JText::_($linkUpText).'</a>'
        .'</div>';
	}
}

echo $this->t['event']->onItemBeforeHeader;


$x = $this->item[0];
if (!empty($x) && isset($x->id) && (int)$x->id > 0) {


	$idName			= 'VItemP'.(int)$x->id;
	echo '<div class="'.$this->s['c']['row'].'">';

	// === IMAGE PANEL
	echo '<div id="phImageBox" class="'.$this->s['c']['col.xs12.sm5.md5'] .'">';

	$label 	= PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);


	// IMAGE
	$image 		= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image
	$imageL 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image Link to enlarge


	// Some of the attribute is selected - this attribute include image so the image should be displayed instead of default
	$imageA = PhocaCartImage::getImageChangedByAttributes($this->t['attr_options'], 'large');
	if ($imageA != '') {
		$image = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
		$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
	}

	$link 	= JURI::base(true).'/'.$imageL->rel;
	if($this->t['display_webp_images'] == 1) {
		$link 	= JURI::base(true).'/'.$imageL->rel_webp;
	}


	if (isset($image->rel) && $image->rel != '') {

        $altValue   = PhocaCartImage::getAltTitle($x->title, $image->rel);

	    echo '<div class="ph-item-image-full-box '.$label['cssthumbnail'].'">';
		echo '<div class="ph-label-box">';
		echo $label['new'] . $label['hot'] . $label['feat'];
		if ($this->t['taglabels_output'] != '') {
			echo $this->t['taglabels_output'];
		}
		echo '</div>';

		echo '<a href="'.$link.'" '.$this->t['image_rel'].' class="'.$this->t['image_class'].' phjProductHref'.$idName.'" data-href="'.$link.'">';

		$d						= array();
		$d['t']					= $this->t;
		$d['s']					= $this->s;
		$d['src']				= JURI::base(true).'/'.$image->rel;
		$d['srcset-webp']		= JURI::base(true).'/'.$image->rel_webp;
		$d['data-image']		= JURI::base(true).'/'.$image->rel;
		$d['data-image-webp']	= JURI::base(true).'/'.$image->rel_webp;
		$d['alt-value']			= PhocaCartImage::getAltTitle($x->title, $image->rel);
		$d['class']				= PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'] , $label['cssthumbnail2'], 'ph-image-full', 'phjProductImage'.$idName));
		$d['style']				= '';
		if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
			$d['style'] = 'width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px';
		}
		echo $layoutI->render($d);

		echo '</a>';

		echo '</div>'. "\n";// end item_row_item_box_full_image
	}


	// ADDITIONAL IMAGES
	if (!empty($this->t['add_images'])) {

		echo '<div class="'.$this->s['c']['row'].' ph-item-image-add-box">';

		foreach ($this->t['add_images'] as $v2) {
			echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-item-image-box">';
			$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'small');
			$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'large');
			$link 	= JURI::base(true).'/'.$imageL->rel;
			if ($this->t['display_webp_images'] == 1) {
				$link 	= JURI::base(true).'/'.$imageL->rel_webp;
			}

            $altValue   = PhocaCartImage::getAltTitle($x->title, $v2->image);

			echo '<a href="'.$link.'" '.$this->t['image_rel'].'  class="'.$this->t['image_class'].'">';

			$d						= array();
			$d['t']					= $this->t;
			$d['s']					= $this->s;
			$d['src']				= JURI::base(true).'/'.$image->rel;
			$d['srcset-webp']		= JURI::base(true).'/'.$image->rel_webp;
			$d['alt-value']			= PhocaCartImage::getAltTitle($x->title, $v2->image);
			$d['class']				= PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'], $label['cssthumbnail2'], 'ph-image-full' /*, 'phjProductImage'.$idName*/));
			echo $layoutI->render($d);

			echo '</a>';
			echo '</div>';
		}

		echo '</div>';// end additional images
	}

	echo '</div>';// end item_row_item_c1

	// === PRICE PANEL
	echo '<div class="'.$this->s['c']['col.xs12.sm7.md7'].'">';
	echo '<div class="ph-item-price-panel">';

	$title = '';
	if (isset($this->item[0]->title) && $this->item[0]->title != '') {
		$title = $this->item[0]->title;
	}


	echo PhocacartRenderFront::renderHeader(array($title));



	// :L: PRICE
	$price 				= new PhocacartPrice;// Can be used by options
	if ($this->t['can_display_price']) {

		$d					= array();
		$d['s']				= $this->s;
		$d['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1, 1, $x->group_price);

		$price->getPriceItemsChangedByAttributes($d['priceitems'], $this->t['attr_options'], $price, $x);

		$d['priceitemsorig']= array();
		if ($x->price_original != '' && $x->price_original > 0) {
			$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype);
		}
		$d['class']			= 'ph-item-price-box';
		$d['product_id']	= (int)$x->id;
		$d['typeview']		= 'Item';

		// Display discount price
		// Move standard prices to new variable (product price -> product discount)
		$d['priceitemsdiscount']		= $d['priceitems'];
		$d['discount'] 					= PhocacartDiscountProduct::getProductDiscountPrice($x->id, $d['priceitemsdiscount']);

		// Display cart discount (global discount) in product views - under specific conditions only
		// Move product discount prices to new variable (product price -> product discount -> product discount cart)
		$d['priceitemsdiscountcart']	= $d['priceitemsdiscount'];
		$d['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($x->id, $x->catid, $d['priceitemsdiscountcart']);

		$d['zero_price']		= 1;// Apply zero price if possible
		echo $layoutP->render($d);
	}

	if ( isset($this->item[0]->description) && $this->item[0]->description != '') {
		echo '<div class="ph-desc">'. JHtml::_('content.prepare', $this->item[0]->description). '</div>';
	}
	// REWARD POINTS - NEEDED
	$pointsN = PhocacartReward::getPoints($x->points_needed, 'needed');
	if ($pointsN) {
		echo '<div class="ph-item-reward-box">';
		echo '<div class="ph-reward-txt">'.JText::_('COM_PHOCACART_PRICE_IN_REWARD_POINTS').'</div>';

		echo '<div class="ph-reward">'.$pointsN.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}

	// REWARD POINTS - RECEIVED
	$pointsR = PhocacartReward::getPoints($x->points_received, 'received', $x->group_points_received);
	if ($pointsR) {
		echo '<div class="ph-item-reward-box">';
		echo '<div class="ph-reward-txt">'.JText::_('COM_PHOCACART_REWARD_POINTS').'</div>';

		echo '<div class="ph-reward">'.$pointsR.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}


	if (isset($x->manufacturertitle) && $x->manufacturertitle != '') {
		echo '<div class="ph-item-manufacturer-box">';
		echo '<div class="ph-manufacturer-txt">'.JText::_('COM_PHOCACART_MANUFACTURER').':</div>';
		echo '<div class="ph-manufacturer">';
		echo PhocacartRenderFront::displayLink($x->manufacturertitle, $x->manufacturerlink);
		echo '</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}



	// STOCK ===================================================
	// Set stock: product, variations, or advanced stock status
	$class_btn	= '';
	$class_icon	= '';

	if ($this->t['display_stock_status'] == 1 || $this->t['display_stock_status'] == 3) {
		$stock = PhocacartStock::getStockItemsChangedByAttributes($this->t['stock_status'], $this->t['attr_options'], $x);

		if ($this->t['hide_add_to_cart_stock'] == 1 && (int)$stock < 1) {
			$class_btn 					= 'ph-visibility-hidden';
			$class_icon					= 'ph-display-none';
		}

		if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count'] !== false) {
			$d							= array();
			$d['s']						= $this->s;
			$d['class']					= 'ph-item-stock-box';
			$d['product_id']			= (int)$x->id;
			$d['typeview']				= 'Item';
			$d['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($this->t['stock_status']);

			echo $layoutS->render($d);
		}

		if($this->t['stock_status']['min_quantity']) {
			$dPOQ						= array();
			$dPOQ['s']					= $this->s;
			$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY');
			$dPOQ['status']				= $this->t['stock_status']['min_quantity'];
			echo $layoutPOQ->render($dPOQ);
		}

		if($this->t['stock_status']['min_multiple_quantity']) {
			$dPOQ						= array();
			$dPOQ['s']					= $this->s;
			$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY');
			$dPOQ['status']				= $this->t['stock_status']['min_multiple_quantity'];
			echo $layoutPOQ->render($dPOQ);
		}
	}

	if ((int)$this->t['item_display_delivery_date'] > 0 && $x->delivery_date != '' && $x->delivery_date != '0000-00-00 00:00:00') {

		echo '<div class="ph-item-delivery-date-box">';
		echo '<div class="ph-delivery-date-txt">'.JText::_('COM_PHOCACART_DELIVERY_DATE').':</div>';
		echo '<div class="ph-delivery-date">';
		echo JHtml::date($x->delivery_date, 'DATE_FORMAT_LC3');
		echo '</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}

	// END STOCK ================================================



	// SIZE OPTIONS =============================================
	if ((int)$this->t['item_display_size_options'] > 0){

		$dSZ						= array();
		$dSZ['s']					= $this->s;
		$dSZ['length']				= $x->length;
		$dSZ['width']				= $x->width;
		$dSZ['height']				= $x->height;
		$dSZ['weight']				= $x->weight;
		$dSZ['volume']				= $x->volume;
		$dSZ['unit_amount']			= $x->unit_amount;
		$dSZ['unit_unit']			= $x->unit_unit;
		echo $layoutSZ->render($dSZ);
	}
	// END SIZE OPTIONS =========================================

	// This form can get two events:
	// when option selected - price or image is changed id=phItemPriceBoxForm
	// when ajax cart is active and submit button is clicked class=phItemCartBoxForm

	echo '<form 
	id="phCartAddToCartButton'.(int)$x->id.'"
	class="phItemCartBoxForm phjAddToCart phjItem phjAddToCartVItemP'.(int)$x->id.' form-inline" 
	action="'.$this->t['linkcheckout'].'" method="post">';

	// ATTRIBUTES, OPTIONS

	$d							= array();
	$d['s']						= $this->s;
	$d['attr_options']			= $this->t['attr_options'];
	$d['hide_attributes']		= $this->t['hide_attributes_item'];
	$d['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
	$d['zero_attribute_price']	= $this->t['zero_attribute_price'];
	$d['pathitem']				= $this->t['pathitem'];
	$d['init_type']				= 0;
	$d['price']					= $price;
	$d['product_id']			= (int)$x->id;
	$d['image_size']			= 'large';
	$d['typeview']				= 'Item';
	echo $layoutAB->render($d);


	// :L: ADD TO CART
	$addToCartHidden = 0;// Button can be hidden based on price
	if ($this->t['hide_add_to_cart_zero_price'] == 1 && $x->price == 0) {
		// Don't display Add to Cart in case the price is zero
		$addToCartHidden = 1;
	} else if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {

		$d					= array();
		$d['s']				= $this->s;
		$d['id']			= (int)$x->id;
		$d['catid']			= $this->t['catid'];
		$d['return']		= $this->t['actionbase64'];
		$d['addtocart']		= $this->t['item_addtocart'];
		$d['typeview']		= 'Item';
		$d['class_btn']		= $class_btn;
		$d['class_icon']	= $class_icon;
		echo $layoutA->render($d);

	} else if ((int)$this->t['item_addtocart'] == 102 && (int)$x->external_id != '') {
		$d					= array();
		$d['s']				= $this->s;
		$d['external_id']	= (int)$x->external_id;
		$d['return']		= $this->t['actionbase64'];

		echo $layoutA2->render($d);
	} else if ((int)$this->t['item_addtocart'] == 103 && $x->external_link != '') {
		$d					= array();
		$d['s']				= $this->s;
		$d['external_link']	= $x->external_link;
		$d['external_text']	= $x->external_text;
		$d['return']		= $this->t['actionbase64'];
		echo $layoutA3->render($d);

	}


	echo '</form>';
	echo '<div class="ph-cb"></div>';


	echo $this->t['event']->onItemAfterAddToCart;
	echo '<div class="ph-top-space"></div>';

	if (isset($x->sku) && $x->sku != '') {
		echo '<div class="ph-item-sku-box">';
		echo '<div class="ph-sku-txt">'.JText::_('COM_PHOCACART_SKU').':</div>';
		echo '<div class="ph-sku">'.$x->sku.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->upc) && $x->upc != '') {
		echo '<div class="ph-item-upc-box">';
		echo '<div class="ph-upc-txt">'.JText::_('COM_PHOCACART_UPC').':</div>';
		echo '<div class="ph-upc">'.$x->upc.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->ean) && $x->ean != '') {
		echo '<div class="ph-item-ean-box">';
		echo '<div class="ph-ean-txt">'.JText::_('COM_PHOCACART_EAN').':</div>';
		echo '<div class="ph-ean">'.$x->ean.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->jan) && $x->jan != '') {
		echo '<div class="ph-item-jan-box">';
		echo '<div class="ph-jan-txt">'.JText::_('COM_PHOCACART_JAN').':</div>';
		echo '<div class="ph-jan">'.$x->jan.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->isbn) && $x->isbn != '') {
		echo '<div class="ph-item-isbn-box">';
		echo '<div class="ph-isbn-txt">'.JText::_('COM_PHOCACART_ISBN').':</div>';
		echo '<div class="ph-isbn">'.$x->isbn.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->mpn) && $x->mpn != '') {
		echo '<div class="ph-item-mpn-box">';
		echo '<div class="ph-mpn-txt">'.JText::_('COM_PHOCACART_MPN').':</div>';
		echo '<div class="ph-mpn">'.$x->mpn.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	if (isset($x->serial_number) && $x->serial_number != '') {
		echo '<div class="ph-item-serial-number-box">';
		echo '<div class="ph-serial-number-txt">'.JText::_('COM_PHOCACART_SERIAL_NUMBER').':</div>';
		echo '<div class="ph-serial-number">'.$x->serial_number.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}

	if (((int)$this->t['item_askquestion'] == 1) || ($this->t['item_askquestion'] == 2 && ((int)$this->t['item_addtocart'] != 0 || $addToCartHidden != 0))) {

		$d					= array();
		$d['s']				= $this->s;
		$d['id']			= (int)$x->id;
		$d['catid']			= $this->t['catid'];
		$d['popup']			= 0;
		$tmpl				= '';
		if ($this->t['popup_askquestion'] == 1) {
			$d['popup']			= 1;
			$tmpl				= 'tmpl=component';
		}
		$d['link']			=  JRoute::_(PhocacartRoute::getQuestionRoute($x->id, $x->catid, $x->alias, $x->catalias, $tmpl));
		$d['return']		= $this->t['actionbase64'];
		echo $layoutQ->render($d);
		echo '<div class="ph-cb"></div>';
	}

	// TAGS
	if ($this->t['tags_output'] != '') {
		echo '<div class="ph-item-tag-box">';
		echo $this->t['tags_output'];
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}

	if ($this->t['display_compare'] == 1 || $this->t['display_wishlist'] == 1) {
		//echo '<div class="ph-cb"></div>';
	}

	// :L: COMPARE
	if ($this->t['display_compare'] == 1) {
		$d			= array();
		$d['s']		= $this->s;
		$d['linkc']	= $this->t['linkcomparison'];
		$d['id']	= (int)$x->id;
		$d['catid']	= (int)$x->catid;
		$d['return']= $this->t['actionbase64'];
		$d['method']= $this->t['add_compare_method'];
		echo $layoutC->render($d);
	}

	// :L: WISHLIST
	if ($this->t['display_wishlist'] == 1) {
		$d			= array();
		$d['s']		= $this->s;
		$d['linkw']	= $this->t['linkwishlist'];
		$d['id']	= (int)$x->id;
		$d['catid']	= (int)$x->catid;
		$d['return']= $this->t['actionbase64'];
		$d['method']= $this->t['add_wishlist_method'];
		echo $layoutW->render($d);
	}

	echo '<div class="ph-cb"></div>';

	// :L: PUBLIC DOWNLOAD
	if ($this->t['display_public_download'] == 1 && $x->public_download_file != '') {
		$d					= array();
		$d['s']				= $this->s;
		$d['linkdownload']	= $this->t['linkdownload'];
		$d['id']			= (int)$x->id;
		$d['return']		= $this->t['actionbase64'];
		$d['title']			= '';
		if ($x->public_download_text != '') {
			$d['title']		= $x->public_download_text;
		}

		echo '<div class="ph-cb"></div>';
		echo $layoutPD->render($d);
	}

	// :L: EXTERNAL LINK
	if ($this->t['display_external_link'] == 1 && $x->external_link != '') {
		$d					= array();
		$d['s']				= $this->s;
		$d['linkexternal']	= $x->external_link;
		//$d['id']			= (int)$x->id;
		//$d['return']		= $this->t['actionbase64'];
		$d['title']			= '';
		if ($x->external_text != '') {
			$d['title']		= $x->external_text;
		}

		echo '<div class="ph-cb"></div>';
		echo $layoutEL->render($d);
	}

	echo $this->t['event']->onItemBeforeEndPricePanel;


	echo '</div>';// end item_row_item_box_price
	echo '</div>';// end item_row_item_c2

	echo '</div>';// end item_row

	echo '<div class="ph-item-bottom-box">';



	// TABS
	$active 		= $this->s['c']['tabactive'];
	$activeTab 		= $this->s['c']['tabactvietab'];// Not displayed in Bootstrap4
	$tabO	= '';
	$tabLiO	= '';

	// DESCRIPTION
	if (isset($x->description_long) && $x->description_long != '') {
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phdescription" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_DESCRIPTION').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phdescription">';
		$tabO	.= JHtml::_('content.prepare', $x->description_long);
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// VIDEO
	if (isset($x->video) && $x->video != '') {
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phvideo" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_VIDEO').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phvideo">';
		$tabO	.= PhocacartRenderFront::displayVideo($x->video);
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// SPECIFICATION
	if (!empty($this->t['specifications'])){
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phspecification" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_SPECIFICATIONS').'</a></li>';
		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phspecification">';


		foreach($this->t['specifications'] as $k => $v) {
			if(isset($v[0]) && $v[0] != '') {
				$tabO	.= '<h4 class="ph-spec-group-title">'.$v[0].'</h4>';
				unset($v[0]);
			}

			if (!empty($v)) {
				foreach($v as $k2 => $v2) {
					$tabO	.= '<div class="'.$this->s['c']['row'].'">';
					$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
					$tabO	.= '<div class="ph-spec-title">'.$v2['title'].'</div>';
					$tabO	.= '</div>';

					$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm7.md7'].'">';
					$tabO	.= '<div class="ph-spec-value">'.$v2['value'].'</div>';
					$tabO	.= '</div>';
					$tabO	.= '</div>';
				}

			}
		}

		$tabO	.= '</div>';
		$active = $activeTab = '';
	}


	// REVIEWS
	if ($this->t['enable_review'] > 0) {
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phreview" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_REVIEWS').'</a></li>';
		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phreview">';

		if (!empty($this->t['reviews'])) {
			foreach($this->t['reviews'] as $k => $v) {
				$rating = $v->rating;
				$tabO	.= '<div class="bs-callout bs-callout-info">';
				$tabO	.= '<h4 class="ph-reviews-name">'.htmlspecialchars($v->name).'</h4>';
				$tabO	.= '<div><span class="ph-stars"><span style="width:'.((int)$rating * 16) .'px;"></span></span></div>';
				$tabO	.= '<div class="ph-reviews-review">'.htmlspecialchars($v->review).'</div>';
				$tabO	.= '</div>';
			}

		}
		if ((int)$this->u->id > 0) {


			$tabO	.= '<form action="'.$this->t['linkitem'].'" method="post" class="'.$this->s['c']['item_review_form'].'">';
			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_RATING').'</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm10.md10'].' ph-rating-box">';
			$tabO	.= '<div class="ph-review-value ph-rating">';
			$tabO	.= '<select name="rating" id="phitemrating">
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						</select>';
			$tabO	.= '</div>';
			$tabO	.= '</div>';

			$tabO	.= '</div>';

			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_NAME').'</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="ph-review-value"><input type="text" name="name" class="form-control" value="'. $this->u->name .'" /></div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_REVIEW').'</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="ph-review-value"><textarea class="" name="review" rows="3"></textarea></div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"></div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="'.$this->s['c']['pull-right'].'">';
			$tabO	.= '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn"><span class="'.$this->s['i']['edit'].'"></span> '.JText::_('COM_PHOCACART_SUBMIT').'</button>';
			$tabO	.= '</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// END ROW

			$tabO	.= JHtml::_('form.token');
			$tabO	.= '<input type="hidden" name="catid" value="'.$this->t['catid'].'">';
			$tabO	.= '<input type="hidden" name="task" value="item.review">';
			$tabO	.= '<input type="hidden" name="tmpl" value="component" />';
			$tabO	.= '<input type="hidden" name="option" value="com_phocacart" />';
			$tabO	.= '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
			$tabO	.= '</form>';

		} else {
			$tabO	.= '<div class="ph-message">'.JText::_('COM_PHOCACART_ONLY_LOGGED_IN_USERS_CAN_MAKE_REVIEW_PLEASE_LOGIN').'</div>';
		}
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// RELATED PRODUCTS

	if (!empty($this->t['rel_products'])) {
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phrelated" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_RELATED_PRODUCTS').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phrelated">';

		$tabO	.= '<div class="'.$this->s['c']['row'].'">';

		foreach($this->t['rel_products'] as $k => $v) {
			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">';
			$tabO	.= '<div class="ph-item-thumbnail-related">';
			$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');

			// Try to find the best menu link
			if (isset($v->catid2) && (int)$v->catid2 > 0 && isset($v->catalias2) && $v->catalias2 != '') {
				$link 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid2, $v->alias, $v->catalias2));
			} else {
				$link 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
			}
			$tabO	.= '<a href="'.$link.'">';
			if (isset($image->rel) && $image->rel != '') {
				$tabO	.= '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="'.$this->s['c']['img_responsive'].' ph-image"';
				if (isset($this->t['image_width']) && $this->t['image_width'] != '' && isset($this->t['image_height']) && $this->t['image_height'] != '') {
					$tabO	.= ' style="width:'.$this->t['image_width'].';height:'.$this->t['image_height'].'"';
				}
				$tabO	.= ' />';

			}
			$tabO	.= '</a>';
			$tabO	.= '<div class="'.$this->s['c']['caption'].'"><h4><a href="'.$link.'">'.$v->title.'</a></h4></div>';

			$tabO	.= '<div class="">';
			$tabO	.= '<a href="'.$link.'" class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn" role="button"><span class="'.$this->s['i']['view-product'].'"></span> '.JText::_('COM_PHOCACART_VIEW_PRODUCT').'</a>';
			$tabO	.= '</div>';

			$tabO	.= '</div>';
			$tabO	.= '</div>';
		}
		$tabO	.= '</div>';
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// PRICE HISTORY
	if ($this->t['enable_price_history'] && $this->t['price_history_data']) {
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#phpricehistory" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.JText::_('COM_PHOCACART_PRICE_HISTORY').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phpricehistory">';
		$tabO	.= '<div class="'.$this->s['c']['row'].'">';

		$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-cpanel-chart-box">';
		$tabO	.= '<div id="phChartAreaLineHolder" class="ph-chart-canvas-holder" style="width:95%" >';
        $tabO	.= '<canvas id="phChartAreaLine" class="ph-chart-area-line" />';
		$tabO	.= '</div>';
		$tabO	.= '</div>';


		$tabO	.= '</div>';
		$tabO	.= '</div>';

	}

	// TABS PLUGIN
	if (!empty($this->t['event']->onItemInsideTabPanel) && is_array($this->t['event']->onItemInsideTabPanel)) {
		foreach($this->t['event']->onItemInsideTabPanel as $k => $v) {
			if (isset($v['title']) && isset($v['alias']) && isset($v['content'])) {
				$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="#'.strip_tags($v['alias']).'" data-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.$v['title'].'</a></li>';
				$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="'.strip_tags($v['alias']).'">';
				$tabO	.= $v['content'];
				$tabO	.= '</div>';
				$active = $activeTab = '';
			}
		}
	}


	if ($tabLiO != '') {
		echo '<ul class="'.$this->s['c']['tabnav'].'">';
		echo $tabLiO;
		echo '</ul>';

	}

	if ($tabO != '') {
		echo '<div class="'.$this->s['c']['tabcontent'].'">';
		echo $tabO;
		echo '</div>';
	}



	echo '</div>'; // end row 2 (bottom)






	echo $this->t['event']->onItemAfterTabs;

}

if ($this->itemnext[0] || $this->itemprev[0]) {
	echo '<div class="'.$this->s['c']['row'].'">';

	echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-item-navigation-box">';
	if($this->itemprev[0]) {
		$p = $this->itemprev[0];
		$title 	= '';
		$titleT = JText::_('COM_PHOCACART_PREVIOUS_PRODUCT'). ' ('. $p->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = JText::_('COM_PHOCACART_PREVIOUS_PRODUCT');
		} else if ($this->t['title_next_prev'] == 3) {
			$title = $p->title;
		}
		$linkPrev = JRoute::_(PhocacartRoute::getItemRoute($p->id, $p->categoryid, $p->alias, $p->categoryalias));
		echo '<div class="'.$this->s['c']['pull-left'].'">';
		echo '<a href="'.$linkPrev.'" class="'.$this->s['c']['btn.btn-default'].' ph-item-navigation" role="button" title="'.$titleT.'"><span class="'.$this->s['i']['prev'].'"></span> '.$title.'</a>';
		echo '</div>';
	}
	echo '</div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' ph-item-navigation-box">';
	echo '</div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' ph-item-navigation-box">';
	echo '</div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-item-navigation-box">';
	if($this->itemnext[0]) {
		$n = $this->itemnext[0];
		$title 	= '';
		$titleT = JText::_('COM_PHOCACART_NEXT_PRODUCT'). ' ('. $n->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = JText::_('COM_PHOCACART_NEXT_PRODUCT');
		} else if ($this->t['title_next_prev'] == 3) {
			$title = $n->title;
		}
		$linkNext = JRoute::_(PhocacartRoute::getItemRoute($n->id, $n->categoryid, $n->alias, $n->categoryalias));
		echo '<div class="'.$this->s['c']['pull-right'].'">';
		echo '<a href="'.$linkNext.'" class="'.$this->s['c']['btn.btn-default'].' ph-item-navigation" role="button" title="'.$titleT.'">'.$title.' <span class="'.$this->s['i']['next'].'"></span></a>';
		echo '</div>';
	}
	echo '</div>';

	echo '</div>';
}

echo '</div>';
echo '<div id="phContainer"></div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
