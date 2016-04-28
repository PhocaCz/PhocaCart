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
$layoutA	= new JLayoutFile('button_add_to_cart_list', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutA2	= new JLayoutFile('button_buy_now_paddle', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutV	= new JLayoutFile('button_product_view', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
$layoutP	= new JLayoutFile('product_price', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');

echo '<div id="ph-pc-category-box" class="pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';

if ( $this->p->get( 'show_page_heading' ) ) { 
	echo '<h1>'. $this->escape($this->p->get('page_heading')) . '</h1>';
} else if (isset($this->category[0]->title)) {
	echo '<h1>'. $this->escape($this->category[0]->title) . '</h1>';
}


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
		
		echo '<div class="thumbnail ph-thumbnail">';
		
		$image = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');
		$link = JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
		echo '<a href="'.$link.'">';
		
		if (isset($image->rel) && $image->rel != '') {
			echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image"';
			if (isset($this->t['image_width_cat']) && $this->t['image_width_cat'] != '' && isset($this->t['image_height_cat']) && $this->t['image_height_cat'] != '') {
				echo ' style="width:'.$this->t['image_width_cat'].';height:'.$this->t['image_height_cat'].'"';
			}
			echo ' />';
		}
		
		echo '</a>';
		

		/*$imageAbs = $this->t['photopathabs'] . htmlspecialchars($v->folder).'/thumb.jpg';
		$imageRel = $this->t['photopathrel'] . htmlspecialchars($v->folder).'/thumb.jpg';
		if (isset($v->image) && $v->image != '') {
			echo '<img src="'. JURI::base(true) . '/' . $v->image.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		} else if (JFile::exists($imageAbs)) {
			echo '<img src="'.$imageRel.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		}*/
		
		// CAPTION, DESCRIPTION
		echo '<div class="caption">';
		
		// :L: COMPARE
		
		if ($this->t['display_compare'] == 1) {
			$d			= array();
			$d['linkc']	= $this->t['linkcomparison'];
			$d['id']	= (int)$v->id;
			$d['catid']	= (int)$v->catid;
			$d['return']= $this->t['actionbase64'];
			$d['method']= $this->t['add_compare_method'];
			echo $layoutC->render($d);
		}
		
		echo '<h3>'.$v->title.'</h3>';
		
		// Description box will be displayed even no description is set - to set height and have all columns same height
		echo '<div class="ph-item-desc">';
		if ($v->description != '') {
			echo JHTML::_('content.prepare', $v->description);
		}
		echo '</div>';// end desc
		
		// :L: PRICE
		$price 				= new PhocaCartPrice;
		$d					= array();
		$d['priceitems']	= $price->getPriceItems($v->price, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, $v->unit_amount, $v->unit_unit);
		$d['priceitemsorig']= array();
		if ($v->price_original != '' && $v->price_original > 0) {
			$d['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxrate, $v->taxcalculationtype);
		}
		$d['class']			= 'ph-category-price-box';
		echo $layoutP->render($d);
		
		// VIEW PRODUCT BUTTON
		echo '<div class="ph-category-add-to-cart-box">';
		
		// :L: LINK TO PRODUCT VIEW
		$d					= array();
		$d['link']			= $link;
		echo $layoutV->render($d);
		
		// :L: ADD TO CART
		if ((int)$this->t['items_addtocart'] == 1) {
			$d					= array();
			$d['link']			= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
			$d['linkch']		= $this->t['linkcheckout'];// link to checkout (add to cart)
			$d['id']			= (int)$v->id;
			$d['catid']			= (int)$v->catid;
			$d['return']		= $this->t['actionbase64'];
			$d['attrrequired']	= 0;
			if (isset($v->attribute_required) && $v->attribute_required == 1) {
				$d['attrrequired']	= 1;
			}
			echo $layoutA->render($d);
		} else if ((int)$this->t['items_addtocart'] == 2 && (int)$v->external_id != '') {
			$d					= array();
			$d['external_id']	= (int)$v->external_id;
			$d['return']		= $this->t['actionbase64'];
			echo $layoutA2->render($d);
		}
		

		echo '</div>';// end add to cart box
		echo '<div class="clearfix"></div>';
		
		
		echo '</div>';// end caption
		
		echo '</div>';// end thumbnail
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