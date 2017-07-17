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
defined('JPATH_BASE') or die;

class PhocacartCaptchaRecaptcha
{
	public static function render() {
		
		$document	= JFactory::getDocument();
		$pC 		= PhocacartUtils::getComponentParameters();
		$siteKey	= strip_tags(trim($pC->get( 'recaptcha_sitekey', '' )));
		
		$document->addScript('https://www.google.com/recaptcha/api.js');
		return '<div class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>';
	}
	public static function isValid() {
		
		$app 		= JFactory::getApplication();
		$pC 		= PhocacartUtils::getComponentParameters();
		$secretKey	= strip_tags(trim($pC->get( 'recaptcha_privatekey', '' )));
		//$response 	= $app->input->post->get('g-recaptcha-response', '', 'string');
		//$response	= $ POST['g-recaptcha-response'];
		$response 	= $app->input->post->get('g-recaptcha-response', '', 'string');
		$remoteIp	= $_SERVER['REMOTE_ADDR'];
		
		try {

			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$data = ['secret'   => $secretKey,
					 'response' => $response,
					 'remoteip' => $remoteIp];

			$options = [
				'http' => [
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data) 
				]
			];

			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			
			//$resultString = print r($result, true);
			//PhocacartLog::add(1, 'Ask a Question - Captcha Result', 0, $resultString);
			return json_decode($result)->success;
		}
		catch (Exception $e) {
			return null;
		}
	}
}
?>
