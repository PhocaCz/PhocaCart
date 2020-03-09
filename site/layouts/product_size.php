<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d  = $displayData;
$size = new PhocacartSize();


// Size
$length     = $size->getSizeFormat($d['length']);
$width      = $size->getSizeFormat($d['width']);
$height     = $size->getSizeFormat($d['height']);

echo '<div class="ph-cb"></div>';

if ($length || $width || $height ) {
   // echo '<h4 class="ph-header-size">' . JText::_('COM_PHOCACART_HEADER_SIZE') . '</h4>';

    echo '<div class="ph-item-size-box">';
    if ($length) {
        echo '<div class="ph-item-length-txt">' . JText::_('COM_PHOCACART_LENGTH') . ':</div>';
        echo '<div class="ph-item-length">' . $length . '</div>';
    }
    if ($width) {
        echo '<div class="ph-item-width-txt">' . JText::_('COM_PHOCACART_WIDTH') . ':</div>';
        echo '<div class="ph-item-width">' . $width . '</div>';
    }
    if ($height) {
        echo '<div class="ph-item-height-txt">' . JText::_('COM_PHOCACART_HEIGHT') . ':</div>';
        echo '<div class="ph-item-height">' . $height . '</div>';
    }
    echo '</div>';
}

// Weight
$weight     = $size->getSizeFormat($d['weight'], 'weight');
if ($weight) {
 //   echo '<h4 class="ph-header-size">' . JText::_('COM_PHOCACART_HEADER_WEIGHT') . '</h4>';

    echo '<div class="ph-item-size-box">';
    echo '<div class="ph-item-weight-txt">' . JText::_('COM_PHOCACART_WEIGHT') . ':</div>';
    echo '<div class="ph-item-weight">' . $weight . '</div>';
    echo '</div>';
}

// Volume
$volume     = $size->getSizeFormat($d['volume'], 'volume');
if ($volume) {
  //  echo '<h4 class="ph-header-size">' . JText::_('COM_PHOCACART_HEADER_VOLUME') . '</h4>';

    echo '<div class="ph-item-size-box">';
    echo '<div class="ph-item-volume-txt">' . JText::_('COM_PHOCACART_VOLUME') . ':</div>';
    echo '<div class="ph-item-volume">' . $volume . '</div>';
    echo '</div>';
}

echo '<div class="ph-cb"></div>';

?>
