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
$layoutA	= new JLayoutFile('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new JLayoutFile('button_external_link', null, array('component' => 'com_phocacart'));
$layoutQ	= new JLayoutFile('button_ask_question', null, array('component' => 'com_phocacart'));
$layoutAtOS	= new JLayoutFile('attribute_options_select', null, array('component' => 'com_phocacart'));
$layoutAtOC	= new JLayoutFile('attribute_options_checkbox', null, array('component' => 'com_phocacart'));
$layoutPD	= new JLayoutFile('button_public_download', null, array('component' => 'com_phocacart'));
$layoutEL	= new JLayoutFile('link_external_link', null, array('component' => 'com_phocacart'));

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
		.'<a class="btn btn-success" title="'.$linkUpText.'" href="'. $linkUp.'" ><span class="glyphicon glyphicon-arrow-left"></span> '.JText::_($linkUpText).'</a></div>';
	}
}

$title = '';
if (isset($this->item[0]->title) && $this->item[0]->title != '') {
	$title = $this->item[0]->title;
}
echo PhocacartRenderFront::renderHeader(array($title));



if ( isset($this->item[0]->description) && $this->item[0]->description != '') {
	echo '<div class="ph-desc">'. JHTML::_('content.prepare', $this->item[0]->description). '</div>';
}

$x = $this->item[0];
if (!empty($x)) {
	echo '<div class="row">';
	
	// === IMAGE PANEL
	echo '<div id="phImageBox" class="col-xs-12 col-sm-6 col-md-6">';
	
	$label 	= PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);
	
	
	// IMAGE
	echo '<div class="ph-item-image-full-box '.$label['cssthumbnail'].'">';
	
	echo $label['new'] . $label['hot'] . $label['feat'];
	
	$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$link 	= JURI::base(true).'/'.$imageL->rel;
		
		
	
	if (isset($image->rel) && $image->rel != '') {
		echo '<a href="'.$link.'" '.$this->t['image_rel'].'>';
		echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive '.$label['cssthumbnail2'].' ph-image-full"';
		if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
			echo ' style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px"';
		}
		echo ' />';
		echo '</a>';
	}
	echo '</div>'. "\n";
	
	// ADDITIONAL IMAGES
	if (!empty($this->t['add_images'])) {
		
		echo '<div class="row ph-item-image-add-box">';
		
		
		foreach ($this->t['add_images'] as $v2) {
			echo '<div class="col-xs-12 col-sm-4 col-md-4 ph-item-image-box">';
			$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'small');
			$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'large');
			$link 	= JURI::base(true).'/'.$imageL->rel;
			echo '<a href="'.$link.'" '.$this->t['image_rel'].'>';
			echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive img-thumbnail ph-image-full" />';
			echo '</a>';
			echo '</div>';
		}
		
		echo '</div>';// end additional images
	}
	
	echo '</div>';// end image panel
	
	// === PRICE PANEL
	echo '<div class="col-xs-12 col-sm-6 col-md-6 ph-item-price-panel">';
	
	

	// :L: PRICE
	$price 				= new PhocacartPrice;// Can be used by options
	if ($this->t['hide_price'] != 1) {
		
		$d					= array();
		$d['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1);
		
		$d['priceitemsorig']= array();
		if ($x->price_original != '' && $x->price_original > 0) {
			$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype);
		}
		$d['class']			= 'ph-item-price-box';
		echo '<div id="phItemPriceBox">';
		echo $layoutP->render($d);
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
	
	echo '<form id="phItemPriceBoxForm" action="'.$this->t['linkcheckout'].'" method="post" class="phItemCartBoxForm form-inline">';
	
	// ATTRIBUTES, OPTIONS
	if (!empty($this->t['attr_options']) && $this->t['hide_attributes'] != 1) {
		
		PhocacartRenderJs::renderPhSwapImageInitialize(1, $this->t['dynamic_change_image']);
		
		echo '<div class="ph-item-attributes-box" id="phItemAttributesBox">';
		echo '<h4>'.JText::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';
		
		foreach ($this->t['attr_options'] as $k => $v) {
			
			
			// SELECTBOX COLOR, SELECTBOX IMAGE
			if ($v->type == 2 || $v->type == 3) {
				PhocacartRenderJs::renderPhAttributeSelectBoxInitialize((int)$v->id, (int)$v->type);
			}
			
			// If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
			// Set jquery required validation, which should help to html 5 in case of checkboxes (see more info in the funtion)
			// TYPES SET for JQUERY require control: 4 5 6
			$req = PhocacartRenderJs::renderRequiredParts((int)$v->id, (int)$v->required );
			
			// HTML5 does not know to check checkboxes - if some value is set
			// CHECKBOX, CHECKBOX COLOR, CHECKBOX IMAGE
			if($v->type == 4 || $v->type == 5 || $v->type == 6) {
				PhocacartRenderJs::renderCheckBoxRequired((int)$v->id);	
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
					echo $layoutAtOS->render($d);// SELECTBOX, SELECTBOX COLOR, SELECTBOX IMAGE
				} else if ($v->type == 4 || $v->type == 5 || $v->type == 6) {
					echo $layoutAtOC->render($d);// CHECKBOX, CHECKBOX COLOR, CHECKBOX COLOR
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
		echo $layoutA->render($d);

	} else if ((int)$this->t['item_addtocart'] == 2 && (int)$x->external_id != '') {
		$d					= array();
		$d['external_id']	= (int)$x->external_id;
		$d['return']		= $this->t['actionbase64'];
		
		echo $layoutA2->render($d);
	} else if ((int)$this->t['item_addtocart'] == 3 && $x->external_link != '') {
		$d					= array();	
		$d['external_link']	= $x->external_link;
		$d['external_text']	= $x->external_text;
		$d['return']		= $this->t['actionbase64'];
		echo $layoutA3->render($d);
			
	}
	
	echo '</form>';
	echo '<div class="ph-cb"></div>';
	
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
	


	
	if (isset($x->manufacturertitle) && $x->manufacturertitle != '') {
		echo '<div class="ph-item-manufacturer-box">';
		echo '<div class="ph-manufacturer-txt">'.JText::_('COM_PHOCACART_MANUFACTURER').':</div>';
		echo '<div class="ph-manufacturer">'.$x->manufacturertitle.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	
	if (((int)$this->t['item_askquestion'] == 1) || ($this->t['item_askquestion'] == 2 && (int)$this->t['item_addtocart'] == 0)) {
			
		$d					= array();
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
	}
	
	// TAGS
	if ($this->t['tags_output'] != '') {
		echo '<div class="ph-item-tag-box">';
		echo $this->t['tags_output'];
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	
	if ($this->t['display_compare'] == 1 || $this->t['display_wishlist'] == 1) {
		echo '<div class="ph-cb"></div>';
	}
	
	// :L: COMPARE
	if ($this->t['display_compare'] == 1) {
		$d			= array();
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
		$d['linkw']	= $this->t['linkwishlist'];
		$d['id']	= (int)$x->id;
		$d['catid']	= (int)$x->catid;
		$d['return']= $this->t['actionbase64'];
		$d['method']= $this->t['add_wishlist_method'];
		echo $layoutW->render($d);
	}
	
	echo '<div class="clearfix"></div>';
	
	// :L: PUBLIC DOWNLOAD
	if ($this->t['display_public_download'] == 1 && $x->public_download_file != '') {
		$d					= array();
		$d['linkdownload']	= $this->t['linkdownload'];
		$d['id']			= (int)$x->id;
		$d['return']		= $this->t['actionbase64'];
		$d['title']			= '';
		if ($x->public_download_text != '') {
			$d['title']		= $x->public_download_text;
		}
		
		echo '<div class="clearfix"></div>';
		echo $layoutPD->render($d);
	}
	
	// :L: EXTERNAL LINK
	if ($this->t['display_external_link'] == 1 && $x->external_link != '') {
		$d					= array();
		$d['linkexternal']	= $x->external_link;
		//$d['id']			= (int)$x->id;
		//$d['return']		= $this->t['actionbase64'];
		$d['title']			= '';
		if ($x->external_text != '') {
			$d['title']		= $x->external_text;
		}
		
		echo '<div class="clearfix"></div>';
		echo $layoutEL->render($d);
	}
	

	echo '</div>';// end right side
	
	echo '</div>';// end row 1
	
	echo '<div class="row ph-item-bottom-box">';
	
	
	
	// TABS
	$active = 'active in';
	$tabO	= '';
	$tabLiO	= '';

	// DESCRIPTION
	if (isset($x->description_long) && $x->description_long != '') {
		$tabLiO .= '<li class="'.$active.'"><a href="#phdescription" data-toggle="tab">'.JText::_('COM_PHOCACART_DESCRIPTION').'</a></li>';
		
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phdescription">';
		$tabO	.= JHTML::_('content.prepare', $x->description_long);
		$tabO	.= '</div>';
		$active = '';
	}

	// VIDEO
	if (isset($x->video) && $x->video != '') {
		$tabLiO .= '<li class="'.$active.'"><a href="#phvideo" data-toggle="tab">'.JText::_('COM_PHOCACART_VIDEO').'</a></li>';
		
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phvideo">';
		$tabO	.= PhocacartRenderFront::displayVideo($x->video);
		$tabO	.= '</div>';
		$active = '';
	}
	
	// SPECIFICATION
	if (!empty($this->t['specifications'])){
		$tabLiO .= '<li class="'.$active.'"><a href="#phspecification" data-toggle="tab">'.JText::_('COM_PHOCACART_SPECIFICATIONS').'</a></li>';
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phspecification">';
		
		
		foreach($this->t['specifications'] as $k => $v) {
			if(isset($v[0]) && $v[0] != '') {
				$tabO	.= '<h4 class="ph-spec-group-title">'.$v[0].'</h4>';
				unset($v[0]);
			}	
			
			if (!empty($v)) {
				foreach($v as $k2 => $v2) {
					$tabO	.= '<div class="row">';
					$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5">';
					$tabO	.= '<div class="ph-spec-title">'.$v2['title'].'</div>';
					$tabO	.= '</div>';
					
					$tabO	.= '<div class="col-xs-12 col-sm-7 col-md-7">';
					$tabO	.= '<div class="ph-spec-value">'.$v2['value'].'</div>';
					$tabO	.= '</div>';
					$tabO	.= '</div>';
				}
			
			}
		}
		
		$tabO	.= '</div>';
		$active = '';
	}
	
	
	// REVIEWS
	if ($this->t['enable_review'] > 0) {
		$tabLiO .= '<li class="'.$active.'"><a href="#phreview" data-toggle="tab">'.JText::_('COM_PHOCACART_REVIEWS').'</a></li>';
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phreview">';
		
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
			
			
			$tabO	.= '<form action="'.$this->t['linkitem'].'" method="post">';
			// ROW
			$tabO	.= '<div class="row">';
			
			$tabO	.= '<div class="col-xs-12 col-sm-2 col-md-2">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_RATING').'</div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-10 col-md-10 ph-rating-box">';
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
			$tabO	.= '<div class="row">';
			
			$tabO	.= '<div class="col-xs-12 col-sm-2 col-md-2">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_NAME').'</div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5">';
			$tabO	.= '<div class="ph-review-value"><input type="text" name="name" class="form-control" value="'. $this->u->name .'" /></div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5"></div>';

			$tabO	.= '</div>';
			
			// ROW
			$tabO	.= '<div class="row">';
			
			$tabO	.= '<div class="col-xs-12 col-sm-2 col-md-2">';
			$tabO	.= '<div class="ph-review-title">'.JText::_('COM_PHOCACART_REVIEW').'</div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5">';
			$tabO	.= '<div class="ph-review-value"><textarea class="form-control" name="review" rows="3"></textarea></div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5"></div>';
			
			$tabO	.= '</div>';
			
			// ROW
			$tabO	.= '<div class="row">';
			
			$tabO	.= '<div class="col-xs-12 col-sm-2 col-md-2"></div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5">';
			$tabO	.= '<div class="pull-right">';
			$tabO	.= '<button class="btn btn-primary btn-sm ph-btn"><span class="glyphicon glyphicon-edit"></span> '.JText::_('COM_PHOCACART_SUBMIT').'</button>';
			$tabO	.= '</div>';
			$tabO	.= '</div>';
			
			$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5"></div>';
			
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
		$active = '';
	}
	
	// RELATED PRODUCTS
	if (!empty($this->t['rel_products'])) {
		$tabLiO .= '<li class="'.$active.'"><a href="#phrelated" data-toggle="tab">'.JText::_('COM_PHOCACART_RELATED_PRODUCTS').'</a></li>';
		
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phrelated">';
		
		$tabO	.= '<div class="row">';
		
		foreach($this->t['rel_products'] as $k => $v) {
			$tabO	.= '<div class="col-xs-12 col-sm-3 col-md-3">';
			$tabO	.= '<div class="thumbnail ph-item-thumbnail-related">';
			$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');
			
			// Try to find the best menu link
			if (isset($v->catid2) && (int)$v->catid2 > 0 && isset($v->catalias2) && $v->catalias2 != '') {
				$link 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid2, $v->alias, $v->catalias2));
			} else {
				$link 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
			}
			$tabO	.= '<a href="'.$link.'">';
			if (isset($image->rel) && $image->rel != '') {
				$tabO	.= '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image"';
				if (isset($this->t['image_width']) && $this->t['image_width'] != '' && isset($this->t['image_height']) && $this->t['image_height'] != '') {
					$tabO	.= ' style="width:'.$this->t['image_width'].';height:'.$this->t['image_height'].'"';
				}
				$tabO	.= ' />';
			}
			$tabO	.= '</a>';
			$tabO	.= '<div class="caption"><h4><a href="'.$link.'">'.$v->title.'</a></h4></div>';
			
			$tabO	.= '<div>';
			$tabO	.= '<a href="'.$link.'" class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-search"></span> '.JText::_('COM_PHOCACART_VIEW_PRODUCT').'</a>';
			$tabO	.= '</div>';
			
			$tabO	.= '</div>';
			$tabO	.= '</div>';
		}
		$tabO	.= '</div>';
		$tabO	.= '</div>';
		$active = '';
	}
	
	
	
	if ($tabLiO != '') {
		echo '<ul class="nav nav-tabs">';
		echo $tabLiO;
		echo '</ul>';
	}
	
	if ($tabO != '') {
		echo '<div class="tab-content">';
		echo $tabO;
		echo '</div>';
	}
	

	
	echo '</div>'; // end row 2 (bottom)
	
	
}

if ($this->itemnext[0] || $this->itemprev[0]) {
	echo '<div class="row"><div class="col-sm-4 col-md-4 ph-item-navigation-box">';
	if($this->itemprev[0]) {
		$p = $this->itemprev[0];
		$title 	= '';
		$titleT = JText::_('COM_PHOCACART_PREVIOUS_PRODUCT'). ' ('. $p->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = JText::_('COM_PHOCACART_PREVIOUS_PRODUCT');
		}
		$linkPrev = JRoute::_(PhocacartRoute::getItemRoute($p->id, $p->categoryid, $p->alias, $p->categoryalias));
		echo '<div class="pull-left"><a href="'.$linkPrev.'" class="btn btn-default ph-item-navigation" role="button" title="'.$titleT.'"><span class="glyphicon glyphicon-arrow-left"></span> '.$title.'</a></div>';
	}
	echo '</div>';
	
	echo '<div class="col-sm-2 col-md-2 ph-item-navigation-box">';
	echo '</div>';
	
	echo '<div class="col-sm-2 col-md-2 ph-item-navigation-box">';
	echo '</div>';
	
	echo '<div class="col-sm-4 col-md-4 ph-item-navigation-box">';
	if($this->itemnext[0]) {
		$n = $this->itemnext[0];
		$title 	= '';
		$titleT = JText::_('COM_PHOCACART_NEXT_PRODUCT'). ' ('. $n->title.')';
		if ($this->t['title_next_prev'] == 1) {
			$title = $titleT;
		} else if ($this->t['title_next_prev'] == 2) {
			$title = JText::_('COM_PHOCACART_NEXT_PRODUCT');
		}
		$linkNext = JRoute::_(PhocacartRoute::getItemRoute($n->id, $n->categoryid, $n->alias, $n->categoryalias));
		echo '<div class="pull-right"><a href="'.$linkNext.'" class="btn btn-default ph-item-navigation" role="button" title="'.$titleT.'">'.$title.' <span class="glyphicon glyphicon-arrow-right"></span></a></div>';
	}
	echo '</div></div>';
}

echo '</div>';
echo '<div id="phContainer"></div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtils::getInfo();
?>