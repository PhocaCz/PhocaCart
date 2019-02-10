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
echo '<th class="ph-title-small">'.JHtml::_('grid.sort',  	$this->t['l'].'_NAME', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-required">'.JHtml::_('grid.sort',  $this->t['l'].'_REQUIRED', 'a.required', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-label">'.JHtml::_('grid.sort',  	$this->t['l'].'_LABEL', 'a.label', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-type">'.JHtml::_('grid.sort',  	$this->t['l'].'_TYPE', 'a.type', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-type ph-center">'.JHtml::_('grid.sort',  $this->t['l'].'_DEFAULT', 'a.type_default', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  	$this->t['l'].'_FORM_DISPLAY_BILLING', 'a.display_billing', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  	$this->t['l'].'_FORM_DISPLAY_SHIPPING', 'a.display_shipping', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  	$this->t['l'].'_FORM_DISPLAY_ACCOUNT', 'a.display_account', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-access">'.JTEXT::_($this->t['l'].'_ACCESS').'</th>'."\n";
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
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small");


echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td(PhocacartHtmlJgrid::displayRequired( $item->required, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td($item->label . '<br /><small>('.JText::_($this->escape($item->label)).')</small>', "small");
echo $r->td($item->type, "small");

if ($item->type_default == 1) {
	$default = '<a data-original-title="'.JText::_('COM_PHOCACART_DEFAULT').'" class="btn btn-micro disabled jgrid hasTooltip" title="'.JText::_('COM_PHOCACART_DEFAULT').'"><i class="icon-featured"></i></a>';
	echo $r->td($default, "small ph-center");
} else {
	echo $r->td('', "small");
}


echo $r->td(PhocacartHtmlJgrid::displayBilling( $item->display_billing, $i, $this->t['tasks'].'.', $canChange), "small");
echo $r->td(PhocacartHtmlJgrid::displayShipping( $item->display_shipping, $i, $this->t['tasks'].'.', $canChange), "small");
echo $r->td(PhocacartHtmlJgrid::displayAccount( $item->display_account, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td($this->escape($item->access_level));

echo $r->td($item->id, "small");

echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 13);
echo $r->endTable();

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>