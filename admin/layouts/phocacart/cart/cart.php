<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var array $displayData */
/** @var Registry $params */
$params = $displayData['params'];

$price	= new PhocacartPrice();
$app 	= Factory::getApplication();

// Component parameters params
// Module parameters paramsmodule
$p = array();
$p['tax_calculation']					= $params->get('tax_calculation', 0);
$p['display_zero_tax']					= $params->get('display_zero_tax', 0);
$p['stock_checkout']					= $params->get('stock_checkout', 0);
$p['stock_checking']					= $params->get('stock_checking', 0);
$p['display_discount_product']			= $params->get('display_discount_product', 1);
$p['zero_shipping_price_calculation']	= $params->get('zero_shipping_price_calculation', 0);
$p['zero_payment_price_calculation']	= $params->get('zero_payment_price_calculation', 0);
$p['display_webp_images']				= $params->get('display_webp_images', 0);
$p['display_zero_total']			    = $params->get('display_zero_total', 0);

if (!empty($displayData['fullitems'])) {
?>
    <div class="table-responsive">
	<table class="table table-sm table-striped table-hover w-100">
        <caption class="caption-top"><?= $displayData['countitems'] . ' ' . Text::_('COM_PHOCACART_ITEM_S') ?></caption>
        <thead>
            <tr>
                <th><?= Text::_('COM_PHOCACART_QUANTITY') ?></th>
                <th><?= Text::_('COM_PHOCACART_ITEM') ?></th>
                <th class="text-end"><?= Text::_('COM_PHOCACART_PRICE') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($displayData['fullitems'][1] as $k => $item) { ?>
        <tr>
            <td><?= $item['quantity'] ?></td>
            <td>
                <?= $item['title'] ?>
                <?php if (!empty($item['attributes'])) { ?>
                <ul class="list-unstyled fs-6 text-secondary mb-0">
                    <?php foreach($item['attributes'] as $v2) { ?>
                        <?php foreach($v2 as $v3) { ?>
                            <li>
                                <?= $v3['atitle'] . ' '.$v3['otitle'] . (($v3['ovalue'] ?? '') ? ': ' . htmlspecialchars(urldecode($v3['ovalue']), ENT_QUOTES, 'UTF-8') : '') ?>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
                <?php } ?>
            </td>
            <td class="text-end"><?= $price->getPriceFormat($item['netto'] ? (int)$item['quantity'] * $item['netto'] : (int)$item['quantity'] * $item['brutto']) ?></td>
        </tr>
        <?php } ?>
        </tbody>

        <tfoot>
        <?php // SUBTOTAL ?>
        <?php if ($displayData['total'][1]['netto']) { ?>
        <tr>
            <td colspan="2"><?= Text::_('COM_PHOCACART_SUBTOTAL') ?></td>
            <td class="text-end"><?= $price->getPriceFormat($displayData['total'][1]['netto']) ?></td>
        </tr>
        <?php } ?>

        <?php // REWARD DISCOUNT ?>
        <?php if ($displayData['total'][5]['dnetto']) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_REWARD_POINTS').$displayData['total'][5]['rewardproducttxtsuffix'] ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][5]['dnetto'], 1) ?></td>
            </tr>
        <?php } ?>

        <?php // PRODUCT DISCOUNT ?>
        <?php if ($displayData['total'][2]['dnetto']) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_PRODUCT_DISCOUNT') ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][2]['dnetto'], 1) ?></td>
            </tr>
        <?php } ?>

        <?php // CART DISCOUNT ?>
        <?php if ($displayData['total'][3]['dnetto']) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_CART_DISCOUNT').$displayData['total'][3]['discountcarttxtsuffix'] ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][3]['dnetto'], 1) ?></td>
            </tr>
        <?php } ?>

        <?php // COUPON ?>
        <?php if ($displayData['total'][4]['dnetto'] && $displayData['couponvalid']) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_CART_DISCOUNT').' '.(($displayData['coupontitle'] ?? null) ?: Text::_('COM_PHOCACART_COUPON')) . $displayData['total'][4]['couponcarttxtsuffix'] ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][4]['dnetto'], 1) ?></td>
            </tr>
        <?php } ?>

        <?php // TAX ?>
        <?php if ($displayData['total'][0]['tax']) { ?>
            <?php foreach($displayData['total'][0]['tax'] as $v3) { ?>
                <?php $v3['taxcalc'] = (int)$p['tax_calculation']; ?>
                <?php $displayPriceItems = PhocaCartPrice::displayPriceItems($v3, 'cart'); ?>
                <?php if ($displayPriceItems['tax'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $v3['title'] ?></td>
                        <td class="text-end"><?= $price->getPriceFormat($v3['tax']) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php // SHIPPING ?>
        <?php if (!empty($displayData['shippingcosts'])) { ?>
            <?php $sC = $displayData['shippingcosts']; ?>

            <?php if ($p['zero_shipping_price_calculation'] == -1 && $sC['zero'] == 1) { ?>
                <?php // Hide completely ?>
            <?php } else 	if ($p['zero_shipping_price_calculation'] == 0 && $sC['zero'] == 1) { ?>
                <?php // Display blank price field ?>
                <tr>
                    <td colspan="2"><?= $sC['title'] ?></td>
                    <td class="text-end">&nbsp;</td>
                </tr>
            <?php } else if ($p['zero_shipping_price_calculation'] == 2 && $sC['zero'] == 1) { ?>
                <?php // Display free text ?>
                <tr>
                    <td colspan="2"><?= $sC['title'] ?></td>
                    <td class="text-end"><?= Text::_('COM_PHOCACART_FREE') ?></td>
                </tr>
            <?php } else { ?>
                <?php if ($sC['title'] != '') {$sC['title'] = $sC['title']. ' - ';} ?>
                <?php $displayPriceItems = PhocaCartPrice::displayPriceItems($sC, 'checkoutshipping'); ?>

                <?php if (isset($sC['nettoformat']) && $sC['nettoformat'] != '' && isset($sC['nettotxt']) && $sC['nettotxt'] != '' && $displayPriceItems['netto'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $sC['title'] . $sC['nettotxt'] ?></td>
                        <td class="text-end"><?= $sC['nettoformat'] ?></td>
                    </tr>
                <?php } ?>

                <?php if (isset($sC['taxformat']) && $sC['taxformat'] != '' && isset($sC['taxtxt']) && $sC['taxtxt'] != '' && $displayPriceItems['tax'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $sC['title'] . $sC['taxtxt'] ?></td>
                        <td class="text-end"><?= $sC['taxformat'] ?></td>
                    </tr>
                <?php } ?>

                <?php if ((isset($sC['bruttoformat']) && $sC['bruttoformat'] != '' && isset($sC['bruttotxt']) && $sC['bruttotxt'] != '') || $sC['freeshipping'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $sC['title'].$sC['bruttotxt'] ?></td>
                        <td class="text-end"><?= $sC['bruttoformat'] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php // PAYMENT ?>
        <?php if (!empty($displayData['paymentcosts'])) { ?>
            <?php $pC = $displayData['paymentcosts']; ?>

            <?php if ($p['zero_payment_price_calculation'] == -1 && $pC['zero'] == 1) { ?>
                <?php // Hide completely ?>
            <?php } else if ($p['zero_payment_price_calculation'] == 0 && $pC['zero'] == 1) { ?>
                <?php // Display blank price field ?>
                <tr>
                    <td colspan="2"><?= $pC['title'] ?></td>
                    <td class="text-end">&nbsp;</td>
                </tr>
            <?php } else if ($p['zero_payment_price_calculation'] == 2 && $pC['zero'] == 1) { ?>
                <?php // Display free text ?>
                <tr>
                    <td colspan="2"><?= $pC['title'] ?></td>
                    <td class="text-end"><?= Text::_('COM_PHOCACART_FREE') ?></td>
                </tr>
            <?php } else { ?>
                <?php if ($pC['title'] != '') {$pC['title'] = $pC['title']. ' - ';} ?>
                <?php $displayPriceItems = PhocaCartPrice::displayPriceItems($pC, 'checkoutpayment'); ?>

                <?php if (isset($pC['nettoformat']) && $pC['nettoformat'] != '' && isset($pC['nettotxt']) && $pC['nettotxt'] != '' && $displayPriceItems['netto'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $pC['title'] . $pC['nettotxt'] ?></td>
                        <td class="text-end"><?= $pC['nettoformat'] ?></td>
                    </tr>
                <?php } ?>

                <?php if (isset($pC['taxformat']) && $pC['taxformat'] != '' && isset($pC['taxtxt']) && $pC['taxtxt'] != '' && $displayPriceItems['tax'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $pC['title'] . $pC['taxtxt'] ?></td>
                        <td class="text-end"><?= $pC['taxformat'] ?></td>
                    </tr>
                <?php } ?>

                <?php if ((isset($pC['bruttoformat']) && $pC['bruttoformat'] != '' && isset($pC['bruttotxt']) && $pC['bruttotxt'] != '') || $pC['freeshipping'] == 1) { ?>
                    <tr>
                        <td colspan="2"><?= $pC['title'].$pC['bruttotxt'] ?></td>
                        <td class="text-end"><?= $pC['bruttoformat'] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php // ROUNDING | ROUNDING CURRENCY ?>
        <?php if ($displayData['total'][0]['rounding_currency'] != 0) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_ROUNDING_CURRENCY') ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][0]['rounding_currency'], 0, 1) ?></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_ROUNDING') ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][0]['rounding']) ?></td>
            </tr>
        <?php } ?>

        <?php // BRUTTO (Because of rounding currency we need to display brutto in currency which is set) ?>
        <?php if ($displayData['total'][0]['brutto_currency'] !== 0) { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_TOTAL') ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][0]['brutto_currency'], 0, 1) ?></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="2"><?= Text::_('COM_PHOCACART_TOTAL') ?></td>
                <td class="text-end"><?= $price->getPriceFormat($displayData['total'][0]['brutto']) ?></td>
            </tr>
        <?php } ?>

        </tfoot>
	</table>
    </div>
<?php
} else {
?>
	<div class="alert alert-info"><?= Text::_('COM_PHOCACART_SHOPPING_CART_IS_EMPTY') ?></div>
<?php
}
