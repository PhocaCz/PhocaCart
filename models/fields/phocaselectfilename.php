<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');

class JFormFieldPhocaSelectFilename extends JFormField
{
	public $type = 'PhocaSelectFileName';

	protected function getInput() {
		$html 			= array();
		$managerOutput	= $this->element['manager'] ? '&amp;manager='.(string)$this->element['manager'] : '';
		$group 			= PhocacartUtilsSettings::getManagerGroup((string) $this->element['manager']);
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id;

		$onchange 		= (string) $this->element['onchange'];
		$size     		= ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    		= ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="text_area"';
		$required 		= ($v = $this->element['required']) ? ' required="required"' : '';
		
		if ((string)$this->element['manager'] == 'publicfile') {
			$idA			= 'phFileNameModalPublic';
		} else {
			$idA			= 'phFileNameModal';
		}
		$w				= 700;
		$h				= 400;
		
		
		JHtml::_('jquery.framework');
		
		if ((string)$this->element['manager'] == 'productfile') {
			
			// This function selects the right folder when clicking on selecting e.g. download file
			// Set by the folder stored in download tab in product edit
			$s 	= array();
			//$s[] = 'var phDownloadFolderModal'.$this->id.' = \'\';';
			$s[] = 'jQuery(document).ready(function() {';
			// Mootools
			//$s[] = '   var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
			//$s[] = '   phDownloadFolderModal'.$this->id.' = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
			//$s[] 	= '   stringToSend = "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
			//$s[] 	= '   alert(stringToSend);var newUri = jQuery(\'.modal_jform_download_file\').attr(\'href\') + stringToSend;';
			//$s[] 	= '   jQuery(\'.modal_jform_download_file\').attr("href", newUri);';

			$s[] = '   jQuery(\'a.'.$idA.'ModalButton\').on(\'click\', function(e) {';
			$s[] = '      var src = jQuery(this).attr(\'data-src\');';
			$s[] = '      var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
			$s[] = '   	  src = src + "&folder=" + phDownloadFolder + "&downloadfolder=" + phDownloadFolder;';
			$s[] = '      var height = jQuery(this).attr(\'data-height\') || '.$w.';';
			$s[] = '      var width = jQuery(this).attr(\'data-width\') || '.$h.';';
			$s[] = '      jQuery("#'.$idA.' iframe").attr({\'src\':src, \'height\': height, \'width\': width});';
			$s[] = '   });';
			
			$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	
			
			$s = array();
			$s[] = '	function phocaSelectFileName_'.$this->id.'(title) {';
			$s[] = '		document.getElementById("'.$this->id.'").value = title;';
			$s[] = '		'.$onchange;
			$s[] 	= '   jQuery(\'.modal\').modal(\'hide\');';
			//$s[] = '		SqueezeBox.close();';
			$s[] = '	}';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		
		} else if ((string)$this->element['manager'] == 'publicfile') {
			
	
			$s 	= array();
			$s[] = 'jQuery(document).ready(function() {';

			$s[] = '   jQuery(\'a.'.$idA.'ModalButton\').on(\'click\', function(e) {';
			$s[] = '      var src = jQuery(this).attr(\'data-src\');';
			//$s[] = '      var phDownloadFolder = jQuery(\'#jform_download_folder\').val();';
			$s[] = '   	  src = src + "&folder=&downloadfolder=";';
			$s[] = '      var height = jQuery(this).attr(\'data-height\') || '.$w.';';
			$s[] = '      var width = jQuery(this).attr(\'data-width\') || '.$h.';';
			$s[] = '      height = height + \'px\';';
			$s[] = '      width = width + \'px\';';
			$s[] = '      jQuery("#'.$idA.' iframe").attr({\'src\':src, \'height\': height, \'width\': width});';
			$s[] = '   });';
			
			$s[] = '})';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
	
			
			$s = array();
			$s[] = '	function phocaSelectFileName_'.$this->id.'(title) {';
			$s[] = '		document.getElementById("'.$this->id.'").value = title;';
			$s[] = '		'.$onchange;
			$s[] 	= '   jQuery(\'.modal\').modal(\'hide\');';
			//$s[] = '		SqueezeBox.close();';
			$s[] = '	}';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}
		
		
		
		$html[] = '<span class="input-append"><input type="text" ' . $required . ' id="' . $this->id . '" name="' . $this->name . '"'
			. ' value="' . $this->value . '"' . $size . $class . ' />';
		$html[] = ' <a href="#'.$idA.'" role="button" class="btn btn-primary '.$idA.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$link.'" data-height='.$w.' data-width='.$h.'">'
			. '<span class="icon-list icon-white"></span> '
			. JText::_($textButton) . '</a></span>';
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			$idA,
			array(
				//'url'    => $link . '\' + (phDownloadFolderModal'.$this->id.') + \'',
				'title'  => JText::_($textButton),
				'width'  => $w.'px',
				'height' => $h.'px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_('COM_PHOCACART_CLOSE') . '</button>'
			),
			'<iframe frameborder="0"></iframe>'
		);

		return implode("\n", $html);
		
		
		/*JHtml::_('behavior.modal', 'a.modal_'.$this->id);
		
		if ($this->element['manager'] == 'productfile') {
			PhocacartRenderJs::renderJsAppendValueToUrl(/* TO DO*//*);
			$s = array();
			$s[] = '	function phocaSelectFileName_'.$this->id.'(title) {';
			$s[] = '		document.getElementById("'.$this->id.'").value = title;';
			$s[] = '		'.$onchange;
			$s[] = '		SqueezeBox.close();';
			$s[] = '	}';
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		}

		$html[] = '<div class="input-append">';
		$html[] = '<input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'. $this->value.'"' .' '.$attr.' />';
		$html[] = '<a class="modal_'.$this->id.' btn" title="'.JText::_($textButton).'"'
				.' href="'.($this->element['readonly'] ? '' : $link).'"'
				.' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">'
				. JText::_($textButton).'</a>';
		$html[] = '</div>'. "\n";
		

		return implode("\n", $html);*/
	}
}
?>