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
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
echo $r->startFilter($this->t['l'].'_FILTER');
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocaDownloadCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilter();

echo $r->startMainContainer();
//echo $r->startFilterBar();
//echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							//$this->escape($this->state->get('filter.search')));
//echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
//echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
//echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
//echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

// DATE FROM - DATE TO
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

// DATE FROM
$name		= "filter_date_from";
$id			= 'filter_date_from';
$format 	= '%Y-%m-%d';
$attributes = '';
$valueFrom 	= $this->escape($this->state->get('filter.date_from', PhocaCartDate::getCurrentDate(30)));

echo '<div class="ph-inline-param">'. JText::_('COM_PHOCACART_DATE_FROM') . ': ';
echo  JHtml::_('calendar', $valueFrom, $name, $id, $format, $attributes).'</div>';

//DATE TO
$name		= "filter_date_to";
$id			= 'filter_date_to';
$valueTo 	= $this->escape($this->state->get('filter.date_to', PhocaCartDate::getCurrentDate()));


echo '<div class="ph-inline-param">'. JText::_('COM_PHOCACART_DATE_TO') . ': ';
echo  JHtml::_('calendar', $valueTo, $name, $id, $format, $attributes).'</div>';

echo '<div class="ph-inline-param">';
//echo '<input type="hidden" name="filter_date_from" value="'.$this->escape($this->state->get('filter.date_from')).'" />'. "\n";
//echo '<input type="hidden" name="filter_date_to" value="'.$this->escape($this->state->get('filter.date_to')).'" />'. "\n";
echo '<input type="hidden" name="limitstart" value="0" />'. "\n";
echo '<input type="hidden" name="limit" value="" />'. "\n";
echo JHtml::_('form.token');
echo '<input class="btn btn-success" type="submit" name="submit" value="'.JText::_('COM_PHOCACART_SELECT').'" /></div>';


echo '<div style="clear:both"></div>';
//echo $r->endFilterBar();
	

if ($this->t['data_error'] == 1) {

	echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MAXIMUM_NUMBER_OF_DAYS_SELECTED_EXCEEDED').' ('. JText::_('COM_PHOCACART_MAXIMUM_NUMBER_OF_DAYS_SELECTED') . ': '.$this->t['data_possible_days'].'</div>';
} else {
	?>
	<div id="graph-wrapper">
		<div class="graph-info">
			<a href="javascript:void(0)" class="orders"><?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?></a>
			<a href="javascript:void(0)" class="amount"><?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></a>
	 
			<a href="#" id="bars"><span class="icon-bars"></span></a>
			<a href="#" id="lines" class="active"><span class="icon-chart"></span></a>
		</div>
	 
		<div class="graph-container">
			<div id="graph-lines"></div>
			<div id="graph-bars"></div>
		</div>
	</div>

	<?php
}

$originalOrders = array();
//echo $r->formInputs();
echo $r->endMainContainer();
echo $r->endForm();
?>