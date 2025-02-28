<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var array $displayData */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Utils\TextUtils;

echo TextUtils::underline(Text::_('COM_PHOCACART_ORDER')) . "\n\n";
echo Text::_('COM_PHOCACART_ORDER_NR').': '.PhocacartOrder::getOrderNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->order_number)."\n";
echo Text::_('COM_PHOCACART_ORDER_DATE').': '.HTMLHelper::date($displayData['order']->date, 'DATE_FORMAT_LC4')."\n";


if (isset($displayData['order']->paymenttitle) && $displayData['order']->paymenttitle != '') {
    echo Text::_('COM_PHOCACART_PAYMENT').': '.$displayData['order']->paymenttitle."\n";
}

if (isset($displayData['order']->shippingtitle) && $displayData['order']->shippingtitle != '') {
    echo Text::_('COM_PHOCACART_SHIPPING').': '.$displayData['order']->shippingtitle;
}
