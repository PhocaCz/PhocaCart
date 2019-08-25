<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 				= $displayData;

$d['paramname']	= str_replace('_', '', $d['param']);
if (isset($d['param2']) && $d['param2'] != '') {
	$d['param2name']	= str_replace('_', '', $d['param2']);
}

if (isset($d['param2']) && $d['param2'] != '') {
	// We have second parameter, so in first we define that the javascript should wait with re-direct
	$jsSet	= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1, 1);';
	$jsSet	.= 'phChangeFilter(\''.$d['param2'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['param2name'].'&quot;]\').val(), 1, \'text\', 1);';
	$jsClear		 = 'phClearField(\'#'.$d['id'].$d['paramname'].'\');';
	$jsClear		.= 'phClearField(\'#'.$d['id'].$d['param2name'].'\');';
	$jsClear		.= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1, 1);';
	$jsClear		.= 'phChangeFilter(\''.$d['param2'].'\', \'\', 0, \'text\', 1);';
} else {
	// We have only one parameter so we don't need define wait and the site is reloaded immediately
	$jsSet	= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1);';
	$jsClear= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1);';
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

$displayData 	= null;
?>
<div class="<?php echo $d['s']['c']['panel.panel-default'] ?>">
	<div class="<?php echo $d['s']['c']['panel-heading'] ?>" role="tab" id="heading<?php echo $d['param']; ?>">
		<h4 class="<?php echo $d['s']['c']['panel-title'] ?>">
			<a data-toggle="collapse" href="#collapse<?php echo $d['param']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $d['param']; ?>" class="panel-collapse"><span class="<?php echo $d['triangle_class'] ?>"></span></a>
			<a data-toggle="collapse" href="#collapse<?php echo $d['param']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $d['param']; ?>" class="panel-collapse"><?php echo $d['title'] ?></a>
		</h4>
	</div>

	<div id="collapse<?php echo $d['param']; ?>" class="<?php echo $d['collapse_class'] ?>" role="tabpanel" aria-labelledby="heading<?php echo $d['param']; ?>">
		<div class="<?php echo $d['s']['c']['panel-body'] ?> <?php echo $d['s']['c']['form-horizontal'] ?>" id="<?php echo $d['id']; ?>">

			<div class="<?php echo $d['s']['c']['form-group'] ?> <?php echo $d['s']['c']['row'] ?>" <?php echo $styleFormGroup ?>>
				<label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['paramname']; ?>"><?php echo $d['title1']; ?></label>
				<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>"><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['paramname']; ?>" value="<?php echo $d['getparams'][0]; ?>" id="<?php echo $d['id'].$d['paramname']; ?>" /></div>
			</div>
			<?php
			if (isset($d['param2']) && $d['param2'] != '') { ?>
				<div class="<?php echo $d['s']['c']['form-group'] ?> <?php echo $d['s']['c']['row'] ?>" <?php echo $styleFormGroup ?>>
					<label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['param2name']; ?>"><?php echo $d['title2']; ?></label>
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
					<button class="<?php echo $d['s']['c']['btn.btn-success'] ?> tip hasTooltip" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo $d['titleset']; ?>"><span class="<?php echo $d['s']['i']['ok'] ?>"></span></button>
					<button class="<?php echo $d['s']['c']['btn.btn-danger'] ?> tip hasTooltip <?php echo $d['s']['c']['pull-right'] ?>" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo $d['titleclear']; ?>"><span class="<?php echo $d['s']['i']['clear'] ?>"></span></button>
				</div>
			</div>
            </div>
			<?php
			/*

			<button class="btn tip hasTooltip" type="button" onclick="phChangeFilter('.$d['param'].', \''. $value.'\', 0, \'text\');" title="<?php echo JText::_('COM_PHOCACART_CLEAR_PRICE'); ?>"><span class="icon-remove"></span></button>

			foreach ($d['items'] as $k => $v) {

				$checked 	= '';
				$value 		= (int)$v->id .'-'. htmlspecialchars($v->alias);

				if (in_array($value, $d['getparams'])) {
					$checked 	= 'checked';
				}

				echo '<div class="checkbox">';
				echo '<label><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="phChangeFilter(this, \''.$d['param'].'\', \''. $value.'\');" />'.$v->title.'</label>';
				echo '</div>';

			}*/
		?>
            <div class="ph-cb"></div>
		</div>
	</div>
</div>
