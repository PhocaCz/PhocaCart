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

namespace Phoca\PhocaCart\Schemaorg;

defined('_JEXEC') or die();

use Joomla\CMS\Cache\CacheControllerFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Constants\ProductCondition;

class Schema
{
    public static function injectProductSchema(string $context, Registry $schema): void
    {
        [$extension, $view, $id] = explode('.', $context);

        if ($extension !== 'com_phocacart') {
            return;
        }

        if (!\in_array($view, ['item'])) {
            return;
        }

        $params = \PhocacartUtils::getComponentParameters();

        if (!$params->get('schema_product', 1)) {
            return;
        }

        $app = Factory::getApplication();

        $mySchema = $schema->toArray();
        if (!isset($mySchema['@graph']) || !\is_array($mySchema['@graph'])) {
            return;
        }

        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . str_replace('.', '/', $context);

        foreach ($mySchema['@graph'] as $entry) {
            if (isset($entry['@id']) && $entry['@id'] == $schemaId) {
                return;
            }
        }

        $additionalSchemas = [];

        $enableCache = $params->get('schema_cache', 1);

        $cache = Factory::getContainer()->get(CacheControllerFactory::class)
            ->createCacheController('Callback', ['lifetime' => $app->get('cachetime'), 'caching' => $enableCache, 'defaultgroup' => 'schemaorg']);

        if ($view == 'item' && $id > 0) {
            $additionalSchemas = $cache->get(function ($id) use ($baseId, $params) {
                $product = \PhocacartProduct::getProduct($id);

                $productSchema = self::createProductSchema($product, $baseId, $params);

                return [$productSchema];
            }, [$id]);
        }

        if (!empty($additionalSchemas)) {
            $mySchema['@graph'] = array_merge($mySchema['@graph'], $additionalSchemas);
        }

        $schema->set('@graph', $mySchema['@graph']);
    }

    private static function createProductSchema(object $product, string $baseId, Registry $params): array
    {
        $schemaId = $baseId . 'com_phocacart/item/' . (int) $product->id;
        $app      = Factory::getApplication();
        $schema = [];
        $schema['@type'] = 'Product';
        $schema['@id'] = $schemaId;
        $schema['productID'] = $product->id;
        if ($product->title_long) {
            $schema['name'] = $product->title_long;
        } else {
            $schema['name'] = $product->title;
        }
        $schema['inLanguage'] = $product->language === '*' ? $app->get('language') : $product->language;
        if ($product->description) {
            $schema['description'] = HTMLHelper::_('string.truncate', $product->description, 0, true, false);
        } elseif ($product->description_long) {
            $schema['description'] = HTMLHelper::_('string.truncate', $product->description_long, 0, true, false);
        } elseif ($product->metadesc) {
            $schema['description'] = $product->metadesc;
        }
        if ($product->cattitle) {
            $schema['category'] = $product->cattitle;
        }
        if ($product->image) {
            $path = \PhocacartPath::getPath('productimage');
            $image = \PhocacartImage::getThumbnailName($path, $product->image, 'large');
            if ($params->get('display_webp_images') && ($image->rel_webp ?? null)) {
                $schema['image'] = Uri::root() . $image->rel_webp;
            } elseif ($image->rel ?? null) {
                $schema['image'] = Uri::root() . $image->rel;
            }
        }
        if ($product->sku) {
            $schema['sku'] = $product->sku;
        }
        if ($product->mpn) {
            $schema['mpn'] = $product->mpn;
        }
        if ($product->ean) {
            $schema['gtin'] = $product->ean;
        }
        elseif ($product->upc) {
            $schema['gtin'] = $product->upc;
        }
        elseif ($product->isbn) {
            $schema['gtin'] = $product->isbn;
        }
        elseif ($product->jan) {
            $schema['gtin'] = $product->ean;
        }

        $offers = [];
        $offers['@type'] = 'Offer';
        $offers['price'] = $product->price;
        $offers['priceCurrency'] = \PhocacartCurrency::getCurrency()->code;
        $offers['url'] = Route::_(\PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias), true, Route::TLS_IGNORE, true);
        switch ($product->condition) {
            case ProductCondition::New:
            default:
                $offers['itemCondition'] = 'http://schema.org/NewCondition';
                break;
            case ProductCondition::Refurbished:
                $offers['itemCondition'] = 'https://schema.org/RefurbishedCondition';
                break;
            case ProductCondition::Used:
                $offers['itemCondition'] = 'https://schema.org/UsedCondition';
                break;
            case ProductCondition::Damaged:
                $offers['itemCondition'] = 'https://schema.org/DamagedCondition';
                break;
        }
        if ($product->stock > 0) {
            $offers['availability'] = 'https://schema.org/InStock';
        } else {
            $offers['availability'] = 'https://schema.org/OutOfStock';
        }
        // TODO Shipping details - Select all shipping methods and display cheapest?
        $schema['offers'] = $offers;

        if ($product->manufacturertitle) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product->manufacturertitle,
            ];
        }

        if ($displayRating = $params->get('display_star_rating', 0)) {
            $reviews = \PhocacartReview::getReviewsByProduct($product->id);

            if ($reviews) {
                $ratingSum = 0;
                $schema['review'] = [];
                foreach ($reviews as $review) {
                    if ($review->rating > 0 || $displayRating == 2) {
                        $ratingSum += $review->rating;
                        $schema['review'][] = [
                            '@type' => 'Review',
                            'reviewRating' => [
                                '@type' => 'Rating',
                                'ratingValue' => $review->rating,
                                'bestRating'  => 5,
                                'worstRating' => 1,
                            ],
                            'author' => [
                                '@type' => 'Person',
                                'name' => $review->name,
                            ],
                            'reviewBody' => $review->review,
                            'datePublished' => $review->date,
                        ];
                    }
                }

                $schema['aggregateRating'] = [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => round($ratingSum / count($reviews), 1),
                    'bestRating'  => 5,
                    'worstRating' => 1,
                    'ratingCount' => count($reviews),
                    'reviewCount' => count($reviews),
                ];
            }
        }

        $schema['isPartOf'] = ['@id' => $baseId . 'WebPage/base'];

        return $schema;
    }
}
