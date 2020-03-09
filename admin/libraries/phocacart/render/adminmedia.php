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

class PhocacartRenderAdminmedia
{
	public $jquery			= 0;
	protected $document		= false;

	public function __construct() {

		$this->document	= JFactory::getDocument();
		JHtml::_('behavior.tooltip');
		JHtml::_('jquery.framework', false);

        $this->document->addScript(JURI::root(true).'/media/com_phocacart/js/administrator/phocacart.js');



        // FORM
		$app = JFactory::getApplication();
		$this->document->addScriptOptions('phLang', array('COM_PHOCACART_CLOSE' => JText::_('COM_PHOCACART_CLOSE'), 'COM_PHOCACART_ERROR_TITLE_NOT_SET' => JText::_('COM_PHOCACART_ERROR_TITLE_NOT_SET')));
		$this->document->addScriptOptions('phVars', array('token' => JSession::getFormToken()));
		//$this->document->getDocument()->addScriptOptions('phParams', array());
        $this->document->addScript(JURI::root(true).'/media/com_phocacart/js/administrator/phocacartform.js');

		//JHtml::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.glyphicons.min.css' );
		JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bootstrap-grid.min.css' );
		JHtml::stylesheet( 'media/com_phocacart/css/administrator/phocacart.css' );
		JHtml::stylesheet( 'media/com_phocacart/css/administrator/phocacarttheme.css' );
		JHtml::stylesheet( 'media/com_phocacart/css/administrator/phocacartcustom.css' );
		JHtml::stylesheet( 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons-icons-only.min.css' );
		
		JHtml::stylesheet( 'media/com_phocacart/duoton/joomla-fonts.css' );

		if(PhocacartUtils::isJCompatible('3.7')) {
			JHtml::stylesheet( 'media/com_phocacart/css/administrator/37.css' );
		}

		$lang = JFactory::getLanguage();
		if ($lang->isRtl()){
			JHtml::stylesheet( 'media/com_phocacart/css/administrator/rtl.css' );
		}

		// EDIT IN PLACE
		$urlText = JURI::base(true).'/index.php?option=com_phocacart&task=phocacarteditinplace.editinplacetext&format=json&'. JSession::getFormToken().'=1';
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jeditable/jquery.jeditable.min.js');

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
			JHtml::stylesheet('media/com_phocacart/css/administrator/phocacartoptions.css' );
		}
	}
}
?>
