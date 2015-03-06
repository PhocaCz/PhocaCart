<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocaCartFormfields extends PhocaCartCpControllerPhocaCartCommons
{
	
	public function __construct($config = array())
	{
		parent::__construct($config);	
		$this->registerTask('hidebilling',	'displaybilling');
		$this->registerTask('hideshipping',	'displayshipping');
		$this->registerTask('hideaccount',	'displayaccount');
	
	}

	public function &getModel($name = 'PhocaCartFormfield', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function displaybilling() {
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('displaybilling' => 1, 'hidebilling' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			$model = $this->getModel();
			JArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_billing')) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function displayshipping() {
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('displayshipping' => 1, 'hideshipping' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			$model = $this->getModel();
			JArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_shipping')) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function displayaccount() {
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('displayaccount' => 1, 'hideaccount' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			$model = $this->getModel();
			JArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_account')) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
}
?>