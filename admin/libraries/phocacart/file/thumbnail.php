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
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
/*
phocacart import('phocacart.path.path');
phocacart import('phocacart.file.file');
*/

class PhocacartFileThumbnail
{
	/*
	 *Get thumbnailname
	 */
	public static function getThumbnailName($filename, $size, $manager) {

		$path		= PhocacartPath::getPath($manager);
		$title 		= PhocacartFile::getTitleFromFilenameWithExt($filename);

		$thumbName	= new JObject();

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
			$thumbName->abs	= JPath::clean(str_replace($title, 'thumbs/'  . $fileNameThumb, $path['orig_abs_ds'] . $filename));
			$thumbName->rel	= str_replace ($title, 'thumbs/' . $fileNameThumb, $path['orig_rel_ds'] . $filename);
			break;
		}

		$thumbName->rel = str_replace('//', '/', $thumbName->rel);

		return $thumbName;
	}

	public static function deleteFileThumbnail ($filename, $small=0, $medium=0, $large=0, $manager) {

		if ($small == 1) {
			$fileNameThumbS = PhocacartFileThumbnail::getThumbnailName ($filename, 'small', $manager);
			if (JFile::exists($fileNameThumbS->abs)) {
				JFile::delete($fileNameThumbS->abs);
			}

			//WEBP COPY
			$webpCopyS = PhocacartFile::changeFileExtension($fileNameThumbS->abs, 'webp');
			if (JFile::exists($webpCopyS)) {
				JFile::delete($webpCopyS);
			}
		}

		if ($medium == 1) {
			$fileNameThumbM = PhocacartFileThumbnail::getThumbnailName ($filename, 'medium', $manager);
			if (JFile::exists($fileNameThumbM->abs)) {
				JFile::delete($fileNameThumbM->abs);
			}

			//WEBP COPY
			$webpCopyM = PhocacartFile::changeFileExtension($fileNameThumbM->abs, 'webp');
			if (JFile::exists($webpCopyM)) {
				JFile::delete($webpCopyM);
			}
		}

		if ($large == 1) {
			$fileNameThumbL = PhocacartFileThumbnail::getThumbnailName ($filename, 'large', $manager);
			if (JFile::exists($fileNameThumbL->abs)) {
				JFile::delete($fileNameThumbL->abs);
			}

			//WEBP COPY
			$webpCopyL = PhocacartFile::changeFileExtension($fileNameThumbL->abs, 'webp');
			if (JFile::exists($webpCopyL)) {
				JFile::delete($webpCopyL);
			}
		}
		return true;
	}

	/*
	 * Main Thumbnail creating function
	 *
	 * file 		= abc.jpg
	 * fileNo	= folder/abc.jpg
	 * if small, medium, large = 1, create small, medium, large thumbnail
	 */
	public static function getOrCreateThumbnail($fileNo, $refreshUrl, $small = 0, $medium = 0, $large = 0, $frontUpload = 0, $manager) {

	/*	if ($frontUpload) {
			$returnFrontMessage = '';
		}*/
		$returnFrontMessage = '';

		$onlyThumbnailInfo = 0;
		if ($small == 0 && $medium == 0 && $large == 0) {
			$onlyThumbnailInfo = 1;
		}

		$app		= JFactory::getApplication();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		//$additional_thumbnails 					= $paramsC->get( 'additional_thumbnails',0 );

		$path 									= PhocacartPath::getPath($manager);
		$origPathServer 						= str_replace('\\', '/', $path['orig_abs_ds']);
		$file['name']							= PhocacartFile::getTitleFromFilenameWithExt($fileNo);
		$file['name_no']						= ltrim($fileNo, '/');
		$file['name_original_abs']				= PhocacartFile::getFileOriginal($fileNo, 0, $manager);
		$file['name_original_rel']				= PhocacartFile::getFileOriginal($fileNo, 1, $manager);
		$file['path_without_file_name_original']= str_replace($file['name'], '', $file['name_original_abs']);
		$file['path_without_file_name_thumb']	= str_replace($file['name'], '', $file['name_original_abs'] . 'thumbs' . '/');
		//$file['path_without_name']				= str_replace('\\', '/', JPath::clean($origPathServer));
		//$file['path_with_name_relative_no']		= str_replace($origPathServer, '', $file['name_original']);
		/*
		$file['path_with_name_relative']		= $path['orig_rel_ds'] . str_replace($origPathServer, '', $file['name_original']);
		$file['path_with_name_relative_no']		= str_replace($origPathServer, '', $file['name_original']);

		$file['path_without_name']				= str_replace('\\', '/', JPath::clean($origPath.'/'));
		$file['path_without_name_relative']		= $path['orig_rel_ds'] . str_replace($origPathServer, '', $file['path_without_name']);
		$file['path_without_name_relative_no']	= str_replace($origPathServer, '', $file['path_without_name']);
		$file['path_without_name_thumbs'] 		= $file['path_without_name'] .'thumbs';
		$file['path_without_file_name_original'] 			= str_replace($file['name'], '', $file['name_original']);
		$file['path_without_name_thumbs_no'] 	= str_replace($file['name'], '', $file['name_original'] .'thumbs');*/


		$ext = strtolower(JFile::getExt($file['name']));
		switch ($ext) {
			case 'jpg':
			case 'png':
			case 'gif':
			case 'jpeg':
            case 'webp':

			//Get File thumbnails name

			$thumbNameS 					= PhocacartFileThumbnail::getThumbnailName ($fileNo, 'small', $manager);
			$file['thumb_name_s_no_abs'] 	= $thumbNameS->abs;
			$file['thumb_name_s_no_rel'] 	= $thumbNameS->rel;


			$thumbNameM						= PhocacartFileThumbnail::getThumbnailName ($fileNo, 'medium', $manager);
			$file['thumb_name_m_no_abs'] 	= $thumbNameM->abs;
			$file['thumb_name_m_no_rel'] 	= $thumbNameM->rel;

			$thumbNameL						= PhocacartFileThumbnail::getThumbnailName ($fileNo, 'large', $manager);
			$file['thumb_name_l_no_abs'] 	= $thumbNameL->abs;
			$file['thumb_name_l_no_rel'] 	= $thumbNameL->rel;


			// Don't create thumbnails from watermarks...
			$dontCreateThumb	= PhocacartFileThumbnail::dontCreateThumb($file['name']);
			if ($dontCreateThumb == 1) {
				$onlyThumbnailInfo = 1; // WE USE $onlyThumbnailInfo FOR NOT CREATE A THUMBNAIL CLAUSE
			}

			// We want only information from the pictures OR
			if ( $onlyThumbnailInfo == 0 ) {

				$thumbInfo = $fileNo;
				//Create thumbnail folder if not exists
				$errorMsg = 'ErrorCreatingFolder';
				$creatingFolder = PhocacartFileThumbnail::createThumbnailFolder($file['path_without_file_name_original'], $file['path_without_file_name_thumb'], $errorMsg );

				switch ($errorMsg) {
					case 'Success':
					case 'DisabledThumbCreation':
					break;

					//case 'ErrorCreatingFolder':
					//	return JText::_('COM_PHOCACART_ERROR_CREATING_THUMBS_FOLDER'); We can test to create the folder directly with file
					default:
						$returnFrontMessage = $errorMsg;
					break;
				}

				// Folder must exist
				if (JFolder::exists($file['path_without_file_name_thumb'])) {

					$errorMsgS = $errorMsgM = $errorMsgL = '';
					//Small thumbnail
					if ($small == 1) {
						PhocacartFileThumbnail::createFileThumbnail($file['name_original_abs'], $thumbNameS->abs, 'small', $frontUpload, $manager, $errorMsgS);
					} else {
						$errorMsgS = 'ThumbnailExists';// in case we only need medium or large, because of if clause bellow
					}

					//Medium thumbnail
					if ($medium == 1) {
						PhocacartFileThumbnail::createFileThumbnail($file['name_original_abs'], $thumbNameM->abs, 'medium', $frontUpload, $manager, $errorMsgM);
					} else {
						$errorMsgM = 'ThumbnailExists'; // in case we only need small or large, because of if clause bellow
					}

					//Large thumbnail
					if ($large == 1) {
						PhocacartFileThumbnail::createFileThumbnail($file['name_original_abs'], $thumbNameL->abs, 'large', $frontUpload, $manager, $errorMsgL);
					} else {
						$errorMsgL = 'ThumbnailExists'; // in case we only need small or medium, because of if clause bellow
					}

					// Error messages for all 3 thumbnails (if the message contains error string, we got error
					// Other strings can be:
					// - ThumbnailExists  - do not display error message nor success page
					// - OnlyInformation - do not display error message nor success page
					// - DisabledThumbCreation - do not display error message nor success page

					$creatingSError = $creatingMError = $creatingLError = false;
					$creatingSError = preg_match("/Error/i", $errorMsgS);
					$creatingMError = preg_match("/Error/i", $errorMsgM);
					$creatingLError = preg_match("/Error/i", $errorMsgL);



					// There is an error while creating thumbnail in m or in s or in l
					if ($creatingSError || $creatingMError || $creatingLError) {
						// if all or two errors appear, we only display the last error message
						// because the errors in this case is the same
						if ($errorMsgS != '') {
							$creatingError = $errorMsgS;
						}
						if ($errorMsgM != '') {
							$creatingError = $errorMsgM;
						}
						if ($errorMsgL != '') {
							$creatingError = $errorMsgL;
						}
						// because the errors in this case is the same

						$returnFrontMessage = JText::_('COM_PHOCACART_ERROR_CREATING_THUMBNAIL') . ' (' . $creatingError . ')';
					} else if ($errorMsgS == '' && $errorMsgM == '' && $errorMsgL == '') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == '' && $errorMsgM == '' && $errorMsgL == 'ThumbnailExists') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == '' && $errorMsgM == 'ThumbnailExists' && $errorMsgL == 'ThumbnailExists') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == '' && $errorMsgM == 'ThumbnailExists' && $errorMsgL == '') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == 'ThumbnailExists' && $errorMsgM == 'ThumbnailExists' && $errorMsgL == '') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == 'ThumbnailExists' && $errorMsgM == '' && $errorMsgL == '') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == 'ThumbnailExists' && $errorMsgM == '' && $errorMsgL == 'ThumbnailExists') {
						$returnFrontMessage = 'Success';
					} else if ($errorMsgS == 'ThumbnailExists' && $errorMsgM == 'ThumbnailExists' && $errorMsgL == 'ThumbnailExists') {
						$returnFrontMessage = 'SuccessThumbnailExists';
					}

					if ($returnFrontMessage == 'Success') {
						return '<span class="ph-result-txt ph-success-txt">' . JText::_('COM_PHOCACART_THUMBNAIL_CREATED'). '</span>';
					} else if ($returnFrontMessage == 'SuccessThumbnailExists') {
						return '<span class="ph-result-txt ph-success-txt">' . JText::_('COM_PHOCACART_THUMBNAIL_EXISTS') . '</span>';
					} else {
						return '<span class="ph-result-txt ph-error-txt">' . $returnFrontMessage . '</span>';
					}
				}
			}
			break;
		}
		return $file;
	}

	public static function dontCreateThumb ($filename) {
		$dontCreateThumb		= false;
		$dontCreateThumbArray	= array ('watermark-large.png', 'watermark-medium.png');
		foreach ($dontCreateThumbArray as $key => $value) {
			if (strtolower($filename) == strtolower($value)) {
				return true;
			}
		}
		return false;
	}

	public static function createThumbnailFolder($folderOriginal, $folderThumbnail, &$errorMsg) {

		$app		= JFactory::getApplication();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$enable_thumb_creation = $paramsC->get( 'enable_thumb_creation', 1 );
		$folder_permissions = $paramsC->get( 'folder_permissions', 0755 );
		//$folder_permissions = octdec((int)$folder_permissions);

		// disable or enable the thumbnail creation
		if ($enable_thumb_creation == 1) {

			if (JFolder::exists($folderOriginal)) {
				if (strlen($folderThumbnail) > 0) {
					$folderThumbnail = JPath::clean($folderThumbnail);
					if (!JFolder::exists($folderThumbnail) && !JFile::exists($folderThumbnail)) {
						switch((int)$folder_permissions) {
							case 777:
								JFolder::create($folderThumbnail, 0777 );
							break;
							case 705:
								JFolder::create($folderThumbnail, 0705 );
							break;
							case 666:
								JFolder::create($folderThumbnail, 0666 );
							break;
							case 644:
								JFolder::create($folderThumbnail, 0644 );
							break;
							case 755:
							Default:
								JFolder::create($folderThumbnail, 0755 );
							break;
						}

						//JFolder::create($folderThumbnail, $folder_permissions );
						if (isset($folderThumbnail)) {
							$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
							JFile::write($folderThumbnail."/index.html", $data);
						}
						// folder was not created
						if (!JFolder::exists($folderThumbnail)) {
							$errorMsg = 'ErrorCreatingFolder';
							return false;
						}
					}
				}
			}
			$errorMsg = 'Success';

			return true;
		} else {
			$errorMsg = 'DisabledThumbCreation';
			return false; // User have disabled the thumbanil creation e.g. because of error
		}
	}

	public static function createFileThumbnail($fileOriginal, $fileThumbnail, $size, $frontUpload=0, $manager, &$errorMsg) {

		$app		= JFactory::getApplication();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$enable_thumb_creation 		= $paramsC->get( 'enable_thumb_creation', 1);
		$watermarkParams['create']	= $paramsC->get( 'create_watermark', 0 );// Watermark
		$watermarkParams['x'] 		= $paramsC->get( 'watermark_position_x', 'center' );
		$watermarkParams['y']		= $paramsC->get( 'watermark_position_y', 'middle' );
		$crop_thumbnail				= $paramsC->get( 'crop_thumbnail', 5);// Crop or not
		$create_webp_copy			= $paramsC->get( 'create_webp_copy', 0);
		$crop 						= null;

		switch ($size) {
			case 'small':
				if ($crop_thumbnail == 3 || $crop_thumbnail == 5 || $crop_thumbnail == 6 || $crop_thumbnail == 7 ) {
					$crop = 1;
				}
			break;

			case 'medium':
				if ($crop_thumbnail == 2 || $crop_thumbnail == 4 || $crop_thumbnail == 5 || $crop_thumbnail == 7 ) {
					$crop = 1;
				}
			break;

			case 'large':
			Default:
				if ($crop_thumbnail == 1 || $crop_thumbnail == 4 || $crop_thumbnail == 6 || $crop_thumbnail == 7 ) {
					$crop = 1;
				}
			break;
		}

		// disable or enable the thumbnail creation
		if ($enable_thumb_creation == 1) {
			$fileResize	= PhocacartFileThumbnail::getThumbnailResize($size);

			if (JFile::exists($fileOriginal)) {
				//file doesn't exist, create thumbnail

				$doThumbnail = false;
				if(!JFile::exists($fileThumbnail)) {
					$doThumbnail = true;
				}

				// WEBP COPY
				if($create_webp_copy && !JFile::exists(PhocacartFile::changeFileExtension($fileThumbnail, 'webp'))) {
					$doThumbnail = true;
					self::deleteFileThumbnail($fileOriginal, 1, 1, 1, $manager);
				}


				if ($doThumbnail) {
					$errorMsg = 'Error4';
					//Don't do thumbnail if the file is smaller (width, height) than the possible thumbnail
					list($width, $height) = GetImageSize($fileOriginal);
					//larger
					/*phocacart import('phocacart.image.imagemagic');*/
					if ($width > $fileResize['width'] || $height > $fileResize['height']) {

						$imageMagic = PhocacartImageMagic::imageMagic($fileOriginal, $fileThumbnail, $fileResize['width'] , $fileResize['height'], $crop, null, $watermarkParams, $frontUpload, $manager, $errorMsg);

					} else {
						$imageMagic = PhocacartImageMagic::imageMagic($fileOriginal, $fileThumbnail, $width , $height, $crop, null, $watermarkParams, $frontUpload, $manager, $errorMsg);
					}
					if ($imageMagic) {
						return true;
					} else {
						return false;// error Msg will be taken from imageMagic
					}
				} else {
					$errorMsg = 'ThumbnailExists';//thumbnail exists
					return false;
				}
			} else {
				$errorMsg = 'ErrorFileOriginalNotExists';
				return false;
			}
			$errorMsg = 'Error3';
			return false;
		} else {
			$errorMsg = 'DisabledThumbCreation'; // User have disabled the thumbanil creation e.g. because of error
			return false;
		}
	}

	public static function getThumbnailResize($size = 'all') {

		// Get width and height from Default settings
		$params 			= PhocacartUtils::getComponentParameters();
		$large_image_width 	= $params->get( 'large_image_width', 640 );
		$large_image_height = $params->get( 'large_image_height', 480 );
		$medium_image_width = $params->get( 'medium_image_width', 300 );
		$medium_image_height= $params->get( 'medium_image_height', 200 );
		$small_image_width 	= $params->get( 'small_image_width', 180 );
		$small_image_height = $params->get( 'small_image_height', 120 );

		switch ($size) {
			case 'large':
			$fileResize['width']	=	$large_image_width;
			$fileResize['height']	=	$large_image_height;
			break;

			case 'medium':
			$fileResize['width']	=	$medium_image_width;
			$fileResize['height']	=	$medium_image_height;
			break;

			case 'small':
			$fileResize['width']	=	$small_image_width;
			$fileResize['height']	=	$small_image_height;
			break;


			Default:
			case 'all':
			$fileResize['smallwidth']	=	$small_width;
			$fileResize['smallheight']	=	$small_height;
			$fileResize['mediumwidth']	=	$medium_width;
			$fileResize['mediumheight']	=	$medium_height;
			$fileResize['largewidth']	=	$large_width;
			$fileResize['largeheight']	=	$large_height;
			break;
		}
		return $fileResize;
	}
}
?>
