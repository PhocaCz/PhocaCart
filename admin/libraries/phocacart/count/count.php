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

class PhocacartCount
{
	public static function setProductCount($cid, $type = '', $skipMessage = 0) {

	    $db 		= Factory::getDBO();
	    $app	    = Factory::getApplication();
	    $successA   = array();
	    $errorA     = array();


	    if ($type == '') { return false;}


	    if (!empty($cid)) {
            foreach ($cid as $k => $v) {

                $date 	= Factory::getDate();
                $now	= $date->toSql();

                switch($type) {

                    case 'category':
                        $q =  ' SELECT count(a.id)'
                        . ' FROM #__phocacart_products AS a'
                        . ' LEFT JOIN #__phocacart_product_categories AS pc ON a.id = pc.product_id'
                        . ' WHERE a.published = 1 AND pc.category_id = '.(int)$v;

                        $db->setQuery($q);
			            $items = $db->loadResult();
			            $q2 = 'UPDATE #__phocacart_categories SET count_products = '.(int)$items.', count_date = '.$db->quote($now) . ' WHERE id = '.(int)$v;
				        $db->setQuery($q2);
                    break;

                    case 'tag':

                        $q0 = 'SELECT type FROM #__phocacart_tags WHERE id = '.(int)$v;// Apply only for tags, not for labels
                        $db->setQuery($q0);
                        $type0  = $db->loadResult();
                        if ($type0 == 0) {
                            $q = ' SELECT count(a.id)'
                                . ' FROM #__phocacart_products AS a'
                                . ' LEFT JOIN #__phocacart_tags_related AS tr ON a.id = tr.item_id'
                                . ' WHERE a.published = 1 AND tr.tag_id = ' . (int)$v;

                            $db->setQuery($q);
                            $items = $db->loadResult();
                            $q2 = 'UPDATE #__phocacart_tags SET count_products = ' . (int)$items . ', count_date = ' . $db->quote($now) . ' WHERE id = ' . (int)$v;
                            $db->setQuery($q2);
                        }
                    break;

                    case 'label':

                        $q0 = 'SELECT type FROM #__phocacart_tags WHERE id = '.(int)$v;// Apply only for labels, not for tags
                        $db->setQuery($q0);
                        $type0  = $db->loadResult();
                        if ($type0 == 1) {
                            $q = ' SELECT count(a.id)'
                                . ' FROM #__phocacart_products AS a'
                                . ' LEFT JOIN #__phocacart_taglabels_related AS tr ON a.id = tr.item_id'
                                . ' WHERE a.published = 1 AND tr.tag_id = ' . (int)$v;

                            $db->setQuery($q);
                            $items = $db->loadResult();
                            $q2 = 'UPDATE #__phocacart_tags SET count_products = ' . (int)$items . ', count_date = ' . $db->quote($now) . ' WHERE id = ' . (int)$v;
                            $db->setQuery($q2);
                        }
                    break;

                    case 'manufacturer':

                        $q =  ' SELECT count(a.id)'
                        . ' FROM #__phocacart_products AS a'
                        . ' WHERE a.published = 1 AND a.manufacturer_id = '.(int)$v;

                        $db->setQuery($q);
			            $items = $db->loadResult();
			            $q2 = 'UPDATE #__phocacart_manufacturers SET count_products = '.(int)$items.', count_date = '.$db->quote($now) . ' WHERE id = '.(int)$v;
				        $db->setQuery($q2);
                    break;

                    case 'parameter':
                        $q =  ' SELECT count(a.id)'
                        . ' FROM #__phocacart_products AS a'
                        . ' LEFT JOIN #__phocacart_parameter_values_related AS pvr ON a.id = pvr.item_id'
                        . ' WHERE a.published = 1 AND pvr.parameter_value_id = '.(int)$v;

                        $db->setQuery($q);
			            $items = $db->loadResult();

			            $q2 = 'UPDATE #__phocacart_parameter_values SET count_products = '.(int)$items.', count_date = '.$db->quote($now) . ' WHERE id = '.(int)$v;
				        $db->setQuery($q2);
                    break;

                }


				if (!$db->execute()) {
			        $errorA[] = $v;
                } else {
				    $successA[] = $v;
                }
	        }
        }

	    if ($skipMessage == 1) {
	        return true;
        }

	    if(!empty($errorA)) {
            $errorS = implode(', ', $errorA);
            $app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_COUNT_NOT_SET_FOLLOWING_ITEMS') . ': '.$errorS, 'error');
        }
        if (!empty($successA)){
	        $successS = implode(', ', $successA);
            $app->enqueueMessage(Text::_( 'COM_PHOCACART_PRODUCT_COUNT_SUCCESSFULLY_SET_FOLLOWING_ITEMS' ) . ': '.$successS, 'success');
        }

	    return true;
    }

}
