<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

use Joomla\Utilities\ArrayHelper;

require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocacartPayments extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocacartPayment', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function setDefault() {
		
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');
		try {
			if (empty($pks)) {
				throw new Exception(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'));
			}
			$pks = ArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->setDefault($id);
			$this->setMessage(Text::_('COM_PHOCACART_SUCCESS_DEFAULT_SET'));
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), 500);
		}

		$this->setRedirect('index.php?option=com_phocacart&view=phocacartpayments');
	}
	
	public function unsetDefault(){
		
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$pks = $this->input->post->get('cid', array(), 'array');
		try {
			if (empty($pks)) {
				throw new Exception(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'));
			}
			$pks = ArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->unsetDefault($id);
			$this->setMessage(Text::_('COM_PHOCACART_SUCCESS_DEFAULT_UNSET'));
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), 500);
		}

		$this->setRedirect('index.php?option=com_phocacart&view=phocacartpayments');
	}
}
?>