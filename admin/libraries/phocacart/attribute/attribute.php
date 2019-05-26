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

class PhocacartAttribute
{
	public static function getAttributesById($productId, $returnArray = 0) {

		$db = JFactory::getDBO();

		$query = 'SELECT a.id, a.title, a.alias, a.required, a.type'
				.' FROM #__phocacart_attributes AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$attributes = $db->loadAssocList();
		} else {
			$attributes = $db->loadObjectList();
		}

		return $attributes;
	}

	public static function getOptionsById($attributeId, $returnArray = 0) {

		$db =JFactory::getDBO();

		$query = 'SELECT a.id, a.title, a.alias, a.amount, a.operator, a.stock, a.operator_weight, a.weight, a.image, a.image_medium, a.image_small, a.download_folder, a.download_file, a.download_token, a.color, a.default_value';
		$query .= ' FROM #__phocacart_attribute_values AS a'
			    .' WHERE a.attribute_id = '.(int) $attributeId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$options = $db->loadAssocList();
		} else {
			$options = $db->loadObjectList();
		}

		return $options;
	}

	public static function getTypeArray($returnId = 0, $returnValue = 0, $returnFull = 0) {

		// 0 ... Title
		// 1 ... Default Value Type (1 ... multiple default values (checkbox), 2 ... single default value (select box))

		// EDIT PHOCACARTATTRIBUTE (attribute class, attribute layouts)
		$o = array(
		'1' => array(JText::_('COM_PHOCACART_ATTR_TYPE_SELECT'), 0),
		'2' => array(JText::_('COM_PHOCACART_ATTR_TYPE_COLOR_SELECT'), 0),
		'3' => array(JText::_('COM_PHOCACART_ATTR_TYPE_IMAGE_SELECT'), 0),
		'4' => array(JText::_('COM_PHOCACART_ATTR_TYPE_CHECKBOX'), 1),
		'5' => array(JText::_('COM_PHOCACART_ATTR_TYPE_COLOR_CHECKBOX'), 1),
		'6' => array(JText::_('COM_PHOCACART_ATTR_TYPE_IMAGE_CHECKBOX'), 1),
		'7' => array(JText::_('COM_PHOCACART_ATTR_TYPE_TEXT_64'), ''),
		'8' => array(JText::_('COM_PHOCACART_ATTR_TYPE_TEXT_128'), ''),
		'9' => array(JText::_('COM_PHOCACART_ATTR_TYPE_TEXT_256'), ''),
        '10' => array(JText::_('COM_PHOCACART_ATTR_TYPE_TEXTAREA_1024'), ''),
        '11' => array(JText::_('COM_PHOCACART_ATTR_TYPE_TEXTAREA_2048'), ''),
		);

		if ((int)$returnValue > 0 && (int)$returnValue > 0) {
			return $o[(int)$returnId][(int)$returnValue];//returnValue: 0 ... Title, 1 ... Default Value Type
		}

		if ((int)$returnId > 0) {
			return $o[(int)$returnId];// whole row
		}
		if ($returnFull == 0) {
			$o2 = array();
			foreach($o as $k => $v) {
				$o2[$k] = $v[0];
			}
			return $o2; // text and value for select box
		}
		return $o;
	}

	public static function getAttributeLength($type) {
		switch($type){
			case 7:
				return 64;
			break;

			case 8:
				return 128;
			break;

			case 9:
				return 256;
			break;

            case 10:
                return 1024;
            break;

            case 11:
                return 2048;
            break;

			default:
				return 0;
			break;
		}
		return 0;
	}

	/*
	 * type of attribute
	 * value of attribute
	 * encoded - is urlencoded yet
	 * display - are we asking it for display (we only want to display text attributes value not checkbox or selectboxes which include
	 *           numbers (their values are displayed other way
	 */

	public static function setAttributeValue($type, $value, $encoded = false, $display = false) {


		switch($type){
			case 7:
			case 8:
			case 9:
            case 10:
            case 11:
				if ($encoded || $display) {
					$value = urldecode($value);
				}

				$value = strip_tags($value);
				return urlencode(substr($value, 0, self::getAttributeLength($type)));
			break;

			default:
				if ($display) {
					return '';
				} else {
					return (int)$value;
				}

			break;
		}
		return false;
	}

	public static function getRequiredArray() {
		$o = array('0' => JText::_('COM_PHOCACART_NO'), '1' => JText::_('COM_PHOCACART_YES'));
		return $o;
	}

	public static function getOperatorArray() {
		$o = array('+' => '+', '-' => '-');
		return $o;
	}

	/**
	 * @param $productId
	 * @param $attributesArray
	 * @param int $new
	 * @param int $copy used by BATCH and COPY ATTRIBUTES - if copy == 1 then only create new tokens, if copy == 2 then create new tokens and create folder and copy the files from source
	 * @throws Exception
	 */

	public static function storeAttributesById($productId, $attributesArray, $new = 0, $copy = 0) {


		if ((int)$productId > 0) {
			$db 	= JFactory::getDBO();
			$app	= JFactory::getApplication();
			$pathAttributes = PhocacartPath::getPath('attributefile');// to check if attribute option download file exists



			// When you add or update attributes and options, you need to have some info about which attributes and options
			// are now active - so all others will be removed
			$notDeleteAttribs = array();// Select all attributes which will be not deleted
			// Options are defined in attributes array

			// ADD ATTRIBUTES
			if (!empty($attributesArray)) {

				foreach($attributesArray as $k => $v) {

					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);

					// correct simple xml
					if (empty($v['title'])) 		{$v['title'] 			= '';}
					if (empty($v['alias'])) 		{$v['alias'] 			= '';}
					if (empty($v['required'])) 		{$v['required'] 		= '';}
					if (empty($v['type'])) 			{$v['type'] 			= '';}


					$idExists = 0;
					if ($new == 0) {
						if (isset($v['id']) && $v['id'] > 0) {

							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_attributes'
							. ' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();

						}
					}

					if ((int)$idExists > 0) {

						$query = 'UPDATE #__phocacart_attributes SET'
						.' product_id = '.(int)$productId.','
						.' title = '.$db->quote($v['title']).','
						.' alias = '.$db->quote($v['alias']).','
						.' required = '.(int)$v['required'].','
						.' type = '.(int)$v['type']
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();

						$newIdA 				= $idExists;

					} else {

						$valuesString 	= '';
						$valuesString 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.(int)$v['required'].', '.(int)$v['type'].')';
						$query = ' INSERT INTO #__phocacart_attributes (product_id, title, alias, required, type)'
									.' VALUES '.(string)$valuesString;
						$db->setQuery($query);
						$db->execute(); // insert is not done together but step by step because of getting last insert id

						// ADD OPTIONS
						$newIdA = $db->insertid();

					}

					$notDeleteAttribs[]	= $newIdA;

					$notDeleteOptions 	= array();// Select all options which will be not deleted

					if (!empty($v['options']) && isset($newIdA) && (int)$newIdA > 0) {

						$options		= array();

						// Get Default Value Type - if the attribute type is single select box or multiple checkbox
						// If 1 ... it is multiple, you don't need to check for unique default value
						// If 0 ... it is single, you need to check that the attribute has selected only one value
						$dTV = self::getTypeArray($v['type'], 1);
						$dI  = 0;// defaultValue $i
						$dVR = 0;// defaultValue removed?


						foreach($v['options'] as $k2 => $v2) {

							if(empty($v2['alias'])) {
								$v2['alias'] = $v2['title'];
							}
							$v2['alias'] = PhocacartUtils::getAliasName($v2['alias']);

							// Transform checkbox to INT (1 or 0)
							// And check if there are more default values which is not possible e.g. for select box
							$defaultValue = 0;
							//PhocacartLog::add(1, $v['title'] . '- '. $v2['title']. $v2['type']. ' - '. $v2['default_value']);

							// can be "on" (sent by form) or "0" or "1" sent by database e.g. in batch
							if (isset($v2['default_value']) && $v2['default_value'] != '0') {
								$defaultValue = 1;


								//  SELECTBOX OR TEXT
								if ($dTV == 0 || $dTV == '') {
									$dI++;
								}

								// Example: we are in loop of options of select box
								// User has selected two default values (checked)
								// But select box can have only one default value, so we need to skip it and inform user
								if ($dTV == 0 && (int)$dI > 1) {
									// SELECT - only one default value
									$defaultValue = 0;
									$dVR = 1;
								} else if ($dTV == '' && (int)$dI > 0) {
									// TEXT - no default value
									$defaultValue = 0;
									$dVR = 1;
								}
							}

							// correct simple xml
							if (empty($v2['title'])) 			{$v2['title'] 			= '';}
							if (empty($v2['alias'])) 			{$v2['alias'] 			= '';}
							if (empty($v2['operator'])) 		{$v2['operator'] 		= '';}
							if (empty($v2['amount'])) 			{$v2['amount'] 			= '';}
							if (empty($v2['stock'])) 			{$v2['stock'] 			= '';}
							if (empty($v2['operator_weight'])) 	{$v2['operator_weight'] = '';}
							if (empty($v2['weight'])) 			{$v2['weight'] 			= '0.0';}
							if (empty($v2['image'])) 			{$v2['image'] 			= '';}
							if (empty($v2['image_medium']))		{$v2['image_medium'] 	= '';}
							if (empty($v2['image_small']))		{$v2['image_small'] 	= '';}
							if (empty($v2['download_folder']))	{$v2['download_folder']	= '';}
							if (empty($v2['download_file']))	{$v2['download_file'] 	= '';}
							if (empty($v2['download_token']))	{$v2['download_token']	= '';}
							if (empty($v2['color'])) 			{$v2['color'] 			= '';}


							// COPY OR BATCH functions - we cannot do the same tokens so create new token and token folder and if set copy the files
							// EACH ATTRIBUTE OPTION DOWNLOAD FILE MUST HAVE UNIQUE DOWNLOAD TOKEN AND DOWNLOAD FOLDER
							if ($copy > 0) {
								// First create new token and token folder
								$oldDownloadFolder		= $v2['download_folder'];
								$v2['download_token'] 	= PhocacartUtils::getToken();
								$v2['download_folder'] 	= PhocacartUtils::getToken('folder');


								if($copy == 2 && $v2['download_file'] != '' && \Joomla\CMS\Filesystem\File::exists($pathAttributes['orig_abs_ds'] . $v2['download_file'])) {

									$newDownloadFile = str_replace($oldDownloadFolder, $v2['download_folder'], $v2['download_file']);
									if (!\Joomla\CMS\Filesystem\Folder::create($pathAttributes['orig_abs_ds'] . $v2['download_folder'])) {
										// Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST
									}

									if (!\Joomla\CMS\Filesystem\File::copy($pathAttributes['orig_abs_ds'] . $v2['download_file'], $pathAttributes['orig_abs_ds'] . $newDownloadFile)) {
										// Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST
									}
									$v2['download_file'] = $newDownloadFile;
								} else {
									$v2['download_file'] = '';
								}


							}


							// CHECK DOWNLOAD FILE
							if ($v2['download_file'] != '' && $v2['download_folder'] == '') {
								$msg = JText::_('COM_PHOCACART_ATTRIBUTE'). ': '. $v['title'] . "<br />";
								$msg .= JText::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_DOES_NOT_INCLUDE_DOWNLOAD_FOLDER');
								$app->enqueueMessage($msg, 'error');

							}

							// If download_file does not exist on the server, remove it
							if ($v2['download_file'] != '' && !JFile::exists($pathAttributes['orig_abs_ds'] . $v2['download_file'])) {
								$v2['download_file'] = '';
								$msg = JText::_('COM_PHOCACART_ATTRIBUTE'). ': '. $v['title'] . "<br />";
								$msg .= JText::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST');
								$app->enqueueMessage($msg, 'error');
							}

							$idExists = 0;

							if ($new == 0) {
								if (isset($v2['id']) && $v2['id'] > 0) {

									// Does the row exist
									$query = ' SELECT id '
									.' FROM #__phocacart_attribute_values'
									. ' WHERE id = '. (int)$v2['id']
									.' ORDER BY id';
									$db->setQuery($query);
									$idExists = $db->loadResult();

								}
							}

							if ((int)$idExists > 0) {


								$v2['amount'] 			= PhocacartUtils::replaceCommaWithPoint($v2['amount']);
								$v2['weight'] 			= PhocacartUtils::replaceCommaWithPoint($v2['weight']);

								$query = 'UPDATE #__phocacart_attribute_values SET'
								.' attribute_id = '.(int)$newIdA.','
								.' title = '.$db->quote($v2['title']).','
								.' alias = '.$db->quote($v2['alias']).','
								.' operator = '.$db->quote($v2['operator']).','
								.' amount = '.$db->quote($v2['amount']).','
								.' stock = '.(int)$v2['stock'].','
								.' operator_weight = '.$db->quote($v2['operator_weight']).','
								.' weight = '.$db->quote($v2['weight']).','
								.' image = '.$db->quote($v2['image']).','
								.' image_medium = '.$db->quote($v2['image_medium']).','
								.' image_small = '.$db->quote($v2['image_small']).','
								.' download_folder = '.$db->quote($v2['download_folder']).','
								.' download_file = '.$db->quote($v2['download_file']).','
								.' download_token = '.$db->quote($v2['download_token']).','
								.' color = '.$db->quote($v2['color']).','
								.' default_value = '.(int)$defaultValue
								.' WHERE id = '.(int)$idExists;


								$db->setQuery($query);
								$db->execute();

								$newIdO 				= $idExists;

							} else {

								$v2['amount'] 			= PhocacartUtils::replaceCommaWithPoint($v2['amount']);
								$v2['weight'] 			= PhocacartUtils::replaceCommaWithPoint($v2['weight']);

								$options 	= '('.(int)$newIdA.', '
									.$db->quote($v2['title']).', '
									.$db->quote($v2['alias']).', '
									.$db->quote($v2['operator']).', '
									.$db->quote($v2['amount']).', '
									.(int)$v2['stock'].', '
									.$db->quote($v2['operator_weight']).', '
									.$db->quote($v2['weight']).', '
									.$db->quote($v2['image']).', '
									.$db->quote($v2['image_medium']).', '
									.$db->quote($v2['image_small']).', '
									.$db->quote($v2['download_folder']).','
									.$db->quote($v2['download_file']).','
									.$db->quote($v2['download_token']).','
									.$db->quote($v2['color']).', '
									.(int)$defaultValue.')';


								$query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, alias, operator, amount, stock, operator_weight, weight, image, image_medium, image_small, download_folder, download_file, download_token, color, default_value)'
											.' VALUES '.$options;

								$db->setQuery($query);
								$db->execute();
								$newIdO = $db->insertid();
							}

							$notDeleteOptions[]	= $newIdO;

						}

						// One or more default values removed
						if ($dVR == 1) {
							$msg = JText::_('COM_PHOCACART_ATTRIBUTE'). ': '. $v['title'] . "<br />";
							$msg .= JText::_('COM_PHOCACART_THIS_ATTRIBUTE_DOES_NOT_ALLOW_TO_STORE_DEFAULT_VALUES_OR_MULTIPLE_DEFAULT_VALUES');
							$app->enqueueMessage($msg, 'error');
						}
					}


					// Remove all options except the active
					if (!empty($notDeleteOptions)) {
						$notDeleteOptionsString = implode($notDeleteOptions, ',');

						// Remove all download files from not active attribute values:
						$qS = ' SELECT download_folder, download_file'
								.' FROM #__phocacart_attribute_values'
								.' WHERE attribute_id = '. (int)$newIdA
								.' AND id NOT IN ('.$notDeleteOptionsString.')';

						$query = ' DELETE '
								.' FROM #__phocacart_attribute_values'
								.' WHERE attribute_id = '. (int)$newIdA
								.' AND id NOT IN ('.$notDeleteOptionsString.')';

					} else {

						// Remove all download files from not active attribute values:
						$qS = ' SELECT download_folder, download_file'
							.' FROM #__phocacart_attribute_values'
							.' WHERE attribute_id = '. (int)$newIdA;

						$query = ' DELETE '
								.' FROM #__phocacart_attribute_values'
								.' WHERE attribute_id = '. (int)$newIdA;

					}

					$db->setQuery($qS);
					$folderFiles = $db->loadAssocList();
					self::removeDownloadFolderAndFiles($folderFiles, $pathAttributes);

					$db->setQuery($query);
					$db->execute();
				}
			}

			// Remove all attributes except the active
			if (!empty($notDeleteAttribs)) {
				$notDeleteAttribsString = implode($notDeleteAttribs, ',');

				// Remove all download files from not active attributes:
				$qS = ' SELECT v.download_folder, v.download_file'
					.' FROM #__phocacart_attribute_values AS v'
					.' LEFT JOIN #__phocacart_attributes AS a ON a.id = v.attribute_id'
					.' WHERE a.product_id = '. (int)$productId
					.' AND a.id NOT IN ('.$notDeleteAttribsString.')';

				$query = ' DELETE '
						.' FROM #__phocacart_attributes'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteAttribsString.')';

			} else {

				// Remove all download files from not active attributes:
				$qS = ' SELECT v.download_folder, v.download_file'
					.' FROM #__phocacart_attribute_values AS v'
					.' LEFT JOIN #__phocacart_attributes AS a ON a.id = v.attribute_id'
					.' WHERE a.product_id = '. (int)$productId;

				$query = ' DELETE '
						.' FROM #__phocacart_attributes'
						.' WHERE product_id = '. (int)$productId;
			}

			$db->setQuery($qS);
			$folderFiles = $db->loadAssocList();
			self::removeDownloadFolderAndFiles($folderFiles, $pathAttributes);

			$db->setQuery($query);
			$db->execute();

		}

	}

	public static function removeDownloadFolderAndFiles($folderFiles, $pathAttributes) {
		if (!empty($folderFiles)) {
			foreach ($folderFiles as $kF => $vF) {
				// Folder will remove the file(s) too
				if (\Joomla\CMS\Filesystem\Folder::exists($pathAttributes['orig_abs_ds'] . $vF['download_folder'])) {
					\Joomla\CMS\Filesystem\Folder::delete($pathAttributes['orig_abs_ds'] . $vF['download_folder']);
				}
			}
		}
	}

	/*
	public static function storeAttributesById($productId, $attributesArray) {


		if ((int)$productId > 0) {
			$db 	= JFactory::getDBO();
			$app	= JFactory::getApplication();


			// REMOVE OPTIONS
			// Get attribute ids which will be removed (to remove options)
			$query = ' SELECT id '
					.' FROM #__phocacart_attributes'
					. ' WHERE product_id = '. (int)$productId
					.' ORDER BY id';
			$db->setQuery($query);
			$deleteIds = $db->loadColumn();

			if (!empty($deleteIds)) {
				$deleteString = implode($deleteIds, ',');

				$query = ' DELETE '
					.' FROM #__phocacart_attribute_values'
					. ' WHERE attribute_id IN ('. (string)$deleteString.')';
				$db->setQuery($query);
				$db->execute();
			}

			// REMOVE ATTRIBUTES
			$query = ' DELETE '
					.' FROM #__phocacart_attributes'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();

			// ADD ATTRIBUTES
			if (!empty($attributesArray)) {


				foreach($attributesArray as $k => $v) {

					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);

					// correct simple xml
					if (empty($v['title'])) 		{$v['title'] 			= '';}
					if (empty($v['alias'])) 		{$v['alias'] 			= '';}
					if (empty($v['required'])) 		{$v['required'] 		= '';}
					if (empty($v['type'])) 			{$v['type'] 			= '';}

					$valuesString 	= '';
					$valuesString 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.(int)$v['required'].', '.(int)$v['type'].')';
					$query = ' INSERT INTO #__phocacart_attributes (product_id, title, alias, required, type)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute(); // insert is not done together but step by step because of getting last insert id

					// ADD OPTIONS
					$newId = $db->insertid();

					if (!empty($v['options']) && isset($newId) && (int)$newId > 0) {

						$options		= array();

						// Get Default Value Type - if the attribute type is single select box or multiple checkbox
						// If 1 ... it is multiple, you don't need to check for unique default value
						// If 0 ... it is single, you need to check that the attribute has selected only one value
						$dTV = self::getTypeArray($v['type'], 1);
						$dI  = 0;// defaultValue $i
						$dVR = 0;// defaultValue removed?


						foreach($v['options'] as $k2 => $v2) {

							if(empty($v2['alias'])) {
								$v2['alias'] = $v2['title'];
							}
							$v2['alias'] = PhocacartUtils::getAliasName($v2['alias']);

							// Transform checkbox to INT (1 or 0)
							// And check if there are more default values which is not possible e.g. for select box
							$defaultValue = 0;
							//PhocacartLog::add(1, $v['title'] . '- '. $v2['title']. $v2['type']. ' - '. $v2['default_value']);

							// can be "on" (sent by form) or "0" or "1" sent by database e.g. in batch
							if (isset($v2['default_value']) && $v2['default_value'] != '0') {
								$defaultValue = 1;

								if ($dTV == 0) {
									$dI++;
								}

								// Example: we are in loop of options of select box
								// User has selected two default values (checked)
								// But select box can have only one default value, so we need to skip it and inform user
								if ((int)$dI > 1) {
									$defaultValue = 0;
									$dVR = 1;
								}
							}

							// correct simple xml
							if (empty($v2['title'])) 			{$v2['title'] 			= '';}
							if (empty($v2['alias'])) 			{$v2['alias'] 			= '';}
							if (empty($v2['operator'])) 		{$v2['operator'] 		= '';}
							if (empty($v2['amount'])) 			{$v2['amount'] 			= '';}
							if (empty($v2['stock'])) 			{$v2['stock'] 			= '';}
							if (empty($v2['operator_weight'])) 	{$v2['operator_weight'] = '';}
							if (empty($v2['weight'])) 			{$v2['weight'] 			= '';}
							if (empty($v2['image'])) 			{$v2['image'] 			= '';}
							if (empty($v2['image_small']))		{$v2['image_small'] 	= '';}
							if (empty($v2['color'])) 			{$v2['color'] 			= '';}


							$options[] 	= '('.(int)$newId.', '.$db->quote($v2['title']).', '.$db->quote($v2['alias']).', '.$db->quote($v2['operator']).', '.$db->quote($v2['amount']).', '.(int)$v2['stock'].', '.$db->quote($v2['operator_weight']).', '.$db->quote($v2['weight']).', '.$db->quote($v2['image']).', '.$db->quote($v2['image_small']).', '.$db->quote($v2['color']).', '.(int)$defaultValue.')';
							if (!empty($options)) {
								$valuesString2 = implode($options, ',');
							}
						}
						$query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, alias, operator, amount, stock, operator_weight, weight, image, image_small, color, default_value)'
									.' VALUES '.(string)$valuesString2;

						$db->setQuery($query);
						$db->execute();

						// One or more default values removed
						if ($dVR == 1) {
							$msg = JText::_('COM_PHOCACART_ATTRIBUTE'). ': '. $v['title'] . "<br />";
							$msg .= JText::_('COM_PHOCACART_THIS_ATTRIBUTE_DOES_NOT_ALLOW_TO_STORE_MULTIPLE_DEFAULT_VALUES');
							$app->enqueueMessage($msg, 'error');
						}
					}
				}
			}
		}
	}*/

	public static function getAttributesAndOptions($productId) {

		$attributes = array();
		$attributes = self::getAttributesById($productId);

		$attributesKey = array();
		if (!empty($attributes)) {
			foreach($attributes as $k => $v) {
				$attributesKey[$v->id] = $v;
				$options = self::getOptionsById((int)$v->id);
				if (!empty($options)) {
					//$attributes[$k]->options 		= $options;

					$optionsKey = array();
					foreach($options as $k2 => $v2) {
						$optionsKey[$v2->id] = $v2;
					}
					$attributesKey[$v->id]->options = $optionsKey;
				} else {
					//$attributes[$k]->options 		= false;
					$attributesKey[$v->id]->options = false;
				}
			}
		}

		return $attributesKey;
	}



	public static function getAllAttributesAndOptions($ordering = 1, $onlyAvailableProducts = 0, $lang = '') {

		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 5);


		$columns		= 'v.id, v.title, v.alias, v.image, v.image_medium, v.image_small, v.download_folder, v.download_file, v.download_token, v.color, v.default_value, at.id AS attrid, at.title AS attrtitle, at.alias AS attralias, at.type as attrtype';
		$groupsFull		= 'v.id, v.title, v.alias, v.image, v.image_medium, v.image_small, v.download_folder, v.download_file, v.download_token, v.color, v.default_value, attralias, at.id, at.title, at.alias, at.type';
		$groupsFast		= 'v.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

		$wheres		= array();
		$lefts		= array();

		$lefts[] = ' #__phocacart_attributes AS at ON at.id = v.attribute_id';

		if ($onlyAvailableProducts == 1) {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' #__phocacart_products AS p ON at.product_id = p.id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
		} else {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
				$lefts[] = ' #__phocacart_products AS p ON at.product_id = p.id';
			}
		}

		$q = ' SELECT '.$columns
			.' FROM  #__phocacart_attribute_values AS v'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			.' GROUP BY '.$groups
			.' ORDER BY '.$orderingText;

		$db->setQuery($q);
		$attributes = $db->loadObjectList();

		$a	= array();
		if (!empty($attributes)) {
			foreach($attributes as $k => $v) {
				if (isset($v->attrtitle) && $v->attrtitle != ''
				&& isset($v->attrid) && $v->attrid != ''
				&& isset($v->attralias) && $v->attralias != '') {
					$a[$v->attralias]['title']				= $v->attrtitle;
					$a[$v->attralias]['id']					= $v->attrid;
					$a[$v->attralias]['alias']				= $v->attralias;
					$a[$v->attralias]['type']				= $v->attrtype;
					if (isset($v->title) && $v->title != ''
					&& isset($v->id) && $v->id != ''
					&& isset($v->alias) && $v->alias != '') {
						$a[$v->attralias]['options'][$v->alias] = new stdClass();
						$a[$v->attralias]['options'][$v->alias]->title			= $v->title;
						$a[$v->attralias]['options'][$v->alias]->id				= $v->id;
						$a[$v->attralias]['options'][$v->alias]->alias			= $v->alias;
						$a[$v->attralias]['options'][$v->alias]->image			= $v->image;
						$a[$v->attralias]['options'][$v->alias]->image_small	= $v->image_small;
						$a[$v->attralias]['options'][$v->alias]->download_folder= $v->download_folder;
						$a[$v->attralias]['options'][$v->alias]->download_file	= $v->download_file;
						$a[$v->attralias]['options'][$v->alias]->download_token	= $v->download_token;
						$a[$v->attralias]['options'][$v->alias]->color			= $v->color;
						$a[$v->attralias]['options'][$v->alias]->default_value	= $v->default_value;
					} else {
						$a[$v->attralias]['options'] = array();
					}
				}
			}

		}
		return $a;

	}

	public static function getAttributeValue($id, $attributeId) {
		$db =JFactory::getDBO();
		$query = ' SELECT a.id, a.title, a.alias, a.amount, a.operator, a.weight, a.operator_weight, a.stock, a.image, a.image_medium, a.image_small, a.download_folder, a.download_file, a.download_token, a.color, a.default_value,'
		.' aa.id as aid, aa.title as atitle, aa.type as atype'
		.' FROM #__phocacart_attribute_values AS a'
		.' LEFT JOIN #__phocacart_attributes AS aa ON a.attribute_id = aa.id'
		.' WHERE a.id = '.(int)$id . ' AND a.attribute_id = '.(int)$attributeId
		.' ORDER BY a.id'
		.' LIMIT 1';
		$db->setQuery($query);
		$attrib = $db->loadObject();
		return $attrib;

	}

	public static function getAttributeFullValues($attributes) {

		$fullAttributes = array();
		if (!empty($attributes)) {
			foreach ($attributes as $k => $v) {
				$fullAttributes[$k] = new stdClass();
				// Could be set a function to get info about the attribute, for now not needed
				if(!empty($v)) {
					foreach ($v as $k2 => $v2) {
						if ((int)$k > 0 && (int)$k2 > 0) {
							$attrib = PhocacartAttribute::getAttributeValue((int)$k2, (int)$k);
							$fullAttributes[$k]->options[$k2] = $attrib;
						}
					}
				}
			}
		}
		return $fullAttributes;
	}




	/*
	 * Check if attribute is required or not
	 * This is checked when adding products to cart (normally, this should not happen, as html5 input form checking should do it)
	 * Adding products to cart - this is only security check
	 * Checking products before making order - this is only security check
	 * Standard user will not add empty attributes if required because html5 form checking will tell him
	 * This is really only for cases, someone will try to forge the form - server side checking
	 */
	/*public static function checkIfRequired($id, $value) {

		// Multiple value
		if ((int)$id > 0 && is_array($value) && !empty($value)) {

			return true;
		}
		// One value
		if ((int)$id > 0 && (int)$value > 0) {
			return true;// Attribute set and value set too - we don't have anything to check, as attribute value was selected
		}

		if ((int)$id > 0 && (int)$value == 0) {
			$db =JFactory::getDBO();
			$query = ' SELECT a.required'
			.' FROM #__phocacart_attributes AS a'
			.' WHERE a.id = '.(int)$id
			.' ORDER BY a.id'
			.' LIMIT 1';
			$db->setQuery($query);
			$attrib = $db->loadObject();
			if (isset($attrib->required) && $attrib->required == 0) {
				return true;
			} else {
				return false;// seems like attribute is required but not selected
			}
		}

		return false;
	}*/


	/* Check if the product includes some required attribute
	 * If yes, but users tries to add the product without attribute (forgery)
	 * just check it on server side
	 * BE AWARE - this test runs only in case when attributes are empty
	 * We don't check if attribute was selected or not or if is required or not
	 * We didn't get any attribute when ordering this product and we only check
	 * if the product includes some attribute
	 */
	/*public static function checkIfExistsAndRequired($productId) {

		$wheres		= array();
		$wheres[] 	= ' a.id = '.(int)$productId;
		$db 		= JFactory::getDBO();
		$query = ' SELECT a.id,'
		.' at.required AS attribute_required'
		.' FROM #__phocacart_products AS a'
		.' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
		. ' WHERE ' . implode( ' AND ', $wheres )
		. ' ORDER BY a.id'
		. ' LIMIT 1';
		$db->setQuery($query);
		$attrib = $db->loadObject();

		if ((int)$attrib->attribute_required > 0) {
			return false;
		} else {
			return true;
		}

		return false;
	}*/


	public static function getAllRequiredAttributesByProduct($productId) {

		$wheres		= array();
		$wheres[] 	= ' a.id = '.(int)$productId;
		$db 		= JFactory::getDBO();
		$query = ' SELECT at.id, at.type'
		.' FROM #__phocacart_products AS a'
		.' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
		. ' WHERE ' . implode( ' AND ', $wheres )
		. ' ORDER BY a.id';
		$db->setQuery($query);
		$attributes = $db->loadAssocList();

		// correct empty attributes
		if (!empty($attributes)) {
			foreach($attributes as $k => $v) {
				if (!$v['id'] && !$v['type']) {
					unset($attributes[$k]);
				}
			}
		}
		return $attributes;
	}
	/*
	public static function getType($id) {

		switch((int)$id) {
			case 4:
				$type = 2;//Multiple Value - handle array
			break;

			case 1:
			case 2:
			case 3:
			default:
				$type = 1;//Single Value - handle string
			break;
		}
		return $type;
	}*/

	public static function checkRequiredAttributes($id, $attributes) {

		// PHOCARTATTRIBUTE

		// Covert all attribute values from strings to array
		if(!empty($attributes)) {
			foreach($attributes as $k => $v) {
				if(!is_array($v)) {
					$attributes[$k] = array(0 => $v);
				}
			}
		}

		// $attributes - attributes sent per form when adding product to cart
		// $requiredAttributes - all required attributes for selected product
		// Get all required attributes for this product
		$requiredAttributes = PhocacartAttribute::getAllRequiredAttributesByProduct($id);



		$msgA				= array();
		$passAll			= true;
		$passAttribute		= array();



		if (!empty($requiredAttributes)) {
			foreach($requiredAttributes as $k2 => $v2) {

				if (isset($v2['id']) && $v2['id'] > 0) {


					if (!empty($attributes)) {

						foreach ($attributes as $k3 => $v3) {


							if (isset($k3) && (int)$k3 == (int)$v2['id']) {
								$passAttribute[$k3] = 0;

								if (!empty($v3)) {

									foreach($v3 as $k4 => $v4) {

										// EDIT PHOCACARTATTRIBUTE
										if (isset($v2['type']) && ($v2['type'] == 7 || $v2['type'] == 8 || $v2['type'] == 9)) {

											// ATTRIBUTE TYPE = TEXT

											// There is reverse testing to select or checkbox
											// In select or checkbox we can wait for some of the option will be selected
											// but by text all input text fields in one attribute must be required
											if (isset($v4['ovalue']) && urldecode($v4['ovalue'] != '')) {
												// Order product - we found value in order of products - OK
												$passAttribute[$k3] = 1;
												//break 2;
											}else if (!is_array($v4) && urldecode($v4 != '')) {
												// Order product - we found value in order of products - OK
												$passAttribute[$k3] = 1;
												//break 2;
											} else {
												$passAll = false;
												break 2;
											}

										} else {

											// ATTRIBUTE TYPE = CHECKBOX, SELECT

											if (isset($v4['oid']) && $v4['oid'] > 0) {
												// Order product - we found value in order of products - OK
												$passAttribute[$k3] = 1;
												break 2;
											} else if (!is_array($v4) && (int)$v4 > 0) {
												// Add to cart - we found value when adding product to cart - OK
												$passAttribute[$k3] = 1;
												break 2;
											}

										}
										// possible break 3;
									}
								}
							}
						}
					} else {
						$msgA[] = 'No FORM ATTRIBUTE found';
					}
				} else {
					$msgA[] = 'No ID found of REQUIRED ATTRIBUTE';
				}

				// Summarization of passed values
				$aId = (int)$v2['id'];// ID of attribute
				if (isset($passAttribute[$aId]) && $passAttribute[$aId] == 1) {
					// this required attribute is OK
				} else {
					// we didn't found any information - any passed information about this required attribute

					$passAll = false;
				}
			}
		}


		if (!empty($msgA)) {
			//$u = PhocacartUser::getUserInfo();
			//PhocacartLog::add(1, implode(' ', $msgA), $id, 'IP: '. $u['ip'].', User ID: '.$u['id'] . ', User Name: '.$u['username']);
		}
		return $passAll;
	}

	public static function isMultipleAttribute($type) {

		switch ($type) {
			case 4:
			case 5:
			case 6:
				return true;
			break;
			default:
				return false;
			break;
		}
	}


	/* When product is displayed, it has selected the default values
	 * We need to filter all attributes assigned to product so the product only includes selected attributes and otpions
	 * and we can make by this selection productKey
	 */

	public static function getAttributesSelectedOnly($attributes) {

		$sAttributes = array();
		if (!empty($attributes)) {
			foreach ($attributes as $k => $v) {
				if (!empty($v->options)) {
					foreach($v->options as $k2 => $v2) {
						if (isset($v2->default_value) && $v2->default_value == 1) {
							$sAttributes[$k][$v2->id] = $v2->id;
						}
					}
				}
			}
		}
		return $sAttributes;
	}




	public static function sanitizeAttributeArray($attribute) {

		// Sanitanize data and do the same level for all attributes:
		// select attribute = 1
		// checkbox attribute = array(0 => 1, 1 => 1) attribute[]
		$aA = array();
		if (!empty($attribute)) {
			foreach($attribute as $k => $v) {
				if (is_array($v) && !empty($v)) {
					foreach($v as $k2 => $v2) {
						if ((int)$v2 > 0) {
							$aA[(int)$k][(int)$v2] = (int)$v2;
						}
					}
				} else {
					if ((int)$v > 0) {
						$aA[(int)$k][(int)$v] = $v;
					}
				}
			}
		}
		return $aA;

	}


	/*
	 * based on: stackoverflow.com/questions/1256117/algorithm-that-will-take-numbers-or-words-and-find-all-possible-combinations
	 * by Adi Bradfield
	 */

	public static function makeCombination($array, $requiredArray){

		$bits		= count($array); //bits of binary number equal to number of words in query;
		$dec		= 1; //Convert decimal number to binary with set number of bits, and split into array
		$arrayNew 	= array();

		while($dec < pow(2, $bits)) {

			$binary = str_split(str_pad(decbin($dec), $bits, '0', STR_PAD_LEFT));

			$current 					= array();
			$current['title'] 			= '';
			$current['valid'] 			= 1;
			$cannotCobminate 			= array();

			$i = 0;

			while($i < ($bits)){


				if($binary[$i] == 1) {

					$current['product_id'] 		= $array[$i]['pid'];
					$current['product_title'] 	= $array[$i]['ptitle'];

					// Attribute, Option ID
					$aid = $array[$i]['aid'];
					$oid = $array[$i]['oid'];

					// Title
					if (isset($current['title']) && $current['title'] != '') {
						$current['title'] .= ' <span class="ph-attribute-option-item">' . $array[$i]['atitle'] . ': '.$array[$i]['otitle']. '</span>';
					} else {
						$current['title'] = '<span class="ph-attribute-option-item">' . $array[$i]['atitle'] . ': '.$array[$i]['otitle'] . '</span>';
					}


					$current['attributes'][$aid][$oid] = (int)$oid;


					// Options inside one select cannot be combinated togeter
					if (!$array[$i]['multiple']) {
						if (isset($cannotCobminate[$aid]) && $cannotCobminate[$aid] > 0) {
							// there is one option selected from select box,
							// this attribute cannot be combinated in this form
							$current['valid'] = 0;
						} else {
							$cannotCobminate[$aid] = 1;
						}
					}

				}
				$i++;

			}



			// Define

			$key 						= PhocacartProduct::getProductKey($current['product_id'], $current['attributes']);
			$current['product_id'] 		= $current['product_id'];
			$current['product_key'] 	= $key;
			$current['product_title'] 	= $current['product_title'];
			$current['stock'] 			= 0;



			/*
			// DEBUG
			echo "Iteration: $dec <table cellpadding=\"5\" border=\"1\"><tr>";
			foreach($binary as $b){
				echo "<td>$b</td>";
			}
			echo "</tr><tr>";
			foreach($array as $l){
				echo "<td>".$l['otitle']."</td>";
			}
			echo "</tr></table>Output: ";
			foreach($current as $c){
			   // echo $c." ";
			}
			echo "<br><br>";
			*/

			if (!empty($requiredArray)) {
				foreach ($requiredArray as $k => $v) {
					if (!array_key_exists($v, $current['attributes'])) {
						$current['valid'] = 0;
					}
				}
			}


			// Add only such attribute combinations which are possible (two options from select box is not possible)
			if ($current['valid'] == 1) {
				$arrayNew[$key] = $current;
			}
			$dec++;
		}
		return $arrayNew;
	}

	public static function getCombinations($id, $title, $attributes, &$combinations = array()) {


		$array 			= array();
		$requiredArray 	= array();


		if (!empty($attributes)) {
			ksort($attributes);
			$i = 0;
			foreach($attributes as $k => $v) {
				if (!empty($v->options)) {
					ksort($v->options);
					foreach ($v->options as $k2 => $v2) {
						$array[$i]['pid'] 		= $id;
						$array[$i]['ptitle'] 	= $title;
						$array[$i]['aid'] 		= $v->id;
						$array[$i]['atitle'] 	= $v->title;
						$array[$i]['oid'] 		= $v2->id;
						$array[$i]['otitle'] 	= $v2->title;
						$array[$i]['multiple'] 	= self::isMultipleAttribute($v->type);
						$array[$i]['required'] 	= $v->required;
						$i++;
					}
				}
				if ($v->required == 1) {
					$requiredArray[] = $v->id;
				}
			}
		}



		// All combinations of attributes
		$pA = self::makeCombination($array, $requiredArray);

		// Add to the array product itself (product without any variation)
		// Only in case, there is no required attribute
		$pI 	= array();
		if (empty($requiredArray)) {
			$pIPK 	= $id. '::';
			$pI[$pIPK]['title'] 			= '('.JText::_('COM_PHOCACART_NO_ATTRIBUTES'). ')';
			$pI[$pIPK]['cannotcombinate'] 	= 0;
			$pI[$pIPK]['product_id'] 		= $id;
			$pI[$pIPK]['product_title'] 	= $title;
			$pI[$pIPK]['attributes'] 		= array();
			$pI[$pIPK]['product_key'] 		= $pIPK;
			$pI[$pIPK]['stock'] 			= 0;
		}

		$combinations = array_merge($pI, $pA);
		return true;

	}

	public static function getCombinationsStockByProductId($id) {
		if ($id > 0) {
			$db 		= JFactory::getDBO();
			$query = ' SELECT a.product_id, a.product_key, a.stock'
			.' FROM #__phocacart_product_stock AS a'
			. ' WHERE a.product_id = '.(int)$id
			. ' ORDER BY a.id';
			$db->setQuery($query);
			$combinations = $db->loadAssocList();

			$combinationsNew = array();
			if (!empty($combinations)) {
				foreach ($combinations as $k => $v) {
					$newK = $v['product_key'];
					$combinationsNew[$newK] = $v;
				}
			}
			return $combinationsNew;
		}
		return false;
	}

	public static function getCombinationsStockByKey($productKey) {

		$db = JFactory::getDBO();

		$query = 'SELECT stock'
				.' FROM #__phocacart_product_stock'
			    .' WHERE product_key = '. $db->quote($productKey)
				.' ORDER BY product_key'
				.' LIMIT 1';
		$db->setQuery($query);
		$stock = $db->loadResult();

		if (isset($stock) && $stock > 0) {
			return $stock;
		}

		return 0;
	}


	public static function getCombinationsStockById($productId, $returnArray = 0) {

		$db = JFactory::getDBO();

		$query = 'SELECT a.id, a.product_id, a.product_key, a.stock'
				.' FROM #__phocacart_product_stock AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$items = $db->loadAssocList();
		} else {
			$items = $db->loadObjectList();
		}

		return $items;
	}

	public static function storeCombinationsById($productId, $aosArray, $new = 0) {

		if ((int)$productId > 0) {
			$db =JFactory::getDBO();


			$notDeleteItems = array();


			if (!empty($aosArray)) {
				$values 	= array();
				foreach($aosArray as $k => $v) {

					// correct simple xml
					if (empty($v['product_id'])) 		{$v['product_id'] 		= '';}
					if (empty($v['product_key'])) 		{$v['product_key'] 		= '';}
					if (empty($v['stock'])) 			{$v['stock'] 			= '';}

					if ($v['product_key'] == '') {
						continue;
					}

					$idExists = 0;

					if ($new == 0) {
						if (isset($v['id']) && $v['id'] > 0) {

							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_product_stock'
							.' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();

						}
					}

					if ((int)$idExists > 0) {

						$query = 'UPDATE #__phocacart_product_stock SET'
						.' product_id = '.(int)$productId.','
						.' product_key = '.$db->quote($v['product_key']).','
						.' stock = '.(int)$v['stock'].','
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();

						$newIdD 				= $idExists;

					} else {

						$values 	= '('.(int)$productId.', '.$db->quote($v['product_key']).', '.(int)$v['stock'].')';


						$query = ' INSERT INTO #__phocacart_product_stock (product_id, product_key, stock)'
								.' VALUES '.$values;
						$db->setQuery($query);
						$db->execute();

						$newIdD = $db->insertid();
					}

					$notDeleteItems[]	= $newIdD;
				}
			}
			// Remove all discounts except the active
			if (!empty($notDeleteItems)) {
				$notDeleteItemsString = implode($notDeleteItems, ',');
				$query = ' DELETE '
						.' FROM #__phocacart_product_stock'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteItemsString.')';

			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_stock'
						.' WHERE product_id = '. (int)$productId;
			}
			$db->setQuery($query);
			$db->execute();
		}
	}


	public static function getAttributeType($id) {

		$db 		= JFactory::getDBO();
		$wheres		= array();
		$wheres[] 	= ' id = '.(int)$id;
		$query = ' SELECT type'
		.' FROM #__phocacart_attributes'
		. ' WHERE ' . implode( ' AND ', $wheres )
		. ' ORDER BY id LIMIT 1';
		$db->setQuery($query);
		$type = $db->loadResult();

		return $type;
	}

	public static function getAttributeOptionDownloadFilesByOrder($orderId, $productId, $orderProductId) {

		$db 		= JFactory::getDBO();
		$wheres		= array();
		$wheres[] 	= ' oa.order_id = '.(int)$orderId;
		$wheres[] 	= ' oa.product_id = '.(int)$productId;
		$wheres[] 	= ' oa.order_product_id = '.(int)$orderProductId;

		// only order_option_id not order_attribute_id - as stored is info about ordered option, not about ordered attribute
		$q = ' SELECT av.download_folder, av.download_file, av.download_token, a.id AS attribute_id, av.id AS option_id, oa.id AS order_option_id, av.title AS option_title, a.title AS attribute_title'
			.' FROM #__phocacart_attribute_values AS av'
			.' LEFT JOIN #__phocacart_attributes AS a ON a.id = av.attribute_id'
			.' LEFT JOIN #__phocacart_order_attributes AS oa ON oa.option_id = av.id'
			.' WHERE ' . implode( ' AND ', $wheres )
			.' ORDER BY av.id';
		$db->setQuery($q);
		$files = $db->loadAssocList();
		return $files;

	}

	/*
	 * Done in PhocacartProduct::getProductKey();
	 *
	public static function correctArrtibutesFormat(&$attributes) {
		if (!empty($attributes)) {
			foreach ($attributes as $k => $v) {
				if (!is_array($v)) {
					$attributes[$k] = array($v => $v);
				}
			}
		}
	}*/


/*	public static function storeOptionsByAttributeId($attributeId, $optArray) {

		if ((int)$attributeId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_attribute_values'
					. ' WHERE attribute_id = '. (int)$attributeId;
			$db->setQuery($query);

			if (!empty($optArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($optArray as $k => $v) {
					if (isset($v['title']) && $v['title'] != ''  && isset($v['amount']) && isset($v['operator'])) {
						$values[] = ' ('.(int)$attributeId.', \''.$v['title'].'\', \''.$v['operator'].'\', \''.(float)$v['amount'].'\')';
					}
				}

				if (!empty($values)) {
					$valuesString = implode($values, ',');

					$query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, operator, amount)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);

				}
			}
		}
	}

	public static function getAllAttributesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {

		$db = JFactory::getDBO();
		$query = 'SELECT a.id AS value, CONCAT(a.title_attribute,\' (\', a.title,  \')\') AS text'
				.' FROM #__phocacart_attributes AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);

		$attributes = $db->loadObjectList();

		$attributesO = JHtml::_('select.genericlist', $attributes, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

		return $attributesO;
	}

	*/
}
?>
