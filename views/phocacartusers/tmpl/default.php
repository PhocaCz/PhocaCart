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

JHtml::_('behavior.modal', 'a.modal_view_cart');

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
echo $r->startFilter($this->t['l'].'_FILTER');
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
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
echo $r->endFilterBar();		

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-name">'.JHTML::_('grid.sort',  	$this->t['l'].'_NAME', 'u.name', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.JText::_($this->t['l'].'_STATUS').'</th>'."\n";
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
echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";


echo $r->tdOrder($canChange, $saveOrder, $orderkey);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small hidden-phone");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if (($canCreate || $canEdit) && (int)$item->id > 0) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->user_name).'</a>';
} else {
	$checkO .= $this->escape($item->user_name);
}

if (isset($item->user_username)) {
	$checkO .= ' <small>('.$item->user_username.')</small>';
}
echo $r->td($checkO, "small hidden-phone");
 
// Status
// NOT ACTIVE
if ((int)$item->id < 1 && (int)$item->cartuserid < 1) {
	echo $r->td( '<span class="label label-important label-danger">'.JText::_('COM_PHOCACART_NOT_ACTIVE').'</span>', "small hidden-phone");
}

// ORDER MADE
else if ( (int)$item->orderuserid > 0 ) {
	$o = '<span class="label label-success">'.JText::_('COM_PHOCACART_ACTIVE_ORDER').'</span>';
	if ((int)$item->cartuserid > 0) {
		$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
	}
	echo $r->td(  $o, "small hidden-phone");
} 

// ADDED BILLING AND SHIPPING ADDRESS
else if ((int)$item->id > 0 && ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {
	$o = '<span class="label label-warning label-info">'.JText::_('COM_PHOCACART_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
	}
	echo $r->td(  $o, "small hidden-phone");
}


// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0 || ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {

	$o = '<span class="label label-warning label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
	}
	echo $r->td(  $o, "small hidden-phone");
}

// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0) {

	$o = '<span class="label label-warning label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.JText::_('COM_PHOCACART_VIEW_CART').'</small></a>';
	}
	echo $r->td(  $o, "small hidden-phone");
}

// OTHER
else {
	echo $r->td('-', "small hidden-phone"); 
}
echo $r->td($item->name_last, "small hidden-phone");
echo $r->td($item->name_first, "small hidden-phone");
echo $r->td($item->address_1, "small hidden-phone");
echo $r->td($item->user_id, "small hidden-phone");
	
echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 8);
echo $r->endTable();

echo '<div class="ph-notes-box"><h3>'.JText::_('COM_PHOCACART_NOTES').'</h3>';
echo '<div><span class="label label-important label-danger">'.JText::_('COM_PHOCACART_NOT_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_NOT_ACTIVE').'</div>';
echo '<div><span class="label label-warning">'.JText::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_PARTIALLY_ACTIVE').'</div>';
echo '<div><span class="label label-info">'.JText::_('COM_PHOCACART_ACTIVE').'</span> ... '.JText::_('COM_PHOCACART_NOTE_ACTIVE').'</div>';
echo '<div><span class="label label-success">'.JText::_('COM_PHOCACART_ACTIVE_ORDER').'</span> ... '.JText::_('COM_PHOCACART_NOTE_ACTIVE_ORDER').'</div>';
echo '</div>';


echo $r->formInputs($listOrder, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>