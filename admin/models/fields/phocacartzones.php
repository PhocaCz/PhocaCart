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

class JFormFieldPhocacartZones extends FormField
{
	protected $type 		= 'PhocacartZones';

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

		$activeZones = array();
		if ((int)$id > 0) {
			$activeZones	= PhocacartZone::getZones($id, 1, $table);
		}

		$zones              = PhocacartZone::getAllZones();
		$data               = $this->getLayoutData();
		$data['options']    = (array)$zones;
		$data['value']      = $activeZones;

        return $this->getRenderer($this->layout)->render($data);

		//return PhocacartZone::getAllZonesSelectBox($this->name.'[]', $this->id, $activeZones, NULL,'id' );
	}
}
?>
