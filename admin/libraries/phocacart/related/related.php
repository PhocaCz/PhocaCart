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
use Joomla\Database\DatabaseInterface;
use Phoca\PhocaCart\ContentType\ContentTypeHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartRelated
{
	public static function storeRelatedItemsById($relatedString, $productId)
    {
    	if ((int)$productId > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_product_related'
					. ' WHERE product_a = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();

			if (isset($relatedString) && $relatedString != '')
            {
				$relatedArray 	= explode(",", $relatedString);
				$values 		= array();

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

    public static function copyRelatedItems(int $sourceProductId, int $destProductId): void
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = 'INSERT INTO #__phocacart_product_related(related_type, product_a, product_b)'
            . 'SELECT related_type, ' . $destProductId . ', product_b FROM #__phocacart_product_related WHERE product_a = ' . $sourceProductId;
        $db->setQuery($query);
        $db->execute();
    }

    public static function storeRelatedItems(int $productId, ?array $related): void
    {
        if (!$productId) {
            return;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = 'DELETE FROM #__phocacart_product_related WHERE product_a = '. $productId;
        $db->setQuery($query);
        $db->execute();

        if ($related) {
            $values = [];
            // Make numeric indexes
            $related = array_values($related);
            $related = array_unique($related, SORT_REGULAR);
            foreach ($related as $index => $relatedItem) {
                $values[] = '(' . $productId . ', ' . $relatedItem['id'] . ', ' . $relatedItem['related_type'] . ', ' . $index . ')';
            }

            $query = 'INSERT INTO #__phocacart_product_related (product_a, product_b, related_type, ordering)'
                .' VALUES ' . implode(', ', $values);
            $db->setQuery($query);
            $db->execute();
        }
    }

	/*
	* Try to find the best menu link so we search for category which we are located
	* if we find the category, we use this, if not we use another if accessible, etc.
	*/

	public static function getRelatedItemsById($productId, $select = 0, $frontend = 0)
    {
        $db         = Factory::getDBO();
        $wheres     = [];
        $wheres[]   = 't.product_a = ' . (int)$productId;
        $catid      = 0;
        $params 	= PhocacartUtils::getComponentParameters();

        // FRONTEND
        $skip = [];
        $skip['access']         = $params->get('sql_product_skip_access', 0);
        $skip['group']          = $params->get('sql_product_skip_group', 0);
        $skip['category_type']  = $params->get('sql_product_skip_category_type', 0);

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

            $limitType = [];
            $contentTypes = ContentTypeHelper::getContentTypes(ContentTypeHelper::ProductRelated);
            foreach ($contentTypes as $contentType) {
                if ($contentType->params->get('display_in_product', 1)) {
                    $limitType[] = $contentType->id;
                }
            }

            if (!$limitType) {
                return [];
            }
            $wheres[] = ' t.related_type in (' . implode(', ', $limitType) . ')';

            $catid = PhocacartCategoryMultiple::getCurrentCategoryId();
        }

        if ($select == 1) {
            $query = ' SELECT t.product_b';
        } else if ($select == 2) {
            $query = ' SELECT a.id, a.alias';
        } else if ($select == 3) {
            $query = ' SELECT DISTINCT a.id as id, a.title as title, a.image as image, a.alias as alias, a.description, a.description_long, t.related_type';
        } else {
            // FULL GROUP BY ISSUE
            // We have three issues with MySQL (zero with MariaDB)
            // - 1. ONLY_FULL_GROUP_BY (now disabled in Joomla) - it lists duplicity items
            //      This we can solve with group_concat (but MySQL has not LIMIT option inside group_concat)
            // - 2. no LIMIT in group_concat
            //      So we can use substring_index because of MySQL (but on some servers there can be memory problems)
            // - 3. substring_index memory limit (3 was hack for 2 in mysql)
            // So because ONLY_FULL_GROUP_BY is disabled for Joomla 4, we break the first rule for now (see GROUP_BY below - no info about c.)

            //$query = ' SELECT a.id as id, a.title as title, a.image as image, a.alias as alias,'
            //		.' c.id as catid, c.alias as catalias, c.title as cattitle';
            $query = ' SELECT DISTINCT a.id as id, a.image as image, t.related_type, ';
            $query .= I18nHelper::sqlCoalesce(['title', 'alias', 'description', 'description_long']) . ',';

            // (1)RANDOM CATEGORY
            /*$query  .= ' GROUP_CONCAT(c.id ORDER BY c.parent_id, c.ordering DESC LIMIT 1) as catid,'
                . ' GROUP_CONCAT(c.title ORDER BY c.parent_id, c.ordering DESC LIMIT 1) as cattitle,'
                . ' GROUP_CONCAT(c.alias ORDER BY c.parent_id, c.ordering DESC LIMIT 1) as catalias,';*/
            /*
            $query  .= ' SUBSTRING_INDEX(GROUP_CONCAT(c.id ORDER BY c.parent_id ASC), \',\', 1) as catid,'
                    . ' SUBSTRING_INDEX(GROUP_CONCAT(c.title ORDER BY c.parent_id ASC), \',\', 1) as cattitle,'
                    . ' SUBSTRING_INDEX(GROUP_CONCAT(c.alias ORDER BY c.parent_id ASC), \',\', 1) as catalias,';*/
            /*
            $query .= ' (SELECT c.id FROM jos_phocacart_product_categories AS pc'
             .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id AND WHEN c. LIMIT 1) AS catid, '
            .' (SELECT c.title FROM jos_phocacart_product_categories AS pc'
            .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id LIMIT 1) AS cattitle, '
            .' (SELECT c.alias FROM jos_phocacart_product_categories AS pc'
            .' LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id WHERE a.id = pc.product_id LIMIT 1) AS catalias';*/

            $query  .= ' c.id as catid, ';
            $query .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat') . ', ';

            // (2)PREFERRED CATEGORY (catid set)
            // FULL GROUP BY ISSUE
            //$query .= ' GROUP_CONCAT(cp.id) AS catid_pref, GROUP_CONCAT(cp.alias) AS catalias_pref, GROUP_CONCAT(cp.title) AS cattitle_pref';
            $query .= ' cp.id AS catid_pref, ';
            $query .= I18nHelper::sqlCoalesce(['title'], 'cp', '', '', '', '', true) . ' AS cattitle_pref, ';
            $query .= I18nHelper::sqlCoalesce(['alias'], 'cp', '', '', '', '', true) . ' AS catalias_pref ';
        }

        if ((int)$catid > 0) {
            // (3)SELECTED CATEGORY (displayed category in frontend)
            // FULL GROUP BY ISSUE
            //$query .= ' GROUP_CONCAT(cs.id) AS catid_sel, GROUP_CONCAT(cs.alias) AS catalias_sel, GROUP_CONCAT(cs.title) AS cattitle_sel';
            $query .= ', cs.id AS catid_sel, ';
            $query .= I18nHelper::sqlCoalesce(['title'], 'cs', '', '', '', '', true) . ' AS cattitle_sel, ';
            $query .= I18nHelper::sqlCoalesce(['alias'], 'cs', '', '', '', '', true) . ' AS catalias_sel ';
        }

        if (!$frontend) {
            $query .= ',';
            $query .= ' GROUP_CONCAT(c.title SEPARATOR ", ") AS categories_title';
        }

        $query .= ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_related AS t ON a.id = t.product_b'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';

        if ($select != 1 && $select != 2 && $select != 3) {
            // (2)
            $query .= ' LEFT JOIN #__phocacart_categories AS cp ON cp.id = pc.category_id and pc.category_id = a.catid';
            $query .= I18nHelper::sqlJoin('#__phocacart_products_i18n');
            $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');
            $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'cp');
        }

        if ((int)$catid > 0) {
            // (3)
            $query .= ' LEFT JOIN #__phocacart_categories AS cs ON cs.id = pc.category_id and pc.category_id = ' . (int)$catid;
            $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'cs');
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
		}  else if ($select == 3) {
			$query .= ' GROUP BY a.id, a.alias';
		} else {
			// FULL GROUP BY ISSUE - BE AWARE: not ready for ONLY_FULL_GROUP_BY because of above mentioned issues in MySQL
            //$query .= ' GROUP BY a.id, a.title, a.alias, a.image, a.description, a.description_long, c.id, c.alias, c.title';// Not used for now because of limits and problems in MySQL
            //$query .= ' GROUP BY a.id, a.title, a.alias, a.image, a.description, a.description_long';
            $query .= ' GROUP BY a.id';
		}

        $query .= ' ORDER BY t.ordering';

        //$query .= ' ORDER BY a.id';

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
