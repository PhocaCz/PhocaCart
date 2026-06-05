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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

// Shorthand variables
$order       = $this->t['order'];
$eligibility = $this->t['eligibility'];
$token       = $this->t['token'];
$orderId     = (int) $order->id;
$orderNumber = htmlspecialchars(PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number), ENT_QUOTES, 'UTF-8');
$orderDate   = HTMLHelper::date($order->date, Text::_('DATE_FORMAT_LC2'));
$firstName   = $order->cancellation->name_first ?? '';
$lastName    = $order->cancellation->name_last ?? '';
$custName    = htmlspecialchars(trim($firstName . ' ' . $lastName), ENT_QUOTES, 'UTF-8');
$custEmail    = htmlspecialchars($order->cancellation->email ?? '', ENT_QUOTES, 'UTF-8');
$deadlineDays = max(14, (int) $this->p->get('cancellation_deadline_days', 14));
$articleId    = (int)($this->t['cancellation_description_article'] ?? 0);

// Action URL for the POST
$actionUrl   = Route::_('index.php?option=com_phocacart&view=cancellation&task=cancellation.cancel');

// Back to orders link
$ordersUrl   = PhocacartRoute::getOrdersRoute();
if ($token !== '') {
    $ordersUrl .= '&o=' . rawurlencode($token);
}

$layoutAl = new FileLayout('alert', null, ['component' => 'com_phocacart']);
?>

<div id="ph-pc-cancellation-box" class="pc-view pc-cancellation-view<?= htmlspecialchars($this->p->get('pageclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?>">

<?php
$header = Text::_('COM_PHOCACART_CANCELLATION_VIEW_TITLE');
if ($articleId > 0) {
    $header = PhocacartRenderFront::renderArticleTitle($articleId, $header);
}
?>
<?= PhocacartRenderFront::renderHeader([$header]) ?>

<div class="ph-cancellation-intro">
    <?php
    if ($articleId > 0) {
        echo PhocacartRenderFront::renderArticle($articleId, 'html', '');
    } else {
        echo '<p>' . sprintf(Text::_('COM_PHOCACART_CANCELLATION_INTRO'), $deadlineDays, $deadlineDays) . '</p>';
    }
    ?>
</div>

<?php if (!$eligibility['eligible']): ?>
    <?php
    $reasonKey = match ($eligibility['reason']) {
        'deadline' => 'COM_PHOCACART_CANCELLATION_ERROR_DEADLINE',
        'status'   => 'COM_PHOCACART_CANCELLATION_ERROR_STATUS',
        'vat'      => 'COM_PHOCACART_CANCELLATION_ERROR_VAT',
        'already'  => 'COM_PHOCACART_CANCELLATION_ERROR_ALREADY',
        default    => 'COM_PHOCACART_CANCELLATION_ERROR_NOT_FOUND',
    };
    $reasonText = Text::_($reasonKey);
    if ($eligibility['reason'] === 'deadline') {
        $reasonText = sprintf($reasonText, $deadlineDays);
    }
    echo $layoutAl->render(['type' => 'danger', 'text' => $reasonText]);
   ?>
    <div class="ph-cancellation-back-box">
        <a href="<?= Route::_($ordersUrl) ?>" class="<?= $this->s['c']['btn.btn-secondary.btn-sm'] ?> ph-btn">
            <?= PhocacartRenderIcon::icon($this->s['i']['prev'] . ' ph-icon-back') ?>
            <?= Text::_('COM_PHOCACART_CANCELLATION_BACK_TO_ORDERS') ?>
        </a>
    </div>

<?php else: ?>
    <?php
    $daysRemaining = (int) $eligibility['days_remaining'];
    if ($daysRemaining > 0) {
        $deadlineNote = sprintf(Text::_('COM_PHOCACART_CANCELLATION_DEADLINE_DAYS_REMAINING'), $daysRemaining);
        echo $layoutAl->render(['type' => 'warning', 'text' => $deadlineNote]);
    }
    ?>

    <form id="ph-pc-cancellation-form" method="post" action="<?= htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Contract data -->
        <div class="ph-cancellation-section">
            <h3 class="ph-cancellation-section-title"><?= Text::_('COM_PHOCACART_CANCELLATION_CONTRACT_DATA') ?></h3>
            <div class="<?= $this->s['c']['row'] ?>">
                <div class="<?= $this->s['c']['col.xs12.sm4.md4'] ?>">
                    <strong><?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_NUMBER') ?>:</strong>
                </div>
                <div class="<?= $this->s['c']['col.xs12.sm8.md8'] ?>">
                    <?= $orderNumber ?>
                </div>
            </div>
            <div class="<?= $this->s['c']['row'] ?>">
                <div class="<?= $this->s['c']['col.xs12.sm4.md4'] ?>">
                    <strong><?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_DATE') ?>:</strong>
                </div>
                <div class="<?= $this->s['c']['col.xs12.sm8.md8'] ?>">
                    <?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>

        <!-- Customer data -->
        <div class="ph-cancellation-section">
            <h3 class="ph-cancellation-section-title"><?= Text::_('COM_PHOCACART_CANCELLATION_YOUR_DATA') ?></h3>
            <div class="<?= $this->s['c']['row'] ?>">
                <div class="<?= $this->s['c']['col.xs12.sm4.md4'] ?>">
                    <strong><?= Text::_('COM_PHOCACART_CANCELLATION_NAME') ?>:</strong>
                </div>
                <div class="<?= $this->s['c']['col.xs12.sm8.md8'] ?>">
                    <?= $custName ?>
                </div>
            </div>
            <div class="<?= $this->s['c']['row'] ?>">
                <div class="<?= $this->s['c']['col.xs12.sm4.md4'] ?>">
                    <strong><?= Text::_('COM_PHOCACART_CANCELLATION_EMAIL') ?>:</strong>
                </div>
                <div class="<?= $this->s['c']['col.xs12.sm8.md8'] ?>">
                    <?= $custEmail ?>
                </div>
            </div>
            <div class="<?= $this->s['c']['row'] ?>">
                <div class="<?= $this->s['c']['col.xs12.sm4.md4'] ?>">
                    <strong><?= Text::_('COM_PHOCACART_CANCELLATION_DATE_OF_WITHDRAWAL') ?>:</strong>
                </div>
                <div class="<?= $this->s['c']['col.xs12.sm8.md8'] ?>">
                    <?= HTMLHelper::date('now', Text::_('DATE_FORMAT_LC2')) ?>
                </div>
            </div>
        </div>

        <!-- Confirmation -->
        <div class="ph-cancellation-section ph-cancellation-confirm">
            <h3 class="ph-cancellation-section-title"><?= Text::_('COM_PHOCACART_CANCELLATION_CONFIRM_HEADING') ?></h3>
            <p>
                <?= htmlspecialchars(
                    str_replace(
                        ['{ORDERNUMBER}', '{ORDERDATE}'],
                        [$orderNumber, htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8')],
                        Text::_('COM_PHOCACART_CANCELLATION_CONFIRM_TEXT')
                    ),
                    ENT_QUOTES, 'UTF-8'
                ) ?>
            </p>
            <div class="<?= $this->s['c']['form-group'] ?>">
                <div class="<?= $this->s['c']['form-check'] ?>">
                    <input type="checkbox"
                           id="ph-cancellation-confirm-checkbox"
                           name="cancellation_confirm"
                           value="1"
                           class="<?= $this->s['c']['form-check-input'] ?>"
                           required="required"
                    />
                    <label class="<?= $this->s['c']['form-check-label'] ?>" for="ph-cancellation-confirm-checkbox">
                        <?= Text::_('COM_PHOCACART_CANCELLATION_CONFIRM_CHECKBOX') ?>
                    </label>
                </div>
            </div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" name="order_id" value="<?= $orderId ?>"/>
        <?php if ($token !== ''): ?>
            <input type="hidden" name="o" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>"/>
        <?php endif; ?>
        <?= HTMLHelper::_('form.token') ?>

        <!-- Buttons -->
        <div class="ph-cancellation-buttons">
            <button type="submit"
                    id="ph-cancellation-submit-btn"
                    class="<?= $this->s['c']['btn.btn-danger.btn-sm'] ?> ph-btn ph-cancellation-submit-btn"
                    onclick="return phCancellationConfirm(this);">
                <?= PhocacartRenderIcon::icon($this->s['i']['remove'] . ' ph-icon-cancel') ?>
                <?= Text::_('COM_PHOCACART_CANCELLATION_SUBMIT') ?>
            </button>
            <a href="<?= Route::_($ordersUrl) ?>"
               class="<?= $this->s['c']['btn.btn-secondary.btn-sm'] ?> ph-btn ph-cancellation-back-btn">
                <?= PhocacartRenderIcon::icon($this->s['i']['prev'] . ' ph-icon-back') ?>
                <?= Text::_('COM_PHOCACART_CANCELLATION_BACK_TO_ORDERS') ?>
            </a>
        </div>

    </form>

    <script>
    function phCancellationConfirm(btn) {
        var cb = document.getElementById('ph-cancellation-confirm-checkbox');
        if (!cb || !cb.checked) {
            return false; // Browser's :required will handle the message
        }
        btn.disabled = true;
        document.getElementById('ph-pc-cancellation-form').submit();
        return false;
    }
    </script>

<?php endif; ?>

</div>
