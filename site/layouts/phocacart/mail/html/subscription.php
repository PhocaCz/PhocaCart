<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Mail\MailHelper;

/** @var Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
$params = $displayData['params'];
$subscription = $displayData['subscription'];
$product = $displayData['product'];
$user = $displayData['user'];
$eventType = $displayData['eventType'];

/* Styles are defined in styles sublayout */
$styles = [];
$displayData['styles'] = &$styles;

$displayData['blocks'] = [
    'styles' => '',
    'header' => '',
    'details' => '',
    'dates' => '',
    'link' => '',
];

/* Blocks to use in MailTemplate */
$displayData['blocks'] = [
    'styles' => $this->sublayout('styles', $displayData),
    'header' => $this->sublayout('header', $displayData),
    'details' => $this->sublayout('details', $displayData),
    'dates' => $this->sublayout('dates', $displayData),
    'link' => $this->sublayout('link', $displayData),
];
?>

<?= $displayData['blocks']['styles']; ?>

<div style="<?= $styles['fs-normal'] . $styles['w100'] ?>">
    <?= $displayData['blocks']['header']; ?>

    <?= $displayData['blocks']['details']; ?>

    <?= $displayData['blocks']['dates']; ?>

    <?= $displayData['blocks']['link']; ?>
</div>
