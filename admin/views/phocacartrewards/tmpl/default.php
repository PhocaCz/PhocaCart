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

$userNameSelected = isset($this->items[0]->user_name_selected) ? $this->items[0]->user_name_selected : '';
echo $r->inputFilterUser($this->t['l'].'_FILTER_USER_LABEL', $this->t['l'].'_FILTER_USER_DESC',
							$this->escape($this->state->get('filter.user')), $userNameSelected);
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR', array(0 => 'field-user-input'));
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
echo '<th class="ph-published">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_APPROVED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title-small">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_USER', 'u.name', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-points">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_REWARD_POINTS', 'a.points', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-points">'.Text::_($this->t['l'].'_TOTAL_POINTS').'</th>'."\n";
echo '<th class="ph-date">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_DATE', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;


$reward = new PhocacartReward();

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
echo $r->td($checkO, "small");


echo $r->td(PhocacartHtmlJgrid::approve($item->published, $i, $this->t['tasks'].'.', $canChange), "small");


$userO = $this->escape($item->user_name);
if (isset($item->user_username)) {
	$userO .= ' <small>('.$item->user_username.')</small>';
}
echo $r->td($userO, "small");


$approvedClass = 'approved';
if ($item->published == 0) {
	$approvedClass = 'not-approved';
}

if ($item->type == 1) {
	echo $r->td('<span class="label label-success badge bg-success '.$approvedClass.'">'.$item->points.'</span>', "small");
} else if ($item->type == -1) {
	echo $r->td('<span class="label label-important label-danger badge bg-danger '.$approvedClass.'">'.$item->points.'</span>', "small");
} else {
	echo $r->td('<span class="label badge bg-primary">'.$item->points.'</span>', "small");
}


$total = $reward->getTotalPointsByUserId($item->user_id);
echo $r->td($total, "small");



echo $r->td($item->date, "small");
echo $r->td($item->id, "small");

echo $r->endTr();

		//}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 9);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
