<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\MVC\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\User\AdvancedACL;

trait AdminControllerTrait
{
	protected string $advancedPermission = '';

	protected function checkAdvancedPermission(): void
	{
		$params = \PhocacartUtils::getComponentParameters();
		if (!$params->get('use_advanced_permissions')) {
			return;
		}

		$action = $this->advancedPermission;

		if (empty($action)) {
			$input = Factory::getApplication()->getInput();
			$view = $input->get('view');
			if ($view) {
				$action = AdvancedACL::getActionFromView($view);
			}
		}

		if (empty($action)) {
			return;
		}

		if (!AdvancedACL::authorise($action)) {
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}
	}
}
