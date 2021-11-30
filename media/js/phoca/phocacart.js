/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

function phRemoveUrlParameter(param, url) {
	var rtn = url.split("?")[0],
	param,
	params_arr = [],
	queryString = (url.indexOf("?") !== -1) ? url.split("?")[1] : "";
	if (queryString !== "") {
	   params_arr = queryString.split("&");
	   for (var i = params_arr.length - 1; i >= 0; i -= 1) {
		  paramV = params_arr[i].split("=")[0];
		  if (paramV === param) {
			 params_arr.splice(i, 1);
		  }
	   }
	   rtn = rtn + "?" + params_arr.join("&");
	}
	return rtn;
 }


function startOverlay(outputDiv) {
	var phOverlay = jQuery('<div id="phOverlayDiv"><div id="phLoaderFull"> </div></div>');

	phOverlay.appendTo(outputDiv);
	jQuery("#phOverlayDiv").fadeIn().css("display","block");

}

function startFullOverlay(phA) {
	if (phA == 2) {

	} else {
		var phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
		phOverlay.appendTo(document.body);
		jQuery("#phOverlay").fadeIn().css("display","block");

	}

}

function stopOverlay() {
	jQuery("#phOverlay").fadeIn().css("display","none");

}

function phRemoveParamFromUrl(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}

function phUpdatePageAndParts(url, source) {


	var phVars 		= Joomla.getOptions('phVarsPC');
	var phParamsS 	= Joomla.getOptions('phParamsModPhocacartSearch');


	var ds = '/';
	if (phVars['basePath'] == 'undefined' || phVars['basePath'] == '') {
		ds = '';
	}


	// Firefox problem
	// FROM:
	//window.history.pushState({},"", url);// update URL
	// TO:
	if (url == '') {
		window.history.pushState({},"", location.pathname);// update URL
	} else {
		window.history.pushState({},"", url);// update URL
	}
	

	if (url != '') {
		// Remove format and set the raw
		var urlMain = phRemoveParamFromUrl('format', url);
		urlMain = url + '&format=raw';
	} else {
		var urlMain = '?format=raw';
	}

	// Remove possible conflict params in URL
	var urlModule = phRemoveParamFromUrl('option', urlMain);
	urlModule = phRemoveParamFromUrl('view', urlModule);
	urlModule = phRemoveParamFromUrl('module', urlModule);

	urlModule = urlModule.substring(urlModule.indexOf('?') + 1);
	
	var urlSearchModule = phVars['basePath'] + ds + 'index.php?option=com_ajax&module=phocacart_search';
	var urlFilterModule = phVars['basePath'] + ds + 'index.php?option=com_ajax&module=phocacart_filter';


	if (urlModule.indexOf("?") == 0) {
		urlSearchModule = urlSearchModule + '&' +urlModule.substr(1);
		urlFilterModule = urlFilterModule + '&' +urlModule.substr(1);
		
	} else if (urlModule.indexOf("&") == 0) {
		urlSearchModule = urlSearchModule + urlModule;
		urlFilterModule = urlFilterModule + urlModule;
		
	} else {
		urlSearchModule = urlSearchModule + '&' + urlModule;
		urlFilterModule = urlFilterModule + '&' + urlModule;
		
	}

	if (typeof phParamsS != 'undefined' && phVars['mod_phocacart_search'] == 1 && phParamsS['displayActiveParameters'] == 1) {
		// Update filter only when
		phRenderPagePart({}, 'phSearchActiveTags', urlSearchModule);// AJAX update search module
	}

	if (typeof phVars != 'undefined' && phVars['mod_phocacart_filter'] == 1 && source == 2) {
		// Update filter only when source comes from search filter
		phRenderPagePart({}, 'phFilterBox', urlFilterModule);// AJAX update filter module
	}
	phRenderPage({},urlMain );// AJAX update main page
}


function phRenderPage(sFormData, phUrlJs) {


	var phVars = Joomla.getOptions('phVarsPC');
	var phParams = Joomla.getOptions('phParamsPC');

	var outputDiv = '#' + phVars['renderPageOutput'];
	var phUrl = phVars['renderPageUrl'];
	var isPOS = phVars['isPOS'];
	var loadChosen = phParams['loadChosen'];


	startOverlay(outputDiv);


	phUrl = typeof phUrlJs !== "undefined" ? phUrlJs : phUrl;
	phRequest = jQuery.ajax({
		type: "POST",
		url: phUrl,
		async: true,
		cache: "false",
		data: sFormData,
		dataType:"HTML",
		success: function(data){

			jQuery(outputDiv).html(data);
			if (isPOS == 1) {
				phPosManagePage();
			}
			if (loadChosen == 1) {
				jQuery('select').chosen('destroy').chosen({disable_search_threshold : 10,allow_single_deselect : true});
			}
			if (typeof phChangeAttributeType === "function") {
				phChangeAttributeType();// Recreate the select attribute (color, image) after AJAX
			}
			if(typeof phLazyLoadInstance !== "undefined" && phLazyLoadInstance) {
				phLazyLoadInstance.update();// Lazy load - reload if enabled
			}
			stopOverlay();
		}
	})

	return false;
}

function phRenderPagePart(sFormData, outputDiv, phUrl) {

	phRequest = jQuery.ajax({
		type: "POST",
		url: phUrl,
		async: true,
		cache: "false",
		data: sFormData,
		dataType:"HTML",
		success: function(data){
			outputDiv = '#'+ outputDiv;
			jQuery(outputDiv).html(data);
		}
	})
}

function phDisableRequirement() {

	var phParams = Joomla.getOptions('phParamsPC');
	var loadChosen = phParams['loadChosen'];

	var selectC = jQuery("#jform_country_phs");
	var selectR = jQuery("#jform_region_phs");
	var checked = jQuery('#phCheckoutBillingSameAsShipping').prop('checked');
	if (checked) {

		jQuery(".phShippingFormFields").prop("readonly", true);
		selectC.attr("disabled", "disabled");
		selectR.attr("disabled", "disabled");
		jQuery(".phShippingFormFieldsRequired").removeAttr('aria-required');
		jQuery(".phShippingFormFieldsRequired").removeAttr('required');

		if (loadChosen > 0) {
			jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");
			jQuery(".phShippingFormFields").trigger("chosen:updated");
		}
	} else {
		jQuery(".phShippingFormFieldsRequired").prop('aria-required', 'true');
		jQuery(".phShippingFormFieldsRequired").prop('required', 'true');
		jQuery(".phShippingFormFields").removeAttr('readonly');
		selectC.removeAttr("disabled");
		selectR.removeAttr("disabled");

		if (loadChosen > 0) {
			jQuery(".phShippingFormFieldsRequired").trigger("chosen:updated");
			jQuery(".phShippingFormFields").trigger("chosen:updated");
		}
	}
}

function phRenderBillingAndShippingSame() {
	phDisableRequirement();
	jQuery("#phCheckoutBillingSameAsShipping").on('click', function() {
		phDisableRequirement();
	})
}



// Events
function phEventChangeFormPagination(sForm, sItem) {


	var phVars = Joomla.getOptions('phVarsPC');
	var phParams = Joomla.getOptions('phParamsPC');

	var phA = 1;// Full Overlay Yes

	// If pagination changes on top (ordering or display num then the bottom pagination is reloaded by ajax
	// But if bottom pagination changes, the top pagination is not reloaded
	// so we need to copy the bottom values from ordering and display num selectbox
	// and set it to top
	// top id: itemorderingtop, limittop
	// bottom id: itemordering, limit
	var phSelectBoxVal  = jQuery(sItem).val();
	var phSelectBoxId 	= "#" + jQuery(sItem).attr("id") + "top";
	jQuery(phSelectBoxId).val(phSelectBoxVal);

	var formName = jQuery(sForm).attr("name");

	if (phParams['ajaxPaginationCategory'] == 1 || phVars['isPOS'] == 1) {
		// Everything is AJAX - pagination top even pagination bottom
		var phUrl = window.location.href;
		phRenderPage(jQuery(sForm).serialize(), phUrl);
	} else {
		// Only top pagination is ajax, bottom pagination is not ajax start prev 1 2 3 next end
		if (formName == "phitemstopboxform") {// AJAX - Top pagination always ajax
			var phUrl = window.location.href;
			phRenderPage(jQuery(sForm).serialize(), phUrl);
		} else {
			sForm.submit();// STANDARD
			startFullOverlay(phA);
		}
	}
}

function phNumberFormat (number, decimals, decPoint, thousandsSep) {
  
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
	var n = !isFinite(+number) ? 0 : +number
	var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
	var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
	var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
	var s = ''
  
	var toFixedFix = function (n, prec) {
	  var k = Math.pow(10, prec)
	  return '' + (Math.round(n * k) / k)
		.toFixed(prec)
	}
  
	// @to do: for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
	if (s[0].length > 3) {
	  s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
	}
	if ((s[1] || '').length < prec) {
	  s[1] = s[1] || ''
	  s[1] += new Array(prec - s[1].length + 1).join('0')
	}
  
	return s.join(dec)
}


// ------
// Events
// ------
jQuery(document).ready(function(){

	// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
	jQuery(".phItemSwitchLayoutType").on('click', function (e) {


		var phDataL = jQuery(this).data("layouttype");// Get the right button (list, grid, gridlist)
		var sForm 	= jQuery(this).closest("form");// Find in which form the right button was clicked

		var sFormData = sForm.serialize() + "&layouttype=" + phDataL;

		jQuery(".phItemSwitchLayoutType").removeClass("active");
		jQuery(".phItemSwitchLayoutType." + phDataL).addClass("active");
		var phUrl = window.location.href;
		phRenderPage(sFormData, phUrl);
	})

	// ::EVENT (CLICK) Pagination - Clicking on Start Prev 1 2 3 Next End
	jQuery(document).on('click', ".phPaginationBox .pagination li a", function (e) {



		var phVars = Joomla.getOptions('phVarsPC');
		var phParams = Joomla.getOptions('phParamsPC');

		if (phParams['ajaxPaginationCategory'] == 1 || phVars['isPOS'] == 1) {
			var phUrl = jQuery(this).attr("href");
			var sForm = jQuery(this).closest("form");// Find in which form the right button was clicked
			var sFormData = sForm.serialize();

			phRenderPage(sFormData, phUrl);

			// Don't set format for url bar (e.g. pagination uses ajax with raw - such cannot be set in url bar)
			// we use ajax and pagination for different views inside one view (customers, products, orders) so we cannot set this parameter in url, because of ajax
			//if (phVars['isPOS'] == 1) {
				phUrl = phRemoveUrlParameter("format", phUrl);
				phUrl = phRemoveUrlParameter("start", phUrl);
			//}

			window.history.pushState("", "", phUrl);// change url bar
			e.preventDefault();
		}
	})

	phRenderBillingAndShippingSame();


})