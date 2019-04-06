<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//JLoader::register('NewsfeedsHelper', JPATH_ADMINISTRATOR . '/components/com_newsfeeds/helpers/newsfeeds.php');
//JLoader::register('NewsfeedsHelperRoute', JPATH_SITE . '/components/com_newsfeeds/helpers/route.php');
//JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Newsfeeds Component Association Helper
 *
 * @since  3.0
 */
abstract class PhocacartHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{


		$jinput = JFactory::getApplication()->input;
		$view   = $view === null ? $jinput->get('view') : $view;
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view === 'item') {
			if ($id) {
				$associations = JLanguageAssociations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', $id, 'id', 'alias', false);

				$return = array();

				foreach ($associations as $tag => $item) {
				    $idA = explode(":",$item->id);
				    $idAlias = '';
				    if (isset($idA[1])) {
                        $idAlias = $idA[1];
                    }
				    $id = (int)$item->id;


					$catidA = PhocacartCategoryMultiple::getCategories($id, 3);
					$catid  = 0;
					if (isset($catidA[0]->id)) {
					    $catid = $catidA[0]->id;
                    }
					$catidAlias = '';
					if (isset($catidA[0]->alias)) {
					    $catidAlias = $catidA[0]->alias;
                    }

					$return[$tag] = PhocacartRoute::getItemRoute((int)$id, (int)$catid, $idAlias, $catidAlias);

				}

				return $return;
			}
		} else if($view === 'category') {
			if ($id) {
				$associations = JLanguageAssociations::getAssociations('com_phocacart', '#__phocacart_categories', 'com_phocacart.category', $id, 'id', 'alias', false);

				$return = array();

				foreach ($associations as $tag => $item) {
				    $idA = explode(":",$item->id);
                    $idAlias = '';
				    if (isset($idA[1])) {
                        $idAlias = $idA[1];
                    }
				    $id = (int)$item->id;

					$return[$tag] = PhocacartRoute::getCategoryRoute((int)$id, $idAlias);

				}

				return $return;
			}

		}

		/*if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_phocacart');
		}*/

		return array();
	}
}
