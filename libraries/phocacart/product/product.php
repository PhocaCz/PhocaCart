<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartProduct
{

	public function __construct() {}
	
	public static function getProduct($productId, $prioritizeCatid = 0) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.id, c.id as catid, a.alias, a.title, a.sku, a.price, a.price_original, a.tax_id as taxid, a.image, a.weight, a.volume, a.unit_amount, a.unit_unit,'
				.' a.download_token, a.download_folder, a.download_file, a.download_hits, a.stock, a.stock_calculation, a.min_quantity, a.min_multiple_quantity, a.min_quantity_calculation,'
				.' t.id as taxid, t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype'
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
					$checkCategory = PhocacartProduct::checkIfAccessPossible((int)$product->id, (int)$prioritizeCatid);
				}
				
				if ($checkCategory) {
					$product->catid 	= (int)$prioritizeCatid;
				}
			}	
		}
		
		
		// Change TAX based on country or region
		if (!empty($product)) {
			$taxChangedA = PhocacartTax::changeTaxBasedOnRule($product->taxid, $product->taxrate, $product->taxcalctype, $product->taxtitle);
			$product->taxrate 	= $taxChangedA['taxrate'];
			$product->taxtitle	= $taxChangedA['taxtitle'];
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
	
	
	// No access rights, publish, stock, etc. Not group contacts, but * - we really need it
	
	public static function getProductsFull($limitOffset = 0, $limitCount = 1, $orderingItem = 1) {
	
		/*phocacart import('phocacart.ordering.ordering');*/
		
		$ordering 		= PhocacartOrdering::getOrderingCombination($orderingItem);
		$db 			= JFactory::getDBO();
		$wheres			= array();
		
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

		$q = ' SELECT a.*';

		// No Images, Categories, Attributes, Specifications here
		$q .= ', CONCAT_WS(":", t.id, t.alias) AS tax';
		$q .= ', CONCAT_WS(":", m.id, m.alias) AS manufacturer';
			
		$q .= ' FROM #__phocacart_products AS a'
			. ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
			. ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'
			. $where
			. ' GROUP BY a.id';
			
		if ($ordering != '') {
			$q .= ' ORDER BY '.$ordering;
		}
		
		if ((int)$limitCount > 0) {
			$q .= ' LIMIT '.(int)$limitOffset. ', '.(int)$limitCount;
		}
		
		$db->setQuery($q);
	
		$products = $db->loadAssocList();
		return $products;
	}	
	
	/*
	* checkPublished = true - select only published products
	* checkPublished = false - select all (published|unpublished) products
	* PUBLISHED MEANS, THEY ARE REALLY PUBLISHED - they are published as products and their category is published too.
	*
	* checkStock - check Stock or not ( > 0 )
	* checkPrice - check if the product has price or not ( > 0 )
	*/
	
	public static function getProducts($limitOffset = 0, $limitCount = 1, $orderingItem = 1, $orderingCat = 0, $checkPublished = false, $checkStock = false, $checkPrice = false, $categoriesList = 0, $categoryIds = array()) {
	
	

		/*phocacart import('phocacart.ordering.ordering');*/
		
		$ordering 	= PhocacartOrdering::getOrderingCombination($orderingItem, $orderingCat);
	
	
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
		
		$q = ' SELECT a.id, a.title, a.image, a.video, a.alias, a.description, a.description_long, a.sku, a.stockstatus_a_id, a.stockstatus_n_id, a.min_quantity, a.min_multiple_quantity, a.stock, a.unit_amount, a.unit_unit, c.id AS catid, c.title AS cattitle, c.alias AS catalias, c.title_feed AS cattitlefeed, a.price, a.price_original, t.id as taxid, t.tax_rate AS taxrate, t.calculation_type AS taxcalculationtype, t.title AS taxtitle, a.date, a.sales, a.featured, a.external_id, m.title AS manufacturertitle,'
			. ' AVG(r.rating) AS rating,'
			. ' at.required AS attribute_required';
			
			if ($categoriesList == 1) {
			$q .= ', GROUP_CONCAT(c.id) AS categories';
		} else if ($categoriesList == 2) {
			$q .= ', GROUP_CONCAT(c.title SEPARATOR "|") AS categories';
		} else if ($categoriesList == 3) {
			$q .= ', GROUP_CONCAT(c.id, ":", c.alias SEPARATOR "|") AS categories';
		} else if ($categoriesList == 4) {
			$q .= ', GROUP_CONCAT(c.id, ":", c.title SEPARATOR "|") AS categories';
		}
		
		// Possible DISTINCT
		//$q .= ', GROUP_CONCAT(DISTINCT c.id, ":", c.title SEPARATOR "|") AS categories';


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
			
			
			if ((int)$limitCount > 0) {
				$q .= ' LIMIT '.(int)$limitOffset. ', '.(int)$limitCount;
			}
		
		$db->setQuery($q);
	
		$products = $db->loadObjectList();
		return $products;
	}
	
	/*
	 * Obsolete
	 */
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
	
	public static function getCategoriesByProductId($id) {
		$db = JFactory::getDBO();
		$q = 'SELECT pc.category_id, c.alias, pc.ordering'
		. ' FROM #__phocacart_product_categories AS pc'
		. ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
		. ' WHERE pc.product_id = '.(int)$id
		. ' ORDER BY pc.ordering';
		$db->setQuery( $q );
		$categories = $db->loadAssocList();
		
		return $categories;
	}
	
	public static function getImagesByProductId($id) {
		$db = JFactory::getDBO();
		$q = 'SELECT pi.image'
		. ' FROM #__phocacart_product_images AS pi'
		. ' WHERE pi.product_id = '.(int)$id
		. ' ORDER BY pi.id';
		$db->setQuery( $q );
		$categories = $db->loadAssocList();
		
		return $categories;
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
	
	/* Used for Export Import
	 * Set in layout so users can select columns
	*/
	/*
	public static function getProductColumns() {
		
		$a = array();
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$export_attributes		= $paramsC->get( 'export_attributes', 1 );
		$export_specifications	= $paramsC->get( 'export_specifications', 1 );
		$export_downloads		= $paramsC->get( 'export_downloads', 0 );
		
		//$a[] = array('catid', 'COM_PHOCACART_FIELD__LABEL');
		// Categories, Images, Attributes, Specifications
		
		$a[] = array('id', 'JGLOBAL_FIELD_ID_LABEL');
		$a[] = array('title', 'COM_PHOCACART_FIELD_TITLE_LABEL');
		$a[] = array('alias', 'COM_PHOCACART_FIELD_ALIAS_LABEL');
		
		
		$a[] = array('sku', 'COM_PHOCACART_FIELD_SKU_LABEL');
		$a[] = array('ean', 'COM_PHOCACART_FIELD_EAN_LABEL');
		
		$a[] = array('price', 'COM_PHOCACART_FIELD_PRICE_LABEL');
		$a[] = array('price_original', 'COM_PHOCACART_FIELD_ORIGINAL_PRICE_LABEL');
		
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
		
		$a[] = array('access', 'JFIELD_ACCESS_LABEL');
		$a[] = array('featured', 'COM_PHOCACART_FIELD_FEATURED_LABEL');
		
		$a[] = array('video', 'COM_PHOCACART_FIELD_VIDEO_URL_LABEL');
		$a[] = array('public_download_file', 'COM_PHOCACART_FIELD_PUBLIC_DOWNLOAD_FILE_LABEL');
		
		$a[] = array('description', 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL');
		$a[] = array('description_long', 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL');
		
		$a[] = array('image', 'COM_PHOCACART_FIELD_IMAGE_LABEL');
		
		// IMAGES (not exist in query)
		$a[] = array('images', 'COM_PHOCACART_ADDITIONAL_IMAGES');
		
		if ($export_attributes == 1) {
			// ATTRIBUTES (not exist in query)
			$a[] = array('attributes', 'COM_PHOCACART_ATTRIBUTES');
		}
		
		if ($export_specifications == 1) {
			// SPECIFICATIONS (not exist in query)
			$a[] = array('specifications', 'COM_PHOCACART_SPECIFICATIONS');
		}
		
		// RELATED_PRODUCTS (not exist in query)
		$a[] = array('related', 'COM_PHOCACART_RELATED_PRODUCTS');
		
		$a[] = array('stock', 'COM_PHOCACART_FIELD_IN_STOCK_LABEL');
		$a[] = array('stockstatus_a_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_A_LABEL');
		$a[] = array('stockstatus_n_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_B_LABEL');
		$a[] = array('min_quantity', 'COM_PHOCACART_FIELD_MIN_ORDER_QUANTITY_LABEL');
		$a[] = array('min_multiple_quantity', 'COM_PHOCACART_FIELD_MIN_MULTIPLE_ORDER_QUANTITY_LABEL');
		//$a[] = array('availability', 'COM_PHOCACART_FIELD_AVAILABILITY_LABEL');
		
		if ($export_downloads == 1) {
			$a[] = array('download_token', 'COM_PHOCACART_FIELD_DOWNLOAD_TOKEN_LABEL');
			$a[] = array('download_folder', 'COM_PHOCACART_FIELD_DOWNLOAD_FOLDER_LABEL');
			$a[] = array('download_file', 'COM_PHOCACART_FIELD_DOWNLOAD_FILE_LABEL');
			$a[] = array('download_hits', 'COM_PHOCACART_FIELD_DOWNLOAD_HITS_LABEL');
		}
		
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
		
		
		$a[] = array('published', 'COM_PHOCACART_FIELD_PUBLISHED_LABEL');
		$a[] = array('language', 'JFIELD_LANGUAGE_LABEL');
		
		$a[] = array('date', 'COM_PHOCACART_FIELD_DATE_LABEL');
		
		// TAGS (not exist in query)
		$a[] = array('tags', 'COM_PHOCACART_TAGS');

		$a[] = array('metakey', 'JFIELD_META_KEYWORDS_LABEL');
		$a[] = array('metadesc', 'JFIELD_META_DESCRIPTION_LABEL');
		

		//$a[] = array('ordering', 'COM_PHOCACART_FIELD_ORDERING_LABEL');
		
		//$a[] = array('allow_upload', 'COM_PHOCACART_FIELD_ALLOW_UPLOAD_LABEL');
		//$a[] = array('custom_text', 'COM_PHOCACART_FIELD_CUSTOM_TEXT_LABEL');
		
		
		
		//$a[] = array('checked_out', 'COM_PHOCACART_FIELD__LABEL');
		//$a[] = array('checked_out_time', 'COM_PHOCACART_FIELD__LABEL');
		
		
		
		//$a[] = array('hits', 'COM_PHOCACART_FIELD_HITS_LABEL');
		//$a[] = array('sales', 'COM_PHOCACART_FIELD__LABEL');
		//$a[] = array('params', 'COM_PHOCACART_FIELD__LABEL');
		
		//$a[] = array('metadata', 'COM_PHOCACART_FIELD__LABEL');
		
		
		return $a;
	} */
	
	public static function featured($pks, $value = 0) {
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);
		$app = JFactory::getApplication();

		if (empty($pks))
		{
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_ITEM_SELECTED'), 'message');
			return false;
		}

		//$table = $this->getTable('PhocacartFeatured', 'Table');
		$table = JTable::getInstance('PhocacartFeatured', 'Table', array());
		

		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__phocacart_products'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();

			if ((int) $value == 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
							->delete($db->quoteName('#__phocacart_product_featured'))
							->where('product_id IN (' . implode(',', $pks) . ')');
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// first, we find out which of our new featured articles are already featured.
				$query = $db->getQuery(true)
					->select('f.product_id')
					->from('#__phocacart_product_featured AS f')
					->where('product_id IN (' . implode(',', $pks) . ')');
				//echo $query;
				$db->setQuery($query);

				$old_featured = $db->loadColumn();

				// we diff the arrays to get a list of the articles that are newly featured
				$new_featured = array_diff($pks, $old_featured);

				// Featuring.
				$tuples = array();
				foreach ($new_featured as $pk)
				{
					$tuples[] = $pk . ', 0';
				}
				if (count($tuples))
				{
					$db = JFactory::getDbo();
					$columns = array('product_id', 'ordering');
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__phocacart_product_featured'))
						->columns($db->quoteName($columns))
						->values($tuples);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (Exception $e)
		{
			
			$app->enqueueMessage($e->getMessage(), 'message');
			return false;
		}

		$table->reorder();

		//$this->cleanCache();

		return true;
	}
	
	
	public static function storeProduct($data, $importColumn = 1) {
		

		
		
		// Store
		$table = JTable::getInstance('PhocaCartItem', 'Table', array());

		$newInsertOldId = 0;
		if ($importColumn == 2) {
			// SKU
			if (isset($data['sku']) && $data['sku'] != '') {
				$found = $table->load(array('sku' => $data['sku']));
				
				// Such id is found, but we store by SKU - we need to unset it to get new created by autoincrement
				if ($found) {
					$data['id'] = $table->id;
				} else {
					// New row
					//unset($data['id']); store the same ID for importing product if possible
					// unfortunately this is not possible per standard way
					
					// We didn't find the row by SKU, but we have the ID, so we try to update by ID
					// If we don't find the ID (so no SKU, no ID), insert new row 
					// We try to add current ID (it does not exist), not new autoincrement
					$found2 = $table->load((int)$data['id']);
					if (!$found2) {
						$newInsertOldId = 1;
					}
				}
			}
		} else {
			// ID
			if (isset($data['id']) && (int)$data['id'] > 0) {
				$found = $table->load((int)$data['id']);
				
				// Such id not found, we need to unset it to get new created by autoincrement
				if (!$found) {
					// New row
					//unset($data['id']);  store the same ID for importing product if possible
					// unfortunately this is not possible per standard way
					$newInsertOldId = 1;
				}
			}
		}
		
		if (!$table->bind($data)) {
			throw new Exception($db->getErrorMsg());
			return false;
		}

		if(intval($table->date) == 0) {
			$table->date = JFactory::getDate()->toSql();
		}
		
		
		if (!$table->check()) {
			throw new Exception($table->getError());
			return false;
		}
		
		if ($newInsertOldId == 1) {
			// The imported ID does not exist, we need to add new row, but we try to use the same ID
			// even the ID is autoincrement (this is why we use non standard method) because
			// standard method cannot add IDs into autoincrement
			$db = JFactory::getDBO();
			if(!$db->insertObject('#__phocacart_products', $table, 'id')) {
				throw new Exception($table->getError());
				return false;
			}
			
		} else {
			if (!$table->store()) {
				throw new Exception($table->getError());
				return false;
					
			}
		}
		
			
		// Test Thumbnails (Create if not exists)
		if ($table->image != '') {
			$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
		}
			
		if ((int)$table->id > 0) {
			
			if (!isset($data['catid_multiple'])) {
				$data['catid_multiple'] = array();
			}
			if (!isset($data['catid_multiple_ordering'])) {
				$data['catid_multiple_ordering'] = array();
			}
			PhocacartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->id, $data['catid_multiple_ordering']);
			
			if (isset($data['featured'])) {
				PhocacartProduct::featured((int)$table->id, $data['featured']);
			}
			
			$dataRelated = '';
			if (!isset($data['related'])) {
				$dataRelated = '';
			} else {
				$dataRelated = $data['related'];
				if (is_array($data['related']) && isset($data['related'][0])) {
					$dataRelated = $data['related'][0];
				}
			}

			PhocacartRelated::storeRelatedItemsById($dataRelated, (int)$table->id );
			PhocacartImageAdditional::storeImagesByProductId((int)$table->id, $data['images']);
			PhocacartAttribute::storeAttributesById((int)$table->id, $data['attributes']);
			PhocacartAttribute::storeCombinationsById((int)$table->id, $data['advanced_stock_options']);
			PhocacartSpecification::storeSpecificationsById((int)$table->id, $data['specifications']);
			PhocacartDiscountProduct::storeDiscountsById((int)$table->id, $data['discounts']);
			PhocacartTag::storeTags($data['tags'], (int)$table->id);

			return $table->id;
		}
		
		return false;
	}
	
	public static function getProductKey($id, $attributes = array()) {
		
		
		
		$key = (int)$id . ':';
		if (!empty($attributes)) {
			
			
			// Sort attributes (becasue of right key generation)
			ksort($attributes);
			// Remove empty values, so items with empty values (add to cart item view) is the same
			// like item without any values (add to cart category view)
			foreach($attributes as $k => $v) {
				
				// Transform all attribute values to array (when they get string instead of array from html)
				if (!is_array($v)) {
					$attributes[$k] = array($v => $v);
				}
				
				// Unset when string is empty or zero
				if ($v == 0 || $v == '') {
					unset($attributes[$k]);
				}
				
				// Unset when we have transformed it to array but it is empty
				if (empty($v)) {
					unset($attributes[$k]);
				}
			}
			
			// Sort options (because of right key generation)
			foreach($attributes as $k3 => $v3) {
				if (is_array($v3)){
					ksort($attributes[$k3]);
				}
			}
			
			if (!empty($attributes)) {
				$key .= base64_encode(serialize($attributes));
			}
		}
		$key .= ':';
		
		return $key;
			
		/*$key = 'ID:'.(int)$id .'{';

		if (!empty($attributes)) {

			ksort($attributes);
			// Remove empty values, so items with empty values (add to cart item view) is the same
			// like item without any values (add to cart category view)
			foreach($attributes as $k => $v) {
				if ($v == 0 || $v == '') {
					unset($attributes[$k]);
				}
			}
			foreach($attributes as $k => $v) {
				if (is_array($v)){
					asort($attributes[$k]);
				}
			}

			foreach($attributes as $k => $v) {
				$key .= 'AID:'.(int)$k . '[';
				if (is_array($v)){
					foreach($v as $k2 => $v2) {
						$key .= 'OID:('.(int)$v2 . ')';
					}
				} else {
					$key .= 'OID:('.(int)$v.')';
				}
				$key .= ']';
			}

		
			if (!empty($attributes)) {
				$k .= base64_encode(serialize($attributes));
			}*/
		/*}
		$key .= '}';
		return $key;*/
	}
	
}