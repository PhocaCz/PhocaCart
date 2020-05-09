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

class PhocacartFormUser
{
	private static $form = false;

	private function __construct(){}

	public static function getFormXml($billingSuffix = '', $shippingSuffix = '_phs', $billing = 1, $shipping = 1, $account = 0, $guest = 0) {

		if(self::$form === false){

			$app	= JFactory::getApplication();
			$o = array();
			$oFb = array();
			$oFs = array();
			$o[] = '<form>';

			$o[] = '<fieldset name="user" addrulepath="/components/com_phocacart/models/rules" addfieldpath="/components/com_phocacart/models/fields" label="COM_PHOCACART_FORM_LABEL">';

			$fields	= new PhocacartFormItems();
			$f		= $fields->getFormItems($billing, $shipping, $account);

			// Specific fields not managed in administration but added to form,
			// so we can work with it in checkout view html (default_address)
			$specFields = array('ba_sa', 'newsletter', 'id');
			$c 			= count($f);

			foreach ($specFields as $k => $v) {
				$f[$c] = new StdClass();
				$f[$c]->title = $v;
				$f[$c]->display_billing = 0;
				$f[$c]->display_shipping = 0;
				$f[$c]->display_account = 0;
				$f[$c]->type = 'hidden';
				$f[$c]->label = $v;
				$f[$c]->required = 0;
				$f[$c]->pattern = '';
				$f[$c]->maxlength = '';
				$c++;
			}


			if (!empty($f)) {
				foreach($f as $k => $v) {

					$fB = $fS 	= array();// Billing and Shipping
					$class 	= '';
					if($v->title != '' && $v->type != '' && $v->label != '') {
						$fB[] = '<field name="'.htmlspecialchars($v->title).$billingSuffix.'"';
						$fS[] = '<field name="'.htmlspecialchars($v->title).$shippingSuffix.'"';





						$tA 	= explode(":", $v->type);
						$type 	= 'text';
						if (isset($tA[0]) && $tA[0] != '') {
							$type = $tA[0];
						}

						if(isset($v->validate) && $v->validate == 'email') {
								$type = 'email';
						}

						$fB[] = $fS[] = ' type="'.htmlspecialchars($type).'"'
						. ' label="'.htmlspecialchars($v->label).'"';



						if (isset($v->description) && $v->description != '') {
							$fB[] = $fS[] =  ' description="'.htmlspecialchars($v->description).'"';
						}
						if (isset($v->class) && $v->class != '') {
							$class .= ' '.htmlspecialchars($v->class);
						}
						if (isset($v->default) && $v->default != '') {
							$fB[] = $fS[] =  ' default="'.htmlspecialchars($v->default).'"';
						}

						// 0 not read only
						// 1 read only
						// 2 read only for registered users but not read only for guests

						if (isset($v->read_only)) {
							//if ($app->isClient('administrator')) {
								// in admin allow to change the readonly input forms
							//} else {
								if($v->read_only == 1) {
									$fB[] = $fS[] =  ' readonly="true"';
									$class .= ' readonly';
								} else if ($v->read_only == 2 && $guest == 0) {
									$fB[] = $fS[] =  ' readonly="true"';
									$class .= ' readonly';

								}
							//}
						}

						// No required in admin
						if (!$app->isClient('administrator')) {
							if (isset($v->required) && $v->required == 1) {
								$fB[] = $fS[] =  ' required="true"';
							}
						}

						// Pattern
						if (isset($v->pattern) && $v->pattern != '') {
							$fB[] = $fS[] =  ' pattern="'.$v->pattern.'"';
						}

						if (isset($v->maxlength) && (int)$v->maxlength > 0) {
							$fB[] = $fS[] =  ' maxlength="'.(int)$v->maxlength.'"';
						}

						if (isset($v->unique) &&  $v->unique == 1) {
							$fB[] = $fS[] =  ' unique="true"';
						}

						if (isset($v->validate) && $v->validate != '') {
							$fB[] = $fS[] =  ' validate="'.htmlspecialchars($v->validate).'"';
							$class .= ' validate-'.htmlspecialchars($v->validate);
						}





						$fB[] = ' class="'.htmlspecialchars($class).'"';

						// Billing and Shipping is the same - javascript function
						$class .= ' phShippingFormFields';
						if (isset($v->required) && $v->required == 1) {
							$class .= ' phShippingFormFieldsRequired';
						}
						$fS[] =  ' class="'.htmlspecialchars($class).'"';


						$fB[] = $fS[] =  ' />';

						$o[] = implode( "", $fB ) . implode( "", $fS );

					} else {
						continue;
					}

				}
			}

			$o[] = '</fieldset>';
			$o[] = '</form>';



			$fields				= array();
			$fields['xml'] 		= implode( "", $o );
			$fields['array']	= $f;


			self::$form = $fields;
		}
		return self::$form;
	}

	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>
