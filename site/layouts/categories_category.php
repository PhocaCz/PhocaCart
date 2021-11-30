<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;

$layoutV	= new FileLayout('button_category_view', null, array('component' => 'com_phocacart'));
$layoutI	= new FileLayout('category_image', null, array('component' => 'com_phocacart'));

$d 		= $displayData;
$v		= $d['v'];
$t		= $d['t'];
$s      = $d['s'];

$image 	= PhocacartImage::getThumbnailName($t['path'], $v->image, $d['image_size']);
$link	= Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias));

//echo '<div class="ph-image-box">';
$dI	= array();
if (isset($image->rel) && $image->rel != '') {

    $dI['t']			    = $t;
    $dI['s']			    = $s;
    $dI['image']['title']	= $v->title;
    $dI['image']['image']	= $image;

    echo '<a href="'.$link.'">';
    echo $layoutI->render($dI);
	echo '</a>';

} else if ($v->icon_class != '') {
    echo '<div class="ph-image-box-content">';
	echo '<div class="ph-image-box-content-item"><span class="'. PhocacartText::filterValue($v->icon_class, 'text').' ph-image-box-content-icon"></span></div>';
	echo '</div>';
}  else {
	// No image, add possible image per CSS
	//echo '<a href="'.$link.'">';
	echo '<div class="ph-image-box-content">';
	echo '<div class="ph-image-box-content-item-'.strip_tags($v->alias).'"></div>';
	echo '</div>';
	//echo '</a>';
}


//echo '</div>';
//echo '<div class="ph-caption">';

echo PhocacartRenderFront::renderCategoryHeader($t['category_name_link'], $v);


if (!empty($v->subcategories) && (int)$t['csv_display_subcategories'] > 0) {
	echo '<ul>';
	$j = 0;
	foreach($v->subcategories as $v2) {
		if ($j == (int)$t['csv_display_subcategories']) {
			break;
		}
		$link2	= Route::_(PhocacartRoute::getCategoryRoute($v2->id, $v2->alias));
		echo '<li><a href="'.$link2.'">'.$v2->title.'</a></li>';
		$j++;
	}
	echo '</ul>';
}

// Description box will be displayed even no description is set - to set height and have all columns same height
echo '<div class="ph-cat-desc">';
if ($v->description != '' && (int)$t['csv_display_category_desc'] > 0) {
	echo $v->description;
}
echo '</div>';


if ((int)$t['display_view_category_button'] > 0) {

	$d2									= array();
	$d2['link']							= $link;
    $d2['s']							= $s;
	$d2['display_view_category_button']	= $t['display_view_category_button'];
	echo '<div class="ph-item-action-box">';
	echo $layoutV->render($d2);
	echo '</div>';
}
//echo '</div>';
