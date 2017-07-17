<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutV	= new JLayoutFile('button_category_view', null, array('component' => 'com_phocacart'));

echo '<div id="ph-pc-categories-box" class="pc-categories-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo $this->t['event']->onCategoriesBeforeHeader;
echo PhocacartRenderFront::renderHeader();

if ( $this->t['main_description'] != '') {
	echo '<div class="ph-desc">'. $this->t['main_description']. '</div>';
}
if (!empty($this->t['categories'])) {
	echo '<div class="ph-categories">';
	$i = 0;
	$c = count($this->t['categories']);
	$nc= (int)$this->t['columns_cats'];
	$nw= 12/$nc;//1,2,3,4,6,12
	echo '<div class="row '.$this->t['class-row-flex'].' grid ph-row-cats">';
	foreach ($this->t['categories'] as $v) {
		
		//if ($i%$nc==0) { echo '<div class="row">';}
		
		echo '<div class="row-item col-sm-6 col-md-'.$nw.'">';
		echo '<div class="ph-item-box grid">';
		echo '<div class="thumbnail ph-thumbnail ph-thumbnail-c">';
		echo '<div class="ph-item-content">';
		
		$image 	= PhocacartImage::getThumbnailName($this->t['path'], $v->image, 'medium');
		$link	= JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias));
		
		if (isset($image->rel) && $image->rel != '') {
			echo '<a href="'.$link.'">';
			echo '<img class="img-responsive ph-image" src="'.JURI::base(true).'/'.$image->rel.'" alt=""';
			if (isset($this->t['image_width_cats']) && $this->t['image_width_cats'] != '' && isset($this->t['image_height_cats']) && $this->t['image_height_cats'] != '') {
				echo ' style="width:'.$this->t['image_width_cats'].';height:'.$this->t['image_height_cats'].'"';
			}
			echo ' />';
			echo '</a>';
		} else {
			// No image, add possible image per CSS
			//echo '<a href="'.$link.'">';
			echo '<div class="ph-image-box-content">';
			echo '<div class="ph-image-box-content-item-'.strip_tags($v->alias).'"></div>';
			echo '</div>';
			//echo '</a>';
		}
		//echo '<div class="caption">';
		
		echo '<h3>';
		if ($this->t['category_name_link'] == 1)
			echo '<a href="'.JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias)).'">'.$v->title.'</a>';
		else {
			echo $v->title;
		}
		echo '</h3>';
		
		
		if (!empty($v->subcategories) && (int)$this->t['csv_display_subcategories'] > 0) {
			echo '<ul>';
			$j = 0;
			foreach($v->subcategories as $v2) {
				if ($j == (int)$this->t['csv_display_subcategories']) {
					break;
				}
				$link2	= JRoute::_(PhocacartRoute::getCategoryRoute($v2->id, $v2->alias));
				echo '<li><a href="'.$link2.'">'.$v2->title.'</a></li>';
				$j++;
			}
			echo '</ul>';
		}
		
		// Description box will be displayed even no description is set - to set height and have all columns same height
		echo '<div class="ph-cat-desc">';
		if ($v->description != '' && (int)$this->t['csv_display_category_desc'] > 0) {
			echo $v->description;
		}
		echo '</div>';
		
		
		if ((int)$this->t['display_view_category_button'] > 0) {
			
			$d									= array();
			$d['link']							= JRoute::_(PhocacartRoute::getCategoryRoute($v->id, $v->alias));
			$d['display_view_category_button']	= $this->t['display_view_category_button'];
			echo '<div class="ph-item-action-box">';
			echo $layoutV->render($d);
			echo '</div>';
		}
		
		echo '<div class="clearfix"></div>';
		//echo '</div>';// end ph-caption
		echo '</div>';// end ph-item-content
		echo '</div>';// end thumbnails
		echo '</div>';// end ph-item-box
		echo '</div>'. "\n";// end row item
		
		$i++;
		// if ($i%$nc==0 || $c==$i) { echo '</div>';}
	}
	echo '</div></div>'. "\n";
}
echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtils::getInfo();
?>