<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocaCartUtils
{
	public static function setVars( $task = '') {
	
		$a			= array();
		$app		= JFactory::getApplication();
		$a['o'] 	= htmlspecialchars(strip_tags($app->input->get('option')));
		$a['c'] 	= str_replace('com_', '', $a['o']);
		$a['n'] 	= 'Phoca' . ucfirst(str_replace('com_phoca', '', $a['o']));
		$a['l'] 	= strtoupper($a['o']);
		$a['i']		= 'media/'.$a['o'].'/images/administrator/';
		$a['ja']	= 'media/'.$a['o'].'/js/administrator/';
		$a['jf']	= 'media/'.$a['o'].'/js/';
		$a['s']		= 'media/'.$a['o'].'/css/administrator/'.$a['c'].'.css';
		$a['css']	= 'media/'.$a['o'].'/css/';
		$a['task']	= $a['c'] . htmlspecialchars(strip_tags($task));
		$a['tasks'] = $a['task']. 's';
		
		switch ($task) {
		 case 'tax':
		 case 'stockstatus':
		 case 'status':
			$a['tasks'] = $a['task']. 'es';
		 break;
		 case 'category':
		 case 'currency':
		 case 'country':
			$tStr = substr($a['task'],0,-1);
			$a['tasks'] = $tStr. 'ies';
		 break;
		}

		return $a;
	}
	
	public static function getPhocaVersion($component = 'com_phocacart') {
		$component = 'com_phocacart';
		$folder = JPATH_ADMINISTRATOR .'/components'.'/'.$component;
		
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE . '/components'.'/'.$component;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		$xml_items = '';
		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($folder.'/'.$xmlfile)) {
					foreach($data as $key => $value) {
						$xml_items[$key] = $value;
					}
				}
			}
		}
		
		if (isset($xml_items['version']) && $xml_items['version'] != '' ) {
			return $xml_items['version'];
		} else {
			return '';
		}
	}
	
	public static function getAliasName($alias) {	
		$alias = JApplication::stringURLSafe($alias);
		if (trim(str_replace('-', '', $alias)) == '') {
			$alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}
		return $alias;
	}
	
	public static function setMessage($new = '', $current = '') {
		
		$message = $current;
		if($new != '') {
			if ($current != '') {
				$message .= '<br />';
			}
			$message .= $new;
		}
		return $message;
	}
	
	public static function getInfo($mode = 1) {
		
		if ($mode == 0) {
			return "\n\n" . 'Powered by Phoca Cart' . "\n" . 'http://www.phoca.cz/phocacart';
		} else {
			return '<div style="text-align:right;color:#ccc;display:block">Powered by <a href="http://www.phoca.cz/phocacart">Phoca Cart</a></div>';
		}
	}
	
	public static function getToken($type = 'token') {
		
		$app		= JFactory::getApplication();
		$secret		= $app->getCfg('secret');
		$secretPartA= substr($secret, mt_rand(4,15), mt_rand(0,10));
		$secretPartB= substr($secret, mt_rand(4,15), mt_rand(0,10));

		$saltArray	= array('a', '0', 'c', '1', 'e', '2', 'h', '3', 'i', '4', 'k', '5', 'm', '6', 'o', '7', 'q', '8', 'r', '0', 'u', '1', 'w', '2', 'y');
		$randA		= mt_rand(0,9999);
		$randB		= mt_rand(0, $randA);
		$randC		= mt_rand(0, $randB);
		$randD		= mt_rand(0,24);
		$randD2		= mt_rand(0,24);
		
		
		$salt 		= md5('string '. $secretPartA . date('s'). $randA . str_replace($randC, $randD, date('r')). $secretPartB . 'end string');
		$salt 		= str_replace($saltArray[$randD], $saltArray[$randD2], $salt);
		if ($type > 100) {
			$salt 	=  md5($salt);
		}
		
	 
		// use password_hash since php 5.5.0
		$salt		= crypt($salt, $salt);
		$rT			= $randC + $randA;
		if ($rT < 1) {$rT = 1;}
		$time		= (int)time() * $randB / $rT;
		$token = hash('sha256', $salt . $time . time());
		
		if ($type == 'folder') {
			return substr($token, $randD, 16);
		} else {
			return $token;
		}
	}
	
	public static function isURLAddress($url) {
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}
	
	public static function round($value, $precision = 2) {
		
		return round($value, $precision);
	}
	
	public static function getRandomString($length = '') {		
		$code = md5(uniqid(rand(), true));
		if ($length != '' && (int)$length > 0) {
			$length = $length - 1;
			return chr(rand(97,122)) . substr($code, 0, $length);
		} else {
			return chr(rand(97,122)) . $code;
		}
	}
	
	public static function wordDelete($string,$length,$end) {
		if (strlen($string) < $length || strlen($string) == $length) {
			return $string;
		} else {
			return substr($string, 0, $length) . $end;
		}
	}
	
	public static function wordDeleteWhole($string,$length,$end = '...') {
		if (strlen($string) < $length || strlen($string) == $length) {
			return $string;
		} else {
			preg_match('/(.{' . $length . '}.*?)\b/', $string, $matches);
			return rtrim($matches[1]) . $end;
		}
	}

	
	public static function strTrimAll($input) {
		$output	= '';
	    $input	= trim($input);
	    for($i=0;$i<strlen($input);$i++) {
	        if(substr($input, $i, 1) != " ") {
	            $output .= trim(substr($input, $i, 1));
	        } else {
	            $output .= " ";
	        }
	    }
	    return $output;
	}
}
?>