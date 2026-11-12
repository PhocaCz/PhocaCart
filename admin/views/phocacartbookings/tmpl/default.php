<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart/booking/booking.php';
$isBookingEnabled = PluginHelper::isEnabled('system', 'phocacartbooking');

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

if ($isBookingEnabled) {
?>
<form action="<?php echo Route::_('index.php?option=com_phocacart&view=phocacartbookings'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped" id="bookingList">
                        <thead>
                            <tr>
                                <th scope="col" style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_USER', 'u.name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_PRODUCT', 'p.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_FIELD_BOOKING_MODE', 'a.booking_mode', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_STATUS', 'a.status', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKING_DATE_START', 'a.booking_date_start', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKING_DATE_END', 'a.booking_date_end', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_FIELD_QUANTITY_LABEL', 'a.quantity', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo Route::_('index.php?option=com_phocacart&task=phocacartbooking.edit&id=' . (int)$item->id); ?>">
                                            <?php echo $this->escape($item->user_name ?: Text::_('COM_PHOCACART_GUEST')); ?>
                                        </a>
                                        <?php if (!empty($item->user_email)) : ?>
                                            <div class="small text-muted"><?php echo $this->escape($item->user_email); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($item->order_id)) : ?>
                                            <div class="small">
                                                <a href="<?php echo Route::_('index.php?option=com_phocacart&task=phocacartorder.edit&id=' . (int)$item->order_id); ?>">
                                                    <?php echo Text::_('COM_PHOCACART_ORDER') . ': ' . $this->escape(PhocacartOrder::getOrderNumber($item->order_id, $item->order_date, $item->order_number)); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $this->escape($item->product_title); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)$item->booking_mode === \PhocacartBooking::MODE_DATE_RANGE) : ?>
                                            <span class="badge bg-primary"><?php echo Text::_('COM_PHOCACART_BOOKING_MODE_DATE_RANGE'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-info text-dark"><?php echo Text::_('COM_PHOCACART_BOOKING_MODE_TIME_SLOT'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'badge bg-secondary';
                                        switch ((int)$item->status) {
                                            case \PhocacartBooking::STATUS_CONFIRMED:
                                                $badgeClass = 'badge bg-success';
                                                break;
                                            case \PhocacartBooking::STATUS_PENDING:
                                                $badgeClass = 'badge bg-info';
                                                break;
                                            case \PhocacartBooking::STATUS_CANCELLED:
                                                $badgeClass = 'badge bg-warning text-dark';
                                                break;
                                            case \PhocacartBooking::STATUS_COMPLETED:
                                                $badgeClass = 'badge bg-success';
                                                break;
                                            case \PhocacartBooking::STATUS_NO_SHOW:
                                                $badgeClass = 'badge bg-dark';
                                                break;
                                            case \PhocacartBooking::STATUS_PENDING_PAYMENT:
                                                $badgeClass = 'badge bg-warning';
                                                break;
                                            case \PhocacartBooking::STATUS_REFUNDED:
                                                $badgeClass = 'badge bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $badgeClass; ?>"><?php echo Text::_(\PhocacartBooking::getStatus($item->status)); ?></span>
                                    </td>
                                    <td>
                                        <?php echo HTMLHelper::_('date', $item->booking_date_start, Text::_('DATE_FORMAT_LC4')); ?>
                                        <?php if (!empty($item->booking_time_start) && (int)$item->booking_mode === \PhocacartBooking::MODE_TIME_SLOT) : ?>
                                            <div class="small text-muted"><?php echo substr($item->booking_time_start, 0, 5); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo HTMLHelper::_('date', $item->booking_date_end, Text::_('DATE_FORMAT_LC4')); ?>
                                        <?php if (!empty($item->booking_time_end) && (int)$item->booking_mode === \PhocacartBooking::MODE_TIME_SLOT) : ?>
                                            <div class="small text-muted"><?php echo substr($item->booking_time_end, 0, 5); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (int)$item->quantity; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (int)$item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<?php
$calJson = !empty($this->calendarBookings) ? json_encode($this->calendarBookings) : '[]';
?>
<div class="row mt-4 mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm ph-admin-calendar-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <span class="icon-calendar" aria-hidden="true"></span> <?php echo Text::_('COM_PHOCACART_BOOKING_CALENDAR'); ?>
                </h4>
                <div class="ph-admin-calendar-legend d-flex gap-3 align-items-center small">
                    <span class="badge bg-success"><?php echo Text::_('COM_PHOCACART_BOOKING_STATUS_CONFIRMED'); ?></span>
                    <span class="badge bg-info text-dark"><?php echo Text::_('COM_PHOCACART_BOOKING_STATUS_PENDING'); ?></span>
                    <span class="badge bg-primary"><?php echo Text::_('COM_PHOCACART_BOOKING_STATUS_COMPLETED'); ?></span>
                    <span class="badge ph-badge-turnover"><?php echo Text::_('COM_PHOCACART_BOOKING_TURNOVER_DAY'); ?></span>
                </div>
            </div>
            <div class="card-body">
                <div id="phAdminBookingCalendar"
                     class="ph-admin-calendar-wrapper"
                     data-bookings="<?php echo htmlspecialchars($calJson, ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-departure="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_DEPARTURE'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-arrival="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_ARRIVAL'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-today="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_TODAY'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-prev="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_PREV_MONTH'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-next="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_NEXT_MONTH'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-booking-details="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_DETAILS'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-order="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_ORDER'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-customer="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_CUSTOMER'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-status="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_STATUS'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-dates="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_DATES'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-time="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_TIME'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-quantity="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_QUANTITY'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-notes="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_FIELD_NOTES_LABEL'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-edit="<?php echo htmlspecialchars(Text::_('JACTION_EDIT'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-close="<?php echo htmlspecialchars(Text::_('JCLOSE'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-timeslot="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_SLOT'), ENT_QUOTES, 'UTF-8'); ?>"
                     data-text-no-time="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_MODE_DATE_RANGE'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Booking Detail Modal -->
                <div class="modal fade" id="phBookingDetailModal" tabindex="-1" aria-labelledby="phBookingDetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="phBookingDetailModalLabel"><?php echo Text::_('COM_PHOCACART_BOOKING_DETAILS'); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
                            </div>
                            <div class="modal-body" id="phBookingDetailModalBody">
                            </div>
                            <div class="modal-footer">
                                <a href="#" class="btn btn-primary" id="phBookingDetailEditBtn" target="_blank"><?php echo Text::_('JACTION_EDIT'); ?></a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('JCLOSE'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
} else {
    echo '<div class="alert alert-warning">' . Text::_('COM_PHOCACART_BOOKING_PLUGIN_REQUIRED') . '</div>';
}
?>
