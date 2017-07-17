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
$layoutA	= new JLayoutFile('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutQV 	= new JLayoutFile('popup_quickview', null, array('component' => 'com_phocacart'));
$layoutAtOS	= new JLayoutFile('attribute_options_select', null, array('component' => 'com_phocacart'));
$layoutAtOC	= new JLayoutFile('attribute_options_checkbox', null, array('component' => 'com_phocacart'));

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

$label 	= PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);

// IMAGE


$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
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
	echo '<a href="javascript:void(0);" '.$this->t['image_rel'].'>';
	echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive '.$label['cssthumbnail2'].' ph-image-full"';
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


// :L: PRICE
$price 	= new PhocacartPrice;// Can be used by options
if ($this->t['hide_price'] != 1) {
	
	$d					= array();
	$d['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1);
	
	$d['priceitemsorig']= array();
	if ($x->price_original != '' && $x->price_original > 0) {
		$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype);
	}
	$d['class']	= 'ph-item-price-box';
	echo '<h1>'.$x->title.'</h1>';
	echo '<div id="phItemPriceBox">';
	echo$layoutP->render($d);
	echo '</div>';
}

// STOCK
if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count']) {
	
	echo '<div class="ph-item-stock-box">';
	echo '<div class="ph-stock-txt">'.JText::_('COM_PHOCACART_AVAILABILITY').'</div>';
	echo '<div class="ph-stock">'.JText::_($this->t['stock_status_output']).'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}

if($this->t['stock_status']['min_quantity']) {
	
	echo '<div class="ph-item-min-qty-box">';
	echo '<div class="ph-min-qty-txt">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY').'</div>';
	echo '<div class="ph-min-qty">'.$this->t['stock_status']['min_quantity'].'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
	
}

if($this->t['stock_status']['min_multiple_quantity']) {
	
	echo '<div class="ph-item-min-qty-box">';
	echo '<div class="ph-min-qty-txt">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY').'</div>';
	echo '<div class="ph-min-qty">'.$this->t['stock_status']['min_multiple_quantity'].'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
	
}

// This form can get two events:
// when option selected - price or image is changed id=phItemPriceBoxForm
// when ajax cart is active and submit button is clicked class=phItemCartBoxForm
//

echo '<form id="phItemPriceBoxForm" action="'.$this->t['linkcheckout'].'" method="post" class="phItemCartBoxForm form-inline" role="form" data-id="'.(int)$x->id.'">';

// data-id="'.(int)$x->id.'" - needed for dynamic change of price in quick view, we need to get the ID per javascript
// because Quick View = Items, Category View and there are more products listed, not like in item id

// ATTRIBUTES, OPTIONS
if (!empty($this->t['attr_options']) && $this->t['hide_attributes'] != 1) {
	
	echo PhocacartRenderJs::renderPhSwapImageInitialize(1, $this->t['dynamic_change_image'], 1);
	
	echo '<div class="ph-item-attributes-box" id="phItemAttributesBox">';
	echo '<h4>'.JText::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';
	
	foreach ($this->t['attr_options'] as $k => $v) {
		
		
		// SELECTBOX COLOR, SELECTBOX IMAGE
		if ($v->type == 2 || $v->type == 3) {
			echo PhocacartRenderJs::renderPhAttributeSelectBoxInitialize((int)$v->id, (int)$v->type, 1);
		}
		
		// If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
		// Set jquery required validation, which should help to html 5 in case of checkboxes (see more info in the funtion)
		// TYPES SET for JQUERY require control: 4 5 6
		$req = PhocacartRenderJs::renderRequiredParts((int)$v->id, (int)$v->required);
		
		// HTML5 does not know to check checkboxes - if some value is set
		// CHECKBOX, CHECKBOX COLOR, CHECKBOX IMAGE
		if($v->type == 4 || $v->type == 5 || $v->type == 6) {
			echo PhocacartRenderJs::renderCheckBoxRequired((int)$v->id, 1);	
		}
		
		echo '<div class="ph-attribute-title">'.$v->title.$req['span'].'</div>';
		if(!empty($v->options)) {
			
			$d							= array();
			$d['attribute']				= $v;
			$d['required']				= $req;
			$d['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
			$d['pathitem']				= $this->t['pathitem'];
			$d['price']					= $price;

			if ($v->type == 1 || $v->type == 2 || $v->type == 3) {
				echo$layoutAtOS->render($d);// SELECTBOX, SELECTBOX COLOR, SELECTBOX IMAGE
			} else if ($v->type == 4 || $v->type == 5 || $v->type == 6) {
				echo$layoutAtOC->render($d);// CHECKBOX, CHECKBOX COLOR, CHECKBOX COLOR
			}
		}
		
	}
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}

// :L: ADD TO CART
if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {
	
	$d					= array();
	$d['id']			= (int)$x->id;
	$d['catid']			= $this->t['catid'];
	$d['return']		= $this->t['actionbase64'];
	$d['addtocart']		= $this->t['item_addtocart'];
	echo$layoutA->render($d);

} else if ((int)$this->t['item_addtocart'] == 2 && (int)$x->external_id != '') {
	$d					= array();
	$d['external_id']	= (int)$x->external_id;
	$d['return']		= $this->t['actionbase64'];
	
	echo$layoutA2->render($d);
}

echo '</form>';
echo '<div class="ph-cb"></div>';


echo '</div>';// end right side price panel
echo '</div>';// end row	
			
		
        ?></div>
		<div class="modal-footer"></div>
	   </div>
    </div>
</div>