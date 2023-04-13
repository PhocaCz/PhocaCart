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
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;

class PhocacartSearch
{
    public $ajax               		    = 1;
	public $search_options 			    = 0;
	public $hide_buttons 				= 0;
	public $display_inner_icon 		    = 0;
	public $placeholder_text 			= '';
	public $display_active_parameters 	= 0;
    public $load_component_media        = 1;


	public function __construct() {}


	public function renderSearch($options = array()) {

		$o						= array();
		$app					= Factory::getApplication();
		$s 			            = PhocacartRenderStyle::getStyles();
		$layout 	            = new FileLayout('form_search', null, array('component' => 'com_phocacart'));
		$layoutAP 	            = new FileLayout('form_search_active_parameters', null, array('component' => 'com_phocacart'));


        // SEARCH FORM
        $specificIdSuffix = '';
        if (isset($options['specific_id_suffix']) && $options['specific_id_suffix'] != '') {
            $specificIdSuffix = $options['specific_id_suffix'];
        }


		$data                       = array();
		$data['s']                  = $s;
		$data['id'] 			    = 'phSearchBox' . $specificIdSuffix;
		$data['param'] 			    = 'search';
		$data['getparams']		    = PhocacartText::filterValue($app->input->get('search', '', 'string'), 'text');
		$data['title']			    = Text::_('COM_PHOCACART_SEARCH');
		//$category				    = PhocacartRoute::getIdForItemsRoute();
		//$data['getparams'][]	    = $category['idalias'];
		$data['activefilter']	    = PhocacartRoute::isFilterActive();
		//$data['searchoptions']	= $searchOptions;
		$data['search_options']     = $this->search_options;
		$data['hide_buttons']       = $this->hide_buttons;
		$data['display_inner_icon'] = $this->display_inner_icon;
		$data['placeholder_text']   = $this->placeholder_text;

        $filter = new PhocacartFilter;
        $f      = $filter->getActiveFilterValues();

        $dataAP         = array();
        $dataAP['s']    = $s;
        $dataAP['f']    = $f;
        $dataAP['id']   = 'phSearchActiveTags'. $specificIdSuffix;;

         // AJAX WILL BE BASED ON CLASS NOT ON ID (because of more possible instances)

        if ($this->ajax == 0) {
            $o[] = '<div class="' . $data['s']['c']['row'] . '">';
            $o[] = '<div class="' . $data['s']['c']['col.xs12.sm12.md12'] . '">';
            $o[] = '<div id="' . $data['id'] . '" class="phSearchBox '.$data['id'].'">';
            $o[] = $layout->render($data);


        }

        if ($this->display_active_parameters) {
            if ($this->ajax == 0) {
                $o[] = '<div id="' . $dataAP['id'] . '" class="phSearchActiveTags '.$dataAP['id'].'">';
            }

            $o[] = $layoutAP->render($dataAP);// only this is displayed by ajax but if display_active_parameters is enabled

            if ($this->ajax == 0) {
                $o[] = '</div>';
            }
        }


        if ($this->ajax == 0) {



            $o[] = '</div>';
            $o[] = '</div>';
            $o[] = '</div>';
        }



		$o2 = implode("\n", $o);
		return $o2;
	}

	/* Static part */

    /*
     * params ... search option parameters
     */

	public static function getSqlParts($type, $search, $param, $params = array(), $prefix = '') {



		$in 	= '';
        $inA    = [];
        $inAS   = [];
		$where 	= '';
		$left	= '';
		$db		= Factory::getDBO();



		switch($type) {
			case 'int':

				$w 		= $param;
				//$w		= str_replace('%2C', ',', $w);
				$a 		= explode(',', $w);

				$inA 	= array();
				if (!empty($a)) {
					foreach($a as $k => $v) {
						$inA[] = (int)$v;
					}
				}
                $inA = array_unique($inA);
				$in = implode(',', $inA);
			break;

			case 'string':
				$in = $param;
			break;

			case 'array':
				$w	= $param;
				$inA 	= array();

				if (!empty($w)) {
					foreach ($w as $k => $v) {
						$s		= '';
						//$v		= str_replace('%2C', ',', $v);
						$a 		= explode(',', $v);
						if ($k != '' && $v != '' && !empty($a)) {
						    $a = array_unique($a);
							if ($search == 'a') {
								// Attributes
                                // QUERY METHOD ANY (product to display must include one of selected attributes)
								$inA[] = '(at2.alias = '.$db->quote($k). ' AND v2.alias IN ('. '\'' . implode('\',\'', $a). '\'' .'))';

                                // QUERY METHOD ALL (product to display must include all attributes togehter)
                                $i = 0;
                                foreach($a as $k2 => $v2) {
                                    $inAS[$i] = 'at2.alias = '.$db->quote($k). ' AND v2x'.$i.'.alias = "'.$v2.'"';
                                    $i++;
                                }
							} else if ($search == 's') {
								// Specifications
                                // QUERY METHOD ANY (product to display must include one of selected specifications)
								$inA[] = '(s2.alias = '.$db->quote($k). ' AND s2.alias_value IN ('. '\'' . implode('\',\'', $a). '\'' .'))';

                                // QUERY METHOD ALL (product to display must include all specifications togehter)
                                $i = 0;
                                foreach($a as $k2 => $v2) {
                                    $inAS[$i] = 's2x'.$i.'.alias = '.$db->quote($k). ' AND s2x'.$i.'.alias_value = "'.$v2.'"';
                                    $i++;
                                }
							}

						}
					}
				}
				$in = $inA;



			break;

			default:
			break;
		}



		if ($in != '') {

		    switch ($search) {
                case 'tag':

                    if ($params['sql_filter_method_tag'] == 1) {
                        // QUERY METHOD ALL (product to display must include all tags togehter)
                        $left = '';
                        $where = '';
                        if (!empty($inA)) {
                            foreach($inA as $k => $v) {
                                $left .= ' INNER JOIN #__phocacart_tags_related AS tr'.(int)$v.' ON a.id = tr'.(int)$v.'.item_id AND tr'.(int)$v.'.tag_id = '.(int)$v;
                            }
                        }
                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected tags)
                        $where = ' tr.tag_id IN (' . $in . ')';
                        $left = ' LEFT JOIN #__phocacart_tags_related AS tr ON a.id = tr.item_id';
                    }
                break;

                case 'label':

                    if ($params['sql_filter_method_label'] == 1) {
                        // QUERY METHOD ALL (product to display must include all labels togehter)
                        $left = '';
                        $where = '';
                        if (!empty($inA)) {
                            foreach($inA as $k => $v) {
                                $left .= ' INNER JOIN #__phocacart_taglabels_related AS lr'.(int)$v.' ON a.id = lr'.(int)$v.'.item_id AND lr'.(int)$v.'.tag_id = '.(int)$v;
                            }
                        }

                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected label)
                        $where = ' lr.tag_id IN (' . $in . ')';
                        $left = ' LEFT JOIN #__phocacart_taglabels_related AS lr ON a.id = lr.item_id';
                    }

                break;

                // Custom parameters
                case 'parameter':


                    if ($params['sql_filter_method_parameter'] == 1) {
                        // QUERY METHOD ALL (product to display must include all parameter togehter)
                        $left = '';
                        $where = '';
                        if (!empty($inA)) {
                            foreach($inA as $k => $v) {
                                $left .= ' INNER JOIN #__phocacart_parameter_values_related AS pr'.(int)$prefix.(int)$v.' ON a.id = pr'.(int)$prefix.(int)$v.'.item_id AND pr'.(int)$prefix.(int)$v.'.parameter_value_id = '.(int)$v;
                            }
                        }

                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected parameter)
                        $where = ' pr'.(int)$prefix.'.parameter_value_id IN (' . $in . ')';
                        $left = ' LEFT JOIN #__phocacart_parameter_values_related AS pr'.(int)$prefix.' ON a.id = pr'.(int)$prefix.'.item_id';
                    }


                break;

                // Custom fields
/*                case 'field':
                  // TODO different way for multiple values
                  $in = $db->quote($in);
                  // TODO own parameter
                  if ($params['sql_filter_method_parameter'] == 1) {
                    // QUERY METHOD ALL (product to display must include all custom filed values togehter)
                    $left = '';
                    $where = '';
                    if (!empty($inA)) {
                      foreach($inA as $k => $v) {
                        $left .= ' INNER JOIN #__field_values AS fv'.(int)$prefix.(int)$v.' ON a.id = pr'.(int)$prefix.(int)$v.'.item_id AND pr'.(int)$prefix.(int)$v.'.parameter_value_id = '.(int)$v;
                      }
                    }
                  } else {
                    $where = ' fv'.(int)$prefix.'.value IN (' . $in . ')';
                    $left = ' LEFT JOIN #__fields_values AS fv'.(int)$prefix.' ON a.id = fv'.(int)$prefix.'.item_id and fv'.(int)$prefix.'.field_id = ' . (int)$prefix;
                  }

                break;*/

                case 'manufacturer':
                    $where = ' m.id IN (' . $in . ')';
                    //$left = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id'; is asked as default
                break;

                case 'price_from':
                case 'price_to':
                    $currency = PhocacartCurrency::getCurrency();
                    $price = PhocacartPrice::convertPriceCurrentToDefaultCurrency($in, $currency->exchange_rate);

                    if ($search == 'price_from') {
                        $where = ' a.price >= ' . $db->quote($price);
                    } else {
                        $where = ' a.price <= ' . $db->quote($price);
                    };
                break;

                case 'id': // Category
                    $where = ' c.id IN (' . $in . ')';
                    $left = '';//' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';// Category always included
                break;

                case 'c': // Category (c)
                    $where = ' c.id IN (' . $in . ')';
                    $left = '';//' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';// Category always included
                break;

                case 'a': // Attributes

                    $where = '';

                    if ($params['sql_filter_method_attribute'] == 1) {
                        // QUERY METHOD ALL (product to display must include all attributes togehter)
                        if (!empty($inAS)) {
                            $where = ' a.id IN (SELECT at2.product_id FROM #__phocacart_attributes AS at2';
                            foreach ($inAS as $k => $v) {
                                $where .= ' INNER JOIN  #__phocacart_attribute_values AS v2x' . $k . ' ON v2x' . $k . '.attribute_id = at2.id AND ' . $v;
                            }
                            $where .= ' GROUP BY at2.product_id'
                                //.' HAVING COUNT(distinct at2.alias) >= '.(int)$c.')';// problematic on some servers
                                // . ' HAVING COUNT(at2.alias) >= ' . (int)$c
                            . ')';
                        }
                    } else {

                        // QUERY METHOD ANY (product to display must include one of selected attributes)
                        if (!empty($in)) {
                            $c = count($in);
                            $where = ' a.id IN (SELECT at2.product_id FROM #__phocacart_attributes AS at2'
                                . ' LEFT JOIN  #__phocacart_attribute_values AS v2 ON v2.attribute_id = at2.id'
                                . ' WHERE ' . implode(' OR ', $in)
                                . ' GROUP BY at2.product_id'
                                //.' HAVING COUNT(distinct at2.alias) >= '.(int)$c.')';// problematic on some servers
                                . ' HAVING COUNT(at2.alias) >= ' . (int)$c
                                . ')';
                        }
                    }

                    $left = '';
                break;

                case 's': // Specifications

                    $where = '';

                    if ($params['sql_filter_method_specification'] == 1) {
                        // QUERY METHOD ALL (product to display must include all specifications togehter)
                        if (!empty($inAS)) {

                            // We don't have any main table so we need to split all items into:
                            // - one main table
                            // - and all other with INNER JOIN
                            $closeWhere = '';
                            $where = ' a.id IN (SELECT';
                            $i = 0;
                            foreach ($inAS as $k => $v) {
                                if ($i == 0) {
                                    $where .= " s2x".$i.".product_id FROM #__phocacart_specifications AS s2x".$i;
                                    $closeWhere = 'WHERE '.$v;
                                } else {
                                    $where .= " INNER JOIN #__phocacart_specifications AS s2x".$i." ON s2x0.product_id = s2x".$i.".product_id AND ". $v;
                                }
                                $i++;
                                //$where .= ' INNER JOIN  #__phocacart_attribute_values AS v2x' . $k . ' ON v2x' . $k . '.attribute_id = at2.id AND ' . $v;
                            }
                            $where .= ' '. $closeWhere
                            //$where .= ' GROUP BY s2.product_id'
                                //.' HAVING COUNT(distinct at2.alias) >= '.(int)$c.')';// problematic on some servers
                                // . ' HAVING COUNT(at2.alias) >= ' . (int)$c
                            . ')';
                        }
                    } else {

                        // QUERY METHOD ANY (product to display must include one of selected specifications)
                        if (!empty($in)) {
                            $c = count($in);
                            $where = ' a.id IN (SELECT s2.product_id FROM #__phocacart_specifications AS s2'
                                . ' WHERE ' . implode(' OR ', $in)
                                . ' GROUP BY s2.product_id'
                                //.' HAVING COUNT(distinct s2.alias) >= '.(int)$c.')';// problematic on some servers
                                . ' HAVING COUNT(s2.alias) >= ' . (int)$c
                                . ')';
                        }
                    }

                    $left = '';
                break;

                case 'search': // Search

                    $phrase = 'any';
                    if (isset($params['search_matching_option'])) {
                        $phrase = $params['search_matching_option'];
                    }


                    $where = '';
                    switch ($phrase) {
                        case 'exact':
                            $text = $db->quote('%' . $db->escape($in, true) . '%', false);
                            $wheresSub = array();
                            $wheresSub[] = 'a.title LIKE ' . $text;
                            $wheresSub[] = 'a.alias LIKE ' . $text;
                            $wheresSub[] = 'a.metakey LIKE ' . $text;
                            $wheresSub[] = 'a.metadesc LIKE ' . $text;
                            $wheresSub[] = 'a.description LIKE ' . $text;
                            $wheresSub[] = 'a.sku LIKE ' . $text;
                            $wheresSub[] = 'a.ean LIKE ' . $text;
                            // Search EAN, SKU in product attributes (advanced stock management) ... can be different for POS or Online Shop
                            if (isset($params['sql_search_skip_id_specific_type']) && $params['sql_search_skip_id_specific_type'] == 0) {
                                $wheresSub[] = 'ps.sku LIKE ' . $text;
                                $wheresSub[] = 'ps.ean LIKE ' . $text;
                            }
                            if (isset($params['search_deep']) && $params['search_deep'] == 1) {
                              $wheresSub[] = 'a.description_long LIKE ' . $text;
                              $wheresSub[] = 'a.features LIKE ' . $text;
                            }


                            $left = '';

                            // Custom Fields
                            if (isset($params['search_custom_fields']) && $params['search_custom_fields'] == 1) {
                                $user        = Factory::getUser();
                                $groups      = implode(',', $user->getAuthorisedViewLevels());
                                $query       = $db->getQuery(true);
                                $wheresSub[] = 'jf.value LIKE ' . $text;
                                $left        .= ' LEFT JOIN #__fields_values AS jf ON jf.item_id = ' . $query->castAsChar('a.id');
                                $left        .= ' LEFT JOIN #__fields AS f ON f.id = jf.field_id and f.context = ' . $db->q('com_phocacart.phocacartitem') . ' and f.state = 1 and f.access IN (' . $groups . ')';
                            }

                            $where = '(' . implode(') OR (', $wheresSub) . ')';

                        break;

                        case 'all':
                        case 'any':
                        default:

                            $words = explode(' ', $in);
                            $wheres = array();



                            foreach ($words as $word) {

                                if (!$word = trim($word)) {
                                    continue;
                                }

                                $word = $db->quote('%' . $db->escape($word, true) . '%', false);
                                $wheresSub = array();
                                $wheresSub[] = 'a.title LIKE ' . $word;
                                $wheresSub[] = 'a.alias LIKE ' . $word;
                                $wheresSub[] = 'a.metakey LIKE ' . $word;
                                $wheresSub[] = 'a.metadesc LIKE ' . $word;
                                $wheresSub[] = 'a.description LIKE ' . $word;
                                $wheresSub[] = 'a.sku LIKE ' . $word;
                                $wheresSub[] = 'a.ean LIKE ' . $word;
                                if (isset($params['sql_search_skip_id_specific_type']) && $params['sql_search_skip_id_specific_type'] == 0) {
                                    $wheresSub[] = 'ps.sku LIKE ' . $word;
                                    $wheresSub[] = 'ps.ean LIKE ' . $word;
                                }
                                if (isset($params['search_deep']) && $params['search_deep'] == 1) {
                                    $wheresSub[] = 'a.description_long LIKE ' . $word;
                                    $wheresSub[] = 'a.features LIKE ' . $word;
                                }



                                // Custom Fields
                                $left = '';// don't repeat left

                                // Custom Fields
                                if (isset($params['search_custom_fields']) && $params['search_custom_fields'] == 1) {

                                    $user        = Factory::getUser();
                                    $groups      = implode(',', $user->getAuthorisedViewLevels());
                                    $query       = $db->getQuery(true);
                                    $wheresSub[] = 'jf.value LIKE ' . $word;
                                    $left        .= ' LEFT JOIN #__fields_values AS jf ON jf.item_id = ' . $query->castAsChar('a.id');
                                    $left        .= ' LEFT JOIN #__fields AS f ON f.id = jf.field_id and f.context = ' . $db->q('com_phocacart.phocacartitem') . ' and f.state = 1 and f.access IN (' . $groups . ')';
                                }


                                $wheres[] = implode(' OR ', $wheresSub);
                            }

                            $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';


                        break;
                    }
                break;

                default:
                break;
            }


		}

		$a			= array();
		$a['where'] = $where;
		$a['left']	= $left;

		return $a;

	}

}
