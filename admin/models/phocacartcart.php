<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocacartCart extends ListModel
{
	protected	$option 		= 'com_phocacart';

	public function getData() {

		$app			= Factory::getApplication();
		$userid			= $app->input->get('userid', 0, 'int');
		$vendorid		= $app->input->get('vendorid', 0, 'int');
		$ticketid		= $app->input->get('ticketid', 0, 'int');
		$unitid			= $app->input->get('unitid', 0, 'int');
		$sectionid		= $app->input->get('sectionid', 0, 'int');


		if ((int)$userid > 0) {
			$db = Factory::getDBO();
			$query = 'SELECT a.cart, a.user_id, a.vendor_id, a.ticket_id, a.unit_id, a.section_id, u.name as user_name, u.username as user_username, a.date'
			. ' FROM #__phocacart_cart_multiple AS a'
			. ' LEFT JOIN #__users AS u ON u.id = a.user_id'
			. ' WHERE a.user_id = '.(int)$userid
			. ' AND a.vendor_id = '.(int)$vendorid
			. ' AND a.ticket_id = '.(int)$ticketid
			. ' AND a.unit_id = '.(int)$unitid
			. ' AND a.section_id = '.(int)$sectionid
			. ' LIMIT 1';
			$db->setQuery( $query );
			$item = $db->loadObject();

			return $item;
		}
	}

	public function emptycart($userid, $vendorid = 0, $ticketid = 0, $unitid = 0, $sectionid = 0) {

		if ((int)$userid > 0) {

			$db = Factory::getDBO();
			$query = 'DELETE FROM #__phocacart_cart_multiple'
				. ' WHERE user_id = '.(int)$userid
				. ' AND vendor_id = '.(int)$vendorid
				. ' AND ticket_id = '.(int)$ticketid
				. ' AND unit_id = '.(int)$unitid
				. ' AND section_id = '.(int)$sectionid;
			$db->setQuery( $query );

			if ($db->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}
}
?>
