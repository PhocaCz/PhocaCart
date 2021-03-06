<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (isset($this->category[0]->parentid) && ($this->t['display_back'] == 1 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->parentid == 0) {
		$linkUp = JRoute::_(PhocacartRoute::getCategoriesRoute());
		$linkUpText = JText::_('COM_PHOCACART_CATEGORIES');
	} else if ($this->category[0]->parentid > 0) {
		$linkUp = JRoute::_(PhocacartRoute::getCategoryRoute($this->category[0]->parentid, $this->category[0]->parentalias));
		$linkUpText = $this->category[0]->parenttitle;
	} else {
		$linkUp 	= false;
		$linkUpText = false;
	}

	if ($linkUp && $linkUpText) {
		echo '<div class="ph-top">'
		.'<a class="'.$this->s['c']['btn.btn-success'].'" title="'.$linkUpText.'" href="'. $linkUp.'" >'
        .'<span class="'.$this->s['i']['back-category'].'"></span> '.JText::_($linkUpText).'</a>'
        .'</div>';
	}
}

echo $this->t['event']->onCategoryBeforeHeader;

$title = '';
if (isset($this->category[0]->title) && $this->category[0]->title != '') {
	$title = $this->category[0]->title;
}

// Image meta is used for open graph plugins
$imageMeta = '';
if (isset($this->category[0]->image) && $this->category[0]->image != '') {
	$pathItem	= $this->t['pathcat'];
	$imageMeta	= JURI::base(true) . '/'. $pathItem['orig_rel_ds'] .$this->category[0]->image;
}

echo PhocacartRenderFront::renderHeader(array($title), '', $imageMeta);

if ( isset($this->category[0]->description) && $this->category[0]->description != '') {
	echo '<div class="ph-desc">'. Joomla\CMS\HTML\HTMLHelper::_('content.prepare', $this->category[0]->description). '</div>';
}
?>
