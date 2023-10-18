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
$r         = $this->r;
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', $this->t['o']);
$saveOrder = false;

$saveOrderingUrl = '';

$sortFields = $this->getSortFields();
//echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
//echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
//echo $r->endFilter();

echo $r->startMainContainer();
/*
echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();*/


echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder, 'a', true);
echo $r->secondColumnHeader($listDirn, $listOrder, 'a', true);
echo '<th class="ph-product">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PRODUCT', 'a.product_id', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-item">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ITEM', 'a.item', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-user">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_USER', 'a.user_id', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-ip">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_IP', 'a.ip', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-date">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DATE', 'a.date', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-hits">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_HITS', 'a.hits', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-type">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TYPE', 'a.type', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr     = "";
$j              = 0;

$price = new PhocacartPrice();

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        //if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
        $j++;

        $urlEdit = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'] . '.edit&id=';
        $urlTask = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
        //$orderkey   	= array_search($item->id, $this->ordering[$item->catid]);
        /*$orderkey		= 0;
        if ($this->t['ordering'] && !empty($this->ordering)) {
            $orderkey   	= array_search($item->id, $this->ordering[$this->t['catid']]);
        }
        $ordering		= ($listOrder == 'a.ordering');
        $canCreate		= $user->authorise('core.create', $this->t['o']);
        $canEdit		= $user->authorise('core.edit', $this->t['o']);
        $canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
        $canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
        $linkEdit 		= Route::_( $urlEdit. $item->id );

        //$linkCat	= Route::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
        $canEditCat	= $user->authorise('core.edit', $this->t['o']);*/

        $this->t['ordering'] = 0;
        $item->ordering     = 0;
        $orderkey		    = 0;
        $canChange          = 0;

        echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);



        echo $r->td($this->escape($item->product_title), "small");
        echo $r->td($this->escape($item->item), "small");

        // Name
        $nameSuffix = '';
        if (isset($item->user_username) && $item->user_username != '') {
            $nameSuffix = ' <small>(' . $this->escape($item->user_username) . ')</small>';
        }
        echo $r->td($this->escape($item->user_name) . $nameSuffix, "small");

        echo $r->td($this->escape($item->ip), "small");


        echo $r->td($item->date, "small");
        echo $r->td($item->hits, "small");

        $typeTxt = PhocacartUtilsSettings::getAdditionalHitsType($item->type);
        echo $r->td($typeTxt, "small");
        echo $r->td($item->id, "small");

        echo $r->endTr();

        //}
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 16);
echo $r->endTable();


echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();


?>
