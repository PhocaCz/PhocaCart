<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-categories-box" class="pc-categories-view'.$this->p->get( 'pageclass_sfx' ).'">';
if ( $this->p->get( 'show_page_heading' ) ) { 
	echo '<h1>'. $this->escape($this->p->get('page_heading')) . '</h1>';
}
if ( $this->t['main_description'] != '') {
	echo '<div class="ph-desc">'. JHTML::_('content.prepare', $this->t['main_description']). '</div>';
}
if (!empty($this->t['categories'])) {
	echo '<div class="ph-categories">';
	$i = 0;
	$c = count($this->t['categories']);
	$nc= (int)$this->t['columns_cats'];
	$nw= 12/$nc;//1,2,3,4,6,12
	echo '<div class="row">';
	foreach ($this->t['categories'] as $v) {
		
		//if ($i%$nc==0) { echo '<div class="row">';}
		
		echo '<div class="col-sm-6 col-md-'.$nw.'">';
		echo '<div class="thumbnail ph-thumbnail">';
		
		$image 	= PhocaCartImage::getThumbnailName($this->t['path'], $v->image, 'medium');
		$link	= JRoute::_(PhocaCartRoute::getCategoryRoute($v->id, $v->alias));
		
		if (isset($image->rel) && $image->rel != '') {
			echo '<a href="'.$link.'">';
			echo '<img class="img-responsive ph-image" src="'.JURI::base(true).'/'.$image->rel.'" alt=""';
			if (isset($this->t['image_width_cats']) && $this->t['image_width_cats'] != '' && isset($this->t['image_height_cats']) && $this->t['image_height_cats'] != '') {
				echo ' style="width:'.$this->t['image_width_cats'].';height:'.$this->t['image_height_cats'].'"';
			}
			echo ' />';
			echo '</a>';
		}
		echo '<div class="caption">';
		echo '<h3>'.$v->title.'</h3>';
	
		if (!empty($v->subcategories) && (int)$this->t['csv_display_subcategories'] > 0) {
			echo '<ul>';
			$j = 0;
			foreach($v->subcategories as $v2) {
				if ($j == (int)$this->t['csv_display_subcategories']) {
					break;
				}
				$link2	= JRoute::_(PhocaCartRoute::getCategoryRoute($v2->id, $v2->alias));
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
		
		echo '<p class="pull-right"><a href="'.JRoute::_(PhocaCartRoute::getCategoryRoute($v->id, $v->alias)).'" class="btn btn-primary" role="button">'.JText::_('COM_PHOCACART_VIEW_CATEGORY').'</a></p>';
		echo '<div class="clearfix"></div>';
		echo '</div>';
		echo '</div>';
		echo '</div>'. "\n";
		
		$i++;
		// if ($i%$nc==0 || $c==$i) { echo '</div>';}
	}
	echo '</div></div>'. "\n";
}
echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>