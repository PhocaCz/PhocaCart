<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
$params = $displayData['params'];

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Utils\TextUtils;

echo TextUtils::underline(Text::_('COM_PHOCACART_SHIPPING_ADDRESS')) . "\n\n";

if (!empty($displayData['bas']['b'])) {
    if (($displayData['bas']['b']['ba_sa'] ?? 0) || ($displayData['bas']['s']['ba_sa'] ?? 0)) {
        $address = $displayData['bas']['b'];
    } else {
        $address = $displayData['bas']['s'];
    }

    echo TextUtils::separetedText([$address['company']], '', "\n");
    echo TextUtils::separetedText([$address['name_degree'], $address['name_first'], $address['name_middle'], $address['name_last']], '', "\n");
    echo TextUtils::separetedText([$address['address_1']], '', "\n");
    echo TextUtils::separetedText([$address['address_2']], '', "\n");
    echo TextUtils::separetedText([$address['zip'], $address['city']], '', "\n");
    echo TextUtils::separetedText([$address['regiontitle']], '', "\n");
    echo TextUtils::separetedText([$address['countrytitle']], '', "\n");

    if ($address['vat_1'] || $address['vat_2']) {
        echo "\n";
    }
    echo TextUtils::separetedText([$address['vat_1']], Text::_('COM_PHOCACART_VAT_1_LABEL') . ': ', "\n");
    echo TextUtils::separetedText([$address['vat_2']], Text::_('COM_PHOCACART_VAT_2_LABEL') . ': ', "\n");

	$addressDescription = '';
	if ($displayData['order']->oidn_spec_shipping_desc) {
        echo MailHelper::renderArticle($displayData['order']->oidn_spec_shipping_desc, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false);
    } else {
        echo MailHelper::renderArticle((int)$params->get( 'oidn_global_shipping_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false);
	}
}
