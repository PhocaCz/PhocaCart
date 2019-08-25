<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartFilter
{
	//public $taglist			= false;
	public $tag				= false;
	public $label			= false;
	public $manufacturer	= false;
	public $price			= false;
	public $attributes		= false;
	public $specifications	= false;

	public $enable_color_filter 		= false;
	public $enable_image_filter 		= false;
	public $image_style_image_filter 	= false;

	public $ordering_tag				= 1;
	public $ordering_label				= 1;
	public $ordering_manufacturer		= 1;
	public $ordering_attribute			= 1;
	public $ordering_specification		= 1;

	public $manufacturer_title			= '';
	public $filter_language				= 0;
	public $open_filter_panel           = 1;


	public function __construct() {}

	public function renderList() {

		// $db = JFactory::getDBO();
		$o			= array();
		$s 			= PhocacartRenderStyle::getStyles();
		//$app		= JFactory::getApplication();
		$layout 	= new JLayoutFile('form_filter_checkbox', null, array('component' => 'com_phocacart')); //foreach with items in layout
		$layout2 	= new JLayoutFile('form_filter_text', null, array('component' => 'com_phocacart'));// foreach with items in this class
		$layout3 	= new JLayoutFile('form_filter_checkbox_categories', null, array('component' => 'com_phocacart'));// foreach with items in this class
		$layout4 	= new JLayoutFile('form_filter_color', null, array('component' => 'com_phocacart'));// foreach with items in layout
		$layout5 	= new JLayoutFile('form_filter_image', null, array('component' => 'com_phocacart'));// foreach with items in layout


		$language = '';
		if ($this->filter_language == 1) {
			$lang 		= JFactory::getLanguage();
			$language	= $lang->getTag();
		}

		$pathProductImage = PhocacartPath::getPath('productimage');

		// =FILTER=
		$data				= array();
		$data['s']			= $s;
		$data['getparams']	= array();

		//-CATEGORY- ACTIVE CATEGORY
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'id';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		$category			= PhocacartRoute::getIdForItemsRoute();

		$data['getparams'][]= $category['idalias'];
		$data['nrinalias']	= 1;
		$data['formtype']	= 'category';
		$data['uniquevalue']= 1;
		$data['params']['open_filter_panel'] = $this->open_filter_panel;


		if ((int)$this->category == 1 && (int)$category['id'] > 0) {
			/* phocacart import('phocacart.category.category');*/
			$data['items'][] = PhocacartCategory::getCategoryById($category['id']);

			if (!empty($data['items'])) {
				$o[] = $layout->render($data);
			}
		}



		// -CATEGORY MORE- (ALL CATEGORIES)
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'c';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;


		// OPEN OR CLOSE PANEL
		// $data['params']['open_filter_panel'] = $this->open_filter_panel;
		if ((int)$this->category == 2) {
			$data['active'] = 0;
			$data['items'] 	= PhocacartCategory::getCategoryTreeArray(1, '', '', array(0,1), $language);
			$data['output']	= PhocacartCategory::nestedToCheckBox($data['items'], $data, 0, $data['active']);

			if ($this->open_filter_panel == 0) {
			    $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
			    $data['triangle_class'] = $s['i']['triangle-right'];
			} else if ($this->open_filter_panel == 2 && $data['active'] == 1) {
			    $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];// closed as default and wait if there is some active item to open it
				$data['triangle_class'] = $s['i']['triangle-bottom'];
			} else if ($this->open_filter_panel == 2 && $data['active'] == 0) {
			    $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
			    $data['triangle_class'] = $s['i']['triangle-right'];
			} else {
				$data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];
				$data['triangle_class'] = $s['i']['triangle-bottom'];
			}

			if (!empty($data['items'])) {
				$o[] = $layout3->render($data);
			}
		}

		// -PRICE- AVAILABLE PRODUCTS ONLY - Yes
		$data				= array();
		$data['s']			= $s;
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
		$data['filterprice']= $this->price;

		// OPEN OR CLOSE PANEL
		// $data['params']['open_filter_panel'] = $this->open_filter_panel;
		if ($this->open_filter_panel == 0) {
		    $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
		    $data['triangle_class'] = $s['i']['triangle-right'];
		} else if ($this->open_filter_panel == 2 && ($data['getparams'][0] != '' || $data['getparams2'][0] != '' )) {
		    $data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];// closed as default and wait if there is some active item to open it
			$data['triangle_class'] = $s['i']['triangle-bottom'];
		} else if ($this->open_filter_panel == 2 && ($data['getparams'][0] == '' || $data['getparams2'][0] == '' )) {
		    $data['collapse_class'] = $s['c']['panel-collapse.collapse'];
		    $data['triangle_class'] = $s['i']['triangle-right'];
		} else {
			$data['collapse_class'] = $s['c']['panel-collapse.collapse.in'];
			$data['triangle_class'] = $s['i']['triangle-bottm'];
		}

		if ($this->price > 0) {
			$o[] = $layout2->render($data);
		}

		// -TAG-
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'tag';
		$data['title']		= JText::_('COM_PHOCACART_TAGS');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		$data['params']['open_filter_panel'] = $this->open_filter_panel;

		if ($this->tag) {
			/*phocacart import('phocacart.tag.tag');*/
			$data['items'] = PhocacartTag::getAllTags($this->ordering_tag, 1, 0, $language);

		}

		if (!empty($data['items'])) {

			$o[] = $layout->render($data);
		}

		// -LABEL-
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'label';
		$data['title']		= JText::_('COM_PHOCACART_LABELS');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		$data['params']['open_filter_panel'] = $this->open_filter_panel;

		if ($this->label) {
			/*phocacart import('phocacart.tag.tag');*/
			$data['items'] = PhocacartTag::getAllTags($this->ordering_label, 1, 1, $language);

		}

		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}

		// -MANUFACTURER- AVAILABLE PRODUCTS ONLY - Yes
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'manufacturer';
		$data['title']		= $this->manufacturer_title != '' ? JText::_($this->manufacturer_title) : JText::_('COM_PHOCACART_MANUFACTURERS');
		$data['getparams']	= $this->getArrayParamValues($data['param'], 'string');
		$data['nrinalias']	= 1;
		$data['formtype']	= 'checked';
		$data['uniquevalue']= 0;
		$data['params']['open_filter_panel'] = $this->open_filter_panel;

		if ($this->manufacturer) {
			/*phocacart import('phocacart.manufacturer.manufacturer');*/
			$data['items'] = PhocacartManufacturer::getAllManufacturers($this->ordering_manufacturer, 1, $language);
		}

		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}

		// -ATTRIBUTES- AVAILABLE PRODUCTS ONLY - Yes
		if ($this->attributes) {
			/*phocacart import('phocacart.attribute.attribute');*/
			$attributes = PhocacartAttribute::getAllAttributesAndOptions($this->ordering_attribute, 1, $language);

			if (!empty($attributes)) {
				foreach($attributes as $k => $v) {

					$data				= array();
					$data['s']			= $s;
					$data['param'] 		= 'a['.$v['alias']. ']';
					$data['title']		= $v['title'];
					$data['items']		= $v['options'];
					$data['getparams']	= $this->getArrayParamValues($data['param'], 'array');
					$data['uniquevalue']= 0;
					$data['pathitem']	= $pathProductImage;
					$data['params']['open_filter_panel'] = $this->open_filter_panel;

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

		// -SPECIFICATIONS- AVAILABLE PRODUCTS ONLY - Yes
		if ($this->specifications) {
			/*phocacart import('phocacart.specification.specification');*/
			$specifications = PhocacartSpecification::getAllSpecificationsAndValues($this->ordering_specification, 1, $language);

			if (!empty($specifications)) {
				foreach($specifications as $k => $v) {
					$data				= array();
					$data['s']			= $s;
					$data['param'] 		= 's['.$v['alias']. ']';
					$data['title']		= $v['title'];
					$data['items']		= $v['value'];
					$data['getparams']	= $this->getArrayParamValues($data['param'], 'array');
					$data['formtype']	= 'checked';
					$data['uniquevalue']= 0;
                    $data['pathitem']	= $pathProductImage;
                    $data['params']['open_filter_panel'] = $this->open_filter_panel;



					if (!empty($data['items'])) {

                        if ($this->enable_color_filter_spec) {
                            // Color
                            $data['formtype']	= 'text';
                            $o[] = $layout4->render($data);
                        } else if ($this->enable_image_filter_spec) {
                            // Image
                            $data['formtype']	= 'text';
                            $data['style']		= strip_tags($this->image_style_image_filter_spec);
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
