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
use Joomla\CMS\Language\Text;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartRegion extends PhocaCartCpControllerPhocaCartCommon {


	function importregions() {
		$app	= Factory::getApplication();
		$model = $this->getModel( 'phocacartregion' );
		if(!$model->importregions()) {
			$message = Text::_( 'COM_PHOCACART_ERROR_REGIONS_IMPORT' );
			$app->enqueueMessage($message, 'error');
		} else {
			$message = Text::_( 'COM_PHOCACART_SUCCESS_REGIONS_IMPORT' );
			$app->enqueueMessage($message, 'message');
		}

		$app->redirect('index.php?option=com_phocacart&view=phocacartregions');
	}
}
?>