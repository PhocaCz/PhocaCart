<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartCategories extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;

	function display($tpl = null) {
	
		$this->t			= PhocacartUtils::setVars('category');
		$model 				= $this->getModel();
		$this->items		= $model->getItems();
		$this->pagination	= $model->getPagination();
		$this->state		= $model->getState();
		
		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}
		
		// if search, don't do a tree, only display the searched items
		$this->t['search'] = $this->state->get('filter.search');
		// We need to load all items because of creating tree
		// After creating tree we get info from pagination
		// and will set displaying of categories for current pagination
		//E.g. pagination is limitstart 5, limit 5 - so only categories from 5 to 10 will be displayed
		
		// the same for max levels
		$this->t['level'] = $this->state->get('filter.level');
		
		if (!empty($this->items) && !$this->t['search']) {
			$text = ''; // text is tree name e.g. Category >> Subcategory
			$tree = array();
			// Filter max levels
			if (isset($this->t['level']) && $this->t['level'] > 0) {
				$maxLevel = (int)$this->t['level'] + 1;
			} else {
				$maxLevel = false;
			}
			
			$this->items = $this->processTree($this->items, $tree, 0, $text, -1, 0, '', $maxLevel);
			
			// Re count the pagination
			$countTotal 		= count($this->items);
			$model->setTotal($countTotal);
			$this->pagination	= $model->getPagination();
		}
		
		$media = new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.category_id'));
		JToolbarHelper::title( JText::_( $this->t['l'].'_CATEGORIES' ), 'folder-open' );
		$user  = JFactory::getUser();
		$bar = JToolBar::getInstance('toolbar');
		
		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			JToolbarHelper::divider();
			JToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList( JText::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}
		
		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		
		$dhtml = '<button class="btn btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.JText::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.JText::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS_CATEGORIES').'\')){submitbutton(\'phocacartcategory.recreate\');}}" ><i class="icon-image" title="'.JText::_('COM_PHOCACART_RECREATE_THUMBS').'"></i> '.JText::_('COM_PHOCACART_RECREATE_THUMBS').'</button>';
		$bar->appendButton('Custom', $dhtml);
		
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
		
	}
	
	protected function processTree( $data, $tree, $id = 0, $text='', $currentId, $level, $parentsTreeString = '', $maxLevel = false) {
	
	
		$countItemsInCat 	= 0;// Ordering
		$level 				= $level + 1;
		$parentsTreeString	= $id . ' '. $parentsTreeString;
		
		// Limit the level of tree		
		if (!$maxLevel || ($maxLevel && $level < $maxLevel)) {
			foreach ($data as $key) {	
				$show_text 	= $text . $key->title;
				
				static $iCT = 0;// All displayed items
				
				if ($key->parent_id == $id && $currentId != $id && $currentId != $key->id ) {	
					$tree[$iCT] 					= new JObject();
		
					// Ordering MUST be solved here
					if ($countItemsInCat > 0) {
						$tree[$iCT]->orderup				= 1;
					} else {
						$tree[$iCT]->orderup 				= 0;
					}
					
					if ($countItemsInCat < ($key->countid - 1)) {
						$tree[$iCT]->orderdown 				= 1;
					} else {
						$tree[$iCT]->orderdown 				= 0;
					}

					$tree[$iCT] 					= new JObject();
					
					$tree[$iCT]->level				= $level;		
					$tree[$iCT]->parentstree		= $parentsTreeString;

					$tree[$iCT]->id 				= $key->id;
					$tree[$iCT]->title 				= $show_text;
					$tree[$iCT]->title_self 		= $key->title;
					$tree[$iCT]->parent_id			= $key->parent_id;
					$tree[$iCT]->alias				= $key->alias;
					$tree[$iCT]->image				= $key->image;
					$tree[$iCT]->description		= $key->description;
					$tree[$iCT]->published			= $key->published;
					$tree[$iCT]->editor				= $key->editor;
					$tree[$iCT]->ordering			= $key->ordering;
					$tree[$iCT]->access				= $key->access;
					$tree[$iCT]->access_level		= $key->access_level;
					$tree[$iCT]->count				= $key->count;
					$tree[$iCT]->params				= $key->params;
					$tree[$iCT]->checked_out		= $key->checked_out;
					$tree[$iCT]->checked_out_time	= $key->checked_out_time;
					$tree[$iCT]->groupname			= 0;
				//	$tree[$iCT]->username			= $key->username;
				//	$tree[$iCT]->usernameno			= $key->usernameno;
					$tree[$iCT]->parentcat_title	= $key->parentcat_title;
					$tree[$iCT]->parentcat_id		= $key->parentcat_id;
					$tree[$iCT]->hits				= $key->hits;
				//	$tree[$iCT]->ratingavg			= $key->ratingavg;
				//	$tree[$iCT]->accessuserid		= $key->accessuserid;
				//	$tree[$iCT]->uploaduserid		= $key->uploaduserid;
					$tree[$iCT]->language			= $key->language;
					$tree[$iCT]->language_title		= $key->language_title;
				//	$tree[$iCT]->deleteuserid		= $key->deleteuserid;
				//	$tree[$iCT]->userfolder			= $key->userfolder;
				//	$tree[$iCT]->approved			= $key->approved;
				//	$tree[$iCT]->link				= '';
				//	$tree[$iCT]->filename			= '';// Will be added in View (after items will be reduced)
				//	$tree[$iCT]->linkthumbnailpath	= '';

					$iCT++;
					$tree = $this->processTree($data, $tree, $key->id, $show_text . " - ", $currentId, $level, $parentsTreeString, $maxLevel);
					$countItemsInCat++;
				}	
			}	
		}
		return($tree);
	}
	
	protected function getSortFields() {
		return array(
			'a.ordering'	=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 		=> JText::_($this->t['l'] . '_TITLE'),
			'a.published' 	=> JText::_($this->t['l'] . '_PUBLISHED'),
			'parent_title' 	=> JText::_($this->t['l'] . '_PARENT_CATEGORY'),
			'language' 		=> JText::_('JGRID_HEADING_LANGUAGE'),
			'a.hits' 		=> JText::_($this->t['l'] . '_HITS'),
			'a.id' 			=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>