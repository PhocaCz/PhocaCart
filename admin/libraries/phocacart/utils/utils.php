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
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
class PhocacartUtils
{
	public static function setVars( $task = '') {

		$a				= array();
		$app			= Factory::getApplication();
		$a['o'] 		= htmlspecialchars(strip_tags($app->input->get('option')));
		$a['c'] 		= str_replace('com_', '', $a['o']);
		$a['n'] 		= 'Phoca' . str_replace('com_phoca', '', $a['o']);
		$a['l'] 		= strtoupper($a['o']);
		$a['i']			= 'media/'.$a['o'].'/images/administrator/';
		$a['ja']		= 'media/'.$a['o'].'/js/administrator/';
		$a['jf']		= 'media/'.$a['o'].'/js/';
		$a['s']			= 'media/'.$a['o'].'/css/administrator/'.$a['c'].'.css';
		$a['css']		= 'media/'.$a['o'].'/css/';
		$a['bootstrap']	= 'media/'.$a['o'].'/bootstrap/';
		$a['task']		= $a['c'] . htmlspecialchars(strip_tags($task));
		$a['tasks'] 	= $a['task']. 's';

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

		if (Folder::exists($folder)) {
			$xmlFilesInDir = Folder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE . '/components'.'/'.$component;
			if (Folder::exists($folder)) {
				$xmlFilesInDir = Folder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		$xml_items = array();
		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				if ($data = Installer::parseXMLInstallFile($folder.'/'.$xmlfile)) {
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

		if (Factory::getConfig()->get('unicodeslugs') == 1) {
			$alias= OutputFilter::stringURLUnicodeSlug($alias);
		} else {
			$alias = OutputFilter::stringURLSafe($alias);
		}

		if (trim(str_replace('-', '', $alias)) == '') {
			$alias = Factory::getDate()->format("Y-m-d-H-i-s");
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
			return "\n\n" . 'Powered by Phoca Cart' . "\n" . 'https://www.phoca.cz/phocacart';
		} else {
			return '<div style="text-align:right;color:#ccc;display:block">Powered by <a href="https://www.phoca.cz/phocacart">Phoca Cart</a></div>';
		}
	}

	public static function getToken($type = 'token') {

		$app		= Factory::getApplication();
		$secret		= $app->get('secret');
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

	public static function convertEncoding($string) {

		$pC						= PhocacartUtils::getComponentParameters();
		$import_encoding_method	= $pC->get( 'import_encoding_method', '' );
		$import_encoding		= $pC->get( 'import_encoding', '' );
		$returnString		= '';

		if ($import_encoding != '') {

			if ($import_encoding_method == 1) { //'iconv'
				$returnString = iconv( $import_encoding, "UTF-8", $string );
			} else if ($import_encoding_method == 2) {//'mb_convert_encoding'
				$returnString = mb_convert_encoding($string, "UTF-8", $import_encoding);
			} else {
				$returnString = $string;
			}
		} else {
			$returnString = $string;
		}

		return self::removeUtf8Bom($returnString);
	}

	public static function removeUtf8Bom($text) {
		$bom = pack('H*','EFBBBF');
		$text = preg_replace("/^$bom/", '', $text);
		return $text;
	}

	public static function getIp() {


		$pC						= PhocacartUtils::getComponentParameters();
		$store_ip				= $pC->get( 'store_ip', 0 );

		if ($store_ip == 0) {
			return '';
		}

		$ip = false;
		if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR')) {
			$ip  = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip  = getenv('HTTP_X_FORWARDED_FOR');
		}
		if (!$ip) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	public static function getUserAgent() {

		$pC						= PhocacartUtils::getComponentParameters();
		$store_user_agent		= $pC->get( 'store_user_agent', 0 );

		if ($store_user_agent == 0) {
			return '';
		}

		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			return (string)$_SERVER['HTTP_USER_AGENT'];
		}

		return '';
	}


	public static function StripHiddenChars($str) {
		$chars = array("\r\n", "\n", "\r", "\t", "\0", "\x0B", "\xEF", "\xBB", "\xBF");

		$str = str_replace($chars," ",$str);

		return preg_replace('/\s+/',' ',$str);
	}


	public static function setOptionParameter($parameter, $value = '') {

		$component			= 'com_phocacart';
		$paramsC			= ComponentHelper::getParams($component) ;
		$paramsC->set($parameter, $value);
		$data['params'] 	= $paramsC->toArray();
		$table 				= Table::getInstance('extension');
		$idCom				= $table->find( array('element' => $component ));
		$table->load($idCom);

		if (!$table->bind($data)) {
			throw new Exception($table->getError());
			return false;
		}

		// pre-save checks
		if (!$table->check()) {
			throw new Exception($table->getError());
			return false;
		}

		// save the changes
		if (!$table->store()) {
			throw new Exception($table->getError());
			return false;
		}
		return true;
	}

	public static function setWizard($status) {
		self::setOptionParameter('enable_wizard', $status);
	}


	public static function doesExist($type) {


		switch ($type) {
			case 'category':
				$q = 'SELECT id FROM #__phocacart_categories ORDER BY id LIMIT 1';
			break;
			case 'tax':
				$q = 'SELECT id FROM #__phocacart_taxes ORDER BY id LIMIT 1';
			break;
			case 'product':
				$q = 'SELECT id FROM #__phocacart_products ORDER BY id LIMIT 1';
			break;
			case 'shipping':
				$q = 'SELECT id FROM #__phocacart_shipping_methods ORDER BY id LIMIT 1';
			break;
			case 'payment':
				$q = 'SELECT id FROM #__phocacart_payment_methods ORDER BY id LIMIT 1';
			break;
			case 'country':
				$q = 'SELECT id FROM #__phocacart_countries ORDER BY id LIMIT 1';
			break;
			case 'region':
				$q = 'SELECT id FROM #__phocacart_regions ORDER BY id LIMIT 1';
			break;
			case 'menu':
				$q = 'SELECT id FROM #__menu WHERE client_id = 0 AND link LIKE \'index.php?option=com_phocacart%\' ORDER BY id LIMIT 1';
			break;
			case 'module':
				$q = 'SELECT id FROM #__modules WHERE module LIKE \'mod_phocacart%\' ORDER BY id LIMIT 1';
			break;
			case 'option':
			default:
				$q = 'SELECT params FROM #__extensions WHERE type = \'component\' AND element = \'com_phocacart\' ORDER BY params LIMIT 1';
			break;
		}

		$db = Factory::getDBO();
		$db->setQuery($q);
		$item = $db->loadResult();

		if ($type == 'option') {
			$item = str_replace('{}', '', $item);
			if (isset($item) && $item != '') {
				return 1;
			}
		} else {
			if (isset($item) && (int)$item > 0) {
				return 1;
			}
		}

		return 0;

	}

	// $version - minimum version it must have
	public static function isJCompatible($version) {

		$currentVersion = new Version();
		if($currentVersion->isCompatible($version)) {
			return true;
		}
		return false;
	}
	public static function setConcatCharCount($count = 16384) {

		$db = Factory::getDBO();
		$db->setQuery("SET @@group_concat_max_len = ".(int)$count);
		$db->execute();
	}

	public static function issetMessage() {

		$app		= Factory::getApplication();
		$message 	= $app->getMessageQueue();

		if (!empty($message)) {
			return true;
		}
		return false;
	}

	public static function date($date, $format = '') {
		if ($format == '') {
			$format = Text::_('DATE_FORMAT_LC2');
		}
		/*$user	= PhocacartUser::getUser();
		$config = Factory::getConfig();
		$dateF 	= Factory::getDate($date, 'UTC');
		$dateF->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));
		$dateN	= $dateF->format('Y-m-d h:i:s', true, false);
		//$dateN = JHtml::date($v->date, JText::_('DATE_FORMAT_LC2'));*/
		$dateN = HTMLHelper::_('date', $date, $format);
		return $dateN;
	}

	public static function getComponentParameters() {

		$app 	= Factory::getApplication();
		$option	= $app->input->get('option');
		$client	= $app->isClient('administrator') ? 'A' : 'S';
		return PhocacartUtilsOptions::getOptions('PC', $client, $option);
	}

	public static function replaceCommaWithPoint($item) {

		$paramsC 			= PhocacartUtils::getComponentParameters();
		$comma_point		= $paramsC->get( 'comma_point', 0 );

		$item = PhocacartUtils::getDecimalFromString($item);
		if ($comma_point == 1) {
			return str_replace(',', '.', $item);
		} else {
			return $item;
		}
	}

	public static function getIntFromString($string) {

		if (empty($string)) {
			return 0;
		}
		$int	= '';//$int = 0
		$parts 	= explode(':', $string);
		if (isset($parts[0])) {
			$int = (int)$parts[0];
		}
		return $int;
	}

	public static function getNullFromEmpty($value = '') {
		if ($value  == '') {
			return 0;
		}
		return $value;
	}

	public static function getDateFromString($string) {


		if (empty($string)) {
			return '0000-00-00';
		}

		return $string;
	}

	public static function getDecimalFromString($string) {


		if (empty($string)) {
			return '0.0';
		}

		return $string;
	}

	public static function getStringFromItem($item) {


		if (empty($item)) {
			return '';
		}

		return $item;
	}

	public static function getDefaultTemplate() {

		$db = Factory::getDBO();
		$q = 'SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1;';
		$db->setQuery($q);
		$item = $db->loadResult();
		return $item;

	}

	public static function cleanExternalHtml($html) {
		$allowedTags = PhocacartUtilsSettings::getHTMLTagsExternalSource();
		$allowedTagsString = '<' . implode('><', $allowedTags). '>';

		$html = strip_tags($html, $allowedTagsString);

		return $html;

	}

	public static function curl_get_contents($url) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	public static function addSeparator($string, $separator = ' - ') {

		$o = '';
		if ($string != '') {
			$o = $separator . $string;
		}
		return $o;
	}

	public static function isView($viewToCheck = '') {

		if ($viewToCheck != '') {
			$app 		= Factory::getApplication();
			$view 		= $app->input->get('view', '');
			$option 	= $app->input->get('option', '');

			if ($option == 'com_phocacart' && $view == $viewToCheck) {
				return true;
			}
		}
		return false;
	}

	public static function isTypeView($viewToCheck = '') {

		if ($viewToCheck != '') {
			$app 		= Factory::getApplication();
			$view 		= $app->input->get('typeview', '');
			$option 	= $app->input->get('option', '');

			if ($option == 'com_phocacart' && $view == $viewToCheck) {
				return true;
			}
		}
		return false;
	}

	public static function isController($controllerToCheck = '') {

		if ($controllerToCheck != '') {
			$app 		= Factory::getApplication();
			//$task 		= $app->input->get('task','', 'raw');
			$controller = $app->input->get('controller', '');// Set in POS controllers
			$option 	= $app->input->get('option', '');

			//$taskA		= explode('.', $task);

			//if ($option == 'com_phocacart' && isset($taskA[0]) && $taskA[0] == $controllerToCheck) {
			if ($option == 'com_phocacart' && $controller == $controllerToCheck) {
				return true;
			}
		}
		return false;
	}


	public static function validateDate($date) {
		$format = 'Y-m-d H:i:s';
		$dateTime = DateTime::createFromFormat($format, $date);

		if ($dateTime instanceof DateTime && $dateTime->format('Y-m-d H:i:s') == $date) {
			return $dateTime->getTimestamp();
		}

		return false;
	}

	public static function convertWeightToKg($weight, $unit){

        if ($unit == 'kg') {
            return $weight;
        } else if ($unit == 'g') {
            return $weight/1000;
        } else if ($unit == 'lb') {
            return $weight*0.45359237;
        } else if ($unit == 'oz') {
            return $weight*0.0283495231;
        } else {
            return $weight;
        }

	}

	public static function convertWeightFromKg($weight, $unit){

        if ($unit == 'kg') {
            return $weight;
        } else if ($unit == 'g') {
            return $weight*1000;
        } else if ($unit == 'lb') {
            return $weight/0.45359237;
        } else if ($unit == 'oz') {
            return $weight/0.0283495231;
        } else {
            return $weight;
        }

	}

	public static function getNumberFromText($text) {

		return (int) filter_var($text, FILTER_SANITIZE_NUMBER_INT);
	}



}
?>
