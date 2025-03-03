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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
//HTMLHelper::_('formbehavior.chosen', 'select');

$r 			= $this->r;
$user		= Factory::getUser();
//$userId		= $user->get('id');
$listOrder			= $this->escape($this->state->get('list.ordering'));
$listDirn			= $this->escape($this->state->get('list.direction'));
$listFullOrdering			= $this->escape($this->state->get('list.fullordering'));
//$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
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
//echo $r->endFilterBar();

echo '<div class="js-stools">';

$ascDir = $descDir = '';
if ($listDirn == 'asc') {$ascDir = 'selected="selected"';}
if ($listDirn == 'desc') {$descDir = 'selected="selected"';}
/*
echo '<div class="ph-inline-param">'. "\n"
.'<label for="directionTable" class="element-invisible">' .Text::_('JFIELD_ORDERING_DESC').'</label>'. "\n"
.'<select name="filter_order_Dir" id="directionTable" class="input-medium">'. "\n"
.'<option value="">' .Text::_('JFIELD_ORDERING_DESC').'</option>'. "\n"
.'<option value="asc" '.$ascDir.'>' . Text::_('JGLOBAL_ORDER_ASCENDING').'</option>'. "\n"
.'<option value="desc" '.$descDir.'>' . Text::_('JGLOBAL_ORDER_DESCENDING').'</option>'. "\n"
.'</select>'. "\n"
.'</div>'. "\n";

echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('JGLOBAL_SORT_BY').'</label>'. "\n"
.'<select name="filter_order" id="sortTable" class="input-medium">'. "\n"
.'<option value="">'.Text::_('JGLOBAL_SORT_BY').'</option>'. "\n"
. HTMLHelper::_('select.options', $sortFields, 'value', 'text', $listOrder). "\n"
.'</select>'. "\n"
.'</div>'. "\n";*/


$sF = array();
if (!empty($sortFields)) {
    foreach($sortFields as $k => $v) {
        $newK = $k . ' ASC';
        $newV = $v . ' '. Text::_('COM_PHOCACART_ASCENDING');

        $sF[$newK] = $newV;

        $newK = $k . ' DESC';
        $newV = $v . ' '. Text::_('COM_PHOCACART_DESCENDING');

        $sF[$newK] = $newV;
    }
}
$lO = $listOrder . ' '. strtoupper($listDirn);


echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('JGLOBAL_SORT_BY').'</label>'. "\n"
.'<select class="form-select" id="list_fullordering" name="list[fullordering]">'. "\n"
.'<option value="">'.Text::_('JGLOBAL_SORT_BY').'</option>'. "\n"
. HTMLHelper::_('select.options', $sF, 'value', 'text', $lO). "\n"
.'</select>'. "\n"
.'</div>'. "\n";




$listCurrency	= $this->escape($this->state->get('filter.currency'));
$currencies 	= PhocacartCurrency::getAllCurrencies();

echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_CURRENCY').'</label>'. "\n"
.'<select class="form-select" name="filter_currency" id="currencyTable" class="input-medium">'. "\n"
.'<option value="">'.Text::_('COM_PHOCACART_SELECT_CURRENCY').'</option>'. "\n";
if (!empty($currencies)) {
    echo HTMLHelper::_('select.options', $currencies, 'value', 'text', $listCurrency). "\n";
}
echo '</select>'. "\n"
.'</div>'. "\n";

$listShopType	= $this->escape($this->state->get('filter.shop_type'));
if ($listShopType == '') {
    // '' is not the default value, it should be disabled
    $listShopType = 0;
}
$shopTypes 		= PhocacartUtilsSettings::getShopTypesForm();
array_unshift($shopTypes, HTMLHelper::_('select.option', '', '' . Text::_('COM_PHOCACART_SELECT_SHOP_TYPE') . '', 'value', 'text', true));
echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_SHOP_TYPE').'</label>'. "\n"
.'<select class="form-select" name="filter_shop_type" id="shopTypeTable" class="input-medium">'. "\n"
. HTMLHelper::_('select.options', $shopTypes, 'value', 'text', $listShopType). "\n"
.'</select>'. "\n"
.'</div>'. "\n";

$listOrderStatus	= $this->escape($this->state->get('filter.order_status'));
$orderStatuses 		= PhocacartOrderStatus::getOptions();
array_unshift($orderStatuses, HTMLHelper::_('select.option', '', '' . Text::_('COM_PHOCACART_OPTION_SELECT_ORDER_STATUS') . '', 'value', 'text'));
echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_ORDER_STATUS').'</label>'. "\n"
.'<select class="form-select" name="filter_order_status" id="orderStatusTable" class="input-medium">'. "\n"
. HTMLHelper::_('select.options', $orderStatuses, 'value', 'text', $listOrderStatus). "\n"
.'</select>'. "\n"
.'</div>'. "\n";


$listPaymentType	= $this->escape($this->state->get('filter.payment_type'));
$paymentTypes 		= PhocacartUtilsSettings::getPaymentTypesForm();
array_unshift($paymentTypes, HTMLHelper::_('select.option', '', '' . Text::_('COM_PHOCACART_SELECT_PAYMENT_STATUS') . '', 'value', 'text', true));
echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_PAYMENT_STATUS').'</label>'. "\n"
.'<select class="form-select" name="filter_payment_type" id="paymentTypeTable" class="input-medium">'. "\n"
. HTMLHelper::_('select.options', $paymentTypes, 'value', 'text', $listPaymentType). "\n"
.'</select>'. "\n"
.'</div>'. "\n";

$listFlowType	= $this->escape($this->state->get('filter.flow_type'));
$flowTypes 		= PhocacartUtilsSettings::getFlowTypesForm();
if ($listFlowType == '') {
    // '' is not the default value, it should be disabled
    $listFlowType = 1;
}
array_unshift($flowTypes, HTMLHelper::_('select.option', '', '' . Text::_('COM_PHOCACART_SELECT_FLOW_TYPE') . '', 'value', 'text', true));
echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_FLOW_TYPE').'</label>'. "\n"
.'<select class="form-select" name="filter_flow_type" id="flowTypeTable" class="input-medium">'. "\n"
. HTMLHelper::_('select.options', $flowTypes, 'value', 'text', $listFlowType). "\n"
.'</select>'. "\n"
.'</div>'. "\n";


$listReportType	= $this->escape($this->state->get('filter.report_type'));
$reportTypes 		= PhocacartUtilsSettings::getReportTypesForm();
if ($listReportType == '') {
    // '' is not the default value, it should be disabled
    $listReportType = 0;
}

array_unshift($reportTypes, HTMLHelper::_('select.option', '', '' . Text::_('COM_PHOCACART_SELECT_REPORT_TYPE') . '', 'value', 'text', true));

echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.Text::_('COM_PHOCACART_SELECT_REPORT_TYPE').'</label>'. "\n"
.'<select class="form-select" name="filter_report_type" id="reportTypeTable" class="input-medium">'. "\n"
. HTMLHelper::_('select.options', $reportTypes, 'value', 'text', $listReportType). "\n"
.'</select>'. "\n"
.'</div>'. "\n";


echo '</div>';

echo '<div style="clear:both">&nbsp;</div>';

// DATE FROM - DATE TO
HTMLHelper::_('jquery.framework');
//HTMLHelper::_('script', 'system/html5fallback.js', false, true);

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


//echo $r->endFilterBar();




echo '<div class="ph-cb"></div>';


if ($this->t['data_error'] > 0 && $this->t['data_error_message'] != '') {
    echo '<div class="alert alert-warning">'.$this->t['data_error_message'].'</div>';
}

echo '<div class="ph-admin-stat-row">';

echo $this->loadTemplate('report');

echo '</div>';// end row

echo '<div class="alert alert-warning">'.Text::_('COM_PHOCACART_NOTE_FILTER_VALUES_VALID_FOR_SELECTED_STATISTICS').'</div>';

echo '<input type="hidden" name="task" value="" />'. "\n"
//.'<input type="hidden" name="filter_order" value="'.$listOrder.'" />'. "\n"
//.'<input type="hidden" name="filter_order_Dir" value="'.$listDirn.'" />'. "\n"
//.'<input type="hidden" name="filter_currency" value="'.$listCurrency.'" />'. "\n"
//.'<input type="hidden" name="filter_shop_type" value="'.$listShopType.'" />'. "\n"
. HTMLHelper::_('form.token'). "\n";
echo $r->endMainContainer();
echo $r->endForm();
?>
