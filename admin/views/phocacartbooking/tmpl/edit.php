<?php
/**
 * @package    phocacart
 * @subpackage Views
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart/booking/booking.php';
$isBookingEnabled = PluginHelper::isEnabled('system', 'phocacartbooking');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

if ($isBookingEnabled) {
?>
<form action="<?php echo Route::_('index.php?option=com_phocacart&view=phocacartbooking&layout=edit&id=' . (int)$this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="row">
        <div class="col-lg-12">
            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_PHOCACART_DETAILS')); ?>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->form->renderFieldset('details'); ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php if ((int)$this->item->id > 0) : ?>

                <?php if (!empty($this->orderInfo)) : ?>
                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'orderinfo', Text::_('COM_PHOCACART_ORDER')); ?>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width:20%"><?php echo Text::_('COM_PHOCACART_ORDER'); ?></th>
                                    <td>
                                        <a href="<?php echo Route::_('index.php?option=com_phocacart&task=phocacartorder.edit&id=' . (int)$this->orderInfo->order_id); ?>" target="_blank">
                                            #<?php echo (int)$this->orderInfo->order_id; ?>
                                        </a> (<?php echo Text::_('COM_PHOCACART_ORDER_NR') . ': ' .PhocacartOrder::getOrderNumber((int)$this->orderInfo->order_id)?>)
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo Text::_('COM_PHOCACART_PRODUCT'); ?></th>
                                    <td><?php echo $this->escape($this->orderInfo->title); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo Text::_('COM_PHOCACART_FIELD_BOOKING_MODE'); ?></th>
                                    <td>
                                        <?php echo (int)$this->orderInfo->booking_mode === 2 ? Text::_('COM_PHOCACART_BOOKING_MODE_DATE_RANGE') : Text::_('COM_PHOCACART_BOOKING_MODE_TIME_SLOT'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo Text::_('COM_PHOCACART_FIELD_QUANTITY_LABEL'); ?></th>
                                    <td><?php echo (int)$this->orderInfo->booking_quantity; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php endif; ?>

                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'history', Text::_('COM_PHOCACART_BOOKING_HISTORY')); ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4><?php echo Text::_('COM_PHOCACART_BOOKING_STATUS_HISTORY'); ?></h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo Text::_('COM_PHOCACART_DATE'); ?></th>
                                    <th><?php echo Text::_('COM_PHOCACART_STATUS'); ?></th>
                                    <th><?php echo Text::_('COM_PHOCACART_EVENT'); ?></th>
                                    <th><?php echo Text::_('COM_PHOCACART_TRIGGERED_BY'); ?></th>
                                    <th><?php echo Text::_('COM_PHOCACART_COMMENT'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($this->history)) : ?>
                                    <?php foreach ($this->history as $h) : ?>
                                        <tr>
                                            <td><?php echo HTMLHelper::_('date', $h->event_date, Text::_('DATE_FORMAT_LC2')); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo Text::_(\PhocacartBooking::getStatus($h->status_to)); ?></span>
                                            </td>
                                            <td><?php echo $this->escape($h->event_type); ?></td>
                                            <td><?php echo $this->escape($h->triggered_by); ?></td>
                                            <td><?php echo $this->escape($h->comment); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php endif; ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
} else {
    echo '<div class="alert alert-warning">' . Text::_('COM_PHOCACART_BOOKING_PLUGIN_REQUIRED') . '</div>';
}
?>
