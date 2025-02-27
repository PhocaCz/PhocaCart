<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

/** @var array $displayData */
/** @var array $styles */
/** @var array $attachments */

$styles = &$displayData['styles'];
$attachments = &$displayData['attachments'];

foreach ($displayData['gifts'] as $gift) {
   if ($gift['gift_image']) {
       $attachments['gift-image-' . $gift['id']] = $gift['gift_image'];
   }
?>
<div style="<?= $styles['ph-gift-voucher-box'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-box'] ?? '') ?>">
    <?php if ($gift['gift_title']) { ?>
        <div style="<?= $styles['ph-gift-voucher-title-wrapper'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-title-wrapper'] ?? '') ?>">
          <div style="<?= $styles['ph-gift-voucher-title'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-title'] ?? '') ?>">
            <?= $gift['gift_title'] ?>
          </div>
          <?php if ($gift['gift_image']) { ?>
              <img src="cid:gift-image-<?= $gift['id'] ?>" style="<?= $styles['ph-gift-voucher-image'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-image'] ?? '') ?> alt="<?= $gift['gift_title'] ?>">
          <?php } ?>
        </div>
    <?php } ?>

    <table style="<?= $styles['ph-gift-voucher-body'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-body'] ?? '') ?>">
      <tbody>
        <tr>
        <td style="<?= $styles['ph-gift-voucher-col1'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-col1'] ?? '') ?>" valign="middle" align="center">
            <div style="<?= $styles['ph-gift-voucher-head'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-head'] ?? '') ?>">
                <div style="<?= $styles['ph-gift-voucher-head-top'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-head-top'] ?? '') ?>">
                    <?= Text::_('COM_PHOCACART_TXT_GIFT_VOUCHER_GIFT') ?>
                </div>

                <div style="<?= $styles['ph-gift-voucher-head-bottom'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-head-bottom'] ?? '') ?>">
                    <?= Text::_('COM_PHOCACART_TXT_GIFT_VOUCHER_VOUCHER') ?>
                </div>
            </div>
        </td>

        <td style="<?= $styles['ph-gift-voucher-col2'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-col2'] ?? '') ?>">
            <?php if ($gift['gift_description']) { ?>
                <div style="<?= $styles['ph-gift-voucher-description'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-description'] ?? '') ?>><?= $gift['gift_description'] ?></div>
            <?php } ?>

            <div style="<?= $styles['ph-gift-voucher-price'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-price'] ?? '') ?>><?= $gift['discount'] ?></div>

            <?php if ($gift['gift_sender_name']) { ?>
                <div style="<?= $styles['ph-gift-voucher-from'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-from'] ?? '') ?>><?= Text::_('COM_PHOCACART_FROM') . ': ' . $gift['gift_sender_name'] ?></div>
            <?php } ?>

            <?php if ($gift['gift_recipient_name']) { ?>
                <div style="<?= $styles['ph-gift-voucher-to'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-to'] ?? '') ?>><?= Text::_('COM_PHOCACART_TO') . ': ' . $gift['gift_recipient_name'] ?></div>
            <?php } ?>

            <?php if ($gift['gift_sender_message']) { ?>
                <div style="<?= $styles['ph-gift-voucher-message'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-message'] ?? '') ?>><?= $gift['gift_sender_message'] ?></div>
            <?php } ?>

            <?php if ($gift['code']) { ?>
                <div style="<?= $styles['ph-gift-voucher-code'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-code'] ?? '') ?>><?= $gift['code'] ?></div>
            <?php } ?>

            <?php if ($gift['valid_to']) { ?>
                <div style="<?= $styles['ph-gift-voucher-date-to'] . ($styles[$gift['gift_class_name']]['ph-gift-voucher-date-to'] ?? '') ?>><?= Text::_('COM_PHOCACART_VALID_TILL') . ': ' . $gift['valid_to'] ?></div>
            <?php } ?>
        </td>
        </tr>
      </tbody>
    </table>
</div>
<?php
}
