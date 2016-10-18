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
 
class PhocaCartCpViewPhocaCartOrders extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	
	function display($tpl = null) {
		
		$document			= JFactory::getDocument();
		$this->t			= PhocaCartUtils::setVars('order');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}
		
		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[0][] = $item->id;
		}
		
		JHTML::stylesheet( $this->t['s'] );
		JHTML::stylesheet( $this->t['css'] . 'icomoon/icomoon.css' );
		
		$this->t['plugin-pdf']		= PhocaCartExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		$this->t['component-pdf']	= PhocaCartExtension::getExtensionInfo('com_phocapdf');
				
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	function addToolbar() {
	
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.order_id'));

		JToolBarHelper::title( JText::_( $this->t['l'].'_ORDERS' ), 'pencil' );
	
		if ($canDo->get('core.create')) {
			//JToolBarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
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
			JToolBarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartorders.delete', $this->t['l'].'_DELETE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.'.$this->t['c'], true );
	}
	
	protected function getSortFields() {
		return array(
			//'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.id' 				=> JText::_($this->t['l'] . '_ORDER_NUMBER'),
			'username' 			=> JText::_($this->t['l'] . '_USER'),
			'a.status' 			=> JText::_($this->t['l'] . '_STATUS'),
			'total' 			=> JText::_($this->t['l'] . '_TOTAL'),
			'a.date' 			=> JText::_($this->t['l'] . '_DATE_ADDED'),
			'a.modified' 		=> JText::_($this->t['l'] . '_DATE_MODIFIED'),
			'a.notify'	 		=> JText::_($this->t['l'] . '_USER_NOTIFIED'),
			'a.published'		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>