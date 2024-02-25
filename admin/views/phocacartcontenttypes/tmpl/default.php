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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$r 			= $this->r;
$user		= Factory::getApplication()->getIdentity();
$userId		= $user->id;
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}

echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');

echo $r->startMainContainer();

echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);

echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title">' . HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-context">' . HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_CONTEXT', 'a.context', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_PUBLISHED', 'a.published', $listDirn, $listOrder ) . '</th>' . "\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_ID', 'a.id', $listDirn, $listOrder ) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
        $linkEdit       = Route::_('index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=' . $item->id);
		$canEdit		= $user->authorise('core.edit', $this->t['o']);
		$canCheckin     = $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
		$canChange      = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;

		echo $r->startTr($i, 0);
        echo $r->firstColumn($i, $item->id);
        echo $r->secondColumn(null, null, $canChange, $saveOrder, null, $item->ordering);

		$checkO = '';
		if ($item->checked_out) {
			$checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
		}
		if ($canEdit) {
			$checkO .= '<a href="'. Route::_($linkEdit).'">'. $this->escape(Text::_($item->title)).'</a>';
		} else {
			$checkO .= $this->escape(Text::_($item->title));
		}
		echo $r->td($checkO);

		echo $r->td(Text::_('COM_PHOCACART_CONTEXT_' . $item->context));

		echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange));


		echo $r->td($item->id);

		echo $r->endTr();
	}
}

echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 6);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, []);
echo $r->endMainContainer();
echo $r->endForm();

