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

class PhocacartProduct
{

    private static $productAccess = array();
    private static $productAttributes = array();

    public function __construct() { }

    public static function getProduct($productId, $prioritizeCatid = 0, $type = array(0, 1))
    {

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $wheres = array();
        $params = PhocacartUtils::getComponentParameters();
        $user = PhocacartUser::getUser();
        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));

        $skip = array();

        //$skip['access']	        = $params->get('sql_product_skip_access', 0);
		$skip['group']	        = $params->get('sql_product_skip_group', 0);
		//$skip['attributes']	    = $params->get('sql_product_skip_attributes', 0);
		//$skip['category_type']  = $params->get('sql_product_skip_category_type', 0);
		$skip['tax']   			= $params->get('sql_product_skip_tax', 0);

        // Access is check by checkIfAccessPossible
        //$wheres[] 	= " a.access IN (".$userLevels.")";
        //$wheres[] 	= " c.access IN (".$userLevels.")";
        //$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
        //$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
        //$wheres[] 	= " a.published = 1";
        //$wheres[] 	= " c.published = 1";
        ///$wheres[] 	= ' c.id = '.(int)$catid;
        /// $typeS = implode(',', $type);
        ///if (!$skip['category_type']) {
        ///   $wheres[] = " c.type IN (".$typeS.")";// type: common, onlineshop, pos
        /// }


        $wheres[] = ' i.id = ' . (int)$productId;

        $columns = 'i.id, i.title, i.alias, i.description, i.features, pc.ordering, i.metatitle, i.metadesc, i.metakey, i.metadata, i.image, i.weight, i.height, i.width, i.length, i.min_multiple_quantity, i.min_quantity_calculation, i.volume, i.description, i.description_long, i.price, i.price_original, i.stockstatus_a_id, i.stockstatus_n_id, i.stock_calculation, i.min_quantity, i.min_multiple_quantity, i.stock, i.sales, i.featured, i.external_id, i.unit_amount, i.unit_unit, i.video, i.external_link, i.external_text, i.external_link2, i.external_text2, i.type, i.public_download_file, i.public_download_text, i.public_play_file, i.public_play_text, i.sku AS sku, i.upc AS upc, i.ean AS ean, i.jan AS jan, i.isbn AS isbn, i.mpn AS mpn, i.serial_number, i.points_needed, i.points_received, i.download_file, i.download_token, i.download_folder, i.download_days, i.date, i.date_update, i.delivery_date, c.id AS catid, c.title AS cattitle, c.alias AS catalias, m.id as manufacturerid, m.title as manufacturertitle, m.alias as manufactureralias,';


        if (!$skip['tax']) {
            $columns .= ' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle,';
        } else {
            $columns .= ' NULL as taxid, NULL as taxrate, NULL as taxcalculationtype, NULL as taxtitle,';
        }


        if (!$skip['group']) {
            $columns .= ' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received';
        } else {
            $columns .= ' NULL as group_price, NULL as group_points_received';
        }


        $query = ' SELECT ' . $columns
            . ' FROM #__phocacart_products AS i'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = i.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id';

        if (!$skip['tax']) {
            $query .= ' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id';
        }

        if (!$skip['group']) {
            $query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON i.id = ga.item_id AND ga.type = 3';// type 3 is product
            $query .= ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
            // user is in more groups, select lowest price by best group
            $query .= ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON i.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN (' . $userGroups . ') AND type = 3)';
            // user is in more groups, select highest points by best group
            $query .= ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON i.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN (' . $userGroups . ') AND type = 3)';
        }

        $groupsFull = 'i.id, i.title, i.alias, i.description, i.features, pc.ordering, i.metatitle, i.metadesc, i.metakey, i.metadata, i.image, i.weight, i.height, i.width, i.length, i.min_multiple_quantity, i.min_quantity_calculation, i.volume, i.description, i.description_long, i.price, i.price_original, i.stockstatus_a_id, i.stockstatus_n_id, i.min_quantity, i.stock_calculation, i.min_multiple_quantity, i.stock, i.date, i.date_update, i.delivery_date, i.sales, i.featured, i.external_id, i.unit_amount, i.unit_unit, i.video, i.external_link, i.external_text, i.external_link2, i.external_text2, i.public_download_file, i.public_download_text, i.public_play_file, i.public_play_text, i.sku, i.upc, i.ean, i.jan, i.isbn, i.mpn, i.serial_number, i.points_needed, i.points_received, i.type, i.download_file, i.download_token, i.download_folder, i.download_days, c.id, c.title, c.alias, m.id, m.title, m.alias';

        if (!$skip['tax']) {
            $groupsFull .= ', t.id, t.tax_rate, t.calculation_type, t.title';
        }


        $groupsFast = 'i.id';
        $groups = PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


        $query .= ' WHERE ' . implode(' AND ', $wheres)
            . ' GROUP BY ' . $groups
            . ' ORDER BY i.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $product = $db->loadObject();

        // When we add the product, we can use the catid from where we are located
        // if we are in category A, then we try to add this product with category A
        // BUT the product can be displayed in cart e.g. 3x so only the last added catid
        // is used for creating the SEF URL
        // Using catid is only about SEF URL
        if ((int)$prioritizeCatid > 0) {
            if (isset($product->catid) && (int)$product->catid == (int)$prioritizeCatid) {
                //$product->catid is $product->catid
            } else {
                // Recheck the category id of product
                $checkCategory = false;
                if (isset($product->id)) {
                    $checkCategory = PhocacartProduct::checkIfAccessPossible((int)$product->id, (int)$prioritizeCatid, $type);
                }

                if ($checkCategory) {
                    $product->catid = (int)$prioritizeCatid;
                }
            }
        }


        // Change TAX based on country or region
        if (!empty($product)) {
            $taxChangedA = PhocacartTax::changeTaxBasedOnRule($product->taxid, $product->taxrate, $product->taxcalculationtype, $product->taxtitle);
            $product->taxrate = $taxChangedA['taxrate'];
            $product->taxtitle = $taxChangedA['taxtitle'];
            $product->taxcountryid = $taxChangedA['taxcountryid'];
            $product->taxregionid = $taxChangedA['taxregionid'];
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

                $db = JFactory::getDBO();
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
                    //$app	= JFactory::getApplication();
                    //$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_ATTRIBUTE_REQUIRED'), 'error');
                    //return false;// seems like attribute is required but not selected
                    self::$productAccess[$id][$catid][$typeS] = false;
                }


            } else {
                self::$productAccess[$id][$catid][$typeS] = false;
            }


        }

        return self::$productAccess[$id][$catid][$typeS];

    }


    public static function checkIfProductAttributesOptionsExist($id, $idKey, $catid, $type = array(0, 1), $attribs)
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

                $db 		= JFactory::getDBO();
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
                    //$app	= JFactory::getApplication();
                    //$app->enqueueMessage(JText::_('COM_PHOCACART_PRODUCT_ATTRIBUTE_REQUIRED'), 'error');
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

        $db = JFactory::getDBO();
        $sku = $db->quote($sku);
        $typeSku = $db->quoteName('a.' . $typeSku);


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
        $wheres[] = ' ' . $typeSku . ' = ' . $sku;

        //$wheres[] 	= ' c.type IN ('.implode(',', $type).')';
        if (!empty($type) && is_array($type)) {
            $wheres[] = ' c.type IN (' . implode(',', $type) . ')';
        }


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

        if (!empty($products)) {
            foreach ($products as $k => $v) {
                if (isset($v->id) && (int)$v->id > 0 && isset($v->catid) && (int)$v->catid > 0) {
                    $access = PhocacartProduct::checkIfAccessPossible((int)$v->id, (int)$v->catid, $type);

                    if ($access) {
                        // if found some return the first possible - accessible
                        // because of different rights, groups, etc., we need to know catid
                        return array('id' => (int)$v->id, 'catid' => (int)$v->catid);
                    }
                }
            }
        }
        return false;
    }

    public static function getProductIdByOrder($orderId)
    {

        $db = JFactory::getDBO();
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
        $db = JFactory::getDBO();
        $query = ' SELECT a.id, a.title,'
            . ' group_concat(CONCAT_WS(":", c.id, c.title) SEPARATOR \',\') AS categories,'
            . ' group_concat(c.title SEPARATOR \' \') AS categories_title,'
            . ' group_concat(c.id SEPARATOR \',\') AS categories_id'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
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
        $db = JFactory::getDBO();
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

    public static function getProducts($limitOffset = 0, $limitCount = 1, $orderingItem = 1, $orderingCat = 0, $checkPublished = false, $checkStock = false, $checkPrice = false, $categoriesList = 0, $categoryIds = array(), $featuredOnly = 0, $type = array(0, 1), $queryColumns = '', $return = '')
    {


        /*phocacart import('phocacart.ordering.ordering');*/

        $ordering = PhocacartOrdering::getOrderingCombination($orderingItem, $orderingCat);


        $db = JFactory::getDBO();
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
            $columns = 'a.id, a.title, a.image, a.video, a.alias, a.description, a.description_long, a.sku, a.ean, a.stockstatus_a_id, a.stockstatus_n_id, a.min_quantity, a.min_multiple_quantity, a.stock, a.unit_amount, a.unit_unit, c.id AS catid, c.title AS cattitle, c.alias AS catalias, c.title_feed AS cattitlefeed, c.type_feed AS cattypefeed, a.price, MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received, a.price_original, t.id as taxid, t.tax_rate AS taxrate, t.calculation_type AS taxcalculationtype, t.title AS taxtitle, a.date, a.sales, a.featured, a.external_id, m.title AS manufacturertitle, a.condition, a.points_received, a.points_needed, a.delivery_date, a.type, a.type_feed, a.type_category_feed, a.params_feed,'
                . ' AVG(r.rating) AS rating,'
                . ' at.required AS attribute_required';
            $groupsFull = 'a.id, a.title, a.image, a.video, a.alias, a.description, a.description_long, a.sku, a.ean, a.stockstatus_a_id, a.stockstatus_n_id, a.min_quantity, a.min_multiple_quantity, a.stock, a.unit_amount, a.unit_unit, c.id, c.title, c.alias, c.title_feed, c.type_feed, a.price, ppg.price, pptg.points_received, a.price_original, t.id, t.tax_rate, t.calculation_type, t.title, a.date, a.sales, a.featured, a.external_id, m.title, r.rating, at.required, a.condition, a.points_receieved, a.points_needed, a.delivery_date, a.type, a.type_feed, a.type_category_feed, a.params_feed';
            $groupsFast = 'a.id';
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
        $db = JFactory::getDBO();
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
        $db = JFactory::getDBO();
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
        $db = JFactory::getDBO();
        $q = 'SELECT pi.image'
            . ' FROM #__phocacart_product_images AS pi'
            . ' WHERE pi.product_id = ' . (int)$id
            . ' ORDER BY pi.id';
        $db->setQuery($q);
        $categories = $db->loadAssocList();

        return $categories;
    }

    public static function getMostViewedProducts($limit = 5, $checkPublished = false, $checkAccess = false, $count = false, $type = array(0, 1))
    {

        $db = JFactory::getDBO();
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

            $q = 'SELECT a.id, a.title, a.alias, SUM(a.hits) AS hits, GROUP_CONCAT(DISTINCT c.id) as catid, GROUP_CONCAT(DISTINCT c.alias) as catalias, GROUP_CONCAT(DISTINCT c.title) as cattitle'
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

        $db = JFactory::getDBO();
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

        $app			= JFactory::getApplication();
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
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        $app = JFactory::getApplication();

        if (empty($pks)) {
            $app->enqueueMessage(JText::_('COM_PHOCACART_NO_ITEM_SELECTED'), 'message');
            return false;
        }

        //$table = $this->getTable('PhocacartFeatured', 'Table');
        $table = JTable::getInstance('PhocacartFeatured', 'Table', array());


        try {
            $db = JFactory::getDbo();
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
                    $db = JFactory::getDbo();
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
        $table = JTable::getInstance('PhocaCartItem', 'Table', array());

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
            return false;
        }

        if (intval($table->date) == 0) {
            $table->date = JFactory::getDate()->toSql();
        }


        if (!$table->check()) {
            throw new Exception($table->getError());
            return false;
        }

        if ($newInsertOldId == 1) {
            // The imported ID does not exist, we need to add new row, but we try to use the same ID
            // even the ID is autoincrement (this is why we use non standard method) because
            // standard method cannot add IDs into autoincrement
            $db = JFactory::getDBO();
            if (!$db->insertObject('#__phocacart_products', $table, 'id')) {
                throw new Exception($table->getError());
                return false;
            }

        } else {
            if (!$table->store()) {
                throw new Exception($table->getError());
                return false;

            }
        }


        // Test Thumbnails (Create if not exists)
        if ($table->image != '') {
            $thumb = PhocacartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
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

            $dataRelated = '';
            if (!isset($data['related'])) {
                $dataRelated = '';
            } else {
                $dataRelated = $data['related'];
                if (is_array($data['related']) && isset($data['related'][0])) {
                    $dataRelated = $data['related'][0];
                }
            }

            $advancedStockOptions = '';
            if (!isset($data['advanced_stock_options'])) {
                $advancedStockOptions = '';
            } else {
                $advancedStockOptions = $data['advanced_stock_options'];
                if (is_array($data['advanced_stock_options']) && isset($data['advanced_stock_options'][0])) {
                    $advancedStockOptions = $data['advanced_stock_options'][0];
                }
            }

            $additionalDownloadFiles = '';
            if (!isset($data['additional_download_files'])) {
                $additionalDownloadFiles = '';
            } else {
                $additionalDownloadFiles = $data['additional_download_files'];
            }

            PhocacartRelated::storeRelatedItemsById($dataRelated, (int)$table->id);
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
                        $attributes[$k][$k2] = PhocaCartAttribute::setAttributeValue((int)$attributeType, $v2);

                        // TEXT attributes
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


    public static function getProductPrice($type = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = array())
    {

        switch ($type) {

            case 2:
                $select = 'MIN(p.price)';
            break;

            case 1:
            default:
                $select = 'MAX(p.price)';
            break;

        }

        $db = JFactory::getDBO();

        $wheres = array();
        $lefts = array();

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

        $db = JFactory::getDBO();
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
            $db = JFactory::getDBO();
            $wheres = array();

            $wheres[] = 'c.id IN ('.$cidS.')';

            $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

            $q = ' SELECT a.*, c.id as category_id, c.title as category_title, t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle';

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

}
