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
use Joomla\CMS\Uri\Uri;
class PhocacartLog
{
	/* type - type : e.g warning, error, etc.
	 * 1 ... all
	 * 2 ... error
	 * 3 ... warnings
	 * 4 ... notices
	 * typeid - for example order id, category id, product id
	 *
	 * Example:
	 * PhocacartLog::add(1, 'Message', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
	 */

	public static function add( $type = 0, $title = '', $typeid = 0, $description = '') {

		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$enable_logging		= $paramsC->get( 'enable_logging', 0 );// 0 disable, 1 all, 2 error only

		if ($enable_logging == 0) {
			return false;
		}

		if ($enable_logging == 2 && $type != 2) {
			return false;
		}

		// e.g. paypal can send Latin-1 (ISO-8859-1) - prevent from database error: Incorrect string value "\xE4...\x0A…"
		$description = self::toUtf8($description);

		// Additional User information
		$descSuffix     = '';
		if ($app->isClient('site')){
			$user 		    = PhocacartUser::getUser();
			$guest		    = PhocacartUserGuestuser::getGuestUser();

			if ($guest){
				$descSuffix .= ', User: Guest Checkout';
			} else if ($user->username != ''){
				$descSuffix .= ', User: '.$user->username;
			} else {
				$descSuffix .= ', User: Anonymous'; // Not logged in user, no guest checkout started
			}
		}
		$description .= $descSuffix;

		if ((int)$type > 0 && $title != '' ) {
			$uri			= Uri::getInstance();
			$user			= PhocacartUser::getUser();
			$db				= Factory::getDBO();
			$ip 			= $_SERVER["REMOTE_ADDR"];
			$incoming_page	= htmlspecialchars($uri->toString());
			$incoming_page  = substr($incoming_page, 0, 2048);

			$userid			= 0;
			if (isset($user->id) && (int)$user->id > 0) {
				$userid = $user->id;
			}

			// Ordering
			$ordering = 0;
			$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_logs');
			$max = $db->loadResult();
			$ordering = $max+1;



			$query = ' INSERT INTO #__phocacart_logs ('
			.$db->quoteName('user_id').', '
			.$db->quoteName('type_id').', '
			.$db->quoteName('type').', '
			.$db->quoteName('title').', '
			.$db->quoteName('ip').', '
			.$db->quoteName('incoming_page').', '
			.$db->quoteName('description').', '
			.$db->quoteName('published').', '
			.$db->quoteName('ordering').', '
			.$db->quoteName('date').' )'
			. ' VALUES ('
			.$db->quote((int)$userid).', '
			.$db->quote((int)$typeid).', '
			.$db->quote((int)$type).', '
			.$db->quote($title).', '
			.$db->quote($ip).', '
			.$db->quote($incoming_page).', '
			.$db->quote($description).', '
			.$db->quote('1').', '
			.$db->quote((int)$ordering).', '
			.$db->quote(gmdate('Y-m-d H:i:s')).' )';

			$db->setQuery($query);
			$db->execute();

			return true;
		}
		return false;

	}

	public static function toUtf8($string) {
		if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
			if (!mb_detect_encoding($string, 'UTF-8', true)) {
				return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
			}
			return $string; // already UTF-8
		} elseif (function_exists('iconv')) {
			// Fallback: assume input is Latin-1 if not UTF-8
			if (!preg_match('//u', $string)) { // valid UTF-8 check
				return iconv('ISO-8859-1', 'UTF-8//IGNORE', $string);
			}
			return $string;
		}

		return $string;
	}
}
?>
