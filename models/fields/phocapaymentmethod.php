<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaPaymentMethod extends JFormField
{
	protected $type 		= 'PhocaPaymentMethod';

	protected function getInput() {
		
		$document = JFactory::getDocument();
		JHtml::_('jquery.framework', false);
		
		$url 	= 'index.php?option=com_phocacart&view=phocacartparama&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		$text	= JText::_('COM_PHOCACART_LOADING_PAYMENT_METHOD_OPTIONS_PLEASE_WAIT');
		$id		= $this->form->getValue('id');
		$method	= $this->form->getValue('method');
		$s 		= array();	
		
		$s[] 	= 'function phLoadParams(value) {';
		$s[]	= '   var url = \''.$url.'\'';
		$s[] 	= '   var phAjax = \'<div>'. JHtml::_( 'image', 'media/com_phocacart/images/administrator/icon-loading5.gif', ''). ' &nbsp; \' + \''. htmlspecialchars($text).'\' + \'</div>\';';
		$s[] 	= '   jQuery("#ph-extended-params-msg").html(phAjax);';
		$s[] 	= '   jQuery("#ph-extended-params-msg").show();';
		$s[] 	= '   jQuery("#ph-extended-params").show();';
		$s[] 	= '   var dataPost = {};';
		$s[] 	= '   dataPost[\'method\'] = encodeURIComponent(value);';
		$s[] 	= '   dataPost[\'id\'] = encodeURIComponent('.(int)$id.');';	
		$s[] 	= '   phRequestActive = jQuery.ajax({';
		$s[] 	= '      url: url,';
		$s[] 	= '      type:\'POST\',';
		$s[] 	= '      data:dataPost,';
		$s[] 	= '      dataType:\'JSON\',';
		$s[] 	= '      success:function(data){';
		$s[] 	= '         if ( data.status == 1 ){';
		$s[] 	= '            jQuery("#ph-extended-params").html(data.message);';
		$s[] 	= '            jQuery("#ph-extended-params-msg").hide();';
		$s[] 	= '	  		   jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		
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
		
		$s[] 	= '         } else {';
		$s[] 	= '	           jQuery("#ph-extended-params-msg").html(data.error);';
		$s[] 	= '         }';
		$s[] 	= '      }';
		$s[] 	= '   });';
		$s[] 	= '}';
		
		$s[] 	= ' ';
		//$s[] 	= 'jQuery(document).ready(function() {';
		//$s[]	= 'jQuery("select").on("change", function() {
  //alert( this.value );
//});';
		//$s[] 	= '})';
		$s[] 	= ' ';
		
		
		if ((int)$this->form->getValue('id') > 0 && $this->form->getValue('method') != '') {
			$s[] 	= ' ';
			$s[] 	= 'jQuery(document).ready(function() {';
			$s[] 	= '   phLoadParams(\''.$this->form->getValue('method').'\');';
			$s[] 	= '})';
			$s[] 	= ' ';
		}
		$document->addScriptDeclaration(implode("\n", $s));
		
		$methods = PhocaCartPayment::getPaymentMethods();
		
		return JHTML::_('select.genericlist',  $methods,  $this->name, 'class="inputbox" onchange="phLoadParams(this.value);"', 'value', 'text', $this->value, $this->id );
	}
}
?>