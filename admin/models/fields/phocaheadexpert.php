<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldPhocaHeadExpert extends FormField
{
	protected $type = 'PhocaHeadExpert';
	protected function getLabel() { return '';}

	protected function getInput() {

		$tc = 'phocacart';
		$ts = 'media/com_'.$tc.'/css/administrator/';
		$ti = 'media/com_'.$tc.'/images/administrator/';
		$app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('com_phocacart.phocacartoptions', $ts.'/'.$tc.'options.css', array('version' => 'auto'));
		//HTMLHelper::stylesheet( $ts.'/'.$tc.'options.css' );
		echo '<div style="clear:both;"></div>';
		$phocaImage	= ( (string)$this->element['phocaimage'] ? $this->element['phocaimage'] : '' );
		$image 		= '';

		if ($phocaImage != ''){
			$image 	= HTMLHelper::_('image', $ti . $phocaImage, '' );
		}

		if ($this->element['default']) {
			if ($image != '') {
				return '<div class="tab-header ph-options-head-expert">'
				.'<div>'. $image.' <strong>'. Text::_($this->element['default']) . '</strong></div>'
				.'</div>';
			} else {
				return '<div class="tab-header ph-options-head-expert">'
				.'<strong>'. Text::_($this->element['default']) . '</strong>'
				.'</div>';
			}
		} else {
			return parent::getLabel();
		}
		echo '<div style="clear:both;"></div>';
	}
}
?>
