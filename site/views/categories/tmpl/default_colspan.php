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

$i      = $j = $k = 0;
$last   = count($this->t['categories']);
$mod    = $last%5;

echo '<div class="ph-t-box">';
foreach ($this->t['categories'] as $v) {

	$size = 'medium';
	// START
	if ($j == 5|| ($j == 0 && $k > 0)) {//Not valid for first zero, but valid for each zero after first round
		echo '</div>';// End the row4 so 5 can start the new row
		echo '<div class="'.$this->s['c']['grid'].' ph-row-cats ph-t-box">' . "\n";// Start the new row after 4 ended it
	}

	if ($j == 0 || $j == 9) {
		$size = 'large';
		echo '<div class="ph-t-row ph-t-row-c1">' . "\n";// c1 is colspan
		echo ' <div class="'.$this->s['c']['thumbnail'].' ph-t-cell b-thumbnail ph-thumbnail ph-thumbnail-c">' . "\n";
	}

	if ($j == 1 || $j == 3 || $j == 5 || $j == 7) {

		if (($last - 2 == $i) && $mod != 0 ) {
			echo '<div class="ph-t-row ph-t-row-c1">' . "\n";// c2 is standard column (no colspan) - not for last group

		} else if (($last - 1 == $i) && $mod == 2 ) {
			echo '<div class="ph-t-row ph-t-row-c1">' . "\n";// c2 is standard column (no colspan) - not for last group
			$size = 'large';
		} else {
			echo '<div class="ph-t-row ph-t-row-c2">' . "\n";// c2 is standard column (no colspan)

		}
		echo ' <div class="'.$this->s['c']['thumbnail'].' ph-t-cell b-thumbnail ph-thumbnail ph-thumbnail-c">' . "\n";
	}
	if ($j == 2 || $j == 4 || $j == 6 || $j == 8) {
		echo ' <div class="'.$this->s['c']['thumbnail'].' ph-t-cell b-thumbnail ph-thumbnail ph-thumbnail-c">' . "\n";
	}



	//echo $this->loadTemplate('category');
	//echo "Cell ". ($j) . "(".$i.") - ".$last;
	echo '<div class="'.$this->s['c']['grid'].' ph-item-box">';


	//echo '<div class="b-thumbnail ph-thumbnail ph-thumbnail-c">';
	//echo '<div class="ph-item-content">';


	$d									= array();
	$d['t'] 							= $this->t;
    $d['s'] 							= $this->s;
	$d['v']								= $v;
	$d['image_size']					= $size;
	echo $layoutC->render($d);

	echo '</div>';// end ph-item-box

	//echo '<div class="clearfix"></div>';
	//echo '</div>';// end ph-caption
	//echo '</div>';// end ph-item-content
	//echo '</div>';// end thumbnails
	//echo '</div>';// end ph-item-box


	// END
	if ($j == 0 || $j == 2 || $j == 4 || $j == 6 || $j == 8 || $j == 9) {
		echo ' </div>';
		echo '</div>';
	}

	if ($j == 1 || $j == 3 || $j == 5 || $j == 7) {
		echo ' </div>' . "\n";

		if ($i == ($last - 1)) {

			echo '</div>';// close last opened column
		}
	}


	$i++;
	$j++;
	if ($j >= 10) {$j = 0; $k = 1;}// run the round again from start

}
echo '</div>';



?>
