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

use Joomla\CMS\HTML\HTMLHelper;

class PhocacartRenderAdminmedia
{

	protected $document		= false;
	public $compatible		= false;
	public $view 			= '';

	public function __construct() {


		$app				= JFactory::getApplication();
		$version 			= new \Joomla\CMS\Version();
		$this->compatible 	= $version->isCompatible('4.0.0-alpha');
		$this->document		= JFactory::getDocument();
		$this->view			= $app->input->get('view');

		Joomla\CMS\HTML\HTMLHelper::_('jquery.framework');

		HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacart.js', array('version' => 'auto'));

        // FORM
		$this->document->addScriptOptions('phLang', array('COM_PHOCACART_CLOSE' => JText::_('COM_PHOCACART_CLOSE'), 'COM_PHOCACART_ERROR_TITLE_NOT_SET' => JText::_('COM_PHOCACART_ERROR_TITLE_NOT_SET')));
		$this->document->addScriptOptions('phVars', array('token' => JSession::getFormToken()));
		//$this->document->getDocument()->addScriptOptions('phParams', array());
        HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacartform.js', array('version' => 'auto'));

		//HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons.min.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap-grid.min.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacart.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacarttheme.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacartcustom.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons-icons-only.min.css', array('version' => 'auto'));


		// CP View - load everywhere because of menu
		//if ($this->view ==  null) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/duotone/joomla-fonts.css', array('version' => 'auto'));
		//}


		if ($this->compatible) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/4.css', array('version' => 'auto'));
		} else {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/3.css', array('version' => 'auto'));
		}


		if(PhocacartUtils::isJCompatible('3.7')) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/37.css', array('version' => 'auto'));
		}

		$lang = JFactory::getLanguage();
		if ($lang->isRtl()){
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/rtl.css', array('version' => 'auto'));
		}

		// EDIT IN PLACE
		$urlText = JURI::base(true).'/index.php?option=com_phocacart&task=phocacarteditinplace.editinplacetext&format=json&'. JSession::getFormToken().'=1';
		HTMLHelper::_('script', 'media/com_phocacart/js/jeditable/jquery.jeditable.min.js', array('version' => 'auto'));

		$s 	= array();
		$s[] = ' ';
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   jQuery(".ph-editinplace-text").editable("'.$urlText.'", {';
		$s[] = '      tooltip : "'.JText::_('COM_PHOCACART_CLICK_TO_EDIT').'",'; //submit : \'OK\',
		$s[] = '      select : true,';
		$s[] = '      cancel : "'.JText::_('COM_PHOCACART_CANCEL').'",';
		$s[] = '      submit : "'.JText::_('COM_PHOCACART_SUBMIT').'",';
		$s[] = '      cssclass : \'ph-edit-in-place-class\',';
		$s[] = '      cancelcssclass : \'btn btn-danger\',';
		$s[] = '      submitcssclass : \'btn btn-success\',';
 		$s[] = '      intercept : function(jsondata) {';
		$s[] = '          json = JSON.parse(jsondata);';
 		$s[] = '          return json.result;';
    	$s[] = '      },';
		$s[] = '      placeholder: "",';

		// Possible information for parts on the site which will be not changed by chaning the value (for example currency view - currency rate)
		$s[] = '      callback: function() {';
		$s[] = '      	var chEIP = ".phChangeEditInPlace" + jQuery(this).attr("data-id");';
		$s[] = '      	jQuery(chEIP).html("'.JText::_('COM_PHOCACART_PLEASE_RELOAD_PAGE_TO_SEE_UPDATED_INFORMATION').'")';
		$s[] = '      },';

		$s[] = '   })';
		$s[] = '})';
		$s[] = ' ';

		$this->document->addScriptDeclaration(implode("\n", $s));
	}

	public function loadOptions($load = 0) {
		if ($load == 1) {
			HTMLHelper::_('stylesheet', 'media/com_phocacart/css/administrator/phocacartoptions.css', array('version' => 'auto'));
		}
	}
}
?>
