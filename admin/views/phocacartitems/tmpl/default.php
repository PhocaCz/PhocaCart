<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$r         = $this->r;
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', $this->t['o']);

$saveOrder       = false;
$saveOrderingUrl = '';
if ($this->t['ordering'] && !empty($this->ordering)) {
    $saveOrder = $listOrder == 'pc.ordering';
    /*$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}*/
    if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
    }
}
//$sortFields = $this->getSortFields();

/*
$nrColumns = 19;
$assoc     = JLanguageAssociations::isEnabled();
if ($assoc) {
    $nrColumns = 20;
}*/


echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
/*echo $r->startFilter();

//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilter();*/
echo $r->startMainContainer();

/*
echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
						$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);
echo $r->startFilterBar(2);
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilterBar();
echo $r->endFilterBar();
*/
//echo $r->startFilterBar();
echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
//echo $r->endFilterBar();

echo $r->startTable('categoryList');

echo $r->startTblHeader();

//echo $r->thOrderingXML('JGRID_HEADING_ORDERING', $listDirn, $listOrder, 'pc');
//echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo $r->firstColumnHeader($listDirn, $listOrder, 'pc');
echo $r->secondColumnHeader($listDirn, $listOrder, 'pc');


$options                = array();
$options['listdirn']    = $listDirn;
$options['listorder']   = $listOrder;
$options['count']       = 2;
$options['type']        = 'render';
$options['association'] = JLanguageAssociations::isEnabled();
$options['tasks']       = $this->t['tasks'];

$c = new PhocacartRenderAdmincolumns();
echo $c->renderHeader($this->t['admin_columns_products'], $options);



/*
echo '<th class="ph-image">' . Text::_($this->t['l'] . '_IMAGE') . '</th>' . "\n";
echo '<th class="ph-sku">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_SKU', 'a.sku', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-title">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
//echo '<th class="ph-parentcattitle">'.HTMLHelper::_('searchtools.sort', $this->t['l'].'_CATEGORY', 'category_id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">' . Text::_($this->t['l'] . '_CATEGORY') . '</th>' . "\n";
echo '<th class="ph-price">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PRICE', 'a.price', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-price_original">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ORIGINAL_PRICE', 'a.price_original', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-stock">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_IN_STOCK', 'a.stock', $listDirn, $listOrder) . '</th>' . "\n";
//echo '<th class="ph-hits">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">' . Text::_($this->t['l'] . '_ACCESS') . '</th>' . "\n";

if ($options['association']) {
    echo '<th class="ph-association">' . HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder) . '</th>' . "\n";
}
echo '<th class="ph-language">' . HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-hits">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_HITS', 'a.hits', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";
*/


echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr     = "";
$j              = 0;

$price = new PhocacartPrice();

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        //if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
        $j++;


        $urlTask = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
        $urlEdit = $urlTask . '.edit&id=';
        //$orderkey   	= array_search($item->id, $this->ordering[$item->catid]);
        $orderkey     = 0;
        $orderingItem = 0;
        if ($this->t['ordering'] && !empty($this->ordering)) {
            $orderkey     = array_search($item->id, $this->ordering[$this->t['catid']]);
            $orderingItem = $orderkey;
        }

        $ordering   = ($listOrder == 'pc.ordering');
        $canCreate  = $user->authorise('core.create', $this->t['o']);
        $canEdit    = $user->authorise('core.edit', $this->t['o']);
        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
        $canChange  = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
        $linkEdit   = JRoute::_($urlEdit . $item->id);


        //$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
        $canEditCat  = $user->authorise('core.edit', $this->t['o']);
        $linkEditCat = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['c'] . 'category.edit';

        echo $r->startTr($i, $this->t['catid']);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, false);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, false);


        if (!empty($this->t['admin_columns_products'])) {
            foreach ($this->t['admin_columns_products'] as $k => $v) {

                $columnParams                 = array();
                $itemColumn                   = array();
                $itemColumn['i']              = $i;
                $itemColumn['params']         = array();
                $itemColumn['params']['edit'] = false;
                $v                            = PhocacartText::parseDbColumnParameter($v, $itemColumn['params']);
                $itemColumn['name']           = $v;
                $itemColumn['value']          = isset($item->{$v}) ? $item->{$v} : '';
                $itemColumn['id']             = isset($item->id) ? $item->id : 0;
                $itemColumn['idtoken']        = 'products:' . $v . ':' . (int)$itemColumn['id'];
                $itemColumn['cancreate']      = $canCreate;
                $itemColumn['canedit']        = $canEdit;
                $itemColumn['canchange']      = $canChange;
                $itemColumn['linkedit']       = $linkEdit;
                $itemColumn['editclass']      = 'text';
                $itemColumn['editfilter']     = 'text';

                if ($v == 'title') {
                    $itemColumn['cancheckin']       = $canCheckin;
                    $itemColumn['checked_out']      = $item->checked_out;
                    $itemColumn['checked_out_time'] = $item->checked_out_time;
                    $itemColumn['editor']           = $item->editor;
                    $itemColumn['valuealias']       = $item->alias;
                    $itemColumn['namealias']        = 'alias';
                    $itemColumn['idtokencombined']  = 'products:alias:' . (int)$itemColumn['id'];
                }

                if ($v == 'published') {
                    $itemColumn['valuefeatured'] = $item->featured;
                    $itemColumn['namefeatured']  = 'featured';
                }

                if ($v == 'categories') {
                    $itemColumn['value']            = $this->t['categories'];
                    $itemColumn['caneditcategory']  = $canEditCat;
                    $itemColumn['linkeditcategory'] = $linkEditCat;
                }

                if ($v == 'language') {
                    $itemColumn['value']                 = new stdClass();
                    $itemColumn['value']->language       = $item->language;
                    $itemColumn['value']->language_title = $item->language_title;
                    $itemColumn['value']->language_image = $item->language_image;
                }

                echo $c->item($v, $itemColumn, $options);
            }
        }

/*
        echo $r->tdImageCart($this->escape($item->image), 'small', 'productimage', 'small ph-items-image-box');
        //echo $r->td($this->escape($item->sku), 'small');

        echo $r->td('<span class="ph-editinplace-text ph-eip-sku" id="products:sku:' . (int)$item->id . '">' . $this->escape($item->sku) . '</span>', "small");

        $checkO = '';
        if ($item->checked_out) {
            $checkO .= Joomla\CMS\HTML\HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'] . '.', $canCheckin);
        }
        if ($canCreate || $canEdit) {
            $checkO .= '<a href="' . JRoute::_($linkEdit) . '"><span id="phIdTitle' . $item->id . '">' . $this->escape($item->title) . '</span></a>';
        } else {
            $checkO .= '<span id="phIdTitle' . $item->id . '">' . $this->escape($item->title) . '</span>';// Id needed for displaying Copy Attributes Titles
        }
        $checkO .= '<br /><span class="smallsub">(<span>' . JText::_($this->t['l'] . '_FIELD_ALIAS_LABEL') . ':</span>' . $this->escape($item->alias) . ')</span>';
        echo $r->td($checkO, "small");

        echo $r->td(
            '<div class="btn-group">' . Joomla\CMS\HTML\HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', $canChange)
            . PhocacartHtmlFeatured::featured($item->featured, $i, $canChange) . '</div>',
            "small");

        $catO = array();
        if (isset($this->t['categories'][$item->id])) {
            foreach ($this->t['categories'][$item->id] as $k => $v) {
                if ($canEditCat) {
                    $linkCat = JRoute::_('index.php?option=' . $this->t['o'] . '&task=' . $this->t['c'] . 'category.edit&id=' . (int)$v['id']);
                    $catO[]  = '<a href="' . JRoute::_($linkCat) . '">' . $this->escape($v['title']) . '</a>';
                } else {
                    $catO[] = $this->escape($v['title']);
                }
            }
        }

        echo $r->td(implode(' ', $catO), "small");
        //echo $r->td($this->escape($item->access_level), "small");


        echo $r->td('<span class="ph-editinplace-text ph-eip-price" id="products:price:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->price) . '</span>', "small");
        echo $r->td('<span class="ph-editinplace-text ph-eip-price_original" id="products:price_original:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->price_original) . '</span>', "small");
        //echo $r->td($item->hits, "small");
        echo $r->td('<span class="ph-editinplace-text ph-eip-price" id="products:stock:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->stock) . '</span>', "small");


        echo $r->td($this->escape($item->access_level));

        if ($options['association']) {
            if ($item->association) {
                echo $r->td(Joomla\CMS\HTML\HTMLHelper::_('phocacartitem.association', $item->id));
            } else {
                echo $r->td('');
            }
        }

        //echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
        echo $r->td(JLayoutHelper::render('joomla.content.language', $item));
        echo $r->td($item->hits, "small");

        echo $r->td($item->id, "small");

*/



        echo $r->endTr();

        //}
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), $options['count']);
echo $r->endTable();

echo $this->loadTemplate('batch');

echo $this->loadTemplate('copy_attributes');

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();


?>
