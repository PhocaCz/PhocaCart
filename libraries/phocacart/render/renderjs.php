<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
class PhocaCartRenderJs
{

	public static function renderAjaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message">'. JHtml::_( 'image', 'media/com_phocacart/images/administrator/icon-loading5.gif', '')
			   .'&nbsp; '. htmlspecialchars($text) . '</div>';
		}
		$o .= '</div>';
		return $o;
	}
	
	public static function renderAjaxDoRequest($text) {
	
		$s 	= array();	 
		$s[] 	= 'function phDoRequest(url, manager, value) {';
		$s[] 	= 'var phAjaxTop = \'<div id="ph-ajaxtop-message">'. JHtml::_( 'image', 'media/com_phocacart/images/administrator/icon-loading5.gif', ''). ' &nbsp; \' + \''. htmlspecialchars($text).'\' + \'</div>\';';
		$s[] 	= '   jQuery("#ph-ajaxtop").html(phAjaxTop);';
		$s[] 	= '   jQuery("#ph-ajaxtop").show();';
		$s[] 	= '   var dataPost = {};';
		$s[] 	= '   dataPost[\'filename\'] = encodeURIComponent(value);';	
		$s[] 	= '   dataPost[\'manager\'] = manager;';
		$s[] 	= '   phRequestActive = jQuery.ajax({';
		$s[] 	= '      url: url,';
		$s[] 	= '      type:\'POST\',';
		$s[] 	= '      data:dataPost,';
		$s[] 	= '      dataType:\'JSON\',';
		$s[] 	= '      success:function(data){';
		$s[] 	= '         if ( data.status == 1 ){';
		$s[] 	= '            jQuery("#ph-ajaxtop-message").html(data.message);';
		$s[] 	= '            phRequestActive = null;';
		$s[] 	= '            setTimeout(function(){';
		$s[] 	= '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] 	= '		        jQuery(".ph-result-txt").remove();';
		$s[] 	= '	           }, 2500);';
		$s[] 	= '         } else {';
		$s[] 	= '	           jQuery("#ph-ajaxtop-message").html(data.error);';
		$s[] 	= '            phRequestActive = null;';
		$s[] 	= '	           setTimeout(function(){';
		$s[] 	= '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] 	= '		        jQuery(".ph-result-txt").remove();';
		$s[] 	= '	           }, 3500);';
		$s[] 	= '         }';
		$s[] 	= '      }';
		$s[] 	= '   });';
		$s[] 	= '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxDoRequestAfterChange($url, $manager = 'product', $value = 'imageCreateThumbs') {
		//$s[] 	= '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s 	= array();
		$s[] 	= 'jQuery(document).ready(function() {';
		$s[] 	= '   jQuery( \'.'.$value.'\' ).live("change", function() {';
		$s[] 	= '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s[] 	= '   })';
		$s[] 	= '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderAjaxDoRequestAfterPaste($url, $manager = 'product') {
		$s 	= array();
		$s[] 	= 'function phAddValue(id, title) {';
		$s[] 	= '   document.getElementById(id).value = title;';
		$s[] 	= '   SqueezeBox.close();';
		$s[] 	= '   phDoRequest(\''.$url.'\', \''.$manager.'\', title );';
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowImage($i, $newRow, $poPup) {
		$s 	= array();
		$s[] 	= 'var phRowCountImage = '.$i.';';
		$s[] 	= 'function phAddRowImage() {';
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] 	= '   jQuery(\'#phrowboximage\').append(phNewRow);';
		$s[] 	= '	  phRowCountImage++;';
		
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';
		$s[] 	= '	  /* Initialize the modal button again */';
		$s[] 	= '	  /* SqueezeBox.initialize({}); */';
		$s[] 	= '	  SqueezeBox.assign($$(\''.$poPup.'\'), {';
		$s[] 	= '	     parse: \'rel\'';
		$s[] 	= '	  });';
		
		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowImage(id) {';
		$s[] 	= '	  jQuery(\'#phrowimage\' + id).remove();';
		$s[] 	= '	  var phCountRowImage = jQuery(\'.ph-row-image\').length;';
		$s[] 	= '   if (phCountRowImage == 0) {';
		$s[] 	= '      jQuery(\'#phrowboximage\').empty();';
		$s[] 	= '   }';
		//$s[] 	= '	  phRowCountImage--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowAttribute($i, $newRow) {
		$s 	= array();
		$s[] 	= 'var phRowCountAttribute = '.$i.';';
		$s[] 	= 'function phAddRowAttribute() {';
		$s[]	= '   var phRowOptionAttributeId = phRowCountAttribute;';  // We need this Id for Options 
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] 	= '   jQuery(\'#phrowboxattribute\').append(phNewRow);';
		$s[] 	= '	  phRowCountAttribute++;';
		
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';
		
		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowAttribute(id) {';
		//$s[] 	= '	  jQuery(\'#phrowattribute\' + id).remove();';
		$s[] 	= '	  jQuery(\'#phAttributeBox\' + id).remove();';
		$s[] 	= '	  var phCountRowAttribute = jQuery(\'.ph-row-attribute\').length;';
		$s[] 	= '   if (phCountRowAttribute == 0) {';
		$s[] 	= '      jQuery(\'#phrowboxattribute\').empty();';
		$s[] 	= '   }';
		//$s[] 	= '	  phRowCountAttribute--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowOption($j,  $newRow, $newHeader) {
		$s 	= array();
		$s[] 	= 'var phRowCountOption = '.$j.';';
		$s[] 	= 'function phAddRowOption(attrid) {';
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] 	= '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] 	= '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] 	= '	  if(phCountRowOption == 0) {';
		$s[] 	= '	     jQuery(\'#phrowboxoption\' + attrid).append(phNewHeader);';
		$s[] 	= '	  }';
		$s[] 	= '   jQuery(\'#phrowboxoption\' + attrid).append(phNewRow);';
		$s[] 	= '	  phRowCountOption++;';
		
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';

		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowOption(id, attrid) {';
		//$s[] 	= '	  jQuery(\'#phrowoption\' + id).remove();';
		$s[] 	= '	  jQuery(\'#phOptionBox\' + attrid + id).remove();';
		$s[] 	= '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] 	= '   if (phCountRowOption == 0) {';
		$s[] 	= '      jQuery(\'#phrowboxoption\' + attrid).empty();';
		$s[] 	= '   }';
		//$s[] 	= '	  phRowCountOption--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowSpecification($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] 	= 'var phRowCountSpecification = '.$i.';';
		$s[] 	= 'function phAddRowSpecification() {';
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] 	= '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] 	= '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] 	= '	  if(phCountRowSpecification == 0) {';
		$s[] 	= '	     jQuery(\'#phrowboxspecification\').append(phNewHeader);';
		$s[] 	= '	  }';
		$s[] 	= '   jQuery(\'#phrowboxspecification\').append(phNewRow);';
		$s[] 	= '	  phRowCountSpecification++;';
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';
		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowSpecification(id) {';
		//$s[] 	= '	  jQuery(\'#phrowSpecification\' + id).remove();';
		$s[] 	= '	  jQuery(\'#phSpecificationBox\' + id).remove();';
		$s[] 	= '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] 	= '   if (phCountSpecification == 0) {';
		$s[] 	= '      jQuery(\'#phrowboxspecification\').empty();';
		$s[] 	= '   }';
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderJsAppendValueToUrl() {
		$s 	= array();
		$s[] 	= 'jQuery(document).ready(function() {';
		$s[] 	= '   var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
		$s[] 	= '   var stringToSend = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
		$s[] 	= '   var newUri = jQuery(\'.modal_jform_download_file\').attr(\'href\') + stringToSend;';
		$s[] 	= '   jQuery(\'.modal_jform_download_file\').attr("href", newUri);';
		$s[] 	= '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderBillingAndShippingSame() {
		
		// BILLING AND SHIPPING THE SAME
		// If checkbox will be enabled (Shipping and Billing address is the same) - remove the required protection of input fields
		$s 	= array();

		$s[] = 'jQuery(document).ready(function(){';

		$s[] = '   phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		$s[] = '   phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		$s[] = '   phDisableRequirement();';
	  
		$s[] = '   jQuery("#phCheckoutBillingSameAsShipping").live(\'click\', function() {';
		$s[] = '      phDisableRequirement();';
		$s[] = '   })';
	  
		$s[] = '   function phDisableRequirement() {';
	  
		$s[] = '      var checked = jQuery(\'#phCheckoutBillingSameAsShipping\').prop(\'checked\');';

		$s[] = '      if (checked) {';
		//jQuery(".phShippingFormFieldsRequired").prop("disabled", true);//.trigger("chosen:updated");// Not working - using readonly instead
		//jQuery(".phShippingFormFields").prop("readonly", true);// Not working for Select box
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'aria-required\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'required\');';	
		$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', \'#f0f0f0\');';
		$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', \'#f0f0f0\');';	
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
		$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		$s[] = '      } else {';
		  
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'aria-required\', \'true\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'required\', \'true\');';
		//jQuery(".phShippingFormFields").removeAttr(\'readonly\'); 
		$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', phBgInputCh);'; 
		$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', phBgInputI);';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
		$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		$s[] = '      }';
		$s[] = '   }';
		$s[] = '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
}
?>