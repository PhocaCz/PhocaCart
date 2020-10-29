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
$x	= $d['x'];

echo '<div id="phItemIdBox'. $d['typeview'] . (int)$d['product_id'] .'">';
echo '<div class="'. $d['class'].'">';
	
if (isset($x->sku) && $x->sku != '') {
	echo '<div class="ph-item-sku-box">';
	echo '<div class="ph-sku-txt">'.JText::_('COM_PHOCACART_SKU').':</div>';
	echo '<div class="ph-sku">'.$x->sku.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->ean) && $x->ean != '') {
	echo '<div class="ph-item-ean-box">';
	echo '<div class="ph-ean-txt">'.JText::_('COM_PHOCACART_EAN').':</div>';
	echo '<div class="ph-ean">'.$x->ean.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->upc) && $x->upc != '') {
	echo '<div class="ph-item-upc-box">';
	echo '<div class="ph-upc-txt">'.JText::_('COM_PHOCACART_UPC').':</div>';
	echo '<div class="ph-upc">'.$x->upc.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->jan) && $x->jan != '') {
	echo '<div class="ph-item-jan-box">';
	echo '<div class="ph-jan-txt">'.JText::_('COM_PHOCACART_JAN').':</div>';
	echo '<div class="ph-jan">'.$x->jan.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->isbn) && $x->isbn != '') {
	echo '<div class="ph-item-isbn-box">';
	echo '<div class="ph-isbn-txt">'.JText::_('COM_PHOCACART_ISBN').':</div>';
	echo '<div class="ph-isbn">'.$x->isbn.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->mpn) && $x->mpn != '') {
	echo '<div class="ph-item-mpn-box">';
	echo '<div class="ph-mpn-txt">'.JText::_('COM_PHOCACART_MPN').':</div>';
	echo '<div class="ph-mpn">'.$x->mpn.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}
if (isset($x->serial_number) && $x->serial_number != '') {
	echo '<div class="ph-item-serial-number-box">';
	echo '<div class="ph-serial-number-txt">'.JText::_('COM_PHOCACART_SERIAL_NUMBER').':</div>';
	echo '<div class="ph-serial-number">'.$x->serial_number.'</div>';
	echo '</div>';
	echo '<div class="ph-cb"></div>';
}

echo '</div>';
echo '</div>';
?>