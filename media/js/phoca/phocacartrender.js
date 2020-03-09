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





 /* POS */
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

function startOverlay(outputDiv) {
	var phOverlay = jQuery('<div id="phOverlayDiv"><div id="phLoaderFull"> </div></div>');

	phOverlay.appendTo(outputDiv);
	jQuery("#phOverlayDiv").fadeIn().css("display","block");
	jQuery("#loading-logo").remove();// Remove loading logo (Joomla!)
}

function startFullOverlay(phA) {
	if (phA == 2) {

	} else {
		var phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
		phOverlay.appendTo(document.body);
		jQuery("#phOverlay").fadeIn().css("display","block");
		jQuery("#loading-logo").remove();// Remove loading logo (Joomla!)
	}

}

function stopOverlay() {
	jQuery("#phOverlay").fadeIn().css("display","none");
	jQuery("#loading-logo").remove();// Remove loading logo (Joomla!)
}



function phUpdatePageAndParts(url, source) {

	Joomla.loadingLayer('load');
	var phVars 		= Joomla.getOptions('phVarsPC');
	var phParamsS 	= Joomla.getOptions('phParamsModPhocacartSearch');

	if (phVars['basePath'] == 'undefined' || phVars['basePath'] == '') {
		phVars['basePath'] = '';
	} else {
		phVars['basePath'] = phVars['basePath'] + '/';
	}

	window.history.pushState({},"", url);// update URL

	if (url != '') {
		var urlMain = url + '&format=raw';
	} else {
		var urlMain = '?format=raw';
	}

	var urlSearchModule = phVars['basePath'] + 'index.php?option=com_ajax&module=phocacart_search';
	var urlFilterModule = phVars['basePath'] + 'index.php?option=com_ajax&module=phocacart_filter';
	if (urlMain.indexOf("?") == 0) {
		urlSearchModule = urlSearchModule + '&' +urlMain.substr(1);
		urlFilterModule = urlFilterModule + '&' +urlMain.substr(1);
	} else if (urlMain.indexOf("&") == 0) {
		urlSearchModule = urlSearchModule + urlMain;
		urlFilterModule = urlFilterModule + urlMain;
	} else {
		urlSearchModule = urlSearchModule + '&' + urlMain;
		urlFilterModule = urlFilterModule + '&' + urlMain;
	}

	console.log(phParamsS);
	if (phParamsS['displayActiveParameters'] == 1) {
		// Update filter only when 
		phRenderPagePart({}, 'phSearchActiveTags', urlSearchModule);// AJAX update search module
	}
	
	if (source == 2) {
		// Update filter only when source comes from search filter
		phRenderPagePart({}, 'phFilterBox', urlFilterModule);// AJAX update filter module
	}
	phRenderPage({},urlMain );// AJAX update main page
}


function phRenderPage(sFormData, phUrlJs) {

	Joomla.loadingLayer('load');
	var phVars = Joomla.getOptions('phVarsPC');
	var phParams = Joomla.getOptions('phParamsPC');

	var outputDiv = '#' + phVars['renderPageOutput'];
	var phUrl = phVars['renderPageUrl'];
	var isPOS = phVars['isPOS'];
	var p_load_chosen = phVars['loadChosen'];


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
			if (p_load_chosen == 1) {
				jQuery('select').chosen('destroy').chosen({disable_search_threshold : 10,allow_single_deselect : true});
			}
			phChangeAttributeType();// Recreate the select attribute (color, image) after AJAX
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



// Events
function phEventChangeFormPagination(sForm, sItem) {

	Joomla.loadingLayer('load');
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


		Joomla.loadingLayer('load');
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
})
