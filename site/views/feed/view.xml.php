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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

jimport('joomla.application.component.view');

class PhocaCartViewFeed extends HtmlView
{

    protected $t;
    protected $r;
    protected $p;

    function display($tpl = null) {

        $app  = Factory::getApplication();
        $id   = $app->input->get('id', 0, 'int');
        $this->t['feed'] = PhocacartFeed::getFeed((int)$id);



        if ($this->t['feed']) {
            $fP = new Registry;
            $iP = new Registry;


            if (isset($this->t['feed']['feed_params']) && $this->t['feed']['feed_params'] != '') {
                $fP->loadString($this->t['feed']['feed_params']);
            }

            if (isset($this->t['feed']['item_params']) && $this->t['feed']['item_params'] != '') {
                $iP->loadString($this->t['feed']['item_params']);
            }


            $this->t['pathitem'] = PhocacartPath::getPath('productimage');

            // Feed Params
            $this->p['export_published_only'] = $fP->get('export_published_only', 1);
            $this->p['export_in_stock_only']  = $fP->get('export_in_stock_only', 0);
            $this->p['export_price_only']     = $fP->get('export_price_only', 1);
            $this->p['strip_html_tags_desc']  = $fP->get('strip_html_tags_desc', 1);
            $this->p['item_limit']            = $fP->get('item_limit', 0);
            $this->p['item_ordering']         = $fP->get('item_ordering', 1);
            $this->p['category_ordering']     = $fP->get('category_ordering', 0);
            $this->p['display_attributes']    = $fP->get('display_attributes', 0);
            $this->p['specification_groups_id']= $fP->get('specification_groups_id', '');
            $this->p['category_separator']    = $fP->get('category_separator', '');
            $this->p['load_all_categories']   = $fP->get('load_all_categories', 0);

            $this->p['price_decimals']           = $fP->get('price_decimals', '');
            $this->p['price_including_currency'] = $fP->get('price_including_currency', 0);

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

            $this->p['item_fixed_elements'] = $iP->get('item_fixed_elements', '');

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
            if ($this->p['load_all_categories'] == 1) {
                $categoriesList = 5;
            }

            // Possible feature - accept languages
            $this->t['products'] = PhocacartProduct::getProducts(0, (int)$this->p['item_limit'], $this->p['item_ordering'], $this->p['category_ordering'], $this->p['export_published_only'], $this->p['export_in_stock_only'], $this->p['export_price_only'], $categoriesList, array(), 0, array(0, 1), '', '', false, $forceLang );

            parent::display($tpl);
        }
    }
}

?>
