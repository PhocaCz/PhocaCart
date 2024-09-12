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

namespace Phoca\PhocaCart\Product;

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class Bundled
{
    public const SELECT_COMPLETE_WITH_CATEGORY = 0;
    public const SELECT_ID = 1;
    public const SELECT_ID_ALIAS = 2;
    public const SELECT_COMPLETE = 3;

    /**
     * Stores child product of Bundle product
     *
     * @param string|null $relatedString - comma separated child product IDs
     * @param int $productId - main product ID
     * @return void
     */
    public static function storeBundledItemsById(?string $relatedString, int $productId): void
    {
	    if ($productId) {
			$db = Factory::getDBO();
			$db->setQuery('DELETE FROM #__phocacart_product_bundles WHERE main_product_id = '. (int)$productId);
			$db->execute();

			if ($relatedString) {
				$relatedArray 	= explode(",", $relatedString);
				$values 		= [];

				foreach($relatedArray as $v) {
					$values[] = ' ('. $productId.', ' . (int)$v . ')';
				}

				$valuesString = implode(',', $values);
				$query = ' INSERT INTO #__phocacart_product_bundles (main_product_id, child_product_id)'
				    . ' VALUES ' . $valuesString;
                $db->setQuery($query);
                $db->execute();
			}
		}
	}

	/*
	* Try to find the best menu link so we search for category which we are located
	* if we find the category, we use this, if not we use another if accessible, etc.
	*/

	public static function getBundledItemsById($productId, int $selectType = self::SELECT_COMPLETE_WITH_CATEGORY, bool $frontend = false)
    {

        $db         = Factory::getDBO();
        $wheres     = array();
        $wheres[]   = 't.main_product_id = ' . (int)$productId;
        $catid      = 0;
        $params 	= \PhocacartUtils::getComponentParameters();

        // FRONTEND
        $skip = array();
        $skip['access']         = $params->get('sql_product_skip_access', 0);
        $skip['group']          = $params->get('sql_product_skip_group', 0);
        $skip['category_type']  = $params->get('sql_product_skip_category_type', 0);

        if ($frontend) {
            $user = \PhocacartUser::getUser();
            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', \PhocacartGroup::getGroupsById($user->id, 1, 1));

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

            $catid = \PhocacartCategoryMultiple::getCurrentCategoryId();
        }

        if ($selectType == self::SELECT_ID) {
            $query = ' SELECT t.product_b';
        } else if ($selectType == self::SELECT_ID_ALIAS) {
            $query = ' SELECT a.id, a.alias';
        } else if ($selectType == self::SELECT_COMPLETE) {
            $query = ' SELECT DISTINCT a.id as id, a.title as title, a.image as image, a.alias as alias, a.description, a.description_long';
        } else {
            $query = ' SELECT DISTINCT a.id as id, a.title as title, a.image as image, a.alias as alias, a.description, a.description_long,';
            $query  .= ' c.id as catid, c.title as cattitle, c.alias as catalias,';
            $query .= ' cp.id AS catid_pref, cp.alias AS catalias_pref, cp.title AS cattitle_pref';
        }

        if ((int)$catid > 0) {
            $query .= ',';
            $query .= ' cs.id AS catid_sel, cs.alias AS catalias_sel, cs.title AS cattitle_sel';
        }

        if (!$frontend) {
            $query .= ',';
            $query .= ' GROUP_CONCAT(c.title SEPARATOR ", ") AS categories_title';
        }

        $query .= ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_product_bundles AS t ON a.id = t.child_product_id'
            . ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
            . ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';

        if ($selectType ==  self::SELECT_COMPLETE_WITH_CATEGORY) {
            $query .= ' LEFT JOIN #__phocacart_categories AS cp ON cp.id = pc.category_id and pc.category_id = a.catid';
        }

        if ((int)$catid > 0) {
            $query .= ' LEFT JOIN #__phocacart_categories AS cs ON cs.id = pc.category_id and pc.category_id = ' . (int)$catid;
        }

        if ($frontend && !$skip['group']) {
            $query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
                    . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
        }


        $query .= ' WHERE ' . implode( ' AND ', $wheres );

		if ($selectType == self::SELECT_ID) {
			$query .= ' GROUP BY a.id, t.product_b';
		} else if ($selectType == self::SELECT_ID_ALIAS) {
			$query .= ' GROUP BY a.id, a.alias';
		}  else if ($selectType == self::SELECT_COMPLETE) {
			$query .= ' GROUP BY a.id, a.alias';
		} else {
            $query .= ' GROUP BY a.id';
		}

		$db->setQuery($query);

		if ($selectType == self::SELECT_ID) {
			$bundles = $db->loadColumn();
		} else {
            $bundles = $db->loadObjectList();
		}

		return $bundles;
	}

	public static function correctProductId(array $productIdChange): void {
		if ($productIdChange) {
            $db 		= Factory::getDBO();

			foreach($productIdChange as $new => $old) {
				if ($new == $old) {
					continue;
				}

				$q = 'UPDATE #__phocacart_product_bundles SET child_product_id = ' . (int)$new . ' WHERE child_product_id = '.(int)$old;
				$db->setQuery($q);
				$db->execute();
			}
		}
	}
}
