<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

class PhocaCartViewFeed extends HtmlView
{
    protected $t;
    protected $r;
    protected $p;
    protected $tempFile;
    protected $pageIndex;
    protected $products = null;
    protected $justClose = false;

    function display($tpl = null) {
        $app  = Factory::getApplication();
        $id   = $app->input->get('id', 0, 'int');
        $this->t['feed'] = PhocacartFeed::getFeed((int)$id);

        if ($this->t['feed']) {
            $fP = new Registry($this->t['feed']['feed_params'] ?? '');
            $iP = new Registry($this->t['feed']['item_params'] ?? '');

            $this->t['pathitem'] = PhocacartPath::getPath('productimage');

            // Feed Params
            $this->p['export_published_only'] = $fP->get('export_published_only', true);
            $this->p['export_in_stock_only']  = $fP->get('export_in_stock_only', false);
            $this->p['export_price_only']     = $fP->get('export_price_only', true);
            $this->p['strip_html_tags_desc']  = $fP->get('strip_html_tags_desc', true);
            $this->p['item_limit']            = $fP->get('item_limit', false);
            $this->p['item_ordering']         = $fP->get('item_ordering', true);
            $this->p['category_ordering']     = $fP->get('category_ordering', false);
            $this->p['display_attributes']    = $fP->get('display_attributes', false);
            $this->p['specification_groups_id']= $fP->get('specification_groups_id', '');
            $this->p['category_separator']    = $fP->get('category_separator', '');
            $this->p['load_all_categories']   = $fP->get('load_all_categories', false);

            $this->p['price_decimals']           = $fP->get('price_decimals', '');
            $this->p['price_including_currency'] = $fP->get('price_including_currency', false);

            if ($this->p['category_separator'] == '\n') {
                $this->p['category_separator'] = "\n";
            }
            if ($this->p['category_separator'] == '\r') {
                $this->p['category_separator'] = "\r";
            }
            if ($this->p['category_separator'] == '\r\n') {
                $this->p['category_separator'] = "\r\n";
            }

            // Item Params (phocacartfeed.xml, language string, view.xml.php here defined and conditions below)
            $this->p['item_id']                         = $iP->get('item_id', '');
            $this->p['item_title']                      = $iP->get('item_title', '');
            $this->p['item_title_extended']             = $iP->get('item_title_extended', '');
            $this->p['item_description_short']          = $iP->get('item_description_short', '');
            $this->p['item_description_long']           = $iP->get('item_description_long', '');
            $this->p['item_sku']                        = $iP->get('item_sku', '');
            $this->p['item_ean']                        = $iP->get('item_ean', '');
            $this->p['item_original_price_with_vat']    = $iP->get('item_original_price_with_vat', '');
            $this->p['item_original_price_without_vat'] = $iP->get('item_original_price_without_vat', '');
            $this->p['item_final_price_with_vat']       = $iP->get('item_final_price_with_vat', '');
            $this->p['item_final_price_without_vat']    = $iP->get('item_final_price_without_vat', '');
            $this->p['item_vat']                        = $iP->get('item_vat', '');
            $this->p['item_currency']                   = $iP->get('item_currency', '');
            $this->p['item_url_image']                  = $iP->get('item_url_image', '');
            $this->p['item_url_video']                  = $iP->get('item_url_video', '');
            $this->p['item_category']                   = $iP->get('item_category', '');
            $this->p['item_categories']                 = $iP->get('item_categories', '');
            $this->p['feed_category']                   = $iP->get('feed_category', '');
            $this->p['item_manufacturer']               = $iP->get('item_manufacturer', '');
            $this->p['item_stock']                      = $iP->get('item_stock', '');
            $this->p['item_delivery_date']              = $iP->get('item_delivery_date', '');     // Stock Status
            $this->p['item_delivery_date_date']         = $iP->get('item_delivery_date_date', '');// Real Date
            $this->p['feed_delivery_date']              = $iP->get('feed_delivery_date', '');
            $this->p['item_attribute']                  = $iP->get('item_attribute', '');
            $this->p['item_attribute_name']             = $iP->get('item_attribute_name', '');
            $this->p['item_attribute_value']            = $iP->get('item_attribute_value', '');
            $this->p['item_specification']              = $iP->get('item_specification', '');
            $this->p['item_specification_group_name']   = $iP->get('item_specification_group_name', '');
            $this->p['item_specification_name']         = $iP->get('item_specification_name', '');
            $this->p['item_specification_value']        = $iP->get('item_specification_value', '');
            $this->p['item_url']                        = $iP->get('item_url', '');
            $this->p['item_condition']                  = $iP->get('item_condition', '');
            $this->p['item_reward_points']              = $iP->get('item_reward_points', '');
            $this->p['item_reward_points_name']         = $iP->get('item_reward_points_name', '');
            $this->p['item_reward_points_value']        = $iP->get('item_reward_points_value', '');
            $this->p['item_type_feed']                  = $iP->get('item_type_feed', '');
            $this->p['item_category_type_feed']         = $iP->get('item_category_type_feed', '');
            $this->p['item_fixed_elements']             = $iP->get('item_fixed_elements', '');

            /*
            // We can find specific feed and customize it for specific needs
            // E.g. Heureka
            $this->t['feed']Name = '';
            if (isset($this->t['feed']['title'])) {
                if (strpos(strtolower($this->t['feed']['title']), 'heureka') !== false) {
                    $this->t['feed']Name = 'heureka';
                }
            }
            */

            $forceLang = '';
            if (isset($this->t['feed']['language']) && $this->t['feed']['language'] != '') {
                $forceLang = $this->t['feed']['language'];
            }

            // Load all categories for a product or only one
            // This influences two parameters: Categories and Product Category Type
            $categoriesList = 0;
            if ($this->p['load_all_categories']) {
                $categoriesList = 5;
            }


            // Possible feature - accept languages
            $filename = $app->getConfig()->get('cache_path') . '/tmpfeed.xml';
            $this->tempFile = fopen($filename, "w");

            $this->products = PhocacartProduct::getProducts(0, 1000, $this->p['item_ordering'], $this->p['category_ordering'], $this->p['export_published_only'], $this->p['export_in_stock_only'], $this->p['export_price_only'], $categoriesList, array(), 0, array(0, 1), '', '', false, $forceLang);
            $this->pageIndex = 0;
            gc_disable();
            while ($this->products) {
                parent::display($tpl);

                $this->products = null;
                gc_collect_cycles();

                $this->pageIndex++;
                $this->products = PhocacartProduct::getProducts($this->pageIndex * 1000, 1000, $this->p['item_ordering'], $this->p['category_ordering'], $this->p['export_published_only'], $this->p['export_in_stock_only'], $this->p['export_price_only'], $categoriesList, array(), 0, array(0, 1), '', '', false, $forceLang);
            }
            $this->justClose = true;
            parent::display($tpl);
            gc_collect_cycles();
            gc_enable();

            fclose($this->tempFile);
            readfile($filename);
            unlink($filename);
            //$this->t['products'] = PhocacartProduct::getProducts(0, (int)$this->p['item_limit'], $this->p['item_ordering'], $this->p['category_ordering'], $this->p['export_published_only'], $this->p['export_in_stock_only'], $this->p['export_price_only'], $categoriesList, array(), 0, array(0, 1), '', '', false, $forceLang);


            //$result = Dispatcher::dispatch(new Event\Feed\Render('', $this->t['products']));
            /*$result = [];
            if (!in_array(true, $result)) {
                $filename = $app->getConfig()->get('cache_path') . '/tmpfeed.xml';
                $this->tempFile = fopen($filename, "w");
                parent::display($tpl);
                fclose($this->tempFile);
                readfile($filename);
                unlink($filename);
            }*/
        }
    }

    public function loadTemplate($tpl = null)
    {
        // Clear prior output
        $this->_output = null;

        $template       = Factory::getApplication()->getTemplate(true);
        $layout         = $this->getLayout();
        $layoutTemplate = $this->getLayoutTemplate();

        // Create the template file name based on the layout
        $file = isset($tpl) ? $layout . '_' . $tpl : $layout;

        // Clean the file name
        $file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
        $tpl  = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

        try {
            // Load the language file for the template
            $lang = $this->getLanguage();
        } catch (\UnexpectedValueException $e) {
            $lang = Factory::getApplication()->getLanguage();
        }

        $lang->load('tpl_' . $template->template, JPATH_BASE)
        || $lang->load('tpl_' . $template->parent, JPATH_THEMES . '/' . $template->parent)
        || $lang->load('tpl_' . $template->template, JPATH_THEMES . '/' . $template->template);

        // Change the template folder if alternative layout is in different template
        if (isset($layoutTemplate) && $layoutTemplate !== '_' && $layoutTemplate != $template->template) {
            $this->_path['template'] = str_replace(
                JPATH_THEMES . DIRECTORY_SEPARATOR . $template->template . DIRECTORY_SEPARATOR,
                JPATH_THEMES . DIRECTORY_SEPARATOR . $layoutTemplate . DIRECTORY_SEPARATOR,
                $this->_path['template']
            );
        }

        // Load the template script
        $filetofind      = $this->_createFileName('template', ['name' => $file]);
        $this->_template = Path::find($this->_path['template'], $filetofind);

        // If alternate layout can't be found, fall back to default layout
        if ($this->_template == false) {
            $filetofind      = $this->_createFileName('', ['name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)]);
            $this->_template = Path::find($this->_path['template'], $filetofind);
        }

        if ($this->_template != false) {
            // Unset so as not to introduce into template scope
            unset($tpl, $file);

            // Never allow a 'this' property
            if (isset($this->this)) {
                unset($this->this);
            }

            // Include the requested template filename in the local scope
            // (this will execute the view logic).
            include $this->_template;
            $this->_output = '';

            return $this->_output;
        }

        throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
    }

    public function output(string $value)
    {
        fwrite($this->tempFile, $value . "\n");
    }
}
