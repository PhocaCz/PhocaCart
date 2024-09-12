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


abstract class PhocacartHelperAssociation
{

	public static function getAssociations($id = 0, $view = null)
	{


		$jinput = Factory::getApplication()->input;
		$view   = $view === null ? $jinput->get('view') : $view;
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view === 'item') {
			if ($id) {
				$associations = Associations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', $id, 'id', 'alias', false);

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

					$return[$tag] = PhocacartRoute::getItemRoute((int)$id, (int)$catid, $idAlias, $catidAlias, array(0 => $tag));

				}

				return $return;
			}
		} else if($view === 'category') {
			if ($id) {
				$associations = Associations::getAssociations('com_phocacart', '#__phocacart_categories', 'com_phocacart.category', $id, 'id', 'alias', false);

				$return = array();

				foreach ($associations as $tag => $item) {
				    $idA = explode(":",$item->id);
                    $idAlias = '';
				    if (isset($idA[1])) {
                        $idAlias = $idA[1];
                    }
				    $id = (int)$item->id;



					$return[$tag] = PhocacartRoute::getCategoryRoute((int)$id, $idAlias, array(0 => $tag));// tag = lang

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
