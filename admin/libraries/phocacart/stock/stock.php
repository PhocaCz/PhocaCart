<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class PhocacartStock
{
	public static function getStockStatusData($stockStatusId, $available = 1) {



		$db = Factory::getDBO();

		if ($available == 1) {
			$statusMethod = 'p.stockstatus_a_id';// Status when product is in stock A(P > 0), or stock is not checked
		} else {
			$statusMethod = 'p.stockstatus_n_id';// Status when product is not in stock N(P = 0)
		}

		$columns		= 's.id, s.title, s.title_feed, s.image';
		$groupsFull		= $columns;
		$groupsFast		= 's.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

		$query = 'SELECT s.id, s.title, s.title_feed, s.image, s.link, s.link_target'
				.' FROM #__phocacart_stock_statuses AS s'
				.' LEFT JOIN #__phocacart_products AS p ON s.id = '.$statusMethod
			    .' WHERE s.id = '.(int) $stockStatusId
				.' GROUP BY '.$groups
				.' ORDER BY s.id';
		$db->setQuery($query);
		$data = $db->loadObjectList();


		return $data;
	}

	public static function getStockStatus($stockCount, $minQuantity, $minMultipleQuantity, $stockStatusIdA, $stockStatusIdN) {

		// A > 0 OR Not checking
		// N = 0
		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$stock_checking			    = $paramsC->get( 'stock_checking', 0 );
		$display_stock_status	    = $paramsC->get( 'display_stock_status', 1 );
		$stock_checkout			    = $paramsC->get( 'stock_checkout', 0 );
        $stock_status_display_count	= $paramsC->get( 'stock_status_display_count', 0 );

		$stock 	= array();

		/*
		if($stockStatusIdN > 0) {
			$dataB = self::getStockStatusData($stockStatusId);
		}*/

		$stock['stock_count'] 	        = false;
		$stock['stock_status'] 	        = false;
        $stock['stock_status_class'] 	= false;
		$stock['status_image'] 	        = false;
        $stock['status_link'] 	        = false;
        $stock['status_link_target'] 	= false;
        $stock['status_display_count'] 	= (int)$stock_status_display_count;

		$stock['stock_status_feed'] = false; // Additional status text for feeds only - it is managed by $stock['stock_status']

		// we differentiate between views: Category, Items, Item view but this happens in view - not here
		// so in views we decide if we will ask this function
		// Example we select that stock status will be not displayed in category (items) view but only in item view $display_stock_status = 1
		// in category(items) view we have the condition so we never ask this function from this view, this is why we don't need to
		// handle different values for $display_stock_status
		// $display_stock_status = 1 ... item view
		// $display_stock_status = 2 ... category (items) view
		// $display_stock_status = 3 ... item and category (items) view
        // $display_stock_status = 3 ... item and category (items) view
		if ($display_stock_status > 0) {
			if ($stock_checking == 1) {
				if ((int)$stockCount > 0) {
					// 1 There is product in stock, display status - if set
					if($stockStatusIdA > 0) {
						$data = self::getStockStatusData($stockStatusIdA, 1);
						if (!empty($data) && $data[0]->title != '') {
							$stock['stock_status'] 		= Text::_($data[0]->title);
							$stock['stock_status_feed'] = Text::_($data[0]->title_feed);
                            $stock['stock_status_class'] = 'pc-status-'.PhocacartText::filterValue($stock['stock_status'], 'class');
						}
						if (!empty($data) && $data[0]->image != '') {
							$stock['status_image'] = $data[0]->image;
						}
                        if (!empty($data) && $data[0]->link != '') {
                            $stock['status_link'] = $data[0]->link;
                        }
                        if (!empty($data) && $data[0]->link_target != '') {
                            $stock['status_link_target'] = $data[0]->link_target;
                        }
					}
					$stock['stock_count'] = $stockCount;
				} else {
					// 2 There is no product in stock, display status - if set
					if($stockStatusIdN > 0) {
						$data = self::getStockStatusData($stockStatusIdN, 0);
						if (!empty($data) && $data[0]->title != '') {
							$stock['stock_status'] 		= Text::_($data[0]->title);
							$stock['stock_status_feed'] = Text::_($data[0]->title_feed);
                            $stock['stock_status_class'] = 'pc-status-'.PhocacartText::filterValue($stock['stock_status'], 'class');
						}
						if (!empty($data) && $data[0]->image != '') {
							$stock['status_image'] = $data[0]->image;
						}
                        if (!empty($data) && $data[0]->link != '') {
                            $stock['status_link'] = $data[0]->link;
                        }
                        if (!empty($data) && $data[0]->link_target != '') {
                            $stock['status_link_target'] = $data[0]->link_target;
                        }
					}
					$stock['stock_count'] = 0;
				}
			} else {
				// 3 No stock checking we don't care about count of products but we want to display status
				if($stockStatusIdA > 0) {
					$data = self::getStockStatusData($stockStatusIdA, 1);
					if (!empty($data) && $data[0]->title != '') {
						$stock['stock_status'] 		= Text::_($data[0]->title);
						$stock['stock_status_feed'] = Text::_($data[0]->title_feed);
                        $stock['stock_status_class'] = 'pc-status-'.PhocacartText::filterValue($stock['stock_status'], 'class');
					}
					if (!empty($data) && $data[0]->image != '') {
						$stock['status_image'] = $data[0]->image;
					}
                    if (!empty($data) && $data[0]->link != '') {
                        $stock['status_link'] = $data[0]->link;
                    }
                    if (!empty($data) && $data[0]->link_target != '') {
                        $stock['status_link_target'] = $data[0]->link_target;
                    }
				}
				// Stock count is set to false
			}

		}

		$stock['min_quantity'] = false;
		if ($minQuantity > 0) {
			$stock['min_quantity'] = $minQuantity;
		}

		$stock['min_multiple_quantity'] = false;
		if ($minMultipleQuantity > 0) {
			$stock['min_multiple_quantity'] = $minMultipleQuantity;
		}

		return $stock;
	}

	public static function getStockStatusOutput($stockStatus) {
		$o = '';
		$s = PhocacartRenderStyle::getStyles();

        // LINK
        if ($stockStatus['status_link'] != '') {
            $o .= '<a href="'.$stockStatus['status_link'].'"';

            if ($stockStatus['status_link_target'] != '') {
                $o .= ' target="'.$stockStatus['status_link_target'].'"';
            }

            $o .= ' >';
        }


		if ($stockStatus['stock_status'] && $stockStatus['stock_count'] !== false && $stockStatus['status_display_count'] == 1) {
			$o .= $stockStatus['stock_status'] . ' ('.$stockStatus['stock_count'].')';
		} else if (!$stockStatus['stock_status'] && $stockStatus['stock_count'] !== false) {
			$o .= $stockStatus['stock_count'];
		} else if ($stockStatus['stock_status'] /* && $stockStatus['status_display_count'] == 0*/) {
			$o .= $stockStatus['stock_status'];
		}

		if ($stockStatus['status_image']) {
			$o .= '<img src="'.Uri::base(true).'/'.$stockStatus['status_image'].'" alt="" class="'.$s['c']['img-responsive'].' ph-image" />';
		}

		// LINK
        if ($stockStatus['status_link'] != '') {
            $o .= '</a>';
        }

		return $o;
	}


	/* Handling of stock */
	public static function handleStockProduct($productId, $orderStatusId, $quantity, $stockMovement = '') {

		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$negative_stocks	= $paramsC->get( 'negative_stocks', 1 );

		// We know the stock movement, ignore the status
		if ($stockMovement == '+' || $stockMovement == '-') {
			$status = array();
			$status['stock_movements'] = $stockMovement;
		} else {
			$status = PhocacartOrderStatus::getStatus((int)$orderStatusId);
		}

		if (isset($status['stock_movements']) && $status['stock_movements'] == '+') {
			$db = Factory::getDBO();
			$query = 'UPDATE #__phocacart_products SET stock = stock + '.(int)$quantity.' WHERE id = '.(int)$productId;
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = Factory::getDBO();

			if ($negative_stocks == 0) {
				// we cannot have negative values in stock
				$query = 'UPDATE #__phocacart_products SET stock = GREATEST(0, stock - '.(int)$quantity.') WHERE id = '.(int)$productId;
			} else {
				$query = 'UPDATE #__phocacart_products SET stock = stock - '.(int)$quantity.' WHERE id = '.(int)$productId;
			}


			$db->setQuery($query);
			$db->execute();
		}
		return true;
	}

	public static function handleStockAttributeOption($optionId, $orderStatusId, $quantity, $stockMovement = '') {

		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$negative_stocks		= $paramsC->get( 'negative_stocks', 1 );

		// We know the stock movement, ignore the status
		if ($stockMovement == '+' || $stockMovement == '-') {
			$status = array();
			$status['stock_movements'] = $stockMovement;
		} else {
			$status = PhocacartOrderStatus::getStatus((int)$orderStatusId);
		}

		if (isset($status['stock_movements']) && $status['stock_movements'] == '+') {
			$db = Factory::getDBO();
			$query = 'UPDATE #__phocacart_attribute_values SET stock = stock + '.(int)$quantity.' WHERE id = '.(int)$optionId;
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = Factory::getDBO();

			if ($negative_stocks == 0) {
				// we cannot have negative values in stock
				$query = 'UPDATE #__phocacart_attribute_values SET stock = GREATEST(0, stock - '.(int)$quantity.') WHERE id = '.(int)$optionId;
			} else {
				$query = 'UPDATE #__phocacart_attribute_values SET stock = stock - '.(int)$quantity.' WHERE id = '.(int)$optionId;
			}

			$db->setQuery($query);
			$db->execute();
		}
		return true;
	}

	public static function handleStockProductKey($productKey, $orderStatusId, $quantity, $stockMovement = '') {

		$app				= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$negative_stocks	= $paramsC->get( 'negative_stocks', 1 );

		// We know the stock movement, ignore the status
		if ($stockMovement == '+' || $stockMovement == '-') {
			$status = array();
			$status['stock_movements'] = $stockMovement;
		} else {
			$status = PhocacartOrderStatus::getStatus((int)$orderStatusId);
		}

		if (isset($status['stock_movements']) && $status['stock_movements'] == '+') {
			$db = Factory::getDBO();
			$query = 'UPDATE #__phocacart_product_stock SET stock = stock + '.(int)$quantity.' WHERE product_key = '.$db->quote($productKey);
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = Factory::getDBO();

			if ($negative_stocks == 0) {
				// we cannot have negative values in stock
				$query = 'UPDATE #__phocacart_product_stock SET stock = GREATEST(0, stock - '.(int)$quantity.') WHERE product_key = '.$db->quote($productKey);
			} else {
				$query = 'UPDATE #__phocacart_product_stock SET stock = stock - '.(int)$quantity.' WHERE product_key = '.$db->quote($productKey);
			}


			$db->setQuery($query);
			$db->execute();
		}
		return true;
	}


	/*
	 * $selectedAttributes ... ajax, checkout, checkout json - attributes are not all attributes assigned to product but only the one selected by e.g. select box
	 * (including the option that no attribute was selected - in this case the main product is even a variant)
	 */



	public static function getStockItemsChangedByAttributes(&$stockStatus, $attributes, $item, $selectedAttributes = 0) {

		//$paramsC 			= PhocacartUtils::getComponentParameters();
		//$display_unit_price	= $paramsC->get( 'display_unit_price', 1 );

		$stock				= 0;// main stock count - rendered output of stock item (by product, attribute or mix of attributes ASM)
		$stockProduct		= isset($item->stock) ? $item->stock : 0;// stock stored by product
		$stockAttribute		= 0;// stock stored by each attribute

		$fullAttributes		= array();// Array of full objects (full options object)
		$thinAttributes		= array();// Array of integers only
		if ($selectedAttributes == 2) {
		    // Final order, we get the attributes in different format as by adding them to cart
            // it is final order, we order the items so we don't check e.g. if default values or not - the values are selected yet
            $fullAttributes = PhocacartAttribute::getAttributeFullValues($attributes);
			$thinAttributes	= PhocacartAttribute::getAttributesSanitizeOptionArray($attributes);//select only default v a to create product key
        } else if ($selectedAttributes == 1) {
			$fullAttributes = PhocacartAttribute::getAttributeFullValues($attributes);
			$thinAttributes	= $attributes;//select only default value attributes (selected options) to create product key
		} else {
			$fullAttributes = $attributes;
			$thinAttributes = PhocacartAttribute::getAttributesSelectedOnly($attributes);//select only default v a to create product key
		}





		// Stock Calculation
		// 0 ... Main Product
		// 1 ... Product Variations
		// 2 ... Advanced Stock Management
        // 3 ... Advanced Stock And Price Management ( 2 + price)


		if ($item->stock_calculation == 1) {

			// Product Variations - Be aware can be wrong count of stock when mixing attributes - works only one attribute
			$i = 0;

			if (!empty($fullAttributes)) {

			    // Is some attribute with its option active (selected). If not, then the main product variant should be set
			    $productAttributeSelected = 0;

				foreach ($fullAttributes as $k => $v) {

					$attributeSelected	= 0;
					$stockAttribute		= 0;
					if (!empty($v->options)) {
						$i++;
						foreach($v->options as $k2 => $v2) {

							// Is the options set as default
							// See: administrator\components\com_phocacart\libraries\phocacart\price\price.php
							// function getPriceItemsChangedByAttributes - similar behaviour
							if ($selectedAttributes == 1 || $selectedAttributes == 2 || ($selectedAttributes == 0 && isset($v2->default_value) && $v2->default_value == 1)) {
								$attributeSelected	= 1;
                                $productAttributeSelected = 1;


								if (isset($v2->stock) && $v2->stock > 0) {
									$stockAttribute += (int)$v2->stock;
								}
							}
						}

					}
					if ($attributeSelected == 1) {
						$stock += $stockAttribute;

					} else {

					    // we iterate over attributes not products
                        // so this is the variant for main product


                        // IF there is more than one parameter, this will be always wrong here because
                        // 1) first attribute not selected, second selected: should we add only second or second plus main product stock?
                        // Product stock 100
                        // Product attribute A stock 50
                        // Product attribute B stock 50
                        // first attribute not selected = 0, second selected = 50 (we skip to add the main product 100)
                        // 2) first not selected, second not selected: ok we add only the main product stock
                        // both have 0 but we select main product which is 100

                        // if we should add main product then:
                        // $stock += $stockProduct;
					}
				}

				if ($productAttributeSelected == 0) {
				    // We have attributes and their options
                    // but no one set as default or selected
                    $stock += $stockProduct;
                }



			} else {
			    if ($selectedAttributes == 1) {
                    // If there are no attributes, the main product is the varaint itself
                    $stock+= $item->stock;
                }
            }

			if ($i > 1 && $selectedAttributes != 1) {
				PhocacartLog::add(3, 'Warning', $item->id, Text::_('COM_PHOCACART_INAPPROPRIATE_METHOD_STOCK_CALCULATION_PRODUCT_VARIATIONS') . ' ' . Text::_('COM_PHOCACART_PRODUCT'). ': ' . $item->title );
			}


		} else if ($item->stock_calculation == 2 || $item->stock_calculation == 3) {

			// Advanced Stock Management
			$k		= PhocacartProduct::getProductKey((int)$item->id, $thinAttributes);
			$stock	= PhocacartAttribute::getCombinationsStockByKey($k);

		} else {
			// Main Product
			$stock = $item->stock;
		}



		// Get all stock status information: count, status, image, ...
		$stockStatus		= PhocacartStock::getStockStatus((int)$stock, (int)$item->min_quantity, (int)$item->min_multiple_quantity, (int)$item->stockstatus_a_id,  (int)$item->stockstatus_n_id);

		return $stock;
	}

}
