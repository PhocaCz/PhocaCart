<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var array $styles */

$styles = &$displayData['styles'];
?>
<table class="ph__button" style="<?= $styles['w100'] ?>">
    <tbody>
        <tr>
            <td style="text-align: center">
                <a href="<?= $displayData['preparereplace']['orderlink'] ?>" style="<?= $styles['button'] ?>"><?= Text::_('COM_PHOCACART_MAIL_ORDER_BUTTON') ?></a>
            </td>
        </tr>
    </tbody>
</table>
