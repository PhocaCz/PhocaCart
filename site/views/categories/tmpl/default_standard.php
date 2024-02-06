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
$layoutC	= new FileLayout('categories_category', null, array('component' => 'com_phocacart'));

echo '<div class="'.PhocacartRenderFront::completeClass(array($this->s['c']['row'], $this->t['class_row_flex'], 'grid', 'ph-row-cats', $this->t['class_lazyload'])).'">';
$col		= 12/(int)$this->p->get('columns_cats', 3);
$colMobile	= 12/(int)$this->p->get('columns_cats_mobile', 1);

foreach ($this->t['categories'] as $v) {

	echo '<div class="'.$this->s['c']["col.xs{$colMobile}.sm{$col}.md{$col}"].' row-item">';
	echo '<div class="'.$this->s['c']['grid'].' ph-item-box">';
	echo '<div class="'.$this->s['c']['thumbnail'].' b-thumbnail ph-thumbnail ph-thumbnail-c">';
	echo '<div class="ph-item-content">';

	$d					= array();
	$d['t']				= $this->t;
	$d['s'] 			= $this->s;
	$d['p']             = $this->p;
	$d['v']				= $v;
	$d['image_size']	= 'medium';
	echo $layoutC->render($d);

	echo '<div class="ph-cb"></div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>'. "\n";
}
echo '</div>';
?>
