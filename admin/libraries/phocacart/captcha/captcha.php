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
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Captcha\CaptchaRegistry;
use Joomla\CMS\Language\Text;

class PhocacartCaptcha
{
	/**
	 * Get the configured captcha type.
	 *
	 * @return int  1 = Google reCAPTCHA, 2 = Joomla POW Captcha
	 */
	public static function getCaptchaType(): int {
		$pC = PhocacartUtils::getComponentParameters();
		return (int)$pC->get('captcha_type', 1);
	}

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

	/**
	 * Render captcha HTML based on configured provider.
	 *
	 * @param  string  $name   The field name for the captcha input.
	 * @param  string  $id     The field id for the captcha input.
	 *
	 * @return string  HTML output of the captcha widget.
	 */
	public static function render(string $name = 'captcha', string $id = 'captcha'): string {

		$captchaType = self::getCaptchaType();

		if ($captchaType === 2) {
			// Joomla POW Captcha
			try {
				$registry = Factory::getContainer()->get(CaptchaRegistry::class);
				$registry->initRegistry();

				if ($registry->has('powcaptcha')) {
					$captcha = Captcha::getInstance('powcaptcha');
					return $captcha->display($name, $id);
				}
			} catch (\Exception $e) {
				// Plugin not available
			}
			return '<div class="alert alert-warning">' . Text::_('COM_PHOCACART_CAPTCHA_POW_PLUGIN_NOT_ENABLED') . '</div>';
		}

		// Default: Google reCAPTCHA
		return PhocacartCaptchaRecaptcha::render();
	}

	/**
	 * Validate captcha response based on configured provider.
	 *
	 * @return bool  True if the captcha response is valid.
	 */
	public static function isValid(): bool {

		$captchaType = self::getCaptchaType();

		if ($captchaType === 2) {
			// Joomla POW Captcha
			try {
				$app = Factory::getApplication();
				$response = $app->getInput()->post->get('captcha', '', 'raw');

				$registry = Factory::getContainer()->get(CaptchaRegistry::class);
				$registry->initRegistry();

				if ($registry->has('powcaptcha')) {
					$captcha = Captcha::getInstance('powcaptcha');
					return $captcha->checkAnswer($response);
				}
			} catch (\Exception $e) {
				return false;
			}
			return false;
		}

		// Default: Google reCAPTCHA
		return (bool)PhocacartCaptchaRecaptcha::isValid();
	}
}
?>
