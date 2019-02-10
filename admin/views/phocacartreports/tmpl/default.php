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

$r 			=  new PhocacartRenderAdminviews();
$user		= JFactory::getUser();
//$userId		= $user->get('id');
$listOrder			= $this->escape($this->state->get('list.ordering'));
$listDirn			= $this->escape($this->state->get('list.direction'));
//$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
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
//echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							//$this->escape($this->state->get('filter.search')));
//echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
//echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
//echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
//echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);
//echo $r->endFilterBar();



$ascDir = $descDir = '';
if ($listDirn == 'asc') {$ascDir = 'selected="selected"';}
if ($listDirn == 'desc') {$descDir = 'selected="selected"';}
echo '<div class="ph-inline-param">'. "\n"
.'<label for="directionTable" class="element-invisible">' .JText::_('JFIELD_ORDERING_DESC').'</label>'. "\n"
.'<select name="filter_order_Dir" id="directionTable" class="input-medium">'. "\n"
.'<option value="">' .JText::_('JFIELD_ORDERING_DESC').'</option>'. "\n"
.'<option value="asc" '.$ascDir.'>' . JText::_('JGLOBAL_ORDER_ASCENDING').'</option>'. "\n"
.'<option value="desc" '.$descDir.'>' . JText::_('JGLOBAL_ORDER_DESCENDING').'</option>'. "\n"
.'</select>'. "\n"
.'</div>'. "\n";

echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.JText::_('JGLOBAL_SORT_BY').'</label>'. "\n"
.'<select name="filter_order" id="sortTable" class="input-medium">'. "\n"
.'<option value="">'.JText::_('JGLOBAL_SORT_BY').'</option>'. "\n"
. JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder). "\n"
.'</select>'. "\n"
.'</div>'. "\n";

$listCurrency	= $this->escape($this->state->get('filter.currency'));
$currencies 	= PhocacartCurrency::getAllCurrencies();

echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.JText::_('COM_PHOCACART_SELECT_CURRENCY').'</label>'. "\n"
.'<select name="filter_currency" id="currencyTable" class="input-medium">'. "\n"
.'<option value="">'.JText::_('COM_PHOCACART_SELECT_CURRENCY').'</option>'. "\n";
if (!empty($currencies)) {
    echo JHtml::_('select.options', $currencies, 'value', 'text', $listCurrency). "\n";
}
echo '</select>'. "\n"
.'</div>'. "\n";

$listShopType	= $this->escape($this->state->get('filter.shop_type'));
$shopTypes 		= PhocacartUtilsSettings::getShopTypes();
echo '<div class="ph-inline-param">'. "\n"
.'<label for="sortTable" class="element-invisible">'.JText::_('COM_PHOCACART_SELECT_CURRENCY').'</label>'. "\n"
.'<select name="filter_shop_type" id="shopTypeTable" class="input-medium">'. "\n"
. JHtml::_('select.options', $shopTypes, 'value', 'text', $listShopType). "\n"
.'</select>'. "\n"
.'</div>'. "\n";

echo '<div style="clear:both"></div>';

// DATE FROM - DATE TO
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

// DATE FROM
$name		= "filter_date_from";
$id			= 'filter_date_from';
$format 	= '%Y-%m-%d';
$attributes = '';
$valueFrom 	= $this->escape($this->state->get('filter.date_from', PhocacartDate::getCurrentDate(30)));

echo '<div class="ph-inline-param">'. JText::_('COM_PHOCACART_DATE_FROM') . ': ';
echo  JHtml::_('calendar', $valueFrom, $name, $id, $format, $attributes).'</div>';

//DATE TO
$name		= "filter_date_to";
$id			= 'filter_date_to';
$valueTo 	= $this->escape($this->state->get('filter.date_to', PhocacartDate::getCurrentDate()));


echo '<div class="ph-inline-param">'. JText::_('COM_PHOCACART_DATE_TO') . ': ';
echo  JHtml::_('calendar', $valueTo, $name, $id, $format, $attributes).'</div>';



echo '<div class="ph-inline-param">';
//echo '<input type="hidden" name="filter_date_from" value="'.$this->escape($this->state->get('filter.date_from')).'" />'. "\n";
//echo '<input type="hidden" name="filter_date_to" value="'.$this->escape($this->state->get('filter.date_to')).'" />'. "\n";
echo '<input type="hidden" name="limitstart" value="0" />'. "\n";
echo '<input type="hidden" name="limit" value="" />'. "\n";
echo JHtml::_('form.token');
echo '<input class="btn btn-success" type="submit" name="submit" value="'.JText::_('COM_PHOCACART_SELECT').'" /></div>';


echo $r->endFilterBar();




echo '<p>&nbsp;</p>';

echo '<div class="row-fluid ph-admin-stat-row">';
echo $this->loadTemplate('report');
echo '</div>';// end row

echo '<input type="hidden" name="task" value="" />'. "\n"
//.'<input type="hidden" name="filter_order" value="'.$listOrder.'" />'. "\n"
//.'<input type="hidden" name="filter_order_Dir" value="'.$listDirn.'" />'. "\n"
//.'<input type="hidden" name="filter_currency" value="'.$listCurrency.'" />'. "\n"
//.'<input type="hidden" name="filter_shop_type" value="'.$listShopType.'" />'. "\n"
. JHtml::_('form.token'). "\n";
echo $r->endMainContainer();
echo $r->endForm();
?>
