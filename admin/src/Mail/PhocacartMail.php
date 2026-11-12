<?php
namespace Joomla\Component\PhocaCart\Administrator\Mail;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailTemplate;

class PhocacartMail extends MailTemplate
{
    public static function getTemplates(): array
    {
        return [
            'com_phocacart.subscription.activated' => [
                'title' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'start_date',
                    'end_date',
                    'download_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.subscription.renewed' => [
                'title' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'renewal_date',
                    'new_end_date',
                    'renewal_count',
                    'site_name'
                ]
            ],
            'com_phocacart.subscription.expiring_soon' => [
                'title' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'end_date',
                    'days_remaining',
                    'renewal_url',
                    'site_name'
                ]
            ],
            'com_phocacart.subscription.expired' => [
                'title' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'expired_date',
                    'resubscribe_url',
                    'site_name'
                ]
            ],
            'com_phocacart.subscription.canceled' => [
                'title' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'cancellation_date',
                    'cancellation_reason',
                    'site_name'
                ]
            ],
            'com_phocacart.booking.created' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_CREATED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_CREATED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_CREATED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.booking.confirmed' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_CONFIRMED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_CONFIRMED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_CONFIRMED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.booking.cancelled' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_CANCELLED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_CANCELLED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_CANCELLED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.booking.completed' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_COMPLETED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_COMPLETED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_COMPLETED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.booking.reminder' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_REMINDER_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_REMINDER_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_REMINDER_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ],
            'com_phocacart.booking.status_changed' => [
                'title' => Text::_('COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_TITLE'),
                'subject' => Text::_('COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_SUBJECT'),
                'body' => Text::_('COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_BODY'),
                'tags' => [
                    'user_name',
                    'product_name',
                    'booking_id',
                    'booking_status',
                    'booking_date_start',
                    'booking_date_end',
                    'booking_time_start',
                    'booking_time_end',
                    'booking_nights',
                    'order_number',
                    'account_url',
                    'site_name',
                    'site_url'
                ]
            ]
        ];
    }
}
