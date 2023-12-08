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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$d = new PhocacartPrice();
$d->setCurrency(1, 6);

$b = new PhocacartPrice();
$b->setCurrency(1, 0);

$shipping = new PhocacartShipping();
$payment = new PhocacartPayment();

// Display additional info about shipping or payment
// For this we need additional tr table rows so we need to change table CSS
$tableClass = '';
if ($this->t['filter-ps-opened'] == 1) {
    $tableClass= 'join';
}

$cols = 13;// number of columns


$r               = $this->r;
$user            = Factory::getUser();
$userId          = $user->get('id');
$listOrder       = $this->escape($this->state->get('list.ordering'));
$listDirn        = $this->escape($this->state->get('list.direction'));
$canOrder        = $user->authorise('core.edit.state', $this->t['o']);
$saveOrder       = $listOrder == 'a.ordering';
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
/*echo $r->inputFilterSearch($this->t['l'].'_FILTER_SEARCH_LABEL', $this->t['l'].'_FILTER_SEARCH_DESC',
							$this->escape($this->state->get('filter.search')));
echo $r->inputFilterSearchClear('JSEARCH_FILTER_SUBMIT', 'JSEARCH_FILTER_CLEAR');
echo $r->inputFilterSearchLimit('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC', $this->pagination->getLimitBox());
echo $r->selectFilterDirection('JFIELD_ORDERING_DESC', 'JGLOBAL_ORDER_ASCENDING', 'JGLOBAL_ORDER_DESCENDING', $listDirn);
echo $r->selectFilterSortBy('JGLOBAL_SORT_BY', $sortFields, $listOrder);

echo $r->startFilterBar(2);
echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
echo $r->endFilterBar();*/

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
//echo $r->endFilterBar();


$idMd       = 'phEditStatusModal';
$textButton = 'COM_PHOCACART_EDIT_STATUS';
$w          = 500;
$h          = 400;

$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMd, $textButton, $w, $h, true);


echo $r->startTable('orderList', $tableClass);

echo $r->startTblHeader();

echo $r->thOrderingXML('JGRID_HEADING_ORDERING', $listDirn, $listOrder);
echo $r->thCheck('JGLOBAL_CHECK_ALL');
echo '<th class="ph-order">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ORDER_NUMBER', 'order_number', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-user">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_USER', 'user_username', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-status">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_STATUS', 'a.status_id', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-info">' . Text::_($this->t['l'] . '_INFO') . '</th>' . "\n";
echo '<th class="ph-action">' . Text::_($this->t['l'] . '_ACTION') . '</th>' . "\n";
echo '<th class="ph-total-center">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TOTAL', 'total_amount', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-invoice">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_INVOICE_NUMBER', 'invoice_number', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-date">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DATE_ADDED', 'a.date', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-date">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_DATE_MODIFIED', 'a.modified', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$price          = new PhocacartPrice();
$originalOrders = array();
$parentsStr     = "";
$j              = 0;


if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        //if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
        $j++;


        $urlEdit           = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'] . '.edit&id=';
        $urlTask           = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
        $orderkey          = array_search($item->id, $this->ordering[0]);
        $ordering          = ($listOrder == 'a.ordering');
        $canCreate         = $user->authorise('core.create', $this->t['o']);
        $canEdit           = $user->authorise('core.edit', $this->t['o']);
        $canCheckin        = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
        $canChange         = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
        $linkEdit          = Route::_($urlEdit . $item->id);
        $linkStatus        = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditstatus&tmpl=component&id=' . (int)$item->id);
        $linkStatusHandler = 'rel="{handler: \'iframe\', size: {x: 580, y: 460}, onClose:function(){var js = window.location.reload();}}"';

        $linkOrderView   = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacartorderview&tmpl=component&id=' . (int)$item->id . '&type=1');
        $linkInvoiceView = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacartorderview&tmpl=component&id=' . (int)$item->id . '&type=2');
        $linkDelNoteView = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacartorderview&tmpl=component&id=' . (int)$item->id . '&type=3');

        //$linkOrderViewHandler= 'rel="{handler: \'iframe\', size: {x: 580, y: 460}}"';
        $linkOrderViewHandler = 'onclick="window.open(this.href, \'orderview\', \'width=780,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';


        // Specific multiselect in media/com_phocacart/js/administrator/phocacart.js for orders view where two columns are together
        // ph-row-multiselect (active row for select) vs. ph-row-no-multiselect (row which is inactive, only has some additional info)
        // the checkbox for selecting row has class: ph-select-row in Adminviews.php in method firstColumn
        echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0, $item->id, -1, '', 'ph-row-multiselect ');

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

        $checkO = '';
        if ($item->checked_out) {
            $checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'] . '.', $canCheckin);
        }
        if ($canCreate || $canEdit) {
            $checkO .= '<a href="' . Route::_($linkEdit) . '">' . $this->escape(PhocacartOrder::getOrderNumber($item->id, $item->date, $item->order_number)) . '</a>';
        } else {
            $checkO .= $this->escape($item->title);
        }
        echo $r->td($checkO, "small", 'th');

        if ($item->user_id > 0) {
            $userO = $this->escape($item->user_name);
            if (isset($item->user_username)) {
                $userO .= ' <small>(' . $item->user_username . ')</small>';
            }
        } else {

            $userO    = '<span class="label label-info badge bg-info">' . Text::_('COM_PHOCACART_GUEST') . '</span>';
            $userData = PhocacartOrder::getOrderCustomerData($item->id);

            if (isset($userData[0]['name_first']) && isset($userData[0]['name_last'])) {
                $userO .= '<br /><span>' . $userData[0]['name_first'] . ' ' . $userData[0]['name_last'] . '</span>';
            }
        }
        echo $r->td($userO, "small");

        //$status			= PhocacartOrderStatus::getStatus((int)$item->status_id, $item->id);
        //$statusSelect	= JHtml::_('select.genericlist',  $status['data'],  'phorderstatus', 'class="form-control"', 'value', 'text', (int)$item->status_id, 'phorderstatus'.(int)$item->id );
        $statusClass = PhocacartUtilsSettings::getOrderStatusClass($item->status_title);
        $status      = '<span class="' . $statusClass . '">' . $this->escape(Text::_($item->status_title)) . '</span>';
        //$status .= ' <a class="modal_edit_status ph-u" href="'.$linkStatus.'" '.$linkStatusHandler.' ><small>'.Text::_('COM_PHOCACART_EDIT_STATUS').'</small></a>';

        $status .= ' <span><a href="#' . $idMd . '" role="button" class="ph-u ph-no-wrap ' . $idMd . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkStatus . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a></span>';

        echo $r->td($status, "small");


        // INFO
        $info = '<div class="ph-order-info-box">';
        if ($item->type == 2) {

            // POS
            if (isset($item->vendor_username) && isset($item->vendor_name)) {
                $vendorO = $this->escape($item->vendor_name);
                $vendorO .= ' <small>(' . $item->vendor_username . ')</small>';
                $info    .= '<span class="label label-success badge bg-success">' . Text::_('COM_PHOCACART_VENDOR') . ': ' . $vendorO . '</span>';
            }

            if (isset($item->section_name)) {
                $section = $this->escape($item->section_name);
                $info    .= '<span class="label label-primary">' . Text::_('COM_PHOCACART_SECTION') . ': ' . $section . '</span>';
            }
            if (isset($item->unit_name)) {
                $unit = $this->escape($item->unit_name);
                $info .= '<span class="label label-info badge bg-info">' . Text::_('COM_PHOCACART_UNIT') . ': ' . $unit . '</span>';
            }
            if (isset($item->ticket_id) && (int)$item->ticket_id > 0) {

                $info .= '<span class="label label-warning badge bg-warning">' . Text::_('COM_PHOCACART_TICKET') . ': ' . $item->ticket_id . '</span>';
            }
        } else if ($item->type == 1) {
            $info = '<span class="label label-info badge bg-info">' . Text::_('COM_PHOCACART_ONLINE_SHOP') . '</span>';
            if (isset($item->shipping_name)) {
                $info .= '<br><span class="badge text-bg-light mt-1">' . $item->shipping_name . '</span>';
            }
            if (isset($item->payment_name)) {
                $info .= '<br><span class="badge text-bg-light mt-1">' . $item->payment_name . '</span>';
            }
        }
        $info .= '</div>';

        echo $r->td($info, "small");
        // ACTION
        $view = '<div class="ph-action-row">';

        $view .= '<a href="' . $linkOrderView . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['search'] . '  ph-icon-success', 'title="' . Text::_('COM_PHOCACART_VIEW_ORDER') . '"', '', '', 'fa5') . '</a>';
        $view .= ' <a href="' . $linkInvoiceView . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['list-alt'] . '  ph-icon-danger', 'title="' . Text::_('COM_PHOCACART_VIEW_INVOICE') . '"', '', '', 'fa5') . '</a>';
        $view .= ' <a href="' . $linkDelNoteView . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['barcode'] . '  ph-icon-warning', 'title="' . Text::_('COM_PHOCACART_VIEW_DELIVERY_NOTE') . '"', '', '', 'fa5') . '</a>';

        $view .= '</div>';

        if ($this->t['plugin-pdf'] == 1 && $this->t['component-pdf']) {

            $formatPDF = '&format=pdf';
            $view .= '<div class="ph-action-row">';
            $view      .= '<a href="' . $linkOrderView  . $formatPDF . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['search'] . '  ph-icon-success', 'title="' . Text::_('COM_PHOCACART_VIEW_ORDER') . '"', '', '', 'fa5') . '<br /><span class="ph-icon-success-txt">' . Text::_('COM_PHOCACART_PDF') . '</span></a>';
            $view      .= ' <a href="' . $linkInvoiceView  . $formatPDF . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['list-alt'] . '  ph-icon-danger', 'title="' . Text::_('COM_PHOCACART_VIEW_INVOICE') . '"', '', '', 'fa5') . '<br /><span class="ph-icon-danger-txt">' . Text::_('COM_PHOCACART_PDF') . '</span></a>';
            $view      .= ' <a href="' . $linkDelNoteView  . $formatPDF . '" class="btn btn-transparent btn-small btn-xs ph-btn" role="button" ' . $linkOrderViewHandler . '>' . PhocacartRenderIcon::icon($this->s['i']['barcode'] . '  ph-icon-warning', 'title="' . Text::_('COM_PHOCACART_VIEW_DELIVERY_NOTE') . '"', '', '', 'fa5') . '<br /><span class="ph-icon-warning-txt">' . Text::_('COM_PHOCACART_PDF') . '</span></a>';

            $view .= '</div>';
        }
        echo $r->td($view, "small");


        $price->setCurrency($item->currency_id, $item->id);

        $amount = (isset($item->total_amount_currency) && $item->total_amount_currency > 0) ? $price->getPriceFormat($item->total_amount_currency, 0, 1) : $price->getPriceFormat($item->total_amount);
        echo $r->td($amount, "small ph-right ph-p-r-med ph-no-wrap");

        echo $r->td($this->escape(PhocacartOrder::getInvoiceNumber($item->id, $item->date, $item->invoice_number, $item->invoice_number_id)), "small");

        echo $r->td(HTMLHelper::date($item->date, Text::_('DATE_FORMAT_LC5')), "small");
        echo $r->td(HTMLHelper::date($item->modified, Text::_('DATE_FORMAT_LC5')), "small");

        echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', $canChange), "small");

        echo $r->td($item->id, "small");





        echo $r->endTr();


        // Display additional information about shipping and paymnet
        if ($this->t['filter-ps-opened'] == 1) {

            // Specific multiselect in media/com_phocacart/js/administrator/phocacart.js for orders view where two columns are together
            // ph-row-multiselect (active row for select) vs. ph-row-no-multiselect (row which is inactive, only has some additional info)
            // the checkbox for selecting row has class: ph-select-row in Adminviews.php in method firstColumn
            echo $r->startTr($i, $item->id, 0, -1, '', 'ph-row-no-multiselect');

            echo '<td colspan="'.$cols.'"><div class="ph-order-info-box">';


            if ($item->shipping_id > 0) {

                $paramsShipping = json_decode($item->params_shipping, true);
                echo '<div class="ph-order-info-box-shipping">';
                echo '<h4>'. Text::_('COM_PHOCACART_SHIPPING_INFORMATION'). '</h4>';



                $titleExistsS = 0;
                if (isset($paramsShipping['title']) && $paramsShipping['title'] != '') {
                    echo '<div><b>' . Text::_('COM_PHOCACART_SHIPPING_METHOD') . '</b>: ' . $paramsShipping['title'] . '</div>';
                    $titleExistsS = 1;
                }
                if (isset($paramsShipping['method']) && $paramsShipping['method'] != '') {
                    $shippingInfo             = $shipping->getShippingMethod($item->shipping_id);
                    if ($titleExistsS == 0 && isset($shippingInfo->title) && $shippingInfo->title != '') {
                        echo '<div><b>' . Text::_('COM_PHOCACART_SHIPPING_METHOD') . '</b>: ' . $shippingInfo->title . '</div>';
                    }

                    $results = Dispatcher::dispatch(new Event\Shipping\GetShippingBranchInfoAdminList('com_phocacart.phocacartorders', $item, $shippingInfo, [
                      'pluginname' => $paramsShipping['method'],
                    ]));

                    if (!empty($results)) {
                        foreach ($results as $k => $v) {
                            if ($v != false && isset($v['content']) && $v['content'] != '') {
                                echo $v['content'];
                            }
                        }
                    }
                }
                echo  '</div>';
            }

            if ($item->payment_id > 0) {

                $paramsPayment = json_decode($item->params_payment, true);

                echo '<div class="ph-order-info-box-payment">';
                echo '<h4>'. Text::_('COM_PHOCACART_PAYMENT_INFORMATION'). '</h4>';

                $titleExistsP = 0;
                if (isset($paramsPayment['title']) && $paramsPayment['title'] != '') {
                    echo '<div><b>' . Text::_('COM_PHOCACART_PAYMENT_METHOD') . '</b>: ' . $paramsPayment['title'] . '</div>';
                    $titleExistsP = 1;
                }

                if (isset($paramsPayment['method']) && $paramsPayment['method'] != '') {

                    $paymentInfo             = $payment->getPaymentMethod($item->payment_id);
                    if ($titleExistsP == 0 && isset($paymentInfo->title) && $paymentInfo->title != '') {
                        echo '<div><b>' . Text::_('COM_PHOCACART_PAYMENT_METHOD') . '</b>: ' . $paymentInfo->title . '</div>';
                    }

                    $results = Dispatcher::dispatch(new Event\Payment\GetPaymentBranchInfoAdminList('com_phocacart.phocacartorders', $item, $paymentInfo, [
                      'pluginname' => $paramsPayment['method'],
                    ]));

                    if (!empty($results)) {
                        foreach ($results as $k => $v) {
                            if ($v != false && isset($v['content']) && $v['content'] != '') {
                                echo $v['content'];
                            }
                        }
                    }

                }
                echo  '</div>';
            }


            echo  '</div></td>';
            echo $r->endTr();
        }
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 13);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
