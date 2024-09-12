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

if (! class_exists('PhocacartRelated')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/related/related.php');
}
if (! class_exists('PhocacartProduct')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/product/product.php');
}

if (! class_exists('PhocacartText')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/text/text.php');
}


class JFormFieldPhocaSelectItem extends FormField
{
	public $type = 'PhocaSelectItem';

	public function getInput() {


		$html 	= array();
		$url 	= 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&'. Session::getFormToken().'=1';

		// Possible problem with modal
		//$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' typeahead"' : ' class="typeahead"';
		$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' "' : ' class=""';

		$attr  .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr  .= $this->element['required'] ? ' required aria-required="true"' : '';


		$related = $this->element['related'] ? true : false;

		//$attr  .= $this->multiple ? ' multiple' : '';

		// Be aware
		// multiple = true -> name will be $this->name = jform[related][]
		// multiple = false -> name will be $this->name = jform[related]


		if ($this->multiple) {
			$multiple = 'true';
		} else {
			$multiple = 'false';
		}

		$maxSize = isset($this->element['maxsize']) &&  (int)$this->element['maxsize'] > 0 ? (int)$this->element['maxsize'] : 0;


		$value = '';
		if ($related) {
			// Related product - select related products by "parent" product ID
			$id 	= $this->form->getValue('id');
			$value = $this->value;
		} else {
			// Standard product - only select one product by ID
			$product = PhocacartProduct::getProductByProductId((int)$this->value);
			if(isset($product->id)) {
				$value .= (int)$product->id . ':'.PhocacartText::filterValue($product->title, 'text') .' ('.PhocacartText::filterValue($product->categories_title, 'text').')';
			}
			$id = (int)$this->value;
		}

		$document = Factory::getDocument();
		HTMLHelper::_('jquery.framework', false);
        $app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseScript('com_phocacart.select2', 'media/com_phocacart/js/administrator/select2/select2.js', ['version' => 'auto']);
		$wa->registerAndUseScript('com_phocacart.phocaselect2', 'media/com_phocacart/js/phoca/jquery.phocaselect2.js', ['version' => 'auto']);
		$wa->registerAndUseStyle('com_phocacart.select2', 'media/com_phocacart/js/administrator/select2/select2.css', ['version' => 'auto']);
        Text::script('COM_PHOCACART_SKU');
        Text::script('COM_PHOCACART_EAN');

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
		$s[] = '   phSearchItemsMultiple("#'.$this->id.'", "'.$url.'", '.(int)$id.', '.$multiple.', "[|]", '.$maxSize.');';
		$s[] = '});';
    	$document->addScriptDeclaration(implode("\n", $s));
		$html[] = '<div>';
		$html[] = '<input type="text" style="width: 100%" id="'.$this->id.'" name="'.$this->name.'" value="'. $value.'"' .' '.$attr.' />';
		$html[] = '</div>'. "\n";


		return implode("\n", $html);
	}

	public function getInputWithoutFormData() {

		$this->value				= '';
		$this->id					= 'copy_attributes';
		$this->name					= 'copy_attributes';
		$this->element['related']	= false;
		$this->element['class']		= '';
		$this->element['size']		= '';
		$this->element['required']	= '';
		$this->element['onchange']	= '';
		// Select2 has problems with Bootstrap Modal, so we use multiple select instead of single select and we limit it to 1
		$this->element['maxsize']	= 1; // Multiple but only one item
		$this->multiple				= true;
		return $this->getInput();
	}
}

