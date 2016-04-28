<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocaCartRenderAdminView
{
	public function __construct(){}
	
	public function startForm($option, $view, $itemId, $id = 'adminForm', $name = 'adminForm', $class = '') {
		return '<div id="'.$view.'"><form action="'.JRoute::_('index.php?option='.$option . '&layout=edit&id='.(int) $itemId).'" method="post" name="'.$name.'" id="'.$id.'" class="form-validate '.$class.'" role="form">'."\n"
		.'<div class="row-fluid">'."\n";
	}
	
	public function endForm() {
		return '</div>'."\n".'</form>'."\n".'</div>'."\n";
	}
	
	public function formInputs() {
	
		return '<input type="hidden" name="task" value="" />'. "\n"
		. JHtml::_('form.token'). "\n";
	}
	
	public function navigation($tabs) {
		$o = '<ul class="nav nav-tabs">';
		$i = 0;
		foreach($tabs as $k => $v) {
			$cA = 0;
			if ($i == 0) {
				$cA = 'class="active"';
			}
			$o .= '<li '.$cA.'><a href="#'.$k.'" data-toggle="tab">'. $v.'</a></li>'."\n";
			$i++;
		}
		$o .= '</ul>';
		return $o;
	}
	
	public function group($form, $formArray, $clear = 0) {
		$o = '';
		if (!empty($formArray)) {
			if ($clear == 1) {
				foreach ($formArray as $value) {
					$o .= '<div>'. $form->getLabel($value) . '</div>'."\n"
					. '<div class="clearfix"></div>'. "\n"
					. '<div>' . $form->getInput($value). '</div>'."\n";
				} 
			} else {
				foreach ($formArray as $value) {
					$o .= '<div class="control-group">'."\n"
					. '<div class="control-label">'. $form->getLabel($value) . '</div>'."\n"
					. '<div class="controls">' . $form->getInput($value). '</div>'."\n"
					. '</div>' . "\n";
				}
			}
		}
		return $o;
	}
	
	
	
	
	public function item($form, $item, $suffix = '', $realSuffix = 0) {
		$value = $o = '';
		if ($suffix != '') {
			if ($realSuffix) {
				$value = $form->getInput($item) .' '. $suffix;
			} else {
				$value = $suffix;
			}
		} else {
			$value = $form->getInput($item);
		}
		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $form->getLabel($item) . '</div>'."\n"
		. '<div class="controls">' . $value.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemLabel($item, $label) {
		$o = '';
		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $label . '</div>'."\n"
		. '<div class="controls">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemText($item, $label) {
		$o = '';
		$o .= '<div class="control-group ph-control-group-text">'."\n";
		$o .= '<div class="control-label">'. $label . '</div>'."\n"
		. '<div class="controls">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}
	
	public function itemCalc($id, $name, $value, $form = 'pform', $size = 1) {
	
		switch ($size){
			case 3: $class = 'input-xxlarge';
			break;
			case 2: $class = 'input-xlarge';
			break;
			case 0: $class = 'input-mini';
			break;
			default: $class= 'input-small';
			break;
		}
		$o = '';
		$o .= '<input type="text" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name).']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" class="'.htmlspecialchars($class).'" />';
		
		return $o;
	}
	
	public function itemCalcCheckbox($id, $name, $value, $form = 'pform' ) {
	                        
		$checked = '';
		if ($value == 1) {
			$checked = 'checked="checked"';
		}
		$o = '';
		$o .= '<input type="checkbox" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name).']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name).'"  '.$checked.' />';
		
		return $o;
	}
	
	/*
	* Common function for Image, Attribute, Option
	*/
	public function addRowButton($text, $type = 'image') {
	

		$o = '<div id="phrowbox'.$type.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-success btn-mini" href="#" onclick="phAddRow'.ucfirst($type).'(); return false;"><i class="icon-plus"></i> '.$text.'</a></div>';
		return $o;
	}
	
	
	
	public function additionalImagesRow($id, $url, $value = '', $js = 0) {
		
		// Will be displayed inside Javascript
		$o = '<div class="ph-row-image'.$id.' ph-row-image" id="phrowimage'.$id.'" >'
		.'<div class="ph-add-item">'
		
		.'<div class="input-append">'
		.'<input class="imageCreateThumbs" id="jform_image'.$id.'" name="pformimg['.$id.'][image]" value="'.htmlspecialchars($value).'" class="inputbox" size="40" type="text">'
		.'<a class="modal_jform_image btn" title="'.JText::_('COM_PHOCACART_FORM_SELECT_IMAGE').'" href="'.$url.$id.'"';

		if ($js == 1) {
			$o .= ' rel="{handler: \\\'iframe\\\', size: {x: 780, y: 560}}">';
		} else {
			$o .= ' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">';
		}
		
		$o .= JText::_('COM_PHOCACART_FORM_SELECT_IMAGE').'</a>'
		.'</div>'
		
		.'<input type="hidden" name="pformimg['.$id.'][imageid]" id="jform_imageid'.$id.'" value="'.$id.'" />'
		.'</div>'
		
		.'<div class="ph-remove-row"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowImage('.$id.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_IMAGE').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>';
		
		return $o;
	}
	
	public function additionalAttributesRow($id, $title, $alias, $required, $type, $js = 0) {
		
		$requiredArray	= PhocaCartAttribute::getRequiredArray();
		$typeArray		= PhocaCartAttribute::getTypeArray();
		$o				= '';
		
		// Will be displayed inside Javascript
		$o .= '<div class="ph-attribute-box" id="phAttributeBox'.$id.'">';
		
		if ($id == 0) {
			// Add Header
			$o .= '<div class="ph-row">'."\n"
			. '<div class="span2">'. JText::_('COM_PHOCACART_TITLE') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_ALIAS') . '</div>'
			. '<div class="span1">'. JText::_('COM_PHOCACART_REQUIRED') . '</div>'
			. '<div class="span2">'. JText::_('COM_PHOCACART_TYPE') . '</div>'
			. '<div class="span5">&nbsp;</div>'
			.'</div><div class="ph-cb"></div>'."\n";
		}
	
		$o .= '<div class="ph-row-attribute'.$id.' ph-row-attribute" id="phrowattribute'.$id.'" >'

		.'<div class="span2">'
		.'<input id="jform_attrtitle'.$id.'" name="pformattr['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox input-small" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2">'
		.'<input id="jform_attralias'.$id.'" name="pformattr['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox input-small" size="20" type="text">'
		.'</div>'
		
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $requiredArray, 'pformattr['.$id.'][required]', 'class="input-mini"', 'value', 'text', htmlspecialchars($required), 'jform_attrrequired'.$id)
		.'</div>'
		
		.'<div class="span2">'
		. JHtml::_('select.genericlist', $typeArray, 'pformattr['.$id.'][type]', 'class="input"', 'value', 'text', htmlspecialchars($type), 'jform_attrtype'.$id)
		.'<input type="hidden" name="pformattr['.$id.'][attrid]" id="jform_attrid'.$id.'" value="'.$id.'" />'
		.'</div>'
	
		.'<div class="span4"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowAttribute('.$id.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_ATTRIBUTE').'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		. '</div>';
		
		if ($js == 1) { 
			$o .= $this->addNewOptionButton($id, $js);
		}
		
		return $o;
	}

	/*
	 * 1 CALL IT BY JAVASCRIPT - we can add button and we can close the additionalAttributesRow box (JS -> BUTTON -> CLOSE)
     * 2 CALL IT BY PHP - we cannot add button and we cannot close the additionalAttributesRow box
	 *                    because we need to list options loaded by database, after they are loaded
	 *                    we call this function specially to add button and to close (inside javascript is it not called specially
	 *                    but by additionalAttributesRow function)
	 *                    (PHP -> OPTIONS -> BUTTON(ADDED SPECIAL) -> CLOSE (ADDED SPECIAL))
	 *                    BE AWARE js must be checked 2x - 1) it decides from where the code is loaded, 2) it changeds the output
	 */
	public function addNewOptionButton($id, $js) {
		
		$o = '';
		if ($js == 1) { 
			$id = '\' + phRowOptionAttributeId +  \'';// if no javascript, get real id, if javascript, get js variable
		}
		$o .= '<div id="phrowboxoption'.$id.'"></div>';
		$o .= '<div style="clear:both;"></div>';
		$o .= '<div class="ph-add-row"><a class="btn btn-primary btn-mini" href="#" onclick="phAddRowOption('.$id.'); return false;"><i class="icon-plus"></i> '.JText::_('COM_PHOCACART_ADD_OPTION').'</a></div>';

		$o .= '</div>';// !!! END OF additionalAttributesRow BOX
		
		return $o;
	}
	
	public function additionalOptionsRow($id, $attrId, $title, $alias, $operator, $amount, $stock, $operatorWeight, $weight, $image) {
		
		
		$operatorArray 	= PhocaCartAttribute::getOperatorArray();
		$o				= '';

		// Will be displayed inside Javascript
		$o .= '<div class="ph-option-box" id="phOptionBox'.$attrId.$id.'">';
		$o .= '<div class="ph-row-option'.$attrId.$id.' ph-row-option-attrid'.$attrId.'" id="phrowoption'.$attrId.$id.'" >'
	
		.'<div class="span2">'
		.'<input id="jform_optiontitle'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox input-small" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2">'
		.'<input id="jform_optionalias'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox input-small" size="30" type="text">'
		.'</div>'
		
		// Amount - Value
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][option]['.$id.'][operator]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operator), 'jform_optionoperator'.$attrId. $id)
		.'</div>'
		.'<div class="span1">'
		.'<input id="jform_optionamount'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][amount]" value="'.htmlspecialchars($amount).'" class="inputbox input-mini" size="30" type="text">'
		.'</div>'
		
		// Stock
		.'<div class="span1">'
		.'<input id="jform_optionstock'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][stock]" value="'.htmlspecialchars($stock).'" class="inputbox input-mini" size="30" type="text">'
		
		.'<input type="hidden" name="pformattr['.$attrId.'][option]['.$id.'][id]" id="jform_optionid'.$attrId.$id.'" value="'.$id.'" />'
		.'</div>'
		
		
		// Weight
		.'<div class="span1">'
		. JHtml::_('select.genericlist', $operatorArray, 'pformattr['.$attrId.'][option]['.$id.'][operator_weight]', 'class="input-mini"', 'value', 'text', htmlspecialchars($operatorWeight), 'jform_optionoperatorweight'.$attrId. $id)
		.'</div>'
		
		.'<div class="span1">'
		.'<input id="jform_optionweight'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][weight]" value="'.htmlspecialchars($weight).'" class="inputbox input-mini" size="40" type="text">'
		.'</div>'	
		
		// Image
		.'<div class="span2">';
		
		if (is_numeric($attrId) && is_numeric($id)) {
			JHtml::_('behavior.modal', 'a.modal_jform_optionimage'.$attrId.$id);
		} else {
			// Don't render anything for items which will be added by javascript
			// it is set in javascript addnewrow function
			// administrator\components\com_phocacart\libraries\phocacart\render\renderjs.php line cca 171
		}
		
		$group 			= PhocaCartSettings::getManagerGroup('productimage');
		$managerOutput	= '&amp;manager=productimage';
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field=jform_optionimage'.$attrId.$id;
		$attr			= '';
		
		$html[] = '<div class="input-append">';
		$html[] = '<input class="imageCreateThumbs ph-w40" type="text" id="jform_optionimage'.$attrId.$id.'" name="pformattr['.$attrId.'][option]['.$id.'][image]" value="'. htmlspecialchars($image).'"' .' '.$attr.' />';
		$html[] = '<a class="modal_jform_optionimage'.$attrId.$id.' btn" title="'.JText::_($textButton).'"'
				.' href="'.$link.'"'
				.' rel="{handler: &quot;iframe&quot;, size: {x: 780, y: 560}}">'
				. JText::_($textButton).'</a>';
		$html[] = '</div>'. "\n";
		
		$o .= implode("\n", $html);
		$o .= '</div>'
	
		//.'</div>'
		
		.'<div class="span1"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowOption('.$id.','.$attrId.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_OPTION').'</a></div>'
		.'<div class="ph-cb"></div>'
		. '</div>'
		
		.'</div>';
		
		return $o;
	}
	
	public function headerOption() {
		
		$o = '<h4>'.JText::_('COM_PHOCACART_OPTIONS').'</h4>';
		$o .= '<div class="ph-row">'."\n"
		. '<div class="span2">'. JText::_('COM_PHOCACART_TITLE') . '</div>'
		. '<div class="span2">'. JText::_('COM_PHOCACART_ALIAS') . '</div>'
		. '<div class="span1">'. JText::_('COM_PHOCACART_VALUE') . '</div>'
		. '<div class="span1">&nbsp;</div>'
		
		. '<div class="span1">'. JText::_('COM_PHOCACART_IN_STOCK') . '</div>'
		
		. '<div class="span2">'. JText::_('COM_PHOCACART_WEIGHT') . '</div>'
		. '<div class="span1">'. JText::_('COM_PHOCACART_IMAGE') . '</div>'
		. '<div class="span2">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		return $o;
	}
	
	
	public function additionalSpecificationsRow($id, $title, $alias, $value, $alias_value, $group, $js = 0) {
		
		$groupArray	= PhocaCartSpecification::getGroupArray();
		$o				= '';
		
		// Will be displayed inside Javascript
		$o .= '<div class="ph-specification-box" id="phSpecificationBox'.$id.'">';
		
	
		$o .= '<div class="ph-row-specification'.$id.' ph-row-specification" id="phrowspecification'.$id.'" >'

		.'<div class="span3">'
		.'<input id="jform_spectitle'.$id.'" name="pformspec['.$id.'][title]" value="'.htmlspecialchars($title).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span3">'
		.'<textarea id="jform_specvalue'.$id.'" name="pformspec['.$id.'][value]" class="inputbox" rows="3" cols="10" type="textarea">'.htmlspecialchars($value).'</textarea>'
		.'</div>'
		
		.'<div class="span2">'
		. JHtml::_('select.genericlist', $groupArray, 'pformspec['.$id.'][group_id]', 'class="input"', 'value', 'text', (int)$group, 'jform_specgroup'.$id)
		.'</div>'
		
	
		.'<div class="span4"><a class="btn btn-danger btn-mini" href="#" onclick="phRemoveRowSpecification('.$id.'); return false;"><i class="icon-minus"></i> '.JText::_('COM_PHOCACART_REMOVE_PARAMETER').'</a></div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		
		
		
		// ALIASES
		.'<div class="ph-row-specification">'
		
		.'<div class="span3">'
		. JText::_('COM_PHOCACART_ALIAS_PARAMETER') . '<br /><input id="jform_specalias'.$id.'" name="pformspec['.$id.'][alias]" value="'.htmlspecialchars($alias).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span3">'
		. JText::_('COM_PHOCACART_ALIAS_VALUE') . '<br /><input id="jform_specalias_value'.$id.'" name="pformspec['.$id.'][alias_value]" value="'.htmlspecialchars($alias_value).'" class="inputbox" size="40" type="text">'
		.'</div>'
		
		.'<div class="span2"> </div>'
		
		.'<div class="span4"> </div>'
		.'<div class="ph-cb ph-pad-b"></div>'
		
		.'</div>'
		
		. '</div>'
		. '</div>';
		
		
		return $o;
	}
	
	public function headerSpecification() {
		$o = '<div class="ph-row">'."\n"
		. '<div class="span3">'. JText::_('COM_PHOCACART_PARAMETER') . '</div>'
		. '<div class="span3">'. JText::_('COM_PHOCACART_VALUE') . '</div>'
		. '<div class="span2">'. JText::_('COM_PHOCACART_GROUP') . '</div>'
		. '<div class="span4">&nbsp;</div>'
		.'</div><div class="ph-cb"></div>'."\n";
		return $o;
	}
	
}
?>