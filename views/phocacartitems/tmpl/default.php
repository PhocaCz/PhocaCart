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
$class		= $this->t['n'] . 'RenderAdminViews';
$r 			=  new $class();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);

$saveOrder 	= false;
if ($this->t['ordering'] && !empty($this->ordering)) {
	$saveOrder	= $listOrder == 'pc.ordering';
	if ($saveOrder) {
		$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
	}
}
$sortFields = $this->getSortFields();
echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocaCartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
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
echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterCategory(PhocaCartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();




	

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder, 'pc');
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-image">'.JText::_($this->t['l'].'_IMAGE').'</th>'."\n";
echo '<th class="ph-title">'.JHTML::_('grid.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHTML::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";	
//echo '<th class="ph-parentcattitle">'.JHTML::_('grid.sort', $this->t['l'].'_CATEGORY', 'category_id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">'.JTEXT::_($this->t['l'].'_CATEGORY').'</th>'."\n";
echo '<th class="ph-price">'.JHTML::_('grid.sort', $this->t['l'].'_PRICE', 'a.price', $listDirn, $listOrder ).'</th>'."\n";
//echo '<th class="ph-hits">'.JHTML::_('grid.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">'.JTEXT::_($this->t['l'].'_ACCESS').'</th>'."\n";
echo '<th class="ph-language">'.JHTML::_('grid.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-hits">'.JHTML::_('grid.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.JHTML::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();
		
echo '<tbody>'. "\n";

$originalOrders = array();	
$parentsStr 	= "";		
$j 				= 0;

$price			= new PhocaCartPrice();

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		//if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$urlTask		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'];
//$orderkey   	= array_search($item->id, $this->ordering[$item->catid]);
$orderkey		= 0;
if ($this->t['ordering'] && !empty($this->ordering)) {
	$orderkey   	= array_search($item->id, $this->ordering[$this->t['catid']]);
}	
$ordering		= ($listOrder == 'pc.ordering');			
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit. $item->id );

//$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
$canEditCat	= $user->authorise('core.edit', $this->t['o']);


$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->category_id.'" item-id="'.$item->id.'" parents="'.$item->category_id.'" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->category_id.'" >'. "\n";

//echo '<td>'.$item->category_id. '/'.$orderkey.'</td>';
echo $r->tdOrder($canChange, $saveOrder, $orderkey, $this->t['ordering']);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
echo $r->tdImageCart($this->escape($item->image), 'small', 'productimage', 'small ph-items-image-box');				
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
$checkO .= '<br /><span class="smallsub">(<span>'.JText::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
echo $r->td($checkO, "small");

echo $r->td(
	'<div class="btn-group">'.JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange)
	. PhocaCartFeatured::featured($item->featured, $i, $canChange). '</div>',
"small");
/*
if ($canEditCat) {
	$catO = '<a href="'. JRoute::_($linkCat).'">'. $this->escape($item->category_title).'</a>';
} else {
	$catO = $this->escape($item->category_title);
}*/
$catO = array();
if (isset($this->t['categories'][$item->id])) {
	foreach($this->t['categories'][$item->id] as $k => $v) {
		if ($canEditCat) {
			$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $v['id'] );
			$catO[] = '<a href="'. JRoute::_($linkCat).'">'. $this->escape($v['title']).'</a>';
		} else {
			$catO[] = $this->escape($v['title']);
		}
	}
}

echo $r->td(implode(' ', $catO), "small");	
//echo $r->td($this->escape($item->access_level), "small");	
						

echo $r->td(PhocaCartPrice::cleanPrice($item->price), "small");
//echo $r->td($item->hits, "small");



echo $r->td($this->escape($item->access_level));

echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
echo $r->td($item->hits, "small");
echo $r->td($item->id, "small");

echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 16);
echo $r->endTable();

echo $this->loadTemplate('batch');

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();


?>
