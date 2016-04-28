<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartModelCategories extends JModelLegacy
{
	protected $categories 			= null;
	protected $categories_ordering	= null;
	protected $category_ordering		= null;

	public function __construct() {
		parent::__construct();
		$app	= JFactory::getApplication();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	public function getCategoriesList($displaySubcategories = 0) {
		if (empty($this->categories)) {			
			$categoriesOrdering = $this->getCategoryOrdering();
			
			if ((int)$displaySubcategories > 0) {
				$id = -1; // display subcategories - -1 means to load all items
			} else {
				$id = 0;// display only parent categories
			}
			
			$query			= $this->getCategoriesListQuery($id, $categoriesOrdering);
			$categories 	= $this->_getList($query);
			
			if (!empty($categories)) {
				
				// Parent Only
				foreach ($categories as $k => $v) {
					if ($v->parent_id == 0) {
						$this->categories[$v->id] = $categories[$k];
					}	
				}
			
				// Subcategories
				foreach ($categories as $k => $v) {
					if (isset($this->categories[$v->parent_id])) {
						$this->categories[$v->parent_id]->subcategories[] = $categories[$k];
						$this->categories[$v->parent_id]->numsubcat++;
					}	
				}
			}
			/*
			$this->categories 	= $this->_getList( $query );	
			if (!empty($this->categories)) {
				foreach ($this->categories as $key => $value) {
					$query	= $this->getCategoriesListQuery( $value->id, $categoriesOrdering );
					$this->categories[$key]->subcategories = $this->_getList( $query );
				}
			}*/
			
		}
		return $this->categories;
	}

	public function getCategoriesListQuery($id, $categoriesOrdering) {
		
		$wheres		= array();
		$user 		= JFactory::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		
		
		/*$app		= JFactory::getApplication();
		$params 	= $app->getParams();
		
		$display_categories = $params->get('display_categories', '');
		$hide_categories 	= $params->get('hide_categoriess', '');
		
		if ( $display_categories != '' ) {
			$wheres[] = " cc.id IN (".$display_categories.")";
		}
		
		if ( $hide_categories != '' ) {
			$wheres[] = " cc.id NOT IN (".$hide_categories.")";
		}*/
		
		if ($id == -1) {
			// No limit for parent_id - load all categories include subcategories
		} else {
			$wheres[] = " c.parent_id = " . (int)$id;
		}
		
		$wheres[] = " c.published = 1";
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$wheres[] = " c.access IN (".$userLevels.")";
		
		/*$query =  " SELECT c.id, c.title, c.alias, c.image, c.description, c.image as image, c.parent_id as parentid, COUNT(c.id) AS numdoc"
		. " FROM #__phocacart_categories AS c"
		. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
		. " WHERE " . implode( " AND ", $wheres )
		. " GROUP BY c.id"
		. " ORDER BY c.".$categoriesOrdering;*/
		
		$query =  " SELECT c.id, c.title, c.alias, c.image, c.description, c.image as image, c.parent_id as parentid, COUNT(c.id) AS numdoc, c.parent_id, 0 AS numsubcat"
		. " FROM #__phocacart_categories AS c"
		//. " LEFT JOIN #__phocacart_categories AS s ON s.parent_id = c.id AND s.published = 1"
		
		//. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id"
		//. " LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id AND a.published = 1"
		//. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
		
		. " WHERE " . implode( " AND ", $wheres )
		. " GROUP BY c.id"
		. " ORDER BY ".$categoriesOrdering;
		/*
		$query =  "SELECT c.id, c.title, group_concat(s.title) as subtitle, group_concat(s.id, ':', s.title, ':', s.alias) as subalias
					FROM #__phocacart_categories as c LEFT JOIN
						 #__phocacart_categories as s
						 on s.parent_id = c.id
					group by c.id";*/
					
		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		return $query;
	}
	
	public function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= JFactory::getApplication();
			$params 					= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocaCartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}
}
?>