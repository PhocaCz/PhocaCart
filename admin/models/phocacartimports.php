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
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartImports extends ListModel
{
	protected $option 	= 'com_phocacart';	
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function getItemsCountImport() {
		
		$db		= $this->getDbo();
		$user	= Factory::getUser();
		$q 		= 'SELECT COUNT(id)'
				.' FROM #__phocacart_import'
			    .' WHERE user_id = '.(int) $user->id
				.' AND type = 0';
				//.' GROUP by id'
				//.' ORDER BY id';
		$db->setQuery($q);
		$count = $db->loadResult();
		
		return $count;
		
	}
}
?>