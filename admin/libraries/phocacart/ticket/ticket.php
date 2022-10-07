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
use Joomla\CMS\Router\Route;

class PhocacartTicket
{
	public static function getTicket($vendorId) {

		$ticket	= array();


		$app		= Factory::getApplication();
		$ticketId	= $app->input->get( 'ticketid', 1, 'int' );// if not set, always set it to 1, ticekt 1 is default
		$unitId		= $app->input->get( 'unitid', 0, 'int' );// if not set, always set it to 1, ticekt 1 is default
		$sectionId	= $app->input->get( 'sectionid', 0, 'int' );// if not set, always set it to 1, ticekt 1 is default


		$existsSection	= PhocacartSection::existsSection($sectionId);
		$existsUnit		= PhocacartUnit::existsUnit($unitId, $sectionId);

		// SECTION IS DEFINED by administrator
		// UNIT IS DEFINED by administrator
		// TICKET CAN BE CREATED by vendor

		// Check if the section even exists, if not set to first you will find
		if (!$existsSection) {

			$sections = PhocacartSection::getSections(1);
			if (!empty($sections)) {
				foreach($sections as $k => $v) {
					$sectionId = (int)$v->id;
				}
			} else {
				$sectionId = 0;
			}

		}
		// Check if the unit even exists, if not set to default 1
		if (!$existsUnit) {
			$units = PhocacartUnit::getUnits($sectionId, 1);
			if (!empty($units)) {
				foreach($units as $k => $v) {
					$unitId = (int)$v->id;
				}
			} else {
				$unitId = 0;
			}
		}

		// Check complet ticket
		$existsTicket = self::existsTicket($vendorId, $ticketId, $unitId, $sectionId);



		if ($existsTicket) {
			// Asked ticket exists ... OK
			$ticket['ticketid']		= $ticketId;
			$ticket['unitid']		= $unitId;
			$ticket['sectionid'] 	= $sectionId;
			return $ticket;
		} else {
			// Asked ticket does not exists ... find another
			$firstTicket = self::getFirstVendorTicket($vendorId, $unitId, $sectionId);

			if ($firstTicket) {
				// Some ticket found ... OK
				$ticket['ticketid']		= $firstTicket;
				$ticket['unitid']		= $unitId;
				$ticket['sectionid'] 	= $sectionId;
				return $ticket;
			} else {
				// No ticket found ... set the default
				// if there is no ticket id for this vendor return the base - ticket = 1
				$ticket['ticketid']		= 1;
				$ticket['unitid']		= $unitId;
				$ticket['sectionid'] 	= $sectionId;
				PhocacartTicket::addNewVendorTicket($vendorId, 1, $unitId, $sectionId);
				return $ticket;
			}
		}
		$ticket['ticketid']		= 1;
		$ticket['unitid']		= $unitId;
		$ticket['sectionid'] 	= $sectionId;
	}

	public static function existsTicket($vendorId, $ticketId, $unitId, $sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT ticket_id FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId;
		$db->setQuery($query);
		$result = $db->loadResult();
		if (isset($result) && (int)$result > 0) {
			return $result;
		}
		return false;
	}

	public static function getVendorTickets($vendorId, $unitId, $sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT user_id, vendor_id, ticket_id, unit_id, section_id FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' ORDER BY ticket_id ASC';
		$db->setQuery($query);
		$result = $db->loadObjectList();
		return $result;

	}



	public static function getLastVendorTicket($vendorId, $unitId, $sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT ticket_id FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' ORDER BY ticket_id DESC';
		$db->setQuery($query);
		$result = $db->loadResult();

		return (int)$result;

	}

	public static function getFirstVendorTicket($vendorId, $unitId, $sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT ticket_id FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' ORDER BY ticket_id ASC';
		$db->setQuery($query);
		$result = $db->loadResult();
		return (int)$result;

	}

	public static function addNewVendorTicket($vendorId, $ticketId, $unitId, $sectionId) {


		$app					= Factory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$pos_payment_force	= $paramsC->get( 'pos_payment_force', 0 );
		$pos_shipping_force	= $paramsC->get( 'pos_shipping_force', 0 );

		if ((int)$pos_payment_force > 0) {
            $pos_payment_force = PhocacartPayment::isPaymentMethodActive($pos_payment_force) === true ? (int)$pos_payment_force : 0;
        }
        if ((int)$pos_shipping_force > 0) {
            $pos_shipping_force = PhocacartShipping::isShippingMethodActive($pos_shipping_force) === true ? (int)$pos_shipping_force : 0;
        }

		$date 	= Factory::getDate();
		$now	= $date->toSql();
		$db 	= Factory::getDBO();
		$query	= 'INSERT INTO #__phocacart_cart_multiple (user_id, vendor_id, ticket_id, unit_id, section_id, shipping, payment, cart, date)'
				.' VALUES (0, '.(int)$vendorId.', '.(int)$ticketId.', '.(int)$unitId.', '.(int)$sectionId.', '.(int)$pos_shipping_force.', '.(int)$pos_payment_force.', \'\', '.$db->quote($now).');';
				$db->setQuery($query);
				$db->execute();
		return true;

	}

	public static function removeVendorTicket($vendorId, $ticketId, $unitId, $sectionId) {

		$db 	= Factory::getDBO();
		$query = ' DELETE FROM #__phocacart_cart_multiple'
				.' WHERE vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId;
		$db->setQuery($query);
		$db->execute();
		return true;

	}

	public static function renderNavigation($vendorId, $ticketId, $unitId, $sectionId) {

		// $ticketId is active ticket
		$tickets = self::getVendorTickets($vendorId, $unitId, $sectionId);

		$o = '<ul class="nav nav-tabs">';
		if (!empty($tickets)) {
			foreach($tickets as $k => $v) {

				$active = '';
				if ((int)$v->ticket_id == (int)$ticketId) {
					$active = 'active';
				}


				$link = Route::_(PhocacartRoute::getPosRoute((int)$v->ticket_id, (int)$v->unit_id, (int)$v->section_id));
				$o .= '<li class="nav-item '.$active.'">';
				$o .= '<a class="nav-link '.$active.'" href="'.$link.'"> '.(int)$v->ticket_id.' </a>';
				$o .= '</li>';

			}

		} else {
			$link = Route::_(PhocacartRoute::getPosRoute());

			$o .= '<li class="nav-item active">';
			$o .= '<a class="nav-link active" href="'.$link.'"> 1 </a>';
			$o .= '</li>';
		}

		$o .= '</ul>';

		return $o;

	}
/*
	$link1 = Route::_(PhocacartRoute::getPosRoute(1));
$link2 = Route::_(PhocacartRoute::getPosRoute(2));
$link3 = Route::_(PhocacartRoute::getPosRoute(3));
?>


<ul class="nav nav-tabs">
  <li class="nav-item active">
    <a class="nav-link active" href="<?php echo $link1 ?>">1</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo $link2 ?>">2</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo $link3 ?>">3</a>
  </li>
</ul>*/

}
