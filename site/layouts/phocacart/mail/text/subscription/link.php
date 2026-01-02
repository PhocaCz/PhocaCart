<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var array $displayData */
$product = $displayData['product'];
$eventType = $displayData['eventType'];

$showRenewButton = in_array($eventType, ['expiring_soon', 'expired', 'canceled']);
$showAccountButton = in_array($eventType, ['activated', 'renewed', 'status_changed']);

$siteUrl = Uri::root();

echo str_repeat('-', 50) . "\n\n";

if ($showRenewButton) {
    echo Text::_('COM_PHOCACART_RENEW_SUBSCRIPTION') . ":\n";
    echo $siteUrl . "index.php?option=com_phocacart&view=item&id=" . (int)$product->id . "\n\n";
    echo Text::_('COM_PHOCACART_SUBSCRIPTION_RENEW_HINT') . "\n\n";
} elseif ($showAccountButton) {
    echo Text::_('COM_PHOCACART_VIEW_MY_ACCOUNT') . ":\n";
    echo $siteUrl . "index.php?option=com_phocacart&view=account\n\n";
}

echo Text::_('COM_PHOCACART_SUBSCRIPTION_EMAIL_FOOTER') . "\n";
