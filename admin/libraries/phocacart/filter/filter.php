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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

class PhocacartFilter
{
    //public $taglist			= false;
    public $tag = false;
    public $label = false;
    public $parameter = false;
    public $fields = false;
    public $manufacturer = false;
    public $price = false;
    public $attributes = false;
    public $specifications = false;
    public $category = false;

    public $enable_color_filter             = 0;
    public $enable_image_filter             = 0;
    public $image_style_image_filter        = 0;
    public $enable_color_filter_spec	    = 0;
    public $enable_image_filter_spec	    = 0;
    public $image_style_image_filter_spec 	= 0;

    public $ordering_tag = 1;
    public $ordering_label = 1;
    public $ordering_parameter = 1;
    public $ordering_manufacturer = 1;
    public $ordering_attribute = 1;
    public $ordering_specification = 1;
    public $ordering_category = 1;

    public $manufacturer_title = '';
    public $filter_language = 0;
    public $open_filter_panel = 1;

    public $force_category = 0;
    public $limit_attributes_category = 0;
    public $limit_tags_category = 0;
    public $limit_labels_category = 0;
    public $limit_parameters_category = 0;
    public $limit_price_category = 0;
    public $limit_manufacturers_category = 0;
    public $limit_specifications_category = 0;

    public $limit_category_count = -1;
    public $display_category_count = 0;
    public $limit_tag_count = -1;
    public $display_tag_count = 0;
    public $limit_parameter_count = -1;
    public $display_parameter_count = 0;
    public $limit_manufacturer_count = -1;
    public $display_manufacturer_count = 0;

    public $check_available_products    = 1;
    public $remove_parameters_cat		= 0;
    public $load_component_media		= 1;

    public $ignore_zero_price = 0;

    public $ajax                    = 0;


    public function __construct() {}

    public function getActiveFilterValues() {


        $app                    = Factory::getApplication();
        $paramsC                = PhocacartUtils::getComponentParameters();
        $manufacturer_alias     = $paramsC->get('manufacturer_alias', 'manufacturer');
        $manufacturer_alias     = $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric')) : 'manufacturer';
        $parameters             = PhocacartParameter::getAllParameters();


        $option         = $app->input->get('option', '', 'string');
        $view           = $app->input->get('view', '', 'string');

        $id = 0;
        if ($option == 'com_phocacart' && ($view == 'item')) {
            $id         = $app->input->get('catid', '', 'int'); // Category ID (Active Category)
        } else if ($option == 'com_phocacart' && ($view == 'items' && $view == 'category')) {
            $id         = $app->input->get('id', '', 'int'); // Category ID (Active Category)
        }
        $c              = $app->input->get('c', '', 'string'); // Category More (All Categories)
        $tags           = $app->input->get('tag', '', 'string');
        $labels         = $app->input->get('label', '', 'string');
        $manufacturers  = $app->input->get($manufacturer_alias, '', 'string');
        $price_from     = $app->input->get('price_from', '', 'float');
        $price_to       = $app->input->get('price_to', '', 'float');
        $a              = $app->input->get('a', '', 'array'); // Attributes
        $s              = $app->input->get('s', '', 'array'); // Specifications




        // CATEGORY id
        $cIdIN = '';
        //- $cIdIK = '';
        if ($id > 0) {
            $cIdIN = (int)$id;
            //- $cIdIK = (int)$cIdIN > 0 ? ':cid:' . $cIdIN : '';
        }

        // CATEGORY
        $cIN = '';
        //- $cIK = '';
        if ($c != '') {
            $cA = explode(',', $c);
            $cAN = array_unique(array_map('intval', $cA));
            $cIN = implode(',', $cAN);
            //- $cIK = (int)$cIN > 0 ? ':c:' . $cIN : '';
        }

        // TAGS
        $tIN = '';
        //- $tIK = '';
        if ($tags != '') {
            $tA = explode(',', $tags);
            $tAN = array_unique(array_map('intval', $tA));
            $tIN = implode(',', $tAN);
           //- $tIK = (int)$tIN > 0 ? ':t:' . $tIN : '';
        }

        // LABELS
        $lIN = '';
        //- $lIK = '';
        if ($labels != '') {
            $lA = explode(',', $labels);
            $lAN = array_unique(array_map('intval', $lA));
            $lIN = implode(',', $lAN);
            //- $lIK = (int)$lIN > 0 ? ':l:' . $lIN : '';
        }

        // MANUFACTURERS
        $mIN = '';
        //- $mIK = '';
        if ($manufacturers != '') {
            $mA = explode(',', $manufacturers);
            $mAN = array_unique(array_map('intval', $mA));
            $mIN = implode(',', $mAN);
            //- $mIK = (int)$mIN > 0 ? ':m:' . $mIN : '';
        }

        // PRICE
        $pfIN = $pfIK = '';
        if ($price_from !== '') {
            $pfIN = $price_from;
            //- $pfIK = $pfIN > 0 ? ':pf:' . $pfIN : '';
        }

        $ptIN = $ptIK = '';
        if ($price_to !== '') {
            $ptIN = $price_to;
            //- $ptIK = $ptIN > 0 ? ':pt:' . $ptIN : '';
        }


        // PARAMETERS
        $pA = array();
       //- $pIK = '';
        if (!empty($parameters)) {
            foreach ($parameters as $k => $v) {
                $alias = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
                $parameter = $app->input->get($alias, '', 'string');

                if ($parameter != '') {
                    $pIN = implode(',', array_unique(array_map('intval', explode(',', $parameter))));

                    if ((int)$pIN > 0) {
                        //- $pIK .= ':' . $alias . ':' . $pIN;
                        $pA[$alias] = $pIN;
                    }
                }
            }
        }


        // ATTRIBUTES
        $aA = array();
        //- $aIK = '';
        if (!empty($a)) {
            foreach ($a as $k => $v) {
                $alias = strip_tags($k);
                $parameter = strip_tags($v);
                $alias = trim(PhocacartText::filterValue($alias, 'alphanumeric'));

                if ($parameter != '') {
                    $aINA = explode(',', $parameter);
                    $aINA = array_map(function ($item) { return PhocacartText::filterValue($item, 'alphanumeric'); }, $aINA);
                    $aINA = array_unique($aINA);
                    $aIN = implode(',', $aINA);

                    if ($aIN != '') {
                        //- $aIK .= ':' . $alias . ':' . $aIN;
                        $aA[$alias] = "'" . implode("','", $aINA) . "'";
                    }
                }
            }
        }

        // SPECIFICATIONS
        $sA = array();
        //- $sIK = '';
        if (!empty($s)) {
            foreach ($s as $k => $v) {
                $alias = strip_tags($k);
                $parameter = strip_tags($v);
                $alias = trim(PhocacartText::filterValue($alias, 'alphanumeric'));

                if ($parameter != '') {
                    $sINA = explode(',', $parameter);
                    $sINA = array_map(function ($item) { return PhocacartText::filterValue($item, 'alphanumeric'); }, $sINA);
                    $sINA = array_unique($sINA);
                    $sIN = implode(',', $sINA);

                    if ($sIN != '') {
                        //- $sIK .= ':' . $alias . ':' . $sIN;
                        $sA[$alias] = "'" . implode("','", $sINA) . "'";
                    }
                }
            }
        }

        //- $key = 'k' . $cIdIK . $cIK . $tIK . $lIK . $mIK . $pfIK . $ptIK . $pIK . $aIK . $sIK;
        //- $key = base64_encode(serialize($key));

        // Get all items
        $f = array();
        if ($cIN != '') {
            $f['c'] = PhocacartCategory::getActiveCategories($cIN, $this->ordering_category);
        } else if ((int)$cIdIN > 0) {
            $f['c'] = PhocacartCategory::getActiveCategories((int)$cIdIN, $this->ordering_category);
        }

        $f['t'] = PhocacartTag::getActiveTags($tIN, $this->ordering_tag);
        $f['l'] = PhocacartTag::getActiveLabels($lIN, $this->ordering_label);
        $f['m'] = PhocacartManufacturer::getActiveManufacturers($mIN, $this->ordering_manufacturer, $manufacturer_alias );


        $f['price'] = array();
        $f['price']['from'] = '';
        $f['price']['to'] = '';
        if ($pfIN !== '') { $f['price']['from'] = $pfIN;}
        if ($ptIN !== '') { $f['price']['to'] = $ptIN;}

        $f['p'] = PhocacartParameter::getActiveParameterValues($pA, $this->ordering_parameter);
        $f['a'] = PhocacartAttribute::getActiveAttributeValues($aA, $this->ordering_attribute);
        $f['s'] = PhocacartSpecification::getActiveSpecificationValues($sA, $this->ordering_specification);

        return $f;

    }

    public function renderList(array $params = [])
    {
        $params = array_merge([
          'layout' => 'form_filter',
          'wrapper_class' => '',
          'wrapper_role' => 'tablist',
        ], $params);

        $document	= Factory::getDocument();
        $p = array();
        $pC = PhocacartUtils::getComponentParameters();
        $p['manufacturer_alias'] = $pC->get('manufacturer_alias', 'manufacturer');
        $p['manufacturer_alias'] = $p['manufacturer_alias'] != '' ? trim(PhocacartText::filterValue($p['manufacturer_alias'], 'alphanumeric')) : 'manufacturer';

        $p['display_products_all_subcategories'] = $pC->get('display_products_all_subcategories', 0);

        // $db = Factory::getDBO();
        $o = array();

        if ($this->ajax == 0) {
            $o[] = '<div id="phFilterBox"' . ($params['wrapper_role'] ? ' role="' . $params['wrapper_role'] . '"' : '') . ($params['wrapper_class'] ? ' class="phFilterBox ' . $params['wrapper_class'] . '"' : ' class="phFilterBox"') . '>';// AJAX ID
        }

        $s = PhocacartRenderStyle::getStyles();
        //$app		= Factory::getApplication();
        $layout = new FileLayout($params['layout'] . '_checkbox', null, array('component' => 'com_phocacart')); //foreach with items in layout
        $layout2 = new FileLayout($params['layout'] . '_text', null, array('component' => 'com_phocacart'));// foreach with items in this class
        $layout3 = new FileLayout($params['layout'] . '_checkbox_categories', null, array('component' => 'com_phocacart'));// foreach with items in this class
        $layout4 = new FileLayout($params['layout'] . '_color', null, array('component' => 'com_phocacart'));// foreach with items in layout
        $layout5 = new FileLayout($params['layout'] . '_image', null, array('component' => 'com_phocacart'));// foreach with items in layout


        $language = '';
        if ($this->filter_language == 1) {
            $lang = Factory::getLanguage();
            $language = $lang->getTag();
        }

        $pathProductImage = PhocacartPath::getPath('productimage');

        // =FILTER=
        $data = array();
        $data['s'] = $s;
        $data['getparams'] = array();


        // - SPECIFIC CASE - CATEGORY -
        // When we are in category view the identificator for category is ID
        // When we are in item view the identificator for category is CATID
        // When we are in items view (search, filter) the identificator for category is c=1-category
        // So when we are in category or item view and someone will filter products we can even set the category (form ID or CATID to c=1-category) for items view
        // Parameter for this is "force_category"
        // We even can limit filter items like attributes only for specific category
        // Parameter for this is "limit_attributes_category" (attributes)
        $category = PhocacartRoute::getIdForItemsRoute();// Used for parameter: Filter Category: Yes (Active Category) (int)$this->category == 1


        $forceCategory = array();
        $forceCategory['id'] = 0;
        $forceCategory['idalias'] = '';
        if ($this->force_category == 1) {
            $forceCategory = $category;
        }


        //-CATEGORY- ACTIVE CATEGORY (CATEGORY VIEW)
        $data = array();
        $data['s'] = $s;
        $data['param'] = 'id';
        $data['title'] = Text::_('COM_PHOCACART_CATEGORY');
        //$category			= PhocacartRoute::getIdForItemsRoute();


        $data['getparams'][] = $category['idalias'];
        $data['nrinalias'] = 1;
        $data['formtype'] = 'category';
        $data['uniquevalue'] = 1;
        $data['params']['open_filter_panel'] = $this->open_filter_panel;


        if ((int)$this->category == 1 && (int)$category['id'] > 0) {
            /* phocacart import('phocacart.category.category');*/
            $data['items'][] = PhocacartCategory::getCategoryById($category['id']);

            if (!empty($data['items'])) {
                $o[] = $layout->render($data);
            }

        }


        // -CATEGORY MORE- (ALL CATEGORIES, ITEMS VIEW)
        $data = array();
        $data['s'] = $s;
        $data['param'] = 'c';
        $data['title'] = Text::_('COM_PHOCACART_CATEGORY');
        $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');
        $data['nrinalias'] = 1;
        $data['formtype'] = 'checked';
        $data['uniquevalue'] = 0;
        $data['params']['display_category_count'] = $this->display_category_count;

        $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view


        // OPEN OR CLOSE PANEL
        // $data['params']['open_filter_panel'] = $this->open_filter_panel;
        if ((int)$this->category == 2) {
            $data['active'] = 0;
            // if we are in category view and want to force active category when clicking on filter
            // so we want to move the category id from CATEGORY VIEW to ITEMS VIEW
            // and we should mark the category active in category list = $forceActive
            $data['items'] = PhocacartCategory::getCategoryTreeArray([
              'ordering' => $this->ordering_category,
              'language' => $language,
              'limitCount' => $this->limit_category_count,
            ]);
            $data['output'] = PhocacartCategory::nestedToCheckBox($data['items'], $data, 0, $data['active'], $forceCategory['id']);

            if ($this->open_filter_panel == 0) {
                $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
                $data['triangle_class'] = $s['i']['triangle-right'];
            } else if ($this->open_filter_panel == 2 && $data['active'] == 1) {
                $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];// closed as default and wait if there is some active item to open it
                $data['triangle_class'] = $s['i']['triangle-bottom'];
            } else if ($this->open_filter_panel == 2 && $data['active'] == 0) {
                $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
                $data['triangle_class'] = $s['i']['triangle-right'];
            } else {
                $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];
                $data['triangle_class'] = $s['i']['triangle-bottom'];
            }

            if (!empty($data['items'])) {
                $o[] = $layout3->render($data);
            }
        }


        // LIMIT ... TO CATEGORY - Display items only from products of the current category
        $activeProducts = array();
        $activeCategory = array();
        $activeProductsAttributes = array();
        $activeProductsTags = array();
        $activeProductsLabels = array();
        $activeProductsParameters = array();
        $activeProductsPrice = array();
        $activeProductsManufacturers = array();
        $activeProductsSpecifications = array();
        if ($this->limit_attributes_category == 1 || $this->limit_tags_category == 1 || $this->limit_labels_category == 1 || $this->limit_parameters_category == 1
            || $this->limit_price_category == 1 || $this->limit_manufacturers_category == 1 || $this->limit_specifications_category == 1) {


            if ((int)$category['id'] > 0) {
                $activeCategory[] = (int)$category['id'];

                // Display not only products from category but even from its subcategories in parent category
                if ($p['display_products_all_subcategories'] == 1) {
                    $categoryChildrenId = PhocacartCategoryMultiple::getCategoryChildrenString((int)$category['id'], (string)$category['id']);
                    if ($categoryChildrenId !== '') {
                        $activeCategoryChildren = explode(',', $categoryChildrenId);
                        foreach ($activeCategoryChildren as $k => $v) {
                            $activeCategory[] = (int)$v;
                        }
                    }
                }
            }


            if (!empty($data['getparams'])) {
                foreach ($data['getparams'] as $k => $v) {

                    if ((int)$v > 0) {
                        $activeCategory[] = (int)$v;
                    }
                }
            }

            $activeCategory = array_unique($activeCategory);

            $activeProducts = PhocacartProduct::getProducts(0, 0, 1, 0, true, false, false, 0, $activeCategory, 0, array(0, 1), 'a.id', 'column');

            // When there are no products in category (no products or not published products)
            // then we cannot send empty $activeProducts to the filter functions, example:
            // PhocacartSpecification::getAllSpecificationsAndValues($this->ordering_specification, $this->check_available_products, $language, $activeProductsSpecifications);
            // as then this is completely ignored and all items are listed
            if(empty($activeProducts)) {
               $activeProducts = array(0);
            }

            if ($this->limit_attributes_category == 1) {
                $activeProductsAttributes = $activeProducts;
            }
            if ($this->limit_tags_category == 1) {
                $activeProductsTags = $activeProducts;
            }
            if ($this->limit_labels_category == 1) {
                $activeProductsLabels = $activeProducts;
            }
            if ($this->limit_parameters_category == 1) {
                $activeProductsParameters = $activeProducts;
            }
            if ($this->limit_price_category == 1) {
                $activeProductsPrice = $activeProducts;
            }
            if ($this->limit_manufacturers_category == 1) {
                $activeProductsManufacturers = $activeProducts;
            }
            if ($this->limit_specifications_category == 1) {
                $activeProductsSpecifications = $activeProducts;

            }

        }

        // -PRICE- AVAILABLE PRODUCTS ONLY - Yes as default
        $data = array();
        $data['s'] = $s;
        $data['param'] = 'price_from';
        $data['param2'] = 'price_to';
        $data['id'] = 'phPriceFromTo';
        $data['title'] = Text::_('COM_PHOCACART_PRICE');
        $data['title1'] = Text::_('COM_PHOCACART_PRICE_FROM');
        $data['title2'] = Text::_('COM_PHOCACART_PRICE_TO');
        $data['titleset'] = Text::_('COM_PHOCACART_SET_PRICE');
        $data['titleclear'] = Text::_('COM_PHOCACART_CLEAR_PRICE');
        $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');// string because of setting '' when no value set
        $data['getparams2'] = $this->getArrayParamValues($data['param2'], 'string');
        $data['formtype'] = 'text';
        $data['uniquevalue'] = 1;
        $data['filterprice'] = $this->price;

        $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

        // OPEN OR CLOSE PANEL
        // $data['params']['open_filter_panel'] = $this->open_filter_panel;
        if ($this->open_filter_panel == 0) {
            $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
            $data['triangle_class'] = $s['i']['triangle-right'];
        } else if ($this->open_filter_panel == 2 && ($data['getparams'][0] != '' || $data['getparams2'][0] != '')) {
            $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];// closed as default and wait if there is some active item to open it
            $data['triangle_class'] = $s['i']['triangle-bottom'];
        } else if ($this->open_filter_panel == 2 && ($data['getparams'][0] == '' || $data['getparams2'][0] == '')) {
            $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
            $data['triangle_class'] = $s['i']['triangle-right'];
        } else {
            $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];
            $data['triangle_class'] = $s['i']['triangle-bottom'];
        }

        if ($this->price > 0) {
            $o[] = $layout2->render($data);
        }


        // RENDER PRICE FROM TO INPUT RANGE
        if ($this->price == 2 || $this->price == 3) {


            $media = PhocacartRenderMedia::getInstance('main');
            $media->loadUiSlider();

            $price_from = $this->getArrayParamValues('price_from', 'string');
            $price_to = $this->getArrayParamValues('price_to', 'string');
            $min = PhocacartProduct::getProductPrice(2, $this->check_available_products, $language, $activeProductsPrice, $this->ignore_zero_price);// min price
            $max = PhocacartProduct::getProductPrice(1, $this->check_available_products, $language, $activeProductsPrice, $this->ignore_zero_price);// max price

            if (!$min) {
                $min = 0;
            }
            if (!$max) {
                $max = 0;
            }

            if ($price_to[0] == '') {
                $price_to[0] = $max;
            }
            if ($price_from[0] == '') {
                $price_from[0] = $min;
            }

           // PhocacartRenderJs::renderFilterRange($min, $max, $price_from[0], $price_to[0]);
            $oParams = array();
            $oParams['filterPriceMin'] = (float)$min;
            $oParams['filterPriceMax'] = (float)$max;
            $oParams['filterPriceFrom'] = (float)$price_from[0];
            $oParams['filterPriceTo'] = (float)$price_to[0];
            $oLang  = array();
            $oLang['COM_PHOCACART_PRICE'] = Text::_('COM_PHOCACART_PRICE');

            $document->addScriptOptions('phParamsPC', $oParams);
            $document->addScriptOptions('phLangPC', $oLang);
        }


        // -MANUFACTURER- AVAILABLE PRODUCTS ONLY - Yes
        $data = array();
        $data['s'] = $s;
        $data['param'] = $p['manufacturer_alias'];
        $data['title'] = $this->manufacturer_title != '' ? Text::_($this->manufacturer_title) : Text::_('COM_PHOCACART_MANUFACTURERS');
        $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');
        $data['nrinalias'] = 1;
        $data['formtype'] = 'checked';
        $data['uniquevalue'] = 0;
        $data['params']['open_filter_panel'] = $this->open_filter_panel;
        $data['params']['display_count'] = $this->display_manufacturer_count;

        $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

        if ($this->manufacturer) {
            /*phocacart import('phocacart.manufacturer.manufacturer');*/
            $data['items'] = PhocacartManufacturer::getAllManufacturers($this->ordering_manufacturer, $this->check_available_products, $language, $activeProductsManufacturers, $this->limit_manufacturer_count);
        }

        if (!empty($data['items'])) {
            $o[] = $layout->render($data);
        }


        // -TAG-
        $data = array();
        $data['s'] = $s;
        $data['param'] = 'tag';
        $data['title'] = Text::_('COM_PHOCACART_TAGS');
        $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');
        $data['nrinalias'] = 1;
        $data['formtype'] = 'checked';
        $data['uniquevalue'] = 0;
        $data['params']['open_filter_panel'] = $this->open_filter_panel;
        $data['params']['display_count'] = $this->display_tag_count;

        $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

        if ($this->tag) {
            /*phocacart import('phocacart.tag.tag');*/
            $data['items'] = PhocacartTag::getAllTags($this->ordering_tag, $this->check_available_products, 0, $language, $activeProductsTags, $this->limit_tag_count);

        }

        if (!empty($data['items'])) {

            $o[] = $layout->render($data);
        }

        // -LABEL-
        $data = array();
        $data['s'] = $s;
        $data['param'] = 'label';
        $data['title'] = Text::_('COM_PHOCACART_LABELS');
        $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');
        $data['nrinalias'] = 1;
        $data['formtype'] = 'checked';
        $data['uniquevalue'] = 0;
        $data['params']['open_filter_panel'] = $this->open_filter_panel;
        $data['params']['display_count'] = $this->display_tag_count;

        $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

        if ($this->label) {
            /*phocacart import('phocacart.tag.tag');*/
            $data['items'] = PhocacartTag::getAllTags($this->ordering_label, $this->check_available_products, 1, $language, $activeProductsLabels, $this->limit_tag_count);

        }

        if (!empty($data['items'])) {
            $o[] = $layout->render($data);
        }


        // -ATTRIBUTES- AVAILABLE PRODUCTS ONLY - Yes
        if ($this->attributes) {
            /*phocacart import('phocacart.attribute.attribute');*/
            $attributes = PhocacartAttribute::getAllAttributesAndOptions($this->ordering_attribute, $this->check_available_products, $language, $activeProductsAttributes, true);

            if (!empty($attributes)) {
                foreach ($attributes as $k => $v) {

                    $data = array();
                    $data['s'] = $s;
                    $data['param'] = 'a[' . $v['alias'] . ']';
                    $data['title'] = $v['title'];
                    $data['items'] = $v['options'];
                    $data['getparams'] = $this->getArrayParamValues($data['param'], 'array');
                    $data['uniquevalue'] = 0;
                    $data['pathitem'] = $pathProductImage;
                    $data['params']['open_filter_panel'] = $this->open_filter_panel;

                    $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

                    if (!empty($data['items'])) {

                        if ($this->enable_color_filter && isset($v['type']) && ($v['type'] == 2 || $v['type'] == 5)) {
                            // Color
                            $data['formtype'] = 'color';
                            $o[] = $layout4->render($data);
                        } else if ($this->enable_image_filter && isset($v['type']) && ($v['type'] == 3 || $v['type'] == 6)) {
                            // Image
                            $data['formtype'] = 'image';
                            $data['style'] = strip_tags($this->image_style_image_filter);
                            $o[] = $layout5->render($data);
                        } else {
                            // Select
                            $data['formtype'] = 'checked';
                            $o[] = $layout->render($data);
                        }
                    }
                }
            }
        }

        // -SPECIFICATIONS- AVAILABLE PRODUCTS ONLY - Yes
        if ($this->specifications) {
            /*phocacart import('phocacart.specification.specification');*/
            $specifications = PhocacartSpecification::getAllSpecificationsAndValues($this->ordering_specification, $this->check_available_products, $language, $activeProductsSpecifications);

            if (!empty($specifications)) {
                foreach ($specifications as $k => $v) {
                    $data = array();
                    $data['s'] = $s;
                    $data['param'] = 's[' . $v['alias'] . ']';
                    $data['title'] = $v['title'];
                    $data['items'] = $v['value'];
                    $data['getparams'] = $this->getArrayParamValues($data['param'], 'array');

                    $data['formtype'] = 'checked';
                    $data['uniquevalue'] = 0;
                    $data['pathitem'] = $pathProductImage;
                    $data['params']['open_filter_panel'] = $this->open_filter_panel;

                    $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view


                    if (!empty($data['items'])) {

                        if ($this->enable_color_filter_spec) {
                            // Color
                            $data['formtype'] = 'color';
                            $o[] = $layout4->render($data);
                        } else if ($this->enable_image_filter_spec) {
                            // Image
                            $data['formtype'] = 'image';
                            $data['style'] = strip_tags($this->image_style_image_filter_spec);
                            $o[] = $layout5->render($data);
                        } else {
                            // Select
                            $data['formtype'] = 'checked';

                            $o[] = $layout->render($data);
                        }
                    }
                }
            }
        }


        // -PARAMETERS-
        $parameters = PhocacartParameter::getAllParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $k => $v) {
                $data = array();
                $data['s'] = $s;
                $data['param'] = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
                $data['title'] = $v->title;
                $data['titleheader'] = $v->title_header;
                $data['getparams'] = $this->getArrayParamValues($data['param'], 'string');
                $data['nrinalias'] = 1;
                $data['formtype'] = 'checked';
                $data['uniquevalue'] = 0;
                $data['params']['open_filter_panel'] = $this->open_filter_panel;
                $data['params']['display_count'] = $this->display_parameter_count;

                $data['forcecategory']['idalias'] = $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

                if ($this->parameter) {

                    $limitCount = $this->limit_parameter_count;
                    if (isset($v->limit_count) && (int)$v->limit_count > -1) {
                        $limitCount = (int)$v->limit_count;
                    }

                    $data['items'] = PhocacartParameter::getAllParameterValues((int)$v->id, $this->ordering_parameter, $this->check_available_products, $language, $activeProductsParameters, $limitCount);

                }

                if (!empty($data['items'])) {

                    $o[] = $layout->render($data);
                }

            }
        }

        // -CUSTOM FIELDS-
        // TODO resolve getAllFieldsValues speed
        /*
        if ($this->fields) {
            $fields = PhocacartFields::getAllFields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if (!$field->params->get('is_filter')) {
                        continue;
                    }

                    $data = [
                      's' => $s,
                      'param' => $field->name,
                      'title' => Text::_($field->title),
                      'titleheader' => Text::_($field->title),
                      'getparams' => $this->getArrayParamValues($field->name, 'string'),
                      'nrinalias' => 0,
                      'formtype' => 'checked',
                      'uniquevalue' => 0,
                      'params' => [
                        'open_filter_panel' => $this->open_filter_panel,
                        'display_count' => $this->display_parameter_count,
                      ],
                      'forcecategory' => $forceCategory,
                    ];

                    $items = PhocacartFields::getAllFieldsValues((int)$field->id, $this->check_available_products, $language, $activeProductsParameters);

                    if ($items) {
                        $data['items'] = $items;
                        $o[] = $layout->render($data);
                    }

                }
            }
        }
        */

        if ($this->ajax == 0) {
            $o[] = '</div>';// End phFilterBox
        }

        $o2 = implode("\n", $o);

        return $o2;

    }

    public function getArrayParamValues($param, $type = '')
    {

        // Make array from GET parameter values which are stored in string separated by comma
        $app = Factory::getApplication();

        if ($type == 'int') {
            $paramString = $app->input->get($param, 0, $type);
        } else if ($type == 'array') {

            $paramE = explode('[', $param);
            if (isset($paramE[0]) && isset($paramE[1])) {
                $paramE[1] = str_replace(']', '', $paramE[1]);
                $paramStringE = $app->input->get($param[0], array(), $type);
                if (isset($paramStringE[$paramE[1]])) {
                    $paramString = $paramStringE[$paramE[1]];
                } else {
                    $paramString = '';
                }
            }

        } else {
            $paramString = $app->input->get($param, '', $type);
        }

        $a = explode(',', $paramString);

        $inA = array();
        if (!empty($a)) {
            if ($type == 'int') {
                foreach ($a as $k => $v) {
                    $inA[] = (int)$v;
                }
            } else {
                foreach ($a as $k => $v) {
                    $inA[] = $v;
                }
            }
        }
        return $inA;
    }

}

?>
