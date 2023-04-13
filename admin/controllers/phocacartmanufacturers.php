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
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocacartManufacturers extends PhocaCartCpControllerPhocaCartCommons
{

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerTask('unfeatured',	'featured');
	}

	public function &getModel($name = 'PhocacartManufacturer', $prefix = 'PhocaCartCpModel', $config = array()){
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

    public function featured()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app	= Factory::getApplication();
		$user   = Factory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_phocacart.phocacartmanufacturer.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				$this->setMessage($this->getError(), 'error');
			}
		}

		if (empty($ids)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NO_ITEMS_SELECTED'), 'error');
		} else {

			$model = $this->getModel();
			if (!$model->featured($ids, $value)) {
				$app->enqueueMessage($model->getError(), 'error');
			}
		}
		$this->setRedirect('index.php?option=com_phocacart&view=phocacartmanufacturers');
	}
}
?>
