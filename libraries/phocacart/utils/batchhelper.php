<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocaCartBatchHelper
{
	public static function storeProductItems($idSource, $idDest) {
		
		if ($idSource > 0 && $idDest > 0) {
			
			// Related products
			$aR = PhocaCartRelated::getRelatedItemsById($idSource, 1);
			$aRS = '';
			if ($aR != '') {
				$aRS = implode(',', $aR);
			}
		
			PhocaCartRelated::storeRelatedItemsById($aRS, (int)$idDest );
			// Additional Images
			$iA = PhocaCartImageAdditional::getImagesByProductId($idSource, 1);
			PhocaCartImageAdditional::storeImagesByProductId((int)$idDest, $iA);
			
			// Attributes
			$aA = PhocaCartAttribute::getAttributesById($idSource, 1);
			if (!empty($aA)) {
				foreach ($aA as $k => $v) {
					if (isset($v['id']) && $v['id'] > 0) {
						$oA = PhocaCartAttribute::getOptionsById((int)$v['id'], 1);
						if (!empty($oA)) {
							$aA[$k]['option'] = $oA;
						}
					}
				}
				
			}	
			PhocaCartAttribute::storeAttributesById((int)$idDest, $aA);
			
			// Specifications
			$sA = PhocaCartSpecification::getSpecificationsById($idSource, 1);
			PhocaCartSpecification::storeSpecificationsById((int)$idDest, $sA);
		
			// Tags
			$tA = PhocaCartTag::getTags($idSource, 1);
			if (!isset($tA)) {
				$tA = array();
			}
			PhocaCartTag::storeTags($tA, (int)$idDest);
		}
		return true;
	}
}
?>