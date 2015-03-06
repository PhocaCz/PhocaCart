<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartFormItems
{
	

	public function __construct() {}
	
	public function getFormItems($billing = 1, $shipping = 1, $account = 0) {
		$db 					= JFactory::getDBO();
		$user 					= JFactory::getUser();
		$userLevels				= implode (',', $user->getAuthorisedViewLevels());
		
		$where 		= array();
		if ((int)$billing == 1) {
			$where[]	= '(a.display_billing = 1 OR a.display_shipping = 1)';
			//$where[]	= 'a.display_shipping = 1';// they are loaded together (shipping and billing loaded togehter)
												  // if billing is disabled and shipping enabled we still need to load the billing too
		}
		if ((int)$shipping == 1) {
			$where[]	= '(a.display_shipping = 1 OR a.display_billing = 1)';
			//$where[]	= 'a.display_billing = 1';// they are loaded together
		}
		if ((int)$account == 1) {
			$where[]	= 'a.display_account = 1';
		}
		
		$where[]	= 'a.published = 1';
		
		// ACCESS
		$where[] = " a.access IN (".$userLevels.")";
		
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		$query = 'SELECT a.id, a.title, a.label, a.description, a.type, a.default, a.class, a.read_only, a.required,'
				.' a.display_billing, a.display_shipping, a.display_account, a.validate, a.unique, a.published, a.access'
				.' FROM #__phocacart_form_fields AS a'
				. $where
				.' ORDER BY a.ordering';
		$db->setQuery($query);

		$fields = $db->loadObjectList();
		
		
		return $fields;
	}
	
	
}