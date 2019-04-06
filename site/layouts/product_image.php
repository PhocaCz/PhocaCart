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
$productIdName			= 'V'.$d['typeview'].'P'.(int)$d['product_id'];
$altValue               = PhocaCartImage::getAltTitle($d['title'], $d['image']->original);
?><div class="phIBoxOH <?php echo $d['layouttype']; ?>">
	<div class="phIBox"><?php
		echo '<img src="'. JURI::base(true).'/'.$d['image']->rel.'" alt="'.$altValue.'" class="img-responsive ph-image '. $d['phil'].' phjProductImage'.$productIdName.'" '.$d['imagestyle'].' data-image="'. JURI::base(true).'/'.$d['default_image']->rel.'" />';

if (isset($d['image2']->rel) && $d['image2']->rel != '') {
	echo '<span class="phIRBox"><img src="'. JURI::base(true).'/'.$d['image2']->rel.'" alt="'.$altValue.'" class="img-responsive ph-image phIR phjProductImageNoChange'.$productIdName.'" '. $d['imagestyle'].' /></span>';
}?>
	</div>
</div>
