<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;

if ($d['t']['display_webp_images'] == 1) {

    echo '<picture>';
    echo ' <source type="image/webp"';
    echo isset($d['srcset-webp']) ? ' srcset="'.$d['srcset-webp'].'"' : ' srcset=""';
    //echo isset($d['alt-value']) ? ' alt="'.$d['alt-value'].'"' : ' alt=""';
    //echo isset($d['class']) && $d['class'] != '' ? ' class="'.$d['class'].'"' : ''; // TEST
    //echo isset($d['style']) && $d['style'] != '' ? ' style="'.$d['style'].'"' : ''; // TEST
    echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="'.$d['data-image'].'"' : '';

    echo isset($d['data-image-small']) && $d['data-image-small'] != '' ? ' data-image-small="'.$d['data-image-small'].'"' : '';
    echo isset($d['data-image-medium']) && $d['data-image-medium'] != '' ? ' data-image-medium="'.$d['data-image-medium'].'"' : '';
    echo isset($d['data-image-large']) && $d['data-image-large'] != '' ? ' data-image-large="'.$d['data-image-large'].'"' : '';
    echo isset($d['data-image-original']) && $d['data-image-original'] != '' ? ' data-image-original="'.$d['data-image-original'].'"' : '';
    //echo isset($d['data-image-meta']) && $d['data-image-meta'] != '' ? ' data-image-meta="'.$d['data-image-meta'].'"' : '';// Display only once in img tag
    echo '/>';

    echo ' <img';
    echo isset($d['src']) ? ' src="'.$d['src'].'"' : ' src=""';
    //echo isset($d['src']) ? ' srcset="'.$d['src'].'"' : ' srcset=""';
    echo isset($d['alt-value']) ? ' alt="'.$d['alt-value'].'"' : ' alt=""';
    echo isset($d['class']) && $d['class'] != '' ? ' class="'.$d['class'].'"' : '';
    echo isset($d['style']) && $d['style'] != '' ? ' style="'.$d['style'].'"' : '';
    echo isset($d['width']) && $d['width'] != '' ? ' width="'.$d['width'].'"' : '';
    echo isset($d['height']) && $d['height'] != '' ? ' height="'.$d['height'].'"' : '';
    echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="'.$d['data-image'].'"' : '';

    echo isset($d['data-image-small']) && $d['data-image-small'] != '' ? ' data-image-small="'.$d['data-image-small'].'"' : '';
    echo isset($d['data-image-medium']) && $d['data-image-medium'] != '' ? ' data-image-medium="'.$d['data-image-medium'].'"' : '';
    echo isset($d['data-image-large']) && $d['data-image-large'] != '' ? ' data-image-large="'.$d['data-image-large'].'"' : '';
    echo isset($d['data-image-original']) && $d['data-image-original'] != '' ? ' data-image-original="'.$d['data-image-original'].'"' : '';
    echo isset($d['data-image-meta']) && $d['data-image-meta'] != '' ? ' data-image-meta="'.$d['data-image-meta'].'"' : '';

    echo isset($d['s']['a']['lazyload']) && $d['s']['a']['lazyload'] != '' ? $d['s']['a']['lazyload'] : '';
    echo '/>';

    echo '</picture>';


} else {

    if(isset($d['src']) && strtolower(pathinfo($d['src'], PATHINFO_EXTENSION)) == 'svg') {
        // SVG Support
        if (isset($d['src'])) {
            $d['src'] = PhocacartUtils::getSvgOriginalInsteadThumb($d['src']);
        }

        if (isset($d['data-image'])) {
            $d['data-image'] = PhocacartUtils::getSvgOriginalInsteadThumb($d['data-image']);
        }

        echo '<img';
        echo isset($d['src']) ? ' src="' . $d['src'] . '"' : ' src=""';
        echo isset($d['alt-value']) ? ' alt="' . $d['alt-value'] . '"' : ' alt=""';
        echo isset($d['class']) && $d['class'] != '' ? ' class="' . $d['class'] . '"' : '';
        echo isset($d['style']) && $d['style'] != '' ? ' style="' . $d['style'] . '"' : '';
        echo isset($d['width']) && $d['width'] != '' ? ' width="' . $d['width'] . '"' : '';
        echo isset($d['height']) && $d['height'] != '' ? ' height="' . $d['height'] . '"' : '';
        echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="' . $d['data-image'] . '"' : '';

        echo isset($d['data-image-small']) && $d['data-image-small'] != '' ? ' data-image-small="' . $d['data-image-small'] . '"' : '';
        echo isset($d['data-image-medium']) && $d['data-image-medium'] != '' ? ' data-image-medium="' . $d['data-image-medium'] . '"' : '';
        echo isset($d['data-image-large']) && $d['data-image-large'] != '' ? ' data-image-large="' . $d['data-image-large'] . '"' : '';
        echo isset($d['data-image-original']) && $d['data-image-original'] != '' ? ' data-image-original="' . $d['data-image-original'] . '"' : '';
        echo isset($d['data-image-meta']) && $d['data-image-meta'] != '' ? ' data-image-meta="' . $d['data-image-meta'] . '"' : '';
        echo isset($d['s']['a']['lazyload']) && $d['s']['a']['lazyload'] != '' ? $d['s']['a']['lazyload'] : '';
        echo '/>';

    } else {


        //list($width, $height) = getimagesize(JPATH_BASE . str_replace(Joomla\CMS\Uri\Uri::base(true), '', $d['src']));
        echo '<img';
        echo isset($d['src']) ? ' src="' . $d['src'] . '"' : ' src=""';
        echo isset($d['alt-value']) ? ' alt="' . $d['alt-value'] . '"' : ' alt=""';
        echo isset($d['class']) && $d['class'] != '' ? ' class="' . $d['class'] . '"' : '';
        echo isset($d['style']) && $d['style'] != '' ? ' style="' . $d['style'] . '"' : '';
        echo isset($d['width']) && $d['width'] != '' ? ' width="' . $d['width'] . '"' : '';
        echo isset($d['height']) && $d['height'] != '' ? ' height="' . $d['height'] . '"' : '';
        echo isset($d['data-image']) && $d['data-image'] != '' ? ' data-image="' . $d['data-image'] . '"' : '';

        echo isset($d['data-image-small']) && $d['data-image-small'] != '' ? ' data-image-small="' . $d['data-image-small'] . '"' : '';
        echo isset($d['data-image-medium']) && $d['data-image-medium'] != '' ? ' data-image-medium="' . $d['data-image-medium'] . '"' : '';
        echo isset($d['data-image-large']) && $d['data-image-large'] != '' ? ' data-image-large="' . $d['data-image-large'] . '"' : '';
        echo isset($d['data-image-original']) && $d['data-image-original'] != '' ? ' data-image-original="' . $d['data-image-original'] . '"' : '';
        echo isset($d['data-image-meta']) && $d['data-image-meta'] != '' ? ' data-image-meta="' . $d['data-image-meta'] . '"' : '';
        echo isset($d['s']['a']['lazyload']) && $d['s']['a']['lazyload'] != '' ? $d['s']['a']['lazyload'] : '';
        echo '/>';
    }
}
?>
