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
$r 			= $this->r;
$user		= Factory::getUser();
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

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title-small">'.HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title-small">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_ORDER_NUMBER', 'a.order_id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title-small">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_USER', 'u.name', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-filename">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_FILENAME', 'a.download_file', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.Text::_( $this->t['l'].'_STATUS' ).'</th>'."\n";
echo '<th class="ph-date">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_DATE', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-download">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_DOWNLOADS', 'a.download_hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

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
$linkEdit 		= Route::_( $urlEdit. $item->id );



$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";

echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
echo $r->td(HTMLHelper::_('grid.id', $i, $item->id), "small");


$checkO = '';
if ($item->checked_out) {
	$checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. Route::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small", 'th');


echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

/*
$rules = array();
if($item->active_amount) {$rules[] = '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_AMOUNT_RULE'). '</span>';}
if($item->active_country) {$rules[] = '<span class="label label-warning badge bg-warning label-warning">'.Text::_('COM_PHOCACART_COUNTRY_RULE'). '</span>';}
if($item->active_region) {$rules[] = '<span class="label label-info badge bg-info label-info">'.Text::_('COM_PHOCACART_REGION_RULE'). '</span>';}
if($item->active_weight) {$rules[] = '<span class="label label-success badge bg-success label-success">'.Text::_('COM_PHOCACART_WEIGHT_RULE'). '</span>';}


echo $r->td(implode(" ", $rules), "small");*/

echo $r->td($this->escape(PhocacartOrder::getOrderNumber($item->order_id, $item->order_date, $item->order_number)), "small");

$userO = $this->escape($item->user_name);
if (isset($item->user_username)) {
	$userO .= ' <small>('.$item->user_username.')</small>';
}
echo $r->td($userO, "small");

echo $r->td($item->download_file, "small");


$status = '';
if((int)$this->t['download_count'] > 0 && ((int)$this->t['download_count'] == (int)$item->download_hits || (int)$this->t['download_count'] < (int)$item->download_hits)) {
	$status .= '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_MAXIMUM_DOWNLOADS_REACHED'). '</span>';

}

if((int)$this->t['download_days'] > 0 && !PhocacartDownload::isActive($item->date, $this->t['download_days'])) {
	$status .= '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_DOWNLOAD_DATE_EXPIRED'). '</span>';

}

if ($status == '') {
	$status = '<span class="label label-success badge bg-success label-success">'.Text::_('COM_PHOCACART_ACTIVE'). '</span>';
}
//COM_PHOCACART_DOWNLOAD_DATE_EXPIRED
echo $r->td($status, "small");
//echo $r->td($item->date, "small");
echo $r->td(HTMLHelper::date($item->date, Text::_('DATE_FORMAT_LC5')), "small");
echo $r->td($item->download_hits, "small");
echo $r->td($item->id, "small");

echo $r->endTr();

		//}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 11);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
