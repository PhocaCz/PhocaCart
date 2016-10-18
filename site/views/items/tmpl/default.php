<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutC 	= new JLayoutFile('button_compare', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutW 	= new JLayoutFile('button_wishlist', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutQVB 	= new JLayoutFile('button_quickview', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutA	= new JLayoutFile('button_add_to_cart_list', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutA2	= new JLayoutFile('button_buy_now_paddle', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutA3	= new JLayoutFile('button_external_link', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutV	= new JLayoutFile('button_product_view', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutP	= new JLayoutFile('product_price', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');


echo '<div id="ph-pc-category-box" class="pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';



$title = '';
if (isset($this->category[0]->title) && $this->category[0]->title != '') {
	$title = $this->category[0]->title;
}
echo PhocaCartRenderFront::renderHeader(array($title, JText::_('COM_PHOCACART_ITEMS')));



if (isset($this->category[0]->parentid) && ($this->t['display_back'] == 1 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->parentid == 0) {
		$linkUp = JRoute::_(PhocaCartRoute::getCategoriesRoute());
		$linkUpText = JText::_('COM_PHOCACART_CATEGORIES');
	} else if ($this->category[0]->parentid > 0) {
		$linkUp = JRoute::_(PhocaCartRoute::getCategoryRoute($this->category[0]->parentid, $this->category[0]->parentalias));
		$linkUpText = $this->category[0]->parenttitle;
	} else {
		$linkUp 	= false;
		$linkUpText = false; 
	}
	
	if ($linkUp && $linkUpText) {
		echo '<div class="ph-top">'
		.'<a class="btn btn-success" title="'.$linkUpText.'" href="'. $linkUp.'" ><span class="glyphicon glyphicon-arrow-left"></span> '.JText::_($linkUpText).'</a></div>';
	}
}

if ( isset($this->category[0]->description) && $this->category[0]->description != '') {
	echo '<div class="ph-desc">'. JHTML::_('content.prepare', $this->category[0]->description). '</div>';
}

if (!empty($this->subcategories) && (int)$this->t['cv_display_subcategories'] > 0) {
	echo '<div class="ph-subcategories">'.JText::_('COM_PHOCACART_SUBCATEGORIES') . ':</div>';
	echo '<ul>';
	$j = 0;
	foreach($this->subcategories as $v) {
		if ($j == (int)$this->t['cv_display_subcategories']) {
			break;
		}
		echo '<li><a href="'.JRoute::_(PhocaCartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a></li>';
		$j++;
	}
	echo '</ul>';
	echo '<hr />';
}

if (!empty($this->items)) {
	echo '<div class="ph-items">';
	$i = 0;
	$c = count($this->items);
	$nc= (int)$this->t['columns_cat'];
	$nw= 12/$nc;//1,2,3,4,6,12
	echo '<div class="row">';
	
	
	// initialize price before foreach
	$price 				= new PhocaCartPrice;
	
	foreach ($this->items as $v) {
		
		//if ($i%3==0) { echo '<div class="row">';}
		
		echo '<div class="col-sm-6 col-md-'.$nw.'">';
		
		$new = $hot = $feat = '';
		$c = 1;
		$new = PhocaCartRenderFront::renderNewIcon($v->date, $c);
		if ($new != '') {$c++;}
		$hot = PhocaCartRenderFront::renderHotIcon($v->sales, $c);
		if ($hot != '') { $c++;}
		$feat = PhocaCartRenderFront::renderFeaturedIcon($v->featured, $c);
		echo $new . $hot . $feat;
		
		echo '<div class="ph-item-box">';
		echo '<div class="'.$this->t['class_thumbnail'].' ph-thumbnail ph-thumbnail-c ph-item">';
		echo '<div class="ph-item-content">';
		
		$image = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');
		$image2 = false;
		$phIL 	= 'phIL-not-active';
		if ($this->t['switch_image_category_items'] == 1) {
			$image2 = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v->additional_image, 'medium');
			if (isset($image2->rel) && $image2->rel != '') {
				$phIL = 'phIL';
			}
		}
		
		$link = JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
		echo '<a href="'.$link.'">';
		
		
		if (isset($image->rel) && $image->rel != '') {
			
			echo '<div class="phIBoxOH"><div class="phIBox">';

			echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image '.$phIL.'"';
			if (isset($this->t['image_width_cat']) && $this->t['image_width_cat'] != '' && isset($this->t['image_height_cat']) && $this->t['image_height_cat'] != '') {
				echo ' style="width:'.$this->t['image_width_cat'].';height:'.$this->t['image_height_cat'].'"';
			}
			echo '  />';
			
			
			if (isset($image2->rel) && $image2->rel != '') {
				echo '<span class="phIRBox"><img src="'.JURI::base(true).'/'.$image2->rel.'" alt="" class="img-responsive ph-image phIR"';
				if (isset($this->t['image_width_cat']) && $this->t['image_width_cat'] != '' && isset($this->t['image_height_cat']) && $this->t['image_height_cat'] != '') {
					echo ' style="width:'.$this->t['image_width_cat'].';height:'.$this->t['image_height_cat'].'"';
				}
				echo '  /></span>';
					
			}
			echo '</div></div>';
		}
		
		echo '</a>';
		
		/*$imageAbs = $this->t['photopathabs'] . htmlspecialchars($v->folder).'/thumb.jpg';
		$imageRel = $this->t['photopathrel'] . htmlspecialchars($v->folder).'/thumb.jpg';
		if (isset($v->image) && $v->image != '') {
			echo '<img src="'. JURI::base(true) . '/' . $v->image.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		} else if (JFile::exists($imageAbs)) {
			echo '<img src="'.$imageRel.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		}*/
		
		
		
		// :L: COMPARE
		$icon = array();
		$icon['compare'] = '';
		if ($this->t['display_compare'] == 1) {
			$d			= array();
			$d['linkc']	= $this->t['linkcomparison'];
			$d['id']	= (int)$v->id;
			$d['catid']	= (int)$v->catid;
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
			$d['catid']	= (int)$v->catid;
			$d['return']= $this->t['actionbase64'];
			$d['method']= $this->t['add_wishlist_method'];
			$icon['wishlist'] = $layoutW->render($d);
		}
		
		
		// :L: QUICKVIEW
		$icon['quickview'] = '';
		if ($this->t['display_quickview'] == 1) {
			$d			= array();
			$d['linkqvb']	= JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));	
			$d['id']	= (int)$v->id;
			$d['catid']	= (int)$v->catid;
			$d['return']= $this->t['actionbase64'];
			$icon['quickview'] = $layoutQVB->render($d);
		}
		
		if ($this->t['fade_in_action_icons'] == 0) {
			echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
			echo $icon['wishlist'];
			echo $icon['quickview'];
		}
		// CAPTION, DESCRIPTION
		echo '<div class="caption ph-item-action-box">';
		
		echo '<h3>';
		if ($this->t['product_name_link'] == 1)
			echo '<a href="'.JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias)).'">'.$v->title.'</a>';
		else {
			echo $v->title;
		}
		echo '</h3>';
		
		// Description box will be displayed even no description is set - to set height and have all columns same height
		echo '<div class="ph-item-desc">';
		if ($v->description != '') {
			echo JHTML::_('content.prepare', $v->description);
		}
		echo '</div>';// end desc
		
		// :L: PRICE
		if ($this->t['hide_price'] != 1) {
			$d					= array();
			$d['priceitems']	= $price->getPriceItems($v->price, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, $v->unit_amount, $v->unit_unit, 1);
			$d['priceitemsorig']= array();
			if ($v->price_original != '' && $v->price_original > 0) {
				$d['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxrate, $v->taxcalculationtype);
			}
			$d['class']			= 'ph-category-price-box';
			echo $layoutP->render($d);
		}
		
		// VIEW PRODUCT BUTTON
		echo '<div class="ph-category-add-to-cart-box">';
		
		// :L: LINK TO PRODUCT VIEW
		if ((int)$this->t['display_view_product_button'] > 0) {
			$d									= array();
			$d['link']							= $link;
			$d['display_view_product_button'] 	= $this->t['display_view_product_button'];
			echo $layoutV->render($d);
		}
		
		// :L: ADD TO CART
		if ((int)$this->t['items_addtocart'] == 1 || (int)$this->t['items_addtocart'] == 4 ) {
			$d						= array();
			$d['link']				= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
			$d['linkch']			= $this->t['linkcheckout'];// link to checkout (add to cart)
			$d['id']				= (int)$v->id;
			$d['catid']				= (int)$v->catid;
			$d['return']			= $this->t['actionbase64'];
			$d['attrrequired']		= 0;
			$d['addtocart']			= $this->t['items_addtocart'];
			if (isset($v->attribute_required) && $v->attribute_required == 1) {
				$d['attrrequired']	= 1;
			}
			echo $layoutA->render($d);
		} else if ((int)$this->t['items_addtocart'] == 2 && (int)$v->external_id != '') {
			// e.g. paddle
			$d					= array();
			$d['external_id']	= (int)$v->external_id;
			$d['return']		= $this->t['actionbase64'];
			echo $layoutA2->render($d);
		} else if ((int)$this->t['items_addtocart'] == 2 && (int)$v->external_id != '') {
			$d					= array();
			$d['external_id']	= (int)$v->external_id;
			$d['return']		= $this->t['actionbase64'];
			echo $layoutA3->render($d);
		}
		

		echo '</div>';// end add to cart box
		echo '<div class="clearfix"></div>';
		
		
		if ($this->t['fade_in_action_icons'] == 1) {
			echo '<div class="ph-item-action-fade">';
			echo $icon['compare'];
			echo $icon['wishlist'];
			echo $icon['quickview'];
			echo '</div>';
		}
		
		echo '</div>';// end caption
		
		
		echo '</div>';// end ph-item-content
		echo '</div>';// end thumbnail ph-item
		echo '</div>';// end ph-item-box
		echo '</div>'. "\n"; // end columns
		
		//$i++; if ($i%3==0 || $c==$i) { echo '</div>';}
		
	}
	echo '</div>';// end row
	echo '</div>'. "\n"; // end items
	
	
	echo $this->loadTemplate('pagination');
} else {
	
	echo '<div class="alert alert-warning">'.JText::_('COM_PHOCACART_NO_PRODUCTS_FOUND').'</div>';
}
echo '</div>';

echo '<div id="phContainer"></div>';
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>