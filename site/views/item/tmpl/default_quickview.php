<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
$layoutS	= new JLayoutFile('product_stock', null, array('component' => 'com_phocacart'));
$layoutA	= new JLayoutFile('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new JLayoutFile('button_external_link', null, array('component' => 'com_phocacart'));

$layoutQV 	= new JLayoutFile('popup_quickview', null, array('component' => 'com_phocacart'));
$layoutAB	= new JLayoutFile('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new JLayoutFile('product_order_quantity', null, array('component' => 'com_phocacart'));


$x = $this->item[0];
?>
<div id="phQuickViewPopup" class="modal zoom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <a role="button" class="close" data-dismiss="modal" >&times;</a>
		  <h4><span class="glyphicon glyphicon-eye-open"></span> <?php echo JText::_('COM_PHOCACART_QUICK_VIEW'); ?></h4>
        </div>
        <div class="modal-body"><?php
		

//echo '<h1>'.$x->title.'</h1>';
echo '<div class="row">';

// === IMAGE PANEL
echo '<div id="phImageBox" class="col-xs-12 col-sm-6 col-md-6">';


$idName			= 'VItemQuickP'.(int)$x->id;

$label 	= PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);

// IMAGE
$image 		= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image
$imageL 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image Link to enlarge
$dataImage	= 'data-image="'.JURI::base(true).'/'.$image->rel.'"';// Default image - when changed by javascript back to default

// Some of the attribute is selected - this attribute include image so the image should be displayed instead of default
$imageA = PhocaCartImage::getImageChangedByAttributes($this->t['attr_options'], 'large');
if ($imageA != '') { 
	$image = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
	$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
}

$link 	= JURI::base(true).'/'.$imageL->rel;

if (isset($image->rel) && $image->rel != '') {
	
	echo '<div class="ph-item-image-full-box '.$label['cssthumbnail'].'">';

	echo $label['new'] . $label['hot'] . $label['feat'];

	//echo '<a href="'.$link.'" '.$this->t['image_rel'].'>';
	// In Quic View there is no linking of image
	// 1) but we use A TAG in javascript jquery.phocaswapimage.js se we need A TAG HERE but we make it inactive
	// 2) we need to do it inactive for switching images which comes with links
	//    and this we will do per customHref in function Display: function(imgBox, form, select, customHref) {
	//    custom href will be javascript:void(0); see this file, line cca 286 phSIO1'.(int)$formId.'.Init
	echo '<a href="javascript:void(0);" '.$this->t['image_rel'].' class="phjProductHref'.$idName.'" data-href="'.$link.'">';
	echo '<img src="'.JURI::base(true).'/'.$image->rel.'" '.$dataImage.' alt="" class="img-responsive '.$label['cssthumbnail2'].' ph-image-full phjProductImage'.$idName.'"';
	if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
		echo ' style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px"';
	}
	echo ' />';
	echo '</a>';
	
	echo '</div>'. "\n";
}


echo '</div>';// end image panel


// === PRICE PANEL
echo '<div class="col-xs-12 col-sm-6 col-md-6 ph-item-price-panel">';

$title = '';
if (isset($x->title) && $x->title != '') {
	$title = $x->title;
}
echo PhocacartRenderFront::renderHeader(array($title));

// :L: PRICE
$price 	= new PhocacartPrice;// Can be used by options
if ($this->t['hide_price'] != 1) {
	
	$d					= array();
	$d['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1, 1, $x->group_price);
	$price->getPriceItemsChangedByAttributes($d['priceitems'], $this->t['attr_options'], $price, $x);
	
	$d['priceitemsorig']= array();
	if ($x->price_original != '' && $x->price_original > 0) {
		$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype);
	}
	$d['class']			= 'ph-item-price-box';
	$d['product_id']	= (int)$x->id;
	$d['typeview']		= 'ItemQuick';
		
	// Display discount price
	// Move standard prices to new variable (product price -> product discount)
	$d['priceitemsdiscount']		= $d['priceitems'];
	$d['discount'] 					= PhocacartDiscountProduct::getProductDiscountPrice($x->id, $d['priceitemsdiscount']);
	
	// Display cart discount (global discount) in product views - under specific conditions only
	// Move product discount prices to new variable (product price -> product discount -> product discount cart)
	$d['priceitemsdiscountcart']	= $d['priceitemsdiscount'];
	$d['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($x->id, $x->catid, $d['priceitemsdiscountcart']);
		
		
	echo$layoutP->render($d);
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
		
		if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count']) {
			$d							= array();
			$d['class']					= 'ph-item-stock-box';
			$d['product_id']			= (int)$x->id;
			$d['typeview']				= 'Item';
			$d['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($this->t['stock_status']);
			echo $layoutS->render($d);
		}
		
		if($this->t['stock_status']['min_quantity']) {
			$dPOQ						= array();
			$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY');
			$dPOQ['status']				= $this->t['stock_status']['min_quantity'];
			echo $layoutPOQ->render($dPOQ);
		}
		
		if($this->t['stock_status']['min_multiple_quantity']) {
			$dPOQ						= array();
			$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY');
			$dPOQ['status']				= $this->t['stock_status']['min_multiple_quantity'];
			echo $layoutPOQ->render($dPOQ);
		}
	}
	// END STOCK ================================================

// This form can get two events:
// when option selected - price or image is changed id=phItemPriceBoxForm
// when ajax cart is active and submit button is clicked class=phItemCartBoxForm
//
echo '<form 
id="phCartAddToCartButton'.(int)$x->id.'"
class="phItemCartBoxForm phjAddToCart phjItemQuick phjAddToCartVItemQuickP'.(int)$x->id.' form-inline" 
action="'.$this->t['linkcheckout'].'" method="post">';

// data-id="'.(int)$x->id.'" - needed for dynamic change of price in quick view, we need to get the ID per javascript
// because Quick View = Items, Category View and there are more products listed, not like in item id

// ATTRIBUTES, OPTIONS
$d							= array();
$d['attr_options']			= $this->t['attr_options'];
$d['hide_attributes']		= $this->t['hide_attributes_item'];
$d['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
$d['zero_attribute_price']  = $this->t['zero_attribute_price'];
$d['pathitem']				= $this->t['pathitem'];
$d['init_type']				= 1;
$d['product_id']			= (int)$x->id;
$d['image_size']			= 'large';
$d['price']					= $price;
$d['typeview']				= 'ItemQuick';
echo $layoutAB->render($d);


// :L: ADD TO CART
if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {
	
	$d					= array();
	$d['id']			= (int)$x->id;
	$d['catid']			= $this->t['catid'];
	$d['return']		= $this->t['actionbase64'];
	$d['addtocart']		= $this->t['item_addtocart'];
	$d['typeview']		= 'ItemQuick';
	$d['class_btn']		= $class_btn;
	$d['class_icon']	= $class_icon;
	echo$layoutA->render($d);

} else if ((int)$this->t['item_addtocart'] == 2 && (int)$x->external_id != '') {
	$d					= array();
	$d['external_id']	= (int)$x->external_id;
	$d['return']		= $this->t['actionbase64'];
	
	echo$layoutA2->render($d);
} else if ((int)$this->t['item_addtocart'] == 3 && $x->external_link != '') {
	$d					= array();	
	$d['external_link']	= $x->external_link;
	$d['external_text']	= $x->external_text;
	$d['return']		= $this->t['actionbase64'];
	echo $layoutA3->render($d);
			
}

echo '</form>';
echo '<div class="ph-cb"></div>';

// TAGS
if ($this->t['tags_output'] != '') {
	echo '<div class="ph-item-tag-box">';
	echo $this->t['tags_output'];
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}


echo '</div>';// end right side price panel
echo '</div>';// end row	
			
		
        ?></div>
		<div class="modal-footer"></div>
	   </div>
    </div>
</div>