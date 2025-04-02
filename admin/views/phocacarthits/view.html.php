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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocaCartHits extends HtmlView
{

    protected $items;
    protected $pagination;
    protected $state;
    protected $t;
    protected $r;
    public $filterForm;
    public $activeFilters;

    function display($tpl = null) {

        $this->t             = PhocacartUtils::setVars('hit');
        $this->r             = new PhocacartRenderAdminviews();
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $media = new PhocacartRenderAdminmedia();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar() {

        require_once JPATH_COMPONENT . '/helpers/' . $this->t['tasks'] . '.php';
        $state = $this->get('State');
        $class = ucfirst($this->t['tasks']) . 'Helper';
        $canDo = $class::getActions($this->t, $state->get('filter.hit_id'));
        $user  = Factory::getUser();
        $bar   = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_($this->t['l'] . '_HITS'), 'mouse-pointer-highlighter');
        /*if ($canDo->get('core.create')) {
            ToolbarHelper::addNew( $this->t['task'].'.add','JTOOLBAR_NEW');

        }
        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::divider();
            ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
            ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::custom($this->t['tasks'].'.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
        }*/

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_($this->t['l'] . '_WARNING_DELETE_ITEMS'), $this->t['tasks'] . '.delete', $this->t['l'] . '_DELETE');
        }


        ToolbarHelper::divider();
        ToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields() {
        return array(
            //'a.ordering'	=> Text::_('JGRID_HEADING_ORDERING'),
            'a.product_id' => Text::_($this->t['l'] . '_PRODUCT'),
            'a.item' => Text::_($this->t['l'] . '_ITEM'),
            'a.user_id' => Text::_($this->t['l'] . '_USER'),
            'a.ip' => Text::_($this->t['l'] . '_IP'),
            'a.date' => Text::_($this->t['l'] . '_DATE'),
            'a.hits' => Text::_($this->t['l'] . '_HITS'),
            'a.type' => Text::_($this->t['l'] . '_TYPE'),
            'a.id' => Text::_($this->t['l'] . '_ID'),
        );
    }
}

?>
