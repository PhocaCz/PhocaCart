<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\FormController;
use Phoca\PhocaCart\MVC\Controller\AdminControllerTrait;

class PhocaCartCpControllerPhocaCartCommon extends FormController
{
	use AdminControllerTrait;

	protected $option = 'com_phocacart';

	public function execute($task)
	{
		$this->checkAdvancedPermission();
		return parent::execute($task);
	}
}

