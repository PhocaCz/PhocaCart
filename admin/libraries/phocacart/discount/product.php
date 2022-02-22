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

class PhocacartDiscountProduct
{

    private static $product = array();

    private function __construct() { }

    /*
     * ID ... id of product
     */

    public static function getProductDiscountsById($id = 0, $returnArray = 0) {

        if (is_null($id)) {
            throw new Exception('Function Error: No id added', 500);
            return false;
        }

        $id = (int)$id;

        if (!array_key_exists($id, self::$product)) {

            $db         = Factory::getDBO();
            $user       = PhocacartUser::getUser();

            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
            $wheres     = array();
            $wheres[]   = "a.product_id = " . (int)$id;
            $wheres[]   = "a.access IN (" . $userLevels . ")";
            $wheres[]   = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";

            $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

            $query = 'SELECT a.id, a.title, a.alias, a.discount, a.access, a.calculation_type, a.quantity_from, a.valid_from, a.valid_to'
                . ' FROM #__phocacart_product_discounts AS a'
                . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 4'// type 4 is product discount
                . $where
                . ' ORDER BY a.id';
            $db->setQuery($query);

            if ($returnArray) {
                $discounts = $db->loadAssocList();
            } else {
                $discounts = $db->loadObjectList();
            }

            self::$product[$id] = $discounts;
        }
        return self::$product[$id];
    }


    /*
     * $groupQuantitry - Group: Product A Option 1 and Product A Option 2 is ONE PRODUCT
     * $productQuantity - Product: Product A Option 1 and Product A Option 2 are TWO PRODUCTS
     *
     * When we apply discount to one product based on quantity, we need to differentiate
     * if the quantity is based on one product variation - each product variation is single product
     * of if the quantity is based on whole product (group) product count is sum of count of all product variations
     */

    public static function getProductDiscount($id = 0, $groupQuantity = 0, $productQuantity = 0, $params = array()) {

        $app                                  = Factory::getApplication();
        $paramsC                              = PhocacartUtils::getComponentParameters();
        $discount_product_variations_quantity = $paramsC->get('discount_product_variations_quantity', 1);
        $discount_priority                    = $paramsC->get('discount_priority', 1);

        // For some part to display the discount price we ignore quantity rule
        // e.g. because it will be explained in description
        $ignore_quantity_rule = 0;
        if (isset($params['ignore_quantity_rule']) && $params['ignore_quantity_rule'] == 1) {
            $ignore_quantity_rule = 1;
        }

        if ($discount_product_variations_quantity == 0) {
            $quantity = $productQuantity;
        } else {
            $quantity = $groupQuantity;
        }

        $discounts = self::getProductDiscountsById($id, 1);


        if (!empty($discounts)) {
            $bestKey     = 0;// get the discount key which best meet the rules
            $maxDiscount = 0;
            foreach ($discounts as $k => $v) {

                // 1. ACCESS CHECK, GROUP CHECK
                // Checked in SQL

                // 2. VALID DATE FROM TO CHECK
                if (isset($v['valid_from']) && isset($v['valid_to'])) {
                    $valid = PhocacartDate::getActiveDate($v['valid_from'], $v['valid_to']);
                    if ($valid != 1) {

                        unset($discounts[$k]);
                        continue;
                    }
                } else {

                    unset($discounts[$k]);
                    continue;
                }

                // 3. VALID QUANTITY
                if (isset($v['quantity_from'])) {

                    if ((int)$v['quantity_from'] == 0 || (int)$ignore_quantity_rule == 1) {
                        // OK we don't check the quantity as zero means, no quantity limit
                    } else if ((int)$v['quantity_from'] > 0 && (int)$quantity < (int)$v['quantity_from']) {

                        unset($discounts[$k]);
                        continue;
                    }
                } else {

                    unset($discounts[$k]);
                    continue;
                }

                // 4. SELECT THE HIGHEST QUANTITY
                // When more product discounts fullfill the rules, select only one
                // Select the one with heighest quantity, e.g.:
                // minimum quantity = 10 -> discount 5%
                // minimum quantity = 20 -> discount 10%
                // minimum quantity = 30 -> discount 20%
                // If customer buys 50 items, we need to select 20% so both 5% and 10% should be unset
                // But if we have quantity_from == 0, this rule does not have quantity rule, it is first used.
                //4.1 if more discountes meet rule select the one with maxDiscount
                //4.2.if quantity is 0 for all select the largest discount (BUT be aware because of possible conflict)

                if ($discount_priority == 2) {
                    if ((int)$v['quantity_from'] == 0) {
                        $maxDiscount = (int)$v['quantity_from'];
                        $bestKey     = $k;
                    } else if (isset($v['quantity_from']) && (int)$v['quantity_from'] > $maxDiscount) {
                        $maxDiscount = (int)$v['quantity_from'];
                        $bestKey     = $k;
                    }
                } else {
                    if ((int)$v['discount'] == 0) {
                        $maxDiscount = (int)$v['discount'];
                        $bestKey     = $k;
                    } else if (isset($v['discount']) && (int)$v['discount'] > $maxDiscount) {
                        $maxDiscount = (int)$v['discount'];
                        $bestKey     = $k;
                    }
                }
            }

            // POSSIBLE CONFLICT discount vs. quantity - solved by parameter
            // POSSIBLE CONFLICT percentage vs. fixed amount


            if (isset($discounts[$bestKey])) {

                return $discounts[$bestKey];
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /*
     * Display the discount price in category, items or product view
     */
    public static function getProductDiscountPrice($productId, &$priceItems, $params = array()) {


        $paramsC                            = PhocacartUtils::getComponentParameters();
        $display_discount_product_views         = $paramsC->get('display_discount_product_views', 0);// 0 ... disabled, 1 ... enabled, 2 ... enabled - quantity rule is ignored

        if (isset($params['ignore_view_rule']) && $params['ignore_view_rule'] == 1) {
           // Different modules like countdown module
        } else if ($display_discount_product_views == 0) {
            return false;
        }

        if ($display_discount_product_views == 2) {
            $params['ignore_quantity_rule'] = 1;
        }

        $discount = self::getProductDiscount($productId, 1, 1, $params);


        if (isset($discount['discount']) && isset($discount['calculation_type'])) {

            $price                   = new PhocacartPrice();
            $priceItems['bruttotxt'] = $discount['title'];
            $priceItems['nettotxt']  = $discount['title'];
            $quantity                = 1;      //Quantity for displaying the price in items,category and product view is always 1
            $total                   = array();// not used in product view

            if ($discount['calculation_type'] == 0) {
                // FIXED AMOUNT
                if (isset($priceItems['netto']) && $priceItems['netto'] > 0) {
                    $r = $discount['discount'] * 100 / $priceItems['netto'];
                } else {
                    $r = 0;
                }
                // The function works with ratio, so we need to recalculate fixed amount to ratio even in this case
                // the amount will be not divided into more items like it is in checkout
                // so only because of compatibility to the function used in checkout we use ratio instead of fixed amount
                PhocacartCalculation::calculateDiscountFixedAmount($r, $quantity, $priceItems, $total);

            } else {
                // PERCENTAGE

                PhocacartCalculation::calculateDiscountPercentage($discount['discount'], $quantity, $priceItems, $total);

            }

            PhocacartCalculation::correctItemsIfNull($priceItems);
            PhocacartCalculation::formatItems($priceItems);
            return true;
        }

        return false;
    }


    /* Module - get all products with discounts */
    public static function getProductsDiscounts($params = array()) {

        $db         = Factory::getDBO();
        $user       = PhocacartUser::getUser();
        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));

        $wheres = array();
        //$wheres[]   = "a.product_id = " . (int)$id;
        $wheres[] = " a.access IN (" . $userLevels . ")";// ACCESS Discount
        $wheres[] = " p.access IN (" . $userLevels . ")";// ACCESS Product
        $wheres[] = " c.access IN (" . $userLevels . ")";// ACCESS Category

        $wheres[] = "p.published = 1";// PUBLISHED Product
        $wheres[] = "c.published = 1";// PUBLISHED Category

        $wheres[] = " (gp.group_id IN (" . $userGroups . ") OR gp.group_id IS NULL)";
        $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
        $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";// GROUP

        // STOCK
        if (isset($params['stock_check']) && $params['stock_check'] == 1) {
            $wheres[] = " p.stock > 0";
        }

        // LIMIT
        $limit = 1;
        if (isset($params['limit'])) {
            if((int)$params['limit'] == 0) {
                return false;
            } else if ((int)$params['limit'] > 0) {
                $limit = (int)$params['limit'];
            }
        }


        $currentDate = PhocacartDate::getCurrentDate();
        $wheres[]    = " ((a.valid_from <= '" . $currentDate . "' AND a.valid_to >= '" . $currentDate . "'))";

        $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        $query = 'SELECT a.id AS discount_id, a.product_id AS discount_product_id, a.title AS discount_title, a.alias AS discount_alias,'
            . ' a.discount AS discount_discount, a.access AS discount_access, a.calculation_type AS discount_calculation_type,'
            . ' a.quantity_from AS discount_quantity_from, a.valid_from AS discount_valid_from, a.valid_to AS discount_valid_to,'
            . ' a.background_image, a.description AS discount_description,'
            . ' p.id, p.title, p.alias, p.description, p.image, p.price, p.unit_amount,'
            . ' p.unit_unit, p.stock, p.stockstatus_a_id, p.stockstatus_n_id,'
            . ' c.id AS category_id, c.title AS category_title, c.alias AS category_alias,'
            . ' t.id as tax_id, t.tax_rate AS tax_rate, t.calculation_type AS tax_calculationtype, t.title AS tax_title,'
            . ' MIN(ppg.price) as group_price'
            . ' FROM #__phocacart_product_discounts AS a'
            . ' LEFT JOIN #__phocacart_products AS p ON a.product_id = p.id'
           // . ' FROM #__phocacart_products AS p'
          //  . ' LEFT JOIN #__phocacart_product_discounts AS a ON p.id = a.product_id'

            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = p.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_taxes AS t ON t.id = p.tax_id'
            . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 4'// type 4 is product discount
            . ' LEFT JOIN #__phocacart_item_groups AS gp ON p.id = gp.item_id AND gp.type = 3'// type 3 is product
            . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category

            // user is in more groups, select lowest price by best group
            . ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON p.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = p.id AND group_id IN (' . $userGroups . ') AND type = 3)'
            // user is in more groups, select highest points by best group
            //. ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.product_id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.product_id AND group_id IN (' . $userGroups . ') AND type = 3)'

            . $where
            . ' GROUP BY a.id'
            . ' ORDER BY a.id'
            . ' LIMIT ' . (int)$limit;
        $db->setQuery($query);


        $items = $db->loadObjectList();


        if (!empty($items) && isset($items[0]->discount_id) && (int)$items[0]->discount_id > 0) {
            return $items;
        }
        return false;
    }

    /* Module - get all products with featured */
    public static function getProductsFeatured($params = array()) {

        $db         = Factory::getDBO();
        $user       = PhocacartUser::getUser();
        $userLevels = implode(',', $user->getAuthorisedViewLevels());
        $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));

        $wheres = array();
        //$wheres[]   = "a.product_id = " . (int)$id;
       // $wheres[] = " a.access IN (" . $userLevels . ")";// ACCESS Discount
        $wheres[] = " p.access IN (" . $userLevels . ")";// ACCESS Product
        $wheres[] = " c.access IN (" . $userLevels . ")";// ACCESS Category

        $wheres[] = "p.published = 1";// PUBLISHED Product
        $wheres[] = "c.published = 1";// PUBLISHED Category

        $wheres[] = " (gp.group_id IN (" . $userGroups . ") OR gp.group_id IS NULL)";
        $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
       // $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";// GROUP

        // STOCK
        if (isset($params['stock_check']) && $params['stock_check'] == 1) {
            $wheres[] = " p.stock > 0";
        }

        // LIMIT
        $limit = 1;
        if (isset($params['limit'])) {
            if((int)$params['limit'] == 0) {
                return false;
            } else if ((int)$params['limit'] > 0) {
                $limit = (int)$params['limit'];
            }
        }

        $wheres[]    = " p.featured = 1";

        $where = (count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '');

        $query = 'SELECT p.id, p.description, p.title, p.alias, p.image, p.price, p.unit_amount,'
            . ' p.unit_unit, p.stock, p.stockstatus_a_id, p.stockstatus_n_id, p.featured_background_image AS background_image,'
            . ' c.id AS category_id, c.title AS category_title, c.alias AS category_alias,'
            . ' t.id as tax_id, t.tax_rate AS tax_rate, t.calculation_type AS tax_calculationtype, t.title AS tax_title,'
            . ' MIN(ppg.price) as group_price'
            . ' FROM #__phocacart_products AS p'

           // . ' FROM #__phocacart_products AS p'
          //  . ' LEFT JOIN #__phocacart_product_discounts AS a ON p.id = a.product_id'

            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = p.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
            . ' LEFT JOIN #__phocacart_taxes AS t ON t.id = p.tax_id'
            //. ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 4'// type 4 is product discount
            . ' LEFT JOIN #__phocacart_item_groups AS gp ON p.id = gp.item_id AND gp.type = 3'// type 3 is product
            . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category

            // user is in more groups, select lowest price by best group
            . ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON p.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = p.id AND group_id IN (' . $userGroups . ') AND type = 3)'
            // user is in more groups, select highest points by best group
            //. ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.product_id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.product_id AND group_id IN (' . $userGroups . ') AND type = 3)'

            . $where
            . ' GROUP BY p.id'
            . ' ORDER BY p.id'
            . ' LIMIT ' . (int)$limit;
        $db->setQuery($query);


        $items = $db->loadObjectList();


        if (!empty($items) && isset($items[0]->id) && (int)$items[0]->id > 0) {
            return $items;
        }
        return false;
    }

    /*
     * Administration
     */

    public static function getDiscountsById($productId, $return = 0) {

        $db = Factory::getDBO();

        $query = 'SELECT a.id, a.title, a.alias, a.discount, a.access, a.discount, a.calculation_type, a.quantity_from, a.valid_from, a.valid_to, a.description, a.background_image'
            . ' FROM #__phocacart_product_discounts AS a'
            . ' WHERE a.product_id = ' . (int)$productId
            . ' ORDER BY a.ordering';
        $db->setQuery($query);

        if ($return == 0) {
            return $db->loadObjectList();
        } else if ($return == 1) {
            return $db->loadAssocList();
        } else {
            $discounts        = $db->loadAssocList();
            $discountsSubform = array();
            $i                = 0;
            if (!empty($discounts)) {
                foreach ($discounts as $k => $v) {
                    $discountsSubform['discounts' . $i]['id']               = (int)$v['id'];
                    $discountsSubform['discounts' . $i]['title']            = (string)$v['title'];
                    $discountsSubform['discounts' . $i]['alias']            = (string)$v['alias'];
                    $discountsSubform['discounts' . $i]['access']           = (int)$v['access'];
                    $discountsSubform['discounts' . $i]['discount']         = (string)$v['discount'];
                    $discountsSubform['discounts' . $i]['calculation_type'] = (int)$v['calculation_type'];
                    $discountsSubform['discounts' . $i]['quantity_from']    = (int)$v['quantity_from'];
                    //$discountsSubform['discounts' . $i]['quantity_to']    = (int)$v['quantity_to'];
                    $discountsSubform['discounts' . $i]['valid_from']       = $v['valid_from'];
                    $discountsSubform['discounts' . $i]['valid_to']         = $v['valid_to'];
                    $discountsSubform['discounts' . $i]['description']      = $v['description'];
                    $discountsSubform['discounts' . $i]['background_image'] = $v['background_image'];

                    if ((int)$v['id'] > 0) {
                        $discountsSubform['discounts' . $i]['groups'] = PhocacartGroup::getGroupsById((int)$v['id'], 4, 1);
                    }

                    if (empty($discountsSubform['discounts' . $i]['groups'])) {
                        $discountsSubform['discounts' . $i]['groups'] = PhocacartGroup::getDefaultGroup(1);
                    }


                    $i++;
                }
            }
            return $discountsSubform;
        }
        return false;
    }

    /* $new = 1 When we copy a product, we create new one and we need to create new items for this product
    */

    public static function storeDiscountsById($productId, $discsArray, $new = 0) {

        if ((int)$productId > 0) {
            $db = Factory::getDBO();

            /*$query = ' DELETE '
                    .' FROM #__phocacart_product_discounts'
                    . ' WHERE product_id = '. (int)$productId;
            $db->setQuery($query);
            $db->execute();*/

            $notDeleteDiscs = array();

            if (!empty($discsArray)) {
                $values = array();
                $i      = 1;
                foreach ($discsArray as $k => $v) {

                    // Don't store empty discounts
                    /*if ($v['title'] == '') {
                        continue;
                    }*/

                    if (empty($v['alias'])) {
                        $v['alias'] = $v['title'];
                    }
                    $v['alias'] = PhocacartUtils::getAliasName($v['alias']);


                    // correct simple xml
                    if (empty($v['title'])) {
                        $v['title'] = '';
                    }
                    if (empty($v['alias'])) {
                        $v['alias'] = '';
                    }
                    if (empty($v['access'])) {
                        $v['access'] = '';
                    }
                    if (empty($v['discount'])) {
                        $v['discount'] = '';
                    }
                    if (empty($v['calculation_type'])) {
                        $v['calculation_type'] = '';
                    }
                    if (empty($v['quantity_from'])) {
                        $v['quantity_from'] = '';
                    }
                    if (empty($v['quantity_to'])) {
                        $v['quantity_to'] = '';
                    }
                    if (empty($v['valid_from'])) {
                        $v['valid_from'] = '0000-00-00';
                    }
                    if (empty($v['valid_to'])) {
                        $v['valid_to'] = '0000-00-00';
                    }

                    // Valid to - day including the last second
                    if ($v['valid_to'] == '0000-00-00' || $v['valid_to'] == '0000-00-00 00:00:00') {

                    } else {
                        $v['valid_to'] = str_replace('00:00:00', '23:59:59', Factory::getDate($v['valid_to'])->toSql());
                    }


                    if (empty($v['groups'])) {
                        $v['groups'] = array();
                    }

                    if (empty($v['description'])) {
                        $v['description'] = '';
                    }

                    if (empty($v['background_image'])) {
                        $v['background_image'] = '';
                    }


                    if ($v['discount'] == '') {
                        continue;
                    }

                    $idExists = 0;

                    if ($new == 0) {
                        if (isset($v['id']) && $v['id'] > 0) {

                            // Does the row exist
                            $query = ' SELECT id '
                                . ' FROM #__phocacart_product_discounts'
                                . ' WHERE id = ' . (int)$v['id']
                                . ' ORDER BY id';
                            $db->setQuery($query);
                            $idExists = $db->loadResult();

                        }
                    }

                    if ((int)$idExists > 0) {

                        $v['discount'] = PhocacartUtils::replaceCommaWithPoint($v['discount']);


                        $query = 'UPDATE #__phocacart_product_discounts SET'
                            . ' product_id = ' . (int)$productId . ','
                            . ' title = ' . $db->quote($v['title']) . ','
                            . ' alias = ' . $db->quote($v['alias']) . ','
                            . ' access = ' . (int)$v['access'] . ','
                            . ' discount = ' . $db->quote($v['discount']) . ','
                            . ' calculation_type = ' . (int)$v['calculation_type'] . ','
                            . ' quantity_from = ' . (int)$v['quantity_from'] . ','
                            . ' quantity_to = ' . (int)$v['quantity_to'] . ','
                            . ' valid_from = ' . $db->quote($v['valid_from']) . ','
                            . ' valid_to = ' . $db->quote($v['valid_to']) . ','
                            . ' description = ' . $db->quote($v['description']) . ','
                            . ' background_image = ' . $db->quote($v['background_image']) . ','
                            . ' ordering = ' . (int)$i
                            . ' WHERE id = ' . (int)$idExists;
                        $db->setQuery($query);
                        $db->execute();
                        $i++;
                        $newIdD = $idExists;

                    } else {

                        $v['discount'] = PhocacartUtils::replaceCommaWithPoint($v['discount']);

                        $values = '(' . (int)$productId . ', ' . $db->quote($v['title']) . ', ' . $db->quote($v['alias']) . ', ' . (int)$v['access'] . ', ' . $db->quote($v['discount']) . ', ' . (int)$v['calculation_type'] . ', ' . (int)$v['quantity_from'] . ', ' . (int)$v['quantity_to'] . ', ' . $db->quote($v['valid_from']) . ', ' . $db->quote($v['valid_to']) .', ' . $db->quote($v['description']). ', ' . $db->quote($v['background_image']) . ', ' . (int)$i . ')';


                        $query = ' INSERT INTO #__phocacart_product_discounts (product_id, title, alias, access, discount, calculation_type, quantity_from, quantity_to, valid_from, valid_to, description, background_image, ordering)'
                            . ' VALUES ' . $values;
                        $db->setQuery($query);
                        $db->execute();
                        $i++;
                        $newIdD = $db->insertid();
                    }


                    PhocacartGroup::storeGroupsById((int)$newIdD, 4, $v['groups'], $productId);


                    $notDeleteDiscs[] = $newIdD;
                }
            }

            // Remove all discounts except the active
            if (!empty($notDeleteDiscs)) {
                $notDeleteDiscsString = implode(',', $notDeleteDiscs);
                $query                = ' DELETE '
                    . ' FROM #__phocacart_product_discounts'
                    . ' WHERE product_id = ' . (int)$productId
                    . ' AND id NOT IN (' . $notDeleteDiscsString . ')';

                $query2 = 'DELETE FROM #__phocacart_item_groups'
                    . ' WHERE item_id NOT IN ( ' . $notDeleteDiscsString . ' )'
                    . ' AND product_id = ' . (int)$productId
                    . ' AND type = 4';


            } else {
                $query = ' DELETE '
                    . ' FROM #__phocacart_product_discounts'
                    . ' WHERE product_id = ' . (int)$productId;

                $query2 = 'DELETE FROM #__phocacart_item_groups'
                    . ' WHERE product_id = ' . (int)$productId
                    . ' AND type = 4';
            }
            $db->setQuery($query);
            $db->execute();

            $db->setQuery($query2);
            $db->execute();


        }
    }

    /*
    public static function storeDiscountsById($productId, $discsArray) {

        if ((int)$productId > 0) {
            $db =Factory::getDBO();

            $query = ' DELETE '
                    .' FROM #__phocacart_product_discounts'
                    . ' WHERE product_id = '. (int)$productId;
            $db->setQuery($query);
            $db->execute();

            if (!empty($discsArray)) {
                $values 	= array();
                foreach($discsArray as $k => $v) {

                    // Don't store empty discounts
                    /*if ($v['title'] == '') {
                        continue;
                    }*//*

					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);



					// correct simple xml
					if (empty($v['title'])) 			{$v['title'] 			= '';}
					if (empty($v['alias'])) 			{$v['alias'] 			= '';}
					if (empty($v['access'])) 			{$v['access'] 			= '';}
					if (empty($v['discount'])) 			{$v['discount'] 		= '';}
					if (empty($v['calculation_type'])) 	{$v['calculation_type'] = '';}
					if (empty($v['quantity_from'])) 	{$v['quantity_from'] 	= '';}
					if (empty($v['quantity_to'])) 		{$v['quantity_to'] 		= '';}
					if (empty($v['valid_from'])) 		{$v['valid_from'] 		= '';}
					if (empty($v['valid_to'])) 			{$v['valid_to'] 		= '';}

					if ($v['discount'] == '') {
						continue;
					}

					$values[] 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.(int)$v['access'].', '.$db->quote($v['discount']).', '.(int)$v['calculation_type'].', '.(int)$v['quantity_from'].', '.(int)$v['quantity_to'].', '.$db->quote($v['valid_from']).', '.$db->quote($v['valid_to']).', '.(int)$k.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);
					$query = ' INSERT INTO #__phocacart_product_discounts (product_id, title, alias, access, discount, calculation_type, quantity_from, quantity_to, valid_from, valid_to, ordering)'
							.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	*/


    public final function __clone() {
        throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
        return false;
    }
}
