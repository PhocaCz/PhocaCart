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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
/*
phocacart import('phocacart.file.file');
*/

class PhocacartDownload
{
	public static function getDownloadFiles($userId, $tokenDownload = '', $tokenOrder = '') {
		
		$db 	= JFactory::getDBO();
		$wheres		= array();
		
		if ((int)$userId < 1 && $tokenDownload != '' && $tokenOrder != '') {
			$wheres[]	= ' d.download_token = '.$db->quote($tokenDownload);
			$wheres[]	= ' o.order_token = '.$db->quote($tokenOrder);
			$leftJoin = '';
		} else {
			$wheres[]	= ' u.id = '.(int)$userId;
			$leftJoin = ' LEFT JOIN #__users AS u ON u.id = o.user_id';
		}
		$wheres[]	= ' d.published = 1';
		
		$query = ' SELECT d.*'
				.' FROM #__phocacart_order_downloads AS d'
				.' LEFT JOIN #__phocacart_orders AS o ON o.id = d.order_id'
				. $leftJoin
				.' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY d.date';
		$db->setQuery($query);
	
		$files = $db->loadObjectList();
		return $files;
	}
	
	public static function getDownloadFile($id) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT d.*, u.id as userid, o.order_token'
				.' FROM #__phocacart_order_downloads AS d'
				.' LEFT JOIN #__phocacart_orders AS o ON o.id = d.order_id'
				.' LEFT JOIN #__users AS u ON u.id = o.user_id'
				.' WHERE d.id = '.(int)$id .' AND d.published = 1'
				.' ORDER BY d.id'
				.' LIMIT 1';
		$db->setQuery($query);
		$file = $db->loadObject();
		return $file;
	}
	
	public static function validUntil($date, $days) {
		$db				= JFactory::getDBO();
		
		$nullDate 		= $db->getNullDate();
		$now			= JFactory::getDate();
		$config			= JFactory::getConfig();
		$orderDate 		= JFactory::getDate($date);
		$tz 			= new DateTimeZone($config->get('offset'));
		$orderDate->setTimezone($tz);
		
		$daysTime 		= $days * 24 * 60 * 60;
		$expireDate		= $orderDate->toUnix() + $daysTime;
		if ($days == '0') {
			// NO LIMIT
			return false;
		} else {
			return JHTML::date($expireDate, JText::_('DATE_FORMAT_LC2'));
		}
		
	
	}
	
	public static function isActive($date, $days) {
		
		$o				= '';
		$db				= JFactory::getDBO();
		
		$nullDate 		= $db->getNullDate();
		$now			= JFactory::getDate();
		$config			= JFactory::getConfig();
		$orderDate 		= JFactory::getDate($date);
		$tz 			= new DateTimeZone($config->get('offset'));
		$orderDate->setTimezone($tz);
		
		$daysTime 		= $days * 24 * 60 * 60;
		$expireDate		= $orderDate->toUnix() + $daysTime;
	
		
		if ( $now->toUnix() <= $expireDate ) {
			return true;
		} else {
			return false;
		}
		return false;
	}
	
	public static function download($id) {
	
		$file 	= self::getDownloadFile((int)$id);
		$user	= JFactory::getUser();
		$app	= JFactory::getApplication();
		
		
		$tokenDownload			= $app->input->post->get('d', '', 'string');
		$tokenOrder				= $app->input->post->get('o', '', 'string');
		
		$pC 					= PhocacartUtils::getComponentParameters();
		$download_days			= $pC->get( 'download_days', 0 );
		$download_count			= $pC->get( 'download_count', 0 );
		$download_guest_access	= $pC->get( 'download_guest_access', 0 );
		if ($download_guest_access == 0) {
			$token = '';
		}
		
		
		
		// CHECK USER AND TOKEN
		if ((int)$user->id < 1 && ($tokenDownload == '' || $tokenOrder == '')) {
			return false;
		}
		if (!isset($file->userid) && ($tokenDownload == '' || $tokenOrder == '')) {
			return false;
		}
		if ($user->id != $file->userid && ($tokenDownload == '' || $tokenOrder == '')) {
			return false;
		}
		
		if ((int)$user->id < 1 && ($tokenDownload == '' || $tokenOrder == '') && ($token != $file->download_token)) {
			return false;
		}

		
		
		// CHECK COUNT
		if($download_count > 0 && ((int)$download_count == (int)$file->download_hits || (int)$download_count < (int)$file->download_hits)) {
			return false;
		}

		// CHECK DAYS
		if((int)$download_days > 0 && !PhocacartDownload::isActive($file->date, $download_days)) {
			return false;
		}
		
		// Clears file status cache
		clearstatcache();
		$pathFile = PhocacartPath::getPath('productfile');
		
		$absOrRelFile = $pathFile['orig_abs_ds'] . $file->download_file;
		
		if (!JFile::exists($absOrRelFile)) {
			return false;
		}
		
		$fileWithoutPath	= basename($absOrRelFile);
		$fileSize 			= filesize($absOrRelFile);
		
		if (function_exists('finfo_open') && function_exists('finfo_open') && function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$f = finfo_file($finfo, $absOrRelFile);
			finfo_close($finfo);
			$mimeType = $f;
		} else if(function_exists('mime_content_type')) { // we have mime magic
			$mimeType = mime_content_type($absOrRelFile);
		} else {
			$mimeType = '';
		}

		// HIT Statistics
		self::hit($id);
		
		/*if ((int)$params->get('send_mail_download', 0) > 0) {
			PhocacartMail::sendMail((int)$params->get('send_mail_download', 0), $fileWithoutPath, 1);
		}*/
		
		
		if ($fileSize == 0 ) {
			die(JText::_('COM_PHOCACART_FILE_SIZE_EMPTY'));
			exit;
		}
		
		// Clean the output buffer
		ob_end_clean();
		
		// test for protocol and set the appropriate headers
		jimport( 'joomla.environment.uri' );
		$_tmp_uri 		= JURI::getInstance( JURI::current() );
		$_tmp_protocol 	= $_tmp_uri->getScheme();
		if ($_tmp_protocol == "https") {
			// SSL Support
			header('Cache-Control: private, max-age=0, must-revalidate, no-store');
		} else {
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Pragma: no-cache");
			header("Expires: 0");
		} /* end if protocol https */
		header("Content-Description: File Transfer");
		header("Expires: Sat, 30 Dec 1990 07:07:07 GMT");
		header("Accept-Ranges: bytes");

		
	
		
		// Modified by Rene
		// HTTP Range - see RFC2616 for more informations (http://www.ietf.org/rfc/rfc2616.txt)
		$httpRange   = 0;
		$newFileSize = $fileSize - 1;
		// Default values! Will be overridden if a valid range header field was detected!
		$resultLenght = (string)$fileSize;
		$resultRange  = "0-".$newFileSize;
		// We support requests for a single range only.
		// So we check if we have a range field. If yes ensure that it is a valid one.
		// If it is not valid we ignore it and sending the whole file.
		if(isset($_SERVER['HTTP_RANGE']) && preg_match('%^bytes=\d*\-\d*$%', $_SERVER['HTTP_RANGE'])) {
			// Let's take the right side
			list($a, $httpRange) = explode('=', $_SERVER['HTTP_RANGE']);
			// and get the two values (as strings!)
			$httpRange = explode('-', $httpRange);
			// Check if we have values! If not we have nothing to do!
			if(!empty($httpRange[0]) || !empty($httpRange[1])) {
				// We need the new content length ...
				$resultLenght	= $fileSize - $httpRange[0] - $httpRange[1];
				// ... and we can add the 206 Status.
				header("HTTP/1.1 206 Partial Content");
				// Now we need the content-range, so we have to build it depending on the given range!
				// ex.: -500 -> the last 500 bytes
				if(empty($httpRange[0]))
					$resultRange = $resultLenght.'-'.$newFileSize;
				// ex.: 500- -> from 500 bytes to filesize
				elseif(empty($httpRange[1]))
					$resultRange = $httpRange[0].'-'.$newFileSize;
				// ex.: 500-1000 -> from 500 to 1000 bytes
				else
					$resultRange = $httpRange[0] . '-' . $httpRange[1];
				//header("Content-Range: bytes ".$httpRange . $newFileSize .'/'. $fileSize);
			} 
		}
		header("Content-Length: ". $resultLenght);
		header("Content-Range: bytes " . $resultRange . '/' . $fileSize);
		header("Content-Type: " . (string)$mimeType);
		header('Content-Disposition: attachment; filename="'.$fileWithoutPath.'"');
		header("Content-Transfer-Encoding: binary\n");
		
		//@readfile($absOrRelFile);
		
		// Try to deliver in chunks
		@set_time_limit(0);
		$fp = @fopen($absOrRelFile, 'rb');
		if ($fp !== false) {
			while (!feof($fp)) {
				echo fread($fp, 8192);
			}
			fclose($fp);
		} else {
			@readfile($absOrRelFile);
		}
		flush();
		exit;
		
	}
	
	protected static function hit($id) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->update('#__phocacart_order_downloads')
			->set($db->quoteName('download_hits') . ' = (' . $db->quoteName('download_hits') . ' + 1)')
			->where('id = ' . $db->quote((int)$id));
		$db->setQuery($query);
		
		$db->execute();
		return true;
	}
	
	public static function setStatusByOrder($orderId, $status) {
		
		$db 	= JFactory::getDBO();
		$query = ' UPDATE #__phocacart_order_downloads'
				.' SET published = '.(int)$status
				.' WHERE order_id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();
		return true;
	}
	
	
	public static function downloadContent($content, $prefix = '', $suffix = '') {
	
		$pC 				= PhocacartUtils::getComponentParameters();
		$import_export_type	= $pC->get( 'import_export_type', 0 );
		
		if ($import_export_type == 0) {
			$mimeType	= 'text/csv';
			$name		= "phocacartproductexport.csv";
		} else {
			$mimeType	= 'application/xml';
			$name		= "phocacartproductexport.xml";
		}
		
		$content = $prefix . $content . $suffix;

		// Clean the output buffer
		ob_end_clean();
		
		// test for protocol and set the appropriate headers
		jimport( 'joomla.environment.uri' );
		$_tmp_uri 		= JURI::getInstance( JURI::current() );
		$_tmp_protocol 	= $_tmp_uri->getScheme();
		if ($_tmp_protocol == "https") {
			// SSL Support
			header('Cache-Control: private, max-age=0, must-revalidate, no-store');
		} else {
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Pragma: no-cache");
			header("Expires: 0");
		} /* end if protocol https */
		header("Content-Description: File Transfer");
		header("Expires: Sat, 30 Dec 1990 07:07:07 GMT");
		header("Accept-Ranges: bytes");
		
		header('Content-Encoding: UTF-8');
		header("Content-Type: " . (string)$mimeType.'; charset=UTF-8');
		header('Content-Disposition: attachment; filename="'.($name).'"');
		header("Content-Transfer-Encoding: binary\n");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $content;
		flush();
		return true;
		//exit;
	}
	
	public static function getDownloadFilePublic($id) {
		
		$db 	= JFactory::getDBO();
		$query = ' SELECT a.public_download_file'
				.' FROM #__phocacart_products AS a'
				.' WHERE a.id = '.(int)$id
				.' ORDER BY a.id'
				.' LIMIT 1';
		$db->setQuery($query);
		$file = $db->loadObject();
		return $file;
	}
	
	public static function downloadPublic($id) {
	
		$file 	= self::getDownloadFilePublic((int)$id);
		$app	= JFactory::getApplication();
		

		
		// Clears file status cache
		clearstatcache();
		$pathFile = PhocacartPath::getPath('publicfile');
		
		
		$absOrRelFile = $pathFile['orig_abs_ds'] . $file->public_download_file;
		
		if (!JFile::exists($absOrRelFile)) {
			return false;
		}
		
		$fileWithoutPath	= basename($absOrRelFile);
		$fileSize 			= filesize($absOrRelFile);
		
		if (function_exists('finfo_open') && function_exists('finfo_open') && function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$f = finfo_file($finfo, $absOrRelFile);
			finfo_close($finfo);
			$mimeType = $f;
		} else if(function_exists('mime_content_type')) { // we have mime magic
			$mimeType = mime_content_type($absOrRelFile);
		} else {
			$mimeType = '';
		}
		
		
		if ($fileSize == 0 ) {
			die(JText::_('COM_PHOCACART_FILE_SIZE_EMPTY'));
			exit;
		}
		
		// Clean the output buffer
		ob_end_clean();
		
		// test for protocol and set the appropriate headers
		jimport( 'joomla.environment.uri' );
		$_tmp_uri 		= JURI::getInstance( JURI::current() );
		$_tmp_protocol 	= $_tmp_uri->getScheme();
		if ($_tmp_protocol == "https") {
			// SSL Support
			header('Cache-Control: private, max-age=0, must-revalidate, no-store');
		} else {
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Pragma: no-cache");
			header("Expires: 0");
		} /* end if protocol https */
		header("Content-Description: File Transfer");
		header("Expires: Sat, 30 Dec 1990 07:07:07 GMT");
		header("Accept-Ranges: bytes");

		// Modified by Rene
		// HTTP Range - see RFC2616 for more informations (http://www.ietf.org/rfc/rfc2616.txt)
		$httpRange   = 0;
		$newFileSize = $fileSize - 1;
		// Default values! Will be overridden if a valid range header field was detected!
		$resultLenght = (string)$fileSize;
		$resultRange  = "0-".$newFileSize;
		// We support requests for a single range only.
		// So we check if we have a range field. If yes ensure that it is a valid one.
		// If it is not valid we ignore it and sending the whole file.
		if(isset($_SERVER['HTTP_RANGE']) && preg_match('%^bytes=\d*\-\d*$%', $_SERVER['HTTP_RANGE'])) {
			// Let's take the right side
			list($a, $httpRange) = explode('=', $_SERVER['HTTP_RANGE']);
			// and get the two values (as strings!)
			$httpRange = explode('-', $httpRange);
			// Check if we have values! If not we have nothing to do!
			if(!empty($httpRange[0]) || !empty($httpRange[1])) {
				// We need the new content length ...
				$resultLenght	= $fileSize - $httpRange[0] - $httpRange[1];
				// ... and we can add the 206 Status.
				header("HTTP/1.1 206 Partial Content");
				// Now we need the content-range, so we have to build it depending on the given range!
				// ex.: -500 -> the last 500 bytes
				if(empty($httpRange[0]))
					$resultRange = $resultLenght.'-'.$newFileSize;
				// ex.: 500- -> from 500 bytes to filesize
				elseif(empty($httpRange[1]))
					$resultRange = $httpRange[0].'-'.$newFileSize;
				// ex.: 500-1000 -> from 500 to 1000 bytes
				else
					$resultRange = $httpRange[0] . '-' . $httpRange[1];
				//header("Content-Range: bytes ".$httpRange . $newFileSize .'/'. $fileSize);
			} 
		}
		header("Content-Length: ". $resultLenght);
		header("Content-Range: bytes " . $resultRange . '/' . $fileSize);
		header("Content-Type: " . (string)$mimeType);
		header('Content-Disposition: attachment; filename="'.$fileWithoutPath.'"');
		header("Content-Transfer-Encoding: binary\n");
		
		//@readfile($absOrRelFile);
		
		// Try to deliver in chunks
		@set_time_limit(0);
		$fp = @fopen($absOrRelFile, 'rb');
		if ($fp !== false) {
			while (!feof($fp)) {
				echo fread($fp, 8192);
			}
			fclose($fp);
		} else {
			@readfile($absOrRelFile);
		}
		flush();
		exit;	
	}
	
	
}