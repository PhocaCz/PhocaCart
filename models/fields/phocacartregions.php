<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartRegions extends JFormField
{
	protected $type 		= 'PhocaCartRegions';

	protected function getInput() {
		
		$id = (int) $this->form->getValue('id');
		
		if (isset($this->element['table'])) {
			switch (strtolower($this->element['table'])) {	
				case "payment":
					$table = 'payment';
				break;
				
				case "shipping":
				default:
					$table = 'shipping';
				break;
			}
		} else {
			$table = 'shipping';
		}

		$activeRegions = array();
		if ((int)$id > 0) {
			$activeRegions	= PhocaCartRegion::getRegions($id, 1, $table);
		}
			
		return PhocaCartRegion::getAllRegionsSelectBox($this->name.'[]', $this->id, $activeRegions, NULL,'id' );
	}
}
?>