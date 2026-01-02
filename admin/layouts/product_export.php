<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


// Specific function for CSV
if (!function_exists('str_putcsv')) {
	function str_putcsv($input, $delimiter = ';', $enclosure = '"') {
		$fp = fopen('php://temp', 'r+b');
		fputcsv($fp, $input, $delimiter, $enclosure);
		rewind($fp);
		$data = rtrim(stream_get_contents($fp), "\n");
		fclose($fp);
		return $data;
	}
}

// Params of component
$db 							= Factory::getDBO();
$user							= Factory::getUser();
$paramsC 						= PhocacartUtils::getComponentParameters();
$import_export_type				= $paramsC->get( 'import_export_type', 0 );
$export_attributes				= $paramsC->get( 'export_attributes', 1 );
$export_advanced_stock_options	= $paramsC->get( 'export_advanced_stock_options', 0 );
$export_specifications			= $paramsC->get( 'export_specifications', 1 );
$export_discounts				= $paramsC->get( 'export_discounts', 1 );
$export_downloads				= $paramsC->get( 'export_downloads', 0 );
$export_add_title				= $paramsC->get( 'export_add_title', 0);
$export_parameters				= $paramsC->get( 'export_parameters', 0);
/*
*
* Unfortunately, add attributes to xml tags can be very problematic
*  - it takes a lot of memory when managing (importing) it
*  - and it takes a lot of place in exporting file
* Problems when importing XML:
* Because of bind columns when importing ( xml-> joomla database), we need to remove '@attribute' array from xml
* The problem is, this action can stop server working when using simple xml methods
*
<field name="export_add_title" class="btn-group" type="radio" default="0" label="COM_PHOCACART_FIELD_ADD_TITLE_ATTRIBUTE_EXPORT_LABEL" description="COM_PHOCACART_FIELD_ADD_TITLE_ATTRIBUTE_EXPORT_DESC">
	<option value="0">COM_PHOCACART_NO</option>
	<option value="1">COM_PHOCACART_YES</option>
</field>
* To enable it
* 1) uncomment in config.xml
* 2) comment "$export_add_title = 0;" here
* 3) ucomment foreach in components\com_phocacart\layouts\product_import.php
*/
$export_add_title		= 0;

$xml = false;
$csv = false;
if ($import_export_type == 1) {
	$xml = true;
	$csv = false;
} else if ($import_export_type == 0) {
	$csv = true;
	$xml = false;
}


$d 	= $displayData;

$iP = array();// Products rows
$iA	= array();// Products formated to database
$iCN= array();// Product column name
$iCV= array();// Product column title (translated name)
$iO = array();// Final output

// Tabs for XML
$t0 = "\t";
$t1 = "\t\t";
$t2 = "\t\t\t";
$t3 = "\t\t\t\t";
$t4 = "\t\t\t\t\t";
$t5 = "\t\t\t\t\t\t";



// COLUMNS OF PRODUCTS
// Here you can comment columns you don't need to export
//



$a = array();
$a[] = array('id', 'JGLOBAL_FIELD_ID_LABEL');
$a[] = array('title', 'COM_PHOCACART_FIELD_TITLE_LABEL');
$a[] = array('alias', 'COM_PHOCACART_FIELD_ALIAS_LABEL');

$a[] = array('sku', 'COM_PHOCACART_FIELD_SKU_LABEL');
$a[] = array('ean', 'COM_PHOCACART_FIELD_EAN_LABEL');

$a[] = array('price', 'COM_PHOCACART_FIELD_PRICE_LABEL');
$a[] = array('price_original', 'COM_PHOCACART_FIELD_ORIGINAL_PRICE_LABEL');

$a[] = array('price_groups', 'COM_PHOCACART_FIELD_PRICE_GROUPS_LABEL');

$a[] = array('price_histories', 'COM_PHOCACART_FIELD_PRICE_HISTORY_LABEL');


// TAX***
//$a[] = array('tax_id', 'COM_PHOCACART_FIELD_TAX_LABEL');
$a[] = array('tax', 'COM_PHOCACART_FIELD_TAX_LABEL');

// CATEGORIES (not exist in query)
$a[] = array('categories', 'COM_PHOCACART_CATEGORIES');

// MANUFACTURER***
//$a[] = array('manufacturer_id', 'COM_PHOCACART_FIELD_MANUFACTURER_LABEL');
$a[] = array('manufacturer', 'COM_PHOCACART_FIELD_MANUFACTURER_LABEL');

$a[] = array('upc', 'COM_PHOCACART_FIELD_UPC_LABEL');
$a[] = array('jan', 'COM_PHOCACART_FIELD_JAN_LABEL');
$a[] = array('isbn', 'COM_PHOCACART_FIELD_ISBN_LABEL');
$a[] = array('mpn', 'COM_PHOCACART_FIELD_MPN_LABEL');

$a[] = array('serial_number', 'COM_PHOCACART_FIELD_SERIAL_NUMBER_LABEL');
$a[] = array('registration_key', 'COM_PHOCACART_FIELD_REGISTRATION_KEY_LABEL');

$a[] = array('external_id', 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_ID_LABEL');
$a[] = array('external_key', 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_KEY_LABEL');
$a[] = array('external_link', 'COM_PHOCACART_FIELD_EXTERNAL_LINK_LABEL');
$a[] = array('external_text', 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_LABEL');

$a[] = array('external_link2', 'COM_PHOCACART_FIELD_EXTERNAL_LINK_2_LABEL');//-
$a[] = array('external_text2', 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_2_LABEL');//-


$a[] = array('access', 'JFIELD_ACCESS_LABEL');
$a[] = array('groups', 'COM_PHOCACART_FIELD_GROUPS_LABEL');

$a[] = array('featured', 'COM_PHOCACART_FIELD_FEATURED_LABEL');

$a[] = array('video', 'COM_PHOCACART_FIELD_VIDEO_URL_LABEL');
$a[] = array('public_download_file', 'COM_PHOCACART_FIELD_PUBLIC_DOWNLOAD_FILE_LABEL');
$a[] = array('public_download_text', 'COM_PHOCACART_FIELD_PUBLIC_DOWNLOAD_FILE_TEXT_LABEL');

$a[] = array('public_play_file', 'COM_PHOCACART_FIELD_PUBLIC_FILE_PLAY_LABEL');//-
$a[] = array('public_play_text', 'COM_PHOCACART_FIELD_PUBLIC_FILE_PLAY_TEXT_LABEL');//-

$a[] = array('condition', 'COM_PHOCACART_FIELD_PRODUCT_CONDITION_LABEL');
$a[] = array('type_feed', 'COM_PHOCACART_FIELD_PRODUCT_TYPE_FEED_LABEL');
$a[] = array('type_category_feed', 'COM_PHOCACART_FIELD_PRODUCT_CATEGORY_TYPE_FEED_LABEL');

$a[] = array('description', 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL');
$a[] = array('description_long', 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL');
$a[] = array('features', 'COM_PHOCACART_FIELD_FEATURES_LABEL');//-

$a[] = array('image', 'COM_PHOCACART_FIELD_IMAGE_LABEL');

// IMAGES (not exist in query)
$a[] = array('images', 'COM_PHOCACART_ADDITIONAL_IMAGES');

if ($export_attributes == 1) {
	// ATTRIBUTES (not exist in query)
	$a[] = array('attributes', 'COM_PHOCACART_ATTRIBUTES');
}

if ($export_advanced_stock_options == 1) {
	// ATTRIBUTES (not exist in query)
	$a[] = array('advanced_stock_options', 'COM_PHOCACART_ADVANCED_STOCK_OPTIONS');
}

if ($export_specifications == 1) {
	// SPECIFICATIONS (not exist in query)
	$a[] = array('specifications', 'COM_PHOCACART_SPECIFICATIONS');
}

if ($export_discounts == 1) {
	// DISCOUNTS (not exist in query)
	$a[] = array('discounts', 'COM_PHOCACART_DISCOUNTS');
}

// RELATED_PRODUCTS (not exist in query)
$a[] = array('related', 'COM_PHOCACART_RELATED_PRODUCTS');

$a[] = array('stock', 'COM_PHOCACART_FIELD_IN_STOCK_LABEL');
$a[] = array('stock_calculation', 'COM_PHOCACART_FIELD_PRODUCT_STOCK_CALCULATION_LABEL');
$a[] = array('stockstatus_a_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_A_LABEL');
$a[] = array('stockstatus_n_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_B_LABEL');
$a[] = array('min_quantity', 'COM_PHOCACART_FIELD_MIN_ORDER_QUANTITY_LABEL');
$a[] = array('min_multiple_quantity', 'COM_PHOCACART_FIELD_MIN_MULTIPLE_ORDER_QUANTITY_LABEL');
$a[] = array('min_quantity_calculation', 'COM_PHOCACART_FIELD_MINIMUM_QUANTITY_CALCULATION_LABEL');
$a[] = array('max_quantity', 'COM_PHOCACART_FIELD_MAX_ORDER_QUANTITY_LABEL');
$a[] = array('max_quantity_calculation', 'COM_PHOCACART_FIELD_MAXIMUM_QUANTITY_CALCULATION_LABEL');

$a[] = array('delivery_date', 'COM_PHOCACART_FIELD_PRODUCT_DELIVERY_DATE_LABEL');//-

//$a[] = array('availability', 'COM_PHOCACART_FIELD_AVAILABILITY_LABEL');

if ($export_downloads == 1) {
	$a[] = array('download_token', 'COM_PHOCACART_FIELD_DOWNLOAD_TOKEN_LABEL');
	$a[] = array('download_folder', 'COM_PHOCACART_FIELD_DOWNLOAD_FOLDER_LABEL');
	$a[] = array('download_file', 'COM_PHOCACART_FIELD_DOWNLOAD_FILE_LABEL');
	$a[] = array('download_hits', 'COM_PHOCACART_FIELD_DOWNLOAD_HITS_LABEL');
	$a[] = array('download_days', 'COM_PHOCACART_FIELD_DOWNLOAD_EXPIRATION_DATE_DAYS_LABEL');//-

    // IMAGES (not exist in query)
    $a[] = array('additional_download_files', 'COM_PHOCACART_ADDITIONAL_DOWNLOAD_FILES');//-
}
$a[] = array('type', 'COM_PHOCACART_FIELD_PRODUCT_TYPE_LABEL');
//$a[] = array('delivery_date', 'COM_PHOCACART_FIELD_PRODUCT_DELIVERY_DATE_LABEL');

$a[] = array('length', 'COM_PHOCACART_FIELD_LENGTH_LABEL');
$a[] = array('width', 'COM_PHOCACART_FIELD_WIDTH_LABEL');
$a[] = array('height', 'COM_PHOCACART_FIELD_HEIGHT_LABEL');

//$a[] = array('unit_size', 'COM_PHOCACART_FIELD_UNIT_SIZE_LABEL');
$a[] = array('weight', 'COM_PHOCACART_FIELD_WEIGHT_LABEL');
//$a[] = array('unit_weight', 'COM_PHOCACART_FIELD_UNIT_WEIGHT_LABEL');
$a[] = array('volume', 'COM_PHOCACART_FIELD_VOLUME_LABEL');
//$a[] = array('unit_volume', 'COM_PHOCACART_FIELD__LABEL');
$a[] = array('unit_amount', 'COM_PHOCACART_FIELD_UNIT_AMOUNT_LABEL');
$a[] = array('unit_unit', 'COM_PHOCACART_FIELD_UNIT_UNIT_LABEL');

$a[] = array('points_needed', 'COM_PHOCACART_FIELD_POINTS_NEEDED_LABEL');
$a[] = array('points_received', 'COM_PHOCACART_FIELD_POINTS_RECEIVED_LABEL');
$a[] = array('point_groups', 'COM_PHOCACART_FIELD_POINT_GROUPS_LABEL');

$a[] = array('published', 'COM_PHOCACART_FIELD_PUBLISHED_LABEL');
$a[] = array('language', 'JFIELD_LANGUAGE_LABEL');

$a[] = array('date', 'COM_PHOCACART_FIELD_DATE_LABEL');
$a[] = array('date_update', 'COM_PHOCACART_FIELD_UPDATE_DATE_LABEL');//-

$a[] = array('created_by', 'COM_PHOCACART_FIELD_CREATED_BY_LABEL');//-
$a[] = array('created', 'COM_PHOCACART_FIELD_CREATED_DATE_LABEL');//-
$a[] = array('modified_by', 'COM_PHOCACART_FIELD_MODIFIED_BY_LABEL');//-
$a[] = array('modified', 'COM_PHOCACART_FIELD_UPDATE_DATE_LABEL');//-





// TAGS (not exist in query)
$a[] = array('tags', 'COM_PHOCACART_TAGS');
$a[] = array('taglabels', 'COM_PHOCACART_LABELS');//-

if($export_parameters == 1){
    $a[] = array('items_parameter', 'COM_PHOCACART_PARAMETERS');//-
    $parameters = PhocacartParameter::getAllParameters();
}


$a[] = array('metakey', 'JFIELD_META_KEYWORDS_LABEL');
$a[] = array('metadesc', 'JFIELD_META_DESCRIPTION_LABEL');
$a[] = array('metatitle', 'COM_PHOCACART_FIELD_META_TITLE_LABEL');//-
$a[] = array('metadata', 'COM_PHOCACART_METADATA_LABEL');//-
$a[] = array('sales', 'COM_PHOCACART_FIELD_SALES_LABEL');//-


//$a[] = array('allow_upload', 'COM_PHOCACART_FIELD_ALLOW_UPLOAD_LABEL');
//$a[] = array('custom_text', 'COM_PHOCACART_FIELD_CUSTOM_TEXT_LABEL');

//$a[] = array('checked_out', 'COM_PHOCACART_FIELD__LABEL');
//$a[] = array('checked_out_time', 'COM_PHOCACART_FIELD__LABEL');
// Not used
//$a[] = array('hits', 'COM_PHOCACART_FIELD_HITS_LABEL');
//$a[] = array('sales', 'COM_PHOCACART_FIELD__LABEL');
//$a[] = array('params', 'COM_PHOCACART_FIELD__LABEL');
//$a[] = array('metadata', 'COM_PHOCACART_FIELD__LABEL');


$d['productcolumns'] = $a;
unset($a);


// RENDERING
// Header
if (!empty($d['productcolumns'])){
	foreach($d['productcolumns'] as $k => $v) {
		$iCN[] = Text::_($v[0]);
		$iCV[] = Text::_($v[1]);

	}
}

if (!empty($d['products'])){

	foreach($d['products'] as $k => $v) {

		if (!empty($d['productcolumns'])) {
			$iP = array();

			if ($xml) { $iP[] =  $t0 . '<product>';}



			foreach($d['productcolumns'] as $k2 => $v2) {

				$col = $v2[0];



				if (isset($v[$col])) {
					// COLUMNS FROM PRODUCT TABLE

					//$iP[] = $qT . $v[$col] . $qT;

					$l 	= '';
					$r 	= '';

					switch($iCN[$k2]) {

						case 'description':
						case 'description_long':
                        case 'features':
						case 'type_feed':
						case 'type_category_feed':
							if ($xml) {
								$l = '<![CDATA[';
								$r = ']]>';
							} else {
								// CSV
								$v[$col] = str_replace("\n", '', $v[$col]);
								$v[$col] = str_replace("\r", '', $v[$col]);

							}
						break;


						default:
						break;

					}

					if ($xml) {

						$title = '';
						if ($export_add_title == 1) {
							$title = ' title="'.strip_tags(Text::_($iCV[$k2])).'"';
						}

						if (isset($v[$col])) {
							$iP[] = $t1 . '<'.strip_tags($iCN[$k2]).$title.'>' . $l . htmlspecialchars($v[$col], ENT_XML1) . $r. '</'.strip_tags($iCN[$k2]).'>';
						}
					} else {
						$iP[] = $v[$col];
					}

				} else {


					// COLUMNS DYNAMICALLY CREATED BY OTHER TABLES
					switch($col) { // we select col, so here we don't need to check: if (isset($v[$col])) {

						case 'categories':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartProduct::getCategoriesByProductId((int)$v['id']);
								if (!empty($items)) {

									if ($xml) {

										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}

										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<category>';
											$iP[] = $t3 . '<id>'.$vX['category_id'].':'.$vX['alias'].'</id>';
											$iP[] = $t3 . '<ordering>'.$vX['ordering'].'</ordering>';
											$iP[] = $t2 . '</category>';
										} else {
											$x[$kX]['id'] 			= $vX['category_id'].':'.$vX['alias'];
											$x[$kX]['ordering'] 	= $vX['ordering'];

										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($x);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'images':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartProduct::getImagesByProductId((int)$v['id']);
								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<image>'.$vX['image'].'</image>';
										} else {
											$x[] = $vX['image'];
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										$iP[] = implode('|', $x);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'attributes':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartAttribute::getAttributesAndOptions((int)$v['id']);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<attribute>';
											$iP[] = $t3 . '<id>'.$vX->id.'</id>';
											$iP[] = $t3 . '<title>'.htmlspecialchars($vX->title, ENT_XML1).'</title>';
											$iP[] = $t3 . '<alias>'.$vX->alias.'</alias>';
											$iP[] = $t3 . '<required>'.$vX->required.'</required>';
											$iP[] = $t3 . '<type>'.$vX->type.'</type>';
											if (!empty($vX->options)) {
												$iP[] = $t3 . '<options>';
												foreach($vX->options as $kX2 => $vX2) {
													$iP[] = $t4 . '<option>';
													$iP[] = $t5 . '<id>'.$vX2->id.'</id>';
													$iP[] = $t5 . '<title>'.htmlspecialchars($vX2->title, ENT_XML1) .'</title>';
													$iP[] = $t5 . '<alias>'.$vX2->alias.'</alias>';
													$iP[] = $t5 . '<amount>'.$vX2->amount.'</amount>';
													$iP[] = $t5 . '<operator>'.$vX2->operator.'</operator>';
													$iP[] = $t5 . '<stock>'.$vX2->stock.'</stock>';
													//$iP[] = $t5 . '<stock_calculation>'.$vX2->stock_calculation.'</stock_calculation>';
													$iP[] = $t5 . '<operator_weight>'.$vX2->operator_weight.'</operator_weight>';
													$iP[] = $t5 . '<weight>'.$vX2->weight.'</weight>';
													$iP[] = $t5 . '<image>'.htmlspecialchars($vX2->image, ENT_XML1) .'</image>';
													$iP[] = $t5 . '<image_medium>'.htmlspecialchars($vX2->image_medium, ENT_XML1) .'</image_medium>';
													$iP[] = $t5 . '<image_small>'.htmlspecialchars($vX2->image_small, ENT_XML1) .'</image_small>';
													$iP[] = $t5 . '<download_folder>'.$vX2->download_folder.'</download_folder>';
													$iP[] = $t5 . '<download_token>'.$vX2->download_token.'</download_token>';
													$iP[] = $t5 . '<download_file>'.$vX2->download_file.'</download_file>';
													$iP[] = $t5 . '<color>'.$vX2->color.'</color>';
													$iP[] = $t5 . '<default_value>'.$vX2->image.'</default_value>';
													$iP[] = $t4 .'</option>';
												}
												$iP[] = $t3 . '</options>';
											}
											$iP[] = $t2 . '</attribute>';
										} else {
											//$x[] = $vX['image'];
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'specifications':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartSpecification::getSpecificationsById((int)$v['id'], 1);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<specification>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<group_id>'.$vX['group_id'].'</group_id>';
											$iP[] = $t3 . '<title>'.htmlspecialchars($vX['title'], ENT_XML1) .'</title>';
											$iP[] = $t3 . '<alias>'.$vX['alias'].'</alias>';
											$iP[] = $t3 . '<value>'.$vX['value'].'</value>';
											$iP[] = $t3 . '<alias_value>'.$vX['alias_value'].'</alias_value>';
											$iP[] = $t3 . '<image>'.$vX['image'].'</image>';
											$iP[] = $t3 . '<image_medium>'.$vX['image_medium'].'</image_medium>';
											$iP[] = $t3 . '<image_small>'.$vX['image_small'].'</image_small>';
											$iP[] = $t3 . '<color>'.$vX['color'].'</color>';
											$iP[] = $t2 . '</specification>';
										} else {
											//$x[] = $vX['image'];
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'discounts':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartDiscountProduct::getDiscountsById((int)$v['id'], 1);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {

										$groups = PhocacartGroup::getGroupsById((int)$vX['id'], 4, 2, (int)$v['id']);


										if ($xml) {
											$iP[] = $t2 . '<discount>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<title>'.htmlspecialchars($vX['title'], ENT_XML1) .'</title>';
											$iP[] = $t3 . '<alias>'.$vX['alias'].'</alias>';
											$iP[] = $t3 . '<discount>'.$vX['discount'].'</discount>';
											$iP[] = $t3 . '<access>'.$vX['access'].'</access>';
											$iP[] = $t3 . '<calculation_type>'.$vX['calculation_type'].'</calculation_type>';
											$iP[] = $t3 . '<quantity_from>'.$vX['quantity_from'].'</quantity_from>';
											$iP[] = $t3 . '<valid_from>'.$vX['valid_from'].'</valid_from>';
											$iP[] = $t3 . '<valid_to>'.$vX['valid_to'].'</valid_to>';



											if (!empty($groups)) {
												$iP[] = $t3 . '<groups>';

												foreach($groups as $kY => $vY) {
													$iP[] = $t4 . '<group>';
													$iP[] = $t5 . '<id>'.$vY['id'].'</id>';
													$iP[] = $t5 . '<title>'.htmlspecialchars($vY['title'], ENT_XML1) .'</title>';
													$iP[] = $t4 . '</group>';
												}

												$iP[] = $t3 . '</groups>';

											}

											$iP[] = $t2 . '</discount>';
										} else {
											//$x[] = $vX['image'];
											if (!empty($groups)) {
												foreach($groups as $kY => $vY) {
													unset($groups[$kY]['alias']);
													unset($groups[$kY]['type']);
												}
											}
											$items[$kX]['groups'] = $groups;// set it for CSV
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;


						case 'advanced_stock_options':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartAttribute::getCombinationsStockById((int)$v['id'], 1);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<advanced_stock_option>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<product_id>'.$vX['product_id'].'</product_id>';
											$iP[] = $t3 . '<product_key>'.$vX['product_key'].'</product_key>';
											$iP[] = $t3 . '<stock>'.$vX['stock'].'</stock>';
											$iP[] = $t2 . '</advanced_stock_option>';
										} else {
											//$x[] = $vX['image'];
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'related':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartRelated::getRelatedItemsById((int)$v['id'], 2, 1);

                                if (!empty($items)) {
									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<related_product>'.$vX->id.':'.$vX->alias.'</related_product>';
										} else {
											$x[] = $vX->id.':'.$vX->alias;
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										$iP[] = implode('|', $x);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'tags':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartTag::getTags((int)$v['id'], 2);
								if (!empty($items)) {
									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<tag>'.$vX->id.':'.$vX->alias.'</tag>';
										} else {
											$x[] = $vX->id.':'.$vX->alias;
										}
									}


									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										$iP[] = implode('|', $x);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

                        case 'taglabels':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartTag::getTagLabels((int)$v['id'], 2);
								if (!empty($items)) {
									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();
									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<label>'.$vX->id.':'.$vX->alias.'</label>';
										} else {
											$x[] = $vX->id.':'.$vX->alias;
										}
									}


									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										$iP[] = implode('|', $x);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

                        case 'items_parameter':

							if (isset($v['id']) && (int)$v['id'] > 0) {

                                // $parameters = Parameters defined at start
                                if (!empty($parameters)) {

                                    if ($xml) {
                                        $title = '';
                                        if ($export_add_title == 1) {
                                            $title = ' title="'.strip_tags(Text::_($v2[1])).'"';
                                        }
                                        $iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
                                    }


                                    $items = array();
                                    foreach($parameters as $kX => $vX) {

                                        $idP = (int)$vX->id;
                                        $items[$idP] = array();
										if ($xml) {
											$pA = PhocacartParameter::getParameterValues((int)$v['id'], $idP,0);
											if (!empty($pA)) {
											    $iP[] = $t2 . '<parameter>';
											    $iP[] = $t3 . '<id>'.$vX->id.'</id>';
											    $iP[] = $t3 . '<title>'.htmlspecialchars($vX->title, ENT_XML1).'</title>';
											    $iP[] = $t3 . '<alias>'.$vX->alias.'</alias>';
												$iP[] = $t3 . '<values>';
												foreach($pA as $kX2 => $vX2) {
													$iP[] = $t4 . '<value>';
													$iP[] = $t5 . '<id>'.$vX2->id.'</id>';
													$iP[] = $t5 . '<title>'.$vX2->title.'</title>';
													$iP[] = $t5 . '<alias>'.$vX2->alias.'</alias>';
													$iP[] = $t4 .'</value>';
												}
												$iP[] = $t3 . '</values>';
												$iP[] = $t2 . '</parameter>';
											}

										} else {
										    $items[$idP]	= PhocacartParameter::getParameterValues((int)$v['id'], $idP,2);// CSV
                                        }
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'groups':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartGroup::getGroupsById((int)$v['id'], 3, 2);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<group>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<title>'.htmlspecialchars($vX['title'], ENT_XML1).'</title>';
											$iP[] = $t2 . '</group>';
										} else {
											//$x[] = $vX['image'];
											unset($items[$kX]['alias']);
											unset($items[$kX]['type']);
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);

										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'price_groups':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartGroup::getProductPriceGroupsById((int)$v['id']);


								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<price_group>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<product_id>'.$vX['product_id'].'</product_id>';
											$iP[] = $t3 . '<group_id>'.$vX['group_id'].'</group_id>';
											$iP[] = $t3 . '<price>'.$vX['price'].'</price>';
											$iP[] = $t2 . '</price_group>';
										} else {
											//$x[] = $vX['image'];

										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);

										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						case 'point_groups':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartGroup::getProductPointGroupsById((int)$v['id']);


								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<point_group>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<product_id>'.$vX['product_id'].'</product_id>';
											$iP[] = $t3 . '<group_id>'.$vX['group_id'].'</group_id>';
											$iP[] = $t3 . '<points_received>'.$vX['points_received'].'</points_received>';
											$iP[] = $t2 . '</point_group>';
										} else {
											//$x[] = $vX['image'];

										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);

										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						// Price history
						case 'price_histories':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartPriceHistory::getPriceHistoryById((int)$v['id'], 0, 1);


								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<price_history>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<product_id>'.$vX['product_id'].'</product_id>';
											$iP[] = $t3 . '<date>'.$vX['date'].'</date>';
											$iP[] = $t3 . '<price>'.$vX['price'].'</price>';
											$iP[] = $t2 . '</price_history>';
										} else {
											//$x[] = $vX['image'];

										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);

										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;


                        case 'additional_download_files':

							if (isset($v['id']) && (int)$v['id'] > 0) {
								$items = PhocacartFileAdditional::getProductFilesByProductId((int)$v['id'], 1);

								if (!empty($items)) {

									if ($xml) {
										$title = '';
										if ($export_add_title == 1) {
											$title = ' title="'.strip_tags(Text::_($v2[1])).'"';
										}
										$iP[] = $t1 . '<'.strip_tags($v2[0]).$title.'>';
									}

									$x = array();

									foreach($items as $kX => $vX) {
										if ($xml) {
											$iP[] = $t2 . '<additional_download_file>';
											$iP[] = $t3 . '<id>'.$vX['id'].'</id>';
											$iP[] = $t3 . '<download_file>'.$vX['download_file'].'</download_file>';
											$iP[] = $t3 . '<download_token>'.$vX['download_token'].'</download_token>';
											$iP[] = $t3 . '<download_days>'.$vX['download_days'].'</download_days>';
											$iP[] = $t2 . '</additional_download_file>';
										} else {
											//$x[] = $vX['image'];
										}
									}

									if ($xml) {
										$iP[] = $t1 . '</'.strip_tags($v2[0]).'>';
									} else {
										//$iP[] = implode('|', $x);
										$iP[] = json_encode($items);
									}
								} else {
									if ($csv) {$iP[] = '';}// CSV set right column count
								}
							}
						break;

						default:

						break;

					}
				}
			}

			if ($xml) { $iP[] = $t0 . '</product>'; }


			if (!empty($iP)) {
				//$iA[] = ' ('.(int)$user->id .', '. $db->quote(implode($sP, $iP)).', 0)';
				//$iA[] = ' ('.(int)$user->id .', '. $db->quote(serialize($iP)).'), 0';
				if ($xml) {
					$iA[] = ' ('.(int)$user->id .', '. $db->quote(implode("\n", $iP)).', 0)';
				} else {
					$iA[] = ' ('.(int)$user->id .', '. $db->quote(str_putcsv($iP)).', 0)';
				}
			}
		}
	}
}

// First Row Head - column name (ID) CSV
if (!empty($iCN) && $d['page'] == 1 && !$xml) {
	$iO[] = ' ('.(int)$user->id .', '. $db->quote(str_putcsv($iCN)).', 1)';
}
// Second Row Head - column name translated CSV
if (!empty($iCV) && $d['page'] == 1 && !$xml) {
	$iO[] = ' ('.(int)$user->id .', '. $db->quote(str_putcsv($iCV)).', 1)';
}

// First and second Row Head - XML
if ($d['page'] == 1 && $xml) {
	$iO[] = ' ('.(int)$user->id .', '. $db->quote('<?xml version="1.0" encoding="utf-8"?>').', 1)';
	$iO[] = ' ('.(int)$user->id .', '. $db->quote('<products>').', 1)';
}


// All product rows
if (!empty($iA)) {
	$iO[] =  implode(", ", $iA);
}


// Last Row - XML
if (($d['last_page'] == $d['page']) && $xml) {
	$iO[] = ' ('.(int)$user->id .', '. $db->quote('</products>').', 2)';
}

if (!empty($iO)) {
	echo implode(", ", $iO);
}
