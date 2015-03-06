<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartCountries extends JFormField
{
	protected $type 		= 'PhocaCartCountries';

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

		$activeCountries = array();
		if ((int)$id > 0) {
			$activeCountries	= PhocaCartCountry::getCountries($id, 1, $table);
		}
			
		return PhocaCartCountry::getAllCountriesSelectBox($this->name.'[]', $this->id, $activeCountries, NULL,'id' );
	}
}
?>