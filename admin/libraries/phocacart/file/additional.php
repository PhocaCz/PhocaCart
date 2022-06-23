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

class PhocacartFileAdditional
{
	public static function getProductFilesByProductId($productId, $return = 0) {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.download_file, a.download_token, a.download_days';
		$query .= ' FROM #__phocacart_product_files AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.ordering';
		$db->setQuery($query);
		if ($return == 0) {
			return $db->loadObjectList();
		} else if ($return == 1) {
			return $db->loadAssocList();
		} else {
		    $files          = $db->loadAssocList();
		    $filesSubform   = array();
		    $i              = 0;
		    if (!empty($files)) {
				foreach($files as $k => $v) {
				    $filesSubform['additional_download_files'.$i]['id'] = (string)$v['id'];
					$filesSubform['additional_download_files'.$i]['download_file'] = (string)$v['download_file'];
					$filesSubform['additional_download_files'.$i]['download_token'] = (string)$v['download_token'];
					$filesSubform['additional_download_files'.$i]['download_days'] = (string)$v['download_days'];
					$i++;
				}
			}
		    return $filesSubform;
        }

		return false;
	}


	public static function storeProductFilesByProductId($productId, $fileArray, $new = 0) {

		if ((int)$productId > 0) {
			$db =Factory::getDBO();

			$notDeleteFiles   = array();// Select all files which will be not deleted
            $i                  = 1;

			if (!empty($fileArray)) {

				foreach($fileArray as $k => $v) {

				    // correct simple xml
					if (empty($v['download_token'])) {$v['download_token'] = '';}
					if (empty($v['download_file'])) {$v['download_file'] = '';}
					if (empty($v['download_days'])) {$v['download_days'] = -1;}

                    $idExists = 0;
					if ($new == 0) {
						if (isset($v['id']) && $v['id'] > 0) {

							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_product_files'
							.' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();

						}
					}

					if ((int)$idExists > 0) {

						$query = 'UPDATE #__phocacart_product_files SET'
						.' product_id = '.(int)$productId.','
						.' download_token = '.$db->quote($v['download_token']).','
                        .' download_file = '.$db->quote($v['download_file']).','
                        .' download_days = '.(int)$v['download_days'].','
                        .' ordering = '.(int)$i
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();
                        $i++;
						$newIdA 				= $idExists;

					} else {


						$valuesString 	= '('.(int)$productId.', '.$db->quote($v['download_token']).', '.$db->quote($v['download_file']).', '.(int)$v['download_days'].', '.$i.')';
						$query = ' INSERT INTO #__phocacart_product_files (product_id, download_token, download_file, download_days, ordering)'
								.' VALUES '.(string)$valuesString;
						$db->setQuery($query);
						$db->execute(); // insert is not done together but step by step because of getting last insert id
                        $i++;
						// ADD OPTIONS
						$newIdA = $db->insertid();

					}


					$notDeleteFiles[]	= $newIdA;

				}

			}


			// Remove all files except the active
			if (!empty($notDeleteFiles)) {
				$notDeleteFilesString = implode(',', $notDeleteFiles);

				$query = ' DELETE '
						.' FROM #__phocacart_product_files'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteFilesString.')';

			} else {

				$query = ' DELETE '
						.' FROM #__phocacart_product_files'
						.' WHERE product_id = '. (int)$productId;
			}

			$db->setQuery($query);
			$db->execute();

		}

	}
}
