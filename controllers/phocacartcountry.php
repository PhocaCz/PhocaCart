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
class PhocaCartCpControllerPhocaCartCountry extends PhocaCartCpControllerPhocaCartCommon {


	function importcountries() {
		$app	= JFactory::getApplication();
		$model = $this->getModel( 'phocacartcountry' );
		if(!$model->importcountries()) {
			$message = JText::_( 'COM_PHOCACART_ERROR_COUNTRIES_IMPORT' );
			$app->enqueueMessage($message, 'error');
		} else {
			$message = JText::_( 'COM_PHOCACART_SUCCESS_COUNTRIES_IMPORT' );
			$app->enqueueMessage($message, 'message');
		}

		$app->redirect('index.php?option=com_phocacart&view=phocacartcountries');
	}
}
?>