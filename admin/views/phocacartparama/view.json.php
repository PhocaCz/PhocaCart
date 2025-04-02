<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartParamA extends HtmlView
{

	function display($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="alert alert-error">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

        //$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$app		= Factory::getApplication();
		$method		= $app->input->get( 'method', '', 'string'  );
		$id			= $app->input->get( 'id', '', 'int'  );
		$type		= $app->input->get( 'type', '', 'int'  ); // 1) payment plugin 2) shipping plugin


		//$method		= PhocacartUtilsSettings::getPaymentMethod((int)$method);

		// TEMP
		//$method = 'paypal';
		//$id = 1;
		// index.php?option=com_phocacart&view=phocacartparama&format=json&tmpl=component&5bf6b09593b13dd0b717228bb82296a9=1&id=1
		$model = $this->getModel();
		//$method = 'coupon';
		if ($type == 2) {
			$model->setFormName('com_phocacart.phocacartshippingmethod', 'phocacartshippingmethod'); // Abstract XML

		} else {
			$model->setFormName('com_phocacart.phocacartpaymentmethod', 'phocacartpaymentmethod'); // Abstract XML
		}

		$form		= $model->getForm();
		$item		= $model->getItem();

		/*
		 * PAYMENT - method selected in Phoca Cart (x001) (or SHIPPINNG))
		 * PAYMENT METHOD - method type like Paypal, Cash on Delivery - set in plugin
		 * Payment method parameters are defined in plugins plugins/pcp/paypal_standard.xml e.g.
		 * This ajax loads the XML from plugin and bind the data with common payment table:
		 * #__phocacart_payment_methods (#__phocacart_shipping_methods)
		 *
		 * In plugin, parameters are only defined in form tag, but stored are in params column of table: #__phocacart_payment_methods
		 *
		 * administrator\components\com_phocacart\views\phocacartparama\view.json.php - ajax loading
		 * administrator\components\com_phocacart\models\phocacartparama.php - model where the plugin parameters are pasted to abstract xml
		 * administrator\components\com_phocacart\models\forms\phocacartpaymentmethod.xml - abastract class so we can load parameters from
		 * plugin
		 * administrator\components\com_phocacart\models\phocacartpayment.php - here standard parameters of payment are stored and the
		 * plugin parameters are converted to params column and stored in payment table: #__phocacart_payment_methods
		 *
		 * Ajax is loading when changing payment method or when starting/loading the payment to edit (at start when editing)
		 * The loading is set in field: administrator\components\com_phocacart\models\fields\phocapaymentmethod.php
		 *
		 */

		$o = '';
		$i = 0;
		if ($form) {

			$form->bind($item->params);// if empty (new id), nothing will be assigned

			$fieldSets = $form->getFieldsets();// the xml must have a fieldset name: <fieldset name="payment">

			foreach ($fieldSets as $name => $fieldSet) {

				$o .= '<div class="tab-pane" id="'. $name.'">';
				if (isset($fieldSet->description) && !empty($fieldSet->description)) {
					$o .= '<p class="tab-description">'.Text::_($fieldSet->description).'</p>';
				}

				$i = 0;
				foreach ($form->getFieldset($name) as $field) {

					$o .= '<div class="control-group" ';


					$description = $field->description;
					$descriptionOutput = '';
					if ($description != '') {
						$descriptionOutput = '<div role="tooltip">'.Text::_($description).'</div>';
					}


					if($field->showon) {
                        //$wa->useScript('showon');
                        $datashowon = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($field->showon, str_replace('jform', 'phform',$field->formControl), $field->group)) . '\'';
                        $o .= ' '.$datashowon;
                    }

					$o .= '>';


					if (!$field->hidden && $name != "permissions") {
						$o .= '<div class="control-label">' . str_replace('jform', 'phform', $field->label) .  $descriptionOutput . '</div>';
					}
					$o .= '<div class="';
					if ($name != "permissions") {
						$o .= 'controls';
					}
					$o .= '"';


                    $o .= '>' . str_replace('jform', 'phform', $field->input) .'</div>';
					$o .= '</div>';
					$i++; // count of parameters, if there is no parameter, see below - the message will say no parameters found
				}
				$o .= '</div>';
			}
		}



		$message = '';
		if ($i == 0 || $o == '') {

			if ($type == 2) {
				$message = '<div class="ph-extended-params-inbox">' . Text::_('COM_PHOCACART_THERE_ARE_NO_PARAMETERS_FOR_THIS_SHIPPING_METHOD') . '</div>';
			} else {
				$message = '<div class="ph-extended-params-inbox">' . Text::_('COM_PHOCACART_THERE_ARE_NO_PARAMETERS_FOR_THIS_PAYMENT_METHOD') . '</div>';
			}
		} else {
			$message = '<div class="ph-extended-params-inbox">' . $o . '</div>';
		}


		$response = array(
		'status'	=> '1',
		'message' => $message);
		echo json_encode($response);
		return;
	}
}
?>
