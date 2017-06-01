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
$layoutQVB 	= new JLayoutFile('button_quickview', null, array('component' => 'com_phocacart'));
$layoutA	= new JLayoutFile('button_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new JLayoutFile('button_external_link', null, array('component' => 'com_phocacart'));
$layoutV	= new JLayoutFile('button_product_view', null, array('component' => 'com_phocacart'));
$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
$layoutI	= new JLayoutFile('product_image', null, array('component' => 'com_phocacart'));

// HEADER - NOT AJAX
if (!$this->t['ajax']) { 
	echo '<div id="ph-pc-category-box" class="pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';
	echo $this->loadTemplate('header');
	echo $this->loadTemplate('pagination_top');
	echo '<div id="phItemsBox">';
}


// ITEMS
if (!empty($this->items)) {
	
	$price	= new PhocacartPrice;
	$col 	= PhocacartRenderFront::getColumnClass((int)$this->t['columns_cat']);
	$lt		= $this->t['layouttype'];
	$i		= 1; // Not equal Heights						  

	
	
	echo '<div id="phItems" class="ph-items '.$lt.'">';
	echo '<div class="row '.$this->t['class-row-flex'].' '.$lt.'">';
	
	foreach ($this->items as $v) {
		
		// DIFF CATEGORY / ITEMS
		$this->t['categoryid'] = (int)$v->catid;
		
		$label 		= PhocacartRenderFront::getLabel($v->date, $v->sales, $v->featured);
		$link 		= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
		
		// Image
		$imageSize	= $lt == 'gridlist' ? 'large' : 'medium';
		$image 		= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, $imageSize);
		$image2 	= false;
		$phIL 		= 'phIL-not-active';
		if ($this->t['switch_image_category_items'] == 1) {
			$image2 = PhocacartImage::getThumbnailName($this->t['pathitem'], $v->additional_image, $imageSize);
			if (isset($image2->rel) && $image2->rel != '') {
				$phIL = 'phIL';
			}
		}
		$imgStyle = '';
		if (isset($this->t['image_width_cat']) && $this->t['image_width_cat'] != '' && isset($this->t['image_height_cat']) && $this->t['image_height_cat'] != '') {
			$imgStyle = 'style="width:'.$this->t['image_width_cat'].';height:'.$this->t['image_height_cat'].'"';
		}
		
		// :L: IMAGE
		$dI	= array();
		if (isset($image->rel) && $image->rel != '') {
			$dI['layouttype']		= $lt;
			$dI['image']			= $image;
			$dI['image2']			= $image2;
			$dI['imagestyle']		= $imgStyle;
			$dI['phil']				= $phIL;
		}
		
		// :L: COMPARE
		$icon 				= array();
		$icon['compare'] 	= '';
		if ($this->t['display_compare'] == 1) {
			$d			= array();
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
			$d			= array();
			$d['linkqvb']	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));	
			$d['id']	= (int)$v->id;
			$d['catid']	= $this->t['categoryid'];
			$d['return']= $this->t['actionbase64'];
			$icon['quickview'] = $layoutQVB->render($d);
		}
		
		// :L: PRICE
		$dP = array();
		if ($this->t['hide_price'] != 1) {
			$dP['priceitems']	= $price->getPriceItems($v->price, $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, $v->unit_amount, $v->unit_unit, 1, 1, $v->group_price);
			$dP['priceitemsorig']= array();
			if ($v->price_original != '' && $v->price_original > 0) {
				$dP['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype);
			}
			$dP['class']			= 'ph-category-price-box '.$lt;
		}
		
		// :L: LINK TO PRODUCT VIEW
		$dV = array();
		if ((int)$this->t['display_view_product_button'] > 0) {
			$dV['link']							= $link;
			$dV['display_view_product_button'] 	= $this->t['display_view_product_button'];
		}
		
		// :L: ADD TO CART
		$dA = $dA2 = $dA3 = array();
		$icon['addtocart'] = '';				  
		if ((int)$this->t['category_addtocart'] == 1 || (int)$this->t['category_addtocart'] == 4 ) {
			
			$dA['link']			= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
			$dA['linkch']		= $this->t['linkcheckout'];// link to checkout (add to cart)
			$dA['id']			= (int)$v->id;
			$dA['catid']		= $this->t['categoryid'];
			$dA['return']		= $this->t['actionbase64'];
			$dA['attrrequired']	= 0;
			$dA['addtocart']		= $this->t['category_addtocart'];
			$dA['method']		= $this->t['add_cart_method'];															
			if (isset($v->attribute_required) && $v->attribute_required == 1) {
				$dA['attrrequired']	= 1;
			}
			
			// Add To Cart as Icon
			if ($this->t['display_addtocart_icon'] == 1) {
				$dA['icon']			= 1;// Display as Icon
				$icon['addtocart'] 	= $layoutA->render($dA);
			}
			$dA['icon']			= 0;// Set back to display as button
			
		} else if ((int)$this->t['category_addtocart'] == 2 && (int)$v->external_id != '') {
			// e.g. paddle
			$dA2['external_id']	= (int)$v->external_id;
			$dA2['return']		= $this->t['actionbase64'];
			
		} else if ((int)$this->t['category_addtocart'] == 3 && $v->external_link != '') {
			$dA3['external_link']	= $v->external_link;
			$dA3['external_text']	= $v->external_text;
			$dA3['return']			= $this->t['actionbase64'];
			
		}
		
		// ======
		// RENDER
		// ======
		echo '<div class="row-item col-sx-12 col-sm-'.$col.' col-md-'.$col.'">';
		echo '<div class="ph-item-box '.$lt.'">';
		echo $label['new'] . $label['hot'] . $label['feat'];
		echo '<div class="'.$this->t['class_thumbnail'].' ph-thumbnail ph-thumbnail-c ph-item '.$lt.'">';
		echo '<div class="ph-item-content">';

		
		if ($lt == 'list') {
			// -----------
			// RENDER LIST
			// -----------
			
			echo '<div class="row ph-item-content-row">';
			
			// 1/3
			echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">';
			// :L: IMAGE
			echo '<a href="'.$link.'">';
			if (!empty($dI)) { echo $layoutI->render($dI);}
			echo '</a>';
			echo '</div>';// end row-item 1/3
			
			// 2/3
			echo '<div class="row-item col-sx-12 col-sm-6 col-md-6">';

			// CAPTION, DESCRIPTION BOX
			echo '<div class="caption ph-item-action-box '.$lt.'">';

			echo '<h3 class="'.$lt.'">'. PhocacartRenderFront::getLinkedTitle($this->t['product_name_link'], $v) . '</h3>';

			// Description box will be displayed even no description is set - to set height and have all columns same height
			echo '<div class="ph-item-desc">';
			if ($v->description != '') { echo JHTML::_('content.prepare', $v->description); }
			echo '</div>';// end desc
			
			if ($this->t['fade_in_action_icons'] == 0) {
				echo '<div class="ph-item-action '.$lt.'">';
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';
			}
			
			echo '</div>';// end caption
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
			
			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0 && isset($v->rating) && (int)$v->rating > 0) {
				echo '<div class="ph-stars-box"><span class="ph-stars"><span style="width:'.((int)$v->rating * 16) .'px;"></span></span></div>';
			}
			
			if ($this->t['fade_in_action_icons'] == 1) {
				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];			
				echo '</div>';
			}
			
			echo '</div>';// end row-item 2/3
			
			// 3/3
			echo '<div class="row-item col-sx-12 col-sm-4 col-md-4">';
			
			// :L: PRICE
			if (!empty($dP)) { echo $layoutP->render($dP);};
			
			// VIEW PRODUCT BUTTON
			echo '<div class="ph-category-add-to-cart-box '.$lt.'">';
			
			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}
			
			// :L: ADD TO CART
			if (!empty($dA)) { echo $layoutA->render($dA);}
			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}
			
			echo '</div>';// end add to cart box 
			
			$results = $this->t['dispatcher']->trigger('onItemsItemAfterAddToCart', array('com_phocacart.items', &$v, &$this->p));
			echo trim(implode("\n", $results));
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
	
			echo '</div>';// end row-item 3/3
			
			echo '</div>';// end row list
			
			
		} else if ( $lt == 'gridlist') {
			// ----------------
			// RENDER GRID LIST
			// ----------------
			echo '<div class="row ph-item-content-row">';
			
			// 1/2
			echo '<div class="row-item col-sx-12 col-sm-6 col-md-6">';
			// :L: IMAGE
			echo '<a href="'.$link.'">';
			if (!empty($dI)) { echo $layoutI->render($dI);}
			echo '</a>';
			echo '</div>';// end row-item 1/2
			
			// 2/2
			echo '<div class="row-item col-sx-12 col-sm-6 col-md-6">';

			// CAPTION, DESCRIPTION BOX
			echo '<div class="caption ph-item-action-box '.$lt.'">';

			echo '<h3 class="'.$lt.'">'. PhocacartRenderFront::getLinkedTitle($this->t['product_name_link'], $v) . '</h3>';

			// Description box will be displayed even no description is set - to set height and have all columns same height
			echo '<div class="ph-item-desc">';
			if ($v->description != '') { echo JHTML::_('content.prepare', $v->description); }
			echo '</div>';// end desc
			
			if ($this->t['fade_in_action_icons'] == 0) {
				echo '<div class="ph-item-action '.$lt.'">';
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';
			}
			
			echo '</div>';// end caption
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
			
			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0 && isset($v->rating) && (int)$v->rating > 0) {
				echo '<div class="ph-stars-box"><span class="ph-stars"><span style="width:'.((int)$v->rating * 16) .'px;"></span></span></div>';
			}
			
			if ($this->t['fade_in_action_icons'] == 1) {
				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];			
				echo '</div>';
			}
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
			
			// :L: PRICE
			if (!empty($dP)) { echo $layoutP->render($dP);};
			
			// VIEW PRODUCT BUTTON
			echo '<div class="ph-category-add-to-cart-box '.$lt.'">';
			
			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}
			
			// :L: ADD TO CART
			if (!empty($dA)) { echo $layoutA->render($dA);}
			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}
			
			echo '</div>';// end add to cart box 
			
			$results = $this->t['dispatcher']->trigger('onItemsItemAfterAddToCart', array('com_phocacart.items', &$v, &$this->p));
			echo trim(implode("\n", $results));
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
	
			echo '</div>';// end row-item 3/3
			
			echo '</div>';// end row list
			
			
		} else  {
			// -----------
			// RENDER GRID
			// -----------
		
			// :L: IMAGE
			echo '<a href="'.$link.'">';
			if (!empty($dI)) { echo $layoutI->render($dI);}
			echo '</a>';
			

			if ($this->t['fade_in_action_icons'] == 0) {
				echo '<div class="ph-item-action '.$lt.'">';
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';
			}
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
			
			// CAPTION, DESCRIPTION BOX
			echo '<div class="caption  '.$lt.'">';
			echo '<h3 class="'.$lt.'">'. PhocacartRenderFront::getLinkedTitle($this->t['product_name_link'], $v) . '</h3>';
			echo '</div>';// end caption
			
			// Description box will be displayed even no description is set - to set height and have all columns same height
			echo '<div class="ph-item-desc">';
			if ($v->description != '') { echo JHTML::_('content.prepare', $v->description); }
			echo '</div>';// end desc
			
		
			
			// :L: PRICE
			if (!empty($dP)) { echo $layoutP->render($dP);}
			
			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0 && isset($v->rating) && (int)$v->rating > 0) {
				echo '<div class="ph-stars-box"><span class="ph-stars"><span style="width:'.((int)$v->rating * 16) .'px;"></span></span></div>';
			}
			
			// VIEW PRODUCT BUTTON
			echo '<div class="ph-category-add-to-cart-box '.$lt.'">';
			
			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}
			
			// :L: ADD TO CART
			if (!empty($dA)) { echo $layoutA->render($dA);}
			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}
			
			echo '</div>';// end add to cart box
			
			$results = $this->t['dispatcher']->trigger('onItemsItemAfterAddToCart', array('com_phocacart.items', &$v, &$this->p));
			echo trim(implode("\n", $results));
			
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';
			
			
			if ($this->t['fade_in_action_icons'] == 1) {
				echo '<div class="ph-item-action-box '.$lt.'">';
				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];			
				echo '</div>';
				echo '</div>';// end action box
			}
		}
		
		
		
		echo '<div class="clearfix"></div>';
		echo '</div>';// end ph-item-content
		echo '</div>';// end thumbnail ph-item
		echo '</div>';// end ph-item-box
		echo '</div>'. "\n"; // end row item - columns
		

		
		if ($i%(int)$this->t['columns_cat'] == 0) {
			echo '<div class="clearfix"></div>';
		}
		$i++;									 
	}
	
	echo '</div>';// end row (row-flex)
	echo '<div class="clearfix"></div>';
	
	
	echo $this->loadTemplate('pagination');
	
	echo '</div>'. "\n"; // end items
}


// FOOTER - NOT AJAX
if (!$this->t['ajax']) {
	
	echo '</div>';// end #phItemsBox
	echo '</div>';// end #ph-pc-category-box

	echo '<div id="phContainer"></div>';
	echo '<div>&nbsp;</div>';
	echo PhocacartUtils::getInfo();
}
?>