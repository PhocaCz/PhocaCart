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

class PhocacartSecurity
{
	public function __construct() {}
	
	
	public static function checkIpAddress($ipAddr, $ipBan) {
		
		$spam = false;
		$ip_ban			= trim( $ipBan );
		$ip_ban_array	= explode( ',', $ip_ban );
		if (is_array($ip_ban_array)) {
			foreach ($ip_ban_array as $valueIp) {
				if ($valueIp != '' && strstr($ipAddr, trim($valueIp)) && strpos($ipAddr, trim($valueIp))==0) {
					$spam = true;
					break;
				}
			}
		}
		
		return $spam;
	} 
	
	public static function setHiddenFieldPos($name, $email, $phone, $message) {
		$form = array();
	
		if ((int)$name > 0) {
			$form[] = 1;
		}
		if ((int)$email > 0) {
			$form[] = 2;
		}
		if ((int)$phone > 0) {
			$form[] = 3;
		}
		if ((int)$message > 0) {
			$form[] = 4;
		}
		$value = mt_rand(0,count($form) - 1);
		
		return $form[$value];
	}
	
}