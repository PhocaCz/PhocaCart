<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocacartOrders extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocacartOrder', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}


    function exportshipping() {


        $app	= Factory::getApplication();
		$cid 	= Factory::getApplication()->input->get( 'cid', array(), '', 'array' );

		$pks   = ArrayHelper::toInteger((array) $cid);

		$message = '';

		if (count( $pks ) < 1) {
			$message = Text::_( 'COM_PHOCACART_SELECT_ITEM_EXPORT' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartorders');
		}

		$shipping = new PhocacartShipping();
		$shippingId = $app->getUserStateFromRequest('com_phocacart.phocacartorders.filter.shipping_id', 'filter_shipping_id', '');

		$shippingInfo = $shipping->getShippingMethod($shippingId);
		if ($shippingId > 0 && isset($shippingInfo->method) && $shippingInfo->method != '') {
			$result = Dispatcher::dispatch(new Event\Shipping\ExportShippingBranchInfo('com_phocacart.phocacartorders', $pks, $shippingInfo, [
				'pluginname' => $shippingInfo->method
			]));
			echo trim(implode("\n", $result->getArgument('result', [])));
		}

		/*
		 * $message = Text::_( 'COM_PHOCACART_THERE_IS_NO_FILE_READY_TO_DOWNLOAD_EXPORT_PRODUCTS_FIRST' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartexports');
			return;
		 */
    }
}
