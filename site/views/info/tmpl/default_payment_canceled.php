<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// PAYMENT CANCELED
echo '<div class="alert alert-error">';
echo JText::_('COM_PHOCACART_PAYMENT_CANCELED');
echo '</br>' . JText::_('COM_PHOCACART_ORDER_PAYMENT_CANCELED_ADDITIONAL_INFO');
echo '</div>';
?>