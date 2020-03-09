<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartTag extends PhocaCartCpControllerPhocaCartCommon {

    function countproducts() {
		$app	= JFactory::getApplication();
		$cid 	= JFactory::getApplication()->input->get( 'cid', array(), '', 'array' );
		\Joomla\Utilities\ArrayHelper::toInteger($cid);
		$redirect = 'index.php?option=com_phocacart&view=phocacarttags';

		if (count( $cid ) < 1) {
			$app->enqueueMessage(JText::_( 'COM_PHOCACART_SELECT_ITEM_COUNT_PRODUCTS' ), 'error');
			$app->redirect($redirect);
		}

		PhocacartCount::setProductCount($cid, 'tag');// Message set by Count Class
		PhocacartCount::setProductCount($cid, 'label');// Message set by Count Class
		$app->redirect($redirect);
	}


}
?>
