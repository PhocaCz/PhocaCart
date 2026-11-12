<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_phocacart&view=phocacartbookingslots'); ?>" method="post" name="adminForm" id="adminForm">
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
                    <table class="table table-striped" id="bookingSlotList">
                        <thead>
                            <tr>
                                <th scope="col" style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th scope="col" style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_PRODUCT', 'p.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKING_DATE', 'a.slot_date', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKING_TIME_START', 'a.slot_time_start', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKING_TIME_END', 'a.slot_time_end', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_FIELD_BOOKING_CAPACITY', 'a.capacity', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PHOCACART_BOOKED_COUNT', 'a.booked', $listDirn, $listOrder); ?>
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
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'phocacartbookingslots.', true, 'cb'); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo Route::_('index.php?option=com_phocacart&task=phocacartbookingslot.edit&id=' . (int)$item->id); ?>">
                                            <?php echo $this->escape($item->product_title ?: Text::_('COM_PHOCACART_SELECT_PRODUCT')); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo HTMLHelper::_('date', $item->slot_date, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td>
                                        <?php echo substr($item->slot_time_start, 0, 5); ?>
                                    </td>
                                    <td>
                                        <?php echo substr($item->slot_time_end, 0, 5); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (int)$item->capacity; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo ((int)$item->booked >= (int)$item->capacity) ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo (int)$item->booked; ?> / <?php echo (int)$item->capacity; ?>
                                        </span>
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
