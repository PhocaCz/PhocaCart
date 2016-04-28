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
	<div class="ph-chart-legend"><span class="ph-orders">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?> &nbsp; <span class="ph-amount">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></div>
	<div id="ph-canvas-holder" class="ph-chart-canvas-holder" style="width: 70%;" >
        <canvas id="ph-chart-area" class="ph-chart-area"s />
    </div>

	<?php
}

$originalOrders = array();

echo '<hr />';

echo '<div class="row ph-admin-stat-row">';

// Best selling - period
echo '<div class="span4 ph-admin-stat-box">';
echo '<h2>'.JText::_('COM_PHOCACART_BEST_SELLING_PRODUCTS').'<br />';
echo '('. $this->t['date_from'] .' - '. $this->t['date_to'] .')</h2>';

if (!empty($this->t['best_selling2'])) {
	echo '<table>';
	foreach ($this->t['best_selling'] as $k => $v) {
		echo '<tr><td><a href="index.php?option=com_phocacart&task=phocacartitem.edit&id='.(int)$v->id.'" target="_blank">'. $v->title. '</td><td class="ph-table-td-left">'.$v->count_products.'x</td></tr>';
	}
	echo '</table>';
} else {
	echo JText::_('COM_PHOCACART_NO_PRODUCTS_SOLD_IN_THIS_PERIOD');
}

echo '</div>';

// Best selling
echo '<div class="span4 ph-admin-stat-box">';
echo '<h2>'.JText::_('COM_PHOCACART_BEST_SELLING_PRODUCTS').'<br />';
echo JText::_('COM_PHOCACART_FOR_THE_WHOLE_PERIOD').'</h2>';

if (!empty($this->t['best_selling'])) {
	echo '<table>';
	foreach ($this->t['best_selling'] as $k => $v) {
		echo '<tr><td><a href="index.php?option=com_phocacart&task=phocacartitem.edit&id='.(int)$v->id.'" target="_blank">'. $v->title. '</td><td class="ph-table-td-left">'.$v->count_products.'x</td></tr>';
	}
	echo '</table>';
} else {
	echo JText::_('COM_PHOCACART_NO_PRODUCTS_SOLD_FOR_THE_WHOLE_PERIOD');
}
echo '</div>';


// Most viewed
echo '<div class="span4 ph-admin-stat-box">';
echo '<h2>'.JText::_('COM_PHOCACART_MOST_VIEWED_PRODUCTS').'<br />';
	echo JText::_('COM_PHOCACART_FOR_THE_WHOLE_PERIOD').'</h2>';
	
if (!empty($this->t['most_viewed'])) {
	echo '<table>';
	foreach ($this->t['most_viewed'] as $k => $v) {
		echo '<tr><td><a href="index.php?option=com_phocacart&task=phocacartitem.edit&id='.(int)$v->id.'" target="_blank">'. $v->title. '</td><td class="ph-table-td-left">'.$v->hits.'x</td></tr>';
	}
	echo '</table>';

} else {
	echo JText::_('COM_PHOCACART_NO_PRODUCTS_DISPLAYED_OF_ALL_TIME');
}
echo '</div>';

//echo '<div class="span2"></div>';
echo '</div>';// end row

//echo $r->formInputs();
echo $r->endMainContainer();
echo $r->endForm();
?>