<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-item-box" class="pc-item-view'.$this->p->get( 'pageclass_sfx' ).'">';

if (isset($this->category[0]->id) && ($this->t['display_back'] == 2 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->id > 0) {
		$linkUp = JRoute::_(PhocaCartRoute::getCategoryRoute($this->category[0]->id, $this->category[0]->alias));
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

echo '<h1>';
if ($this->p->get('show_page_heading')) { 
	 $this->escape($this->p->get('page_heading'));
}
if ( isset($this->item[0]->title) && $this->item[0]->title != '') {
	if ($this->p->get('show_page_heading')) { echo ' - ';}
	echo $this->item[0]->title;
}
echo '</h1>';


if ( isset($this->item[0]->description) && $this->item[0]->description != '') {
	echo '<div class="ph-desc">'. $this->item[0]->description. '</div>';
}

$x = $this->item[0];
if (!empty($x)) {
	echo '<div class="row">';
	
	// === IMAGE PANEL
	echo '<div class="col-xs-12 col-sm-6 col-md-6">';
	
	// IMAGE
	echo '<div class="ph-item-image-full-box">';
	$image 	= PhocaCartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$imageL = PhocaCartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$link 	= JURI::base(true).'/'.$imageL->rel;
			
	if (isset($image->rel) && $image->rel != '') {
		echo '<a href="'.$link.'" rel="prettyPhoto[pc_gal1]">';
		echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive img-thumbnail ph-image-full"';
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
			echo '<div class="col-xs-12 col-sm-4 col-md-4">';
			$image 	= PhocaCartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'small');
			$imageL = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'large');
			$link 	= JURI::base(true).'/'.$imageL->rel;
			echo '<a href="'.$link.'" rel="prettyPhoto[pc_gal1]">';
			echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive img-thumbnail ph-image-full" />';
			echo '</a>';
			echo '</div>';
		}
		
		echo '</div>';// end additional images
	}
	
	echo '</div>';// end image panel
	
	// === PRICE PANEL
	echo '<div class="col-xs-12 col-sm-6 col-md-6 ph-item-price-panel">';
	
	// PRICE
	$price 		= new PhocaCartPrice;
	$priceItems	= $price->getPriceItems($x->price, $x->taxrate, $x->taxcalculationtype, $x->taxtitle);
	
	echo '<div class="ph-item-price-box">';
	if ($x->price_original != '' && $x->price_original > 0) {
		$priceItemsOriginal = $price->getPriceItems($x->price_original, $x->taxrate, $x->taxcalculationtype);
		if ($priceItemsOriginal['brutto']) {
			echo '<div class="ph-price-txt">'.JText::_('COM_PHOCACART_ORIGINAL_PRICE').'</div>';
			echo '<div class="ph-price-original">'.$priceItemsOriginal['bruttoformat'].'</div>';
		}
	
	}
	if ($priceItems['netto']) {
		echo '<div class="ph-price-txt">'.$priceItems['nettotxt'].'</div>';
		echo '<div class="ph-price-netto">'.$priceItems['nettoformat'].'</div>';
	}
	if ($priceItems['tax']) {
		echo '<div class="ph-tax-txt">'.$priceItems['taxtxt'].'</div>';
		echo '<div class="ph-tax">'.$priceItems['taxformat'].'</div>';
	}
	if ($priceItems['brutto']) {
		echo '<div class="ph-price-txt">'.$priceItems['bruttotxt'].'</div>';
		echo '<div class="ph-price-brutto">'.$priceItems['bruttoformat'].'</div>';
	}
	echo '</div>';
	echo '<div class="ph-cb"></div>';
	
	
	// STOCK
	
	if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count']) {
		
		echo '<div class="ph-item-stock-box">';
		echo '<div class="ph-stock-txt">'.JText::_('COM_PHOCACART_AVAILABILITY').'</div>';
		
		echo '<div class="ph-stock">'.$this->t['stock_status_output'].'</div>';
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
	
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-inline" role="form">';
	
	// ATTRIBUTES, OPTIONS
	if (!empty($this->t['attr_options'])) {
		echo '<div class="ph-item-attributes-box">';
		echo '<h4>'.JText::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';
		
		
		
		foreach ($this->t['attr_options'] as $k => $v) {
			
			// If the attribute is required
			$req 	= '';
			$reqS	= '';
			if (isset($v->required) && $v->required == 1) {
				
				$req = ' required="" aria-required="true"';
				$reqS = '<span class="ph-req">*</span>';
			}

			
			echo '<div class="ph-attribute-title">'.$v->title.$reqS.'</div>';
			if(!empty($v->options)) {
			
				
			
				echo '<select name="attribute['.$v->id.']" class="form-control chosen-select ph-item-input-select-attributes" '.$req.'>';
				echo '<option value="">Select Option</option>';
				
				foreach ($v->options as $k2 => $v2) {
					if($v2->operator == '=') {
						$operator = '';
					} else {
						$operator = $v2->operator;
					}
					$amount = $price->getPriceFormat($v2->amount);
					echo '<option value="'.$v2->id.'">'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')</option>';
				}
				
				echo '</select>';
			}
			
		}
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	
	// ADD TO CART BUTTON
	if ($this->t['display_addtocart'] == 1) {
		echo '<div class="pull-right ph-item-add-to-cart-box">';
		
		echo '<input type="hidden" name="id" value="'.(int)$x->id.'">';
		echo '<input type="hidden" name="task" value="checkout.add">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
		echo '<div class="form-group">';
		echo '<label>'.JText::_('COM_PHOCACART_QTY').': </label> ';
		echo '<input class="form-control ph-input-quantity" type="text" name="quantity" value="1" />';
		echo '</div>';
		echo ' <div class="form-group">';
		echo '<button type="submit" class="btn btn-primary ph-btn" role="button"><span class="glyphicon glyphicon-shopping-cart"></span> '.JText::_('COM_PHOCACART_ADD_TO_CART').'</button>';
		//echo '<input type="submit" value="submit" name="submit" role="btn btn-default" />';
		echo '</div>';
		echo JHtml::_('form.token');
		echo '</div>';
	}
	
	echo '</form>';
	echo '<div class="ph-cb"></div>';
	
	if (isset($x->manufacturertitle) && $x->manufacturertitle != '') {
		echo '<div class="ph-item-manufacturer-box">';
		echo '<div class="ph-manufacturer-txt">'.JText::_('COM_PHOCACART_MANUFACTURER').'</div>';
		echo '<div class="ph-manufacturer">'.$x->manufacturertitle.'</div>';
		echo '</div>';
		echo '<div class="ph-cb"></div>';
	}
	
	// TAGS
	if ($this->t['tags_output'] != '') {
		echo '<div class="ph-item-tag-box">';
		echo $this->t['tags_output'];
		echo '</div>';
		echo '<div class="ph-cb"></div>';
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
		$tabO	.= $x->description_long;
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
		$tabO	.= '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-edit"></span> '.JText::_('COM_PHOCACART_SUBMIT').'</button>';
		$tabO	.= '</div>';
		$tabO	.= '</div>';
		
		$tabO	.= '<div class="col-xs-12 col-sm-5 col-md-5"></div>';
		
		$tabO	.= '</div>';
		
		// END ROW
		
		$tabO	.= JHtml::_('form.token');
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
	
	// RELATED PRODUCTS
	if (!empty($this->t['rel_products'])) {
		$tabLiO .= '<li class="'.$active.'"><a href="#phrelated" data-toggle="tab">'.JText::_('COM_PHOCACART_RELATED_PRODUCTS').'</a></li>';
		
		$tabO 	.= '<div class="tab-pane '.$active.' fade ph-tab-pane" id="phrelated">';
		
		$tabO	.= '<div class="row">';
		
		foreach($this->t['rel_products'] as $k => $v) {
			$tabO	.= '<div class="col-xs-12 col-sm-3 col-md-3">';
			$tabO	.= '<div class="thumbnail ph-item-thumbnail-related">';
			$image 	= PhocaCartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');
			$link 	= JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->categoryalias));
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
		$linkPrev = JRoute::_(PhocaCartRoute::getItemRoute($p->id, $p->catid, $p->alias, $p->categoryalias));
		echo '<div class="pull-left"><a href="'.$linkPrev.'" class="btn btn-default ph-item-navigation" role="button"><span class="glyphicon glyphicon-arrow-left"></span> '.JText::_('COM_PHOCACART_PREVIOUS_PRODUCT'). ' ('. $p->title.')</a></div>';
	}
	echo '</div>';
	
	echo '<div class="col-sm-2 col-md-2 ph-item-navigation-box">';
	echo '</div>';
	
	echo '<div class="col-sm-2 col-md-2 ph-item-navigation-box">';
	echo '</div>';
	
	echo '<div class="col-sm-4 col-md-4 ph-item-navigation-box">';
	if($this->itemnext[0]) {
		$n = $this->itemnext[0];
		$linkNext = JRoute::_(PhocaCartRoute::getItemRoute($n->id, $n->catid, $n->alias, $n->categoryalias));
		echo '<div class="pull-right"><a href="'.$linkNext.'" class="btn btn-default ph-item-navigation" role="button">'.JText::_('COM_PHOCACART_NEXT_PRODUCT'). ' ('. $n->title.') <span class="glyphicon glyphicon-arrow-right"></span></a></div>';
	}
	echo '</div></div>';
}

echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>