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

if (! class_exists('PhocacartRelated')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/related/related.php');
}
if (! class_exists('PhocacartProduct')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/product/product.php');
}

if (! class_exists('PhocacartText')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/text/text.php');
}


class JFormFieldPhocaSelectItem extends JFormField
{
	public $type = 'PhocaSelectItem';

	public function getInput() {
		$html 	= array();
		$url 	= 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&'. JSession::getFormToken().'=1';

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


		$onchange 	= (string) $this->element['onchange'];
		$value = '';
		if ($related) {
			// Related product - select related products by "parent" product ID
			$id 	= $this->form->getValue('id');


			if ((int)$id > 0) {
				$relatedOption	= PhocacartRelated::getRelatedItemsById((int)$id);

				if(!empty($relatedOption)) {
					$i = 0;
					//$value .= '[';
					foreach($relatedOption as $k => $v) {
						if ($i > 0) {
							$value .= '[|]';
						}

						$title = PhocacartText::filterValue($v->title, 'text');
						$titleCat = PhocacartText::filterValue($v->categories_title, 'text');
						//$title = str_replace(',', '-', $title);
						//$titleCat = str_replace(',', '-', $titleCat);

						$value .= (int)$v->id . ':'.$title.' ('.$titleCat.')';
						$i++;
					}
					//$value .= ']';
				}
			}
		} else {
			// Standard product - only select one product by ID
			$product = PhocacartProduct::getProductByProductId((int)$this->value);

			if(isset($product->id)) {
				$value .= (int)$product->id . ':'.PhocacartText::filterValue($product->title, 'text') .' ('.PhocacartText::filterValue($product->categories_title, 'text').')';
			}
			$id = (int)$this->value;

		}




		$document = JFactory::getDocument();
		JHtml::stylesheet('media/com_phocacart/js/administrator/select2/select2.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/administrator/select2/select2.js');
		Joomla\CMS\HTML\HTMLHelper::_('jquery.framework', false);
$s = array();
$s[] = 'jQuery(document).ready(function() {';
$s[] = ' ';
$s[] = '(function (jQuery) {';
$s[] = '  "use strict";';
$s[] = '  jQuery.extend(jQuery.fn.select2.defaults, {';
$s[] = '   formatNoMatches: function () { return "'.JText::_('COM_PHOCACART_NO_MATCHES_FOUND').'"; },';
$s[] = '   formatInputTooShort: function (input, min) { var n = min - input.length; return "'.JText::_('COM_PHOCACART_PLEASE_ENTER').' " + n + " '.JText::_('COM_PHOCACART_S_MORE_CHARACTER').'" + (n == 1? "" : "s"); },';
$s[] = '   formatInputTooLong: function (input, max) { var n = input.length - max; return "'.JText::_('COM_PHOCACART_PLEASE_DELETE').' " + n + " '.JText::_('COM_PHOCACART_S_CHARACTER').'" + (n == 1? "" : "s"); },';
$s[] = '   formatSelectionTooBig: function (limit) { return "'.JText::_('COM_PHOCACART_YOU_CAN_ONLY_SELECT').' " + limit + " '.JText::_('COM_PHOCACART_S_ITEM').'" + (limit == 1 ? "" : "s"); },';
$s[] = '   formatLoadMore: function (pageNumber) { return "'.JText::_('COM_PHOCACART_LOADING_MORE_RESULTS').'..."; },';
$s[] = '   formatSearching: function () { return "'.JText::_('COM_PHOCACART_SEARCHING').'..."; }';
$s[] = '  });';
$s[] = '})(jQuery);';
$s[] = ' ';
$s[] = ' function phSearchItemsMultiple(element, url) {';
$s[] = '   jQuery(element).select2({';
//$s[] = '   dropdownAutoWidth : true,';
//$s[] = '   width: "auto",';
$s[] = '   placeholder: "",';
$s[] = '   minimumInputLength: 1,';
$s[] = '   multiple: '.$multiple.',';
$s[] = '   ajax: {';
$s[] = '    url: url,';
$s[] = '    dataType: \'json\',';
$s[] = '    data: function(term, page) {';
$s[] = '     return {';
$s[] = '      q: term,';
$s[] = '      page_limit: 10,';
$s[] = '      item_id: '.(int)$id.',';
$s[] = '     }';
$s[] = '    },';
$s[] = '    results: function(data, page) {';
$s[] = '	   if ( data.status == 0 ){';
$s[] = '       return { results: data.error }';
$s[] = '      } else {';
$s[] = '      return { results: data.items }';
$s[] = '      }';
$s[] = '    }';
$s[] = '   },';
$s[] = '   formatResult: formatResult,';
$s[] = '   formatSelection: formatSelection,';
$s[] = '   initSelection: function(element, callback) {';
$s[] = '    var data = [];';
$s[] = '    jQuery(element.val().split("[|]")).each(function(i) {';
$s[] = '     var item = this.split(\':\');';
$s[] = '      data.push({';
$s[] = '       id: item[0],';
$s[] = '       title: item[1]';
$s[] = '      });';
$s[] = '    });';

if ($multiple == 'false') {
	$s[] = '    callback(data[0]);';// NOT MULTIPLE
} else {
	$s[] = '    jQuery(element).val(\'\');';// Cannot be set when single product because the input will be empty at start (now it is including string but when saving, string will be changed to int)
	$s[] = '    callback(data);';// MULTIPLE
}
$s[] = '   }';
$s[] = '  });';
$s[] = ' };';
$s[] = ' ';
$s[] = ' function formatResult(item) {';
$s[] = '  if (item.image !== undefined) {';
$s[] = '   return \'<div><img src="'.JURI::root().'\' + item.image + \'" /> \' + item.title + \'</div>\';';
$s[] = '  } else {';
$s[] = '  	return \'<div>\' + item.title + \'</div>\';';
$s[] = '  }';
$s[] = ' };';
$s[] = ' ';
$s[] = ' function formatSelection(data) {';

// Menu link - we need to select category in menu link too
// Options of categories will be loaded by ajax
$s[] = '    if(data.categories && jQuery("#jform_request_catid").length) {';
$s[] = '       jQuery("#jform_request_catid option").remove();';
$s[] = '       jQuery(data.categories.split(",")).each(function(i) {';
$s[] = '          var itemC = this.split(\':\');';
$s[] = '          jQuery("#jform_request_catid").append(jQuery(\'<option>\', {value: itemC[0], text: itemC[1]}));';
$s[] = '       });';
$s[] = '	   jQuery("select").trigger("liszt:updated");';
$s[] = '	   jQuery("select").trigger("chosen:updated");';
//$s[] = '	   jQuery(".inputbox").chosen({disable_search_threshold : 10,allow_single_deselect : true});';
$s[] = '    }';
// End Menu link
$s[] = '    return data.title;';
$s[] = ' };';
$s[] = ' ';
$s[] = ' phSearchItemsMultiple("#'.$this->id.'", "'.$url.'");';
//$s[] = ' ';
//$s[] = ' jQuery(\'#save\').click(function() {';
//$s[] = '  console log(jQuery(\'#jform_related\').val());';
//$s[] = ' });';
$s[] = '});';



    $document->addScriptDeclaration(implode("\n", $s));


		$html[] = '<div>';
		$html[] = '<input type="hidden" style="width: 400px;" id="'.$this->id.'" name="'.$this->name.'" value="'. $value.'"' .' '.$attr.' />';
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
		return $this->getInput();
	}
}
?>
