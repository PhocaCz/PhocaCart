<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Phoca\PhocaCart\Mail\MailHelper;

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var array $attachments */
$params = $displayData['params'];
$attachments = &$displayData['attachments'];

if ($store_title = $params->get('store_title')) {
	echo '<h3>'.$store_title.'</h3>';
}

if ($store_logo = $params->get( 'store_logo')) {
    $attachments['store-logo'] = $store_logo;
    echo '<div class="ph__logo"><img src="cid:store-logo" alt="" style="max-width: 200px; max-height: 200px" /></div>';
}

if ($store_info = MailHelper::renderArticle((int)$params->get( 'store_info'), [], [], [])) {
	echo '<div>'.$store_info.'</div>';
}
