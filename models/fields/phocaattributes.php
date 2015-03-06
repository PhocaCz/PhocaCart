<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaAttributes extends JFormField
{
	protected $type 		= 'PhocaAttributes';

	protected function getInput() {
		
		$id = (int) $this->form->getValue('id');

		$activeAttributes = array();
		if ((int)$id > 0) {
			$activeAttributes	= PhocaCartAttribute::getAttributesById($id, 1);
		}
			
		return PhocaCartAttribute::getAllAttributesSelectBox($this->name.'[]', $this->id, $activeAttributes, NULL,'id' );
	}
}
?>