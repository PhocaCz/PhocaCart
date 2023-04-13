/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
let phFilterNewUrlSet = "";
let phFilterNewUrlRemove = "";
let phFilterNewUrlSetPreviousParamWaiting = 0;
let phFilterNewUrlRemovePreviousParamWaiting = 0;

// ------
// Utils
// ------

/*
function phReplaceAll(find, replace, str) {
  return str.replace(new RegExp(find, 'gi'), replace);
}
*/

function phEscapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function phReplaceAll(find, replace, string) {
    return string.replace(new RegExp(phEscapeRegExp(find), "g"), replace);
}

function phEncode(string) {
    let s;
    s = encodeURIComponent(string);
    s = phReplaceAll("%5B", "[", s);
    s = phReplaceAll("%5D", "]", s);
    s = phReplaceAll("%2C", ",", s);
    s = phReplaceAll("%3A", ":", s);
    return s;
}

function phArrayToString(a) {
    let s;
    s = phReplaceAll("[", "(", a);
    s = phReplaceAll("]", ")", s);
    //s = phReplaceAll('%5B', '(', s);
    //s = phReplaceAll('%5D', ')', s);
    return s;
}

function phStringToArray(a) {
    let s;
    s = phReplaceAll("(", "[", a);
    s = phReplaceAll(")", "]", s);
    return s;
}

function phCleanArray(actual) {
    //let newArray = new Array();
    let newArray = [];
    for (let i = 0; i < actual.length; i++) {
        if (actual[i]) {
            newArray.push(actual[i]);
        }
    }
    return newArray;
}

function phCleanEmptyParams(url) {
    return url.replace(/&?[^&?]+=(?=(?:&|$))/g, "");
}

function phCleanAloneQuestionMark(url) {
    if (url == "?&") {url = "?";}
    if (url == "?") {url = "";}
    return url;
}

function phFilterValue(value) {
    let v;
    v = phReplaceAll("<", "", value);
    v = phReplaceAll(">", "", v);
    return v;
}

/*
function phSetUrl(url) {
	const urlItemsView= url;
	let urlPathName	= location.pathname;
	let urlSearch	= location.search;
	let urlPage		= urlPathName + urlSearch;
}
*/

function phResetNewUrl(waiting) {
    phFilterNewUrlRemove = "";
    phFilterNewUrlSet = "";
    if (waiting == 1) {
        phFilterNewUrlRemovePreviousParamWaiting = 0;
        phFilterNewUrlSetPreviousParamWaiting = 0;
    }
}

function phIsString(arg) {
    return typeof arg === "string";
}

// Get location.search (or what you'd expect location.search to be) sans any leading #
function phGetQueryString(url) {
    return url.replace(/(?:^[^?#]*\?([^#]*).*$)?.*/, "$1");
}


// jQuery Param alternative
function phUrlParam(data) {

	if (data == null) { return "";}

    const urlParams = [];
    const rbracket = /\[\]$/;

    const add = (name, valueOrFunction) => {
        let value = typeof valueOrFunction === "function" ? valueOrFunction() : valueOrFunction;
        if (value == null) { value = "";}
        urlParams.push(`${encodeURIComponent(name)}=${encodeURIComponent(value)}`);
    };

    const buildParams = (prefix, obj) => {
        if (Array.isArray(obj)) {
            obj.forEach((value, index) => {
                if (rbracket.test(prefix)) {
                    add(prefix, value);
                } else {
                    const i = typeof value === "object" && value != null ? index : "";
                    buildParams(`${prefix}[${i}]`, value);
                }
            });
        } else if (typeof obj === "object" && obj != null) {
            for (const [name, value] of Object.entries(obj)) {
                buildParams(`${prefix}[${name}]`, value);
            }
        } else {
            add(prefix, obj);
        }
    };

    if (Array.isArray(data)) {
        // If an array was passed in, assume that it is a collection of form elements:
        data.forEach((el) => add(el.name, el.value));
    } else {
        for (const [name, value] of Object.entries(data)) {
            buildParams(name, value);
        }
    }

    return urlParams.join("&");
}

// Opposite to jQuery Param
function phUrlDeParam(params, coerce) {
    let obj = {};
    let coerce_types = { true: !0, false: !1, null: null };

    // Iterate over all name=value pairs.
    let paramsA = params.replace(/\+/g, " ").split("&");
    paramsA.forEach(function (v) {

        let param = v.split("=");
        let key = decodeURIComponent(param[0]);
        let val = '';
        let cur = obj;
        // If key is more complex than 'foo', like 'a[]' or 'a[b][c]', split it into its component parts.
        let keys = key.split("][");
        let keys_last = keys.length - 1;

        // If the first keys part contains [ and the last ends with ], then [] are correctly balanced.
        if (/\[/.test(keys[0]) && /\]$/.test(keys[keys_last])) {
            // Remove the trailing ] from the last keys part.
            keys[keys_last] = keys[keys_last].replace(/\]$/, "");
            // Split first keys part into two parts on the [ and add them back onto the beginning of the keys array.
            keys = keys.shift().split("[").concat(keys);
            keys_last = keys.length - 1;
        } else {
            // Basic 'foo' style key.
            keys_last = 0;
        }

        // Are we dealing with a name=value pair, or just a name?
        if (param.length === 2) {
            val = decodeURIComponent(param[1]);
            // Coerce values.
            if (coerce) {
                val = val && !isNaN(val)
					// number
                    ? +val : val === "undefined"
					// undefined
                    ? undefined : coerce_types[val] !== undefined
                    // true, false, null
					? coerce_types[val] : val; // string
            }

            if (keys_last) {

				// Complex key, build deep object structure based on a few rules:
                // * The 'cur' pointer starts at the object top-level.
                // * [] = array push (n is set to array length), [n] = array if n is
                //   numeric, otherwise object.
                // * If at the last keys part, set the value.
                // * For each keys part, if the current level is undefined create an
                //   object or array based on the type of the next keys part.
                // * Move the 'cur' pointer to the next level.
                // * Rinse & repeat.
                for (let i = 0; i <= keys_last; i++) {
                    key = keys[i] === "" ? cur.length : keys[i];
                    cur = cur[key] = i < keys_last ? cur[key] || (keys[i + 1] && isNaN(keys[i + 1]) ? {} : []) : val;
                }
            } else {

                // Simple key, even simpler rules, since only scalars and shallow arrays are allowed.

                if (Array.isArray(obj[key])) {
                    // val is already an array, so push on the next value.
                    obj[key].push(val);
                } else if (obj[key] !== undefined) {
                    // val isn't an array, but since a second value has been specified, convert val into an array.
                    obj[key] = [obj[key], val];
                } else {
                    // val is a scalar.
                    obj[key] = val;
                }
            }
        } else if (key) {
            // No value was defined, so set something meaningful.
            obj[key] = coerce ? undefined : "";
        }
    });

    return obj;
}

// jQuery Param.querystring alternative
function phUrlParamQueryString(url, params, merge_mode) {

	let result 		= '';
	let qs 			= '';
	let matches 	= '';
	let url_params 	= '';
	let hash 		= '';

    if (params !== undefined) {

        // Build URL by merging params into url string.
        // matches[1] = url part that precedes params, not including trailing ?/#
        // matches[2] = params, not including leading ?/#
        // matches[3] = if in 'querystring' mode, hash including leading #, otherwise ''
        matches = url.match(/^([^#?]*)\??([^#]*)(#?.*)/);

        // Get the hash if in 'querystring' mode, and it exists
        hash = matches[3] || "";

        if (merge_mode === 2 && phIsString(params)) {
            // If merge_mode is 2 and params is a string, merge the fragment / query string into the URL wholesale, without converting it into an object
            qs = params.replace(/^.*\?|#.*$/g, "");
        } else {
            // Convert relevant params in url to object
            url_params = phUrlDeParam(matches[2]);

			// Convert passed params string into object
            params = phIsString(params)
				? phUrlDeParamQueryString(params) : params; // Passed params object
            qs = merge_mode === 2
				? params
				: merge_mode === 1
					// Url params override passed params
					? { ...params, ...url_params } : { ...url_params, ...params }; // Passed params override url params

            // Convert params object to a string.
            qs = phUrlParam(qs);
        }

        // Build URL from the base url, querystring and hash. In 'querystring' mode, ? is only added if a query string exists.
        result = matches[1] + (qs || !matches[1] ? "?" : "") + qs + hash;
    } else {
        // If URL was passed in, parse params from URL string, otherwise parse params from window.location.
        result = phGetQueryString(url !== undefined ? url : window.location.href);
    }

    return result;
}

// jQuery DeParam.querystring
function phUrlDeParamQueryString(url_or_params, coerce) {

	if (url_or_params === undefined || typeof url_or_params === "boolean") {
        // url_or_params not specified.
        coerce = url_or_params;
        url_or_params = phUrlParamQueryString();
    } else {
        url_or_params = phIsString(url_or_params) ? url_or_params.replace(/^.*\?|#.*$/g, "") : url_or_params;
    }

    return phUrlDeParam(url_or_params, coerce);
}


// ------
// Main
// ------

function phRemoveFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, uniqueValue, wait, source) {

    const phParams = Joomla.getOptions("phParamsPC");

    /* Set back phFilterNewUrlSet - it can happen that we set more parameter at once
     * typically parameter plus category id because of forcing category when setting some parameter
     * So be sure, phFilterNewUrlSet is cleaned (except id of category which is set in urlItemsView)
     */
    if (phParams["ajaxSearchingFilteringItems"] != 1) {
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
    let queryString = phUrlParamQueryString();
    queryString 	= phArrayToString(queryString);

    let paramsAll;
    if (phFilterNewUrlRemove !== "" || phFilterNewUrlRemovePreviousParamWaiting == 1) {
        paramsAll = phUrlDeParamQueryString(phFilterNewUrlRemove);
    } else {
        paramsAll = phUrlDeParamQueryString(queryString);
    }

    let paramsTypeStringNew = {};
    let mergeMode = 0;

    /* Handle pagination - when changing filter, set pagination to zero - to start */
    if (typeof paramsAll["start"] !== "undefined") {
        paramsTypeStringNew["start"] = 0;
    }
    if (typeof paramsAll["limitstart"] !== "undefined") {
        paramsTypeStringNew["limitstart"] = 0;
    }

    if (uniqueValue == 1) {
        delete paramsAll[paramV];
        paramsTypeStringNew = paramsAll;
        mergeMode = 2;
    } else if (typeof paramsAll[paramV] !== "undefined") {
        let paramsTypeString = paramsAll[paramV];
        let paramsTypeArray = paramsTypeString.split(",");
        paramsTypeArray = phCleanArray(paramsTypeArray);
        let findVal = paramsTypeArray.indexOf(value);

        if (findVal === -1) {
            // Value to remove is not there
        } else {
            // Value to remove is there
            let index = paramsTypeArray.indexOf(value);

            if (index > -1) {
                paramsTypeArray.splice(index, 1);
            }

            paramsTypeString = paramsTypeArray.join();

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

    /*let url;

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

    let url;

    if ((isItemsView == 1 && filteredProductsOnly != 1) || isItemsView != 1) {
        url = urlItemsView; // skip all parameters (a) search all products in items view or b) no items view

        if (url == "") {
            url = document.location.pathname;
        }

        document.location = url;
        return 1;
    } else {
        url = location.search; // complete url with selected parameters
    }

    // Set new url or take the one from previous parameter
    if (phFilterNewUrlRemove !== "" || phFilterNewUrlRemovePreviousParamWaiting == 1) {
        url = phFilterNewUrlRemove;
    }

    /* Array -> String */
    url = phArrayToString(url);

    phFilterNewUrlRemove = phUrlParamQueryString(url, paramsTypeStringNew, mergeMode); // one parameter only

    phFilterNewUrlRemove = phReplaceAll("%2C", ",", phFilterNewUrlRemove);
    phFilterNewUrlRemove = phReplaceAll("%5B", "[", phFilterNewUrlRemove);
    phFilterNewUrlRemove = phReplaceAll("%5D", "]", phFilterNewUrlRemove);
    phFilterNewUrlRemove = phReplaceAll("%3A", ":", phFilterNewUrlRemove);

    /* String -> Array */
    phFilterNewUrlRemove = phStringToArray(phFilterNewUrlRemove);
    phFilterNewUrlRemove = phCleanAloneQuestionMark(phFilterNewUrlRemove);

    // Wait for next parameter
    if (wait == 1) {
        // Don't reload, wait for other parameter
        phFilterNewUrlRemovePreviousParamWaiting = 1;

        if (isItemsView == 1 && phParams["ajaxSearchingFilteringItems"] == 1) {
            // and ajax
            return 2; // don't run overlay
        }
    } else {
        if (isItemsView == 1 && phParams["ajaxSearchingFilteringItems"] == 1) {
            phUpdatePageAndParts(phFilterNewUrlRemove, source); // Update Main, Search, Filter
            phResetNewUrl(1);
            return 2;
        } else {
            //document.location = phFilterNewUrlSet;
            if (phFilterNewUrlRemove == "") {
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

	const phParams = Joomla.getOptions("phParamsPC");

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
    let queryString = phUrlParamQueryString();
    queryString 	= phArrayToString(queryString);

    let paramsAll;
    if (/*phFilterNewUrlSet !== '' || */ phFilterNewUrlSetPreviousParamWaiting == 1) {
        phFilterNewUrlSet = phArrayToString(phFilterNewUrlSet); // wait back from () to [] so it can be read by querystring
        paramsAll = phUrlDeParamQueryString(phFilterNewUrlSet);
    } else {
        paramsAll = phUrlDeParamQueryString(queryString);
    }

    let paramsTypeStringNew = {};
    let mergeMode = 0;

    /* Handle pagination - when changing filter, set pagination to zero - to start */
    if (typeof paramsAll["start"] !== "undefined") {
        paramsTypeStringNew["start"] = 0;
    }
    if (typeof paramsAll["limitstart"] !== "undefined") {
        paramsTypeStringNew["limitstart"] = 0;
    }

    if (uniqueValue == 1) {
        paramsTypeStringNew[paramV] = value; // { param:value};// unique value - always overwrite old value
    } else if (value === "") {
    } else if (typeof paramsAll[paramV] !== "undefined") {
        let paramsTypeString = paramsAll[paramV];

        let paramsTypeArray = paramsTypeString.split(",");
        paramsTypeArray = phCleanArray(paramsTypeArray);

        let findVal = paramsTypeArray.indexOf(value);

        if (findVal === -1) {
            // New value is not there - add it
            paramsTypeArray.push(value);
            paramsTypeString = paramsTypeArray.join();
            paramsTypeStringNew[paramV] = paramsTypeString; //{ param:paramsTypeString};// Changed
        } else {
            // New value is there - don't change it
            paramsTypeStringNew[paramV] = paramsTypeString; //{ param:paramsTypeString};// Unchanged
        }
    } else {
        paramsTypeStringNew[paramV] = value; //{ param:value};
    }

    let url;
    if ((isItemsView == 1 && filteredProductsOnly != 1) || isItemsView != 1) {
        url = urlItemsView; // skip all parameters (a) search all products in items view or b) no items view
    } else {
        url = location.search; // complete url with selected parameters
    }

    // Set new url or take the one from previous parameter
    if (/*phFilterNewUrlSet !== '' ||*/ phFilterNewUrlSetPreviousParamWaiting == 1) {
        url = phFilterNewUrlSet;
    }

    /* Array -> String */
    url = phArrayToString(url);

    phFilterNewUrlSet = phUrlParamQueryString(url, paramsTypeStringNew, mergeMode); // one parameter only

    phFilterNewUrlSet = phReplaceAll("%2C", ",", phFilterNewUrlSet);
    phFilterNewUrlSet = phReplaceAll("%5B", "[", phFilterNewUrlSet);
    phFilterNewUrlSet = phReplaceAll("%5D", "]", phFilterNewUrlSet);
    phFilterNewUrlSet = phReplaceAll("%3A", ":", phFilterNewUrlSet);
    /* String -> Array */

    phFilterNewUrlSet = phStringToArray(phFilterNewUrlSet);
    phFilterNewUrlSet = phCleanEmptyParams(phFilterNewUrlSet);
    phFilterNewUrlSet = phCleanAloneQuestionMark(phFilterNewUrlSet);

    // Wait for next parameter
    if (wait == 1) {
        // Don't reload, wait for other parameter
        phFilterNewUrlSetPreviousParamWaiting = 1;

        if (isItemsView == 1 && phParams["ajaxSearchingFilteringItems"] == 1) {
            return 2; // don't run overlay
        }
    } else {
        if (isItemsView == 1 && phParams["ajaxSearchingFilteringItems"] == 1) {
            phUpdatePageAndParts(phFilterNewUrlSet, source); // Update Main, Search, Filter
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

	const phVars = Joomla.getOptions("phVarsModPhocacartFilter");
    const phParams = Joomla.getOptions("phParamsModPhocacartFilter");
    const isItemsView = phVars["isItemsView"];
    let isSef = phVars["isSef"];
    const urlItemsView = phVars["urlItemsView"];
    const urlItemsViewWithoutParams = phVars["urlItemsViewWithoutParams"];
    let phA = 1;

    if (formType == "itemview") {
        // Specific case for item view (no filtering but setting unique url for product with attributes)
        if (value === undefined) {
            value = "";
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

        if (phParams["removeParametersCat"] == 1) {
            document.location = urlItemsView;
        } else {
            let currentUrlParams = phUrlParamQueryString();
            if (isItemsView == 1) {
                if (isSef == 1) {
                    document.location = phUrlParamQueryString(urlItemsView, currentUrlParams, 2);
                } else {
                    phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
                }
            } else {
                document.location = urlItemsView;
            }
        }
    } else if (formType == "color" || formType == "image") {
        let isActive = jQuery(formAction).hasClass("on");
        if (isActive) {
            phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
            jQuery(formAction).removeClass("on");
        } else {
            phA = phSetFilter(paramV, value, isItemsView, urlItemsView, 1, uniqueValue, wait, source);
            jQuery(formAction).addClass("on");
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

/* Function phChangeSearch */
function phChangeSearch(paramV, value, formAction) {

	const phVars = Joomla.getOptions("phVarsModPhocacartSearch");
    const phParams = Joomla.getOptions("phParamsModPhocacartSearch");
    //const phVarsPC = Joomla.getOptions("phParamsPC");
    const isItemsView = phVars["isItemsView"];
    let urlItemsView = phVars["urlItemsView"];
    const urlItemsViewWithoutParams = phVars["urlItemsViewWithoutParams"];
    let phA = 1;

    let filteredProductsOnly = isItemsView;

    if (formAction == 1) {
        if (phParams["searchOptions"] == 1) {
            //jQuery("#phSearchBoxSearchAllProducts:checked").val();
            //jQuery("#phSearchBoxSearchAllProducts").attr("checked");
            //if (jQuery("#phSearchBoxSearchAllProducts:checked").length > 0) {
            // Class instead of ID because of more possible instances (desktop/mobile/...)
            if (jQuery(".phSearchBoxSearchAllProducts:checked").length > 0) {
                urlItemsView = urlItemsViewWithoutParams;
                filteredProductsOnly = 0; // When options are enabled and searching is set to all - we search without filtering
            }
        } else {
            filteredProductsOnly = 0; // When options are disabled we always search without filtering
        }
        phA = phSetFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, 1, 0, 2);
    } else {
        phA = phRemoveFilter(paramV, value, isItemsView, urlItemsView, filteredProductsOnly, 1, 0, 2);
    }
    
    // Possible search in offcanvas
    var phItemSearchBoxOffCanvas = document.getElementById('phItemSearchBoxOffCanvas');
    if(phItemSearchBoxOffCanvas) {
        var phItemSearchBoxOffCanvasBs = bootstrap.Offcanvas.getInstance(phItemSearchBoxOffCanvas);
        phItemSearchBoxOffCanvasBs.hide();
    }

    startFullOverlay(phA);
}

function phPriceFilterRange() {

	const phVars = Joomla.getOptions("phParamsPC");
    const phLang = Joomla.getOptions("phLangPC");

    // Filter Range
    if (typeof jQuery("#phPriceFilterRange").slider === "function") {
        jQuery("#phPriceFilterRange").slider({
            range: true,
            min: phVars["filterPriceMin"],
            max: phVars["filterPriceMax"],
            values: [phVars["filterPriceFrom"], phVars["filterPriceTo"]],
            slide: function (event, ui) {
                jQuery("#phPriceFromTopricefrom").val(ui.values[0]);
                jQuery("#phPriceFromTopriceto").val(ui.values[1]);
                jQuery("#phPriceFilterPrice").html("" + phLang["COM_PHOCACART_PRICE"] + ": " + phGetPriceFormat(ui.values[0]) + " - " + phGetPriceFormat(ui.values[1]));
            },
        });
    }

    jQuery("#phPriceFilterPrice").html("" + phLang["COM_PHOCACART_PRICE"] + ": " + phGetPriceFormat(phVars["filterPriceFrom"]) + " - " + phGetPriceFormat(phVars["filterPriceTo"]));

    jQuery("#phPriceFromTopricefrom").on("change", function (e) {

		let from = jQuery("#phPriceFromTopricefrom").val();
        let to = jQuery("#phPriceFromTopriceto").val();
        if (to == "") {
            to = phVars["filterPriceMax"];
        }
        if (from == "") {
            from = phVars["filterPriceMin"];
        }
        if (Number(to) < Number(from)) {
            to = from;
            jQuery("#phPriceFromTopriceto").val(to);
        }
        if (typeof jQuery("#phPriceFilterRange").slider === "function") {
            jQuery("#phPriceFilterRange").slider({ values: [from, to] });
        }
        jQuery("#phPriceFilterPrice").html("" + phLang["COM_PHOCACART_PRICE"] + ": " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));
    });

    jQuery("#phPriceFromTopriceto").on("change", function (e) {
        let from = jQuery("#phPriceFromTopricefrom").val();
        let to = jQuery("#phPriceFromTopriceto").val();
        if (to == "") {
            to = phVars["filterPriceMax"];
        }
        if (from == "") {
            from = phVars["filterPriceMin"];
        }
        if (Number(to) < Number(from)) {
            to = from;
            jQuery("#phPriceFromTopriceto").val(to);
        }
        if (typeof jQuery("#phPriceFilterRange").slider === "function") {
            jQuery("#phPriceFilterRange").slider({ values: [from, to] });
        }
        jQuery("#phPriceFilterPrice").html("" + phLang["COM_PHOCACART_PRICE"] + ": " + phGetPriceFormat(from) + " - " + phGetPriceFormat(to));
    });
}

function phClearField(field) {
    jQuery(field).val("");
    if (field == "#phPriceFromTopricefrom" || field == "#phPriceFromTopriceto") {
        phPriceFilterRange();
    }
}

// ------
// Events
// ------
jQuery(document).ready(function () {
    jQuery(".collapse")
        .on("shown.bs.collapse", function () {
            jQuery(this).parent().find(".glyphicon-triangle-right").removeClass("glyphicon-triangle-right").addClass("glyphicon-triangle-bottom");
            jQuery(this).parent().find(".fa-caret-right").removeClass("fa-caret-right").addClass("fa-caret-down");
            /* SVG */
            let useT = jQuery(this).parent().find("svg use").first();
            if (useT.length > 0) {
                let link = useT.attr("xlink:href");
                link = link.replace(
                    "#pc-si-triangle-right",
                    "#pc-si-triangle-bottom"
                );
                useT.attr("xlink:href", link);
            }
        })
        .on("hidden.bs.collapse", function () {
            jQuery(this).parent().find(".glyphicon-triangle-bottom").removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-right");
            jQuery(this).parent().find(".fa-caret-down").removeClass("fa-caret-down").addClass("fa-caret-right");
            /* SVG */
            let useT = jQuery(this).parent().find("svg use").first();
            if (useT.length > 0) {
                let link = useT.attr("xlink:href");
                link = link.replace("#pc-si-triangle-bottom", "#pc-si-triangle-right");
                useT.attr("xlink:href", link);
            }
        });

    phPriceFilterRange();
});
