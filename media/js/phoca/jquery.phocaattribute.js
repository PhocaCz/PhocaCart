/*
 * jQuery Phoca Attribute
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */

/* SELECT BOXES */
/* Changes Selects to color or image attributes*/

function phChangeAttributeType(typeView) {

	typeView = typeof typeView !== 'undefined' ? '.phj' + typeView : '';

	var phProductAttribute = typeView + '.phjProductAttribute';// Find all select boxes which should be transformed to color or image
	var phCleanAttribute = typeView + '.phjCleanAttribute';// Clean previously transformed select boxes in case of ajax reload

	jQuery(phCleanAttribute).remove();


	jQuery(phProductAttribute).each(function() {

		var phClass = '';
		var phClassId = '';

		var phSelectNameId	= '#phItemAttribute' + jQuery(this).data('attribute-id-name');
		var phSelectNameIdT	= '#phItemHiddenAttribute' + jQuery(this).data('attribute-id-name');
		var phSelectNameIdB	= '#phItemBoxAttribute' + jQuery(this).data('attribute-id-name');
		var phType			= jQuery(this).data('attribute-type');// Type of attribute
		var phTypeView		= jQuery(this).data('type-view');// Type of view, e.g. ItemQuickView is reloaded by ajax
		var phTypeIcon 		= jQuery(this).data('type-icon');// Type of used icon library
		var phRequired	 	= jQuery(this).data('required');// Type of used icon library

		if (phType == 3) {
			phClass			= 'phSelectBoxImage';// Image
		} else if (phType == 2) {
			phClass			= 'phSelectBoxButton';// Color
		}

		// Transform only attributes which are select box image - 3 or select box color - 2
		if (phClass != '') {

			phClassId			= phSelectNameIdT + ' .' + phClass;
			var phSelectName 	= jQuery(phSelectNameId).attr('name');
			var phHiddenEl 		= jQuery('<input type="hidden" name="'+ phSelectName +'">');
			phHiddenEl.val(jQuery(phSelectNameId).val());
			phHiddenEl.insertAfter(jQuery(phSelectNameId));


			// ON START DISPLAY OR HIDE
			// jQuery(phSelectNameId).hide();
			// jQuery(phSelectNameIdB).hide();
			// Cannot be hidden because of html5 required field and its message
			// Hide select box even its chosen alternative
			jQuery(phSelectNameIdT).css( "display", "block");
			jQuery(phSelectNameIdB).css( {"display": "visible", 'position': 'absolute', 'clip': 'rect(0,0,0,0)' });
			jQuery(phSelectNameIdB).addClass('phj' + phTypeView + ' phjCleanAttribute');

			// ON START TRANSFORM
			jQuery(phSelectNameId + ' option').each(function() {

				/* Do not display default value (empty value), can be set by clicking back from other value */
				if (jQuery(this).val() != '') {

					if (phType == 3) {
						// Image
						var phSBtn = jQuery('<div class="'+ phClass	+' '+ phTypeIcon +'" data-value="'+ jQuery(this).val() +'" data-value-alias="'+ jQuery(this).data('value-alias') +'" title="'+ jQuery(this).text() +'"><img src="'+ jQuery(this).data('image') +'" alt="'+ jQuery(this).text() +'" /></div>');
					} else if (phType == 2) {
						// Color
						var phSBtn = jQuery('<div class="'+ phClass +' '+ phTypeIcon +'" style="background-color:' + jQuery(this).data('color') +'" data-value="'+ jQuery(this).val() +'" data-value-alias="'+ jQuery(this).data('value-alias') +'" title="'+ jQuery(this).text() +'">'+ '&nbsp;' +'</div>');
					}


					if(jQuery(this).is(':selected')) {
						phSBtn.addClass('on');
					}

					jQuery(phSelectNameIdT).append(phSBtn);

				}

			});


			// Change on Click event
			jQuery(phClassId).on('click', function(e) {

				e.preventDefault();// Bootstrap modal (close and open again duplicates events)

				var isActive = jQuery(this).hasClass('on');

				if (isActive) {
					if (phRequired == 1) {
						e.preventDefault();// Active item cannot be unselected when the select box is required
						return false;
					}
					jQuery(this).removeClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val('');
					jQuery(phSelectNameId).val('').change();// Because of required field
				} else {
					jQuery(phClassId).removeClass('on');//Remove when multiple
					jQuery(this).addClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val(jQuery(this).data('value'));
					jQuery(phSelectNameId).val(jQuery(this).data('value')).change();// Because of required field
				}
			})

		}

	})
}

function phAjaxChangeAttributeData(phProductId, phTypeView, phDataA1, phDataA2){

	var phParams 	= Joomla.getOptions('phParamsPC');
	var phVars 		= Joomla.getOptions('phVarsPC');
	var phUrl 		= phVars['urlCheckoutChangeData'];
    var phOptions 	= [];
    phOptions["id"] = phProductId;
    phOptions["id_item_price"] = "#phItemPriceBox" + phTypeView + phProductId;
    phOptions["id_item_stock"] = "#phItemStockBox" + phTypeView + phProductId;
	phOptions["id_item_id"] = "#phItemIdBox" + phTypeView + phProductId;
	phOptions["id_item_name"] = "V" + phTypeView + 'P' + phProductId;
    phOptions["product_add_to_cart_item"] 		= ".phProductAddToCart" + phTypeView + phProductId;// display or hide add to cart button
    phOptions["product_add_to_cart_item_icon"] 	= ".phProductAddToCartIcon" + phTypeView + phProductId;// display or hide add to cart icon
	phOptions["view"] = phTypeView;
	phOptions["method_price"]  = phParams['dynamicChangePrice'];
	phOptions["method_stock"]  = phParams['dynamicChangeStock'];
	phOptions["method_id"]  = phParams['dynamicChangeId'];
	phOptions["method_image"]  = phParams['dynamicChangeImage'];
	phOptions["task"]  = "change";
	phOptions["type"]  = "changedata";
	if (phTypeView == 'ItemQuick' || phTypeView == 'Pos' || phTypeView == 'Item') {
		phOptions["class"] ='ph-item-data-box';
	} else {
		phOptions["class"] ='ph-category-data-box';// Category, Items
	}
	var phData 	= 'id='+ phOptions["id"] +'&'+ phDataA1 +'&'+ phDataA2 +'&'+'class='+ phOptions["class"] +'&'+'typeview='+ phOptions["view"];
    phDoRequestMethods(phUrl, phData, phOptions);
}

function phSetAttributeUrl(phSetValueByUser) {


	var phParams			= Joomla.getOptions('phParamsPC');
	var phVars 				= Joomla.getOptions('phVarsPC');

	if (phParams['dynamicChangeUrlAttributes'] != 1) {
		return false;
	}

	if(phVars['view'] != 'item') {
		return false;
	}

	var phHash				= jQuery(location).attr('hash');
	phHash					= phReplaceAll('#', '', phHash)
	var phHashParams 		= jQuery.deparam(phHash);
	var phProductAttribute 	= '.phjProductAttribute';
	var phHashNew 			= '';
	var phTypeView 			= '';
	var phProductId 		= '';

	// Find all attributes in Item View
	jQuery(phProductAttribute).each(function() {

		if (phHashNew != '') {
			phHashNew = phHashNew + '&';
		}

		var attributeId 	= this.id;
		var attributeAlias 	= jQuery(this).data('alias');
		var valueAlias 		= '';// One value for selectbox
		var valuesAlias 	= '';// One or more values for checkbox
		var phSelectNameIdT	= '#phItemHiddenAttribute' + jQuery(this).data('attribute-id-name');


		if (phSetValueByUser == 1) {
			// 1) Set by user

			// SELECTBOX
			if (jQuery(this).find(':selected').data('value-alias') !== undefined) {
				valueAlias = jQuery(this).find(':selected').data('value-alias');
				phHashNew = phHashNew + 'a[' + jQuery(this).data('alias') + ']=' + valueAlias;
			}

			// CHECKBOX
			if (jQuery(this).find(':input:checked').data('value-alias') !== undefined) {
				jQuery.each(jQuery(this).find(':input:checked'), function(){

					if (valuesAlias != '') {
						valuesAlias = valuesAlias + ',';
					}
					valuesAlias = valuesAlias + jQuery(this).data('value-alias');
				});

				if (valuesAlias != '') {
					phHashNew = phHashNew + 'a[' + jQuery(this).data('alias') + ']=' + valuesAlias;
				}
			}


		} else {

			// 2) Set on document load
			if (phHashParams.a !== undefined && attributeAlias !== undefined && phHashParams.a[attributeAlias] !== undefined) {

				// 2a) Set by the URL parameters - e.g. direct link - attributes will be set on the site by the URL

				phHashNew = phHashNew + 'a[' + jQuery(this).data('alias') + ']=' + phHashParams.a[attributeAlias];

				var arrayValues = phHashParams.a[attributeAlias].split(",");

				if (arrayValues !== undefined || arrayValues.length != 0) {

					// clea all selected values first
					jQuery("#" + attributeId + " option").removeAttr("selected");// Select box
					jQuery(phSelectNameIdT + " div").removeClass('on'); // Select Color or Image
					jQuery("#" + attributeId + " input").removeAttr("checked");// Check box

					jQuery(arrayValues).each(function() {
						jQuery("#" + attributeId + " option[data-value-alias='" + this + "']").attr("selected","selected");// Select box
						jQuery(phSelectNameIdT + " div[data-value-alias='" + this + "']").addClass('on'); // Select Color or Image
						jQuery("#" + attributeId + " input[data-value-alias='" + this + "']").attr("checked","checked");// Check box
					})
				}
			} else {

				// 2b) Attribute not found in URL so change the URL be values set as default (default value for attribute)

				// SELECT BOX
				if (jQuery(this).find(':selected').data('value-alias') !== undefined) {
					valueAlias = jQuery(this).find(':selected').data('value-alias');
					phHashNew = phHashNew + 'a[' + jQuery(this).data('alias') + ']=' + valueAlias;
				}

				// CHECKBOX
				if (jQuery(this).find(':input:checked').data('value-alias') !== undefined) {
					jQuery.each(jQuery(this).find(':input:checked'), function(){

						if (valuesAlias != '') {
							valuesAlias = valuesAlias + ',';
						}
						valuesAlias = valuesAlias + jQuery(this).data('value-alias');
					});

					if (valuesAlias != '') {
						phHashNew = phHashNew + 'a[' + jQuery(this).data('alias') + ']=' + valuesAlias;
					}
				}
			}

			// Accessible in each parameter - will be used for ajax if set in parameters
			phTypeView = jQuery(this).data('type-view');
			phProductId = jQuery(this).data('product-id');
		}
	})

	// URL parameter (hash) can change the setting of attributes, if changed then ajax which changes stock, price and ID (EAN, SKU), image needs to be run
	if (phParams['dynamicChangePrice'] == 0 && phParams['dynamicChangeStock'] == 0 && phParams['dynamicChangeId'] == 0 && (phParams['dynamicChangeImage'] == 0 || phParams['dynamicChangeImage'] == 1)) {
		//Interactive change is disabled
	} else {
		var phProductGroup = '.phjAddToCartV' + phTypeView + 'P' + phProductId;
		var phDataA1 = jQuery(phProductGroup).find('select').serialize();// All Selects
		var phDataA2 = jQuery(phProductGroup).find(':checkbox').serialize();// All Checkboxes
		phAjaxChangeAttributeData(phProductId, phTypeView, phDataA1, phDataA2);
	}

	// Last character &
	if (phHashNew.lastIndexOf('&') == (phHashNew.length - 1)) {
		phHashNew = phHashNew.slice(0, -1);
	}
	// Update URL after #
	if (phSetValueByUser == 0 && phHashNew != '') {
		phHashNew = '#' + phHashNew;
		window.history.pushState({},"", phHashNew);
	} else if (phSetValueByUser == 1) {
		phHashNew = '#' + phHashNew;
		window.history.pushState({},"", phHashNew);
	}
}

jQuery(document).ready(function() {

	phChangeAttributeType();// Change select to color or image, change checkbox to color or image
	phSetAttributeUrl(0);// Change URL (# attributes) or change attributes by URL at start


	/* Interactive change - price, stock, ID (EAN, SKU, ...) */
	var phSelectboxA            =  "select.phjProductAttribute";
	var phSelectboxASelected	=  phSelectboxA + ":selected";
	// Select box
	jQuery(document).on('change', phSelectboxA, function(e){



		var phParams = Joomla.getOptions('phParamsPC');
		if (phParams['dynamicChangePrice'] == 0 && phParams['dynamicChangeStock'] == 0 && phParams['dynamicChangeId'] == 0 && (phParams['dynamicChangeImage'] == 0 || phParams['dynamicChangeImage'] == 1)) {
			return false;// Interactive Change is disabled
		}

		//jQuery(this).off("change");';
		var phTypeView = jQuery(this).data('type-view');
		var phProductId = jQuery(this).data('product-id');
		var phProductGroup = '.phjAddToCartV' + phTypeView + 'P' + phProductId;
		var phDataA1 = jQuery(phProductGroup).find('select').serialize();// All Selects
		var phDataA2 = jQuery(phProductGroup).find(':checkbox').serialize();// All Checkboxes
		phAjaxChangeAttributeData(phProductId, phTypeView, phDataA1, phDataA2);
		phSetAttributeUrl(1);
	})


	var phCheckboxA             =  ".ph-checkbox-attribute.phjProductAttribute";
    // var phCheckboxAInputChecked =  phCheckboxA + " input:checked";
	// Checkbox
	jQuery(document).on('click', phCheckboxA, function(e){


		var phParams = Joomla.getOptions('phParamsPC');
		if (phParams['dynamicChangePrice'] == 0 && phParams['dynamicChangeStock'] == 0 && phParams['dynamicChangeId'] == 0 && (phParams['dynamicChangeImage'] == 0 || phParams['dynamicChangeImage'] == 1)) {
			return false;// Interactive Change is disabled
		}

		if (e.target.tagName.toUpperCase() === "LABEL") { return;}// Prevent from twice running
        if (phParams['theme'] == 'bs4') {

			if (e.target.tagName.toUpperCase() === "SPAN" || e.target.tagName.toUpperCase() === "IMG") {  return;}// Prevent from twice running
		}



		var phProductId = jQuery(this).data('product-id');
        var phTypeView = jQuery(this).data('type-view');
        var phProductGroup = '.phjAddToCartV' + phTypeView + 'P' + phProductId;
        var phDataA1 = jQuery(phProductGroup).find('select').serialize();// All Selects
        var phDataA2 = jQuery(phProductGroup).find(':checkbox').serialize();// All Checkboxes

        // If REQUIRED, don't allow to untick all checkboxes
        var phRequired = jQuery(this).data("required");
        var phCheckboxAInputChecked =  "#" + jQuery(this).attr("id") + " input:checked";
        var phACheckedLength = jQuery(phCheckboxAInputChecked).length;

        if (phACheckedLength == 0) {
            var phThisLabel = jQuery(e.target).parent();// Bootstrap checkboxes - colors, images
            phThisLabel.addClass("active");// Bootstrap checkboxes - colors, images
            e.preventDefault();
            return false;
        }

		phAjaxChangeAttributeData(phProductId, phTypeView, phDataA1, phDataA2);
		phSetAttributeUrl(1);
    })
})
