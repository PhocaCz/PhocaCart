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

class PhocaCartViewItems extends JViewLegacy
{
	protected $category;
	protected $subcategories;
	protected $items;
	protected $t;
	protected $p;
	
	function display($tpl = null) {		
		
		$app						= JFactory::getApplication();
		$this->p 					= $app->getParams();
		$uri 						= JFactory::getURI();
		$model						= $this->getModel();
		$document					= JFactory::getDocument();
		$this->t['categoryid']		= $app->input->get( 'id', 0, 'int' );
		$this->t['limitstart']		= $app->input->get( 'limitstart', 0, 'int' );
		$this->t['ajax'] 			= 1;
		
		// PARAMS
		$this->t['display_new']				= $this->p->get( 'display_new', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		//$this->t['description']			= $this->p->get( 'description', '' );
		$this->t['cv_display_description']	= $this->p->get( 'cv_display_description', 1 );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );
		//$this->t['image_link']			= $this->p->get( 'image_link', 0 );
		$this->t['columns_cat']				= $this->p->get( 'columns_cat', 3 );
		$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
		$this->t['cv_display_subcategories']= $this->p->get( 'cv_display_subcategories', 5 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		= $this->p->get( 'display_wishlist', 0 );
		$this->t['display_quickview']		= $this->p->get( 'display_quickview', 0 );
		$this->t['display_addtocart_icon']	= $this->p->get( 'display_addtocart_icon', 0 );
		$this->t['fade_in_action_icons']	= $this->p->get( 'fade_in_action_icons', 0 );
		$this->t['dynamic_change_price']	= $this->p->get( 'dynamic_change_price', 0 );
		$this->t['category_addtocart']		= $this->p->get( 'category_addtocart', 1 );
		$this->t['dynamic_change_image']	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['add_compare_method']		= $this->p->get( 'add_compare_method', 0 );
		$this->t['add_wishlist_method']		= $this->p->get( 'add_wishlist_method', 0 );
		$this->t['hide_price']				= $this->p->get( 'hide_price', 0 );
		$this->t['hide_addtocart']			= $this->p->get( 'hide_addtocart', 0 );
		$this->t['display_star_rating']		= $this->p->get( 'display_star_rating', 0 );
		$this->t['add_cart_method']			= $this->p->get( 'add_cart_method', 0 );
		$this->t['hide_attributes_category']= $this->p->get( 'hide_attributes_category', 1 );
		$this->t['hide_attributes']			= $this->p->get( 'hide_attributes', 0 );
	
		// Catalogue function
		if ($this->t['hide_addtocart'] == 1) {
			$this->t['category_addtocart']		= 0;
			$this->t['display_addtocart_icon'] 	= 0;
			//$this->t['hide_attributes_category']= 1; Should be displayed or not?
		}
		if ($this->t['hide_attributes'] == 1) {
			$this->t['hide_attributes_category'] = 1;
		}
		
		$this->t['display_view_product_button']	= $this->p->get( 'display_view_product_button', 1 );
		$this->t['product_name_link']			= $this->p->get( 'product_name_link', 0 );
		$this->t['switch_image_category_items']	= $this->p->get( 'switch_image_category_items', 0 );
		
		//$this->category						= $model->getCategory($this->t['categoryid']);
		//$this->subcategories		= $model->getSubcategories($this->t['categoryid']);
		$this->items				= $model->getItemList();
		$this->t['pagination']		= $model->getPagination();
		$this->t['ordering']		= $model->getOrdering();
		$this->t['layouttype']		= $model->getLayoutType();

		$this->t['layouttypeactive'] 	= PhocacartRenderFront::setActiveLayoutType($this->t['layouttype']);
		$this->t['columns_cat'] 		= $this->t['layouttype'] == 'grid' ? $this->t['columns_cat'] : 1;

		$this->t['action']			= $uri->toString();
		//$this->t['actionbase64']	= base64_encode(htmlspecialchars($this->t['action']));
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkcheckout']	= JRoute::_(PhocacartRoute::getCheckoutRoute(0));
		$this->t['linkcomparison']	= JRoute::_(PhocacartRoute::getComparisonRoute(0));
		$this->t['linkwishlist']	= JRoute::_(PhocacartRoute::getWishListRoute(0));
		$this->t['limitstarturl'] 	= $this->t['limitstart'] > 0 ? '&start='.$this->t['limitstart'] : '';
		$this->t['pathcat'] 		= PhocacartPath::getPath('categoryimage');
		$this->t['pathitem'] 		= PhocacartPath::getPath('productimage');
		
		$media 						= new PhocacartRenderMedia();
		$this->t['class-row-flex'] 	= $media->loadEqualHeights();
		$this->t['class_thumbnail'] = $media->loadProductHover();
		
		
	
		
		//$model->hit((int)$this->t['categoryid']);
		
		// Plugins ------------------------------------------
		JPluginHelper::importPlugin('pcv');
		$this->t['dispatcher']	= JEventDispatcher::getInstance();
		$this->t['event']		= new stdClass;
			
		// Foreach values are rendered in default foreaches
		// END Plugins --------------------------------------
		
		parent::display($tpl);
		
	}
}
?>