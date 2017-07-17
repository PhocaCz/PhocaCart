<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.controlleradmin');

class PhocaCartCpControllerPhocaCartCommons extends JControllerAdmin
{
	protected	$option 		= 'com_phocacart';

	public function &getModel($name = 'PhocaCartCommon', $prefix = 'PhocaCartCpModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function saveOrderAjax() {
		
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		
		// TEST per URL
		//$pks = $this->input->get->get('cid', array(), 'array');
		//$order = $this->input->get->get('order', array(), 'array');
		//print r($pks);
		//print r($order);
		
		
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);
		$model = $this->getModel();
		$return = $model->saveorder($pks, $order);
	
		
		if ($return) { echo "1";}
		JFactory::getApplication()->close();
	}
}
?>