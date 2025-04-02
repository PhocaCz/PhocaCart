<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\MVC\Controller\AdminControllerTrait;

class PhocaCartCpControllerPhocaCartCommons extends AdminController
{
	use AdminControllerTrait;

	protected $option = 'com_phocacart';

	public function execute($task)
	{
		$this->checkAdvancedPermission();
		return parent::execute($task);
	}

	public function &getModel($name = 'PhocaCartCommon', $prefix = 'PhocaCartCpModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function saveOrderAjax()
	{
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);
		$model = $this->getModel();
		$return = $model->saveorder($pks, $order);


		if ($return) { echo "1";}
		Factory::getApplication()->close();
	}
}

