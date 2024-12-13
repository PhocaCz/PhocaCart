<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper as JoomlaMailHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Container\Container;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Exception\PhocaCartException;
use Phoca\PhocaCart\Extension\Pdf;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Mail\MailTemplate;

class PhocacartOrderStatus
{
    private static ?array $statuses = null;
    private static array $options = [];

    private static function loadStatuses(): void
    {
        if (self::$statuses === null) {
            $db    = Container::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__phocacart_order_statuses')
                ->order('ordering, id');
            $db->setQuery($query);
            self::$statuses = $db->loadAssocList('id');

            foreach (self::$statuses as &$status) {
                $status['title'] = Text::_($status['title']);
                if ($status['published']) {
                    self::$options[] = (object) [
                        'text'  => $status['title'],
                        'value' => $status['id'],
                    ];
                }
            }
        }
    }

    public static function getStatus($id = 0): ?array
    {
        self::loadStatuses();
        $status = self::$statuses[$id] ?? null;

        // B/C compatibility
        if ($status) {
            $status['data'] = self::getOptions();
        }

        return $status;
    }

    public static function getOptions(): array
    {
        self::loadStatuses();

        return self::$options;
    }

    private static function updateStock($orderId, $statusId, $stockMovements): void
    {
        if (in_array($stockMovements, ['+', '-'])) {
            //Phocacart
            $orderV   = new PhocacartOrderView();
            $products = $orderV->getItemProducts($orderId);

            if (!empty($products)) {
                foreach ($products as $product) {
                    // See: https://www.phoca.cz/documents/116-phoca-cart-component/932-stock-handling
                    if ((int) $product->stock_calculation == 1) {
                        // =====================
                        // b) Product Variations
                        // In case of b) Product Variations - main product is one of many product variations
                        if (!empty($product->attributes)) {
                            foreach ($product->attributes as $attribute) {
                                if ((int)$attribute->option_id > 0 && (int) $attribute->productquantity > 0) {
                                    // Status ID will be ignored as we know the Stock Movement / Quantity set by product not attribute
                                    PhocacartStock::handleStockAttributeOption((int) $attribute->option_id, $statusId, (int) $attribute->productquantity, $stockMovements);
                                }
                            }
                        } elseif ((int) $product->product_id > 0 && (int) $product->quantity > 0) {
                            // Status ID will be ignored as we know the Stock Movement
                            PhocacartStock::handleStockProduct((int)$product->product_id, $statusId, (int) $product->quantity, $stockMovements);
                        }
                    } else if ((int) $product->stock_calculation == 2 || (int) $product->stock_calculation == 3) {

                        // ============================
                        // c) Advanced Stock Management
                        if ((int) $product->product_id_key > 0 && (int) $product->quantity > 0) {
                            // Status ID will be ignored as we know the Stock Movement
                            PhocacartStock::handleStockProductKey($product->product_id_key, $statusId, (int) $product->quantity, $stockMovements);
                        }

                    } else {
                        // ===============
                        // a) Main Product
                        if ((int) $product->product_id > 0 && (int) $product->quantity > 0) {
                            // Status ID will be ignored as we know the Stock Movement
                            PhocacartStock::handleStockProduct((int) $product->product_id, $statusId, (int) $product->quantity, $stockMovements);
                        }

                        if (!empty($product->attributes)) {
                            foreach ($product->attributes as $attribute) {
                                if ((int) $attribute->option_id > 0 && (int) $attribute->productquantity > 0) {
                                    // Status ID will be ignored as we know the Stock Movement / Quantity set by product not attribute
                                    PhocacartStock::handleStockAttributeOption((int)$attribute->option_id, $statusId, (int)$attribute->productquantity, $stockMovements);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Change user group by changing of status
     *
     * @param   int  $changeUserGroupV
     * @param   int  $userId User ID
     *
     *
     * @since 5.0.0
     */
    private static function updateUserGroup(int $changeUserGroup, int $userId): void
    {
        if (in_array($changeUserGroup, [0, 1]) && $userId > 0) {
            PhocacartGroup::changeUserGroupByRule($userId);
        }
    }

    private static function updatePoints($orderId, $changePointsNeeded, $changePointsReceived): void
    {
        if ($changePointsNeeded == 1 || $changePointsNeeded == 2) {
            $published 	= $changePointsNeeded == 1 ? 1 : 0;
            $db			= Factory::getDBO();

            $q = ' SELECT id '
                .' FROM #__phocacart_reward_points'
                .' WHERE order_id = '. (int)$orderId
                .' AND type = -1'
                .' ORDER BY id';

            $db->setQuery($q);

            $idExists = $db->loadResult();



            if ((int)$idExists > 0) {
                $query = 'UPDATE #__phocacart_reward_points SET'
                    .' published = '.(int)$published
                    .' WHERE id = '.(int)$idExists;
                $db->setQuery($query);
                $db->execute();
            }
        }

        // POINTS RECEIVED
        if ($changePointsReceived == 1 || $changePointsReceived == 2) {

            $published 	= $changePointsReceived == 1 ? 1 : 0;
            $db			= Factory::getDBO();
            $q = ' SELECT id '
                .' FROM #__phocacart_reward_points'
                .' WHERE order_id = '. (int)$orderId
                .' AND type = 1'
                .' ORDER BY id';
            $db->setQuery($q);
            $idExists = $db->loadResult();


            if ((int)$idExists > 0) {
                $query = 'UPDATE #__phocacart_reward_points SET'
                    .' published = '.(int)$published
                    .' WHERE id = '.(int)$idExists;
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    private static function updateDownload(int $orderId, $status): void
    {
        if ($status !== null) {
            PhocacartDownload::setStatusByOrder($orderId, (int)$status);
        }
    }

    private static function sendOrderEmail(object $order, PhocacartOrderView $orderView, array $status, array $addresses, string $orderToken, bool $notifyUser, bool $notifyOthers, $emailSend, $emailSendFormat): int
    {
        $recipient       = '';// Customer/Buyer
        $recipientOthers = '';// others
        $bcc             = '';
        $subject         = '';
        $notificationResult = -1;

        if ($notifyUser) {
            if (self::canSendEmail($orderToken, $order)) {
                $recipient = self::getRecipient($addresses);
            } else {
                PhocacartLog::add(2, 'Order Status - Notify - ERROR', $order->id, Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND'));
            }
        }

        if ($notifyOthers) {
            if ($status['email_others']) {
                $bcc = explode(',', $status['email_others']);
                if (isset($bcc[0]) && JoomlaMailHelper::isEmailAddress($bcc[0])) {
                    $recipientOthers = array_shift($bcc);
                }
            }
        }

        // PDF Feature
        $attachmentContent = '';
        $attachmentName    = '';

        // ------------------------
        // BUILD EMAIL for customer or others
        // ------------------------

        // Set language of order for the customer
        $pLang = new PhocacartLanguage();

        if (!JoomlaMailHelper::isEmailAddress($recipient) && !JoomlaMailHelper::isEmailAddress($recipientOthers)) {
            return 0;
        }

        $orderNumber = PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number);

        // All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
        // which language version the customer got
        $pLang->setLanguage($order->user_lang);
        try {
            // EMAIL CUSTOMER
            $body = $status['email_text'];
            if (!$body) {
                $body = '{text_nr}: {ordernumber} - {text_changed_to}: {status_title}';
            }

            // EMAIL OTHERS
            $bodyOthers = $status['email_text_others'];
            if (!$bodyOthers) {
                $bodyOthers = '{text_nr}: {ordernumber} - {text_changed_to}: {status_title}';
            }

            // REPLACE
            $mailData = MailHelper::prepareOrderMailData($orderView, $order, $addresses, $status);
            $mailData['email']        = $recipient;// Overwrites the $mailData
            $mailData['emailothers'] = $recipientOthers;

            // EMAIL CUSTOMER
            if (!$status['email_subject']) {
                $subject = '{sitename} - {status_title} {text_nr}: {ordernumber}';
            }
            $subject = PhocacartText::completeText($subject, $mailData, 1);

            // EMAIL OTHERS
            if (!$status['email_subject_others']) {
                $subjectOthers = '{sitename} - {status_title} {text_nr}: {ordernumber}';
            }
            $subjectOthers = PhocacartText::completeText($subjectOthers, $mailData, 1);

            // COMPLETE BODY
            $body       = PhocacartText::completeText($body, $mailData, 1);
            $bodyOthers = PhocacartText::completeText($bodyOthers, $mailData, 2);

            $document = '';
            $orderRender = new PhocacartOrderRender();
            if (in_array($emailSendFormat, [0, 2])) {
                switch ($emailSend) {
                    case 1: // Order
                        $documentNumber = $orderNumber;
                        $attachmentName = strip_tags(Text::_('COM_PHOCACART_ORDER') . '_' . $documentNumber) . '.pdf';
                        $attachmentTitle = Text::_('COM_PHOCACART_ORDER_NR') . '_' . $documentNumber;

                        break;
                    case 2: // Invoice
                        $documentNumber = PhocacartOrder::getInvoiceNumber($order->id, $order->date, $order->invoice_number);
                        $attachmentName    = strip_tags(Text::_('COM_PHOCACART_INVOICE') . '_' . $documentNumber) . '.pdf';
                        $attachmentTitle = Text::_('COM_PHOCACART_INVOICE_NR') . '_' . $documentNumber;

                        if (!$documentNumber) {
                            PhocacartLog::add(3, 'Status changed - sending email: The invoice should have been attached to the email, but it does not exist yet. Check order status settings and billing settings.', $order->id, 'Order ID: ' . $order->id . ', Status ID: ' . $status['id']);
                        }

                        break;
                    case 3: // Delivery note
                        $documentNumber = $orderNumber;
                        $attachmentName = strip_tags(Text::_('COM_PHOCACART_DELIVERY_NOTE') . '_' . $documentNumber) . '.pdf';
                        $attachmentTitle = Text::_('COM_PHOCACART_DELIVERY_NOTE_NR') . '_' . $documentNumber;

                        break;
                }

                if ($documentNumber) {
                    $document   = $orderRender->render($order->id, $emailSend, 'mail', $orderToken);
                    $body       .= '<br><br>' . $document;
                    $bodyOthers .= '<br><br>' . $document;
                }
            }

            if (Pdf::load() && in_array($emailSendFormat, [1, 2])) {
                $attachmentContent = Pdf::renderPdf([
                    'title'    => $attachmentTitle,
                    'filename' => $attachmentName,
                    'output'   => $orderRender->render($order->id, $emailSend, 'pdf', $orderToken),
                ]);
            }

            // Email Footer
            $body .= '<br><br>' . PhocacartText::completeText($status['email_footer'], $mailData, 1);
        } finally {
            $pLang->setLanguageBack();
        }

        // CUSTOMER
        // TODO - is it needed???
        self::handleLangPlugin($pLang, $order, $subject);
        self::handleLangPlugin($pLang, $order, $body);

        // OTHERS
        self::handleLangPluginOthers($subjectOthers);
        self::handleLangPluginOthers($bodyOthers);

        // ---------
        // CUSTOMERS
        if (JoomlaMailHelper::isEmailAddress($recipient)) {
            // Notify
            // 1 ... sent
            // 0 ... not sent
            // -1 ... not sent (error)

            // Additional attachments
            $attachment = null;
            if (isset($status['email_attachments']) && !empty($status['email_attachments'])) {
                $attachmentA = json_decode($status['email_attachments'], true);

                if (!empty($attachmentA)) {

                    $attachment     = array();
                    $pathAttachment = PhocacartPath::getPath('attachmentfile');

                    foreach ($attachmentA as $v) {
                        if (isset($v['file_attachment']) && $v['file_attachment'] != '') {
                            $pathAttachmentFile = $pathAttachment['orig_abs_ds'] . $v['file_attachment'];

                            if (Joomla\CMS\Filesystem\File::exists($pathAttachmentFile)) {
                                $attachment[] = $pathAttachmentFile;
                            }
                        }
                    }
                }
            }

            $mailer = new MailTemplate('com_phocacart.order_status.' . $status['id'], $order->user_lang);
            $mailData['document'] = $document;
            $mailer->addTemplateData($mailData);
            if ($attachmentContent) {
                $mailer->addAttachment($attachmentName, $attachmentContent);
            }
            if ($attachment) {
                foreach ($attachment as $file) {
                    $mailer->addAttachment(pathinfo($file, PATHINFO_FILENAME), $file);
                }
            }
            $mailer->addRecipient($recipient);
            try {
                if ($mailer->send()) {
                    $app = Factory::getApplication();
                    if ($app->isClient('administrator')) {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_CUSTOMER_SENT'));
                    }

                    $notificationResult = 1;
                } else {
                    $notificationResult = 0;
                }
            } catch (\Exception $exception) {
                $notificationResult = 0;
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }

        // ------
        // OTHERS
        if ($recipientOthers != '' && JoomlaMailHelper::isEmailAddress($recipientOthers)) {
            $mailer = new MailTemplate('com_phocacart.order_status.' . $status['id'], $order->default_lang);
            $mailData['document'] = $document;
            $mailer->addTemplateData($mailData);
            if ($attachmentContent) {
                $mailer->addAttachment($attachmentName, $attachmentContent);
            }
            $mailer->addRecipient($recipientOthers);
            foreach ($bcc as $email) {
                $mailer->addRecipient($email, null, 'bcc');
            }
            try {
                if ($mailer->send()) {
                    $app = Factory::getApplication();
                    if ($app->isClient('administrator')) {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_OTHERS_SENT'));
                    }
                }
            } catch (\Exception $exception) {
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }

        return $notificationResult;
    }

    private static function sendGiftEmail(object $order, PhocacartOrderView $orderView, array $status, $addresses, $orderToken): void
    {
        $layout = new FileLayout('gift_voucher', null, ['component' => 'com_phocacart', 'client' => 0]);
        $config = Factory::getApplication()->getConfig();
        $pLang  = new PhocacartLanguage();
        $price  = new PhocacartPrice();

        $recipientsEmails    = [];
        $bodyRecipient       = [];// body for all recipients - each recipient has own body
        $attachmentRecipient = [];// attachment for all recipients - each recipient has own attachment (for example PDF with generaded coupons)
        $buyerBody           = ''; // buyer of gift coupons has another body
        $attachmentBuyer     = ''; // buyer of gift coupons gets all coupons - not like recipients - recipients only get own coupons

        if ($status['activate_gift']) {
            PhocaCartCoupon::activateAllGiftsByOrderId($order->id);
        }

        if (!$status['email_gift']) {
            return;
        }

        // Get all Gifts stored for this order
        $gifts = PhocacartCoupon::getGiftsByOrderId($order->id);

        if (!$gifts) {
            return;
        }

        if (in_array($status['email_gift'], [2, 3])) {
            foreach ($gifts as $gift) {
                // 2) Do we have some recipients?
                // One order can include more gifts
                // And one order can include more recipients - e.g. two gifts for different users will be bought in one order
                if (JoomlaMailHelper::isEmailAddress($gift['gift_recipient_email'] ?? '')) {
                    $recipientsEmails[$gift['gift_recipient_email']] = $gift['gift_recipient_email'];
                }
            }
        }

        // Can we send the email to buyer email
        if (in_array($status['email_gift'], [1, 3])) {
            if (self::canSendEmail($orderToken, $order)) {
                $buyerEmail = self::getRecipient($addresses);
                if (!JoomlaMailHelper::isEmailAddress($buyerEmail)) {
                    $buyerEmail = '';
                }
            } else {
                PhocacartLog::add(2, 'Order Status - Notify - ERROR', $order->id, Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND') . ' ' . Text::_('COM_PHOCACART_GIFT_VOUCHER'));
            }
        }

        if (!$buyerEmail && !$recipientsEmails) {
            return;
        }

        if (count($gifts) > 1) {
            $giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHERS');
        } else {
            $giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHER');
        }

        // Build email or paste gift vouchers - do them when at least one should get the email with gift voucher

        // Part for buyer only
        if ($buyerEmail) {
            $buyerEmptyBody = 0;
            if ($status['email_text_gift_sender'] == '') {
                $buyerEmptyBody = 1;
            }
            $buyerBody = $status['email_text_gift_sender'];

            $replacements          = PhocacartText::prepareReplaceText($orderView, $order->id, $order, $addresses, $status);
            $replacements['email'] = $buyerEmail;// Overwrites the $replacements

            if ($status['email_subject_gift_sender'] != '') {
                $buyerSubject = PhocacartText::completeText($status['email_subject_gift_sender'], $replacements, 1);
            } else if ($status['title'] != '') {
                $buyerSubject = $config->get('sitename') . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $replacements['ordernumber'] . ' - ' . $giftVoucherText;
            }

            $buyerBody = PhocacartText::completeText($buyerBody, $replacements, 1);
            $buyerBody = PhocacartText::completeTextFormFields($buyerBody, $addresses['b'], $addresses['s']);

            // All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
            // which language version the customer got
            $pLang->setLanguage($order->user_lang);
        }

        // Prepare PDF
        if (Pdf::load() && in_array($status['email_gift_format'], [1, 2])) {
            $orderNumber = PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number);

            $pdfData     = [
                'title' => Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText,
                'filename' => strip_tags($giftVoucherText . '_' . $orderNumber) . '.pdf',
            ];

            // Initialize PDF for buyer which gets all the coupons
            // we need to initilaize PDF here because we need tcpdf classed in template output
            $pdf      = new stdClass();
            $content  = new stdClass();
            $document = new stdClass();
            Pdf::initializePdf($pdf, $content, $document, $pdfData);
        }

        // Start to prepare gift vouchers (HTML or PDF) based on recipients
        foreach ($gifts as $gift) {
            $recipientUnique = $gift['gift_recipient_email'];

            // Gift voucher rendered to mail: create body for buyer but even for all gift voucher recipients
            if ($status['email_gift_format'] == 0 || $status['email_gift_format'] == 2) {
                $d               = $gift;
                $d['typeview']   = 'Order';
                $d['product_id'] = $gift['gift_product_id'];

                $d['discount']   = $price->getPriceFormat($gift['discount']);
                $d['valid_from'] = HTMLHelper::date($gift['valid_from'], Text::_('DATE_FORMAT_LC3'));
                $d['valid_to']   = HTMLHelper::date($gift['valid_to'], Text::_('DATE_FORMAT_LC3'));
                $d['format']     = 'mail';

                $layoutOutput = $layout->render($d);

                // Render each coupon to buyer body
                $buyerBody .= $layoutOutput;
                $buyerBody .= '<div>&nbsp;</div>';

                // Render each coupon to each recipient body
                if (!isset($bodyRecipient[$recipientUnique])) {
                    $bodyRecipient[$recipientUnique]                     = array();// Each recipient will have own body
                    $bodyRecipient[$recipientUnique]['body_initialized'] = true;
                    $bodyRecipient[$recipientUnique]['data']             = $gift;
                    $bodyRecipient[$recipientUnique]['output']           = '';
                }

                if (isset($bodyRecipient[$recipientUnique]['body_initialized']) && $bodyRecipient[$recipientUnique]['body_initialized']) {
                    $bodyRecipient[$recipientUnique]['output'] .= $layoutOutput;
                    $bodyRecipient[$recipientUnique]['output'] .= '<div>&nbsp;</div>';
                }
            } else {
                // We don't send the voucher in email body but e.g. only as PDF so we still need to initiate mail body for recipient
                if (!isset($bodyRecipient[$recipientUnique])) {
                    $bodyRecipient[$recipientUnique]                     = array();// Each recipient will have own body
                    $bodyRecipient[$recipientUnique]['body_initialized'] = true;
                    $bodyRecipient[$recipientUnique]['data']             = $gift;
                    $bodyRecipient[$recipientUnique]['output']           = '';
                }
            }

            if (Pdf::load() && in_array($status['email_gift_format'], [1, 2])) {
                $d               = $gift;
                $d['typeview']   = 'Order';
                $d['product_id'] = $gift['gift_product_id'];

                $d['discount']   = $price->getPriceFormat($gift['discount']);
                $d['valid_from'] = HTMLHelper::date($gift['valid_from'], Text::_('DATE_FORMAT_LC3'));
                $d['valid_to']   = HTMLHelper::date($gift['valid_to'], Text::_('DATE_FORMAT_LC3'));
                $d['format']     = 'pdf';

                // Render each coupon to buyer PDF
                $d['pdf_instance'] = $pdf;// we need tcpdf instance in output to use different tcpdf functions
                $attachmentBuyer   .= $layout->render($d);


                // Because of token in tcpdf, each recipient needs own tcpdf instance
                // Initialize PDF for each recipient
                // we need to initilaize PDF here because we need tcpdf classed in template output
                if (!isset($attachmentRecipient[$recipientUnique]['pdf_initialized'])) {
                    $attachmentRecipient[$recipientUnique]                    = array();
                    $attachmentRecipient[$recipientUnique]['pdf_initialized'] = true;
                    $attachmentRecipient[$recipientUnique]['pdf']             = new stdClass();
                    $attachmentRecipient[$recipientUnique]['content']         = new stdClass();
                    $attachmentRecipient[$recipientUnique]['document']        = new stdClass();
                    $attachmentRecipient[$recipientUnique]['count']           = 0;
                    $attachmentRecipient[$recipientUnique]['output']          = '';
                    Pdf::initializePdf($attachmentRecipient[$recipientUnique]['pdf'], $attachmentRecipient[$recipientUnique]['content'], $attachmentRecipient[$recipientUnique]['document'], $pdfData);
                }

                if (isset($attachmentRecipient[$recipientUnique]['pdf_initialized']) && $attachmentRecipient[$recipientUnique]['pdf_initialized']) {
                    $d['pdf_instance']                               = $attachmentRecipient[$recipientUnique]['pdf'];// we need tcpdf instance in output to use different tcpdf functions
                    $attachmentRecipient[$recipientUnique]['output'] .= $layout->render($d);
                    $attachmentRecipient[$recipientUnique]['count']++;
                }
            }
        }

        // Send mail to buyer
        if ($buyerEmail && $attachmentBuyer) {
            $pdfData['output']          = $attachmentBuyer;
            $buyerAttachmentContent     = Pdf::renderInitializedPdf($pdf, $content, $document, $pdfData);
            $buyerAttachmentName        = $pdfData['filename'];

            $pLang->setLanguageBack();

            // CUSTOMER
            self::handleLangPlugin($pLang, $order, $buyerSubject);
            self::handleLangPlugin($pLang, $order, $buyerBody);

            if ($buyerEmptyBody == 1) {
                $buyerBody = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText . '<br>' . $buyerBody;
            }

            $notifyGift = PhocacartEmail::sendEmail('', '', $buyerEmail, $buyerSubject, $buyerBody, true, null, null, null, $buyerAttachmentContent, $buyerAttachmentName);

            if (!$notifyGift) {
                PhocacartLog::add(2, 'Order Status - Notify - ERROR - Gift voucher not sent', $order->id, 'Email with gift voucher not sent to buyer (' . $buyerEmail . ')');
            }
        }

        // Send mail to all recipients
        foreach ($recipientsEmails as $k => $v) {
            if (isset($bodyRecipient[$k]['output']) /*&& $bodyRecipient[$k]['output'] != '' - body (built by voucher) can be empty if we set the voucher in PDF only*/) {

                $sitename           = $config->get('sitename');
                $recipientEmptyBody = 0;
                if ($status['email_text_gift_recipient'] == '') {
                    $recipientEmptyBody = 1;
                }
                $recipientBody = $status['email_text_gift_recipient'];

                $replacements                         = PhocacartText::prepareReplaceText($orderView, $order->id, $order, $addresses, $status);
                $replacements['emailgiftrecipient'] = $v;// Overwrites the $replacements
                $replacements['namegiftrecipient']  = isset($bodyRecipient[$k]['data']['gift_recipient_name']) ? $bodyRecipient[$k]['data']['gift_recipient_name'] : '';
                $replacements['namegiftsender']     = isset($bodyRecipient[$k]['data']['gift_sender_name']) ? $bodyRecipient[$k]['data']['gift_sender_name'] : '';
                $replacements['validtogift']        = isset($bodyRecipient[$k]['data']['valid_to']) ? HTMLHelper::date($bodyRecipient[$k]['data']['valid_to'], Text::_('DATE_FORMAT_LC1')) : '';


                if (isset($attachmentRecipient[$k]['count']) && (int) $attachmentRecipient[$k]['count'] > 1) {
                    $giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHERS');
                }

                if ($status['email_subject_gift_recipient'] != '') {
                    $recipientSubject = PhocacartText::completeText($status['email_subject_gift_recipient'], $replacements, 3);
                } else if ($status['title'] != '') {
                    $recipientSubject = $sitename . ' - ' . $status['title'] . ' ' . Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $replacements['ordernumber'] . ' - ' . $giftVoucherText;
                }

                if (isset($bodyRecipient[$k]['output']) /*&& $bodyRecipient[$k]['output'] != ''*/) {
                    $recipeintBody = $recipientBody . $bodyRecipient[$k]['output'];
                    $recipientBody = PhocacartText::completeText($recipientBody, $replacements, 3);
                    $recipientBody = PhocacartText::completeTextFormFields($recipientBody, $addresses['b'], $addresses['s']);
                }


                $recipientAttachmentContent = '';
                $recipientAttachmentName    = '';

                if (isset($attachmentRecipient[$k]['output']) && $attachmentRecipient[$k]['output'] != '' && isset($attachmentRecipient[$k]['pdf']) && $attachmentRecipient[$k]['content'] && $attachmentRecipient[$k]['document']) {

                    // Initialize new PDF for each recipient
                    $pdf                  = new stdClass();
                    $content              = new stdClass();
                    $document             = new stdClass();
                    $pdfData['output'] = '';
                    Pdf::initializePdf($pdf, $content, $document, $pdfData);

                    $pdfData['output']          = $attachmentRecipient[$k]['output'];
                    $recipientAttachmentContent    = Pdf::renderInitializedPdf($attachmentRecipient[$k]['pdf'], $attachmentRecipient[$k]['content'], $attachmentRecipient[$k]['document'], $pdfData);
                    $recipientAttachmentName       = $pdfData['filename'];
                }

                $pLang->setLanguageBack();

                // CUSTOMER
                self::handleLangPlugin($pLang, $order, $recipientSubject);
                self::handleLangPlugin($pLang, $order, $recipientBody);

                if ($recipientEmptyBody == 1) {
                    $recipientBody = Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText . '<br>' . $recipientBody;
                }

                $notifyGift = PhocacartEmail::sendEmail('', '', $v, $recipientSubject, $recipientBody, true, null, null, null, $recipientAttachmentContent, $recipientAttachmentName);

                if (!$notifyGift) {
                    PhocacartLog::add(2, 'Order Status - Notify - ERROR - Gift voucher not sent', $order->id, 'Email with gift voucher not sent to recipient (' . $v . ')');
                }
            }
        }
    }

	/*
	 * $orderToken ... token of order when there is no user - means that the method will not check the user in such case
	 * 1) guest checkout - token is active, user will be not checked when user calls this script (e.g. when ordering)
	 * 2) or payment method server contacts server - token is active, user will be not checked (user is not the one who calls the script but payment method)
	 *
	 * User will be not checked when:
	 * 1) status is changed in administration (vendor in admin is the the shopper user)
	 * 2) guest user makes the order (there is nothing to check)
	 * 3) payment method contact server to change status (payment method does not identify as user - $user = Factory::getUser())
	 *
	 * $notifyUser 0 ... no  1 ... yes 99 ... defined in order status settings
	 * $notifyOthers   0 ... no  1 ... yes 99 ... defined in order status settings
	 * $emailSend  0 ... no  1 ... order, 2 ... invoice, 3 ... delivery_note,  99 ... defined in order status settings
	 * $emailSend  0 ... html  1 ... pdf, 2 ... both,  99 ... defined in order status settings
	 * $stockMovements  = ... no  + ... plus - ... minus 99 ... defined in order status settings
	 */

	public static function changeStatus($orderId, $statusId, $orderToken = '',
        $notifyUser = 99, $notifyOthers = 99, $emailSend = 99,
        $stockMovements = '99', $changeUserGroup = '99', $changePointsNeeded = '99', $changePointsReceived = '99', $emailSendFormat = '99'
    )
    {
        // ORDER INFO
        $orderView = new PhocacartOrderView();
        $order     = $orderView->getItemCommon($orderId);
        $orderToken = (string)$orderToken;

        if (!$order) {
            PhocacartLog::add(2, 'Order Status - ERROR', (int)$orderId, 'Invalid order ID');
            throw new PhocaCartException('Invalid order ID');
        }

        $addresses = $orderView->getItemBaS($order->id, 1);
        if (!isset($addresses['b'])) {
            $addresses['b'] = [];
        }

        if (!isset($addresses['s'])) {
            $addresses['s'] = [];
        }

        $status = self::getStatus($statusId);
        if (!$status) {
            PhocacartLog::add(2, 'Order Status - ERROR', $order->id, 'Invalid status ID');
            throw new PhocaCartException('Invalid status ID');
        }

        if ($notifyUser == 99) {
            if ($status['email_customer'] == 2) {
                $notifyUser = !PhocacartPos::isPos();
            } else {
                $notifyUser = !!$status['email_customer'];
            }
        } else {
            $notifyUser = !!$notifyUser;
        }

        if ($notifyOthers == 99) {
            $notifyOthers = !!$status['email_others'];
        } else {
            $notifyOthers = !!$notifyOthers;
        }

        if ($emailSend == 99) {
            $emailSend = $status['email_send'];
        }

        if ($emailSendFormat == 99) {
            $emailSendFormat = $status['email_send_format'];
        }

        if ($stockMovements == '99') {
            $stockMovements = $status['stock_movements'];
        }
        if (!in_array($stockMovements, ['+', '-'])) {
            $stockMovements = '';
        }

        if ($changeUserGroup == '99') {
            $changeUserGroup = $status['change_user_group'];
        }

        if ($changePointsNeeded == '99') {
            $changePointsNeeded = $status['change_points_needed'];
        }

        if ($changePointsReceived == '99') {
            $changePointsReceived = $status['change_points_received'];
        }

        self::updateStock($order->id, $statusId, $stockMovements);
        self::updateUserGroup((int) $changeUserGroup, (int) $order->user_id);
        self::updatePoints($order->id, $changePointsNeeded, $changePointsReceived);
        self::updateDownload($order->id, $status['download']);
        $notificationResult = self::sendOrderEmail($order, $orderView, $status, $addresses, $orderToken, $notifyUser, $notifyOthers, $emailSend, $emailSendFormat);
        self::sendGiftEmail($order, $orderView, $status, $addresses, $orderToken);

        return $notificationResult;
    }

	/* Usually
	 * - order is saved - the status id in order table is set while saving order (libraries/order/order.php)
	 * - status is changed in administration - the status id in order table is set while changing status (models/phocacarteditstatus.php)
	 * - BUT e.g. if payment method returns status, the status in order needs to be changed - by this function
	 *
	 * method changeStatus - in fact check all possible conditions and cares about notify (send email)
	 * method changeStatusInOrderTable - changes the status directly in order table
	 *
	 * Mostly changeStatus is called when the status is changed in order table (saving order, changing status)
	 * but sometimes e.g. when Payment method set the response, we need to change the status with help of this function
	 * in order table as in fact when making payment response nothing happen to order table - only status is changed
	 * Payment method runs both scripts: changeStatus - to send notify emails, set stock, etc. and changeStatusInOrderTable to check the status in table
	 */

	public static function changeStatusInOrderTable($orderId, $statusId)
    {
        $db = Container::getDbo();
		$query = ' UPDATE #__phocacart_orders SET status_id = '.(int)$statusId
            .' WHERE id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();

		// Set invoice data in case status can set invoice ID
		PhocacartOrder::storeOrderReceiptInvoiceId((int)$orderId, false, (int)$statusId, array('I'));

		return true;
	}

	public static function setHistory($id, $statusId, $notify, $comment): bool
    {
		$db     = Container::getDbo();
		$user   = Container::getUser();// Logged in user, does not matter if customer|vendor|admin
		$userId	= 0;

		if ($user->id > 0) {
			$userId = $user->id;
		}
		$date = Factory::getDate()->toSql();

		$valuesString 	= '('.(int)$id.', '.(int)$statusId.', '.(int)$notify.', ' . $db->quote($comment) . ', ' . $db->quote($date) . ', ' . (int)$userId.')';
		$query = ' INSERT INTO #__phocacart_order_history (order_id, order_status_id, notify, comment, date, user_id)'
					.' VALUES ' . $valuesString;
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	private static function canSendEmail($orderToken, $common)
    {
        $app = Factory::getApplication();
        if ($app->isClient('administrator')) {
            return true;
        }

        // Frontend - Check if we can send email to customer
        $user    = PhocacartUser::getUser();
        $guest   = PhocacartUserGuestuser::getGuestUser();

        // $orderToken is set in case we will not check the user:
        // - in case of guest users
        // - in case of payment method server contacts the server to change the status
        if ($orderToken != '' && $orderToken == $common->order_token && $guest) {
            // User is guest - not logged-in user run this script
            return true;
        } else if ($orderToken != '' && $orderToken == $common->order_token) {
            // Payment method server returned status which will change order status - payment method runs this script
            return true;
        } else if ($user->id == $common->user_id) {
            // User is the customer who made the order
            return true;
        }

        return false;
    }

	private static function getRecipient($addresses): string
    {
		if (JoomlaMailHelper::isEmailAddress($addresses['b']['email_contact'] ?? '')) {
			return $addresses['b']['email_contact'];
		} else if (JoomlaMailHelper::isEmailAddress($addresses['b']['email'] ?? '')) {
			return $addresses['b']['email'];
		} else if (JoomlaMailHelper::isEmailAddress($addresses['s']['email_contact'] ?? '')) {
			return $addresses['s']['email_contact'];
		} else if (JoomlaMailHelper::isEmailAddress($addresses['s']['email'] ?? '')) {
			return $addresses['s']['email'];
		}

		return '';
	}

	private static function handleLangPlugin(PhocacartLanguage $pLang, $common, &$object)
    {
		PluginHelper::importPlugin('system');
		PluginHelper::importPlugin('plgSystemMultilanguagesck');

		// CUSTOMER
		if (isset($common->user_lang) && $common->user_lang != '' && $common->user_lang != '*') {
			$pLang->setLanguage($common->user_lang);
            try {
                if ($object) {
                    // Run content plugins e.g. because of translation
                    // Disable emailclock for PDF | MAIL
                    $object = '{emailcloak=off}' . $object;
                    $object = HTMLHelper::_('content.prepare', $object);
                }

                Dispatcher::dispatchChangeText($object);
            } finally {
                $pLang->setLanguageBack();
            }
		} else {
            if ($object) {
                // Run content plugins e.g. because of translation
                // Disable emailclock for PDF | MAIL
                $object = '{emailcloak=off}' . $object;
                $object = HTMLHelper::_('content.prepare', $object);
            }

			Dispatcher::dispatchChangeText($object);
		}
	}

	private static function handleLangPluginOthers(&$object)
    {
		PluginHelper::importPlugin( 'system' );
		PluginHelper::importPlugin('plgSystemMultilanguagesck');

        if ($object) {
            // Run content plugins e.g. because of translation
            // Disable emailclock for PDF | MAIL
            $object = '{emailcloak=off}' . $object;
            $object = HTMLHelper::_('content.prepare', $object);
        }

		Dispatcher::dispatchChangeText($object);
	}
}
