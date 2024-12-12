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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartShipping
{

	protected $type = array(0,1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

	public function __construct() {

	}

	public function setType($type = array(0,1)) {

		$this->type = $type;
	}

	/*
	 * Be aware:
	 * if id > 0 ... it test the selected shipping method and return it if OK
	 * if id = 0 ... it tests all shipping methods they meet the criteria and return all to list them (e.g. in checkout)
	 * Always test for the id before using this function
	 */

	public function getPossibleShippingMethods($amountNetto, $amountBrutto, $quantity, $country, $region, $zip, $weight, $length, $width, $height, $id = 0, $selected = 0) {
		$paramsC = PhocacartUtils::getComponentParameters();
		$shipping_amount_rule = $paramsC->get( 'shipping_amount_rule', 0 );

		$user 			= PhocacartUser::getUser();
		$userLevels		= implode (',', $user->getAuthorisedViewLevels());
		$userGroups 	= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		$db 			= Factory::getDBO();

		$wheres	  		= array();
		// ACCESS
		$wheres[] = " s.published = 1";
		$wheres[] = " s.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";


		if (!empty($this->type) && is_array($this->type)) {
			// if type is empty, then all types are asked
			$wheres[] = " s.type IN (" . implode(',', $this->type) . ')';
		}
		if ((int)$id > 0) {
			$wheres[] =  's.id = '.(int)$id;
			$limit = ' LIMIT 1';
			//$group = '';
		} else {
			$limit = '';

		}

		$columns = 's.id, s.tax_id, s.cost, s.cost_additional, s.calculation_type, s.image, s.access, s.method, s.zip,'
			. ' s.active_amount, s.active_quantity, s.active_zone, s.active_country, s.active_region, s.active_zip,'
			. ' s.active_weight, s.active_size,'
			. ' s.lowest_amount, s.highest_amount, s.minimal_quantity, s.maximal_quantity, s.lowest_weight,'
			. ' s.highest_weight, s.default,'
			. ' s.minimal_length, s.minimal_width, s.minimal_height, s.maximal_length, s.maximal_width, s.maximal_height, s.params,'
			. ' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.tax_hide as taxhide,'
			. ' GROUP_CONCAT(DISTINCT r.region_id) AS region,'
			. ' GROUP_CONCAT(DISTINCT c.country_id) AS country,'
			. ' GROUP_CONCAT(DISTINCT z.zone_id) AS zone';

		$groupsFull = 's.id, s.tax_id, s.cost, s.cost_additional, s.calculation_type, s.image, s.access, s.method, s.zip,'
			. ' s.active_amount, s.active_quantity, s.active_zone, s.active_country, s.active_region, s.active_zip,'
			. ' s.active_weight, s.active_size,'
			. ' s.lowest_amount, s.highest_amount, s.minimal_quantity, s.maximal_quantity, s.lowest_weight,'
			. ' s.minimal_length, s.minimal_width, s.minimal_height, s.maximal_length, s.maximal_width, s.maximal_height, s.params,'
			. ' s.highest_weight, s.default,'
			. ' t.id, t.title, t.tax_rate, t.calculation_type, t.tax_hide as taxhide';

		/*if (I18nHelper::useI18n()) {
			$columns .= ', coalesce(i18n_s.title, s.title) as title, coalesce(i18n_s.description, s.description) as description';
			$groupsFull .= ', coalesce(i18n_s.title, s.title), coalesce(i18n_s.description, s.description)';
		} else {
			$columns .= ', s.title, s.description';
			$groupsFull .= ', s.title, s.description';
		}*/

		$columns .= I18nHelper::sqlCoalesce(['title', 'description'], 's', '', '', ',');
		$columns .= I18nHelper::sqlCoalesce(['title'], 't', 'tax', '', ',');

		$groupsFull .= ', s.title, s.description';
		$groupsFast		= 's.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;



		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

		$query = ' SELECT '.$columns
				.' FROM #__phocacart_shipping_methods AS s'
				. I18nHelper::sqlJoin('#__phocacart_shipping_methods_i18n', 's')
				.' LEFT JOIN #__phocacart_shipping_method_regions AS r ON r.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_shipping_method_countries AS c ON c.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_shipping_method_zones AS z ON z.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = s.tax_id'
				. I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't')
				.' LEFT JOIN #__phocacart_item_groups AS ga ON s.id = ga.item_id AND ga.type = 7'// type 8 is payment
				. $where
				. ' GROUP BY '.$groups
				. ' ORDER BY s.ordering'
				. $limit;

		PhocacartUtils::setConcatCharCount();
		$db->setQuery($query);

		$shippings = $db->loadObjectList();

		/*if (empty($shippings)) {
			return false;
		}*/
		if (!empty($shippings) && !isset($shippings[0]->id) || (isset($shippings[0]->id) && (int)$shippings[0]->id < 1)) {
			return false;
		}

		$i = 0;

		if (!empty($shippings)) {
			foreach($shippings as $k => $v) {

				if (isset($v->taxhide)) {
					$registry = new Registry($v->taxhide);
					$v->taxhide = $registry->toArray();
				}

				$v->active = 0;
				$v->selected = 0;
				$a = 0;
				$q = 0;
				$z = 0;
				$c = 0;
				$r = 0;
				$zi = 0;
				$w = 0;
				$s = 0;
				// Amount Rule
				if($v->active_amount == 1) {


					if ($shipping_amount_rule == 0 || $shipping_amount_rule == 2) {
						// No tax, brutto
						if ($amountBrutto >= $v->lowest_amount && $amountBrutto <= $v->highest_amount) {
							$a = 1;
						}

					} else if ($shipping_amount_rule == 1) {
						// Netto
						if ($amountNetto >= $v->lowest_amount && $amountNetto <= $v->highest_amount) {
							$a = 1;
						}

					}

				} else {
					$a = 1;
				}


				// Quantity Rule
				if($v->active_quantity == 1) {
					if ($quantity >= $v->minimal_quantity && $quantity <= $v->maximal_quantity) {
						$q = 1;
					}
				} else {
					$q = 1;
				}

				// Zone Rule
				if($v->active_zone == 1) {
					if (isset($v->zone) && $v->zone != '')  {
						$zones = explode(',', $v->zone);

						if (PhocacartZone::isCountryOrRegionIncluded($zones, (int)$country, (int)$region)) {
							$z = 1;
						}
					}

				} else {
					$z = 1;
				}

				// Country Rule
				if($v->active_country == 1) {
					if (isset($v->country) && $v->country != '') {
						$countries = explode(',', $v->country);
						if (in_array((int)$country, $countries)) {
							$c = 1;
						}
					}

				} else {
					$c = 1;
				}

				// Region Rule
				if($v->active_region == 1) {
					if (isset($v->region) && $v->region != '') {
						$regions = explode(',', $v->region);
						if (in_array((int)$region, $regions)) {
							$r = 1;
						}
					}
				} else {
					$r = 1;
				}

				// ZIP Rule
				if($v->active_zip == 1) {
					if (isset($v->zip) && $v->zip != '') {
						$zips = array_map('trim', explode(',', $v->zip));

						if (in_array((int)$zip, $zips)) {
							$zi = 1;

						}
					}
				} else {
					$zi = 1;
				}

				// Weight Rule
				if($v->active_weight == 1) {
					if (($weight >= $v->lowest_weight || $weight == $v->lowest_weight)
						&& ($weight <= $v->highest_weight || $weight == $v->highest_weight)) {
						$w = 1;
					}

				} else {
					$w = 1;
				}



				// Size Rule
				if($v->active_size == 1) {
					$sP = 0;
					if (($length >= $v->minimal_length || $length == $v->minimal_length)
						&& ($length <= $v->maximal_length || $length == $v->maximal_length)) {

						$sP++;

					}

					if (($width >= $v->minimal_width || $width == $v->minimal_width)
						&& ($width <= $v->maximal_width || $width == $v->maximal_width)) {

						$sP++;
					}

					if (($height >= $v->minimal_height || $height == $v->minimal_height)
						&& ($height <= $v->maximal_height || $height == $v->maximal_height)) {

						$sP++;
					}

					if ($sP == 3) {
						$s = 1;
					}
				} else {
					$s = 1;
				}



				// No rule was set for shipping, it will be displayed at all events
				if($v->active_amount == 0 && $v->active_quantity == 0 && $v->active_country == 0 && $v->active_region == 0 && $v->active_zip == 0 && $v->active_weight == 0) {
					$v->active = 1;
				}

				// if some of the rules is not valid, all the payment is NOT valid

				if ($a == 0 || $q == 0 || $z == 0 || $c == 0 || $r == 0 || $zi == 0 || $w == 0 || $s == 0) {
					$v->active = 0;
				} else {
					$v->active = 1;
				}

				if ($v->active == 0) {
					if (isset($shippings[$i])) {
						unset($shippings[$i]);
					}
				} else {
					// Shipping is active but shipping method plugin can deactivate it
					$active = true;
					Dispatcher::dispatch(new Event\Shipping\BeforeShowPossibleShippingMethod($active, $v, [
						'pluginname' => $v->method,
					]));

					if (!$active && isset($shippings[$i])) {
						unset($shippings[$i]);
					}
				}

				// Try to set default for frontend form
				// If user selected some shipping, such will be set as default
				// If not then the default will be set
				if ((int)$selected > 0) {
					if ((int)$v->id == (int)$selected) {
						$v->selected = 1;
					}
				} else {
					$v->selected = $v->default;
				}


				$i++;
			}

		}

		return $shippings;

	}

	/*public function checkAndGetShippingMethodInsideCart($id, $total) {

		if ((int)$id > 0 && !empty($total)) {
			return $this->checkAndGetShippingMethods($id, 0, $total);
		}
		return false;

	}*/


	/**
	 * Check current shipping method
	 * Shipping method must be selected
	 * @param number $id
	 * @return boolean|array
	 */
	public function checkAndGetShippingMethod($id = 0, $total = array()) {

		if ((int)$id > 0) {
			return $this->checkAndGetShippingMethods($id, 0, $total);
		}
		return false;

	}

	/**
	 * Check current shipping method or all methods they meet criteria to be selected
	 * @param number $selectedShippingId
	 * @param number $selected
	 * @return boolean|array
	 */

	public function checkAndGetShippingMethods($selectedShippingId = 0, $selected = 0, $total = array()) {


		if (empty($total)) {
			$cart					= new PhocacartCartRendercheckout();
			$cart->setType($this->type);
			$cart->setFullItems();
			$total					= $cart->getTotal();
			$totalFinal				= $total[0];
			//$currentShippingId 		= $cart->getShippingId();
		} else {
			$totalFinal				= $total;
		}
		$user					= PhocacartUser::getUser();
		$data					= PhocacartUser::getUserData((int)$user->id);
		$fields 				= PhocacartFormUser::getFormXml('', '_phs', 1,1,0);

		$dataAddress = array();
		if (!empty($data)) {
			$dataAddress	= PhocacartUser::getAddressDataOutput($data, $fields['array'], $user);
		} else {
            // Is this guest user
            $guest = PhocacartUserGuestuser::getGuestUser();
            if ($guest) {
                $data 			= PhocacartUserGuestuser::getUserAddressGuest();
                $dataAddress	= PhocacartUser::getAddressDataOutput($data, $fields['array'], $user, 1);
            }

        }


		$country 	= $this->getUserCountryShipping($dataAddress);
		$region 	= $this->getUserRegionShipping($dataAddress);
		$zip 		= $this->getUserZipShipping($dataAddress);
		/*$country = 0;
		if(isset($dataAddress['bcountry']) && (int)$dataAddress['bcountry']) {
			$country = (int)$dataAddress['bcountry'];
		}

		$region = 0;
		if(isset($dataAddress['bregion']) && (int)$dataAddress['bregion']) {
			$region = (int)$dataAddress['bregion'];
		}*/

		$shippingMethods	= $this->getPossibleShippingMethods($totalFinal['subtotalnetto'], $totalFinal['subtotalbrutto'], $totalFinal['quantity'], $country, $region, $zip, $totalFinal['weight'], $totalFinal['length'], $totalFinal['width'], $totalFinal['height'], $selectedShippingId, $selected);


		if (!empty($shippingMethods)) {
			return $shippingMethods;
		}
		return false;

	}

	public static function getUserCountryShipping($dataAddress) {

		$pC = PhocacartUtils::getComponentParameters();
        $shipping_country_rule = $pC->get('shipping_country_rule', 1);
		$delivery_billing_same_rules = $pC->get('delivery_billing_same_rules', 1);

        $country = 0;

		// Before we start to check, if user set that billing and shipping
		// address is the same, this in fact means that
		// shipping country, shipping region and shipping zip is zero
		// even we still store some shipping info for possible future orders
		if ($delivery_billing_same_rules == 1 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['scountry'] = isset($dataAddress['bcountry']) ? (int)$dataAddress['bcountry'] : 0;
		}
		if ($delivery_billing_same_rules == 2 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['scountry'] = 0;
		}

        switch($shipping_country_rule) {

			case 2:
				if(isset($dataAddress['scountry']) && (int)$dataAddress['scountry']) {
					$country = (int)$dataAddress['scountry'];
				}
			break;

			case 3:
				if(isset($dataAddress['bcountry']) && (int)$dataAddress['bcountry']) {
					$country = (int)$dataAddress['bcountry'];
				} else if(isset($dataAddress['scountry']) && (int)$dataAddress['scountry']) {
					$country = (int)$dataAddress['scountry'];
				}
			break;

			case 4:
				if(isset($dataAddress['scountry']) && (int)$dataAddress['scountry']) {
					$country = (int)$dataAddress['scountry'];
				} else if(isset($dataAddress['bcountry']) && (int)$dataAddress['bcountry']) {
					$country = (int)$dataAddress['bcountry'];
				}
			break;

			case 1:
			default:
				if(isset($dataAddress['bcountry']) && (int)$dataAddress['bcountry']) {
					$country = (int)$dataAddress['bcountry'];
				}
			break;

		}


		return $country;
	}

	public static function getUserRegionShipping($dataAddress) {

		$pC = PhocacartUtils::getComponentParameters();
        $shipping_region_rule = $pC->get('shipping_region_rule', 1);
		$delivery_billing_same_rules = $pC->get('delivery_billing_same_rules', 1);

        $region = 0;

		// Before we start to check, if user set that billing and shipping
		// address is the same, this in fact means that
		// shipping country, shipping region and shipping zip is zero
		// even we still store some shipping info for possible future orders
		if ($delivery_billing_same_rules == 1 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['sregion'] = isset($dataAddress['bregion']) ? (int)$dataAddress['bregion'] : 0;
		}
		if ($delivery_billing_same_rules == 2 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['sregion'] = 0;
		}

        switch($shipping_region_rule) {

			case 2:
				if(isset($dataAddress['sregion']) && (int)$dataAddress['sregion']) {
					$region = (int)$dataAddress['sregion'];
				}
			break;

			case 3:
				if(isset($dataAddress['bregion']) && (int)$dataAddress['bregion']) {
					$region = (int)$dataAddress['bregion'];
				} else if(isset($dataAddress['sregion']) && (int)$dataAddress['sregion']) {
					$region = (int)$dataAddress['sregion'];
				}
			break;

			case 4:
				if(isset($dataAddress['sregion']) && (int)$dataAddress['sregion']) {
					$region = (int)$dataAddress['sregion'];
				} else if(isset($dataAddress['bregion']) && (int)$dataAddress['bregion']) {
					$region = (int)$dataAddress['bregion'];
				}
			break;

			case 1:
			default:
				if(isset($dataAddress['bregion']) && (int)$dataAddress['bregion']) {
					$region = (int)$dataAddress['bregion'];
				}
			break;

		}

		return $region;
	}

	public static function getUserZipShipping($dataAddress) {

		$pC = PhocacartUtils::getComponentParameters();
        $shipping_zip_rule = $pC->get('shipping_zip_rule', 1);
		$delivery_billing_same_rules = $pC->get('delivery_billing_same_rules', 1);

        $zip = '';


		// Before we start to check, if user set that billing and shipping
		// address is the same, this in fact means that
		// shipping country, shipping region and shipping zip is zero
		// even we still store some shipping info for possible future orders
		if ($delivery_billing_same_rules == 1 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['szip'] = isset($dataAddress['bzip']) ? (int)$dataAddress['bzip'] : '';
		}
		if ($delivery_billing_same_rules == 2 && isset($dataAddress['bsch']) && ((int)$dataAddress['bsch'] == 1)) {
			$dataAddress['szip'] = '';
		}

        switch($shipping_zip_rule) {

			case 2:
				if(isset($dataAddress['szip']) && $dataAddress['szip']) {
					$zip = $dataAddress['szip'];
				}
			break;

			case 3:
				if(isset($dataAddress['bzip']) && $dataAddress['bzip']) {
					$zip = $dataAddress['bzip'];
				} else if(isset($dataAddress['szip']) && $dataAddress['szip']) {
					$zip = $dataAddress['szip'];
				}
			break;

			case 4:
				if(isset($dataAddress['szip']) && $dataAddress['szip']) {
					$zip = $dataAddress['szip'];
				} else if(isset($dataAddress['bzip']) && $dataAddress['bzip']) {
					$zip = $dataAddress['bzip'];
				}
			break;

			case 1:
			default:
				if(isset($dataAddress['bzip']) && $dataAddress['bzip']) {
					$zip = $dataAddress['bzip'];
				}
			break;

		}

		return $zip;
	}



	public function getShippingMethod($shippingId, $total = [])
	{
		$db = Factory::getDBO();

		/*if (I18nHelper::useI18n()) {
			$columns = 'coalesce(i18n_s.title, s.title) as title, coalesce(i18n_s.description, s.description) as description';
		} else {
			$columns = 's.title, s.description';
		}*/

		$columns = I18nHelper::sqlCoalesce(['title', 'description'], 's');

		$taxId = 's.tax_id';
		$taxIdColumn = 's.tax_id';

		// CHANGE TAX OF SHIPPING BASED ON PRODUCT TAXES - set the highest rate, cannot mix percentage and fixed value taxes
		if (!empty($total['tax'])) {

			$query = 'SELECT change_tax FROM #__phocacart_shipping_methods WHERE id = '.(int)$shippingId . ' ORDER BY id  LIMIT 1';
			$db->setQuery($query);
			$change_tax = $db->loadResult();

			if (isset($change_tax) && (int)$change_tax == 1) {

				$highestTaxRateId = 0;
				$highestTaxRate   = -1;

				foreach ($total['tax'] as $k => $v) {
					if (isset($v['taxid']) && $v['rate']) {
						if ((float)$v['rate'] > (float)$highestTaxRate) {
							$highestTaxRateId = $v['taxid'];
							$highestTaxRate   = $v['rate'];
						}
					}
				}
				if ((int)$highestTaxRateId > 0 || (int)$highestTaxRateId == 0) {
					$taxId       = (int)$highestTaxRateId;
					$taxIdColumn = (int)$highestTaxRateId . ' AS tax_id';
				}
			}
		}


		$query = ' SELECT s.id, '.$taxIdColumn.', s.cost, s.cost_additional, s.calculation_type, s.method, s.params, s.image,'
				.' t.id as taxid, '.I18nHelper::sqlCoalesce(['title'], 't', 'tax').', t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.tax_hide as taxhide'
				.', ' . $columns
				.' FROM #__phocacart_shipping_methods AS s'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = '.$taxId
				. I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't')
				. I18nHelper::sqlJoin('#__phocacart_shipping_methods_i18n', 's')
				.' WHERE s.id = '.(int)$shippingId
				.' ORDER BY s.id'
				.' LIMIT 1';
		$db->setQuery($query);

		$shipping = $db->loadObject();

		if (isset($shipping->params)) {
			$registry = new Registry;
			//$registry->loadString($shipping->params);
			if (isset($shipping->params)) {
     			$registry->loadString($shipping->params);
			}
			$shipping->params = $registry;
		}

        if (isset($shipping->taxhide)) {
			$registry = new Registry($shipping->taxhide);
			$shipping->taxhide = $registry->toArray();
		}

		return $shipping;
	}

	/* Used as payment rule */
	public static function getShippingMethods($paymentId, $select = 0, $table = 'payment') {

		if ($table == 'payment') {
			$t = '#__phocacart_payment_method_shipping';
			$c = 'payment_id';
		}

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT p.shipping_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_shipping_methods AS a'
				.' LEFT JOIN '.$t.' AS p ON a.id = p.shipping_id'
			    .' WHERE p.'.$c.' = '.(int) $paymentId;
		$db->setQuery($query);
		if ($select == 1) {
			$items = $db->loadColumn();
		} else {
			$items = $db->loadObjectList();
		}

		return $items;
	}

	/**
	 * Store shipping id inside checkout - if enabled in parameters and there is only one valid shipping, it can be directly stored
	 * But then the cart needs to be reloaded to store the costs of the shipping and make ready for payment (payment gets info about shipping because of rules)
	 * @param $shippingId
	 * @param $userId
	 */

	public function storeShippingRegistered($shippingId, $userId)
	{

		$row = Table::getInstance('PhocacartCart', 'Table');


		if ((int)$userId > 0) {
			if (!$row->load(array('user_id' => (int)$userId, 'vendor_id' => 0, 'ticket_id' => 0, 'unit_id' => 0, 'section_id' => 0))) {
				return false;// there must be some info in cart yet
			}
		}

		if (!empty($row->cart)) {

			$data['shipping'] = (int)$shippingId;
			$data['user_id'] = (int)$userId;

			if (!$row->bind($data)) {
				$this->setError($row->getError());
				return false;
			}

			$row->date = gmdate('Y-m-d H:i:s');

			if (!$row->check()) {
				$this->setError($row->getError());
				return false;
			}


			if (!$row->store()) {
				$this->setError($row->getError());
				return false;
			}
			return (int)$shippingId;
		}

		return false;
	}


	/* Used as payment rule too
	 * Used in administration (this is why $type = array();
	 */
	public static function getAllShippingMethodsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id', $type = array() ) {

		$db =Factory::getDBO();

		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_shipping_methods AS a';

		$query .= !empty($type) && is_array($type) ? ' WHERE a.type IN ('. implode(',', $type). ')' : '';
		$query .= ' ORDER BY a.'. $order;

		$db->setQuery($query);
		$methods = $db->loadObjectList();


		$methodsO = HTMLHelper::_('select.genericlist', $methods, $name, 'class="form-select" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

		return $methodsO;
	}

	public static function getAllShippingMethods($order = 'id', $type = array() ) {
		$db = Factory::getDBO();

		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_shipping_methods AS a';

		$query .= !empty($type) && is_array($type) ? ' WHERE a.type IN ('. implode(',', $type). ')' : '';
		$query .= ' ORDER BY a.'. $order;

		$db->setQuery($query);
		$methods = $db->loadObjectList();

		return $methods;
	}

	/* used as payment rule*/
	public static function storeShippingMethods($shippingsArray, $id, $table = 'payment') {

		if ($table == 'payment') {
			$t = '#__phocacart_payment_method_shipping';
			$c = 'payment_id';
		}

		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();

			if (!empty($shippingsArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($shippingsArray as $k => $v) {
					//$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
					// No multidimensional in J4
					$values[] = ' ('.(int)$id.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO '.$t.' ('.$c.', shipping_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}


	/*
	 * Important function - when e.g. user changes the address or change the items in cart, the shipping method
	 * needs to be removed, because user can get shipping advantage when he orders 10 items but after changing
	 * cart to e.g. one item, shipping cannot stay the same, the same happens with countries and region
	 */

	public static function removeShipping($type = 0) {

		if ($type == 0 || $type == 1) {

			$session 		= Factory::getSession();
			$session->set('guestshipping', false, 'phocaCart');
			$session->set('guestshippingparams', false, 'phocaCart');
		}

		if ($type == 0) {
			$db = Factory::getDBO();
			$user = array();
			$vendor = array();
			$ticket = array();
			$unit = array();
			$section = array();
			$dUser = PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section);

			$pos_shipping_force = 0;
			if (PhocacartPos::isPos()) {
				$app = Factory::getApplication();
				$paramsC = PhocacartUtils::getComponentParameters();
				$pos_shipping_force = $paramsC->get('pos_shipping_force', 0);
				if ((int)$pos_shipping_force > 0) {
					$pos_shipping_force = PhocacartShipping::isShippingMethodActive($pos_shipping_force) === true ? (int)$pos_shipping_force : 0;
				}
			}



			$query = 'UPDATE #__phocacart_cart_multiple SET shipping = ' . (int)$pos_shipping_force;

			// Remove shipping params too
			if ((int)$pos_shipping_force == 0) {
				$query .= ', params_shipping = \'\'';
			}


				$query .= ' WHERE user_id = ' . (int)$user->id
				. ' AND vendor_id = ' . (int)$vendor->id
				. ' AND ticket_id = ' . (int)$ticket->id
				. ' AND unit_id = ' . (int)$unit->id
				. ' AND section_id = ' . (int)$section->id;
			$db->setQuery($query);

			$db->execute();
		}
		return true;
	}

	/* Checkout - is there even some shipping NOT is used reverse
	 * This function is different to getPossibleShippingMethods()
	 *
	 * getPossibleShippingMethods - all methods they fit the criterias (e.g. amount rule, contry rule, etc.)
	 * isShippingNotUsed() - all existing methods in shop which are published
	 *
	 * IF NO SHIPPPING METHOD EXIST - it is ignored when 1) skip_shipping_method parameter is enabled 2) all products are digital and skip_shipping_method is enabled
	 *
	 * */
	public static function isShippingNotUsed($options = array()) {

		$paramsC 		= PhocacartUtils::getComponentParameters();
		$skip_shipping_method	= $paramsC->get( 'skip_shipping_method', 0 );

		// 1) TEST IF ANY SHIPPING METHOD EXISTS
		$db =Factory::getDBO();
		$query = 'SELECT a.id'
				.' FROM #__phocacart_shipping_methods AS a'
				.' WHERE a.published = 1'
				.' AND a.type IN (0, 1)'
				.' ORDER BY id LIMIT 1';
		$db->setQuery($query);
		$methods = $db->loadObjectList();

		if (empty($methods) && $skip_shipping_method == 2) {
			return true;
		}

		// 2) TEST IF SHIPPING METHOD IS NOT DISABLED FOR ALL DOWNLOADABLE PRODUCTS
		// PRODUCTTYPE tested
		if (isset($options['all_digital_products']) &&  $options['all_digital_products'] == 1 && $skip_shipping_method == 1) {
			return true;
		}

		return false;
	}

	/*
	 * Get all PCS Plugins
	 */
	public static function getShippingPluginMethods($namePlugin = '') {

		$plugin = array();
		$plugin['name'] = $namePlugin;
		$plugin['group'] = 'pcs';
		$plugin['title'] = 'Phoca Cart Shipping';
		$plugin['selecttitle'] = Text::_('COM_PHOCACART_SELECT_SHIPPING_METHOD');
		$plugin['returnform'] = 1;

		return PhocacartPlugin::getPluginMethods($plugin);

	}

	/*
	 * Used in POS - we can define forced shipping method in Global Configuration
	 * But if user unpublish this method, we need to test it
	*/

	public static function isShippingMethodActive($id) {


		$db =Factory::getDBO();

		$query = 'SELECT a.id'
				.' FROM #__phocacart_shipping_methods AS a'
				.' WHERE a.published = 1'
				.' AND a.type IN (0,2)'// IT IS A POS (0 common, 2 POS)
				.' AND a.id = '.(int)$id
				.' ORDER BY id LIMIT 1';
		$db->setQuery($query);
		$method = $db->loadResult();

		if ((int)$method > 0) {
			return true;
		}


		return false;
	}

	public static function getInfoDescriptionById($id) {

		if ((int)$id > 0) {
			$db =Factory::getDBO();

			$query = 'SELECT a.description_info'
					.' FROM #__phocacart_shipping_methods AS a'
					.' WHERE a.published = 1'
					.' AND a.type IN (0,2)'// IT IS A POS (0 common, 2 POS)
					.' AND a.id = '.(int)$id
					.' ORDER BY id LIMIT 1';
			$db->setQuery($query);
			return $db->loadResult();
		}
	}


	/**
	 * @param $methodName
	 * @param int $return 1 ... Association list, 2 ... Object list, 3 ... ID (be aware when setting 3, only first ID will be returned even more methods with the same method name can exist)
	 * @param bool $onlyPublished
	 * @return mixed
	 */

	protected function getShippingMethodIdByMethodName($methodName, $return = 3, $onlyPublished = false) {

		$db = Factory::getDBO();
		$query = ' SELECT s.id'
		.' FROM #__phocacart_shipping_methods AS s'
		.' WHERE s.method = '.$db->quote($methodName);

		if ($onlyPublished) {
			$query .= ' AND s.published = 1';
		}

		$query .= ' ORDER BY s.id';

		if ($return == 3) {
			$query .= ' LIMIT 1';
		}

		$db->setQuery($query);

		if ($return == 1) {
			return $db->loadAssocList();
		} else if ($return == 2) {
			return $db->loadObjectList();
		} else if ($return == 3) {
			$result = (array) $db->loadObject();
			return $result["id"];
		}

		return false;
	}
}
?>
