<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartFilter
{
	public $taglist			= false;
	public $manufacturer	= false;
	public $price			= false;
	public $attributes		= false;
	
	
	public function __construct() {}
	
	public function renderList() {
		
		// $db = JFactory::getDBO();
		$o			= array();
		//$app		= JFactory::getApplication();
		$layout 	= new JLayoutFile('form_filter_checkbox', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		$layout2 	= new JLayoutFile('form_filter_text', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		$layout3 	= new JLayoutFile('form_filter_checkbox_categories', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		
		// =FILTER=
		$data['getparams']	= array();
		
		//-CATEGORY- ACTIVE CATEGORY
		$data['param'] 		= 'id';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		$category			= PhocaCartRoute::getIdForItemsRoute();
		
		$data['getparams'][]= $category['idalias'];
		$data['nrinalias']	= 1;
		$data['formtype']	= 'category';
		$data['uniquevalue']= 1;
		

		if ((int)$this->category == 1 && (int)$category['id'] > 0) {
			phocacartimport('phocacart.category.category');
			$data['items'][] = PhocaCartCategory::getCategoryById($category['id']);
		
			if (!empty($data['items'])) {
				$o[] = $layout->render($data);
			}
		}
	
		

		// -CATEGORY MORE- (ALL CATEGORIES)
		$data['param'] 		= 'c';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		
		if ((int)$this->category == 2) {
			$data['items'] 	= PhocaCartCategory::getCategoryTreeArray();
			$data['output']	= PhocaCartCategory::nestedToCheckBox($data['items'], $data);
			
			if (!empty($data['items'])) {
				$o[] = $layout3->render($data);
			}
		}
	
		
		
		// -PRICE-
		$data['param'] 		= 'price_from';
		$data['param2'] 	= 'price_to';
		$data['id'] 		= 'phPriceFromTo';
		$data['title']		= JText::_('COM_PHOCACART_PRICE');
		$data['title1']		= JText::_('COM_PHOCACART_PRICE_FROM');
		$data['title2']		= JText::_('COM_PHOCACART_PRICE_TO');
		$data['titleset']	= JText::_('COM_PHOCACART_SET_PRICE');
		$data['titleclear']	= JText::_('COM_PHOCACART_CLEAR_PRICE');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');// string because of setting '' when no value set
		$data['getparams2']	= $this->getArrayParamValues($data['param2'], 'string');
		$data['formtype']	= 'text';
		$data['uniquevalue']= 1;
		
		if ($this->price) {
			$o[] = $layout2->render($data);
		}
		
		// -TAG-
		$data['param'] 		= 'tag';
		$data['title']		= JText::_('COM_PHOCACART_TAGS');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		
		if ($this->tag) {
			phocacartimport('phocacart.tag.tag');
			$data['items'] = PhocaCartTag::getAllTags();
		}
		
		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}
		
		// -MANUFACTURER-
		$data['param'] 		= 'manufacturer';
		$data['title']		= JText::_('COM_PHOCACART_MANUFACTURERS');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		
		if ($this->manufacturer) {
			phocacartimport('phocacart.manufacturer.manufacturer');
			$data['items'] = PhocaCartManufacturer::getAllManufacturers();
		}
		
		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}
		
		// -ATTRIBUTES-
		if ($this->attributes) {
			phocacartimport('phocacart.attribute.attribute');
			$attributes = PhocaCartAttribute::getAllAttributesAndOptions();
			if (!empty($attributes)) {
				foreach($attributes as $k => $v) {
					$data				= array();
					$data['param'] 		= 'a['.$v['alias']. ']';
					$data['title']		= $v['title'];
					$data['items']		= $v['option'];
					$data['getparams']	= $this->getArrayParamValues($data['param'], 'array');
					$data['formtype']	= 'checked';
					$data['uniquevalue']= 0;
					
					if (!empty($data['items'])) {
						$o[] = $layout->render($data);
					}
				}
			}	
		}
		
		// -SPECIFICATIONS-
		if ($this->specifications) {
			phocacartimport('phocacart.specification.specification');
			$specifications = PhocaCartSpecification::getAllSpecificationsAndValues();
			if (!empty($specifications)) {
				foreach($specifications as $k => $v) {
					$data				= array();
					$data['param'] 		= 's['.$v['alias']. ']';
					$data['title']		= $v['title'];
					$data['items']		= $v['value'];
					$data['getparams']	= $this->getArrayParamValues($data['param'], 'array');
					$data['formtype']	= 'checked';
					$data['uniquevalue']= 0;
					
					if (!empty($data['items'])) {
						$o[] = $layout->render($data);
					}
				}
			}	
		}

		$o2 = implode("\n", $o);
		return $o2;
		
	}
	
	public function getArrayParamValues($param, $type = '') {
		
		// Make array from GET parameter values which are stored in string separated by comma
		$app			= JFactory::getApplication();
		
		if ($type == 'int') {
			$paramString 	= $app->input->get($param, 0, $type);
		} else if ($type == 'array') {
			
			$paramE = explode('[', $param);
			if (isset($paramE[0]) && isset($paramE[1])) {
				$paramE[1] 		= str_replace(']', '', $paramE[1]);
				$paramStringE 	= $app->input->get($param[0], array(), $type);
				if (isset($paramStringE[$paramE[1]])) {
					$paramString = $paramStringE[$paramE[1]];
				} else {
					$paramString = '';
				}
			}
			
		}else {
			$paramString 	= $app->input->get($param, '', $type);
		}
		
		$a 		= explode(',', $paramString);
		$inA 	= array();
		if (!empty($a)) {
			if ($type == 'int') {
				foreach($a as $k => $v) {
					$inA[] = (int)$v;
				}
			} else {
				foreach($a as $k => $v) {
					$inA[] = $v;
				}
			}
		}
		return $inA;
	}
	
}
?>