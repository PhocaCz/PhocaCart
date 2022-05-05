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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PhocacartPos
{
	public static function updateUserCart($vendorId, $ticketId, $unitId = 0, $sectionId = 0, $userId = 0, $loyaltyCardNumber = '') {

		// User ID, section ID and unit ID can be null (deselect user)
		if ((int)$vendorId > 0 && (int)$ticketId > 0) {
			$db 	= Factory::getDBO();
			$date 	= Factory::getDate();
			$now	= $date->toSql();

			/*$app					= JFactory::getApplication();
			$paramsC 				= PhocacartUtils::getComponentParameters();
			$pos_payment_force	= $paramsC->get( 'pos_payment_force', 0 );
			$pos_shipping_force	= $paramsC->get( 'pos_shipping_force', 0 );*/

			$query = 'UPDATE #__phocacart_cart_multiple'
				.' SET user_id = '.(int)$userId.','
				.' date = '.$db->quote($now).','
				.' loyalty_card_number = '.$db->quote($loyaltyCardNumber)
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId;

			$db->setQuery($query);
			$db->execute();
			return true;
		}
		return false;
	}

	public static function getUserIdByVendorAndTicket($vendorId, $ticketId, $unitId, $sectionId) {

		if ((int)$vendorId > 0 && (int)$ticketId > 0) {
			$db 	= Factory::getDBO();

			$query = ' SELECT user_id FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' ORDER BY user_id LIMIT 1';
			$db->setQuery($query);
			$result = $db->loadResult();
			if ((int)$result > 0) {
				return (int)$result;
			}
			return 0;
		}
		return 0;
	}

	public static function getCardByVendorAndTicket($vendorId, $ticketId, $unitId, $sectionId, $userId = 0) {

		if ((int)$vendorId > 0 && (int)$ticketId > 0) {
			$db 	= Factory::getDBO();

			$query = ' SELECT loyalty_card_number FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' AND user_id = '.(int)$userId
				.' ORDER BY loyalty_card_number LIMIT 1';
			$db->setQuery($query);
			$result = $db->loadResult();
			if ($result != '') {
				return $result;
			}
			return '';
		}
		return '';
	}


	public static function isPosView() {


		$isView 		= PhocacartUtils::isView('pos');
		$isController 	= PhocacartUtils::isController('pos');

		if ($isView || $isController) {
			return true;
		}
		return false;
	}

	public static function isPos($forcePos = 0) {


		// We check if we are located in POS view or POS controller
		$isView 		= PhocacartUtils::isView('pos');
		$isTypeView		= PhocacartUtils::isTypeView('Pos');
		$isController 	= PhocacartUtils::isController('pos');


		if (!PhocacartPos::isPosEnabled()){

			if ($isView || $isTypeView || $isController) {
				// Return the error info only in case of POS view or controller
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('COM_PHOCACART_POS_IS_DISABLED'), 'error');
			}
			return false;
		}



		if ($forcePos) {
			// We are not located in POS view but we ask some view
			// where we need POS rules (for example - in POS we are ask Order view
			// to display invoices)
			return true;
		} else {


			if ($isView || $isTypeView || $isController) {
				return true;
			}
			return false;
		}


		return false;
	}

	public static function isPosEnabled() {

		$pC 			= PhocacartUtils::getComponentParameters();
		$pos_enabled	= $pC->get( 'pos_enabled', 0 );
		if($pos_enabled){
			return true;
		}
		return false;
	}

	/*
	public static function getUserByVendorAndTicket($vendorId, $ticketId) {

		if ((int)$vendorId > 0 && (int)$ticketId > 0) {
			$db 	= Factory::getDBO();

			$query = ' SELECT a.user_id, u.id, u.name, u.user_name, u.email FROM #__phocacart_cart_multiple AS a'
				.' LEFT JOIN #__users AS u ON a_user_id = u.id'
				.' WHERE a.vendor_id = '.(int)$vendorId
				.' AND a.ticket_id = '.(int)$ticketId
				.' ORDER BY a.user_id LIMIT 1';
			$db->setQuery($query);
			$result = $db->loadObject();
			return $result;
		}
		return false;
	}*/


	public static function renderPosPage() {

		$document		= Factory::getDocument();
		$pC 			= PhocacartUtils::getComponentParameters();


		/*
		 * Page
		 * +---------------------------------+
		 * |              top                |
		 * +---------------------------------+
		 * |              main               |
		 * | +---------------+-------------+ |
		 * | |filter         | cart        | |
		 * | |---------------|             | |
		 * | |categories     |             | |
		 * | |---------------+-------------| |
		 * | |content        | input       | |
		 * | +---------------+-------------+ |
		 * |---------------------------------|
		 * |             bottom              |
		 * +---------------------------------+
		 *
		 * content: products, customers, shipping, payment, ...
		 */


		$s = array();
		$s[0]['top']			= $pC->get( 'pos_layout_top', 8 );//8
		$s[0]['bottom']			= $pC->get( 'pos_layout_bottom', 6 );//6

		$s[0]['mainfilter'] 	= $pC->get( 'pos_layout_mainfilter', 80 );//8 // MUST BE SMALLER THAN main - (top + bottom)
		$s[0]['maincategories'] = $pC->get( 'pos_layout_maincategories', 4 );//6 // MUST BE SMALLER THAN main - (top + bottom + main filter)

		$s[0]['maincart'] 		= $pC->get( 'pos_layout_maincart', 50 );//50 // MUST BE SMALLER THAN main - (top + bottom)

		// Virtual Keyboard e.g.
		$vK						= $pC->get( 'pos_layout_media_maxheight', '24rem' );//'24rem';
		$s[1]['top']			= $pC->get( 'pos_layout_top_maxheight', 16 );//16;//8
		$s[1]['bottom']			= $pC->get( 'pos_layout_bottom_maxheight', 1 );//1;//6

		$s[1]['mainfilter'] 	= $pC->get( 'pos_layout_mainfilter_maxheight', 16 );//16;//8
		$s[1]['maincategories'] = $pC->get( 'pos_layout_maincategories_maxheight', 13 );//13;//6

		$s[1]['maincart'] 		= $pC->get( 'pos_layout_maincart_maxheight', 70 );//70;//50


		$o = array();

		foreach ($s as $k => $v) {

			$s[$k]['main'] 			= 100 - $s[$k]['top'] - $s[$k]['bottom'];
			$s[$k]['maincolleft'] 	= $s[$k]['main'];
			$s[$k]['maincolright'] 	= $s[$k]['main'];
			$s[$k]['mainpage'] 		= $s[$k]['main'];

			$s[$k]['maincontent'] 	= $s[$k]['main'] - $s[$k]['mainfilter'] - $s[$k]['maincategories'];

			$s[$k]['maininput'] 	= $s[$k]['main'] - $s[$k]['maincart'];

			if ($k == 1) {
				$o[] = '@media (max-height: '.$vK.') {';
			}

			$o[] = '.ph-pos-wrap-top {height:'.(int)$s[$k]['top'].'vh}';
			$o[] = '.ph-pos-wrap-main {height:'.(int)$s[$k]['main'].'vh}';
			$o[] = '.ph-pos-wrap-bottom {height:'.(int)$s[$k]['bottom'].'vh}';

			$o[] = '.ph-pos-main-column-left {height:'.(int)$s[$k]['maincolleft'].'vh}';
			$o[] = '.ph-pos-main-column-right {height:'.(int)$s[$k]['maincolleft'].'vh}';

			$o[] = '.ph-pos-main-filter {height:'.(int)$s[$k]['mainfilter'].'vh}';
			$o[] = '.ph-pos-main-categories {height:'.(int)$s[$k]['maincategories'].'vh}';
			$o[] = '.ph-pos-main-content {height:'.(int)$s[$k]['maincontent'].'vh}';

			$o[] = '.ph-pos-main-cart {height:'.(int)$s[$k]['maincart'].'vh}';
			$o[] = '.ph-pos-main-input {height:'.(int)$s[$k]['maininput'].'vh}';

			$o[] = '.ph-pos-main-page {height:'.(int)$s[$k]['mainpage'].'vh}';

			if ($k == 1) {
				$o[] = '}';
			}
		}


		$document->addCustomTag('<style type="text/css">'.implode("\n", $o).'</style>');

	}

	public static function getPreferredSku() {

		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$pos_preferred_sku		= $paramsC->get( 'pos_preferred_sku', 'sku' );

		$a 			= array();
		$a['name'] 	= $pos_preferred_sku;
		switch ($pos_preferred_sku) {

			case 'upc':				$a['title'] 	= Text::_('COM_PHOCACART_FIELD_UPC_LABEL'); break;
			case 'ean':				$a['title'] 	= Text::_('COM_PHOCACART_FIELD_EAN_LABEL'); break;
			case 'jan':				$a['title'] 	= Text::_('COM_PHOCACART_FIELD_JAN_LABEL'); break;
			case 'isbn':			$a['title'] 	= Text::_('COM_PHOCACART_FIELD_ISBN_LABEL'); break;
			case 'mpn':				$a['title'] 	= Text::_('COM_PHOCACART_FIELD_MPN_LABEL'); break;
			case 'serial_number':	$a['title'] 	= Text::_('COM_PHOCACART_FIELD_SERIAL_NUMBER_LABEL'); break;
			case 'registration_key':$a['title'] 	= Text::_('COM_PHOCACART_FIELD_REGISTRATION_KEY_LABEL'); break;

			default: case 'sku':	$a['title'] 	= Text::_('COM_PHOCACART_FIELD_SKU_LABEL'); break;

		}

		return $a;
	}
}
?>
