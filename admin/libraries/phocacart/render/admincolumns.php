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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Phoca\PhocaCart\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Phoca\PhocaCart\Html\Grid\HtmlGridHelper;

class PhocacartRenderAdmincolumns
{
    private PhocacartRenderAdminviews $r;

    /*
     * The Flow
     *
     * view.html.php
     * -> get all columns set by options
     *
     * HEADER (default.php) RENDER COLUMN HEADER
     *
     * -> renderHeader() -> all columns iterate in foreach
     * -> header() -> column will be transformed to function
     * -> e.g. skuHeader() -> all column data will be defined here, possible change for each view
     * -> renderHeaderColumn() -> rendering of column HTML header
     *
     * BODY (default.php) RENDER COLUMN BODY
     *
     * -> foreach -> all columns iterate in default.php
     * -> item -> column will be transformed to function
     * -> e.g. sku -> commonColumn -> sku function calls commonColumn (e.g. title renders HTML immediately) - possible change for each view
     * -> commonColumn() -> rendering of column HTML body
     *
     * SORTFIELDS (view.html.php) RENDER SORT FIELDS
     *
     * -> renderSortFields() -> all columns iterate in foreach
     * -> header() -> column will be transformed to function
     * -> e.g. skuHeader -> all column data will be defined here, possible change for each view - RETURNS DATA!!!, does not render column header
     */
    public function __construct()
    {
        $this->r = new PhocacartRenderAdminviews();
    }

    public function renderHeader($items, &$options)
    {
        $o = array();
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                $v   = PhocacartText::parseDbColumnParameter($v);
                $o[] = $this->header($v, $options);
            }
        }

        return implode("\n", $o);
    }

    public function getSortFields($items, &$options)
    {
        $sortFields = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $item = PhocacartText::parseDbColumnParameter($item);
                $data = $this->header($item, $options);
                if ($data['column'] ?? '') {
                    $id     = $data['column'];
                    $sortFields[$id] = Text::_($data['title']);
                }
            }
        }

        return $sortFields;
    }

    public function renderHeaderColumn($data, &$options)
    {
        if ($options['type'] == 'data') {
            return $data;
        }

        $options['count']++;

        if (isset($data['tool']) && $data['tool'] != '' && isset($data['column']) && $data['column'] != '') {
            return '<th class="' . $data['class'] . '">' . HTMLHelper::_($data['tool'], $data['title'], $data['column'], $options['listdirn'], $options['listorder']) . '</th>';
        }

        return '<th class="' . $data['class'] . '">' . Text::_($data['title']) . '</th>';
    }

    public function header($function, &$options)
    {
        if (!$function) {
            return false;
        }

        $function = strtolower($function) . 'Header';
        if (!is_callable(array($this, $function))) {
            $app = Factory::getApplication();
            $app->enqueueMessage(__CLASS__ . ': Function ' . $function . ' not supported', $app::MSG_WARNING);

            return false;
        }

        return call_user_func_array([$this, $function], [&$options]);
    }

    public function item($function, $item, &$options)
    {
        if (!$function) {
            return false;
        }

        $function = strtolower($function);
        if (!is_callable(array($this, $function))) {
            $app = Factory::getApplication();
            $app->enqueueMessage(__CLASS__ . ': Function ' . $function . ' not supported', $app::MSG_WARNING);

            return false;
        }

        return call_user_func_array([$this, $function], [$item, &$options]);
    }


    /* COLUMN HEADERS */

    public function skuHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-sku',
            'title'  => 'COM_PHOCACART_FIELD_SKU_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.sku'
        ], $options);
    }

    public function titleHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-title',
            'title'  => 'COM_PHOCACART_FIELD_TITLE_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.title'
        ], $options);
    }

    public function imageHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-image',
            'title'  => 'COM_PHOCACART_FIELD_IMAGE_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.image'
        ], $options);
    }

    public function publishedHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-published',
            'title'  => 'COM_PHOCACART_FIELD_PUBLISHED_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.published'
        ], $options);
    }

    public function categoryHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class' => 'ph-parentcattitle',
            'title' => 'COM_PHOCACART_FIELD_CATEGORY_LABEL'
        ], $options);
    }

    public function categoriesHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class' => 'ph-parentcattitle',
            'title' => 'COM_PHOCACART_FIELD_CATEGORY_LABEL'
        ], $options);
    }

    public function priceHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-price',
            'title'  => 'COM_PHOCACART_FIELD_PRICE_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.price'
        ], $options);
    }

    public function price_originalHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-price_original',
            'title'  => 'COM_PHOCACART_FIELD_ORIGINAL_PRICE_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.price_original'
        ], $options);
    }

    public function discount_percentHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-discount-percent',
            'title'  => 'COM_PHOCACART_FIELD_DISCOUNT_PERCENT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.discount_percent'
        ], $options);
    }

    public function stockHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-stock',
            'title'  => 'COM_PHOCACART_FIELD_IN_STOCK_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.stock'
        ], $options);
    }

    public function access_levelHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class' => 'ph-access',
            'title' => 'JFIELD_ACCESS_LABEL'
        ], $options);
    }

    public function associationHeader(&$options)
    {
        if ($options['association'] ?? false) {
            return $this->renderHeaderColumn([
                'class' => 'ph-association',
                'title' => 'COM_PHOCACART_FIELD_ASSOCIATION_LABEL'
            ], $options);
        }

        if ($options['type'] == 'data') {
            return [];
        }

        return '';
    }

    public function languageHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-language',
            'title'  => 'JGRID_HEADING_LANGUAGE',
            'tool'   => 'searchtools.sort',
            'column' => 'a.language'
        ], $options);
    }

    public function hitsHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-hits',
            'title'  => 'COM_PHOCACART_FIELD_HITS_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.hits'
        ], $options);
    }

    public function descriptionHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-description',
            'title'  => 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.description'
        ], $options);
    }

    public function phoca_actionHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class' => 'ph-edit',
            'title' => 'COM_PHOCACART_ACTION_LABEL'
        ], $options);
    }

    public function phoca_infoHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class' => 'ph-edit',
            'title' => 'COM_PHOCACART_INFO_LABEL'
        ], $options);
    }

    public function idHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-id',
            'title'  => 'JGLOBAL_FIELD_ID_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.id'
        ], $options);
    }

    public function upcHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_UPC_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.upc'
        ], $options);
    }

    public function eanHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_EAN_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.ean'
        ], $options);
    }

    public function janHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_JAN_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.jan'
        ], $options);
    }

    public function isbnHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_ISBN_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.isbn'
        ], $options);
    }

    public function mpnHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_MPN_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.mpn'
        ], $options);
    }

    public function serial_numberHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-upc',
            'title'  => 'COM_PHOCACART_FIELD_SERIAL_NUMBER_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.serial_number'
        ], $options);
    }

    public function registration_keyHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-registration-key',
            'title'  => 'COM_PHOCACART_FIELD_REGISTRATION_KEY_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.registration_key'
        ], $options);
    }

    public function external_idHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-id',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_ID_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_id'
        ], $options);
    }

    public function external_keyHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-key',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_KEY_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_key'
        ], $options);
    }

    public function external_linkHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-link',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_LINK_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_link'
        ], $options);
    }

    public function external_textHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-text',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_text'
        ], $options);
    }

    public function external_link2Header(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-link2',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_LINK_2_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_link2'
        ], $options);
    }

    public function external_text2Header(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-external-text2',
            'title'  => 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_2_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.external_text2'
        ], $options);
    }

    public function min_quantityHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-min-quantity',
            'title'  => 'COM_PHOCACART_FIELD_MIN_ORDER_QUANTITY_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.min_quantity'
        ], $options);
    }

    public function max_quantityHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-max-quantity',
            'title'  => 'COM_PHOCACART_FIELD_MAX_ORDER_QUANTITY_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.max_quantity'
        ], $options);
    }

    public function min_multiple_quantityHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-min-multiple-quantity',
            'title'  => 'COM_PHOCACART_FIELD_MIN_MULTIPLE_ORDER_QUANTITY_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.min_multiple_quantity'
        ], $options);
    }

    public function unit_amountHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-unit-amount',
            'title'  => 'COM_PHOCACART_FIELD_UNIT_AMOUNT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.unit_amount'
        ], $options);
    }

    public function unit_unitHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-unit-unit',
            'title'  => 'COM_PHOCACART_FIELD_UNIT_UNIT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.unit_unit'
        ], $options);
    }

    public function lengthHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-length',
            'title'  => 'COM_PHOCACART_FIELD_LENGTH_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.length'
        ], $options);
    }

    public function widthHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-width',
            'title'  => 'COM_PHOCACART_FIELD_WIDTH_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.width'
        ], $options);
    }

    public function heightHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-height',
            'title'  => 'COM_PHOCACART_FIELD_HEIGHT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.height'
        ], $options);
    }

    public function weightHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-weight',
            'title'  => 'COM_PHOCACART_FIELD_WEIGHT_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.weight'
        ], $options);
    }

    public function volumeHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-volume',
            'title'  => 'COM_PHOCACART_FIELD_VOLUME_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.volume'
        ], $options);
    }

    public function points_neededHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-points-needed',
            'title'  => 'COM_PHOCACART_FIELD_POINTS_NEEDED_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.points_needed'
        ], $options);
    }

    public function points_receivedHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-points-received',
            'title'  => 'COM_PHOCACART_FIELD_POINTS_RECEIVED_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.points_received'
        ], $options);
    }

    public function metatitleHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-metatitle',
            'title'  => 'COM_PHOCACART_FIELD_META_TITLE_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.metatitle'
        ], $options);
    }

    public function description_longHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-description-long',
            'title'  => 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.description_long'
        ], $options);
    }

    public function featuresHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-features',
            'title'  => 'COM_PHOCACART_FIELD_FEATURES_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.features'
        ], $options);
    }

    public function videoHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-video',
            'title'  => 'COM_PHOCACART_FIELD_VIDEO_URL_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.video'
        ], $options);
    }

    public function type_feedHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-type-feed',
            'title'  => 'COM_PHOCACART_FIELD_PRODUCT_TYPE_FEED_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.type_feed'
        ], $options);
    }

    public function type_category_feedHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-type-category-feed',
            'title'  => 'COM_PHOCACART_FIELD_PRODUCT_CATEGORY_TYPE_FEED_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.type_category_feed'
        ], $options);
    }

    public function metakeyHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-metakey',
            'title'  => 'JFIELD_META_KEYWORDS_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.metakey'
        ], $options);
    }

    public function metadescHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-metadesc',
            'title'  => 'JFIELD_META_DESCRIPTION_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.metadesc'
        ], $options);
    }

    public function special_parameterHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-special_parameter',
            'title'  => 'COM_PHOCACART_FIELD_SPECIAL_PARAMETER_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'a.special_parameter'
        ], $options);
    }

    public function manufacturerHeader(&$options)
    {
        return $this->renderHeaderColumn([
            'class'  => 'ph-manufacturer',
            'title'  => 'COM_PHOCACART_FIELD_MANUFACTURER_LABEL',
            'tool'   => 'searchtools.sort',
            'column' => 'pm.title as manufacturer'
        ], $options);
    }

    /* COLUMN BODY */
    public function commonColumn($item, $options)
    {
        if ($item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            return $this->r->td('<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['name'] . '" id="' . $item['idtoken'] . '">' . PhocacartText::filterValue($item['value'], $item['editfilter']) . '</span>', 'small');
        } else {
            return $this->r->td('<span class="ph-' . $item['name'] . '">' . PhocacartText::filterValue($item['value'], $item['editfilter']) . '</span>', 'small');
        }
    }

    public function sku($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function upc($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function ean($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function jan($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function isbn($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function mpn($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function serial_number($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function registration_key($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_id($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_key($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_link($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_text($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_link2($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function external_text2($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function min_quantity($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function max_quantity($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function min_multiple_quantity($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function unit_amount($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function unit_unit($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function length($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function width($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function height($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function weight($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function volume($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function points_needed($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function points_received($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function metatitle($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function description_long($item, &$options)
    {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function features($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function video($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function type_feed($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function type_category_feed($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function metakey($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function metadesc($item, &$options)
    {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function special_parameter($item, &$options)
    {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }


    public function price($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function price_original($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function discount_percent($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']) . '%';

        return $this->commonColumn($item, $options);
    }

    public function stock($item, &$options)
    {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);

        return $this->commonColumn($item, $options);
    }

    public function access_level($item, &$options)
    {
        $item['params']['edit'] = false;

        return $this->commonColumn($item, $options);
    }

    public function hits($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function id($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

    public function description($item, &$options)
    {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';

        return $this->commonColumn($item, $options);
    }

    public function image($item, &$options)
    {
        $thumbnail = PhocacartFileThumbnail::getThumbnailName(PhocacartText::filterValue($item['value'], 'text'), 'small', 'productimage');
        $img = '';

        $thumbnail->rel = PhocacartUtils::getSvgOriginalInsteadThumb($thumbnail->rel);// SVG image
        $thumbnail->abs = PhocacartUtils::getSvgOriginalInsteadThumb($thumbnail->abs);// SVG image

        if (File::exists($thumbnail->abs)) {

            $img = '<img src="' . Uri::root() . $thumbnail->rel . '?imagesid=' . md5(uniqid(time())) . '" />';
        }

        return $this->r->td('<span class="ph-' . $item['name'] . '">' . $img . '</span>', 'small ph-items-image-box');
    }

    /*
     * $item['params']['edit'] ... defined by user in Phoca Cart options: to display title: "title", to display editable title: "title=E"
     * $item['canedit'] ... defined by Joomla! permissions system
     */
    public function title($item, &$options)
    {
        $paramsC         = PhocacartUtils::getComponentParameters();
        $admin_eip_title = $paramsC->get('admin_eip_title', 4);

        $o = array();
        if ($item['checked_out']) {
            $o[] = HTMLHelper::_('jgrid.checkedout', $item['i'], $item['editor'], $item['checked_out_time'], $options['tasks'] . '.', $item['cancheckin']);
        }

        if ($item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            $o[] = '<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['name'] . ' phIdTitle' . (int) $item['id'] . '" id="' . $item['idtoken'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span>';

            if ($admin_eip_title == 3 || $admin_eip_title == 4) {
                $o[] = '<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['namealias'] . '" id="' . $item['idtokencombined'] . '">' . PhocacartText::filterValue($item['valuealias'], 'text') . '</span>';
            }
        } else if (!$item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            if (isset($item['linkeditbox']) && $item['linkeditbox'] != '') {
                $o[] = $item['linkeditbox'];
            } else {
                $o[] = '<a href="' . Route::_($item['linkedit']) . '"><span id="phIdTitle' . (int) $item['id'] . '" class="ph-' . $item['name'] . ' phIdTitle' . (int) $item['id'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span></a>';
            }
            $o[] = '<br /><span class="smallsub">(<span>' . Text::_('COM_PHOCACART_FIELD_ALIAS_LABEL') . ':</span>' . PhocacartText::filterValue($item['valuealias'], 'text') . ')</span>';

        } else {
            // Class phIdTitle needed for displaying Copy Attributes Titles
            $o[] = '<span id="phIdTitle' . (int) $item['id'] . '" class="ph-' . $item['name'] . ' phIdTitle' . (int) $item['id'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span>';
            $o[] = '<br /><span class="smallsub">(<span>' . Text::_('COM_PHOCACART_FIELD_FIELD_ALIAS_LABEL') . ':</span>' . PhocacartText::filterValue($item['valuealias'], 'text') . ')</span>';
        }

        return $this->r->td(implode("\n", $o), 'small');
    }

    public function published($item, &$options)
    {
        return $this->r->td('<div>' .
            HtmlGridHelper::stateButton('phocacartitems', $item['id'], $item['value'], $item['canchange']) .
            HtmlGridHelper::featuredButton('phocacartitems', $item['id'], $item['valuefeatured'], $item['canchange']) .
            '</div>', 'small');
    }

    public function categories($item, &$options)
    {

        $o  = array();
        $id = $item['id'];

        if (isset($item['value'][$id])) {
            foreach ($item['value'][$id] as $k => $v) {
                if ($item['caneditcategory'] && isset($item['linkeditcategory']) && $item['linkeditcategory'] != '') {
                    $linkCat = Route::_($item['linkeditcategory'] . '&id=' . (int) $v['id']);
                    $o[]     = '<a href="' . Route::_($linkCat) . '">' . PhocacartText::filterValue($v['title'], 'text') . '</a>';
                } else {
                    $o[] = PhocacartText::filterValue($v['title'], 'text');
                }
            }
        }

        return $this->r->td(implode(",\n", $o), 'small');
    }

    public function association($item, &$options)
    {
        if (!isset($options['association']) || (isset($options['association']) && !$options['association'])) {
            return '';
        } else if ($item['value']) {
            return $this->r->td(HTMLHelper::_('phocacartitem.association', $item['id']));
        } else {
            return $this->r->td('');
        }
    }

    public function language($item, &$options)
    {
        return $this->r->td(LayoutHelper::render('joomla.content.language', $item['value']), 'small');
    }

    public function phoca_action($item, &$options)
    {

        if ($item['cancreate'] || $item['canedit']) {
            if (isset($item['linkeditbox']) && $item['linkeditbox'] != '') {
                return $this->r->td(str_replace('<a ', '<a class="ph-no-underline"', $item['linkeditbox']), '');
            } else {
                return $this->r->td('<a class="pha-no-underline" href="' . Route::_($item['linkedit']) . '"><span id="phIdTitle' . $item['id'] . '" class="ph-icon-task ph-cp-item"><i class="duotone icon-apply"></i></span></a>', '');
            }

        }
    }

    public function phoca_info($item, &$options)
    {


        $pC                        = PhocacartUtils::getComponentParameters();
        $admin_columns_info_column = $pC->get('admin_columns_info_column', []);


        $o = [];
        if (isset($item['id']) && $item['id'] > 0) {

            $o[] = '<div class="ph-info-column">';


            // ATTRIBUTES
            if (in_array('1', $admin_columns_info_column) || (in_array('2', $admin_columns_info_column))) {

                $attributes = PhocacartAttribute::getAttributesAndOptions($item['id']);
                if (!empty($attributes)) {

                    // $o[] = '<div class="ph-info-column-attributes">'.Text::_('COM_PHOCACART_ATTRIBUTES').'</div>';
                    $o[] = '<div class="ph-label-box">';

                    foreach ($attributes as $k => $v) {

                        $o[] = '<span class="badge bg-info" title="' . Text::_('COM_PHOCACART_ATTRIBUTES') . '">' . $v->title . '</span>';

                        if (!empty($v->options) && in_array('2', $admin_columns_info_column)) {
                            // $o[] = '<div class="phAttributes">'.Text::_('COM_PHOCACART_OPTIONS').'</div>';
                            $o[] = '<div class="ph-label-box">';
                            foreach ($v->options as $k2 => $v2) {
                                $o[] = '<span class="badge bg-primary" title="' . Text::_('COM_PHOCACART_OPTIONS') . '">' . $v2->title . '</span>';

                            }
                            $o[] = '</div>';
                        }

                    }
                    $o[] = '</div>';
                }
            }

            // SPECIFICATIONS
            if (in_array('3', $admin_columns_info_column)) {
                $specificationGrops = PhocacartSpecification::getSpecificationGroupsAndSpecifications($item['id']);

                if (!empty($specificationGrops)) {

                    $o[] = '<div class="ph-label-box">';

                    foreach ($specificationGrops as $k => $v) {

                        $newV = $v;
                        unset($newV[0]);
                        if (!empty($newV)) {
                            $o[] = '<div class="ph-label-box">';
                            foreach ($newV as $k2 => $v2) {
                                $o[] = '<span class="badge bg-success" title="' . Text::_('COM_PHOCACART_SPECIFICATIONS') . ' - ' . Text::_('COM_PHOCACART_GROUP') . ': ' . $v[0] . '">' . $v2['title'] . ': ' . $v2['value'] . '</span>';
                            }
                            $o[] = '</div>';
                        }
                    }

                    $o[] = '</div>';
                }
            }


            // TAGS
            if (in_array('4', $admin_columns_info_column)) {
                $tags = PhocacartTag::getTags($item['id']);
                if (!empty($tags)) {

                    $o[] = '<div class="ph-label-box">';

                    foreach ($tags as $k => $v) {
                        $o[] = '<span class="badge bg-warning" title="' . Text::_('COM_PHOCACART_TAGS') . '">' . $v->title . '</span>';
                    }
                    $o[] = '</div>';
                }
            }

            // LABELS
            if (in_array('5', $admin_columns_info_column)) {
                $labels = PhocacartTag::getTagLabels($item['id']);
                if (!empty($labels)) {

                    $o[] = '<div class="ph-label-box">';

                    foreach ($labels as $k => $v) {
                        $o[] = '<span class="badge bg-danger" title="' . Text::_('COM_PHOCACART_LABELS') . '">' . $v->title . '</span>';
                    }
                    $o[] = '</div>';
                }
            }


            $o[] = '</div>';

        }


        return $this->r->td(implode("\n", $o), '');
    }

    public function manufacturer($item, &$options)
    {
        return $this->commonColumn($item, $options);
    }

}


/* Supported columns - Products

sku
image
title (title=E - in options, there is parameter: Title - Edit in Place which manages alias behavior)
published
categories
price
price_original
stock
access_level
language
association
hits
id

upc
ean
jan
isbn
mpn
serial_number
registration_key
external_id
external_key
external_link
external_text
external_link2
external_text2
min_quantity
max_quantity
min_multiple_quantity
unit_amount
unit_unit
length
width
height
weight
volume
points_needed
points_received
metatitle
description
description_long
features
video
type_feed
type_category_feed
metakey
metadesc
special_parameter
phoca_action
phoca_info
*/
