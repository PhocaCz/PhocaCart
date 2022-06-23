<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\Field\ListField;

class JFormFieldPhocacartGroups extends ListField
{
	protected $type 		= 'PhocacartGroups';

	protected function getInput() {


		$tableType	= (string)$this->element['table'];

		switch($tableType) {

			case 1:
				// User table by user_id not by id
				$id	= (int) $this->form->getValue('user_id');
			break;

			default:
				$id	= (int) $this->form->getValue('id');
			break;
		}
		$activeGroups = array();


		if ((int)$id > 0) {
			$activeGroups	= PhocacartGroup::getGroupsById($id, $tableType, 1);
		}

		if (empty($activeGroups)) {
			$activeGroups	= PhocacartGroup::getDefaultGroup(1);
		}

		$groups             = PhocacartGroup::getAllGroups();
		$data               = $this->getLayoutData();
		$data['options']    = (array)$groups;
		$data['value']      = $activeGroups;

        return $this->getRenderer($this->layout)->render($data);

		//return PhocacartGroup::getAllGroupsSelectBox($this->name.'[]', $this->id, $activeGroups, NULL, 'id' );
	}
}
?>
