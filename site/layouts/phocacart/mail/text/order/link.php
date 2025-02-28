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
echo Text::_('COM_PHOCACART_MAIL_ORDER_BUTTON').': '.$displayData['preparereplace']['orderlink'] . "\n";
