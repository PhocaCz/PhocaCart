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
use Joomla\CMS\Form\FormField;

require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/bootstrap.php');

class JFormFieldPhocacartParameter extends FormField
{
	protected $type 		= 'PhocacartParameter';

	public function __construct($form = null)
	{
		parent::__construct($form);

		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_phocacart');
	}

	protected function getInput() {
		return PhocacartParameter::getAllParametersSelectBox($this->name, $this->id, $this->value /*$activeId*/, 'class="form-control"','id' );
	}
}

