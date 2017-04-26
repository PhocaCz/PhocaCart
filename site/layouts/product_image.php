<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;
?>

<div class="phIBoxOH <?php echo $d['layouttype']; ?>">
	<div class="phIBox"><?php
		echo '<img src="'. JURI::base(true).'/'.$d['image']->rel.'" alt="" class="img-responsive ph-image '. $d['phil'].'" '.$d['imagestyle'].' />';

if (isset($d['image2']->rel) && $d['image2']->rel != '') { 
	echo '<span class="phIRBox"><img src="'. JURI::base(true).'/'.$d['image2']->rel.'" alt="" class="img-responsive ph-image phIR" '. $d['imagestyle'].' /></span>';
}?>
	</div>
</div>