<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartRelated
{
	public static function storeRelatedItemsById($relatedString, $productId) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_product_related'
					. ' WHERE product_a = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			if (isset($relatedString) && $relatedString != '') {
				
				$relatedArray 	= explode(",", $relatedString);
				$values 		= array();
				$valuesString 	= '';
				
				foreach($relatedArray as $k => $v) {
					$values[] = ' ('.(int)$productId.', '.(int)$v.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_product_related (product_a, product_b)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	public static function getRelatedItemsById($productId, $select = 0) {
	
		$db =JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT t.product_b';
		} else {
			$query = 'SELECT a.id as id, a.title as title, a.image as image, a.alias as alias, a.catid as catid, c.alias as categoryalias, c.title as category_title';
		}
		$query .= ' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_related AS t ON a.id = t.product_b'
				.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
			    .' WHERE t.product_a = '.(int) $productId;
		$db->setQuery($query);
		
		if ($select == 1) {
			$related = $db->loadColumn();
		} else {
			$related = $db->loadObjectList();
		}
		return $related;
	}
}