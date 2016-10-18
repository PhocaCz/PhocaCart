<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartProduct
{

	public function __construct() {}
	public static function getProduct($productId, $prioritizeCatid = 0) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.id, c.id as catid, a.alias, a.title, a.sku, a.price, a.price_original, a.tax_id as taxid, a.image, a.weight, a.volume, a.unit_amount, a.unit_unit,'
				.' a.download_token, a.download_folder, a.download_file, a.download_hits, a.stock, a.min_quantity, a.min_multiple_quantity,'
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
				.' WHERE a.id = '.(int)$productId
				.' ORDER BY a.id'
				.' LIMIT 1';
		$db->setQuery($query);
		$product = $db->loadObject();
		
		// When we add the product, we can use the catid from where we are located
		// if we are in category A, then we try to add this product with category A
		// BUT the product can be displayed in cart e.g. 3x so only the last added catid
		// is used for creating the SEF URL
		// Using catid is only about SEF URL
		if ((int)$prioritizeCatid > 0) {
			if (isset($product->catid) && (int)$product->catid == (int)$prioritizeCatid) {
				//$product->catid is $product->catid
			} else {
				// Recheck the category id of product
				$checkCategory = false;
				if (isset($product->id)) {
					$checkCategory = PhocaCartProduct::checkIfAccessPossible((int)$product->id, (int)$prioritizeCatid);
				}
				
				if ($checkCategory) {
					$product->catid 	= (int)$prioritizeCatid;
				}
			}	
		}
		return $product;
	}
	
	/*
	 * Check if user has access to this product
	 * when adding to cart
	 * when ordering
	 * NOT USED when displaying, as no products are displayed which cannnot be accessed
	 * So this is security feature in case of forgery - server side checking
	 * STRICT RULES ARE VALID - if the product is included in 
	 */
	
	public static function checkIfAccessPossible($id, $catid) {
	
		if ((int)$id > 0) {
			
			$db 		= JFactory::getDBO();
			$wheres		= array();
			$user 		= JFactory::getUser();
			$userLevels	= implode (',', $user->getAuthorisedViewLevels());
			$wheres[] 	= " a.access IN (".$userLevels.")";
			$wheres[] 	= " c.access IN (".$userLevels.")";
			$wheres[] 	= " a.published = 1";
			$wheres[] 	= " c.published = 1";
			$wheres[] 	= ' a.id = '.(int)$id;
			$wheres[] 	= ' c.id = '.(int)$catid;
			
			
			//$wheres[] 	= ' c.id = '.(int)$catid;
			
			$query = ' SELECT a.id'
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
			//.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
			.' WHERE ' . implode( ' AND ', $wheres )
			.' ORDER BY a.id'
			.' LIMIT 1';
			$db->setQuery($query);
			$product = $db->loadObject();
			
			if (isset($product->id) && (int)$product->id > 0) {
				return true;
			} else {
				return false;// seems like attribute is required but not selected
			}
		}
		
		return false;
	
	}
	
	public static function getProductIdByOrder($orderId) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.id'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_order_products AS o ON o.product_id = a.id'
				.' WHERE o.id = '.(int)$orderId
				.' ORDER BY a.id'
				.' LIMIT 1';
		$db->setQuery($query);
		$product = $db->loadObject();
		return $product;
	}
	
	/*
	 * We don't need catid, we get all categories for this product listed from group_concat
	 */
	public static function getProductByProductId($id) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.id, a.title, group_concat(c.title SEPARATOR \' \') AS categories_title'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
				.' WHERE a.id = '.(int)$id
				.' GROUP BY a.id'
				.' ORDER BY a.id'
				.' LIMIT 1';
		$db->setQuery($query);
		$product = $db->loadObject();
		
		return $product;
	}
	
	/*
	* checkPublished = true - select only published products
	* checkPublished = false - select all (published|unpublished) products
	* PUBLISHED MEANS, THEY ARE REALLY PUBLISHED - they are published as products and their category is published too.
	*
	* checkStock - check Stock or not ( > 0 )
	* checkPrice - check if the product has price or not ( > 0 )
	*/
	
	public static function getProducts($limit = 1, $orderingItem = 1, $orderingCat = 0, $checkPublished = false, $checkStock = false, $checkPrice = false, $categoriesList = 0, $categoryIds = array()) {
	
		phocacartimport('phocacart.ordering.ordering');
		
		$ordering 	= PhocaCartOrdering::getOrderingCombination($orderingItem, $orderingCat);
	
	
		$db 			= JFactory::getDBO();
		$wheres			= array();
		$user 			= JFactory::getUser();
		$userLevels		= implode (',', $user->getAuthorisedViewLevels());
		$wheres[] 		= " a.access IN (".$userLevels.")";
		$wheres[] 		= " c.access IN (".$userLevels.")";
		
		if ($checkPublished) {
			$wheres[] 		= " a.published = 1";
			$wheres[] 		= " c.published = 1";
		}
		
		if ($checkStock) {
			$wheres[] 		= " a.stock > 0";
		}
		
		if ($checkPrice) {
			$wheres[] 		= " a.price > 0";
		}
		
		if (!empty($categoryIds)) {
			
			$catIdsS = implode (',', $categoryIds);
			$wheres[]	= 'pc.category_id IN ('.$catIdsS.')';
		}
		
		$q = ' SELECT a.id, a.title, a.image, a.video, a.alias, a.description, a.description_long, a.sku, a.stockstatus_a_id, a.stockstatus_n_id, a.min_quantity, a.min_multiple_quantity, a.stock, a.unit_amount, a.unit_unit, c.id AS catid, c.title AS cattitle, c.alias AS catalias, c.title_feed AS cattitlefeed, a.price, a.price_original, t.tax_rate AS taxrate, t.calculation_type AS taxcalculationtype, t.title AS taxtitle, a.date, a.sales, a.featured, a.external_id, m.title AS manufacturertitle,'
			. ' AVG(r.rating) AS rating,'
			. ' at.required AS attribute_required';
			
		if ($categoriesList == 1) {
			$q .= ', GROUP_CONCAT(c.id) AS categories';
		} else if ($categoriesList == 2) {
			$q .= ', GROUP_CONCAT(c.title SEPARATOR "|") AS categories';
		} else if ($categoriesList == 3) {
			$q .= ', GROUP_CONCAT(c.id, ":", c.alias SEPARATOR "|") AS categories';
		}
		
		$q .= ' FROM #__phocacart_products AS a'
			. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
			. ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
			. ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
			. ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0'
			. ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'
			. ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id';
			
			if ($ordering != '') {
				$q .= ' ORDER BY '.$ordering;
			}
			
			if ((int)$limit > 0) {
				$q .= ' LIMIT '.(int)$limit;
			}
		
		$db->setQuery($q);
		$products = $db->loadObjectList();
		
		return $products;
	}
	
	public static function getCategoryByProductId($id) {
		$db = JFactory::getDBO();
		$query = 'SELECT a.catid'
		. ' FROM #__phocacart_products AS a'
		. ' WHERE a.id = '.(int)$id
		. ' ORDER BY a.id'
		. ' LIMIT 1';
		$db->setQuery( $query );
		$category = $db->loadRow();
		
		if (isset($category[0]) && $category[0] > 0) {
			return $category[0];
		}
		return 0;
	}
	
	public static function getMostViewedProducts($limit = 5, $checkPublished = false, $checkAccess = false, $count = false) {
		
		$db 		= JFactory::getDBO();
		$wheres		= array();
		
		if ($checkPublished) {
			$user 			= JFactory::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$wheres[] 		= " a.access IN (".$userLevels.")";
		}
		if ($checkAccess) {
			$wheres[] 		= " a.published = 1";
		}
		
		$wheres[] 		= " a.hits > 0";
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		if ($count) {
			$q = 'SELECT SUM(a.hits)'
			. ' FROM #__phocacart_products AS a'
			. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
			. ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
			.  $where;
			if ((int)$limit > 0) {
				$q .=  ' LIMIT '.(int)$limit;
			}
			
			$db->setQuery( $q );
			$products = $db->loadResult();
		
		} else {
		
			$q = 'SELECT a.id, a.title, a.alias, a.hits, c.id as catid, c.alias as catalias, c.title as cattitle'
			. ' FROM #__phocacart_products AS a'
			. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
			. ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
			.  $where
			. ' GROUP BY a.id'
			. ' ORDER BY a.hits DESC';
			if ((int)$limit > 0) {
				$q .=  ' LIMIT '.(int)$limit;
			}
			
			$db->setQuery( $q );
			$products = $db->loadObjectList();
		}
		
		
		return $products;
	}
	
	public static function getBestSellingProducts($limit = 5, $dateFrom = '', $dateTo = '', $count = false) {
		
		$db 		= JFactory::getDBO();
		$wheres		= array();
		
		$wheres[] 	= " o.id > 0";
		
		if ($dateTo != '' && $dateFrom != '') {
			$dateFrom 	= $db->Quote($dateFrom);
			$dateTo 	= $db->Quote($dateTo);
			$wheres[] = ' DATE(od.date) >= '.$dateFrom.' AND DATE(od.date) <= '.$dateTo;
		}
		
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		if ($count) {
			$q =  ' SELECT count(o.id)'
			. ' FROM #__phocacart_order_products AS o'
			. ' LEFT JOIN #__phocacart_products AS a ON a.id = o.product_id';
			if ($dateTo != '' && $dateFrom != '') {
				$q .= ' LEFT JOIN #__phocacart_orders AS od ON od.id = o.order_id';
			}
			$q .= $where;
			if ((int)$limit > 0) {
				$q .=  ' LIMIT '.(int)$limit;
			}
			
			
			$db->setQuery($q);
			$products = $db->loadResult();
			
		} else {
			$q =  ' SELECT o.product_id AS id, o.title, o.alias, COUNT( o.id ) AS count_products'
			. ' FROM #__phocacart_order_products AS o';
			//. ' LEFT JOIN #__phocacart_products AS a ON a.id = o.product_id';
			if ($dateTo != '' && $dateFrom != '') {
				$q .= ' LEFT JOIN #__phocacart_orders AS od ON od.id = o.order_id';
			}
			$q .= $where
			. ' GROUP BY o.id'
			. ' ORDER BY count_products DESC';
			if ((int)$limit > 0) {
				$q .=  ' LIMIT '.(int)$limit;
			}
		
			$db->setQuery($q);
			$products = $db->loadObjectList();
		}
		
		
		
		/* For now we don't need SEF url, if SEF url is needed, we need to get category alias and category id
		 * This cannot be done in sql as then because of table jos_phocacart_product_categories will count count duplicities
		 */
		
		
		
		/*
		$productsA = array();
		if (!empty($products)) {
			foreach ($products as $k => $v) {
				if (isset($v->id)) {
					$productsA[] = (int)$v->id;
				}
			}
		}
		$productsS = '';
		if (!empty($productsA)) {
			$productsS = implode(',', $productsA);
		}
		
		$categories = array();
		if ($productsS != '') {
			$query = 'SELECT pc.product_id AS id, c.id AS catid, c.title AS cattitle, c.alias AS catalias'
			. ' FROM #__phocacart_categories AS c'
			. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id'
			. ' LEFT JOIN #__phocacart_products AS p ON p.id = pc.product_id'
			. ' WHERE pc.product_id IN ('.$productsS.')'
			. ' GROUP BY pc.product_id';
			$db->setQuery( $query );

			$categories = $db->loadObjectList();
			
		}
		if (!empty($categories) && !empty($products)) {
			foreach($products as $k => &$v) {
				foreach($categories as $k2 => $v2) {
					if (isset($v->id) && isset($v2->id) && (int)$v->id > 0 && (int)$v->id == (int)$v2->id) {
						$v->catid 		= $v2->catid;
						$v->catalias 	= $v2->catalias;
						$v->cattitle	= $v2->cattitle;
					}
				}
			}
			
		}*/
		return $products;
	}
}