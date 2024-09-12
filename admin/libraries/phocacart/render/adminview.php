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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Session\Session;

use Phoca\Render\Adminview;

class PhocacartRenderAdminview extends Adminview
{
	public $view = '';
	public $viewtype = 2;
	public $option = '';
	public $optionLang = '';
	public $compatible = false;
	public $sidebar = true;
	protected $document = false;

	public function __construct() {
		parent::__construct();
	}


	public static function renderWizardButton($type = 'enable', $idMd = '', $url = '', $w = '', $h = '') {

		$s 				= PhocacartRenderStyle::getStyles();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$enable_wizard	= $paramsC->get( 'enable_wizard', 1 );



		// We have two evens when clicking on start wizard button
		// Only applies to start wizard not on back wizard as this is not ajaxed - so controller will switch on the info
		$id 	= 'phStartWizdard'; // wizard will be enabled in system - in options, so other parts of website know it
		$class 	= 'button-options btn btn-warning '.$idMd.'ModalButton';// modal window with wizard will be popuped

		if ($type == 'back' && $enable_wizard > 0) {
			// BACK TO WIZARD (can be called everywhere)
			$bar = Toolbar::getInstance( 'toolbar' );
			$dhtml = '<joomla-toolbar-button><button onclick="Joomla.submitbutton(\'phocacartwizard.backtowizard\');" class="btn btn-small btn-warning">
	<span id="ph-icon-wizard" class="icon-dummy fa fa-edit fa-fw ph-icon-wizard fas fa fa-hat-wizard"></span>'.Text::_('COM_PHOCACART_BACK_TO_WIZARD').'</button></joomla-toolbar-button>';

			$bar->appendButton('Custom', $dhtml, 'wizard');
		} else if ($type == 'start') {
			// START WIZARD (can be called only in control panel) - to start in ohter place, back to wizard format needs to be copied
			// this button is starded by javascript in function modalWindowDynamic libraries\phocacart\render\adminview.php
			$bar = Toolbar::getInstance( 'toolbar' );
			$dhtml = '<joomla-toolbar-button><a href="'.$url.'" id="'.$id.'" class="'.$class.'" data-bs-target="#'.$idMd.'" data-bs-toggle="modal" data-src="'.$url.'" data-width="'.$w.'" data-heigth="'.$h.'">
	<span id="ph-icon-wizard" class="icon-dummy fa fa-edit fa-fw ph-icon-wizard fas fa fa-hat-wizard"></span>'.Text::_('COM_PHOCACART_START_WIZARD').'</a></joomla-toolbar-button>';
			$bar->appendButton('Custom', $dhtml, 'wizard');

			// We have displayed the modal with wizard
			// but we need to enable it in system so if we go from wizad to each task, it is enabled
			$urlAjaxEnableWizard 	= 'index.php?option=com_phocacart&task=phocacartwizard.enablewizard&format=json&'. Session::getFormToken().'=1';
			PhocacartRenderAdminjs::renderAjaxDoRequestWizardController($urlAjaxEnableWizard, $id, false);
		}
	}


	public function modalWindowDynamic($id, $textButton, $w = 700, $h = 400, $reload = false, $autoOpen = 0, $iframeLink = '', $iframeClass = '', $customFooter = '', $pageClass = '') {


		$s 	= array();

		if ($customFooter != '') {
			$footer = $customFooter;
		} else {
			$footer = '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-hidden="true">' . Text::_('COM_PHOCACART_CLOSE') . '</button>';
		}

		// Global Close
		//if ($autoOpen == 1) {
			$s[] = 'window.phCloseModal = function(){';
			//$s[] = '   jQuery("#'.$id.'").modal(\'hide\');';
			//$s[] = '   var modal = new bootstrap.Modal(document.getElementById("'.$id.'"), {});';
			//$s[] = '   modal.dispose();';

			$s[] = '   var modalEl = document.getElementById("'.$id.'");';
			//$s[] = '   var modal = bootstrap.Modal.getInstance(modalEl);';
			$s[] = '   var modal = bootstrap.Modal.getOrCreateInstance(modalEl);';
			$s[] = '   modal.hide();';
			$s[] = '   jQuery("#'.$id.' .modal-body").css("background", "");';// set back the backround, previously set to white for not first page of wizard
			$s[] = '}';
			$s[] = ' ';
		//}

		$s[] = 'jQuery(document).ready(function() {';

		if ($autoOpen == 1) {
			//$s[] = '   jQuery("#'.$id.'").modal("show");';

			$s[] = '   var modal = new bootstrap.Modal(document.getElementById("'.$id.'"), {});';
			$s[] = '   modal.show();';

			//$s[] = '   jQuery("#'.$id.'").on("shown", function() {';
			$s[] = '   var bsModal = document.getElementById("'.$id.'");';
			$s[] = '   bsModal.addEventListener("shown.bs.modal", function () {';
		} else {
			$s[] = '   jQuery(document.body).on(\'click\', \'.'.$id.'ModalButton\' ,function(e) {';
			$s[] = '      var src = jQuery(this).attr(\'data-src\');';

			// Specific case for downloadable files - they need to include token folder like product has (product and attribute download files are stored in one folder)
			if ($id == 'phFileDownloadNameModalO') {
				// Get value from each row not from main
                $s[] = '      var idFolder = \'#\' + jQuery(this).attr(\'data-id-folder\');';
				$s[] = '      var phDownloadFolder = jQuery(idFolder).val();';
				$s[] = '   	  src = src + "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
			}
		}




		if ($iframeLink != '') {

			$modalBody = '';

			$s[] = '      var src = "'.$iframeLink.'";';
			$s[] = '      jQuery("iframe").on("load", function(){';
			// Add specific class to body because of making the background transparent (body in iframe)
			$s[] = '         jQuery(this).contents().find("body").addClass("ph-body-iframe");';

			// Get information about current page in start wizard and set white background to all pages larger than 1
			// Only first page (page = 0) is different, it has background set in phocacart.css
			if ($pageClass != '') {
				$s[] = '         var phPage =jQuery(this).contents().find(".'.$pageClass.'").data("page");';
				$s[] = '         if (phPage > 0) {';
				$s[] = '            jQuery("#'.$id.' .modal-body").css("background", "#ffffff");';
				$s[] = '         }';
			}
			if ($iframeClass != '') {
				// Add specific class to body in iframe - to stylize it easily
				$s[] = '         jQuery(this).contents().find("body").addClass("'.strip_tags(htmlspecialchars($iframeClass, ENT_QUOTES, 'UTF-8')).'");';
			}
			$s[] = '      });';
		} else {
			// NO IFRAME LINK We have no iframe link, we will build it dynamically
			$modalBody = '<iframe frameborder="0"></iframe>';
			$s[] = '      jQuery("#'.$id.' iframe").attr({\'src\':src});';
		}

		//$s[] = '      var height = jQuery(this).attr(\'data-height\') || '.$w.';';
		//$s[] = '      var height = jQuery(window).height() - 200;';
		//$s[] = '      var width = jQuery(this).attr(\'data-width\') || '.$h.';';
		//$s[] = '      height = height + \'px\';';


		//$s[] = '      width = width + \'px\';';
		//$s[] = '      jQuery("#'.$id.' iframe").attr({\'src\':src, \'height\': height, \'width\': width});';


	///	$s[] = '      jQuery("#'.$id.' iframe").attr({\'src\':src});';
		//$s[] = ' var maxHeight = jQuery(this).height();';
		//$s[] = ' var maxHeightString = \'max-height:\' + maxHeight + \'px\';';
		//$s[] = '     jQuery("#'.$id.' iframe").attr({\'src\':src, \'height\': \''.$h.'px\', \'width\': \'auto\', \'style\' : maxHeightString});';
		$s[] = '   });';

		if ($reload) {
			//$s[] = '	jQuery("#'.$id.'").on("hidden", function () {';
			$s[] = '   var bsModal = document.getElementById("'.$id.'");';
			$s[] = '   bsModal.addEventListener("hidden.bs.modal", function () {';
			$s[] = '	   var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = '	   phOverlay.appendTo(document.body);';
			$s[] = '	   jQuery("#phOverlay").fadeIn().css("display","block");';
			$s[] = '		setTimeout(function(){';
			$s[] = '			window.parent.location.reload();';
			$s[] = '		},10);';
			$s[] = '	});';
		}
		$s[] = '})';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
		$html	= array();

		//libraries/src/HTML/Helpers/Bootstrap.php
		$html[] = HTMLHelper::_(
			'bootstrap.renderModal',
			$id,
			array(
				'url'    => $iframeLink,
				'title'  => Text::_($textButton),
				'width'  => '',
				'height' => '',
				'modalWidth' => '80',
				'bodyHeight' => '80',
				'footer' =>  $footer
			),
			$modalBody
		);
		return implode("\n", $html);

		/* Row
		$o .= ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url.$id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a></span>';
		*/
	}


}
/*
	public function startCp($id = '', $class = '') {

		$idO = '';
		if ($id != '') {
			$idO = ' id="'.$id.'"';
		}

		$classO = ' class="row"';
		if ($class != '') {
			$classO = ' class="row '.$class.'"';
		}

		$o = array();
		if ($this->compatible) {

			if ($this->sidebar) {

			} else {
				$o[] = '<div'. $idO . $classO .'>';
				$o[] = '<div id="j-sidebar-container" class="col-md-2">'.JHtmlSidebar::render().'</div>';
				$o[] = '<div id="j-main-container" class="col-md-10">';
			}

		} else {
			$o[] = '<div'. $idO . $classO .'>';
			//$o[] = '<div id="j-sidebar-container" class="span2">' . JHtmlSidebar::render() . '</div>'."\n";

			$o[] = '<div class="col-xs-12 col-sm-2 col-md-2 ph-admin-box-menu">'. JHtmlSidebar::render().'</div>';

			//$o[] = '<div id="j-main-container" class="span10">'."\n";
			$o[] = '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10 ph-admin-box-content ph-admin-manage">'. "\n";
		 	$o[] = '<div id="ph-system-message-container"></div>'. "\n";// specific container for moving messages from joomla to phoca
		PhocacartRenderAdminjs::moveSystemMessageFromJoomlaToPhoca();
		}

		return implode("\n", $o);
	}

	public function endCp() {

		$o = array();
		if ($this->compatible) {
			if ($this->sidebar) {

			} else {

				$o[] = '</div></div>';
			}
		} else {
			$o[] = '</div></div>';
		}

		return implode("\n", $o);
	}



	public function itemCalc($id, $name, $value, $form = 'pform', $size = 1, $class = '') {

		switch ($size){
			case 3: $class = 'input-xxlarge'. ' ' . $class;
			break;
			case 2: $class = 'input-xlarge'. ' ' . $class;
			break;
			case 0: $class = 'input-mini'. ' ' . $class;
			break;
			default: $class= 'input-small'. ' ' . $class;
			break;
		}
		$o = '';
		$o .= '<input type="text" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'" />';

		return $o;
	}

	public function itemCalcCheckbox($id, $name, $value, $form = 'pform' ) {

		$checked = '';
		if ($value == 1) {
			$checked = 'checked="checked"';
		}
		$o = '';
		$o .= '<input type="checkbox" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'"  '.$checked.' />';

		return $o;
	}

	/*
	* Common function for Image, Attribute, Option
	*//*
	public function addRowButton($text, $type = 'image') {


		$o = '<div id="phrowbox'.$type.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-success btn-mini" href="#" onclick="phAddRow'.ucfirst($type).'(); return false;"><i class="icon-plus"></i> '.$text.'</a></div>';
		return $o;
	}

	/*
	public function additionalImagesRow($id, $url, $value = '', $js = 0) {

		// Will be displayed inside Javascript
		$o = '<div class="ph-row-image'.$id.' ph-row-image" id="phrowimage'.$id.'" >'
		.'<div class="ph-add-item">'

		.'<div class="input-append input-group">'
		.'<input class="imageCreateThumbs" id="jform_image'.$id.'" name="pformimg['.$id.'][image]" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" class="form-control" size="40" type="text">'
		.'<a class="modal_jform_image btn" title="'.Text::_('COM_PHOCACART_FORM_SELECT_IMAGE').'" href="'.$url.$id.'"';

		if ($js == 1) {
			$o .= ' rel="{handler: \\\'iframe\\\', size: {x: 780, y: 560}}">';
		} else {
			$o .= ' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">';
		}

		$o .= Text::_('COM_PHOCACART_FORM_SELECT_IMAGE').'</a>'
		.'</div>'

		.'<input type="hidden" name="pformimg['.$id.'][imageid]" id="jform_imageid'.$id.'" value="'.$id.'" />'
		.'</div>'

		.'<div class="ph-remove-row"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowImage('.$id.'); return false;"><i class="icon-minus"></i> '.Text::_('COM_PHOCACART_REMOVE_IMAGE').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>';

		return $o;
	}*//*


	public function additionalImagesRow($id, $url, $value = '', $js = 0, $w = 700, $h = 400) {


		$idA			= 'phFileImageNameModalAT'; //phFileImageNameModal - standard image, phFileImageNameModalAT - additional images
		$textButton		= 'COM_PHOCACART_FORM_SELECT_IMAGE';

		// Will be displayed inside Javascript
		$o = '<div class="ph-row-image'.$id.' ph-row-image" id="phrowimage'.$id.'" >'
		.'<div class="ph-add-item">';

		$o .= '<span class="input-prepend input-append">';


		// Preview image - when changing the image dynamically the preview must be changed too
		$path = PhocacartPath::getPath('productimage'); // we use it only in image, could be changed in future - extended to others view like category, etc.
		if ($value && file_exists($path['orig_abs_ds'] . $value)) {
			$src = Uri::root() . $path['orig_rel_ds'] . $value;
		} else {
			$src = '';
		}


		$imgPreview = $src != '' ? '<img src="'.$src.'" alt="" />' : '<span class="glyphicon glyphicon-ban-circle ban-circle"></span>';

		$o .= '<span class="btn btn-primary btn-prepend ph-tooltip">'
			. '<span class="icon-eye icon-white"></span>'
			. '<span class="ph-tooltiptext" id=phTooltipImagePreview_jform_image'.$id.'>'.$imgPreview.'</span>'
			. '</span>';


		$o .='<input class="imageCreateThumbs inputbox" id="jform_image'.$id.'" name="pformimg['.$id.'][image]" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" size="40" type="text">';
		//$o .= '<a class="modal_jform_image btn" title="'.Text::_('COM_PHOCACART_FORM_SELECT_IMAGE').'" href="'.$url.$id.'"';

		//$o .= '<a href="#'.$idA.'" onclick="setPhRowImageId('.$id.')" role="button" class="btn btn-primary phbtnaddimages" data-bs-toggle="modal" title="' . Text::_($textButton) . '">'



			$o .= ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url.$id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a></span>';


		// Javascript rendered by modal windows $this->modalWindowDynamic() but in edit file to produce html code on right place


		$o .= '<input type="hidden" name="pformimg['.$id.'][imageid]" id="jform_imageid'.$id.'" value="'.$id.'" />'
		.'</div>'

		.'<div class="ph-remove-row"><a class="btn btn-danger" href="#" onclick="phRemoveRowImage('.$id.'); return false;"><i class="icon-minus"></i> '.Text::_('COM_PHOCACART_REMOVE_IMAGE').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>';

		return $o;
	}

	public function additionalAttributesRow($id, $idDb, $title, $alias, $required, $type, $js = 0) {

		$requiredArray	= PhocacartAttribute::getRequiredArray();
		$typeArray		= PhocacartAttribute::getTypeArray();
		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div id="phAttributeBox'.$id.'" class="ph-attribute-box" >';

		if ($id == 0) {
			// Add Header
			$o .= '<div class="ph-row">'."\n"
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_TITLE') . '</div>'
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_ALIAS') . '</div>'
			. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_REQUIRED') . '</div>'
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_TYPE') . '</div>'
			. '<div class="col-xs-12 col-sm-5 col-md-5">&nbsp;</div>'
			.'</div><div class="ph-cb"></div>'."\n";
		}


		$o .= '<div class="ph-row-attribute'.$id.' ph-row-attribute" id="phrowattribute'.$id.'" >'

		.'<div class="col-xs-12 col-sm-2 col-md-2">'
		.'<input id="jform_attrid'.$id.'" name="pformattr['.$id.'][id]" value="'.(int)$idDb.'" type="hidden">'
		.'<input id="jform_attrtitle'.$id.'" name="pformattr['.$id.'][title]" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" class="form-control input-small" size="40" type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-2 col-md-2">'
		.'<input id="jform_attralias'.$id.'" name="pformattr['.$id.'][alias]" value="'.htmlspecialchars($alias, ENT_QUOTES, 'UTF-8').'" class="form-control input-small" size="20" type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. HTMLHelper::_('select.genericlist', $requiredArray, 'pformattr['.$id.'][required]', 'class="input-mini"', 'value', 'text', htmlspecialchars($required, ENT_QUOTES, 'UTF-8'), 'jform_attrrequired'.$id)
		.'</div>'

		.'<div class="col-xs-12 col-sm-2 col-md-2">'
		. HTMLHelper::_('select.genericlist', $typeArray, 'pformattr['.$id.'][type]', 'class="input"', 'value', 'text', htmlspecialchars($type, ENT_QUOTES, 'UTF-8'), 'jform_attrtype'.$id)
		.'<input type="hidden" name="pformattr['.$id.'][attrid]" id="jform_attrid'.$id.'" value="'.$id.'" />'
		.'</div>'

		.'<div class="col-xs-12 col-sm-5 col-md-5"></div>'
		.'<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowAttribute('.$id.'); return false;" title="'.htmlspecialchars(Text::_('COM_PHOCACART_REMOVE_ATTRIBUTE'), ENT_QUOTES, 'UTF-8').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'

		. '</div>';

		if ($js == 1) {
			$o .= $this->addNewOptionButton($id, $js);
		}

		return $o;
	}

	/*
	 * 1 CALL IT BY JAVASCRIPT - we can add button and we can close the additionalAttributesRow box (JS -> BUTTON -> CLOSE)
     * 2 CALL IT BY PHP - we cannot add button and we cannot close the additionalAttributesRow box
	 *                    because we need to list options loaded by database, after they are loaded
	 *                    we call this function specially to add button and to close (inside javascript is it not called specially
	 *                    but by additionalAttributesRow function)
	 *                    (PHP -> OPTIONS -> BUTTON(ADDED SPECIAL) -> CLOSE (ADDED SPECIAL))
	 *                    BE AWARE js must be checked 2x - 1) it decides from where the code is loaded, 2) it changeds the output
	 *//*
	public function addNewOptionButton($id, $js) {

		$o = '';
		if ($js == 1) {
			$id = '\' + phRowOptionAttributeId +  \'';// if no javascript, get real id, if javascript, get js variable
		}
		$o .= '<div id="phrowboxoptionjs'.$id.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-primary btn-mini" href="#" onclick="phAddRowOption('.$id.'); return false;"><i class="icon-plus"></i> '.Text::_('COM_PHOCACART_ADD_OPTION').'</a></div>';

		$o .= '</div>';// !!! END OF additionalAttributesRow BOX

		return $o;
	}

	public function additionalOptionsRow($id, $attrId, $idDb, $title, $alias, $operator, $amount, $stock, $operatorWeight, $weight, $image, $image_medium, $image_small, $download_folder, $download_file, $download_token, $color, $defaultValue, $url, $url2, $url3, $urlO4, $w = 700, $h = 400) {



		$operatorArray 	= PhocacartAttribute::getOperatorArray();
		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div class="ph-option-box row" id="phOptionBox'.$attrId.$id.'">';
		$o .= '<div class="ph-row-option'.$attrId.$id.' ph-row-option-attrid'.$attrId.'" id="phrowoption'.$attrId.$id.'" >'

		.'<div class="col-xs-12 col-sm-2 col-md-2">'
		.'<input id="jform_optionid'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][id]" value="'.(int)$idDb.'" type="hidden">'
		.'<input id="jform_optiontitle'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][title]" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" class="form-control input-small" size="40" type="text" autofocus>'
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_optionalias'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][alias]" value="'.htmlspecialchars($alias, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini" size="30" type="text">'
		.'</div>'

		// Amount - Value
		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. HTMLHelper::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][options]['.$id.'][operator]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operator, ENT_QUOTES, 'UTF-8'), 'jform_optionoperator'.$attrId. $id)
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_optionamount'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][amount]" value="'.htmlspecialchars($amount, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini" size="30" type="text">'
		.'</div>'

		// Stock
		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_optionstock'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][stock]" value="'.htmlspecialchars($stock, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini" size="30" type="text">'

		//.'<input type="hidden" name="pformattr['.$attrId.'][options]['.$id.'][id]" id="jform_optionid'.$attrId.$id.'" value="'.$id.'" />'
		.'</div>'


		// Weight
		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. HTMLHelper::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][options]['.$id.'][operator_weight]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operatorWeight, ENT_QUOTES, 'UTF-8'), 'jform_optionoperatorweight'.$attrId. $id)
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_optionweight'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][weight]" value="'.htmlspecialchars($weight, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini" size="40" type="text">'
		.'</div>';


		// Images
		// -----
		$o .= '<div class="col-xs-12 col-sm-2 col-md-2">';

		/*if (is_numeric($attrId) && is_numeric($id)) {
			HTMLHelper::_('behavior.modal', 'a.modal_jform_optionimage'.$attrId.$id);
		} else {
			// Don't render anything for items which will be added by javascript
			// it is set in javascript addnewrow function
			// administrator\components\com_phocacart\libraries\phocacart\render\renderjs.php line cca 171
		}*//*

		// IMAGE LARGE

		$group 			= PhocacartUtilsSettings::getManagerGroup('productimage');
		$managerOutput	= '&amp;manager=productimage';
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$textButton2	= 'COM_PHOCACART_LARGE';
		//$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field=jform_optionimage'.$attrId.$id;
		$attr			= '';
		$idA			= 'phFileImageNameModalO';

		$html	= array();
		$html[] = '<span class="input-append input-group">';
		$html[] = '<input class="imageCreateThumbs ph-w40 input-mini" type="text" id="jform_optionimage'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][image]" value="'. htmlspecialchars($image, ENT_QUOTES, 'UTF-8').'"' .' '.$attr.' />';

		/*$html[] = '<a class="modal_jform_optionimage'.$attrId.$id.' btn" title="'.Text::_($textButton).'"'
				.' href="'.$link.'"'
				.' rel="{handler: &quot;iframe&quot;, size: {x: 780, y: 560}}">'
				. Text::_($textButton).'</a>';


		$html[] = '<a href="#'.$idA.'" onclick="setPhRowImgOptionId('.$attrId.','.$id.')" role="button" class="btn btn-primary phbtnaddimagesoptions" data-bs-toggle="modal" title="' . Text::_($textButton) . '">'
			. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a>';*//*

		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. Text::_($textButton2). '</a></span>';

		$html[] = '</span>'. "\n";

		$o .= implode("\n", $html);

		$o .= '<div class="ph-br-small"></div>';


		// IMAGE MEDIUM
		// -----------

		$attr			= '';
		$idA			= 'phFileImageNameModalO';
		$textButton2	= 'COM_PHOCACART_MEDIUM';

		$html	= array();
	/*	$html[] = '<span class="input-append input-group">';
		$html[] = '<input class="imageCreateThumbs ph-w40 input-mini" type="text" id="jform_optionimage_medium'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][image_medium]" value="'. htmlspecialchars($image_medium, ENT_QUOTES, 'UTF-8').'"' .' '.$attr.' />';
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url2 . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. Text::_($textButton2). '</a></span>';

		$html[] = '</span>'. "\n";

		*//*
		// We use only small image for icon and large image which have thumbnails (small, medium, large), so we don't need medium
		$html[] = '<input type="hidden" id="jform_optionimage_medium'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][image_medium]" value=""' .' '.$attr.' />';
		$o .= implode("\n", $html);
		/*
		$o .= '<div class="ph-br-small"></div>';
		*//*
		// IMAGE SMALL
		// -----------

		$attr			= '';
		$idA			= 'phFileImageNameModalO';
		$textButton2	= 'COM_PHOCACART_SMALL';

		$html	= array();
		$html[] = '<span class="input-append input-group">';
		$html[] = '<input class="imageCreateThumbs ph-w40 input-mini" type="text" id="jform_optionimage_small'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][image_small]" value="'. htmlspecialchars($image_small, ENT_QUOTES, 'UTF-8').'"' .' '.$attr.' />';
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url3 . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. Text::_($textButton2). '</a></span>';

		$html[] = '</span>'. "\n";

		$o .= implode("\n", $html);


		$o .= '</div>';


		// COLOR
		// -----
		$o .= '<div class="col-xs-12 col-sm-1 col-md-1">';

		$format 		= 'hex';
		$keywords 		= '';
		$validate 		= ' data-validate="hex"';
		$class			= '';
		$control		= '';
		$readonly		= '';
		$autocomplete 	= true;
		$lang 			= Factory::getLanguage();
		$position		= '';
		$disabled		= '';
		$required		= '';
		$onchange		= '';
		$autofocus		= 'autofocus';

		if (in_array($format, array('rgb', 'rgba')) && $validate != 'color') {
			$alpha = ($format == 'rgba') ? true : false;
			$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
		} else {
			$placeholder = '#rrggbb';
		}

		$inputclass   = ($keywords && ! in_array($format, array('rgb', 'rgba'))) ? ' keywords' : ' ' . $format;
		$class        = ' class="' . trim('minicolors ' . $class) . ($validate == 'color' ? '' : $inputclass) . '"';
		$control      = $control ? ' data-control="' . $control . '"' : '';
		$format       = $format ? ' data-format="' . $format . '"' : '';
		$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
		$readonly     = $readonly ? ' readonly' : '';
		$hint         = ' placeholder="' . $placeholder . '"';
		$autocomplete = ! $autocomplete ? ' autocomplete="off"' : '';
		$direction    = $lang->isRTL() ? ' dir="ltr" style="text-align:right"' : '';

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);
		HTMLHelper::_('behavior.colorpicker');

		/*$jQ = "jQuery('INPUT[type=minicolors]').on('change', function() {
					var hex = jQuery(this).val(),
					opacity = jQuery(this).attr('data-opacity');
					jQuery('BODY').css('backgroundColor', hex);

				});";
		Factory::getDocument()->addScriptDeclaration($jQ);*//*

		$html 	= array();
		$html[] =  '<input type="text" id="jform_optioncolor'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][color]"'
				. ' value="'. htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '"'
				. $hint . $class . $position . $control
				. $readonly . $disabled . $required . $onchange . $autocomplete . $autofocus
				. $format . $keywords . $direction . $validate . '/>';


		$o .= implode("\n", $html);
		$o .= '</div>';



		//$o .= '<div class="col-xs-12 col-sm-1 col-md-1"></div>';

		// ****
		// Design issue - reverse ordering because of float right, we just don't have enough bootstrap spans
		// ****
		$o .= '<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowOption('.$id.','.$attrId.'); return false;" title="'.htmlspecialchars(Text::_('COM_PHOCACART_REMOVE_OPTION'), ENT_QUOTES, 'UTF-8').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>';



		// DEFAULT VALUE
		// -------------
		$checkedDV = '';
		if ($defaultValue == 1) {
			$checkedDV = 'checked="checked"';
		}

		$o .= '<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<div class="ph-radio-top"><input id="jform_optiondefault_value'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][default_value]" class="form-control input-mini" size="10" type="checkbox" '.$checkedDV.'></div>'
		.'</div>';

		$o .= '<div class="ph-cb"></div>';


		// Second Row

		// DOWNLOAD FILE
		// -----------

		$group 			= PhocacartUtilsSettings::getManagerGroup('attributefile');
		$managerOutput	= '&amp;manager=productfile';
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$textButton2	= 'COM_PHOCACART_FILE';
		//$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field=jform_optionimage'.$attrId.$id;
		$attr			= '';
		$idA			= 'phFileDownloadNameModalO';

		$html	= array();
		$html[] = '<span class="input-append input-group">';
		$html[] = '<input class="imageCreateThumbs ph-w40 input-medium" type="text" id="jform_optiondownload_file'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][download_file]" value="'. htmlspecialchars($download_file, ENT_QUOTES, 'UTF-8').'"' .' '.$attr.' />';


		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$urlO4 . $attrId. $id.'" data-height="'.$h.'" data-width="'.$w.'" data-id-folder="jform_optiondownload_folder'.$attrId.$id.'">'
			. '<span class="icon-list icon-white"></span>'
			. Text::_($textButton2). '</a></span>';

		$html[] = '</span>'. "\n";



		$o .= '<div class="col-xs-12 col-sm-4 col-md-4"><div class="ph-product-attribute-download-title">'.Text::_('COM_PHOCACART_FIELD_DOWNLOAD_FOLDER_LABEL'). '</div></div>';
		$o .= '<div class="col-xs-12 col-sm-4 col-md-4"><div class="ph-product-attribute-download-title">'.Text::_('COM_PHOCACART_FIELD_DOWNLOAD_FILE_LABEL'). '</div></div>';
		$o .= '<div class="col-xs-12 col-sm-4 col-md-4"><div class="ph-product-attribute-download-title">'.Text::_('COM_PHOCACART_FIELD_DOWNLOAD_TOKEN_LABEL'). '</div></div>';

		$o .= '<div class="ph-cb"></div>';

		// Folder
		$o .= '<div class="col-xs-12 col-sm-4 col-md-4">';
		$o .= '<input id="jform_optiondownload_folder'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][download_folder]" value="'.htmlspecialchars($download_folder, ENT_QUOTES, 'UTF-8').'" class="form-control input-medium" size="40" type="text" readonly="readonly" data-attribute-id="'.$attrId.'" >';
		$o .= '</div>';

		// File
		$o .= '<div class="col-xs-12 col-sm-4 col-md-4">';
		$o .= implode("\n", $html);
		$o .= '</div>';

		// Token
		$o .= '<div class="col-xs-12 col-sm-4 col-md-4">';
		$o .= '<input id="jform_optiondownload_token'.$attrId.$id.'" name="pformattr['.$attrId.'][options]['.$id.'][download_token]" value="'.htmlspecialchars($download_token, ENT_QUOTES, 'UTF-8').'" class="form-control input-medium" size="40" type="text">';
		$o .= '</div>';



		$o .= '</div>'; // end row
		$o .= '</div>';// end box

		return $o;
	}

	public function headerOption($id = 0) {

		$o = '';

		// we have two phrowboxoptions - phrowboxoption - loaded with php/mysql, phrowboxoptionjs - added by javascript
		$o .= '<div id="phrowboxoption'.$id.'">';

		$o .= '<h4>'.Text::_('COM_PHOCACART_OPTIONS').'</h4>';
		$o .= '<div class="ph-row">'."\n"
		. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_TITLE') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_ALIAS') . '</div>'

		. '<div class="col-xs-12 col-sm-1 col-md-1">&nbsp;</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_VALUE') . '</div>'

		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_IN_STOCK') . '</div>'

		. '<div class="col-xs-12 col-sm-1 col-md-1">&nbsp;</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_WEIGHT') . '</div>'

		. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_IMAGES') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_COLOR') . '</div>'

		// Not enough columns
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_DEFAULT') . '</div>'
		//. '<div class="col-xs-12 col-sm-1 col-md-1">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";

		$o .= '</div>';
		return $o;
	}

/*
	public function additionalSpecificationsRow($id, $idDb, $title, $alias, $value, $alias_value, $group, $image, $image_medium, $image_small, $color, $js = 0, $url, $url2, $url3, $w = 700, $h = 400) {

		$groupArray	= PhocacartSpecification::getGroupArray();
		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div class="ph-specification-box" id="phSpecificationBox'.$id.'">';

		if ($id == 0) {
			// Add Header
			/*$o .= '<div class="ph-row">'."\n"
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_TITLE') . '</div>'
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_ALIAS') . '</div>'
			. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_REQUIRED') . '</div>'
			. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_TYPE') . '</div>'
			. '<div class="col-xs-12 col-sm-5 col-md-5">&nbsp;</div>'
			.'</div><div class="ph-cb"></div>'."\n";*//*
			$o .= $this->headerSpecification();
		}

		$o .= '<div class="ph-row-specification'.$id.' ph-row-specification" id="phrowspecification'.$id.'" >'

		.'<div class="col-xs-12 col-sm-3 col-md-3">'
		.'<input id="jform_specid'.$id.'" name="pformspec['.$id.'][id]" value="'.(int)$idDb.'" type="hidden">'
		.'<input id="jform_spectitle'.$id.'" name="pformspec['.$id.'][title]" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" class="form-control" size="40" type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-3 col-md-3">'
		.'<textarea id="jform_specvalue'.$id.'" name="pformspec['.$id.'][value]" class="form-control" rows="3" cols="10" type="textarea">'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'</textarea>'
		.'</div>'

		.'<div class="col-xs-12 col-sm-2 col-md-2">'
		. HTMLHelper::_('select.genericlist', $groupArray, 'pformspec['.$id.'][group_id]', 'class="input"', 'value', 'text', (int)$group, 'jform_specgroup'.$id)
		.'</div>'


		.'<div class="col-xs-12 col-sm-4 col-md-4"></div>'
		.'<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowSpecification('.$id.'); return false;" title="'.htmlspecialchars(Text::_('COM_PHOCACART_REMOVE_PARAMETER'), ENT_QUOTES, 'UTF-8').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'




		// ALIASES
		.'<div class="ph-row-specification">'

		.'<div class="col-xs-12 col-sm-3 col-md-3">'
		. Text::_('COM_PHOCACART_ALIAS_PARAMETER') . '<br /><input id="jform_specalias'.$id.'" name="pformspec['.$id.'][alias]" value="'.htmlspecialchars($alias, ENT_QUOTES, 'UTF-8').'" class="form-control" size="40" type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-3 col-md-3">'
		. Text::_('COM_PHOCACART_ALIAS_VALUE') . '<br /><input id="jform_specalias_value'.$id.'" name="pformspec['.$id.'][alias_value]" value="'.htmlspecialchars($alias_value, ENT_QUOTES, 'UTF-8').'" class="form-control" size="40" type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-2 col-md-2"> </div>'

		.'<div class="col-xs-12 col-sm-4 col-md-4"> </div>'
		.'<div class="ph-cb ph-pad-b"></div>'

		.'</div>';

		// COLOR AND SMALL IMAGE
		$o .= '<div class="ph-row-specification">';

		// IMAGE SMALL
		// -----------

		$o .= '<div class="col-xs-12 col-sm-3 col-md-3">';


		$group 			= PhocacartUtilsSettings::getManagerGroup('productimage');
		$managerOutput	= '&amp;manager=productimage';
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$attr			= '';
		$idA			= 'phFileImageNameModalS';
		$textButton2	= 'COM_PHOCACART_SMALL';

		$html	= array();
		$html[] = Text::_('COM_PHOCACART_IMAGES') . '<br />';
		$html[] = '<span class="input-append input-group">';
		$html[] = '<input class="imageCreateThumbs ph-w40 input-mini" type="text" id="jform_specimage_small'.$id.'" name="pformspec['.$id.'][image_small]" value="'.htmlspecialchars($image_small, ENT_QUOTES, 'UTF-8').'"' .' '.$attr.' />';
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url3 . $id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span>'
			. Text::_($textButton2). '</a></span>';

	//	$html[] = '</span>'. "\n";

		$o .= implode("\n", $html);


		$o .= '</div>';


		// COLOR
		// -----
		$o .= '<div class="col-xs-12 col-sm-3 col-md-3">';

		$format 		= 'hex';
		$keywords 		= '';
		$validate 		= ' data-validate="hex"';
		$class			= '';
		$control		= '';
		$readonly		= '';
		$autocomplete 	= true;
		$lang 			= Factory::getLanguage();
		$position		= '';
		$disabled		= '';
		$required		= '';
		$onchange		= '';
		//$autofocus		= 'autofocus';
		$autofocus		= '';


		if (in_array($format, array('rgb', 'rgba')) && $validate != 'color') {
			$alpha = ($format == 'rgba') ? true : false;
			$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
		} else {
			$placeholder = '#rrggbb';
		}

		$inputclass   = ($keywords && ! in_array($format, array('rgb', 'rgba'))) ? ' keywords' : ' ' . $format;
		$class        = ' class="' . trim('minicolors ' . $class) . ($validate == 'color' ? '' : $inputclass) . '"';
		$control      = $control ? ' data-control="' . $control . '"' : '';
		$format       = $format ? ' data-format="' . $format . '"' : '';
		$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
		$readonly     = $readonly ? ' readonly' : '';
		$hint         = ' placeholder="' . $placeholder . '"';
		$autocomplete = ! $autocomplete ? ' autocomplete="off"' : '';
		$direction    = $lang->isRTL() ? ' dir="ltr" style="text-align:right"' : '';

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);
		HTMLHelper::_('behavior.colorpicker');

		/*$jQ = "jQuery('INPUT[type=minicolors]').on('change', function() {
					var hex = jQuery(this).val(),
					opacity = jQuery(this).attr('data-opacity');
					jQuery('BODY').css('backgroundColor', hex);

				});";
		Factory::getDocument()->addScriptDeclaration($jQ);*//*

		$html 	= array();
		$html[] = Text::_('COM_PHOCACART_COLOR') . '<br />';
		$html[] =  '<input type="text" id="jform_speccolor'.$id.'" name="pformspec['.$id.'][color]" value="'.htmlspecialchars($color, ENT_QUOTES, 'UTF-8').'" '
			. $hint . $class . $position . $control
			. $readonly . $disabled . $required . $onchange . $autocomplete . $autofocus
			. $format . $keywords . $direction . $validate . '/>';


		$o .= implode("\n", $html);
		$o .= '</div>';



		$o .= '<div class="col-xs-12 col-sm-2 col-md-2"> </div>';

		$o .= '<div class="col-xs-12 col-sm-4 col-md-4"> </div>';
		$o .= '<div class="ph-cb ph-pad-b"></div>';

		$o .= '</div>';// END Colors and images



		$o .= '</div>'
		. '</div>';


		return $o;
	}*//*

	public function headerSpecification() {
		//$o = '<div class="ph-row" id="phrowboxspecificationheader">'."\n"
		$o = '<div class="row ph-row">'."\n"
		. '<div class="col-xs-12 col-sm-3 col-md-3">'. Text::_('COM_PHOCACART_PARAMETER') . '</div>'
		. '<div class="col-xs-12 col-sm-3 col-md-3">'. Text::_('COM_PHOCACART_VALUE') . '</div>'
		. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_GROUP') . '</div>'
		. '<div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		return $o;
	}


	public static function getCalendarAttributes($js = 0, $initialize = 0) {

		// Calendar
		// Initialized only for php, not javascript
		// we have inintialized calender in publishing options so there is no need to render it
		// when no item loaded from php, when only javascript rows will be added
		if ($initialize == 1) {
			$tag       = Factory::getLanguage()->getTag();
			$calendar  = Factory::getLanguage()->getCalendar();
			$direction = strtolower(Factory::getDocument()->getDirection());

			// Get the appropriate file for the current language date helper
			$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';

			if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar)))
			{
				$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
			}

			// Get the appropriate locale file for the current language
			$localesPath = 'system/fields/calendar-locales/en.js';

			if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js'))
			{
				$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
			}
			elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js'))
			{
				$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
			}
			Factory::getDocument()->addScript(Uri::root(true).'/',$helperPath);
			Factory::getDocument()->addScript(Uri::root(true).'/',$localesPath);
		}
		if ($js == 0) {
			HTMLHelper::_('jquery.framework');
			HTMLHelper::_('script', 'system/html5fallback.js', false, true);
		}
		$attributes['size'] 		= 30;
		$attributes['maxlength'] 	= 30;
		$attributes['class'] 		= "input-mini";

		return $attributes;
	}




	public function additionalDiscountsRow($id, $idDb, $title, $alias, $access, $group, $discount, $calculation_type, $quantity_from, $quantity_to, $valid_from, $valid_to, $js = 0) {


		// Calendar
		$attributes = self::getCalendarAttributes($js);
		$valid_from = self::getCalendarDate($valid_from);
		$valid_to	= self::getCalendarDate($valid_to);


		$calcTypeArray	= PhocacartUtilsSettings::getDiscountCalculationTypeArray();
		if ($calculation_type == '') {
			//Set percentage as default for new rows
			$calculation_type = 1;
		}

		$o				= '';


		if ($id == 0 && $js == 0) {
			// Add Header
			$o .= $this->headerDiscount();
		}

		// Will be displayed inside Javascript
		$o .= '<div class="ph-discount-box" id="phDiscountBox'.$id.'">';



		$o .= '<div class="ph-row-discount'.$id.' ph-row-discount" id="phrowdiscount'.$id.'" >'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_discid'.$id.'" name="pformdisc['.$id.'][id]" value="'.(int)$idDb.'" type="hidden">'
		.'<input id="jform_disctitle'.$id.'" name="pformdisc['.$id.'][title]" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini"  type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_discalias'.$id.'" name="pformdisc['.$id.'][alias]" value="'.htmlspecialchars($alias, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini"  type="text">'
		.'</div>'


		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. HTMLHelper::_('access.level', 'pformdisc['.$id.'][access]', (int)$access, 'class="input input-small"', array(), 'jform_discaccess'.$id)
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. PhocacartGroup::getAllGroupsSelectBox('pformdisc['.$id.'][groups][]', 'jform_discaccess'.$id, $group, NULL, 'id', 'class="input input-small" size="4" multiple="multiple"' )
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_discdiscount'.$id.'" name="pformdisc['.$id.'][discount]" value="'.htmlspecialchars($discount, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini"  type="text">'
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		. HTMLHelper::_('select.genericlist', $calcTypeArray, 'pformdisc['.$id.'][calculation_type]', 'class="input input-small"', 'value', 'text', (int)$calculation_type, 'jform_disccalculation_type'.$id)
		.'</div>'

		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_discquantity_from'.$id.'" name="pformdisc['.$id.'][quantity_from]" value="'.htmlspecialchars($quantity_from, ENT_QUOTES, 'UTF-8').'" class="form-control input-mini" type="text">'
		.'</div>';

		/*
		.'<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<input id="jform_discquantity_to'.$id.'" name="pformdisc['.$id.'][quantity_to]" value="'.htmlspecialchars($quantity_to).'" class="form-control input-mini"  type="text">'
		.'</div>';*//*



		$o .= '<div class="col-xs-12 col-sm-2 col-md-2">';
		if ($js == 1) {

			if (PhocacartUtils::isJCompatible('3.7')) {
				$o .= HTMLHelper::_('calendar', $valid_from, 'pformdisc['.$id.'][valid_from]', 'jform_discvalid_from'.$id, '%Y-%m-%d', $attributes);
			} else {
				// Calender is initialized and cannot access DOM then so we need to render it manually in javascript
				// and then initialize the calendar in phAddRowDiscount function
				$o .= '<div class="input-append input-group">'
				. '<input title="" name="pformdisc['.$id.'][valid_from]" id="jform_discvalid_from'.$id.'" value="'.htmlspecialchars($quantity_from, ENT_QUOTES, 'UTF-8').'" size="30" maxlength="30" class="input-mini hasTooltip" type="text">'
				. '<button type="button" class="btn btn-primary" id="jform_discvalid_from'.$id.'_img"><span class="icon-calendar"></span></button>'
				.'</div>';

				$o .= '\<script\>Calendar.setup({'
				. '		inputField: "jform_discvalid_from'.$id.'",'
				. '		ifFormat: "%Y-%m-%d",'
				. '		button: "jform_discvalid_from'.$id.'_img",'
				. '		align: "Tl",'
				. '		singleClick: true,'
				. '		firstDay: 0'
				. '	})\<\/script\>';
			}

		} else {
			$o .= HTMLHelper::_('calendar', $valid_from, 'pformdisc['.$id.'][valid_from]', 'jform_discvalid_from'.$id, '%Y-%m-%d', $attributes);
		}
		$o .= '</div>';


		$o .= '<div class="col-xs-12 col-sm-2 col-md-2">';
		if ($js == 1) {
			if (PhocacartUtils::isJCompatible('3.7')) {
				$o .=  HTMLHelper::_('calendar', $valid_to, 'pformdisc['.$id.'][valid_to]', 'jform_discvalid_to'.$id, '%Y-%m-%d', $attributes);
			} else {
				$o .= '<div class="input-append input-group">'
				. '<input title="" name="pformdisc['.$id.'][valid_to]" id="jform_discvalid_to'.$id.'" value="'.htmlspecialchars($quantity_from, ENT_QUOTES, 'UTF-8').'" size="30" maxlength="30" class="input-mini hasTooltip" type="text">'
				. '<button type="button" class="btn btn-primary" id="jform_discvalid_to'.$id.'_img"><span class="icon-calendar"></span></button>'
				.'</div>';

				$o .= '\<script\>Calendar.setup({'
				. '		inputField: "jform_discvalid_to'.$id.'",'
				. '		ifFormat: "%Y-%m-%d",'
				. '		button: "jform_discvalid_to'.$id.'_img",'
				. '		align: "Tl",'
				. '		singleClick: true,'
				. '		firstDay: 0'
				. '	})\<\/script\>';
			}


		} else {
			$o .=  HTMLHelper::_('calendar', $valid_to, 'pformdisc['.$id.'][valid_to]', 'jform_discvalid_to'.$id, '%Y-%m-%d', $attributes);
		}
		$o .= '</div>';



		$o .= '<div class="col-xs-12 col-sm-1 col-md-1"></div>'
		.'<div class="ph-float-icon"><a class="btn btn-transparent" href="#" onclick="phRemoveRowDiscount('.$id.'); return false;" title="'.htmlspecialchars(Text::_('COM_PHOCACART_REMOVE_DISCOUNT'), ENT_QUOTES, 'UTF-8').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'


		. '</div>'
		. '</div>';


		return $o;
	}

	public function headerDiscount() {
		//$o = '<div class="ph-row" id="phrowboxspecificationheader">'."\n"
		$o = '<div class="row ph-row">'."\n"
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_TITLE') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_ALIAS') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_ACCESS') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_GROUP') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_DISCOUNT') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_CALCULATION_TYPE') . '</div>';

		/*. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_QUANTITY_FROM') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_QUANTITY_TO') . '</div>'*//*
		$o .= '<div class="col-xs-12 col-sm-1 col-md-1">'. Text::_('COM_PHOCACART_MINIMUM_QUANTITY') . '</div>'
		. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_VALID_FROM') . '</div>'
		. '<div class="col-xs-12 col-sm-2 col-md-2">'. Text::_('COM_PHOCACART_VALID_TO') . '</div>'
		. '<div class="col-xs-12 col-sm-1 col-md-1">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		return $o;
	}


	public function additionalPricehistoryRow($id, $idDb, $price, $date, $productId, $js = 0) {


		// Calendar
		$date 					= self::getCalendarDate($date);
		$attributes 			= self::getCalendarAttributes($js);
		$attributes['class'] 	= "input";

		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div class="ph-pricehistory-box" id="phPricehistoryBox'.$id.'">';

		$o .= '<div class="ph-row-pricehistory'.$id.' ph-row-pricehistory" id="phrowpricehistory'.$id.'" >';



		$o .= '<div class="col-xs-12 col-sm-3 col-md-3">';
		$o .= HTMLHelper::_('calendar', $date, 'jform['.$id.'][date]', 'jform_date'.$id, '%Y-%m-%d', $attributes);
		$o .= '</div>';

		// Set value from database
		$priceN = '';
		if (isset($price)) {
			$priceN = $price;
			if ($priceN > 0 || $priceN == 0) {
				$priceN = PhocacartPrice::cleanPrice($priceN);
			}
		}
		$o .= '<div class="col-xs-12 col-sm-2 col-md-2">';
		$o .= '<input type="text" class="input-small" name="jform['.$id.'][price]" value="'.$priceN.'" />';
		$o .= '<input type="hidden" name="jform['.$id.'][product_id]" value="'.$productId.'" />';
		$o .= '</div>';




		$o .= '<div class="col-xs-12 col-sm-1 col-md-1">'
		.'<div class="ph-float-icon-l"><a class="btn btn-transparent" href="#" onclick="phRemoveRowPricehistory('.$id.'); return false;" title="'.htmlspecialchars(Text::_('COM_PHOCACART_REMOVE_PRICE'), ENT_QUOTES, 'UTF-8').'"><i class="icon-cancel ph-icon-remove"></i>'.''.'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>';
		$o .= '</div>';

		$o .= '<div class="col-xs-12 col-sm-6 col-md-6">';
		$o .= '</div>';

		$o .= '</div>';
		$o .= '</div>';

		$o .= '<div class="ph-cb"></div>'."\n";


		return $o;
	}


	public function modalWindow($id, $link, $textButton) {

		// Add javascript for additional images
		// Specific case for additional images
		// In case we have more than one "select image form input" and the additional form inputs are made by javascript
		// we need to differentiate between them - the field id for each form input
		// phRowImage is a variable set when clicking select button for additional images
		//$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id . '\'+ (phRowImage) +\'';
		$html	= array();
		$html[] = HTMLHelper::_(
			'bootstrap.renderModal',
			$id,
			array(
				'url'    => $link,
				'title'  => Text::_($textButton),
				'width'  => '700px',
				'height' => '400px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' => '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-hidden="true">'
					. Text::_('COM_PHOCACART_CLOSE') . '</button>'
			)
		);
		return implode("\n", $html);
	}


	public function modalWindowDynamic($id, $textButton, $w = 700, $h = 400, $reload = false, $autoOpen = 0, $iframeLink = '', $iframeClass = '', $customFooter = '', $pageClass = '') {


		$s 	= array();

		if ($customFooter != '') {
			$footer = $customFooter;
		} else {
			$footer = '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-hidden="true">' . Text::_('COM_PHOCACART_CLOSE') . '</button>';
		}

		// Global Close
		//if ($autoOpen == 1) {
			$s[] = 'window.phCloseModal = function(){';
			$s[] = '   jQuery("#'.$id.'").modal(\'hide\');';
			$s[] = '}';
			$s[] = ' ';
		//}

		$s[] = 'jQuery(document).ready(function() {';

		if ($autoOpen == 1) {
			$s[] = '   jQuery("#'.$id.'").modal("show");';
			$s[] = '   jQuery("#'.$id.'").on("shown", function() {';
		} else {
			$s[] = '   jQuery(document.body).on(\'click\', \'.'.$id.'ModalButton\' ,function(e) {';
			$s[] = '      var src = jQuery(this).attr(\'data-src\');';

			// Specific case for downloadable files - they need to include token folder like product has (product and attribute download files are stored in one folder)
			if ($id == 'phFileDownloadNameModalO') {
				// Get value from each row not from main
                $s[] = '      var idFolder = \'#\' + jQuery(this).attr(\'data-id-folder\');';
				$s[] = '      var phDownloadFolder = jQuery(idFolder).val();';
				$s[] = '   	  src = src + "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
			}
		}

		if ($iframeLink != '') {
			$s[] = '      var src = "'.$iframeLink.'";';
			$s[] = '      jQuery("iframe").load(function(){';
			// Add specific class to body because of making the background transparent (body in iframe)
			$s[] = '         jQuery(this).contents().find("body").addClass("ph-body-iframe");';

			// Get information about current page in start wizard and set white background to all pages larger than 1
			// Only first page (page = 0) is different, it has background set in phocacart.css
			if ($pageClass != '') {
				$s[] = '         var phPage =jQuery(this).contents().find(".'.$pageClass.'").data("page");';
				$s[] = '         if (phPage > 0) {';
				$s[] = '            jQuery("#'.$id.' .modal-body").css("background", "#ffffff");';
				$s[] = '         }';
			}
			if ($iframeClass != '') {
				// Add specific class to body in iframe - to stylize it easily
				$s[] = '         jQuery(this).contents().find("body").addClass("'.strip_tags(htmlspecialchars($iframeClass, ENT_QUOTES, 'UTF-8')).'");';
			}
			$s[] = '      });';
		}

		//$s[] = '      var height = jQuery(this).attr(\'data-height\') || '.$w.';';
		$s[] = '      var height = jQuery(window).height() - 200;';
		$s[] = '      var width = jQuery(this).attr(\'data-width\') || '.$h.';';
		$s[] = '      height = height + \'px\';';


		$s[] = '      width = width + \'px\';';
		$s[] = '      jQuery("#'.$id.' iframe").attr({\'src\':src, \'height\': height, \'width\': width});';
		//$s[] = ' var maxHeight = jQuery(this).height();';
		//$s[] = ' var maxHeightString = \'max-height:\' + maxHeight + \'px\';';
		//$s[] = '     jQuery("#'.$id.' iframe").attr({\'src\':src, \'height\': \''.$h.'px\', \'width\': \'auto\', \'style\' : maxHeightString});';
		$s[] = '   });';

		if ($reload) {
			$s[] = '	jQuery("#'.$id.'").on("hidden", function () {';
			$s[] = '	   var phOverlay = jQuery(\'<div id="phOverlay"><div id="phLoaderFull"> </div></div>\');';
			$s[] = '	   phOverlay.appendTo(document.body);';
			$s[] = '	   jQuery("#phOverlay").fadeIn().css("display","block");';
			$s[] = '		setTimeout(function(){';
			$s[] = '			window.parent.location.reload();';
			$s[] = '		},10);';
			$s[] = '	});';
		}
		$s[] = '})';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

		$html	= array();
		$html[] = HTMLHelper::_(
			'bootstrap.renderModal',
			$id,
			array(
				//'url'    => $link,
				'title'  => Text::_($textButton),
				'width'  => $w.'px',
				'height' => $h.'px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' =>  $footer
			),
			'<iframe frameborder="0"></iframe>'
		);
		return implode("\n", $html);

		/* Row
		$o .= ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$url.$id.'" data-height="'.$h.'" data-width="'.$w.'">'
			. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a></span>';
		*//*
	}

	public static function renderWizardButton($type = 'enable', $idMd = '', $url = '', $w = '', $h = '') {

		$s 				= PhocacartRenderStyle::getStyles();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$enable_wizard	= $paramsC->get( 'enable_wizard', 1 );



		// We have two evens when clicking on start wizard button
		// Only applies to start wizard not on back wizard as this is not ajaxed - so controller will switch on the info
		$id 	= 'phStartWizdard'; // wizard will be enabled in system - in options, so other parts of website know it
		$class 	= 'btn btn-small btn-warning '.$idMd.'ModalButton';// modal window with wizard will be popuped

		if ($type == 'back' && $enable_wizard > 0) {
			// BACK TO WIZARD (can be called everywhere)
			$bar = Toolbar::getInstance( 'toolbar' );
			$dhtml = '<button onclick="Joomla.submitbutton(\'phocacartwizard.backtowizard\');" class="btn btn-small btn-warning">
	<span id="ph-icon-wizard" class="icon-dummy fa fa-edit fa-fw ph-icon-wizard"></span>'.Text::_('COM_PHOCACART_BACK_TO_WIZARD').'</button>';

			$bar->appendButton('Custom', $dhtml, 'wizard');
		} else if ($type == 'start') {
			// START WIZARD (can be called only in control panel) - to start in ohter place, back to wizard format needs to be copied
			// this button is starded by javascript in function modalWindowDynamic libraries\phocacart\render\adminview.php
			$bar = Toolbar::getInstance( 'toolbar' );
			$dhtml = '<button id="'.$id.'" class="'.$class.'" data-bs-target="#'.$idMd.'" data-toggle="modal" data-src="'.$url.'" data-width="'.$w.'" data-heigth="'.$h.'">
	<span id="ph-icon-wizard" class="icon-dummy fa fa-edit fa-fw ph-icon-wizard"></span>'.Text::_('COM_PHOCACART_START_WIZARD').'</button>';
			$bar->appendButton('Custom', $dhtml, 'wizard');

			// We have displayed the modal with wizard
			// but we need to enable it in system so if we go from wizad to each task, it is enabled
			$urlAjaxEnableWizard 	= 'index.php?option=com_phocacart&task=phocacartwizard.enablewizard&format=json&'. Session::getFormToken().'=1';
			PhocacartRenderAdminjs::renderAjaxDoRequestWizardController($urlAjaxEnableWizard, $id, false);
		}
	}
}*/
?>
