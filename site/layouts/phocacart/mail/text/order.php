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

/** @var \Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

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
    'rewardpoints' => '',
];


switch ($documentType) {
    case EmailDocumentType::Order:
        if (!$displayData['order']->order_number) {
            echo Text::_('COM_PHOCACART_ORDER_DOES_NOT_EXIST') . "\n\n";
            return;
        }
        break;
    case EmailDocumentType::Invoice:
        if (!$displayData['order']->invoice_number) {
            echo Text::_('COM_PHOCACART_INVOICE_NOT_YET_ISSUED') . "\n\n";
            return;
        }
        break;
    case EmailDocumentType::DeliveryNote:
        if (!$displayData['order']->order_number) {
            echo Text::_('COM_PHOCACART_DELIVERY_NOTE_NOT_YET_ISSUED') . "\n\n";
            return;
        }
        break;
    case EmailDocumentType::POSReceipt:
        if (!$displayData['order']->receipt_number) {
            echo Text::_('COM_PHOCACART_RECEIPT_NOT_YET_ISSUED') . "\n\n";
            return;
        }
        break;
}


/* Blocks to use in MailTemplate */
$displayData['blocks'] = [
  'header' => $this->sublayout('header', $displayData),
  'info' => $this->sublayout('info', $displayData),
  'billing' => $this->sublayout('billing', $displayData),
  'shipping' => $this->sublayout('shipping', $displayData),
  'products' => $this->sublayout('products', $displayData),
  'totals' => $this->sublayout('totals', $displayData),
  'link' => $this->sublayout('link', $displayData),
  'downloads' => $this->sublayout('downloads', $displayData),
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

<?= $displayData['blocks']['header']; ?>

<?= $displayData['blocks']['info']; ?>

<?= $displayData['blocks']['link']; ?>

<?= $displayData['blocks']['billing']; ?>

<?= $displayData['blocks']['shipping']; ?>

<?= MailHelper::renderArticle($topArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $displayData['blocks']['products']; ?>

<?= $displayData['blocks']['totals']; ?>

<?= MailHelper::renderArticle($middleArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $displayData['blocks']['downloads']; ?>

<?= $displayData['blocks']['rewardpoints']; ?>

<?= MailHelper::renderArticle($bottomArticle, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>
