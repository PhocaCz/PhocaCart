<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocacartLogs extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $t;
    protected $r;
    public $filterForm;
    public $activeFilters;

    function display($tpl = null) {

        $this->t             = PhocacartUtils::setVars('log');
        $this->r             = new PhocacartRenderAdminviews();
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $paramsC                   = PhocacartUtils::getComponentParameters();
        $this->t['enable_logging'] = $paramsC->get('enable_logging', 0);

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

        require_once JPATH_COMPONENT . '/helpers/' . $this->t['tasks'] . '.php';
        $state = $this->get('State');
        $class = ucfirst($this->t['tasks']) . 'Helper';
        $canDo = $class::getActions($this->t, $state->get('filter.log_id'));

        ToolbarHelper::title(Text::_($this->t['l'] . '_SYSTEM_LOG'), 'logs');

        // This button is unnecessary but it is displayed because Joomla! design bug
        $bar   = Toolbar::getInstance('toolbar');
        $dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-primary btn-small"><i class="icon-home-2" title="' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '"></i> ' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '</a>';
        $bar->appendButton('Custom', $dhtml);
        /*
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
            }*/

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList($this->t['l'] . '_WARNING_DELETE_ITEMS', 'phocacartlogs.delete', $this->t['l'] . '_DELETE');
        }
        ToolbarHelper::divider();
        ToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields() {
        return array(
            'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
            'a.date' => Text::_($this->t['l'] . '_DATE'),
            'a.type' => Text::_($this->t['l'] . '_TYPE'),
            'a.title' => Text::_($this->t['l'] . '_TITLE'),
            'user_username' => Text::_($this->t['l'] . '_USER'),
            'a.ip' => Text::_($this->t['l'] . '_IP'),
            'a.incoming_page' => Text::_($this->t['l'] . '_INCOMING_PAGE'),
            'a.description' => Text::_($this->t['l'] . '_MESSAGE'),
            'a.published' => Text::_($this->t['l'] . '_PUBLISHED'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        );
    }
}

?>
