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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$r 			= $this->r;
$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
//echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocaDownloadCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
//echo $r->endFilter();

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
$valueFrom 	= $this->escape($this->state->get('filter.date_from', PhocacartDate::getCurrentDate(30)));

echo '<div class="ph-inline-param">'. Text::_('COM_PHOCACART_DATE_FROM') . ': ';
echo  HTMLHelper::_('calendar', $valueFrom, $name, $id, $format, $attributes).'</div>';

//DATE TO
$name		= "filter_date_to";
$id			= 'filter_date_to';
$valueTo 	= $this->escape($this->state->get('filter.date_to', PhocacartDate::getCurrentDate()));


echo '<div class="ph-inline-param">'. Text::_('COM_PHOCACART_DATE_TO') . ': ';
echo  HTMLHelper::_('calendar', $valueTo, $name, $id, $format, $attributes).'</div>';

echo '<div class="ph-inline-param">';
//echo '<input type="hidden" name="filter_date_from" value="'.$this->escape($this->state->get('filter.date_from')).'" />'. "\n";
//echo '<input type="hidden" name="filter_date_to" value="'.$this->escape($this->state->get('filter.date_to')).'" />'. "\n";
echo '<input type="hidden" name="limitstart" value="0" />'. "\n";
echo '<input type="hidden" name="limit" value="" />'. "\n";
echo HTMLHelper::_('form.token');
echo '<input class="btn btn-success" type="submit" name="submit" value="'.Text::_('COM_PHOCACART_SELECT').'" /></div>';


echo '<div style="clear:both"></div>';
//echo $r->endFilterBar();

// Chart
$s = new PhocacartStatistics();

$s->renderChartJsLine('phChartAreaLine', $this->d['amount'], Text::_('COM_PHOCACART_TOTAL_AMOUNT'), $this->d['orders'], Text::_('COM_PHOCACART_TOTAL_ORDERS'), $this->d['ticks']);
$s->setFunction('phChartAreaLine', 'Line');

if ($this->t['data_error'] == 1) {
	echo '<div class="alert alert-error">'.Text::_('COM_PHOCACART_MAXIMUM_NUMBER_OF_DAYS_SELECTED_EXCEEDED').' ('. Text::_('COM_PHOCACART_MAXIMUM_NUMBER_OF_DAYS_SELECTED') . ': '.$this->t['data_possible_days'].')</div>';
} else {
	/*
	<div class="ph-chart-legend"><span class="ph-orders">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?> &nbsp; <span class="ph-amount">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></div> */ ?>
	<div class="ph-cpanel-chart-box">
	<div id="phChartAreaLineHolder" class="ph-chart-canvas-holder" style="width:95%" >
        <canvas id="phChartAreaLine" class="ph-chart-area-line"></canvas>
    </div>
	</div>
	<?php
}

$originalOrders = array();

echo '<div class="ph-cb"></div>';

echo '<div class="row ph-admin-stat-row">';

// Best selling - period
echo '<div class="col-xs-12 col-sm-4 col-md-4 ph-admin-stat-box">';
echo '<h2>'. Text::_('COM_PHOCACART_TOP_5') . ' - '. Text::_('COM_PHOCACART_BEST_SELLING_PRODUCTS').'<br />';
echo '('. $this->t['date_from'] .' - '. $this->t['date_to'] .')</h2>';


$dataBs2 = array();
if (!empty($this->t['best_selling2'])) {
	echo '<table>';
	foreach ($this->t['best_selling2'] as $k => $v) {
	    if ($k >= 5) {break;}
		$dataBs2[$k]['title'] = $v->title;
		$dataBs2[$k]['items'] = $v->count_products;
		echo '<tr><td>'. $v->title. '</td><td class="ph-table-td-left">'.$v->count_products.'x</td></tr>';
	}
	echo '</table>';

	$s->renderChartJsPie('phChartAreaPieBs2', $dataBs2);
	$s->setFunction('phChartAreaPieBs2', 'Pie');

	echo '<div id="phChartAreaPieBs2Holder" style="width: 300px;margin-top:10px;" >';
    echo '<canvas class="ph-stats-canvas" id="phChartAreaPieBs2" width="300" height="300" />';
    echo '</div>';

	if ($this->t['best_selling2_count'] != '') {
		echo '<div class="ph-stat-total">';
		echo '<h3>'.Text::_('COM_PHOCACART_ALL_PRODUCTS'). '</h3>';
		echo '<div>'.Text::_('COM_PHOCACART_TOTAL'). ': '.$this->t['best_selling2_count'].'</div>';
		echo '</div>';
	}

} else {
	echo Text::_('COM_PHOCACART_NO_PRODUCTS_SOLD_IN_THIS_PERIOD');
}

echo '</div>';

// Best selling
$dataBs1 = array();
echo '<div class="col-xs-12 col-sm-4 col-md-4 ph-admin-stat-box">';
echo '<h2>'. Text::_('COM_PHOCACART_TOP_5') . ' - '. Text::_('COM_PHOCACART_BEST_SELLING_PRODUCTS').'<br />';
echo Text::_('COM_PHOCACART_FOR_THE_WHOLE_PERIOD').'</h2>';

if (!empty($this->t['best_selling'])) {

	$dataBs = array();
	echo '<table>';
	foreach ($this->t['best_selling'] as $k => $v) {
	    if ($k >= 5) {break;}
		$dataBs1[$k]['title'] = $v->title;
		$dataBs1[$k]['items'] = $v->count_products;
		echo '<tr><td>'. $v->title. '</td><td class="ph-table-td-left">'.$v->count_products.'x</td></tr>';
	}
	echo '</table>';

	$s->renderChartJsPie('phChartAreaPieBs1', $dataBs1);
	$s->setFunction('phChartAreaPieBs1', 'Pie');

	echo '<div id="phChartAreaPieBs1Holder" style="width: 300px;margin-top:10px;" >';
    echo '<canvas class="ph-stats-canvas" id="phChartAreaPieBs1" width="300" height="300" />';
    echo '</div>';

	if ($this->t['best_selling_count'] != '') {
		echo '<div class="ph-stat-total">';
		echo '<h3>'.Text::_('COM_PHOCACART_ALL_PRODUCTS'). '</h3>';
		echo '<div>'.Text::_('COM_PHOCACART_TOTAL'). ': '.$this->t['best_selling_count'].'</div>';
		echo '</div>';
	}

} else {
	echo Text::_('COM_PHOCACART_NO_PRODUCTS_SOLD_FOR_THE_WHOLE_PERIOD');
}
echo '</div>';



// Most viewed
$dataMv = array();
echo '<div class="col-xs-12 col-sm-4 col-md-4 ph-admin-stat-box">';
echo '<h2>'. Text::_('COM_PHOCACART_TOP_5') . ' - '. Text::_('COM_PHOCACART_MOST_VIEWED_PRODUCTS').'<br />';
	echo Text::_('COM_PHOCACART_FOR_THE_WHOLE_PERIOD').'</h2>';

if (!empty($this->t['most_viewed'])) {
	echo '<table>';
	foreach ($this->t['most_viewed'] as $k => $v) {
	    if ($k >= 5) {break;}
		$dataMv[$k]['title'] = $v->title;
		$dataMv[$k]['items'] = $v->hits;
		echo '<tr><td>'. $v->title. '</td><td class="ph-table-td-left">'.$v->hits.'x</td></tr>';
	}
	echo '</table>';

	$s->renderChartJsPie('phChartAreaPieMv', $dataMv);
	$s->setFunction('phChartAreaPieMv', 'Pie');

	echo '<div id="phChartAreaPieMvHolder" style="width: 300px;margin-top:10px;" >';
    echo '<canvas class="ph-stats-canvas" id="phChartAreaPieMv" width="300" height="300" />';
    echo '</div>';

	if ($this->t['most_viewed_count'] != '') {
		echo '<div class="ph-stat-total">';
		echo '<h3>'.Text::_('COM_PHOCACART_ALL_PRODUCTS'). '</h3>';
		echo '<div>'.Text::_('COM_PHOCACART_TOTAL'). ': '.$this->t['most_viewed_count'].'</div>';
		echo '</div>';
	}

} else {
	echo Text::_('COM_PHOCACART_NO_PRODUCTS_DISPLAYED_FOR_THE_WHOLE_PERIOD');
}
echo '</div>';

$s->renderFunctions();


////echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';
echo '</div>';// end row

//echo $r->formInputs();
echo $r->endMainContainer();
echo $r->endForm();
?>
