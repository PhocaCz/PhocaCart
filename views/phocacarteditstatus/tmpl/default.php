<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
if (!empty($this->itemhistory)) {
	echo '<table class="ph-order-status-edit">';
	echo '<tr class="ph-order-status-edit-header">';
	echo '<th align="center">'.JText::_('COM_PHOCACART_DATE').'<th>';
	echo '<th align="center">'.JText::_('COM_PHOCACART_STATUS').'<th>';
	echo '<th align="center">'.JText::_('COM_PHOCACART_USER').'<th>';
	echo '<th align="center">'.JText::_('COM_PHOCACART_USER_NOTIFIED').'<th>';
	echo '<th align="center">'.JText::_('COM_PHOCACART_COMMENT').'<th>';
	echo '</tr>';
	foreach($this->itemhistory as $k => $v) {
		echo '<tr class="ph-order-status-edit-item">';
		echo '<td align="center">'.JHtml::date($v->date, 'd. m. Y. h:s').'<td>';
		echo '<td align="center">'.JText::_($v->statustitle).'<td>';
		$userO = $v->user_name;
		if (isset($v->user_username)) {
			$userO .= ' <small>('.$v->user_username.')</small>';
		}
		echo '<td align="center">'.$userO.'<td>';
		$notifyText = JText::_('COM_PHOCACART_NO');
		if ($v->notify > 0) {
			$notifyText = JText::_('COM_PHOCACART_YES');
		}
		echo '<td align="center">'.$notifyText.'<td>';
		$comment = '';
		if ($v->comment != '') {
			$comment = $v->comment;
		}
		echo '<td align="center">'.$comment.'<td>';
		echo '</tr>';
	}
	echo '</table>';
}

echo '<p>&nbsp;</p>';

echo '<p>&nbsp;</p>';

$link	= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstatus&tmpl=component&id='.(int)$this->id);


echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditstatus.editstatus">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';

echo '<table class="ph-edit-status-box">';
echo '<tr><td>'.JText::_('COM_PHOCACART_COMMENT').'</td>';
echo '<td><textarea name="jform[comment]"></textarea></td></tr>';

echo '<tr><td>'.JText::_('COM_PHOCACART_NOTIFY_CUSTOMER').'</td>';
$checked = '';
if (isset($this->item['email_customer']) && (int)$this->item['email_customer'] > 0) {
	$checked = 'checked="checked"';
}
echo '<td><input type="checkbox" name="jform[notify_customer]" '.$checked.' /></td></tr>';

echo '<tr><td>'.JText::_('COM_PHOCACART_NOTIFY_OTHERS').'</td>';
$checked = '';
if (isset($this->item['email_others']) && $this->item['email_others'] != '') {
	$checked = 'checked="checked"';
}
echo '<td><input type="checkbox" name="jform[notify_others]" '.$checked.' /></td></tr>';


echo '<tr><td>'.JText::_('COM_PHOCACART_FIELD_EMAIL_SEND_LABEL').'</td>';
echo '<td>'.PhocaCartOrderStatus::getEmailSendSelectBox($this->item['email_send']).'</tr>';


echo '<tr><td>'.JText::_('COM_PHOCACART_STATUS').'</td>';
echo '<td>'.$this->item['select'].'</td></tr>';

echo '<tr><td>'.JText::_('COM_PHOCACART_FIELD_STOCK_MOVEMENTS_LABEL').'</td>';
echo '<td>'.PhocaCartOrderStatus::getStockMovementsSelectBox($this->item['stock_movements']).'</tr>';


echo '<tr><td></td>';
echo '<td>';
echo '<button class="btn btn-success btn-sm ph-btn" role="button"><span class="icon-edit"></span> '.JText::_('COM_PHOCACART_EDIT_STATUS').'</button>';
echo '</td></tr>';
echo '</table>';

echo '<div class="ph-cb">&nbsp;</div>';

echo JHtml::_('form.token');
echo '</form>';

echo '<p>&nbsp;</p>';

echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditstatus.emptyhistory">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="icon-delete"></span> '.JText::_('COM_PHOCACART_EMPTY_ORDER_HISTORY').'</button>';
echo '</div>';
echo JHtml::_('form.token');
echo '</form>';


	
?>
