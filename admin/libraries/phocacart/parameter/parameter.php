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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

class PhocacartParameter
{

	private static $parameter			= array();
	/*
	 * PARAMETERS (group for each parameter values)
	 */
	public static function getAllParametersSelectBox($name, $id, $active, $attr = 'class="form-select"', $order = 'id', $selectText = 0 ) {

		$db = Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_parameters AS a'
				//.' WHERE a.type ='.(int)$type
				.' ORDER BY '. $order;
		$db->setQuery($query);
		$parameters = $db->loadObjectList();

		//if (!empty($parameters) && $selectText == 1) {
		if ($selectText == 1) {
			array_unshift($parameters, HTMLHelper::_('select.option', '', '- ' . Text::_('COM_PHOCACART_SELECT_PARAMETER') . ' -', 'value', 'text'));
		}

		$paramsO = HTMLHelper::_('select.genericlist', $parameters, $name, $attr, 'value', 'text', $active, $id);

		return $paramsO;
	}


	public static function getAllParameters($key = 'id', $ordering = 1, $lang = '') {


		$keyP = base64_encode(serialize($key) . ':' . serialize((int)$ordering . ':' . $lang ));

		if( !array_key_exists( (string)$keyP, self::$parameter )) {


			$db = Factory::getDBO();
			$orderingText = PhocacartOrdering::getOrderingText($ordering, 12);

			$wheres = array();
			$lefts = array();

			$columns = 'pp.id, pp.title, pp.title_header, pp.image, pp.alias, pp.description, pp.limit_count, pp.link_type';
			/*$groupsFull		= $columns;
			$groupsFast		= 'm.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/

			$wheres[] = ' pp.published = 1';

			if ($lang != '' && $lang != '*') {

				$wheres[] = PhocacartUtilsSettings::getLangQuery('pp.language', $lang);
			}


			$q = ' SELECT DISTINCT ' . $columns
				. ' FROM  #__phocacart_parameters AS pp'
				. (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
				. (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
				//.' GROUP BY '.$groups
				. ' ORDER BY ' . $orderingText;

			$db->setQuery($q);


			try {
				$items = $db->loadObjectList($key);
			} catch (Exception $e) {
				Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
				return;
			}
			/*
			try {
				$items = $db->loadObjectList();
			} catch (RuntimeException $e) {
				throw new Exception($e->getMessage(), 500, $e);
				return false;
			}*/


			self::$parameter[$keyP] = $items;
		}

		return self::$parameter[$keyP];
	}


	/*
	 * PARAMETER VALUES (stored in products) Field PhocacartParameterValues
	 */
	public static function getParameterValues($itemId, $parameterId, $select = 0) {

		$db = Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT r.parameter_value_id';
		} else if ($select == 2){
			$query = 'SELECT a.id, a.alias ';
		} else {
			$query = 'SELECT a.id, a.title, a.alias, a.type, a.display_format';
		}
		$query .= ' FROM #__phocacart_parameter_values AS a'
				//.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
				.' LEFT JOIN #__phocacart_parameter_values_related AS r ON a.id = r.parameter_value_id'
				.' LEFT JOIN #__phocacart_parameters AS p ON a.parameter_id = p.id'
				.' WHERE r.item_id = '.(int) $itemId
				.' AND p.id = '.(int) $parameterId
                .' ORDER BY a.id';
		$db->setQuery($query);


		if ($select == 1) {
			$params = $db->loadColumn();
		} else {
			$params = $db->loadObjectList();
		}

		return $params;
	}

	/*
	 * PARAMETER VALUES (stored in submitted items) Field PhocacartParameterValuesSumbitItems
	 */
	public static function getParameterValuesSubmitItems($itemId, $parameterId, $select = 0) {

		$db = Factory::getDBO();

		$query = 'SELECT a.items_parameter';
		$query .= ' FROM #__phocacart_submit_items AS a'
				.' WHERE a.id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);
		$items = $db->loadResult();

		if (!empty($items)) {
			$itemsA = json_decode($items, true);

			if (isset($itemsA[$parameterId])){
				return $itemsA[$parameterId];
			}
		}

		return array();
	}

	/*
	 * We don't need to care about parameter_id here as parameter_value_id is unique indepentend to its parameter group
	 */

	public static function getParameterValuesByIds($cids) {

		$db = Factory::getDBO();
        if ($cids != '') {//cids is string separated by comma

            $query = 'SELECT pvr.parameter_value_id FROM #__phocacart_parameter_values AS a'
                //.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
                . ' LEFT JOIN #__phocacart_parameter_values_related AS pvr ON a.id = pvr.parameter_value_id'
                . ' AND pvr.item_id IN (' . $cids . ')'
                . ' ORDER BY a.id';

            $db->setQuery($query);
            $tags = $db->loadColumn();
            $tags = array_unique($tags);

            return $tags;
        }
        return array();
	}

	/*
	 * PARAMETER VALUES (stored in products) Field PhocacartParameterValues
	 */
	public static function getAllParameterValuesSelectBox($name, $id, $parameterId, $activeArray, $attributes = '', $order = 'a.id') {

		$db = Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_parameter_values AS a'
				.' LEFT JOIN #__phocacart_parameters AS p ON a.parameter_id = p.id'
				.' WHERE p.id = '.(int) $parameterId
				.' ORDER BY '. $order;


		$db->setQuery($query);
		$parameters = $db->loadObjectList();

		$parametersO = HTMLHelper::_('select.genericlist', $parameters, $name, $attributes, 'value', 'text', $activeArray, $id);

		return $parametersO;
	}

	/*
	 * PARAMETER VALUES (stored in products) Field PhocacartParameterValues
	 * administrator/components/com_phocacart/models/phocacartitem.php 1547 // Load Parameter Values for Parameters
	 */
	public static function getAllParameterValuesList($parameterId, $order = 'a.id') {

		$db = Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_parameter_values AS a'
				.' LEFT JOIN #__phocacart_parameters AS p ON a.parameter_id = p.id'
				.' WHERE p.id = '.(int) $parameterId
				.' ORDER BY '. $order;


		$db->setQuery($query);
		$parameters = $db->loadObjectList();

		return $parameters;
	}

	public static function storeParameterValues($parameterValuesArray, $itemId, $parameterId) {

		if ((int)$itemId > 0 && (int)$parameterId > 0) {
			$db = Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_parameter_values_related'
					.' WHERE item_id = '. (int)$itemId
                    .' AND parameter_id = '.(int)$parameterId;
			$db->setQuery($query);
			$db->execute();


			if (!empty($parameterValuesArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($parameterValuesArray as $k => $v) {
					$values[] = ' ('.(int)$itemId.', '.(int)$v.', '.(int)$parameterId.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_parameter_values_related (item_id, parameter_value_id, parameter_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();

				}
			}
		}

	}

	public static function getAllParameterValues($parameterId, $ordering = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = array() , $limitCount = -1) {

	/*	$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 12);

		$query = 'SELECT t.id, t.title, t.alias FROM #__phocacart_tags AS t WHERE t.published = 1 ORDER BY '.$orderingText;
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		return $tags;*/

        $wheres		= array();
        $lefts		= array();
		$related    = '#__phocacart_parameter_values_related';



		$db 			= Factory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 13);


		$columns		= 'pv.id, pv.title, pv.alias, pv.count_products';
		/*$groupsFull		= $columns;
		$groupsFast		= 'm.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/

		$wheres[]	= ' pp.published = 1';// Parameter (parameter group)
		$wheres[]	= ' pv.published = 1';// Parameter Value



		if ($onlyAvailableProducts == 1) {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' #__phocacart_parameter_values_related AS pr ON pr.parameter_value_id = pv.id';
			$lefts[] = ' #__phocacart_products AS p ON pr.item_id = p.id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);

		} else {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('pv.language', $lang);
				$lefts[] 	= ' #__phocacart_parameter_values_related AS pr ON pr.parameter_value_id = pv.id';
				$lefts[] 	= ' #__phocacart_products AS p ON pr.item_id = p.id';
			}
		}

		if (!empty($filterProducts)) {
			$productIds = implode (',', $filterProducts);
			$wheres[]	= 'p.id IN ('.$productIds.')';
		}

		if ($limitCount > -1) {
			$wheres[]	= 'pv.count_products > '.(int)$limitCount;
		}

		$lefts[] 	= ' #__phocacart_parameters AS pp ON pv.parameter_id = pp.id';
		$wheres[] 	= 'pp.id = '.(int)$parameterId;

		$q = ' SELECT DISTINCT '.$columns
			.' FROM  #__phocacart_parameter_values AS pv'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			//.' GROUP BY '.$groups
			.' ORDER BY '.$orderingText;

		$db->setQuery($q);


		$items = $db->loadObjectList();

		return $items;
	}

	public static function options($type = 0) {


		$db = Factory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_parameters AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		return $items;

	}

    public static function getActiveParameterValues($items, $ordering) {


		$db     = Factory::getDbo();
	    $o      = array();
        $wheres = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 13);//pv
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                $wheres[] = '( pp.alias = ' . $db->quote($k) . ' AND pv.id IN (' . $v . ') )';
            }

            if (!empty($wheres)) {
                // FULL GROUP BY GROUP_CONCAT(DISTINCT o.title) AS title
                $q = 'SELECT DISTINCT CONCAT(pv.id, \'-\', pv.alias) AS alias, pv.title, pp.alias  AS parameteralias, pp.title AS parametertitle FROM #__phocacart_parameter_values AS pv'
                    . ' LEFT JOIN #__phocacart_parameters AS pp ON pp.id = pv.parameter_id'
                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY pp.alias, pv.alias, pv.title'
                    . ' ORDER BY ' . $ordering;

                $db->setQuery($q);
                $o = $db->loadAssocList();
            }
        }
        return $o;
    }


	public static function getParametersRendered($itemId, $type = 0, $separator = '') {

	    if ($type == 0) {
			return false;
		}

	    $o 			= array();
	    $parameters = self::getAllParameters();
	    $s      	= PhocacartRenderStyle::getStyles();

	    if (!empty($parameters)) {
			foreach ($parameters as $k => $v) {
				if((int)$v->id) {


					$parameterValues = self::getParameterValues($itemId, (int)$v->id);
					if (!empty($parameterValues)) {
						$o[] = '<h3>'.$v->title.'</h3>';
						$o2 = array();
						foreach ($parameterValues as $k2 => $v2) {

							$title = '';
							if ($v->link_type == 1) {
								if ($v2->id > 0 && $v->alias != '' && $v2->alias != '') {
									$title = '<a href="'.Route::_(PhocacartRoute::getItemsRoute('', '', PhocacartText::filterValue($v->alias, 'alphanumeric'), PhocacartText::filterValue((int)$v2->id . '-' . $v2->alias, 'text'))).'">'.$v2->title.'</a>';
								}

							} else {
								$title = $v2->title;
							}

							$o2[$k2] = '<span class="'.$s['c']['label.label-info'] .'">'.$title.'</span>';
						}
						$o[] = implode($separator, $o2);
						$o[] = '<div class="ph-cb"></div>';
					}
				}
	    	}
	    }

	    return implode('', $o);
	}


}
?>
