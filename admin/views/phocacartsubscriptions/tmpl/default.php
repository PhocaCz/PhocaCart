<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
require_once JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart/subscription/subscription.php';
$isProEnabled = PluginHelper::isEnabled('system', 'phocacartsubscription');

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

if ($isProEnabled) {
?><form action="<?php echo Route::_('index.php?option=com_phocacart&view=phocacartsubscriptions'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped" id="subscriptionList">
                        <thead>
                            <tr>
                                <th scope="col" style="width:1%" class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'User', 'u.name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'Product', 'p.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'Status', 'a.status', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'Start Date', 'a.start_date', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'End Date', 'a.end_date', $listDirn, $listOrder); ?>
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
                                        <a href="<?php echo Route::_('index.php?option=com_phocacart&task=phocacartsubscription.edit&id=' . (int) $item->id); ?>">
                                            <?php echo $this->escape($item->user_name); ?>
                                        </a>
                                        <div class="small"><?php echo $this->escape($item->user_email); ?></div>
                                    </td>
                                    <td>
                                        <?php echo $this->escape($item->product_title); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $class = 'badge bg-secondary';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_ACTIVE) $class = 'badge bg-success';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_FUTURE) $class = 'badge bg-info';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_EXPIRED) $class = 'badge bg-danger';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_ON_HOLD) $class = 'badge bg-warning';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_PENDING) $class = 'badge bg-warning text-dark';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_FAILED) $class = 'badge bg-danger';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_IN_TRIAL) $class = 'badge bg-info';
                                        if ((int)$item->status === \PhocacartSubscription::STATUS_CARD_EXPIRED) $class = 'badge bg-danger';
                                        ?>
                                        <span class="<?php echo $class; ?>"><?php echo Text::_(\PhocacartSubscription::getStatus($item->status)); ?></span>
                                    </td>
                                    <td>
                                        <?php echo HTMLHelper::_('date', $item->start_date, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td>
                                        <?php echo HTMLHelper::_('date', $item->end_date, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php echo $this->pagination->getListFooter(); ?>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
<?php
} else {
    echo '<div class="ph-pro-box">'.Text::_('COM_PHOCACART_ADVANCED_FEATURE_PRO'). '</div>';
}

