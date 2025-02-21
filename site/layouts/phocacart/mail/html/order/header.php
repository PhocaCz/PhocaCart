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
$store_title = $params->get('store_title');
$store_logo = $params->get( 'store_logo');
$store_info = MailHelper::renderArticle((int)$params->get( 'store_info'), [], [], []);

if ($store_title) {
	echo '<h3>'.$store_title.'</h3>';
}

if ($store_logo) {
    echo '<div class="ph__logo"><img src="cid:shop-logo" alt="" style="max-width: 200px; max-height: 200px" /></div>';
}

if ($store_info) {
	echo '<div>'.$store_info.'</div>';
}
