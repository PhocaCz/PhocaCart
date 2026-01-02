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
use Phoca\PhocaCart\Event\Calculation\CalculationPrice;
use Joomla\CMS\Plugin\PluginHelper;
$isProEnabled = PluginHelper::isEnabled('system', 'phocacartsubscription');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

if ($isProEnabled) {

    ?><form action="<?php echo Route::_('index.php?option=com_phocacart&view=phocacartsubscription&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

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

            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'price', Text::_('COM_PHOCACART_PRICE'));

            if (!empty($this->orderInfo)) {

                $orderInfo = $this->orderInfo;
                $price = new \PhocacartPrice();
                $lang = Factory::getLanguage();
		        $lang->load('plg_system_phocacartsubscription', JPATH_ADMINISTRATOR);


                if ((isset($orderInfo->subscription_order_signup_fee) && $orderInfo->subscription_order_signup_fee> 0)
                || (isset($orderInfo->subscription_order_renewal_discount) && $orderInfo->subscription_order_renewal_discount> 0)) {
                    echo '<div class="ph-price-box">';
                    echo '<div class="ph-price-txt">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_BASE_PRICE').': </div>';
                    echo '<div class="ph-price">' . $price->getPriceFormat($orderInfo->subscription_order_base_price) . '</div>';
                    echo '</div>';
                }

                if (isset($orderInfo->subscription_order_signup_fee) && $orderInfo->subscription_order_signup_fee > 0) {
                    echo '<div class="ph-price-box">';
                    echo '<div class="ph-price-txt">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SIGNUP_FEE').': </div>';
                    echo '<div class="ph-price">'.$price->getPriceFormat($orderInfo->subscription_order_signup_fee).'</div>';
                    echo '</div>';
                }

                if (isset($orderInfo->subscription_order_renewal_discount) && $orderInfo->subscription_order_renewal_discount > 0) {
                    echo '<div class="ph-price-box">';
                    echo '<div class="ph-price-txt">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_RENEWAL_DISCOUNT').': </div>';
                    echo '<div class="ph-price">'.$price->getPriceFormat($orderInfo->subscription_order_renewal_discount , 1).'</div>';
                    echo '</div>';
                }

                echo '<div class="ph-price-box">';
                echo '<div class="ph-price-txt">'.Text::_('COM_PHOCACART_PRICE').': </div>';
                echo '<div class="ph-price">'.$price->getPriceFormat($orderInfo->subscription_order_total_price).'</div>';
                echo '</div>';

                if ($orderInfo->order_id > 0) {

                    $orderEditUrl = 'index.php?option=com_phocacart&task=phocacartorder.edit&id='.(int)$orderInfo->order_id ;
                    $orderTitle = PhocacartOrder::getOrderNumber((int)$orderInfo->order_id);
                    echo '<div class="ph-admin-additional-box ph-box-info">' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SUBSCRIPTION_PRICE_ANNOTATION') . ': <a href="'.$orderEditUrl.'" target="_blank">' . $orderTitle . '</a></div>';
                }

            }


            echo HTMLHelper::_('uitab.endTab');
            ?>


            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'history', Text::_('COM_PHOCACART_SUBSCRIPTION_HISTORY')); ?>
            <div class="row">
                <div class="col-md-12">
                     <h4><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_STATUS_HISTORY'); ?></h4>
                     <table class="table table-striped">
                         <thead>
                             <tr>
                                 <th><?php echo Text::_('COM_PHOCACART_DATE'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_STATUS'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_EVENT'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_TRIGGERED_BY'); ?></th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php if (!empty($this->history)) : ?>
                                 <?php foreach ($this->history as $h) : ?>
                                 <tr>
                                     <td><?php echo HTMLHelper::_('date', $h->event_date, Text::_('DATE_FORMAT_LC2')); ?></td>
                                     <td><?php echo Text::_($this->allStatuses[$h->status_to] ?? 'COM_PHOCACART_SUBSCRIPTION_STATUS_UNKNOWN'); ?></td>
                                     <td><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_EVENT_'.strtoupper($h->event_type)); ?></td>
                                     <td><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_TRIGGERED_BY_'.strtoupper($h->triggered_by)); ?></td>
                                 </tr>
                                 <?php endforeach; ?>
                             <?php else : ?>
                                 <tr><td colspan="4"><?php echo Text::_('COM_PHOCACART_NO_HISTORY_FOUND'); ?></td></tr>
                             <?php endif; ?>
                         </tbody>
                     </table>

                     <h4 class="mt-4"><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_ACL_LOG'); ?></h4>
                     <table class="table table-striped">
                         <thead>
                             <tr>
                                 <th><?php echo Text::_('COM_PHOCACART_DATE'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_GROUP'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_ACTION'); ?></th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php if (!empty($this->acl)) : ?>
                                 <?php foreach ($this->acl as $a) : ?>
                                 <tr>
                                     <td><?php echo HTMLHelper::_('date', $a->applied_date, Text::_('DATE_FORMAT_LC2')); ?></td>
                                     <td><?php echo $a->group_title; ?> (<?php echo $a->group_id; ?>)</td>
                                     <td><?php echo Text::_($a->action); ?></td>
                                 </tr>
                                 <?php endforeach; ?>
                             <?php else : ?>
                                 <tr><td colspan="3"><?php echo Text::_('COM_PHOCACART_NO_ACL_LOG_FOUND'); ?></td></tr>
                             <?php endif; ?>
                         </tbody>
                     </table>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php endif; ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'statusinfo', Text::_('COM_PHOCACART_SUBSCRIPTION_STATUS_INFO')); ?>
            <div class="row">
                <div class="col-md-12">
                     <h4><?php echo Text::_('COM_PHOCACART_STATUS_LEGEND'); ?></h4>
                     <table class="table table-striped mt-3">
                         <thead>
                             <tr>
                                 <th style="width: 20%;"><?php echo Text::_('COM_PHOCACART_STATUS'); ?></th>
                                 <th style="width: 20%;"><?php echo Text::_('COM_PHOCACART_BEHAVIOR'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_DESCRIPTION'); ?></th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($this->allStatuses as $id => $langKey) : ?>
                                 <?php
                                    $props = \PhocacartSubscription::getStatusProperties($id);
                                    $behavior = $props['behavior'];

                                    if ($behavior == \PhocacartSubscription::BEHAVIOR_ACTIVE) {
                                        $bType = 'ACTIVE';
                                        $badge = 'bg-success';
                                    } elseif ($behavior == \PhocacartSubscription::BEHAVIOR_INACTIVE) {
                                        $bType = 'INACTIVE';
                                        $badge = 'bg-danger';
                                    } else {
                                        $bType = 'NEUTRAL';
                                        $badge = 'bg-secondary';
                                    }
                                 ?>
                                 <tr>
                                     <td><strong><?php echo Text::_($langKey); ?></strong></td>
                                     <td><span class="badge <?php echo $badge; ?>"><?php echo Text::_('COM_PHOCACART_BEHAVIOR_' . $bType); ?></span></td>
                                     <td><small><?php echo Text::_('COM_PHOCACART_BEHAVIOR_' . $bType . '_DESC'); ?></small></td>
                                 </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                     <h4><?php echo Text::_('COM_PHOCACART_EVENT_EXPLANATION'); ?></h4>
                     <table class="table table-striped mt-3">
                         <thead>
                             <tr>
                                 <th style="width: 20%;"><?php echo Text::_('COM_PHOCACART_EVENT'); ?></th>
                                 <th><?php echo Text::_('COM_PHOCACART_DESCRIPTION'); ?></th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php
                                $events = \PhocacartSubscription::getEvents();
                                foreach ($events as $event) :?>
                                 <tr>
                                     <td><strong><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_EVENT_' . strtoupper($event)); ?></strong></td>
                                     <td><small><?php echo Text::_('COM_PHOCACART_SUBSCRIPTION_EVENT_' . strtoupper($event) . '_DESC'); ?></small></td>
                                 </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
} else {
    echo '<div class="ph-pro-box">'.Text::_('COM_PHOCACART_ADVANCED_FEATURE_PRO'). '</div>';
}
