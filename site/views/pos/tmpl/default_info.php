<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die();

$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

echo '<div id="ph-pc-pos-site">';

// TOP
echo '<div class="ph-pos-wrap-top">';
echo $this->loadTemplate('vendor');
echo $this->loadTemplate('logo');
echo '</div>';

echo '<div class="ph-pos-wrap-main">';
echo '<div class="ph-pos-main-page">';

echo $layoutAl->render(array('type' => $this->t['infotype'], 'text' => $this->t['infotext'], 'pos' => 1));

echo '</div>';// end ph-pos-main-page
echo '</div>';// end ph-pos-wrap-main

echo '<div class="ph-pos-wrap-bottom">';
echo $this->loadTemplate('bottom');
echo '</div>';

echo '</div>';
?>
