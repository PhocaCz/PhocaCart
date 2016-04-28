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
jimport( 'joomla.itemsystem.folder' ); 
jimport( 'joomla.itemsystem.file' );

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
		$this->t['enable_item_navigation']	= $this->p->get( 'enable_item_navigation', 0 );
		$this->t['item_addtocart']			= $this->p->get( 'item_addtocart', 1 );
		$this->t['enable_review']			= $this->p->get( 'enable_review', 1 );
		$this->t['load_chosen']				= $this->p->get( 'load_chosen', 1 );
		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
		$this->t['image_popup_method']		= $this->p->get( 'image_popup_method', 1 );
		$this->t['add_compare_method']		= $this->p->get( 'add_compare_method', 0 );
		
		if (!$this->item) {
			
			echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_NO_PRODUCT_FOUND').'</div>';
			
		} else {
		
			$this->t['add_images']			= PhocaCartImage::getAdditionalImages((int)$id);
			$this->t['rel_products']		= PhocaCartRelated::getRelatedItemsById((int)$id, 0, 1);
			$this->t['tags_output']			= PhocaCartTag::getTagsRendered((int)$id);
			$this->t['stock_status']		= PhocaCartStock::getStockStatus((int)$this->item[0]->stock, (int)$this->item[0]->min_quantity, (int)$this->item[0]->stockstatus_a_id,  (int)$this->item[0]->stockstatus_n_id);
			$this->t['stock_status_output'] = PhocaCartStock::getStockStatusOutput($this->t['stock_status']);
			$this->t['attr_options']		= PhocaCartAttribute::getAttributesAndOptions((int)$id);
			$this->t['specifications']		= PhocaCartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
			$this->t['reviews']				= PhocaCartReview::getReviewsByProduct((int)$id);
		
			$this->t['action']				= $uri->toString();
			//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= JRoute::_(PhocaCartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkitem']			= JRoute::_(PhocaCartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

			
			
			$this->itemnext[0]			= false;
			$this->itemprev[0]			= false;
			if ($this->t['enable_item_navigation'] == 1) {
				if (isset($this->item[0]->ordering) && isset($this->item[0]->catid) && isset($this->item[0]->id) && $this->item[0]->catid > 0 && $this->item[0]->id > 0) {
					$this->itemnext			= $model->getItemNext($this->item[0]->ordering, $this->item[0]->catid);
					$this->itemprev			= $model->getItemPrev($this->item[0]->ordering, $this->item[0]->catid);
				}
			}
			
			
			$media = new PhocaCartRenderMedia();
			$media->loadBootstrap($this->t['load_bootstrap']);
			$media->loadChosen($this->t['load_chosen']);
			
			$media->loadRating();
			$media->loadSwapImage($this->t['dynamic_change_image']);
			
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
			
			
			if (isset($this->category[0]) && is_object($this->category[0]) && isset($this->item[0]) && is_object($this->item[0])){
				$this->_prepareDocument($this->category[0], $this->item[0]);
			}
			
			$this->t['pathitem'] = PhocaCartpath::getPath('productimage');
			
		}
		$model->hit((int)$id);
		parent::display($tpl);
	}
	

	
	protected function _prepareDocument() {
		$category = false;
		if (isset($this->category[0]) && is_object($this->category[0])) {
			$category = $this->category[0];
		}
		$item = false;
		if (isset($this->item[0]) && is_object($this->item[0])) {
			$item = $this->item[0];
		}
		PhocaCartRenderFront::prepareDocument($this->document, $this->p, $category, $item);
	}
}
?>