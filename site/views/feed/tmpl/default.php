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

if (!$this->pageIndex) {
// HEADER
    if (isset($this->t['feed']['header']) && $this->t['feed']['header'] != '') {
        $this->output($this->t['feed']['header']);
    }
    else {
        $this->output('<?xml version="1.0" encoding="utf-8"?>');
    }
// ROOT START
    if (isset($this->t['feed']['root']) && $this->t['feed']['root'] != '') {
        $this->output('<' . $this->t['feed']['root'] . '>');
    }
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
if (!empty($this->products)) {
    foreach ($this->products as $k => $v) {
        // PRODUCT - Specific FEED plugin
        $feedName = trim($this->t['feed']['feed_plugin']);
         $paramsFeedA = array();
        if (isset($v->params_feed) && $v->params_feed != '') {

            $registry = new Registry;
            $registry->loadString($v->params_feed);
            $paramsFeedA = $registry->toArray();
            unset($registry);

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
            unset($registry);
        }


        // PRODUCT START
        if (isset($this->t['feed']['item']) && $this->t['feed']['item'] != '') {
            $this->output('<'.$this->t['feed']['item'].'>');
        }

        if ($this->p['item_id'] != '' && isset($v->id) && $v->id != '') {
            $this->output('<'.$this->p['item_id'].'>'.$v->id.'</'.$this->p['item_id'].'>');
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
                $this->output('<' . $this->p['item_title'] . '>' . htmlspecialchars($title) . '</' . $this->p['item_title'] . '>');
            }
        }

        if ($this->p['item_title_extended'] != '' && isset($v->title) && $v->title != '') {
            $this->output('<'.$this->p['item_title_extended'].'>'.htmlspecialchars($v->title).'</'.$this->p['item_title_extended'].'>');
        }

        if ($this->p['item_description_short'] != '' && isset($v->description) && $v->description != '') {

            if ($this->p['strip_html_tags_desc'] == 1) {
                $v->description = strip_tags($v->description);
            }
            $this->output('<'.$this->p['item_description_short'].'>'.'<![CDATA['.$v->description.']]>'.'</'.$this->p['item_description_short'].'>');
        }

        if ($this->p['item_description_long'] != '' && isset($v->description_long) && $v->description_long != '') {

            if ($this->p['strip_html_tags_desc'] == 1) {
                $v->description_long = strip_tags($v->description_long);
            }

            $this->output('<'.$this->p['item_description_long'].'>'.'<![CDATA['.$v->description_long.']]>'.'</'.$this->p['item_description_long'].'>');
        }

        if ($this->p['item_sku'] != '' && isset($v->sku) && $v->sku != '') {
            $this->output('<'.$this->p['item_sku'].'>'.$v->sku.'</'.$this->p['item_sku'].'>');
        }

        if ($this->p['item_ean'] != '' && isset($v->ean) && $v->ean != '') {
            $this->output('<'.$this->p['item_ean'].'>'.$v->ean.'</'.$this->p['item_ean'].'>');
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
                $this->output('<'.$this->p['item_original_price_without_vat'].'>'.$priceO['netto'].'</'.$this->p['item_original_price_without_vat'].'>');
            }
            if ($this->p['item_original_price_with_vat'] != '' && isset($priceO['brutto']) && (int)$priceO['brutto'] > 0) {
                $this->output('<'.$this->p['item_original_price_with_vat'].'>'.$priceO['brutto'].'</'.$this->p['item_original_price_with_vat'].'>');
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
                $this->output('<'.$this->p['item_final_price_without_vat'].'>'.$priceF['netto'].'</'.$this->p['item_final_price_without_vat'].'>');
            }

            if ($this->p['item_final_price_with_vat'] != '' && isset($priceF['brutto']) && (int)$priceF['brutto'] > 0) {
                $this->output('<'.$this->p['item_final_price_with_vat'].'>'.$priceF['brutto'].'</'.$this->p['item_final_price_with_vat'].'>');
            }

            if ($this->p['item_vat'] != '' && isset($priceF['tax']) && (int)$priceF['tax'] > 0) {
                $this->output('<'.$this->p['item_vat'].'>'.$priceF['tax'].'</'.$this->p['item_vat'].'>');
            }
        }

        // PRODUCT CURRENCY (DEFAULT)
        if ($this->p['item_currency'] != '' && $cur != '') {
            $this->output('<'.$this->p['item_currency'].'>'.htmlspecialchars($cur).'</'.$this->p['item_currency'].'>');
        }


        // PRODUCT URL
        if ($this->p['item_url'] != '' && isset($v->id) && $v->id > 0 && isset($v->catid) && $v->catid > 0 && isset($v->alias) && isset($v->catalias)) {

            $itemUrl 	= PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias);
            $itemUrl	= PhocacartRoute::getFullUrl($itemUrl);
            $this->output('<'.$this->p['item_url'].'>'.$itemUrl.'</'.$this->p['item_url'].'>');
        }

        // IMAGE URL
        if ($this->p['item_url_image'] != '' && isset($v->image) && $v->image != '') {
            $image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'large');
            if (isset($image->rel) && $image->rel != '') {
                $imageUrl	= PhocacartRoute::getFullUrl($image->rel);
                $this->output('<'.$this->p['item_url_image'].'>'.$imageUrl.'</'.$this->p['item_url_image'].'>');
            }
        }

        // VIDEO URL
        if ($this->p['item_url_video'] != '' && isset($v->video) && $v->video != '') {
            if (PhocacartUtils::isURLAddress($v->video)) {
                $this->output('<'.$this->p['item_url_video'].'>'.$v->video.'</'.$this->p['item_url_video'].'>');
            }
        }

        // CATEGORY
        if ($this->p['item_category'] != '' && isset($v->cattitle) && $v->cattitle != '') {
            $this->output('<'.$this->p['item_category'].'>'.htmlspecialchars($v->cattitle).'</'.$this->p['item_category'].'>');
        }

        // CATEGORIES
        if ($this->p['item_categories'] != '' && isset($v->categories) && $v->categories != '') {

            if ($this->p['category_separator'] == '') {
                $this->p['category_separator'] = ' ';
            }
            $categories = str_replace('|', $this->p['category_separator'], $v->categories);
            $this->output('<'.$this->p['item_categories'].'>'.htmlspecialchars($categories).'</'.$this->p['item_categories'].'>');
        }

        // CATEGORY FEED
        if ($this->p['feed_category'] != '' && isset($v->cattitlefeed) && $v->cattitlefeed != '') {
            $this->output('<'.$this->p['feed_category'].'>'.htmlspecialchars($v->cattitlefeed).'</'.$this->p['feed_category'].'>');
        }

        // CATEGORY TYPE OR PRODUCT CATEGORY TYPE
        if ($this->p['item_category_type_feed'] != '') {

            if (isset($v->type_category_feed) && $v->type_category_feed != '') {

                // 1) Product - the one you can set in product edit in first tab
                $this->output('<'.$this->p['item_category_type_feed'].'>'.htmlspecialchars($v->type_category_feed).'</'.$this->p['item_category_type_feed'].'>');

            } else if (isset($v->feedcategories ) && $v->feedcategories  != '') {

                // 2) Categories - loaded by db - generated by categories set in Phoca Cart
                $v->feedcategories = str_replace('|', $this->p['category_separator'], htmlspecialchars($v->feedcategories));
                if ($v->feedcategories != '') {
                    $this->output('<' . $this->p['item_category_type_feed'] . '>' . htmlspecialchars($v->feedcategories) . '</' . $this->p['item_category_type_feed'] . '>');
                }
                // Only one category possible e.g. in Google Products, so this can be customized
                //$this->t['feed']categories = explode('|', $v->feedcategories);
                //if (isset($this->t['feed']categories[0]) && $this->t['feed']categories[0] != '') {
                //	$this->output('<'.$this->p['item_category_type_feed'].'>'.htmlspecialchars($this->t['feed']categories[0]).'</'.$this->p['item_category_type_feed'].'>');
                //}

            } else if (isset($v->cattypefeed) && $v->cattypefeed != '') {
                // 3) Category - if not 2) loaded - the one you can set in category
                $this->output('<'.$this->p['item_category_type_feed'].'>'.htmlspecialchars($v->cattypefeed).'</'.$this->p['item_category_type_feed'].'>');
            }
        }

        // MANUFACTURER
        if ($this->p['item_manufacturer'] != '' && isset($v->manufacturertitle) && $v->manufacturertitle != '') {
            $this->output('<'.$this->p['item_manufacturer'].'>'.htmlspecialchars($v->manufacturertitle).'</'.$this->p['item_manufacturer'].'>');
        }

        // STOCK (Product edit - Stock Options - In Stock)
        if ($this->p['item_stock'] != '' && isset($v->stock) && $v->stock != '') {
            $this->output('<'.$this->p['item_stock'].'>'.$v->stock.'</'.$this->p['item_stock'].'>');
        }

        // STOCK DELIVERY_DATE (Product edit - Stock Options - Stock Status)
        if ($this->p['item_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) ) {


            $stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id);

            //$stockText		= PhocacartStock::getStockStatusOutput($stockStatus);
            if (isset($stockStatus['stock_status']) && $stockStatus['stock_status'] != '') {
                $this->output('<'.$this->p['item_delivery_date'].'>'.htmlspecialchars($stockStatus['stock_status']).'</'.$this->p['item_delivery_date'].'>');
            }
        }

        // STOCK DELIVERY_DATE - REAL DATE (Product edit - Stock Options - Product Delivery Date)
        if ($this->p['item_delivery_date_date'] != '' && isset($v->delivery_date) && $v->delivery_date != '' && $v->delivery_date != '0000-00-00 00:00:00') {
            $this->output('<'.$this->p['item_delivery_date_date'].'>'.$v->delivery_date.'</'.$this->p['item_delivery_date_date'].'>');
        }

        // STOCK DELIVERY_DATE FEED (Stock Status Edit - Title (XML Feed))
        if ($this->p['feed_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) ) {
            $stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id);


            if (isset($stockStatus['stock_status_feed']) && $stockStatus['stock_status_feed'] != '') {
                $this->output('<'.$this->p['feed_delivery_date'].'>'.htmlspecialchars($stockStatus['stock_status_feed']).'</'.$this->p['feed_delivery_date'].'>');
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
            if (!empty($attributes)) {
                foreach ($attributes as $k2 => $v2) {
                    if (isset($v2->title) && $v2->title != '') {

                        $this->output('<'.$this->p['item_attribute'].'>');
                        $this->output('<'.$this->p['item_attribute_name'].'>'.htmlspecialchars($v2->title).'</'.$this->p['item_attribute_name'].'>');

                        if (!empty($v2->options)) {
                            $opt = array();
                            foreach ($v2->options as $k3 => $v3) {
                                $opt[] = $v3->title;
                            }
                            $optText = implode(';', $opt);
                            $this->output('<'.$this->p['item_attribute_value'].'>'.htmlspecialchars($optText).'</'.$this->p['item_attribute_value'].'>');
                        }
                        $this->output('</'.$this->p['item_attribute'].'>');
                    }

                }
            }
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

                            foreach ($specItems as $k3 => $v3) {

                                $this->output('<' . $this->p['item_specification'] . '>');

                                if (isset($v3['title']) && $v3['title'] != '') {
                                    $this->output('<' . $this->p['item_specification_name'] . '>' . htmlspecialchars($v3['title']) . '</' . $this->p['item_specification_name'] . '>');
                                }
                                if (isset($v3['value']) && $v3['value'] != '') {
                                    $this->output('<' . $this->p['item_specification_value'] . '>' . htmlspecialchars($v3['value']) . '</' . $this->p['item_specification_value'] . '>');
                                }

                                $this->output('</' . $this->p['item_specification'] . '>');
                            }
                        }

                    }
                }
            }
        }



        // PRODUCT CONDITION
        if ($this->p['item_condition'] != '' && isset($v->condition)) {
            $condition = PhocacartUtilsSettings::getProductConditionValues($v->condition);
            $this->output('<'.$this->p['item_condition'].'>'.htmlspecialchars($condition).'</'.$this->p['item_condition'].'>');

        }

        // PRODUCT REWARD POINTS
        if ($this->p['item_reward_points'] != '' && isset($v->points_received) && (int)$v->points_received > 0) {
            if ($this->p['item_reward_points_name'] != '' && $this->p['item_reward_points_value'] != '') {
                $this->output('<'.$this->p['item_reward_points'].'>');

                $this->output('<'.$this->p['item_reward_points_name'].'>'.Text::_('COM_PHOCACART_FEED_TXT_PRODUCT_REWARD_POINTS').'</'.$this->p['item_reward_points_name'].'>');
                $this->output('<'.$this->p['item_reward_points_value'].'>'.(int)$v->points_received.'</'.$this->p['item_reward_points_value'].'>');
                // Possible RATION value

                $this->output('</'.$this->p['item_reward_points'].'>');
            } else {
                $this->output('<'.$this->p['item_reward_points'].'>'.(int)$v->points_received.'</'.$this->p['item_reward_points'].'>');
            }
        }

        // PRODUCT TYPE FEED
        if ($this->p['item_type_feed'] != '' && isset($v->type_feed) && $v->type_feed != '') {
            $this->output('<'.$this->p['item_type_feed'].'>'.htmlspecialchars($v->type_feed).'</'.$this->p['item_type_feed'].'>');
        }


        // CATEGORY AND PRODUCT SPECIFIC FEED PLUGIN
        // First try to set parameters by category feed options
        // Second try to check if the parameter by product has value and if yes, set the product feed options value
        // PRODUCT - Specific FEED plugin
        if (!empty($paramsFeedCA)) {
            foreach ($paramsFeedCA as $kCA => $v) {
                if (trim($kCA) == trim($this->t['feed']['feed_plugin'])) {

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
                                if ($k2 == 'HEUREKA_CPC') {
                                    $v2 = str_replace(',', '.', $v2);
                                }
                                $this->output('<' . $k2 . '>' . htmlspecialchars($v2) . '</' . $k2 . '>');
                            }
                        }
                    }
                }
            }
        }

        if (!empty($paramsFeedA)) {
            foreach ($paramsFeedA as $kA => $v) {
                if (trim($kA) == trim($this->t['feed']['feed_plugin'])) {

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
                                if ($k2 == 'HEUREKA_CPC') {
                                    $v2 = str_replace(',', '.', $v2);
                                }
                                $this->output('<' . $k2 . '>' . htmlspecialchars($v2) . '</' . $k2 . '>');
                            }
                        }
                    }
                }
            }
        }

        // PRODUCT - Fixed XML Elements
        if ($this->p['item_fixed_elements'] != '') {
            $this->output($this->p['item_fixed_elements']);
        }



        // PRODUCT END
        if (isset($this->t['feed']['item']) && $this->t['feed']['item'] != '') {
            $this->output('</'.$this->t['feed']['item'].'>');
        }

        unset($v);
        unset($paramsFeedCA);
        unset($paramsFeedA);
    }

}

if ($this->justClose) {
    $this->output('<!-- Memory: ' . memory_get_usage(true) . ', Peak Memory ' . memory_get_peak_usage(true) . ' -->');

// ROOT END
    if (isset($this->t['feed']['root']) && $this->t['feed']['root'] != '') {
        $this->output('</' . $this->t['feed']['root'] . '>');
    }


// FOOTER
    if (isset($this->t['feed']['footer']) && $this->t['feed']['footer'] != '') {
        $this->output($this->t['feed']['footer']);
    }
}
