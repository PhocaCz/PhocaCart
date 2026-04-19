<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * Customer-facing HTML block for the withdrawal confirmation e-mail.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

/** @var array $displayData */
$styles = $displayData['styles'];
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="<?= $styles['reset'] ?>">
    <tr>
        <td style="<?= $styles['fs-normal'] ?> padding: 20px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="<?= $styles['fs-xlarge'] ?> padding-bottom: 12px;">
                        <strong>Withdrawal confirmation: <?= $displayData['mailData']['ordernumber'] ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <p><?= Text::_('COM_PHOCACART_CANCELLATION_CUSTOMER_NOTICE_TEXT') ?></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="<?= $styles['table-cell'] ?> <?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_NUMBER') ?>:</td>
                                <td style="<?= $styles['table-cell'] ?>"><?= $displayData['mailData']['ordernumber'] ?></td>
                            </tr>
                            <tr>
                                <td style="<?= $styles['table-cell'] ?> <?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_DATE') ?>:</td>
                                <td style="<?= $styles['table-cell'] ?>"><?= $displayData['mailData']['orderdate'] ?></td>
                            </tr>
                            <tr>
                                <td style="<?= $styles['table-cell'] ?> <?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_CANCELLATION_NAME') ?>:</td>
                                <td style="<?= $styles['table-cell'] ?>"><?= $displayData['mailData']['customer_name'] ?></td>
                            </tr>
                            <tr>
                                <td style="<?= $styles['table-cell'] ?> <?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_CANCELLATION_DATE_OF_WITHDRAWAL') ?>:</td>
                                <td style="<?= $styles['table-cell'] ?>"><?= $displayData['mailData']['withdrawal_date'] ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 20px;">
                        <a href="<?= $displayData['mailData']['site_url'] ?>" style="<?= $styles['button'] ?>"><?= $displayData['mailData']['site_name'] ?></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
