<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
if (!empty($this->subcategories) && (int)$this->t['cv_display_subcategories'] > 0) {

	echo '<div class="ph-subcategories">'.JText::_('COM_PHOCACART_SUBCATEGORIES') . ':</div>';
	$j = 0;

	if ($this->t['cv_subcategories_layout'] == 2) {

		// IMAGE BOXES
		// Columns of subcategories = columns of products
		$col 			= PhocacartRenderFront::getColumnClass((int)$this->t['columns_subcat_cat']);
		echo '<div class="row row-flex grid">';

		foreach($this->subcategories as $v) {

			if ($j == (int)$this->t['cv_display_subcategories']) {
				break;
			}
			echo ' <div class="row-item-subcategory col-sx-12 col-sm-'.$col.' col-md-'.$col.'">';
			echo '  <div class="ph-item-subcategory-box">';



			$image = PhocacartImage::getThumbnailName($this->t['pathcat'], $v->image, 'small');
			if (isset($image->rel)) {
                $altValue = PhocaCartImage::getAltTitle($v->title, $v->image);
				echo '<a href="'.JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'"><img src="'. JURI::base(true).'/'.$image->rel.'" alt="'.$altValue.'" class="img-responsive ph-image" /></a>';
			}

			echo '<h3><a href="'.JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a></h3>';

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
				echo '<a href="'.JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'"><img src="'. JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive ph-image" /></a>';
			}*/


			echo '<li><a href="'.JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a></li>';
			$j++;
		}
		echo '</ul>';
	}

	echo '<hr />';

}

?>
