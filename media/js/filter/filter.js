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

function phReplaceAll(find, replace, str) {
  return str.replace(new RegExp(find, 'gi'), replace);
}

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

function phRemoveFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait) {

	/*
	 * If there is empty phFilterNewUrlRemove, this means:
	 * a) there were no previous parameter which is waiting or
	 * b) there were previous parameter which is waiting but previous parameter deleted the url
	 *    so it looks like the previous parameter does not exists (but it exists)
	 */

	/* Array -> String */
	param 			= phArrayToString(param);
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

		delete paramsAll[param];
		paramsTypeStringNew = paramsAll;
		mergeMode = 2;
	} else if (typeof paramsAll[param] !== 'undefined') {
		var paramsTypeString 	= paramsAll[param];
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
				paramsTypeStringNew[param] = paramsTypeString;
			} else {
				delete paramsAll[param];
				paramsTypeStringNew = paramsAll;
				mergeMode = 2;
			}
		}
	} else {
		delete paramsAll[param];
		paramsTypeStringNew = paramsAll;
		mergeMode = 2;
	}


	var url;
	
	if (isItemsView == 1) {
		url = location.search;
	} else {
		
		url = urlItemsView;// It is possible to deselect category in category/item view
		document.location = url;
		return 1;
		//return 2; // Not possible to deselect in other than items view
		//return false;
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
	} else {
		
		document.location = phFilterNewUrlRemove;
		phFilterNewUrlRemove = '';
	}
}


function phSetFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait) {

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
	param 			= phArrayToString(param);
	var queryString	= jQuery.param.querystring();
	queryString		= phArrayToString(queryString);


	var paramsAll;
	if (phFilterNewUrlSet !== '' || phFilterNewUrlSetPreviousParamWaiting == 1) {
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
		paramsTypeStringNew[param] = value;// { param:value};// unique value - always overwrite old value
	} else if (value === '') {

	} else if (typeof paramsAll[param] !== 'undefined') {

		var paramsTypeString 	= paramsAll[param];

		var paramsTypeArray		= paramsTypeString.split(',');
		paramsTypeArray 		= phCleanArray(paramsTypeArray);
	

		var findVal				= paramsTypeArray.indexOf(value);

		if ( findVal === -1 ){
			// New value is not there - add it
			paramsTypeArray.push(value);
			paramsTypeString = paramsTypeArray.join();
			paramsTypeStringNew[param] = paramsTypeString;//{ param:paramsTypeString};// Changed
		} else {
			// New value is there - don't change it
			paramsTypeStringNew[param] = paramsTypeString;//{ param:paramsTypeString};// Unchanged
		}
	} else {
		
		paramsTypeStringNew[param] = value;//{ param:value};
	}


	

	var url;
	if (isItemsView == 1) {
		
		url = location.search;
	} else {
		url = urlItemsView;
	}


	// Set new url or take the one from previous parameter
	if (phFilterNewUrlSet !== '' || phFilterNewUrlSetPreviousParamWaiting == 1) {
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

	} else {
		
		//window.history.pushState({},"", phFilterNewUrlSet);return 2;
		document.location = phFilterNewUrlSet;
		phFilterNewUrlSet = '';
	}

}


/* Function phChangeFilter */
function phChangeFilter(param, value, formAction, formType, uniqueValue, wait) {
	
	Joomla.loadingLayer('load');
	var phVars 		= Joomla.getOptions('phVarsModPhocacartFilter');
	var phParams 	= Joomla.getOptions('phParamsModPhocacartFilter');

	var isItemsView					= phVars['isItemsView'];
	var isSef						= phVars['isSef'];
	var urlItemsView				= phVars['urlItemsView'];
	var urlItemsViewWithoutParams	= phVars['urlItemsViewWithoutParams'];
	var phA = 1;
 
	if (formType == "text") {
		//value = phEncode(value);
      	if (formAction == 1) {
         	phA = phSetFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait);
      	} else {
			
         	phA = phRemoveFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait);
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
					phRemoveFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait);
				}
			} else {
				document.location 		= urlItemsView;
			}
		}
   	} else {
		
      	if (formAction.checked) {
         	phA = phSetFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait);
      	} else {
			
         	phA = phRemoveFilter(param, value, isItemsView, urlItemsView, uniqueValue, wait);
      	}
   	}
   	phRenderFullOverlay(phA);
}

/* Function phChangeSearch*/ 
function phChangeSearch(param, value, formAction) {

	Joomla.loadingLayer('load');
	var phVars 		= Joomla.getOptions('phVarsModPhocacartSearch');
	var phParams 	= Joomla.getOptions('phParamsModPhocacartSearch');

	var isItemsView					= phVars['isItemsView'];
	var urlItemsView				= phVars['urlItemsView'];
	var urlItemsViewWithoutParams	= phVars['urlItemsViewWithoutParams'];
	var phA = 1;
	
	if (formAction == 1) {
		if (phParams['searchOptions'] == 1) {
		   if(jQuery("#phSearchSearchAllProducts").attr("checked")) {
			  	urlItemsView = urlItemsViewWithoutParams;
			  	isItemsView = 0; // When options are enabled and searching is set to all - we search without filtering
			}
		} else {
			isItemsView = 0;// When options are disabled we always search without filtering
		}
		
		phA = phSetFilter(param, value, isItemsView, urlItemsView, 1, 0);
	} else {
		phA = phRemoveFilter(param, value, isItemsView, urlItemsView, 1,0);  
	}
	phRenderFullOverlay(phA);
 }

/* phRenderFullOverlay*/
function phRenderFullOverlay(phA) {
	if (phA == 2) {
		// 2 means false
	} else {
		var phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
		phOverlay.appendTo(document.body);
		jQuery("#phOverlay").fadeIn().css("display","block");
	}
}

jQuery(document).ready(function () {
	jQuery('.collapse')
    .on('shown.bs.collapse', function() {
		jQuery(this).parent().find(".glyphicon-triangle-right").removeClass("glyphicon-triangle-right").addClass("glyphicon-triangle-bottom");
		jQuery(this).parent().find(".fa-caret-right").removeClass("fa-caret-right").addClass("fa-caret-down");
    })
    .on('hidden.bs.collapse', function() {
        jQuery(this).parent().find(".glyphicon-triangle-bottom").removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-right");
        jQuery(this).parent().find(".fa-caret-down").removeClass("fa-caret-down").addClass("fa-caret-right");
    });
});

function phClearField(field) {
	jQuery(field).val('');
}
