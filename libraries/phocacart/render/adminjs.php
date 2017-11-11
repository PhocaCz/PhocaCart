<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
final class PhocacartRenderAdminjs
{
	private function __construct(){}	
	
	/*
	public static function renderJsAppendValueToUrl() {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
		$s[] = '   var stringToSend = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
		$s[] = '   var newUri = jQuery(\'.modal_jform_download_file\').attr(\'href\') + stringToSend;';
		$s[] = '   jQuery(\'.modal_jform_download_file\').attr("href", newUri);';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	*/
	
	public static function renderImportExportItems($url, $messageBox, $formId, $count, $successMessage, $reload = 0) {
		
		$document	= JFactory::getDocument();
		
		$s   = array();
		$s[] = 'function phUpdateProgress(percentage){';
		$s[] = '   if(percentage > 100) {percentage = 100;}';
		$s[] = '   jQuery(\'#phProgressBar\').css(\'width\', percentage+\'%\');';
		$s[] = '   jQuery(\'#phProgressBar\').html(percentage+\'%\');';
		$s[] = '}';
		
		$s[] = ' ';

		$s[] = 'function phImportAllItems(e) {';
		$s[] = '   e.stopPropagation();';
		$s[] = '   e.preventDefault();';

		$s[] = '   var phMaxItems = e.data.a1;';// see the last but one row of this s
		$s[] = '   var phLastPage = phMaxItems;';
		$s[] = '   var phItemCount = 1;';
		$s[] = '   var phMessageBoxId 	= \'#'.$messageBox.'\';';
		$s[] = '   var formId			= \'#'.$formId.'\';';

		$s[] = '   function phGetNextItem() {';
		$s[] = '      phCurrentItemCount = phItemCount;';

		$s[] = '      if (phItemCount <= phMaxItems) {';
		$s[] = '          jQuery.ajax({';
		$s[] = '             url: \''.$url.'\' + \'&p=\' + phItemCount + \'&lp=\' + phLastPage,';
		$s[] = '             method: \'GET\',';
		$s[] = '             async: true,';
		$s[] = '             success: function(data) {';
		$s[] = '                if (data.status == 1) {';
		$s[] = '                   ++phItemCount;';
		$s[] = '                   phUpdateProgress(Math.round((phItemCount/phMaxItems)*100));';
		$s[] = '                   if (phMaxItems > phCurrentItemCount) {';
		$s[] = '	                  phGetNextItem();';
		$s[] = '                   }';
		$s[] = '                }';
		$s[] = ' ';
		$s[] = '                if (phMaxItems == phCurrentItemCount) {';
		$s[] = '                   jQuery(".circle").addClass("circle-active");';
		$s[] = '                   jQuery(phMessageBoxId).html(\'<div class="alert alert-success"><button class="close" type="button" data-dismiss="alert">×</button>'.$successMessage.'</div>\');';

		if ($reload == 1) {
			// TO DO enable
			$s[] =	'	           window.setTimeout(function () {document.location.reload();}, 1000);';
			$s[] = PhocacartRenderJs::renderOverlay();
		}

		$s[] = '			    }';
		$s[] = '             }';// end success
		$s[] = '          });';// end ajax
		$s[] = '	   }';
		$s[] = '   }';
		$s[] = '   phGetNextItem();';
		$s[] = '}';
		
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '	jQuery(\'#'.$formId.'\').on(\'submit\',{a1: '.(int)$count.'}, phImportAllItems);';
		$s[] = '})';
		
		$document->addScriptDeclaration(implode("\n", $s));
		
	}
	
	/* Really not nice way to move the system messages from bootstrap2 (Joomla) to bootstrap3 (Phoca)
	*/
	public static function moveSystemMessageFromJoomlaToPhoca() {

		$s = array();
		//$s[] = 'document.getElementById("system-message-container").style.display = "none";';
		$s[] = 'jQuery(document).ready(function() {';
		//$s[] = '   jQuery("#system-message-container").removeClass("j-toggle-main");';
		$s[] = '   jQuery("#system-message-container").css("display", "none");';
		$s[] = '   var phSystemMsg = jQuery("#system-message-container").html();';
		$s[] = '   jQuery("#ph-system-message-container").html(phSystemMsg);';
		$s[] = '});';
		
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/*
	 * Is used for:
	 * - skip wizard in wizard view - modal window must be closed
	 * - start wizard in control panel - modal windows stays unchanged
	 */
	
	public static function renderAjaxDoRequestWizardController($url, $id, $closeModal = true) {
	
		$s 	= array();
		
		$s[] = 'function phDoRequestWizardController(url) {';
		$s[] = '   var dataPost = {};';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		$s[] = '            phRequestActive = null;';
		$s[] = '         } else {';
		$s[] = '            phRequestActive = null;';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		
		// This function is a part of iframe in modal window
		// We can close whole modal window through global function defined in
		// modalWindowDynamic in administrator\components\com_phocacart\libraries\phocacart\render\renderadminview.php
		if ($closeModal == true) {
			$s[] = '  window.parent.phCloseModal();';
		}
		
		$s[] = '}';
		
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery("#'.$id.'").on("click", function(e) {';
		$s[] = '      phDoRequestWizardController("'.$url.'");';
		$s[] = '   })';
		$s[] = '})';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/* When the modal window will be focused, check again the statuses of all the items 
	*/
	public static function renderAjaxDoRequestWizardAfterChange($url, $value = 'phClickBtn') {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   phDoRequestWizard(\''.$url.'\');';

		$s[] = '   jQuery(window).on("blur focus", function(e) {';
		$s[] = '      var prevType = jQuery(this).data("prevType");';
		$s[] = ' ';
		$s[] = '	  if (prevType != e.type) {';
		$s[] = '	     switch (e.type) {';
		$s[] = '		   case "blur":';
		$s[] = '		   break;';
		$s[] = '		   case "focus":';
		$s[] = '              phDoRequestWizard(\''.$url.'\');';
		$s[] = '		   break;';
		$s[] = '	     }';
		$s[] = '      }';
		$s[] = ' ';
		$s[] = '   jQuery(this).data("prevType", e.type);';
		$s[] = '   })';
		
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	/* Ask for the changes, if something changes - e.g. category is created
	 * return this to inform and update status in wizard modal window
	 */
	
	public static function renderAjaxDoRequestWizard() {
	
		$s 	= array();	 
		$s[] = 'function phDoRequestWizard(url) {';
		$s[] = '   var dataPost = {};';
		$s[] = '   var phTrue   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_CREATED').'</div>\';';
		$s[] = '   var phTrueAdd   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_ADDED').'</div>\';';
		$s[] = '   var phTrueEdit   = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.JText::_('COM_PHOCACART_EDITED').'</div>\';';
		$s[] = '   var phTrueAll = 1';
		$s[] = '   var phFalse  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_CREATED_YET').'</div>\';';
		$s[] = '   var phFalseAdd  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_ADDED_YET').'</div>\';';
		$s[] = '   var phFalseEdit  = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.JText::_('COM_PHOCACART_NOT_EDITED_YET').'</div>\';';
		$s[] = '   phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		
		// Category
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.category == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardCategory").html(phOutput);';
		
		// Tax
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.tax == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardTax").html(phOutput);';
		
		// Product
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.product == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardProduct").html(phOutput);';
		
		// Shipping
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.shipping == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardShipping").html(phOutput);';
		
		// Payment
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.payment == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardPayment").html(phOutput);';
		
		// Country
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.country == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardCountry").html(phOutput);';
		
		// Region
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.region == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardRegion").html(phOutput);';
		
		// Menu
		$s[] = '   		   var phOutput = phFalse;';
		$s[] = '            if (data.menu == 1) { phOutput = phTrue;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardMenu").html(phOutput);';
		
		// Module
		$s[] = '   		   var phOutput = phFalseAdd;';
		$s[] = '            if (data.module == 1) { phOutput = phTrueAdd;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardModule").html(phOutput);';
		
		// Options
		$s[] = '   		   var phOutput = phFalseEdit;';
		$s[] = '            if (data.option == 1) { phOutput = phTrueEdit;} else {phTrueAll = 0;}';
		$s[] = '            jQuery("#phResultWizardOption").html(phOutput);';
		
		$s[] = '   		   if(phTrueAll == 1) {';
		$s[] = '               jQuery("#phResultWizardAll").css("display", "block")';
		$s[] = '            }';
		
		$s[] = '            phRequestActive = null;';
		$s[] = '         } else {';
		// No Displaying of error
		//$s[] = '	           jQuery("#phResultWizardCategory").html(data.error);';
		$s[] = '            phRequestActive = null;';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '}';
	
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	
	// ========
	// ROWS
	// ========
	
	public static function renderJsManageRowImage($i, $newRow) {
		$s 	= array();
		$s[] = 'var phRowCountImage = '.$i.';';
		
		// We have only two modal box - 1. for image, 2. for additinal images (all)
		// So we need to get info, which additional images we have clicked
		// For example, if there are 3 additional images and we click on second, in modal box, we need to get this value to set right value
		// phRowImage variable is a part of iframe url (the iframe url is build dynamically)
		// administrator\components\com_phocacart\models\fields\phocaselectfilenameimage.php
		// $link = 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id . '\'+ (phRowImage) +\'';
		// Here
		// administrator\components\com_phocacart\views\phocacartmanager\tmpl\default_file.php
		// we get is as $this->field
		// Here
		// administrator\components\com_phocacart\libraries\phocacart\render\renderadminview.php
		// in function additionalImagesRow we call this function
		//$s[] = 'var phRowImage = 0;';
		//$s[] = 'function setPhRowImageId(rowImgId) {';
		//$s[] = '   phRowImage = rowImgId;';
		//$s[] = '}';
		
		$s[] = 'function phAddRowImage() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '   jQuery(\'#phrowboximage\').append(phNewRow);';
		$s[] = '	  phRowCountImage++;';
		
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		//$s[] = '	  /* Initialize the modal button again - FOR IMAGES */';
		//$s[] = '	  /* SqueezeBox.initialize({}); */';
		//$s[] = '	  SqueezeBox.assign($$(\''.$poPup.'\'), {';
		//$s[] = '	     parse: \'rel\'';
		//$s[] = '	  });';
		
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowImage(id) {';
		$s[] = '	  jQuery(\'#phrowimage\' + id).remove();';
		$s[] = '	  var phCountRowImage = jQuery(\'.ph-row-image\').length;';
		$s[] = '   if (phCountRowImage == 0) {';
		$s[] = '      jQuery(\'#phrowboximage\').empty();';
		$s[] = '   }';
		//$s[] = '	  phRowCountImage--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowAttribute($i, $newRow) {
		$s 	= array();
		$s[] = 'var phRowCountAttribute = '.$i.';';
		$s[] = 'function phAddRowAttribute() {';
		$s[]	= '   var phRowOptionAttributeId = phRowCountAttribute;';  // We need this Id for Options 
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   jQuery(\'#phrowboxattribute\').append(phNewRow);';
		$s[] = '	  phRowCountAttribute++;';
		
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowAttribute(id) {';
		//$s[] = '	  jQuery(\'#phrowattribute\' + id).remove();';
		$s[] = '	  jQuery(\'#phAttributeBox\' + id).remove();';
		$s[] = '	  var phCountRowAttribute = jQuery(\'.ph-row-attribute\').length;';
		$s[] = '   if (phCountRowAttribute == 0) {';
		$s[] = '      jQuery(\'#phrowboxattribute\').empty();';
		$s[] = '   }';
		//$s[] = '	  phRowCountAttribute--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	
	public static function renderJsManageRowOption($j,  $newRow, $newHeader) {
		$s 	= array();
		
		/* $s[] = '
		jQuery(function($) {
			jQuery(function($) {
				SqueezeBox.initialize({});
			

			SqueezeBox.assign($(\'a.modal_jform_optionimage00\').get(), {parse: \'rel\'});
			SqueezeBox.assign($(\'a.modal_jform_optionimage01\').get(), {parse: \'rel\'});
			SqueezeBox.assign($(\'a.modal_jform_optionimage02\').get(), {parse: \'rel\'});
			//SqueezeBox.assign($(\'a.modal_jform_optionimage03\').get(), {parse: \'rel\'});
			//SqueezeBox.assign($(\'a.modal_jform_optionimage04\').get(), {parse: \'rel\'});
			});
		});
		function jModalClose() {SqueezeBox.close();}'	;*/
		
		$s[] = 'var phRowCountOption = '.$j.';';
		
		
		//$s[] = 'var phRowImgOption = \'00\';';
		//$s[] = 'function setPhRowImgOptionId(attrId, id) {';
		//$s[] = '  phRowImgOption = attrId.toString() + id.toString();';
		//$s[] = '}';
		
		
		$s[] = 'function phAddRowOption(attrid) {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] = '	  if(phCountRowOption == 0) {';
		$s[] = '	     jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxoptionjs\' + attrid).append(phNewRow);';
			
		// Modal Box Popup wee need to initialize it for every newly added item
		//$s[]	= '   /* Initialize the modal button again - FOR IMAGES IN ATTRIBUTE OPTIONS */';
		//$s[]	= '   SqueezeBox.initialize({});';
		//$s[]	= '   var phModalFormOption = \'a.modal_jform_optionimage\' + attrid + phRowCountOption;';
		//$s[]	= '   SqueezeBox.assign(jQuery(phModalFormOption).get(), {parse: \'rel\'});';
		//$s[]	= '   function jModalClose() {SqueezeBox.close();}';
		// End Modal Box
		
		$s[]	= ' ';
		$s[]	= '   var phMiniColorsId =  \'#jform_optioncolor\' + attrid + phRowCountOption;';// Reload minicolors
		$s[]	= '   jQuery(phMiniColorsId).minicolors({
							control: \'hex\',
							format: \'hex\',
							position: \'default\',
							theme: \'bootstrap\'
						});';
		$s[]	= ' ';
		$s[] = '	  phRowCountOption++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';

		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowOption(id, attrid) {';
		//$s[] = '	  jQuery(\'#phrowoption\' + id).remove();';
		$s[] = '	  jQuery(\'#phOptionBox\' + attrid + id).remove();';
		$s[] = '	  var phCountRowOption = jQuery(\'.ph-row-option-attrid\' + attrid).length;';
		$s[] = '   if (phCountRowOption == 0) {';
		$s[] = '      jQuery(\'#phrowboxoptionjs\' + attrid).empty();';// clean header of option added by js
		$s[] = '      jQuery(\'#phrowboxoption\' + attrid).empty();';// clean header of option loaded by php
		$s[] = '   }';
		//$s[] = '	  phRowCountOption--;';// DON'T SUBTRACT, it is not COUNT, but ID, every row should be unique ID
		//$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxoptionjs - header created by javascript
		// #phrowboxoption - header created by php/mysql
		
	}
	
	public static function renderJsManageRowSpecification($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] = 'var phRowCountSpecification = '.$i.';';
		$s[] = 'function phAddRowSpecification() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] = '	  if(phCountRowSpecification == 0) {';
		$s[] = '	     jQuery(\'#phrowboxspecification\').append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxspecification\').append(phNewRow);';
		$s[] = '	  phRowCountSpecification++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		$s[] = ' ';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowSpecification(id) {';
		//$s[] = '	  jQuery(\'#phrowSpecification\' + id).remove();';
		$s[] = '	  jQuery(\'#phSpecificationBox\' + id).remove();';
		$s[] = '	  var phCountRowSpecification = jQuery(\'.ph-row-specification\').length;';
		$s[] = '   if (phCountRowSpecification == 0) {';
		$s[] = '      jQuery(\'#phrowboxspecification\').empty();';
		$s[] = '      jQuery(\'#phrowboxspecificationheader\').empty();';
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	

	
	public static function renderJsManageRowDiscount($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] = 'var phRowCountDiscount = '.$i.';';
		$s[] = 'function phAddRowDiscount() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '	  var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '	  var phCountRowDiscount = jQuery(\'.ph-row-discount\').length;';
		$s[] = '	  if(phCountRowDiscount == 0) {';
		$s[] = '	     jQuery(\'#phrowboxdiscount\').append(phNewHeader);';
		$s[] = '	  }';
		$s[] = '   jQuery(\'#phrowboxdiscount\').append(phNewRow);';
		$s[] = '	  phRowCountDiscount++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		if(PhocacartUtils::isJCompatible('3.7')) {
			$s[] = '	var elements = document.querySelectorAll(".field-calendar");';
			$s[] = '	for (i = 0; i < elements.length; i++) {';
			$s[] = '		JoomlaCalendar.init(elements[i]);';
			$s[] = '	}';
		}
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		// CALENDAR IS RELOADED DIRECTLY BELOW THE NEW ROW administrator\components\com_phocacart\libraries\phocacart\render\adminview.php
			
		$s[] = ' ';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowDiscount(id) {';
		//$s[] = '	  jQuery(\'#phrowDiscount\' + id).remove();';
		$s[] = '	  jQuery(\'#phDiscountBox\' + id).remove();';
		$s[] = '	  var phCountRowDiscount = jQuery(\'.ph-row-discount\').length;';
		$s[] = '   if (phCountRowDiscount == 0) {';
		$s[] = '      jQuery(\'#phrowboxdiscount\').empty();';
		$s[] = '      jQuery(\'#phrowboxdiscountheader\').empty();';
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	
	
	public static function renderJsManageRowPriceHistory($i, $newRow) {
		$s 	= array();
		$s[] = 'var phRowCountPricehistory = '.$i.';';
		$s[] = 'function phAddRowPricehistory() {';
		$s[] = '	  var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   jQuery(\'#phrowboxpricehistory\').append(phNewRow);';
		$s[] = '	  phRowCountPricehistory++;';
		$s[] = '	  jQuery(\'select\').chosen({disable_search_threshold : 10,allow_single_deselect : true});';//Reload Chosen Admin
		if(PhocacartUtils::isJCompatible('3.7')) {
			$s[] = '	var elements = document.querySelectorAll(".field-calendar");';
			$s[] = '	for (i = 0; i < elements.length; i++) {';
			$s[] = '		JoomlaCalendar.init(elements[i]);';
			$s[] = '	}';
		}
		//$s[] = '	  jQuery(\'select\').trigger("chosen:updated");';//Reload Chosen
		// CALENDAR IS RELOADED DIRECTLY BELOW THE NEW ROW administrator\components\com_phocacart\libraries\phocacart\render\adminview.php
			
		$s[] = ' ';
		$s[] = '}';
		$s[] = ' ';
		$s[] = 'function phRemoveRowPricehistory(id) {';
		//$s[] = '	  jQuery(\'#phrowDiscount\' + id).remove();';
		$s[] = '	  jQuery(\'#phPricehistoryBox\' + id).remove();';
		$s[] = '	  var phRowCountPricehistory = jQuery(\'.ph-row-pricehistory\').length;';
		$s[] = '   if (phRowCountPricehistory == 0) {';
		$s[] = '      jQuery(\'#phrowboxpricehistory\').empty();';
		$s[] = '   }';
		$s[] = '}';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
		
	}
	
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
	
	
	
	public static function renderHtmlAfterChange($changeElement, $targetElement) {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery("'.$changeElement.'").on("change", function(e) {';
		$s[] = '      jQuery("'.$targetElement.'").show();';
		$s[] = '   })';
		$s[] = '})';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
}
?>