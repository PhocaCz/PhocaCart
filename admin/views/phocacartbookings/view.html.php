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

class PhocaCartCpViewPhocaCartBookings extends HtmlView
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
        $this->t = PhocacartUtils::setVars('booking');
        $this->r = new PhocacartRenderAdminviews();

        $this->items            = $this->get('Items');
        $this->calendarBookings = $this->get('CalendarBookings');
        $this->pagination       = $this->get('Pagination');
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
        require_once JPATH_COMPONENT . '/helpers/phocacartbookings.php';
        $canDo = PhocacartBookingsHelper::getActions($this->t, $this->state->get('filter.id'));
        $bar   = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_PHOCACART_BOOKINGS'), 'calendar');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('phocacartbooking.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('phocacartbooking.edit');
        }

        if ($canDo->get('core.delete')) {
            $title = Text::_('JTOOLBAR_DELETE');
            ToolbarHelper::deleteList(Text::_('COM_PHOCACART_WARNING_DELETE_ITEMS'), 'phocacartbookings.delete', $title);
        }

        ToolbarHelper::preferences('com_phocacart');

        PhocacartRenderAdminview::renderWizardButton('back');
    }
}
