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
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);

$d['paramname']	= str_replace('_', '', $d['param']);
if (isset($d['param2']) && $d['param2'] != '') {
	$d['param2name']	= str_replace('_', '', $d['param2']);
}

if (isset($d['param2']) && $d['param2'] != '') {
	// We have second parameter, so in first we define that the javascript should wait with re-direct

    $jsSet = '';
    if (isset($d['forcecategory']['idalias']) && $d['forcecategory']['idalias']  != '') {
        // Category View - force the category parameter if set in parameters
        $jsSet .= 'phChangeFilter(\'c\', \''.$d['forcecategory']['idalias'].'\', 1, \'text\', 0, 1, 1);';
    }
    $jsSet	.= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1, 1, 1);';
	$jsSet	.= 'phChangeFilter(\''.$d['param2'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['param2name'].'&quot;]\').val(), 1, \'text\', 1, 0, 1);';

	$jsClear		 = 'phClearField(\'#'.$d['id'].$d['paramname'].'\');';
	$jsClear		.= 'phClearField(\'#'.$d['id'].$d['param2name'].'\');';
	$jsClear		.= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1, 1, 1);';
	$jsClear		.= 'phChangeFilter(\''.$d['param2'].'\', \'\', 0, \'text\', 1, 0, 1);';
} else {
	// We have only one parameter so we don't need define wait and the site is reloaded immediately
    $jsSet = '';
    if (isset($d['forcecategory']['idalias']) && $d['forcecategory']['idalias']  != '') {
        // Category View - force the category parameter if set in parameters
        $jsSet .= 'phChangeFilter(\'c\', \''.$d['forcecategory']['idalias'].'\', 1, \'text\', 0, 1, 1);';
    }
	$jsSet	.= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1, 0, 1);';
	$jsClear= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1, 0 , 1);';
}


// Display Price input text or Price input range or both
$styleFormGroup = '';
if (isset($d['filterprice']) && $d['filterprice'] == 2) {
	// Hide form input text of price from and price to in case
	// only range (graphic output) should be displayed
	// 1 ... display only input text
	// 3 ... display input text and input range
	// 2 ... display only input range (the input text will be hidden because they values we need to manage the form)
	$styleFormGroup = 'style="display:none"';
}

$title = isset($d['titleheader']) && $d['titleheader'] != '' ? $d['titleheader'] : $d['title'];
$displayData 	= null;
?><span class="<?php echo $d['s']['c']['dropdown'] ?> filter-<?php echo $dParamAttr; ?>">
  <button class="<?php echo $d['s']['c']['btn.btn-outline-secondary'] ?> <?php echo $d['s']['c']['dropdown-toggle'] ?>" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><?php echo $title ?></button>
  <div <?php echo $d['s']['a']['dropdown'] ?> class="<?php echo $d['s']['c']['dropdown-menu'] ?> <?php echo $d['s']['c']['px-3'] ?> <?php echo $d['s']['c']['py-3'] ?> <?php echo $d['s']['c']['form-horizontal'] ?>" id="<?php echo $d['id']; ?>">
    <div style="min-width: 250px">
	  <div class="<?php echo $d['s']['c']['form-group'] ?> <?php echo $d['s']['c']['row'] ?>" <?php echo $styleFormGroup ?>>
		  <label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['id'].$d['paramname']; ?>"><?php echo $d['title1']; ?></label>
			<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>"><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['paramname']; ?>" value="<?php echo $d['getparams'][0]; ?>" id="<?php echo $d['id'].$d['paramname']; ?>" /></div>
		</div>
    <?php
      if (isset($d['param2']) && $d['param2'] != '') { ?>
        <div class="<?php echo $d['s']['c']['form-group'] ?> <?php echo $d['s']['c']['row'] ?>" <?php echo $styleFormGroup ?>>
					<label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['id'].$d['param2name']; ?>"><?php echo $d['title2']; ?></label>
					<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>"><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['param2name']; ?>" value="<?php echo $d['getparams2'][0]; ?>" id="<?php echo $d['id'].$d['param2name']; ?>" /></div>
				</div>
      <?php } ?>


    <?php
    // Display filter price range (graphic range)
    if (isset($d['filterprice']) && ($d['filterprice'] == 2 || $d['filterprice'] == 3)) { ?>
      <div class="<?php echo $d['s']['c']['row'] ?>">
			<div class="<?php echo $d['s']['c']['col.xs12.sm12.md12'] ?> ph-price-filter-box">
				<div id="phPriceFilterRange"></div>
				<div id="phPriceFilterPrice"></div>
			</div>
            </div>
    <?php } ?>

    <div class="<?php echo $d['s']['c']['row'] ?>">
			<div class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>"></div>
			<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>">
				<div class="<?php echo $d['s']['c']['pull-right'] ?> <?php echo $d['s']['c']['btn-group'] ?> ph-zero ph-right-zero">
					<button class="<?php echo $d['s']['c']['btn.btn-success'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?>" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo $d['titleset']; ?>"  aria-label="<?php echo $d['titleset']; ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['ok']) ?></button>
					<button class="<?php echo $d['s']['c']['btn.btn-danger'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?> <?php echo $d['s']['c']['pull-right'] ?> ph-button-clear-box" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo $d['titleclear']; ?>" aria-label="<?php echo $d['titleclear']; ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['clear']) ?></button>
				</div>
			</div>
    </div>

    <div class="ph-cb"></div>
    </div>
	</div>
</span>
