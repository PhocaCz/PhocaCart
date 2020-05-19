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

echo $this->t['event']->onCategoriesBeforeHeader;
echo PhocacartRenderFront::renderHeader(array(), '', $this->t['image_categories_view']);

if ( $this->t['main_description'] != '') {
	echo '<div class="ph-desc">'. $this->t['main_description']. '</div>';
}
if (!empty($this->t['categories'])) {
	echo '<div class="ph-categories">';

	if ($this->t['categories_view_layout'] == 2) {
		echo $this->loadTemplate('colspan');
	} else {
		echo $this->loadTemplate('standard');
	}
	echo '</div>'. "\n";
}
echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
