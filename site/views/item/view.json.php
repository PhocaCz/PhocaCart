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
		$this->t['pathitem'] = PhocacartPath::getPath('productimage');
		
		if (!$this->item) {
			

			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">'.JText::_('COM_PHOCACART_NO_PRODUCT_FOUND').'</span>');
			echo json_encode($response);
			return;
			
		} else {
		
			//$this->t['add_images']			= PhocacartImage::getAdditionalImages((int)$id);
			//$this->t['rel_products']		= PhocacartRelated::getRelatedItemsById((int)$id, 0, 1);
			//$this->t['tags_output']			= PhocacartTag::getTagsRendered((int)$id);
			$this->t['stock_status']		= PhocacartStock::getStockStatus((int)$this->item[0]->stock, (int)$this->item[0]->min_quantity, (int)$this->item[0]->min_multiple_quantity, (int)$this->item[0]->stockstatus_a_id,  (int)$this->item[0]->stockstatus_n_id);
			$this->t['stock_status_output'] = PhocacartStock::getStockStatusOutput($this->t['stock_status']);
			$this->t['attr_options']		= PhocacartAttribute::getAttributesAndOptions((int)$id);
			$this->t['specifications']		= PhocacartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
			//$this->t['reviews']				= PhocacartReview::getReviewsByProduct((int)$id);
		
			//$this->t['action']				= $uri->toString();
			$this->t['action']				= JRoute::_(PhocacartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= JRoute::_(PhocacartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkitem']			= JRoute::_(PhocacartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

		
			
			/*
			$media = new PhocacartRenderMedia();
			$media->loadBootstrap($this->t['load_bootstrap']);
			$media->loadChosen($this->t['load_chosen']);
			
			$media->loadRating();
			$media->loadPhocaSwapImage($this->t['dynamic_change_image']);
			$media->loadPhocaAttribute(1);
			
			if ($this->t['image_popup_method'] == 2) {
				PhocacartRenderJs::renderMagnific();
				$this->t['image_rel'] = 'rel="magnific" class="magnific"';
			} else {
				PhocacartRenderJs::renderPrettyPhoto();
				$this->t['image_rel'] = 'rel="prettyPhoto[pc_gal1]"';
			}

			if ($this->t['dynamic_change_price'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductPriceByOptions((int)$this->item[0]->id, 'ph-item-price-box');
			}
			
			PhocacartRenderJs::renderAjaxAddToCart();
			PhocacartRenderJs::renderAjaxAddToCompare();
			PhocacartRenderJs::renderAjaxAddToWishList();
			
			if ($this->t['popup_askquestion'] == 1) {
				$document->addScript(JURI::root(true).'/media/com_phocacart/js/windowpopup.js');
			}
			
			
			if (isset($this->category[0]) && is_object($this->category[0]) && isset($this->item[0]) && is_object($this->item[0])){
				$this->_prepareDocument($this->category[0], $this->item[0]);
			}
			
			$this->t['pathitem'] = PhocacartPath::getPath('productimage');*/
			
			$layoutP	= new JLayoutFile('product_price', null, array('component' => 'com_phocacart'));
			$layoutA	= new JLayoutFile('button_add_to_cart_item', null, array('component' => 'com_phocacart'));
			$layoutA2	= new JLayoutFile('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
			$layoutQV 	= new JLayoutFile('popup_quickview', null, array('component' => 'com_phocacart'));
			$layoutAtOS	= new JLayoutFile('attribute_options_select', null, array('component' => 'com_phocacart'));
			$layoutAtOC	= new JLayoutFile('attribute_options_checkbox', null, array('component' => 'com_phocacart'));


	
			
$o = '';
$x = $this->item[0];
if (!empty($x)) {
	
	
	$o[] = '<h1>'.$x->title.'</h1>';
	
	$o[] = '<div class="row">';
	
	// === IMAGE PANEL
	$o[] = '<div id="phImageBox" class="col-xs-12 col-sm-6 col-md-6">';
	
	$label 	= PhocacartRenderFront::getLabel($x->date, $x->sales, $x->featured);

	
	// IMAGE
	$o[] = '<div class="ph-item-image-full-box '.$label['cssthumbnail'].'">';
	
	$o[] = $label['new'] . $label['hot'] . $label['feat'];
	
	$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$imageL = PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'large');
	$link 	= JURI::base(true).'/'.$imageL->rel;
		
		 
	
	if (isset($image->rel) && $image->rel != '') {
		//$o[] = '<a href="'.$link.'" '.$this->t['image_rel'].'>';
		// In Quic View there is no linking of image
		// 1) but we use A TAG in javascript jquery.phocaswapimage.js se we need A TAG HERE but we make it inactive
		// 2) we need to do it inactive for switching images which comes with links
		//    and this we will do per customHref in function Display: function(imgBox, form, select, customHref) {
		//    custom href will be javascript:void(0); see this file, line cca 286 phSIO1'.(int)$formId.'.Init
		$o[] = '<a href="javascript:void(0);" '.$this->t['image_rel'].'>';
		$o[] = '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive '.$label['cssthumbnail2'].' ph-image-full"';
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
	$price 				= new PhocacartPrice;// Can be used by options
	if ($this->t['hide_price'] != 1) {
		
		$d					= array();
		$d['priceitems']	= $price->getPriceItems($x->price, $x->taxid, $x->taxrate, $x->taxcalculationtype, $x->taxtitle, $x->unit_amount, $x->unit_unit, 1);
		
		$d['priceitemsorig']= array();
		if ($x->price_original != '' && $x->price_original > 0) {
			$d['priceitemsorig'] = $price->getPriceItems($x->price_original, $x->taxid, $x->taxrate, $x->taxcalculationtype);
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
		
		$o[] =  PhocacartRenderJs::renderPhSwapImageInitialize(1, $this->t['dynamic_change_image'], 1);
		
		$o[] =  '<div class="ph-item-attributes-box" id="phItemAttributesBox">';
		$o[] =  '<h4>'.JText::_('COM_PHOCACART_AVAILABLE_OPTIONS').'</h4>';
		
		foreach ($this->t['attr_options'] as $k => $v) {
			
			
			// SELECTBOX COLOR, SELECTBOX IMAGE
			if ($v->type == 2 || $v->type == 3) {
				$o[] = PhocacartRenderJs::renderPhAttributeSelectBoxInitialize((int)$v->id, (int)$v->type, 1);
			}
			
			// If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
			// Set jquery required validation, which should help to html 5 in case of checkboxes (see more info in the funtion)
			// TYPES SET for JQUERY require control: 4 5 6
			$req = PhocacartRenderJs::renderRequiredParts((int)$v->id, (int)$v->required);
			
			// HTML5 does not know to check checkboxes - if some value is set
			// CHECKBOX, CHECKBOX COLOR, CHECKBOX IMAGE
			if($v->type == 4 || $v->type == 5 || $v->type == 6) {
				$o[] = PhocacartRenderJs::renderCheckBoxRequired((int)$v->id, 1);	
			}
			
			$o[] = '<div class="ph-attribute-title">'.$v->title.$req['span'].'</div>';
			if(!empty($v->options)) {
				
				$d							= array();
				$d['attribute']				= $v;
				$d['required']				= $req;
				$d['dynamic_change_image'] 	= $this->t['dynamic_change_image'];
				$d['pathitem']				= $this->t['pathitem'];
				$d['price']					= $price;

				if ($v->type == 1 || $v->type == 2 || $v->type == 3) {
					$o[] = $layoutAtOS->render($d);// SELECTBOX, SELECTBOX COLOR, SELECTBOX IMAGE
				} else if ($v->type == 4 || $v->type == 5 || $v->type == 6) {
					$o[] = $layoutAtOC->render($d);// CHECKBOX, CHECKBOX COLOR, CHECKBOX COLOR
				}
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
			//ob_start();
			$o2 = $layoutQV->render($d);
			//$o2 = ob_get_contents();
			//ob_end_clean();
		
			//echo implode("\n", $o);
	
			$model->hit((int)$id);
			PhocacartStatisticsHits::productHit((int)$id);
			
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