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

$d = new PhocacartPrice();
$d->setCurrency(1,6);

$b = new PhocacartPrice();

$b->setCurrency(1,0);



JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
//JHtml::_('behavior.modal', 'a.modal_edit_status');
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


$idMd = 'phEditStatusModal';
$textButton = 'COM_PHOCACART_EDIT_STATUS';
$w = 500;
$h = 400;

$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMd, $textButton, $w, $h, true);

	

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-order">'.JHtml::_('grid.sort',  	$this->t['l'].'_ORDER_NUMBER', 'a.id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-user">'.JHtml::_('grid.sort',  $this->t['l'].'_USER', 'username', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.JHtml::_('grid.sort',  $this->t['l'].'_STATUS', 'a.status', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-action">'.JText::_($this->t['l'].'_INFO').'</th>'."\n";
echo '<th class="ph-action">'.JText::_($this->t['l'].'_ACTION').'</th>'."\n";
echo '<th class="ph-total-center">'.JHtml::_('grid.sort',  $this->t['l'].'_TOTAL', 'total', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-date">'.JHtml::_('grid.sort',  $this->t['l'].'_DATE_ADDED', 'a.date', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-date">'.JHtml::_('grid.sort',  $this->t['l'].'_DATE_MODIFIED', 'a.modified', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-id">'.JHtml::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();
			
echo '<tbody>'. "\n";

$price			= new PhocacartPrice();
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
echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape(PhocacartOrder::getOrderNumber($item->id, $item->date, $item->order_number)).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small");

if ($item->user_id > 0) {
	$userO = $this->escape($item->user_name);
	if (isset($item->user_username)) {
		$userO .= ' <small>('.$item->user_username.')</small>';
	}
} else {
	
	$userO = '<span class="label label-info">'.JText::_('COM_PHOCACART_GUEST').'</span>';
	$userData = PhocacartOrder::getOrderCusomerData($item->id);
	
	if (isset($userData[0]['name_first']) && isset($userData[0]['name_last'])) {
		$userO .= '<br /><span>'. $userData[0]['name_first'] .' ' . $userData[0]['name_last'].'</span>';
	}
}
echo $r->td($userO, "small");

//$status			= PhocacartOrderStatus::getStatus((int)$item->status_id, $item->id);
//$statusSelect	= JHtml::_('select.genericlist',  $status['data'],  'phorderstatus', 'class="inputbox"', 'value', 'text', (int)$item->status_id, 'phorderstatus'.(int)$item->id );
$status = '<span class="label label-default">'.$this->escape(JText::_($item->status_title)).'</span>';
//$status .= ' <a class="modal_edit_status ph-u" href="'.$linkStatus.'" '.$linkStatusHandler.' ><small>'.JText::_('COM_PHOCACART_EDIT_STATUS').'</small></a>';

$status .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u ph-no-wrap '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkStatus.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';

echo $r->td($status, "small");


// INFO
$info = '<div class="ph-order-info-box">';
if ($item->type == 2) {
	
	// POS
	if (isset($item->vendor_username) && isset($item->vendor_name)) {
		$vendorO = $this->escape($item->vendor_name);
		$vendorO .= ' <small>('.$item->vendor_username.')</small>';
		$info .= '<span class="label label-success">'.JText::_('COM_PHOCACART_VENDOR').': '.$vendorO.'</span>';
	}
	
	if (isset($item->section_name)) {
		$section = $this->escape($item->section_name);
		$info .= '<span class="label label-primary">'.JText::_('COM_PHOCACART_SECTION').': '.$section.'</span>';
	}
	if (isset($item->unit_name)) {
		$unit = $this->escape($item->unit_name);
		$info .= '<span class="label label-info">'.JText::_('COM_PHOCACART_UNIT').': '.$unit.'</span>';
	}
	if (isset($item->ticket_id) && (int)$item->ticket_id > 0) {
		
		$info .= '<span class="label label-warning">'.JText::_('COM_PHOCACART_TICKET').': '.$item->ticket_id.'</span>';
	}
}
$info .= '</div>';

echo $r->td($info, "small");
// ACTION
$view = '<a href="'.$linkOrderView.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_ORDER').'" class="'.PhocacartRenderIcon::getClassAdmin('search').' ph-icon-success"></span></a>';
$view .= ' <a href="'.$linkInvoiceView.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_INVOICE').'" class="'.PhocacartRenderIcon::getClassAdmin('list-alt').' ph-icon-danger"></span></a>';
$view .= ' <a href="'.$linkDelNoteView.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_DELIVERY_NOTE').'" class="'.PhocacartRenderIcon::getClassAdmin('barcode').' ph-icon-warning"></span></a>';


if ($this->t['plugin-pdf'] == 1 && $this->t['component-pdf']) {
	
	$formatPDF = '&format=pdf';
	$view .= '<br />';
	$view .= '<a href="'.$linkOrderView.$formatPDF.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_ORDER').'" class="'.PhocacartRenderIcon::getClassAdmin('search').' ph-icon-success"></span><br /><span class="ph-icon-success-txt">'.JText::_('COM_PHOCACART_PDF').'</span></a>';
	$view .= ' <a href="'.$linkInvoiceView.$formatPDF.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_INVOICE').'" class="'.PhocacartRenderIcon::getClassAdmin('list-alt').' ph-icon-danger"></span><br /><span class="ph-icon-danger-txt">'.JText::_('COM_PHOCACART_PDF').'</span></a>';
	$view .= ' <a href="'.$linkDelNoteView.$formatPDF.'" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_DELIVERY_NOTE').'" class="'.PhocacartRenderIcon::getClassAdmin('barcode').' ph-icon-warning"></span><br /><span class="ph-icon-warning-txt">'.JText::_('COM_PHOCACART_PDF').'</span></a>';
	
}
echo $r->td($view, "small");


$price->setCurrency($item->currency_id, $item->id);

$amount = (isset($item->total_amount_currency) && $item->total_amount_currency > 0) ? $price->getPriceFormat($item->total_amount_currency, 0, 1) : $price->getPriceFormat($item->total_amount);
echo $r->td($amount, "small ph-right ph-p-r-med ph-no-wrap");

echo $r->td(JHtml::date($item->date, JText::_('DATE_FORMAT_LC5')), "small");
echo $r->td(JHtml::date($item->modified, JText::_('DATE_FORMAT_LC5')), "small");

echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td($item->id, "small");

echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 12);
echo $r->endTable();

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>