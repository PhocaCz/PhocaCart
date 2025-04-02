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

// BACKWARD COMPATIBILITY
require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;


class PhocacartRenderMedia
{
    public $jquery = 0;
    protected $document = false;
    protected $p = array();
    protected $t = array();
    protected $format = '';
    protected $view = '';
    protected $load = true;
    protected $scriptAtribute = array();
    protected $scriptAtributeInline = array();
    protected $wa;

    protected $name = '';
    protected static $instances = array();


    public function __construct($name = 'main') {
        $this->name = $name;


        $app = Factory::getApplication();
        $this->wa = $app->getDocument()->getWebAssetManager();
        //$params = $app->getParams();// We call media from modules too
        $params                                    = PhocacartUtils::getComponentParameters();
        $this->p['load_bootstrap']                 = $params->get('load_bootstrap', 1);
        $this->p['load_chosen']                    = $params->get('load_chosen', 0);
        $this->p['load_main_css']                  = $params->get('load_main_css', 1);
        $this->p['load_spec_css']                  = $params->get('load_spec_css', '');
        $this->p['load_rtl_css']                   = $params->get('load_rtl_css', 0);
        $this->p['equal_height']                   = $params->get('equal_height', 1);
        $this->p['display_compare']                = $params->get('display_compare', 0);
        $this->p['display_wishlist']               = $params->get('display_wishlist', 0);
        $this->p['display_quickview']              = $params->get('display_quickview', 0);
        $this->p['display_addtocart_icon']         = $params->get('display_addtocart_icon', 0);
        $this->p['fade_in_action_icons']           = $params->get('fade_in_action_icons', 0);
        $this->p['dynamic_change_image']           = $params->get('dynamic_change_image', 0);
        $this->p['dynamic_change_price']           = $params->get('dynamic_change_price', 0);
        $this->p['dynamic_change_stock']           = $params->get('dynamic_change_stock', 0);
        $this->p['dynamic_change_id']              = $params->get('dynamic_change_id', 0);
        $this->p['dynamic_change_url_attributes']  = $params->get('dynamic_change_url_attributes', 0);
        $this->p['quantity_input_spinner']         = $params->get('quantity_input_spinner', 0);
        $this->p['ajax_pagination_category']       = $params->get('ajax_pagination_category', 0);
        $this->p['ajax_searching_filtering_items'] = $params->get('ajax_searching_filtering_items', 0);

        $this->p['theme']                           = $params->get('theme', 'bs5');
        $this->p['icon_type']                       = $params->get( 'icon_type', 'svg');

        $this->p['pos_focus_input_fields'] = $params->get('pos_focus_input_fields', 0);
        $this->p['pos_filter_category']    = $params->get('pos_filter_category', 1);// reload equal height
        $this->p['lazy_load_category_items'] = $params->get('lazy_load_category_items', 2);
        $this->p['lazy_load_categories']     = $params->get('lazy_load_categories', 2);
        $this->p['pos_server_print']         = $params->get('pos_server_print', 0);

        //$this->p['load_min_js'] = $params->get('load_min_js', 1);
        $this->t['min']         = '';// Removed, managed by Joomla debug, $this->p['load_min_js'] == 0 ? '' : '.min';


        $this->format   = $app->input->get('format', '', 'string');
        $this->view     = $app->input->get('view', '', 'string');
        $this->document = Factory::getDocument();

        if ($this->format == 'raw' || $this->format == 'json') {
            $this->load = false;
        }

        $this->scriptAtribute       = array('defer' => true, 'async' => true);
        $this->scriptAtributeInline = array();

        $this->document = Factory::getDocument();
        $uri            = Uri::getInstance();
        $action         = $uri->toString();
        // =================
        // Render Page
        // =================


        $oVars   = array();
        $oLang   = array();
        $oParams = array();
        $oLang   = array('COM_PHOCACART_CLOSE' => Text::_('COM_PHOCACART_CLOSE'), 'COM_PHOCACART_ERROR_TITLE_NOT_SET' => Text::_('COM_PHOCACART_ERROR_TITLE_NOT_SET'));

        $oVars['renderPageUrl'] = $action;
        $oVars['token']         = Session::getFormToken();
        $oVars['basePath']      = Uri::base(true);
        if ($this->view == 'category' || $this->view == 'items' || $this->view == 'pos') {

            if ($this->view == 'pos') {
                $oVars['renderPageOutput'] = 'phPosContentBox';
            } else {
                $oVars['renderPageOutput'] = 'phItemsBox';
            }
        }
        $oVars['isPOS']                         = (int)PhocacartUtils::isView('pos');
        $oParams['loadChosen']                  = (int)$this->p['load_chosen'];
        $oParams['ajaxPaginationCategory']      = (int)$this->p['ajax_pagination_category'];
        $oParams['ajaxSearchingFilteringItems'] = (int)$this->p['ajax_searching_filtering_items'];
        $oParams['theme']                       = $this->p['theme'];
        $oParams['iconType']			        = $this->p['icon_type'];
        if($oVars['isPOS']) {
            $oParams['theme'] = 'svg';// media\com_phocacart\js\phoca\jquery.phocaattribute.js line 319
        }
        $oVars['view']                          = $this->view;

        // Change Attribute data
        $oVars['urlCheckoutChangeData'] = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changedatabox&format=json&' . Session::getFormToken() . '=1';

        /*
         * typeview
         * if($oVars['isPOS']) {
            // We need to identify POS in AJAX URL Request (when using common controller like checkout, checkout controller is used in POS too)
            // For example: displaying group price depends on logged in user
            // Logged in user in online shop is logged in user
            // Logged in user in POS is vendor, not logged in user so in POS displaying the group price is different
            // Displaying interactive price does not have any effect on real price in checkout
            $oVars['urlCheckoutChangeData'] = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changedatabox&format=json&' . Session::getFormToken() . '=1';
        }*/


        $oParams['dynamicChangePrice']           = (int)$this->p['dynamic_change_price'];
        $oParams['dynamicChangeStock']           = (int)$this->p['dynamic_change_stock'];
        $oParams['dynamicChangeId']              = (int)$this->p['dynamic_change_id'];
        $oParams['dynamicChangeImage']           = (int)$this->p['dynamic_change_image'];
        $oParams['dynamicChangeUrlAttributes']   = (int)$this->p['dynamic_change_url_attributes'];

        HTMLHelper::_('jquery.framework', false);


        if ($this->p['theme'] != 'uikit') {

            $this->wa->useScript('bootstrap.modal');
        }

        //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/phocacart'.$this->t['min'].'.js');
        //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/phocarequest'.$this->t['min'].'.js');
        //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/filter/jquery.ba-bbq.min.js');
        //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/phocacart' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/phocarequest' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
       // HTMLHelper::_('script', 'media/com_phocacart/js/filter/jquery.ba-bbq.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

        $this->wa->registerAndUseScript('com_phocacart.phocacart', 'media/com_phocacart/js/phoca/phocacart' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        $this->wa->registerAndUseScript('com_phocacart.phocacartrequest', 'media/com_phocacart/js/phoca/phocarequest' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        //$this->wa->registerAndUseScript('com_phocacart.ba-bbq', 'media/com_phocacart/js/filter/jquery.ba-bbq.js', array('version' => 'auto'), $this->scriptAtributeInline);// Not more used

        // phocacartfilter.js can work without loaded jquery-ui.slder.min.js - it tests if the function exist - no error
        // if set in phoca cart filter moduler - the slider range can be displayed in filter module
        // in such case the following libraries are loaded:
        // $this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/ui/jquery-ui.slider.min.js');
        // JHtml::stylesheet('media/com_phocacart/js/ui/jquery-ui.slider.min.css');

        // if there will be problem when loading the libraries, possible solution is to get module parameters here
        // and load before phocafilter.js library
        /*
        $module			= ModuleHelper::getModule('mod_phocacart_filter');
        $paramsM		= new Registry($module->params);
        $filter_price	= $paramsM->get( 'filter_price', 1 );
        if ($filter_price == 2 || $filter_price == 3) {
            $this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/ui/jquery-ui.slider.min.js');
             HTMLHelper::stylesheet('media/com_phocacart/js/ui/jquery-ui.slider.min.css');
        }
        */

        // Are filter and search modules enabled - needed because of phocacart.js
        $modPhocaCartFilter = ModuleHelper::getModule('mod_phocacart_filter');
        // Get info if modules are enabled and installed
        $modPhocaCartSearch            = ModuleHelper::getModule('mod_phocacart_search');
        $oVars['mod_phocacart_filter'] = 1;
        if (!$modPhocaCartFilter || (isset($modPhocaCartFilter->id) && (int)$modPhocaCartFilter->id < 1)) {
            $oVars['mod_phocacart_filter'] = 0;
        }

        $oVars['mod_phocacart_search'] = 1;
        if (!$modPhocaCartSearch || (isset($modPhocaCartSearch->id) && (int)$modPhocaCartSearch->id < 1)) {
            $oVars['mod_phocacart_search'] = 0;
        }


        $currency = PhocacartCurrency::getCurrency();
        PhocacartRenderJs::getPriceFormatJavascript($currency->price_decimals, $currency->price_dec_symbol, $currency->price_thousands_sep, $currency->price_currency_symbol, $currency->price_prefix, $currency->price_suffix, $currency->price_format);
        // $this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/phocafilter'.$this->t['min'].'.js');


        //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/phocafilter' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        $this->wa->registerAndUseScript('com_phocacart.phocafilter', 'media/com_phocacart/js/phoca/phocafilter' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

        if ($this->view == 'pos') {


            $oLang['COM_PHOCACART_WARNING_BOOTSTRAP_JS_LOADED_MORE_THAN_ONCE'] = Text::_('COM_PHOCACART_WARNING_BOOTSTRAP_JS_LOADED_MORE_THAN_ONCE');

            $oLang['COM_PHOCACART_OK']     = Text::_('COM_PHOCACART_OK');
            $oLang['COM_PHOCACART_CANCEL'] = Text::_('COM_PHOCACART_CANCEL');


            $oParams['posFocusInputFields'] = (int)$this->p['pos_focus_input_fields'];
            $oParams['posFilterCategory']   = (int)$this->p['pos_filter_category'];
            $oParams['posServerPrint']      = (int)$this->p['pos_server_print'];
            $oVars['urlOrder']              = Route::_('index.php?option=com_phocacart&view=order&tmpl=component&format=raw');
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/base64/base64'.$this->t['min'].'.js');
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/phocapos'.$this->t['min'].'.js');

            //HTMLHelper::_('script', 'media/com_phocacart/js/base64/base64' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtribute);
           // HTMLHelper::_('script', 'media/com_phocacart/js/phoca/phocapos' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->registerAndUseScript('com_phocacart.base64', 'media/com_phocacart/js/base64/base64' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
            $this->wa->registerAndUseScript('com_phocacart.phocapos', 'media/com_phocacart/js/phoca/phocapos' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

        }

        $this->document->addScriptOptions('phLangPC', $oLang);
        $this->document->addScriptOptions('phVarsPC', $oVars);
        $this->document->addScriptOptions('phParamsPC', $oParams);


        // Loaded this way because of possible override by template
        if ($this->format == 'raw' || $this->format == 'json') {

        } else if ($oParams['theme'] == 'svg') {
            $this->document->addHeadLink(HTMLHelper::image('com_phocacart/svg-definitions.svg', '', [], true, 1), 'preload', 'rel', ['as' => 'image', 'type' => 'image/svg+xml']);
        }

        //HTMLHelper::_('image', 'com_phocacart/svg-definitions.css', array('version' => 'auto', 'relative' => true), array('rel' => 'preload','as' => 'image', 'type' => 'image/svg+xml'));



        //$this->wa->registerAndUseStyle('com_phocacart.svg', 'svg-definitions.css', array('version' => 'auto', 'relative' => true), array('rel' => 'preload','as' => 'image', 'type' => 'image/svg+xml'));

       // $wa = $app->getDocument()->getWebAssetManager();
        //$a = $wa->getAsset('style', 'com_phocacart.svg');


        // Get path for the svg-definitions.svg - this can be path to media folder:
        // com_phocacart: media/com_phocacart/images/svg-definitions.svg
        // or override in template: media/templates/site/cassiopeia/images/com_phocacart/svg-definitions.svg
        //$path = HTMLHelper::image('com_phocacart/svg-definitions.svg', '', [], true, 1);





        // Bootstrap 3 Modal transition
        /*if ($this->p['theme'] == 'bs3') {
            //JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bs_modal_transition.css', array('version' => 'auto'));
            $this->wa->registerAndUseStyle('com_phocacart.bs_modal_transition', 'media/com_phocacart/bootstrap/css/bs_modal_transition.css' . $this->t['min'] . '.js', array('version' => 'auto'));
        }*/

    }

    /**
     * Returns the global PhocacartRenderMedia object, only creating it if it doesn't already exist.
     * @param string $name The name of the media
     * @return  PhocacartRenderMedia  The PhocacartRenderMedia object
     * @since   3.5
     */
    public static function getInstance($name = 'main') {
        if (empty(self::$instances[$name])) {
            self::$instances[$name] = new PhocacartRenderMedia($name);
        }

        return self::$instances[$name];
    }

    public function loadBase() {

        if ($this->load) {
            HTMLHelper::_('jquery.framework', false);

            if ($this->p['load_main_css'] == 1) {
                //JHtml::stylesheet('media/com_phocacart/css/main.css');
                //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/main.css', array('version' => 'auto'));

                $this->wa->registerAndUseStyle('com_phocacart.main', 'com_phocacart/main.css');// Because of ordering of CSS items (user.css from template should load as last)
                //$this->wa->registerAndUseStyle('com_phocacart.main', 'media/com_phocacart/css/main.css');
            }

            //$this->loadBootstrap();

            if (PhocacartUtils::isView('pos')) {
                //JHtml::stylesheet('media/com_phocacart/css/pos.css');
                //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/pos.css', array('version' => 'auto'));
                $this->wa->registerAndUseStyle('com_phocacart.pos', 'media/com_phocacart/css/pos.css', array('version' => 'auto'));

            }
        }
    }

    public function loadSpec() {
        // should be loaded as last because they overwrite base
        if ($this->p['load_spec_css'] != '') {
            //JHtml::stylesheet('media/com_phocacart/css/spec/'.htmlspecialchars(strip_tags($this->p['load_spec_css'])).'.css');
           // HTMLHelper::_('stylesheet', 'media/com_phocacart/css/spec/' . htmlspecialchars(strip_tags($this->p['load_spec_css'])) . '.css', array('version' => 'auto'));
            $this->wa->registerAndUseStyle('com_phocacart.spec_css', 'media/com_phocacart/css/spec/' . htmlspecialchars(strip_tags($this->p['load_spec_css'])) . '.css', array('version' => 'auto'));
        }

        if ($this->p['load_rtl_css'] == 1) {
            //JHtml::stylesheet('media/com_phocacart/css/spec/rtl.css');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/spec/rtl.css', array('version' => 'auto'));
            $this->wa->registerAndUseStyle('com_phocacart.rtl', 'media/com_phocacart/css/spec/rtl.css', array('version' => 'auto'));
        }
    }


    public function returnLazyLoad() {

        $s   = array();
        $s[] = '<script>';
        $s[] = ' window.lazyLoadOptions = {	';
        $s[] = '   elements_selector: ".ph-lazyload",';
        $s[] = '   load_delay: 0,';
        $s[] = ' };';

        $s[] = ' window.addEventListener(\'LazyLoad::Initialized\', function (event) {';
        $s[] = '   window.phLazyLoadInstance = event.detail.instance;';
        $s[] = ' }, false);';
        $s[] = '</script>';
        $s[] = '<script async src="' . Uri::root(true) . '/media/com_phocacart/js/lazyload/lazyload.min.js"></script>';

        if ($this->p['lazy_load_category_items'] == 1 && ($this->view == 'category' || $this->view == 'items') && $this->load) {
            return implode("\n", $s);
        } else if ($this->p['lazy_load_categories'] == 1 && $this->view == 'categories') {
            return implode("\n", $s);
        }
        return '';
    }

    public function loadProductHover() {
        if ($this->p['fade_in_action_icons'] == 1 && $this->load) {
            if ($this->p['display_compare'] == 0 && $this->p['display_wishlist'] == 0 && $this->p['display_quickview'] == 0 && $this->p['display_addtocart_icon'] == 0) {
                return false;
            }
            //JHtml::stylesheet('media/com_phocacart/css/main-product-hover.css');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/main-product-hover.css', array('version' => 'auto'));
            $this->wa->registerAndUseStyle('com_phocacart.main-product-hover', 'media/com_phocacart/css/main-product-hover.css', array('version' => 'auto'));
        }
    }

    public function loadPhocaMoveImage($load = 0) {
        if ($load == 1 && $this->load) {
            //JHtml::stylesheet('media/com_phocacart/css/main-product-image-move.css' );
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/main-product-image-move.css', array('version' => 'auto'));
            $this->wa->registerAndUseStyle('com_phocacart.main-product-image-move', 'media/com_phocacart/css/main-product-image-move.css', array('version' => 'auto'));
        }
    }

    public function loadBootstrap() {

        return false;
        /*if ($this->p['load_bootstrap'] == 1 && $this->load) {
            //JHtml::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
            HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap.min.css', array('version' => 'auto'));
            HTMLHelper::_('script', 'media/com_phocacart/bootstrap/js/bootstrap.min.js', array('version' => 'auto'), $this->scriptAtribute);
        } else if ($this->p['load_bootstrap'] == 2 && $this->load) {
            //JHtml::stylesheet('media/com_phocacart/bootstrap/css/bootstrap4.min.css' );
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/bootstrap/js/bootstrap4.min.js');
            HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap4.min.css', array('version' => 'auto'));
            HTMLHelper::_('script', 'media/com_phocacart/bootstrap/js/bootstrap4.min.js', array('version' => 'auto'), $this->scriptAtribute);
        }*/
    }

    public function loadWindowPopup() {
        if ($this->load) {
            //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/phoca/jquery.phocawindowpopup'.$this->t['min'].'.js');
            //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/jquery.phocawindowpopup' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtribute);
            $this->wa->registerAndUseScript('com_phocacart.phocawindowpopup', 'media/com_phocacart/js/phoca/jquery.phocawindowpopup' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }

    public function loadChosen() {
        if ($this->load) {
            if ($this->p['load_chosen'] == 2) {
                //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/bootstrap/js/bootstrap.min.js');
                //HTMLHelper::_('script', 'media/com_phocacart/bootstrap/js/bootstrap.min.js', array('version' => 'auto'));
                $this->wa->registerAndUseScript('com_phocacart.bootstrap', 'media/com_phocacart/bootstrap/js/bootstrap.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
            }
            if ($this->p['load_chosen'] == 1 || $this->p['load_chosen'] == 2) {
                //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/chosen/chosen.jquery.min.js');
                //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/chosen/chosen.required.js');
                //HTMLHelper::_('script', 'media/com_phocacart/js/chosen/chosen.jquery.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
                //HTMLHelper::_('script', 'media/com_phocacart/js/chosen/chosen.required.js', array('version' => 'auto'), $this->scriptAtributeInline);

                $this->wa->registerAndUseScript('com_phocacart.chosen.jquery', 'media/com_phocacart/js/chosen/chosen.jquery.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
                $this->wa->registerAndUseScript('com_phocacart.chosen.required', 'media/com_phocacart/js/chosen/chosen.required.js', array('version' => 'auto'), $this->scriptAtributeInline);

                $js = "\n" . 'jQuery(document).ready(function(){' . "\n";
                $js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});' . "\n"; // Set chosen, created hidden will be required
                // When select box is required, display the error message (when value not selected)
                // But on mobiles, this hide standard select boxes
                // we need to have condition, if really chosen is applied:
                // https://github.com/harvesthq/chosen/issues/1582
                //$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
                $js .= '});' . "\n";
                $this->wa->addInlineScript($js);
                //$this->document->addScriptDeclaration($js);
                //JHtml::stylesheet('media/com_phocacart/js/chosen/chosen.css');
                //JHtml::stylesheet('media/com_phocacart/js/chosen/chosen-bootstrap.css');
                //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/chosen/chosen.css', array('version' => 'auto'));
                //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/chosen/chosen-bootstrap.css', array('version' => 'auto'));

                $this->wa->registerAndUseStyle('com_phocacart.chosen', 'media/com_phocacart/js/chosen/chosen.css', array('version' => 'auto'));
                $this->wa->registerAndUseStyle('com_phocacart.chosen-bootstrap', 'media/com_phocacart/js/chosen/chosen-bootstrap.css', array('version' => 'auto'));
            }
        }
    }


    public function loadFileInput() {
        if ($this->load) {
            //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/tower/tower-file-input.min.js');
            //JHtml::stylesheet('media/com_phocacart/js/tower/tower-file-input.min.css');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/tower/tower-file-input.min.css', array('version' => 'auto'));
           // HTMLHelper::_('script', 'media/com_phocacart/js/tower/tower-file-input.min.js', array('version' => 'auto'), $this->scriptAtribute);

            $this->wa->registerAndUseStyle('com_phocacart.tower-file-input', 'media/com_phocacart/js/tower/tower-file-input.min.css', array('version' => 'auto'));
            $this->wa->registerAndUseScript('com_phocacart.tower-file-input', 'media/com_phocacart/js/tower/tower-file-input.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }


    public function loadTouchSpin($name, $icons, $load = true) {

        $jsS = "\n" . 'jQuery(document).ready(function(){' . "\n";
        $js  = '   jQuery("input[name=\'' . $name . '\']:visible").TouchSpin({';
        $js  .= '   verticalbuttons: true,';


       /* if ($this->p['quantity_input_spinner'] == 2) {

            $js .= '   verticalup: \'<span class="' . $icons['chevron-up'] . '"></span>\',';
            $js .= '   verticaldown: \'<span class="' . $icons['chevron-down'] . '"></span>\',';
        } else {
            $js .= '   verticalup: \'<span class="' . $icons['plus'] . '"></span>\',';
            $js .= '   verticaldown: \'<span class="' . $icons['minus'] . '"></span>\',';
        }*/

        if ($this->p['quantity_input_spinner'] == 2) {

            $js .= '   verticalup: \''.PhocacartRenderIcon::icon($icons['chevron-up']).'\',';
            $js .= '   verticaldown: \''.PhocacartRenderIcon::icon($icons['chevron-down']).'\',';
        } else {
            $js .= '   verticalup: \''.PhocacartRenderIcon::icon($icons['plus']).'\',';
            $js .= '   verticaldown: \''.PhocacartRenderIcon::icon($icons['minus']).'\',';
        }



        //$js .= '   verticalupclass: "'.PhocacartRenderIcon::getClass('chevron-up').'",';
        //$js .= '   verticaldownclass: "'.PhocacartRenderIcon::getClass('chevron-down').'"';
        $js  .= ' })';
        $jsE = '});' . "\n";

        if ($this->p['quantity_input_spinner'] > 0 && $load && $this->load) {
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin'.$this->t['min'].'.js');
            //JHtml::stylesheet( 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin.css' );
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin.css', array('version' => 'auto'));
           // HTMLHelper::_('script', 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->registerAndUseStyle('com_phocacart.jquery.bootstrap-touchspin', 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin.css', array('version' => 'auto'));
            $this->wa->registerAndUseScript('com_phocacart.jquery.bootstrap-touchspin', 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->addInlineScript($jsS . $js . $jsE);
           // $this->document->addScriptDeclaration($jsS . $js . $jsE);
        }

        //return $js;
    }

    public function loadSwiper() {
        if ($this->load) {
            //JHtml::stylesheet('media/com_phocacart/js/swiper/swiper.min.css');
            //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/swiper/swiper.min.js');
            HTMLHelper::_('jquery.framework', false);
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/swiper/swiper.min.css', array('version' => 'auto'));
            //HTMLHelper::_('script', 'media/com_phocacart/js/swiper/swiper.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

            //$this->wa->registerAndUseStyle('com_phocacart.swiper', 'media/com_phocacart/js/swiper/swiper.min.css', array('version' => 'auto'));
            //$this->wa->registerAndUseScript('com_phocacart.swiper', 'media/com_phocacart/js/swiper/swiper.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
            $this->wa->registerAndUseStyle('com_phocacart.swiper', 'media/com_phocacart/js/swiper/swiper.min.css', array('version' => 'auto'));
            $this->wa->registerAndUseScript('com_phocacart.swiper', 'media/com_phocacart/js/swiper/swiper.min.js', array('version' => 'auto'), ['defer' => true]);
        }
    }

    public function loadAnimateCss() {
        if ($this->load) {
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/css/animate/animate.min.css', array('version' => 'auto'));

            $this->wa->registerAndUseStyle('com_phocacart.animate', 'media/com_phocacart/css/animate/animate.min.css', array('version' => 'auto'));
        }
    }


    /*
        public function loadEqualHeights() {

            if ($this->p['equal_height'] == 1) {
                return 'row-flex';
            } else {
                return '';
            }

            /*if ($load == 1) {

                //$app			= Factory::getApplication();
                //$paramsC 		= PhocacartUtils::getComponentParameters();
                //$equal_height_method	= $paramsC->get( 'equal_height_method', 1 );
                $equal_height_method 	= 0;// FLEXBOX USED

                if ($equal_height_method == 1) {
                    $this->document->addScript(Uri::root(true).'/media/com_phocacart/js/jquery.equal.heights.js');
                    //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/jquery.equalminheights.min.js');
                    $this->document->addScriptDeclaration(
                    'jQuery(window).load(function(){
                        jQuery(\'.ph-thumbnail-c\').equalHeights();
                    });');
                } else if ($equal_height_method == 2) {
                    $this->document->addScript(Uri::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
                    $this->document->addScriptDeclaration(
                    'jQuery(window).load(function(){
                        jQuery(\'.ph-thumbnail-c\').matchHeight();
                    });');
                } else if ($equal_height_method == 3) {

                    $this->document->addScript(Uri::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
                    $this->document->addScriptDeclaration(
                    'jQuery(window).load(function(){
                        jQuery(\'.ph-thumbnail-c.grid\').matchHeight({
                           byRow: false,
                           property: \'height\',
                           target: null,
                           remove: false
                        });
                    });');
                }
                // not ph-thumbnail but only ph-thumbnail-c (in component area so module will not influence it)
            }
        }*/


    public function loadPhocaSwapImage() {
        if ($this->p['dynamic_change_image'] == 1 && $this->load) {
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/jquery.phocaswapimage'.$this->t['min'].'.js');
            //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/jquery.phocaswapimage' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtribute);

            $this->wa->registerAndUseScript('com_phocacart.phocaswapimage', 'media/com_phocacart/js/phoca/jquery.phocaswapimage' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }


    public function loadPhocaAttribute($load = 0) {
        if ($load == 1 && $this->load) {
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute'.$this->t['min'].'.js');
            //HTMLHelper::_('script', 'media/com_phocacart/js/base64/base64' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
           // HTMLHelper::_('script', 'media/com_phocacart/js/phoca/jquery.phocaattribute' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->registerAndUseScript('com_phocacart.base64', 'media/com_phocacart/js/base64/base64' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
            $this->wa->registerAndUseScript('com_phocacart.phocaattribute', 'media/com_phocacart/js/phoca/jquery.phocaattribute' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }

    public function loadPhocaAttributeRequired($load = 0) {
        if ($load == 1 && $this->load) {
            //$this->document->addScript(Uri::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattributerequired'.$this->t['min'].'.js');
            //HTMLHelper::_('script', 'media/com_phocacart/js/phoca/jquery.phocaattributerequired' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtribute);

            $this->wa->registerAndUseScript('com_phocacart.phocaattributerequired', 'media/com_phocacart/js/phoca/jquery.phocaattributerequired' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }

    public function loadRating() {
        if ($this->load) {
            //JHtml::stylesheet('media/com_phocacart/js/barrating/css/rating.css');
            //JHtml::stylesheet('media/com_phocacart/js/barrating/themes/css-stars.css');
            //$this->document->addScript(Uri::root(true) . '/media/com_phocacart/js/barrating/jquery.barrating'.$this->t['min'].'.js');
           // HTMLHelper::_('stylesheet', 'media/com_phocacart/js/barrating/themes/css-stars.css', array('version' => 'auto'));
           // HTMLHelper::_('script', 'media/com_phocacart/js/barrating/jquery.barrating' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->registerAndUseStyle('com_phocacart.css-stars', 'media/com_phocacart/js/barrating/themes/css-stars.css', array('version' => 'auto'));
            $this->wa->registerAndUseScript('com_phocacart.barrating', 'media/com_phocacart/js/barrating/jquery.barrating' . $this->t['min'] . '.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $js = "\n" . 'jQuery(document).ready(function(){' . "\n";
            $js .= '   jQuery(\'#phitemrating\').barrating({ showSelectedRating:false, theme: \'css-stars\' });' . "\n";
            $js .= '});' . "\n";
            $this->wa->addInlineScript($js);
            //$this->document->addScriptDeclaration($js);
        }
    }


    public function loadUiSlider() {
        if ($this->load) {
            //$document->addScript(Uri::root(true) . '/media/com_phocacart/js/ui/jquery-ui.slider.min.js');
            //JHtml::stylesheet('media/com_phocacart/js/ui/jquery-ui.slider.min.css');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/ui/jquery-ui.slider.min.css', array('version' => 'auto'));
            //HTMLHelper::_('script', 'media/com_phocacart/js/ui/jquery-ui.slider.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

            $this->wa->registerAndUseStyle('com_phocacart.jquery-ui.slider', 'media/com_phocacart/js/ui/jquery-ui.slider.min.css', array('version' => 'auto'));
            $this->wa->registerAndUseScript('com_phocacart.jquery-ui.slider', 'media/com_phocacart/js/ui/jquery-ui.slider.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

            // Can be set in case of problem with Joomla! core bootstrap which loads tooltip js
            // libraries/cms/html/bootstrap.php cca line 500 - loaded e.g. per pagination
            //HTMLHelper::_('script', 'media/com_phocacart/js/ui/jquery-ui.slider.tooltip.min.js', array('version' => 'auto'), $this->scriptAtribute);
        }
    }

    public function loadJsTree() {
        if ($this->load) {
            //JHtml::stylesheet('media/com_phocacart/js/jstree/themes/proton/style.min.css');
            //$document->addScript(Uri::root(true).'/media/com_phocacart/js/jstree/jstree.min.js');
            //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/jstree/themes/proton/style.min.css', array('version' => 'auto'));
            //HTMLHelper::_('script', 'media/com_phocacart/js/jstree/jstree.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

            HTMLHelper::_('jquery.framework', false);
            $this->wa->registerAndUseStyle('com_phocacart.jstree', 'media/com_phocacart/js/jstree/themes/proton/style.min.css');
            $this->wa->registerAndUseScript('com_phocacart.jstree', 'media/com_phocacart/js/jstree/jstree.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
        }
    }

    public function renderMagnific() {

        //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/magnific/magnific-popup.css', array('version' => 'auto'));
       // HTMLHelper::_('script', 'media/com_phocacart/js/magnific/jquery.magnific-popup.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

        $this->wa->registerAndUseStyle('com_phocacart.magnific-popup', 'media/com_phocacart/js/magnific/magnific-popup.css', array('version' => 'auto'));
        $this->wa->registerAndUseScript('com_phocacart.jquery.magnific-popup', 'media/com_phocacart/js/magnific/jquery.magnific-popup.min.js', array('version' => 'auto'), $this->scriptAtributeInline);

        $s   = array();
        $s[] = 'jQuery(document).ready(function() {';
        $s[] = '	jQuery(\'#phImageBox\').magnificPopup({';
        $s[] = '		tLoading: \'' . Text::_('COM_PHOCACART_LOADING') . '\',';
        $s[] = '		tClose: \'' . Text::_('COM_PHOCACART_CLOSE') . '\',';
        $s[] = '		delegate: \'a.magnific\',';
        $s[] = '		type: \'image\',';
        $s[] = '		mainClass: \'mfp-img-mobile\',';
        $s[] = '		zoom: {';
        $s[] = '			enabled: true,';
        $s[] = '			duration: 300,';
        $s[] = '			easing: \'ease-in-out\'';
        $s[] = '		},';
        $s[] = '		gallery: {';
        $s[] = '			enabled: true,';
        $s[] = '			navigateByImgClick: true,';
        $s[] = '			tPrev: \'' . Text::_('COM_PHOCACART_PREVIOUS') . '\',';
        $s[] = '			tNext: \'' . Text::_('COM_PHOCACART_NEXT') . '\',';
        $s[] = '			tCounter: \'' . Text::_('COM_PHOCACART_MAGNIFIC_CURR_OF_TOTAL') . '\'';
        $s[] = '		},';
        $s[] = '		image: {';
        $s[] = '			titleSrc: function(item) {';
        $s[] = '				return item.el.attr(\'title\');';
        $s[] = '			},';
        $s[] = '			tError: \'' . Text::_('COM_PHOCACART_IMAGE_NOT_LOADED') . '\'';
        $s[] = '		}';
        $s[] = '	});';
        $s[] = '});';

        $this->wa->addInlineScript(implode("\n", $s));
        //$this->document->addScriptDeclaration(implode("\n", $s));

    }

    public function renderPrettyPhoto() {

        //HTMLHelper::_('stylesheet', 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css', array('version' => 'auto'));
       // HTMLHelper::_('script', 'media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js', array('version' => 'auto'), $this->scriptAtributeInline);

        $this->wa->registerAndUseStyle('com_phocacart.prettyphoto', 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css', array('version' => 'auto'));
        $this->wa->registerAndUseScript('com_phocacart.prettyphoto', 'media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js', array('version' => 'auto'), $this->scriptAtributeInline);

        $s[] = 'jQuery(document).ready(function(){';
        $s[] = '	jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({';
        $s[] = '  social_tools: 0';
        $s[] = '  });';
        $s[] = '})';

        $this->wa->addInlineScript(implode("\n", $s));
        //$this->document->addScriptDeclaration(implode("\n", $s));
    }

    public function renderChartJs() {
        //HTMLHelper::_('script', 'media/com_phocacart/js/chartjs/Chart.min.js', array('version' => 'auto'), $this->scriptAtribute);
        $this->wa->registerAndUseScript('com_phocacart.chart', 'media/com_phocacart/js/chartjs/Chart.min.js', array('version' => 'auto'), $this->scriptAtributeInline);
    }
}

?>
