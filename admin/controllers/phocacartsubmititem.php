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
class PhocaCartCpControllerPhocaCartSubmititem extends PhocaCartCpControllerPhocaCartCommon
{

    function create() {
		$app	= Factory::getApplication();
		$cid 	= Factory::getApplication()->input->get( 'cid', array(), '', 'array' );
		ArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			$message = Text::_( 'COM_PHOCACART_SELECT_ITEM_CREATE_PRODUCT' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartsubmititems');
		}
		$message = '';
		$model = $this->getModel( 'phocacartsubmititem' );
		if(!$model->create($cid, $message)) {
			$message = PhocacartUtils::setMessage($message, Text::_( 'COM_PHOCACART_ERROR_PRODUCT_CREATING' ));
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartsubmititems');
		} else {
			//$message = Text::_( 'COM_PHOCACART_SUCCESS_THUMBS_REGENERATING' );
			$message = PhocacartUtils::setMessage($message, Text::_( 'COM_PHOCACART_SUCCESS_PRODUCT_CREATED' ));
			$app->enqueueMessage($message, 'message');
			$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
		}
	}

}
?>
