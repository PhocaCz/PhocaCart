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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

class PhocacartRenderAdmincolumns
{
    public $view = '';
    public $option = '';
    public $tmpl = '';
    public $r = false;
    public $compatible = false;


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


    public function __construct() {

        $app              = Factory::getApplication();
        $version          = new Version();
        $this->compatible = $version->isCompatible('4.0.0-alpha');
        $this->view       = $app->input->get('view');
        $this->option     = $app->input->get('option');
        $this->tmpl       = $app->input->get('tmpl');
        $this->r          = new PhocacartRenderAdminviews();

    }


    public function renderHeader($items, &$options) {
        $o = array();
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                $v   = PhocacartText::parseDbColumnParameter($v);
                $o[] = $this->header($v, $options);
            }
        }
        return implode("\n", $o);
    }

    public function getSortFields($items, &$options) {
        $o = array();
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                $v    = PhocacartText::parseDbColumnParameter($v);
                $data = $this->header($v, $options);
                if (isset($data['column']) && $data['column'] != '') {
                    $id     = $data['column'];
                    $o[$id] = Text::_($data['title']);
                }
            }
        }
        return $o;
    }

    public function renderHeaderColumn($data, &$options) {

        if ($options['type'] == 'data') {
            return $data;
        }

        $options['count']++;

        $o = array();
        if (isset($data['tool']) && $data['tool'] != '' && isset($data['column']) && $data['column'] != '') {
            $o[] = '<th class="' . $data['class'] . '">' . HTMLHelper::_($data['tool'], $data['title'], $data['column'], $options['listdirn'], $options['listorder']) . '</th>';
        } else {
            $o[] = '<th class="' . $data['class'] . '">' . Text::_($data['title']) . '</th>';
        }

        return implode("\n", $o);

    }

    public function header($function, &$options) {

        if ($function == '') {
            return false;
        }

        $function = strtolower($function) . 'Header';
        if (!is_callable(array($this, $function))) {
            throw new \InvalidArgumentException('Function ' . $function . ' not supported', 500);
        }

        return call_user_func_array(array($this, $function), array(&$options));
    }

    public function item($function, $item, &$options) {

        if ($function == '') {
            return false;
        }

        $function = strtolower($function) . '';
        if (!is_callable(array($this, $function))) {
            throw new \InvalidArgumentException('Function ' . $function . ' not supported', 500);
        }

        return call_user_func_array(array($this, $function), array($item, &$options));
    }


    /* COLUMN HEADER */

    public function skuHeader(&$options) {
        $data = array('class' => 'ph-sku', 'title' => 'COM_PHOCACART_FIELD_SKU_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.sku');
        return $this->renderHeaderColumn($data, $options);
    }

    public function titleHeader(&$options) {
        $data = array('class' => 'ph-title', 'title' => 'COM_PHOCACART_FIELD_TITLE_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.title');
        return $this->renderHeaderColumn($data, $options);
    }

    public function imageHeader(&$options) {
        $data = array('class' => 'ph-image', 'title' => 'COM_PHOCACART_FIELD_IMAGE_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.image');
        return $this->renderHeaderColumn($data, $options);
    }

    public function publishedHeader(&$options) {
        $data = array('class' => 'ph-published', 'title' => 'COM_PHOCACART_FIELD_PUBLISHED_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.published');
        return $this->renderHeaderColumn($data, $options);
    }

    public function categoryHeader(&$options) {
        $data = array('class' => 'ph-parentcattitle', 'title' => 'COM_PHOCACART_FIELD_CATEGORY_LABEL');
        return $this->renderHeaderColumn($data, $options);
    }

    public function categoriesHeader(&$options) {
        $data = array('class' => 'ph-parentcattitle', 'title' => 'COM_PHOCACART_FIELD_CATEGORY_LABEL');
        return $this->renderHeaderColumn($data, $options);
    }

    public function priceHeader(&$options) {
        $data = array('class' => 'ph-price', 'title' => 'COM_PHOCACART_FIELD_PRICE_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.price');
        return $this->renderHeaderColumn($data, $options);
    }

    public function price_originalHeader(&$options) {
        $data = array('class' => 'ph-price_original', 'title' => 'COM_PHOCACART_FIELD_ORIGINAL_PRICE_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.price_original');
        return $this->renderHeaderColumn($data, $options);
    }

    public function discount_percentHeader(&$options) {
        $data = array('class' => 'ph-discount-percent', 'title' => 'COM_PHOCACART_FIELD_DISCOUNT_PERCENT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.discount_percent');
        return $this->renderHeaderColumn($data, $options);
    }

    public function stockHeader(&$options) {
        $data = array('class' => 'ph-stock', 'title' => 'COM_PHOCACART_FIELD_IN_STOCK_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.stock');
        return $this->renderHeaderColumn($data, $options);
    }

    public function access_levelHeader(&$options) {
        $data = array('class' => 'ph-access', 'title' => 'JFIELD_ACCESS_LABEL');
        return $this->renderHeaderColumn($data, $options);
    }

    public function associationHeader(&$options) {
        $data = array('class' => 'ph-association', 'title' => 'COM_PHOCACART_FIELD_ASSOCIATION_LABEL'/*, 'tool' => 'searchtools.sort', 'column' => 'association'*/);
        if (isset($options['association']) && $options['association']) {
            return $this->renderHeaderColumn($data, $options);
        }
    }

    public function languageHeader(&$options) {
        $data = array('class' => 'ph-language', 'title' => 'JGRID_HEADING_LANGUAGE', 'tool' => 'searchtools.sort', 'column' => 'a.language');
        return $this->renderHeaderColumn($data, $options);
    }

    public function hitsHeader(&$options) {
        $data = array('class' => 'ph-hits', 'title' => 'COM_PHOCACART_FIELD_HITS_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.hits');
        return $this->renderHeaderColumn($data, $options);
    }

    public function descriptionHeader(&$options) {
        $data = array('class' => 'ph-description', 'title' => 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.description');
        return $this->renderHeaderColumn($data, $options);
    }

    public function phoca_actionHeader(&$options) {
        $data = array('class' => 'ph-edit', 'title' => 'COM_PHOCACART_ACTION_LABEL');
        return $this->renderHeaderColumn($data, $options);
    }

    public function phoca_infoHeader(&$options) {
        $data = array('class' => 'ph-edit', 'title' => 'COM_PHOCACART_INFO_LABEL');
        return $this->renderHeaderColumn($data, $options);
    }

    public function idHeader(&$options) {
        $data = array('class' => 'ph-id', 'title' => 'JGLOBAL_FIELD_ID_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.id');
        return $this->renderHeaderColumn($data, $options);
    }

    public function upcHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_UPC_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.upc');
        return $this->renderHeaderColumn($data, $options);
    }

    public function eanHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EAN_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.ean');
        return $this->renderHeaderColumn($data, $options);
    }

    public function janHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_JAN_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.jan');
        return $this->renderHeaderColumn($data, $options);
    }

    public function isbnHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_ISBN_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.isbn');
        return $this->renderHeaderColumn($data, $options);
    }

    public function mpnHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_MPN_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.mpn');
        return $this->renderHeaderColumn($data, $options);
    }

    public function serial_numberHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_SERIAL_NUMBER_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.serial_number');
        return $this->renderHeaderColumn($data, $options);
    }

    public function registration_keyHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_REGISTRATION_KEY_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.registration_key');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_idHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_ID_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_id');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_keyHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_KEY_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_key');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_linkHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_LINK_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_link');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_textHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_text');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_link2Header(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_LINK_2_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_link2');
        return $this->renderHeaderColumn($data, $options);
    }

    public function external_text2Header(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_2_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.external_text2');
        return $this->renderHeaderColumn($data, $options);
    }

    public function min_quantityHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_MIN_ORDER_QUANTITY_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.min_quantity');
        return $this->renderHeaderColumn($data, $options);
    }

    public function min_multiple_quantityHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_MIN_MULTIPLE_ORDER_QUANTITY_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.min_multiple_quantity');
        return $this->renderHeaderColumn($data, $options);
    }

    public function unit_amountHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_UNIT_AMOUNT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.unit_amount');
        return $this->renderHeaderColumn($data, $options);
    }

    public function unit_unitHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_UNIT_UNIT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.unit_unit');
        return $this->renderHeaderColumn($data, $options);
    }

    public function lengthHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_LENGTH_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.length');
        return $this->renderHeaderColumn($data, $options);
    }

    public function widthHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_WIDTH_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.width');
        return $this->renderHeaderColumn($data, $options);
    }

    public function heightHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_HEIGHT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.height');
        return $this->renderHeaderColumn($data, $options);
    }

    public function weightHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_WEIGHT_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.weight');
        return $this->renderHeaderColumn($data, $options);
    }

    public function volumeHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_VOLUME_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.volume');
        return $this->renderHeaderColumn($data, $options);
    }

    public function points_neededHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_POINTS_NEEDED_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.points_needed');
        return $this->renderHeaderColumn($data, $options);
    }

    public function points_receivedHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_POINTS_RECEIVED_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.points_received');
        return $this->renderHeaderColumn($data, $options);
    }

    public function metatitleHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_META_TITLE_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.metatitle');
        return $this->renderHeaderColumn($data, $options);
    }

    public function description_longHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.description_long');
        return $this->renderHeaderColumn($data, $options);
    }

    public function featuresHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_FEATURES_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.features');
        return $this->renderHeaderColumn($data, $options);
    }

    public function videoHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_VIDEO_URL_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.video');
        return $this->renderHeaderColumn($data, $options);
    }

    public function type_feedHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_PRODUCT_TYPE_FEED_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.type_feed');
        return $this->renderHeaderColumn($data, $options);
    }

    public function type_category_feedHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'COM_PHOCACART_FIELD_PRODUCT_CATEGORY_TYPE_FEED_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.type_category_feed');
        return $this->renderHeaderColumn($data, $options);
    }

    public function metakeyHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'JFIELD_META_KEYWORDS_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.metakey');
        return $this->renderHeaderColumn($data, $options);
    }

    public function metadescHeader(&$options) {
        $data = array('class' => 'ph-upc', 'title' => 'JFIELD_META_DESCRIPTION_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.metadesc');
        return $this->renderHeaderColumn($data, $options);
    }

    public function special_parameterHeader(&$options) {
        $data = array('class' => 'ph-special_parameter', 'title' => 'COM_PHOCACART_FIELD_SPECIAL_PARAMETER_LABEL', 'tool' => 'searchtools.sort', 'column' => 'a.special_parameter');
        return $this->renderHeaderColumn($data, $options);
    }





    /* COLUMN BODY */
    public function commonColumn($item, $options) {

        if ($item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            return $this->r->td('<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['name'] . '" id="' . $item['idtoken'] . '">' . PhocacartText::filterValue($item['value'], $item['editfilter']) . '</span>', 'small');
        } else {
            return $this->r->td('<span class="ph-' . $item['name'] . '">' . PhocacartText::filterValue($item['value'], $item['editfilter']) . '</span>', 'small');
        }
    }


    public function sku($item, &$options) { return $this->commonColumn($item, $options); }

    public function upc($item, &$options) { return $this->commonColumn($item, $options); }

    public function ean($item, &$options) { return $this->commonColumn($item, $options); }

    public function jan($item, &$options) { return $this->commonColumn($item, $options); }

    public function isbn($item, &$options) { return $this->commonColumn($item, $options); }

    public function mpn($item, &$options) { return $this->commonColumn($item, $options); }

    public function serial_number($item, &$options) { return $this->commonColumn($item, $options); }

    public function registration_key($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_id($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_key($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_link($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_text($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_link2($item, &$options) { return $this->commonColumn($item, $options); }

    public function external_text2($item, &$options) { return $this->commonColumn($item, $options); }

    public function min_quantity($item, &$options) { return $this->commonColumn($item, $options); }

    public function min_multiple_quantity($item, &$options) { return $this->commonColumn($item, $options); }

    public function unit_amount($item, &$options) { return $this->commonColumn($item, $options); }

    public function unit_unit($item, &$options) { return $this->commonColumn($item, $options); }

    public function length($item, &$options) { $item['value'] = PhocacartPrice::cleanPrice($item['value']); return $this->commonColumn($item, $options); }

    public function width($item, &$options) { $item['value'] = PhocacartPrice::cleanPrice($item['value']); return $this->commonColumn($item, $options); }

    public function height($item, &$options) { $item['value'] = PhocacartPrice::cleanPrice($item['value']); return $this->commonColumn($item, $options); }

    public function weight($item, &$options) { $item['value'] = PhocacartPrice::cleanPrice($item['value']); return $this->commonColumn($item, $options); }

    public function volume($item, &$options) { $item['value'] = PhocacartPrice::cleanPrice($item['value']); return $this->commonColumn($item, $options); }

    public function points_needed($item, &$options) { return $this->commonColumn($item, $options); }

    public function points_received($item, &$options) { return $this->commonColumn($item, $options); }

    public function metatitle($item, &$options) { return $this->commonColumn($item, $options); }


    public function description_long($item, &$options) {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function features($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function video($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function type_feed($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function type_category_feed($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function metakey($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function metadesc($item, &$options) {
        // textarea in features instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function special_parameter($item, &$options) {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }


    public function price($item, &$options) {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);
        return $this->commonColumn($item, $options);
    }

    public function price_original($item, &$options) {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);
        return $this->commonColumn($item, $options);
    }

    public function discount_percent($item, &$options) {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']) . '%';
        return $this->commonColumn($item, $options);
    }

    public function stock($item, &$options) {
        $item['value'] = PhocacartPrice::cleanPrice($item['value']);
        return $this->commonColumn($item, $options);
    }

    public function access_level($item, &$options) {
        $item['params']['edit'] = false;
        return $this->commonColumn($item, $options);
    }

    public function hits($item, &$options) {
        return $this->commonColumn($item, $options);
    }

    public function id($item, &$options) {
        //$item['params']['edit'] = false;
        return $this->commonColumn($item, $options);
    }

    public function description($item, &$options) {
        // textarea in description instead of text like e.g. by title, sku, price
        $item['editclass']  = 'autogrow';
        $item['editfilter'] = 'html';
        return $this->commonColumn($item, $options);
    }

    public function image($item, &$options) {

        $thumbnail = PhocacartFileThumbnail::getThumbnailName(PhocacartText::filterValue($item['value'], 'text'), 'small', 'productimage');
        $img       = '';
        if (File::exists($thumbnail->abs)) {
            $img = '<img src="' . Uri::root() . $thumbnail->rel . '?imagesid=' . md5(uniqid(time())) . '" />';
        }

        //if ($item['params']['edit'] == 2) {
        //   $o[] = '<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['name'] . '" id="'.$item['idtoken'].'">' . PhocacartText::filterValue($item['value'], 'text') . '</span>';
        // } else {
        return $this->r->td('<span class="ph-' . $item['name'] . '">' . $img . '</span>', 'small ph-items-image-box');
        // }
    }

    /*
     * $item['params']['edit'] ... defined by user in Phoca Cart options: to display title: "title", to display editable title: "title=E"
     * $item['canedit'] ... defined by Joomla! permissions system
     */

    public function title($item, &$options) {

        $paramsC = PhocacartUtils::getComponentParameters();
        $admin_eip_title = $paramsC->get('admin_eip_title', 4);

        $o = array();
        if ($item['checked_out']) {
            $o[] = HTMLHelper::_('jgrid.checkedout', $item['i'], $item['editor'], $item['checked_out_time'], $options['tasks'] . '.', $item['cancheckin']);
        }

        if ($item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            $o[] = '<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['name'] . ' phIdTitle' . (int)$item['id'] . '" id="' . $item['idtoken'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span>';

            if ($admin_eip_title == 3 || $admin_eip_title == 4) {
                $o[] = '<span class="ph-editinplace-text ph-eip-' . $item['editclass'] . ' ph-eip-' . $item['namealias'] . '" id="' . $item['idtokencombined'] . '">' . PhocacartText::filterValue($item['valuealias'], 'text') . '</span>';
            }
        } else if (!$item['params']['edit'] && ($item['cancreate'] || $item['canedit'])) {
            if (isset($item['linkeditbox']) && $item['linkeditbox'] != '') {
                $o[] = $item['linkeditbox'];
            } else {
                $o[] = '<a href="' . Route::_($item['linkedit']) . '"><span id="phIdTitle' . (int)$item['id'] . '" class="ph-' . $item['name'] . ' phIdTitle' . (int)$item['id'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span></a>';
            }
            $o[] = '<br /><span class="smallsub">(<span>' . Text::_('COM_PHOCACART_FIELD_ALIAS_LABEL') . ':</span>' . PhocacartText::filterValue($item['valuealias'], 'text') . ')</span>';

        } else {
            // Class phIdTitle needed for displaying Copy Attributes Titles
            $o[] = '<span id="phIdTitle' . (int)$item['id'] . '" class="ph-' . $item['name'] . ' phIdTitle' . (int)$item['id'] . '">' . PhocacartText::filterValue($item['value'], 'text') . '</span>';
            $o[] = '<br /><span class="smallsub">(<span>' . Text::_('COM_PHOCACART_FIELD_FIELD_ALIAS_LABEL') . ':</span>' . PhocacartText::filterValue($item['valuealias'], 'text') . ')</span>';
        }

        return $this->r->td(implode("\n", $o), 'small', 'th');


    }

    public function published($item, &$options) {
        return $this->r->td('<div class="">' . HTMLHelper::_('jgrid.published', $item['value'], $item['i'], $options['tasks'] . '.', $item['canchange']) . PhocacartHtmlFeatured::featured($item['valuefeatured'], $item['i'], $item['canchange']) . '</div>', "small");
    }

    public function categories($item, &$options) {

        $o  = array();
        $id = $item['id'];

        if (isset($item['value'][$id])) {
            foreach ($item['value'][$id] as $k => $v) {
                if ($item['caneditcategory'] && isset($item['linkeditcategory']) && $item['linkeditcategory'] != '') {
                    $linkCat = Route::_($item['linkeditcategory'] . '&id=' . (int)$v['id']);
                    $o[]     = '<a href="' . Route::_($linkCat) . '">' . PhocacartText::filterValue($v['title'], 'text') . '</a>';
                } else {
                    $o[] = PhocacartText::filterValue($v['title'], 'text');
                }
            }
        }

        return $this->r->td(implode(",\n", $o), 'small');
    }

    public function association($item, &$options) {

        if (!isset($options['association']) || (isset($options['association']) && !$options['association'])) {
            return '';
        } else if ($item['value']) {
            return $this->r->td(HTMLHelper::_('phocacartitem.association', $item['id']));
        } else {
            return $this->r->td('');
        }
    }

    public function language($item, &$options) {
        return $this->r->td(LayoutHelper::render('joomla.content.language', $item['value']), 'small');
    }

    public function phoca_action($item, &$options) {

        if ($item['cancreate'] || $item['canedit']) {
            if (isset($item['linkeditbox']) && $item['linkeditbox'] != '') {
                return $this->r->td( str_replace('<a ', '<a class="ph-no-underline"', $item['linkeditbox']), '');
            } else {
                return $this->r->td('<a class="pha-no-underline" href="' . Route::_($item['linkedit']) . '"><span id="phIdTitle' . $item['id'] . '" class="ph-icon-task ph-cp-item"><i class="duotone icon-apply"></i></span></a>', '');
            }

        }
    }

    public function phoca_info($item, &$options) {


        $pC = PhocacartUtils::getComponentParameters();
		$admin_columns_info_column = $pC->get('admin_columns_info_column', '');


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
                        $o[] = '<span class="badge bg-danger" title="'.Text::_('COM_PHOCACART_LABELS').'">'.$v->title.'</span>';
                    }
                    $o[] = '</div>';
                }
            }




            $o[] = '</div>';

        }



        return $this->r->td(implode("\n", $o), '');

        /*if ($item['cancreate'] || $item['canedit']) {
            if (isset($item['linkeditbox']) && $item['linkeditbox'] != '') {
                return $this->r->td( str_replace('<a ', '<a class="ph-no-underline"', $item['linkeditbox']), '');
            } else {
                return $this->r->td('<a class="pha-no-underline" href="' . Route::_($item['linkedit']) . '"><span id="phIdTitle' . $item['id'] . '" class="ph-icon-task ph-cp-item"><i class="duotone icon-apply"></i></span></a>', '');
            }

        }*/
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
*/
?>
