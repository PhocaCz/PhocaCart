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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
$class		= $this->t['n'] . 'RenderAdminviews';
$r 			=  new $class();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocaDownloadCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilter();

echo $r->startMainContainer();

if($this->t['enable_logging'] == 0) {
	echo '<div class="alert alert-info">';
	echo '<button type="button" class="close" data-dismiss="alert">×</button>';
	//echo '<h4 class="alert-heading">'.JText::_('COM_PHOCACART_INFO').'</h4>';
	echo '<div class="alert-message">'.JText::_('COM_PHOCACART_LOGGING_IS_DISABLED_AT_THE_MOMENT').'</div>';
	echo '</div>';
}


echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
echo $r->endFilterBar();

echo $r->endFilterBar();

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-date">'.JHtml::_('grid.sort',  	$this->t['l'].'_DATE', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-type">'.JHtml::_('grid.sort',  	$this->t['l'].'_TYPE', 'a.type', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title">'.JHtml::_('grid.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-user">'.JHtml::_('grid.sort',  	$this->t['l'].'_USER', 'username', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-ip">'.JHtml::_('grid.sort',  		$this->t['l'].'_IP', 'a.ip', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-incoming-page">'.JHtml::_('grid.sort',  $this->t['l'].'_INCOMING_PAGE', 'a.incoming_page', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-description">'.JHtml::_('grid.sort',  $this->t['l'].'_MESSAGE', 'a.description', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.JHtml::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo '<tbody>'. "\n";

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



$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="0" >'. "\n";
echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
//echo $r->tdOrder(0, $saveOrder, $orderkey);// ORDERING DISABLED
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");

echo $r->td($item->date, "small");
/*$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}*/

$item->type = 4;
switch($item->type) {
	case 2:
		$type = '<span class="label label-important label-danger">'.JText::_('COM_PHOCACART_ERROR').'</span>';
	break;
	case 3:
		$type = '<span class="label label-warning">'.JText::_('COM_PHOCACART_WARNING').'</span>';
	break;
	case 4:
		$type = '<span class="label label-info">'.JText::_('COM_PHOCACART_NOTICE').'</span>';
	break;
	case 1:
	default:
		$type = '<span class="label label-default">'.JText::_('COM_PHOCACART_GENERAL').'</span>';
	break;

}
echo $r->td($type, "small");

$checkO = $this->escape($item->title);
echo $r->td('<b>'.$checkO.'</b>', "small");

//echo $r->td(JHtml::date($v->date, JText::_('DATE_FORMAT_LC5')), "small");
//echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td($item->user_username, "small");
echo $r->td($item->ip, "small");

// Because of Chrome
$item->incoming_page = str_replace('?', '<wbr>?', $item->incoming_page);
$item->incoming_page = str_replace('&amp;', '<wbr>&amp;', $item->incoming_page);
echo $r->td($item->incoming_page, "small ph-incoming-page");
echo $r->td('<textarea>'.$item->description.'</textarea>');
echo $r->td($item->id, "small");

echo '</tr>'. "\n";

		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 10);
echo $r->endTable();

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
