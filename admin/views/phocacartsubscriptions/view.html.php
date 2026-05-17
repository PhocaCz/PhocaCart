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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\PhocaCart\Administrator\Helper\SubscriptionHelper as PhocacartSubscriptionHelper;

jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartSubscriptions extends HtmlView
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
        // Init Phoca Cart Helpers
        $this->t = PhocacartUtils::setVars('subscription');
        $this->r = new PhocacartRenderAdminviews();

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (!empty($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        require_once JPATH_COMPONENT.'/helpers/phocacartsubscriptions.php';
        $canDo = PhocacartSubscriptionsHelper::getActions($this->t, $this->state->get('filter.id'));
        $bar   = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_PHOCACART_SUBSCRIPTIONS'), 'calendar');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('phocacartsubscription.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('phocacartsubscription.edit');
        }

        if ($canDo->get('core.edit.state')) {
         /*   $dropdown = $bar->dropdownButton('status-group')->text('JTOOLBAR_CHANGE_STATUS')->toggleSplit(false)->icon('icon-ellipsis-h')->buttonClass('btn btn-action');
            $childBar = $dropdown->getChildToolbar();
            $childBar->publish('phocacartsubscriptions.publish')->listCheck(true);
            $childBar->unpublish('phocacartsubscriptions.unpublish')->listCheck(true);*/
        }

        if ($canDo->get('core.delete')) {
            $title = Text::_('JTOOLBAR_DELETE');
            ToolbarHelper::deleteList('COM_PHOCACART_SUBSCRIPTION_DELETE_CONFIRM_WITH_LOGS', 'phocacartsubscriptions.delete', $title);
        }

        ToolbarHelper::preferences('com_phocacart');

        PhocacartRenderAdminview::renderWizardButton('back');
    }
}
