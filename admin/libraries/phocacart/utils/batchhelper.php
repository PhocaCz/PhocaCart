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
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocacartUtilsBatchhelper
{
	public static function storeProductItems($idSource, $idDest, $batchParams, $params = array()) {

		if ($idSource > 0 && $idDest > 0) {
			PhocacartRelated::copyRelatedItems($idSource, $idDest);

			// Additional Images
			$iA = PhocacartImageAdditional::getImagesByProductId($idSource, 2);
			PhocacartImageAdditional::storeImagesByProductId((int)$idDest, $iA, 1);

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


            $copy = 1;// When copying attributes or batch products we do a copy of attributes (copy = 1) but in this case without copying download files on the server
            if (isset($batchParams['copy_attributes_download_files']) && $batchParams['copy_attributes_download_files'] == 1) {
                $copy = 2;// The same like 1 but in this case we even copy the download files on the server, see: PhocacartAttribute::storeAttributesById() for more info
            }

			PhocacartAttribute::storeAttributesById((int)$idDest, $aA, 1, $copy);


            // Additional download files
			if (isset($batchParams['copy_download_files']) && $batchParams['copy_download_files'] == 1) {

				$fA = PhocacartFileAdditional::getProductFilesByProductId($idSource, 2);

				$fANew = array();
				if(!empty($fA)) {
					foreach($fA as $k => $v) {
						if (isset($v['download_file']) && $v['download_file'] != '') {
							$fANew[]['download_file'] = str_replace($params['olddownloadfolder'], $params['newdownloadfolder'], $v['download_file']);
						}
					}
				}

				PhocacartFileAdditional::storeProductFilesByProductId((int)$idDest, $fANew, 1);
			}





			// Specifications
			$sA = PhocacartSpecification::getSpecificationsById($idSource, 1);
			PhocacartSpecification::storeSpecificationsById((int)$idDest, $sA, 1);

			// Discounts
			$dA = PhocacartDiscountProduct::getDiscountsById($idSource, 1);
			PhocacartDiscountProduct::storeDiscountsById((int)$idDest, $dA, 1);

			// Advanced Stock Options
			 $aSOA = PhocacartAttribute::getCombinationsStockById($idSource, 1);// NOT POSSIBLE TO COPY
			// We need to create new attributes including new product key, etc.


			$product = array();
			$product['product']			= PhocacartProduct::getProduct((int)$idDest);
			$product['attr_options']	= PhocacartAttribute::getAttributesAndOptions((int)$idDest);
			$product['combinations']		= array();
			$product['combinations_stock']	= array();

			if (!empty($product['product'])) {
				//PhocacartAttribute::storeCombinationsById((int)$idDest, $product['attr_options'], 1);
				PhocacartAttribute::getCombinations( $product['product']->id, $product['product']->title,  $product['attr_options'], $product['combinations']);

				$aSOANew = array();
				if (!empty($product['combinations'])) {
					$i = 0;
					foreach($product['combinations'] as $k => $v) {

						$attributes = PhocacartProduct::getProductKey($v['product_id'], $v['attributes'], 0);
						$aSOANew[$i]['product_id'] = (int)$v['product_id'];
						$aSOANew[$i]['product_key'] = $v['product_key'];
						$aSOANew[$i]['stock'] = 0;// Stock cannot be copied as the attributes are completely new and product key is different
						$aSOANew[$i]['attributes'] = $attributes;
						$i++;
					}


					PhocacartAttribute::storeCombinationsById((int)$idDest, $aSOANew, 1);
				}

			}




			// Customer groups
			$cA = PhocacartGroup::getGroupsById($idSource, 3, 1);
			PhocacartGroup::storeGroupsById((int)$idDest, 3, $cA);

			// Tags
			$tA = PhocacartTag::getTags($idSource, 1);
			if (!isset($tA)) {
				$tA = array();
			}
			PhocacartTag::storeTags($tA, (int)$idDest);

			// Tag Labels
			$tLA = PhocacartTag::getTagLabels($idSource, 1);
			if (!isset($tLA)) {
				$tLA = array();
			}
			PhocacartTag::storeTagLabels($tLA, (int)$idDest);

			// Parameters
			$parameters = PhocacartParameter::getAllParameters();
			if (!empty($parameters)) {
				foreach ($parameters as $kP => $vP) {
					if (isset($vP->id) && (int)$vP->id > 0) {
						$idP = (int)$vP->id;

						$pA = PhocacartParameter::getParameterValues($idSource, $idP, 1);
						if (!isset($pA)) {
							$pA = array();
						}
						PhocacartParameter::storeParameterValues($pA, (int)$idDest, $idP);
					}
				}
			}
		}
		return true;
	}


	public static function storeCategoryItems($idSource, $idDest) {

		if ($idSource > 0 && $idDest > 0) {

			// Customer groups
			$cA = PhocacartGroup::getGroupsById($idSource, 2, 1);
			PhocacartGroup::storeGroupsById((int)$idDest, 2, $cA);

		}
		return true;
	}
}

