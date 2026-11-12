<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Mail\MailHelper;

/** @var array $displayData */
$accountUrl = MailHelper::link(PhocacartRoute::getAccountRoute());

echo str_repeat('-', 50) . "\n\n";
echo Text::_('COM_PHOCACART_MAIL_BOOKING_VIEW_BOOKING') . ":\n";
echo $accountUrl . "\n\n";
echo Text::_('COM_PHOCACART_MAIL_BOOKING_ACCOUNT_HINT') . "\n\n";
echo Text::_('COM_PHOCACART_BOOKING_EMAIL_FOOTER') . "\n";

