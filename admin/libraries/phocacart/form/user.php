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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

abstract class PhocacartFormUser
{
	public const FieldReadWrite = 0;
	public const FieldReadOnly = 1;
	public const FieldReadOnlyRegistered = 2;
	public const FieldReadOnlyButAdmin = 3;
	public const FieldReadOnlyRegisteredButAdmin = 4;

	private static $form = false;

	public static function getFormXml($billingSuffix = '', $shippingSuffix = '_phs', $billing = 1, $shipping = 1, $account = 0, $guest = 0, ?string $fieldsName = null) {
		if(self::$form === false) {
			$app = Factory::getApplication();
			$o = [];
			$o[] = '<form>';

			$o[] = '<fieldset name="user" addrulepath="/components/com_phocacart/models/rules" addfieldpath="/components/com_phocacart/models/fields">';
			if ($fieldsName) {
				$o[] = '<fields name="' . $fieldsName. '">';
			}

			$fields	= new PhocacartFormItems();
			$f		= $fields->getFormItems($billing, $shipping, $account);

			// Specific fields not managed in administration but added to form,
			// so we can work with it in checkout view html (default_address)
			$specFields = ['ba_sa', 'newsletter', 'id'];
			$c = count($f);

			foreach ($specFields as $v) {
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
				foreach($f as $v) {
					$fB = $fS 	= array();// Billing and Shipping
					$class 	= '';
					if($v->title != '' && $v->type != '' && $v->label != '') {
						$fB[] = '<field name="'.htmlspecialchars($v->title).$billingSuffix.'"';
						$fS[] = '<field name="'.htmlspecialchars($v->title).$shippingSuffix.'"';

						$tA 	= explode(":", $v->type);
						$type 	= 'text';
						$typeSuffix = '';
						if (isset($tA[0]) && $tA[0] != '') {
							$type = $tA[0];
						}
						if (isset($tA[1]) && $tA[1] != '') {
							$typeSuffix = $tA[1];
						}
						$typeLimit = '';
						if ($typeSuffix != '') {
							$typeLimit = (int)PhocacartUtils::getNumberFromText($typeSuffix);
						}

						if(isset($v->validate) && $v->validate == 'email') {
								$type = 'email';
						}

						if(isset($v->validate) && $v->validate == 'tel') {
								$type = 'tel';
						}

						// --- PREDEFINED VALUES (limited feature, see documentation)
						$predefinedValues = '';
						if(isset($v->predefined_values) && $v->predefined_values != '') {
							$predefinedValues = array_map('trim', explode(',', $v->predefined_values));
							$predefinedValues = array_filter($predefinedValues, 'strlen');
						}

						if (!empty($predefinedValues)) {
							//$type= "list";
							//$type= "radio";
							//$type = 'checkbox';

							if ($type != 'list' && $type != 'radio' && $type != 'checkbox') {
								$type = 'checkbox';
							}

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

						if ($type == 'checkbox' && isset($v->default) && $v->default == '1') {
							$fB[] = $fS[] = ' checked="checked"';
						}

						// Calendar
						if ($type == 'calendar') {
							$fB[] = $fS[] = ' filter="user_utc" translateformat="true" showtime="true"';
						}


						// 0 not read only
						// 1 read only
						// 2 read only for registered users but not read only for guests

						if (isset($v->read_only)) {
							//if ($app->isClient('administrator')) {
							// in admin allow to change the readonly input forms
							//} else {
							if ($v->read_only == 1) {
								// Read only everwhere
								$fB[]  = $fS[] = ' readonly="true"';
								$class .= ' readonly';
							} else if ($v->read_only == 3) {
								// Read only everwhere except administration
								if ($app->isClient('administrator')) {
								} else {
									$fB[]  = $fS[] = ' readonly="true"';
									$class .= ' readonly';
								}

							} else if ($v->read_only == 2 && $guest == 0) {
								// Read only for NO guests only
								$fB[] = $fS[] =  ' readonly="true"';
								$class .= ' readonly';

							} else if ($v->read_only == 4 && $guest == 0) {
								// Read only for NO guests only everwhere except administration
								if ($app->isClient('administrator')) {

								} else {
									$fB[]  = $fS[] = ' readonly="true"';
									$class .= ' readonly';
								}

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
						} else {
							// e.g. we limit varchar(100)
							if(($type == 'text' || $type == 'tel') && $typeLimit > 0) {
								$fB[] = $fS[] =  ' maxlength="'.$typeLimit.'"';
							}

						}

						if (isset($v->unique) &&  $v->unique == 1) {
							$fB[] = $fS[] =  ' unique="true"';
						}

						if (isset($v->validate) && $v->validate != '') {
							$fB[] = $fS[] =  ' validate="'.htmlspecialchars($v->validate).'"';
							$class .= ' validate-'.htmlspecialchars($v->validate);
						}

						if (isset($v->autocomplete) && $v->autocomplete) {
							$fB[] = $fS[] =  ' autocomplete="'.htmlspecialchars($v->autocomplete).'"';
						}

						$fB[] = ' class="'.htmlspecialchars($class).'"';

						// Billing and Shipping is the same - javascript function
						$class .= ' phShippingFormFields';
						if (isset($v->required) && $v->required == 1) {
							$class .= ' phShippingFormFieldsRequired';
						}
						$fS[] =  ' class="'.htmlspecialchars($class).'"';


						// --- PREDEFINED VALUES (limited feature, see documentation)
						// Selectbox is displayed instead of standard types when predefined values are defined
						if (!empty($predefinedValues)) {


							$fB[] = $fS[] =  ' >';

							if (isset($v->predefined_values_first_option) && $v->predefined_values_first_option != '') {

								$fB[] = $fS[] = '<option value="">' . Text::_(htmlspecialchars($v->predefined_values_first_option)). '</option>';
							}

							foreach ($predefinedValues as $k => $v) {
								$defaultSelected = '';
								if (isset($v->default) && $v->default != '') {
									$defaultSelected = 'selected="selected"';
								}

								$fB[] = $fS[] =  '<option value="'.htmlspecialchars($v).'" '.$defaultSelected.'>'.htmlspecialchars($v).'</option>';
							}
							$fB[] = $fS[] =  '</field>';

						} else {

							$fB[] = $fS[] =  ' />';

						}

						if ($shippingSuffix) {
							$o[] = implode("", $fB) . implode("", $fS);
						} else {
							$o[] = implode("", $fB);
						}

					} else {
						continue;
					}

				}
			}

			if ($fieldsName) {
				$o[] = '</fields>';
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

	private static function addFieldsNode(\DOMElement $formNode, string $name): \DOMElement
	{
		$fieldsNode = $formNode->appendChild(new \DOMElement('fields'));
		$fieldsNode->setAttribute('name', $name);

		$fieldset = $fieldsNode->appendChild(new \DOMElement('fieldset'));
		$fieldset->setAttribute('name', $name);

		return $fieldset;
	}

	private static function addOptionNode(\DOMElement $fieldNode, string $value, string $text): \DOMElement
	{
		$node = $fieldNode->appendChild(new \DOMElement('option'));
		$node->setAttribute('value', $value);
		$node->appendChild(new \DOMText($text));

		return $node;
	}

	private static function addFieldNode(\DOMElement $fieldset, object $field, bool $isAdmin = false, bool $isGuest = false): \DOMElement
	{
		$node = $fieldset->appendChild(new \DOMElement('field'));
		$node->setAttribute('name', $field->title);
		[$type, $subtype] = array_merge(explode(':', $field->type), ['', '']);

		$type = $type ?: 'text';

		switch ($field->validate) {
			case 'email':
			case 'tel':
				$type = $field->validate;
				break;
		}

		// --- PREDEFINED VALUES (limited feature, see documentation)
		$options = [];
		if ($field->predefined_values) {
			$options = array_map('trim', explode(',', $field->predefined_values));
			$options = array_filter($options, 'strlen');
		}

		if ($options && !in_array($type, ['list', 'radio', 'checkbox'])) {
			$type = 'checkbox';
		}

		$node->setAttribute('type', $type);
        if ($field->label) {
            $node->setAttribute('label', $field->label);
        }
        if ($field->description) {
            $node->setAttribute('description', $field->description);
        }

		if ($field->default) {
			if ($type === 'checkbox') {
				$node->setAttribute('checked', 'checked');
			} else {
				$node->setAttribute('default', $field->default);
			}
		}

		if ($type === 'calendar') {
			$node->setAttribute('filter', 'user_utc');
			$node->setAttribute('translateformat', 'true');
			$node->setAttribute('showtime', 'true');
		}

		switch ($field->read_only) {
			case self::FieldReadOnly:
				$node->setAttribute('readonly', 'true');
				break;
			case self::FieldReadOnlyRegistered:
				if (!$isGuest) {
					$node->setAttribute('readonly', 'true');
				}
				break;
			case self::FieldReadOnlyButAdmin:
				if (!$isAdmin) {
					$node->setAttribute('readonly', 'true');
				}
				break;
			case self::FieldReadOnlyRegisteredButAdmin:
				if (!$isGuest && !$isAdmin) {
					$node->setAttribute('readonly', 'true');
				}
				break;
		}

		if (!$isAdmin && $field->required) {
			$node->setAttribute('required', 'true');
		}

		if ($field->pattern) {
			$node->setAttribute('pattern', $field->pattern);
		}

		if ((int)$field->maxlength > 0) {
			$node->setAttribute('maxlength', $field->maxlength);
		} elseif (in_array($type, ['text', 'tel', 'email']) && $subtype) {
			$size = PhocacartUtils::getNumberFromText($subtype);
			if ($size > 0) {
				$node->setAttribute('maxlength', $size);
			}
		}

		if ($field->unique && !$isAdmin) {
			$node->setAttribute('unique', 'true');
		}

		if ($field->class) {
			$node->setAttribute('class', $field->class);
		}

		if ($field->validate) {
			$node->setAttribute('validate', $field->validate);
		}

		if ($field->autocomplete && !$isAdmin) {
			$node->setAttribute('autocomplete', $field->autocomplete);
		}

		if ($options) {
			if ($field->predefined_values_first_option) {
				self::addOptionNode($node, '', $field->predefined_values_first_option);
			}

			foreach ($options as $option) {
				self::addOptionNode($node, $option, $option);
			}
		}

		return $node;
	}

	public static function loadAddressForm(Form $form, bool $loadBilling = true, bool $loadShipping = true, bool $loadAccount = false, bool $isAdmin = false, bool $isGuest = false): void
	{
		Form::addFieldPath(JPATH_ROOT . '/administrator/components/com_phocacart/models/fields');

		$xml = new \DOMDocument('1.0', 'UTF-8');
		$formNode = $xml->appendChild(new \DOMElement('form'));

		if ($loadBilling) {
			$billingFieldset = self::addFieldsNode($formNode, 'billing_address');
		}

		if ($loadShipping) {
			$shippingFieldset = self::addFieldsNode($formNode, 'shipping_address');
		}

		if ($loadAccount) {
			$accountFieldset = self::addFieldsNode($formNode, 'account');
		}

		$fields	= (new PhocacartFormItems())->getFormItems($loadBilling ? 1 : 0, $loadShipping ? 1 : 0, $loadAccount? 1 : 0);

		foreach ($fields as $field) {
			if ($loadBilling && $field->display_billing) {
				self::addFieldNode($billingFieldset, $field, $isAdmin, $isGuest);
			}

			if ($loadShipping && $field->display_shipping) {
				self::addFieldNode($shippingFieldset, $field, $isAdmin, $isGuest);
			}

			if ($loadAccount && $field->display_account) {
				self::addFieldNode($accountFieldset, $field, $isAdmin, $isGuest);
			}
		}

		$form->load($xml->saveXML());
	}
}
