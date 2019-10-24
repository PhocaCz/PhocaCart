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


final class PhocacartRenderJspos
{
	private function __construct(){}

	/* =============
	 * POS
	 * =============
	 */


	/*
	 * Main Pos function
	 * ajax to render all pages in content area
	 * url ... current url
	 * outputDiv ... input box div (cart div is reloaded, main div is redirected)
	 */
	public static function managePos($url) {

		$paramsC 					= PhocacartUtils::getComponentParameters();
		$pos_focus_input_fields		= $paramsC ->get( 'pos_focus_input_fields', 0 );

		JFactory::getDocument()->addScript(JURI::root(true).'/media/com_phocacart/js/base64.js');

		$s 	= array();


		// ------------
		// FUNCTIONS
		// ------------

		/*
		 * Update input box after change
		 */
		$s[] = ' ';
		$s[] = '/* Function phDoSubmitFormUpdateInputBox */';
		$s[] = 'function phDoSubmitFormUpdateInputBox(sFormData, phUrlAjax) {';
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "POST",';
		$s[] = '      url: phUrlAjax,';
		//$s[] = '      async: false,';
		$s[] = '      async: true,';
		$s[] = '      cache: "false",';
		$s[] = '      data: sFormData,';
		$s[] = '      dataType:"HTML",';
		$s[] = '      success: function(data){';
		$s[] = '         jQuery("#phPosInputBox").html(data);';
		$s[] = '      }';
		$s[] = '   })';
		$s[] = '   return false;';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Update categories box after change (users can have different access rights for different catgories, so when selecting user, categories must be changed)
		 */
		$s[] = ' /* Function phDoSubmitFormUpdateCategoriesBox */';
		$s[] = 'function phDoSubmitFormUpdateCategoriesBox(sFormData, phUrlAjax) {';

		// Change categories only when customer changed
		$s[] = '   var page 	= jQuery("#phPosPaginationBox input[name=page]").val();';
		$s[] = '   if (page != "main.content.customers") {';
		$s[] = '      return false;';
		$s[] = '   }';
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "POST",';
		$s[] = '      url: phUrlAjax,';
		//$s[] = '      async: false,';
		$s[] = '      async: true,';
		$s[] = '      cache: "false",';
		$s[] = '      data: sFormData,';
		$s[] = '      dataType:"HTML",';
		$s[] = '      success: function(data){';
		$s[] = '         jQuery("#phPosCategoriesBox").html(data);';
		$s[] = '      }';
		$s[] = '   })';
		$s[] = '   return false;';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Main content box can be variable: products/customers/payment/shippment
		 * Get info about current ticket id and page (page: products, customers, payment, shipping)
		 *
		 */
		$s[] = '/* Function phPosCurrentData */';
		$s[] = 'function phPosCurrentData(forcepage, format, id) {';

		$s[] = '   if (typeof forcepage !== "undefined") {';
		$s[] = '      var page 	= forcepage;';
		$s[] = '   } else {';
		$s[] = '      var page 	= jQuery("#phPosPaginationBox input[name=page]").val();';
		$s[] = '   }';

		$s[] = '   if (typeof format !== "undefined") {';
		$s[] = '      var formatSuffix = format;';
		$s[] = '   } else {';
		$s[] = '      var formatSuffix = "raw";';
		$s[] = '   }';

		$s[] = '   if (typeof id !== "undefined") {';
		$s[] = '      var idSuffix 	= "&id="+id;';
		$s[] = '   } else {';
		$s[] = '      var idSuffix 	= "";';
		$s[] = '   }';

		$s[] = '   var ticketid	= jQuery("#phPosPaginationBox input[name=ticketid]").val();';
		$s[] = '   var unitid		= jQuery("#phPosPaginationBox input[name=unitid]").val();';
		$s[] = '   var sectionid	= jQuery("#phPosPaginationBox input[name=sectionid]").val();';
		$s[] = '   var phData		= "format=" + formatSuffix + "&tmpl=component&page=" + page + idSuffix +"&ticketid=" + ticketid + "&unitid=" + unitid + "&sectionid=" + sectionid + "&'. JSession::getFormToken().'=1";';
		$s[] = '   return phData;';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * When chaning main page, clear all filters (e.g. going from product list to customer list)
		 * Category - remove url parameters in url bar, then empty all checkboxes
		 * Search - remove url parameters in url bar, then empty search input field
		 */
		$s[] = '/* Function phPosClearFilter */';
		$s[] = 'function phPosClearFilter() {';
		$s[] = '   phUpdateUrlParameter("category", "");';
		$s[] = '   jQuery("input.phPosCategoryCheckbox:checkbox:checked").prop("checked", false);';
		$s[] = '   jQuery("label.phCheckBoxCategory").removeClass("active");';
		$s[] = '   phUpdateUrlParameter("search", "");';
		$s[] = '   jQuery("#phPosSearch").val("");';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Focus on form input if asked (sku, loyalty card, coupon, tendered amount)
		 */
		$s[] = '/* Function phPosManagePageFocus */';
		$s[] = 'function phPosManagePageFocus(page) {';

		if ($pos_focus_input_fields	== 1) {
			$s[] = '   if (page == "main.content.products") {';
			$s[] = '      var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '      if (!hasFocusSearch) {';
			$s[] = '         jQuery("#phPosSku").focus();';
			$s[] = '      }';
			$s[] = '   } else if (page == "main.content.customers") {';
			$s[] = '      var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '      if (!hasFocusSearch) {';
			$s[] = '         jQuery("#phPosCard").focus();';
			$s[] = '      }';
			$s[] = '   } else if (page == "main.content.paymentmethods") {';
			$s[] = '      var hasFocusSearch = jQuery("#phPosSearch").is(":focus");';
			$s[] = '      if (!hasFocusSearch) {';
			$s[] = '         jQuery("#phcoupon").focus();';
			$s[] = '      }';
			$s[] = '   } else if (page == "main.content.payment") {';
			$s[] = '      jQuery("#phAmountTendered").focus();';
			$s[] = '   }';
		} else {
			$s[] = '   return true;';
		}
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Manage view after ajax request (hide or display different parts on site)
		 * 1) Hide categories for another views than products
		 */
		$s[] = '/* Function phPosManagePage */';
		$s[] = 'function phPosManagePage() {';
		$s[] = '   var page = jQuery("#phPosPaginationBox input[name=page]").val();';

		// we use ajax and start parameter can be used for more items (products, customers, orders) so we cannot leave it in url
		// because if there are 100 products and 10 customers - switching to customers per ajax will leave e.g. 50 which is will display zero results
		// START IS SET ONLY WHEN CLICKING ON PAGINATION LINKS (see: renderSubmitPaginationTopFor, it is removed directly by click
		//$s[] = 'phUpdateUrlParameter("start", "");';


		$s[] = '   if (page == "main.content.products") {'; // PRODUCTS
		$s[] = '      jQuery(".ph-pos-checkbox-box").show();';
		$s[] = '      jQuery(".ph-pos-sku-product-box").show();';
		$s[] = '      jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '      jQuery(".ph-pos-search-box").show();';
		$s[] = '      jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '      phPosManagePageFocus(page);';// Focus on start
		$s[] = '	} else if (page == "main.content.customers") {'; // CUSTOMERS
		$s[] = '      jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '      jQuery(".ph-pos-search-box").show();';
		$s[] = '      jQuery(".ph-pos-card-user-box").show();';
		$s[] = '      jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '      jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '      phPosManagePageFocus(page);';// Focus on start
		$s[] = '   } else if (page == "main.content.order") {';// ORDER
		$s[] = '      jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '      jQuery(".ph-pos-search-box").hide();';
		$s[] = '      jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '      jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '      jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '   } else if (page == "main.content.orders") {'; // ORDERS
		$s[] = '      jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '      jQuery(".ph-pos-search-box").hide();';
		$s[] = '      jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '      jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '      jQuery(".ph-pos-date-order-box").show();';
		$s[] = '   } else if (page == "main.content.paymentmethods") {'; // PAYMENT METHODS
		$s[] = '   	  jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '   	  jQuery(".ph-pos-search-box").hide();';
		$s[] = '      jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '   	  phPosManagePageFocus(page);';// Focus on start
		$s[] = '   } else if (page == "main.content.payment") {'; // PAYMENT
		$s[] = '   	  jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '   	  jQuery(".ph-pos-search-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '      jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '      jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '      phPosManagePageFocus(page);';// Focus on start
		$s[] = '   } else {';
		$s[] = '   	  jQuery(".ph-pos-checkbox-box").hide();';//categories
		$s[] = '   	  jQuery(".ph-pos-search-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-card-user-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-sku-product-box").hide();';
		$s[] = '   	  jQuery(".ph-pos-date-order-box").hide();';
		$s[] = '   }';
		$s[] = '}';

		$s[] = ' ';

		// Declare it on start (event associated to phPosManagePage function
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   phPosManagePage();';
		$s[] = '})';

		$s[] = ' ';

		/*
		 * When adding new parameter to url bar, check if ? is there to set ? or &
		 */
		$s[] = '/* Function phAddSuffixToUrl */';
		$s[] = 'function phAddSuffixToUrl(action, suffix) {';
		$s[] = '   return action + (action.indexOf(\'?\') != -1 ? \'&\' : \'?\') + suffix;';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Edit something in main view and then reload cart, main page, input page
		 *
		 */
		$s[] = '/* Function phAjaxEditPos */';
		$s[] = 'function phAjaxEditPos(sFormData, phUrlAjax, forcepageSuccess, forcepageError) {';
		$s[] = '   var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '   var phDataInput = phPosCurrentData("main.input");';
		$s[] = '   var phDataCats  = phPosCurrentData("main.categories");';
		$s[] = '   var phDataCart 	= phPosCurrentData("main.cart", "json");';
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "POST",';
		$s[] = '      url: phUrlAjax,';
		//$s[] = '     async: false,';
		$s[] = '      async: true,';
		$s[] = '      cache: "false",';
		$s[] = '      data: sFormData,';
		$s[] = '      dataType:"JSON",';
		$s[] = '      success: function(data){';
		$s[] = '         if (data.status == 1){';
		//$s[] = '            jQuery("'.$outputDiv.'").html(data.item);';
		$s[] = '            if (data.id !== "undefined") {';
		$s[] = '               var id 	= data.id;';
		$s[] = '            } else {';
		$s[] = '               var id	= "";';
		$s[] = '            }';
		$s[] = '            var phDataMain 	= phPosCurrentData(forcepageSuccess, "raw", id);';
		$s[] = '            phDoSubmitFormUpdateCategoriesBox(phDataCats, phUrl);';// refresh categories box (when chaning users, users can have different access to categories)
		$s[] = '            phDoSubmitFormPaginationTop(phDataMain, phUrl);';// reload main box to default (list of products)
		$s[] = '            phDoSubmitFormUpdateInputBox(phDataInput, phUrl);';// refresh input box
		$s[] = '            phDoSubmitFormUpdateCart(phDataCart);';// reload updated cart
		$s[] = '            jQuery(".ph-pos-message-box").html(data.message);';
		$s[] = '         } else if (data.status == 0){';
		$s[] = '            var phDataMain 	= phPosCurrentData(forcepageError);';
		$s[] = '            phDoSubmitFormPaginationTop(phDataMain, phUrl);';// reload main box to default (list of products)
		$s[] = '            phDoSubmitFormUpdateInputBox(phDataInput, phUrl);';// refresh input box
		$s[] = '            phDoSubmitFormUpdateCart(phDataCart);';// reload updated cart
		$s[] = '            jQuery(".ph-pos-message-box").html(data.error);';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   })';
		$s[] = '   return false;';
		$s[] = '}';

		$s[] = ' ';


		// ------------
		// EVENTS
		// ------------
		$s[] = 'jQuery(document).ready(function(){';

		$s[] = ' ';
		/*
		 * Clear form input after submit - for example, if vendor add products per
		 * bar scanner, after scanning the field must be empty for new product scan
		 * PRODUCTS, LOYALTY CARD
		 */
	    $s[] = 'jQuery(document).on("submit","#phPosSkuProductForm",function(){';
	   // $s[] = '   jQuery("#phPosSku").val("");';
	   // $s[] = '   e.preventDefault();';
	   // $s[] = '   this.submit();';
	    $s[] = '   setTimeout(function(){';
	    $s[] = '      jQuery("#phPosSku").val("");';
	    $s[] = '   }, 100);';
	    $s[] = '});';

	    $s[] = ' ';

	    $s[] = 'jQuery(document).on("submit","#phPosCardUserForm",function(){';
	   // $s[] = '   jQuery("#phPosSku").val("");';
	   // $s[] = '   e.preventDefault();';
	   // $s[] = '   this.submit();';
	    $s[] = '   setTimeout(function(){';
	    $s[] = '      jQuery("#phPosCard").val("");';
	    $s[] = '   }, 100);';
	    $s[] = '});';

		$s[] = ' ';
		/*
		 * Test if Bootstrap JS is loaded more than once
		 * This is important because of toggle buttons in select/checkboxes
		 * Toggle can be switched more than one time because of loaded instances of Bootstrap JS
		 */
		$s[] = '/* Test Bootstrap JS libraries */';
		$s[] = 'var phScriptsLoaded = document.getElementsByTagName("script");';
		$s[] = 'var bMinJs = "bootstrap.min.js";';
		$s[] = 'var bJs = "bootstrap.js";';
		$s[] = 'var bJsCount = 0;';

		$s[] = ' ';

		$s[] = 'jQuery.each(phScriptsLoaded, function (k, v) {';
		$s[] = '   var s = v.src;';
		$s[] = '   var n = s.indexOf("?")';
		$s[] = '   s = s.substring(0, n != -1 ? n : s.length);';
		$s[] = '   var filename = s.split(\'\\\\\').pop().split(\'/\').pop();';
		$s[] = '   if (filename == bMinJs || filename == bJs) {';
		$s[] = '      bJsCount++;';
		$s[] = '   }';
		$s[] = '})';

		$s[] = 'if (bJsCount > 1){';
		$s[] = '   jQuery("#phPosWarningMsgBox").text("'.JText::_('COM_PHOCACART_WARNING_BOOTSTRAP_JS_LOADED_MORE_THAN_ONCE').'");';
		$s[] = '   jQuery("#phPosWarningMsgBox").show();';
		$s[] = '}';

		$s[] = ' ';

		/*
		 * Load main content by links - e.g. in input box we call list of customers, payment methods or shipping methods
		 */
		$s[] = '/* Event loadMainContent */';
		$s[] = 'jQuery(document).on("click", ".loadMainContent", function (e) {';
		$s[] = '   phPosClearFilter();';
		$s[] = '   var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '   var sForm 		= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '   var sFormData 	= sForm.serialize();';
		$s[] = '   phDoSubmitFormPaginationTop(sFormData, phUrl);';
		$s[] = '   jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '      e.preventDefault();';
		$s[] = '})';

		$s[] = ' ';

		/*
		 * Edit something in content area (e.g. customer list is loaded in main content and we change it)
		 */
		$s[] = '/* Event editMainContent */';
		$s[] = 'jQuery(document).on("click", ".editMainContent", function (e) {';
		$s[] = '   phPosClearFilter();';
		$s[] = '   var phUrl 				= phAddSuffixToUrl(window.location.href, \'format=json\');';
		$s[] = '   var sForm				= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '   var sFormData			= sForm.serialize();';
		$s[] = '   var phRedirectSuccess 	= sForm.find(\'input[name="redirectsuccess"]\').val();';
		$s[] = '   var phRedirectError 	= sForm.find(\'input[name="redirecterror"]\').val();';
		$s[] = '   phAjaxEditPos(sFormData, phUrl, phRedirectSuccess, phRedirectError);';
		$s[] = '   jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '   e.preventDefault();';
		$s[] = '})';

		$s[] = ' ';

		// Unfortunately we have form without buttons so we need to run the form without click too
		// to not submit more forms at once we will use ID :-(
		$s[] = '/* Event phPosCardUserForm */';
		$s[] = 'jQuery(document).on("submit", "#phPosCardUserForm", function (e) {';
		$s[] = '   phPosClearFilter();';
		$s[] = '   var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=json\');';
		$s[] = '   var sForm		= jQuery("#phPosCardUserForm");';
		$s[] = '   var sFormData	= sForm.serialize();';
		$s[] = '   phAjaxEditPos(sFormData, phUrl, "main.content.products", "main.content.products");';
		$s[] = '   jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '      e.preventDefault();';
		$s[] = '})';

		$s[] = ' ';

		$s[] = '/* Event phPosDateOrdersForm */';
		$s[] = 'jQuery(document).on("submit", "#phPosDateOrdersForm", function (e) {';
		$s[] = '   phPosClearFilter();';
		$s[] = '   var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '   var sForm		= jQuery("#phPosDateOrdersForm");';
		$s[] = '   var sFormData	= sForm.serialize();';
		//$s[] = '   var phDataMain 	= phPosCurrentData();';
		$s[] = '   phDoSubmitFormPaginationTop(sFormData, phUrl);';// reload main box to default (list of products)
		$s[] = '   jQuery(".ph-pos-message-box").html("");';// clean message box
		$s[] = '      e.preventDefault();';
		$s[] = '})';

		$s[] = ' ';

		/*
		 * Display warning when closing a ticket
		 */
		$s[] = '/* Event phPosCloseTicketForm */';
		$s[] = 'phPosCloseTicketFormConfirmed = false;';
		$s[] = 'jQuery(document).on("submit", "#phPosCloseTicketForm", function (e) {';
		$s[] = '   var txt = jQuery(this).data("txt");';
		$s[] = '   if(!phPosCloseTicketFormConfirmed) {';
		//$s[] = '      var phData = jQuery(this).serialize();'	;
		$s[] = '      phConfirm("#phPosCloseTicketForm", "", txt);';
		$s[] = '      e.preventDefault();';
		$s[] = '      return false;';
		$s[] = '   } else {';
		$s[] = '      phPosCloseTicketFormConfirmed = false;';// set back the variable
		$s[] = '      return true;';
		$s[] = '   }';
		$s[] = '})';

		$s[] = ' ';

		$s[] = '})';// end document ready
		$s[] = ' ';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}



	/*
	 * Search by key type - typing of charcters into the search field
	 *
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 */
	public static function searchPosByType($id) {

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phFindMemer*/';
		$s[] = 'function phFindMember(typeValue) {';
		$s[] = '   var phData 	= "search=" + typeValue + "&" + phPosCurrentData();';
		$s[] = '   phUpdateUrlParameter("search", typeValue);';
		$s[] = '   var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';//get the url after update
		$s[] = '   phDoSubmitFormPaginationTop(phData, phUrl);';
		$s[] = '   jQuery(".ph-pos-message-box").html("");';// clear message box
		$s[] = '}';

		$s[] = ' ';

		$s[] = '/* Event phFindMemer Keyuup */';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var phThread = null;';
	    $s[] = '   jQuery("'.$id.'").keyup(function() {';
	    $s[] = '      clearTimeout(phThread);';
	    $s[] = '      var $this = jQuery(this); phThread = setTimeout(function(){phFindMember($this.val())}, 800);';
	    $s[] = '   });';
		$s[] = '})';

		$s[] = ' ';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}




	/*
	 * Get all checkboxes of categories which are active and add them to url bar and filter the categories
	 *
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 *
	 * Test checkbox
	 * components\com_phocacart\views\pos\tmpl\default_main_categories.php
	 * data-toggle="buttons" - changes the standard checkbox to graphical checkbox
	 *
	 */
	public static function searchPosByCategory() {

		$paramsC 					= PhocacartUtils::getComponentParameters();
		$pos_filter_category		= $paramsC ->get( 'pos_filter_category', 1 );// reload equal height

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Event phPosCategoryCheckbox */';
		$s[] = 'jQuery(document).ready(function(){';

		//$s[] = '   jQuery("#phPosCategoriesBox .phPosCategoryCheckbox").off().on("change", function() {';// categories box is loaded newly, document needed
		$s[] = '   jQuery(document).on("change", "#phPosCategoriesBox .phPosCategoryCheckbox", function() {';

		if ($pos_filter_category == 2) {

			// Multiple categories can be displayed - can be active
			$s[] = '      var phA = [];';
			$s[] = '      jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {';
			$s[] = '         phA.push(jQuery(this).val());';
			$s[] = '      })';
			$s[] = '      var cValue = phA.join(",");';
		} else {
			// Only one category can be displayed
			// Deselect all checkboxed except the one selected - can be active
			$s[] = '      var cValue = jQuery(this).val();';
			$s[] = '      jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {';
			$s[] = '         if (cValue != jQuery(this).val() ) {';
			$s[] = '            jQuery(this).prop("checked", false);';
			$s[] = '            jQuery("label.phCheckBoxCategory").removeClass("active");';
			$s[] = '         }';
			$s[] = '      })';

			// Current checkbox was deselected
			$s[] = '      if (jQuery(this).prop("checked") == false) {';
			$s[] = '         cValue = "";';
			$s[] = '      }; ';
		}


		$s[] = ' ';

		$s[] = '      var phData 	= "category=" + cValue + "&" + phPosCurrentData();';
		$s[] = '      phUpdateUrlParameter("category", cValue);';// update URL bar
		//$s[] = '      var phUrl = phUpdateUrlParameter("category", cValue, phUrl);';// Update phUrl - it is a form url which is taken by joomla to create pagination links
		$s[] = '      var phUrl 	= phAddSuffixToUrl(window.location.href, \'format=raw\');';// get the link after update of url bar
		$s[] = '      phDoSubmitFormPaginationTop(phData, phUrl);';
		$s[] = '      jQuery(".ph-pos-message-box").html("");';// clear message box
		$s[] = '   });';
		$s[] = '})';

		$s[] = ' ';

		/*$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(document).on("click", ".mainBoxCategory", function (e) {';
		$s[] = '        var phUrl 		= phAddSuffixToUrl(window.location.href, \'format=raw\');';
		$s[] = '	    var sForm 		= jQuery(this).closest("form");';// Find in which form the right button was clicked
		$s[] = '	    var sFormData 	= sForm.serialize()';
		$s[] = '		phDoSubmitFormPaginationTop(sFormData, phUrl);';
		$s[] = '		e.preventDefault();';
		$s[] = '	})';
		$s[] = '})';*/

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}



	public static function changeUrlParameter($params) {

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phRemoveUrlParameter */';
		$s[] = 'function phRemoveUrlParameter(param, url) {';
		$s[] = '   var rtn = url.split("?")[0],';
        $s[] = '   param,';
        $s[] = '   params_arr = [],';
        $s[] = '   queryString = (url.indexOf("?") !== -1) ? url.split("?")[1] : "";';
        $s[] = '   if (queryString !== "") {';
        $s[] = '      params_arr = queryString.split("&");';
        $s[] = '      for (var i = params_arr.length - 1; i >= 0; i -= 1) {';
        $s[] = '         paramV = params_arr[i].split("=")[0];';
        $s[] = '         if (paramV === param) {';
        $s[] = '            params_arr.splice(i, 1);';
        $s[] = '         }';
        $s[] = '      }';
        $s[] = '      rtn = rtn + "?" + params_arr.join("&");';
        $s[] = '   }';
        $s[] = '   return rtn;';
	    $s[] = '}';

		$s[] = ' ';

		$s[] = '/* Function phRemoveUrlParameter */';
		$s[] = 'function phUpdateUrlParameter(param, value, urlChange) {';
		$s[] = '   if (typeof urlChange !== "undefined") {';
		$s[] = '      var url =  urlChange;';
		$s[] = '      var urlA =  url.split("#");';
	    $s[] = '      var hash =  ""';
	    $s[] = '      if(urlA.length > 1) { hash = urlA[1];}';
	    $s[] = '   } else {';
	    $s[] = '      var url = window.location.href;';
	    $s[] = '      var hash = location.hash;';
	    $s[] = '   }';

	    $s[] = '   url = url.replace(hash, \'\');';
	    $s[] = '   if (url.indexOf(param + "=") >= 0) {';
	    $s[] = '      var prefix = url.substring(0, url.indexOf(param));';
	    $s[] = '      var suffix = url.substring(url.indexOf(param));';
	    $s[] = '      suffix = suffix.substring(suffix.indexOf("=") + 1);';
	    $s[] = '      suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";';
	    $s[] = '      url = prefix + param + "=" + value + suffix;';
	    $s[] = '   } else {';
	    $s[] = '      if (url.indexOf("?") < 0) {';
	    $s[] = '         url += "?" + param + "=" + value;';
	    $s[] = '      } else {';
	    $s[] = '         url += "&" + param + "=" + value;';
	    $s[] = '      }';
	    $s[] = '   }';
	    $s[] = '   url = url.replace(/[^=&]+=(&|$)/g,"").replace(/&$/,"");';// remove all parameters with empty values

	    $s[] = '   if (typeof urlChange !== "undefined") {';
	    $s[] = '      return (url + hash);';
	    $s[] = '   } else {';
	    $s[] = '      window.history.pushState(null, null, url + hash);';
	    $s[] = '   }';
	    $s[] = '}';

	    $s[] = ' ';

	    if (!empty($params)) {

			$s[] = 'jQuery(document).ready(function(){';
			foreach($params as $k => $v) {
				$s[] = '   phUpdateUrlParameter("'.$k.'", '.(int)$v.');';
			}
			$s[] = '})';

			$s[] = ' ';
	    }

	    JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}


	/*
	 * Print to POS printer
	 */
	public static function printPos($url) {

		$paramsC 			= PhocacartUtils::getComponentParameters();
		$pos_server_print	= $paramsC->get( 'pos_server_print', 0 );


		$s 	 = array();
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function(){';

		$s[] = ' ';

		$s[] = '/* Event phPosContentBox */';
		$s[] = 'jQuery("#phPosContentBox").on("click", ".phOrderPrintBtn", function (e) {';
		$s[] = '   var phUrlAjax 		= "'.$url.'"';
		$s[] = '   var phOrder	 		= jQuery(this).data("order");';
		$s[] = '   var phType	 		= jQuery(this).data("type");';
		$s[] = '   var phOrderCurrent	= jQuery("#phPosOrderPrintBox").attr("data-order");';// data("order"); not working
		$s[] = '   var phTypeCurrent	= jQuery("#phPosOrderPrintBox").attr("data-type");';// data("type"); not working

		$s[] = ' ';

		// PC PRINT
		$s[] = '   if (phType == "-1") {';// -1 type is print (1 order, 2 invoice, 3 delivery note, 4 receipt)

		if ($pos_server_print == 2 || $pos_server_print == 3) {
			// - 1 AND 4 PC PRINT FOR ALL DOCUMENTS EXCEPT 4 (Receipt) - Receipt will be printend by SERVER PRINT
			$s[] = '      if (phTypeCurrent == "4") {';
			$s[] = '         var phUrlAjaxPrint = phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phTypeCurrent + "&pos=1&printserver=1");';
			$s[] = '         phRequestPrint = jQuery.ajax({';
			$s[] = '            type: "GET",';
			$s[] = '            url: phUrlAjaxPrint,';
			$s[] = '            async: true,';
			$s[] = '            cache: "false",';
			$s[] = '            dataType:"HTML",';
			$s[] = '            success: function(data){';
			$s[] = '               jQuery(".ph-pos-message-box").html(\'<div>\' + data + \'</div>\');';
			//$s[] = '               jQuery("#phPosOrderPrintBox").attr("class", phClass);';// Add class to box of document - to differentiate documents loaded by ajax
			//$s[] = '               jQuery("#phPosOrderPrintBox").attr("data-type", phType);';// Add data type to box of document - so it can be read by print function
			//$s[] = '               jQuery("#phPosOrderPrintBox").html(data);';// Add the document itself to the site
			$s[] = '            }';
			$s[] = '         })';
			$s[] = '         e.preventDefault();';
			$s[] = '         return false;';
			// -1 PC PRINT
			$s[] = '      } else {';
			$s[] = '         window.print(); return false;';// print with javascript for all documents except receipt (receipt is ready for server POS printers)
			$s[] = '      }';

		} else {
			$s[] = '      window.print(); return false;';// print with javascript for all document (including receipt)
		}
		$s[] = '   }';

		$s[] = ' ';

		// DISPLAYING THE DOCUMENT
		$s[] = '   var phClass 	= "phType" + phType;';
		$s[] = '   var phUrlAjax 	= phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phType + "&pos=1");';
		$s[] = '   phRequest = jQuery.ajax({';
		$s[] = '      type: "GET",';
		$s[] = '      url: phUrlAjax,';
		$s[] = '      async: true,';
		$s[] = '      cache: "false",';
		$s[] = '      dataType:"HTML",';
		$s[] = '      success: function(data){';
		$s[] = '         jQuery("#phPosOrderPrintBox").attr("class", phClass);';// Add class to box of document - to differentiate documents loaded by ajax
		$s[] = '         jQuery("#phPosOrderPrintBox").attr("data-type", phType);';// Add data type to box of document - so it can be read by print function
		$s[] = '         jQuery("#phPosOrderPrintBox").html(data);';// Add the document itself to the site
		$s[] = '      }';
		$s[] = '   })';


		$s[] = '   e.preventDefault();';
		$s[] = '})';

		$s[] = ' ';

		$s[] = '})';// end document ready

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	/* POS Scroll cart */

	public static function renderJsScrollToPos() {

		$s = array();
		$s[] = ' ';
		$s[] = '/* Function phScrollPosCart */';
		$s[] = 'function phScrollPosCart(phPosCart) {';
		$s[] = '   if (jQuery("#ph-msg-ns").length > 0){';
		$s[] = '      phPosCart.animate({scrollTop: 0}, 1500 );';
		$s[] = '   } else {';
		$s[] = '      var phPosCartHeight = phPosCart[0].scrollHeight;';
		$s[] = '      phPosCart.animate({scrollTop: phPosCartHeight}, 1500 );';
		$s[] = '   }';
		$s[] = '}';

		$s[] = ' ';

		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var phPosCart 		= jQuery(\'#phPosCart\');';
		$s[] = '   phScrollPosCart(phPosCart);';//  On start
		$s[] = '   phPosCart.on("DOMSubtreeModified", function(){';// On modified
		$s[] = '      if (phPosCart.text() != \'\') {';// this event runs twice - first when jquery empty the object, second when it fills it again
		$s[] = '         phScrollPosCart(phPosCart);';// run only on second when it fills the object
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '});';

		$s[] = ' ';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}




	// ========
	// JQUERY UI
	// ========
	public static function renderJsUi() {

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phSetCloseButton */';
		$s[] = 'function phSetCloseButton() {';
		$s[] = '   jQuery(".ui-dialog-titlebar-close").html(\'<span class="ph-close-button ui-button-icon-primary ui-icon ui-icon-closethick"></span>\');';
		$s[] = '}';

		$s[] = ' ';

		$s[] = 'function phConfirm(submitForm, dataPost, txt) {';
		$s[] = '   jQuery("#phDialogConfirm" ).html( txt );';
		$s[] = '   jQuery("#phDialogConfirm").dialog({';
		$s[] = '      autoOpen: false,';
		$s[] = '      modal: true,';
		$s[] = '      buttons: {';
		$s[] = '         "'. JText::_('COM_PHOCACART_OK').'": function() {';
		$s[] = '            jQuery(this).dialog("close");';
        $s[] = '            phPosCloseTicketFormConfirmed = true;';
		$s[] = '            if (submitForm != "") {';
		$s[] = '               jQuery(submitForm).submit();';
		$s[] = '            } else if (typeof dataPost !== "undefined" && dataPost != "") {';
		$s[] = '               //phDoRequest(dataPost);';
		$s[] = '            }';
		$s[] = '            return true;';
		$s[] = '         },';
		$s[] = '         "'.  JText::_('COM_PHOCACART_CANCEL').'": function() {';
		$s[] = '            jQuery(this).dialog("close");';
		$s[] = '            return false;';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   })';
		$s[] = '   jQuery( "#phDialogConfirm" ).dialog( "open" );';
		$s[] = '   phSetCloseButton();/* Correct class */';
		$s[] = '   jQuery("button").addClass("btn btn-default");';
		$s[] = '}';
		$s[] = ' ';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
} ?>
