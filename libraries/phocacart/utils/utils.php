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
		$folder = JPATH_ADMINISTRATOR .DS. 'components'.DS.$component;
		
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE .DS. 'components'.DS.$component;
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
				if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
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
	
	public static function getInfo() {
		return '<div style="text-align:right;color:#ccc;display:block">Powered by <a href="http://www.phoca.cz/phocacart">Phoca Cart</a></div>';		
	}
	
	public static function getToken($type = 'token') {
		
		$app		= JFactory::getApplication();
		$secret		= $app->getCfg('secret');
		$secretPartA= substr($secret, mt_rand(4,15), mt_rand(0,10));
		$secretPartB= substr($secret, mt_rand(4,15), mt_rand(0,10));

		$saltArray	= array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y');
		$randA		= mt_rand(0,9999);
		$randB		= mt_rand(0, $randA);
		$randC		= mt_rand(0, $randB);
		$randD		= mt_rand(0,24);
		
		
		$salt 		= md5('string '. $secretPartA . date('s'). $randA . str_replace($randC, $randD, date('r')). $secretPartB . 'end string');
		$salt 		= str_replace($saltArray[$randD], $saltArray[$randD], $salt);
		if ($type > 100) {
			$salt 	=  md5($salt);
		}
		$salt		= crypt($salt);
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
}
?>