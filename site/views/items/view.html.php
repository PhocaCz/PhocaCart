<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * Why Items View or why category view? Category view always has category ID,
 * but items view is here for filtering and searching and this can be without category ID
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

jimport('joomla.application.component.view');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class PhocaCartViewItems extends HtmlView
{
    protected $category;
    //protected $subcategories;
    protected $items;
    protected $t;
    protected $r;
    protected $p;
    protected $s;

    function display($tpl = null) {


        $app                   = Factory::getApplication();
        $this->p               = $app->getParams();
        $this->s               = PhocacartRenderStyle::getStyles();
        $uri                   = Uri::getInstance();
        $model                 = $this->getModel();
        $document              = Factory::getDocument();
        $this->t['categoryid'] = $app->getInput()->get('id', 0, 'int');// optional
        $this->t['limitstart'] = $app->getInput()->get('limitstart', 0, 'int');
        $this->t['ajax']       = 0;



        // PARAMS

		$this->t['view']			= 'items';
		$this->t['items_layout_plugin']	= $this->p->get( 'items_layout_plugin', '');
        $this->t['display_new']   = $this->p->get('display_new', 0);
        $this->t['cart_metakey']  = $this->p->get('cart_metakey', '');
        $this->t['cart_metadesc'] = $this->p->get('cart_metadesc', '');
        //$this->t['description']			= $this->p->get( 'description', '' );
        $this->t['cv_display_description'] = $this->p->get('cv_display_description', 1);
        $this->t['image_width_cat']        = $this->p->get('image_width_cat', '');
        $this->t['image_height_cat']       = $this->p->get('image_height_cat', '');
        //$this->t['image_link']			= $this->p->get( 'image_link', 0 );
        $this->t['columns_cat']   = $this->p->get('columns_cat', 3);
        $this->t['columns_cat_mobile']   = $this->p->get('columns_cat_mobile', 1);
        $this->t['enable_social'] = $this->p->get('enable_social', 0);
        //$this->t['cv_display_subcategories']= $this->p->get( 'cv_display_subcategories', 5 );
        $this->t['display_back']           = $this->p->get('display_back', 3);
        $this->t['display_compare']        = $this->p->get('display_compare', 0);
        $this->t['display_wishlist']       = $this->p->get('display_wishlist', 0);
        $this->t['display_quickview']      = $this->p->get('display_quickview', 0);
        $this->t['display_addtocart_icon'] = $this->p->get('display_addtocart_icon', 0);
        $this->t['fade_in_action_icons']   = $this->p->get('fade_in_action_icons', 0);

        // Hide action icon box if no icon displayed
        $this->t['display_action_icons'] = 1;
        if ($this->t['display_compare'] == 0 && $this->t['display_wishlist'] == 0 && $this->t['display_quickview'] == 0 && $this->t['display_addtocart_icon'] == 0) {
            $this->t['display_action_icons'] = 0;
        }

        $this->t['category_addtocart']          = $this->p->get('category_addtocart', 1);
        $this->t['dynamic_change_image']        = $this->p->get('dynamic_change_image', 0);
        $this->t['dynamic_change_price']        = $this->p->get('dynamic_change_price', 0);
        $this->t['dynamic_change_stock']        = $this->p->get('dynamic_change_stock', 0);
        $this->t['dynamic_change_id']           = $this->p->get('dynamic_change_id', 0);
        $this->t['remove_select_option_attribute']= $this->p->get( 'remove_select_option_attribute', 1 );
        $this->t['add_compare_method']          = $this->p->get('add_compare_method', 2);
        $this->t['add_wishlist_method']         = $this->p->get('add_wishlist_method', 2);
        $this->t['display_addtocart']           = $this->p->get('display_addtocart', 1);
        $this->t['display_star_rating']         = $this->p->get('display_star_rating', 0);
        $this->t['add_cart_method']             = $this->p->get('add_cart_method', 2);
        $this->t['hide_attributes_category']    = $this->p->get('hide_attributes_category', 1);
        $this->t['hide_attributes']             = $this->p->get('hide_attributes', 0);
        $this->t['display_stock_status']        = $this->p->get('display_stock_status', 1);
        $this->t['hide_add_to_cart_stock']      = $this->p->get('hide_add_to_cart_stock', 0);
        $this->t['zero_attribute_price']        = $this->p->get('zero_attribute_price', 1);
        $this->t['hide_add_to_cart_zero_price'] = $this->p->get('hide_add_to_cart_zero_price', 0);
        $this->t['category_askquestion']        = $this->p->get('category_askquestion', 0);
        $this->t['popup_askquestion']           = $this->p->get('popup_askquestion', 1);


        // Rights or catalogue options --------------------------------
        $rights                            = new PhocacartAccessRights();
        $this->t['can_display_price']      = $rights->canDisplayPrice();
        $this->t['can_display_addtocart']  = $rights->canDisplayAddtocart();
        $this->t['can_display_attributes'] = $rights->canDisplayAttributes();

        if (!$this->t['can_display_addtocart']) {
            $this->t['category_addtocart']     = 0;
            $this->t['display_addtocart_icon'] = 0;
            //$this->t['hide_attributes_category']= 1; Should be displayed or not?
        }
        if (!$this->t['can_display_attributes']) {
            $this->t['hide_attributes_category'] = 1;
        }
        // ------------------------------------------------------------

        $this->t['display_view_product_button'] = $this->p->get('display_view_product_button', 1);
        $this->t['product_name_link']           = $this->p->get('product_name_link', 0);
        $this->t['switch_image_category_items'] = $this->p->get('switch_image_category_items', 0);

        $this->t['lazy_load_category_items']      = $this->p->get('lazy_load_category_items', 2);// Products
        $this->t['lazy_load_categories']          = $this->p->get('lazy_load_categories', 2);    // Subcategories
        $this->t['medium_image_width']            = $this->p->get('medium_image_width', 300);
        $this->t['medium_image_height']           = $this->p->get('medium_image_height', 200);
        $this->t['small_image_width']             = $this->p->get('small_image_width', 180);
        $this->t['small_image_height']            = $this->p->get('small_image_height', 120);
        $this->t['display_webp_images']           = $this->p->get('display_webp_images', 0);
        $this->t['category_display_labels']       = $this->p->get('category_display_labels', 2);
        $this->t['category_display_tags']         = $this->p->get('category_display_tags', 0);
        $this->t['category_display_manufacturer'] = $this->p->get('category_display_manufacturer', 0);

        $this->t['manufacturer_alias'] = $this->p->get('manufacturer_alias', 'manufacturer');
        $this->t['manufacturer_alias'] = $this->t['manufacturer_alias'] != '' ? trim(PhocacartText::filterValue($this->t['manufacturer_alias'], 'alphanumeric')) : 'manufacturer';

        $this->t['show_pagination']           = $this->p->get('show_pagination');
        $this->t['show_pagination_top']       = $this->p->get('show_pagination_top', 1);
        $this->t['display_item_ordering']     = $this->p->get('display_item_ordering');
        $this->t['display_item_ordering_top'] = $this->p->get('display_item_ordering_top', 1);
        $this->t['show_pagination_limit']     = $this->p->get('show_pagination_limit');
        $this->t['show_pagination_limit_top'] = $this->p->get('show_pagination_limit_top', 1);
        $this->t['ajax_pagination_category']  = $this->p->get('ajax_pagination_category', 0);
        $this->t['display_pagination_labels'] = $this->p->get('display_pagination_labels', 1);
        $this->t['show_switch_layout_type']   = $this->p->get('show_switch_layout_type', 1);


        //$this->category					= $model->getCategory($this->t['categoryid']);
        //$this->subcategories				= $model->getSubcategories($this->t['categoryid']);
        $this->items = $model->getItemList();

        $this->t['pagination']       = $model->getPagination();




        $this->t['ordering']         = $model->getOrdering();
        $this->t['layouttype']       = $model->getLayoutType();
        $this->t['layouttypeactive'] = PhocacartRenderFront::setActiveLayoutType($this->t['layouttype']);
        $this->t['columns_cat']      = $this->t['layouttype'] == 'grid' ? $this->t['columns_cat'] : 1;
        $this->t['columns_cat_mobile']      = $this->t['layouttype'] == 'grid' ? $this->t['columns_cat_mobile'] : 1;

        $this->t['action'] = $uri->toString();
        //$this->t['actionbase64']			= base64_encode(htmlspecialchars($this->t['action']));
        $this->t['actionbase64']   = base64_encode($this->t['action']);
        $this->t['linkcheckout']   = Route::_(PhocacartRoute::getCheckoutRoute(0));
        $this->t['linkcomparison'] = Route::_(PhocacartRoute::getComparisonRoute(0));
        $this->t['linkwishlist']   = Route::_(PhocacartRoute::getWishListRoute(0));
        $this->t['limitstarturl']  = $this->t['limitstart'] > 0 ? '&start=' . $this->t['limitstart'] : '';

        $this->t['class_row_flex']             = $this->p->get('equal_height', 1) == 1 ? 'ph-row-flex' : '';
        $this->t['class_fade_in_action_icons'] = $this->p->get('fade_in_action_icons', 0) == 1 ? 'b-thumbnail' : '';
        $this->t['class_lazyload']             = $this->t['lazy_load_category_items'] == 1 ? 'ph-lazyload' : '';
        $this->t['tag_separator']		        = $this->p->get( 'tag_separator', ' ' );
        $this->t['image_items_view'] = $this->p->get('image_items_view', '');
        $this->t['image_items_view'] = $this->t['image_items_view'] != '' ? Uri::base(true) . '/' . $this->t['image_items_view'] : '';

        $media = PhocacartRenderMedia::getInstance('main');
        $media->loadBase();
        $media->loadChosen();
        $media->loadProductHover();

        PhocacartRenderJs::renderAjaxAddToCart();
        //PhocacartRenderJs::renderAjaxUpdateCart();// used only in POS
        PhocacartRenderJs::renderAjaxAddToCompare();
        PhocacartRenderJs::renderAjaxAddToWishList();
        // Moved to JS PhocacartRenderJs::renderSubmitPaginationTopForm($this->t['action'], '#phItemsBox');

        if ((int)$this->t['category_askquestion'] > 0) {
            PhocacartRenderJs::renderAjaxAskAQuestion();
            if ($this->t['popup_askquestion'] == 1) {
                $media->loadWindowPopup();
            }
        }

        $touchSpinJs = $media->loadTouchSpin('quantity', $this->s['i']);// only css, js will be loaded in ajax success
        $media->loadPhocaSwapImage($this->t['dynamic_change_image']);


        if ($this->t['hide_attributes_category'] == 0) {
            $media->loadPhocaAttribute(1);
            $media->loadPhocaAttributeRequired(1); // Some of the attribute can be required and can be a image checkbox
        }


        /*	if ($this->t['dynamic_change_price'] == 1) {
                // items == category -> this is why items has class: ph-category-price-box (to have the same styling)
                PhocacartRenderJs::renderAjaxChangeProductPriceByOptions(0, 'Items', 'ph-category-price-box');// We need to load it here
            }
            if ($this->t['dynamic_change_stock'] == 1) {
                PhocacartRenderJs::renderAjaxChangeProductStockByOptions(0, 'Items', 'ph-item-stock-box');
            }*/

     /*   if ($this->t['dynamic_change_id'] == 1 || $this->t['dynamic_change_price'] == 1 || $this->t['dynamic_change_stock'] == 1) {
            PhocacartRenderJs::renderAjaxChangeProductDataByOptions((int)$this->item[0]->id, 'Items', 'ph-category-data-box');
        }*/

        // CHANGE PRICE FOR ITEM QUICK VIEW
        if ($this->t['display_quickview'] == 1 || $this->t['category_addtocart'] == 104) {
            PhocacartRenderJs::renderAjaxQuickViewBox();

            // CHANGE PRICE FOR ITEM QUICK VIEW
            /*if ($this->t['dynamic_change_price'] == 1) {
                PhocacartRenderJs::renderAjaxChangeProductPriceByOptions(0, 'ItemQuick', 'ph-item-price-box');// We need to load it here
            }
            if ($this->t['dynamic_change_stock'] == 1) {
                PhocacartRenderJs::renderAjaxChangeProductStockByOptions(0, 'ItemQuick', 'ph-item-stock-box');
            }*/

           /* if ($this->t['dynamic_change_id'] == 1 || $this->t['dynamic_change_price'] == 1 || $this->t['dynamic_change_stock'] == 1) {
                PhocacartRenderJs::renderAjaxChangeProductDataByOptions(0, 'ItemQuick', 'ph-item-data-box');
            }*/


            $media->loadPhocaAttribute(1);                               // We need to load it here
            $media->loadPhocaSwapImage($this->t['dynamic_change_image']);// We need to load it here in ITEM (QUICK VIEW) VIEW
        }

        $media->loadPhocaMoveImage($this->t['switch_image_category_items']);// Move (switch) images in CATEGORY, ITEMS VIEW

        $media->loadSpec();


        $this->_prepareDocument();
        $this->t['pathcat']  = PhocacartPath::getPath('categoryimage');
        $this->t['pathitem'] = PhocacartPath::getPath('productimage');


        // Plugins ------------------------------------------
        $this->t['event']                      = new stdClass;
        $results                               = Dispatcher::dispatch(new Event\View\Items\BeforeHeader('com_phocacart.items', $this->items, $this->p));
        $this->t['event']->onItemsBeforeHeader = trim(implode("\n", $results));

        $results                               = Dispatcher::dispatch(new Event\View\Items\BeforePaginationTop('com_phocacart.items', $this->items, $this->p));
        $this->t['event']->onItemsBeforePaginationTop = trim(implode("\n", $results));
        // Foreach values are rendered in default foreaches

        // Layout plugins - completely new layout including foreach
        $this->t['pluginlayout']        = false;

        if ($this->t['items_layout_plugin'] != '') {
            $this->t['items_layout_plugin']     = PhocacartText::filterValue($this->t['items_layout_plugin'], 'alphanumeric2');
            $this->t['pluginlayout'] 	        = PluginHelper::importPlugin('pcl', $this->t['items_layout_plugin']);
        }


		if ($this->t['pluginlayout']) {
            $this->t['show_switch_layout_type'] = 0;
        }
        // END Plugins --------------------------------------




        parent::display($tpl);

        echo $media->returnLazyLoad();// Render all bottom scripts // Must be loaded bottom because of ignoring async in Firefox

    }


    protected function _prepareDocument() {
        $category = false;
        if (isset($this->category[0]) && is_object($this->category[0])) {
            $category = $this->category[0];
        }
        PhocacartRenderFront::prepareDocument($this->document, $this->p, $category);
    }
}
