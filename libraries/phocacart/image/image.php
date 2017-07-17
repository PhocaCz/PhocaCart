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

class PhocacartImage
{
	public static function getThumbnailName($path, $filename, $size) {
		
		$thumbName	= new StdClass();
		if ($filename == '') {
			$thumbName->abs = false;
			$thumbName->rel	= false;
			return $thumbName;
		}
		
		$title 		= self::getTitleFromFile($filename , 1);
		switch ($size) {
			case 'large':
			$fileNameThumb 	= 'phoca_thumb_l_'. $title;
			$thumbName->abs	= JPath::clean(str_replace($title, 'thumbs/' . $fileNameThumb, $path['orig_abs_ds'] . $filename));
			$thumbName->rel	= str_replace ($title, 'thumbs/' . $fileNameThumb, $path['orig_rel_ds'] . $filename);
			break;

			case 'medium':
			$fileNameThumb 	= 'phoca_thumb_m_'. $title;
			$thumbName->abs	= JPath::clean(str_replace($title, 'thumbs/' . $fileNameThumb, $path['orig_abs_ds'] . $filename));
			$thumbName->rel	= str_replace ($title, 'thumbs/' . $fileNameThumb, $path['orig_rel_ds'] . $filename);
			break;
			
			default:
			case 'small':
			$fileNameThumb 	= 'phoca_thumb_s_'. $title;
			$thumbName->abs	= JPath::clean(str_replace($title, 'thumbs/' . $fileNameThumb, $path['orig_abs_ds'] . $filename));
			$thumbName->rel	= str_replace ($title, 'thumbs/' . $fileNameThumb, $path['orig_rel_ds'] . $filename);
			break;	
		}
		return $thumbName;
	}
	
	public static function getTitleFromFile(&$filename, $displayExt = 0) {
		
		$filename 			= str_replace('//', '/', $filename);
		//$filename			= str_replace(DS, '/', $filename);
		$folderArray		= explode('/', $filename);
		$countFolderArray	= count($folderArray);
		$lastArrayValue 	= $countFolderArray - 1;
		
		$title = new stdClass();
		$title->with_extension 		= $folderArray[$lastArrayValue];
		$title->without_extension	= self::removeExtension($folderArray[$lastArrayValue]);
		
		if ($displayExt == 1) {
			return $title->with_extension;
		} else if ($displayExt == 0) {
			return $title->without_extension;
		} else {
			return $title;
		}
	}
	
	public static function removeExtension($filename) {
		return substr($filename, 0, strrpos( $filename, '.' ));
	}

	public static function getJpegQuality($jpegQuality) {
		if ((int)$jpegQuality < 0) {
			$jpegQuality = 0;
		}
		if ((int)$jpegQuality > 100) {
			$jpegQuality = 100;
		}
		return $jpegQuality;
	}
	
	public static function getAdditionalImages($itemId) {
		$db = JFactory::getDBO();
		$query = 'SELECT i.image FROM #__phocacart_product_images AS i'
				.' LEFT JOIN #__phocacart_products AS p ON p.id = i.product_id'
			    .' WHERE p.id = '.(int) $itemId
				.' ORDER BY i.image';
		$db->setQuery($query);
		$images = $db->loadObjectList();
		
		return $images;
	}
	
	public static function getImage($image, $path = '', $width = '', $height = '') {
		
		if (JFile::exists(JPATH_ROOT.'/'.$image)) {
			$style = ' style="';
			if ($width = '') {
				$style .= 'width: '.$width.';'; 
			}
			if ($width = '') {
				$style .= 'height: '.$width.';'; 
			}
			$style = '" ';
			
			if ($path != '') {
				$path = $path . '/';
			}

			return '<img src="'.JURI::root(true) .'/'. $path . $image.'"'.$style.'alt=""/>';
		} else {
			return false;
		}
	}
}
?>