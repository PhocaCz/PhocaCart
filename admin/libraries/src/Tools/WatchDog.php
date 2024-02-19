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

namespace Phoca\PhocaCart\Tools;

defined('_JEXEC') or die();

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Phoca\PhocaCart\Constants\WishListType;
use Phoca\PhocaCart\Exception\WatchDogException;

class WatchDog
{
    public static function has(int $productId): bool
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $user = Factory::getApplication()->getIdentity();

        if ($user->guest) {
            return false;
        }

        $query = $db->getQuery(true)
            ->select('product_id')
            ->from('#__phocacart_wishlists')
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('product_id') . ' = :productId')
            ->where($db->quoteName('type') . ' = ' . WishListType::WatchDog)
            ->bind(':userId', $user->id, ParameterType::INTEGER)
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);

        return !!$db->loadResult();
    }

    public static function count(): int
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $user = Factory::getApplication()->getIdentity();

        if ($user->guest) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(product_id)')
            ->from('#__phocacart_wishlists')
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('type') . ' = ' . WishListType::WatchDog)
            ->bind(':userId', $user->id, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    public static function set(int $productId, ?int $categoryId = null): void
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $user = Factory::getApplication()->getIdentity();
        $lang = Factory::getApplication()->getLanguage()->getTag();
        if ($user->guest) {
            throw new WatchDogException(Text::_('COM_PHOCACART_ERROR_WATCHDOG_LOGIN'));
        }

        $wishlist = (object)[
            'product_id' => $productId,
            'category_id' => $categoryId,
            'user_id' => $user->id,
            'language' => $lang,
            'type' => WishListType::WatchDog,
            'date' => (new Date())->toSql(),
        ];

        $db->insertObject('#__phocacart_wishlists', $wishlist);
    }
}
