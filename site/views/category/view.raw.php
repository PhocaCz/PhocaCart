<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class PhocaCartViewCategory extends HtmlView
{
	protected $category;
	protected $subcategories;
	protected $items;
	protected $t;
	protected $r;
	protected $p;
	protected $s;

	function display($tpl = null) {


		$layoutAl 					= new FileLayout('alert', null, array('component' => 'com_phocacart'));
		$app						= Factory::getApplication();
		$this->p 					= $app->getParams();
		$this->s					= PhocacartRenderStyle::getStyles();
		$uri 						= Uri::getInstance();
		$model						= $this->getModel();
		$document					= Factory::getDocument();
		$this->t['categoryid']		= $app->getInput()->get( 'id', 0, 'int' );
		$this->t['limitstart']		= $app->getInput()->get( 'limitstart', 0, 'int' );
		$this->t['ajax'] 			= 1;



		// PARAMS
		$this->t['view']					= 'category';
		$this->t['category_layout_plugin']	= $this->p->get( 'category_layout_plugin', '');
		$this->t['display_new']				= $this->p->get( 'display_new', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		//$this->t['description']			= $this->p->get( 'description', '' );
		$this->t['cv_display_description']	= $this->p->get( 'cv_display_description', 1 );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );
		//$this->t['image_link']			= $this->p->get( 'image_link', 0 );
		$this->t['columns_cat']				= $this->p->get( 'columns_cat', 3 );
		$this->t['columns_cat_mobile']		= $this->p->get( 'columns_cat_mobile', 1 );
		$this->t['columns_subcat_cat']		= $this->p->get( 'columns_subcat_cat', 3 );
		$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
		$this->t['cv_display_subcategories']= $this->p->get( 'cv_display_subcategories', 5 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		= $this->p->get( 'display_wishlist', 0 );
		$this->t['display_quickview']		= $this->p->get( 'display_quickview', 0 );
		$this->t['display_addtocart_icon']	= $this->p->get( 'display_addtocart_icon', 0 );
		$this->t['fade_in_action_icons']	= $this->p->get( 'fade_in_action_icons', 0 );

		// Hide action icon box if no icon displayed
		$this->t['display_action_icons'] = 1;
		if ($this->t['display_compare'] == 0 && $this->t['display_wishlist'] == 0 && $this->t['display_quickview'] == 0 && $this->t['display_addtocart_icon'] == 0) {
			$this->t['display_action_icons'] = 0;
		}

		$this->t['category_addtocart']			= $this->p->get( 'category_addtocart', 1 );
		$this->t['dynamic_change_image']		= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']		= $this->p->get( 'dynamic_change_price', 0 );
		$this->t['dynamic_change_stock']		= $this->p->get( 'dynamic_change_stock', 0 );
		$this->t['remove_select_option_attribute']= $this->p->get( 'remove_select_option_attribute', 1 );
		$this->t['add_compare_method']			= $this->p->get( 'add_compare_method', 2 );
		$this->t['add_wishlist_method']			= $this->p->get( 'add_wishlist_method', 2 );
		$this->t['display_addtocart']			= $this->p->get( 'display_addtocart', 1 );
		$this->t['display_star_rating']			= $this->p->get( 'display_star_rating', 0 );
		$this->t['add_cart_method']				= $this->p->get( 'add_cart_method', 2 );
		$this->t['hide_attributes_category']	= $this->p->get( 'hide_attributes_category', 1 );
		$this->t['hide_attributes']				= $this->p->get( 'hide_attributes', 0 );
		$this->t['display_stock_status']		= $this->p->get( 'display_stock_status', 1 );
		$this->t['hide_add_to_cart_stock']		= $this->p->get( 'hide_add_to_cart_stock', 0 );
		$this->t['zero_attribute_price']		= $this->p->get( 'zero_attribute_price', 1 );
		$this->t['hide_add_to_cart_zero_price']	= $this->p->get( 'hide_add_to_cart_zero_price', 0 );
		$this->t['cv_subcategories_layout']		= $this->p->get( 'cv_subcategories_layout', 1 );
		$this->t['category_askquestion']	 	= $this->p->get( 'category_askquestion', 0 );
		$this->t['popup_askquestion']		    = $this->p->get( 'popup_askquestion', 1 );
		$this->t['display_products_all_subcategories'] = $this->p->get('display_products_all_subcategories', 0);


		// Rights or catalogue options --------------------------------
		$rights								= new PhocacartAccessRights();
		$this->t['can_display_price']		= $rights->canDisplayPrice();
		$this->t['can_display_addtocart']	= $rights->canDisplayAddtocart();
		$this->t['can_display_attributes']	= $rights->canDisplayAttributes();

		if (!$this->t['can_display_addtocart']) {
			$this->t['category_addtocart']		= 0;
			$this->t['display_addtocart_icon'] 	= 0;
			//$this->t['hide_attributes_category']= 1; Should be displayed or not?
		}
		if (!$this->t['can_display_attributes']) {
			$this->t['hide_attributes_category'] = 1;
		}
		// ------------------------------------------------------------

		$this->t['display_view_product_button']	= $this->p->get( 'display_view_product_button', 1 );
		$this->t['product_name_link']			= $this->p->get( 'product_name_link', 0 );
		$this->t['switch_image_category_items']	= $this->p->get( 'switch_image_category_items', 0 );

		$this->t['lazy_load_category_items']	= $this->p->get( 'lazy_load_category_items', 2 );
		$this->t['lazy_load_categories']		= $this->p->get( 'lazy_load_categories', 2 );// Subcategories
		$this->t['medium_image_width']			= $this->p->get( 'medium_image_width', 300 );
		$this->t['medium_image_height']			= $this->p->get( 'medium_image_height', 200 );
		$this->t['display_webp_images']			= $this->p->get( 'display_webp_images', 0 );
		$this->t['category_display_labels']		= $this->p->get( 'category_display_labels', 2 );
		$this->t['category_display_tags']		= $this->p->get( 'category_display_tags', 0 );
		$this->t['category_display_manufacturer']		= $this->p->get( 'category_display_manufacturer', 0 );
		$this->t['manufacturer_alias']			= $this->p->get( 'manufacturer_alias', 'manufacturer');
		$this->t['manufacturer_alias']			= $this->t['manufacturer_alias'] != '' ? trim(PhocacartText::filterValue($this->t['manufacturer_alias'], 'alphanumeric'))  : 'manufacturer';

		$this->t['show_pagination'] 			= $this->p->get('show_pagination');
		$this->t['show_pagination_top'] 		= $this->p->get('show_pagination_top', 1);
		$this->t['display_item_ordering'] 		= $this->p->get('display_item_ordering');
		$this->t['display_item_ordering_top'] 	= $this->p->get('display_item_ordering_top', 1);
		$this->t['show_pagination_limit'] 		= $this->p->get('show_pagination_limit');
		$this->t['show_pagination_limit_top'] 	= $this->p->get('show_pagination_limit_top', 1);
		$this->t['ajax_pagination_category'] 	= $this->p->get('ajax_pagination_category', 0);
		$this->t['display_pagination_labels'] 	= $this->p->get('display_pagination_labels', 1);
		$this->t['show_switch_layout_type'] 	= $this->p->get('show_switch_layout_type', 1);

		$this->category						= $model->getCategory($this->t['categoryid']);

		if (empty($this->category)) {
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_NO_CATEGORY_FOUND')));
		} else {
			$this->subcategories		= $model->getSubcategories($this->t['categoryid']);
			$this->items				= $model->getItemList($this->t['categoryid']);
			$this->t['pagination']		= $model->getPagination($this->t['categoryid']);
			$this->t['ordering']		= $model->getOrdering();
			$this->t['layouttype']		= $model->getLayoutType();

			$this->t['layouttypeactive'] 	= PhocacartRenderFront::setActiveLayoutType($this->t['layouttype']);
			$this->t['columns_cat'] 		= $this->t['layouttype'] == 'grid' ? $this->t['columns_cat'] : 1;
			$this->t['columns_cat_mobile'] 	= $this->t['layouttype'] == 'grid' ? $this->t['columns_cat_mobile'] : 1;


			$uri->delVar('format');// !!! REMOVE format parameter because return url needs to go to standard html
			$this->t['action']			= $uri->toString();
			//$this->t['actionbase64']	= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']	= base64_encode($this->t['action']);
			$this->t['linkcheckout']	= Route::_(PhocacartRoute::getCheckoutRoute(0, (int)$this->t['categoryid']));
			$this->t['linkcomparison']	= Route::_(PhocacartRoute::getComparisonRoute(0, (int)$this->t['categoryid']));
			$this->t['linkwishlist']	= Route::_(PhocacartRoute::getWishListRoute(0, (int)$this->t['categoryid']));
			$this->t['limitstarturl'] 	= $this->t['limitstart'] > 0 ? '&start='.$this->t['limitstart'] : '';
			$this->t['pathcat'] 		= PhocacartPath::getPath('categoryimage');
			$this->t['pathitem'] 		= PhocacartPath::getPath('productimage');


			$this->t['class_row_flex']              = $this->p->get('equal_height', 1)  == 1 ? 'ph-row-flex' : '';
        	$this->t['class_fade_in_action_icons']  = $this->p->get('fade_in_action_icons', 0)  == 1 ? 'b-thumbnail' : '';
        	$this->t['class_lazyload']       		= $this->t['lazy_load_category_items']  == 1 ? 'ph-lazyload' : '';
			$this->t['tag_separator']		    	= $this->p->get( 'tag_separator', ' ' );

			$model->hit((int)$this->t['categoryid']);

			// Plugins ------------------------------------------
			$this->t['event']		= new stdClass;
			$results = Dispatcher::dispatch(new Event\View\Category\BeforeHeader('com_phocacart.category', $this->items, $this->p));
			$this->t['event']->onCategoryBeforeHeader = trim(implode("\n", $results));

			$results = Dispatcher::dispatch(new Event\View\Category\BeforePaginationTop('com_phocacart.category', $this->items, $this->p));
			$this->t['event']->onCategoryBeforePaginationTop = trim(implode("\n", $results));
			// Foreach values are rendered in default foreaches

			// Layout plugins - completely new layout including foreach
			$this->t['pluginlayout']        = false;
			if ($this->t['category_layout_plugin'] != '') {
				$this->t['category_layout_plugin']     = PhocacartText::filterValue($this->t['category_layout_plugin'], 'alphanumeric2');
				$this->t['pluginlayout'] 	        	= PluginHelper::importPlugin('pcl', $this->t['category_layout_plugin']);
			}
			if ($this->t['pluginlayout']) {
				$this->t['show_switch_layout_type'] = 0;
			}
			// END Plugins --------------------------------------

			parent::display($tpl);
		}

	}
}
?>
