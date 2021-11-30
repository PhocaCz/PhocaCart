<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;

class JFormFieldPhocacaptcha extends FormField
{
	protected $type 		= 'phocacaptcha';

	protected function getInput() {

		$document	= Factory::getDocument();
		$session 	= Factory::getSession();
		$params     = PhocacartUtils::getComponentParameters();
		$string 	= bin2hex(openssl_random_pseudo_bytes(10));
		$namespace	= 'pc'.$params->get('session_suffix', $string);
		$captchaCnt = $session->get('captcha_cnt',  0, $namespace) + 1;

		// Possible extension of different captcha
		$id = $session->get('captcha_id', '', $namespace);

		switch ($id){
			default:
			case 1:
				$retval = PhocacartCaptchaRecaptcha::render();
				//$session->set('captcha_cnt', $captchaCnt, $namespace);
			break;
		}

		return $retval;
	}


}
?>
