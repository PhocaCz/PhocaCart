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
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Constants\EmailDocumentType;
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

    private static function updatePaymentDate($orderId){

        $db			= Factory::getDBO();
        $date     = gmdate('Y-m-d H:i:s');
        $query = 'UPDATE #__phocacart_orders SET'
                .' payment_date = '. $db->quote($date)
                .' WHERE id = '.(int)$orderId;
        $db->setQuery($query);
        $db->execute();

    }

    private static function updateDownload(int $orderId, $status): void
    {
        if ($status !== null) {
            PhocacartDownload::setStatusByOrder($orderId, (int)$status);
        }
    }

    /**
     * @param   object              $order
     * @param   PhocacartOrderView  $orderView
     * @param   array               $status
     * @param   array               $addresses
     * @param   string              $orderToken
     * @param   bool                $notifyUser
     * @param   bool                $notifyOthers
     * @param                       $documentType
     * @param   bool                $attachPDF // Attach PDF document to email?
     *
     * @return int  1 ... sent, 0 ... not sent, -1 ... not sent (error)
     *
     * @throws Exception
     * @since 5.0.0
     */
    private static function sendOrderEmail(object $order, PhocacartOrderView $orderView, array $status, array $addresses, string $orderToken, bool $notifyUser, bool $notifyOthers, int $documentType, bool $attachPDF): int
    {
        // Backward compatibility
        $documentType = EmailDocumentType::tryFrom($documentType);
        if (!$documentType) {
            $documentType = EmailDocumentType::Order;
        }

        $recipient       = ''; // Customer/Buyer
        $recipientOthers = ''; // others
        $bcc             = '';
        //$notificationResult = -1;
        $notificationResult = 0;

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

        if (!JoomlaMailHelper::isEmailAddress($recipient) && !JoomlaMailHelper::isEmailAddress($recipientOthers)) {
            //PhocacartLog::add(2, 'Sending email - ERROR', $order->id, Text::_('COM_PHOCACART_ERROR'). ' (Incorrect recipient email)');
            //return -1;
            PhocacartLog::add(1, 'Sending email - Info', $order->id, Text::_('COM_PHOCACART_INFO'). ' (No recipient, no recipient others, no email sent)');
            return 0;
        }

        // ------------------------
        // BUILD EMAIL for customer or others
        // ------------------------

        // PDF Feature
        $attachmentContent = '';

        // Set language of order for the customer
        $pLang = new PhocacartLanguage();

        $orderNumber = PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number);

        // All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
        // which language version the customer got
        $pLang->setLanguage($order->user_lang);
        try {
            $mailData = MailHelper::prepareOrderMailData($orderView, $order, $addresses, $status);
            $mailData['email']       = $recipient;
            $mailData['emailothers'] = $recipientOthers;

            $document = '';
            $orderRender = new PhocacartOrderRender();

            switch ($documentType) {
                case EmailDocumentType::Order:
                default: // Render order as default. If user doesn't want to have odred in email, he can remove it from mail template
                    $documentNumber = $orderNumber;
                    $attachmentName = strip_tags(Text::_('COM_PHOCACART_ORDER') . '_' . $documentNumber) . '.pdf';
                    $attachmentTitle = Text::_('COM_PHOCACART_ORDER_NR') . '_' . $documentNumber;

                    break;
                case EmailDocumentType::Invoice:
                    $documentNumber = PhocacartOrder::getInvoiceNumber($order->id, $order->date, $order->invoice_number);
                    $attachmentName    = strip_tags(Text::_('COM_PHOCACART_INVOICE') . '_' . $documentNumber) . '.pdf';
                    $attachmentTitle = Text::_('COM_PHOCACART_INVOICE_NR') . '_' . $documentNumber;

                    if (!$documentNumber) {
                        PhocacartLog::add(3, 'Status changed - sending email: The invoice should have been attached to the email, but it does not exist yet. Check order status settings and billing settings.', $order->id, 'Order ID: ' . $order->id . ', Status ID: ' . $status['id']);
                    }

                    break;
                case EmailDocumentType::DeliveryNote:
                    $documentNumber = $orderNumber;
                    $attachmentName = strip_tags(Text::_('COM_PHOCACART_DELIVERY_NOTE') . '_' . $documentNumber) . '.pdf';
                    $attachmentTitle = Text::_('COM_PHOCACART_DELIVERY_NOTE_NR') . '_' . $documentNumber;

                    break;
            }

            if ($documentNumber) {
                $document   = $orderRender->render($order->id, $documentType->value, 'mail', $orderToken);
                $mailData['html.document'] = MailHelper::renderOrderBody($order, 'html', $documentType, $mailData);
                $mailData['text.document'] = MailHelper::renderOrderBody($order, 'text', $documentType, $mailData);
            } else {
                $mailData['html.document'] = '';
                $mailData['text.document'] = '';
            }

            if ($attachPDF && Pdf::load()) {
                $attachmentContent = Pdf::renderPdf([
                    'title'    => $attachmentTitle,
                    'filename' => $attachmentName,
                    'output'   => $orderRender->render($order->id, $documentType->value, 'pdf', $orderToken),
                ]);
            }
        } finally {
            $pLang->setLanguageBack();
        }

        // ---------
        // CUSTOMERS
        if (JoomlaMailHelper::isEmailAddress($recipient)) {
            $mailer = new MailTemplate('com_phocacart.order_status.' . $status['id'], $order->user_lang);
            $mailData['document'] = $document;
            $mailer->addTemplateData($mailData);

            if ($attachmentContent) {
                $mailer->addAttachment($attachmentName, $attachmentContent);
            }


            if (!$status['email_attachments']) {
                $status['email_attachments'] = '';
            }
            MailHelper::addAttachments($mailer, json_decode($status['email_attachments'], true));

            $mailer->addRecipient($recipient);
            try {
                if ($mailer->send()) {
                    $app = Factory::getApplication();
                    if ($app->isClient('administrator')) {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_CUSTOMER_SENT'));
                    }
                    $notificationResult = 1;
                } else {
                    PhocacartLog::add(2, 'Sending email - ERROR', $order->id, Text::_('COM_PHOCACART_ERROR'). ' (Error when sending email using mailer)');
                    $notificationResult = -1;
                }
            } catch (\Exception $exception) {
                $notificationResult = -1;
                PhocacartLog::add(2, 'Sending email - ERROR', $order->id, Text::_('COM_PHOCACART_ERROR'). ' ('.Text::_($exception->errorMessage()).')');
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }

        // ------
        // OTHERS
        if ($recipientOthers != '' && JoomlaMailHelper::isEmailAddress($recipientOthers)) {
            $mailer = new MailTemplate('com_phocacart.order_status.notification.' . $status['id'], $order->default_lang);
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

    private static function giftEmailRecipient(string $email, array $mailData, ?array $pdfData, bool $isBuyer = false): object
    {
        $mailData['email'] = $email;
        $mailData['emailgiftrecipient'] = null;
        $mailData['namegiftrecipient'] = null;
        $mailData['namegiftsender'] = null;
        $mailData['validtogift'] = null;

        $recipient = new stdClass();
        $recipient->isBuyer = $isBuyer;
        $recipient->email = $email;
        $recipient->gifts = [];
        $recipient->attachments = [];
        $recipient->mailData = $mailData;

        if ($pdfData) {
            $recipient->pdf = new stdClass();
            $recipient->pdfContent  = new stdClass();
            $recipient->pdfDocument = new stdClass();
            Pdf::initializePdf($recipient->pdf, $recipient->pdfContent, $recipient->pdfDocument, $pdfData);
        } else {
            $recipient->pdf = null;
            $recipient->pdfContent = null;
            $recipient->pdfDocument = null;
        }

        $recipient->pdfFile = null;
        $recipient->pdfFileName = null;

        return $recipient;
    }

    private static function sendGiftEmail(object $order, PhocacartOrderView $orderView, array $status, $addresses, $orderToken): void
    {
        if ($status['activate_gift']) {
            PhocacartCoupon::activateAllGiftsByOrderId($order->id);
        }

        if (!$status['email_gift']) {
            return;
        }

        // Get all Gifts stored for this order
        $gifts = PhocacartCoupon::getGiftsByOrderId($order->id);
        $pLang = new PhocacartLanguage();

        if (!$gifts) {
            return;
        }

        $recipients = [];
        $buyer      = null;
        $mailData   = MailHelper::prepareOrderMailData($orderView, $order, $addresses, $status);
        $pdfData    = null;

        if (in_array($status['email_gift_format'], [1, 2]) && Pdf::load()) {
            $orderNumber = PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number);

            if (count($gifts) > 1) {
                $giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHERS');
            } else {
                $giftVoucherText = Text::_('COM_PHOCACART_GIFT_VOUCHER');
            }

            $pdfData = [
                'title'    => Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText,
                'subject'  => Text::_('COM_PHOCACART_ORDER_NR') . ': ' . $orderNumber . ' - ' . $giftVoucherText,
                'filename' => strip_tags($giftVoucherText . '_' . $orderNumber) . '.pdf',
                'keywords' => '',
            ];
        }

        if (in_array($status['email_gift'], [2, 3])) {
            foreach ($gifts as $gift) {
                // 2) Do we have some recipients?
                // One order can include more gifts
                // And one order can include more recipients - e.g. two gifts for different users will be bought in one order
                if (JoomlaMailHelper::isEmailAddress($gift['gift_recipient_email'] ?? '')) {
                    $recipients[$gift['gift_recipient_email']] = self::giftEmailRecipient($gift['gift_recipient_email'], $mailData, $pdfData);
                }
            }
        }

        // All - users or others get the documents in user language - to save the memory when creating e.g. PDF documents. Even it is better that others see
        // which language version the customer got
        $pLang->setLanguage($order->user_lang);

        // Can we send the email to buyer email
        if (in_array($status['email_gift'], [1, 3])) {
            if (self::canSendEmail($orderToken, $order)) {
                $buyerEmail = self::getRecipient($addresses);
                if (JoomlaMailHelper::isEmailAddress($buyerEmail)) {
                    $buyer = self::giftEmailRecipient($buyerEmail, $mailData, $pdfData, true);
                }
            } else {
                PhocacartLog::add(2, 'Order Status - Notify - ERROR', $order->id, Text::_('COM_PHOCACART_NO_USER_ORDER_FOUND') . ' ' . Text::_('COM_PHOCACART_GIFT_VOUCHER'));
            }
        }

        if (!$recipients) {
            return;
        }

        if ($buyer) {
            $buyer->gifts = $gifts;
        }

        foreach ($gifts as $gift) {
            if (isset($recipients[$gift['gift_recipient_email']])){

                $recipients[$gift['gift_recipient_email']]->gifts[] = $gift;
                $recipients[$gift['gift_recipient_email']]->mailData['emailgiftrecipient'] = $gift['gift_recipient_email'];
                $recipients[$gift['gift_recipient_email']]->mailData['namegiftrecipient']  = $gift['gift_recipient_name'] ?? '';
                $recipients[$gift['gift_recipient_email']]->mailData['namegiftsender']     = $gift['gift_sender_name'] ?? '';
                $recipients[$gift['gift_recipient_email']]->mailData['validtogift']        = $gift['valid_to'] ? HTMLHelper::date($gift['valid_to'], Text::_('DATE_FORMAT_LC1')) : '';
            }
        }

        $pdfLayout = new FileLayout('gift_voucher', null, ['component' => 'com_phocacart', 'client' => 0]);
        $price     = new PhocacartPrice();

        if (in_array($status['email_gift_format'], [1, 2]) && Pdf::load()) {
            // Prepare PDF data
            foreach ($recipients as $recipient) {
                foreach ($recipient->gifts as $gift) {
                    $displayData               = $gift;
                    $displayData['typeview']   = 'Order';
                    $displayData['product_id'] = $gift['gift_product_id'];

                    $displayData['discount']   = $price->getPriceFormat($gift['discount']);

                    if ($gift['valid_from'] == '' || $gift['valid_from'] == '0000-00-00 00:00:00') {
                        $displayData['valid_from'] = '';
                    } else {
                        $displayData['valid_from'] = HTMLHelper::date($gift['valid_from'], Text::_('DATE_FORMAT_LC3'));
                    }

                    if ($gift['valid_to'] == '' || $gift['valid_to'] == '0000-00-00 00:00:00') {
                        $displayData['valid_to'] = '';
                    } else {
                        $displayData['valid_to'] = HTMLHelper::date($gift['valid_to'], Text::_('DATE_FORMAT_LC3'));
                    }
                    $displayData['format']     = 'pdf';
                    // recipient PDF content
                    $displayData['pdf_instance'] = $recipient->pdf; // we need tcpdf instance in output to use different tcpdf functions
                    $recipient->attachments[]    = $pdfLayout->render($displayData);

                    // buyer PDF content (receives all vouchers)
                    $displayData['pdf_instance'] = $buyer->pdf; // we need tcpdf instance in output to use different tcpdf functions
                    $buyer->attachments[]        = $pdfLayout->render($displayData);
                }
            }

            // Create PDFs for recipients
            foreach ($recipients as $recipient) {
                $pdfData['output']      = implode('<div>&nbsp;</div>', $recipient->attachments);
                $recipient->pdfFile     = PhocaPDFRender::renderInitializedPdf($recipient->pdf, $recipient->pdfContent, $recipient->pdfDocument, $pdfData);
                $recipient->pdfFileName = $pdfData['filename'];
            }

            // Create DPF for buyer
            if ($buyer) {
                $pdfData['output']  = implode('<div>&nbsp;</div>', $buyer->attachments);
                $buyer->pdfFile     = PhocaPDFRender::renderInitializedPdf($buyer->pdf, $buyer->pdfContent, $buyer->pdfDocument, $pdfData);
                $buyer->pdfFileName = $pdfData['filename'];
            }
        }

        if ($buyer) {
            $recipients[] = $buyer;
        }

        $allSent = true;
        foreach ($recipients as $recipient) {
            if ($recipient->isBuyer) {
                $mailer = new MailTemplate('com_phocacart.order_status.gift_notification.' . $status['id'], $order->user_lang);
            } else {
                $mailer = new MailTemplate('com_phocacart.order_status.gift.' . $status['id'], $order->user_lang);
            }

            $recipient->mailData['gift_count']    = count($recipient->gifts);
            $recipient->mailData['gift_multiple'] = count($recipient->gifts) > 1;
            $recipient->mailData['html.document'] = MailHelper::renderGiftBody($order, 'html', $gifts, $recipient->mailData);
            $recipient->mailData['text.document'] = MailHelper::renderGiftBody($order, 'text', $gifts, $recipient->mailData);

            $mailer->addTemplateData($recipient->mailData);

            if ($recipient->pdfFile) {
                $mailer->addAttachment($recipient->pdfFileName, $recipient->pdfFile);
            }

            // PHOCADEBUG
            /*
            $config = Factory::getConfig();
            $tmp_path = $config->get('tmp_path');
            $tmp_pdf_file =  $tmp_path . '/debug.pdf';
            try {
                if (!Joomla\Filesystem\File::write($tmp_pdf_file, $recipient->pdfFile)) {
                    throw new Exception('Could not save ' . $tmp_pdf_file, 500);
                }

            } catch (Exception $e) {
                echo "Error writing file: " . $tmp_pdf_file;
            }
            b dump($tmp_pdf_file);
            exit;*/
            // END PHOCADEBUG

            $mailer->addRecipient($recipient->email);
            try {
                if ($mailer->send()) {
                    $app = Factory::getApplication();
                } else {
                    $allSent = false;
                    PhocacartLog::add(2, 'Order Status - Notify - ERROR - Gift voucher not sent', $order->id, 'Email with gift voucher not sent to (' . $recipient->email . ')');
                }
            }
            catch (\Exception $exception) {
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }

        if ($app->isClient('administrator')) {
            if ($allSent) {
                $app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_GIFTS_SENT'));
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_EMAIL_GIFTS_NOT_SENT'), $app::MSG_WARNING);
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
	 * Possible To Do:  $changePaymentDate = 0 ... no  1 ... yes 99 ... defined in order status settings
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

        if ($notifyUser === 99) {
            if ($status['email_customer'] == 2) {
                $notifyUser = !PhocacartPos::isPos();
            } else {
                $notifyUser = !!$status['email_customer'];
            }
        } else {
            $notifyUser = !!$notifyUser;
        }

        if ($notifyOthers === 99) {
            $notifyOthers = !!$status['email_others'];
        } else {
            $notifyOthers = !!$notifyOthers;
        }

        if ($emailSend === 99) {
            $emailSend = $status['email_send'];
        }

        if ($emailSendFormat === 99) {
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

        /* Payment date is automatically set by order status, this cannot be changed when editing status
         * Payment date can be changed in edit of order
        /*
        if ($changePaymentDate == '99') {
            $changePaymentDate = $status['change_payment_date'];
        }*/
        $registry = new Registry($status['params']);
        $statusParams = $registry->toArray();

        if (isset($statusParams['order_paid']) && $statusParams['order_paid'] == 1 && !$order->payment_date) {
            //self::updatePaymentDate($order->id, $changePaymentDate);
            self::updatePaymentDate($order->id);
        }


        self::updateStock($order->id, $statusId, $stockMovements);
        self::updateUserGroup((int) $changeUserGroup, (int) $order->user_id);
        self::updatePoints($order->id, $changePointsNeeded, $changePointsReceived);
        self::updateDownload($order->id, $status['download']);
        $notificationResult = self::sendOrderEmail($order, $orderView, $status, $addresses, $orderToken, $notifyUser, $notifyOthers, (int)$emailSend, !!$emailSendFormat);
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
                    $emailCloakEnabled = PluginHelper::isEnabled('content', 'emailcloak');
                    if ($emailCloakEnabled) {
                        $object = '{emailcloak=off}' . $object;
                    }
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
                $emailCloakEnabled = PluginHelper::isEnabled('content', 'emailcloak');
                if ($emailCloakEnabled) {
                    $object = '{emailcloak=off}' . $object;
                }
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
            $emailCloakEnabled = PluginHelper::isEnabled('content', 'emailcloak');
            if ($emailCloakEnabled) {
                $object = '{emailcloak=off}' . $object;
            }
            $object = HTMLHelper::_('content.prepare', $object);
        }

		Dispatcher::dispatchChangeText($object);
	}
}
