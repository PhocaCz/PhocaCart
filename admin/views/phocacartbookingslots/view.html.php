<?php
/**
 * @package    phocacart
 * @subpackage Views
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.view');

class PhocaCartCpViewPhocaCartBookingSlots extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $t;
    protected $r;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null)
    {
        $this->t = PhocacartUtils::setVars('bookingslot');
        $this->r = new PhocacartRenderAdminviews();

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (!empty($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

                $media = new PhocacartRenderAdminmedia();
        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        require_once JPATH_COMPONENT . '/helpers/phocacartbookingslots.php';
        $canDo = PhocacartBookingSlotsHelper::getActions($this->t, $this->state->get('filter.id'));

        ToolbarHelper::title(Text::_('COM_PHOCACART_BOOKING_SLOTS'), 'calendar');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('phocacartbookingslot.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('phocacartbookingslot.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publish('phocacartbookingslots.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('phocacartbookingslots.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_PHOCACART_WARNING_DELETE_ITEMS'), 'phocacartbookingslots.delete', Text::_('JTOOLBAR_DELETE'));
        }

        ToolbarHelper::preferences('com_phocacart');

        PhocacartRenderAdminview::renderWizardButton('back');
    }
}
