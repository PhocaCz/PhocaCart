<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

class PhocaCartControllerCheckout extends FormController
{
    // Set Region
    public function setregion() {

        $layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => $layoutAl->render(array('type' => 'error', 'text' => Text::_('JINVALID_TOKEN'), 'json' => 1)));
            echo json_encode($response);
            exit;
        }

        $app = Factory::getApplication();
        $id  = $app->input->get('countryid', 0, 'int');

        //$model = $this->getModel('checkout');
        //$options = $model->getRegions($id);
        $options = PhocacartRegion::getRegionsByCountry($id);
        $o       = '';
        if (!empty($options)) {

            $o .= '<option value="">-&nbsp;' . Text::_('COM_PHOCACART_SELECT_REGION') . '&nbsp;-</option>';
            foreach ($options as $k => $v) {
                $o .= '<option value="' . $v->id . '">' . $v->title . '</option>';
            }
        }
        $response = array(
            'status' => '1',
            'content' => $o);
        echo json_encode($response);
        exit;

    }

    // Change Data Box
    // a) Price Box
    // b) Stock Box
    // c) ID Box (SKU, EAN, ...)

	// We use common "data" for different parts (price box, stock box, id box) so we need replace -data- class to specific for each case
	// e.g. -data- ==> -price-, -data- ==> -stock-, ... (not used in JS but it can be used there)

    function changedatabox($tpl = null) {

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }

        $paramsC              = PhocacartUtils::getComponentParameters();
        $dynamic_change_price = $paramsC->get('dynamic_change_price', 0);
        $dynamic_change_stock = $paramsC->get('dynamic_change_stock', 0);
        $dynamic_change_id    = $paramsC->get('dynamic_change_id', 0);
        $dynamic_change_image   = $paramsC->get('dynamic_change_image', 0);

        $hide_add_to_cart_stock   = $paramsC->get('hide_add_to_cart_stock', 0);
        $hide_add_to_cart_zero_price   = $paramsC->get('hide_add_to_cart_zero_price', 0);



        $app       = Factory::getApplication();
        $s         = PhocacartRenderStyle::getStyles();
        $attribute = $app->input->get('attribute', '', 'array');
        $id        = $app->input->get('id', 0, 'int');
        $class     = $app->input->get('class', '', 'string');
        $typeView  = $app->input->get('typeview', '', 'string');

        $oA = array();

        // Sanitanize data and do the same level for all attributes:
        $aA = PhocacartAttribute::sanitizeAttributeArray($attribute);



        if ((int)$id > 0) {

            $price = new PhocacartPrice();
            $item  = PhocacartProduct::getProduct((int)$id);// We don't need catid
            //$priceO = array();

            if (!empty($item)) {

                // ==================
                // PRICE
                $priceP = $price->getPriceItems($item->price, $item->taxid, $item->taxrate, $item->taxcalculationtype, $item->taxtitle, 0, '', 1, 1, $item->group_price, $item->taxhide);
                $price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $item, 1);


                $price->correctMinusPrice($priceP);


                if ($dynamic_change_price == 1) {


                    $d               = array();
                    $d['type']       = $item->type;
                    $d['s']          = $s;
                    $d['class']      = str_replace('-data-', '-price-', $class);// change common "data" class to specific one
                    $d['zero_price'] = 1;// Apply zero price if possible
                    // Original Price
                    $d['priceitemsorig']['bruttoformat'] = '';
                    if (isset($item->price_original) && $item->price_original != '' && (int)$item->price_original > 0) {
                        $d['priceitemsorig']['bruttoformat'] = $price->getPriceFormat($item->price_original);
                    }

                    $d['priceitems'] = $priceP;
                    $d['product_id'] = (int)$item->id;
                    $d['typeview']   = $typeView;


                    // Display discount price
                    // Move standard prices to new variable (product price -> product discount)
                    $d['priceitemsdiscount'] = $d['priceitems'];
                    $d['discount']           = PhocacartDiscountProduct::getProductDiscountPrice($item->id, $d['priceitemsdiscount']);

                    // Display cart discount (global discount) in product views - under specific conditions only
                    // Move product discount prices to new variable (product price -> product discount -> product discount cart)
                    $d['priceitemsdiscountcart'] = $d['priceitemsdiscount'];
                    $d['discountcart']           = PhocacartDiscountCart::getCartDiscountPriceForProduct($item->id, $item->catid, $d['priceitemsdiscountcart']);

                    // Render the layout
                    $layoutP     = new FileLayout('product_price', null, array('component' => 'com_phocacart'));

                    $oA['price'] = $layoutP->render($d);
                    $oA['priceitems'] = $d['priceitems'];
                }


                // ==================
                // STOCK
                $stockStatus = array();
                $stock       = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $item, 1);

                if ($dynamic_change_stock == 1) {


                    $o = '';
                    if ($stockStatus['stock_status'] || $stockStatus['stock_count'] !== false) {
                        $layoutS                  = new FileLayout('product_stock', null, array('component' => 'com_phocacart'));
                        $d                        = array();
                        $d['s']                   = $s;
                        $d['class']               = str_replace('-data-', '-stock-', $class);// change common "data" class to specific one
                        $d['product_id']          = (int)$id;
                        $d['typeview']            = $typeView;
                        $d['stock_status_class']	= isset($stockStatus['stock_status_class']) ? $stockStatus['stock_status_class'] : '';
                        $d['stock_status_output'] = PhocacartStock::getStockStatusOutput($stockStatus);
                        $d['ajax']                 = 1;

                        $oA['stock'] = $layoutS->render($d);

                        //$stock						= (int)$stockStatus['stock_count'];// return stock anyway to enable disable add to cart button if set
                    }
                    $oA['stockvalue'] = (int)$stock;
                }

                // ==================
                // ID (EAN, SKU, ...)
                if ($dynamic_change_id == 1) {
                    $id = new PhocacartId();
                    $id->getIdItemsChangedByAttributes($item, $aA, 1);

                    $d               = array();
                    $d['type']       = $item->type;
                    $d['s']          = $s;
                    $d['class']      = str_replace('-data-', '-id-', $class);// change common "data" class to specific one
                    $d['x']          = $item;
                    $d['product_id'] = (int)$item->id;
                    $d['typeview']   = $typeView;

                    // Render the layout
                    $layoutID = new FileLayout('product_id', null, array('component' => 'com_phocacart'));
                    $oA['id'] = $layoutID->render($d);
                }

                // ================
                // IMAGE
                if ($dynamic_change_image == 2) {

                    $params = array();
                    $params['typeview'] = $typeView;

                    PhocacartImage::getImageItemsChangedByAttributes($item, $aA, $params, 1);
                    $oA['image'] = $item->image;

                }


                // Should add to cart be displayed
                $oA['hideaddtocart'] = 1;
                $rights				= new PhocacartAccessRights();

                $priceA = isset($priceP['brutto']) ? $priceP['brutto'] : 0;


                if($rights->canDisplayAddtocartAdvanced($item) && $rights->canDisplayAddtocartPrice($item, $priceA)  && $rights->canDisplayAddtocartStock($item, $stock)) {
		            $oA['hideaddtocart'] = 0;
                }

                $response = array(
                    'status' => '1',
                    'item' => $oA);
                echo json_encode($response);
                return;
            }
        }

        $response = array(
            'status' => '0',
            'items' => '');
        echo json_encode($response);
        return;


    }

    /*
    // Change pricebox
    function changepricebox($tpl = null) {

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }


        $app       = Factory::getApplication();
        $s         = PhocacartRenderStyle::getStyles();
        $attribute = $app->input->get('attribute', '', 'array');
        $id        = $app->input->get('id', 0, 'int');
        $class     = $app->input->get('class', '', 'string');
        $typeView  = $app->input->get('typeview', '', 'string');

        // Sanitanize data and do the same level for all attributes:
        $aA = PhocacartAttribute::sanitizeAttributeArray($attribute);




        if ((int)$id > 0) {
            $price = new PhocacartPrice();
            $item  = PhocacartProduct::getProduct((int)$id);// We don't need catid
            //$priceO = array();

            if (!empty($item)) {

                $priceP = $price->getPriceItems($item->price, $item->taxid, $item->taxrate, $item->taxcalculationtype, $item->taxtitle, 0, '', 1, 1, $item->group_price);

                $price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $item, 1);


                $d               = array();
                $d['type']       = $item->type;
                $d['s']          = $s;
                $d['class']      = $class;
                $d['zero_price'] = 1;// Apply zero price if possible
                // Original Price
                $d['priceitemsorig']['bruttoformat'] = '';
                if (isset($item->price_original) && $item->price_original != '' && (int)$item->price_original > 0) {
                    $d['priceitemsorig']['bruttoformat'] = $price->getPriceFormat($item->price_original);
                }

                $d['priceitems'] = $priceP;
                $d['product_id'] = (int)$item->id;
                $d['typeview']   = $typeView;


                // Display discount price
                // Move standard prices to new variable (product price -> product discount)
                $d['priceitemsdiscount'] = $d['priceitems'];
                $d['discount']           = PhocacartDiscountProduct::getProductDiscountPrice($item->id, $d['priceitemsdiscount']);

                // Display cart discount (global discount) in product views - under specific conditions only
                // Move product discount prices to new variable (product price -> product discount -> product discount cart)
                $d['priceitemsdiscountcart'] = $d['priceitemsdiscount'];
                $d['discountcart']           = PhocacartDiscountCart::getCartDiscountPriceForProduct($item->id, $item->catid, $d['priceitemsdiscountcart']);

                // Render the layout
                $layoutP = new FileLayout('product_price', null, array('component' => 'com_phocacart'));
                //ob_start();
                $o = $layoutP->render($d);
                //$o = ob_get_contents();
                //ob_end_clean();


                $response = array(
                    'status' => '1',
                    'item' => $o);
                echo json_encode($response);
                return;
            }
        }

        $response = array(
            'status' => '0',
            'items' => '');
        echo json_encode($response);
        return;


    }

    // Change idbox (SKU, EAN, ...)
    function changeidbox($tpl = null) {

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }


        $app       = Factory::getApplication();
        $s         = PhocacartRenderStyle::getStyles();
        $attribute = $app->input->get('attribute', '', 'array');
        $id        = $app->input->get('id', 0, 'int');
        $class     = $app->input->get('class', '', 'string');
        $typeView  = $app->input->get('typeview', '', 'string');

        // Sanitanize data and do the same level for all attributes:
        $aA = PhocacartAttribute::sanitizeAttributeArray($attribute);

        if ((int)$id > 0) {
            $item = PhocacartProduct::getProduct((int)$id);// We don't need catid
            if (!empty($item)) {

                $id = new PhocacartId();
                $id->getIdItemsChangedByAttributes($item, $aA, 1);

                $d               = array();
                $d['type']       = $item->type;
                $d['s']          = $s;
                $d['class']      = $class;
                $d['x']          = $item;
                $d['product_id'] = (int)$item->id;
                $d['typeview']   = $typeView;

                // Render the layout
                $layoutID = new FileLayout('product_id', null, array('component' => 'com_phocacart'));
                //ob_start();
                $o = $layoutID->render($d);
                //$o = ob_get_contents();
                //ob_end_clean();

                $response = array(
                    'status' => '1',
                    'item' => $o);
                echo json_encode($response);
                return;
            }
        }

        $response = array(
            'status' => '0',
            'items' => '');
        echo json_encode($response);
        return;

    }

    // Change stockbox
    function changestockbox($tpl = null) {


        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }

        $app       = Factory::getApplication();
        $s         = PhocacartRenderStyle::getStyles();
        $attribute = $app->input->get('attribute', '', 'array');
        $id        = $app->input->get('id', 0, 'int');
        $class     = $app->input->get('class', '', 'string');
        $typeView  = $app->input->get('typeview', '', 'string');

        // Sanitanize data and do the same level for all attributes:
        $aA = PhocacartAttribute::sanitizeAttributeArray($attribute);


        if ((int)$id > 0) {

            $item = PhocacartProduct::getProduct((int)$id);// We don't need catid

            $stockStatus = array();
            $stock       = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $item, 1);

            $o = '';
            if ($stockStatus['stock_status'] || $stockStatus['stock_count'] !== false) {
                $layoutS                  = new FileLayout('product_stock', null, array('component' => 'com_phocacart'));
                $d                        = array();
                $d['s']                   = $s;
                $d['class']               = $class;
                $d['product_id']          = (int)$id;
                $d['typeview']            = $typeView;
                $d['stock_status_class']	= isset($stockStatus['stock_status_class']) ? $stockStatus['stock_status_class'] : '';
                $d['stock_status_output'] = PhocacartStock::getStockStatusOutput($stockStatus);

                $o = $layoutS->render($d);

                //$stock						= (int)$stockStatus['stock_count'];// return stock anyway to enable disable add to cart button if set
            }


            $response = array(
                'status' => '1',
                'stock' => (int)$stock,
                'item' => $o);
            echo json_encode($response);
            return;
        }


        $response = array(
            'status' => '0',
            'items' => '');
        echo json_encode($response);
        return;

    }*/

    // Add item to cart
    function add($tpl = null) {

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }


        $app                  = Factory::getApplication();
        $s                    = PhocacartRenderStyle::getStyles();
        $item                 = array();
        $item['id']           = $this->input->get('id', 0, 'int');
        $item['catid']        = $this->input->get('catid', 0, 'int');
        $item['quantity']     = $this->input->get('quantity', 0, 'int');
        $item['return']       = $this->input->get('return', '', 'string');
        $item['attribute']    = $this->input->get('attribute', array(), 'array');
        $item['checkoutview'] = $this->input->get('checkoutview', 0, 'int');

        if ((int)$item['id'] > 0) {

            $itemP = PhocacartProduct::getProduct((int)$item['id'], $item['catid']);

            if (!empty($itemP)) {

                // Price (don't display add to cart when price is zero)
                $price  = new PhocacartPrice();
                $priceP = $price->getPriceItems($itemP->price, $itemP->taxid, $itemP->taxrate, $itemP->taxcalculationtype, $itemP->taxtitle, 0, '', 1, 1, $itemP->group_price, $itemP->taxhide);
                $aA     = PhocacartAttribute::sanitizeAttributeArray($item['attribute']);
                $price->getPriceItemsChangedByAttributes($priceP, $aA, $price, $itemP, 1);
                $price->correctMinusPrice($priceP);
                $priceA = isset($priceP['brutto']) ? $priceP['brutto'] : 0;

                // Stock (don't display add to cart when stock is zero)
                $stockStatus = array();
                $stock       = PhocacartStock::getStockItemsChangedByAttributes($stockStatus, $aA, $itemP, 1);

                $rights                             = new PhocacartAccessRights();
                $r                                  = [];
                $r['can_display_addtocart']         = $rights->canDisplayAddtocartAdvanced($itemP);
                $r['can_display_addtocart_price']   = $rights->canDisplayAddtocartPrice($itemP, $priceA);
                $r['can_display_addtocart_stock']   = $rights->canDisplayAddtocartStock($itemP, $stock);

                $canDisplay = 1;
                if (!$r['can_display_addtocart']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $canDisplay = 0;
                }

                if (!$r['can_display_addtocart_price']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRICE_IS_ZERO'), 'error');
                    $canDisplay = 0;
                }

                if (!$r['can_display_addtocart_stock']) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');
                    $app->enqueueMessage(Text::_('COM_PHOCACART_STOCK_IS_EMPTY'), 'error');
                    $canDisplay = 0;
                }

                if ($canDisplay == 0) {
                    $d             = array();
                    $d['s']        = $s;
                    $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();
                    $layoutPE      = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
                    $oE            = $layoutPE->render($d);
                    $response      = array(
                        'status' => '0',
                        'popup' => $oE,
                        'error' => $d['info_msg']);
                    echo json_encode($response);
                    return;
                }

                $cart = new PhocacartCartRendercart();// is subclass of PhocacartCart, so we can use only subclass

                // Get Phoca Cart Cart Module Parameters
                $module                                = ModuleHelper::getModule('mod_phocacart_cart');
                $paramsM                               = new Registry($module->params);
                $cart->params['display_image']         = $paramsM->get('display_image', 0);
                $cart->params['display_checkout_link'] = $paramsM->get('display_checkout_link', 1);
                $cart->params['display_product_tax_info'] = $paramsM->get('display_product_tax_info', 0);

                $added = $cart->addItems((int)$item['id'], (int)$item['catid'], (int)$item['quantity'], $item['attribute']);

                if (!$added) {
                    $d             = array();
                    $d['s']        = $s;
                    $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();

                    $layoutPE = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
                    $oE       = $layoutPE->render($d);
                    $response = array(
                        'status' => '0',
                        'popup' => $oE,
                        'error' => $d['info_msg']);
                    echo json_encode($response);
                    return;
                }

                //$catid	= PhocacartProduct::getCategoryByProductId((int)$item['id']);
                $cart->setFullItems();

                $o = $o2 = '';
                // Content of the cart


                ob_start();
                echo $cart->render();
                $o = ob_get_contents();
                ob_end_clean();


                // Render the layout
                $d       = array();
                $d['s']  = $s;
                $layoutP = new FileLayout('popup_add_to_cart', null, array('component' => 'com_phocacart'));

                $d['link_checkout'] = Route::_(PhocacartRoute::getCheckoutRoute((int)$item['id'], (int)$item['catid']));
                $d['link_continue'] = '';
                // It can happen that add to cart button will be e.g. in module and when the module will be displayed on checkout site:
                // If yes and one item will be added per AJAX, we need to refresh checkout site
                // If now and one item will be added per AJAX, everything is OK, nothing needs to be refreshed
                $d['checkout_view'] = (int)$item['checkoutview'];

                if ($added) {
                    $d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_SHOPPING_CART');
                } else {
                    $d['info_msg'] = Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART');
                }

                // Popup with info - Continue,Proceed to Checkout
                //ob_start();

                //$o2 = ob_get_contents();
                //ob_end_clean();


                $count  = $cart->getCartCountItems();
                $total  = "";
                $totalA = $cart->getCartTotalItems();
                if (!empty($totalA)) {
                     $layoutT = new FileLayout('cart_total', null, array('component' => 'com_phocacart'));
                     $dT = array();
                     $dT['s']  = $s;
                     $dT['total'] = $totalA;

                     $total = $layoutT->render($dT);
                    //$total = $price->getPriceFormat($totalA[0]['brutto']);
                    //$total = $totalA[0]['brutto'];
                }

                // Get the complete calculation total
                /*   $shippingEdit	= 0;
                   $shippingEdit	= $app->input->get('shippingedit', 0, 'int');
                   $shippingId 	= $cart->getShippingId();
                   if (isset($shippingId) && (int)$shippingId > 0 && $shippingEdit == 0) {
                       $cart->addShippingCosts($shippingId);
                   }
                   // PAYMENT
                   $paymentEdit	= 0;
                   $paymentEdit	= $app->input->get('paymentedit', 0, 'int');
                   $paymentMethod 	= $cart->getPaymentMethod();
                   if (isset($paymentMethod['id']) && (int)$paymentMethod['id'] > 0 && $paymentEdit == 0) {
                       $cart->addPaymentCosts($paymentMethod['id']);
                   }*/
                $cart->roundTotalAmount();
                $d['total']    = $cart->getTotal();
                $d['products'] = $cart->getFullItems();
                $productKey    = PhocacartProduct::getProductKey((int)$item['id'], $item['attribute']);


                $d['product'] = array();
                if (isset($d['products'][0][$productKey])) {
                    $d['product'] = $d['products'][0][$productKey];
                }

                $d['product']['current_added']     = $added;
                $d['product']['current_id']        = (int)$item['id'];
                $d['product']['current_catid']     = (int)$item['catid'];
                $d['product']['current_quantity']  = (int)$item['quantity'];
                $d['product']['current_attribute'] = $item['attribute'];

                $o2 = $layoutP->render($d);

                $response = array(
                    'status' => '1',
                    'item' => $o,
                    'popup' => $o2,
                    'count' => $count,
                    'total' => $total);

                echo json_encode($response);
                return;

            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
                $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_FOUND'), 'error');
            }
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_SHOPPING_CART'), 'error');
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_SELECTED'), 'error');
        }

        $d             = array();
        $d['s']        = $s;
        $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();

        $layoutPE = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
        $oE       = $layoutPE->render($d);
        $response = array(
            'status' => '0',
            'popup' => $oE,
            'error' => $d['info_msg']);
        echo json_encode($response);
        return;
    }


    // Add item to cart
    function update($tpl = null) {

        if (!Session::checkToken('request')) {
            $response = array(
                'status' => '0',
                'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
            echo json_encode($response);
            return;
        }

        $msgSuffix            = '';
        $app                  = Factory::getApplication();
        $s                    = PhocacartRenderStyle::getStyles();
        $item                 = array();
        $item['id']           = $this->input->get('id', 0, 'int');
        $item['idkey']        = $this->input->get('idkey', '', 'string');
        $item['quantity']     = $this->input->get('quantity', 0, 'int');
        $item['catid']        = $this->input->get('catid', 0, 'int');
        $item['ticketid']     = $this->input->get('ticketid', 0, 'int');
        $item['quantity']     = $this->input->get('quantity', 0, 'int');
        $item['return']       = $this->input->get('return', '', 'string');
        $item['attribute']    = $this->input->get('attribute', array(), 'array');
        $item['checkoutview'] = $this->input->get('checkoutview', 0, 'int');
        $item['action']       = $this->input->get('action', '', 'string');


        $rights = new PhocacartAccessRights();
        $itemProduct       = PhocacartProduct::getProduct($item['id'], $item['catid']);
        $r = [];
        $r['can_display_addtocart'] = $rights->canDisplayAddtocartAdvanced($itemProduct);

        if (!$r['can_display_addtocart']) {

            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NOT_ALLOWED_TO_ADD_PRODUCTS_TO_SHOPPING_CART'), 'error');


            $d             = array();
            $d['s']        = $s;
            $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();
            $layoutPE      = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
            $oE            = $layoutPE->render($d);
            $response      = array(
                'status' => '0',
                'popup' => $oE,
                'error' => $d['info_msg']);
            echo json_encode($response);
            return;
        }

        if ((int)$item['idkey'] != '' && $item['action'] != '') {

            $cart = new PhocacartCartRendercheckout();

            // Get Phoca Cart Cart Module Parameters
            $module                                = ModuleHelper::getModule('mod_phocacart_cart');
            $paramsM                               = new Registry($module->params);
            $cart->params['display_image']         = $paramsM->get('display_image', 0);
            $cart->params['display_checkout_link'] = $paramsM->get('display_checkout_link', 1);
            $cart->params['display_product_tax_info'] = $paramsM->get('display_product_tax_info', 1);

            if ($item['action'] == 'delete') {
                $updated = $cart->updateItemsFromCheckout($item['idkey'], 0);

                if (!$updated) {

                    $d      = array();
                    $d['s'] = $s;
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
                    $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();;
                    $layoutPE = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
                    $oE       = $layoutPE->render($d);
                    $response = array(
                        'status' => '0',
                        'popup' => $oE,
                        'error' => $d['info_msg']);
                    echo json_encode($response);
                    return;
                }

                /*if ($updated) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'message');
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_NOT_REMOVED_FROM_SHOPPING_CART') . $msgSuffix, 'error');
                }*/
            } else {// update
                $updated = $cart->updateItemsFromCheckout($item['idkey'], (int)$item['quantity']);

                if (!$updated) {

                    $d      = array();
                    $d['s'] = $s;
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED') . $msgSuffix, 'error');
                    $d['info_msg'] = PhocacartRenderFront::renderMessageQueue();;
                    $layoutPE = new FileLayout('popup_error', null, array('component' => 'com_phocacart'));
                    $oE       = $layoutPE->render($d);
                    $response = array(
                        'status' => '0',
                        'popup' => $oE,
                        'error' => $d['info_msg']);
                    echo json_encode($response);
                    return;
                }
                /*if ($updated) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_QUANTITY_UPDATED') .$msgSuffix , 'message');
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_QUANTITY_NOT_UPDATED'). $msgSuffix, 'error');
                }*/
            }

            $cart->setFullItems();

            $o = $o2 = '';

            ob_start();
            echo $cart->render();
            $o = ob_get_contents();
            ob_end_clean();


            $count  = $cart->getCartCountItems();
            $total  = "";
            $totalA = $cart->getCartTotalItems();
            if (!empty($totalA)) {
                 $layoutT = new FileLayout('cart_total', null, array('component' => 'com_phocacart'));
                 $dT = array();
                 $dT['s']  = $s;
                 $dT['total'] = $totalA;

                 $total = $layoutT->render($dT);
                //$total = $price->getPriceFormat($totalA[0]['brutto']);
                //$total = $totalA[0]['brutto'];
            }

            $response = array(
                'status' => '1',
                'item' => $o,
                'popup' => $o2,
                'count' => $count,
                'total' => $total);

            echo json_encode($response);
            return;
        }

        $response = array(
            'status' => '0',
            'popup' => '',
            'error' => '');
        echo json_encode($response);
        return;

    }

}

?>
