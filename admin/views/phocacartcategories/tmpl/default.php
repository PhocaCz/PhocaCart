<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');


$r 			= $this->r;
$user		= JFactory::getUser();
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


$nrColumns = 10;
$assoc     = JLanguageAssociations::isEnabled();
if ($assoc) {$nrColumns = 11;}

echo $r->jsJorderTable($listOrder);

//echo '<div class="clearfix"></div>';


echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
//echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->endFilter();

echo $r->startMainContainer();

if ($this->t['search']) {
	echo '<div class="alert alert-message">' . JText::_('COM_PHOCACART_SEARCH_FILTER_IS_ACTIVE') .'</div>';
}

//echo $r->startFilterBar();
/*
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterLevels('COM_PHOCACART_SELECT_MAX_LEVELS', $this->state->get('filter.level'));
echo $r->endFilterBar();
*/
//echo $r->endFilterBar();

echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

//echo $r->thOrderingXML('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
//echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', $this->t['l'].'_PARENT_CATEGORY', 'parentcat_title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-productcount">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', $this->t['l'].'_PRODUCT_COUNT', 'a.count_products', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">'.JTEXT::_($this->t['l'].'_ACCESS').'</th>'."\n";

if ($assoc) {
    echo '<th class="ph-association">' . Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder) . '</th>' . "\n";
}

echo '<th class="ph-language">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-hits">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$orderkey   	= array_search($item->id, $this->ordering[$item->parent_id]);
$ordering		= ($listOrder == 'a.ordering');
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit.(int) $item->id );
$linkParent		= JRoute::_( $urlEdit.(int) $item->parent_id );
$canEditParent	= $user->authorise('core.edit', $this->t['o']);

$parentsStr = '';
if (isset($item->parentstree)) {
	$parentsStr = ' '.$item->parentstree;
}
if (!isset($item->level)) {
	$item->level = 0;
}

$iD = $i % 2;
//echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);
echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->parent_id.'" item-id="'.$item->id.'" parents="'.$parentsStr.'" level="'. $item->level.'">'. "\n";


//echo $r->tdOrder($canChange, $saveOrder, $orderkey, $item->ordering);
//echo $r->td(Joomla\CMS\HTML\HTMLHelper::_('grid.id', $i, $item->id), "small");
echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

$checkO = '';
if ($item->checked_out) {
	$checkO .= Joomla\CMS\HTML\HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'">'. $this->escape($item->title).'</a>';
} else {
	$checkO .= $this->escape($item->title);
}
$checkO .= ' <span class="smallsub">(<span>'.JText::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
echo $r->td($checkO, "small");
echo $r->td(Joomla\CMS\HTML\HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

if ($canEditParent) {
	$parentO = '<a href="'. JRoute::_($linkParent).'">'. $this->escape($item->parentcat_title).'</a>';
} else {
	$parentO = $this->escape($item->parentcat_title);
}
echo $r->td($parentO, "small");


$pC = '<div class="center">'.$item->count_products;
if (PhocacartUtils::validateDate($item->count_date)) {
    $pC .= '<br><small class="nowrap">('.Joomla\CMS\HTML\HTMLHelper::_('date', $item->count_date, 'd-m-Y H:i').')</small>';
}
$pC .= '</div>';
echo $r->td($pC, "small");

echo $r->td($this->escape($item->access_level), "small");

if ($assoc) {
    if ($item->association) {
        echo $r->td(Joomla\CMS\HTML\HTMLHelper::_('phocacartcategory.association', $item->id));
    } else {
        echo $r->td('');
    }
}

//echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
echo $r->td(JLayoutHelper::render('joomla.content.language', $item));
echo $r->td($item->hits, "small");
echo $r->td($item->id, "small");

echo $r->endTr();

		}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), $nrColumns);
echo $r->endTable();

echo $this->loadTemplate('batch');

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
