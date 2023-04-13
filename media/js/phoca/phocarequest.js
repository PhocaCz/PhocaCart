/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

function phShowModal(element, options) {

	var phParams = Joomla.getOptions('phParamsPC');

	if (phParams['theme'] == 'uikit') {

		UIkit.modal(element, options);
		UIkit.modal(element).show();
	} else {
		var modal = new bootstrap.Modal(element, options);
		modal.show();
	}

}

function phAppendContainerRequest() {
	if (jQuery('#phContainerRequest').length === 0) {
		jQuery("body").append('<div id="phContainerRequest"></div>');
	}
	else {
		// phContainerRequest exists
	}

}

/* All popups can share one the same container but Quick View can be displayed together with another popup
 * so it must have own container
 */
function phAppendContainerRequestQickView() {
	if (jQuery('#phContainerRequestQuickView').length === 0) {
		jQuery("body").append('<div id="phContainerRequestQuickView"></div>');
	}
	else {
		// phContainerRequest exists
	}

}

function phDoRequestSuccess(data, options) {


	if (options['type'] == 'cart') {
		/* Add to cart, update cart */
		jQuery(options['class']).html(data.item);
		jQuery(options['class'] + "Count").html(data.count);
		jQuery(options['class'] + "Total").html(data.total);

		if (options['pos'] == 1) {
			var phUrlPos 	= phAddSuffixToUrl(window.location.href, 'format=raw');
			var phDataInput = phPosCurrentData("main.input");
			phDoSubmitFormUpdateInputBox(phDataInput, phUrlPos);// refresh input box
			jQuery(".ph-pos-message-box").html(data.message);// clear message box
			phPosManagePage();
		}

		if (options['method'] == 2) {
			phAppendContainerRequest();
			jQuery("#phContainerRequest").html(data.popup);
			//jQuery("#phAddToCartPopup").modal();
			//var modal = new bootstrap.Modal(document.getElementById("phAddToCartPopup"), {});
			//modal.show();
			phShowModal(document.getElementById("phAddToCartPopup"), {});

		}

		if (options['method'] == 1) {
			// If no popup is displayed we can reload the page when we are in specific view. If popup, this will be done when clicking continue
			if (options['view'] == 1) {
				startFullOverlay(1);
				setTimeout(function() {location.reload();}, 1);
			}
		}



	} else if (options['type'] == 'compare') {
		/* Comparison Add, Remove */
		jQuery(".phItemCompareBox").html(data.item);
		jQuery(".phItemCompareBoxCount").html(data.count);
		if (options['method'] == 2) {

			phAppendContainerRequest();
			jQuery("#phContainerRequest").html(data.popup);

			if (options['task'] == "add") {
				//jQuery("#phAddToComparePopup").modal();
				//var modal = new bootstrap.Modal(document.getElementById("phAddToComparePopup"), {});
				//modal.show();
				phShowModal(document.getElementById("phAddToComparePopup"), {});
			} else if (options['task'] == "remove") {
				//jQuery("#phRemoveFromComparePopup").modal();
				//var modal = new bootstrap.Modal(document.getElementById("phRemoveFromComparePopup"), {});
				//modal.show();
				phShowModal(document.getElementById("phRemoveFromComparePopup"), {});
			}

		}
		if (options['method'] == 1) {
			// If no popup is displayed we can reload the page when we are in specific view. If popup, this will be done when clicking continue
			if (options['view'] == 1) {
				startFullOverlay(1);
				setTimeout(function() {location.reload();}, 1);
			}
		}
	} else if (options['type'] == 'wishlist') {
		/* Wishlist Add, Remove */
		jQuery(".phItemWishListBox").html(data.item);
		jQuery(".phItemWishListBoxCount").html(data.count);
		if (options['method'] == 2) {
			phAppendContainerRequest();
			jQuery("#phContainerRequest").html(data.popup);
			if (options['task'] == "add") {
				//jQuery("#phAddToWishListPopup").modal();
				//var modal = new bootstrap.Modal(document.getElementById("phAddToWishListPopup"), {});
				//modal.show();
				phShowModal(document.getElementById("phAddToWishListPopup"), {});
			} else if (options['task'] == "remove") {
				//jQuery("#phRemoveFromWishListPopup").modal();
				//var modal = new bootstrap.Modal(document.getElementById("phRemoveFromWishListPopup"), {});
				//modal.show();
				phShowModal(document.getElementById("phRemoveFromWishListPopup"), {});
			}

		}
		if (options['method'] == 1) {
			// If no popup is displayed we can reload the page when we are in specific view. If popup, this will be done when clicking continue
			if (options['view'] == 1) {
				startFullOverlay(1);
				setTimeout(function() {location.reload();}, 1);
			}
		}
	} else if (options['type'] == 'quickview') {
		/* Quick View */

		jQuery(".phjItemQuick.phjProductAttribute").remove();// Clear attributes from dom when ajax reload
		//jQuery("body").append(jQuery("#phContainer"));
		phAppendContainerRequestQickView();
		jQuery("#phContainerRequestQuickView").html(data.popup);
		//jQuery("#phContainer").html(data.popup);
		//jQuery("body").append(jQuery("#phQuickViewPopup"));
		//jQuery("#phQuickViewPopup").modal();
		//var modal = new bootstrap.Modal(document.getElementById("phQuickViewPopup"), {});
		//modal.show();
		phShowModal(document.getElementById("phQuickViewPopup"), {});
		if (options['load_chosen'] > 0) {
			jQuery('select').chosen('destroy').chosen({disable_search_threshold : 10,allow_single_deselect : true});
		}
		phChangeAttributeType('ItemQuick');

		if (options['quantity_input_spinner'] > 0) {
			jQuery("input[name='quantity']:visible").TouchSpin({
				verticalbuttons: true,
				verticalup: options["icon_spinner_verticalup"],
				verticaldown: options["icon_spinner_verticaldown"]
			})
		}
	} else if (options['type'] == 'changedata') {


		/* Change Image */
		if( data.item.image !== undefined && data.item.image !== '' ) {


			if (options['method_image'] == 2) {
				var phProductImg	= '.phjProductImage' + options["id_item_name"];
				var phProductSource	= '.phjProductSource' + options["id_item_name"];// Webp source
				var phProductHref	= '.phjProductHref' + options["id_item_name"];


				// New image found - change to new image
				jQuery(phProductHref).attr('href', data.item.image);
				jQuery(phProductImg).attr('src', data.item.image);
				jQuery(phProductSource).attr('srcset', data.item.image);//webp
			}
		}

		/* Change Price */
		if( data.item.price !== undefined ) {
			jQuery(options["id_item_price"]).html(data.item.price);
			// Change also Gift voucher if displayed
			jQuery(options["id_item_price_gift"]).html(data.item.priceitems.bruttoformat);

		}

		/* Change ID (SKU, EAN, ...) */
		if( data.item.id !== undefined ) {
			jQuery(options["id_item_id"]).html(data.item.id);
		}

		if( data.item.stock !== undefined ) {
			if (options['method_stock'] == 1) {

				//if (data.item.stockvalue < 1) {
				if (data.item.hideaddtocart == 1) {
					//jQuery(phProductAddToCart).hide();';
					jQuery(options["product_add_to_cart_item"]).css('visibility', 'hidden');
					jQuery(options["product_add_to_cart_item_icon"]).css('display', 'none');

				} else {
					//jQuery(phProductAddToCart).show();';
					jQuery(options["product_add_to_cart_item"]).css('visibility', 'visible');
					jQuery(options["product_add_to_cart_item_icon"]).css('display', 'block');
				}
			}

			jQuery(options["id_item_stock"]).html(data.item.stock);
		}

	} /*else if (options['type'] == 'changeprice') {
		/* Change Price *//*
		jQuery(options["id_item"]).html(data.item);
	}  else if (options['type'] == 'changeid') {
		/* Change ID (SKU, EAN, ...) *//*
		jQuery(options["id_item"]).html(data.item);
	} else if (options['type'] == 'changestock') {
		/* Change Stock *//*


		if (options['method'] == 1) {

			if (data.stock < 1) {
            	//jQuery(phProductAddToCart).hide();';
            	jQuery(options["product_add_to_cart_item"]).css('visibility', 'hidden');
            	jQuery(options["product_add_to_cart_item_icon"]).css('display', 'none');

            } else {
            	//jQuery(phProductAddToCart).show();';
            	jQuery(options["product_add_to_cart_item"]).css('visibility', 'visible');
            	jQuery(options["product_add_to_cart_item_icon"]).css('display', 'block');
            }
		}

		jQuery(options["id_item"]).html(data.item);
	}*/
}

function phDoRequestError(data, options) {

	if (options['type'] == 'cart') {
		/* Add to cart, update cart */

		if (options['pos'] == 1) {
			jQuery(".ph-pos-message-box").html(data.error);// clear message box
			phPosManagePage();
		}

		if (options['method'] != 2) {
			jQuery(".phItemCartBox").html(data.error);
		}

		if (options['method'] == 2) {
			phAppendContainerRequest();
			jQuery("#phContainerRequest").html(data.popup);
			//jQuery("#phAddToCartPopup").modal();
			//var modal = new bootstrap.Modal(document.getElementById("phAddToCartPopup"), {});
			//modal.show();
			phShowModal(document.getElementById("phAddToCartPopup"), {});


		}

		if (options['method'] == 1) {
			// If no popup is displayed we can reload the page when we are in specific view. If popup, this will be done when clicking continue
			if (options['view'] == 1) {
				startFullOverlay(1);
				setTimeout(function() {location.reload();}, 1);
			}
		}
	}
}


/*
 * type ... compare
 * view ... is compare view or not
 * task ... add/remove
 * method ... popup method: no ajax, ajax without popup, ajax with popup
 * url ... ajax url
 * data ... ajax data
 */

function phDoRequestMethods(url, data, options) {

	jQuery.ajax({
		type: "POST",
		url: url,
		async: "false",
		cache: "false",
		data: data,
		dataType:"JSON",
		success: function(data){
			if (data.status == 1){
				phDoRequestSuccess(data, options);
		   	} else if (data.status == 0){
				phDoRequestError(data, options);
			} else {
				// No change
		   	}
		}
	})
	return false;
}


// ------
// Events
// ------
jQuery(document).ready(function(){

	/* Add to cart, update cart */
	// :: EVENT (SUBMIT) Item View
	//jQuery(".phItemCartBoxForm").on(\'submit\', function (e) {// Not working when form is added by ajax
	jQuery(document).on("submit", "form.phItemCartBoxForm", function (e) { // Works with forms added by ajax


		if (typeof phDoSubmitFormAddToCart === "function") {
			e.preventDefault();
			var sFormData = jQuery(this).serialize();
			phDoSubmitFormAddToCart(sFormData);
		}
	})

	/* Update cart  only in POS */
	// ::EVENT (CLICK) Change Layout Type Clicking on Grid, Gridlist, List
	jQuery(document).on("click", "#ph-pc-pos-site form.phItemCartUpdateBoxForm button", function (e) {

		if (typeof phDoSubmitFormAddToCart === "function") {
			e.preventDefault();
			var sForm 	= jQuery(this).closest("form");// Find in which form the right button was clicked
			var phAction= jQuery(this).val();
			var sFormData = sForm.serialize() + "&action=" + phAction;
			phDoSubmitFormUpdateCart(sFormData);
		}
	})
})
