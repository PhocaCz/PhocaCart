<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
$r               = $this->r;
$user            = Factory::getUser();
$userId          = $user->get('id');
$listOrder       = $this->escape($this->state->get('list.ordering'));
$listDirn        = $this->escape($this->state->get('list.direction'));
$canOrder        = $user->authorise('core.edit.state', $this->t['o']);
$saveOrder       = $listOrder == 'a.ordering';
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
echo $r->startMainContainer();

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-preview ph-center">' . Text::_('COM_PHOCACART_PREVIEW') . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-movements ph-center">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_STOCK_MOVEMENTS', 'a.stock_movements', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-download ph-center">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DOWNLOAD', 'a.download', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-type ph-center">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DEFAULT', 'a.type', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-id ph-center">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr     = "";
$j              = 0;

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        $j++;

        $urlEdit    = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'] . '.edit&id=';
        $urlTask    = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
        $orderkey   = array_search($item->id, $this->ordering[0]);
        $ordering   = ($listOrder == 'a.ordering');
        $canCreate  = $user->authorise('core.create', $this->t['o']);
        $canEdit    = $user->authorise('core.edit', $this->t['o']);
        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
        $canChange  = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
        $linkEdit   = Route::_($urlEdit . $item->id);


        echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

        $checkO = '';
        if ($item->checked_out) {
            $checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'] . '.', $canCheckin);
        }
        if ($canCreate || $canEdit) {
            $checkO .= '<a href="' . Route::_($linkEdit) . '">' . $this->escape(Text::_($item->title)) . '</a>' . ' <small>(' . $this->escape($item->title) . ')</small>';
        } else {
            $checkO .= $this->escape(Text::_($item->title)) . ' <small>(' . $this->escape($item->title) . ')</small>';
        }
        echo $r->td($checkO, "small", 'th');

        echo $r->td(PhocacartUtilsSettings::getOrderStatusBadge($item->title, $item->params), "small ph-center");

        echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', $canChange), "small");

        echo $r->td($this->escape($item->stock_movements), "small ph-center");

        if ($item->download == 1) {
            $download = '<span class="badge bg-success">' . Text::_('COM_PHOCACART_YES') . '</span>';
        } else {
            $download = '<span class="badge bg-danger">' . Text::_('COM_PHOCACART_NO') . '</span>';
        }
        echo $r->td($download, "small ph-center");

        if ($item->type == 1) {
            $default = '<a data-original-title="' . Text::_('COM_PHOCACART_DEFAULT') . '" class="btn btn-micro disabled jgrid hasTooltip ph-no-btn" title="' . Text::_('COM_PHOCACART_DEFAULT') . '"><i class="icon-featured"></i></a>';
            echo $r->td($default, "small ph-center");
        } else {
            echo $r->td('', "small");
        }
        echo $r->td($item->id, "small ph-center");

        echo $r->endTr();
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 8);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
