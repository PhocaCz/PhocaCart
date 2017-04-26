<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartFilter
{
	public $taglist			= false;
	public $tag				= false;
	public $manufacturer	= false;
	public $price			= false;
	public $attributes		= false;
	public $specifications	= false;
	
	public $enable_color_filter 		= false;
	public $enable_image_filter 		= false;
	public $image_style_image_filter 	= false;
	
	public $ordering_tag				= 1;
	public $ordering_manufacturer		= 1;
	public $ordering_attribute			= 1;
	public $ordering_specification		= 1;
	
	
	public function __construct() {}
	
	public function renderList() {
		
		// $db = JFactory::getDBO();
		$o			= array();
		//$app		= JFactory::getApplication();
		$layout 	= new JLayoutFile('form_filter_checkbox', null, array('component' => 'com_phocacart'));
		$layout2 	= new JLayoutFile('form_filter_text', null, array('component' => 'com_phocacart'));
		$layout3 	= new JLayoutFile('form_filter_checkbox_categories', null, array('component' => 'com_phocacart'));
		$layout4 	= new JLayoutFile('form_filter_color', null, array('component' => 'com_phocacart'));
		$layout5 	= new JLayoutFile('form_filter_image', null, array('component' => 'com_phocacart'));
		
		
		$pathProductImage = PhocacartPath::getPath('productimage');
		
		// =FILTER=
		$data['getparams']	= array();
		
		//-CATEGORY- ACTIVE CATEGORY
		$data['param'] 		= 'id';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		$category			= PhocacartRoute::getIdForItemsRoute();
		
		$data['getparams'][]= $category['idalias'];
		$data['nrinalias']	= 1;
		$data['formtype']	= 'category';
		$data['uniquevalue']= 1;
		

		if ((int)$this->category == 1 && (int)$category['id'] > 0) {
			/* phocacart import('phocacart.category.category');*/
			$data['items'][] = PhocacartCategory::getCategoryById($category['id']);
		
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
			$data['items'] 	= PhocacartCategory::getCategoryTreeArray();
			$data['output']	= PhocacartCategory::nestedToCheckBox($data['items'], $data);
			
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
			/*phocacart import('phocacart.tag.tag');*/
			$data['items'] = PhocacartTag::getAllTags($this->ordering_tag);
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
			/*phocacart import('phocacart.manufacturer.manufacturer');*/
			$data['items'] = PhocacartManufacturer::getAllManufacturers($this->ordering_manufacturer);
		}
		
		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}
		
		// -ATTRIBUTES-
		if ($this->attributes) {
			/*phocacart import('phocacart.attribute.attribute');*/
			$attributes = PhocacartAttribute::getAllAttributesAndOptions($this->ordering_attribute);
			
			if (!empty($attributes)) {
				foreach($attributes as $k => $v) {
					
					$data				= array();
					$data['param'] 		= 'a['.$v['alias']. ']';
					$data['title']		= $v['title'];
					$data['items']		= $v['options'];
					$data['getparams']	= $this->getArrayParamValues($data['param'], 'array');
					$data['uniquevalue']= 0;
					$data['pathitem']	= $pathProductImage;
					
					if (!empty($data['items'])) {
						
						if ($this->enable_color_filter && isset($v['type']) && ($v['type'] == 2 || $v['type'] == 5)) {
							// Color
							$data['formtype']	= 'text';
							$o[] = $layout4->render($data);
						} else if ($this->enable_image_filter && isset($v['type']) && ($v['type'] == 3 || $v['type'] == 6)) {
							// Image
							$data['formtype']	= 'text';
							$data['style']		= strip_tags($this->image_style_image_filter);
							$o[] = $layout5->render($data);
						} else {
							// Select
							$data['formtype']	= 'checked';
							$o[] = $layout->render($data);
						}
					}
				}
			}	
		}
		
		// -SPECIFICATIONS-
		if ($this->specifications) {
			/*phocacart import('phocacart.specification.specification');*/
			$specifications = PhocacartSpecification::getAllSpecificationsAndValues($this->ordering_specification);
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