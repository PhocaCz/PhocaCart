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

class JFormFieldPhocacartShipping extends FormField
{
	protected $type 		= 'PhocacartShipping';

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

		$activeMethods = array();
		if ((int)$id > 0) {
			$activeMethods	= PhocacartShipping::getShippingMethods($id, 1, $table);
		}

		$shippings 			= PhocacartShipping::getAllShippingMethods();
		$data               = $this->getLayoutData();
		$data['options']    = (array)$shippings;
		$data['value']      = $activeMethods;


		return $this->getRenderer($this->layout)->render($data);

		//return PhocacartShipping::getAllShippingMethodsSelectBox($this->name.'[]', $this->id, $activeMethods, NULL,'id' );
	}
}
?>
