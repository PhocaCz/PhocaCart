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
$layoutAI	= new JLayoutFile('button_add_to_cart_icon', null, array('component' => 'com_phocacart'));
$layoutA	= new JLayoutFile('button_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new JLayoutFile('button_external_link', null, array('component' => 'com_phocacart'));
$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
$layoutI	= new JLayoutFile('product_image', null, array('component' => 'com_phocacart'));
$layoutAB	= new JLayoutFile('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutV	= new JLayoutFile('button_product_view', null, array('component' => 'com_phocacart'));
$layoutPFS	= new JLayoutFile('form_part_start_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutPFE	= new JLayoutFile('form_part_end', null, array('component' => 'com_phocacart'));
$layoutBSH	= new JLayoutFile('button_submit_hidden', null, array('component' => 'com_phocacart'));
$layoutS	= new JLayoutFile('product_stock', null, array('component' => 'com_phocacart'));
$layoutPOQ	= new JLayoutFile('product_order_quantity', null, array('component' => 'com_phocacart'));
$layoutR	= new JLayoutFile('product_rating', null, array('component' => 'com_phocacart'));

// HEADER - NOT AJAX
if (!$this->t['ajax']) {
	echo '<div id="ph-pc-category-box" class="pc-category-view'.$this->p->get( 'pageclass_sfx' ).'">';

	$c = isset($this->t['categories']) ? count($this->t['categories']) : 0;


	echo $this->loadTemplate('header');
	echo $this->loadTemplate('subcategories');
	echo $this->loadTemplate('pagination_top');
	echo '<div id="phItemsBox">';
}


// ITEMS
if (!empty($this->items)) {

	$price			= new PhocacartPrice;
	$col 			= PhocacartRenderFront::getColumnClass((int)$this->t['columns_cat']);
	$lt				= $this->t['layouttype'];
	$i				= 1; // Not equal Heights

	echo '<div id="phItems" class="ph-items '.$lt.'">';
	echo '<div class="row '.$this->t['class-row-flex'].' '.$lt.'">';

	foreach ($this->items as $v) {


		$label 				= PhocacartRenderFront::getLabel($v->date, $v->sales, $v->featured);
		$link 				= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));


		// Image data
		$attributesOptions 	= $this->t['hide_attributes_category'] == 0 ? PhocacartAttribute::getAttributesAndOptions((int)$v->id) : array();

		if (!isset($v->additional_image)) { $v->additional_image = '';}
		$image = PhocacartImage::getImageDisplay($v->image, $v->additional_image, $this->t['pathitem'], $this->t['switch_image_category_items'], $this->t['image_width_cat'], $this->t['image_height_cat'], '', $lt, $attributesOptions);

		// :L: IMAGE
		$dI	= array();
		if (isset($image['image']->rel) && $image['image']->rel != '') {
			$dI['product_id']		= (int)$v->id;
			$dI['layouttype']		= $lt;
			$dI['title']			= $v->title;
			$dI['image']			= $image['image'];
			$dI['default_image']	= $image['default'];
			$dI['image2']			= $image['second'];
			$dI['imagestyle']		= $image['style'];
			$dI['phil']				= $image['phil'];
			$dI['typeview']			= 'Category';
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

			$price->getPriceItemsChangedByAttributes($dP['priceitems'], $attributesOptions, $price, $v);
			$dP['priceitemsorig']= array();
			if ($v->price_original != '' && $v->price_original > 0) {
				$dP['priceitemsorig'] = $price->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype);
			}
			//$dP['class']		= 'ph-category-price-box '.$lt;
			$dP['class']		= 'ph-category-price-box';// Cannot be dynamic as can change per ajax - this can cause jumping of boxes
			$dP['product_id']	= (int)$v->id;
			$dP['typeview']		= 'Category';


			// Display discount price
			// Move standard prices to new variable (product price -> product discount)
			$dP['priceitemsdiscount']		= $dP['priceitems'];
			$dP['discount'] 				= PhocacartDiscountProduct::getProductDiscountPrice($v->id, $dP['priceitemsdiscount']);

			// Display cart discount (global discount) in product views - under specific conditions only
			// Move product discount prices to new variable (product price -> product discount -> product discount cart)
			$dP['priceitemsdiscountcart']	= $dP['priceitemsdiscount'];
			$dP['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($v->id, $v->catid, $dP['priceitemsdiscountcart']);

			$dP['zero_price']		= 1;// Apply zero price if possible
		}


		// :L: LINK TO PRODUCT VIEW
		$dV = array();
		if ((int)$this->t['display_view_product_button'] > 0) {
			$dV['link']							= $link;
			$dV['display_view_product_button'] 	= $this->t['display_view_product_button'];
		}

		// :L: ADD TO CART
		$dA = $dA2 = $dA3 = $dAb = $dF = array();
		$icon['addtocart'] = '';

		// STOCK ===================================================
		// Set stock: product, variations, or advanced stock status
		$dSO 				= '';
		$dA['class_btn']	= '';
		$dA['class_icon']	= '';
		if ($this->t['display_stock_status'] == 2 || $this->t['display_stock_status'] == 3) {

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
				$dS['typeview']				= 'Category';
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

		if ((int)$this->t['category_addtocart'] == 1 || (int)$this->t['category_addtocart'] == 4 || $this->t['display_addtocart_icon'] == 1) {

			// FORM DATA
			$dF['linkch']				= $this->t['linkcheckout'];// link to checkout (add to cart)
			$dF['id']					= (int)$v->id;
			$dF['catid']				= $this->t['categoryid'];
			$dF['return']				= $this->t['actionbase64'];
			$dF['typeview']				= 'Category';
			$dA['addtocart']			= $this->t['category_addtocart'];
			$dA['addtocart_icon']		= $this->t['display_addtocart_icon'];


			// Both buttons + icon
			$dA['id']					= (int)$v->id;
			$dA['link']					= $link;// link to item (product) view e.g. when there are required attributes - we cannot add it to cart
			$dA['addtocart']			= $this->t['category_addtocart'];
			$dA['method']				= $this->t['add_cart_method'];
			$dA['typeview']				= 'Category';

			// ATTRIBUTES, OPTIONS
			$dAb['attr_options']			= $attributesOptions;
			$dAb['hide_attributes']			= $this->t['hide_attributes_category'];
			$dAb['zero_attribute_price']	= $this->t['zero_attribute_price'];
			$dAb['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
			$dAb['pathitem']				= $this->t['pathitem'];
			$dAb['product_id']				= (int)$v->id;
			$dAb['image_size']				= $image['size'];
			$dAb['typeview']				= 'Category';
			$dAb['price']					= $price;

			// Attribute is required and we don't display it in category/items view, se we need to redirect to detail view
			$dA['selectoptions']	= 0;
			if (isset($v->attribute_required) && $v->attribute_required == 1 && $this->t['hide_attributes_category'] == 1) {
				$dA['selectoptions']	= 1;
			}

			// Add To Cart as Icon
			if ($this->t['display_addtocart_icon'] == 1) {
				$icon['addtocart'] 	= $layoutAI->render($dA);
			}

		}

		// Different button or icons
		//$addToCartHidden = 0;// Button can be hidden based on price

		if ($this->t['hide_add_to_cart_zero_price'] == 1 && $v->price == 0) {
			// Don't display Add to Cart in case the price is zero
			//$addToCartHidden = 1;
			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form
		} else if ((int)$this->t['category_addtocart'] == 1 || (int)$this->t['category_addtocart'] == 4) {
			// ADD TO CART BUTTONS - we have data yet
		} else if ((int)$this->t['category_addtocart'] == 102 && (int)$v->external_id != '') {
			// EXTERNAL LINK PADDLE
			$dA2['external_id']		= (int)$v->external_id;
			$dA2['return']			= $this->t['actionbase64'];

			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form

		} else if ((int)$this->t['category_addtocart'] == 103 && $v->external_link != '') {
			// EXTERNAL LINK
			$dA3['external_link']	= $v->external_link;
			$dA3['external_text']	= $v->external_text;
			$dA3['return']			= $this->t['actionbase64'];

			$dA = array(); // Skip Standard Add to cart button
			$icon['addtocart'] = '';// Skip Add to cart icon
			$dF = array();// Skip form

		} else {
			// ADD TO CART ICON ONLY (NO BUTTONS)
			$dA = array(); // Skip Standard Add to cart button
			// We remove the $dA completely, even for the icon, but the icon has the data already stored in $icon['addtocart']
			// so no problem with removing the data completely
			// $dA for button will be rendered
			// $dA for icon was rendered already
			// Do not skip the form here
		}
		// ---------------------------- END BUTTONS


		// ======
		// RENDER
		// ======
		echo '<div class="row-item col-sx-12 col-sm-'.$col.' col-md-'.$col.'">';
		echo '<div class="ph-item-box '.$lt.'">';

		// LABELS
		echo '<div class="ph-label-box">';
		echo $label['new'] . $label['hot'] . $label['feat'];
		$tagLabelsOutput = PhocacartTag::getTagsRendered((int)$v->id, 1);
		if ($tagLabelsOutput != '') {
			echo $tagLabelsOutput;
		}
		echo '</div>';

		echo '<div class="'.$this->t['class_thumbnail'].' ph-thumbnail ph-thumbnail-c ph-item '.$lt.'">';
		echo '<div class="ph-item-content '.$lt.'">';


		if ($lt == 'list') {
			// -----------
			// RENDER LIST
			// -----------

			echo '<div class="row ph-item-content-row jf_ph_cat_list">';

			// 1/3
			echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">';
			// :L: IMAGE
			echo '<a href="'.$link.'">';
			if (!empty($dI)) { echo $layoutI->render($dI);}
			echo '</a>';
			echo '</div>';// end row-item 1/3

			// 2/3
			echo '<div class="row-item col-sx-12 col-sm-5 col-md-5">';

			// CAPTION, DESCRIPTION BOX
			echo '<div class="ph-item-action-box ph-caption '.$lt.'">';

			echo PhocacartRenderFront::renderProductHeader($this->t['product_name_link'], $v, 'item', '', $lt);

			if ($this->t['cv_display_description'] == 1 && $v->description != '') {
				echo '<div class="ph-item-desc">';
				echo JHtml::_('content.prepare', $v->description);
				echo '</div>';// end desc
			}

			echo '</div>';// end caption

			echo '</div>';// end row-item 2/3

			// 3/3
			echo '<div class="row-item col-sx-12 col-sm-5 col-md-5">';

			// :L: PRICE
			if (!empty($dP)) { echo $layoutP->render($dP);}

			if ($this->t['fade_in_action_icons'] == 0) {
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
			}

			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0) {
				$d							= array();
				$d['rating']				= isset($v->rating) && (int)$v->rating > 0 ? (int)$v->rating : 0;
				$d['size']					= 16;
				$d['display_star_rating']	= (int)$this->t['display_star_rating'];
				echo $layoutR->render($d);
			}

			// VIEW PRODUCT BUTTON
			echo '<div class="ph-category-add-to-cart-box '.$lt.'">';

			// :L: Stock status
			if (!empty($dSO)) { echo $dSO;}

			// Start Form
			if (!empty($dF)) { echo $layoutPFS->render($dF);}

			// :L: ATTRIBUTES AND OPTIONS
			if (!empty($dAb)) { echo $layoutAB->render($dAb);}

			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}

			// :L: ADD TO CART
			if (!empty($dA)) { echo $layoutA->render($dA);} else if ($icon['addtocart'] != '') { echo $layoutBSH->render();}

			// End Form
			if (!empty($dF)) { echo $layoutPFE->render();}


			// :L: External link buttons
			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}

			echo '</div>';// end add to cart box

			$results = \JFactory::getApplication()->triggerEvent('onCategoryItemAfterAddToCart', array('com_phocacart.category', &$v, &$this->p));
			echo trim(implode("\n", $results));
			echo '<div class="ph-item-clearfix '.$lt.'"></div>';


			if ($this->t['fade_in_action_icons'] == 1) {
				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';
			}

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
			echo '<div class="ph-item-action-box ph-caption '.$lt.'">';

			echo PhocacartRenderFront::renderProductHeader($this->t['product_name_link'], $v, 'item', '', $lt);

			// :L: PRICE
			if (!empty($dP)) { echo $layoutP->render($dP);}


			if ($this->t['fade_in_action_icons'] == 0) {
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
			}



			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0) {
				$d							= array();
				$d['rating']				= isset($v->rating) && (int)$v->rating > 0 ? (int)$v->rating : 0;
				$d['size']					= 16;
				$d['display_star_rating']	= (int)$this->t['display_star_rating'];
				echo $layoutR->render($d);
			}

			if ($this->t['cv_display_description'] == 1 && $v->description != '') {
				echo '<div class="ph-item-desc">';
				echo JHtml::_('content.prepare', $v->description);
				echo '</div>';// end desc
			}

			// VIEW PRODUCT BUTTON
			echo '<div class="ph-category-add-to-cart-box '.$lt.'">';

			// :L: Stock status
			if (!empty($dSO)) { echo $dSO;}

			// Start Form
			if (!empty($dF)) { echo $layoutPFS->render($dF);}

			// :L: ATTRIBUTES AND OPTIONS
			if (!empty($dAb)) { echo $layoutAB->render($dAb);}

			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}

			// :L: ADD TO CART
			if (!empty($dA)) { echo $layoutA->render($dA);} else if ($icon['addtocart'] != '') { echo $layoutBSH->render();}

			// End Form
			if (!empty($dF)) { echo $layoutPFE->render();}

			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}

			echo '</div>';// end add to cart box

			$results = \JFactory::getApplication()->triggerEvent('onCategoryItemAfterAddToCart', array('com_phocacart.category', &$v, &$this->p));
			echo trim(implode("\n", $results));


			echo '</div>';// end caption

			echo '<div class="ph-item-clearfix '.$lt.'"></div>';

			if ($this->t['fade_in_action_icons'] == 1) {
				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';
			}

			echo '<div class="ph-item-clearfix '.$lt.'"></div>';


			echo '</div>';// end row-item 3/3

			echo '</div>';// end row list


		} else  {
			// -----------
			// RENDER GRID
			// -----------
			echo '<div class="jf_ph_cat_item_grid">';
			// :L: IMAGE
			echo '<a href="'.$link.'">';
			if (!empty($dI)) { echo $layoutI->render($dI);}
			echo '</a>';

			echo '<div class="jf_ph_cat_item_btns_wrap">';

			if ($this->t['fade_in_action_icons'] == 0) {
				echo $icon['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
			}

			$results = \JFactory::getApplication()->triggerEvent('onCategoryItemAfterAddToCart', array('com_phocacart.category', &$v, &$this->p));
			echo trim(implode("\n", $results));


			echo '</div>';
			echo '</div>';

			echo '<div class="ph-item-clearfix '.$lt.'"></div>';

			// CAPTION, DESCRIPTION BOX


			//echo '<div class="ph-caption  '.$lt.'">';
			echo PhocacartRenderFront::renderProductHeader($this->t['product_name_link'], $v, 'item', '', $lt);
			//echo '</div>';// end caption




			// REVIEW - STAR RATING
			if ((int)$this->t['display_star_rating'] > 0) {
				$d							= array();
				$d['rating']				= isset($v->rating) && (int)$v->rating > 0 ? (int)$v->rating : 0;
				$d['size']					= 16;
				$d['display_star_rating']	= (int)$this->t['display_star_rating'];
				echo $layoutR->render($d);
			}

			if ($this->t['cv_display_description'] == 1 && $v->description != '') {
				echo '<div class="ph-item-desc">';
				echo JHtml::_('content.prepare', $v->description);
				echo '</div>';// end desc
			}

			echo '<div class="ph-item-action-box '.$lt.'">';

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

			// :L: LINK TO PRODUCT VIEW
			if (!empty($dV)) { echo $layoutV->render($dV);}

			// :L: ADD TO CART

			if (!empty($dA)) { echo $layoutA->render($dA);} else if ($icon['addtocart'] != '') { echo $layoutBSH->render();}

			// End Form
			if (!empty($dF)) { echo $layoutPFE->render();}

			if (!empty($dA2)) { echo $layoutA2->render($dA2);}
			if (!empty($dA3)) { echo $layoutA3->render($dA3);}

			echo '</div>';// end add to cart box


			if ($this->t['fade_in_action_icons'] == 1) {

				echo '<div class="ph-item-action-fade '.$lt.'">';
				echo $icon['compare'];
				echo $icon['wishlist'];
				echo $icon['quickview'];
				echo $icon['addtocart'];
				echo '</div>';

			}

			echo '</div>';// end action box


		}
		// --------------- END RENDER



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
	echo PhocacartUtilsInfo::getInfo();
}
?>
