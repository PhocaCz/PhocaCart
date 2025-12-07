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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
final class PhocacartRenderAdminjs
{
	private function __construct(){}


	  /* For example creating thumbnail message - administration */
	public static function renderAjaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> '. strip_tags(addslashes($text)) . '</div>';
		}
		$o .= '</div>';
		return $o;
	}


	// =======
	// AJAX
	// =======
	/*public static function phEventCreateImageThumbnail($url, $msg, $manager = 'product', $value = 'imageCreateThumbs') {

		$path = PhocacartPath::getPath($manager);
		$pathImage = Uri::root() . $path['orig_rel_ds'];

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Event Create Thumbnails *//* ';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery(document).on("change", \'.'.$value.'\', function() {';

		$s[] = '   		var data = {};';
		$s[] = '   		data[\'filename\'] = encodeURIComponent(jQuery(this).val());';
		$s[] = '   		data[\'manager\'] = \''.$manager.'\';';

		// Change Preview Image
        $s[] = '        var image = "";';
        $s[] = '        if (jQuery(this).val().trim() != "") {';
		$s[] = '		    var image 	= \''.strip_tags(addslashes($pathImage)).'\' + jQuery(this).val();';
        $s[] = '		}';
		$s[] = '		var id 		= jQuery(this).attr(\'id\');';
		$s[] = '		phChangePreviewImage(id, image);';


		$s[] = '       	phDoRequest(\''.$url.'\', data, \''.strip_tags(addslashes($msg)).'\');';
		$s[] = '   })';
		$s[] = '})';
		$s[] = ' ';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}


	/**
	 * Add value from popup window (file manager mostly) to the list of items - e.g. select image - then thumbnails will be recreated
	 */
/*
	public static function phAddValueImage($url, $msg, $manager = 'productimage') {

		$path = PhocacartPath::getPath($manager);
		$pathImage = Uri::root() . $path['orig_rel_ds'];

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phAddValueImage *//* ';
		$s[] = 'function phAddValueImage(id, title, request) {';
		$s[] = '   document.getElementById(id).value = title;';
		//$s[] = '   SqueezeBox.close();';// close
		$s[] = '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] = '   if (request == 1) {'; // do request - do thumbnails

		$s[] = '   		var data = {};';
		$s[] = '   		data[\'filename\'] = encodeURIComponent(title);';
		$s[] = '   		data[\'manager\'] = \''.$manager.'\';';

		// Change Preview Image
        $s[] = '        var image = "";';
        $s[] = '        if (title.trim() != "") {';
		$s[] = '		    image 	= \''.strip_tags(addslashes($pathImage)).'\' + title;';
        $s[] = '		}';
		$s[] = '		phChangePreviewImage(id, image);';


		$s[] = '      	phDoRequest(\''.$url.'\', data, \''.strip_tags(addslashes($msg)).'\' );';
		$s[] = '   }';
		$s[] = '}';
		$s[] = ' ';
		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
*/

	/*public static function phAddValueFile() {

		//$path = PhocacartPath::getPath($manager);
		//$pathImage = Uri::root() . $path['orig_rel_ds'];

		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phAddValueFile /* ';
		$s[] = 'function phAddValueFile(id, title) {';
		$s[] = '   document.getElementById(id).value = title;';
		$s[] = '   jQuery(\'.modal\').modal(\'hide\');';
		$s[] = '}';
		$s[] = ' ';
		//jQuery('.modal').on('hidden', function () {
		//  // Do something after close
		//});
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}*/

	public static function renderOverlayOnSubmit($id) {

		$document	= Factory::getDocument();

		$s[] = 'jQuery(document).ready(function(){';
		$s[] = '   jQuery(\'#'.$id.'\').on(\'submit\', function(){';
		$s[] = self::renderOverlay();
		$s[] = '   })';
		$s[] = '})';

		//$document->addScriptDeclaration(implode("\n", $s));
		$app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->addInlineScript(implode("\n", $s));
	}

	public static function renderOverlay(){
		$s	 = array();
		$s[] = '		var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
		$s[] = '		phOverlay.appendTo(document.body);';
		$s[] = '		jQuery("#phOverlay").fadeIn().css("display","block");';
		return implode("\n", $s);
	}



	public static function renderImportExportItems($url, $messageBox, $formId, $count, $successMessage, $reload = 0) {

		$document	= Factory::getDocument();

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
		$s[] = '                   jQuery(phMessageBoxId).html(\'<div class="alert alert-dismissible fade show">'.$successMessage.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . Text::_('COM_PHOCACART_CLOSE', true) . '"></button></div>\');';

		if ($reload == 1) {
			// TO DO enable
			$s[] =	'	           window.setTimeout(function () {document.location.reload();}, 1000);';
			$s[] = self::renderOverlay();
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

		//$document->addScriptDeclaration(implode("\n", $s));
		$app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->addInlineScript(implode("\n", $s));

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
		$s[] = '   let phRequestActive = jQuery.ajax({';
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

		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
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
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	/* Ask for the changes, if something changes - e.g. category is created
	 * return this to inform and update status in wizard modal window
	 */

	public static function renderAjaxDoRequestWizard() {

		$s = PhocacartRenderStyle::getStyles();

		$js 	= array();
		$js[] = ' function phDoRequestWizard(url) {';
		$js[] = '   var s = [];';
		$js[] = '   s["phTrue"] = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.Text::_('COM_PHOCACART_CREATED').'</div>\';';
		$js[] = '   s["phTrueAdd"] = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.Text::_('COM_PHOCACART_ADDED').'</div>\';';
		$js[] = '   s["phTrueEdit"] = \'<div class="ph-true"><span class="glyphicon glyphicon-ok icon-ok"></span> '.Text::_('COM_PHOCACART_EDITED').'</div>\';';
		$js[] = '   s["phTrueAll"] = 1';
		$js[] = '   s["phFalse"] = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.Text::_('COM_PHOCACART_NOT_CREATED_YET').'</div>\';';
		$js[] = '   s["phFalseAdd"] = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.Text::_('COM_PHOCACART_NOT_ADDED_YET').'</div>\';';
		$js[] = '   s["phFalseEdit"] = \'<div class="ph-false"><span class="glyphicon glyphicon-remove icon-remove"></span> '.Text::_('COM_PHOCACART_NOT_EDITED_YET').'</div>\';';
		$js[] = '  phDoRequestWizardParent(url, s);';
		$js[] = '}';

		Factory::getDocument()->addScriptDeclaration(implode("\n", $js));
	}


	// ========
	// ROWS
	// ========

	/*public static function renderJsManageRowImage($i, $newRow) {

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

		$s 	= array();
		$s[] = 'var phRowCountImage = '.$i.';';
		$s[] = ' function phAddRowImage() {';
		$s[] = '   var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '   phAddRowImageParent(phNewRow);';
		$s[] = ' }';

		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}*/

	/*public static function renderJsManageRowAttribute($i, $newRow) {

		$url = 'index.php?option=com_phocacart&view=phocacartattributea&format=json&tmpl=component&'. Session::getFormToken().'=1';

		$s 	= array();
		$s[] = ' var phRowCountAttribute = '.$i.';';
		$s[] = ' function phAddRowAttribute() {';
		$s[] = '   var phRowOptionAttributeId = phRowCountAttribute;';// We need this Id for Options (added per $newRow)
		$s[] = '   var phNewRow = \'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   phAddRowAttributeParent(phNewRow);';
		$s[] = ' }';

		$s[] = ' ';
		$s[] = ' function phRemoveRowAttribute(id) {';
		$s[] = '   if(confirm(\''.Text::_('COM_PHOCACART_WARNING_REMOVING_ATTRIBUTE_DELETES_DOWNLOAD_FOLDER_DOWNLOAD_FILE').'\')){';
		$s[] = '      phRemoveRowAttributeParent(id, \''. $url .'\');';
		$s[] = '   }';
		$s[] = ' }';

		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}*/

	/*public static function renderJsManageRowOption($j,  $newRow, $newHeader) {

		$url = 'index.php?option=com_phocacart&view=phocacartattributea&format=json&tmpl=component&'. Session::getFormToken().'=1';

	    $s 	= array();
		$s[] = ' var phRowCountOption = '.$j.';';
		$s[] = ' function phAddRowOption(attrid) {';
		$s[] = '   var phNewRow		= 	\'<div></div>'. $newRow .'\';';
		$s[] = '   var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '   phAddRowOptionParent(phNewRow, phNewHeader, attrid, \''. $url .'\');';
		$s[] = ' }';

		$s[] = ' ';
		$s[] = ' function phRemoveRowOption(id, attrid) {';
		$s[] = '   if(confirm(\''.Text::_('COM_PHOCACART_WARNING_REMOVING_ATTRIBUTE_OPTION_DELETES_DOWNLOAD_FOLDER_DOWNLOAD_FILE').'\')){';
		$s[] = '      phRemoveRowOptionParent(id, attrid, \''. $url .'\');';
		$s[] = '   }';
		$s[] = ' }';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
		// #phrowboxoptionjs - header created by javascript
		// #phrowboxoption - header created by php/mysql
	}*/

	/*public static function renderJsManageRowSpecification($i, $newRow, $newHeader) {
		$s 	= array();
		$s[] = ' var phRowCountSpecification = '.$i.';';
		$s[] = ' function phAddRowSpecification() {';
		$s[] = '   var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '   phAddRowSpecificationParent(phNewRow, phNewHeader);';
		$s[] = '}';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
	}*/


/*
	public static function renderJsManageRowDiscount($i, $newRow, $newHeader) {

		$compatible = PhocacartUtils::isJCompatible('3.7') ? '1' : '0';
		$s 	= array();
		$s[] = ' var phRowCountDiscount = '.$i.';';
		$s[] = ' function phAddRowDiscount() {';
		$s[] = '   var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   var phNewHeader	= 	\'<div></div>'. $newHeader .'\';';
		$s[] = '   phAddRowDiscountParent(phNewRow, phNewHeader, '. $compatible . ');';
		$s[] = ' }';

		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
	}*/


	/*public static function renderJsManageRowPriceHistory($i, $newRow) {

		$compatible = PhocacartUtils::isJCompatible('3.7') ? '1' : '0';
		$s 	= array();
		$s[] = ' var phRowCountPricehistory = '.$i.';';
		$s[] = ' function phAddRowPricehistory() {';
		$s[] = '   var phNewRow		= 	\'<div></div>'. $newRow .'\';';// (to add option in right attribute box)
		$s[] = '   phAddRowPricehistoryParent(phNewRow, '. $compatible . ');';
		$s[] = '}';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
		// #phrowboxspecification - header created by javascript
		// #phrowboxspecificationheader - header created by php/mysql
	}*/

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
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	/* Really not nice way to move the system messages from bootstrap2 (Joomla) to bootstrap3 (Phoca)
	 * Don't add it phocacart.js as it should be run only in some views, not everywhere
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

		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}



	/*
	public static function renderJsAppendValueToUrl() {
		$s 	= array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
		$s[] = '   var stringToSend = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
		$s[] = '   var newUri = jQuery(\'.modal_jform_download_file\').attr(\'href\') + stringToSend;';
		$s[] = '   jQuery(\'.modal_jform_download_file\').attr("href", newUri);';
		$s[] = '})';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}

	public static function renderAjaxDoRequest($text) {


		$s 	= array();
		$s[] = ' ';
		$s[] = '/* Function phDoRequest *//*';
		$s[] = 'function phDoRequest(url, manager, value) {';
		$s[] = '   var phAjaxTop = \'<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> \' + \''. strip_tags(addslashes($text)).'\' + \'</div>\';';
		$s[] = '   jQuery("#ph-ajaxtop").html(phAjaxTop);';
		$s[] = '   jQuery("#ph-ajaxtop").show();';
		$s[] = '   var dataPost = {};';
		$s[] = '   dataPost[\'filename\'] = encodeURIComponent(value);';
		$s[] = '   dataPost[\'manager\'] = manager;';
		$s[] = '   let phRequestActive = jQuery.ajax({';
		$s[] = '      url: url,';
		$s[] = '      type:\'POST\',';
		$s[] = '      data:dataPost,';
		$s[] = '      dataType:\'JSON\',';
		$s[] = '      success:function(data){';
		$s[] = '         if ( data.status == 1 ){';
		$s[] = '            jQuery("#ph-ajaxtop-message").html(data.message);';
		$s[] = '            phRequestActive = null;';
		$s[] = '            setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 2500);';
		$s[] = '         } else {';
		$s[] = '	           jQuery("#ph-ajaxtop-message").html(data.error);';
		$s[] = '            phRequestActive = null;';
		$s[] = '	           setTimeout(function(){';
		$s[] = '		        jQuery("#ph-ajaxtop").hide(600);';
		$s[] = '		        jQuery(".ph-result-txt").remove();';
		$s[] = '	           }, 3500);';
		$s[] = '         }';
		$s[] = '      }';
		$s[] = '   });';
		$s[] = '}';
		$s[] = ' ';


		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
	}
	*/
}
?>
