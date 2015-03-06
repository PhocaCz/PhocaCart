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

class JFormFieldPhocaCouponProduct extends JFormField
{
	public $type = 'PhocaCouponProduct';

	protected function getInput() {
		$html 	= array();
		$url 	= 'index.php?option=com_phocacart&view=phocacartitema&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		$attr 	= $this->element['class'] ? ' class="'.(string) $this->element['class'].' typeahead"' : ' class="typeahead"';
		$attr 	.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$onchange 	= (string) $this->element['onchange'];
		
		$id 	= $this->form->getValue('id');
		
		$value = '';
		if ((int)$id > 0) {
			$relatedOption	= PhocaCartCoupon::getCouponProductsById((int)$id);
			if(!empty($relatedOption)) {
				$i = 0;
				foreach($relatedOption as $k => $v) {
					if ($i > 0) {
						$value .= ',';
					}
					$value .= (int)$v->id . ':'. $v->title .' ('.$v->category_title.')';
					$i++;
				}
			}
		}
		
		$document = JFactory::getDocument();
		JHtml::stylesheet('media/com_phocacart/js/administrator/select2/select2.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/administrator/select2/select2.js');
		JHtml::_('jquery.framework', false);
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
$s[] = '  jQuery(element).select2({';
$s[] = '   placeholder: "",';
$s[] = '   minimumInputLength: 1,';
$s[] = '   multiple: true,';
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
$s[] = '	  if ( data.status == 0 ){';
$s[] = '       return { results: data.error }';
$s[] = '      } else {';
$s[] = '       return { results: data.items }';
$s[] = '      }';
$s[] = '    }';
$s[] = '   },';
$s[] = '   formatResult: formatResult,';
$s[] = '   formatSelection: formatSelection,';
$s[] = '   initSelection: function(element, callback) {';
$s[] = '    var data = [];';
$s[] = '    jQuery(element.val().split(",")).each(function(i) {';
$s[] = '     var item = this.split(\':\');';
$s[] = '      data.push({';
$s[] = '       id: item[0],';
$s[] = '       title: item[1]';
$s[] = '      });';
$s[] = '    });';
$s[] = '    jQuery(element).val(\'\');';
$s[] = '    callback(data);';
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
$s[] = '  return data.title;';
$s[] = ' };';
$s[] = ' ';
$s[] = ' phSearchItemsMultiple("#'.$this->id.'", "'.$url.'");';
//$s[] = ' ';
//$s[] = ' jQuery(\'#save\').click(function() {';
//$s[] = '  alert(jQuery(\'#jform_related\').val());';
//$s[] = ' });';
$s[] = '});';

    $document->addScriptDeclaration(implode("\n", $s));

		$html[] = '<div>';
		$html[] = '<input type="hidden" style="width: 400px;" id="'.$this->id.'" name="'.$this->name.'" value="'. $value.'"' .' '.$attr.' />';
		$html[] = '</div>'. "\n";
		return implode("\n", $html);
	}
}
?>