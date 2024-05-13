<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$d = $displayData;
$stockStatusClass = isset($d['stock_status_class']) ? ' '. $d['stock_status_class'] : '';

if (!isset($d['ajax']) || (isset($d['ajax']) && $d['ajax'] != 1)) {
    echo '<div class="ph-item-stock-box" id="phItemStockBox'. $d['typeview'] . (int)$d['product_id'].'">';
}

echo ' <div class="'. $d['class'].'">';
echo '  <div class="ph-stock-txt">'. Text::_('COM_PHOCACART_AVAILABILITY').':</div>';
echo '  <div class="ph-stock'. $stockStatusClass . '">'.Text::_($d['stock_status_output']).'</div>';
echo '  <div class="ph-cb"></div>';
echo ' </div>';

if (!isset($d['ajax']) || (isset($d['ajax']) && $d['ajax'] != 1)) {
    echo '</div>';
}
?>

