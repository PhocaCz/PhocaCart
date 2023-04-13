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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
$r         = $this->r;
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', $this->t['o']);
$saveOrder = $listOrder == 'a.ordering';
$adminDesc = new PhocacartUtilsAdmindescription();
$nrColumns = $adminDesc->isActive() ? 11 : 10;

$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
//echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocaDownloadCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
//echo $r->endFilter();

echo $r->startMainContainer();
/*
echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'] . '_FILTER_SEARCH_LABEL', $this->t['l'] . '_FILTER_SEARCH_DESC',
    $this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
echo $r->endFilterBar();

echo $r->endFilterBar();*/

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title-small">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-default">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DEFAULT', 'a.default', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-method">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PAYMENT_METHOD', 'a.method', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-price">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PRICE', 'a.cost', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-rule">' . Text::_($this->t['l'] . '_ACTIVE_RULE_S') . '</th>' . "\n";
echo $adminDesc->isActive() ? '<th class="ph-description-small">' . Text::_($this->t['l'] . '_DESCRIPTION') . '</th>' . "\n" : '';
echo '<th class="ph-access">' . Text::_($this->t['l'] . '_ACCESS') . '</th>' . "\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr     = "";
$j              = 0;

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        //if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
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
            $checkO .= '<a href="' . Route::_($linkEdit) . '">' . $this->escape($item->title) . '</a>';
        } else {
            $checkO .= $this->escape($item->title);
        }
        echo $r->td($checkO, "small", 'th');


        echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', $canChange), "small");

        if ($item->default == '0' || $item->default == '1') {
            $default = HTMLHelper::_('jgrid.isdefault', $item->default, $i, $this->t['tasks'] . '.', $canChange);

        } else if ($canChange) {
            $default = '<a href="' . Route::_('index.php?option=com_phocacart&task=' . $this->t['tasks'] . '.unsetDefault&cid[]=' . $item->id . '&' . Session::getFormToken() . '=1') . '">';
        }
        echo $r->td($default, "small");

        //$method = PhocacartUtilsSettings::getPaymentMethod($item->method);
        echo $r->td(Text::_($item->method), "small");

        echo $r->td('<span class="ph-editinplace-text ph-eip-text ph-eip-price" id="payment_methods:cost:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->cost) . '</span>', "small");

        $rules = array();
        if ($item->active_amount) {
            $rules[] = '<span class="label label-important label-danger badge bg-danger">' . Text::_('COM_PHOCACART_AMOUNT_RULE') . '</span>';
        }
        if ($item->active_country) {
            $rules[] = '<span class="label label-warning badge bg-warning label-warning">' . Text::_('COM_PHOCACART_COUNTRY_RULE') . '</span>';
        }
        if ($item->active_region) {
            $rules[] = '<span class="label label-info badge bg-info label-info">' . Text::_('COM_PHOCACART_REGION_RULE') . '</span>';
        }
        if ($item->active_shipping) {
            $rules[] = '<span class="label label-success badge bg-success label-success">' . Text::_('COM_PHOCACART_SHIPPING_RULE') . '</span>';
        }

        if ($item->active_zone) {
            $rules[] = '<span class="label label-primary badge bg-primary label-primary">' . Text::_('COM_PHOCACART_ZONE_RULE') . '</span>';
        }

        if ($item->active_currency) {
            $rules[] = '<span class="label label-primary badge bg-success label-success">' . Text::_('COM_PHOCACART_CURRENCY_RULE') . '</span>';
        }


        echo $r->td(implode(" ", $rules), "small");

        if ($adminDesc->isActive()) {
            echo $r->td($this->escape($adminDesc->getAdminDescription($item->description)));
        }

        echo $r->td($this->escape($item->access_level));

        echo $r->td($item->id, "small");

        echo $r->endTr();

        //}
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), $nrColumns);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
