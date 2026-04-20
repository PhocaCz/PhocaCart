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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

final class PhocacartRenderJs
{
    private function __construct() { }


    public static function renderAjaxAddToCart() {

        $paramsC 			= PhocacartUtils::getComponentParameters();
        $add_cart_method 	= $paramsC->get('add_cart_method', 2);

        // We need to refresh checkout site when AJAX used for removing or adding products to cart
        $task = 'checkout.add';
        $class = '.phItemCartBox';
        $cView = PhocacartUtils::isView('checkout') ? 1 : 0;

        // POS
        $isPOS = false;
        if (PhocacartUtils::isView('pos')) {
            $task = 'pos.add';
            $add_cart_method = 1;// POS has always 1 (ajax and no popup)
            $cView = 0;
            $class = '.phPosCartBox';
            $isPOS = true;
        }

        if ($add_cart_method == 0) {
            return false;
        }

        if ($add_cart_method > 0) {

            //$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=' . $task . '&format=json&' . Session::getFormToken() . '=1&checkoutview=' . (int)$cView;
            $urlAjax = Route::_('index.php?option=com_phocacart&task=' . $task . '&format=json&' . Session::getFormToken() . '=1&checkoutview=' . (int)$cView);


            $s = array();
            $s[] = 'function phDoSubmitFormAddToCart(sFormData) {';
            $s[] = '	var phUrl 	= "' . $urlAjax . '";';
            $s[] = '	var phOptions = [];';
            $s[] = '	phOptions["view"] = ' . (int)$cView . ';';
            $s[] = '	phOptions["method"]  = ' . (int)$add_cart_method . ';';
            $s[] = '	phOptions["task"]  = "add";';
            $s[] = '	phOptions["type"]  = "cart";';
            $s[] = '	phOptions["class"]  = "' . $class . '";';
            $s[] = $isPOS === true ? '	phOptions["pos"]  = 1;' : '	phOptions["pos"]  = 0;';
            $s[] = '	phDoRequestMethods(phUrl, sFormData, phOptions);';
            $s[] = '}';
            $s[] = ' ';
            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

            /*$s[] = '   let phRequest = jQuery.ajax({';
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
            //$s[] = '      error: function(data){}';
            $s[] = '   })';
            $s[] = '   return false;';
            $s[] = '}';

            $s[] = ' ';

            // :: EVENT (CLICK) Category/Items View (icon/button - ajax/standard)
            //$s[] = 'function phEventClickFormAddToCart(phFormId) {';
            //$s[] = '   var phForm = \'#\' + phFormId;';
            //$s[] = '   var sForm 	= jQuery(this).closest("form");';// Find in which form the right button was clicked
            //$s[] = '   var sFormData = jQuery(phForm).serialize();';
            //$s[] = '   phDoSubmitFormAddToCart(sFormData);';
            //$s[] = '}';

            // Set it onclick as it is used in even not ajax submitting
            //$s[] = 'function phEventClickFormAddToCart(phFormId) {';
            //$s[] = '   var phForm = \'#\' + phFormId;';
            //$s[] = '   jQuery(\'phFormId\').find(\':submit\').click();"';
            //$s[] = '   return false;';
            //$s[] = '}';

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
            $s[] = ' ';*/
        }
    }

    public static function renderAjaxUpdateCart() {

        $paramsC 			= PhocacartUtils::getComponentParameters();
        $add_cart_method 	= $paramsC->get('add_cart_method', 2);

        // We need to refresh checkout site when AJAX used for removing or adding products to cart
        $task = 'checkout.update';
        $class = '.phCheckoutCartBox';
        $cView = PhocacartUtils::isView('checkout') ? 1 : 0;

        // POS
        $isPOS = false;
        if (PhocacartUtils::isView('pos')) {
            $task = 'pos.update';
            $add_cart_method = 1;// POS has always 1 (ajax and no popup)
            $cView = 0;
            $class = '.phPosCartBox';
            $isPOS = true;
        }

        //$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=' . $task . '&format=json&' . Session::getFormToken() . '=1&checkoutview=' . (int)$cView;
        $urlAjax = Route::_('index.php?option=com_phocacart&task=' . $task . '&format=json&' . Session::getFormToken() . '=1&checkoutview=' . (int)$cView);

        $s = array();
        $s[] = 'function phDoSubmitFormUpdateCart(sFormData) {';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["view"] = ' . (int)$cView . ';';
        $s[] = '	phOptions["method"]  = ' . (int)$add_cart_method . ';';
        $s[] = '	phOptions["task"]  = "update";';
        $s[] = '	phOptions["type"]  = "cart";';
        $s[] = '	phOptions["class"]  = "' . $class . '";';
        $s[] = $isPOS === true ? '	phOptions["pos"]  = 1;' : '	phOptions["pos"]  = 0;';
        $s[] = '	phDoRequestMethods(phUrl, sFormData, phOptions);';
        $s[] = '}';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

        /*
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
                //$s[] = '            if (phCheckoutView == 1) {';
                //$s[] = '               setTimeout(function() {location.reload();}, 0001);';
                //$s[] = '            }';

                $s[] = '         } else if (data.status == 0){';

                // If no popup is displayed we can relaod the page when we are in comparison page
                // If popup, this will be done when clicking continue or comparison list
                //$s[] = '            if (phCheckoutView == 1) {';
                //$s[] = '               setTimeout(function() {location.reload();}, 0001);';
                //$s[] = '            }';

                $s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';
                $s[] = ' 				jQuery("#phContainer").html(data.popup);';
                $s[] = ' 				jQuery("#phAddToCartPopup").modal();';

                $s[] = '         } else {';
                //$s[] = '					// Don\'t change the price box';
                $s[] = '         }';
                $s[] = '      },';
                //$s[] = '      error: function(data){}';
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
                $s[] = ' ';*/

        //$s[] = '}';
    }


    public static function renderAjaxAddToCompare() {

        $paramsC 			= PhocacartUtils::getComponentParameters();
        $add_compare_method = $paramsC->get('add_compare_method', 2);

        // We need to refresh comparison site when AJAX used for removing or adding products to comparison list
        $app 	= Factory::getApplication();
        $view 	= $app->getInput()->get('view', '');
        $option = $app->getInput()->get('option', '');
        $cView	= $option == 'com_phocacart' && $view == 'comparison' ? 1 : 0;

        if ($add_compare_method == 0) {
            return false;
        }

        if ($add_compare_method > 0) {

        	//$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=comparison.add&format=json&' . Session::getFormToken() . '=1&comparisonview=' . (int)$cView;
            $urlAjax = Route::_('index.php?option=com_phocacart&task=comparison.add&format=json&' . Session::getFormToken() . '=1&comparisonview=' . (int)$cView);

            $s = array();
            $s[] = 'function phItemCompareBoxFormAjax(phItemId) {';
            $s[] = '	var phUrl 	= "' . $urlAjax . '";';
            $s[] = '	var phItem = \'#\' + phItemId;';
            $s[] = '	var phOptions = [];';
            $s[] = '	phOptions["view"] = ' . (int)$cView . ';';
            $s[] = '	phOptions["method"]  = ' . (int)$add_compare_method . ';';
            $s[] = '	phOptions["task"]  = "add";';
            $s[] = '	phOptions["type"]  = "compare";';
            $s[] = '	var phData = jQuery(phItem).serialize();';
            $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
            $s[] = '}';
            $s[] = ' ';
            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

            /*$s[] = ' ';
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
            //$s[] = '       return false;';*/
        }
    }


    public static function renderAjaxRemoveFromCompare() {

        $paramsC 			= PhocacartUtils::getComponentParameters();
        $add_compare_method = $paramsC->get('add_compare_method', 2);

        // We need to refresh comparison site when AJAX used for removing or adding products to comparison list
        $app 	= Factory::getApplication();
        $view 	= $app->getInput()->get('view', '');
        $option = $app->getInput()->get('option', '');
        $cView	= $option == 'com_phocacart' && $view == 'comparison' ? 1 : 0;

        if ($add_compare_method == 0) {
            return false;
        }

        if ($add_compare_method > 0) {
            //$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=comparison.remove&format=json&' . Session::getFormToken() . '=1&comparisonview=' . (int)$cView;
            $urlAjax = Route::_( 'index.php?option=com_phocacart&task=comparison.remove&format=json&' . Session::getFormToken() . '=1&comparisonview=' . (int)$cView);

            $s = array();
            $s[] = ' ';
            $s[] = 'function phItemRemoveCompareFormAjax(phItemId) {';
            $s[] = '	var phUrl 	= "' . $urlAjax . '";';
            $s[] = '	var phItem = \'#\' + phItemId;';
            $s[] = '	var phOptions = [];';
            $s[] = '	phOptions["view"] = ' . (int)$cView . ';';
            $s[] = '	phOptions["method"]  = ' . (int)$add_compare_method . ';';
            $s[] = '	phOptions["task"]  = "remove";';
            $s[] = '	phOptions["type"]  = "compare";';
            $s[] = '	var phData = jQuery(phItem).serialize();';
            $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
            $s[] = '}';
            $s[] = ' ';
            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

            /*$s[] = ' ';
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
            //$s[] = '       return false;';*/
        }
    }


    public static function renderAjaxAddToWishList() {

        $paramsC 				= PhocacartUtils::getComponentParameters();
        $add_wishlist_method 	= $paramsC->get('add_wishlist_method', 2);

        // We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
        $app 	= Factory::getApplication();
        $view 	= $app->getInput()->get('view', '');
        $option = $app->getInput()->get('option', '');
        $wView	= $option == 'com_phocacart' && $view == 'wishlist' ? 1 : 0;

        if ($add_wishlist_method == 0) {
            return false;
        }

        if ($add_wishlist_method > 0) {
            //$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=wishlist.add&format=json&' . Session::getFormToken() . '=1&wishlistview=' . (int)$wView;
            $urlAjax = ('index.php?option=com_phocacart&task=wishlist.add&format=json&' . Session::getFormToken() . '=1&wishlistview=' . (int)$wView);
            $s = array();
            $s[] = 'function phItemWishListBoxFormAjax(phItemId) {';
            $s[] = '	var phUrl 	= "' . $urlAjax . '";';
            $s[] = '	var phItem = \'#\' + phItemId;';
            $s[] = '	var phOptions = [];';
            $s[] = '	phOptions["view"] = ' . (int)$wView . ';';
            $s[] = '	phOptions["method"]  = ' . (int)$add_wishlist_method . ';';
            $s[] = '	phOptions["task"]  = "add";';
            $s[] = '	phOptions["type"]  = "wishlist";';
            $s[] = '	var phData = jQuery(phItem).serialize();';
            $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
            $s[] = '}';
            $s[] = ' ';

            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

            /*$s[] = ' ';
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
            //$s[] = '       return false;';*/

        }
    }

    public static function renderAjaxRemoveFromWishList() {

        $paramsC = PhocacartUtils::getComponentParameters();
        $add_wishlist_method = $paramsC->get('add_wishlist_method', 2);

        // We need to refresh wishlist site when AJAX used for removing or adding products to wishlist list
        $app = Factory::getApplication();
        $view = $app->getInput()->get('view', '');
        $option = $app->getInput()->get('option', '');
        $wView	= $option == 'com_phocacart' && $view == 'wishlist' ? 1 : 0;

        if ($add_wishlist_method == 0) {
            return false;
        }

        if ($add_wishlist_method > 0) {

            //$urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=wishlist.remove&format=json&' . Session::getFormToken() . '=1&wishlistview=' . (int)$wView;
            $urlAjax = Route::_('index.php?option=com_phocacart&task=wishlist.remove&format=json&' . Session::getFormToken() . '=1&wishlistview=' . (int)$wView);
            $s = array();
            $s[] = ' ';
            $s[] = 'function phItemRemoveWishListFormAjax(phItemId) {';
            $s[] = '	var phUrl 	= "' . $urlAjax . '";';
            $s[] = '	var phItem = \'#\' + phItemId;';
            $s[] = '	var phOptions = [];';
            $s[] = '	phOptions["view"] = ' . (int)$wView . ';';
            $s[] = '	phOptions["method"]  = ' . (int)$add_wishlist_method . ';';
            $s[] = '	phOptions["task"]  = "remove";';
            $s[] = '	phOptions["type"]  = "wishlist";';
            $s[] = '	var phData = jQuery(phItem).serialize();';
            $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
            $s[] = '}';
            $s[] = ' ';

            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

            /*
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
                        //$s[] = '       return false;';*/
        }
    }

    public static function renderAjaxQuickViewBox($options = array()) {

        $style 					= PhocacartRenderStyle::getStyles();
        $paramsC 				= PhocacartUtils::getComponentParameters();
        $dynamic_change_price 	= $paramsC->get('dynamic_change_price', 1);
        $load_chosen 			= $paramsC->get('load_chosen', 0);
        $quantity_input_spinner = $paramsC->get('quantity_input_spinner', 0);

        // needed because of phChangeAttributeType() - is included
        //$document = Factory::getDocument();
        //$document->addScript(Uri::root(true) . '/media/com_phocacart/js/phoca/jquery.phocaattribute.min.js');
        $media = PhocacartRenderMedia::getInstance('main');
        $media->loadPhocaAttribute(1);
        // We need to refresh comparison site when AJAX used for removing or adding products to comparison list


        //$urlAjax = Uri::base(true) . '/index.php?test=tes&option=com_phocacart&view=item&format=json&tmpl=component&' . Session::getFormToken() . '=1';
        $urlAjax = Route::_('index.php?test=tes&option=com_phocacart&view=item&format=json&tmpl=component&' . Session::getFormToken() . '=1');

        $s = array();
        $s[] = 'function phItemQuickViewBoxFormAjax(phItemId) {';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phItem = \'#\' + phItemId;';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["view"] = "";';
        $s[] = '	phOptions["method"]  = "";';
        $s[] = '	phOptions["task"]  = "";';
        $s[] = '	phOptions["type"]  = "quickview";';
        $s[] = '	phOptions["load_chosen"]  = ' . (int)$load_chosen . ';';
        $s[] = '	phOptions["quantity_input_spinner"]  = ' . (int)$quantity_input_spinner . ';';
      /*  if ((int)$quantity_input_spinner == 2) {
            $s[] = '	phOptions["icon_spinner_verticalup"] =  \'<span class="' . $style['i']['chevron-up'] . '"></span>\';';
            $s[] = '	phOptions["icon_spinner_verticaldown"] =  \'<span class="' . $style['i']['chevron-down'] . '"></span>\';';
        } else {
            $s[] = '	phOptions["icon_spinner_verticalup"] =  \'<span class="' . $style['i']['plus'] . '"></span>\';';
            $s[] = '	phOptions["icon_spinner_verticaldown"] =  \'<span class="' . $style['i']['minus'] . '"></span>\';';
        }
*/
        if ((int)$quantity_input_spinner == 2) {
            $s[] = '	phOptions["icon_spinner_verticalup"] =  \''.PhocacartRenderIcon::icon($style['i']['chevron-up']).'\';';
            $s[] = '	phOptions["icon_spinner_verticaldown"] =  \''.PhocacartRenderIcon::icon($style['i']['chevron-down']).'\';';
        } else {
            $s[] = '	phOptions["icon_spinner_verticalup"] =  \''.PhocacartRenderIcon::icon($style['i']['plus']).'\';';
            $s[] = '	phOptions["icon_spinner_verticaldown"] =  \''.PhocacartRenderIcon::icon($style['i']['minus']).'\';';
        }


        $s[] = '	var phData = jQuery(phItem).serialize();';
        $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
        $s[] = '}';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

        /*$s[] = '	phRequest = jQuery.ajax({';
        $s[] = '		type: "POST",';
        $s[] = '		url: phUrl,';
        $s[] = '		async: "false",';
        $s[] = '		cache: "false",';
        $s[] = '		data: phData,';
        $s[] = '		dataType:"JSON",';
        $s[] = '		success: function(data){';
        $s[] = '			if (data.status == 1){';
        //$s[] = '				jQuery("#phItemCompareBox").html(data.item);';
        //$s[] = ' 				jQuery("#phQuickViewPopupBody").html(data.popup);'; added in ajax
        //- $s[] = ' 			jQuery("#phContainer").html(data.popup); ';
        $s[] = ' 				jQuery(".phjItemQuick.phjProductAttribute").remove(); ';// Clear attributes from dom when ajax reload
        $s[] = ' 				jQuery("body").append(jQuery("#phContainer"));';
        $s[] = ' 				jQuery("#phContainer").html(data.popup); ';
        //- $s[] = ' 			jQuery("#phQuickViewPopup").modal();';
        $s[] = ' 				jQuery("body").append(jQuery("#phQuickViewPopup"));';
        $s[] = ' 				jQuery("#phQuickViewPopup").modal();';
        if ($load_chosen > 0) {

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

        $s[] = '		'. $options['touchspin'];// Touch spin for input

        $s[] = '			  } else {';
        //$s[] = '					// Don\'t change the price box';
        $s[] = '			  }';
        $s[] = '		}';
        $s[] = '	})';
        //$s[] = '		e.preventDefault();';
        //$s[] = '       return false;';*/
        //$s[] = '}';
        //$s[] = '})';
        //$s[] = ' ';

    }


    /*
     * Change Price, Stock, ID (EAN, SKU, ...)
     * select box (standard, image, color)
     * check box
     */
   /* public static function renderAjaxChangeProductDataByOptions($id = 0, $typeView = '', $class = '') {

        $paramsC = PhocacartUtils::getComponentParameters();
        $dynamic_change_price   = $paramsC->get('dynamic_change_price', 0);
        $dynamic_change_stock   = $paramsC->get('dynamic_change_stock', 0);
        $dynamic_change_id   = $paramsC->get('dynamic_change_id', 0);
        $theme                  = $paramsC->get('theme', 'bs3');

        if ($dynamic_change_price == 0 && $dynamic_change_stock == 0 && $dynamic_change_id == 0) {
            return false;
        }


        $urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changedatabox&format=json&' . Session::getFormToken() . '=1';

        $s = array();
        $s[] = 'function phAjaxChangeData' . $typeView . '(phProductId, phDataA1, phDataA2){';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["id"] = phProductId;';
         $s[] = '	phOptions["id_item_price"] = "#phItemPriceBox' . $typeView . '" + phProductId;';
         $s[] = '	phOptions["id_item_stock"] = "#phItemStockBox' . $typeView . '" + phProductId;';
         $s[] = '	phOptions["id_item_id"] = "#phItemIdBox' . $typeView . '" + phProductId;';
         $s[] = '	phOptions["product_add_to_cart_item"] 		= ".phProductAddToCart' . $typeView . '" + phProductId;';// display or hide add to cart button
         $s[] = '	phOptions["product_add_to_cart_item_icon"] 	= ".phProductAddToCartIcon' . $typeView . '" + phProductId;';// display or hide add to cart icon
        $s[] = '	phOptions["view"] = ' . (int)$typeView . ';';
         $s[] = '	phOptions["method_price"]  = ' . (int)$dynamic_change_price . ';';
         $s[] = '	phOptions["method_stock"]  = ' . (int)$dynamic_change_stock . ';';
         $s[] = '	phOptions["method_id"]  = ' . (int)$dynamic_change_id . ';';
        $s[] = '	phOptions["task"]  = "change";';
         $s[] = '	phOptions["type"]  = "changedata";';
        $s[] = '	phOptions["class"]  = "' . $class . '";';
        $s[] = '	var phData 	= \'id=\'+ phOptions["id"] +\'&\'+ phDataA1 +\'&\'+ phDataA2 +\'&\'+\'class=\'+ phOptions["class"] +\'&\'+\'typeview=\'+ phOptions["view"];';
        $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
        $s[] = '}';
        $s[] = ' ';



        $s[] = 'jQuery(document).ready(function(){  ';
      /*  $s[] = '    var phSelectboxA             =  "select.phj' . $typeView . '.phjProductAttribute";';
        $s[] = '    var phSelectboxASelected    =  phSelectboxA + ":selected";';
        $s[] = '	jQuery(document).on(\'change\', phSelectboxA, function(e){ ';// Select Box
        //$s[] = '		jQuery(this).off("change");';

        $s[] = '		var phTypeView = jQuery(this).data(\'type-view\');';
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes
        $s[] = '		phAjaxChangeData' . $typeView . '(phProductId, phDataA1, phDataA2);';// If REQUIRED, don't allow to untick image or color "select box" - see jquery.phocaattribute.js
        $s[] = '	})';*/

        // Checkbox
        // Unfortunately, we cannot run event:
        // 1. CHANGE - because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
        // 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
        // So we run click on div box over the checkbox which works and don't run ajax 3x
        //$s[] = '	jQuery(document).on(\'change\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute :checkbox\', function(){';
        //$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';

      /*  $s[] = '    var phCheckboxA             =  ".ph-checkbox-attribute.phj' . $typeView . '.phjProductAttribute"';
       // $s[] = '    var phCheckboxAInputChecked =  phCheckboxA + " input:checked"';
        $s[] = '	jQuery(document).on(\'click\', phCheckboxA, function(e){';
        $s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';// Prevent from twice running
        if ($theme == 'bs4') {
            $s[] = '        if (e.target.tagName.toUpperCase() === "SPAN" || e.target.tagName.toUpperCase() === "IMG") { return;}';// Prevent from twice running
        }
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phTypeView = jQuery(this).data(\'type-view\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes

        // If REQUIRED, don't allow to untick all checkboxes
        $s[] = '		var phRequired = jQuery(this).data("required");';
        $s[] = '        var phCheckboxAInputChecked =  "#" + jQuery(this).attr("id") + " input:checked"';
        $s[] = '        var phACheckedLength = jQuery(phCheckboxAInputChecked).length;';

        $s[] = '        if (phACheckedLength == 0) {';
        $s[] = '            var phThisLabel = jQuery(e.target).parent();';// Bootstrap checkboxes - colors, images
        $s[] = '            phThisLabel.addClass("active");';// Bootstrap checkboxes - colors, images
        $s[] = '            e.preventDefault();';
        $s[] = '            return false;';
        $s[] = '        }';

        $s[] = '		phAjaxChangeData' . $typeView . '(phProductId, phDataA1, phDataA2);';
        $s[] = '	})';*/

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
/*
        $s[] = '})';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }*/


    /*
     * Change Price
     * select box (standard, image, color)
     * check box
     *//*
    public static function renderAjaxChangeProductPriceByOptions($id = 0, $typeView = '', $class = '') {

        $paramsC = PhocacartUtils::getComponentParameters();
        $dynamic_change_price   = $paramsC->get('dynamic_change_price', 0);
        $theme                  = $paramsC->get('theme', 'bs3');

        if ($dynamic_change_price == 0) {
            return false;
        }


        $urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changepricebox&format=json&' . Session::getFormToken() . '=1';

        $s = array();
        $s[] = 'function phAjaxChangePrice' . $typeView . '(phProductId, phDataA1, phDataA2){';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["id"] = phProductId;';
        $s[] = '	phOptions["id_item"] = "#phItemPriceBox' . $typeView . '" + phProductId;';
        $s[] = '	phOptions["view"] = ' . (int)$typeView . ';';
        $s[] = '	phOptions["method"]  = ' . (int)$dynamic_change_price . ';';
        $s[] = '	phOptions["task"]  = "change";';
        $s[] = '	phOptions["type"]  = "changeprice";';
        $s[] = '	phOptions["class"]  = "' . $class . '";';
        $s[] = '	var phData 	= \'id=\'+ phOptions["id"] +\'&\'+ phDataA1 +\'&\'+ phDataA2 +\'&\'+\'class=\'+ phOptions["class"] +\'&\'+\'typeview=\'+ phOptions["view"];';
        $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
        $s[] = '}';
        $s[] = ' ';


       /* $s[] = '	jQuery.ajax({';
        $s[] = '		type: "POST",';
        $s[] = '		url: phUrl,';
        $s[] = '		async: "false",';
        $s[] = '		cache: "false",';
        $s[] = '		data: phData,';
        $s[] = '		dataType:"JSON",';
        $s[] = '		success: function(data){';
        $s[] = '			if (data.status == 1){';
        $s[] = '				jQuery(phIdItem).html(data.item);';
        $s[] = '		   } else {';// Don't change the price box, don't render any error message
        $s[] = '			  }';
        $s[] = '		}';
        $s[] = '	})';
        $s[] = '}';
        $s[] = ' ';*//*


        $s[] = 'jQuery(document).ready(function(){  ';
        $s[] = '    var phSelectboxA             =  "select.phj' . $typeView . '.phjProductAttribute";';
        $s[] = '    var phSelectboxASelected    =  phSelectboxA + ":selected";';

        $s[] = '	jQuery(document).on(\'change\', phSelectboxA, function(e){ ';// Select Box

        //$s[] = '		jQuery(this).off("change");';
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes
        $s[] = '		phAjaxChangePrice' . $typeView . '(phProductId, phDataA1, phDataA2);';// If REQUIRED, don't allow to untick image or color "select box" - see jquery.phocaattribute.js
        $s[] = '	})';

        // Checkbox
        // Unfortunately, we cannot run event:
        // 1. CHANGE - because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
        // 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
        // So we run click on div box over the checkbox which works and don't run ajax 3x
        //$s[] = '	jQuery(document).on(\'change\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute :checkbox\', function(){';
        //$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';

        $s[] = '    var phCheckboxA             =  ".ph-checkbox-attribute.phj' . $typeView . '.phjProductAttribute"';
       // $s[] = '    var phCheckboxAInputChecked =  phCheckboxA + " input:checked"';
        $s[] = '	jQuery(document).on(\'click\', phCheckboxA, function(e){';
        $s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';// Prevent from twice running
        if ($theme == 'bs4') {
            $s[] = '        if (e.target.tagName.toUpperCase() === "SPAN" || e.target.tagName.toUpperCase() === "IMG") { return;}';// Prevent from twice running
        }
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes

        // If REQUIRED, don't allow to untick all checkboxes
        $s[] = '		var phRequired = jQuery(this).data("required");';
        $s[] = '        var phCheckboxAInputChecked =  "#" + jQuery(this).attr("id") + " input:checked"';
        $s[] = '        var phACheckedLength = jQuery(phCheckboxAInputChecked).length;';

        $s[] = '        if (phACheckedLength == 0) {';
        $s[] = '            var phThisLabel = jQuery(e.target).parent();';// Bootstrap checkboxes - colors, images
        $s[] = '            phThisLabel.addClass("active");';// Bootstrap checkboxes - colors, images
        $s[] = '            e.preventDefault();';
        $s[] = '            return false;';
        $s[] = '        }';

        $s[] = '		phAjaxChangePrice' . $typeView . '(phProductId, phDataA1, phDataA2);';
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
        }*//*

        $s[] = '})';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }*/

    /*
     * Change ID (SKU, EAN, ...)
     * select box (standard, image, color)
     * check box
     *//*
    public static function renderAjaxChangeProductIdByOptions($id = 0, $typeView = '', $class = '') {

        $paramsC = PhocacartUtils::getComponentParameters();
        $dynamic_change_id      = $paramsC->get('dynamic_change_id', 0);
        $theme                  = $paramsC->get('theme', 'bs3');

        if ($dynamic_change_id == 0) {
            return false;
        }


        $urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changeidbox&format=json&' . Session::getFormToken() . '=1';

        $s = array();
        $s[] = 'function phAjaxChangeId' . $typeView . '(phProductId, phDataA1, phDataA2){';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["id"] = phProductId;';
        $s[] = '	phOptions["id_item"] = "#phItemIdBox' . $typeView . '" + phProductId;';
        $s[] = '	phOptions["view"] = ' . (int)$typeView . ';';
        $s[] = '	phOptions["method"]  = ' . (int)$dynamic_change_id . ';';
        $s[] = '	phOptions["task"]  = "change";';
        $s[] = '	phOptions["type"]  = "changeid";';
        $s[] = '	phOptions["class"]  = "' . $class . '";';
        $s[] = '	var phData 	= \'id=\'+ phOptions["id"] +\'&\'+ phDataA1 +\'&\'+ phDataA2 +\'&\'+\'class=\'+ phOptions["class"] +\'&\'+\'typeview=\'+ phOptions["view"];';
        $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
        $s[] = '}';
        $s[] = ' ';

        $s[] = 'jQuery(document).ready(function(){  ';
        $s[] = '    var phSelectboxA             =  "select.phj' . $typeView . '.phjProductAttribute";';
        $s[] = '    var phSelectboxASelected    =  phSelectboxA + ":selected";';
        $s[] = '	jQuery(document).on(\'change\', phSelectboxA, function(e){ ';// Select Box
        //$s[] = '		jQuery(this).off("change");';
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes
        $s[] = '		phAjaxChangeId' . $typeView . '(phProductId, phDataA1, phDataA2);';// If REQUIRED, don't allow to untick image or color "select box" - see jquery.phocaattribute.js
        $s[] = '	})';

        // Checkbox
        // Unfortunately, we cannot run event:
        // 1. CHANGE - because of Bootstrap toogle button, this will run 3x ajax (checkbox is hidden and changes when clicking on button)
        // 2. CLICK directly on checkbox as if Bootstrap toogle button is use, clicked will be icon not the checkbox
        // So we run click on div box over the checkbox which works and don't run ajax 3x
        //$s[] = '	jQuery(document).on(\'change\', \'.ph-checkbox-attribute.phj'.$typeView.'.phjProductAttribute :checkbox\', function(){';
        //$s[] = '	jQuery(document).on(\'click\', \'#phItemPriceBoxForm .ph-checkbox-attribute.ph-item-input-set-attributes\', function(){';

        $s[] = '    var phCheckboxA             =  ".ph-checkbox-attribute.phj' . $typeView . '.phjProductAttribute"';
       // $s[] = '    var phCheckboxAInputChecked =  phCheckboxA + " input:checked"';
        $s[] = '	jQuery(document).on(\'click\', phCheckboxA, function(e){';
        $s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';// Prevent from twice running
        if ($theme == 'bs4') {
            $s[] = '        if (e.target.tagName.toUpperCase() === "SPAN" || e.target.tagName.toUpperCase() === "IMG") { return;}';// Prevent from twice running
        }
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes

        // If REQUIRED, don't allow to untick all checkboxes
        $s[] = '		var phRequired = jQuery(this).data("required");';
        $s[] = '        var phCheckboxAInputChecked =  "#" + jQuery(this).attr("id") + " input:checked"';
        $s[] = '        var phACheckedLength = jQuery(phCheckboxAInputChecked).length;';

        $s[] = '        if (phACheckedLength == 0) {';
        $s[] = '            var phThisLabel = jQuery(e.target).parent();';// Bootstrap checkboxes - colors, images
        $s[] = '            phThisLabel.addClass("active");';// Bootstrap checkboxes - colors, images
        $s[] = '            e.preventDefault();';
        $s[] = '            return false;';
        $s[] = '        }';

        $s[] = '		phAjaxChangeId' . $typeView . '(phProductId, phDataA1, phDataA2);';
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
        }*//*

        $s[] = '})';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }*/


    /*
     * Change Stock
     * select box (standard, image, color)
     * check box
     *//*
    public static function renderAjaxChangeProductStockByOptions($id = 0, $typeView = '', $class = '') {

        $paramsC                = PhocacartUtils::getComponentParameters();
        $dynamic_change_stock   = $paramsC->get('dynamic_change_stock', 0);
        $hide_add_to_cart_stock = $paramsC->get('hide_add_to_cart_stock', 0);
        $theme                  = $paramsC->get('theme', 'bs3');

        if ($dynamic_change_stock == 0) {
            return false;
        }

        $urlAjax = Uri::base(true) . '/index.php?option=com_phocacart&task=checkout.changestockbox&format=json&' . Session::getFormToken() . '=1';

       /* $s[] = ' ';
        $s[] = '/* Function phAjaxChangeStock *//* ';
        $s[] = 'function phAjaxChangeStock' . $typeView . '(phProductId, phDataA1, phDataA2){';
        $s[] = '	var phUrl 					= "' . $urlAjax . '";';
        $s[] = '	var phId 					= phProductId;' . "\n";
        $s[] = '	var phIdItem 				= "#phItemStockBox' . $typeView . '" + phProductId;';
        $s[] = '	var phProductAddToCart 		= ".phProductAddToCart' . $typeView . '" + phProductId;';// display or hide add to cart button
        $s[] = '	var phProductAddToCartIcon 	= ".phProductAddToCartIcon' . $typeView . '" + phProductId;';// display or hide add to cart icon
        $s[] = '	var phClass 				= "' . $class . '";';
        $s[] = '	var phTypeView 				= "' . $typeView . '";';

        $s[] = '	var phData 	= \'id=\'+phId+\'&\'+phDataA1+\'&\'+phDataA2+\'&\'+\'class=\'+phClass+\'&\'+\'typeview=\'+phTypeView;';
        *//*

        $s = array();
        $s[] = 'function phAjaxChangeStock' . $typeView . '(phProductId, phDataA1, phDataA2){';
        $s[] = '	var phUrl 	= "' . $urlAjax . '";';
        $s[] = '	var phOptions = [];';
        $s[] = '	phOptions["id"] = phProductId;';
        $s[] = '	phOptions["id_item"] = "#phItemStockBox' . $typeView . '" + phProductId;';
        $s[] = '	phOptions["product_add_to_cart_item"] 		= ".phProductAddToCart' . $typeView . '" + phProductId;';// display or hide add to cart button
        $s[] = '	phOptions["product_add_to_cart_item_icon"] 	= ".phProductAddToCartIcon' . $typeView . '" + phProductId;';// display or hide add to cart icon
        $s[] = '	phOptions["view"] = ' . (int)$typeView . ';';
        $s[] = '	phOptions["method"]  = ' . (int)$hide_add_to_cart_stock . ';';
        $s[] = '	phOptions["task"]  = "change";';
        $s[] = '	phOptions["type"]  = "changestock";';
        $s[] = '	phOptions["class"]  = "' . $class . '";';
        $s[] = '	var phData 	= \'id=\'+ phOptions["id"] +\'&\'+ phDataA1 +\'&\'+ phDataA2 +\'&\'+\'class=\'+ phOptions["class"] +\'&\'+\'typeview=\'+ phOptions["view"];';
        $s[] = '	phDoRequestMethods(phUrl, phData, phOptions);';
        $s[] = '}';
        $s[] = ' ';


/*

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
        $s[] = '			  } else {';// Don't change the price box, don't render any error message
        $s[] = '			  }';
        $s[] = '		}';
        $s[] = '	})';
        $s[] = '}';
        $s[] = ' ';
*//*
        $s[] = 'jQuery(document).ready(function(){';
        $s[] = '	jQuery(document).on(\'change\', \'select.phj' . $typeView . '.phjProductAttribute\', function(){';// select box
        //$s[] = '		jQuery(this).off("change");';
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes
        $s[] = '		phAjaxChangeStock' . $typeView . '(phProductId, phDataA1, phDataA2);';// If REQUIRED, don't allow to untick image or color "select box" - see jquery.phocaattribute.js
        $s[] = '	})';

        $s[] = '    var phCheckboxA             =  ".ph-checkbox-attribute.phj' . $typeView . '.phjProductAttribute"';
        //$s[] = '    var phCheckboxAInputChecked =  phCheckboxA + " input:checked"';
        $s[] = '	jQuery(document).on(\'click\', phCheckboxA, function(e){';// check box
        $s[] = '        if (e.target.tagName.toUpperCase() === "LABEL") { return;}';// Prevent from twice running
        if ($theme == 'bs4') {
            $s[] = '        if (e.target.tagName.toUpperCase() === "SPAN" || e.target.tagName.toUpperCase() === "IMG") { return;}';// Prevent from twice running
        }
        $s[] = '		var phProductId = jQuery(this).data(\'product-id\');';
        $s[] = '		var phProductGroup = \'.phjAddToCartV' . $typeView . 'P\' + phProductId;';
        $s[] = '		var phDataA1 = jQuery(phProductGroup).find(\'select\').serialize();';// All Selects
        $s[] = '		var phDataA2 = jQuery(phProductGroup).find(\':checkbox\').serialize();';// All Checkboxes

        // If REQUIRED, don't allow to untick all checkboxes
        $s[] = '		var phRequired = jQuery(this).data("required");';
        $s[] = '        var phCheckboxAInputChecked =  "#" + jQuery(this).attr("id") + " input:checked"';
        $s[] = '        var phACheckedLength = jQuery(phCheckboxAInputChecked).length;';

        $s[] = '        if (phACheckedLength == 0) {';
        $s[] = '            var phThisLabel = jQuery(e.target).parent();';// Bootstrap checkboxes - colors, images
        $s[] = '            phThisLabel.addClass("active");';// Bootstrap checkboxes - colors, images
        $s[] = '            e.preventDefault();';
        $s[] = '            return false;';
        $s[] = '        }';

        $s[] = '		phAjaxChangeStock' . $typeView . '(phProductId, phDataA1, phDataA2);';
        $s[] = '	})';

        $s[] = '})';
        $s[] = ' ';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }*/

    /*
     * JS equivalent to PhocacartPrice::getPriceFormat();
     */

    public static function getPriceFormatJavascript($price_decimals, $price_dec_symbol, $price_thousands_sep, $price_currency_symbol, $price_prefix, $price_suffix, $price_format) {

        //Factory::getDocument()->addScript(Uri::root(true).'/media/com_phocacart/js/number_format.js');
        $s = array();
        $s[] = 'function phGetPriceFormat($price) {';
        $s[] = '	var $negative = 0;';
        $s[] = ' 	if ($price < 0) {';
        $s[] = ' 		$negative = 1;';
        $s[] = '	}';

        $s[] = '	if ($negative == 1 ) {';
        $s[] = ' 		$price = Math.abs($price);';
        $s[] = ' 	}';

        $s[] = ' 	$price = phNumberFormat($price, "' . $price_decimals . '", "' . $price_dec_symbol . '", "' . $price_thousands_sep . '");';

        switch ($price_format) {
            case 1:             $s[] = '	$price = $price + "' . $price_currency_symbol . '";';       break;
            case 2:             $s[] = '	$price = "' . $price_currency_symbol . '" + $price;';       break;
            case 3:             $s[] = '	$price = "' . $price_currency_symbol . '" + " " + $price;'; break;
            case 0: default:    $s[] = '	$price = $price + " " + "' . $price_currency_symbol . '";'; break;
        }

        $s[] = '	if ($negative == 1) {';
        $s[] = '		return "- " + "' . addslashes($price_prefix) . '" + $price + "' . addslashes($price_suffix) . '";';
        $s[] = '	} else {';
        $s[] = '		return "' . addslashes($price_prefix) . '" + $price + "' . addslashes($price_suffix) . '";';
        $s[] = '	}';
        $s[] = '}';
        $s[] = ' ';

        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
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
        $req['attribute']   = '';// Attribute - required field HTML 5
        $req['span']        = '';// Span - displayed * next to title
        $req['class']       = '';// Class - Checkboxes cannot be checked per HTML 5, jquery used PhocacartRenderJs::renderCheckBoxRequired()
        $req['required']    = 0;// data-required attribute

        if ($required) {
            $req['attribute']   = ' required="" aria-required="true"';
            $req['span']        = ' <span class="ph-req">*</span>';
            $req['class']       = ' checkbox-group-' . (int)$id . ' checkbox-group required';
            $req['required']    = 1;
        }
        return $req;
    }

    public static function renderJsScrollTo($scrollTo = '', $animation = 0) {

        $s[] = 'jQuery(function() {';
        $s[] = '   if (jQuery("#ph-msg-ns").length > 0){';
        $s[] = '      jQuery(document).scrollTop( jQuery("#ph-msg-ns").parent().offset().top );';
        //$s[] = '      jQuery(\'html,body\').animate({scrollTop: jQuery("#system-message").offset().top}, 1500 );';

        if ($scrollTo != '') {
            $s[] = '   } else {';
            if ($animation == 1) {
                $s[] = '	  jQuery(\'html,body\').animate({scrollTop: jQuery("#' . $scrollTo . '").offset().top}, 1500 );';
            } else  if ($animation == 2) {
                $s[] = '	  jQuery(\'html,body\').animate({scrollTop: jQuery("#' . $scrollTo . '").offset().top}, 1000 );';
            } else {
                $s[] = '      jQuery(document).scrollTop( jQuery("#' . $scrollTo . '").offset().top );';
            }
        }
        $s[] = '   }';
        $s[] = '});';

        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }

    /**
     * Shipping tracking code - add automatically tracking number to tracking link in edit of order - to see the link (while typing the id)
     * @param string $idSource
     * @param string $classDestination
     */
    public static function renderJsAddTrackingCode($idSource, $classDestination) {
        $s = array();
        $s[] = 'jQuery(document).ready(function() {';
        $s[] = '   var destGlobal 	= jQuery( \'.' . $classDestination . '\').val();';
        $s[] = '   var sourceGlobal	= jQuery(\'#' . $idSource . '\').val();';
        $s[] = '   jQuery( \'.' . $classDestination . '\').val(destGlobal + sourceGlobal);';

        $s[] = '   jQuery(\'#' . $idSource . '\').on("input", function() {';
        $s[] = '       var source	= jQuery(this).val();';
        $s[] = '       var text = destGlobal + source;';
        $s[] = '       jQuery( \'.' . $classDestination . '\').val(text);';
        $s[] = '   })';
        $s[] = '})';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
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
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }
    */

    public static function renderAjaxAskAQuestion($options = array()) {

        $paramsC = PhocacartUtils::getComponentParameters();
        $item_askquestion = $paramsC->get('item_askquestion', 0);
        $popup_askquestion = $paramsC->get('popup_askquestion', 0);

        if ($item_askquestion > 0 && $popup_askquestion == 2) {

            $s[] = ' jQuery(document).ready(function(){';
            $s[] = '	jQuery(document).on("click", "a.phModalContainerButton", function (e) {';
            $s[] = '      var src = jQuery(this).attr("data-src");';
            $s[] = '      var height = "100%";';//jQuery(this).attr("data-height") || 300;// Does not work and it is solved by CSS
            $s[] = '      var width = "100%";';//jQuery(this).attr("data-width") || 400;
            $s[] = '      var id = jQuery(this).attr("data-id");';
            $s[] = '      var idIframe = "#" + id + " iframe";';

            // Loaded dynamically to not have previous src in iframe, see: components/com_phocacart/layouts/popup_container_iframe.php
            $s[] = '      var idBody = "#" + id + " .modal-body";';
            $s[] = '      jQuery(idBody).html(\'<iframe frameborder="0"></iframe>\');';
            // end iframe could be a past of layout file

            $s[] = '      jQuery(idIframe).attr({"src":src, "height": height, "width": width});';
            //$s[] = '      jQuery(id).modal();';

        // Is loaded by HTML
        //    $s[] = '      var modal = new bootstrap.Modal(document.getElementById(id), {});';
        //    $s[] = '      modal.show();';


            $s[] = '   });';
            $s[] = ' });';

            Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
        }
    }

    public static function renderModalCommonIframeWindow($options = []) {

        $params         = PhocacartUtils::getComponentParameters();
        $p['theme']     = $params->get('theme', 'bs5');

        $s[] = ' jQuery(document).ready(function(){';
        $s[] = '	jQuery(document).on("click", "a.phModalContainerCommonIframeButton", function (e) {';
        $s[] = '      var src = jQuery(this).attr("data-src");';
        $s[] = '      var height = "100%";';//jQuery(this).attr("data-height") || 300;// Does not work and it is solved by CSS
        $s[] = '      var width = "100%";';//jQuery(this).attr("data-width") || 400;
        $s[] = '      var id = jQuery(this).attr("data-id");';
        $s[] = '      var idIframe = "#" + id + " iframe";';

        // Loaded dynamically to not have previous src in iframe, see: components/com_phocacart/layouts/popup_container_iframe.php

        if ($p['theme'] != 'uikit') {
            $s[] = '      var idBody = "#" + id + " .modal-body";';
        } else {
            $s[] = '      var idBody = "#" + id + " .uk-modal-body";';
        }

        $iframe = '      jQuery(idBody).html(\'<iframe frameborder="0"';

        if(isset($options['allow_geolocation']) && $options['allow_geolocation']) {
            $iframe .= ' allow="geolocation"';
        }
        $iframe .= ' ></iframe>\');';
        $s[] = $iframe;
        // end iframe could be a past of layout file

        $s[] = '      jQuery(idIframe).attr({"src":src, "height": height, "width": width});';

        /*if(isset($options['allow_geolocation']) && $options['allow_geolocation']) {

           $s[] = '       , "allow": "geolocation"';
        }*/

        //$s[] = '      });';
        //$s[] = '      jQuery(id).modal();';

    // Is loaded by HTML
    //    $s[] = '      var modal = new bootstrap.Modal(document.getElementById(id), {});';
    //    $s[] = '      modal.show();';


        $s[] = '   });';
        $s[] = ' });';

        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

    }

    public final function __clone()
    {
        throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
        return false;
    }
}

?>
