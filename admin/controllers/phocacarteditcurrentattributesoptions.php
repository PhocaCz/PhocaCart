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
class PhocaCartCpControllerPhocaCartEditCurrentAttributesOptions extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocaCartEditCurrentAttributesOptions', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
?>
