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
use Joomla\CMS\Factory;

class PhocacartCaptchaRecaptcha
{
	public static function render() {

		$document	= Factory::getDocument();
		$pC 		= PhocacartUtils::getComponentParameters();
		$siteKey	= strip_tags(trim($pC->get( 'recaptcha_sitekey', '' )));
		$lang	    = strip_tags(trim($pC->get( 'recaptcha_lang', '' )));

		if ($lang != '') {
		    $lang = '?hl='.$lang;
        }

		$document->addScript('https://www.google.com/recaptcha/api.js'.$lang);
		return '<div class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>';
	}
	public static function isValid() {

		$app 		= Factory::getApplication();
		$pC 		= PhocacartUtils::getComponentParameters();
		$secretKey	= strip_tags(trim($pC->get( 'recaptcha_privatekey', '' )));
		//$response 	= $app->input->post->get('g-recaptcha-response', '', 'string');
		//$response	= $ POST['g-recaptcha-response'];
		$response 	= $app->input->post->get('g-recaptcha-response', '', 'string');
		$remoteIp	= $_SERVER['REMOTE_ADDR'];
		$urlVerify	= 'https://www.google.com/recaptcha/api/siteverify';

		$recaptchaMethod = $pC->get( 'recaptcha_request_method', 2 );//1 file_get_contents, 2 curl

		try {


			if ($recaptchaMethod == 1) {
				// FILE GET CONTENTS
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
				$result = file_get_contents($urlVerify, false, $context);
			} else {
			// CURL
				$ch = curl_init();

				curl_setopt_array($ch, [
					CURLOPT_URL => $urlVerify,
					CURLOPT_POST => true,
				//	CURLOPT_SSL_VERIFYPEER => false,
				//	CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_POSTFIELDS => [
						'secret' => $secretKey,
						'response' => $response,
						'remoteip' => $remoteIp],
					CURLOPT_RETURNTRANSFER => true
				]);

				$result = curl_exec($ch);
				curl_close($ch);
			}


			//$resultString = print r($result, true);
			//PhocacartLog::add(3, 'Ask a Question - Captcha Result', 0, $resultString);
			if (!$result) {
				return false;
			}
			return json_decode($result)->success;

		}
		catch (Exception $e) {
			return null;
		}
	}
}
