<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Mail;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Filesystem\File;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Mail\MailHelper as JoomlaMailHelper;
use Phoca\PhocaCart\Helper\PhocaCartHelper;
use Phoca\PhocaCart\Utils\TextUtils;

defined('_JEXEC') or die;

abstract class MailHelper
{
    private static function link(string $url, bool $xhtml = false, bool $absolute = true): string
    {
        $link = Route::link('site', $url, $xhtml, Route::TLS_IGNORE, $absolute);
        if ($absolute) {
            return $link;
        }

        // Bypass absolute links in href issue (JoomlaMailHelper::convertRelativeToAbsoluteUrls)
        return preg_replace('~^/~', '', $link);
    }

    public static function parseRceipients(?string $recipients): array
    {
        $emails = explode(',', $recipients);

        $emails = array_filter($emails, function($email) {
            return JoomlaMailHelper::isEmailAddress($email);
        });

        return $emails;
    }

    public static function prepareMailData(array $mailData = []): array
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $date = new Date();
        $language = Factory::getApplication()->getLanguage();

        $mailData = array_merge([
            'site_name' => $app->get('sitename'),
            'site_link' => Uri::root(),
            'site_url' => Uri::root(true),

            'user_logged' => !!$user->id,
            'user_name' => $user->name,
            'user_username' => $user->username,
            'user_email' => $user->email,
            'user_language' => $language->getName(),
            'user_language_tag' => $language->getTag(),

            'remote_ip' => IpHelper::getIp(),
            'current_datetime' => $date->format(Text::_('DATE_FORMAT_LC6')),
            'current_date' => $date->format(Text::_('DATE_FORMAT_LC4')),
        ], $mailData);

        return $mailData;
    }

    public static function prepareOrderMailData(\PhocacartOrderView $orderView, object $order, array $addresses, array $status): array
    {
        // Basic data
        $mailData = \PhocacartText::prepareReplaceText($orderView, $order->id, $order, $addresses, $status);
        $mailData = array_merge(self::prepareMailData(), $mailData);

        $mailData['name_others']  = '';
        $mailData['sitename']     = Factory::getApplication()->getConfig()->get('sitename');
        $mailData['status_title'] = Text::_($status['title']);
        $mailData['text_nr'] = Text::_('COM_PHOCACART_ORDER_NR');
        $mailData['text_changed_to'] = Text::_('COM_PHOCACART_ORDER_STATUS_CHANGED_TO');

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

        return $mailData;
    }

    public static function prepareQuestionMailData(Table $question): array
    {
        $mailData = MailHelper::prepareMailData([
            'name' => $question->name,
            'phone' => $question->phone,
            'email' => $question->email,
            'message' => $question->message,
            'message_html' => nl2br($question->message),
            'product_id' => null,
            'product_title' => null,
            'product_title_long' => null,
            'product_sku' => null,
            'product_link' => null,
            'product_link_html' => null,
            'product_url' => null,
            'category_id' => null,
            'category_title' => null,
            'category_title_long' => null,
            'category_link' => null,
            'category_link_html' => null,
            'category_url' => null,
        ]);

        $lang = Factory::getApplication()->getLanguage()->getTag();

        $category = null;
        if ($question->category_id) {
            $category = \PhocacartCategory::getCategoryById($question->category_id);

            if ($category) {
                $mailData['category_id']         = $category->id;
                $mailData['category_title']      = $category->title;
                $mailData['category_title_long'] = $category->title_long;
                $link = \PhocacartRoute::getCategoryRoute($category->id, $category->alias, $lang);
                $mailData['category_link']  = self::link($link);
                $mailData['category_link_html']  = self::link($link, true);
                $mailData['category_url']  = self::link($link, true, false);
            }
        }

        if ($question->product_id) {
            $product = \PhocacartProduct::getProduct($question->product_id, $question->category_id);

            if ($product) {
                $mailData['product_id']         = $product->id;
                $mailData['product_title']      = $product->title;
                $mailData['product_title_long'] = $product->title_long;
                $mailData['product_sku']        = $product->sku;
                if ($category) {
                    $link = \PhocacartRoute::getProductCanonicalLink($product->id, $category->id, $product->alias, $category->alias, 0, $lang);
                } else {
                    $link = \PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias, $product->preferred_catid, $lang);
                }
                $mailData['product_link'] = self::link($link);
                $mailData['product_link_html'] = self::link($link, true);
                $mailData['product_url'] = self::link($link, true, false);
            }
        }

        return $mailData;
    }

    public static function questionMailRecipients(MailTemplate $mailer): bool
    {
        $hasRecipient = false;

        if ($userId = PhocaCartHelper::param('send_email_question')) {
            /** @var \Joomla\CMS\User\User $user */
            $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
            if ($user->id) {
                $mailer->addRecipient($user->email, $user->name);
                $hasRecipient = true;
            }
        }

        if ($emails = MailHelper::parseRceipients(PhocaCartHelper::param('send_email_question_others'))) {
            if (!$hasRecipient) {
                $email = array_shift($emails);
                $mailer->addRecipient($email);
            }

            foreach ($emails as $email) {
                $mailer->addRecipient($email, null, 'bcc');
            }

            $hasRecipient = true;
        }

        return $hasRecipient;
    }

    public static function prepareWatchdogMailData(object $user, array $products, string $lang): array
    {
        $mailProducts = [];
        foreach ($products as $product) {
            if (isset($product->count_categories) && (int)$product->count_categories > 1) {
                $catidA	        = explode(',', $product->catid);
                $cattitleA	    = explode(',', $product->cattitle);
                $cataliasA	    = explode(',', $product->catalias);
                if (isset($product->preferred_catid) && (int)$product->preferred_catid > 0) {
                    $key  = array_search((int)$product->preferred_catid, $catidA);
                } else {
                    $key = 0;
                }
                $product->catid	    = $catidA[$key];
                $product->cattitle 	= $cattitleA[$key];
                $product->catalias 	= $cataliasA[$key];
            }

            // TODO Force useI18n from admin
            $mailProducts[] = [
                'product_title' => $product->title_long ?: $product->title,
                'product_sku'  => $product->sku,
                'product_link'  => self::link(\PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias, (int)$product->preferred_catid, $lang)),
                'product_link_html'  => self::link(\PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias, (int)$product->preferred_catid, $lang), true),
                'product_url'  => self::link(\PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias, (int)$product->preferred_catid, $lang), false, false),
            ];
        }

        $mailData = MailHelper::prepareMailData([
            'user_name' => $user->name,
            'user_username' => $user->username,
            'user_email' => $user->email,
            'products' => $mailProducts,
        ]);

        return $mailData;
    }

    public static function addAttachments(MailTemplate $mailer, ?array $attachments): void
    {
        if (!$attachments) {
            return;
        }

        $pathAttachment = \PhocacartPath::getPath('attachmentfile');

        foreach ($attachments as $attachment) {
            if ($attachment['file_attachment'] ?? '') {
                $attachmentPath = $pathAttachment['orig_abs_ds'] . $attachment['file_attachment'];

                if (File::exists($attachmentPath)) {
                    $mailer->addAttachment(pathinfo($attachmentPath, PATHINFO_FILENAME), $attachmentPath);
                }
            }
        }
    }

    public static function checkOrderStatusMailTemplates(object $status): void
    {
        $tags = [
            'html.document', 'text.document', 'document',
            'ordernumber', 'status_title', 'sitename',
            'html.header', 'html.info', 'html.billing', 'html.shipping', 'html.products', 'html.totals', 'html.link',  'html.downloads',
            'text.header', 'text.info', 'text.billing', 'text.shipping', 'text.products', 'text.totals', 'text.link',  'text.downloads',
        ];

        if ($status->email_customer) {
            MailTemplate::checkTemplate('com_phocacart.order_status.' . $status->id, 'COM_PHOCACART_EMAIL_ORDER_STATUS_SUBJECT', 'COM_PHOCACART_EMAIL_ORDER_STATUS_BODY', $tags, 'COM_PHOCACART_EMAIL_ORDER_STATUS_HTMLBODY');
        }

        if ($status->email_others) {
            MailTemplate::checkTemplate('com_phocacart.order_status.notification.' . $status->id, 'COM_PHOCACART_EMAIL_ORDER_STATUS_NOTIFICATION_SUBJECT', 'COM_PHOCACART_EMAIL_ORDER_STATUS_NOTIFICATION_BODY', $tags, 'COM_PHOCACART_EMAIL_ORDER_STATUS_NOTIFICATION_HTMLBODY');
        }

        $tags = [
            'html_document', 'text_document', 'legacy_document', 'document',
            'ordernumber', 'status_title', 'sitename',
            'html_header', 'html_info', 'html_billing', 'html_shipping', 'html_products', 'html_totals', 'html_link',  'html_downloads',
            'text_header', 'text_info', 'text_billing', 'text_shipping', 'text_products', 'text_totals', 'text_link',  'text_downloads',
        ];
        if ($status->email_gift) {
            MailTemplate::checkTemplate('com_phocacart.order_status.gift.' . $status->id, 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_SUBJECT', 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_BODY', $tags, 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_HTMLBODY');
            MailTemplate::checkTemplate('com_phocacart.order_status.gift_notification.' . $status->id, 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_NOTIFICATION_SUBJECT', 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_NOTIFICATION_BODY', $tags, 'COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_NOTIFICATION_HTMLBODY');
        }
    }

    public static function deleteOrderStatusMailTemplates(int $statusId): void
    {
        MailTemplate::deleteTemplate('com_phocacart.order_status.' . $statusId);
        MailTemplate::deleteTemplate('com_phocacart.order_status.notification.' . $statusId);
        MailTemplate::deleteTemplate('com_phocacart.order_status.gift.' . $statusId);
    }

    public static function renderBody(string $layoutFile, string $format, array $data): string
    {
        return LayoutHelper::render('phocacart.mail.' . $format . '.' . $layoutFile, $data, null, ['client' => 'site']);
    }

    public static function renderOrderBody(object $order, string $format, array &$mailData): string
    {
        $orderView = new \PhocacartOrderView();

        $displayData = [];
        $displayData['params'] = \PhocacartUtils::getComponentParameters();
        $displayData['order'] = $orderView->getItemCommon($order->id);
        $displayData['price'] = new \PhocacartPrice();
        $displayData['price']->setCurrency($displayData['order']->currency_id);
        $displayData['bas'] = $orderView->getItemBaS($order->id, 1);
        if(!isset($displayData['bas']['b'])) {
            $displayData['bas']['b'] = [];
        }
        if(!isset($displayData['bas']['s'])) {
            $displayData['bas']['s'] = [];
        }
        $displayData['products'] = $orderView->getItemProducts($order->id);
        $displayData['discounts'] = $orderView->getItemProductDiscounts($order->id, 0);
        $displayData['total'] = $orderView->getItemTotal($order->id, 1);
        $displayData['taxrecapitulation'] = $orderView->getItemTaxRecapitulation($order->id);
        $displayData['preparereplace'] = \PhocacartText::prepareReplaceText($orderView, $order->id, $displayData['order'], $displayData['bas']);
        $displayData['qrcode'] = \PhocacartText::completeText($displayData['params']->get( 'pdf_invoice_qr_code', '' ), $displayData['preparereplace'], 1);

        $blocks = [];
        $displayData['blocks'] = &$blocks;

        $result = self::renderBody('order', $format, $displayData);

        foreach ($blocks as $name => $block) {
            $mailData[$format . '.' . $name] = $block;
        }

        return $result;
    }

    public static function renderGiftBody(object $order, string $format, array $gifts, array &$mailData): string
    {
        $orderView = new \PhocacartOrderView();

        $displayData = [];
        $displayData['params'] = \PhocacartUtils::getComponentParameters();
        $displayData['order'] = $orderView->getItemCommon($order->id);
        $displayData['gifts'] = $gifts;
        $displayData['price'] = new \PhocacartPrice();
        $displayData['price']->setCurrency($displayData['order']->currency_id);
        $displayData['bas'] = $orderView->getItemBaS($order->id, 1);
        if(!isset($displayData['bas']['b'])) {
            $displayData['bas']['b'] = [];
        }
        if(!isset($displayData['bas']['s'])) {
            $displayData['bas']['s'] = [];
        }
        $displayData['products'] = $orderView->getItemProducts($order->id);
        $displayData['discounts'] = $orderView->getItemProductDiscounts($order->id, 0);
        $displayData['total'] = $orderView->getItemTotal($order->id, 1);
        $displayData['taxrecapitulation'] = $orderView->getItemTaxRecapitulation($order->id);
        $displayData['preparereplace'] = \PhocacartText::prepareReplaceText($orderView, $order->id, $displayData['order'], $displayData['bas']);

        $blocks = [];
        $displayData['blocks'] = &$blocks;

        $result = self::renderBody('gift', $format, $displayData);

        foreach ($blocks as $name => $block) {
            $mailData[$format . '.' . $name] = $block;
        }

        return $result;
    }

    public static function renderArticle(int|string $articleId, array $replaceData, array $billingAddress, array $shippingAddress, bool $allowHtml = true): ?string
    {
        if (!$articleId) {
            return null;
        }

        if (is_numeric($articleId)) {
            $article = \PhocacartRenderFront::renderArticle($articleId, 'mail');
        } else {
            $article = $articleId;
        }

        if ($article) {
            $article 	= \PhocacartText::completeText($article, $replaceData);
            $article 	= \PhocacartText::completeTextFormFields($article, $billingAddress, $shippingAddress);

            if (!$allowHtml) {
                $article = TextUtils::htmlToPlainText($article);
            }

            return $article;
        }

        return null;
    }
}
