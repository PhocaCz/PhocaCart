<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Phoca\Render;

defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Version;


class Adminviews
{
    public $view        = '';
    public $viewtype    = 1;
    public $option      = '';
    public $optionLang  = '';
    public $tmpl        = '';
    public $compatible  = false;
    public $sidebar     = true;
    protected $document	= false;

    public function __construct() {

        $app              = Factory::getApplication();
        $version          = new Version();
        $this->compatible = $version->isCompatible('4.0.0-alpha');
        $this->view       = $app->input->get('view');
        $this->option     = $app->input->get('option');
        $this->optionLang = strtoupper($this->option);
        $this->tmpl       = $app->input->get('tmpl');
        $this->document   = Factory::getDocument();


        $this->sidebar = Factory::getApplication()->getTemplate(true)->params->get('menu', 1) ? true : false;


        /* switch($this->view) {

             case 2:
                 HTMLHelper::_('behavior.keepalive');
                 if (!$this->compatible) {
                     HTMLHelper::_('formbehavior.chosen', 'select');
                 }
             break;

             case 1:
             default:*/

				HTMLHelper::_('bootstrap.tooltip');
				HTMLHelper::_('behavior.multiselect');
				HTMLHelper::_('dropdown.init');
				if (!$this->compatible) {
					HTMLHelper::_('formbehavior.chosen', 'select');
				}

        //	break;
        //}

        // Modal
        if ($this->tmpl == 'component') {

            HTMLHelper::_('behavior.core');
            HTMLHelper::_('behavior.polyfill', array('event'), 'lt IE 9');
            HTMLHelper::_('script', 'media/' . $this->option . '/js/administrator/admin-phocaitems-modal.min.js', array('version' => 'auto', 'relative' => true));
            HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
            HTMLHelper::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));

        }

        HTMLHelper::_('stylesheet', 'media/' . $this->option . '/duotone/joomla-fonts.css', array('version' => 'auto'));
        HTMLHelper::_('stylesheet', 'media/' . $this->option . '/css/administrator/' . str_replace('com_', '', $this->option) . '.css', array('version' => 'auto'));

        if ($this->compatible) {
            HTMLHelper::_('stylesheet', 'media/' . $this->option . '/css/administrator/4.css', array('version' => 'auto'));
        } else {
            HTMLHelper::_('stylesheet', 'media/' . $this->option . '/css/administrator/3.css', array('version' => 'auto'));
        }
    }


    public function startMainContainer($id = 'phAdminView', $class = 'ph-admin-box') {

        $o = array();

        if ($this->compatible) {

            // Joomla! 4

            $o[] = '<div class="row">';
            if ($this->sidebar) {

                $o[] = '<div id="j-main-container" class="col-md-12">';
            } else {

                $o[] = '<div id="j-sidebar-container" class="col-md-2">' . \JHtmlSidebar::render() . '</div>';
                $o[] = '<div id="j-main-container" class="col-md-10">';
            }


        } else {
            $o[] = '<div id="j-sidebar-container" class="span2">' . \JHtmlSidebar::render() . '</div>';
            $o[] = '<div id="j-main-container" class="span10">';
        }

        return implode("\n", $o);
    }

    public function endMainContainer() {
        $o = array();

        $o[] = '</div>';
        if ($this->compatible) {
            $o[] = '</div>';
        }
        return implode("\n", $o);
    }

    public function jsJorderTable($listOrder) {

        $js = 'Joomla.orderTable = function() {' . "\n"
            . '  table = document.getElementById("sortTable");' . "\n"
            . '  direction = document.getElementById("directionTable");' . "\n"
            . '  order = table.options[table.selectedIndex].value;' . "\n"
            . '  if (order != \'' . $listOrder . '\') {' . "\n"
            . '    dirn = \'asc\';' . "\n"
            . '	} else {' . "\n"
            . '    dirn = direction.options[direction.selectedIndex].value;' . "\n"
            . '  }' . "\n"
            . '  Joomla.tableOrdering(order, dirn, \'\');' . "\n"
            . '}' . "\n";
        Factory::getDocument()->addScriptDeclaration($js);
    }

    public function startForm($option, $view, $id = 'adminForm', $name = 'adminForm') {
        return '<div id="' . $view . '"><form action="' . Route::_('index.php?option=' . $option . '&view=' . $view) . '" method="post" name="' . $name . '" id="' . $id . '">' . "\n" . '';
    }

    public function startFormModal($option, $view, $id = 'adminForm', $name = 'adminForm', $function = '') {

        return '<div id="' . $view . '"><form action="' . Route::_('index.php?option=' . $option . '&view=' . $view . '&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1') . '" method="post" name="' . $name . '" id="' . $id . '">' . "\n" . '';
    }

    public function endForm() {
        return '</form>' . "\n" . '' . "\n" . $this->ajaxTopHtml();
    }

    public function ajaxTopHtml($text = '') {
        $o = '<div id="ph-ajaxtop">';
        if ($text != '') {
            $o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> ' . strip_tags(addslashes($text)) . '</div>';
        }
        $o .= '</div>';
        return $o;
    }

    /* Modal */
    public function startMainContainerNoSubmenu() {
        //return '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10">'. "\n";
        $o = '<div id="j-main-container" class="col-xs-12 col-sm-12 col-md-12 ph-admin-box-content ph-admin-manage">' . "\n";
        $o .= '<div id="ph-system-message-container"></div>' . "\n";// specific container for moving messages from joomla to phoca
        //$this->moveSystemMessageFromJoomlaToPhoca();
        return $o;
    }

    public function moveSystemMessageFromJoomlaToPhoca() {

        $s = array();
        //$s[] = 'document.getElementById("system-message-container").style.display = "none";';
        $s[] = 'jQuery(document).ready(function() {';
        //$s[] = '   jQuery("#system-message-container").removeClass("j-toggle-main");';
        $s[] = '   jQuery("#system-message-container").css("display", "none");';
        $s[] = '   var phSystemMsg = jQuery("#system-message-container").html();';
        $s[] = '   jQuery("#ph-system-message-container").html(phSystemMsg);';
        $s[] = '});';
        Factory::getDocument()->addScriptDeclaration(implode("\n", $s));
    }

    public function startTable($id) {
        return '<table class="table table-striped" id="' . $id . '">' . "\n";
    }

    public function endTable() {
        return '</table>' . "\n";
    }

    public function tblFoot($listFooter, $columns) {
        return '<tfoot>' . "\n" . '<tr><td colspan="' . (int)$columns . '">' . $listFooter . '</td></tr>' . "\n" . '</tfoot>' . "\n";
    }

    public function startTblHeader() {
        return '<thead>' . "\n" . '<tr>' . "\n";
    }

    public function endTblHeader() {
        return '</tr>' . "\n" . '</thead>' . "\n";
    }

    public function thOrderingXML($txtHo, $listDirn, $listOrder, $prefix = 'a', $empty = false) {

        if ($empty) {
            return '<th class="nowrap center ph-ordering"></th>' . "\n";
        }

        return '<th class="nowrap center ph-ordering">' . "\n"
            . HTMLHelper::_('searchtools.sort', '', strip_tags($prefix) . '.ordering', $listDirn, $listOrder, null, 'asc', $txtHo, 'icon-menu-2') . "\n"
            . '</th>';
        //HTMLHelper::_('searchtools.sort', $this->t['l'].'_IN_STOCK', 'a.stock', $listDirn, $listOrder ).'</th>'."\n";

    }

    public function thCheck($txtCh) {
        return '<th class=" ph-check">' . "\n"
            . '<input type="checkbox" name="checkall-toggle" value="" title="' . Text::_($txtCh) . '" onclick="Joomla.checkAll(this)" />' . "\n"
            . '</th>' . "\n";
    }

    public function tdOrder($canChange, $saveOrder, $orderkey, $ordering = 0, $catOrderingEnabled = true) {

        $o = '<td class="order nowrap center ">' . "\n";
        if ($canChange) {
            $disableClassName = '';
            $disabledLabel    = '';
            if (!$saveOrder) {
                $disabledLabel    = Text::_('JORDERINGDISABLED');
                $disableClassName = 'inactive tip-top';
            }
            if (!$catOrderingEnabled && !$saveOrder) {
                //$disableClassName = 'inactive tip-top';
                $disabledLabel = Text::_($this->optionLang . '_SELECT_CATEGORY_TO_ORDER_ITEMS');
            }
            $o .= '<span class="sortable-handler hasTooltip ' . $disableClassName . '" title="' . $disabledLabel . '"><i class="icon-menu"></i></span>' . "\n";
        } else {
            $o .= '<span class="sortable-handler inactive"><i class="icon-menu"></i></span>' . "\n";
        }
        $orderkeyPlus = $ordering; //$orderkey + 1;
        $o            .= '<input type="text" style="display:none" name="order[]" size="5" value="' . $orderkeyPlus . '" />' . "\n"
            . '</td>' . "\n";
        return $o;
    }

    public function tdRating($ratingAvg) {
        $o            = '<td class="small ">';
        $voteAvg      = round(((float)$ratingAvg / 0.5)) * 0.5;
        $voteAvgWidth = 16 * $voteAvg;
        $o            .= '<ul class="star-rating-small">'
            . '<li class="current-rating" style="width:' . $voteAvgWidth . 'px"></li>'
            . '<li><span class="star1"></span></li>';

        for ($ir = 2; $ir < 6; $ir++) {
            $o .= '<li><span class="stars' . $ir . '"></span></li>';
        }
        $o .= '</ul>';
        $o .= '</td>' . "\n";
        return $o;
    }

    public function tdLanguage($lang, $langTitle, $langTitleE) {

        $o = '<td class="small nowrap ">';
        if ($lang == '*') {
            $o .= Text::_('JALL');
        } else {
            if ($langTitle) {
                $o .= $langTitleE;
            } else {
                $o .= Text::_('JUNDEFINED');
            }
        }
        $o .= '</td>' . "\n";
        return $o;
    }

    public function tdEip($id, $value, $params = array()) {

        $classBox = isset($params['classbox']) ? $params['clasbox'] : 'small';
        $classEip = isset($params['classeip']) ? $params['classeip'] : 'ph-editinplace-text ph-eip-text ph-eip-price';

        $o   = array();
        $o[] = '<td class="' . $classBox . '">';
        $o[] = '<span class="' . $classEip . '" id="' . $id . '">' . $value . '</span>';
        $o[] = '</td>';

        return implode("\n", $o);
    }


    public function formInputsXml($listOrder, $listDirn, $originalOrders) {

        return '<input type="hidden" name="task" value="" />' . "\n"
            . '<input type="hidden" name="boxchecked" value="0" />' . "\n"
            //.'<input type="hidden" name="filter_order" value="'.$listOrder.'" />'. "\n"
            //.'<input type="hidden" name="filter_order_Dir" value="'.$listDirn.'" />'. "\n"
            . HTMLHelper::_('form.token') . "\n"
            . '<input type="hidden" name="original_order_values" value="' . implode(',', $originalOrders) . '" />' . "\n";
    }

    public function td($value, $class = '') {
        if ($class != '') {
            return '<td class="' . $class . '">' . $value . '</td>' . "\n";
        } else {
            return '<td>' . $value . '</td>' . "\n";
        }
    }

    public function tdPublishDownUp($publishUp, $publishDown) {

        $o  = '';
        $db = Factory::getDBO();
        //$app			= Factory::getApplication();
        $nullDate     = $db->getNullDate();
        $now          = Factory::getDate();
        $config       = Factory::getConfig();
        $publish_up   = Factory::getDate($publishUp);
        $publish_down = Factory::getDate($publishDown);
        $tz           = new \DateTimeZone($config->get('offset'));
        $publish_up->setTimezone($tz);
        $publish_down->setTimezone($tz);


        if ($now->toUnix() <= $publish_up->toUnix()) {
            $text = Text::_($this->optionLang . '_PENDING');
        } else if (($now->toUnix() <= $publish_down->toUnix() || $publishDown == $nullDate)) {
            $text = Text::_($this->optionLang . '_ACTIVE');
        } else if ($now->toUnix() > $publish_down->toUnix()) {
            $text = Text::_($this->optionLang . '_EXPIRED');
        }

        $times = '';
        if (isset($publishUp)) {
            if ($publishUp == $nullDate) {
                $times .= Text::_($this->optionLang . '_START') . ': ' . Text::_($this->optionLang . '_ALWAYS');
            } else {
                $times .= Text::_($this->optionLang . '_START') . ": " . $publish_up->format("D, d M Y H:i:s");
            }
        }
        if (isset($publishDown)) {
            if ($publishDown == $nullDate) {
                $times .= "<br />" . Text::_($this->optionLang . '_FINISH') . ': ' . Text::_($this->optionLang . '_NO_EXPIRY');
            } else {
                $times .= "<br />" . Text::_($this->optionLang . '_FINISH') . ": " . $publish_down->format("D, d M Y H:i:s");
            }
        }

        if ($times) {
            $o .= '<td align="center">'
                . '<span class="editlinktip hasTip" title="' . Text::_($this->optionLang . '_PUBLISH_INFORMATION') . '::' . $times . '">'
                . '<a href="javascript:void(0);" >' . $text . '</a></span>'
                . '</td>' . "\n";
        } else {
            $o .= '<td></td>' . "\n";
        }
        return $o;
    }


    public function saveOrder($t, $listDirn) {

        $saveOrderingUrl = 'index.php?option=' . $t['o'] . '&task=' . $t['tasks'] . '.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
        if ($this->compatible) {
            HTMLHelper::_('draggablelist.draggable');
        } else {
            HTMLHelper::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
        }

        return $saveOrderingUrl;
    }

    public function firstColumnHeader($listDirn, $listOrder, $prefix = 'a', $empty = false) {
        if ($this->compatible) {
            return '<th class="w-1 text-center ph-check">' . HTMLHelper::_('grid.checkall') . '</td>';
        } else {
            return $this->thOrderingXML('JGRID_HEADING_ORDERING', $listDirn, $listOrder, $prefix, $empty);
        }
    }

    public function secondColumnHeader($listDirn, $listOrder, $prefix = 'a', $empty = false) {
        if ($this->compatible) {
            return $this->thOrderingXML('JGRID_HEADING_ORDERING', $listDirn, $listOrder, $prefix, $empty);
        } else {
            return $this->thCheck('JGLOBAL_CHECK_ALL');
        }
    }


    public function startTblBody($saveOrder, $saveOrderingUrl, $listDirn) {

        $o = array();

        if ($this->compatible) {
            $o[] = '<tbody';
            if ($saveOrder) {
                $o[] = ' class="js-draggable" data-url="' . $saveOrderingUrl . '" data-direction="' . strtolower($listDirn) . '" data-nested="true"';
            }
            $o[] = '>';

        } else {
            $o[] = '<tbody>' . "\n";
        }

        return implode("", $o);
    }

    public function endTblBody() {
        return '</tbody>' . "\n";
    }

    public function startTr($i, $catid = 0) {
        $iD = $i % 2;
        if ($this->compatible) {
            return '<tr class="row' . $iD . '" data-dragable-group="' . $catid . '">' . "\n";
        } else {

            return '<tr class="row' . $iD . '" sortable-group-id="' . $catid . '" >' . "\n";
        }
    }

    public function endTr() {
        return '</tr>' . "\n";
    }

    public function firstColumn($i, $itemId, $canChange, $saveOrder, $orderkey, $ordering, $catOrderingEnabled = true) {
        if ($this->compatible) {
            return $this->td(HTMLHelper::_('grid.id', $i, $itemId), 'text-center');
        } else {
            return $this->tdOrder($canChange, $saveOrder, $orderkey, $ordering, $catOrderingEnabled);
        }
    }

    public function secondColumn($i, $itemId, $canChange, $saveOrder, $orderkey, $ordering, $catOrderingEnabled = true) {

        if ($this->compatible) {

            $o   = array();
            $o[] = '<td class="text-center d-none d-md-table-cell">';

            $iconClass = '';
            if (!$canChange) {
                $iconClass = ' inactive';
            } else if (!$saveOrder) {
                $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
            } else if (!$catOrderingEnabled) {
                $iconClass = ' inactive" title="' . Text::_($this->optionLang . '_SELECT_CATEGORY_TO_ORDER_ITEMS');
            }

            $o[] = '<span class="sortable-handler' . $iconClass . '"><span class="fas fa-ellipsis-v" aria-hidden="true"></span></span>';

            if ($canChange && $saveOrder) {
                $o[] = '<input type="text" name="order[]" size="5" value="' . $ordering . '" class="width-20 text-area-order hidden">';
            }

            $o[] = '</td>';

            return implode("", $o);

        } else {
            return $this->td(HTMLHelper::_('grid.id', $i, $itemId), "small ");
        }
    }
}
?>
