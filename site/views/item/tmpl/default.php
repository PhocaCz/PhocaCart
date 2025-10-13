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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Phoca\PhocaCart\ContentType\ContentTypeHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$layoutC 	= new FileLayout('button_compare', null, array('component' => 'com_phocacart'));
$layoutW 	= new FileLayout('button_wishlist', null, array('component' => 'com_phocacart'));
$layoutP	= new FileLayout('product_price', null, array('component' => 'com_phocacart'));
$layoutS	= new FileLayout('product_stock', null, array('component' => 'com_phocacart'));
$layoutID	= new FileLayout('product_id', null, array('component' => 'com_phocacart'));
$layoutPP	= new FileLayout('product_play', null, array('component' => 'com_phocacart'));
$layoutA	= new FileLayout('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new FileLayout('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new FileLayout('button_external_link', null, array('component' => 'com_phocacart'));
$layoutQ	= new FileLayout('button_ask_question', null, array('component' => 'com_phocacart'));
$layoutPD	= new FileLayout('button_public_download', null, array('component' => 'com_phocacart'));
$layoutEL	= new FileLayout('link_external_link', null, array('component' => 'com_phocacart'));
$layoutAB	= new FileLayout('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new FileLayout('product_order_quantity', null, array('component' => 'com_phocacart'));
$layoutSZ	= new FileLayout('product_size', null, array('component' => 'com_phocacart'));
$layoutI	= new FileLayout('image', null, array('component' => 'com_phocacart'));
$layoutAAQ	= new FileLayout('popup_container_iframe', null, array('component' => 'com_phocacart'));
$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));
$layoutWatchdog	= new FileLayout('product_watchdog', null, ['component' => 'com_phocacart']);

echo '<div id="ph-pc-item-box" class="pc-view pc-item-view'.$this->p->get( 'pageclass_sfx' ).'">';


if (isset($this->category[0]->id) && ($this->t['display_back'] == 2 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->id > 0) {
		$linkUp = Route::_(PhocacartRoute::getCategoryRoute($this->category[0]->id, $this->category[0]->alias));
		$linkUpText = $this->category[0]->title;
	} else {
		$linkUp 	= false;
		$linkUpText = false;
	}

	if ($this->t['skip_category_view'] == 1) {
        $linkUpText = Text::_('COM_PHOCACART_BACK_TO_LIST');
    }

	if ($linkUp && $linkUpText) {
		echo '<div class="ph-top">'
		.'<a class="'.$this->s['c']['btn.btn-secondary'].'" title="'.$linkUpText.'" href="'. $linkUp.'" >'
        //.'<span class="'.$this->s['i']['back-category'].'"></span> '
		. PhocacartRenderIcon::icon($this->s['i']['back-category'], '', ' ')
		.Text::_($linkUpText) .'</a>'
        .'</div>';
	}
}

echo $this->t['event']->onItemBeforeHeader;


$popupAskAQuestion = 0;// we need this info for the container at the bottom (if modal popup is used for ask a question)
$x = isset($this->item[0]) ? $this->item[0] : null;

if (!empty($x) && isset($x->id) && (int)$x->id > 0) {

	$idName			= 'VItemP'.(int)$x->id;

    // FIRST - GET PRICE INFO (the price is not displayed here but we need the info about price e.g. for different classes)
    $price 				= new PhocacartPrice;// Can be used by options
    $priceItems = array();
	if ($this->t['can_display_price']) {

		$priceItems	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1, 1, $x->group_price, $x->taxhide);
		// Can change price and also SKU OR EAN (Advanced Stock and Price Management)
		$price->getPriceItemsChangedByAttributes($priceItems, $this->t['attr_options'], $price, $x);

		$dP					= array();
		$dP['s']				= $this->s;
		$dP['type']          = $x->type;// PRODUCTTYPE
		$dP['priceitems']	= $priceItems;

		$dP['priceitemsorig']= array();
		if ($x->price_original != '' && $x->price_original > 0) {
			$dP['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype, '', 0, '', 0, 1, null, $x->taxhide);
		}
		$dP['class']			= 'ph-item-price-box';
		$dP['product_id']	= (int)$x->id;
		$dP['typeview']		= 'Item';

		// Display discount price
		// Move standard prices to new variable (product price -> product discount)
		$dP['priceitemsdiscount']		= $dP['priceitems'];
		$dP['discount'] 					= PhocacartDiscountProduct::getProductDiscountPrice($x->id, $dP['priceitemsdiscount']);

		// Display cart discount (global discount) in product views - under specific conditions only
		// Move product discount prices to new variable (product price -> product discount -> product discount cart)
		$dP['priceitemsdiscountcart']	= $dP['priceitemsdiscount'];
		$dP['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($x->id, $x->catid, $dP['priceitemsdiscountcart']);

		$dP['zero_price']		= 1;// Apply zero price if possible
	}

    // Additional class
    $classAdditional = [];
    if (PhocacartRenderFront::renderNewIcon($x->date, 1, 1)) {
        $classAdditional[] = 'pc-status-new';
    }
    if (PhocacartRenderFront::renderHotIcon($x->sales, 1, 1)) {
        $classAdditional[] = 'pc-status-hot';
    }

    if (PhocacartRenderFront::renderFeaturedIcon($x->featured, 1, 1)) {
        $classAdditional[] = 'pc-status-featured';
    }

    if (isset($dP['discount']) && $dP['discount']) {
        $classAdditional[] = 'pc-status-discount-product';
    }

    if (isset($dP['discountcart']) && $dP['discountcart']) {
        $classAdditional[] = 'pc-status-discount-cart';
    }

    $classAdditionalOutput = !empty($classAdditional) ? ' '. implode(' ', $classAdditional) : '';// Additional class



    // RENDER
	echo '<div class="'.$this->s['c']['row'].$classAdditionalOutput.'">';

	// === IMAGE PANEL
	echo '<div id="phImageBox" class="'.$this->s['c']['col.xs12.sm5.md5'] .' ph-item-view-image-box">';

	$results = Dispatcher::dispatch(new Event\View\Item\Image('com_phocacart.item', $x, $this->t, $this->p));
	$imageOutput = trim(implode("\n", $results));


	if ($imageOutput != '') {
		echo $imageOutput;// rendered by plugin
	} else {

		$label = PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);


		// IMAGE
		$image = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image
		$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');// Image Link to enlarge


		// Some of the attribute is selected - this attribute include image so the image should be displayed instead of default
		$imageA = PhocaCartImage::getImageChangedByAttributes($this->t['attr_options'], 'large');
		if ($imageA != '') {
			$image = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
			$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $imageA, 'large');
		}

		$link = Uri::base(true) . '/' . $imageL->rel;// Thumbnail
		//$link = Uri::base(true) . '/' . $this->t['pathitem']['orig_rel_ds'] . $x->image;// Original image
		if ($this->t['display_webp_images'] == 1) {
			$link = Uri::base(true) . '/' . $imageL->rel_webp;
		}


		if (isset($image->rel) && $image->rel != '') {

			$altValue = PhocaCartImage::getAltTitle($x->title, $image->rel);

			echo '<div class="ph-item-image-full-box ' . $label['cssthumbnail'] . '">';
			echo '<div class="ph-label-box">';
			echo $label['new'] . $label['hot'] . $label['feat'];
			if ($this->t['taglabels_output'] != '') {
				echo $this->t['taglabels_output'];
			}
			echo '</div>';

			$imageS = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'small');
			$linkS = Uri::base(true) . '/' . $imageS->rel;// Thumbnail
			if ($this->t['display_webp_images'] == 1) {
				$linkS = Uri::base(true) . '/' . $imageS->rel_webp;
			}
			echo '<a href="' . $link . '" ' . $this->t['image_rel'] . ' class="' . $this->t['image_class'] . ' phjProductHref' . $idName . ' phImageFullHref phImageGalleryHref" data-href="' . $link . '" data-href-s="' . $linkS . '">';

			$d = array();
			$d['t'] = $this->t;
			$d['s'] = $this->s;
			$d['src'] = Uri::base(true) . '/' . $image->rel;
			$d['data-image'] = Uri::base(true) . '/' . $image->rel;
			$d['data-image-webp'] = Uri::base(true) . '/' . $image->rel_webp;
			$d['alt-value'] = PhocaCartImage::getAltTitle($x->title, $image->rel);
			$d['srcset-webp'] = $d['data-image-webp'];
			$d['data-image-meta'] = $d['data-image'];
			$d['class'] = PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'], $label['cssthumbnail2'], 'ph-image-full', 'phImageFull', 'phjProductImage' . $idName));
			$d['style'] = '';
			if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
				$d['style'] = 'width:' . $this->t['image_width'] . 'px;height:' . $this->t['image_height'] . 'px';
			}
			echo $layoutI->render($d);

			echo '</a>';

			echo '</div>' . "\n";// end item_row_item_box_full_image
		}


		// ADDITIONAL IMAGES
		if (!empty($this->t['add_images'])) {

			echo '<div class="' . $this->s['c']['row'] . ' ph-item-image-add-box">';

			foreach ($this->t['add_images'] as $v2) {

                if ($v2->image == '') {
					continue;
				}

				echo '<div class="' . $this->s['c']['col.xs12.sm4.md4'] . ' ph-item-image-box">';
				$image = PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'small');
				$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'large');
				$link = Uri::base(true) . '/' . $imageL->rel;
				if ($this->t['display_webp_images'] == 1) {
					$link = Uri::base(true) . '/' . $imageL->rel_webp;
				}

				$altValue = PhocaCartImage::getAltTitle($x->title, $v2->image);

				echo '<a href="' . $link . '" ' . $this->t['image_rel'] . '  class="' . $this->t['image_class'] . ' phImageAdditionalHref">';

				$d = array();
				$d['t'] = $this->t;
				$d['s'] = $this->s;
				$d['src'] = Uri::base(true) . '/' . $image->rel;
				$d['srcset-webp'] = Uri::base(true) . '/' . $image->rel_webp;
				$d['alt-value'] = PhocaCartImage::getAltTitle($x->title, $v2->image);
				$d['class'] = PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'], $label['cssthumbnail2'], 'ph-image-full', 'phImageAdditional', /*, 'phjProductImage'.$idName*/));
				echo $layoutI->render($d);

				echo '</a>';
				echo '</div>';
			}

			echo '</div>';// end additional images
		}
	} // end image output

	echo '</div>';// end item_row_item_c1




	// === PRICE PANEL
	echo '<div class="'.$this->s['c']['col.xs12.sm7.md7'].' ph-item-view-data-box">';
	echo '<div class="ph-item-price-panel phItemPricePanel">';

	$title = '';
	if (isset($this->item[0]->title) && $this->item[0]->title != '') {
		$title = $this->item[0]->title;
	}
	if (isset($this->item[0]->title_long) && $this->item[0]->title_long != '') {
		$title = $this->item[0]->title_long;
	}


	echo PhocacartRenderFront::renderHeader(array($title));

	// :L: PRICE
	if ($this->t['can_display_price']) {
		echo $layoutP->render($dP);
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

       /* if (isset($x->manufacturerimage) && $x->manufacturerimage) {
            echo  '<img src="'.Uri::base(true) . '/' . $x->manufacturerimage.'" alt="'.PhocacartText::filterValue($x->manufacturertitle, 'text').'" />';
        }*/

		echo '</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}


	// :L: ADD TO CART
	$addToCartHidden = 0;// Button can be hidden based on price This variable is used for displaying Ask Question



	// STOCK ===================================================
	// Set stock: product, variations, or advanced stock status
	// There are classes because AJAX can change the visibility of buttons
	// Last word when checking if product can be ordered have always checkout
	$class_btn	= '';
	$class_icon	= '';

	$stock = PhocacartStock::getStockItemsChangedByAttributes($this->t['stock_status'], $this->t['attr_options'], $x);


	if ($this->t['hide_add_to_cart_stock'] == 1 && (int)$stock < 1) {
		$class_btn 					= 'ph-visibility-hidden';
		$class_icon					= 'ph-display-none';
		$addToCartHidden 			= 1;// used for displaying Ask Question
	}

	if ($this->t['display_stock_status'] == 1 || $this->t['display_stock_status'] == 3) {


		if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count'] !== false) {

			$d							= array();
			$d['s']						= $this->s;
			$d['class']					= 'ph-item-stock-box';
			$d['product_id']			= (int)$x->id;
			$d['typeview']				= 'Item';
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
	}

    if ($stock < 1) {
        echo $layoutWatchdog->render([
            'product' => $x,
        ]);
    }

	if ((int)$this->t['item_display_delivery_date'] > 0 && $x->delivery_date != '' && $x->delivery_date != '0000-00-00 00:00:00') {

		echo '<div class="ph-item-delivery-date-box">';
		echo '<div class="ph-delivery-date-txt">'.Text::_('COM_PHOCACART_DELIVERY_DATE').':</div>';
		echo '<div class="ph-delivery-date">';
		echo HTMLHelper::date($x->delivery_date, 'DATE_FORMAT_LC3');
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

	// ID OPTIONS (SKU, EAN, UPC, ...) ==========================

	$id = new PhocacartId();
	$id->getIdItemsChangedByAttributes($x, $this->t['attr_options']);

	$dID						= array();
	$dID['s']					= $this->s;
	$dID['x']					= $x;
	$dID['class']			= 'ph-item-id-box';
	$dID['product_id']	= (int)$x->id;
	$dID['typeview']		= 'Item';
	echo $layoutID->render($dID);
	// END ID OPTIONS ===========================================


	// ARCHIVED PRODUCT ===========================================
	if ($x->published == 2) {
		echo $layoutAl->render(array('type' => 'warning', 'text' => Text::_('COM_PHOCACART_ARCHIVED_PRODUCT')));
	}
	// END ARCHIVED PRODUCT ===========================================

	// This form can get two events:
	// when option selected - price or image is changed id=phItemPriceBoxForm
	// when ajax cart is active and submit button is clicked class=phItemCartBoxForm
	echo '<form 
	id="phCartAddToCartButton'.(int)$x->id.'"
	class="phItemCartBoxForm phjAddToCart phjItem phjAddToCartVItemP'.(int)$x->id.' form-inline" 
	action="'.$this->t['linkcheckout'].'" method="post">';

	// ATTRIBUTES, OPTIONS
	$d = array();
	$d['s'] = $this->s;
	$d['attr_options'] = $this->t['attr_options'];
	$d['hide_attributes'] = $this->t['hide_attributes_item'];
	$d['dynamic_change_image'] = $this->t['dynamic_change_image'];
	$d['zero_attribute_price'] = $this->t['zero_attribute_price'];
	$d['stock_calculation'] = (int)$x->stock_calculation;
	$d['remove_select_option_attribute'] = $this->t['remove_select_option_attribute'];
	$d['pathitem'] = $this->t['pathitem'];
	$d['init_type'] = 0;
	$d['price'] = $price;
	$d['product_id'] = (int)$x->id;
	$d['gift_types'] = $x->gift_types;
	$d['image_size'] = 'large';
	$d['typeview'] = 'Item';
	$d['priceitems'] = $priceItems;
	echo $layoutAB->render($d);

	if ($x->type == PhocacartProduct::PRODUCT_TYPE_PRICE_ON_DEMAND_PRODUCT) {
		// PRODUCTTYPE - price on demand product cannot be added to cart
		$addToCartHidden = 1;

	} else if ($this->t['hide_add_to_cart_zero_price'] == 1 && $x->price == 0) {
		// Don't display Add to Cart in case the price is zero
		$addToCartHidden = 1;

	} else if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {

		$d					= array();
		$d['s']				= $this->s;
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

	// :L: PUBLIC FILE PLAY
	if ($this->t['display_file_play'] == 1 && $x->public_play_file != '') {
		$d						= array();
		$d['s']					= $this->s;
		$d['id']				= (int)$x->id;
		$d['publicplayfile']	= $x->public_play_file;
		$d['pathpublicfile'] 	= $this->t['pathpublicfile'];
		$d['title']				= '';
		if ($x->public_play_text != '') {
			$d['title']			= $x->public_play_text;
		}

		echo '<div class="ph-cb"></div>';
		echo $layoutPP->render($d);
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

	// :L: EXTERNAL LINK 2
	if ($this->t['display_external_link'] == 1 && $x->external_link2 != '') {
		$d					= array();
		$d['s']				= $this->s;
		$d['linkexternal']	= $x->external_link2;
		//$d['id']			= (int)$x->id;
		//$d['return']		= $this->t['actionbase64'];
		$d['title']			= '';
		if ($x->external_text2 != '') {
			$d['title']		= $x->external_text2;
		}

		echo '<div class="ph-cb"></div>';
		echo $layoutEL->render($d);
	}

	// ASK A QUESTION

	if (((int)$this->t['item_askquestion'] == 1) || ($this->t['item_askquestion'] == 2 && ((int)$this->t['item_addtocart'] == 0 || $addToCartHidden != 0))) {

		$d					= array();
		$d['s']				= $this->s;
		$d['id']			= (int)$x->id;
		$d['catid']			= $this->t['catid'];
		$d['popup']			= 0;
		$tmpl				= '';
		if ((int)$this->t['popup_askquestion'] > 0) {
			$d['popup']			= (int)$this->t['popup_askquestion'];
			$popupAskAQuestion	= (int)$this->t['popup_askquestion'];
			$tmpl				= 'tmpl=component';
		}
		$d['link']			=  Route::_(PhocacartRoute::getQuestionRoute($x->id, $x->catid, $x->alias, $x->catalias, $tmpl));
		$d['return']		= $this->t['actionbase64'];

		echo '<div class="ph-cb"></div>';
		echo $layoutQ->render($d);
	}


	echo '<div class="ph-cb"></div>';

	echo $this->t['event']->onItemBeforeEndPricePanel;// View Plugin

	echo $this->t['event']->PCPonItemBeforeEndPricePanel;// Payment Plugin

	echo '</div>';// end item_row_item_box_price
	echo '</div>';// end item_row_item_c2

	echo '</div>';// end item_row

	echo '<div class="ph-item-bottom-box">';



	// TABS
	$opt = array();
	$opt['active'] = $this->s['c']['tabactive'];

	if ($this->s['c']['class-type'] != 'uikit') {
		//HTMLHelper::_('bootstrap.framework');

		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->useScript('bootstrap.tab');
		Factory::getDocument()->addScriptOptions('bootstrap.tabs', array('PcItemTab' => $opt));

	}


	$active 		= $this->s['c']['tabactive'];
	$activeTab 		= $this->s['c']['tabactvietab'];// Not displayed in Bootstrap4
	$tabO	= '';
	$tabLiO	= '';


	// DESCRIPTION
	if (isset($x->description_long) && $x->description_long != '') {

        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phdescription';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_DESCRIPTION').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phdescription">';
		$tabO	.= HTMLHelper::_('content.prepare', $x->description_long);
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// FEATURES
	if (isset($x->features) && $x->features != '') {
        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phfeatures';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_FEATURES').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phfeatures">';
		$tabO	.= HTMLHelper::_('content.prepare', $x->features);
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// VIDEO
	if (isset($x->video) && $x->video != '') {
        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phvideo';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_VIDEO').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phvideo">';
		$tabO	.= PhocacartRenderFront::displayVideo($x->video);
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// SPECIFICATION
	if (!empty($this->t['specifications'])){
        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phspecification';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_SPECIFICATIONS').'</a></li>';
		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phspecification">';


		foreach($this->t['specifications'] as $k => $v) {
			if(isset($v[0]) && $v[0] != '') {
				$tabO	.= '<h4 class="ph-spec-group-title">'.$v[0].'</h4>';
				unset($v[0]);
			}

			if (!empty($v)) {
				foreach($v as $k2 => $v2) {
					if (isset($v2['title']) && isset($v2['value'])) {
						$tabO .= '<div class="' . $this->s['c']['row'] . '">';
						$tabO .= '<div class="' . $this->s['c']['col.xs12.sm5.md5'] . '">';
						$tabO .= '<div class="ph-spec-title">' . $v2['title'] . '</div>';
						$tabO .= '</div>';

						$tabO .= '<div class="' . $this->s['c']['col.xs12.sm7.md7'] . '">';
						$tabO .= '<div class="ph-spec-value">' . $v2['value'] . '</div>';
						$tabO .= '</div>';
						$tabO .= '</div>';
					}
				}
			}
		}

		$tabO	.= '</div>';
		$active = $activeTab = '';
	}


	// REVIEWS
	if ($this->t['enable_review'] > 0) {
        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phreview';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_REVIEWS').'</a></li>';
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
			$tabO	.= '<div class="ph-review-title">'.Text::_('COM_PHOCACART_RATING').'</div>';
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
			$tabO	.= '<div class="ph-review-title">'.Text::_('COM_PHOCACART_NAME').'</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="ph-review-value"><input type="text" name="name" class="'.$this->s['c']['inputbox.form-control'].'" value="'. $this->u->name .'" /></div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'">';
			$tabO	.= '<div class="ph-review-title">'.Text::_('COM_PHOCACART_REVIEW').'</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="ph-review-value"><textarea class="'.$this->s['c']['inputbox.textarea'].'" name="review" rows="3"></textarea></div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// ROW
			$tabO	.= '<div class="'.$this->s['c']['row'].'">';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm2.md2'].'"></div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'">';
			$tabO	.= '<div class="'.$this->s['c']['pull-right'].' ph-button-edit-box">';
			$tabO	.= '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn">';
			//$tabO   .= '<span class="'.$this->s['i']['edit'].'"></span> '
			$tabO   .= PhocacartRenderIcon::icon($this->s['i']['edit'], '', ' ');
			$tabO   .= Text::_('COM_PHOCACART_SUBMIT').'</button>';
			$tabO	.= '</div>';
			$tabO	.= '</div>';

			$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm5.md5'].'"></div>';

			$tabO	.= '</div>';

			// END ROW

			$tabO	.= HTMLHelper::_('form.token');
			$tabO	.= '<input type="hidden" name="catid" value="'.$this->t['catid'].'">';
			$tabO	.= '<input type="hidden" name="task" value="item.review">';
			$tabO	.= '<input type="hidden" name="tmpl" value="component" />';
			$tabO	.= '<input type="hidden" name="option" value="com_phocacart" />';
			$tabO	.= '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
			$tabO	.= '</form>';

		} else {
			$tabO	.= '<div class="ph-message">'.Text::_('COM_PHOCACART_ONLY_LOGGED_IN_USERS_CAN_MAKE_REVIEW_PLEASE_LOGIN').'</div>';
		}
		$tabO	.= '</div>';
		$active = $activeTab = '';
	}

	// RELATED PRODUCTS

	if (!empty($this->t['rel_products'])) {
        foreach (ContentTypeHelper::getContentTypes(ContentTypeHelper::ProductRelated) as $relatedType) {
            if (!$relatedType->params->get('display_in_product', 1)) {
                continue;
            }

            $related = array_filter($this->t['rel_products'], function($relatedProduct) use ($relatedType) {
               return $relatedProduct->related_type === $relatedType->id;
            });

            if (!$related) {
                continue;
            }



            $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phrelated-' . $relatedType->id ;
            $tabLiO .= '<li class="' . $this->s['c']['nav-item'] . ' ' . $activeTab . '"><a href="'. $tabAnchor . '" data-bs-toggle="tab" class="' . $this->s['c']['nav-link'] . ' ' . $active . '">' . Text::_($relatedType->title) . '</a></li>';

            $tabO .= '<div class="' . $this->s['c']['tabpane'] . ' ph-tab-pane ' . $active . '" id="phrelated-' . $relatedType->id . '">';

            $tabO .= '<div class="' . $this->s['c']['row'] . '">';
            foreach ($related as $k => $v) {

                // This should not happen but if the product is the same like related, don't display it.
                if (isset($x->id) && isset($v->id) && (int)$x->id == (int)$v->id) {
                    continue;
                }
                $tabO .= '<div class="' . $this->s['c']['row-item'] . ' ' . $this->s['c']['col.xs12.sm3.md3'] . '">';
                $tabO .= '<div class="ph-item-box grid ph-item-thumbnail-related">';


                $tabO .= '<div class="' . PhocacartRenderFront::completeClass(array($this->s['c']['thumbnail'], 'ph-thumbnail', 'ph-thumbnail-c', 'ph-item')) . '">';
                $tabO .= '<div class="ph-item-content">';

                $image = PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');

                // Try to find the best menu link
                if (isset($v->catid_pref) && (int) $v->catid_pref > 0 && isset($v->catalias_pref) && $v->catalias_pref != '') {

                    // PREFERRED CATEGORY SET BY VENDER in product edit (catid column administration)
                    $link = Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid_pref, $v->alias, $v->catalias_pref));

                } else if (isset($v->catid_sel) && (int) $v->catid_sel > 0 && isset($v->catalias_sel) && $v->catalias_sel != '') {

                    // SELECTED CATEGORY SET BY USER in product view (frontend)
                    $link = Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid_sel, $v->alias, $v->catalias_sel));
                } else {

                    // RANDOM CATEGORY ORDERED BY ORDERING
                    $link = Route::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
                }

                $tabO .= '<a href="' . $link . '">';
                if (isset($image->rel) && $image->rel != '') {
                    /*$tabO	.= '<img src="'.Uri::base(true).'/'.$image->rel.'" alt="" class="'.$this->s['c']['img-responsive'].' ph-image"';
                    if (isset($this->t['image_width']) && $this->t['image_width'] != '' && isset($this->t['image_height']) && $this->t['image_height'] != '') {
                        $tabO	.= ' style="width:'.$this->t['image_width'].';height:'.$this->t['image_height'].'"';
                    }
                    $tabO	.= ' />';*/

                    $d                    = array();
                    $d['t']               = $this->t;
                    $d['s']               = $this->s;
                    $d['src']             = Uri::base(true) . '/' . $image->rel;
                    $d['srcset-webp']     = Uri::base(true) . '/' . $image->rel_webp;
                    $d['data-image']      = Uri::base(true) . '/' . $image->rel;
                    $d['data-image-webp'] = Uri::base(true) . '/' . $image->rel_webp;
                    $d['alt-value']       = PhocaCartImage::getAltTitle($v->title, $image->rel);
                    $d['class']           = PhocacartRenderFront::completeClass(array($this->s['c']['img-responsive'], 'img-thumbnail', 'ph-image-full', 'phImageFull', 'phjProductImage' . ''));
                    $d['style']           = '';
                    /*if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
                        $d['style'] = 'width:' . $this->t['image_width'] . 'px;height:' . $this->t['image_height'] . 'px';
                    }*/

                    $tabO .= $layoutI->render($d);

                }
                $tabO .= '</a>';
                $tabO .= '<div class="' . $this->s['c']['caption'] . '"><h4><a href="' . $link . '">' . $v->title . '</a></h4></div>';

                $tabO .= '<div class="">';
                $tabO .= '<a href="' . $link . '" class="' . $this->s['c']['btn.btn-primary.btn-sm'] . ' ph-btn" role="button">';
                //$tabO   .= '<span class="'.$this->s['i']['view-product'].'"></span> ';
                $tabO .= PhocacartRenderIcon::icon($d['s']['i']['ok'], '', ' ');
                $tabO .= Text::_('COM_PHOCACART_VIEW_PRODUCT') . '</a>';
                $tabO .= '</div>';

                $tabO .= '</div>';
                $tabO .= '</div>';

                $tabO .= '</div>';
                $tabO .= '</div>';


            }
            $tabO   .= '</div>';
            $tabO   .= '</div>';
            $active = $activeTab = '';
        }
	}

	// PRICE HISTORY
	if ($this->t['enable_price_history'] && $this->t['price_history_data']) {
        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#phpricehistory';
		$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.Text::_('COM_PHOCACART_PRICE_HISTORY').'</a></li>';

		$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="phpricehistory">';
		$tabO	.= '<div class="'.$this->s['c']['row'].'">';

		$tabO	.= '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-cpanel-chart-box">';
		$tabO	.= '<div id="phChartAreaLineHolder" class="ph-chart-canvas-holder" style="width:95%" >';
        $tabO	.= '<canvas id="phChartAreaLine" class="ph-chart-area-line"></canvas>';
		$tabO	.= '</div>';
		$tabO	.= '</div>';


		$tabO	.= '</div>';
		$tabO	.= '</div>';

	}

	// TABS CUSTOM FIELDS
	$cFG = [];
    $fields = PhocacartFields::getProductFields($x);
    foreach ($fields as $fieldsGroup) {
        $alias = 'field-' . $fieldsGroup->id;
        $title = $fieldsGroup->title ?: Text::_('COM_PHOCACART_PRODUCT_CUSTOM_FIELDS');

        $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#' . $alias;
        $tabLiO .= '<li class="' . $this->s['c']['nav-item'] . ' ' . $activeTab.'"><a href="'.$tabAnchor. '" data-bs-toggle="tab" class="' . $this->s['c']['nav-link'] . ' ' . $active . '">' . $title . '</a></li>';
        $tabO 	.= '<div class="' . $this->s['c']['tabpane'] . ' ph-tab-pane ' . $active . '" id="' . $alias . '">';
        $tabO	.= '<div class="' . $this->s['c']['row'] . '">';
        foreach ($fieldsGroup->fields as $field) {
            if (!empty($field->value)) {
                $tabO .= '<div class="' . $this->s['c']['col.xs12.sm4.md4'] . ' ph-cf-title">';
                $tabO .= isset($field->title) ? $field->title : '';
                $tabO .= '</div>';

                $tabO .= '<div class="' . $this->s['c']['col.xs12.sm6.md6'] . ' ph-cf-value">';
                $tabO .= $field->value;
                $tabO .= '</div>';
            }
        }
        $tabO	.= '</div>';
        $tabO	.= '</div>';
        $active = $activeTab = '';
    }

	// TABS PLUGIN
	if (!empty($this->t['event']->onItemInsideTabPanel) && is_array($this->t['event']->onItemInsideTabPanel)) {
		foreach($this->t['event']->onItemInsideTabPanel as $k => $v) {
			if (isset($v['title']) && isset($v['alias']) && isset($v['content'])) {
                $tabAnchor = $this->s['c']['class-type'] == 'uikit' ? '#' : '#'.strip_tags($v['alias']);
				$tabLiO .= '<li class="'.$this->s['c']['nav-item'].' '.$activeTab.'"><a href="'.$tabAnchor.'" data-bs-toggle="tab" class="'.$this->s['c']['nav-link'].' '.$active.'">'.$v['title'].'</a></li>';
				$tabO 	.= '<div class="'.$this->s['c']['tabpane'].' ph-tab-pane '.$active.'" id="'.strip_tags($v['alias']).'">';
				$tabO	.= $v['content'];
				$tabO	.= '</div>';
				$active = $activeTab = '';
			}
		}
	}


	if ($tabLiO != '') {
		echo '<ul class="'.$this->s['c']['tabnav'].'" id="PcItemTab"'.$this->s['a']['tab'].'>';
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

	// TAGS
	if ($this->t['tags_output'] != '') {

		echo '<div class="ph-cb"></div>';

		echo '<div class="ph-item-tag-box">';
		echo '<h3>'.Text::_('COM_PHOCACART_TAGS').'</h3>';
		echo $this->t['tags_output'];
		echo '</div>';

	}

	// PARAMETERS
	if ($this->t['parameters_output'] != '') {

		echo '<div class="ph-cb"></div>';
		echo '<div class="ph-item-parameter-box">';
		echo $this->t['parameters_output'];
		echo '</div>';


	}

	echo '<div class="ph-cb"></div>';

}

if ((isset($this->itemnext[0]) && $this->itemnext[0]) || (isset($this->itemprev[0]) && $this->itemprev[0])) {
	echo '<div class="'.$this->s['c']['row'].' ph-item-navigation">';

	echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-item-navigation-box ph-item-navigation-box-prev">';
	if(isset($this->itemprev[0]) && $this->itemprev[0]) {
		$p = $this->itemprev[0];

		$title 	= '';
		$titleT = Text::_('COM_PHOCACART_PREVIOUS_PRODUCT'). ' ('. $p->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = Text::_('COM_PHOCACART_PREVIOUS_PRODUCT');
		} else if ($this->t['title_next_prev'] == 3) {
			$title = $p->title;
		}
		$linkPrev = Route::_(PhocacartRoute::getItemRoute($p->id, $p->categoryid, $p->alias, $p->categoryalias));
		echo '<div class="ph-button-prev-box">';
		echo '<a href="'.$linkPrev.'" class="'.$this->s['c']['btn.btn-default'].' ph-item-navigation" role="button" title="'.$titleT.'">'
		//.'<span class="'.$this->s['i']['prev'].'"></span> '
		. PhocacartRenderIcon::icon($this->s['i']['prev'], '', ' ')
		.$title
		.'</a>';
		echo '</div>';
	}
	echo '</div>';

	/*echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' ph-item-navigation-box">';
	echo '</div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm2.md2'].' ph-item-navigation-box">';
	echo '</div>';*/

	echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-item-navigation-box ph-item-navigation-box-next">';
	if(isset($this->itemnext[0]) && $this->itemnext[0]) {
		$n = $this->itemnext[0];
		$title 	= '';
		$titleT = Text::_('COM_PHOCACART_NEXT_PRODUCT'). ' ('. $n->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = Text::_('COM_PHOCACART_NEXT_PRODUCT');
		} else if ($this->t['title_next_prev'] == 3) {
			$title = $n->title;
		}
		$linkNext = Route::_(PhocacartRoute::getItemRoute($n->id, $n->categoryid, $n->alias, $n->categoryalias));
		echo '<div class="ph-button-next-box">';
		echo '<a href="'.$linkNext.'" class="'.$this->s['c']['btn.btn-default'].' ph-item-navigation" role="button" title="'.$titleT.'">'.$title
		//.' <span class="'.$this->s['i']['next'].'"></span>'
		. PhocacartRenderIcon::icon($this->s['i']['next'], '', '', ' ')
		.'</a>';
		echo '</div>';
	}
	echo '</div>';

	echo '</div>';
}

echo '</div>';
echo '<div id="phContainer"></div>';

if ($popupAskAQuestion == 2) {
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

