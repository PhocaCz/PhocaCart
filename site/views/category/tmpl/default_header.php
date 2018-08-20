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
		.'<a class="btn btn-success" title="'.$linkUpText.'" href="'. $linkUp.'" ><span class="glyphicon glyphicon-arrow-left"></span> '.JText::_($linkUpText).'</a></div>';
	}
}

echo $this->t['event']->onCategoryBeforeHeader;

$title = '';
if (isset($this->category[0]->title) && $this->category[0]->title != '') {
	$title = $this->category[0]->title;
}

echo PhocacartRenderFront::renderHeader(array($title));

if ( isset($this->category[0]->description) && $this->category[0]->description != '') {
	echo '<div class="ph-desc">'. JHtml::_('content.prepare', $this->category[0]->description). '</div>';
}
?>