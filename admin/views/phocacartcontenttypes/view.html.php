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

class PhocaCartCpViewPhocacartContentTypes extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null)
	{
		$this->t = PhocacartUtils::setVars('contenttype');
		$this->r = new PhocacartRenderAdminviews();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
		}

		new PhocacartRenderAdminmedia();

		$this->addToolbar();

		parent::display($tpl);
	}

	function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/phocacartcommon.php';
		$canDo	= PhocaCartCommonHelper::getActions($this->t);

		ToolbarHelper::title( Text::_('COM_PHOCACART_CONTENT_TYPES' ), 'sourcetree' );

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew('phocacartcontenttype.add');
		}

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList('phocacartcontenttype.edit');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::divider();
			ToolbarHelper::publish('phocacartcontenttype.publish');
			ToolbarHelper::unpublish('phocacartcontenttype.unpublish');
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList( 'COM_PHOCACART_WARNING_DELETE_ITEMS', 'phocacartcontenttypes.delete');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.com_phocacart', true);
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
