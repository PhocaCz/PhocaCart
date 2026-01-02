<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Uri\Uri;
$d                      = $displayData;
$productIdName			= 'V'.$d['typeview'].'P'.(int)$d['product_id'];
$altValue               = PhocaCartImage::getAltTitle($d['title'], $d['image']['image']->original);


// Native lazy load
$attributeLazyLoad = '';
if ($d['t']['lazy_load_category_items'] == 2) {
    $attributeLazyLoad = isset($d['s']['a']['lazyload']) && $d['s']['a']['lazyload'] != '' ? $d['s']['a']['lazyload'] : '';
}

if ($d['typeview'] == 'Pos') {
    $d['t']['lazy_load_category_items'] = 2;
}

$class          = $d['s']['c']['img-responsive'].' ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName;
$classSwitch    = $d['s']['c']['img-responsive'].' ph-image phIR phjProductImageNoChange'.$productIdName;
$classSource    = 'phjProductSource'.$productIdName;
$classLazyLoad  = '';
if ($d['t']['lazy_load_category_items'] == 1) {
    $classLazyLoad = 'ph-lazyload';
    $class = $classLazyLoad . ' '.$d['s']['c']['img-responsive'].' ph-image phjProductImage'.$productIdName;// Remove $d['image']['phil'] for lazy loads (switch image disabled)
}


$src            = Uri::base(true).'/'.$d['image']['image']->rel;
$srcImg         = Uri::base(true).'/'.$d['image']['image']->rel; // fallback
$dataImg        = Uri::base(true).'/'.$d['image']['default']->rel; // switch - back to default

$srcPlaceHolder = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 '.(int)$d['t']['medium_image_width'] .' '.(int)$d['t']['medium_image_height'] .'\'%3E%3C/svg%3E';


echo '<div class="phIBoxOH '. $d['layouttype'] . '">';
echo '<div class="phIBox '.$classLazyLoad.'">';

if ($d['t']['display_webp_images'] == 1) {

    $srcWebP        = Uri::base(true).'/'.$d['image']['image']->rel_webp;
    $srcSetWebP     = Uri::base(true).'/'.$d['image']['image']->rel_webp;
    $dataImgWebP    = Uri::base(true).'/'.$d['image']['default']->rel_webp;

    if ($d['t']['lazy_load_category_items'] == 1) {

        echo '<picture>';
        //echo '<source type="image/webp" data-src="'. $srcWebP.'" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' data-srcset="' . $srcSetWebP . '" data-image="' . $dataImgWebP . '" />';
       // echo '<source type="image/webp" data-src="'. $srcWebP.'" alt="' . $altValue . '"  data-srcset="' . $srcSetWebP . '" data-image="' . $dataImgWebP . '" />';// TEST

        // TEST 2 (removed data-src because it is transformed to src in picture tag which is obsolete)
        // class needed because of chaning attributes - changing attributes changes images
        // alt="' . $altValue . '"
        echo '<source type="image/webp" data-srcset="' . $srcSetWebP . '" data-image="' . $dataImgWebP . '" class="'.$classSource.'" />';


        echo '<img src="'.$srcPlaceHolder.'" data-src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' data-image="'. $dataImg.'" />';
        echo '</picture>';

    } else {

        echo '<picture>';
        //echo '<source type="image/webp" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' srcset="' . $srcSetWebP . '" data-image="' . $dataImg . '" />';
        // class needed because of chaning attributes - changing attributes changes images
        // alt="' . $altValue . '"
        echo '<source type="image/webp" srcset="' . $srcSetWebP . '" data-image="' . $dataImg . '" class="'.$classSource.'" />';// TEST
        echo '<img src="' . $srcImg . '" alt="' . $altValue . '" class="' . $class . '" ' . $d['image']['style'] . ' data-image="' . $dataImg . '"'.$attributeLazyLoad.' />';
        echo '</picture>';

        // Switch
        if (isset($d['image']['second']->rel_webp) && $d['image']['second']->rel_webp != '') {
            $switchImg      = Uri::base(true).'/'.$d['image']['second']->rel_webp; // switch
            echo '<span class="phIRBox"><img src="'. $switchImg.'" alt="'.$altValue.'" class="'.$classSwitch.'" '. $d['image']['style'].' /></span>';
        }

    }

} else {

    if(isset($d['image']['image']->original) && strtolower(pathinfo($d['image']['image']->original, PATHINFO_EXTENSION)) == 'svg') {
        // SVG Support
        $src = PhocacartUtils::getSvgOriginalInsteadThumb($src);
        $dataImg = PhocacartUtils::getSvgOriginalInsteadThumb($dataImg);


        echo '<img src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' data-image="'. $dataImg.'"'.$attributeLazyLoad.' />';

    } else {

        if ($d['t']['lazy_load_category_items'] == 1) {

            echo '<img src="'.$srcPlaceHolder.'" data-src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' data-image="'. $dataImg.'" />';

        } else {

            echo '<img src="'. $src.'" alt="'.$altValue.'" class="'.$class.'" '.$d['image']['style'].' data-image="'. $dataImg.'"'.$attributeLazyLoad.' />';

            // Switch
            if (isset($d['image']['second']->rel) && $d['image']['second']->rel != '') {
                $switchImg      = Uri::base(true).'/'.$d['image']['second']->rel; // switch
                echo '<span class="phIRBox"><img src="'. $switchImg.'" alt="'.$altValue.'" class="'.$classSwitch.'" '. $d['image']['style'].' /></span>';
            }

        }

    }
}


echo '</div>';// end phIBox
echo '</div>';// end phIBoxOH

/*
 *
 *
 *     //$class  = 'ph-lazy img-responsive ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName;
data-src="'. $src.'" - is in webp source for
    //echo '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 640 428\'%3E%3C/svg%3E" data-src="'. Uri::base(true).'/'.$d['image']->rel.'" alt="'.$altValue.'" class="ph-lazy img-responsive ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName.'" '.$d['image']['style'].' data-image="'. Uri::base(true).'/'.$d['default_image']->rel.'" />';

		//echo '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 640 428\'%3E%3C/svg%3E"  data-src="'. Uri::base(true).'/'.$d['image']->rel.'" alt="'.$altValue.'" class="ph-lazy img-responsive ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName.'" '.$d['image']['style'].' data-image="'. Uri::base(true).'/'.$d['default_image']->rel.'" />';

echo '<picture>';



echo '<source type="image/webp" alt="'.$altValue.'"  data-src="'. Uri::base(true).'/'.$d['image']->rel_webp.'" alt="'.$altValue.'"  data-srcset="'. Uri::base(true).'/'.$d['image']->rel_webp.'" alt="'.$altValue.'"  class="ph-lazy img-responsive ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName.'" '.$d['image']['style'].' data-image="'. Uri::base(true).'/'.$d['default_image']->rel.'" />';

echo '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 640 428\'%3E%3C/svg%3E" data-src="'. Uri::base(true).'/'.$d['image']->rel.'" alt="'.$altValue.'" class="ph-lazy img-responsive ph-image '. $d['image']['phil'].' phjProductImage'.$productIdName.'" '.$d['image']['style'].' data-image="'. Uri::base(true).'/'.$d['default_image']->rel.'" />';

echo '</picture>';
*/

?>
