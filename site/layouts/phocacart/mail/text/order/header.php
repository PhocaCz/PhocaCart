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

/*
 * Parameters
 */
$store_title = $params->get('store_title', '');
$store_info = MailHelper::renderArticle((int)$params->get( 'store_info', '' ), [], [], [], false);
//$store_logo = \PhocacartUtils::realCleanImageUrl($store_logo);

if ($store_title != '') {
	echo $store_title . "\n" . str_repeat('-', strlen($store_title)) . "\n\n";
}

if ($store_info != '') {
	echo $store_info . "\n\n";
}
