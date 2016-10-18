<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.folder' ); 
jimport( 'joomla.filesystem.file' );

class PhocaCartViewItem extends JViewLegacy
{
	protected $item;
	protected $itemnext;
	protected $itemprev;
	protected $category;
	protected $t;
	protected $p;
	protected $u;

	function display($tpl = null){

		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		
		$app = JFactory::getApplication();
		$menus	= $app->getMenu('site', array());
		$items	= $menus->getItems('component', 'com_phocacart');
		
		$app					= JFactory::getApplication();
		$this->p 				= $app->getParams();
		$this->u				= JFactory::getUser();
		$uri 					= JFactory::getURI();
		$model					= $this->getModel();
		$document				= JFactory::getDocument();
		$id						= $app->input->get('id', 0, 'int');
		$catid					= $app->input->get('catid', 0, 'int');
		$this->category			= $model->getCategory($id, $catid);
		$this->item				= $model->getItem($id, $catid);
		$this->t['catid']		= 0;
		if (isset($this->category[0]->id)) {
			$this->t['catid']	= (int)$this->category[0]->id;
		}
	
		// PARAMS
		$this->t['tax_calculation'] 		= $this->p->get( 'tax_calculation', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		//$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
	//	$this->t['enable_item_navigation']	= $this->p->get( 'enable_item_navigation', 0 );
		$this->t['item_addtocart']			= $this->p->get( 'item_addtocart', 1 );
	//	$this->t['enable_review']			= $this->p->get( 'enable_review', 1 );
		$this->t['load_chosen']				= $this->p->get( 'load_chosen', 1 );
		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
	/*	$this->t['image_popup_method']		= $this->p->get( 'image_popup_method', 1 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		= $this->p->get( 'display_wishlist', 0 );
		$this->t['add_compare_method']		= $this->p->get( 'add_compare_method', 0 );
		$this->t['add_wishlist_method']		= $this->p->get( 'add_wishlist_method', 0 );*/
		$this->t['hide_price']				= $this->p->get( 'hide_price', 0 );
		$this->t['hide_addtocart']			= $this->p->get( 'hide_addtocart', 0 );
		$this->t['hide_attributes']			= $this->p->get( 'hide_attributes', 0 );/*
		$this->t['item_askquestion']		= $this->p->get( 'item_askquestion', 0 );
		$this->t['popup_askquestion']		= $this->p->get( 'popup_askquestion', 1 );*/

		
		if ($this->t['hide_addtocart'] == 1) {
			$this->t['item_addtocart']		= 0;
		}
		
		
		$this->t['image_rel'] = '';
		$this->t['pathitem'] = PhocaCartpath::getPath('productimage');
		
		if (!$this->item) {
			

			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">'.JText::_('COM_PHOCACART_NO_PRODUCT_FOUND').'</span>');
			echo json_encode($response);
			return;
			
		} else {
		
			//$this->t['add_images']			= PhocaCartImage::getAdditionalImages((int)$id);
			//$this->t['rel_products']		= PhocaCartRelated::getRelatedItemsById((int)$id, 0, 1);
			//$this->t['tags_output']			= PhocaCartTag::getTagsRendered((int)$id);
			$this->t['stock_status']		= PhocaCartStock::getStockStatus((int)$this->item[0]->stock, (int)$this->item[0]->min_quantity, (int)$this->item[0]->min_multiple_quantity, (int)$this->item[0]->stockstatus_a_id,  (int)$this->item[0]->stockstatus_n_id);
			$this->t['stock_status_output'] = PhocaCartStock::getStockStatusOutput($this->t['stock_status']);
			$this->t['attr_options']		= PhocaCartAttribute::getAttributesAndOptions((int)$id);
			$this->t['specifications']		= PhocaCartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
			//$this->t['reviews']				= PhocaCartReview::getReviewsByProduct((int)$id);
		
			//$this->t['action']				= $uri->toString();
			$this->t['action']				= JRoute::_(PhocaCartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= JRoute::_(PhocaCartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkitem']			= JRoute::_(PhocaCartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

		
			
			/*
			$media = new PhocaCartRenderMedia();
			$media->loadBootstrap($this->t['load_bootstrap']);
			$media->loadChosen($this->t['load_chosen']);
			
			$media->loadRating();
			$media->loadPhocaSwapImage($this->t['dynamic_change_image']);
			$media->loadPhocaAttribute(1);
			
			if ($this->t['image_popup_method'] == 2) {
				PhocaCartRenderJs::renderMagnific();
				$this->t['image_rel'] = 'rel="magnific" class="magnific"';
			} else {
				PhocaCartRenderJs::renderPrettyPhoto();
				$this->t['image_rel'] = 'rel="prettyPhoto[pc_gal1]"';
			}

			if ($this->t['dynamic_change_price'] == 1) {
				PhocaCartRenderJs::renderAjaxChangeProductPriceByOptions((int)$this->item[0]->id, 'ph-item-price-box');
			}
			
			PhocaCartRenderJs::renderAjaxAddToCart();
			PhocaCartRenderJs::renderAjaxAddToCompare();
			PhocaCartRenderJs::renderAjaxAddToWishList();
			
			if ($this->t['popup_askquestion'] == 1) {
				$document->addScript(JURI::root(true).'/media/com_phocacart/js/windowpopup.js');
			}
			
			
			if (isset($this->category[0]) && is_object($this->category[0]) && isset($this->item[0]) && is_object($this->item[0])){
				$this->_prepareDocument($this->category[0], $this->item[0]);
			}
			
			$this->t['pathitem'] = PhocaCartpath::getPath('productimage');*/
			
			$layoutP	= new JLayoutFile('product_price', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
			$layoutA	= new JLayoutFile('button_add_to_cart_item', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
			$layoutA2	= new JLayoutFile('button_buy_now_paddle', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
			$layoutQV 	= new JLayoutFile('popup_quickview', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');		
			
$o = '';
$x = $this->item[0];
if (!empty($x)) {
	
	
	$o[] = '<h1>'.$x->title.'</h1>';
	
	$o[] = '<div class="row">';
	
	// === IMAGE PANEL
	$o[] = '<div id="phImageBox" class="col-xs-12 col-sm-6 col-md-6">';
	
	$new = $hot = $feat = '';
	$c = 1;
	$new = PhocaCartRenderFront::renderNewIcon($x->date, $c);
	if ($new != '') {$c++;}
	$hot = PhocaCartRenderFront::renderHotIcon($x->sales, $c);
	if ($hot != '') { $c++;}
	$feat = PhocaCartRenderFront::renderFeaturedIcon($x->featured, $c);
	$o[] = $new . $hot . $feat;
	$cssT = '';
	$cssT2 = 'img-thumbnail';
	if ($c > 1) {
		$cssT = 'thumbnail';
		$cssT2 = '';
	}
	
	// IMAGE
	$o[] = '<div class="ph-item-image-full-box '.$cssT.'">';
	$image 	= PhocaCartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$imageL = PhocaCartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$link 	= JURI::base(true).'/'.$imageL->rel;
		
		 
	
	if (isset($image->rel) && $image->rel != '') {
		//$o[] = '<a href="'.$link.'" '.$this->t['image_rel'].'>';
		// In Quic View there is no linking of image
		// 1) but we use A TAG in javascript jquery.phocaswapimage.js se we need A TAG HERE but we make it inactive
		// 2) we need to do it inactive for switching images which comes with links
		//    and this we will do per customHref in function Display: function(imgBox, form, select, customHref) {
		//    custom href will be javascript:void(0); see this file, line cca 286 phSIO1'.(int)$formId.'.Init
		$o[] = '<a href="javascript:void(0);" '.$this->t['image_rel'].'>';
		$o[] = '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive '.$cssT2.' ph-image-full"';
		if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
			$o[] = ' style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px"';
		}
		$o[] = ' />';
		$o[] = '</a>';
	}
	$o[] = '</div>'. "\n";
	
	
	
	$o[] = '</div>';// end image panel


		// === PRICE PANEL
	$o[] = '<div class="col-xs-12 col-sm-6 col-md-6 ph-item-price-panel">';
	
	

	// :L: PRICE
	$price 				= new PhocaCartPrice;// Can be used by options
	if ($this->t['hide_price'] != 1) {
		
		$d					= array();
		$d['priceitems']	= $price->getPriceItems($x->price, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1);
		
		$d['priceitemsorig']= array();
		if ($x->price_original != '' && $x->price_original > 0) {
			$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxrate, $x->taxcalculationtype);
		}
		$d['class']			= 'ph-item-price-box';
		$o[] = '<div id="phItemPriceBox">';
		$o[] = $layoutP->render($d);
		$o[] = '</div>';
	}
	
	// STOCK
	if($this->t['stock_status']['stock_status'] || $this->t['stock_status']['stock_count']) {
		
		$o[] = '<div class="ph-item-stock-box">';
		$o[] = '<div class="ph-stock-txt">'.JText::_('COM_PHOCACART_AVAILABILITY').'</div>';
		
		$o[] = '<div class="ph-stock">'.JText::_($this->t['stock_status_output']).'</div>';
		$o[] = '</div>';
		$o[] = '<div class="ph-cb"></div>';
	}
	
	if($this->t['stock_status']['min_quantity']) {
		
		$o[] = '<div class="ph-item-min-qty-box">';
		$o[] = '<div class="ph-min-qty-txt">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY').'</div>';
		$o[] = '<div class="ph-min-qty">'.$this->t['stock_status']['min_quantity'].'</div>';
		$o[] = '</div>';
		$o[] = '<div class="ph-cb"></div>';
		
	}
	
	if($this->t['stock_status']['min_multiple_quantity']) {
		
		$o[] = '<div class="ph-item-min-qty-box">';
		$o[] = '<div class="ph-min-qty-txt">'.JText::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY').'</div>';
		$o[] = '<div class="ph-min-qty">'.$this->t['stock_status']['min_multiple_quantity'].'</div>';
		$o[] = '</div>';
		$o[] = '<div class="ph-cb"></div>';
		
	}
	
	// This form can get two events:
	// when option selected - price or image is changed id=phItemPriceBoxForm
	// when ajax cart is active and submit button is clicked class=phItemCartBoxForm
	//
	
	$o[] = '<form id="phItemPriceBoxForm" action="'.$this->t['linkcheckout'].'" method="post" class="phItemCartBoxForm form-inline" role="form" data-id="'.(int)$x->id.'">';
	
	// data-id="'.(int)$x->id.'" - needed for dynamic change of price in quick view, we need to get the ID per javascript
	// because Quick View = Items, Category View and there are more products listed, not like in item id
	
	// ATTRIBUTES, OPTIONS
	if (!empty($this->t['attr_options']) && $this->t['hide_attributes'] != 1) {
		

		// Javascript library phSwapImage
		$formId = 1;
		if ($this->t['dynamic_change_image'] == 1) {
			$s = array();
			$s[] = 'jQuery(document).ready(function() {';
			$s[] = '	var phSIO1'.(int)$formId.' = new phSwapImage;';
			$s[] = '	phSIO1'.(int)$formId.'.Init(\'.ph-item-image-full-box\', \'#phItemPriceBoxForm\', \'select.ph-item-input-select-attributes\', \'javascript:void(0);\');';// Added custom href as in Quick View there i no link of image
			$s[] = '	phSIO1'.(int)$formId.'.Display();';
			$s[] = '});';
			//JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
			$o[] = '<script type="text/javascript">'.implode("\n", $s).'</script>';
		}
		
		$o[] = '<div class="ph-item-attributes-box">';
		$o[] = '<h4>'.JText::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';
		
		
		
		foreach ($this->t['attr_options'] as $k => $v) {
			
			
			// Color Type
			if ($v->type == 2 || $v->type == 3) {
				
				// Javascript library phAttribute
				$s = array();
				$s[] = 'jQuery(document).ready(function() {';
				$s[] = '	var phAO'.(int)$v->id.' = new phAttribute;';
				$s[] = '	phAO'.(int)$v->id.'.Init('.(int)$v->id.', '.(int)$v->type.');';
				$s[] = '	phAO'.(int)$v->id.'.Display();';
				$s[] = '});';
				//JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
				$o[] = '<script type="text/javascript">'.implode("\n", $s).'</script>';
			}
			
			// If the attribute is required
			$req 	= '';
			$reqS	= '';
			if (isset($v->required) && $v->required == 1) {
				
				$req = ' required="" aria-required="true"';
				$reqS = '<span class="ph-req">*</span>';
			}

			
			$o[] = '<div class="ph-attribute-title">'.$v->title.$reqS.'</div>';
			if(!empty($v->options)) {
			
				
			
				$o[] = '<div id="phItemBoxAttribute'.$v->id.'"><select id="phItemAttribute'.$v->id.'" name="attribute['.$v->id.']" class="form-control chosen-select ph-item-input-select-attributes" '.$req.'>';
				$o[] = '<option value="">Select Option</option>';
				
				
				
				foreach ($v->options as $k2 => $v2) {
					if($v2->operator == '=') {
						$operator = '';
					} else {
						$operator = $v2->operator;
					}
					$amount = $price->getPriceFormat($v2->amount);
					
					// Images to switch e.g.
					$attrO		= '';
					if ($this->t['dynamic_change_image'] == 1) {
						if (isset($v2->image) && $v2->image != '') {
							$imageO 	= PhocaCartImage::getThumbnailName($this->t['pathitem'], $v2->image, 'large');
							$linkO 		= JURI::base(true).'/'.$imageO->rel;
							if (JFile::exists($imageO->abs)) {
								$attrO		.= 'data-image-option="'.htmlspecialchars($linkO).'"';
							}
						}
					}
					
					// Color
					if ($v->type == 2 && isset($v2->color) && $v2->color != '') {
						$attrO		.= ' data-color="'.strip_tags($v2->color).'"';
					}
					
					// Image
					if ($v->type == 3 && isset($v2->image_small) && $v2->image_small != '') {
						$linkI 		= JURI::base(true).'/'.$this->t['pathitem']['orig_rel'].'/'.$v2->image_small;
						$attrO		.= ' data-image="'.strip_tags($linkI).'"';
					}
					
					$o[] = '<option '.$attrO.' value="'.$v2->id.'">'.htmlspecialchars($v2->title).' ('.$operator.' '.$amount.')</option>';
				}
				
				$o[] = '</select></div>';
				$o[] = '<div id="phItemHiddenAttribute'.$v->id.'" style="display:none;"></div>';
			}
			
		}
		$o[] = '</div>';
		$o[] = '<div class="ph-cb"></div>';
	}
	
	// :L: ADD TO CART
	
	if ((int)$this->t['item_addtocart'] == 1 || (int)$this->t['item_addtocart'] == 4) {
		
		$d					= array();
		$d['id']			= (int)$x->id;
		$d['catid']			= $this->t['catid'];
		$d['return']		= $this->t['actionbase64'];
		$d['addtocart']		= $this->t['item_addtocart'];
		$o[] = $layoutA->render($d);

	} else if ((int)$this->t['item_addtocart'] == 2 && (int)$x->external_id != '') {
		$d					= array();
		$d['external_id']	= (int)$x->external_id;
		$d['return']		= $this->t['actionbase64'];
		
		$o[] = $layoutA2->render($d);
	}
	
	$o[] = '</form>';
	$o[] = '<div class="ph-cb"></div>';


	$o[] = '</div>';// end right side price panel
	$o[] = '</div>';// end row	
			
			
			
}
			
			$d				= array();
			$d['content']	= implode("\n", $o);
			// Popup with info - Continue,Proceed to Comparison list
			ob_start();
			echo $layoutQV->render($d);
			$o2 = ob_get_contents();
			ob_end_clean();
		
			//echo implode("\n", $o);
			$model->hit((int)$id);
			
			$response = array(
			'status'	=> '1',
			'item'		=> '',
			'popup'		=> $o2);
		
			echo json_encode($response);
			return;
		
		
		}
	}
		
	

	
	protected function _prepareDocument() {
	}
}
?>