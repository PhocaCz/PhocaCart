<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die;

class PhocacartCaptcha
{
	public static function enableCaptchaCheckout() {
		
		$document					= JFactory::getDocument();
		$pC 						= JComponentHelper::getParams('com_phocacart') ;
		$enable_captcha_checkout	= $pC->get( 'enable_captcha_checkout', 0 );
		
		//$user						= JFactory::getUser();
		$guest						= PhocacartUserGuestuser::getGuestUser();
		
		if ($enable_captcha_checkout == 1) {
			return true;// All
		} else if ($enable_captcha_checkout == 2 && $guest) {
			return true;// Guest user
		} else if ($enable_captcha_checkout == 2 && !$guest) {
			return false;// Guest user but in checkout there is standard user
		} else {
			return false;// Disabled
		}
		return false;
	}
}
?>
