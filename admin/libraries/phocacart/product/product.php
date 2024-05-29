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

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartProduct
{

    public const PRODUCT_TYPE_PHYSICAL_PRODUCT = 0;
    public const PRODUCT_TYPE_DIGITAL_DOWNLOADABLE_PRODUCT = 1;
    public const PRODUCT_TYPE_MIXED_PRODUCT_DIGITAL_PHYSICAL = 2;
    public const PRODUCT_TYPE_PRICE_ON_DEMAND_PRODUCT = 3;
    public const PRODUCT_TYPE_GIFT_VOUCHER = 4;
    public const PRODUCT_TYPE_BUNDLE = 5;

    private static $productAccess = array();
    private static $productAttributes = array();

    private static function dispatchLoadColumns(array &$columns)
    {
        $pluginOptions = [];
        Dispatcher::dispatch(new Event\View\Product\BeforeLoadColumns('com_phocacart.product', $pluginOptions));

        $pluginColumns = $pluginOptions['columns'] ?? [];
        array_walk($pluginColumns, function($column) {
            return PhocacartText::filterValue($column, 'alphanumeric3');
        });

        $columns = array_merge($columns, $pluginColumns);
    }

    public static function getProduct($productId, $prioritizeCatid = 0, $type = array(0, 1))
    {
        $db     = Factory::getDBO();
        $where  = [];
        $params = PhocacartUtils::getComponentParameters();
        $user   = PhocacartUser::getUser();

        $where[] = 'i.id = ' . (int) $productId;

        $columns = [
            'i.id', 'i.metadata',
            'i.type', 'i.image', 'i.weight', 'i.height', 'i.width', 'i.length', 'i.min_multiple_quantity', 'i.min_quantity_calculation', 'i.volume',
            'i.price', 'i.price_original', 'i.stockstatus_a_id', 'i.stockstatus_n_id', 'i.stock_calculation',
            'i.min_quantity', 'i.min_multiple_quantity', 'i.stock', 'i.sales', 'i.featured', 'i.external_id', 'i.unit_amount', 'i.unit_unit',
            'i.video', 'i.external_link', 'i.external_text', 'i.external_link2', 'i.external_text2', 'i.public_download_file', 'i.public_download_text',
            'i.public_play_file', 'i.public_play_text', 'i.sku', 'i.upc', 'i.ean', 'i.jan', 'i.isbn', 'i.mpn', 'i.serial_number',
            'i.points_needed', 'i.points_received', 'i.download_file', 'i.download_token', 'i.download_folder', 'i.download_days',
            'i.date', 'i.date_update', 'i.delivery_date', 'i.gift_types', 'i.owner_id',
            'pc.ordering', 'c.id AS catid', 'i.condition', 'i.language',
            'm.id as manufacturerid',
        ];

       /* if (I18nHelper::useI18n()) {
            $columns = array_merge($columns, [
                'coalesce(i18n_i.title, i.title) as title', 'i18n_i.title_long', 'coalesce(i18n_i.alias, i.alias) as alias', 'i18n_i.description', 'i18n_i.description_long', 'i18n_i.features', 'i18n_i.metatitle', 'i18n_i.metadesc', 'i18n_i.metakey',
                'coalesce(i18n_c.title, c.title) AS cattitle', 'coalesce(i18n_c.alias, c.alias) AS catalias',
                'coalesce(i18n_m.title, m.title) as manufacturertitle', 'coalesce(i18n_m.alias, m.alias) as manufactureralias',
            ]);
        } else {
            $columns = array_merge($columns, [
                'i.title', 'i.title_long', 'i.alias', 'i.description', 'i.description_long', 'i.features', 'i.metatitle', 'i.metadesc', 'i.metakey',
                'c.title AS cattitle', 'c.alias AS catalias',
                'm.title as manufacturertitle', 'm.alias as manufactureralias',
            ]);
        }*/

        $columns = array_merge($columns, [
            I18nHelper::sqlCoalesce(['title'], 'i'),
            I18nHelper::sqlCoalesce(['alias'], 'i'),
            I18nHelper::sqlCoalesce(['title_long'], 'i'),
            I18nHelper::sqlCoalesce(['description'], 'i'),
            I18nHelper::sqlCoalesce(['description_long'], 'i'),
            I18nHelper::sqlCoalesce(['features'], 'i'),
            I18nHelper::sqlCoalesce(['metatitle'], 'i'),
            I18nHelper::sqlCoalesce(['metadesc'], 'i'),
            I18nHelper::sqlCoalesce(['metakey'], 'i'),
            I18nHelper::sqlCoalesce(['title'], 'c', 'cat'),
            I18nHelper::sqlCoalesce(['alias'], 'c', 'cat'),
            I18nHelper::sqlCoalesce(['title'], 'm', 'manufacturer'),
            I18nHelper::sqlCoalesce(['link'], 'm', 'manufacturer')
        ]);

        if (!$params->get('sql_product_skip_tax', false)) {
            $columns = array_merge($columns, [
                't.id as taxid', 't.tax_rate as taxrate', 't.calculation_type as taxcalculationtype', I18nHelper::sqlCoalesce(['title'], 't', 'tax'), 't.tax_hide as taxhide'
            ]);
        } else {
            $columns = array_merge($columns, [
                'NULL as taxid', 'NULL as taxrate', 'NULL as taxcalculationtype', 'NULL as taxtitle', 'NULL as taxhide'
            ]);
        }


        if (!$params->get('sql_product_skip_group', false)) {
            $columns = array_merge($columns, [
                'MIN(ppg.price) as group_price', 'MAX(pptg.points_received) as group_points_received'
            ]);
        } else {
            $columns = array_merge($columns, [
                'NULL as group_price', 'NULL as group_points_received'
            ]);
        }

        self::dispatchLoadColumns($columns);

        $columns = array_unique($columns);


        $query = ' SELECT ' . implode(', ', $columns)
            . ' FROM #__phocacart_products AS i'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = i.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id';

        if (I18nHelper::useI18n()) {
            $query .= I18nHelper::sqlJoin('#__phocacart_products_i18n', 'i');
            $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');
            $query .= I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm');
        }

        if (!$params->get('sql_product_skip_tax', false)) {
            $query .= ' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id';
            $query .= I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't');
        }

        if (!$params->get('sql_product_skip_group', false)) {
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
            $query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON i.id = ga.item_id AND ga.type = ' . GroupType::Product;
            $query .= ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;
            // user is in more groups, select lowest price by best group
            $query .= ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON i.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN (' . $userGroups . ') AND type = ' . GroupType::Product . ')';
            // user is in more groups, select highest points by best group
            $query .= ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON i.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN (' . $userGroups . ') AND type = ' . GroupType::Product . ')';
        }

        $query .= ' WHERE ' . implode(' AND ', $where)
            . ' LIMIT 1';
        $db->setQuery($query);
        $product = $db->loadObject();

        // When we add the product, we can use the catid from where we are located
        // if we are in category A, then we try to add this product with category A
        // BUT the product can be displayed in cart e.g. 3x so only the last added catid
        // is used for creating the SEF URL
        // Using catid is only about SEF URL
        if ((int) $prioritizeCatid > 0) {
            if (isset($product->catid) && (int) $product->catid == (int) $prioritizeCatid) {
                //$product->catid is $product->catid
            }
            else {
                // Recheck the category id of product
                $checkCategory = false;
                if (isset($product->id)) {
                    $checkCategory = PhocacartProduct::checkIfAccessPossible((int) $product->id, (int) $prioritizeCatid, $type);
                }

                if ($checkCategory) {
                    $product->catid = (int) $prioritizeCatid;
                }
            }
        }


        // Change TAX based on country or region
        if (!empty($product)) {

            // Is tax active?
            $tax = PhocacartTax::getTaxById($product->taxid);

            if (isset($product->taxhide)) {
                $registry = new Registry;
                $registry->loadString($product->taxhide);
                $product->taxhide = $registry->toArray();
            }

            if ($tax) {
                $taxChangedA           = PhocacartTax::changeTaxBasedOnRule($product->taxid, $product->taxrate, $product->taxcalculationtype, $product->taxtitle, $product->taxhide);
                $product->taxid        = $taxChangedA['taxid'];
                $product->taxrate      = $taxChangedA['taxrate'];
                $product->taxtitle     = $taxChangedA['taxtitle'];
                $product->taxcountryid = $taxChangedA['taxcountryid'];
                $product->taxregionid  = $taxChangedA['taxregionid'];
                $product->taxpluginid  = $taxChangedA['taxpluginid'];
                $product->taxhide      = $taxChangedA['taxhide'];
            } else {
                $product->taxid        = 0;
                $product->taxrate      = 0;
                $product->taxtitle     = '';
                $product->taxcountryid = 0;
                $product->taxregionid  = 0;
                $product->taxpluginid  = 0;
            }
        }

        return $product;
    }

    /*
     * Check if user has access to this product
     * when adding to cart
     * when ordering
     * NOT USED when displaying, as no products are displayed which cannnot be accessed
     * So this is security feature in case of forgery - server side checking
     * STRICT RULES ARE VALID - if the product is included in
     */

    public static function checkIfAccessPossible($id, $catid, $type = array(0, 1))
    {

        $typeS = base64_encode(serialize(ksort($type)));

        if (!isset(self::$productAccess[$id][$catid][$typeS])) {

            if ((int)$id > 0) {

                $db = Factory::getDBO();
                $wheres = array();
                $user = PhocacartUser::getUser();

                $userLevels = implode(',', $user->getAuthorisedViewLevels());
                $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
                $wheres[] = " a.access IN (" . $userLevels . ")";
                $wheres[] = " c.access IN (" . $userLevels . ")";
                $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
                $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
                $wheres[] = " a.published = 1";
                $wheres[] = " c.published = 1";
                $wheres[] = ' a.id = ' . (int)$id;
                $wheres[] = ' c.id = ' . (int)$catid;

                //$wheres[] 	= ' c.type IN ('.implode(',', $type).')';
                if (!empty($type) && is_array($type)) {
                    $wheres[] = ' c.type IN (' . implode(',', $type) . ')';// Category Type (Shop/POS)
                }

                //$wheres[] 	= ' c.id = '.(int)$catid;

                // PRODUCTTYPE
                // 0 ... physical product, 1 ... digital product, 2 ... physical and digital product, 3 ... price on demand product
                $wheres[] = ' a.type != 3';// price on demand product cannot be ordered and cannot be added to cart

                $query = ' SELECT a.id'
                    . ' FROM #__phocacart_products AS a'
                    . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
                    . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
                    //.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
                    . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                    . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
                    . ' WHERE ' . implode(' AND ', $wheres)
                    . ' ORDER BY a.id'
                    . ' LIMIT 1';

                $db->setQuery($query);

                $product = $db->loadObject();



                if (isset($product->id) && (int)$product->id > 0) {
                    //return true;
                    self::$productAccess[$id][$catid][$typeS] = true;
                } else {
                    //$app	= Factory::getApplication();
                    //$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ATTRIBUTE_REQUIRED'), 'error');
                    //return false;// seems like attribute is required but not selected
                    self::$productAccess[$id][$catid][$typeS] = false;
                }


            } else {
                self::$productAccess[$id][$catid][$typeS] = false;
            }


        }

        return self::$productAccess[$id][$catid][$typeS];

    }


    public static function checkIfProductAttributesOptionsExist($id, $idKey, $catid, $type = array(0, 1), $attribs = array())
    {

        $typeS = base64_encode(serialize(ksort($type)));

        if (!isset(self::$productAttributes[$idKey][$catid][$typeS])) {


            if (!empty($attribs)) {

                $productAttribs = PhocacartAttribute::getAttributesAndOptions($id);

                foreach ($attribs as $k => $v) {

                    if (isset($productAttribs[$k])) {

                        foreach ($v as $k2 => $v2) {

                            if (isset($productAttribs[$k]->options[$k2])) {

                            } else {
                                self::$productAttributes[$idKey][$catid][$typeS] = false;
                                break 2;
                            }
                        }

                        self::$productAttributes[$idKey][$catid][$typeS] = true;
                    } else {
                        self::$productAttributes[$idKey][$catid][$typeS] = false;
                        break;
                    }
                }

            } else {
                self::$productAttributes[$idKey][$catid][$typeS] = true;
            }


            /*if ((int)$id > 0) {

                $db 		= Factory::getDBO();
                $wheres		= array();
                $user 		= PhocacartUser::getUser();
                $userLevels	= implode (',', $user->getAuthorisedViewLevels());
                $userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
                $wheres[] 	= " a.access IN (".$userLevels.")";
                $wheres[] 	= " c.access IN (".$userLevels.")";
                $wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
                $wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
                $wheres[] 	= " a.published = 1";
                $wheres[] 	= " c.published = 1";
                $wheres[] 	= ' a.id = '.(int)$id;
                $wheres[] 	= ' c.id = '.(int)$catid;

                //$wheres[] 	= ' c.type IN ('.implode(',', $type).')';
                if (!empty($type) && is_array($type)) {
                    $wheres[] = ' c.type IN ('.implode(',', $type).')';
                }

                //$wheres[] 	= ' c.id = '.(int)$catid;

                $query = ' SELECT a.id'
                .' FROM #__phocacart_products AS a'
                .' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
                .' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
                //.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
                . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
                .' WHERE ' . implode( ' AND ', $wheres )
                .' ORDER BY a.id'
                .' LIMIT 1';

                $db->setQuery($query);

                $product = $db->loadObject();

                if (isset($product->id) && (int)$product->id > 0) {
                    //return true;
                    self::$productAttributes[$id][$catid][$typeS] = true;
                } else {
                    //$app	= Factory::getApplication();
                    //$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ATTRIBUTE_REQUIRED'), 'error');
                    //return false;// seems like attribute is required but not selected
                    self::$productAttributes[$id][$catid][$typeS] = false;
                }


            } else {
                self::$productAttributes[$id][$catid][$typeS] = false;
            }*/


        }

        return self::$productAttributes[$idKey][$catid][$typeS];

    }

    public static function getProductIdBySku($sku, $typeSku = 'sku', $type = array(0, 1))
    {

        $db = Factory::getDBO();
        $sku = $db->quote($sku);
        $typeSkuS = $db->quoteName('a.' . $typeSku);
        $typeSkuSA = $db->quoteName('ps.' . $typeSku);

        $wheres = array(); // standard product
        $wheresA = array();// advanced stock management product (EAN, SKU)

        $user = PhocacartUser::getUser();

        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
        $wheres[] = " a.access IN (" . $userLevels . ")";
        $wheres[] = " c.access IN (" . $userLevels . ")";
        $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
        $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
        $wheres[] = " a.published = 1";
        $wheres[] = " c.published = 1";


        //$wheres[] 	= ' c.type IN ('.implode(',', $type).')';
        if (!empty($type) && is_array($type)) {
            $wheres[] = ' c.type IN (' . implode(',', $type) . ')';
        }

        $wheresA = $wheres;

        $wheres[] = ' ' . $typeSkuS . ' = ' . $sku;
        $wheresA[] = ' ' . $typeSkuSA . ' = ' . $sku;


        $query = ' SELECT a.id, c.id as catid'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
            . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY a.id';
        //.' LIMIT 1';
        $db->setQuery($query);
        $products = $db->loadObjectlist();



        // We didn' find SKU or EAN in standard products
        // Then try to find it in Advanced Stock Management (only SKU and EAN are active)
        // POS
        if (empty($products) && ($typeSku == 'sku' || $typeSku == 'ean')) {

            // Try to find SKU or EAN in Advanced stock management

            $query = ' SELECT a.id, c.id AS catid, ps.attributes AS attributes'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id'
            . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
            . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
            . ' WHERE ' . implode(' AND ', $wheresA)
            . ' ORDER BY a.id';
            //.' LIMIT 1';
            $db->setQuery($query);
            $products = $db->loadObjectlist();

        }

        if (!empty($products)) {
            foreach ($products as $k => $v) {
                if (isset($v->id) && (int)$v->id > 0 && isset($v->catid) && (int)$v->catid > 0) {
                    $access = PhocacartProduct::checkIfAccessPossible((int)$v->id, (int)$v->catid, $type);

                    if ($access) {
                        // if found some return the first possible - accessible
                        // because of different rights, groups, etc., we need to know catid
                        $product = array();
                        $product['id'] = (int)$v->id;
                        $product['catid'] = (int)$v->catid;

                        if (isset($v->attributes) && $v->attributes != '') {
                            $product['attributes'] = unserialize($v->attributes);
                        }


                        return $product;

                    }
                }
            }
        }
        return false;
    }

    public static function getProductIdByOrder($orderId)
    {

        $db = Factory::getDBO();
        $query = ' SELECT a.id'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_order_products AS o ON o.product_id = a.id'
            . ' WHERE o.id = ' . (int)$orderId
            . ' ORDER BY a.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $product = $db->loadObject();
        return $product;
    }

    /*
     * We don't need catid, we get all categories for this product listed from group_concat
     */
    public static function getProductByProductId($id)
    {
        if ($id < 1) {
            return false;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = 'SELECT a.id, a.catid, a.language, '
            . I18nHelper::sqlCoalesce(['title']) . ', '
            . ' group_concat(CONCAT_WS(":", c.id, ' . I18nHelper::sqlCoalesce(['title'], 'c', '', '', '', '', true) . ') SEPARATOR \',\') AS categories,'
            . ' group_concat(' . I18nHelper::sqlCoalesce(['title'], 'c', '', '', '', '', true) . ' SEPARATOR \' \') AS categories_title,'
            . ' group_concat(c.id SEPARATOR \',\') AS categories_id'
            . ' FROM #__phocacart_products AS a'
            . I18nHelper::sqlJoin('#__phocacart_products_i18n')
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
            . ' WHERE a.id = ' . (int)$id
            . ' GROUP BY a.id, a.title'
            . ' ORDER BY a.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $product = $db->loadObject();

        return $product;
    }


    // No access rights, publish, stock, etc. Not group contacts, but * - we really need it

    public static function getProductsFull($limitOffset = 0, $limitCount = 1, $orderingItem = 1)
    {

        /*phocacart import('phocacart.ordering.ordering');*/

        $ordering = PhocacartOrdering::getOrderingCombination($orderingItem);
        $db = Factory::getDBO();
        $wheres = array();

        $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        $q = ' SELECT a.*';

        // No Images, Categories, Attributes, Specifications here
        $q .= ', CONCAT_WS(":", t.id, t.alias) AS tax';
        $q .= ', CONCAT_WS(":", m.id, m.alias) AS manufacturer';

        $q .= ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
            . ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'
            . $where;


        if ($ordering != '') {
            $q .= ' ORDER BY ' . $ordering;
        }

        if ((int)$limitCount > 0) {
            $q .= ' LIMIT ' . (int)$limitOffset . ', ' . (int)$limitCount;
        }

        $db->setQuery($q);

        $products = $db->loadAssocList();

        return $products;
    }

    /*
    * checkPublished = true - select only published products
    * checkPublished = false - select all (published|unpublished) products
    * PUBLISHED MEANS, THEY ARE REALLY PUBLISHED - they are published as products and their category is published too.
    *
    * checkStock - check Stock or not ( > 0 )
    * checkPrice - check if the product has price or not ( > 0 )
    */

    public static function getProducts($limitOffset = 0, $limitCount = 1, $orderingItem = 1, $orderingCat = 0, $checkPublished = false, $checkStock = false, $checkPrice = false, $categoriesList = 0, $categoryIds = array(), $featuredOnly = 0, $type = array(0, 1), $queryColumns = '', $return = '', $filterLang = false, $forceLang = '' )
    {


        /*phocacart import('phocacart.ordering.ordering');*/

        $ordering = PhocacartOrdering::getOrderingCombination($orderingItem, $orderingCat);

        $db = Factory::getDBO();
        $wheres = array();
        $user = PhocacartUser::getUser();

        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
        $wheres[] = " a.access IN (" . $userLevels . ")";
        $wheres[] = " c.access IN (" . $userLevels . ")";
        $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
        $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";

        if ($checkPublished) {
            $wheres[] = " a.published = 1";
            $wheres[] = " c.published = 1";
        }

        if (!empty($type) && is_array($type)) {
            $wheres[] = 'c.type IN (' . implode(',', $type) . ')';
        }

        if ($checkStock) {
            $wheres[] = " a.stock > 0";
        }

        if ($checkPrice) {
            $wheres[] = " a.price > 0";
        }

        if (!empty($categoryIds)) {

            $catIdsS = implode(',', $categoryIds);
            $wheres[] = 'pc.category_id IN (' . $catIdsS . ')';
        }

        if ($featuredOnly) {
            $wheres[] = 'a.featured = 1';
        }

        // Additional Hits
        if ($orderingItem == 17 || $orderingItem == 18) {
            $wheres[] = 'ah.product_id = a.id';
            $wheres[] = 'ah.user_id = ' . (int)$user->id;
            $wheres[] = 'ah.user_id > 0';
        }

        // Filter langauge
        if ($forceLang != '' && $forceLang != '*') {
           $wheres[] = ' ' . $db->quoteName('a.language') . ' IN ('.$db->quote($forceLang).')';
        } else if ($filterLang) {
            $lang 		= Factory::getLanguage()->getTag();

            $wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
            $wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
        }

        // Views Plugin can load additional columns
		$additionalColumns = [];
		$pluginOptions = [];
    Dispatcher::dispatch(new Event\View\Products\BeforeLoadColumns('com_phocacart.products', $pluginOptions));

		if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
			if (!empty($pluginOptions['columns'])) {
				foreach ($pluginOptions['columns'] as $v) {
					$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
				}
			}
		}

        $baseColumns = array('a.id', 'a.image', 'a.video', 'a.sku', 'a.ean', 'a.stockstatus_a_id', 'a.stockstatus_n_id', 'a.min_quantity', 'a.min_multiple_quantity', 'a.stock', 'a.unit_amount', 'a.unit_unit', 'a.price', 'a.price_original', 'a.date', 'a.date_update', 'a.sales', 'a.featured', 'a.external_id', 'a.condition', 'a.points_received', 'a.points_needed', 'a.delivery_date', 'a.type', 'a.type_feed', 'a.type_category_feed', 'a.params_feed', 'a.gift_types');


       /* if (I18nHelper::isI18n()) {
			$baseColumns = array_merge($baseColumns, [
				'coalesce(i18n_a.alias, a.alias) as alias', 'coalesce(i18n_a.title, a.title) as title', 'i18n_a.title_long', 'i18n_a.description', 'i18n_a.description_long', 'i18n_a.features', 'i18n_a.metatitle', 'i18n_a.metadesc', 'i18n_a.metakey'
			]);
		} else {
			$baseColumns = array_merge($baseColumns, [
				'a.alias', 'a.title', 'a.title_long', 'a.description', 'a.description_long', 'a.features', 'a.metatitle', 'a.metadesc', 'a.metakey'
			]);
		}*/

        $baseColumns = array_merge($baseColumns, [
            I18nHelper::sqlCoalesce(['title']),
            I18nHelper::sqlCoalesce(['alias']),
            I18nHelper::sqlCoalesce(['title_long']),
            I18nHelper::sqlCoalesce(['description']),
            I18nHelper::sqlCoalesce(['description_long']),
            I18nHelper::sqlCoalesce(['features']),
            I18nHelper::sqlCoalesce(['metatitle']),
            I18nHelper::sqlCoalesce(['metadesc']),
            I18nHelper::sqlCoalesce(['metakey'])
        ]);


		$col = array_merge($baseColumns, $additionalColumns);
		$col = array_unique($col);

        /*
         * type_feed - specific type of products used in XML feed (for example by Google products: g:product_type)
         * type_category_feed - specific type of product category used in XML feed (for example by Google products: g:google_product_category)
         *                    - overrides category type_feed (type feed in category table)
         * type - digital (downloadable) product or physical product
         */
        if ($queryColumns != '') {
            $columns = $queryColumns;
            $groupsFull = $queryColumns;
            $groupsFast = 'a.id';
        } else {
             //if (I18nHelper::isI18n()) {
                $columns = implode(',', $col) . ', c.id AS catid, '.I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat').', c.title_feed AS cattitlefeed, c.type_feed AS cattypefeed, c.params_feed AS params_feed_category,'
                 . ' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received, t.id as taxid, t.tax_rate AS taxrate, t.calculation_type AS taxcalculationtype, '.I18nHelper::sqlCoalesce(['title'], 't', 'tax').', t.tax_hide as taxhide, '.I18nHelper::sqlCoalesce(['title', 'link'], 'm', 'manufacturer').','
                    . ' AVG(r.rating) AS rating,'
                    . ' at.required AS attribute_required';
                $groupsFull = implode(',', $col) . ', c.id, c.title, c.alias, c.title_feed, c.type_feed, ppg.price, pptg.points_received, t.id, t.tax_rate, t.calculation_type, t.title, m.title, r.rating, at.required';
                $groupsFast = 'a.id';
            /*} else {
                $columns = implode(',', $col) . ', c.id AS catid, c.title AS cattitle, c.alias AS catalias, c.title_feed AS cattitlefeed, c.type_feed AS cattypefeed, c.params_feed AS params_feed_category,'
                 . ' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received, t.id as taxid, t.tax_rate AS taxrate, t.calculation_type AS taxcalculationtype, t.title AS taxtitle, t.tax_hide as taxhide, m.title AS manufacturertitle, m.link as manufacturerlink'
                    . ' AVG(r.rating) AS rating,'
                    . ' at.required AS attribute_required';
                $groupsFull = implode(',', $col) . ', c.id, c.title, c.alias, c.title_feed, c.type_feed, ppg.price, pptg.points_received, t.id, t.tax_rate, t.calculation_type, t.title, m.title, r.rating, at.required';
                $groupsFast = 'a.id';
            }*/


        }
        $groups = PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

        $q = ' SELECT ' . $columns;

        if ($categoriesList == 1) {
            $q .= ', GROUP_CONCAT(c.id) AS categories';
        } else if ($categoriesList == 2) {
            $q .= ', GROUP_CONCAT(c.title SEPARATOR "|") AS categories';
        } else if ($categoriesList == 3) {
            $q .= ', GROUP_CONCAT(c.id, ":", c.alias SEPARATOR "|") AS categories';
        } else if ($categoriesList == 4) {
            $q .= ', GROUP_CONCAT(c.id, ":", c.title SEPARATOR "|") AS categories';
        } else if ($categoriesList == 5) {
            // add to 2 type_category_feed - used in XML FEED
            $q .= ', GROUP_CONCAT(c.title SEPARATOR "|") AS categories';
            $q .= ', GROUP_CONCAT(c.type_feed SEPARATOR "|") AS feedcategories';

            // Possible feature but in all known feeds we use only one category
            //$q .= ', GROUP_CONCAT(c.params_feed SEPARATOR "|") AS feedcategoriesparams';
        }

        // Possible DISTINCT
        //$q .= ', GROUP_CONCAT(DISTINCT c.id, ":", c.title SEPARATOR "|") AS categories';


        $q .= ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
            . ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0'
            . ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'
            . ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
            . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
            . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category

            // user is in more groups, select lowest price by best group
            . ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . $userGroups . ') AND type = 3)'
            // user is in more groups, select highest points by best group
            . ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . $userGroups . ') AND type = 3)';

        if (I18nHelper::isI18n()) {
			$q .= I18nHelper::sqlJoin('#__phocacart_products_i18n', 'a');
			$q .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');
            $q .= I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm');
            $q .= I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't');
		}

        // Additional Hits
        if ($orderingItem == 17 || $orderingItem == 18) {
            $q .= ' LEFT JOIN #__phocacart_hits AS ah ON a.id = ah.product_id';
        }

        $q .= ' WHERE ' . implode(' AND ', $wheres)
            . ' GROUP BY ' . $groups;

        if ($ordering != '') {
            $q .= ' ORDER BY ' . $ordering;
        }


        if ((int)$limitCount > 0) {
            $q .= ' LIMIT ' . (int)$limitOffset . ', ' . (int)$limitCount;
        }

        $db->setQuery($q);

        if ($return == 'column') {
            $products = $db->loadColumn();
        } else {
            $products = $db->loadObjectList();
        }

        return $products;
    }

    /*
     * Obsolete
     */
    public static function getCategoryByProductId($id)
    {
        $db = Factory::getDBO();
        $query = 'SELECT a.catid'
            . ' FROM #__phocacart_products AS a'
            . ' WHERE a.id = ' . (int)$id
            . ' ORDER BY a.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $category = $db->loadRow();

        if (isset($category[0]) && $category[0] > 0) {
            return $category[0];
        }
        return 0;
    }

    public static function getCategoriesByProductId($id)
    {
        $db = Factory::getDBO();
        $q = 'SELECT pc.category_id, c.alias, pc.ordering'
            . ' FROM #__phocacart_product_categories AS pc'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' WHERE pc.product_id = ' . (int)$id
            . ' ORDER BY pc.ordering';
        $db->setQuery($q);
        $categories = $db->loadAssocList();

        return $categories;
    }

    public static function getImagesByProductId($id)
    {
        $db = Factory::getDBO();
        $q = 'SELECT pi.image'
            . ' FROM #__phocacart_product_images AS pi'
            . ' WHERE pi.product_id = ' . (int)$id
            . ' ORDER BY pi.id';
        $db->setQuery($q);
        $images = $db->loadAssocList();

        return $images;
    }

    public static function getImageByProductId($id)
    {
        $db = Factory::getDBO();
        $q = 'SELECT p.image'
            . ' FROM #__phocacart_products AS p'
            . ' WHERE p.id = ' . (int)$id
            . ' ORDER BY p.id'
            . ' LIMIT 1';
        $db->setQuery($q);
        $image = $db->loadResult();

        return $image;
    }

    public static function getMostViewedProducts($limit = 5, $checkPublished = false, $checkAccess = false, $count = false, $type = array(0, 1))
    {

        $db = Factory::getDBO();
        $wheres = array();

        if ($checkAccess) {
            $user = PhocacartUser::getUser();
            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
            $wheres[] = " a.access IN (" . $userLevels . ")";
            $wheres[] = " c.access IN (" . $userLevels . ")";
            $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
            $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
        }
        if ($checkPublished) {
            $wheres[] = " a.published = 1";
        }

        if (!empty($type) && is_array($type)) {
            $wheres[] = 'c.type IN (' . implode(',', $type) . ')';
        }

        $wheres[] = " a.hits > 0";
        $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        if ($count) {
            $q = 'SELECT SUM(a.hits)'
                . ' FROM #__phocacart_products AS a'
                . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
                . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';

            if ($checkAccess) {
                $q .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                    . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
            }

            $q .= $where;
            if ((int)$limit > 0) {
                $q .= ' LIMIT ' . (int)$limit;
            }

            $db->setQuery($q);
            $products = $db->loadResult();

        } else {

            $q = 'SELECT a.id, a.title, a.alias, SUM(a.hits) AS hits, GROUP_CONCAT(DISTINCT c.id) as catid, GROUP_CONCAT(DISTINCT c.alias) as catalias, GROUP_CONCAT(DISTINCT c.title) as cattitle, a.catid AS preferred_catid'
                . ' FROM #__phocacart_products AS a'
                . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
                . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
            if ($checkAccess) {
                $q .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                    . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
            }

            $q .= $where
                . ' GROUP BY a.id, a.title, a.alias, a.hits'
                . ' ORDER BY a.hits DESC';
            if ((int)$limit > 0) {
                $q .= ' LIMIT ' . (int)$limit;
            }

            $db->setQuery($q);
            $products = $db->loadObjectList();
        }


        return $products;
    }

    public static function getBestSellingProducts($limit = 5, $dateFrom = '', $dateTo = '', $count = false)
    {

        $db = Factory::getDBO();
        $wheres = array();

        $wheres[] = " o.id > 0";

        if ($dateTo != '' && $dateFrom != '') {
            $dateFrom = $db->Quote($dateFrom);
            $dateTo = $db->Quote($dateTo);
            $wheres[] = ' DATE(od.date) >= ' . $dateFrom . ' AND DATE(od.date) <= ' . $dateTo;
        }

        $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        if ($count) {
            $q = ' SELECT COUNT(o.id)'
                . ' FROM #__phocacart_order_products AS o'
                . ' LEFT JOIN #__phocacart_products AS a ON a.id = o.product_id';
            if ($dateTo != '' && $dateFrom != '') {
                $q .= ' LEFT JOIN #__phocacart_orders AS od ON od.id = o.order_id';
            }
            $q .= $where;
            if ((int)$limit > 0) {
                $q .= ' LIMIT ' . (int)$limit;
            }


            $db->setQuery($q);
            $products = $db->loadResult();

        } else {
            $q = ' SELECT o.product_id AS id, o.title, o.alias, COUNT( o.id ) AS count_products'
                . ' FROM #__phocacart_order_products AS o';
            //. ' LEFT JOIN #__phocacart_products AS a ON a.id = o.product_id';
            if ($dateTo != '' && $dateFrom != '') {
                $q .= ' LEFT JOIN #__phocacart_orders AS od ON od.id = o.order_id';
            }
            $q .= $where
                . ' GROUP BY o.product_id, o.title, o.alias'
                . ' ORDER BY count_products DESC';
            if ((int)$limit > 0) {
                $q .= ' LIMIT ' . (int)$limit;
            }


            $db->setQuery($q);
            $products = $db->loadObjectList();
        }


        /* For now we don't need SEF url, if SEF url is needed, we need to get category alias and category id
         * This cannot be done in sql as then because of table jos_phocacart_product_categories will count count duplicities
         */


        /*
        $productsA = array();
        if (!empty($products)) {
            foreach ($products as $k => $v) {
                if (isset($v->id)) {
                    $productsA[] = (int)$v->id;
                }
            }
        }
        $productsS = '';
        if (!empty($productsA)) {
            $productsS = implode(',', $productsA);
        }

        $categories = array();
        if ($productsS != '') {
            $query = 'SELECT pc.product_id AS id, c.id AS catid, c.title AS cattitle, c.alias AS catalias'
            . ' FROM #__phocacart_categories AS c'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id'
            . ' LEFT JOIN #__phocacart_products AS p ON p.id = pc.product_id'
            . ' WHERE pc.product_id IN ('.$productsS.')'
            . ' GROUP BY pc.product_id';
            $db->setQuery( $query );

            $categories = $db->loadObjectList();

        }
        if (!empty($categories) && !empty($products)) {
            foreach($products as $k => &$v) {
                foreach($categories as $k2 => $v2) {
                    if (isset($v->id) && isset($v2->id) && (int)$v->id > 0 && (int)$v->id == (int)$v2->id) {
                        $v->catid 		= $v2->catid;
                        $v->catalias 	= $v2->catalias;
                        $v->cattitle	= $v2->cattitle;
                    }
                }
            }

        }*/
        return $products;
    }

    /* Used for Export Import
     * Set in layout so users can select columns
    */
    /*
    public static function getProductColumns() {

        $a = array();

        $app			= Factory::getApplication();
        $paramsC 		= PhocacartUtils::getComponentParameters();
        $export_attributes		= $paramsC->get( 'export_attributes', 1 );
        $export_specifications	= $paramsC->get( 'export_specifications', 1 );
        $export_downloads		= $paramsC->get( 'export_downloads', 0 );

        //$a[] = array('catid', 'COM_PHOCACART_FIELD__LABEL');
        // Categories, Images, Attributes, Specifications

        $a[] = array('id', 'JGLOBAL_FIELD_ID_LABEL');
        $a[] = array('title', 'COM_PHOCACART_FIELD_TITLE_LABEL');
        $a[] = array('alias', 'COM_PHOCACART_FIELD_ALIAS_LABEL');


        $a[] = array('sku', 'COM_PHOCACART_FIELD_SKU_LABEL');
        $a[] = array('ean', 'COM_PHOCACART_FIELD_EAN_LABEL');

        $a[] = array('price', 'COM_PHOCACART_FIELD_PRICE_LABEL');
        $a[] = array('price_original', 'COM_PHOCACART_FIELD_ORIGINAL_PRICE_LABEL');

        // TAX***
        //$a[] = array('tax_id', 'COM_PHOCACART_FIELD_TAX_LABEL');
        $a[] = array('tax', 'COM_PHOCACART_FIELD_TAX_LABEL');

        // CATEGORIES (not exist in query)
        $a[] = array('categories', 'COM_PHOCACART_CATEGORIES');

        // MANUFACTURER***
        //$a[] = array('manufacturer_id', 'COM_PHOCACART_FIELD_MANUFACTURER_LABEL');
        $a[] = array('manufacturer', 'COM_PHOCACART_FIELD_MANUFACTURER_LABEL');

        $a[] = array('upc', 'COM_PHOCACART_FIELD_UPC_LABEL');
        $a[] = array('jan', 'COM_PHOCACART_FIELD_JAN_LABEL');
        $a[] = array('isbn', 'COM_PHOCACART_FIELD_ISBN_LABEL');
        $a[] = array('mpn', 'COM_PHOCACART_FIELD_MPN_LABEL');

        $a[] = array('serial_number', 'COM_PHOCACART_FIELD_SERIAL_NUMBER_LABEL');
        $a[] = array('registration_key', 'COM_PHOCACART_FIELD_REGISTRATION_KEY_LABEL');

        $a[] = array('external_id', 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_ID_LABEL');
        $a[] = array('external_key', 'COM_PHOCACART_FIELD_EXTERNAL_PRODUCT_KEY_LABEL');
        $a[] = array('external_link', 'COM_PHOCACART_FIELD_EXTERNAL_LINK_LABEL');
        $a[] = array('external_text', 'COM_PHOCACART_FIELD_EXTERNAL_TEXT_LABEL');

        $a[] = array('access', 'JFIELD_ACCESS_LABEL');
        $a[] = array('featured', 'COM_PHOCACART_FIELD_FEATURED_LABEL');

        $a[] = array('video', 'COM_PHOCACART_FIELD_VIDEO_URL_LABEL');
        $a[] = array('public_download_file', 'COM_PHOCACART_FIELD_PUBLIC_DOWNLOAD_FILE_LABEL');

        $a[] = array('description', 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL');
        $a[] = array('description_long', 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL');

        $a[] = array('image', 'COM_PHOCACART_FIELD_IMAGE_LABEL');

        // IMAGES (not exist in query)
        $a[] = array('images', 'COM_PHOCACART_ADDITIONAL_IMAGES');

        if ($export_attributes == 1) {
            // ATTRIBUTES (not exist in query)
            $a[] = array('attributes', 'COM_PHOCACART_ATTRIBUTES');
        }

        if ($export_specifications == 1) {
            // SPECIFICATIONS (not exist in query)
            $a[] = array('specifications', 'COM_PHOCACART_SPECIFICATIONS');
        }

        // RELATED_PRODUCTS (not exist in query)
        $a[] = array('related', 'COM_PHOCACART_RELATED_PRODUCTS');

        $a[] = array('stock', 'COM_PHOCACART_FIELD_IN_STOCK_LABEL');
        $a[] = array('stockstatus_a_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_A_LABEL');
        $a[] = array('stockstatus_n_id', 'COM_PHOCACART_FIELD_STOCK_STATUS_B_LABEL');
        $a[] = array('min_quantity', 'COM_PHOCACART_FIELD_MIN_ORDER_QUANTITY_LABEL');
        $a[] = array('min_multiple_quantity', 'COM_PHOCACART_FIELD_MIN_MULTIPLE_ORDER_QUANTITY_LABEL');
        //$a[] = array('availability', 'COM_PHOCACART_FIELD_AVAILABILITY_LABEL');

        if ($export_downloads == 1) {
            $a[] = array('download_token', 'COM_PHOCACART_FIELD_DOWNLOAD_TOKEN_LABEL');
            $a[] = array('download_folder', 'COM_PHOCACART_FIELD_DOWNLOAD_FOLDER_LABEL');
            $a[] = array('download_file', 'COM_PHOCACART_FIELD_DOWNLOAD_FILE_LABEL');
            $a[] = array('download_hits', 'COM_PHOCACART_FIELD_DOWNLOAD_HITS_LABEL');
        }

        $a[] = array('length', 'COM_PHOCACART_FIELD_LENGTH_LABEL');
        $a[] = array('width', 'COM_PHOCACART_FIELD_WIDTH_LABEL');
        $a[] = array('height', 'COM_PHOCACART_FIELD_HEIGHT_LABEL');

        //$a[] = array('unit_size', 'COM_PHOCACART_FIELD_UNIT_SIZE_LABEL');
        $a[] = array('weight', 'COM_PHOCACART_FIELD_WEIGHT_LABEL');
        //$a[] = array('unit_weight', 'COM_PHOCACART_FIELD_UNIT_WEIGHT_LABEL');
        $a[] = array('volume', 'COM_PHOCACART_FIELD_VOLUME_LABEL');
        //$a[] = array('unit_volume', 'COM_PHOCACART_FIELD__LABEL');
        $a[] = array('unit_amount', 'COM_PHOCACART_FIELD_UNIT_AMOUNT_LABEL');
        $a[] = array('unit_unit', 'COM_PHOCACART_FIELD_UNIT_UNIT_LABEL');


        $a[] = array('published', 'COM_PHOCACART_FIELD_PUBLISHED_LABEL');
        $a[] = array('language', 'JFIELD_LANGUAGE_LABEL');

        $a[] = array('date', 'COM_PHOCACART_FIELD_DATE_LABEL');

        // TAGS (not exist in query)
        $a[] = array('tags', 'COM_PHOCACART_TAGS');

        $a[] = array('metakey', 'JFIELD_META_KEYWORDS_LABEL');
        $a[] = array('metadesc', 'JFIELD_META_DESCRIPTION_LABEL');


        //$a[] = array('ordering', 'COM_PHOCACART_FIELD_ORDERING_LABEL');

        //$a[] = array('allow_upload', 'COM_PHOCACART_FIELD_ALLOW_UPLOAD_LABEL');
        //$a[] = array('custom_text', 'COM_PHOCACART_FIELD_CUSTOM_TEXT_LABEL');



        //$a[] = array('checked_out', 'COM_PHOCACART_FIELD__LABEL');
        //$a[] = array('checked_out_time', 'COM_PHOCACART_FIELD__LABEL');



        //$a[] = array('hits', 'COM_PHOCACART_FIELD_HITS_LABEL');
        //$a[] = array('sales', 'COM_PHOCACART_FIELD__LABEL');
        //$a[] = array('params', 'COM_PHOCACART_FIELD__LABEL');

        //$a[] = array('metadata', 'COM_PHOCACART_FIELD__LABEL');


        return $a;
    } */

    public static function featured($pks, $value = 0)
    {
        // Sanitize the ids.
        $pks = (array)$pks;
        ArrayHelper::toInteger($pks);
        $app = Factory::getApplication();

        if (empty($pks)) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'), 'message');
            return false;
        }

        //$table = $this->getTable('PhocacartFeatured', 'Table');
        $table = Table::getInstance('PhocacartFeatured', 'Table', array());


        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__phocacart_products'))
                ->set('featured = ' . (int)$value)
                ->where('id IN (' . implode(',', $pks) . ')');
            $db->setQuery($query);
            $db->execute();

            if ((int)$value == 0) {
                // Adjust the mapping table.
                // Clear the existing features settings.
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__phocacart_product_featured'))
                    ->where('product_id IN (' . implode(',', $pks) . ')');
                $db->setQuery($query);
                $db->execute();
            } else {
                // first, we find out which of our new featured articles are already featured.
                $query = $db->getQuery(true)
                    ->select('f.product_id')
                    ->from('#__phocacart_product_featured AS f')
                    ->where('product_id IN (' . implode(',', $pks) . ')');
                //echo $query;
                $db->setQuery($query);

                $old_featured = $db->loadColumn();

                // we diff the arrays to get a list of the articles that are newly featured
                $new_featured = array_diff($pks, $old_featured);

                // Featuring.
                $tuples = array();
                foreach ($new_featured as $pk) {
                    $tuples[] = $pk . ', 0';
                }
                if (count($tuples)) {
                    $db = Factory::getDbo();
                    $columns = array('product_id', 'ordering');
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__phocacart_product_featured'))
                        ->columns($db->quoteName($columns))
                        ->values($tuples);
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        } catch (Exception $e) {

            $app->enqueueMessage($e->getMessage(), 'message');
            return false;
        }

        $table->reorder();

        //$this->cleanCache();

        return true;
    }


    public static function storeProduct($data, $importColumn = 1)
    {
        // Store
        $table = Table::getInstance('PhocaCartItem', 'Table', array());

        $newInsertOldId = 0;
        if ($importColumn == 2) {
            // SKU
            if (isset($data['sku']) && $data['sku'] != '') {
                $found = $table->load(array('sku' => $data['sku']));

                // Such id is found, but we store by SKU - we need to unset it to get new created by autoincrement
                if ($found) {
                    $data['id'] = $table->id;
                } else {
                    // New row
                    //unset($data['id']); store the same ID for importing product if possible
                    // unfortunately this is not possible per standard way

                    // We didn't find the row by SKU, but we have the ID, so we try to update by ID
                    // If we don't find the ID (so no SKU, no ID), insert new row
                    // We try to add current ID (it does not exist), not new autoincrement
                    $found2 = $table->load((int)$data['id']);
                    if (!$found2) {
                        $newInsertOldId = 1;
                    }
                }
            }
        } else {
            // ID
            if (isset($data['id']) && (int)$data['id'] > 0) {
                $found = $table->load((int)$data['id']);


                // Such id not found, we need to unset it to get new created by autoincrement
                if (!$found) {
                    // New row
                    //unset($data['id']);  store the same ID for importing product if possible
                    // unfortunately this is not possible per standard way
                    $newInsertOldId = 1;
                }
            }
        }



        if (!$table->bind($data)) {
            throw new Exception($table->getError());
        }

        if (intval($table->date) == 0) {
            $table->date = Factory::getDate()->toSql();
        }

        if(isset($table->manufacturer_id) && $table->manufacturer_id == '') {$table->manufacturer_id = 0;}
        if(isset($table->type) && $table->type == '') {$table->type = 0;}
        if(isset($table->min_quantity) && $table->min_quantity == '') {$table->min_quantity = 0;}
        if(isset($table->min_multiple_quantity) && $table->min_multiple_quantity == '') {$table->min_multiple_quantity = 0;}
        if(isset($table->min_quantity_calculation) && $table->min_quantity_calculation == '') {$table->min_quantity_calculation = 0;}
        if(isset($table->condition) && $table->condition == '') {$table->condition = 0;}
        if(isset($table->points_received) && $table->points_received == '') {$table->points_received = 0;}
        if(isset($table->points_needed) && $table->points_needed == '') {$table->points_needed = 0;}
        if(isset($table->stock_calculation) && $table->stock_calculation == '') {$table->stock_calculation = 0;}
        if(isset($table->featured) && $table->featured == '') {$table->featured = 0;}
        if(isset($table->tax_id) && $table->tax_id == '') {$table->tax_id = 0;}
        if(isset($table->stock) && $table->stock == '') {$table->stock = 0;}
        if(isset($table->created_by) && $table->created_by == '') {$table->created_by = 0;}
        if(isset($table->modified_by) && $table->modified_by == '') {$table->modified_by = 0;}
        if(isset($table->sales) && $table->sales == '') {$table->sales = 0;}
        if(isset($table->sales) && $table->sales == '') {$table->sales = 0;}

        if (!$table->check()) {
            throw new Exception($table->getError());
        }

        if ($newInsertOldId == 1) {
            // The imported ID does not exist, we need to add new row, but we try to use the same ID
            // even the ID is autoincrement (this is why we use non standard method) because
            // standard method cannot add IDs into autoincrement
            $db = Factory::getDBO();


            if (!$db->insertObject('#__phocacart_products', $table, 'id')) {
                throw new Exception($table->getError());
            }

        } else {
            if (!$table->store()) {
                throw new Exception($table->getError());
            }
        }


        // Test Thumbnails (Create if not exists)
        if ($table->image != '') {
            PhocacartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
        }

        if ((int)$table->id > 0) {

            if (!isset($data['catid_multiple'])) {
                $data['catid_multiple'] = array();
            }
            if (!isset($data['catid_multiple_ordering'])) {
                $data['catid_multiple_ordering'] = array();
            }
            PhocacartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->id, $data['catid_multiple_ordering']);

            if (isset($data['featured'])) {
                PhocacartProduct::featured((int)$table->id, $data['featured']);
            }

            if (!isset($data['advanced_stock_options'])) {
                $advancedStockOptions = '';
            } else {
                $advancedStockOptions = $data['advanced_stock_options'];
                if (is_array($data['advanced_stock_options']) && isset($data['advanced_stock_options'][0])) {
                    $advancedStockOptions = $data['advanced_stock_options'][0];
                }
            }

            if (!isset($data['additional_download_files'])) {
                $additionalDownloadFiles = '';
            } else {
                $additionalDownloadFiles = $data['additional_download_files'];
            }

            if ($data['related'] == '') {
                $data['related'] = [];
            }

            PhocacartRelated::storeRelatedItems((int)$table->id, $data['related']);
            PhocacartImageAdditional::storeImagesByProductId((int)$table->id, $data['images']);
            PhocacartAttribute::storeAttributesById((int)$table->id, $data['attributes']);
            PhocacartAttribute::storeCombinationsById((int)$table->id, $advancedStockOptions);
            PhocacartSpecification::storeSpecificationsById((int)$table->id, $data['specifications']);
            PhocacartDiscountProduct::storeDiscountsById((int)$table->id, $data['discounts']);
            PhocacartFileAdditional::storeProductFilesByProductId((int)$table->id, $additionalDownloadFiles);
            PhocacartTag::storeTags($data['tags'], (int)$table->id);
            PhocacartTag::storeTagLabels($data['taglabels'], (int)$table->id);

            // PARAMETERS
            $parameters = PhocacartParameter::getAllParameters();
            if (!empty($parameters)) {
                foreach ($parameters as $kP => $vP) {
                    if (isset($vP->id) && (int)$vP->id > 0) {
                        $idP = (int)$vP->id;
                        if (!empty($data['items_parameter'][$idP])) {
                            PhocacartParameter::storeParameterValues($data['items_parameter'][$idP], (int)$table->id, $idP);
                        } else {
                            PhocacartParameter::storeParameterValues(array(), (int)$table->id, $idP);
                        }
                    }
                }
            }


            PhocacartGroup::storeProductPriceGroupsById($data['price_groups'], (int)$table->id);
            PhocacartGroup::storeProductPointGroupsById($data['point_groups'], (int)$table->id);
            PhocacartGroup::storeGroupsById((int)$table->id, 3, $data['groups']);

            PhocacartPriceHistory::storePriceHistoryCustomById($data['price_histories'], (int)$table->id);

            PhocacartGroup::updateGroupProductPriceById((int)$table->id, $data['price']);
            PhocacartGroup::updateGroupProductRewardPointsById((int)$table->id, $data['points_received']);

            return $table->id;
        }

        return false;
    }

    public static function getProductKey($id, $attributes = array(), $encode = 1)
    {


        $key = (int)$id . ':';
        if (!empty($attributes)) {


            // Sort attributes (because of right key generation)
            ksort($attributes);
            // Remove empty values, so items with empty values (add to cart item view) is the same
            // like item without any values (add to cart category view)

            foreach ($attributes as $k => $v) {

                // Transform all attribute values to array (when they get string instead of array from html)


                if (!is_array($v)) {
                    $attributes[$k] = array((int)$v => (int)$v);
                }


                // Unset when string is empty or zero
                if ($v == 0 || $v == '') {
                    unset($attributes[$k]);
                }

                // Unset when we have transformed it to array but it is empty
                if (empty($v)) {
                    unset($attributes[$k]);
                }

                if (!empty($v) && is_array($v)) {
                    $attributeType = PhocacartAttribute::getAttributeType((int)$k);

                    foreach ($v as $k2 => $v2) {
                        //$attributes[$k][$k2] = (int)$v2;
                        $optionType = 0;
                        // Ask for optionType but only by GIFT attribute (20) ATTRIBUTETYPE
                        if ($attributeType == 20) {
                            $optionType = PhocacartAttribute::getOptionType((int)$k2);

                        }
                        $attributes[$k][$k2] = PhocaCartAttribute::setAttributeValue((int)$attributeType, $v2, false, false, $optionType);

                        // TEXT or GIFT attributes
                        if ($attributes[$k][$k2] == '') {
                            unset($attributes[$k][$k2]);
                        }
                    }

                    if (empty($attributes[$k])) {
                        unset($attributes[$k]);// if all values removed from attribute, remove the attribute completely
                    }


                }
            }

            // Sort options (because of right key generation)
            foreach ($attributes as $k3 => $v3) {
                if (is_array($v3)) {
                    ksort($attributes[$k3]);
                }
            }


            if (!empty($attributes)) {

                if ($encode == 0) {
                    return serialize($attributes);
                }

                $key .= base64_encode(serialize($attributes));
            }
        }
        $key .= ':';

        return $key;

        /*$key = 'ID:'.(int)$id .'{';

        if (!empty($attributes)) {

            ksort($attributes);
            // Remove empty values, so items with empty values (add to cart item view) is the same
            // like item without any values (add to cart category view)
            foreach($attributes as $k => $v) {
                if ($v == 0 || $v == '') {
                    unset($attributes[$k]);
                }
            }
            foreach($attributes as $k => $v) {
                if (is_array($v)){
                    asort($attributes[$k]);
                }
            }

            foreach($attributes as $k => $v) {
                $key .= 'AID:'.(int)$k . '[';
                if (is_array($v)){
                    foreach($v as $k2 => $v2) {
                        $key .= 'OID:('.(int)$v2 . ')';
                    }
                } else {
                    $key .= 'OID:('.(int)$v.')';
                }
                $key .= ']';
            }


            if (!empty($attributes)) {
                $k .= base64_encode(serialize($attributes));
            }*/
        /*}
        $key .= '}';
        return $key;*/
    }


    public static function getProductPrice($type = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = array(), $ignoreZeroPrice = 0) {

        switch ($type) {

            case 2:
                $select = 'MIN(p.price)';
            break;

            case 1:
            default:
                $select = 'MAX(p.price)';
            break;

        }

        $db = Factory::getDBO();

        $wheres = array();
        $lefts  = array();

        if ($ignoreZeroPrice == 1){
            $wheres[] = 'p.price > 0';
        }

        $wheres[] = ' p.published = 1';

        if ($lang != '' && $lang != '*') {
            $wheres[] = " p.language = " . $db->quote($lang);
        }

        if ($onlyAvailableProducts == 1) {
            $rules = PhocacartProduct::getOnlyAvailableProductRules();
            $wheres = array_merge($wheres, $rules['wheres']);
            $lefts = array_merge($lefts, $rules['lefts']);
        }

        $group = PhocacartUtilsSettings::isFullGroupBy() ? ' GROUP BY p.published' : '';

        if (!empty($filterProducts)) {
            $productIds = implode(',', $filterProducts);
            $wheres[] = 'p.id IN (' . $productIds . ')';
        }

        $q = ' SELECT ' . $select
            . ' FROM  #__phocacart_products AS p'
            . (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
            . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
            . $group
            . ' ORDER BY p.id'
            . ' LIMIT 1';

        /*	// Don't care about access rights to make the query faster
            $q = 'SELECT '.$select
            . ' FROM #__phocacart_products AS p'
            . ' WHERE p.published = 1'
            . ' ORDER BY p.id'
            . ' LIMIT 1';*/

        $db->setQuery($q);
        $price = $db->loadResult();


        return $price;
    }


    public static function getProductCodes($id)
    {

        $db = Factory::getDBO();
        $wheres = array();
        $wheres[] = ' a.id = ' . (int)$id;
        $query = ' SELECT a.sku, a.upc, a.ean, a.jan, a.isbn, a.mpn, a.serial_number'
            . ' FROM #__phocacart_products AS a'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY a.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $productCodes = $db->loadAssoc();

        return $productCodes;
    }

    public static function getOnlyAvailableProductRules()
    {

        $user = PhocacartUser::getUser();
        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
        $type = PhocacartUtilsSettings::getShopType();

        $wheres = array();
        $wheres[] = " p.access IN (" . $userLevels . ")";
        $wheres[] = " c.access IN (" . $userLevels . ")";
        $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
        $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
        $wheres[] = " p.published = 1";
        $wheres[] = " c.published = 1";
        if (!empty($type) && is_array($type)) {
            $wheres[] = ' c.type IN (' . implode(',', $type) . ')';
        }

        $lefts = array();
        $lefts[] = ' #__phocacart_product_categories AS pc ON pc.product_id = p.id';
        $lefts[] = ' #__phocacart_categories AS c ON c.id = pc.category_id';
        $lefts[] = ' #__phocacart_item_groups AS ga ON p.id = ga.item_id AND ga.type = 3';// type 3 is product
        $lefts[] = ' #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category

        $rules = array();
        $rules['wheres'] = $wheres;
        $rules['lefts'] = $lefts;
        return $rules;

    }

    public static function getProductsByCategories($cidA, $limitOffset = 0, $limitCount = 0, $orderingItem = 1) {

        if (!empty($cidA)) {

            $cidS = implode(',', $cidA);

            $ordering = PhocacartOrdering::getOrderingCombination($orderingItem);
            $db = Factory::getDBO();
            $wheres = array();

            $wheres[] = 'c.id IN ('.$cidS.')';

            $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

            $q = ' SELECT a.*, c.id as category_id, c.title as category_title, t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle, t.tax_hide as taxhide';

            // No Images, Categories, Attributes, Specifications here
            $q .= ', CONCAT_WS(":", t.id, t.alias) AS tax';
            $q .= ', CONCAT_WS(":", m.id, m.alias) AS manufacturer';

            $q .= ' FROM #__phocacart_products AS a'
                . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
                . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
                . ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
                . ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'
                . $where;


            if ($ordering != '') {
                $q .= ' ORDER BY c.ordering, ' . $ordering;
            }

            if ((int)$limitCount > 0) {
                $q .= ' LIMIT ' . (int)$limitOffset . ', ' . (int)$limitCount;
            }

            $db->setQuery($q);

            $products = $db->loadAssocList();

            return $products;

        }

    }

    public static function getProductCount()
    {

        $db = Factory::getDBO();
        $query = 'SELECT COUNT(*) FROM #__phocacart_products';
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

}
