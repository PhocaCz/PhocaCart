<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
jimport('joomla.application.component.view');

class PhocaCartCpViewPhocaCartTools extends JViewLegacy
{
    protected $t;
    protected $r;
    protected $rv;

    function display($tpl = null) {

        $this->t                = PhocacartUtils::setVars('tool');
        $this->r                = new PhocacartRenderAdminviews();
        $this->rv               = new PhocacartRenderAdminview();
        $model                  = $this->getModel();
        $this->t['countexport'] = $model->getItemsCountExport(); // count of products ready in export table
        $this->t['count']       = $model->getItemsCountProduct();// count of products


        HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacarttools.js', array('version' => 'auto'));

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
        JHtml::stylesheet($this->t['bootstrap'] . 'css/bootstrap.glyphicons-icons-only.min.css');

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

        JToolbarHelper::title(JText::_($this->t['l'] . '_ADVANCED_TOOLS'), 'tool');


        // This button is unnecessary but it is displayed because Joomla! design bug
        $bar   = JToolbar::getInstance('toolbar');
        $dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-small"><i class="icon-home-2" title="' . JText::_('COM_PHOCACART_CONTROL_PANEL') . '"></i> ' . JText::_('COM_PHOCACART_CONTROL_PANEL') . '</a>';
        $bar->appendButton('Custom', $dhtml);


        if ($canDo->get('core.edit')) {

        }

        JToolbarHelper::divider();
        JToolbarHelper::help('screen.' . $this->t['c'], true);
    }

    protected function getSortFields() {
        return array();
    }
}

?>
