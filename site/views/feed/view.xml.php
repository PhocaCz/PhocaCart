<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

class PhocaCartViewFeed extends JViewLegacy
{

	protected $t;

	function display($tpl = null)
	{

		$app	= JFactory::getApplication();
		$id		= $app->input->get( 'id', 0, 'int' );
		$feed	= PhocacartFeed::getFeed((int)$id);

		$o		= array();
		$l		= '<';
		$r		= '>';
		$e		= '</';

		if ($feed) {
			$fP = new JRegistry;
			$iP	= new JRegistry;


			if (isset($feed['feed_params']) && $feed['feed_params'] != '') {
				$fP->loadString($feed['feed_params']);
			}

			if (isset($feed['item_params']) && $feed['item_params'] != '') {
				$iP->loadString($feed['item_params']);
			}

			$this->t['pathitem'] = PhocacartPath::getPath('productimage');

			// Feed Params
			$p['export_published_only']		= $fP->get('export_published_only', 1);
			$p['export_in_stock_only']		= $fP->get('export_in_stock_only', 0);
			$p['export_price_only']			= $fP->get('export_price_only', 1);
			$p['strip_html_tags_desc']		= $fP->get('strip_html_tags_desc', 1);
			$p['item_limit']				= $fP->get('item_limit', 0);
			$p['item_ordering']				= $fP->get('item_ordering', 1);
			$p['category_ordering']			= $fP->get('category_ordering', 0);
			$p['display_attributes']		= $fP->get('display_attributes', 0);
			$p['category_separator']		= $fP->get('category_separator', '');
			$p['load_all_categories']		= $fP->get('load_all_categories', 0);

			$p['price_decimals']		        = $fP->get('price_decimals', '');
			$p['price_including_currency']		= $fP->get('price_including_currency', 0);

			if ($p['category_separator'] == '\n') {$p['category_separator'] = "\n";}
			if ($p['category_separator'] == '\r') {$p['category_separator'] = "\r";}
			if ($p['category_separator'] == '\r\n') {$p['category_separator'] = "\r\n";}

			// Item Params (phocacartfeex.xml, language string, view.xml.php here defined and conditions below)
			$p['item_id'] 							= $iP->get('item_id', '');
			$p['item_title'] 						= $iP->get('item_title', '');
			$p['item_title_extended'] 				= $iP->get('item_title_extended', '');
			$p['item_description_short']			= $iP->get('item_description_short', '');
			$p['item_description_long'] 			= $iP->get('item_description_long', '');
			$p['item_sku'] 							= $iP->get('item_sku', '');
			$p['item_ean'] 							= $iP->get('item_ean', '');
			$p['item_original_price_with_vat'] 		= $iP->get('item_original_price_with_vat', '');
			$p['item_original_price_without_vat'] 	= $iP->get('item_original_price_without_vat', '');
			$p['item_final_price_with_vat'] 		= $iP->get('item_final_price_with_vat', '');
			$p['item_final_price_without_vat'] 		= $iP->get('item_final_price_without_vat', '');
			$p['item_vat'] 							= $iP->get('item_vat', '');
			$p['item_currency'] 					= $iP->get('item_currency', '');
			$p['item_url_image'] 					= $iP->get('item_url_image', '');
			$p['item_url_video'] 					= $iP->get('item_url_video', '');
			$p['item_category'] 					= $iP->get('item_category', '');
			$p['item_categories'] 					= $iP->get('item_categories', '');
			$p['feed_category'] 					= $iP->get('feed_category', '');
			$p['item_manufacturer'] 				= $iP->get('item_manufacturer', '');
			$p['item_stock'] 						= $iP->get('item_stock', '');
			$p['item_delivery_date'] 				= $iP->get('item_delivery_date', '');// Stock Status
			$p['item_delivery_date_date'] 			= $iP->get('item_delivery_date_date', '');// Real Date
			$p['feed_delivery_date'] 				= $iP->get('feed_delivery_date', '');
			$p['item_attribute'] 					= $iP->get('item_attribute', '');
			$p['item_attribute_name'] 				= $iP->get('item_attribute_name', '');
			$p['item_attribute_value'] 				= $iP->get('item_attribute_value', '');
			$p['item_url'] 							= $iP->get('item_url', '');
			$p['item_condition'] 					= $iP->get('item_condition', '');
			$p['item_reward_points'] 				= $iP->get('item_reward_points', '');
			$p['item_reward_points_name'] 			= $iP->get('item_reward_points_name', '');
			$p['item_reward_points_value'] 			= $iP->get('item_reward_points_value', '');
			$p['item_type_feed'] 					= $iP->get('item_type_feed', '');
			$p['item_category_type_feed'] 			= $iP->get('item_category_type_feed', '');


			/*
			// We can find specific feed and customize it for specific needs
			// E.g. Heureka
			$feedName = '';
			if (isset($feed['title'])) {
				if (strpos(strtolower($feed['title']), 'heureka') !== false) {
					$feedName = 'heureka';
				}
			}
			*/


			// Load all categories for a product or only one
			// This influences two parameters: Categories and Product Category Type
			$categoriesList = 0;
			if ($p['load_all_categories'] == 1) {
				$categoriesList = 5;
			}

			$products = PhocacartProduct::getProducts(0, (int)$p['item_limit'], $p['item_ordering'], $p['category_ordering'], $p['export_published_only'], $p['export_in_stock_only'], $p['export_price_only'], $categoriesList);


			// HEADER
			if (isset($feed['header']) && $feed['header'] != '') {
				$o[] = $feed['header'];
			} else {
				$o[] = '<?xml version="1.0" encoding="utf-8"?>';
			}

			// ROOT START
			if (isset($feed['root']) && $feed['root'] != '') {
				$o[] = $l.$feed['root'].$r;
			}


			// PREPARE FUNCTIONS BEFORE FOREACH, so we save memory
			// E.g. currency - to get info about default currency we need to ask sql but we should to it only
			// one time, not in foreach. Of course currency class is singleton so we don't run sql query many time
			// but we don't need to run the function many times too.
			$cur = '';
			if ($p['item_currency'] != '') {
				$cur	= PhocacartCurrency::getDefaultCurrencyCode();
			}

			// START FOREACH OF PRODUCTS
			if (!empty($products)) {
				foreach ($products as $k => $v) {

					// PRODUCT START
					if (isset($feed['item']) && $feed['item'] != '') {
						$o[] = $l.$feed['item'].$r;
					}

					if ($p['item_id'] != '' && isset($v->id) && $v->id != '') {
						$o[] = $l.$p['item_id'].$r.$v->id.$e.$p['item_id'].$r;
					}

					if ($p['item_title'] != '' && isset($v->title) && $v->title != '') {
						$o[] = $l.$p['item_title'].$r.$v->title.$e.$p['item_title'].$r;
					}

					if ($p['item_title_extended'] != '' && isset($v->title) && $v->title != '') {
						$o[] = $l.$p['item_title_extended'].$r.$v->title.$e.$p['item_title_extended'].$r;
					}

					if ($p['item_description_short'] != '' && isset($v->description) && $v->description != '') {

						if ($p['strip_html_tags_desc'] == 1) {
							$v->description = strip_tags($v->description);
						}
						$o[] = $l.$p['item_description_short'].$r.'<![CDATA['.$v->description.']]>'.$e.$p['item_description_short'].$r;
					}

					if ($p['item_description_long'] != '' && isset($v->description_long) && $v->description_long != '') {

						if ($p['strip_html_tags_desc'] == 1) {
							$v->description_long = strip_tags($v->description_long);
						}

						$o[] = $l.$p['item_description_long'].$r.'<![CDATA['.$v->description_long.']]>'.$e.$p['item_description_long'].$r;
					}

					if ($p['item_sku'] != '' && isset($v->sku) && $v->sku != '') {
						$o[] = $l.$p['item_sku'].$r.$v->sku.$e.$p['item_sku'].$r;
					}

					if ($p['item_ean'] != '' && isset($v->ean) && $v->ean != '') {
						$o[] = $l.$p['item_ean'].$r.$v->ean.$e.$p['item_ean'].$r;
					}


					// PRICE ORIGINAL
					if ($p['item_original_price_with_vat'] != '' || $p['item_original_price_without_vat'] != ''
					&& isset($v->price_original) && isset($v->taxrate) && isset($v->taxcalculationtype)) {

						$priceOc 	= new PhocacartPrice;
						$priceO		= $priceOc->getPriceItems($v->price_original, $v->taxid, $v->taxrate, $v->taxcalculationtype);

						if ($p['price_decimals'] != '') {
                            $priceO['netto'] = number_format($priceO['netto'], (int)$p['price_decimals']);
                            $priceO['brutto'] = number_format($priceO['brutto'], (int)$p['price_decimals']);
                        }

                        if ($p['price_including_currency'] == 1){
                            $priceO['netto'] = $cur != '' ? $priceO['netto'] . ' ' . $cur : $priceO['netto'];
                            $priceO['brutto'] = $cur != '' ? $priceO['brutto'] . ' ' . $cur : $priceO['brutto'];
                        }

						if ($p['item_original_price_without_vat'] != '' && isset($priceO['netto']) && (int)$priceO['netto'] > 0) {
							$o[] = $l.$p['item_original_price_without_vat'].$r.$priceO['netto'].$e.$p['item_original_price_without_vat'].$r;
						}
						if ($p['item_original_price_with_vat'] != '' && isset($priceO['brutto']) && (int)$priceO['brutto'] > 0) {
							$o[] = $l.$p['item_original_price_with_vat'].$r.$priceO['brutto'].$e.$p['item_original_price_with_vat'].$r;
						}
					}

					// PRICE FINAL
					if ($p['item_final_price_with_vat'] != '' || $p['item_final_price_without_vat'] != ''
					&& isset($v->price) && isset($v->taxrate) && isset($v->taxcalculationtype)) {

                        $priceFc = new PhocacartPrice;
                        $priceF = $priceFc->getPriceItems($v->price, $v->taxid, $v->taxrate, $v->taxcalculationtype);


                        if ($p['price_decimals'] != '') {
                            $priceF['netto'] = number_format($priceF['netto'], (int)$p['price_decimals']);
                            $priceF['brutto'] = number_format($priceF['brutto'], (int)$p['price_decimals']);
                            $priceF['tax'] = number_format($priceF['tax'], (int)$p['price_decimals']);
                        }

                        if ($p['price_including_currency'] == 1){

                            $priceF['netto'] = $cur != '' ? $priceF['netto'] . ' ' . $cur : $priceF['netto'];
                            $priceF['brutto'] = $cur != '' ? $priceF['brutto'] . ' ' . $cur : $priceF['brutto'];
                            $priceF['tax'] = $cur != '' ? $priceF['tax'] . ' ' . $cur : $priceF['tax'];
                        }

						if ($p['item_final_price_without_vat'] != '' && isset($priceF['netto']) && (int)$priceF['netto'] > 0) {
							$o[] = $l.$p['item_final_price_without_vat'].$r.$priceF['netto'].$e.$p['item_final_price_without_vat'].$r;
						}
						if ($p['item_final_price_with_vat'] != '' && isset($priceF['brutto']) && (int)$priceF['brutto'] > 0) {
							$o[] = $l.$p['item_final_price_with_vat'].$r.$priceF['brutto'].$e.$p['item_final_price_with_vat'].$r;
						}

						if ($p['item_vat'] != '' && isset($priceF['tax']) && (int)$priceF['tax'] > 0) {
							$o[] = $l.$p['item_vat'].$r.$priceF['tax'].$e.$p['item_vat'].$r;
						}
					}

					// PRODUCT CURRENCY (DEFAULT)
					if ($p['item_currency'] != '' && $cur != '') {
						$o[] = $l.$p['item_currency'].$r.$cur.$e.$p['item_currency'].$r;
					}


					// PRODUCT URL
					if ($p['item_url'] != '' && isset($v->id) && $v->id > 0 && isset($v->catid) && $v->catid > 0 && isset($v->alias) && isset($v->catalias)) {

						$itemUrl 	= PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias);
						$itemUrl	= PhocacartRoute::getFullUrl($itemUrl);
						$o[] = $l.$p['item_url'].$r.$itemUrl.$e.$p['item_url'].$r;
					}

					// IMAGE URL
					if ($p['item_url_image'] != '' && isset($v->image) && $v->image != '') {
						$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v->image, 'large');
						if (isset($image->rel) && $image->rel != '') {
							$imageUrl	= PhocacartRoute::getFullUrl($image->rel);
							$o[] = $l.$p['item_url_image'].$r.$imageUrl.$e.$p['item_url_image'].$r;
						}
					}

					// VIDEO URL
					if ($p['item_url_video'] != '' && isset($v->video) && $v->video != '') {
						if (PhocacartUtils::isURLAddress($v->video)) {
							$o[] = $l.$p['item_url_video'].$r.$v->video.$e.$p['item_url_video'].$r;
						}
					}

					// CATEGORY
					if ($p['item_category'] != '' && isset($v->cattitle) && $v->cattitle != '') {
						$o[] = $l.$p['item_category'].$r.$v->cattitle.$e.$p['item_category'].$r;
					}

					// CATEGORIES
					if ($p['item_categories'] != '' && isset($v->categories) && $v->categories != '') {

						if ($p['category_separator'] == '') {
							$p['category_separator'] = ' ';
						}
						$categories = str_replace('|', $p['category_separator'], $v->categories);
						$o[] = $l.$p['item_categories'].$r.$categories.$e.$p['item_categories'].$r;
					}

					// CATEGORY FEED
					if ($p['feed_category'] != '' && isset($v->cattitlefeed) && $v->cattitlefeed != '') {
						$o[] = $l.$p['feed_category'].$r.$v->cattitlefeed.$e.$p['feed_category'].$r;
					}

					// CATEGORY TYPE OR PRODUCT CATEGORY TYPE
					if ($p['item_category_type_feed'] != '') {

						if (isset($v->type_category_feed) && $v->type_category_feed != '') {

							// 1) Product
							$o[] = $l.$p['item_category_type_feed'].$r.htmlspecialchars($v->type_category_feed).$e.$p['item_category_type_feed'].$r;

						} else if (isset($v->feedcategories ) && $v->feedcategories  != '') {

							// 2) Categories - loaded by db
							$feedcategories = str_replace('|', $p['category_separator'], htmlspecialchars($v->feedcategories));
							$o[] = $l.$p['item_category_type_feed'].$r.$feedcategories.$e.$p['item_category_type_feed'].$r;

							// Only one category possible e.g. in Google Products, so this can be customized
							//$feedcategories = explode('|', $v->feedcategories);
							//if (isset($feedcategories[0]) && $feedcategories[0] != '') {
							//	$o[] = $l.$p['item_category_type_feed'].$r.htmlspecialchars($feedcategories[0]).$e.$p['item_category_type_feed'].$r;
							//}

						} else if (isset($v->cattypefeed) && $v->cattypefeed != '') {
							// 3) Category - if not 2) loaded
							$o[] = $l.$p['item_category_type_feed'].$r.htmlspecialchars($v->cattypefeed).$e.$p['item_category_type_feed'].$r;
						}
					}

					// MANUFACTURER
					if ($p['item_manufacturer'] != '' && isset($v->manufacturertitle) && $v->manufacturertitle != '') {
						$o[] = $l.$p['item_manufacturer'].$r.$v->manufacturertitle.$e.$p['item_manufacturer'].$r;
					}

					// STOCK
					if ($p['item_stock'] != '' && isset($v->stock) && $v->stock != '') {
						$o[] = $l.$p['item_stock'].$r.$v->stock.$e.$p['item_stock'].$r;
					}

					// STOCK DELIVERY_DATE
					if ($p['item_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) ) {


						$stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id);
						//$stockText		= PhocacartStock::getStockStatusOutput($stockStatus);
						if (isset($stockStatus['stock_status']) && $stockStatus['stock_status'] != '') {
							$o[] = $l.$p['item_delivery_date'].$r.$stockStatus['stock_status'].$e.$p['item_delivery_date'].$r;
						}
					}

					// STOCK DELIVERY_DATE - REAL DATE
					if ($p['item_delivery_date_date'] != '' && isset($v->delivery_date) && $v->delivery_date != '' && $v->delivery_date != '0000-00-00 00:00:00') {
						$o[] = $l.$p['item_delivery_date_date'].$r.$v->delivery_date.$e.$p['item_delivery_date_date'].$r;
					}

					// STOCK DELIVERY_DATE FEED
					if ($p['feed_delivery_date'] != '' && isset($v->stock) && isset($v->min_quantity) && isset($v->min_multiple_quantity) && isset($v->stockstatus_a_id) && isset($v->stockstatus_n_id) ) {
						$stockStatus 	= PhocacartStock::getStockStatus((int)$v->stock, (int)$v->min_quantity, (int)$v->min_multiple_quantity, (int)$v->stockstatus_a_id,  (int)$v->stockstatus_n_id);


						if (isset($stockStatus['stock_status_feed']) && $stockStatus['stock_status_feed'] != '') {
							$o[] = $l.$p['feed_delivery_date'].$r.$stockStatus['stock_status_feed'].$e.$p['feed_delivery_date'].$r;
						}
					}

					//
					// NEEDS TO BE CUSTOMIZED FOR EACH XML FEED
					//
					if ($p['display_attributes'] == 1 && $p['item_attribute'] != '' && $p['item_attribute_name'] != '' && $p['item_attribute_value'] != '') {
						// ATTRIBUTES - BE AWARE TO USER ATTRIBUTES
						// RENDERING can take a lot of memory
						// THE FORMAT can be not correct
						$attributes = PhocacartAttribute::getAttributesAndOptions((int)$v->id);
						if (!empty($attributes)) {
							foreach ($attributes as $k2 => $v2) {
								if (isset($v2->title) && $v2->title != '') {

									$o[] = $l.$p['item_attribute'].$r;
									$o[] = $l.$p['item_attribute_name'].$r.$v2->title.$e.$p['item_attribute_name'].$r;

									if (!empty($v2->options)) {
										$opt = array();
										foreach ($v2->options as $k3 => $v3) {
											$opt[] = $v3->title;
										}
										$optText = implode(';', $opt);
										$o[] = $l.$p['item_attribute_value'].$r.$optText.$e.$p['item_attribute_value'].$r;
									}
									$o[] = $e.$p['item_attribute'].$r;
								}

							}
						}
					}

					// PRODUCT CONDITION
					if ($p['item_condition'] != '' && isset($v->condition)) {
						$condition = PhocacartUtilsSettings::getProductConditionValues($v->condition);
						$o[] = $l.$p['item_condition'].$r.$condition.$e.$p['item_condition'].$r;

					}

					// PRODUCT REWARD POINTS
					if ($p['item_reward_points'] != '' && isset($v->points_received) && (int)$v->points_received > 0) {


						if ($p['item_reward_points_name'] != '' && $p['item_reward_points_value'] != '') {
							$o[] = $l.$p['item_reward_points'].$r;

							$o[] = $l.$p['item_reward_points_name'].$r.JText::_('COM_PHOCACART_FEED_TXT_PRODUCT_REWARD_POINTS').$e.$p['item_reward_points_name'].$r;
							$o[] = $l.$p['item_reward_points_value'].$r.(int)$v->points_received.$e.$p['item_reward_points_value'].$r;
							// Possible RATION value

							$o[] = $e.$p['item_reward_points'].$r;
						} else {
							$o[] = $l.$p['item_reward_points'].$r.(int)$v->points_received.$e.$p['item_reward_points'].$r;
						}
					}

					// PRODUCT TYPE FEED
					if ($p['item_type_feed'] != '' && isset($v->type_feed) && $v->type_feed != '') {
						$o[] = $l.$p['item_type_feed'].$r.htmlspecialchars($v->type_feed).$e.$p['item_type_feed'].$r;
					}




					// PRODUCT END
					if (isset($feed['item']) && $feed['item'] != '') {
						$o[] = $e.$feed['item'].$r;
					}
				}

			}


			// ROOT END
			if (isset($feed['root']) && $feed['root'] != '') {
				$o[] = $e.$feed['root'].$r;
			}


			// FOOTER
			if (isset($feed['footer']) && $feed['footer'] != '') {
				$o[] = $feed['footer'];
			}
			$a = implode( "\n", $o );

			echo $a;

		}
	}
}
?>
