<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartCart extends JModelList
{
	protected	$option 		= 'com_phocacart';
	
	public function getData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		
		if ((int)$id > 0) {
			$db = JFactory::getDBO();
			$query = 'SELECT a.cart, a.user_id, u.name as user_name, u.username as user_username, a.date'
			. ' FROM #__phocacart_cart AS a'
			. ' LEFT JOIN #__users AS u ON u.id = a.user_id'
			. ' WHERE a.user_id = '.(int)$id
			. ' LIMIT 1';
			$db->setQuery( $query );
			$item = $db->loadObject();
			return $item;
		}
	}
	
	public function emptycart($id) {
		
		if ((int)$id > 0) {
		
			$db = JFactory::getDBO();
			$query = 'DELETE FROM #__phocacart_cart WHERE user_id = '.(int)$id;
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