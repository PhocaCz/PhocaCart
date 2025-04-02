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
use Joomla\Registry\Registry;

class JFormFieldPhocacartShippingParamsOrderStatus extends FormField
{
	protected $type 		= 'PhocacartShippingParamsOrderStatus';

	protected function getInput() {

		//$id = (int) $this->form->getValue('id');

		// AJAX POWERED PARAMETER
		$app 		= Factory::getApplication();
		$id 		= (int)$app->input->post->get('id');// Get ID from shipping method
		$method 	= $app->input->post->get('method');

		$activeMethods = array();
		if ($id > 0) {

			$db =Factory::getDBO();


			$query = 'SELECT a.params';
			$query .= ' FROM #__phocacart_shipping_methods AS a'
					.' WHERE a.id = '.$id;
			$db->setQuery($query);

			$params = $db->loadResult();

			$registry = new Registry;
			$registry->loadString($params);
			$params = $registry;

			$activeMethods = $params->get('status_zero', array());

		}

		$statuses 			= PhocacartOrderStatus::getOptions();

		$data               = $this->getLayoutData();
		$data['options']    = $statuses;
		$data['value']      = $activeMethods;


		return $this->getRenderer($this->layout)->render($data);

		//return PhocacartShipping::getAllShippingMethodsSelectBox($this->name.'[]', $this->id, $activeMethods, NULL,'id' );
	}
}
?>
