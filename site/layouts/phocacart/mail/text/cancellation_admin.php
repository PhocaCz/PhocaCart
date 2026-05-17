<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var \Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */

$displayData['blocks'] = [
    'admin' => $this->sublayout('admin', $displayData),
];
?>
<?= $displayData['blocks']['admin']; ?>
