<?php
namespace Phoca\PhocaCart\ContentType;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

final class ContentTypeHelper
{
    private static $cache = null;
    public static function getContentTypes(string $context, ?array $publishedFilter = null)
    {
        if (self::$cache === null) {
            /** @var DatabaseInterface $db */
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__phocacart_content_types')
                ->order('ordering, id');
            if ($publishedFilter !== null) {
                ArrayHelper::toInteger($publishedFilter);
                $query->whereIn('published', $publishedFilter);
            }

            $db->setQuery($query);

            self::$cache = $db->loadObjectList();
        }

        return array_filter(self::$cache, function($contentType) use ($context) {
            return $contentType->context === $context;
        });
    }
}
