<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
final class PhocaCartRenderJs
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
		$s[] 	= 'function phDoRequest(url, manager, value) {';
		$s[] 	= 'var phAjaxTop = \'<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> \' + \''. htmlspecialchars($text).'\' + \'</div>\';';
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
		$s[] 	= 'function phAddValue(id, title, titleModal) {';
		$s[] 	= '   document.getElementById(id).value = title;';
		//$s[] 	= '   SqueezeBox.close();';// close
		$s[] 	= '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] 	= '   phDoRequest(\''.$url.'\', \''.$manager.'\', title );';
		$s[] 	= '}';

		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowImage($i, $newRow) {
		$s 	= array();
		$s[] 	= 'var phRowCountImage = '.$i.';';
		
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
		//$s[] 	= 'var phRowImage = 0;';
		//$s[] 	= 'function setPhRowImageId(rowImgId) {';
		//$s[] 	= '   phRowImage = rowImgId;';
		//$s[] 	= '}';
		
		$s[] 	= 'function phAddRowImage() {';
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] 	= '   jQuery(\'#phrowboximage\').append(phNewRow);';
		$s[] 	= '	  phRowCountImage++;';
		
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';
		//$s[] 	= '	  /* Initialize the modal button again - FOR IMAGES */';
		//$s[] 	= '	  /* SqueezeBox.initialize({}); */';
		//$s[] 	= '	  SqueezeBox.assign($$(\''.$poPup.'\'), {';
		//$s[] 	= '	     parse: \'rel\'';
		//$s[] 	= '	  });';
		
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
		
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
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
		
		/* $s[] 	= '
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
		
		$s[] 	= 'var phRowCountOption = '.$j.';';
		
		
		//$s[] 	= 'var phRowImgOption = \'00\';';
		//$s[] 	= 'function setPhRowImgOptionId(attrId, id) {';
		//$s[] 	= '  phRowImgOption = attrId.toString() + id.toString();';
		//$s[] 	= '}';
		
		
		$s[] 	= 'function phAddRowOption(attrid) {';
		$s[] 	= '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] 	= '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] 	= '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] 	= '	  if(phCountRowOption == 0) {';
		$s[] 	= '	     jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewHeader);';
		$s[] 	= '	  }';
		$s[] 	= '   jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewRow);';
			
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
		$s[] 	= '	  phRowCountOption++;';
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';

		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowOption(id, attrid) {';
		//$s[] 	= '	  jQuery(\'#phrowoption\' + id).remove();';
		$s[] 	= '	  jQuery(\'#phOptionBox\' + attrid + id).remove();';
		$s[] 	= '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] 	= '   if (phCountRowOption == 0) {';
		$s[] 	= '      jQuery(\'#phrowboxoptionjs\' + attrid).empty();';// clean header of option added by js
		$s[] 	= '      jQuery(\'#phrowboxoption\' + attrid).empty();';// clean header of option loaded by php
		$s[] 	= '   }';
		//$s[] 	= '	  phRowCountOption--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxoptionjs - header created by javascript
		// #phrowboxoption - header created by php/mysql
		
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
		$s[] 	= '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] 	= '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] 	= ' ';
		$s[] 	= '}';
		$s[] 	= ' ';
		$s[] 	= 'function phRemoveRowSpecification(id) {';
		//$s[] 	= '	  jQuery(\'#phrowSpecification\' + id).remove();';
		$s[] 	= '	  jQuery(\'#phSpecificationBox\' + id).remove();';
		$s[] 	= '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] 	= '   if (phCountRowSpecification == 0) {';
		$s[] 	= '      jQuery(\'#phrowboxspecification\').empty();';
		$s[] 	= '      jQuery(\'#phrowboxspecificationheader\').empty();';
		$s[] 	= '   }';
		$s[] 	= '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	
	public static function renderBillingAndShippingSame() {
		
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
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
	
	public static function renderAjaxChangeProductPriceByOptions($id = 0, $class = '') {
		
		$paramsC 				= JComponentHelper::getParams('com_phocacart') ;
		$attribute_change_price = $paramsC->get( 'attribute_change_price', 1 );
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
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(document).on(\'change\', \'#phItemPriceBoxForm select.ph-item-input-select-attributes\', function(){';
		$s[] = '		var phUrl 	= "'. $urlAjax.'";';
		$s[] = '		var phId 	= '.$id.';';
		$s[] = '		var phClass = "'.$class.'";';
		$s[] = '		var phDataA = jQuery("#phItemPriceBoxForm select").serialize();';
		$s[] = '		var phData 	= \'id=\'+phId+\'&\'+phDataA+\'&\'+\'class=\'+phClass;';
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
		$s[] = '					jQuery("#phItemPriceBox").html(data.item);';
		$s[] = '			   } else {';
		//$s[] = '					// Don\'t change the price box, don't render any error message
		$s[] = '			   }';
		$s[] = '			}';
		$s[] = '		})';	
		
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '	})';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderAjaxAddToCart() {
		
		$paramsC 		= JComponentHelper::getParams('com_phocacart') ;
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
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
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
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
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
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
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
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
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
		
		
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
		//$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		
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
				$s[] = ' 					jQuery("#phContainer").html(data.popup);';
				$s[] = ' 					jQuery("#phQuickViewPopup").modal();';
				$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
	
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
	
	// Singleton - check if loaded - xxx No Singleton, it must be inside each javascript function
	public static function renderLoaderFullOverlay() {
		//static $fullOverlay = 0;
		//if( $fullOverlay == 0) {
			$s 	= array();
			$s[] = 'var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = 'phOverlay.appendTo(document.body);';
			$s[] = 'jQuery("#phOverlay").fadeIn().css("display","block");';
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
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>