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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$r = $this->r;
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

echo $r->startMainContainer();

$idMd = 'phViewCartModal';
$textButton = 'COM_PHOCACART_VIEW_CART';
$w = 500;
$h = 400;
$rV = new PhocacartRenderAdminview();
echo $rV->modalWindowDynamic($idMd, $textButton, $w, $h, true);

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-name">'.HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_NAME', 'u.name', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-status">'.Text::_($this->t['l'].'_STATUS').'</th>'."\n";
echo '<th class="ph-action">' . Text::_($this->t['l'] . '_INFO') . '</th>' . "\n";
echo '<th class="ph-group">'.Text::_($this->t['l'].'_GROUPS').'</th>'."\n";
echo '<th class="ph-name">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_FIRST_NAME_LABEL', 'a.name_first', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-name">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_LAST_NAME_LABEL', 'a.name_last', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-address">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_ADDRESS_1_LABEL', 'a.address_1', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-email">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_EMAIL_LABEL', 'u.email', $listDirn, $listOrder ).'</th>'."\n";
//echo '<th class="ph-published">'.JHtml::_('searchtools.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'u.id', $listDirn, $listOrder ).'</th>'."\n";



echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {

	$emailConflict	= 0;


	foreach ($this->items as $i => $item) {
		//if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$urlTask		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'];
$orderkey   	= array_search($item->id, $this->ordering[0]);
$ordering		= ($listOrder == 'a.ordering');
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= Route::_( $urlEdit. $item->user_id );
$linkCart 		= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacartcart&tmpl=component&userid='.(int)$item->cartuserid . '&vendorid='.(int)$item->cartvendorid . '&ticketid='.(int)$item->cartticketid . '&unitid='.(int)$item->cartunitid . '&sectionid='.(int)$item->cartsectionid  );
$linkCartHandler= 'rel="{handler: \'iframe\', size: {x: 580, y: 460}}"';



echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

        echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

$checkO = '';
if ($item->checked_out) {
	$checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
}
//if (($canCreate || $canEdit) && (int)$item->id > 0) {
if ($canCreate || $canEdit) {
	$checkO .= '<a href="'. Route::_($linkEdit).'">'. $this->escape($item->user_name).'</a>';
} else {
	$checkO .= $this->escape($item->user_name);
}

if (isset($item->user_username)) {
	$checkO .= ' <small>('.$item->user_username.')</small>';
}
echo $r->td($checkO, "small", 'th');

// Status
// NOT ACTIVE
$active = 0;
if ((int)$item->id < 1 && (int)$item->cartuserid < 1) {
	echo $r->td( '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_NOT_ACTIVE').'</span>', "small");
}

// ORDER MADE
else if ( (int)$item->orderuserid > 0 ) {
	$o = '<span class="label label-success badge bg-success">'.Text::_('COM_PHOCACART_ACTIVE_ORDER').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.Text::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. Text::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
	$active = 1;
}

// ADDED BILLING AND SHIPPING ADDRESS
else if ((int)$item->id > 0 && ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {
	$o = '<span class="label label-warning badge bg-warning label-info">'.Text::_('COM_PHOCACART_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.Text::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. Text::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
	$active = 1;
}


// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0 || ($item->name_last != '' || $item->name_first != '' || $item->city != '' || $item->address_1 != '')) {

	$o = '<span class="label label-warning badge bg-warning label-warning">'.Text::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.Text::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. Text::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
	$active = 1;
}

// ADDED ITEMS TO CART BUT NO ORDER, NO BILLING OR SHIPPING ADDRESS
else if ( (int)$item->cartuserid > 0) {

	$o = '<span class="label label-warning badge bg-warning label-warning">'.Text::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span>';
	if ((int)$item->cartuserid > 0) {
		//$o .= ' <a class="modal_view_cart ph-u" href="'.$linkCart.'" '.$linkCartHandler.' ><small>'.Text::_('COM_PHOCACART_VIEW_CART').'</small></a>';
		$o .= ' <span><a href="#'.$idMd.'" role="button" class="ph-u '.$idMd.'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="'.$linkCart.'" data-height="'.$h.'" data-width="'.$w.'">'. Text::_($textButton) . '</a></span>';
	}
	echo $r->td(  $o, "small");
	$active = 1;
}

// OTHER
else {
	echo $r->td( '<span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_NOT_ACTIVE').'</span>', "small");
	//echo $r->td('-', "small");
}


// INFO
$info = '<div class="ph-order-info-box">';
if ($active == 1 && isset($item->vendor_id) && (int)$item->vendor_id > 0) {

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
} else if ($active == 1) {
	$info = '<span class="label label-info badge bg-info">' . Text::_('COM_PHOCACART_ONLINE_SHOP') . '</span>';
} else {
	$info = '';
}
$info .= '</div>';

echo $r->td($info, "small");

// GROUP
if (isset($item->usergroups) && $item->usergroups != '') {
	$groupsA = explode(',', $item->usergroups);
	asort($groupsA);
	$groupsI = '';
	foreach($groupsA as $k => $v) {
		$groupsI .= ' '.Text::_($v);
	}
	echo $r->td($groupsI, "small");
} else {
	echo $r->td('', "small");
}


echo $r->td($item->name_last, "small");
echo $r->td($item->name_first, "small");
echo $r->td($item->address_1, "small");

$email 			= $item->email;
if ($item->email != $item->user_email) {
	$email = Text::_('COM_PHOCACART_EMAIL_CART'). ': '. $item->email
	. '<br />'. Text::_('COM_PHOCACART_EMAIL_SYSTEM'). ': '.$item->user_email . ' <span class="ph-important-text">*</span>';
	$emailConflict = 1;
}

echo $r->td($email, "small");
echo $r->td($item->user_id, "small");

echo $r->endTr();

		//}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 11);
echo $r->endTable();

echo '<div class="ph-notes-box"><h3>'.Text::_('COM_PHOCACART_NOTES').'</h3>';
echo '<div><span class="label label-important label-danger badge bg-danger">'.Text::_('COM_PHOCACART_NOT_ACTIVE').'</span> ... '.Text::_('COM_PHOCACART_NOTE_NOT_ACTIVE').'</div>';
echo '<div><span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_PARTIALLY_ACTIVE').'</span> ... '.Text::_('COM_PHOCACART_NOTE_PARTIALLY_ACTIVE').'</div>';
echo '<div><span class="label label-info badge bg-info">'.Text::_('COM_PHOCACART_ACTIVE').'</span> ... '.Text::_('COM_PHOCACART_NOTE_ACTIVE').'</div>';
echo '<div><span class="label label-success badge bg-success">'.Text::_('COM_PHOCACART_ACTIVE_ORDER').'</span> ... '.Text::_('COM_PHOCACART_NOTE_ACTIVE_ORDER').'</div>';

if ($emailConflict == 1) {
	echo '<div>&nbsp;</div>';
	echo '<div><span class="ph-important-text">*</span> ... '.Text::_('COM_PHOCACART_NOTE_DIFFERENT_EMAILS').'</div>';
}

echo '</div>';


echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();
?>
