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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

$layoutP	= new FileLayout('product_price', null, array('component' => 'com_phocacart'));
$layoutS	= new FileLayout('product_stock', null, array('component' => 'com_phocacart'));
$layoutA	= new FileLayout('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new FileLayout('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new FileLayout('button_external_link', null, array('component' => 'com_phocacart'));

//$layoutQV 	= new FileLayout('popup_quickview', null, array('component' => 'com_phocacart'));
$layoutAB	= new FileLayout('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new FileLayout('product_order_quantity', null, array('component' => 'com_phocacart'));
$layoutI	= new FileLayout('image', null, array('component' => 'com_phocacart'));

$close = '<button type="button" class="'.$this->s['c']['modal-btn-close'].'"'.$this->s['a']['modal-btn-close'].' aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '.$this->s['a']['data-bs-dismiss-modal'].' ></button>';

$x = $this->item[0];
?>
<div id="phQuickViewPopup" class="<?php echo $this->s['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="<?php echo $this->s['c']['modal-dialog'] ?> <?php echo $this->s['c']['modal-lg'] ?>">
      <div class="<?php echo $this->s['c']['modal-content'] ?>">
        <div class="<?php echo $this->s['c']['modal-header'] ?>">
		 <h5 class="<?php echo $this->s['c']['modal-title'] ?>"><?php echo PhocacartRenderIcon::icon($this->s['i']['quick-view'], '', ' ') . Text::_('COM_PHOCACART_QUICK_VIEW'); ?></h5>
            <?php echo $close ?>
        </div>
        <div class="<?php echo $this->s['c']['modal-body'] ?>"><?php


//echo '<h1>'.$x->title.'</h1>';
echo '<div class="'. $this->s['c']['row'].'">';

// === IMAGE PANEL
echo '<div id="phImageBox" class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-item-view-image-box">';


$idName			= 'VItemQuickP'.(int)$x->id;

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

$link 	= Uri::base(true).'/'.$imageL->rel;

if (isset($image->rel) && $image->rel != '') {

	echo '<div class="ph-item-image-full-box '.$label['cssthumbnail'].'">';

	echo '<div class="ph-label-box">';
	echo $label['new'] . $label['hot'] . $label['feat'];
	if ($this->t['taglabels_output'] != '') {
		echo $this->t['taglabels_output'];
	}
	echo '</div>';


    $altValue   = PhocaCartImage::getAltTitle($x->title, $x->image);

	//echo '<a href="'.$link.'" '.$this->t['image_rel'].'>';
	// In Quic View there is no linking of image
	// 1) but we use A TAG in javascript jquery.phocaswapimage.js se we need A TAG HERE but we make it inactive
	// 2) we need to do it inactive for switching images which comes with links
	//    and this we will do per customHref in function Display: function(imgBox, form, select, customHref) {
	//    custom href will be javascript:void(0); see this file, line cca 286 phSIO1'.(int)$formId.'.Init
	echo '<a href="javascript:void(0);" '.$this->t['image_rel'].' class="phjProductHref'.$idName.'" data-href="'.$link.'">';

    $d						= array();
    $d['t']					= $this->t;
    $d['s']					= $this->s;
    $d['src']				= Uri::base(true).'/'.$image->rel;
    $d['srcset-webp']		= Uri::base(true).'/'.$image->rel_webp;
    $d['data-image']		= Uri::base(true).'/'.$image->rel;// Default image - when changed by javascript back to default
    $d['data-image-webp']	= Uri::base(true).'/'.$image->rel_webp;// Default image - when changed by javascript back to default
    $d['alt-value']			= PhocaCartImage::getAltTitle($x->title, $image->rel);
    $d['class']				= PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'], $label['cssthumbnail2'], 'ph-image-full', 'phjProductImage'.$idName));
    $d['style']				= '';
    if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
        $d['style'] = 'width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px';
    }
    echo $layoutI->render($d);


	echo '</a>';

	echo '</div>'. "\n";
}


echo '</div>';// end image panel


// === PRICE PANEL
echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-item-view-data-box ph-item-price-panel">';

$title = '';
if (isset($x->title) && $x->title != '') {
	$title = $x->title;
}
echo PhocacartRenderFront::renderHeader(array($title));

// :L: PRICE
$price 	= new PhocacartPrice;// Can be used by options

if ($this->t['can_display_price']) {

	$dP					= array();
	$dP['s']				= $this->s;
	$dP['type']          = $x->type;// PRODUCTTYPE
	$dP['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1, 1, $x->group_price, $x->taxhide);
	$price->getPriceItemsChangedByAttributes($dP['priceitems'], $this->t['attr_options'], $price, $x);

	$dP['priceitemsorig']= array();
	if ($x->price_original != '' && $x->price_original > 0) {
		$dP['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype, '', 0, '', 0, 1, null, $x->taxhide);
	}
	$dP['class']			= 'ph-item-price-box';
	$dP['product_id']	= (int)$x->id;
	$dP['typeview']		= 'ItemQuick';

	// Display discount price
	// Move standard prices to new variable (product price -> product discount)
	$dP['priceitemsdiscount']		= $dP['priceitems'];
	$dP['discount'] 					= PhocacartDiscountProduct::getProductDiscountPrice($x->id, $dP['priceitemsdiscount']);

	// Display cart discount (global discount) in product views - under specific conditions only
	// Move product discount prices to new variable (product price -> product discount -> product discount cart)
	$dP['priceitemsdiscountcart']	= $dP['priceitemsdiscount'];
	$dP['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($x->id, $x->catid, $dP['priceitemsdiscountcart']);

	$dP['zero_price']		= 1;// Apply zero price if possible
	echo$layoutP->render($dP);
}
if ( isset($x->description) && $x->description != '') {
    echo '<div class="ph-desc">'. HTMLHelper::_('content.prepare', $x->description). '</div>';
}
// REWARD POINTS - NEEDED
$pointsN = PhocacartReward::getPoints($x->points_needed, 'needed');
if ($pointsN) {
    echo '<div class="ph-item-reward-box ph-item-reward-needed">';
    echo '<div class="ph-reward-txt">'.Text::_('COM_PHOCACART_PRICE_IN_REWARD_POINTS').'</div>';

    echo '<div class="ph-reward">'.$pointsN.'</div>';
    echo '</div>';
    echo '<div class="ph-cb"></div>';
}

// REWARD POINTS - RECEIVED
$pointsR = PhocacartReward::getPoints($x->points_received, 'received', $x->group_points_received);
if ($pointsR) {
    echo '<div class="ph-item-reward-box ph-item-reward-received">';
    echo '<div class="ph-reward-txt">'.Text::_('COM_PHOCACART_REWARD_POINTS').'</div>';

    echo '<div class="ph-reward">'.$pointsR.'</div>';
    echo '</div>';
    echo '<div class="ph-cb"></div>';
}


if (isset($x->manufacturertitle) && $x->manufacturertitle != '') {
    echo '<div class="ph-item-manufacturer-box">';
    echo '<div class="ph-manufacturer-txt">'.Text::_('COM_PHOCACART_MANUFACTURER').':</div>';
    echo '<div class="ph-manufacturer">';
    echo PhocacartRenderFront::displayLink($x->manufacturertitle, $x->manufacturerlink);
    echo '</div>';
    echo '</div>';
    echo '<div class="ph-cb"></div>';
}

// :L: ADD TO CART
$addToCartHidden = 0;// Button can be hidden based on price This variable is used for displaying Ask Question

// STOCK ===================================================
// Set stock: product, variations, or advanced stock status
$class_btn	= '';
$class_icon	= '';
$this->stock = PhocacartStock::getStockItemsChangedByAttributes($this->t['stock_status'], $this->t['attr_options'], $x);

if ($this->t['display_stock_status'] == 1 || $this->t['display_stock_status'] == 3) {


    if ($this->t['hide_add_to_cart_stock'] == 1 && (int)$this->stock < 1) {
        $class_btn 					= 'ph-visibility-hidden';
        $class_icon					= 'ph-display-none';
        $addToCartHidden 			= 1;// used for displaying Ask Question
    }

    if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count'] !== false) {
        $d							= array();
        $d['s']					    = $this->s;
        $d['class']					= 'ph-item-stock-box';
        $d['product_id']			= (int)$x->id;
        $d['typeview']				= 'ItemQuick';
        $d['stock_status_class']	= isset($this->t['stock_status']['stock_status_class']) ? $this->t['stock_status']['stock_status_class'] : '';
        $d['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($this->t['stock_status']);
        echo $layoutS->render($d);
    }

    if($this->t['stock_status']['min_quantity']) {
        $dPOQ						= array();
        $dPOQ['s']					= $this->s;
        $dPOQ['text']				= Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY');
        $dPOQ['status']				= $this->t['stock_status']['min_quantity'];
        echo $layoutPOQ->render($dPOQ);
    }

    if($this->t['stock_status']['min_multiple_quantity']) {
        $dPOQ						= array();
        $dPOQ['s']					= $this->s;
        $dPOQ['text']				= Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY');
        $dPOQ['status']				= $this->t['stock_status']['min_multiple_quantity'];
        echo $layoutPOQ->render($dPOQ);
    }

    if($this->t['stock_status']['max_quantity']) {
        $dPOQ						= array();
        $dPOQ['s']					= $this->s;
        $dPOQ['text']				= Text::_('COM_PHOCACART_MAXIMUM_ORDER_QUANTITY');
        $dPOQ['status']				= $this->t['stock_status']['max_quantity'];
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
$d['s']      = $this->s;
$d['attr_options']			= $this->t['attr_options'];
$d['hide_attributes']		= $this->t['hide_attributes_item'];
$d['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
$d['remove_select_option_attribute']	= $this->t['remove_select_option_attribute'];
$d['zero_attribute_price']  = $this->t['zero_attribute_price'];
$d['stock_calculation']	    = (int)$x->stock_calculation;
$d['pathitem']				= $this->t['pathitem'];
$d['init_type']				= 1;
$d['product_id']			= (int)$x->id;
$d['image_size']			= 'large';
$d['price']					= $price;
$d['typeview']				= 'ItemQuick';
echo $layoutAB->render($d);



if ($x->type == 3) {
    // PRODUCTTYPE - price on demand price cannot be added to cart
    $addToCartHidden = 1;
} else if ($this->t['hide_add_to_cart_zero_price'] == 1 && $x->price == 0) {
	// Don't display Add to Cart in case the price is zero
	$addToCartHidden = 1;
} else if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {

	$d					= array();
	$d['s']			    = $this->s;
	$d['id']			= (int)$x->id;
	$d['catid']			= $this->t['catid'];
    $d['sku']			    = isset($x->sku) ? $x->sku : '';
    $d['ean']			    = isset($x->ean) ? $x->ean : '';
    $d['basepricenetto']    = isset($dP['priceitems']['nettocurrency']) ? $dP['priceitems']['nettocurrency'] : '';
    $d['basepricetax']      = isset($dP['priceitems']['taxcurrency']) ? $dP['priceitems']['taxcurrency'] : '';
    $d['basepricebrutto']   = isset($dP['priceitems']['bruttocurrency']) ? $dP['priceitems']['bruttocurrency'] : '';
    $d['title']				= isset($x->title) ? $x->title : '';
	$d['return']		= $this->t['actionbase64'];
	$d['addtocart']		= $this->t['item_addtocart'];
	$d['typeview']		= 'ItemQuick';
	$d['class_btn']		= $class_btn;
	$d['class_icon']	= $class_icon;
	echo$layoutA->render($d);

} else if ((int)$this->t['item_addtocart'] == 2 && (int)$x->external_id != '') {
	$d					= array();
	$d['s']				= $this->s;
	$d['external_id']	= (int)$x->external_id;
	$d['return']		= $this->t['actionbase64'];

	echo$layoutA2->render($d);
} else if ((int)$this->t['item_addtocart'] == 3 && $x->external_link != '') {
	$d					= array();
	$d['s']				= $this->s;
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
		<div class="<?php echo $this->s['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>
