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

class PhocacartImageAdditional
{
	public static function getImagesByProductId($productId, $return = 0) {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.image';
		$query .= ' FROM #__phocacart_product_images AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.ordering';
		$db->setQuery($query);

		if ($return == 0) {
			return $db->loadObjectList();
		} else if ($return == 1) {
			return $db->loadAssocList();
		} else {
		    $images          = $db->loadAssocList();
		    $imagesSubform   = array();
		    $i              = 0;
		    if (!empty($images)) {
				foreach($images as $k => $v) {
					$imagesSubform['additional_images'.$i]['id'] = (string)$v['id'];
				    $imagesSubform['additional_images'.$i]['image'] = (string)$v['image'];
					$i++;
				}
			}
		    return $imagesSubform;
        }

		return false;
	}


	public static function storeImagesByProductId($productId, $imageArray, $new = 0) {

		if ((int)$productId > 0) {
			$db =Factory::getDBO();

			$notDeleteImages   = array();// Select all images which will be not deleted
            $i                  = 1;

			if (!empty($imageArray)) {

				foreach($imageArray as $k => $v) {

				    // correct simple xml
					if (empty($v['image'])) {$v['image'] = '';}

                    $idExists = 0;
					if ($new == 0) {
						if (isset($v['id']) && $v['id'] > 0) {

							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_product_images'
							. ' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();

						}
					}

					if ((int)$idExists > 0) {

						$query = 'UPDATE #__phocacart_product_images SET'
						.' product_id = '.(int)$productId.','
						.' image = '.$db->quote($v['image']).','
                        .' ordering = '.(int)$i
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();
                        $i++;
						$newIdA 				= $idExists;

					} else {

					     // Test Thumbnails (Create if not exists)
					    $thumb = PhocacartFileThumbnail::getOrCreateThumbnail($v['image'], '', 1, 1, 1, 0, 'productimage');

						$valuesString 	= '('.(int)$productId.', '.$db->quote($v['image']).', '.$i.')';
						$query = ' INSERT INTO #__phocacart_product_images (product_id, image, ordering)'
								.' VALUES '.(string)$valuesString;
						$db->setQuery($query);
						$db->execute(); // insert is not done together but step by step because of getting last insert id
                        $i++;
						// ADD OPTIONS
						$newIdA = $db->insertid();

					}


					$notDeleteImages[]	= $newIdA;

				}

			}


			// Remove all images except the active
			if (!empty($notDeleteImages)) {
				$notDeleteImagesString = implode(',', $notDeleteImages);

				$query = ' DELETE '
						.' FROM #__phocacart_product_images'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteImagesString.')';

			} else {

				$query = ' DELETE '
						.' FROM #__phocacart_product_images'
						.' WHERE product_id = '. (int)$productId;
			}

			$db->setQuery($query);
			$db->execute();

		}

	}
}
