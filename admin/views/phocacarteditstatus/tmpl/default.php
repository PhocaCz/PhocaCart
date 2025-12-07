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

$link	= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstatus&tmpl=component&id='.(int)$this->id);
PhocacartRenderAdminjs::renderOverlayOnSubmit('phEditStatus');

if (!empty($this->itemhistory)) {
?>
    <h3><?php echo Text::_('COM_PHOCACART_ORDER_STATUS_HISTORY'); ?></h3>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover">
            <thead>
                <tr class="ph-order-status-edit-header">
                    <th><?php echo Text::_('COM_PHOCACART_DATE'); ?></th>
                    <th><?php echo Text::_('COM_PHOCACART_STATUS'); ?></th>
                    <th><?php echo Text::_('COM_PHOCACART_USER'); ?></th>
                    <th><?php echo Text::_('COM_PHOCACART_USER_NOTIFIED'); ?></th>
                    <th><?php echo Text::_('COM_PHOCACART_COMMENT'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($this->itemhistory as $history) { ?>

                <tr class="ph-order-status-edit-item">
                    <td><?php echo HTMLHelper::date($history->date, Text::_('DATE_FORMAT_LC5')); ?></td>
                    <td><?php echo PhocacartUtilsSettings::getOrderStatusBadge($history->statustitle, $history->status_params); ?></td>
                    <td><?php echo $history->user_name . ($history->user_username ? ' <small>(' . $history->user_username . ')</small>' : ''); ?></td>
                    <td><?php echo Text::_([-1 => 'COM_PHOCACART_NO_ERROR', 'COM_PHOCACART_NO', 'COM_PHOCACART_YES'][$history->notify]); ?></td>
                    <td><?php echo $history->comment; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <form action="<?php echo $link; ?>" method="post" class="text-end">
        <input type="hidden" name="jform[id]" value="<?php echo (int)$this->id; ?>">
        <input type="hidden" name="task" value="phocacarteditstatus.emptyhistory">
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="option" value="com_phocacart" />
        <button class="btn btn-danger btn-sm ph-btn"><span class="icon-delete"></span> <?php echo Text::_('COM_PHOCACART_EMPTY_ORDER_HISTORY'); ?></button>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
<?php
}
?>
<form action="<?php echo $link; ?>" method="post" id="phEditStatus">
  <div class="row mt-4">
    <div class="col-md-6">
        <?php echo $this->form->renderFieldset('status'); ?>

      <button class="btn btn-success ph-btn"><span class="icon-edit"></span> <?php echo Text::_('COM_PHOCACART_EDIT_STATUS'); ?></button>
    </div>
    <div class="col-md-6">
        <?php echo $this->form->renderFieldset('params'); ?>
    </div>
  </div>

  <input type="hidden" name="task" value="phocacarteditstatus.editstatus" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name="option" value="com_phocacart" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
