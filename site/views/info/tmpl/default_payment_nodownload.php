<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// ORDER PROCESSED - STANDARD PRODUCTS - PAYMENT MADE (PAYMENT/NO DOWNLOAD)
echo '<div class="alert alert-success">';

if (isset($this->t['infomessage']['payment_nodownload']) && $this->t['infomessage']['payment_nodownload'] != '') {
	echo $this->t['infomessage']['payment_nodownload'];
} else {
	echo JText::_('COM_PHOCACART_ORDER_AND_PAYMENT_SUCCESSFULLY_PROCESSED');
	echo '</br>' . JText::_('COM_PHOCACART_ORDER_PAYMENT_PROCESSED_ADDITIONAL_INFO');
}

echo '</div>';
?>