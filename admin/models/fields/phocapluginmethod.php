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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

class JFormFieldPhocaPluginMethod extends FormField
{
	protected $type 		= 'PhocaPluginMethod';

	protected function getInput() {

		$document = Factory::getDocument();
		HTMLHelper::_('jquery.framework', false);

		$plugintype	= 1;
		if (isset($this->element['plugintype']) && $this->element['plugintype'] > 0) {
			$plugintype 	= $this->element['plugintype'];// 1 Payment, 2 Shipping
		}

		if ($plugintype == 2) {
			$text	= Text::_('COM_PHOCACART_LOADING_SHIPPING_METHOD_OPTIONS_PLEASE_WAIT');
			$url 	= 'index.php?option=com_phocacart&view=phocacartparama&type='.(int)$plugintype.'&format=json&tmpl=component&'. Session::getFormToken().'=1';
		} else {
			$text	= Text::_('COM_PHOCACART_LOADING_PAYMENT_METHOD_OPTIONS_PLEASE_WAIT');
			$url 	= 'index.php?option=com_phocacart&view=phocacartparama&type='.(int)$plugintype.'&format=json&tmpl=component&'. Session::getFormToken().'=1';
		}

		$id		= $this->form->getValue('id');
		$method	= $this->form->getValue('method');

		$s 		= array();

		$s[] 	= 'function phLoadParams(value) {';
		$s[]	= '   var url = \''.$url.'\'';
		$s[] 	= '   var phAjax = \'<div class="ph-ajax-message"><div class="ph-loader"></div>\' + \''. htmlspecialchars($text).'\' + \'</div>\';';
		$s[] 	= '   jQuery("#ph-extended-params-msg").html(phAjax);';
		$s[] 	= '   jQuery("#ph-extended-params-msg").show();';
		$s[] 	= '   jQuery("#ph-extended-params").show();';
		$s[] 	= '   var dataPost = {};';
		$s[] 	= '   dataPost[\'method\'] = encodeURIComponent(value);';
		$s[] 	= '   dataPost[\'id\'] = encodeURIComponent('.(int)$id.');';
		$s[] 	= '   let phRequestActive = jQuery.ajax({';
		$s[] 	= '      url: url,';
		$s[] 	= '      type:\'POST\',';
		$s[] 	= '      data:dataPost,';
		$s[] 	= '      dataType:\'JSON\',';
		$s[] 	= '      success:function(data){';
		$s[] 	= '         if ( data.status == 1 ){';
		$s[] 	= '            jQuery("#ph-extended-params").html(data.message);';
		$s[] 	= '            jQuery("#ph-extended-params-msg").hide();';
		//$s[] 	= '	  		   jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Adm

		//$s[]	= '				jQuery(\'.hasTooltip\').tooltip({"html": true,"container": "body"});';//Reload Tooltip
		//$s[]	= '				jQuery(".hasPopover").popover({"html": true,"trigger": "hover focus","container": "body"});';
		$s[]	= '				var sandbox = jQuery("#phform_params_sandbox").val();';
		$s[]	= '				if (sandbox == 1) {';
		$s[]	= '					jQuery("#ph-sandbox-msg").show();';
		$s[]	= '				}';
		$s[]	= '				jQuery("#phform_params_sandbox").on("change", function() {';
		$s[]	= '					if (this.value == 1) {';
		$s[]	= '						jQuery("#ph-sandbox-msg").show();';
		$s[]	= '					} else {';
		$s[]	= '						jQuery("#ph-sandbox-msg").hide();';
		$s[]	= '					}';
		$s[]	= '				});';

		// Initialize showon when parameters are loaded by AJAX
		$s[] = '				Joomla.Showon.initialise(document);';

		$s[] 	= '         } else {';
		$s[] 	= '	            jQuery("#ph-extended-params-msg").html(data.error);';
		$s[] 	= '         }';
		$s[] 	= '      },';
		$s[] 	= '      error:function(data){';
		$s[] 	= '            jQuery("#ph-extended-params").html(data.message);';
		$s[] 	= '            //jQuery("#ph-extended-params-msg").hide();';
		$s[] 	= '      }';
		$s[] 	= '   });';
		$s[] 	= '}';

		$s[] 	= ' ';
		//$s[] 	= 'jQuery(document).ready(function() {';
		//$s[]	= 'jQuery("select").on("change", function() {});';
		//$s[] 	= '})';
		$s[] 	= ' ';

		if ((int)$this->form->getValue('id') > 0 && $this->form->getValue('method') != '') {
			$s[] 	= ' ';
			$s[] 	= 'jQuery(document).ready(function() {';
			$s[] 	= '   phLoadParams(\''.$this->form->getValue('method').'\');';
			$s[] 	= '})';
			$s[] 	= ' ';
		}
		//$document->addScriptDeclaration(implode("\n", $s));
		$app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->addInlineScript(implode("\n", $s));

		if ($plugintype == 2) {
			$methods = PhocacartShipping::getShippingPluginMethods();
		} else {
			$methods = PhocacartPayment::getPaymentPluginMethods();
		}
		return HTMLHelper::_('select.genericlist',  $methods,  $this->name, 'class="form-select" onchange="phLoadParams(this.value);"', 'value', 'text', $this->value, $this->id );
	}
}
?>
