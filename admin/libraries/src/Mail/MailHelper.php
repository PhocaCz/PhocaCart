<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Mail;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailTemplate as JoomlaMailTemplate;

defined('_JEXEC') or die;

abstract class MailHelper
{
    public static function prepareOrderMailData(\PhocacartOrderView $orderView, object $order, array $addresses, array $status)
    {
        // Basic data
        $mailData = \PhocacartText::prepareReplaceText($orderView, $order->id, $order, $addresses, $status);

        $mailData['name_others']  = '';
        $mailData['sitename']     = Factory::getApplication()->getConfig()->get('sitename');
        $mailData['status_title'] = $status['title'];
        $mailData['text_nr'] = Text::_('COM_PHOCACART_ORDER_NR');
        $mailData['text_changed_to'] = Text::_('COM_PHOCACART_ORDER_NR');

        // Billing and shipping address data
        $billingAddress = $addresses['b'];
        $shippingAddress = $addresses['s'];
        $addressKeys = array_merge(array_keys($billingAddress), array_keys($shippingAddress));

        foreach ($addressKeys as $addressKey) {
            if (in_array($addressKey, ['id', 'order_id', 'user_address_id', 'user_token', 'user_groups', 'ba_sa', 'type'])) {
                continue;
            }

            // Common prefix means that if you set:
            // {b_name} ... billing name will be displayed
            // {s_name} ... shipping name will be displayed
            // {bs_name} ... first displaying billing name and if it is not available then display shipping name
            // {sb_name} ... first displaying shipping name and if it is not available then display billing name

            $mailData['b_' . $addressKey] = $billingAddress[$addressKey] ?? '';
            $mailData['s_' . $addressKey] = $shippingAddress[$addressKey] ?? '';

            if ($billingAddress[$addressKey] ?? '') {
                $mailData['bs_' . $addressKey] = $billingAddress[$addressKey];
            } else {
                $mailData['bs_' . $addressKey] = $shippingAddress[$addressKey] ?? '';
            }

            if ($shippingAddress[$addressKey] ?? '') {
                $mailData['sb_' . $addressKey] = $shippingAddress[$addressKey];
            } else {
                $mailData['sb_' . $addressKey] = $billingAddress[$addressKey] ?? '';
            }
        }

        // Products

        // Gifts

        return $mailData;
    }
}
