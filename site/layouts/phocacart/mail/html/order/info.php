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

echo '<div><h1>'.Text::_('COM_PHOCACART_ORDER').'</h1></div>';
echo '<div><b>'.Text::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->order_number).'</div>';
echo '<div><b>'.Text::_('COM_PHOCACART_ORDER_DATE').'</b>: '.HTMLHelper::date($displayData['order']->date, 'DATE_FORMAT_LC4').'</div>';


if (isset($displayData['order']->paymenttitle) && $displayData['order']->paymenttitle != '') {
    echo '<div><b>'.Text::_('COM_PHOCACART_PAYMENT').'</b>: '.$displayData['order']->paymenttitle.'</div>';
}

if (isset($displayData['order']->shippingtitle) && $displayData['order']->shippingtitle != '') {
    echo '<div><b>'.Text::_('COM_PHOCACART_SHIPPING').'</b>: '.$displayData['order']->shippingtitle.'</div>';
}

