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
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.form.formfield');

if (! class_exists('PhocacartRelated')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/related/related.php');
}
if (! class_exists('PhocacartProduct')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/product/product.php');
}

if (! class_exists('PhocacartCategoryMultiple')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/multiple.php');
}

class JFormFieldPhocaSelectItemCategory extends FormField
{
	public $type = 'PhocaSelectItemCategory';

	public function getInput() {


		// Runs with 'PhocaSelectItem' form field
		$html 	= array();
		//- $url 	= 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&'. JSession::getFormToken().'=1';

		// Possible problem with modal
		//$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' typeahead"' : ' class="typeahead"';
		$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' "' : ' class=""';

		$attr  .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr  .= $this->element['required'] ? ' required aria-required="true"' : '';


		$options    = array();
		$request	= $this->form->getValue('request');
		$productId	= isset($request->id) ? $request->id : 0;
		$options    = PhocacartCategoryMultiple::getCategories($productId, 2);

		$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);


		return implode("\n", $html);
	}

}
?>
