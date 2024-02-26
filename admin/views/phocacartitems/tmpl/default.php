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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\I18n\I18nHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$r         = $this->r;
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', $this->t['o']);

$saveOrder              = false;
$saveOrderingUrl        = '';
$saveOrderCatSelected   = false;

// Is ordering selected as ordering?
if ($this->t['ordering'] && !empty($this->ordering)) {
    $saveOrder = $listOrder == 'pc.ordering';

    // Joomla BUG: https://github.com/joomla/joomla-cms/issues/36346 $this->t['catid']
    // Add catid to the URL instead of sending in POST

    if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = $r->saveOrder($this->t, $listDirn, $this->t['catid']);
    }
    $saveOrderCatSelected = true;
}

echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');

echo $r->startMainContainer();

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));

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
$options['association'] = I18nHelper::associationsEnabled();
$options['tasks']       = $this->t['tasks'];

$c = new PhocacartRenderAdmincolumns();
echo $c->renderHeader($this->t['admin_columns_products'], $options);

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr     = "";
$j              = 0;

$price = new PhocacartPrice();

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        $j++;

        $urlTask = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
        $urlEdit = $urlTask . '.edit&id=';
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

        $canEditCat  = $user->authorise('core.edit', $this->t['o']);
        $linkEditCat = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['c'] . 'category.edit';

        echo $r->startTr($i, $this->t['catid']);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, $saveOrderCatSelected);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $orderingItem, $saveOrderCatSelected);


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
        echo $r->endTr();
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
