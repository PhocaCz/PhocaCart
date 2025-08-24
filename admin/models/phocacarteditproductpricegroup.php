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
use Phoca\PhocaCart\MVC\Model\AdminModelTrait;
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditProductPriceGroup extends ListModel
{
    use AdminModelTrait;
	protected	$option 		= 'com_phocacart';


	public function save($data, $productId) {

		if (!empty($data)) {
			return PhocacartGroup::storeProductPriceGroupsById($data, $productId);
		}
	}
}
?>
