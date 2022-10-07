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

class PhocacartCaptcha
{
	public static function enableCaptchaCheckout() {
		
		$document					= Factory::getDocument();
		$pC 						= PhocacartUtils::getComponentParameters();
		$enable_captcha_checkout	= $pC->get( 'enable_captcha_checkout', 0 );
		
		
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
