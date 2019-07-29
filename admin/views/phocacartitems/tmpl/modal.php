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

$app = JFactory::getApplication();
if ($app->isClient('site')) {
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_phocacart/administrator/admin-phocacartitems-modal.min.js', array('version' => 'auto', 'relative' => true));


//$class		= $this->t['n'] . 'RenderAdminviews';
$r 			=  new PhocacartRenderAdminviews();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$function  	= $app->input->getCmd('function', 'jSelectPhocacartitem');
$onclick   	= $this->escape($function);

if (!empty($editor)) {
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	JFactory::getDocument()->addScriptOptions('xtd-phocacartitems', array('editor' => $editor));
	$onclick = "jSelectPhocacartitem";
}


$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

$saveOrder 	= false;
if ($this->t['ordering'] && !empty($this->ordering)) {
	$saveOrder	= $listOrder == 'pc.ordering';
	if ($saveOrder) {
		$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'categoryList', 'phocacartitem-form', strtolower($listDirn), $saveOrderingUrl, false, true);
	}
}
$sortFields = $this->getSortFields();
echo $r->jsJorderTable($listOrder);

echo $r->startFormModal($this->t['o'], $this->t['tasks'], 'phocacartitem-form', 'adminForm', $function);
echo $r->startFilterNoSubmenu();


echo $r->endFilter();
echo $r->startMainContainerNoSubmenu();

echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();






echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->thOrdering('JGRID_HEADING_ORDERING', $listDirn, $listOrder, 'pc');
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-image">'.JText::_($this->t['l'].'_IMAGE').'</th>'."\n";
echo '<th class="ph-sku">'.JHtml::_('grid.sort',  	$this->t['l'].'_SKU', 'a.sku', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title">'.JHtml::_('grid.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.JHtml::_('grid.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
//echo '<th class="ph-parentcattitle">'.JHtml::_('grid.sort', $this->t['l'].'_CATEGORY', 'category_id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">'.JTEXT::_($this->t['l'].'_CATEGORY').'</th>'."\n";
echo '<th class="ph-price">'.JHtml::_('grid.sort', $this->t['l'].'_PRICE', 'a.price', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-price">'.JHtml::_('grid.sort', $this->t['l'].'_ORIGINAL_PRICE', 'a.price_original', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-stock">'.JHtml::_('grid.sort', $this->t['l'].'_IN_STOCK', 'a.stock', $listDirn, $listOrder ).'</th>'."\n";
//echo '<th class="ph-hits">'.JHtml::_('grid.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">'.JTEXT::_($this->t['l'].'_ACCESS').'</th>'."\n";
echo '<th class="ph-language">'.JHtml::_('grid.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-hits">'.JHtml::_('grid.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.JHtml::_('grid.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo '<tbody>'. "\n";

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

$price			= new PhocacartPrice();

if (!empty($this->items)) {
	foreach ($this->items as $i => $item) {
		//if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$urlTask		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'];
//$orderkey   	= array_search($item->id, $this->ordering[$item->catid]);
$orderkey		= 0;
$orderingItem	= 0;
if ($this->t['ordering'] && !empty($this->ordering)) {
	$orderkey   	= array_search($item->id, $this->ordering[$this->t['catid']]);
	$orderingItem	= $orderkey;
}
$ordering		= ($listOrder == 'pc.ordering');
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit. $item->id );
$linkLang		= JRoute::_('index.php?option='.$this->t['o'].'&view=phocacartitem&id='.$this->escape($item->id).'&lang='.$this->escape($item->language));

//$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
$canEditCat	= 0;// FORCE NOT EDITING CATEGORY IN MODAL $user->authorise('core.edit', $this->t['o']);
if ($item->language && JLanguageMultilang::isEnabled()) {
	$tag = strlen($item->language);
	if ($tag == 5) {
		$lang = substr($item->language, 0, 2);
	} else if ($tag == 6) {
		$lang = substr($item->language, 0, 3);
	} else {
		$lang = '';
	}
} else if (!JLanguageMultilang::isEnabled()) {
	$lang = '';
}


$iD = $i % 2;
echo "\n\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->category_id.'" item-id="'.$item->id.'" parents="'.$item->category_id.'" level="0">'. "\n";
echo '<tr class="row'.$iD.'" sortable-group-id="'.$this->t['catid'].'" >'. "\n";

//echo '<td>'.$item->category_id. '/'.$orderkey.'</td>';



echo $r->tdOrder($canChange, $saveOrder, $orderkey, $orderingItem, false);
echo $r->td(JHtml::_('grid.id', $i, $item->id), "small");
echo $r->tdImageCart($this->escape($item->image), 'small', 'productimage', 'small ph-items-image-box');
//echo $r->td($this->escape($item->sku), 'small');

echo $r->td('<span class="ph-editinplace-text ph-eip-sku" id="products:sku:'.(int)$item->id.'">'.$this->escape($item->sku).'</span>', "small");

/*
$checkO = '';
if ($item->checked_out) {
	$checkO .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. JRoute::_($linkEdit).'"><span id="phIdTitle'.$item->id.'">'. $this->escape($item->title).'</span></a>';
} else {
	$checkO .= '<span id="phIdTitle'.$item->id.'">'.$this->escape($item->title).'</span>';// Id needed for displaying Copy Attributes Titles
}
$checkO .= '<br /><span class="smallsub">(<span>'.JText::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
echo $r->td($checkO, "small");*/

$linkBox = '<a class="select-link" href="javascript:void(0)" data-function="'.$this->escape($onclick).'" data-id="'.$item->id.'" data-title="'.$this->escape($item->title).'" data-uri="'. $this->escape($linkLang).'" data-language="'.$this->escape($lang).'">';
$linkBox .= $this->escape($item->title);
$linkBox .= '</a>';

echo $r->td($linkBox, "small");

/*echo $r->td(
	'<div class="btn-group">'.JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange)
	. PhocacartHtmlFeatured::featured($item->featured, $i, $canChange). '</div>',
"small");*/

echo $r->td('<span class="'.$iconStates[$this->escape($item->published)].'" aria-hidden="true"></span>');
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


echo $r->td('<span class="ph-editinplace-text ph-eip-price" id="products:price:'.(int)$item->id.'">'.PhocacartPrice::cleanPrice($item->price).'</span>', "small");
echo $r->td('<span class="ph-editinplace-text ph-eip-price_original" id="products:price_original:'.(int)$item->id.'">'.PhocacartPrice::cleanPrice($item->price_original).'</span>', "small");
//echo $r->td($item->hits, "small");
echo $r->td('<span class="ph-editinplace-text ph-eip-price" id="products:stock:'.(int)$item->id.'">'.PhocacartPrice::cleanPrice($item->stock).'</span>', "small");


echo $r->td($this->escape($item->access_level));

//echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
echo $r->td(JLayoutHelper::render('joomla.content.language', $item));

echo $r->td($item->hits, "small");

echo $r->td($item->id, "small");



echo '</tr>'. "\n";

		//}
	}
} else {
	// No items
	echo '<div class="alert alert-no-items">'.JText::_('JGLOBAL_NO_MATCHING_RESULTS').'</div>';
}
echo '</tbody>'. "\n";

echo $r->tblFoot($this->pagination->getListFooter(), 19);
echo $r->endTable();

echo $this->loadTemplate('batch');

echo $this->loadTemplate('copy_attributes');

echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();

echo '<input type="hidden" name="forcedLanguage" value="'. $app->input->get('forcedLanguage', '', 'CMD') .'" />';
echo $r->endForm();


?>
