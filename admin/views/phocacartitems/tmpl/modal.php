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
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
use Phoca\PhocaCart\I18n\I18nHelper;

// ASSOCIATION
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$app = Factory::getApplication();
if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

$r         = $this->r;
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', $this->t['o']);
$function  = $app->input->getCmd('function', 'jSelectPhocacartitem');
$onclick   = $this->escape($function);

if (!empty($editor)) {
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    Factory::getDocument()->addScriptOptions('xtd-phocacartitems', array('editor' => $editor));
    $onclick = "jSelectPhocacartitem";
}

// Special case for the search field tooltip. TO DO
/*$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => Text::_($searchFilterDesc), 'placement' => 'bottom'));
*/


$iconStates = array(
    -2 => 'icon-trash',
    0 => 'icon-unpublish',
    1 => 'icon-publish',
    2 => 'icon-archive',
);

$saveOrder 			= false;
$saveOrderingUrl 	= '';
if ($this->t['ordering'] && !empty($this->ordering)) {
	$saveOrder	= $listOrder == 'pc.ordering';
	/*$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}*/
	if ($saveOrder && !empty($this->items)) {
		$saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
	}
}
//$sortFields = array();// $this->getSortFields();
echo $r->jsJorderTable($listOrder);

// phocacartitem-form => adminForm

echo $r->startFormModal($this->t['o'], $this->t['tasks'], 'adminForm', 'adminForm', $function);
//echo $r->startFilterNoSubmenu();


//echo $r->endFilter();
echo $r->startMainContainerNoSubmenu();
/*

echo $r->startFilterBar();
echo $r->inputFilterSearch($this->t['l'] . '_FILTER_SEARCH_LABEL', $this->t['l'] . '_FILTER_SEARCH_DESC',
    $this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();
*/

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder, 'pc');
echo $r->secondColumnHeader($listDirn, $listOrder, 'pc');

$options                = array();
$options['listdirn']    = $listDirn;
$options['listorder']   = $listOrder;
$options['count']       = 2;
$options['type']        = 'render';
$options['association'] = I18nHelper::associationsEnabled();
$options['tasks']       = $this->t['tasks'];

$c = new PhocacartRenderAdmincolumns();

$adminColumnProducts = array();
if(!empty($this->t['admin_columns_products'])) {
    foreach ($this->t['admin_columns_products'] as $k => $v) {
        $item = explode('=', $v);

        if (isset($item[0]) && $item[0] != '') {
           $itemO = PhocacartText::filterValue($item[0], 'alphanumeric2');

           if ($itemO != 'phoca_action') {
               $adminColumnProducts[] = $itemO;
           }
        }
    }
}


echo $c->renderHeader($adminColumnProducts, $options);

/*
echo '<th class="ph-image">' . Text::_($this->t['l'] . '_IMAGE') . '</th>' . "\n";
echo '<th class="ph-sku">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_SKU', 'a.sku', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-title">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
//echo '<th class="ph-parentcattitle">'.JHtml::_('searchtools.sort', $this->t['l'].'_CATEGORY', 'category_id', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">' . Text::_($this->t['l'] . '_CATEGORY') . '</th>' . "\n";
echo '<th class="ph-price">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PRICE', 'a.price', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-price">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ORIGINAL_PRICE', 'a.price_original', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-stock">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_IN_STOCK', 'a.stock', $listDirn, $listOrder) . '</th>' . "\n";
//echo '<th class="ph-hits">'.JHtml::_('searchtools.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">' . Text::_($this->t['l'] . '_ACCESS') . '</th>' . "\n";
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

if (!empty($this->items)) {
    foreach ($this->items as $i => $item) {
        //if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
        $j++;

        $urlEdit = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'] . '.edit&id=';
        $urlTask = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
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
        $linkEdit   = Route::_($urlEdit . $item->id);
        $linkLang   = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacartitem&id=' . $this->escape($item->id) . '&lang=' . $this->escape($item->language));

        //$linkCat	= Route::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
        $canEditCat = 0;// FORCE NOT EDITING CATEGORY IN MODAL $user->authorise('core.edit', $this->t['o']);
        if ($item->language && Multilanguage::isEnabled()) {
            $tag = strlen($item->language);
            if ($tag == 5) {
                $lang = substr($item->language, 0, 2);
            } else if ($tag == 6) {
                $lang = substr($item->language, 0, 3);
            } else {
                $lang = '';
            }
        } else if (!Multilanguage::isEnabled()) {
            $lang = '';
        }

        //$linkEditBox = '<a class="select-link phIdTitle' . (int)$item->id . '" href="javascript:void(0)" data-function="' . $this->escape($onclick) . '" data-id="' . $item->id . '" data-title="' . $this->escape($item->title) . '" data-uri="' . $this->escape($linkLang) . '" data-language="' . $this->escape($lang) . '">';

        $linkEditBox = '<a class="select-link phIdTitle' . (int)$item->id . '" href="javascript:void(0)" onclick="if (window.parent) window.parent.'.$this->escape($onclick).'(\''. $item->id.'\', \''. $this->escape(addslashes($item->title)).'\', null, \''. $this->escape($linkLang).'\', \''. $this->escape($lang).'\', null);">';

        $linkEditBox .= $this->escape($item->title);
        $linkEditBox .= '</a>';
        $linkEdit = '';
        $linkEditCat = '';

        echo $r->startTr($i, $this->t['catid']);
        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, false);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, false);

        if (!empty($adminColumnProducts)) {
            foreach ($adminColumnProducts as $k => $v) {

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
                $itemColumn['linkeditbox']    = $linkEditBox;
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

        echo $r->td('<span class="ph-editinplace-text ph-eip-text ph-eip-sku" id="products:sku:' . (int)$item->id . '">' . $this->escape($item->sku) . '</span>', "small");

        /*
        $checkO = '';
        if ($item->checked_out) {
            $checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
        }
        if ($canCreate || $canEdit) {
            $checkO .= '<a href="'. Route::_($linkEdit).'"><span id="phIdTitle'.$item->id.'">'. $this->escape($item->title).'</span></a>';
        } else {
            $checkO .= '<span id="phIdTitle'.$item->id.'">'.$this->escape($item->title).'</span>';// Id needed for displaying Copy Attributes Titles
        }
        $checkO .= '<br /><span class="smallsub">(<span>'.Text::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
        echo $r->td($checkO, "small", 'th');*/
/*
        $linkBox = '<a class="select-link" href="javascript:void(0)" data-function="' . $this->escape($onclick) . '" data-id="' . $item->id . '" data-title="' . $this->escape($item->title) . '" data-uri="' . $this->escape($linkLang) . '" data-language="' . $this->escape($lang) . '">';
        $linkBox .= $this->escape($item->title);
        $linkBox .= '</a>';

        echo $r->td($linkBox, "small");

        /*echo $r->td(
            '<div class="btn-group">'.HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange)
            . PhocacartHtmlFeatured::featured($item->featured, $i, $canChange). '</div>',
        "small");*/
/*
        echo $r->td('<span class="' . $iconStates[$this->escape($item->published)] . '" aria-hidden="true"></span>');
        /*
        if ($canEditCat) {
            $catO = '<a href="'. Route::_($linkCat).'">'. $this->escape($item->category_title).'</a>';
        } else {
            $catO = $this->escape($item->category_title);
        }*/
       /* $catO = array();
        if (isset($this->t['categories'][$item->id])) {
            foreach ($this->t['categories'][$item->id] as $k => $v) {
                if ($canEditCat) {
                    $linkCat = Route::_('index.php?option=' . $this->t['o'] . '&task=' . $this->t['c'] . 'category.edit&id=' . (int)$v['id']);
                    $catO[]  = '<a href="' . Route::_($linkCat) . '">' . $this->escape($v['title']) . '</a>';
                } else {
                    $catO[] = $this->escape($v['title']);
                }
            }
        }

        echo $r->td(implode(' ', $catO), "small");
        //echo $r->td($this->escape($item->access_level), "small");


        echo $r->td('<span class="ph-editinplace-text ph-eip-text ph-eip-price" id="products:price:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->price) . '</span>', "small");
        echo $r->td('<span class="ph-editinplace-text ph-eip-text ph-eip-price_original" id="products:price_original:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->price_original) . '</span>', "small");
        //echo $r->td($item->hits, "small");
        echo $r->td('<span class="ph-editinplace-text ph-eip-text ph-eip-price" id="products:stock:' . (int)$item->id . '">' . PhocacartPrice::cleanPrice($item->stock) . '</span>', "small");


        echo $r->td($this->escape($item->access_level));

        //echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
        echo $r->td(LayoutHelper::render('joomla.content.language', $item), 'small');

        echo $r->td($item->hits, "small");

        echo $r->td($item->id, "small");
*/

        echo $r->endTr();

        //}
    }
} else {
    // No items
    echo '<div class="alert alert-no-items">' . Text::_('JGLOBAL_NO_MATCHING_RESULTS') . '</div>';
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 19);
echo $r->endTable();

//echo $this->loadTemplate('batch');

//echo $this->loadTemplate('copy_attributes');

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();

echo '<input type="hidden" name="forcedLanguage" value="' . $app->input->get('forcedLanguage', '', 'CMD') . '" />';
echo $r->endForm();


?>
