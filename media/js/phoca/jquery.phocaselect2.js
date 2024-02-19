/*
 * jQuery Phoca Select2
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */


(function (jQuery) {
  "use strict";

  var phLang = Joomla.getOptions('phLang');

  jQuery.extend(jQuery.fn.select2.defaults, {
		formatNoMatches: function () { return phLang['COM_PHOCACART_NO_MATCHES_FOUND']; },
		formatInputTooShort: function (input, min) { var n = min - input.length; return phLang['COM_PHOCACART_PLEASE_ENTER'] + " " + n + " " + phLang['COM_PHOCACART_S_MORE_CHARACTER'] + (n == 1? "" : "s"); },
		formatInputTooLong: function (input, max) { var n = input.length - max; return phLang['COM_PHOCACART_PLEASE_DELETE'] + " " + n + " " + phLang['COM_PHOCACART_S_CHARACTER'] + (n == 1? "" : "s"); },
		formatSelectionTooBig: function (limit) { return phLang['COM_PHOCACART_YOU_CAN_ONLY_SELECT'] + " " + limit + " " + phLang['COM_PHOCACART_S_ITEM'] + (limit == 1 ? "" : "s"); },
		formatLoadMore: function (pageNumber) { return phLang['COM_PHOCACART_LOADING_MORE_RESULTS'] + " ..."; },
		formatSearching: function () { return phLang['COM_PHOCACART_SEARCHING'] + " ..."; }
  });
})(jQuery);

function phSearchItemsMultiple(element, url, id, multiple, splitChar, maxSize) {

	var maxSizeValue = 0;
	if (typeof maxSize !== 'undefined') {
		maxSizeValue = maxSize;
	}

	jQuery(element).select2({
		//dropdownAutoWidth : true,
		//width: "auto",
		placeholder: "",
		minimumInputLength: 1,
		maximumSelectionSize: maxSizeValue,
		multiple: multiple,
		ajax: {
			url: url,
			dataType: 'json',
			data: function(term, page) {
				return {
					q: term,
					page_limit: 10,
					item_id: id,
				}
			},
			results: function(data, page) {
				if ( data.status == 0 ){
					return { results: data.error }
				} else {
					return { results: data.items }
				}
			}
		},
		formatResult: formatResult,
		formatSelection: formatSelection,
		initSelection: function(element, callback) {
			var data = [];
			jQuery(element.val().split("" + splitChar + "")).each(function(i) {
				var item = this.split(':');
				data.push({
					id: item[0],
					title: item[1]
				});
			});

			if (multiple == false) {
				// No multiple
				callback(data[0]);
			} else {
				// Multiple
				// Cannot be set when single product because the input will be empty at start (now it is including string but when saving, string will be changed to int)
				jQuery(element).val('');
				callback(data);
			}
		}
	})

}

function formatResult(item) {
	var phVars = Joomla.getOptions('phVars');
	let line = '<div class="ph-select-search-result"><span class="ph-select-search-result-image">';
	if (item.image ?? null) {
		line += '<img src="' + phVars['uriRoot'] + item.image + '"/>';
	}
	line += '</span>';
	line += '<span class="ph-select-search-result-title">' + (item.title ?? '') + '</span>';
	line += '<span class="ph-select-search-result-codes">';
	if (item.sku ?? null) {
		line += Joomla.Text._('COM_PHOCACART_SKU') + ': ' + item.sku;
	}
	if (item.ean ?? null) {
		if (item.sku ?? null) {
			line += ', ';
		}
		line += Joomla.Text._('COM_PHOCACART_EAN') + ': ' + item.ean;
	}
	line += '</span>';
	line += '<span class="ph-select-search-result-categories">' + (item.categories_title ?? '') + '</span>';
	line += '</div>';

	return line;
}

function formatSelection(data) {

	// Menu link - we need to select category in menu link too
	// Options of categories will be loaded by ajax
    if(data.categories && jQuery("#jform_request_catid").length) {
		jQuery("#jform_request_catid option").remove();
		jQuery(data.categories.split(",")).each(function(i) {
			var itemC = this.split(':');
        	jQuery("#jform_request_catid").append(jQuery('<option>', {value: itemC[0], text: itemC[1]}));
       	});
	   	jQuery("select").trigger("liszt:updated");
	   	jQuery("select").trigger("chosen:updated");
		//jQuery(".inputbox").chosen({disable_search_threshold : 10,allow_single_deselect : true});
    }
	// End Menu link

    return data.title;
 };

