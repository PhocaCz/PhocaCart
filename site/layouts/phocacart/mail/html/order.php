<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Constants\EmailDocumentType;
use Phoca\PhocaCart\Mail\MailHelper;

/** @var Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

/* Styles are defined in styles sublayout */
$styles= [];
$displayData['styles'] = &$styles;

$displayData['blocks'] = [
    'styles' => '',
    'header' => '',
    'info' => '',
    'billing' => '',
    'shipping' => '',
    'products' => '',
    'totals' => '',
    'link' => '',
    'downloads' => '',
    'taxes' => '',
    'rewardpoints' => '',
];


switch ($documentType) {
    case EmailDocumentType::Order:
        if (!$displayData['order']->order_number) {
            echo '<div><strong>' . Text::_('COM_PHOCACART_ORDER_DOES_NOT_EXIST') . '</strong></div>';
            return;
        }
        break;
    case EmailDocumentType::Invoice:
        if (!$displayData['order']->invoice_number) {
            echo '<div><strong>' . Text::_('COM_PHOCACART_INVOICE_NOT_YET_ISSUED') . '</strong></div>';
            return;
        }
        break;
    case EmailDocumentType::DeliveryNote:
        if (!$displayData['order']->order_number) {
            echo '<div><strong>' . Text::_('COM_PHOCACART_DELIVERY_NOTE_NOT_YET_ISSUED') . '</strong></div>';
            return;
        }
        break;
    case EmailDocumentType::POSReceipt:
        if (!$displayData['order']->receipt_number) {
          echo '<div><strong>' . Text::_('COM_PHOCACART_RECEIPT_NOT_YET_ISSUED') . '</strong></div>';
          return;
        }
        break;
}



/* Blocks to use in MailTemplate */
$displayData['blocks'] = [
  'styles' => $this->sublayout('styles', $displayData),
  'header' => $this->sublayout('header', $displayData),
  'info' => $this->sublayout('info', $displayData),
  'billing' => $this->sublayout('billing', $displayData),
  'shipping' => $this->sublayout('shipping', $displayData),
  'products' => $this->sublayout('products', $displayData),
  'totals' => $this->sublayout('totals', $displayData),
  'link' => $this->sublayout('link', $displayData),
  'downloads' => $this->sublayout('downloads', $displayData),
  'taxes' => $this->sublayout('taxes', $displayData),
  'rewardpoints' => $this->sublayout('rewardpoints', $displayData),
];

$topArticle = null;
$middleArticle = null;
$bottomArticle = null;

switch ($documentType) {
    case EmailDocumentType::Order:
        $topArticle = (int)$params->get( 'order_global_top_desc', 0 );
        $middleArticle = (int)$params->get( 'order_global_middle_desc', 0 );
        $bottomArticle = (int)$params->get( 'order_global_bottom_desc', 0 );
        break;
    case EmailDocumentType::Invoice:
        if ($displayData['order']->invoice_spec_top_desc) {
            $topArticle = $displayData['order']->invoice_spec_top_desc;
        } else {
            $topArticle = (int) $params->get('invoice_global_top_desc', 0);
        }

        if ($displayData['order']->invoice_spec_middle_desc) {
            $middleArticle = $displayData['order']->invoice_spec_middle_desc;
        } else {
            $middleArticle = (int) $params->get('invoice_global_middle_desc', 0);
        }

        if ($displayData['order']->invoice_spec_bottom_desc) {
            $bottomArticle = $displayData['order']->invoice_spec_bottom_desc;
        } else {
            $bottomArticle = (int)$params->get( 'invoice_global_bottom_desc', 0 );
        }
        break;
    case EmailDocumentType::DeliveryNote:
        $topArticle = (int)$params->get( 'dn_global_top_desc', 0 );
        $middleArticle = (int)$params->get( 'dn_global_middle_desc', 0 );
        $bottomArticle = (int)$params->get( 'dn_global_bottom_desc', 0 );
        break;
}
?>

<?= $displayData['blocks']['styles']; ?>

<div style="<?= $styles['fs-normal'] . $styles['w100'] ?>">
  <table style="<?= $styles['w100'] ?>">
    <tbody>
      <tr>
        <td style="width: 50%;">
            <?= $displayData['blocks']['header']; ?>
        </td>
        <td style="width: 20px;"></td>
        <td style="width: 50%;">
            <?= $displayData['blocks']['info']; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <?= $displayData['blocks']['link']; ?>

  <table style="<?= $styles['w100'] ?>">
    <tbody>
      <tr>
        <td style="width: 50%;">
            <?= $displayData['blocks']['billing']; ?>
        </td>
        <td style="width: 20px;"></td>
        <td style="width: 50%;">
            <?= $displayData['blocks']['shipping']; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <?= MailHelper::renderArticle($topArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>

  <?= $displayData['blocks']['products']; ?>
  <?= $displayData['blocks']['totals']; ?>

  <?= MailHelper::renderArticle($middleArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>

  <?= $displayData['blocks']['downloads']; ?>

  <?= $displayData['blocks']['taxes']; ?>

  <?= $displayData['blocks']['rewardpoints']; ?>

  <?= MailHelper::renderArticle($bottomArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>
</div>
