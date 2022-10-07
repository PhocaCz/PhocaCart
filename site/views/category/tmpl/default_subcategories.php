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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
$layoutI	= new FileLayout('category_image', null, array('component' => 'com_phocacart'));

if (!empty($this->subcategories) && (int)$this->t['cv_display_subcategories'] > 0) {

	echo '<div class="ph-subcategories">'.Text::_('COM_PHOCACART_SUBCATEGORIES') . ':</div>';
	$j = 0;

	if ($this->t['cv_subcategories_layout'] == 2) {

		// IMAGE BOXES
		// Columns of subcategories = columns of products
		$col = PhocacartRenderFront::getColumnClass((int)$this->t['columns_subcat_cat']);
		echo '<div class="'.$this->s['c']['row'].' grid">';

		foreach($this->subcategories as $v) {

			if ($j == (int)$this->t['cv_display_subcategories']) {
				break;
			}
			echo ' <div class="'.$this->s['c']["col.xs12.sm{$col}.md{$col}"].' row-item-subcategory">';
			echo '  <div class="ph-item-subcategory-box">';

			$image = PhocacartImage::getThumbnailName($this->t['pathcat'], $v->image, 'small');
			if (isset($image->rel) && $image->rel != '') {

				echo '<a href="'.Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">';

				$dI	= array();
				$dI['t']			    = $this->t;
				$dI['s']			    = $this->s;
				$dI['image']['title']	= $v->title;
				$dI['image']['image']	= $image;
				echo $layoutI->render($dI);

				echo '</a>';
			} else {
				echo '<a href="'.Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a>';
			}

			echo '<h3><a href="'.Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a></h3>';

			echo '  </div>';
			echo ' </div>';

			$j++;
		}

		echo '</div>';

	} else {

		// LISTS

		echo '<ul>';
		foreach($this->subcategories as $v) {

			if ($j == (int)$this->t['cv_display_subcategories']) {
				break;
			}

		/*	$image = PhocacartImage::getThumbnailName($this->t['pathcat'], $v->image, 'small');
			if (isset($image->rel)) {
				echo '<a href="'.Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'"><img src="'. Uri::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image" /></a>';
			}*/


			echo '<li><a href="'.Route::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a></li>';
			$j++;
		}
		echo '</ul>';
	}

	echo '<div class="ph-hr"></div>';

}

?>
