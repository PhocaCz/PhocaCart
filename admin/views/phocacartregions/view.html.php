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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartRegions extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null) {

		$this->t			= PhocacartUtils::setVars('region');
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


		foreach ($this->items as &$item) {
			$this->ordering[$item->country_id][] = $item->id;
		}

		$media = new PhocacartRenderAdminmedia();
		//HTMLHelper::stylesheet( $this->t['bootstrap'] . 'css/bootstrap.glyphicons-icons-only.min.css' );

		$this->addToolbar();
		parent::display($tpl);
	}

	function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.region_id'));
		$bar 	= Toolbar::getInstance('toolbar');

		ToolbarHelper::title( Text::_( $this->t['l'].'_REGIONS' ), 'globe' );

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
			ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartregions.delete', $this->t['l'].'_DELETE');
		}

		//JToolbarHelper::custom($this->t['task'].'.importregions', 'earth', 'earth', $this->t['l'].'_IMPORT_REGIONS', false);
		$dhtml = '<joomla-toolbar-button><button onclick="if (confirm(\''.Text::_('COM_PHOCACART_WARNING_IMPORT_REGIONS').'\')) { Joomla.submitbutton(\'phocacartregion.importregions\'); }" class="btn btn-primary btn-small button-earth"><i class="icon-earth fas fa fa-globe" title="'.Text::_($this->t['l'].'_IMPORT_REGIONS').'"></i> '.Text::_($this->t['l'].'_IMPORT_REGIONS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml, 'importregions');


		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'country_title' 		=> Text::_($this->t['l'] . '_COUNTRY'),
			'a.code2' 			=> Text::_($this->t['l'] . '_CODE2'),
			'a.code3' 			=> Text::_($this->t['l'] . '_CODE3'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
