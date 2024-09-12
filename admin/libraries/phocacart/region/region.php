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

class PhocacartRegion
{
	public static function getRegionById($regionId) {

		$db =Factory::getDBO();
		$query = 'SELECT title FROM #__phocacart_regions WHERE id = '.(int) $regionId. ' ORDER BY title LIMIT 1';
		$db->setQuery($query);
		$region = $db->loadColumn();
		if(isset($region[0])) {
			return (string)$region[0];
		}
		return '';
	}

	public static function getRegionsByCountry($countryId) {

		$db =Factory::getDBO();

		$query = 'SELECT a.id, a.title FROM #__phocacart_regions AS a'
			    .' WHERE a.country_Id = '.(int) $countryId
				.' ORDER BY a.id';
		$db->setQuery($query);
		$regions = $db->loadObjectList();

		return $regions;
	}





	public static function getRegions($id, $select = 0, $table = 'shipping') {

		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_regions';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_regions';
			$c = 'payment_id';
		}  else if ($table == 'zone') {
			$t = '#__phocacart_zone_regions';
			$c = 'zone_id';
		}

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT r.region_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_regions AS a'
				.' LEFT JOIN '.$t.' AS r ON a.id = r.region_id'
			    .' WHERE r.'.$c.' = '.(int) $id;
		$db->setQuery($query);
		if ($select == 1) {
			$items = $db->loadColumn();
		} else {
			$items = $db->loadObjectList();
		}

		return $items;
	}

	public static function storeRegions($regionsArray, $id, $table = 'shipping') {

		if ($table == 'shipping') {
			$t = '#__phocacart_shipping_method_regions';
			$c = 'shipping_id';
		} else if ($table == 'payment') {
			$t = '#__phocacart_payment_method_regions';
			$c = 'payment_id';
		}  else if ($table == 'zone') {
			$t = '#__phocacart_zone_regions';
			$c = 'zone_id';
		}

		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();


			if (!empty($regionsArray)) {

				$values 		= array();
				$valuesString 	= '';

				foreach($regionsArray as $k => $v) {
					//$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
					// No multidimensional in J4
					$values[] = ' ('.(int)$id.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO '.$t.' ('.$c.', region_id)'
								.' VALUES '.(string)$valuesString;



					$db->setQuery($query);
					$db->execute();
				}
			}
		}

	}

	public static function getAllRegionsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {

		$db =Factory::getDBO();
		/*$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_countries AS a'
				. ' ORDER BY '. $order;
		$query = 'SELECT a.id AS value, a.title AS text, a.country_id as countrid'
				.' FROM #__phocacart_regions AS a'
				. ' ORDER BY '. $order;*/

		$query = 'SELECT a.id AS value, a.title AS text, a.country_id as cid, c.title as countrytext'
				.' FROM #__phocacart_regions AS a'
				.' LEFT JOIN #__phocacart_countries AS c ON c.id = a.country_id'
				.' ORDER BY c.id, a.'. $order;
		$db->setQuery($query);
		$regions = $db->loadObjectList();


		//$regionsO = JHtml::_('select.genericlist', $regions, $name, 'class="form-control" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);


		// Try to do 1 SQL Query and 1 Foreach
		$prev = 0;// the id of previous item
		$usedFirst = 0;// first time opened the optgroup, so we can close it next time when cid is differnt to prev
		$open = 0;// if we reach the end of foreach, we discover if the tag is open, if yet, close it.

		$countRegions = count($regions);

		$regionsO	= '';
		$regionsO .= '<select id="'.$id.'" name="'.$name.'" class="form-control" size="4" multiple="multiple">';


		$i = 0;
		foreach($regions as $k => $v) {
			$selected = '';
			if (in_array((int)$v->value, $activeArray)) {
				$selected = 'selected="selected"';
			}

			// Groups
			if ((int)$v->cid > 0 && $v->cid != $prev) {
				if ($usedFirst == 1) {
					$regionsO .= '</optgroup>';
					$open		= 0;
				}

				$regionsO .= '<optgroup label="'.$v->countrytext.'">';
				$prev 		= (int)$v->cid;// we prepare previous version
				$usedFirst	= 1;// we have used the optgroup first time
				$open		= 1;
			}
			$regionsO .= '<option value="'.(int)$v->value.'" '.$selected.'>'.$v->text.'</option>';

			$i++;
			if ((int)$v->cid > 0 && $i == $countRegions && $open == 1) {
				$regionsO .= '</optgroup>';
			}
		}
		$regionsO .= '</select>';

		return $regionsO;
	}

	public static function getAllRegions($order = 'id' ) {

		$db =Factory::getDBO();
		/*$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_countries AS a'
				. ' ORDER BY '. $order;
		$query = 'SELECT a.id AS value, a.title AS text, a.country_id as countrid'
				.' FROM #__phocacart_regions AS a'
				. ' ORDER BY '. $order;*/

		$query = 'SELECT a.id AS value, a.title AS text, a.country_id as cid, c.title as countrytext'
				.' FROM #__phocacart_regions AS a'
				.' LEFT JOIN #__phocacart_countries AS c ON c.id = a.country_id'
				.' ORDER BY c.id, a.'. $order;
		$db->setQuery($query);
		$regions = $db->loadObjectList();

		foreach($regions as $k => $v) {
			$v->text = $v->countrytext . ' - ' . $v->text;

		}
		return $regions;

/*
		//$regionsO = JHtml::_('select.genericlist', $regions, $name, 'class="form-control" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);


		// Try to do 1 SQL Query and 1 Foreach
		$prev = 0;// the id of previous item
		$usedFirst = 0;// first time opened the optgroup, so we can close it next time when cid is differnt to prev
		$open = 0;// if we reach the end of foreach, we discover if the tag is open, if yet, close it.

		$countRegions = count($regions);

		$regionsO	= '';
		$regionsO .= '<select id="'.$id.'" name="'.$name.'" class="form-control" size="4" multiple="multiple">';


		$i = 0;
		foreach($regions as $k => $v) {
			$selected = '';
			if (in_array((int)$v->value, $activeArray)) {
				$selected = 'selected="selected"';
			}

			// Groups
			if ((int)$v->cid > 0 && $v->cid != $prev) {
				if ($usedFirst == 1) {
					$regionsO .= '</optgroup>';
					$open		= 0;
				}

				$regionsO .= '<optgroup label="'.$v->countrytext.'">';
				$prev 		= (int)$v->cid;// we prepare previous version
				$usedFirst	= 1;// we have used the optgroup first time
				$open		= 1;
			}
			$regionsO .= '<option value="'.(int)$v->value.'" '.$selected.'>'.$v->text.'</option>';

			$i++;
			if ((int)$v->cid > 0 && $i == $countRegions && $open == 1) {
				$regionsO .= '</optgroup>';
			}
		}
		$regionsO .= '</select>';

		return $regionsO;*/
	}


	/*
	public static function displayRegions($shippingId, $popupLink = 0) {

		$o 		= '';
		$db 	= Factory::getDBO();
		$params = PhocacartUtils::getComponentParameters() ;

		$query = 'SELECT a.id, a.title, a.link_ext, a.link_cat'
		.' FROM #__phocacart_regions AS a'
		.' LEFT JOIN #__phocacart_shipping_method_regions AS r ON r.region_id = a.id'
		.' WHERE r.shipping_id = '.(int)$shippingId;

		$db->setQuery($query);
		$imgObject = $db->loadObjectList();

		if (!$db->query()) {
			echo PhocacartUtilsException::renderErrorInfo($db->getError());
			return false;
		}

		/*
		if ($popupLink == 1) {
			$tl	= 0;
		} else  {
			$tl	= $params->get( 'tags_links', 0 );
		}*/
/*
		$targetO = '';
		if ($popupLink == 1) {
			$targetO = 'target="_parent"';
		}
		$tl	= $params->get( 'regions_links', 0 );

		foreach ($imgObject as $k => $v) {
			$o .= '<span>';
			if ($tl == 0) {
				$o .= $v->title;
			} else if ($tl == 1) {
				if ($v->link_ext != '') {
					$o .= '<a href="'.$v->link_ext.'" '.$targetO.'>'.$v->title.'</a>';
				} else {
					$o .= $v->title;
				}
			} else if ($tl == 2) {

				if ($v->link_cat != '') {
					$query = 'SELECT a.id, a.alias'
					.' FROM #__phocacart_categories AS a'
					.' WHERE a.id = '.(int)$v->link_cat;

					$db->setQuery($query, 0, 1);
					$category = $db->loadObject();

					if (!$db->query()) {
						echo PhocacartUtilsException::renderErrorInfo($db->getError());
						return false;
					}
					if (isset($category->id) && isset($category->alias)) {
						$link = PhocacartRoute::getCategoryRoute($category->id, $category->alias);
						$o .= '<a href="'.$link.'" '.$targetO.'>'.$v->title.'</a>';
					} else {
						$o .= $v->title;
					}
				} else {
					$o .= $v->title;
				}
			} else if ($tl == 3) {
				$link = PhocacartRoute::getCategoryRouteByTag($v->id);
				$o .= '<a href="'.$link.'" '.$targetO.'>'.$v->title.'</a>';
			}

			$o .= '</span> ';
		}

		return $o;
	}*/
}
?>
