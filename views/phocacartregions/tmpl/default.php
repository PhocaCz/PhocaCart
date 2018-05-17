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
//echo $r->selectFilterCountry(PhocacartCountry::options($this->t['o']), 'COM_PHOCACART_SELECT_COUNTRY', $this->state->get('filter.country_id'));
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
echo $r->selectFilterCountry(PhocacartCountry::options($this->t['o']), 'COM_PHOCACART_SELECT_COUNTRY', $this->state->get('filter.country_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();	

$idMd = 'phEditStatusModal';
$textButton = 'COM_PHOCACART_EDIT_TAX';
$w = 500;
$h = 400;
$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMd, $textButton, $w, $h, true);	

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-title">'.JHtml::_('grid.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-taxrate">'.JText::_($this->t['l'].'_TAX_RATE').'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-country">'.JHtml::_('grid.sort',  	$this->t['l'].'_COUNTRY', 'country_title', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-code">'.JHtml::_('grid.sort',  	$this->t['l'].'_CODE2', 'a.code2', $listDirn, $listOrder ).'</th>'."\n";	
echo '<th class="ph-code">'.JHtml::_('grid.sort',  	$this->t['l'].'_CODE3', 'a.code3', $listDirn, $listOrder ).'</th>'."\n";	
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
$orderkey   	= array_search($item->id, $this->ordering[$item->country_id]);		
$ordering		= ($listOrder == 'a.ordering');			
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit. $item->id );
$linkTax 		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacartedittax&type=2&tmpl=component&id='.(int)$item->id  );

$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="0" item-id="'.$item->id.'" parents="0" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->country_id.'" >'. "\n";
echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
					
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}

if (isset($item->image) && $item->image != '') {
	$checkO .= ' <span>'.PhocacartImage::getImage($item->image, '', '20px') . '<span> '; 
}

if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $item->title.'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
echo $r->td($checkO, "small");

$tax = '';

$tax .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-toggle="modal" title="' . JText::_($textButton) . '" data-src="'.$linkTax.'" data-height="'.$h.'" data-width="'.$w.'">'. JText::_($textButton) . '</a></span>';

if (isset($item->tr_tax_rate) && $item->tr_tax_rate != '') {
	$taxRateA = explode(',', $item->tr_tax_rate);
	
	if (!empty($taxRateA)) {
		
		foreach($taxRateA as $k => $v) {
			$taxRateA[$k] = PhocacartPrice::cleanPrice($v);
		}
		$taxRateS = implode(', ', $taxRateA);
		$tax .= ' <small>('.$taxRateS.')</small>';
	}
}

echo $r->td($tax, "small");


echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

echo $r->td($item->country_title, "small");
echo $r->td($item->code2, "small");
echo $r->td($item->code3, "small");

echo $r->td($item->id, "small");

echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(),9);
echo $r->endTable();

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>