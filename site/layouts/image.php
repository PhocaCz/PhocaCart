<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d                      = $displayData;


if ($d['t']['display_webp_images'] == 1) {

    echo '<picture>';
    echo ' <source type="image/webp"';
    echo isset($d['srcset-webp']) ? ' srcset="'.$d['srcset-webp'].'"' : ' srcset=""';
    echo isset($d['alt-value']) ? ' alt="'.$d['alt-value'].'"' : ' alt=""';
    echo isset($d['class']) && $d['class'] != '' ? ' class="'.$d['class'].'"' : '';
    echo isset($d['style']) && $d['style'] != '' ? ' style="'.$d['style'].'"' : '';
    echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="'.$d['data-image'].'"' : '';
    echo '/>';

    echo ' <img';
    echo isset($d['src']) ? ' src="'.$d['src'].'"' : ' src=""';
    echo isset($d['alt-value']) ? ' alt="'.$d['alt-value'].'"' : ' alt=""';
    echo isset($d['class']) && $d['class'] != '' ? ' class="'.$d['class'].'"' : '';
    echo isset($d['style']) && $d['style'] != '' ? ' style="'.$d['style'].'"' : '';
    echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="'.$d['data-image'].'"' : '';
    echo '/>';

    echo '</picture>';


} else {
    echo '<img';
    echo isset($d['src']) ? ' src="'.$d['src'].'"' : ' src=""';
    echo isset($d['alt-value']) ? ' alt="'.$d['alt-value'].'"' : ' alt=""';
    echo isset($d['class']) && $d['class'] != '' ? ' class="'.$d['class'].'"' : '';
    echo isset($d['style']) && $d['style'] != '' ? ' style="'.$d['style'].'"' : '';
    echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="'.$d['data-image'].'"' : '';
    echo '/>';
}
?>
