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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;




if (!empty($this->itemhistory)) {
	//echo '<table class="ph-order-status-edit">';
	echo '<div class="row ph-order-status-edit-header">';
	echo '<div class="span4 col-sm-4 col-md-4">'.Text::_('COM_PHOCACART_DATE').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_STATUS').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_USER').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_USER_NOTIFIED').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_COMMENT').'</div>';
	echo '</div>';
	foreach($this->itemhistory as $k => $v) {
		echo '<div class="row ph-order-status-edit-item">';
		echo '<div class="span4 col-sm-4 col-md-4">'.HTMLHelper::date($v->date, Text::_('DATE_FORMAT_LC5')).'</div>';
		echo '<div class="span2 col-sm-2 col-md-2">'.Text::_($v->statustitle).'</div>';
		$userO = $v->user_name;
		if (isset($v->user_username)) {
			$userO .= ' <small>('.$v->user_username.')</small>';
		}
		echo '<div class="span2 col-sm-2 col-md-2">'.$userO.'</div>';

		$notifyText = Text::_('COM_PHOCACART_NO');
		if ($v->notify == -1) {
			$notifyText = Text::_('COM_PHOCACART_NO_ERROR');
		} else if ($v->notify > 0) {
			$notifyText = Text::_('COM_PHOCACART_YES');
		}

		echo '<div class="span2 col-sm-2 col-md-2">'.$notifyText.'</div>';
		$comment = '';
		if ($v->comment != '') {
			$comment = $v->comment;
		}
		echo '<div class="span2 col-sm-2 col-md-2">'.$comment.'</div>';
		echo '</div>';
	}
	//echo '</table>';
}

echo '<p>&nbsp;</p>';

echo '<p>&nbsp;</p>';

$link	= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstatus&tmpl=component&id='.(int)$this->id);

echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditstatus.editstatus">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';

//echo '<table class="ph-edit-status-box">';
echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_COMMENT').'</div>';
echo '<div class="span3 col-sm-3 col-md-3"><textarea name="jform[comment_history]" class="form-control"></textarea></div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_NOTIFY_CUSTOMER').'</div>';
$checked = '';
if (isset($this->item['email_customer']) && (int)$this->item['email_customer'] > 0) {
	$checked = 'checked="checked"';
}
echo '<div class="span3 col-sm-3 col-md-3"><input type="checkbox" name="jform[notify_customer]" '.$checked.' /></div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_NOTIFY_OTHERS').'</div>';
$checked = '';
if (isset($this->item['email_others']) && $this->item['email_others'] != '') {
	$checked = 'checked="checked"';
}
echo '<div class="span3 col-sm-3 col-md-3"><input type="checkbox" name="jform[notify_others]" '.$checked.' /></div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="span12 col-sm-12 col-md-12"><div class="alert alert-warning" style="display:none" id="phWarningNotify">'.Text::_('COM_PHOCACART_WARNING_ORDER_STATUS_CHANGED_CHECK_NOTIFY_CUSTOMER_NOTIFY_OTHERS_FIELDS_AGAIN').'</div></div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_EMAIL_SEND_ATTACHMENT_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getEmailSendSelectBox($this->item['email_send']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_EMAIL_SEND_FORMAT_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getEmailSendFormatSelectBox($this->item['email_send_format']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_STATUS').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.$this->item['select'].'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_STOCK_MOVEMENTS_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getStockMovementsSelectBox($this->item['stock_movements']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_USER_GROUP_CHANGE_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getChangeUserGroupSelectBox($this->item['change_user_group']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_REWARD_POINTS_CHANGE_NEEDED_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getChangeChangePointsNeededSelectBox($this->item['change_points_needed']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_REWARD_POINTS_CHANGE_RECEIVED_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getChangePointsReceivedSelectBox($this->item['change_points_received']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';
/*
echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_FIELD_REWARD_EMAIL_SEND_GIFT_VOUCHERS_LABEL').'</div>';
echo '<div class="span3 col-sm-3 col-md-3">'.PhocacartOrderStatus::getEmailSendGiftSelectBox($this->item['email_gift']).'</div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';
*/
echo '<div class="row">';
echo '<div class="span3 col-sm-3 col-md-3 ph-tax-edit-button"><button class="btn btn-success btn-sm ph-btn"><span class="icon-edit"></span> '.Text::_('COM_PHOCACART_EDIT_STATUS').'</button></div>';
echo '<div class="span3 col-sm-3 col-md-3"></div>';
echo '<div class="span6 col-sm-6 col-md-6"></div>';
echo '</div>';
//echo '</table>';

echo '<div class="ph-cb">&nbsp;</div>';

echo HTMLHelper::_('form.token');
echo '</form>';

echo '<p>&nbsp;</p>';

echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditstatus.emptyhistory">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-primary btn-sm ph-btn"><span class="icon-delete"></span> '.Text::_('COM_PHOCACART_EMPTY_ORDER_HISTORY').'</button>';
echo '</div>';
echo HTMLHelper::_('form.token');
echo '</form>';



?>
