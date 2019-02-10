<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartImport extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	
	public static function getFileType() {
		
		$db		= JFactory::getDbo();
		$user	= JFactory::getUser();
		$q 		= 'SELECT file_type'
				.' FROM #__phocacart_import'
			    .' WHERE user_id = '.(int) $user->id
				.' ORDER BY id'
				.' LIMIT 1';
		$db->setQuery($q);
		$type = $db->loadResult();
		return $type;
	}
	
	public static function getUploadedProducts($limitOffset = 0, $limitCount = 1) {
	

		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();
		
		$q 	= 'SELECT item'
			.' FROM #__phocacart_import'
			.' WHERE user_id = '.(int) $user->id
			.' AND type = 0'
			.' ORDER BY id';
		if ((int)$limitCount > 0) {
			$q .= ' LIMIT '.(int)$limitOffset. ', '.(int)$limitCount;
		}
		$db->setQuery($q);
		
		$products = $db->loadAssocList();
		return $products;
	}

	public static function getUploadedProductColumns() {
	

		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();
		
		$q 	= 'SELECT item'
			.' FROM #__phocacart_import'
			.' WHERE user_id = '.(int) $user->id
			.' AND type = 1'
			.' ORDER BY id'
			.' LIMIT 1';
		$db->setQuery($q);
		
		$productColumns = $db->loadAssocList();
		return $productColumns;
	}
	
	public function getForm($data = array(), $loadData = true) {
		return false;
	}
}
?>