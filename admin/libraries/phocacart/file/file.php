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

class PhocacartFile
{
	/*
	 * http://aidanlister.com/repos/v/function.size_readable.php
	 */
	public static function getFileSizeReadable ($size, $retstring = null, $onlyMB = false) {

		if ($onlyMB) {
			$sizes = array('B', 'kB', 'MB');
		} else {
			$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        }


		if ($retstring === null) { $retstring = '%01.2f %s'; }
        $lastsizestring = end($sizes);

        foreach ($sizes as $sizestring) {
                if ($size < 1024) { break; }
                if ($sizestring != $lastsizestring) { $size /= 1024; }
        }

        if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
        return sprintf($retstring, $size, $sizestring);
	}


	public static function getFileSize($manager, $filename, $readable = 1) {

		$path			= PhocacartPath::getPath($manager);
		$fileNameAbs	= JPath::clean($path['orig_abs'] . '/' . $filename);

		if ($readable == 1) {
			return self::getFileSizeReadable(filesize($fileNameAbs));
		} else {
			return filesize($fileNameAbs);
		}
	}

	public static function getFileTime($manager, $filename, $function, $format = "d. M Y") {

		$path			= PhocaDownloadPath::getPath($manager);
		$fileNameAbs	= JPath::clean($path['orig_abs'] . '/' . $filename);
		if (JFile::exists($fileNameAbs)) {
			switch($function) {
				case 2:
					$fileTime = filectime($fileNameAbs);
				break;
				case 3:
					$fileTime = fileatime($fileNameAbs);
				break;
				case 1:
				default:
					$fileTime = filemtime($fileNameAbs);
				break;
			}

			$fileTime = JHtml::Date($fileTime, $format);
		} else {
			$fileTime = '';
		}
		return $fileTime;
	}


	public static function getTitleFromFilenameWithExt (&$filename) {
		$folder_array		= explode('/', $filename);//Explode the filename (folder and file name)
		$count_array		= count($folder_array);//Count this array
		$last_array_value 	= $count_array - 1;//The last array value is (Count array - 1)

		return $folder_array[$last_array_value];
	}


	public static function getMimeType($extension, $params) {

		$regex_one		= '/({\s*)(.*?)(})/si';
		$regex_all		= '/{\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$params,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$returnMime = '';

		for($i = 0; $i < $count_matches; $i++) {

			$phocaDownload	= $matches[0][$i][0];
			preg_match($regex_one,$phocaDownload,$phocaDownloadParts);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocaDownloadParts[2], 2);
			foreach ($values_replace as $key2 => $values2) {
				$values = preg_replace($values2, '', $values);
			}

			// Return mime if extension call it
			if ($extension == $values[0]) {
				$returnMime = $values[1];
			}
		}

		if ($returnMime != '') {
			return $returnMime;
		} else {
			return "PhocaErrorNoMimeFound";
		}
	}

	public static function getMimeTypeString($params) {

		$regex_one		= '/({\s*)(.*?)(})/si';
		$regex_all		= '/{\s*.*?}/si';
		$matches 		= array();
		$count_matches	= preg_match_all($regex_all,$params,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		$extString 	= '';
		$mimeString	= '';

		for($i = 0; $i < $count_matches; $i++) {

			$phocaDownload	= $matches[0][$i][0];
			preg_match($regex_one,$phocaDownload,$phocaDownloadParts);
			$values_replace = array ("/^'/", "/'$/", "/^&#39;/", "/&#39;$/", "/<br \/>/");
			$values = explode("=", $phocaDownloadParts[2], 2);

			foreach ($values_replace as $key2 => $values2) {
				$values = preg_replace($values2, '', $values);
			}

			// Create strings
			$extString .= $values[0];
			$mimeString .= $values[1];

			$j = $i + 1;
			if ($j < $count_matches) {
				$extString .=',';
				$mimeString .=',';
			}
		}

		$string 		= array();
		$string['mime']	= $mimeString;
		$string['ext']	= $extString;

		return $string;
	}

	public static function getTitleFromFilenameWithoutExt (&$filename) {

		$folder_array		= explode('/', $filename);//Explode the filename (folder and file name)
		$count_array		= count($folder_array);//Count this array
		$last_array_value 	= $count_array - 1;//The last array value is (Count array - 1)

		$string = false;
		$string = preg_match( "/\./i", $folder_array[$last_array_value] );
		if ($string) {
			return PhocacartFile::removeExtension($folder_array[$last_array_value]);
		} else {
			return $folder_array[$last_array_value];
		}
	}

	public static function getFolderFromTheFile($filename) {

		$folder_array		= explode('/', $filename);
		$count_array		= count($folder_array);//Count this array
		$last_array_value 	= $count_array - 1;
		return str_replace($folder_array[$last_array_value], '', $filename);
	}

	public static function removeExtension($file_name) {
		return substr($file_name, 0, strrpos( $file_name, '.' ));
	}

	public static function getExtension( $file_name ) {
		return strtolower( substr( strrchr( $file_name, "." ), 1 ) );
	}

	public static function getFileOriginal($filename, $rel = 0, $manager) {
		$path	= PhocacartPath::getPath($manager);
		if ($rel == 1) {
			return str_replace('//', '/', $path['orig_rel_ds'] . $filename);
		} else {
			return JPath::clean($path['orig_abs_ds'] . $filename);
		}
	}

	public static function createDownloadFolder($folder) {
		$path = PhocacartPath::getPath('productfile');
		if (!JFolder::exists($path['orig_abs_ds'] . $folder )) {
			if (JFolder::create( $path['orig_abs_ds'] . $folder, 0755 )) {
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				JFile::write($path['orig_abs_ds'] . $folder.'/index.html', $data);
				return true;
			} else {
				return false;
			}
		}
		return true;
	}

	public static function existsFileOriginal($filename, $manager) {
		$fileOriginal = self::getFileOriginal($filename, 0, $manager);
		if (JFile::exists($fileOriginal)) {
			return true;
		} else {
			return false;
		}
	}

	public static function deleteDownloadFolders($folders, $manager = 'productfile') {

		if (!empty($folders)) {
			foreach($folders as $k => $v) {

				$path = PhocacartPath::getPath($manager);
				if(JFolder::exists($path['orig_abs_ds'] . $v)) {
					if(JFolder::delete($path['orig_abs_ds'] . $v)) {

						if ($manager == 'attributefile') {
							JFactory::getApplication()->enqueueMessage(JText::_('COM_PHOCACART_SUCCESS_ATTRIBUTE_OPTION_DOWNLOAD_FOLDER_DELETED'). ': ' . $v, 'success');
						} else {
							JFactory::getApplication()->enqueueMessage(JText::_('COM_PHOCACART_SUCCESS_PRODUCT_DOWNLOAD_FOLDER_DELETED'). ': ' . $v, 'success');
						}
					} else {
						if ($manager == 'attributefile') {
							JFactory::getApplication()->enqueueMessage(JText::_('COM_PHOCACART_ERROR_REMOVE_ATTRIBUTE_OPTION_DOWNLOAD_FOLDER'). ': ' . $v, 'error');
						} else {
							JFactory::getApplication()->enqueueMessage(JText::_('COM_PHOCACART_ERROR_REMOVE_PRODUCT_DOWNLOAD_FOLDER'). ': ' . $v, 'error');
						}
					}
				}
			}
		}


	}

}
?>
