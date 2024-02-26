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

class PhocaCartCpControllerPhocacartContentTypes extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocacartContentType', $prefix = 'PhocaCartCpModel', $config = [])
	{
		return parent::getModel($name, $prefix, ['ignore_request' => true]);
	}
}
