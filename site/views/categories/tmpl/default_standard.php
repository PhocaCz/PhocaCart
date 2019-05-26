<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutC	= new JLayoutFile('categories_category', null, array('component' => 'com_phocacart'));


$i = 0;
$nc= (int)$this->t['columns_cats'];
$nw= 12/$nc;//1,2,3,4,6,12
echo '<div class="'.PhocacartRenderFront::getClass(array('row', $this->t['class-row-flex'], 'grid', 'ph-row-cats', $this->t['class_lazyload'])).'">';

foreach ($this->t['categories'] as $v) {

	//if ($i%$nc==0) { echo '<div class="row">';}

	echo '<div class="row-item col-sm-6 col-md-'.$nw.'">';
	echo '<div class="ph-item-box grid">';
	echo '<div class="b-thumbnail ph-thumbnail ph-thumbnail-c">';
	echo '<div class="ph-item-content">';

	$d					= array();
	$d['t']				= $this->t;
	$d['p']             = $this->p;
	$d['v']				= $v;
	$d['image_size']	= 'medium';
	echo $layoutC->render($d);




	echo '<div class="clearfix"></div>';
	//echo '</div>';// end ph-caption
	echo '</div>';// end ph-item-content
	echo '</div>';// end thumbnails
	echo '</div>';// end ph-item-box
	echo '</div>'. "\n";// end row item

	$i++;
	// if ($i%$nc==0 || $c==$i) { echo '</div>';}
}
echo '</div>';
?>
