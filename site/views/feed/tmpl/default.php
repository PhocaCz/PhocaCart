<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

$o		= array();
$l		= '<';
$r		= '>';
$e		= '</';


// HEADER
    if (isset($this->t['feed']['header']) && $this->t['feed']['header'] != '') {
    $o['header'] = $this->t['feed']['header'];
} else {
    $o['header'] = '<?xml version="1.0" encoding="utf-8"?>';
    }

// ROOT START
    if (isset($this->t['feed']['root']) && $this->t['feed']['root'] != '') {
    $o['rootstart'] = $l.$this->t['feed']['root'].$r;
}


// PREPARE FUNCTIONS BEFORE FOREACH, so we save memory
// E.g. currency - to get info about default currency we need to ask sql but we should to it only
// one time, not in foreach. Of course currency class is singleton so we don't run sql query many time
// but we don't need to run the function many times too.
$cur = '';
if (isset($this->t['feed']['currency_id']) && (int)$this->t['feed']['currency_id'] > 0) {
    $forceCurrencyObject = PhocacartCurrency::getCurrency((int)$this->t['feed']['currency_id']);
    $cur = $forceCurrencyObject->code;
} else {
    $cur	= PhocacartCurrency::getDefaultCurrencyCode();
}



// START FOREACH OF PRODUCTS
$o['items'] = '';
$o['params'] = '';
if (!empty($this->t['products'])) {


    foreach ($this->t['products'] as $k => $v) {


        // PRODUCT - Specific FEED plugin
        $feedName = trim($this->t['feed']['feed_plugin']);
         $paramsFeedA = array();
        if (isset($v->params_feed) && $v->params_feed != '') {

            $registry = new Registry;
            $registry->loadString($v->params_feed);
            $paramsFeedA = $registry->toArray();

            if (isset($paramsFeedA[$feedName]['pcf_param_published']) && $paramsFeedA[$feedName]['pcf_param_published'] == 0) {
                // The product is unpublished from feed
                continue;
            }
        }

        // Specific FEED plugin set in Category
        $paramsFeedCA = array();
        if (isset($v->params_feed_category) && $v->params_feed_category != '') {

            $registry = new Registry;
            $registry->loadString($v->params_feed_category);
            $paramsFeedCA = $registry->toArray();

        }


        $oI     = array();
        // PRODUCT START
        if (isset($this->t['feed']['item']) && $this->t['feed']['item'] != '') {
            $oI['itemstart'] = $l.$this->t['feed']['item'].$r;
        }

        if ($this->p['item_id'] != '' && isset($v->id) && $v->id != '') {
            $oI['item_id'] = $l.$this->p['item_id'].$r.$v->id.$e.$this->p['item_id'].$r;
        }

        if ($this->p['item_title']) {
            $title = '';
            if (isset($paramsFeedA[$feedName][$this->p['item_title']])) {
                $title = $paramsFeedA[$feedName][$this->p['item_title']];
                unset($paramsFeedA[$feedName][$this->p['item_title']]);
            }

            if ($title === '' && isset($v->title)) {
                $title = $v->title;
            }

            if ($title !== '') {
                $oI['item_title'] = $l . $this->p['item_title'] . $r . htmlspecialchars($title) . $e . $this->p['item_title'] . $r;
            }
        }

        if ($this->p['item_title_extended'] != '' && isset($v->title) && $v->title != '') {
            $oI['item_title_extended'] = $l.$this->p['item_title_extended'].$r.htmlspecialchars($v->title).$e.$this->p['item_title_extended'].$r;
        }

        if ($this->p['item_description_short'] != '' && isset($v->description) && $v->description != '') {

            if ($this->p['strip_html_tags_desc'] == 1) {
                $v->description = strip_tags($v->description);
            }
            $oI['item_description_short'] = $l.$this->p['item_description_short'].$r.'<![CDATA['.$v->description.']]>'.$e.$this->p['item_description_short'].$r;
        }

        if ($this->p['item_description_long'] != '' && isset($v->description_long) && $v->description_long != '') {

            if ($this->p['strip_html_tags_desc'] == 1) {
                $v->description_long = strip_tags($v->description_long);
            }

            $oI['item_description_long'] = $l.$this->p['item_description_long'].$r.'<![CDATA['.$v->description_long.']]>'.$e.$this->p['item_description_long'].$r;
        }

        if ($this->p['item_sku'] != '' && isset($v->sku) && $v->sku != '') {
            $oI['item_sku'] = $l.$this->p['item_sku'].$r.htmlspecialchars($v->sku).$e.$this->p['item_sku'].$r;
        }

        if ($this->p['item_ean'] != '' && isset($v->ean) && $v->ean != '') {
            $oI['item_ean'] = $l.$this->p['item_ean'].$r.$v->ean.$e.$this->p['item_ean'].$r;
        }


        // PRICE ORIGINAL
        if ($this->p['item_original_price_with_vat'] != '' || $this->p['item_original_price_without_vat'] != ''
            && isset($v->price_original) && isset($v->taxrate) && isset($v->taxcalculationtype)) {

            $priceOc 	= new PhocacartPrice;
            if (isset($this->t['feed']['currency_id']) && (int)$this->t['feed']['currency_id'] > 0) {
                $priceOc->setCurrency((int)$this->t['feed']['currency_id']);
            }
            $priceO		= $priceOc->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype, '', 0, '', 0, 1, null, $v->taxhide);


            if (isset($forceCurrencyObject->exchange_rate) && $forceCurrencyObject->exchange_rate > 0) {
                $priceO['netto'] = $priceO['netto'] * $forceCurrencyObject->exchange_rate;
                $priceO['brutto'] = $priceO['brutto'] * $forceCurrencyObject->exchange_rate;
                $priceO['tax'] = $priceO['tax'] * $forceCurrencyObject->exchange_rate;
            }

            if ($this->p['price_decimals'] != '') {
                $priceO['netto'] = number_format($priceO['netto'], (int)$this->p['price_decimals']);
                $priceO['brutto'] = number_format($priceO['brutto'], (int)$this->p['price_decimals']);
            }

            if ($this->p['price_including_currency'] == 1){
                $priceO['netto'] = $cur != '' ? $priceO['netto'] . ' ' . $cur : $priceO['netto'];
                $priceO['brutto'] = $cur != '' ? $priceO['brutto'] . ' ' . $cur : $priceO['brutto'];
            }

            if ($this->p['item_original_price_without_vat'] != '' && isset($priceO['netto']) && (int)$priceO['netto'] > 0) {
                $oI['item_original_price_without_vat'] = $l.$this->p['item_original_price_without_vat'].$r.$priceO['netto'].$e.$this->p['item_original_price_without_vat'].$r;
            }
            if ($this->p['item_original_price_with_vat'] != '' && isset($priceO['brutto']) && (int)$priceO['brutto'] > 0) {
                $oI['item_original_price_with_vat'] = $l.$this->p['item_original_price_with_vat'].$r.$priceO['brutto'].$e.$this->p['item_original_price_with_vat'].$r;
            }
        }

        // PRICE FINAL
        if ($this->p['item_final_price_with_vat'] != '' || $this->p['item_final_price_without_vat'] != ''
            && isset($v->price) && isset($v->taxrate) && isset($v->taxcalculationtype)) {

            $priceFc = new PhocacartPrice;
            if (isset($this->t['feed']['currency_id']) && (int)$this->t['feed']['currency_id'] > 0) {
                $priceFc->setCurrency((int)$this->t['feed']['currency_id']);
            }
            $priceF = $priceFc->getPriceItems($v->price, $v->taxid, $v->taxrate, $v->taxcalculationtype, '', 0, '', 0, 1, null, $v->taxhide);

            if (isset($forceCurrencyObject->exchange_rate) && $forceCurrencyObject->exchange_rate > 0) {
                $priceF['netto'] = $priceF['netto'] * $forceCurrencyObject->exchange_rate;
                $priceF['brutto'] = $priceF['brutto'] * $forceCurrencyObject->exchange_rate;
                $priceF['tax'] = $priceF['tax'] * $forceCurrencyObject->exchange_rate;
            }


            if ($this->p['price_decimals'] != '') {
                $priceF['netto'] = number_format($priceF['netto'], (int)$this->p['price_decimals']);
                $priceF['brutto'] = number_format($priceF['brutto'], (int)$this->p['price_decimals']);
                $priceF['tax'] = number_format($priceF['tax'], (int)$this->p['price_decimals']);
            }

            if ($this->p['price_including_currency'] == 1){

                $priceF['netto'] = $cur != '' ? $priceF['netto'] . ' ' . $cur : $priceF['netto'];
                $priceF['brutto'] = $cur != '' ? $priceF['brutto'] . ' ' . $cur : $priceF['brutto'];
                $priceF['tax'] = $cur != '' ? $priceF['tax'] . ' ' . $cur : $priceF['tax'];
            }

            if ($this->p['item_final_price_without_vat'] != '' && isset($priceF['netto']) && (int)$priceF['netto'] > 0) {
                $oI['item_final_price_without_vat'] = $l.$this->p['item_final_price_without_vat'].$r.$priceF['netto'].$e.$this->p['item_final_price_without_vat'].$r;
            }

            if ($this->p['item_final_price_with_vat'] != '' && isset($priceF['brutto']) && (int)$priceF['brutto'] > 0) {
                $oI['item_final_price_with_vat'] = $l.$this->p['item_final_price_with_vat'].$r.$priceF['brutto'].$e.$this->p['item_final_price_with_vat'].$r;
            }

            if ($this->p['item_vat'] != '' && isset($priceF['tax']) && (int)$priceF['tax'] > 0) {
                $oI['item_vat'] = $l.$this->p['item_vat'].$r.$priceF['tax'].$e.$this->p['item_vat'].$r;
            }
        }

        // PRODUCT CURRENCY (DEFAULT)
        if ($this->p['item_currency'] != '' && $cur != '') {
            $oI['item_currency'] = $l.$this->p['item_currency'].$r.htmlspecialchars($cur).$e.$this->p['item_currency'].$r;
        }


        // PRODUCT URL
        if ($this->p['item_url'] != '' && isset($v->id) && $v->id > 0 && isset($v->catid) && $v->catid > 0 && isset($v->alias) && isset($v->catalias)) {

            $itemUrl 	= PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias);
            $itemUrl	= PhocacartRoute::getFullUrl($itemUrl);
            $oI['item_url'] = $l.$this->p['item_url'].$r.$itemUrl.$e.$this->p['item_url'].$r;
        }

        // IMAGE URL
        if ($this->p['item_url_image'] != '' && isset($v->image) && $v->image != '') {
            $image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'large');
            if (isset($image->rel) && $image->rel != '') {
                $imageUrl	= PhocacartRoute::getFullUrl($image->rel);
                $oI['item_url_image'] = $l.$this->p['item_url_image'].$r.$imageUrl.$e.$this->p['item_url_image'].$r;
            }
        }

        // VIDEO URL
        if ($this->p['item_url_video'] != '' && isset($v->video) && $v->video != '') {
            if (PhocacartUtils::isURLAddress($v->video)) {
                $oI['item_url_video'] = $l.$this->p['item_url_video'].$r.$v->video.$e.$this->p['item_url_video'].$r;
            }
        }

        // CATEGORY
        if ($this->p['item_category'] != '' && isset($v->cattitle) && $v->cattitle != '') {
            $oI['item_category'] = $l.$this->p['item_category'].$r.htmlspecialchars($v->cattitle).$e.$this->p['item_category'].$r;
        }

        // CATEGORIES
        if ($this->p['item_categories'] != '' && isset($v->categories) && $v->categories != '') {

            if ($this->p['category_separator'] == '') {
                $this->p['category_separator'] = ' ';
            }
            $categories = str_replace('|', $this->p['category_separator'], $v->categories);
            $oI['item_categories'] = $l.$this->p['item_categories'].$r.htmlspecialchars($categories).$e.$this->p['item_categories'].$r;
        }

        // CATEGORY FEED
        if ($this->p['feed_category'] != '' && isset($v->cattitlefeed) && $v->cattitlefeed != '') {
            $oI['feed_category'] = $l.$this->p['feed_category'].$r.htmlspecialchars($v->cattitlefeed).$e.$this->p['feed_category'].$r;
        }

        // CATEGORY TYPE OR PRODUCT CATEGORY TYPE
        if ($this->p['item_category_type_feed'] != '') {

            if (isset($v->type_category_feed) && $v->type_category_feed != '') {

                // 1) Product - the one you can set in product edit in first tab
                $oI['item_category_type_feed'] = $l.$this->p['item_category_type_feed'].$r.htmlspecialchars($v->type_category_feed).$e.$this->p['item_category_type_feed'].$r;

            } else if (isset($v->feedcategories ) && $v->feedcategories  != '') {

                // 2) Categories - loaded by db - generated by categories set in Phoca Cart
                $v->feedcategories = str_replace('|', $this->p['category_separator'], htmlspecialchars($v->feedcategories));
                if ($v->feedcategories != '') {
                    $oI['item_category_type_feed'] = $l . $this->p['item_category_type_feed'] . $r . htmlspecialchars($v->feedcategories) . $e . $this->p['item_category_type_feed'] . $r;
                }
                // Only one category possible e.g. in Google Products, so this can be customized
                //$this->t['feed']categories = explode('|', $v->feedcategories);
                //if (isset($this->t['feed']categories[0]) && $this->t['feed']categories[0] != '') {
                //	$oI['item_category_type_feed'] = $l.$this->p['item_category_type_feed'].$r.htmlspecialchars($this->t['feed']categories[0]).$e.$this->p['item_category_type_feed'].$r;
                //}

            } else if (isset($v->cattypefeed) && $v->cattypefeed != '') {
                // 3) Category - if not 2) loaded - the one you can set in category
                $oI['item_category_type_feed'] = $l.$this->p['item_category_type_feed'].$r.htmlspecialchars($v->cattypefeed).$e.$this->p['item_category_type_feed'].$r;
            }
        }

        // MANUFACTURER
        if ($this->p['item_manufacturer'] != '' && isset($v->manufacturertitle) && $v->manufacturertitle != '') {
            $oI['item_manufacturer'] = $l.$this->p['item_manufacturer'].$r.htmlspecialchars($v->manufacturertitle).$e.$this->p['item_manufacturer'].$r;
        }

        // STOCK (Product edit - Stock Options - In Stock)
        if ($this->p['item_stock'] != '' && isset($v->stock) && $v->stock != '') {
            $oI['item_stock'] = $l.$this->p['item_stock'].$r.$v->stock.$e.$this->p['item_stock'].$r;
        }

        // STOCK DELIVERY_DATE (Product edit - Stock Options - Stock Status)
        if ($this->p['item_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) && isset($v->max_quantity) ) {


            $stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id, (int)$v->max_quantity);

            //$stockText		= PhocacartStock::getStockStatusOutput($stockStatus);
            if (isset($stockStatus['stock_status']) && $stockStatus['stock_status'] != '') {
                $oI['item_delivery_date'] = $l.$this->p['item_delivery_date'].$r.htmlspecialchars($stockStatus['stock_status']).$e.$this->p['item_delivery_date'].$r;
            }
        }

        // STOCK DELIVERY_DATE - REAL DATE (Product edit - Stock Options - Product Delivery Date)
        if ($this->p['item_delivery_date_date'] != '' && isset($v->delivery_date) && $v->delivery_date != '' && $v->delivery_date != '0000-00-00 00:00:00') {
            $oI['item_delivery_date_date'] = $l.$this->p['item_delivery_date_date'].$r.$v->delivery_date.$e.$this->p['item_delivery_date_date'].$r;
        }

        // STOCK DELIVERY_DATE FEED (Stock Status Edit - Title (XML Feed))
        if ($this->p['feed_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) && isset($v->max_quantity) ) {
            $stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id, (int)$v->max_quantity);


            if (isset($stockStatus['stock_status_feed']) && $stockStatus['stock_status_feed'] != '') {
                $oI['stock_status_feed'] = $l.$this->p['feed_delivery_date'].$r.htmlspecialchars($stockStatus['stock_status_feed']).$e.$this->p['feed_delivery_date'].$r;
            }
        }

        //
        // NEEDS TO BE CUSTOMIZED FOR EACH XML FEED
        //
        if ($this->p['display_attributes'] == 1 && $this->p['item_attribute'] != '' && $this->p['item_attribute_name'] != '' && $this->p['item_attribute_value'] != '') {
            // ATTRIBUTES - BE AWARE TO USER ATTRIBUTES
            // RENDERING can take a lot of memory
            // THE FORMAT can be not correct
            $attributes = PhocacartAttribute::getAttributesAndOptions((int)$v->id);
            $oIA = array();
            if (!empty($attributes)) {
                foreach ($attributes as $k2 => $v2) {
                    if (isset($v2->title) && $v2->title != '') {

                        $oIA[] = $l.$this->p['item_attribute'].$r;
                        $oIA[] = $l.$this->p['item_attribute_name'].$r.htmlspecialchars($v2->title).$e.$this->p['item_attribute_name'].$r;

                        if (!empty($v2->options)) {
                            $opt = array();
                            foreach ($v2->options as $k3 => $v3) {
                                $opt[] = $v3->title;
                            }
                            $optText = implode(';', $opt);
                            $oIA[] = $l.$this->p['item_attribute_value'].$r.htmlspecialchars($optText).$e.$this->p['item_attribute_value'].$r;
                        }
                        $oIA[] = $e.$this->p['item_attribute'].$r;
                    }

                }
            }
            $oI['attributes'] = implode("\n", $oIA);
        }


        // SPECIFICATION
        if (!empty($this->p['specification_groups_id']) && $this->p['item_specification'] != '' /*&& $this->p['item_specification_group_name']*/ && $this->p['item_specification_name'] != '' && $this->p['item_specification_value'] != '') {

            $specifications = PhocacartSpecification::getSpecificationGroupsAndSpecifications((int)$v->id);


            $oIS = array();
            if (!empty($specifications)) {

                foreach ($specifications as $k2 => $v2) {

                    if (!empty($v2)) {
                        $specGroup = array_slice($v2, 0, 1);
                        $specItems = array_slice($v2, 1);

                        if (!in_array((int)$k2, $this->p['specification_groups_id'])) {
                            // The specification is not selected
                            continue;
                        }

                        /*
                         * Possible feature move the parameter from feed to product/feed

                        if (isset($paramsFeedA[$feedName]['pcf_param_specification_group_id']) && !empty($paramsFeedA[$feedName]['pcf_param_specification_group_id'])) {

                            if (!in_array((int)$k2, $paramsFeedA[$feedName]['pcf_param_specification_group_id'])) {
                                // The specification is not selected
                                continue;
                            }
                        }
                        */

                        if (!empty($specGroup) && !empty($specItems)) {

                            //$oIS[] = $l . $this->p['item_specification'] . $r;
                            //$oIS[] = $l . $this->p['item_specification_group_name'] . $r . htmlspecialchars($v2[0]) . $e . $this->p['item_specification_group_name'] . $r;

                            foreach ($specItems as $k3 => $v3) {

                                $oIS[] = $l . $this->p['item_specification'] . $r;

                                if (isset($v3['title']) && $v3['title'] != '') {
                                    $oIS[] = $l . $this->p['item_specification_name'] . $r . htmlspecialchars($v3['title']) . $e . $this->p['item_specification_name'] . $r;
                                }
                                if (isset($v3['value']) && $v3['value'] != '') {
                                    $oIS[] = $l . $this->p['item_specification_value'] . $r . htmlspecialchars($v3['value']) . $e . $this->p['item_specification_value'] . $r;
                                }

                                $oIS[] = $e . $this->p['item_specification'] . $r;
                            }

                            //$oIS[] = $e . $this->p['item_specification'] . $r;
                        }

                    }
                }
            }
            $oI['specifications'] = implode("\n", $oIS);
        }



        // PRODUCT CONDITION
        if ($this->p['item_condition'] != '' && isset($v->condition)) {
            $condition = PhocacartUtilsSettings::getProductConditionValues($v->condition);
            $oI['item_condition'] = $l.$this->p['item_condition'].$r.htmlspecialchars($condition).$e.$this->p['item_condition'].$r;

        }

        // PRODUCT REWARD POINTS
        if ($this->p['item_reward_points'] != '' && isset($v->points_received) && (int)$v->points_received > 0) {

            $oIRP = array();
            if ($this->p['item_reward_points_name'] != '' && $this->p['item_reward_points_value'] != '') {
                $oIRP[] = $l.$this->p['item_reward_points'].$r;

                $oIRP[] = $l.$this->p['item_reward_points_name'].$r.Text::_('COM_PHOCACART_FEED_TXT_PRODUCT_REWARD_POINTS').$e.$this->p['item_reward_points_name'].$r;
                $oIRP[] = $l.$this->p['item_reward_points_value'].$r.(int)$v->points_received.$e.$this->p['item_reward_points_value'].$r;
                // Possible RATION value

                $oIRP[] = $e.$this->p['item_reward_points'].$r;
            } else {
                $oIRP[] = $l.$this->p['item_reward_points'].$r.(int)$v->points_received.$e.$this->p['item_reward_points'].$r;
            }
            $oI['reward_points'] = implode("\n", $oIRP);
        }

        // PRODUCT TYPE FEED
        if ($this->p['item_type_feed'] != '' && isset($v->type_feed) && $v->type_feed != '') {
            $oI['item_type_feed'] = $l.$this->p['item_type_feed'].$r.htmlspecialchars($v->type_feed).$e.$this->p['item_type_feed'].$r;
        }


        // CATEGORY AND PRODUCT SPECIFIC FEED PLUGIN
        // First try to set parameters by category feed options
        // Second try to check if the parameter by product has value and if yes, set the product feed options value
        // PRODUCT - Specific FEED plugin
        $oIP = array();

        if (!empty($paramsFeedCA)) {
            foreach ($paramsFeedCA as $k => $v) {
                if (trim($k) == trim($this->t['feed']['feed_plugin'])) {

                    if (!empty($v)) {
                        foreach ($v as $k2 => $v2) {

                            // display items except the parameter items
                            $pos = strpos($k2, 'pcf_param');
                            if ($pos !== false) {
                                continue;
                            }

                            if (trim($v2) != '') {
                                // Some feeds have the same parameters but we cannot store them under the same name
                                // so internaly they are stored as e.g.: EXTRA_MESSAGE{1}, EXTRA_MESSAGE{2}
                                // in XML the {1} and {2} are removed and there is only one parameter EXTRA_MESSAGE on different places
                                $k2       = preg_replace("/\{[^}]+\}/", "", $k2);
                                $oIP[$k2] = $l . $k2 . $r . htmlspecialchars($v2) . $e . $k2 . $r;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($paramsFeedA)) {
            foreach ($paramsFeedA as $k => $v) {
                if (trim($k) == trim($this->t['feed']['feed_plugin'])) {

                    if (!empty($v)) {
                        foreach ($v as $k2 => $v2) {

                            // display items except the parameter items
                            $pos = strpos($k2, 'pcf_param');
                            if ($pos !== false) {
                                continue;
                            }

                            if (trim($v2) != '') {
                                // Some feeds have the same parameters but we cannot store them under the same name
                                // so internaly they are stored as e.g.: EXTRA_MESSAGE{1}, EXTRA_MESSAGE{2}
                                // in XML the {1} and {2} are removed and there is only one parameter EXTRA_MESSAGE on different places
                                $k2       = preg_replace("/\{[^}]+\}/", "", $k2);
                                $oIP[$k2] = $l . $k2 . $r . htmlspecialchars($v2) . $e . $k2 . $r;
                            }
                        }
                    }
                }
            }
        }



        $oI['params'] = implode("\n", $oIP);





        // PRODUCT - Fixed XML Elements
        if ($this->p['item_fixed_elements'] != '') {
            $oI['item_fixed_elements'] = $this->p['item_fixed_elements'];
        }



        // PRODUCT END
        if (isset($this->t['feed']['item']) && $this->t['feed']['item'] != '') {
            $oI['itemend'] = $e.$this->t['feed']['item'].$r;
        }


        $o['items'] .= implode("\n", $oI) . "\n";

    }

}


// ROOT END
    if (isset($this->t['feed']['root']) && $this->t['feed']['root'] != '') {
    $o['rootend'] = $e.$this->t['feed']['root'].$r;
    }


// FOOTER
    if (isset($this->t['feed']['footer']) && $this->t['feed']['footer'] != '') {
    $o['footer'] = $this->t['feed']['footer'];
}



echo implode( "\n", $o );



?>
