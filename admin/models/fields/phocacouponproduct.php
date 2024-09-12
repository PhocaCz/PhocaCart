<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
jimport('joomla.form.formfield');

class JFormFieldPhocaCouponProduct extends FormField
{
	public $type = 'PhocaCouponProduct';

	protected function getInput() {
		$html 	= array();
		$url 	= 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&'. Session::getFormToken().'=1';
		$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' typeahead"' : ' class="typeahead"';
		$attr 	.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$onchange 	= (string) $this->element['onchange'];

		$id 	= $this->form->getValue('id');

		$value = '';
		if ((int)$id > 0) {
			$relatedOption	= PhocacartCoupon::getCouponProductsById((int)$id);
			if(!empty($relatedOption)) {
				$i = 0;
				foreach($relatedOption as $k => $v) {
					if ($i > 0) {
						$value .= ',';
					}
					$value .= (int)$v->id . ':'. $v->title .' ('.$v->categories_title.')';
					$i++;
				}
			}
		}

		$document = Factory::getDocument();
		HTMLHelper::_('jquery.framework', false);
		$app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseScript('com_phocacart.select2', 'media/com_phocacart/js/administrator/select2/select2.js', ['version' => 'auto']);
		$wa->registerAndUseScript('com_phocacart.phocaselect2', 'media/com_phocacart/js/phoca/jquery.phocaselect2.js', ['version' => 'auto']);
		$wa->registerAndUseStyle('com_phocacart.select2', 'media/com_phocacart/js/administrator/select2/select2.css', ['version' => 'auto']);
		///HTMLHelper::_('script', 'media/com_phocacart/js/administrator/select2/select2.js', array('version' => 'auto'));
		///HTMLHelper::_('script', 'media/com_phocacart/js/phoca/jquery.phocaselect2.js', array('version' => 'auto'));
		///HTMLHelper::_('stylesheet', 'media/com_phocacart/js/administrator/select2/select2.css', array('version' => 'auto'));

		$document->addScriptOptions('phLang', array(
			'COM_PHOCACART_NO_MATCHES_FOUND' => Text::_('COM_PHOCACART_NO_MATCHES_FOUND'),
			'COM_PHOCACART_PLEASE_ENTER' => Text::_('COM_PHOCACART_PLEASE_ENTER'),
			'COM_PHOCACART_S_MORE_CHARACTER' => Text::_('COM_PHOCACART_S_MORE_CHARACTER'),
			'COM_PHOCACART_PLEASE_DELETE' => Text::_('COM_PHOCACART_PLEASE_DELETE'),
			'COM_PHOCACART_S_CHARACTER' => Text::_('COM_PHOCACART_S_CHARACTER'),
			'COM_PHOCACART_YOU_CAN_ONLY_SELECT' => Text::_('COM_PHOCACART_YOU_CAN_ONLY_SELECT'),
			'COM_PHOCACART_S_ITEM' => Text::_('COM_PHOCACART_S_ITEM'),
			'COM_PHOCACART_LOADING_MORE_RESULTS' => Text::_('COM_PHOCACART_LOADING_MORE_RESULTS'),
			'COM_PHOCACART_SEARCHING' => Text::_('COM_PHOCACART_SEARCHING')
		));
		$document->addScriptOptions('phVars', array('uriRoot' => Uri::root()));

		$s = array();
		$s[] = 'jQuery(document).ready(function() {';
		$s[] = '   phSearchItemsMultiple("#'.$this->id.'", "'.$url.'", '.(int)$id.', true, ",");';
		$s[] = '});';
    	$document->addScriptDeclaration(implode("\n", $s));

		$html[] = '<div>';
		$html[] = '<input type="hidden" style="width: 100%" id="'.$this->id.'" name="'.$this->name.'" value="'. $value.'"' .' '.$attr.' />';
		$html[] = '</div>'. "\n";
		return implode("\n", $html);
	}
}
?>
