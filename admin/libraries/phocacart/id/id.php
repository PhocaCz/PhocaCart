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

class PhocacartId
{

    /* Change ID (EAN, SKU, ...) based on Advanced Stock Management */
	public function getIdItemsChangedByAttributes(&$item, $attributes, $ajax = 0) {


      //  $paramsC            = PhocacartUtils::getComponentParameters();

      //  $fullAttributes = array();// Array of integers only
        $thinAttributes = array();// Array of full objects (full options object)
        if ($ajax == 1) {
           // $fullAttributes = PhocacartAttribute::getAttributeFullValues($attributes);
            $thinAttributes = $attributes;
        } else {
           // $fullAttributes = $attributes;
            $thinAttributes = PhocacartAttribute::getAttributesSelectedOnly($attributes);
        }


        // Stock Calculation
        // 0 ... Main Product
        // 1 ... Product Variations
        // 2 ... Advanced Stock Management
        // 3 ... Advanced Stock and Price Management

         if ($item->stock_calculation == 2 || $item->stock_calculation == 3) {


            // Advanced Stock Management
            $k       = PhocacartProduct::getProductKey((int)$item->id, $thinAttributes);
            $dataASM = PhocacartAttribute::getCombinationsDataByKey($k);


            if (isset($dataASM['sku']) && $dataASM['sku'] != '') {
                $item->sku = $dataASM['sku'];
            }

            if (isset($dataASM['ean']) && $dataASM['ean'] != '') {
                $item->ean = $dataASM['ean'];
            }

        }
    }

}
