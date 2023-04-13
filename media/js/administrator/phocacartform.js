/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/* VARIABLES */




/* FUNCTIONS */
function phRenderModalWindow(id, title) {


    var phLang = Joomla.getOptions('phLang');


    o = ''
    + '<div id="'+ id +'" tabindex="-1" role="dialog" class="joomla-modal modal fade">'
    + ' <div class="modal-dialog modal-lg jviewport-width80">'
    + '  <div class="modal-content">'
    + '   <div class="modal-header">'
    + '    <h3 class="modal-title">'+ title +'</h3>'
    + '    <button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="'+ phLang['COM_PHOCACART_CLOSE'] + '"></button>'
    + '   </div>'
    + '   <div class="modal-body jviewport-height80">'
   // + '    <div class="p-3">'
	//+ '     <div class="row">'
   // + '      <div class="form-group col-md-12">'
    + '       <iframe frameborder="0"></iframe>'
    //+ '      </div>'
   // + '     </div>'
  //  + '    </div>'
    + '   </div>'
    + '   <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-hidden="true">'+ phLang['COM_PHOCACART_CLOSE'] + '</button></div>'
    + '  </div>';// end modal content
    + ' </div>';// end modal dialog
    + '</div>';// end joomla-modal

    jQuery(".modal-backdrop").remove();// Remove not correctly hidden modal-backdrop
    jQuery("#phModalContainer").remove();// Remove previously created container


    var phModalContainer = jQuery('<div id="phModalContainer"></div>');
	phModalContainer.appendTo(document.body);
    jQuery("#phModalContainer").html(o);
   // jQuery("#" + id).modal();



    var myModal = new bootstrap.Modal(document.getElementById(id), {});

//document.onreadystatechange = function () {

  myModal.show();
//};

}

/* Function phDoRequest (create thumbnails) */
function phDoRequest(url, data, msg) {

	jQuery("#ph-ajaxtop").html(phGetMsg(msg, 1));
	jQuery("#ph-ajaxtop").show();

	let phRequestActive = jQuery.ajax({
	   	url: url,
	   	type:'POST',
	   	data:data,
	   	dataType:'JSON',
	   	success:function(response){

			if ( response.status == 2) {
				// No message
				jQuery("#ph-ajaxtop").hide();
				jQuery(".ph-result-txt").remove();
				phRequestActive = null;
			} else if ( response.status == 1 ){
			 	jQuery("#ph-ajaxtop-message").html(phGetMsg(response.message, 0));
			 	phRequestActive = null;
			 	phCloseMsgBoxSuccess();
		  	} else {

				jQuery("#ph-ajaxtop-message").html(phGetMsg(response.error, 0));
			 	phRequestActive = null;
				phCloseMsgBoxError();
		  	}
	   	}
	});
}

/* ProductFile, PublicFile */
function phAddValueFile(id, title) {
    document.getElementById(id).value = title;
    jQuery(".modal").modal("hide");
}

/* Image */
function phAddValueImage(id, title, params) {
    document.getElementById(id).value = title;
    jQuery(".modal").modal("hide");

    if (params["request"] == 1) {
        var data = {};
        data["filename"] = encodeURIComponent(title);
        data["manager"] = params['manager'];

        /* Change image preview */
        var image = "";
        if (title.trim() != "") {
            image 	= params["pathimage"] + title;
        }
        phChangePreviewImage(id, image);
        phDoRequest(params["requesturl"], data, params["requestmsg"]);
    }

}

/* Image preview - product/category - change image for preview in admin in tooltip */
function phChangePreviewImage(id, image) {
	if (image != '') {
		var phOutput = '<img src="' + image + '" alt="" />';
	} else {
		var phOutput = '<span class="glyphicon glyphicon-ban-circle ban-circle"></span>';
	}

    // Dynamically added form fields do not set right ID for other tags except input
    //var idItem = '#phTooltipImagePreview_' + id;
   // var idItem = jQuery(this).prev(".phTooltipImagePreview").attr("id");
    var idItem = jQuery("#"+id).prev("span").children(".phTooltipImagePreview");

	jQuery(idItem).html(phOutput);
	return true;
}

/* EVENTS */
jQuery(document).ready(function() {



    /* ProductFile */
	jQuery(document).on("click", "a.phProductFileModalButton", function (e) {
        var src         = jQuery(this).attr("data-src");
        var title       = jQuery(this).attr("data-title");
        var id          = jQuery(this).prev("input").attr("id");// data-id does not work by dynamically added form fields
        var idModal     = "phProductFileModalName" + id;
        var idIframe    = idModal + " iframe";
        src = src.replace("{ph-field-id}", id);

        // Select right download folder
        var idFolder =  id;
        // 1) Download File - form field added manually
        idFolder = idFolder.replace("jform_download_file", "jform_download_folder");
        // 2) Download File - form field added dynamically
        // 2a) Files in download options have only one download folder - in this case == undefined (if undefined use the 1) )
        // 2b) Files in attribute options have download folder for each file - in this case == true
        idFolder = idFolder.replace("__download_file", "__download_folder");

        if(typeof jQuery("#" + idFolder).val() !== "undefined") {
            // attribute options + download options (statically added download file form field)
            var phDownloadFolder = jQuery("#" + idFolder).val();
        } else {
            // download options (dynamically added download file form fields)
            var phDownloadFolder = jQuery("#jform_download_folder").val();
        }

		src = src + "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;

        var phModalWidth = 700;
        //var phModalHeight = 400;
        var width = jQuery(this).attr("data-width") || phModalWidth;
        //var height = jQuery(this).attr("data-height") || phModalHeight;
        var height = jQuery(window).height() - 200;

        phRenderModalWindow(idModal, title);// Render Modal Window
        //jQuery("#" + idIframe).attr({"src": src, "height": height, "width": width});// Set iframe url for rendered modal window
        jQuery("#" + idIframe).attr({"src": src});

    });

    /* PublicFile */
	jQuery(document).on("click", "a.phPublicFileModalButton", function (e) {
        var src         = jQuery(this).attr("data-src");
        var title       = jQuery(this).attr("data-title");
        var id          = jQuery(this).prev("input").attr("id");// data-id does not work by dynamically added form fields
        var idModal     = "phPublicFileModalName" + id;
        var idIframe    = idModal + " iframe";
        src = src.replace("{ph-field-id}", id);
		//var phDownloadFolder = jQuery("#jform_download_folder").val();
		src = src + "&folder=&downloadfolder=";

        var phModalWidth = 700;
        var width = jQuery(this).attr("data-width") || phModalWidth;
        var height = jQuery(window).height() - 200;

        phRenderModalWindow(idModal, title);// Render Modal Window
        jQuery("#" + idIframe).attr({"src": src, "height": height, "width": width});// Set iframe url for rendered modal window

    });

    /* Image */
	jQuery(document).on("click", "a.phImageFileModalButton", function (e) {
        var src         = jQuery(this).attr("data-src");
        var title       = jQuery(this).attr("data-title");
        // data-id does not work by dynamically added form fields
        // only input tag has right ID - all other parts NOT
        var id          = jQuery(this).prev("input").attr("id");
        var idModal     = "phImageFileModalName" + id;
        var idIframe    = idModal + " iframe";
        src = src.replace("{ph-field-id}", id);
		//src = src + "&folder=&downloadfolder=";

        var phModalWidth = 700;
        var width = jQuery(this).attr("data-width") || phModalWidth;
        var height = jQuery(window).height() - 200;

        phRenderModalWindow(idModal, title);// Render Modal Window
        jQuery("#" + idIframe).attr({"src": src, "height": height, "width": width});// Set iframe url for rendered modal window

    });


    /* Event Create Thumbnails */
	jQuery(document).on("change", ".imageCreateThumbs", function() {
        var data = {};
		data["filename"] = encodeURIComponent(jQuery(this).val());
		data["manager"] = jQuery(this).attr("data-manager");

		// Change Preview Image
        var image = "";
        if (jQuery(this).val().trim() != "") {
			var image 	= jQuery(this).attr("data-pathimage") + jQuery(this).val();
        }

        phChangePreviewImage(jQuery(this).attr("id"), image);
        phDoRequest(jQuery(this).attr("data-requesturl"), data, jQuery(this).attr("data-requestmsg"));
    })

     /* Color */
	jQuery(document).on("click", "a.phColorTextPickerButton", function (e) {
        var id = jQuery(this).prev("input").attr("id");// data-id does not work by dynamically added form fields
        openPicker(id);
    });





    /* Event - adding new row of options (in attributes)
     * Add and create download token and download folder for attribute download files
     */
    //jQuery(document).on('subform-row-add', function(event, row){
    //document.addEventListener('subform-row-add', function(event, row){
        //document.addEventListener('subform-row-add', ({ detail: { row } }) => {

        document.addEventListener('subform-row-add', function (_ref) {
            var row = _ref.detail.row;

        /*
        * Get "download_token" and "download_folder for "options"
        * Get only "download_token" for "additional download files"
        */
       if (jQuery(row).attr("data-base-name") == "options" || jQuery(row).attr("data-base-name") == "additional_download_files") {


            var phVars = Joomla.getOptions('phVars');

            var data = {};
            data["task"] = "gettoken";
            var optionId = jQuery(row).find('input').first().attr('id');// Get the option form field ID name of added option row
            var idFolder = optionId.replace("__id", "__download_folder");
            var idToken = optionId.replace("__id", "__download_token");
            var url = 'index.php?option=com_phocacart&view=phocacartattributea&format=json&tmpl=component&' + phVars['token'] + '=1';


            let phRequestActiveToken = jQuery.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'JSON',
                success:function(response){
                    if ( response.status == 1 ){

                        if (jQuery(row).attr("data-base-name") == "options") {
                            // folder is not set for additional files
                            jQuery("#" + idFolder).val(response.folder);
                        }
                        jQuery("#" + idToken).val(response.token);
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
    })


    /* Event - remove row of options (in attributes)
     * Remove download folder and its files
     */
    jQuery(document).on('subform-row-remove', function(event, row){


        /* Possible warning but unfortunately the event cannot be stopped
         *if(confirm("COM_PHOCACART_WARNING_REMOVING_ATTRIBUTE_OPTION_DELETES_DOWNLOAD_FOLDER_DOWNLOAD_FILE")){}
        */
        if (jQuery(row).attr("data-base-name") == "options") {


            var phVars = Joomla.getOptions('phVars');

            data = {};
            data['task'] 	= 'removefolder';
            var optionId = jQuery(row).find('input').first().attr('id');// Get the option form field ID name of added option row
            var idFolder = optionId.replace("__id", "__download_folder");
           // var idToken = optionId.replace("__id", "__download_token");
           var folder = jQuery("#" + idFolder).val();
           data['folder'] 	= {0: folder};

            var url = 'index.php?option=com_phocacart&view=phocacartattributea&format=json&tmpl=component&' + phVars['token'] + '=1';

            let phRequestActiveToken = jQuery.ajax({
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
    })




    jQuery(document).on('paste', '.imageCreateThumbs',function() {

        var element     = jQuery(this);
        var id          = element.attr("id");
        var path 	    = element.attr("data-pathimage");
        var manager 	= element.attr("data-manager");
        var requestUrl  = element.attr("data-requesturl");
        var requestMsg  = element.attr("data-requestmsg");


        var phVars = Joomla.getOptions('phVars');
        var phLang = Joomla.getOptions('phLang');
        var url = 'index.php?option=com_phocacart&view=phocacartimagea&format=json&tmpl=component&' + phVars['token'] + '=1';

        // use event.originalEvent.clipboard for newer chrome versions
        var items = (event.clipboardData  || event.originalEvent.clipboardData).items;
        //console . log(JSON.stringify(items)); // will give you the mime types
        // find pasted image among pasted items
        var blob = null;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") === 0) {
            blob = items[i].getAsFile();
            }
        }
        // load image if there is a pasted image
        if (blob !== null) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var imgFormat = event.target.result.split(',')[0];
                var imgData = event.target.result.split(',')[1];


                var title = jQuery('#jform_title').val();

                if (title == '') {
                    jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
                    jQuery("#ph-ajaxtop").show();
                    jQuery("#ph-ajaxtop-message").html(phGetMsg('<span class="ph-result-txt ph-error-txt">'+ phLang['COM_PHOCACART_ERROR_TITLE_NOT_SET'] + '</span>', 0));
                    phCloseMsgBoxSuccess();
                    return false;
                }

                jQuery.ajax({
                    url: url,
                    type:'post',
                    dataType: 'JSON',
                    data:{'image':imgData, 'imagetitle': title, 'imageformat': imgFormat},
                    success: function(response) {

                        if ( response.status == 1 ){

                            jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
                            jQuery("#ph-ajaxtop").show();
                            jQuery("#ph-ajaxtop-message").html(phGetMsg(response.message, 0));
                            phCloseMsgBoxSuccess();
                            if ( response.file != '' ){
                                element.val(response.file);

                                var image = path + response.file;
                                phChangePreviewImage(id, image);

                                var dataCreateThumbs            = {};
                                dataCreateThumbs["filename"]    = encodeURIComponent(response.file);
                                dataCreateThumbs["manager"]     = manager;
                                phDoRequest(requestUrl, dataCreateThumbs, requestMsg);
                            }
                        } else {
                            jQuery("#ph-ajaxtop").html(phGetMsg(' &nbsp; ', 1));
                            jQuery("#ph-ajaxtop").show();
                            jQuery("#ph-ajaxtop-message").html(phGetMsg(response.error, 0));
                            phCloseMsgBoxError();
                        }
                    }
                });
            }
            reader.readAsDataURL(blob);
        }

        return true;
    })

})
