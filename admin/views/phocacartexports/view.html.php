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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocaCartExports extends HtmlView
{
    protected $t;
    protected $r;

    function display($tpl = null) {

        $this->t                = PhocacartUtils::setVars('export');
        $this->r                = new PhocacartRenderAdminviews();
        $model                  = $this->getModel();
        $this->t['countexport'] = $model->getItemsCountExport(); // count of products ready in export table
        $this->t['count']       = $model->getItemsCountProduct();// count of products


        $paramsC                             = PhocacartUtils::getComponentParameters();
        $this->t['import_export_pagination'] = $paramsC->get('import_export_pagination', 20);

        $this->t['count_pagination'] = 0;
        if ($this->t['count'] > 0) {
            if ((int)$this->t['import_export_pagination'] > (int)$this->t['count'] ||
                (int)$this->t['import_export_pagination'] == (int)$this->t['count']) {
                $this->t['count_pagination'] = 1;

            } else if ((int)$this->t['count'] > (int)$this->t['import_export_pagination'] &&
                (int)$this->t['import_export_pagination'] > 0) {

                $this->t['count_pagination'] = ceil((int)$this->t['count'] / (int)$this->t['import_export_pagination']);
            }
        }


        $media = new PhocacartRenderAdminmedia();
        HTMLHelper::stylesheet($this->t['bootstrap'] . 'css/bootstrap.glyphicons-icons-only.min.css');

        HTMLHelper::_('jquery.framework', false);
        //PhocacartRenderJs::renderOverlayOnSubmit('phFormUpload');

        $this->addToolbar();
        parent::display($tpl);
    }

    function addToolbar() {

        require_once JPATH_COMPONENT . '/helpers/' . $this->t['tasks'] . '.php';
        $state = $this->get('State');
        $class = ucfirst($this->t['tasks']) . 'Helper';
        $canDo = $class::getActions($this->t, $state->get('filter.export'));

        ToolbarHelper::title(Text::_($this->t['l'] . '_EXPORT'), 'sign-out');


        // This button is unnecessary but it is displayed because Joomla! design bug
        $bar   = Toolbar::getInstance('toolbar');
        $dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-primary btn-small"><i class="icon-home-2" title="' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '"></i> ' . Text::_('COM_PHOCACART_CONTROL_PANEL') . '</a>';
        $bar->appendButton('Custom', $dhtml);


        if ($canDo->get('core.edit')) {

        }

        ToolbarHelper::divider();
        ToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields() {
        return array();
    }
}

?>
