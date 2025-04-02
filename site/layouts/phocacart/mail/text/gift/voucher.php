<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var array $styles */

$styles = &$displayData['styles'];

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Utils\TextUtils;

foreach ($displayData['gifts'] as $gift) {
  echo TextUtils::underline(Text::_('COM_PHOCACART_TXT_GIFT_VOUCHER_GIFT'))  . "\n\n";
  echo TextUtils::separetedText([$gift['gift_title']], '', "\n");
  echo TextUtils::separetedText([TextUtils::htmlToPlainText($gift['gift_description'])], '', "\n");
  echo TextUtils::separetedText([$gift['gift_sender_name']], Text::_('COM_PHOCACART_FROM') . ': ', "\n");
  echo TextUtils::separetedText([$gift['gift_recipient_name']], Text::_('COM_PHOCACART_TO') . ': ', "\n");
  echo TextUtils::separetedText([$gift['gift_sender_message']], "\n", "\n");
  echo TextUtils::separetedText([$gift['code']], "\n" . Text::_('COM_PHOCACART_COUPON_CODE') . ': ', "\n");
  echo TextUtils::separetedText([$gift['valid_to']], Text::_('COM_PHOCACART_VALID_TILL') . ': ', "\n");
}
