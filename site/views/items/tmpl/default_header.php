<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;






if (isset($this->category[0]->parentid) && ($this->t['display_back'] == 1 || $this->t['display_back'] == 3)) {
	if ($this->category[0]->parentid == 0) {
		$linkUp = Route::_(PhocacartRoute::getCategoriesRoute());
		$linkUpText = Text::_('COM_PHOCACART_CATEGORIES');
	} else if ($this->category[0]->parentid > 0) {
		$linkUp = Route::_(PhocacartRoute::getCategoryRoute($this->category[0]->parentid, $this->category[0]->parentalias));
		$linkUpText = $this->category[0]->parenttitle;
	} else {
		$linkUp 	= false;
		$linkUpText = false;
	}

	if ($linkUp && $linkUpText) {
		echo '<div class="ph-top">'
		.'<a class="'.$this->s['c']['btn.btn-secondary'].'" title="'.$linkUpText.'" href="'. $linkUp.'" >'
        //.'<span class="'.$this->s['i']['back-category'].'"></span> '
		. PhocacartRenderIcon::icon($this->s['i']['back-category'], '', ' ')
		.Text::_($linkUpText).'</a>'
        .'</div>';
	}
}

echo $this->t['event']->onItemsBeforeHeader;

$title = '';
if (isset($this->category[0]->title) && $this->category[0]->title != '') {
	$title = $this->category[0]->title;
}

// DIFF CATEGORY / ITEMS
echo PhocacartRenderFront::renderHeader(array($title, Text::_('COM_PHOCACART_ITEMS')), '', $this->t['image_items_view']);

if ( isset($this->category[0]->description) && $this->category[0]->description != '') {
	echo '<div class="ph-desc">'. HTMLHelper::_('content.prepare', $this->category[0]->description). '</div>';
}
?>
