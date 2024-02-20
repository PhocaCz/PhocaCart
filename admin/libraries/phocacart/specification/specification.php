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
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartSpecification
{
	public static function getSpecificationsById($productId, $return = 0) {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.title, a.alias, a.value, a.alias_value, a.group_id, a.image, a.image_medium, a.image_small, a.color'
				.' FROM #__phocacart_specifications AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.ordering';
		$db->setQuery($query);

		if ($return == 0) {
			return $db->loadObjectList();
		} else if ($return == 1) {
			return $db->loadAssocList();
		} else {
		    $specifications          = $db->loadAssocList();
		    $specificationsSubform   = array();
		    $i              = 0;
		    if (!empty($specifications)) {
				foreach($specifications as $k => $v) {

				    $specificationsSubform['specifications'.$i]['id'] = (int)$v['id'];
				    $specificationsSubform['specifications'.$i]['title'] = (string)$v['title'];
				    $specificationsSubform['specifications'.$i]['alias'] = (string)$v['alias'];
				    $specificationsSubform['specifications'.$i]['value'] = (string)$v['value'];
				    $specificationsSubform['specifications'.$i]['alias_value'] = (string)$v['alias_value'];
				    $specificationsSubform['specifications'.$i]['group_id'] = (int)$v['group_id'];
				    $specificationsSubform['specifications'.$i]['image'] = (string)$v['image'];
				    $specificationsSubform['specifications'.$i]['image_medium'] = (string)$v['image_medium'];
				    $specificationsSubform['specifications'.$i]['image_small'] = (string)$v['image_small'];
				    $specificationsSubform['specifications'.$i]['color'] = (string)$v['color'];
					$i++;
				}
			}
		    return $specifications;
        }

		return false;
	}

	public static function getGroupArray() {

		$db = Factory::getDBO();

		$query = 'SELECT id, title'
				.' FROM #__phocacart_specification_groups'
			    .' ORDER by ordering';
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$groupsA = array();
		if (!empty($groups)) {
			foreach($groups as $k => $v) {
				$groupsA[$v->id] = $v->title;
			}
		}

		return $groupsA;
	}


	public static function storeSpecificationsById($productId, $specsArray, $new = 0) {
		if ((int)$productId > 0) {
			$db =Factory::getDBO();

			$notDeleteSpecs = [];

			if (!empty($specsArray)) {
				$i = 1;
				foreach($specsArray as $specification) {
					// Don't store empty specification
					if ($specification['title'] == '') {
						continue;
					}

					$i18nData = I18nHelper::prepareI18nData($specification, ['title', 'alias', 'value', 'alias_value']);

					if(empty($specification['alias'])) {
						$specification['alias'] = $specification['title'];
					}
					$specification['alias'] = PhocacartUtils::getAliasName($specification['alias']);

					if(empty($specification['alias_value'])) {
						$specification['alias_value'] = $specification['value'];
					}

					// When no value, then no alias
					if ($specification['alias_value']) {
						$specification['alias_value'] = PhocacartUtils::getAliasName($specification['alias_value']);
					}

					$specification = PhocacartUtils::arrayDefValues($specification, [
						'group_id' => 0,
						'value' => '',
						'alias_value' => '',
						'image' => '',
						'image_medium' => '',
						'image_small' => '',
						'color' => '',
					]);

					$idExists = 0;
					if ($new == 0) {
						if (isset($specification['id']) && $specification['id'] > 0) {
							// Does the row exist
							$query = ' SELECT id FROM #__phocacart_specifications WHERE id = '. (int)$specification['id'];
							$db->setQuery($query);
							$idExists = $db->loadResult();
						}
					}

					if ($idExists) {
						$query = 'UPDATE #__phocacart_specifications SET'
						.' product_id = '.(int)$productId.','
						.' title = '.$db->quote($specification['title']).','
						.' alias = '.$db->quote($specification['alias']).','
						.' value = '.$db->quote($specification['value']).','
						.' alias_value = '.$db->quote($specification['alias_value']).','
						.' group_id = '.(int)$specification['group_id'].','
                        .' image = '.$db->quote($specification['image']).','
                        .' image_medium = '.$db->quote($specification['image_medium']).','
                        .' image_small = '.$db->quote($specification['image_small']).','
                        .' color = '.$db->quote($specification['color']).','
						.' ordering = '.$i
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();
						$i++;

						$newIdS = $idExists;
					} else {
						$date             = gmdate('Y-m-d H:i:s');// not null

						$values 	= '('.(int)$productId.', '.$db->quote($specification['title']).', '.$db->quote($specification['alias']).', '.$db->quote($specification['value']).', '.$db->quote($specification['alias_value']).', '.(int)$specification['group_id'].', '.$db->quote($specification['image']).', '.$db->quote($specification['image_medium']).', '.$db->quote($specification['image_small']).', '.$db->quote($specification['color']).', '.$db->quote($date).', '.$i.')';

						$query = ' INSERT INTO #__phocacart_specifications (product_id, title, alias, value, alias_value, group_id, image, image_medium, image_small, color, date, ordering)'
								.' VALUES '.$values;
						$db->setQuery($query);
						$db->execute();
						$i++;

						$newIdS = $db->insertid();
					}

					I18nHelper::saveI18nData($newIdS, $i18nData, '#__phocacart_specifications_i18n');
					$notDeleteSpecs[]	= $newIdS;
				}
			}

			// Remove all specifications except the active
			if (I18nHelper::isI18n()) {
				$query = ' DELETE s, i18n FROM #__phocacart_specifications s '
					. ' JOIN #__phocacart_specifications_i18n i18n ON i18n.id = s.id';
			} else {
				$query = ' DELETE s FROM #__phocacart_specifications s';
			}
			$query .= ' WHERE s.product_id = '. (int)$productId;
			if ($notDeleteSpecs) {
				$query .= ' AND s.id NOT IN (' . implode(',', $notDeleteSpecs) . ')';
			}
			$db->setQuery($query);
			$db->execute();

		}
	}


	/*
	public static function storeSpecificationsById($productId, $specsArray) {

		if ((int)$productId > 0) {
			$db =Factory::getDBO();

			$query = ' DELETE '
					.' FROM #__phocacart_specifications'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();

			if (!empty($specsArray)) {
				$values 	= array();
				foreach($specsArray as $k => $v) {

					// Don't store empty specification
					if ($v['title'] == '') {
						continue;
					}

					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);

					if(empty($v['alias_value'])) {
						$v['alias_value'] = $v['value'];
					}

					// When no value, then no alias
					if ($v['alias_value'] != '') {
						$v['alias_value'] = PhocacartUtils::getAliasName($v['alias_value']);
					}

					if(empty($v['group_id'])) {
						$v['group_id'] = 0;
					}

					// correct simple xml
					if (empty($v['title'])) 		{$v['title'] 			= '';}
					if (empty($v['alias'])) 		{$v['alias'] 			= '';}
					if (empty($v['value'])) 		{$v['value'] 			= '';}
					if (empty($v['alias_value'])) 	{$v['alias_value'] 		= '';}
					if (empty($v['group_id'])) 		{$v['group_id'] 		= '';}

					$values[] 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.$db->quote($v['value']).', '.$db->quote($v['alias_value']).', '.(int)$v['group_id'].')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);
					$query = ' INSERT INTO #__phocacart_specifications (product_id, title, alias, value, alias_value, group_id)'
							.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	*/

	public static function getSpecificationGroupsAndSpecifications($productId) {
		$db = Factory::getDBO();

		/*if (I18nHelper::useI18n()) {
			$columns = 's.id, coalesce(i18n_s.title, s.title) as title, coalesce(i18n_s.alias, s.alias) as alias, coalesce(i18n_s.value, s.value) as value, coalesce(i18n_s.alias_value, s.alias_value) as alias_value, '
				. 's.image, s.image_medium, s.image_small, s.color, g.id as groupid, coalesce(i18n_g.title, g.title) as grouptitle';
		} else {
			$columns = 's.id, s.title, s.alias, s.value, s.alias_value, s.image, s.image_medium, s.image_small, s.color, g.id as groupid, g.title as grouptitle';
		}*/

		$columns = 's.id, '.I18nHelper::sqlCoalesce(['title', 'alias', 'value', 'alias_value'], 's').', '
				. 's.image, s.image_medium, s.image_small, s.color, g.id as groupid, '.I18nHelper::sqlCoalesce(['title'], 'g', 'group');

		$query = 'SELECT ' . $columns
				.' FROM #__phocacart_specifications AS s'
				.' LEFT JOIN #__phocacart_specification_groups AS g ON g.id = s.group_id'
				. I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's')
				. I18nHelper::sqlJoin('#__phocacart_specification_groups_i18n', 'g')
				.' WHERE s.product_id = '.(int)$productId
			    .' ORDER by g.ordering';
		$db->setQuery($query);
		$specs = $db->loadObjectList();

		$specsA = array();
		if (!empty($specs)){
			foreach ($specs as $k => $v) {
				$specsA[$v->groupid][0] = $v->grouptitle;
				$specsA[$v->groupid][$v->id]['title'] = $v->title;
				$specsA[$v->groupid][$v->id]['value'] = $v->value;
			}
		}
		return $specsA;

	}

	public static function getAllSpecificationsAndValues($ordering = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = array()) {

		$db 			= Factory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 6);

		$columns		= 's.id, s.image, s.image_medium, s.image_small, s.color';
		//$groupsFull		= $columns;

		/*if (I18nHelper::useI18n()) {
			$groupsFull = $columns . ', coalesce(i18n_s.title, s.title), coalesce(i18n_s.alias, s.alias), coalesce(i18n_s.value, s.value), coalesce(i18n_s.alias_value, s.alias_value)';
            $columns .= ', coalesce(i18n_s.title, s.title) as title, coalesce(i18n_s.alias, s.alias) as  alias, coalesce(i18n_s.value, s.value) as value, coalesce(i18n_s.alias_value, s.alias_value) as alias_value';
        } else {
            $columns   .= ', s.title, s.alias, s.value, s.alias_value';
			$groupsFull = $columns;
        }*/

		$groupsFull = $columns;
		$columns .= I18nHelper::sqlCoalesce(['title', 'alias', 'value', 'alias_value'], 's', '', '', ',');


		$groupsFast		= 's.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

		$wheres		= array();
		$lefts		= array();

		$wheres[]	= ' sg.published = 1';
		$lefts[] 	= ' #__phocacart_specification_groups AS sg ON s.group_id = sg.id';

		$productTableAdded = 0;

		if ($onlyAvailableProducts == 1) {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' #__phocacart_products AS p ON s.product_id = p.id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$productTableAdded = 1;
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
		} else {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);

			}
			$lefts[] = ' #__phocacart_products AS p ON s.product_id = p.id';
			$productTableAdded = 1;
		}

		if (!empty($filterProducts)) {
			$productIds = implode (',', $filterProducts);
			$wheres[]	= 'p.id IN ('.$productIds.')';
			if ($productTableAdded == 0) {
                $lefts[] = ' #__phocacart_products AS p ON s.product_id = p.id';
            }
		}

		$query = 'SELECT '.$columns
				.' FROM  #__phocacart_specifications AS s'
				. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
				. I18nHelper::sqlJoin('#__phocacart_specification_groups_i18n', 'sg')
				. I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's')
				. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
				.' GROUP BY '.$groups
				.' ORDER BY '.$orderingText;

		$db->setQuery($query);
		$specifications = $db->loadObjectList();



		$a	= array();
		if (!empty($specifications)) {
			foreach($specifications as $k => $v) {
				if (isset($v->title) && $v->title != '' && isset($v->id) && $v->id != '' && isset($v->alias) && $v->alias != '') {
					$a[$v->alias]['title']				= $v->title;
					$a[$v->alias]['id']					= $v->id;
					$a[$v->alias]['alias']				= $v->alias;
					if (isset($v->value) && $v->value != '' && isset($v->alias_value) && $v->alias_value != '') {
						$a[$v->alias]['value'][$v->alias_value] = new stdClass();
						$a[$v->alias]['value'][$v->alias_value]->title	= $v->value;
						$a[$v->alias]['value'][$v->alias_value]->id		= $v->id;
						$a[$v->alias]['value'][$v->alias_value]->alias	= $v->alias_value;

						$a[$v->alias]['value'][$v->alias_value]->image			= $v->image;
						$a[$v->alias]['value'][$v->alias_value]->image_medium	= $v->image_medium;
						$a[$v->alias]['value'][$v->alias_value]->image_small	= $v->image_small;
						$a[$v->alias]['value'][$v->alias_value]->color			= $v->color;
					} else {
						$a[$v->alias]['value'] = array();
					}
				}
			}

		}
		return $a;

	}

    public static function getActiveSpecificationValues($items, $ordering) {

		$db     = Factory::getDbo();
	    $o      = array();
        $wheres = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 6);//s

        if (!empty($items)) {
            foreach ($items as $k => $v) {

				$wheres[] = '( '.I18nHelper::sqlCoalesce(['alias'], 's', '', '', '', '', true).' = ' . $db->quote($k) . ' AND '.I18nHelper::sqlCoalesce(['alias_value'], 's', '', '', '', '', true).' IN (' . $v . ') )';

            }
            if (!empty($wheres)) {
                // FULL GROUP BY GROUP_CONCAT(DISTINCT o.title) AS title
             /*   $q = 'SELECT DISTINCT CONCAT(s.title, \'(\', s.alias_value, \')\') as title, s.alias, s.alias_value, CONCAT(\'s[\', s.alias, \']\')  AS parameteralias, p.title AS parametertitle FROM #__phocacart_specifications AS s'
                    . ' LEFT JOIN #__phocacart_specification_groups AS p ON p.id = s.group_id'

                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY p.alias, s.alias, s.title'
                    . ' ORDER BY ' . $ordering;

				$q = 'SELECT DISTINCT CONCAT(COALESCE(i18n_s.title, s.title), \'(\', COALESCE(i18n_s.alias_value, s.alias_value), \')\') as title, COALESCE(i18n_s.alias, s.alias) as alias, COALESCE(i18n_s.alias_value, s.alias_value) as alias_value, CONCAT(\'s[\', COALESCE(i18n_s.alias, s.alias), \']\')  AS parameteralias, COALESCE(i18n_p.title, p.title) AS parametertitle FROM #__phocacart_specifications AS s'
                    . ' LEFT JOIN #__phocacart_specification_groups AS p ON p.id = s.group_id'

					. I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's')
					. I18nHelper::sqlJoin('#__phocacart_specification_groups_i18n', 'p')
                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY p.alias, s.alias, s.title'
                    . ' ORDER BY ' . $ordering;
*/

				$q = 'SELECT DISTINCT CONCAT('.I18nHelper::sqlCoalesce(['title'], 's', '', '', '', '', true).', \'(\', '.I18nHelper::sqlCoalesce(['alias_value'], 's', '', '', '', '', true).', \')\') as title,'
					.I18nHelper::sqlCoalesce(['alias'], 's')
					.I18nHelper::sqlCoalesce(['alias_value'], 's', '', '', ',')
					.I18nHelper::sqlCoalesce(['alias'], 's', 'parameter', 'concatparameters', ',')
					.I18nHelper::sqlCoalesce(['title'], 'p', 'parameter', '', ',')
					. ' FROM #__phocacart_specifications AS s'
                    . ' LEFT JOIN #__phocacart_specification_groups AS p ON p.id = s.group_id '
					. I18nHelper::sqlJoin('#__phocacart_specifications_i18n', 's')
					. I18nHelper::sqlJoin('#__phocacart_specification_groups_i18n', 'p')
                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY p.alias, s.alias, s.title'
                    . ' ORDER BY ' . $ordering;

                $db->setQuery($q);
                $o = $db->loadAssocList();
            }
        }
        return $o;
	}
}
?>
