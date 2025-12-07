<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldPhocacartFile extends FormField
{
	protected $type 		= 'PhocacartFile';

	protected function getInput()
	{

		$document = Factory::getDocument();

        $app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('com_phocacart.tower-file-input', $this->t['bootstrap'] . 'media/com_phocacart/js/tower/tower-file-input.min.js', array('version' => 'auto'));
        $wa->registerAndUseScript('com_phocacart.tower-file-input.js', 'media/com_phocacart/js/tower/tower-file-input.min.js', ['version' => 'auto']);

		//$document->addScript(Uri::root(true) . '/media/com_phocacart/js/tower/tower-file-input.min.js');
		//HTMLHelper::stylesheet('media/com_phocacart/js/tower/tower-file-input.min.css');

		$pC = PhocacartUtils::getComponentParameters();
		$s = PhocacartRenderStyle::getStyles();
		$submit_item_upload_image_maxsize = $pC->get('submit_item_upload_image_maxsize', 512000);
		$submit_item_upload_image_count = $pC->get('submit_item_upload_image_count', 1);

		$option = $app->getInput()->get('option');
		$view 	= $app->getInput()->get('view');

		$admin = 0;
		if (!$app->isClient('site') && $option == 'com_phocacart' && $view == 'phocacartsubmititem') {
			$admin = 1;
		}

		if ($admin == 1) {

			$o = array();


			if (!empty($this->value)) {

				foreach($this->value as $k => $v) {






					if (isset($v['name'])) {
						$o[] = '<div class="'.$s['c']['control-group'].'">';
						$o[] = '<div class="'.$s['c']['control-label'].'">'.$this->form->getLabel($this->name).'</div>';
						$o[] = '<div class="'.$s['c']['controls'].'">';
						$o[] = '<input type="text" name="jform[image]['.$k.'][name]" id="jform_image" value="'.$v['name'].'" class="form-control" size="40">';
						$o[] = '</div>';
						$o[] = '</div>';
					}
					if (isset($v['size'])) {
						$o[] = '<div class="'.$s['c']['control-group'].'">';
						$o[] = '<div class="'.$s['c']['control-label'].'">'.$this->form->getLabel($this->name).'</div>';
						$o[] = '<div class="'.$s['c']['controls'].'">';
						$o[] = '<input type="text" name="jform[image]['.$k.'][size]" id="jform_image" value="'.$v['size'].'" class="form-control" size="40">';
						$o[] = '</div>';
						$o[] = '</div>';
					}
					if (isset($v['nametoken'])) {
						$o[] = '<div class="'.$s['c']['control-group'].'">';
						$o[] = '<div class="'.$s['c']['control-label'].'">'.$this->form->getLabel($this->name).'</div>';
						$o[] = '<div class="'.$s['c']['controls'].'">';
						$o[] = '<input type="text" name="jform[image]['.$k.'][nametoken]" id="jform_image" value="'.$v['nametoken'].'" class="form-control" size="40">';
						$o[] = '</div>';
						$o[] = '</div>';
					}
					if (isset($v['fullpath'])) {
						$o[] = '<div class="'.$s['c']['control-group'].'">';
						$o[] = '<div class="'.$s['c']['control-label'].'">'.$this->form->getLabel($this->name).'</div>';
						$o[] = '<div class="'.$s['c']['controls'].'">';
						$o[] = '<input type="text" name="jform[image]['.$k.'][fullpath]" id="jform_image" value="'.$v['fullpath'].'" class="form-control" size="40">';
						$o[] = '</div>';
						$o[] = '</div>';
					}





				}

			}


		} else {
			$requInput	= $this->required ? ' required aria-required="true"' : '';


			$typeMethod = $this->element['typemethod'];

			$accepts = '';
			if ($typeMethod == 'image'){
				$accepts = 'accept="image/*"';
				$accepts = 'accept="image/x-png,image/gif,image/jpeg,image/webp,image/avif"';
			}

			$s  = PhocacartRenderStyle::getStyles();
			$id = PhocacartUtils::getRandomString(12);
			$id = 'phFile'. $id;

			$o = array();
			$o[] = '<div class="tower-file">';
			$o[] = '<input type="file" id="'.$id.'" name="'.$this->name.'[]" multiple '.$accepts.' '. $requInput.' />';
			$o[] = '<label for="'.$id.'" class="'.$s['c']['btn.btn-primary'].'">'.PhocacartRenderIcon::icon($s['i']['upload'], '', ' ') .Text::_('COM_PHOCACART_SELECT_FILES').'</label>';
			$o[] = '<button type="button" class="tower-file-clear '.$s['c']['btn.btn-secondary'].' align-top">';
			//$o[] = '<span class="'.$s['i']['clear'].'"></span> ';
			$o[] = PhocacartRenderIcon::icon($s['i']['clear'], '', ' ');
			$o[] = Text::_('COM_PHOCACART_CLEAR').'</button>';
			$o[] = '</div>';

			$o[] = '<script type="text/javascript">';
			$o[] = 'jQuery("#'.$id.'").phFileInput({';
			$o[] = '   fileCount: '.(int)$submit_item_upload_image_count.',';
			$o[] = '   fileSizeLimit: '.(int)$submit_item_upload_image_maxsize.',';
			$o[] = '   iconClass: "'.addslashes(PhocacartRenderIcon::icon($s['i']['upload'])).'",';
			$o[] = '   lang: {';
			$o[] = '      "COM_PHOCACART_ERROR_TOO_MANY_FILES_SELECTED": "'.Text::_('COM_PHOCACART_ERROR_TOO_MANY_FILES_SELECTED'). '",';
			$o[] = '      "COM_PHOCACART_MAXIMUM_NUMBER_FILES_SELECTED_IS": "'.Text::_('COM_PHOCACART_MAXIMUM_NUMBER_FILES_SELECTED_IS'). '",';
			$o[] = '      "COM_PHOCACART_WARNFILETOOLARGE": "'.Text::_('COM_PHOCACART_WARNFILETOOLARGE'). '",';
			$o[] = '      "COM_PHOCACART_FILE_SIZE": "'.Text::_('COM_PHOCACART_FILE_SIZE'). '",';
			$o[] = '      "COM_PHOCACART_FILE_SIZE_LIMIT": "'.Text::_('COM_PHOCACART_FILE_SIZE_LIMIT'). '",';
			$o[] = '      "COM_PHOCACART_ERROR": "'.Text::_('COM_PHOCACART_ERROR'). '",';
			$o[] = '      "COM_PHOCACART_FILES_SELECTED": "'.Text::_('COM_PHOCACART_FILES_SELECTED'). '"';
			$o[] = '   }';
			$o[] = '});';
			$o[] = '</script>';

		}




        return implode("\n", $o);
	}
}
