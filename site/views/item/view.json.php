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
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		//$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
	//	$this->t['enable_item_navigation']	= $this->p->get( 'enable_item_navigation', 0 );
		$this->t['item_addtocart']			= $this->p->get( 'item_addtocart', 1 );
	//	$this->t['enable_review']			= $this->p->get( 'enable_review', 1 );
		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
	/*	$this->t['image_popup_method']		= $this->p->get( 'image_popup_method', 1 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		= $this->p->get( 'display_wishlist', 0 );
		$this->t['add_compare_method']		= $this->p->get( 'add_compare_method', 0 );
		$this->t['add_wishlist_method']		= $this->p->get( 'add_wishlist_method', 0 );*/
		$this->t['hide_price']				= $this->p->get( 'hide_price', 0 );
		$this->t['hide_addtocart']			= $this->p->get( 'hide_addtocart', 0 );
		$this->t['hide_attributes_item']	= $this->p->get( 'hide_attributes_item', 0 );
		$this->t['hide_attributes']			= $this->p->get( 'hide_attributes', 0 );
	/*	$this->t['item_askquestion']		= $this->p->get( 'item_askquestion', 0 );
		$this->t['popup_askquestion']		= $this->p->get( 'popup_askquestion', 1 );*/
		$this->t['enable_rewards']			= $this->p->get( 'enable_rewards', 1 );
		
			// Catalogue function
		if ($this->t['hide_addtocart'] == 1) {
			$this->t['item_addtocart']		= 0;
			//$this->t['display_addtocart_icon'] 	= 0;
			//$this->t['hide_attributes_category']= 1; Should be displayed or not?
		}
		if ($this->t['hide_attributes'] == 1) {
			$this->t['hide_attributes_item'] = 1;
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
			$this->t['attr_options']		= $this->t['hide_attributes_item'] == 0 ? PhocacartAttribute::getAttributesAndOptions((int)$id) : array();
			$this->t['specifications']		= PhocacartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
			//$this->t['reviews']				= PhocacartReview::getReviewsByProduct((int)$id);
		
			//$this->t['action']				= $uri->toString();
			$this->t['action']				= JRoute::_(PhocacartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= JRoute::_(PhocacartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkitem']			= JRoute::_(PhocacartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

		
			$o2 = '';
			if (!empty($this->item[0])) {		
				$o2 = $this->loadTemplate('quickview');
				$model->hit((int)$id);
				PhocacartStatisticsHits::productHit((int)$id);		
			}

			$response = array(
			'status'	=> '1',
			'item'		=> '',
			'popup'		=> $o2);
		
			echo json_encode($response);
			return;
		}
	}
		
	protected function _prepareDocument() {}
}
?>