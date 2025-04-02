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

class PhocacartFormItems
{


	public function __construct() {}

	public function getFormItems($billing = 1, $shipping = 1, $account = 0) {
		$db 					= Factory::getDBO();
		$user 					= PhocacartUser::getUser();
		$userLevels				= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		$wheres 		= array();
		if ((int)$billing == 1) {
			$wheres[]	= '(a.display_billing = 1 OR a.display_shipping = 1)';
			//$wheres[]	= 'a.display_shipping = 1';// they are loaded together (shipping and billing loaded togehter)
												  // if billing is disabled and shipping enabled we still need to load the billing too
		}
		if ((int)$shipping == 1) {
			$wheres[]	= '(a.display_shipping = 1 OR a.display_billing = 1)';
			//$wheres[]	= 'a.display_billing = 1';// they are loaded together
		}
		if ((int)$account == 1) {
			$wheres[]	= 'a.display_account = 1';
		}

		$wheres[]	= 'a.published = 1';

		// ACCESS
		$wheres[] = " a.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";

		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		$query = 'SELECT a.id, a.title, a.label, a.description, a.type, a.predefined_values, a.predefined_values_first_option, a.default, a.class, a.read_only, a.required, a.pattern, a.maxlength,'
				.' a.display_billing, a.display_shipping, a.display_account, a.validate, a.unique, a.published, a.access, a.autocomplete'
				.' FROM #__phocacart_form_fields AS a'
				.' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 9'// type 9 is formfield
				. $where
				.' ORDER BY a.ordering';
		$db->setQuery($query);

		$fields = $db->loadObjectList();



		return $fields;
	}

	/*
	 * Check if we ask some form field in billing or shipping address
	 * E.g. if vendor completely skip adding shipping address and user sends no shipping address
	 * this does not mean that user didn't added data but vendor just didn't asked for them.
	 */

	public static function isFormFieldActive($type = 'billing') {

	    $db = Factory::getDBO();

	    $wheres = array();

	    switch($type) {

            case 'account':
                $wheres[] = " a.display_display_account = 1";
            break;

	        case 'shipping':
                $wheres[] = " a.display_shipping = 1";
            break;

            case 'billing':
            default:
                $wheres[] = " a.display_billing = 1";
            break;
        }


		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

	    $query = 'SELECT a.id'
				.' FROM #__phocacart_form_fields AS a'
				. $where
				.' ORDER BY a.id'
                .' LIMIT 1';
		$db->setQuery($query);

		$row = $db->loadResult();
		if ((int)$row > 0) {
		    return true;
        }
		return false;

    }

	public static function getColumnType($type) {

		$t = '';
		if ($type != '') {
			$tA = explode(":", $type);
			if (isset($tA[0]) && isset($tA[1]) && $tA[1] != '') {


				$pos = strpos($tA[1], 'varchar');
				if ($pos === 0) {
					$t = $tA[1] . ' NOT NULL DEFAULT \'\'';
				}

				$pos1 = strpos($tA[1], 'int');
				if ($pos1 === 0) {
					$t = $tA[1] . ' NOT NULL DEFAULT \'0\'';
				}

				$pos2 = strpos($tA[1], 'text');
				if ($pos2 === 0) {
					$t = $tA[1];
				}

				$pos3 = strpos($tA[1], 'datetime');
				if ($pos3 === 0) {
					$t = $tA[1] . ' NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
				}

			}
		}
		return $t;
	}

}
