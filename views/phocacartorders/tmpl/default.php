<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal', 'a.modal_edit_status');
$class		= $this->t['n'] . 'RenderAdminViews';
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
echo '<th class="ph-order">'.JHTML::_('grid.sort',  	$this->t['l'].'_ORDER_NUMBER', 'a.id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-user">'.JHTML::_('grid.sort',  $this->t['l'].'_USER', 'username', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.JHTML::_('grid.sort',  $this->t['l'].'_STATUS', 'a.status', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-action">'.JText::_($this->t['l'].'_ACTION').'</th>'."\n";
echo '<th class="ph-total-center">'.JHTML::_('grid.sort',  $this->t['l'].'_TOTAL', 'total', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-date">'.JHTML::_('grid.sort',  $this->t['l'].'_DATE_ADDED', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-date">'.JHTML::_('grid.sort',  $this->t['l'].'_DATE_MODIFIED', 'a.modified', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHTML::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-id">'.JHTML::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();
			
echo '<tbody>'. "\n";

$price			= new PhocaCartPrice();
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
$linkStatus 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstatus&tmpl=component&id='.(int)$item->id  );
$linkStatusHandler	= 'rel="{handler: \'iframe\', size: {x: 580, y: 460}, onClose:function(){var js = window.location.reload();}}"';

$linkOrderView 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartorderview&tmpl=component&id='.(int)$item->id.'&type=1' );
$linkInvoiceView 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartorderview&tmpl=component&id='.(int)$item->id.'&type=2' );
$linkDelNoteView 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartorderview&tmpl=component&id='.(int)$item->id.'&type=3' );

//$linkOrderViewHandler= 'rel="{handler: \'iframe\', size: {x: 580, y: 460}}"';
$linkOrderViewHandler= 'onclick="window.open(this.href, \'orderview\', \'width=780,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';



$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="0" >'. "\n";
echo $r->tdOrder($canChange, $saveOrder, $orderkey);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small hidden-phone");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape(PhocaCartOrder::getOrderNumber($item->id)).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small hidden-phone");

if ($item->user_id > 0) {
	$userO = $this->escape($item->user_name);
	if (isset($item->user_username)) {
		$userO .= ' <small>('.$item->user_username.')</small>';
	}
} else {
	$userO = '<span class="label label-info">'.JText::_('COM_PHOCACART_GUEST').'</span>';
}
echo $r->td($userO, "small hidden-phone");

//$status			= PhocaCartOrderStatus::getStatus((int)$item->status_id, $item->id);
//$statusSelect	= JHTML::_('select.genericlist',  $status['data'],  'phorderstatus', 'class="inputbox"', 'value', 'text', (int)$item->status_id, 'phorderstatus'.(int)$item->id );
$status = '<span class="label label-default">'.$this->escape(JText::_($item->status_title)).'</span>';
$status .= ' <a class="modal_edit_status ph-u" href="'.$linkStatus.'" '.$linkStatusHandler.' ><small>'.JText::_('COM_PHOCACART_EDIT_STATUS').'</small></a>';
echo $r->td($status, "small hidden-phone");

$view = '<a href="'.$linkOrderView.'" class="btn btn-success btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_ORDER').'" class="glyphicon glyphicon-search icon-search"></span></a>';
$view .= ' <a href="'.$linkInvoiceView.'" class="btn btn-danger btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_INVOICE').'" class="glyphicon glyphicon-list-alt icon-ph-invoice"></span></a>';
$view .= ' <a href="'.$linkDelNoteView.'" class="btn btn-warning btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_DELIVERY_NOTE').'" class="glyphicon glyphicon-barcode icon-ph-del-note"></span></a>';
echo $r->td($view, "small hidden-phone");

$price->setCurrency($item->currency_id, $item->id);
$total = $price->getPriceFormat($item->total_amount);
echo $r->td($total, "small hidden-phone ph-right ph-p-r-med");

echo $r->td(JHtml::date($item->date, 'd. m. Y h:s'), "small hidden-phone");
echo $r->td(JHtml::date($item->modified, 'd. m. Y h:s'), "small hidden-phone");

echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small hidden-phone");

echo $r->td($item->id, "small hidden-phone");

echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 11);
echo $r->endTable();

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>