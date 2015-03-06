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
	public static function getProduct($productId) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.id, a.catid, a.alias, a.title, a.sku, a.price, a.tax_id as taxid, a.image, a.weight, a.volume,'
				.' a.download_token, a.download_folder, a.download_file, a.download_hits, a.stock, a.min_quantity,'
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
				.' WHERE a.id = '.(int)$productId
				.' LIMIT 1';
		$db->setQuery($query);
		$product = $db->loadObject();
		return $product;
	}
	
	/*
	 * Check if user has access to this product
	 * when adding to cart
	 * when ordering
	 * NOT USED when displaying, as no products are displayed which cannnot be accessed
	 * So this is security feature in case of forgery - server side checking
	 */
	
	public static function checkIfAccessPossible($id) {
	
		if ((int)$id > 0) {
			
			$db 		= JFactory::getDBO();
			$wheres		= array();
			$user 		= JFactory::getUser();
			$userLevels	= implode (',', $user->getAuthorisedViewLevels());
			$wheres[] 	= " a.access IN (".$userLevels.")";
			$wheres[] 	= " c.access IN (".$userLevels.")";
			$wheres[] 	= ' a.id = '.(int)$id;
			
			$query = ' SELECT a.id'
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
			. ' WHERE ' . implode( ' AND ', $wheres )
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
				.' LIMIT 1';
		$db->setQuery($query);
		$product = $db->loadObject();
		return $product;
	}
}