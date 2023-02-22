<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartManufacturer extends PhocaCartCpControllerPhocaCartCommon {

    function countproducts() {
		$app	= Factory::getApplication();
		$cid 	= Factory::getApplication()->input->get( 'cid', array(), '', 'array' );
		ArrayHelper::toInteger($cid);
		$redirect = 'index.php?option=com_phocacart&view=phocacartmanufacturers';

		if (count( $cid ) < 1) {
			$app->enqueueMessage(Text::_( 'COM_PHOCACART_SELECT_ITEM_COUNT_PRODUCTS' ), 'error');
			$app->redirect($redirect);
		}

		PhocacartCount::setProductCount($cid, 'manufacturer');// Message set by Count Class
		$app->redirect($redirect);
	}
}
?>
