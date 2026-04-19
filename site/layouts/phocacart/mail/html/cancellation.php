<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var \Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */

/* Styles are defined in styles sublayout */
$styles = [];
$displayData['styles'] = &$styles;

/* Blocks to use in MailTemplate */
$displayData['blocks'] = [
    'styles'   => $this->sublayout('styles', $displayData),
    'customer' => $this->sublayout('customer', $displayData),
];
?>

<?= $displayData['blocks']['styles']; ?>

<?= $displayData['blocks']['customer']; ?>
