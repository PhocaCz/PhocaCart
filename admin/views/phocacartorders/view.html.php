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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocacartOrders extends HtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $filterForm;
    public $activeFilters;
    protected $t;
    protected $r;
    protected $s;

    function display($tpl = null)
    {

        $document            = Factory::getDocument();
        $this->t             = PhocacartUtils::setVars('order');
        $this->r             = new PhocacartRenderAdminviews();
        $this->s             = PhocacartRenderStyle::getStyles();
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');


        $this->t['filter-ps-opened'] = 0;
        if ((int)$this->state->get('filter.shipping_id') > 0 || (int)$this->state->get('filter.payment_id') > 0) {
               $this->t['filter-ps-opened'] = 1;
        }


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
        HTMLHelper::stylesheet($this->t['bootstrap'] . 'css/bootstrap.glyphicons-icons-only.min.css');

        $this->t['plugin-pdf']    = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
        $this->t['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');


        $this->addToolbar();
        parent::display($tpl);
    }

    function addToolbar()
    {

        require_once JPATH_COMPONENT . '/helpers/' . $this->t['tasks'] . '.php';
        $state = $this->get('State');
        $class = ucfirst($this->t['tasks']) . 'Helper';
        $canDo = $class::getActions($this->t, $state->get('filter.order_id'));

        ToolbarHelper::title(Text::_($this->t['l'] . '_ORDERS'), 'cart');

        if ($canDo->get('core.create')) {
            //JToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList($this->t['task'] . '.edit', 'JTOOLBAR_EDIT');
        }
        if ($canDo->get('core.edit.state')) {

            ToolbarHelper::divider();
            ToolbarHelper::custom($this->t['tasks'] . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::custom($this->t['tasks'] . '.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        }

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList($this->t['l'] . '_WARNING_DELETE_ITEMS', 'phocacartorders.delete', $this->t['l'] . '_DELETE');
        }



        if ((int)$this->state->get('filter.shipping_id') > 0) {
            //ToolbarHelper::custom($this->t['tasks'] . '.exportshipping', 'share.png', 'share.png', 'COM_PHOCACART_EXPORT_SHIPPING', true);
            $bar 	= Toolbar::getInstance('toolbar');
            $dhtml = '<joomla-toolbar-button><button class="btn btn-primary btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_EXPORT_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_INFO_SHIPPING_EXPORT').'\')){Joomla.submitbutton(\'phocacartorders.exportshipping\');}}" ><i class="icon-share" title="'.Text::_('COM_PHOCACART_EXPORT_SHIPPING').'"></i> '.Text::_('COM_PHOCACART_EXPORT_SHIPPING').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields()
    {
        return array(
            //'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
            'order_number' => Text::_($this->t['l'] . '_ORDER_NUMBER'),
            'user_username' => Text::_($this->t['l'] . '_USER'),
            'a.status_id' => Text::_($this->t['l'] . '_STATUS'),
            'total_amount' => Text::_($this->t['l'] . '_TOTAL'),
            'a.date' => Text::_($this->t['l'] . '_DATE_ADDED'),
            'a.modified' => Text::_($this->t['l'] . '_DATE_MODIFIED'),
            'a.notify' => Text::_($this->t['l'] . '_USER_NOTIFIED'),
            'a.published' => Text::_($this->t['l'] . '_PUBLISHED'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        );
    }
}
