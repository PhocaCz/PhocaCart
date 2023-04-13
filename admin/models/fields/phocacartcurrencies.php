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

class JFormFieldPhocacartCurrencies extends FormField
{
	protected $type 		= 'PhocacartCurrencies';

	protected function getInput() {

		$db =Factory::getDBO();

		$id = (int) $this->form->getValue('id');

		if (isset($this->element['table'])) {
			switch (strtolower($this->element['table'])) {
				case "payment":
				default:
					$table = 'payment';
				break;
			/*
				case "zone":
					$table = 'zone';
				break;

				case "shipping":
				default:
					$table = 'shipping';
				break;*/
			}
		} else {
			$table = 'payment';
		}

		$activeCurrencies = array();
		if ((int)$id > 0) {
			$activeCurrencies	= PhocacartCurrency::getFieldCurrencies($id, 1, $table);
		}


		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_currencies AS a'
				. ' ORDER BY a.id';
		$db->setQuery($query);
		$currencies = $db->loadObjectList();
		$data               = $this->getLayoutData();
		$data['options']    = (array)$currencies;
		$data['value']      = $activeCurrencies;

        return $this->getRenderer($this->layout)->render($data);

		//return PhocacartRegion::getAllRegionsSelectBox($this->name.'[]', $this->id, $activeCurrencies, NULL,'id' );
	}
}
?>
