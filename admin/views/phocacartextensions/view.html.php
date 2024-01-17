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
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocacartExtensions extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $t;
    protected $r;
    protected $d;
    public $filterForm;
    public $activeFilters;


    function display($tpl = null) {

        $document = Factory::getDocument();
        $this->t  = PhocacartUtils::setVars('extension');
        $this->r  = new PhocacartRenderAdminviews();

        $paramsC                        = PhocacartUtils::getComponentParameters();
        $this->t['load_extension_list'] = $paramsC->get('load_extension_list', 1);

        if ($this->t['load_extension_list'] == 1) {
            $this->items         = $this->get('Items');
            $this->news          = $this->get('News');
            $this->state         = $this->get('State');
            $this->filterForm    = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters');
        }
        $media = new PhocacartRenderAdminmedia();

        $this->addToolbar();
        parent::display($tpl);
    }

    function addToolbar() {


        require_once JPATH_COMPONENT . '/helpers/' . $this->t['tasks'] . '.php';
        $state = $this->get('State');
        $class = ucfirst($this->t['tasks']) . 'Helper';
        $canDo = $class::getActions($this->t, $state->get('filter.extension_id'));

        ToolbarHelper::title(Text::_($this->t['l'] . '_EXTENSIONS'), 'modules');

        // This button is unnecessary but it is displayed because Joomla! design bug
        $bar   = Toolbar::getInstance('toolbar');
        $dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-primary btn-small"><i class="icon-home-2" title="' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '"></i> ' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '</a>';
        $bar->appendButton('Custom', $dhtml);

        if ($this->t['load_extension_list'] == 1) {

            ToolbarHelper::custom($this->t['task'] . '.refresh', 'refresh.png', 'refresh.png', 'COM_PHOCACART_REFRESH', false);
        }
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
            }

            if ($canDo->get('core.delete')) {
                ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartlogs.delete', $this->t['l'].'_DELETE');
            }*/
        ToolbarHelper::divider();
        ToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields() {
        return array(
            'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
            'a.title' => Text::_($this->t['l'] . '_TITLE'),
            'a.published' => Text::_($this->t['l'] . '_PUBLISHED'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        );
    }
}

?>
