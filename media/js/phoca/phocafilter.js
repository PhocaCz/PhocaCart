/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

var phFilterNewUrlSet							= '';
var phFilterNewUrlRemove						= '';
var phFilterNewUrlSetPreviousParamWaiting		= 0;
var phFilterNewUrlRemovePreviousParamWaiting	= 0;
/*
function phReplaceAll(find, replace, str) {
  return str.replace(new RegExp(find, 'gi'), replace);
}
*/

function phEscapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function phReplaceAll(find, replace, string) {
  return string.replace(new RegExp(phEscapeRegExp(find), 'g'), replace);
}

function phEncode(string) {
	var s;
	s = encodeURIComponent(string);
	s = phReplaceAll('%5B', '[', s);
	s = phReplaceAll('%5D', ']', s);
	s = phReplaceAll('%2C', ',', s);
	s = phReplaceAll('%3A', ':', s);
	return s;
}

function phArrayToString(a) {
	var s;
	s = phReplaceAll('[', '(', a);
	s = phReplaceAll(']', ')', s);
	//s = phReplaceAll('%5B', '(', s);
	//s = phReplaceAll('%5D', ')', s);
	return s;
}

function phStringToArray(a) {
	var s;
	s = phReplaceAll('(', '[', a);
	s = phReplaceAll(')', ']', s);
	return s;
}

function phCleanArray(actual){
  //var newArray = new Array();
  var newArray = [];
  for(var i = 0; i <actual.length; i++){
      if (actual[i]){
        newArray.push(actual[i]);
    }
  }
  return newArray;
}

function phCleanEmptyParams(url) {
	return url.replace(/&?[^&?]+=(?=(?:&|$))/g, '');
}

function phCleanAloneQuestionMark(url) {

	if (url == '?&') {
		url = '?';
	}

	if (url == '?') {
		url = '';
	}
	return url;
}

function phFilterValue(value) {
	var v;
	v = phReplaceAll('<', '', value);
	v = phReplaceAll('>', '', v);
	return v;
}
/*
function phSetUrl(url) {
	var urlItemsView= url;
	var urlPathName	= location.pathname;
	var urlSearch	= location.search;
	var urlPage		= urlPathName + urlSearch;
}
*/

function phResetNewUrl(waiting) {

	phFilterNewUrlRemove = '';
	phFilterNewUrlSet = '';
	if (waiting == 1) {
		phFilterNewUrlRemovePreviousParamWaiting = 0;
		phFilterNewUrlSetPreviousParamWaiting = 0;
	}
}

function phRemoveFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, uniqueValue, wait, source) {

	var phParams = Joomla.getOptions('phParamsPC');



	/* Set back phFilterNewUrlSet - it can happen that we set more parameter at once
	 * typically parameter plus category id because of forcing category when setting some parameter
	 * So be sure, phFilterNewUrlSet is cleaned (except id of category which is set in urlItemsView)
	*/
	if (phParams['ajaxSearchingFilteringItems'] != 1) {
		phFilterNewUrlSet = urlItemsView;
	}


	/*
	 * If there is empty phFilterNewUrlRemove, this means:
	 * a) there were no previous parameter which is waiting or
	 * b) there were previous parameter which is waiting but previous parameter deleted the url
	 *    so it looks like the previous parameter does not exists (but it exists)
	 */

	/* Array -> String */
	paramV 			= phArrayToString(paramV);
	var queryString	= jQuery.param.querystring();
	queryString		= phArrayToString(queryString);

	var paramsAll;
	if (phFilterNewUrlRemove !== '' || phFilterNewUrlRemovePreviousParamWaiting == 1) {
		paramsAll = jQuery.deparam.querystring(phFilterNewUrlRemove);
	} else {
		paramsAll = jQuery.deparam.querystring(queryString);

	}

	var paramsTypeStringNew	= {};
	var mergeMode = 0;


	/* Handle pagination - when changing filter, set pagination to zero - to start */
	if (typeof paramsAll['start'] !== 'undefined') {
		paramsTypeStringNew['start'] = 0;
	}
	if (typeof paramsAll['limitstart'] !== 'undefined') {
		paramsTypeStringNew['limitstart'] = 0;
	}

	if (uniqueValue == 1) {

		delete paramsAll[paramV];
		paramsTypeStringNew = paramsAll;
		mergeMode = 2;
	} else if (typeof paramsAll[paramV] !== 'undefined') {
		var paramsTypeString 	= paramsAll[paramV];
		var paramsTypeArray		= paramsTypeString.split(',');
		paramsTypeArray 		= phCleanArray(paramsTypeArray);
		var findVal				= paramsTypeArray.indexOf(value);


		if ( findVal === -1 ){
			// Value to remove is not there
		} else {
			// Value to remove is there
			var index = paramsTypeArray.indexOf(value);

			if (index > -1) {
				paramsTypeArray.splice(index, 1);
			}

			paramsTypeString 	= paramsTypeArray.join();

			if (paramsTypeString) {
				paramsTypeStringNew[paramV] = paramsTypeString;
			} else {
				delete paramsAll[paramV];
				paramsTypeStringNew = paramsAll;
				mergeMode = 2;
			}
		}
	} else {
		delete paramsAll[paramV];
		paramsTypeStringNew = paramsAll;
		mergeMode = 2;
	}


	/*var url;

	if (filteredProductsOnly == 1) {
		url = location.search;
	} else {

		url = urlItemsView;// It is possible to deselect category in category/item view
		document.location = url;
		return 1;
		//return 2; // Not possible to deselect in other than items view
		//return false;
	}*/
	/*
	if ((isItemsView == 1 && filteredProductsOnly != 1) || isItemsView != 1) {
		url = urlItemsView;// skip all parameters (a) search all products in items view or b) no items view
	} else {
		url = location.search;// complete url with selected parameters
	}*/

	var url;

	if ((isItemsView == 1 && filteredProductsOnly != 1) || isItemsView != 1) {
		url = urlItemsView;// skip all parameters (a) search all products in items view or b) no items view

		if (url == '') {
			url = document.location.pathname;
		}

		document.location = url;
		return 1;
	} else {
		url = location.search;// complete url with selected parameters
	}


	// Set new url or take the one from previous parameter
	if (phFilterNewUrlRemove !== '' || phFilterNewUrlRemovePreviousParamWaiting == 1) {
		url = phFilterNewUrlRemove;
	}

	/* Array -> String */
	url		= phArrayToString(url);


	phFilterNewUrlRemove 	= jQuery.param.querystring( url, paramsTypeStringNew, mergeMode);// one parameter only

	phFilterNewUrlRemove 	= phReplaceAll('%2C', ',', phFilterNewUrlRemove);
	phFilterNewUrlRemove 	= phReplaceAll('%5B', '[', phFilterNewUrlRemove);
	phFilterNewUrlRemove 	= phReplaceAll('%5D', ']', phFilterNewUrlRemove);
	phFilterNewUrlRemove 	= phReplaceAll('%3A', ':', phFilterNewUrlRemove);

	/* String -> Array */
	phFilterNewUrlRemove	= phStringToArray(phFilterNewUrlRemove);
	phFilterNewUrlRemove 	= phCleanAloneQuestionMark(phFilterNewUrlRemove);


	// Wait for next parameter

	if (wait == 1) {
		// Don't reload, wait for other parameter
		phFilterNewUrlRemovePreviousParamWaiting = 1;

		if (isItemsView == 1 && phParams['ajaxSearchingFilteringItems'] == 1) {// and ajax
			return 2;// don't run overlay
		}

	} else {

		if (isItemsView == 1 && phParams['ajaxSearchingFilteringItems'] == 1) {
			phUpdatePageAndParts(phFilterNewUrlRemove, source);// Update Main, Search, Filter
			phResetNewUrl(1);
			return 2;
		} else {
			//document.location = phFilterNewUrlSet;
			if (phFilterNewUrlRemove == '') {
				phFilterNewUrlRemove = document.location.pathname;
			}
			document.location = phFilterNewUrlRemove;
		}
		phResetNewUrl();
	}

	return 1;
}


/*
 * param: parameter name
 * value: parameter value
 * isItemsView: comes the request from itemsView (Ajax possible) or not
 * urlItemsView: urlItemsView differently set by different parameters
 * filteredProductsOnly: when searching - a) all products can be searched or - b) only filtered products can be searched
						 a) c=1-category&search=search - c=1-category will be removed from url to search all parameters
						 b) c=1-category&search=search - nothing will be removed from url to search filtered parameters
 * uniqueValue: c=1-category,c=2category is not unique value, price_from=100 is unique value
 * wait: wait for next parameter before reload and end the action (e.g. price with two values)
 * source: where the request comes, values: 1 filter, 2 search, 3 itemview (specific case)
 */


function phSetFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, uniqueValue, wait, source) {

	/* When Force Category, then urlItemsview get the ID of category included by e.g. module filter - loaded by php
		force_category ... yes
		skip_category_view ... no
	*/
	var phParams = Joomla.getOptions('phParamsPC');
	
	/*
	 * We need to differentiate:
	 * a) there is no parameter in the URL
	 * b) there is no parameter in the URL but it was here but we have removed it previously
	 *    as there is a wait function which handles e.g. two parameters at once and if
	 *    the first parameter will be removed we need to differentiate between:
	 * a) url which was empty (no parameters) at the beginning
	 * b) url which had parameters but they were removed while working with this function and waiting
	 */

	value = phFilterValue(value);

	/* Array -> String */
	paramV 			= phArrayToString(paramV);
	var queryString	= jQuery.param.querystring();
	queryString		= phArrayToString(queryString);

	var paramsAll;
	if (/*phFilterNewUrlSet !== '' || */ phFilterNewUrlSetPreviousParamWaiting == 1) {
		phFilterNewUrlSet	= phArrayToString(phFilterNewUrlSet);// wait back from () to [] so it can be read by querystring
		
	
		
		paramsAll = jQuery.deparam.querystring(phFilterNewUrlSet);

	} else {
		paramsAll = jQuery.deparam.querystring(queryString);

	}



	var paramsTypeStringNew		= {};
	var mergeMode 				= 0;

	/* Handle pagination - when changing filter, set pagination to zero - to start */
	if (typeof paramsAll['start'] !== 'undefined') {
		paramsTypeStringNew['start'] = 0;
	}
	if (typeof paramsAll['limitstart'] !== 'undefined') {
		paramsTypeStringNew['limitstart'] = 0;
	}


	if (uniqueValue == 1) {
		paramsTypeStringNew[paramV] = value;// { param:value};// unique value - always overwrite old value

	} else if (value === '') {

	} else if (typeof paramsAll[paramV] !== 'undefined') {

		var paramsTypeString 	= paramsAll[paramV];


		var paramsTypeArray		= paramsTypeString.split(',');
		paramsTypeArray 		= phCleanArray(paramsTypeArray);


		var findVal				= paramsTypeArray.indexOf(value);

		if ( findVal === -1 ){
			// New value is not there - add it
			paramsTypeArray.push(value);
			paramsTypeString = paramsTypeArray.join();
			paramsTypeStringNew[paramV] = paramsTypeString;//{ param:paramsTypeString};// Changed
		} else {
			// New value is there - don't change it
			paramsTypeStringNew[paramV] = paramsTypeString;//{ param:paramsTypeString};// Unchanged
		}
	} else {

		paramsTypeStringNew[paramV] = value;//{ param:value};
	}


	var url;
	if ((isItemsView == 1 && filteredProductsOnly != 1) || isItemsView != 1) {
		url = urlItemsView;// skip all parameters (a) search all products in items view or b) no items view
	} else {
		url = location.search;// complete url with selected parameters
	}


	// Set new url or take the one from previous parameter
	if (/*phFilterNewUrlSet !== '' ||*/ phFilterNewUrlSetPreviousParamWaiting == 1) {
		url = phFilterNewUrlSet;
	}

	/* Array -> String */
	url		= phArrayToString(url);


	phFilterNewUrlSet = jQuery.param.querystring( url, paramsTypeStringNew, mergeMode);// one parameter only



	phFilterNewUrlSet = phReplaceAll('%2C', ',', phFilterNewUrlSet);
	phFilterNewUrlSet 	= phReplaceAll('%2C', ',', phFilterNewUrlSet);
	phFilterNewUrlSet 	= phReplaceAll('%5B', '[', phFilterNewUrlSet);
	phFilterNewUrlSet 	= phReplaceAll('%5D', ']', phFilterNewUrlSet);
	phFilterNewUrlSet 	= phReplaceAll('%3A', ':', phFilterNewUrlSet);
	/* String -> Array */

	phFilterNewUrlSet	= phStringToArray(phFilterNewUrlSet);

	phFilterNewUrlSet	= phCleanEmptyParams(phFilterNewUrlSet);
	phFilterNewUrlSet	= phCleanAloneQuestionMark(phFilterNewUrlSet);


	// Wait for next parameter
	if (wait == 1) {
		// Don't reload, wait for other parameter
		phFilterNewUrlSetPreviousParamWaiting = 1;

		if (isItemsView == 1 && phParams['ajaxSearchingFilteringItems'] == 1) {
			return 2;// don't run overlay
		}

	} else {
		if (isItemsView == 1 && phParams['ajaxSearchingFilteringItems'] == 1) {
			phUpdatePageAndParts(phFilterNewUrlSet, source);// Update Main, Search, Filter
			phResetNewUrl(1);
			return 2;
		} else {
			document.location = phFilterNewUrlSet;
		}
		phResetNewUrl();
	}

	return 1;

}


/* Function phChangeFilter */
function phChangeFilter(paramV, value, formAction, formType, uniqueValue, wait, source) {


	var phVars 		= Joomla.getOptions('phVarsModPhocacartFilter');
	var phParams 	= Joomla.getOptions('phParamsModPhocacartFilter');


	var isItemsView					= phVars['isItemsView'];
	var isSef						= phVars['isSef'];
	var urlItemsView				= phVars['urlItemsView'];
	var urlItemsViewWithoutParams	= phVars['urlItemsViewWithoutParams'];
	var phA = 1;


	if (formType == 'itemview'){
		// Specific case for item view (no filtering but setting unique url for product with attributes)
		if(value === undefined) {
			value = '';
		}

		phA = phSetFilter(paramV, value, 1, 0, 1, uniqueValue, wait, source);
	} else if (formType == "text") {
		//value = phEncode(value);
      	if (formAction == 1) {
			 phA = phSetFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);

      	} else {

         	phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
      	}
   	} else if (formType == "category") {
		urlItemsView = urlItemsViewWithoutParams;

     	if (phParams['removeParametersCat'] == 1) {
			document.location 		= urlItemsView;
		} else {
			var currentUrlParams	= jQuery.param.querystring();
			if (isItemsView == 1) {
				if (isSef == 1) {
					document.location 		= jQuery.param.querystring(urlItemsView, currentUrlParams, 2);
				} else {
					phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
				}
			} else {
				document.location 		= urlItemsView;
			}
		}
   	}  else if (formType == "color" || formType == 'image') {

		var isActive = jQuery(formAction).hasClass('on');
		if (isActive) {
			phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
			jQuery(formAction).removeClass('on');
		} else {
			phA = phSetFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
			jQuery(formAction).addClass('on');
		}

   	} else {

      	if (formAction.checked) {


         	phA = phSetFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
      	} else {

         	phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
      	}
	}


	startFullOverlay(phA);
}

/* Function phChangeSearch*/
function phChangeSearch(paramV, value, formAction) {


	var phVars 		= Joomla.getOptions('phVarsModPhocacartSearch');
	var phParams 	= Joomla.getOptions('phParamsModPhocacartSearch');
	var phVarsPC 	= Joomla.getOptions('phParamsPC');

	var isItemsView					= phVars['isItemsView'];
	var urlItemsView				= phVars['urlItemsView'];
	var urlItemsViewWithoutParams	= phVars['urlItemsViewWithoutParams'];
	var phA = 1;

	var filteredProductsOnly = isItemsView;
	if (formAction == 1) {

		if (phParams['searchOptions'] == 1) {


			//jQuery("#phSearchBoxSearchAllProducts:checked").val();
			//jQuery("#phSearchBoxSearchAllProducts").attr("checked");
		   if(jQuery("#phSearchBoxSearchAllProducts:checked").length > 0) {
			  	urlItemsView = urlItemsViewWithoutParams;
				  filteredProductsOnly = 0; // When options are enabled and searching is set to all - we search without filtering
			}
		} else {
			filteredProductsOnly = 0;// When options are disabled we always search without filtering
		}

		phA = phSetFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly,  1, 0, 2);
	} else {
		phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, 1, 0, 2);
	}

	startFullOverlay(phA);
 }


function phPriceFilterRange() {


	var phVars 	= Joomla.getOptions('phParamsPC');
	var phLang	= Joomla.getOptions('phLangPC');

	// Filter Range
	if (typeof jQuery("#phPriceFilterRange").slider === "function") {
		jQuery("#phPriceFilterRange").slider({
			range: true,
			min: phVars['filterPriceMin'],
			max: phVars['filterPriceMax'],
			values: [phVars['filterPriceFrom'], phVars['filterPriceTo']],
			slide: function( event, ui ) {
				jQuery("#phPriceFromTopricefrom").val(ui.values[0]);
				jQuery("#phPriceFromTopriceto").val(ui.values[1]);
				jQuery("#phPriceFilterPrice").html("" + phLang['COM_PHOCACART_PRICE'] + ": " + phGetPriceFormat(ui.values[0]) + " - " + phGetPriceFormat(ui.values[1]));
			}
		});
	}

	jQuery("#phPriceFilterPrice").html("" + phLang['COM_PHOCACART_PRICE'] + ": " + phGetPriceFormat(phVars['filterPriceFrom']) + " - " + phGetPriceFormat(phVars['filterPriceTo']));


	jQuery("#phPriceFromTopricefrom").on("change", function (e) {
		var from = jQuery("#phPriceFromTopricefrom").val();
		var to = jQuery("#phPriceFromTopriceto").val();
		if (to == '') { to = phVars['filterPriceMax'];}
		if (from == '') { from = phVars['filterPriceMin'];}
		if (Number(to) < Number(from)) {to = from;jQuery("#phPriceFromTopriceto").val(to);}
		if (typeof jQuery("#phPriceFilterRange").slider === "function") {
			jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});
		}
		jQuery("#phPriceFilterPrice").html("" + phLang['COM_PHOCACART_PRICE'] + ": " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));
	})

	jQuery("#phPriceFromTopriceto").on("change", function (e) {
		var from = jQuery("#phPriceFromTopricefrom").val();
		var to = jQuery("#phPriceFromTopriceto").val();
		if (to == '') { to = phVars['filterPriceMax'];}
		if (from == '') { from = phVars['filterPriceMin'];}
		if (Number(to) < Number(from)) {to = from;jQuery("#phPriceFromTopriceto").val(to);}
		if (typeof jQuery("#phPriceFilterRange").slider === "function") {
			jQuery( "#phPriceFilterRange" ).slider({values: [from,to]});
		}
		jQuery("#phPriceFilterPrice").html("" + phLang['COM_PHOCACART_PRICE'] + ": " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));
	})
}

function phClearField(field) {
	jQuery(field).val('');
	if (field == '#phPriceFromTopricefrom' || field == '#phPriceFromTopriceto') {
		phPriceFilterRange ();
	}
}


// ------
// Events
// ------
jQuery(document).ready(function () {
	jQuery('.collapse')
    .on('shown.bs.collapse', function() {
		jQuery(this).parent().find(".glyphicon-triangle-right").removeClass("glyphicon-triangle-right").addClass("glyphicon-triangle-bottom");
		jQuery(this).parent().find(".fa-caret-right").removeClass("fa-caret-right").addClass("fa-caret-down");
		/* SVG */
		var useT = jQuery(this).parent().find("svg use");
		var link = useT.attr('xlink:href');
		link = link.replace('#pc-si-triangle-right', '#pc-si-triangle-bottom');
		useT.attr('xlink:href', link);
    })
    .on('hidden.bs.collapse', function() {
        jQuery(this).parent().find(".glyphicon-triangle-bottom").removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-right");
        jQuery(this).parent().find(".fa-caret-down").removeClass("fa-caret-down").addClass("fa-caret-right");
		/* SVG */
		var useT = jQuery(this).parent().find("svg use");
		var link = useT.attr('xlink:href');
		link = link.replace('#pc-si-triangle-bottom', '#pc-si-triangle-right');
		useT.attr('xlink:href', link);
	});


	phPriceFilterRange ();
});
