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

class PhocaCartCpViewPhocaCartSubmititems extends JViewLegacy
{

	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
    public $filterForm;
	public $activeFilters;
	protected $sidebar;

	function display($tpl = null) {


		$this->t			= PhocacartUtils::setVars('submititem');
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



		JToolbarHelper::title( JText::_($this->t['l'].'_SUBMITTED_ITEMS'), 'duplicate' );
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
		}



		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList( JText::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}

		// Add a batch button
	/*	if ($user->authorise('core.edit'))
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

*/

		$dhtml = '<button class="btn btn-small" onclick="javascript: if(document.adminForm.boxchecked.value==0){alert(\''.JText::_('COM_PHOCACART_WARNING_CREATE_PRODUCTS_ITEMS_MAKE_SELECTION').'\');}else{if(confirm(\''.JText::_('COM_PHOCACART_WARNING_CREATE_PRODUCTS_ITEMS').'\')){submitbutton(\'phocacartsubmititem.create\');}}" ><i class="icon-new" title="'.JText::_('COM_PHOCACART_CREATE_PRODUCTS').'"></i> '.JText::_('COM_PHOCACART_CREATE_PRODUCTS').'</button>';



			$bar->appendButton('Custom', $dhtml);



		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );

		//PhocacartRenderAdminview::renderWizardButton('back');
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> JText::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.submit_date' 			=> JText::_($this->t['l'] . '_DATE'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>
