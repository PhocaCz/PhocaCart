<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartExports extends JModelList
{
	protected $option 	= 'com_phocacart';	
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	
	public function getItemsCountProduct() {
		
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$q 		= 'SELECT COUNT(id)'
				.' FROM #__phocacart_products'
				.' ORDER BY id';
		$db->setQuery($q);
		$count = $db->loadResult();
		
		return $count;
		
	}
	
	public function getItemsCountExport() {
		
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$q 		= 'SELECT COUNT(id)'
				.' FROM #__phocacart_export'
			    .' WHERE user_id = '.(int) $user->id
				.' ORDER BY id';
		$db->setQuery($q);
		$count = $db->loadResult();
		
		return $count;
		
	}
}
?>