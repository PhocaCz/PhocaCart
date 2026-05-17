<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
$d = $displayData;
$price	= new PhocacartPrice();
$lang = Factory::getApplication()->getLanguage();

$lang->load('plg_system_phocacartsubscription', JPATH_ADMINISTRATOR);
if (isset($d['scenario']['signup_fee']) && $d['scenario']['signup_fee'] > 0) {
    echo '<div class="ph-subscription-breakdown ph-small">';
    echo '<small>' . $price->getPriceFormat($d['scenario']['base_price']) . ' (' . Text::_('COM_PHOCACART_PRICE') . ')';
    echo ' + ' . $price->getPriceFormat($d['scenario']['signup_fee']) . ' (' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SIGNUP_FEE') . ')';
    echo '</small>';
    echo '</div>';
}
if (isset($d['scenario']['renewal_discount']) && $d['scenario']['renewal_discount'] > 0) {
    echo '<div class="ph-subscription-breakdown ph-small">';
    echo '<small>' . $price->getPriceFormat($d['scenario']['base_price']) . ' (' . Text::_('COM_PHOCACART_PRICE') . ')';
    echo ' - ' . $price->getPriceFormat($d['scenario']['renewal_discount']) . ' (' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_RENEWAL_DISCOUNT') . ')';
    echo '</small>';
    echo '</div>';
}
