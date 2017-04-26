<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocacartRegions extends JFormField
{
	protected $type 		= 'PhocacartRegions';

	protected function getInput() {
		
		$id = (int) $this->form->getValue('id');
		
		if (isset($this->element['table'])) {
			switch (strtolower($this->element['table'])) {	
				case "payment":
					$table = 'payment';
				break;
				
				case "zone":
					$table = 'zone';
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
			$activeRegions	= PhocacartRegion::getRegions($id, 1, $table);
		}
			
		return PhocacartRegion::getAllRegionsSelectBox($this->name.'[]', $this->id, $activeRegions, NULL,'id' );
	}
}
?>