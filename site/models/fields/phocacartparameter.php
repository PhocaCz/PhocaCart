<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! class_exists('PhocacartParameter')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/parameter/parameter.php');
}

$lang = JFactory::getLanguage();
$lang->load('com_phocacart');

class JFormFieldPhocacartParameter extends JFormField
{
	protected $type 		= 'PhocacartParameter';

	protected function getInput() {

		//$activeId = (int) $this->form->getValue('id');



		return PhocacartParameter::getAllParametersSelectBox($this->name, $this->id, $this->value /*$activeId*/, 'class="inputbox"','id' );
	}
}
?>
