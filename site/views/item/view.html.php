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
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\Product;

class PhocaCartViewItem extends HtmlView
{
	protected $item;
	protected $itemnext;
	protected $itemprev;
	protected $category;
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	function display($tpl = null){

		$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

		$app = Factory::getApplication();
		//D $menus	= $app->getMenu('site', array());
		//D $items	= $menus->getItems('component', 'com_phocacart');

		$app					= Factory::getApplication();
		$this->p 				= $app->getParams();
		$this->u				= PhocacartUser::getUser();
		$this->s				= PhocacartRenderStyle::getStyles();
		$uri 					= Uri::getInstance();
		$model					= $this->getModel();
		//D $document				= Factory::getDocument();
		$id						= $app->getInput()->get('id', 0, 'int');
		$catid					= $app->getInput()->get('catid', 0, 'int');

		$this->category			= $model->getCategory($id, $catid);

		$this->item				= $model->getItem($id, $catid);

		$this->t['catid']		= 0;
		if (isset($this->category[0]->id)) {
			$this->t['catid']	= (int)$this->category[0]->id;
		}


		// PARAMS
		$this->t['skip_category_view'] 		    = $this->p->get( 'skip_category_view', 0 );
		$this->t['tax_calculation'] 		    = $this->p->get( 'tax_calculation', 0 );
		$this->t['cart_metakey'] 			    = $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			    = $this->p->get( 'cart_metadesc', '' );
		$this->t['display_back']			    = $this->p->get( 'display_back', 3 );
		//$this->t['enable_social']			    = $this->p->get( 'enable_social', 0 );
		$this->t['enable_item_navigation']  	= $this->p->get( 'enable_item_navigation', 0 );
		$this->t['item_addtocart']		    	= $this->p->get( 'item_addtocart', 1 );
		//$this->t['add_cart_method']			= $this->p->get( 'add_cart_method', 0 );
		$this->t['enable_review']			    = $this->p->get( 'enable_review', 1 );
		$this->t['dynamic_change_image']    	= $this->p->get( 'dynamic_change_image', 0);
		$this->t['dynamic_change_price']	    = $this->p->get( 'dynamic_change_price', 0 );
		$this->t['dynamic_change_stock']	    = $this->p->get( 'dynamic_change_stock', 0 );
		$this->t['dynamic_change_id']	   	 	= $this->p->get( 'dynamic_change_id', 0 );
		$this->t['remove_select_option_attribute']= $this->p->get( 'remove_select_option_attribute', 1 );
		$this->t['image_popup_method']	    	= $this->p->get( 'image_popup_method', 1 );
		$this->t['display_compare']			    = $this->p->get( 'display_compare', 0 );
		$this->t['display_wishlist']		    = $this->p->get( 'display_wishlist', 0 );
		$this->t['add_compare_method']		    = $this->p->get( 'add_compare_method', 2 );
		$this->t['add_wishlist_method']		    = $this->p->get( 'add_wishlist_method', 2 );

		$this->t['hide_addtocart']			    = $this->p->get( 'hide_addtocart', 0 );
		$this->t['hide_attributes_item']	    = $this->p->get( 'hide_attributes_item', 0 );
		$this->t['hide_attributes']			    = $this->p->get( 'hide_attributes', 0 );
		$this->t['item_askquestion']		    = $this->p->get( 'item_askquestion', 0 );
		$this->t['popup_askquestion']		    = $this->p->get( 'popup_askquestion', 1 );
		$this->t['title_next_prev']			    = $this->p->get( 'title_next_prev', 1 );
		$this->t['display_public_download']     = $this->p->get( 'display_public_download', 1 );
		$this->t['display_file_play']     		= $this->p->get( 'display_file_play', 1 );
		$this->t['display_external_link']	    = $this->p->get( 'display_external_link', 1 );
		$this->t['enable_rewards']			    = $this->p->get( 'enable_rewards', 1 );
		$this->t['enable_price_history'] 	    = $this->p->get( 'enable_price_history', 0 );
		$this->t['display_stock_status']	    = $this->p->get( 'display_stock_status', 1 );
		$this->t['item_display_delivery_date']	= $this->p->get( 'item_display_delivery_date', 0 );
        $this->t['item_display_size_options']	= $this->p->get( 'item_display_size_options', 0 );
		$this->t['hide_add_to_cart_stock']	    = $this->p->get( 'hide_add_to_cart_stock', 0 );
		$this->t['zero_attribute_price']	    = $this->p->get( 'zero_attribute_price', 1 );
		$this->t['hide_add_to_cart_zero_price']	= $this->p->get( 'hide_add_to_cart_zero_price', 0 );
		$this->t['display_webp_images']			= $this->p->get( 'display_webp_images', 0 );
		$this->t['item_display_labels']			= $this->p->get( 'item_display_labels', 2 );
		$this->t['item_display_tags']			= $this->p->get( 'item_display_tags', 1 );
		$this->t['item_display_parameters']		= $this->p->get( 'item_display_parameters', 0 );


		// Rights or catalogue options --------------------------------
		$rights								= new PhocacartAccessRights();
		$this->t['can_display_price']		= $rights->canDisplayPrice();
		$this->t['can_display_addtocart']	= $rights->canDisplayAddtocart();
		$this->t['can_display_attributes']	= $rights->canDisplayAttributes();

		if (!$this->t['can_display_addtocart']) {
			$this->t['item_addtocart']		= 0;
			//$this->t['display_addtocart_icon'] 	= 0;
			//$this->t['hide_attributes_category']= 1; Should be displayed or not?
		}
		if (!$this->t['can_display_attributes']) {
			$this->t['hide_attributes_item'] = 1;
		}
		// ------------------------------------------------------------

		if (!isset($this->item[0]->id) || (isset($this->item[0]->id) && $this->item[0]->id < 1)) {
            $app->setHeader('status',  '404 Not found');
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_NO_PRODUCT_FOUND')));
		} else {

            // Possible redirect, by ID or by URL
			$currentUrl = Uri::getInstance()->toString();
			if ($this->item[0]->redirect_product_id && $this->item[0]->redirect_product_id != $this->item[0]->id) {
				$redirectProduct = PhocacartProduct::getProductByProductId($this->item[0]->redirect_product_id);
				$linkPreview     = PhocacartRoute::getItemRoute($redirectProduct->id, $redirectProduct->catid, '', '', [$redirectProduct->language]);
				$app->redirect(Route::_($linkPreview));
            } else if ($this->item[0]->redirect_url && $currentUrl != $this->item[0]->redirect_url && $currentUrl != Route::_($this->item[0]->redirect_url)) {
				// Possible TO DO - there can be more checks to prevent from redirect loop
				$app->redirect(Route::_($this->item[0]->redirect_url));
			}

            if ($this->item[0]->published !== 1) {
                // Archived product
                $this->t['can_display_price'] = false;
                $this->t['display_stock_status'] = false;
                $this->t['item_display_delivery_date'] = false;
                $this->item[0]->type = PhocacartProduct::PRODUCT_TYPE_PRICE_ON_DEMAND_PRODUCT;
            }

			$this->t['add_images']			= PhocacartImage::getAdditionalImages((int)$id);
			$this->t['rel_products']		= PhocacartRelated::getRelatedItemsById((int)$id, 0, 1);
            if ($this->item[0]->type == PhocacartProduct::PRODUCT_TYPE_BUNDLE) {
                $this->t['child_products'] = Product\Bundled::getBundledItemsById((int)$id, Product\Bundled::SELECT_COMPLETE_WITH_CATEGORY, true);
            }

			$this->t['tags_output']			= PhocacartTag::getTagsRendered((int)$id, $this->t['item_display_tags'], ' ');
			$this->t['taglabels_output']	= PhocacartTag::getTagsRendered((int)$id, $this->t['item_display_labels'], ' ');
			$this->t['stock_status']		= array();
			//$this->t['stock_status']		= PhocacartStock::getStockStatus((int)$this->item[0]->stock, (int)$this->item[0]->min_quantity, (int)$this->item[0]->min_multiple_quantity, (int)$this->item[0]->stockstatus_a_id,  (int)$this->item[0]->stockstatus_n_id, (int)$this->item[0]->max_quantity);


			//$this->t['stock_status_output'] = PhocacartStock::getStockStatusOutput($this->t['stock_status']);
			$this->t['attr_options']		= $this->t['hide_attributes_item'] == 0 ? PhocacartAttribute::getAttributesAndOptions((int)$id) : array();

			$this->t['specifications']		= PhocacartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
			$this->t['reviews']				= PhocacartReview::getReviewsByProduct((int)$id);

			if ($this->t['enable_price_history']) {
                $currencyRate      = PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();
				$this->t['price_history_data']	= PhocacartPriceHistory::getPriceHistoryChartById((int)$id, $currencyRate);
			}

			$this->t['parameters_output']	= PhocacartParameter::getParametersRendered((int)$id, $this->t['item_display_parameters']);

			$this->t['action']				= $uri->toString();
			//$this->t['action+
            //0']		= base64_encode(htmlspecialchars($this->t['action']));
			$this->t['actionbase64']		= base64_encode($this->t['action']);
			$this->t['linkcheckout']		= Route::_(PhocacartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkitem']			= Route::_(PhocacartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

			$this->t['linkcomparison']	= Route::_(PhocacartRoute::getComparisonRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkwishlist']	= Route::_(PhocacartRoute::getWishListRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->t['linkdownload']	= Route::_(PhocacartRoute::getDownloadRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
			$this->itemnext[0]			= false;
			$this->itemprev[0]			= false;

			if ($this->t['enable_item_navigation'] == 1) {
				if (isset($this->item[0]->ordering) && isset($this->item[0]->catid) && isset($this->item[0]->id) && $this->item[0]->catid > 0 && $this->item[0]->id > 0) {
					$this->itemnext			= $model->getItemNext($this->item[0]->ordering, $this->item[0]->catid);
					$this->itemprev			= $model->getItemPrev($this->item[0]->ordering, $this->item[0]->catid);
				}
			}


			$media = PhocacartRenderMedia::getInstance('main');
			$media->loadBase();
			$media->loadChosen();
			$media->loadRating();
			$media->loadPhocaSwapImage();
			$media->loadPhocaAttribute(1);
			$media->loadTouchSpin('quantity', $this->s['i']);// only css, js will be loaded in ajax success


			if ($this->t['popup_askquestion'] == 1) {
				$media->loadWindowPopup();
			}

			// Possible change of image_popup_method parameter in plugin - to no load e.g. magnific or prettyphoto if not needed
			$pluginData = [
				'image_popup_method' => $this->t['image_popup_method'],
			];
			$result = Dispatcher::dispatch(new Event\View\Item\BeforeLoadImageLibrary($pluginData));
			if ($result) {
				$this->t['image_popup_method'] = $pluginData['image_popup_method'];
			}

			if ($this->t['image_popup_method'] == 2) {
				$media->renderMagnific();
				$this->t['image_rel'] 	= 'rel="magnific"';
				$this->t['image_class']	= 'magnific';
			} else if ($this->t['image_popup_method'] == 1) {
				$media->renderPrettyPhoto();
				$this->t['image_rel'] = 'rel="prettyPhoto[pc_gal1]"';
				$this->t['image_class']	= '';
			} else {
				// None
				$this->t['image_rel'] 	= '';
				$this->t['image_class']	= '';
			}

			if ($this->t['hide_attributes_item'] == 0) {
				$media->loadPhocaAttributeRequired(1); // Some of the attribute can be required and can be a image checkbox
			}

		/*	if ($this->t['dynamic_change_id'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductIdByOptions((int)$this->item[0]->id, 'Item', 'ph-item-id-box');
			}
			if ($this->t['dynamic_change_price'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductPriceByOptions((int)$this->item[0]->id, 'Item', 'ph-item-price-box');
			}
			if ($this->t['dynamic_change_stock'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductStockByOptions((int)$this->item[0]->id, 'Item', 'ph-item-stock-box');
			}*/

			/*if ($this->t['dynamic_change_id'] == 1 || $this->t['dynamic_change_price'] == 1 || $this->t['dynamic_change_stock'] == 1) {
				PhocacartRenderJs::renderAjaxChangeProductDataByOptions((int)$this->item[0]->id, 'Item', 'ph-item-data-box');
			}*/


			PhocacartRenderJs::renderAjaxAddToCart();
			//PhocacartRenderJs::renderAjaxUpdateCart();// used only in POS
			PhocacartRenderJs::renderAjaxAddToCompare();
			PhocacartRenderJs::renderAjaxAddToWishList();
			PhocacartRenderJs::renderAjaxAskAQuestion();
            $media->loadSpec();

			if (isset($this->category[0]) && is_object($this->category[0]) && isset($this->item[0]) && is_object($this->item[0])){
				$this->_prepareDocument($this->category[0], $this->item[0]);
			}

			$this->t['pathitem'] 		= PhocacartPath::getPath('productimage');
			$this->t['pathpublicfile'] 	= PhocacartPath::getPath('publicfile');

		}

		$model->hit((int)$id);
		PhocacartStatisticsHits::productHit((int)$id);

		// Plugins ------------------------------------------
		$this->t['event']		= new stdClass;

		$results = Dispatcher::dispatch(new Event\View\Item\BeforeHeader('com_phocacart.item', $this->item, $this->p));
		$this->t['event']->onItemBeforeHeader = trim(implode("\n", $results));
        if ($this->item) {
            $results = Dispatcher::dispatch(new Event\View\Item\AfterAddToCart('com_phocacart.item', $this->item, $this->p));
            $this->t['event']->onItemAfterAddToCart = trim(implode("\n", $results));

            $results = Dispatcher::dispatch(new Event\View\Item\BeforeEndPricePanel('com_phocacart.item', $this->item, $this->p));
            $this->t['event']->onItemBeforeEndPricePanel = trim(implode("\n", $results));

            $results = Dispatcher::dispatch(new Event\View\Item\InsideTabPanel('com_phocacart.item', $this->item, $this->p));
            $this->t['event']->onItemInsideTabPanel = [];
            foreach ($results as $result) {
                if (!is_array($result)) {
                    continue;
                }

                if (isset($result['alias']) && isset($result['title']) && isset($result['content'])) {
                    $this->t['event']->onItemInsideTabPanel[] = $result;
                    continue;
                }

                foreach ($result as $subresult) {
                    if (is_array($subresult) && isset($subresult['alias']) && isset($subresult['title']) && isset($subresult['content'])) {
                        $this->t['event']->onItemInsideTabPanel[] = $subresult;
                    }
                }
            }

            $results = Dispatcher::dispatch(new Event\View\Item\AfterTabs('com_phocacart.item', $this->item, $this->p));
            $this->t['event']->onItemAfterTabs = trim(implode("\n", $results));

            // Some payment plugins want to display specific information in detail view
            $results = Dispatcher::dispatch(new Event\Payment\ItemBeforeEndPricePanel('com_phocacart.item', $this->item, $this->p));
            $this->t['event']->PCPonItemBeforeEndPricePanel = trim(implode("\n", $results));
        }
		// END Plugins --------------------------------------

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
		PhocacartRenderFront::prepareDocument($this->document, $this->p, $category, $item);
	}
}
