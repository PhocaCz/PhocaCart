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

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Phoca\PhocaCart\I18n\I18nHelper;


abstract class PhocacartHelperAssociation
{

    private static function getJoomlaAssociations($id = 0, $view = null)
    {
        $input = Factory::getApplication()->input;
        $view  = $view === null ? $input->get('view') : $view;
        $id    = empty($id) ? $input->getInt('id') : $id;

        if (!$id) {
            return [];
        }

        if ($view === 'item') {
            $associations = Associations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', $id, 'id', 'alias', false);

            $return = array();

            foreach ($associations as $tag => $item) {
                $idA     = explode(":", $item->id);
                $idAlias = '';
                if (isset($idA[1])) {
                    $idAlias = $idA[1];
                }
                $id = (int) $item->id;

                $catidA = PhocacartCategoryMultiple::getCategories($id, 3);
                $catid  = 0;
                if (isset($catidA[0]->id)) {
                    $catid = $catidA[0]->id;
                }
                $catidAlias = '';
                if (isset($catidA[0]->alias)) {
                    $catidAlias = $catidA[0]->alias;
                }

                $return[$tag] = PhocacartRoute::getItemRoute((int) $id, (int) $catid, $idAlias, $catidAlias, array(0 => $tag));
            }

            return $return;
        }
        else if ($view === 'category') {
            $associations = Associations::getAssociations('com_phocacart', '#__phocacart_categories', 'com_phocacart.category', $id, 'id', 'alias', false);

            $return = array();

            foreach ($associations as $tag => $item) {
                $idA     = explode(":", $item->id);
                $idAlias = '';
                if (isset($idA[1])) {
                    $idAlias = $idA[1];
                }
                $id = (int) $item->id;

                $return[$tag] = PhocacartRoute::getCategoryRoute((int) $id, $idAlias, array(0 => $tag));// tag = lang
            }

            return $return;
        }

        return [];
    }

    public static function getI18nAssociations($id = 0, $view = null)
    {
        $input = Factory::getApplication()->input;
        $view   = $view === null ? $input->get('view') : $view;
        $id     = empty($id) ? $input->getInt('id') : $id;
        $languages    = I18nHelper::getI18nLanguages();

        if ($view === 'item') {
            if (!$id) {
                return [];
            }

            $associations = [];

            foreach ($languages as $langTag => $language) {
                $categories = PhocacartCategoryMultiple::getCategories($id, 3);
                $catId      = $categories[0]->id ?? 0;

                $associations[$langTag] = PhocacartRoute::getItemRoute($id, $catId, '', '', [$langTag]);
            }

            return $associations;
        } elseif ($view === 'category') {
            if (!$id) {
                return [];
            }

            $associations = [];

            foreach ($languages as $langTag => $language) {
                $associations[$langTag] = PhocacartRoute::getCategoryRoute($id, '', [$langTag]);
            }

            return $associations;
        } else {
            return self::getJoomlaAssociations($id, $view);
        }
    }

	public static function getAssociations($id = 0, $view = null)
	{
        if (I18nHelper::isI18n()) {
            return self::getI18nAssociations($id, $view);
        } else {
            return self::getJoomlaAssociations($id, $view);
        }
	}
}
