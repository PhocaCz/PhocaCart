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
	public $category		= false;

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

	public $force_category				= 0;
	public $limit_attributes_category	= 0;
	public $limit_tags_category			= 0;
	public $limit_labels_category		= 0;
	public $limit_price_category		= 0;
	public $limit_manufacturers_category	= 0;
	public $limit_specifications_category = 0;


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


		// - SPECIFIC CASE - CATEGORY -
		// When we are in category view the identificator for category is ID
		// When we are in item view the identificator for category is CATID
		// When we are in items view (search, filter) the identificator for category is c=1-category
		// So when we are in category or item view and someone will filter products we can even set the category (form ID or CATID to c=1-category) for items view
		// Parameter for this is "force_category"
		// We even can limit filter items like attributes only for specific category
		// Parameter for this is "limit_attributes_category" (attributes)
		$category					= PhocacartRoute::getIdForItemsRoute();// Used for parameter: Filter Category: Yes (Active Category) (int)$this->category == 1
		$forceCategory				= array();
		$forceCategory['id']		= 0;
		$forceCategory['idalias']	= '';
		if ($this->force_category == 1) {
			$forceCategory	= $category;
		}



		//-CATEGORY- ACTIVE CATEGORY (CATEGORY VIEW)
		$data				= array();
		$data['s']			= $s;
		$data['param'] 		= 'id';
		$data['title']		= JText::_('COM_PHOCACART_CATEGORY');
		//$category			= PhocacartRoute::getIdForItemsRoute();


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



		// -CATEGORY MORE- (ALL CATEGORIES, ITEMS VIEW)
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
			// if we are in category view and want to force active category when clicking on filter
			// so we want to move the category id from CATEGORY VIEW to ITEMS VIEW
			// and we should mark the category active in category list = $forceActive
			$data['items'] 	= PhocacartCategory::getCategoryTreeArray(1, '', '', array(0,1), $language);
			$data['output']	= PhocacartCategory::nestedToCheckBox($data['items'], $data, 0, $data['active'], $forceCategory['id']);

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


		// LIMIT ... TO CATEGORY - Display items only from products of the current category
		$activeProducts = array();
		$activeCategory = array();
		$activeProductsAttributes 	= array();
		$activeProductsTags			= array();
		$activeProductsLabels 		= array();
		$activeProductsPrice 		= array();
		$activeProductsManufacturers	= array();
		$activeProductsSpecifications	= array();
		if($this->limit_attributes_category == 1 || $this->limit_tags_category == 1 || $this->limit_labels_category == 1
			|| $this->limit_price_category == 1 || $this->limit_manufacturers_category == 1 || $this->limit_specifications_category == 1) {
			if ((int)$category['id'] > 0) {
				$activeCategory[] = (int)$category['id'];
			}

			if (!empty($data['getparams'])) {
				foreach ($data['getparams'] as $k => $v) {
					$activeCategory[] = (int)$v;
				}
			}
			$activeCategory = array_unique($activeCategory);
			$activeProducts = PhocacartProduct::getProducts(0, 0, 1, 0, true, false, false, 0, $activeCategory, 0, array(0, 1), 'a.id', 'column');

			if($this->limit_attributes_category == 1) {
				$activeProductsAttributes = $activeProducts;
			}
			if($this->limit_tags_category == 1) {
				$activeProductsTags = $activeProducts;
			}
			if($this->limit_labels_category == 1) {
				$activeProductsLabels = $activeProducts;
			}
			if($this->limit_price_category == 1) {
				$activeProductsPrice = $activeProducts;
			}
			if($this->limit_manufacturers_category == 1) {
				$activeProductsManufacturers = $activeProducts;
			}
			if($this->limit_specifications_category == 1) {
				$activeProductsSpecifications = $activeProducts;
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

		$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

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
			$data['triangle_class'] = $s['i']['triangle-bottom'];
		}

		if ($this->price > 0) {
			$o[] = $layout2->render($data);
		}


		// RENDER PRICE FROM TO INPUT RANGE

		if ($this->price == 2 || $this->price == 3) {

			$document					= JFactory::getDocument();
			$document->addScript(JURI::root(true).'/media/com_phocacart/js/ui/jquery-ui.slider.min.js');
			JHTML::stylesheet('media/com_phocacart/js/ui/jquery-ui.slider.min.css' );

			$currency 	= PhocacartCurrency::getCurrency();
			PhocacartRenderJs::getPriceFormatJavascript($currency->price_decimals, $currency->price_dec_symbol, $currency->price_thousands_sep, $currency->price_currency_symbol, $currency->price_prefix, $currency->price_suffix, $currency->price_format);
			$price_from	= $this->getArrayParamValues('price_from', 'string');
			$price_to	= $this->getArrayParamValues('price_to', 'string');
			$min		= PhocacartProduct::getProductPrice(2, 1, $language, $activeProductsPrice);// min price
			$max		= PhocacartProduct::getProductPrice(1, 1, $language, $activeProductsPrice);// max price

			if (!$min) {
				$min = 0;
			}
			if (!$max) {
				$max = 0;
			}

			if ($price_to[0] == '') {
				$price_to[0] = $max;
			}
			if ($price_from[0] == '') {
				$price_from[0] = $min;
			}

			PhocacartRenderJs::renderFilterRange($min, $max, $price_from[0], $price_to[0]);
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

		$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

		if ($this->tag) {
			/*phocacart import('phocacart.tag.tag');*/
			$data['items'] = PhocacartTag::getAllTags($this->ordering_tag, 1, 0, $language, $activeProductsTags);

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

		$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

		if ($this->label) {
			/*phocacart import('phocacart.tag.tag');*/
			$data['items'] = PhocacartTag::getAllTags($this->ordering_label, 1, 1, $language, $activeProductsLabels);

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

		$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

		if ($this->manufacturer) {
			/*phocacart import('phocacart.manufacturer.manufacturer');*/
			$data['items'] = PhocacartManufacturer::getAllManufacturers($this->ordering_manufacturer, 1, $language, $activeProductsManufacturers);
		}

		if (!empty($data['items'])) {
			$o[] = $layout->render($data);
		}

		// -ATTRIBUTES- AVAILABLE PRODUCTS ONLY - Yes
		if ($this->attributes) {
			/*phocacart import('phocacart.attribute.attribute');*/
			$attributes = PhocacartAttribute::getAllAttributesAndOptions($this->ordering_attribute, 1, $language, $activeProductsAttributes);

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

					$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view

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
			$specifications = PhocacartSpecification::getAllSpecificationsAndValues($this->ordering_specification, 1, $language, $activeProductsSpecifications);

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

					$data['forcecategory']['idalias'] 	= $forceCategory['idalias'];// This input form field can force category when filtering from category/item view to items view


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
