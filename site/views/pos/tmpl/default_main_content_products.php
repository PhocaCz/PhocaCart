<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();



$layoutA	= new JLayoutFile('button_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
$layoutI	= new JLayoutFile('product_image', null, array('component' => 'com_phocacart'));
$layoutAB	= new JLayoutFile('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutPFS	= new JLayoutFile('form_part_start_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutPFE	= new JLayoutFile('form_part_end', null, array('component' => 'com_phocacart'));
$layoutBSH	= new JLayoutFile('button_submit_hidden', null, array('component' => 'com_phocacart'));																								  
$layoutS	= new JLayoutFile('product_stock', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new JLayoutFile('product_order_quantity', null, array('component' => 'com_phocacart'));


// ITEMS
if (!empty($this->items)) {
	
	$price	= new PhocacartPrice;
	$col 	= PhocacartRenderFront::getColumnClass($this->t['columns_pos']);
	$lt		= $this->t['pos_hide_attributes'] == 0 ? 'grid' : 'fullbutton grid';
	$i		= 1; // Not equal Heights

	echo '<div id="phItems" class="ph-items '.$lt.'">';
	echo '<div class="row '.$this->t['class-row-flex'].' '.$lt.'">';
	
	foreach ($this->items as $v) {
		
		// DIFF CATEGORY / ITEMS
		//$this->t['categoryid'] = (int)$v->catid;
		
		//$label 		= PhocacartRenderFront::getLabel($v->date, $v->sales, $v->featured);
		$link 		= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
		
		
		// Image data
		$attributesOptions 	= $this->t['pos_hide_attributes'] == 0 ? PhocacartAttribute::getAttributesAndOptions((int)$v->id) : array();
		if (!isset($v->additional_image)) { $v->additional_image = '';}
		$image = PhocacartImage::getImageDisplay($v->image, $v->additional_image, $this->t['pathitem'], $this->t['switch_image_category_items'], $this->t['image_width_cat'], $this->t['image_height_cat'], '', $lt, $attributesOptions);
		
		
		if (!isset($image['image']->rel) || (isset($image['image']->rel) && $image['image']->rel == '')) {
			$image['image']->rel 	= 'media/com_phocacart/images/no-image.png';
			$image['image']->abs 	= JPATH_ROOT . '/media/com_phocacart/images/no-image.png';
			$image['default']->rel 	= $image['image']->rel;
			$image['default']->abs 	= $image['image']->abs;

			
		}

		// :L: IMAGE
		$dI	= array();
		if (isset($image['image']->rel) && $image['image']->rel != '') {
			$dI['product_id']		= (int)$v->id;						
			$dI['layouttype']		= $lt;
			$dI['image']			= $image['image'];
			$dI['default_image']	= $image['default'];
			$dI['image2']			= '';
			$dI['imagestyle']		= $image['style'];
			$dI['phil']				= $image['phil'];
			$dI['typeview']			= 'Pos';
		}
		

		

		
		// :L: PRICE
		$dP = array();
		if ($this->t['hide_price'] != 1) {
			$dP['priceitems']	= $price->getPriceItems($v->price, $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, $v->unit_amount, $v->unit_unit, 1, 1, $v->group_price);
			$price->getPriceItemsChangedByAttributes($dP['priceitems'], $attributesOptions, $price, $v);
			$dP['priceitemsorig']= array();
			if ($v->price_original != '' && $v->price_original > 0) {
				$dP['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype);
			}
			//$dP['class']		= 'ph-category-price-box '.$lt;
			$dP['class']		= 'ph-category-price-box';// Cannot be dynamic as can change per ajax - this can cause jumping of boxes
			$dP['product_id']	= (int)$v->id;
			$dP['typeview']		= 'Pos';
			
			// Display discount price
			// Move standard prices to new variable (product price -> product discount)
			$dP['priceitemsdiscount']		= $dP['priceitems'];
			$dP['discount'] 				= PhocacartDiscountProduct::getProductDiscountPrice($v->id, $dP['priceitemsdiscount']);
			
			// Display cart discount (global discount) in product views - under specific conditions only
			// Move product discount prices to new variable (product price -> product discount -> product discount cart)
			$dP['priceitemsdiscountcart']	= $dP['priceitemsdiscount'];
			$dP['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($v->id, $v->catid, $dP['priceitemsdiscountcart']);
		}
		

		
		// :L: ADD TO CART
		$dA = $dA2 = $dA3 = $dAb = $dF = array();
		$icon['addtocart'] = '';
		
		// STOCK ===================================================
		// Set stock: product, variations, or advanced stock status
		$dSO 				= '';
		$dA['class_btn']	= '';
		$dA['class_icon']	= '';
		if ($this->t['pos_display_stock_status'] == 1) {
		
			$stockStatus 				= array();
			$stock 						= PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $attributesOptions, $v);
			
			if ($this->t['hide_add_to_cart_stock'] == 1 && (int)$stock < 1) {
				$dA['class_btn'] 		= 'ph-visibility-hidden';// hide button
				$dA['class_icon']		= 'ph-display-none';// hide icon
			}
			
			if($stockStatus['stock_status'] || $stockStatus['stock_count']) {
				$dS							= array();
				$dS['class']				= 'ph-item-stock-box';
				$dS['product_id']			= (int)$v->id;
				$dS['typeview']				= 'Pos';
				$dS['stock_status_output'] 	= PhocacartStock::getStockStatusOutput($stockStatus);
				$dSO = $layoutS->render($dS);
			}
			
			if($stockStatus['min_quantity']) {
				$dPOQ						= array();
				$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY');
				$dPOQ['status']				= $stockStatus['min_quantity'];
				$dSO .= $layoutPOQ->render($dPOQ);
			}
			
			if($stockStatus['min_multiple_quantity']) {
				$dPOQ						= array();
				$dPOQ['text']				= JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY');
				$dPOQ['status']				= $stockStatus['min_multiple_quantity'];
				$dSO .= $layoutPOQ->render($dPOQ);
			}
		}
		// END STOCK ================================================
  
		
		// ------------------------------------
		// BUTTONS + ICONS
		// ------------------------------------
		// Prepare data for Add to cart button
		// - Add To Cart Standard Button
		// - Add to Cart Icon Button
		// - Add to Cart Icon Only
		
		// FORM DATA
		$dF['linkch']				= $this->t['linkcheckout'];// link to checkout (add to cart)
		$dF['id']					= (int)$v->id;
		$dF['catid']				= (int)$v->catid;
		$dF['ticketid']				= $this->t['ticket']->id;
		$dF['unitid']				= $this->t['unit']->id;
		$dF['sectionid']			= $this->t['section']->id;
		$dF['return']				= $this->t['actionbase64'];
		$dF['typeview']				= 'Pos';
		$dA['addtocart']			= $this->t['category_addtocart'];
		$dA['addtocart_icon']		= $this->t['display_addtocart_icon'];

		// Both buttons + icon
		$dA['id']					= (int)$v->id;
		$dA['link']					= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
		$dA['addtocart']			= $this->t['category_addtocart'];
		$dA['method']				= $this->t['add_cart_method'];
		$dA['typeview']				= 'Pos';
		
		// ATTRIBUTES, OPTIONS
		$dAb['attr_options']			= $attributesOptions;
		$dAb['hide_attributes']			= $this->t['pos_hide_attributes'];
		$dAb['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
		$dAb['pathitem']				= $this->t['pathitem'];
																	
		$dAb['product_id']				= (int)$v->id;
		$dAb['image_size']				= $image['size'];
		$dAb['typeview']				= 'Pos';
		$dAb['price']					= $price;
		
		// Attribute is required and we don't display it in category/items view, se we need to redirect to detail view
		$dA['selectoptions']	= 0;
		if (isset($v->attribute_required) && $v->attribute_required == 1 && $this->t['pos_hide_attributes'] == 1) {
			$dA['selectoptions']	= 1;
		}
			
		
		
		
		
		// ======
		// RENDER
		// ======
		echo '<div class="row-item col-sx-12 col-sm-'.$col.' col-md-'.$col.'">';
		echo '<div class="ph-item-box '.$lt.'">';
		//echo $label['new'] . $label['hot'] . $label['feat'];
		echo '<div class="'.$this->t['class_thumbnail'].' ph-thumbnail ph-thumbnail-c ph-item '.$lt.'">';
		echo '<div class="ph-item-content '.$lt.'">';

		
		// -----------
		// RENDER GRID
		// -----------
		echo '<div class="jf_ph_cat_item_grid">';
		// :L: IMAGE
		echo '<a href="'.$link.'">';
		if (!empty($dI)) { echo $layoutI->render($dI);}
		echo '</a>';
		

		echo '</div>';
		
		echo '<div class="ph-item-clearfix '.$lt.'"></div>';
		
		// CAPTION, DESCRIPTION BOX
		
		
		//echo '<div class="ph-caption  '.$lt.'">';
		echo PhocacartRenderFront::renderProductHeader($this->t['product_name_link'], $v, 'item', '', $lt);
		//echo '</div>';// end caption
		

		

		
		
		
		// :L: PRICE
		if (!empty($dP)) { echo $layoutP->render($dP);}
		
		// VIEW PRODUCT BUTTON
		echo '<div class="ph-category-add-to-cart-box '.$lt.'">';
		
		// :L: Stock status
		if (!empty($dSO)) { echo $dSO;}
		
		// Start Form
		if (!empty($dF)) { echo $layoutPFS->render($dF);}	
		
		// :L: ATTRIBUTES AND OPTIONS
		if (!empty($dAb)) { echo $layoutAB->render($dAb);}

		// :L: ADD TO CART
		if (!empty($dA)) { echo $layoutA->render($dA);} else if ($icon['addtocart'] != '') { echo $layoutBSH->render();}
		
		// End Form
		if (!empty($dF)) { echo $layoutPFE->render();}
		
			
		echo '</div>';// end add to cart box
		
		

		
					  
			
		
		// --------------- END RENDER
		
		
		
		echo '<div class="clearfix"></div>';
		
		
		echo '</div>';// end ph-item-content
		echo '</div>';// end thumbnail ph-item
		echo '</div>';// end ph-item-box
		echo '</div>'. "\n"; // end row item - columns
		
		if ($i%(int)$this->t['columns_pos'] == 0) {
			echo '<div class="clearfix"></div>';
		}
		$i++;
	}

			
	echo '</div>';// end row (row-flex)
	echo '<div class="clearfix"></div>';
	
	echo $this->loadTemplate('pagination');
	
	echo '</div>'. "\n"; // end items
} else {
	echo '<div id="phItems" class="ph-items grid">';
	echo '<div class="ph-pos-no-items-icon"><span class="glyphicon glyphicon-ban-circle"></span></div>';
	echo '<div class="ph-pos-no-items">'.JText::_('COM_PHOCACART_NO_PRODUCT_FOUND').'</div>';
	
	echo $this->loadTemplate('pagination');// empty pagination only needed variables
	echo '</div>'. "\n"; // end items
}
?>