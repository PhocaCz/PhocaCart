/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/* Update parameter */
function phUpdateUrlParameter(param, value, urlChange) {
	if (typeof urlChange !== "undefined") {
	   var url =  urlChange;
	   var urlA =  url.split("#");
	   var hash =  ""
	   if(urlA.length > 1) { hash = urlA[1];}
	} else {
	   var url = window.location.href;
	   var hash = location.hash;
	}

	url = url.replace(hash, '');
	if (url.indexOf(param + "=") >= 0) {
	   var prefix = url.substring(0, url.indexOf(param));
	   var suffix = url.substring(url.indexOf(param));
	   suffix = suffix.substring(suffix.indexOf("=") + 1);
	   suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
	   url = prefix + param + "=" + value + suffix;
	} else {
	   if (url.indexOf("?") < 0) {
		  url += "?" + param + "=" + value;
	   } else {
		  url += "&" + param + "=" + value;
	   }
	}
	url = url.replace(/[^=&]+=(&|$)/g,"").replace(/&$/,"");// remove all parameters with empty values

	if (typeof urlChange !== "undefined") {
	   return (url + hash);
	} else {
	   window.history.pushState(null, null, url + hash);
	}
}

/* Update input box after change */
function phDoSubmitFormUpdateInputBox(sFormData, phUrlAjax) {

	jQuery.ajax({
	   type: "POST",
	   url: phUrlAjax,
	   async: true,
	   cache: "false",
	   data: sFormData,
	   dataType:"HTML",
	   success: function(data){
		  jQuery("#phPosInputBox").html(data);
	   }
	})
	return false;
 }

/* Update categories box after change (users can have different access rights for different categories, so when selecting user, categories must be changed) */
function phDoSubmitFormUpdateCategoriesBox(sFormData, phUrlAjax) {

	// Change categories only when customer changed
	var page 	= jQuery("#phPosPaginationBox input[name=page]").val();
	if (page != "main.content.customers") {
		return false;
	}

	jQuery.ajax({
		type: "POST",
		url: phUrlAjax,
		async: true,
		cache: "false",
		data: sFormData,
		dataType:"HTML",
		success: function(data){
			jQuery("#phPosCategoriesBox").html(data);
		}
	})
	return false;
}

/* Main content box can be variable: products/customers/payment/shippment
 * Get info about current ticket id and page (page: products, customers, payment, shipping)
 */
function phPosCurrentData(forcepage, format, id) {

	var phVars 	= Joomla.getOptions('phVarsPC');
	var phToken = phVars['token'];

	if (typeof forcepage !== "undefined") {
	   var page 	= forcepage;
	} else {
	   var page 	= jQuery("#phPosPaginationBox input[name=page]").val();
	}

	if (typeof format !== "undefined") {
	   var formatSuffix = format;
	} else {
	   var formatSuffix = "raw";
	}

	if (typeof id !== "undefined") {
	   var idSuffix 	= "&id="+id;
	} else {
	   var idSuffix 	= "";
	}

	var ticketid	= jQuery("#phPosPaginationBox input[name=ticketid]").val();
	var unitid		= jQuery("#phPosPaginationBox input[name=unitid]").val();
	var sectionid	= jQuery("#phPosPaginationBox input[name=sectionid]").val();
	var phData		= "format=" + formatSuffix + "&tmpl=component&page=" + page + idSuffix +"&ticketid=" + ticketid + "&unitid=" + unitid + "&sectionid=" + sectionid + "&" + phToken + "=1";

	return phData;
}

/*
* When chaning main page, clear all filters (e.g. going from product list to customer list)
* Category - remove url parameters in url bar, then empty all checkboxes
* Search - remove url parameters in url bar, then empty search input field
*/
function phPosClearFilter() {
	phUpdateUrlParameter("category", "");
	jQuery("input.phPosCategoryCheckbox:checkbox:checked").prop("checked", false);
	jQuery("label.phCheckBoxCategory").removeClass("active");
	phUpdateUrlParameter("search", "");
	jQuery("#phPosSearch").val("");
}

/* Focus on form input if asked (sku, loyalty card, coupon, tendered amount) */
function phPosManagePageFocus(page) {

	var phParams 	= Joomla.getOptions('phParamsPC');
	var posFocusInputFields = phParams['posFocusInputFields'];

	if (posFocusInputFields == 1) {
		if (page == "main.content.products") {
			var hasFocusSearch = jQuery("#phPosSearch").is(":focus");
			if (!hasFocusSearch) {
				jQuery("#phPosSku").focus();
			}
		} else if (page == "main.content.customers") {
			var hasFocusSearch = jQuery("#phPosSearch").is(":focus");
			if (!hasFocusSearch) {
				jQuery("#phPosCard").focus();
			}
		} else if (page == "main.content.paymentmethods") {
			var hasFocusSearch = jQuery("#phPosSearch").is(":focus");
			if (!hasFocusSearch) {
				jQuery("#phcoupon").focus();
			}
		} else if (page == "main.content.applybenefits") {
			var hasFocusSearch = jQuery("#phPosSearch").is(":focus");
			if (!hasFocusSearch) {
				jQuery("#phcoupon").focus();
			}
		} else if (page == "main.content.payment") {
			jQuery("#phAmountTendered").focus();
		}
	} else {
		return true;
	}
}

/*
 * Manage view after ajax request (hide or display different parts on site)
 * 1) Hide categories for another views than products
 * we use ajax and start parameter can be used for more items (products, customers, orders) so we cannot leave it in url
 * because if there are 100 products and 10 customers - switching to customers per ajax will leave e.g. 50 which is will display zero results
 * START IS SET ONLY WHEN CLICKING ON PAGINATION LINKS (see: renderSubmitPaginationTopFor, it is removed directly by click)
*/

function phPosManagePage() {
	var page = jQuery("#phPosPaginationBox input[name=page]").val();

	if (page == "main.content.products") { // PRODUCTS
		jQuery(".ph-pos-checkbox-box").show();
		jQuery(".ph-pos-sku-product-box").show();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-search-box").show();
		jQuery(".ph-pos-date-order-box").hide();
		phPosManagePageFocus(page);// Focus on start
	} else if (page == "main.content.customers") { // CUSTOMERS
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").show();
		jQuery(".ph-pos-card-user-box").show();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
		phPosManagePageFocus(page);// Focus on start
	} else if (page == "main.content.order") {// ORDER
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
	} else if (page == "main.content.orders") { // ORDERS
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").show();
	} else if (page == "main.content.paymentmethods") { // PAYMENT METHODS
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
		phPosManagePageFocus(page);// Focus on start
	} else if (page == "main.content.applybenefits") { // Apply Coupon
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
		phPosManagePageFocus(page);// Focus on start
	} else if (page == "main.content.payment") { // PAYMENT
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
		phPosManagePageFocus(page);// Focus on start
	} else {
		jQuery(".ph-pos-checkbox-box").hide();//categories
		jQuery(".ph-pos-search-box").hide();
		jQuery(".ph-pos-card-user-box").hide();
		jQuery(".ph-pos-sku-product-box").hide();
		jQuery(".ph-pos-date-order-box").hide();
	}
}

/* When adding new parameter to url bar, check if ? is there to set ? or & */
function phAddSuffixToUrl(action, suffix) {
	return action + (action.indexOf('?') != -1 ? '&' : '?') + suffix;
}

/* Edit something in main view and then reload cart, main page, input page */
function phAjaxEditPos(sFormData, phUrlAjax, forcepageSuccess, forcepageError) {
	var phUrl 		= phAddSuffixToUrl(window.location.href, 'format=raw');
	var phDataInput = phPosCurrentData("main.input");
	var phDataCats  = phPosCurrentData("main.categories");
	var phDataCart 	= phPosCurrentData("main.cart", "json");
	jQuery.ajax({
		type: "POST",
		url: phUrlAjax,
		async: true,
		cache: "false",
		data: sFormData,
		dataType:"JSON",
		success: function(data){
			if (data.status == 1){
				if (data.id !== "undefined") {
					var id 	= data.id;
				} else {
					var id	= "";
				}
				var phDataMain 	= phPosCurrentData(forcepageSuccess, "raw", id);
				phDoSubmitFormUpdateCategoriesBox(phDataCats, phUrl);// refresh categories box (when chaning users, users can have different access to categories)
				phRenderPage(phDataMain, phUrl);// reload main box to default (list of products)
				phDoSubmitFormUpdateInputBox(phDataInput, phUrl);// refresh input box
				phDoSubmitFormUpdateCart(phDataCart);// reload updated cart
				jQuery(".ph-pos-message-box").html(data.message);
			} else if (data.status == 0){
				var phDataMain 	= phPosCurrentData(forcepageError);
				phRenderPage(phDataMain, phUrl);// reload main box to default (list of products)
				phDoSubmitFormUpdateInputBox(phDataInput, phUrl);// refresh input box
				phDoSubmitFormUpdateCart(phDataCart);// reload updated cart
				jQuery(".ph-pos-message-box").html(data.error);
			}
		}
	})
	return false;
}

/*
* Search by key type - typing of charcters into the search field FIND MEMBER FUNCTION
*
* Must be loaded:
* renderSubmitPaginationTopForm()
* changeUrlParameter()
* editPos()
*/
function phFindMember(typeValue) {
	var phData 	= "search=" + typeValue + "&" + phPosCurrentData();
	phUpdateUrlParameter("search", typeValue);
	var phUrl 	= phAddSuffixToUrl(window.location.href, 'format=raw');//get the url after update
	phRenderPage(phData, phUrl);
	jQuery(".ph-pos-message-box").html("");// clear message box
}


/* POS Scroll cart */
function phScrollPosCart(phPosCart) {
	if (jQuery("#ph-msg-ns").length > 0){
	   phPosCart.animate({scrollTop: 0}, 1500 );
	} else {
	   var phPosCartHeight = phPosCart[0].scrollHeight;
	   phPosCart.animate({scrollTop: phPosCartHeight}, 1500 );
	}
}

function phConfirm(submitForm, dataPost, txt) {

	//var phLang 	= Joomla.getOptions('phLangPC');
	//var phLangOk = phLang['COM_PHOCACART_OK'];
	//var phLangCancel = phLang['COM_PHOCACART_CANCEL'];


	jQuery("#phDialogConfirm .modal-body" ).html( txt );

	var modal = new bootstrap.Modal(document.getElementById("phDialogConfirm"), { keyboard: false });
	modal.show();

	//jQuery('#phDialogConfirm').modal();
	//jQuery('#phDialogConfirm').modal({ keyboard: false });
	//jQuery('#phDialogConfirm').modal('show') ;



	jQuery("#phDialogConfirmSave").on("click", function(e){
		phPosCloseTicketFormConfirmed = true;
		if (submitForm != "") {
			jQuery(submitForm).submit();
		} else if (typeof dataPost !== "undefined" && dataPost != "") {
			//phDoRequest(dataPost);
		}
		return true;
	});

	return false;
}


var phPosCloseTicketFormConfirmed = false;

// ------
// Events
// ------
jQuery(document).ready(function(){

	/* Declare it on start (event associated to phPosManagePage function) */
	phPosManagePage();

	/*
	* Clear form input after submit - for example, if vendor add products per
	* bar scanner, after scanning the field must be empty for new product scan
	* PRODUCTS, LOYALTY CARD
	*/
	jQuery(document).on("submit","#phPosSkuProductForm",function(){
	    setTimeout(function(){
	    	jQuery("#phPosSku").val("");
	    }, 100);
	});

	jQuery(document).on("submit","#phPosCardUserForm",function(){
	    setTimeout(function(){
	    	jQuery("#phPosCard").val("");
	    }, 100);
	});

	/* Test Bootstrap JS libraries */
	var phLang = Joomla.getOptions('phLangPC');
	var phScriptsLoaded = document.getElementsByTagName("script");
	var bMinJs = "bootstrap.min.js";
	var bJs = "bootstrap.js";
	var bJsCount = 0;

	jQuery.each(phScriptsLoaded, function (k, v) {
		var s = v.src;
		var n = s.indexOf("?")
		s = s.substring(0, n != -1 ? n : s.length);
		var filename = s.split('\\\\').pop().split('/').pop();
		if (filename == bMinJs || filename == bJs) {
		   bJsCount++;
		}
	 })

	 if (bJsCount > 1){
		jQuery("#phPosWarningMsgBox").text(phLang['COM_PHOCACART_WARNING_BOOTSTRAP_JS_LOADED_MORE_THAN_ONCE']);
		jQuery("#phPosWarningMsgBox").show();
	 }

	 // Close Metismenu when button iside Metismenu is clicked and no reload (ajax)
	 /*jQuery(document).on("click", ".closeMM", function (e) {
		jQuery(".phPOSMMButton").trigger("click");
	 })*/

	 /* Load main content by links - e.g. in input box we call list of customers, payment methods or shipping methods */
	jQuery(document).on("click", ".loadMainContent", function (e) {
		phPosClearFilter();
		var phUrl 		= phAddSuffixToUrl(window.location.href, 'format=raw');
		var sForm 		= jQuery(this).closest("form");// Find in which form the right button was clicked
		var sFormData 	= sForm.serialize();
		phRenderPage(sFormData, phUrl);
		jQuery(".ph-pos-message-box").html("");// clean message box
		e.preventDefault();
	});

	/* Edit something in content area (e.g. customer list is loaded in main content and we change it) */
	jQuery(document).on("click", ".editMainContent", function (e) {
		phPosClearFilter();
		var phUrl 				= phAddSuffixToUrl(window.location.href, 'format=json');
		var sForm				= jQuery(this).closest("form");// Find in which form the right button was clicked
		var sFormData			= sForm.serialize();
		var phRedirectSuccess 	= sForm.find('input[name="redirectsuccess"]').val();
		var phRedirectError 	= sForm.find('input[name="redirecterror"]').val();
		phAjaxEditPos(sFormData, phUrl, phRedirectSuccess, phRedirectError);
		jQuery(".ph-pos-message-box").html("");// clean message box
		e.preventDefault();
	});

	/*
	* Unfortunately we have form without buttons so we need to run the form without click too
    * to not submit more forms at once we will use ID :-(
	*/
	jQuery(document).on("submit", "#phPosCardUserForm", function (e) {
		phPosClearFilter();
		var phUrl 		= phAddSuffixToUrl(window.location.href, 'format=json');
		var sForm		= jQuery("#phPosCardUserForm");
		var sFormData	= sForm.serialize();
		phAjaxEditPos(sFormData, phUrl, "main.content.products", "main.content.products");
		jQuery(".ph-pos-message-box").html("");// clean message box
		e.preventDefault();
	});

	jQuery(document).on("submit", "#phPosDateOrdersForm", function (e) {
		phPosClearFilter();
		var phUrl 		= phAddSuffixToUrl(window.location.href, 'format=raw');
		var sForm		= jQuery("#phPosDateOrdersForm");
		var sFormData	= sForm.serialize();
		phRenderPage(sFormData, phUrl);// reload main box to default (list of products)
		jQuery(".ph-pos-message-box").html("");// clean message box
		e.preventDefault();
	});

	/* Display warning when closing a ticket */
	jQuery(document).on("submit", "#phPosCloseTicketForm", function (e) {
		var txt = jQuery(this).data("txt");
		if(!phPosCloseTicketFormConfirmed) {

			phConfirm("#phPosCloseTicketForm", "", txt);
			e.preventDefault();
			return false;
		} else {
			phPosCloseTicketFormConfirmed = false;// set back the variable
			return true;
		}
	});

	/*
	 * Get all checkboxes of categories which are active and add them to url bar and filter the categories
	 *
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 *
	 * Test checkbox (BOOTSTRAP VERSION IS OBSOLETE)
	 * components\com_phocacart\views\pos\tmpl\default_main_categories.php
	 * data-bs-toggle="buttons" - changes the standard checkbox to graphical checkbox
	 *
	 */
	jQuery(document).on("change", "#phPosCategoriesBox .phPosCategoryCheckbox", function() {

		var phParams 	= Joomla.getOptions('phParamsPC');
		var posFilterCategory = phParams['posFilterCategory'];




		if (posFilterCategory == 2) {
			// Multiple categories can be displayed - can be active
			var phA = [];

			// DESIGN - Remove active from all categories and add only for selected
			jQuery("label.phCheckBoxCategory").removeClass("active");

			jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {
				phA.push(jQuery(this).val());
				jQuery(this).parent("label").addClass("active");
			})
			var cValue = phA.join(",");
		} else {
			// Only one category can be displayed
			// Deselect all checkboxed except the one selected - can be active
			var cValue = jQuery(this).val();

			// DESIGN Mark this category active
			jQuery(this).parent().addClass("active");

			jQuery("input.phPosCategoryCheckbox:checkbox:checked").each(function () {

				if (cValue != jQuery(this).val() ) {
					jQuery(this).prop("checked", false);
					jQuery(this).parent("label").removeClass("active");
				} else {

				}
			})

			// Current checkbox was deselected
			if (jQuery(this).prop("checked") == false) {
			cValue = "";
			};
		}

      	var phData 	= "category=" + cValue + "&" + phPosCurrentData();
      	phUpdateUrlParameter("category", cValue);// update URL bar
      	var phUrl 	= phAddSuffixToUrl(window.location.href, 'format=raw');// get the link after update of url bar
      	phRenderPage(phData, phUrl);
      	jQuery(".ph-pos-message-box").html("");// clear message box
   });

   /*
	 * Search by key type - typing of charcters into the search field FIND MEMBER KEYUP
	 *
	 * Must be loaded:
	 * renderSubmitPaginationTopForm()
	 * changeUrlParameter()
	 * editPos()
	 */
	var phThread = null;
	jQuery('#phPosSearch').keyup(function() {
		clearTimeout(phThread);
		var $this = jQuery(this);
		phThread = setTimeout(function(){phFindMember($this.val())}, 800);
	});


	/* Print to POS printer */
	jQuery("#phPosContentBox").on("click", ".phOrderPrintBtn", function (e) {

		var phVars 			= Joomla.getOptions('phVarsPC');
		var phParams 		= Joomla.getOptions('phParamsPC');
		var posServerPrint 	= phParams['posServerPrint'];

		var phUrlAjax 		= phVars['urlOrder'];
		var phOrder	 		= jQuery(this).data("order");
		var phType	 		= jQuery(this).data("type");
		var phOrderCurrent	= jQuery("#phPosOrderPrintBox").attr("data-order");// data("order"); not working
		var phTypeCurrent	= jQuery("#phPosOrderPrintBox").attr("data-type");// data("type"); not working

		// PC PRINT
		if (phType == "-1") {// -1 type is print (1 order, 2 invoice, 3 delivery note, 4 receipt)

			if (posServerPrint == 2 || posServerPrint == 3) {

				// - 1 AND 4 PC PRINT FOR ALL DOCUMENTS EXCEPT 4 (Receipt) - Receipt will be printend by SERVER PRINT
				if (phTypeCurrent == "4") {
					var phUrlAjaxPrint = phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phTypeCurrent + "&pos=1&printserver=1");
					jQuery.ajax({
						type: "GET",
						url: phUrlAjaxPrint,
						async: true,
						cache: "false",
						dataType:"HTML",
						success: function(data){
						jQuery(".ph-pos-message-box").html('<div>' + data + '</div>');
						// jQuery("#phPosOrderPrintBox").attr("class", phClass);// Add class to box of document - to differentiate documents loaded by ajax
						// jQuery("#phPosOrderPrintBox").attr("data-type", phType);// Add data type to box of document - so it can be read by print function
						// jQuery("#phPosOrderPrintBox").html(data);// Add the document itself to the site
						}
					})
					e.preventDefault();
					return false;
					// -1 PC PRINT
				} else {
					window.print(); return false;// print with javascript for all documents except receipt (receipt is ready for server POS printers)
				}

			} else {
				window.print(); return false;// print with javascript for all document (including receipt)
			}
		}

		var phClass 	= "phType" + phType;
		var phUrlAjax 	= phAddSuffixToUrl(phUrlAjax, "id=" + phOrder + "&type=" + phType + "&pos=1");
			jQuery.ajax({
			type: "GET",
			url: phUrlAjax,
			async: true,
			cache: "false",
			dataType:"HTML",
			success: function(data){
				jQuery("#phPosOrderPrintBox").attr("class", phClass);// Add class to box of document - to differentiate documents loaded by ajax
				jQuery("#phPosOrderPrintBox").attr("data-type", phType);// Add data type to box of document - so it can be read by print function
				jQuery("#phPosOrderPrintBox").html(data);// Add the document itself to the site
			}
		})

		e.preventDefault();
	})

	/* EVENT - POS Scroll cart */
	var phPosCart 		= jQuery('#phPosCart');
	phScrollPosCart(phPosCart);//  On start
	phPosCart.on("DOMSubtreeModified", function(){// On modified
		if (phPosCart.text() != '') {// this event runs twice - first when jquery empty the object, second when it fills it again
			phScrollPosCart(phPosCart);// run only on second when it fills the object
		}
	});
})
