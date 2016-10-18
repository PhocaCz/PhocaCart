<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die;

class PhocaCartRecaptcha
{
	public static function render() {
		
		$document	= JFactory::getDocument();
		$pC 		= JComponentHelper::getParams('com_phocacart') ;
		$siteKey	= strip_tags(trim($pC->get( 'recaptcha_sitekey', '' )));
		
		$document->addScript('https://www.google.com/recaptcha/api.js');
		return '<div class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>';
	}
	public static function isValid() {
		
		$app 		= JFactory::getApplication();
		$pC 		= JComponentHelper::getParams('com_phocacart') ;
		$secretKey	= strip_tags(trim($pC->get( 'recaptcha_privatekey', '' )));
		//$response 	= $app->input->post->get('g-recaptcha-response', '', 'string');
		//$response	= $_POST['g-recaptcha-response'];
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
			
			//$resultString = print_r($result, true);
			//PhocaCartLog::add(1, 'Ask a Question - Captcha Result', 0, $resultString);
			return json_decode($result)->success;
		}
		catch (Exception $e) {
			return null;
		}
	}
}
?>
