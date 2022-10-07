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
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditProductPointGroup extends ListModel
{
	protected	$option 		= 'com_phocacart';
	
	
	public function save($data, $productId) {
		
		if (!empty($data)) {
			return PhocacartGroup::storeProductPointGroupsById($data, $productId);
		}
	}
}
?>