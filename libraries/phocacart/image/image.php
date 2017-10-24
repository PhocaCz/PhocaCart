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
	
	/*
	 * Attribute was selected by vendor in administration - it is default attribute
	 * Displayed in category or item view when the page is loaded, the default attribute image is displayed
	 */
	
	public static function getImageChangedByAttributes($attributes, $imageSize) {
		
		
		$paramsC 					= JComponentHelper::getParams('com_phocacart');
		$display_attribute_image	= $paramsC->get( 'display_attribute_image', 1 );
		
		if ($display_attribute_image == 0) {
			return '';
		}
		
		$image = '';
		if (!empty($attributes)) {
			foreach ($attributes as $k => $v) {
				if (!empty($v->options)) {
					foreach($v->options as $k2 => $v2) {
						
						// Is the options set as default
						if (isset($v2->default_value) && $v2->default_value == 1) {
							
							switch($imageSize) {
								
								case 'small': 
									if (isset($v2->image_small) &&  $v2->image_small != '') {
										$image = $v2->image_small;
										break 2;
									}
								
								break;
								case 'medium':
								// !!!!
								// In options when we select image, it has 3 thumbnails
								// so $v2->image has three thumbnails, we ask for image and then we transform to thumbnail we need
								/*case 'medium':
									if (isset($v2->image_medium) &&  $v2->image_medium != '') {
										$image = $v2->image_medium;
										break 2;
									}
								break;*/
								case 'large':
								default:
									if (isset($v2->image) &&  $v2->image != '') {
										$image = $v2->image;
										
										break 3;
									}
								break;
								
							}

						}
					}
					
				}
			}
		}
	
		return $image;
	}
	
	/*
	 * Attribute was selected by customer and product added to cart (displayed in checkout)
	 * Displayed in checkout or cart view when the page is loaded, the selected attribute by customer is displayed
	 */
	
	public static function getImageChangedBySelectedAttribute($attributes, $imageSize) {
		
		$paramsC 							= JComponentHelper::getParams('com_phocacart');
		$display_attribute_image_checkout	= $paramsC->get( 'display_attribute_image_checkout', 1 );
		
		if ($display_attribute_image_checkout == 0) {
			return '';
		}
		
		$image = '';
		if (!empty($attributes)) {
			foreach ($attributes as $k => $v) {
				if (!empty($v)) {
					foreach($v as $k2 => $v2) {
						
							
						switch($imageSize) {
							
							case 'small':
								if (isset($v2['oimagesmall']) &&  $v2['oimagesmall'] != '') {
									$image = $v2['oimagesmall'];
									break 2;
								}
								
							break;
							case 'medium':

							case 'large':
							default:
								if (isset($v2['oimage']) &&  $v2['oimage'] != '') {
									$image = $v2['oimage'];
									break 3;
								}
							break;
								
						}
							
					}
					
				}
			}
		}
		
		return $image;
		
	}
	
	// posible feature request
	/*
	 * 
	 * Get ID key by attributes (when vender set default value of attribute in administration)
	 *	$thinAttributes = PhocacartAttribute::getAttributesSelectedOnly($this->t['attr_options']);
	 *	$idkey = PhocacartProduct::getProductKey($x->id, $thinAttributes);
	 * When customer add the product with selected attributes to cart, the idkey is known
	 * 
	public static function getImageChangedBySelectedAttributeAdvancedStockManagement($idKey, $imageSize) {
		// Ask jos_phocacart_product_stock table for image by product idKey
		 
		 
		
	}
	*/
	
	// Image
	// 1. Display default image
	// 2. Store the information about default image into data-default-image because when we change images by selecting attributes
	//    and at the end we deselect all the attributes, we should get back to default image
	// 3. If there is some attribute option selected as default, display the image of this attribute options so the image will be
	//    loaded at start
	// But when we load an attribute image at start and user deselect attributes we need to go back to default image (we didn't load
	// it in image src tag but we loaded it in data-default-image attribute
	//
	// Image2 is the image which is changed when hovering over image box
	
	public static function getImageDisplay($image, $imageAdditional, $pathItem, $switchImage, $width, $height, $imageSize, $layoutType, $attributesOptions, $attributesOptionsType = 1) {
		
		
		$imageA 			= array();
		
		if ($imageSize != '') {
			$imageA['size']		= $imageSize;
		} else {
			$imageA['size']		= $layoutType == 'gridlist' ? 'large' : 'medium';
		}
		
		
		$imageA['image'] 	= PhocacartImage::getThumbnailName($pathItem, $image, $imageA['size']);
		$imageA['default']	= $imageA['image'];
		
		if ($attributesOptionsType == 1) {
			// Default attributes - set by vendor in administration (default value of attributes)
			$imageAttributes 	= PhocaCartImage::getImageChangedByAttributes($attributesOptions, 'large');
			
		} else {
			// Selected attributes - selected by customer in e-shop - displayed e.g. in cart or checkout
			// the size is always large:
			// Attribute Image Large (normal image with small/medium/large thumbnails)
			// Attribute Image Small (small icon used e.g. for module or as click button)
			$imageAttributes 	= PhocaCartImage::getImageChangedBySelectedAttribute($attributesOptions, 'large');
		}
		
		if ($imageAttributes != '') {
			$imageA['image'] = PhocacartImage::getThumbnailName($pathItem, $imageAttributes, $imageA['size']);
			
		}
		
		$imageA['second'] 	= false;
		$imageA['phil'] 	= 'phIL-not-active';
		
		if ($switchImage == 1 && $imageAdditional != '') {
			$iAI = explode(',', $imageAdditional);
			if (isset($iAI[0]) && $iAI[0] != '') {
				$imageA['second'] = PhocacartImage::getThumbnailName($pathItem, $iAI[0], $imageA['size']);
				if (isset($imageA['second']->rel) && $imageA['second']->rel != '') {
					$imageA['phil'] = 'phIL';
				}
			}
		}
		
		$imageA['style'] = '';
		
		if (isset($width) && $width != '' && isset($height) && $height != '') {
			$imageA['style'] = 'style="width:'.$width.';height:'.$height.'"';
		}
		
		return $imageA;
		
	}
}
?>