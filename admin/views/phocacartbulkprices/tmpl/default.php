<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
$r 			= $this->r;
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
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
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
echo $r->endFilterBar();

echo $r->endFilterBar();*/

echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));

$idMdRun       = 'phEditBulkPriceRunModal';
$idMdRevert       = 'phEditBulkPriceRevertModal';
$textButtonRun = 'COM_PHOCACART_RUN';
$textButtonRevert = 'COM_PHOCACART_REVERT';
$w          = 500;
$h          = 400;

$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMdRun, $textButtonRun, $w, $h, true);
echo $rV->modalWindowDynamic($idMdRevert, $textButtonRevert, $w, $h, true);

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title2">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-desc">'.Text::_('COM_PHOCACART_DESCRIPTION').'</th>'."\n";
echo '<th class="ph-desc">'.Text::_('COM_PHOCACART_CALCULATION').'</th>'."\n";
echo '<th class="ph-status">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_STATUS', 'a.status', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-action">'.Text::_('COM_PHOCACART_ACTION').'</th>'."\n";
echo '<th class="ph-date">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_DATE', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		//if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$urlTask		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'];
$orderkey   	= array_search($item->id, $this->ordering[0]);
$ordering		= ($listOrder == 'a.ordering');
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit. $item->id );
$linkRun        = JRoute::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditbulkprice&status='.(int)$item->status.'&tmpl=component&id=' . (int)$item->id);
$linkRevert     = JRoute::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditbulkprice&status='.(int)$item->status.'&tmpl=component&id=' . (int)$item->id);



echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

$checkO = '';
if ($item->checked_out) {
	$checkO .= Joomla\CMS\HTML\HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small");


echo $r->td($item->description, "small");

$registry = new JRegistry;
$registry->loadString($item->params);
$item->params = $registry;
$amount = $item->params->get('amount', '');
$operator = $item->params->get('operator', '');
$calculation_price = $item->params->get('calculation_type', '');

if ($calculation_price == 1) {
	$calculation_price = '%';
} else {
	$calculation_price = '';
}
echo $r->td($operator . $amount . $calculation_price, "small");

$status = '';
if ($item->status == 1) {
	$status .= '<span class="label label-success label-success badge badge-success">'.JText::_('COM_PHOCACART_ACTIVE'). '</span>';
} else {
	$status .= '<span class="label label-important label-danger badge badge-danger">'.JText::_('COM_PHOCACART_INACTIVE'). '</span>';
}

echo $r->td($status, "small");

$action = '';
if ($item->status == 0) {
	$action .= ' <span><a href="#' . $idMdRun . '" role="button" class="btn btn-success ' . $idMdRun . 'ModalButton" data-toggle="modal" title="' . JText::_($textButtonRun) . '" data-src="' . $linkRun . '" data-height="' . $h . '" data-width="' . $w . '">' . JText::_($textButtonRun) . '</a></span>';
}

if ($item->status == 1) {
	$action .= ' <span><a href="#' . $idMdRevert . '" role="button" class="btn btn-danger ' . $idMdRevert . 'ModalButton" data-toggle="modal" title="' . JText::_($textButtonRevert) . '" data-src="' . $linkRevert . '" data-height="' . $h . '" data-width="' . $w . '">' . JText::_($textButtonRevert) . '</a></span>';
}
echo $r->td($action, "small");

echo $r->td($item->date, "small");

echo $r->td($item->id, "small");




echo $r->endTr();

		//}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 12);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
