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
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocaCartFormfields extends PhocaCartCpControllerPhocaCartCommons
{
	
	public function __construct($config = array())
	{
		parent::__construct($config);	
		$this->registerTask('hidebilling',	'displaybilling');
		$this->registerTask('hideshipping',	'displayshipping');
		$this->registerTask('hideaccount',	'displayaccount');
		$this->registerTask('disablerequired',	'enablerequired');
	
	}

	public function &getModel($name = 'PhocaCartFormfield', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function displaybilling() {
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		
		$app	= Factory::getApplication();
		$cid	= $app->input->get('cid', array(), '', 'array');
		$data	= array('displaybilling' => 1, 'hidebilling' => 0);
		$task 	= $this->getTask();
		$value	= ArrayHelper::getValue($data, $task, 0, 'int');

		
		
		if (empty($cid)) {
			$app->enqueueMessage(Text::_($this->text_prefix.'_NO_ITEM_SELECTED'), 'error');
		} else {
			$model = $this->getModel();
			ArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_billing')) {
				$app->enqueueMessage($model->getError(), 'error');
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function displayshipping() {
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$app	= Factory::getApplication();
		$cid	= $app->input->get('cid', array(), '', 'array');
		$data	= array('displayshipping' => 1, 'hideshipping' => 0);
		$task 	= $this->getTask();
		$value	= ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			$app->enqueueMessage(Text::_($this->text_prefix.'_NO_ITEM_SELECTED'), 'error');
		} else {
			$model = $this->getModel();
			ArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_shipping')) {
				$app->enqueueMessage($model->getError(), 'error');
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function enablerequired() {
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		$app	= Factory::getApplication();
		$cid	= $app->input->get('cid', array(), '', 'array');
		$data	= array('enablerequired' => 1, 'disablerequired' => 0);
		$task 	= $this->getTask();
		$value	= ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			$app->enqueueMessage(Text::_($this->text_prefix.'_NO_ITEM_SELECTED'), 'error');
		} else {
			$model = $this->getModel();
			ArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'required')) {
				$app->enqueueMessage($model->getError(), 'error');
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_MADE_REQUIRED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_MADE_NOT_REQUIRED';
				} 
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function displayaccount() {
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		
		$app	= Factory::getApplication();
		$cid	= $app->input->get('cid', array(), '', 'array');
		$data	= array('displayaccount' => 1, 'hideaccount' => 0);
		$task 	= $this->getTask();
		$value	= ArrayHelper::getValue($data, $task, 0, 'int');

	
		if (empty($cid)) {
			$app->enqueueMessage(Text::_($this->text_prefix.'_NO_ITEM_SELECTED'), 'error');
		} else {
			$model = $this->getModel();
			ArrayHelper::toInteger($cid);
			if (!$model->displayItem($cid, $value, 'display_account')) {
				$app->enqueueMessage($model->getError(), 'error');
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_DISPLAYED';
				} else if ($value == 0) {
					$ntext = $this->text_prefix.'_N_ITEMS_HIDDEN';
				} 
				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
}
?>
