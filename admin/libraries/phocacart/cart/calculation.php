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

class PhocacartCartCalculation
{

    public $correctsubtotal = 1;// because of rounding 0.25 -> 0.3 or 0.2 it can come to difference e.g. 0.01
    public $posbruttocalculation = 1;
    protected $type = array(0, 1);

    public function __construct() {

        $app     = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        // Affect only calculation in POS cart
        // Not receipts, invoices
        // Display or hide brutto prices in POS cart
        $this->posbruttocalculation = PhocacartPos::isPos() ? $paramsC->get('pos_brutto_calculation', 1) : 0;
    }

    public function setType($type = array(0, 1)) {
        $this->type = $type;
    }


    // ==============
    // BASIC PRODUCT
    // ==============
    public function calculateBasicProducts(&$fullItems, &$fullItemsGroup, &$total, &$stock, &$minqty, &$minmultipleqty, $items) {

        $app             = Factory::getApplication();
        $paramsC         = PhocacartUtils::getComponentParameters();
        $tax_calculation = $paramsC->get('tax_calculation', 0);
        // Moved to product parameters
        //$min_ quantity_calculation	= $paramsC->get( 'min_ quantity_calculation', 0 );
        //$stock_ calculation	= $paramsC->get( 'stock_ calculation', 0 );


        $price = new PhocacartPrice();

        $total['netto']           = 0;
        $total['brutto']          = 0;
        $total['brutto_currency'] = 0;
        $total['tax']             = array();
        $total['weight']          = 0;
        $total['volume']          = 0;
        $total['dnetto']          = 0;
        $total['quantity']        = 0;

        $total['length'] = 0;
        $total['width']  = 0;
        $total['height'] = 0;

        $total['points_needed']   = 0;
        $total['points_received'] = 0;

        // Free shipping or payment
        $total['free_shipping'] = 0;
        $total['free_payment']  = 0;

        // Discount fixed amount
        $total['discountcartfixedamount'] = array();
        $total['couponcartfixedamount']   = array();
        $total['discountcarttxtsuffix']   = '';
        $total['couponcarttxtsuffix']     = '';

        $total['rewardproductusedtotal'] = '';
        $total['rewardproducttxtsuffix'] = '';

        $total['countallproducts']           = 0;
        $total['countphysicalproducts']      = 0;
        $total['countdigitalproducts']       = 0;
        $total['countpriceondemandproducts'] = 0;

        // OPTIONS (VARIANTS) QUANTITY
        // The same option can be in different items
        $optionsQuantity = array();

        // Rounding
        $total['rounding']          = 0;
        $total['rounding_currency'] = 0;

        $total['wdnetto']           = 0;//Subtotal after all discounts
        $total['rounding_currency'] = 0;

        foreach ($items as $k => $v) {

            $item   = explode(':', $k);
            $itemId = $item[0];


            // Define
            $fullItems[$k]['id']    = (int)$itemId;
            $fullItems[$k]['idkey'] = (string)$k;

            $fullItems[$k]['netto']  = 0;
            $fullItems[$k]['brutto'] = 0;
            $fullItems[$k]['tax']    = 0;
            $fullItems[$k]['final']  = 0;// netto or brutto * quantity


            $fullItems[$k]['nettodiscount']  = 0;
            $fullItems[$k]['bruttodiscount'] = 0;
            $fullItems[$k]['taxdiscount']    = 0;
            $fullItems[$k]['finaldiscount']  = 0;

            $fullItems[$k]['taxid']    = 0;
            $fullItems[$k]['taxkey']   = '';// Tax Id: Country Tax Id: Region Tax Id
            $fullItems[$k]['taxtitle'] = '';
            $fullItems[$k]['weight']   = '';
            $fullItems[$k]['volume']   = '';
            $fullItems[$k]['quantity'] = $fQ = (int)$v['quantity'];// Quantity of product - one product

            $fullItems[$k]['catid']               = 0;
            $fullItems[$k]['alias']               = '';
            $fullItems[$k]['sku']                 = '';
            $fullItems[$k]['image']               = '';
            $fullItems[$k]['title']               = '';
            $fullItems[$k]['stock']               = 0; // database value set in product settings
            $fullItems[$k]['stockvalid']          = 1; // variable to inform if stock validity is ok
            $fullItems[$k]['stockcalculation']    = 0;
            $fullItems[$k]['minqty']              = 0; // database value set in product settings
            $fullItems[$k]['minmultipleqty']      = 0;
            $fullItems[$k]['minqtyvalid']         = 1; // variable to inform if minimum order is ok
            $fullItems[$k]['minmultipleqtyvalid'] = 1;

            // DISCOUNTS (Product, Cart, Voucher) / Fixed amount / Percentage
            $fullItems[$k]['discountproduct']      = 0;
            $fullItems[$k]['discountproducttitle'] = '';
            $fullItems[$k]['discountcart']         = 0;
            $fullItems[$k]['discountcarttitle']    = '';
            $fullItems[$k]['discountcartfixedid']  = 0;
            $fullItems[$k]['discountcartid']       = 0;


            $fullItems[$k]['couponcart']        = 0;
            $fullItems[$k]['couponcarttitle']   = '';
            $fullItems[$k]['couponcartfixedid'] = 0;
            $fullItems[$k]['couponcartid']      = 0;

            $fullItems[$k]['rewardproduct']          = 0;
            $fullItems[$k]['rewardproducttitle']     = Text::_('COM_PHOCACART_REWARD_POINTS');
            $fullItems[$k]['rewardproductpoints']    = 0;
            $fullItems[$k]['rewardproducttxtsuffix'] = '';
            $fullItems[$k]['points_needed']          = 0;
            $fullItems[$k]['points_received']        = 0;
            $fullItems[$k]['type']                    = 0;
            $fullItems[$k]['owner_id']                = null;
            $fullItems[$k]['owner_name']              = null;
            $fullItems[$k]['owner_ordering']          = null;


            // GROUP QUANTITY
            // Get quantity of a group. Group is sum of all product variations
            // - explained in PhocacartDiscountProduct::getProductDiscount


            $fullItemsGroup[$itemId]['id']    = (int)$itemId;
            $fullItemsGroup[$itemId]['title'] = '';
            if (isset($fullItemsGroup[$itemId]['quantity'])) {
                $fullItemsGroup[$itemId]['quantity'] += (int)$v['quantity'];
            } else {
                $fullItemsGroup[$itemId]['quantity'] = (int)$v['quantity'];

            }

            $total['quantity'] += (int)$v['quantity'];

            // ATTRIBUTES
            $attribs = array();
            if (!empty($item[1])) {
                $attribs = unserialize(base64_decode($item[1]));
            }

            // ITEM D - product info from database
            $itemD = PhocacartProduct::getProduct((int)$itemId, (int)$v['catid'], $this->type);

            // Correct the tax rate - no tax calculation, no tax rate for each product
            if (!empty($itemD) && $tax_calculation == 0) {
                $itemD->taxrate = 0;
            }

            if (isset($itemD->id) && (int)$itemD->id > 0) {
                $fullItems[$k]['title'] = $itemD->title;
                $fullItems[$k]['catid'] = $itemD->catid;
                $fullItems[$k]['alias'] = $itemD->alias;
                $fullItems[$k]['sku']   = $itemD->sku;
                $fullItems[$k]['image'] = $itemD->image;
                $fullItems[$k]['type']  = $itemD->type;
                $fullItems[$k]['owner_id'] = $itemD->owner_id;
                if ($itemD->owner_id) {
                    if ($vendor = PhocacartVendor::getVendor($itemD->owner_id)) {
                        $fullItems[$k]['owner_name'] = $vendor->title;
                        $fullItems[$k]['owner_ordering'] = $vendor->ordering;
                    }
                }


                $fullItems[$k]['default_price'] = $itemD->price;

                $fullItems[$k]['price']             = $price->getPriceItem($itemD->price, $itemD->group_price, 0);
                $fullItems[$k]['taxid']             = $itemD->taxid;
                $fullItems[$k]['taxrate']           = $itemD->taxrate;
                $fullItems[$k]['taxtitle']          = Text::_($itemD->taxtitle);
                $fullItems[$k]['taxcountryid']      = $itemD->taxcountryid;
                $fullItems[$k]['taxregionid']       = $itemD->taxregionid;
                $fullItems[$k]['taxpluginid']       = $itemD->taxpluginid;
                $taxKey                             = PhocacartTax::getTaxKey($itemD->taxid, $itemD->taxcountryid, $itemD->taxregionid, $itemD->taxpluginid);
                $fullItems[$k]['taxkey']            = $taxKey;
                $fullItems[$k]['taxcalctype']       = $itemD->taxcalculationtype;
                $fullItems[$k]['weight']            = $itemD->weight;
                $fullItems[$k]['volume']            = $itemD->volume;
                $fullItems[$k]['stock']             = $itemD->stock;
                $fullItems[$k]['stockadvanced']     = 0;
                $fullItems[$k]['stockcalculation']  = $itemD->stock_calculation;
                $fullItems[$k]['minqty']            = $itemD->min_quantity;
                $fullItems[$k]['minmultipleqty']    = $itemD->min_multiple_quantity;
                $fullItems[$k]['minqtycalculation'] = $itemD->min_quantity_calculation;

                $fullItems[$k]['default_points_received'] = $itemD->points_received;
                $pointsN                                  = PhocacartReward::getPoints($itemD->points_needed, 'needed');
                $pointsR                                  = PhocacartReward::getPoints($itemD->points_received, 'received', $itemD->group_points_received);
                $fullItems[$k]['points_needed']           = $pointsN;
                $fullItems[$k]['points_received']         = $pointsR;

                // Group
                $fullItemsGroup[$itemId]['minqty']              = $itemD->min_quantity;
                $fullItemsGroup[$itemId]['minmultipleqty']      = $itemD->min_multiple_quantity;
                $fullItemsGroup[$itemId]['title']               = $itemD->title;
                $fullItemsGroup[$itemId]['minqtyvalid']         = 1;
                $fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;


                $priceI = $price->getPriceItems($itemD->price, $itemD->taxid, $itemD->taxrate, $itemD->taxcalculationtype, $itemD->taxtitle, 0, '', 0, 1, $itemD->group_price, $itemD->taxhide);


                // Get price from advanced stock managment TO DO group price
                if ($fullItems[$k]['stockcalculation'] == 3) {
                    $aA = PhocacartAttribute::sanitizeAttributeArray($attribs);
                    $price->getPriceItemsChangedByAttributes($priceI, $aA, $price, $itemD, 1);

                }

                $fullItems[$k]['netto']  = $priceI['netto'];
                $fullItems[$k]['brutto'] = $priceI['brutto'];
                $fullItems[$k]['tax']    = $priceI['tax'];


                // Advanced Stock Calculation
                if ($fullItems[$k]['stockcalculation'] == 2 || $fullItems[$k]['stockcalculation'] == 3) {
                    $fullItems[$k]['stockadvanced'] = PhocacartAttribute::getCombinationsStockByKey($k);

                }

                // Total
                $total['netto']  += ($fullItems[$k]['netto'] * $fQ);
                $total['brutto'] += ($fullItems[$k]['brutto'] * $fQ);
                $total['weight'] += ($fullItems[$k]['weight'] * $fQ);
                $total['volume'] += ($fullItems[$k]['volume'] * $fQ);

                $total['length'] = $itemD->length > $total['length'] ? $itemD->length : $total['length'];
                $total['width']  = $itemD->width > $total['width'] ? $itemD->width : $total['width'];
                $total['height'] = $itemD->height > $total['height'] ? $itemD->height : $total['height'];

                $total['points_needed']   += ($fullItems[$k]['points_needed'] * $fQ);
                $total['points_received'] += ($fullItems[$k]['points_received'] * $fQ);


                // TAX
                if (!isset($total['tax'][$taxKey]['tax'])) {
                    $total['tax'][$taxKey]['tax'] = 0;// Define
                }
                if (!isset($total['tax'][$taxKey]['netto'])) {
                    $total['tax'][$taxKey]['netto'] = 0;// Define (set netto for each tax)
                }
                if (!isset($total['tax'][$taxKey]['brutto'])) {
                    $total['tax'][$taxKey]['brutto'] = 0;// Define
                }


                $total['tax'][$taxKey]['tax']    += ($fullItems[$k]['tax'] * $fQ);
                $total['tax'][$taxKey]['netto']  += ($fullItems[$k]['netto'] * $fQ);
                $total['tax'][$taxKey]['brutto'] += ($fullItems[$k]['brutto'] * $fQ);


                $taxSuffix = '';
                if ($itemD->taxcalculationtype == 1) {
                    $taxSuffix = ' (' . ($price->getTaxFormat($itemD->taxrate, $itemD->taxcalculationtype, 0)) . ')';
                }

                $total['tax'][$taxKey]['title']              = Text::_($itemD->taxtitle) . $taxSuffix;
                $total['tax'][$taxKey]['title_lang']         = $itemD->taxtitle;
                $total['tax'][$taxKey]['title_lang_suffix2'] = '(' . $taxSuffix . ')';
                $total['tax'][$taxKey]['type']               = $itemD->taxcalculationtype;
                $total['tax'][$taxKey]['rate']               = $itemD->taxrate;
                $total['tax'][$taxKey]['taxid']              = $itemD->taxid;
                $total['tax'][$taxKey]['taxhide']            = $itemD->taxhide;

                // PRODUCTTYPE Digital product
                $total['countallproducts']++;
                if ($itemD->type == 0) {
                    $total['countphysicalproducts']++;
                } else if ($itemD->type == 1) {
                    $total['countdigitalproducts']++;
                } else if ($itemD->type == 2) {
                    // physical and digital
                    // This rule can be changed but for now e.g. we test if the product is digital to ensure that the shipping will be skipped
                    // if the product is both - digital and physical, we cannot skip shipping so we do not count it as digital
                    // Uncomment if you need to opposite rule
                    //$total['countphysicalproducts']++;
                    //$total['countdigitalproducts']++;
                } else if ($itemD->type == 3) {
                    $total['countpriceondemandproducts']++;
                } else if ($itemD->type == 4) {
                    // Gift Vouchers are even digital products
                    $total['countdigitalproducts']++;
                }

                // ==========
                // ATTRIBUTES
                // ==========
                //
                // Stock handling - one variant can be set in e.g. two products, so we need to count attributes stock:

                if (!empty($attribs)) {
                    foreach ($attribs as $k2 => $v2) {

                        // Make array from all attributes even they are not multiple - to go through the foreach
                        if (!is_array($v2)) {
                            $v2 = array(0 => $v2);
                        }

                        if (!empty($v2)) {
                            // Be aware the k3 is not the key of attribute
                            // this is the k2

                            foreach ($v2 as $k3 => $v3) {

                                if ((int)$k2 > 0 && (int)$k3 > 0) {

                                    $attrib = PhocacartAttribute::getAttributeValue((int)$k3, (int)$k2);

                                    // Price is set as fixed with help of advanced stock management
                                    if ($fullItems[$k]['stockcalculation'] != 3) {
                                        /*	if (!$attrib->aid) {

                                                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_PRODUCT_ATTRIBUTE_NOT_EXISTS_PLEASE_RECHECK_PRODUCTS_IN_YOUR_CART'), 'error');
                                                break;
                                            }*/
                                        if (isset($attrib->title) && isset($attrib->amount) && isset($attrib->operator)) {

                                            // If there is fixed VAT - don't change it in attributes - it is just fix - set taxtrate to 0
                                            if ($itemD->taxcalculationtype == 2) {
                                                $priceA = $price->getPriceItems($attrib->amount, $itemD->taxid, 0, $itemD->taxcalculationtype, $itemD->taxtitle, 0, '', 0, 1, null, $itemD->taxhide);
                                            } else {
                                                $priceA = $price->getPriceItems($attrib->amount, $itemD->taxid, $itemD->taxrate, $itemD->taxcalculationtype, $itemD->taxtitle, 0, '', 0, 1, null, $itemD->taxhide);
                                            }


                                            //$fQ 	= (int)$fullItems[$k]['quantity'];
                                            // Price
                                            if ($attrib->operator == '-') {

                                                $nettoBefore = $fullItems[$k]['netto'];
                                                $fullItems[$k]['netto'] -= $priceA['netto'];
                                                if ($fullItems[$k]['netto'] < 0) {
                                                    $total['netto'] -= ($nettoBefore * $fQ);
                                                    $fullItems[$k]['netto'] = 0;

                                                } else {
                                                    $total['netto'] -= ($priceA['netto'] * $fQ);
                                                }

                                                $bruttoBefore = $fullItems[$k]['brutto'];
                                                $fullItems[$k]['brutto'] -= $priceA['brutto'];
                                                if ($fullItems[$k]['brutto'] < 0 ) {
                                                    $total['brutto'] -= ($bruttoBefore * $fQ);
                                                    $fullItems[$k]['brutto'] = 0;
                                                } else {

                                                    $total['brutto'] -= ($priceA['brutto'] * $fQ);
                                                }

                                                $taxBefore = $fullItems[$k]['tax'];
                                                $fullItems[$k]['tax'] -= $priceA['tax'];
                                                if ($fullItems[$k]['tax'] < 0 ) {
                                                    $total['tax'][$taxKey]['tax']    -= ($taxBefore * $fQ);
                                                    $total['tax'][$taxKey]['netto']  -= ($nettoBefore * $fQ);
                                                    $total['tax'][$taxKey]['brutto'] -= ($bruttoBefore * $fQ);
                                                    $fullItems[$k]['tax'] = 0;
                                                } else {

                                                    $total['tax'][$taxKey]['tax']    -= ($priceA['tax'] * $fQ);
                                                    $total['tax'][$taxKey]['netto']  -= ($priceA['netto'] * $fQ);
                                                    $total['tax'][$taxKey]['brutto'] -= ($priceA['brutto'] * $fQ);
                                                }


                                            } else if ($attrib->operator == '+') {

                                                $fullItems[$k]['brutto']         += $priceA['brutto'];// * multiply in render checkout
                                                $fullItems[$k]['netto']          += $priceA['netto']; // * multiply in render checkout
                                                $fullItems[$k]['tax']            += $priceA['tax'];   // * multiply in render checkout

                                                $total['netto']                  += ($priceA['netto'] * $fQ);
                                                $total['brutto']                 += ($priceA['brutto'] * $fQ);
                                                $total['tax'][$taxKey]['tax']    += ($priceA['tax'] * $fQ);
                                                $total['tax'][$taxKey]['netto']  += ($priceA['netto'] * $fQ);
                                                $total['tax'][$taxKey]['brutto'] += ($priceA['brutto'] * $fQ);

                                            }

                                        }
                                    }

                                    // Weight
                                    if ($attrib->operator_weight == '-') {
                                        $fullItems[$k]['weight'] -= $attrib->weight;
                                        $fullItems[$k]['weight'] < 0 ? $fullItems[$k]['weight'] = 0 : $total['weight'] -= ($attrib->weight * $fullItems[$k]['quantity']);
                                    } else if ($attrib->operator_weight == '+') {
                                        $fullItems[$k]['weight'] += $attrib->weight;
                                        $total['weight']         += ($attrib->weight * $fullItems[$k]['quantity']);
                                    }

                                    // Volume
                                    if ($attrib->operator_volume == '-') {
                                        $fullItems[$k]['volume'] 	-= $attrib->volume;
                                        $fullItems[$k]['volume'] < 0 ? $fullItems[$k]['volume'] = 0 : $total['volume']	-= ($attrib->volume * $fullItems[$k]['quantity']);
                                    }  else if ($attrib->operator_volume == '+') {
                                        $fullItems[$k]['volume'] 	+= $attrib->volume;
                                        $total['volume']			+= ($attrib->volume * $fullItems[$k]['quantity']);
                                    }


                                    if (isset($optionsQuantity[$attrib->id])) {
                                        $optionsQuantity[$attrib->id] += (int)$fQ;

                                    } else {
                                        $optionsQuantity[$attrib->id] = (int)$fQ;

                                    }
                                    // STOCK-1 ... we count each product variation separately
                                    if ($fullItems[$k]['stockcalculation'] == 1 && (int)$optionsQuantity[$attrib->id] > (int)$attrib->stock) {
                                        $total['stockvalid']         = 0;
                                        $fullItems[$k]['stockvalid'] = 0;
                                        $stock['valid']              = 0;
                                    }


                                    // Attribute values
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['aid']    = $attrib->aid; // Attribute Id
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['atitle'] = $attrib->atitle;
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['atype']  = $attrib->atype;
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['oid']    = $attrib->id;// Option Id
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['otitle'] = $attrib->title;
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['oimage'] = $attrib->image;
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['ovalue'] = PhocacartAttribute::setAttributeValue($attrib->atype, $v3, false, true, $attrib->type);
                                    $fullItems[$k]['attributes'][$attrib->aid][$k3]['otype']  = $attrib->type;

                                    //$fullItems[$k]['attributes'][$attrib->aid][$k3]['odownloadfile']= $attrib->download_file;

                                }
                            }
                        }
                    }
                }


                // ==============================
                // MINIUM ORDER AMOUNT
                // ==============================
                // THERE CAN BE THREE METHODS HOW TO COUNT MINIMUM ORDER AMOUNT
                // a) every product is unique (Product A - Option A, Product A - Option B are two different products)
                // b) there are product groups (Product A- Option A, Product A - Option B is still one product - product A)
                // c) advanced stock management - in this case it is the same like a)

                if ($fullItems[$k]['minqtycalculation'] == 1 || $fullItems[$k]['minqtycalculation'] == 2) {
                    // a)
                    // MINIMUM QUANTITY - FOR ITEM - PRODUCT VARIATION - each product variation
                    if ((int)$fullItems[$k]['quantity'] < (int)$fullItems[$k]['minqty']) {
                        $minqty['valid']              = 0;
                        $fullItems[$k]['minqtyvalid'] = 0;
                    }

                    if ((int)$fullItems[$k]['minmultipleqty'] == 0) {
                        // Do not modulo by zero
                        // Set it back because we are in foreach
                        $minmultipleqty['valid']              = 1;
                        $fullItems[$k]['minmultipleqtyvalid'] = 1;
                    } else if (((int)$fullItems[$k]['quantity']) % (int)$fullItems[$k]['minmultipleqty'] != 0) {
                        $minmultipleqty['valid']              = 0;
                        $fullItems[$k]['minmultipleqtyvalid'] = 0;
                    }

                } else {

                    // b)
                    // MINIMUM QUANTITY - FOR GROUP (Group is the same product but with different options values) - MAIN PRODUCT
                    if (empty($fullItemsGroup[$itemId]['minqty'])) {
                        $minqty['valid']                        = 1;
                        $fullItemsGroup[$itemId]['minqtyvalid'] = 1;
                    } else if ((int)$fullItemsGroup[$itemId]['quantity'] < (int)$fullItemsGroup[$itemId]['minqty']) {
                        $minqty['valid']                        = 0;
                        $fullItemsGroup[$itemId]['minqtyvalid'] = 0;
                    } else {
                        // Set it back because we are in foreach
                        $minqty['valid']                        = 1;
                        $fullItemsGroup[$itemId]['minqtyvalid'] = 1;
                    }

                    // MINIMUM MULTIPLE QUANTITY
                    if (empty($fullItemsGroup[$itemId]['minmultipleqty'])) {
                        $minmultipleqty['valid']                        = 1;
                        $fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
                    } else if ($fullItemsGroup[$itemId]['minmultipleqty'] == 0) {
                        // Do not modulo by zero
                        // Set it back because we are in foreach
                        $minmultipleqty['valid']                        = 1;
                        $fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
                    } else if (((int)$fullItemsGroup[$itemId]['quantity']) % (int)$fullItemsGroup[$itemId]['minmultipleqty'] != 0) {
                        $minmultipleqty['valid']                        = 0;
                        $fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 0;
                    } else {
                        // Set it back because we are in foreach
                        $minmultipleqty['valid']                        = 1;
                        $fullItemsGroup[$itemId]['minmultipleqtyvalid'] = 1;
                    }
                }


                // ==============================
                // STOCK VALID
                // ==============================

                // The difference between STOCK-1, STOCK-0
                // b) STOCK-0 - There is only one main product even it is divided into more product variations
                //         - so we count only main product - group
                // a) STOCK-1 - Each product variation is one product but this means that product without any variation
                //         - is in one one of the product variation:
                // Product 1 Option A - one product
                // Product 1 Option B - one product
                // Product 1 (no options) - one product - as sum there are 3 products
                // c) STOCK-2 - advanced stock management
                // Product 1 Option A - one product
                // Product 1 Option B - one product
                // Product 1 (no options) - one product - as sum there are 3 products
                // Product 1 Option A + Option B - one product

                // STOCK-2 ... we count main product as own product variation - only in case it does not have any attributes
                //         ... but combination of attributes can create different products

                if (($fullItems[$k]['stockcalculation'] == 2 || $fullItems[$k]['stockcalculation'] == 3) && (int)$fullItems[$k]['quantity'] > (int)$fullItems[$k]['stockadvanced']) {
                    $stock['valid']              = 0;// Global - some of the product is out of stock
                    $fullItems[$k]['stockvalid'] = 0;// Current product is out of stock

                }

                // STOCK-1 ... we count main product as own product variation - only in case it does not have any attributes
                //             variations of product are checked in ohter place (cca line 271)
                // THIS IS DIVEDED RULE - ONE HERE, SECOND ABOVE IN ATTRIBUTES FOREACH
                if ($fullItems[$k]['stockcalculation'] == 1 && empty($fullItems[$k]['attributes']) && (int)$fullItems[$k]['quantity'] > (int)$fullItems[$k]['stock']) {
                    $stock['valid']              = 0;// Global - some of the product is out of stock
                    $fullItems[$k]['stockvalid'] = 0;// Current product is out of stock

                }

                // STOCK-0 ... we count main product as group: Product 1 Option A ... 5 + Product 1 Option B ... 5 = 10
                if ($fullItems[$k]['stockcalculation'] == 0 && (int)$fullItemsGroup[$itemId]['quantity'] > (int)$fullItems[$k]['stock']) {
                    $stock['valid']              = 0;// Global - some of the product is out of stock
                    $fullItems[$k]['stockvalid'] = 0;// Current product is out of stock

                }

                $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $fQ : $fullItems[$k]['brutto'] * $fQ;

            }

            if ($this->correctsubtotal) {
                $this->correctSubTotal($fullItems[$k], $total);
            }
        }
    }


    // ================
    // REWARD POINTS
    // ================
    public function calculateRewardDiscounts(&$fullItems, &$fullItemsGroup, &$total, $rewardCart) {


        $reward               = new PhocacartReward();
        $rewards = array();
        $rewards['used'] = $reward->checkReward((int)$rewardCart['used']);
        $rewards['usedtotal'] = 0;

        foreach ($fullItems as $k => $v) {

            if (isset($v['points_needed']) && (int)$v['points_needed'] > 0) {


                $rewards['needed'] = $v['quantity'] * $v['points_needed'];

                $reward->calculatedRewardDiscountProduct($rewards);

                if (isset($rewards['percentage']) && $rewards['percentage'] > 0) {

                    $fullItems[$k]['rewardproduct']       = 1;
                    $fullItems[$k]['rewardproductpoints'] = $rewards['usedproduct'];
                    $fullItems[$k]['rewardproducttitle']  = Text::_('COM_PHOCACART_REWARD_POINTS');


                    PhocacartCalculation::calculateDiscountPercentage($rewards['percentage'], $v['quantity'], $fullItems[$k], $total, $v['taxkey']);

                    $fullItems[$k]['rewardproducttxtsuffix'] = ' (' . $rewards['usedproduct'] . ')';
                    $total['rewardproducttxtsuffix']         = ' (' . $rewards['usedtotal'] . ')';
                    $total['rewardproductusedtotal']         = $rewards['usedtotal'];

                    PhocacartCalculation::correctItemsIfNull($fullItems[$k]);
                    PhocacartCalculation::correctTotalIfNull($total, $v['taxkey']);

                }

                $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];


                if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                    $fullItems[$k]['finaldiscount'] = $fullItems[$k]['nettodiscount'] * $v['quantity'];
                } else if (isset($fullItems[$k]['bruttodiscount'])) {
                    $fullItems[$k]['finaldiscount'] = $fullItems[$k]['bruttodiscount'] * $v['quantity'];
                }


                if ($this->correctsubtotal) {
                    $this->correctSubTotal($fullItems[$k], $total);
                }

            }
        }
    }



    // ================
    // PRODUCT DISCOUNT
    // ================
    public function calculateProductDiscounts(&$fullItems, &$fullItemsGroup, &$total) {


        foreach ($fullItems as $k => $v) {


            // Get quantity of a group. Group is sum of all product variations
            // - explained in PhocacartDiscountProduct::getProductDiscount
            $groupId            = $v['id'];
            $v['quantitygroup'] = $v['quantity'];// define
            if (isset($fullItemsGroup[$groupId]['quantity'])) {
                $v['quantitygroup'] = $fullItemsGroup[$groupId]['quantity'];
            }


            $discount = PhocacartDiscountProduct::getProductDiscount($v['id'], $v['quantitygroup'], $v['quantity']);

            if (isset($discount['discount']) && isset($discount['calculation_type'])) {

                $fullItems[$k]['discountproduct']      = 1;
                $fullItems[$k]['discountproducttitle'] = $discount['title'];

                if ($discount['calculation_type'] == 0) {
                    // FIXED AMOUNT
                    if (isset($v['netto']) && $v['netto'] > 0) {
                        //$r = $discount['discount'] / $v['quantity'] * 100 / $v['netto'];// Ratio to use it for brutto and tax
                        // PRODUCT DISCOUNT - DON'T DIVIDE IT INTO QUANTITY
                        //if you set 500 fixed amount as discount - it applies to each quantity
                        $r = $discount['discount'] * 100 / $v['netto'];

                    } else {
                        $r = 0;
                    }

                    PhocacartCalculation::calculateDiscountFixedAmount($r, $v['quantity'], $fullItems[$k], $total, $v['taxkey']);

                } else {
                    // PERCENTAGE

                    PhocacartCalculation::calculateDiscountPercentage($discount['discount'], $v['quantity'], $fullItems[$k], $total, $v['taxkey']);
                }

                PhocacartCalculation::correctItemsIfNull($fullItems[$k]);
                PhocacartCalculation::correctTotalIfNull($total, $v['taxkey']);

            }

            $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];

            if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['nettodiscount'] * $v['quantity'];
            } else if (isset($fullItems[$k]['bruttodiscount'])) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['bruttodiscount'] * $v['quantity'];
            }

            if ($this->correctsubtotal) {
                $this->correctSubTotal($fullItems[$k], $total);
            }
        }
    }

    // =============
    // CART DISCOUNT
    // =============
    public function calculateCartDiscounts(&$fullItems, &$fullItemsGroup, &$total, &$cartDiscount) {

        // If there are more cart discounts e.g. separated by different rules
        // remove the suffix because it will be not valid
        $discountSuffixItems = array();

        foreach ($fullItems as $k => $v) {


            $discount = PhocacartDiscountCart::getCartDiscount($v['id'], $v['catid'], $total['quantity'], $total['netto'], $total['subtotalnetto']);

            // First check if there is even some discount
            if ($discount) {

                $discountId     = $discount['id'];
                $discountAmount = $discount['discount'];

                if ($discount['free_shipping'] == 1) {
                    $total['free_shipping'] = $discount['free_shipping'];
                }
                if ($discount['free_payment'] == 1) {
                    $total['free_payment'] = $discount['free_payment'];
                }

                if (isset($discount['discount']) && isset($discount['calculation_type'])) {

                    $fullItems[$k]['discountcart']      = 1;
                    $fullItems[$k]['discountcarttitle'] = $discount['title'];
                    $fullItems[$k]['discountcartid']    = $discount['id'];

                    if ($discount['calculation_type'] == 0) {
                        // FIXED AMOUNT
                        // We need to divide fixed discount amount to products which meet the discount ID rule
                        // There can be two products in the cart and each can meet other discount rules
                        if (isset($total['discountcartfixedamount'][$discountId]['quantity'])) {
                            $total['discountcartfixedamount'][$discountId]['quantity'] += $v['quantity'];
                        } else {
                            $total['discountcartfixedamount'][$discountId]['quantity'] = $v['quantity'];
                        }

                        if (isset($total['discountcartfixedamount'][$discountId]['netto'])) {
                            $total['discountcartfixedamount'][$discountId]['netto'] += $v['netto'] * $v['quantity'];
                        } else {
                            $total['discountcartfixedamount'][$discountId]['netto'] = $v['netto'] * $v['quantity'];
                        }


                        $total['discountcartfixedamount'][$discountId]['discount'] = $discountAmount;
                        $fullItems[$k]['discountcartfixedid']                      = $discountId;


                    } else {
                        // PERCENTAGE
                        PhocacartCalculation::calculateDiscountPercentage($discount['discount'], $v['quantity'], $fullItems[$k], $total, $v['taxkey']);

                        $discountSuffixItems[$discount['discount']] = $discount['discount'];

                        if (count($discountSuffixItems) > 1) {
                            // There are different types of discounts, remove the suffix Cart discount (10%) become Cart discount. Because if there is
                            // e.g. 5% and 10% then the 10% in () will be misleading
                            $total['discountcarttxtsuffix'] = '';
                        } else {
                             $price                          = new PhocacartPrice();
                            $total['discountcarttxtsuffix'] = ' (' . $price->cleanPrice($discount['discount']) . ' %)';
                        }

                    }

                    $cartDiscount['id']    = $discount['id'];
                    $cartDiscount['title'] = $discount['title'];

                }
            }

            $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];


            /*
             * Must be done in recalculateCartDiscounts()
             *
            if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                $fullItems[$k]['finaldiscount']	= $fullItems[$k]['nettodiscount'] * $v['quantity'];
            } else if (isset($fullItems[$k]['bruttodiscount'])) {
                $fullItems[$k]['finaldiscount']	= $fullItems[$k]['bruttodiscount'] * $v['quantity'];
            }*/

            if ($this->correctsubtotal) {
                //$this->correctSubTotal($fullItems[$k], $total);
            }
        }
    }

    /*
     * Used only for fixed amount of discount
     */
    public function recalculateCartDiscounts(&$fullItems, &$fullItemsGroup, &$total) {

        foreach ($fullItems as $k => $v) {


            // Fixed amount - we need to recalculate
            $dF = $v['discountcartfixedid'];

            if (isset($total['discountcartfixedamount'][$dF]['discount']) && isset($total['discountcartfixedamount'][$dF]['netto'])) {

                $dPRel = 0;
                if ($total['discountcartfixedamount'][$dF]['netto'] > 0) {
                    $dPRel = $total['discountcartfixedamount'][$dF]['discount'] / $total['discountcartfixedamount'][$dF]['netto'];
                }
                // CART DISCOUNT - DIVIDE IT INTO QUANTITY
                $dPFix = $dPRel * $v['netto'] * $v['quantity'];

                if ($v['netto'] > 0) {
                    $r = $dPFix * 100 / $v['netto'] / $v['quantity'];// Ratio to use it for brutto and tax but you need to divide it into qunatity
                } else {
                    $r = 0;
                }


                PhocacartCalculation::calculateDiscountFixedAmount($r, $v['quantity'], $fullItems[$k], $total, $v['taxkey']);


                PhocacartCalculation::correctItemsIfNull($fullItems[$k]);
                PhocacartCalculation::correctTotalIfNull($total, $v['taxkey']);


            }

            $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];


            if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['nettodiscount'] * $v['quantity'];
            } else if (isset($fullItems[$k]['bruttodiscount'])) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['bruttodiscount'] * $v['quantity'];
            }

            if ($this->correctsubtotal) {
                $this->correctSubTotal($fullItems[$k], $total);
            }

        }
    }

    // ============
    // CART COUPON
    // ============

    /*
     * $coupon ... coupon ID and Title set in checkout
     * $couponO ... coupon object
     * $couponDb ... all the information from coupon
     * $validCoupon ... is or isn't valid TRUE/FALSE
     */
    public function calculateCartCoupons(&$fullItems, &$fullItemsGroup, &$total, &$coupon) {

        $couponO = new PhocacartCoupon();
        $couponO->setType($this->type);
        $couponO->setCoupon($coupon['id']);
        $couponDb = $couponO->getCoupon();

        foreach ($fullItems as $k => $v) {

            $validCoupon = $couponO->checkCoupon(0, $v['id'], $v['catid'], $total['quantity'], $total['netto'], $total['subtotalnetto']);


            if ($validCoupon) {
                $validCouponId     = $couponDb['id'];
                $validCouponAmount = $couponDb['discount'];

                if ($couponDb['free_shipping'] == 1) {
                    $total['free_shipping'] = $couponDb['free_shipping'];
                }
                if ($couponDb['free_payment'] == 1) {
                    $total['free_payment'] = $couponDb['free_payment'];
                }

                if (isset($couponDb['discount']) && isset($couponDb['calculation_type'])) {

                    $fullItems[$k]['couponcart']      = 1;
                    $fullItems[$k]['couponcarttitle'] = $couponDb['title'];
                    $fullItems[$k]['couponcartid']    = $couponDb['id'];


                    if ($couponDb['calculation_type'] == 0) {
                        // FIXED AMOUNT
                        // We need to divide fixed couponDb amount to products which meet the couponDb ID rule
                        // There can be two products in the cart and each can meet other couponDb rules
                        if (isset($total['couponcartfixedamount'][$validCouponId]['quantity'])) {
                            $total['couponcartfixedamount'][$validCouponId]['quantity'] += $v['quantity'];
                        } else {
                            $total['couponcartfixedamount'][$validCouponId]['quantity'] = $v['quantity'];
                        }

                        if (isset($total['couponcartfixedamount'][$validCouponId]['netto'])) {
                            $total['couponcartfixedamount'][$validCouponId]['netto'] += $v['netto'] * $v['quantity'];
                        } else {
                            $total['couponcartfixedamount'][$validCouponId]['netto'] = $v['netto'] * $v['quantity'];
                        }

                        $total['couponcartfixedamount'][$validCouponId]['discount'] = $validCouponAmount;
                        $fullItems[$k]['couponcartfixedid']                         = $validCouponId;


                    } else {
                        // PERCENTAGE
                        PhocacartCalculation::calculateDiscountPercentage($couponDb['discount'], $v['quantity'], $fullItems[$k], $total, $v['taxkey']);
                        PhocacartCalculation::correctItemsIfNull($fullItems[$k]);
                        PhocacartCalculation::correctTotalIfNull($total, $v['taxkey']);

                        $price                        = new PhocacartPrice();
                        $total['couponcarttxtsuffix'] = ' (' . $price->cleanPrice($couponDb['discount']) . ' %)';

                    }
                }
            }

            // !!! VALID COUPON
            // In case the coupon is valid at least for one product or one category it is then valid
            // and will be divided into valid products/categories
            // As global we mark it as valid - so change the valid coupon variable only in case it is valid
            if ($validCoupon == 1) {
                $coupon['valid'] = $validCoupon;
            }
            $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];

            /*
             *
             * Must be done in recalculateCartCoupons for fixed coupons
             */
            if (isset($couponDb['calculation_type']) && $couponDb['calculation_type'] != 0) {
                if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                    $fullItems[$k]['finaldiscount'] = $fullItems[$k]['nettodiscount'] * $v['quantity'];
                } else if (isset($fullItems[$k]['bruttodiscount'])) {
                    $fullItems[$k]['finaldiscount'] = $fullItems[$k]['bruttodiscount'] * $v['quantity'];
                }
            }

            if ($this->correctsubtotal) {
                $this->correctSubTotal($fullItems[$k], $total);
            }
        }
    }

    /*
     * Used only for fixed amount of coupon
     */
    public function recalculateCartCoupons(&$fullItems, &$fullItemsGroup, &$total) {

        foreach ($fullItems as $k => $v) {

            $dF = $v['couponcartfixedid'];

            if (isset($total['couponcartfixedamount'][$dF]['discount']) && isset($total['couponcartfixedamount'][$dF]['netto'])) {

                $dPRel = 0;
                if ($total['couponcartfixedamount'][$dF]['netto'] > 0) {
                    $dPRel = $total['couponcartfixedamount'][$dF]['discount'] / $total['couponcartfixedamount'][$dF]['netto'];
                }
                // CART DISCOUNT - DIVIDE IT INTO QUANTITY
                $dPFix = $dPRel * $v['netto'] * $v['quantity'];

                if ($v['netto'] > 0) {
                    $r = $dPFix * 100 / $v['netto'] / $v['quantity'];// Ratio to use it for brutto and tax but you need to divide it into qunatity
                } else {
                    $r = 0;
                }


                PhocacartCalculation::calculateDiscountFixedAmount($r, $v['quantity'], $fullItems[$k], $total, $v['taxkey']);
                PhocacartCalculation::correctItemsIfNull($fullItems[$k]);
                PhocacartCalculation::correctTotalIfNull($total, $v['taxkey']);
            }

            $fullItems[$k]['final'] = $fullItems[$k]['netto'] && !$this->posbruttocalculation ? $fullItems[$k]['netto'] * $v['quantity'] : $fullItems[$k]['brutto'] * $v['quantity'];

            if (isset($fullItems[$k]['nettodiscount']) && !$this->posbruttocalculation) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['nettodiscount'] * $v['quantity'];
            } else if (isset($fullItems[$k]['bruttodiscount'])) {
                $fullItems[$k]['finaldiscount'] = $fullItems[$k]['bruttodiscount'] * $v['quantity'];
            }

            if ($this->correctsubtotal) {
                $this->correctSubTotal($fullItems[$k], $total);
            }

        }
    }


    // Correct rounding errors
    // When we count the tax and quantity, we need to do without rounding because of VAT
    // This is why we run $price->getPriceItems without rounding Output
    // Ok, now rounding, this means, we can get difference e.g. 0.01 so such we need to correct
    // - in total, even on the row
    // This can happen when using Exclusive Tax - this is common problem with exclusive tax counting

    public function correctSubTotal(&$item, &$total) {


        // Fix when discount is larger than final netto (this can happen due to rounding, so it can happen that netto is e.g. - 0.01

        if ($total['netto'] < 0) {
            $item['finaldiscount'] += $total['netto'];
            $total['netto']        = 0;
        }

        // Fixed VAT
        if ($item['taxcalctype'] == 2) {
            //return;
        }
        $price           = new PhocacartPrice();
        $quantityCorrect = $item['quantity'] > 0 ? $item['quantity'] : 1;
        $nettoNotRounded = $item['netto'] * $quantityCorrect;
        $taxNotRounded   = $item['tax'] * $quantityCorrect;

        $nettoRounded  = $price->roundPrice($item['netto'] * $quantityCorrect);
        $bruttoRounded = $price->roundPrice($item['brutto'] * $quantityCorrect);
        $taxRounded    = $price->roundPrice($item['tax'] * $quantityCorrect);

        //- $nettoRounded 		= $item['netto'] * $quantityCorrect;
        //- $bruttoRounded 		= $item['brutto'] * $quantityCorrect;
        //- $taxRounded 		= $item['tax'] * $quantityCorrect;

        $nettoRoundedCorrected = $bruttoRounded - $taxRounded;

        if ($nettoNotRounded > 0) {
            if (abs(($nettoRoundedCorrected - $nettoNotRounded) / $nettoNotRounded) < 0.00001) {
                // the floats are the same
            } else {

                $item['netto']  = $nettoRoundedCorrected / $quantityCorrect;
                $total['netto'] = $total['netto'] + ($nettoRoundedCorrected / $item['quantity']) - ($nettoNotRounded / $quantityCorrect);


                if (!empty($total['tax'])) {
                    foreach ($total['tax'] as $kT => $vT) {
                        if ($kT == $item['taxkey']) {

                            $total['tax'][$kT]['netto']  = $vT['netto'] + ($nettoRoundedCorrected / $item['quantity']) - ($nettoNotRounded / $quantityCorrect);
                            $total['tax'][$kT]['tax']   = $vT['tax'];
                            $total['tax'][$kT]['brutto'] = $total['tax'][$kT]['tax'] + $total['tax'][$kT]['netto'];
                        }
                    }
                }

            }
        }
    }

    /*
    public function correctSubTotalAll(&$fullItems, &$total) {

        foreach($fullItems as $k => $v) {
            $price				= new PhocacartPrice();
            $quantityCorrect	= $v['quantity'] > 0 ? $v['quantity'] : 1;
            $nettoNotRounded	= $v['netto'] * $quantityCorrect;
            $taxNotRounded		= $v['tax'] * $quantityCorrect;
            $nettoRounded 		= $price->roundPrice($v['netto'] * $quantityCorrect);
            $bruttoRounded 		= $price->roundPrice($v['brutto'] * $quantityCorrect);
            $taxRounded 		= $price->roundPrice($v['tax'] * $quantityCorrect);

            $nettoRoundedCorrected = $bruttoRounded - $taxRounded;

            if ($nettoNotRounded > 0) {
                if (abs(($nettoRoundedCorrected - $nettoNotRounded)/$nettoNotRounded) < 0.00001) {
                    // the floats are the same
                } else {

                    $fullItems[$k]['netto'] = $nettoRoundedCorrected / $quantityCorrect;
                    $total['netto'] = $total['netto'] + ($nettoRoundedCorrected / $v['quantity']) - ($nettoNotRounded / $quantityCorrect);

                    if (!empty($total['tax'])) {
                        foreach($total['tax'] as $kT => $vT) {
                            if ($kT == $v['taxkey']) {
                                $total['tax'][$kT]['tax'] = $vT['tax'] + ($taxRounded / $quantityCorrect) - ($taxNotRounded / $quantityCorrect);
                            }
                        }
                    }
                }
            }
        }
    }*/


    public function roundFixedAmountDiscount(&$total) {


        $app                      = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        $rounding_calculation_fad = $paramsC->get('rounding_calculation_fixed_amount_discount', -1);

        if ($rounding_calculation_fad < 0) {
            return;
        }

        $discount = 0;
        if (!empty($total['discountcartfixedamount'])) {
            foreach ($total['discountcartfixedamount'] as $k => $v) {
                if (isset($v['discount'])) {
                    if (isset($v['netto']) && $v['netto'] < $v['discount']) {
                        $discount = $v['netto']; // more pieces of product - so the cart discount is divided to more pieces of products, but max to cart discount fixed amount
                    } else {
                        $discount = $v['discount'];// one product - whole cart discount goes to one product
                    }
                }
            }
        }

        if (isset($total['dnetto']) && $total['dnetto'] > 0 && $discount > 0) {
            $dif = $discount - $total['dnetto'];

            if ($dif > 0) {
                $total['rounding'] += $dif;
                $total['dnetto']   = $discount;

            } else if ($dif < 0) {
                $total['rounding'] -= $dif;
                $total['dnetto']   = $discount;
                $total['netto']    += $dif;

            }

        }

        return;
    }

    public function roundFixedAmountCoupon(&$total) {

        $app                      = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        $rounding_calculation_fac = $paramsC->get('rounding_calculation_fixed_amount_coupon', -1);

        if ($rounding_calculation_fac < 0) {
            return;
        }

        $discount = 0;
        if (!empty($total['couponcartfixedamount'])) {
            foreach ($total['couponcartfixedamount'] as $k => $v) {
                if (isset($v['discount'])) {
                    if (isset($v['netto']) && $v['netto'] < $v['discount']) {
                        $discount = $v['netto']; // more pieces of product - so the cart discount is divided to more pieces of products, but max to cart discount fixed amount
                    } else {
                        $discount = $v['discount'];// one product - whole cart discount goes to one product
                    }
                }
            }
        }

        if (isset($total['dnetto']) && $total['dnetto'] > 0 && $discount > 0) {
            $dif = $discount - $total['dnetto'];

            if ($dif > 0) {
                $total['rounding'] += $dif;
                $total['dnetto']   = $discount;

            } else if ($dif < 0) {
                $total['rounding'] -= $dif;
                $total['dnetto']   = $discount;

            }

        }

        return;
    }

    /**
     *
     * @param unknown $total
     * @param unknown $shippingCosts
     * @param unknown $paymentCosts
     * @param array $options - set specific options - this function is called twice so in second loop just set specific option (e.g. to not create brutto currency again)
     * @return boolean
     */


    public function correctTotalItems(&$total, &$shippingCosts, &$paymentCosts, $options = array()) {


        if (!isset($total[0]['brutto'])) {
            return false;
        }

        $price = new PhocacartPrice();
        //$paramsC 						= PhocacartUtils::getComponentParameters();
        //$rounding_calculation			= $paramsC->get( 'rounding_calculation', 1 );
        //$rounding_calculation_total	= $paramsC->get( 'rounding_calculation_total', 2 );
        //$currencyRate 				= PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();
        //$currency 					= PhocacartCurrency::getCurrency();
        //$cr							= $currency->exchange_rate;
        $cr      = PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();
        $totalDC = 0; // total in default currency
        $totalOR = 0; // total in order currency


        // Subtotal
        if (isset($total[1]['netto'])) {
            $totalDC += $price->roundPrice($total[1]['netto']);
            $totalOR += $price->roundPrice($total[1]['netto'] * $cr);
        }

        // - Reward points
        if (isset($total[5]['dnetto'])) {
            $totalDC -= $price->roundPrice($total[5]['dnetto']);
            $totalOR -= $price->roundPrice($total[5]['dnetto'] * $cr);
        }

        // - Product Discount
        if (isset($total[2]['dnetto'])) {
            $totalDC -= $price->roundPrice($total[2]['dnetto']);
            $totalOR -= $price->roundPrice($total[2]['dnetto'] * $cr);
        }

        // - Discount cart
        if (isset($total[3]['dnetto'])) {
            $totalDC -= $price->roundPrice($total[3]['dnetto']);
            $totalOR -= $price->roundPrice($total[3]['dnetto'] * $cr);
        }

        // - Coupon cart
        if (isset($total[4]['dnetto'])) {
            $totalDC -= $price->roundPrice($total[4]['dnetto']);
            $totalOR -= $price->roundPrice($total[4]['dnetto'] * $cr);
        }

        // + VAT
        if (!empty($total[0]['tax'])) {
            foreach ($total[0]['tax'] as $k => $v) {
                $totalDC += $price->roundPrice($v['tax']);
                $totalOR += $price->roundPrice($v['tax'] * $cr);
            }
        }

        // + Shipping Costs
        if (isset($shippingCosts['brutto'])) {
            $shippingCosts['bruttorounded'] = $price->roundPrice($shippingCosts['brutto']);
            $totalDC                        += $price->roundPrice($shippingCosts['brutto']);
            $totalOR                        += $price->roundPrice($shippingCosts['brutto'] * $cr);
        }

        // + Payment Costs
        if (isset($paymentCosts['brutto'])) {
            $paymentCosts['bruttorounded'] = $price->roundPrice($paymentCosts['brutto']);
            $totalDC                       += $price->roundPrice($paymentCosts['brutto']);
            $totalOR                       += $price->roundPrice($paymentCosts['brutto'] * $cr);
        }


        // ------------------------
        // 1) NO ROUNDING - CORRECTION ONLY
        // ------------------------
        // 1a) CORRECT BRUTTO - DEFAULT CURRENCY
        $diff = $total[0]['brutto'] - $totalDC;
        if (!($price->roundPrice($diff) > -0.01 && $price->roundPrice($diff) < 0.01)) {
            $total[0]['rounding'] = $price->roundPrice($diff);
        } else {
            $total[0]['rounding'] = 0;
        }

        // 1b) CORRECT BRUTTO - ORDER CURRENCY
        if ($cr > 1 || $cr < 1) {


            if (isset($options['brutto_currency_set']) && isset($options['brutto_currency_set']) == 1) {
                $totalBruttoCurrency = $price->roundPrice($total[0]['brutto_currency']);
            } else {
                $totalBruttoCurrency         = $price->roundPrice($total[0]['brutto'] * $cr);
                $total[0]['brutto_currency'] = $totalBruttoCurrency;

            }


            $diff = $price->roundPrice($totalBruttoCurrency) - $price->roundPrice($totalOR);
            if (!($price->roundPrice($diff) > -0.01 && $price->roundPrice($diff) < 0.01)) {
                $total[0]['rounding_currency'] = $price->roundPrice($diff);
            } else {
                $total[0]['rounding_currency'] = 0;
            }
            return true;
        }


    }

    public function roundTotalAmount(&$total) {

        $price                      = new PhocacartPrice();
        $paramsC = PhocacartUtils::getComponentParameters();
        $rounding_calculation = $paramsC->get('rounding_calculation', 1);
        $rounding_calculation_total = $paramsC->get('rounding_calculation_total', 2);
        $currencyRate               = PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();


        if (!isset($total['brutto'])) {
            return false;
        }

        // ------------------------
        // 2) ROUNDING
        // ------------------------
        // !Important
        // Each currency has own total rounding and brutto
        if ($rounding_calculation_total > -1) {


            if ($currencyRate > 1 || $currencyRate < 1) {

                /*$totalBruttoCurrency 		= $total['brutto_currency'];//$total['brutto'] * $currencyRate;
                $totalBruttoCurrencyRound	= round($totalBruttoCurrency , (int)$rounding_calculation_total, $rounding_calculation);
                $bruttoCurrency 			= round($total['brutto_currency'], 2, $rounding_calculation);
                if ($totalBruttoCurrency != $bruttoCurrency) {

                    $total['rounding_currency']	+= ($totalBruttoCurrencyRound - $bruttoCurrency);
                }
                $total['brutto_currency'] 	= $price->roundPrice($totalBruttoCurrencyRound);*/

                // 2a) ROUNDING ORDER CURRENCY
                $bruttoCurrency = round($total['brutto_currency'], (int)$rounding_calculation_total, $rounding_calculation);
                $diff           = $price->roundPrice($bruttoCurrency) - $price->roundPrice($total['brutto_currency']);

                if (!($price->roundPrice($diff) > -0.01 && $price->roundPrice($diff) < 0.01)) {
                    $total['rounding_currency'] += $diff;
                    //	$total['taxrecapitulation']['rounding_currency']	+= $diff;


                }
                $total['brutto_currency'] = $price->roundPrice($bruttoCurrency);
                //	$total['taxrecapitulation']['brutto_currency'] 			= $price->roundPrice($bruttoCurrency);


                $bruttoCurrencyTax = round($total['brutto_currency_tax'], (int)$rounding_calculation_total, $rounding_calculation);
                $diffTax           = $price->roundPrice($bruttoCurrencyTax) - $price->roundPrice($total['brutto_currency_tax']);

                if (!($price->roundPrice($diffTax) > -0.01 && $price->roundPrice($diffTax) < 0.01)) {
                    //$total['rounding_currency']							+= $diff;
                    $total['taxrecapitulation']['rounding_currency'] += $diffTax;


                }
                $total['brutto_currency_tax']                  = $price->roundPrice($bruttoCurrencyTax);
                $total['taxrecapitulation']['brutto_currency'] = $price->roundPrice($bruttoCurrencyTax);

            }


            // We store default to database, so run it always
            // 2b) ROUNDING DEFAULT CURRENCY
            $brutto = round($total['brutto'], (int)$rounding_calculation_total, $rounding_calculation);
            $diff   = $price->roundPrice($brutto) - $price->roundPrice($total['brutto']);
            if (!($price->roundPrice($diff) > -0.01 && $price->roundPrice($diff) < 0.01)) {
                $total['rounding'] += $diff;
                //$total['taxrecapitulation']['rounding']		+= $diff;
            }
            $total['brutto'] = $price->roundPrice($brutto);
            //$total['taxrecapitulation']['brutto']			= $price->roundPrice($brutto);

            $bruttoTax = round($total['brutto_tax'], (int)$rounding_calculation_total, $rounding_calculation);

            $diffTax = $price->roundPrice($bruttoTax) - $price->roundPrice($total['brutto_tax']);
            if (!($price->roundPrice($diffTax) > -0.01 && $price->roundPrice($diffTax) < 0.01)) {
                //$total['rounding']							+= $diff;
                $total['taxrecapitulation']['rounding'] += $diffTax;
            }
            $total['brutto_tax']                  = $price->roundPrice($bruttoTax);
            $total['taxrecapitulation']['brutto'] = $price->roundPrice($bruttoTax);

        }

        // Final correction
        // Correct float, so we can compare to zero
        if (isset($total['rounding'])) {
            if ($price->roundPrice($total['rounding']) > -0.01 && $price->roundPrice($total['rounding']) < 0.01) {
                $total['rounding'] = (int)0;
            }
        }

        // Correct float, so we can compare to zero
        if (isset($total['rounding_currency'])) {
            if ($price->roundPrice($total['rounding_currency']) > -0.01 && $price->roundPrice($total['rounding_currency']) < 0.01) {
                $total['rounding_currency'] = (int)0;
            }
        }

        // Correct float, so we can compare to zero
        if (isset($total['brutto'])) {
            if ($price->roundPrice($total['brutto']) > -0.01 && $price->roundPrice($total['brutto']) < 0.01) {
                $total['brutto'] = (int)0;
            }
        }

        // Correct float, so we can compare to zero
        if (isset($total['brutto_currency'])) {
            if ($price->roundPrice($total['brutto_currency']) > -0.01 && $price->roundPrice($total['brutto_currency']) < 0.01) {
                $total['brutto_currency'] = (int)0;
            }
        }

        // Correct float, so we can compare to zero
        if (isset($total['taxrecapitulation']['rounding'])) {
            if ($price->roundPrice($total['taxrecapitulation']['rounding']) > -0.01 && $price->roundPrice($total['taxrecapitulation']['rounding']) < 0.01) {
                $total['taxrecapitulation']['rounding'] = (int)0;
            }
        }

        // Correct float, so we can compare to zero
        if (isset($total['taxrecapitulation']['rounding_currency'])) {
            if ($price->roundPrice($total['taxrecapitulation']['rounding_currency']) > -0.01 && $price->roundPrice($total['taxrecapitulation']['rounding_currency']) < 0.01) {
                $total['taxrecapitulation']['rounding_currency'] = (int)0;
            }
        }


        $total['taxrecapitulation']['brutto_incl_rounding']          = $total['taxrecapitulation']['brutto'];
        $total['taxrecapitulation']['brutto_currency_incl_rounding'] = $total['taxrecapitulation']['brutto_currency'];
        if ($rounding_calculation_total == -1) {

            // If not rounded here: e.g. 0.87 -> 1, 0.93 -> 1
            // We need to count standard rounding 0.8666 -> 0.87 (if rounded here, such will be included)

            if ($price->roundPrice($total['taxrecapitulation']['rounding']) > 0) {
                $total['taxrecapitulation']['brutto_incl_rounding'] = $total['taxrecapitulation']['brutto'] + $price->roundPrice($total['taxrecapitulation']['rounding']);
            }


            if ($price->roundPrice($total['taxrecapitulation']['rounding_currency']) > 0) {
                $total['taxrecapitulation']['brutto_currency_incl_rounding'] = $total['taxrecapitulation']['brutto_currency'] + $price->roundPrice($total['taxrecapitulation']['rounding_currency']);
            }
        }


        return true;

    }

    /*
    public function resetVariables(&$fullItems, $levels = array()) {
        if (!empty($levels)) {
            foreach($levels as $k => $v) {
                if (!empty($fullItems[$k])) {
                    foreach($fullItems[$k] as $k => $v) {
                        $fullItems[$k]['discountproduct'] 		= 0;
                        $fullItems[$k]['discountcartfixedid'] 	= 0;
                        $fullItems[$k]['discounttitle'] 		= '';
                    }
                }
            }
        }
    }*/


    public static function taxRecapitulation(&$total, $shippingCosts, $paymentCosts) {


        if (empty($total)) {
            return;
        }

        //$app						= Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        //$dynamic_tax_rate			= $paramsC->get( 'dynamic_tax_rate', 0 );
        $tax_calculation          = $paramsC->get('tax_calculation', 0);
        $tax_calculation_shipping = $paramsC->get('tax_calculation_shipping', 0);
        $tax_calculation_payment  = $paramsC->get('tax_calculation_payment', 0);

        // 0 ... don't fix anything
        // 1 ... fix TAX RECAPITULATION - when total brutto will change, change the total brutto and rounding for CART CALCULATION
        // 2 ... fix TAX RECAPITULATION - fix taxes in CART CALCULATION - total brutto and rounding will change even
        $tax_recapitulation = $paramsC->get('tax_recapitulation', 0);
        $round              = 1;
        $currencyRate       = PhocacartCurrency::getCurrentCurrencyRateIfNotDefault();
        $price              = new PhocacartPrice();

        $total['taxrecapitulation'] = array();


        $total['taxrecapitulation']['brutto'] = 0;

        $total['taxrecapitulation']['netto']         = 0;
        $total['taxrecapitulation']['netto_incl_sp'] = 0; // netto including shipping and payment costs
        $total['taxrecapitulation']['tax']           = 0;
        $total['taxrecapitulation']['rounding']      = 0;

        $total['taxrecapitulation']['items'] = array();
        //	$total['taxrecapitulation']['diffbrutto']		= 0;// is cart calculation brutto different to tax recapitulation
        //	$total['taxrecapitulation']['corrected']		= 0;
        //$total['taxrecapitulation']['corrected_currency']= 0;

        // Currency
        $total['taxrecapitulation']['currency_rate']     = $currencyRate;
        $total['taxrecapitulation']['brutto_currency'] = 0;
        $total['taxrecapitulation']['rounding_currency'] = 0;

        $total['taxrecapitulation']['brutto_incl_rounding']          = 0;
        $total['taxrecapitulation']['brutto_currency_incl_rounding'] = 0;

        $total['taxsum'] = 0;// Sum all taxes in total so we can compare them because of rounding with $total['taxrecapitulation']['tax']


        // We compare $total['taxrecapitulation']['brutto'] and $total['brutto'] at the end BUT
        // there are 3 SETTINGS: Tax for products, Tax for Shipping and Tax for Payment $total['brutto'] can include amount of products which don't not belong to tax
        // $total['brutto_tax'] is brutt but only for taxable items (products, shipping, payment)
        $total['brutto_tax']          = $total['brutto'];
        $total['brutto_currency_tax'] = $total['brutto_currency'];
        if ($tax_calculation == 0) {
            $total['brutto_tax']          -= $total['netto'];
            $total['brutto_currency_tax'] -= $price->roundPrice($total['netto'] * $currencyRate);
        }


        if (!empty($total['tax']) && $tax_calculation > 0) {
            foreach ($total['tax'] as $k => $v) {

                $total['taxsum'] += $v['tax'];
                $netto           = $v['netto'];
                $tax             = $price->roundPrice($v['tax']);
                $brutto          = $v['brutto'];
                $rate            = $price->roundPrice($v['rate']);
                $taxId           = $v['taxid'];
                $taxHide         = $v['taxhide'];

                //$bruttoCurrency = $price->roundPrice(($v['brutto'] * $currencyRate));

                if ($tax_recapitulation > 0) {
                    // Tax
                    // - Tax changed by country/region is set
                    // - $tax_calculation: no/inclusive/exclusive (set in options)
                    // - $v['type']: fixed/percentage (set in product)

                    // NO TAX
                    if ($tax_calculation == 0) {
                        $tax = 0;
                        // EXCLUSIVE TAX
                    } else if ($tax_calculation == 1) {
                        if ($v['type'] == 2) { // FIX
                            $brutto = $netto + $tax;

                        } else { // Percentage
                            $tax = $netto * ($rate / 100);
                            if ($round == 1) {
                                $tax = $price->roundPrice($tax);
                            }
                            $brutto = $netto + $tax;
                        }
                        // INCLUSIVE TAX
                    } else if ($tax_calculation == 2) {
                        if ($v['type'] == 2) { // FIX
                            $netto = $brutto - $tax;
                        } else { // Percentage

                            $tax = $brutto - ($brutto / (($rate / 100) + 1));
                            if ($round == 1) {
                                $tax = $price->roundPrice($tax);
                            }
                            $netto = $brutto - $tax;
                        }
                    }
                }

                if ($round == 1) {
                    $netto  = $price->roundPrice($netto);
                    $brutto = $price->roundPrice($brutto);
                    $tax    = $price->roundPrice($tax);
                }


                $total['taxrecapitulation']['items'][$k]['title']              = $v['title'];
                $total['taxrecapitulation']['items'][$k]['title_lang'] = $v['title_lang'];
                $total['taxrecapitulation']['items'][$k]['title_lang_suffix2'] = $v['title_lang_suffix2'];
                $total['taxrecapitulation']['items'][$k]['netto']              = $netto;
                $total['taxrecapitulation']['items'][$k]['tax']                = $tax;
                $total['taxrecapitulation']['items'][$k]['brutto']             = $brutto;
                $total['taxrecapitulation']['items'][$k]['taxid']               = $taxId;
                $total['taxrecapitulation']['items'][$k]['taxhide']             = $taxHide;

                // Currency
                //	$bruttoCurrency = $price->roundPrice($netto * $currencyRate) + $price->roundPrice($tax * $currencyRate);
                //	$total['taxrecapitulation']['items'][$k]['brutto_currency'] = $bruttoCurrency;

                $total['taxrecapitulation']['brutto'] += $brutto;// changed when tax inclusive
                $total['taxrecapitulation']['netto']  += $netto; // changed when tax exclusive
                $total['taxrecapitulation']['tax']    += $tax;   // changed when tax exclusive

                // Currency
                //$total['taxrecapitulation']['brutto_currency'] 		+= $bruttoCurrency;

                if ($tax_recapitulation == 2) {
                    $total['tax'][$k]['netto']  = $netto;
                    $total['tax'][$k]['tax']   = $tax;
                    $total['tax'][$k]['brutto'] = $brutto;
                    //$total['tax'][$k]['brutto_currency']	= $bruttoCurrency;
                }


                if ($tax_recapitulation == 2) {
                    //$total['taxrecapitulation']['rounding'] += ($price->roundPrice($total['tax'][$k]['tax']) - $price->roundPrice($total['taxrecapitulation']['items'][$k]['tax']));
                }
            }

            if ($tax_recapitulation == 2) {
                //$total['taxrecapitulation']['rounding'] += ($price->roundPrice($total['netto']) - $price->roundPrice($total['taxrecapitulation']['netto']));
            }
        }


        $total['taxrecapitulation']['netto_incl_sp'] = $total['taxrecapitulation']['netto'];


        // Shipping
        if ($tax_calculation_shipping == 0 && isset($shippingCosts['brutto'])) {
            $total['brutto_tax']          -= $shippingCosts['brutto'];
            $total['brutto_currency_tax'] -= $price->roundPrice($shippingCosts['brutto'] * $currencyRate);
        }

        if (isset($shippingCosts['taxkey']) && (int)$shippingCosts['taxkey']) {

            $netto  = $shippingCosts['netto'];
            $tax   = $shippingCosts['tax'];
            $brutto = $shippingCosts['brutto'];
            $taxkey = $shippingCosts['taxkey'];

             $taxId = $shippingCosts['taxid'];
             $taxHide = $shippingCosts['taxhide'];


            // Nothing to fix
            /*if ($correct_tax_recapitulation == 1) {

                if ($v['type'] == 1) {
                    $tax 	= $price->roundPrice($netto * $shippingCosts['taxrate'] / 100);
                    $brutto = $shippingCosts['netto'] + $tax;
                } else {
                    $brutto = $shippingCosts['netto'] + $tax;
                }
            }*/

            if (isset($total['taxrecapitulation']['items'][$taxkey]['netto'])) {
                $total['taxrecapitulation']['items'][$taxkey]['netto'] += $netto;
                $total['taxrecapitulation']['netto_incl_sp']           += $netto;
                //	$total['taxrecapitulation']['netto'] 					+= $netto;
            } else {
                $total['taxrecapitulation']['items'][$taxkey]['title']              = $shippingCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang'] = $shippingCosts['tax_title_lang'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang_suffix'] = $shippingCosts['tax_title_suffix'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang_suffix2'] = $shippingCosts['tax_title_suffix2'] != '' ? '(' . $shippingCosts['tax_title_suffix2'] . ')' : '';
                $total['taxrecapitulation']['items'][$taxkey]['netto']              = $netto;
                $total['taxrecapitulation']['netto_incl_sp']                        = $netto;
                $total['taxrecapitulation']['items'][$taxkey]['taxid']               = $taxId;
                $total['taxrecapitulation']['items'][$taxkey]['taxhide']             = $taxHide;

                //	$total['taxrecapitulation']['netto'] 					= $netto;
            }

            if (isset($total['taxrecapitulation']['items'][$taxkey]['tax'])) {
                $total['taxrecapitulation']['items'][$taxkey]['tax'] += $tax;
                $total['taxrecapitulation']['tax']                   += $tax;
            } else {
                //$total['taxrecapitulation']['items'][$taxkey]['title'] 	= $shippingCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['tax'] = $tax;
                $total['taxrecapitulation']['tax']                   += $tax;
            }

            if (isset($total['taxrecapitulation']['items'][$taxkey]['brutto'])) {
                $total['taxrecapitulation']['items'][$taxkey]['brutto'] += $brutto;
                //	$total['taxrecapitulation']['items'][$taxkey]['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);

                $total['taxrecapitulation']['brutto'] += $brutto;
                //	$total['taxrecapitulation']['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);
            } else {
                //$total['taxrecapitulation']['items'][$taxkey]['title'] 	= $shippingCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['brutto'] = $brutto;
                //	$total['taxrecapitulation']['items'][$taxkey]['brutto_currency'] 	= $price->roundPrice($brutto * $currencyRate);

                $total['taxrecapitulation']['brutto'] += $brutto;
                //	$total['taxrecapitulation']['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);
            }
        }


        // Payment
        if ($tax_calculation_payment == 0 && isset($paymentCosts['brutto'])) {
            $total['brutto_tax']          -= $paymentCosts['brutto'];
            $total['brutto_currency_tax'] -= $price->roundPrice($paymentCosts['brutto'] * $currencyRate);
        }

        if (isset($paymentCosts['taxkey']) && (int)$paymentCosts['taxkey']) {

            $netto  = $paymentCosts['netto'];
            $tax   = $paymentCosts['tax'];
            $brutto = $paymentCosts['brutto'];
            $taxkey = $paymentCosts['taxkey'];
            $taxId = $paymentCosts['taxid'];
             $taxHide = $paymentCosts['taxhide'];

            /*if ($correct_tax_recapitulation == 1) {

                if ($v['type'] == 1) {
                    $tax 	= $price->roundPrice($netto * $paymentCosts['taxrate'] / 100);
                    $brutto = $paymentCosts['netto'] + $tax;
                } else {
                    $brutto = $paymentCosts['netto'] + $tax;
                }
            }*/

            if (isset($total['taxrecapitulation']['items'][$taxkey]['netto'])) {
                $total['taxrecapitulation']['items'][$taxkey]['netto'] += $netto;
                $total['taxrecapitulation']['netto_incl_sp']           += $netto;
                //$total['taxrecapitulation']['netto_incl_sp'] 			+= $netto;
            } else {
                $total['taxrecapitulation']['items'][$taxkey]['title']              = $paymentCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang'] = $paymentCosts['tax_title_lang'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang_suffix'] = $paymentCosts['tax_title_suffix'];
                $total['taxrecapitulation']['items'][$taxkey]['title_lang_suffix2'] = $paymentCosts['tax_title_suffix2'] != '' ? '(' . $paymentCosts['tax_title_suffix2'] . ')' : '';
                $total['taxrecapitulation']['items'][$taxkey]['netto']              = $netto;
                $total['taxrecapitulation']['netto_incl_sp']                        = $netto;
                $total['taxrecapitulation']['items'][$taxkey]['taxid']               = $taxId;
                $total['taxrecapitulation']['items'][$taxkey]['taxhide']             = $taxHide;
                //$total['taxrecapitulation']['netto'] 					= $netto;
            }

            if (isset($total['taxrecapitulation']['items'][$taxkey]['tax'])) {
                $total['taxrecapitulation']['items'][$taxkey]['tax'] += $tax;
                $total['taxrecapitulation']['tax']                   += $tax;
            } else {
                //$total['taxrecapitulation']['items'][$taxkey]['title'] 	= $paymentCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['tax'] = $tax;
                $total['taxrecapitulation']['tax']                   += $tax;
            }

            if (isset($total['taxrecapitulation']['items'][$taxkey]['brutto'])) {
                $total['taxrecapitulation']['items'][$taxkey]['brutto'] += $brutto;
                //	$total['taxrecapitulation']['items'][$taxkey]['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);

                $total['taxrecapitulation']['brutto'] += $brutto;
                //	$total['taxrecapitulation']['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);
            } else {
                //$total['taxrecapitulation']['items'][$taxkey]['title'] 	= $paymentCosts['taxtxt'];
                $total['taxrecapitulation']['items'][$taxkey]['brutto'] = $brutto;
                //$total['taxrecapitulation']['items'][$taxkey]['brutto_currency'] 	= $price->roundPrice($brutto * $currencyRate);

                $total['taxrecapitulation']['brutto'] += $brutto;
                //	$total['taxrecapitulation']['brutto_currency'] 	+= $price->roundPrice($brutto * $currencyRate);
            }

        }

        // Currency is counted with shipping and payment
        if (!empty($total['taxrecapitulation']['items'])) {
            foreach ($total['taxrecapitulation']['items'] as $k => $v) {
                $bruttoCurrency                                             = $price->roundPrice($v['netto'] * $currencyRate) + $price->roundPrice($v['tax'] * $currencyRate);
                $total['taxrecapitulation']['items'][$k]['brutto_currency'] = $bruttoCurrency;
                $total['taxrecapitulation']['brutto_currency']              += $bruttoCurrency;
            }
        }


        // MUST BE RUN AFTER PAYMENT AND SHIPPING - BRUTTO INCLUDES Payment and Shipping methods
        if ($tax_recapitulation > 0) {
            // There are two options
            // 1) tax_recapitulation == 1: Fix tax recapitulation but not change cart calculation
            // 2) tax_recapitulation == 2: Fix tax recapitulation and change cart calculation (fixing means that tax is countent from sum not from each product items - different rounding
            //
            // In case of 1) when TOTAL BRUTTO is smaller than FIXED TOTAL BRUTTO - we need to add rounding
            // In case of 2) when TOTAL BRUTTO is smaller than FIXED TOTAL BRUTTO - we don't need to do rounding as we fixed whole CART CALCULATION - so no need to fix as both are same

            // BRUTTO MUST BE ALWAYS THE SAME - so when fixed brutto in tax recapitulation is larger, we need to change the cart calculation brutto
            //                                  but not when the BRUTTO is larger than tax recapitulation brutto

            if ($tax_recapitulation > 0) {
                // BRUTTO STANDARD

                if ($total['brutto_tax'] > $total['taxrecapitulation']['brutto']) {
                    $total['taxrecapitulation']['rounding'] += ($price->roundPrice($total['brutto_tax']) - $price->roundPrice($total['taxrecapitulation']['brutto']));
                }/* else if ($total['brutto'] < $total['taxrecapitulation']['brutto']) {
					if ($tax_recapitulation == 1) {
						//	$total['taxrecapitulation']['rounding'] 	+= $price->roundPrice($total['taxrecapitulation']['brutto']) - $price->roundPrice($total['brutto']);
					}
					// Can be set for $tax_recapitulation == 2
					$total['brutto'] = $price->roundPrice($total['taxrecapitulation']['brutto']);
					$total['brutto_tax'] = $price->roundPrice($total['taxrecapitulation']['brutto']);

				}*/
                if ($total['brutto'] < $total['taxrecapitulation']['brutto']) {
                    $total['brutto'] = $price->roundPrice($total['taxrecapitulation']['brutto']);
                }
                if ($total['brutto_tax'] < $total['taxrecapitulation']['brutto']) {
                    $total['brutto_tax'] = $price->roundPrice($total['taxrecapitulation']['brutto']);
                }
            }

            // Currency
            if ($tax_recapitulation > 0) {
                // BRUTTO STANDARD
                if ($total['brutto_currency_tax'] > $total['taxrecapitulation']['brutto_currency']) {
                    $total['taxrecapitulation']['rounding_currency'] += ($price->roundPrice($total['brutto_currency_tax']) - $price->roundPrice($total['taxrecapitulation']['brutto_currency']));

                } /*else if ($total['brutto_currency'] < $total['taxrecapitulation']['brutto_currency']) {
					if ($tax_recapitulation == 1) {
						//	$total['taxrecapitulation']['rounding_currency'] 	+= $price->roundPrice($total['taxrecapitulation']['brutto_currency']) - $price->roundPrice($total['brutto_currency']);
					}
					$total['brutto_currency'] = $price->roundPrice($total['taxrecapitulation']['brutto_currency']);
					$total['brutto_currency_tax'] = $price->roundPrice($total['taxrecapitulation']['brutto_currency']);
				}*/
                if ($total['brutto_currency'] < $total['taxrecapitulation']['brutto_currency']) {
                    $total['brutto_currency'] = $price->roundPrice($total['taxrecapitulation']['brutto_currency']);
                }
                if ($total['brutto_currency_tax'] < $total['taxrecapitulation']['brutto_currency']) {
                    $total['brutto_currency_tax'] = $price->roundPrice($total['taxrecapitulation']['brutto_currency']);
                }
            }
        }


    }


    public function calculateShipping($priceI, &$total) {

        if (!isset($total['brutto'])) {
            return false;
        }
        if (isset($priceI['brutto']) && $priceI['brutto'] > 0) {
            $total['brutto'] += $priceI['brutto'];
        } else if (isset($priceI['netto']) && $priceI['netto'] > 0 && isset($priceI['tax']) && $priceI['tax'] > 0) {
            $total['brutto'] += ($priceI['netto'] + $priceI['tax']);
        }
    }

    public function calculatePayment($priceI, &$total) {

        if (!isset($total['brutto'])) {
            return false;
        }

        if (isset($priceI['brutto']) && $priceI['brutto'] > 0) {
            $total['brutto'] += $priceI['brutto'];
        } else if (isset($priceI['netto']) && $priceI['netto'] > 0 && isset($priceI['tax']) && $priceI['tax'] > 0) {
            $total['brutto'] += ($priceI['netto'] + $priceI['tax']);
        }
    }
}

?>
