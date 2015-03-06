<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-category-box" class="pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';

if ( $this->p->get( 'show_page_heading' ) ) { 
	echo '<h1>'. $this->escape($this->p->get('page_heading')) . '</h1>';
} else {
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
	echo '<div class="ph-desc">'. $this->category[0]->description. '</div>';
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
		
		echo '<div class="thumbnail ph-thumbnail">';
	
		$image = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v->image, 'medium');
		
		
	
			
		/*if ($this->t['image_link'] == 1) {
			$imageL = PhocaPhotoHelper::getThumbnailName($this->t['path'], $v->filename, 'large');
			$link = JURI::base(true).'/'.$imageL->rel;
			echo '<a href="'.$link.'" rel="prettyPhoto[pp_gal1]">';
		} else {*/
			$link = JRoute::_(PhocaCartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->categoryalias));
			echo '<a href="'.$link.'">';
		//}
		
		if (isset($image->rel) && $image->rel != '') {
			echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image"';
			if (isset($this->t['image_width_cat']) && $this->t['image_width_cat'] != '' && isset($this->t['image_height_cat']) && $this->t['image_height_cat'] != '') {
				echo ' style="width:'.$this->t['image_width_cat'].';height:'.$this->t['image_height_cat'].'"';
			}
			echo ' />';
		}
		
		echo '</a>';
		
		echo PhocaCartRenderFront::renderNewIcon($v->date);
		
		/*$imageAbs = $this->t['photopathabs'] . htmlspecialchars($v->folder).'/thumb.jpg';
		$imageRel = $this->t['photopathrel'] . htmlspecialchars($v->folder).'/thumb.jpg';
		if (isset($v->image) && $v->image != '') {
			echo '<img src="'. JURI::base(true) . '/' . $v->image.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		} else if (JFile::exists($imageAbs)) {
			echo '<img src="'.$imageRel.'" alt="" style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px" >';
		}*/
		
		// CAPTION, DESCRIPTION
		
		
		echo '<div class="caption">';
		
		// COMPARE
		if ($this->t['display_compare'] == 1) {
			echo '<form action="'.$this->t['linkcomparison'].'" method="post" id="phCompare'.(int)$v->id.'">';
			echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
			echo '<input type="hidden" name="task" value="comparison.add">';
			echo '<input type="hidden" name="tmpl" value="component" />';
			echo '<input type="hidden" name="option" value="com_phocacart" />';
			echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
			echo '<div class="pull-right">';
			echo '<div class="ph-category-item-compare"><a href="javascript::void();" onclick="document.getElementById(\'phCompare'.(int)$v->id.'\').submit();". title="'.JText::_('COM_PHOCACART_COMPARE').'"><span class="glyphicon glyphicon-stats"></span></a></div>';
			echo '</div>';
			echo JHtml::_('form.token');
			echo '</form>';
		}
		
		echo '<h3>'.$v->title.'</h3>';
		
		// Description box will be displayed even no description is set - to set height and have all columns same height
		echo '<div class="ph-item-desc">';
		if ($v->description != '') {
			echo $v->description;
		}
		echo '</div>';// end desc
		
		// PRICE
		$price 		= new PhocaCartPrice;
		$priceItems	= $price->getPriceItems($v->price, $v->taxrate, $v->taxcalculationtype, $v->taxtitle);
		
		echo '<div class="ph-category-price-box">';
		if ($v->price_original != '' && $v->price_original > 0) {
			$priceItemsOriginal = $price->getPriceItems($v->price_original, $v->taxrate, $v->taxcalculationtype);
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
		echo '</div>'; // end price box
		echo '<div class="ph-cb"></div>';
		
		// VIEW PRODUCT BUTTON
		echo '<div class="ph-category-add-to-cart-box">';
		
		echo '<div class="pull-left">';
		echo '<a href="'.$link.'" class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-search"></span> '.JText::_('COM_PHOCACART_VIEW_PRODUCT').'</a>';
		echo '</div>';
		
		if ($this->t['display_addtocart'] == 1) {
		
			if (isset($v->attribute_required) && $v->attribute_required == 1) {
				// One of the attributes is required, cannot add to cart
				
				// CUSTOMIZATION - possible to customize
				
				echo '<div class="pull-right">';
				echo '<a href="'.$link.'" class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-shopping-cart"></span> '.JText::_('COM_PHOCACART_ADD_TO_CART').'</a>';
				echo '</div>';
			
			} else {
				// ADD TO CART BUTTON
				echo '<form action="'.$this->t['linkcheckout'].'" method="post">';
				echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
				echo '<input type="hidden" name="quantity" value="1">';
				echo '<input type="hidden" name="task" value="checkout.add">';
				echo '<input type="hidden" name="tmpl" value="component" />';
				echo '<input type="hidden" name="option" value="com_phocacart" />';
				echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
				echo '<div class="pull-right">';
				echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-shopping-cart"></span> '.JText::_('COM_PHOCACART_ADD_TO_CART').'</button>';
				echo '</div>';
				echo JHtml::_('form.token');
				echo '</form>';
			
			}
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
}
echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>