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

class PhocaCartCpViewPhocacartParameters extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null) {

		$this->t			= PhocacartUtils::setVars('parameter');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm   	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[0][] = $item->id;
		}

		$media = new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}

	function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.tag_id'));

		JToolbarHelper::title( JText::_( $this->t['l'].'_PARAMETERS' ), 'align-justify' );

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
			JToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartparameters.delete', $this->t['l'].'_DELETE');
		}
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> JText::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>
