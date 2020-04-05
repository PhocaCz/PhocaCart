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

class PhocacartSearch
{
    public $ajax               		    = 1;
	public $search_options 			    = 0;
	public $hide_buttons 				= 0;
	public $display_inner_icon 		    = 0;
	public $placeholder_text 			= '';
	public $display_active_parameters 	= 0;


	public function __construct() {}


	public function renderSearch($options = array()) {

		$o						= array();
		$app					= JFactory::getApplication();
		$s 			            = PhocacartRenderStyle::getStyles();
		$layout 	            = new JLayoutFile('form_search', null, array('component' => 'com_phocacart'));
		$layoutAP 	            = new JLayoutFile('form_search_active_parameters', null, array('component' => 'com_phocacart'));


        // SEARCH FORM
		$data                       = array();
		$data['s']                  = $s;
		$data['id'] 			    = 'phSearchBox';// AJAX ID
		$data['param'] 			    = 'search';
		$data['getparams']		    = PhocacartText::filterValue($app->input->get('search', '', 'string'), 'text');
		$data['title']			    = JText::_('COM_PHOCACART_SEARCH');
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
        $dataAP['id']   = 'phSearchActiveTags';


        if ($this->ajax == 0) {
            $o[] = '<div class="' . $data['s']['c']['row'] . '">';
            $o[] = '<div class="' . $data['s']['c']['col.xs12.sm12.md12'] . '">';
            $o[] = '<div id="' . $data['id'] . '">';
            $o[] = $layout->render($data);


        }

        if ($this->display_active_parameters) {
            if ($this->ajax == 0) {
                $o[] = '<div id="' . $dataAP['id'] . '">';
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
		$where 	= '';
		$left	= '';
		$db		= JFactory::getDBO();

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
							if ($search == 'a') {
								// Attributes
								$inA[] = '(at2.alias = '.$db->quote($k). ' AND v2.alias IN ('. '\'' . implode('\',\'', $a). '\'' .'))';
							} else if ($search == 's') {
								// Specifications
								$inA[] = '(s2.alias = '.$db->quote($k). ' AND s2.alias_value IN ('. '\'' . implode('\',\'', $a). '\'' .'))';
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
                    $where = ' tr.tag_id IN (' . $in . ')';
                    $left = ' LEFT JOIN #__phocacart_tags_related AS tr ON a.id = tr.item_id';
                break;

                case 'label':
                    $where = ' lr.tag_id IN (' . $in . ')';
                    $left = ' LEFT JOIN #__phocacart_taglabels_related AS lr ON a.id = lr.item_id';
                break;

                // Custom parameters
                case 'parameter':
                    $where = ' pr'.(int)$prefix.'.parameter_value_id IN (' . $in . ')';
                    $left = ' LEFT JOIN #__phocacart_parameter_values_related AS pr'.(int)$prefix.' ON a.id = pr'.(int)$prefix.'.item_id';
                break;

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
                    $left = '';
                break;

                case 's': // Specifications

                    $where = '';

                    if (!empty($in)) {
                        $c = count($in);
                        $where = ' a.id IN (SELECT s2.product_id FROM #__phocacart_specifications AS s2'
                            . ' WHERE ' . implode(' OR ', $in)
                            . ' GROUP BY s2.product_id'
                            //.' HAVING COUNT(distinct s2.alias) >= '.(int)$c.')';// problematic on some servers
                            . ' HAVING COUNT(s2.alias) >= ' . (int)$c
                            . ')';
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
                            $where = '(' . implode(') OR (', $wheresSub) . ')';
                            $left = '';
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
                                $wheres[] = implode(' OR ', $wheresSub);
                            }

                            $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
                            $left = '';

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
