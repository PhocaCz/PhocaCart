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
$booking = $displayData['booking'];
$product = $displayData['product'];

$statusText = Text::_(\PhocacartBooking::getStatus((int)($booking->status ?? 0)));

echo Text::_('COM_PHOCACART_PRODUCT') . ": " . $product->title . "\n";

if (!empty($product->sku)) {
    echo Text::_('COM_PHOCACART_SKU') . ": " . $product->sku . "\n";
}

echo Text::_('COM_PHOCACART_BOOKING') . " #: #" . (int)$booking->id . "\n";
echo Text::_('COM_PHOCACART_STATUS') . ": " . $statusText . "\n\n";

