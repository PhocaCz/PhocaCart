<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// ORDER PROCESSED - DOWNLOADABLE ITEMS (No payment made, display only information about possible downloads) (ORDER/DOWNLOAD)
echo '<div class="alert alert-success">';
echo JText::_('COM_PHOCACART_ORDER_SUCCESSFULLY_PROCESSED');
echo '</br>' . JText::_('COM_PHOCACART_ORDER_PROCESSED_DOWNLOADABLE_ITEMS_ADDITIONAL_INFO');
echo '</div>';
?>