<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocacartUtilsBatchhelper
{
	public static function storeProductItems($idSource, $idDest) {
		
		if ($idSource > 0 && $idDest > 0) {
			
			// Related products
			$aR = PhocacartRelated::getRelatedItemsById($idSource, 1);
			$aRS = '';
			if ($aR != '') {
				$aRS = implode(',', $aR);
			}
		
			PhocacartRelated::storeRelatedItemsById($aRS, (int)$idDest );
			// Additional Images
			$iA = PhocacartImageAdditional::getImagesByProductId($idSource, 1);
			PhocacartImageAdditional::storeImagesByProductId((int)$idDest, $iA);
			
			// Attributes
			$aA = PhocacartAttribute::getAttributesById($idSource, 1);
			if (!empty($aA)) {
				foreach ($aA as $k => $v) {
					if (isset($v['id']) && $v['id'] > 0) {
						$oA = PhocacartAttribute::getOptionsById((int)$v['id'], 1);
						if (!empty($oA)) {
							$aA[$k]['options'] = $oA;
						}
					}
				}
				
			}	
			PhocacartAttribute::storeAttributesById((int)$idDest, $aA, 1);
			
			// Specifications
			$sA = PhocacartSpecification::getSpecificationsById($idSource, 1);
			PhocacartSpecification::storeSpecificationsById((int)$idDest, $sA, 1);
			
			// Discounts
			$dA = PhocacartDiscountProduct::getDiscountsById($idSource, 1);
			PhocacartDiscountProduct::storeDiscountsById((int)$idDest, $dA, 1);
			
			// Advanced Stock Options
			$aSOA = PhocacartAttribute::storeCombinationsById($idSource, 1);
			PhocacartAttribute::storeCombinationsById((int)$idDest, $aSOA, 1);
		
			// Tags
			$tA = PhocacartTag::getTags($idSource, 1);
			if (!isset($tA)) {
				$tA = array();
			}
			PhocacartTag::storeTags($tA, (int)$idDest);
		}
		return true;
	}
}
?>