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

class PhocacartRelated
{
	public static function storeRelatedItemsById($relatedString, $productId) {


		if ((int)$productId > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_product_related'
					. ' WHERE product_a = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();

			if (isset($relatedString) && $relatedString != '') {

				$relatedArray 	= explode(",", $relatedString);
				$values 		= array();
				$valuesString 	= '';

				foreach($relatedArray as $k => $v) {
					$values[] = ' ('.(int)$productId.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_product_related (product_a, product_b)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/*
	* Try to find the best menu link so we search for category which we are located
	* if we find the category, we use this, if not we use another if accessible, etc.
	*/

	public static function getRelatedItemsById($productId, $select = 0, $frontend = 0)
    {

        $db = Factory::getDBO();
        $wheres = array();
        $wheres[] = 't.product_a = ' . (int)$productId;
        $catid = 0;
        $params 	= PhocacartUtils::getComponentParameters();

        // FRONTEND
        $skip = array();
        $skip['access'] = $params->get('sql_product_skip_access', 0);
        $skip['group'] = $params->get('sql_product_skip_group', 0);
        //$skip['attributes']	    = $params->get('sql_product_skip_attributes', 0);
        $skip['category_type'] = $params->get('sql_product_skip_category_type', 0);
        //$skip['tax'] = $params->get('sql_product_skip_tax', 0);

        if ($frontend) {
            $user = PhocacartUser::getUser();
            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));


            if (!$skip['category_type']) {
                $wheres[] = " c.type IN (0,1)";// Related are displayed only in online shop (0 - all, 1 - online shop, 2 - pos)
            }

            if (!$skip['access']) {
                $wheres[] = " c.access IN (" . $userLevels . ")";
                $wheres[] = " a.access IN (" . $userLevels . ")";
            }

            if (!$skip['group']) {
                $wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
                $wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
            }

            $wheres[] = " c.published = 1";
            $wheres[] = " a.published = 1";

            $catid = PhocacartCategoryMultiple::getCurrentCategoryId();

        }

        if ($select == 1) {
            $query = ' SELECT t.product_b';
        } else if ($select == 2) {
            $query = ' SELECT a.id, a.alias';
        } else {
            // FULL GROUP BY ISSUE
            //$query = ' SELECT a.id as id, a.title as title, a.image as image, a.alias as alias,'
            //		.' c.id as catid, c.alias as catalias, c.title as cattitle';
            $query = ' SELECT DISTINCT a.id as id, a.title as title, a.image as image, a.alias as alias, a.description, a.description_long,'
                . ' SUBSTRING_INDEX(GROUP_CONCAT(c.id ORDER BY c.parent_id), \',\', 1) as catid,'
                . ' SUBSTRING_INDEX(GROUP_CONCAT(c.title ORDER BY c.parent_id), \',\', 1) as cattitle,'
                . ' SUBSTRING_INDEX(GROUP_CONCAT(c.alias ORDER BY c.parent_id), \',\', 1) as catalias';

            /*.' (SELECT c.id FROM jos_phocacart_product_categories AS pc'
             .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id LIMIT 1) AS catid, '
            .' (SELECT c.title FROM jos_phocacart_product_categories AS pc'
            .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id LIMIT 1) AS cattitle, '
            .' (SELECT c.alias FROM jos_phocacart_product_categories AS pc'
            .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id LIMIT 1) AS catalias';*/

        }
        if ((int)$catid > 0) {
            $query .= ', ';
            $query .= ' GROUP_CONCAT(c2.id) AS catid2, GROUP_CONCAT(c2.alias) AS catalias2, GROUP_CONCAT(c2.title) AS cattitle2';
        }

        if (!$frontend) {
            $query .= ', ';
            $query .= ' GROUP_CONCAT(c.title SEPARATOR " ") AS categories_title';
        }

        $query .= ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_related AS t ON a.id = t.product_b'
            //.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
        if ((int)$catid > 0) {
            $query .= ' LEFT JOIN #__phocacart_categories AS c2 ON c2.id = pc.category_id and pc.category_id = ' . (int)$catid;
        }


        if ($frontend && !$skip['group']) {
            $query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                    . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
        }


        $query .= ' WHERE ' . implode( ' AND ', $wheres );

		if ($select == 1) {
			$query .= ' GROUP BY a.id, t.product_b';
		} else if ($select == 2) {
			$query .= ' GROUP BY a.id, a.alias';
		}else {
			// FULL GROUP BY ISSUE
			//$query .= ' GROUP BY a.id, a.title, a.image, a.alias, c.id, c.alias, c.title';
			$query .= ' GROUP BY a.id, a.title, a.alias, a.image, a.description, a.description_long';
		}

		$db->setQuery($query);

		if ($select == 1) {
			$related = $db->loadColumn();
		} else {
			$related = $db->loadObjectList();
		}

		return $related;
	}

	public static function correctProductId($productIdChange) {
		$db 		= Factory::getDBO();
		if (!empty($productIdChange)) {
			foreach($productIdChange as $new => $old) {
				if ($new == $old) {
					continue;
				}
				$q = 'UPDATE #__phocacart_product_related SET product_b = '.(int)$new.' WHERE product_b = '.(int)$old;
				$db->setQuery($q);
				$db->execute();
			}
		}
		return true;
	}
}
