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
use Joomla\CMS\Component\ComponentHelper;

class PhocacartSubmit {


	public static function isAllowedToSubmit() {

	    $paramsC 			            = ComponentHelper::getParams('com_phocacart') ;
        $submit_item_registered_only 	= $paramsC->get( 'submit_item_registered_only', 1 );

        $user				= PhocacartUser::getUser();



		$allowed = false;
		if ($submit_item_registered_only == 0) {
			$allowed =  true;
		} else if ($submit_item_registered_only == 1 && (int)$user->id > 0) {
		    $allowed =  true;
        }

		return $allowed;

    }


    /* Product item form fields:
    title,
    alias,
    sku,
    upc,
    ean,
    jan,
    isbn,
    mpn,
    serial_number,
    registration_key,
    external_id,
    external_key,
    external_link,
    external_text,
    external_link2,
    external_text2,
    price,
    price_original,
    tax_id,
    catid_multiple,
    manufacturer_id,
    description,
    description_long,
    features,
    image,
    video,
    type,
    unit_amount,
    unit_unit,
    length,
    width,
    height,
    weight,
    volume,
    condition,
    type_feed,
    type_category_feed,
    delivery_date,
    metatitle,
    metakey,
    metadesc,
    date,
    date_update,
    tags,
    taglabels

    Contact item form fields:

    name*, email, phone, message
    */
}
