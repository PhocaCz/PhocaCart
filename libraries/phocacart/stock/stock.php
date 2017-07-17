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

class PhocacartStock
{
	public static function getStockStatusData($stockStatusId, $available = 1) {
	
		$db = JFactory::getDBO();
		
		if ($available == 1) {
			$statusMethod = 'p.stockstatus_a_id';// Status when product is in stock A(P > 0), or stock is not checked
		} else {
			$statusMethod = 'p.stockstatus_n_id';// Status when product is not in stock N(P = 0)
		}
		
		$query = 'SELECT s.id, s.title, s.title_feed, s.image FROM #__phocacart_stock_statuses AS s'
				.' LEFT JOIN #__phocacart_products AS p ON s.id = '.$statusMethod
			    .' WHERE s.id = '.(int) $stockStatusId
				.' ORDER BY s.id';
		$db->setQuery($query);
		$data = $db->loadObjectList();
			
		
		return $data;
	}
	
	public static function getStockStatus($stockCount, $minQuantity, $minMultipleQuantity, $stockStatusIdA, $stockStatusIdN) {
		
		// A > 0 OR Not checking
		// N = 0
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$stock_checking			= $paramsC->get( 'stock_checking', 0 );
		$display_stock_status	= $paramsC->get( 'display_stock_status', 0 );
		$stock_checkout			= $paramsC->get( 'stock_checkout', 0 );
		
		$stock 	= array();

		/*
		if($stockStatusIdN > 0) {
			$dataB = self::getStockStatusData($stockStatusId);
		}*/
		
		$stock['stock_count'] 	= false;
		$stock['stock_status'] 	= false;
		$stock['status_image'] 	= false;
		
		$stock['stock_status_feed'] = false; // Additional status text for feeds only - it is managed by $stock['stock_status']
			
		if ($display_stock_status == 1) {
			if ($stock_checking == 1) {
				if ((int)$stockCount > 0) {
					// 1 There is product in stock, display status - if set
					if($stockStatusIdA > 0) {
						$data = self::getStockStatusData($stockStatusIdA, 1);
						if (!empty($data) && $data[0]->title != '') {
							$stock['stock_status'] 		= JText::_($data[0]->title);
							$stock['stock_status_feed'] = JText::_($data[0]->title_feed);
						}
						if (!empty($data) && $data[0]->image != '') {
							$stock['status_image'] = $data[0]->image;
						}
					}
					$stock['stock_count'] = $stockCount;
				} else {
					// 2 There is no product in stock, display status - if set
					if($stockStatusIdN > 0) {
						$data = self::getStockStatusData($stockStatusIdN, 0);
						if (!empty($data) && $data[0]->title != '') {
							$stock['stock_status'] 		= JText::_($data[0]->title);
							$stock['stock_status_feed'] = JText::_($data[0]->title_feed);
						}
						if (!empty($data) && $data[0]->image != '') {
							$stock['status_image'] = $data[0]->image;
						}
					}
					$stock['stock_count'] = 0;
				}
			} else {
				// 3 No stock checking we don't care about count of products but we want to display status
				if($stockStatusIdA > 0) {
					$data = self::getStockStatusData($stockStatusIdA, 1);
					if (!empty($data) && $data[0]->title != '') {
						$stock['stock_status'] 		= JText::_($data[0]->title);
						$stock['stock_status_feed'] = JText::_($data[0]->title_feed);
					}
					if (!empty($data) && $data[0]->image != '') {
						$stock['status_image'] = $data[0]->image;
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
		
		if ($stockStatus['stock_status'] && $stockStatus['stock_count']) {
			$o .= $stockStatus['stock_status'] . ' ('.$stockStatus['stock_count'].')';
		} else if (!$stockStatus['stock_status'] && $stockStatus['stock_count']) {
			$o .= $stockStatus['stock_count'];
		} else if ($stockStatus['stock_status'] && !$stockStatus['stock_count']) {
			$o .= $stockStatus['stock_status'];
		}
		
		if ($stockStatus['status_image']) {
			$o .= '<img src="'.JURI::base(true).'/'.$stockStatus['status_image'].'" alt="" class="img-responsive ph-image" />';
		}
		return $o;
	}
	
	
	/* Handling of stock */
	public static function handleStockProduct($productId, $orderStatusId, $quantity, $stockMovement = '') {
		
		$app				= JFactory::getApplication();
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
			$db = JFactory::getDBO();
			$query = 'UPDATE #__phocacart_products SET stock = stock + '.(int)$quantity.' WHERE id = '.(int)$productId;
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = JFactory::getDBO();
			
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

		$app			= JFactory::getApplication();
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
			$db = JFactory::getDBO();
			$query = 'UPDATE #__phocacart_attribute_values SET stock = stock + '.(int)$quantity.' WHERE id = '.(int)$optionId;
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = JFactory::getDBO();
			
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
		
		$app				= JFactory::getApplication();
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
			$db = JFactory::getDBO();
			$query = 'UPDATE #__phocacart_product_stock SET stock = stock + '.(int)$quantity.' WHERE product_key = '.$db->quote($productKey);
			$db->setQuery($query);
			$db->execute();
		} else if (isset($status['stock_movements']) && $status['stock_movements'] == '-') {
			$db = JFactory::getDBO();
			
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
}