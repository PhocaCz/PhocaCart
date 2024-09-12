/*
 * @package   Phoca Component
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
/*
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
*/

/* -------------------- */
function phChangeBackground(element, seconds, color) {

    var originalColor = jQuery(element).css("background");
	jQuery(element).css("background", color);
	setTimeout(function(){
  		jQuery(element).css("background", originalColor);
	}, seconds);
}

function phEscapeColon(element) {
    return element.replace(/:/g, '\\:');
}

function phEditInPlaceMsg(msg, type) {

    jQuery("#ph-ajaxtop").html(phGetMsg( '&nbsp;', 1));
    jQuery("#ph-ajaxtop").show();
    jQuery("#ph-ajaxtop-message").html(phGetMsg(msg, 0));

    if (type == 0) {
        phCloseMsgBoxError();
    } else {
        phCloseMsgBoxSuccess();
    }

}

function phEditInPlacePasteAndMark(element, json) {

    /* combined input means title and alias (both editable) or date and dateformat (only date editable) */
        if (json.idcombined && json.resultcombined) {
        var combinedElement = "#" + phEscapeColon(json.idcombined);
        jQuery(combinedElement).html(json.resultcombined);
        phChangeBackground(combinedElement, 700, "#D4E9E6");
    }

    var currentElement = "#" + phEscapeColon(element);
    phChangeBackground(currentElement, 700, "#D4E9E6" );
}



jQuery(document).ready(function() {

    var phVars = Joomla.getOptions('phVars');
    var phLang = Joomla.getOptions('phLang');

    jQuery(".ph-editinplace-text.ph-eip-text").editable(phVars['urleditinplace'], {

        tooltip : phLang['PHOCA_CLICK_TO_EDIT'],
        select : true,
        type : "text",
        cancel : phLang['PHOCA_CANCEL'],
        submit : phLang['PHOCA_SUBMIT'],
        cssclass : 'ph-edit-in-place-class input-group',
        inputcssclass : 'form-control form-control-sm',
        cancelcssclass : 'btn btn-danger btn-sm',
        submitcssclass : 'btn btn-success btn-sm',

        submitdata : {type: "text"},

        before : function(e) {
            /* set height to not jump - e.g. description, if 5 row description changes in popup form to 3 row */
            var height = jQuery(e.currentTarget).height();// e.target // outerHeight()
            jQuery(e.currentTarget).height(height);
            //var height = e.currentTarget.offsetHeight;
            //e.currentTarget.setAttribute("style","height:" + height + "px");
        },

        //DEBUG
        //onblur : function() { ... },

        intercept : function(jsondata) {
            json = JSON.parse(jsondata);

            /* return back from fixed height */
            jQuery(this).css("height", "");

            if (json.status == 0){
                phEditInPlaceMsg(json.error, 0)
                this.reset();
            } else {
                var id = jQuery(this).attr("id");
                phEditInPlacePasteAndMark(id, json);
                return json.result;
            }
        },

        placeholder: "",

        // Possible information for parts on the site which will be not changed by chaning the value (for example currency view - currency rate)
        callback: function() {
            var chEIP = ".phChangeEditInPlace" + jQuery(this).attr("data-id");
            jQuery(chEIP).html(phLang['PHOCA_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION'])
        },

    })

    jQuery(".ph-editinplace-text.ph-eip-autogrow").editable(phVars['urleditinplace'], {

        tooltip : phLang['PHOCA_CLICK_TO_EDIT'],
        //select : true,
        type : "autogrow",
        cancel : phLang['PHOCA_CANCEL'],
        submit : phLang['PHOCA_SUBMIT'],
        cssclass : 'ph-edit-in-place-class input-group',
        inputcssclass : 'form-control form-control-sm',
        cancelcssclass : 'btn btn-danger btn-sm',
        submitcssclass : 'btn btn-success btn-sm',

        submitdata : {type: "autogrow"},

        before : function(e) {
            /* set height to not jump */
            var height = jQuery(e.target).height();//outerHeight()
            jQuery(e.target).height(height);
        },
        // DEBUG
        //onblur : function() {  ... },

        intercept : function(jsondata) {

            json = JSON.parse(jsondata);

            /* return back from fixed height */
            jQuery(this).css("height", "");

            if (json.status == 0){
                phEditInPlaceMsg(json.error, 0)
                this.reset();
            } else {
                var id = jQuery(this).attr("id");
                phEditInPlacePasteAndMark(id, json);
                return json.result;
            }
        },

        placeholder: "",

        // Possible information for parts on the site which will be not changed by chaning the value (for example currency view - currency rate)
        callback: function() {
            var chEIP = ".phChangeEditInPlace" + jQuery(this).attr("data-id");
            jQuery(chEIP).html(phLang['PHOCA_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION'])
        },

    })

    jQuery(".ph-editinplace-text.ph-eip-date").editable(phVars['urleditinplace'], {

        tooltip : phLang['PHOCA_CLICK_TO_EDIT'],
        select : true,
        type : "masked",
        mask : "9999-99-99",
        cancel : phLang['PHOCA_CANCEL'],
        submit : phLang['PHOCA_SUBMIT'],
        cssclass : 'ph-edit-in-place-class input-group',
        inputcssclass : 'form-control form-control-sm',
        cancelcssclass : 'btn btn-danger btn-sm',
        submitcssclass : 'btn btn-success btn-sm',

        submitdata : {type: "date", dateformat : phVars['dateformat']},

        before : function(e) {
            /* set height to not jump */
            var height = jQuery(e.currentTarget).height();// e.target // outerHeight()
            jQuery(e.currentTarget).height(height);
        },

        //DEBUG
        //onblur : function() { ... },

        intercept : function(jsondata) {
            json = JSON.parse(jsondata);

            /* return back from fixed height */
            jQuery(this).css("height", "");

            if (json.status == 0){
                phEditInPlaceMsg(json.error, 0)
                this.reset();
            } else {
                var id = jQuery(this).attr("id");
                phEditInPlacePasteAndMark(id, json);
                return json.result;
            }
        },

        placeholder: "",

        // Possible information for parts on the site which will be not changed by chaning the value (for example currency view - currency rate)
        callback: function() {
            var chEIP = ".phChangeEditInPlace" + jQuery(this).attr("data-id");
            jQuery(chEIP).html(phLang['PHOCA_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION'])
        },

    })

})
