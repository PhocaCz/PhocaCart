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

class PhocacartUser
{

	protected $guest = array();
	
	public function __construct() {
	
	}
	

	public static function getAddressDataForm($form, $fields, $user, $billingSuffix = '', $shippingSuffix = '_phs', $guestUser = 0) {
	
		$o['b']			= '';// Output Billing
		$o['s'] 		= '';// Output Shipping
		$o['bsch']		= '';// B S Checked? Is the billing address the same like shipping address
		$o['filled']	= 1; // Is every form input filled out

		$app	= JFactory::getApplication();
		
		$baSa = $form->getValue('ba_sa');
		
		if ($baSa == 1) {
			$o['bsch'] = 'checked="checked"';
		}

	
		if (!empty($fields)) {
			foreach($fields as $k => $v) {
				
				if ($v->display_billing == 1 || ($app->isClient('administrator') && $v->title == 'id')){
					
					$value = $form->getValue($v->title);// If form input is required but it is empty, this is wrong
					
					if ($v->required == 1 && $value == '') {
						$o['filled'] = 0;
					}
					
					if ($v->title == 'email' && $guestUser == 0) {
						$form->setValue($v->title, null, $user->email );
					}
					
					if (!$app->isClient('administrator')) {
						$o['b'] .= '<div class="form-group">';
						$o['b'] .= '<div class="col-sm-5 control-label">'.$form->getLabel($v->title . $billingSuffix).'</div>';
						$o['b'] .= '<div class="col-sm-7">'.$form->getInput($v->title . $billingSuffix).'</div>';
						$o['b'] .= '</div>' . "\n";
						
					} else {
						// Admin uses obsolete bootstrap
						$o['b'] .= '<div class="control-group">';
						$o['b'] .= '<div class="control-label"><label>'.$form->getLabel($v->title . $billingSuffix).'</label></div>';
						$o['b'] .= '<div class="controls">'.$form->getInput($v->title . $billingSuffix).'</div>';
						$o['b'] .= '</div>' . "\n";
					}
						
				}
				
				if ($v->display_shipping == 1 || ($app->isClient('administrator') && $v->title == 'id')) {
				
					$value = $form->getValue($v->title);				    // Form input value is required but it is empty 
					if ($v->required == 1 && $value == '' && $baSa == 0) {  // and we have set that the shipping address
						$o['filled'] = 0;									// is other than billing
					}
					
					if ($v->title == 'email' && $guestUser == 0) {
						$form->setValue($v->title, null, $user->email );
					}
					
					if (!$app->isClient('administrator')) {
						$o['s'] .= '<div class="form-group">';
						$o['s'] .= '<div class="col-sm-5 control-label">'.$form->getLabel($v->title . $shippingSuffix).'</div>';
						$o['s'] .= '<div class="col-sm-7">'.$form->getInput($v->title . $shippingSuffix).'</div>';
						$o['s'] .= '</div>' . "\n";
					} else {
						// Admin uses obsolete bootstrap
						$o['s'] .= '<div class="control-group">';
						$o['s'] .= '<div class="control-label"><label>'.$form->getLabel($v->title . $shippingSuffix).'</label></div>';
						$o['s'] .= '<div class="controls">'.$form->getInput($v->title . $shippingSuffix).'</div>';
						$o['s'] .= '</div>' . "\n";
					}
					
				}
			}
		}
		
		return $o;
	}
	
	public static function getAddressDataOutput($data, $fields, $user, $guestUser = 0) {
		
		$o['b']		= '';// Output Billing
		$o['s'] 	= '';// Output Shipping
		$o['bsch']	= '';// B S Checked? Is the billing address the same like shipping address
		$o['filled']= 1; // Is every form input filled out
		$bNameO		= '';// join first, middle, last name and degree
		$sNameO		= '';// join first, middle, last name and degree
		$o['bregion'] 	= '';// Return for shipping method (save database query)
		$o['bcountry']	= '';// Return for shipping method (save database query)
		
		// ba_sa = 0: billing and shipping addresses are NOT the same
		// ba_sa = 1: billing and shipping addresses are same (don't check shipping fields)
		
		if (empty($data[0])) {
			// no billing return false
			$o['filled'] = 0;
			
			return $o;
		} else if (!empty($data[0])
					&& isset($data[0]->ba_sa) && $data[0]->ba_sa == 0 
					&& isset($data[0]->type) && $data[0]->type == 0
					&& empty($data[1])){
			// In words - we have billing data, so we check if billing data are the same like shipping (ba_sa = 1)
			// If not then we check if we have shipping data, if not return false
			// And check if array 0 is really billing - array 0 (first array) cannot be shpping as we order it by type ASC in db query
			$o['filled'] = 0;
			
			return $o;
		}
		
		
		// Billing the same like shipping
		if ($data[0]->ba_sa == 1 || $data[0]->ba_sa == 1){
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
							$o['b'] .= '<div class="col-sm-12">'.$data[0]->countrytitle.'</div>';
						} else if ($v->title == 'region') {
							$o['bregion']	= (int)$value;// Return region and country
							$o['b'] .= '<div class="col-sm-12">'.$data[0]->regiontitle.'</div>';
						} else {
							$o['b'] .= '<div class="col-sm-12">'.$value.'</div>';
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
							$o['s'] .= '<div class="col-sm-12">'.$data[1]->countrytitle.'</div>';
						} else if ($v->title == 'region') {
							$o['s'] .= '<div class="col-sm-12">'.$data[1]->regiontitle.'</div>';
						} else {
							$o['s'] .= '<div class="col-sm-12">'.$value.'</div>';
						}
					}
					
				}
			}
			
			$o['b'] = '<div class="col-sm-12">'.$bNameO.'</div>' . $o['b'];
			$o['s'] = '<div class="col-sm-12">'.$sNameO.'</div>' . $o['s'];
		}
		
		return $o;
	}

	public static function getUserAddress($userId) {
	
		$db = JFactory::getDBO();
		
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
	
	public static function convertAddressTwo($data, $array = 1) {
		$dataNew	= array();
		if ($array == 1) {
			$dataNew[0] = array();
			$dataNew[1]	= array();
		} else {
			$dataNew[0]= new StdClass();
			$dataNew[1]= new StdClass();
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
					$k = str_replace('_phs', '', $k);
					if ($array == 1) {
						$dataNew[1][$k] = $v;
					} else {
						$dataNew[1]->$k = $v;
					} 
				}
			}
		}
		
		if (!empty($dataNew[1])) {
			// Set right type for shipping address
			if ($array == 1) {
				$dataNew[1]['type'] = 1;
			} else {
				$dataNew[1]->type = 1;
			}
		}
		return $dataNew;
	}
	
	public static function getUserInfo() {
		
		$u				= array();
		$user 			= JFactory::getUser();
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
			$db = JFactory::getDBO();
		
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
					//$billingO .= '<div class="col-sm-5">'.JText::_($v->title).'</div>';
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
					//$shippingO .= '<div class="col-sm-5">'.JText::_($v->title).'</div>';
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