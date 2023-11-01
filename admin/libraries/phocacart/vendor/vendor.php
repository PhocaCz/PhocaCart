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
use Joomla\CMS\Factory;

class PhocacartVendor
{
    public const POS_VENDOR = 0;
    public const PRODUCT_VENDOR = 1;

    /**
     * Checks if provided vendor is valid POS vendor
     *
     * @param $vendor
     * @return bool
     */
    public static function isVendor(&$vendor) {
		if (!empty($vendor) && isset($vendor->id) && (int)$vendor->id > 0) {

			$db 	= Factory::getDBO();
			$query = ' SELECT a.id, a.title, a.image FROM #__phocacart_vendors AS a'
					.' WHERE a.user_id = '.(int)$vendor->id
					.' AND a.published = 1'
                    .' AND a.type = ' . self::POS_VENDOR
					.' ORDER BY a.ordering';
			$db->setQuery($query);
			$vendorO = $db->loadObject();

			if (isset($vendorO->id) && (int)$vendorO->id > 0) {
				if (isset($vendorO->image) && $vendorO->image != '') {
					$vendor->image = $vendorO->image;// Add image info to vendor object
				}
				return true;// current user is vendor
			}

			return false;
		}

		return false;
	}
}
