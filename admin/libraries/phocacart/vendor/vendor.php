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

    private static $cache = null;

    /**
     * Loads internal vendors cache
     *
     * @return void
     *
     * @since 5.0.0
     */
    private static function loadCache(): void
    {
        if (self::$cache === null) {
            $db = Factory::getDBO();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__phocacart_vendors');
            $db->setQuery($query);
            self::$cache = $db->loadObjectList('id');
        }
    }

    /**
     * Checks if provided vendor is valid POS vendor
     *
     * @param $user
     * @return bool
     *
     * @since 5.0.0
     */
    public static function isVendor(&$user): bool {
        if (!empty($user) && isset($user->id) && (int)$user->id > 0) {
            self::loadCache();
            $userId = (int)$user->id;
            $vendors = array_filter(self::$cache, function($vendor) use ($userId) {
                return !!$vendor->published
                    && $vendor->type === self::POS_VENDOR
                    && $vendor->user_id === $userId;
            });

            if ($vendors) {
                $vendorO = array_shift($vendors);
                if ($vendorO->image) {
                    $user->image = $vendorO->image;// Add image info to vendor object
                }

                return true;// current user is vendor
            }
		}

		return false;
	}

    /**
     * Returns vendor by ID
     *
     * @param int $vendorId
     * @return object|mixed|null
     *
     * @since 5.0.0
     */
    public static function getVendor(int $vendorId): ?object {
        self::loadCache();
        if (isset(self::$cache[$vendorId])) {
            return self::$cache[$vendorId];
        }

        return null;
    }

}
