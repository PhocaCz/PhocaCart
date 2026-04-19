<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var \Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */

$styles = [];
$displayData['styles'] = &$styles;

$displayData['blocks'] = [
    'styles' => $this->sublayout('styles', $displayData),
    'admin'  => $this->sublayout('admin', $displayData),
];
?>

<?= $displayData['blocks']['styles']; ?>

<?= $displayData['blocks']['admin']; ?>
