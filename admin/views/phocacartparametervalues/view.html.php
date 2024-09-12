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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartParameterValues extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null) {

		$this->t			= PhocacartUtils::setVars('parametervalue');
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
		$canDo	= $class::getActions($this->t, $state->get('filter.parametervalue_id'));

		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title( Text::_( $this->t['l'].'_PARAMETER_VALUES' ), 'ellipsis-v' );

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
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
			ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartparametervalues.delete', $this->t['l'].'_DELETE');
		}



		$dhtml = '<joomla-toolbar-button><button onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_COUNT_PRODUCTS_MAKE_SELECTION').'\');}else{Joomla.submitbutton(\'phocacartparametervalue.countproducts\');}" class="btn btn-primary btn-small button-plus"><i class="icon-plus" title="'.Text::_($this->t['l'].'_COUNT_PRODUCTS').'"></i> '.Text::_($this->t['l'].'_COUNT_PRODUCTS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml, 'countproducts');

		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'a.parameter_id' 	=> Text::_($this->t['l'] . '_PARAMETER'),
			'a.count_products' 	=> Text::_($this->t['l'] . '_PRODUCT_COUNT'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
