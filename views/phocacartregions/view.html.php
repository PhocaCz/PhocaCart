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
 
class PhocaCartCpViewPhocaCartRegions extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	
	function display($tpl = null) {
		
		$this->t			= PhocaCartUtils::setVars('region');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}
		
		
		foreach ($this->items as &$item) {
			$this->ordering[$item->country_id][] = $item->id;
		}
		
		JHTML::stylesheet( $this->t['s'] );
		JHTML::stylesheet( $this->t['css'] . 'icomoon/icomoon.css' );
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	function addToolbar() {
	
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.region_id'));

		JToolBarHelper::title( JText::_( $this->t['l'].'_REGIONS' ), 'ph-earth' );
	
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}
	
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			JToolBarHelper::divider();
			JToolBarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}
	
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartregions.delete', $this->t['l'].'_DELETE');
		}
		
		JToolBarHelper::custom($this->t['task'].'.importregions', 'ph-earth', 'ph-earth', $this->t['l'].'_IMPORT_REGIONS', false);
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.'.$this->t['c'], true );
	}
	
	protected function getSortFields() {
		return array(
			'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> JText::_($this->t['l'] . '_TITLE'),
			'country_title' 		=> JText::_($this->t['l'] . '_COUNTRY'),
			'a.code3' 			=> JText::_($this->t['l'] . '_CODE'),
			'a.published' 		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>