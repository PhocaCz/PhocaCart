<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * Plain-text body block for the withdrawal confirmation email.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
?>
<?= Text::sprintf('COM_PHOCACART_CANCELLATION_CUSTOMER_NOTICE_TITLE', $displayData['mailData']['ordernumber']) ?>
<?= Text::_('COM_PHOCACART_CANCELLATION_CUSTOMER_NOTICE_TEXT') ?>

<?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_NUMBER') ?>:	<?= $displayData['mailData']['ordernumber'] ?>
<?= Text::_('COM_PHOCACART_CANCELLATION_ORDER_DATE') ?>:	<?= $displayData['mailData']['orderdate'] ?>
<?= Text::_('COM_PHOCACART_CANCELLATION_NAME') ?>:	<?= $displayData['mailData']['customer_name'] ?>
<?= Text::_('COM_PHOCACART_CANCELLATION_DATE_OF_WITHDRAWAL') ?>:	<?= $displayData['mailData']['withdrawal_date'] ?>
<?= $displayData['mailData']['site_name'] ?>
