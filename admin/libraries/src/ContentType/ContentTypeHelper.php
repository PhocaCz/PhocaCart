<?php
namespace Phoca\PhocaCart\ContentType;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

final class ContentTypeHelper
{
    public const Category = 'category';
    public const ProductRelated = 'product_related';
    public const Attribute = 'attribute';

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

            $db->setQuery($query);

            $items = $db->loadObjectList('id');
            array_walk($items, function($item) {
               $params = new Registry($item->params);
               $params = $params->toArray()[$item->context] ?? [];
               $item->params = new Registry($params);
            });
            self::$cache = $items;
        }

        if ($publishedFilter !== null) {
            ArrayHelper::toInteger($publishedFilter);
        }

        return array_filter(self::$cache, function($contentType) use ($context, $publishedFilter) {
            if ($publishedFilter !== null) {
                if (!in_array($contentType->published, $publishedFilter))
                    return false;
            }

            return $contentType->context === $context;
        });
    }

    public static function getContentType(string $context, int $id): ?object
    {
        $contentTypes = self::getContentTypes($context);
        return $contentTypes[$id] ?? null;
    }

    public static function getContentTypeParams(string $context, int $id): Registry
    {
        $contentType = self::getContentType($context, $id);
        return $contentType->params ?? new Registry();
    }

}
