<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartImageAdditional
{
	public static function getImagesByProductId($productId, $returnArray = 0) {
	
		$db = JFactory::getDBO();

		$query = 'SELECT a.id, a.image';
		$query .= ' FROM #__phocacart_product_images AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$option = $db->loadAssocList();
		} else {
			$option = $db->loadObjectList();
		}
		
		return $option;
	}

	
	public static function storeImagesByProductId($productId, $imageArray) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_product_images'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			if (!empty($imageArray)) {
				
				$values 		= array();
				$valuesString 	= '';

				foreach($imageArray as $k => $v) {
					
					// Test Thumbnails (Create if not exists)
					$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($v['image'], '', 1, 1, 1, 0, 'productimage');
				
					// correct simple xml
					if (empty($v['image'])) {$v['image'] = '';}
					
					if (isset($v['image']) && $v['image'] != '') {
						$values[] = ' ('.(int)$productId.', '.$db->quote($v['image']).')';
					}
				}
			
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_product_images (product_id, image)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	
	}
}