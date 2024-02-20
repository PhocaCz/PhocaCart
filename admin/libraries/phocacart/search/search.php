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
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

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


	public function renderSearch($options = [])
    {
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
		$data['activefilter']	    = PhocacartRoute::isFilterActive();
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

    private static function prepareKeywords(?string $search, string $matchinOption = 'any'): array
    {
        if (!$search) {
            return [];
        }

        switch ($matchinOption) {
            case 'exact':
                $keywords = [$search];
                break;
            case 'all':
            case 'any':
                $keywords = explode(' ', $search);
                break;
        }

        array_walk($keywords, function(string &$keyword) {
            $keyword = trim($keyword);
        });

        $keywords = array_filter($keywords);

        if (!$keywords) {
            return [];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        array_walk($keywords, function(string &$keyword) use ($db) {
            $keyword = $db->quote('%' . $db->escape($keyword) . '%');
        });

        return $keywords;
    }

    private static function prepareIntArray(?string $search): array
    {
        if (!$search) {
            return [];
        }

        $values = explode(',', $search);
        $values = ArrayHelper::toInteger($values);
        $values = array_unique($values);

        return $values;
    }

    private static function getSqlPartsInt($searchArea, $value, array $searchParams = [], $prefix = ''): array
    {
        $where 	= '';
        $join	= '';
        $prefix = (int)$prefix;
        $db		= Factory::getDBO();

        $values = self::prepareIntArray($value);

        if ($values) {
            switch ($searchArea) {
                case 'tag':
                    if ($searchParams['sql_filter_method_tag']) {
                        // QUERY METHOD ALL (product to display must include all tags togehter)
                        foreach($values as $v) {
                            $join .= ' INNER JOIN #__phocacart_tags_related AS tr' . $v . ' ON a.id = tr' . $v . '.item_id AND tr' . $v . '.tag_id = ' . $v;
                        }
                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected tags)
                        $where = ' tr.tag_id IN (' . implode(', ', $values) . ')';
                        $join = ' LEFT JOIN #__phocacart_tags_related AS tr ON a.id = tr.item_id';
                    }
                    break;

                case 'label':
                    if ($searchParams['sql_filter_method_label']) {
                        // QUERY METHOD ALL (product to display must include all labels togehter)
                        foreach($values as $v) {
                            $join .= ' INNER JOIN #__phocacart_taglabels_related AS lr' . $v . ' ON a.id = lr' . $v . '.item_id AND lr' . $v . '.tag_id = ' . $v;
                        }
                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected label)
                        $where = ' lr.tag_id IN (' . implode(', ', $values) . ')';
                        $join = ' LEFT JOIN #__phocacart_taglabels_related AS lr ON a.id = lr.item_id';
                    }
                    break;

                // Custom parameters
                case 'parameter':
                    if ($searchParams['sql_filter_method_parameter']) {
                        // QUERY METHOD ALL (product to display must include all parameter togehter)
                        foreach($values as $v) {
                            $join .= ' INNER JOIN #__phocacart_parameter_values_related AS pr' . $prefix . $v . ' ON a.id = pr' . $prefix . $v . '.item_id AND pr' . $prefix . $v . '.parameter_value_id = ' . $v;
                        }
                    } else {
                        // QUERY METHOD ANY (product to display must include one of selected parameter)
                        $where = ' pr' .  $prefix . '.parameter_value_id IN (' . implode(', ', $values) . ')';
                        $join = ' LEFT JOIN #__phocacart_parameter_values_related AS pr' . $prefix . ' ON a.id = pr' . $prefix . '.item_id';
                    }
                    break;

                case 'field':
                    // TODO search custom fields
                    break;

                case 'manufacturer':
                    $where = ' m.id IN (' . implode(', ', $values) . ')';
                    break;

                case 'price_from':
                case 'price_to':
                    $currency = PhocacartCurrency::getCurrency();
                    $price = PhocacartPrice::convertPriceCurrentToDefaultCurrency($value, $currency->exchange_rate);

                    if ($searchArea == 'price_from') {
                        $where = ' a.price >= ' . $db->quote($price);
                    } else {
                        $where = ' a.price <= ' . $db->quote($price);
                    };
                    break;

                case 'id': // Category
                case 'c': // Category (c)
                    $where = ' c.id IN (' . implode(', ', $values) . ')';
                    break;
            }
        }

        return [
            'where' => $where,
            'left' => $join,
        ];
    }

    private static function getSqlPartsString($value, array $searchParams = []): array
    {
        $where = '';
        $join  = '';

        $searchMatch = $searchParams['search_matching_option'] ?? 'any';
        $keywords    = self::prepareKeywords($value, $searchMatch);

        if ($keywords) {
            $where = [];
            foreach ($keywords as $keyword) {
                if (I18nHelper::useI18n()) {
                    $conditions = [
                        'coalesce(i18n_a.title, a.title) LIKE ' . $keyword,
                        'coalesce(i18n_a.alias, a.alias) LIKE ' . $keyword,
                        'coalesce(i18n_a.title_long, a.title_long) LIKE ' . $keyword,
                        'coalesce(i18n_a.metatitle, a.metatitle) LIKE ' . $keyword,
                        'coalesce(i18n_a.metakey, a.metakey) LIKE ' . $keyword,
                        'coalesce(i18n_a.metadesc, a.metadesc) LIKE ' . $keyword,
                        'coalesce(i18n_a.description, a.description) LIKE ' . $keyword,
                        'a.sku LIKE ' . $keyword,
                        'a.ean LIKE ' . $keyword,
                    ];

                    if ($searchParams['search_deep'] ?? false) {
                        $conditions[] = 'coalesce(i18n_a.description_long, a.description_long) LIKE ' . $keyword;
                        $conditions[] = 'coalesce(i18n_a.features, a.features) LIKE ' . $keyword;
                    }

                    if (!in_array($searchParams['sql_search_skip_id'] ?? 1, [1, 2])) {
                        $conditions[] = 'ps.sku LIKE ' . $keyword;
                        $conditions[] = 'ps.ean LIKE ' . $keyword;
                    }
                } else {
                    $conditions = [
                        'a.title LIKE ' . $keyword,
                        'a.alias LIKE ' . $keyword,
                        'a.title_long LIKE ' . $keyword,
                        'a.metatitle LIKE ' . $keyword,
                        'a.metakey LIKE ' . $keyword,
                        'a.metadesc LIKE ' . $keyword,
                        'a.description LIKE ' . $keyword,
                        'a.sku LIKE ' . $keyword,
                        'a.ean LIKE ' . $keyword,
                    ];

                    if ($searchParams['search_deep'] ?? false) {
                        $conditions[] = 'a.description_long LIKE ' . $keyword;
                        $conditions[] = 'a.features LIKE ' . $keyword;
                    }

                    if (!in_array($searchParams['sql_search_skip_id'] ?? 1, [1, 2])) {
                        $conditions[] = 'ps.sku LIKE ' . $keyword;
                        $conditions[] = 'ps.ean LIKE ' . $keyword;
                    }
                }
                $where[] = '(' . implode(' OR ', $conditions) . ')';
            }
            $where = '(' . implode(($searchMatch === 'all' ? ') AND (' : ') OR ('), $where) . ')';
        }

        return [
            'where' => $where,
            'left'  => $join,
        ];
    }

    private static function getSqlPartsArray($searchArea, $value, $searchParams = []): array
    {
        $where = '';
        $join  = '';
        $db    = Factory::getDBO();

        $inA = [];
        $inAS = [];

        if (is_array($value) && $value) {
            foreach ($value as $k => $v) {
                $a = explode(',', $v);
                $a = array_unique($a);
                if ($k && $v) {
                    if ($searchArea == 'a') {
                        // Attributes
                        // QUERY METHOD ANY (product to display must include one of selected attributes)
                        if (I18nHelper::useI18n()) {
                            $inA[] = '(coalesce(i18n_at2.alias, at2.alias) = ' . $db->quote($k) . ' AND coalesce(i18n_v2.alias, v2.alias) IN (' . '\'' . implode('\',\'', $a) . '\'' . '))';
                        } else {
                            $inA[] = '(at2.alias = ' . $db->quote($k) . ' AND v2.alias IN (' . '\'' . implode('\',\'', $a) . '\'' . '))';
                        }


                        // QUERY METHOD ALL (product to display must include all attributes togehter)
                        $i = 0;
                        foreach ($a as $v2) {

                            if (I18nHelper::useI18n()) {
                                $inAS[$i] = 'coalesce(i18n_at2.alias, at2.alias) = ' . $db->quote($k) . ' AND coalesce(i18n_v2x'.$i.'.alias, v2x'.$i.'.alias) = "' . $v2 . '"';
                            } else {
                                $inAS[$i] = 'at2.alias = ' . $db->quote($k) . ' AND v2x' . $i . '.alias = "' . $v2 . '"';
                            }

                            $i++;
                        }
                    }
                    else if ($searchArea == 's') {
                        // Specifications
                        // QUERY METHOD ANY (product to display must include one of selected specifications)
                        if (I18nHelper::useI18n()) {
                            $inA[] = '(coalesce(i18n_s2.alias, s2.alias) = ' . $db->quote($k) . ' AND coalesce(i18n_s2.alias_value, s2.alias_value) IN (' . '\'' . implode('\',\'', $a) . '\'' . '))';
                        } else {
                            $inA[] = '(s2.alias = ' . $db->quote($k) . ' AND s2.alias_value IN (' . '\'' . implode('\',\'', $a) . '\'' . '))';
                        }

                        // QUERY METHOD ALL (product to display must include all specifications togehter)
                        $i = 0;
                        foreach ($a as $v2) {
                            //$inAS[$i] = 's2x' . $i . '.alias = ' . $db->quote($k) . ' AND s2x' . $i . '.alias_value = "' . $v2 . '"';

                            if (I18nHelper::useI18n()) {
                                $inAS[$i] = 'coalesce(i18n_s2x'.$i.'.alias, s2x'.$i.'.alias) = ' . $db->quote($k) . ' AND coalesce(i18n_s2x'.$i.'.alias_value, s2x'.$i.'.alias_value) = "' . $v2 . '"';
                            } else {
                                $inAS[$i] = 's2x' . $i . '.alias = ' . $db->quote($k) . ' AND s2x' . $i . '.alias_value = "' . $v2 . '"';
                            }

                            $i++;
                        }
                    }

                }
            }
        }
        $condition = $inA;


        if ($condition != '') {
            switch ($searchArea) {
                case 'a': // Attributes
                    if ($searchParams['sql_filter_method_attribute'] == 1) {
                        // QUERY METHOD ALL (product to display must include all attributes togehter)
                        if (!empty($inAS)) {
                            $where = ' a.id IN (SELECT at2.product_id FROM #__phocacart_attributes AS at2';


                            $where .= I18nHelper::sqlJoin('#__phocacart_attributes_i18n', 'at2');
                           // $where .= I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n', 'v2');

                            foreach ($inAS as $k => $v) {

                                 if (I18nHelper::isI18n()) {
                                     $where .= ' LEFT JOIN #__phocacart_attribute_values AS v2x'.$k . ' ON v2x'.$k.'.attribute_id = at2.id';
                                    $where .= I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n', 'v2x' . $k );
                                     $where .= ' INNER JOIN  #__phocacart_attribute_values AS v2bx' . $k . ' ON v2x' . $k . '.attribute_id = at2.id AND ' . $v;
                                 } else {
                                     $where .= ' INNER JOIN  #__phocacart_attribute_values AS v2x' . $k . ' ON v2x' . $k . '.attribute_id = at2.id AND ' . $v;
                                 }

                                //$where .= 'AND ' . $v;
                            }

                            $where .= ' GROUP BY at2.product_id)';




                        }
                    }
                    else {

                        // QUERY METHOD ANY (product to display must include one of selected attributes)
                        if (!empty($condition)) {
                            $c     = count($condition);
                            $where = ' a.id IN (SELECT at2.product_id FROM #__phocacart_attributes AS at2'
                                . ' LEFT JOIN  #__phocacart_attribute_values AS v2 ON v2.attribute_id = at2.id'


                                . I18nHelper::sqlJoin('#__phocacart_attributes_i18n', 'at2')
                                . I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n', 'v2')
                                . ' WHERE ' . implode(' OR ', $condition)
                                . ' GROUP BY at2.product_id'
                                //.' HAVING COUNT(distinct at2.alias) >= '.(int)$c.')';// problematic on some servers
                                . ' HAVING COUNT(at2.alias) >= ' . (int) $c
                                . ')';
                        }
                    }
                    break;

                case 's': // Specifications
                    if ($searchParams['sql_filter_method_specification'] == 1) {

                        // QUERY METHOD ALL (product to display must include all specifications togehter)
                        if (!empty($inAS)) {
                            // We don't have any main table so we need to split all items into:
                            // - one main table
                            // - and all other with INNER JOIN
                            $closeWhere = '';
                            $where      = ' a.id IN (SELECT';
                            $i          = 0;
                            foreach ($inAS as $v) {
                                if ($i == 0) {

                                    $where  .= " s2x" . $i . ".product_id FROM #__phocacart_specifications AS s2x" . $i;

                                   // $where .= I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's2');
                                    $where .= I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's2x' . $i );

                                    $closeWhere = 'WHERE ' . $v;
                                }
                                else {

                                    $where .= I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's2x' . $i );
                                    if (I18nHelper::isI18n()) {
                                        $where .= " INNER JOIN #__phocacart_specifications AS s2bx" . $i . " ON s2x" . $i . ".product_id = s2x" . $i . ".product_id AND " . $v;
                                    } else {
                                        $where .= " INNER JOIN #__phocacart_specifications AS s2x".$i." ON s2x" . $i . ".product_id = s2x".$i.".product_id AND ". $v;
                                    }
                                }
                                $i++;
                            }



                            $where .= ' ' . $closeWhere . ')';
                        }
                    }
                    else {
                        // QUERY METHOD ANY (product to display must include one of selected specifications)
                        if (!empty($condition)) {
                            $c     = count($condition);

                            $where = ' a.id IN (SELECT s2.product_id FROM #__phocacart_specifications AS s2'
                                . I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's2')

                                . ' WHERE ' . implode(' OR ', $condition)
                                . ' GROUP BY s2.product_id'
                                . ' HAVING COUNT(s2.alias) >= 1' /* TEST: . $c */
                                . ')';
                        }
                    }
                    break;

            }
        }


        return [
            'where' => $where,
            'left'  => $join,
        ];
    }

    /*
     * params ... search option parameters
     */
	public static function getSqlParts($valueType, $searchArea, $value, $searchParams = [], $prefix = ''): array
    {
        switch($valueType) {
            case 'int': return self::getSqlPartsInt($searchArea, $value, $searchParams, $prefix);
            case 'array': return self::getSqlPartsArray($searchArea, $value, $searchParams);
            case 'string': default: return self::getSqlPartsString($value, $searchParams);
        }
	}

}
