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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartCoupons extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	protected $s;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null) {

		$this->t			= PhocacartUtils::setVars('coupon');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->s             = PhocacartRenderStyle::getStyles();
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

		$this->t['plugin-pdf']    = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
        $this->t['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');

		$this->addToolbar();
		parent::display($tpl);
	}


	function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.coupon_id'));

		ToolbarHelper::title( Text::_( $this->t['l'].'_COUPONS' ), 'gift' );

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
			ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartcoupons.delete', $this->t['l'].'_DELETE');
		}
		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.code' 			=> Text::_($this->t['l'] . '_CODE'),
			'a.discount' 		=> Text::_($this->t['l'] . '_DISCOUNT'),
			'a.valid_from' 		=> Text::_($this->t['l'] . '_VALID_FROM'),
			'a.valid_to' 		=> Text::_($this->t['l'] . '_VALLID_TO'),
			'a.coupon_type' 	=> Text::_($this->t['l'] . '_COUPON_TYPE'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
