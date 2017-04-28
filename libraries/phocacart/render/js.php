<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
final class PhocacartRenderJs
{
	private function __construct(){}

	public static function renderAjaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> '. htmlspecialchars($text) . '</div>';
		}
		$o .= '</div>';
		return $o;
	}
	
	public static function renderAjaxDoRequest($text) {
	
		$s 	= array();	 
		$s[] = 'function phDoRequest(url, manager, value) {';
		$s[] = 'var phAjaxTop = \'<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> \' + \''. htmlspecialchars($text).'\' + \'</div>\';';
		$s[] = '   jQuery("#ph-ajaxtop").html(phAjaxTop);';
		$s[] = '   jQuery("#ph-ajaxtop").show();';
		$s[] = '   var dataPost = {};';
		$s[] = '   dataPost[\'filename\'] = encodeURIComponent(value);';	
		$s[] = '   dataPost[\'manager\'] = manager;';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		$s[] = '            jQuery("#ph-ajaxtop-message").html(data.message);';
		$s[] = '            phRequestActive = null;';
		$s[] = '            setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 2500);';
		$s[] = '         } else {';
		$s[] = '	           jQuery("#ph-ajaxtop-message").html(data.error);';
		$s[] = '            phRequestActive = null;';
		$s[] = '	           setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 3500);';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxDoRequestAfterChange($url, $manager = 'product', $value = 'imageCreateThumbs') {
		//$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery( \'.'.$value.'\' ).live("change", function() {';
		$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s[] = '   })';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderAjaxDoRequestAfterPaste($url, $manager = 'product') {
		$s 	= array();
		$s[] = 'function phAddValue(id, title, titleModal) {';
		$s[] = '   document.getElementById(id).value = title;';
		//$s[] = '   SqueezeBox.close();';// close
		$s[] = '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] = '   phDoRequest(\''.$url.'\', \''.$manager.'\', title );';
		$s[] = '}';

		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowImage($i, $newRow) {
		$s 	= array();
		$s[] = 'var phRowCountImage = '.$i.';';
		
		// We have only two modal box - 1. for image, 2. for additinal images (all)
		// So we need to get info, which additional images we have clicked
		// For example, if there are 3 additional images and we click on second, in modal box, we need to get this value to set right value
		// phRowImage variable is a part of iframe url (the iframe url is build dynamically)
		// administrator\components\com_phocacart\models\fields\phocaselectfilenameimage.php
		// $link = 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id . '\'+ (phRowImage) +\'';
		// Here
		// administrator\components\com_phocacart\views\phocacartmanager\tmpl\default_file.php
		// we get is as $this->field
		// Here
		// administrator\components\com_phocacart\libraries\phocacart\render\renderadminview.php
		// in function additionalImagesRow we call this function
		//$s[] = 'var phRowImage = 0;';
		//$s[] = 'function setPhRowImageId(rowImgId) {';
		//$s[] = '   phRowImage = rowImgId;';
		//$s[] = '}';
		
		$s[] = 'function phAddRowImage() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '   jQuery(\'#phrowboximage\').append(phNewRow);';
		$s[] = '	  phRowCountImage++;';
		
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		//$s[] = '	  /* Initialize the modal button again - FOR IMAGES */';
		//$s[] = '	  /* SqueezeBox.initialize({}); */';
		//$s[] = '	  SqueezeBox.assign($$(\''.$poPup.'\'), {';
		//$s[] = '	     parse: \'rel\'';
		//$s[] = '	  });';
		
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowImage(id) {';
		$s[] = '	  jQuery(\'#phrowimage\' + id).remove();';
		$s[] = '	  var phCountRowImage = jQuery(\'.ph-row-image\').length;';
		$s[] = '   if (phCountRowImage == 0) {';
		$s[] = '      jQuery(\'#phrowboximage\').empty();';
		$s[] = '   }';
		//$s[] = '	  phRowCountImage--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowAttribute($i, $newRow) {
		$s 	= array();
		$s[] = 'var phRowCountAttribute = '.$i.';';
		$s[] = 'function phAddRowAttribute() {';
		$s[]	= '   var phRowOptionAttributeId = phRowCountAttribute;';  // We need this Id for Options 
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   jQuery(\'#phrowboxattribute\').append(phNewRow);';
		$s[] = '	  phRowCountAttribute++;';
		
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowAttribute(id) {';
		//$s[] = '	  jQuery(\'#phrowattribute\' + id).remove();';
		$s[] = '	  jQuery(\'#phAttributeBox\' + id).remove();';
		$s[] = '	  var phCountRowAttribute = jQuery(\'.ph-row-attribute\').length;';
		$s[] = '   if (phCountRowAttribute == 0) {';
		$s[] = '      jQuery(\'#phrowboxattribute\').empty();';
		$s[] = '   }';
		//$s[] = '	  phRowCountAttribute--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowOption($j,  $newRow, $newHeader) {
		$s 	= array();
		
		/* $s[] = '
		jQuery(function($) {
			jQuery(function($) {
				SqueezeBox.initialize({});
			

			SqueezeBox.assign($(\'a.modal_jform_optionimage00\').get(), {parse: \'rel\'});
			SqueezeBox.assign($(\'a.modal_jform_optionimage01\').get(), {parse: \'rel\'});
			SqueezeBox.assign($(\'a.modal_jform_optionimage02\').get(), {parse: \'rel\'});
			//SqueezeBox.assign($(\'a.modal_jform_optionimage03\').get(), {parse: \'rel\'});
			//SqueezeBox.assign($(\'a.modal_jform_optionimage04\').get(), {parse: \'rel\'});
			});
		});
		function jModalClose() {SqueezeBox.close();}'	;*/
		
		$s[] = 'var phRowCountOption = '.$j.';';
		
		
		//$s[] = 'var phRowImgOption = \'00\';';
		//$s[] = 'function setPhRowImgOptionId(attrId, id) {';
		//$s[] = '  phRowImgOption = attrId.toString() + id.toString();';
		//$s[] = '}';
		
		
		$s[] = 'function phAddRowOption(attrid) {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] = '	  if(phCountRowOption == 0) {';
		$s[] = '	     jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewRow);';
			
		// Modal Box Popup wee need to initialize it for every newly added item
		//$s[]	= '   /* Initialize the modal button again - FOR IMAGES IN ATTRIBUTE OPTIONS */';
		//$s[]	= '   SqueezeBox.initialize({});';
		//$s[]	= '   var phModalFormOption = \'a.modal_jform_optionimage\' + attrid + phRowCountOption;';
		//$s[]	= '   SqueezeBox.assign(jQuery(phModalFormOption).get(), {parse: \'rel\'});';
		//$s[]	= '   function jModalClose() {SqueezeBox.close();}';
		// End Modal Box
		
		$s[]	= ' ';
		$s[]	= '   var phMiniColorsId =  \'#jform_optioncolor\' + attrid + phRowCountOption;';// Reload minicolors
		$s[]	= '   jQuery(phMiniColorsId).minicolors({
							control: \'hex\',
							format: \'hex\',
							position: \'default\',
							theme: \'bootstrap\'
						});';
		$s[]	= ' ';
		$s[] = '	  phRowCountOption++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';

		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowOption(id, attrid) {';
		//$s[] = '	  jQuery(\'#phrowoption\' + id).remove();';
		$s[] = '	  jQuery(\'#phOptionBox\' + attrid + id).remove();';
		$s[] = '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] = '   if (phCountRowOption == 0) {';
		$s[] = '      jQuery(\'#phrowboxoptionjs\' + attrid).empty();';// clean header of option added by js
		$s[] = '      jQuery(\'#phrowboxoption\' + attrid).empty();';// clean header of option loaded by php
		$s[] = '   }';
		//$s[] = '	  phRowCountOption--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxoptionjs - header created by javascript
		// #phrowboxoption - header created by php/mysql
		
	}
	
	public static function renderJsManageRowSpecification($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] = 'var phRowCountSpecification = '.$i.';';
		$s[] = 'function phAddRowSpecification() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] = '	  if(phCountRowSpecification == 0) {';
		$s[] = '	     jQuery(\'#phrowboxspecification\').append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxspecification\').append(phNewRow);';
		$s[] = '	  phRowCountSpecification++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowSpecification(id) {';
		//$s[] = '	  jQuery(\'#phrowSpecification\' + id).remove();';
		$s[] = '	  jQuery(\'#phSpecificationBox\' + id).remove();';
		$s[] = '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] = '   if (phCountRowSpecification == 0) {';
		$s[] = '      jQuery(\'#phrowboxspecification\').empty();';
		$s[] = '      jQuery(\'#phrowboxspecificationheader\').empty();';
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	

	
	public static function renderJsManageRowDiscount($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] = 'var phRowCountDiscount = '.$i.';';
		$s[] = 'function phAddRowDiscount() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowDiscount = jQuery(\'.ph-row-discount\').length;';
		$s[] = '	  if(phCountRowDiscount == 0) {';
		$s[] = '	     jQuery(\'#phrowboxdiscount\').append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxdiscount\').append(phNewRow);';
		$s[] = '	  phRowCountDiscount++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		if(PhocacartUtils::isJCompatible('3.7')) {
			$s[] = '	var elements = document.querySelectorAll(".field-calendar");';
			$s[] = '	for (i = 0; i < elements.length; i++) {';
			$s[] = '		JoomlaCalendar.init(elements[i]);';
			$s[] = '	}';
		}
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		// CALENDAR IS RELOADED DIRECTLY BELOW THE NEW ROW administrator\components\com_phocacart\libraries\phocacart\render\adminview.php
			
		$s[] = ' ';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowDiscount(id) {';
		//$s[] = '	  jQuery(\'#phrowDiscount\' + id).remove();';
		$s[] = '	  jQuery(\'#phDiscountBox\' + id).remove();';
		$s[] = '	  var phCountRowDiscount = jQuery(\'.ph-row-discount\').length;';
		$s[] = '   if (phCountRowDiscount == 0) {';
		$s[] = '      jQuery(\'#phrowboxdiscount\').empty();';
		$s[] = '      jQuery(\'#phrowboxdiscountheader\').empty();';
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	
	public static function renderJsAppendValueToUrl() {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
		$s[] = '   var stringToSend = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
		$s[] = '   var newUri = jQuery(\'.modal_jform_download_file\').attr(\'href\') + stringToSend;';
		$s[] = '   jQuery(\'.modal_jform_download_file\').attr("href", newUri);';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderBillingAndShippingSame() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		//$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
		$load_chosen 		= $paramsC->get( 'load_chosen', 1 );
		

		// BILLING AND SHIPPING THE SAME
		// If checkbox will be enabled (Shipping and Billing address is the same) - remove the required protection of input fields
		$s 	= array();

		$s[] = 'jQuery(document).ready(function(){';

		//$s[] = '   phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		//$s[] = '   phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		$s[] = '   phDisableRequirement();';
	  
		$s[] = '   jQuery("#phCheckoutBillingSameAsShipping").live(\'click\', function() {';
		$s[] = '      phDisableRequirement();';
		$s[] = '   })';
	  
		$s[] = '   function phDisableRequirement() {';
		
		//$s[] = '   var phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		//$s[] = '   var phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		
		$s[] = '		var selectC = jQuery("#jform_country_phs");';
		$s[] = '		var selectR = jQuery("#jform_region_phs");';
	  
		$s[] = '      var checked = jQuery(\'#phCheckoutBillingSameAsShipping\').prop(\'checked\');';

		$s[] = '      if (checked) {';
		//jQuery(".phShippingFormFieldsRequired").prop("disabled", true);//.trigger("chosen:updated");// Not working - using readonly instead
		//jQuery(".phShippingFormFields").prop("readonly", true);// Not working for Select box
		
		$s[] = '		jQuery(".phShippingFormFields").prop("readonly", true);';
		$s[] = '		selectC.attr("disabled", "disabled");';
		$s[] = '		selectR.attr("disabled", "disabled");';
		
		
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'aria-required\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").removeAttr(\'required\');';	
		//$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', \'#f0f0f0\');';
		//$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', \'#f0f0f0\');';	
		if ($load_chosen == 1) {
			$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
			$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		}
		$s[] = '      } else {';
		  
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'aria-required\', \'true\');';
		$s[] = '	     jQuery(".phShippingFormFieldsRequired").prop(\'required\', \'true\');';
		//jQuery(".phShippingFormFields").removeAttr(\'readonly\'); 
		//$s[] = '	     jQuery("#phShippingAddress .chosen-single").css(\'background\', phBgInputCh);'; 
		//$s[] = '	     jQuery(".phShippingFormFields").css(\'background\', phBgInputI);';
		
		$s[] = '	    jQuery(".phShippingFormFields").removeAttr(\'readonly\');';
		$s[] = '		selectC.removeAttr("disabled");';
		$s[] = '		selectR.removeAttr("disabled");';
		if ($load_chosen == 1) {
			$s[] = '	     jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");';
			$s[] = '	     jQuery(".phShippingFormFields").trigger("chosen:updated");';
		}
		$s[] = '      }';
		$s[] = '   }';
		$s[] = '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	

	
	
	public static function renderAjaxAddToCart() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_cart_method= $paramsC->get( 'add_cart_method', 0 );
		
		// We need to refresh checkout site when AJAX used for removing or adding products to cart
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'checkout') {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		if ($add_cart_method == 0) {
			return false;
		}
		
		if ($add_cart_method == 2) {
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_cart_method > 0) {
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.add&format=json&'. JSession::getFormToken().'=1&checkoutview='.(int)$cView;
			$s[] = 'jQuery(document).ready(function(){';
			$s[] = '	jQuery(".phItemCartBoxForm").on(\'submit\', function (e) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phCheckoutView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(this).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery("#phItemCartBox").html(data.item);';
			$s[] = '					jQuery("#phItemCartBoxCount").html(data.count);';
			$s[] = '					jQuery("#phItemCartBoxTotal").html(data.total);';
			if ($add_cart_method == 2) {
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phAddToCartPopup").modal();';
			}
			if ($add_cart_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '						if (phCheckoutView == 1) {';
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}
			$s[] = '			   } else if (data.status == 0){';
			$s[] = '					jQuery("#phItemCartBox").html(data.error);';
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			$s[] = '		e.preventDefault();';
			$s[] = '       return false;';	
			$s[] = '	})';
			$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	
	public static function renderAjaxAddToCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		if ($add_compare_method == 0) {
			return false;
		}
		if ($add_compare_method == 2) {
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.add&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemCompareBoxFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phComparisonView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery("#phItemCompareBox").html(data.item);';
			$s[] = '					jQuery("#phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phAddToComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '						if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		
		if ($add_compare_method == 0) {
			return false;
		}
		if ($add_compare_method == 2) {
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.remove&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemRemoveCompareFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phComparisonView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery("#phItemCompareBox").html(data.item);';
			$s[] = '					jQuery("#phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				// Display the popup
				$s[] = ' 					jQuery("#phContainerModuleCompare").html(data.popup);';
				$s[] = ' 					jQuery("#phRemoveFromComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '						if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}	
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	

	public static function renderAjaxAddToWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );
		
		// We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'wishlist') {
			$wView = 1;
		} else {
			$wView = 0;
		}
		
		if ($add_wishlist_method == 0) {
			return false;
		}
		if ($add_wishlist_method == 2) {
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.add&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemWishListBoxFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phWishListView = '.(int)$wView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery("#phItemWishListBox").html(data.item);';
			$s[] = '					jQuery("#phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phAddToWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '						if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );
		
		// We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		if ($option == 'com_phocacart' && $view == 'wishlist') {
			$wView = 1;
		} else {
			$wView = 0;
		}
		
		
		if ($add_wishlist_method == 0) {
			return false;
		}
		if ($add_wishlist_method == 2) {
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
		
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.remove&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemRemoveWishListFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phWishListView = '.(int)$wView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			$s[] = '					jQuery("#phItemWishListBox").html(data.item);';
			$s[] = '					jQuery("#phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				// Display the popup
				$s[] = ' 					jQuery("#phContainerModuleWishList").html(data.popup);';
				$s[] = ' 					jQuery("#phRemoveFromWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '						if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '							setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			   			}';
			}	
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	
	

	
	
	
	
	public static function renderAjaxQuickViewBox() {
		
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		//$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
		// When quick view loaded, change the price in quickview
		$attribute_change_price = $paramsC->get( 'attribute_change_price', 1 );
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		/*if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}*/
		
		
			JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		
		
		
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&view=item&format=json&tmpl=component&'. JSession::getFormToken().'=1';
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = '	function phItemQuickViewBoxFormAjax(phItemId) {';
			$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			//$s[] = '		var phComparisonView = '.(int)$cView.'';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"JSON",';
			$s[] = '			success: function(data){';
			$s[] = '				if (data.status == 1){';
			//$s[] = '					jQuery("#phItemCompareBox").html(data.item);';
			
			//$s[] = ' 					jQuery("#phQuickViewPopupBody").html(data.popup);'; added in ajax
			$s[] = ' 					jQuery("#phContainer").html(data.popup); ';
			$s[] = ' 					jQuery("#phQuickViewPopup").modal();';
			$s[] = '	  				jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
			
			if ($attribute_change_price == 1) {
				$s[] = '					phAjaxChangePrice();';
			}
				
			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		
		
		
		
		
		
		
		
		/*
		
		
		
		$paramsC 	= JComponentHelper::getParams('com_phocacart') ;
		$app 		= JFactory::getApplication();

		JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		
		
	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&view=item';

$s[] = 'jQuery(document).ready(function(){';			
			$s[] = '


	 
jQuery(\'.test\').on(\'click\', function(e) {
	
	
	
	
    var src = jQuery(this).attr(\'data-src\');
   
   
	var height = jQuery(this).attr(\'data-height\') || \'100%\';
    var width = jQuery(this).attr(\'data-width\') || \'100%\';
	
	

    jQuery("#phQuickViewPopup iframe").attr({\'src\':src,
                               \'height\': height,
                               \'width\': width});
					jQuery("#phQuickViewPopup").modal();		   
							  		
	});';
$s[] = '})';


	//		function phItemQuickViewBoxFormAjax(phItemId) {';
			
	//		$s[] = 'jQuery(\'.modal\').on(\'shown.bs.modal\',function(){      
  //jQuery(this).find(\'iframe\').attr(\'src\',\'http:\/\/phoca.cz\')
//})';
			/*$s[] = '		var phUrl 	= "'. $urlAjax.'";';
			$s[] = '		var phItem = \'#\' + phItemId;';
			$s[] = '		var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '		phRequest = jQuery.ajax({';
			$s[] = '			type: "POST",';
			$s[] = '			url: phUrl,';
			$s[] = '			async: "false",';
			$s[] = '			cache: "false",';
			$s[] = '			data: phData,';
			$s[] = '			dataType:"HTML",';
			$s[] = '			success: function(data){';
			$s[] = '				if (1 == 1){';
		//	$s[] = '					jQuery("#phItemWishListBox").html(data.item);';
			
				$s[] = ' 					jQuery("#phQuickViewPopupBody").html(data);';
				$s[] = ' 					jQuery("#phQuickViewPopup").modal();';
			

			$s[] = '			   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			   }';
			$s[] = '			}';
			$s[] = '		})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	*//*
			//$s[] = '	}';
			//$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));*/
		
	}
	
	
		/*
	 * Change Price
	 * select box (standard, image, color)
	 * check box
	 */
	 
	
	public static function renderAjaxChangeProductPriceByOptions($id = 0, $class = '') {
		
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$attribute_change_price = $paramsC->get( 'attribute_change_price', 1 );
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		if ($attribute_change_price == 0) {
			return false;
		}
		if ($id == 0) {
			// Quick View Popup
			$id = 'jQuery(\'#phItemPriceBoxForm\').data(\'id\')';
		} else {
			$id = (int)$id;
		}

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.changepricebox&format=json&'. JSession::getFormToken().'=1';
		
		
		
		$s[] = '	function phAjaxChangePrice(){';
		$s[] = '		var phUrl 	= "'. $urlAjax.'";';
		$s[] = '		var phId 	= '.$id.';';
		$s[] = '		var phClass = "'.$class.'";';
		
		$s[] = '		var phDataA1 = jQuery("#phItemPriceBoxForm select.ph-item-input-set-attributes").serialize();';
		$s[] = '		var phDataA2 = jQuery("#phItemPriceBoxForm .ph-item-input-set-attributes :checkbox").serialize();';
		
		$s[] = '		var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass;';
		$s[] = '		jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		$s[] = '			async: "false",';
		$s[] = '			cache: "false",';
		$s[] = '			data: phData,';
		$s[] = '			dataType:"JSON",';
		$s[] = '			success: function(data){';
		$s[] = '				if (data.status == 1){';
		$s[] = '					jQuery("#phItemPriceBox").html(data.item);';
		$s[] = '			   } else {';
		//$s[] = '					// Don\'t change the price box, don't render any error message
		$s[] = '			   }';
		$s[] = '			}';
		$s[] = '		})';
		$s[] = '	}';
		$s[] = ' ';
		
		$s[] = 'jQuery(document).ready(function(){';
		// Select Box
		$s[] = '	jQuery(document).on(\'change\', \'#phItemPriceBoxForm select.ph-item-input-set-attributes\', function(){';	
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '		phAjaxChangePrice();';
		$s[] = '	})';
		
		// Checkbox
		// Unfortunately, we cannot run event:
		// 1. CHANGE as because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
		// 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
		// So we run click on div box over the checkbox which works and don't run ajax 3x
		//$s[] = '	jQuery(document).on(\'change\', \'#phItemPriceBoxForm .ph-item-input-set-attributes :checkbox\', function(){';
		$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';			
		$s[] = '		phAjaxChangePrice();';
		$s[] = '	})';
		
		// Change the price on time view when site is initialized
		// Because some parameters can be selected as default
		// Automatically start only in item view, not in category or another view
		if ($option == 'com_phocacart' && $view == 'item') {
			$s[] = '		phAjaxChangePrice();';
		}
		
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	
	
	
	

	
	// Singleton - do not load items more times from database
	/*public static function renderLoaderFullOverlay() {
		
		if( self::$fullOverlay == '' ) {
			
			$s 	= array();
			$s[] = 'var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = 'phOverlay.appendTo(document.body);';
			$s[] = 'jQuery("#phOverlay").fadeIn().css("display","block");';
			self::$fullOverlay = implode("\n", $s);
		}		
		return self::$fullOverlay;
	}*/
	
	// loading.gif - whole page
	// Singleton - check if loaded - xxx No Singleton, it must be inside each javascript function
	public static function renderLoaderFullOverlay() {
		//static $fullOverlay = 0;
		//if( $fullOverlay == 0) {
			$s 	= array();
			$s[] = 'if (phA == 2) {';
			$s[] = '';// 2 means false
			$s[] = '} else {';
			$s[] = '   var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = '   phOverlay.appendTo(document.body);';
			$s[] = '   jQuery("#phOverlay").fadeIn().css("display","block");';
			$s[] = '}';
			$fullOverlay = 1;
			return implode("\n", $s);
		//} else {
		//	return '';
		//}		
		
		/*
		var phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
		phOverlay.appendTo(document.body);
		var $loading = jQuery('#phOverlay').hide();
		jQuery(document)
		  .ajaxStart(function () {
			$loading.show();
		  })
		  .ajaxStop(function () {
			$loading.hide();
		  });
		*/
	}
	
	public static function renderLoaderDivOverlay($outputDiv) {
		
		$overlay['start'] = '';
		$overlay['end'] = '';
		
		$s[] = '   var phOverlay = jQuery(\'<div id="phOverlayDiv"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '   phOverlay.appendTo("'.$outputDiv.'");';
		$s[] = '   jQuery("#phOverlayDiv").fadeIn().css("display","block");';
		
		$overlay['start'] = implode("\n", $s);
		
		$s2[] = '   jQuery("#phOverlay").fadeIn().css("display","none");';
	
		$overlay['end'] = implode("\n", $s2);
		
		return $overlay;
	}
	public static function renderMagnific() {
		
		$document	= JFactory::getDocument();
		$document->addScript(JURI::base(true).'/media/com_phocacart/js/magnific/jquery.magnific-popup.min.js');
		$document->addStyleSheet(JURI::base(true).'/media/com_phocacart/js/magnific/magnific-popup.css');
		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	jQuery(\'#phImageBox\').magnificPopup({';
		$s[] = '		tLoading: \''.JText::_('COM_PHOCACART_LOADING').'\',';
		$s[] = '		tClose: \''.JText::_('COM_PHOCACART_CLOSE').'\',';
		$s[] = '		delegate: \'a.magnific\',';
		$s[] = '		type: \'image\',';
		$s[] = '		mainClass: \'mfp-img-mobile\',';
		$s[] = '		zoom: {';
		$s[] = '			enabled: true,';
		$s[] = '			duration: 300,';
		$s[] = '			easing: \'ease-in-out\'';
		$s[] = '		},';
		$s[] = '		gallery: {';
		$s[] = '			enabled: true,';
		$s[] = '			navigateByImgClick: true,';
		$s[] = '			tPrev: \''.JText::_('COM_PHOCACART_PREVIOUS').'\',';
		$s[] = '			tNext: \''.JText::_('COM_PHOCACART_NEXT').'\',';
		$s[] = '			tCounter: \''.JText::_('COM_PHOCACART_MAGNIFIC_CURR_OF_TOTAL').'\'';
		$s[] = '		},';
		$s[] = '		image: {';
		$s[] = '			titleSrc: function(item) {';
		$s[] = '				return item.el.attr(\'title\');';
		$s[] = '			},';
		$s[] = '			tError: \''.JText::_('COM_PHOCACART_IMAGE_NOT_LOADED').'\'';
		$s[] = '		}';
		$s[] = '	});';
		$s[] = '});';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
				
	}
	
	public static function renderPrettyPhoto() {
		$document	= JFactory::getDocument();
		JHTML::stylesheet( 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js');
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({';
		$s[] = '  social_tools: 0';		
		$s[] = '  });';
		$s[] = '})';

		$document->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderOverlay(){
		
		$s	 = array();
		$s[] = '		var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '		phOverlay.appendTo(document.body);';
		$s[] = '		jQuery("#phOverlay").fadeIn().css("display","block");';
		return implode("\n", $s);
	}

	
	public static function renderPhocaAttribute() {
		$document	= JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute.js');
	}
	
	
	public static function renderImportExportItems($url, $messageBox, $formId, $count, $successMessage, $reload = 0) {
		
		$document	= JFactory::getDocument();
		
		$s   = array();
		$s[] = 'function phUpdateProgress(percentage){';
		$s[] = '   if(percentage > 100) {percentage = 100;}';
		$s[] = '   jQuery(\'#phProgressBar\').css(\'width\', percentage+\'%\');';
		$s[] = '   jQuery(\'#phProgressBar\').html(percentage+\'%\');';
		$s[] = '}';
		
		$s[] = ' ';

		$s[] = 'function phImportAllItems(e) {';
		$s[] = '   e.stopPropagation();';
		$s[] = '   e.preventDefault();';

		$s[] = '   var phMaxItems = e.data.a1;';// see the last but one row of this s
		$s[] = '   var phLastPage = phMaxItems;';
		$s[] = '   var phItemCount = 1;';
		$s[] = '   var phMessageBoxId 	= \'#'.$messageBox.'\';';
		$s[] = '   var formId			= \'#'.$formId.'\';';

		$s[] = '   function phGetNextItem() {';
		$s[] = '      phCurrentItemCount = phItemCount;';

		$s[] = '      if (phItemCount <= phMaxItems) {';
		$s[] = '          jQuery.ajax({';
		$s[] = '             url: \''.$url.'\' + \'&p=\' + phItemCount + \'&lp=\' + phLastPage,';
		$s[] = '             method: \'GET\',';
		$s[] = '             async: true,';
		$s[] = '             success: function(data) {';
		$s[] = '                if (data.status == 1) {';
		$s[] = '                   ++phItemCount;';
		$s[] = '                   phUpdateProgress(Math.round((phItemCount/phMaxItems)*100));';
		$s[] = '                   if (phMaxItems > phCurrentItemCount) {';
		$s[] = '	                  phGetNextItem();';
		$s[] = '                   }';
		$s[] = '                }';
		$s[] = ' ';
		$s[] = '                if (phMaxItems == phCurrentItemCount) {';
		$s[] = '                   jQuery(".circle").addClass("circle-active");';
		$s[] = '                   jQuery(phMessageBoxId).html(\'<div class="alert alert-success"><button class="close" type="button" data-dismiss="alert">×</button>'.$successMessage.'</div>\');';

		if ($reload == 1) {
			// TO DO enable
			$s[] =	'	           window.setTimeout(function () {document.location.reload();}, 1000);';
			$s[] = self::renderOverlay();
		}

		$s[] = '			    }';
		$s[] = '             }';// end success
		$s[] = '          });';// end ajax
		$s[] = '	   }';
		$s[] = '   }';
		$s[] = '   phGetNextItem();';
		$s[] = '}';
		
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(\'#'.$formId.'\').on(\'submit\',{a1: '.(int)$count.'}, phImportAllItems);';
		$s[] = '})';
		
		$document->addScriptDeclaration(implode("\n", $s));
		
	}
	
	public static function renderOverlayOnSubmit($id) {
		
		$document	= JFactory::getDocument();
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'#'.$id.'\').on(\'submit\', function(){';
		$s[] = self::renderOverlay();
		$s[] = '   })';
		$s[] = '})';

		$document->addScriptDeclaration(implode("\n", $s));
	}
	
	/*
	 * Swap large images by attributes
	 */
	public static function renderPhSwapImageInitialize($formId, $dynamicChangeImage = 0, $ajax = 0) {
		
		if ($dynamicChangeImage == 1) {
			$s = array();
			$s[] = 'jQuery(document).ready(function() {';
			$s[] = '	var phSIO1'.(int)$formId.' = new phSwapImage;';
			$s[] = '	phSIO1'.(int)$formId.'.Init(\'.ph-item-image-full-box\', \'#phItemPriceBoxForm\', \'.ph-item-input-set-attributes\', 0);';
			$s[] = '	phSIO1'.(int)$formId.'.Display();';
			$s[] = '});';
			if ($ajax == 1) {
				return '<script type="text/javascript">'.implode("\n", $s).'</script>';
			} else {
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
			}
			
		}
	}
	
	/*
	 * Type Color Select Box and Image Select Box - displaying images or colors instead of select box
	 */
	 
	public static function renderPhAttributeSelectBoxInitialize($id, $type, $ajax = 0) {
	
		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	var phAO'.(int)$id.' = new phAttribute;';
		$s[] = '	phAO'.(int)$id.'.Init('.(int)$id.', '.(int)$type.');';
		$s[] = '	phAO'.(int)$id.'.Display();';
		$s[] = '});';
		
		if ($ajax == 1) {
			return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		} else {
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	
	}
	
	/*
	 * Checkbox color and image - set by bootstrap button - active class
	 */ 
	 
	 
	/*
	* This jQuery function replaces HTML 5 for checking required checkboxes
	* If there is a required group of checkboxes: 
	* components\com_phocacart\views\item\tmpl\default.php reqC (cca line 277)
	* it checks for at least one checked checkbox
	*/
	
	public static function renderCheckBoxRequired($id, $ajax = 0) {
		
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'#phItemPriceBoxForm button[type="submit"]\').on(\'click\', function() {';
		$s[] = '      var phCheckBoxGroup = jQuery(".checkbox-group-'.(int)$id.' input:checkbox");';
		$s[] = '      phCheckBoxGroup.prop(\'required\', true);';
		$s[] = '      if(phCheckBoxGroup.is(":checked")){';
		$s[] = '         phCheckBoxGroup.prop(\'required\', false);';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '})';
		
		if ($ajax == 1) {
			return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		} else {
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	/*
	 * Two features in one function
	 * 1) return required parts 
	 *    If the attribute is required, return different required parts (attribute - html5, class - jquery, span - heading)
	 * 2) Initialize JQuery Check for required fields working with HTML 5
	 *    Checkboxes cannot be checked by HTML 5, so we need help of jQuery which manage for example:
	 *    There are 3 checkboxes - one selected, two not (It is OK but not for HTML5)
	 */
	public static function renderRequiredParts($id, $required) {
		
		// If the attribute is required
		$req['attribute'] 	= '';// Attribute - required field HTML 5
		$req['span']		= '';// Span - displayed * next to title
		$req['class']		= '';// Class - Checkboxes cannot be checked per HTML 5,
								 //jquery used PhocacartRenderJs::renderCheckBoxRequired()
		
		if($required) {
			$req['attribute'] 	= ' required="" aria-required="true"';
			$req['span'] 		= ' <span class="ph-req">*</span>';
			$req['class'] 		= ' checkbox-group-'.(int)$id.' required';
		}
		return $req;	
	}
	
	
	/* When the modal window will be focused, check again the statuses of all the items 
	*/
	public static function renderAjaxDoRequestWizardAfterChange($url, $value = 'phClickBtn') {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   phDoRequestWizard(\''.$url.'\');';

		$s[] = '   jQuery(window).on("blur focus", function(e) {';
		$s[] = '      var prevType = jQuery(this).data("prevType");';
		$s[] = ' ';
		$s[] = '	  if (prevType != e.type) {';
		$s[] = '	     switch (e.type) {';
		$s[] = '		   case "blur":';
		$s[] = '		   break;';
		$s[] = '		   case "focus":';
		$s[] = '              phDoRequestWizard(\''.$url.'\');';
		$s[] = '		   break;';
		$s[] = '	     }';
		$s[] = '      }';
		$s[] = ' ';
		$s[] = '   jQuery(this).data("prevType", e.type);';
		$s[] = '   })';
		
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/* Ask for the changes, if something changes - e.g. category is created
	 * return this to inform and update status in wizard modal window
	 */
	
	public static function renderAjaxDoRequestWizard() {
	
		$s 	= array();	 
		$s[] = 'function phDoRequestWizard(url) {';
		$s[] = '   var dataPost = {};';
		$s[] = '   var phTrue   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_CREATED').'</div>\';';
		$s[] = '   var phTrueAdd   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_ADDED').'</div>\';';
		$s[] = '   var phTrueEdit   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_EDITED').'</div>\';';
		$s[] = '   var phTrueAll = 1';
		$s[] = '   var phFalse  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_CREATED_YET').'</div>\';';
		$s[] = '   var phFalseAdd  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_ADDED_YET').'</div>\';';
		$s[] = '   var phFalseEdit  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_EDITED_YET').'</div>\';';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		
		// Category
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.category == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardCategory").html(phOutput);';
		
		// Tax
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.tax == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardTax").html(phOutput);';
		
		// Product
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.product == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardProduct").html(phOutput);';
		
		// Shipping
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.shipping == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardShipping").html(phOutput);';
		
		// Payment
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.payment == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardPayment").html(phOutput);';
		
		// Country
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.country == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardCountry").html(phOutput);';
		
		// Region
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.region == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardRegion").html(phOutput);';
		
		// Menu
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.menu == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardMenu").html(phOutput);';
		
		// Module
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.module == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardModule").html(phOutput);';
		
		// Options
		$s[] = '   		   var phOutput = phFalseEdit;';
		$s[] = '            if (data.option == 1) { phOutput = phTrueEdit;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardOption").html(phOutput);';
		
		$s[] = '   		   if(phTrueAll == 1) {';
		$s[] = '               jQuery("#phResultWizardAll").css("display", "block")';
		$s[] = '            }';
		
		$s[] = '            phRequestActive = null;';
		$s[] = '         } else {';
		// No Displaying of error
		//$s[] = '	           jQuery("#phResultWizardCategory").html(data.error);';
		$s[] = '            phRequestActive = null;';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	/*
	 * Is used for:
	 * - skip wizard in wizard view - modal window must be closed
	 * - start wizard in control panel - modal windows stays unchanged
	 */
	
	public static function renderAjaxDoRequestWizardController($url, $id, $closeModal = true) {
	
		$s 	= array();
		
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery("#'.$id.'").on("click", function(e) {';
		$s[] = '      phDoRequestWizardController("'.$url.'");';
		$s[] = '   })';
		$s[] = '})';
		
		$s[] = 'function phDoRequestWizardController(url) {';
		$s[] = '   var dataPost = {};';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		$s[] = '            phRequestActive = null;';
		$s[] = '         } else {';
		$s[] = '            phRequestActive = null;';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		
		// This function is a part of iframe in modal window
		// We can close whole modal window through global function defined in
		// modalWindowDynamic in administrator\components\com_phocacart\libraries\phocacart\render\renderadminview.php
		if ($closeModal == true) {
			$s[] = '  window.parent.phCloseModal();';
		}
		
		$s[] = '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/* Really not nice way to move the system messages from bootstrap2 (Joomla) to bootstrap3 (Phoca)
	*/
	
	public static function moveSystemMessageFromJoomlaToPhoca() {

		$s = array();
		//$s[] = 'document.getElementById("system-message-container").style.display = "none";';
		$s[] = 'jQuery(document).ready(function() {';
		//$s[] = '   jQuery("#system-message-container").removeClass("j-toggle-main");';
		$s[] = '   jQuery("#system-message-container").css("display", "none");';
		$s[] = '   var phSystemMsg = jQuery("#system-message-container").html();';
		$s[] = '   jQuery("#ph-system-message-container").html(phSystemMsg);';
		$s[] = '});';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));

	}
	

	
	
	/*
	 * Javascript for Pagination TOP
	 * - change Layout Type: Grid, List, Gridlist
	 * - change pagination
	 * - change ordering
	 * with help of AJAX
	 */
	
	public static function renderSubmitPaginationTopForm($urlAjax, $outputDiv, $reloadChosen, $reloadEqualHeights) {
		
		// loading.gif
		$overlay1 = PhocacartRenderJs::renderLoaderDivOverlay($outputDiv);
		$overlay2 = PhocacartRenderJs::renderLoaderFullOverlay();
		
		// ----------------------------------------------------
		// Ajax for top pagination: pagination/ordering/layouttype
		
		$s[] = 'function phSubmitPaginationTopForm(sFormData) {';
		//$s[] = '    	e.preventDefault();';
		
		$s[] = $overlay1['start'];
		
		$s[] = '		var phUrl 	= "'. $urlAjax.'";';
		$s[] = '		phRequest = jQuery.ajax({';
		$s[] = '			type: "POST",';
		$s[] = '			url: phUrl,';
		$s[] = '			async: "false",';
		$s[] = '			cache: "false",';
		$s[] = '			data: sFormData,';
		$s[] = '			dataType:"HTML",';
		$s[] = '			success: function(data){';
		$s[] = '				jQuery("'.$outputDiv.'").html(data);';
		
		if ($reloadChosen) {
			$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
		}
		
		if ($reloadEqualHeights) {
			//$s[] = '	   jQuery(\'.ph-thumbnail-c.grid\').matchHeight();';// FLEXBOX USED
		}
		$s[] = $overlay1['end'];
		$s[] = '			}';
		$s[] = '		})';
		//$s[] = '		e.preventDefault();';

		$s[] = '       return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		// ----------------------------------------------------
		// Change Layout Type
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(".phItemSwitchLayoutType").on(\'click\', function (e) {';
		$s[] = '	    var phDataL = jQuery(this).data("layouttype");';// Get the right button (list, grid, gridlist)
		$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	    var sFormData = sForm.serialize() + "&layouttype=" + phDataL;';
		$s[] = '	    jQuery(".phItemSwitchLayoutType").removeClass("active");';
		$s[] = '	    jQuery(".phItemSwitchLayoutType." + phDataL).addClass("active");';
		$s[] = '		phSubmitPaginationTopForm(sFormData);';	
		$s[] = '	})';
		$s[] = '})';
		
		// ----------------------------------------------------
		// Automatically reload of the pagination/ordering form
		
		$s[] = 'function phSubmitPaginationForm(sForm) {';
		$s[] = '   var formName = jQuery(sForm).attr("name");';
		$s[] = '   if (formName == "phitemstopboxform") {';// AJAX
		$s[] = '       phSubmitPaginationTopForm(jQuery(sForm).serialize());';
		$s[] = '   } else {';
		$s[] = '	   sForm.submit();'; // STANDARD
		$s[] = $overlay2;
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	

	
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>