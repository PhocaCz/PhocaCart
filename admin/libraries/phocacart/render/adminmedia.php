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
use Joomla\CMS\Version;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;


class PhocacartRenderAdminmedia
{

	protected $document		= false;
	public $compatible		= false;
	public $view 			= '';

	public function __construct() {


		$app				= Factory::getApplication();
		$version 			= new Version();
		$this->compatible 	= $version->isCompatible('4.0.0-alpha');
		$this->document		= Factory::getDocument();
		$this->view			= $app->input->get('view');

		HTMLHelper::_('jquery.framework');

		HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacart.js', array('version' => 'auto'));


		// FORM
		// Lang starting with "PHOCA_" - general phoca string used e.g. in general JS libraries
		$this->document->addScriptOptions('phLang', array(
			'COM_PHOCACART_CLOSE' => Text::_('COM_PHOCACART_CLOSE'),
			'COM_PHOCACART_ERROR_TITLE_NOT_SET' => Text::_('COM_PHOCACART_ERROR_TITLE_NOT_SET'),
			'PHOCA_CLICK_TO_EDIT' => Text::_('COM_PHOCACART_CLICK_TO_EDIT'),
			'PHOCA_CANCEL' => Text::_('COM_PHOCACART_CANCEL'),
			'PHOCA_SUBMIT' => Text::_('COM_PHOCACART_SUBMIT'),
			'PHOCA_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION' => Text::_('COM_PHOCACART_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION')
		));
		$this->document->addScriptOptions('phVars', array('token' => Session::getFormToken(), 'urleditinplace' => Uri::base(true).'/index.php?option=com_phocacart&task=phocacarteditinplace.editinplacetext&format=json&'. Session::getFormToken().'=1'));

		//$this->document->getDocument()->addScriptOptions('phParams', array());
        HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacartform.js', array('version' => 'auto'));

		//HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons.min.css', array('version' => 'auto'));
		//HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap-grid.min.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacart.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacarttheme.css', array('version' => 'auto'));
		//HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacartcustom.css', array('version' => 'auto'));
		//HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons-icons-only.min.css', array('version' => 'auto'));


		// CP View - load everywhere because of menu
		//if ($this->view ==  null) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/duotone/joomla-fonts.css', array('version' => 'auto'));
		//}


		//if ($this->compatible) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/4.css', array('version' => 'auto'));
		//} else {
		//	HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/3.css', array('version' => 'auto'));
		//}


		//if(PhocacartUtils::isJCompatible('3.7')) {
			//HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/37.css', array('version' => 'auto'));
		//}

		$lang = Factory::getLanguage();
		if ($lang->isRtl()){
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/rtl.css', array('version' => 'auto'));
		}

		// EDIT IN PLACE
		$urlText = Uri::base(true).'/index.php?option=com_phocacart&task=phocacarteditinplace.editinplacetext&format=json&'. Session::getFormToken().'=1';
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.jeditable.min.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.jeditable.autogrow.min.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.autogrowtextarea.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.phocajeditable.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.jeditable.masked.min.js', array('version' => 'auto'));
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.maskedinput.min.js', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/js/jeditable/phocajeditable.css', array('version' => 'auto'));
/*
		$s 	= array();
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery(".ph-editinplace-text").editable("'.$urlText.'", {';
		$s[] = '      tooltip : "'.JText::_('COM_PHOCACART_CLICK_TO_EDIT').'",'; //submit : \'OK\',
		$s[] = '      select : true,';
		$s[] = '      type : jQuery(this).hasClass("autogrow") ? "text" : "textarea",';
		$s[] = '      cancel : "'.Text::_('COM_PHOCACART_CANCEL').'",';
		$s[] = '      submit : "'.Text::_('COM_PHOCACART_SUBMIT').'",';
		$s[] = '      cssclass : \'ph-edit-in-place-class\',';
		$s[] = '      cancelcssclass : \'btn btn-danger\',';
		$s[] = '      submitcssclass : \'btn btn-success\',';

		//DEBUG
		//$s[] = '     onblur : function() { ... },';

		$s[] = '     onblur : function() { ... },';

 		$s[] = '      intercept : function(jsondata) {';
		$s[] = '          json = JSON.parse(jsondata);';

		$s[] = '		  if (json.status == 0){';

		$s[] = '		     jQuery("#ph-ajaxtop").html(phGetMsg(\' &nbsp; \', 1));';
		$s[] = '             jQuery("#ph-ajaxtop").show();';
		$s[] = '             jQuery("#ph-ajaxtop-message").html(phGetMsg(json.error, 0));';
		$s[] = '             phCloseMsgBoxError();';
		$s[] = '             this.reset();';

		$s[] = '          } else {';

		$s[] = '             if (json.idcombined && json.resultcombined) {';
		$s[] = '			    var combinedElement = "#" + phEscapeColon(json.idcombined);';
		$s[] = '                jQuery(combinedElement).html(json.resultcombined);';
		$s[] = '                phChangeBackground(combinedElement, 700, "#D4E9E6");';
		$s[] = '             }';

		$s[] = '             var currentElement = "#" + phEscapeColon(jQuery(this).attr("id"))';
		$s[] = '             phChangeBackground(currentElement, 700, "#D4E9E6" );';
		$s[] = '			 return json.result;';
		$s[] = '          }';

    	$s[] = '      },';
		$s[] = '      placeholder: "",';

		// Possible information for parts on the site which will be not changed by chaning the value (for example currency view - currency rate)
		$s[] = '      callback: function() {';
		$s[] = '      	var chEIP = ".phChangeEditInPlace" + jQuery(this).attr("data-id");';
		$s[] = '      	jQuery(chEIP).html("'.Text::_('COM_PHOCACART_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION').'")';
		$s[] = '      },';

		$s[] = '   })';
		$s[] = '})';
		$s[] = ' ';

		$this->document->addScriptDeclaration(implode("\n", $s));*/
	}

	public function loadOptions($load = 0) {
		if ($load == 1) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacartoptions.css', array('version' => 'auto'));
		}
	}
}
?>
