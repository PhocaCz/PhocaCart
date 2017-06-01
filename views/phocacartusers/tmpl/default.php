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

JHtml::_('behavior.modal', 'a.modal_view_cart');

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
							
$userNameSelected = isset($this->items[0]->user_name_selected) ? $this->items[0]->user_name_selected : '';
echo $r->inputFilterUser($this->t['l'].'_FILTER_USER_LABEL', $this->t['l'].'_FILTER_USER_DESC',
							$this->escape($this->state->get('filter.user')), $userNameSelected);
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR', array(0 => 'field-user-input'));
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
echo $r->endFilterBar();

echo $r->endFilterBar();

$idMd = 'phViewCartModal';
$textButton = 'COM_PHOCACART_VIEW_CART';
$w = 500;
$h = 400;
$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMd, $textButton, $w, $h, true);		

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-name">'.JHTML::_('grid.sort',  	$this->t['l'].'_NAME', 'u.name', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.JText::_($this->t['l'].'_STATUS').'</th>'."\n";
echo '<th class="ph-group">'.JText::_($this->t['l'].'_GROUPS').'</th>'."\n";
echo '<th class="ph-name">'.JHTML::_('grid.sort',  $this->t['l'].'_FIRST_NAME_LABEL', 'a.name_first', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-name">'.JHTML::_('grid.sort',  $this->t['l'].'_LAST_NAME_LABEL', 'a.name_last', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-address">'.JHTML::_('grid.sort',  $this->t['l'].'_ADDRESS_1_LABEL', 'a.address_1', $listDirn, $listOrder ).'</th>'."\n";
//echo '<th class="ph-published">'.JHTML::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-id">'.JHTML::_('grid.sort',  		$this->t['l'].'_ID', 'u.id', $listDirn, $listOrder ).'</th>'."\n";

				
				
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
$linkEdit 		= JRoute::_( $urlEdit. $item->user_id );
$linkCart 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartcart&tmpl=component&id='.(int)$item->user_id  );
$linkCartHandler= 'rel="{handler: \'iframe\', size: {x: 580, y: 460}}"';



$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="0" >'. "\n";

echo $r->tdOrder($canChange, $saveOrder, $orderkey);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
//if (($canCreate || $canEdit) && (int)$item->id > 0) {
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->user_name).'</a>';
} else {
	$checkO .= $this->escape($item->user_name);
}

if (isset($item->user_username)) {
	$checkO .= ' <small>('.$item->user_username.')</small>';
}
echo $r->td($checkO, "small");
 
// Status
// NOT ACTIVE
if ((int)$item->id < 1 && (int)$item->cartuserid < 1) {
	echo $r->td( '<span class="label label-important label-danger">'.JText::_('COM_PHOCACART_NOT_ACTIVE').'</span>', "small");
}

// ORDER MADE
else if ( (int)$item->orderuserid > 0 ) {
	$o = '<span class="label label-success">'.JText::_('COM_PHOCACART_ACTIVE_ORDER').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
} 

// ADDED BILLING AND SHIPPING ADDRESS
else if ((int)$item->id > 0 && ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {
	$o = '<span class="label label-warning label-info">'.JText::_('COM_PHOCACART_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
}


// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0 || ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {

	$o = '<span class="label label-warning label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
}

// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0) {

	$o = '<span class="label label-warning label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
}

// OTHER
else {
	echo $r->td('-', "small"); 
}


// GROUP
if (isset($item->groups) && $item->groups != '') {
	$groupsA = explode(',', $item->groups);
	asort($groupsA);
	$groupsI = '';
	foreach($groupsA as $k => $v) {
		$groupsI .= ' '.JText::_($v);
	}
	echo $r->td($groupsI, "small");
} else {
	echo $r->td('', "small");
}


echo $r->td($item->name_last, "small");
echo $r->td($item->name_first, "small");
echo $r->td($item->address_1, "small");
echo $r->td($item->user_id, "small");
	
echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 9);
echo $r->endTable();

echo '<div class="ph-notes-box"><h3>'.JText::_('COM_PHOCACART_NOTES').'</h3>';
echo '<div><span class="label label-important label-danger">'.JText::_('COM_PHOCACART_NOT_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_NOT_ACTIVE').'</div>';
echo '<div><span class="label label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_PARTIALLY_ACTIVE').'</div>';
echo '<div><span class="label label-info">'.JText::_('COM_PHOCACART_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_ACTIVE').'</div>';
echo '<div><span class="label label-success">'.JText::_('COM_PHOCACART_ACTIVE_ORDER').'</span> ... '.JText::_('COM_PHOCACART_NOTE_ACTIVE_ORDER').'</div>';
echo '</div>';


echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>