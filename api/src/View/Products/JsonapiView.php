<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\View\Products;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\Event\OnGetApiFields;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\PhocaCart\Api\Serializer\ProductsSerializer;
use Joomla\Component\PhocaCart\Api\View\BaseJsonApiView;
use Tobscure\JsonApi\Collection;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The products view
 *
 * @since  4.0.0
 */
class JsonApiView extends BaseJsonApiView
{
    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $model = 'items';

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $modelItem = 'item';

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'title',
        'alias',
        'title_long',
        'sku',
        'upc',
        'ean',
        'jan',
        'isbn',
        'mpn',
        'serial_number',
        'registration_key',
        'external_id',
        'external_key',
        'external_link',
        'external_text',
        'external_link2',
        'external_text2',
        'featured',
        'featured_background_image',
        'price',
        'price_original',
        'tax_id',
        'catid_multiple',
        'catid',
        'manufacturer_id',
        'ordering',
        'access',
        'group',
        'description',
        'description_long',
        'features',
        'stock',
        'stock_calculation',
        'min_quantity',
        'min_multiple_quantity',
        'min_quantity_calculation',
        'stockstatus_a_id',
        'stockstatus_n_id',
        'related',
        'image',
        'special_parameter',
        'special_image',
        'public_download_file',
        'public_download_text',
        'public_play_file',
        'public_play_text',
        'video',
        'download_folder',
        'download_file',
        'download_days',
        'download_token',
        'download_hits',
        'unit_amount',
        'unit_unit',
        'length',
        'width',
        'height',
        'weight',
        'volume',
        'points_needed',
        'points_received',
        'condition',
        'type_feed',
        'type_category_feed',
        'delivery_date',
        'metatitle',
        'metakey',
        'metadesc',
        'metadata',
        'additional_images',
        'attributes',
        'specifications',
        'discounts',
        'additional_download_files',
        'published',
        'type',
        'language',
        'date',
        'date_update',
        'tags',
        'taglabels',
        'created',
        'created_by',
        'modified',
        'modified_by',
        'hits',
        'sales',
        'gift_types',
        'internal_comment',
        'ai_keywords',
    ];

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'alias',
        'title_long',
        'sku',
        'upc',
        'ean',
        'jan',
        'isbn',
        'mpn',
        'external_id',
        'external_key',
        'featured',
        'price',
        'price_original',
        'tax_id',
        'catid_multiple',
        'catid',
        'manufacturer_id',
        'ordering',
        'access',
        'group',
        'stock',
        'min_quantity',
        'min_multiple_quantity',
        'min_quantity_calculation',
        'image',
        'unit_amount',
        'unit_unit',
        'length',
        'width',
        'height',
        'weight',
        'volume',
        'condition',
        'type_feed',
        'type_category_feed',
        'delivery_date',
        'metatitle',
        'metakey',
        'metadesc',
        'metadata',
        'discounts',
        'published',
        'type',
        'language',
        'date',
        'date_update',
        'internal_comment',
    ];

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $relationship = [
        'category',
        'manufacturer',
        'created_by',
        'modified_by',
    ];

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $fieldsToResult = [
        'id',
        'success',
        'error',
        'count',
    ];

    public function __construct($config = [])
    {
        if (\array_key_exists('contentType', $config)) {
            $this->serializer = new ProductsSerializer($config['contentType']);
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    public function displayItem($item = null)
    {
        if (FieldsHelper::getFields('com_phocacart.phocacartitem')) {
            $this->fieldsToRenderItem[] = 'fields';
        }

        if (Multilanguage::isEnabled()) {
            $this->fieldsToRenderItem[] = 'languageAssociations';
            $this->relationship[] = 'languageAssociations';
        }

        return parent::displayItem();
    }

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected function prepareItem($item)
    {
        foreach (FieldsHelper::getFields('com_phocacart.phocacartitem', $item, true) as $field) {
            if (!isset($item->fields))
                $item->fields = new \stdClass();

            $item->fields->{$field->name} = $field->apivalue ?? $field->rawvalue;
        }

        if (Multilanguage::isEnabled() && !empty($item->associations)) {
            $associations = [];

            foreach ($item->associations as $language => $association) {
                $itemId = explode(':', $association)[0];

                $associations[] = (object) [
                    'id'       => $itemId,
                    'language' => $language,
                ];
            }

            $item->associations = $associations;
        }

        if (in_array($item->stock_calculation, [2, 3])) {
            $item->fields->variants = [];
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__phocacart_product_stock')
                ->where('product_id = ' . $item->id);
            $db->setQuery($query);
            $variants = $db->loadObjectList();
            if ($variants) {
                $item->fields->variants = $variants;
            }
        }

        return parent::prepareItem($item);
    }

    public function displayresults(array $items)
    {
        $this->type = 'results';
        $serializer = new JoomlaSerializer('results');

        $eventData = ['type' => OnGetApiFields::LIST, 'fields' => $this->fieldsToResult, 'context' => $this->type];
        $event     = new OnGetApiFields('onApiGetFields', $eventData);

        /** @var OnGetApiFields $eventResult */
        $eventResult = Factory::getApplication()->getDispatcher()->dispatch('onApiGetFields', $event);

        $collection = (new Collection($items, $serializer))
            ->fields([$this->type => $eventResult->getAllPropertiesToRender()]);

        // Set the data into the document and render it
        $this->document->setData($collection);

        return $this->document->render();

    }
}
