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

/* Event - specific multiselect in orders view because of two rows at once */
jQuery(document).on("click", "tr.ph-row-multiselect", function() {
	el = jQuery(this).find('.ph-select-row input:checkbox.form-check-input')[0];
	el.checked = ! el.checked;
	Joomla.isChecked(el.checked);
});
jQuery(document).on("click", '.ph-select-row input:checkbox.form-check-input', function(e) {
	e.stopPropagation();
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
	let phRequestActiveToken = jQuery.ajax({
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
	let phRequestActive = jQuery.ajax({
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


// Add icons to submenu
jQuery(document).ready(function() {

	var getUrlParameter = function getUrlParameter(sParam, url) {
		var sPageURL = url,
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
		return false;
	};

	jQuery('.item-level-3').find('a').each(function() {
		var view = getUrlParameter('view', jQuery(this).attr('href'));
		view = String(view);
		if(view.includes('phocacart')) {
			var className = view.replace('phocacart', '');
			jQuery(this).addClass('ph-submenu ph-submenu-' + className);
		}

		/* Link to custom fields and custom field groups */
		var context = getUrlParameter('context', jQuery(this).attr('href'));
		context = String(context);
		if(context == 'com_phocacart.phocacartitem') {
			if (view == 'groups') {
				var className = 'fieldgroups';
			} else {
				var className = 'fields';
			}

			jQuery(this).addClass('ph-submenu ph-submenu-' + className);
		}


		if (jQuery(this).attr('href') == 'index.php?option=com_phocacart') {
			jQuery(this).addClass('ph-submenu ph-submenu-cp');
		}
	});

	const container = jQuery('.main-nav-container a[href="index.php?option=com_phocacart"] + ul').parents('.main-nav-container');
	if (container) {
		const menu = container.children('ul');
		if (menu) {
			const submenu = menu.find('a[href="index.php?option=com_phocacart"] + ul');
			if (submenu) {
				const phSubmenu = submenu.clone();
				phSubmenu.attr('class', 'nav flex-column main-nav metismenu child-open ph-menu');
				phSubmenu.find('li').each(function() {
					jQuery(this).removeClass('item-level-3').addClass('item-level-1');
				});

				const allContainers = jQuery('.main-nav-container');
				const allMenus = allContainers.children('ul');

				phSubmenu.prepend(jQuery('<li class="item item-level-1"><a href="#" class="no-dropdown ph-submenu ph-submenu-back"><span class="sidebar-item-title">' + Joomla.Text._('COM_PHOCACART_MENU_BACK') + '</span></a></li>'));
				const phMenuSwitch = phSubmenu.find('.ph-submenu-back');
				phMenuSwitch.click(function(e) {
					e.preventDefault();
					allMenus.css('display', 'block');
					phSubmenu.css('display', 'none');
				});

				menu.prepend(jQuery('<li class="item item-level-1"><a href="#" class="no-dropdown ph-submenu ph-submenu-phocacart"><span class="sidebar-item-title">' + Joomla.Text._('COM_PHOCACART_MENU_PHOCACART') + '</span></a></li>'));
				const menuSwitch = menu.find('.ph-submenu-phocacart');
				menuSwitch.click(function(e) {
					e.preventDefault();
					phSubmenu.css('display', 'block');
					allMenus.css('display', 'none');
				});

				//console.log(container.length);
				container.append(phSubmenu);
				allMenus.css('display', 'none');
			}
		}
	}
})


let phocaToastContainer = null;

/**
 * @param {string} message Message Text
 * @param {string} type Toast color scheme class success|error
 */
const phocaToast = (message, type) => {
	if (phocaToastContainer === null) {
		phocaToastContainer = document.createElement('div');
		phocaToastContainer.classList.add('toast-container', 'position-fixed', 'bottom-0', 'end-0', 'p-3');

		document.body.appendChild(phocaToastContainer);
	}

	const toast = document.createElement('div');
	toast.classList.add('toast', 'align-items-center', 'bg-gradient');
	if (type === 'success') {
		toast.classList.add('bg-success', 'text-white');
	} else {
		toast.classList.add('bg-danger', 'text-white');
	}
	toast.setAttribute('role', 'alert');
	toast.setAttribute('aria-live', 'assertive');
	toast.setAttribute('aria-atomic', 'true');

	const toastBodyWrapper = document.createElement('div');
	toastBodyWrapper.classList.add('d-flex');

	const toastBody = document.createElement('div');
	toastBody.classList.add('toast-body');
	toastBody.textContent = message;

	const toastButton = document.createElement('button');
	toastButton.setAttribute('type', 'button');
	toastButton.setAttribute('data-bs-dismiss', 'toast');
	toastButton.setAttribute('aria-label', Joomla.Text._('COM_PHOCACART_AJAX_CLOSE'));
	toastButton.classList.add('btn-close', 'btn-close-white', 'me-2', 'm-auto');

	toast.appendChild(toastBodyWrapper);
	toastBodyWrapper.appendChild(toastBody);
	toastBodyWrapper.appendChild(toastButton);

	phocaToastContainer.appendChild(toast);

	const bootstrapToast = new bootstrap.Toast(toast);
	bootstrapToast.show();
};

const phocaAjax = () => {
	const targets = document.querySelectorAll('[data-phajax]');
	targets.forEach((target) => {
		target.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();

			var xhr = new XMLHttpRequest();
			xhr.open('POST', target.href, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.responseType = 'json';

			xhr.onload = () => {
				// Check if the request was successful (status code 200)
				if(xhr.status === 200) {
					var jsonResponse = xhr.response;

					if (jsonResponse.success) {
						phocaToast(jsonResponse.message, 'success');

						target.dataset.phajax = jsonResponse.data.phajax;
						target.innerHTML = jsonResponse.data.content;
					} else {
						phocaToast(jsonResponse.message, 'error');
					}
				} else {
					phocaToast(Joomla.Text._('COM_PHOCACART_AJAX_ERROR'), 'error');
				}
			};

			xhr.onerror = () => {
				phocaToast(Joomla.Text._('COM_PHOCACART_AJAX_ERROR'), 'error');
			};

			xhr.send(target.dataset.phajax);
		});
	});
};

document.addEventListener('DOMContentLoaded', function() {
	phocaAjax();
});

/* Barcode Scanning */

let phVars = Joomla.getOptions('phVars');
let phParams = Joomla.getOptions('phParams');
let phScannerInput = '';
let phLastScannerClear = 0;
let urlItem = 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&' + phVars['token'] + '=1';
let urlItemEdit = 'index.php?option=com_phocacart&task=phocacartitem.edit';
let phListenScanner = false;

jQuery(document).ready(function() {

	if (phParams['barcode_scanning_product_list'] > 0) {
		if (document.getElementById('phocacartitems') && document.getElementById('filter_search')) {
			phListenScanner = true;
		}
	}
	//console.log(phListenScanner);

	if (phListenScanner) {
		document.addEventListener('keydown', (ev) => {

			if (ev.ctrlKey || ev.altKey) {
				// Ignore command-like keys
				return;
			}

			clearTimeout(phLastScannerClear);
			phLastScannerClear = window.setTimeout(function(){
				phScannerInput = '';
			}, 500);

			if (ev.key == 'Enter') {
				// Submit
				document.getElementById('filter_search').value = phScannerInput;

				if (phParams['barcode_scanning_product_list'] == 2) {
					jQuery.ajax({
						url: urlItem + '&q=' + phScannerInput,
						type: 'POST',
						data: [],
						dataType: 'JSON',
						success:function(response){
							if ( response.status == 1 ){
								if (typeof response.items[0] !== 'undefined') {
									if (response.items[0].id > 0) {
										window.location.href = urlItemEdit + '&id=' + response.items[0].id;
									}
								}

							}
						}
					});
				}

				// If product not found, set it to standard search where user gets info about not finding it
				document.getElementById('filter_search').form.submit();
			} else if (ev.key == 'Space') {
				// IE
				phScannerInput += ' ';
			} else if (ev.key.length == 1) {
				// A character not a key like F12 or Backspace
				phScannerInput += ev.key;
			}
		});
	}
})
