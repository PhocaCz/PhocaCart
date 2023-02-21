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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldPhocaFormCountry extends FormField
{
	protected $type 		= 'PhocaFormCountry';

	protected function getInput() {

		$app	= Factory::getApplication();
		$db	 	= Factory::getDBO();

		if ($this->id == 'jform_country') {
			$regionId = 'jform_region';
		} else if ($this->id == 'jform_country_phs') {
			$regionId = 'jform_region_phs';
		} else if ($this->id == 'jform_country_phb') {
			$regionId = 'jform_region_phb';
		}

		$s 	= array();
		$s[] 	= 'function phUpdateRegion'.$this->id.'(value) {';


		$config 	= ComponentHelper::getParams('com_media');
		//$paramsC 	= PhocacartUtils::getComponentParameters() ;




		if (!$app->isClient('administrator')) {
			$paramsC 	= $app->getParams();
			$load_chosen= $paramsC->get( 'load_chosen', 0 );
			$s[] 	= '   var url = \''.Uri::base(true).'/index.php?option=com_phocacart&task=checkout.setregion&format=json&'. Session::getFormToken().'=1\';';
		} else {
			$paramsC 	= PhocacartUtils::getComponentParameters();
			$load_chosen= $paramsC->get( 'load_chosen', 0 );
			$s[] 	= '   var url = \''.Uri::base(true).'/index.php?option=com_phocacart&task=phocacartuser.setregion&format=json&'. Session::getFormToken().'=1\';';
		}

		$s[] 	= '   var dataPost = {};';
		$s[] 	= '   dataPost[\'countryid\'] = encodeURIComponent(value);';
		$s[] 	= '   let phRequestActive = jQuery.ajax({';
		$s[] 	= '      url: url,';
		$s[] 	= '      type:\'POST\',';
		$s[] 	= '      data:dataPost,';
		$s[] 	= '      dataType:\'JSON\',';
		$s[] 	= '      success:function(data){';
		$s[] 	= '         if ( data.status == 1 ){';
		$s[] 	= '            jQuery(\'#'.$regionId.'\').empty().append(data.content);';
		if (!$app->isClient('administrator')) {
			if ($load_chosen > 0) {
				$s[] 	= '	           jQuery(\'#'.$regionId.'\').trigger("chosen:updated");';//Reload Chosen
			}
		} else {
			// in admin, older version of chosen is used
			$s[] 	= '	           jQuery(\'#'.$regionId.'\').trigger("liszt:updated");';//Reload Chosen older version
		}
		$s[] 	= '         } else {';
		$s[]	= '			   jQuery("#ph-request-message").show();';
		$s[] 	= '	           jQuery(\'#ph-request-message\').html(data.error)';
		$s[] 	= '         }';
		$s[] 	= '      }';
		$s[] 	= '   });';



		$s[] 	= '}';
		Factory::getDocument()->addScriptDeclaration(implode("\n", $s));




		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_countries AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();


		$attr = '';
		$attr .= !empty($this->class) ? ' class="' . $this->class . ' form-select chosen-select ph-input-select-countries"' : 'class="form-select chosen-select ph-input-select-countries"';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// Set default value in case, there is no value
		if (($this->value == 0 || $this->value == '') && isset($this->default) && (int)$this->default > 0) {
			$this->value = $this->default;
			$this->form->setValue(str_replace('jform_', '', $this->id), null, $this->default);

		}

		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->onchange ? ' onchange="phUpdateRegion'.$this->id.'(this.value);' . $this->onchange . '" ' : ' onchange="phUpdateRegion'.$this->id.'(this.value);" ';

		array_unshift($data, HTMLHelper::_('select.option', '', '-&nbsp;'.Text::_('COM_PHOCACART_SELECT_COUNTRY').'&nbsp;-', 'value', 'text'));

		return HTMLHelper::_('select.genericlist',  $data,  $this->name, trim($attr), 'value', 'text', $this->value, $this->id );
	}
}
?>
