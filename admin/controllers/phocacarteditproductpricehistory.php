<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocaCartEditProductPriceHistory extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocaCartEditProductPriceHistory', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function save() {
	
		if (!Session::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$app					= Factory::getApplication();
		$jform					= $app->input->get('jform', array(), 'array');
		$id						= $app->input->get('id', 0, 'int');
		

		if (!empty($jform)) {
			$model = $this->getModel( 'phocacarteditproductpricehistory' );
			if(!$model->save($jform)) {
				$message = Text::_( 'COM_PHOCACART_ERROR_ADD_PRODUCT_PRICE_HISTORY' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = Text::_( 'COM_PHOCACART_SUCCESS_ADD_PRODUCT_PRICE_HISTORY' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditproductpricehistory&tmpl=component&id='.(int)$id);
		} else {
		
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditproductpricehistory&tmpl=component&id='.(int)$id);
		}
	}
	
}
?>