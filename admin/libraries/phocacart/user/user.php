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

class PhocacartUser
{

	/*protected $guest = array();

	protected $user 	= array();
	protected $vendor	= array();
	protected $ticket	= array();
	protected $pos		= false;*/


	/*public function __construct($pos) {


		$this->user		= Factory::getUser();
		$this->pos		= $pos;
	}*/


	public static function getUser($id = 0) {

		$app         = Factory::getApplication();
		$pUser		= $vendor = $ticket = $unit	= $section = array();
		$isVendor	= false;

		if ((int)$id > 0) {
			$jUser = Factory::getUser($id);
			return $jUser;
		} else {
			if (PhocacartPos::isPos()) {
				$isVendor = self::defineUser($pUser, $vendor, $ticket, $unit, $section);
				if ($isVendor) {
					// Current Joomla! User is Vendor (in POS)
					// Check if this vendor has selected some customer
					// If yes: switch the selected user as current Joomla! user
					//         for example to check access rights: Vendor selected User A
					//         so we must check the rights of User A and not of Vendor
					if (!empty($pUser)) {
						// ======= LOGGED JOOMLA! USER IS VENDOR - CUSTOMER SELECTED
						return $pUser;
					} else {
						// ======= LOGGED JOOMLA! USER IS VENDOR - NO CUSTOMER SELECTED
						return false;// Joomla! User is vendor and he/she didn't select any customer
					}
				} else {
					// ======= LOGGED JOOMLA! USER IS NOT VENDOR - BUT IN POS WE NEED VENDOR
					return false;
				}
			} else if ($app->getName() == 'administrator') {

				// ======= LOGGED JOOMLA! USER IS ADMINISTRATOR
				// We want to get information about user's cart in administration
				$userid    = $app->input->get('userid', 0, 'int');
				//$vendorid  = $app->input->get('vendorid', 0, 'int');
				//$ticketid  = $app->input->get('ticketid', 0, 'int');
				//$unitid    = $app->input->get('unitid', 0, 'int');
				//$sectionid = $app->input->get('sectionid', 0, 'int');

				// Test if we ask POS user
				//$userIdPos = PhocacartPos::getUserIdByVendorAndTicket($vendorid, $ticketid, $unitid, $sectionid);

				if ((int)$userid > 0) {
					// ======= LOGGED JOOMLA! USER IS ADMINISTRATOR - CUSTOMER FOUND
					return Factory::getUser($userid);
				} else {
					// ======= LOGGED JOOMLA! USER IS ADMINISTRATOR - NO CUSTOMER FOUND
					return Factory::getUser(0);// Joomla! User is administrator and there is no information about user
				}


			} else {
				// ======= LOGGED JOOMLA! USER IS CUSTOMER
				// No POS, return standard Joomla! User
				$jUser = Factory::getUser();
				return $jUser;
			}
		}
		// ======= NO LOGGED JOOMLA! USER
		return false;
	}

	public static function defineUser(&$user, &$vendor, &$ticket, &$unit, &$section, $forcePos = 0) {

		$pos		= PhocacartPos::isPos($forcePos);

		$user		= Factory::getUser();
		$vendor 	= array();
		$vendor		= new stdClass();
		$vendor->id = 0;

		$ticket 	= array();
		$ticket		= new stdClass();
		$ticket->id = 0;

		$unit 	= array();
		$unit		= new stdClass();
		$unit->id = 0;

		$section 	= array();
		$section	= new stdClass();
		$section->id = 0;

		if ($pos == 1) {

			// Is logged in user a vendor?
			if (!empty($user) && (int)$user->id > 0) {

				$vendor 	= clone $user;
				$isVendor	= PhocacartVendor::isVendor($vendor);

				if ($isVendor) {
					//unset($user); cannot unset $user as we lost it for reference
					$user 			= array();
					$user 			= new stdClass();
					$ticketA		= PhocacartTicket::getTicket($vendor->id);
					$ticket->id 	= $ticketA['ticketid'];
					$unit->id 		= $ticketA['unitid'];
					$section->id 	= $ticketA['sectionid'];
					$userId 		= PhocacartPos::getUserIdByVendorAndTicket($vendor->id, $ticket->id, $unit->id, $section->id);
					$user			= Factory::getUser($userId);
					return true;
				} else {
					$vendor = array();
					$vendor	= new stdClass();
					$vendor->id = 0;
					return false;
				}
				return false;
			}
			return false;
		}
		return false;
	}


	public static function getUserIdByCard($card) {

		$db = Factory::getDBO();

		$query = ' SELECT a.user_id FROM #__phocacart_users AS a'
			    .' WHERE a.loyalty_card_number = '.$db->quote($card)
				.' AND a.type = 0'
				.' LIMIT 1';


		$db->setQuery($query);
		$id = $db->loadResult();
		return $id;


	}


	public static function getAddressDataForm($form, $fields, $user, $billingSuffix = '', $shippingSuffix = '_phs', $guestUser = 0) {

		$o['b']			= '';// Output Billing
		$o['s'] 		= '';// Output Shipping
		$o['bsch']		= '';// B S Checked? Is the billing address the same like shipping address
		$o['filled']	= 1; // Is every form input filled out

		$app	= Factory::getApplication();

		$s = PhocacartRenderStyle::getStyles();

		$baSa = $form->getValue('ba_sa');



		// Setting "Delivery and billing addresses are the same" - enable or disabled
		// This can be set for new users who didn't set the address yet and for gest only. Not for users how added the address as they made the choice which is saved and cannot be changed
		$pC = PhocacartUtils::getComponentParameters();
        $delivery_billing_same_enabled = $pC->get('delivery_billing_same_enabled', 0);
		$form_design_type = $pC->get('form_design_type', '');

        // 1) GUEST USER (no preference stored)
		if ($guestUser > 0 && !PhocacartUserGuestuser::getAddress() && ($delivery_billing_same_enabled == 2 || $delivery_billing_same_enabled == 3)) {
			$o['bsch'] = 'checked="checked"';
		}

		// 1) NEW LOGGED IN USER (no preference stored)
		$userIdAddress = $form->getValue('id');
		if (!(int)$userIdAddress > 0 && !$guestUser && ($delivery_billing_same_enabled == 1 || $delivery_billing_same_enabled == 3)){
			$o['bsch'] = 'checked="checked"';
		}

		// 3) STANDARD USER WHO ADDED ADDRESS AND PREFERENCES WERE SAVED - now this is only stored preference of the user
		if ($baSa == 1) {
			$o['bsch'] = 'checked="checked"';
		}

		// This feature is completely disabled
		if ($delivery_billing_same_enabled == -1) {
			$o['bsch'] = '';
		}


		if (!empty($fields)) {
			foreach($fields as $k => $v) {

				if ($v->display_billing == 1 || ($app->isClient('administrator') && $v->title == 'id')){

					$value = $form->getValue($v->title);// If form input is required but it is empty, this is wrong

					if($v->type == 'checkbox:text') {
						// Checkbox field can have set default value e.g. to 1 - that in default it is enabled
						// so such checkbox is set as checked as default so if we stored that it is disabled, we need to remove checked
						if(!$value || $value == false || $value == 0 || $value == null || is_null($value)) {
							$form->setFieldAttribute($v->title. $billingSuffix, 'checked', 'false');
						}
					}

					if ($v->required == 1 && $value == '') {
						$o['filled'] = 0;
					}

					if ($v->title == 'email' && $guestUser == 0) {
						$form->setValue($v->title. $billingSuffix, null, $user->email );
					}

					if (!$app->isClient('administrator')) {

						// change general xml class to general variable
						$input = str_replace('form-control', $s['c']['inputbox.form-control'], $form->getInput($v->title . $billingSuffix));
						$input = str_replace('form-select', $s['c']['inputbox.form-select'], $input);


						if ($form_design_type == 'ph-form-placeholder'){
							// When input is not required we cannot select empty input field by CSS so we help us with setting value:
							// FROM: .ph-form-input-box input:not(:focus):valid ~ label TO: .ph-form-input-box input.form-control:not([value='']) ~ label in main CSS
							// Having all inputs required, such hack is not needed
							$input = str_replace('<input', '<input onkeyup="this.setAttribute(\'value\', this.value);" ', $input);

							$o['b'] .= '<div class="ph-form-group ph-form-group-'.$v->title.'">';
							$o['b'] .= '<div class="ph-form-input-box">'.$input.'';
							$o['b'] .= $form->getLabel($v->title . $billingSuffix);
							$o['b'] .= '</div>' . "\n";
							$o['b'] .= '</div>' . "\n";
						} else {
							$o['b'] .= '<div class="' . $s['c']['row'] . ' ' . $s['c']['form-group'] . '">';
							$o['b'] .= '<div class="' . $s['c']['col.xs12.sm5.md5'] . ' ' . $s['c']['control-label'] . '">' . $form->getLabel($v->title . $billingSuffix) . '</div>';
							$o['b'] .= '<div class="' . $s['c']['col.xs12.sm7.md7'] . '">' . $input . '</div>';
							$o['b'] .= '</div>' . "\n";
						}

						/*
						$form->setFieldAttribute($v->title, 'hint', ' ');
						$o['b'] .= '<div class="'.$s['c']['row'].' '.$s['c']['form-group'].'">';
						$o['b'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$form->getInput($v->title . $billingSuffix) . '' .$form->getLabel($v->title . $billingSuffix).'</div>';
						$o['b'] .= '</div>' . "\n";*/

					} else {
						// Admin uses obsolete bootstrap
						$o['b'] .= '<div class="control-group">';
						$o['b'] .= '<div class="control-label"><label>'.$form->getLabel($v->title . $billingSuffix).'</label></div>';
						$o['b'] .= '<div class="controls">'.$form->getInput($v->title . $billingSuffix).'</div>';
						$o['b'] .= '</div>' . "\n";
					}

				}



				if ($v->display_shipping == 1 || ($app->isClient('administrator') && $v->title == 'id')) {

					$value = $form->getValue($v->title.$shippingSuffix);// Form input value is required but it is empty


					if($v->type == 'checkbox:text') {


						// Checkbox field can have set default value e.g. to 1 - that in default it is enabled
						// so such checkbox is set as checked as default so if we stored that it is disabled, we need to remove checked
						if(!$value || $value == false || $value == 0 || $value == null || is_null($value)) {


							$form->setFieldAttribute($v->title.$shippingSuffix, 'checked', 'false');

						}
					}

					if ($v->required == 1 && $value == '' && $baSa == 0) {  // and we have set that the shipping address
						$o['filled'] = 0;									// is other than billing
					}

					if ($v->title == 'email' && $guestUser == 0) {
						$form->setValue($v->title.$shippingSuffix, null, $user->email );
					}

					if (!$app->isClient('administrator')) {

						// change general xml class to general variable
						$input = str_replace('form-control', $s['c']['inputbox.form-control'], $form->getInput($v->title . $shippingSuffix));
						$input = str_replace('form-select', $s['c']['inputbox.form-select'], $input);

						if ($form_design_type == 'ph-form-placeholder'){
							// When input is not required we cannot select empty input field by CSS so we help us with setting value:
							// FROM: .ph-form-input-box input:not(:focus):valid ~ label TO: .ph-form-input-box input.form-control:not([value='']) ~ label in main CSS
							// Having all inputs required, such hack is not needed
							$input = str_replace('<input', '<input onkeyup="this.setAttribute(\'value\', this.value);" ', $input);

							$o['s'] .= '<div class="ph-form-group ph-form-group-'.$v->title.'">';
							$o['s'] .= '<div class="ph-form-input-box">'.$input.'';
							$o['s'] .= $form->getLabel($v->title . $shippingSuffix);
							$o['s'] .= '</div>' . "\n";
							$o['s'] .= '</div>' . "\n";
						} else {
							$o['s'] .= '<div class="' . $s['c']['row'] . ' ' . $s['c']['form-group'] . '">';
							$o['s'] .= '<div class="' . $s['c']['col.xs12.sm5.md5'] . ' ' . $s['c']['control-label'] . '">' . $form->getLabel($v->title . $shippingSuffix) . '</div>';
							$o['s'] .= '<div class="' . $s['c']['col.xs12.sm7.md7'] . '">' . $input . '</div>';
							$o['s'] .= '</div>' . "\n";
						}

						/*
						$form->setFieldAttribute($v->title, 'hint', ' ');
						$o['s'] .= '<div class="'.$s['c']['row'].' '.$s['c']['form-group'].'">';
						$o['s'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$form->getInput($v->title . $shippingSuffix) . '' .$form->getLabel($v->title . $shippingSuffix).'</div>';
						$o['s'] .= '</div>' . "\n";*/

					} else {
						// Admin uses obsolete bootstrap
						$o['s'] .= '<div class="control-group">';
						$o['s'] .= '<div class="control-label"><label>'.$form->getLabel($v->title . $shippingSuffix).'</label></div>';
						$o['s'] .= '<div class="controls">'.$form->getInput($v->title . $shippingSuffix).'</div>';
						$o['s'] .= '</div>' . "\n";
					}

				}

				// Possible way to change Billing and Shipping is the same in administration
				// Change the hidden field to Select box, so it can be changes in administration
				// BE AWARE - it is a billing parameter ($billingSuffix but displayed in Shipping tab: $o[s]
				if ($app->isClient('administrator') && $v->title == 'ba_sa') {
					$valueField = $form->getValue($v->title . $billingSuffix);
					$selectedYes = '';
					$selectedNo = ' selected';
					if ($valueField == 1) {
						$selectedYes = ' selected';
						$selectedNo = '';
					}
					$nameField = 'jform['.$v->title.$billingSuffix.']';
					$idField = 'jform_'.$v->title.$billingSuffix.'';
					$o['s'] .= '<div class="control-group">';
					$o['s'] .= '<div class="control-label"><label>'.Text::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS_IS_THE_SAME').'</label></div>';
					//$o['s'] .= '<div class="controls">'.$form->getInput($v->title . $billingSuffix).'</div>';
					$o['s'] .= '<div class="controls">';
					$o['s'] .= '<select name="'.$nameField.'" id="'.$idField.'" class="form-select">';
					$o['s'] .= '<option value="1" '.$selectedYes.'>'.Text::_('COM_PHOCACART_YES').'</option>';
					$o['s'] .= '<option value="0" '.$selectedNo.'>'.Text::_('COM_PHOCACART_NO').'</option>';
					$o['s'] .= '</select>' . "\n";
					$o['s'] .= '</div>' . "\n";
					$o['s'] .= '</div>' . "\n";
				}
			}
		}

		return $o;
	}

	public static function getAddressDataOutput($data, $fields, $user, $guestUser = 0) {

		$s = PhocacartRenderStyle::getStyles();

		$o['b']		= '';// Output Billing
		$o['s'] 	= '';// Output Shipping
		$o['bsch']	= '';// B S Checked? Is the billing address the same like shipping address
		$o['filled']= 1; // Is every form input filled out
		$bNameO		= '';// join first, middle, last name and degree
		$sNameO		= '';// join first, middle, last name and degree
		$o['bregion'] 	= '';// Return for shipping method (save database query)
		$o['bcountry']	= '';// Return for shipping method (save database query)
		$o['bzip']		= '';// Return for shipping method (save database query)

		$o['sregion'] 	= '';// Return for shipping method (save database query)
		$o['scountry']	= '';// Return for shipping method (save database query)
		$o['szip']		= '';// Return for shipping method (save database query)

		// ba_sa = 0: billing and shipping addresses are NOT the same
		// ba_sa = 1: billing and shipping addresses are same (don't check shipping fields)

		if (empty($data[0])) {
			// BILLING
			// no billing return false
			// No shipping data so test if we want any shipping data at all
			$fI		= new PhocacartFormItems();
			$active		= $fI->isFormFieldActive('billing');

			if ($active) {
				// there is active some form field, so vendor asks for some field but nothing was filled in
				$o['filled'] = 0;
				return $o;
			}

		} else if (!empty($data[0])
					&& isset($data[0]->ba_sa) && $data[0]->ba_sa == 0
					&& isset($data[0]->type) && $data[0]->type == 0
					&& empty($data[1])){
			// SHIPPING
			// In words - we have billing data, so we check if billing data are the same like shipping (ba_sa = 1)
			// If not then we check if we have shipping data, if not return false
			// And check if array 0 is really billing - array 0 (first array) cannot be shpping as we order it by type ASC in db query
			// type = 0 ... billing, type = 1 ... shipping

			// No shipping data so test if we want any shipping data at all
			$fI		= new PhocacartFormItems();
			$active		= $fI->isFormFieldActive('shipping');

			if ($active) {
				// there is active some form field, so vendor asks for some field but nothing was filled in
				$o['filled'] = 0;
				return $o;
			}

		}


		// Billing the same like shipping
		if (isset($data[0]->ba_sa) && $data[0]->ba_sa == 1){
			$o['bsch'] = 1;
		}

		if (!empty($fields)) {
			foreach($fields as $k => $v) {

				if ($v->display_billing == 1){

					$field  = (string)$v->title;
					if (isset($data[0]->$field)) {
						$value = $data[0]->$field;

						if ($v->required == 1 && $value == '') {
							$o['filled'] = 0;
							return $o;//Don't check and list any other, form it not complete
						}

						if ($v->title == 'email' && $guestUser == 0) {
							$value = $user->email;
						}


						if ($v->title == 'name_first' || $v->title == 'name_middle' || $v->title == 'name_last' || $v->title == 'name_degree' ) {
							$bNameO .= $value . ' ';
						} else if ($v->title == 'country') {
							$o['bcountry']	= (int)$value;// Return region and country
                            $countryTitle = isset($data[0]->countrytitle) ? $data[0]->countrytitle : PhocacartCountry::getCountryById((int)$value);
							$o['b'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$countryTitle.'</div>';
						} else if ($v->title == 'region') {
							$o['bregion']	= (int)$value;// Return region and country
                            $regionTitle = isset($data[0]->regiontitle) ? $data[0]->regiontitle : PhocacartRegion::getRegionById((int)$value);
							$o['b'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$regionTitle.'</div>';
						} else if ($v->title == 'zip') {
							$o['bzip']	= $value;// Return region and country or zip
							$o['b'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$value.'</div>';
						} else {
							$o['b'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$value.'</div>';
						}
					}
				}

				if ($v->display_shipping == 1) {

					$field  = (string)$v->title;
					if (isset($data[1]->$field)) {
						$value = $data[1]->$field;

						if ($v->required == 1 && $value == '' && $data[0]->ba_sa == 0 && $data[1]->ba_sa == 0) {
							$o['filled'] = 0;
							return $o;//Don't check and list any other, form it not complete
						}

						if ($v->title == 'email' && $guestUser == 0) {
							$value = $user->email;
						}


						if ($v->title == 'name_first' || $v->title == 'name_middle' || $v->title == 'name_last' || $v->title == 'name_degree' ) {
							$sNameO .= $value . ' ';
						} else if ($v->title == 'country') {
							$o['scountry']	= (int)$value;// Return region and country
                            $countryTitle = isset($data[1]->countrytitle) ? $data[1]->countrytitle : PhocacartCountry::getCountryById((int)$value);
							$o['s'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$countryTitle.'</div>';
						} else if ($v->title == 'region') {
							$o['sregion']	= (int)$value;// Return region and country
                            $regionTitle = isset($data[1]->regiontitle) ? $data[1]->regiontitle : PhocacartRegion::getRegionById((int)$value);
							$o['s'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$regionTitle.'</div>';
						} else if ($v->title == 'zip') {
							$o['szip']	= $value;// Return region and country or zip
							$o['s'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$value.'</div>';
						} else {
							$o['s'] .= '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$value.'</div>';
						}
					}

				}
			}

			if ($bNameO != '' && $o['b'] != '') {
				$o['b'] = '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$bNameO.'</div>' . $o['b'];
			}
			if ($sNameO != '' && $o['s'] != '') {
				$o['s'] = '<div class="'.$s['c']['col.xs12.sm12.md12'].'">'.$sNameO.'</div>' . $o['s'];
			}
		}

		return $o;
	}

	public static function getUserAddress($userId) {

		$db = Factory::getDBO();

		$query = ' (SELECT * FROM #__phocacart_users AS a'
			    .' WHERE a.user_id = '.(int) $userId
				.' AND a.type = 0'
				.' LIMIT 1)'
				.' UNION ALL'
				.' (SELECT * FROM #__phocacart_users AS a'
			    .' WHERE a.user_id = '.(int) $userId
				.' AND a.type = 1'
				.' LIMIT 1)';

		$db->setQuery($query);
		$address = $db->loadObjectList();
		return $address;

	}

	public static function getAllCheckboxesFromFormFields() {

		$db = Factory::getDBO();

		$query = ' SELECT title, published, display_billing, display_shipping, display_account  FROM #__phocacart_form_fields AS a'
			    .' WHERE a.published = 1'
				.' AND a.type = '.$db->quote('checkbox:text');
		$db->setQuery($query);
		$checkboxes = $db->loadObjectList();
		return $checkboxes;
	}

	public static function convertAddressTwo($data, $array = 1) {
		$dataNew	= array();
		if ($array == 1) {
			$dataNew[0] = array();// billing
			$dataNew[1]	= array();// shipping
			$dataNew[2] = array();// shipping including postfix _phs
		} else {
			$dataNew[0]= new StdClass();
			$dataNew[1]= new StdClass();
			$dataNew[2]= new StdClass();
		}

		if (!empty($data)) {
			foreach($data as $k => $v) {
				$pos = strpos($k, '_phs');
				if ($pos === false) {
					if ($array == 1) {
						$dataNew[0][$k] = $v;
					} else {
						$dataNew[0]->$k = $v;
					}
				} else {

					$kx = str_replace('_phs', '', $k);

					if ($array == 1) {
						$dataNew[1][$kx] = $v;
						$dataNew[2][$k] = $v;
					} else {
						$dataNew[1]->$kx = $v;
						$dataNew[2]->k = $v;
					}
				}
			}
		}

		if (!empty($dataNew[1])) {
			// Set right type for shipping address
			if ($array == 1) {
				$dataNew[1]['type'] = 1;
				$dataNew[2]['type'] = 1;
			} else {
				$dataNew[1]->type = 1;
				$dataNew[2]->type = 1;
			}
		}

		return $dataNew;
	}

	public static function getUserInfo() {

		$u				= array();
		$user 			= PhocacartUser::getUser();
		$u['username']	= '';
		$u['id']		= 0;
		$u['ip']		= '';

		if (isset($user->id)) {
			$u['id'] = $user->id;
		}

		if (isset($user->username)) {
			$u['username'] = $user->username;
		}

		$u['ip']		= PhocacartUtils::getIp();

		return $u;
	}


	public static function getUserOrderSum($userId) {

		$total = 0;
		if ($userId > 0) {
			$db = Factory::getDBO();

			$query = 'SELECT SUM(a.amount) FROM #__phocacart_order_total AS a'
				.' LEFT JOIN #__phocacart_orders AS o ON a.order_id = o.id'
				.' WHERE o.user_id = '.(int) $userId
				.' AND a.type = '.$db->quote('brutto');
			$db->setQuery($query);


			$total = $db->loadResult();

			if (!$total) {
				$total = 0;
			}
		}
		return $total;
	}

	public static function getUserData($userId = 0) {

		$db = Factory::getDBO();

		if ((int)$userId == 0) {
			$user 	= PhocacartUser::getUser();
			$userId = (int)$user->id;
		}

		if ((int)$userId > 0) {

			$query = 'SELECT u.*, r.title as regiontitle, c.title as countrytitle FROM #__phocacart_users AS u'
					.' LEFT JOIN #__phocacart_countries AS c ON c.id = u.country'
					.' LEFT JOIN #__phocacart_regions AS r ON r.id = u.region'
					.' WHERE u.user_id = '.(int)$userId
					.' ORDER BY u.type ASC';
			$db->setQuery($query);
			$data = $db->loadObjectList();

			return $data;
		}
		return false;
	}

	public static function buildName($nameFirst, $nameLast, $nameMiddle = '', $nameDegreePrefix = '', $nameDegreePostfix = '') {

		$name = '';

		if ($nameDegreePrefix != '') {
			$name = $nameDegreePrefix;
		}

		if ($nameFirst != '') {

			if ($name != '') {
				$name = $name . ' '. $nameFirst;
			} else {
				$name = $nameFirst;
			}
		}

		if ($nameMiddle != '') {

			if ($name != '') {
				$name = $name . ' '. $nameMiddle;
			} else {
				$name = $nameMiddle;
			}
		}

		if ($nameLast != '') {

			if ($name != '') {
				$name = $name . ' '. $nameLast;
			} else {
				$name = $nameLast;
			}
		}

		if ($nameDegreePostfix != '') {

			if ($name != '') {
				$name = $name . ' '. $nameDegreePostfix;
			} else {
				$name = $nameDegreePostfix;
			}
		}

		return $name;
	}




	/*
	$billing		= '';// form
	$shipping		= '';// form - edit the billing and shipping
	$billingO		= '';// output
	$shippingO		= '';// output - display billing and shipping
	$billNameO		= '';
	$shipNameO		= '';// complete the name on one row

	// Is the billing address the same like shipping address
	$bASaChecked = '';
	$baSa = $this->form->getValue('ba_sa');
	if ($baSa == 1) {
		$bASaChecked = 'checked="checked"';
	}
	// Is every form input value filled out? - if yes, don't display the form, if no display it.
	$filledOk = 1;

	if (!empty($this->fields)) {
		foreach($this->fields as $k => $v) {

			if ($v->display_billing == 1){

				// Form input field is required but it is empty, wrong
				$value = $this->form->getValue($v->title);
				if ($v->required == 1 && $value == '') {
					$filledOk = 0;
				}

				// Form
				$billing .= '<div class="form-group">'."\n"
				. '<div class="col-sm-5 control-label">'.$this->form->getLabel($v->title). '</div>';

				if ($v->title == 'email') {
					$this->form->setValue($v->title, null, $this->u->email );
				}
				$billing.= '<div class="col-sm-7">' . $this->form->getInput($v->title) . '</div>'
				.'</div>' . "\n";

				// Output
				if ($filledOk == 1 && $this->t['editaddress'] == 0) {
					//$billingO .= '<div class="col-sm-5">'.Text::_($v->title).'</div>';
					if ($v->title == 'name_first' || $v->title == 'name_middle' || $v->title == 'name_last' || $v->title == 'name_degree' ) {
						$billNameO .= $value . ' ';
					} else if ($v->title == 'country') {
						$billingO .= '<div class="col-sm-12">'.PhocacartCountry::getCountryById((int)$value).'</div>';
					} else if ($v->title == 'region') {
						$billingO .= '<div class="col-sm-12">'.PhocacartRegion::getRegionById((int)$value).'</div>';
					} else {
						$billingO .= '<div class="col-sm-12">'.$value.'</div>';
					}
				}



			}

			if ($v->display_shipping == 1) {

				// Form input value is required but it is empty and we have set that the shipping address is other than billing
				$value = $this->form->getValue($v->title);
				if ($v->required == 1 && $value == '' && $baSa == 0) {
					$filledOk = 0;
				}

				// Form
				$shipping .= '<div class="form-group">'."\n"
				. '<div class="col-sm-5 control-label">'. $this->form->getLabel($v->title . '_phs') . '</div>';

				if ($v->title == 'email') {
					$this->form->setValue($v->title, null, $this->u->email );
				}

				$shipping .= '<div class="col-sm-7">' . $this->form->getInput($v->title. '_phs') . '</div>'
				.'</div>' . "\n";


				// Output
				if ($filledOk == 1 && $this->t['editaddress'] == 0) {
					//$shippingO .= '<div class="col-sm-5">'.Text::_($v->title).'</div>';
					if ($v->title == 'name_first' || $v->title == 'name_middle' || $v->title == 'name_last' || $v->title == 'name_degree' ) {
						$shipNameO .= $value . ' ';
					} else if ($v->title == 'country') {
						$shippingO .= '<div class="col-sm-12">'.PhocacartCountry::getCountryById((int)$value).'</div>';
					} else if ($v->title == 'region') {
						$shippingO .= '<div class="col-sm-12">'.PhocacartRegion::getRegionById((int)$value).'</div>';
					} else {
						$shippingO .= '<div class="col-sm-12">'.$value.'</div>';
					}
				}


			}
		}
	}

	$billNameO = '<div class="col-sm-12">'.$billNameO.'</div>';
	$shipNameO = '<div class="col-sm-12">'.$shipNameO.'</div>';

	*/
}
?>
