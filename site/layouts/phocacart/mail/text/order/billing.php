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

echo Text::_('COM_PHOCACART_BILLING_ADDRESS') . "\n";

if (!empty($displayData['bas']['b'])) {
    $address = $displayData['bas']['b'];

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
	if ($displayData['order']->oidn_spec_billing_desc) {
        echo MailHelper::renderArticle($displayData['order']->oidn_spec_billing_desc, $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false);
    } else {
        echo MailHelper::renderArticle((int)$params->get( 'oidn_global_billing_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false);
	}
}
