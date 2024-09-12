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

namespace Phoca\PhocaCart\User;

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class AdvancedACL
{
	private static array $permissionsMap = [
		'phocacartattributea' => 'phocacart.products',
		'phocacartbulkprice' => 'phocacart.bulkprices',
		'phocacartbulkprices' => 'phocacart.bulkprices',
		'phocacartcart' => 'phocacart.users',
		'phocacartcatalogs' => 'phocacart.categories',
		'phocacartcategories' => 'phocacart.categories',
		'phocacartcategory' => 'phocacart.categories',
        'phocacartcontenttype' => 'phocacart.contenttypes',
        'phocacartcontenttypes' => 'phocacart.contenttypes',
		'phocacartcountries' => 'phocacart.countries',
		'phocacartcountry' => 'phocacart.countries',
		'phocacartcoupon' => 'phocacart.coupons',
		'phocacartcoupons' => 'phocacart.coupons',
		'phocacartcouponview' => 'phocacart.coupons',
		'phocacartcurrencies' => 'phocacart.currencies',
		'phocacartcurrency' => 'phocacart.currencies',
		'phocacartdiscount' => 'phocacart.discounts',
		'phocacartdiscounts' => 'phocacart.discounts',
		'phocacartdownload' => 'phocacart.downloads',
		'phocacartdownloads' => 'phocacart.downloads',
		'phocacarteditbulkprice' => 'phocacart.bulkprices',
		'phocacarteditcurrentattributesoptions' => 'phocacart.products',
		'phocacarteditproductpointgroup' => 'phocacart.products',
		'phocacarteditproductpricegroup' => 'phocacart.products',
		'phocacarteditproductpricehistory' => 'phocacart.products',
		'phocacarteditstatus' => 'phocacart.orders',
		'phocacarteditstockadvanced' => 'phocacart.products',
		'phocacartedittax' => 'phocacart.countries',
		'phocacartexports' => 'phocacart.exports',
		'phocacartextensions' => 'phocacart.extensions',
		'phocacartfeed' => 'phocacart.feeds',
		'phocacartfeeds' => 'phocacart.feeds',
		'phocacartformfield' => 'phocacart.formfields',
		'phocacartformfields' => 'phocacart.formfields',
		'phocacartgroup' => 'phocacart.groups',
		'phocacartgroups' => 'phocacart.groups',
		'phocacarthits' => 'phocacart.hits',
		'phocacartimagea' => 'phocacart.products',
		'phocacartimports' => 'phocacart.imports',
		'phocacartitem' => 'phocacart.products',
		'phocacartitema' => 'phocacart.products',
		'phocacartitems' => 'phocacart.products',
		'phocacartlogs' => 'phocacart.logs',
		'phocacartmanufacturer' => 'phocacart.manufacturers',
		'phocacartmanufacturers' => 'phocacart.manufacturers',
		'phocacartorder' => 'phocacart.orders',
		'phocacartorders' => 'phocacart.orders',
		'phocacartorderview' => 'phocacart.orders',
		'phocacartparameter' => 'phocacart.parameters',
		'phocacartparameters' => 'phocacart.parameters',
		'phocacartparametervalue' => 'phocacart.parameters',
		'phocacartparametervalues' => 'phocacart.parameters',
		'phocacartpayment' => 'phocacart.payments',
		'phocacartpayments' => 'phocacart.payments',
		'phocacartquestion' => 'phocacart.questions',
		'phocacartquestions' => 'phocacart.questions',
		'phocacartregion' => 'phocacart.countries',
		'phocacartregions' => 'phocacart.countries',
		'phocacartreports' => 'phocacart.reports',
		'phocacartreview' => 'phocacart.reviews',
		'phocacartreviews' => 'phocacart.reviews',
		'phocacartreward' => 'phocacart.rewards',
		'phocacartrewards' => 'phocacart.rewards',
		'phocacartsection' => 'phocacart.sections',
		'phocacartsections' => 'phocacart.sections',
		'phocacartshipping' => 'phocacart.shippings',
		'phocacartshippings' => 'phocacart.shippings',
		'phocacartspecification' => 'phocacart.specifications',
		'phocacartspecifications' => 'phocacart.specifications',
		'phocacartstatistics' => 'phocacart.statistics',
		'phocacartstatus' => 'phocacart.statuses',
		'phocacartstatuses' => 'phocacart.statuses',
		'phocacartstockstatus' => 'phocacart.stockstatuses',
		'phocacartstockstatuses' => 'phocacart.stockstatuses',
		'phocacartsubmititem' => 'phocacart.submititems',
		'phocacartsubmititems' => 'phocacart.submititems',
		'phocacarttag' => 'phocacart.tags',
		'phocacarttags' => 'phocacart.tags',
		'phocacarttax' => 'phocacart.taxes',
		'phocacarttaxes' => 'phocacart.taxes',
		'phocacarttime' => 'phocacart.times',
		'phocacarttimes' => 'phocacart.times',
		'phocacartunit' => 'phocacart.sections',
		'phocacartunits' => 'phocacart.sections',
		'phocacartunittests' => 'phocacart.',
		'phocacartuser' => 'phocacart.users',
		'phocacartusers' => 'phocacart.users',
		'phocacartvendor' => 'phocacart.vendors',
		'phocacartvendors' => 'phocacart.vendors',
		'phocacartwishlist' => 'phocacart.wishlists',
		'phocacartwishlists' => 'phocacart.wishlists',
		'phocacartzone' => 'phocacart.countries',
		'phocacartzones' => 'phocacart.countries',
	];

	public static function getActionFromView(string $view): ?string
	{
		if (isset(static::$permissionsMap[$view])) {
			return static::$permissionsMap[$view];
		}

		return null;
	}

	public static function authorise(string $action): bool
	{
		$params = \PhocacartUtils::getComponentParameters();
		if (!$params->get('use_advanced_permissions')) {
			return true;
		}

		$user = Factory::getApplication()->getIdentity();
		return $user->authorise($action, 'com_phocacart');
	}
}
