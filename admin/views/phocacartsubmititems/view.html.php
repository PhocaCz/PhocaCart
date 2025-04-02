<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartSubmititems extends HtmlView
{

	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
    public $filterForm;
	public $activeFilters;
	//protected $sidebar;

	function display($tpl = null) {


		$this->t			= PhocacartUtils::setVars('submititem');
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

	protected function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.item_id'));
		$user  	= Factory::getUser();
		$bar 	= Toolbar::getInstance('toolbar');



		ToolbarHelper::title( Text::_($this->t['l'].'_SUBMITTED_ITEMS'), 'duplicate-alt' );
		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew( $this->t['task'].'.add','JTOOLBAR_NEW');

		}
		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::divider();
			ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}



		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList( Text::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}

		// Add a batch button
	/*	if ($user->authorise('core.edit'))
		{
			HTMLHelper::_('bootstrap.renderModal', 'collapseModal');
			$title = Text::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-bs-toggle=\"modal\" data-bs-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');

			HTMLHelper::_('bootstrap.renderModal', 'collapseModalCA');
			$title = Text::_('COM_PHOCACART_COPY_ATTRIBUTES');
			$dhtml = "<button data-bs-toggle=\"modal\" data-bs-target=\"#collapseModalCA\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'copy_attributes');
		}

*/

		$dhtml = '<joomla-toolbar-button><button class="btn btn-primary btn-small" onclick="javascript: if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_CREATE_PRODUCTS_ITEMS_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_CREATE_PRODUCTS_ITEMS').'\')){Joomla.submitbutton(\'phocacartsubmititem.create\');}}" ><i class="icon-new" title="'.Text::_('COM_PHOCACART_CREATE_PRODUCTS').'"></i> '.Text::_('COM_PHOCACART_CREATE_PRODUCTS').'</button></joomla-toolbar-button>';



			$bar->appendButton('Custom', $dhtml);



		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		//PhocacartRenderAdminview::renderWizardButton('back');
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.submit_date' 			=> Text::_($this->t['l'] . '_DATE'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
