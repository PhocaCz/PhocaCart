<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
final class PhocacartRenderJs
{
	private function __construct(){}	
	
	// =======
	// AJAX
	// =======
	
	public static function renderAjaxDoRequest($text) {
	
		
		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phDoRequest */';
		$s[] = 'function phDoRequest(url, manager, value) {';
		$s[] = '   var phAjaxTop = \'<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> \' + \''. strip_tags(addslashes($text)).'\' + \'</div>\';';
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
		$s[] = ' ';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxDoRequestAfterChange($url, $manager = 'product', $value = 'imageCreateThumbs') {
		//$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s 	= array();
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery( \'.'.$value.'\' ).on("change", function() {';
		$s[] = '       phDoRequest(\''.$url.'\', \''.$manager.'\', jQuery(this).val());';
		$s[] = '   })';
		$s[] = '})';
		$s[] = ' ';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderAjaxDoRequestAfterPaste($url, $manager = 'product') {
		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phAddValue */ ';
		$s[] = 'function phAddValue(id, title, titleModal) {';
		$s[] = '   document.getElementById(id).value = title;';
		//$s[] = '   SqueezeBox.close();';// close
		$s[] = '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] = '   phDoRequest(\''.$url.'\', \''.$manager.'\', title );';
		$s[] = '}';
		$s[] = ' ';
		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderAjaxAddToCart() {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$add_cart_method		= $paramsC->get( 'add_cart_method', 0 );
		
		
		// We need to refresh checkout site when AJAX used for removing or adding products to cart
		$app 		= JFactory::getApplication();
		
		$task 		= 'checkout.add';
		$class		= '.phItemCartBox';
		if (PhocacartUtils::isView('checkout')) {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		
		// POS
		if (PhocacartUtils::isView('pos')) {
			$task 				= 'pos.add';
			$add_cart_method	= 1;// POS has always 1 (ajax and no popup)
			$cView 				= 0;
			$class		= '.phPosCartBox';
		}
		
		
		if ($add_cart_method == 0) {
			return false;
		}
		
		if ($add_cart_method == 2) {
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_cart_method > 0) {
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task='.$task.'&format=json&'. JSession::getFormToken().'=1&checkoutview='.(int)$cView;
			
			
			// ::ACTION - Ajax the form
			$s[] = ' ';
			$s[] = '/* Function phDoSubmitFormAddToCart */ ';
			$s[] = 'function phDoSubmitFormAddToCart(sFormData) {';
			$s[] = '   var phUrl 	= "'. $urlAjax.'";';
			$s[] = '   var phCheckoutView = '.(int)$cView.'';
			$s[] = '   ';   
			$s[] = '   phRequest = jQuery.ajax({';
			$s[] = '      type: "POST",';
			$s[] = '      url: phUrl,';
			$s[] = '      async: "false",';
			$s[] = '      cache: "false",';
			$s[] = '      data: sFormData,';
			$s[] = '      dataType:"JSON",';
			$s[] = '      success: function(data){';
			$s[] = '         if (data.status == 1){';
			$s[] = '            jQuery("'.$class.'").html(data.item);';
			$s[] = '            jQuery("'.$class.'Count").html(data.count);';
			$s[] = '            jQuery("'.$class.'Total").html(data.total); ';
			
			
			// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
			if (PhocacartUtils::isView('pos')) {
				$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
				$s[] = '			var phDataInput = phPosCurrentData("main.input");';
				$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
				$s[] = '			jQuery(".ph-pos-message-box").html(data.message);';// clear message box
				$s[] = '			phPosManagePage();';
			}
			
			if ($add_cart_method == 2) {
				$s[] = ' 			jQuery("body").append(jQuery("#phContainer"));';												
				$s[] = '            jQuery("#phContainer").html(data.popup);';
				$s[] = '            jQuery("#phAddToCartPopup").modal();';
			}
			if ($add_cart_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '            if (phCheckoutView == 1) {';
				$s[] = '               setTimeout(function() {location.reload();}, 0001);';
				$s[] = '            }';
			}
			$s[] = '         } else if (data.status == 0){';
			
			if ($add_cart_method != 2) {
				$s[] = '            jQuery(".phItemCartBox").html(data.error);';
			}
			if ($add_cart_method == 2) {
				$s[] = ' 			jQuery("body").append(jQuery("#phContainer"));';												
				$s[] = '            jQuery("#phContainer").html(data.popup);';
				$s[] = '            jQuery("#phAddToCartPopup").modal();';
			}
			if ($add_cart_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '            if (phCheckoutView == 1) {';
				$s[] = '               setTimeout(function() {location.reload();}, 0001);';
				$s[] = '            }';
			}
			

			// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
			if (PhocacartUtils::isView('pos')) {
				//$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
				//$s[] = '			var phDataInput = phPosCurrentData("main.input");';
				//$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
				$s[] = '			jQuery(".ph-pos-message-box").html(data.error);';// clear message box
				$s[] = '			phPosManagePage();';
			}
			
			
			
			
			$s[] = '         } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '         }';
			$s[] = '      },';
		//	$s[] = '      error: function(data){}';
			$s[] = '   })';
			$s[] = '   return false;';	
			$s[] = '}';
			
			$s[] = ' ';
			
			// :: EVENT (CLICK) Category/Items View (icon/button - ajax/standard)
		/*	$s[] = 'function phEventClickFormAddToCart(phFormId) {';
			$s[] = '   var phForm = \'#\' + phFormId;';
			//$s[] = '   var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
			$s[] = '   var sFormData = jQuery(phForm).serialize();';
			$s[] = '   phDoSubmitFormAddToCart(sFormData);';
			$s[] = '}';*/
			
			// Set it onclick as it is used in even not ajax submitting
		/*	$s[] = 'function phEventClickFormAddToCart(phFormId) {';
			$s[] = '   var phForm = \'#\' + phFormId;';
			$s[] = '   jQuery(\'phFormId\').find(\':submit\').click();"';
			$s[] = '   return false;';
			$s[] = '}';*/
			
			$s[] = ' ';
			
			
			
			// :: EVENT (SUBMIT) Item View
			$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCartBoxForm").on(\'submit\', function (e) {';// Not working when form is added by ajax
			$s[] = '	jQuery(document).on("submit", "form.phItemCartBoxForm", function (e) {';// Works with forms added by ajax
			$s[] = '		e.preventDefault();';
			$s[] = '	    var sFormData = jQuery(this).serialize();';
			$s[] = '	    phDoSubmitFormAddToCart(sFormData);';
			$s[] = '    })';
			$s[] = '})';
			$s[] = ' ';
			
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxUpdateCart() {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		
		// We need to refresh checkout site when AJAX used for removing or adding products to cart
		
	
		$task 		= 'checkout.update';
		$class		= '.phCheckoutCartBox';
		if (PhocacartUtils::isView('checkout')) {
			$cView = 1;
		} else {
			$cView = 0;
		}
		
		// POS
		if (PhocacartUtils::isView('pos')) {
			$task 				= 'pos.update';
			$add_cart_method	= 1;// POS has always 1 (ajax and no popup)
			$cView 				= 0;
			$class		= '.phPosCartBox';
		}
		

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task='.$task.'&format=json&'. JSession::getFormToken().'=1&checkoutview='.(int)$cView;
		
		
		// ::ACTION - Ajax the form
		$s[] = ' ';
		$s[] = '/* Function phDoSubmitFormUpdateCart */ ';
		$s[] = 'function phDoSubmitFormUpdateCart(sFormData) {';
		$s[] = '   var phUrl 	= "'. $urlAjax.'";';
		$s[] = '   var phCheckoutView = '.(int)$cView.'';
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "POST",';
		$s[] = '      url: phUrl,';
		$s[] = '      async: "false",';
		$s[] = '      cache: "false",';
		$s[] = '      data: sFormData,';
		$s[] = '      dataType:"JSON",';
		$s[] = '      success: function(data){';
		$s[] = '         if (data.status == 1){';
		$s[] = '            jQuery("'.$class.'").html(data.item);';
		$s[] = '            jQuery("'.$class.'Count").html(data.count);';
		$s[] = '            jQuery("'.$class.'Total").html(data.total); ';

		
		// POS update message box (clean) and input box (when product added or changed - shipping and payment method must be cleaned)
		if (PhocacartUtils::isView('pos')) {
			$s[] = '    		var phUrlPos 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
			$s[] = '			var phDataInput = phPosCurrentData("main.input");';
			$s[] = '			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);';// refresh input box
			$s[] = '			jQuery(".ph-pos-message-box").html(data.message);';// clear message box
			$s[] = '			phPosManagePage();';
		}
		
			// If no popup is displayed we can relaod the page when we are in comparison page
			// If popup, this will be done when clicking continue or comparison list
		/*	$s[] = '            if (phCheckoutView == 1) {';
			$s[] = '               setTimeout(function() {location.reload();}, 0001);';
			$s[] = '            }';*/
		
		$s[] = '         } else if (data.status == 0){';
		

		
			// If no popup is displayed we can relaod the page when we are in comparison page
			// If popup, this will be done when clicking continue or comparison list
			/*$s[] = '            if (phCheckoutView == 1) {';
			$s[] = '               setTimeout(function() {location.reload();}, 0001);';
			$s[] = '            }';*/
		
		$s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';												  
		$s[] = ' 				jQuery("#phContainer").html(data.popup);';
		$s[] = ' 				jQuery("#phAddToCartPopup").modal();';
		
		
		
		$s[] = '         } else {';
		//$s[] = '					// Don\'t change the price box';
		$s[] = '         }';
		$s[] = '      },';
	//	$s[] = '      error: function(data){}';
		$s[] = '   })';
		$s[] = '   return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		
		
		// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(document).on("click", "form.phItemCartUpdateBoxForm button", function (e) {';
		$s[] = '		e.preventDefault();';
		$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '		var phAction= jQuery(this).val()';	
		$s[] = '	    var sFormData = sForm.serialize() + "&action=" + phAction;';
		$s[] = '	    phDoSubmitFormUpdateCart(sFormData);';	
		$s[] = '	})';
		$s[] = '})';
		$s[] = ' ';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	
	}
	
	
	public static function renderAjaxAddToCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
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
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.add&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = ' ';
			$s[] = '/* Function phItemCompareBoxFormAjax */ ';
			$s[] = 'function phItemCompareBoxFormAjax(phItemId) {';
			$s[] = '	var phUrl 	= "'. $urlAjax.'";';
			$s[] = '	var phItem = \'#\' + phItemId;';
			$s[] = '	var phComparisonView = '.(int)$cView.'';
			$s[] = '	var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '	phRequest = jQuery.ajax({';
			$s[] = '		type: "POST",';
			$s[] = '		url: phUrl,';
			$s[] = '		async: "false",';
			$s[] = '		cache: "false",';
			$s[] = '		data: phData,';
			$s[] = '		dataType:"JSON",';
			$s[] = '		success: function(data){';
			$s[] = '			if (data.status == 1){';
			$s[] = '				jQuery(".phItemCompareBox").html(data.item);';
			$s[] = '				jQuery(".phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				$s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';												  
				$s[] = ' 				jQuery("#phContainer").html(data.popup);';
				$s[] = ' 				jQuery("#phAddToComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '					if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '						setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			  			}';
			}
			$s[] = '		   } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '		   }';
			$s[] = '		}';
			$s[] = '	})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '}';
			//$s[] = '})';
			$s[] = ' ';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromCompare() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
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
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_compare_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=comparison.remove&format=json&'. JSession::getFormToken().'=1&comparisonview='.(int)$cView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
			$s[] = ' ';
			$s[] = '/* Function phItemRemoveCompareFormAjax */ ';
			$s[] = 'function phItemRemoveCompareFormAjax(phItemId) {';
			$s[] = '	var phUrl 	= "'. $urlAjax.'";';
			$s[] = '	var phItem = \'#\' + phItemId;';
			$s[] = '	var phComparisonView = '.(int)$cView.'';
			$s[] = '	var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '	phRequest = jQuery.ajax({';
			$s[] = '		type: "POST",';
			$s[] = '		url: phUrl,';
			$s[] = '		async: "false",';
			$s[] = '		cache: "false",';
			$s[] = '		data: phData,';
			$s[] = '		dataType:"JSON",';
			$s[] = '		success: function(data){';
			$s[] = '			if (data.status == 1){';
			$s[] = '				jQuery(".phItemCompareBox").html(data.item);';
			$s[] = '				jQuery(".phItemCompareBoxCount").html(data.count);';
			if ($add_compare_method == 2) {
				// Display the popup
				$s[] = ' 				jQuery("#phContainerModuleCompare").html(data.popup);';
				$s[] = ' 				jQuery("#phRemoveFromComparePopup").modal();';
			}
			if ($add_compare_method == 1) {
				// If no popup is displayed we can relaod the page when we are in comparison page
				// If popup, this will be done when clicking continue or comparison list
				$s[] = '					if (phComparisonView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '						setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			  			}';
			}	
			$s[] = '			  } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			  }';
			$s[] = '		}';
			$s[] = '	})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '}';
			//$s[] = '})';
			$s[] = ' ';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	

	public static function renderAjaxAddToWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
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
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.add&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = ' ';
			$s[] = '/* Function phItemWishListBoxFormAjax */ ';
			$s[] = 'function phItemWishListBoxFormAjax(phItemId) {';
			$s[] = '	var phUrl 	= "'. $urlAjax.'";';
			$s[] = '	var phItem = \'#\' + phItemId;';
			$s[] = '	var phWishListView = '.(int)$wView.'';
			$s[] = '	var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '	phRequest = jQuery.ajax({';
			$s[] = '		type: "POST",';
			$s[] = '		url: phUrl,';
			$s[] = '		async: "false",';
			$s[] = '		cache: "false",';
			$s[] = '		data: phData,';
			$s[] = '		dataType:"JSON",';
			$s[] = '		success: function(data){';
			$s[] = '			if (data.status == 1){';
			$s[] = '				jQuery(".phItemWishListBox").html(data.item);';
			$s[] = '				jQuery(".phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				$s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';												  
				$s[] = ' 				jQuery("#phContainer").html(data.popup);';
				$s[] = ' 				jQuery("#phAddToWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '					if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '						setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			  			}';
			}
			$s[] = '			  } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '			  }';
			$s[] = '		}';
			$s[] = '	})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '}';
			//$s[] = '})';
			$s[] = ' ';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxRemoveFromWishList() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
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
			JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
		}
		
		if ($add_wishlist_method > 0) {	
		
			$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=wishlist.remove&format=json&'. JSession::getFormToken().'=1&wishlistview='.(int)$wView;
			//$s[] = 'jQuery(document).ready(function(){';
			//$s[] = '	jQuery(".phItemWishListBoxForm").on(\'submit\', function (e) {';
			$s[] = ' ';
			$s[] = '/* Function phItemRemoveWishListFormAjax */ ';
			$s[] = 'function phItemRemoveWishListFormAjax(phItemId) {';
			$s[] = '	var phUrl 	= "'. $urlAjax.'";';
			$s[] = '	var phItem = \'#\' + phItemId;';
			$s[] = '	var phWishListView = '.(int)$wView.'';
			$s[] = '	var phData = jQuery(phItem).serialize();';
			$s[] = ' ';		
			$s[] = '	phRequest = jQuery.ajax({';
			$s[] = '		type: "POST",';
			$s[] = '		url: phUrl,';
			$s[] = '		async: "false",';
			$s[] = '		cache: "false",';
			$s[] = '		data: phData,';
			$s[] = '		dataType:"JSON",';
			$s[] = '		success: function(data){';
			$s[] = '			if (data.status == 1){';
			$s[] = '				jQuery(".phItemWishListBox").html(data.item);';
			$s[] = '				jQuery(".phItemWishListBoxCount").html(data.count);';
			if ($add_wishlist_method == 2) {
				// Display the popup
				$s[] = ' 				jQuery("#phContainerModuleWishList").html(data.popup);';
				$s[] = ' 				jQuery("#phRemoveFromWishListPopup").modal();';
			}
			if ($add_wishlist_method == 1) {
				// If no popup is displayed we can relaod the page when we are in wishlist page
				// If popup, this will be done when clicking continue or wishlist list
				$s[] = '					if (phWishListView == 1) {';
				$s[] = self::renderOverlay();
				$s[] = '						setTimeout(function() {location.reload();}, 0001);';
				$s[] = '			  			}';
			}	
			$s[] = '			  } else {';
			//$s[] = '					// Don\'t change the price box';
			$s[] = '		   }';
			$s[] = '		}';
			$s[] = '	})';
			//$s[] = '		e.preventDefault();';
			//$s[] = '       return false;';	
			$s[] = '}';
			//$s[] = '})';
			$s[] = ' ';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	}
	
	public static function renderAjaxQuickViewBox() {
		
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$dynamic_change_price 	= $paramsC->get( 'dynamic_change_price', 1 );
		$load_chosen 			= $paramsC->get( 'load_chosen', 1 );
		$media 					= new PhocacartRenderMedia();
		self::renderPhocaAttribute();// needed because of phChangeAttributeType()
		
		
		// We need to refresh comparison site when AJAX used for removing or adding products to comparison list
		$app 		= JFactory::getApplication();
		$view 		= $app->input->get('view', '');
		$option 	= $app->input->get('option', '');
		/*if ($option == 'com_phocacart' && $view == 'comparison') {
			$cView = 1;
		} else {
			$cView = 0;
		}*/
		
		
		JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bs_modal_transition.css');
	
		
	
		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&view=item&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		//$s[] = 'jQuery(document).ready(function(){';
		//$s[] = '	jQuery(".phItemCompareBoxForm").on(\'submit\', function (e) {';
		$s[] = ' ';
		$s[] = '/* Function phItemQuickViewBoxFormAjax */ ';
		$s[] = 'function phItemQuickViewBoxFormAjax(phItemId) {';
		$s[] = '	var phUrl 	= "'. $urlAjax.'";';
		$s[] = '	var phItem = \'#\' + phItemId;';
		//$s[] = '	var phComparisonView = '.(int)$cView.'';
		$s[] = '	var phData = jQuery(phItem).serialize();';
		$s[] = ' ';		
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrl,';
		$s[] = '		async: "false",';
		$s[] = '		cache: "false",';
		$s[] = '		data: phData,';
		$s[] = '		dataType:"JSON",';
		$s[] = '		success: function(data){';
		$s[] = '			if (data.status == 1){';
		//$s[] = '					jQuery("#phItemCompareBox").html(data.item);';
		
		
		
		//$s[] = ' 				jQuery("#phQuickViewPopupBody").html(data.popup);'; added in ajax
		/////$s[] = ' 			jQuery("#phContainer").html(data.popup); ';
		$s[] = ' 				jQuery(".phjItemQuick.phjProductAttribute").remove(); ';// Clear attributes from dom when ajax reload
		$s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';
		$s[] = ' 				jQuery("#phContainer").html(data.popup); ';
		
		/////$s[] = ' 			jQuery("#phQuickViewPopup").modal();';
		$s[] = ' 				jQuery("body").append(jQuery("#phQuickViewPopup"));';
		$s[] = ' 				jQuery("#phQuickViewPopup").modal();';
		if ($load_chosen == 1) {
			
			// TO DO 
			// Chosen cannot be dynamically recreated in case
			// we want to add support for mobiles and have support for html required forms (browser checks for html required forms)
			// Now choosen is disables on mobile devices so when we reload choosen for standard devices
			// we lost the select boxes on mobiles
			//$s[] = '	  				jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
			// This seems to work
			//$s[] = '	 jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
			$s[] = '	 jQuery(\'select\').chosen(\'destroy\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
		}
		
		if ($dynamic_change_price == 1) {
			//$s[] = '					phAjaxChangePrice();';
		}
		
		$s[] = 'phChangeAttributeType(\'ItemQuick\');';// Recreate the select attribute (color, image) after AJAX
		
		$s[] = '		'. $media->loadTouchSpin('quantity');// Touch spin for input
			
		$s[] = '			  } else {';
		//$s[] = '					// Don\'t change the price box';
		$s[] = '			  }';
		$s[] = '		}';
		$s[] = '	})';
		//$s[] = '		e.preventDefault();';
		//$s[] = '       return false;';	
		$s[] = '}';
		//$s[] = '})';
		$s[] = ' ';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	/*
	 * Change Price
	 * select box (standard, image, color)
	 * check box
	 */

	public static function renderAjaxChangeProductPriceByOptions($id = 0, $typeView = '', $class = '') {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$dynamic_change_price = $paramsC->get( 'dynamic_change_price', 0 );
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		
		if ($dynamic_change_price == 0) {
			return false;
		}
		//if ($id == 0) {
		//	$idJs = 'var phId = phProductId;'. "\n";
		//	$idJs .= 'var phIdItem = "#phItemPriceBox'.$typeView.'" + phProductId;';
	/*	} else {
			$idJs = 'var phId = '.(int)$id.';'. "\n";
			$idJs .= 'var phIdItem = "#phItemPriceBox'.(int)$id.'";';
		}*/

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.changepricebox&format=json&'. JSession::getFormToken().'=1';
		
		
		$s[] = ' ';
		$s[] = '/* Function phAjaxChangePrice */ ';
		$s[] = 'function phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2){';
		$s[] = '	var phUrl 		= "'. $urlAjax.'";';
		$s[] = '	var phId 		= phProductId;'. "\n";
		$s[] = '	var phIdItem 	= "#phItemPriceBox'.$typeView.'" + phProductId;';
		$s[] = '	var phClass 	= "'.$class.'";';
		$s[] = '	var phTypeView 	= "'.$typeView.'";';
		
		$s[] = '	var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass+\'&\'+\'typeview=\'+phTypeView;';
		$s[] = '	jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrl,';
		$s[] = '		async: "false",';
		$s[] = '		cache: "false",';
		$s[] = '		data: phData,';
		$s[] = '		dataType:"JSON",';
		$s[] = '		success: function(data){';
		$s[] = '			if (data.status == 1){';
		$s[] = '				jQuery(phIdItem).html(data.item);';
		$s[] = '		   } else {';
		//$s[] = '				// Don\'t change the price box, don't render any error message
		$s[] = '			  }';
		$s[] = '		}';
		$s[] = '	})';
		$s[] = '}';
		$s[] = ' ';
		
		$s[] = 'jQuery(document).ready(function(){';
		
		
		// Select Box
		$s[] = '	jQuery(document).on(\'change\', \'select.phj'.$typeView.'.phjProductAttribute\', function(){';	
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2);';
	
		$s[] = '	})';
		
		// Checkbox
		// Unfortunately, we cannot run event:
		// 1. CHANGE - because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
		// 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
		// So we run click on div box over the checkbox which works and don't run ajax 3x
		//$s[] = '	jQuery(document).on(\'change\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute :checkbox\', function(){';
		//$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';		
		$s[] = '	jQuery(document).on(\'click\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute\', function(e){';
		
		// Prevent from twice running
		$s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';
		
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangePrice'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
		
		// Change the price on time view when site is initialized
		// Because some parameters can be selected as default
		// Automatically start only in item view, not in category or another view
		/*if ($option == 'com_phocacart' && $view == 'item') {
			//$s[] = '		var phProductId = jQuery(\'.phjItemAttribute\').data(\'product-id\')';
			$s[] = '		var phDataA1 = jQuery("select.phjItemAttribute").serialize();';
			$s[] = '		var phDataA2 = jQuery(".ph-checkbox-attribute.phjItemAttribute :checkbox").serialize();';
			$s[] = '		var phpDataA = phDataA1 +\'&\'+ phDataA2;';
			$s[] = '		phAjaxChangePrice'.$typeView.'('.(int)$id.');';
		}*/
		
		
		$s[] = '})';
		$s[] = ' ';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	/*
	 * Change Stock
	 * select box (standard, image, color)
	 * check box
	 */

	public static function renderAjaxChangeProductStockByOptions($id = 0, $typeView = '', $class = '') {
		
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$dynamic_change_stock 	= $paramsC->get( 'dynamic_change_stock', 0 );
		$hide_add_to_cart_stock = $paramsC->get( 'hide_add_to_cart_stock', 0 );
		
		$app					= JFactory::getApplication();
		$option					= $app->input->get( 'option', '', 'string' );
		$view					= $app->input->get( 'view', '', 'string' );
		
		
		if ($dynamic_change_stock == 0) {
			return false;
		}

		$urlAjax = JURI::base(true).'/index.php?option=com_phocacart&task=checkout.changestockbox&format=json&'. JSession::getFormToken().'=1';
		
		$s[] = ' ';
		$s[] = '/* Function phAjaxChangeStock */ ';
		$s[] = 'function phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2){';
		$s[] = '	var phUrl 					= "'. $urlAjax.'";';
		$s[] = '	var phId 					= phProductId;'. "\n";
		$s[] = '	var phIdItem 				= "#phItemStockBox'.$typeView.'" + phProductId;';
		$s[] = '	var phProductAddToCart 		= ".phProductAddToCart'.$typeView.'" + phProductId;';// display or hide add to cart button
		$s[] = '	var phProductAddToCartIcon 	= ".phProductAddToCartIcon'.$typeView.'" + phProductId;';// display or hide add to cart icon
		$s[] = '	var phClass 				= "'.$class.'";';
		$s[] = '	var phTypeView 				= "'.$typeView.'";';
		
		$s[] = '	var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass+\'&\'+\'typeview=\'+phTypeView;';
		$s[] = '	jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrl,';
		$s[] = '		async: "false",';
		$s[] = '		cache: "false",';
		$s[] = '		data: phData,';
		$s[] = '		dataType:"JSON",';
		$s[] = '		success: function(data){';
		$s[] = '			if (data.status == 1){';
		
		if ($hide_add_to_cart_stock == 1) {
			$s[] = '				if (data.stock < 1) {';
			//$s[] = '					jQuery(phProductAddToCart).hide();';
			$s[] = '					jQuery(phProductAddToCart).css(\'visibility\', \'hidden\');';
			$s[] = '					jQuery(phProductAddToCartIcon).css(\'display\', \'none\');';
			
			$s[] = '				} else {';
			//$s[] = '					jQuery(phProductAddToCart).show();';
			$s[] = '					jQuery(phProductAddToCart).css(\'visibility\', \'visible\');';
			$s[] = '					jQuery(phProductAddToCartIcon).css(\'display\', \'block\');';
			$s[] = '				}';
		}
		
		$s[] = '				jQuery(phIdItem).html(data.item);';
		$s[] = '			  } else {';
		//$s[] = '					// Don\'t change the price box, don't render any error message
		$s[] = '			  }';
		$s[] = '		}';
		$s[] = '	})';
		$s[] = '}';
		$s[] = ' ';
		
		$s[] = 'jQuery(document).ready(function(){';
		
		
		// Select Box
		$s[] = '	jQuery(document).on(\'change\', \'select.phj'.$typeView.'.phjProductAttribute\', function(){';	
		//$s[] = '		jQuery(this).off("change");';
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
			
		$s[] = '	jQuery(document).on(\'click\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute\', function(e){';
		
		// Prevent from twice running
		$s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';
		
		$s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
		$s[] = '		var phProductGroup = \'.phjAddToCartV'.$typeView.'P\' + phProductId;';
		// All Selects
		$s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';
		// All Checkboxes
		$s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';
		
		$s[] = '		phAjaxChangeStock'.$typeView.'(phProductId, phDataA1, phDataA2);';
		$s[] = '	})';
		
		$s[] = '})';
		$s[] = ' ';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	
	/*
	 * Javascript for Pagination TOP
	 * - change Layout Type: Grid, List, Gridlist
	 * - change pagination
	 * - change ordering
	 * with help of AJAX
	 * 
	 * This function is used to reload POS main box by ajax
	 */
	
	public static function renderSubmitPaginationTopForm($urlAjax, $outputDiv) {
		
		$app						= JFactory::getApplication();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$equal_height				= $paramsC ->get( 'equal_height', 0 );// reload equal height
		$load_chosen				= $paramsC ->get( 'load_chosen', 1 );// reload choosen
		$ajax_pagination_category	= $paramsC ->get( 'ajax_pagination_category', 0 );
		
		// loading.gif
		$overlay1 = PhocacartRenderJs::renderLoaderDivOverlay($outputDiv);
		$overlay2 = PhocacartRenderJs::renderLoaderFullOverlay();
		
	
		
		self::renderPhocaAttribute();// needed because of phChangeAttributeType()
		
		// ::ACTION Ajax for top pagination: pagination/ordering/layouttype
		$s[] = ' ';
		$s[] = '/* Function phDoSubmitFormPaginationTop */ ';
		$s[] = 'function phDoSubmitFormPaginationTop(sFormData, phUrlJs) {';
		//$s[] = '    	e.preventDefault();';

		$s[] = $overlay1['start'];
		
		//if (PhocacartUtils::isView('pos')) {
		//	$s[] = '    var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		//} else {
			$s[] = '	var phUrl 	= "'. $urlAjax.'";';
		//}
		$s[] = '	phUrl 		= typeof phUrlJs !== "undefined" ? phUrlJs : phUrl;';
		$s[] = '	phRequest = jQuery.ajax({';
		$s[] = '		type: "POST",';
		$s[] = '		url: phUrl,';
		//$s[] = '		async: false,';
		$s[] = '		async: true,';
		$s[] = '		cache: "false",';
		$s[] = '		data: sFormData,';
		$s[] = '		dataType:"HTML",';
		$s[] = '		success: function(data){';
		$s[] = '			jQuery("'.$outputDiv.'").html(data);';
		
		if (PhocacartUtils::isView('pos')) {
			$s[] = '			phPosManagePage()';
		}
		
		if ($load_chosen) {
			//$s[] = '			jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
			$s[] = '			jQuery(\'select\').chosen(\'destroy\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';
		
		}
		
		if ($equal_height) {
			//$s[] = '			jQuery(\'.ph-thumbnail-c.grid\').matchHeight();';// FLEXBOX USED
		}
		
		$s[] = '			phChangeAttributeType();';// Recreate the select attribute (color, image) after AJAX
		$s[] = '			'.$overlay1['end'];
		$s[] = '		}';
		$s[] = '	})';
		//$s[] = '		e.preventDefault();';

		$s[] = '       return false;';	
		$s[] = '}';
		
		$s[] = ' ';
		
		// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(".phItemSwitchLayoutType").on(\'click\', function (e) {';
		$s[] = '	    var phDataL = jQuery(this).data("layouttype");';// Get the right button (list, grid, gridlist)
		$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	    var sFormData = sForm.serialize() + "&layouttype=" + phDataL;';
		$s[] = '	    jQuery(".phItemSwitchLayoutType").removeClass("active");';
		$s[] = '	    jQuery(".phItemSwitchLayoutType." + phDataL).addClass("active");';
		$s[] = '        var phUrl = window.location.href;';
		$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';	
		$s[] = '	})';
		$s[] = '})';
		$s[] = ' ';
		
		
		// ::EVENT (CLICK) Pagination - Clicking on Start Prev 1 2 3 Next End
		if ($ajax_pagination_category == 1 || PhocacartUtils::isView('pos')) {
			$s[] = 'jQuery(document).ready(function(){';
			$s[] = '	jQuery(document).on(\'click\', ".phPaginationBox .pagination li a", function (e) {';
			$s[] = '		var phUrl = jQuery(this).attr("href");';
			$s[] = '	    var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
			$s[] = '	    var sFormData = sForm.serialize();';
			$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';
			
			// Don't set format for url bar (e.g. pagination uses ajax with raw - such cannot be set in url bar)
			// we use ajax and pagination for different views inside one view (customers, products, orders) so we cannot set this parameter in url, because of ajax
			if (PhocacartUtils::isView('pos')) {
				$s[] = '		phUrl = phRemoveUrlParameter("format", phUrl);';
				$s[] = '		phUrl = phRemoveUrlParameter("start", phUrl);';
			}
			
			$s[] = '		window.history.pushState("", "", phUrl);';// change url bar
			$s[] = '		e.preventDefault();';
			$s[] = '	})';
			$s[] = '})';
			$s[] = ' ';
		}
		
		// ::EVENT (CHANGE) Automatically reload of the pagination/ordering form Clicking on Ordering and Display Num
		$s[] = ' ';
		$s[] = '/* Function phEventChangeFormPagination */ ';
		$s[] = 'function phEventChangeFormPagination(sForm, sItem) {';
		$s[] = '   var phA = 1;';// Full Overlay Yes
		
		
		// If pagination changes on top (ordering or display num then the bottom pagination is reloaded by ajax
		// But if bottom pagination changes, the top pagination is not reloaded
		// so we need to copy the bottom values from ordering and display num selectbox
		// and set it to top
		// top id: itemorderingtop, limittop
		// bottom id: itemordering, limit
		$s[] = '   var phSelectBoxVal  	= jQuery(sItem).val();';
		$s[] = '   var phSelectBoxId 	= "#" + jQuery(sItem).attr("id") + "top";';
		$s[] = '   jQuery(phSelectBoxId).val(phSelectBoxVal);';
		
		
		$s[] = '   var formName = jQuery(sForm).attr("name");';
		
		if ($ajax_pagination_category == 1 || PhocacartUtils::isView('pos')) {
			// Everything is AJAX - pagination top even pagination bottom
			$s[] = '   var phUrl = window.location.href;';
			$s[] = '   phDoSubmitFormPaginationTop(jQuery(sForm).serialize(), phUrl);';
		} else {
			// Only top pagination is ajax, bottom pagination is not ajax start prev 1 2 3 next end
			$s[] = '   if (formName == "phitemstopboxform") {';// AJAX - Top pagination always ajax
			$s[] = '       var phUrl = window.location.href;';
			$s[] = '       phDoSubmitFormPaginationTop(jQuery(sForm).serialize(), phUrl);';
			$s[] = '   } else {';
			$s[] = '	   sForm.submit();'; // STANDARD
			$s[] = $overlay2;
			$s[] = '   }';
		}

		$s[] = '}';
		$s[] = ' ';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	
	

	
	
	
	
	
	/*
	 * JS equivalent to PhocacartPrice::getPriceFormat();
	 */
	
	public static function getPriceFormatJavascript($price_decimals, $price_dec_symbol, $price_thousands_sep, $price_currency_symbol, $price_prefix, $price_suffix, $price_format) {
		

		JFactory::getDocument()->addScript(JURI::root(true).'/media/com_phocacart/js/number_format.js');
	
		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phGetPriceFormat*/ ';
		$s[] = 'function phGetPriceFormat($price) {';
		$s[] = '	var $negative = 0;';
		$s[] = ' 	if ($price < 0) {';
		$s[] = ' 		$negative = 1;';
		$s[] = '	}';

		$s[] = '	if ($negative == 1 ) {';
		$s[] = ' 		$price = Math.abs($price);';
		$s[] = ' 	}';

		$s[] = ' 	$price = number_format($price, "'.$price_decimals.'", "'.$price_dec_symbol.'", "'.$price_thousands_sep.'");';
		
		switch($price_format) {
			case 1:
				$s[] = '	$price = $price + "'.$price_currency_symbol.'";';
			break;
			
			case 2:
				$s[] = '	$price = "'.$price_currency_symbol.'" + $price;';
			break;
			
			case 3:
				$s[] = '	$price = "'.$price_currency_symbol.'" + " " + $price;';
			break;
			
			case 0:
			default:
				$s[] = '	$price = $price + " " + "'.$price_currency_symbol.'";';
			break;
		}
		
		$s[] = '	if ($negative == 1) {';
		$s[] = '		return "- " + "'.$price_prefix.'" + $price + "'.$price_suffix.'";';
		$s[] = '	} else {';
		$s[] = '		return "'.$price_prefix.'" + $price + "'.$price_suffix.'";';
		$s[] = '	}';
		$s[] = '}';
		$s[] = ' ';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	public static function renderFilterRange($min, $max, $priceFrom, $priceTo) {
		
		
		$s 	= array();
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery( "#phPriceFilterRange" ).slider({';
		$s[] = '      range: true,';
		$s[] = '      min: '.$min.',';
		$s[] = '      max: '.$max.',';
		$s[] = '  	  values: ['.$priceFrom.', '.$priceTo.'],';
		$s[] = '      slide: function( event, ui ) {';
		$s[] = '         jQuery("#phPriceFromTopricefrom").val(ui.values[0]);';
		$s[] = '	     jQuery("#phPriceFromTopriceto").val(ui.values[1]);';
		$s[] = '         jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat(ui.values[0]) + " - " + phGetPriceFormat(ui.values[1]));';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = ' ';
		$s[] = '   jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat('.$priceFrom.') + " - " + phGetPriceFormat('.$priceTo.'));';
		$s[] = ' ';
		
		$s[] = '	jQuery("#phPriceFromTopricefrom").on("change", function (e) {';
		$s[] = '		var from = jQuery("#phPriceFromTopricefrom").val();';
		$s[] = '		var to = jQuery("#phPriceFromTopriceto").val();';
		$s[] = '		if (to == \'\') { to = '.$max.';}';
		$s[] = '		if (from == \'\') { from = '.$min.';}';
		$s[] = '		if (Number(to) < Number(from)) {to = from;jQuery("#phPriceFromTopriceto").val(to);}';
		$s[] = '		jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});';
		$s[] = '         jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));';
		$s[] = '	})';
		
		$s[] = '	jQuery("#phPriceFromTopriceto").on("change", function (e) {';
		$s[] = '		var from = jQuery("#phPriceFromTopricefrom").val();';
		$s[] = '		var to = jQuery("#phPriceFromTopriceto").val(); ';
		$s[] = '		if (to == \'\') { to = '.$max.';}';
		$s[] = '		if (from == \'\') { from = '.$min.';}';
		$s[] = '		if (Number(to) < Number(from)) {to = from;jQuery("#phPriceFromTopriceto").val(to);}';
		$s[] = '		jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});';
		$s[] = '         jQuery("#phPriceFilterPrice").html("'.JText::_('COM_PHOCACART_PRICE').': " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));';
		$s[] = '	})';
		
		$s[] = '});';
		$s[] = ' ';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
	}
	
	
	
	
	
	// ========
	// HELPERS
	// ========
	
		public static function renderBillingAndShippingSame() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$load_chosen 		= $paramsC->get( 'load_chosen', 1 );
		

		// BILLING AND SHIPPING THE SAME
		// If checkbox will be enabled (Shipping and Billing address is the same) - remove the required protection of input fields
		$s 	= array();

		$s[] = 'jQuery(document).ready(function(){';

		//$s[] = '   phBgInputCh  = jQuery("#phShippingAddress .chosen-single").css("background");';
		//$s[] = '   phBgInputI	= jQuery(".phShippingFormFields").css("background");';
		$s[] = '   phDisableRequirement();';
	  
		$s[] = '   jQuery("#phCheckoutBillingSameAsShipping").on(\'click\', function() {';
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
	
	public static function renderAjaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> '. strip_tags(addslashes($text)) . '</div>';
		}
		$o .= '</div>';
		return $o;
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
	// Singleton - check if loaded - No Singleton, it must be inside each javascript function
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
	
	public static function renderOverlay(){
		
		$s	 = array();
		$s[] = '		var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '		phOverlay.appendTo(document.body);';
		$s[] = '		jQuery("#phOverlay").fadeIn().css("display","block");';
		return implode("\n", $s);
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
		JHtml::stylesheet( 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js');
		
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({';
		$s[] = '  social_tools: 0';		
		$s[] = '  });';
		$s[] = '})';

		$document->addScriptDeclaration(implode("\n", $s));
	}
	


	
	public static function renderPhocaAttribute() {
		$document	= JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute.js');
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
	
	/* OBSOLETE
	 * Swap large images by attributes
	 */
/*	public static function renderPhSwapImageInitialize($formId, $dynamicChangeImage = 0, $ajax = 0, $imgClass = 'ph-item-image-full-box') {
		/*
		if ($dynamicChangeImage == 1) {
			$s = array();
			$s[] = 'jQuery(document).ready(function() {';
			$s[] = '	var phSIO1'.(int)$formId.' = new phSwapImage;';
		//	$s[] = '	phSIO1'.(int)$formId.'.Init(\'.ph-item-image-full-box\', \'#phItemPriceBoxForm\', \'.ph-item-input-set-attributes\', 0);';
		$s[] = '	phSIO1'.(int)$formId.'.Init(\'.'.$imgClass.'\', \'#'.$formId.'\', \'.ph-item-input-set-attributes\', 0);';
			$s[] = '	phSIO1'.(int)$formId.'.Display();';
			$s[] = '});';
			if ($ajax == 1) {
				return '<script type="text/javascript">'.implode("\n", $s).'</script>';
			} else {
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
			}
			
		}*//*
	}*/
	
	/* OBSOLETE
	 * Type Color Select Box and Image Select Box - displaying images or colors instead of select box
	 */
	/* 
	public static function renderPhAttributeSelectBoxInitialize($id, $type, $typeView) {
	
		
		return;

		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '	var phAOV'.$typeView.'I'.(int)$id.' = new phAttribute;';
		$s[] = '	phAOV'.$typeView.'I'.(int)$id.'.Init('.(int)$id.', '.(int)$type.', \''.$typeView.'\');';
		$s[] = '	phAOV'.$typeView.'I'.(int)$id.'.Display();';
		$s[] = '});';
		
		if ($typeView == 'ItemQuick') {
			return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		} else {
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
	
	}
	*/
	
	/*
	 * Checkbox color and image - set by bootstrap button - active class
	 */ 
	 
	 
	/*
	* This jQuery function replaces HTML 5 for checking required checkboxes
	* If there is a required group of checkboxes: 
	* components\com_phocacart\views\item\tmpl\default.php reqC (cca line 277)
	* it checks for at least one checked checkbox
	* 1. it loops for every required checkbox
	* 2. then asks for groups or required checkbox
	* 3. then the group id selects all checkboxes in the group and make them not required if some of the checkbox was selected
	*/
	
	/*
	OBSOLETE
	public static function renderCheckBoxRequired() {
		
		
	/*	$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'.phjPriceBoxForm button[type="submit"]\').on(\'click\', function() {';
		$s[] = '      jQuery(this).closest("form").find(\' .checkbox-group.required input:checkbox\').each(function() {';// 1
		$s[] = '	  var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr(\'id\');';// 2
		$s[] = '      var phAttributeGroupItems	= jQuery(\'#\' + phAttributeGroup + \' input:checkbox\');';// 3
		$s[] = '         phAttributeGroupItems.prop(\'required\', true);';
		$s[] = '      	 if(phAttributeGroupItems.is(":checked")){';
		$s[] = '      		phAttributeGroupItems.prop(\'required\', false);';
		$s[] = '      	 }';
		$s[] = '      })';
		
		//var phCheckBoxGroup = jQuery(".checkbox-group-'.(int)$id.' input:checkbox");';
		//$s[] = '      phCheckBoxGroup.prop(\'required\', true);';
		//$s[] = '      if(phCheckBoxGroup.is(":checked")){';
		//$s[] = '         phCheckBoxGroup.prop(\'required\', false);';
		//$s[] = '      }';
		$s[] = '   });';
		$s[] = '})';
	*/	/*
		//if ($ajax == 1) {
		//	return '<script type="text/javascript">'.implode("\n", $s).'</script>';
		//} else {
		
		//	JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		//}
	} */
	
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
			$req['class'] 		= ' checkbox-group-'.(int)$id.' checkbox-group required';
		}
		return $req;	
	}
	
	
	public static function renderJsScrollTo($scrollTo = '', $animation = 0) {
		
		
		
		$s[] = 'jQuery(function() {';
		$s[] = '   if (jQuery("#ph-msg-ns").length > 0){';
		$s[] = '      jQuery(document).scrollTop( jQuery("#system-message").offset().top );';
		//$s[] = '      jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );';
		
		if ($scrollTo != '') {
			$s[] = '   } else {';
			if ($animation == 1) {
				$s[] = '	  jQuery(\'html,body\').animate({scrollTop: jQuery("#'.$scrollTo.'").offset().top}, 1500 );';
			} else {
				$s[] = '      jQuery(document).scrollTop( jQuery("#'.$scrollTo.'").offset().top );';
			}
		}
		$s[] = '   }';
		$s[] = '});';
			
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/**
	 * Shipping tracking code - add automatically tracking number to tracking link in edit of order - to see the link (while typing the id)
	 * @param string $idSource
	 * @param string $classDestination
	 */
	
	public static function renderJsAddTrackingCode($idSource, $classDestination) {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var destGlobal 	= jQuery( \'.'.$classDestination.'\').text();';
		$s[] = '   var sourceGlobal	= jQuery(\'#'.$idSource.'\').val();';
		$s[] = '   var textGlobal 	= destGlobal + sourceGlobal';
		$s[] = '   jQuery( \'.'.$classDestination.'\').html(textGlobal);';
		
		$s[] = '   jQuery(\'#'.$idSource.'\').on("input", function() {';
		$s[] = '       var source	= jQuery(this).val();';
		$s[] = '       var text = destGlobal + source;';
		$s[] = '       jQuery( \'.'.$classDestination.'\').html(text);';
		$s[] = '   })';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/*
	public static function renderDetectVirtualKeyboard() {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '  var phDefaultSize = jQuery(window).width() + jQuery(window).height()';
		$s[] = '  jQuery(window).resize(function(){';
		$s[] = '    if(jQuery(window).width() + jQuery(window).height() != phDefaultSize){';
		$s[] = '       ';
		$s[] = '      jQuery(".ph-pos-wrap-main").css("position","fixed");';  
		$s[] = '    } else {';
		$s[] = '       ';
		$s[] = '      jQuery(".ph-pos-wrap-main").css("position","relative");';
		$s[] = '    }';
		$s[] = '  });';
		$s[] = '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	*/
	
	
	
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>