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

class JFormFieldPhocacartRegions extends FormField
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

		$regions             = PhocacartRegion::getAllRegions();
		$data               = $this->getLayoutData();
		$data['options']    = (array)$regions;
		$data['value']      = $activeRegions;

        return $this->getRenderer($this->layout)->render($data);

		//return PhocacartRegion::getAllRegionsSelectBox($this->name.'[]', $this->id, $activeRegions, NULL,'id' );
	}
}
?>
