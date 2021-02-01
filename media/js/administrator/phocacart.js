/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


function phGetMsg(msg, defaultMsg) {

	if (defaultMsg == 1) {
		return '<div id="ph-ajaxtop-message">'
		+ '<div id="ph-ajaxtop-close">x</div>'
		+ '<div class="ph-result-txt ph-info-txt">' + msg + '</div>'
		+ '<div class="ph-progressbar-bottom"></div>'
		+ '</div>';
	} else {
		return '<div id="ph-ajaxtop-close">x</div>'  + msg + '<div class="ph-progressbar-bottom"></div>';
	}

}

function phCloseMsgBoxSuccess() {

	setTimeout(function(){
		jQuery("#ph-ajaxtop").hide();
		jQuery(".ph-result-txt").remove();
	}, 2500);
	jQuery(".ph-progressbar-bottom").animate({
		width: "0%"
	}, 2500 );
}

function phCloseMsgBoxError() {

	setTimeout(function(){
		jQuery("#ph-ajaxtop").hide();
		jQuery(".ph-result-txt").remove();
	}, 3500);
	jQuery(".ph-progressbar-bottom").animate({
		width: "0%"
	}, 3500 );
}

/* Event - close ajax message on click */
jQuery(document).ready(function() {
	jQuery("#ph-ajaxtop").on("click", "#ph-ajaxtop-close", function() {
		jQuery("#ph-ajaxtop").hide();
	})
})




function phAddRowOptionParent(newRow, newHeader, attrid, url) {

	var phCountRowOption = jQuery('.ph-row-option-attrid' + attrid).length;
	if(phCountRowOption == 0) {
		jQuery('#phrowboxoptionjs' + attrid).append(newHeader);
	}
	jQuery('#phrowboxoptionjs' + attrid).append(newRow);

	var phMiniColorsId =  '#jform_optioncolor' + attrid + phRowCountOption;// Reload minicolors
	jQuery(phMiniColorsId).minicolors({
		control: 'hex',
		format: 'hex',
		position: 'default',
		theme: 'bootstrap'
	});

	/* Get and set the download token and download folder by ajax*/
	data = {};
	data['task'] = 'gettoken';

	phRowCountOptionBeforeAjax = phRowCountOption;/* AJAX returns the values after the phRowCountOption will be phRowCountOption++ in next rows*/
	phRequestActiveToken = jQuery.ajax({
		url: url,
		type: 'POST',
	   	data: data,
		dataType: 'JSON',
		success:function(response){
			if ( response.status == 1 ){
				var idFolder = '#jform_optiondownload_folder' + attrid + phRowCountOptionBeforeAjax;
				var idToken = '#jform_optiondownload_token' + attrid + phRowCountOptionBeforeAjax;
				jQuery(idFolder).val(response.folder);
				jQuery(idToken).val(response.token);
				phRequestActiveToken = null;
			} else {
				jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
				jQuery("#ph-ajaxtop").show();
				jQuery("#ph-ajaxtop-message").html(phGetMsg(response.error, 0));
				phRequestActiveToken = null;
				phCloseMsgBoxError();
			}
		}
	});

	phRowCountOption++;

	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});
}

function phRemoveOptionFolder(data, url) {



	phRequestActiveToken = jQuery.ajax({
		url: url,
		type: 'POST',
	   	data: data,
		dataType: 'JSON',
		success:function(response){
			if ( response.status == 1 ){
				jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
				jQuery("#ph-ajaxtop").show();
				jQuery("#ph-ajaxtop-message").html(phGetMsg(response.message, 0));
				phRequestActiveToken = null;
				phCloseMsgBoxSuccess();
			} else if (response.status == 2) {
				/* no folder exists - nothing deleted - no need any message */
				phRequestActiveToken = null;
			} else {
				jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
				jQuery("#ph-ajaxtop").show();
				jQuery("#ph-ajaxtop-message").html(phGetMsg(response.error, 0));
				phRequestActiveToken = null;
				phCloseMsgBoxError();
			}
		}
	});
}

function phRemoveRowOptionParent(id, attrid, url) {

	/* Remove download folder for deleted attribute option */
	var idDownloadFolder = '#jform_optiondownload_folder' + attrid + id;
	var downloadFolder = jQuery(idDownloadFolder).val();

	data = {};
	data['task'] 	= 'removefolder';
	data['folder'] 	= {0: downloadFolder};
	phRemoveOptionFolder(data, url);

	jQuery('#phOptionBox' + attrid + id).remove();
	var phCountRowOption = jQuery('.ph-row-option-attrid' + attrid).length;
	if (phCountRowOption == 0) {
		jQuery('#phrowboxoptionjs' + attrid).empty();/* clean header of option added by js */
		jQuery('#phrowboxoption' + attrid).empty();/* clean header of option loaded by php */
	}
	/* phRowCountOption--;//DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID*/
}


function phAddRowAttributeParent(newRow) {
	jQuery('#phrowboxattribute').append(newRow);
	phRowCountAttribute++;
	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});
}

function phRemoveRowAttributeParent(id, url) {

	/* Remove all attribute option folders */
	var attrOptions = jQuery("#phAttributeBox" + id).find("[data-attribute-id=\'" + id + "\']");
	var foldersToDelete = [];
	for(var i = 0; i < attrOptions.length; i++){
    	foldersToDelete.push(attrOptions[i].value);
	}

	if (foldersToDelete.length !== 0) {
		data = {};
		data['task'] 	= 'removefolder';
		data['folder'] 	= foldersToDelete;
		phRemoveOptionFolder(data, url);
	}

	jQuery('#phAttributeBox' + id).remove();
	var phCountRowAttribute = jQuery('.ph-row-attribute').length;
	if (phCountRowAttribute == 0) {
		jQuery('#phrowboxattribute').empty();
	}
	/* phRowCountAttribute--; DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID */
}

function phAddRowSpecificationParent(newRow, newHeader) {
	var phCountRowSpecification = jQuery('.ph-row-specification').length;
	if(phCountRowSpecification == 0) {
		jQuery('#phrowboxspecification').append(newHeader);
	}
	jQuery('#phrowboxspecification').append(newRow);

	var phMiniColorsId =  '#jform_speccolor' + phRowCountSpecification;// Reload minicolors

	jQuery(phMiniColorsId).minicolors({
		control: 'hex',
		format: 'hex',
		position: 'default',
		theme: 'bootstrap'
	});

	phRowCountSpecification++;
	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});
}

function phRemoveRowSpecification(id) {
	jQuery('#phSpecificationBox' + id).remove();
	var phCountRowSpecification = jQuery('.ph-row-specification').length;
	if (phCountRowSpecification == 0) {
		jQuery('#phrowboxspecification').empty();
		jQuery('#phrowboxspecificationheader').empty();
	}
}


function phAddRowImageParent(newRow) {
	jQuery('#phrowboximage').append(newRow);
	phRowCountImage++;
	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});
}

function phRemoveRowImage(id) {
	jQuery('#phrowimage' + id).remove();
	var phCountRowImage = jQuery('.ph-row-image').length;
	if (phCountRowImage == 0) {
		jQuery('#phrowboximage').empty();
	}
	/* phRowCountImage--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID*/
}

function phAddRowDiscountParent(newRow, newHeader, isCompatible) {
	var phCountRowDiscount = jQuery('.ph-row-discount').length;
	if(phCountRowDiscount == 0) {
		jQuery('#phrowboxdiscount').append(newHeader);
	}

	jQuery('#phrowboxdiscount').append(newRow);
	phRowCountDiscount++;
	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});

	if(isCompatible) {
		var elements = document.querySelectorAll(".field-calendar");
		for (i = 0; i < elements.length; i++) {
			JoomlaCalendar.init(elements[i]);
		}
	}
	/* jQuery(\'select\').trigger("chosen:updated");//Reload Chosen
	CALENDAR IS RELOADED DIRECTLY BELOW THE NEW ROW
	administrator\components\com_phocacart\libraries\phocacart\render\adminview.php*/
}

function phRemoveRowDiscount(id) {
	jQuery('#phDiscountBox' + id).remove();
	var phCountRowDiscount = jQuery('.ph-row-discount').length;
	if (phCountRowDiscount == 0) {
		jQuery('#phrowboxdiscount').empty();
		jQuery('#phrowboxdiscountheader').empty();
	}
}

function phAddRowPricehistoryParent(newRow, isCompatible) {
	jQuery('#phrowboxpricehistory').append(newRow);
	phRowCountPricehistory++;
	jQuery('select').chosen({disable_search_threshold : 10,allow_single_deselect : true});
	if(isCompatible) {
		var elements = document.querySelectorAll(".field-calendar");
		for (i = 0; i < elements.length; i++) {
				JoomlaCalendar.init(elements[i]);
		}
	}
	/* jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
	// CALENDAR IS RELOADED DIRECTLY BELOW THE NEW ROW
	administrator\components\com_phocacart\libraries\phocacart\render\adminview.php*/
}

function phRemoveRowPricehistory(id) {
	jQuery('#phPricehistoryBox' + id).remove();
	var phRowCountPricehistory = jQuery('.ph-row-pricehistory').length;
	if (phRowCountPricehistory == 0) {
		jQuery('#phrowboxpricehistory').empty();
	}
}



/* WIZARD */

function phDoRequestWizardParent(url, s) {

	var dataPost = {};
	phRequestActive = jQuery.ajax({
		url: url,
		type:'POST',
		data:dataPost,
		dataType:'JSON',
		success:function(data){
			if ( data.status == 1 ){

				/* Category */
				var phOutput = s["phFalse"];
				if (data.category == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardCategory").html(phOutput);

				/* Tax */
				var phOutput = s["phFalse"];
				if (data.tax == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardTax").html(phOutput);

				/* Product */
				var phOutput = s["phFalse"];
				if (data.product == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardProduct").html(phOutput);

				/* Shipping */
				var phOutput = s["phFalse"];
				if (data.shipping == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardShipping").html(phOutput);

				/* Payment */
				var phOutput = s["phFalse"];
				if (data.payment == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardPayment").html(phOutput);

				/* Country */
				var phOutput = s["phFalseAdd"];
				if (data.country == 1) { phOutput = s["phTrueAdd"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardCountry").html(phOutput);

				/* Region */
				var phOutput = s["phFalseAdd"];
				if (data.region == 1) { phOutput = s["phTrueAdd"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardRegion").html(phOutput);

				/* Menu */
				var phOutput = s["phFalse"];
				if (data.menu == 1) { phOutput = s["phTrue"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardMenu").html(phOutput);

				/* Module */
				var phOutput = s["phFalseAdd"];
				if (data.module == 1) { phOutput = s["phTrueAdd"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardModule").html(phOutput);

				/* Options */
				var phOutput = s["phFalseEdit"];
				if (data.option == 1) { phOutput = s["phTrueEdit"];} else {s["phTrueAll"] = 0;}
				jQuery("#phResultWizardOption").html(phOutput);

				if(s["phTrueAll"] == 1) {
					jQuery("#phResultWizardAll").css("display", "block")
				}

				phRequestActive = null;

			} else {
				/* No Displaying of error
		        jQuery("#phResultWizardCategory").html(data.error);*/
				phRequestActive = null;
			}
		}
	});
}


