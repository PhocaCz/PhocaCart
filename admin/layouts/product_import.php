<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

function filter_xml($matches) {
    return trim(htmlspecialchars($matches[1]));
}


$paramsC 				= PhocacartUtils::getComponentParameters();
$import_export_type		= $paramsC->get( 'import_export_type', 0 );
$import_column			= $paramsC->get( 'import_column', 1 );

$d 		= $displayData;
$data	= array();

$xml = false;
$csv = false;
if (isset($d['file_type']) && $d['file_type'] == 1) {
	$xml = true;
	$csv = false;
} else if (isset($d['file_type']) && $d['file_type'] == 0) {
	$csv = true;
	$xml = false;
}

$productIdChange = array();// Needed for related table, when the product changes its ID (autoincrement), recreat related table




if($xml) {
	// --------
	// XML
	if (!empty($d['products'])){
		foreach($d['products'] as $k => $v) {

			$data = array();

			if (isset($v['item']) && $v['item'] != '') {


				$item = simplexml_load_string($v['item'], null, LIBXML_NOCDATA);

				// Remove @attributes in case xml includes parameters (this can be enabled by customization)
				// See comment in components\com_phocacart\layouts\product_export.php - line cca 36
				/*
				foreach($item as $k => $v) {
					switch($k) {
						//case 'tax':
						case 'categories':
						//case 'manufacturer':
						case 'images':
						case 'attributes':
						case 'specifications':
						case 'discounts':
						case 'related':
						case 'tags':
							$item2[$k] = $v;
						break;

						default:
							$item2[$k] = (string)$item->$k;
						break;
					}
				}

				$json = json_encode($item2);*/

				$json = json_encode($item);
				$data = json_decode($json, true);


				$data['tax_id'] 				= array();
				$data['manufacturer_id'] 		= array();
				$data['catid_multiple']			= array();
				$data['catid_multiple_ordering']= array();


				// TITLE
				// No title - skip
				if (isset($data['title']) && $data['title'] == '') {
					continue;
				}

				// TAX
				// Specific rules
				if (isset($data['tax'])) {
					$data['tax_id'] = PhocacartUtils::getIntFromString($data['tax']);
					unset($data['tax']);
				}

				// MANUFACTURER
				if (isset($data['manufacturer'])) {
					$data['manufacturer_id'] = PhocacartUtils::getIntFromString($data['manufacturer']);
					unset($data['manufacturer']);
				}

				// CATEGORY
				if (!empty($data['categories']['category'])) {

					if (isset($data['categories']['category']['id']) && $data['categories']['category']['ordering']) {

						$idC = (int)PhocacartUtils::getIntFromString($data['categories']['category']['id']);
						$data['catid_multiple'][0] 				= (int)PhocacartUtils::getIntFromString($idC);
						$data['catid_multiple_ordering'][$idC]	= (int)$data['categories']['category']['ordering'];

					} else if (is_array($data['categories']['category'])) {

						$categories = $data['categories']['category'];
						foreach($categories as $kC => $vC) {
							$idC = (int)PhocacartUtils::getIntFromString($vC['id']);
							$data['catid_multiple'][] 				= $idC;
							$data['catid_multiple_ordering'][$idC]	= $vC['ordering'];
						}

					} else {
						// skip
						continue;
					}
				}

				if(empty($data['catid_multiple'])) {
					// skip no category found
					continue;
				}

				// IMAGES
				if (!empty($data['images']['image'])) {

					if (is_array($data['images']['image'])) {
						$images 					= $data['images']['image'];
						$data['images'] 			= array();
						foreach($images as $kI => $vI) {
							$data['images'][]['image'] = $vI;
						}
					} else {
						$image 						= $data['images']['image'];
						$data['images'] 			= array();
						$data['images'][0]['image'] = $image;
					}

				} else {
					$data['images'] = array();
				}

				// ATTRIBUTES
				if (!empty($data['attributes']['attribute'])) {

					if (isset($data['attributes']['attribute']['id'])) {
						// ATTRIBUTES STRING
						$attribute 			= $data['attributes']['attribute'];
						$data['attributes'] = array();

						// OPTIONS
						if (isset($attribute['options']['option']['id'])) {
							// OPTION STRING
							$option = $attribute['options']['option'];
							unset($attribute['options']['option']);
							$attribute['options'][0] = $option;
						} else if (isset($attribute['options']['option']) && is_array($attribute['options']['option'])) {
							// OPTIONS ARRAY
							$attribute['options'] = $attribute['options']['option'];
						}

						$data['attributes'][0]	= $attribute;


					} else if (is_array($data['attributes']['attribute'])) {
						// ATTRIBUTES ARRAY
						$attributes 			= $data['attributes']['attribute'];
						$data['attributes'] 	= array();

						// OPTIONS

						foreach($attributes as $kA => $vA) {
							if (isset($vA['options']['option']['id'])) {
								// OPTION STRING
								$option = $vA['options']['option'];
								unset($attributes[$kA]['options']['option']);
								$attributes[$kA]['options'][0] = $option;
							} else if (isset($vA['options']['option']) && is_array($vA['options']['option'])) {
								// OPTIONS ARRAY
								$attributes[$kA]['options'] = $vA['options']['option'];
							}
						}
						$data['attributes']		= $attributes;

					}


				} else {
					$data['attributes'] = array();
				}

				// SPECIFICATIONS
				if (!empty($data['specifications']['specification'])) {

					if (isset($data['specifications']['specification']['id'])) {
						// SPECIFICATIONS STRING
						$specification 				= $data['specifications']['specification'];
						$data['specifications'] 	= array();
						$data['specifications'][0]	= $specification;
					} else if (is_array($data['specifications']['specification'])) {
						// SPECIFICATIONS ARRAY
						$specifications 			= $data['specifications']['specification'];
						$data['specifications'] 	= array();
						$data['specifications']		= $specifications;

					}
				} else {
					$data['specifications'] = array();
				}

				// ADVANCED STOCK OPTIONS
				if (!empty($data['advanced_stock_options']['advanced_stock_option'])) {

					if (isset($data['advanced_stock_options']['advanced_stock_option']['id'])) {
						// ADVANCED STOCK OPTIONS
						$aso 								= $data['advanced_stock_options']['advanced_stock_option'];
						$data['advanced_stock_options'] 	= array();
						$data['advanced_stock_options'][0]	= $aso;
					} else if (is_array($data['advanced_stock_options']['advanced_stock_option'])) {
						// ADVANCED STOCK OPTIONS
						$asos 								= $data['advanced_stock_options']['advanced_stock_option'];
						$data['advanced_stock_options'] 	= array();
						$data['advanced_stock_options']		= $asos;

					}
				} else {
					$data['advanced_stock_options'] = array();
				}

				// DISCOUNTS
				if (!empty($data['discounts']['discount'])) {

					if (isset($data['discounts']['discount']['id'])) {
						// DISCOUNTS STRING
						$discount 				= $data['discounts']['discount'];
						$data['discounts'] 	= array();
						$data['discounts'][0]	= $discount;
					} else if (is_array($data['discounts']['discount'])) {
						// DISCOUNTS ARRAY
						$discounts 			= $data['discounts']['discount'];
						$data['discounts'] 	= array();
						$data['discounts']	= $discounts;

					}
				} else {
					$data['discounts'] = array();
				}

				// GROUPS IN DISCOUNT
				if (!empty($data['discounts'])) {
					foreach ($data['discounts'] as $kD => $vD) {

						if (!empty($data['discounts'][$kD]['groups']['group'])) {

							if (is_array($data['discounts'][$kD]['groups']['group']) && isset($data['discounts'][$kD]['groups']['group'][0])) {

								$groups 						= $data['discounts'][$kD]['groups']['group'];
								$data['discounts'][$kD]['groups'] 	= array();

								foreach($groups as $kG => $vG) {
									if (isset($vG['id'])) {
										$data['discounts'][$kD]['groups'][] = (int)PhocacartUtils::getIntFromString($vG['id']);
									}
								}
							} else {
								$group 						= $data['discounts'][$kD]['groups']['group'];

								$data['discounts'][$kD]['groups'] 			= array();
								$data['discounts'][$kD]['groups'][0] 		= (int)PhocacartUtils::getIntFromString($group['id']);
							}

						} else {
							$data['discounts'][$kD]['groups'] = array();
						}
					}
				}


				// GROUPS (USER)
				if (!empty($data['groups']['group'])) {

					if (is_array($data['groups']['group']) && isset($data['groups']['group'][0])) {
						$groups 					= $data['groups']['group'];
						$data['groups'] 			= array();

						foreach($groups as $kG => $vG) {

							if (isset($vG['id'])) {
								$data['groups'][] = (int)PhocacartUtils::getIntFromString($vG['id']);

							}
						}

					} else {
						$group 						= $data['groups']['group'];

						$data['groups'] 			= array();
						$data['groups'][0] 			= (int)PhocacartUtils::getIntFromString($group['id']);
					}

				} else {
					$data['groups'] = array();
				}


				// PRICE GROUPS
				if (!empty($data['price_groups']['price_group'])) {

					if (is_array($data['price_groups']['price_group']) && isset($data['price_groups']['price_group'][0])) {

						$groups 					= $data['price_groups']['price_group'];
						$data['price_groups'] 		= array();
						foreach($groups as $kG => $vG) {

							$data['price_groups'][$kG]['id'] 			= (int)PhocacartUtils::getIntFromString($vG['id']);
							$data['price_groups'][$kG]['product_id'] 	= (int)PhocacartUtils::getIntFromString($vG['product_id']);
							$data['price_groups'][$kG]['group_id'] 		= (int)PhocacartUtils::getIntFromString($vG['group_id']);
							$data['price_groups'][$kG]['price'] 		= $vG['price'];
						}
					} else {
						$group 						= $data['price_groups']['price_group'];

						$data['price_groups'] 			= array();
						$data['price_groups'][0]['id'] 			= (int)PhocacartUtils::getIntFromString($group['id']);
						$data['price_groups'][0]['product_id'] 	= (int)PhocacartUtils::getIntFromString($group['product_id']);
						$data['price_groups'][0]['group_id'] 	= (int)PhocacartUtils::getIntFromString($group['group_id']);
						$data['price_groups'][0]['price'] 		= $group['price'];
					}


				} else {
					$data['price_groups'] = array();
				}

				// POINT GROUPS
				if (!empty($data['point_groups']['point_group'])) {

					if (is_array($data['point_groups']['point_group']) && isset($data['point_groups']['point_group'][0])) {
						$groups 					= $data['point_groups']['point_group'];
						$data['point_groups'] 		= array();
						foreach($groups as $kG => $vG) {
							$data['point_groups'][$kG]['id'] 			= (int)PhocacartUtils::getIntFromString($vG['id']);
							$data['point_groups'][$kG]['product_id'] 	= (int)PhocacartUtils::getIntFromString($vG['product_id']);
							$data['point_groups'][$kG]['group_id'] 		= (int)PhocacartUtils::getIntFromString($vG['group_id']);
							$data['point_groups'][$kG]['points_received'] = $vG['points_received'];
						}
					} else {
						$group 						= $data['point_groups']['point_group'];

						$data['point_groups'] 			= array();
						$data['point_groups'][0]['id'] 			= (int)PhocacartUtils::getIntFromString($group['id']);
						$data['point_groups'][0]['product_id'] 	= (int)PhocacartUtils::getIntFromString($group['product_id']);
						$data['point_groups'][0]['group_id'] 	= (int)PhocacartUtils::getIntFromString($group['group_id']);
						$data['point_groups'][0]['points_received'] = $group['points_received'];
					}

				} else {
					$data['point_groups'] = array();
				}


				// PRICE HISTORIES
				if (!empty($data['price_histories']['price_history'])) {

					if (is_array($data['price_histories']['price_history']) && isset($data['price_histories']['price_history'][0])) {

						$histories 					= $data['price_histories']['price_history'];
						$data['price_histories'] 		= array();
						foreach($histories as $kG => $vG) {

							$data['price_histories'][$kG]['id'] 			= (int)PhocacartUtils::getIntFromString($vG['id']);
							$data['price_histories'][$kG]['product_id'] 	= (int)PhocacartUtils::getIntFromString($vG['product_id']);
							$data['price_histories'][$kG]['date'] 			= $vG['date'];
							$data['price_histories'][$kG]['price'] 			= $vG['price'];
						}
					} else {
						$group 						= $data['price_histories']['price_history'];

						$data['price_histories'] 			= array();
						$data['price_histories'][0]['id'] 			= (int)PhocacartUtils::getIntFromString($group['id']);
						$data['price_histories'][0]['product_id'] 	= (int)PhocacartUtils::getIntFromString($group['product_id']);
						$data['price_histories'][0]['date'] 		= $group['date'];
						$data['price_histories'][0]['price'] 		= $group['price'];
					}


				} else {
					$data['price_histories'] = array();
				}


				// RELATED
				if (!empty($data['related']['related_product'])) {

					if (is_array($data['related']['related_product']) && isset($data['related']['related_product'][0])) {
						$relateds 					= $data['related']['related_product'];
						$data['related'] 			= array();
						foreach($relateds as $kR => $vR) {
							$data['related'][] = (int)PhocacartUtils::getIntFromString($vR);
						}
					} else {
						$related 			= $data['related']['related_product'];
						$data['related'] 	= array();
						$data['related'][0] = (int)PhocacartUtils::getIntFromString($related);
					}

				} else {
					$data['related'] = array();
				}

				$data['related'] = implode(',', $data['related']);



				// TAG
				if (!empty($data['tags']['tag'])) {

					if (isset($data['tags']['tag']['id'])) {
						// TAG STRING
						$tag 				= $data['tags']['tag'];
						$data['tags'] 		= array();
						$data['tags'][0]	= PhocacartUtils::getIntFromString($tag);
					} else if (is_array($data['tags']['tag'])) {
						// TAGS ARRAY
						$tags = $data['tags']['tag'];
						$data['tags'] 	= array();
						foreach($tags as $kT => $vT) {
							$vT = (int)PhocacartUtils::getIntFromString($vT);
							$data['tags'][] 				= $vT;
						}
					}
				} else {
					$data['tags'] = array();
				}

				// LABELS
				if (!empty($data['taglabels']['label'])) {

					if (isset($data['taglabels']['label']['id'])) {
						// TAG STRING
						$tag 				    = $data['taglabels']['label'];
						$data['taglabels'] 		= array();
						$data['taglabels'][0]	= PhocacartUtils::getIntFromString($tag);
					} else if (is_array($data['taglabels']['label'])) {
						// TAGS ARRAY
						$tags = $data['taglabels']['label'];
						$data['taglabels'] 	= array();
						foreach($tags as $kT => $vT) {
							$vT = (int)PhocacartUtils::getIntFromString($vT);
							$data['taglabels'][] 				= $vT;
						}
					}
				} else {
					$data['taglabels'] = array();
				}

				// ADDITIONAL DOWNLOAD FILES
				if (!empty($data['additional_download_files']['additional_download_file'])) {

					if (isset($data['additional_download_files']['additional_download_file']['id'])) {
						// ADDITIONAL DOWNLOAD FILES STRING
						$files 				= $data['additional_download_files']['additional_download_file'];
						$data['additional_download_files'] 	= array();
						$data['additional_download_files'][0]	= $files;
					} else if (is_array($data['additional_download_files']['additional_download_file'])) {
						// ADDITIONAL DOWNLOAD FILES ARRAY
						$files 			= $data['additional_download_files']['additional_download_file'];
						$data['additional_download_files'] 	= array();
						$data['additional_download_files']		= $files;

					}
				} else {
					$data['additional_download_files'] = array();
				}

				// PARAMETERS

                if (isset($data['items_parameter']['parameter']['id'])) {
                    // PARAMETER STRING
                    $parameters = $data['items_parameter']['parameter'];

                    $data['items_parameter'] = array();
                    $data['items_parameter']['parameter'][0] = $parameters;
                }

                if (!empty($data['items_parameter']['parameter'])) {

                    $parameters = $data['items_parameter']['parameter'];
                    $data['items_parameter'] = array();
                    foreach($parameters as $kP => $vP) {

                        if (isset($vP['id']) && (int)$vP['id'] > 0) {
                            $vPId = (int)$vP['id'];
                            if(!empty($vP['values']['value'])) {

                                if (isset($vP['values']['value']['id'])) {
                                    // VALUES STRING
                                    $data['items_parameter'][$vPId][] = (int)PhocacartUtils::getIntFromString($vP['values']['value']['id']);
                                } else if (is_array($vP['values']['value'])) {
                                    // ADDITIONAL DOWNLOAD FILES ARRAY
                                    foreach ($vP['values']['value'] as $kPV => $vPV) {
                                        if (isset($vPV['id']) && (int)$vPV['id'] > 0) {
                                            $data['items_parameter'][$vPId][] = (int)PhocacartUtils::getIntFromString($vPV['id']);
                                        }
                                    }
                                }
                            }
                        }
                    }
				} else {
					$data['items_parameter'] = array();
				}


				// correct simple xml
				foreach($data as $k2 => $v2) {

					if (!isset($v2)) {
						$data[$k2] = '';
					} else if (is_array($v2) && empty($v2)) {
						$data[$k2] = '';
					}
				}
				$a[] = $data;
				$newId = PhocacartProduct::storeProduct($data, $import_column);
				if ($newId > 0) {
					$productIdChange[$newId] = $data['id'];
				}
			}
		}

		PhocacartRelated::correctProductId($productIdChange);// needed for related when new IDs are created by auto increment
	}
} else {

	// --------
	// CSV
	if (isset($d['productcolumns'][0]['item']) && ($d['productcolumns'][0]['item'] != '')) {
		$pcAH = str_getcsv($d['productcolumns'][0]['item'], ';', '"');

		if (!empty($d['products'])){
			foreach($d['products'] as $k => $v) {

				if (isset($v['item']) && $v['item'] != '') {
					$pcAP = str_getcsv($v['item'], ';', '"');


					if (!empty($pcAH) && !empty($pcAP)) {

						$cPcAH = count($pcAH);
						$cPcAP = count($pcAP);
						if ($cPcAH > $cPcAP) {
							$pcAP = array_pad($pcAP, $cPcAH, '');
						} else if ($cPcAH < $cPcAP) {
							$pcAH = array_pad($pcAH, $cPcAP, '');
						}


						$data = array_combine($pcAH, $pcAP);

						$data['tax_id'] 				= array();
						$data['manufacturer_id'] 		= array();
						$data['catid_multiple']			= array();
						$data['catid_multiple_ordering']= array();



						// No title - skip
						if (isset($data['title']) && $data['title'] == '') {
							continue;
						}

						// Specific rules
						if (isset($data['tax'])) {
							$data['tax_id'] = PhocacartUtils::getIntFromString($data['tax']);
							unset($data['tax']);
						}

						if (isset($data['manufacturer'])) {
							$data['manufacturer_id'] = PhocacartUtils::getIntFromString($data['manufacturer']);
							unset($data['manufacturer']);
						}

						if (isset($data['categories'])) {


							$categories = json_decode($data['categories'], true);

							if (!empty($categories)) {

								foreach($categories as $kC => $vC) {
									$idC = (int)PhocacartUtils::getIntFromString($vC['id']);
									$data['catid_multiple'][] 				= $idC;
									$data['catid_multiple_ordering'][$idC]	= $vC['ordering'];
								}
							} else {
								// No categories - skip
								continue;
							}

							unset($data['categories']);
						}

						if (isset($data['images'])) {
							$images = array();
							if ($data['images'] != '') {
								$images = explode("|", $data['images']);
							}

							$data['images'] = array();
							if (!empty($images)) {
								foreach($images as $kI => $vI) {
									$data['images'][]['image'] = $vI;
								}
							}
						}

						if (isset($data['attributes'])) {
							$data['attributes'] = json_decode($data['attributes'], true);
						}

						if (isset($data['specifications'])) {
							$data['specifications'] = json_decode($data['specifications'], true);
						}
						if (isset($data['advanced_stock_options'])) {
							$data['advanced_stock_options'] = json_decode($data['advanced_stock_options'], true);
						}


						// DISCOUNT GROUPS
						if (isset($data['discounts'])) {
							$data['discounts'] = json_decode($data['discounts'], true);


							$groupsD = array();
							if (!empty($data['discounts'])) {
								foreach($data['discounts'] as $kDG => $vDG) {
									$groupsD = $vDG['groups'];

									if (!empty($groupsD)) {
										foreach($groupsD as $kR => $vR) {
											$groupsD[$kR] = PhocacartUtils::getIntFromString($vR['id']);
										}
									}
									$data['discounts'][$kDG]['groups'] = $groupsD;

								}

							}

						}

						if (isset($data['groups'])) {
							$data['groups'] = json_decode($data['groups'], true);
							$groups = array();
							$groups = $data['groups'];

							if (!empty($groups)) {
								foreach($groups as $kR => $vR) {
									$groups[$kR] = PhocacartUtils::getIntFromString($vR['id']);
								}
							}
							$data['groups'] = $groups;
						}

						// Price Groups
						if (isset($data['price_groups'])) {
							$data['price_groups'] = json_decode($data['price_groups'], true);
							/*$groups = array();
							$groups = $data['price_groups'];

							if (!empty($groups)) {
								foreach($groups as $kR => $vR) {
									$groups[$kR] = PhocacartUtils::getIntFromString($vR['id']);
								}
							}
							$data['price_groups'] = $groups;*/

						} else {
							$data['price_groups'] = array();
						}

						// Point Groups
						if (isset($data['point_groups'])) {
							$data['point_groups'] = json_decode($data['point_groups'], true);
							/*$groups = array();
							$groups = $data['point_groups'];

							if (!empty($groups)) {
								foreach($groups as $kR => $vR) {
									$groups[$kR] = PhocacartUtils::getIntFromString($vR['id']);
								}
							}
							$data['point_groups'] = $groups;*/
						} else {
							$data['point_groups'] = array();
						}

						// Price Groups
						if (isset($data['price_histories'])) {
							$data['price_histories'] = json_decode($data['price_histories'], true);
							/*$groups = array();
							$groups = $data['price_groups'];

							if (!empty($groups)) {
								foreach($groups as $kR => $vR) {
									$groups[$kR] = PhocacartUtils::getIntFromString($vR['id']);
								}
							}
							$data['price_groups'] = $groups;*/

						} else {
							$data['price_histories'] = array();
						}


						if (isset($data['related'])) {
							$related = array();
							if ($data['related'] != '') {
								$related = explode("|", $data['related']);
							}
							if (!empty($related)) {
								foreach($related as $kR => $vR) {
									$related[$kR] = PhocacartUtils::getIntFromString($vR);
								}
							}
							$data['related'] = implode(',', $related);

						}

						if (isset($data['tags'])) {

							$tags = array();
							if ($data['tags'] != '') {
								$tags = explode("|", $data['tags']);
							}
							if (!empty($tags)) {
								foreach($tags as $kT => $vT) {
									$tags[$kT] = PhocacartUtils::getIntFromString($vT);
								}
							}
							$data['tags'] = $tags;

						}

						if (isset($data['taglabels'])) {

							$tags = array();
							if ($data['taglabels'] != '') {
								$tags = explode("|", $data['taglabels']);
							}
							if (!empty($tags)) {
								foreach($tags as $kT => $vT) {
									$tags[$kT] = PhocacartUtils::getIntFromString($vT);
								}
							}
							$data['taglabels'] = $tags;

						}

						if (isset($data['items_parameter'])) {

						    $data['items_parameter'] = json_decode($data['items_parameter'], true);

                            $itemParameters = array();
                            if (!empty($data['items_parameter'])) {
                                foreach($data['items_parameter'] as $kT => $vT) {
                                    if (!empty($vT)) {
                                        foreach($vT as $kT2 => $vT2) {
                                            if (isset($vT2['id']) && (int)$vT2['id'] > 0) {
                                                $itemParameters[$kT][$kT2] = (int)$vT2['id'];
                                            }
                                        }
                                    }
                                }
                            }
                            $data['items_parameter'] = $itemParameters;
                        }

						if (isset($data['additional_download_files'])) {
							$data['additional_download_files'] = json_decode($data['additional_download_files'], true);
						}

						$newId = PhocacartProduct::storeProduct($data, $import_column);
						if ($newId > 0) {
							$productIdChange[$newId] = $data['id'];
						}
					}
				}
			}

			PhocacartRelated::correctProductId($productIdChange);// needed for related when new IDs are created by auto increment
		}
	}
}
