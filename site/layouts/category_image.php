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

$altValue               = PhocaCartImage::getAltTitle($d['image']['title'], $d['image']['image']->rel);
$d['image']['style']    = '';
$srcPlaceHolder = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 '.(int)$d['t']['medium_image_width'] .' '.(int)$d['t']['medium_image_height'] .'\'%3E%3C/svg%3E';
if (isset($d['t']['image_width_cats']) && $d['t']['image_width_cats'] != '' && isset($d['t']['image_height_cats']) && $d['t']['image_height_cats'] != '') {
    $d['image']['style'] = 'style="width:'.$d['t']['image_width_cats'].';height:'.$d['t']['image_height_cats'].'"';
    $srcPlaceHolder = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 '.(int)$d['t']['image_width_cats'] .' '.(int)$d['t']['image_height_cats'] .'\'%3E%3C/svg%3E';
}

$class          = PhocacartRenderFront::completeClass(array($d['s']['c']['img-responsive'], 'ph-image', $d['t']['class_lazyload']));
$src            = JURI::base(true).'/'.$d['image']['image']->rel;
$srcImg         = JURI::base(true).'/'.$d['image']['image']->rel; // fallback

if ($d['t']['display_webp_images'] == 1) {

    $srcWebP        = JURI::base(true).'/'.$d['image']['image']->rel_webp;
    $srcSetWebP     = JURI::base(true).'/'.$d['image']['image']->rel_webp;

    if ($d['t']['lazy_load_categories'] == 1) {

        echo '<picture>';
        echo '<source type="image/webp" data-src="'. $srcWebP.'" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' data-srcset="' . $srcSetWebP . '" />';
        echo '<img src="'.$srcPlaceHolder.'" data-src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' />';
        echo '</picture>';

    } else {

        echo '<picture>';
        echo '<source type="image/webp" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' srcset="' . $srcSetWebP . '" />';
        echo '<img src="' . $srcImg . '" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' />';
        echo '</picture>';
    }

} else {

    if (($d['t']['view'] == 'categories' && $d['t']['lazy_load_categories'] == 1) || (($d['t']['view'] == 'category' || $d['t']['view'] == 'items') && $d['t']['lazy_load_category_items'] == 1)) {
        echo '<img src="'.$srcPlaceHolder.'" data-src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' />';
    } else {
        echo '<img src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' />';
    }
}
?>
