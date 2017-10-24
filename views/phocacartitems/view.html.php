<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.view' );
 
class PhocaCartCpViewPhocaCartItems extends JViewLegacy
{

	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	
	function display($tpl = null) {
		
		$this->t			= PhocacartUtils::setVars('item');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		

		// Multiple categories, ordering
		$this->t['catid']	= $this->escape($this->state->get('filter.category_id'));
		$this->t['ordering']= false;// Is specific ordering used (ordering in phocacart_product_categories reference table)
		if (isset($this->t['catid']) && (int)$this->t['catid'] > 0) {
			$this->t['ordering']= true;
		} 
		
		// Multiple categories: Orderinga and list all ids on the site ($idItems)
		$idItems			= array();
		foreach ($this->items as &$item) {
			if (isset($this->t['catid']) && (int)$this->t['catid'] > 0) {
				$this->ordering[(int)$this->t['catid']][] = $item->id;
			}
			$idItems[] = $item->id;
		}
		
		// Make list of categories for each product (don't run group_concat alternative but own sql)
		$categories	= PhocacartCategoryMultiple::getCategoriesByProducts($idItems);
		
		$this->t['categories'] = array();
		if (!empty($categories)) {
			foreach ($categories as $k => $v) {
				$id = $v['product_id'];
				$this->t['categories'][$id][$k]['id'] = $v['id'];  
				$this->t['categories'][$id][$k]['alias'] = $v['alias'];
				$this->t['categories'][$id][$k]['title'] = $v['title'];
			}
		}
		
		$media = new PhocacartRenderAdminmedia();
	
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.item_id'));
		$user  	= JFactory::getUser();
		$bar 	= JToolbar::getInstance('toolbar');
		
		JToolbarHelper::title( JText::_($this->t['l'].'_PRODUCTS'), 'folder-close' );
		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew( $this->t['task'].'.add','JTOOLBAR_NEW');

		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		
		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::divider();
			JToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom($this->t['tasks'].'.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
		}

		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList( JText::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}
		
		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			JHtml::_('bootstrap.renderModal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
			
			JHtml::_('bootstrap.renderModal', 'collapseModalCA');
			$title = JText::_('COM_PHOCACART_COPY_ATTRIBUTES');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModalCA\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'copy_attributes');
		}
		
		$dhtml = '<button class="btn btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.JText::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.JText::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS').'\')){submitbutton(\'phocacartitem.recreate\');}}" ><i class="icon-image" title="'.JText::_('COM_PHOCACART_RECREATE_THUMBS').'"></i> '.JText::_('COM_PHOCACART_RECREATE_THUMBS').'</button>';
		$bar->appendButton('Custom', $dhtml);
		
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
		
		PhocacartRenderAdminview::renderWizardButton('back');
	}
	
	protected function getSortFields() {
		return array(
			'pc.ordering'	=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 		=> JText::_($this->t['l'] . '_TITLE'),
			'a.image' 		=> JText::_($this->t['l'] . '_IMAGE'),
			'a.hits' 		=> JText::_($this->t['l'] . '_HITS'),
			'a.published' 	=> JText::_($this->t['l'] . '_PUBLISHED'),
			'category_id' 	=> JText::_($this->t['l'] . '_CATEGORY'),
			'language' 		=> JText::_('JGRID_HEADING_LANGUAGE'),
			'a.hits' 		=> JText::_($this->t['l'] . '_HITS'),
			'a.id' 			=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>